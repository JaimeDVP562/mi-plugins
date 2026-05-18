var gamipress_monster_taming_game_current = 0;
var gamipress_monster_taming_game_process = [
    'points',
    'achievements',
    'finish',
];

(function( $ ) {

    // Update resources
    $('body').on('click', '#monster_taming_game_update_resources', function(e) {
        e.preventDefault();

        var $this = $(this);

        // Disable the button
        $this.prop('disabled', true);

        if( ! $('#monster-taming-game-response').length )
            $this.parent().append('<span id="monster-taming-game-response"></span>');

        // Show the spinner
        $('#monster-taming-game-response').html('<span class="spinner is-active" style="float: none;margin-top:0;"></span>');

        var form_data = new FormData();
        form_data.append( 'action', 'gamipress_monster_taming_game_update_resources' );
        form_data.append( 'nonce', gamipress_admin_tools.nonce );

        $.ajax({
            url: ajaxurl,
            method: 'post',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            success: function(response) {

                if( response.success === false )
                    $('#monster-taming-game-response').css({color:'#a00'});
                else
                    $('#monster-taming-game-response').css({color:''});

                $('#monster-taming-game-response').html(response.data);

                $this.prop('disabled', false);

                if( response.success )
                    setTimeout(function() { window.location.reload(true); }, 2000);

            }
        });

    });

    // Run tool
    $('body').on('click', '#monster_taming_game_run', function(e) {
        e.preventDefault();

        var $this = $(this);

        // Disable the button
        $this.prop('disabled', true);

        if( ! $('#monster-taming-game-response').length )
            $this.parent().append('<span id="monster-taming-game-response"></span>');

        // Show the spinner
        $('#monster-taming-game-response').html('<span class="spinner is-active" style="float: none;margin-top:0;"></span>');

        gamipress_monster_taming_game_current = 0;

        gamipress_monster_taming_game_run();

    });

    // Points Type Visibility
    $('body').on('change', '#cmb2-metabox-monster-taming-game .cmb2-id-points-config input', function() {

        gamipress_monster_taming_game_update_points_labels();
        gamipress_monster_taming_game_update_points_preview();

        var checked = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-config input:checked').val();

        var pt_0 = $('#cmb2-metabox-monster-taming-game .cmb-row[class*="cmb2-id-points-type-0"]');
        var pt_1 = $('#cmb2-metabox-monster-taming-game .cmb-row[class*="cmb2-id-points-type-1"]');
        var pt_2 = $('#cmb2-metabox-monster-taming-game .cmb-row[class*="cmb2-id-points-type-2"]');
        var pt_3 = $('#cmb2-metabox-monster-taming-game .cmb-row[class*="cmb2-id-points-type-3"]');
        var pt_4 = $('#cmb2-metabox-monster-taming-game .cmb-row[class*="cmb2-id-points-type-4"]');
        var pt_5 = $('#cmb2-metabox-monster-taming-game .cmb-row[class*="cmb2-id-points-type-5"]');

        var pt_0_val = pt_0.find('select').val();
        var pt_1_val = pt_1.find('select').val();
        var pt_2_val = pt_2.find('select').val();
        var pt_3_val = pt_3.find('select').val();
        var pt_4_val = pt_4.find('select').val();
        var pt_5_val = pt_5.find('select').val();

        switch( checked ) {
            case '1':
                // Show
                if( pt_0_val === '' ) pt_0 = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-0')

                pt_0.slideDown('fast');

                // Hide
                pt_1.slideUp('fast');
                pt_2.slideUp('fast');
                pt_3.slideUp('fast');
                pt_4.slideUp('fast');
                pt_5.slideUp('fast');
                break;
            case '5':
                // Hide
                pt_0.slideUp('fast');

                // Show
                if( pt_1_val === '' ) pt_1 = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-1')
                if( pt_2_val === '' ) pt_2 = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-2')
                if( pt_3_val === '' ) pt_3 = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-3')
                if( pt_4_val === '' ) pt_4 = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-4')
                if( pt_5_val === '' ) pt_5 = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-5')

                pt_1.slideDown('fast');
                pt_2.slideDown('fast');
                pt_3.slideDown('fast');
                pt_4.slideDown('fast');
                pt_5.slideDown('fast');
                break;
            case '6':
                // Show
                if( pt_0_val === '' ) pt_0 = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-0')
                if( pt_1_val === '' ) pt_1 = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-1')
                if( pt_2_val === '' ) pt_2 = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-2')
                if( pt_3_val === '' ) pt_3 = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-3')
                if( pt_4_val === '' ) pt_4 = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-4')
                if( pt_5_val === '' ) pt_5 = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-5')

                pt_0.slideDown('fast');
                pt_1.slideDown('fast');
                pt_2.slideDown('fast');
                pt_3.slideDown('fast');
                pt_4.slideDown('fast');
                pt_5.slideDown('fast');
                break;
        }


    });

    // Initial check of points types visibility
    $('#cmb2-metabox-monster-taming-game .cmb2-id-points-config input:checked').trigger('change')

    // Points type style
    $('body').on('change', '#cmb2-metabox-monster-taming-game .cmb2-id-points-style input', function() {
        gamipress_monster_taming_game_update_points_labels();
        gamipress_monster_taming_game_update_points_preview();
    });

    gamipress_monster_taming_game_update_points_labels();

    // Points multiplier
    $('body').on('change keyup', '#cmb2-metabox-monster-taming-game .cmb2-id-points-multiplier input', function() {
        gamipress_monster_taming_game_update_points_preview();
    });

    gamipress_monster_taming_game_update_points_preview();

})( jQuery );

function gamipress_monster_taming_game_run() {
    var $ = $ || jQuery;

    var current_process = gamipress_monster_taming_game_process[gamipress_monster_taming_game_current];

    var button = $('#monster_taming_game_run');
    var response_div = $('#monster-taming-game-response');
    var spinner = '<span class="spinner is-active" style="float: none;margin-top:0;"></span>';

    if( current_process === undefined ) {
        // Tool finished
        button.prop('disabled', false);

        return;
    }

    var options = {};
    $.each($('#monster-taming-game *').serializeArray(), function() {
        options[this.name] = this.value;
    });

    $.ajax({
        url: ajaxurl,
        method: 'post',
        cache: false,
        data: {
            action: 'gamipress_monster_taming_game_import_' + current_process,
            nonce: gamipress_admin_tools.nonce,
            options: options,
        },
        success: function(response) {

            if( response.success === false ) {
                // Failure
                response_div.css({color:'#a00'});

                response_div.html( response.data );

                button.prop('disabled', false);
            } else {
                // Success
                if( response.data.message !== undefined ) {
                    response_div.css({color:''});

                    if( gamipress_monster_taming_game_process[gamipress_monster_taming_game_current+1] === undefined ) {
                        spinner = '';
                    }

                    response_div.html( spinner + response.data.message );
                }

                if( response.data.run_again ) {
                    // Run again the same process
                    gamipress_monster_taming_game_run();
                } else {
                    // Run the next process
                    gamipress_monster_taming_game_current++;

                    gamipress_monster_taming_game_run();
                }
            }

        }
    });

}

function gamipress_monster_taming_game_update_points_labels() {
    var $ = $ || jQuery;

    var url = gamipress_monster_taming_game_admin.resources_url;

    $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-0 label > .points-type-0')
        .html('<img src="' + url + 'points/coin.png" >' + gamipress_monster_taming_game_admin.coins);
    $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-0 .cmb-tooltip-desc .points-type-0')
        .html(gamipress_monster_taming_game_admin.coins);

    var style = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-style input:checked').val();

    var points_details = gamipress_monster_taming_game_admin[style];

    for (var i=1; i < 6; i++) {
        $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-' + i + ' label > .points-type-' + i)
            .html('<img src="' + url + 'points/' + style + '/' + i + '.png" >' + points_details[i-1]);
        $('#cmb2-metabox-monster-taming-game .cmb2-id-points-type-' + i + ' .cmb-tooltip-desc .points-type-' + i)
            .html(points_details[i-1]);
    }
}

function gamipress_monster_taming_game_update_points_preview() {
    var $ = $ || jQuery;

    var url = gamipress_monster_taming_game_admin.resources_url;
    var multiplier = parseInt( $('#cmb2-metabox-monster-taming-game input#points_multiplier').val() );
    if( isNaN( multiplier ) ) multiplier = 0;

    var config = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-config input:checked').val();
    var style = $('#cmb2-metabox-monster-taming-game .cmb2-id-points-style input:checked').val();
    var points_details = gamipress_monster_taming_game_admin[style];

    var label = gamipress_monster_taming_game_admin.coins;
    var image = url + 'points/coin.png';
    var label_1 = label;
    var label_2 = label;
    var label_3 = label;
    var image_1 = image;
    var image_2 = image;
    var image_3 = image;


    if( config !== '1' ) {
        label_1 = gamipress_monster_taming_game_admin[style][0];
        label_2 = gamipress_monster_taming_game_admin[style][2];
        label_3 = gamipress_monster_taming_game_admin[style][4];
        image_1 = url + 'points/' + style + '/1.png';
        image_2 = url + 'points/' + style + '/3.png';
        image_3 = url + 'points/' + style + '/5.png';
    }

    $('.gamipress-monster-taming-game-preview .points-preview-1').html( (1*multiplier) + ' <img src="' + image_1 + '"> ' + label_1 );
    $('.gamipress-monster-taming-game-preview .points-preview-2').html( (3*multiplier) + ' <img src="' + image_2 + '"> ' + label_2 );
    $('.gamipress-monster-taming-game-preview .points-preview-3').html( (4*multiplier) + ' <img src="' + image_3 + '"> ' + label_3 );
}