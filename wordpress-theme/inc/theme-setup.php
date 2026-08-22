<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xva_gallery_setup() {
	load_theme_textdomain( 'xva-gallery', XVA_GALLERY_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 45, 'width' => 66, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/xva.css' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary navigation', 'xva-gallery' ),
			'footer'  => __( 'Footer navigation', 'xva-gallery' ),
		)
	);

	add_image_size( 'xva-hero', 2400, 1500, true );
	add_image_size( 'xva-portrait', 1000, 1250, true );
	add_image_size( 'xva-card', 900, 1125, true );
	add_image_size( 'xva-wide', 1800, 1100, true );
}
add_action( 'after_setup_theme', 'xva_gallery_setup' );

function xva_gallery_enqueue_assets() {
	wp_enqueue_style( 'xva-gallery-style', get_stylesheet_uri(), array(), XVA_GALLERY_VERSION );
	wp_enqueue_style( 'xva-gallery-dm-sans', 'https://fonts.googleapis.com/css2?display=swap&family=DM+Sans', array(), null );
	wp_enqueue_style( 'xva-gallery-visual-system', XVA_GALLERY_URI . '/assets/css/xva.css', array( 'xva-gallery-style', 'xva-gallery-dm-sans' ), XVA_GALLERY_VERSION );
	wp_enqueue_script( 'xva-gallery-interactions', XVA_GALLERY_URI . '/assets/js/xva.js', array(), XVA_GALLERY_VERSION, true );
	wp_localize_script(
		'xva-gallery-interactions',
		'xvaGallery',
		array(
			'homeUrl' => esc_url_raw( home_url( '/' ) ),
			'menuLabel' => __( 'Menu', 'xva-gallery' ),
			'closeLabel' => __( 'Close menu', 'xva-gallery' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'xva_gallery_enqueue_assets' );

function xva_gallery_body_class( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'xva-front-page';
	}
	if ( is_singular( 'xva_artist' ) ) {
		$classes[] = 'xva-artist-page';
	}
	if ( is_singular( 'xva_exhibition' ) ) {
		$classes[] = 'xva-exhibition-page';
	}
	return $classes;
}
add_filter( 'body_class', 'xva_gallery_body_class' );

function xva_gallery_preconnect() {
	// The mockups use a system font stack so the site remains self-contained.
}
add_action( 'wp_head', 'xva_gallery_preconnect', 1 );

/**
 * Give search engines and social previews useful, page-specific metadata.
 *
 * The imported WordPress installation had no meta description. Keep this
 * lightweight and theme-owned so it works with the temporary IONOS URL and
 * does not require another SEO plugin.
 */
function xva_gallery_document_title( $title ) {
	if ( is_front_page() ) {
		return 'XVA Gallery | Contemporary Art from the Arab World';
	}

	if ( is_post_type_archive( 'xva_artist' ) || is_page( 'artists' ) ) {
		return 'Artists | XVA Gallery | Contemporary Art';
	}

	if ( is_post_type_archive( 'xva_exhibition' ) || is_page( 'exhibitions' ) ) {
		return 'Exhibitions | XVA Gallery | Dubai';
	}

	if ( is_singular( 'xva_artist' ) ) {
		return get_the_title() . ' | Artist | XVA Gallery';
	}

	if ( is_singular( 'xva_exhibition' ) ) {
		return get_the_title() . ' | Exhibition | XVA Gallery';
	}

	if ( is_singular() ) {
		$page_title = trim( wp_strip_all_tags( get_the_title() ) );
		if ( $page_title ) {
			return $page_title . ' | XVA Gallery';
		}
	}

	return $title ? $title : 'XVA Gallery | Contemporary Art from the Arab World';
}
add_filter( 'pre_get_document_title', 'xva_gallery_document_title' );

function xva_gallery_meta_description() {
	if ( is_front_page() ) {
		return 'XVA Gallery presents contemporary art from the Arab world and the Sub-continent, with exhibitions and artists in Dubai\'s Al Fahidi Historical Neighbourhood.';
	}

	if ( is_post_type_archive( 'xva_artist' ) || is_page( 'artists' ) ) {
		return 'Meet the artists represented by XVA Gallery, presenting contemporary art from the Arab world and the Sub-continent in Dubai.';
	}

	if ( is_post_type_archive( 'xva_exhibition' ) || is_page( 'exhibitions' ) ) {
		return 'Explore current, past and archived exhibitions at XVA Gallery in Dubai\'s Al Fahidi Historical Neighbourhood.';
	}

	if ( is_page( 'contact' ) ) {
		return 'Visit XVA Gallery in Dubai\'s Al Fahidi Historical Neighbourhood. Find directions, opening hours and gallery contact details.';
	}

	if ( is_singular() ) {
		$excerpt = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( get_the_excerpt() ) ) );
		if ( $excerpt ) {
			return wp_trim_words( $excerpt, 28, '…' );
		}
	}

	return 'XVA Gallery is a contemporary art gallery in Dubai\'s Al Fahidi Historical Neighbourhood.';
}

function xva_gallery_seo_meta() {
	if ( is_admin() ) {
		return;
	}

	$description = xva_gallery_meta_description();
	$title       = wp_get_document_title();
	$url         = is_singular() ? get_permalink() : home_url( '/' );
	$image       = get_the_post_thumbnail_url( null, 'large' );
	if ( ! $image ) {
		$image = XVA_GALLERY_URI . '/assets/images/hero-01.webp';
	}

	echo '<meta name="description" content="' . esc_attr( $description ) . '">';
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">';
	echo '<meta property="og:description" content="' . esc_attr( $description ) . '">';
	echo '<meta property="og:type" content="website">';
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">';
	echo '<meta property="og:image" content="' . esc_url( $image ) . '">';
	echo '<meta name="twitter:card" content="summary_large_image">';
}
add_action( 'wp_head', 'xva_gallery_seo_meta', 2 );
