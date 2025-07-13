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
    $dompdf->loadHtml( $html );
    $dompdf->setPaper( 'A4', 'portrait' );
    $dompdf->render();
    return $dompdf->output();
}
