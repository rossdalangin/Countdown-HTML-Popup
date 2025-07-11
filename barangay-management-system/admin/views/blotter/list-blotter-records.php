<?php
/**
 * View for Blotter Records List.
 * Variables available:
 * @var array $blotter_records Array of blotter record objects.
 * @var array $statuses Array of unique status strings for filter.
 * @var array $incident_types Array of unique incident type strings for filter.
 * @var array $filter_args Current filter arguments.
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}

$current_status_filter = $filter_args['status'] ?? '';
$current_type_filter = $filter_args['incident_type'] ?? '';
$current_date_from = $filter_args['date_from'] ?? '';
$current_date_to = $filter_args['date_to'] ?? '';
$current_search = $filter_args['search'] ?? '';

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Blotter Records', 'barangay-management-system' ); ?></h1>
    <?php if ( current_user_can( BMS_MANAGE_BLOTTER_CAP ) ) : ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-blotter-add' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New Record', 'barangay-management-system' ); ?>
    </a>
    <?php endif; ?>

    <?php
    if (isset($_GET['record_added']) && $_GET['record_added'] == 'true' && isset($_GET['id'])) {
        echo '<div id="message" class="updated notice is-dismissible"><p>' . sprintf(esc_html__('Blotter record added successfully. %sEdit this record%s.', 'barangay-management-system'), '<a href="' . esc_url(admin_url('admin.php?page=bms-blotter-add&record_id=' . absint($_GET['id']))) . '">', '</a>') . '</p></div>';
    }
    settings_errors();
    ?>

    <form method="get" id="bms-blotter-filter">
        <input type="hidden" name="page" value="bms-blotter" />
        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="status">
                    <option value=""><?php esc_html_e('All Statuses', 'barangay-management-system'); ?></option>
                    <?php foreach ($statuses as $status_option) : ?>
                        <option value="<?php echo esc_attr($status_option); ?>" <?php selected($current_status_filter, $status_option); ?>><?php echo esc_html($status_option); ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="incident_type">
                    <option value=""><?php esc_html_e('All Incident Types', 'barangay-management-system'); ?></option>
                     <?php foreach ($incident_types as $type_option) : ?>
                        <option value="<?php echo esc_attr($type_option); ?>" <?php selected($current_type_filter, $type_option); ?>><?php echo esc_html($type_option); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="date_from" class="screen-reader-text"><?php esc_html_e('Incident Date From:', 'barangay-management-system'); ?></label>
                <input type="date" name="date_from" id="date_from" value="<?php echo esc_attr($current_date_from); ?>">

                <label for="date_to" class="screen-reader-text"><?php esc_html_e('Incident Date To:', 'barangay-management-system'); ?></label>
                <input type="date" name="date_to" id="date_to" value="<?php echo esc_attr($current_date_to); ?>">

                <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'barangay-management-system'); ?>">
            </div>
            <div class="alignleft actions">
                <label for="bms-blotter-search-input" class="screen-reader-text"><?php esc_html_e('Search Blotter', 'barangay-management-system'); ?></label>
                <input type="search" id="bms-blotter-search-input" name="s" value="<?php echo esc_attr($current_search); ?>" placeholder="<?php esc_attr_e('Search case #, names, narrative...', 'barangay-management-system'); ?>">
                <input type="submit" class="button" value="<?php esc_attr_e('Search', 'barangay-management-system'); ?>">
            </div>
            <br class="clear">
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-case-number"><?php esc_html_e( 'Case #', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-incident-date"><?php esc_html_e( 'Incident Date', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-complainant"><?php esc_html_e( 'Complainant(s)', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-respondent"><?php esc_html_e( 'Respondent(s)', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-type"><?php esc_html_e( 'Type', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-status"><?php esc_html_e( 'Status', 'barangay-management-system' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $blotter_records ) ) : ?>
                    <?php foreach ( $blotter_records as $record ) : ?>
                        <tr>
                            <td class="column-case-number">
                                 <?php if ( current_user_can( BMS_MANAGE_BLOTTER_CAP ) ) : ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-blotter-add&record_id=' . $record->id ) ); ?>">
                                    <strong><?php echo esc_html( $record->case_number ? $record->case_number : 'ID: ' . $record->id ); ?></strong>
                                </a>
                                 <?php else: ?>
                                    <strong><?php echo esc_html( $record->case_number ? $record->case_number : 'ID: ' . $record->id ); ?></strong>
                                <?php endif; ?>
                                <?php if ( current_user_can( BMS_MANAGE_BLOTTER_CAP ) ) : ?>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-blotter-add&record_id=' . $record->id ) ); ?>">
                                            <?php esc_html_e( 'Edit/View Details', 'barangay-management-system' ); ?></a> |
                                    </span>
                                    <span class="delete">
                                        <?php
                                        $delete_url = add_query_arg(
                                            array(
                                                'action'    => 'delete_blotter',
                                                'record_id' => $record->id,
                                                '_wpnonce'  => wp_create_nonce( 'bms_delete_blotter_' . $record->id ),
                                            ),
                                            admin_url( 'admin.php?page=bms-blotter' )
                                        );
                                        ?>
                                        <a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this blotter record?', 'barangay-management-system' ); ?>');" style="color:red;">
                                            <?php esc_html_e( 'Delete', 'barangay-management-system' ); ?></a>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="column-incident-date"><?php echo esc_html( gmdate('M j, Y H:i', strtotime($record->incident_date)) ); ?></td>
                            <td class="column-complainant"><?php echo esc_html( $record->complainant_name ); ?></td>
                            <td class="column-respondent"><?php echo esc_html( $record->respondent_name ); ?></td>
                            <td class="column-type"><?php echo esc_html( $record->incident_type ); ?></td>
                            <td class="column-status"><?php echo esc_html( $record->status ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6"><?php esc_html_e( 'No blotter records found for the current filters.', 'barangay-management-system' ); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
    <?php // Add pagination here later ?>
</div>
