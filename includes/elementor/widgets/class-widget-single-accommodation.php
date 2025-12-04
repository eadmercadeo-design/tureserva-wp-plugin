<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * TuReserva Single Accommodation Widget
 */
class TuReserva_Widget_Single_Accommodation extends \Elementor\Widget_Base {

	public function get_name() {
		return 'tureserva_single_accommodation';
	}

	public function get_title() {
		return esc_html__( 'Alojamiento Individual', 'tureserva' );
	}

	public function get_icon() {
		return 'eicon-single-product';
	}

	public function get_categories() {
		return [ 'tureserva' ];
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Contenido', 'tureserva' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		// Fetch all accommodations for the select control
		$options = [];
		$accommodations = get_posts( [
			'post_type' => 'trs_alojamiento',
			'posts_per_page' => -1,
			'post_status' => 'publish',
		] );

		foreach ( $accommodations as $accommodation ) {
			$options[ $accommodation->ID ] = $accommodation->post_title;
		}

		$this->add_control(
			'accommodation_id',
			[
				'label' => esc_html__( 'Seleccionar Alojamiento', 'tureserva' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'options' => $options,
				'default' => ! empty( $options ) ? array_key_first( $options ) : '',
				'label_block' => true,
			]
		);

		$this->add_control(
			'show_image',
			[
				'label' => esc_html__( 'Mostrar Imagen', 'tureserva' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Sí', 'tureserva' ),
				'label_off' => esc_html__( 'No', 'tureserva' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_price',
			[
				'label' => esc_html__( 'Mostrar Precio', 'tureserva' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Sí', 'tureserva' ),
				'label_off' => esc_html__( 'No', 'tureserva' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$accommodation_id = $settings['accommodation_id'];

		if ( ! $accommodation_id ) {
			return;
		}

		$post = get_post( $accommodation_id );

		if ( ! $post || $post->post_type !== 'trs_alojamiento' ) {
			echo '<p>' . esc_html__( 'Alojamiento no encontrado.', 'tureserva' ) . '</p>';
			return;
		}

		echo '<div class="tureserva-single-accommodation">';
		
		if ( 'yes' === $settings['show_image'] ) {
			$image = get_the_post_thumbnail_url( $accommodation_id, 'large' );
			if ( $image ) {
				echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $post->post_title ) . '" style="width: 100%; height: auto; border-radius: 8px; margin-bottom: 15px;">';
			}
		}

		echo '<h3>' . esc_html( $post->post_title ) . '</h3>';
		echo '<div class="tureserva-accommodation-description">' . wpautop( $post->post_content ) . '</div>';

		if ( 'yes' === $settings['show_price'] ) {
			$price = get_post_meta( $accommodation_id, 'precio_base', true );
			if ( $price ) {
				echo '<p class="price" style="font-size: 1.2em; font-weight: bold; color: #333;">Desde: $' . esc_html( $price ) . '</p>';
			}
		}

		echo '<a href="' . get_permalink( $accommodation_id ) . '" class="button" style="display: inline-block; padding: 10px 20px; background: #0073aa; color: #fff; text-decoration: none; border-radius: 4px; margin-top: 10px;">' . esc_html__( 'Reservar Ahora', 'tureserva' ) . '</a>';

		echo '</div>';
	}

}
