<?php
/**
 * Public Shortcodes
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Certificate Request Form Shortcode.
 *
 * @return string The HTML for the certificate request form.
 */
function bms_certificate_request_form_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>' . __( 'You must be logged in to request a certificate.', 'barangay-management-system' ) . '</p>';
    }

    if ( isset( $_POST['bms_submit_certificate_request'] ) ) {
        if ( ! isset( $_POST['bms_certificate_request_form_nonce'] ) || ! wp_verify_nonce( $_POST['bms_certificate_request_form_nonce'], 'bms_certificate_request_form' ) ) {
            return '<p>' . __( 'Security check failed.', 'barangay-management-system' ) . '</p>';
        }

        $template_id = absint( $_POST['document_template_id'] );
        $purpose = sanitize_textarea_field( $_POST['purpose'] );
        $resident_id = get_current_user_id();

        $request_id = bms_create_certificate_request( [
            'resident_id' => $resident_id,
            'document_template_id' => $template_id,
            'purpose' => $purpose,
        ] );

        if ( $request_id ) {
            return '<p>' . __( 'Your certificate request has been submitted successfully.', 'barangay-management-system' ) . '</p>';
        } else {
            return '<p>' . __( 'There was an error submitting your request. Please try again.', 'barangay-management-system' ) . '</p>';
        }
    }

    ob_start();
    include BMS_PLUGIN_DIR . 'public/views/certificate-request-form.php';
    return ob_get_clean();
}
add_shortcode( 'bms_certificate_request_form', 'bms_certificate_request_form_shortcode' );

/**
 * My Certificate Requests Shortcode.
 *
 * @return string The HTML for the certificate requests table.
 */
function bms_my_certificate_requests_shortcode() {
    if ( ! is_user_logged_in() ) {
        return '<p>' . __( 'You must be logged in to view your certificate requests.', 'barangay-management-system' ) . '</p>';
    }

    $resident_id = get_current_user_id();
    $requests = bms_get_certificate_requests( [ 'resident_id' => $resident_id, 'number' => 999 ] );

    ob_start();
    include BMS_PLUGIN_DIR . 'public/views/my-certificate-requests.php';
    return ob_get_clean();
}
add_shortcode( 'bms_my_certificate_requests', 'bms_my_certificate_requests_shortcode' );
