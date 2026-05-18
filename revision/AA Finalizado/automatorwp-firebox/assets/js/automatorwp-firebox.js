(function ($) {
    'use strict';

    if (typeof automatorwp_firebox === 'undefined') {
        return;
    }

    // =========================================================================
    // Conversion Trigger
    //
    // FireBox dispatches a CustomEvent named 'FireBoxConversion' (via its
    // emitEvent("conversion") call) when a user clicks a tracked Button or
    // Image block inside a popup. We relay the campaign ID to PHP via AJAX
    // so AutomatorWP can process the trigger.
    // =========================================================================

    function handleConversion(data) {
        if (!data || !data.campaignID) {
            return;
        }

        $.post(automatorwp_firebox.ajax_url, {
            action:      'automatorwp_firebox_conversion',
            nonce:       automatorwp_firebox.nonce,
            campaign_id: data.campaignID,
        });
    }

    document.addEventListener('FireBoxConversion', function (e) {
        handleConversion(e.detail || {});
    });

    // =========================================================================
    // Show Popup Action
    //
    // When the AutomatorWP "Show a popup to the user" action runs, it stores
    // the popup ID in a transient. On the next page load, scripts.php reads it
    // and passes it here via automatorwp_firebox.pending_popup. We then open
    // the popup using the FireBox JavaScript API: FireBox.getInstance(id).open()
    // =========================================================================

    var pendingPopup = parseInt(automatorwp_firebox.pending_popup, 10);

    if (pendingPopup > 0) {
        $(document).ready(function () {

            if (typeof FireBox === 'undefined' || typeof FireBox.getInstance !== 'function') {
                return;
            }

            var instance = FireBox.getInstance(pendingPopup);
            if (instance && typeof instance.open === 'function') {
                instance.open();
            }
        });
    }

})(jQuery);
