/* global jQuery, grrAdminData */
/**
 * GamiPress Recurring Rewards - Admin JS
 *
 * Handles:
 * - Cycle field conditional visibility on the CT edit screen (Bug 1)
 * - CT edit form ID fix for WP compatibility (Bug 8)
 * - Requirements selector (Select2 + add/remove) on edit and add screens (Bug 8)
 * - Revoke access on the users meta box (Bug 8)
 * - Cycle field conditional visibility on the custom add screen (Bug 8)
 *
 * @package GamiPress\Recurring_Rewards
 * @since   1.0.0
 */
( function( $ ) {
    'use strict';

    // -------------------------------------------------------------------------
    // Edit screen: cycle_day / cycle_month_day conditional visibility
    // Uses CMB2 row_classes: .grr-cycle-day-row  and .grr-cycle-month-day-row
    // Applies the disabled attribute so hidden fields are not submitted (Bug 1)
    // -------------------------------------------------------------------------

    /**
     * Show/hide and enable/disable the cycle sub-fields depending on cycle_type.
     *
     * @since 1.0.0
     */
    function grrUpdateCycleVisibility() {
        var cycleType   = $( '[name="cycle_type"]' ).val();
        var $dayRow     = $( '.grr-cycle-day-row' );
        var $monthRow   = $( '.grr-cycle-month-day-row' );
        var $dayInput   = $( '[name="cycle_day"]' );
        var $monthInput = $( '[name="cycle_month_day"]' );

        // Hide and disable both rows by default.
        $dayRow.hide();
        $monthRow.hide();
        $dayInput.prop( 'disabled', true );
        $monthInput.prop( 'disabled', true );

        if ( 'week' === cycleType ) {
            $dayRow.show();
            $dayInput.prop( 'disabled', false );
        } else if ( 'month' === cycleType ) {
            $monthRow.show();
            $monthInput.prop( 'disabled', false );
        }
    }

    // -------------------------------------------------------------------------
    // Edit screen: requirements meta box (Select2 + add/remove)
    // -------------------------------------------------------------------------

    /**
     * Initialise the requirements selector on the CT edit screen.
     *
     * @since 1.0.0
     */
    function grrInitRequirementsEdit() {
        var $typeSel   = $( '#requirement-type-selector' );
        var $itemSel   = $( '#requirement-item-selector' );
        var $container = $( '#requirements-container' );
        var $btnAdd    = $( '#add-requirement' );

        if ( ! $typeSel.length ) {
            return;
        }

        if ( $.fn.select2 ) {
            $typeSel.select2( { width: '100%' } );
            $itemSel.select2( { placeholder: grrAdminData.strings.search, width: '100%' } );
        }

        $typeSel.on( 'change', function() {
            var type = $( this ).val();

            $itemSel.empty().append( '<option>' + grrAdminData.strings.loading + '</option>' );
            if ( $.fn.select2 ) {
                $itemSel.trigger( 'change' );
            }

            if ( ! type ) {
                return;
            }

            $.ajax( {
                url:  grrAdminData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'gamipress_recurring_rewards_search_posts',
                    type: type,
                    _wpnonce: grrAdminData.nonce
                },
                success: function( response ) {
                    $itemSel.empty().append( '<option value="">' + grrAdminData.strings.select + '</option>' );
                    if ( $.fn.select2 ) {
                        $itemSel.select2( { data: response, width: '100%' } ).trigger( 'change' );
                    }
                }
            } );
        } );

        $btnAdd.on( 'click', function() {
            var type     = $typeSel.val();
            var itemId   = $itemSel.val();
            var itemText = $itemSel.find( 'option:selected' ).text();

            if ( ! type || ! itemId ) {
                return;
            }

            var index     = $container.find( '.requirement-item' ).length;
            var typeLabel = 'achievement' === type ? grrAdminData.strings.achievement : grrAdminData.strings.rank;

            var $item = $( '<div>', { 'class': 'requirement-item' } ).css( {
                'margin-bottom': '8px',
                padding: '10px',
                border: '1px solid #ccd0d4',
                'border-left': '4px solid #2271b1',
                background: '#fff',
                display: 'flex',
                'justify-content': 'space-between',
                'align-items': 'center'
            } );

            $item.append( $( '<input>', { type: 'hidden', name: 'requirements[' + index + '][type]', value: type } ) );
            $item.append( $( '<input>', { type: 'hidden', name: 'requirements[' + index + '][id]',   value: itemId } ) );
            $item.append(
                $( '<span>' ).html(
                    '<strong>' + $( '<span>' ).text( typeLabel ).html() + ':</strong> ' +
                    $( '<span>' ).text( itemText ).html()
                )
            );
            $item.append(
                $( '<button>', { type: 'button', 'class': 'remove-requirement button-link-delete' } )
                    .text( grrAdminData.strings.remove )
            );

            $container.append( $item );
            $itemSel.val( null );
            if ( $.fn.select2 ) {
                $itemSel.trigger( 'change' );
            }
        } );

        $( document ).on( 'click', '#requirements-container .remove-requirement', function() {
            $( this ).closest( '.requirement-item' ).remove();
        } );
    }

    // -------------------------------------------------------------------------
    // Edit screen: revoke access (users meta box)
    // -------------------------------------------------------------------------

    /**
     * Handle revoke-access clicks in the Users meta box.
     *
     * @since 1.0.0
     */
    function grrInitRevokeAccess() {
        $( document ).on( 'click', '.revoke-access', function( e ) {
            e.preventDefault();
            var userId   = $( this ).data( 'user-id' );
            var rewardId = $( this ).data( 'reward-id' );

            $.post(
                grrAdminData.ajaxurl,
                {
                    action:              'gamipress_recurring_rewards_revoke_access',
                    user_id:             userId,
                    recurring_reward_id: rewardId,
                    _wpnonce:            grrAdminData.nonce
                },
                function( response ) {
                    if ( response.success ) {
                        location.reload();
                    }
                }
            );
        } );
    }

    // -------------------------------------------------------------------------
    // Add custom page: cycle_day / cycle_month_day toggle
    // -------------------------------------------------------------------------

    /**
     * Show/hide and enable/disable cycle sub-fields on the custom add form.
     *
     * @param {string} cycleType - The selected cycle type value.
     * @since 1.0.0
     */
    function grrToggleCycleFieldsAdd( cycleType ) {
        var $dayRow     = $( '#cycle_day_row_add' );
        var $monthRow   = $( '#cycle_month_day_row_add' );
        var $dayInput   = $( '#cycle_day_add' );
        var $monthInput = $( '#cycle_month_day_add' );

        $dayRow.hide();
        $monthRow.hide();
        $dayInput.prop( 'disabled', true );
        $monthInput.prop( 'disabled', true );

        if ( 'week' === cycleType ) {
            $dayRow.show();
            $dayInput.prop( 'disabled', false );
        } else if ( 'month' === cycleType ) {
            $monthRow.show();
            $monthInput.prop( 'disabled', false );
        }
    }

    // -------------------------------------------------------------------------
    // Add custom page: requirements selector (Select2 + add/remove)
    // -------------------------------------------------------------------------

    /**
     * Initialise the requirements selector on the custom add page.
     *
     * @since 1.0.0
     */
    function grrInitRequirementsAdd() {
        var $typeSel   = $( '#requirement-type-selector-add' );
        var $container = $( '#requirements-container-add' );
        var $btnAdd    = $( '#add-requirement-add' );

        if ( ! $typeSel.length ) {
            return;
        }

        if ( $.fn.select2 ) {
            $typeSel.select2();
            $( '#requirement-item-selector-add' ).select2( { placeholder: grrAdminData.strings.search } );
        }

        $typeSel.on( 'change', function() {
            var type    = $( this ).val();
            var $itemSel = $( '#requirement-item-selector-add' );

            if ( $.fn.select2 && $itemSel.data( 'select2' ) ) {
                $itemSel.select2( 'destroy' );
            }

            $itemSel.html( '<option value="">' + grrAdminData.strings.loading + '</option>' );

            if ( ! type ) {
                $itemSel.html( '<option value="">' + grrAdminData.strings.select + '</option>' );
                if ( $.fn.select2 ) {
                    $itemSel.select2( { placeholder: grrAdminData.strings.search, width: '300px' } );
                }
                return;
            }

            $.ajax( {
                url:      grrAdminData.ajaxurl,
                type:     'POST',
                dataType: 'json',
                data:     {
                    action: 'gamipress_recurring_rewards_search_posts',
                    type: type,
                    _wpnonce: grrAdminData.nonce
                },
                success: function( response ) {
                    var $sel = $( '#requirement-item-selector-add' );
                    $sel.empty().append( '<option value="">' + grrAdminData.strings.select + '</option>' );
                    if ( $.fn.select2 ) {
                        $sel.select2( {
                            data:        response,
                            placeholder: grrAdminData.strings.search,
                            width:       '300px'
                        } );
                    }
                    $sel.trigger( 'change' );
                }
            } );
        } );

        $btnAdd.on( 'click', function() {
            var type     = $typeSel.val();
            var $itemSel = $( '#requirement-item-selector-add' );
            var itemId   = $itemSel.val();
            var itemText = $itemSel.find( 'option:selected' ).text();

            if ( ! type || ! itemId ) {
                return;
            }

            var index     = $container.find( '.requirement-item' ).length;
            var typeLabel = 'achievement' === type ? grrAdminData.strings.achievement : grrAdminData.strings.rank;

            var $item = $( '<div>', { 'class': 'requirement-item' } ).css( {
                'margin-bottom': '8px',
                padding: '10px',
                border: '1px solid #ccd0d4',
                background: '#fff',
                display: 'flex',
                'justify-content': 'space-between',
                'align-items': 'center'
            } );

            $item.append( $( '<input>', { type: 'hidden', name: 'requirements[' + index + '][type]', value: type } ) );
            $item.append( $( '<input>', { type: 'hidden', name: 'requirements[' + index + '][id]',   value: itemId } ) );
            $item.append(
                $( '<span>' ).html(
                    '<strong>' + $( '<span>' ).text( typeLabel ).html() + ':</strong> ' +
                    $( '<span>' ).text( itemText ).html()
                )
            );
            $item.append(
                $( '<button>', { type: 'button', 'class': 'remove-requirement button-link-delete' } )
                    .css( 'color', '#a00' )
                    .text( grrAdminData.strings.remove )
            );

            $container.append( $item );
        } );

        $( document ).on( 'click', '#requirements-container-add .remove-requirement', function() {
            $( this ).closest( '.requirement-item' ).remove();
        } );
    }

    // -------------------------------------------------------------------------
    // Boot
    // -------------------------------------------------------------------------
    $( function() {
        if ( $( '.grr-cycle-day-row, .grr-cycle-month-day-row' ).length ) {
            grrUpdateCycleVisibility();
            $( document ).on( 'change', '[name="cycle_type"]', grrUpdateCycleVisibility );
        }

        grrInitRequirementsEdit();
        grrInitRevokeAccess();

        if ( $( '#cycle_type_add' ).length ) {
            grrToggleCycleFieldsAdd( $( '#cycle_type_add' ).val() );
            $( '#cycle_type_add' ).on( 'change', function() {
                grrToggleCycleFieldsAdd( $( this ).val() );
            } );
        }

        grrInitRequirementsAdd();
    } );

} )( jQuery );
