<?php
get_header();
if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'archive' ) ) :
elseif ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
else :
	get_template_part( 'template-parts/content', 'none' );
endif;
get_footer();
