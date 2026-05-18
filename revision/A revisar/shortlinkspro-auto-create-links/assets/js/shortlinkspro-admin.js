(function ( $ ) {

    // Duplicate slug check
    $(".shortlinkspro-form  #slug").on("keyup", function() {
        var $this = $(this);
        var td = $this.closest('.cmb-td');

        // Remove error class
        td.removeClass('shortlinkspro-has-error');

        // Hide error message
        if( td.find('.shortlinkspro-error-message').length ) {
            td.find('.shortlinkspro-error-message').fadeOut('fast');
        }

        // Enable save button in case it was disabled
        shortlinkspro_enable_link_save_button();
    });

    $(".shortlinkspro-form #slug").on("change", function() {
        var $this = $(this);
        var td = $this.closest('.cmb-td');

        $.ajax({
            url: shortlinkspro_admin.ajaxurl,
            method: 'POST',
            data: {
                action: 'shortlinkspro_get_link_by_slug',
                nonce: shortlinkspro_admin.nonce,
                slug: $this.val(),
            },
            success: function( response ) {

                if( response.success ) {

                    var id = parseInt( $('#object_id').val() );

                    // Another link found with the same slug
                    if( id !== parseInt( response.data.link.id ) ) {

                        // Add error class
                        td.addClass('shortlinkspro-has-error');

                        // Append error message wrapper
                        if( td.find('.shortlinkspro-error-message').length === 0 ) {
                            td.append('<p class="shortlinkspro-error-message" style="display: none;">' + shortlinkspro_admin.duplicated_slug_text + '</p>')
                        }

                        // Show error message
                        td.find('.shortlinkspro-error-message').fadeIn('fast');

                        // Disable save button
                        shortlinkspro_disable_link_save_button();

                    }

                } else {
                    // Link by slug not found
                }
            },
            error: function( response ) {
                // Error to call ajax
            }
        });

    });

    // Copy to clipboard
    $(".shortlinkspro-copy-to-clipboard").on("click", function() {

        var $this = $(this);

        // get the URL
        if( $this.attr('data-url').length ) {
            var url = $this.attr('data-url');
        } else {
            var url = shortlinkspro_admin.site_url + $('#slug').val();
        }

        // Copy to clipboard
        navigator.clipboard.writeText( url );

        // Update tooltip desc
        $this.find('.cmb-tooltip-desc').html( shortlinkspro_admin.copied_text );

        setTimeout(function() {
            $this.find('.cmb-tooltip-desc').html( shortlinkspro_admin.copy_text );
        }, 2000 );
    });

    // UTM Builder dialog
    $('body').on('click', 'a.shortlinkspro-utm-builder', function(e) {
        e.preventDefault();

        // Update dialog fields
        var dialog = $('.shortlinkspro-utm-builder-dialog');
        var url_field = $('.shortlinkspro-form #url');
        var td = url_field.closest('.cmb-td');
        var invalid_url = true;
        var url = url_field.val();

        if( ! url.startsWith('http') ) {
            url = 'https://' + url;
        }

        try {
            url = new URL( url );
            invalid_url = false;
        } catch (error) {

        }

        if( invalid_url ) {
            // Append error message wrapper
            if( td.find('.shortlinkspro-error-message').length === 0 ) {
                td.append('<p class="shortlinkspro-error-message" style="display: none;">' + shortlinkspro_admin.invalid_url_text + '</p>')
            }

            // Show error message
            td.find('.shortlinkspro-error-message').fadeIn('fast');

            return;
        }

        // Loop fields to update their values
        dialog.find('input[type=text]').each(function() {
            var field = $(this);

            field.val( url.searchParams.get( field.attr('id') ) );
        });

        // Show dialog
        $('.shortlinkspro-utm-builder-dialog').dialog({
            dialogClass: 'shortlinkspro-dialog',
            closeText: '',
            show: { effect: 'fadeIn', duration: 200 },
            hide: { effect: 'fadeOut', duration: 200 },
            resizable: false,
            height: 'auto',
            width: 500,
            modal: true,
            draggable: false,
            closeOnEscape: false,
        });

    });

    $('.shortlinkspro-form #url').on('keyup', function(e) {
        var td = $(this).closest('.cmb-td');

        // Hide error message
        if( td.find('.shortlinkspro-error-message').length ) {
            td.find('.shortlinkspro-error-message').fadeOut('fast');
        }
    });

    // UTM Builder save button
    $('body').on('click', '.shortlinkspro-utm-builder-dialog .shortlinkspro-utm-builder-dialog-save', function(e) {

        var $this = $(this);
        var dialog = $this.closest('.shortlinkspro-utm-builder-dialog');
        var url_field = $('.shortlinkspro-form #url');
        var url = url_field.val();

        if( ! url.startsWith('http') ) {
            url = 'https://' + url;
        }

        url = new URL( url );

        // Remove UTM parameters from the URL
        url.searchParams.delete('utm_campaign');
        url.searchParams.delete('utm_medium');
        url.searchParams.delete('utm_source');
        url.searchParams.delete('utm_term');
        url.searchParams.delete('utm_content');

        // Loop fields and get the UTM vars to add them to the URL
        dialog.find('input[type=text]').each(function() {
            var field = $(this);

            if( field.val() !== '' ) {
                url.searchParams.set( field.attr('id'), field.val() );
            }
        });

        // Update the URL
        url_field.val(url.toString())

        dialog.dialog('close');

    });

    // UTM Builder cancel button
    $('body').on('click', '.shortlinkspro-utm-builder-dialog .shortlinkspro-utm-builder-dialog-cancel', function(e) {

        var $this = $(this);
        var dialog = $this.closest('.shortlinkspro-utm-builder-dialog');

        dialog.dialog('close');

    });

    // Settings

    // Slug prefix preview
    $(".shortlinkspro-slug-prefix-preview").html( $("#slug_prefix").val() );

    $("#slug_prefix").on("keyup", function() {
        $(".shortlinkspro-slug-prefix-preview").html( $(this).val() );
    });

    // Slug length preview
    var random_slug = 'sH0l1NkSPR01Sth3M0sT4w3s0M3pLug1N0fTH3w0rlD';

    $(".shortlinkspro-slug-length-preview").html( shortlinkspro_static_random_slug( random_slug, $("#slug_length").val() ) );

    $("#slug_length").on("change keyup", function() {
        $(".shortlinkspro-slug-length-preview").html( shortlinkspro_static_random_slug( random_slug, $(this).val() ) );
    });

})( jQuery );

function shortlinkspro_disable_link_save_button() {

    var $ = jQuery || $;

    // $('#publishing-action .spinner').addClass('is-active');
    $('#publishing-action #ct-save').attr('disabled', true);

}

function shortlinkspro_enable_link_save_button() {

    var $ = jQuery || $;

    // $('#publishing-action .spinner').removeClass('is-active');
    $('#publishing-action #ct-save').attr('disabled', false);

}

function shortlinkspro_static_random_slug( random_slug, length ) {

    if( length > random_slug.length ) {
        random_slug += random_slug;
        return shortlinkspro_static_random_slug( random_slug, length );
    }

    return random_slug.substring( 0, length );

}

function shortlinkspro_random_slug( length ) {

    let result = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const characters_length = characters.length;
    let counter = 0;

    while (counter < length) {
        result += characters.charAt(Math.floor(Math.random() * characters_length));
        counter += 1;
    }

    return result;

}