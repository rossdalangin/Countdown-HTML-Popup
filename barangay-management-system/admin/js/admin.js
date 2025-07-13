jQuery(document).ready(function($) {
    $('.wrap .page-title-action').after('<a href="#" id="load-professional-template" class="page-title-action">Load Professional Template</a>');

    $('#load-professional-template').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?php echo esc_url( get_rest_url( null, 'bms/v1/professional-template' ) ); ?>',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ); ?>' );
            },
            success: function( response ) {
                var url = new URL(window.location.href);
                url.pathname = url.pathname.replace('edit.php', 'post-new.php');
                url.searchParams.set('post_type', 'bms_doc_template');
                url.searchParams.set('post_title', 'Professional Certificate');
                url.searchParams.set('content', response);
                window.location.href = url.href;
            }
        });
    });
});
