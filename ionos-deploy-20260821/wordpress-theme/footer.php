<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>
<?php if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) : ?>
<?php else : ?>
<footer class="xva-site-footer">
	<div class="xva-shell">
		<div class="xva-footer-top"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'XVA Gallery home', 'xva-gallery' ); ?>"><?php echo xva_gallery_logo( array( 'class' => 'xva-logo xva-logo--light' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><div class="xva-footer-social"><a href="https://www.instagram.com/xvagallery/" target="_blank" rel="noopener">Instagram</a><a href="https://www.facebook.com/xvagallery/" target="_blank" rel="noopener">Facebook</a><a href="https://twitter.com/XVAGALLERY" target="_blank" rel="noopener">Twitter</a></div></div>
		<div class="xva-footer-grid">
			<div><span class="xva-footer-label"><?php esc_html_e( 'Visit', 'xva-gallery' ); ?></span><p>Al Fahidi Historical Neighbourhood<br>Dubai, United Arab Emirates</p></div>
			<div><span class="xva-footer-label"><?php esc_html_e( 'Contact', 'xva-gallery' ); ?></span><p><a href="mailto:xva@xvagallery.com">xva@xvagallery.com</a><br><a href="tel:+97143533462">+971 4 353 3462</a></p></div>
			<div><span class="xva-footer-label"><?php esc_html_e( 'Explore', 'xva-gallery' ); ?></span><p><a href="<?php echo esc_url( home_url( '/artists/' ) ); ?>">Artists</a><br><a href="<?php echo esc_url( home_url( '/exhibitions/' ) ); ?>">Exhibitions</a></p></div>
			<div><span class="xva-footer-label"><?php esc_html_e( 'Opening hours', 'xva-gallery' ); ?></span><p>Monday–Sunday<br>10:00–18:00</p></div>
		</div>
		<div class="xva-footer-bottom"><span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> XVA Gallery</span><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy policy</a></div>
	</div>
</footer>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
