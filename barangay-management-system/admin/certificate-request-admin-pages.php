<?php
/**
 * Admin pages for Certificate Request Management
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Handler for displaying the Certificate Requests List page.
 */
function bms_cert_requests_list_page_handler() {
    if ( ! current_user_can( BMS_MANAGE_CERTIFICATE_REQUESTS_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'barangay-management-system' ) );
    }

    if ( ! function_exists('bms_get_certificate_requests') || ! function_exists('bms_update_certificate_request') || ! function_exists('bms_issue_document') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/certificate-request-crud.php';
        require_once BMS_PLUGIN_DIR . 'includes/crud/document-crud.php'; // For issuing
        require_once BMS_PLUGIN_DIR . 'includes/crud/resident-crud.php'; // For resident info
    }

    // Handle actions like approve, reject, generate, mark paid, mark claimed
    if ( isset( $_GET['action'] ) && isset( $_GET['request_id'] ) ) {
        $action = sanitize_key($_GET['action']);
        $request_id = absint($_GET['request_id']);
        // Nonce check for all actions
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bms_cert_request_action_' . $request_id ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }

        $update_data = [];
        $redirect_url = admin_url('admin.php?page=bms-cert-requests');
        $message = '';
        $message_type = 'success';

        switch ($action) {
            case 'approve_request':
                $update_data['status'] = 'Approved';
                $message = __('Request approved.', 'barangay-management-system');
                break;
            case 'reject_request':
                // For rejection, ideally a modal or separate page to input reason.
                // For now, a simple status change. Admin notes can be added via edit.
                $update_data['status'] = 'Rejected';
                 $message = __('Request rejected.', 'barangay-management-system');
                break;
            case 'mark_processing':
                $update_data['status'] = 'Processing';
                $message = __('Request marked as processing.', 'barangay-management-system');
                break;
            case 'mark_ready':
                $update_data['status'] = 'Ready for Pickup';
                $message = __('Request marked as ready for pickup.', 'barangay-management-system');
                break;
            case 'mark_claimed':
                $update_data['status'] = 'Claimed';
                 $message = __('Request marked as claimed.', 'barangay-management-system');
                break;
            case 'mark_paid':
                // Ideally, a form to input OR number and amount.
                $update_data['payment_status'] = 'Paid';
                $update_data['payment_details'] = ($_GET['details'] ?? 'Paid at barangay hall'); // Simple for now
                $message = __('Request marked as paid.', 'barangay-management-system');
                break;
            case 'generate_cert':
                $request = bms_get_certificate_request($request_id);
                if ($request && $request->status === 'Approved') { // Only generate for approved requests
                    $issue_data = [
                        'resident_id' => $request->resident_id,
                        'template_id' => $request->document_template_id,
                        'notes'       => 'Generated from Certificate Request ID: ' . $request_id,
                        'placeholder_values' => ['purpose' => $request->purpose] // Pass purpose as a placeholder
                    ];
                    $issued_doc_id = bms_issue_document($issue_data);
                    if ($issued_doc_id) {
                        bms_update_certificate_request($request_id, ['status' => 'Processing', 'issued_document_id' => $issued_doc_id]);
                        // Redirect to view the issued document
                        wp_redirect(admin_url('admin.php?page=bms-view-issued-document&issued_doc_id=' . $issued_doc_id . '&from_request=true'));
                        exit;
                    } else {
                        $message = __('Failed to generate certificate.', 'barangay-management-system');
                        $message_type = 'error';
                    }
                } else {
                    $message = __('Certificate can only be generated for approved requests.', 'barangay-management-system');
                    $message_type = 'error';
                }
                break;
        }

        if (!empty($update_data)) {
            if (bms_update_certificate_request( $request_id, $update_data)) {
                 // $message already set
            } else {
                $message = __('Failed to update request status.', 'barangay-management-system');
                $message_type = 'error';
            }
        }
        if ($message) {
            bms_add_admin_notice( $message, $message_type );
        }
         // To reflect changes immediately if not redirecting elsewhere
        // wp_redirect($redirect_url); exit; // This causes "headers already sent" with add_action('admin_notices')
    }

    // Filters
    $filter_args = [];
    $filter_args['status'] = $_GET['status'] ?? '';
    $filter_args['date_from'] = $_GET['date_from'] ?? '';
    $filter_args['date_to'] = $_GET['date_to'] ?? '';
    $filter_args['search_resident'] = $_GET['s_res'] ?? '';
    $filter_args['search_template'] = $_GET['s_tpl'] ?? '';


    $requests = bms_get_certificate_requests( array_merge(['number' => 50], $filter_args) );
    $statuses = bms_get_certificate_request_statuses();
    $document_templates = bms_get_document_templates(['number' => 999]); // For filter dropdown


    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/certificate-request/list-certificate-requests.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/certificate-request/list-certificate-requests.php';
    } else {
        echo '<div class="wrap"><h1>' . esc_html__( 'Certificate Requests', 'barangay-management-system' ) . '</h1><p>' . esc_html__( 'Error: List view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}

// No separate add/edit page for requests from admin side for now.
// Requests are primarily submitted by residents. Admin manages them.
// Staff could submit on behalf of resident via front-end form or a simplified admin form if needed later.

?>
