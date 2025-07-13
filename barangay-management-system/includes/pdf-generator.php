<?php
/**
 * PDF Generator
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Download and extract the dompdf library.
 */
function bms_download_dompdf() {
    $zip_file = BMS_PLUGIN_DIR . 'includes/lib/dompdf.zip';
    $unzip_dir = BMS_PLUGIN_DIR . 'includes/lib/';

    if ( ! file_exists( $unzip_dir . 'dompdf' ) ) {
        $response = wp_remote_get( 'https://github.com/dompdf/dompdf/releases/download/v2.0.3/dompdf_2-0-3.zip' );
        if ( is_wp_error( $response ) ) {
            return;
        }

        file_put_contents( $zip_file, wp_remote_retrieve_body( $response ) );

        WP_Filesystem();
        unzip_file( $zip_file, $unzip_dir );
        unlink( $zip_file );
    }
}
add_action( 'init', 'bms_download_dompdf' );

require_once wp_normalize_path( BMS_PLUGIN_DIR . 'includes/lib/dompdf/autoload.inc.php' );

use Dompdf\Dompdf;

/**
 * Generate a PDF from HTML.
 *
 * @param string $html The HTML to convert to PDF.
 * @return string The PDF content.
 */
function bms_generate_pdf( $html ) {
    $dompdf = new Dompdf();
    $css = file_get_contents( BMS_PLUGIN_DIR . 'public/css/certificate.css' );
    $full_html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>' . $css . '</style></head><body>' . $html . '</body></html>';
    $dompdf->loadHtml( $full_html );
    $dompdf->setPaper( 'A4', 'portrait' );
    $dompdf->render();
    return $dompdf->output();
}
