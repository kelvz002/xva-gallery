<?php
/**
 * Apply the local XVA Elementor/ACF templates to imported source records.
 *
 * Run with:
 * wp eval-file /var/www/html/wp-content/uploads/xva-tools/xva-apply-local-templates.php
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) ) {
	exit;
}

function xva_apply_template_shortcode( $post_id, $shortcode ) {
	$data = array(
		array(
			'id'         => 'xva-source-' . (int) $post_id,
			'elType'     => 'widget',
			'widgetType' => 'shortcode',
			'settings'   => array( 'shortcode' => $shortcode ),
			'elements'   => array(),
		),
	);
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
}

$counts = array( 'artists' => 0, 'exhibitions' => 0, 'pages' => 0 );

foreach ( array( 'xva_artist' => 'artist', 'xva_exhibition' => 'exhibition' ) as $post_type => $template_type ) {
	$ids = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_xva_source_url',
		)
	);
	foreach ( $ids as $post_id ) {
		xva_apply_template_shortcode( $post_id, '[xva_mockup_single type="' . $template_type . '"]' );
		$counts[ 'xva_artist' === $post_type ? 'artists' : 'exhibitions' ]++;
	}
}

$page_map = array(
	'home'                                   => 'home',
	'home-new'                               => 'home',
	'home-2'                                 => 'home',
	'artists'                                => 'artists',
	'artists-list'                           => 'artists',
	'exhibitions-new'                        => 'exhibitions',
	'exhibitions-old'                        => 'exhibitions',
	'press-release'                           => 'press',
	'art-consultancy-and-advisory-services' => 'art-consultancy',
	'hotel-cafe'                             => 'hotel-cafe',
	'hotel-cafe-2'                           => 'hotel-cafe',
	'privacy-policy'                         => 'privacy-policy',
);

$page_ids = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => '_xva_source_url',
	)
);
foreach ( $page_ids as $post_id ) {
	$slug = get_post_field( 'post_name', $post_id );
	if ( isset( $page_map[ $slug ] ) ) {
		xva_apply_template_shortcode( $post_id, '[xva_mockup_page page="' . $page_map[ $slug ] . '"]' );
		$counts['pages']++;
	}
}

flush_rewrite_rules( false );
WP_CLI::success( sprintf( 'Local templates applied: %d pages, %d artists, %d exhibitions.', $counts['pages'], $counts['artists'], $counts['exhibitions'] ) );
