<?php
/**
 * Admin pages for Event Management
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Handler for displaying the Events List page.
 */
function bms_events_list_page_handler() {
    if ( ! current_user_can( BMS_VIEW_EVENTS_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'barangay-management-system' ) );
    }

    if ( ! function_exists('bms_get_events') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/event-crud.php';
    }

    // Handle delete action
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete_event' && isset( $_GET['event_id'] ) ) {
        if ( ! current_user_can( BMS_MANAGE_EVENTS_CAP ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to delete events.', 'barangay-management-system' ) );
        }
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bms_delete_event_' . $_GET['event_id'] ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }
        $event_id = absint( $_GET['event_id'] );
        if ( bms_delete_event( $event_id ) ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Event deleted successfully.', 'barangay-management-system' ) . '</p></div>';
            });
        } else {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete event.', 'barangay-management-system' ) . '</p></div>';
            });
        }
    }

    $events_type_filter = $_GET['type'] ?? 'all'; // upcoming, past, all
    $events_args = ['number' => 999];
    if ($events_type_filter === 'upcoming') {
        $events_args['type'] = 'upcoming';
    } elseif ($events_type_filter === 'past') {
        $events_args['type'] = 'past';
    }

    $events = bms_get_events( $events_args );

    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/event/list-events.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/event/list-events.php';
    } else {
        echo '<div class="wrap"><h1>' . esc_html__( 'Events', 'barangay-management-system' ) . '</h1><p>' . esc_html__( 'Error: List view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}

/**
 * Handler for displaying the Add/Edit Event page.
 */
function bms_event_add_edit_page_handler() {
    if ( ! current_user_can( BMS_MANAGE_EVENTS_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to manage events.', 'barangay-management-system' ) );
    }

    if ( ! function_exists('bms_create_event') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/event-crud.php';
    }

    $event_id = isset( $_GET['event_id'] ) ? absint( $_GET['event_id'] ) : 0;
    $event = null;
    $is_editing = false;

    if ( $event_id > 0 ) {
        $event = bms_get_event( $event_id );
        if ($event) {
            $is_editing = true;
        } else {
            wp_die( __( 'Event not found.', 'barangay-management-system' ) );
        }
    }

    if ( isset( $_POST['bms_submit_event'] ) ) {
        if ( ! isset( $_POST['bms_event_nonce'] ) || ! wp_verify_nonce( $_POST['bms_event_nonce'], 'bms_save_event_action' ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }

        $data = array(
            'event_name'        => sanitize_text_field( $_POST['event_name'] ),
            'event_description' => sanitize_textarea_field( $_POST['event_description'] ),
            'event_date'        => sanitize_text_field( $_POST['event_date'] ), // Validate date format
            'event_time'        => sanitize_text_field( $_POST['event_time'] ), // Validate time format
            'event_location'    => sanitize_text_field( $_POST['event_location'] ),
        );

        // Basic date validation
        if (empty($data['event_date']) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['event_date'])) {
             add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Invalid event date format. Please use YYYY-MM-DD.', 'barangay-management-system') . '</p></div>';
            });
        } else {
            $result = false;
            if ( $is_editing && $event_id > 0 ) {
                $result = bms_update_event( $event_id, $data );
                $message = $result ? __( 'Event updated successfully.', 'barangay-management-system' ) : __( 'Failed to update event.', 'barangay-management-system' );
            } else {
                $data['created_by'] = get_current_user_id();
                $new_event_id = bms_create_event( $data );
                $result = $new_event_id !== false;
                $message = $result ? __( 'Event added successfully.', 'barangay-management-system' ) : __( 'Failed to add event.', 'barangay-management-system' );
                if ($result) {
                    wp_redirect( admin_url('admin.php?page=bms-events&event_added=true&id=' . $new_event_id) );
                    exit;
                }
            }
            add_action( 'admin_notices', function() use ( $message, $result ) {
                $notice_type = $result ? 'success' : 'error';
                echo '<div class="notice notice-' . $notice_type . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
            });
            if ($result && $is_editing) {
                $event = bms_get_event( $event_id );
            }
        }
    }

    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/event/add-edit-event.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/event/add-edit-event.php';
    } else {
        $page_title = $is_editing ? __( 'Edit Event', 'barangay-management-system' ) : __( 'Add New Event', 'barangay-management-system' );
        echo '<div class="wrap"><h1>' . esc_html( $page_title ) . '</h1><p>' . esc_html__( 'Error: Form view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}

/**
 * Handler for managing event attendance.
 */
function bms_event_attendance_page_handler() {
    if ( ! current_user_can( BMS_MANAGE_EVENTS_CAP ) ) { // Managing attendance is part of managing events
        wp_die( esc_html__( 'You do not have sufficient permissions to manage event attendance.', 'barangay-management-system' ) );
    }

    if ( ! function_exists('bms_get_event') || ! function_exists('bms_add_event_attendee') || !function_exists('bms_get_residents') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/event-crud.php';
        require_once BMS_PLUGIN_DIR . 'includes/crud/resident-crud.php'; // For listing residents
    }

    $event_id = isset( $_GET['event_id'] ) ? absint( $_GET['event_id'] ) : 0;
    if ( ! $event_id ) {
        wp_die( __( 'No event specified.', 'barangay-management-system' ) );
    }
    $event = bms_get_event( $event_id );
    if ( ! $event ) {
        wp_die( __( 'Event not found.', 'barangay-management-system' ) );
    }

    // Handle adding attendee
    if ( isset( $_POST['bms_add_attendee'] ) && isset( $_POST['resident_id'] ) ) {
        if ( ! isset( $_POST['bms_attendance_nonce'] ) || ! wp_verify_nonce( $_POST['bms_attendance_nonce'], 'bms_add_attendee_action_' . $event_id ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }
        $resident_id = absint( $_POST['resident_id'] );
        $notes = sanitize_text_field( $_POST['attendance_notes'] ?? '' );
        if ( bms_add_event_attendee( $event_id, $resident_id, ['notes' => $notes] ) ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Attendee added successfully.', 'barangay-management-system' ) . '</p></div>';
            });
        } else {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to add attendee. They might already be on the list.', 'barangay-management-system' ) . '</p></div>';
            });
        }
    }

    // Handle removing attendee
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'remove_attendee' && isset( $_GET['resident_id'] ) ) {
         if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bms_remove_attendee_' . $event_id . '_' . $_GET['resident_id'] ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }
        $resident_id_to_remove = absint( $_GET['resident_id'] );
        if ( bms_remove_event_attendee( $event_id, $resident_id_to_remove ) ) {
             add_action( 'admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Attendee removed successfully.', 'barangay-management-system' ) . '</p></div>';
            });
        } else {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to remove attendee.', 'barangay-management-system' ) . '</p></div>';
            });
        }
    }

    $attendees = bms_get_event_attendees( $event_id, ['number' => 9999] ); // Get all for now
    $all_residents = bms_get_residents( ['number' => 9999, 'orderby' => 'last_name', 'order' => 'ASC'] ); // For dropdown

    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/event/manage-event-attendance.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/event/manage-event-attendance.php';
    } else {
        echo '<div class="wrap"><h1>' . sprintf(esc_html__('Manage Attendance for %s', 'barangay-management-system'), esc_html($event->event_name)) . '</h1><p>' . esc_html__( 'Error: Attendance view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}
?>
