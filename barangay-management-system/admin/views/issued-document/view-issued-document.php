<?php
/**
 * View for displaying a single issued document.
 * Variables available:
 * @var ?object $issued_document The issued document object.
 *                             Includes resident_id, template_id, document_type, generated_content, issued_at etc.
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! $issued_document ) {
    echo '<div class="wrap"><p>' . esc_html__( 'Issued document not found.', 'barangay-management-system' ) . '</p></div>';
    return;
}

// Optionally, fetch resident and template details if needed for display
$resident = null;
if (function_exists('bms_get_resident')) {
    $resident = bms_get_resident($issued_document->resident_id);
}
// $template = bms_get_document_template($issued_document->template_id); // If needed

$page_title = sprintf(
    esc_html__( 'Issued Document: %s', 'barangay-management-system' ),
    esc_html($issued_document->document_type)
);

?>
<div class="wrap">
    <h1><?php echo $page_title; ?></h1>
    <?php settings_errors(); // For notices like "Document issued successfully" ?>

    <div id="bms-issued-document-actions" style="margin-bottom: 20px;">
        <button onclick="window.print();" class="button button-primary"><?php esc_html_e('Print Document', 'barangay-management-system'); ?></button>
        <?php
        $edit_resident_url = $resident ? admin_url('admin.php?page=bms-resident-add&resident_id=' . $resident->id) : '#';
        if($resident) {
            printf(
                ' <a href="%s" class="button">%s</a>',
                esc_url($edit_resident_url),
                esc_html__('View Resident Profile', 'barangay-management-system')
            );
        }
        // Link to list of all issued documents (page not created yet)
        // printf(
        //     ' <a href="%s" class="button">%s</a>',
        //     esc_url(admin_url('admin.php?page=bms-issued-documents')),
        //     esc_html__('All Issued Documents', 'barangay-management-system')
        // );
        ?>
    </div>

    <div id="bms-issued-document-details" style="margin-bottom: 20px; padding:10px; border:1px solid #eee;">
        <h2><?php esc_html_e('Document Information', 'barangay-management-system'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php esc_html_e('Document Type:', 'barangay-management-system'); ?></th>
                <td><?php echo esc_html($issued_document->document_type); ?></td>
            </tr>
            <?php if ($resident): ?>
            <tr>
                <th scope="row"><?php esc_html_e('Issued To:', 'barangay-management-system'); ?></th>
                <td><a href="<?php echo esc_url($edit_resident_url); ?>"><?php echo esc_html(trim(sprintf('%s %s', $resident->first_name, $resident->last_name))); ?></a></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th scope="row"><?php esc_html_e('Issued On:', 'barangay-management-system'); ?></th>
                <td><?php echo esc_html(gmdate('F j, Y H:i:s', strtotime($issued_document->issued_at))); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Issued By:', 'barangay-management-system'); ?></th>
                <td>
                    <?php
                    $issuer_info = $issued_document->issued_by_user_id ? get_userdata($issued_document->issued_by_user_id) : null;
                    echo $issuer_info ? esc_html($issuer_info->display_name) : esc_html__('N/A', 'barangay-management-system');
                    ?>
                </td>
            </tr>
             <tr>
                <th scope="row"><?php esc_html_e('Status:', 'barangay-management-system'); ?></th>
                <td><?php echo esc_html($issued_document->status); ?></td>
            </tr>
            <?php if (!empty($issued_document->notes)): ?>
            <tr>
                <th scope="row"><?php esc_html_e('Notes:', 'barangay-management-system'); ?></th>
                <td><?php echo nl2br(esc_html($issued_document->notes)); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <div id="bms-document-content-preview" class="postbox">
        <h2 class="hndle"><span><?php esc_html_e('Generated Document Content', 'barangay-management-system'); ?></span></h2>
        <div class="inside">
            <?php
            // Output the generated content. It's expected to be HTML.
            // wp_kses_post is important here if this content could ever be user-modified AFTER generation
            // or if templates themselves are not fully trusted.
            // For direct output of trusted generated HTML:
            echo wp_kses_post( $issued_document->generated_content );
            ?>
        </div>
    </div>

    <style type="text/css" media="print">
        body * { visibility: hidden; }
        #bms-document-content-preview, #bms-document-content-preview * { visibility: visible; }
        #bms-document-content-preview { position: absolute; left: 0; top: 0; width: 100%; margin:0; padding:0; border:0;}
        .postbox .inside { margin: 0 !important; padding: 0 !important; }
        /* Add any other print-specific styles here */
        @page { size: auto; margin: 0.5in; } /* Adjust margin for printing */
    </style>

</div>
