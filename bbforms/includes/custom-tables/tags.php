<?php
/**
 * Tags
 *
 * @package     BBForms\Custom_Tables\Tags
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Custom Table Labels
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_tags_labels() {

    return array(
        'singular' => __( 'Tag', 'bbforms' ),
        'plural' => __( 'Tags', 'bbforms' ),
        'labels' => array(
            'list_menu_title' => __( 'Tags', 'bbforms' ),
        ),
    );

}
add_filter( 'ct_bbforms_tags_labels', 'bbforms_tags_labels' );

/**
 * Parse query args for fields
 *
 * @since 1.0.0
 *
 * @param string $where
 * @param CT_Query $ct_query
 *
 * @return string
 */
function bbforms_tags_query_where( $where, $ct_query ) {

    global $ct_table;

    if( $ct_table->name !== 'bbforms_tags' ) {
        return $where;
    }

    // Shorthand
    $qv = $ct_query->query_vars;

    // Name
    $where .= bbforms_custom_table_where( $qv, 'name', 'string' );

    // Slug
    $where .= bbforms_custom_table_where( $qv, 'slug', 'string' );

    // Description
    $where .= bbforms_custom_table_where( $qv, 'description', 'string' );

    return $where;
}
add_filter( 'ct_query_where', 'bbforms_tags_query_where', 10, 2 );

/**
 * Define the search fields
 *
 * @since 1.0.0
 *
 * @param array $search_fields
 *
 * @return array
 */
function bbforms_tags_search_fields( $search_fields ) {

    $search_fields[] = 'name';
    $search_fields[] = 'slug';
    $search_fields[] = 'description';

    return $search_fields;

}
add_filter( 'ct_query_bbforms_tags_search_fields', 'bbforms_tags_search_fields' );

/**
 * Columns in list view
 *
 * @since 1.0.0
 *
 * @param array $columns
 *
 * @return array
 */
function bbforms_tags_manage_columns( $columns = array() ) {

    $columns['name']        = __( 'Name', 'bbforms' );
    $columns['slug']        = __( 'Slug', 'bbforms' );
    $columns['description'] = __( 'Description', 'bbforms' );
    $columns['count']       = __( 'Count', 'bbforms' );

    return $columns;
}
add_filter( 'manage_bbforms_tags_columns', 'bbforms_tags_manage_columns' );

/**
 * Sortable columns for list view
 *
 * @since 1.0.0
 *
 * @param array $sortable_columns
 *
 * @return array
 */
function bbforms_tags_manage_sortable_columns( $sortable_columns ) {

    $sortable_columns['name']               = array( 'name', false );
    $sortable_columns['slug']               = array( 'slug', false );
    $sortable_columns['description']        = array( 'description', false );

    return $sortable_columns;

}
add_filter( 'manage_bbforms_tags_sortable_columns', 'bbforms_tags_manage_sortable_columns' );

/**
 * Columns rendering for list view
 *
 * @since  1.0.0
 *
 * @param string $column_name
 * @param integer $object_id
 */
function bbforms_tags_manage_custom_column(  $column_name, $object_id ) {

    // Setup vars
    $tag = ct_get_object( $object_id );

    switch( $column_name ) {
        case 'name':
            $name = $tag->name;
            if( empty( $name ) ) {
                $name = __( '(no name)', 'bbforms' );
            }
            ?>
            <strong><a href="<?php echo esc_attr( ct_get_edit_link( 'bbforms_tags', $tag->id ) ); ?>"><?php echo esc_html( $name ); ?></a></strong>
            <?php

            break;
        case 'slug':
            echo esc_html( $tag->slug );
            break;
        case 'description':
            echo esc_html( $tag->description );
            break;
        case 'count':
            ct_setup_table( 'bbforms_tags_relationships' );
            $count = ct_get_term_relationships_count( $tag->id );
            ct_reset_setup_table();

            if( $count > 0 ) {
                $title = __( 'View forms with this tag', 'bbforms' );

                $url = ct_get_list_link( 'bbforms_forms' );
                $url = add_query_arg( array( 'tag_id' => $tag->id ), $url );
                $url = add_query_arg( array( 'from' => 'tags' ), $url );
                ?>
                <a href="<?php echo esc_attr( $url ); ?>" title="<?php echo esc_attr( $title ); ?>"><?php echo esc_html( $count ); ?></a>
                <?php
            } else {
                echo '<span>' . esc_html( $count ) . '</span>';
            }
            break;
    }
}
add_action( 'manage_bbforms_tags_custom_column', 'bbforms_tags_manage_custom_column', 10, 2 );

/**
 * Default data when creating a new item (similar to WP auto draft) see ct_insert_object()
 *
 * @since  1.0.0
 *
 * @param array $default_data
 *
 * @return array
 */
function bbforms_tags_default_data( $default_data = array() ) {

    $default_data['name'] = '';
    $default_data['slug'] = '';
    $default_data['description'] = '';

    return $default_data;
}
add_filter( 'ct_bbforms_tags_default_data', 'bbforms_tags_default_data' );

/**
 * CMB2 Meta boxes
 *
 * @since  1.0.0
 */
function bbforms_tags_meta_boxes() {

    // Title
    bbforms_add_meta_box(
        'bbforms-tag',
        __( 'Link Category', 'bbforms' ),
        'bbforms_tags',
        array(
            'name' => array(
                'name' => __('Name', 'bbforms'),
                'desc' => __('The tag name.', 'bbforms'),
                'type' => 'text',
                'attributes' => array(
                    'required' => true,
                ),
            ),
            'slug' => array(
                'name' => __('Slug', 'bbforms'),
                'desc' => __('The URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'bbforms'),
                'type' => 'text',
                'attributes' => array(
                    'required' => true,
                ),
            ),
            'description' => array(
                'name' => __('Description', 'bbforms'),
                'desc' => __('Internal description for this tag.', 'bbforms'),
                'type' => 'textarea',
            ),
        ),
        array(
            'priority' => 'high',
        )
    );
}

add_action( 'cmb2_init', 'bbforms_tags_meta_boxes' );

/**
 * Add meta box (used in list view)
 *
 * @since  1.0.0
 */
function bbforms_tags_get_add_meta_box() {

    global $ct_object;

    $object_id = $ct_object ? $ct_object->id : null;

    // Setup the CMB2 form
    $cmb = new CMB2( array(
        'id'        => 'bbforms_tags_form',
        'object_types' => array( 'bbforms_tags' ),
        'classes'   => 'bbforms-form bbforms-tags-form',
        'hookup'    => false,
    ), $object_id );

    // Name
    $cmb->add_field( array(
        'id' => 'name',
        'name' => __('Name', 'bbforms'),
        'desc' => __('The tag name.', 'bbforms'),
        'type' => 'text',
        'attributes' => array(
            'required' => true,
        ),
    ) );

    // Slug
    $cmb->add_field( array(
        'id' => 'slug',
        'name' => __('Slug', 'bbforms'),
        'desc' => __('The URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'bbforms'),
        'type' => 'text',
        'attributes' => array(
            'required' => true,
        ),
    ) );

    // Description
    $cmb->add_field( array(
        'id' => 'description',
        'name' => __('Description', 'bbforms'),
        'desc' => __('Internal description for this tag.', 'bbforms'),
        'type' => 'textarea',
    ) );

    return $cmb;

}

/**
 * Render the add form in list view
 *
 * @since  1.0.0
 */
function bbforms_tags_add_form() {

    // Get the add meta box
    $cmb = bbforms_tags_get_add_meta_box();

    // Render the form
    CMB2_Hookup::enqueue_cmb_css();
    CMB2_Hookup::enqueue_cmb_js();
    $cmb->show_form();

}
add_action( 'ct_render_bbforms_tags_add_form', 'bbforms_tags_add_form' );
add_action( 'ct_render_bbforms_tags_edit_form', 'bbforms_tags_add_form' );

/**
 * Filters slashed data just before it is inserted into the database.
 *
 * @since 1.0.0
 *
 * @param array $object_data    An array with new object data.
 * @param array $original_object_data An array with the original object data.
 *
 * @return array
 */
function bbforms_tags_object_data( $object_data, $original_object_data ) {

    global $ct_table;

    if( $ct_table->name !== 'bbforms_tags' ) {
        return $object_data;
    }

    // Ensure slug
    if( ! isset( $object_data['slug'] ) ) {
        $object_data['slug'] = $object_data['name'];
    } else if( empty( $object_data['slug'] ) ) {
        $object_data['slug'] = $object_data['name'];
    }

    // Sanitize slug
    $object_data['slug'] = sanitize_title_with_dashes( $object_data['slug'] );

    // Get the add meta box
    $cmb = bbforms_tags_get_add_meta_box();

    return ct_cmb_get_sanitized_data( $cmb, $object_data );

}
add_filter( 'ct_insert_object_data', 'bbforms_tags_object_data', 10, 2 );