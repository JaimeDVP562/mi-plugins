(function($){

    // when the group selector changes, propagate value to member selectors
    $('body').on('change', '.automatorwp-ajax-selector select[data-action="automatorwp_googlegroups_get_groups"]', function(){
        var group = $(this).val();
        var container = $(this).closest('.automatorwp-option-form-container');
        var member = container.find('select[data-action="automatorwp_googlegroups_get_members"]');
        if( member.length ){
            // store current group in data attribute for ajax
            member.data('group', group);
            // reset member selection
            member.val('').trigger('change');
        }
    });

    // append group data to ajax request for member selector
    $('body').on('automatorwp_ajax_selector_data', '.automatorwp-ajax-selector select[data-action="automatorwp_googlegroups_get_members"]', function(e,data,element){
        data.group = element.data('group') || '';
    });

})(jQuery);
