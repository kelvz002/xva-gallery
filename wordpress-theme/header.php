<?php
/** XVA fallback header. Elementor Theme Builder can replace this location. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_home = is_front_page();
$has_gtranslate = function_exists( 'shortcode_exists' ) && shortcode_exists( 'gt-link' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'xva-body' ); ?>>
<?php wp_body_open(); ?>
<a class="xva-skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'xva-gallery' ); ?></a>
<?php if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) : ?>
<?php else : ?>
<header class="xva-site-header<?php echo $is_home ? ' xva-site-header--hero' : ' xva-site-header--light'; ?>" data-xva-header>
	<div class="xva-header-shell">
		<a class="xva-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'XVA Gallery home', 'xva-gallery' ); ?>">
			<?php echo xva_gallery_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
		<div class="xva-header-meta">
			<nav class="xva-language-switch" aria-label="<?php esc_attr_e( 'Language', 'xva-gallery' ); ?>">
				<?php if ( $has_gtranslate ) : ?>
					<span class="xva-language-option" data-xva-language="en"><?php echo do_shortcode( '[gt-link lang="en" label="EN" widget_look="lang_codes"]' ); ?></span><span aria-hidden="true">|</span><span class="xva-language-option" data-xva-language="ar"><?php echo do_shortcode( '[gt-link lang="ar" label="AR" widget_look="lang_codes"]' ); ?></span>
				<?php else : ?>
					<a href="<?php echo esc_url( add_query_arg( 'lang', 'en' ) ); ?>" data-xva-language="en" aria-current="true">EN</a><span aria-hidden="true">|</span><a href="<?php echo esc_url( add_query_arg( 'lang', 'ar' ) ); ?>" data-xva-language="ar" aria-current="false">AR</a>
				<?php endif; ?>
			</nav>
			<a class="xva-shop-link" href="<?php echo esc_url( 'http://xvashop.com/' ); ?>" aria-label="<?php esc_attr_e( 'Shop', 'xva-gallery' ); ?>"><?php echo xva_gallery_icon( 'bag' ); ?></a>
			<button class="xva-menu-toggle" type="button" aria-expanded="false" aria-controls="xva-menu" data-xva-menu-toggle><span><?php esc_html_e( 'Menu', 'xva-gallery' ); ?></span><?php echo xva_gallery_icon( 'menu' ); ?></button>
		</div>
	</div>
	<div class="xva-header-rule" aria-hidden="true"></div>
</header>
<?php endif; ?>
<div class="xva-menu-drawer" id="xva-menu" data-xva-menu-drawer hidden>
	<button class="xva-menu-close" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'xva-gallery' ); ?>" data-xva-menu-close><?php echo xva_gallery_icon( 'close' ); ?></button>
	<nav class="xva-menu-nav" aria-label="<?php esc_attr_e( 'Main navigation', 'xva-gallery' ); ?>"><?php xva_gallery_menu_fallback(); ?></nav>
</div>
<main id="main-content" class="xva-page-main">
