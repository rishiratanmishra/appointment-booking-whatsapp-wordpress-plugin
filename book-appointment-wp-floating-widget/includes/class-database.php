<?php
if (!defined('ABSPATH')) {
    exit;
}

class BAW_Database {
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'book_appointments';
    }

    public static function create_or_update_table($fields) {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        // Base columns
        $columns_sql = [
            'id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
            'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'contacted TINYINT(1) NOT NULL DEFAULT 0',
        ];

        // Ensure standard fields exist as text
        $standard_keys = ['name', 'mobile', 'visit_time', 'description'];
        foreach ($standard_keys as $key) {
            $identifier = preg_replace('/[^a-z0-9_]/', '', $key);
            $columns_sql[] = "$identifier TEXT";
        }

        // Custom fields
        if (is_array($fields)) {
            foreach ($fields as $field) {
                if (empty($field['key'])) {
                    $field['key'] = baw_sanitize_field_key($field['label'] ?? '');
                }
                $col = sanitize_key($field['key']);
                if (!in_array($col, $standard_keys, true)) {
                    $columns_sql[] = "$col TEXT";
                }
            }
        }

        $sql = "CREATE TABLE $table_name (" . implode(",", $columns_sql) . ", PRIMARY KEY  (id)) $charset_collate;";
        dbDelta($sql);
    }

    public static function insert_submission($data) {
        global $wpdb;
        $table = self::get_table_name();
        $wpdb->insert($table, $data);
        return (int) $wpdb->insert_id;
    }

    public static function get_submissions($args = []) {
        global $wpdb;
        $table = self::get_table_name();
        $where = 'WHERE 1=1';
        $params = [];
        if (isset($args['contacted']) && $args['contacted'] !== '') {
            $where .= ' AND contacted = %d';
            $params[] = (int) $args['contacted'];
        }
        if (!empty($args['date_from'])) {
            $where .= ' AND created_at >= %s';
            $params[] = $args['date_from'];
        }
        if (!empty($args['date_to'])) {
            $where .= ' AND created_at <= %s';
            $params[] = $args['date_to'];
        }
        $sql = "SELECT * FROM $table $where ORDER BY created_at DESC";
        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }
        return $wpdb->get_results($sql, ARRAY_A);
    }

    public static function mark_contacted($ids, $contacted = 1) {
        global $wpdb;
        $table = self::get_table_name();
        $ids = array_map('intval', (array) $ids);
        if (empty($ids)) {
            return 0;
        }
        $ids_in = '(' . implode(',', $ids) . ')';
        return $wpdb->query("UPDATE $table SET contacted = " . (int) $contacted . " WHERE id IN $ids_in");
    }
}


