<?php
/**
 * Custom Post Types
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register the Document Template custom post type.
 */
function bms_register_document_template_post_type() {
    $labels = array(
        'name'                  => _x( 'Document Templates', 'Post type general name', 'barangay-management-system' ),
        'singular_name'         => _x( 'Document Template', 'Post type singular name', 'barangay-management-system' ),
        'menu_name'             => _x( 'Document Templates', 'Admin Menu text', 'barangay-management-system' ),
        'name_admin_bar'        => _x( 'Document Template', 'Add New on Toolbar', 'barangay-management-system' ),
        'add_new'               => __( 'Add New', 'barangay-management-system' ),
        'add_new_item'          => __( 'Add New Document Template', 'barangay-management-system' ),
        'new_item'              => __( 'New Document Template', 'barangay-management-system' ),
        'edit_item'             => __( 'Edit Document Template', 'barangay-management-system' ),
        'view_item'             => __( 'View Document Template', 'barangay-management-system' ),
        'all_items'             => __( 'All Document Templates', 'barangay-management-system' ),
        'search_items'          => __( 'Search Document Templates', 'barangay-management-system' ),
        'parent_item_colon'     => __( 'Parent Document Templates:', 'barangay-management-system' ),
        'not_found'             => __( 'No document templates found.', 'barangay-management-system' ),
        'not_found_in_trash'    => __( 'No document templates found in Trash.', 'barangay-management-system' ),
        'featured_image'        => _x( 'Document Template Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'barangay-management-system' ),
        'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'barangay-management-system' ),
        'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'barangay-management-system' ),
        'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'barangay-management-system' ),
        'archives'              => _x( 'Document Template archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'barangay-management-system' ),
        'insert_into_item'      => _x( 'Insert into document template', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'barangay-management-system' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this document template', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'barangay-management-system' ),
        'filter_items_list'     => _x( 'Filter document templates list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'barangay-management-system' ),
        'items_list_navigation' => _x( 'Document templates list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'barangay-management-system' ),
        'items_list'            => _x( 'Document templates list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'barangay-management-system' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => 'barangay-ms',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'document-template' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor' ),
    );

    register_post_type( 'bms_doc_template', $args );
}
add_action( 'init', 'bms_register_document_template_post_type' );
