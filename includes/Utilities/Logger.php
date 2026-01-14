<?php
/**
 * Logger Utility Class
 *
 * Provides file and database logging with multiple log levels
 * for debugging and audit trails.
 *
 * @package Travelism_Office_Management
 * @subpackage Utilities
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travelism_Logger
 *
 * Handles logging operations for the plugin.
 *
 * @since 1.0.0
 */
class Travelism_Logger {

	/**
	 * Log levels
	 */
	const LEVEL_ERROR   = 'error';
	const LEVEL_WARNING = 'warning';
	const LEVEL_INFO    = 'info';
	const LEVEL_DEBUG   = 'debug';

	/**
	 * Log directory
	 *
	 * @var string
	 */
	private static $log_dir;

	/**
	 * Initialize logger
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {
		$upload_dir      = wp_upload_dir();
		self::$log_dir   = $upload_dir['basedir'] . '/travelism-logs';

		// Create log directory if it doesn't exist
		if ( ! file_exists( self::$log_dir ) ) {
			wp_mkdir_p( self::$log_dir );
			// Add .htaccess to prevent direct access
			file_put_contents( self::$log_dir . '/.htaccess', 'Deny from all' );
		}
	}

	/**
	 * Log a message
	 *
	 * @since 1.0.0
	 * @param string $message Log message.
	 * @param string $level Log level.
	 * @param array  $context Additional context data.
	 * @return bool True on success, false on failure.
	 */
	public static function log( $message, $level = self::LEVEL_INFO, $context = array() ) {
		// Only log if WP_DEBUG is enabled or level is error
		if ( ! WP_DEBUG && $level !== self::LEVEL_ERROR ) {
			return false;
		}

		// Log to file
		self::log_to_file( $message, $level, $context );

		// Log to database for errors and warnings
		if ( in_array( $level, array( self::LEVEL_ERROR, self::LEVEL_WARNING ), true ) ) {
			self::log_to_database( $message, $level, $context );
		}

		return true;
	}

	/**
	 * Log to file
	 *
	 * @since 1.0.0
	 * @param string $message Log message.
	 * @param string $level Log level.
	 * @param array  $context Additional context.
	 * @return bool True on success, false on failure.
	 */
	private static function log_to_file( $message, $level, $context ) {
		if ( ! self::$log_dir ) {
			self::init();
		}

		$log_file = self::$log_dir . '/travelism-' . gmdate( 'Y-m-d' ) . '.log';
		$timestamp = gmdate( 'Y-m-d H:i:s' );
		$user_id = get_current_user_id();
		$ip_address = self::get_ip_address();

		$log_entry = sprintf(
			"[%s] [%s] [User: %d] [IP: %s] %s\n",
			$timestamp,
			strtoupper( $level ),
			$user_id,
			$ip_address,
			$message
		);

		if ( ! empty( $context ) ) {
			$log_entry .= 'Context: ' . wp_json_encode( $context, JSON_PRETTY_PRINT ) . "\n";
		}

		$log_entry .= str_repeat( '-', 80 ) . "\n";

		return (bool) file_put_contents( $log_file, $log_entry, FILE_APPEND );
	}

	/**
	 * Log to database
	 *
	 * @since 1.0.0
	 * @param string $message Log message.
	 * @param string $level Log level.
	 * @param array  $context Additional context.
	 * @return bool True on success, false on failure.
	 */
	private static function log_to_database( $message, $level, $context ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'travelism_activity_logs';
		$user_id = get_current_user_id();
		$user = get_userdata( $user_id );

		$data = array(
			'activity_type' => 'system_log',
			'entity_type'   => 'log',
			'user_id'       => $user_id,
			'user_email'    => $user ? $user->user_email : '',
			'action'        => $level,
			'action_details' => $message,
			'ip_address'    => self::get_ip_address(),
			'user_agent'    => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			'severity'      => $level,
			'metadata'      => wp_json_encode( $context ),
			'date_created'  => current_time( 'mysql' ),
		);

		return (bool) $wpdb->insert( $table_name, $data );
	}

	/**
	 * Get client IP address
	 *
	 * @since 1.0.0
	 * @return string IP address.
	 */
	private static function get_ip_address() {
		$ip = '';

		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip;
	}

	/**
	 * Log error
	 *
	 * @since 1.0.0
	 * @param string $message Error message.
	 * @param array  $context Additional context.
	 * @return bool True on success, false on failure.
	 */
	public static function error( $message, $context = array() ) {
		return self::log( $message, self::LEVEL_ERROR, $context );
	}

	/**
	 * Log warning
	 *
	 * @since 1.0.0
	 * @param string $message Warning message.
	 * @param array  $context Additional context.
	 * @return bool True on success, false on failure.
	 */
	public static function warning( $message, $context = array() ) {
		return self::log( $message, self::LEVEL_WARNING, $context );
	}

	/**
	 * Log info
	 *
	 * @since 1.0.0
	 * @param string $message Info message.
	 * @param array  $context Additional context.
	 * @return bool True on success, false on failure.
	 */
	public static function info( $message, $context = array() ) {
		return self::log( $message, self::LEVEL_INFO, $context );
	}

	/**
	 * Log debug
	 *
	 * @since 1.0.0
	 * @param string $message Debug message.
	 * @param array  $context Additional context.
	 * @return bool True on success, false on failure.
	 */
	public static function debug( $message, $context = array() ) {
		return self::log( $message, self::LEVEL_DEBUG, $context );
	}

	/**
	 * Clear old log files
	 *
	 * @since 1.0.0
	 * @param int $days Number of days to keep logs.
	 * @return int Number of files deleted.
	 */
	public static function clear_old_logs( $days = 30 ) {
		if ( ! self::$log_dir ) {
			self::init();
		}

		$deleted = 0;
		$files = glob( self::$log_dir . '/travelism-*.log' );

		if ( $files ) {
			$cutoff_time = time() - ( $days * DAY_IN_SECONDS );

			foreach ( $files as $file ) {
				if ( filemtime( $file ) < $cutoff_time ) {
					if ( wp_delete_file( $file ) ) {
						$deleted++;
					}
				}
			}
		}

		return $deleted;
	}
}

// Initialize logger
Travelism_Logger::init();
