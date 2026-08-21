<?php
/**
 * XVA Gallery theme bootstrap.
 *
 * Presentation is intentionally Elementor-friendly. Permanent content models
 * live in the optional xva-gallery-core companion plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XVA_GALLERY_VERSION', '1.1.38' );
define( 'XVA_GALLERY_DIR', get_template_directory() );
define( 'XVA_GALLERY_URI', get_template_directory_uri() );

require_once XVA_GALLERY_DIR . '/inc/theme-setup.php';
require_once XVA_GALLERY_DIR . '/inc/elementor.php';
require_once XVA_GALLERY_DIR . '/inc/template-tags.php';
