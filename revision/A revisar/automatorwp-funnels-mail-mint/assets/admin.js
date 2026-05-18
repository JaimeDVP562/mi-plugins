(function($){
    $(document).on('click', '#automatorwp_mailmint_authorize_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        $btn.prop('disabled', true).text('Validating...');
        var auth_method = $('select[name="automatorwp_mailmint_auth_method"]').val();
        var api_key = $('input[name="automatorwp_mailmint_api_key"]').val();
        var api_base = $('input[name="automatorwp_mailmint_api_base"]').val();
        $.post(automatorwp_mailmint_vars.ajax_url, {
            action: 'automatorwp_mailmint_authorize',
            nonce: automatorwp_mailmint_vars.nonce,
            auth_method: auth_method,
            api_key: api_key,
            api_base: api_base
        }, function(response){
            if ( response.success ) {
                alert(response.data.message || 'OK');
            } else {
                var msg = (response.data && response.data.message) ? response.data.message : (response.data || 'Error');
                alert('Error: ' + msg);
            }
            $btn.prop('disabled', false).text('Save / Validate');
        }).fail(function(){
            alert('Request failed');
            $btn.prop('disabled', false).text('Save / Validate');
        });
    });
})(jQuery);