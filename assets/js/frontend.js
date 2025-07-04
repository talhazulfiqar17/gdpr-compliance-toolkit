jQuery(document).ready(function($) {
    // Cookie Consent
    if ($('.gdpr-cookie-consent').length && !document.cookie.match(/gdpr_consent_/)) {
        $('.gdpr-cookie-consent').fadeIn();
        
        $('.gdpr-accept').click(function() {
            setConsent('necessary', true);
            $('.gdpr-cookie-consent').fadeOut();
        });
        
        $('.gdpr-decline').click(function() {
            setConsent('necessary', false);
            $('.gdpr-cookie-consent').fadeOut();
        });
    }
    
    // Data Request Form
    $('.gdpr-data-request-form').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var messageContainer = form.find('.gdpr-response-message');
        
        messageContainer.html('<p class="loading"><?php _e('Processing...', 'gdpr-compliance-toolkit'); ?></p>');
        
        $.post(gdprCompliance.ajaxurl, {
            action: 'gdpr_submit_request',
            request_type: form.find('[name="request_type"]').val(),
            email: form.find('[name="email"]').val(),
            nonce: form.find('[name="nonce"]').val()
        }, function(response) {
            if (response.success) {
                messageContainer.html('<p class="success">' + response.data.message + '</p>');
                form[0].reset();
            } else {
                messageContainer.html('<p class="error">' + response.data.message + '</p>');
            }
        }).fail(function() {
            messageContainer.html('<p class="error"><?php _e('An error occurred. Please try again.', 'gdpr-compliance-toolkit'); ?></p>');
        });
    });
    
    function setConsent(type, value) {
        $.post(gdprCompliance.ajaxurl, {
            action: 'gdpr_set_consent',
            consent_type: type,
            consent_value: value,
            nonce: gdprCompliance.nonce
        });
    }
});
