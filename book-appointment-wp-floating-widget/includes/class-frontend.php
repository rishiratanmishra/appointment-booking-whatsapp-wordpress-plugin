<?php
if (!defined('ABSPATH')) {
    exit;
}

class BAW_Frontend {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_widget']);
        add_action('wp_ajax_baw_submit_appointment', [$this, 'ajax_submit']);
        add_action('wp_ajax_nopriv_baw_submit_appointment', [$this, 'ajax_submit']);
    }

    public function enqueue_assets() {
        $settings = wp_parse_args(get_option('baw_settings'), baw_get_default_settings());
        if (empty($settings['enabled'])) {
            return;
        }
        wp_enqueue_style('baw-frontend', BAW_PLUGIN_URL . 'assets/css/frontend-style.css', [], BAW_VERSION);
        wp_enqueue_script('baw-frontend', BAW_PLUGIN_URL . 'assets/js/frontend-script.js', [], BAW_VERSION, true);
        $fields = get_option('baw_form_fields');
        if (!is_array($fields) || empty($fields)) {
            $fields = baw_get_default_fields();
        }
        wp_localize_script('baw-frontend', 'BAW_OPTIONS', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('baw_frontend_nonce'),
            'settings' => $settings,
            'fields' => $fields,
            'assets' => [
                'icons' => BAW_PLUGIN_URL . 'assets/icons/',
            ],
        ]);
    }

    public function render_widget() {
        $settings = wp_parse_args(get_option('baw_settings'), baw_get_default_settings());
        if (empty($settings['enabled'])) {
            return;
        }
        $fields = get_option('baw_form_fields');
        if (!is_array($fields) || empty($fields)) {
            $fields = baw_get_default_fields();
        }
        $position_class = $settings['position'] === 'left' ? 'baw-left' : 'baw-right';
        include BAW_PLUGIN_DIR . 'templates/floating-button.php';
        include BAW_PLUGIN_DIR . 'templates/modal-form.php';
    }

    public function ajax_submit() {
        check_ajax_referer('baw_frontend_nonce', 'nonce');
        $settings = wp_parse_args(get_option('baw_settings'), baw_get_default_settings());
        $fields = get_option('baw_form_fields');
        if (!is_array($fields) || empty($fields)) {
            $fields = baw_get_default_fields();
        }

        $payload = wp_unslash($_POST);

        // Validate required fields
        $errors = [];
        $data_to_store = [];
        foreach ($fields as $field) {
            if (!($field['enabled'] ?? true)) {
                continue;
            }
            $key = $field['key'] ?? baw_sanitize_field_key($field['label'] ?? '');
            $label = $field['label'] ?? $key;
            $value = isset($payload[$key]) ? trim((string) $payload[$key]) : '';
            if (!empty($field['required']) && $value === '') {
                $errors[] = sprintf(__('%s is required', 'book-appointment-wp-floating-widget'), $label);
            }
            $data_to_store[$key] = $value;
        }

        if (!empty($errors)) {
            wp_send_json_error(['errors' => $errors], 422);
        }

        // Store in DB
        $data_to_store['created_at'] = current_time('mysql');
        $data_to_store['contacted'] = 0;
        $insert_id = BAW_Database::insert_submission($data_to_store);

        // Build WhatsApp URL
        $message_text = BAW_Whatsapp::build_message_from_template($settings['message_template'] ?? '', $data_to_store);
        $full_number = '';
        $country = preg_replace('/[^0-9]/', '', (string) ($settings['country_code'] ?? ''));
        $phone = preg_replace('/[^0-9]/', '', (string) ($settings['whatsapp_phone'] ?? ''));
        if ($country || $phone) {
            $full_number = $country . $phone;
        } else {
            // Fallback to legacy combined number
            $full_number = preg_replace('/[^0-9]/', '', (string) ($settings['whatsapp_number'] ?? ''));
        }
        $wa_url = BAW_Whatsapp::build_wa_url($full_number, $message_text);

        // Send Emails
        $this->send_emails($settings, $data_to_store, $insert_id);

        wp_send_json_success([
            'wa_url' => $wa_url,
            'message' => __('Appointment submitted successfully', 'book-appointment-wp-floating-widget'),
        ]);
    }

    private function send_emails($settings, $data, $insert_id) {
        $emails = [];
        $admin_email = get_option('admin_email');
        if ($admin_email) {
            $emails[] = $admin_email;
        }
        if (!empty($settings['extra_emails'])) {
            $extra = array_map('trim', explode(',', $settings['extra_emails']));
            foreach ($extra as $em) {
                if (is_email($em)) {
                    $emails[] = $em;
                }
            }
        }
        $emails = array_unique($emails);
        if (empty($emails)) {
            return;
        }
        $subject = sprintf(__('New Appointment #%d', 'book-appointment-wp-floating-widget'), $insert_id);
        $lines = [__('You have a new appointment submission:', 'book-appointment-wp-floating-widget')];
        foreach ($data as $k => $v) {
            $lines[] = ucwords(str_replace('_', ' ', $k)) . ': ' . $v;
        }
        $body = implode("\n", $lines);
        wp_mail($emails, $subject, $body);
    }
}


