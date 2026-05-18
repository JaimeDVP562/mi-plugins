/**
 * ShortLinks Pro - Auto-Create Links Admin JS
 *
 * Handles the dynamic behavior of the Auto-Create Links section in Settings:
 * - Shows/hides per-post-type settings when checkboxes are toggled
 * - AJAX Create/Update/Delete buttons with batch processing
 * - Recursive AJAX calls with progress indicator (e.g. "Processing... 150/1000")
 *
 * @since 1.2.0
 */
(function($) {
    'use strict';

    /**
     * Let's put the per-post-type settings panels exactly where they belong in the DOM.
     * We want them right after our handy post type checkbox list!
     */
    function initAutoCreateSettings() {

        var $container = $('#shortlinkspro-auto-create-links-settings');

        if( ! $container.length ) {
            return;
        }

        // Find the auto_create_post_types multicheck field
        var $postTypesField = $('[id$="auto_create_post_types"]').closest('.cmb-row');

        if( ! $postTypesField.length ) {
            // Try alternate selector
            $postTypesField = $('input[name="auto_create_post_types[]"]').closest('.cmb-row');
        }

        if( ! $postTypesField.length ) {
            return;
        }

        // Move the container after the post types field
        $container.insertAfter( $postTypesField.closest('.cmb2-metabox') );
        $container.show();

        // Show or hide the specific settings depending on what the user checked
        togglePostTypeSettings();

        // Listen for when the user clicks those checkboxes so we can update things on the fly
        $(document).on( 'change', 'input[name="auto_create_post_types[]"]', function() {
            togglePostTypeSettings();
        });

    }

    /**
     * Toggle the visibility of per-post-type settings panels
     */
    function togglePostTypeSettings() {

        // Hide all the panels first just to clear the board
        $('.shortlinkspro-auto-create-pt-settings').hide();

        // Now, find out which post types are checked and only show those
        $('input[name="auto_create_post_types[]"]:checked').each(function() {
            var postType = $(this).val();
            $('.shortlinkspro-auto-create-pt-settings[data-post-type="' + postType + '"]').show();
        });

    }

    /**
     * Collect field values from a post-type settings panel
     *
     * @param {jQuery} $wrapper The post-type settings panel
     * @return {Object}
     */
    function collectFieldValues( $wrapper ) {

        var prefix = $wrapper.find('.shortlinkspro-auto-create-prefix').val() || '';
        var category = $wrapper.find('.shortlinkspro-auto-create-category').val() || '';
        var tags = $wrapper.find('.shortlinkspro-auto-create-tags').val() || [];
        var redirectType = $wrapper.find('.shortlinkspro-auto-create-redirect').val() || '307';

        var linkOptions = [];
        $wrapper.find('input[name^="auto_create_link_options_"]:checked').each(function() {
            linkOptions.push( $(this).val() );
        });

        return {
            prefix: prefix,
            category: category,
            tags: tags,
            redirect_type: redirectType,
            link_options: linkOptions
        };
    }

    /**
     * Determine the AJAX action name from the button action
     *
     * @param {string} action
     * @return {string}
     */
    function getAjaxAction( action ) {

        switch( action ) {
            case 'create':
                return 'shortlinkspro_auto_create_links';
            case 'update':
                return 'shortlinkspro_auto_update_links';
            case 'delete':
                return 'shortlinkspro_auto_delete_links';
            default:
                return '';
        }
    }

    /**
     * Run a single batch of the AJAX operation and recursively call the next
     * batch until done.
     *
     * @param {Object} params
     * @param {string} params.ajaxAction     The WP AJAX action name
     * @param {string} params.postType       The post type being processed
     * @param {Object} params.fieldValues    Collected field values
     * @param {int}    params.offset         The cumulative offset (processed so far)
     * @param {jQuery} params.$wrapper       The settings panel wrapper
     * @param {jQuery} params.$status        The status element
     * @param {string} params.actionLabel    Human-readable label (created/updated/deleted)
     */
    function runBatch( params ) {

        var data = {
            action: params.ajaxAction,
            nonce: shortlinkspro_auto_create.nonce,
            post_type: params.postType,
            prefix: params.fieldValues.prefix,
            category: params.fieldValues.category,
            tags: params.fieldValues.tags,
            redirect_type: params.fieldValues.redirect_type,
            link_options: params.fieldValues.link_options,
            offset: params.offset
        };

        $.ajax({
            url: shortlinkspro_auto_create.ajaxurl,
            type: 'POST',
            data: data,
            success: function( response ) {

                if( ! response.success ) {
                    params.$status.html(
                        '<span style="color: #dc3232;">✗ ' + response.data + '</span>'
                    );
                    params.$wrapper.find('.shortlinkspro-auto-create-btn').prop('disabled', false);
                    return;
                }

                var d = response.data;

                if( d.done ) {
                    // All batches completed
                    var finalMsg = d.processed + ' ' + params.actionLabel;
                    params.$status.html(
                        '<span style="color: #46b450;">✓ ' + finalMsg + '</span>'
                    );

                    // Reload after a short delay to update button states
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    // Show progress and run next batch
                    var progressMsg = shortlinkspro_auto_create.processing_text
                        + ' ' + d.processed + '/' + d.total;

                    params.$status.html(
                        '<span class="spinner is-active" style="float: none; margin: 0;"></span> '
                        + progressMsg
                    );

                    // Recursive call with updated offset
                    params.offset = d.processed;
                    runBatch( params );
                }

            },
            error: function() {
                params.$status.html(
                    '<span style="color: #dc3232;">✗ ' + shortlinkspro_auto_create.error_text + '</span>'
                );
                params.$wrapper.find('.shortlinkspro-auto-create-btn').prop('disabled', false);
            }
        });
    }

    /**
     * Handle AJAX button clicks (Create, Update, Delete) with batch processing
     */
    function initAutoCreateButtons() {

        $(document).on( 'click', '.shortlinkspro-auto-create-btn', function(e) {
            e.preventDefault();

            var $button = $(this);
            var action = $button.data('action');
            var $wrapper = $button.closest('.shortlinkspro-auto-create-pt-settings');
            var postType = $wrapper.data('post-type');
            var $status = $wrapper.find('.shortlinkspro-auto-create-status');

            // Confirmation for delete
            if( action === 'delete' ) {
                if( ! confirm( shortlinkspro_auto_create.confirm_delete_text ) ) {
                    return;
                }
            }

            // Collect current field values
            var fieldValues = collectFieldValues( $wrapper );

            // Determine AJAX action and label
            var ajaxAction = getAjaxAction( action );
            var actionLabel = '';

            switch( action ) {
                case 'create':
                    actionLabel = shortlinkspro_auto_create.created_text || 'links created';
                    break;
                case 'update':
                    actionLabel = shortlinkspro_auto_create.updated_text || 'links updated';
                    break;
                case 'delete':
                    actionLabel = shortlinkspro_auto_create.deleted_text || 'links deleted';
                    break;
            }

            // Disable all buttons in this section
            $wrapper.find('.shortlinkspro-auto-create-btn').prop('disabled', true);
            $status.html(
                '<span class="spinner is-active" style="float: none; margin: 0;"></span> '
                + shortlinkspro_auto_create.processing_text
            );

            // Start the first batch
            runBatch({
                ajaxAction: ajaxAction,
                postType: postType,
                fieldValues: fieldValues,
                offset: 0,
                $wrapper: $wrapper,
                $status: $status,
                actionLabel: actionLabel
            });

        });

    }

    /**
     * When the page is locked, loaded and ready to go!
     */
    $(document).ready(function() {
        initAutoCreateSettings();
        initAutoCreateButtons();
    });

})(jQuery);
