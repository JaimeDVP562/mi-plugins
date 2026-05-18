(function ($) {
  function gamipress_learndash_points_gateway_update_block_checkout(checkout) {
    // Append the points cart details
    if (
      checkout.find(".gamipress-learndash-points-gateway-checkout-details")
        .length === 0
    ) {
      checkout
        .find(".wc-block-components-totals-wrapper")
        .last()
        .after(gamipress_learndash_points_gateway_block_checkout.cart_details);
    }

    var payment_method = checkout
      .find("input.wc-block-components-radio-control__input:checked")
      .val();

    if (
      payment_method !== undefined &&
      payment_method.startsWith("gamipress_")
    ) {
      var points_type = payment_method.replace("gamipress_", "");

      // Hide previously active gateway
      $(
        ".gamipress-learndash-points-gateway-checkout-details .gamipress-learndash-points-gateway-active",
      )
        .removeClass("gamipress-learndash-points-gateway-active")
        .hide();

      // Show current active gateway
      checkout
        .find(".gamipress-learndash-points-gateway-checkout-details")
        .show();
      checkout
        .find(
          ".gamipress-learndash-points-gateway-checkout-details #block-payment-method-gamipress-" +
            points_type +
            "-user-balance-wrap",
        )
        .addClass("gamipress-learndash-points-gateway-active")
        .show();
      checkout
        .find(
          ".gamipress-learndash-points-gateway-checkout-details #block-payment-method-gamipress-" +
            points_type +
            "-required-balance-wrap",
        )
        .addClass("gamipress-learndash-points-gateway-active")
        .show();
      checkout
        .find(
          ".gamipress-learndash-points-gateway-checkout-details #block-payment-method-gamipress-" +
            points_type +
            "-new-balance-wrap",
        )
        .addClass("gamipress-learndash-points-gateway-active")
        .show();
    } else {
      // Hide previously active gateway
      checkout
        .find(".gamipress-learndash-points-gateway-checkout-details")
        .hide();
      $(
        ".gamipress-learndash-points-gateway-checkout-details .gamipress-learndash-points-gateway-active",
      )
        .removeClass("gamipress-learndash-points-gateway-active")
        .hide();
    }
  }

  $("body").on(
    "change",
    ".wp-block-learndash-checkout input.wc-block-components-radio-control__input",
    function () {
      var checkout = $(this).closest(".wp-block-learndash-checkout");
      gamipress_learndash_points_gateway_update_block_checkout(checkout);
    },
  );

  $("body").ready(function () {
    $(
      ".wp-block-learndash-checkout input.wc-block-components-radio-control__input:checked",
    ).each(function () {
      var checkout = $(this).closest(".wp-block-learndash-checkout");
      gamipress_learndash_points_gateway_update_block_checkout(checkout);
    });
  });
})(jQuery);
