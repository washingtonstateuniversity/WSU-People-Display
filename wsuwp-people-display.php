<?php
/*
Plugin Name: WSU People Display
Version: 0.0.1
Description: Displays people from the WSU People Directory.
Author: washingtonstateuniversity, philcable
Author URI: https://web.wsu.edu/
Plugin URI: https://github.com/washingtonstateuniversity/WSU-People-Display
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// The core plugin class.
require dirname( __FILE__ ) . '/includes/class-wsuwp-people-display.php';

// Flush rewrite rules on activation or deactivation.
// We may want to handle this with a bit more intent...
register_activation_hook( __FILE__, 'flush_rewrite_rules' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

add_action( 'after_setup_theme', 'WSUWP_People_Display' );
/**
 * Start things up.
 *
 * @return \WSUWP_People_Display
 */
function WSUWP_People_Display() {
	return WSUWP_People_Display::get_instance();
}

/**
 * Retrieve the version number of the plugin for script enqueues.
 *
 * @return string
 */
function WSUWP_People_Display_Version() {
	return WSUWP_People_Display::$version;
}

/**
 * Retrieve the instance of the admin page.
 *
 * @return WSUWP_People_Display_Admin
 */
function WSUWP_People_Display_Admin() {
	return WSUWP_People_Display_Admin::get_instance();
}

/**
 * Retrieve the instance of the list table.
 *
 * @return WSUWP_People_Display_Admin_List_Table
 */
function WSUWP_People_Display_Admin_List_Table() {
	return WSUWP_People_Display_Admin_List_Table::get_instance();
}

/**
 * Retrieve the instance of the settings page.
 *
 * @return WSUWP_People_Display_Admin_Settings
 */
function WSUWP_People_Display_Admin_Settings() {
	return WSUWP_People_Display_Admin_Settings::get_instance();
}

/**
 * Retrieve the instance of the frontend handler.
 *
 * @return WSUWP_People_Display_Settings
 */
function WSUWP_People_Display_Frontend() {
	return WSUWP_People_Display_Frontend::get_instance();
}
