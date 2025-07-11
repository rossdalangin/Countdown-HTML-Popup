<?php
/**
 * Resident CRUD Functions
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Get the residents table name with the WordPress prefix.
 *
 * @return string The prefixed table name.
 */
function bms_get_residents_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'bms_residents';
}

/**
 * Create a new resident.
 *
 * @param array $data An array of data for the new resident.
 *                    Expected keys: 'first_name', 'last_name', 'birth_date', etc.
 * @return int|false The ID of the newly created resident on success, false on failure.
 */
function bms_create_resident( array $data ) {
    global $wpdb;
    $table_name = bms_get_residents_table_name();

    // Basic validation (can be expanded)
    if ( empty( $data['first_name'] ) || empty( $data['last_name'] ) ) {
        return false;
    }

    // Sanitize data before insertion
    $defaults = array(
        'first_name'        => '',
        'middle_name'       => '',
        'last_name'         => '',
        'suffix'            => '',
        'birth_date'        => null,
        'gender'            => '',
        'civil_status'      => '',
        'address_street'    => '',
        'address_barangay'  => '', // Should perhaps be a setting
        'address_city'      => '',
        'address_province'  => '',
        'contact_phone'     => '',
        'contact_email'     => '',
        'family_head_id'    => null,
        'occupation'        => '',
        'nationality'       => 'Filipino',
        'date_registered'   => current_time( 'mysql', 1 ), // GMT
        'created_by'        => get_current_user_id(),
    );

    $data = shortcode_atts( $defaults, $data );

    // Prepare data formats
    $formats = array(
        '%s', // first_name
        '%s', // middle_name
        '%s', // last_name
        '%s', // suffix
        '%s', // birth_date (string, ensure YYYY-MM-DD)
        '%s', // gender
        '%s', // civil_status
        '%s', // address_street
        '%s', // address_barangay
        '%s', // address_city
        '%s', // address_province
        '%s', // contact_phone
        '%s', // contact_email
        '%d', // family_head_id
        '%s', // occupation
        '%s', // nationality
        '%s', // date_registered
        '%d', // created_by
    );

    if ( $data['birth_date'] === null || $data['birth_date'] === '' ) {
        unset( $data['birth_date'] );
        // Adjust formats if birth_date is not set
        // This is tricky with $wpdb->insert, might be better to ensure it's always set or handle differently
    } else {
        // Ensure birth_date is in YYYY-MM-DD format
        $data['birth_date'] = gmdate('Y-m-d', strtotime($data['birth_date']));
    }

    if ( $data['family_head_id'] === null || $data['family_head_id'] === '' || $data['family_head_id'] == 0) {
        $data['family_head_id'] = null; // Ensure it's NULL for the DB
    }


    $result = $wpdb->insert( $table_name, $data, $formats );

    if ( $result ) {
        return $wpdb->insert_id;
    }

    return false;
}

/**
 * Get a single resident by ID.
 *
 * @param int $resident_id The ID of the resident to retrieve.
 * @return object|null An object representing the resident, or null if not found or on error.
 */
function bms_get_resident( int $resident_id ) {
    global $wpdb;
    $table_name = bms_get_residents_table_name();

    $sql = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $resident_id );
    $resident = $wpdb->get_row( $sql );

    return $resident;
}

/**
 * Get a list of residents.
 *
 * @param array $args Optional arguments for filtering and pagination.
 *                    'number' => int (number of items to return)
 *                    'offset' => int (number of items to skip)
 *                    'orderby' => string (column to order by)
 *                    'order' => string ('ASC' or 'DESC')
 *                    'search' => string (search term)
 * @return array An array of resident objects.
 */
function bms_get_residents( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_residents_table_name();

    $defaults = array(
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'last_name',
        'order'   => 'ASC',
        'search'  => '',
    );
    $args = wp_parse_args( $args, $defaults );

    $sql = "SELECT * FROM {$table_name}";

    if ( ! empty( $args['search'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        $sql .= $wpdb->prepare(
            " WHERE first_name LIKE %s OR last_name LIKE %s OR contact_email LIKE %s OR address_street LIKE %s",
            $search_term, $search_term, $search_term, $search_term
        );
    }

    // Ensure orderby and order are safe
    $allowed_orderby = ['id', 'first_name', 'last_name', 'birth_date', 'date_registered'];
    $args['orderby'] = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'last_name';
    $args['order'] = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';

    $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
    $sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['number'], $args['offset'] );

    $residents = $wpdb->get_results( $sql );

    return $residents;
}

/**
 * Update an existing resident.
 *
 * @param int $resident_id The ID of the resident to update.
 * @param array $data An array of data to update.
 * @return bool True on success, false on failure.
 */
function bms_update_resident( int $resident_id, array $data ) {
    global $wpdb;
    $table_name = bms_get_residents_table_name();

    // Ensure birth_date is in YYYY-MM-DD format if provided
    if (isset($data['birth_date']) && !empty($data['birth_date'])) {
        $data['birth_date'] = gmdate('Y-m-d', strtotime($data['birth_date']));
    } else if (isset($data['birth_date']) && empty($data['birth_date'])) {
        // If birth_date is explicitly set to empty, store it as NULL
        $data['birth_date'] = null;
    }

    if (isset($data['family_head_id']) && ($data['family_head_id'] === '' || $data['family_head_id'] == 0)) {
        $data['family_head_id'] = null; // Ensure it's NULL for the DB
    }

    // data should not contain 'id', 'created_by', 'date_registered', 'created_at', 'updated_at'
    // these are set by the system or only on creation. 'updated_at' is handled by DB.
    // We might want to update an 'updated_by' field if we add one.

    $result = $wpdb->update( $table_name, $data, array( 'id' => $resident_id ) );

    return $result !== false; // $wpdb->update returns number of rows affected or false on error
}

/**
 * Delete a resident.
 *
 * @param int $resident_id The ID of the resident to delete.
 * @return bool True on success, false on failure.
 */
function bms_delete_resident( int $resident_id ) {
    global $wpdb;
    $table_name = bms_get_residents_table_name();

    $result = $wpdb->delete( $table_name, array( 'id' => $resident_id ), array( '%d' ) );

    return $result !== false; // $wpdb->delete returns number of rows affected or false on error
}

/**
 * Get the total number of residents, for pagination.
 *
 * @param array $args Optional arguments for filtering (e.g., search).
 * @return int Total number of residents.
 */
function bms_count_residents(array $args = []) {
    global $wpdb;
    $table_name = bms_get_residents_table_name();

    $sql = "SELECT COUNT(*) FROM {$table_name}";

    if (!empty($args['search'])) {
        $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
        $sql .= $wpdb->prepare(
            " WHERE first_name LIKE %s OR last_name LIKE %s OR contact_email LIKE %s OR address_street LIKE %s",
            $search_term, $search_term, $search_term, $search_term
        );
    }

    return (int) $wpdb->get_var($sql);
}

?>
