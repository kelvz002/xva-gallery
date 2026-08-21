<?php
/**
 * Plugin Name: XVA Gallery Core
 * Plugin URI: https://www.xvagallery.com/
 * Description: Permanent content models, ACF configuration, shortcodes and Elementor query integrations for XVA Gallery.
 * Version: 1.0.25
 * Author: XVA Gallery
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Text Domain: xva-gallery-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XVA_CORE_VERSION', '1.0.25' );
define( 'XVA_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'XVA_CORE_URI', plugin_dir_url( __FILE__ ) );

require_once XVA_CORE_DIR . 'inc/post-types.php';
require_once XVA_CORE_DIR . 'inc/acf.php';
require_once XVA_CORE_DIR . 'inc/shortcodes.php';
require_once XVA_CORE_DIR . 'inc/mockup-renderer.php';
require_once XVA_CORE_DIR . 'inc/rest.php';
require_once XVA_CORE_DIR . 'inc/elementor.php';
require_once XVA_CORE_DIR . 'inc/dynamic-tags.php';

function xva_core_activate() {
	xva_core_register_post_types();
	xva_core_register_taxonomies();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'xva_core_activate' );

function xva_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'xva_core_deactivate' );
