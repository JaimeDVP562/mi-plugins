(function( $ ) {

    // Consecutive
    $('#_gamipress_daily_login_rewards_consecutive').on('change', function() {

        var target = $('.cmb2-id--gamipress-daily-login-rewards-consecutive-penalty');

        if( $(this).prop('checked') ) {
            target.slideDown(250);
        } else {
            target.slideUp(250);
        }
    });

    if( ! $('#_gamipress_daily_login_rewards_consecutive').prop('checked') ) {
        $('.cmb2-id--gamipress-daily-login-rewards-consecutive-penalty').hide();
    }

    // Date limit
    $('#_gamipress_daily_login_rewards_date_limit').on('change', function() {

        var target = $('.cmb2-id--gamipress-daily-login-rewards-start-date, .cmb2-id--gamipress-daily-login-rewards-end-date');

        if( $(this).prop('checked') ) {
            target.slideDown(250);
        } else {
            target.slideUp(250);
        }
    });

    if( ! $('#_gamipress_daily_login_rewards_date_limit').prop('checked') ) {
        $('.cmb2-id--gamipress-daily-login-rewards-start-date, .cmb2-id--gamipress-daily-login-rewards-end-date').hide();
    }

    // Repeatable
    $('#_gamipress_daily_login_rewards_repeatable').on('change', function() {

        var target = $('.cmb2-id--gamipress-daily-login-rewards-repeatable-times');

        if( $(this).prop('checked') ) {
            target.slideDown(250);
        } else {
            target.slideUp(250);
        }
    });

    if( ! $('#_gamipress_daily_login_rewards_repeatable').prop('checked') ) {
        $('.cmb2-id--gamipress-daily-login-rewards-repeatable-times').hide();
    }

    // Complete rewards calendars
    $('#_gamipress_daily_login_rewards_complete_rewards_calendars').on('change', function() {

        var target = $('.cmb2-id--gamipress-daily-login-rewards-required-rewards-calendars');

        if( $(this).prop('checked') ) {
            target.slideDown(250);
        } else {
            target.slideUp(250);
        }
    });

    if( ! $('#_gamipress_daily_login_rewards_complete_rewards_calendars').prop('checked') ) {
        $('.cmb2-id--gamipress-daily-login-rewards-required-rewards-calendars').hide();
    }

    if( $('#_gamipress_daily_login_rewards_required_rewards_calendars').length ) {

        $('#_gamipress_daily_login_rewards_required_rewards_calendars').gamipress_select2( {
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                delay: 250,
                type: 'POST',
                data: function( params ) {
                    return {
                        q: params.term,
                        action: 'gamipress_daily_login_rewards_get_rewards_calendars_options',
                        nonce: gamipress_daily_login_rewards_admin.nonce,
                    };
                },
                processResults: function( results, page ) {
                    if( results === null ) {
                        return { results: [] };
                    }

                    var formatted_results = [];

                    results.data.forEach(function(item) {
                        formatted_results.push({
                            id: item.ID,
                            text: item.post_title + ' (#' + item.ID + ')',
                        });
                    });

                    return { results: formatted_results };
                }
            },
            theme: 'default gamipress-select2',
            placeholder: gamipress_daily_login_rewards_admin.rewards_calendar_placeholder,
            allowClear: true,
            multiple: true
        } );

    }

    // Rewards Calendar slug setting

    $('#gamipress_daily_login_rewards_slug').on( 'keyup', function() {
        var field = $(this);
        var slug = $(this).val();
        var preview = $(this).next('.cmb2-metabox-description').find('.gamipress-daily-login-rewards-slug');

        if( preview.length ) {
            preview.text(slug);
        }

        // Delete any existing version of this warning
        $('#slug-warning').remove();

        // Throw a warning on Points/Achievement Type editor if slug is > 20 characters
        if ( slug.length > 20 ) {
            // Set input to look like danger
            field.css({'background':'#faa', 'color':'#a00', 'border-color':'#a55' });

            // Output a custom warning
            // TODO: Localization here
            field.parent().append('<span id="slug-warning" class="cmb2-metabox-description" style="color: #a00;">Rewards Calendar\'s slug supports a maximum of 20 characters.</span>');
        } else {
            // Restore the input style
            field.css({'background':'', 'color':'', 'border-color':''});
        }
    });

    $('#gamipress_daily_login_rewards_slug').keyup();

})( jQuery );