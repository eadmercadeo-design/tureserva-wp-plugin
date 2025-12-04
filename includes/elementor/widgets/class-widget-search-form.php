<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * TuReserva Search Form Widget
 */
class TuReserva_Widget_Search_Form extends \Elementor\Widget_Base {

	public function get_name() {
		return 'tureserva_search_form';
	}

	public function get_title() {
		return esc_html__( 'Buscador de Disponibilidad', 'tureserva' );
	}

	public function get_icon() {
		return 'eicon-search';
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

		$this->add_control(
			'title',
			[
				'label' => esc_html__( 'Título', 'tureserva' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Buscar Alojamiento', 'tureserva' ),
				'placeholder' => esc_html__( 'Escribe un título', 'tureserva' ),
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		echo '<div class="tureserva-elementor-search-form">';
		
		if ( ! empty( $settings['title'] ) ) {
			echo '<h3>' . esc_html( $settings['title'] ) . '</h3>';
		}

		// Use the existing shortcode to render the form
		echo do_shortcode( '[tureserva_buscador]' );

		echo '</div>';
	}

}
