(function ($) {
  var _prefix = "automatorwp_cohere_";

  // On click authorize button
  $("body").on(
    "click",
    ".automatorwp_settings #" + _prefix + "authorize",
    function (e) {
      e.preventDefault();
      var button = $(this);
      var wrapper = button.parent();

      var api_key = $("#" + _prefix + "api_key").val();

      var response_wrap = wrapper.find("#" + _prefix + "response");
      if (!response_wrap.length) {
        wrapper.append(
          '<div id="' + _prefix + 'response" style="display: none; margin-top: 10px;"></div>'
        );
        response_wrap = wrapper.find("#" + _prefix + "response");
      }

      if (api_key.length === 0) {
        response_wrap.addClass("automatorwp-notice-error");
        response_wrap.html("API Key is required to connect with Cohere.");
        response_wrap.slideDown("fast");
        return;
      }

      response_wrap.slideUp("fast");
      response_wrap.attr("class", "");

      wrapper.append('<span class="spinner is-active" style="float: none;"></span>');
      button.prop("disabled", true);

      $.post(
        ajaxurl,
        {
          action: "automatorwp_cohere_authorize",
          nonce: automatorwp_cohere.nonce,
          api_key: api_key,
        },
        function (response) {
          response_wrap.addClass(
            "automatorwp-notice-" + (response.success === true ? "success" : "error")
          );
          response_wrap.html(
            response.data.message !== undefined ? response.data.message : response.data
          );
          response_wrap.slideDown("fast");

          wrapper.find(".spinner").remove();

          if (response.success === true && response.data.redirect_url !== undefined) {
            window.location = response.data.redirect_url;
            return;
          }

          button.prop("disabled", false);
        }
      );
    }
  );
})(jQuery);
