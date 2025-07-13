<?php
/**
 * Admin Hooks for Barangay Management System
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register all admin menus for the plugin.
 */
function bms_register_admin_menus() {
    // Top-level menu
    add_menu_page(
        __( 'Barangay MS', 'barangay-management-system' ),
        __( 'Barangay MS', 'barangay-management-system' ),
        BMS_VIEW_RESIDENTS_CAP,
        'barangay-ms',
        'bms_residents_list_page_handler',
        'dashicons-admin-users',
        26
    );

    // Resident Submenus
    add_submenu_page(
        'barangay-ms',
        __( 'All Residents', 'barangay-management-system' ),
        __( 'All Residents', 'barangay-management-system' ),
        BMS_VIEW_RESIDENTS_CAP,
        'bms-residents',
        'bms_residents_list_page_handler'
    );

    add_submenu_page(
        'bms-residents',
        __( 'Add New Resident', 'barangay-management-system' ),
        __( 'Add New Resident', 'barangay-management-system' ),
        BMS_MANAGE_RESIDENTS_CAP,
        'bms-resident-add',
        'bms_resident_add_edit_page_handler'
    );


    add_submenu_page(
        null,
        __( 'Issue Document', 'barangay-management-system' ),
        __( 'Issue Document', 'barangay-management-system' ),
        BMS_ISSUE_DOCUMENTS_CAP,
        'bms-issue-document',
        'bms_issue_document_page_handler'
    );

    add_submenu_page(
        null,
        __( 'View Issued Document', 'barangay-management-system' ),
        __( 'View Issued Document', 'barangay-management-system' ),
        BMS_VIEW_ISSUED_DOCUMENTS_CAP,
        'bms-view-issued-document',
        'bms_view_issued_document_page_handler'
    );

    // Event Submenus
    add_submenu_page(
        'barangay-ms',
        __( 'Events', 'barangay-management-system' ),
        __( 'Events', 'barangay-management-system' ),
        BMS_VIEW_EVENTS_CAP,
        'bms-events',
        'bms_events_list_page_handler'
    );

    add_submenu_page(
        'bms-events',
        __( 'Add New Event', 'barangay-management-system' ),
        __( 'Add New Event', 'barangay-management-system' ),
        BMS_MANAGE_EVENTS_CAP,
        'bms-event-add',
        'bms_event_add_edit_page_handler'
    );

    add_submenu_page(
        null,
        __( 'Manage Event Attendance', 'barangay-management-system' ),
        __( 'Manage Event Attendance', 'barangay-management-system' ),
        BMS_MANAGE_EVENTS_CAP,
        'bms-event-attendance',
        'bms_event_attendance_page_handler'
    );

    // Financial Submenus
    add_submenu_page(
        'barangay-ms',
        __( 'Financials', 'barangay-management-system' ),
        __( 'Financials', 'barangay-management-system' ),
        BMS_VIEW_FINANCIALS_CAP,
        'bms-financials',
        'bms_financials_overview_page_handler'
    );

    add_submenu_page(
        'bms-financials',
        __( 'Add Transaction', 'barangay-management-system' ),
        __( 'Add Transaction', 'barangay-management-system' ),
        BMS_MANAGE_FINANCIALS_CAP,
        'bms-financial-add',
        'bms_financial_add_edit_transaction_page_handler'
    );

    // Blotter Submenus
    add_submenu_page(
        'barangay-ms',
        __( 'Blotter Records', 'barangay-management-system' ),
        __( 'Blotter Records', 'barangay-management-system' ),
        BMS_VIEW_BLOTTER_CAP,
        'bms-blotter',
        'bms_blotter_records_list_page_handler'
    );

    add_submenu_page(
        'bms-blotter',
        __( 'Add New Blotter Record', 'barangay-management-system' ),
        __( 'Add New Record', 'barangay-management-system' ),
        BMS_MANAGE_BLOTTER_CAP,
        'bms-blotter-add',
        'bms_blotter_add_edit_record_page_handler'
    );

    // Certificate Request Submenu
    add_submenu_page(
        'barangay-ms',
        __( 'Certificate Requests', 'barangay-management-system' ),
        __( 'Certificate Requests', 'barangay-management-system' ),
        BMS_MANAGE_CERTIFICATE_REQUESTS_CAP,
        'bms-cert-requests',
        'bms_cert_requests_list_page_handler'
    );
}
add_action( 'admin_menu', 'bms_register_admin_menus' );
