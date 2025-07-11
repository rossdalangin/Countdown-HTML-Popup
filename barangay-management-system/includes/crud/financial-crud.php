<?php
/**
 * Financial Transactions CRUD Functions
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Get the financial transactions table name.
 */
function bms_get_financial_transactions_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'bms_financial_transactions';
}

/**
 * Create a new financial transaction.
 * @param array $data Transaction data.
 * @return int|false Transaction ID on success, false on failure.
 */
function bms_create_transaction( array $data ) {
    global $wpdb;
    $table_name = bms_get_financial_transactions_table_name();

    if ( empty( $data['transaction_type'] ) || empty( $data['description'] ) || !isset( $data['amount'] ) || empty( $data['transaction_date'] ) ) {
        return false;
    }

    $defaults = array(
        'transaction_type' => '', // 'income' or 'expense'
        'description'      => '',
        'amount'           => 0.00,
        'transaction_date' => '', // YYYY-MM-DD
        'category'         => null,
        'reference_number' => null,
        'notes'            => null,
        'created_by'       => get_current_user_id(),
    );
    $data = shortcode_atts( $defaults, $data );

    // Validate and sanitize amount
    $data['amount'] = floatval( $data['amount'] );
    if ( $data['amount'] <= 0 ) return false; // Amount must be positive

    $data['transaction_date'] = gmdate('Y-m-d', strtotime($data['transaction_date']));

    $allowed_types = ['income', 'expense'];
    if (!in_array($data['transaction_type'], $allowed_types)) {
        return false; // Invalid transaction type
    }

    $result = $wpdb->insert( $table_name, $data, array('%s', '%s', '%f', '%s', '%s', '%s', '%s', '%d') );
    return $result ? $wpdb->insert_id : false;
}

/**
 * Get a single transaction by ID.
 * @param int $transaction_id Transaction ID.
 * @return object|null Transaction object or null if not found.
 */
function bms_get_transaction( int $transaction_id ) {
    global $wpdb;
    $table_name = bms_get_financial_transactions_table_name();
    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $transaction_id ) );
}

/**
 * Get a list of transactions.
 * @param array $args Arguments for filtering.
 * @return array Array of transaction objects.
 */
function bms_get_transactions( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_financial_transactions_table_name();

    $defaults = array(
        'number'           => 20,
        'offset'           => 0,
        'orderby'          => 'transaction_date',
        'order'            => 'DESC',
        'search'           => '',
        'transaction_type' => '', // 'income', 'expense'
        'category'         => '',
        'date_from'        => '', // YYYY-MM-DD
        'date_to'          => '', // YYYY-MM-DD
    );
    $args = wp_parse_args( $args, $defaults );

    $sql = "SELECT * FROM {$table_name}";
    $where_clauses = array();

    if ( ! empty( $args['search'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        $where_clauses[] = $wpdb->prepare( "(description LIKE %s OR category LIKE %s OR reference_number LIKE %s)", $search_term, $search_term, $search_term );
    }
    if ( ! empty( $args['transaction_type'] ) ) {
        $where_clauses[] = $wpdb->prepare( "transaction_type = %s", $args['transaction_type'] );
    }
    if ( ! empty( $args['category'] ) ) {
        $where_clauses[] = $wpdb->prepare( "category = %s", $args['category'] );
    }
    if ( ! empty( $args['date_from'] ) ) {
        $where_clauses[] = $wpdb->prepare( "transaction_date >= %s", $args['date_from'] );
    }
    if ( ! empty( $args['date_to'] ) ) {
        $where_clauses[] = $wpdb->prepare( "transaction_date <= %s", $args['date_to'] );
    }

    if ( !empty($where_clauses) ) {
        $sql .= " WHERE " . implode( " AND ", $where_clauses );
    }

    $allowed_orderby = ['id', 'transaction_type', 'amount', 'transaction_date', 'category'];
    $args['orderby'] = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'transaction_date';
    $args['order'] = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

    $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
    $sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $args['number'], $args['offset'] );

    return $wpdb->get_results( $sql );
}

/**
 * Update an existing transaction.
 * @param int $transaction_id Transaction ID.
 * @param array $data Data to update.
 * @return bool True on success, false on failure.
 */
function bms_update_transaction( int $transaction_id, array $data ) {
    global $wpdb;
    $table_name = bms_get_financial_transactions_table_name();

    if (isset($data['amount'])) {
        $data['amount'] = floatval( $data['amount'] );
        if ( $data['amount'] <= 0 ) return false;
    }
     if (isset($data['transaction_date'])) {
        $data['transaction_date'] = gmdate('Y-m-d', strtotime($data['transaction_date']));
    }
    if (isset($data['transaction_type'])) {
        $allowed_types = ['income', 'expense'];
        if (!in_array($data['transaction_type'], $allowed_types)) {
            return false;
        }
    }

    $result = $wpdb->update( $table_name, $data, array( 'id' => $transaction_id ) );
    return $result !== false;
}

/**
 * Delete a transaction.
 * @param int $transaction_id Transaction ID.
 * @return bool True on success, false on failure.
 */
function bms_delete_transaction( int $transaction_id ) {
    global $wpdb;
    $table_name = bms_get_financial_transactions_table_name();
    return $wpdb->delete( $table_name, array( 'id' => $transaction_id ), array( '%d' ) ) !== false;
}

/**
 * Count total transactions.
 * @param array $args Arguments for filtering.
 */
function bms_count_transactions( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_financial_transactions_table_name();

    $defaults = array(
        'search'           => '',
        'transaction_type' => '',
        'category'         => '',
        'date_from'        => '',
        'date_to'          => '',
    );
    $args = wp_parse_args( $args, $defaults );

    $sql = "SELECT COUNT(*) FROM {$table_name}";
    $where_clauses = array();

    if ( ! empty( $args['search'] ) ) {
        $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        $where_clauses[] = $wpdb->prepare( "(description LIKE %s OR category LIKE %s OR reference_number LIKE %s)", $search_term, $search_term, $search_term );
    }
    if ( ! empty( $args['transaction_type'] ) ) {
        $where_clauses[] = $wpdb->prepare( "transaction_type = %s", $args['transaction_type'] );
    }
    if ( ! empty( $args['category'] ) ) {
        $where_clauses[] = $wpdb->prepare( "category = %s", $args['category'] );
    }
    if ( ! empty( $args['date_from'] ) ) {
        $where_clauses[] = $wpdb->prepare( "transaction_date >= %s", $args['date_from'] );
    }
    if ( ! empty( $args['date_to'] ) ) {
        $where_clauses[] = $wpdb->prepare( "transaction_date <= %s", $args['date_to'] );
    }

    if ( !empty($where_clauses) ) {
        $sql .= " WHERE " . implode( " AND ", $where_clauses );
    }
    return (int) $wpdb->get_var( $sql );
}

/**
 * Get financial summary (total income, total expenses, net).
 * @param array $args Filters like date_from, date_to, category.
 * @return object Object with total_income, total_expenses, net_amount.
 */
function bms_get_financial_summary( array $args = [] ) {
    global $wpdb;
    $table_name = bms_get_financial_transactions_table_name();

    $defaults = array(
        'category'  => '',
        'date_from' => '', // YYYY-MM-DD
        'date_to'   => '', // YYYY-MM-DD
    );
    $args = wp_parse_args( $args, $defaults );

    $where_clauses_income = ["transaction_type = 'income'"];
    $where_clauses_expenses = ["transaction_type = 'expense'"];

    if ( ! empty( $args['category'] ) ) {
        $cat_clause = $wpdb->prepare( "category = %s", $args['category'] );
        $where_clauses_income[] = $cat_clause;
        $where_clauses_expenses[] = $cat_clause;
    }
    if ( ! empty( $args['date_from'] ) ) {
        $date_from_clause = $wpdb->prepare( "transaction_date >= %s", $args['date_from'] );
        $where_clauses_income[] = $date_from_clause;
        $where_clauses_expenses[] = $date_from_clause;
    }
    if ( ! empty( $args['date_to'] ) ) {
        $date_to_clause = $wpdb->prepare( "transaction_date <= %s", $args['date_to'] );
        $where_clauses_income[] = $date_to_clause;
        $where_clauses_expenses[] = $date_to_clause;
    }

    $sql_income = "SELECT SUM(amount) FROM {$table_name} WHERE " . implode(' AND ', $where_clauses_income);
    $sql_expenses = "SELECT SUM(amount) FROM {$table_name} WHERE " . implode(' AND ', $where_clauses_expenses);

    $total_income = (float) $wpdb->get_var( $sql_income );
    $total_expenses = (float) $wpdb->get_var( $sql_expenses );

    return (object) array(
        'total_income'   => $total_income,
        'total_expenses' => $total_expenses,
        'net_amount'     => $total_income - $total_expenses,
    );
}

/**
 * Get distinct categories used in transactions.
 * @return array Array of category names.
 */
function bms_get_transaction_categories() {
    global $wpdb;
    $table_name = bms_get_financial_transactions_table_name();
    $sql = "SELECT DISTINCT category FROM {$table_name} WHERE category IS NOT NULL AND category != '' ORDER BY category ASC";
    $results = $wpdb->get_col($sql);
    return $results;
}

?>
