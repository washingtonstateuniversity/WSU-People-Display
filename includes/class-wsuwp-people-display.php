<?php

class WSUWP_People_Display {
	/**
	 * @var WSUWP_People_Display
	 *
	 * @since 0.0.1
	 */
	private static $instance;

	/**
	 * Tracks the version number of the plugin for script enqueues.
	 *
	 * @since 0.0.1
	 *
	 * @var string
	 */
	public static $version = '0.0.1';

	/**
	 * Maintain and return the one instance. Initiate hooks when called the first time.
	 *
	 * @since 0.0.1
	 *
	 * @return \WSUWP_People_Display
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_People_Display();
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
		require_once( dirname( __FILE__ ) . '/class-people-display-admin.php' );
		require_once( dirname( __FILE__ ) . '/class-people-display-admin-list-table.php' );
		require_once( dirname( __FILE__ ) . '/class-people-display-admin-settings.php' );
		require_once( dirname( __FILE__ ) . '/class-people-display-frontend.php' );

		add_action( 'init', 'WSUWP_People_Display_Admin' );
		add_action( 'init', 'WSUWP_People_Display_Admin_Settings' );
		add_action( 'init', 'WSUWP_People_Display_Frontend' );
	}
}
