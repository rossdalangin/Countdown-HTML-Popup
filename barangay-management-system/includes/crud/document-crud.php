<?php
/**
 * Document Templates & Issued Documents CRUD Functions
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Get the document templates table name with the WordPress prefix.
 */
function bms_get_document_templates_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'bms_document_templates';
}

/**
 * Get the issued documents table name with the WordPress prefix.
 */
function bms_get_issued_documents_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'bms_issued_documents';
}

/*
 * ==========================================================================
 * Document Template CRUD Functions
 * ==========================================================================
 */

/**
 * Create a new document template.
 *
 * @param array $data An array of data for the new template.
 *                    Expected keys: 'template_name', 'template_content', 'defined_placeholders'.
 * @return int|false The ID of the newly created template on success, false on failure.
 */
function bms_create_document_template( array $data ) {
    global $wpdb;
    $table_name = bms_get_document_templates_table_name();

    if ( empty( $data['template_name'] ) ) {
        return false;
    }

    $defaults = array(
        'template_name'        => '',
        'template_content'     => '', // Should be sanitized with wp_kses_post or similar if HTML
        'defined_placeholders' => '', // JSON string or serialized array
        'created_by'           => get_current_user_id(),
    );
    $data = shortcode_atts( $defaults, $data );

    // Sanitize content appropriately if it contains HTML. wp_kses_post is good for post content.
    // $data['template_content'] = wp_kses_post($data['template_content']);

    $result = $wpdb->insert( $table_name, $data );

    if ( $result ) {
        return $wpdb->insert_id;
    }
    return false;
}

/**
 * Get a single document template by ID.
 *
 * @param int $template_id The ID of the template to retrieve.
 * @return object|null An object representing the template, or null if not found.
 */
function bms_get_document_template( int $template_id ) {
    global $wpdb;
    $table_name = bms_get_document_templates_table_name();
    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $template_id ) );
}

/**
 * Get a list of document templates.
 *
 * @param array $args Optional arguments for filtering and pagination.
 * @return array An array of template objects.
 */
function bms_get_document_templates( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_document_templates_table_name();

    $defaults = array(
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'template_name',
        'order'   => 'ASC',
        'search'  => '',
    );
    $args = wp_parse_args( $args, $defaults );

    $sql = "SELECT * FROM {$table_name}";
    if ( ! empty( $args['search'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        $sql .= $wpdb->prepare( " WHERE template_name LIKE %s", $search_term );
    }

    $allowed_orderby = ['id', 'template_name', 'created_at'];
    $args['orderby'] = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'template_name';
    $args['order'] = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';

    $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
    $sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['number'], $args['offset'] );

    return $wpdb->get_results( $sql );
}

/**
 * Update an existing document template.
 *
 * @param int $template_id The ID of the template to update.
 * @param array $data An array of data to update.
 * @return bool True on success, false on failure.
 */
function bms_update_document_template( int $template_id, array $data ) {
    global $wpdb;
    $table_name = bms_get_document_templates_table_name();

    // $data['template_content'] = wp_kses_post($data['template_content']); // If allowing HTML

    $result = $wpdb->update( $table_name, $data, array( 'id' => $template_id ) );
    return $result !== false;
}

/**
 * Delete a document template.
 *
 * @param int $template_id The ID of the template to delete.
 * @return bool True on success, false on failure.
 */
function bms_delete_document_template( int $template_id ) {
    global $wpdb;
    $table_name = bms_get_document_templates_table_name();
    // Consider what happens to issued documents if a template is deleted.
    // Maybe a soft delete or prevent deletion if documents are linked.
    return $wpdb->delete( $table_name, array( 'id' => $template_id ), array( '%d' ) ) !== false;
}

/**
 * Count total document templates.
 */
function bms_count_document_templates( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_document_templates_table_name();
    $sql = "SELECT COUNT(*) FROM {$table_name}";
    if ( ! empty( $args['search'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        $sql .= $wpdb->prepare( " WHERE template_name LIKE %s", $search_term );
    }
    return (int) $wpdb->get_var( $sql );
}


/*
 * ==========================================================================
 * Issued Document Functions
 * ==========================================================================
 */

/**
 * Issue a new document based on a template and resident data.
 *
 * @param array $data {
 *     Required. An array of data for issuing the document.
 *
 *     @type int    $resident_id         ID of the resident.
 *     @type int    $template_id         ID of the document template.
 *     @type string $document_type       Optional. Name/type of the document, defaults to template name.
 *     @type array  $placeholder_values  Optional. Key-value pairs for replacing placeholders.
 *                                       e.g., ['custom_field_1' => 'value1']
 *     @type string $notes               Optional. Any notes for this issuance.
 *     @type string $status              Optional. Initial status, defaults to 'Issued'.
 * }
 * @return int|false The ID of the newly issued document on success, false on failure.
 */
function bms_issue_document( array $data ) {
    global $wpdb;
    $issued_table_name = bms_get_issued_documents_table_name();

    if ( empty( $data['resident_id'] ) || empty( $data['template_id'] ) ) {
        return false;
    }

    $resident = function_exists('bms_get_resident') ? bms_get_resident( $data['resident_id'] ) : null;
    $template = bms_get_document_template( $data['template_id'] );

    if ( ! $resident || ! $template ) {
        error_log("BMS Error: Resident or Template not found for issuing document. Res ID: {$data['resident_id']}, Tpl ID: {$data['template_id']}");
        return false;
    }

    // Prepare placeholders from resident data
    $resident_placeholders = array(
        '{{resident_full_name}}'      => trim(sprintf('%s %s %s %s', $resident->first_name, $resident->middle_name, $resident->last_name, $resident->suffix)),
        '{{resident_first_name}}'     => $resident->first_name,
        '{{resident_last_name}}'      => $resident->last_name,
        '{{resident_address}}'        => trim(sprintf('%s, %s, %s, %s', $resident->address_street, $resident->address_barangay, $resident->address_city, $resident->address_province)),
        '{{resident_birth_date}}'     => $resident->birth_date ? gmdate('F j, Y', strtotime($resident->birth_date)) : 'N/A',
        '{{resident_age}}'            => $resident->birth_date ? (date_diff(date_create($resident->birth_date), date_create('now'))->y) : 'N/A',
        '{{resident_gender}}'         => $resident->gender,
        '{{resident_civil_status}}'   => $resident->civil_status,
        '{{current_date}}'            => gmdate('F j, Y'),
        // Add more common resident fields as needed
    );

    // Merge with any custom passed placeholder values
    $all_placeholders = array_merge($resident_placeholders, $data['placeholder_values'] ?? []);

    $generated_content = str_replace(
        array_keys($all_placeholders),
        array_values($all_placeholders),
        $template->template_content
    );

    // Replace any remaining known placeholders from `defined_placeholders` with a default (e.g., "N/A")
    $defined_placeholders_list = !empty($template->defined_placeholders) ? json_decode($template->defined_placeholders, true) : [];
    if (is_array($defined_placeholders_list)) {
        foreach($defined_placeholders_list as $dp_key) {
            if (strpos($generated_content, $dp_key) !== false) {
                 // A bit simplistic, assumes {{placeholder}} format
                $generated_content = str_replace($dp_key, __('N/A', 'barangay-management-system'), $generated_content);
            }
        }
    }


    $insert_data = array(
        'resident_id'       => $data['resident_id'],
        'template_id'       => $data['template_id'],
        'document_type'     => !empty($data['document_type']) ? $data['document_type'] : $template->template_name,
        'generated_content' => $generated_content, // Should be sanitized if displayed directly, but stored raw.
        'issued_by_user_id' => get_current_user_id(),
        'issued_at'         => current_time( 'mysql' ),
        'notes'             => $data['notes'] ?? null,
        'status'            => $data['status'] ?? 'Issued',
    );

    $result = $wpdb->insert( $issued_table_name, $insert_data );

    if ( $result ) {
        return $wpdb->insert_id;
    }
    error_log("BMS Error: Failed to insert issued document. WPDB Error: " . $wpdb->last_error);
    return false;
}

/**
 * Get a single issued document by ID.
 */
function bms_get_issued_document( int $issued_doc_id ) {
    global $wpdb;
    $table_name = bms_get_issued_documents_table_name();
    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $issued_doc_id ) );
}

/**
 * Get issued documents for a specific resident.
 */
function bms_get_issued_documents_for_resident( int $resident_id, array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_issued_documents_table_name();
    $defaults = array(
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'issued_at',
        'order'   => 'DESC',
    );
    $args = wp_parse_args( $args, $defaults );

    $sql = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE resident_id = %d", $resident_id );

    $allowed_orderby = ['id', 'document_type', 'issued_at', 'status'];
    $args['orderby'] = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'issued_at';
    $args['order'] = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';

    $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
    $sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['number'], $args['offset'] );

    return $wpdb->get_results( $sql );
}

/**
 * Get all issued documents.
 */
function bms_get_all_issued_documents( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_issued_documents_table_name();
    // Similar to bms_get_residents, with search, pagination etc.
    // To be fully implemented when listing issued documents.
    $defaults = array(
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'issued_at',
        'order'   => 'DESC',
        'search_resident_name' => '', // Example custom search param
        'search_doc_type' => '', // Example custom search param
    );
    $args = wp_parse_args( $args, $defaults );

    $sql = "SELECT id.*, res.first_name, res.last_name
            FROM {$table_name} id
            LEFT JOIN " . bms_get_residents_table_name() . " res ON id.resident_id = res.id";

    $where_clauses = [];
    if (!empty($args['search_doc_type'])) {
        $where_clauses[] = $wpdb->prepare("id.document_type LIKE %s", '%' . $wpdb->esc_like($args['search_doc_type']) . '%');
    }
    if (!empty($args['search_resident_name'])) {
         $where_clauses[] = $wpdb->prepare("(res.first_name LIKE %s OR res.last_name LIKE %s)",
            '%' . $wpdb->esc_like($args['search_resident_name']) . '%',
            '%' . $wpdb->esc_like($args['search_resident_name']) . '%'
        );
    }

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }

    $allowed_orderby = ['id.id', 'id.document_type', 'id.issued_at', 'id.status', 'res.last_name']; // Qualify column names
    $args['orderby'] = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'id.issued_at';
    $args['order'] = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';

    $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
    $sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['number'], $args['offset'] );

    return $wpdb->get_results( $sql );
}

/**
 * Count all issued documents.
 */
function bms_count_all_issued_documents(array $args = []) {
    global $wpdb;
    $table_name = bms_get_issued_documents_table_name();
    $sql = "SELECT COUNT(id.id)
            FROM {$table_name} id
            LEFT JOIN " . bms_get_residents_table_name() . " res ON id.resident_id = res.id";

    $where_clauses = [];
    if (!empty($args['search_doc_type'])) {
        $where_clauses[] = $wpdb->prepare("id.document_type LIKE %s", '%' . $wpdb->esc_like($args['search_doc_type']) . '%');
    }
    if (!empty($args['search_resident_name'])) {
         $where_clauses[] = $wpdb->prepare("(res.first_name LIKE %s OR res.last_name LIKE %s)",
            '%' . $wpdb->esc_like($args['search_resident_name']) . '%',
            '%' . $wpdb->esc_like($args['search_resident_name']) . '%'
        );
    }
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    return (int) $wpdb->get_var($sql);
}

?>
