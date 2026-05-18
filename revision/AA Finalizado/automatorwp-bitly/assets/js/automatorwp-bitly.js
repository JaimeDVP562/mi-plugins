(function ($) {
  var _prefix = "automatorwp_bitly_";

  // On click authorize button
  $("body").on("click", "#" + _prefix + "authorize", function (e) {
    e.preventDefault();

    var button = $(this);
    var wrapper = button.closest("#automatorwp-bitly-authorize-wrapper");

    var api_key = $("#" + _prefix + "api_key").val();

    // Check if response div exists
    var response_wrap = wrapper.find("#" + _prefix + "response");

    if (!response_wrap.length) {
      wrapper.append(
        '<div id="' +
          _prefix +
          'response" style="display: none; margin-top: 10px;"></div>',
      );
      response_wrap = wrapper.find("#" + _prefix + "response");
    }

    // Show error message if api key is empty
    if (undefined === api_key || 0 === api_key.length) {
      response_wrap.addClass("automatorwp-notice-error");
      response_wrap.html("API key is required to connect with Bitly");
      response_wrap.slideDown("fast");
      return;
    }

    response_wrap.slideUp("fast");
    response_wrap.attr("class", "");

    // Show spinner
    wrapper.append(
      '<span class="spinner is-active" style="float: none;"></span>',
    );

    // Disable button
    button.prop("disabled", true);

    $.post(
      automatorwp_bitly.ajaxurl,
      {
        action: "automatorwp_bitly_authorize",
        nonce: automatorwp_bitly.nonce,
        api_key: api_key,
      },
      function (response) {
        response_wrap.addClass(
          "automatorwp-notice-" +
            (true === response.success ? "success" : "error"),
        );
        response_wrap.html(
          undefined !== response.data.message
            ? response.data.message
            : response.data,
        );
        response_wrap.slideDown("fast");

        // Hide spinner
        wrapper.find(".spinner").remove();

        // Enable button
        button.prop("disabled", false);

        // Reload page after 2 seconds on success
        if (true === response.success) {
          setTimeout(function () {
            location.reload();
          }, 2000);
        }
      },
    );
  });
})(jQuery);
