<?php
/**
 * Native WordPress Media Library gallery for exhibitions.
 *
 * The gallery is stored as attachment IDs in post meta so it works without
 * the ACF Pro Gallery field. Existing ACF gallery values remain a read-only
 * fallback until an exhibition is saved through this native control.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xva_core_native_exhibition_gallery_meta_key() {
	return '_xva_exhibition_gallery_ids';
}

function xva_core_native_exhibition_gallery_ids( $post_id ) {
	$post_id = (int) $post_id;
	$key     = xva_core_native_exhibition_gallery_meta_key();
	if ( ! $post_id || ! metadata_exists( 'post', $post_id, $key ) ) {
		return null;
	}

	$stored = get_post_meta( $post_id, $key, true );
	$ids    = array();
	foreach ( (array) $stored as $attachment_id ) {
		$attachment_id = absint( $attachment_id );
		if ( $attachment_id && wp_attachment_is_image( $attachment_id ) ) {
			$ids[] = $attachment_id;
		}
	}
	return array_values( array_unique( $ids ) );
}

function xva_core_legacy_exhibition_gallery_ids( $post_id ) {
	$value = function_exists( 'xva_core_get_field' ) ? xva_core_get_field( 'artwork_gallery', $post_id, array() ) : array();
	if ( is_array( $value ) && ( isset( $value['ID'] ) || isset( $value['id'] ) ) ) {
		$value = array( $value );
	}
	$ids = array();
	foreach ( (array) $value as $item ) {
		$attachment_id = is_array( $item ) ? ( ! empty( $item['ID'] ) ? $item['ID'] : ( ! empty( $item['id'] ) ? $item['id'] : 0 ) ) : $item;
		$attachment_id = absint( $attachment_id );
		if ( $attachment_id && wp_attachment_is_image( $attachment_id ) ) {
			$ids[] = $attachment_id;
		}
	}
	return array_values( array_unique( $ids ) );
}

function xva_core_hide_legacy_exhibition_gallery_field( $field ) {
	return false;
}
add_filter( 'acf/prepare_field/key=field_xva_exhibition_artwork_gallery', 'xva_core_hide_legacy_exhibition_gallery_field' );

function xva_core_register_native_exhibition_gallery_meta_box() {
	add_meta_box(
		'xva-exhibition-native-gallery',
		__( 'Artwork / installation gallery', 'xva-gallery-core' ),
		'xva_core_render_native_exhibition_gallery_meta_box',
		'xva_exhibition',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_xva_exhibition', 'xva_core_register_native_exhibition_gallery_meta_box' );

function xva_core_render_native_exhibition_gallery_meta_box( $post ) {
	$native_ids = xva_core_native_exhibition_gallery_ids( $post->ID );
	$ids        = null !== $native_ids ? $native_ids : xva_core_legacy_exhibition_gallery_ids( $post->ID );
	wp_nonce_field( 'xva_save_native_exhibition_gallery', 'xva_native_exhibition_gallery_nonce' );
	?>
	<div class="xva-native-gallery" id="xva-native-gallery">
		<p class="description">Choose multiple artwork or installation photos from the native WordPress Media Library. These images appear in landscape 3:2 gallery slots and should not include the poster.</p>
		<input type="hidden" name="xva_exhibition_gallery_ids" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
		<p class="xva-native-gallery__actions">
			<button type="button" class="button button-primary" id="xva-native-gallery-select">Choose images from Media Library</button>
			<button type="button" class="button" id="xva-native-gallery-clear">Clear gallery</button>
		</p>
		<ul class="xva-native-gallery__list" id="xva-native-gallery-preview">
			<?php if ( $ids ) : ?>
				<?php foreach ( $ids as $attachment_id ) : ?>
					<li class="xva-native-gallery__item" data-id="<?php echo esc_attr( $attachment_id ); ?>">
						<?php echo wp_get_attachment_image( $attachment_id, 'thumbnail', false, array( 'class' => 'xva-native-gallery__image' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<button type="button" class="button-link-delete xva-native-gallery__remove" data-remove="<?php echo esc_attr( $attachment_id ); ?>">Remove</button>
					</li>
				<?php endforeach; ?>
			<?php else : ?>
				<li class="xva-native-gallery__empty">No gallery images selected yet.</li>
			<?php endif; ?>
		</ul>
	</div>
	<?php
}

function xva_core_enqueue_native_exhibition_gallery_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'xva_exhibition' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'xva-core-native-gallery', XVA_CORE_URI . 'assets/css/native-gallery.css', array(), XVA_CORE_VERSION );
	wp_enqueue_script( 'xva-core-native-gallery', XVA_CORE_URI . 'assets/js/native-gallery.js', array( 'jquery', 'media-editor' ), XVA_CORE_VERSION, true );
	wp_localize_script(
		'xva-core-native-gallery',
		'xvaNativeGallery',
		array(
			'title'  => __( 'Select exhibition gallery images', 'xva-gallery-core' ),
			'button' => __( 'Use selected images', 'xva-gallery-core' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'xva_core_enqueue_native_exhibition_gallery_assets' );

function xva_core_save_native_exhibition_gallery( $post_id ) {
	if ( ! isset( $_POST['xva_native_exhibition_gallery_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['xva_native_exhibition_gallery_nonce'] ) ), 'xva_save_native_exhibition_gallery' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$raw_ids = isset( $_POST['xva_exhibition_gallery_ids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['xva_exhibition_gallery_ids'] ) ) ) : array();
	$ids     = array();
	foreach ( $raw_ids as $attachment_id ) {
		$attachment_id = absint( $attachment_id );
		if ( $attachment_id && wp_attachment_is_image( $attachment_id ) ) {
			$ids[] = $attachment_id;
		}
	}
	$ids = array_values( array_unique( $ids ) );
	update_post_meta( $post_id, xva_core_native_exhibition_gallery_meta_key(), $ids );
}
add_action( 'save_post_xva_exhibition', 'xva_core_save_native_exhibition_gallery' );
