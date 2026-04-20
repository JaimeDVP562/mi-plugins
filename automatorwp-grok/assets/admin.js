( function() {
    document.addEventListener( 'DOMContentLoaded', function() {
        var btn = document.getElementById( 'automatorwp-grok-verify' );
        if ( ! btn ) {
            return;
        }

        btn.addEventListener( 'click', function() {
            var nonce = btn.getAttribute( 'data-nonce' );
            var resultEl = document.getElementById( 'automatorwp-grok-verify-result' );
            resultEl.textContent = 'Verifying...';

            var data = new FormData();
            data.append( 'action', 'automatorwp_grok_verify' );
            data.append( 'nonce', nonce );

            fetch( ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: data,
            } ).then( function( res ) {
                return res.json();
            } ).then( function( json ) {
                if ( json.success ) {
                    resultEl.textContent = json.data.message || 'Connected';
                } else {
                    resultEl.textContent = ( json.data && json.data.message ) ? json.data.message : ( json.message || 'Error' );
                }
            } ).catch( function() {
                resultEl.textContent = 'Error verifying API key.';
            } );
        } );
    } );
} )();
