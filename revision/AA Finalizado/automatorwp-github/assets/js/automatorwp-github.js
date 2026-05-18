(function( $ ) {
    var prefix  = 'automatorwp-github-';
    var _prefix = 'automatorwp_github_';
    
    // On click authorize button
    $('body').on('click' , '#'+_prefix + 'authorize', function(e){
        e.preventDefault();
        console.log("Botón clicado");

         var button = $(this);
        var wrapper = button.parent();

       var username = $('#' +_prefix + 'username').val();
            var url = 'https://api.github.com/user';
            var key = $('#' + _prefix + 'key').val();
            var webhook = $('#'+_prefix+'webhook_token').val();
            console.log('variables creadas exitosamente');

        // Check if response div exists
        var response_wrap = wrapper.find('#' + _prefix + 'response');
        
        if(  response_wrap.length === 0 ){
            wrapper.append( '<div id="' + _prefix + 'response" style="display: none; margin-top: 10px;"></div>' );
            response_wrap = wrapper.find('#' + _prefix + 'response');
        }

        // Show error message if not correctly configured
        if( webhook.length === 0 || username.length === 0 || key.length === 0 ){
            console.log('No ha detectado algo');
            response_wrap.addClass( 'automatorwp-notice-error' );
            response_wrap.html( 'All fields are required to connect with Github' );
            response_wrap.slideDown('fast');
            wrapper.find('.spinner').remove();
            return;
        }

        response_wrap.slideUp('fast');
        response_wrap.attr('class', '');
        
        console.log('Creación Spinner');
        // Show spinner
        wrapper.append ('<span class="spinner is-active" style="float: none;"></span>');

        // Disable button
        button.prop('disabled',true);
        console.log('Inicio AJAX');
        $.post(
            ajaxurl,
            {
                action: 'automatorwp_github_authorize',
                nonce: automatorwp_github.nonce,
                url: url,
                key: key,
                username: username,
                webhook_token: webhook
            },
            function ( response ){
                console.log('Respuesta AJAX')
                // Add class automator-notice-success on successful unlock, if not will add the class automator-notice-error
                response_wrap.addClass( 'automatorwp-notice-' + ( response.success === true ? 'success' : 'error' ) );
                response_wrap.html( ( response.data.message !== undefined ? response.data.message : response.data ) );
                response_wrap.slideDown('fast');

                console.log('Borrado Spinner')
                // Hide spinner
                wrapper.find('.spinner').remove();

                // Redirect on success
                if( response.success === true && response.data.redirect_url !== undefined ){
                    window.location = response.data.redirect_url;
                    return;
                }

                // Enable button
                button.prop('disabled',false);
            }
        ).fail(function(jqXHR, textStatus, errorThrown) {

    wrapper.find('.spinner').remove();
    button.prop('disabled', false);

    response_wrap.addClass('automatorwp-notice-error');
    response_wrap.html('AJAX error: ' + textStatus);
    response_wrap.slideDown('fast');

});

    });

    // View webhook on triggers
    $('body').on('click', '.automatorwp-github-view-webhook', function(e){
        e.preventDefault();

        $(this).parent().next().slideDown('fast');
    });

} )( jQuery );