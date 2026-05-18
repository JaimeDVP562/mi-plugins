(function( $ ) {

    var thumbnail_frame,
        current_thumbnail;

    function gamipress_daily_login_rewards_update_order() {

        // Loop through each element
        $('.gamipress-daily-login-rewards-rewards-list .reward-row:not(.ignore-order)').each(function( index, value ) {

            var day = index + 1;

            // Write it's current position to our hidden input value
            $(this).find('input[name="order"]').val( day );

            $(this).find('.reward-header h3').html('Day ' + day);

        });

    }

    // Add reward
    $('.gamipress-daily-login-rewards-add-new-reward').on('click', function(e) {
        e.preventDefault();

        var $this = $(this);
        var post_id = $('input[name="post_ID"]').val();
        var rewards_list = $this.closest('.gamipress-daily-login-rewards-rewards-list');
        rewards_list.siblings( '.rewards-spinner' ).addClass('is-active');

        $.post(
            ajaxurl,
            {
                action: 'gamipress_daily_login_rewards_add_reward',
                nonce: gamipress_daily_login_rewards_rewards_ui.nonce,
                post_id: post_id,
            },
            function( response ) {
                $( response ).insertBefore( $this );

                // Hide the new requirement
                rewards_list.find( 'li.reward-row:last').attr('style', 'display: none;');

                gamipress_daily_login_rewards_update_order();

                // Hide the spinner
                rewards_list.siblings( '.rewards-spinner' ).removeClass('is-active');

                // Slide Down the new requirement
                rewards_list.find( 'li.reward-row:last').slideDown('fast');
            }
        );
    });

    $('.gamipress-daily-login-rewards-save-rewards').on('click', function(e) {
        e.preventDefault();

        var $this = $(this);
        var post_id = $('input[name="post_ID"]').val();
        var rewards_list = $this.siblings('.gamipress-daily-login-rewards-rewards-list');

        rewards_list.siblings( '.rewards-spinner' ).addClass('is-active');

        var request_data = {
            action: 'gamipress_daily_login_rewards_update_rewards',
            nonce: gamipress_daily_login_rewards_rewards_ui.nonce,
            post_id: post_id,
            rewards: []
        };

        // Loop through each points award and collect its data
        rewards_list.find( '.reward-row' ).each( function() {

            var reward = $(this);

            // Setup our reward object
            var reward_details = {
                reward_id               : reward.find( 'input[name="reward_id"]').val(),
                reward_type             : reward.find( '.reward-type:checked' ).val(),
                order                   : reward.find( 'input[name="order"]' ).val(),
                label                   : reward.find( '.reward-label' ).val(),
                thumbnail               : reward.find( 'input[name="reward_thumbnail"]' ).val()
            };

            if( reward_details.reward_type === 'points' ) {

                reward_details.points = reward.find( '.reward-points-amount' ).val();
                reward_details.points_type = reward.find( '.reward-points-type' ).val();
                reward_details.points_type_thumbnail = reward.find( '.reward-points-image' ).prop('checked') ? 1 : 0;

            } else if( reward_details.reward_type === 'achievement' ) {

                reward_details.achievement = reward.find( '.reward-achievement' ).val();
                reward_details.achievement_thumbnail = reward.find( '.reward-achievement-image' ).prop('checked') ? 1 : 0;

            } else if( reward_details.reward_type === 'rank' ) {

                reward_details.rank = reward.find( '.reward-rank' ).val();
                reward_details.rank_thumbnail = reward.find( '.reward-rank-image' ).prop('checked') ? 1 : 0;

            }

            // Allow external functions to add their own data to the array
            reward.trigger( 'update_reward_data', [ reward_details, reward ] );

            // Add our relevant data to the array
            request_data.rewards.push( reward_details );

        });

        $.post(
            ajaxurl,
            request_data,
            function( response ) {
                // Hide the spinner
                rewards_list.siblings( '.rewards-spinner' ).removeClass('is-active');
            }
        );
    });

    // Delete reward
    $('.gamipress-daily-login-rewards-rewards-list').on('click', '.delete-reward', function(e) {
        e.preventDefault();

        var $this = $(this);
        var rewards_list = $this.closest('.gamipress-daily-login-rewards-rewards-list');
        var reward_id =  $this.closest('.reward-row').find('input[name="reward_id"]').val();

        rewards_list.find( '.reward-' + reward_id ).addClass('ignore-order').slideUp( 'fast');

        gamipress_daily_login_rewards_update_order();

        $.post(
            ajaxurl,
            {
                action: 'gamipress_daily_login_rewards_delete_reward',
                nonce: gamipress_daily_login_rewards_rewards_ui.nonce,
                reward_id: reward_id
            },
            function( response ) {
                rewards_list.find( '.reward-' + reward_id ).remove();

                $('.gamipress-daily-login-rewards-save-rewards').trigger('click');
            }
        );

    });

    // Make rewards list sortable
    $(".gamipress-daily-login-rewards-rewards-list").sortable({

        // When the list order is updated
        update : function ( e, ui ) {


            gamipress_daily_login_rewards_update_order();

        }
    });

    // Thumbnail click
    $('.gamipress-daily-login-rewards-rewards-list').on('click', '.reward-thumbnail', function(e) {

        if( $(e.target).hasClass('remove-reward-thumbnail') ) {
            return;
        }

        current_thumbnail = $(this);

        // If the media frame already exists, reopen it.
        if ( thumbnail_frame ) {
            thumbnail_frame.open();
            return;
        }

        // Create a new media frame
        thumbnail_frame = wp.media({
            title: gamipress_daily_login_rewards_rewards_ui.media_title,
            multiple: false
        });


        // When an image is selected in the media frame...
        thumbnail_frame.on( 'select', function() {

            // Get media attachment details from the frame state
            var attachment = thumbnail_frame.state().get('selection').first().toJSON();

            current_thumbnail.addClass('has-thumbnail');

            // Send the attachment URL to our custom image input field.
            if( ! current_thumbnail.find('img').length ) {
                current_thumbnail.append( '<img src="' + attachment.url + '" alt="" style="max-width:100%;"/>' );
            } else {
                current_thumbnail.find('img').attr( 'src', attachment.url );
            }

            // Send the attachment id to our hidden input
            current_thumbnail.find('input[name="reward_thumbnail"]').val( attachment.id );

            // Unhide the remove image link
            current_thumbnail.find('.remove-reward-thumbnail').show();
        });

        // Finally, open the modal on click
        thumbnail_frame.open();
    });

    // Remove thumbnail
    $('.gamipress-daily-login-rewards-rewards-list').on('click', '.remove-reward-thumbnail', function(e) {
        var $this = $(this);
        var thumbnail = $this.closest('.reward-thumbnail');

        thumbnail.removeClass('has-thumbnail');

        // Remove the image preview
        thumbnail.find('img').remove();

        // Clear the thumbnail ID
        thumbnail.find('input[name="reward_thumbnail"]').val( '' );

        // Hide the remove image link
        $this.hide();
    });

    // Reward type change
    $('.gamipress-daily-login-rewards-rewards-list').on('change', '.reward-type', function(e) {

        var $this = $(this);

        if( ! $this.prop('checked') ) {
            return;
        }

        var reward = $this.closest('.reward-row');
        var reward_type = $this.val();
        var previous_type = reward.find('.reward-type:checked:not([value="' + $this.val() + '"])');

        // Uncheck previous checked type
        previous_type.prop( 'checked', false );

        if( reward_type === 'nothing' ) {

        } else if( reward_type === 'points' ) {

        } else if( reward_type === 'achievement' ) {
            var achievement_selector = reward.find('.reward-achievement');

            // Check if achievement selector Select2 has been initialized
            if( ! achievement_selector.hasClass('select2-hidden-accessible') ) {
                achievement_selector.gamipress_select2({
                    ajax: {
                        url: ajaxurl,
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function( params ) {
                            return {
                                q: params.term,
                                action: 'gamipress_get_achievements_options',
                                nonce: gamipress_daily_login_rewards_rewards_ui.nonce,
                            };
                        },
                        processResults: gamipress_select2_posts_process_results
                    },
                    escapeMarkup: function ( markup ) { return markup; }, // Let our custom formatter work
                    templateResult: gamipress_select2_posts_template_result,
                    theme: 'default gamipress-select2',
                    placeholder: gamipress_daily_login_rewards_rewards_ui.achievement_placeholder,
                    allowClear: true,
                    multiple: false
                });
            }
        } else if( reward_type === 'rank' ) {
            var rank_selector = reward.find('.reward-rank');

            // Check if rank selector Select2 has been initialized
            if( ! rank_selector.hasClass('select2-hidden-accessible') ) {
                rank_selector.gamipress_select2({
                    ajax: {
                        url: ajaxurl,
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function( params ) {
                            return {
                                q: params.term,
                                action: 'gamipress_get_ranks_options',
                                nonce: gamipress_daily_login_rewards_rewards_ui.nonce,
                            };
                        },
                        processResults: gamipress_select2_posts_process_results
                    },
                    escapeMarkup: function ( markup ) { return markup; }, // Let our custom formatter work
                    templateResult: gamipress_select2_posts_template_result,
                    theme: 'default gamipress-select2',
                    placeholder: gamipress_daily_login_rewards_rewards_ui.rank_placeholder,
                    allowClear: true,
                    multiple: false
                });
            }
        }

        // Toggle reward type forms visibility
        previous_type.closest('.reward-type-row').find('.reward-type-form').slideUp(250);
        $this.closest('.reward-type-row').find('.reward-type-form').slideDown(250);

    });

    // Initial change
    $('.gamipress-daily-login-rewards-rewards-list .reward-type').trigger('change');

})( jQuery );