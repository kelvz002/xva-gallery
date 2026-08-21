<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xva_core_acf_json_paths( $paths ) {
	$paths[] = XVA_CORE_DIR . 'acf-json';
	return array_unique( $paths );
}
add_filter( 'acf/settings/load_json', 'xva_core_acf_json_paths' );

function xva_core_acf_save_path( $path ) {
	return XVA_CORE_DIR . 'acf-json';
}
add_filter( 'acf/settings/save_json', 'xva_core_acf_save_path' );

function xva_core_add_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}
	acf_add_options_page(
		array(
			'page_title' => __( 'XVA Site Settings', 'xva-gallery-core' ),
			'menu_title' => __( 'XVA Settings', 'xva-gallery-core' ),
			'menu_slug' => 'xva-site-settings',
			'capability' => 'manage_options',
			'redirect' => false,
			'icon_url' => 'dashicons-admin-customizer',
		)
	);
}
add_action( 'acf/init', 'xva_core_add_options_page' );

function xva_core_get_field( $field, $post_id = false, $fallback = '' ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $field, $post_id );
		return false !== $value && null !== $value && '' !== $value ? $value : $fallback;
	}
	return $fallback;
}

function xva_core_artist_media_aliases() {
	static $aliases = null;

	if ( null !== $aliases ) {
		return $aliases;
	}

	$aliases = array(
		'NS-003-imageJarrive-A-from-a-series-of-7-2014-Signed-and-dated-on-reverse-Digital-C-print-color-drawing-media-and-ink-10.5-cm-x-10.5-cm-Framed-30-x-40-cm-1024x1005.jpg' => 'xva-artist-repair-001.jpg',
		'NS-004-image-cover-phoJarrive-G-from-a-series-of-7-2014-Signed-and-dated-on-reverse-Digital-C-print-color-drawing-media-and-ink-10.5-cm-x-10.5-cm-Framed-30-x-40-cm-1024x1019.jpg' => 'xva-artist-repair-002.jpg',
		'NS-005-imageJarrive-E-from-a-series-of-7-2014-Signed-and-dated-on-reverse-Digital-C-print-color-drawing-media-and-ink-10.5-cm-x-10.5-cm-Framed-30-x-40-cm-1024x1019.jpg' => 'xva-artist-repair-003.jpg',
		'NS-007-imageJarrive-F-from-a-series-of-7-2014-Signed-and-dated-on-reverse-Digital-C-print-color-drawing-media-and-ink-10.5-cm-x-10.5-cm-Framed-30-x-40-cm-1024x1024.jpg' => 'xva-artist-repair-004.jpg',
		'NS-007-imageJarrive-F-from-a-series-of-7-2014-Signed-and-dated-on-reverse-Digital-C-print-color-drawing-media-and-ink-10.5-cm-x-10.5-cm-Framed-30-x-40-cm-scaled-1-1024x1024.jpg' => 'xva-artist-repair-005.jpg',
		'SKA003-imageQajar-Woman-2024-Mixed-Media-on-Fabric-70-x-75-cm-scaled-1-950x1024.jpg' => 'xva-artist-repair-006.jpg',
		'SKA010-image-cover-phoUntitled-2024-65-x-65-cm-1016x1024.jpg' => 'xva-artist-repair-007.jpg',
		'SKA014-imageFamilie-2022-Mixed-Media-on-Fabric-166-x-98-cm-615x1024.jpg' => 'xva-artist-repair-008.jpg',
		'SM-1-imageve-Been-Left-in-Your-Room-1-2010-Mixed-media-on-wooden-box-28-x-84-x-15-cm.jpg' => 'xva-artist-repair-009.jpg',
		'SM-10-imageUntitled-2014-Mixed-media-on-transparent-paper-23-x-16-cm-1024x683.jpg' => 'xva-artist-repair-010.jpg',
		'SM-15-imageUntitled-2014-Mixed-media-on-transparent-paper-23-x-16-cm-695x1024.jpg' => 'xva-artist-repair-011.jpg',
		'SM-15-imageUntitled-2014-Mixed-media-on-transparent-paper-23-x-16-cm-scaled-1-695x1024.jpg' => 'xva-artist-repair-012.jpg',
		'SM-6-imageUntitled-2014-Mixed-media-on-transparent-paper-23-x-16-cm-1024x683.jpg' => 'xva-artist-repair-013.jpg',
		'SM-8-image-cover-phoUntitled-2014-Mixed-media-on-transparent-paper-23-x-16-cm-1024x683.jpg' => 'xva-artist-repair-014.jpg',
		'TS-017-imageInterchange-Nr.1-2013-Photography-67-x-77-cms-1024x883.jpg' => 'xva-artist-repair-015.jpg',
		'TS-025-imageRiposte-Attack-on-Mona-Lisa-Leonardo-da-Vinci.-Louvre-Paris-2022-2024-Duratrans-Lightbox-107-x-80-x-6-cm-Lightbox-1-5-1024x767.jpg' => 'xva-artist-repair-016.jpg',
		'TS-027-image-cover-phoStains-Shadow-Attack-on-Clown-Henry-de-Toulouse-Lautrec-Alte-National-Gallery-Berlin-2022-2024-Duratrans-Lightbox-88-x-70-x-6-cm-Lightbox-1-5-1024x793.jpg' => 'xva-artist-repair-017.jpg',
		'TS-028-imagePink-Attack-on-Lhetaire-Pablo-Picasso-Museum-of-Fine-Arts-Montreal-2025-2025-Duratrans-Lightbox-105-x-70-x-6-cm-1-5-1024x670.jpg' => 'xva-artist-repair-018.jpg',
		'TS-030-imageSplash-1-Hahnemuhle-Fine-Art-Baryta-2025-Frame-Glass-85-x-57-cm-other-sizes-on-request-1-7-1024x703.jpg' => 'xva-artist-repair-019.jpg',
		'TS-0361-1024x305.jpg' => 'xva-artist-repair-020.jpg',
		'TS-037-imageBecause-science-tells-us-2025-Hahnemuhle-Fine-Art-Baryta-Enhanced-Matte-Paper-Frame-Glass-70-x-80-cm-1024x980.jpg' => 'xva-artist-repair-021.jpg',
		'TS-039-imageRiposte-2025-Hahnemuhle-Fine-Art-Baryta-Enhanced-Matte-Paper-Frame-Glass-Print-63-x-80-cm-799x1024.jpg' => 'xva-artist-repair-022.jpg',
		'TS-041-imagePink-on-Yellow-Hahnemuhle-Fine-Art-Baryta-2025-Frame-Glass-69-x-78-cm-other-sizes-on-request-2-7-915x1024.jpg' => 'xva-artist-repair-023.jpg',
		'TS-042-imageBlack-Stains-on-Red-Hahnemuhle-Fine-Art-Baryta-2025-Frame-Glass-78-x-70-cm-other-sizes-available-on-request-2-7-983x1024.jpg' => 'xva-artist-repair-024.jpg',
		'YT-007-imageNo.-4-2016-Wood-Cercidiphyllum-Japonicum-and-coloured-pencil-sculpture-and-pencil-on-paper-17-x-3-x-3-cms-26-x-17-cms.jpg' => 'xva-artist-repair-025.jpg',
		'YT-18-imageMultidimensional-Specimen-2016-yes-Plastic-wood-paper-colored-pencil-H215mm-W125mm-D115mm-819x1024.jpg' => 'xva-artist-repair-026.jpg',
		'YT-28-imagefontain-20202-Yes-acryl-gouache-pencil-paper-H350mm-W270mm--817x1024.jpg' => 'xva-artist-repair-027.jpg',
		'YT-29-imagebundle-2020-Yes-acryl-gouachecolored-pencil-wood-iron-H330mm-W30mm-D30mm-576x1024.jpg' => 'xva-artist-repair-028.jpg',
		'YT-31-imagefragmdent-2014-paintcolored-white-pencil-wooden-board-Yes-834x1024.jpg' => 'xva-artist-repair-029.jpg',
		'YT-36-imageragment-2023-Yes-paint-colored-white-pencil-paper-H250mm-W190mm-791x1024.jpg' => 'xva-artist-repair-030.jpg',
		'YT-38-imageoriental-2-2021-Yes-acrylic-paint-paper-pen-H420mm-W120mm-298x1024.jpg' => 'xva-artist-repair-031.jpg',
		'YT-44-imagein-out-Mixed-media-acrylic-paint-crayon-pencil-plastic-120-x-80-cm-686x1024.jpg' => 'xva-artist-repair-032.jpg',
		'YT-44-imagein-out-Mixed-media-acrylic-paint-crayon-pencil-plastic-120-x-80-cm-scaled-1-686x1024.jpg' => 'xva-artist-repair-033.jpg',
		'YT-50-image-cover-phoin-out-2023-Mixed-media-acrylic-paint-crayon-pencil-plastic-60-x-45-cm-776x1024.jpg' => 'xva-artist-repair-034.jpg',
		'YT-50-image-cover-phoin-out-2023-Mixed-media-acrylic-paint-crayon-pencil-plastic-60-x-45-cm-scaled-1-776x1024.jpg' => 'xva-artist-repair-035.jpg',
		'YT-57-imageSpecimen-of-imagination-2023-scaled-1-819x1024.jpg' => 'xva-artist-repair-036.jpg',
		'YT-62-imageComposition-of-Cosmos-2023-Folded-and-painted-waterproof-paper-29-x-37-x-5.5cm-897x1024.jpg' => 'xva-artist-repair-037.jpg',
		'YT-62-imageComposition-of-Cosmos-2023-Folded-and-painted-waterproof-paper-29-x-37-x-5.5cm-scaled-1-896x1024.jpg' => 'xva-artist-repair-038.jpg',
	);

	return $aliases;
}

function xva_core_media_alias_url( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return $url;
	}

	$path = wp_parse_url( $url, PHP_URL_PATH );
	if ( ! is_string( $path ) || '' === $path ) {
		return $url;
	}

	$basename = wp_basename( rawurldecode( $path ) );
	$aliases = xva_core_artist_media_aliases();
	if ( empty( $aliases[ $basename ] ) ) {
		return $url;
	}

	$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
	$alias = $aliases[ $basename ];
	if ( in_array( $site_host, array( 'localhost', '127.0.0.1' ), true ) ) {
		return $url;
	}

	$encoded_basename = rawurlencode( $basename );
	$encoded_alias = rawurlencode( $alias );
	if ( false !== strpos( $url, $encoded_basename ) ) {
		return str_replace( $encoded_basename, $encoded_alias, $url );
	}

	return false !== strpos( $url, $basename ) ? str_replace( $basename, $alias, $url ) : $url;
}

function xva_core_image_url( $value, $size = 'large' ) {
	if ( is_array( $value ) ) {
		$attachment_id = ! empty( $value['ID'] ) ? (int) $value['ID'] : ( ! empty( $value['id'] ) ? (int) $value['id'] : 0 );
		if ( $attachment_id && ! wp_attachment_is_image( $attachment_id ) ) {
			return '';
		}
		if ( ! empty( $value['mime_type'] ) && 0 !== strpos( (string) $value['mime_type'], 'image/' ) ) {
			return '';
		}
		$url = ! empty( $value['sizes'][ $size ] ) ? $value['sizes'][ $size ] : ( isset( $value['url'] ) ? $value['url'] : '' );
		return xva_core_media_alias_url( $url );
	}
	if ( is_numeric( $value ) ) {
		if ( ! wp_attachment_is_image( (int) $value ) ) {
			return '';
		}
		return xva_core_media_alias_url( (string) wp_get_attachment_image_url( (int) $value, $size ) );
	}
	if ( is_string( $value ) ) {
		$attachment_id = attachment_url_to_postid( $value );
		if ( $attachment_id && ! wp_attachment_is_image( $attachment_id ) ) {
			return '';
		}
		return xva_core_media_alias_url( $value );
	}
	return '';
}
