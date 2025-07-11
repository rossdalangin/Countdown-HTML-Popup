<?php
/**
 * View for Certificate Requests List.
 * Variables available:
 * @var array $requests Array of request objects (joined with resident and template names).
 * @var array $statuses Array of unique status strings for filter.
 * @var array $document_templates Array of document template objects for filter.
 * @var array $filter_args Current filter arguments.
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}

$current_status_filter = $filter_args['status'] ?? '';
$current_date_from = $filter_args['date_from'] ?? '';
$current_date_to = $filter_args['date_to'] ?? '';
$current_search_res = $filter_args['search_resident'] ?? '';
$current_search_tpl = $filter_args['search_template'] ?? '';

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Certificate Requests', 'barangay-management-system' ); ?></h1>
    <?php /* No "Add New" button here as requests come from residents primarily */ ?>

    <?php settings_errors(); ?>

    <form method="get" id="bms-cert-requests-filter">
        <input type="hidden" name="page" value="bms-cert-requests" />
        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="status">
                    <option value=""><?php esc_html_e('All Statuses', 'barangay-management-system'); ?></option>
                    <?php foreach ($statuses as $status_option) : ?>
                        <option value="<?php echo esc_attr($status_option); ?>" <?php selected($current_status_filter, $status_option); ?>><?php echo esc_html($status_option); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="date_from" class="screen-reader-text"><?php esc_html_e('Request Date From:', 'barangay-management-system'); ?></label>
                <input type="date" name="date_from" id="date_from" value="<?php echo esc_attr($current_date_from); ?>">

                <label for="date_to" class="screen-reader-text"><?php esc_html_e('Request Date To:', 'barangay-management-system'); ?></label>
                <input type="date" name="date_to" id="date_to" value="<?php echo esc_attr($current_date_to); ?>">

                <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'barangay-management-system'); ?>">
            </div>
            <div class="alignleft actions">
                <input type="search" name="s_res" value="<?php echo esc_attr($current_search_res); ?>" placeholder="<?php esc_attr_e('Search Resident...', 'barangay-management-system'); ?>">
                <input type="search" name="s_tpl" value="<?php echo esc_attr($current_search_tpl); ?>" placeholder="<?php esc_attr_e('Search Certificate Type...', 'barangay-management-system'); ?>">
                <input type="submit" class="button" value="<?php esc_attr_e('Search', 'barangay-management-system'); ?>">
            </div>
            <br class="clear">
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-request-id"><?php esc_html_e( 'ID', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-resident"><?php esc_html_e( 'Resident', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-certificate"><?php esc_html_e( 'Certificate Type', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-purpose"><?php esc_html_e( 'Purpose', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-request-date"><?php esc_html_e( 'Date Requested', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-status"><?php esc_html_e( 'Status', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-payment"><?php esc_html_e( 'Payment', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-actions" style="width:20%;"><?php esc_html_e( 'Actions', 'barangay-management-system' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $requests ) ) : ?>
                    <?php foreach ( $requests as $request ) : ?>
                        <tr>
                            <td class="column-request-id"><?php echo esc_html( $request->id ); ?></td>
                            <td class="column-resident">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=bms-resident-add&resident_id=' . $request->resident_id)); ?>">
                                    <?php echo esc_html( $request->resident_full_name ); ?>
                                </a>
                            </td>
                            <td class="column-certificate"><?php echo esc_html( $request->template_name ); ?></td>
                            <td class="column-purpose"><?php echo esc_html( wp_trim_words($request->purpose, 10, '...') ); ?></td>
                            <td class="column-request-date"><?php echo esc_html( gmdate('M j, Y H:i', strtotime($request->request_date)) ); ?></td>
                            <td class="column-status">
                                <?php echo esc_html( $request->status ); ?>
                                <?php if($request->notes_admin): ?>
                                    <p class="description"><em><?php esc_html_e('Admin Note:', 'barangay-management-system');?> <?php echo esc_html($request->notes_admin);?></em></p>
                                <?php endif; ?>
                            </td>
                            <td class="column-payment">
                                <?php echo esc_html( $request->payment_status ); ?>
                                <?php if($request->payment_details): ?>
                                    <p class="description"><em><?php echo esc_html($request->payment_details);?></em></p>
                                <?php endif; ?>
                            </td>
                            <td class="column-actions">
                                <?php
                                $base_action_url = admin_url('admin.php?page=bms-cert-requests&request_id=' . $request->id);
                                $nonce_url_part = '&_wpnonce=' . wp_create_nonce('bms_cert_request_action_' . $request->id);
                                ?>
                                <?php if ($request->status === 'Pending'): ?>
                                    <a href="<?php echo esc_url($base_action_url . '&action=approve_request' . $nonce_url_part); ?>" class="button button-small"><?php esc_html_e('Approve', 'barangay-management-system'); ?></a>
                                    <a href="<?php echo esc_url($base_action_url . '&action=reject_request' . $nonce_url_part); ?>" class="button button-small button-secondary"><?php esc_html_e('Reject', 'barangay-management-system'); ?></a>
                                <?php elseif ($request->status === 'Approved'): ?>
                                    <a href="<?php echo esc_url($base_action_url . '&action=generate_cert' . $nonce_url_part); ?>" class="button button-small button-primary"><?php esc_html_e('Generate Certificate', 'barangay-management-system'); ?></a>
                                <?php elseif ($request->status === 'Processing' && $request->issued_document_id): ?>
                                     <a href="<?php echo esc_url(admin_url('admin.php?page=bms-view-issued-document&issued_doc_id=' . $request->issued_document_id)); ?>" class="button button-small"><?php esc_html_e('View Generated', 'barangay-management-system'); ?></a>
                                     <a href="<?php echo esc_url($base_action_url . '&action=mark_ready' . $nonce_url_part); ?>" class="button button-small"><?php esc_html_e('Mark Ready', 'barangay-management-system'); ?></a>
                                <?php elseif ($request->status === 'Ready for Pickup'): ?>
                                    <a href="<?php echo esc_url($base_action_url . '&action=mark_claimed' . $nonce_url_part); ?>" class="button button-small button-primary"><?php esc_html_e('Mark Claimed', 'barangay-management-system'); ?></a>
                                <?php endif; ?>

                                <?php if ($request->payment_status === 'Unpaid' && in_array($request->status, ['Pending', 'Approved', 'Processing', 'Ready for Pickup'])): ?>
                                    <a href="<?php echo esc_url($base_action_url . '&action=mark_paid' . $nonce_url_part); ?>" class="button button-small" style="margin-top:5px;"><?php esc_html_e('Mark Paid', 'barangay-management-system'); ?></a>
                                <?php endif; ?>
                                <br>
                                <!-- A more generic "Update Status" or "Edit Details" could link to a dedicated page if needed -->
                                <!-- <a href="#" class="button button-small">Details/Update</a> -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8"><?php esc_html_e( 'No certificate requests found for the current filters.', 'barangay-management-system' ); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
    <?php // Add pagination here later ?>
</div>
