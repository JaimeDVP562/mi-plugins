/**
 * SLP Dynamic Redirects – Parameter type
 * Admin UI script.
 *
 * Hides the "value" field when the selected condition does not need a value
 * (i.e. "is empty" / "is not empty").
 */
( function( $ ) {
    'use strict';

    // Conditions that do NOT need a comparison value.
    var noValueConditions = [ 'empty', 'not_empty' ];

    /**
     * Show or hide the value input row depending on the selected condition.
     *
     * @param {jQuery} $row  The .slp-dr-parameter-fields wrapper.
     */
    function toggleValueRow( $row ) {
        var condition  = $row.find( '.slp-dr-condition' ).val();
        var $valueRow  = $row.find( '.slp-dr-value-row' );

        if ( noValueConditions.indexOf( condition ) !== -1 ) {
            $valueRow.hide();
            $valueRow.find( 'input' ).val( '' ); // clear so no stale value is saved
        } else {
            $valueRow.show();
        }
    }

    /**
     * Initialise all existing parameter field rows on page load.
     */
    function initAll() {
        $( '.slp-dr-parameter-fields' ).each( function() {
            toggleValueRow( $( this ) );
        } );
    }

    // Re-run when the user changes the condition dropdown.
    $( document ).on( 'change', '.slp-dr-parameter-fields .slp-dr-condition', function() {
        toggleValueRow( $( this ).closest( '.slp-dr-parameter-fields' ) );
    } );

    // Also initialise if a new redirect row is dynamically added (e.g. via "Add rule" button).
    $( document ).on( 'slp_dynamic_redirects_row_added', function( e, $newRow ) {
        var $paramFields = $newRow.find( '.slp-dr-parameter-fields' );
        if ( $paramFields.length ) {
            toggleValueRow( $paramFields );
        }
    } );

    // Run on DOM ready.
    $( initAll );

} )( jQuery );
