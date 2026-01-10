<?php
/**
 * Database Class for Travelism Office Management
 *
 * Handles all database table creation, versioning, and schema management
 * using WordPress dbDelta for proper database maintenance.
 *
 * @package Travelism_Office_Management
 * @subpackage Includes
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travelism_Database
 *
 * Manages database tables for the Travelism Office Management system.
 *
 * @since 1.0.0
 */
class Travelism_Database {

	/**
	 * Database version
	 *
	 * @var string
	 * @since 1.0.0
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Database version option key
	 *
	 * @var string
	 * @since 1.0.0
	 */
	const DB_VERSION_KEY = 'travelism_db_version';

	/**
	 * Initialize the database
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'create_tables' ) );
	}

	/**
	 * Create database tables if needed
	 *
	 * Checks the current database version and creates/updates tables
	 * as necessary using WordPress dbDelta function.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function create_tables() {
		$installed_db_version = get_option( self::DB_VERSION_KEY );

		// Only create tables if not installed or version mismatch
		if ( empty( $installed_db_version ) || version_compare( $installed_db_version, self::DB_VERSION, '<' ) ) {
			self::run_migrations();
			update_option( self::DB_VERSION_KEY, self::DB_VERSION );
		}
	}

	/**
	 * Run database migrations
	 *
	 * Creates all required tables using dbDelta function.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function run_migrations() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Create Customers table
		self::create_customers_table();

		// Create Leads table
		self::create_leads_table();

		// Create Services table
		self::create_services_table();

		// Create Visas table
		self::create_visas_table();

		// Create Tasks table
		self::create_tasks_table();

		// Create Activity Logs table
		self::create_activity_logs_table();
	}

	/**
	 * Create Customers table
	 *
	 * Stores customer information including contact details and metadata.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function create_customers_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'travelism_customers';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			first_name varchar(100) NOT NULL DEFAULT '',
			last_name varchar(100) NOT NULL DEFAULT '',
			email varchar(100) NOT NULL DEFAULT '',
			phone varchar(20) NOT NULL DEFAULT '',
			country varchar(100) NOT NULL DEFAULT '',
			city varchar(100) NOT NULL DEFAULT '',
			address text,
			postal_code varchar(20),
			customer_type varchar(50) NOT NULL DEFAULT 'individual',
			status varchar(50) NOT NULL DEFAULT 'active',
			notes longtext,
			date_created datetime DEFAULT CURRENT_TIMESTAMP,
			date_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			created_by bigint(20),
			modified_by bigint(20),
			PRIMARY KEY (id),
			KEY email (email),
			KEY phone (phone),
			KEY country (country),
			KEY status (status),
			KEY date_created (date_created),
			KEY created_by (created_by)
		) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create Leads table
	 *
	 * Stores lead information including potential customers and their status.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function create_leads_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'travelism_leads';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			customer_id bigint(20) unsigned,
			first_name varchar(100) NOT NULL DEFAULT '',
			last_name varchar(100) NOT NULL DEFAULT '',
			email varchar(100) NOT NULL DEFAULT '',
			phone varchar(20) NOT NULL DEFAULT '',
			source varchar(100) NOT NULL DEFAULT '',
			lead_status varchar(50) NOT NULL DEFAULT 'new',
			interested_services longtext,
			budget varchar(100),
			travel_date date,
			notes longtext,
			date_created datetime DEFAULT CURRENT_TIMESTAMP,
			date_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			assigned_to bigint(20),
			created_by bigint(20),
			modified_by bigint(20),
			PRIMARY KEY (id),
			KEY customer_id (customer_id),
			KEY email (email),
			KEY lead_status (lead_status),
			KEY source (source),
			KEY date_created (date_created),
			KEY assigned_to (assigned_to),
			FOREIGN KEY (customer_id) REFERENCES " . $wpdb->prefix . "travelism_customers(id) ON DELETE SET NULL
		) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create Services table
	 *
	 * Stores service offerings including visa processing, travel bookings, etc.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function create_services_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'travelism_services';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(200) NOT NULL DEFAULT '',
			description longtext,
			service_type varchar(100) NOT NULL DEFAULT '',
			price decimal(10, 2),
			currency varchar(10) NOT NULL DEFAULT 'USD',
			duration varchar(100),
			status varchar(50) NOT NULL DEFAULT 'active',
			category varchar(100),
			requirements longtext,
			processing_time varchar(100),
			date_created datetime DEFAULT CURRENT_TIMESTAMP,
			date_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			created_by bigint(20),
			modified_by bigint(20),
			PRIMARY KEY (id),
			KEY name (name),
			KEY service_type (service_type),
			KEY status (status),
			KEY category (category),
			KEY date_created (date_created)
		) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create Visas table
	 *
	 * Stores visa application information and processing details.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function create_visas_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'travelism_visas';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			customer_id bigint(20) unsigned NOT NULL,
			visa_type varchar(100) NOT NULL DEFAULT '',
			destination_country varchar(100) NOT NULL DEFAULT '',
			passport_number varchar(50) NOT NULL DEFAULT '',
			passport_expiry date,
			application_date date,
			submission_date date,
			expected_completion date,
			completion_date date,
			visa_status varchar(50) NOT NULL DEFAULT 'pending',
			visa_validity_start date,
			visa_validity_end date,
			visa_number varchar(100),
			processing_fee decimal(10, 2),
			service_fee decimal(10, 2),
			total_cost decimal(10, 2),
			currency varchar(10) NOT NULL DEFAULT 'USD',
			payment_status varchar(50) NOT NULL DEFAULT 'pending',
			documents_required longtext,
			documents_uploaded longtext,
			notes longtext,
			rejection_reason longtext,
			date_created datetime DEFAULT CURRENT_TIMESTAMP,
			date_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			assigned_to bigint(20),
			created_by bigint(20),
			modified_by bigint(20),
			PRIMARY KEY (id),
			KEY customer_id (customer_id),
			KEY visa_type (visa_type),
			KEY destination_country (destination_country),
			KEY visa_status (visa_status),
			KEY payment_status (payment_status),
			KEY application_date (application_date),
			KEY expected_completion (expected_completion),
			KEY assigned_to (assigned_to),
			KEY date_created (date_created),
			FOREIGN KEY (customer_id) REFERENCES " . $wpdb->prefix . "travelism_customers(id) ON DELETE CASCADE
		) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create Tasks table
	 *
	 * Stores task information for team members to track work items.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function create_tasks_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'travelism_tasks';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL DEFAULT '',
			description longtext,
			task_type varchar(100),
			related_entity_type varchar(50),
			related_entity_id bigint(20) unsigned,
			assigned_to bigint(20) unsigned NOT NULL,
			priority varchar(50) NOT NULL DEFAULT 'medium',
			task_status varchar(50) NOT NULL DEFAULT 'pending',
			due_date datetime,
			start_date datetime,
			completion_date datetime,
			estimated_hours decimal(10, 2),
			actual_hours decimal(10, 2),
			category varchar(100),
			tags longtext,
			attachments longtext,
			checklist_items longtext,
			notes longtext,
			date_created datetime DEFAULT CURRENT_TIMESTAMP,
			date_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			created_by bigint(20),
			modified_by bigint(20),
			PRIMARY KEY (id),
			KEY assigned_to (assigned_to),
			KEY task_status (task_status),
			KEY priority (priority),
			KEY due_date (due_date),
			KEY related_entity_type (related_entity_type),
			KEY related_entity_id (related_entity_id),
			KEY date_created (date_created),
			KEY category (category)
		) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Create Activity Logs table
	 *
	 * Stores activity logs for audit trail and tracking system events.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function create_activity_logs_table() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'travelism_activity_logs';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			activity_type varchar(100) NOT NULL DEFAULT '',
			entity_type varchar(100) NOT NULL DEFAULT '',
			entity_id bigint(20) unsigned,
			entity_name varchar(255),
			user_id bigint(20) unsigned,
			user_email varchar(100),
			action varchar(100) NOT NULL DEFAULT '',
			action_details longtext,
			old_values longtext,
			new_values longtext,
			ip_address varchar(45),
			user_agent longtext,
			status varchar(50) NOT NULL DEFAULT 'success',
			error_message longtext,
			severity varchar(50) NOT NULL DEFAULT 'info',
			metadata longtext,
			date_created datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY activity_type (activity_type),
			KEY entity_type (entity_type),
			KEY entity_id (entity_id),
			KEY user_id (user_id),
			KEY action (action),
			KEY date_created (date_created),
			KEY severity (severity),
			KEY status (status)
		) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Get database version
	 *
	 * Returns the current installed database version.
	 *
	 * @since 1.0.0
	 * @return string|false Database version or false if not installed
	 */
	public static function get_db_version() {
		return get_option( self::DB_VERSION_KEY );
	}

	/**
	 * Reset database (for development/testing only)
	 *
	 * Drops all plugin tables. WARNING: This is destructive and should only
	 * be used in development/testing environments.
	 *
	 * @since 1.0.0
	 * @return bool True on success, false on failure
	 */
	public static function reset_database() {
		// Only allow in development environment
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return false;
		}

		global $wpdb;

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

		delete_option( self::DB_VERSION_KEY );

		return true;
	}

	/**
	 * Get table name
	 *
	 * Returns the full table name with WordPress prefix.
	 *
	 * @since 1.0.0
	 * @param string $table Table name without prefix
	 * @return string Full table name with prefix
	 */
	public static function get_table_name( $table ) {
		global $wpdb;

		$tables = array(
			'customers'     => 'travelism_customers',
			'leads'         => 'travelism_leads',
			'services'      => 'travelism_services',
			'visas'         => 'travelism_visas',
			'tasks'         => 'travelism_tasks',
			'activity_logs' => 'travelism_activity_logs',
		);

		if ( isset( $tables[ $table ] ) ) {
			return $wpdb->prefix . $tables[ $table ];
		}

		return null;
	}

	/**
	 * Check if tables exist
	 *
	 * Verifies that all required tables are present in the database.
	 *
	 * @since 1.0.0
	 * @return bool True if all tables exist, false otherwise
	 */
	public static function check_tables_exist() {
		global $wpdb;

		$tables = array(
			self::get_table_name( 'customers' ),
			self::get_table_name( 'leads' ),
			self::get_table_name( 'services' ),
			self::get_table_name( 'visas' ),
			self::get_table_name( 'tasks' ),
			self::get_table_name( 'activity_logs' ),
		);

		foreach ( $tables as $table ) {
			$result = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( ! $result ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get database statistics
	 *
	 * Returns statistics about the database tables.
	 *
	 * @since 1.0.0
	 * @return array Database statistics
	 */
	public static function get_database_stats() {
		global $wpdb;

		$stats = array(
			'version'      => self::get_db_version(),
			'tables_exist' => self::check_tables_exist(),
			'table_sizes'  => array(),
		);

		$tables = array(
			'customers',
			'leads',
			'services',
			'visas',
			'tasks',
			'activity_logs',
		);

		foreach ( $tables as $table ) {
			$table_name = self::get_table_name( $table );
			$count      = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $table_name ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$size       = $wpdb->get_var( "SELECT ROUND((data_length + index_length) / 1024 / 1024, 2) FROM information_schema.tables WHERE table_name = '" . $table_name . "' AND table_schema = '" . DB_NAME . "'" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			$stats['table_sizes'][ $table ] = array(
				'rows' => (int) $count,
				'size' => (float) $size,
			);
		}

		return $stats;
	}
}

// Initialize database tables on plugin load
Travelism_Database::init();
