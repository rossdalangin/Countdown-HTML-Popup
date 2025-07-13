<?php
/**
 * Admin Notices Helper
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Set an admin notice.
 *
 * @param string $message The message to display.
 * @param string $type The type of notice (e.g., 'success', 'error', 'warning', 'info').
 */
function bms_add_admin_notice( $message, $type = 'success' ) {
    $notices = get_transient( 'bms_admin_notices' );
    if ( ! is_array( $notices ) ) {
        $notices = [];
    }
    $notices[] = [
        'message' => $message,
        'type'    => $type,
    ];
    set_transient( 'bms_admin_notices', $notices, 60 ); // Store for 60 seconds
}

/**
 * Display admin notices.
 */
function bms_display_admin_notices() {
    $notices = get_transient( 'bms_admin_notices' );
    if ( is_array( $notices ) ) {
        foreach ( $notices as $notice ) {
            printf(
                '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
                esc_attr( $notice['type'] ),
                esc_html( $notice['message'] )
            );
        }
        delete_transient( 'bms_admin_notices' );
    }
}
add_action( 'admin_notices', 'bms_display_admin_notices' );
