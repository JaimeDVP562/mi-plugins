/**
 * DeepSeek Integration Script
 */
(function ($) {
    'use strict';

    // Prefix used for DeepSeek integration
    var _prefix = 'automatorwp_deepseek_';

    // Handle the Authorization button click
    $('body').on('click', '#' + _prefix + 'authorize_btn', function (e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();

        // Get the API token from the input field
        var token = $('#' + _prefix + 'token').val();

        // Manage the response wrapper
        var response_wrap = $('#' + _prefix + 'response');

        if (!response_wrap.length) {
            button.after('<div id="' + _prefix + 'response" style="display: none; margin-top: 10px; padding: 10px; border-radius: 4px;"></div>');
            response_wrap = $('#' + _prefix + 'response');
        }

        // Validate that the token is not empty
        if (!token || token.trim().length === 0) {
            response_wrap.attr('class', 'automatorwp-notice-error')
                .html('API token is required to connect with DeepSeek')
                .slideDown('fast');
            return;
        }

        response_wrap.slideUp('fast').attr('class', '');

        // Show the spinner for visual feedback
        if (!wrapper.find('.spinner').length) {
            button.after('<span class="spinner is-active" style="float: none; vertical-align: middle;"></span>');
        }

        button.prop('disabled', true);

        // Perform the AJAX request for authorization
        $.post(
            automatorwp_deepseek.ajax_url,
            {
                action: 'automatorwp_deepseek_authorize',
                nonce: automatorwp_deepseek.nonce,
                token: token
            },
            function (response) {
                wrapper.find('.spinner').remove();

                var statusClass = response.success ? 'automatorwp-notice-success' : 'automatorwp-notice-error';
                var message = (response.data && response.data.message) ? response.data.message : 'Unknown error';

                response_wrap.addClass(statusClass).html(message).slideDown('fast');

                // Redirect if authorization was successful
                if (response.success && response.data.redirect_url) {
                    setTimeout(function () {
                        window.location.href = response.data.redirect_url;
                    }, 1000);
                    return;
                }

                button.prop('disabled', false);
            }
        ).fail(function () {
            wrapper.find('.spinner').remove();
            button.prop('disabled', false);
            response_wrap.addClass('automatorwp-notice-error')
                .html('Server error. Please try again.')
                .slideDown('fast');
        });
    });

})(jQuery);