jQuery(document).ready(function($) {
    // Tab functionality
    $('.gdpr-tab').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs
        $('.gdpr-tab').removeClass('active');
        $('.gdpr-tab-content').removeClass('active');
        
        // Add active class to clicked tab
        $(this).addClass('active');
        var tabId = $(this).data('tab');
        $('#' + tabId).addClass('active');
    });
    
    // Process data request action
    $('.gdpr-process-request').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var requestId = button.data('request-id');
        var action = button.data('action');
        
        button.prop('disabled', true).text(button.data('processing-text'));
        
        $.post(ajaxurl, {
            action: 'gdpr_admin_process_request',
            request_id: requestId,
            process_action: action,
            security: gdprAdmin.nonce
        }, function(response) {
            if (response.success) {
                // Update UI
                button.closest('tr').fadeOut(300, function() {
                    $(this).remove();
                });
                
                // Show success message
                $('.gdpr-notices').append(
                    '<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>'
                );
            } else {
                // Show error message
                $('.gdpr-notices').append(
                    '<div class="notice notice-error is-dismissible"><p>' + response.data.message + '</p></div>'
                );
                
                // Reset button
                button.prop('disabled', false).text(button.data('original-text'));
            }
            
            // Dismiss notices after 5 seconds
            setTimeout(function() {
                $('.notice').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }).fail(function() {
            // Show error message
            $('.gdpr-notices').append(
                '<div class="notice notice-error is-dismissible"><p>An error occurred while processing the request.</p></div>'
            );
            
            // Reset button
            button.prop('disabled', false).text(button.data('original-text'));
        });
    });
    
    // Settings page enhancements
    if ($('body.toplevel_page_gdpr-compliance').length) {
        // Enable/disable fields based on checkbox state
        $('.gdpr-toggle-field').each(function() {
            var checkbox = $(this);
            var target = checkbox.data('toggle-target');
            
            function toggleField() {
                if (checkbox.is(':checked')) {
                    $('#' + target).prop('disabled', false).closest('tr').show();
                } else {
                    $('#' + target).prop('disabled', true).closest('tr').hide();
                }
            }
            
            // Initial state
            toggleField();
            
            // Change event
            checkbox.on('change', toggleField);
        });
        
        // Confirm before resetting settings
        $('.gdpr-reset-settings').on('click', function(e) {
            if (!confirm('Are you sure you want to reset all settings to their default values? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    }
    
    // Dismissible notices
    $(document).on('click', '.notice.is-dismissible .notice-dismiss', function() {
        $(this).closest('.notice').remove();
    });
});
