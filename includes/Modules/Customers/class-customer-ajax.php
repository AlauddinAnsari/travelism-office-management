<?php
/**
 * Customer AJAX Handler
 *
 * Handles AJAX requests for customer operations with secure nonce verification
 * and role-based capability checks.
 *
 * @package Travelism_Office_Management
 * @subpackage Modules/Customers
 * @version 1.0.0
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer AJAX Handler Class
 *
 * Manages AJAX operations for customer CRUD and bulk operations
 * with comprehensive security measures.
 */
class Customer_AJAX {

	/**
	 * Nonce action name
	 *
	 * @var string
	 */
	private $nonce_action = 'customer_ajax_nonce';

	/**
	 * Nonce name
	 *
	 * @var string
	 */
	private $nonce_name = 'customer_nonce';

	/**
	 * Constructor
	 *
	 * Initializes AJAX hooks
	 */
	public function __construct() {
		$this->register_hooks();
	}

	/**
	 * Register AJAX hooks
	 *
	 * Registers all AJAX handlers for customer operations
	 *
	 * @return void
	 */
	private function register_hooks() {
		// Create customer
		add_action( 'wp_ajax_create_customer', array( $this, 'create_customer' ) );

		// Read customer
		add_action( 'wp_ajax_read_customer', array( $this, 'read_customer' ) );

		// Update customer
		add_action( 'wp_ajax_update_customer', array( $this, 'update_customer' ) );

		// Delete customer
		add_action( 'wp_ajax_delete_customer', array( $this, 'delete_customer' ) );

		// Get customers list
		add_action( 'wp_ajax_get_customers_list', array( $this, 'get_customers_list' ) );

		// Bulk delete customers
		add_action( 'wp_ajax_bulk_delete_customers', array( $this, 'bulk_delete_customers' ) );

		// Bulk update customers
		add_action( 'wp_ajax_bulk_update_customers', array( $this, 'bulk_update_customers' ) );

		// Search customers
		add_action( 'wp_ajax_search_customers', array( $this, 'search_customers' ) );
	}

	/**
	 * Verify nonce and capability
	 *
	 * Verifies nonce validity and user capability
	 *
	 * @param string $capability Required capability
	 * @return bool True if nonce and capability are valid
	 */
	private function verify_nonce_and_capability( $capability ) {
		// Check nonce
		if ( ! isset( $_REQUEST[ $this->nonce_name ] ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Security token missing.', 'travelism-office-management' ),
				),
				403
			);
			return false;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST[ $this->nonce_name ] ) ), $this->nonce_action ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Security token invalid.', 'travelism-office-management' ),
				),
				403
			);
			return false;
		}

		// Check capability
		if ( ! current_user_can( $capability ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You do not have permission to perform this action.', 'travelism-office-management' ),
				),
				403
			);
			return false;
		}

		return true;
	}

	/**
	 * Sanitize customer data
	 *
	 * Sanitizes customer input data
	 *
	 * @param array $data Customer data
	 * @return array Sanitized customer data
	 */
	private function sanitize_customer_data( $data ) {
		return array(
			'first_name'  => sanitize_text_field( $data['first_name'] ?? '' ),
			'last_name'   => sanitize_text_field( $data['last_name'] ?? '' ),
			'email'       => sanitize_email( $data['email'] ?? '' ),
			'phone'       => sanitize_text_field( $data['phone'] ?? '' ),
			'company'     => sanitize_text_field( $data['company'] ?? '' ),
			'country'     => sanitize_text_field( $data['country'] ?? '' ),
			'city'        => sanitize_text_field( $data['city'] ?? '' ),
			'address'     => sanitize_textarea_field( $data['address'] ?? '' ),
			'postal_code' => sanitize_text_field( $data['postal_code'] ?? '' ),
			'status'      => in_array( $data['status'] ?? 'active', array( 'active', 'inactive' ), true ) ? $data['status'] : 'active',
		);
	}

	/**
	 * Validate customer data
	 *
	 * Validates customer required fields
	 *
	 * @param array $data Customer data
	 * @return array Array with 'valid' bool and 'errors' array
	 */
	private function validate_customer_data( $data ) {
		$errors = array();

		if ( empty( $data['first_name'] ) ) {
			$errors['first_name'] = esc_html__( 'First name is required.', 'travelism-office-management' );
		}

		if ( empty( $data['last_name'] ) ) {
			$errors['last_name'] = esc_html__( 'Last name is required.', 'travelism-office-management' );
		}

		if ( empty( $data['email'] ) || ! is_email( $data['email'] ) ) {
			$errors['email'] = esc_html__( 'Valid email is required.', 'travelism-office-management' );
		}

		if ( empty( $data['phone'] ) ) {
			$errors['phone'] = esc_html__( 'Phone number is required.', 'travelism-office-management' );
		}

		return array(
			'valid'  => empty( $errors ),
			'errors' => $errors,
		);
	}

	/**
	 * Create customer
	 *
	 * Handles AJAX request to create a new customer
	 *
	 * @return void
	 */
	public function create_customer() {
		// Verify nonce and capability
		if ( ! $this->verify_nonce_and_capability( 'manage_customers_create' ) ) {
			return;
		}

		try {
			// Get POST data
			$data = isset( $_POST['customer_data'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['customer_data'] ) ), true ) : array();

			if ( empty( $data ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'No customer data provided.', 'travelism-office-management' ),
					)
				);
				return;
			}

			// Sanitize data
			$sanitized_data = $this->sanitize_customer_data( $data );

			// Validate data
			$validation = $this->validate_customer_data( $sanitized_data );

			if ( ! $validation['valid'] ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'Validation failed.', 'travelism-office-management' ),
						'errors'  => $validation['errors'],
					)
				);
				return;
			}

			// Check if email already exists
			$existing_customer = get_posts(
				array(
					'post_type'      => 'tom_customer',
					'meta_query'     => array(
						array(
							'key'   => '_customer_email',
							'value' => $sanitized_data['email'],
						),
					),
					'posts_per_page' => 1,
				)
			);

			if ( ! empty( $existing_customer ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'A customer with this email already exists.', 'travelism-office-management' ),
					)
				);
				return;
			}

			// Create customer post
			$customer_id = wp_insert_post(
				array(
					'post_type'    => 'tom_customer',
					'post_title'   => $sanitized_data['first_name'] . ' ' . $sanitized_data['last_name'],
					'post_status'  => 'active' === $sanitized_data['status'] ? 'publish' : 'draft',
					'post_author'  => get_current_user_id(),
					'post_content' => '',
				)
			);

			if ( is_wp_error( $customer_id ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'Failed to create customer.', 'travelism-office-management' ),
					)
				);
				return;
			}

			// Save customer meta
			update_post_meta( $customer_id, '_customer_first_name', $sanitized_data['first_name'] );
			update_post_meta( $customer_id, '_customer_last_name', $sanitized_data['last_name'] );
			update_post_meta( $customer_id, '_customer_email', $sanitized_data['email'] );
			update_post_meta( $customer_id, '_customer_phone', $sanitized_data['phone'] );
			update_post_meta( $customer_id, '_customer_company', $sanitized_data['company'] );
			update_post_meta( $customer_id, '_customer_country', $sanitized_data['country'] );
			update_post_meta( $customer_id, '_customer_city', $sanitized_data['city'] );
			update_post_meta( $customer_id, '_customer_address', $sanitized_data['address'] );
			update_post_meta( $customer_id, '_customer_postal_code', $sanitized_data['postal_code'] );
			update_post_meta( $customer_id, '_customer_created_date', current_time( 'mysql' ) );

			/**
			 * Action hook after customer creation
			 *
			 * @param int   $customer_id Customer ID
			 * @param array $sanitized_data Customer data
			 */
			do_action( 'tom_customer_created', $customer_id, $sanitized_data );

			wp_send_json_success(
				array(
					'message'     => esc_html__( 'Customer created successfully.', 'travelism-office-management' ),
					'customer_id' => $customer_id,
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'An error occurred while creating the customer.', 'travelism-office-management' ),
				)
			);
		}
	}

	/**
	 * Read customer
	 *
	 * Handles AJAX request to read a customer
	 *
	 * @return void
	 */
	public function read_customer() {
		// Verify nonce and capability
		if ( ! $this->verify_nonce_and_capability( 'manage_customers_read' ) ) {
			return;
		}

		try {
			$customer_id = isset( $_REQUEST['customer_id'] ) ? absint( $_REQUEST['customer_id'] ) : 0;

			if ( ! $customer_id ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'Customer ID is missing.', 'travelism-office-management' ),
					)
				);
				return;
			}

			$customer = get_post( $customer_id );

			if ( ! $customer || 'tom_customer' !== $customer->post_type ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'Customer not found.', 'travelism-office-management' ),
					),
					404
				);
				return;
			}

			// Gather customer data
			$customer_data = array(
				'id'           => $customer_id,
				'first_name'   => get_post_meta( $customer_id, '_customer_first_name', true ),
				'last_name'    => get_post_meta( $customer_id, '_customer_last_name', true ),
				'email'        => get_post_meta( $customer_id, '_customer_email', true ),
				'phone'        => get_post_meta( $customer_id, '_customer_phone', true ),
				'company'      => get_post_meta( $customer_id, '_customer_company', true ),
				'country'      => get_post_meta( $customer_id, '_customer_country', true ),
				'city'         => get_post_meta( $customer_id, '_customer_city', true ),
				'address'      => get_post_meta( $customer_id, '_customer_address', true ),
				'postal_code'  => get_post_meta( $customer_id, '_customer_postal_code', true ),
				'status'       => 'publish' === $customer->post_status ? 'active' : 'inactive',
				'created_date' => get_post_meta( $customer_id, '_customer_created_date', true ),
			);

			wp_send_json_success(
				array(
					'message'  => esc_html__( 'Customer retrieved successfully.', 'travelism-office-management' ),
					'customer' => $customer_data,
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'An error occurred while retrieving the customer.', 'travelism-office-management' ),
				)
			);
		}
	}

	/**
	 * Update customer
	 *
	 * Handles AJAX request to update a customer
	 *
	 * @return void
	 */
	public function update_customer() {
		// Verify nonce and capability
		if ( ! $this->verify_nonce_and_capability( 'manage_customers_update' ) ) {
			return;
		}

		try {
			$customer_id = isset( $_REQUEST['customer_id'] ) ? absint( $_REQUEST['customer_id'] ) : 0;

			if ( ! $customer_id ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'Customer ID is missing.', 'travelism-office-management' ),
					)
				);
				return;
			}

			$customer = get_post( $customer_id );

			if ( ! $customer || 'tom_customer' !== $customer->post_type ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'Customer not found.', 'travelism-office-management' ),
					),
					404
				);
				return;
			}

			// Get POST data
			$data = isset( $_POST['customer_data'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['customer_data'] ) ), true ) : array();

			if ( empty( $data ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'No customer data provided.', 'travelism-office-management' ),
					)
				);
				return;
			}

			// Sanitize data
			$sanitized_data = $this->sanitize_customer_data( $data );

			// Validate data
			$validation = $this->validate_customer_data( $sanitized_data );

			if ( ! $validation['valid'] ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'Validation failed.', 'travelism-office-management' ),
						'errors'  => $validation['errors'],
					)
				);
				return;
			}

			// Check if email exists for different customer
			$existing_customer = get_posts(
				array(
					'post_type'      => 'tom_customer',
					'meta_query'     => array(
						array(
							'key'   => '_customer_email',
							'value' => $sanitized_data['email'],
						),
					),
					'posts_per_page' => 1,
					'exclude'        => $customer_id,
				)
			);

			if ( ! empty( $existing_customer ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'This email is already used by another customer.', 'travelism-office-management' ),
					)
				);
				return;
			}

			// Update customer post
			wp_update_post(
				array(
					'ID'          => $customer_id,
					'post_title'  => $sanitized_data['first_name'] . ' ' . $sanitized_data['last_name'],
					'post_status' => 'active' === $sanitized_data['status'] ? 'publish' : 'draft',
				)
			);

			// Update customer meta
			update_post_meta( $customer_id, '_customer_first_name', $sanitized_data['first_name'] );
			update_post_meta( $customer_id, '_customer_last_name', $sanitized_data['last_name'] );
			update_post_meta( $customer_id, '_customer_email', $sanitized_data['email'] );
			update_post_meta( $customer_id, '_customer_phone', $sanitized_data['phone'] );
			update_post_meta( $customer_id, '_customer_company', $sanitized_data['company'] );
			update_post_meta( $customer_id, '_customer_country', $sanitized_data['country'] );
			update_post_meta( $customer_id, '_customer_city', $sanitized_data['city'] );
			update_post_meta( $customer_id, '_customer_address', $sanitized_data['address'] );
			update_post_meta( $customer_id, '_customer_postal_code', $sanitized_data['postal_code'] );
			update_post_meta( $customer_id, '_customer_updated_date', current_time( 'mysql' ) );

			/**
			 * Action hook after customer update
			 *
			 * @param int   $customer_id Customer ID
			 * @param array $sanitized_data Customer data
			 */
			do_action( 'tom_customer_updated', $customer_id, $sanitized_data );

			wp_send_json_success(
				array(
					'message'     => esc_html__( 'Customer updated successfully.', 'travelism-office-management' ),
					'customer_id' => $customer_id,
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'An error occurred while updating the customer.', 'travelism-office-management' ),
				)
			);
		}
	}

	/**
	 * Delete customer
	 *
	 * Handles AJAX request to delete a customer
	 *
	 * @return void
	 */
	public function delete_customer() {
		// Verify nonce and capability
		if ( ! $this->verify_nonce_and_capability( 'manage_customers_delete' ) ) {
			return;
		}

		try {
			$customer_id = isset( $_REQUEST['customer_id'] ) ? absint( $_REQUEST['customer_id'] ) : 0;

			if ( ! $customer_id ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'Customer ID is missing.', 'travelism-office-management' ),
					)
				);
				return;
			}

			$customer = get_post( $customer_id );

			if ( ! $customer || 'tom_customer' !== $customer->post_type ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'Customer not found.', 'travelism-office-management' ),
					),
					404
				);
				return;
			}

			// Store customer data before deletion for action hook
			$customer_data = array(
				'id'    => $customer_id,
				'email' => get_post_meta( $customer_id, '_customer_email', true ),
			);

			// Delete customer
			$result = wp_delete_post( $customer_id, true );

			if ( ! $result ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'Failed to delete customer.', 'travelism-office-management' ),
					)
				);
				return;
			}

			/**
			 * Action hook after customer deletion
			 *
			 * @param array $customer_data Customer data
			 */
			do_action( 'tom_customer_deleted', $customer_data );

			wp_send_json_success(
				array(
					'message'     => esc_html__( 'Customer deleted successfully.', 'travelism-office-management' ),
					'customer_id' => $customer_id,
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'An error occurred while deleting the customer.', 'travelism-office-management' ),
				)
			);
		}
	}

	/**
	 * Get customers list
	 *
	 * Handles AJAX request to get paginated list of customers
	 *
	 * @return void
	 */
	public function get_customers_list() {
		// Verify nonce and capability
		if ( ! $this->verify_nonce_and_capability( 'manage_customers_read' ) ) {
			return;
		}

		try {
			$page       = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
			$per_page   = isset( $_REQUEST['per_page'] ) ? absint( $_REQUEST['per_page'] ) : 20;
			$search     = isset( $_REQUEST['search'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search'] ) ) : '';
			$status     = isset( $_REQUEST['status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status'] ) ) : '';
			$sort_by    = isset( $_REQUEST['sort_by'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sort_by'] ) ) : 'date';
			$sort_order = isset( $_REQUEST['sort_order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sort_order'] ) ) : 'DESC';

			// Validate sort parameters
			$allowed_sort = array( 'date', 'title', 'id' );
			$sort_by      = in_array( $sort_by, $allowed_sort, true ) ? $sort_by : 'date';
			$sort_order   = in_array( strtoupper( $sort_order ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $sort_order ) : 'DESC';

			// Build query args
			$args = array(
				'post_type'      => 'tom_customer',
				'paged'          => $page,
				'posts_per_page' => $per_page,
				'orderby'        => $sort_by,
				'order'          => $sort_order,
			);

			// Add search
			if ( ! empty( $search ) ) {
				$args['s'] = $search;
			}

			// Add status filter
			if ( ! empty( $status ) ) {
				$args['post_status'] = 'active' === $status ? 'publish' : 'draft';
			} else {
				$args['post_status'] = array( 'publish', 'draft' );
			}

			// Get customers
			$query = new WP_Query( $args );

			$customers = array();
			foreach ( $query->posts as $customer ) {
				$customers[] = array(
					'id'           => $customer->ID,
					'name'         => $customer->post_title,
					'first_name'   => get_post_meta( $customer->ID, '_customer_first_name', true ),
					'last_name'    => get_post_meta( $customer->ID, '_customer_last_name', true ),
					'email'        => get_post_meta( $customer->ID, '_customer_email', true ),
					'phone'        => get_post_meta( $customer->ID, '_customer_phone', true ),
					'company'      => get_post_meta( $customer->ID, '_customer_company', true ),
					'country'      => get_post_meta( $customer->ID, '_customer_country', true ),
					'city'         => get_post_meta( $customer->ID, '_customer_city', true ),
					'status'       => 'publish' === $customer->post_status ? 'active' : 'inactive',
					'created_date' => get_post_meta( $customer->ID, '_customer_created_date', true ),
				);
			}

			wp_send_json_success(
				array(
					'message'     => esc_html__( 'Customers retrieved successfully.', 'travelism-office-management' ),
					'customers'   => $customers,
					'total'       => $query->found_posts,
					'total_pages' => $query->max_num_pages,
					'page'        => $page,
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'An error occurred while retrieving customers.', 'travelism-office-management' ),
				)
			);
		}
	}

	/**
	 * Search customers
	 *
	 * Handles AJAX request to search customers
	 *
	 * @return void
	 */
	public function search_customers() {
		// Verify nonce and capability
		if ( ! $this->verify_nonce_and_capability( 'manage_customers_read' ) ) {
			return;
		}

		try {
			$search_term = isset( $_REQUEST['search'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search'] ) ) : '';
			$limit       = isset( $_REQUEST['limit'] ) ? absint( $_REQUEST['limit'] ) : 10;

			if ( empty( $search_term ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'Search term is required.', 'travelism-office-management' ),
					)
				);
				return;
			}

			$query = new WP_Query(
				array(
					'post_type'      => 'tom_customer',
					'posts_per_page' => $limit,
					's'              => $search_term,
					'post_status'    => array( 'publish', 'draft' ),
				)
			);

			$customers = array();
			foreach ( $query->posts as $customer ) {
				$customers[] = array(
					'id'    => $customer->ID,
					'name'  => $customer->post_title,
					'email' => get_post_meta( $customer->ID, '_customer_email', true ),
				);
			}

			wp_send_json_success(
				array(
					'message'   => esc_html__( 'Search results retrieved successfully.', 'travelism-office-management' ),
					'customers' => $customers,
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'An error occurred while searching customers.', 'travelism-office-management' ),
				)
			);
		}
	}

	/**
	 * Bulk delete customers
	 *
	 * Handles AJAX request to bulk delete customers
	 *
	 * @return void
	 */
	public function bulk_delete_customers() {
		// Verify nonce and capability
		if ( ! $this->verify_nonce_and_capability( 'manage_customers_delete' ) ) {
			return;
		}

		try {
			$customer_ids = isset( $_REQUEST['customer_ids'] ) ? array_map( 'absint', wp_unslash( (array) $_REQUEST['customer_ids'] ) ) : array();

			if ( empty( $customer_ids ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'No customers selected for deletion.', 'travelism-office-management' ),
					)
				);
				return;
			}

			$deleted_count = 0;
			$failed_count  = 0;

			foreach ( $customer_ids as $customer_id ) {
				$customer = get_post( $customer_id );

				if ( ! $customer || 'tom_customer' !== $customer->post_type ) {
					$failed_count++;
					continue;
				}

				// Store customer data before deletion for action hook
				$customer_data = array(
					'id'    => $customer_id,
					'email' => get_post_meta( $customer_id, '_customer_email', true ),
				);

				$result = wp_delete_post( $customer_id, true );

				if ( $result ) {
					$deleted_count++;

					/**
					 * Action hook after customer deletion
					 *
					 * @param array $customer_data Customer data
					 */
					do_action( 'tom_customer_deleted', $customer_data );
				} else {
					$failed_count++;
				}
			}

			wp_send_json_success(
				array(
					'message'        => esc_html__( 'Bulk delete completed.', 'travelism-office-management' ),
					'deleted_count'  => $deleted_count,
					'failed_count'   => $failed_count,
					'total_count'    => count( $customer_ids ),
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'An error occurred while deleting customers.', 'travelism-office-management' ),
				)
			);
		}
	}

	/**
	 * Bulk update customers
	 *
	 * Handles AJAX request to bulk update customers
	 *
	 * @return void
	 */
	public function bulk_update_customers() {
		// Verify nonce and capability
		if ( ! $this->verify_nonce_and_capability( 'manage_customers_update' ) ) {
			return;
		}

		try {
			$customer_ids = isset( $_REQUEST['customer_ids'] ) ? array_map( 'absint', wp_unslash( (array) $_REQUEST['customer_ids'] ) ) : array();
			$update_data  = isset( $_REQUEST['update_data'] ) ? json_decode( sanitize_text_field( wp_unslash( $_REQUEST['update_data'] ) ), true ) : array();

			if ( empty( $customer_ids ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'No customers selected for update.', 'travelism-office-management' ),
					)
				);
				return;
			}

			if ( empty( $update_data ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'No update data provided.', 'travelism-office-management' ),
					)
				);
				return;
			}

			$updated_count = 0;
			$failed_count  = 0;

			foreach ( $customer_ids as $customer_id ) {
				$customer = get_post( $customer_id );

				if ( ! $customer || 'tom_customer' !== $customer->post_type ) {
					$failed_count++;
					continue;
				}

				try {
					// Update status if provided
					if ( isset( $update_data['status'] ) && in_array( $update_data['status'], array( 'active', 'inactive' ), true ) ) {
						wp_update_post(
							array(
								'ID'          => $customer_id,
								'post_status' => 'active' === $update_data['status'] ? 'publish' : 'draft',
							)
						);
					}

					// Update other fields as needed
					if ( isset( $update_data['company'] ) ) {
						update_post_meta( $customer_id, '_customer_company', sanitize_text_field( $update_data['company'] ) );
					}

					if ( isset( $update_data['country'] ) ) {
						update_post_meta( $customer_id, '_customer_country', sanitize_text_field( $update_data['country'] ) );
					}

					if ( isset( $update_data['city'] ) ) {
						update_post_meta( $customer_id, '_customer_city', sanitize_text_field( $update_data['city'] ) );
					}

					update_post_meta( $customer_id, '_customer_updated_date', current_time( 'mysql' ) );

					$updated_count++;

					/**
					 * Action hook after bulk customer update
					 *
					 * @param int   $customer_id Customer ID
					 * @param array $update_data Data that was updated
					 */
					do_action( 'tom_customer_bulk_updated', $customer_id, $update_data );
				} catch ( Exception $e ) {
					$failed_count++;
				}
			}

			wp_send_json_success(
				array(
					'message'       => esc_html__( 'Bulk update completed.', 'travelism-office-management' ),
					'updated_count' => $updated_count,
					'failed_count'  => $failed_count,
					'total_count'   => count( $customer_ids ),
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'An error occurred while updating customers.', 'travelism-office-management' ),
				)
			);
		}
	}

	/**
	 * Get nonce
	 *
	 * Returns the nonce for AJAX requests
	 *
	 * @return string Nonce value
	 */
	public function get_nonce() {
		return wp_create_nonce( $this->nonce_action );
	}

	/**
	 * Get nonce action
	 *
	 * Returns the nonce action name
	 *
	 * @return string Nonce action
	 */
	public function get_nonce_action() {
		return $this->nonce_action;
	}

	/**
	 * Get nonce name
	 *
	 * Returns the nonce name
	 *
	 * @return string Nonce name
	 */
	public function get_nonce_name() {
		return $this->nonce_name;
	}
}

// Initialize the AJAX handler
new Customer_AJAX();
