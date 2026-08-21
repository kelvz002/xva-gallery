<?php
/**
 * One-time bootstrap for the isolated XVA Gallery IONOS project.
 *
 * Upload this file to the WordPress web root, request it once with the token,
 * then leave the file inert for auditability. It never drops tables or files.
 */

declare( strict_types=1 );

$token = 'xva-bootstrap-20260821-7e2b9a1c4d6f';
if ( ! hash_equals( $token, (string) ( $_GET['token'] ?? '' ) ) ) {
	http_response_code( 404 );
	exit;
}

header( 'Content-Type: text/plain; charset=utf-8' );

$root          = __DIR__;
$config_path   = $root . '/wp-config.php';
$site_url      = 'https://xvagallery-iftkz5kuwo.live-website.com';
$old_site_url  = 'http://localhost:8090';
$messages      = array();

if ( ! is_readable( $config_path ) || ! is_writable( $config_path ) ) {
	http_response_code( 500 );
	echo "wp-config.php is not readable and writable.\n";
	exit;
}

$config = file_get_contents( $config_path );
if ( false === $config ) {
	http_response_code( 500 );
	echo "Could not read wp-config.php.\n";
	exit;
}

$updated_config = preg_replace(
	array(
		'/\$table_prefix\s*=\s*[\'\"][^\'\"]*[\'\"]\s*;/',
		'/\$table_prefix\s*=\s*getenv_docker\(\s*[\'\"]WORDPRESS_TABLE_PREFIX[\'\"]\s*,\s*[\'\"][^\'\"]*[\'\"]\s*\)\s*;/',
	),
	array(
		"\$table_prefix = 'wp_';",
		"\$table_prefix = 'wp_';",
	),
	$config,
	1,
	$config_count
);

if ( 0 === $config_count ) {
	$updated_config = str_replace(
		"require_once ABSPATH . 'wp-settings.php';",
		"\$table_prefix = 'wp_';\nrequire_once ABSPATH . 'wp-settings.php';",
		$config,
		$fallback_count
	);
	$config_count = $fallback_count;
}

if ( 0 === $config_count || false === $updated_config || $updated_config === $config ) {
	$messages[] = 'table_prefix already uses wp_ or did not require a change';
} elseif ( false === file_put_contents( $config_path, $updated_config, LOCK_EX ) ) {
	http_response_code( 500 );
	echo "Could not update wp-config.php.\n";
	exit;
} else {
	$messages[] = 'wp-config.php now uses the imported wp_ tables';
}

define( 'WP_USE_THEMES', false );
require_once $root . '/wp-load.php';

global $wpdb;

if ( ! function_exists( 'xva_bootstrap_replace' ) ) {
	function xva_bootstrap_replace( $value, string $old, string $new ) {
		if ( is_string( $value ) ) {
			return str_replace( $old, $new, $value );
		}
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				$value[ $key ] = xva_bootstrap_replace( $item, $old, $new );
			}
		}
		return $value;
	}
}

if ( ! function_exists( 'xva_bootstrap_update_serialized_table' ) ) {
	function xva_bootstrap_update_serialized_table( string $table, string $id_column, string $value_column, string $old, string $new ): int {
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT `{$id_column}`, `{$value_column}` FROM `{$table}`", ARRAY_A );
		$count = 0;
		foreach ( $rows as $row ) {
			$decoded = maybe_unserialize( $row[ $value_column ] );
			$changed = xva_bootstrap_replace( $decoded, $old, $new );
			$encoded = is_array( $changed ) ? maybe_serialize( $changed ) : $changed;
			if ( $encoded !== $row[ $value_column ] ) {
				$wpdb->update(
					$table,
					array( $value_column => $encoded ),
					array( $id_column => $row[ $id_column ] ),
					array( '%s' ),
					array( '%d' )
				);
				$count++;
			}
		}
		return $count;
	}
}

$wpdb->query(
	$wpdb->prepare(
		"UPDATE `{$wpdb->posts}` SET post_content = REPLACE(post_content, %s, %s), post_excerpt = REPLACE(post_excerpt, %s, %s), post_content_filtered = REPLACE(post_content_filtered, %s, %s), guid = REPLACE(guid, %s, %s)",
		$old_site_url,
		$site_url,
		$old_site_url,
		$site_url,
		$old_site_url,
		$site_url,
		$old_site_url,
		$site_url
	)
);

$updated_meta    = xva_bootstrap_update_serialized_table( $wpdb->postmeta, 'meta_id', 'meta_value', $old_site_url, $site_url );
$updated_options = xva_bootstrap_update_serialized_table( $wpdb->options, 'option_id', 'option_value', $old_site_url, $site_url );
$updated_users   = xva_bootstrap_update_serialized_table( $wpdb->usermeta, 'umeta_id', 'meta_value', $old_site_url, $site_url );

update_option( 'siteurl', $site_url );
update_option( 'home', $site_url );
update_option( 'blogname', 'XVA Gallery' );
update_option(
	'active_plugins',
	array(
		'ionos-sso/ionos-sso.php',
		'advanced-custom-fields/acf.php',
		'elementor/elementor.php',
		'xva-gallery-core/xva-gallery-core.php',
	)
);
update_option( 'template', 'xva-gallery' );
update_option( 'stylesheet', 'xva-gallery' );
if ( function_exists( 'switch_theme' ) ) {
	switch_theme( 'xva-gallery' );
}
flush_rewrite_rules( false );

echo implode( "\n", $messages ) . "\n";
echo "Updated post content and attachment URLs: {$updated_meta} postmeta, {$updated_options} options, {$updated_users} usermeta rows.\n";
echo "Activated XVA Gallery theme and available site plugins.\n";
echo "Temporary site URL: {$site_url}\n";
