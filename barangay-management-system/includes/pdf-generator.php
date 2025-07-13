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
    $full_html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>' .
                 'body { font-family: sans-serif; }' .
                 '.certificate-container { border: 10px solid #000; padding: 20px; margin: 20px; font-family: \'Times New Roman\', Times, serif; }' .
                 '.certificate-header { text-align: center; }' .
                 '.certificate-header h1 { font-size: 36px; margin-bottom: 10px; }' .
                 '.certificate-header h2 { font-size: 24px; margin-bottom: 20px; }' .
                 '.certificate-body { display: flex; justify-content: space-between; }' .
                 '.certificate-body .left-column { width: 30%; text-align: center; }' .
                 '.certificate-body .right-column { width: 70%; }' .
                 '.certificate-body .seal { width: 150px; height: 150px; border: 5px solid #000; border-radius: 50%; margin: 0 auto; }' .
                 '.certificate-footer { margin-top: 50px; text-align: center; }' .
                 '</style></head><body>' . $html . '</body></html>';
    $dompdf->loadHtml( $full_html );
    $dompdf->setPaper( 'A4', 'portrait' );
    $dompdf->render();
    return $dompdf->output();
}
