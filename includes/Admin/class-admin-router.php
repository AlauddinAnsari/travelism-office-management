<?php
/**
 * Admin Router for Travelism Office Management
 *
 * Responsible for rendering admin pages (views) for each admin menu tab.
 *
 * @package TravelismOfficeManagement
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Travelism_Admin_Router' ) ) {

    class Travelism_Admin_Router {

        /**
         * Base path for admin view templates
         *
         * @var string
         */
        protected $views_dir;

        /**
         * Constructor.
         *
         * @param string|null $views_dir Optional base directory for views. Defaults to includes/Admin/views/.
         */
        public function __construct( $views_dir = null ) {
            $this->views_dir = $views_dir ?: dirname( __FILE__ ) . '/views/';
        }

        /**
         * Return the current tab (from $_GET['tab']). Falls back to 'dashboard'.
         *
         * @return string
         */
        public function get_current_tab() {
            if ( isset( $_GET['tab'] ) ) {
                return sanitize_key( wp_unslash( $_GET['tab'] ) );
            }
            return 'dashboard';
        }

        /**
         * Render the view for a tab. If view not found, outputs a helpful placeholder.
         *
         * @param string $tab Tab slug to render.
         */
        public function render( $tab = '' ) {
            $tab = $tab ?: $this->get_current_tab();
            $template = $this->views_dir . $tab . '.php';

            // Allow customization via filter.
            $template = apply_filters( 'travelism_admin_template', $template, $tab );

            if ( file_exists( $template ) ) {
                include $template;
                return;
            }

            // Fallback output when template doesn't exist yet.
            echo '<div class="wrap">';
            echo '<h1>' . esc_html( ucfirst( $tab ) ) . '</h1>';
            echo '<p>' . esc_html__( 'This page is not implemented yet. Create the view at:', 'travelism' ) . ' <code>' . esc_html( $template ) . '</code></p>';
            echo '</div>';
        }
    }
}
