<?php
/**
 * Formatter Utility Class
 *
 * Provides output formatting for currency, dates, status badges, etc.
 *
 * @package Travelism_Office_Management
 * @subpackage Utilities
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travelism_Formatter
 *
 * Handles formatting operations for the plugin.
 *
 * @since 1.0.0
 */
class Travelism_Formatter {

	/**
	 * Format currency
	 *
	 * @since 1.0.0
	 * @param float  $amount Amount to format.
	 * @param string $currency Currency code (default: USD).
	 * @return string Formatted currency string.
	 */
	public static function format_currency( $amount, $currency = 'USD' ) {
		$symbols = array(
			'USD' => '$',
			'EUR' => '€',
			'GBP' => '£',
			'INR' => '₹',
			'AED' => 'د.إ',
			'SAR' => '﷼',
		);

		$symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : $currency . ' ';
		return $symbol . number_format( (float) $amount, 2 );
	}

	/**
	 * Format date
	 *
	 * @since 1.0.0
	 * @param string $date Date string to format.
	 * @param string $format Desired format (default: WordPress date format).
	 * @return string Formatted date string.
	 */
	public static function format_date( $date, $format = null ) {
		if ( empty( $date ) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00' ) {
			return '-';
		}

		if ( null === $format ) {
			$format = get_option( 'date_format' );
		}

		return gmdate( $format, strtotime( $date ) );
	}

	/**
	 * Format datetime
	 *
	 * @since 1.0.0
	 * @param string $datetime DateTime string to format.
	 * @return string Formatted datetime string.
	 */
	public static function format_datetime( $datetime ) {
		if ( empty( $datetime ) || $datetime === '0000-00-00 00:00:00' ) {
			return '-';
		}

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		return gmdate( $date_format . ' ' . $time_format, strtotime( $datetime ) );
	}

	/**
	 * Format time ago
	 *
	 * @since 1.0.0
	 * @param string $datetime DateTime string.
	 * @return string Time ago string.
	 */
	public static function format_time_ago( $datetime ) {
		if ( empty( $datetime ) ) {
			return '-';
		}

		return human_time_diff( strtotime( $datetime ), current_time( 'timestamp' ) ) . ' ago';
	}

	/**
	 * Format status badge
	 *
	 * @since 1.0.0
	 * @param string $status Status value.
	 * @param string $type Type of status (default, success, warning, danger).
	 * @return string HTML for status badge.
	 */
	public static function format_status_badge( $status, $type = 'default' ) {
		$classes = array(
			'default' => 'travelism-badge-default',
			'success' => 'travelism-badge-success',
			'warning' => 'travelism-badge-warning',
			'danger'  => 'travelism-badge-danger',
			'info'    => 'travelism-badge-info',
		);

		$class = isset( $classes[ $type ] ) ? $classes[ $type ] : $classes['default'];
		return sprintf( '<span class="travelism-badge %s">%s</span>', esc_attr( $class ), esc_html( ucfirst( $status ) ) );
	}

	/**
	 * Get status badge type
	 *
	 * @since 1.0.0
	 * @param string $status Status value.
	 * @param string $context Context (visa, task, payment, etc.).
	 * @return string Badge type.
	 */
	public static function get_status_badge_type( $status, $context = 'general' ) {
		$status = strtolower( $status );

		switch ( $context ) {
			case 'visa':
				$types = array(
					'approved'   => 'success',
					'processing' => 'info',
					'pending'    => 'warning',
					'rejected'   => 'danger',
					'completed'  => 'success',
				);
				break;

			case 'payment':
				$types = array(
					'paid'     => 'success',
					'pending'  => 'warning',
					'failed'   => 'danger',
					'refunded' => 'info',
				);
				break;

			case 'task':
				$types = array(
					'completed'    => 'success',
					'in_progress'  => 'info',
					'pending'      => 'warning',
					'overdue'      => 'danger',
				);
				break;

			default:
				$types = array(
					'active'   => 'success',
					'inactive' => 'danger',
					'pending'  => 'warning',
				);
				break;
		}

		return isset( $types[ $status ] ) ? $types[ $status ] : 'default';
	}

	/**
	 * Format phone number
	 *
	 * @since 1.0.0
	 * @param string $phone Phone number to format.
	 * @return string Formatted phone number.
	 */
	public static function format_phone( $phone ) {
		if ( empty( $phone ) ) {
			return '-';
		}

		// Remove all non-numeric characters except +
		$phone = preg_replace( '/[^0-9+]/', '', $phone );

		// If it starts with country code, keep the +
		if ( strpos( $phone, '+' ) === 0 ) {
			return $phone;
		}

		return $phone;
	}

	/**
	 * Format name
	 *
	 * @since 1.0.0
	 * @param string $first_name First name.
	 * @param string $last_name Last name.
	 * @return string Full name.
	 */
	public static function format_name( $first_name, $last_name ) {
		$name = trim( $first_name . ' ' . $last_name );
		return ! empty( $name ) ? $name : '-';
	}

	/**
	 * Truncate text
	 *
	 * @since 1.0.0
	 * @param string $text Text to truncate.
	 * @param int    $length Maximum length.
	 * @param string $suffix Suffix to add if truncated.
	 * @return string Truncated text.
	 */
	public static function truncate( $text, $length = 100, $suffix = '...' ) {
		if ( strlen( $text ) <= $length ) {
			return $text;
		}

		return substr( $text, 0, $length ) . $suffix;
	}

	/**
	 * Format file size
	 *
	 * @since 1.0.0
	 * @param int $bytes File size in bytes.
	 * @return string Formatted file size.
	 */
	public static function format_file_size( $bytes ) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );

		for ( $i = 0; $bytes > 1024 && $i < count( $units ) - 1; $i++ ) {
			$bytes /= 1024;
		}

		return round( $bytes, 2 ) . ' ' . $units[ $i ];
	}

	/**
	 * Format percentage
	 *
	 * @since 1.0.0
	 * @param float $value Percentage value.
	 * @param int   $decimals Number of decimal places.
	 * @return string Formatted percentage.
	 */
	public static function format_percentage( $value, $decimals = 2 ) {
		return number_format( (float) $value, $decimals ) . '%';
	}

	/**
	 * Format priority badge
	 *
	 * @since 1.0.0
	 * @param string $priority Priority level.
	 * @return string HTML for priority badge.
	 */
	public static function format_priority_badge( $priority ) {
		$types = array(
			'low'      => 'success',
			'medium'   => 'warning',
			'high'     => 'danger',
			'critical' => 'danger',
		);

		$type = isset( $types[ strtolower( $priority ) ] ) ? $types[ strtolower( $priority ) ] : 'default';
		return self::format_status_badge( $priority, $type );
	}

	/**
	 * Escape and format output
	 *
	 * @since 1.0.0
	 * @param mixed $value Value to escape.
	 * @param string $type Escape type (html, attr, url, textarea).
	 * @return string Escaped value.
	 */
	public static function escape( $value, $type = 'html' ) {
		switch ( $type ) {
			case 'attr':
				return esc_attr( $value );
			case 'url':
				return esc_url( $value );
			case 'textarea':
				return esc_textarea( $value );
			case 'html':
			default:
				return esc_html( $value );
		}
	}
}
