<?php
/**
 * Admin Dashboard View
 *
 * Main dashboard page with statistics and charts.
 *
 * @package Travelism_Office_Management
 * @subpackage Admin/Views
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get dashboard instance
$dashboard = new Travelism_Admin_Dashboard();
$stats = $dashboard->get_statistics();
$activities = $dashboard->get_recent_activities( 5 );
?>

<div class="travelism-wrap travelism-dashboard">
	<div class="travelism-header">
		<h1><?php esc_html_e( 'Dashboard', 'travelism-office-management' ); ?></h1>
		<button type="button" class="travelism-btn travelism-btn-secondary" onclick="TravelismDashboard.refresh()">
			<?php esc_html_e( 'Refresh', 'travelism-office-management' ); ?>
		</button>
	</div>

	<!-- Statistics Cards -->
	<div class="travelism-stats-grid">
		<div class="travelism-stat-card">
			<div class="travelism-stat-icon">👥</div>
			<div class="travelism-stat-value" data-stat="total_customers"><?php echo esc_html( $stats['total_customers'] ); ?></div>
			<div class="travelism-stat-label"><?php esc_html_e( 'Total Customers', 'travelism-office-management' ); ?></div>
		</div>

		<div class="travelism-stat-card success">
			<div class="travelism-stat-icon">✓</div>
			<div class="travelism-stat-value" data-stat="active_customers"><?php echo esc_html( $stats['active_customers'] ); ?></div>
			<div class="travelism-stat-label"><?php esc_html_e( 'Active Customers', 'travelism-office-management' ); ?></div>
		</div>

		<div class="travelism-stat-card info">
			<div class="travelism-stat-icon">🌍</div>
			<div class="travelism-stat-value" data-stat="total_visas"><?php echo esc_html( $stats['total_visas'] ); ?></div>
			<div class="travelism-stat-label"><?php esc_html_e( 'Total Visas', 'travelism-office-management' ); ?></div>
		</div>

		<div class="travelism-stat-card warning">
			<div class="travelism-stat-icon">⏳</div>
			<div class="travelism-stat-value" data-stat="pending_visas"><?php echo esc_html( $stats['pending_visas'] ); ?></div>
			<div class="travelism-stat-label"><?php esc_html_e( 'Pending Visas', 'travelism-office-management' ); ?></div>
		</div>

		<div class="travelism-stat-card secondary">
			<div class="travelism-stat-icon">📋</div>
			<div class="travelism-stat-value" data-stat="total_tasks"><?php echo esc_html( $stats['total_tasks'] ); ?></div>
			<div class="travelism-stat-label"><?php esc_html_e( 'Total Tasks', 'travelism-office-management' ); ?></div>
		</div>

		<div class="travelism-stat-card">
			<div class="travelism-stat-icon">💰</div>
			<div class="travelism-stat-value" data-stat="total_revenue">$<?php echo esc_html( number_format( $stats['total_revenue'], 2 ) ); ?></div>
			<div class="travelism-stat-label"><?php esc_html_e( 'Total Revenue', 'travelism-office-management' ); ?></div>
		</div>
	</div>

	<!-- Quick Actions -->
	<div class="travelism-quick-actions">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=travelism-office-management-customers' ) ); ?>" class="travelism-quick-action">
			<div class="travelism-quick-action-icon">➕</div>
			<div class="travelism-quick-action-label"><?php esc_html_e( 'Add Customer', 'travelism-office-management' ); ?></div>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=travelism-office-management-visas' ) ); ?>" class="travelism-quick-action">
			<div class="travelism-quick-action-icon">🌍</div>
			<div class="travelism-quick-action-label"><?php esc_html_e( 'New Visa', 'travelism-office-management' ); ?></div>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=travelism-office-management-tasks' ) ); ?>" class="travelism-quick-action">
			<div class="travelism-quick-action-icon">📝</div>
			<div class="travelism-quick-action-label"><?php esc_html_e( 'Create Task', 'travelism-office-management' ); ?></div>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=travelism-office-management-analytics' ) ); ?>" class="travelism-quick-action">
			<div class="travelism-quick-action-icon">📊</div>
			<div class="travelism-quick-action-label"><?php esc_html_e( 'View Reports', 'travelism-office-management' ); ?></div>
		</a>
	</div>

	<!-- Charts -->
	<div class="travelism-charts-grid">
		<div class="travelism-chart-card">
			<div class="travelism-chart-header"><?php esc_html_e( 'Visa Status Distribution', 'travelism-office-management' ); ?></div>
			<div class="travelism-chart-container">
				<canvas id="visa-status-chart"></canvas>
			</div>
		</div>

		<div class="travelism-chart-card">
			<div class="travelism-chart-header"><?php esc_html_e( 'Task Status', 'travelism-office-management' ); ?></div>
			<div class="travelism-chart-container">
				<canvas id="task-status-chart"></canvas>
			</div>
		</div>

		<div class="travelism-chart-card">
			<div class="travelism-chart-header"><?php esc_html_e( 'Monthly Revenue', 'travelism-office-management' ); ?></div>
			<div class="travelism-chart-container">
				<canvas id="monthly-revenue-chart"></canvas>
			</div>
		</div>

		<div class="travelism-chart-card">
			<div class="travelism-chart-header"><?php esc_html_e( 'Customer Types', 'travelism-office-management' ); ?></div>
			<div class="travelism-chart-container">
				<canvas id="customer-type-chart"></canvas>
			</div>
		</div>
	</div>

	<!-- Recent Activities -->
	<div class="travelism-card">
		<div class="travelism-card-header"><?php esc_html_e( 'Recent Activities', 'travelism-office-management' ); ?></div>
		<div class="travelism-activity-feed">
			<?php if ( ! empty( $activities ) ) : ?>
				<?php foreach ( $activities as $activity ) : ?>
					<div class="travelism-activity-item">
						<div class="travelism-activity-icon <?php echo esc_attr( strtolower( $activity['action'] ) ); ?>">
							<?php echo esc_html( $activity['action'] === 'create' ? '➕' : ( $activity['action'] === 'update' ? '✏️' : '🗑️' ) ); ?>
						</div>
						<div class="travelism-activity-content">
							<div class="travelism-activity-title"><?php echo esc_html( $activity['action_details'] ); ?></div>
							<div class="travelism-activity-meta">
								<?php echo esc_html( Travelism_Formatter::format_time_ago( $activity['date_created'] ) ); ?>
								<?php if ( ! empty( $activity['user_email'] ) ) : ?>
									· <?php echo esc_html( $activity['user_email'] ); ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No recent activities', 'travelism-office-management' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>

