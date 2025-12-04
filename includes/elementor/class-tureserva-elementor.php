<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * TuReserva Elementor Integration
 */
class TuReserva_Elementor {

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 * @var TuReserva_Elementor The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 * @return TuReserva_Elementor An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );
	}

	/**
	 * Register Widgets
	 *
	 * Load widgets files and register them with Elementor.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {

		require_once( __DIR__ . '/widgets/class-widget-search-form.php' );
		require_once( __DIR__ . '/widgets/class-widget-accommodation-list.php' );
		require_once( __DIR__ . '/widgets/class-widget-single-accommodation.php' );

		$widgets_manager->register( new \TuReserva_Widget_Search_Form() );
		$widgets_manager->register( new \TuReserva_Widget_Accommodation_List() );
		$widgets_manager->register( new \TuReserva_Widget_Single_Accommodation() );

	}

	/**
	 * Register Categories
	 *
	 * Register new categories for widgets.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 */
	public function register_categories( $elements_manager ) {

		$elements_manager->add_category(
			'tureserva',
			[
				'title' => esc_html__( 'TuReserva', 'tureserva' ),
				'icon'  => 'fa fa-plug',
			]
		);

	}

}

// Initialize the class
TuReserva_Elementor::instance();
