(function ( $ ) {

    // -----------------------------------------------
    // Form template dialog
    // -----------------------------------------------


    // Form template dialog
    if( window.location.search.indexOf('bbforms_forms') !== -1 ) {
        // Show form template dialog on new form links
        $('body').on('click', 'a[href$="admin.php?page=add_bbforms_forms"], a[href$="admin.php?page=bbforms_forms#add-new"]', function(e) {
            e.preventDefault();

            $('.bbforms-form-template-dialog').dialog({
                dialogClass: 'bbforms-dialog',
                closeText: '',
                show: { effect: 'fadeIn', duration: 200 },
                hide: { effect: 'fadeOut', duration: 200 },
                resizable: false,
                height: 'auto',
                width: 800,
                modal: true,
                draggable: false,
                closeOnEscape: false,
            });

        });

        if( window.location.hash === '#add-new' ) {
            $('a[href$="admin.php?page=add_bbforms_forms"]').trigger('click');
        }

        // Add the Import Form button (since there are not hooks to append it)
        $('<input type="button" class="button button-primary bbforms-form-import" value="' + bbforms_admin.import_form_text + '">').insertAfter('.page-title-action');

    }

    // Form export dialog
    $('body').on('click', '.bbforms-form-export', function(e) {
        e.preventDefault();

        var $this = $(this);
        var value_container = $this.next('.bbforms-form-export-value');
        var form_id = $this.attr('id').replace('bbforms-form-export-', '');

        if( window.location.search.indexOf('edit_bbforms_forms') !== -1 ) {
            // On form edit screen, we need to update the form value from the editors values
            value_container.html(
                '<!---------- FORM ---------->' + "\n\n" +
                bbforms_remove_last_line_breaks( bbforms_form_editor.codemirror.getValue() ) + "\n\n" +
                '<!---------- ACTIONS ---------->' + "\n\n" +
                bbforms_remove_last_line_breaks( bbforms_actions_editor.codemirror.getValue() ) + "\n\n" +
                '<!---------- OPTIONS ---------->' + "\n\n" +
                bbforms_remove_last_line_breaks( bbforms_options_editor.codemirror.getValue() ) + "\n"
            );

        }

        var value = value_container.html();

        bbforms_form_export_editor_set_value( value );

        $('.bbforms-form-export-dialog').data('form', form_id );

        $('.bbforms-form-export-dialog').dialog({
            dialogClass: 'bbforms-dialog',
            closeText: '',
            show: { effect: 'fadeIn', duration: 200 },
            hide: { effect: 'fadeOut', duration: 200 },
            resizable: false,
            height: 'auto',
            width: 800,
            modal: true,
            draggable: false,
            closeOnEscape: false,
        });

    });

    // Form export dialog > Download
    $('body').on('click', '.bbforms-form-export-dialog .bbforms-dialog-button-download', function(e) {
        var value = bbforms_form_export_editor_get_value();
        var filename = 'form-' +  $('.bbforms-form-export-dialog').data('form') + '-export';

        bbforms_download_file( value, filename, 'txt' );
    });

    // Form export dialog > Copy
    $('body').on('click', '.bbforms-form-export-dialog .bbforms-dialog-button-copy', function(e) {
        var $this = $(this);
        var value = bbforms_form_export_editor_get_value();

        // Copy to clipboard
        navigator.clipboard.writeText( value );

        // Update tooltip desc
        $this.html( bbforms_admin.copied_text );

        setTimeout(function() {
            $this.html( bbforms_admin.copy_text );
        }, 2000 );

    });

    // Form import dialog
    $('body').on('click', '.bbforms-form-import', function(e) {
        e.preventDefault();

        $('.bbforms-form-import-dialog').dialog({
            dialogClass: 'bbforms-dialog',
            closeText: '',
            show: { effect: 'fadeIn', duration: 200 },
            hide: { effect: 'fadeOut', duration: 200 },
            resizable: false,
            height: 'auto',
            width: 800,
            modal: true,
            draggable: false,
            closeOnEscape: false,
        });

        // Clear the fields
        if( bbforms_form_import_editor_get_value() !== '' ) {
            bbforms_form_import_editor_set_value('' );
        }

        $('.bbforms-form-import-dialog input[type="file"]').val('');

    });

    // Form import dialog > Import
    $('body').on('click', '.bbforms-form-import-dialog .bbforms-dialog-button-import', function(e) {
        var $this = $(this);

        if( $this.prop('disabled') ) {
            return;
        }

        var dialog = $this.closest('.ui-dialog-content');
        var active_tab = dialog.find('.bbforms-dialog-tab-content-active');
        var import_from = 'code';

        if( active_tab.hasClass('bbforms-form-import-content-2') ) {
            import_from = 'file';
        }

        var formData = new FormData();

        formData.append( 'import_from', import_from );

        if( import_from === 'code' ) {
            value = bbforms_form_import_editor_get_value();

            // Bail if no code entered
            if( value === '' ) {
                return;
            }

            formData.append( 'code', value );
        } else {
            var files = $('.bbforms-form-import-dialog input[type="file"]')[0].files;

            if( files.length !== 1 ) {
                return;
            }

            var value = files[0];

            if( value.name.indexOf('.txt') === false || value.type !== 'text/plain' ) {
                return;
            }

            formData.append( 'file', value );
        }

        $this.prop('disabled', true );
        $this.text( bbforms_admin.importing_text );
        $('<span class="spinner is-active"></span>').insertAfter($this);

        $.ajax({
            url: bbforms_admin.ajaxurl + "?action=bbforms_import_form&nonce=" + bbforms_admin.nonce,
            method: 'POST',
            cache: false,
            processData: false,
            contentType: false,
            data: formData,
            success: function( response ) {
                if( response.success ) {
                    $this.text( bbforms_admin.import_done_text );
                    dialog.find('.spinner').remove();

                    // Redirect to the imported form
                    window.location.href = response.data;
                } else {
                    $this.prop('disabled', false );
                    dialog.find('.spinner').remove();
                    $this.closest('.bbforms-dialog-bottom').append('<div class="bbforms-form-import-response">' + response.data + '</div>');
                }
            },
            error: function( response ) {
                console.log(response);

                $this.prop('disabled', false );
                dialog.find('.spinner').remove();
                $this.closest('.bbforms-dialog-bottom').append('<div class="bbforms-form-import-response">' + response.data + '</div>');
            }
        });

    });

    var creating_from_template = false;

    // Form template selection
    $('body').on('click', '.bbforms-form-template a', function(e) {

        if( creating_from_template === true ) {
            e.preventDefault();
            return;
        }

        var $this = $(this);
        $this.find('.bbforms-form-template-title').append('<span class="spinner is-active"></span>');

        creating_from_template = true;

    });

    // Dialog close button
    $('body').on('click', '.bbforms-dialog-button-close', function(e) {

        var $this = $(this);
        var dialog = $this.closest('.ui-dialog-content');

        dialog.dialog('close');

    });

    // Dialog table toggle
    $('body').on('click', '.bbforms-attrs-table-toggle', function(e) {
        e.preventDefault();

        var $this = $(this);
        var table = $this.next('.bbforms-attrs-table');

        if( $this.data('active') ) {
            table.hide();
            $this.data( 'active', false );
            $this.html( bbforms_admin.show_attrs_text );
        } else {
            table.show();
            $this.data( 'active', true );
            $this.html( bbforms_admin.hide_attrs_text );
        }

    });

    // Dialog tabs
    $('body').on('click', '.bbforms-dialog-tab', function(e) {

        var $this = $(this);

        if( $this.hasClass('bbforms-dialog-tab-active' ) ) return;

        var tabs = $this.closest('.bbforms-dialog-tabs');
        var toggle = $($this.data('toggle'));
        var active = tabs.find( '.bbforms-dialog-tab-active' )
        var active_toggle = $(active.data('toggle'));

        active.removeClass('bbforms-dialog-tab-active');
        active_toggle.removeClass('bbforms-dialog-tab-content-active');

        $this.addClass('bbforms-dialog-tab-active');
        toggle.addClass('bbforms-dialog-tab-content-active');

    });

    // Submissions - Form change
    $('body').on('change', '.bbforms-submissions-form-selector select', function(e) {

        var $this = $(this);
        var value = $this.val();

        $this.prop( 'disabled', 'disabled' );

        var url = window.location.href.split('?')[0];
        url += '?page=bbforms_submissions&form_id=' + value;

        $this.closest('.bbforms-submissions-form-selector').append('<span class="spinner is-active"></span>')

        window.location.href = url;

    });

    // Submissions - Columns toggle
    $('body').on('change', '.bbforms-hide-column-toggle', function(e) {

        var $this = $(this);
        var checked = $this.prop('checked');
        var value = $this.val();

        if( checked ) {
            $('.wp-list-table .column-' + value).show();
        } else {
            $('.wp-list-table .column-' + value).hide();
        }

    });

    $('body').on('change', '.bbforms-show-file-previews-toggle', function(e) {

        var $this = $(this);
        var checked = $this.prop('checked');
        var value = $this.val();

        if( checked ) {
            $('.wp-list-table .bbforms-file-display').show();
        } else {
            $('.wp-list-table .bbforms-file-display').hide();
        }

    });

    // Submissions - Export as CSV
    $('body').on('click', '.bbforms-submissions-export-csv button', function(e) {
        e.preventDefault();

        var $this = $(this);

        if( $this.prop('disabled') ) return;

        var container = $this.closest('.bbforms-submissions-export-csv');

        $this.prop( 'disabled', true );
        $this.html( bbforms_admin.exporting_text );
        container.append('<span class="spinner is-active"></span>');
        container.find('.bbforms-submissions-export-csv-response').remove();

        bbforms_submissions = [];

        bbforms_submissions_export_csv( $this );

    });

    // Range field
    $('body').on("input", '.bbforms-field-row input[type="range"]', function(e) {
        var field = $(this);
        var row = field.closest('.bbforms-field');
        var preview = row.find('.bbforms-field-range-output');

        if( preview ) {
            preview.html( field.val() );
        }

    });

})( jQuery );

function bbforms_remove_last_line_breaks( content ) {

    var lines = content.split( "\n" );

    lines = lines.reverse();

    var new_lines = [];
    var found = false;

    for (var i in lines) {

        if( lines[i].trim().replace("\r", '') === '' && ! found ) {
            //lines.splice( i, 1 );
        } else {
            found = true;
            new_lines.push( lines[i] );
        }
    }

    lines = new_lines.reverse();

    content = lines.join( "\n" );

    return content;

}

var bbforms_submissions = [];
function bbforms_submissions_export_csv( button, page ) {

    var $ = $ || jQuery;

    if( page === undefined ) page = 1;

    var container = button.closest('.bbforms-submissions-export-csv');

    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'bbforms_submissions_export_csv',
            nonce: bbforms_admin.nonce,
            form_id: button.data('form'),
            page: page,
        },
        success: function(response) {
            if (response.success) {
                bbforms_submissions.push(...response.data.submissions);

                if ( response.data.done ) {


                    // Restore button
                    button.prop( 'disabled', false );
                    button.html( bbforms_admin.export_as_csv_text );
                    container.find('.spinner').remove();

                    if ( bbforms_submissions.length === 0 ) {
                        // Show no results found message
                        container.append('<span class="bbforms-submissions-export-csv-response">' + bbforms_admin.export_as_csv_no_results_text + '</span>');
                    } else {
                        // Show success message
                        container.append('<span class="bbforms-submissions-export-csv-response">' + bbforms_admin.export_as_csv_done_text + '</span>');

                        // Download the data as CSV
                        bbforms_download_csv( bbforms_submissions, button.data('filename') );
                    }


                } else {
                    bbforms_submissions_export_csv( button, page + 1 );
                }
            } else {
                // Restore button
                button.prop( 'disabled', false );
                button.html( bbforms_admin.export_as_csv_text );
                container.find('.spinner').remove();

                // Show error message
                container.append('<span class="bbforms-submissions-export-csv-response">' + response.data + '</span>');
            }
        },
        error: function(xhr, status, error) {
            console.error(error);
        }
    });
}

/**
 * Function to turn an object or a JSON object to a CSV file and force the download (Used on import/export tools)
 *
 * @since 1.6.4
 *
 * @param {Object} data
 * @param {string} filename
 */
function bbforms_download_csv( data, filename ) {

    // Convert JSON to CSV
    var csv = bbforms_object_to_csv( data );

    bbforms_download_file( csv, filename, 'csv' );

}

/**
 * Function to force the download of the given content (Used on import/export tools)
 *
 * @since 1.7.0
 *
 * @param {string} content
 * @param {string} filename
 * @param {string} extension
 * @param {string} mime_type
 * @param {string} charset
 */
function bbforms_download_file( content, filename, extension, mime_type = '', charset = '' ) {

    if( mime_type === undefined || mime_type === '' )
        mime_type = 'text/' + extension;

    if( charset === undefined || charset === '' )
        charset = 'utf-8';

    // Setup the file name
    var file = ( filename.length ? filename + '.' + extension : 'file.' + extension );

    var blob = new Blob( [content], { type: mime_type + ';charset=' + charset + ';' } );

    if (navigator.msSaveBlob) {

        // IE 10+
        navigator.msSaveBlob( blob, file );

    } else {

        var link = document.createElement("a");

        // Hide the link element
        link.style.visibility = 'hidden';

        // Check if browser supports HTML5 download attribute
        if ( link.download !== undefined ) {

            // Build the URL object
            var url = URL.createObjectURL( blob );

            // Update link attributes
            link.setAttribute( "href", url );
            link.setAttribute( "download", file );

            // Append the link element and trigger the click event
            document.body.appendChild( link );

            link.click(); // NOTE: Is not a jQuery element, so is safe to use click()

            // Finally remove the link element
            document.body.removeChild( link );

        }
    }

}

/**
 * Format an object into a CSV line
 *
 * @since 1.6.4
 *
 * @param {Object} obj
 *
 * @return {string}
 */
function bbforms_object_to_csv( obj ) {

    // Convert JSON to Object
    var array = typeof obj !== 'object' ? JSON.parse( obj ) : obj;
    var str = '';

    for ( var i = 0; i < array.length; i++ ) {

        var line = '';

        for ( var index in array[i] ) {

            // Separator
            if ( line !== '' ) {
                line += ',';
            }

            // Build a new line
            line += '"' + array[i][index] + '"';
        }

        // Append the line break
        str += line + '\r\n';

    }

    return str;

}