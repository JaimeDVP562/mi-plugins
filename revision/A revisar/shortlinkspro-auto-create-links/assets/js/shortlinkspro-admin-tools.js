(function ( $ ) {

    // Tools

    // Clicks cleanup

    // On click cleanup button
    $(".shortlinkspro-clicks-cleanup-button").on("click", function(e) {
        e.preventDefault();

        var $this = $(this);
        var row = $this.closest('.cmb-row');
        var spinner = row.find('.spinner');
        var response_div = row.find('.shortlinkspro-clicks-cleanup-response');
        var id = $this.attr('id');

        // Changes in HTML elements
        $(".shortlinkspro-clicks-cleanup-button").attr('disabled', true);
        spinner.addClass('is-active');
        response_div.fadeOut('fast');

        $.ajax({
            url: shortlinkspro_admin_tools.ajaxurl,
            method: 'POST',
            data: {
                action: 'shortlinkspro_clicks_cleanup',
                id: id,
                nonce: shortlinkspro_admin_tools.nonce,
            },
            success: function( response ) {

                // Restore HTML elements
                $(".shortlinkspro-clicks-cleanup-button").attr('disabled', false);
                spinner.removeClass('is-active');
                response_div.fadeIn('fast');
            },
            error: function( response ) {
                // Error to call ajax
            }
        });

    });

    // Import From Plugin

    // On change plugin input
    $(".cmb2-id-import-from-plugin input[name=\"import_from_plugin\"]").on("change", function(e) {
        var $this = $(this);
        var plugin = $this.val();
        var all_supports = [ 'links', 'clicks', 'link_categories', 'link_tags' ];
        var supports = $this.data(plugin).split(',');
        var not_supports = all_supports.filter(function(i) {return supports.indexOf(i) < 0;});

        $('.cmb2-id-import-from-plugin-data').slideDown("fast");

        supports.forEach((item) => {
            $(".cmb2-id-import-from-plugin-data input[value=\"" + item + "\"]").parent().slideDown("fast");
        });

        not_supports.forEach((item) => {
            $(".cmb2-id-import-from-plugin-data input[value=\"" + item + "\"]").parent().slideUp("fast");
        });

    });

    // On click import
    $("#import_from_plugin_button").on("click", function(e) {
        var $this = $(this);

        if( $this.prop('disabled') ) {
            return;
        }

        var plugin = $('.cmb2-id-import-from-plugin input[name=\"import_from_plugin\"]:checked').val();
        var supports = $('.cmb2-id-import-from-plugin input[name=\"import_from_plugin\"]:checked').data(plugin);
        var row = $this.closest('.cmb-row');
        var spinner = row.find('.spinner');
        var response_div = row.find('.shortlinkspro-import-from-plugin-response');
        var error_div = row.find('.shortlinkspro-import-from-plugin-error');

        // Show error message
        if( plugin === '' ) {
            error_div.html(shortlinkspro_admin_tools.import_from_plugin_no_plugin_text);
            error_div.slideDown('fast');
            return;
        }

        var plugin_data = '';

        $('.cmb2-id-import-from-plugin-data input[name=\"import_from_plugin_data[]\"]:checked').each(function() {
            if( supports.indexOf( $(this).val() ) !== -1 ) {
                plugin_data += $(this).val() + ','
            }
        });

        // Remove the last ,
        plugin_data = plugin_data.substring(0, plugin_data.length-1);

        // Show error message
        if( plugin_data === '' ) {
            error_div.html(shortlinkspro_admin_tools.import_from_plugin_no_data_text);
            error_div.slideDown('fast');
            return;
        }

        // Hide any error message
        error_div.slideUp('fast');

        // Prepare the response element
        response_div.html(shortlinkspro_admin_tools.import_from_plugin_importing_text);
        response_div.slideDown('fast');

        // Show spinner
        spinner.addClass('is-active');

        // Disable the button
        $this.prop('disabled', true);

        shortlinkspro_run_plugin_import_loop = 0;
        shortlinkspro_plugin_import_group = 0;

        shortlinkspro_run_plugin_import( plugin, plugin_data, $this, response_div, spinner )

    });

    var shortlinkspro_run_plugin_import_loop = 0;
    var shortlinkspro_plugin_import_group = 0;

    function shortlinkspro_run_plugin_import( plugin, plugin_data, button, response_div, spinner ) {

        var groups = plugin_data.split( ',' );

        if( groups[shortlinkspro_plugin_import_group] === undefined ) {

            // Restore HTML elements
            spinner.removeClass('is-active');
            button.prop('disabled', false);
            response_div.html(shortlinkspro_admin_tools.import_from_plugin_finished_text);
            return;
        }

        $.ajax({
            url: shortlinkspro_admin_tools.ajaxurl,
            method: 'POST',
            data: {
                action: 'shortlinkspro_import_from_plugin',
                nonce: shortlinkspro_admin_tools.nonce,
                plugin: plugin,
                group: groups[shortlinkspro_plugin_import_group],
                loop: shortlinkspro_run_plugin_import_loop,
            },
            success: function( response ) {

                // Set the response message
                if( response.data.message !== undefined ) {
                    response_div.html( response.data.message );
                }

                if( response.data.run_again !== undefined && response.data.run_again ) {
                    // If run again is set, we need to send again the same action

                    // Increment loop
                    shortlinkspro_run_plugin_import_loop++;

                    // Run again the same import group with different loop
                    shortlinkspro_run_plugin_import( plugin, plugin_data, button, response_div, spinner );
                } else {

                    // Increment import group
                    shortlinkspro_plugin_import_group++;

                    // Reset loop
                    shortlinkspro_run_plugin_import_loop = 0;

                    // Run the next import group
                    setTimeout(function () {
                        shortlinkspro_run_plugin_import( plugin, plugin_data, button, response_div, spinner );
                    }, 500 );
                }
            },
            error: function( response ) {
                // Error to call ajax
            }
        });

    }

})( jQuery );