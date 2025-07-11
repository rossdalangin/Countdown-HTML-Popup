<?php
/**
 * View for Issue Document form (confirmation and custom fields).
 * Variables available:
 * @var ?object $template The document template object.
 * @var ?object $resident The resident object.
 * @var int $template_id
 * @var int $resident_id
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! $template || ! $resident ) {
    echo '<div class="wrap"><p>' . esc_html__( 'Template or Resident not found.', 'barangay-management-system' ) . '</p></div>';
    return;
}

$page_title = sprintf(
    esc_html__( 'Issue "%s" for %s %s', 'barangay-management-system' ),
    $template->template_name,
    $resident->first_name,
    $resident->last_name
);

$defined_placeholders_list = [];
if (!empty($template->defined_placeholders)) {
    // Try JSON decoding first
    $json_decoded = json_decode($template->defined_placeholders, true);
    if (is_array($json_decoded)) {
        $defined_placeholders_list = $json_decoded;
    } else {
        // Fallback to comma-separated if not valid JSON or if it's a simple string
        $defined_placeholders_list = array_map('trim', explode(',', $template->defined_placeholders));
    }
}
// Filter out standard placeholders that are auto-filled, only show truly custom ones.
$standard_auto_filled = [
    '{{resident_full_name}}', '{{resident_first_name}}', '{{resident_last_name}}',
    '{{resident_address}}', '{{resident_birth_date}}', '{{resident_age}}',
    '{{resident_gender}}', '{{resident_civil_status}}', '{{current_date}}'
];
$custom_fields_to_fill = array_diff($defined_placeholders_list, $standard_auto_filled);
// Clean up placeholder format for display and input name: remove {{ and }}
$custom_fields_cleaned = array_map(function($ph) {
    return str_replace(['{{', '}}'], '', $ph);
}, $custom_fields_to_fill);


?>
<div class="wrap">
    <h1><?php echo esc_html( $page_title ); ?></h1>
    <?php settings_errors(); ?>

    <form method="POST" action="<?php echo esc_url( admin_url('admin.php?page=bms-issue-document&template_id=' . $template_id . '&resident_id=' . $resident_id) ); ?>">
        <?php wp_nonce_field( 'bms_issue_document_action_' . $template_id . '_' . $resident_id, 'bms_issue_doc_nonce' ); ?>

        <h2><?php esc_html_e('Document Details', 'barangay-management-system'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Resident Name:', 'barangay-management-system'); ?></th>
                <td><?php echo esc_html(trim(sprintf('%s %s %s', $resident->first_name, $resident->middle_name, $resident->last_name))); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Document Type:', 'barangay-management-system'); ?></th>
                <td><?php echo esc_html($template->template_name); ?></td>
            </tr>
             <tr>
                <th scope="row"><label for="notes"><?php esc_html_e( 'Notes (Optional)', 'barangay-management-system' ); ?></label></th>
                <td><textarea name="notes" id="notes" rows="3" class="large-text"></textarea></td>
            </tr>
        </table>

        <?php if (!empty($custom_fields_cleaned)) : ?>
            <h2><?php esc_html_e('Additional Information', 'barangay-management-system'); ?></h2>
            <p><?php esc_html_e('Please provide values for the following custom fields defined in the template:', 'barangay-management-system'); ?></p>
            <table class="form-table">
                <?php foreach($custom_fields_cleaned as $index => $field_key) :
                    $original_placeholder = $custom_fields_to_fill[$index]; // Get original placeholder with {{}}
                    $field_label = ucwords(str_replace('_', ' ', $field_key));
                ?>
                <tr>
                    <th scope="row"><label for="custom_placeholder_<?php echo esc_attr($field_key); ?>"><?php echo esc_html($field_label); ?></label></th>
                    <td>
                        <input type="text" name="custom_placeholders[<?php echo esc_attr($original_placeholder); ?>]" id="custom_placeholder_<?php echo esc_attr($field_key); ?>" class="regular-text">
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <h2><?php esc_html_e('Preview (Approximate)', 'barangay-management-system'); ?></h2>
        <div style="border: 1px solid #ccc; padding: 15px; background: #f9f9f9; max-height: 400px; overflow-y: auto;">
            <?php
            // A very basic preview - actual placeholders will be filled on submission
            $preview_content = nl2br(esc_html($template->template_content));
            echo $preview_content; // Not using wp_kses_post here for a raw preview
            ?>
        </div>
        <p class="description"><?php esc_html_e('This is a raw preview of the template. Actual data will be filled upon confirmation.', 'barangay-management-system');?></p>

        <?php submit_button( __( 'Confirm and Issue Document', 'barangay-management-system' ), 'primary', 'bms_confirm_issue_document' ); ?>
    </form>
</div>
