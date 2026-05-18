/* AutomatorWP - FluentCart | Admin JS */
(function ($) {
    'use strict';

    $(document).ready(function () {

        // Check connection status button on the settings page.
        $(document).on('click', '#automatorwp_fluentcart_status', function (e) {
            e.preventDefault();

            var $btn = $(this);
            $btn.prop('disabled', true);

            $.post(automatorwp_fluentcart.ajaxurl, {
                action: 'automatorwp_fluentcart_check_status',
                nonce:  automatorwp_fluentcart.nonce
            }, function (response) {
                $btn.prop('disabled', false);
                alert(response.data.message);
            });
        });

    });

}(jQuery));
