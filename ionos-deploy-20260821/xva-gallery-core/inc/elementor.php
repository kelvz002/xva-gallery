<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xva_core_elementor_artists_query( $query ) {
	$query->set( 'post_type', 'xva_artist' );
	$query->set( 'post_status', 'publish' );
	$query->set( 'orderby', 'title' );
	$query->set( 'order', 'ASC' );
}
add_action( 'elementor/query/xva_artists', 'xva_core_elementor_artists_query' );

function xva_core_elementor_exhibitions_query( $query ) {
	$query->set( 'post_type', 'xva_exhibition' );
	$query->set( 'post_status', 'publish' );
	$query->set( 'meta_key', 'start_date' );
	$query->set( 'orderby', 'meta_value' );
	$query->set( 'order', 'DESC' );
}
add_action( 'elementor/query/xva_exhibitions', 'xva_core_elementor_exhibitions_query' );

function xva_core_elementor_press_query( $query ) {
	$query->set( 'post_type', 'xva_press' );
	$query->set( 'post_status', 'publish' );
	$query->set( 'orderby', 'date' );
	$query->set( 'order', 'DESC' );
}
add_action( 'elementor/query/xva_press', 'xva_core_elementor_press_query' );
