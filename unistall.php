<?php
/**
 * Uninstall file, which would delete all user metadata and configuration settings
 *
 * @since 1.0
 */
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();

global $wpdb;

$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '%al%';");
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name='al_version';");