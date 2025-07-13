<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bms_document_templates" );
