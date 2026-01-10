<?php
/**
 * Admin Menu and Page Routing Class
 *
 * This class handles the admin menu structure and page routing for the Travelism Office Management system.
 * It manages menu items and their corresponding page routes for Dashboard, Customers, Services, Visa, Tasks, Analytics, and Settings.
 *
 * @package Travelism_Office_Management
 * @subpackage Admin
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Travelism_Admin_Menu {

	/**
	 * Menu items array
	 *
	 * @var array
	 */
	private $menu_items = array();

	/**
	 * Current page
	 *
	 * @var string
	 */
	private $current_page = '';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->init_menu_items();
		$this->setup_hooks();
	}

	/**
	 * Initialize menu items
	 *
	 * @since 1.0.0
	 */
	private function init_menu_items() {
		$this->menu_items = array(
			'dashboard' => array(
				'label'       => __( 'Dashboard', 'travelism-office-management' ),
				'slug'        => 'tom-dashboard',
				'icon'        => 'dashicons-dashboard',
				'position'    => 1,
				'capability'  => 'manage_options',
				'callback'    => array( $this, 'render_dashboard' ),
				'parent'      => null,
			),
			'customers' => array(
				'label'       => __( 'Customers', 'travelism-office-management' ),
				'slug'        => 'tom-customers',
				'icon'        => 'dashicons-groups',
				'position'    => 2,
				'capability'  => 'manage_tom_customers',
				'callback'    => array( $this, 'render_customers' ),
				'parent'      => null,
				'submenu'     => array(
					'all-customers' => array(
						'label'    => __( 'All Customers', 'travelism-office-management' ),
						'slug'     => 'tom-customers',
						'callback' => array( $this, 'render_customers' ),
					),
					'add-customer' => array(
						'label'    => __( 'Add New Customer', 'travelism-office-management' ),
						'slug'     => 'tom-add-customer',
						'callback' => array( $this, 'render_add_customer' ),
					),
				),
			),
			'services' => array(
				'label'       => __( 'Services', 'travelism-office-management' ),
				'slug'        => 'tom-services',
				'icon'        => 'dashicons-briefcase',
				'position'    => 3,
				'capability'  => 'manage_tom_services',
				'callback'    => array( $this, 'render_services' ),
				'parent'      => null,
				'submenu'     => array(
					'all-services' => array(
						'label'    => __( 'All Services', 'travelism-office-management' ),
						'slug'     => 'tom-services',
						'callback' => array( $this, 'render_services' ),
					),
					'add-service' => array(
						'label'    => __( 'Add New Service', 'travelism-office-management' ),
						'slug'     => 'tom-add-service',
						'callback' => array( $this, 'render_add_service' ),
					),
				),
			),
			'visa' => array(
				'label'       => __( 'Visa', 'travelism-office-management' ),
				'slug'        => 'tom-visa',
				'icon'        => 'dashicons-id',
				'position'    => 4,
				'capability'  => 'manage_tom_visa',
				'callback'    => array( $this, 'render_visa' ),
				'parent'      => null,
				'submenu'     => array(
					'all-visa' => array(
						'label'    => __( 'All Visa Applications', 'travelism-office-management' ),
						'slug'     => 'tom-visa',
						'callback' => array( $this, 'render_visa' ),
					),
					'add-visa' => array(
						'label'    => __( 'Add New Visa Application', 'travelism-office-management' ),
						'slug'     => 'tom-add-visa',
						'callback' => array( $this, 'render_add_visa' ),
					),
				),
			),
			'tasks' => array(
				'label'       => __( 'Tasks', 'travelism-office-management' ),
				'slug'        => 'tom-tasks',
				'icon'        => 'dashicons-clipboard',
				'position'    => 5,
				'capability'  => 'manage_tom_tasks',
				'callback'    => array( $this, 'render_tasks' ),
				'parent'      => null,
				'submenu'     => array(
					'all-tasks' => array(
						'label'    => __( 'All Tasks', 'travelism-office-management' ),
						'slug'     => 'tom-tasks',
						'callback' => array( $this, 'render_tasks' ),
					),
					'add-task' => array(
						'label'    => __( 'Add New Task', 'travelism-office-management' ),
						'slug'     => 'tom-add-task',
						'callback' => array( $this, 'render_add_task' ),
					),
				),
			),
			'analytics' => array(
				'label'       => __( 'Analytics', 'travelism-office-management' ),
				'slug'        => 'tom-analytics',
				'icon'        => 'dashicons-chart-bar',
				'position'    => 6,
				'capability'  => 'manage_tom_analytics',
				'callback'    => array( $this, 'render_analytics' ),
				'parent'      => null,
			),
			'settings' => array(
				'label'       => __( 'Settings', 'travelism-office-management' ),
				'slug'        => 'tom-settings',
				'icon'        => 'dashicons-admin-generic',
				'position'    => 7,
				'capability'  => 'manage_options',
				'callback'    => array( $this, 'render_settings' ),
				'parent'      => null,
				'submenu'     => array(
					'general' => array(
						'label'    => __( 'General Settings', 'travelism-office-management' ),
						'slug'     => 'tom-settings',
						'callback' => array( $this, 'render_settings' ),
					),
					'email' => array(
						'label'    => __( 'Email Settings', 'travelism-office-management' ),
						'slug'     => 'tom-email-settings',
						'callback' => array( $this, 'render_email_settings' ),
					),
					'permissions' => array(
						'label'    => __( 'Permissions', 'travelism-office-management' ),
						'slug'     => 'tom-permissions',
						'callback' => array( $this, 'render_permissions' ),
					),
				),
			),
		);
	}

	/**
	 * Setup WordPress hooks
	 *
	 * @since 1.0.0
	 */
	private function setup_hooks() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'route_page' ) );
	}

	/**
	 * Register menu items
		 *
	 * @since 1.0.0
	 */
	public function register_menu() {
		foreach ( $this->menu_items as $key => $item ) {
			if ( ! current_user_can( $item['capability'] ) ) {
				continue;
			}

			if ( is_null( $item['parent'] ) ) {
				add_menu_page(
					$item['label'],
					$item['label'],
					$item['capability'],
					$item['slug'],
					$item['callback'],
					$item['icon'],
					$item['position']
				);
			}

			// Register submenu items
			if ( ! empty( $item['submenu'] ) ) {
				foreach ( $item['submenu'] as $sub_key => $submenu ) {
					add_submenu_page(
						$item['slug'],
						$submenu['label'],
						$submenu['label'],
						$item['capability'],
						$submenu['slug'],
						$submenu['callback']
					);
				}
			}
		}
	}

	/**
	 * Route the current page
	 *
	 * @since 1.0.0
	 */
	public function route_page() {
		// Get current page from query string
		$this->current_page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
	}

	/**
	 * Get menu items
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_menu_items() {
		return $this->menu_items;
	}

	/**
	 * Get current page
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_current_page() {
		return $this->current_page;
	}

	/**
	 * Check if page is active
	 *
	 * @param string $page_slug The page slug to check.
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_page_active( $page_slug ) {
		return $this->current_page === $page_slug;
	}

	// ============================================
	// Page Rendering Callbacks
	// ============================================

	/**
	 * Render Dashboard page
	 *
	 * @since 1.0.0
	 */
	public function render_dashboard() {
		do_action( 'tom_before_dashboard_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Dashboard', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'Welcome to Travelism Office Management Dashboard', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_dashboard_page' );
	}

	/**
	 * Render Customers page
	 *
	 * @since 1.0.0
	 */
	public function render_customers() {
		do_action( 'tom_before_customers_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Customers', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'Manage your customers here', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_customers_page' );
	}

	/**
	 * Render Add Customer page
	 *
	 * @since 1.0.0
	 */
	public function render_add_customer() {
		do_action( 'tom_before_add_customer_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Add New Customer', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'Create a new customer entry', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_add_customer_page' );
	}

	/**
	 * Render Services page
	 *
	 * @since 1.0.0
	 */
	public function render_services() {
		do_action( 'tom_before_services_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Services', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'Manage your services here', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_services_page' );
	}

	/**
	 * Render Add Service page
	 *
	 * @since 1.0.0
	 */
	public function render_add_service() {
		do_action( 'tom_before_add_service_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Add New Service', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'Create a new service', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_add_service_page' );
	}

	/**
	 * Render Visa page
	 *
	 * @since 1.0.0
	 */
	public function render_visa() {
		do_action( 'tom_before_visa_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Visa Applications', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'Manage visa applications here', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_visa_page' );
	}

	/**
	 * Render Add Visa page
	 *
	 * @since 1.0.0
	 */
	public function render_add_visa() {
		do_action( 'tom_before_add_visa_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Add New Visa Application', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'Create a new visa application', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_add_visa_page' );
	}

	/**
	 * Render Tasks page
	 *
	 * @since 1.0.0
	 */
	public function render_tasks() {
		do_action( 'tom_before_tasks_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Tasks', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'Manage your tasks here', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_tasks_page' );
	}

	/**
	 * Render Add Task page
	 *
	 * @since 1.0.0
	 */
	public function render_add_task() {
		do_action( 'tom_before_add_task_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Add New Task', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'Create a new task', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_add_task_page' );
	}

	/**
	 * Render Analytics page
	 *
	 * @since 1.0.0
	 */
	public function render_analytics() {
		do_action( 'tom_before_analytics_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Analytics', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'View analytics and reports here', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_analytics_page' );
	}

	/**
	 * Render Settings page
	 *
	 * @since 1.0.0
	 */
	public function render_settings() {
		do_action( 'tom_before_settings_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Settings', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'Configure general settings here', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_settings_page' );
	}

	/**
	 * Render Email Settings page
	 *
	 * @since 1.0.0
	 */
	public function render_email_settings() {
		do_action( 'tom_before_email_settings_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Email Settings', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'Configure email settings here', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_email_settings_page' );
	}

	/**
	 * Render Permissions page
	 *
	 * @since 1.0.0
	 */
	public function render_permissions() {
		do_action( 'tom_before_permissions_page' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Permissions', 'travelism-office-management' ) . '</h1>';
		echo '<p>' . esc_html__( 'Manage user permissions here', 'travelism-office-management' ) . '</p>';
		echo '</div>';
		do_action( 'tom_after_permissions_page' );
	}
}
