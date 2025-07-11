<?php
/**
 * View for Events List.
 * Variables available:
 * @var array $events Array of event objects.
 * @var string $events_type_filter Current filter ('all', 'upcoming', 'past').
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Events', 'barangay-management-system' ); ?></h1>
    <?php if ( current_user_can( BMS_MANAGE_EVENTS_CAP ) ) : ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-event-add' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New Event', 'barangay-management-system' ); ?>
    </a>
    <?php endif; ?>

    <?php
    if (isset($_GET['event_added']) && $_GET['event_added'] == 'true' && isset($_GET['id'])) {
        echo '<div id="message" class="updated notice is-dismissible"><p>' . sprintf(esc_html__('Event added successfully. %sEdit this event%s or %smanage attendance%s.', 'barangay-management-system'), '<a href="' . esc_url(admin_url('admin.php?page=bms-event-add&event_id=' . absint($_GET['id']))) . '">', '</a>', '<a href="' . esc_url(admin_url('admin.php?page=bms-event-attendance&event_id=' . absint($_GET['id']))) . '">', '</a>') . '</p></div>';
    }
    settings_errors();
    ?>

    <ul class="subsubsub">
		<li><a href="<?php echo esc_url(admin_url('admin.php?page=bms-events&type=all')); ?>" class="<?php echo $events_type_filter === 'all' ? 'current' : ''; ?>"><?php esc_html_e('All', 'barangay-management-system'); ?></a> |</li>
		<li><a href="<?php echo esc_url(admin_url('admin.php?page=bms-events&type=upcoming')); ?>" class="<?php echo $events_type_filter === 'upcoming' ? 'current' : ''; ?>"><?php esc_html_e('Upcoming', 'barangay-management-system'); ?></a> |</li>
		<li><a href="<?php echo esc_url(admin_url('admin.php?page=bms-events&type=past')); ?>" class="<?php echo $events_type_filter === 'past' ? 'current' : ''; ?>"><?php esc_html_e('Past', 'barangay-management-system'); ?></a></li>
	</ul>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name"><?php esc_html_e( 'Event Name', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-date"><?php esc_html_e( 'Date', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-time"><?php esc_html_e( 'Time', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-location"><?php esc_html_e( 'Location', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-attendees"><?php esc_html_e( 'Attendees', 'barangay-management-system' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $events ) ) : ?>
                <?php foreach ( $events as $event ) : ?>
                    <?php $attendee_count = bms_count_event_attendees($event->id); ?>
                    <tr>
                        <td class="column-name">
                            <strong>
                                <?php if ( current_user_can( BMS_MANAGE_EVENTS_CAP ) ) : ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-event-add&event_id=' . $event->id ) ); ?>">
                                    <?php echo esc_html( $event->event_name ); ?>
                                </a>
                                <?php else: ?>
                                     <?php echo esc_html( $event->event_name ); ?>
                                <?php endif; ?>
                            </strong>
                            <div class="row-actions">
                                <?php if ( current_user_can( BMS_MANAGE_EVENTS_CAP ) ) : ?>
                                <span class="edit">
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-event-add&event_id=' . $event->id ) ); ?>">
                                        <?php esc_html_e( 'Edit', 'barangay-management-system' ); ?></a> |
                                </span>
                                <span class="attendance">
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-event-attendance&event_id=' . $event->id ) ); ?>">
                                        <?php esc_html_e( 'Manage Attendance', 'barangay-management-system' ); ?></a> |
                                </span>
                                <span class="delete">
                                    <?php
                                    $delete_url = add_query_arg(
                                        array(
                                            'action'    => 'delete_event',
                                            'event_id'  => $event->id,
                                            '_wpnonce'  => wp_create_nonce( 'bms_delete_event_' . $event->id ),
                                        ),
                                        admin_url( 'admin.php?page=bms-events' )
                                    );
                                    ?>
                                    <a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this event and all its attendance records?', 'barangay-management-system' ); ?>');" style="color:red;">
                                        <?php esc_html_e( 'Delete', 'barangay-management-system' ); ?></a>
                                </span>
                                <?php elseif ( current_user_can( BMS_VIEW_EVENTS_CAP ) ) :?>
                                 <span class="attendance">
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-event-attendance&event_id=' . $event->id ) ); ?>">
                                        <?php esc_html_e( 'View Attendance', 'barangay-management-system' ); ?></a>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="column-date"><?php echo esc_html( gmdate('M j, Y', strtotime($event->event_date)) ); ?></td>
                        <td class="column-time"><?php echo esc_html( $event->event_time ? gmdate('g:i A', strtotime($event->event_time)) : 'N/A' ); ?></td>
                        <td class="column-location"><?php echo esc_html( $event->event_location ? $event->event_location : 'N/A'); ?></td>
                        <td class="column-attendees">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-event-attendance&event_id=' . $event->id ) ); ?>">
                                <?php echo esc_html($attendee_count); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5"><?php esc_html_e( 'No events found.', 'barangay-management-system' ); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
