/**
 * Admin Main JavaScript
 * Core admin functionality for Travelism Office Management
 */

(function($) {
  'use strict';

  // Global Travelism Admin object
  window.TravelismAdmin = {
    init: function() {
      this.setupAjax();
      this.setupDeleteHandlers();
      this.setupFormValidation();
      this.setupNotifications();
      this.setupSearch();
    },

    // Setup AJAX defaults
    setupAjax: function() {
      $.ajaxSetup({
        beforeSend: function(xhr) {
          xhr.setRequestHeader('X-WP-Nonce', travelismAdmin.nonce);
        }
      });
    },

    // Setup delete confirmation handlers
    setupDeleteHandlers: function() {
      $(document).on('click', '.travelism-delete', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
          return false;
        }

        var $btn = $(this);
        var itemId = $btn.data('id');
        var itemType = $btn.data('type');
        
        $btn.prop('disabled', true).html('<span class="travelism-loading"></span>');

        $.ajax({
          url: travelismAdmin.ajaxUrl,
          type: 'POST',
          data: {
            action: 'travelism_delete_' + itemType,
            nonce: travelismAdmin.nonce,
            id: itemId
          },
          success: function(response) {
            if (response.success) {
              $btn.closest('tr').fadeOut(300, function() {
                $(this).remove();
              });
              TravelismAdmin.showNotification(response.data.message || 'Item deleted successfully', 'success');
            } else {
              TravelismAdmin.showNotification(response.data.message || 'Failed to delete item', 'error');
              $btn.prop('disabled', false).html('Delete');
            }
          },
          error: function() {
            TravelismAdmin.showNotification('An error occurred. Please try again.', 'error');
            $btn.prop('disabled', false).html('Delete');
          }
        });
      });
    },

    // Setup form validation
    setupFormValidation: function() {
      $(document).on('submit', '.travelism-form', function(e) {
        var $form = $(this);
        var isValid = true;

        // Clear previous errors
        $form.find('.travelism-error').remove();
        $form.find('.error').removeClass('error');

        // Check required fields
        $form.find('[required]').each(function() {
          var $field = $(this);
          if (!$field.val() || $field.val().trim() === '') {
            isValid = false;
            $field.addClass('error');
            $field.after('<span class="travelism-error">This field is required</span>');
          }
        });

        // Email validation
        $form.find('input[type="email"]').each(function() {
          var $field = $(this);
          var email = $field.val();
          if (email && !TravelismAdmin.validateEmail(email)) {
            isValid = false;
            $field.addClass('error');
            $field.after('<span class="travelism-error">Please enter a valid email address</span>');
          }
        });

        if (!isValid) {
          e.preventDefault();
          TravelismAdmin.showNotification('Please fix the errors in the form', 'error');
          return false;
        }
      });
    },

    // Validate email
    validateEmail: function(email) {
      var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    },

    // Show notification
    showNotification: function(message, type) {
      type = type || 'info';
      
      var $notification = $('<div>')
        .addClass('travelism-notification travelism-notification-' + type)
        .html(message)
        .hide();

      $('body').append($notification);
      $notification.fadeIn(300);

      setTimeout(function() {
        $notification.fadeOut(300, function() {
          $(this).remove();
        });
      }, 5000);
    },

    // Setup notifications
    setupNotifications: function() {
      // Add notification styles if not already present
      if (!$('#travelism-notification-styles').length) {
        var styles = `
          <style id="travelism-notification-styles">
            .travelism-notification {
              position: fixed;
              top: 32px;
              right: 20px;
              padding: 15px 20px;
              border-radius: 6px;
              box-shadow: 0 4px 12px rgba(0,0,0,0.15);
              z-index: 99999;
              max-width: 400px;
              animation: slideInRight 0.3s ease;
            }
            .travelism-notification-success {
              background: #10b981;
              color: white;
            }
            .travelism-notification-error {
              background: #ef4444;
              color: white;
            }
            .travelism-notification-warning {
              background: #f59e0b;
              color: white;
            }
            .travelism-notification-info {
              background: #3b82f6;
              color: white;
            }
            @keyframes slideInRight {
              from {
                transform: translateX(100%);
                opacity: 0;
              }
              to {
                transform: translateX(0);
                opacity: 1;
              }
            }
            .travelism-error {
              color: #ef4444;
              font-size: 12px;
              display: block;
              margin-top: 5px;
            }
            .error {
              border-color: #ef4444 !important;
            }
          </style>
        `;
        $('head').append(styles);
      }
    },

    // Setup search functionality
    setupSearch: function() {
      var searchTimeout;
      $(document).on('input', '.travelism-search', function() {
        clearTimeout(searchTimeout);
        var $input = $(this);
        var searchTerm = $input.val();
        var searchType = $input.data('search-type');

        searchTimeout = setTimeout(function() {
          TravelismAdmin.performSearch(searchTerm, searchType);
        }, 500);
      });
    },

    // Perform search
    performSearch: function(term, type) {
      // Implementation would depend on the specific search type
      console.log('Searching for:', term, 'Type:', type);
    },

    // AJAX helper
    ajax: function(action, data, callback) {
      data = data || {};
      data.action = action;
      data.nonce = travelismAdmin.nonce;

      $.ajax({
        url: travelismAdmin.ajaxUrl,
        type: 'POST',
        data: data,
        success: function(response) {
          if (typeof callback === 'function') {
            callback(response);
          }
        },
        error: function(xhr, status, error) {
          TravelismAdmin.showNotification('An error occurred: ' + error, 'error');
        }
      });
    }
  };

  // Initialize on document ready
  $(document).ready(function() {
    TravelismAdmin.init();
  });

})(jQuery);
