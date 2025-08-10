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
        add_action('wp_ajax_baw_update_visit', [$this, 'ajax_update_visit']);
        add_action('wp_ajax_baw_get_user_profile', [$this, 'ajax_get_user_profile']);
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
        add_submenu_page('baw_main', __('Manage Users', 'book-appointment-wp-floating-widget'), __('Manage Users', 'book-appointment-wp-floating-widget'), $cap, 'baw_users', [$this, 'render_users_page']);
    }

    public function enqueue_assets($hook) {
        // Load assets only on our plugin pages
        $is_plugin_screen = (strpos($hook, 'baw_') !== false) || ($hook === 'toplevel_page_baw_main');
        if (!$is_plugin_screen) {
            return;
        }

        // Cache-busting using file modification time
        $css_path = BAW_PLUGIN_DIR . 'assets/css/admin-style.css';
        $js_path  = BAW_PLUGIN_DIR . 'assets/js/admin-script.js';
        $css_ver  = file_exists($css_path) ? filemtime($css_path) : BAW_VERSION;
        $js_ver   = file_exists($js_path) ? filemtime($js_path) : BAW_VERSION;

        wp_enqueue_style('dashicons');
        wp_enqueue_style('baw-admin', BAW_PLUGIN_URL . 'assets/css/admin-style.css', [], $css_ver);
        wp_enqueue_script('baw-admin', BAW_PLUGIN_URL . 'assets/js/admin-script.js', ['jquery'], $js_ver, true);
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

    public function render_users_page() {
        include BAW_PLUGIN_DIR . 'admin/users-page.php';
    }

    public function ajax_save_settings() {
        check_ajax_referer('baw_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'book-appointment-wp-floating-widget')], 403);
        }

        $payload = wp_unslash($_POST);
        $scope = isset($payload['baw_scope']) ? sanitize_text_field($payload['baw_scope']) : '';

        // Settings (only update when scope is general or not provided but settings fields are present)
        $settings = wp_parse_args(get_option('baw_settings'), baw_get_default_settings());
        $should_update_settings = ($scope === 'general') || (
            $scope === '' && (
                isset($payload['position']) || isset($payload['icon']) || isset($payload['floating_text_type']) || isset($payload['static_text']) || isset($payload['animation_speed_ms']) || isset($payload['country_code']) || isset($payload['whatsapp_phone']) || isset($payload['whatsapp_number']) || isset($payload['message_template']) || isset($payload['extra_emails']) || isset($payload['enabled']) || isset($payload['animated_messages'])
            )
        );

        if ($should_update_settings) {
            // Checkbox: enabled → when saving general, absent means 0; otherwise keep existing
            if ($scope === 'general') {
                $settings['enabled'] = isset($payload['enabled']) ? 1 : 0;
            } elseif (isset($payload['enabled'])) {
                $settings['enabled'] = (int) $payload['enabled'];
            }

            if (isset($payload['position'])) {
                $settings['position'] = in_array($payload['position'], ['left', 'right'], true) ? $payload['position'] : 'right';
            }
            if (isset($payload['icon'])) {
                $settings['icon'] = sanitize_text_field($payload['icon']);
            }
            if (isset($payload['floating_text_type'])) {
                $settings['floating_text_type'] = in_array($payload['floating_text_type'], ['static', 'animated'], true) ? $payload['floating_text_type'] : 'static';
            }
            if (isset($payload['static_text'])) {
                $settings['static_text'] = sanitize_text_field($payload['static_text']);
            }
            if (isset($payload['animation_speed_ms'])) {
                $settings['animation_speed_ms'] = max(500, (int) $payload['animation_speed_ms']);
            }
            if (isset($payload['country_code'])) {
                $settings['country_code'] = preg_replace('/[^0-9]/', '', (string) $payload['country_code']);
            }
            if (isset($payload['whatsapp_phone'])) {
                $settings['whatsapp_phone'] = preg_replace('/[^0-9]/', '', (string) $payload['whatsapp_phone']);
            }
            if (isset($payload['whatsapp_number'])) {
                $settings['whatsapp_number'] = preg_replace('/[^0-9]/', '', (string) $payload['whatsapp_number']);
            }
            if (isset($payload['message_template'])) {
                $settings['message_template'] = wp_kses_post($payload['message_template']);
            }
            if (isset($payload['extra_emails'])) {
                $settings['extra_emails'] = sanitize_text_field($payload['extra_emails']);
            }

            if (isset($payload['animated_messages']) && is_array($payload['animated_messages'])) {
                $animated_messages = [];
                foreach ($payload['animated_messages'] as $msg) {
                    $msg = trim(wp_strip_all_tags($msg));
                    if ($msg !== '') {
                        $animated_messages[] = $msg;
                    }
                }
                if (!empty($animated_messages)) {
                    $settings['animated_messages'] = $animated_messages;
                }
            }

            update_option('baw_settings', $settings);
        }

        // Fields
        $incoming_fields = (isset($payload['fields']) && is_array($payload['fields'])) ? $payload['fields'] : null;
        if ($scope === 'fields' || $incoming_fields !== null) {
            $normalized_fields = [];
            foreach ((array) $incoming_fields as $f) {
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
            if (!empty($normalized_fields)) {
                update_option('baw_form_fields', $normalized_fields);
                // Ensure DB schema matches latest fields
                BAW_Database::create_or_update_table($normalized_fields);
            }
        }

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

    public function ajax_update_visit() {
        check_ajax_referer('baw_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'book-appointment-wp-floating-widget')], 403);
        }
        global $wpdb;
        $table = BAW_Database::get_table_name();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $charges = sanitize_text_field(wp_unslash($_POST['charges'] ?? ''));
        $prescription = wp_kses_post(wp_unslash($_POST['prescription'] ?? ''));
        if ($id <= 0) {
            wp_send_json_error(['message' => __('Invalid visit ID', 'book-appointment-wp-floating-widget')]);
        }
        $wpdb->update($table, [
            'charges' => $charges,
            'prescription' => $prescription,
        ], ['id' => $id]);
        wp_send_json_success(['message' => __('Saved', 'book-appointment-wp-floating-widget')]);
    }

    public function ajax_get_user_profile() {
        check_ajax_referer('baw_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'book-appointment-wp-floating-widget')], 403);
        }
        $identifier = sanitize_text_field(wp_unslash($_GET['identifier'] ?? ''));
        if ($identifier === '') {
            wp_send_json_error(['message' => __('Invalid identifier', 'book-appointment-wp-floating-widget')]);
        }
        $visits = BAW_Database::get_visits_by_identifier($identifier);
        wp_send_json_success(['visits' => $visits]);
    }
}


