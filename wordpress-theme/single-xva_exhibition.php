<?php
/**
 * Shared single template for every XVA exhibition.
 *
 * Exhibition content lives in the core plugin's metafields. Keeping this
 * template in the theme makes newly created exhibitions use the same detail
 * layout even when an Elementor single template has not been assigned yet.
 */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		if ( function_exists( 'xva_core_mockup_exhibition_single' ) ) :
			xva_core_mockup_exhibition_single( get_the_ID() );
		endif;
	endwhile;
endif;

get_footer();
