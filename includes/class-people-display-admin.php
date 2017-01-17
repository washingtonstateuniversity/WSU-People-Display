<?php

class WSUWP_People_Display_Admin {
	/**
	 * @var WSUWP_People_Display_Admin
	 *
	 * @since 0.0.1
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.0.1
	 *
	 * @return \WSUWP_People_Display_Admin
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_Display_Admin();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks to include.
	 *
	 * @since 0.0.1
	 */
	public function setup_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );

		if ( ! empty( $_GET['page'] ) && 'wsu-people' === $_GET['page'] ) {
			add_filter( 'set-screen-option', array( $this, 'list_table_page_set_option' ), 10, 3 );
		}
	}

	/**
	 * Create an admin page for the people list table.
	 *
	 * @since 0.0.1
	 */
	public function add_admin_page() {
		$hook = add_menu_page(
			'People',
			'People Directory',
			'manage_options',
			'wsu-people',
			array( $this, 'display_admin_page' ),
			'dashicons-groups',
			58
		);

		add_action( "load-$hook", array( $this, 'list_table_page_options' ) );
	}

	/**
	 * Display the appropriate view for the admin page.
	 *
	 * @since 0.0.1
	 */
	function display_admin_page() {
		?>
		<div class="wrap">
			<?php
			if ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['person'] ) ) {
				$id = absint( $_GET['person'] );

				$this->display_profile( $id );
			} else {
				$this->display_list_table();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Display the people list table.
	 *
	 * @since 0.0.1
	 */
	function display_list_table() {
		$wsu_people_list_table = WSUWP_People_Display_Admin_List_Table();
		$wsu_people_list_table->prepare_items();
		?>
		<h1 class="wp-heading-inline">WSU People Directory</h1>

		<!-- Wrap the table in a form to use features like bulk actions -->
		<form id="wsuwp-people-filter" method="get">
			<!-- Ensure that the form posts back to our current page -->
			<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
			<!-- Render the list table -->
			<?php $wsu_people_list_table->display(); ?>
		</form>
		<?php
	}

	/**
	 * Display the profile editing interface.
	 *
	 * @since 0.0.1
	 */
	function display_profile( $id ) {
		?>
		<h1 class="wp-heading-inline">Edit Profile</h1>
		<?php
		$request_url = 'https://people.wsu.edu/wp-json/wp/v2/people/' . $id;

		$response = wp_remote_get( $request_url );

		if ( is_wp_error( $response ) ) {
			echo '<!-- ' . sanitize_text_field( $response->get_error_message() ) . ' -->';
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			echo '<!-- empty -->';
		}

		$person = json_decode( $data );

		if ( empty( $person ) ) {
			echo '<!-- empty -->';
		}

		?>
		<p>Here's where you would edit <?php echo esc_html( $person->title->rendered ); ?>'s profile.</p>
		<p>Finding the best way to pull in the profile interface will be a fun challenge.</p>
		<?php
	}

	/**
	 * Add screen options to the list table page.
	 *
	 * @since 0.0.1
	 */
	public function list_table_page_options() {
		$option = 'per_page';
		$args = array(
			'label' => 'Number of people per page:',
			'default' => 20,
			'option' => 'wsu_people_per_page',
		);

		if ( ! isset( $_GET['action'] ) && ! isset( $_GET['person'] ) ) {
			add_screen_option( $option, $args );
		}
	}

	/**
	 * Validate 'Number of people per page' screen option on update.
	 *
	 * @since 0.0.1
	 *
	 * @return
	 */
	function list_table_page_set_option( $status, $option, $value ) {
		if ( 'wsu_people_per_page' === $option ) {
			return absint( $value );
		}

		return $status;
	}
}
