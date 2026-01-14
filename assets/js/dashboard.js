/**
 * Dashboard JavaScript
 * Dashboard functionality for Travelism Office Management
 */

(function($) {
  'use strict';

  window.TravelismDashboard = {
    init: function() {
      this.loadStatistics();
      this.loadRecentActivities();
      this.setupRefresh();
    },

    // Load dashboard statistics
    loadStatistics: function() {
      $.ajax({
        url: travelismAdmin.ajaxUrl,
        type: 'POST',
        data: {
          action: 'travelism_get_dashboard_stats',
          nonce: travelismAdmin.nonce
        },
        success: function(response) {
          if (response.success) {
            TravelismDashboard.updateStatistics(response.data);
          }
        }
      });
    },

    // Update statistics on dashboard
    updateStatistics: function(stats) {
      $.each(stats, function(key, value) {
        $('[data-stat="' + key + '"]').text(value);
      });
    },

    // Load recent activities
    loadRecentActivities: function() {
      $.ajax({
        url: travelismAdmin.ajaxUrl,
        type: 'POST',
        data: {
          action: 'travelism_get_recent_activities',
          nonce: travelismAdmin.nonce,
          limit: 10
        },
        success: function(response) {
          if (response.success) {
            TravelismDashboard.displayActivities(response.data);
          }
        }
      });
    },

    // Display activities
    displayActivities: function(activities) {
      var $container = $('.travelism-activity-feed');
      if (!$container.length) return;

      $container.empty();

      if (!activities || activities.length === 0) {
        $container.html('<p>No recent activities</p>');
        return;
      }

      $.each(activities, function(i, activity) {
        var $item = $('<div>').addClass('travelism-activity-item');
        
        var icon = TravelismDashboard.getActivityIcon(activity.action);
        var $icon = $('<div>').addClass('travelism-activity-icon ' + activity.action).html(icon);
        
        var $content = $('<div>').addClass('travelism-activity-content');
        var $title = $('<div>').addClass('travelism-activity-title').text(activity.action_details);
        var $meta = $('<div>').addClass('travelism-activity-meta').text(activity.date_created);
        
        $content.append($title, $meta);
        $item.append($icon, $content);
        $container.append($item);
      });
    },

    // Get activity icon
    getActivityIcon: function(action) {
      var icons = {
        'create': '➕',
        'update': '✏️',
        'delete': '🗑️',
        'default': '📋'
      };
      return icons[action] || icons.default;
    },

    // Setup auto-refresh
    setupRefresh: function() {
      // Refresh every 5 minutes
      setInterval(function() {
        TravelismDashboard.loadStatistics();
        TravelismDashboard.loadRecentActivities();
      }, 300000);
    },

    // Refresh dashboard
    refresh: function() {
      this.loadStatistics();
      this.loadRecentActivities();
      TravelismAdmin.showNotification('Dashboard refreshed', 'success');
    }
  };

  // Initialize on document ready
  $(document).ready(function() {
    if ($('.travelism-dashboard').length) {
      TravelismDashboard.init();
    }
  });

})(jQuery);
