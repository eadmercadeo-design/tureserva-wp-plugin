<?php
/**
 * Base class for displaying a list of items in an HTML table.
 *
 * @package WordPress
 * @subpackage Administration
 * @since 3.1.0
 */

if ( ! class_exists( 'WP_List_Table', false ) ) :
require_once ABSPATH . 'wp-includes/class-wp-sqlite-compatibility.php';

abstract class WP_List_Table {

	/** @var string Singular label for an item. */
	protected $singular = '';

	/** @var string Plural label for items. */
	protected $plural = '';

	/** @var bool Whether the table supports AJAX. */
	protected $ajax = false;

	/** @var array List of columns. */
	protected $columns = array();

	/** @var array Hidden columns. */
	protected $hidden = array();

	/** @var array Sortable columns. */
	protected $sortable = array();

	/** @var array Items for the current page. */
	protected $items = array();

	/** @var array Column headers for display. */
	protected $_column_headers = array();

	/** @var string The current screen. */
	protected $screen;

	/** @var string The current page number. */
	protected $current_page;

	/** @var int Total items for pagination. */
	protected $total_items = 0;

	/**
	 * Constructor.
	 *
	 * @param array|string $args {
	 *     Array or string of arguments.
	 *
	 *     @type string $plural   Plural label for items.
	 *     @type string $singular Singular label for an item.
	 *     @type bool   $ajax     Whether the table supports AJAX.
	 * }
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'plural'   => '',
				'singular' => '',
				'ajax'     => false,
			)
		);

		$this->plural   = $args['plural'];
		$this->singular = $args['singular'];
		$this->ajax     = $args['ajax'];

		if ( isset( $args['screen'] ) ) {
			$this->screen = $args['screen'];
		} else {
			$this->screen = get_current_screen();
		}
	}

	/**
	 * Returns the list of columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return $this->columns;
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @return void
	 */
	public function prepare_items() {}

	/**
	 * Displays the list table.
	 *
	 * @return void
	 */
	public function display() {
		$this->display_tablenav( 'top' );

		echo '<table class="wp-list-table widefat fixed striped">';
		$this->print_column_headers();
		echo '<tbody id="the-list">';
		$this->display_rows_or_placeholder();
		echo '</tbody>';
		echo '</table>';

		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Displays the table navigation above or below the table.
	 *
	 * @param string $which 'top' or 'bottom'.
	 */
	protected function display_tablenav( $which ) {
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>
			<br class="clear" />
		</div>
		<?php
	}

	/**
	 * Prints column headers, accounting for hidden and sortable columns.
	 *
	 * @param bool $with_id Whether to set the id attribute on the headers.
	 */
	protected function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable ) = $this->get_column_info();
		echo '<thead><tr>';
		foreach ( $columns as $column_key => $column_display_name ) {
			$classes = array( 'manage-column', "column-$column_key" );
			if ( in_array( $column_key, $hidden, true ) ) {
				$classes[] = 'hidden';
			}
			$class = 'class="' . esc_attr( implode( ' ', $classes ) ) . '"';
			echo "<th scope=\"col\" $class>$column_display_name</th>";
		}
		echo '</tr></thead>';
	}

	/**
	 * Displays rows or a placeholder if no items exist.
	 */
	protected function display_rows_or_placeholder() {
		if ( empty( $this->items ) ) {
			echo '<tr class="no-items"><td class="colspanchange" colspan="5">';
			echo __( 'No items found.' );
			echo '</td></tr>';
		} else {
			$this->display_rows();
		}
	}

	/**
	 * Displays the rows.
	 */
	protected function display_rows() {
		foreach ( $this->items as $item ) {
			$this->single_row( $item );
		}
	}

	/**
	 * Outputs a single row.
	 *
	 * @param object|array $item The current item.
	 */
	protected function single_row( $item ) {
		echo '<tr>';
		foreach ( $this->get_columns() as $column_name => $column_display_name ) {
			echo '<td>' . esc_html( $this->column_default( $item, $column_name ) ) . '</td>';
		}
		echo '</tr>';
	}

	/**
	 * Handles default column output.
	 *
	 * @param object|array $item        The current item.
	 * @param string       $column_name Current column name.
	 * @return mixed
	 */
	protected function column_default( $item, $column_name ) {
		return $item[ $column_name ] ?? '';
	}

	/**
	 * Prints pagination controls.
	 *
	 * @param string $which 'top' or 'bottom'.
	 */
	protected function pagination( $which ) {
		// For simplicity, this minimal version does not implement pagination.
	}
}
endif;
