(function ( $ ) {

    // Form submit
    $('body').on( "submit", ".bbforms-form", function(e){
        e.preventDefault(e);

        var form = $(this);

        bbforms_validate_form( form );
    });

    // On interact with a submit button
    $('body').on( "mouseenter mouseover focus click mousedown mouseup", '.bbforms-form *[type="submit"]', function(e){
        var $this = $(this);

        if(  $this.attr( 'data-active' ) === 1 ) {
            return;
        }

        var form = $this.closest('.bbforms-form');

        form.find('*[type="submit"][data-active="1"]').attr( 'data-active', 0 );
        $this.attr( 'data-active', 1 );
    });

    // on change any form input
    $('body').on("change", '.bbforms-form input, .bbforms-form textarea, .bbforms-form select',function() {
        var field = $(this);
        var form = field.closest('.bbforms-form');
        var form_data = new FormData(form[0]);

        bbforms_is_field_valid( field, form, form_data )
    });

    // Reset > on click > clear the form
    $('body').on("click", '.bbforms-form input[type="reset"]', function(e) {
        e.preventDefault();

        var field = $(this);
        var form = field.closest('.bbforms-form');

        bbforms_clear_form( form );
    });

    // Range > on input > update preview
    $('body').on("input", '.bbforms-form input[type="range"]', function(e) {
        var field = $(this);
        var row = field.closest('.bbforms-field');
        var preview = row.find('.bbforms-field-range-output');

        if( preview ) {
            preview.html( field.val() );
        }

    });

    // Fire input event on Range inputs
    $('.bbforms-form input[type="range"]').trigger('input');

})( jQuery );

function bbforms_validate_form( form ) {

    var $ = $ || jQuery;

    // Array with objects with name & value
    var form_data = new FormData(form[0]);
    var has_errors = false;
    var field_to_focus = undefined;

    form.find('input, textarea, select').each(function() {
        var field = $(this);

        if( ! bbforms_is_field_valid( field, form, form_data ) ) {
            has_errors = true;

            if( field_to_focus === undefined ) {
                field_to_focus = field;
            }
        }
    });

    if( has_errors ) {
        field_to_focus.focus();
        return;
    }

    // Clear form messages
    bbforms_clear_form_messages( form );

    // Search any active submit button
    var submit_button = form.find('*[type="submit"][data-active="1"]');

    // If name is not empty, append it to the form data
    if( submit_button.length && submit_button.attr('name') !== undefined && submit_button.attr('name') !== '' ) {
        form_data.append( submit_button.attr('name'), submit_button.val() );
    }

    // Show the spinner
    form.find('.bbforms-spinner').addClass('is-active');
    // Disable submit input
    form.find('*[type="submit"]').prop('disabled', true);
    form.find('*[type="submit"]').attr('aria-disabled', true);

    // Submit the form through ajax
    $.ajax({
        url: bbforms.ajaxurl + "?action=bbforms_form_submit&nonce=" + bbforms.nonce,
        method: 'POST',
        cache: false,
        processData: false,
        contentType: false,
        data: form_data,
        success: function( response ) {
            bbforms_process_ajax_response( form, form_data, response );
        },
        error: function( response ) {
            bbforms_process_ajax_response( form, form_data, response );
        }
    });

}

function bbforms_process_ajax_response( form, form_data, response ) {

    if( ! ( 'data' in response ) ) {
        // Some error happened, so lets show the response through console and make the form available again
        console.log(response);

        bbforms_add_form_message( form, bbforms.unknown_submit_error_message, 'error' );

        // Hide the spinner
        form.find('.bbforms-spinner').removeClass('is-active');
        // Enable submit input
        form.find('*[type="submit"]').prop('disabled', false);
        form.find('*[type="submit"]').attr('aria-disabled', false);

        /**
         * Form submit error
         *
         * @since 1.0.0
         *
         * @selector    .bbforms-form
         * @event       bbforms_form_submit_error
         *
         * @param Object    form        The form element
         * @param Object    form_data   The form data
         * @param Object    response    The submit response
         */
        form.trigger( 'bbforms_form_submit_error', [ form, form_data, response ] );

        /**
         * Form unknown submit error
         *
         * @since 1.0.0
         *
         * @selector    .bbforms-form
         * @event       bbforms_form_submit_unknown_error
         *
         * @param Object    form        The form element
         * @param Object    form_data   The form data
         * @param Object    response    The submit response
         */
        form.trigger( 'bbforms_form_submit_unknown_error', [ form, form_data, response ] );

        return;

    }

    // Fire events
    if( response.success ) {
        /**
         * Form submit success
         *
         * @since 1.0.0
         *
         * @selector    .bbforms-form
         * @event       bbforms_form_submit_success
         *
         * @param Object    form        The form element
         * @param Object    form_data   The form data
         * @param Object    response    The submit response
         */
        form.trigger( 'bbforms_form_submit_success', [ form, form_data, response ] );
    } else {
        /**
         * Form submit error
         *
         * @since 1.0.0
         *
         * @selector    .bbforms-form
         * @event       bbforms_form_submit_error
         *
         * @param Object    form        The form element
         * @param Object    form_data   The form data
         * @param Object    response    The submit response
         */
        form.trigger( 'bbforms_form_submit_error', [ form, form_data, response ] );
    }

    // Show form messages
    if( 'messages' in response.data ) {
        for (const [key, message] of Object.entries( response.data.messages ) ) {
            bbforms_add_form_message( form, message.text, message.type );
        }
    }

    // Show field messages
    if( 'field_messages' in response.data ) {
        var focused_field = false;

        for (const [key, message] of Object.entries( response.data.field_messages ) ) {

            var row = form.find(".bbforms-" + key + "-field-row");
            var field_row = form.find(".bbforms-" + key + "-field");

            bbforms_add_field_error_message( field_row, message );

            // Focus the first field with error
            if( ! focused_field ) {
                form.find('*[name="' + key + '"]').focus();
                focused_field = true;
            }
        }
    }

    // Process options
    if( 'options' in response.data ) {
        if( response.success && response.data.options.hide_form_on_success ) {
            form.slideUp('fast');
        }

        if( response.success && response.data.options.clear_form_on_success ) {
            bbforms_clear_form( form );
        }
    }

    // Process actions
    if( 'actions' in response.data ) {
        if( 'redirect' in response.data.actions ) {
            window.location.href = response.data.actions.redirect.to;
        }
    }


    // Hide the spinner
    form.find('.bbforms-spinner').removeClass('is-active');
    // Enable submit input
    form.find('*[type="submit"]').prop('disabled', false);
    form.find('*[type="submit"]').attr('aria-disabled', false);

}

function bbforms_is_field_valid( field, form, form_data ) {

    var $ = $ || jQuery;

    var row = field.closest('.bbforms-field-row');
    var field_row = field.closest('.bbforms-field');

    var tag = field.prop("tagName");
    var value = form_data.get( field.attr('name') );
    var type = "";
    var required = field.attr("required");

    required = ( required !== undefined && required !== false );

    if( tag === 'TEXTAREA' ) {
        type = 'textarea';
    } else {
        type = field.attr("type");
    }

    // Checkbox specific
    if( type === 'checkbox' ) {
        required = field_row.hasClass('bbforms-field-required');
    }

    // Required
    if( required && ( value === '' || value === null ) ) {
        bbforms_add_field_error_message( field_row, bbforms.error_messages.required_error );
        return false;
    }

    // Required file
    if( required && type === 'file' && ! bbforms_has_file( field ) ) {
        bbforms_add_field_error_message( field_row, bbforms.error_messages.required_error );
        return false;
    }

    // Only apply field value check if value has something (required already checks if has value or not)
    if( value !== '' && value !== null ) {
        // Email
        if( type === 'email' && ! bbforms_is_valid_email( value ) ) {
            bbforms_add_field_error_message( field_row, bbforms.error_messages.email_error );
            return false;
        }

        // Email
        if( type === 'url' && ! bbforms_is_valid_url( value ) ) {
            bbforms_add_field_error_message( field_row, bbforms.error_messages.url_error );
            return false;
        }

        // Time value between 00:00 & 23:59
        if( type === 'time' && ! bbforms_is_valid_time( value ) ) {
            bbforms_add_field_error_message( field_row, bbforms.error_messages.time_error );
            return false;
        }

        // Date
        if( type === 'date' && ! bbforms_is_valid_date( value ) ) {
            bbforms_add_field_error_message( field_row, bbforms.error_messages.date_error );
            return false;
        }

        // File

        if( type === 'file' && bbforms_has_file( field ) ) {
            var file = field[0].files[0];

            // File Type
            if( ! bbforms_is_valid_file_type( field ) ) {
                bbforms_add_field_error_message( field_row, bbforms.error_messages.file_type_error );
                return false;
            }

            // File Size
            var min = field.attr('min');
            var max = field.attr('max');

            // Min file size
            if( min !== undefined ) {
                min = parseInt( min );

                if( file.size < min ) {
                    bbforms_add_field_error_message( field_row, bbforms.error_messages.file_size_min_error );
                    return false;
                }
            }

            // Max file size
            if( max !== undefined ) {
                max = parseInt( max );

                if( file.size > max ) {
                    bbforms_add_field_error_message( field_row, bbforms.error_messages.file_size_max_error );
                    return false;
                }
            }
        }



        // min & max + minlength & maxlength
        var min = ( field.attr("min") !== undefined ? field.attr("min") : '' );
        var minLength = ( field.attr("minlength") !== undefined ? field.attr("minlength") : '' );
        var max = ( field.attr("max") !== undefined ? field.attr("max") : '' );
        var maxLength = ( field.attr("maxLength") !== undefined ? field.attr("maxLength") : '' );

        min = ( minLength !== '' ? minLength : min );
        max = ( maxLength !== '' ? maxLength : max );
        var length = 0;
        var min_value = min;
        var max_value = min;

        if( min !== '' || max !== '' ) {
            // Get the value
            switch( type ) {
                case 'date':
                    // Date (format Y-m-d)
                    length = Date.parse( value );
                    min_value = ( min !== '' ? Date.parse( min ) : '' );
                    max_value = ( max !== '' ? Date.parse( max ) : '' );
                    break;
                case 'time':
                    // Time (format H:i)
                    length = Date.parse( '2025-06-06 ' + value );
                    min_value = ( min !== '' ? Date.parse( '2025-06-06 ' + min ) : '' );
                    max_value = ( max !== '' ? Date.parse( '2025-06-06 ' + max ) : '' );
                    break;
                case 'number':
                case 'range':
                    // Number or range (check as float)
                    length = parseFloat( value );
                    min_value = ( min !== '' ? parseFloat( min ) : '' );
                    max_value = ( max !== '' ? parseFloat( max ) : '' );
                    break;
                case 'file':
                    // Prevent to pass this check
                    length = 0;
                    min_value = 0;
                    max_value = 0;
                    min = '';
                    max = '';
                default:
                    length = value.length;
                    min_value = ( min !== '' ? parseInt( min ) : '' );
                    max_value = ( max !== '' ? parseInt( max ) : '' );
                    break;
            }

            if( min !== '' && max === '' ) {
                // Check min
                if( length < min_value ) {
                    bbforms_add_field_error_message( field_row, bbforms.error_messages.min_error.replace( '%s', min ) );
                    return false;
                }
            } else if( min === '' && max !== '' ) {
                // Check max
                if( length > max_value ) {
                    bbforms_add_field_error_message( field_row, bbforms.error_messages.max_error.replace( '%s', max ) );
                    return false;
                }
            } else if( min !== '' && max !== '' ) {
                // Check min & max
                if( length < min_value || length > max_value ) {
                    bbforms_add_field_error_message( field_row, bbforms.error_messages.min_max_error.replace( '%1$s', min ).replace( '%2$s', max ) );
                    return false;
                }
            }
        }



        var pattern = ( field.attr("pattern") !== undefined ? field.attr("pattern") : '' );

        // Fields with pattern attribute
        if( pattern !== '' && ! bbforms_matches_pattern( value, pattern ) ) {
            bbforms_add_field_error_message( field_row, bbforms.error_messages.pattern_error );
            return false;
        }

    }

    // Remove error element if everything is fine
    var error_element = field_row.find('.bbforms-error');

    if( error_element.length !== 0 ) {
        error_element.remove();
    }

    return true;

}

/**
 * Add field error message
 *
 * @since 1.0.0
 *
 * @param row
 * @param message
 */
function bbforms_add_field_error_message( row, message ) {

    var error_element = row.find('.bbforms-error');

    if( error_element.length === 0 ) {
        row.append('<div class="bbforms-error"></div>');
        error_element = row.find('.bbforms-error');
    }

    error_element.html(message);

}

/**
 * Add form message
 *
 * @since 1.0.0
 *
 * @param form
 * @param message
 * @param type
 */
function bbforms_add_form_message( form, message, type ) {

    var container = form.closest('.bbforms');

    var messages = container.find('.bbforms-messages');

    if( messages.length === 0 ) {
        container.append('<div class="bbforms-messages"></div>');
        messages = container.find('.bbforms-messages');
    }

    messages.append('<div class="bbforms-message bbforms-' + type + '-message">' + message + '</div>');

}

/**
 * Clear form messages
 *
 * @since 1.0.0
 *
 * @param form
 */
function bbforms_clear_form_messages( form ) {
    form.closest('.bbforms').find('.bbforms-messages').remove();

}

/**
 * Clear form fields
 *
 * @since 1.0.0
 *
 * @param form
 */
function bbforms_clear_form( form ) {

    // Clear field values
    form.find('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="reset"]):not([type="button"]), textarea, select').val('');

    // Clear checked inputs
    form.find('input:checked').prop('checked', false);

}

/**
 * Check if value is a valid email
 *
 * @since 1.0.0
 *
 * @param value
 */
function bbforms_is_valid_email( value ) {
    return value.match(
        /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    );
}

/**
 * Check if value is a valid time
 *
 * @since 1.0.0
 *
 * @param value
 */
function bbforms_is_valid_time( value ) {
    return value.match(/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/);
}

/**
 * Check if value is a valid date
 *
 * @since 1.0.0
 *
 * @param value
 */
function bbforms_is_valid_date( value ) {
    return ! isNaN( new Date( value ) );
}

/**
 * Check if value is a valid URL
 *
 * @since 1.0.0
 *
 * @param value
 */
function bbforms_is_valid_url( value ) {
    try{
        new URL( value );
        return true;
    } catch(e) {
        return false;
    }
}

/**
 * Check if field is a file
 *
 * @since 1.0.0
 *
 * @param field
 */
function bbforms_has_file( field ) {
    if( field[0].files.length === 0 ) {
        return false;
    }

    return true;
}

/**
 * Check if field that has a file is an allowed one (from the accept attribute)
 *
 * @since 1.0.0
 *
 * @param field
 */
function bbforms_is_valid_file_type( field ) {
    if( field[0].files.length === 0 ) {
        return true;
    }

    var accept = ( field.attr("accept") !== undefined ? field.attr("accept") : '' );

    if( accept.length === 0 ) {
        return true;
    }

    var file = field[0].files[0];
    var ext = file.name.split('.').pop();

    if( accept.includes( '.' + ext ) ) {
        return true;
    }

    if( accept.includes( file.type ) ) {
        return true;
    }

    var main_type = file.type.split('/')[0];

    if( accept.includes( main_type + '/*' ) ) {
        return true;
    }

    return false;
}

/**
 * Check if value matches with a pattern
 *
 * @since 1.0.0
 *
 * @param value
 * @param pattern
 */
function bbforms_matches_pattern( value, pattern ) {
    return value.match( pattern )
}