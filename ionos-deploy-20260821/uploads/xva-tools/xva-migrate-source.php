<?php
/**
 * Import the public XVA Gallery WordPress content into the local staging site.
 *
 * Run inside the Docker WordPress CLI container:
 * wp eval-file /var/www/html/wp-content/uploads/xva-tools/xva-migrate-source.php
 *
 * The importer is deliberately idempotent. It keys records by the source
 * URL, downloads referenced uploads into the local Media Library, rewrites
 * content URLs to local attachment URLs, and keeps the original source HTML
 * in _xva_source_content for later template mapping.
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

define( 'XVA_MIGRATION_SOURCE', 'https://www.xvagallery.com' );

$GLOBALS['xva_migration_api_cache']   = array();
$GLOBALS['xva_migration_media_cache'] = array();
$GLOBALS['xva_migration_url_map']     = array();
$GLOBALS['xva_migration_page_media_cache'] = array();
$GLOBALS['xva_migration_report']      = array(
	'pages'       => 0,
	'artists'     => 0,
	'exhibitions' => 0,
	'media'       => 0,
	'media_errors' => 0,
);

function xva_migration_request( $endpoint, $args = array() ) {
	global $xva_migration_api_cache;

	$url = add_query_arg( $args, trailingslashit( XVA_MIGRATION_SOURCE ) . 'wp-json/' . ltrim( $endpoint, '/' ) );
	if ( isset( $xva_migration_api_cache[ $url ] ) ) {
		return $xva_migration_api_cache[ $url ];
	}

	$response = wp_remote_get(
		$url,
		array(
			'timeout'    => 60,
			'redirection' => 3,
			'user-agent' => 'XVA Gallery local migration/' . get_bloginfo( 'version' ),
		)
	);

	if ( is_wp_error( $response ) ) {
		WP_CLI::warning( 'Request failed: ' . $url . ' — ' . $response->get_error_message() );
		return array();
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		WP_CLI::warning( 'Request returned HTTP ' . $code . ': ' . $url );
		return array();
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) ) {
		WP_CLI::warning( 'Invalid JSON response: ' . $url );
		return array();
	}

	$result = array(
		'data'    => $data,
		'headers' => wp_remote_retrieve_headers( $response ),
	);
	$xva_migration_api_cache[ $url ] = $result;
	return $result;
}

function xva_migration_collection( $type ) {
	$items = array();
	$page  = 1;

	while ( $page <= 100 ) {
		$response = xva_migration_request(
			'wp/v2/' . $type,
			array(
				'per_page' => 100,
				'page'     => $page,
				'_fields'  => 'id,slug,link,title,content,excerpt,date,modified,featured_media,status,template,parent',
			)
		);

		if ( empty( $response['data'] ) || ! is_array( $response['data'] ) ) {
			break;
		}

		foreach ( $response['data'] as $item ) {
			if ( is_array( $item ) ) {
				$items[] = $item;
			}
		}

		$total_pages = isset( $response['headers']['x-wp-totalpages'] ) ? (int) $response['headers']['x-wp-totalpages'] : 1;
		if ( $page >= $total_pages || count( $response['data'] ) < 100 ) {
			break;
		}
		$page++;
	}

	return $items;
}

function xva_migration_clean_title( $value ) {
	$value = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
	return trim( wp_strip_all_tags( $value ) );
}

function xva_migration_source_path( $url ) {
	$path = wp_parse_url( (string) $url, PHP_URL_PATH );
	return '/' . trim( (string) $path, '/' ) . '/';
}

function xva_migration_source_url( $url ) {
	$path = xva_migration_source_path( $url );
	return untrailingslashit( XVA_MIGRATION_SOURCE . $path ) . '/';
}

function xva_migration_image_urls( $html ) {
	$urls = array();
	$html = html_entity_decode( (string) $html, ENT_QUOTES, 'UTF-8' );

	preg_match_all(
		'~(?:https?:)?//(?:www\.)?xvagallery\.com/wp-content/uploads/[^"\'\s<>\\)“”]+~iu',
		$html,
		$matches
	);
	$encoded_html = rawurldecode( $html );
	preg_match_all(
		'~https?://(?:www\.)?xvagallery\.com/wp-content/uploads/[^"\'\s<>\\)“”]+~iu',
		$encoded_html,
		$encoded_matches
	);
	$matches[0] = array_merge( (array) ( $matches[0] ?? array() ), (array) ( $encoded_matches[0] ?? array() ) );

	foreach ( (array) ( $matches[0] ?? array() ) as $url ) {
		if ( 0 === strpos( $url, '//' ) ) {
			$url = 'https:' . $url;
		}
		$url = preg_replace( '/[^\p{L}\p{N}\._\/:%-]+$/u', '', $url );
		$url = esc_url_raw( $url );
		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		if ( ! preg_match( '/\.(?:jpe?g|png|gif|webp|avif|svg|pdf|docx?|xlsx?)$/i', $path ) ) {
			continue;
		}
		$urls[] = $url;
	}

	return array_values( array_unique( $urls ) );
}

/**
 * The live artist templates render gallery images from custom fields, so
 * those images are not always present in the REST post body. Read the
 * rendered source page as well and collect its raster artwork images.
 */
function xva_migration_source_page_image_urls( $url ) {
	global $xva_migration_page_media_cache;

	$source_url = xva_migration_source_url( $url );
	if ( ! $source_url ) {
		return array();
	}

	if ( isset( $xva_migration_page_media_cache[ $source_url ] ) ) {
		return $xva_migration_page_media_cache[ $source_url ];
	}

	$response = wp_remote_get(
		$source_url,
		array(
			'timeout'     => 60,
			'redirection' => 3,
			'user-agent'  => 'XVA Gallery local migration/' . get_bloginfo( 'version' ),
		)
	);

	if ( is_wp_error( $response ) ) {
		WP_CLI::warning( 'Rendered page request failed: ' . $source_url . ' — ' . $response->get_error_message() );
		$xva_migration_page_media_cache[ $source_url ] = array();
		return array();
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		WP_CLI::warning( 'Rendered page returned HTTP ' . $code . ': ' . $source_url );
		$xva_migration_page_media_cache[ $source_url ] = array();
		return array();
	}

	$urls = array();
	foreach ( xva_migration_image_urls( wp_remote_retrieve_body( $response ) ) as $image_url ) {
		$path = (string) wp_parse_url( $image_url, PHP_URL_PATH );
		$name = wp_basename( $path );

		// The rendered page also contains site chrome. Keep artwork photos and
		// leave logos, icons, loaders, and other interface assets out of ACF.
		if ( ! preg_match( '/\.(?:jpe?g|png|gif|webp|avif)$/i', $path ) ) {
			continue;
		}
		if ( preg_match( '/(?:logo|favicon|icon|placeholder|loader|sprite)/i', $name ) ) {
			continue;
		}

		$urls[] = xva_migration_original_media_url( $image_url );
	}

	$urls = array_values( array_unique( $urls ) );
	$xva_migration_page_media_cache[ $source_url ] = $urls;
	return $urls;
}

function xva_migration_original_media_url( $url ) {
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	$original_path = preg_replace( '/-\d{2,4}x\d{2,4}(?=\.[a-z0-9]+$)/i', '', $path );
	if ( $original_path && $original_path !== $path ) {
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME ) ?: 'https';
		$host   = wp_parse_url( $url, PHP_URL_HOST ) ?: 'www.xvagallery.com';
		return $scheme . '://' . $host . $original_path;
	}
	return $url;
}

function xva_migration_source_media( $source_media_id ) {
	global $xva_migration_api_cache;

	$source_media_id = (int) $source_media_id;
	if ( ! $source_media_id ) {
		return array();
	}

	$cache_key = 'media-record-' . $source_media_id;
	if ( isset( $xva_migration_api_cache[ $cache_key ] ) ) {
		return $xva_migration_api_cache[ $cache_key ];
	}

	$response = xva_migration_request(
		'wp/v2/media/' . $source_media_id,
		array(
			'_fields' => 'id,source_url,alt_text,caption,title',
		)
	);
	$data = ! empty( $response['data'] ) && is_array( $response['data'] ) ? $response['data'] : array();
	$xva_migration_api_cache[ $cache_key ] = $data;
	return $data;
}

function xva_migration_existing_attachment( $source_url ) {
	$ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_xva_source_media_url',
			'meta_value'     => $source_url,
		)
	);
	return ! empty( $ids ) ? (int) $ids[0] : 0;
}

function xva_migration_download_attachment( $source_url, $parent_id, $title = '', $alt = '' ) {
	global $xva_migration_media_cache, $xva_migration_report;

	$source_url = esc_url_raw( html_entity_decode( (string) $source_url, ENT_QUOTES, 'UTF-8' ) );
	if ( ! $source_url || 0 !== strpos( $source_url, XVA_MIGRATION_SOURCE . '/wp-content/uploads/' ) ) {
		return 0;
	}

	if ( isset( $xva_migration_media_cache[ $source_url ] ) ) {
		return (int) $xva_migration_media_cache[ $source_url ];
	}

	$existing = xva_migration_existing_attachment( $source_url );
	if ( $existing ) {
		$xva_migration_media_cache[ $source_url ] = $existing;
		return $existing;
	}

	$path = (string) wp_parse_url( $source_url, PHP_URL_PATH );
	$name = rawurldecode( wp_basename( $path ) );
	$name = sanitize_file_name( $name );
	if ( ! $name ) {
		$name = 'xva-source-' . md5( $source_url ) . '.jpg';
	}

	$tmp = download_url( $source_url, 120 );
	if ( is_wp_error( $tmp ) ) {
		$xva_migration_report['media_errors']++;
		WP_CLI::warning( 'Media download failed: ' . $source_url . ' — ' . $tmp->get_error_message() );
		return 0;
	}

	$file = array(
		'name'     => $name,
		'tmp_name' => $tmp,
		'type'     => wp_check_filetype( $name )['type'] ?? 'image/jpeg',
		'error'    => 0,
		'size'     => file_exists( $tmp ) ? filesize( $tmp ) : 0,
	);

	$attachment_id = media_handle_sideload( $file, (int) $parent_id, $title ? $title : $name );
	@unlink( $tmp );

	if ( is_wp_error( $attachment_id ) ) {
		$xva_migration_report['media_errors']++;
		WP_CLI::warning( 'Media import failed: ' . $source_url . ' — ' . $attachment_id->get_error_message() );
		return 0;
	}

	$attachment_id = (int) $attachment_id;
	update_post_meta( $attachment_id, '_xva_source_media_url', $source_url );
	if ( $alt ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
	}

	$xva_migration_media_cache[ $source_url ] = $attachment_id;
	$xva_migration_report['media']++;
	return $attachment_id;
}

function xva_migration_import_media_for_content( $html, $parent_id, $title, $featured_url = '', $featured_alt = '', $additional_urls = array() ) {
	global $xva_migration_url_map;

	$media_ids = array();
	$urls      = xva_migration_image_urls( $html );
	if ( $featured_url ) {
		$urls[] = $featured_url;
	}
	if ( $additional_urls ) {
		$urls = array_merge( $urls, (array) $additional_urls );
	}
	$urls = array_values( array_unique( $urls ) );

	foreach ( $urls as $source_url ) {
		$original_url = xva_migration_original_media_url( $source_url );
		$attachment_id = xva_migration_download_attachment( $original_url, $parent_id, $title, $featured_alt );
		if ( ! $attachment_id && $original_url !== $source_url ) {
			$attachment_id = xva_migration_download_attachment( $source_url, $parent_id, $title, $featured_alt );
		}
		if ( ! $attachment_id ) {
			continue;
		}

		$media_ids[] = $attachment_id;
		$local_url = wp_get_attachment_url( $attachment_id );
		if ( $local_url ) {
			$xva_migration_url_map[ $source_url ] = $local_url;
			$xva_migration_url_map[ $original_url ] = $local_url;
		}
	}

	$media_ids = array_values( array_unique( array_map( 'intval', $media_ids ) ) );
	$rewritten = (string) $html;
	foreach ( $xva_migration_url_map as $source_url => $local_url ) {
		$rewritten = str_replace( $source_url, $local_url, $rewritten );
		$rewritten = str_replace( esc_attr( $source_url ), esc_attr( $local_url ), $rewritten );
	}

	return array(
		'html'       => $rewritten,
		'media_ids'  => $media_ids,
	);
}

function xva_migration_date_range( $text ) {
	$text = html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES, 'UTF-8' );
	$months = array(
		'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
		'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
		'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
	);
	$pattern = '/(\d{1,2})(?:st|nd|rd|th)?\s+([a-z]+)(?:\s+(\d{4}))?\s*(?:to|[-–—])\s*(\d{1,2})(?:st|nd|rd|th)?\s+([a-z]+)(?:\s+(\d{4}))?/i';
	if ( ! preg_match( $pattern, $text, $match ) ) {
		return array();
	}

	$start_month = strtolower( $match[2] );
	$end_month   = strtolower( $match[5] );
	if ( ! isset( $months[ $start_month ], $months[ $end_month ] ) ) {
		return array();
	}

	$end_year   = ! empty( $match[6] ) ? (int) $match[6] : (int) gmdate( 'Y' );
	$start_year = ! empty( $match[3] ) ? (int) $match[3] : $end_year;
	$start      = DateTime::createFromFormat( '!Y-n-j', $start_year . '-' . $months[ $start_month ] . '-' . (int) $match[1] );
	$end        = DateTime::createFromFormat( '!Y-n-j', $end_year . '-' . $months[ $end_month ] . '-' . (int) $match[4] );

	if ( ! $start || ! $end ) {
		return array();
	}

	return array(
		'start' => $start->format( 'Ymd' ),
		'end'   => $end->format( 'Ymd' ),
	);
}

function xva_migration_status_from_dates( $dates ) {
	if ( empty( $dates['start'] ) || empty( $dates['end'] ) ) {
		return 'past';
	}
	$today = (int) current_time( 'Ymd' );
	if ( (int) $dates['start'] > $today ) {
		return 'upcoming';
	}
	if ( (int) $dates['end'] >= $today ) {
		return 'current';
	}
	return 'past';
}

function xva_migration_set_terms( $post_id, $terms, $taxonomy ) {
	$terms = array_values( array_unique( array_filter( (array) $terms ) ) );
	$desired_ids = array();
	foreach ( $terms as $term ) {
		$existing = term_exists( $term, $taxonomy );
		if ( is_array( $existing ) && ! empty( $existing['term_id'] ) ) {
			$desired_ids[] = (int) $existing['term_id'];
		} elseif ( is_numeric( $existing ) ) {
			$desired_ids[] = (int) $existing;
		} else {
			$created = wp_insert_term( $term, $taxonomy );
			if ( ! is_wp_error( $created ) && ! empty( $created['term_id'] ) ) {
				$desired_ids[] = (int) $created['term_id'];
			}
		}
	}

	$current_ids = wp_get_object_terms(
		$post_id,
		$taxonomy,
		array(
			'fields' => 'ids',
		)
	);
	if ( ! is_wp_error( $current_ids ) ) {
		$current_ids = array_map( 'intval', (array) $current_ids );
		$compare_current = $current_ids;
		$compare_desired = array_map( 'intval', $desired_ids );
		sort( $compare_current );
		sort( $compare_desired );
		if ( $compare_current === $compare_desired ) {
			return;
		}
	}

	wp_set_object_terms( $post_id, $desired_ids, $taxonomy, false );
}

function xva_migration_related_slugs( $html, $type ) {
	$segment = 'xva_artist' === $type ? 'artist' : 'exhibitions';
	$slugs   = array();
	$html    = html_entity_decode( (string) $html, ENT_QUOTES, 'UTF-8' );

	preg_match_all(
		'~https?://www\.xvagallery\.com/' . preg_quote( $segment, '~' ) . '/([^/?#"\']+)~i',
		$html,
		$matches
	);
	foreach ( (array) ( $matches[1] ?? array() ) as $slug ) {
		$slugs[] = sanitize_title( rawurldecode( $slug ) );
	}
	return array_values( array_unique( array_filter( $slugs ) ) );
}

function xva_migration_upsert_post( $record, $post_type, $content, $excerpt ) {
	$source_url = xva_migration_source_url( $record['link'] ?? '' );
	$existing   = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_xva_source_url',
			'meta_value'     => $source_url,
		)
	);

	if ( ! $existing && ! empty( $record['slug'] ) ) {
		$by_path = get_page_by_path( sanitize_title( $record['slug'] ), OBJECT, $post_type );
		if ( $by_path ) {
			$existing = array( $by_path->ID );
		}
	}

	$postarr = array(
		'post_title'   => xva_migration_clean_title( $record['title']['rendered'] ?? '' ),
		'post_name'    => sanitize_title( $record['slug'] ?? '' ),
		'post_content' => wp_slash( $content ),
		'post_excerpt' => wp_slash( wp_kses_post( $excerpt ) ),
		'post_status'  => 'publish',
		'post_type'    => $post_type,
	);
	if ( ! empty( $record['date'] ) ) {
		$postarr['post_date'] = gmdate( 'Y-m-d H:i:s', strtotime( $record['date'] ) );
	}

	if ( $existing ) {
		$postarr['ID'] = (int) $existing[0];
		$post_id = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( 'Could not import ' . $source_url . ': ' . $post_id->get_error_message() );
		return 0;
	}

	$post_id = (int) $post_id;
	update_post_meta( $post_id, '_xva_source_url', $source_url );
	update_post_meta( $post_id, '_xva_source_id', (int) ( $record['id'] ?? 0 ) );
	update_post_meta( $post_id, '_xva_source_content', $content );
	update_post_meta( $post_id, '_xva_source_modified', sanitize_text_field( $record['modified'] ?? '' ) );
	return $post_id;
}

function xva_migration_set_elementor_shortcode( $post_id, $shortcode ) {
	$content = array(
		array(
			'id'         => 'xva-source-' . $post_id,
			'elType'     => 'widget',
			'widgetType' => 'shortcode',
			'settings'   => array(
				'shortcode' => $shortcode,
			),
			'elements'   => array(),
		),
	);
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );
}

function xva_migration_page_template( $post_id, $slug ) {
	$map = array(
		'home'                                      => 'home',
		'home-new'                                  => 'home',
		'home-2'                                    => 'home',
		'artists'                                   => 'artists',
		'artists-list'                              => 'artists',
		'exhibitions-new'                           => 'exhibitions',
		'exhibitions-old'                           => 'exhibitions',
		'press-release'                              => 'press',
		'art-consultancy-and-advisory-services'    => 'art-consultancy',
		'hotel-cafe'                                => 'hotel-cafe',
		'hotel-cafe-2'                              => 'hotel-cafe',
		'privacy-policy'                             => 'privacy-policy',
	);
	if ( isset( $map[ $slug ] ) ) {
		xva_migration_set_elementor_shortcode( $post_id, '[xva_mockup_page page="' . $map[ $slug ] . '"]' );
	}
}

function xva_migration_page( $record ) {
	global $xva_migration_report;

	$content = (string) ( $record['content']['rendered'] ?? '' );
	$excerpt = (string) ( $record['excerpt']['rendered'] ?? '' );
	$title   = xva_migration_clean_title( $record['title']['rendered'] ?? '' );
	$media   = xva_migration_source_media( $record['featured_media'] ?? 0 );
	$media_url = ! empty( $media['source_url'] ) ? $media['source_url'] : '';
	$media_alt = ! empty( $media['alt_text'] ) ? $media['alt_text'] : $title;
	$post_id = xva_migration_upsert_post( $record, 'page', $content, $excerpt );
	if ( ! $post_id ) {
		return 0;
	}

	// Import with the final parent so source media appears in the page's
	// Media Library relationship and the stored content uses local URLs.
	$media_result = xva_migration_import_media_for_content( $content, $post_id, $title, $media_url, $media_alt );
	$content = $media_result['html'];
	wp_update_post( array( 'ID' => $post_id, 'post_content' => wp_slash( $content ) ) );
	update_post_meta( $post_id, '_xva_source_content', $content );

	if ( ! empty( $media_result['media_ids'] ) ) {
		foreach ( $media_result['media_ids'] as $attachment_id ) {
			wp_update_post( array( 'ID' => (int) $attachment_id, 'post_parent' => $post_id ) );
		}
		set_post_thumbnail( $post_id, (int) $media_result['media_ids'][0] );
	}
	xva_migration_page_template( $post_id, sanitize_title( $record['slug'] ?? '' ) );
	$xva_migration_report['pages']++;
	return $post_id;
}

function xva_migration_cpt( $record, $post_type ) {
	global $xva_migration_report;

	$content = (string) ( $record['content']['rendered'] ?? '' );
	$excerpt = (string) ( $record['excerpt']['rendered'] ?? '' );
	$title   = xva_migration_clean_title( $record['title']['rendered'] ?? '' );
	$media   = xva_migration_source_media( $record['featured_media'] ?? 0 );
	$media_url = ! empty( $media['source_url'] ) ? $media['source_url'] : '';
	$media_alt = ! empty( $media['alt_text'] ) ? $media['alt_text'] : $title;

	// Import the record first so media can be attached to the final parent.
	$post_id = xva_migration_upsert_post( $record, $post_type, $content, $excerpt );
	if ( ! $post_id ) {
		return 0;
	}

	$additional_media_urls = 'xva_artist' === $post_type
		? xva_migration_source_page_image_urls( $record['link'] ?? '' )
		: array();
	$media_result = xva_migration_import_media_for_content( $content, $post_id, $title, $media_url, $media_alt, $additional_media_urls );
	$content      = $media_result['html'];
	if ( $content !== (string) get_post_field( 'post_content', $post_id ) ) {
		wp_update_post( array( 'ID' => $post_id, 'post_content' => wp_slash( $content ) ) );
		update_post_meta( $post_id, '_xva_source_content', $content );
	}

	$media_ids = $media_result['media_ids'];
	if ( $media_ids ) {
		set_post_thumbnail( $post_id, (int) $media_ids[0] );
	}
	$image_media_ids = array_values(
		array_filter(
			$media_ids,
			static function ( $attachment_id ) {
				return wp_attachment_is_image( (int) $attachment_id );
			}
		)
	);

	if ( function_exists( 'update_field' ) ) {
		if ( 'xva_artist' === $post_type ) {
			update_field( 'portrait', $image_media_ids ? (int) $image_media_ids[0] : 0, $post_id );
			update_field( 'intro', wp_strip_all_tags( $excerpt ), $post_id );
			update_field( 'biography', $content, $post_id );
			update_field( 'artist_statement', '<p></p>', $post_id );
			update_field( 'origin', wp_strip_all_tags( $excerpt ), $post_id );
			update_field( 'artwork_gallery', $image_media_ids, $post_id );
			xva_migration_set_terms( $post_id, array( 'XVA Gallery' ), 'xva_artist_region' );
		} else {
			$dates = xva_migration_date_range( $excerpt . ' ' . $content );
			$status = xva_migration_status_from_dates( $dates );
			update_field( 'subtitle', 'XVA Gallery / Exhibition', $post_id );
			update_field( 'start_date', $dates['start'] ?? '', $post_id );
			update_field( 'end_date', $dates['end'] ?? '', $post_id );
			update_field( 'status_label', $status, $post_id );
			update_field( 'hero_image', $image_media_ids ? (int) $image_media_ids[0] : 0, $post_id );
			update_field( 'intro', wp_strip_all_tags( $excerpt ), $post_id );
			update_field( 'curatorial_text', $content, $post_id );
			update_field( 'artwork_gallery', $image_media_ids, $post_id );
			update_field( 'venue', 'XVA Gallery', $post_id );
			xva_migration_set_terms( $post_id, array( ucfirst( $status ) ), 'xva_exhibition_status' );
			if ( ! empty( $dates['start'] ) ) {
				xva_migration_set_terms( $post_id, array( substr( $dates['start'], 0, 4 ) ), 'xva_exhibition_year' );
			}
		}
	}
	xva_migration_set_elementor_shortcode( $post_id, '[xva_mockup_single type="' . ( 'xva_artist' === $post_type ? 'artist' : 'exhibition' ) . '"]' );

	$xva_migration_report[ 'xva_artist' === $post_type ? 'artists' : 'exhibitions' ]++;
	return $post_id;
}

function xva_migration_run() {
	global $xva_migration_report;

	$pages = xva_migration_collection( 'pages' );
	$posts = xva_migration_collection( 'posts' );
	WP_CLI::log( 'Source records discovered: ' . count( $pages ) . ' pages, ' . count( $posts ) . ' posts.' );

	$artist_ids     = array();
	$exhibition_ids = array();
	$records        = array();

	foreach ( $posts as $record ) {
		$path = xva_migration_source_path( $record['link'] ?? '' );
		if ( 0 === strpos( $path, '/artist/' ) ) {
			$record['_xva_type'] = 'xva_artist';
			$artist_ids[ sanitize_title( $record['slug'] ?? '' ) ] = xva_migration_cpt( $record, 'xva_artist' );
			$records[] = $record;
		} elseif ( 0 === strpos( $path, '/exhibitions/' ) ) {
			$record['_xva_type'] = 'xva_exhibition';
			$exhibition_ids[ sanitize_title( $record['slug'] ?? '' ) ] = xva_migration_cpt( $record, 'xva_exhibition' );
			$records[] = $record;
		}
	}

	$source_map = array();
	foreach ( $artist_ids as $slug => $id ) {
		if ( $id ) {
			$source_map['/artist/' . $slug . '/'] = $id;
		}
	}
	foreach ( $exhibition_ids as $slug => $id ) {
		if ( $id ) {
			$source_map['/exhibitions/' . $slug . '/'] = $id;
		}
	}

	foreach ( $records as $record ) {
		$slug = sanitize_title( $record['slug'] ?? '' );
		$type = $record['_xva_type'] ?? '';
		$id   = 'xva_artist' === $type ? ( $artist_ids[ $slug ] ?? 0 ) : ( $exhibition_ids[ $slug ] ?? 0 );
		if ( ! $id || ! function_exists( 'update_field' ) ) {
			continue;
		}

		$content = (string) ( $record['content']['rendered'] ?? '' );
		$related_type = 'xva_artist' === $type ? 'xva_exhibition' : 'xva_artist';
		$related_slugs = xva_migration_related_slugs( $content, $related_type );
		$related_ids = array();
		foreach ( $related_slugs as $related_slug ) {
			$path_key = ( 'xva_artist' === $related_type ? '/artist/' : '/exhibitions/' ) . $related_slug . '/';
			if ( ! empty( $source_map[ $path_key ] ) ) {
				$related_ids[] = (int) $source_map[ $path_key ];
			}
		}

		if ( 'xva_artist' === $type ) {
			update_field( 'selected_exhibitions', array_values( array_unique( $related_ids ) ), $id );
		} else {
			update_field( 'participating_artists', array_values( array_unique( $related_ids ) ), $id );
		}
	}

	$excluded_page_slugs = array( 'story-of-art', 'path-to-modernism' );
	foreach ( $pages as $record ) {
		$slug = sanitize_title( $record['slug'] ?? '' );
		if ( in_array( $slug, $excluded_page_slugs, true ) ) {
			WP_CLI::log( 'Skipping removed page: ' . $slug );
			continue;
		}
		xva_migration_page( $record );
	}

	flush_rewrite_rules( false );
	update_option( 'xva_source_migration_last_run', current_time( 'mysql' ) );
	update_option( 'xva_source_migration_source', XVA_MIGRATION_SOURCE );
	update_option( 'xva_source_migration_report', $xva_migration_report );

	WP_CLI::success(
		sprintf(
			'XVA source migration complete: %d pages, %d artists, %d exhibitions, %d media imported, %d media errors.',
			$xva_migration_report['pages'],
			$xva_migration_report['artists'],
			$xva_migration_report['exhibitions'],
			$xva_migration_report['media'],
			$xva_migration_report['media_errors']
		)
	);
}

xva_migration_run();
