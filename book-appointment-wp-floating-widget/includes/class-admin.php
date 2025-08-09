<?php
if (!defined('ABSPATH')) {
    exit;
}

class BAW_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'register_menus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_baw_save_settings', [$this, 'ajax_save_settings']);
        add_action('wp_ajax_baw_mark_contacted', [$this, 'ajax_mark_contacted']);
        add_action('admin_post_baw_export_csv', [$this, 'handle_export_csv']);
    }

    public function register_menus() {
        $cap = 'manage_options';
        add_menu_page(
            __('Book Appointments', 'book-appointment-wp-floating-widget'),
            __('Book Appointments', 'book-appointment-wp-floating-widget'),
            $cap,
            'baw_main',
            [$this, 'render_admin_page'],
            'dashicons-calendar-alt'
        );
        add_submenu_page('baw_main', __('Settings', 'book-appointment-wp-floating-widget'), __('Settings', 'book-appointment-wp-floating-widget'), $cap, 'baw_main', [$this, 'render_admin_page']);
        add_submenu_page('baw_main', __('Submissions', 'book-appointment-wp-floating-widget'), __('Submissions', 'book-appointment-wp-floating-widget'), $cap, 'baw_submissions', [$this, 'render_submissions_page']);
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'baw_') === false) {
            return;
        }
        wp_enqueue_style('baw-admin', BAW_PLUGIN_URL . 'assets/css/admin-style.css', [], BAW_VERSION);
        wp_enqueue_script('baw-admin', BAW_PLUGIN_URL . 'assets/js/admin-script.js', ['jquery'], BAW_VERSION, true);
        wp_localize_script('baw-admin', 'BAW_ADMIN', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('baw_admin_nonce'),
        ]);
    }

    public function render_admin_page() {
        $settings = wp_parse_args(get_option('baw_settings'), baw_get_default_settings());
        $fields = get_option('baw_form_fields');
        if (!is_array($fields) || empty($fields)) {
            $fields = baw_get_default_fields();
        }
        include BAW_PLUGIN_DIR . 'admin/admin-page.php';
    }

    public function render_submissions_page() {
        include BAW_PLUGIN_DIR . 'admin/submissions-page.php';
    }

    public function ajax_save_settings() {
        check_ajax_referer('baw_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'book-appointment-wp-floating-widget')], 403);
        }

        $payload = wp_unslash($_POST);

        // Settings
        $settings = wp_parse_args(get_option('baw_settings'), baw_get_default_settings());
        $settings['enabled'] = isset($payload['enabled']) ? (int) $payload['enabled'] : 0;
        $settings['position'] = in_array($payload['position'] ?? 'right', ['left', 'right'], true) ? $payload['position'] : 'right';
        $settings['icon'] = sanitize_text_field($payload['icon'] ?? 'whatsapp');
        $settings['floating_text_type'] = in_array($payload['floating_text_type'] ?? 'static', ['static', 'animated'], true) ? $payload['floating_text_type'] : 'static';
        $settings['static_text'] = sanitize_text_field($payload['static_text'] ?? '');
        $settings['animation_speed_ms'] = max(500, (int) ($payload['animation_speed_ms'] ?? 2500));
        // Phone settings: separate country code and phone
        $settings['country_code'] = preg_replace('/[^0-9]/', '', (string) ($payload['country_code'] ?? ''));
        $settings['whatsapp_phone'] = preg_replace('/[^0-9]/', '', (string) ($payload['whatsapp_phone'] ?? ''));
        // Legacy field kept for backward compatibility and fallback
        $settings['whatsapp_number'] = preg_replace('/[^0-9]/', '', (string) ($payload['whatsapp_number'] ?? ($settings['whatsapp_number'] ?? '')));
        $settings['message_template'] = wp_kses_post($payload['message_template'] ?? '');
        $settings['extra_emails'] = sanitize_text_field($payload['extra_emails'] ?? '');

        $animated_messages = [];
        if (!empty($payload['animated_messages']) && is_array($payload['animated_messages'])) {
            foreach ($payload['animated_messages'] as $msg) {
                $msg = trim(wp_strip_all_tags($msg));
                if ($msg !== '') {
                    $animated_messages[] = $msg;
                }
            }
        }
        if (!empty($animated_messages)) {
            $settings['animated_messages'] = $animated_messages;
        }

        update_option('baw_settings', $settings);

        // Fields
        $incoming_fields = isset($payload['fields']) && is_array($payload['fields']) ? $payload['fields'] : [];
        $normalized_fields = [];
        foreach ($incoming_fields as $f) {
            $label = sanitize_text_field($f['label'] ?? '');
            if ($label === '') {
                continue;
            }
            $key = sanitize_key($f['key'] ?? baw_sanitize_field_key($label));
            $type = in_array(($f['type'] ?? 'text'), ['text', 'textarea', 'number', 'email', 'select', 'checkbox', 'date', 'time', 'datetime-local', 'tel'], true) ? $f['type'] : 'text';
            $placeholder = sanitize_text_field($f['placeholder'] ?? '');
            $required = !empty($f['required']);
            $enabled = isset($f['enabled']) ? (bool) $f['enabled'] : true;
            $options = [];
            // For 'select', allow comma-separated options in placeholder field for simpler UI
            if ($type === 'select') {
                $raw = $f['options'] ?? ($f['placeholder'] ?? '');
                if (is_string($raw)) {
                    foreach (explode(',', $raw) as $opt) {
                        $o = trim(sanitize_text_field($opt));
                        if ($o !== '') { $options[] = $o; }
                    }
                } elseif (is_array($raw)) {
                    foreach ($raw as $opt) {
                        $o = trim(sanitize_text_field($opt));
                        if ($o !== '') { $options[] = $o; }
                    }
                }
            }
            $normalized_fields[] = compact('key', 'label', 'type', 'placeholder', 'required', 'enabled', 'options');
        }
        if (empty($normalized_fields)) {
            $normalized_fields = baw_get_default_fields();
        }
        update_option('baw_form_fields', $normalized_fields);

        // Ensure DB schema matches latest fields
        BAW_Database::create_or_update_table($normalized_fields);

        wp_send_json_success(['message' => __('Settings saved', 'book-appointment-wp-floating-widget')]);
    }

    public function ajax_mark_contacted() {
        check_ajax_referer('baw_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'book-appointment-wp-floating-widget')], 403);
        }
        $ids = isset($_POST['ids']) ? array_map('intval', (array) $_POST['ids']) : [];
        $state = isset($_POST['contacted']) ? (int) $_POST['contacted'] : 1;
        $updated = BAW_Database::mark_contacted($ids, $state);
        wp_send_json_success(['updated' => $updated]);
    }

    public function handle_export_csv() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'book-appointment-wp-floating-widget'));
        }
        $filename = 'book_appointments_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . $filename);

        $submissions = BAW_Database::get_submissions();
        $out = fopen('php://output', 'w');
        if (!empty($submissions)) {
            // Header
            fputcsv($out, array_keys($submissions[0]));
            foreach ($submissions as $row) {
                fputcsv($out, $row);
            }
        }
        fclose($out);
        exit;
    }
}


