<?php
/**
 * Main Plugin Class - Travelism Office Management
 *
 * Handles plugin initialization, module setup, hooks registration,
 * and admin menu configuration.
 *
 * @package TravelismOfficeManagement
 * @subpackage Includes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Travelism_Plugin
 *
 * Main plugin class that orchestrates all plugin functionality.
 *
 * @since 1.0.0
 */
class Travelism_Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var Travelism_Plugin
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Plugin name.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $plugin_name = 'travelism-office-management';

	/**
	 * Plugin version.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $version = '1.0.0';

	/**
	 * Plugin text domain.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $text_domain = 'travelism-office-management';

	/**
	 * Loaded modules.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private $modules = array();

	/**
	 * Get plugin instance (Singleton).
	 *
	 * @return Travelism_Plugin
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0.0
	 */
	private function init() {
		// Load text domain
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Load modules
		add_action( 'plugins_loaded', array( $this, 'load_modules' ), 5 );

		// Register hooks
		add_action( 'plugins_loaded', array( $this, 'register_hooks' ), 10 );

		// Setup admin menu
		add_action( 'admin_menu', array( $this, 'setup_admin_menu' ) );

		// Enqueue admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Activation and deactivation hooks
		register_activation_hook( TRAVELISM_PLUGIN_FILE, array( $this, 'on_activation' ) );
		register_deactivation_hook( TRAVELISM_PLUGIN_FILE, array( $this, 'on_deactivation' ) );
	}

	/**
	 * Load plugin text domain.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			$this->text_domain,
			false,
			dirname( plugin_basename( TRAVELISM_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Load all plugin modules.
	 *
	 * @since 1.0.0
	 */
	public function load_modules() {
		$modules = array(
			'Travelism_Settings',
			'Travelism_Dashboard',
			'Travelism_Employees',
			'Travelism_Projects',
			'Travelism_Tasks',
			'Travelism_Reports',
		);

		/**
		 * Filter the list of modules to load.
		 *
		 * @param array $modules List of module class names to load.
		 * @since 1.0.0
		 */
		$modules = apply_filters( 'travelism_modules', $modules );

		foreach ( $modules as $module ) {
			if ( class_exists( $module ) ) {
				$this->modules[ $module ] = new $module();
			}
		}

		/**
		 * Action fired after all modules are loaded.
		 *
		 * @param Travelism_Plugin $plugin Plugin instance.
		 * @since 1.0.0
		 */
		do_action( 'travelism_modules_loaded', $this );
	}

	/**
	 * Register all hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks() {
		// Initialize all loaded modules
		foreach ( $this->modules as $module ) {
			if ( method_exists( $module, 'init' ) ) {
				$module->init();
			}
		}

		/**
		 * Action fired after all hooks are registered.
		 *
		 * @param Travelism_Plugin $plugin Plugin instance.
		 * @since 1.0.0
		 */
		do_action( 'travelism_hooks_registered', $this );
	}

	/**
	 * Setup admin menu structure.
	 *
	 * @since 1.0.0
	 */
	public function setup_admin_menu() {
		// Main plugin menu
		add_menu_page(
			esc_html__( 'Travelism Office', 'travelism-office-management' ),
			esc_html__( 'Travelism Office', 'travelism-office-management' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'render_admin_page' ),
			'dashicons-briefcase',
			25
		);

		// Dashboard submenu
		add_submenu_page(
			$this->plugin_name,
			esc_html__( 'Dashboard', 'travelism-office-management' ),
			esc_html__( 'Dashboard', 'travelism-office-management' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'render_admin_page' )
		);

		// Employees submenu
		add_submenu_page(
			$this->plugin_name,
			esc_html__( 'Employees', 'travelism-office-management' ),
			esc_html__( 'Employees', 'travelism-office-management' ),
			'manage_options',
			$this->plugin_name . '-employees',
			array( $this, 'render_admin_page' )
		);

		// Projects submenu
		add_submenu_page(
			$this->plugin_name,
			esc_html__( 'Projects', 'travelism-office-management' ),
			esc_html__( 'Projects', 'travelism-office-management' ),
			'manage_options',
			$this->plugin_name . '-projects',
			array( $this, 'render_admin_page' )
		);

		// Tasks submenu
		add_submenu_page(
			$this->plugin_name,
			esc_html__( 'Tasks', 'travelism-office-management' ),
			esc_html__( 'Tasks', 'travelism-office-management' ),
			'manage_options',
			$this->plugin_name . '-tasks',
			array( $this, 'render_admin_page' )
		);

		// Reports submenu
		add_submenu_page(
			$this->plugin_name,
			esc_html__( 'Reports', 'travelism-office-management' ),
			esc_html__( 'Reports', 'travelism-office-management' ),
			'manage_options',
			$this->plugin_name . '-reports',
			array( $this, 'render_admin_page' )
		);

		// Settings submenu
		add_submenu_page(
			$this->plugin_name,
			esc_html__( 'Settings', 'travelism-office-management' ),
			esc_html__( 'Settings', 'travelism-office-management' ),
			'manage_options',
			$this->plugin_name . '-settings',
			array( $this, 'render_admin_page' )
		);

		/**
		 * Action fired after admin menu is set up.
		 *
		 * @param Travelism_Plugin $plugin Plugin instance.
		 * @since 1.0.0
		 */
		do_action( 'travelism_admin_menu_setup', $this );
	}

	/**
	 * Render admin page.
	 *
	 * Routes to appropriate module based on current page.
	 *
	 * @since 1.0.0
	 */
	public function render_admin_page() {
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : $this->plugin_name;

		/**
		 * Filter to allow custom admin page rendering.
		 *
		 * @param string $page Current page slug.
		 * @since 1.0.0
		 */
		$page = apply_filters( 'travelism_admin_page', $page );

		// Route to appropriate module renderer
		switch ( $page ) {
			case $this->plugin_name:
			case $this->plugin_name . '-dashboard':
				$this->render_dashboard();
				break;

			case $this->plugin_name . '-employees':
				do_action( 'travelism_render_employees' );
				break;

			case $this->plugin_name . '-projects':
				do_action( 'travelism_render_projects' );
				break;

			case $this->plugin_name . '-tasks':
				do_action( 'travelism_render_tasks' );
				break;

			case $this->plugin_name . '-reports':
				do_action( 'travelism_render_reports' );
				break;

			case $this->plugin_name . '-settings':
				do_action( 'travelism_render_settings' );
				break;

			default:
				/**
				 * Action fired for custom admin pages.
				 *
				 * @param string $page Current page slug.
				 * @since 1.0.0
				 */
				do_action( 'travelism_render_admin_page_' . $page );
				break;
		}
	}

	/**
	 * Render dashboard page
	 *
	 * @since 1.0.0
	 */
	private function render_dashboard() {
		$dashboard_file = TRAVELISM_PLUGIN_DIR . 'includes/Admin/views/dashboard.php';
		if ( file_exists( $dashboard_file ) ) {
			require_once $dashboard_file;
		}
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_assets() {
		if ( ! isset( $_GET['page'] ) || strpos( sanitize_key( $_GET['page'] ), $this->plugin_name ) === false ) {
			return;
		}

		// Enqueue admin CSS
		wp_enqueue_style(
			$this->plugin_name . '-admin-main',
			TRAVELISM_PLUGIN_URL . 'assets/css/admin-main.css',
			array(),
			$this->version
		);

		wp_enqueue_style(
			$this->plugin_name . '-dashboard',
			TRAVELISM_PLUGIN_URL . 'assets/css/dashboard.css',
			array( $this->plugin_name . '-admin-main' ),
			$this->version
		);

		wp_enqueue_style(
			$this->plugin_name . '-brand',
			TRAVELISM_PLUGIN_URL . 'assets/css/brand-style.css',
			array(),
			$this->version
		);

		// Enqueue Chart.js from CDN
		wp_enqueue_script(
			'chart-js',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
			array(),
			'4.4.0',
			true
		);

		// Enqueue admin JS
		wp_enqueue_script(
			$this->plugin_name . '-admin-main',
			TRAVELISM_PLUGIN_URL . 'assets/js/admin-main.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_enqueue_script(
			$this->plugin_name . '-modal-handler',
			TRAVELISM_PLUGIN_URL . 'assets/js/modal-handler.js',
			array( 'jquery', $this->plugin_name . '-admin-main' ),
			$this->version,
			true
		);

		wp_enqueue_script(
			$this->plugin_name . '-chart-manager',
			TRAVELISM_PLUGIN_URL . 'assets/js/chart-manager.js',
			array( 'jquery', 'chart-js' ),
			$this->version,
			true
		);

		wp_enqueue_script(
			$this->plugin_name . '-dashboard',
			TRAVELISM_PLUGIN_URL . 'assets/js/dashboard.js',
			array( 'jquery', $this->plugin_name . '-admin-main' ),
			$this->version,
			true
		);

		wp_enqueue_script(
			$this->plugin_name . '-dashboard-charts',
			TRAVELISM_PLUGIN_URL . 'assets/js/dashboard-charts.js',
			array( 'jquery', $this->plugin_name . '-chart-manager' ),
			$this->version,
			true
		);

		// Localize script
		wp_localize_script(
			$this->plugin_name . '-admin-main',
			'travelismAdmin',
			array(
				'nonce'       => wp_create_nonce( 'travelism_nonce' ),
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'pluginUrl'   => TRAVELISM_PLUGIN_URL,
				'pluginDir'   => TRAVELISM_PLUGIN_DIR,
			)
		);

		/**
		 * Action fired after admin assets are enqueued.
		 *
		 * @param Travelism_Plugin $plugin Plugin instance.
		 * @since 1.0.0
		 */
		do_action( 'travelism_enqueue_admin_assets', $this );
	}

	/**
	 * Plugin activation hook.
	 *
	 * @since 1.0.0
	 */
	public function on_activation() {
		// Create necessary tables
		$this->create_plugin_tables();

		// Set default options
		$this->set_default_options();

		// Fire activation hooks for modules
		foreach ( $this->modules as $module ) {
			if ( method_exists( $module, 'on_activation' ) ) {
				$module->on_activation();
			}
		}

		/**
		 * Action fired on plugin activation.
		 *
		 * @param Travelism_Plugin $plugin Plugin instance.
		 * @since 1.0.0
		 */
		do_action( 'travelism_plugin_activated', $this );

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation hook.
	 *
	 * @since 1.0.0
	 */
	public function on_deactivation() {
		// Fire deactivation hooks for modules
		foreach ( $this->modules as $module ) {
			if ( method_exists( $module, 'on_deactivation' ) ) {
				$module->on_deactivation();
			}
		}

		/**
		 * Action fired on plugin deactivation.
		 *
		 * @param Travelism_Plugin $plugin Plugin instance.
		 * @since 1.0.0
		 */
		do_action( 'travelism_plugin_deactivated', $this );

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Create necessary plugin tables.
	 *
	 * @since 1.0.0
	 */
	private function create_plugin_tables() {
		// Use database class to create tables
		Travelism_Database::create_tables();

		/**
		 * Action fired to allow modules to create their own tables.
		 *
		 * @param wpdb $wpdb WordPress database instance.
		 * @since 1.0.0
		 */
		do_action( 'travelism_create_tables', $GLOBALS['wpdb'] );
	}

	/**
	 * Set default plugin options.
	 *
	 * @since 1.0.0
	 */
	private function set_default_options() {
		$default_options = array(
			'plugin_version'   => $this->version,
			'installed_at'     => current_time( 'mysql' ),
		);

		/**
		 * Filter default plugin options.
		 *
		 * @param array $default_options Default options.
		 * @since 1.0.0
		 */
		$default_options = apply_filters( 'travelism_default_options', $default_options );

		foreach ( $default_options as $option_key => $option_value ) {
			if ( ! get_option( 'travelism_' . $option_key ) ) {
				add_option( 'travelism_' . $option_key, $option_value );
			}
		}
	}

	/**
	 * Get a loaded module.
	 *
	 * @param string $module_name Module class name.
	 * @return object|null Module instance or null if not found.
	 * @since 1.0.0
	 */
	public function get_module( $module_name ) {
		return isset( $this->modules[ $module_name ] ) ? $this->modules[ $module_name ] : null;
	}

	/**
	 * Get all loaded modules.
	 *
	 * @return array Array of loaded modules.
	 * @since 1.0.0
	 */
	public function get_modules() {
		return $this->modules;
	}

	/**
	 * Check if a module is loaded.
	 *
	 * @param string $module_name Module class name.
	 * @return bool True if module is loaded, false otherwise.
	 * @since 1.0.0
	 */
	public function has_module( $module_name ) {
		return isset( $this->modules[ $module_name ] );
	}

	/**
	 * Prevent cloning of the class.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning this class is not allowed.', 'travelism-office-management' ), '1.0.0' );
	}

	/**
	 * Prevent unserializing of the class.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is not allowed.', 'travelism-office-management' ), '1.0.0' );
	}
}

/**
 * Get plugin instance.
 *
 * @return Travelism_Plugin
 * @since 1.0.0
 */
function travelism_get_plugin() {
	return Travelism_Plugin::get_instance();
}
