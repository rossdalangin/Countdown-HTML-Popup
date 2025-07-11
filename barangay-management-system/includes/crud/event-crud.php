<?php
/**
 * Event & Event Attendance CRUD Functions
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Get the events table name.
 */
function bms_get_events_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'bms_events';
}

/**
 * Get the event attendance table name.
 */
function bms_get_event_attendance_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'bms_event_attendance';
}

/*
 * ==========================================================================
 * Event CRUD Functions
 * ==========================================================================
 */

/**
 * Create a new event.
 * @param array $data Event data. Expected keys: 'event_name', 'event_date', etc.
 * @return int|false Event ID on success, false on failure.
 */
function bms_create_event( array $data ) {
    global $wpdb;
    $table_name = bms_get_events_table_name();

    if ( empty( $data['event_name'] ) || empty( $data['event_date'] ) ) {
        return false;
    }

    $defaults = array(
        'event_name'        => '',
        'event_description' => '',
        'event_date'        => '', // YYYY-MM-DD
        'event_time'        => null, // HH:MM:SS or HH:MM
        'event_location'    => '',
        'created_by'        => get_current_user_id(),
    );
    $data = shortcode_atts( $defaults, $data );

    // Ensure date is in correct format
    $data['event_date'] = gmdate('Y-m-d', strtotime($data['event_date']));
    if (!empty($data['event_time'])) {
        $data['event_time'] = gmdate('H:i:s', strtotime($data['event_time']));
    } else {
        $data['event_time'] = null; // Store as NULL if empty
    }


    $result = $wpdb->insert( $table_name, $data );
    return $result ? $wpdb->insert_id : false;
}

/**
 * Get a single event by ID.
 * @param int $event_id Event ID.
 * @return object|null Event object or null if not found.
 */
function bms_get_event( int $event_id ) {
    global $wpdb;
    $table_name = bms_get_events_table_name();
    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $event_id ) );
}

/**
 * Get a list of events.
 * @param array $args Arguments for filtering (e.g., 'type' => 'upcoming'/'past', 'number', 'offset').
 * @return array Array of event objects.
 */
function bms_get_events( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_events_table_name();
    $current_date = current_time('Y-m-d');

    $defaults = array(
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'event_date',
        'order'   => 'DESC',
        'search'  => '',
        'type'    => '', // 'upcoming', 'past', or empty for all
    );
    $args = wp_parse_args( $args, $defaults );

    $sql = "SELECT * FROM {$table_name}";
    $where_clauses = array();

    if ( ! empty( $args['search'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        $where_clauses[] = $wpdb->prepare( "(event_name LIKE %s OR event_description LIKE %s OR event_location LIKE %s)", $search_term, $search_term, $search_term );
    }

    if ( $args['type'] === 'upcoming' ) {
        $where_clauses[] = $wpdb->prepare( "event_date >= %s", $current_date );
        $args['order'] = 'ASC'; // Typically upcoming events are shown oldest first
    } elseif ( $args['type'] === 'past' ) {
        $where_clauses[] = $wpdb->prepare( "event_date < %s", $current_date );
    }

    if ( !empty($where_clauses) ) {
        $sql .= " WHERE " . implode( " AND ", $where_clauses );
    }

    $allowed_orderby = ['id', 'event_name', 'event_date', 'event_location', 'created_at'];
    $args['orderby'] = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'event_date';
    // Order for 'upcoming' is handled above.
    if ($args['type'] !== 'upcoming') {
        $args['order'] = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
    }


    $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
    $sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['number'], $args['offset'] );

    return $wpdb->get_results( $sql );
}

/**
 * Update an existing event.
 * @param int $event_id Event ID.
 * @param array $data Data to update.
 * @return bool True on success, false on failure.
 */
function bms_update_event( int $event_id, array $data ) {
    global $wpdb;
    $table_name = bms_get_events_table_name();

    if (isset($data['event_date'])) {
        $data['event_date'] = gmdate('Y-m-d', strtotime($data['event_date']));
    }
    if (isset($data['event_time'])) {
        if (!empty($data['event_time'])) {
            $data['event_time'] = gmdate('H:i:s', strtotime($data['event_time']));
        } else {
            $data['event_time'] = null;
        }
    }

    $result = $wpdb->update( $table_name, $data, array( 'id' => $event_id ) );
    return $result !== false;
}

/**
 * Delete an event. Also deletes associated attendance records.
 * @param int $event_id Event ID.
 * @return bool True on success, false on failure.
 */
function bms_delete_event( int $event_id ) {
    global $wpdb;
    $events_table = bms_get_events_table_name();
    $attendance_table = bms_get_event_attendance_table_name();

    // Start transaction if possible (requires specific DB engines like InnoDB)
    // $wpdb->query('START TRANSACTION');

    // Delete attendance records first
    $wpdb->delete( $attendance_table, array( 'event_id' => $event_id ), array( '%d' ) );

    // Delete the event
    $deleted = $wpdb->delete( $events_table, array( 'id' => $event_id ), array( '%d' ) );

    // if ($deleted !== false) {
    //     $wpdb->query('COMMIT');
    //     return true;
    // } else {
    //     $wpdb->query('ROLLBACK');
    //     return false;
    // }
    return $deleted !== false; // Simplified without explicit transaction control for broader compatibility
}

/**
 * Count total events.
 * @param array $args Arguments for filtering (e.g. 'type').
 */
function bms_count_events( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_events_table_name();
    $current_date = current_time('Y-m-d');

    $sql = "SELECT COUNT(*) FROM {$table_name}";
    $where_clauses = array();

    if ( ! empty( $args['search'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        $where_clauses[] = $wpdb->prepare( "(event_name LIKE %s OR event_description LIKE %s OR event_location LIKE %s)", $search_term, $search_term, $search_term );
    }
    if ( !empty($args['type']) && $args['type'] === 'upcoming' ) {
        $where_clauses[] = $wpdb->prepare( "event_date >= %s", $current_date );
    } elseif ( !empty($args['type']) && $args['type'] === 'past' ) {
        $where_clauses[] = $wpdb->prepare( "event_date < %s", $current_date );
    }
     if ( !empty($where_clauses) ) {
        $sql .= " WHERE " . implode( " AND ", $where_clauses );
    }
    return (int) $wpdb->get_var( $sql );
}


/*
 * ==========================================================================
 * Event Attendance CRUD Functions
 * ==========================================================================
 */

/**
 * Add an attendee to an event.
 * @param int $event_id Event ID.
 * @param int $resident_id Resident ID.
 * @param array $data Optional data like 'notes'.
 * @return int|false Attendance record ID on success, false on failure or if already attending.
 */
function bms_add_event_attendee( int $event_id, int $resident_id, array $data = [] ) {
    global $wpdb;
    $table_name = bms_get_event_attendance_table_name();

    if ( bms_is_resident_attending( $event_id, $resident_id ) ) {
        return false; // Already attending
    }

    $defaults = array(
        'event_id'    => $event_id,
        'resident_id' => $resident_id,
        'attended_at' => current_time( 'mysql' ),
        'notes'       => '',
    );
    $insert_data = wp_parse_args( $data, $defaults );

    $result = $wpdb->insert( $table_name, $insert_data );
    return $result ? $wpdb->insert_id : false;
}

/**
 * Remove an attendee from an event.
 * @param int $event_id Event ID.
 * @param int $resident_id Resident ID.
 * @return bool True on success, false on failure.
 */
function bms_remove_event_attendee( int $event_id, int $resident_id ) {
    global $wpdb;
    $table_name = bms_get_event_attendance_table_name();
    return $wpdb->delete( $table_name, array( 'event_id' => $event_id, 'resident_id' => $resident_id ), array( '%d', '%d' ) ) !== false;
}

/**
 * Get attendees for a specific event.
 * @param int $event_id Event ID.
 * @param array $args Pagination args.
 * @return array Array of resident objects who attended (or stdClass objects with resident info).
 */
function bms_get_event_attendees( int $event_id, array $args = [] ) {
    global $wpdb;
    $attendance_table = bms_get_event_attendance_table_name();
    $residents_table = function_exists('bms_get_residents_table_name') ? bms_get_residents_table_name() : $wpdb->prefix . 'bms_residents';


    $defaults = array(
        'number'  => 50, // Default number of attendees to fetch
        'offset'  => 0,
        'orderby' => 'r.last_name', // Order by resident's last name
        'order'   => 'ASC',
    );
    $args = wp_parse_args( $args, $defaults );

    $sql = $wpdb->prepare(
        "SELECT r.*, ea.attended_at, ea.notes as attendance_notes, ea.id as attendance_id
         FROM {$attendance_table} ea
         JOIN {$residents_table} r ON ea.resident_id = r.id
         WHERE ea.event_id = %d",
        $event_id
    );

    $allowed_orderby = ['r.last_name', 'r.first_name', 'ea.attended_at'];
    $args['orderby'] = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'r.last_name';
    $args['order'] = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';

    $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
    $sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['number'], $args['offset'] );

    return $wpdb->get_results( $sql );
}

/**
 * Count attendees for a specific event.
 * @param int $event_id Event ID.
 * @return int Number of attendees.
 */
function bms_count_event_attendees( int $event_id ) {
    global $wpdb;
    $table_name = bms_get_event_attendance_table_name();
    return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE event_id = %d", $event_id ) );
}


/**
 * Check if a resident is already marked as attending an event.
 * @param int $event_id Event ID.
 * @param int $resident_id Resident ID.
 * @return bool True if attending, false otherwise.
 */
function bms_is_resident_attending( int $event_id, int $resident_id ) {
    global $wpdb;
    $table_name = bms_get_event_attendance_table_name();
    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_name} WHERE event_id = %d AND resident_id = %d",
        $event_id, $resident_id
    ) );
    return $count > 0;
}
?>
