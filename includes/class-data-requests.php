<?php
namespace GDPRComplianceToolkit;

class DataRequests {
    public function __construct() {
        add_action('init', [$this, 'register_data_request_post_type']);
        add_action('wp_ajax_gdpr_submit_request', [$this, 'handle_data_request']);
        add_action('wp_ajax_nopriv_gdpr_submit_request', [$this, 'handle_data_request']);
        add_action('gdpr_cleanup_data_requests', [$this, 'cleanup_old_requests']);
    }

    public function register_data_request_post_type() {
        $args = [
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'gdpr-compliance',
            'label' => __('Data Requests', 'gdpr-compliance-toolkit'),
            'supports' => ['title'],
            'capabilities' => [
                'create_posts' => false,
                'edit_posts' => 'manage_options'
            ]
        ];
        register_post_type('gdpr_data_request', $args);
    }

    public function handle_data_request() {
        try {
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'gdpr_request_nonce')) {
                throw new \Exception(__('Invalid nonce', 'gdpr-compliance-toolkit'));
            }

            if (!isset($_POST['request_type']) || !isset($_POST['email'])) {
                throw new \Exception(__('Missing required fields', 'gdpr-compliance-toolkit'));
            }

            $request_type = sanitize_text_field($_POST['request_type']);
            $email = sanitize_email($_POST['email']);
            $user = get_user_by('email', $email);

            if (!$user && $request_type !== 'export') {
                throw new \Exception(__('No user found with this email', 'gdpr-compliance-toolkit'));
            }

            // Create request entry
            $request_id = wp_insert_post([
                'post_type' => 'gdpr_data_request',
                'post_title' => sprintf('%s request from %s', ucfirst($request_type), $email),
                'post_status' => 'pending',
                'meta_input' => [
                    'request_type' => $request_type,
                    'email' => $email,
                    'user_id' => $user ? $user->ID : 0,
                    'ip_address' => $this->get_client_ip(),
                    'request_date' => current_time('mysql')
                ]
            ]);

            if (is_wp_error($request_id)) {
                throw new \Exception($request_id->get_error_message());
            }

            // Handle the request based on type
            switch ($request_type) {
                case 'export':
                    $this->handle_export_request($request_id, $email, $user);
                    break;
                case 'delete':
                    $this->handle_delete_request($request_id, $email, $user);
                    break;
                default:
                    throw new \Exception(__('Invalid request type', 'gdpr-compliance-toolkit'));
            }

            wp_send_json_success([
                'message' => __('Your request has been submitted successfully', 'gdpr-compliance-toolkit')
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    private function handle_export_request($request_id, $email, $user) {
        // Generate export data
        $export_data = $this->generate_export_data($user);
        
        // Store export data temporarily
        update_post_meta($request_id, 'export_data', $export_data);
        
        // Send email with export data
        $this->send_export_email($email, $export_data);
        
        // Update request status
        wp_update_post([
            'ID' => $request_id,
            'post_status' => 'completed'
        ]);
    }

    private function handle_delete_request($request_id, $email, $user) {
        // Schedule anonymization (give time for cancellation)
        wp_schedule_single_event(
            time() + (3 * DAY_IN_SECONDS),
            'gdpr_process_delete_request',
            [$request_id]
        );
        
        // Send confirmation email
        $this->send_delete_confirmation_email($email);
    }

    public function cleanup_old_requests() {
        $settings = get_option('gdpr_compliance_settings');
        $retention_days = isset($settings['data_retention_days']) ? (int)$settings['data_retention_days'] : 30;
        
        $old_requests = get_posts([
            'post_type' => 'gdpr_data_request',
            'post_status' => 'any',
            'date_query' => [
                [
                    'column' => 'post_date',
                    'before' => $retention_days . ' days ago'
                ]
            ],
            'fields' => 'ids',
            'posts_per_page' => -1
        ]);
        
        foreach ($old_requests as $request_id) {
            wp_delete_post($request_id, true);
        }
    }

    // Helper methods...
}