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

    /**
     * Ensure a visible fallback email input exists inside the option form.
     * Some admin themes or integration setups hide or render the CMB2 email
     * field differently; this helper injects a simple visible input so the
     * end user (client) can enter the Subscriber Email regardless of the UI.
     */
    function ensureFallbackEmailInput(option_form) {
        try {
            if (!option_form || !option_form.length) return;
            var $ = window.jQuery || jQuery;
            var emailInput = option_form.find('input[type="email"]').first();
            if (!emailInput || !emailInput.length) {
                emailInput = option_form.find('input[name="email"]').first();
            }

            // If a visible email input already exists, nothing to do.
            var inputVisible = false;
            try { inputVisible = emailInput && emailInput.length ? (emailInput.is(':visible') && emailInput.css('display') !== 'none') : false; } catch(e) { inputVisible = false; }
            if (inputVisible) return;

            // If we already injected a fallback input previously, don't inject again
            try {
                if ( option_form.find('input[data-automatorwp-fallback="1"]').length ) return;
            } catch(e){}

            // Inject a visible fallback input so the client always sees a Subscriber Email field
            try {
                // Check for an existing label that mentions 'Subscriber Email' so
                // we don't duplicate the title. If found, insert only the input
                // after that label. Otherwise create a full row with label + input.
                var foundLabel = null;
                try {
                    option_form.find('label').each(function(){
                        try {
                            var txt = $(this).text() || '';
                            if ( txt.indexOf('Subscriber Email') !== -1 ) { foundLabel = $(this); return false; }
                        } catch(e){}
                    });
                } catch(e){}

                var input = $('<input type="email" name="email" placeholder="example@domain.test" class="regular-text" data-automatorwp-fallback="1" />');

                if ( foundLabel && foundLabel.length ) {
                    // Insert just the input after the existing label's parent if possible
                    try {
                        var parent = foundLabel.parent();
                        if ( parent && parent.length ) {
                            parent.append(input);
                        } else {
                            option_form.prepend(input);
                        }
                        console.info('[automatorwp-sendpulse] fallback email input injected after existing label');
                    } catch(e) {
                        try { option_form.prepend(input); } catch(e) { option_form.append(input); }
                    }
                } else {
                    try {
                        var emailRow = $('<div class="cmb-row automatorwp-sendpulse-email-row" style="margin-bottom:8px;display:block;"></div>');
                        var label = $('<label class="cmb2-id-sub" style="display:block;font-weight:600;margin-bottom:4px;">Subscriber Email:<span style="color:#d00">*</span></label>');
                        emailRow.append(label).append(input);
                        try { option_form.prepend(emailRow); } catch(e){ option_form.append(emailRow); }
                        console.info('[automatorwp-sendpulse] fallback email input injected');
                    } catch(e) { /* ignore */ }
                }
            } catch(e) { /* ignore */ }
        } catch(e) { /* ignore */ }
    }

    /**
     * Ensure backing hidden inputs exist for fields that AutomatorWP saves to meta.
     * This creates (if missing) hidden inputs named: email, first_name, last_name, addressbook_id
     * and keeps them synchronized with the visible inputs/select so values are persisted when saving.
     */
    function ensureBackingFields(option_form) {
        try {
            if (!option_form || !option_form.length) return;
            var $ = window.jQuery || jQuery;

                    // helpers to find visible fields. Prefer CMB2-rendered inputs that have
                    // data-option attributes (AutomatorWP sets data-option instead of name).
                    var visibleEmail = option_form.find('input[data-option="email"]').first();
                    if (!visibleEmail || !visibleEmail.length) visibleEmail = option_form.find('input[type="email"]').first();
                    if (!visibleEmail || !visibleEmail.length) visibleEmail = option_form.find('input[name="email"]').first();

                    var visibleFirst = option_form.find('[data-option="first_name"]').first();
                    if (!visibleFirst || !visibleFirst.length) visibleFirst = option_form.find('input[name*="first_name"]').first();

                    var visibleLast = option_form.find('[data-option="last_name"]').first();
                    if (!visibleLast || !visibleLast.length) visibleLast = option_form.find('input[name*="last_name"]').first();

                    var addressbookSelect = option_form.find('select[data-option="addressbook_id"]').first();
                    if (!addressbookSelect || !addressbookSelect.length) addressbookSelect = option_form.find('select[name*="addressbook_id"]').first();

            // create or find hidden backing inputs in multiple potential containers
            function ensureHiddenInContainers(name) {
                var hid = null;
                // prefer option_form
                try { hid = option_form.find('input[type="hidden"][name="' + name + '"]').first(); } catch(e) { hid = null; }
                if (hid && hid.length) return hid;

                // try to find in the closest automation item container
                var item = null;
                try { item = option_form.closest('.automatorwp-automation-item'); } catch(e) { item = null; }
                try { if (item && item.length) hid = item.find('input[type="hidden"][name="' + name + '"]').first(); } catch(e) { hid = null; }
                if (hid && hid.length) return hid;

                // try to find inside any form wrapping the item
                try {
                    var form = option_form.closest('form');
                    if ((!hid || !hid.length) && form && form.length) hid = form.find('input[type="hidden"][name="' + name + '"]').first();
                } catch(e) { hid = null; }
                if (hid && hid.length) return hid;

                // Not found — create inside the option_form if possible, otherwise in the item, otherwise in document.body
                try {
                    hid = $('<input type="hidden" />').attr('name', name).attr('data-automatorwp-backing','1');
                    // Prefer appending to the nearest enclosing form so AutomatorWP's
                    // serialization picks up the hidden input reliably. Fall back to
                    // option_form, then item, then body.
                    try {
                        var form = option_form && option_form.length ? option_form.closest('form') : null;
                        if (form && form.length) {
                            form.append(hid);
                        } else if (option_form && option_form.length) {
                            option_form.append(hid);
                        } else if (item && item.length) {
                            item.append(hid);
                        } else {
                            $('body').append(hid);
                        }
                        try { console.info('[automatorwp-sendpulse] created backing input', name, 'appendedTo', (form && form.length) ? form[0] : (option_form && option_form.length) ? option_form[0] : (item && item.length) ? item[0] : document.body); } catch(e){}
                    } catch(e) {
                        try { $('body').append(hid); } catch(ignore) { }
                    }
                } catch(e) {
                    try { hid = $('<input type="hidden" />').attr('name', name).attr('data-automatorwp-backing','1'); $('body').append(hid); } catch(ignore) { hid = null; }
                }
                return hid;
            }

            var hidEmail = ensureHiddenInContainers('email');
            var hidFirst = ensureHiddenInContainers('first_name');
            var hidLast = ensureHiddenInContainers('last_name');
            var hidAddressbook = ensureHiddenInContainers('addressbook_id');

            // initial sync — prefer values from CMB2/data-option inputs, then fall back
            try {
                var v = '';
                if (visibleEmail && visibleEmail.length) v = visibleEmail.val();
                if (!v) {
                    var alt = option_form.find('input[name="email"]').first(); if (alt && alt.length) v = alt.val();
                }
                if (v) {
                    hidEmail.val(v);
                    try { console.info('[automatorwp-sendpulse] initial sync email ->', v, 'hidEmail parent form:', (hidEmail && hidEmail.length) ? hidEmail.closest('form')[0] : null); } catch(e){}
                } else {
                    try { console.info('[automatorwp-sendpulse] initial sync email empty; visibleEmail?', !!(visibleEmail && visibleEmail.length), 'alt present?', !!option_form.find('input[name="email"]').length); } catch(e){}
                }
            } catch(e){}
            try {
                var v = '';
                if (visibleFirst && visibleFirst.length) v = visibleFirst.val();
                if (!v) {
                    var alt = option_form.find('input[name*="first_name"]').first(); if (alt && alt.length) v = alt.val();
                }
                if (v) hidFirst.val(v);
            } catch(e){}
            try {
                var v = '';
                if (visibleLast && visibleLast.length) v = visibleLast.val();
                if (!v) {
                    var alt = option_form.find('input[name*="last_name"]').first(); if (alt && alt.length) v = alt.val();
                }
                if (v) hidLast.val(v);
            } catch(e){}
            try {
                var v = '';
                if (addressbookSelect && addressbookSelect.length) v = addressbookSelect.val();
                if (!v) {
                    var alt = option_form.find('input[name*="addressbook_id"]').first(); if (alt && alt.length) v = alt.val();
                }
                if (v) hidAddressbook.val(v);
            } catch(e){}

            // If CMB2 rendered fields exist with values, ensure any backing inputs
            // are populated from them so saved meta and UI stay in sync on reopen.
            try { populateBackingFromCMB2(option_form); } catch(e){}

            // helper to sync values across all backing inputs when a visible field changes
            function syncBacking(name, value) {
                try {
                    // update any matching hidden inputs across option_form, item and forms
                    try { option_form.find('input[type="hidden"][name="' + name + '"]').each(function(){ try{ $(this).val(value); }catch(ignore){} }); } catch(e){}
                    try { var item = option_form.closest('.automatorwp-automation-item'); if (item && item.length) item.find('input[type="hidden"][name="' + name + '"]').each(function(){ try{ $(this).val(value); }catch(ignore){} }); } catch(e){}
                    try { var form = option_form.closest('form'); if (form && form.length) form.find('input[type="hidden"][name="' + name + '"]').each(function(){ try{ $(this).val(value); }catch(ignore){} }); } catch(e){}
                    // also update any global ones in body
                    try { $('body').find('input[type="hidden"][name="' + name + '"]').each(function(){ try{ $(this).val(value); }catch(ignore){} }); } catch(e){}
                    try { if ( name === 'email' ) { console.info('[automatorwp-sendpulse] syncBacking', name, '->', value, 'option_form.closest(form):', option_form && option_form.length ? option_form.closest('form')[0] : null); } } catch(e){}
                } catch(e){}
            }

            // bind events to keep in sync
            try {
                if (visibleEmail && visibleEmail.length) visibleEmail.off('input.automatorwp_backing').on('input.automatorwp_backing', function(){ syncBacking('email', $(this).val()); });
                if (visibleFirst && visibleFirst.length) visibleFirst.off('input.automatorwp_backing').on('input.automatorwp_backing', function(){ syncBacking('first_name', $(this).val()); });
                if (visibleLast && visibleLast.length) visibleLast.off('input.automatorwp_backing').on('input.automatorwp_backing', function(){ syncBacking('last_name', $(this).val()); });
                if (addressbookSelect && addressbookSelect.length) addressbookSelect.off('change.automatorwp_backing').on('change.automatorwp_backing', function(){ syncBacking('addressbook_id', $(this).val()); });
            } catch(e){}

            // also ensure sync on any programmatic changes triggered by selects dropdowns
            try {
                option_form.find('.automatorwp-sendpulse-email-select').each(function(){ var s = $(this); s.off('change.automatorwp_backing_prog').on('change.automatorwp_backing_prog', function(){ syncBacking('email', $(this).val()); }); });
            } catch(e){}

            // final safety: when any form is submitted in admin, make sure backing inputs mirror visible values
            try { $('body').off('submit.automatorwp_backing').on('submit.automatorwp_backing', 'form', function(){ try { if (visibleEmail && visibleEmail.length) syncBacking('email', visibleEmail.val()); if (visibleFirst && visibleFirst.length) syncBacking('first_name', visibleFirst.val()); if (visibleLast && visibleLast.length) syncBacking('last_name', visibleLast.val()); if (addressbookSelect && addressbookSelect.length) syncBacking('addressbook_id', addressbookSelect.val()); } catch(e){} }); } catch(e){}

        } catch(e) { /* ignore */ }
    }

    /**
     * Populate any backing hidden inputs from CMB2-rendered fields (data-option/id).
     * This runs when an option form is initialized so hidden inputs reflect
     * stored meta and the visible UI stays in sync after saving/reopen.
     */
    function populateBackingFromCMB2(option_form) {
        try {
            if (!option_form || !option_form.length) return;
            var $ = window.jQuery || jQuery;
            var mapping = {
                'email': ['[data-option="email"]', 'input[type="email"]', 'input[name="email"]'],
                'first_name': ['[data-option="first_name"]', 'input[name*="first_name"]', 'input[id*="first_name-"]'],
                'last_name': ['[data-option="last_name"]', 'input[name*="last_name"]', 'input[id*="last_name-"]'],
                'addressbook_id': ['select[data-option="addressbook_id"]', 'select[name*="addressbook_id"]', 'input[name*="addressbook_id"]']
            };

            Object.keys(mapping).forEach(function(name){
                try {
                    var selectors = mapping[name];
                    var val = '';
                    for (var i=0;i<selectors.length;i++) {
                        try { var src = option_form.find(selectors[i]).first(); if (src && src.length && src.val()) { val = src.val(); break; } } catch(e){}
                    }
                    // If still empty, try inputs elsewhere in the item
                    if (!val) {
                        try { var item = option_form.closest('.automatorwp-automation-item'); if (item && item.length) { var alt = item.find(selectors.join(',' )).first(); if (alt && alt.length && alt.val()) val = alt.val(); } } catch(e){}
                    }
                    if (val) {
                        try { option_form.find('input[type="hidden"][name="' + name + '"]').each(function(){ try{ $(this).val(val); }catch(ignore){} }); } catch(e){}
                        try { var bodyAlt = $('body').find('input[type="hidden"][name="' + name + '"]').each(function(){ try{ $(this).val(val); }catch(ignore){} }); } catch(e){}
                        // update visible fallback email if present
                        if (name === 'email') {
                            try { var vf = option_form.find('input[data-automatorwp-fallback="1"]').first(); if (vf && vf.length && !vf.val()) vf.val(val); } catch(e){}
                        }
                    }
                } catch(e){}
            });
        } catch(e){}
    }

    /**
     * Force-populate underlying select with addressbooks via AJAX.
     * Used as a fallback when the UI is hidden and Select2 measures 0x0.
     */
    function _forcePopulateAddressbooks(select, option_form, existingVal) {
        try {
            if (!select || !select.length) return;
            // If there are already options (beyond placeholder), skip
            if (select.find('option').length > 1) return;
            $.post(_getAjaxUrl(), { action: 'automatorwp_sendpulse_list_addressbooks', nonce: automatorwp_sendpulse.nonce }, function(resp){
                if (!resp || resp.success !== true) return;
                var books = (resp.data && resp.data.addressbooks) ? resp.data.addressbooks : [];
                var existing = {};
                select.find('option').each(function(){ existing[$(this).val()] = true; });
                books.forEach(function(b){
                    if (!existing[b.id]) select.append($('<option>').attr('value', b.id).text(b.name));
                });
                if (existingVal) select.val(existingVal);
                // Trigger change so Select2 updates its UI when options are added dynamically
                try { select.trigger('change'); } catch(e){}
                // Ensure AutomatorWP selector attributes and initialize Select2 after force-populate
                try {
                    if ( select && select.length ) {
                        if ( ! select.attr('data-action') ) select.attr('data-action', 'automatorwp_sendpulse_list_addressbooks');
                        if ( ! select.hasClass('automatorwp-ajax-selector') ) select.addClass('automatorwp-ajax-selector');
                        try { select.removeAttr('data-select2-id'); } catch(e){}
                        try { select.next('.select2').remove(); } catch(e){}
                        if ( typeof automatorwp_ajax_selector === 'function' ) {
                            try { automatorwp_ajax_selector( select ); } catch(e){}
                        } else if ( typeof automatorwp_select2 === 'function' ) {
                            try { automatorwp_select2( select ); } catch(e){}
                        }
                    }
                } catch(e){}
                // show load button if value present
                try {
                    var loadbtn = option_form && option_form.length ? option_form.find('.automatorwp-sendpulse-load-emails') : null;
                    if (loadbtn && loadbtn.length && select.val()) loadbtn.show();
                } catch(e){}
                try { console.info('[automatorwp-sendpulse] forcePopulateAddressbooks completed; options:', select.find('option').length); } catch(e){}
            });
        } catch(e) { console.warn('[automatorwp-sendpulse] forcePopulateAddressbooks error', e); }
    }

        // Ensure an existing addressbook select is initialized with AutomatorWP Select2
        function _ensureInitAddressbookSelect( select, option_form ) {
            try {
                if ( !select || !select.length ) return;
                // mark it so we don't re-init unnecessarily
                try { select.addClass('automatorwp-sendpulse-addressbook-select'); } catch(e){}
                if ( ! select.attr('data-action') ) select.attr('data-action', 'automatorwp_sendpulse_list_addressbooks');
                // Remove any stale Select2 instance
                try { select.removeAttr('data-select2-id'); } catch(e){}
                try { select.next('.select2').remove(); } catch(e){}
                // Initialize AutomatorWP ajax selector if available
                if ( typeof automatorwp_ajax_selector === 'function' ) {
                    try { automatorwp_ajax_selector( select ); } catch(e){}
                } else if ( typeof automatorwp_select2 === 'function' ) {
                    try { automatorwp_select2( select ); } catch(e){}
                } else {
                    try { select.trigger('change'); } catch(e){}
                    var sel = of.find('.automatorwp-sendpulse-addressbook-select'); 

                // Keep a binding to sync value into any backing input field
                try {
                    var input = null;
                    if ( option_form && option_form.length ) {
                        input = option_form.find('input[data-option="addressbook_id"], input[name*="addressbook_id"], input[id*="addressbook_id"]').first();
                    }
                    if ( (!input || !input.length) && select && select.length ) {
                        var item = select.closest('.automatorwp-automation-item');
                        if ( item && item.length ) input = item.find('input[data-option="addressbook_id"], input[name*="addressbook_id"], input[id*="addressbook_id"]').first();
                    }
                    if ( input && input.length ) {
                        select.off('change.automatorwp_sendpulse_sync').on('change.automatorwp_sendpulse_sync', function(){
                            try { input.val( $(this).val() ); } catch(e){}
                        });
                    }
                } catch(e){}

            } catch(e) { try{ console.warn('[automatorwp-sendpulse] ensureInitAddressbookSelect error', e); }catch(ignore){} }
        }

    // Expose quick console hook for forcing population on demand
    try {
        if ( typeof window !== 'undefined' ) {
            window.automatorwp_sendpulse_forcePopulateAddressbooks = function(option_form){
                try {
                    var of = option_form && option_form.length ? option_form : $('.automatorwp-option-form-container').first();
                    var sel = of.find('.automatorwp-sendpulse-addressbook-select');
                    // fallback: find any select rendered by CMB2 with data-option
                    if ((!sel || !sel.length) && of && of.length) {
                        sel = of.find('select[data-option="addressbook_id"], select[name*="addressbook_id"], select[id*="addressbook_id"]').first();
                    }
                    var existing = '';
                    var inp = of.find('input[data-option="addressbook_id"], input[name*="addressbook_id"], input[id*="addressbook_id"]').first();
                    if (inp && inp.length) existing = inp.val();
                    // If sel is an input (rare), try to locate a select elsewhere in the item
                    if ( sel && sel.length && sel.is('input') ) {
                        var alt = of.find('select[data-option="addressbook_id"]').first();
                        if (alt && alt.length) sel = alt;
                    }
                    _forcePopulateAddressbooks(sel, of, existing);
                } catch(e) { console.warn('[automatorwp-sendpulse] automatorwp_sendpulse_forcePopulateAddressbooks error', e); }
            };
        }
    } catch(e){}

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
        // Ensure fallback email input early so it's present for token insertion
        try { ensureFallbackEmailInput(option_form); } catch(e){}
        // Ensure backing hidden inputs exist and are synced so values persist when the action is saved
        try { ensureBackingFields(option_form); } catch(e){}

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

        // Extra robustness: if a CMB2-rendered select exists somewhere in the automation item,
        // prefer initializing that existing select instead of creating a new one.
        try {
            var existing_global_sel = null;
            var item = option_form.closest('.automatorwp-automation-item');
            if ( option_form && option_form.length ) {
                existing_global_sel = option_form.find('select[data-option="addressbook_id"]').first();
            }
            if ( (!existing_global_sel || !existing_global_sel.length) && item && item.length ) {
                existing_global_sel = item.find('select[data-option="addressbook_id"]').first();
            }
            if ( existing_global_sel && existing_global_sel.length ) {
                try { _ensureInitAddressbookSelect( existing_global_sel, option_form ); } catch(e){}
                // Ensure a load button exists and is visible if value present
                try {
                    var row_for_sel = existing_global_sel.closest('.cmb-row, .cmb2-row, .cmb2-id, .form-field, .control-group');
                    if ( !row_for_sel.length ) row_for_sel = existing_global_sel.parent();
                    var loadBtn = row_for_sel.find('.automatorwp-sendpulse-load-emails');
                    if ( !loadBtn.length ) {
                        loadBtn = $('<button type="button" class="button automatorwp-sendpulse-load-emails" style="margin-left:8px;display:none">Load emails</button>');
                        row_for_sel.append(loadBtn);
                        // attach click handler similar to created select
                        loadBtn.on('click', function(){
                            var ab = existing_global_sel.val();
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
                                     // Prefer placing the email list dropdown after the Load emails button
                                     // so the control the user clicked is immediately associated with
                                     // the resulting select. Fall back to inserting after the email
                                     // input or the row if Load button isn't available.
                                     if (typeof loadBtn !== 'undefined' && loadBtn && loadBtn.length) {
                                         emailWrapper = $('<div class="automatorwp-sendpulse-email-wrapper" style="margin-top:6px;"></div>');
                                         loadBtn.after(emailWrapper);
                                     } else if (emailInput && emailInput.length) {
                                         emailWrapper = $('<div class="automatorwp-sendpulse-email-wrapper" style="margin-top:6px;"></div>');
                                         emailInput.after(emailWrapper);
                                     } else {
                                         emailWrapper = $('<div class="automatorwp-sendpulse-email-wrapper" style="margin-top:6px;"></div>');
                                         row_for_sel.after(emailWrapper);
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
                                // Synchronize select <-> visible email input so the client
                                // sees the selected email in the Subscriber Email field.
                                (function(sel, option_form, emailWrapper){
                                    try {
                                        var emailInput = option_form.find('input[type="email"]').first();
                                        if (!emailInput || !emailInput.length) emailInput = option_form.find('input[name="email"]').first();

                                        // If an email is already present in the input, try to select it in the dropdown
                                        if (emailInput && emailInput.length) {
                                            var current = (emailInput.val() || '').trim();
                                            if (current) {
                                                if ( sel.find('option[value="' + current + '"]').length ) {
                                                    try { sel.val(current).trigger('change'); } catch(e){}
                                                } else {
                                                    // add current email as first option so it remains selectable
                                                    try { sel.prepend($('<option>').attr('value', current).text(current + ' (Current)')); sel.val(current).trigger('change'); } catch(e){}
                                                }
                                            }

                                            // When the input changes manually, try to reflect it in the select
                                            try {
                                                emailInput.off('input.automatorwp_sendpulse_sync').on('input.automatorwp_sendpulse_sync', function(){
                                                    var iv = ($(this).val() || '').trim();
                                                    if (iv && sel.find('option[value="' + iv + '"]').length) {
                                                        try { sel.val(iv).trigger('change'); } catch(e){}
                                                    } else {
                                                        try { sel.val(''); } catch(e){}
                                                    }
                                                });
                                            } catch(e){}
                                        }

                                        sel.off('change.automatorwp_sendpulse_emailsync').on('change.automatorwp_sendpulse_emailsync', function(){
                                            var v = $(this).val();
                                            try {
                                                var emailInput2 = option_form.find('input[type="email"]').first();
                                                if (emailInput2 && emailInput2.length) {
                                                    emailInput2.val(v);
                                                    try { emailInput2.focus(); } catch(e){}
                                                } else {
                                                    var hidden = option_form.find('input[name="email"]');
                                                    if (!hidden.length) {
                                                        hidden = $('<input type="hidden" name="email">');
                                                        emailWrapper.after(hidden);
                                                    }
                                                    hidden.val(v);
                                                }
                                            } catch(e){}
                                        });
                                    } catch(e) { /* ignore */ }
                                })(sel, option_form, emailWrapper);
                            }).fail(function(){ btn.prop('disabled', false).text('Load emails'); alert('Request failed'); });
                        });
                    }
                    if ( existing_global_sel.val() ) loadBtn.show();
                } catch(e){}
                return;
            }
        } catch(e) { console.warn('[automatorwp-sendpulse] existing select check failed', e); }

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

        // Ensure there is a visible email input for Subscriber Email. Some
        // AutomatorWP setups or admin themes may render the CMB2 input in a
        // way that is hidden or not present; create a fallback visible input
        // so the end-user (client) can enter the subscriber email directly.
        try {
            var emailInput = option_form.find('input[type="email"]').first();
            if (!emailInput || !emailInput.length) {
                // Also check for existing hidden input named 'email'
                emailInput = option_form.find('input[name="email"]').first();
            }
            if (!emailInput || !emailInput.length) {
                // Build a simple row compatible with CMB2-like layout
                var emailRow = $('<div class="cmb-row automatorwp-sendpulse-email-row" style="margin-bottom:8px;"></div>');
                var label = $('<label class="cmb2-id-sub" style="display:block;font-weight:600;margin-bottom:4px;">Subscriber Email:<span style="color:#d00">*</span></label>');
                var input = $('<input type="email" name="email" placeholder="example@domain.test" class="regular-text" />');
                emailRow.append(label).append(input);
                // Insert the email row at top of the option form so it's visible
                try { option_form.prepend(emailRow); } catch(e){ option_form.append(emailRow); }
                emailInput = input;
                console.info('[automatorwp-sendpulse] fallback email input injected');
            }
        } catch(e) { /* ignore errors */ }

        // Prefer to use the existing CMB2 <select> and let AutomatorWP initialize it via its helper
        var existingVal = input && input.length ? input.val() : '';

        var select = null;
        if ( input && input.length && input.is('select') ) {
            select = input;
        } else {
            select = row.find('select[data-option="addressbook_id"], select[name*="addressbook_id"], select[id*="addressbook_id"]').first();
        }

        if ( !select || !select.length ) {
            console.info('[automatorwp-sendpulse] no addressbook <select> found; skipping dynamic creation');
            return;
        }

        try { select.addClass('automatorwp-sendpulse-addressbook-select'); } catch(e){}
        try { if ( ! select.attr('data-action') ) select.attr('data-action', 'automatorwp_sendpulse_list_addressbooks'); } catch(e){}
        try { if ( ! select.hasClass('automatorwp-ajax-selector') ) select.addClass('automatorwp-ajax-selector'); } catch(e){}
        try { select.removeAttr('data-select2-id'); } catch(e){}
        try { select.next('.select2').remove(); } catch(e){}

        if ( typeof automatorwp_ajax_selector === 'function' ) {
            try { automatorwp_ajax_selector( select ); } catch(e){}
        } else if ( typeof automatorwp_select2 === 'function' ) {
            try { automatorwp_select2( select ); } catch(e){}
        }

        // Ensure load emails button exists and works against the existing select
        var row_for_sel = select.closest('.cmb-row, .cmb2-row, .cmb2-id, .form-field, .control-group');
        if ( !row_for_sel.length ) row_for_sel = select.parent();
        var loadBtn = row_for_sel.find('.automatorwp-sendpulse-load-emails');
        if ( !loadBtn.length ) {
            loadBtn = $('<button type="button" class="button automatorwp-sendpulse-load-emails" style="margin-left:8px;display:none">Load emails</button>');
            row_for_sel.append(loadBtn);
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
                        if (typeof loadBtn !== 'undefined' && loadBtn && loadBtn.length) {
                            emailWrapper = $('<div class="automatorwp-sendpulse-email-wrapper" style="margin-top:6px;"></div>');
                            loadBtn.after(emailWrapper);
                        } else if (emailInput && emailInput.length) {
                            emailWrapper = $('<div class="automatorwp-sendpulse-email-wrapper" style="margin-top:6px;"></div>');
                            emailInput.after(emailWrapper);
                        } else {
                            emailWrapper = $('<div class="automatorwp-sendpulse-email-wrapper" style="margin-top:6px;"></div>');
                            row_for_sel.after(emailWrapper);
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
                    // Keep select and email input synchronized (both directions).
                    (function(sel, option_form, emailWrapper){
                        try {
                            var emailInput = option_form.find('input[type="email"]').first();
                            if (!emailInput || !emailInput.length) emailInput = option_form.find('input[name="email"]').first();

                            // If input already contains an address, pre-select it in dropdown
                            if (emailInput && emailInput.length) {
                                var cur = (emailInput.val() || '').trim();
                                if (cur) {
                                    if ( sel.find('option[value="' + cur + '"]').length ) {
                                        try { sel.val(cur).trigger('change'); } catch(e){}
                                    } else {
                                        try { sel.prepend($('<option>').attr('value', cur).text(cur + ' (Current)')); sel.val(cur).trigger('change'); } catch(e){}
                                    }
                                }

                                // When input is edited manually, reflect in select if possible
                                try {
                                    emailInput.off('input.automatorwp_sendpulse_sync').on('input.automatorwp_sendpulse_sync', function(){
                                        var iv = ($(this).val() || '').trim();
                                        if (iv && sel.find('option[value="' + iv + '"]').length) {
                                            try { sel.val(iv).trigger('change'); } catch(e){}
                                        } else {
                                            try { sel.val(''); } catch(e){}
                                        }
                                    });
                                } catch(e){}
                            }

                            sel.off('change.automatorwp_sendpulse_emailsync').on('change.automatorwp_sendpulse_emailsync', function(){
                                var v = $(this).val();
                                try {
                                    var emailInput2 = option_form.find('input[type="email"]').first();
                                    if (emailInput2 && emailInput2.length) {
                                        emailInput2.val(v);
                                        try { emailInput2.focus(); } catch(e){}
                                    } else {
                                        var hidden = option_form.find('input[name="email"]');
                                        if (!hidden.length) {
                                            hidden = $('<input type="hidden" name="email">');
                                            emailWrapper.after(hidden);
                                        }
                                        hidden.val(v);
                                    }
                                } catch(e){}
                            });
                        } catch(e) { /* ignore */ }
                    })(sel, option_form, emailWrapper);
                }).fail(function(){ btn.prop('disabled', false).text('Load emails'); alert('Request failed'); });
            });
        }
        if ( select.val() ) loadBtn.show();

        // Keep underlying input (if present) in sync with select value
        try {
            select.off('change.automatorwp_sendpulse_sync').on('change.automatorwp_sendpulse_sync', function(){
                var v = $(this).val();
                if (input && input.length && !input.is('select')) input.val(v);
                if (v) loadBtn.show(); else loadBtn.hide();
            });
        } catch(e){}

        if ( autoLoad && select.val() ) {
            try { loadBtn.trigger('click'); } catch(e){}
        }

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

                // Instead of triggering a click on the UI toggle (which may be
                // intercepted by browser extensions or other admin scripts and
                // produce async/message-channel errors), directly ensure the
                // option form is visible and initialize it. This avoids relying
                // on the host page event handlers and makes the UI more robust.
                if (option_form && option_form.length) {
                    try {
                        // Remove draggable attributes from potential handles to
                        // avoid 'move' cursor interception that blocks clicks.
                        try { document.querySelectorAll('[draggable="true"]').forEach(function(e){ e.removeAttribute('draggable'); e.style.cursor = ''; }); } catch(ignore){}

                        // Force the option form visible
                        try { option_form.addClass('automatorwp-option-form-active').show(); } catch(ignore){}

                        // Initialize the addressbook selector on the visible form
                        try {
                            if (typeof initAddressbookSelector === 'function') {
                                initAddressbookSelector(option_form, { autoSelectFirst: true, autoLoad: true });
                            } else if ( typeof window !== 'undefined' && typeof window.automatorwp_sendpulse_initAddressbookSelector === 'function' ) {
                                window.automatorwp_sendpulse_initAddressbookSelector(option_form, { autoSelectFirst: true, autoLoad: true });
                            }
                        } catch(e) { console.warn('[automatorwp-sendpulse] init fallback error', e); }

                        try { $item.data('automatorwp-sendpulse-opened', true); } catch(e){}
                        try { $item.removeData('automatorwp-sendpulse-opening'); } catch(e){}
                    } catch(e) {
                        try { $item.removeData('automatorwp-sendpulse-opening'); } catch(ignore){}
                    }
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

// Ensure addressbook selector is initialized in hidden/collapsed option forms
(function($){
    function _ensureInitAddressbookOnHiddenForms() {
        var attempts = 0;
        var maxAttempts = 20; // ~5s (20 * 250ms)
        var interval = setInterval(function(){
            attempts++;
            $('.automatorwp-option-form-container').each(function(){
                try {
                    var of = $(this);
                    // Only target SendPulse action items
                    var parentItem = of.closest('.automatorwp-automation-item[class*="sendpulse"]');
                    if (!parentItem.length) return;

                    // If selector already present, skip
                    if ( of.find('.automatorwp-sendpulse-addressbook-select').length ) return;

                    // If there's an addressbook input/placeholder field, try to init
                    var hasField = of.find('input[data-option="addressbook_id"], select[data-option="addressbook_id"], input[name*="addressbook_id"], select[name*="addressbook_id"], input[id*="addressbook_id"], select[id*="addressbook_id"]').length;
                    if ( hasField ) {
                        if ( typeof initAddressbookSelector === 'function' ) {
                            initAddressbookSelector( of, { autoSelectFirst: true, autoLoad: true } );
                        } else if ( typeof window !== 'undefined' && typeof window.automatorwp_sendpulse_initAddressbookSelector === 'function' ) {
                            window.automatorwp_sendpulse_initAddressbookSelector( of, { autoSelectFirst: true, autoLoad: true } );
                        }
                    }
                } catch(e) { try{ console.warn('[automatorwp-sendpulse] ensureInit error', e); }catch(ignore){} }
            });
            if ( attempts >= maxAttempts ) {
                clearInterval(interval);
            }
        }, 250);
    }

    $(document).ready(function(){ _ensureInitAddressbookOnHiddenForms(); });
})(jQuery);

// Observe document for CMB2-rendered addressbook selects and initialize them
(function($){
    $(document).ready(function(){
        try {
            var mo = new MutationObserver(function(mutations){
                mutations.forEach(function(m){
                    if (m.addedNodes && m.addedNodes.length) {
                        Array.prototype.forEach.call(m.addedNodes, function(node){
                            try {
                                if (!node || node.nodeType !== 1) return;
                                if (node.matches && node.matches('select[data-option="addressbook_id"]')) {
                                    try { _ensureInitAddressbookSelect( $(node), $(node).closest('.automatorwp-option-form-container') ); } catch(e){}
                                } else if (node.querySelectorAll) {
                                    var sels = node.querySelectorAll('select[data-option="addressbook_id"]');
                                    Array.prototype.forEach.call(sels, function(s){ try { _ensureInitAddressbookSelect( $(s), $(s).closest('.automatorwp-option-form-container') ); } catch(e){} });
                                }
                            } catch(e){}
                        });
                    }
                });
            });
            mo.observe(document.body || document.documentElement, { childList: true, subtree: true });
        } catch(e){}
    });
})(jQuery);
