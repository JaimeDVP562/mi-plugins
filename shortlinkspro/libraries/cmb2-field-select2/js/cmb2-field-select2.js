(function( $ ) {

    // Selector Control
    $('.cmb2-select2').each(function() { cmb2_select2_init( $(this) ); });

    // On add a new group row, reinitialize fields
    $('body').on('cmb2_add_row', '.cmb-repeatable-group', function( e, row, cmb ) {

        // Remove Select2 element
        row.find('.select2').remove();

        // Find all Select2 elements
        var select2_elements = row.find('.select2-hidden-accessible');

        // Reset Select2 data on options
        select2_elements.find('optgroup, option')
            .removeAttr('id')
            .removeAttr('data-select2-id'); // For options and group options, select2 assigns this attribute as id

        // Reset Select2 data on the input element
        select2_elements
            .removeClass('select2-hidden-accessible')
            .removeAttr('id')
            .removeAttr('data-select2-id'); // For fields without id, select2 assigns this attribute as id

        row.find('.cmb2-select2').each(function() { cmb2_select2_init( $(this) ); })
    });

})(jQuery);

/**
 * Helper function to initialize select2
 *
 * @since 1.0.0
 *
 * @param {Object} $this
 */
function cmb2_select2_init( $this ) {

    // Prevent load select2 on widgets lists
    if( $this.closest('#available-widgets').length ) {
        return;
    }

    var from = $this.data('from');

    if( from !== undefined && from.length ) {
        cmb2_select2_ajax_init( $this );
        return;
    }

    var select2_args = {
        theme: 'default cmb2-select2',
        placeholder: ( $this.data('placeholder') ? $this.data('placeholder') : '' ),
        allowClear: true,
        multiple: ( $this[0].hasAttribute('multiple') ),
        // Tags
        createTag: cmb2_select2_create_tag,
    };

    var tags = $this.data('tags');

    if( tags !== undefined && tags.length ) {
        // select2 with tags
        select2_args = cmb2_select2_add_tag_args( select2_args );
    }

    var country_flags = $this.data('country-flags');

    if( country_flags !== undefined && country_flags ) {
        // select2 with tags
        select2_args = cmb2_select2_add_country_flags_args( select2_args );
    }

    $this.cmb2_select2( select2_args );

}

/**
 * Helper function to initialize select2 ajax selector on fields
 *
 * @since 1.0.0
 *
 * @param {Object} $this
 */
function cmb2_select2_ajax_init( $this ) {

    var select2_args = {
        ajax: {
            url: cmb2_field_select2.ajaxurl,
            dataType: 'json',
            delay: 250,
            type: 'POST',
            data: function( params ) {
                return {
                    q: params.term,
                    page: params.page || 1,
                    nonce: cmb2_field_select2.nonce,
                    action: $this.data('action'),
                    from: $this.data('from'),
                    field_id: $this.attr('id'),
                    // posts
                    post_type: $this.data('post-type'),
                    post_type_not_in: $this.data('post-type-not-in'),
                    post_status: $this.data('post-status'),
                    post_status_not_in: $this.data('post-status-not-in'),
                    // users
                    // Nothing
                    // terms
                    taxonomy: $this.data('taxonomy'),
                    // ct
                    table: $this.data('table'),
                    id_field: $this.data('id-field'),
                    text_field: $this.data('text-field'),
                    label_field: $this.data('label-field'),
                };
            },
            processResults: cmb2_select2_ajax_process_results,
            cache: true
        },
        escapeMarkup: function ( markup ) { return markup; },
        templateResult: cmb2_select2_template_result,
        theme: 'default cmb2-select2',
        placeholder: ( $this.data('placeholder') ? $this.data('placeholder') : '' ),
        allowClear: true,
        multiple: ( $this[0].hasAttribute('multiple') ),
        // Tags
        createTag: cmb2_select2_create_tag,
    };

    var tags = $this.data('tags');

    if( tags !== undefined && tags.length ) {
        // select2 with tags
        select2_args = cmb2_select2_add_tag_args( select2_args );
    }

    $this.cmb2_select2( select2_args );

}

function cmb2_select2_add_tag_args( select2_args ) {
    select2_args.tags = true;
    select2_args.tokenSeparators = [','];
    //select2_args.createTag = cmb2_select2_create_tag;

    return select2_args
}

function cmb2_select2_create_tag( params ) {
    var term = params.term.trim();

    if (term === '') {
        return null;
    }

    return {
        id: term,
        text: term,
        label: "Create \"" + term + "\"",
        new_tag: true // add additional parameters
    }

}

function cmb2_select2_add_country_flags_args( select2_args ) {

    select2_args.escapeMarkup = function ( markup ) { return markup; };
    select2_args.templateResult = cmb2_select_country_flags_template_result;
    select2_args.templateSelection = cmb2_select_country_flags_template_result;

    return select2_args;
}

function cmb2_select_country_flags_template_result( item ) {
    return (  item.id !== undefined ? '<span class="flag flag-' + item.id.toLowerCase() + '"></span> ' : '' ) + item.text;
}

/**
 * Custom results processing on select2
 *
 * @since 1.0.0
 *
 * @param {Object} response
 * @param {Object} params
 *
 * @return {string}
 */
function cmb2_select2_ajax_process_results( response, params ) {

    if( response === null ) {
        return { results: [] };
    }

    var formatted_results = [];

    // Paginated responses will come with results and more_results keys
    var results = ( response.data.results !== undefined ? response.data.results : response.data );

    results.forEach( function( item ) {

        // Extend select2 keys (id and text) with given keys (id and text)
        formatted_results.push( jQuery.extend({
            id: item.id,
            text: item.text,
            label: item.label,
        }, item ) );

    } );

    return {
        results: formatted_results,
        pagination: {
            more: ( response.data.more_results !== undefined ? response.data.more_results : false )
        }
    };

}

/**
 * Custom formatting for posts on select2
 *
 * @since 1.0.0
 *
 * @param {Object} item
 *
 * @return {string}
 */
function cmb2_select2_template_result( item ) {

    var is_new = false;
    if( item.new_tag === true ) {
        is_new = true;
    }

    if( item.label !== undefined ) {

        return item.text
            + '<span class="result-description">'
            + item.label + ( ! isNaN( item.id ) && ! is_new ?  '<span class="align-right">' + '#' + item.id + '</span>' : '' )
            + '</span>';
    }

    return item.text + ( ! isNaN( item.id ) && ! is_new ? '<span class="result-description align-right">#' + item.id + '</span></span>': '' );

}