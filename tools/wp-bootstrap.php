<?php
/**
 * XVA Gallery local staging bootstrap.
 * Run with: wp eval-file /var/www/html/wp-content/uploads/xva-tools/wp-bootstrap.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function xva_bootstrap_json( $filename ) {
	$path = WP_CONTENT_DIR . '/uploads/xva-elementor-json/' . $filename;
	if ( ! file_exists( $path ) ) {
		return array();
	}
	$data = json_decode( file_get_contents( $path ), true );
	return is_array( $data ) ? $data : array();
}

function xva_bootstrap_elementor_data( $post_id, $json ) {
	if ( empty( $json['content'] ) ) {
		return;
	}
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $json['content'] ) ) );
}

function xva_bootstrap_mockup_elementor_data( $post_id, $page ) {
	$content = array(
		array(
			'id' => 'xva-mockup-' . sanitize_key( $page ),
			'elType' => 'widget',
			'widgetType' => 'shortcode',
			'settings' => array(
				'shortcode' => '[xva_mockup_page page="' . sanitize_key( $page ) . '"]',
			),
			'elements' => array(),
		),
	);
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );
}

function xva_bootstrap_mockup_single_data( $post_id, $type ) {
	$content = array(
		array(
			'id' => 'xva-mockup-single-' . sanitize_key( $type ),
			'elType' => 'widget',
			'widgetType' => 'shortcode',
			'settings' => array(
				'shortcode' => '[xva_mockup_single type="' . sanitize_key( $type ) . '"]',
			),
			'elements' => array(),
		),
	);
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );
}

function xva_bootstrap_page( $title, $slug, $json_file ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	$postarr = array(
		'post_title' => $title,
		'post_name' => $slug,
		'post_type' => 'page',
		'post_status' => 'publish',
	);
	if ( $page ) {
		$postarr['ID'] = $page->ID;
		$page_id = wp_update_post( $postarr, true );
	} else {
		$page_id = wp_insert_post( $postarr, true );
	}
	if ( is_wp_error( $page_id ) ) {
		return 0;
	}
	$page_json = xva_bootstrap_json( $json_file );
	if ( ! empty( $page_json['content'] ) ) {
		xva_bootstrap_elementor_data( $page_id, $page_json );
	} else {
		xva_bootstrap_mockup_elementor_data( $page_id, $slug );
	}
	return (int) $page_id;
}

function xva_bootstrap_template( $title, $json_file, $template_type ) {
	$existing = get_page_by_title( $title, OBJECT, 'elementor_library' );
	$postarr = array(
		'post_title' => $title,
		'post_type' => 'elementor_library',
		'post_status' => 'publish',
	);
	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$template_id = wp_update_post( $postarr, true );
	} else {
		$template_id = wp_insert_post( $postarr, true );
	}
	if ( is_wp_error( $template_id ) ) {
		return 0;
	}
	update_post_meta( $template_id, '_elementor_template_type', $template_type );
	xva_bootstrap_elementor_data( $template_id, xva_bootstrap_json( $json_file ) );
	return (int) $template_id;
}

function xva_bootstrap_media( $filename, $title ) {
	$source = get_template_directory() . '/assets/images/' . $filename;
	if ( ! file_exists( $source ) ) {
		return 0;
	}
	$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'title' => sanitize_title( $title ), 'posts_per_page' => 1, 'fields' => 'ids' ) );
	if ( $existing ) {
		return (int) $existing[0];
	}
	$tmp = wp_tempnam( $source );
	if ( ! $tmp || ! copy( $source, $tmp ) ) {
		return 0;
	}
	$filetype = wp_check_filetype( $filename );
	$file = array(
		'name' => basename( $filename ),
		'tmp_name' => $tmp,
		'type' => isset( $filetype['type'] ) ? $filetype['type'] : 'image/jpeg',
		'error' => 0,
		'size' => filesize( $tmp ),
	);
	$attachment_id = media_handle_sideload( $file, 0, $title );
	@unlink( $tmp );
	return is_wp_error( $attachment_id ) ? 0 : (int) $attachment_id;
}

function xva_bootstrap_artist( $title, $slug, $image, $origin, $intro ) {
	$existing = get_page_by_path( $slug, OBJECT, 'xva_artist' );
	$postarr = array( 'post_title' => $title, 'post_name' => $slug, 'post_type' => 'xva_artist', 'post_status' => 'publish', 'post_excerpt' => $intro );
	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$post_id = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
	}
	if ( is_wp_error( $post_id ) ) {
		return 0;
	}
	$attachment_id = xva_bootstrap_media( $image, $title );
	if ( $attachment_id ) {
		set_post_thumbnail( $post_id, $attachment_id );
		if ( function_exists( 'update_field' ) ) {
			update_field( 'portrait', $attachment_id, $post_id );
		}
	}
	if ( function_exists( 'update_field' ) ) {
		update_field( 'origin', $origin, $post_id );
		update_field( 'intro', $intro, $post_id );
		update_field( 'biography', '<p>' . esc_html( $intro ) . '</p><p>Replace this staging copy with the approved artist biography during content migration.</p>', $post_id );
		update_field( 'artist_statement', '<p>The artist statement is connected to the ACF-powered Artist Page template.</p>', $post_id );
	}
	wp_set_object_terms( $post_id, $origin, 'xva_artist_region', false );
	xva_bootstrap_mockup_single_data( $post_id, 'artist' );
	return (int) $post_id;
}

function xva_bootstrap_exhibition( $title, $slug, $image, $intro, $status = 'current', $start = '20260120', $end = '20260228' ) {
	$existing = get_page_by_path( $slug, OBJECT, 'xva_exhibition' );
	$postarr = array( 'post_title' => $title, 'post_name' => $slug, 'post_type' => 'xva_exhibition', 'post_status' => 'publish', 'post_excerpt' => $intro );
	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$post_id = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
	}
	if ( is_wp_error( $post_id ) ) {
		return 0;
	}
	$attachment_id = xva_bootstrap_media( $image, $title );
	if ( $attachment_id ) {
		set_post_thumbnail( $post_id, $attachment_id );
		if ( function_exists( 'update_field' ) ) {
			update_field( 'hero_image', $attachment_id, $post_id );
		}
	}
	if ( function_exists( 'update_field' ) ) {
		update_field( 'subtitle', 'Current exhibition at XVA Gallery', $post_id );
		update_field( 'start_date', $start, $post_id );
		update_field( 'end_date', $end, $post_id );
		update_field( 'status_label', $status, $post_id );
		update_field( 'intro', $intro, $post_id );
		update_field( 'venue', 'XVA Gallery', $post_id );
		update_field( 'curatorial_text', '<p>' . esc_html( $intro ) . '</p><p>Replace this staging copy with the approved exhibition curatorial text during migration.</p>', $post_id );
	}
	wp_set_object_terms( $post_id, ucfirst( $status ), 'xva_exhibition_status', false );
	wp_set_object_terms( $post_id, substr( $start, 0, 4 ), 'xva_exhibition_year', false );
	xva_bootstrap_mockup_single_data( $post_id, 'exhibition' );
	return (int) $post_id;
}

// Pages that correspond to the public mockup routes.
$page_ids = array();
$pages = array(
	'Home' => array( 'home', 'homepage.json' ),
	'Artists' => array( 'artists', 'artists-archive.json' ),
	'Exhibitions' => array( 'exhibitions', 'exhibitions-archive.json' ),
	'Art Consultancy' => array( 'art-consultancy', 'art-consultancy.json' ),
	'Press' => array( 'press', 'press.json' ),
	'Contact' => array( 'contact', 'contact.json' ),
	'Hotel & Café' => array( 'hotel-cafe', 'hotel-cafe.json' ),
	'Privacy Policy' => array( 'privacy-policy', 'privacy-policy.json' ),
);
foreach ( $pages as $title => $page ) {
	$page_ids[ $page[0] ] = xva_bootstrap_page( $title, $page[0], $page[1] );
}
if ( ! empty( $page_ids['home'] ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page_ids['home'] );
}

// Shared Elementor site parts and reusable loop templates.
$templates = array(
	'XVA Global Header' => array( 'global-header.json', 'header' ),
	'XVA Global Footer' => array( 'global-footer.json', 'footer' ),
	'XVA Fullscreen Menu' => array( 'popup-menu.json', 'popup' ),
	'XVA Artist Page Template' => array( 'artist-single.json', 'single' ),
	'XVA Exhibition Page Template' => array( 'exhibition-single.json', 'single' ),
	'XVA Artists Archive' => array( 'artists-archive.json', 'archive' ),
	'XVA Exhibitions Archive' => array( 'exhibitions-archive.json', 'archive' ),
	'XVA 404' => array( '404.json', 'error-404' ),
	'XVA Artist Loop Card' => array( 'loop-artist-card.json', 'loop-item' ),
	'XVA Exhibition Loop Card' => array( 'loop-exhibition-card.json', 'loop-item' ),
	'XVA Press Loop Card' => array( 'loop-press-card.json', 'loop-item' ),
);
foreach ( $templates as $title => $template ) {
	xva_bootstrap_template( $title, $template[0], $template[1] );
}

$artist_ids = array();
$artist_seed = array(
	array( 'Akim Monet', 'akim-monet', 'artist-akim.jpg', 'Arab world', 'Akim Monet works across image, memory and the architectures of looking.' ),
	array( 'Al Braithwaite', 'al-braithwaite', 'artist-al-braithwaite.jpg', 'Arab world', 'Al Braithwaite’s practice moves between material, language and lived experience.' ),
	array( 'Alejandra Palos', 'alejandra-palos', 'artist-alejandra.jpg', 'Sub-continent', 'Alejandra Palos investigates the unstable relationship between body, place and image.' ),
	array( 'Azad Nanakeli', 'azad-nanakeli', 'artist-halim.jpg', 'Arab world', 'Azad Nanakeli builds poetic encounters between found material and memory.' ),
	array( 'Barbara Wildenboer', 'barbara-wildenboer', 'artist-charlie.jpg', 'Sub-continent', 'Barbara Wildenboer’s work traces the relationships between natural systems and human narratives.' ),
	array( 'Charlie Koolhaas', 'charlie-koolhaas', 'artist-hussein.jpg', 'Arab world', 'Charlie Koolhaas works through the social life of images, cities and contemporary culture.' ),
	array( 'Halim Al Karim', 'halim-al-karim', 'artist-halim.jpg', 'Arab world', 'Halim Al Karim explores displacement, intimacy and the dreamlike image.' ),
	array( 'Huma Shoaib', 'huma-shoaib', 'artist-huma.jpg', 'Lahore / Pakistan', 'Huma Shoaib’s practice considers the intimate relationship between memory, image and place.' ),
	array( 'Hussein Al-Mohasen', 'hussein-al-mohasen', 'artist-hussein.jpg', 'Arab world', 'Hussein Al-Mohasen brings colour, poetry and social observation into conversation.' ),
	array( 'Mona Saudi', 'mona-saudi', 'artist-mona.jpg', 'Arab world', 'Mona Saudi’s sculptural language gives form to questions of history, body and belonging.' ),
	array( 'Moussa Tiba', 'moussa-tiba', 'artist-moussa.jpg', 'Arab world', 'Moussa Tiba’s paintings hold gesture, colour and the rhythm of everyday life.' ),
	array( 'Naqsh Raj', 'naqsh-raj', 'artist-naqsh.jpg', 'Sub-continent', 'Naqsh Raj works across abstraction, atmosphere and the emotional charge of colour.' ),
);
foreach ( $artist_seed as $artist ) {
	$artist_ids[ $artist[1] ] = xva_bootstrap_artist( $artist[0], $artist[1], $artist[2], $artist[3], $artist[4] );
}
$huma_id = isset( $artist_ids['huma-shoaib'] ) ? $artist_ids['huma-shoaib'] : 0;

$exhibition_ids = array();
$exhibition_seed = array(
	array( 'Oblivion', 'oblivion', 'oblivion.webp', 'Oblivion brings together practices that move between material trace, memory and the unstable image.', 'current', '20260120', '20260228' ),
	array( 'After the Image', 'after-the-image', 'ex-after-image.jpg', 'A study of the image after it has left the screen and entered the room.', 'upcoming', '20260618', '20260716' ),
	array( 'LOCAL', 'local', 'ex-local.jpeg', 'A first solo presentation that captures the essence of Dubai, and belonging.', 'upcoming', '20260523', '20260618' ),
	array( 'White', 'white', 'ex-white.jpg', 'A considered group exhibition of material, form and the white field.', 'past', '20260425', '20260521' ),
	array( 'Fractal', 'fractal', 'ex-fractal.jpg', 'Fractals are infinitely complex patterns that repeat across different scales.', 'past', '20260207', '20260305' ),
	array( 'The Truth', 'the-truth', 'ex-truth.jpg', 'A visual conversation about truth, hope, colour and poetry.', 'past', '20260110', '20260205' ),
	array( 'Half Light', 'half-light', 'ex-half-light.jpg', 'A study in atmosphere, shadow and the quiet space between seeing and remembering.', 'past', '20251114', '20251218' ),
);
foreach ( $exhibition_seed as $exhibition ) {
	$exhibition_ids[ $exhibition[1] ] = xva_bootstrap_exhibition( $exhibition[0], $exhibition[1], $exhibition[2], $exhibition[3], $exhibition[4], $exhibition[5], $exhibition[6] );
}
$oblivion_id = isset( $exhibition_ids['oblivion'] ) ? $exhibition_ids['oblivion'] : 0;

if ( function_exists( 'update_field' ) && $huma_id && $oblivion_id ) {
	update_field( 'participating_artists', array( $huma_id ), $oblivion_id );
	update_field( 'selected_exhibitions', array( $oblivion_id ), $huma_id );
}

update_option( 'blogname', 'XVA Gallery Dev' );
update_option( 'blogdescription', 'Contemporary art from the Arab world and the Sub-continent.' );
update_option( 'permalink_structure', '/%postname%/' );
flush_rewrite_rules();

WP_CLI::success( 'XVA Gallery staging content and Elementor data imported.' );
