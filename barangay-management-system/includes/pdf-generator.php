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
