<?php
/**
 * Uninstall Script for Travelism Office Management
 *
 * This file handles complete cleanup when the plugin is uninstalled.
 * It removes all database tables, options, and user roles created by the plugin.
 *
 * @package Travelism_Office_Management
 * @since 1.0.0
 */

// Exit if accessed directly or not uninstalling
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Delete all plugin options
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'travelism_%'" );

// Drop all plugin tables
$tables = array(
	$wpdb->prefix . 'travelism_activity_logs',
	$wpdb->prefix . 'travelism_tasks',
	$wpdb->prefix . 'travelism_visas',
	$wpdb->prefix . 'travelism_services',
	$wpdb->prefix . 'travelism_leads',
	$wpdb->prefix . 'travelism_customers',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS $table" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

// Remove custom roles
remove_role( 'tom_ceo' );
remove_role( 'tom_manager' );
remove_role( 'tom_employee' );

// Clear any cached data
wp_cache_flush();
