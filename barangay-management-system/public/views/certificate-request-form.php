<?php
/**
 * Certificate Request Form View
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

// Get the document templates
$templates = bms_get_document_templates();

?>

<form method="post">
    <?php wp_nonce_field( 'bms_certificate_request_form', 'bms_certificate_request_form_nonce' ); ?>
    <p>
        <label for="document_template_id"><?php _e( 'Certificate Type', 'barangay-management-system' ); ?></label>
        <select id="document_template_id" name="document_template_id" required>
            <option value=""><?php _e( 'Select Certificate Type', 'barangay-management-system' ); ?></option>
            <?php foreach ( $templates as $template ) : ?>
                <option value="<?php echo esc_attr( $template->id ); ?>"><?php echo esc_html( $template->template_name ); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="purpose"><?php _e( 'Purpose', 'barangay-management-system' ); ?></label>
        <textarea id="purpose" name="purpose" rows="5" required></textarea>
    </p>
    <p>
        <input type="submit" name="bms_submit_certificate_request" value="<?php _e( 'Submit Request', 'barangay-management-system' ); ?>">
    </p>
</form>
