<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xva_gallery_elementor_support() {
	add_theme_support( 'elementor' );
}
add_action( 'after_setup_theme', 'xva_gallery_elementor_support' );

function xva_gallery_register_elementor_locations( $elementor_theme_manager ) {
	if ( method_exists( $elementor_theme_manager, 'register_all_core_location' ) ) {
		$elementor_theme_manager->register_all_core_location();
		return;
	}

	$elementor_theme_manager->register_location( 'header' );
	$elementor_theme_manager->register_location( 'footer' );
	$elementor_theme_manager->register_location( 'single' );
	$elementor_theme_manager->register_location( 'archive' );
}
add_action( 'elementor/theme/register_locations', 'xva_gallery_register_elementor_locations' );
