<?php
/**
 * Plugin Name: GDPR Compliance Toolkit
 * Description: Comprehensive GDPR compliance solution for WordPress
 * Version: 1.0.0
 * Author: Mozayyan Abbas
 * License: GPL-2.0+
 * Text Domain: gdpr-compliance-toolkit
 */

defined('ABSPATH') || exit;

// Define constants
define('GDPR_COMPLIANCE_VERSION', '1.0.0');
define('GDPR_COMPLIANCE_PATH', plugin_dir_path(__FILE__));
define('GDPR_COMPLIANCE_URL', plugin_dir_url(__FILE__));
define('GDPR_COMPLIANCE_BASENAME', plugin_basename(__FILE__));

// Autoloader

require_once GDPR_COMPLIANCE_PATH . 'includes/class-admin.php';
require_once GDPR_COMPLIANCE_PATH . 'includes/class-cookie-consent.php';
require_once GDPR_COMPLIANCE_PATH . 'includes/class-data-requests.php';
require_once GDPR_COMPLIANCE_PATH . 'includes/class-privacy-policy.php';
require_once GDPR_COMPLIANCE_PATH . 'includes/class-settings.php';

/*
spl_autoload_register(function ($class) {
    $prefix = 'GDPRComplianceToolkit\\';
    $base_dir = GDPR_COMPLIANCE_PATH . 'includes/class-';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    //echo $relative_class;
    //echo $file;
    
    if (file_exists($file)) {
        require $file;
    }else{
        echo $file . '<br>';
    }
});
*/

// Initialize plugin
function gdpr_compliance_toolkit_init() {
    // Load text domain
    load_plugin_textdomain(
        'gdpr-compliance-toolkit',
        false,
        dirname(GDPR_COMPLIANCE_BASENAME) . '/languages'
    );

    // Initialize components
    new GDPRComplianceToolkit\Admin();
    new GDPRComplianceToolkit\CookieConsent();
    new GDPRComplianceToolkit\DataRequests();
    new GDPRComplianceToolkit\PrivacyPolicy();
}
add_action('plugins_loaded', 'gdpr_compliance_toolkit_init');

// Activation/Deactivation hooks
register_activation_hook(__FILE__, ['GDPRComplianceToolkit\\Admin', 'activate']);
register_deactivation_hook(__FILE__, ['GDPRComplianceToolkit\\Admin', 'deactivate']);