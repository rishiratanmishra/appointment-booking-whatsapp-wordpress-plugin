<?php
/**
 * Plugin Name: Book Appointment WP Floating Widget
 * Description: Adds a floating appointment button with a customizable modal form. Sends submissions to WhatsApp, stores them in DB, and emails admins.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: book-appointment-wp-floating-widget
 */

if (!defined('ABSPATH')) {
    exit;
}

define('BAW_PLUGIN_FILE', __FILE__);
define('BAW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BAW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BAW_VERSION', '1.0.0');

// Default options and fields
function baw_get_default_settings() {
    return [
        'enabled' => 1,
        'position' => 'right', // 'left' or 'right'
        'icon' => 'whatsapp',
        'floating_text_type' => 'static', // 'static' or 'animated'
        'static_text' => __('Book Appointment', 'book-appointment-wp-floating-widget'),
        'animated_messages' => [
            __('Book Appointment', 'book-appointment-wp-floating-widget'),
            __('Visit with your pet', 'book-appointment-wp-floating-widget'),
            __('We take care of him', 'book-appointment-wp-floating-widget'),
        ],
        'animation_speed_ms' => 2500,
        // Phone settings
        'country_code' => '', // digits only, e.g., 1, 44, 91
        'whatsapp_phone' => '', // local/national number without country code
        // Legacy combined number kept for backward compatibility
        'whatsapp_number' => '',
        'message_template' => "New Appointment:%0AName: {name}%0AMobile: {mobile}%0AVisit Time: {visit_time}%0ADetails: {description}",
        'extra_emails' => '', // comma-separated
    ];
}

function baw_get_default_fields() {
    return [
        [
            'key' => 'name',
            'label' => __('Name', 'book-appointment-wp-floating-widget'),
            'type' => 'text',
            'placeholder' => __('Your name', 'book-appointment-wp-floating-widget'),
            'required' => true,
            'enabled' => true,
        ],
        [
            'key' => 'mobile',
            'label' => __('Mobile Number', 'book-appointment-wp-floating-widget'),
            'type' => 'tel',
            'placeholder' => __('e.g., +91 884086....', 'book-appointment-wp-floating-widget'),
            'required' => true,
            'enabled' => true,
        ],
        [
            'key' => 'visit_time',
            'label' => __('Timing of Visit', 'book-appointment-wp-floating-widget'),
            'type' => 'datetime-local',
            'placeholder' => '',
            'required' => true,
            'enabled' => true,
        ],
        [
            'key' => 'description',
            'label' => __('Description', 'book-appointment-wp-floating-widget'),
            'type' => 'textarea',
            'placeholder' => __('Describe your needs', 'book-appointment-wp-floating-widget'),
            'required' => false,
            'enabled' => true,
        ],
    ];
}

// Load includes
require_once BAW_PLUGIN_DIR . 'includes/class-database.php';
require_once BAW_PLUGIN_DIR . 'includes/class-whatsapp.php';
require_once BAW_PLUGIN_DIR . 'includes/class-admin.php';
require_once BAW_PLUGIN_DIR . 'includes/class-frontend.php';

// Activation: set defaults and ensure DB
function baw_activate() {
    $settings = get_option('baw_settings');
    if (!is_array($settings)) {
        $settings = baw_get_default_settings();
        update_option('baw_settings', $settings);
    } else {
        $settings = wp_parse_args($settings, baw_get_default_settings());
        update_option('baw_settings', $settings);
    }

    $fields = get_option('baw_form_fields');
    if (!is_array($fields) || empty($fields)) {
        $fields = baw_get_default_fields();
        update_option('baw_form_fields', $fields);
    }

    // Ensure DB schema
    BAW_Database::create_or_update_table($fields);
}
register_activation_hook(__FILE__, 'baw_activate');

// Initialize
add_action('plugins_loaded', function () {
    load_plugin_textdomain('book-appointment-wp-floating-widget', false, dirname(plugin_basename(__FILE__)) . '/languages');
    new BAW_Admin();
    new BAW_Frontend();
});

// Utility: sanitize field key from label
function baw_sanitize_field_key($label) {
    $key = sanitize_title($label);
    $key = preg_replace('/[^a-z0-9_\-]/', '', $key);
    return $key;
}


