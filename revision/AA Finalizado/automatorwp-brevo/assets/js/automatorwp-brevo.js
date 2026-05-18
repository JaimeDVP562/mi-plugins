(function ($) {
  var prefix = "automatorwp-brevo-";
  var _prefix = "automatorwp_brevo_";

  // On click authorize button
  $("body").on(
    "click",
    ".automatorwp_settings #" + _prefix + "authorize",
    function (e) {
      e.preventDefault();
      var button = $(this);
      var wrapper = button.parent();

      var token = $("#" + _prefix + "token").val();

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
      if (token.length === 0) {
        response_wrap.addClass("automatorwp-notice-error");
        response_wrap.html("API token is required to connect with Brevo");
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
          action: "automatorwp_brevo_authorize",
          nonce: automatorwp_brevo.nonce,
          token: token,
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
          if (
            response.success === true &&
            response.data.redirect_url !== undefined
          ) {
            window.location = response.data.redirect_url;
            return;
          }

          // Enable button
          button.prop("disabled", false);
        }
      );
    }
  );
  // On change folder
  $("body").on(
    "change",
    ".automatorwp-action-brevo-add-contact-to-list .cmb2-id-folder select, " +
      ".automatorwp-action-brevo-remove-contact-from-list .cmb2-id-folder select",
    function (e, first_change) {
      var folder = $(this).closest(".cmb-row");
      var list_folder = folder.next(".cmb2-id-list");

      var folder_id = $(this).val();

      if (first_change === undefined) {
        first_change = false;
      }

      if (folder_id === "any" || folder_id === "" || folder_id === null) {
        // Hide the term selector
        if (first_change) {
          list_folder.hide();
        } else {
          list_folder.slideUp("fast");
        }
      } else {
        var list_selector = list_folder.find(
          "select.select2-hidden-accessible"
        );

        // Remove Select2 element
        list_selector.next(".select2").remove();

        // Update the folder (since we do not use the table attribute, lets to use it as folder)
        list_selector.data("table", folder_id);

        if (!first_change) {
          // Update the the term value
          list_selector.val("");
        }

        // Reset the selector
        list_selector.removeAttr("data-select2-id");

        // Init it again
        automatorwp_ajax_selector(list_selector);

        // Show the term selector
        if (first_change) {
          list_folder.show();
        } else {
          list_folder.slideDown("fast");
        }
      }
    }
  );

  // On change list
  $("body").on(
    "change",
    ".automatorwp-action-brevo-remove-contact-from-list .cmb2-id-list select",
    function (e, first_change) {
      var list = $(this).closest(".cmb-row");
      var contact_list = list.next(".cmb2-id-email");

      var list_id = $(this).val();

      if (first_change === undefined) {
        first_change = false;
      }

      if (list_id === "any" || list_id === "" || list_id === null) {
        // Hide the term selector
        if (first_change) {
          contact_list.hide();
        } else {
          contact_list.slideUp("fast");
        }
      } else {
        var contact_selector = contact_list.find(
          "select.select2-hidden-accessible"
        );

        // Remove Select2 element
        contact_selector.next(".select2").remove();

        // Update the list (since we do not use the table attribute, lets to use it as list)
        contact_selector.data("table", list_id);

        // Reset the selector
        contact_selector.removeAttr("data-select2-id");

        // Init it again
        automatorwp_ajax_selector(contact_selector);

        // Show the term selector
        if (first_change) {
          contact_list.show();
        } else {
          contact_list.slideDown("fast");
        }
      }
    }
  );

  // On change pipeline
  $("body").on(
    "change",
    ".automatorwp-action-brevo-create-deal .cmb2-id-pipeline select",
    function (e, first_change) {
      var pipeline = $(this).closest(".cmb-row");
      var stage_pipeline = pipeline.next(".cmb2-id-stage");

      var pipeline_id = $(this).val();

      if (first_change === undefined) {
        first_change = false;
      }

      if (pipeline_id === "any" || pipeline_id === "" || pipeline_id === null) {
        // Hide the term selector
        if (first_change) {
          stage_pipeline.hide();
        } else {
          stage_pipeline.slideUp("fast");
        }
      } else {
        var stage_selector = stage_pipeline.find(
          "select.select2-hidden-accessible"
        );

        // Remove Select2 element
        stage_selector.next(".select2").remove();

        // Update the pipeline (since we do not use the table attribute, lets to use it as pipeline)
        stage_selector.data("table", pipeline_id);

        if (!first_change) {
          // Update the the term value
          stage_selector.val("");
        }

        // Reset the selector
        stage_selector.removeAttr("data-select2-id");

        // Init it again
        automatorwp_ajax_selector(stage_selector);

        // Show the term selector
        if (first_change) {
          stage_pipeline.show();
        } else {
          stage_pipeline.slideDown("fast");
        }
      }
    }
  );

  // On click on an option, check if form contains the folder selector
  $("body").on(
    "click",
    ".automatorwp-automation-item-label > .automatorwp-option",
    function (e) {
      var item = $(this).closest(
        '.automatorwp-automation-item[class*="brevo"]'
      );
      var option = $(this).data("option");
      var option_form = item.find(
        '.automatorwp-option-form-container[data-option="' + option + '"]'
      );

      var folder_selector = option_form.find(".cmb2-id-folder");

      if (folder_selector !== undefined) {
        folder_selector
          .find("select.select2-hidden-accessible")
          .trigger("change", [true]);
      }

      var list_selector = option_form.find(".cmb2-id-list");

      if (list_selector !== undefined) {
        list_selector
          .find("select.select2-hidden-accessible")
          .trigger("change", [true]);
      }

      var pipeline_selector = option_form.find(".cmb2-id-pipeline");

      if (pipeline_selector !== undefined) {
        pipeline_selector
          .find("select.select2-hidden-accessible")
          .trigger("change", [true]);
      }
    }
  );
})(jQuery);
