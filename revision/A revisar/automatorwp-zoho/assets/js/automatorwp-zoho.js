/**
 * Zoho CRM Admin Interaction
 * * @package     AutomatorWP\Integrations\Zoho\Assets
 * @since       1.0.0
 */
(function ($) {

    var prefix = 'automatorwp_zoho_';

    /**
     * Get or create the response wrapper for AJAX feedback
     */
    function getResponseWrap(button) {
        var $wrapper = $(button).parent();
        var $response = $wrapper.find('#' + prefix + 'response');

        if (!$response.length) {
            $wrapper.append('<div id="' + prefix + 'response" style="display:none; margin-top:10px;"></div>');
            $response = $wrapper.find('#' + prefix + 'response');
        }

        return $response;
    }

    /**
     * Show formatted message to the user
     */
    function showMessage($response, ok, msg) {
        $response.stop(true, true);
        $response.removeClass('automatorwp-notice-success automatorwp-notice-error');
        $response.addClass('automatorwp-notice-' + (ok ? 'success' : 'error'));
        $response.html(msg || '');
        $response.slideDown('fast');
    }

    /**
     * Toggle the delete button state
     */
    function setDeleteButtonEnabled(enabled) {
        var $delete = $('#' + prefix + 'delete_credentials');

        if (!$delete.length) {
            return;
        }

        $delete.prop('disabled', !enabled);

        if (enabled) {
            $delete.css({ opacity: '', cursor: '' });
        } else {
            $delete.css({ opacity: 0.5, cursor: 'not-allowed' });
        }
    }

    // --- AUTHORIZE / CONNECT ---
    $('body').on('click', '#' + prefix + 'authorize', function (e) {
        e.preventDefault();

        var $button = $(this);
        var $wrapper = $button.parent();
        var $response = getResponseWrap(this);

        // Capture Zoho fields
        var access_token = $('#' + prefix + 'access_token').val();
        var region = $('#' + prefix + 'region').val();

        // Validate fields before sending
        if (!access_token || access_token.length === 0) {
            showMessage($response, false, 'The Access Token is required to connect with Zoho CRM');
            return;
        }

        $response.removeClass('automatorwp-notice-success automatorwp-notice-error').slideUp('fast');

        // Show spinner
        $wrapper.find('.spinner').remove();
        $wrapper.append('<span class="spinner is-active" style="float: none; margin-left: 10px;"></span>');

        // Disable button
        $button.prop('disabled', true);

        $.post(
            automatorwp_zoho.ajaxurl,
            {
                action: 'automatorwp_zoho_authorize',
                nonce: automatorwp_zoho.nonce,
                access_token: access_token,
                region: region
            },
            function (response) {
                var ok = (response && response.success === true);
                var msg = (response && response.data && response.data.message !== undefined) ? response.data.message : (response ? response.data : '');

                showMessage($response, ok, msg);
                $wrapper.find('.spinner').remove();

                if (ok) {
                    setDeleteButtonEnabled(true);
                    // Redirect to the Zoho tab to refresh the UI
                    if (response.data && response.data.redirect_url) {
                        window.location = response.data.redirect_url;
                    }
                } else {
                    $button.prop('disabled', false);
                }
            }
        ).fail(function () {
            $wrapper.find('.spinner').remove();
            showMessage($response, false, 'Request failed. Please check your connection and try again.');
            $button.prop('disabled', false);
        });
    });

    // --- DELETE CREDENTIALS / DISCONNECT ---
    $('body').on('click', '#' + prefix + 'delete_credentials', function (e) {
        e.preventDefault();

        if ($(this).prop('disabled')) return;

        var $button = $(this);
        var $wrapper = $button.parent();
        var $response = getResponseWrap(this);

        // Confirm Action
        if (!window.confirm('Are you sure you want to disconnect from Zoho CRM?')) {
            return;
        }

        $response.removeClass('automatorwp-notice-success automatorwp-notice-error').slideUp('fast');

        // Spinner
        $wrapper.find('.spinner').remove();
        $wrapper.append('<span class="spinner is-active" style="float:none; margin-left: 10px;"></span>');

        $button.prop('disabled', true);

        $.post(
            automatorwp_zoho.ajaxurl,
            {
                action: 'automatorwp_zoho_delete_credentials',
                nonce: automatorwp_zoho.nonce
            },
            function (response) {
                var ok = (response && response.success === true);

                if (ok) {
                    setDeleteButtonEnabled(false);
                    $('#' + prefix + 'access_token').val('');
                    // Reload to refresh the settings UI
                    window.location.reload();
                } else {
                    $wrapper.find('.spinner').remove();
                    $button.prop('disabled', false);
                    showMessage($response, false, 'Failed to delete credentials.');
                }
            }
        ).fail(function () {
            $wrapper.find('.spinner').remove();
            showMessage($response, false, 'Request failed.');
            $button.prop('disabled', false);
        });
    });

})(jQuery);