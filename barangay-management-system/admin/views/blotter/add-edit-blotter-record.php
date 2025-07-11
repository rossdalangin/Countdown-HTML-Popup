<?php
/**
 * View for Add/Edit Blotter Record form.
 * Variables available:
 * @var bool $is_editing True if editing, false if adding.
 * @var ?object $record The blotter record object if editing.
 * @var int $record_id The ID of the record being edited, or 0.
 * @var array $blotter_statuses Possible statuses.
 * @var array $blotter_incident_types Possible incident types for datalist.
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}

$case_number = $record->case_number ?? ($is_editing ? '' : bms_generate_blotter_case_number());
$complainant_name = $record->complainant_name ?? '';
$complainant_address = $record->complainant_address ?? '';
$complainant_contact = $record->complainant_contact ?? '';
$respondent_name = $record->respondent_name ?? '';
$respondent_address = $record->respondent_address ?? '';
$respondent_contact = $record->respondent_contact ?? '';
$incident_type = $record->incident_type ?? '';
// For datetime-local input, format is YYYY-MM-DDTHH:MM
$incident_date = isset($record->incident_date) ? gmdate('Y-m-d\TH:i', strtotime($record->incident_date)) : current_time('Y-m-d\TH:i');
$incident_location = $record->incident_location ?? '';
$incident_narrative = $record->incident_narrative ?? '';
$status = $record->status ?? 'Open';
$assigned_official_name = $record->assigned_official_name ?? '';
$resolution_details = $record->resolution_details ?? '';
$date_reported = isset($record->date_reported) ? gmdate('M j, Y H:i', strtotime($record->date_reported)) : current_time('M j, Y H:i');
$recorded_by_user = isset($record->recorded_by) && $record->recorded_by ? get_userdata($record->recorded_by) : null;
$recorded_by_name = $recorded_by_user ? $recorded_by_user->display_name : (get_current_user_id() && !$is_editing ? wp_get_current_user()->display_name : 'N/A');


$page_title = $is_editing ? sprintf(esc_html__('Edit Blotter Record #%s', 'barangay-management-system'), esc_html($case_number)) : esc_html__('Add New Blotter Record', 'barangay-management-system');
$submit_button_text = $is_editing ? __( 'Update Record', 'barangay-management-system' ) : __( 'Add Record', 'barangay-management-system' );

// Predefined statuses if not fetched from DB or to ensure all are available
if (empty($blotter_statuses) || !is_array($blotter_statuses)) {
    $blotter_statuses = ['Open', 'Under Investigation', 'Amicably Settled', 'Endorsed to PNP', 'Closed', 'Dismissed'];
}


?>
<div class="wrap">
    <h1><?php echo $page_title; ?></h1>
    <?php settings_errors(); ?>

    <form method="POST" action="">
        <?php wp_nonce_field( 'bms_save_blotter_action', 'bms_blotter_nonce' ); ?>
        <input type="hidden" name="record_id" value="<?php echo esc_attr( $record_id ); ?>">

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <!-- Main content -->
                <div id="post-body-content">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Incident Details', 'barangay-management-system'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="incident_date"><?php esc_html_e( 'Date and Time of Incident', 'barangay-management-system' ); ?></label></th>
                                    <td><input name="incident_date" type="datetime-local" id="incident_date" value="<?php echo esc_attr( $incident_date ); ?>" required></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="incident_location"><?php esc_html_e( 'Location of Incident', 'barangay-management-system' ); ?></label></th>
                                    <td><input name="incident_location" type="text" id="incident_location" value="<?php echo esc_attr( $incident_location ); ?>" class="large-text" required></td>
                                </tr>
                                 <tr>
                                    <th scope="row"><label for="incident_type"><?php esc_html_e( 'Type of Incident', 'barangay-management-system' ); ?></label></th>
                                    <td>
                                        <input name="incident_type" type="text" id="incident_type" value="<?php echo esc_attr( $incident_type ); ?>" class="regular-text" list="bms-incident-types">
                                        <?php if (!empty($blotter_incident_types)) : ?>
                                        <datalist id="bms-incident-types">
                                            <?php foreach ($blotter_incident_types as $type_option) : ?>
                                                <option value="<?php echo esc_attr($type_option); ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                        <?php endif; ?>
                                         <p class="description"><?php esc_html_e('E.g., Theft, Disturbance, Property Damage, Physical Injury.', 'barangay-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="incident_narrative"><?php esc_html_e( 'Narrative of Incident', 'barangay-management-system' ); ?></label></th>
                                    <td><textarea name="incident_narrative" id="incident_narrative" rows="8" class="large-text" required><?php echo esc_textarea( $incident_narrative ); ?></textarea></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Parties Involved', 'barangay-management-system'); ?></span></h2>
                        <div class="inside">
                             <h3><?php esc_html_e('Complainant(s)', 'barangay-management-system'); ?></h3>
                             <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="complainant_name"><?php esc_html_e( 'Name(s)', 'barangay-management-system' ); ?></label></th>
                                    <td><input name="complainant_name" type="text" id="complainant_name" value="<?php echo esc_attr( $complainant_name ); ?>" class="large-text" required>
                                    <p class="description"><?php esc_html_e('Separate multiple names with a comma.', 'barangay-management-system'); ?></p></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="complainant_address"><?php esc_html_e( 'Address', 'barangay-management-system' ); ?></label></th>
                                    <td><input name="complainant_address" type="text" id="complainant_address" value="<?php echo esc_attr( $complainant_address ); ?>" class="large-text"></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="complainant_contact"><?php esc_html_e( 'Contact Info', 'barangay-management-system' ); ?></label></th>
                                    <td><input name="complainant_contact" type="text" id="complainant_contact" value="<?php echo esc_attr( $complainant_contact ); ?>" class="regular-text"></td>
                                </tr>
                            </table>
                            <hr>
                            <h3><?php esc_html_e('Respondent(s)', 'barangay-management-system'); ?></h3>
                             <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="respondent_name"><?php esc_html_e( 'Name(s)', 'barangay-management-system' ); ?></label></th>
                                    <td><input name="respondent_name" type="text" id="respondent_name" value="<?php echo esc_attr( $respondent_name ); ?>" class="large-text" required>
                                     <p class="description"><?php esc_html_e('Separate multiple names with a comma.', 'barangay-management-system'); ?></p></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="respondent_address"><?php esc_html_e( 'Address', 'barangay-management-system' ); ?></label></th>
                                    <td><input name="respondent_address" type="text" id="respondent_address" value="<?php echo esc_attr( $respondent_address ); ?>" class="large-text"></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="respondent_contact"><?php esc_html_e( 'Contact Info', 'barangay-management-system' ); ?></label></th>
                                    <td><input name="respondent_contact" type="text" id="respondent_contact" value="<?php echo esc_attr( $respondent_contact ); ?>" class="regular-text"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                     <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Resolution and Actions', 'barangay-management-system'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                 <tr>
                                    <th scope="row"><label for="assigned_official_name"><?php esc_html_e( 'Assigned Official (Optional)', 'barangay-management-system' ); ?></label></th>
                                    <td><input name="assigned_official_name" type="text" id="assigned_official_name" value="<?php echo esc_attr( $assigned_official_name ); ?>" class="regular-text"></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="resolution_details"><?php esc_html_e( 'Resolution/Action Taken (Optional)', 'barangay-management-system' ); ?></label></th>
                                    <td><textarea name="resolution_details" id="resolution_details" rows="5" class="large-text"><?php echo esc_textarea( $resolution_details ); ?></textarea></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div> <!-- /post-body-content -->

                <!-- Sidebar -->
                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php esc_html_e('Record Status', 'barangay-management-system'); ?></span></h2>
                        <div class="inside">
                            <p>
                                <label for="case_number"><strong><?php esc_html_e( 'Case Number', 'barangay-management-system' ); ?></strong></label><br>
                                <input name="case_number" type="text" id="case_number" value="<?php echo esc_attr( $case_number ); ?>" class="widefat">
                                <small><?php esc_html_e('If empty, will be auto-generated on save for new records.', 'barangay-management-system');?></small>
                            </p>
                            <p>
                                <label for="status"><strong><?php esc_html_e( 'Status', 'barangay-management-system' ); ?></strong></label><br>
                                <select name="status" id="status" class="widefat" required>
                                    <?php foreach ($blotter_statuses as $status_val) : ?>
                                    <option value="<?php echo esc_attr($status_val); ?>" <?php selected($status, $status_val); ?>><?php echo esc_html($status_val); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </p>
                             <p>
                                <strong><?php esc_html_e( 'Date Reported:', 'barangay-management-system' ); ?></strong><br>
                                <?php echo esc_html($date_reported); ?>
                            </p>
                            <p>
                                <strong><?php esc_html_e( 'Recorded By:', 'barangay-management-system' ); ?></strong><br>
                                <?php echo esc_html($recorded_by_name); ?>
                            </p>
                        </div>
                        <div id="major-publishing-actions">
                            <div id="publishing-action">
                                <?php submit_button( $submit_button_text, 'primary large', 'bms_submit_blotter', false ); // false to not echo ?>
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div> <!-- /postbox-container-1 -->
            </div><!-- /post-body -->
            <br class="clear">
        </div><!-- /poststuff -->
    </form>
</div>
