(function( $ ) {

    // Listen for our change to our trigger type selectors
    $('.requirements-list').on( 'change', '.select-trigger-type', function() {

        // Grab our selected trigger type and achievement selector
        var trigger_type = $(this).val();
        var count_input = $(this).siblings('.referrals-count');

        // Toggle count field visibility
        if(
            trigger_type === 'gamipress_referrals_referral_visits'
            || trigger_type === 'gamipress_referrals_referral_signups'
            || trigger_type === 'gamipress_referrals_woocommerce_referral_sales'
            || trigger_type === 'gamipress_referrals_woocommerce_referral_sales_refunds'
            || trigger_type === 'gamipress_referrals_easy_digital_downloads_referral_sales'
            || trigger_type === 'gamipress_referrals_easy_digital_downloads_referral_sales_refunds'
        ) {
            count_input.show();
        } else {
            count_input.hide();
        }

    });

    // Loop requirement list items to show/hide count input on initial load
    $('.requirements-list li').each(function() {

        // Grab our selected trigger type and achievement selector
        var trigger_type = $(this).find('.select-trigger-type').val();
        var count_input = $(this).find('.referrals-count');

        // Toggle count field visibility
        if(
            trigger_type === 'gamipress_referrals_referral_visits'
            || trigger_type === 'gamipress_referrals_referral_signups'
            || trigger_type === 'gamipress_referrals_woocommerce_referral_sales'
            || trigger_type === 'gamipress_referrals_woocommerce_referral_sales_refunds'
            || trigger_type === 'gamipress_referrals_easy_digital_downloads_referral_sales'
            || trigger_type === 'gamipress_referrals_easy_digital_downloads_referral_sales_refunds'
        ) {
            count_input.show();
        } else {
            count_input.hide();
        }

    });

    $('.requirements-list').on( 'update_requirement_data', '.requirement-row', function(e, requirement_details, requirement) {

        // Add count field
        if(
            requirement_details.trigger_type === 'gamipress_referrals_referral_visits'
            || requirement_details.trigger_type === 'gamipress_referrals_referral_signups'
            || requirement_details.trigger_type === 'gamipress_referrals_woocommerce_referral_sales'
            || requirement_details.trigger_type === 'gamipress_referrals_woocommerce_referral_sales_refunds'
            || requirement_details.trigger_type === 'gamipress_referrals_easy_digital_downloads_referral_sales'
            || requirement_details.trigger_type === 'gamipress_referrals_easy_digital_downloads_referral_sales_refunds'
        ) {
            requirement_details.referrals_count = requirement.find( '.referrals-count input' ).val();
        }

    });

})( jQuery );