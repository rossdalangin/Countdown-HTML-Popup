<?php

require_once(dirname(__FILE__) . '/../../../wp-load.php');

function bms_doc_template_add_edit_page_handler_test() {
    $is_editing = false;
    $template_content = '';

    if ( ! $is_editing ) {
        ob_start();
        include BMS_PLUGIN_DIR . 'admin/views/document-template/professional-certificate.php';
        $template_content = ob_get_clean();
    }

    echo $template_content;
}

bms_doc_template_add_edit_page_handler_test();
