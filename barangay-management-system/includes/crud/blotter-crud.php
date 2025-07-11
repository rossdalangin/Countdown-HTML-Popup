<?php
/**
 * Blotter Records CRUD Functions
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Get the blotter records table name.
 */
function bms_get_blotter_records_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'bms_blotter_records';
}

/**
 * Create a new blotter record.
 * @param array $data Blotter record data.
 * @return int|false Record ID on success, false on failure.
 */
function bms_create_blotter_record( array $data ) {
    global $wpdb;
    $table_name = bms_get_blotter_records_table_name();

    // Basic validation
    if ( empty( $data['complainant_name'] ) || empty( $data['respondent_name'] ) || empty( $data['incident_date'] ) || empty( $data['incident_location'] ) || empty( $data['incident_narrative'] ) ) {
        return false;
    }

    $defaults = array(
        'case_number'              => null, // Can be auto-generated or manual
        'complainant_name'         => '',
        'complainant_address'      => null,
        'complainant_contact'      => null,
        'respondent_name'          => '',
        'respondent_address'       => null,
        'respondent_contact'       => null,
        'incident_type'            => null,
        'incident_date'            => '', // YYYY-MM-DD HH:MM:SS
        'incident_location'        => '',
        'incident_narrative'       => '',
        'status'                   => 'Open',
        'assigned_official_name'   => null,
        'resolution_details'       => null,
        'date_reported'            => current_time( 'mysql' ),
        'recorded_by'              => get_current_user_id(),
    );
    $data = shortcode_atts( $defaults, $data );

    $data['incident_date'] = gmdate('Y-m-d H:i:s', strtotime($data['incident_date']));
    $data['date_reported'] = gmdate('Y-m-d H:i:s', strtotime($data['date_reported']));


    // Auto-generate case number if not provided and needed
    if (empty($data['case_number'])) {
        // Example: BMS-BLOTTER-YYYYMMDD-ID (ID will be available after insert, so this is tricky)
        // For now, let's assume it can be set manually or updated after creation.
        // A simpler unique ID could be just based on timestamp or a sequence.
        // Or, leave it null and let user fill it.
    }

    $result = $wpdb->insert( $table_name, $data );
    return $result ? $wpdb->insert_id : false;
}

/**
 * Get a single blotter record by ID.
 * @param int $record_id Record ID.
 * @return object|null Record object or null if not found.
 */
function bms_get_blotter_record( int $record_id ) {
    global $wpdb;
    $table_name = bms_get_blotter_records_table_name();
    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $record_id ) );
}

/**
 * Get a list of blotter records.
 * @param array $args Arguments for filtering.
 * @return array Array of record objects.
 */
function bms_get_blotter_records( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_blotter_records_table_name();

    $defaults = array(
        'number'           => 20,
        'offset'           => 0,
        'orderby'          => 'date_reported',
        'order'            => 'DESC',
        'search'           => '', // Search complainant, respondent, case number, narrative
        'status'           => '',
        'incident_type'    => '',
        'date_from'        => '', // Incident date range
        'date_to'          => '',
    );
    $args = wp_parse_args( $args, $defaults );

    $sql = "SELECT * FROM {$table_name}";
    $where_clauses = array();

    if ( ! empty( $args['search'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        $where_clauses[] = $wpdb->prepare( "(case_number LIKE %s OR complainant_name LIKE %s OR respondent_name LIKE %s OR incident_narrative LIKE %s)", $search_term, $search_term, $search_term, $search_term );
    }
    if ( ! empty( $args['status'] ) ) {
        $where_clauses[] = $wpdb->prepare( "status = %s", $args['status'] );
    }
    if ( ! empty( $args['incident_type'] ) ) {
        $where_clauses[] = $wpdb->prepare( "incident_type = %s", $args['incident_type'] );
    }
    if ( ! empty( $args['date_from'] ) ) {
        $where_clauses[] = $wpdb->prepare( "incident_date >= %s", $args['date_from'] . ' 00:00:00' );
    }
    if ( ! empty( $args['date_to'] ) ) {
        $where_clauses[] = $wpdb->prepare( "incident_date <= %s", $args['date_to'] . ' 23:59:59');
    }

    if ( !empty($where_clauses) ) {
        $sql .= " WHERE " . implode( " AND ", $where_clauses );
    }

    $allowed_orderby = ['id', 'case_number', 'complainant_name', 'respondent_name', 'incident_date', 'date_reported', 'status', 'incident_type'];
    $args['orderby'] = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'date_reported';
    $args['order'] = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

    $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
    $sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['number'], $args['offset'] );

    return $wpdb->get_results( $sql );
}

/**
 * Update an existing blotter record.
 * @param int $record_id Record ID.
 * @param array $data Data to update.
 * @return bool True on success, false on failure.
 */
function bms_update_blotter_record( int $record_id, array $data ) {
    global $wpdb;
    $table_name = bms_get_blotter_records_table_name();

    if (isset($data['incident_date'])) {
        $data['incident_date'] = gmdate('Y-m-d H:i:s', strtotime($data['incident_date']));
    }
     if (isset($data['date_reported']) && !empty($data['date_reported'])) { // Should generally not be updated after creation though
        $data['date_reported'] = gmdate('Y-m-d H:i:s', strtotime($data['date_reported']));
    }


    $result = $wpdb->update( $table_name, $data, array( 'id' => $record_id ) );
    return $result !== false;
}

/**
 * Delete a blotter record.
 * @param int $record_id Record ID.
 * @return bool True on success, false on failure.
 */
function bms_delete_blotter_record( int $record_id ) {
    global $wpdb;
    $table_name = bms_get_blotter_records_table_name();
    return $wpdb->delete( $table_name, array( 'id' => $record_id ), array( '%d' ) ) !== false;
}

/**
 * Count total blotter records.
 * @param array $args Arguments for filtering.
 */
function bms_count_blotter_records( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_blotter_records_table_name();

    $defaults = array(
        'search'           => '',
        'status'           => '',
        'incident_type'    => '',
        'date_from'        => '',
        'date_to'          => '',
    );
    $args = wp_parse_args( $args, $defaults );

    $sql = "SELECT COUNT(*) FROM {$table_name}";
    $where_clauses = array();

    if ( ! empty( $args['search'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        $where_clauses[] = $wpdb->prepare( "(case_number LIKE %s OR complainant_name LIKE %s OR respondent_name LIKE %s OR incident_narrative LIKE %s)", $search_term, $search_term, $search_term, $search_term );
    }
    if ( ! empty( $args['status'] ) ) {
        $where_clauses[] = $wpdb->prepare( "status = %s", $args['status'] );
    }
     if ( ! empty( $args['incident_type'] ) ) {
        $where_clauses[] = $wpdb->prepare( "incident_type = %s", $args['incident_type'] );
    }
    if ( ! empty( $args['date_from'] ) ) {
        $where_clauses[] = $wpdb->prepare( "incident_date >= %s", $args['date_from'] . ' 00:00:00');
    }
    if ( ! empty( $args['date_to'] ) ) {
        $where_clauses[] = $wpdb->prepare( "incident_date <= %s", $args['date_to'] . ' 23:59:59');
    }

    if ( !empty($where_clauses) ) {
        $sql .= " WHERE " . implode( " AND ", $where_clauses );
    }
    return (int) $wpdb->get_var( $sql );
}

/**
 * Get distinct incident types used in blotter records.
 * @return array Array of incident type names.
 */
function bms_get_blotter_incident_types() {
    global $wpdb;
    $table_name = bms_get_blotter_records_table_name();
    $sql = "SELECT DISTINCT incident_type FROM {$table_name} WHERE incident_type IS NOT NULL AND incident_type != '' ORDER BY incident_type ASC";
    return $wpdb->get_col($sql);
}

/**
 * Get distinct statuses used in blotter records.
 * @return array Array of status names.
 */
function bms_get_blotter_statuses() {
    global $wpdb;
    $table_name = bms_get_blotter_records_table_name();
    // Could also be a predefined list if statuses are fixed
    $sql = "SELECT DISTINCT status FROM {$table_name} WHERE status IS NOT NULL AND status != '' ORDER BY status ASC";
    return $wpdb->get_col($sql);
    // Or predefined: return ['Open', 'Under Investigation', 'Amicably Settled', 'Endorsed to PNP', 'Closed', 'Dismissed'];
}

/**
 * Generates a unique case number.
 * This is a basic example; can be made more robust.
 * @return string Unique case number.
 */
function bms_generate_blotter_case_number() {
    global $wpdb;
    $table_name = bms_get_blotter_records_table_name();

    // Get current year and month
    $year_month = current_time('Ym');

    // Find the last sequence number for the current year and month
    $last_case = $wpdb->get_var( $wpdb->prepare(
        "SELECT case_number FROM {$table_name} WHERE case_number LIKE %s ORDER BY id DESC LIMIT 1",
        $wpdb->esc_like( "BMS-" . $year_month . "-" ) . '%'
    ) );

    $sequence = 1;
    if ( $last_case ) {
        $parts = explode('-', $last_case);
        $last_sequence = end($parts);
        if (is_numeric($last_sequence)) {
            $sequence = intval($last_sequence) + 1;
        }
    }

    return sprintf("BMS-%s-%04d", $year_month, $sequence);
}

?>
