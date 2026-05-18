( function( $ ) {

    // Show/hide fields when checkbox is toggled
    $( document ).on( 'change', '.gamipress-date-time-requirements-enable', function() {
        var $wrap = $( this ).closest( '.gamipress-date-time-requirements-wrap' );
        var $fields = $wrap.find( '.gamipress-date-time-requirements-fields' );

        if( $( this ).is( ':checked' ) ) {
            $fields.show();
        } else {
            $fields.hide();
        }
    } );

    // Show/hide before time field
    $( document ).on( 'change', '.gamipress-date-time-requirements-before-enable', function() {
        var $wrap = $( this ).closest( '.gamipress-date-time-requirements-time-wrap' );
        var $timeField = $wrap.find( '.gamipress-date-time-requirements-time-to' );

        if( $( this ).is( ':checked' ) ) {
            $timeField.show();
        } else {
            $timeField.hide().val('');
        }
    } );

    // Show/hide after time field
    $( document ).on( 'change', '.gamipress-date-time-requirements-after-enable', function() {
        var $wrap = $( this ).closest( '.gamipress-date-time-requirements-time-wrap' );
        var $timeField = $wrap.find( '.gamipress-date-time-requirements-time-from' );

        if( $( this ).is( ':checked' ) ) {
            $timeField.show();
        } else {
            $timeField.hide().val('');
        }
    } );

    // Hook into the update_requirement_data event on each requirement row directly
    $( document ).on( 'update_requirement_data', '.requirement-row', function( e, requirement_details, requirement ) {

        var $requirement = $( this );

        var days = [];
        $requirement.find( '.gamipress-date-time-requirements-day:checked' ).each( function() {
            days.push( $( this ).val() );
        } );

        requirement_details['date_time_requirements_days']      = days;
        requirement_details['date_time_requirements_time_from'] = $requirement.find( '.gamipress-date-time-requirements-time-from' ).val();
        requirement_details['date_time_requirements_time_to']   = $requirement.find( '.gamipress-date-time-requirements-time-to' ).val();

        console.log( 'days saved:', days );

    } );

} )( jQuery );