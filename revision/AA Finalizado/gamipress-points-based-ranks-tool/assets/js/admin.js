/**
 * GamiPress Points-Based Ranks Tool - Admin JavaScript
 *
 * Handles the frontend functionality for the Points-Based Ranks Builder tool.
 * Manages rank creation, badge image generation, and user awarding processes.
 *
 * @package GamiPress\Points_Based_Ranks\Admin
 * @version 1.0.0
 */

(function($) {

    var gprbt_created_rank_ids = [];

    var gprbt_badge_canvas;

    var gprbt_badge_builder_ready = false;

    /**
     * Convert a number to letters (A, B, C, ... Z, AA, AB, ...).
     *
     * Generates alphabetic sequences similar to Excel column headers.
     * Used for generating letter-based rank identifiers.
     *
     * @since 1.0.0
     * @param {number} number - The positive integer to convert (1-based).
     * @returns {string} The corresponding uppercase letters.
     */
    function gprbt_number_to_letters( number ) {
        var letters = '';

        while ( number > 0 ) {
            var mod = ( number - 1 ) % 26;
            letters = String.fromCharCode( 65 + mod ) + letters;
            number = Math.floor( ( number - 1 ) / 26 );
        }

        return letters;
    }

    /**
     * Initialize the Fabric.js badge builder canvas.
     *
     * Sets up the canvas element for generating rank badge images.
     * Only initializes once and only if Fabric.js is available.
     *
     * @since 1.0.0
     */
    function gprbt_init_badge_builder() {
        if ( gprbt_badge_builder_ready ) {
            return;
        }

        if ( typeof fabric === 'undefined' ) {
            return;
        }

        if ( $( '#gamipress-badge-builder-canvas' ).length ) {
            gprbt_badge_canvas = new fabric.Canvas( 'gamipress-badge-builder-canvas', {
                preserveObjectStacking: true,
                width: 600,
                height: 600,
            });
        }

        if ( gprbt_badge_canvas ) {
            gprbt_badge_builder_ready = true;
        }
    }

    /**
     * Generate a rank badge image using Fabric.js.
     *
     * Creates a circular badge with the rank name centered.
     * Returns a Promise that resolves to a base64 PNG data URL.
     *
     * @since 1.0.0
     *
     * @param {string} rank_name - The name to display on the badge.
     * @param {string} primary_color - Fill color for the main circle.
     * @param {string} secondary_color - Color for border and inner circle.
     * @param {string} text_color - Color for the rank name text.
     * @returns {Promise<string>} Resolves to base64 PNG image data URL.
     */
    function gprbt_generate_rank_badge_image( rank_name, primary_color, secondary_color, text_color ) {
        return new Promise( function( resolve, reject ) {
            if ( ! gprbt_badge_builder_ready ) {
                gprbt_init_badge_builder();
            }

            if ( ! gprbt_badge_builder_ready || ! gprbt_badge_canvas ) {
                return reject( 'Badge builder not available.' );
            }

            primary_color = primary_color || '#2196f3';
            secondary_color = secondary_color || '#ffffff';
            text_color = text_color || '#ffffff';

            gprbt_badge_canvas.clear();

            var canvasSize = Math.min( gprbt_badge_canvas.width, gprbt_badge_canvas.height );
            var badgeRadius = canvasSize / 2 - 24;

            var circle = new fabric.Circle( {
                radius: badgeRadius,
                left: gprbt_badge_canvas.width / 2,
                top: gprbt_badge_canvas.height / 2,
                originX: 'center',
                originY: 'center',
                fill: primary_color,
                stroke: secondary_color,
                strokeWidth: 24,
                selectable: false,
                evented: false,
            } );

            var accentCircle = new fabric.Circle( {
                radius: badgeRadius - 32,
                left: gprbt_badge_canvas.width / 2,
                top: gprbt_badge_canvas.height / 2,
                originX: 'center',
                originY: 'center',
                fill: secondary_color,
                selectable: false,
                evented: false,
            } );

            var text = new fabric.Textbox( rank_name, {
                left: gprbt_badge_canvas.width / 2,
                top: gprbt_badge_canvas.height / 2,
                originX: 'center',
                originY: 'center',
                width: canvasSize - 80,
                fontSize: 80,
                fill: text_color,
                textAlign: 'center',
                fontFamily: 'Arial, sans-serif',
                selectable: false,
                evented: false,
            } );

            gprbt_badge_canvas.add( circle, accentCircle, text );
            gprbt_badge_canvas.renderAll();

            resolve( gprbt_badge_canvas.toDataURL( 'image/png' ) );
        });
    }

    /**
     * Generate badge images for a batch of ranks.
     *
     * Processes a batch of ranks and generates badge images for each.
     * Uses Promise.all to generate images in parallel.
     *
     * @since 1.0.0
     *
     * @param {number} loop - Current batch iteration number.
     * @param {number} points_step - Points increment (unused but passed for context).
     * @param {number} rank_count - Total number of ranks to create.
     * @param {string} name_pattern - Rank name pattern with {number} and {letter} placeholders.
     * @param {string} primary_color - Primary color for badges.
     * @param {string} secondary_color - Secondary color for badges.
     * @param {string} text_color - Text color for badges.
     * @returns {Promise<string[]>} Array of base64 PNG image data URLs.
     */
    function gprbt_generate_batch_badge_images( loop, points_step, rank_count, name_pattern, primary_color, secondary_color, text_color ) {
        var limit = 20;
        var offset = loop * limit;
        var current_position = offset + 1;
        var max_position = Math.min( rank_count, offset + limit );
        var promises = [];

        for ( var position = current_position; position <= max_position; position++ ) {
            var rank_name = name_pattern.replace( /\{number\}/g, position ).replace( /\{letter\}/g, gprbt_number_to_letters( position ) );
            promises.push( gprbt_generate_rank_badge_image( rank_name, primary_color, secondary_color, text_color ) );
        }

        return Promise.all( promises );
    }

    /**
     * Display the "Award Existing Users" button after successful rank creation.
     *
     * Only shows the button if ranks were created and button doesn't already exist.
     *
     * @since 1.0.0
     *
     * @param {string} response_id - The ID of the response element to append to.
     */
    function gprbt_show_award_button( response_id ) {
        if ( ! gprbt_created_rank_ids.length ) {
            return;
        }

        if ( $( '#bulk_award_points_based_ranks_award_users_button' ).length ) {
            return;
        }
        
        var award_button = $( '<button type="button" id="bulk_award_points_based_ranks_award_users_button" class="button button-primary" style="margin-top: 10px;">Award Existing Users</button>' );
        $( '#' + response_id ).append( '<br />' ).append( award_button );
    }

    /**
     * Display an error message in the tool response area.
     *
     * @since 1.0.0
     *
     * @param {string} response_id - The ID of the response element.
     * @param {string} message - The error message to display.
     */
    function gprbt_display_tool_error( response_id, message ) {
        $( '#' + response_id ).css( { color: '#a00' } ).html( message );
    }

    /**
     * Send AJAX request to create ranks.
     *
     * Handles the AJAX communication for rank creation, including
     * batch processing when more than 20 ranks are requested.
     *
     * @since 1.0.0
     *
     * @param {jQuery} button - The button element that triggered the action.
     * @param {Object} data - The data object to send with the AJAX request.
     * @param {number} loop - Current batch iteration number.
     */
    function gprbt_post_tool( button, data, loop ) {
        var response_id = button.attr( 'id' ).replace( '_button', '_response' );

        $.post( gprbt_data.ajax_url, data, function( response ) {
            if ( response.success && response.data && response.data.rank_ids ) {
                gprbt_created_rank_ids = gprbt_created_rank_ids.concat( response.data.rank_ids );
            }

            if ( response.success && response.data && response.data.run_again ) {
                $( '#' + response_id ).html( '<span class="spinner is-active" style="float: none; margin: 0;"></span><span style="display: inline-block; padding-left: 5px;">' + response.data.message + '</span>' );
                gprbt_run_tool( button, loop + 1 );
                return;
            }

            if ( response.success === false ) {
                $( '#' + response_id ).css( { color: '#a00' } );
            } else {
                $( '#' + response_id ).css( { color: '' } );
            }

            $( '#' + response_id ).html( response.data && response.data.message ? response.data.message : response.data );
            button.prop( 'disabled', false );

            if ( response.success && response.data && ! response.data.run_again ) {
                gprbt_show_award_button( response_id );
            }
        } ).fail( function() {
            $( '#' + response_id ).html( 'The server has returned an internal error.' );
            button.prop( 'disabled', false );
        });
    }

    /**
     * Run the points-based ranks tool.
     *
     * Main function that gathers form values, validates input,
     * optionally generates badge images, and initiates rank creation.
     *
     * @since 1.0.0
     *
     * @param {jQuery} button - The button element that triggered the action.
     * @param {number} loop - Current batch iteration number (default: 0).
     */
    function gprbt_run_tool( button, loop ) {
        if ( loop === undefined ) {
            loop = 0;
        }

        var response_id = button.attr( 'id' ).replace( '_button', '_response' );

        if ( ! $( '#' + response_id ).length ) {
            button.parent().append( '<span id="' + response_id + '" style="display: inline-block; padding: 5px 0 0 8px;"></span>' );
        }

        $( '#' + response_id ).html( '<span class="spinner is-active" style="float: none; margin: 0;"></span>' );

        var points_type = $( '#bulk_award_points_based_ranks_points_type' ).val();
        var rank_type = $( '#bulk_award_points_based_ranks_rank_type' ).val();
        var points_step = parseInt( $( '#bulk_award_points_based_ranks_step' ).val(), 10 );
        var rank_count = parseInt( $( '#bulk_award_points_based_ranks_count' ).val(), 10 );
        var name_pattern = $( '#bulk_award_points_based_ranks_name_pattern' ).val();
        var generate_images = $( '#bulk_award_points_based_ranks_generate_images' ).is( ':checked' );
        var primary_color = $( '#bulk_award_points_based_ranks_badge_color' ).val() || '#2196f3';
        var secondary_color = $( '#bulk_award_points_based_ranks_badge_stroke_color' ).val() || '#ffffff';
        var text_color = $( '#bulk_award_points_based_ranks_badge_text_color' ).val() || '#ffffff';

        if ( isNaN( points_step ) || points_step <= 0 ) {
            gprbt_display_tool_error( response_id, 'Points step must be a number greater than zero.' );
            button.prop( 'disabled', false );
            return;
        }

        if ( isNaN( rank_count ) || rank_count <= 0 ) {
            gprbt_display_tool_error( response_id, 'The number of ranks must be greater than zero.' );
            button.prop( 'disabled', false );
            return;
        }

        var data = {
            action: 'gprbt_points_based_ranks_tool',
            nonce: gprbt_data.nonce,
            points_type: points_type,
            rank_type: rank_type,
            points_step: points_step,
            rank_count: rank_count,
            name_pattern: name_pattern,
            loop: loop,
        };

        if ( generate_images ) {
            gprbt_generate_batch_badge_images( loop, points_step, rank_count, name_pattern, primary_color, secondary_color, text_color ).then( function( images ) {
                data.badge_images = images;
                gprbt_post_tool( button, data, loop );
            } ).catch( function( error ) {
                $( '#' + response_id ).css( { color: '#a00' } );
                $( '#' + response_id ).html( 'Could not generate images: ' + error );
                button.prop( 'disabled', false );
            });
            return;
        }

        gprbt_post_tool( button, data, loop );
    }

    /**
     * Award ranks to existing users based on their points.
     *
     * Processes users in batches of 50, checking each user's points
     * against rank requirements and awarding qualifying ranks.
     *
     * @since 1.0.0
     *
     * @param {jQuery} button - The button element that triggered the action.
     * @param {number} batch - Current batch iteration number (default: 0).
     */
    function gprbt_award_users( button, batch ) {
        if ( batch === undefined ) {
            batch = 0;
        }

        var response_id = button.attr( 'id' ).replace( '_button', '_award_response' );

        if ( ! $( '#' + response_id ).length ) {
            button.parent().append( '<span id="' + response_id + '" style="display: inline-block; padding: 8px 0 0 8px;"></span>' );
        }

        $( '#' + response_id ).html( '<span class="spinner is-active" style="float: none; margin: 0;"></span><span style="display: inline-block; padding-left: 5px;">Processing users...</span>' );
        button.prop( 'disabled', true );

        var data = {
            action: 'gprbt_award_users_with_ranks_tool',
            nonce: gprbt_data.nonce,
            rank_ids: gprbt_created_rank_ids,
            batch: batch,
            points_type: $( '#bulk_award_points_based_ranks_points_type' ).val(),
        };

        $.post( gprbt_data.ajax_url, data, function( response ) {
            if ( response.success && response.data && response.data.run_again ) {
                $( '#' + response_id ).html( '<span class="spinner is-active" style="float: none; margin: 0;"></span><span style="display: inline-block; padding-left: 5px;">' + response.data.message + '</span>' );
                gprbt_award_users( button, response.data.batch );
                return;
            }

            if ( response.success === false ) {
                $( '#' + response_id ).css( { color: '#a00' } );
            } else {
                $( '#' + response_id ).css( { color: '' } );
            }

            $( '#' + response_id ).html( response.data && response.data.message ? response.data.message : response.data );
            button.prop( 'disabled', false );
        } ).fail( function() {
            $( '#' + response_id ).html( 'The server has returned an internal error.' );
            button.prop( 'disabled', false );
        });
    }

    /**
     * Toggle visibility of badge color controls.
     *
     * Shows or hides the color picker fields based on whether
     * badge image generation is enabled.
     *
     * @since 1.0.0
     *
     * @param {boolean} visible - Whether to show (true) or hide (false) the controls.
     */
    function gprbt_toggle_badge_color_controls( visible ) {
        var targets = [
            '.cmb2-id-bulk-award-points-based-ranks-badge-color',
            '.cmb2-id-bulk-award-points-based-ranks-badge-stroke-color',
            '.cmb2-id-bulk-award-points-based-ranks-badge-text-color',
        ];

        targets.forEach( function( selector ) {
            var $target = $( selector );

            if ( $target.length ) {
                $target.toggle( visible );
            }
        });
    }

    $( document ).ready( function() {
        gprbt_init_badge_builder();

        $( document ).on( 'click', '#bulk_award_points_based_ranks_button', function( e ) {
            e.preventDefault();
            var button = $( this );
            button.prop( 'disabled', true );
            gprbt_created_rank_ids = [];
            gprbt_run_tool( button );
        } );

        $( document ).on( 'click', '#bulk_award_points_based_ranks_award_users_button', function( e ) {
            e.preventDefault();
            var button = $( this );
            gprbt_award_users( button );
        } );

        $( document ).on( 'input change', '#bulk_award_points_based_ranks_step, #bulk_award_points_based_ranks_count', function() {
            var value = $( this ).val();
            if ( value.indexOf( '-' ) !== -1 ) {
                $( this ).val( value.replace( /-/g, '' ) );
            }
        } );

        $( document ).on( 'blur', '#bulk_award_points_based_ranks_step, #bulk_award_points_based_ranks_count', function() {
            var value = parseInt( $( this ).val(), 10 );
            if ( ! isNaN( value ) && value < 1 ) {
                $( this ).val( 1 );
            }
        } );

        $( document ).on( 'change', '#bulk_award_points_based_ranks_generate_images', function() {
            gprbt_toggle_badge_color_controls( $( this ).is( ':checked' ) );
        } );

        gprbt_toggle_badge_color_controls( $( '#bulk_award_points_based_ranks_generate_images' ).is( ':checked' ) );
    });

})( jQuery );
