(function ($) {
    var prefix = "automatorwp-klaviyo-";
    var _prefix = "automatorwp_klaviyo_";

    // On click authorize button
    $("body").on(
        "click",
        ".automatorwp_settings #" + _prefix + "authorize",
        function (e) {
            e.preventDefault();

            var button = $(this);
            var wrapper = button.parent();

            var key = $("#" + _prefix + "key").val();
            var secret = $("#" + _prefix + "secret").val();

            // Check if response div exists
            var response_wrap = wrapper.find("#" + _prefix + "response");

            if (!response_wrap.length) {
                wrapper.append(
                    '<div id="' +
                        _prefix +
                        'response" style="display: none; margin-top: 10px;"></div>'
                );
                response_wrap = wrapper.find("#" + _prefix + "response");
            }

            // Show error message if not correctly configured
            if (key.length === 0 || secret.length === 0) {
                response_wrap.addClass("automatorwp-notice-error");
                response_wrap.html(
                    "All fields are required to connect with Klaviyo"
                );
                response_wrap.slideDown("fast");
                return;
            }

            response_wrap.slideUp("fast");
            response_wrap.attr("class", "");

            // Show spinner
            wrapper.append(
                '<span class="spinner is-active" style="float: none;"></span>'
            );

            // Disable button
            button.prop("disabled", true);

            $.post(
                ajaxurl,
                {
                    action: "automatorwp_klaviyo_authorize",
                    nonce: automatorwp_klaviyo.nonce,
                    key: key,
                    secret: secret,
                },
                function (response) {
                    // Add class automatorwp-notice-success on successful unlock, if not will add the class automatorwp-notice-error
                    response_wrap.addClass(
                        "automatorwp-notice-" +
                            (response.success === true ? "success" : "error")
                    );
                    response_wrap.html(
                        response.data.message !== undefined
                            ? response.data.message
                            : response.data
                    );
                    response_wrap.slideDown("fast");

                    // Hide spinner
                    wrapper.find(".spinner").remove();

                  // Redirect on success
                if( response.success === true && response.data.redirect_url !== undefined ) {
                    window.location = response.data.redirect_url;
                    return;
                }
                
                    // Enable button
                    button.prop("disabled", false);
                }
            );
        }
    );

    // On change list
    $("body").on(
        "change",
        ".automatorwp-action-klaviyo-add-profile-task .cmb2-id-list select",
        function (e, first_change) {
            var list = $(this).closest(".cmb-row");
            var task_list = list.next(".cmb2-id-task");

            var list_id = $(this).val();

            if (first_change === undefined) {
                first_change = false;
            }

            if (list_id === "any" || list_id === "" || list_id === null) {
                // Hide the term selector
                if (first_change) {
                    task_list.hide();
                } else {
                    task_list.slideUp("fast");
                }
            } else {
                var task_selector = task_list.find("select.select2-hidden-accessible");

                // Remove Select2 element
                task_selector.next(".select2").remove();

                // Update the list (since we do not use the table attribute, lets to use it as list)
                task_selector.data("table", list_id);

                // Reset the selector
                task_selector.removeAttr("data-select2-id");

                // Init it again
                automatorwp_ajax_selector(task_selector);

                // Show the term selector
                if (first_change) {
                    task_list.show();
                } else {
                    task_list.slideDown("fast");
                }
            }
        }
    );
})(jQuery);
