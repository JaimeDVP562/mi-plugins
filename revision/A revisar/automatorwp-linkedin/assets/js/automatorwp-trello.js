(function( $ ) {
    var _prefix = 'automatorwp_trello_';

    /**
     * Función genérica para refrescar selectores dependientes (Board -> List -> Card)
     */
    function updateDependentSelector($currentSelect, targetRowClass, isFirstChange) {
        var $form = $currentSelect.closest('.automatorwp-option-form-container');
        var $targetRow = $form.find(targetRowClass);
        var parentId = $currentSelect.val();


        if (!parentId || parentId === 'any' || parentId === '') {
            isFirstChange ? $targetRow.hide() : $targetRow.slideUp('fast');
            return;
        }

        var $selector = $targetRow.find('select.select2-hidden-accessible');

        // Reiniciar Select2 y preparar para AJAX
        $selector.next('.select2').remove();
        $selector.data('table', parentId); // Usamos el ID del padre como filtro

        if (!isFirstChange) {
            $selector.val(''); // Resetear valor si es un cambio manual
        }

        $selector.removeAttr('data-select2-id');


        if (typeof automatorwp_ajax_selector === 'function') {
            automatorwp_ajax_selector($selector);
        }


        isFirstChange ? $targetRow.show() : $targetRow.slideDown('fast');
    }

    // --- EVENTOS DE INTERFAZ ---

    // 1. Cuando cambia el TABLERO (Actualiza Listas, Etiquetas, Miembros, etc.)
    $('body').on('change', '[class*="trello"] .cmb2-id-board select', function(e, first_change) {
        var $this = $(this);
        updateDependentSelector($this, '.cmb2-id-list', first_change);
        updateDependentSelector($this, '.cmb2-id-new-list', first_change);
        updateDependentSelector($this, '.cmb2-id-label', first_change);
        updateDependentSelector($this, '.cmb2-id-member', first_change);
    });

    // 2. Cuando cambia la LISTA (Actualiza Tarjetas)
    $('body').on('change', '[class*="trello"] .cmb2-id-list select', function(e, first_change) {
        updateDependentSelector($(this), '.cmb2-id-card', first_change);
    });

    // 3. Cuando cambia la TARJETA (Actualiza Checklists)
    $('body').on('change', '[class*="trello"] .cmb2-id-card select', function(e, first_change) {
        updateDependentSelector($(this), '.cmb2-id-checklist', first_change);
    });

    // --- LÓGICA DE AUTORIZACIÓN ---
    $('body').on('click', '#' + _prefix + 'authorize', function(e) {
        e.preventDefault();
        var $btn = $(this), $wrap = $btn.parent();
        var key = $('#' + _prefix + 'consumer_key').val();
        var token = $('#' + _prefix + 'access_token').val();

        var $resp = $('#' + _prefix + 'response');
        if (!$resp.length) {
            $wrap.append('<div id="'+_prefix+'response" style="display:none; margin-top:10px;"></div>');
            $resp = $('#' + _prefix + 'response');
        }

        if (!key || !token) {
            $resp.attr('class', 'automatorwp-notice-error').html('All fields are required').slideDown();
            return;
        }

        $resp.slideUp('fast');
        $btn.prop('disabled', true);
        $wrap.append('<span class="spinner is-active"></span>');

        $.post(ajaxurl, {
            action: _prefix + 'authorize',
            nonce: automatorwp_trello.nonce,
            consumer_key: key,
            access_token: token
        }, function(response) {
            $wrap.find('.spinner').remove();

            var isSuccess = response.success === true;
            $resp.attr('class', 'automatorwp-notice-' + (isSuccess ? 'success' : 'error'));
            $resp.html(response.data.message || response.data).slideDown();

            if (isSuccess && response.data.redirect_url) {
                window.location = response.data.redirect_url;
            } else {
                $btn.prop('disabled', false);
            }
        }, 'json'); // <--- CRUCIAL: Forzar respuesta JSON
    });

    // --- CARGA INICIAL ---
    $('body').on('click', '.automatorwp-automation-item-label > .automatorwp-option', function() {
        var $item = $(this).closest('.automatorwp-automation-item[class*="trello"]');
        var option = $(this).data('option');
        var $form = $item.find('.automatorwp-option-form-container[data-option="' + option + '"]');

        // Disparamos el cambio inicial para que se carguen las jerarquías
        $form.find('.cmb2-id-board select, .cmb2-id-list select, .cmb2-id-card select').trigger('change', [true]);
    });

})( jQuery );