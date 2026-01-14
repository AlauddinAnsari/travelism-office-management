<?php
/**
 * Admin Menu class for Travelism Office Management
 *
 * Registers admin menu and submenus, enqueues assets, and delegates rendering to router.
 *
 * @package TravelismOfficeManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Travelism_Admin_Menu' ) ) {

    class Travelism_Admin_Menu {

        /**
         * Router instance.
         *
         * @var Travelism_Admin_Router
         */
        protected $router;

        /**
         * Constructor.
         *
         * @param Travelism_Admin_Router|null $router Optional router instance for rendering pages.
         */
        public function __construct( $router = null ) {
            $this->router = $router ?: new Travelism_Admin_Router();
            add_action( 'admin_menu', array( $this, 'register_menus' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        }

        /**
         * Register top-level and submenu pages.
         */
        public function register_menus() {
            $capability = 'manage_options';
            $slug = 'travelism-office';

            add_menu_page(
                __( 'Travelism', 'travelism' ),
                __( 'Travelism', 'travelism' ),
                $capability,
                $slug,
                array( $this, 'render_dashboard' ),
                'dashicons-palmtree',
                26
            );

            $submenus = array(
                'customers' => __( 'Customers', 'travelism' ),
                'services'  => __( 'Services', 'travelism' ),
                'visa'      => __( 'Visa', 'travelism' ),
                'tasks'     => __( 'Tasks', 'travelism' ),
                'analytics' => __( 'Analytics', 'travelism' ),
                'settings'  => __( 'Settings', 'travelism' ),
            );

            foreach ( $submenus as $key => $title ) {
                add_submenu_page(
                    $slug,
                    $title,
                    $title,
                    $capability,
                    $slug . '&tab=' . $key,
                    function() use ( $key ) {
                        $this->router->render( $key );
                    }
                );
            }

            // Ensure top-level points to dashboard (remove duplicate top-level submenu)
            remove_submenu_page( $slug, $slug );
        }

        /**
         * Enqueue admin assets for plugin pages.
         *
         * @param string $hook Current admin page hook suffix.
         */
        public function enqueue_assets( $hook ) {
            // Scope to plugin pages by checking for our slug in the hook.
            if ( strpos( $hook, 'travelism-office' ) === false ) {
                return;
            }

            wp_enqueue_style( 'travelism-admin', plugins_url( 'assets/css/admin.css', dirname( __FILE__ ) ), array(), '1.0.0' );
            wp_enqueue_script( 'travelism-admin', plugins_url( 'assets/js/admin.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0', true );
            wp_localize_script( 'travelism-admin', 'TravelismAdmin', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'travelism_admin' ),
            ) );
        }

        /**
         * Render the dashboard page (top-level menu callback).
         */
        public function render_dashboard() {
            $this->router->render( 'dashboard' );
        }
    }
}
