<form class="gdpr-data-request-form">
    <h3><?php _e('Submit Data Request', 'gdpr-compliance-toolkit'); ?></h3>
    
    <div class="form-group">
        <label for="gdpr-request-type"><?php _e('Request Type', 'gdpr-compliance-toolkit'); ?></label>
        <select id="gdpr-request-type" name="request_type" required>
            <option value="export"><?php _e('Export Personal Data', 'gdpr-compliance-toolkit'); ?></option>
            <option value="delete"><?php _e('Delete Personal Data', 'gdpr-compliance-toolkit'); ?></option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="gdpr-email"><?php _e('Email Address', 'gdpr-compliance-toolkit'); ?></label>
        <input type="email" id="gdpr-email" name="email" required>
    </div>
    
    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('gdpr_request_nonce'); ?>">
    <button type="submit"><?php _e('Submit Request', 'gdpr-compliance-toolkit'); ?></button>
    
    <div class="gdpr-response-message"></div>
</form>