<?php
/**
 * Mockup-faithful front-end compositions for Elementor page imports.
 *
 * The page exports intentionally use one shortcode widget per page. This
 * keeps the imported Elementor documents portable while the content inside
 * the composition remains driven by WordPress, ACF and the XVA post types.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xva_core_mockup_page_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'page' => 'home' ), $atts, 'xva_mockup_page' );
	$page = sanitize_key( $atts['page'] );

	ob_start();
	switch ( $page ) {
		case 'home':
			xva_core_mockup_home();
			break;
		case 'artists':
			xva_core_mockup_artists();
			break;
		case 'exhibitions':
			xva_core_mockup_exhibitions();
			break;
		case 'press':
			xva_core_mockup_press();
			break;
		case 'contact':
			xva_core_mockup_contact();
			break;
		case 'art-consultancy':
			xva_core_mockup_consultancy();
			break;
		case 'hotel-cafe':
			xva_core_mockup_hotel();
			break;
		case 'story-of-art':
			xva_core_mockup_story();
			break;
		case 'path-to-modernism':
			xva_core_mockup_workshop();
			break;
		case 'privacy-policy':
			xva_core_mockup_privacy();
			break;
		default:
			xva_core_mockup_home();
	}
	return ob_get_clean();
}
add_shortcode( 'xva_mockup_page', 'xva_core_mockup_page_shortcode' );

function xva_core_mockup_theme_image( $file, $alt = '', $class = '' ) {
	$url = get_template_directory_uri() . '/assets/images/' . rawurlencode( $file );
	return '<img class="' . esc_attr( $class ) . '" src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async">';
}

function xva_core_mockup_page_hero( $eyebrow, $title, $meta, $night = false ) {
	$classes = 'xva-page-hero' . ( $night ? ' xva-page-hero--night' : '' );
	$kicker  = $night ? 'xva-kicker xva-kicker--light' : 'xva-kicker';
	?>
	<section class="<?php echo esc_attr( $classes ); ?>">
		<div class="xva-shell">
			<div class="xva-page-hero__top">
				<div>
					<span class="<?php echo esc_attr( $kicker ); ?>"><?php echo esc_html( $eyebrow ); ?></span>
					<h1 class="xva-serif"><?php echo esc_html( $title ); ?></h1>
				</div>
				<p class="xva-page-hero__meta"><?php echo esc_html( $meta ); ?></p>
			</div>
		</div>
	</section>
	<?php
}

function xva_core_mockup_button( $label, $url, $dark = false, $icon = 'external' ) {
	$class = 'xva-button-link' . ( $dark ? ' xva-button-link--light' : '' );
	printf(
		'<a class="%1$s" href="%2$s">%3$s %4$s</a>',
		esc_attr( $class ),
		esc_url( $url ),
		esc_html( $label ),
		xva_core_inline_icon( $icon ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

function xva_core_mockup_field_html( $field, $post_id, $fallback = '' ) {
	$value = xva_core_get_field( $field, $post_id, $fallback );
	return wp_kses_post( (string) $value );
}

function xva_core_mockup_artist_strip_bio_prefix( $value ) {
	$value = (string) $value;
	return preg_replace( '/^(\s*(?:<[^>]+>\s*)*)bio\b(?:\s*[:\-–—]\s*|\s+|&nbsp;|&#160;)*/iu', '$1', $value, 1 );
}

function xva_core_mockup_dom_has_class( $node, $class ) {
	if ( ! is_object( $node ) || ! method_exists( $node, 'getAttribute' ) ) {
		return false;
	}
	$classes = ' ' . preg_replace( '/\s+/', ' ', trim( (string) $node->getAttribute( 'class' ) ) ) . ' ';
	return false !== strpos( $classes, ' ' . $class . ' ' );
}

function xva_core_mockup_dom_inner_html( $node ) {
	if ( ! is_object( $node ) || ! isset( $node->ownerDocument ) ) {
		return '';
	}
	$html = '';
	foreach ( $node->childNodes as $child ) {
		$html .= $node->ownerDocument->saveHTML( $child );
	}
	return $html;
}

function xva_core_mockup_artist_clean_html( $html ) {
	$html = xva_core_mockup_artist_strip_bio_prefix( wp_kses_post( (string) $html ) );
	$html = preg_replace( '/<p[^>]*>\s*(?:(?:&nbsp;|&#160;|\xC2\xA0|<br\s*\/?\s*>|\s)*)<\/p>/i', '', $html );
	$html = preg_replace( '/(?:\s*<br\s*\/?\s*>\s*){3,}/i', '<br>', $html );
	$plain = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( html_entity_decode( $html, ENT_QUOTES, 'UTF-8' ) ) ) );
	if ( ! $plain && false === stripos( $html, '<img' ) ) {
		return '';
	}
	return trim( $html );
}

function xva_core_mockup_artist_section_bucket( $label ) {
	$normalized = strtolower( trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( (string) $label ), ENT_QUOTES, 'UTF-8' ) ) ) );
	if ( preg_match( '/^(?:bio|biography)$/', $normalized ) || preg_match( '/^about\s+the\s+artist$/', $normalized ) ) {
		return 'biography';
	}
	if ( preg_match( '/\b(?:exhibitions?|showings?|art\s+fairs?|fairs?|biennales?|participat)/', $normalized ) ) {
		return 'exhibitions';
	}
	return 'about';
}

function xva_core_mockup_artist_sections( $html ) {
	$sections = array(
		'biography'   => array(),
		'exhibitions' => array(),
		'about'       => array(),
	);
	$source = trim( (string) $html );
	if ( ! $source ) {
		return $sections;
	}

	if ( ! class_exists( 'DOMDocument' ) ) {
		$sections['biography'][] = array( 'label' => 'Bio', 'content' => xva_core_mockup_artist_clean_html( $source ) );
		return $sections;
	}

	$previous_errors = libxml_use_internal_errors( true );
	$dom             = new DOMDocument();
	$loaded          = $dom->loadHTML( '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><div id="xva-artist-source">' . $source . '</div></body></html>', LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous_errors );
	if ( ! $loaded ) {
		$sections['biography'][] = array( 'label' => 'Bio', 'content' => xva_core_mockup_artist_clean_html( $source ) );
		return $sections;
	}

	$xpath = new DOMXPath( $dom );
	$root  = $xpath->query( '//*[@id="xva-artist-source"]' )->item( 0 );
	$nodes = $root ? $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " elementor-divider__element ")]', $root ) : false;
	if ( ! $nodes || ! $nodes->length ) {
		$sections['biography'][] = array( 'label' => 'Bio', 'content' => xva_core_mockup_artist_clean_html( $source ) );
		return $sections;
	}

	foreach ( $nodes as $heading ) {
		$label = trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( $heading->textContent ), ENT_QUOTES, 'UTF-8' ) ) );
		if ( ! $label ) {
			continue;
		}

		$inner = null;
		for ( $ancestor = $heading->parentNode; $ancestor instanceof DOMElement; $ancestor = $ancestor->parentNode ) {
			if ( xva_core_mockup_dom_has_class( $ancestor, 'e-con-inner' ) ) {
				$inner = $ancestor;
				break;
			}
		}
		if ( ! $inner ) {
			for ( $ancestor = $heading->parentNode; $ancestor instanceof DOMElement; $ancestor = $ancestor->parentNode ) {
				if ( xva_core_mockup_dom_has_class( $ancestor, 'e-con' ) ) {
					$inner = $ancestor;
					break;
				}
			}
		}

		$content = '';
		if ( $inner ) {
			foreach ( $inner->childNodes as $child ) {
				if ( XML_ELEMENT_NODE !== $child->nodeType || xva_core_mockup_dom_has_class( $child, 'elementor-widget-divider' ) ) {
					continue;
				}
				if ( xva_core_mockup_dom_has_class( $child, 'elementor-widget-text-editor' ) ) {
					$content .= xva_core_mockup_dom_inner_html( $child );
					continue;
				}
				$text_widgets = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " elementor-widget-text-editor ")]', $child );
				if ( $text_widgets ) {
					foreach ( $text_widgets as $text_widget ) {
						$content .= xva_core_mockup_dom_inner_html( $text_widget );
					}
				}
			}
		}

		$content = xva_core_mockup_artist_clean_html( $content );
		if ( ! $content && $inner ) {
			$fallback = '';
			foreach ( $inner->childNodes as $child ) {
				if ( XML_ELEMENT_NODE === $child->nodeType && ! xva_core_mockup_dom_has_class( $child, 'elementor-widget-divider' ) ) {
					$fallback .= $dom->saveHTML( $child );
				}
			}
			$content = xva_core_mockup_artist_clean_html( $fallback );
		}
		if ( ! $content ) {
			continue;
		}

		$bucket            = xva_core_mockup_artist_section_bucket( $label );
		$sections[ $bucket ][] = array(
			'label'   => $label,
			'content' => $content,
		);
	}

	$has_content = false;
	foreach ( $sections as $items ) {
		if ( $items ) {
			$has_content = true;
			break;
		}
	}
	if ( ! $has_content ) {
		$sections['about'][] = array( 'label' => 'Details', 'content' => xva_core_mockup_artist_clean_html( $source ) );
	}
	return $sections;
}

function xva_core_mockup_artist_blocks_html( $blocks ) {
	$output = '';
	foreach ( (array) $blocks as $block ) {
		$content = isset( $block['content'] ) ? xva_core_mockup_artist_clean_html( $block['content'] ) : '';
		if ( ! $content ) {
			continue;
		}
		$label         = isset( $block['label'] ) ? trim( (string) $block['label'] ) : '';
		$label_compare = strtolower( trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( $label ), ENT_QUOTES, 'UTF-8' ) ) ) );
		if ( in_array( $label_compare, array( 'bio', 'biography' ), true ) ) {
			$label = '';
		}
		$output .= '<div class="xva-artist-content-block">';
		if ( $label ) {
			$output .= '<h3>' . esc_html( $label ) . '</h3>';
		}
		$output .= '<div class="xva-artist-content-block__body">' . $content . '</div></div>';
	}
	return $output;
}

function xva_core_mockup_exhibition_description_html( $post_id, $fallback = '' ) {
	$source = xva_core_mockup_field_html( 'curatorial_text', $post_id, $fallback );
	if ( ! $source || ! class_exists( 'DOMDocument' ) ) {
		return $source;
	}

	$previous_errors = libxml_use_internal_errors( true );
	$dom             = new DOMDocument();
	$loaded          = $dom->loadHTML( '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><div id="xva-exhibition-source">' . $source . '</div></body></html>', LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous_errors );
	if ( ! $loaded ) {
		return $source;
	}

	$xpath   = new DOMXPath( $dom );
	$root    = $xpath->query( '//*[@id="xva-exhibition-source"]' )->item( 0 );
	$widgets = $root ? $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " elementor-widget-text-editor ")]', $root ) : false;
	$output  = '';
	if ( $widgets ) {
		foreach ( $widgets as $widget ) {
			if ( xva_core_mockup_dom_has_class( $widget, 'elementor-hidden-desktop' ) || xva_core_mockup_dom_has_class( $widget, 'elementor-hidden-tablet' ) || xva_core_mockup_dom_has_class( $widget, 'elementor-hidden-mobile' ) || xva_core_mockup_dom_has_class( $widget, 'elementor-hidden-mobile_extra' ) ) {
				continue;
			}
			$output .= xva_core_mockup_dom_inner_html( $widget );
		}
	}

	$output = xva_core_mockup_artist_clean_html( $output );
	return $output ? $output : $source;
}

function xva_core_mockup_date( $field, $post_id, $fallback = '' ) {
	$value = xva_core_get_field( $field, $post_id, $fallback );
	if ( ! $value ) {
		return '';
	}
	if ( preg_match( '/^\d{8}$/', (string) $value ) ) {
		$date = DateTime::createFromFormat( 'Ymd', $value );
		return $date ? wp_date( 'j F Y', $date->getTimestamp() ) : $value;
	}
	return (string) $value;
}

function xva_core_mockup_date_from_parts( $day, $month, $year ) {
	$day    = absint( $day );
	$year   = absint( $year );
	$month  = strtolower( trim( (string) $month ) );
	$months = array(
		'january'   => 1,
		'february'  => 2,
		'march'     => 3,
		'april'     => 4,
		'may'       => 5,
		'june'      => 6,
		'july'      => 7,
		'august'    => 8,
		'september' => 9,
		'october'   => 10,
		'november'  => 11,
		'december'  => 12,
	);
	if ( ! $day || ! $year || ! isset( $months[ $month ] ) ) {
		return '';
	}
	$date = DateTime::createFromFormat( '!j-n-Y', $day . '-' . $months[ $month ] . '-' . $year );
	return $date ? wp_date( 'j F Y', $date->getTimestamp() ) : '';
}

function xva_core_mockup_exhibition_date_range_from_text( $text ) {
	$text = html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = str_replace( array( "\xC2\xA0", '–', '—', '−', '‑' ), array( ' ', '-', '-', '-', '-' ), $text );
	$text = trim( preg_replace( '/\s+/', ' ', $text ) );
	if ( ! $text ) {
		return '';
	}

	$month_names = 'January|February|March|April|May|June|July|August|September|October|November|December';
	$year        = '(?:19|20)\d{2}';
	$separator   = '(?:to|through|until|-)';
	$day         = '(\d{1,2})(?:st|nd|rd|th)?';
	$patterns    = array(
		// Month-first ranges where each side carries its own year.
		'/\b(' . $month_names . ')\s+' . $day . '\s*,?\s*(' . $year . ')\s*' . $separator . '\s*(?:(\d{1,2})(?:st|nd|rd|th)?\s+)?(' . $month_names . ')\s*,?\s*(' . $year . ')\b/i',
		// Day-first ranges where each side carries its own year.
		'/\b' . $day . '\s+(' . $month_names . ')\s*,?\s*(' . $year . ')\s*' . $separator . '\s*' . $day . '\s+(' . $month_names . ')\s*,?\s*(' . $year . ')\b/i',
		// A month-first range whose second side starts with the day, e.g. March 17 - 18 April 2018.
		'/\b(' . $month_names . ')\s+' . $day . '\s*' . $separator . '\s*' . $day . '\s+(' . $month_names . ')\s*,?\s*(' . $year . ')\b/i',
		// Month-first ranges with one year at the end, including November 06 - 16, 2019.
		'/\b(' . $month_names . ')\s+' . $day . '\s*' . $separator . '\s*(?:(' . $month_names . ')\s+)?' . $day . '\s*,?\s*(' . $year . ')\b/i',
		// Day-first ranges with one year at the end, including 11 January - 4 March 2018.
		'/\b' . $day . '\s+(' . $month_names . ')\s*' . $separator . '\s*' . $day . '\s+(' . $month_names . ')\s*,?\s*(' . $year . ')\b/i',
	);

	foreach ( $patterns as $index => $pattern ) {
		if ( ! preg_match( $pattern, $text, $match ) ) {
			continue;
		}

		$start = '';
		$end   = '';
		if ( 0 === $index ) {
			$start = xva_core_mockup_date_from_parts( $match[2], $match[1], $match[3] );
			$end   = xva_core_mockup_date_from_parts( $match[4], $match[5], $match[6] );
		} elseif ( 1 === $index ) {
			$start = xva_core_mockup_date_from_parts( $match[1], $match[2], $match[3] );
			$end   = xva_core_mockup_date_from_parts( $match[4], $match[5], $match[6] );
		} elseif ( 2 === $index ) {
			$start = xva_core_mockup_date_from_parts( $match[2], $match[1], $match[5] );
			$end   = xva_core_mockup_date_from_parts( $match[3], $match[4], $match[5] );
		} elseif ( 3 === $index ) {
			$start = xva_core_mockup_date_from_parts( $match[2], $match[1], $match[5] );
			$end   = xva_core_mockup_date_from_parts( $match[4], $match[3] ? $match[3] : $match[1], $match[5] );
		} else {
			$start = xva_core_mockup_date_from_parts( $match[1], $match[2], $match[5] );
			$end   = xva_core_mockup_date_from_parts( $match[4], $match[3], $match[5] );
		}

		if ( $start && $end ) {
			return $start . ' – ' . $end;
		}
	}

	return '';
}

function xva_core_mockup_exhibition_date_range( $post_id, $fallback = '' ) {
	$start = xva_core_mockup_date( 'start_date', $post_id, '' );
	$end   = xva_core_mockup_date( 'end_date', $post_id, '' );
	if ( $start && $end ) {
		return $start . ' – ' . $end;
	}

	$sources = array(
		xva_core_get_field( 'intro', $post_id, '' ),
		xva_core_get_field( 'curatorial_text', $post_id, '' ),
		get_post_field( 'post_excerpt', $post_id ),
		get_post_field( 'post_content', $post_id ),
	);
	foreach ( $sources as $source ) {
		$range = xva_core_mockup_exhibition_date_range_from_text( $source );
		if ( $range ) {
			return $range;
		}
	}

	$overrides = array(
		'white'   => '25 April 2026 – 21 May 2026',
		'fractal' => '7 February 2026 – 5 March 2026',
	);
	$slug = get_post_field( 'post_name', $post_id );
	if ( isset( $overrides[ $slug ] ) ) {
		return $overrides[ $slug ];
	}
	if ( $start ) {
		return $start;
	}
	if ( $end ) {
		return $end;
	}
	return (string) $fallback;
}

function xva_core_mockup_exhibition_start_date( $post_id, $fallback = '' ) {
	$start = xva_core_mockup_date( 'start_date', $post_id, '' );
	if ( $start ) {
		return $start;
	}
	$range = xva_core_mockup_exhibition_date_range( $post_id, '' );
	if ( $range ) {
		$parts = preg_split( '/\s+–\s+/u', $range, 2 );
		return trim( $parts[0] );
	}
	return (string) $fallback;
}

function xva_core_mockup_artist_image( $post_id, $fallback = 'artist-huma.jpg' ) {
	$value = xva_core_get_field( 'portrait', $post_id );
	$url   = xva_core_image_url( $value, 'xva-portrait' );
	if ( ! $url ) {
		$url = get_the_post_thumbnail_url( $post_id, 'xva-portrait' );
	}
	return $url ? $url : get_template_directory_uri() . '/assets/images/' . rawurlencode( $fallback );
}

function xva_core_mockup_exhibition_gallery_items( $post_id, $size = 'large' ) {
	$native_ids = function_exists( 'xva_core_native_exhibition_gallery_ids' ) ? xva_core_native_exhibition_gallery_ids( $post_id ) : null;
	$gallery    = null !== $native_ids ? $native_ids : xva_core_get_field( 'artwork_gallery', $post_id, array() );
	$items   = array();
	$title   = get_the_title( $post_id );
	foreach ( (array) $gallery as $item ) {
		$url = xva_core_image_url( $item, $size );
		if ( ! $url ) {
			continue;
		}
		$items[] = array(
			'url'     => $url,
			'alt'     => is_array( $item ) && ! empty( $item['alt'] ) ? $item['alt'] : $title . ' exhibition',
			'caption' => is_array( $item ) && ! empty( $item['caption'] ) ? $item['caption'] : '',
		);
	}
	return $items;
}

function xva_core_mockup_exhibition_hero_slides( $post_id, $fallback = 'oblivion.webp' ) {
	$title  = get_the_title( $post_id );
	$date   = xva_core_mockup_exhibition_date_range( $post_id, '' );
	$items  = xva_core_mockup_exhibition_gallery_items( $post_id, 'xva-hero' );
	$slides = array();

	foreach ( $items as $item ) {
		$slides[] = array(
			'image'  => $item['url'],
			'alt'    => ! empty( $item['alt'] ) ? $item['alt'] : $title . ' exhibition artwork',
			'title'  => $title,
			'date'   => $date,
			'source' => 'artwork-gallery',
		);
	}

	if ( ! $slides ) {
		$slides[] = array(
			'image'  => xva_core_mockup_exhibition_poster( $post_id, $fallback, 'large' ),
			'alt'    => $title . ' exhibition poster',
			'title'  => $title,
			'date'   => $date,
			'source' => 'poster-fallback',
		);
	}

	return $slides;
}

function xva_core_mockup_exhibition_image( $post_id, $fallback = 'oblivion.webp', $size = 'xva-card' ) {
	$gallery = xva_core_mockup_exhibition_gallery_items( $post_id, $size );
	$url     = ! empty( $gallery[0]['url'] ) ? $gallery[0]['url'] : '';
	if ( ! $url ) {
		$url = get_the_post_thumbnail_url( $post_id, $size );
	}
	if ( ! $url ) {
		$value = xva_core_get_field( 'hero_image', $post_id );
		$url   = xva_core_image_url( $value, $size );
	}
	return $url ? $url : get_template_directory_uri() . '/assets/images/' . rawurlencode( $fallback );
}

function xva_core_mockup_exhibition_poster( $post_id, $fallback = 'oblivion.webp', $size = 'large' ) {
	$value = xva_core_get_field( 'hero_image', $post_id );
	$url   = xva_core_image_url( $value, $size );
	if ( ! $url ) {
		$url = get_the_post_thumbnail_url( $post_id, $size );
	}
	if ( ! $url ) {
		$url = xva_core_mockup_exhibition_image( $post_id, $fallback, $size );
	}
	return $url ? $url : get_template_directory_uri() . '/assets/images/' . rawurlencode( $fallback );
}

function xva_core_mockup_post_excerpt( $post_id, $fallback = '', $limit = 0 ) {
	$excerpt = wp_strip_all_tags( get_the_excerpt( $post_id ) );
	if ( $excerpt ) {
		$text = trim( preg_replace( '/\s+/', ' ', $excerpt ) );
	} else {
		$intro = xva_core_get_field( 'intro', $post_id, $fallback );
		$text  = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $intro ) ) );
	}
	return $limit > 0 ? trim( wp_html_excerpt( $text, $limit, '…' ) ) : $text;
}

function xva_core_mockup_home() {
	$exhibitions = array();
	foreach ( array( 'after-the-image', 'local', 'white', 'fractal', 'the-truth', 'half-light' ) as $slug ) {
		$post = get_page_by_path( $slug, OBJECT, 'xva_exhibition' );
		if ( $post instanceof WP_Post && 'publish' === $post->post_status ) {
			$exhibitions[] = $post;
		}
	}
	$current_post    = xva_core_mockup_current_exhibition_post();
	$current_id      = $current_post ? $current_post->ID : 0;
	$current_title   = $current_post ? get_the_title( $current_id ) : '';
	$current_image   = $current_post ? xva_core_mockup_exhibition_poster( $current_id, 'oblivion.webp', 'large' ) : '';
	$current_date    = $current_post ? xva_core_mockup_exhibition_date_range( $current_id, '' ) : '';
	$current_venue   = $current_post ? xva_core_get_field( 'venue', $current_id, 'XVA Gallery' ) : '';
	$current_excerpt = $current_post ? xva_core_mockup_post_excerpt( $current_id, '', 220 ) : '';
	$current_url     = $current_post ? get_permalink( $current_id ) : '';
	$slides          = $current_post ? xva_core_mockup_exhibition_hero_slides( $current_id, 'oblivion.webp' ) : array();
	?>
	<?php if ( $slides ) : ?>
	<section class="xva-hero" data-xva-carousel="true" aria-label="Current XVA exhibition artwork carousel">
		<div class="xva-hero-slides">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<article class="xva-hero-slide<?php echo 0 === $index ? ' is-active' : ''; ?>" data-xva-slide data-xva-slide-source="<?php echo esc_attr( $slide['source'] ); ?>" aria-hidden="<?php echo 0 === $index ? 'false' : 'true'; ?>">
					<img src="<?php echo esc_url( $slide['image'] ); ?>" alt="<?php echo esc_attr( $slide['alt'] ); ?>" loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"<?php echo 0 === $index ? ' fetchpriority="high"' : ''; ?> decoding="async">
					<div class="xva-hero-caption">
						<div class="xva-hero-caption__copy">
							<span class="xva-kicker xva-kicker--light">Now on view / XVA Gallery</span>
							<?php if ( 0 === $index ) : ?>
								<h1 class="xva-serif"><?php echo esc_html( $slide['title'] ); ?></h1>
							<?php else : ?>
								<h2 class="xva-serif"><?php echo esc_html( $slide['title'] ); ?></h2>
							<?php endif; ?>
							<p><?php echo esc_html( $slide['date'] ); ?></p>
						</div>
						<div class="xva-hero-caption__action"><span class="xva-kicker xva-kicker--light">Al Fahidi Neighborhood / Dubai</span></div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php if ( count( $slides ) > 1 ) : ?>
		<div class="xva-hero-controls">
			<div class="xva-carousel-dots" aria-label="Choose featured exhibition">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<button class="xva-carousel-dot<?php echo 0 === $index ? ' is-active' : ''; ?>" type="button" data-xva-carousel-dot aria-label="Show slide <?php echo esc_attr( $index + 1 ); ?>"></button>
				<?php endforeach; ?>
			</div>
			<div class="xva-carousel-buttons">
				<button class="xva-carousel-button" type="button" data-xva-carousel-prev aria-label="Previous slide"><?php echo xva_core_inline_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
				<button class="xva-carousel-button" type="button" data-xva-carousel-next aria-label="Next slide"><?php echo xva_core_inline_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			</div>
		</div>
		<?php endif; ?>
	</section>
	<?php endif; ?>

	<section class="xva-section xva-home-programme" aria-labelledby="programme-title">
		<div class="xva-shell">
			<div class="xva-home-intro">
				<div><span class="xva-kicker">The programme</span><h2 id="programme-title" class="xva-serif">Contemporary Art Beyond Borders</h2></div>
				<p>Established in 2003, XVA Gallery presents the work of the region’s foremost artists alongside emerging voices, from its home in Dubai’s heritage district.</p>
			</div>
		</div>
	</section>

	<?php if ( $current_post ) : ?>
	<section class="xva-section xva-section--dark" aria-labelledby="current-exhibition-title">
		<div class="xva-shell">
			<div class="xva-split-story">
				<div class="xva-split-story__media"><img src="<?php echo esc_url( $current_image ); ?>" alt="<?php echo esc_attr( $current_title . ' exhibition at XVA Gallery' ); ?>" loading="lazy" decoding="async"></div>
				<div class="xva-split-story__copy">
					<span class="xva-kicker xva-kicker--light">Current exhibition / XVA Gallery</span>
					<h2 id="current-exhibition-title" class="xva-serif"><?php echo esc_html( $current_title ); ?></h2>
					<div class="xva-card__meta"><span><?php echo esc_html( $current_date ); ?></span><span><?php echo esc_html( $current_venue ); ?></span></div>
					<p><?php echo esc_html( $current_excerpt ); ?></p>
					<?php xva_core_mockup_button( 'View exhibition', $current_url, true ); ?>
				</div>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<section class="xva-section xva-home-exhibitions" aria-labelledby="home-exhibitions-title">
		<div class="xva-shell">
			<div class="xva-section-header xva-home-exhibitions-header">
				<div><span class="xva-eyebrow">Current / upcoming / archive</span><h2 id="home-exhibitions-title" class="xva-serif">Exhibitions</h2></div>
				<?php xva_core_mockup_button( 'View all', home_url( '/exhibitions/' ) ); ?>
			</div>
			<?php xva_core_mockup_exhibition_grid( $exhibitions ); ?>
		</div>
	</section>

	<section class="xva-cafe-feature xva-section" aria-labelledby="homepage-cafe-title">
		<div class="xva-shell">
			<div class="xva-cafe-feature__layout">
				<div class="xva-cafe-feature__media"><?php echo xva_core_mockup_theme_image( 'cafe-2.png', 'XVA Café courtyard', 'xva-cafe-feature__image' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<div class="xva-cafe-feature__copy">
					<h2 id="homepage-cafe-title">XVA Café</h2>
					<p>Offering a range of Middle Eastern inspired vegetarian options served in any of the three shaded courtyards.</p>
					<p>Since XVA Café opened it has been hailed as “Dubai’s best kept secret”, serving freshly prepared vegetarian food. Be transported back to a simpler time with a signature mint lemonade in the traditional courtyard, serenaded by the rustle of leaves and light chirping of birds. XVA Café is open from 7 am to 10 pm everyday.</p>
					<?php xva_core_mockup_button( 'Visit XVA Hotel website', 'https://www.xvahotel.com/' ); ?>
				</div>
			</div>
		</div>
	</section>

	<section class="xva-newsletter" aria-labelledby="newsletter-title">
		<div class="xva-shell xva-newsletter__inner">
			<div><span class="xva-kicker">XVA Journal</span><h2 id="newsletter-title" class="xva-serif">Hear about the latest news and events</h2></div>
			<form class="xva-newsletter-form" data-xva-newsletter-form><input type="email" name="email" autocomplete="email" placeholder="Email" aria-label="Email" required><button type="submit">Join our Newsletter <?php echo xva_core_inline_icon( 'external' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button></form>
		</div>
	</section>
	<?php
}

function xva_core_mockup_exhibition_grid( $posts ) {
	if ( ! $posts ) {
		return;
	}
	?>
	<div class="xva-exhibition-grid">
		<?php foreach ( $posts as $post ) : setup_postdata( $post ); ?>
			<?php xva_core_mockup_exhibition_card( $post ); ?>
		<?php endforeach; wp_reset_postdata(); ?>
	</div>
	<?php
}

function xva_core_mockup_exhibition_card( $post ) {
	$post_id = $post->ID;
	$image   = xva_core_mockup_exhibition_poster( $post_id, 'oblivion.webp', 'xva-card' );
	$date    = xva_core_mockup_exhibition_start_date( $post_id, get_the_date( 'Y', $post_id ) );
	$venue   = xva_core_get_field( 'venue', $post_id, 'XVA Gallery' );
	?>
	<article class="xva-card xva-exhibition-card">
		<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<div class="xva-card__media"><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy" decoding="async"></div>
			<div class="xva-card__meta"><span><?php echo esc_html( $date ); ?></span><span><?php echo esc_html( $venue ); ?></span></div>
			<h3><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
			<p><?php echo esc_html( xva_core_mockup_post_excerpt( $post_id, 'Contemporary practices, material memory and shifting states.', 180 ) ); ?></p>
			<span class="xva-card-link">View exhibition <?php echo xva_core_inline_icon( 'external' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</a>
	</article>
	<?php
}

function xva_core_mockup_artists() {
	$artists = get_posts( array( 'post_type' => 'xva_artist', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
	$view    = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'grid';
	$view    = in_array( $view, array( 'grid', 'list' ), true ) ? $view : 'grid';
	$base_url = get_permalink( get_queried_object_id() );
	$list_url = add_query_arg( 'view', 'list', $base_url );
	$grid_url = add_query_arg( 'view', 'grid', $base_url );
	?>
	<section class="xva-artists-reference" aria-labelledby="xva-artists-title">
		<div class="xva-artists-reference__shell">
			<header class="xva-artists-reference__top">
				<h1 id="xva-artists-title">Artists</h1>
				<nav class="xva-artists-reference__controls" aria-label="Artist list layout controls">
					<a class="<?php echo 'list' === $view ? 'is-current' : ''; ?>" href="<?php echo esc_url( $list_url ); ?>"<?php echo 'list' === $view ? ' aria-current="page"' : ''; ?>>List</a>
					<a class="<?php echo 'grid' === $view ? 'is-current' : ''; ?>" href="<?php echo esc_url( $grid_url ); ?>"<?php echo 'grid' === $view ? ' aria-current="page"' : ''; ?>>Thumbnails</a>
				</nav>
			</header>

			<div class="xva-artists-reference__section-label"><span>Gallery artists</span><span aria-hidden="true"></span></div>

			<?php if ( 'list' === $view ) : ?>
				<ul class="xva-artists-reference__list">
					<?php foreach ( $artists as $post ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>"><h2><?php echo esc_html( get_the_title( $post->ID ) ); ?></h2></a></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<ul class="xva-artists-reference__grid">
					<?php foreach ( $artists as $post ) : $post_id = $post->ID; $image = xva_core_mockup_artist_image( $post_id ); $name = get_the_title( $post_id ); ?>
						<li>
							<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
								<span class="xva-artists-reference__image"><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" decoding="async"></span>
								<span class="xva-artists-reference__content"><h2><?php echo esc_html( $name ); ?></h2></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

function xva_core_mockup_artist_grid( $posts ) {
	if ( ! $posts ) {
		return;
	}
	?>
	<div class="xva-artist-grid" data-xva-artist-preview>
		<?php foreach ( $posts as $index => $post ) : $post_id = $post->ID; $image = xva_core_mockup_artist_image( $post_id ); ?>
			<article class="xva-artist-card" data-xva-artist-card data-xva-index="<?php echo esc_attr( $index ); ?>" data-xva-name="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" data-xva-image="<?php echo esc_url( $image ); ?>">
				<div class="xva-artist-card__media"><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy" decoding="async"></div>
				<a class="xva-artist-card__link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
					<span class="xva-artist-card__name"><?php echo esc_html( get_the_title( $post_id ) ); ?></span><span class="xva-artist-card__index"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				</a>
			</article>
		<?php endforeach; ?>
	</div>
	<div class="xva-artist-preview" data-xva-preview-overlay aria-hidden="true">
		<img src="" alt="">
		<div class="xva-artist-preview__grid" aria-hidden="true">
			<?php foreach ( $posts as $index => $post ) : $post_id = $post->ID; ?>
				<div class="xva-artist-preview__item" data-xva-preview-item data-xva-preview-index="<?php echo esc_attr( $index ); ?>"><span class="xva-artist-preview__name"><?php echo esc_html( get_the_title( $post_id ) ); ?></span><span class="xva-artist-preview__index"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span></div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

function xva_core_mockup_current_exhibition_post() {
	$current = get_posts(
		array(
			'post_type'      => 'xva_exhibition',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'xva_exhibition_status',
					'field'    => 'slug',
					'terms'    => 'current',
				),
			),
		)
	);
	if ( $current ) {
		return $current[0];
	}

	$current = get_posts(
		array(
			'post_type'      => 'xva_exhibition',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_key'       => 'status_label',
			'meta_value'     => 'current',
		)
	);

	return $current ? $current[0] : null;
}

function xva_core_mockup_exhibition_timestamp( $field, $post_id ) {
	$value = xva_core_get_field( $field, $post_id, '' );
	if ( is_array( $value ) ) {
		$value = isset( $value['date'] ) ? $value['date'] : ( isset( $value['value'] ) ? $value['value'] : '' );
	}
	$value = trim( (string) $value );
	if ( ! $value ) {
		$range = xva_core_mockup_exhibition_date_range( $post_id, '' );
		if ( $range && preg_match_all( '/\d{1,2}\s+[A-Za-z]+\s+\d{4}/', $range, $matches ) ) {
			$value = 'end_date' === $field && isset( $matches[0][1] ) ? $matches[0][1] : $matches[0][0];
		}
	}
	if ( ! $value ) {
		return 0;
	}
	if ( preg_match( '/^\d{4}$/', $value ) ) {
		return 0;
	}
	if ( preg_match( '/^\d{8}$/', $value ) ) {
		$date = DateTime::createFromFormat( '!Ymd', $value, wp_timezone() );
		return $date ? $date->getTimestamp() : 0;
	}
	try {
		$date = new DateTime( $value, wp_timezone() );
		return $date->getTimestamp();
	} catch ( Exception $exception ) {
		return 0;
	}
}

function xva_core_mockup_exhibition_status( $post_id, $current_id = 0 ) {
	if ( $current_id && (int) $post_id === (int) $current_id ) {
		return 'current';
	}
	if ( has_term( 'current', 'xva_exhibition_status', $post_id ) ) {
		return 'current';
	}
	$status_label = strtolower( trim( (string) xva_core_get_field( 'status_label', $post_id, '' ) ) );
	if ( 'current' === $status_label ) {
		return 'current';
	}

	$start = xva_core_mockup_exhibition_timestamp( 'start_date', $post_id );
	$end   = xva_core_mockup_exhibition_timestamp( 'end_date', $post_id );
	$now   = current_time( 'timestamp' );
	if ( $start && $start > $now ) {
		return 'upcoming';
	}
	if ( 'upcoming' === $status_label && ! $start && ! $end ) {
		return 'upcoming';
	}
	if ( ! $end ) {
		$end = $start;
	}
	if ( ! $end ) {
		$end = get_post_time( 'U', false, $post_id );
	}
	$cutoff = ( new DateTime( 'now', wp_timezone() ) )->modify( '-12 months' )->getTimestamp();
	return $end && $end >= $cutoff ? 'past' : 'archive';
}

function xva_core_mockup_exhibition_archive_card( $post, $current_id = 0 ) {
	$post_id = $post->ID;
	$status  = xva_core_mockup_exhibition_status( $post_id, $current_id );
	$image   = xva_core_mockup_exhibition_poster( $post_id, 'oblivion.webp', 'xva-card' );
	?>
	<article class="xva-archive-card" data-xva-filter-card="<?php echo esc_attr( $status ); ?>">
		<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<div class="xva-archive-card__media"><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy" decoding="async"></div>
			<div class="xva-archive-card__meta">
				<span><?php echo esc_html( xva_core_get_field( 'curator', $post_id, 'XVA Gallery' ) ); ?></span>
				<span><?php echo esc_html( xva_core_mockup_exhibition_start_date( $post_id, get_the_date( 'Y', $post_id ) ) ); ?></span>
				<time datetime="<?php echo esc_attr( get_the_date( 'c', $post_id ) ); ?>">Posted <?php echo esc_html( get_the_date( 'j F Y', $post_id ) ); ?></time>
			</div>
			<h3><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
		</a>
	</article>
	<?php
}

function xva_core_mockup_exhibitions() {
	$current_post = xva_core_mockup_current_exhibition_post();
	$current_id   = $current_post ? (int) $current_post->ID : 0;
	$filter       = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : 'current';
	$filter       = in_array( $filter, array( 'current', 'past', 'archive' ), true ) ? $filter : 'current';
	?>
	<section class="xva-page-hero">
		<div class="xva-shell">
			<div class="xva-page-hero__top">
				<div>
					<span class="xva-kicker">Programme / XVA Gallery</span>
					<h1 class="xva-serif">Exhibitions</h1>
				</div>
				<div class="xva-page-hero__meta xva-exhibition-page-filter">
					<nav class="xva-exhibition-filter" data-xva-filter-group data-xva-filter-default="<?php echo esc_attr( $filter ); ?>" aria-label="Exhibition categories">
						<button class="<?php echo 'current' === $filter ? 'is-active' : ''; ?>" type="button" data-xva-filter="current">Current</button>
						<button class="<?php echo 'past' === $filter ? 'is-active' : ''; ?>" type="button" data-xva-filter="past">Past</button>
						<button class="<?php echo 'archive' === $filter ? 'is-active' : ''; ?>" type="button" data-xva-filter="archive">Archive</button>
					</nav>
				</div>
			</div>
		</div>
	</section>
	<section class="xva-section xva-section--tight" data-xva-exhibition-current-view<?php echo 'current' === $filter ? '' : ' hidden'; ?>>
		<div class="xva-shell">
			<?php if ( $current_post ) : $image = xva_core_mockup_exhibition_poster( $current_post->ID ); ?>
			<div class="xva-current-exhibition">
				<div class="xva-current-exhibition__media"><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $current_post->ID ) ); ?>" loading="lazy" decoding="async"></div>
				<div class="xva-current-exhibition__copy"><span class="xva-kicker">Current exhibition</span><h2 class="xva-serif"><?php echo esc_html( get_the_title( $current_post->ID ) ); ?></h2><div class="xva-current-exhibition__details"><span><?php echo esc_html( xva_core_mockup_exhibition_date_range( $current_post->ID, '28 July 2026 – 15 August 2026' ) ); ?></span><span><?php echo esc_html( xva_core_get_field( 'venue', $current_post->ID, 'XVA Gallery / Dubai' ) ); ?></span></div><p><?php echo esc_html( xva_core_mockup_post_excerpt( $current_post->ID, 'To forget is not only to lose. Oblivion is also where the mind makes room; clearing space for what matters now.' ) ); ?></p><?php xva_core_mockup_button( 'View exhibition', get_permalink( $current_post->ID ) ); ?></div>
			</div>
			<?php endif; ?>
		</div>
	</section>
	<section class="xva-section xva-exhibition-listing" data-xva-exhibition-listing<?php echo 'current' === $filter ? ' hidden' : ''; ?>>
		<div class="xva-shell">
			<div class="xva-directory-controls"><div><span class="xva-kicker">Programme</span><h2 class="xva-serif">Selected exhibitions</h2></div></div>
			<?php
			$per_page             = 12;
			$initial_non_current  = $current_post ? $per_page - 1 : $per_page;
			$archive_query        = new WP_Query(
				array(
					'post_type'      => 'xva_exhibition',
					'post_status'    => 'publish',
					'posts_per_page' => $initial_non_current,
					'post__not_in'   => $current_id ? array( $current_id ) : array(),
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);
			$archive = $archive_query->posts;
			if ( $current_post ) {
				array_unshift( $archive, $current_post );
			}
			?>
			<div class="xva-archive-grid" data-xva-exhibition-archive data-xva-next-offset="<?php echo esc_attr( $archive_query->post_count ); ?>" data-xva-total="<?php echo esc_attr( $archive_query->found_posts ); ?>" data-xva-per-page="<?php echo esc_attr( $per_page ); ?>" data-xva-endpoint="<?php echo esc_url( rest_url( 'xva/v1/exhibitions' ) ); ?>">
				<?php foreach ( $archive as $post ) : ?>
					<?php xva_core_mockup_exhibition_archive_card( $post, $current_id ); ?>
				<?php endforeach; ?>
			</div>
			<?php if ( $archive_query->found_posts > $archive_query->post_count ) : ?>
				<div class="xva-archive-loader" data-xva-exhibition-loader aria-live="polite">
					<button class="xva-button-link" type="button" data-xva-exhibition-load>Load more exhibitions</button>
					<span data-xva-exhibition-status></span>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

function xva_core_mockup_press() {
	xva_core_mockup_page_hero( 'Press / XVA Gallery', 'Press Releases', 'Current announcements and archive' );
	$items = array(
		array( 'title' => 'Oblivion', 'image' => 'oblivion.webp', 'status' => 'Current exhibition', 'date' => '28 July - 15 August 2026', 'summary' => 'Join us for Oblivion, our summer exhibition, running from 28 July to 15 August 2026.' ),
		array( 'title' => 'After the Image', 'image' => 'ex-after-image.jpg', 'status' => 'Upcoming', 'date' => '18 June - 16 July 2026', 'summary' => 'Join us from 18 June to 16 July 2026 for Charlie Koolhaas’ After the Image.' ),
		array( 'title' => 'LOCAL', 'image' => 'ex-local.jpeg', 'status' => 'Upcoming', 'date' => '23 May - 18 June 2026', 'summary' => 'Jane Beveridge’s first solo show captures the essence of Dubai, and belonging.' ),
		array( 'title' => 'White', 'image' => 'ex-white.jpg', 'status' => 'Exhibition', 'date' => '25 April - 21 May 2026', 'summary' => 'XVA Gallery Dubai presents the Academy of Visual Arts annual faculty exhibition.' ),
		array( 'title' => 'Fractal', 'image' => 'ex-fractal.jpg', 'status' => 'Archive', 'date' => '7 February - 5 March 2026', 'summary' => 'Fractals are infinitely complex patterns that repeat across different scales.' ),
		array( 'title' => 'The Truth', 'image' => 'ex-truth.jpg', 'status' => 'Archive', 'date' => '10 January - 5 February 2026', 'summary' => 'Coming up at XVA Gallery: The Truth, by Hussein Al Mohasen.' ),
	);
	?>
	<section class="xva-section xva-section--tight">
		<div class="xva-shell">
			<div class="xva-press-layout">
				<aside class="xva-press-contact"><span class="xva-kicker xva-kicker--light">Press contact</span><h2 class="xva-serif">MONA HAUSER</h2><p>For press enquiries and image requests.</p><a href="mailto:xva@xvagallery.com">xva@xvagallery.com</a></aside>
				<div class="xva-release-list">
					<?php foreach ( $items as $index => $item ) : ?>
						<article class="xva-release"><div class="xva-release__media"><?php echo xva_core_mockup_theme_image( $item['image'], $item['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div class="xva-release__body"><div class="xva-release__heading"><span class="xva-release-index"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><h2><?php echo esc_html( $item['title'] ); ?></h2><button class="xva-release-toggle" type="button" aria-expanded="false">+</button></div><div class="xva-release-meta"><span><?php echo esc_html( $item['status'] ); ?></span><span>XVA Gallery / Dubai</span><span><?php echo esc_html( $item['date'] ); ?></span></div><p class="xva-release-summary"><?php echo esc_html( $item['summary'] ); ?></p><div class="xva-release-details"><p><?php echo esc_html( $item['summary'] ); ?> Contact the gallery for the full press release and approved image assets.</p></div></div></article>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
	<?php
}

function xva_core_mockup_contact() {
	xva_core_mockup_page_hero( 'Visit / XVA Gallery', 'Contact', 'Al Fahidi Neighborhood / Dubai' );
	?>
	<section class="xva-contact-reference">
		<div class="xva-shell">
			<nav class="xva-contact-subnav" aria-label="Contact sections">
				<a href="<?php echo esc_url( home_url( '/art-consultancy/' ) ); ?>">About XVA</a>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" aria-current="page">Contact</a>
			</nav>

			<div class="xva-contact-reference__map" aria-label="Google map showing the location of XVA Gallery">
				<iframe
					title="Google map showing XVA Gallery"
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					src="https://www.google.com/maps?q=XVA+Gallery+Al+Fahidi+Dubai&output=embed"></iframe>
				<a class="xva-contact-map-link" href="https://maps.app.goo.gl/bLNhJxKtoZW1jk8F9" target="_blank" rel="noopener">
					View on Google Maps <?php echo xva_core_inline_icon( 'external' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			</div>

			<div class="xva-contact-reference__details">
				<div class="xva-contact-detail-block">
					<span class="xva-kicker">Address</span>
					<p>Al Fahidi Historical Neighbourhood<br>Dubai, United Arab Emirates</p>
					<p><a href="tel:+97143533462">T. +971 4 353 3462</a></p>
					<p><strong>For general enquiries please email:</strong><br><a href="mailto:xva@xvagallery.com">xva@xvagallery.com</a></p>
					<p><strong>Art enquiries:</strong><br><a href="mailto:xva@xvagallery.com">xva@xvagallery.com</a></p>
				</div>
				<div class="xva-contact-detail-block xva-contact-detail-block--hours">
					<span class="xva-kicker">Opening hours</span>
					<p>Monday – Sunday<br>10:00 – 18:00</p>
					<div class="xva-contact-actions">
						<a href="mailto:xva@xvagallery.com">Send an email <?php echo xva_core_inline_icon( 'external' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
						<a href="https://maps.app.goo.gl/bLNhJxKtoZW1jk8F9" target="_blank" rel="noopener">View on Google Maps <?php echo xva_core_inline_icon( 'external' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php
}

function xva_core_mockup_consultancy() {
	xva_core_mockup_page_hero( 'Services / XVA Gallery', 'Art Consultancy', 'Expertise and project management' );
	?>
	<section class="xva-section xva-section--tight"><div class="xva-shell"><div class="xva-mission-grid"><div><span class="xva-kicker">Our mission statement</span><h2 class="xva-serif">A gallery that moves with the work.</h2></div><div class="xva-mission-copy"><p>Established in 2003, XVA is one of the leading galleries in the Middle East that specializes in contemporary art from the Arab world and the Sub-continent.</p><p>Exhibitions focus on works by the region’s foremost artists as well as those emerging onto the scene. The gallery’s artists express their different cultural identities and perspectives.</p><p>XVA Gallery exhibits locally and internationally, collaborating with galleries and participating in art fairs in order to further expose Middle Eastern contemporary art.</p></div></div><div class="xva-service-list"><div class="xva-service-row"><span class="xva-service-row__number">01</span><h2>Curatorial advice</h2><p>Expert curatorial advice for site-specific commissions and art rotations for rented or loaned work.</p></div><div class="xva-service-row"><span class="xva-service-row__number">02</span><h2>Exhibition planning</h2><p>The gallery specializes in exhibition installation and planning, art acquisition and collection management.</p></div><div class="xva-service-row"><span class="xva-service-row__number">03</span><h2>Art handling logistics</h2><p>Coordination of art handling and transportation logistics, tailored to projects of different proportions.</p></div></div></div></section>
	<section class="xva-section xva-section--dark"><div class="xva-shell"><div class="xva-section-header"><div><span class="xva-kicker xva-kicker--light">Start a conversation</span><h2 class="xva-serif">Bring the right work into the room.</h2></div><?php xva_core_mockup_button( 'Contact the gallery', home_url( '/contact/' ), true ); ?></div></div></section>
	<?php
}

function xva_core_mockup_hotel() {
	xva_core_mockup_page_hero( 'Al Fahidi / XVA Gallery', 'Hotel & Café', 'A stay, a meal, a courtyard pause' );
	?>
	<section class="xva-section xva-section--tight"><div class="xva-shell"><div class="xva-hospitality-grid"><div class="xva-hospitality-copy"><span class="xva-kicker">XVA Hotel</span><h2 class="xva-serif">A retreat in the maze-like sikkas of Al Fahidi.</h2><p>The Al Fahidi Neighborhood, formerly known as Al Bastakiya, is Dubai’s oldest area and a former prominent trader’s settlement, near the Dubai Fort. Hidden in the heart of the historic district, the hotel extends the gallery experience into a slower rhythm.</p></div><div class="xva-hospitality-media"><?php echo xva_core_mockup_theme_image( 'hotel.jpeg', 'XVA Hotel courtyard' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div></div></section>
	<section class="xva-section"><div class="xva-shell"><div class="xva-split-story"><div class="xva-split-story__media"><?php echo xva_core_mockup_theme_image( 'cafe.jpg', 'XVA Café courtyard' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div class="xva-split-story__copy"><span class="xva-kicker">XVA Hotel</span><h2 class="xva-serif">Art, culture, heritage and creativity.</h2><p>Offering 15 individually designed suites and rooms, an award-winning restaurant, wind towers and three courtyards, this centrally located hotspot is the city’s unique retreat.</p><p>XVA Café is Dubai’s best kept secret, serving freshly prepared vegetarian and vegan food including main courses, salads, soups and desserts.</p><p>Open daily from 7 am to 10 pm.</p><?php xva_core_mockup_button( 'Visit XVA Hotel', 'https://www.xvahotel.com/' ); ?></div></div></div></section>
	<?php
}

function xva_core_mockup_story() {
	xva_core_mockup_page_hero( 'Education / XVA Gallery', 'The Story of Art', 'In-person lectures' );
	?>
	<section class="xva-section xva-section--tight"><div class="xva-shell"><div class="xva-program-intro"><div><span class="xva-kicker">In-person lectures</span><h2 class="xva-serif">The Story of Art</h2></div><p>A series of art history lectures headed by Dr. Michaela Watrelot exploring the highlights of different art movements, the most influential artists and their masterpieces.</p></div><div class="xva-program-list"><div class="xva-program-row"><span>Lecture 01</span><h2>The Age of Revolution - Classicism to Realism</h2><p>The times leading to the 1789 French Revolution provided artists with enormous impulse. Follow Delacroix, Goya, Turner, Constable and Courbet.</p></div><div class="xva-program-row"><span>Lecture 02</span><h2>The power of Light - Impressionism</h2><p>Explore the new treatment of colour and movement developed by Manet, Monet, Pissarro, Renoir and Degas as the Impressionists followed the light.</p></div><div class="xva-program-row"><span>Lecture 03</span><h2>On the path to Modern Art - Post Impressionism</h2><p>Meet Seurat, Cézanne, Gauguin and Van Gogh, whose work marked a turning point in the relationship between colour, form, perspective and emotion.</p></div></div></div></section>
	<section class="xva-section xva-section--dark"><div class="xva-shell"><div class="xva-split-story"><div class="xva-split-story__media"><?php echo xva_core_mockup_theme_image( 'hero-04.webp', 'Art lecture at XVA Gallery' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div class="xva-split-story__copy"><span class="xva-kicker xva-kicker--light">Join the conversation</span><h2 class="xva-serif">A room for looking, listening and asking better questions.</h2><p>The live site exposes this as an education page. The redesigned template keeps the lecture series discoverable and gives each programme a clear contact path.</p><?php xva_core_mockup_button( 'Ask about lectures', home_url( '/contact/' ), true ); ?></div></div></div></section>
	<?php
}

function xva_core_mockup_workshop() {
	xva_core_mockup_page_hero( 'Workshop / XVA Gallery', 'Path to Modernism', 'Impressionism workshop at XVA Gallery' );
	?>
	<section class="xva-section xva-section--tight"><div class="xva-shell"><div class="xva-program-intro"><div><span class="xva-kicker">Impressionism workshop at XVA Gallery</span><h2 class="xva-serif">Path to Modernism</h2></div><p>Join us at XVA Gallery for an intensive art day with the Impressionists. Explore the movements and works by Manet, Monet, Pissarro, Renoir and Degas.</p></div><div class="xva-split-story xva-split-story--reverse"><div class="xva-split-story__media"><?php echo xva_core_mockup_theme_image( 'ex-fractal.jpg', 'Impressionism workshop at XVA Gallery' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><div class="xva-split-story__copy"><span class="xva-kicker">Saturday 17th December</span><h2 class="xva-serif">A day with the Impressionists.</h2><p>10:00 - 11:15 / Manet and the forming of the movement<br>11:15 - 11:30 / Coffee break<br>11:30 - 13:00 / Impressionism, late works<br>13:00 - 14:00 / Lunch and art discussion</p><?php xva_core_mockup_button( 'Leave us a message', home_url( '/contact/' ) ); ?></div></div></div></section>
	<section class="xva-section xva-section--dark"><div class="xva-shell"><div class="xva-section-header"><div><span class="xva-kicker xva-kicker--light">Leave us a message</span><h2 class="xva-serif">Bring your curiosity to the gallery.</h2></div><?php xva_core_mockup_button( 'Contact the gallery', home_url( '/contact/' ), true ); ?></div></div></section>
	<?php
}

function xva_core_mockup_privacy() {
	xva_core_mockup_page_hero( 'Legal / XVA Gallery', 'XVA Gallery’s Privacy Policy', 'Review with the gallery’s legal adviser before production launch', true );
	?>
	<section class="xva-section"><div class="xva-shell xva-legal-content"><p class="xva-legal-lede">At XVA Gallery, we are committed to maintaining the trust and confidence of our customers, visitors to our website and subscribers to our newsletter.</p><h2>Owner and Data Controller</h2><p>XVA Gallery<br>Al Fahidi Neighborhood, Dubai, UAE<br>+971 4 353 3462<br>Owner contact email: <a href="mailto:xva@xvagallery.com">xva@xvagallery.com</a></p><h2>Visitors to our Website</h2><p>When someone visits the gallery website we use standard internet log information and details of visitor behaviour patterns to understand how the site is used and to improve the experience.</p><h2>Newsletter Sign Up</h2><p>We collect personal information when you subscribe to one of our mailing lists, either online or in the gallery. We use that information to keep you informed about exhibitions, events and news.</p><h2>Links to Other Web Sites</h2><p>This privacy notice does not cover links within this site to other websites. Those sites are not governed by this Privacy Notice.</p><h2>Access to Your Personal Information</h2><p>You are entitled to access the personal information that we hold. Email your request to <a href="mailto:xva@xvagallery.com">xva@xvagallery.com</a> and we will send a record of the information we hold on you.</p><h2>Changes to this Privacy Notice</h2><p>We keep our privacy notice under regular review. The production policy should be reviewed with the gallery’s legal adviser before launch.</p><h2>WiFi</h2><p>When you use XVA Gallery Wi-Fi, no data is collected.</p></div></section>
	<?php
}

function xva_core_mockup_single_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'type' => 'artist' ), $atts, 'xva_mockup_single' );
	ob_start();
	if ( 'exhibition' === sanitize_key( $atts['type'] ) ) {
		xva_core_mockup_exhibition_single();
	} else {
		xva_core_mockup_artist_single();
	}
	return ob_get_clean();
}
add_shortcode( 'xva_mockup_single', 'xva_core_mockup_single_shortcode' );

function xva_core_mockup_artist_single() {
	$post_id       = get_the_ID();
	$title         = get_the_title( $post_id );
	$origin        = xva_core_mockup_artist_strip_bio_prefix( xva_core_get_field( 'origin', $post_id, '' ) );
	$birth         = xva_core_get_field( 'birth_information', $post_id, '' );
	$intro         = xva_core_mockup_artist_strip_bio_prefix( xva_core_get_field( 'intro', $post_id, 'Significantly influenced by Sufi ideologies, the work rests as serene and meditative in parallel to disturbing questions of division and social hierarchy.' ) );
	$image         = xva_core_mockup_artist_image( $post_id );
	$bio           = xva_core_mockup_artist_strip_bio_prefix( xva_core_mockup_field_html( 'biography', $post_id, '<p>' . esc_html( $intro ) . '</p><p>Source-derived copy is retained and presented here as a legible long-form reading experience.</p>' ) );
	$statement     = xva_core_mockup_artist_strip_bio_prefix( xva_core_mockup_field_html( 'artist_statement', $post_id, '' ) );
	$artist_sections = xva_core_mockup_artist_sections( $bio );
	$statement_text  = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( html_entity_decode( $statement, ENT_QUOTES, 'UTF-8' ) ) ) );
	if ( $statement_text ) {
		$artist_sections['about'][] = array(
			'label'   => 'Artist statement',
			'content' => $statement,
		);
	}
	$biography_html  = xva_core_mockup_artist_blocks_html( $artist_sections['biography'] );
	$exhibitions_html = xva_core_mockup_artist_blocks_html( $artist_sections['exhibitions'] );
	$about_html      = xva_core_mockup_artist_blocks_html( $artist_sections['about'] );
	$gallery       = xva_core_get_field( 'artwork_gallery', $post_id, array() );
	$cv            = xva_core_get_field( 'cv_download', $post_id, array() );
	$cv_url        = is_array( $cv ) && ! empty( $cv['url'] ) ? $cv['url'] : '';
	$exhibitions   = xva_core_get_field( 'selected_exhibitions', $post_id, array() );
	$press_links   = xva_core_get_field( 'press_links', $post_id, array() );
	$overview_html = wpautop( wp_kses_post( $intro ) );

	if ( preg_match( '/<p[^>]*>(.*?)<\/p>/is', $bio, $overview_match ) && ! empty( trim( wp_strip_all_tags( $overview_match[1] ) ) ) ) {
		$overview_html = '<p>' . wp_kses_post( $overview_match[1] ) . '</p>';
	}

	$artworks = array();
	if ( is_array( $gallery ) ) {
		foreach ( $gallery as $item ) {
			if ( is_numeric( $item ) && ! wp_attachment_is_image( (int) $item ) ) {
				continue;
			}
			if ( is_array( $item ) && ! empty( $item['ID'] ) && ! wp_attachment_is_image( (int) $item['ID'] ) ) {
				continue;
			}
			$url = xva_core_image_url( $item, 'large' );
			if ( ! $url ) {
				continue;
			}
			$artworks[] = array(
				'url'     => $url,
				'alt'     => is_array( $item ) && ! empty( $item['alt'] ) ? $item['alt'] : $title,
				'caption' => is_array( $item ) && ! empty( $item['caption'] ) ? $item['caption'] : $title,
			);
		}
	}

	if ( ! $artworks ) {
		$artworks = array(
			array( 'url' => $image, 'alt' => $title, 'caption' => $title ),
			array( 'url' => get_template_directory_uri() . '/assets/images/huma-02.jpg', 'alt' => $title, 'caption' => $title . ' / Selected work' ),
			array( 'url' => get_template_directory_uri() . '/assets/images/huma-03.jpg', 'alt' => $title, 'caption' => $title . ' / Selected work' ),
			array( 'url' => get_template_directory_uri() . '/assets/images/huma-04.jpg', 'alt' => $title, 'caption' => $title . ' / Selected work' ),
		);
	}

	$nav_items = array(
		'overview' => 'Overview',
		'works'    => 'Works',
	);
	if ( $biography_html ) {
		$nav_items['biography'] = 'Biography';
	}
	if ( $exhibitions_html || ( is_array( $exhibitions ) && $exhibitions ) ) {
		$nav_items['exhibitions'] = 'Exhibitions';
	}
	if ( $about_html ) {
		$nav_items['about'] = 'About';
	}
	$nav_items['cv'] = 'CV';
	if ( is_array( $press_links ) && $press_links ) {
		$nav_items['press'] = 'Press';
	}
	?>
	<section class="xva-artist-page" aria-labelledby="xva-artist-title">
		<div class="xva-artist-heading">
			<div class="xva-shell">
				<div class="xva-artist-heading__inner">
					<h1 id="xva-artist-title"><?php echo esc_html( $title ); ?><?php if ( $birth ) : ?> <span><?php echo esc_html( $birth ); ?></span><?php endif; ?></h1>
					<nav class="xva-artist-nav" aria-label="Artist sections" data-xva-artist-nav>
						<?php foreach ( $nav_items as $anchor => $label ) : ?>
							<a href="#<?php echo esc_attr( $anchor ); ?>"<?php echo 'overview' === $anchor ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $label ); ?></a>
						<?php endforeach; ?>
					</nav>
				</div>
			</div>
		</div>

		<section id="overview" class="xva-artist-overview xva-artist-anchor">
			<div class="xva-shell">
				<div class="xva-artist-overview__layout">
					<div class="xva-artist-overview__copy">
						<?php echo $overview_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php if ( $origin ) : ?><p class="xva-artist-overview__origin"><?php echo esc_html( $origin ); ?></p><?php endif; ?>
						<a class="xva-artist-text-link" href="#works">View works <?php echo xva_core_inline_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
						<?php if ( $cv_url ) : ?><a class="xva-artist-text-link" href="<?php echo esc_url( $cv_url ); ?>" target="_blank" rel="noopener">View CV <?php echo xva_core_inline_icon( 'external' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><?php endif; ?>
					</div>
					<figure class="xva-artist-overview__media">
						<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="eager" decoding="async">
						<figcaption><?php echo esc_html( $title ); ?></figcaption>
					</figure>
				</div>
			</div>
		</section>

		<section id="works" class="xva-artist-section xva-artist-works xva-artist-anchor">
			<div class="xva-shell">
				<div class="xva-artist-section__rule"><h2>Works</h2><span aria-hidden="true"></span></div>
				<div class="xva-artist-works__grid">
					<?php foreach ( $artworks as $artwork ) : ?>
						<figure class="xva-artist-work">
							<img src="<?php echo esc_url( $artwork['url'] ); ?>" alt="<?php echo esc_attr( $artwork['alt'] ); ?>" loading="lazy" decoding="async">
							<figcaption><?php echo esc_html( $artwork['caption'] ); ?></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<?php if ( $biography_html ) : ?>
			<section id="biography" class="xva-artist-section xva-artist-biography xva-artist-anchor">
				<div class="xva-shell">
					<div class="xva-artist-section__rule"><h2>Biography</h2><span aria-hidden="true"></span></div>
					<div class="xva-artist-biography__layout">
						<div class="xva-artist-prose"><?php echo $biography_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $exhibitions_html || ( is_array( $exhibitions ) && $exhibitions ) ) : ?>
			<section id="exhibitions" class="xva-artist-section xva-artist-exhibitions xva-artist-anchor">
				<div class="xva-shell">
					<div class="xva-artist-section__rule"><h2>Exhibitions</h2><span aria-hidden="true"></span></div>
					<?php if ( $exhibitions_html ) : ?><div class="xva-artist-prose"><?php echo $exhibitions_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
					<?php if ( is_array( $exhibitions ) && $exhibitions ) : ?>
						<div class="xva-artist-list">
							<?php foreach ( $exhibitions as $exhibition ) : $exhibition_id = is_object( $exhibition ) ? $exhibition->ID : (int) $exhibition; ?>
								<?php if ( ! $exhibition_id ) : continue; endif; ?>
								<a class="xva-artist-list__item" href="<?php echo esc_url( get_permalink( $exhibition_id ) ); ?>"><span><?php echo esc_html( get_the_title( $exhibition_id ) ); ?></span><span><?php echo esc_html( xva_core_mockup_date( 'start_date', $exhibition_id, 'XVA Gallery' ) ); ?></span><?php echo xva_core_inline_icon( 'external' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $about_html ) : ?>
			<section id="about" class="xva-artist-section xva-artist-about xva-artist-anchor">
				<div class="xva-shell">
					<div class="xva-artist-section__rule"><h2>About <?php echo esc_html( $title ); ?></h2><span aria-hidden="true"></span></div>
					<div class="xva-artist-prose"><?php echo $about_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				</div>
			</section>
		<?php endif; ?>

		<section id="cv" class="xva-artist-section xva-artist-cv xva-artist-anchor">
			<div class="xva-shell">
				<div class="xva-artist-section__rule"><h2>CV</h2><span aria-hidden="true"></span></div>
				<div class="xva-artist-cv__content">
					<p>Curriculum vitae and selected exhibitions for <?php echo esc_html( $title ); ?>.</p>
					<?php if ( $cv_url ) : ?><a class="xva-artist-text-link" href="<?php echo esc_url( $cv_url ); ?>" target="_blank" rel="noopener">View CV (PDF) <?php echo xva_core_inline_icon( 'external' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><?php else : ?><span>Available on request from XVA Gallery.</span><?php endif; ?>
				</div>
			</div>
		</section>
	</section>
	<?php
}

function xva_core_mockup_exhibition_single() {
	$post_id             = get_the_ID();
	$title               = get_the_title( $post_id );
	$current_post        = xva_core_mockup_current_exhibition_post();
	$current_id          = $current_post ? (int) $current_post->ID : 0;
	$detail_filter       = xva_core_mockup_exhibition_status( $post_id, $current_id );
	$exhibitions_url      = home_url( '/exhibitions/' );
	$subtitle            = trim( (string) xva_core_get_field( 'subtitle', $post_id, '' ) );
	$generic_subtitles   = array( 'xva gallery / exhibition', 'xva gallery/exhibition' );
	$subtitle_compare    = strtolower( preg_replace( '/\s+/', ' ', $subtitle ) );
	$participants        = xva_core_get_field( 'participating_artists', $post_id, array() );
	$participant_names    = array();
	foreach ( (array) $participants as $participant ) {
		$participant_id = is_object( $participant ) ? (int) $participant->ID : (int) $participant;
		if ( $participant_id ) {
			$participant_names[] = get_the_title( $participant_id );
		}
	}
	if ( in_array( $subtitle_compare, $generic_subtitles, true ) ) {
		$subtitle = '';
	}
	if ( ! $subtitle && $participant_names ) {
		$subtitle = implode( ', ', $participant_names );
	}
	$intro               = xva_core_get_field( 'intro', $post_id, 'Join us for Oblivion, our summer exhibition, running from 28 July to 15 August 2026.' );
	$start_date          = xva_core_mockup_exhibition_date_range( $post_id, '' );
	$description         = xva_core_mockup_exhibition_description_html( $post_id, '<p>' . esc_html( $intro ) . '</p>' );
	$image               = xva_core_mockup_exhibition_poster( $post_id );
	$gallery_items       = xva_core_mockup_exhibition_gallery_items( $post_id, 'large' );
	$image_data          = xva_core_get_field( 'hero_image', $post_id, array() );
	$image_alt           = is_array( $image_data ) && ! empty( $image_data['alt'] ) ? $image_data['alt'] : $title . ' exhibition';
	$image_caption       = is_array( $image_data ) && ! empty( $image_data['caption'] ) ? $image_data['caption'] : '';
	$press               = xva_core_get_field( 'press_release_pdf', $post_id, array() );
	$press_url           = is_array( $press ) && ! empty( $press['url'] ) ? $press['url'] : '';
	$visit_url           = xva_core_get_field( 'visit_cta_url', $post_id, '' );
	?>
	<section class="xva-exhibition-detail">
		<div class="xva-shell">
			<header class="xva-exhibition-detail__header">
				<div class="xva-exhibition-detail__header-top">
					<div>
						<h1><?php echo esc_html( $title ); ?></h1>
						<div class="xva-exhibition-detail__meta">
							<?php if ( $subtitle ) : ?><span><?php echo esc_html( $subtitle ); ?></span><?php endif; ?>
							<?php if ( $start_date ) : ?><span><?php echo esc_html( $start_date ); ?></span><?php endif; ?>
						</div>
					</div>
					<nav class="xva-exhibition-filter xva-exhibition-detail__filter" aria-label="Exhibition categories">
						<a href="<?php echo esc_url( add_query_arg( 'status', 'current', $exhibitions_url ) ); ?>"<?php echo 'current' === $detail_filter ? ' aria-current="true"' : ''; ?>>Current</a>
						<a href="<?php echo esc_url( add_query_arg( 'status', 'past', $exhibitions_url ) ); ?>"<?php echo 'past' === $detail_filter ? ' aria-current="true"' : ''; ?>>Past</a>
						<a href="<?php echo esc_url( add_query_arg( 'status', 'archive', $exhibitions_url ) ); ?>"<?php echo 'archive' === $detail_filter ? ' aria-current="true"' : ''; ?>>Archive</a>
					</nav>
				</div>
			</header>
			<div class="xva-exhibition-detail__content">
				<div class="xva-exhibition-detail__prose">
					<?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<div class="xva-exhibition-detail__actions">
						<span>Share</span>
						<?php if ( $press_url ) : ?><a href="<?php echo esc_url( $press_url ); ?>" target="_blank" rel="noopener">View press release <?php echo xva_core_inline_icon( 'external' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><?php endif; ?>
						<?php if ( $visit_url ) : ?><a href="<?php echo esc_url( $visit_url ); ?>">Plan a visit <?php echo xva_core_inline_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><?php endif; ?>
					</div>
				</div>
				<figure class="xva-exhibition-detail__media">
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="eager" decoding="async">
					<?php if ( $image_caption ) : ?><figcaption><?php echo esc_html( $image_caption ); ?></figcaption><?php endif; ?>
				</figure>
			</div>
			<?php if ( $gallery_items ) : ?>
			<section class="xva-exhibition-detail__gallery" aria-labelledby="xva-exhibition-gallery-title">
				<div class="xva-section-header">
					<span class="xva-kicker">Artwork / installation gallery</span>
					<h2 id="xva-exhibition-gallery-title" class="xva-serif">The exhibition in view.</h2>
				</div>
				<div class="xva-artwork-gallery">
					<?php foreach ( $gallery_items as $gallery_item ) : ?>
						<figure><img src="<?php echo esc_url( $gallery_item['url'] ); ?>" alt="<?php echo esc_attr( $gallery_item['alt'] ); ?>" loading="lazy" decoding="async"></figure>
					<?php endforeach; ?>
				</div>
			</section>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
