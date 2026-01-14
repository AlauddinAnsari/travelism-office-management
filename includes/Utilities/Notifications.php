<?php
/**
 * Notifications Utility Class
 *
 * Provides email and in-app notifications functionality.
 *
 * @package Travelism_Office_Management
 * @subpackage Utilities
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Travelism_Notifications
 *
 * Handles notification operations for the plugin.
 *
 * @since 1.0.0
 */
class Travelism_Notifications {

	/**
	 * Send email notification
	 *
	 * @since 1.0.0
	 * @param string|array $to Email address(es).
	 * @param string       $subject Email subject.
	 * @param string       $message Email message.
	 * @param array        $args Additional arguments.
	 * @return bool True on success, false on failure.
	 */
	public static function send_email( $to, $subject, $message, $args = array() ) {
		$defaults = array(
			'from_name'  => get_bloginfo( 'name' ),
			'from_email' => get_bloginfo( 'admin_email' ),
			'headers'    => array(),
			'attachments' => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		// Set headers
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			sprintf( 'From: %s <%s>', $args['from_name'], $args['from_email'] ),
		);

		if ( ! empty( $args['headers'] ) ) {
			$headers = array_merge( $headers, $args['headers'] );
		}

		// Wrap message in email template
		$message = self::get_email_template( $message, $subject );

		// Send email
		$sent = wp_mail( $to, $subject, $message, $headers, $args['attachments'] );

		// Log email sent
		if ( $sent ) {
			Travelism_Logger::info( sprintf( 'Email sent to %s: %s', is_array( $to ) ? implode( ', ', $to ) : $to, $subject ) );
		} else {
			Travelism_Logger::error( sprintf( 'Failed to send email to %s: %s', is_array( $to ) ? implode( ', ', $to ) : $to, $subject ) );
		}

		return $sent;
	}

	/**
	 * Get email template
	 *
	 * @since 1.0.0
	 * @param string $content Email content.
	 * @param string $title Email title.
	 * @return string HTML email template.
	 */
	private static function get_email_template( $content, $title ) {
		$template = '
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>' . esc_html( $title ) . '</title>
			<style>
				body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				.header { background: #dc2626; color: white; padding: 20px; text-align: center; }
				.content { background: #f9f9f9; padding: 20px; }
				.footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
			</style>
		</head>
		<body>
			<div class="container">
				<div class="header">
					<h1>' . esc_html( get_bloginfo( 'name' ) ) . '</h1>
				</div>
				<div class="content">
					' . wp_kses_post( $content ) . '
				</div>
				<div class="footer">
					<p>&copy; ' . esc_html( gmdate( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) ) . '. All rights reserved.</p>
				</div>
			</div>
		</body>
		</html>';

		return $template;
	}

	/**
	 * Send customer welcome email
	 *
	 * @since 1.0.0
	 * @param array $customer Customer data.
	 * @return bool True on success, false on failure.
	 */
	public static function send_customer_welcome( $customer ) {
		$subject = sprintf( 'Welcome to %s', get_bloginfo( 'name' ) );
		$message = sprintf(
			'<p>Dear %s,</p>
			<p>Thank you for choosing %s. We are excited to assist you with your travel needs.</p>
			<p>Your customer account has been created successfully.</p>
			<p>If you have any questions, please don\'t hesitate to contact us.</p>
			<p>Best regards,<br>%s Team</p>',
			esc_html( $customer['first_name'] . ' ' . $customer['last_name'] ),
			esc_html( get_bloginfo( 'name' ) ),
			esc_html( get_bloginfo( 'name' ) )
		);

		return self::send_email( $customer['email'], $subject, $message );
	}

	/**
	 * Send visa status update email
	 *
	 * @since 1.0.0
	 * @param array $visa Visa data.
	 * @param array $customer Customer data.
	 * @return bool True on success, false on failure.
	 */
	public static function send_visa_status_update( $visa, $customer ) {
		$subject = sprintf( 'Visa Application Status Update - %s', $visa['destination_country'] );
		$message = sprintf(
			'<p>Dear %s,</p>
			<p>Your visa application for %s has been updated.</p>
			<p><strong>Status:</strong> %s</p>
			<p><strong>Application Date:</strong> %s</p>
			<p>For more details, please contact our office.</p>
			<p>Best regards,<br>%s Team</p>',
			esc_html( $customer['first_name'] . ' ' . $customer['last_name'] ),
			esc_html( $visa['destination_country'] ),
			esc_html( ucfirst( $visa['visa_status'] ) ),
			esc_html( Travelism_Formatter::format_date( $visa['application_date'] ) ),
			esc_html( get_bloginfo( 'name' ) )
		);

		return self::send_email( $customer['email'], $subject, $message );
	}

	/**
	 * Send task assignment email
	 *
	 * @since 1.0.0
	 * @param array $task Task data.
	 * @param array $user User data.
	 * @return bool True on success, false on failure.
	 */
	public static function send_task_assignment( $task, $user ) {
		$subject = sprintf( 'New Task Assigned: %s', $task['title'] );
		$message = sprintf(
			'<p>Hi %s,</p>
			<p>A new task has been assigned to you.</p>
			<p><strong>Task:</strong> %s</p>
			<p><strong>Priority:</strong> %s</p>
			<p><strong>Due Date:</strong> %s</p>
			<p><strong>Description:</strong></p>
			<p>%s</p>
			<p>Please log in to view more details.</p>
			<p>Best regards,<br>%s Team</p>',
			esc_html( $user->display_name ),
			esc_html( $task['title'] ),
			esc_html( ucfirst( $task['priority'] ) ),
			esc_html( Travelism_Formatter::format_date( $task['due_date'] ) ),
			wp_kses_post( $task['description'] ),
			esc_html( get_bloginfo( 'name' ) )
		);

		return self::send_email( $user->user_email, $subject, $message );
	}

	/**
	 * Send task reminder email
	 *
	 * @since 1.0.0
	 * @param array $task Task data.
	 * @param array $user User data.
	 * @return bool True on success, false on failure.
	 */
	public static function send_task_reminder( $task, $user ) {
		$subject = sprintf( 'Task Reminder: %s', $task['title'] );
		$message = sprintf(
			'<p>Hi %s,</p>
			<p>This is a reminder about your task:</p>
			<p><strong>Task:</strong> %s</p>
			<p><strong>Due Date:</strong> %s</p>
			<p><strong>Status:</strong> %s</p>
			<p>Please complete this task as soon as possible.</p>
			<p>Best regards,<br>%s Team</p>',
			esc_html( $user->display_name ),
			esc_html( $task['title'] ),
			esc_html( Travelism_Formatter::format_date( $task['due_date'] ) ),
			esc_html( ucfirst( $task['task_status'] ) ),
			esc_html( get_bloginfo( 'name' ) )
		);

		return self::send_email( $user->user_email, $subject, $message );
	}

	/**
	 * Create admin notice
	 *
	 * @since 1.0.0
	 * @param string $message Notice message.
	 * @param string $type Notice type (success, error, warning, info).
	 * @return void
	 */
	public static function admin_notice( $message, $type = 'success' ) {
		add_action( 'admin_notices', function() use ( $message, $type ) {
			$class = 'notice notice-' . $type . ' is-dismissible';
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		} );
	}

	/**
	 * Add in-app notification
	 *
	 * @since 1.0.0
	 * @param int    $user_id User ID.
	 * @param string $title Notification title.
	 * @param string $message Notification message.
	 * @param string $type Notification type.
	 * @return bool True on success, false on failure.
	 */
	public static function add_notification( $user_id, $title, $message, $type = 'info' ) {
		$notifications = get_user_meta( $user_id, 'travelism_notifications', true );
		if ( ! is_array( $notifications ) ) {
			$notifications = array();
		}

		$notifications[] = array(
			'title'   => $title,
			'message' => $message,
			'type'    => $type,
			'date'    => current_time( 'mysql' ),
			'read'    => false,
		);

		return update_user_meta( $user_id, 'travelism_notifications', $notifications );
	}

	/**
	 * Get user notifications
	 *
	 * @since 1.0.0
	 * @param int  $user_id User ID.
	 * @param bool $unread_only Get only unread notifications.
	 * @return array Notifications array.
	 */
	public static function get_notifications( $user_id, $unread_only = false ) {
		$notifications = get_user_meta( $user_id, 'travelism_notifications', true );
		if ( ! is_array( $notifications ) ) {
			return array();
		}

		if ( $unread_only ) {
			$notifications = array_filter( $notifications, function( $notification ) {
				return ! $notification['read'];
			} );
		}

		return $notifications;
	}

	/**
	 * Mark notification as read
	 *
	 * @since 1.0.0
	 * @param int $user_id User ID.
	 * @param int $notification_index Notification index.
	 * @return bool True on success, false on failure.
	 */
	public static function mark_as_read( $user_id, $notification_index ) {
		$notifications = get_user_meta( $user_id, 'travelism_notifications', true );
		if ( ! is_array( $notifications ) || ! isset( $notifications[ $notification_index ] ) ) {
			return false;
		}

		$notifications[ $notification_index ]['read'] = true;
		return update_user_meta( $user_id, 'travelism_notifications', $notifications );
	}
}
