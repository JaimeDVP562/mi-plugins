(function ($) {
  "use strict";

  // Main checkbox → show/hide the full block.
  $(document).on(
    "change",
    ".gamipress-date-time-requirements-enable",
    function () {
      const $wrap = $(this).closest(".gamipress-date-time-requirements-wrap");
      const $fields = $wrap.find(".gamipress-date-time-requirements-fields");

      if ($(this).is(":checked")) {
        $fields.show();
      } else {
        // Reset everything so nothing leaks into the save payload.
        $fields.hide();
        $fields
          .find(".gamipress-date-time-requirements-day")
          .prop("checked", false);
        $fields
          .find(".gamipress-date-time-requirements-before-enable")
          .prop("checked", false);
        $fields
          .find(".gamipress-date-time-requirements-after-enable")
          .prop("checked", false);
        $fields
          .find(".gamipress-date-time-requirements-time-to")
          .hide()
          .val("");
        $fields
          .find(".gamipress-date-time-requirements-time-from")
          .hide()
          .val("");
      }
    },
  );

  // "Before" checkbox → show/hide time-to picker.
  $(document).on(
    "change",
    ".gamipress-date-time-requirements-before-enable",
    function () {
      const $picker = $(this)
        .closest(".gamipress-date-time-requirements-time-wrap")
        .find(".gamipress-date-time-requirements-time-to");

      $(this).is(":checked") ? $picker.show() : $picker.hide().val("");
    },
  );

  // "After" checkbox → show/hide time-from picker.
  $(document).on(
    "change",
    ".gamipress-date-time-requirements-after-enable",
    function () {
      const $picker = $(this)
        .closest(".gamipress-date-time-requirements-time-wrap")
        .find(".gamipress-date-time-requirements-time-from");

      $(this).is(":checked") ? $picker.show() : $picker.hide().val("");
    },
  );

  // Collect data before the AJAX save call.
  $(document).on(
    "update_requirement_data",
    ".requirement-row",
    function (e, requirementDetails) {
      const $row = $(this);
      const days = [];

      $row
        .find(".gamipress-date-time-requirements-day:checked")
        .each(function () {
          days.push($(this).val());
        });

      requirementDetails["date_time_requirements_days"] = days;
      requirementDetails["date_time_requirements_time_from"] = $row
        .find(".gamipress-date-time-requirements-time-from")
        .val();
      requirementDetails["date_time_requirements_time_to"] = $row
        .find(".gamipress-date-time-requirements-time-to")
        .val();
    },
  );
})(jQuery);
