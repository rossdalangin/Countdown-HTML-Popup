<?php
/**
 * User Roles and Capabilities Management
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define custom capabilities
define( 'BMS_MANAGE_ALL_CAP', 'bms_manage_all_settings' ); // For top-level admin

// Resident Module Capabilities
define( 'BMS_MANAGE_RESIDENTS_CAP', 'bms_manage_residents' );
define( 'BMS_VIEW_RESIDENTS_CAP', 'bms_view_residents' );

// Document Module Capabilities
define( 'BMS_MANAGE_DOCUMENT_TEMPLATES_CAP', 'bms_manage_document_templates');
define( 'BMS_VIEW_DOCUMENT_TEMPLATES_CAP', 'bms_view_document_templates');
define( 'BMS_ISSUE_DOCUMENTS_CAP', 'bms_issue_documents');
define( 'BMS_VIEW_ISSUED_DOCUMENTS_CAP', 'bms_view_issued_documents');

// Event Module Capabilities
define( 'BMS_MANAGE_EVENTS_CAP', 'bms_manage_events');
define( 'BMS_VIEW_EVENTS_CAP', 'bms_view_events');

// Financial Module Capabilities
define( 'BMS_MANAGE_FINANCIALS_CAP', 'bms_manage_financials');
define( 'BMS_VIEW_FINANCIALS_CAP', 'bms_view_financials');

// Blotter/Complaints Module Capabilities
define( 'BMS_MANAGE_BLOTTER_CAP', 'bms_manage_blotter');
define( 'BMS_VIEW_BLOTTER_CAP', 'bms_view_blotter');

// Certificate Request Module Capabilities
define( 'BMS_MANAGE_CERTIFICATE_REQUESTS_CAP', 'bms_manage_certificate_requests'); // For staff/admin
define( 'BMS_SUBMIT_CERTIFICATE_REQUEST_CAP', 'bms_submit_certificate_request');    // For residents (front-end)
define( 'BMS_VIEW_OWN_CERTIFICATE_REQUESTS_CAP', 'bms_view_own_certificate_requests'); // For residents (front-end)


// Add more capabilities as modules are developed
// define( 'BMS_VIEW_OWN_DATA_CAP', 'bms_view_own_data' ); // General capability for residents viewing their profile.


/**
 * Add custom user roles and capabilities.
 * This function should be called on plugin activation.
 */
function bms_add_roles_and_capabilities() {
    // Barangay Administrator Role
    add_role(
        'barangay_administrator',
        __( 'Barangay Administrator', 'barangay-management-system' ),
        array(
            'read'                         => true, // Core WP capability
            BMS_MANAGE_ALL_CAP             => true,
            // Resident Caps
            BMS_MANAGE_RESIDENTS_CAP       => true,
            BMS_VIEW_RESIDENTS_CAP         => true,
            // Document Caps
            BMS_MANAGE_DOCUMENT_TEMPLATES_CAP => true,
            BMS_VIEW_DOCUMENT_TEMPLATES_CAP => true,
            BMS_ISSUE_DOCUMENTS_CAP        => true,
            BMS_VIEW_ISSUED_DOCUMENTS_CAP  => true,
            // Event Caps
            BMS_MANAGE_EVENTS_CAP          => true,
            BMS_VIEW_EVENTS_CAP            => true,
            // Financial Caps
            BMS_MANAGE_FINANCIALS_CAP      => true,
            BMS_VIEW_FINANCIALS_CAP        => true,
            // Blotter Caps
            BMS_MANAGE_BLOTTER_CAP         => true,
            BMS_VIEW_BLOTTER_CAP           => true,
            // Certificate Request Caps
            BMS_MANAGE_CERTIFICATE_REQUESTS_CAP => true,
            BMS_SUBMIT_CERTIFICATE_REQUEST_CAP  => true, // Admin/Staff can also submit on behalf
            BMS_VIEW_OWN_CERTIFICATE_REQUESTS_CAP => true, // Admin/Staff can view their own if they were to make one
            // Add all other BMS capabilities here as they are defined
        )
    );

    // Barangay Staff Role
    add_role(
        'barangay_staff',
        __( 'Barangay Staff', 'barangay-management-system' ),
        array(
            'read'                         => true, // Core WP capability
            // Resident Caps
            BMS_MANAGE_RESIDENTS_CAP       => true,
            BMS_VIEW_RESIDENTS_CAP         => true,
            // Document Caps (Staff can view templates, issue documents, view issued documents, but not manage templates)
            BMS_VIEW_DOCUMENT_TEMPLATES_CAP => true,
            BMS_ISSUE_DOCUMENTS_CAP        => true,
            BMS_VIEW_ISSUED_DOCUMENTS_CAP  => true,
            // Event Caps (Staff can manage events - includes adding attendees)
            BMS_MANAGE_EVENTS_CAP          => true,
            BMS_VIEW_EVENTS_CAP            => true,
            // Financial Caps (Staff can view financials, but not manage them)
            BMS_VIEW_FINANCIALS_CAP        => true,
            // Blotter Caps (Staff can manage blotter records)
            BMS_MANAGE_BLOTTER_CAP         => true,
            BMS_VIEW_BLOTTER_CAP           => true,
            // Certificate Request Caps (Staff can manage requests)
            BMS_MANAGE_CERTIFICATE_REQUESTS_CAP => true,
            BMS_SUBMIT_CERTIFICATE_REQUEST_CAP  => true, // Staff can submit on behalf
            BMS_VIEW_OWN_CERTIFICATE_REQUESTS_CAP => true,
            // Add other specific capabilities for staff
        )
    );

    // Resident Role
    add_role(
        'resident',
        __( 'Resident', 'barangay-management-system' ),
        array(
            'read' => true, // Core WP capability
            // BMS_VIEW_OWN_DATA_CAP => true, // General profile view
            BMS_SUBMIT_CERTIFICATE_REQUEST_CAP => true,    // Can submit requests
            BMS_VIEW_OWN_CERTIFICATE_REQUESTS_CAP => true, // Can view their own requests
            // More capabilities will be added as front-end features for residents are built.
        )
    );

    // Add capabilities to existing administrator role for full control
    $admin_role = get_role( 'administrator' );
    if ( $admin_role ) {
        $admin_role->add_cap( BMS_MANAGE_ALL_CAP );
        // Resident Caps
        $admin_role->add_cap( BMS_MANAGE_RESIDENTS_CAP );
        $admin_role->add_cap( BMS_VIEW_RESIDENTS_CAP );
        // Document Caps
        $admin_role->add_cap( BMS_MANAGE_DOCUMENT_TEMPLATES_CAP );
        $admin_role->add_cap( BMS_VIEW_DOCUMENT_TEMPLATES_CAP );
        $admin_role->add_cap( BMS_ISSUE_DOCUMENTS_CAP );
        $admin_role->add_cap( BMS_VIEW_ISSUED_DOCUMENTS_CAP );
        // Event Caps
        $admin_role->add_cap( BMS_MANAGE_EVENTS_CAP );
        $admin_role->add_cap( BMS_VIEW_EVENTS_CAP );
        // Financial Caps
        $admin_role->add_cap( BMS_MANAGE_FINANCIALS_CAP );
        $admin_role->add_cap( BMS_VIEW_FINANCIALS_CAP );
        // Blotter Caps
        $admin_role->add_cap( BMS_MANAGE_BLOTTER_CAP );
        $admin_role->add_cap( BMS_VIEW_BLOTTER_CAP );
        // Certificate Request Caps
        $admin_role->add_cap( BMS_MANAGE_CERTIFICATE_REQUESTS_CAP );
        $admin_role->add_cap( BMS_SUBMIT_CERTIFICATE_REQUEST_CAP );
        $admin_role->add_cap( BMS_VIEW_OWN_CERTIFICATE_REQUESTS_CAP );
        // Add all other BMS caps to WP Admin
    }
}

/**
 * Remove custom user roles and capabilities.
 * This function should be called on plugin deactivation/uninstall (optional).
 */
function bms_remove_roles_and_capabilities() {
    // Remove capabilities from WP Administrator first
    $admin_role = get_role( 'administrator' );
    if ( $admin_role ) {
        $admin_role->remove_cap( BMS_MANAGE_ALL_CAP );
        // Resident Caps
        $admin_role->remove_cap( BMS_MANAGE_RESIDENTS_CAP );
        $admin_role->remove_cap( BMS_VIEW_RESIDENTS_CAP );
        // Document Caps
        $admin_role->remove_cap( BMS_MANAGE_DOCUMENT_TEMPLATES_CAP );
        $admin_role->remove_cap( BMS_VIEW_DOCUMENT_TEMPLATES_CAP );
        $admin_role->remove_cap( BMS_ISSUE_DOCUMENTS_CAP );
        $admin_role->remove_cap( BMS_VIEW_ISSUED_DOCUMENTS_CAP );
        // Event Caps
        $admin_role->remove_cap( BMS_MANAGE_EVENTS_CAP );
        $admin_role->remove_cap( BMS_VIEW_EVENTS_CAP );
        // Financial Caps
        $admin_role->remove_cap( BMS_MANAGE_FINANCIALS_CAP );
        $admin_role->remove_cap( BMS_VIEW_FINANCIALS_CAP );
        // Blotter Caps
        $admin_role->remove_cap( BMS_MANAGE_BLOTTER_CAP );
        $admin_role->remove_cap( BMS_VIEW_BLOTTER_CAP );
        // Certificate Request Caps
        $admin_role->remove_cap( BMS_MANAGE_CERTIFICATE_REQUESTS_CAP );
        $admin_role->remove_cap( BMS_SUBMIT_CERTIFICATE_REQUEST_CAP );
        $admin_role->remove_cap( BMS_VIEW_OWN_CERTIFICATE_REQUESTS_CAP );
        // Remove all other BMS caps
    }

    // Remove custom roles
    if ( get_role( 'barangay_administrator' ) ) {
        remove_role( 'barangay_administrator' );
    }
    if ( get_role( 'barangay_staff' ) ) {
        remove_role( 'barangay_staff' );
    }
    if ( get_role( 'resident' ) ) {
        remove_role( 'resident' );
    }
}

?>
