<?php
/**
 * Migration Scripts
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Migrate the old document templates to the new custom post type.
 */
function bms_migrate_document_templates() {
    if ( get_option( 'bms_templates_migrated' ) ) {
        return;
    }

    global $wpdb;
    $table_name = bms_get_document_templates_table_name();
    $templates = $wpdb->get_results( "SELECT * FROM {$table_name}" );

    foreach ( $templates as $template ) {
        $post_data = array(
            'post_title'    => $template->template_name,
            'post_content'  => $template->template_content,
            'post_status'   => 'publish',
            'post_type'     => 'bms_doc_template',
        );

        wp_insert_post( $post_data );
    }

    update_option( 'bms_templates_migrated', true );
}
add_action( 'admin_init', 'bms_migrate_document_templates' );
