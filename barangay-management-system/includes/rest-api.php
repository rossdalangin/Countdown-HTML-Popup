<?php
/**
 * REST API Endpoints
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register the REST API endpoints.
 */
function bms_register_rest_routes() {
    register_rest_route( 'bms/v1', '/residents', array(
        'methods' => 'GET',
        'callback' => 'bms_get_residents_rest',
        'permission_callback' => function () {
            return current_user_can( BMS_VIEW_RESIDENTS_CAP );
        }
    ) );
}
add_action( 'rest_api_init', 'bms_register_rest_routes' );

/**
 * Register the professional template REST API endpoint.
 */
function bms_register_professional_template_rest_route() {
    register_rest_route( 'bms/v1', '/professional-template', array(
        'methods' => 'GET',
        'callback' => 'bms_get_professional_template_rest',
        'permission_callback' => function () {
            return current_user_can( BMS_MANAGE_DOCUMENT_TEMPLATES_CAP );
        }
    ) );
}
add_action( 'rest_api_init', 'bms_register_professional_template_rest_route' );

/**
 * Get residents for the REST API.
 *
 * @param WP_REST_Request $request The REST API request.
 * @return WP_REST_Response The REST API response.
 */
function bms_get_residents_rest( WP_REST_Request $request ) {
    $search = $request->get_param( 'search' );
    $residents = bms_get_residents( [
        'search' => $search,
        'number' => 10,
    ] );

    $results = array();
    foreach ( $residents as $resident ) {
        $results[] = array(
            'id' => $resident->id,
            'text' => $resident->first_name . ' ' . $resident->last_name,
        );
    }

    return new WP_REST_Response( $results, 200 );
}

/**
 * Get the professional template content for the REST API.
 *
 * @return WP_REST_Response The REST API response.
 */
function bms_get_professional_template_rest() {
    ob_start();
    include BMS_PLUGIN_DIR . 'admin/views/document-template/professional-certificate.php';
    $content = ob_get_clean();
    return new WP_REST_Response( $content, 200 );
}
