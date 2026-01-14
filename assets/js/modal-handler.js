/**
 * Modal Handler JavaScript
 * Modal management for Travelism Office Management
 */

(function($) {
  'use strict';

  window.TravelismModal = {
    init: function() {
      this.setupHandlers();
    },

    // Setup modal event handlers
    setupHandlers: function() {
      // Open modal
      $(document).on('click', '[data-modal-open]', function(e) {
        e.preventDefault();
        var modalId = $(this).data('modal-open');
        TravelismModal.open(modalId);
      });

      // Close modal
      $(document).on('click', '[data-modal-close]', function(e) {
        e.preventDefault();
        var $modal = $(this).closest('.travelism-modal');
        TravelismModal.close($modal.attr('id'));
      });

      // Close on overlay click
      $(document).on('click', '.travelism-modal', function(e) {
        if ($(e.target).hasClass('travelism-modal')) {
          TravelismModal.close($(this).attr('id'));
        }
      });

      // Close on ESC key
      $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
          $('.travelism-modal.active').each(function() {
            TravelismModal.close($(this).attr('id'));
          });
        }
      });

      // Edit handlers
      $(document).on('click', '.travelism-edit', function(e) {
        e.preventDefault();
        var itemId = $(this).data('id');
        var itemType = $(this).data('type');
        TravelismModal.openEdit(itemType, itemId);
      });

      // Add new handlers
      $(document).on('click', '.travelism-add-new', function(e) {
        e.preventDefault();
        var itemType = $(this).data('type');
        TravelismModal.openNew(itemType);
      });
    },

    // Open modal
    open: function(modalId) {
      var $modal = $('#' + modalId);
      if ($modal.length) {
        $modal.addClass('active');
        $('body').css('overflow', 'hidden');
      }
    },

    // Close modal
    close: function(modalId) {
      var $modal = modalId ? $('#' + modalId) : $('.travelism-modal.active');
      $modal.removeClass('active');
      $('body').css('overflow', '');
      
      // Reset form if exists
      $modal.find('form').trigger('reset');
      $modal.find('.travelism-error').remove();
      $modal.find('.error').removeClass('error');
    },

    // Open edit modal
    openEdit: function(type, id) {
      var modalId = 'travelism-' + type + '-modal';
      var $modal = $('#' + modalId);

      if (!$modal.length) {
        this.createModal(type, id);
        return;
      }

      // Load data
      this.loadItemData(type, id, function(data) {
        TravelismModal.populateForm($modal, data);
        TravelismModal.open(modalId);
      });
    },

    // Open new item modal
    openNew: function(type) {
      var modalId = 'travelism-' + type + '-modal';
      var $modal = $('#' + modalId);

      if (!$modal.length) {
        this.createModal(type);
        return;
      }

      $modal.find('form').trigger('reset');
      $modal.find('[name="id"]').val('');
      $modal.find('.travelism-modal-title').text('Add New ' + this.capitalize(type));
      this.open(modalId);
    },

    // Load item data via AJAX
    loadItemData: function(type, id, callback) {
      $.ajax({
        url: travelismAdmin.ajaxUrl,
        type: 'POST',
        data: {
          action: 'travelism_get_' + type,
          nonce: travelismAdmin.nonce,
          id: id
        },
        success: function(response) {
          if (response.success) {
            callback(response.data);
          } else {
            TravelismAdmin.showNotification(response.data.message || 'Failed to load data', 'error');
          }
        },
        error: function() {
          TravelismAdmin.showNotification('An error occurred while loading data', 'error');
        }
      });
    },

    // Populate form with data
    populateForm: function($modal, data) {
      var $form = $modal.find('form');
      
      $.each(data, function(key, value) {
        var $field = $form.find('[name="' + key + '"]');
        if ($field.length) {
          if ($field.is(':checkbox')) {
            $field.prop('checked', value == 1 || value == 'yes' || value == true);
          } else if ($field.is(':radio')) {
            $field.filter('[value="' + value + '"]').prop('checked', true);
          } else {
            $field.val(value);
          }
        }
      });

      $modal.find('.travelism-modal-title').text('Edit ' + this.capitalize(data.type || ''));
    },

    // Create modal dynamically
    createModal: function(type, id) {
      var modalId = 'travelism-' + type + '-modal';
      var title = id ? 'Edit ' : 'Add New ';
      title += this.capitalize(type);

      var modalHTML = `
        <div id="${modalId}" class="travelism-modal">
          <div class="travelism-modal-content">
            <div class="travelism-modal-header">
              <h2 class="travelism-modal-title">${title}</h2>
              <button type="button" class="travelism-modal-close" data-modal-close>&times;</button>
            </div>
            <div class="travelism-modal-body">
              <div class="travelism-loading-container">
                <span class="travelism-loading"></span>
                <p>Loading form...</p>
              </div>
            </div>
          </div>
        </div>
      `;

      $('body').append(modalHTML);

      // Load form content via AJAX
      this.loadForm(type, id);
    },

    // Load form content
    loadForm: function(type, id) {
      var modalId = 'travelism-' + type + '-modal';
      var $modal = $('#' + modalId);

      $.ajax({
        url: travelismAdmin.ajaxUrl,
        type: 'POST',
        data: {
          action: 'travelism_get_form',
          nonce: travelismAdmin.nonce,
          type: type,
          id: id || ''
        },
        success: function(response) {
          if (response.success) {
            $modal.find('.travelism-modal-body').html(response.data.form);
            if (id) {
              TravelismModal.loadItemData(type, id, function(data) {
                TravelismModal.populateForm($modal, data);
              });
            }
            TravelismModal.open(modalId);
          } else {
            $modal.remove();
            TravelismAdmin.showNotification('Failed to load form', 'error');
          }
        },
        error: function() {
          $modal.remove();
          TravelismAdmin.showNotification('An error occurred', 'error');
        }
      });
    },

    // Capitalize first letter
    capitalize: function(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    }
  };

  // Initialize on document ready
  $(document).ready(function() {
    TravelismModal.init();
  });

})(jQuery);
