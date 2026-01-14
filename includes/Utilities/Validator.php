<?php
/**
 * Validator Utility Class
 *
 * Provides input validation for email, phone, dates, currency, and more.
 *
 * @package Travelism_Office_Management
 * @subpackage Utilities
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travelism_Validator
 *
 * Handles validation operations for the plugin.
 *
 * @since 1.0.0
 */
class Travelism_Validator {

	/**
	 * Validate email address
	 *
	 * @since 1.0.0
	 * @param string $email Email address to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public static function validate_email( $email ) {
		return is_email( $email ) !== false;
	}

	/**
	 * Validate phone number
	 *
	 * @since 1.0.0
	 * @param string $phone Phone number to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public static function validate_phone( $phone ) {
		// Remove all non-numeric characters
		$phone = preg_replace( '/[^0-9+]/', '', $phone );

		// Phone should be between 10 and 15 digits
		$length = strlen( $phone );
		return $length >= 10 && $length <= 15;
	}

	/**
	 * Validate date
	 *
	 * @since 1.0.0
	 * @param string $date Date string to validate.
	 * @param string $format Expected date format (default: Y-m-d).
	 * @return bool True if valid, false otherwise.
	 */
	public static function validate_date( $date, $format = 'Y-m-d' ) {
		$d = DateTime::createFromFormat( $format, $date );
		return $d && $d->format( $format ) === $date;
	}

	/**
	 * Validate currency amount
	 *
	 * @since 1.0.0
	 * @param mixed $amount Amount to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public static function validate_currency( $amount ) {
		return is_numeric( $amount ) && $amount >= 0;
	}

	/**
	 * Validate URL
	 *
	 * @since 1.0.0
	 * @param string $url URL to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public static function validate_url( $url ) {
		return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
	}

	/**
	 * Validate required field
	 *
	 * @since 1.0.0
	 * @param mixed $value Value to check.
	 * @return bool True if not empty, false otherwise.
	 */
	public static function validate_required( $value ) {
		if ( is_string( $value ) ) {
			return trim( $value ) !== '';
		}
		return ! empty( $value );
	}

	/**
	 * Validate string length
	 *
	 * @since 1.0.0
	 * @param string $value String to validate.
	 * @param int    $min Minimum length.
	 * @param int    $max Maximum length.
	 * @return bool True if valid length, false otherwise.
	 */
	public static function validate_length( $value, $min = 0, $max = 255 ) {
		$length = strlen( $value );
		return $length >= $min && $length <= $max;
	}

	/**
	 * Validate numeric value
	 *
	 * @since 1.0.0
	 * @param mixed $value Value to validate.
	 * @param int   $min Minimum value.
	 * @param int   $max Maximum value.
	 * @return bool True if valid, false otherwise.
	 */
	public static function validate_number( $value, $min = null, $max = null ) {
		if ( ! is_numeric( $value ) ) {
			return false;
		}

		if ( null !== $min && $value < $min ) {
			return false;
		}

		if ( null !== $max && $value > $max ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate in array
	 *
	 * @since 1.0.0
	 * @param mixed $value Value to check.
	 * @param array $allowed Allowed values.
	 * @return bool True if value is in allowed array, false otherwise.
	 */
	public static function validate_in_array( $value, $allowed ) {
		return in_array( $value, $allowed, true );
	}

	/**
	 * Validate passport number
	 *
	 * @since 1.0.0
	 * @param string $passport Passport number to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public static function validate_passport( $passport ) {
		// Basic validation: alphanumeric, 6-9 characters
		return (bool) preg_match( '/^[A-Z0-9]{6,9}$/i', $passport );
	}

	/**
	 * Validate postal code
	 *
	 * @since 1.0.0
	 * @param string $postal Postal code to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public static function validate_postal_code( $postal ) {
		// Alphanumeric with optional spaces and hyphens, 3-10 characters
		return (bool) preg_match( '/^[A-Z0-9\s\-]{3,10}$/i', $postal );
	}

	/**
	 * Validate array of data
	 *
	 * @since 1.0.0
	 * @param array $data Data to validate.
	 * @param array $rules Validation rules.
	 * @return array Array with 'valid' boolean and 'errors' array.
	 */
	public static function validate_data( $data, $rules ) {
		$errors = array();

		foreach ( $rules as $field => $rule_set ) {
			$value = isset( $data[ $field ] ) ? $data[ $field ] : '';

			foreach ( $rule_set as $rule ) {
				$rule_parts = explode( ':', $rule );
				$rule_name = $rule_parts[0];
				$rule_param = isset( $rule_parts[1] ) ? $rule_parts[1] : null;

				$is_valid = false;

				switch ( $rule_name ) {
					case 'required':
						$is_valid = self::validate_required( $value );
						if ( ! $is_valid ) {
							$errors[ $field ][] = sprintf( '%s is required', ucfirst( str_replace( '_', ' ', $field ) ) );
						}
						break;

					case 'email':
						if ( ! empty( $value ) ) {
							$is_valid = self::validate_email( $value );
							if ( ! $is_valid ) {
								$errors[ $field ][] = sprintf( '%s must be a valid email', ucfirst( str_replace( '_', ' ', $field ) ) );
							}
						}
						break;

					case 'phone':
						if ( ! empty( $value ) ) {
							$is_valid = self::validate_phone( $value );
							if ( ! $is_valid ) {
								$errors[ $field ][] = sprintf( '%s must be a valid phone number', ucfirst( str_replace( '_', ' ', $field ) ) );
							}
						}
						break;

					case 'date':
						if ( ! empty( $value ) ) {
							$is_valid = self::validate_date( $value );
							if ( ! $is_valid ) {
								$errors[ $field ][] = sprintf( '%s must be a valid date', ucfirst( str_replace( '_', ' ', $field ) ) );
							}
						}
						break;

					case 'currency':
						if ( ! empty( $value ) ) {
							$is_valid = self::validate_currency( $value );
							if ( ! $is_valid ) {
								$errors[ $field ][] = sprintf( '%s must be a valid amount', ucfirst( str_replace( '_', ' ', $field ) ) );
							}
						}
						break;

					case 'min':
						if ( ! empty( $value ) && $rule_param ) {
							$is_valid = self::validate_length( $value, (int) $rule_param );
							if ( ! $is_valid ) {
								$errors[ $field ][] = sprintf( '%s must be at least %d characters', ucfirst( str_replace( '_', ' ', $field ) ), $rule_param );
							}
						}
						break;

					case 'max':
						if ( ! empty( $value ) && $rule_param ) {
							$is_valid = self::validate_length( $value, 0, (int) $rule_param );
							if ( ! $is_valid ) {
								$errors[ $field ][] = sprintf( '%s must not exceed %d characters', ucfirst( str_replace( '_', ' ', $field ) ), $rule_param );
							}
						}
						break;
				}
			}
		}

		return array(
			'valid'  => empty( $errors ),
			'errors' => $errors,
		);
	}

	/**
	 * Sanitize text input
	 *
	 * @since 1.0.0
	 * @param string $input Input to sanitize.
	 * @return string Sanitized input.
	 */
	public static function sanitize_text( $input ) {
		return sanitize_text_field( $input );
	}

	/**
	 * Sanitize textarea input
	 *
	 * @since 1.0.0
	 * @param string $input Input to sanitize.
	 * @return string Sanitized input.
	 */
	public static function sanitize_textarea( $input ) {
		return sanitize_textarea_field( $input );
	}

	/**
	 * Sanitize email input
	 *
	 * @since 1.0.0
	 * @param string $input Input to sanitize.
	 * @return string Sanitized input.
	 */
	public static function sanitize_email( $input ) {
		return sanitize_email( $input );
	}
}
