<?php
/**
 * Admin pages for Document Template Management
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register Document Template Admin Menu.
 */
function bms_document_template_admin_menu() {
    // Submenu under "Barangay MS"
    add_submenu_page(
        'barangay-ms',    // Parent slug
        __( 'Document Templates', 'barangay-management-system' ), // Page title
        __( 'Document Templates', 'barangay-management-system' ), // Menu title
        BMS_VIEW_DOCUMENT_TEMPLATES_CAP, // Capability to view templates
        'bms-doc-templates',  // Menu slug
        'bms_doc_templates_list_page_handler' // Function to display page content
    );

    add_submenu_page(
        'bms-doc-templates',  // Parent slug (under Document Templates for better grouping)
        __( 'Add New Template', 'barangay-management-system' ),
        __( 'Add New Template', 'barangay-management-system' ),
        BMS_MANAGE_DOCUMENT_TEMPLATES_CAP, // Capability to manage templates
        'bms-doc-template-add', // Menu slug
        'bms_doc_template_add_edit_page_handler' // Function
    );

    // Hidden submenu for issuing a document from a template (will be linked from templates list or resident profile)
    add_submenu_page(
        null, // No parent menu item, effectively hidden
        __( 'Issue Document', 'barangay-management-system' ),
        __( 'Issue Document', 'barangay-management-system' ),
        BMS_ISSUE_DOCUMENTS_CAP,
        'bms-issue-document',
        'bms_issue_document_page_handler'
    );

    // Hidden submenu for viewing an issued document
     add_submenu_page(
        null, // No parent menu item, effectively hidden
        __( 'View Issued Document', 'barangay-management-system' ),
        __( 'View Issued Document', 'barangay-management-system' ),
        BMS_VIEW_ISSUED_DOCUMENTS_CAP, // Capability to view issued documents
        'bms-view-issued-document',
        'bms_view_issued_document_page_handler'
    );

    // We might add a page for "All Issued Documents" later
}
add_action( 'admin_menu', 'bms_document_template_admin_menu' );

/**
 * Handler for displaying the Document Templates List page.
 */
function bms_doc_templates_list_page_handler() {
    if ( ! current_user_can( BMS_VIEW_DOCUMENT_TEMPLATES_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'barangay-management-system' ) );
    }

    if ( ! function_exists('bms_get_document_templates') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/document-crud.php';
    }

    // Handle delete action
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete_template' && isset( $_GET['template_id'] ) ) {
        if ( ! current_user_can( BMS_MANAGE_DOCUMENT_TEMPLATES_CAP ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to delete templates.', 'barangay-management-system' ) );
        }
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bms_delete_template_' . $_GET['template_id'] ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }
        $template_id = absint( $_GET['template_id'] );
        if ( bms_delete_document_template( $template_id ) ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Template deleted successfully.', 'barangay-management-system' ) . '</p></div>';
            });
        } else {
             add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete template.', 'barangay-management-system' ) . '</p></div>';
            });
        }
    }

    $templates = bms_get_document_templates( [ 'number' => 999 ] );

    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/document-template/list-document-templates.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/document-template/list-document-templates.php';
    } else {
        echo '<div class="wrap"><h1>' . esc_html__( 'Document Templates', 'barangay-management-system' ) . '</h1><p>' . esc_html__( 'Error: List view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}


/**
 * Handler for displaying the Add/Edit Document Template page.
 */
function bms_doc_template_add_edit_page_handler() {
    if ( ! current_user_can( BMS_MANAGE_DOCUMENT_TEMPLATES_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to manage document templates.', 'barangay-management-system' ) );
    }

    if ( ! function_exists('bms_create_document_template') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/document-crud.php';
    }

    $template_id = isset( $_GET['template_id'] ) ? absint( $_GET['template_id'] ) : 0;
    $template = null;
    $is_editing = false;

    if ( $template_id > 0 ) {
        $template = bms_get_document_template( $template_id );
        if ($template) {
            $is_editing = true;
        } else {
            wp_die( __( 'Template not found.', 'barangay-management-system' ) );
        }
    }

    if ( isset( $_POST['bms_submit_template'] ) ) {
        if ( ! isset( $_POST['bms_doc_template_nonce'] ) || ! wp_verify_nonce( $_POST['bms_doc_template_nonce'], 'bms_save_doc_template_action' ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }

        $data = array(
            'template_name'        => sanitize_text_field( $_POST['template_name'] ),
            'template_content'     => wp_kses_post( $_POST['template_content'] ), // Using wp_kses_post for HTML content
            'defined_placeholders' => sanitize_textarea_field( $_POST['defined_placeholders'] ), // Expecting comma-separated or JSON
        );

        $result = false;
        if ( $is_editing && $template_id > 0 ) {
            $result = bms_update_document_template( $template_id, $data );
            $message = $result ? __( 'Template updated successfully.', 'barangay-management-system' ) : __( 'Failed to update template.', 'barangay-management-system' );
        } else {
            $data['created_by'] = get_current_user_id();
            $new_template_id = bms_create_document_template( $data );
            $result = $new_template_id !== false;
            $message = $result ? __( 'Template added successfully.', 'barangay-management-system' ) : __( 'Failed to add template.', 'barangay-management-system' );
            if ($result) {
                wp_redirect( admin_url('admin.php?page=bms-doc-templates&template_added=true&id=' . $new_template_id) );
                exit;
            }
        }
        add_action( 'admin_notices', function() use ( $message, $result ) {
            $notice_type = $result ? 'success' : 'error';
            echo '<div class="notice notice-' . $notice_type . ' is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
        });
        if ($result && $is_editing) {
            $template = bms_get_document_template( $template_id ); // Reload data
        }
    }

    if ( ! $is_editing ) {
        $template_content = 'This is a test template.';
    }

    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/document-template/add-edit-document-template.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/document-template/add-edit-document-template.php';
    } else {
        $page_title = $is_editing ? __( 'Edit Document Template', 'barangay-management-system' ) : __( 'Add New Document Template', 'barangay-management-system' );
        echo '<div class="wrap"><h1>' . esc_html( $page_title ) . '</h1><p>' . esc_html__( 'Error: Form view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}

/**
 * Handler for issuing a document.
 */
function bms_issue_document_page_handler() {
    if ( ! current_user_can( BMS_ISSUE_DOCUMENTS_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to issue documents.', 'barangay-management-system' ) );
    }

    if ( ! function_exists('bms_issue_document') || ! function_exists('bms_get_resident') || ! function_exists('bms_get_document_template') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/document-crud.php';
        if (!function_exists('bms_get_resident')) { // Make sure resident CRUD is also loaded
             require_once BMS_PLUGIN_DIR . 'includes/crud/resident-crud.php';
        }
    }

    $template_id = isset( $_GET['template_id'] ) ? absint( $_GET['template_id'] ) : 0;
    $resident_id = isset( $_GET['resident_id'] ) ? absint( $_GET['resident_id'] ) : 0;

    $template = $template_id ? bms_get_document_template($template_id) : null;
    $resident = $resident_id ? bms_get_resident($resident_id) : null;

    if ( ! $template || ! $resident ) {
        wp_die( __( 'Template or Resident not specified or found.', 'barangay-management-system' ) );
    }

    if ( isset( $_POST['bms_confirm_issue_document'] ) ) {
        if ( ! isset( $_POST['bms_issue_doc_nonce'] ) || ! wp_verify_nonce( $_POST['bms_issue_doc_nonce'], 'bms_issue_document_action_' . $template_id . '_' . $resident_id ) ) {
            wp_die( __( 'Security check failed!', 'barangay-management-system' ) );
        }

        // Collect additional placeholder values if any from form
        $custom_placeholders = [];
        if(isset($_POST['custom_placeholders']) && is_array($_POST['custom_placeholders'])) {
            foreach($_POST['custom_placeholders'] as $key => $value) {
                $custom_placeholders[sanitize_text_field($key)] = sanitize_text_field($value);
            }
        }

        $issue_data = [
            'resident_id' => $resident_id,
            'template_id' => $template_id,
            'notes' => sanitize_textarea_field($_POST['notes'] ?? ''),
            'placeholder_values' => $custom_placeholders // Pass any extra values from a form
        ];

        $issued_doc_id = bms_issue_document( $issue_data );

        if ( $issued_doc_id ) {
            wp_redirect( admin_url( 'admin.php?page=bms-view-issued-document&issued_doc_id=' . $issued_doc_id . '&issued=true' ) );
            exit;
        } else {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to issue document.', 'barangay-management-system' ) . '</p></div>';
            });
        }
    }

    // Load the view for confirmation and potentially adding custom placeholder values
    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/issued-document/issue-document-form.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/issued-document/issue-document-form.php';
    } else {
        echo '<div class="wrap"><h1>' . esc_html__( 'Issue Document', 'barangay-management-system' ) . '</h1><p>' . esc_html__( 'Error: Issue document form view file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}


/**
 * Handler for viewing an issued document.
 */
function bms_view_issued_document_page_handler() {
    if ( ! current_user_can( BMS_VIEW_ISSUED_DOCUMENTS_CAP ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to view issued documents.', 'barangay-management-system' ) );
    }

    if ( ! function_exists('bms_get_issued_document') ) {
        require_once BMS_PLUGIN_DIR . 'includes/crud/document-crud.php';
    }

    $issued_doc_id = isset( $_GET['issued_doc_id'] ) ? absint( $_GET['issued_doc_id'] ) : 0;
    if ( ! $issued_doc_id ) {
        wp_die( __( 'No issued document ID specified.', 'barangay-management-system' ) );
    }

    $issued_document = bms_get_issued_document( $issued_doc_id );
    if ( ! $issued_document ) {
        wp_die( __( 'Issued document not found.', 'barangay-management-system' ) );
    }

    if (isset($_GET['issued']) && $_GET['issued'] == 'true') {
         add_action( 'admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Document issued successfully.', 'barangay-management-system' ) . '</p></div>';
        });
    }

    if ( file_exists( BMS_PLUGIN_DIR . 'admin/views/issued-document/view-issued-document.php' ) ) {
        include BMS_PLUGIN_DIR . 'admin/views/issued-document/view-issued-document.php';
    } else {
        echo '<div class="wrap"><h1>' . esc_html__( 'View Issued Document', 'barangay-management-system' ) . '</h1><p>' . esc_html__( 'Error: View file not found.', 'barangay-management-system' ) . '</p></div>';
    }
}

?>
