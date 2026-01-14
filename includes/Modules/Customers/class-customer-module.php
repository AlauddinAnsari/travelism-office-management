<?php
/**
 * Customer Module Class
 *
 * Handles all customer-related operations including CRUD operations,
 * validation, and customer management functionality.
 *
 * @package TravelismOfficeManagement
 * @subpackage Modules
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Customer_Module
 *
 * Main class for managing customer operations
 *
 * @since 1.0.0
 */
class Customer_Module {

	/**
	 * Database instance
	 *
	 * @var object
	 */
	private $db;

	/**
	 * Table name for customers
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Customers table prefix
	 *
	 * @var string
	 */
	private $table_prefix = 'travelism_customers';

	/**
	 * Error messages
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Success messages
	 *
	 * @var array
	 */
	private $messages = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->db         = $wpdb;
		$this->table_name = $this->db->prefix . $this->table_prefix;
	}

	/**
	 * Create a new customer
	 *
	 * @param array $customer_data Customer data array
	 *
	 * @return int|false Customer ID on success, false on failure
	 * @since 1.0.0
	 */
	public function create_customer( $customer_data ) {
		// Validate customer data
		if ( ! $this->validate_customer_data( $customer_data ) ) {
			return false;
		}

		// Sanitize data
		$sanitized_data = $this->sanitize_customer_data( $customer_data );

		// Check for duplicate email
		if ( $this->email_exists( $sanitized_data['email'] ) ) {
			$this->errors[] = __( 'A customer with this email already exists.', 'travelism-office-management' );
			return false;
		}

		// Insert customer into database
		$insert_result = $this->db->insert(
			$this->table_name,
			array(
				'first_name'   => $sanitized_data['first_name'],
				'last_name'    => $sanitized_data['last_name'],
				'email'        => $sanitized_data['email'],
				'phone'        => $sanitized_data['phone'],
				'address'      => $sanitized_data['address'],
				'city'         => $sanitized_data['city'],
				'state'        => $sanitized_data['state'],
				'postal_code'  => $sanitized_data['postal_code'],
				'country'      => $sanitized_data['country'],
				'company'      => $sanitized_data['company'],
				'notes'        => $sanitized_data['notes'],
				'customer_type' => $sanitized_data['customer_type'],
				'status'       => $sanitized_data['status'],
				'created_at'   => current_time( 'mysql' ),
				'updated_at'   => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $insert_result ) {
			$this->errors[] = __( 'Failed to create customer. Please try again.', 'travelism-office-management' );
			return false;
		}

		$customer_id = $this->db->insert_id;
		$this->messages[] = sprintf(
			__( 'Customer %s created successfully.', 'travelism-office-management' ),
			esc_html( $sanitized_data['first_name'] . ' ' . $sanitized_data['last_name'] )
		);

		return $customer_id;
	}

	/**
	 * Get a customer by ID
	 *
	 * @param int $customer_id Customer ID
	 *
	 * @return array|null Customer data or null if not found
	 * @since 1.0.0
	 */
	public function get_customer( $customer_id ) {
		$customer_id = absint( $customer_id );

		if ( ! $customer_id ) {
			$this->errors[] = __( 'Invalid customer ID.', 'travelism-office-management' );
			return null;
		}

		$query = $this->db->prepare(
			"SELECT * FROM {$this->table_name} WHERE id = %d",
			$customer_id
		);

		$customer = $this->db->get_row( $query, ARRAY_A );

		if ( ! $customer ) {
			$this->errors[] = __( 'Customer not found.', 'travelism-office-management' );
			return null;
		}

		return $customer;
	}

	/**
	 * Get all customers with optional filtering
	 *
	 * @param array $args Arguments for filtering and pagination
	 *
	 * @return array Array of customers
	 * @since 1.0.0
	 */
	public function get_customers( $args = array() ) {
		$defaults = array(
			'orderby'  => 'created_at',
			'order'    => 'DESC',
			'limit'    => -1,
			'offset'   => 0,
			'status'   => '',
			'customer_type' => '',
			'search'   => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$query = "SELECT * FROM {$this->table_name} WHERE 1=1";

		// Status filter
		if ( ! empty( $args['status'] ) ) {
			$query .= $this->db->prepare( " AND status = %s", sanitize_text_field( $args['status'] ) );
		}

		// Customer type filter
		if ( ! empty( $args['customer_type'] ) ) {
			$query .= $this->db->prepare( " AND customer_type = %s", sanitize_text_field( $args['customer_type'] ) );
		}

		// Search filter
		if ( ! empty( $args['search'] ) ) {
			$search = sanitize_text_field( $args['search'] );
			$query .= $this->db->prepare(
				" AND (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR phone LIKE %s)",
				'%' . $search . '%',
				'%' . $search . '%',
				'%' . $search . '%',
				'%' . $search . '%'
			);
		}

		// Orderby
		$allowed_orderby = array( 'id', 'first_name', 'last_name', 'email', 'created_at', 'updated_at' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order           = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		$query .= " ORDER BY {$orderby} {$order}";

		// Limit and offset
		if ( 0 < absint( $args['limit'] ) ) {
			$query .= $this->db->prepare( " LIMIT %d OFFSET %d", absint( $args['limit'] ), absint( $args['offset'] ) );
		}

		return $this->db->get_results( $query, ARRAY_A );
	}

	/**
	 * Update a customer
	 *
	 * @param int   $customer_id Customer ID
	 * @param array $customer_data Customer data to update
	 *
	 * @return bool True on success, false on failure
	 * @since 1.0.0
	 */
	public function update_customer( $customer_id, $customer_data ) {
		$customer_id = absint( $customer_id );

		if ( ! $customer_id ) {
			$this->errors[] = __( 'Invalid customer ID.', 'travelism-office-management' );
			return false;
		}

		// Check if customer exists
		if ( ! $this->customer_exists( $customer_id ) ) {
			$this->errors[] = __( 'Customer not found.', 'travelism-office-management' );
			return false;
		}

		// Validate customer data
		if ( ! $this->validate_customer_data( $customer_data, true ) ) {
			return false;
		}

		// Sanitize data
		$sanitized_data = $this->sanitize_customer_data( $customer_data );

		// Check for duplicate email (excluding current customer)
		if ( isset( $sanitized_data['email'] ) ) {
			$existing_customer = $this->get_customer_by_email( $sanitized_data['email'] );
			if ( $existing_customer && (int) $existing_customer['id'] !== $customer_id ) {
				$this->errors[] = __( 'A customer with this email already exists.', 'travelism-office-management' );
				return false;
			}
		}

		// Prepare update data
		$update_data = array();
		$format      = array();

		$allowed_fields = array(
			'first_name',
			'last_name',
			'email',
			'phone',
			'address',
			'city',
			'state',
			'postal_code',
			'country',
			'company',
			'notes',
			'customer_type',
			'status',
		);

		foreach ( $allowed_fields as $field ) {
			if ( isset( $sanitized_data[ $field ] ) ) {
				$update_data[ $field ] = $sanitized_data[ $field ];
				$format[]              = '%s';
			}
		}

		// Always update the updated_at timestamp
		$update_data['updated_at'] = current_time( 'mysql' );
		$format[]                  = '%s';

		if ( empty( $update_data ) ) {
			$this->errors[] = __( 'No valid data provided for update.', 'travelism-office-management' );
			return false;
		}

		// Update customer
		$update_result = $this->db->update(
			$this->table_name,
			$update_data,
			array( 'id' => $customer_id ),
			$format,
			array( '%d' )
		);

		if ( false === $update_result ) {
			$this->errors[] = __( 'Failed to update customer. Please try again.', 'travelism-office-management' );
			return false;
		}

		$this->messages[] = __( 'Customer updated successfully.', 'travelism-office-management' );
		return true;
	}

	/**
	 * Delete a customer
	 *
	 * @param int $customer_id Customer ID
	 *
	 * @return bool True on success, false on failure
	 * @since 1.0.0
	 */
	public function delete_customer( $customer_id ) {
		$customer_id = absint( $customer_id );

		if ( ! $customer_id ) {
			$this->errors[] = __( 'Invalid customer ID.', 'travelism-office-management' );
			return false;
		}

		// Check if customer exists
		$customer = $this->get_customer( $customer_id );
		if ( ! $customer ) {
			return false;
		}

		// Delete customer
		$delete_result = $this->db->delete(
			$this->table_name,
			array( 'id' => $customer_id ),
			array( '%d' )
		);

		if ( false === $delete_result ) {
			$this->errors[] = __( 'Failed to delete customer. Please try again.', 'travelism-office-management' );
			return false;
		}

		$this->messages[] = sprintf(
			__( 'Customer %s deleted successfully.', 'travelism-office-management' ),
			esc_html( $customer['first_name'] . ' ' . $customer['last_name'] )
		);

		return true;
	}

	/**
	 * Get customer count
	 *
	 * @param array $args Arguments for filtering
	 *
	 * @return int Customer count
	 * @since 1.0.0
	 */
	public function get_customer_count( $args = array() ) {
		$defaults = array(
			'status'   => '',
			'customer_type' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$query = "SELECT COUNT(*) FROM {$this->table_name} WHERE 1=1";

		if ( ! empty( $args['status'] ) ) {
			$query .= $this->db->prepare( " AND status = %s", sanitize_text_field( $args['status'] ) );
		}

		if ( ! empty( $args['customer_type'] ) ) {
			$query .= $this->db->prepare( " AND customer_type = %s", sanitize_text_field( $args['customer_type'] ) );
		}

		return absint( $this->db->get_var( $query ) );
	}

	/**
	 * Get customer by email
	 *
	 * @param string $email Customer email
	 *
	 * @return array|null Customer data or null if not found
	 * @since 1.0.0
	 */
	public function get_customer_by_email( $email ) {
		$email = sanitize_email( $email );

		if ( ! is_email( $email ) ) {
			return null;
		}

		$query = $this->db->prepare(
			"SELECT * FROM {$this->table_name} WHERE email = %s",
			$email
		);

		return $this->db->get_row( $query, ARRAY_A );
	}

	/**
	 * Check if email exists
	 *
	 * @param string $email Customer email
	 *
	 * @return bool True if email exists, false otherwise
	 * @since 1.0.0
	 */
	public function email_exists( $email ) {
		return null !== $this->get_customer_by_email( $email );
	}

	/**
	 * Check if customer exists
	 *
	 * @param int $customer_id Customer ID
	 *
	 * @return bool True if customer exists, false otherwise
	 * @since 1.0.0
	 */
	public function customer_exists( $customer_id ) {
		$customer_id = absint( $customer_id );

		$query = $this->db->prepare(
			"SELECT id FROM {$this->table_name} WHERE id = %d",
			$customer_id
		);

		return ! empty( $this->db->get_row( $query ) );
	}

	/**
	 * Validate customer data
	 *
	 * @param array $customer_data Customer data to validate
	 * @param bool  $is_update Whether this is an update operation
	 *
	 * @return bool True if valid, false otherwise
	 * @since 1.0.0
	 */
	private function validate_customer_data( $customer_data, $is_update = false ) {
		$this->errors = array();

		// For create operation, first name and last name are required
		if ( ! $is_update ) {
			if ( empty( $customer_data['first_name'] ) ) {
				$this->errors[] = __( 'First name is required.', 'travelism-office-management' );
			}

			if ( empty( $customer_data['last_name'] ) ) {
				$this->errors[] = __( 'Last name is required.', 'travelism-office-management' );
			}

			if ( empty( $customer_data['email'] ) ) {
				$this->errors[] = __( 'Email is required.', 'travelism-office-management' );
			} elseif ( ! is_email( $customer_data['email'] ) ) {
				$this->errors[] = __( 'Invalid email format.', 'travelism-office-management' );
			}
		}

		// Validate email if provided
		if ( isset( $customer_data['email'] ) && ! empty( $customer_data['email'] ) ) {
			if ( ! is_email( $customer_data['email'] ) ) {
				$this->errors[] = __( 'Invalid email format.', 'travelism-office-management' );
			}
		}

		// Validate phone if provided
		if ( isset( $customer_data['phone'] ) && ! empty( $customer_data['phone'] ) ) {
			if ( ! $this->validate_phone( $customer_data['phone'] ) ) {
				$this->errors[] = __( 'Invalid phone format.', 'travelism-office-management' );
			}
		}

		// Validate postal code if provided
		if ( isset( $customer_data['postal_code'] ) && ! empty( $customer_data['postal_code'] ) ) {
			if ( ! $this->validate_postal_code( $customer_data['postal_code'] ) ) {
				$this->errors[] = __( 'Invalid postal code format.', 'travelism-office-management' );
			}
		}

		// Validate customer type if provided
		if ( isset( $customer_data['customer_type'] ) && ! empty( $customer_data['customer_type'] ) ) {
			$allowed_types = array( 'individual', 'corporate', 'vip', 'travel_agent' );
			if ( ! in_array( $customer_data['customer_type'], $allowed_types, true ) ) {
				$this->errors[] = __( 'Invalid customer type.', 'travelism-office-management' );
			}
		}

		// Validate status if provided
		if ( isset( $customer_data['status'] ) && ! empty( $customer_data['status'] ) ) {
			$allowed_statuses = array( 'active', 'inactive', 'suspended' );
			if ( ! in_array( $customer_data['status'], $allowed_statuses, true ) ) {
				$this->errors[] = __( 'Invalid status.', 'travelism-office-management' );
			}
		}

		return empty( $this->errors );
	}

	/**
	 * Sanitize customer data
	 *
	 * @param array $customer_data Customer data to sanitize
	 *
	 * @return array Sanitized customer data
	 * @since 1.0.0
	 */
	private function sanitize_customer_data( $customer_data ) {
		$sanitized = array();

		$fields = array(
			'first_name',
			'last_name',
			'email',
			'phone',
			'address',
			'city',
			'state',
			'postal_code',
			'country',
			'company',
			'notes',
			'customer_type',
			'status',
		);

		foreach ( $fields as $field ) {
			if ( isset( $customer_data[ $field ] ) ) {
				if ( 'email' === $field ) {
					$sanitized[ $field ] = sanitize_email( $customer_data[ $field ] );
				} elseif ( 'notes' === $field ) {
					$sanitized[ $field ] = wp_kses_post( $customer_data[ $field ] );
				} else {
					$sanitized[ $field ] = sanitize_text_field( $customer_data[ $field ] );
				}
			} else {
				$sanitized[ $field ] = '';
			}
		}

		return $sanitized;
	}

	/**
	 * Validate phone number
	 *
	 * @param string $phone Phone number to validate
	 *
	 * @return bool True if valid, false otherwise
	 * @since 1.0.0
	 */
	private function validate_phone( $phone ) {
		// Remove common phone characters
		$phone = preg_replace( '/[^0-9+\-\s()]/', '', $phone );

		// Basic validation: at least 10 digits
		$digits = preg_replace( '/[^0-9]/', '', $phone );

		return strlen( $digits ) >= 10;
	}

	/**
	 * Validate postal code
	 *
	 * @param string $postal_code Postal code to validate
	 *
	 * @return bool True if valid, false otherwise
	 * @since 1.0.0
	 */
	private function validate_postal_code( $postal_code ) {
		// Basic validation: 3-10 alphanumeric characters
		return preg_match( '/^[a-zA-Z0-9\s\-]{3,10}$/', $postal_code );
	}

	/**
	 * Bulk update customer status
	 *
	 * @param array  $customer_ids Array of customer IDs
	 * @param string $status New status
	 *
	 * @return bool True on success, false on failure
	 * @since 1.0.0
	 */
	public function bulk_update_status( $customer_ids, $status ) {
		if ( ! is_array( $customer_ids ) || empty( $customer_ids ) ) {
			$this->errors[] = __( 'Invalid customer IDs provided.', 'travelism-office-management' );
			return false;
		}

		$allowed_statuses = array( 'active', 'inactive', 'suspended' );
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			$this->errors[] = __( 'Invalid status.', 'travelism-office-management' );
			return false;
		}

		$customer_ids = array_map( 'absint', $customer_ids );
		$placeholders = implode( ',', array_fill( 0, count( $customer_ids ), '%d' ) );

		$query = "UPDATE {$this->table_name} SET status = %s, updated_at = %s WHERE id IN ({$placeholders})";

		$params = array_merge(
			array( $status, current_time( 'mysql' ) ),
			$customer_ids
		);

		$update_result = $this->db->query( $this->db->prepare( $query, $params ) );

		if ( false === $update_result ) {
			$this->errors[] = __( 'Failed to update customers. Please try again.', 'travelism-office-management' );
			return false;
		}

		$this->messages[] = sprintf(
			__( '%d customer(s) status updated successfully.', 'travelism-office-management' ),
			count( $customer_ids )
		);

		return true;
	}

	/**
	 * Get customer statistics
	 *
	 * @return array Customer statistics
	 * @since 1.0.0
	 */
	public function get_customer_statistics() {
		$stats = array(
			'total'       => $this->get_customer_count(),
			'active'      => $this->get_customer_count( array( 'status' => 'active' ) ),
			'inactive'    => $this->get_customer_count( array( 'status' => 'inactive' ) ),
			'suspended'   => $this->get_customer_count( array( 'status' => 'suspended' ) ),
			'individual'  => $this->get_customer_count( array( 'customer_type' => 'individual' ) ),
			'corporate'   => $this->get_customer_count( array( 'customer_type' => 'corporate' ) ),
			'vip'         => $this->get_customer_count( array( 'customer_type' => 'vip' ) ),
			'travel_agent' => $this->get_customer_count( array( 'customer_type' => 'travel_agent' ) ),
		);

		return $stats;
	}

	/**
	 * Get error messages
	 *
	 * @return array Error messages
	 * @since 1.0.0
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Get success messages
	 *
	 * @return array Success messages
	 * @since 1.0.0
	 */
	public function get_messages() {
		return $this->messages;
	}

	/**
	 * Clear error messages
	 *
	 * @since 1.0.0
	 */
	public function clear_errors() {
		$this->errors = array();
	}

	/**
	 * Clear success messages
	 *
	 * @since 1.0.0
	 */
	public function clear_messages() {
		$this->messages = array();
	}

}
