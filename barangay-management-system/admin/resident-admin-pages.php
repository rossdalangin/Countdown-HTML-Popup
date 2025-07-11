<?php
/**
 * Admin pages for Resident Management
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register Resident Admin Menu.
 *
 * Adds a top-level menu page for "Barangay MS" and
 * a submenu page for "Residents".
 */
function bms_resident_admin_menu() {
    add_menu_page(
        __( 'Barangay MS', 'barangay-management-system' ), // Page title
        __( 'Barangay MS', 'barangay-management-system' ), // Menu title
        BMS_VIEW_RESIDENTS_CAP, // Capability - view for parent, manage for specific sub-items
        'barangay-ms',    // Menu slug
        'bms_residents_list_page_handler', // Function to display the first submenu page content
        'dashicons-admin-users', // Icon
        26 // Position
    );

    add_submenu_page(
        'barangay-ms',    // Parent slug
        __( 'All Residents', 'barangay-management-system' ), // Page title
        __( 'All Residents', 'barangay-management-system' ), // Menu title
        BMS_VIEW_RESIDENTS_CAP, // Capability to view the list
        'bms-residents',  // Menu slug (this will be the main residents page)
        'bms_residents_list_page_handler' // Function to display page content
    );

    add_submenu_page(
        'bms-residents',  // Parent slug (under All Residents for better grouping, or 'barangay-ms')
        __( 'Add New Resident', 'barangay-management-system' ),
        __( 'Add New Resident', 'barangay-management-system' ),
        BMS_MANAGE_RESIDENTS_CAP, // Capability to add/edit/delete
        'bms-resident-add', // Menu slug
        'bms_resident_add_edit_page_handler' // Function
    );

    // We will also need an edit page, but it can share the same handler as "add new"
    // and won't have its own menu item. The link to edit will pass resident_id.
}
add_action( 'admin_menu', 'bms_resident_admin_menu' );

/**
 * Handler for displaying the Residents List page.
 */
function bms_residents_list_page_handler() {
    // Ensure the CRUD functions are available
    if ( ! current_user_can( BMS_VIEW_RESIDENTS_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'barangay-management-system' ) );
    }

    // Ensure the CRUD functions are available
    if ( ! function_exists('bms_get_residents') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/resident-crud.php';
    }

    // Check if a delete action is requested
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['resident_id'] ) ) {
        if ( ! current_user_can( BMS_MANAGE_RESIDENTS_CAP ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to delete residents.', 'barangay-management-system' ) );
        }
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bms_delete_resident_' . $_GET['resident_id'] ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }
        $resident_id = absint( $_GET['resident_id'] );
        if (bms_delete_resident( $resident_id )) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Resident deleted successfully.', 'barangay-management-system' ) . '</p></div>';
            });
        } else {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete resident.', 'barangay-management-system' ) . '</p></div>';
            });
        }
    }


    // For now, a simple list. We will integrate WP_List_Table later.
    $residents = bms_get_residents( [ 'number' => 999 ] ); // Get all for now

    // The view file will be in admin/views/resident/list-residents.php
    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/resident/list-residents.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/resident/list-residents.php';
    } else {
        echo '<div class="wrap"><h1>' . esc_html__( 'Residents List', 'barangay-management-system' ) . '</h1><p>' . esc_html__( 'Error: List view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}

/**
 * Handler for displaying the Add/Edit Resident page.
 */
function bms_resident_add_edit_page_handler() {
    if ( ! current_user_can( BMS_MANAGE_RESIDENTS_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to manage residents.', 'barangay-management-system' ) );
    }

    // Ensure the CRUD functions are available
    if ( ! function_exists('bms_create_resident') || ! function_exists('bms_get_resident') || ! function_exists('bms_update_resident') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/resident-crud.php';
    }

    $resident_id = isset( $_GET['resident_id'] ) ? absint( $_GET['resident_id'] ) : 0;
    $resident = null;
    $is_editing = false;

    if ( $resident_id > 0 ) {
        $resident = bms_get_resident( $resident_id );
        if ($resident) {
            $is_editing = true;
        } else {
            // Resident not found, maybe show an error or redirect
            wp_die( __( 'Resident not found.', 'barangay-management-system' ) );
        }
    }

    // Handle form submission
    if ( isset( $_POST['bms_submit_resident'] ) ) {
        if ( ! isset( $_POST['bms_resident_nonce_field'] ) || ! wp_verify_nonce( $_POST['bms_resident_nonce_field'], 'bms_save_resident_action' ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }

        $data = array(
            'first_name'        => sanitize_text_field( $_POST['first_name'] ),
            'middle_name'       => sanitize_text_field( $_POST['middle_name'] ),
            'last_name'         => sanitize_text_field( $_POST['last_name'] ),
            'suffix'            => sanitize_text_field( $_POST['suffix'] ),
            'birth_date'        => sanitize_text_field( $_POST['birth_date'] ), // Further validation for date format needed
            'gender'            => sanitize_text_field( $_POST['gender'] ),
            'civil_status'      => sanitize_text_field( $_POST['civil_status'] ),
            'address_street'    => sanitize_text_field( $_POST['address_street'] ),
            'address_barangay'  => sanitize_text_field( $_POST['address_barangay'] ),
            'address_city'      => sanitize_text_field( $_POST['address_city'] ),
            'address_province'  => sanitize_text_field( $_POST['address_province'] ),
            'contact_phone'     => sanitize_text_field( $_POST['contact_phone'] ),
            'contact_email'     => sanitize_email( $_POST['contact_email'] ),
            'family_head_id'    => isset($_POST['family_head_id']) ? absint( $_POST['family_head_id'] ) : null,
            'occupation'        => sanitize_text_field( $_POST['occupation'] ),
            'nationality'       => sanitize_text_field( $_POST['nationality'] ),
        );

        // Validate birth_date format (YYYY-MM-DD)
        if (!empty($data['birth_date']) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $data['birth_date'])) {
            // Handle invalid date format - perhaps add an admin notice and don't save
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Invalid birth date format. Please use YYYY-MM-DD.', 'barangay-management-system') . '</p></div>';
            });
        } else {
            $result = false;
            if ( $is_editing && $resident_id > 0 ) {
                $result = bms_update_resident( $resident_id, $data );
                $message = $result ? __( 'Resident updated successfully.', 'barangay-management-system' ) : __( 'Failed to update resident.', 'barangay-management-system' );
            } else {
                // For new resident, add date_registered and created_by
                $data['date_registered'] = current_time( 'mysql', 1 ); // GMT
                $data['created_by'] = get_current_user_id();
                $new_resident_id = bms_create_resident( $data );
                $result = $new_resident_id !== false;
                $message = $result ? __( 'Resident added successfully.', 'barangay-management-system' ) : __( 'Failed to add resident.', 'barangay-management-system' );
                if ($result) {
                    // Redirect to edit page of the new resident or list page
                    wp_redirect( admin_url('admin.php?page=bms-residents&resident_added=true&id=' . $new_resident_id) );
                    exit;
                }
            }

            add_action( 'admin_notices', function() use ( $message, $result ) {
                $notice_type = $result ? 'success' : 'error';
                echo '<div class="notice notice-' . $notice_type . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
            });

            // If successful and editing, reload the resident data
            if ($result && $is_editing) {
                $resident = bms_get_resident( $resident_id );
            }
        }
    }

    // The view file will be in admin/views/resident/add-edit-resident.php
    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/resident/add-edit-resident.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/resident/add-edit-resident.php';
    } else {
        $page_title = $is_editing ? __( 'Edit Resident', 'barangay-management-system' ) : __( 'Add New Resident', 'barangay-management-system' );
        echo '<div class="wrap"><h1>' . esc_html( $page_title ) . '</h1><p>' . esc_html__( 'Error: Form view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}

?>
