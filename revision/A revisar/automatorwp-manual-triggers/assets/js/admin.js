/**
 * AutomatorWP Manual Triggers - Admin JavaScript
 *
 * Enhances the trigger edit panel with:
 * - "Run Now" section (with user ID input for logged-in triggers)
 * - PHP code example (collapsible)
 * - Shortcode example
 *
 * @package AutomatorWP\Manual_Triggers
 * @since   1.0.0
 */
(function($) {

    // Trigger types we handle
    var LOGGED_IN_TYPE  = 'manual_triggers_manual_launch';
    var ANONYMOUS_TYPE  = 'manual_triggers_anonymous_manual_launch';

    /**
     * Build the HTML for the "Run Now" section
     */
    function buildRunNowSection( triggerId, isLoggedIn ) {

        var html = '<div class="automatorwp-manual-trigger-section automatorwp-manual-trigger-run-now">';
        html += '<h4>' + automatorwp_manual_triggers.i18n.run_now + '</h4>';

        if ( isLoggedIn ) {
            html += '<div class="automatorwp-manual-trigger-field">';
            html += '<label>' + automatorwp_manual_triggers.i18n.user_id + '</label>';
            html += '<input type="number" class="automatorwp-manual-trigger-user-id" value="" placeholder="' + automatorwp_manual_triggers.i18n.user_id_placeholder + '" />';
            html += '</div>';
        }

        html += '<button type="button" class="button button-primary automatorwp-manual-trigger-run-btn" data-trigger-id="' + triggerId + '" data-logged-in="' + ( isLoggedIn ? '1' : '0' ) + '">';
        html += automatorwp_manual_triggers.i18n.run_now;
        html += '</button>';
        html += '<span class="automatorwp-manual-trigger-run-status"></span>';
        html += '</div>';

        return html;
    }

    /**
     * Build the HTML for the PHP code example section (collapsible)
     */
    function buildCodeExampleSection( triggerId, isLoggedIn ) {

        var html = '<div class="automatorwp-manual-trigger-section automatorwp-manual-trigger-code">';
        html += '<h4 class="automatorwp-manual-trigger-toggle">';
        html += '<span class="dashicons dashicons-arrow-right-alt2"></span> ';
        html += automatorwp_manual_triggers.i18n.code_example;
        html += '</h4>';
        html += '<div class="automatorwp-manual-trigger-toggle-content" style="display:none;">';

        if ( isLoggedIn ) {
            // Logged-in: code example WITH user ID
            html += '<p>' + automatorwp_manual_triggers.i18n.code_example_desc_logged_in + '</p>';
            html += '<pre class="automatorwp-manual-trigger-pre"><code>';
            html += '// ' + automatorwp_manual_triggers.i18n.code_basic_usage + '\n';
            html += 'automatorwp_run_trigger( ' + triggerId + ' );\n';
            html += '// ' + automatorwp_manual_triggers.i18n.code_current_user + '\n\n';
            html += '// ' + automatorwp_manual_triggers.i18n.code_specific_user + '\n';
            html += 'automatorwp_run_trigger( ' + triggerId + ', 123 );\n';
            html += '// ' + automatorwp_manual_triggers.i18n.code_user_123;
            html += '</code></pre>';
        } else {
            // Anonymous: code example WITHOUT user ID
            html += '<p>' + automatorwp_manual_triggers.i18n.code_example_desc_anonymous + '</p>';
            html += '<pre class="automatorwp-manual-trigger-pre"><code>';
            html += '// ' + automatorwp_manual_triggers.i18n.code_basic_usage + '\n';
            html += 'automatorwp_run_trigger( ' + triggerId + ' );';
            html += '</code></pre>';
        }

        html += '</div>';
        html += '</div>';

        return html;
    }

    /**
     * Build the HTML for the Shortcode section
     */
    function buildShortcodeSection( triggerId, isLoggedIn ) {

        var html = '<div class="automatorwp-manual-trigger-section automatorwp-manual-trigger-shortcode">';
        html += '<h4 class="automatorwp-manual-trigger-toggle">';
        html += '<span class="dashicons dashicons-arrow-right-alt2"></span> ';
        html += automatorwp_manual_triggers.i18n.shortcode;
        html += '</h4>';
        html += '<div class="automatorwp-manual-trigger-toggle-content" style="display:none;">';

        if ( isLoggedIn ) {
            html += '<p>' + automatorwp_manual_triggers.i18n.shortcode_desc_logged_in + '</p>';

            // Shortcode with current logged-in user (user="")
            html += '<label>' + automatorwp_manual_triggers.i18n.shortcode_current_user + '</label>';
            html += '<pre class="automatorwp-manual-trigger-pre"><code>';
            html += '[automatorwp_manual_trigger trigger="' + triggerId + '" user="" label="' + automatorwp_manual_triggers.i18n.run_label + '"]';
            html += '</code></pre>';

            // Shortcode with specific user ID
            html += '<label>' + automatorwp_manual_triggers.i18n.shortcode_specific_user + '</label>';
            html += '<pre class="automatorwp-manual-trigger-pre"><code>';
            html += '[automatorwp_manual_trigger trigger="' + triggerId + '" user="123" label="' + automatorwp_manual_triggers.i18n.run_label + '"]';
            html += '</code></pre>';
        } else {
            html += '<p>' + automatorwp_manual_triggers.i18n.shortcode_desc_anonymous + '</p>';

            // Shortcode without user
            html += '<pre class="automatorwp-manual-trigger-pre"><code>';
            html += '[automatorwp_manual_trigger trigger="' + triggerId + '" label="' + automatorwp_manual_triggers.i18n.run_label + '"]';
            html += '</code></pre>';
        }

        html += '</div>';
        html += '</div>';

        return html;
    }

    /**
     * Inject the manual trigger sections into a trigger panel
     */
    function injectManualTriggerSections( $triggerItem ) {

        // Skip if already injected
        if ( $triggerItem.find('.automatorwp-manual-trigger-sections').length ) {
            return;
        }

        var triggerType = $triggerItem.find('.automatorwp-trigger-type').val()
                       || $triggerItem.data('type')
                       || $triggerItem.attr('data-type');

        // Also try to get from hidden input
        if ( ! triggerType ) {
            triggerType = $triggerItem.find('input[name*="[type]"]').val();
        }

        if ( triggerType !== LOGGED_IN_TYPE && triggerType !== ANONYMOUS_TYPE ) {
            return;
        }

        var isLoggedIn = ( triggerType === LOGGED_IN_TYPE );

        // Get the trigger ID (the post ID of this trigger item in the automation)
        var triggerId = $triggerItem.find('.automatorwp-trigger-id').val()
                     || $triggerItem.data('id')
                     || $triggerItem.attr('data-id');

        if ( ! triggerId ) {
            triggerId = $triggerItem.find('input[name*="[id]"]').val();
        }

        if ( ! triggerId || triggerId === '0' ) {
            // Trigger hasn't been saved yet, show placeholder
            triggerId = '{ID}';
        }

        // Build the sections wrapper
        var html = '<div class="automatorwp-manual-trigger-sections">';
        html += buildRunNowSection( triggerId, isLoggedIn );
        html += buildCodeExampleSection( triggerId, isLoggedIn );
        html += buildShortcodeSection( triggerId, isLoggedIn );
        html += '</div>';

        // Find the best insertion point: after the options, before the times field
        var $optionsWrap = $triggerItem.find('.automatorwp-trigger-options, .cmb2-wrap, .automatorwp-option-form');

        if ( $optionsWrap.length ) {
            $optionsWrap.last().after( html );
        } else {
            // Fallback: append to the trigger content area
            var $content = $triggerItem.find('.automatorwp-trigger-content, .automatorwp-automation-item-content');
            if ( $content.length ) {
                $content.append( html );
            } else {
                $triggerItem.append( html );
            }
        }
    }

    /**
     * Scan for manual trigger panels and inject sections
     */
    function scanAndInject() {
        // Look for trigger items in the automation editor
        $('.automatorwp-automation-item[data-type="' + LOGGED_IN_TYPE + '"], ' +
          '.automatorwp-automation-item[data-type="' + ANONYMOUS_TYPE + '"]').each(function() {
            injectManualTriggerSections( $(this) );
        });

        // Also scan by trigger type input value
        $('input[name*="[type]"]').each(function() {
            var val = $(this).val();
            if ( val === LOGGED_IN_TYPE || val === ANONYMOUS_TYPE ) {
                var $item = $(this).closest('.automatorwp-automation-item');
                if ( $item.length ) {
                    injectManualTriggerSections( $item );
                }
            }
        });
    }

    // -------------------------------------------------------------------------
    // Event Handlers
    // -------------------------------------------------------------------------

    // Toggle collapsible sections
    $(document).on('click', '.automatorwp-manual-trigger-toggle', function(e) {
        e.preventDefault();
        var $content = $(this).next('.automatorwp-manual-trigger-toggle-content');
        var $icon = $(this).find('.dashicons');

        $content.slideToggle(200);
        $icon.toggleClass('dashicons-arrow-right-alt2 dashicons-arrow-down-alt2');
    });

    // "Run Now" button click
    $(document).on('click', '.automatorwp-manual-trigger-run-btn', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var triggerId = $btn.data('trigger-id');
        var isLoggedIn = $btn.data('logged-in') === 1 || $btn.data('logged-in') === '1';
        var userId = 0;
        var $status = $btn.siblings('.automatorwp-manual-trigger-run-status');

        if ( isLoggedIn ) {
            var $userInput = $btn.siblings('.automatorwp-manual-trigger-field').find('.automatorwp-manual-trigger-user-id');
            if ( ! $userInput.length ) {
                $userInput = $btn.parent().find('.automatorwp-manual-trigger-user-id');
            }
            userId = $userInput.val() ? parseInt( $userInput.val(), 10 ) : 0;
        }

        // Disable button
        $btn.prop('disabled', true).text( automatorwp_manual_triggers.i18n.running );
        $status.text('');

        $.ajax({
            url: automatorwp_manual_triggers.ajax_url,
            type: 'POST',
            data: {
                action: 'automatorwp_manual_trigger_admin_run',
                trigger_id: triggerId,
                user_id: userId,
                nonce: automatorwp_manual_triggers.nonce,
            },
            success: function( response ) {
                if ( response.success ) {
                    $status.text( automatorwp_manual_triggers.i18n.done ).css('color', '#46b450');
                } else {
                    $status.text( response.data.message || automatorwp_manual_triggers.i18n.error ).css('color', '#dc3232');
                }
            },
            error: function() {
                $status.text( automatorwp_manual_triggers.i18n.error ).css('color', '#dc3232');
            },
            complete: function() {
                $btn.prop('disabled', false).text( automatorwp_manual_triggers.i18n.run_now );
                setTimeout(function() {
                    $status.text('');
                }, 3000);
            }
        });
    });

    // -------------------------------------------------------------------------
    // Initialization
    // -------------------------------------------------------------------------

    // Run on page load
    $(document).ready(function() {
        // Initial scan
        setTimeout( scanAndInject, 500 );

        // Watch for new trigger items being added via MutationObserver
        var observer = new MutationObserver(function( mutations ) {
            mutations.forEach(function( mutation ) {
                if ( mutation.addedNodes.length ) {
                    setTimeout( scanAndInject, 300 );
                }
            });
        });

        var $editor = $('#automatorwp-automation-editor, .automatorwp-automation-items, #poststuff');
        if ( $editor.length ) {
            observer.observe( $editor[0], { childList: true, subtree: true } );
        }
    });

    // Also run when AutomatorWP triggers events (if available)
    $(document).on('automatorwp_trigger_added automatorwp_trigger_updated automatorwp_automation_item_added', function() {
        setTimeout( scanAndInject, 300 );
    });

})(jQuery);
