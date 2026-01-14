<?php
/**
 * Security Utility Class
 *
 * Provides security utilities including nonce verification, sanitization,
 * rate limiting, and security logging.
 *
 * @package Travelism_Office_Management
 * @subpackage Utilities
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travelism_Security
 *
 * Handles security operations for the plugin.
 *
 * @since 1.0.0
 */
class Travelism_Security {

	/**
	 * Rate limit transient prefix
	 *
	 * @var string
	 */
	const RATE_LIMIT_PREFIX = 'travelism_rate_limit_';

	/**
	 * Verify nonce
	 *
	 * @since 1.0.0
	 * @param string $nonce Nonce value to verify.
	 * @param string $action Nonce action.
	 * @return bool True if valid, false otherwise.
	 */
	public static function verify_nonce( $nonce, $action = 'travelism_nonce' ) {
		return wp_verify_nonce( $nonce, $action );
	}

	/**
	 * Create nonce
	 *
	 * @since 1.0.0
	 * @param string $action Nonce action.
	 * @return string Nonce value.
	 */
	public static function create_nonce( $action = 'travelism_nonce' ) {
		return wp_create_nonce( $action );
	}

	/**
	 * Check user capability
	 *
	 * @since 1.0.0
	 * @param string $capability Capability to check.
	 * @param int    $user_id User ID (optional, defaults to current user).
	 * @return bool True if user has capability, false otherwise.
	 */
	public static function check_capability( $capability, $user_id = null ) {
		if ( null === $user_id ) {
			return current_user_can( $capability );
		}

		$user = get_userdata( $user_id );
		return $user && user_can( $user, $capability );
	}

	/**
	 * Check AJAX referer
	 *
	 * @since 1.0.0
	 * @param string $action Nonce action.
	 * @return bool True if valid, false otherwise.
	 */
	public static function check_ajax_referer( $action = 'travelism_nonce' ) {
		return check_ajax_referer( $action, 'nonce', false );
	}

	/**
	 * Sanitize array recursively
	 *
	 * @since 1.0.0
	 * @param array $array Array to sanitize.
	 * @return array Sanitized array.
	 */
	public static function sanitize_array( $array ) {
		$sanitized = array();

		foreach ( $array as $key => $value ) {
			$key = sanitize_key( $key );

			if ( is_array( $value ) ) {
				$sanitized[ $key ] = self::sanitize_array( $value );
			} else {
				$sanitized[ $key ] = sanitize_text_field( $value );
			}
		}

		return $sanitized;
	}

	/**
	 * Check rate limit
	 *
	 * @since 1.0.0
	 * @param string $action Action identifier.
	 * @param int    $limit Maximum attempts allowed.
	 * @param int    $window Time window in seconds.
	 * @return bool True if within limit, false if exceeded.
	 */
	public static function check_rate_limit( $action, $limit = 10, $window = 60 ) {
		$user_id = get_current_user_id();
		$ip = self::get_ip_address();
		$key = self::RATE_LIMIT_PREFIX . md5( $action . $user_id . $ip );

		$attempts = get_transient( $key );

		if ( false === $attempts ) {
			set_transient( $key, 1, $window );
			return true;
		}

		if ( $attempts >= $limit ) {
			return false;
		}

		set_transient( $key, $attempts + 1, $window );
		return true;
	}

	/**
	 * Get client IP address
	 *
	 * @since 1.0.0
	 * @return string IP address.
	 */
	public static function get_ip_address() {
		$ip = '';

		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip;
	}

	/**
	 * Log security event
	 *
	 * @since 1.0.0
	 * @param string $event Event description.
	 * @param string $severity Severity level.
	 * @param array  $data Additional data.
	 * @return bool True on success, false on failure.
	 */
	public static function log_security_event( $event, $severity = 'info', $data = array() ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'travelism_activity_logs';
		$user_id = get_current_user_id();
		$user = get_userdata( $user_id );

		$log_data = array(
			'activity_type'  => 'security',
			'entity_type'    => 'security_log',
			'user_id'        => $user_id,
			'user_email'     => $user ? $user->user_email : '',
			'action'         => 'security_event',
			'action_details' => $event,
			'ip_address'     => self::get_ip_address(),
			'user_agent'     => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			'severity'       => $severity,
			'metadata'       => wp_json_encode( $data ),
			'date_created'   => current_time( 'mysql' ),
		);

		return (bool) $wpdb->insert( $table_name, $log_data );
	}

	/**
	 * Validate file upload
	 *
	 * @since 1.0.0
	 * @param array $file File array from $_FILES.
	 * @param array $allowed_types Allowed MIME types.
	 * @param int   $max_size Maximum file size in bytes.
	 * @return array Array with 'valid' boolean and 'error' message.
	 */
	public static function validate_file_upload( $file, $allowed_types = array(), $max_size = 2097152 ) {
		// Check for upload errors
		if ( isset( $file['error'] ) && $file['error'] !== UPLOAD_ERR_OK ) {
			return array(
				'valid' => false,
				'error' => 'File upload error occurred',
			);
		}

		// Check file size
		if ( isset( $file['size'] ) && $file['size'] > $max_size ) {
			return array(
				'valid' => false,
				'error' => sprintf( 'File size exceeds maximum allowed size of %s', size_format( $max_size ) ),
			);
		}

		// Check file type
		if ( ! empty( $allowed_types ) && isset( $file['type'] ) ) {
			if ( ! in_array( $file['type'], $allowed_types, true ) ) {
				return array(
					'valid' => false,
					'error' => 'File type not allowed',
				);
			}
		}

		return array( 'valid' => true );
	}

	/**
	 * Sanitize filename
	 *
	 * @since 1.0.0
	 * @param string $filename Filename to sanitize.
	 * @return string Sanitized filename.
	 */
	public static function sanitize_filename( $filename ) {
		return sanitize_file_name( $filename );
	}

	/**
	 * Hash sensitive data
	 *
	 * @since 1.0.0
	 * @param string $data Data to hash.
	 * @return string Hashed data.
	 */
	public static function hash_data( $data ) {
		return wp_hash( $data );
	}

	/**
	 * Encrypt data
	 *
	 * Note: This is a placeholder implementation using base64 encoding.
	 * For production use, implement proper encryption using wp_salt() and
	 * a secure encryption library like sodium or openssl.
	 *
	 * @since 1.0.0
	 * @param string $data Data to encrypt.
	 * @return string Encrypted data.
	 */
	public static function encrypt_data( $data ) {
		// TODO: Implement proper encryption for production
		// Example: Use sodium_crypto_secretbox() or openssl_encrypt()
		return base64_encode( $data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Decrypt data
	 *
	 * Note: This is a placeholder implementation using base64 decoding.
	 * For production use, implement proper decryption using wp_salt() and
	 * a secure encryption library like sodium or openssl.
	 *
	 * @since 1.0.0
	 * @param string $data Data to decrypt.
	 * @return string Decrypted data.
	 */
	public static function decrypt_data( $data ) {
		// TODO: Implement proper decryption for production
		// Example: Use sodium_crypto_secretbox_open() or openssl_decrypt()
		return base64_decode( $data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}

	/**
	 * Check if request is AJAX
	 *
	 * @since 1.0.0
	 * @return bool True if AJAX request, false otherwise.
	 */
	public static function is_ajax() {
		return wp_doing_ajax();
	}

	/**
	 * Check if user is logged in
	 *
	 * @since 1.0.0
	 * @return bool True if logged in, false otherwise.
	 */
	public static function is_user_logged_in() {
		return is_user_logged_in();
	}

	/**
	 * Prevent SQL injection
	 *
	 * @since 1.0.0
	 * @param string $value Value to prepare.
	 * @return string Prepared value.
	 */
	public static function prepare_for_sql( $value ) {
		global $wpdb;
		return $wpdb->prepare( '%s', $value );
	}
}
