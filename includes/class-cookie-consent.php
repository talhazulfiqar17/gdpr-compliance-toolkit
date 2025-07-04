<?php
namespace GDPRComplianceToolkit;

class CookieConsent {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_cookie_consent']);
        add_action('wp_ajax_gdpr_set_consent', [$this, 'handle_consent']);
        add_action('wp_ajax_nopriv_gdpr_set_consent', [$this, 'handle_consent']);
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'gdpr-cookie-consent',
            GDPR_COMPLIANCE_URL . 'assets/css/frontend.css',
            [],
            GDPR_COMPLIANCE_VERSION
        );

        wp_enqueue_script(
            'gdpr-cookie-consent',
            GDPR_COMPLIANCE_URL . 'assets/js/frontend.js',
            ['jquery'],
            GDPR_COMPLIANCE_VERSION,
            true
        );

        wp_localize_script(
            'gdpr-cookie-consent',
            'gdprCompliance',
            [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('gdpr_consent_nonce')
            ]
        );
    }

    public function render_cookie_consent() {
        $settings = get_option('gdpr_compliance_settings');
        
        if (empty($settings['cookie_consent_enabled'])) {
            return;
        }

        include GDPR_COMPLIANCE_PATH . 'templates/cookie-consent.php';
    }

    public function handle_consent() {
        try {
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'gdpr_consent_nonce')) {
                throw new \Exception(__('Invalid nonce', 'gdpr-compliance-toolkit'));
            }

            if (!isset($_POST['consent_type'])) {
                throw new \Exception(__('Missing consent type', 'gdpr-compliance-toolkit'));
            }

            $consent_type = sanitize_text_field($_POST['consent_type']);
            $settings = get_option('gdpr_compliance_settings');
            $expiry_days = isset($settings['cookie_expiry_days']) ? (int)$settings['cookie_expiry_days'] : 365;

            setcookie(
                'gdpr_consent_' . $consent_type,
                '1',
                time() + (86400 * $expiry_days),
                COOKIEPATH,
                COOKIE_DOMAIN
            );

            wp_send_json_success([
                'message' => __('Consent saved successfully', 'gdpr-compliance-toolkit')
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }
}