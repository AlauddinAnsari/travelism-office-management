<?php
/**
 * Capabilities and Roles Management
 *
 * This class manages all user roles and their capabilities for the
 * Travelism Office Management system.
 *
 * @package Travelism_Office_Management
 * @subpackage Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class TOM_Capabilities
 *
 * Manages user roles and capabilities for the office management system.
 */
class TOM_Capabilities {

	/**
	 * Instance of the class.
	 *
	 * @var TOM_Capabilities
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return TOM_Capabilities
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_roles' ) );
		add_action( 'init', array( $this, 'register_capabilities' ) );
	}

	/**
	 * Register custom roles.
	 *
	 * @return void
	 */
	public function register_roles() {
		// CEO Role
		add_role(
			'tom_ceo',
			__( 'CEO', 'travelism-office-management' ),
			array(
				'read' => true,
			)
		);

		// Manager Role
		add_role(
			'tom_manager',
			__( 'Manager', 'travelism-office-management' ),
			array(
				'read' => true,
			)
		);

		// Employee Role
		add_role(
			'tom_employee',
			__( 'Employee', 'travelism-office-management' ),
			array(
				'read' => true,
			)
		);
	}

	/**
	 * Register and assign capabilities to roles.
	 *
	 * @return void
	 */
	public function register_capabilities() {
		$ceo_role      = get_role( 'tom_ceo' );
		$manager_role  = get_role( 'tom_manager' );
		$employee_role = get_role( 'tom_employee' );

		if ( $ceo_role ) {
			// CEO has all capabilities
			$this->add_ceo_capabilities( $ceo_role );
		}

		if ( $manager_role ) {
			// Manager has management capabilities
			$this->add_manager_capabilities( $manager_role );
		}

		if ( $employee_role ) {
			// Employee has limited capabilities
			$this->add_employee_capabilities( $employee_role );
		}
	}

	/**
	 * Add capabilities for CEO role.
	 *
	 * @param WP_Role $role CEO role object.
	 * @return void
	 */
	private function add_ceo_capabilities( $role ) {
		// Dashboard
		$role->add_cap( 'view_dashboard' );
		$role->add_cap( 'view_reports' );
		$role->add_cap( 'export_reports' );

		// Employee Management
		$role->add_cap( 'manage_employees' );
		$role->add_cap( 'create_employee' );
		$role->add_cap( 'edit_employee' );
		$role->add_cap( 'delete_employee' );
		$role->add_cap( 'view_employee' );

		// Department Management
		$role->add_cap( 'manage_departments' );
		$role->add_cap( 'create_department' );
		$role->add_cap( 'edit_department' );
		$role->add_cap( 'delete_department' );

		// Project Management
		$role->add_cap( 'manage_projects' );
		$role->add_cap( 'create_project' );
		$role->add_cap( 'edit_project' );
		$role->add_cap( 'delete_project' );
		$role->add_cap( 'assign_project' );

		// Task Management
		$role->add_cap( 'manage_tasks' );
		$role->add_cap( 'create_task' );
		$role->add_cap( 'edit_task' );
		$role->add_cap( 'delete_task' );
		$role->add_cap( 'assign_task' );

		// Leave Management
		$role->add_cap( 'manage_leaves' );
		$role->add_cap( 'approve_leave' );
		$role->add_cap( 'reject_leave' );
		$role->add_cap( 'view_leave_report' );

		// Attendance
		$role->add_cap( 'manage_attendance' );
		$role->add_cap( 'view_attendance' );
		$role->add_cap( 'export_attendance' );

		// Payroll
		$role->add_cap( 'manage_payroll' );
		$role->add_cap( 'process_payroll' );
		$role->add_cap( 'view_payroll' );
		$role->add_cap( 'export_payroll' );

		// Settings
		$role->add_cap( 'manage_settings' );
		$role->add_cap( 'manage_users' );
		$role->add_cap( 'manage_roles' );

		// Audit & Logs
		$role->add_cap( 'view_audit_logs' );
		$role->add_cap( 'export_audit_logs' );
	}

	/**
	 * Add capabilities for Manager role.
	 *
	 * @param WP_Role $role Manager role object.
	 * @return void
	 */
	private function add_manager_capabilities( $role ) {
		// Dashboard
		$role->add_cap( 'view_dashboard' );
		$role->add_cap( 'view_reports' );

		// Employee Management
		$role->add_cap( 'view_employee' );
		$role->add_cap( 'edit_employee' );

		// Project Management
		$role->add_cap( 'view_project' );
		$role->add_cap( 'create_project' );
		$role->add_cap( 'edit_project' );
		$role->add_cap( 'assign_project' );

		// Task Management
		$role->add_cap( 'view_task' );
		$role->add_cap( 'create_task' );
		$role->add_cap( 'edit_task' );
		$role->add_cap( 'assign_task' );

		// Leave Management
		$role->add_cap( 'view_leave' );
		$role->add_cap( 'approve_leave' );
		$role->add_cap( 'view_leave_report' );

		// Attendance
		$role->add_cap( 'view_attendance' );

		// Team Members
		$role->add_cap( 'view_team_members' );
		$role->add_cap( 'manage_team' );

		// Feedback
		$role->add_cap( 'create_feedback' );
		$role->add_cap( 'view_feedback' );
		$role->add_cap( 'manage_feedback' );

		// Performance
		$role->add_cap( 'view_performance' );
	}

	/**
	 * Add capabilities for Employee role.
	 *
	 * @param WP_Role $role Employee role object.
	 * @return void
	 */
	private function add_employee_capabilities( $role ) {
		// Dashboard
		$role->add_cap( 'view_dashboard' );

		// Profile
		$role->add_cap( 'edit_own_profile' );
		$role->add_cap( 'view_own_profile' );

		// Task Management
		$role->add_cap( 'view_task' );
		$role->add_cap( 'update_task_status' );

		// Leave Management
		$role->add_cap( 'create_leave_request' );
		$role->add_cap( 'view_own_leave' );

		// Attendance
		$role->add_cap( 'view_own_attendance' );
		$role->add_cap( 'check_in' );
		$role->add_cap( 'check_out' );

		// Payroll
		$role->add_cap( 'view_own_payroll' );

		// Projects
		$role->add_cap( 'view_project' );

		// Feedback
		$role->add_cap( 'view_own_feedback' );
		$role->add_cap( 'create_feedback' );

		// Performance
		$role->add_cap( 'view_own_performance' );
	}

	/**
	 * Get all custom roles.
	 *
	 * @return array Array of custom roles.
	 */
	public static function get_custom_roles() {
		return array(
			'tom_ceo'      => __( 'CEO', 'travelism-office-management' ),
			'tom_manager'  => __( 'Manager', 'travelism-office-management' ),
			'tom_employee' => __( 'Employee', 'travelism-office-management' ),
		);
	}

	/**
	 * Check if user has a specific capability.
	 *
	 * @param int    $user_id User ID.
	 * @param string $capability Capability to check.
	 * @return bool True if user has capability, false otherwise.
	 */
	public static function user_has_capability( $user_id, $capability ) {
		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return false;
		}

		return user_can( $user, $capability );
	}

	/**
	 * Check if user has a specific role.
	 *
	 * @param int    $user_id User ID.
	 * @param string $role Role to check.
	 * @return bool True if user has role, false otherwise.
	 */
	public static function user_has_role( $user_id, $role ) {
		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return false;
		}

		return in_array( $role, $user->roles, true );
	}

	/**
	 * Get user's role.
	 *
	 * @param int $user_id User ID.
	 * @return string|null User's custom role or null.
	 */
	public static function get_user_role( $user_id ) {
		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return null;
		}

		$custom_roles = self::get_custom_roles();

		foreach ( $user->roles as $role ) {
			if ( array_key_exists( $role, $custom_roles ) ) {
				return $role;
			}
		}

		return null;
	}

	/**
	 * Get all users by role.
	 *
	 * @param string $role Role to filter by.
	 * @return array Array of users with the specified role.
	 */
	public static function get_users_by_role( $role ) {
		return get_users(
			array(
				'role' => $role,
			)
		);
	}
}

// Initialize the capabilities manager.
TOM_Capabilities::get_instance();
