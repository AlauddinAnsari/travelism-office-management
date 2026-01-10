<?php
/**
 * Plugin Name: Travelism Office Management
 * Plugin URI: https://travelism.com
 * Description: Comprehensive office management system for travel agencies - manage customers, services, visas, and tasks efficiently.
 * Version: 1.0.0
 * Author: Travelism Team
 * Author URI: https://travelism.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: travelism-office-management
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Network: false
 *
 * @package Travelism_Office_Management
 * @author Travelism Team
 * @copyright 2026 Travelism
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'TRAVELISM_PLUGIN_FILE', __FILE__ );
define( 'TRAVELISM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TRAVELISM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TRAVELISM_PLUGIN_VERSION', '1.0.0' );
define( 'TRAVELISM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'TRAVELISM_PLUGIN_TEXT_DOMAIN', 'travelism-office-management' );

// Database table constants.
define( 'TRAVELISM_CUSTOMERS_TABLE', 'travelism_customers' );
define( 'TRAVELISM_SERVICES_TABLE', 'travelism_services' );
define( 'TRAVELISM_VISA_TABLE', 'travelism_visa' );
define( 'TRAVELISM_TASKS_TABLE', 'travelism_tasks' );
define( 'TRAVELISM_ACTIVITIES_LOG_TABLE', 'travelism_activities_log' );
define( 'TRAVELISM_NOTIFICATIONS_TABLE', 'travelism_notifications' );

/**
 * Autoloader for plugin classes
 *
 * @param string $class Class name to load.
 * @return void
 */
function travelism_autoloader( $class ) {
	// Only load classes with our namespace.
	if ( strpos( $class, 'Travelism_' ) === false ) {
		return;
	}

	// Remove the namespace prefix.
	$class_name = str_replace( 'Travelism_', '', $class );
	$class_name = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name );

	// Build the file path.
	$file_path = TRAVELISM_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR . 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

	// Load the file if it exists.
	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
}

// Register autoloader.
spl_autoload_register( 'travelism_autoloader' );

/**
 * Load the main plugin class
 */
require_once TRAVELISM_PLUGIN_DIR . 'includes/class-travelism-plugin.php';

/**
 * Plugin activation hook
 *
 * @return void
 */
function travelism_activate() {
	// Check user capabilities.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	// Initialize the plugin instance.
	$plugin = Travelism_Plugin::get_instance();
	$plugin->activate();

	// Set activation transient for admin notice.
	set_transient( 'travelism_activated', true, 5 * 60 );
}

/**
 * Plugin deactivation hook
 *
 * @return void
 */
function travelism_deactivate() {
	// Check user capabilities.
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	// Initialize the plugin instance.
	$plugin = Travelism_Plugin::get_instance();
	$plugin->deactivate();
}

// Register activation and deactivation hooks.
register_activation_hook( TRAVELISM_PLUGIN_FILE, 'travelism_activate' );
register_deactivation_hook( TRAVELISM_PLUGIN_FILE, 'travelism_deactivate' );

/**
 * Initialize the plugin
 *
 * @return void
 */
function travelism_init() {
	// Load plugin text domain for translations.
	load_plugin_textdomain(
		TRAVELISM_PLUGIN_TEXT_DOMAIN,
		false,
		dirname( TRAVELISM_PLUGIN_BASENAME ) . '/languages/'
	);

	// Initialize the plugin.
	Travelism_Plugin::get_instance();
}

// Hook into WordPress init.
add_action( 'plugins_loaded', 'travelism_init' );
