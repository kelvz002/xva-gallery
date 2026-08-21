<?php
/**
 * Public read-only endpoints used by the progressive exhibition archive.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xva_core_rest_exhibitions( WP_REST_Request $request ) {
	$offset  = max( 0, (int) $request->get_param( 'offset' ) );
	$per_page = min( 24, max( 1, (int) $request->get_param( 'per_page' ) ) );
	$status  = sanitize_key( (string) $request->get_param( 'status' ) );
	$status  = in_array( $status, array( 'past', 'archive' ), true ) ? $status : 'all';
	$current = xva_core_mockup_current_exhibition_post();
	$current_id = $current ? (int) $current->ID : 0;
	$query = new WP_Query(
		array(
			'post_type'      => 'xva_exhibition',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'post__not_in'   => $current_id ? array( $current_id ) : array(),
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	$posts = $query->posts;
	if ( 'all' !== $status ) {
		$posts = array_values(
			array_filter(
				$posts,
				static function ( $post ) use ( $status, $current_id ) {
					return $status === xva_core_mockup_exhibition_status( $post->ID, $current_id );
				}
			)
		);
	}
	$total = count( $posts );
	$posts = array_slice( $posts, $offset, $per_page );

	ob_start();
	foreach ( $posts as $post ) {
		xva_core_mockup_exhibition_archive_card( $post, $current_id );
	}
	$html = ob_get_clean();

	$next_offset = min( $offset + count( $posts ), $total );
	return rest_ensure_response(
		array(
			'html'        => $html,
			'next_offset' => $next_offset,
			'has_more'    => $next_offset < $total,
			'count'       => count( $posts ),
			'total'       => $total,
		)
	);
}

function xva_core_register_rest_routes() {
	register_rest_route(
		'xva/v1',
		'/exhibitions',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'xva_core_rest_exhibitions',
			'permission_callback' => '__return_true',
			'args'                => array(
				'offset'   => array( 'default' => 0, 'sanitize_callback' => 'absint' ),
				'per_page' => array( 'default' => 12, 'sanitize_callback' => 'absint' ),
				'status'   => array( 'default' => 'all', 'sanitize_callback' => 'sanitize_key' ),
			),
		)
	);
}
add_action( 'rest_api_init', 'xva_core_register_rest_routes' );
