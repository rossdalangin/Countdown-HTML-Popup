<?php
/**
 * Admin pages for Financial Management
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register Financial Admin Menu.
 */
function bms_financial_admin_menu() {
    add_submenu_page(
        'barangay-ms',    // Parent slug
        __( 'Financials', 'barangay-management-system' ),
        __( 'Financials', 'barangay-management-system' ),
        BMS_VIEW_FINANCIALS_CAP,
        'bms-financials',
        'bms_financials_overview_page_handler'
    );

    add_submenu_page(
        'bms-financials', // Parent slug
        __( 'Add Transaction', 'barangay-management-system' ),
        __( 'Add Transaction', 'barangay-management-system' ),
        BMS_MANAGE_FINANCIALS_CAP,
        'bms-financial-add',
        'bms_financial_add_edit_transaction_page_handler'
    );
}
add_action( 'admin_menu', 'bms_financial_admin_menu' );

/**
 * Handler for displaying the Financials Overview/List page.
 */
function bms_financials_overview_page_handler() {
    if ( ! current_user_can( BMS_VIEW_FINANCIALS_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'barangay-management-system' ) );
    }

    if ( ! function_exists('bms_get_transactions') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/financial-crud.php';
    }

    // Handle delete action
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete_transaction' && isset( $_GET['transaction_id'] ) ) {
        if ( ! current_user_can( BMS_MANAGE_FINANCIALS_CAP ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to delete transactions.', 'barangay-management-system' ) );
        }
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bms_delete_transaction_' . $_GET['transaction_id'] ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }
        $transaction_id = absint( $_GET['transaction_id'] );
        if ( bms_delete_transaction( $transaction_id ) ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Transaction deleted successfully.', 'barangay-management-system' ) . '</p></div>';
            });
        } else {
             add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete transaction.', 'barangay-management-system' ) . '</p></div>';
            });
        }
    }

    // Filters
    $filter_args = [];
    $filter_args['transaction_type'] = $_GET['type'] ?? '';
    $filter_args['category'] = $_GET['category'] ?? '';
    $filter_args['date_from'] = $_GET['date_from'] ?? '';
    $filter_args['date_to'] = $_GET['date_to'] ?? '';
    // Basic search for description
    $filter_args['search'] = $_GET['s'] ?? '';


    $transactions = bms_get_transactions( array_merge(['number' => 50], $filter_args) ); // Get 50 for now, pagination later
    $summary = bms_get_financial_summary($filter_args);
    $categories = bms_get_transaction_categories();


    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/financial/list-transactions.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/financial/list-transactions.php';
    } else {
        echo '<div class="wrap"><h1>' . esc_html__( 'Financials', 'barangay-management-system' ) . '</h1><p>' . esc_html__( 'Error: List view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}

/**
 * Handler for displaying the Add/Edit Transaction page.
 */
function bms_financial_add_edit_transaction_page_handler() {
    if ( ! current_user_can( BMS_MANAGE_FINANCIALS_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to manage financial transactions.', 'barangay-management-system' ) );
    }

    if ( ! function_exists('bms_create_transaction') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/financial-crud.php';
    }

    $transaction_id = isset( $_GET['transaction_id'] ) ? absint( $_GET['transaction_id'] ) : 0;
    $transaction = null;
    $is_editing = false;

    if ( $transaction_id > 0 ) {
        $transaction = bms_get_transaction( $transaction_id );
        if ($transaction) {
            $is_editing = true;
        } else {
            wp_die( __( 'Transaction not found.', 'barangay-management-system' ) );
        }
    }

    if ( isset( $_POST['bms_submit_transaction'] ) ) {
        if ( ! isset( $_POST['bms_financial_transaction_nonce'] ) || ! wp_verify_nonce( $_POST['bms_financial_transaction_nonce'], 'bms_save_transaction_action' ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }

        $data = array(
            'transaction_type' => sanitize_text_field( $_POST['transaction_type'] ),
            'description'      => sanitize_textarea_field( $_POST['description'] ),
            'amount'           => sanitize_text_field( $_POST['amount'] ), // Further validation in CRUD
            'transaction_date' => sanitize_text_field( $_POST['transaction_date'] ), // Further validation in CRUD
            'category'         => sanitize_text_field( $_POST['category'] ),
            'reference_number' => sanitize_text_field( $_POST['reference_number'] ),
            'notes'            => sanitize_textarea_field( $_POST['notes'] ),
        );

        // Amount validation
        if ( !is_numeric($data['amount']) || floatval($data['amount']) <= 0 ) {
             add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Invalid amount. Please enter a positive number.', 'barangay-management-system') . '</p></div>';
            });
        }
        // Date validation
        elseif (empty($data['transaction_date']) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['transaction_date'])) {
             add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Invalid transaction date format. Please use YYYY-MM-DD.', 'barangay-management-system') . '</p></div>';
            });
        } else {
            $result = false;
            if ( $is_editing && $transaction_id > 0 ) {
                $result = bms_update_transaction( $transaction_id, $data );
                $message = $result ? __( 'Transaction updated successfully.', 'barangay-management-system' ) : __( 'Failed to update transaction.', 'barangay-management-system' );
            } else {
                $data['created_by'] = get_current_user_id();
                $new_transaction_id = bms_create_transaction( $data );
                $result = $new_transaction_id !== false;
                $message = $result ? __( 'Transaction added successfully.', 'barangay-management-system' ) : __( 'Failed to add transaction. Please check all required fields and amount.', 'barangay-management-system' );
                if ($result) {
                    wp_redirect( admin_url('admin.php?page=bms-financials&transaction_added=true&id=' . $new_transaction_id) );
                    exit;
                }
            }
            add_action( 'admin_notices', function() use ( $message, $result ) {
                $notice_type = $result ? 'success' : 'error';
                echo '<div class="notice notice-' . $notice_type . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
            });
            if ($result && $is_editing) {
                $transaction = bms_get_transaction( $transaction_id );
            }
        }
    }

    $categories = bms_get_transaction_categories(); // For datalist or select options

    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/financial/add-edit-transaction.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/financial/add-edit-transaction.php';
    } else {
        $page_title = $is_editing ? __( 'Edit Transaction', 'barangay-management-system' ) : __( 'Add New Transaction', 'barangay-management-system' );
        echo '<div class="wrap"><h1>' . esc_html( $page_title ) . '</h1><p>' . esc_html__( 'Error: Form view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}

?>
