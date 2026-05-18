jQuery(document).ready(function ($) {
  $("#gamipress_ld_apply_points_btn").on("click", function (e) {
    e.preventDefault();

    var pointsToApply = $("#gamipress_ld_points_to_apply").val();
    var courseId = $("#gamipress_ld_course_id").val();
    var chosenPointType = $("#gamipress_ld_selected_point_type").val();
    var messageContainer = $("#gamipress_ld_partial_payments_message");

    if (pointsToApply <= 0 || pointsToApply === "") {
      messageContainer
        .css("color", "red")
        .text("Please enter a valid amount of points.")
        .show();
      return;
    }

    $.ajax({
      url: gamipress_ld_partial_payments_vars.ajax_url,
      type: "POST",
      dataType: "json",
      data: {
        action: "gamipress_ld_apply_discount",
        nonce: gamipress_ld_partial_payments_vars.nonce,
        points: pointsToApply,
        course_id: courseId,
        point_type: chosenPointType,
      },
      beforeSend: function () {
        messageContainer
          .css("color", "black")
          .text("Applying discount...")
          .show();
      },
      success: function (response) {
        if (response.success) {
          messageContainer
            .css("color", "green")
            .text(response.data.message)
            .show();
          setTimeout(function () {
            location.reload();
          }, 1500);
        } else {
          messageContainer
            .css("color", "red")
            .text(response.data.message)
            .show();
        }
      },
      error: function () {
        messageContainer
          .css("color", "red")
          .text("A server error occurred. Please try again.")
          .show();
      },
    });
  });

  $("#gamipress_ld_remove_points_btn").on("click", function (e) {
    e.preventDefault();

    var courseId = $("#gamipress_ld_course_id").val();
    var messageContainer = $("#gamipress_ld_partial_payments_message");

    $.ajax({
      url: gamipress_ld_partial_payments_vars.ajax_url,
      type: "POST",
      dataType: "json",
      data: {
        action: "gamipress_ld_remove_discount",
        nonce: gamipress_ld_partial_payments_vars.nonce,
        course_id: courseId,
      },
      beforeSend: function () {
        messageContainer
          .css("color", "black")
          .text("Removing discount...")
          .show();
      },
      success: function (response) {
        if (response.success) {
          messageContainer
            .css("color", "green")
            .text(response.data.message)
            .show();
          setTimeout(function () {
            location.reload();
          }, 1500);
        } else {
          messageContainer
            .css("color", "red")
            .text(response.data.message)
            .show();
        }
      },
      error: function () {
        messageContainer
          .css("color", "red")
          .text("A server error occurred. Please try again.")
          .show();
      },
    });
  });
});
