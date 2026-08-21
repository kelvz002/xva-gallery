<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xva_core_register_post_types() {
	register_post_type(
		'xva_artist',
		array(
			'labels' => array(
				'name' => __( 'Artists', 'xva-gallery-core' ),
				'singular_name' => __( 'Artist', 'xva-gallery-core' ),
				'add_new_item' => __( 'Add Artist', 'xva-gallery-core' ),
				'edit_item' => __( 'Edit Artist', 'xva-gallery-core' ),
			),
			'public' => true,
			'show_in_rest' => true,
			'menu_icon' => 'dashicons-art',
			// Match the live XVA URL family: /artist/{slug}/.
			'rewrite' => array( 'slug' => 'artist', 'with_front' => false ),
			'has_archive' => false,
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		)
	);

	register_post_type(
		'xva_exhibition',
		array(
			'labels' => array(
				'name' => __( 'Exhibitions', 'xva-gallery-core' ),
				'singular_name' => __( 'Exhibition', 'xva-gallery-core' ),
				'add_new_item' => __( 'Add Exhibition', 'xva-gallery-core' ),
				'edit_item' => __( 'Edit Exhibition', 'xva-gallery-core' ),
			),
			'public' => true,
			'show_in_rest' => true,
			'menu_icon' => 'dashicons-format-gallery',
			'rewrite' => array( 'slug' => 'exhibitions', 'with_front' => false ),
			'has_archive' => false,
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		)
	);

	register_post_type(
		'xva_press',
		array(
			'labels' => array(
				'name' => __( 'Press releases', 'xva-gallery-core' ),
				'singular_name' => __( 'Press release', 'xva-gallery-core' ),
				'add_new_item' => __( 'Add Press Release', 'xva-gallery-core' ),
				'edit_item' => __( 'Edit Press Release', 'xva-gallery-core' ),
			),
			'public' => true,
			'show_in_rest' => true,
			'menu_icon' => 'dashicons-media-document',
			'rewrite' => array( 'slug' => 'press-releases', 'with_front' => false ),
			'has_archive' => false,
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		)
	);
}
add_action( 'init', 'xva_core_register_post_types' );

function xva_core_register_taxonomies() {
	register_taxonomy(
		'xva_artist_region',
		array( 'xva_artist' ),
		array(
			'labels' => array( 'name' => __( 'Regions', 'xva-gallery-core' ), 'singular_name' => __( 'Region', 'xva-gallery-core' ) ),
			'public' => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'rewrite' => array( 'slug' => 'artist-region' ),
		)
	);

	register_taxonomy(
		'xva_exhibition_status',
		array( 'xva_exhibition' ),
		array(
			'labels' => array( 'name' => __( 'Exhibition status', 'xva-gallery-core' ), 'singular_name' => __( 'Exhibition status', 'xva-gallery-core' ) ),
			'public' => true,
			'show_in_rest' => true,
			'hierarchical' => false,
			'rewrite' => array( 'slug' => 'exhibition-status' ),
		)
	);

	register_taxonomy(
		'xva_exhibition_year',
		array( 'xva_exhibition' ),
		array(
			'labels' => array( 'name' => __( 'Exhibition years', 'xva-gallery-core' ), 'singular_name' => __( 'Exhibition year', 'xva-gallery-core' ) ),
			'public' => true,
			'show_in_rest' => true,
			'hierarchical' => false,
			'rewrite' => array( 'slug' => 'exhibition-year' ),
		)
	);
}
add_action( 'init', 'xva_core_register_taxonomies' );
