<?php
/**
 * Small, idempotent content migrations for the XVA data model.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xva_core_exhibition_storage_date( $value ) {
	if ( preg_match( '/^\d{8}$/', (string) $value ) ) {
		return (string) $value;
	}
	if ( ! preg_match( '/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/', trim( (string) $value ), $match ) ) {
		return '';
	}
	$months = array(
		'january'   => 1,
		'february'  => 2,
		'march'     => 3,
		'april'     => 4,
		'may'       => 5,
		'june'      => 6,
		'july'      => 7,
		'august'    => 8,
		'september' => 9,
		'october'   => 10,
		'november'  => 11,
		'december'  => 12,
	);
	$month = strtolower( $match[2] );
	if ( ! isset( $months[ $month ] ) ) {
		return '';
	}
	return sprintf( '%04d%02d%02d', (int) $match[3], $months[ $month ], (int) $match[1] );
}

function xva_core_sync_exhibition_dates() {
	$version = '20260822-1';
	if ( $version === get_option( 'xva_core_exhibition_dates_migration' ) ) {
		return;
	}

	$posts = get_posts(
		array(
			'post_type'      => 'xva_exhibition',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $posts as $post_id ) {
		$range = xva_core_mockup_exhibition_date_range( $post_id, '' );
		if ( ! $range ) {
			continue;
		}
		$parts = preg_split( '/\s+–\s+/u', $range, 2 );
		$start = xva_core_exhibition_storage_date( $parts[0] );
		$end   = isset( $parts[1] ) ? xva_core_exhibition_storage_date( $parts[1] ) : '';
		if ( $start && ! xva_core_get_field( 'start_date', $post_id, '' ) ) {
			if ( function_exists( 'update_field' ) ) {
				update_field( 'start_date', $start, $post_id );
			} else {
				update_post_meta( $post_id, 'start_date', $start );
			}
		}
		if ( $end && ! xva_core_get_field( 'end_date', $post_id, '' ) ) {
			if ( function_exists( 'update_field' ) ) {
				update_field( 'end_date', $end, $post_id );
			} else {
				update_post_meta( $post_id, 'end_date', $end );
			}
		}
	}
	update_option( 'xva_core_exhibition_dates_migration', $version, false );
}
add_action( 'init', 'xva_core_sync_exhibition_dates', 20 );
