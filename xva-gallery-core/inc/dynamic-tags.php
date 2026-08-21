<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function xva_core_register_dynamic_tag_group( $manager ) {
	$manager->register_group( 'xva', array( 'title' => esc_html__( 'XVA Gallery', 'xva-gallery-core' ) ) );
}
add_action( 'elementor/dynamic_tags/register', 'xva_core_register_dynamic_tag_group' );

function xva_core_register_dynamic_tags( $manager ) {
	if ( ! class_exists( '\Elementor\Core\DynamicTags\Tag' ) ) {
		return;
	}
	$manager->register( new XVA_Elementor_ACF_Text_Tag() );
	$manager->register( new XVA_Elementor_ACF_Image_Tag() );
}
add_action( 'elementor/dynamic_tags/register', 'xva_core_register_dynamic_tags' );

if ( class_exists( '\Elementor\Core\DynamicTags\Tag' ) ) {
	class XVA_Elementor_ACF_Text_Tag extends \Elementor\Core\DynamicTags\Tag {
		public function get_name(): string { return 'xva-acf-text'; }
		public function get_title(): string { return esc_html__( 'XVA ACF Text', 'xva-gallery-core' ); }
		public function get_group(): array { return array( 'xva' ); }
		public function get_categories(): array { return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ); }
		protected function register_controls(): void {
			$this->add_control( 'field_name', array( 'label' => esc_html__( 'ACF field name', 'xva-gallery-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'intro' ) );
			$this->add_control( 'fallback', array( 'label' => esc_html__( 'Fallback', 'xva-gallery-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA ) );
		}
		public function render(): void {
			$field = $this->get_settings( 'field_name' );
			$value = xva_core_get_field( $field, get_the_ID(), $this->get_settings( 'fallback' ) );
			if ( is_scalar( $value ) ) {
				echo wp_kses_post( (string) $value );
			}
		}
	}

	class XVA_Elementor_ACF_Image_Tag extends \Elementor\Core\DynamicTags\Tag {
		public function get_name(): string { return 'xva-acf-image'; }
		public function get_title(): string { return esc_html__( 'XVA ACF Image', 'xva-gallery-core' ); }
		public function get_group(): array { return array( 'xva' ); }
		public function get_categories(): array { return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY ); }
		protected function register_controls(): void {
			$this->add_control( 'field_name', array( 'label' => esc_html__( 'ACF image field name', 'xva-gallery-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'portrait' ) );
		}
		public function get_value( array $options = array() ) {
			$value = xva_core_get_field( $this->get_settings( 'field_name' ), get_the_ID() );
			if ( is_array( $value ) ) {
				return array( 'id' => isset( $value['ID'] ) ? (int) $value['ID'] : ( isset( $value['id'] ) ? (int) $value['id'] : 0 ), 'url' => isset( $value['url'] ) ? $value['url'] : '' );
			}
			if ( is_numeric( $value ) ) {
				return array( 'id' => (int) $value, 'url' => wp_get_attachment_image_url( (int) $value, 'large' ) );
			}
			return array( 'id' => 0, 'url' => is_string( $value ) ? $value : '' );
		}
		public function render(): void {
			$value = $this->get_value();
			if ( ! empty( $value['url'] ) ) {
				echo esc_url( $value['url'] );
			}
		}
	}
}
