(function ($) {
  var gcg = {
    groupIndex: 0,
    currentCalendar: [],
    $overlay: null,
    $modal: null,
  };

  function init() {
    gcg.$overlay = $("#gamipress-calendar-generator-overlay");
    gcg.$modal = $("#gamipress-calendar-generator-modal");

    if (!gcg.$overlay.length) return;

    injectButton();
    bindEvents();
  }

  /**
   * Inject the "Generate Calendar" button next to the "Add New" button.
   * .page-title-action is a sibling of h1.wp-heading-inline, not a child.
   */
  function injectButton() {
    // Bail if button already exists (prevents duplicates on re-init)
    if ($("#gcg-open-btn").length) return;

    var $btn = $("<a>", {
      href: "#",
      id: "gcg-open-btn",
      class: "page-title-action",
      text: gamipress_calendar_generator.generate_calendar_text,
    });

    // Insert after the first .page-title-action sibling of the heading
    var $addNew = $(".wrap .page-title-action").first();

    if ($addNew.length) {
      $addNew.after($btn);
    } else {
      // Fallback: append after the h1
      $(".wrap h1.wp-heading-inline").first().after($btn);
    }
  }

  function bindEvents() {
    $(document).on("click", "#gcg-open-btn", function (e) {
      e.preventDefault();
      openModal();
    });

    gcg.$overlay.on("click", ".gcg-cancel-btn", closeModal);

    // Close on overlay click (but not on modal click)
    gcg.$overlay.on("click", function (e) {
      if ($(e.target).is(gcg.$overlay)) {
        closeModal();
      }
    });

    $("#gcg-add-reward-btn").on("click", function () {
      addRewardGroup();
    });

    gcg.$modal.on("click", ".gcg-remove-reward-btn", function () {
      $(this).closest(".gcg-reward-group").remove();
      renumberGroups();
    });

    gcg.$modal.on("change", ".gcg-reward-type", function () {
      handleTypeChange($(this).closest(".gcg-reward-group"));
    });

    gcg.$modal.on("change", ".gcg-achievement-mode", function () {
      var $group = $(this).closest(".gcg-reward-group");
      if ($(this).val() === "specific") {
        $group.find(".gcg-achievement-specific").slideDown(200);
        initAchievementSelect($group);
      } else {
        $group.find(".gcg-achievement-specific").slideUp(200);
      }
    });

    gcg.$modal.on("change", ".gcg-rank-mode", function () {
      var $group = $(this).closest(".gcg-reward-group");
      if ($(this).val() === "specific") {
        $group.find(".gcg-rank-specific").slideDown(200);
        initRankSelect($group);
      } else {
        $group.find(".gcg-rank-specific").slideUp(200);
      }
    });

    $("#gcg-reorder-btn").on("click", function () {
      updatePreview();
    });

    $("#gcg-generate-btn").on("click", function () {
      updatePreview();
      submitGenerate();
    });
  }

  function openModal() {
    gcg.$overlay.fadeIn(200);
    $("body").addClass("gcg-modal-open");

    // Add a default reward group if the list is empty
    if (!$("#gcg-rewards-list .gcg-reward-group").length) {
      addRewardGroup();
    }

    updatePreview();
  }

  function closeModal() {
    gcg.$overlay.fadeOut(200);
    $("body").removeClass("gcg-modal-open");
  }

  function addRewardGroup() {
    gcg.groupIndex++;

    var template = $("#gcg-reward-group-template").html();
    template = template.replace(/\{\{INDEX\}\}/g, gcg.groupIndex);
    template = template.replace(
      /\{\{NUM\}\}/g,
      $("#gcg-rewards-list .gcg-reward-group").length + 1,
    );

    var $group = $(template);
    $("#gcg-rewards-list").append($group);

    handleTypeChange($group);
  }

  function renumberGroups() {
    $("#gcg-rewards-list .gcg-reward-group").each(function (i) {
      $(this)
        .find(".gcg-group-num")
        .text(i + 1);
    });
  }

  function handleTypeChange($group) {
    var raw = $group.find(".gcg-reward-type").val() || "none";

    $group.find(".gcg-type-fields").hide();

    if (raw.indexOf("points:") === 0) {
      $group.find(".gcg-points-fields").show();
    } else if (raw === "achievement") {
      $group.find(".gcg-achievement-fields").show();
    } else if (raw === "rank") {
      $group.find(".gcg-rank-fields").show();
    }
    // 'none' — no extra fields needed
  }

  function initAchievementSelect($group) {
    var $select = $group.find(".gcg-achievement-select");
    if ($select.hasClass("select2-hidden-accessible")) return;

    $select.gamipress_select2({
      ajax: {
        url: ajaxurl,
        dataType: "json",
        delay: 250,
        type: "POST",
        data: function (params) {
          return {
            q: params.term,
            action: "gamipress_get_achievements_options",
            nonce: gamipress_calendar_generator.nonce,
          };
        },
        processResults: gamipress_select2_posts_process_results,
      },
      escapeMarkup: function (markup) {
        return markup;
      },
      templateResult: gamipress_select2_posts_template_result,
      theme: "default gamipress-select2",
      placeholder: gamipress_calendar_generator.achievement_placeholder,
      allowClear: true,
      multiple: false,
    });
  }

  function initRankSelect($group) {
    var $select = $group.find(".gcg-rank-select");
    if ($select.hasClass("select2-hidden-accessible")) return;

    $select.gamipress_select2({
      ajax: {
        url: ajaxurl,
        dataType: "json",
        delay: 250,
        type: "POST",
        data: function (params) {
          return {
            q: params.term,
            action: "gamipress_get_ranks_options",
            nonce: gamipress_calendar_generator.nonce,
          };
        },
        processResults: gamipress_select2_posts_process_results,
      },
      escapeMarkup: function (markup) {
        return markup;
      },
      templateResult: gamipress_select2_posts_template_result,
      theme: "default gamipress-select2",
      placeholder: gamipress_calendar_generator.rank_placeholder,
      allowClear: true,
      multiple: false,
    });
  }

  function collectGroups() {
    var groups = [];

    $("#gcg-rewards-list .gcg-reward-group").each(function () {
      var $g = $(this);
      var raw = $g.find(".gcg-reward-type").val() || "none";
      var reps = parseInt($g.find(".gcg-reward-repetitions").val(), 10);
      if (isNaN(reps) || reps < 0) reps = 0;

      var group = { reps: reps };

      if (raw.indexOf("points:") === 0) {
        group.reward_type = "points";
        group.points_type = raw.split(":")[1];
        group.points_min = parseInt($g.find(".gcg-points-min").val(), 10) || 0;
        group.points_max = parseInt($g.find(".gcg-points-max").val(), 10) || 0;
        if (group.points_max < group.points_min)
          group.points_max = group.points_min;
      } else if (raw === "achievement") {
        group.reward_type = "achievement";
        group.achievement_mode =
          $g.find(".gcg-achievement-mode").val() || "random";
        group.achievement_id =
          parseInt($g.find(".gcg-achievement-select").val(), 10) || 0;
        group.achievement_name =
          $g.find(".gcg-achievement-select option:selected").text() || "";
      } else if (raw === "rank") {
        group.reward_type = "rank";
        group.rank_mode = $g.find(".gcg-rank-mode").val() || "random";
        group.rank_id = parseInt($g.find(".gcg-rank-select").val(), 10) || 0;
        group.rank_name =
          $g.find(".gcg-rank-select option:selected").text() || "";
      } else {
        group.reward_type = "none";
      }

      groups.push(group);
    });

    return groups;
  }

  function buildDayItem(group, day) {
    var item = { day: day, reward_type: group.reward_type };

    if (group.reward_type === "points") {
      var pts = randInt(group.points_min, group.points_max);
      item.points = pts;
      item.points_type = group.points_type;
      item.label = pts + " " + group.points_type;
      item.preview_label = pts + " " + group.points_type;
      item.preview_class = "gcg-day-points";
      item.preview_icon = "dashicons-star-filled";
    } else if (group.reward_type === "achievement") {
      item.achievement_mode = group.achievement_mode;
      item.achievement_id = group.achievement_id;
      var achName =
        group.achievement_mode === "specific" && group.achievement_name
          ? group.achievement_name
          : gamipress_calendar_generator.random_achievement_text;
      item.label = achName;
      item.preview_label = achName;
      item.preview_class = "gcg-day-achievement";
      item.preview_icon = "dashicons-awards";
    } else if (group.reward_type === "rank") {
      item.rank_mode = group.rank_mode;
      item.rank_id = group.rank_id;
      var rankName =
        group.rank_mode === "specific" && group.rank_name
          ? group.rank_name
          : gamipress_calendar_generator.random_rank_text;
      item.label = rankName;
      item.preview_label = rankName;
      item.preview_class = "gcg-day-rank";
      item.preview_icon = "dashicons-rank";
    } else {
      // Empty day
      item.label = gamipress_calendar_generator.empty_day_text;
      item.preview_label = gamipress_calendar_generator.empty_day_text;
      item.preview_class = "gcg-day-none";
      item.preview_icon = "dashicons-marker";
    }

    return item;
  }

  function generateCalendarData() {
    var numDays = parseInt($("#gcg-num-days").val(), 10) || 30;
    if (numDays < 1) numDays = 1;

    var groups = collectGroups();

    // Separate filler groups (reps = 0) from counted groups (reps > 0)
    var fillers = groups.filter(function (g) {
      return g.reps === 0;
    });
    var counted = groups.filter(function (g) {
      return g.reps > 0;
    });

    // Build the slot pool from counted groups
    var slots = [];
    counted.forEach(function (g) {
      for (var i = 0; i < g.reps; i++) {
        slots.push(g);
      }
    });

    // Cap to numDays if we already have too many
    if (slots.length > numDays) {
      slots = slots.slice(0, numDays);
    }

    // Fill remaining slots
    var remaining = numDays - slots.length;
    if (remaining > 0) {
      if (fillers.length > 0) {
        for (var i = 0; i < remaining; i++) {
          slots.push(fillers[i % fillers.length]);
        }
      } else {
        // No fillers configured — fill with empty days
        for (var i = 0; i < remaining; i++) {
          slots.push({ reps: 0, reward_type: "none" });
        }
      }
    }

    // Shuffle and assign day numbers
    slots = shuffleArray(slots);

    return slots.map(function (g, idx) {
      return buildDayItem(g, idx + 1);
    });
  }

  function updatePreview() {
    gcg.currentCalendar = generateCalendarData();
    renderPreview(gcg.currentCalendar);
  }

  function renderPreview(calendar) {
    var $grid = $("#gcg-preview-grid");
    $grid.empty();

    if (!calendar || !calendar.length) {
      $grid.html(
        '<p class="gcg-preview-placeholder">' +
          gamipress_calendar_generator.no_days_text +
          "</p>",
      );
      return;
    }

    calendar.forEach(function (item) {
      var $day = $("<div>", {
        class: "gcg-preview-day " + (item.preview_class || "gcg-day-none"),
      });

      $day.append($("<span>", { class: "gcg-day-num", text: item.day }));
      $day.append(
        $("<span>", {
          class: "dashicons " + (item.preview_icon || "dashicons-marker"),
        }),
      );
      $day.append(
        $("<span>", { class: "gcg-day-label", text: item.preview_label }),
      );

      $grid.append($day);
    });
  }

  function submitGenerate() {
    if (!gcg.currentCalendar || !gcg.currentCalendar.length) {
      alert(gamipress_calendar_generator.no_calendar_text);
      return;
    }

    var $btn = $("#gcg-generate-btn");
    var $spinner = $("#gcg-spinner");

    $btn.prop("disabled", true);
    $spinner.addClass("is-active");

    // Build the minimal payload to send to PHP
    var calendarPayload = gcg.currentCalendar.map(function (item) {
      var d = {
        day: item.day,
        reward_type: item.reward_type,
        label: item.label || "Day " + item.day,
      };

      if (item.reward_type === "points") {
        d.points = item.points;
        d.points_type = item.points_type;
      } else if (item.reward_type === "achievement") {
        d.achievement_mode = item.achievement_mode;
        d.achievement_id = item.achievement_id || 0;
      } else if (item.reward_type === "rank") {
        d.rank_mode = item.rank_mode;
        d.rank_id = item.rank_id || 0;
      }

      return d;
    });

    $.post(
      ajaxurl,
      {
        action: "gamipress_daily_login_rewards_generate_calendar",
        nonce: gamipress_calendar_generator.nonce,
        calendar: calendarPayload,
      },
      function (response) {
        $btn.prop("disabled", false);
        $spinner.removeClass("is-active");

        if (response.success && response.data.redirect) {
          window.location.href = response.data.redirect;
        } else {
          var msg =
            response.data && response.data.message
              ? response.data.message
              : gamipress_calendar_generator.error_text;
          alert(msg);
        }
      },
    ).fail(function () {
      $btn.prop("disabled", false);
      $spinner.removeClass("is-active");
      alert(gamipress_calendar_generator.error_text);
    });
  }

  // --- Utilities ---

  function randInt(min, max) {
    if (min >= max) return min;
    return Math.floor(Math.random() * (max - min + 1)) + min;
  }

  function shuffleArray(arr) {
    var a = arr.slice();
    for (var i = a.length - 1; i > 0; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var tmp = a[i];
      a[i] = a[j];
      a[j] = tmp;
    }
    return a;
  }

  $(document).ready(function () {
    init();
  });
})(jQuery);
