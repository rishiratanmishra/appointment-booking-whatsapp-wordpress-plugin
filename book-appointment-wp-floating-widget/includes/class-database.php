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
            // Admin notes fields for after-visit info
            'charges TEXT',
            'prescription TEXT',
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

    public static function get_submissions_page($args = []) {
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

        // Free text search across common columns if present
        if (!empty($args['search'])) {
            $columns = $wpdb->get_col("DESC $table", 0);
            $searchable = array_intersect($columns, ['id','name','email','mobile','description']);
            if (!empty($searchable)) {
                $like = '%' . $wpdb->esc_like($args['search']) . '%';
                $parts = [];
                foreach ($searchable as $col) {
                    if ($col === 'id' && is_numeric($args['search'])) {
                        $parts[] = 'id = %d';
                        $params[] = (int) $args['search'];
                    } else {
                        $parts[] = "$col LIKE %s";
                        $params[] = $like;
                    }
                }
                if (!empty($parts)) {
                    $where .= ' AND (' . implode(' OR ', $parts) . ')';
                }
            }
        }

        $per_page = isset($args['per_page']) ? max(1, (int) $args['per_page']) : 20;
        $page = isset($args['page']) ? max(1, (int) $args['page']) : 1;
        $offset = ($page - 1) * $per_page;

        $count_sql = "SELECT COUNT(*) FROM $table $where";
        if (!empty($params)) {
            $count_sql = $wpdb->prepare($count_sql, $params);
        }
        $total = (int) $wpdb->get_var($count_sql);

        $sql = "SELECT * FROM $table $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $params_with_limit = $params;
        $params_with_limit[] = $per_page;
        $params_with_limit[] = $offset;
        $sql = $wpdb->prepare($sql, $params_with_limit);
        $rows = $wpdb->get_results($sql, ARRAY_A);

        return [
            'rows' => $rows,
            'total' => $total,
            'per_page' => $per_page,
            'page' => $page,
        ];
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

    public static function get_visits_by_identifier($identifier) {
        global $wpdb;
        $table = self::get_table_name();
        $identifier = trim((string) $identifier);
        if ($identifier === '') {
            return [];
        }
        $columns = $wpdb->get_col("DESC $table", 0);
        $whereParts = [];
        $params = [];

        // Try to infer phone/email field keys from configured form
        $fields = get_option('baw_form_fields');
        $phoneKeys = [];
        $emailKeys = [];
        if (is_array($fields)) {
            foreach ($fields as $f) {
                $key = sanitize_key($f['key'] ?? '');
                $label = strtolower($f['label'] ?? '');
                $type = $f['type'] ?? '';
                if ($type === 'tel' || preg_match('/phone|mobile|contact/', $label) || preg_match('/phone|mobile/', $key)) {
                    $phoneKeys[] = $key;
                }
                if ($type === 'email' || strpos($key, 'email') !== false || strpos($label, 'email') !== false) {
                    $emailKeys[] = $key;
                }
            }
        }
        if (empty($phoneKeys) && in_array('mobile', $columns, true)) { $phoneKeys[] = 'mobile'; }
        if (empty($emailKeys) && in_array('email', $columns, true)) { $emailKeys[] = 'email'; }

        foreach (array_unique($phoneKeys) as $k) {
            if (in_array($k, $columns, true)) { $whereParts[] = "$k = %s"; $params[] = $identifier; }
        }
        foreach (array_unique($emailKeys) as $k) {
            if (in_array($k, $columns, true)) { $whereParts[] = "$k = %s"; $params[] = $identifier; }
        }
        if (empty($whereParts)) {
            return [];
        }
        $sql = "SELECT * FROM $table WHERE " . implode(' OR ', $whereParts) . ' ORDER BY created_at DESC';
        $sql = $wpdb->prepare($sql, $params);
        return $wpdb->get_results($sql, ARRAY_A);
    }

    public static function get_user_groups() {
        global $wpdb;
        $table = self::get_table_name();
        $columns = $wpdb->get_col("DESC $table", 0);
        $hasMobile = in_array('mobile', $columns, true);
        $hasEmail = in_array('email', $columns, true);
        if (!$hasMobile && !$hasEmail) {
            return [];
        }
        $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A);
        $groups = [];
        foreach ($rows as $row) {
            $identifier = '';
            if ($hasMobile && !empty($row['mobile'])) {
                $identifier = $row['mobile'];
            } elseif ($hasEmail && !empty($row['email'])) {
                $identifier = $row['email'];
            } else {
                continue;
            }
            if (!isset($groups[$identifier])) {
                $groups[$identifier] = [
                    'identifier' => $identifier,
                    'latest' => $row,
                    'count' => 0,
                    'contactedCount' => 0,
                    'notContactedCount' => 0,
                ];
            }
            $groups[$identifier]['count']++;
            if ((int) $row['contacted'] === 1) {
                $groups[$identifier]['contactedCount']++;
            } else {
                $groups[$identifier]['notContactedCount']++;
            }
            // Update latest by created_at
            if (strtotime($row['created_at']) > strtotime($groups[$identifier]['latest']['created_at'])) {
                $groups[$identifier]['latest'] = $row;
            }
        }
        return $groups;
    }

    // Backward-compatible: returns only contacted groups
    public static function get_users_index() {
        $groups = self::get_user_groups();
        $out = [];
        foreach ($groups as $g) {
            if ($g['contactedCount'] > 0) {
                $out[] = [
                    'identifier' => $g['identifier'],
                    'latest' => $g['latest'],
                    'count' => $g['count'],
                ];
            }
        }
        return $out;
    }
}


