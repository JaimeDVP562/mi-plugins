/**
 * AutomatorWP Manual Triggers - Frontend Shortcode JavaScript
 *
 * Handles the click event on the shortcode button/link to fire a manual trigger
 * via AJAX from the frontend.
 *
 * @package AutomatorWP\Manual_Triggers
 * @since   1.0.0
 */
(function() {

    document.addEventListener('click', function(e) {

        var el = e.target.closest('.automatorwp-manual-trigger-btn');

        if ( ! el ) return;

        e.preventDefault();

        var triggerId = el.getAttribute('data-trigger');
        var userId    = el.getAttribute('data-user');
        var nonce     = el.getAttribute('data-nonce');

        // Disable button while processing
        el.disabled = true;
        var originalText = el.textContent;
        el.textContent = automatorwp_manual_triggers_shortcode.i18n.running;

        var formData = new FormData();
        formData.append('action', 'automatorwp_manual_trigger_run');
        formData.append('trigger_id', triggerId);
        formData.append('user_id', userId);
        formData.append('nonce', nonce);

        fetch( automatorwp_manual_triggers_shortcode.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                el.textContent = automatorwp_manual_triggers_shortcode.i18n.done;
            } else {
                el.textContent = automatorwp_manual_triggers_shortcode.i18n.error;
            }
            setTimeout(function() {
                el.textContent = originalText;
                el.disabled = false;
            }, 2000);
        })
        .catch(function() {
            el.textContent = originalText;
            el.disabled = false;
        });

    });

})();
