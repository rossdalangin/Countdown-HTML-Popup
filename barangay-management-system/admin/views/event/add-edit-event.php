<?php
/**
 * View for Add/Edit Event form.
 * Variables available:
 * @var bool $is_editing True if editing, false if adding.
 * @var ?object $event The event object if editing.
 * @var int $event_id The ID of the event being edited, or 0.
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}

$event_name = $event->event_name ?? '';
$event_description = $event->event_description ?? '';
$event_date = isset($event->event_date) ? gmdate('Y-m-d', strtotime($event->event_date)) : '';
$event_time = isset($event->event_time) ? gmdate('H:i', strtotime($event->event_time)) : ''; // Format for input type="time"
$event_location = $event->event_location ?? '';

$page_title = $is_editing ? __( 'Edit Event', 'barangay-management-system' ) : __( 'Add New Event', 'barangay-management-system' );
$submit_button_text = $is_editing ? __( 'Update Event', 'barangay-management-system' ) : __( 'Add Event', 'barangay-management-system' );
?>
<div class="wrap">
    <h1><?php echo esc_html( $page_title ); ?></h1>
    <?php settings_errors(); ?>

    <form method="POST" action="">
        <?php wp_nonce_field( 'bms_save_event_action', 'bms_event_nonce' ); ?>
        <input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="event_name"><?php esc_html_e( 'Event Name', 'barangay-management-system' ); ?></label></th>
                    <td><input name="event_name" type="text" id="event_name" value="<?php echo esc_attr( $event_name ); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="event_description"><?php esc_html_e( 'Description', 'barangay-management-system' ); ?></label></th>
                    <td><textarea name="event_description" id="event_description" rows="5" class="large-text"><?php echo esc_textarea( $event_description ); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="event_date"><?php esc_html_e( 'Date', 'barangay-management-system' ); ?></label></th>
                    <td><input name="event_date" type="date" id="event_date" value="<?php echo esc_attr( $event_date ); ?>" class="regular-text" required>
                         <p class="description"><?php esc_html_e('Format: YYYY-MM-DD', 'barangay-management-system'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="event_time"><?php esc_html_e( 'Time (Optional)', 'barangay-management-system' ); ?></label></th>
                    <td><input name="event_time" type="time" id="event_time" value="<?php echo esc_attr( $event_time ); ?>" class="regular-text"></td>
                </tr>
                 <tr>
                    <th scope="row"><label for="event_location"><?php esc_html_e( 'Location (Optional)', 'barangay-management-system' ); ?></label></th>
                    <td><input name="event_location" type="text" id="event_location" value="<?php echo esc_attr( $event_location ); ?>" class="regular-text"></td>
                </tr>
            </tbody>
        </table>

        <?php submit_button( $submit_button_text, 'primary', 'bms_submit_event' ); ?>
    </form>
</div>
