<?php
get_header();
if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'single' ) ) :
elseif ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
endif;
get_footer();
