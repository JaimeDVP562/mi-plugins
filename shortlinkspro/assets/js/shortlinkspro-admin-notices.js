(function ( $ ) {

    // Hide review notice
    $('body').on('click', '.shortlinkspro-hide-review-notice', function(e) {

        e.preventDefault();

        $.ajax({
            url: ajaxurl,
            data: {
                action: 'shortlinkspro_hide_review_notice',
                nonce: shortlinkspro_admin_notices.nonce,
            },
            success: function(response) {
                // Hide the notice on success
                $('.shortlinkspro-review-notice').slideUp('fast');
            }
        });

    });

})( jQuery );