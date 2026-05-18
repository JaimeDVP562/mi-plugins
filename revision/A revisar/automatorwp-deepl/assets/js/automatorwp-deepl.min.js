( function( $ ) {

    'use strict';

    /**
     * Handle "Save credentials" button click for DeepL settings
     */
    $( document ).on( 'click', '#automatorwp_deepl_authorize', function( e ) {

        e.preventDefault();

        var $btn    = $( this );
        var $notice = $btn.siblings( '.automatorwp-notice-success, .automatorwp-notice-error' );

        // Get field values
        var api_key = $( '#automatorwp_deepl_api_key' ).val();

        if ( ! api_key ) {
            alert( 'Please enter your DeepL API Key.' );
            return;
        }

        // Disable button while processing
        $btn.prop( 'disabled', true ).text( 'Connecting...' );
        $notice.remove();

        $.ajax( {
            url:  ajaxurl,
            type: 'POST',
            data: {
                action: 'automatorwp_deepl_authorize',
                nonce:  automatorwp_deepl.nonce,
                api_key: api_key,
            },
            success: function( response ) {

                if ( response.success ) {
                    $btn.after(
                        '<div class="automatorwp-notice-success" style="margin-top:8px;">' +
                        response.data.message +
                        '</div>'
                    );
                    // Redirect to reload the settings page with saved state
                    setTimeout( function() {
                        window.location.href = response.data.redirect_url;
                    }, 1200 );
                } else {
                    $btn.after(
                        '<div class="automatorwp-notice-error" style="margin-top:8px;">' +
                        response.data.message +
                        '</div>'
                    );
                    $btn.prop( 'disabled', false ).text( 'Save credentials' );
                }

            },
            error: function() {
                $btn.after(
                    '<div class="automatorwp-notice-error" style="margin-top:8px;">' +
                    'Connection error. Please try again.' +
                    '</div>'
                );
                $btn.prop( 'disabled', false ).text( 'Save credentials' );
            }
        } );

    } );

} )( jQuery );
