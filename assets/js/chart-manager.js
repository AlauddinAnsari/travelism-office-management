/**
 * Chart Manager JavaScript
 * Chart creation and management using Chart.js
 */

(function($) {
  'use strict';

  window.TravelismCharts = {
    charts: {},
    colors: {
      primary: '#dc2626',
      primaryLight: '#fca5a5',
      success: '#10b981',
      warning: '#f59e0b',
      danger: '#ef4444',
      info: '#3b82f6',
      gray: '#6b7280'
    },

    init: function() {
      this.setupDefaults();
    },

    // Setup Chart.js defaults
    setupDefaults: function() {
      if (typeof Chart === 'undefined') {
        console.warn('Chart.js not loaded');
        return;
      }

      Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
      Chart.defaults.color = this.colors.gray;
    },

    // Create line chart
    createLineChart: function(canvasId, data, options) {
      var ctx = document.getElementById(canvasId);
      if (!ctx) return null;

      var defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      };

      options = $.extend(true, defaultOptions, options || {});

      this.charts[canvasId] = new Chart(ctx, {
        type: 'line',
        data: data,
        options: options
      });

      return this.charts[canvasId];
    },

    // Create bar chart
    createBarChart: function(canvasId, data, options) {
      var ctx = document.getElementById(canvasId);
      if (!ctx) return null;

      var defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      };

      options = $.extend(true, defaultOptions, options || {});

      this.charts[canvasId] = new Chart(ctx, {
        type: 'bar',
        data: data,
        options: options
      });

      return this.charts[canvasId];
    },

    // Create pie chart
    createPieChart: function(canvasId, data, options) {
      var ctx = document.getElementById(canvasId);
      if (!ctx) return null;

      var defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      };

      options = $.extend(true, defaultOptions, options || {});

      this.charts[canvasId] = new Chart(ctx, {
        type: 'pie',
        data: data,
        options: options
      });

      return this.charts[canvasId];
    },

    // Create doughnut chart
    createDoughnutChart: function(canvasId, data, options) {
      var ctx = document.getElementById(canvasId);
      if (!ctx) return null;

      var defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      };

      options = $.extend(true, defaultOptions, options || {});

      this.charts[canvasId] = new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: options
      });

      return this.charts[canvasId];
    },

    // Create radar chart
    createRadarChart: function(canvasId, data, options) {
      var ctx = document.getElementById(canvasId);
      if (!ctx) return null;

      var defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      };

      options = $.extend(true, defaultOptions, options || {});

      this.charts[canvasId] = new Chart(ctx, {
        type: 'radar',
        data: data,
        options: options
      });

      return this.charts[canvasId];
    },

    // Update chart data
    updateChart: function(canvasId, newData) {
      if (this.charts[canvasId]) {
        this.charts[canvasId].data = newData;
        this.charts[canvasId].update();
      }
    },

    // Destroy chart
    destroyChart: function(canvasId) {
      if (this.charts[canvasId]) {
        this.charts[canvasId].destroy();
        delete this.charts[canvasId];
      }
    },

    // Get chart instance
    getChart: function(canvasId) {
      return this.charts[canvasId] || null;
    },

    // Generate color palette
    generateColors: function(count, opacity) {
      opacity = opacity || 0.8;
      var colors = [
        this.colors.primary,
        this.colors.success,
        this.colors.info,
        this.colors.warning,
        this.colors.danger,
        this.colors.gray
      ];

      var result = [];
      for (var i = 0; i < count; i++) {
        var color = colors[i % colors.length];
        result.push(this.hexToRgba(color, opacity));
      }

      return result;
    },

    // Convert hex to rgba
    hexToRgba: function(hex, alpha) {
      var r = parseInt(hex.slice(1, 3), 16);
      var g = parseInt(hex.slice(3, 5), 16);
      var b = parseInt(hex.slice(5, 7), 16);

      return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    }
  };

  // Initialize on document ready
  $(document).ready(function() {
    TravelismCharts.init();
  });

})(jQuery);
