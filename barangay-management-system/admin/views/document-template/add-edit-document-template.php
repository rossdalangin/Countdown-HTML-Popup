<?php
/**
 * View for Add/Edit Document Template form.
 * Variables available:
 * @var bool $is_editing True if editing, false if adding.
 * @var ?object $template The template object if editing.
 * @var int $template_id The ID of the template being edited, or 0.
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}

$template_name = $template->template_name ?? '';
$template_content = $template->template_content ?? '';
// Assuming defined_placeholders is stored as a comma-separated string for simple input
// Or it could be JSON, adjust textarea and processing accordingly.
$defined_placeholders = $template->defined_placeholders ?? '';

$page_title = $is_editing ? __( 'Edit Document Template', 'barangay-management-system' ) : __( 'Add New Document Template', 'barangay-management-system' );
$submit_button_text = $is_editing ? __( 'Update Template', 'barangay-management-system' ) : __( 'Add Template', 'barangay-management-system' );
?>
<div class="wrap">
    <h1><?php echo esc_html( $page_title ); ?></h1>
    <?php settings_errors(); ?>

    <form method="POST" action="">
        <?php wp_nonce_field( 'bms_save_doc_template_action', 'bms_doc_template_nonce' ); ?>
        <input type="hidden" name="template_id" value="<?php echo esc_attr( $template_id ); ?>">

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="template_name"><?php esc_html_e( 'Template Name', 'barangay-management-system' ); ?></label></th>
                    <td><input name="template_name" type="text" id="template_name" value="<?php echo esc_attr( $template_name ); ?>" class="regular-text" required>
                    <p class="description"><?php esc_html_e( 'E.g., Barangay Clearance, Certificate of Indigency', 'barangay-management-system' ); ?></p></td>
                </tr>
                <tr>
                    <th scope="row"><label for="template_content"><?php esc_html_e( 'Template Content', 'barangay-management-system' ); ?></label></th>
                    <td>
                        <?php
                        wp_editor( $template_content, 'template_content', array(
                            'textarea_name' => 'template_content',
                            'media_buttons' => false,
                            'textarea_rows' => 15,
                            'tinymce'       => true, // Use TinyMCE
                            'quicktags'     => true
                        ) );
                        ?>
                        <p class="description">
                            <?php esc_html_e( 'Use placeholders like:', 'barangay-management-system' ); ?>
                            <code>{{resident_full_name}}</code>,
                            <code>{{resident_address}}</code>,
                            <code>{{resident_birth_date}}</code>,
                            <code>{{resident_age}}</code>,
                            <code>{{current_date}}</code>.
                            <?php esc_html_e( 'Define any other custom placeholders below.', 'barangay-management-system' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="defined_placeholders"><?php esc_html_e( 'Custom Placeholders (Optional)', 'barangay-management-system' ); ?></label></th>
                    <td>
                        <textarea name="defined_placeholders" id="defined_placeholders" rows="3" class="large-text code"><?php echo esc_textarea( $defined_placeholders ); ?></textarea>
                        <p class="description">
                            <?php esc_html_e( 'If your template uses custom placeholders beyond the standard resident fields (e.g., <code>{{purpose}}</code>, <code>{{or_number}}</code>), list them here. Enter as a comma-separated list (e.g., <code>{{purpose}}, {{or_number}}</code>) or a JSON array (e.g., <code>["{{purpose}}", "{{or_number}}"]</code>). These can be used to generate form fields when issuing the document.', 'barangay-management-system' ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button( $submit_button_text, 'primary', 'bms_submit_template' ); ?>
    </form>
</div>
