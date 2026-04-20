(function ( $ ) {

    // Add-ons tabs
    $('.automatorwp_page_automatorwp_add_ons .wp-filter a').on('click', function(e) {
        e.preventDefault();

        if( $(this).hasClass('current') ) {
            return;
        }

        var current = $(this).closest('.wp-filter').find('a.current');

        // Toggle plugin cards visibility
        $('.automatorwp-plugin-card.' + current.data('target')).hide();

        $('.automatorwp-plugin-card.' + $(this).data('target')).fadeIn(250);

        // Toggle current class
        current.removeClass('current');
        $(this).addClass('current');
    });

    // Hide all plugins cards
    $('.automatorwp-add-ons .automatorwp-plugin-card:not(.' + $('.automatorwp_page_automatorwp_add_ons .wp-filter a.current').data('target') + ')').hide();

    // Trigger click on first tab
    $('.automatorwp_page_automatorwp_add_ons .wp-filter a.current').trigger('click');

})( jQuery );