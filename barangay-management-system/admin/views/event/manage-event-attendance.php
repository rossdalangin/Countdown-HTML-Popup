<?php
/**
 * View for Managing Event Attendance.
 * Variables available:
 * @var object $event The event object.
 * @var array $attendees Array of resident objects who are attending.
 * @var array $all_residents Array of all resident objects for the dropdown.
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wrap">
    <h1><?php printf(esc_html__('Manage Attendance: %s', 'barangay-management-system'), esc_html($event->event_name)); ?></h1>
    <p><strong><?php esc_html_e('Date:', 'barangay-management-system'); ?></strong> <?php echo esc_html(gmdate('M j, Y', strtotime($event->event_date))); ?>
    <?php if ($event->event_time) echo ' | <strong>' . esc_html__('Time:', 'barangay-management-system') . '</strong> ' . esc_html(gmdate('g:i A', strtotime($event->event_time))); ?>
    <?php if ($event->event_location) echo ' | <strong>' . esc_html__('Location:', 'barangay-management-system') . '</strong> ' . esc_html($event->event_location); ?>
    </p>

    <?php settings_errors(); ?>

    <?php if ( current_user_can( BMS_MANAGE_EVENTS_CAP ) ) : ?>
    <div id="add-attendee-form" style="margin-bottom:20px; padding:15px; border:1px solid #ccc; background-color:#f9f9f9;">
        <h2><?php esc_html_e('Add Attendee', 'barangay-management-system'); ?></h2>
        <form method="POST" action="<?php echo esc_url(admin_url('admin.php?page=bms-event-attendance&event_id=' . $event->id)); ?>">
            <?php wp_nonce_field( 'bms_add_attendee_action_' . $event->id, 'bms_attendance_nonce' ); ?>
            <input type="hidden" name="event_id" value="<?php echo esc_attr($event->id); ?>">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="resident_id"><?php esc_html_e('Select Resident', 'barangay-management-system'); ?></label></th>
                    <td>
                        <select name="resident_id" id="resident_id" required style="width:300px;">
                            <option value=""><?php esc_html_e('-- Select Resident --', 'barangay-management-system'); ?></option>
                            <?php foreach ($all_residents as $resident_option) : ?>
                                <?php
                                // Check if this resident is already an attendee to optionally disable or skip
                                $is_already_attending = false;
                                foreach ($attendees as $current_attendee) {
                                    if ($current_attendee->id == $resident_option->id) {
                                        $is_already_attending = true;
                                        break;
                                    }
                                }
                                ?>
                                <option value="<?php echo esc_attr($resident_option->id); ?>" <?php disabled($is_already_attending); ?>>
                                    <?php echo esc_html(trim(sprintf('%s, %s %s', $resident_option->last_name, $resident_option->first_name, $resident_option->middle_name))); ?>
                                    <?php if($is_already_attending) echo ' (' . esc_html__('Already attending', 'barangay-management-system') . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="attendance_notes"><?php esc_html_e('Notes (Optional)', 'barangay-management-system'); ?></label></th>
                    <td><input type="text" name="attendance_notes" id="attendance_notes" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button( __( 'Add Attendee', 'barangay-management-system' ), 'primary', 'bms_add_attendee' ); ?>
        </form>
    </div>
    <?php endif; // BMS_MANAGE_EVENTS_CAP ?>

    <h2><?php esc_html_e('Current Attendees', 'barangay-management-system'); ?> (<?php echo count($attendees); ?>)</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name"><?php esc_html_e( 'Resident Name', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-contact"><?php esc_html_e( 'Contact', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-attended-at"><?php esc_html_e( 'Time Logged', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-notes"><?php esc_html_e( 'Notes', 'barangay-management-system' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $attendees ) ) : ?>
                <?php foreach ( $attendees as $attendee ) : // These are resident objects with attendance_notes, attendance_id, attended_at added ?>
                    <tr>
                        <td class="column-name">
                            <strong>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-resident-add&resident_id=' . $attendee->id ) ); ?>">
                                    <?php echo esc_html( trim(sprintf( '%s, %s %s', $attendee->last_name, $attendee->first_name, $attendee->middle_name )) ); ?>
                                </a>
                            </strong>
                             <?php if ( current_user_can( BMS_MANAGE_EVENTS_CAP ) ) : ?>
                            <div class="row-actions">
                                <span class="delete">
                                    <?php
                                    $remove_url = add_query_arg(
                                        array(
                                            'action'      => 'remove_attendee',
                                            'event_id'    => $event->id,
                                            'resident_id' => $attendee->id,
                                            '_wpnonce'    => wp_create_nonce( 'bms_remove_attendee_' . $event->id . '_' . $attendee->id ),
                                        ),
                                        admin_url( 'admin.php?page=bms-event-attendance' )
                                    );
                                    ?>
                                    <a href="<?php echo esc_url( $remove_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to remove this attendee?', 'barangay-management-system' ); ?>');" style="color:red;">
                                        <?php esc_html_e( 'Remove', 'barangay-management-system' ); ?>
                                    </a>
                                </span>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="column-contact">
                            <?php if ( $attendee->contact_phone ) echo esc_html( $attendee->contact_phone ) . '<br>'; ?>
                            <?php if ( $attendee->contact_email ) echo esc_html( $attendee->contact_email ); ?>
                        </td>
                        <td class="column-attended-at"><?php echo esc_html( $attendee->attended_at ? gmdate('M j, Y g:i A', strtotime($attendee->attended_at)) : 'N/A'); ?></td>
                        <td class="column-notes"><?php echo esc_html( $attendee->attendance_notes ? $attendee->attendance_notes : ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4"><?php esc_html_e( 'No attendees recorded for this event yet.', 'barangay-management-system' ); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
