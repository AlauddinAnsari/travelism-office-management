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
		// Chart.js is enqueued in the main plugin class
		// No need to enqueue here to avoid duplication
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
			'total_customers' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$customers_table}" ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			'active_customers' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$customers_table} WHERE status = %s", 'active' ) ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			'total_visas' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$visas_table}" ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			'pending_visas' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$visas_table} WHERE visa_status = %s", 'pending' ) ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			'approved_visas' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$visas_table} WHERE visa_status = %s", 'approved' ) ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			'total_tasks' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$tasks_table}" ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			'pending_tasks' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tasks_table} WHERE task_status = %s", 'pending' ) ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			'completed_tasks' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tasks_table} WHERE task_status = %s", 'completed' ) ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			'total_revenue' => (float) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(total_cost), 0) FROM {$visas_table} WHERE payment_status = %s", 'paid' ) ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
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
