<?php
/**
 * View for Document Templates List.
 * Variables available:
 * @var array $templates Array of template objects.
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Document Templates', 'barangay-management-system' ); ?></h1>
    <?php if ( current_user_can( BMS_MANAGE_DOCUMENT_TEMPLATES_CAP ) ) : ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-doc-template-add' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New Template', 'barangay-management-system' ); ?>
    </a>
    <?php endif; ?>

    <?php
    if (isset($_GET['template_added']) && $_GET['template_added'] == 'true' && isset($_GET['id'])) {
        echo '<div id="message" class="updated notice is-dismissible"><p>' . sprintf(esc_html__('Template added successfully. %sEdit this template%s.', 'barangay-management-system'), '<a href="' . esc_url(admin_url('admin.php?page=bms-doc-template-add&template_id=' . absint($_GET['id']))) . '">', '</a>') . '</p></div>';
    }
    settings_errors();
    ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name"><?php esc_html_e( 'Template Name', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-placeholders"><?php esc_html_e( 'Defined Placeholders', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-created"><?php esc_html_e( 'Date Created', 'barangay-management-system' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $templates ) ) : ?>
                <?php foreach ( $templates as $template ) : ?>
                    <tr>
                        <td class="column-name">
                            <strong>
                                <?php if ( current_user_can( BMS_MANAGE_DOCUMENT_TEMPLATES_CAP ) ) : ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-doc-template-add&template_id=' . $template->id ) ); ?>">
                                    <?php echo esc_html( $template->template_name ); ?>
                                </a>
                                <?php else: ?>
                                    <?php echo esc_html( $template->template_name ); ?>
                                <?php endif; ?>
                            </strong>
                            <div class="row-actions">
                                <?php if ( current_user_can( BMS_MANAGE_DOCUMENT_TEMPLATES_CAP ) ) : ?>
                                <span class="edit">
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-doc-template-add&template_id=' . $template->id ) ); ?>">
                                        <?php esc_html_e( 'Edit', 'barangay-management-system' ); ?></a> |
                                </span>
                                <span class="delete">
                                    <?php
                                    $delete_url = add_query_arg(
                                        array(
                                            'action'      => 'delete_template',
                                            'template_id' => $template->id,
                                            '_wpnonce'    => wp_create_nonce( 'bms_delete_template_' . $template->id ),
                                        ),
                                        admin_url( 'admin.php?page=bms-doc-templates' )
                                    );
                                    ?>
                                    <a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this template?', 'barangay-management-system' ); ?>');" style="color:red;">
                                        <?php esc_html_e( 'Delete', 'barangay-management-system' ); ?></a> |
                                </span>
                                <?php endif; ?>
                                 <?php if ( current_user_can( BMS_ISSUE_DOCUMENTS_CAP ) ) : ?>
                                <span class="issue">
                                    <?php
                                    // Link to select a resident first, then issue. Or a direct issue link if resident context is available.
                                    // For now, let's assume we'll need to select a resident. This link is a placeholder.
                                    // A better UX would be an "Issue" button that leads to a resident selection modal/page.
                                    $issue_url = admin_url( 'admin.php?page=bms-residents&action=select_for_document&template_id=' . $template->id );
                                    // For demonstration, a direct link to issue page (needs resident_id)
                                    // $issue_url_direct = admin_url( 'admin.php?page=bms-issue-document&template_id=' . $template->id . '&resident_id=RESIDENT_ID_HERE' );
                                    ?>
                                    <a href="<?php echo esc_url($issue_url); // Placeholder for better UX later ?>">
                                        <?php esc_html_e( 'Issue This Document', 'barangay-management-system' ); ?>
                                    </a>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="column-placeholders">
                            <?php
                            $placeholders = !empty($template->defined_placeholders) ? json_decode($template->defined_placeholders, true) : [];
                            if (is_array($placeholders) && !empty($placeholders)) {
                                echo esc_html(implode(', ', $placeholders));
                            } elseif (!empty($template->defined_placeholders)) {
                                echo esc_html($template->defined_placeholders); // If not JSON, display as is
                            } else {
                                esc_html_e('N/A', 'barangay-management-system');
                            }
                            ?>
                        </td>
                        <td class="column-created"><?php echo esc_html( gmdate('M j, Y H:i', strtotime($template->created_at)) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="3"><?php esc_html_e( 'No document templates found.', 'barangay-management-system' ); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
