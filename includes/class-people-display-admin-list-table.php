<?php
/**
 * `WP_List_Table` is technically private, so using it may be frowned upon.
 * Since it might be the best way forward, I'm going to use it regardless -
 * if nothing else, we can ship our own copy of the class with this plugin.
 *
 * Sourced from https://wordpress.org/plugins/custom-list-table-example/
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WSUWP_People_Display_Admin_List_Table extends WP_List_Table {
	/**
	 * @var WSUWP_People_Display_Admin_List_Table
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.0.1
	 *
	 * @return \WSUWP_People_Display_Admin_List_Table
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_Display_Admin_List_Table();
			self::$instance->__construct();
		}
		return self::$instance;
	}

	/**
	 * Set up defaults using the referenced parent constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'wsu-person', // Singular name of the listed records
			'plural' => 'wsu-people', // Plural name of the listed records
			'ajax' => false, // Does this table support ajax?
		) );
	}

	/**
	 * Catch-all for any columns without a dedicated method.
	 * We may not need any other columns, though.
	 *
	 * @param array $item An individual item.
	 * @param array $column_name The name/slug of the column to be processed.
	 *
	 * @return string HTML to be placed inside the column <td>
	 */
	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'email':
				return $item->$column_name;
			case 'classification':
				return implode( ', ', $item->$column_name );
			default:
				return '';
		}
	}

	/**
	 * Render the 'cb' column. (Required if displaying checkboxes/using bulk actions.)
	 *
	 * @param array $item An individual item.
	 *
	 * @return string HTML to output.
	 */
	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->id );
	}

	/**
	 * Render the 'Name' column.
	 *
	 * @param array $item An individual item.
	 *
	 * @return string HTML to output.
	 */
	function column_name( $item ) {
		$actions = array(
			'edit' => sprintf( '<a href="?page=%s&action=%s&person=%s">Edit</a>', $_REQUEST['page'], 'edit', $item->id ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&person=%s">Delete</a>', $_REQUEST['page'], 'delete', $item->id ),
		);

		return sprintf(
			'%1$s %2$s',
			sprintf(
				'<a href="?page=%s&action=%s&person=%s">%s</a>',
				$_REQUEST['page'],
				'edit',
				$item->id,
				$item->title->rendered
			),
			$this->row_actions( $actions )
		);
	}



	/**
	 * Table columns and titles. (We can remove the 'cb' entry if we don't need bulk actions.)
	 *
	 * @return array List table columns to display.
	 */
	function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />', // Render a checkbox instead of text
			'name' => 'Name',
			// The following two are just for testing purposes.
			'email' => 'Email',
			'classification' => 'Classification',
		);

		$user = get_current_user_id();
		$screen = get_current_screen();
		$option = $screen->get_option( 'per_page', 'option' );
		$per_page = get_user_meta( $user, $option, true );

		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		return $columns;
	}

	/**
	 * Register columns to be sortable.
	 *
	 * @return array All the columns that should be sortable
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'title' => array( 'title', false ), // true means it's already sorted
		);

		return $sortable_columns;
	}

	/**
	 * Define bulk actions for the people list table. (Optional.)
	 *
	 * @return array An associative array of bulk actions.
	 */
	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete',
		);

		return $actions;
	}

	/**
	 * Bulk action handling. (Optional.)
	 */
	function process_bulk_action() {

		if ( 'delete' === $this->current_action() ) {
			wp_die( 'Items deleted (or they would be if we had items to delete)!' );
		}

	}

	/**
	 * Prepare people data for display.
	 */
	function prepare_items() {
		// Determine how many records to show per page.
		$user = get_current_user_id();
		$screen = get_current_screen();
		$option = $screen->get_option( 'per_page', 'option' );
		$per_page = get_user_meta( $user, $option, true );

		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		// Define column headers.
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		// Create an array of column headers to be used by the class.
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Handle bulk actions.
		//$this->process_bulk_action();

		/**
		 * @todo Retrieve existing content from cache if available.
		 */

		// Otherwise, call people.wsu.edu
		$request_url = 'https://people.wsu.edu/wp-json/wp/v2/people';

		/**
		 * @todo Add query args.
		 */

		$response = wp_remote_get( esc_url( $request_url ) );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			return '';
		}

		$people = json_decode( $data );

		if ( empty( $people ) ) {
			return '';
		}

		/**
		 * @todo Store data in cache for repeated use.
		 */

		$current_page = $this->get_pagenum();
		$total_people = count( $people );
		$people = array_slice( $people, ( ( $current_page - 1 ) * $per_page ), $per_page );

		// Add people data to the items property so it can be used by the rest of the class.
		$this->items = $people;

		// Register pagination options and calculations.
		$this->set_pagination_args( array(
			'total_items' => $total_people,
			'per_page' => $per_page,
			'total_pages' => ceil( $total_people / $per_page ),
		) );
	}
}
