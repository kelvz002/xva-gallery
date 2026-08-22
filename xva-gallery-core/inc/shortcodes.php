<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xva_core_logo_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'class' => 'xva-logo', 'alt' => get_bloginfo( 'name' ) ), $atts, 'xva_logo' );
	$theme_uri = get_template_directory_uri();
	return '<img class="' . esc_attr( $atts['class'] ) . '" src="' . esc_url( $theme_uri . '/assets/images/xva-logo.svg' ) . '" alt="' . esc_attr( $atts['alt'] ) . '" width="66" height="45" decoding="async">';
}
add_shortcode( 'xva_logo', 'xva_core_logo_shortcode' );

function xva_core_title_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'post_id' => get_the_ID(), 'fallback' => '' ), $atts, 'xva_title' );
	$title = get_the_title( $atts['post_id'] );
	return esc_html( $title ? $title : $atts['fallback'] );
}
add_shortcode( 'xva_title', 'xva_core_title_shortcode' );

function xva_core_heading_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'text' => '', 'field' => '', 'level' => 'h2', 'class' => 'xva-serif' ), $atts, 'xva_heading' );
	$text = $atts['field'] ? get_the_title( get_the_ID() ) : $atts['text'];
	$level = in_array( strtolower( $atts['level'] ), array( 'h1', 'h2', 'h3', 'h4' ), true ) ? strtolower( $atts['level'] ) : 'h2';
	return '<' . $level . ' class="' . esc_attr( $atts['class'] ) . '">' . esc_html( $text ) . '</' . $level . '>';
}
add_shortcode( 'xva_heading', 'xva_core_heading_shortcode' );

function xva_core_theme_image_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'file' => '', 'alt' => '', 'class' => '' ), $atts, 'xva_theme_image' );
	$file = ltrim( (string) $atts['file'], '/\\' );
	$allowed = array( 'hero-01.webp', 'hero-02.webp', 'hero-03.webp', 'hero-04.webp', 'oblivion.webp', 'huma-02.jpg', 'huma-03.jpg', 'huma-04.jpg', 'artist-akim.jpg', 'artist-al-braithwaite.jpg', 'artist-alejandra.jpg', 'artist-charlie.jpg', 'artist-halim.jpg', 'artist-huma.jpg', 'artist-hussein.jpg', 'artist-mona.jpg', 'artist-moussa.jpg', 'artist-naqsh.jpg', 'ex-after-image.jpg', 'ex-fractal.jpg', 'ex-half-light.jpg', 'ex-local.jpeg', 'ex-truth.jpg', 'ex-white.jpg', 'hotel.jpeg', 'cafe.jpg', 'cafe-2.png' );
	if ( ! in_array( $file, $allowed, true ) ) {
		return '';
	}
	$url = get_template_directory_uri() . '/assets/images/' . rawurlencode( $file );
	return '<img class="' . esc_attr( $atts['class'] ) . '" src="' . esc_url( $url ) . '" alt="' . esc_attr( $atts['alt'] ) . '" loading="lazy" decoding="async">';
}
add_shortcode( 'xva_theme_image', 'xva_core_theme_image_shortcode' );

function xva_core_field_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'field' => '', 'post_id' => get_the_ID(), 'fallback' => '' ), $atts, 'xva_field' );
	$value = xva_core_get_field( $atts['field'], $atts['post_id'], $atts['fallback'] );
	if ( is_array( $value ) || is_object( $value ) ) {
		return '';
	}
	return wp_kses_post( (string) $value );
}
add_shortcode( 'xva_field', 'xva_core_field_shortcode' );

function xva_core_image_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'field' => '', 'post_id' => get_the_ID(), 'size' => 'large', 'alt' => '' ), $atts, 'xva_image' );
	$value = xva_core_get_field( $atts['field'], $atts['post_id'] );
	$url = xva_core_image_url( $value, $atts['size'] );
	if ( ! $url ) {
		return '';
	}
	$alt = $atts['alt'];
	if ( ! $alt && is_array( $value ) && ! empty( $value['alt'] ) ) {
		$alt = $value['alt'];
	}
	return '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async">';
}
add_shortcode( 'xva_image', 'xva_core_image_shortcode' );

function xva_core_gallery_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'field' => 'artwork_gallery', 'post_id' => get_the_ID() ), $atts, 'xva_gallery' );
	$native_ids = 'artwork_gallery' === $atts['field'] && function_exists( 'xva_core_native_exhibition_gallery_ids' ) ? xva_core_native_exhibition_gallery_ids( $atts['post_id'] ) : null;
	$items     = null !== $native_ids ? $native_ids : xva_core_get_field( $atts['field'], $atts['post_id'], array() );
	if ( ! is_array( $items ) || ! $items ) {
		return '<div class="xva-gallery-empty"><span class="xva-kicker">Artwork gallery</span><p>Choose artwork or installation photos from the native WordPress Media Library gallery on the exhibition edit screen.</p></div>';
	}
	$output = '<div class="xva-artwork-gallery">';
	foreach ( $items as $item ) {
		$url = xva_core_image_url( $item, 'large' );
		if ( ! $url ) {
			continue;
		}
		$alt = is_array( $item ) && ! empty( $item['alt'] ) ? $item['alt'] : '';
		$output .= '<figure><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async"></figure>';
	}
	$output .= '</div>';
	return $output;
}
add_shortcode( 'xva_gallery', 'xva_core_gallery_shortcode' );

function xva_core_related_artists_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'field' => 'participating_artists', 'post_id' => get_the_ID() ), $atts, 'xva_related_artists' );
	$items = xva_core_get_field( $atts['field'], $atts['post_id'], array() );
	if ( ! is_array( $items ) || ! $items ) {
		return '';
	}
	$output = '<div class="xva-artist-grid xva-related-artists">';
	foreach ( $items as $item ) {
		$post_id = is_object( $item ) ? $item->ID : (int) $item;
		if ( ! $post_id ) {
			continue;
		}
		$image = get_the_post_thumbnail_url( $post_id, 'xva-portrait' );
		$output .= '<article class="xva-card xva-artist-card"><a class="xva-artist-card__link" href="' . esc_url( get_permalink( $post_id ) ) . '"><div class="xva-card__media">' . ( $image ? '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( get_the_title( $post_id ) ) . '" loading="lazy" decoding="async">' : '' ) . '</div><div class="xva-artist-card__meta"><span class="xva-artist-card__name">' . esc_html( get_the_title( $post_id ) ) . '</span></div></a></article>';
	}
	return $output . '</div>';
}
add_shortcode( 'xva_related_artists', 'xva_core_related_artists_shortcode' );

function xva_core_artist_grid_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'limit' => 12, 'region' => '' ), $atts, 'xva_artist_grid' );
	$query_args = array(
		'post_type' => 'xva_artist',
		'post_status' => 'publish',
		'posts_per_page' => (int) $atts['limit'],
		'orderby' => 'title',
		'order' => 'ASC',
	);
	if ( $atts['region'] ) {
		$query_args['tax_query'] = array( array( 'taxonomy' => 'xva_artist_region', 'field' => 'slug', 'terms' => sanitize_title( $atts['region'] ) ) );
	}
	$query = new WP_Query( $query_args );
	ob_start();
	if ( $query->have_posts() ) :
		?><div class="xva-artist-grid" data-xva-artist-preview><?php
		$index = 1;
		while ( $query->have_posts() ) : $query->the_post();
			$image = get_the_post_thumbnail_url( get_the_ID(), 'xva-portrait' );
			?><article class="xva-card xva-artist-card" data-xva-artist-card data-xva-name="<?php echo esc_attr( get_the_title() ); ?>" data-xva-image="<?php echo esc_url( $image ); ?>"><a class="xva-artist-card__link" href="<?php the_permalink(); ?>"><div class="xva-card__media"><?php if ( $image ) : ?><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" decoding="async"><?php endif; ?></div><div class="xva-artist-card__meta"><span class="xva-artist-card__name"><?php the_title(); ?></span><span class="xva-artist-card__index"><?php echo esc_html( str_pad( (string) $index, 2, '0', STR_PAD_LEFT ) ); ?></span></div></a></article><?php
			$index++;
		endwhile;
		?></div><div class="xva-artist-preview" data-xva-preview-overlay aria-hidden="true"><img src="" alt=""><span data-xva-preview-label></span></div><?php
	endif;
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'xva_artist_grid', 'xva_core_artist_grid_shortcode' );

function xva_core_exhibition_grid_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'limit' => 12, 'status' => '' ), $atts, 'xva_exhibition_grid' );
	$query_args = array( 'post_type' => 'xva_exhibition', 'post_status' => 'publish', 'posts_per_page' => (int) $atts['limit'], 'orderby' => 'meta_value', 'meta_key' => 'start_date', 'order' => 'DESC' );
	if ( $atts['status'] ) {
		$query_args['tax_query'] = array( array( 'taxonomy' => 'xva_exhibition_status', 'field' => 'slug', 'terms' => sanitize_title( $atts['status'] ) ) );
	}
	$query = new WP_Query( $query_args );
	ob_start();
	if ( $query->have_posts() ) :
		?><div class="xva-exhibition-grid"><?php
		while ( $query->have_posts() ) : $query->the_post();
			$image = xva_core_mockup_exhibition_poster( get_the_ID(), 'oblivion.webp', 'xva-card' );
			?><article class="xva-card xva-exhibition-card"><a href="<?php the_permalink(); ?>"><div class="xva-card__media"><?php if ( $image ) : ?><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" decoding="async"><?php endif; ?></div><div class="xva-card__meta"><span><?php echo esc_html( xva_core_mockup_exhibition_start_date( get_the_ID(), get_the_date( 'Y', get_the_ID() ) ) ); ?></span><span><?php echo esc_html( xva_core_get_field( 'venue', get_the_ID(), 'XVA Gallery' ) ); ?></span></div><h3><?php the_title(); ?></h3><p><?php echo esc_html( xva_core_mockup_post_excerpt( get_the_ID(), '', 180 ) ); ?></p><span class="xva-card-link">View exhibition <?php echo xva_core_inline_icon( 'external' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></a></article><?php
		endwhile;
		?></div><?php
	endif;
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'xva_exhibition_grid', 'xva_core_exhibition_grid_shortcode' );

function xva_core_inline_icon( $name ) {
	if ( 'arrow' === $name ) {
		return '<svg class="xva-icon xva-icon--arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15"/><path d="m13 6 6 6-6 6"/></svg>';
	}
	if ( 'external' === $name ) {
		return '<svg class="xva-icon xva-icon--external" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9"/><path d="M19 13v6a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/></svg>';
	}
	return '';
}
