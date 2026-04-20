(function( $ ) {

    var prefix = 'automatorwp-sendpulse-';
    var _prefix = 'automatorwp_sendpulse_';

    function _getAjaxUrl() {
        if ( typeof automatorwp_sendpulse !== 'undefined' && automatorwp_sendpulse.ajax_url ) return automatorwp_sendpulse.ajax_url;
        if ( typeof ajaxurl !== 'undefined' ) return ajaxurl;
        return '/wp-admin/admin-ajax.php';
    }

    // Debug: indicate script loaded
    try { console.info('[automatorwp-sendpulse] script loaded', typeof automatorwp_sendpulse !== 'undefined' ? automatorwp_sendpulse : null); } catch(e){}

    // On click authorize button
    $('body').on('click', '.automatorwp_settings #' + _prefix + 'authorize', function(e) {
        e.preventDefault();

        var button = $(this);
        var wrapper = button.parent();

        var application_id = $('#' + _prefix + 'application_id').val();
        var application_secret = $('#' + _prefix + 'application_secret').val();

        // Check if response div exists
        var response_wrap = wrapper.find('#' + _prefix + 'response');

        if( ! response_wrap.length ) {
            wrapper.append( '<div id="' + _prefix + 'response" style="display: none; margin-top: 10px;"></div>' );
            response_wrap = wrapper.find('#' + _prefix + 'response');
        }

        // Show error message if not correctly configured
        if( application_id.length === 0 || application_secret.length === 0 ) {
            response_wrap.addClass( 'automatorwp-notice-error' );
            response_wrap.html( 'All fields are required to connect to SendPulse' );
            response_wrap.slideDown('fast');
            return;
        }

        response_wrap.slideUp('fast');
        response_wrap.attr('class', '');

        // Show spinner
        wrapper.append('<span class="spinner is-active" style="float: none;"></span>');

        // Disable button
        button.prop('disabled', true);

        // Server-side Client Credentials flow: post credentials to the authorize AJAX handler
        $.post(
            _getAjaxUrl(),
            {
                action: 'automatorwp_sendpulse_authorize',
                nonce: automatorwp_sendpulse.nonce,
                application_id: application_id,
                application_secret: application_secret,
            },
            function( response ) {

                // Add class automatorwp-notice-success on successful connect, otherwise error
                response_wrap.addClass( 'automatorwp-notice-' + ( response.success === true ? 'success' : 'error' ) );
                response_wrap.html( ( response.data && response.data.message !== undefined ? response.data.message : ( response.data ? response.data : '' ) ) );
                response_wrap.slideDown('fast');

                // Hide spinner
                wrapper.find('.spinner').remove();

                if ( response.success === true ) {
                    // Optionally reload to reflect connected state
                    setTimeout( function(){ location.reload(); }, 800 );
                    return;
                }

                // Enable button
                button.prop('disabled', false);

            }
        );
 
    });

    /**
     * Initialize addressbook selector inside an action option form.
     * Replaces the addressbook text input with a select, loads addressbooks via AJAX
     * and provides a button to load emails from the selected addressbook.
     */
    function initAddressbookSelector(option_form, opts) {
        opts = opts || {};
        var autoSelectFirst = !!opts.autoSelectFirst;
        var autoLoad = !!opts.autoLoad;

        if (!option_form || !option_form.length) return;

        console.info('[automatorwp-sendpulse] initAddressbookSelector called', { autoSelectFirst: autoSelectFirst, autoLoad: autoLoad });

        // Try to find the CMB2 row by class first
        var row = option_form.find('.cmb2-id-addressbook_id');
        var input = null;

        if ( row.length ) {
            input = row.find('input[type="text"]').first();
        }

        // Also try data-option attribute or name/id based selectors inside the option form
        if ( !input || !input.length ) {
            input = option_form.find('input[data-option="addressbook_id"], select[data-option="addressbook_id"], input[name*="addressbook_id"], input[id*="addressbook_id"], select[name*="addressbook_id"], select[id*="addressbook_id"]').first();
            if ( input && input.length && !row.length ) {
                row = input.closest('.cmb-row, .cmb2-row, .cmb2-id, .form-field, .control-group');
                if ( ! row.length ) row = input.parent();
            }
        }

        // As a last resort, search the whole automation item container (some markup is rendered outside the option_form)
        if ( (!row || !row.length) && option_form && option_form.length ) {
            var item = option_form.closest('.automatorwp-automation-item');
            if ( item && item.length ) {
                input = item.find('input[data-option="addressbook_id"], select[data-option="addressbook_id"], input[name*="addressbook_id"], input[id*="addressbook_id"], select[name*="addressbook_id"], select[id*="addressbook_id"]').first();
                if ( input && input.length ) {
                    row = input.closest('.cmb-row, .cmb2-row, .cmb2-id, .form-field, .control-group');
                    if ( ! row.length ) row = input.parent();
                }
            }
        }

        if ( ! row || ! row.length ) {
            console.info('[automatorwp-sendpulse] addressbook row not found in option_form or item');
            return;
        }

        // avoid initializing twice
        if (row.find('select.automatorwp-sendpulse-addressbook-select').length) return;

        var existingVal = input && input.length ? input.val() : '';

        // hide original input to preserve data backing
        if (input && input.length) input.hide();

        var select = $('<select class="automatorwp-sendpulse-addressbook-select" style="width:100%;"></select>');
        select.append($('<option>').attr('value', '').text('-- Select addressbook --'));
        row.append(select);

        var loader = $('<span class="automatorwp-sendpulse-loading" style="margin-left:8px;display:none">Loading…</span>');
        row.append(loader);

        var loadBtn = $('<button type="button" class="button automatorwp-sendpulse-load-emails" style="margin-left:8px;display:none">Load emails</button>');
        row.append(loadBtn);

        loader.show();
        $.post( _getAjaxUrl(), { action: 'automatorwp_sendpulse_list_addressbooks', nonce: automatorwp_sendpulse.nonce }, function(resp){
            loader.hide();
            if (!resp || resp.success !== true) {
                console.warn('Failed to load addressbooks', resp);
                // Show inline error next to selector
                var msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Failed to load addressbooks';
                var err = row.find('.automatorwp-sendpulse-error');
                if (!err.length) {
                    err = $('<div class="automatorwp-sendpulse-error" style="color:#a00;margin-top:6px;"></div>');
                    row.append(err);
                }
                err.text(msg);

                // Add retry button
                var retry = row.find('.automatorwp-sendpulse-retry');
                if (!retry.length) {
                    retry = $('<button type="button" class="button automatorwp-sendpulse-retry" style="margin-left:8px;">Retry</button>');
                    row.append(retry);
                    retry.on('click', function(){
                        err.remove();
                        retry.remove();
                        initAddressbookSelector(option_form);
                    });
                }
                return;
            }
            var books = (resp.data && resp.data.addressbooks) ? resp.data.addressbooks : [];
            books.forEach(function(b){
                select.append( $('<option>').attr('value', b.id).text(b.name) );
            });
            if (existingVal) select.val(existingVal);
            // Auto-select first addressbook when requested
            if ( ! existingVal && autoSelectFirst && books.length ) {
                try { select.val( books[0].id ); } catch(e){}
            }
            if (select.val()) loadBtn.show();
            // Optionally auto-load subscribers
            if ( autoLoad && select.val() ) {
                try { loadBtn.trigger('click'); } catch(e){}
            }
        }).fail(function(){ loader.hide(); });

        select.on('change', function(){
            var v = $(this).val();
            if (input && input.length) input.val(v);
            if (v) loadBtn.show(); else loadBtn.hide();
        });

        loadBtn.on('click', function(){
            var ab = select.val();
            var btn = $(this);
            if (!ab) return;
            btn.prop('disabled', true).text('Loading...');
            $.post( _getAjaxUrl(), { action: 'automatorwp_sendpulse_list_subscribers', nonce: automatorwp_sendpulse.nonce, addressbook_id: ab, page: 1, per_page: 100 }, function(resp){
                btn.prop('disabled', false).text('Load emails');
                if (!resp || resp.success !== true) {
                    alert('Failed to load subscribers: ' + (resp && resp.data && resp.data.message ? resp.data.message : 'unknown'));
                    return;
                }
                var data = (resp.data && resp.data.data) ? resp.data.data : (resp.data ? resp.data : []);
                var emailWrapper = option_form.find('.automatorwp-sendpulse-email-wrapper');
                if (!emailWrapper.length) {
                    var emailInput = option_form.find('input[type="email"]').first();
                    if (emailInput && emailInput.length) {
                        emailWrapper = $('<div class="automatorwp-sendpulse-email-wrapper" style="margin-top:6px;"></div>');
                        emailInput.after(emailWrapper);
                    } else {
                        emailWrapper = $('<div class="automatorwp-sendpulse-email-wrapper" style="margin-top:6px;"></div>');
                        row.after(emailWrapper);
                    }
                } else {
                    emailWrapper.empty();
                }
                var sel = $('<select class="automatorwp-sendpulse-email-select" style="width:100%;"></select>');
                sel.append($('<option>').attr('value','').text('-- Select email --'));
                data.forEach(function(e){
                    var em = e.email || e.email_address || '';
                    if (!em) return;
                    sel.append($('<option>').attr('value', em).text(em + ' (' + (e.status_explain || e.status || '') + ')'));
                });
                emailWrapper.append(sel);
                sel.on('change', function(){
                    var v = $(this).val();
                    var emailInput = option_form.find('input[type="email"]').first();
                    if (emailInput && emailInput.length) {
                        emailInput.val(v);
                    } else {
                        var hidden = option_form.find('input[name="email"]');
                        if (!hidden.length) {
                            hidden = $('<input type="hidden" name="email">');
                            emailWrapper.after(hidden);
                        }
                        hidden.val(v);
                    }
                });
            }).fail(function(){ btn.prop('disabled', false).text('Load emails'); alert('Request failed'); });
        });

    }

    // On change board
    $('body').on('change', '.automatorwp-action-sendpulse-create-card .cmb2-id-board select, '
    + '.automatorwp-action-sendpulse-change-card-list .cmb2-id-board select, '
    + '.automatorwp-action-sendpulse-delete-card .cmb2-id-board select, '
    + '.automatorwp-action-sendpulse-change-desc .cmb2-id-board select, '
    + '.automatorwp-action-sendpulse-comment-card .cmb2-id-board select, '
    + '.automatorwp-action-sendpulse-add-label .cmb2-id-board select, '
    + '.automatorwp-action-sendpulse-add-member .cmb2-id-board select, '
    + '.automatorwp-action-sendpulse-add-checklist-item .cmb2-id-board select', function(e, first_change) {
        var board = $(this).closest('.cmb-row'); 
        var list_board = board.next('.cmb2-id-list');
        var board_id = $(this).val();

        if( board_id === 'any' || board_id === '' || board_id === null ) {
            if( first_change ) {
                list_board.hide();
            } else {
                list_board.slideUp();
            }
        }else{
            var list_selector = list_board.find('select.select2-hidden-accessible');

            // Remove Select2 element
            list_selector.next('.select2').remove();
            // Update the team (since we do not use the table attribute, lets to use it as team)
            list_selector.data( 'table', board_id );

            if( ! first_change ) {
                // Update the term value
                list_selector.val( '' );
            }

            // Reset the selector
            list_selector.removeAttr('data-select2-id');

            // Init it again
            automatorwp_ajax_selector( list_selector );

            // Show the term selector
            if( first_change ) {
                list_board.show();
            } else {
                list_board.slideDown('fast');
            }
        }

    })

    // On change board for new list
    $('body').on('change', '.automatorwp-action-sendpulse-change-card-list .cmb2-id-board select', function(e, first_change) {
        var board = $(this).closest('.cmb-row'); 
        var new_list_board = board.nextAll('.cmb2-id-new-list');
        var board_id = $(this).val();

        if( board_id === 'any' || board_id === '' || board_id === null ) {
            if( first_change ) {
                new_list_board.hide();
            } else {
                new_list_board.slideUp();
            }
        }else{
            var new_list_selector = new_list_board.find('select.select2-hidden-accessible');

            // Remove Select2 element
            new_list_selector.next('.select2').remove();
            // Update the team (since we do not use the table attribute, lets to use it as team)
            new_list_selector.data( 'table', board_id );

            if( ! first_change ) {
                // Update the term value
                new_list_selector.val( '' );
            }

            // Reset the selector
            new_list_selector.removeAttr('data-select2-id');

            // Init it again
            automatorwp_ajax_selector( new_list_selector );

            // Show the term selector
            if( first_change ) {
                new_list_board.show();
            } else {
                new_list_board.slideDown('fast');
            }
        }

    })

    // On change add new label to card
    $('body').on('change', '.automatorwp-action-sendpulse-add-label .cmb2-id-board select', function(e, first_change) {
        var board = $(this).closest('.cmb-row'); 
        var new_label = board.nextAll('.cmb2-id-label');
        var board_id = $(this).val();

        if( board_id === 'any' || board_id === '' || board_id === null ) {
            if( first_change ) {
                new_label.hide();
            } else {
                new_label.slideUp();
            }
        }else{
            var new_label_selector = new_label.find('select.select2-hidden-accessible');

            // Remove Select2 element
            new_label_selector.next('.select2').remove();
            // Update the team (since we do not use the table attribute, lets to use it as team)
            new_label_selector.data( 'table', board_id );

            if( ! first_change ) {
                // Update the term value
                new_label_selector.val( '' );
            }

            // Reset the selector
            new_label_selector.removeAttr('data-select2-id');

            // Init it again
            automatorwp_ajax_selector( new_label_selector );

            // Show the term selector
            if( first_change ) {
                new_label.show();
            } else {
                new_label.slideDown('fast');
            }
        }

    })

    // On change add new member to card
    $('body').on('change', '.automatorwp-action-sendpulse-add-member .cmb2-id-board select', function(e, first_change) {
        var board = $(this).closest('.cmb-row'); 
        var new_member = board.nextAll('.cmb2-id-member');
        var board_id = $(this).val();

        if( board_id === 'any' || board_id === '' || board_id === null ) {
            if( first_change ) {
                new_member.hide();
            } else {
                new_member.slideUp();
            }
        }else{
            var new_member_selector = new_member.find('select.select2-hidden-accessible');

            // Remove Select2 element
            new_member_selector.next('.select2').remove();
            // Update the team (since we do not use the table attribute, lets to use it as team)
            new_member_selector.data( 'table', board_id );

            if( ! first_change ) {
                // Update the term value
                new_member_selector.val( '' );
            }

            // Reset the selector
            new_member_selector.removeAttr('data-select2-id');

            // Init it again
            automatorwp_ajax_selector( new_member_selector );

            // Show the term selector
            if( first_change ) {
                new_member.show();
            } else {
                new_member.slideDown('fast');
            }
        }

    })

    // On change add new checklist item
    $('body').on('change', '.automatorwp-action-sendpulse-add-checklist-item .cmb2-id-card select', function(e, first_change) {
        var card = $(this).closest('.cmb-row'); 
        var new_checklist_item = card.nextAll('.cmb2-id-checklist');
        var card_id = $(this).val();
        console.log(new_checklist_item);
        if( card_id === 'any' || card_id === '' || card_id === null ) {
            if( first_change ) {
                new_checklist_item.hide();
            } else {
                new_checklist_item.slideUp();
            }
        }else{
            var new_checklist_item_selector = new_checklist_item.find('select.select2-hidden-accessible');

            // Remove Select2 element
            new_checklist_item_selector.next('.select2').remove();
            // Update the team (since we do not use the table attribute, lets to use it as team)
            new_checklist_item_selector.data( 'table', card_id );

            if( ! first_change ) {
                // Update the term value
                new_checklist_item_selector.val( '' );
            }

            // Reset the selector
            new_checklist_item_selector.removeAttr('data-select2-id');

            // Init it again
            automatorwp_ajax_selector( new_checklist_item_selector );

            // Show the term selector
            if( first_change ) {
                new_checklist_item.show();
            } else {
                new_checklist_item.slideDown('fast');
            }
        }
    })

    // On change list
    $('body').on('change', '.automatorwp-action-sendpulse-change-card-list .cmb2-id-list select, '
    + '.automatorwp-action-sendpulse-delete-card .cmb2-id-list select, '
    + '.automatorwp-action-sendpulse-change-desc .cmb2-id-list select, '
    + '.automatorwp-action-sendpulse-comment-card .cmb2-id-list select, '
    + '.automatorwp-action-sendpulse-add-label .cmb2-id-list select, '
    + '.automatorwp-action-sendpulse-add-member .cmb2-id-list select, '
    + '.automatorwp-action-sendpulse-add-checklist-item .cmb2-id-list select', function(e, first_change) {
        var list = $(this).closest('.cmb-row');
        
        var card_list = list.next('.cmb2-id-card');

        var list_id = $(this).val();

        if ( first_change === undefined ) {
            first_change = false;
        }

        if( list_id === 'any' || list_id === '' || list_id === null ) {
            // Hide the term selector
            if( first_change ) {
                card_list.hide();
            }else {
                card_list.slideUp('fast')
            }
        }else {
            var card_selector = card_list.find('select.select2-hidden-accessible');

            // Remove Select2 element
            card_selector.next('.select2').remove();

            // Update the space (since we do not use the table attribute, lets to use it as space)
            card_selector.data( 'table', list_id );

            if( ! first_change ) {
                // Update the the term value
                card_selector.val('');
            }

            // Reset the selector
            card_selector.removeAttr('data-select2-id');

            // Init it again
            automatorwp_ajax_selector( card_selector );

            // Show the term selector
            if( first_change ) {
                card_list.show();
            } else {
                card_list.slideDown('fast');
            }
        }
    });

    // On click on an option, check if form contains the board selector
    $('body').on('click', '.automatorwp-automation-item-label > .automatorwp-option', function(e) {

        var item = $(this).closest('.automatorwp-automation-item[class*="sendpulse"]');
        var option = $(this).data('option');
        var option_form = item.find('.automatorwp-option-form-container[data-option="' + option + '"]');
        
        var board_selector = option_form.find('.cmb2-id-board');

        if( board_selector !== undefined ) {
            board_selector.find('select.select2-hidden-accessible').trigger('change', [true]);
        }
    
        var list_selector = option_form.find('.cmb2-id-list');
    
        if( list_selector !== undefined ) {
            list_selector.find('select.select2-hidden-accessible').trigger('change', [true]);
        }
    
        var card_selector = option_form.find('.cmb2-id-card');
    
        if( card_selector !== undefined ) {
            card_selector.find('select.select2-hidden-accessible').trigger('change', [true]);
        }

        // Init addressbook selector and email loader (auto-select + auto-load)
        if ( typeof initAddressbookSelector === 'function' ) {
            try { initAddressbookSelector(option_form, { autoSelectFirst: true, autoLoad: true }); } catch(e) { console.warn('initAddressbookSelector error', e); }
        } else if ( typeof window !== 'undefined' && typeof window.automatorwp_sendpulse_initAddressbookSelector === 'function' ) {
            try { window.automatorwp_sendpulse_initAddressbookSelector(option_form, { autoSelectFirst: true, autoLoad: true }); } catch(e) { console.warn('automatorwp_sendpulse_initAddressbookSelector error', e); }
        }

        });

        // Expose initializer globally so external code (other IIFEs or the console)
        // can trigger the addressbook selector when needed. This keeps the
        // implementation encapsulated but provides a safe debug/integration hook.
        try {
            if ( typeof window !== 'undefined' ) {
                window.automatorwp_sendpulse_initAddressbookSelector = function(option_form, opts){
                    try {
                        if ( typeof initAddressbookSelector === 'function' ) {
                            return initAddressbookSelector(option_form, opts);
                        }
                    } catch(e) {
                        try { console.warn('[automatorwp-sendpulse] init wrapper error', e); } catch(ignore){}
                    }
                    return null;
                };
            }
        } catch(e){}

    })( jQuery );

// Initialize addressbook selectors on page load for existing action forms
(function($){
    function _initAll() {
        $('.automatorwp-automation-item').each(function(){
            var item = $(this);
            item.find('.automatorwp-option-form-container').each(function(){
                try {
                    if (typeof initAddressbookSelector === 'function') {
                        initAddressbookSelector($(this));
                    } else if ( typeof window !== 'undefined' && typeof window.automatorwp_sendpulse_initAddressbookSelector === 'function' ) {
                        window.automatorwp_sendpulse_initAddressbookSelector($(this));
                    }
                } catch(e){}
            });
        });
    }
    // Try to run init after DOM ready. If localization object isn't available yet,
    // poll for it a few times to avoid missing initialization due to ordering.
    function _runWhenReady() {
        var attempts = 0;
        var maxAttempts = 12;
        function tryInit() {
            attempts++;
            if ( typeof window.automatorwp_sendpulse !== 'undefined' || attempts >= maxAttempts ) {
                $(document).ready(function(){ _initAll(); });
                return;
            }
            setTimeout(tryInit, 150);
        }
        tryInit();
    }
    _runWhenReady();
})(jQuery);
// Observe the AutomatorWP items container for newly added actions and auto-open SendPulse items
(function($){
    $(document).ready(function(){
        var containers = document.querySelectorAll('.automatorwp-automation-items');
        if (!containers || !containers.length) return;

        // Mark items that exist at initialization so we only suppress auto-open for them.
        var _aws_autoopen_suppressed = true;
        try {
            var initialItems = document.querySelectorAll('.automatorwp-automation-item');
            Array.prototype.forEach.call(initialItems, function(it){ try{ it.setAttribute('data-aws-initial','1'); }catch(e){} });
        } catch(e){}
        setTimeout(function(){ _aws_autoopen_suppressed = false; }, 850);

        function tryOpenSendpulseItem(el) {
            try {
                // Initialize jQuery item and then check suppression for initial-load items.
                var $item = $(el);
                if (!$item || !$item.length) return;
                try {
                    var domEl = ($item[0]) ? $item[0] : null;
                    if (typeof _aws_autoopen_suppressed !== 'undefined' && _aws_autoopen_suppressed) {
                        if (domEl && domEl.getAttribute && domEl.getAttribute('data-aws-initial') === '1') return;
                    }
                } catch(e){}
                if ($item.data('automatorwp-sendpulse-opened')) return;
                if ($item.data('automatorwp-sendpulse-opening')) return;
                var cls = ($item[0] && $item[0].className) ? $item[0].className : '';
                if (cls.indexOf('sendpulse') === -1) return;

                // Mark as opening to prevent concurrent attempts
                try { $item.data('automatorwp-sendpulse-opening', true); } catch(e){}

                var label = $item.find('.automatorwp-automation-item-label > .automatorwp-option').first();
                var option_form = $item.find('.automatorwp-option-form-container').first();

                // If there's a UI toggle (label), prefer triggering it and wait for the form to become visible.
                if (label && label.length) {
                    try { label.trigger('click'); } catch(e){}

                    var attempts = 0;
                    var maxAttempts = 20;
                    var waitForForm = function() {
                        attempts++;
                        var visible = $item.find('.automatorwp-option-form-container:visible').first();
                        if (visible && visible.length) {
                            try {
                                if (typeof initAddressbookSelector === 'function') {
                                    initAddressbookSelector(visible, { autoSelectFirst: true, autoLoad: true });
                                } else if ( typeof window !== 'undefined' && typeof window.automatorwp_sendpulse_initAddressbookSelector === 'function' ) {
                                    window.automatorwp_sendpulse_initAddressbookSelector(visible, { autoSelectFirst: true, autoLoad: true });
                                }
                            } catch(e){}
                            try { $item.data('automatorwp-sendpulse-opened', true); } catch(e){}
                            try { $item.removeData('automatorwp-sendpulse-opening'); } catch(e){}
                            return;
                        }
                        if (attempts < maxAttempts) {
                            setTimeout(waitForForm, 100);
                            return;
                        }
                        // Fallback: force open the option form if it exists
                        if (option_form && option_form.length) {
                            try { option_form.addClass('automatorwp-option-form-active').slideDown('fast'); } catch(e){}
                            try {
                                if (typeof initAddressbookSelector === 'function') {
                                    initAddressbookSelector(option_form, { autoSelectFirst: true, autoLoad: true });
                                } else if ( typeof window !== 'undefined' && typeof window.automatorwp_sendpulse_initAddressbookSelector === 'function' ) {
                                    window.automatorwp_sendpulse_initAddressbookSelector(option_form, { autoSelectFirst: true, autoLoad: true });
                                }
                            } catch(e){}
                            try { $item.data('automatorwp-sendpulse-opened', true); } catch(e){}
                        } else {
                            try { $item.data('automatorwp-sendpulse-opened', true); } catch(e){}
                        }
                        try { $item.removeData('automatorwp-sendpulse-opening'); } catch(e){}
                    };
                    setTimeout(waitForForm, 100);
                    return;
                }

                // No label toggle available -> force-open safely
                    if (option_form && option_form.length) {
                    if (option_form.is(':visible') || option_form.is(':animated')) {
                        try {
                            if (typeof initAddressbookSelector === 'function') {
                                initAddressbookSelector(option_form, { autoSelectFirst: true, autoLoad: true });
                            } else if ( typeof window !== 'undefined' && typeof window.automatorwp_sendpulse_initAddressbookSelector === 'function' ) {
                                window.automatorwp_sendpulse_initAddressbookSelector(option_form, { autoSelectFirst: true, autoLoad: true });
                            }
                        } catch(e){}
                        try { $item.data('automatorwp-sendpulse-opened', true); } catch(e){}
                        try { $item.removeData('automatorwp-sendpulse-opening'); } catch(e){}
                        return;
                    }
                    setTimeout(function(){
                        try { $item.find('.automatorwp-option-form-active').removeClass('automatorwp-option-form-active').slideUp('fast'); } catch(e){}
                        try { option_form.addClass('automatorwp-option-form-active').slideDown('fast'); } catch(e){}
                        try {
                            if (typeof initAddressbookSelector === 'function') {
                                initAddressbookSelector(option_form, { autoSelectFirst: true, autoLoad: true });
                            } else if ( typeof window !== 'undefined' && typeof window.automatorwp_sendpulse_initAddressbookSelector === 'function' ) {
                                window.automatorwp_sendpulse_initAddressbookSelector(option_form, { autoSelectFirst: true, autoLoad: true });
                            }
                        } catch(e){}
                        try { $item.data('automatorwp-sendpulse-opened', true); } catch(e){}
                        try { $item.removeData('automatorwp-sendpulse-opening'); } catch(e){}
                    }, 225);
                } else {
                    // No option form found right now; clear opening flag so future attempts can try again.
                    try { $item.removeData('automatorwp-sendpulse-opening'); } catch(e){}
                }
            } catch(e) { console.warn('[automatorwp-sendpulse] tryOpenSendpulseItem error', e); }
        }

        var observer = new MutationObserver(function(mutations){
            mutations.forEach(function(m){
                if (m.addedNodes && m.addedNodes.length) {
                    Array.prototype.forEach.call(m.addedNodes, function(node){
                        try {
                            if (!node || node.nodeType !== 1) return;
                            if (node.matches && node.matches('.automatorwp-automation-item')) {
                                tryOpenSendpulseItem(node);
                            }
                            if (node.querySelectorAll) {
                                var items = node.querySelectorAll('.automatorwp-automation-item');
                                Array.prototype.forEach.call(items, function(it){ tryOpenSendpulseItem(it); });
                            }
                        } catch(err) { console.warn('[automatorwp-sendpulse] observer addedNodes error', err); }
                    });
                }
                if (m.type === 'attributes' && m.attributeName === 'class') {
                    try {
                        var target = m.target;
                        if (target && target.nodeType === 1) {
                            if (target.matches && target.matches('.automatorwp-automation-item')) {
                                tryOpenSendpulseItem(target);
                            } else if (target.closest) {
                                var parent = target.closest('.automatorwp-automation-item');
                                if (parent) tryOpenSendpulseItem(parent);
                            }
                        }
                    } catch(err) { console.warn('[automatorwp-sendpulse] observer attribute error', err); }
                }
            });
        });

        // Observe both triggers and actions containers
        Array.prototype.forEach.call(containers, function(ct){
            observer.observe(ct, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });
        });
    });
})(jQuery);

// Debug panel removed for production — no debug UI displayed.

// Extra admin handlers: generate/copy/test webhook token
(function($){
    // Generate token
    $('body').on('click', '#automatorwp_sendpulse_generate_token', function(e){
        e.preventDefault();
        var btn = $(this);
        btn.prop('disabled', true).text('Generating...');
        $.post( _getAjaxUrl(), { action: 'automatorwp_sendpulse_generate_webhook_token', nonce: automatorwp_sendpulse.nonce }, function(resp){
            if ( resp && resp.success && resp.data && resp.data.token ) {
                $('#automatorwp_sendpulse_webhook_token').val( resp.data.token );
            } else {
                alert('Unable to generate token');
            }
            btn.prop('disabled', false).text('Generate');
        }).fail(function(){ btn.prop('disabled', false).text('Generate'); alert('Request failed'); });
    });

    // Copy token
    $('body').on('click', '#automatorwp_sendpulse_copy_token', function(e){
        e.preventDefault();
        var $input = $('#automatorwp_sendpulse_webhook_token');
        $input.select();
        try {
            document.execCommand('copy');
            $(this).text('Copied');
            var self = $(this);
            setTimeout(function(){ self.text('Copy'); }, 2000);
        } catch(err) {
            alert('Copy not supported');
        }
    });

    // Test webhook
    $('body').on('click', '#automatorwp_sendpulse_test_webhook', function(e){
        e.preventDefault();
        var btn = $(this);
        var result = $('#automatorwp_sendpulse_webhook_test_result');
        btn.prop('disabled', true).text('Testing...');
        result.hide().removeClass('automatorwp-notice-success automatorwp-notice-error').html('');
        $.post( _getAjaxUrl(), { action: 'automatorwp_sendpulse_test_webhook', nonce: automatorwp_sendpulse.nonce }, function(resp){
            if ( resp && resp.success && resp.data ) {
                result.addClass('automatorwp-notice-success');
                result.html( 'Response: ' + resp.data.code + '<br/><pre style="white-space:pre-wrap;">' + $('<div/>').text(resp.data.body).html() + '</pre>' );
            } else {
                result.addClass('automatorwp-notice-error');
                var msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Test failed';
                result.html( msg );
            }
            result.slideDown('fast');
            btn.prop('disabled', false).text('Test webhook');
        }).fail(function(xhr){
            result.addClass('automatorwp-notice-error');
            result.html( 'Request failed: ' + xhr.status + ' ' + xhr.statusText );
            result.slideDown('fast');
            btn.prop('disabled', false).text('Test webhook');
        });
    });

})(jQuery);
