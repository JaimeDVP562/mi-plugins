(function ($) {
  // =========================================================================
  // Código original del plugin (sin cambios)
  // =========================================================================

  $("body").on("change", ".cmb2-id-delay-action input", function () {
    var form = $(this).closest(".cmb2-metabox");
    var target = form.find(
      ".cmb2-id-delay-action-amount, .cmb2-id-delay-action-period",
    );
    if ($(this).prop("checked")) {
      target.slideDown("fast");
      if (form.find(".cmb2-id-schedule-action input").prop("checked")) {
        form
          .find(".cmb2-id-schedule-action input")
          .prop("checked", false)
          .trigger("change");
      }
    } else {
      target.slideUp("fast");
    }
  });

  $("body").on("change", ".cmb2-id-schedule-action input", function () {
    var form = $(this).closest(".cmb2-metabox");
    var target = form.find(".cmb2-id-schedule-action-datetime");
    if ($(this).prop("checked")) {
      target.slideDown("fast");
      if (form.find(".cmb2-id-delay-action input").prop("checked")) {
        form
          .find(".cmb2-id-delay-action input")
          .prop("checked", false)
          .trigger("change");
      }
    } else {
      target.slideUp("fast");
    }
  });

  $(".cmb2-id-schedule-action input").trigger("change");
  $(".cmb2-id-delay-action input").trigger("change");

  $("body").on(
    "click",
    '.automatorwp-option[data-option="schedule_action"]',
    function () {
      var item = $(this).closest(".automatorwp-automation-item");
      var form = item.find(
        '.automatorwp-option-form-container[data-option="schedule_action"]',
      );
      var delay = form.find(".cmb2-id-delay-action input");
      var delay_target = form.find(
        ".cmb2-id-delay-action-amount, .cmb2-id-delay-action-period",
      );
      var schedule = form.find(".cmb2-id-schedule-action input");
      var schedule_target = form.find(".cmb2-id-schedule-action-datetime");

      if (!delay.prop("checked") && !schedule.prop("checked")) {
        delay.prop("checked", true);
      }

      delay.prop("checked") ? delay_target.show() : delay_target.hide();
      schedule.prop("checked")
        ? schedule_target.show()
        : schedule_target.hide();
    },
  );

  // =========================================================================
  // Textos del modal — con fallback por si automatorwpSA no está definido
  // =========================================================================

  function saText(key) {
    var defaults = {
      modalTitle: "Cambiar fecha programada",
      utcNotice: "Introduce la nueva fecha en UTC.",
      save: "Guardar nueva fecha",
      cancel: "Cancelar",
      saving: "Guardando…",
      emptyDate: "Selecciona una fecha y hora.",
      pastDate: "La fecha debe ser en el futuro.",
      ajaxError: "Error de conexión. Inténtalo de nuevo.",
    };
    if (
      typeof automatorwpSA !== "undefined" &&
      automatorwpSA.i18n &&
      automatorwpSA.i18n[key]
    ) {
      return automatorwpSA.i18n[key];
    }
    return defaults[key] || key;
  }

  // =========================================================================
  // Modal de cambio de fecha (listado de logs)
  // =========================================================================

  function buildModal(logId, currentDate, nonce) {
    // Contenedor oscuro de fondo
    var overlay = $("<div>", { id: "automatorwp-sa-modal" });
    overlay.css({
      position: "fixed",
      top: "0",
      left: "0",
      width: "100%",
      height: "100%",
      background: "rgba(0,0,0,0.6)",
      zIndex: "999999",
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
    });

    // Caja del modal
    var box = $("<div>");
    box.css({
      background: "#ffffff",
      borderRadius: "4px",
      padding: "24px",
      minWidth: "340px",
      maxWidth: "90vw",
      boxShadow: "0 6px 30px rgba(0,0,0,0.35)",
    });

    // Título
    var title = $("<h3>");
    title.css({ margin: "0 0 8px 0", fontSize: "15px", fontWeight: "600" });
    title.text(saText("modalTitle"));

    // Aviso UTC
    var notice = $("<p>");
    notice.css({ margin: "0 0 14px 0", color: "#777", fontSize: "12px" });
    notice.text(saText("utcNotice"));

    // Input fecha
    var input = $("<input>");
    input.attr("type", "datetime-local");
    input.val(currentDate);
    input.css({
      width: "100%",
      padding: "6px 8px",
      fontSize: "14px",
      boxSizing: "border-box",
    });

    // Mensaje de error
    var errorMsg = $("<p>");
    errorMsg.css({
      color: "#dc3232",
      fontSize: "12px",
      margin: "8px 0 0 0",
      display: "none",
    });

    // Fila de botones
    var btnRow = $("<div>");
    btnRow.css({
      display: "flex",
      gap: "8px",
      justifyContent: "flex-end",
      marginTop: "18px",
    });

    var cancelBtn = $("<button>", { type: "button" });
    cancelBtn.addClass("button");
    cancelBtn.text(saText("cancel"));

    var saveBtn = $("<button>", { type: "button" });
    saveBtn.addClass("button button-primary");
    saveBtn.text(saText("save"));

    // Montar el DOM
    btnRow.append(cancelBtn);
    btnRow.append(saveBtn);
    box.append(title);
    box.append(notice);
    box.append(input);
    box.append(errorMsg);
    box.append(btnRow);
    overlay.append(box);

    // Cerrar al hacer clic en el fondo
    overlay.on("click", function (e) {
      if ($(e.target).is(overlay)) {
        overlay.remove();
      }
    });

    // Cerrar con botón cancelar
    cancelBtn.on("click", function () {
      overlay.remove();
    });

    // Guardar nueva fecha
    saveBtn.on("click", function () {
      var newDate = input.val();

      if (!newDate) {
        errorMsg.text(saText("emptyDate")).show();
        return;
      }

      if (new Date(newDate).getTime() <= Date.now()) {
        errorMsg.text(saText("pastDate")).show();
        return;
      }

      errorMsg.hide();
      saveBtn.prop("disabled", true).text(saText("saving"));

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "automatorwp_sa_change_date",
          nonce: nonce,
          log_id: logId,
          new_date: newDate,
        },
        success: function (response) {
          if (response && response.success) {
            overlay.remove();
            window.location.reload();
          } else {
            var msg =
              response && response.data && response.data.message
                ? response.data.message
                : saText("ajaxError");
            errorMsg.text(msg).show();
            saveBtn.prop("disabled", false).text(saText("save"));
          }
        },
        error: function () {
          errorMsg.text(saText("ajaxError")).show();
          saveBtn.prop("disabled", false).text(saText("save"));
        },
      });
    });

    return overlay;
  }

  // Clic en "Cambiar fecha" del listado
  $("body").on("click", ".automatorwp-sa-change-date", function (e) {
    e.preventDefault();
    e.stopPropagation();

    var $btn = $(this);
    var logId = $btn.data("log-id");
    var nonce = $btn.data("nonce");
    var currentDate = $btn.data("current-date");

    // Eliminar modal anterior si existe
    $("#automatorwp-sa-modal").remove();

    // Construir y añadir el modal
    $("body").append(buildModal(logId, currentDate, nonce));
  });

  // =========================================================================
  // Guardar fecha desde la pantalla de edición del log
  // =========================================================================

  $("body").on("click", ".automatorwp-sa-save-date", function () {
    var btn = $(this);
    var logId = btn.data("log-id");
    var nonce = btn.data("nonce");
    var newDate = $("#automatorwp-sa-new-date").val();
    var feedback = $(".automatorwp-sa-feedback");

    if (!newDate) {
      feedback.css("color", "#dc3232").text(saText("emptyDate")).show();
      return;
    }

    if (new Date(newDate).getTime() <= Date.now()) {
      feedback.css("color", "#dc3232").text(saText("pastDate")).show();
      return;
    }

    btn.prop("disabled", true).text(saText("saving"));
    feedback.hide();

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "automatorwp_sa_change_date",
        nonce: nonce,
        log_id: logId,
        new_date: newDate,
      },
      success: function (response) {
        if (response && response.success) {
          feedback
            .css("color", "#46b450")
            .text("✓ " + response.data.message)
            .show();
          btn.prop("disabled", false).text(saText("save"));
        } else {
          var msg =
            response && response.data && response.data.message
              ? response.data.message
              : saText("ajaxError");
          feedback.css("color", "#dc3232").text(msg).show();
          btn.prop("disabled", false).text(saText("save"));
        }
      },
      error: function () {
        feedback.css("color", "#dc3232").text(saText("ajaxError")).show();
        btn.prop("disabled", false).text(saText("save"));
      },
    });
  });
})(jQuery);
