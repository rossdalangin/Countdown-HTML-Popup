<?php
/**
 * Professional Certificate Template
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<style>
    .certificate-container {
        border: 10px solid #000;
        padding: 20px;
        margin: 20px;
        font-family: 'Times New Roman', Times, serif;
    }
    .certificate-header {
        text-align: center;
    }
    .certificate-header h1 {
        font-size: 36px;
        margin-bottom: 10px;
    }
    .certificate-header h2 {
        font-size: 24px;
        margin-bottom: 20px;
    }
    .certificate-body {
        display: flex;
        justify-content: space-between;
    }
    .certificate-body .left-column {
        width: 30%;
        text-align: center;
    }
    .certificate-body .right-column {
        width: 70%;
    }
    .certificate-body .seal {
        width: 150px;
        height: 150px;
        border: 5px solid #000;
        border-radius: 50%;
        margin: 0 auto;
    }
    .certificate-footer {
        margin-top: 50px;
        text-align: center;
    }
</style>
<div class="certificate-container">
    <div class="certificate-header">
        <h1>Certificate of Indigency</h1>
        <h2>Office of the Barangay Captain</h2>
    </div>
    <div class="certificate-body">
        <div class="left-column">
            <div class="seal"></div>
        </div>
        <div class="right-column">
            <p>This is to certify that <strong>{{resident_full_name}}</strong>, of legal age, is a bonafide resident of this Barangay, with address at <strong>{{resident_address}}</strong>.</p>
            <p>This certification is being issued upon the request of the above-named person for whatever legal purpose it may serve.</p>
            <p>Given this <strong>{{current_date}}</strong> at the Office of the Barangay Captain, Barangay Hall, {{address_barangay}}, {{address_city}}, {{address_province}}.</p>
        </div>
    </div>
    <div class="certificate-footer">
        <p>_________________________</p>
        <p><strong>Barangay Captain</strong></p>
    </div>
</div>
