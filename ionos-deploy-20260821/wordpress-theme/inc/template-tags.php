<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xva_gallery_logo( $args = array() ) {
	$defaults = array(
		'class' => 'xva-logo',
		'alt'   => get_bloginfo( 'name' ),
	);
	$args = wp_parse_args( $args, $defaults );

	return sprintf(
		'<img class="%1$s" src="%2$s" alt="%3$s" width="66" height="45" decoding="async">',
		esc_attr( $args['class'] ),
		esc_url( XVA_GALLERY_URI . '/assets/images/xva-logo.svg' ),
		esc_attr( $args['alt'] )
	);
}

function xva_gallery_icon( $name = 'arrow' ) {
	$paths = array(
		'arrow' => '<path d="M4 12h15M13 5l7 7-7 7"/>',
		'external' => '<path d="M14 4h6v6M20 4l-9 9"/><path d="M19 13v6a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/>',
		'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
		'close' => '<path d="M5 5l14 14M19 5L5 19"/>',
		'bag' => '<path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
	);
	$path = isset( $paths[ $name ] ) ? $paths[ $name ] : $paths['arrow'];
	return '<svg class="xva-icon xva-icon--' . esc_attr( $name ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

function xva_gallery_menu_fallback() {
	$items = wp_get_nav_menu_items( 'primary' );
	if ( ! $items ) {
		$items = array(
			(object) array( 'title' => __( 'Artists', 'xva-gallery' ), 'url' => home_url( '/artists/' ) ),
			(object) array( 'title' => __( 'Exhibitions', 'xva-gallery' ), 'url' => home_url( '/exhibitions/' ) ),
			(object) array( 'title' => __( 'Art consultancy', 'xva-gallery' ), 'url' => home_url( '/art-consultancy/' ) ),
			(object) array( 'title' => __( 'Hotel & Café', 'xva-gallery' ), 'url' => home_url( '/hotel-cafe/' ) ),
			(object) array( 'title' => __( 'Press', 'xva-gallery' ), 'url' => home_url( '/press/' ) ),
			(object) array( 'title' => __( 'Contact', 'xva-gallery' ), 'url' => home_url( '/contact/' ) ),
		);
	}
	foreach ( $items as $item ) {
		echo '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '<span class="xva-nav-item-arrow">' . xva_gallery_icon( 'external' ) . '</span></a>';
	}
}
