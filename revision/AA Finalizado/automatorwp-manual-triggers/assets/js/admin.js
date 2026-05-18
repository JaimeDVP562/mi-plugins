/**
 * AutomatorWP Manual Triggers - Admin JavaScript
 *
 * Injects three UI sections into manual trigger panels:
 *  1. Run Now    — always visible, executes trigger via AJAX
 *  2. Code       — collapsible, PHP snippet to fire trigger from code
 *  3. Shortcode  — collapsible, ready-to-paste shortcode
 *
 * Anonymous triggers: no user ID field anywhere.
 * Logged-in triggers: native dialog for user ID input.
 *
 * @package AutomatorWP\Manual_Triggers
 * @since   1.0.0
 */
(function ($) {
  var LOGGED_IN_TYPE = "manual_triggers_manual_launch";
  var ANONYMOUS_TYPE = "manual_triggers_anonymous_manual_launch";
  var i18n = automatorwp_manual_triggers.i18n;

  // -------------------------------------------------------------------------
  // Section builders
  // -------------------------------------------------------------------------

  function buildRunNowSection(triggerId, isLoggedIn) {
    var dialogId = "automatorwp-run-dialog-" + triggerId;
    var html =
      '<div class="automatorwp-manual-trigger-section automatorwp-manual-trigger-run-now">';
    html += "<h4>" + i18n.run_now + "</h4>";

    if (isLoggedIn) {
      html +=
        '<dialog id="' +
        dialogId +
        '" style="padding:20px;border-radius:6px;border:1px solid #ccc;min-width:300px;">';
      html +=
        '<p style="margin:0 0 10px;font-weight:600;">' + i18n.user_id + "</p>";
      html +=
        '<input id="' +
        dialogId +
        '-input" type="text"' +
        ' placeholder="' +
        i18n.user_id_placeholder +
        '"' +
        ' style="width:100%;padding:6px 8px;border:1px solid #8c8f94;border-radius:3px;font-size:13px;box-sizing:border-box;" />';
      html += '<div style="margin-top:12px;text-align:right;">';
      html +=
        '<button class="button automatorwp-run-dialog-cancel" style="margin-right:8px;">Cancel</button>';
      html +=
        '<button class="button button-primary automatorwp-run-dialog-confirm"' +
        ' data-trigger-id="' +
        triggerId +
        '">Run Now</button>';
      html += "</div>";
      html += "</dialog>";
    }

    html +=
      '<button type="button" class="button button-primary automatorwp-manual-trigger-run-btn"' +
      ' data-trigger-id="' +
      triggerId +
      '"' +
      ' data-logged-in="' +
      (isLoggedIn ? "1" : "0") +
      '"' +
      (isLoggedIn ? ' data-dialog-id="' + dialogId + '"' : "") +
      ">";
    html += i18n.run_now;
    html += "</button>";
    html += '<span class="automatorwp-manual-trigger-run-status"></span>';
    html += "</div>";

    return html;
  }

  function buildCodeSection(triggerId, isLoggedIn) {
    var html =
      '<div class="automatorwp-manual-trigger-section automatorwp-manual-trigger-code">';
    html += buildToggleHeader(i18n.code_example);
    html +=
      '<div class="automatorwp-manual-trigger-toggle-content" style="display:none;">';

    if (isLoggedIn) {
      html += "<p>" + i18n.code_example_desc_logged_in + "</p>";

      html +=
        "<label>" +
        i18n.code_basic_usage +
        " <em>(" +
        i18n.code_current_user +
        ")</em></label>";
      html += '<pre class="automatorwp-manual-trigger-pre"><code>';
      html += "function automatorwp_run_trigger_" + triggerId + "() {\n";
      html += "    automatorwp_run_trigger( " + triggerId + " );\n";
      html += "}";
      html += "</code></pre>";

      html += "<label>" + i18n.code_specific_user + "</label>";
      html += '<pre class="automatorwp-manual-trigger-pre"><code>';
      html += "function automatorwp_run_trigger_" + triggerId + "() {\n";
      html += "    $user_id = 123; // " + i18n.code_user_123 + "\n";
      html += "    automatorwp_run_trigger( " + triggerId + ", $user_id );\n";
      html += "}";
      html += "</code></pre>";
    } else {
      html += "<p>" + i18n.code_example_desc_anonymous + "</p>";
      html += '<pre class="automatorwp-manual-trigger-pre"><code>';
      html += "function automatorwp_run_trigger_" + triggerId + "() {\n";
      html += "    automatorwp_run_trigger( " + triggerId + " );\n";
      html += "}";
      html += "</code></pre>";
    }

    html += "</div></div>";
    return html;
  }

  function buildShortcodeSection(triggerId, isLoggedIn) {
    var html =
      '<div class="automatorwp-manual-trigger-section automatorwp-manual-trigger-shortcode">';
    html += buildToggleHeader(i18n.shortcode);
    html +=
      '<div class="automatorwp-manual-trigger-toggle-content" style="display:none;">';

    if (isLoggedIn) {
      html += "<p>" + i18n.shortcode_desc_logged_in + "</p>";

      html += "<label>" + i18n.shortcode_current_user + "</label>";
      html += '<pre class="automatorwp-manual-trigger-pre"><code>';
      html +=
        '[automatorwp_manual_trigger trigger="' +
        triggerId +
        '" user="" label="' +
        i18n.run_label +
        '"]';
      html += "</code></pre>";

      html += "<label>" + i18n.shortcode_specific_user + "</label>";
      html += '<pre class="automatorwp-manual-trigger-pre"><code>';
      html +=
        '[automatorwp_manual_trigger trigger="' +
        triggerId +
        '" user="123" label="' +
        i18n.run_label +
        '"]';
      html += "</code></pre>";
    } else {
      html += "<p>" + i18n.shortcode_desc_anonymous + "</p>";
      html += '<pre class="automatorwp-manual-trigger-pre"><code>';
      html +=
        '[automatorwp_manual_trigger trigger="' +
        triggerId +
        '" label="' +
        i18n.run_label +
        '"]';
      html += "</code></pre>";
    }

    html += "</div></div>";
    return html;
  }

  function buildToggleHeader(label) {
    return (
      '<h4 class="automatorwp-manual-trigger-toggle">' +
      '<span class="dashicons dashicons-arrow-right-alt2"></span> ' +
      label +
      "</h4>"
    );
  }

  // -------------------------------------------------------------------------
  // AJAX execution
  // -------------------------------------------------------------------------

  function runTrigger(triggerId, userId, $status, $btn) {
    $btn.prop("disabled", true).text(i18n.running);
    $status.text("").css("color", "");

    $.ajax({
      url: automatorwp_manual_triggers.ajax_url,
      type: "POST",
      data: {
        action: "automatorwp_manual_trigger_admin_run",
        trigger_id: triggerId,
        user_id: userId,
        nonce: automatorwp_manual_triggers.nonce,
      },
      success: function (response) {
        if (response.success) {
          $status.text(i18n.done).css("color", "#46b450");
        } else {
          $status
            .text((response.data && response.data.message) || i18n.error)
            .css("color", "#dc3232");
        }
      },
      error: function () {
        $status.text(i18n.error).css("color", "#dc3232");
      },
      complete: function () {
        $btn.prop("disabled", false).text(i18n.run_now);
        setTimeout(function () {
          $status.text("");
        }, 3000);
      },
    });
  }

  // -------------------------------------------------------------------------
  // Injection
  // -------------------------------------------------------------------------

  function injectSections($item) {
    if ($item.next(".automatorwp-manual-trigger-sections").length) {
      return;
    }

    var triggerType = getTriggerType($item);
    if (triggerType !== LOGGED_IN_TYPE && triggerType !== ANONYMOUS_TYPE) {
      return;
    }

    var isLoggedIn = triggerType === LOGGED_IN_TYPE;
    var triggerId = getTriggerId($item);

    var html =
      '<div class="automatorwp-manual-trigger-sections">' +
      buildRunNowSection(triggerId, isLoggedIn) +
      buildCodeSection(triggerId, isLoggedIn) +
      buildShortcodeSection(triggerId, isLoggedIn) +
      "</div>";

    $item.after(html);
  }

  function getTriggerType($item) {
    var type =
      $item.find("input.type").val() ||
      $item.find(".automatorwp-trigger-type").val() ||
      $item.data("type") ||
      $item.attr("data-type") ||
      $item.find('input[name*="[type]"]').val() ||
      "";

    if (type) {
      return type;
    }

    var classes = $item.attr("class") || "";
    var match = classes.match(/automatorwp-trigger-([\w-]+)/);
    if (match && match[1]) {
      return match[1].replace(/-/g, "_");
    }

    return "";
  }

  function getTriggerId($item) {
    var id =
      $item.find("input.id").val() ||
      $item.find(".automatorwp-trigger-id").val() ||
      $item.data("id") ||
      $item.attr("data-id") ||
      $item.find('input[name*="[id]"]').val();
    return id && id !== "0" ? id : "{ID}";
  }

  function scanAndInject() {
    var loggedInClass = LOGGED_IN_TYPE.replace(/_/g, "-");
    var anonymousClass = ANONYMOUS_TYPE.replace(/_/g, "-");

    $(
      ".automatorwp-automation-item.automatorwp-trigger-" +
        loggedInClass +
        "," +
        ".automatorwp-automation-item.automatorwp-trigger-" +
        anonymousClass,
    ).each(function () {
      injectSections($(this));
    });

    $(
      '.automatorwp-automation-item[data-type="' +
        LOGGED_IN_TYPE +
        '"],' +
        '.automatorwp-automation-item[data-type="' +
        ANONYMOUS_TYPE +
        '"]',
    ).each(function () {
      injectSections($(this));
    });

    $("input.type").each(function () {
      var val = $(this).val();
      if (val === LOGGED_IN_TYPE || val === ANONYMOUS_TYPE) {
        var $item = $(this).closest(".automatorwp-automation-item");
        if ($item.length) {
          injectSections($item);
        }
      }
    });
  }

  // -------------------------------------------------------------------------
  // Event handlers
  // -------------------------------------------------------------------------

  $(document).on("click", ".automatorwp-manual-trigger-toggle", function (e) {
    e.preventDefault();
    var $content = $(this).next(".automatorwp-manual-trigger-toggle-content");
    var $icon = $(this).find(".dashicons");
    $content.slideToggle(200);
    $icon.toggleClass("dashicons-arrow-right-alt2 dashicons-arrow-down-alt2");
  });

  // Run Now button — open dialog for logged-in, run directly for anonymous
  $(document).on("click", ".automatorwp-manual-trigger-run-btn", function (e) {
    e.preventDefault();
    e.stopPropagation();

    var $btn = $(this);
    var triggerId = $btn.data("trigger-id");
    var isLoggedIn =
      $btn.data("logged-in") === 1 || $btn.data("logged-in") === "1";
    var $status = $btn.siblings(".automatorwp-manual-trigger-run-status");

    if (isLoggedIn) {
      // Open native dialog
      var dialogId = $btn.data("dialog-id");
      var dialog = document.getElementById(dialogId);
      if (dialog) {
        // Clear previous value
        document.getElementById(dialogId + "-input").value = "";
        dialog.showModal();
        // Store reference to button and status for confirm handler
        dialog._triggerBtn = $btn;
        dialog._triggerStatus = $status;
        dialog._triggerId = triggerId;
      }
    } else {
      runTrigger(triggerId, 0, $status, $btn);
    }
  });

  // Dialog confirm
  $(document).on("click", ".automatorwp-run-dialog-confirm", function (e) {
    e.preventDefault();
    e.stopPropagation();

    var triggerId = $(this).data("trigger-id");
    var dialogId = "automatorwp-run-dialog-" + triggerId;
    var dialog = document.getElementById(dialogId);

    if (!dialog) return;

    var rawVal = document.getElementById(dialogId + "-input").value.trim();
    var userId = rawVal ? parseInt(rawVal, 10) : 0;

    dialog.close();
    runTrigger(triggerId, userId, dialog._triggerStatus, dialog._triggerBtn);
  });

  // Dialog cancel
  $(document).on("click", ".automatorwp-run-dialog-cancel", function (e) {
    e.preventDefault();
    e.stopPropagation();
    var $dialog = $(this).closest("dialog");
    if ($dialog.length) {
      $dialog[0].close();
    }
  });

  // -------------------------------------------------------------------------
  // Initialisation
  // -------------------------------------------------------------------------

  $(document).ready(function () {
    setTimeout(scanAndInject, 3000);

    var $root = $(
      "#automatorwp-automation-editor, .automatorwp-automation-items, #poststuff",
    );
    if ($root.length) {
      new MutationObserver(function (mutations) {
        var hasNewNodes = mutations.some(function (m) {
          return m.addedNodes.length > 0;
        });
        if (hasNewNodes) {
          setTimeout(scanAndInject, 300);
        }
      }).observe($root[0], { childList: true, subtree: true });
    }
  });

  $(document).on(
    "automatorwp_trigger_added automatorwp_trigger_updated automatorwp_automation_item_added",
    function () {
      setTimeout(scanAndInject, 300);
    },
  );
})(jQuery);
