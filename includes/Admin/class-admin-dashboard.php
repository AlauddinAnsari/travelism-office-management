<?php
/**
 * Admin Dashboard Class
 *
 * Handles dashboard functionality and widgets.
 *
 * @package Travelism_Office_Management
 * @subpackage Admin
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travelism_Admin_Dashboard
 *
 * Manages the admin dashboard.
 *
 * @since 1.0.0
 */
class Travelism_Admin_Dashboard {

	/**
	 * Initialize dashboard
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue dashboard scripts
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		if ( strpos( $hook, 'travelism-office-management' ) === false ) {
			return;
		}

		// Enqueue Chart.js
		wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true );
	}

	/**
	 * Get dashboard statistics
	 *
	 * @since 1.0.0
	 * @return array Dashboard statistics.
	 */
	public function get_statistics() {
		global $wpdb;

		$customers_table = $wpdb->prefix . 'travelism_customers';
		$visas_table = $wpdb->prefix . 'travelism_visas';
		$tasks_table = $wpdb->prefix . 'travelism_tasks';
		$services_table = $wpdb->prefix . 'travelism_services';

		return array(
			'total_customers' => $wpdb->get_var( "SELECT COUNT(*) FROM {$customers_table}" ),
			'active_customers' => $wpdb->get_var( "SELECT COUNT(*) FROM {$customers_table} WHERE status = 'active'" ),
			'total_visas' => $wpdb->get_var( "SELECT COUNT(*) FROM {$visas_table}" ),
			'pending_visas' => $wpdb->get_var( "SELECT COUNT(*) FROM {$visas_table} WHERE visa_status = 'pending'" ),
			'approved_visas' => $wpdb->get_var( "SELECT COUNT(*) FROM {$visas_table} WHERE visa_status = 'approved'" ),
			'total_tasks' => $wpdb->get_var( "SELECT COUNT(*) FROM {$tasks_table}" ),
			'pending_tasks' => $wpdb->get_var( "SELECT COUNT(*) FROM {$tasks_table} WHERE task_status = 'pending'" ),
			'completed_tasks' => $wpdb->get_var( "SELECT COUNT(*) FROM {$tasks_table} WHERE task_status = 'completed'" ),
			'total_revenue' => $wpdb->get_var( "SELECT COALESCE(SUM(total_cost), 0) FROM {$visas_table} WHERE payment_status = 'paid'" ),
		);
	}

	/**
	 * Get recent activities
	 *
	 * @since 1.0.0
	 * @param int $limit Number of activities to retrieve.
	 * @return array Recent activities.
	 */
	public function get_recent_activities( $limit = 10 ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'travelism_activity_logs';
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} ORDER BY date_created DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);
	}
}
