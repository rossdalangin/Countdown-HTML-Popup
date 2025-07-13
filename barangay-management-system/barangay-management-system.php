<?php
/**
 * Plugin Name:       Barangay Management System
 * Plugin URI:        https://example.com/plugins/barangay-management-system/
 * Description:       A comprehensive management system for barangay operations, including resident, document, event, financial, blotter, and certificate request management.
 * Version:           1.0.0
 * Author:            Jules - AI Plugin Developer
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       barangay-management-system
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define BMS_PLUGIN_FILE - path to this file.
if ( ! defined( 'BMS_PLUGIN_FILE' ) ) {
    define( 'BMS_PLUGIN_FILE', __FILE__ );
}

// Define BMS_PLUGIN_DIR - path to the plugin directory.
if ( ! defined( 'BMS_PLUGIN_DIR' ) ) {
    define( 'BMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Define BMS_PLUGIN_URL - URL to the plugin directory.
if ( ! defined( 'BMS_PLUGIN_URL' ) ) {
    define( 'BMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bms-activator.php
 */
function activate_barangay_management_system() {
    // Placeholder for activation code
    // e.g., creating database tables, setting default options
    if ( ! get_option( 'bms_version' ) ) {
        update_option( 'bms_version', '1.0.0' );
    }
    bms_create_database_tables();

    // Add user roles and capabilities
    if (function_exists('bms_add_roles_and_capabilities')) {
        bms_add_roles_and_capabilities();
    }
}

/**
 * Create necessary database tables for the plugin.
 */
function bms_create_database_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    // Residents Table
    $table_name_residents = $wpdb->prefix . 'bms_residents';
    $sql_residents = "CREATE TABLE $table_name_residents (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        first_name VARCHAR(100) NOT NULL,
        middle_name VARCHAR(100) NULL,
        last_name VARCHAR(100) NOT NULL,
        suffix VARCHAR(20) NULL,
        birth_date DATE NULL,
        gender VARCHAR(20) NULL,
        civil_status VARCHAR(50) NULL,
        address_street VARCHAR(255) NULL,
        address_barangay VARCHAR(100) NULL,
        address_city VARCHAR(100) NULL,
        address_province VARCHAR(100) NULL,
        contact_phone VARCHAR(20) NULL,
        contact_email VARCHAR(100) NULL,
        family_head_id BIGINT(20) UNSIGNED NULL,
        occupation VARCHAR(100) NULL,
        nationality VARCHAR(50) DEFAULT 'Filipino',
        date_registered DATETIME NOT NULL,
        created_by BIGINT(20) UNSIGNED NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_last_name (last_name),
        KEY idx_first_name (first_name),
        KEY idx_family_head_id (family_head_id),
        KEY idx_created_by (created_by)
    ) $charset_collate;";
    dbDelta( $sql_residents );

    // Document Templates Table
    $table_name_doc_templates = $wpdb->prefix . 'bms_document_templates';
    $sql_doc_templates = "CREATE TABLE $table_name_doc_templates (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        template_name VARCHAR(255) NOT NULL,
        template_content LONGTEXT NULL,
        defined_placeholders TEXT NULL,
        created_by BIGINT(20) UNSIGNED NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_template_name (template_name)
    ) $charset_collate;";
    dbDelta( $sql_doc_templates );

    // Issued Documents Table
    $table_name_issued_docs = $wpdb->prefix . 'bms_issued_documents';
    $sql_issued_docs = "CREATE TABLE $table_name_issued_docs (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        resident_id BIGINT(20) UNSIGNED NOT NULL,
        template_id BIGINT(20) UNSIGNED NOT NULL,
        document_type VARCHAR(255) NULL,
        generated_content LONGTEXT NULL,
        issued_by_user_id BIGINT(20) UNSIGNED NULL,
        issued_at DATETIME NOT NULL,
        notes TEXT NULL,
        status VARCHAR(50) DEFAULT 'Issued',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_resident_id (resident_id),
        KEY idx_template_id (template_id),
        KEY idx_issued_at (issued_at),
        KEY idx_status (status)
    ) $charset_collate;";
    dbDelta( $sql_issued_docs );

    // Events Table
    $table_name_events = $wpdb->prefix . 'bms_events';
    $sql_events = "CREATE TABLE $table_name_events (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        event_name VARCHAR(255) NOT NULL,
        event_description TEXT NULL,
        event_date DATE NOT NULL,
        event_time TIME NULL,
        event_location VARCHAR(255) NULL,
        created_by BIGINT(20) UNSIGNED NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_event_date (event_date),
        KEY idx_event_name (event_name)
    ) $charset_collate;";
    dbDelta( $sql_events );

    // Event Attendance Table
    $table_name_event_attendance = $wpdb->prefix . 'bms_event_attendance';
    $sql_event_attendance = "CREATE TABLE $table_name_event_attendance (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        event_id BIGINT(20) UNSIGNED NOT NULL,
        resident_id BIGINT(20) UNSIGNED NOT NULL,
        attended_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
        notes VARCHAR(255) NULL,
        PRIMARY KEY (id),
        UNIQUE KEY unique_event_resident (event_id, resident_id),
        KEY idx_event_id (event_id),
        KEY idx_resident_id (resident_id)
    ) $charset_collate;";
    dbDelta( $sql_event_attendance );

    // Financial Transactions Table
    $table_name_financials = $wpdb->prefix . 'bms_financial_transactions';
    $sql_financials = "CREATE TABLE $table_name_financials (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        transaction_type VARCHAR(20) NOT NULL,
        description TEXT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        transaction_date DATE NOT NULL,
        category VARCHAR(100) NULL,
        reference_number VARCHAR(100) NULL,
        notes TEXT NULL,
        created_by BIGINT(20) UNSIGNED NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_transaction_type (transaction_type),
        KEY idx_transaction_date (transaction_date),
        KEY idx_category (category)
    ) $charset_collate;";
    dbDelta( $sql_financials );

    // Blotter Records Table
    $table_name_blotter = $wpdb->prefix . 'bms_blotter_records';
    $sql_blotter = "CREATE TABLE $table_name_blotter (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        case_number VARCHAR(50) NULL UNIQUE,
        complainant_name VARCHAR(255) NOT NULL,
        complainant_address VARCHAR(255) NULL,
        complainant_contact VARCHAR(100) NULL,
        respondent_name VARCHAR(255) NOT NULL,
        respondent_address VARCHAR(255) NULL,
        respondent_contact VARCHAR(100) NULL,
        incident_type VARCHAR(100) NULL,
        incident_date DATETIME NOT NULL,
        incident_location VARCHAR(255) NOT NULL,
        incident_narrative TEXT NOT NULL,
        status VARCHAR(50) DEFAULT 'Open',
        assigned_official_name VARCHAR(255) NULL,
        resolution_details TEXT NULL,
        date_reported DATETIME NOT NULL,
        recorded_by BIGINT(20) UNSIGNED NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_status (status),
        KEY idx_incident_date (incident_date),
        KEY idx_incident_type (incident_type)
    ) $charset_collate;";
    dbDelta( $sql_blotter );

    // Certificate Requests Table
    $table_name_cert_requests = $wpdb->prefix . 'bms_certificate_requests';
    $sql_cert_requests = "CREATE TABLE $table_name_cert_requests (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        resident_id BIGINT(20) UNSIGNED NOT NULL,
        document_template_id BIGINT(20) UNSIGNED NOT NULL,
        purpose TEXT NULL,
        request_date DATETIME NOT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        notes_admin TEXT NULL,
        notes_resident TEXT NULL,
        issued_document_id BIGINT(20) UNSIGNED NULL,
        payment_status VARCHAR(50) DEFAULT 'Unpaid',
        payment_details TEXT NULL,
        processed_by BIGINT(20) UNSIGNED NULL,
        processed_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_resident_id (resident_id),
        KEY idx_document_template_id (document_template_id),
        KEY idx_status (status),
        KEY idx_request_date (request_date),
        KEY idx_issued_document_id (issued_document_id)
    ) $charset_collate;";
    dbDelta( $sql_cert_requests );

    // Add other table creation statements here as modules are developed
}


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bms-deactivator.php
 */
function deactivate_barangay_management_system() {
    // Placeholder for deactivation code
    // e.g., removing options, transients, or custom tables if the user wants to uninstall

    // Optional: Remove roles and capabilities on deactivation.
    // This is generally better done on uninstall to avoid issues if the plugin is temporarily deactivated.
    // if (function_exists('bms_remove_roles_and_capabilities')) {
    //     bms_remove_roles_and_capabilities();
    // }
}

register_activation_hook( __FILE__, 'activate_barangay_management_system' );
register_deactivation_hook( __FILE__, 'deactivate_barangay_management_system' );


// Include plugin core files
require_once BMS_PLUGIN_DIR . 'includes/admin-notices.php';
require_once BMS_PLUGIN_DIR . 'includes/user-roles.php'; // User roles and capabilities
require_once BMS_PLUGIN_DIR . 'includes/crud/resident-crud.php';
require_once BMS_PLUGIN_DIR . 'includes/crud/document-crud.php';
require_once BMS_PLUGIN_DIR . 'includes/crud/event-crud.php';
require_once BMS_PLUGIN_DIR . 'includes/crud/financial-crud.php';
require_once BMS_PLUGIN_DIR . 'includes/crud/blotter-crud.php';
require_once BMS_PLUGIN_DIR . 'includes/crud/certificate-request-crud.php';

// Include admin-specific files
if ( is_admin() ) {
    require_once BMS_PLUGIN_DIR . 'admin/admin-hooks.php';
    require_once BMS_PLUGIN_DIR . 'admin/resident-admin-pages.php';
    require_once BMS_PLUGIN_DIR . 'admin/document-admin-pages.php';
    require_once BMS_PLUGIN_DIR . 'admin/event-admin-pages.php';
    require_once BMS_PLUGIN_DIR . 'admin/financial-admin-pages.php';
    require_once BMS_PLUGIN_DIR . 'admin/blotter-admin-pages.php';
    require_once BMS_PLUGIN_DIR . 'admin/certificate-request-admin-pages.php';
    // We will add other admin files here, e.g., for settings, other modules
}

// Include public-facing files (if any yet)
// Example: require_once BMS_PLUGIN_DIR . 'public/resident-public-views.php';


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
// require BMS_PLUGIN_DIR . 'includes/class-barangay-management-system.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_barangay_management_system() {

    // $plugin = new Barangay_Management_System();
    // $plugin->run();
    // For now, we'll keep it simple. We will uncomment and build out the class structure later.

}
// run_barangay_management_system(); // We will call this once we have the main class.

// Basic check to see if the plugin is loaded
if ( ! function_exists( 'bms_is_active' ) ) {
    function bms_is_active() {
        return true; // Simple check, can be expanded
    }
}

// Placeholder for loading text domain for translation
function bms_load_textdomain() {
    load_plugin_textdomain(
        'barangay-management-system',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}
add_action( 'plugins_loaded', 'bms_load_textdomain' );

// Placeholder for enqueueing scripts and styles
function bms_enqueue_scripts() {
    // Example: wp_enqueue_style( 'bms-style', BMS_PLUGIN_URL . 'css/bms-public.css', array(), '1.0.0', 'all' );
    // Example: wp_enqueue_script( 'bms-script', BMS_PLUGIN_URL . 'js/bms-public.js', array( 'jquery' ), '1.0.0', false );
}
// add_action( 'wp_enqueue_scripts', 'bms_enqueue_scripts' ); // For front-end

function bms_admin_enqueue_scripts() {
    // Example: wp_enqueue_style( 'bms-admin-style', BMS_PLUGIN_URL . 'css/bms-admin.css', array(), '1.0.0', 'all' );
    // Example: wp_enqueue_script( 'bms-admin-script', BMS_PLUGIN_URL . 'js/bms-admin.js', array( 'jquery' ), '1.0.0', false );
}
// add_action( 'admin_enqueue_scripts', 'bms_admin_enqueue_scripts' ); // For admin area

// We will create these directories and files in later steps
// includes/
// css/
// js/
// languages/
// admin/
// public/

?>
