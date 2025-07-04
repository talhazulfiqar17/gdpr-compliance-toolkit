<?php
namespace GDPRComplianceToolkit;

class Settings {
    private $options;

    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings() {
        register_setting(
            'gdpr_compliance_group',
            'gdpr_compliance_settings',
            [$this, 'sanitize_settings']
        );

        // General Settings Section
        add_settings_section(
            'gdpr_general_section',
            __('General Settings', 'gdpr-compliance-toolkit'),
            [$this, 'general_section_callback'],
            'gdpr-compliance'
        );

        add_settings_field(
            'cookie_consent_enabled',
            __('Enable Cookie Consent', 'gdpr-compliance-toolkit'),
            [$this, 'checkbox_callback'],
            'gdpr-compliance',
            'gdpr_general_section',
            [
                'id' => 'cookie_consent_enabled',
                'label' => __('Display cookie consent banner', 'gdpr-compliance-toolkit')
            ]
        );

        // Privacy Policy Section
        add_settings_section(
            'gdpr_policy_section',
            __('Privacy Policy', 'gdpr-compliance-toolkit'),
            [$this, 'policy_section_callback'],
            'gdpr-compliance'
        );

        add_settings_field(
            'privacy_policy_page',
            __('Privacy Policy Page', 'gdpr-compliance-toolkit'),
            [$this, 'page_select_callback'],
            'gdpr-compliance',
            'gdpr_policy_section',
            [
                'id' => 'privacy_policy_page',
                'label' => __('Select your privacy policy page', 'gdpr-compliance-toolkit')
            ]
        );

        // Data Retention Section
        add_settings_section(
            'gdpr_retention_section',
            __('Data Retention', 'gdpr-compliance-toolkit'),
            [$this, 'retention_section_callback'],
            'gdpr-compliance'
        );

        add_settings_field(
            'data_retention_days',
            __('Data Retention Period (days)', 'gdpr-compliance-toolkit'),
            [$this, 'number_callback'],
            'gdpr-compliance',
            'gdpr_retention_section',
            [
                'id' => 'data_retention_days',
                'min' => 1,
                'max' => 365,
                'step' => 1
            ]
        );
    }

    public function sanitize_settings($input) {
        $sanitized = [];
        
        // General settings
        $sanitized['cookie_consent_enabled'] = isset($input['cookie_consent_enabled']) ? true : false;
        $sanitized['cookie_expiry_days'] = isset($input['cookie_expiry_days']) ? 
            absint($input['cookie_expiry_days']) : 365;
        
        // Privacy policy
        $sanitized['privacy_policy_page'] = isset($input['privacy_policy_page']) ? 
            absint($input['privacy_policy_page']) : 0;
        
        // Data retention
        $sanitized['data_retention_days'] = isset($input['data_retention_days']) ? 
            min(max(absint($input['data_retention_days']), 1, 365)) : 30;
        
        return $sanitized;
    }

    // Various callback methods for settings fields...
}