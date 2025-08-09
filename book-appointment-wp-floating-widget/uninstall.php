<?php
// If uninstall not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('baw_settings');
delete_option('baw_form_fields');

// Optionally drop table
global $wpdb;
$table = $wpdb->prefix . 'book_appointments';
$wpdb->query("DROP TABLE IF EXISTS $table");


