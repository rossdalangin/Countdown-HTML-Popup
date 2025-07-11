<?php
/**
 * Certificate Request CRUD Functions
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Get the certificate requests table name.
 */
function bms_get_certificate_requests_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'bms_certificate_requests';
}

/**
 * Create a new certificate request.
 * Typically called from a front-end form by a resident, or by staff on behalf of a resident.
 * @param array $data Request data.
 * @return int|false Request ID on success, false on failure.
 */
function bms_create_certificate_request( array $data ) {
    global $wpdb;
    $table_name = bms_get_certificate_requests_table_name();

    if ( empty( $data['resident_id'] ) || empty( $data['document_template_id'] ) ) {
        return false;
    }

    $defaults = array(
        'resident_id'          => 0,
        'document_template_id' => 0,
        'purpose'              => '',
        'request_date'         => current_time( 'mysql' ),
        'status'               => 'Pending', // Default status
        'notes_resident'       => null,
        'payment_status'       => 'Unpaid', // Default payment status
        // 'created_by_user_id' => get_current_user_id(), // If staff creates it
    );
    $data = shortcode_atts( $defaults, $data );

    // Ensure resident_id and document_template_id are integers
    $data['resident_id'] = absint($data['resident_id']);
    $data['document_template_id'] = absint($data['document_template_id']);
    $data['request_date'] = gmdate('Y-m-d H:i:s', strtotime($data['request_date']));


    $result = $wpdb->insert( $table_name, $data );
    return $result ? $wpdb->insert_id : false;
}

/**
 * Get a single certificate request by ID.
 * @param int $request_id Request ID.
 * @return object|null Request object or null if not found.
 */
function bms_get_certificate_request( int $request_id ) {
    global $wpdb;
    $table_name = bms_get_certificate_requests_table_name();

    $sql = $wpdb->prepare(
        "SELECT cr.*, dt.template_name, res.first_name, res.last_name, res.middle_name
         FROM {$table_name} cr
         LEFT JOIN " . bms_get_document_templates_table_name() . " dt ON cr.document_template_id = dt.id
         LEFT JOIN " . bms_get_residents_table_name() . " res ON cr.resident_id = res.id
         WHERE cr.id = %d",
        $request_id
    );
    return $wpdb->get_row( $sql );
}

/**
 * Get a list of certificate requests.
 * @param array $args Arguments for filtering.
 * @return array Array of request objects.
 */
function bms_get_certificate_requests( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_certificate_requests_table_name();
    $residents_table = bms_get_residents_table_name();
    $templates_table = bms_get_document_templates_table_name();

    $defaults = array(
        'number'               => 20,
        'offset'               => 0,
        'orderby'              => 'cr.request_date', // Default order by request_date of certificate_requests table
        'order'                => 'DESC',
        'search_resident'      => '', // Search by resident name
        'search_template'      => '', // Search by template name
        'status'               => '',
        'document_template_id' => 0,
        'resident_id'          => 0,
        'date_from'            => '', // request_date range
        'date_to'              => '',
    );
    $args = wp_parse_args( $args, $defaults );

    $sql_select = "SELECT cr.*,
                          dt.template_name,
                          CONCAT(res.first_name, ' ', res.last_name) as resident_full_name,
                          issuer.display_name as processed_by_name";
    $sql_from = " FROM {$table_name} cr";
    $sql_joins = " LEFT JOIN {$templates_table} dt ON cr.document_template_id = dt.id
                   LEFT JOIN {$residents_table} res ON cr.resident_id = res.id
                   LEFT JOIN {$wpdb->users} issuer ON cr.processed_by = issuer.ID";

    $where_clauses = array();

    if ( ! empty( $args['search_resident'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search_resident'] ) . '%';
        $where_clauses[] = $wpdb->prepare( "(res.first_name LIKE %s OR res.last_name LIKE %s OR CONCAT(res.first_name, ' ', res.last_name) LIKE %s)", $search_term, $search_term, $search_term );
    }
    if ( ! empty( $args['search_template'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search_template'] ) . '%';
        $where_clauses[] = $wpdb->prepare( "dt.template_name LIKE %s", $search_term );
    }
    if ( ! empty( $args['status'] ) ) {
        $where_clauses[] = $wpdb->prepare( "cr.status = %s", $args['status'] );
    }
    if ( $args['document_template_id'] > 0 ) {
        $where_clauses[] = $wpdb->prepare( "cr.document_template_id = %d", $args['document_template_id'] );
    }
    if ( $args['resident_id'] > 0 ) { // For fetching requests of a specific resident
        $where_clauses[] = $wpdb->prepare( "cr.resident_id = %d", $args['resident_id'] );
    }
    if ( ! empty( $args['date_from'] ) ) {
        $where_clauses[] = $wpdb->prepare( "cr.request_date >= %s", $args['date_from'] . ' 00:00:00' );
    }
    if ( ! empty( $args['date_to'] ) ) {
        $where_clauses[] = $wpdb->prepare( "cr.request_date <= %s", $args['date_to'] . ' 23:59:59');
    }

    $sql = $sql_select . $sql_from . $sql_joins;
    if ( !empty($where_clauses) ) {
        $sql .= " WHERE " . implode( " AND ", $where_clauses );
    }

    // Ensure orderby is safe and qualified with table alias
    $allowed_orderby = ['cr.request_date', 'cr.status', 'dt.template_name', 'resident_full_name'];
    $args['orderby'] = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'cr.request_date';
    $args['order'] = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

    $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
    $sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['number'], $args['offset'] );

    return $wpdb->get_results( $sql );
}

/**
 * Update a certificate request, typically status and notes.
 * @param int $request_id Request ID.
 * @param array $data Data to update. Should include 'status'.
 * @return bool True on success, false on failure.
 */
function bms_update_certificate_request( int $request_id, array $data ) {
    global $wpdb;
    $table_name = bms_get_certificate_requests_table_name();

    // Ensure essential fields for update logic are present
    if ( empty($data) ) {
        return false;
    }

    // Always set processed_by and processed_at when status or significant data changes
    if (isset($data['status']) || isset($data['issued_document_id']) || isset($data['payment_status'])) {
        $data['processed_by'] = get_current_user_id();
        $data['processed_at'] = current_time( 'mysql' );
    }

    $result = $wpdb->update( $table_name, $data, array( 'id' => $request_id ) );
    return $result !== false;
}


/**
 * Delete a certificate request.
 * @param int $request_id Request ID.
 * @return bool True on success, false on failure.
 */
function bms_delete_certificate_request( int $request_id ) {
    global $wpdb;
    $table_name = bms_get_certificate_requests_table_name();
    // Consider if associated issued_document should also be deleted or unlinked.
    return $wpdb->delete( $table_name, array( 'id' => $request_id ), array( '%d' ) ) !== false;
}

/**
 * Count total certificate requests.
 * @param array $args Arguments for filtering.
 */
function bms_count_certificate_requests( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_certificate_requests_table_name();
    $residents_table = bms_get_residents_table_name();
    $templates_table = bms_get_document_templates_table_name();

    $defaults = array(
        'search_resident'      => '',
        'search_template'      => '',
        'status'               => '',
        'document_template_id' => 0,
        'resident_id'          => 0,
        'date_from'            => '',
        'date_to'              => '',
    );
    $args = wp_parse_args( $args, $defaults );

    $sql_from = " FROM {$table_name} cr";
    $sql_joins = " LEFT JOIN {$templates_table} dt ON cr.document_template_id = dt.id
                   LEFT JOIN {$residents_table} res ON cr.resident_id = res.id";

    $where_clauses = array();
    // ... (copy where clauses from bms_get_certificate_requests) ...
    if ( ! empty( $args['search_resident'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search_resident'] ) . '%';
        $where_clauses[] = $wpdb->prepare( "(res.first_name LIKE %s OR res.last_name LIKE %s OR CONCAT(res.first_name, ' ', res.last_name) LIKE %s)", $search_term, $search_term, $search_term );
    }
    if ( ! empty( $args['search_template'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search_template'] ) . '%';
        $where_clauses[] = $wpdb->prepare( "dt.template_name LIKE %s", $search_term );
    }
    if ( ! empty( $args['status'] ) ) {
        $where_clauses[] = $wpdb->prepare( "cr.status = %s", $args['status'] );
    }
    if ( $args['document_template_id'] > 0 ) {
        $where_clauses[] = $wpdb->prepare( "cr.document_template_id = %d", $args['document_template_id'] );
    }
     if ( $args['resident_id'] > 0 ) {
        $where_clauses[] = $wpdb->prepare( "cr.resident_id = %d", $args['resident_id'] );
    }
    if ( ! empty( $args['date_from'] ) ) {
        $where_clauses[] = $wpdb->prepare( "cr.request_date >= %s", $args['date_from'] . ' 00:00:00' );
    }
    if ( ! empty( $args['date_to'] ) ) {
        $where_clauses[] = $wpdb->prepare( "cr.request_date <= %s", $args['date_to'] . ' 23:59:59');
    }

    $sql = "SELECT COUNT(cr.id)" . $sql_from . $sql_joins;
    if ( !empty($where_clauses) ) {
        $sql .= " WHERE " . implode( " AND ", $where_clauses );
    }
    return (int) $wpdb->get_var( $sql );
}

/**
 * Get possible statuses for certificate requests.
 * Could be predefined or fetched if statuses are dynamic.
 * @return array
 */
function bms_get_certificate_request_statuses() {
    return ['Pending', 'Approved', 'Processing', 'Ready for Pickup', 'Claimed', 'Rejected', 'Cancelled'];
}

?>
