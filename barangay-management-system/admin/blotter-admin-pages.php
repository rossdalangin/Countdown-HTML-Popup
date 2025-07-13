<?php
/**
 * Admin pages for Blotter/Complaints Management
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Handler for displaying the Blotter Records List page.
 */
function bms_blotter_records_list_page_handler() {
    if ( ! current_user_can( BMS_VIEW_BLOTTER_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'barangay-management-system' ) );
    }

    if ( ! function_exists('bms_get_blotter_records') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/blotter-crud.php';
    }

    // Handle delete action
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete_blotter' && isset( $_GET['record_id'] ) ) {
        if ( ! current_user_can( BMS_MANAGE_BLOTTER_CAP ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to delete blotter records.', 'barangay-management-system' ) );
        }
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bms_delete_blotter_' . $_GET['record_id'] ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }
        $record_id = absint( $_GET['record_id'] );
        if ( bms_delete_blotter_record( $record_id ) ) {
            bms_add_admin_notice( __( 'Blotter record deleted successfully.', 'barangay-management-system' ) );
        } else {
            bms_add_admin_notice( __( 'Failed to delete blotter record.', 'barangay-management-system' ), 'error' );
        }
    }

    // Filters
    $filter_args = [];
    $filter_args['status'] = $_GET['status'] ?? '';
    $filter_args['incident_type'] = $_GET['incident_type'] ?? '';
    $filter_args['date_from'] = $_GET['date_from'] ?? ''; // Incident date
    $filter_args['date_to'] = $_GET['date_to'] ?? '';     // Incident date
    $filter_args['search'] = $_GET['s'] ?? '';


    $blotter_records = bms_get_blotter_records( array_merge(['number' => 50], $filter_args) );
    $statuses = bms_get_blotter_statuses(); // For filter dropdown
    $incident_types = bms_get_blotter_incident_types(); // For filter dropdown


    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/blotter/list-blotter-records.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/blotter/list-blotter-records.php';
    } else {
        echo '<div class="wrap"><h1>' . esc_html__( 'Blotter Records', 'barangay-management-system' ) . '</h1><p>' . esc_html__( 'Error: List view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}

/**
 * Handler for displaying the Add/Edit Blotter Record page.
 */
function bms_blotter_add_edit_record_page_handler() {
    ob_start();
    if ( ! current_user_can( BMS_MANAGE_BLOTTER_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to manage blotter records.', 'barangay-management-system' ) );
    }

    if ( ! function_exists('bms_create_blotter_record') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/blotter-crud.php';
    }

    $record_id = isset( $_GET['record_id'] ) ? absint( $_GET['record_id'] ) : 0;
    $record = null;
    $is_editing = false;

    if ( $record_id > 0 ) {
        $record = bms_get_blotter_record( $record_id );
        if ($record) {
            $is_editing = true;
        } else {
            wp_die( __( 'Blotter record not found.', 'barangay-management-system' ) );
        }
    }

    if ( isset( $_POST['bms_submit_blotter'] ) ) {
        if ( ! isset( $_POST['bms_blotter_nonce'] ) || ! wp_verify_nonce( $_POST['bms_blotter_nonce'], 'bms_save_blotter_action' ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }

        $data = array(
            'case_number'            => sanitize_text_field( $_POST['case_number'] ),
            'complainant_name'       => sanitize_text_field( $_POST['complainant_name'] ),
            'complainant_address'    => sanitize_text_field( $_POST['complainant_address'] ),
            'complainant_contact'    => sanitize_text_field( $_POST['complainant_contact'] ),
            'respondent_name'        => sanitize_text_field( $_POST['respondent_name'] ),
            'respondent_address'     => sanitize_text_field( $_POST['respondent_address'] ),
            'respondent_contact'     => sanitize_text_field( $_POST['respondent_contact'] ),
            'incident_type'          => sanitize_text_field( $_POST['incident_type'] ),
            'incident_date'          => sanitize_text_field( $_POST['incident_date'] ), // Validate datetime
            'incident_location'      => sanitize_text_field( $_POST['incident_location'] ),
            'incident_narrative'     => sanitize_textarea_field( $_POST['incident_narrative'] ),
            'status'                 => sanitize_text_field( $_POST['status'] ),
            'assigned_official_name' => sanitize_text_field( $_POST['assigned_official_name'] ),
            'resolution_details'     => sanitize_textarea_field( $_POST['resolution_details'] ),
        );

        // Datetime validation for incident_date
        $incident_datetime_obj = date_create_from_format('Y-m-d\TH:i', $data['incident_date']);
        if (!$incident_datetime_obj) {
            $incident_datetime_obj = date_create_from_format('Y-m-d H:i', $data['incident_date']);
        }

        if (empty($data['incident_date']) || !$incident_datetime_obj) {
             bms_add_admin_notice( __('Invalid incident date/time format. Please use YYYY-MM-DDTHH:MM or YYYY-MM-DD HH:MM.', 'barangay-management-system'), 'error' );
        } else {
            $data['incident_date'] = $incident_datetime_obj->format('Y-m-d H:i:s'); // Convert to DB format
            $result = false;
            if ( $is_editing && $record_id > 0 ) {
                $result = bms_update_blotter_record( $record_id, $data );
                $message = $result ? __( 'Blotter record updated successfully.', 'barangay-management-system' ) : __( 'Failed to update blotter record.', 'barangay-management-system' );
                bms_add_admin_notice( $message, $result ? 'success' : 'error' );
            } else {
                $data['recorded_by'] = get_current_user_id();
                $data['date_reported'] = current_time( 'mysql' );
                if(empty($data['case_number'])) { // Generate if empty
                    $data['case_number'] = bms_generate_blotter_case_number();
                }
                $new_record_id = bms_create_blotter_record( $data );
                $result = $new_record_id !== false;
                if ($result) {
                    bms_add_admin_notice( __( 'Blotter record added successfully.', 'barangay-management-system' ) );
                    wp_redirect( admin_url('admin.php?page=bms-blotter-add&record_id=' . $new_record_id . '&record_added=true') );
                    exit;
                } else {
                    bms_add_admin_notice( __( 'Failed to add blotter record. Please check all required fields.', 'barangay-management-system' ), 'error' );
                }
            }
            if ($result && $is_editing) {
                $record = bms_get_blotter_record( $record_id );
            }
        }
    }

    $blotter_statuses = bms_get_blotter_statuses();
    // If predefined is better:
    // $blotter_statuses = ['Open', 'Under Investigation', 'Amicably Settled', 'Endorsed to PNP', 'Closed', 'Dismissed'];
    $blotter_incident_types = bms_get_blotter_incident_types(); // For datalist

    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/blotter/add-edit-blotter-record.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/blotter/add-edit-blotter-record.php';
    } else {
        $page_title = $is_editing ? __( 'Edit Blotter Record', 'barangay-management-system' ) : __( 'Add New Blotter Record', 'barangay-management-system' );
        echo '<div class="wrap"><h1>' . esc_html( $page_title ) . '</h1><p>' . esc_html__( 'Error: Form view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
    ob_end_flush();
}

?>
