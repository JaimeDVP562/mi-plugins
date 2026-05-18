(function ( $ ) {

// Listen for our change to our trigger type selectors
$('requirements-list').on( 'change', '.select-trigger-type', function(){

    // Grab our selected trigger type and achievement selector
    var trigger_type = $(this).val();
    var inactive_days_input = $(this).siblings('.nar_inactive_days');

    if( trigger_type === 'gamipress_nar_user_has_not_logged_in' 
        || trigger_type === 'gamipress_nar_user_has_not_earned_any_achievement'
        || trigger_type === 'gamipress_nar_user_has_not_earned_achievement_type'
        || trigger_type === 'gamipress_nar_user_has_not_earned_points'
        || trigger_type === 'gamipress_nar_user_has_not_earned_points_type'
        || trigger_type === 'gamipress_nar_user_has_not_earned_ranks'
        || trigger_type === 'gamipress_nar_user_has_not_earned_rank_type'
     ) {
        inactive_days_input.show();
     } else {
        inactive_days_input.hide();
     }

} );

// Loop requirement list items to show/hide amount input on initial load
$( '.requirements-list li' ).each(function(){
    // Grab our selected trigger type and achievement selector
    var trigger_type = $(this).val();
    var inactive_days_input = $(this).siblings('.nar_inactive_days');
    
    if( trigger_type === 'gamipress_nar_user_has_not_logged_in' 
        || trigger_type === 'gamipress_nar_user_has_not_earned_any_achievement'
        || trigger_type === 'gamipress_nar_user_has_not_earned_achievement_type'
        || trigger_type === 'gamipress_nar_user_has_not_earned_points'
        || trigger_type === 'gamipress_nar_user_has_not_earned_points_type'
        || trigger_type === 'gamipress_nar_user_has_not_earned_ranks'
        || trigger_type === 'gamipress_nar_user_has_not_earned_rank_type' 
    ) {
        inactive_days_input.show();
       }else{
        inactive_days_input.hide();
       }

} );

$('.requirements-list').on('update_requirement_data', '.requirement-row', function(e, requirement_details, requirement){
    var $req = $(requirement);
    
    var valor = $req.find('.nar_inactive_days input').val();
    
    // Add custom field
    if( valor !== undefined ) {
        requirement_details.days = valor;
    }
} );

})( jQuery );