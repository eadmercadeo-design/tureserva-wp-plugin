<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * TuReserva Accommodation List Widget
 */
class TuReserva_Widget_Accommodation_List extends \Elementor\Widget_Base {

	public function get_name() {
		return 'tureserva_accommodation_list';
	}

	public function get_title() {
		return esc_html__( 'Lista de Alojamientos', 'tureserva' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
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
			'posts_per_page',
			[
				'label' => esc_html__( 'Cantidad a mostrar', 'tureserva' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$args = [
			'post_type' => 'trs_alojamiento',
			'posts_per_page' => $settings['posts_per_page'],
			'post_status' => 'publish',
		];

		$query = new \WP_Query( $args );

		if ( $query->have_posts() ) {
			echo '<div class="tureserva-accommodation-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
				$price = get_post_meta( get_the_ID(), 'precio_base', true );
				
				echo '<div class="tureserva-accommodation-item" style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">';
				if ( $image ) {
					echo '<img src="' . esc_url( $image ) . '" alt="' . get_the_title() . '" style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px; margin-bottom: 10px;">';
				}
				echo '<h3 style="margin: 0 0 10px;">' . get_the_title() . '</h3>';
				// echo '<p>' . wp_trim_words( get_the_excerpt(), 20 ) . '</p>';
				if ( $price ) {
					echo '<p style="font-weight: bold; color: #333;">Desde: $' . esc_html( $price ) . '</p>';
				}
				echo '<a href="' . get_permalink() . '" class="button" style="display: inline-block; padding: 8px 16px; background: #0073aa; color: #fff; text-decoration: none; border-radius: 4px;">Ver Detalles</a>';
				echo '</div>';
			}
			echo '</div>';
			wp_reset_postdata();
		} else {
			echo '<p>' . esc_html__( 'No hay alojamientos disponibles.', 'tureserva' ) . '</p>';
		}
	}

}
