/**
 * Dashboard Charts JavaScript
 * Chart rendering for Travelism dashboard
 */

(function($) {
  'use strict';

  window.TravelismDashboardCharts = {
    init: function() {
      this.renderVisaStatusChart();
      this.renderTaskStatusChart();
      this.renderMonthlyRevenueChart();
      this.renderCustomerTypeChart();
    },

    // Render visa status chart
    renderVisaStatusChart: function() {
      var data = {
        labels: ['Pending', 'Processing', 'Approved', 'Rejected'],
        datasets: [{
          label: 'Visa Applications',
          data: [12, 19, 15, 5],
          backgroundColor: [
            TravelismCharts.colors.warning,
            TravelismCharts.colors.info,
            TravelismCharts.colors.success,
            TravelismCharts.colors.danger
          ]
        }]
      };

      TravelismCharts.createDoughnutChart('visa-status-chart', data);
    },

    // Render task status chart
    renderTaskStatusChart: function() {
      var data = {
        labels: ['Pending', 'In Progress', 'Completed', 'Overdue'],
        datasets: [{
          label: 'Tasks',
          data: [8, 12, 25, 3],
          backgroundColor: [
            TravelismCharts.colors.gray,
            TravelismCharts.colors.info,
            TravelismCharts.colors.success,
            TravelismCharts.colors.danger
          ]
        }]
      };

      TravelismCharts.createPieChart('task-status-chart', data);
    },

    // Render monthly revenue chart
    renderMonthlyRevenueChart: function() {
      var data = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Revenue ($)',
          data: [12000, 19000, 15000, 22000, 18000, 25000],
          borderColor: TravelismCharts.colors.primary,
          backgroundColor: TravelismCharts.hexToRgba(TravelismCharts.colors.primary, 0.1),
          tension: 0.4,
          fill: true
        }]
      };

      TravelismCharts.createLineChart('monthly-revenue-chart', data);
    },

    // Render customer type chart
    renderCustomerTypeChart: function() {
      var data = {
        labels: ['Individual', 'Corporate', 'VIP', 'Travel Agent'],
        datasets: [{
          label: 'Customers',
          data: [45, 25, 15, 10],
          backgroundColor: [
            TravelismCharts.colors.info,
            TravelismCharts.colors.primary,
            TravelismCharts.colors.warning,
            TravelismCharts.colors.success
          ]
        }]
      };

      TravelismCharts.createBarChart('customer-type-chart', data);
    }
  };

  // Initialize charts when dashboard is ready
  $(document).ready(function() {
    if ($('.travelism-dashboard').length && typeof Chart !== 'undefined') {
      // Wait for Chart.js to be fully loaded
      setTimeout(function() {
        TravelismDashboardCharts.init();
      }, 100);
    }
  });

})(jQuery);
