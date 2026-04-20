<?php
/**
 * Forms
 *
 * @package     BBForms\Custom_Tables\Forms
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
function bbforms_forms_labels() {

    return array(
        'singular' => __( 'Form', 'bbforms' ),
        'plural' => __( 'Forms', 'bbforms' ),
        'labels' => array(
            'list_menu_title' => __( 'Forms', 'bbforms' ),
            'add_menu_title' => __( 'Add New', 'bbforms' ),
        ),
    );

}
add_filter( 'ct_bbforms_forms_labels', 'bbforms_forms_labels' );

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
function bbforms_forms_query_where( $where, $ct_query ) {

    global $ct_table;

    if( $ct_table->name !== 'bbforms_forms' ) {
        return $where;
    }

    // Shorthand
    $qv = $ct_query->query_vars;

    // Title
    $where .= bbforms_custom_table_where( $qv, 'title', 'string' );

    // URL
    $where .= bbforms_custom_table_where( $qv, 'url', 'string' );

    // Slug
    $where .= bbforms_custom_table_where( $qv, 'slug', 'string' );

    // Link Category ID
    if( isset( $qv['category_id'] ) ) {
        $category_id = absint( $qv['category_id'] );
        $where .= " AND fc.category_id = {$category_id}";
    }

    // Link Tag ID
    if( isset( $qv['tag_id'] ) ) {
        $tag_id = absint( $qv['tag_id'] );
        $where .= " AND ft.tag_id = {$tag_id}";
    }

    return $where;
}
add_filter( 'ct_query_where', 'bbforms_forms_query_where', 10, 2 );

/**
 * Parse query args for user earnings to be applied on JOIN clause
 *
 * @since   1.2.8
 *
 * @param string    $join
 * @param CT_Query  $ct_query
 *
 * @return string
 */
function bbforms_forms_query_join( $join, $ct_query ) {

    global $ct_table, $ct_registered_tables;

    if( $ct_table->name !== 'bbforms_forms' ) {
        return $join;
    }

    $table_name = $ct_table->db->table_name;

    // Shorthand
    $qv = $ct_query->query_vars;

    // Parent link category
    if( isset( $qv['category_id'] ) ) {
        $relationship_table = $ct_registered_tables['bbforms_categories_relationships'];
        $join .= " LEFT JOIN {$relationship_table->db->table_name} fc ON ( fc.form_id = {$table_name}.id )";
    }

    // Parent link tag
    if( isset( $qv['tag_id'] ) ) {
        $relationship_table = $ct_registered_tables['bbforms_tags_relationships'];
        $join .= " LEFT JOIN {$relationship_table->db->table_name} ft ON ( ft.form_id = {$table_name}.id )";
    }

    return $join;

}
add_filter( 'ct_query_join', 'bbforms_forms_query_join', 10, 2 );

/**
 * Define the search fields
 *
 * @since 1.0.0
 *
 * @param array $search_fields
 *
 * @return array
 */
function bbforms_forms_search_fields( $search_fields ) {

    $search_fields[] = 'title';
    $search_fields[] = 'form';
    $search_fields[] = 'actions';
    $search_fields[] = 'options';

    return $search_fields;

}
add_filter( 'ct_query_bbforms_forms_search_fields', 'bbforms_forms_search_fields' );

/**
 * Columns in list view
 *
 * @since 1.0.0
 *
 * @param array $columns
 *
 * @return array
 */
function bbforms_forms_manage_columns( $columns = array() ) {

    $columns['title']       = __( 'Title', 'bbforms' );
    $columns['category']    = __( 'Category', 'bbforms' );
    $columns['tags']        = __( 'Tags', 'bbforms' );
    $columns['submissions'] = __( 'Submissions', 'bbforms' );
    $columns['shortcode']   = __( 'Shortcode', 'bbforms' );

    return $columns;
}
add_filter( 'manage_bbforms_forms_columns', 'bbforms_forms_manage_columns' );

/**
 * Sortable columns for list view
 *
 * @since 1.0.0
 *
 * @param array $sortable_columns
 *
 * @return array
 */
function bbforms_forms_manage_sortable_columns( $sortable_columns ) {

    $sortable_columns['title']              = array( 'title', false );
    $sortable_columns['shortcode']          = array( 'id', false );

    return $sortable_columns;

}
add_filter( 'manage_bbforms_forms_sortable_columns', 'bbforms_forms_manage_sortable_columns' );

/**
 * Row actions.
 *
 * @since 1.0.0
 *
 * @param array $actions An array of row action links. Defaults are
 *                         'Edit', 'Quick Edit', 'Restore, 'Trash',
 *                         'Delete Permanently', 'Preview', and 'View'.
 * @param stdClass $object The item object.
 *
 * @return array
 */
function bbforms_forms_row_actions( $actions, $object ) {

    global $ct_table;

    // List link
    $url = $ct_table->views->list->get_link();
    $url = add_query_arg( array( 'id' => $object->id ), $url );

    if ( current_user_can( $ct_table->cap->edit_item, $object->id ) ) {
        $duplicate_url = add_query_arg( array( 'bbforms-action' => 'duplicate_form' ), $url );
        $duplicate_url = add_query_arg( '_wpnonce', wp_create_nonce( 'bbforms_duplicate_form_' . $object->id ), $duplicate_url );

        $actions['export'] = sprintf(
            '<a href="%s" id="bbforms-form-export-%s" class="bbforms-form-export" aria-label="%s">%s</a><div id="bbforms-form-export-value-%s" class="bbforms-form-export-value" style="display: none !important;">%s</div>',
            $duplicate_url,
            esc_attr( $object->id ),
            esc_attr( __( 'Export', 'bbforms' ) ),
            __( 'Export', 'bbforms' ),
            esc_attr( $object->id ),
            bbforms_get_form_content_to_export( $object )
        );

        $actions['duplicate'] = sprintf(
            '<a href="%s" class="bbforms-duplicate-form" aria-label="%s">%s</a>',
            $duplicate_url,
            esc_attr( __( 'Duplicate', 'bbforms' ) ),
            __( 'Duplicate', 'bbforms' )
        );
    }

    /**
     * Row actions, after duplicate (for other plugins).
     *
     * @since 1.0.0
     *
     * @param array $actions An array of row action links. Defaults are
     *                         'Edit', 'Quick Edit', 'Restore, 'Trash',
     *                         'Delete Permanently', 'Preview', and 'View'.
     * @param stdClass $object The item object.
     *
     * @return array
     */
    $actions = apply_filters( 'bbforms_forms_row_actions_after_duplicate', $actions, $object );

    // Check delete cap from submissions
    $submissions_table = ct_setup_table( 'bbforms_submissions' );

    if ( current_user_can( $submissions_table->cap->delete_items ) ) {
        $reset_url = add_query_arg( array( 'bbforms-action' => 'reset_submissions' ), $url );
        $reset_url = add_query_arg( '_wpnonce', wp_create_nonce( 'bbforms_reset_submissions_' . $object->id ), $reset_url );

        $actions['reset_submissions'] = sprintf(
            '<a href="%s" class="bbforms-reset-form-submissions" onclick="%s" aria-label="%s">%s</a>',
            $reset_url,
            "return confirm('" .
            esc_attr( __( "Are you sure you want to reset submissions for this form?\\n\\nClick \\'Cancel\\' to go back, \\'OK\\' to confirm the reset.", 'bbforms' ) ) .
            "');",
            esc_attr( __( 'Reset Submissions', 'bbforms' ) ),
            __( 'Reset Submissions', 'bbforms' )
        );
    }

    ct_reset_setup_table();

    // Override delete
    if ( current_user_can( $ct_table->cap->delete_item, $object->id ) ) {
        unset( $actions['delete'] );
        $actions['delete'] = sprintf(
            '<a href="%s" class="submitdelete" onclick="%s" aria-label="%s">%s</a>',
            ct_get_delete_link( $ct_table->name, $object->id ),
            "return confirm('" .
            esc_attr( __( "Are you sure you want to delete this form?\\n\\nClick \\'Cancel\\' to go back, \\'OK\\' to confirm deletion.", 'bbforms' ) ) .
            "');",
            esc_attr( __( 'Delete', 'bbforms' ) ),
            __( 'Delete', 'bbforms' )
        );
    }

    return $actions;

}
add_filter( 'bbforms_forms_row_actions', 'bbforms_forms_row_actions', 10, 2 );

/**
 * Process duplicate form action
 *
 * @since 1.0.0
 *
 * @param array $request
 */
function bbforms_action_duplicate_form( $request ) {

    if( isset( $request['id'] ) ) {

        $form_id = absint( $request['id'] );

        // Check id
        if( $form_id !== 0 ) {

            $nonce = $request['_wpnonce'];

            // Check nonce
            if( wp_verify_nonce( sanitize_text_field( wp_unslash ( $nonce ) ), 'bbforms_duplicate_form_' . $form_id ) ) {

                // Setup table
                $ct_table = ct_setup_table( 'bbforms_forms' );

                // Check user capabilities
                if ( current_user_can( $ct_table->cap->edit_item, $form_id ) ) {
                    $form = ct_get_object( $form_id, ARRAY_A );

                    unset( $form['id'] );

                    $new_form_id = ct_insert_object( $form );

                    if( $new_form_id ) {
                        $new_form_id = absint( $new_form_id );

                        // Clone metas
                        bbforms_clone_item_metas( 'bbforms_forms', $form_id, $new_form_id );

                        // TODO: Clone categories and tags
                    }

                }

                ct_reset_setup_table();

                // Redirect to the same URL but without action parameters
                wp_redirect( remove_query_arg( array( 'id', 'bbforms-action', '_wpnonce' ) ) );
                exit;

            }

        }

    }

}
add_action( 'bbforms_action_duplicate_form', 'bbforms_action_duplicate_form' );

/**
 * Process reset submissions action
 *
 * @since 1.0.0
 *
 * @param array $request
 */
function bbforms_action_reset_submissions( $request ) {

    global $wpdb;

    if( isset( $request['id'] ) ) {

        $form_id = absint( $request['id'] );

        // Check id
        if( $form_id !== 0 ) {

            $nonce = $request['_wpnonce'];

            // Check nonce
            if( wp_verify_nonce( sanitize_text_field( wp_unslash ( $nonce ) ), 'bbforms_reset_submissions_' . $form_id ) ) {

                // Setup table
                $ct_table = ct_setup_table( 'bbforms_submissions' );

                // Check user capabilities
                if ( current_user_can( $ct_table->cap->delete_items ) ) {
                    // Reset the form submissions
                    bbforms_reset_submissions( $form_id );
                }

                ct_reset_setup_table();

                // Redirect to the same URL but without action parameters
                wp_redirect( remove_query_arg( array( 'id', 'bbforms-action', '_wpnonce' ) ) );
                exit;

            }

        }

    }

}
add_action( 'bbforms_action_reset_submissions', 'bbforms_action_reset_submissions' );

function bbforms_forms_get_views( $views ) {

    $category_id = ( isset( $_GET['category_id'] ) ) ? absint( $_GET['category_id'] ) : 0;
    $tag_id = ( isset( $_GET['tag_id'] ) ) ? absint( $_GET['tag_id'] ) : 0;
    $from = ( isset( $_GET['from'] ) ) ? sanitize_text_field( $_GET['from'] ) : '';

    ?>
    <?php if( isset( $_GET['category_id'] ) || isset( $_GET['tag_id'] ) ) : ?>
        <div class="bbforms-forms-filtered-by">
            <?php
            if( isset( $_GET['category_id'] ) ) {
                /* translators: %s: Category link. */
                echo sprintf( esc_html__( "Filtered by category %s", 'bbforms' ), wp_kses_post( bbforms_get_category_edit_link( $category_id ) ) );
            } else {
                /* translators: %s: Tag link. */
                echo sprintf( esc_html__( "Filtered by tag %s", 'bbforms' ), wp_kses_post( bbforms_get_tag_edit_link( $tag_id ) ) );
            }

            echo ' | ';

            if( $from === 'forms' ) {
                echo sprintf( '<a href="%s">&laquo %s</a>',
                    esc_attr( ct_get_list_link( 'bbforms_forms' ) ),
                    esc_html__( "Remove filter", 'bbforms' )
                );
            } else if( $from === 'categories' ) {
                echo sprintf( '<a href="%s">&laquo %s</a>',
                    esc_attr( ct_get_list_link( 'bbforms_categories' ) ),
                    esc_html__( "Back to Categories", 'bbforms' )
                );
            }  else if( $from === 'tags' ) {
                echo sprintf( '<a href="%s">&laquo %s</a>',
                    esc_attr( ct_get_list_link( 'bbforms_tags' ) ),
                    esc_html__( "Back to Tags", 'bbforms' )
                );
            }
            ?>
        </div>
    <?php endif; ?>

    <?php

    return $views;

}
add_filter( 'bbforms_forms_get_views', 'bbforms_forms_get_views' );

/**
 * Columns rendering for list view
 *
 * @since  1.0.0
 *
 * @param string $column_name
 * @param integer $object_id
 */
function bbforms_forms_manage_custom_column(  $column_name, $object_id ) {

    // Setup vars
    $form = ct_get_object( $object_id );

    switch( $column_name ) {
        case 'title':
            $title = $form->title;
            if( empty( $title ) ) {
                $title = __( '(no title)', 'bbforms' );
            }
            ?>
            <strong><a href="<?php echo esc_attr( ct_get_edit_link( 'bbforms_forms', $form->id ) ); ?>"><?php echo esc_html( $title ); ?></a></strong>
            <?php

            break;
        case 'category':
            $title = __( 'View forms with this category', 'bbforms' );

            $list_url = ct_get_list_link( 'bbforms_forms' );
            $list_url = add_query_arg( array( 'from' => 'forms' ), $list_url );

            ct_setup_table( 'bbforms_categories_relationships' );
            $categories = ct_get_object_terms( $form->id );
            ct_reset_setup_table();

            if( $categories !== null ) {

                if( ! is_array( $categories ) ) {
                    $categories = array( $categories );
                }

                foreach( $categories as $category ) {
                    $url = add_query_arg( array( 'category_id' => $category->id ), $list_url );

                    ?>
                    <a href="<?php echo esc_attr( $url ); ?>" title="<?php echo esc_attr( $title ); ?>" class="bbforms-form-category"><?php echo esc_html( $category->name ); ?></a>
                    <?php
                }
            }
            break;
        case 'tags':
            $title = __( 'View forms with this tag', 'bbforms' );

            $list_url = ct_get_list_link( 'bbforms_forms' );
            $list_url = add_query_arg( array( 'from' => 'forms' ), $list_url );

            ct_setup_table( 'bbforms_tags_relationships' );
            $tags = ct_get_object_terms( $form->id );
            ct_reset_setup_table();

            if( $tags !== null ) {

                if( ! is_array( $tags ) ) {
                    $tags = array( $tags );
                }

                foreach( $tags as $tag ) {
                    $url = add_query_arg( array( 'tag_id' => $tag->id ), $list_url );

                    ?>
                    <a href="<?php echo esc_attr( $url ); ?>" title="<?php echo esc_attr( $title ); ?>" class="bbforms-form-tag"><?php echo esc_html( $tag->name ); ?></a>
                    <?php
                }
            }
            break;
        case 'submissions':
            $url = ct_get_list_link( 'bbforms_submissions' );
            $url = add_query_arg( array( 'form_id' => absint( $form->id ) ), $url );
            $url = add_query_arg( array( 'from' => 'forms' ), $url );
            $count = bbforms_get_submissions_count( $form->id );
            // translators: %d: Number of submissions
            $title = sprintf( _n( '%d Submission', '%d Submissions', $count, 'bbforms' ), $count );
            ?>
            <a href="<?php echo esc_attr( $url ); ?>" title="<?php echo esc_attr( $title ); ?>"><?php echo esc_html( $count ); ?></a>
            <?php
            break;
        case 'shortcode':
            $shortcode = '[bbforms id="' . $form->id . '"]';
            ?>
            <input type="text" value="<?php echo esc_attr( $shortcode ); ?>" onclick="this.focus(); this.select()" readonly>
            <?php
            break;
    }
}
add_action( 'manage_bbforms_forms_custom_column', 'bbforms_forms_manage_custom_column', 10, 2 );

/**
 * Default data when creating a new item (similar to WP auto draft) see ct_insert_object()
 *
 * @since  1.0.0
 *
 * @param array $default_data
 *
 * @return array
 */
function bbforms_forms_default_data( $default_data = array() ) {

    $template_name = ( isset( $_GET['template'] ) ) ? sanitize_key( $_GET['template'] ) : '';
    $templates = bbforms_get_form_templates();


    if( ! isset( $templates[ $template_name ] ) ) {
        $template_name = 'blank';
    }

    $template = $templates[ $template_name ];

    $default_data['title'] = ( $template_name !== 'blank' ? $template['title'] : '' );
    $default_data['form'] = bbforms_fill_editor_lines( $template['form'] );
    $default_data['actions'] = bbforms_fill_editor_lines( $template['actions'] );
    $default_data['options'] = bbforms_fill_editor_lines( $template['options'] );
    $default_data['author_id'] = get_current_user_id();
    $default_data['created_at'] = gmdate( 'Y-m-d H:i:s' );
    $default_data['updated_at'] = gmdate( 'Y-m-d H:i:s' );

    return $default_data;
}
add_filter( 'ct_bbforms_forms_default_data', 'bbforms_forms_default_data' );

function bbforms_fill_editor_lines( $content, $lines = 25 ) {
    $count = substr_count( $content, "\n" );

    if( $count < $lines ) {
        $content .= str_repeat( "\n", $lines - $count - 1 );
    }

    return $content;
}

/**
 * Register custom meta boxes
 *
 * @since  1.0.0
 */
function bbforms_forms_add_meta_boxes() {

    global $ct_table;

    remove_meta_box( 'submitdiv', 'bbforms_forms', 'side' );
    add_meta_box( 'submitdiv', __( 'Save Changes', 'bbforms' ), 'bbforms_forms_submit_meta_box', 'bbforms_forms', 'normal', 'default' );


}
add_action( 'add_meta_boxes', 'bbforms_forms_add_meta_boxes' );

/**
 * Submit meta box
 *
 * @since  1.0.0
 */
function bbforms_forms_submit_meta_box() {

    global $ct_table;

    $primary_key = $ct_table->db->primary_key;
    $editing = false;
    $object_id = 0;
    $form = null;

    if( isset( $_GET[$primary_key] ) ) {
        $object_id = absint( $_GET[$primary_key] );
        $form = ct_get_object( $object_id );
        $editing = true;
    }

    $submit_label = __( 'Add', 'bbforms' );

    if( $editing ) {
        $submit_label = __( 'Update', 'bbforms' );
    }

    ?>

    <div class="submitbox" id="submitpost">

        <div id="major-publishing-actions">

            <?php
            if ( current_user_can( $ct_table->cap->delete_item, $object_id ) ) {

                printf(
                    '<a href="%s" class="submitdelete deletion button button-large bbforms-button-danger" onclick="%s" aria-label="%s">%s</a>',
                    esc_attr( ct_get_delete_link( $ct_table->name, $object_id ) ),
                    "return confirm('" .
                    esc_attr( ct_get_table_label( $ct_table->name, 'delete_item_confirm' ) ) .
                    "');",
                    esc_attr( __( 'Delete permanently', 'bbforms' ) ),
                    esc_html__( 'Delete Permanently', 'bbforms' )
                );

            } ?>

            <div id="publishing-action">
                <span class="spinner"></span>
                <?php submit_button( $submit_label, 'primary large', 'ct-save', false ); ?>
                <input type="button" name="" id="ct-save-disabled" class="button button-primary" value="<?php echo esc_attr( $submit_label ); ?>" disabled="disabled" style="display: none;">
            </div>

            <div id="view-submissions-action">
                <?php
                $view_submissions_url = ct_get_list_link( 'bbforms_submissions' );
                $view_submissions_url = add_query_arg( array( 'form_id' => absint( $form->id ) ), $view_submissions_url );
                $view_submissions_url = add_query_arg( array( 'from' => 'edit_form' ), $view_submissions_url );
                ?>
                <a href="<?php echo esc_attr( $view_submissions_url ); ?>" target="_blank" id="bbforms-form-view-submissions-<?php echo esc_attr( $object_id ); ?>" class="button button-large bbforms-form-view-submission"><?php echo esc_attr( __( 'View Submissions', 'bbforms' ) ); ?></a>
            </div>

            <div id="export-action">
                <input type="button" name="" id="bbforms-form-export-<?php echo esc_attr( $object_id ); ?>" class="button button-large bbforms-form-export" value="<?php echo esc_attr( __( 'Export', 'bbforms' ) ); ?>">
                <?php if( $form ) : ?>
                <div id="bbforms-form-export-value-<?php echo esc_attr( $object_id ); ?>" class="bbforms-form-export-value" style="display: none !important;"><?php echo bbforms_get_form_content_to_export( $form ); ?></div>
                <?php endif; ?>
            </div>

            <div class="clear"></div>

        </div>

    </div>
    <?php

}
add_action( 'ct_bbforms_forms_edit_screen_submit_meta_box_submit_post_top', 'bbforms_forms_submit_meta_box' );

function bbforms_get_form_content_to_export( $form ) {

    $output = '';

    $output .= '<!---------- FORM ---------->' . "\n\n";
    $output .= bbforms_remove_last_line_breaks( $form->form ) . "\n\n";
    $output .= '<!---------- ACTIONS ---------->' . "\n\n";
    $output .= bbforms_remove_last_line_breaks( $form->actions ) . "\n\n";
    $output .= '<!---------- OPTIONS ---------->' . "\n\n";
    $output .= bbforms_remove_last_line_breaks( $form->options );

    return $output;

}

function bbforms_remove_last_line_breaks( $content ) {

    $lines = explode( "\n", $content );

    $lines = array_reverse( $lines );

    foreach( $lines as $i => $line ) {

        if( trim( $line ) === '' ) {
            unset( $lines[$i] );
        } else {
            break;
        }
    }

    $lines = array_reverse( $lines );

    $content = implode( "\n", $lines );

    return $content;

}

/**
 * CMB2 Meta boxes
 *
 * @since  1.0.0
 */
function bbforms_forms_meta_boxes() {

    // Title
    bbforms_add_meta_box(
        'bbforms-form-title',
        __( 'Form Title', 'bbforms' ),
        'bbforms_forms',
        array(
            'title' => array(
                'name'      => __( 'Title', 'bbforms' ),
                'type'      => 'text',
                'attributes' => array(
                    'placeholder' => __('Enter title here', 'bbforms'),
                ),
            ),
        ),
        array(
            'priority' => 'high',
        )
    );

    // Form Shortcode
    bbforms_add_meta_box(
        'bbforms-form-shortcode',
        __( 'Shortcode', 'bbforms' ),
        'bbforms_forms',
        array(
            'shortcode' => array(
                'name'      => __( 'Shortcode:', 'bbforms' ),
                'type'      => 'text',
                'attributes' => array(
                    'readonly' => 'readonly',
                    'onclick' => 'this.focus(); this.select()',
                ),
                'save_field' => false,
                'default_cb' => 'bbforms_form_shortcode_default',
                'tooltip'   => array(
                    'position' => 'top',
                    'desc' =>  __( 'Copy this shortcode to place this form anywhere in your site.', 'bbforms' ),
                ),
                'after_field' => 'cmb_tooltip_after_field',
            ),
        ),
        array(
            //'context' => 'side',
            'priority' => 'high',
        )
    );

    // Editor
    bbforms_add_meta_box(
        'bbforms-form-editor',
        __( 'Form Editor', 'bbforms' ),
        'bbforms_forms',
        array(
            'form' => array(
                'type'      => 'textarea',
                'before_field'   => 'bbforms_render_form_editor_controls',
            ),
            'actions' => array(
                'type'      => 'textarea',
                'before_field'   => 'bbforms_render_actions_editor_controls',
            ),
            'options' => array(
                'type'      => 'textarea',
                'before_field'   => 'bbforms_render_options_editor_controls',
            ),
            'settings_open' => array(
                'type'      => 'text',
                'save'      => false,
                'render_row_cb'   => 'bbforms_settings_open_render_row',
            ),
            'category' => array(
                'name'      => __( 'Category', 'bbforms' ),
                'type'      => 'select2',
                'tooltip'   => array(
                    'position' => 'top',
                    'desc' => __( 'Set the category of your choice to this form.', 'bbforms' ),
                ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'attributes' => array(
                    'placeholder' => __('Select a category', 'bbforms'),
                    'data-from' => 'ct',
                    'data-table' => 'bbforms_categories',
                    'data-id-field' => 'id',
                    'data-text-field' => 'name',
                    'data-label-field' => '',
                ),
                'options_cb' => 'bbforms_term_options_cb',
                'default_cb' => 'bbforms_term_default_cb',
                'save_field' => false,
            ),
            'tags' => array(
                'name'      => __( 'Tags', 'bbforms' ),
                'type'      => 'multiselect2',
                'tooltip'   => array(
                    'position' => 'top',
                    'desc' => __( 'Set the tags of your choice to this form.', 'bbforms' ),
                ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'attributes' => array(
                    'placeholder' => __('Select the tags', 'bbforms'),
                    'data-tags' => true,
                    'data-from' => 'ct',
                    'data-table' => 'bbforms_tags',
                    'data-id-field' => 'id',
                    'data-text-field' => 'name',
                    'data-label-field' => '',
                ),
                'options_cb' => 'bbforms_term_options_cb',
                'default_cb' => 'bbforms_term_default_cb',
                'save_field' => false,
            ),
            'settings_close' => array(
                'type'      => 'text',
                'save'      => false,
                'render_row_cb'   => 'bbforms_settings_close_render_row',
            ),
            'preview' => array(
                'type'      => 'text',
                'save'      => false,
                'render_row_cb'   => 'bbforms_preview_render_row',
            ),
        ),
        array(
            'priority' => 'high',
            'tabs_speed' => '0',
            'tabs'     => array(
                'form-tab' => array(
                    'icon' => 'dashicons-editor-alignleft',
                    'title' => __( 'Form', 'bbforms' ),
                    'fields' => array( 'form' ),
                ),
                'actions-tab' => array(
                    'icon' => 'dashicons-controls-forward',
                    'title' => __( 'Actions', 'bbforms' ),
                    'fields' => array( 'actions' ),
                ),
                'options-tab' => array(
                    'icon' => 'dashicons-admin-tools',
                    'title' => __( 'Options', 'bbforms' ),
                    'fields' => array( 'options' ),
                ),
                'settings-tab' => array(
                    'icon' => 'dashicons-admin-generic',
                    'title' => __( 'Settings', 'bbforms' ),
                    'fields' => array( 'settings' ),
                ),
            ),
        )
    );

}
add_action( 'cmb2_init', 'bbforms_forms_meta_boxes' );

/**
 * Handles sanitization for code fields
 *
 * @since 1.0.0
 *
 * @param  mixed      $value      The unsanitized value from the form
 *
 * @return mixed                  Sanitized value to be stored/displayed
 */
function bbforms_code_sanitization_cb( $value ) {

    $sanitized = $value;
    $sanitized = is_array( $sanitized ) ? array_map( 'bbforms_code_keep_ampersands', $sanitized ) : bbforms_code_keep_ampersands( $sanitized );
    $sanitized = is_array( $sanitized ) ? array_map( 'wp_kses_post', $sanitized ) : wp_kses_post( $sanitized );
    $sanitized = is_array( $sanitized ) ? array_map( 'bbforms_code_restore_ampersands', $sanitized ) : bbforms_code_restore_ampersands( $sanitized );

    return $sanitized;

}

/**
 * Keep &amp; in a string
 *
 * @since 1.0.0
 *
 * @param string $value
 *
 * @return string
 */
function bbforms_code_keep_ampersands( $value ) {
    return str_replace( '&amp;', 'KEEP_AMP', $value );
}

/**
 * Restore & in a string
 *
 * @since 1.0.0
 *
 * @param string $value
 *
 * @return string
 */
function bbforms_code_restore_ampersands( $value ) {
    $value = str_replace( '&amp;', '&', $value );
    return str_replace( 'KEEP_AMP', '&amp;', $value );
}

/**
 * Body classes
 *
 * @since 1.0.0
 *
 * @param string $classes
 *
 * @return string
 */
function bbforms_forms_admin_body_class( $classes ) {

    if( ! bbforms_is_page( array( 'bbforms_forms', 'edit_bbforms_forms' ) ) ) return $classes;

    $editor_dark = bbforms_get_option( 'editor_theme', 'dark' ) === 'dark';

    if( $editor_dark ) {
        $classes .= ' bbforms-editor-dark ';
    }

    return $classes;
}
add_filter( 'admin_body_class', 'bbforms_forms_admin_body_class' );

/**
 * Admin footer
 *
 * @since 1.0.0
 */
function bbforms_forms_admin_footer() {
    if( ! bbforms_is_page( array( 'bbforms_forms', 'edit_bbforms_forms' ) ) ) return;

    ?>
    <div class="bbforms-dialogs" style="display: none;">
        <?php
        // Form template dialog
        bbforms_render_dialog( array(
            'classes' => 'bbforms-form-template-dialog',
            'title' => esc_html__( 'Choose a form template', 'bbforms' ),
            'content' => bbforms_get_form_templates_dialog_content(),
        ) );

        // Form export dialog
        bbforms_render_dialog( array(
            'classes' => 'bbforms-form-export-dialog',
            'title' => esc_html__( 'Export Form', 'bbforms' ),
            'content' => bbforms_get_form_export_dialog_content(),
            'buttons' => array(
                'download' => array(
                    'label' => esc_html__( 'Download as TXT', 'bbforms' ),
                    'classes' => 'button button-primary',
                ),
                'copy' => array(
                    'label' => esc_html__( 'Copy to clipboard', 'bbforms' ),
                ),
                'close' => array(
                    'label' => esc_html__( 'Close', 'bbforms' ),
                ),
            ),
        ) );

        // Form import dialog
        bbforms_render_dialog( array(
            'classes' => 'bbforms-form-import-dialog',
            'title' => esc_html__( 'Import Form', 'bbforms' ),
            'content' => bbforms_get_form_import_dialog_content(),
            'buttons' => array(
                'import' => array(
                    'label' => esc_html__( 'Import Form', 'bbforms' ),
                    'classes' => 'button button-primary',
                ),
                'close' => array(
                    'label' => esc_html__( 'Close', 'bbforms' ),
                ),
            ),
        ) );

        // Fields help dialog
        bbforms_render_dialog( array(
            'classes' => 'bbforms-fields-help-dialog',
            'title' => esc_html__( 'Fields', 'bbforms' ),
            'subtitle' => esc_html__( 'Help guide about the form fields.', 'bbforms' ),
            'content' => bbforms_get_fields_help(),
            'buttons' => array(
                'close' => array(
                    'label' => esc_html__( 'Close', 'bbforms' ),
                ),
            ),
        ) );

        // Actions help dialog
        bbforms_render_dialog( array(
            'classes' => 'bbforms-actions-help-dialog',
            'title' => esc_html__( 'Actions', 'bbforms' ),
            'subtitle' => esc_html__( 'Help guide about the form actions.', 'bbforms' ),
            'content' => bbforms_get_actions_help(),
            'buttons' => array(
                'close' => array(
                    'label' => esc_html__( 'Close', 'bbforms' ),
                ),
            ),
        ) );

        // BBCodes help dialog
        bbforms_render_dialog( array(
            'classes' => 'bbforms-bbcodes-help-dialog',
            'title' => esc_html__( 'BBCodes', 'bbforms' ),
            'subtitle' => esc_html__( 'Help guide about the BBCodes.', 'bbforms' ),
            'content' => bbforms_get_bbcodes_help(),
            'buttons' => array(
                'close' => array(
                    'label' => esc_html__( 'Close', 'bbforms' ),
                ),
            ),
        ) );

        // Tags help dialog
        bbforms_render_dialog( array(
            'classes' => 'bbforms-tags-help-dialog',
            'title' => esc_html__( 'Tags', 'bbforms' ),
            'subtitle' => esc_html__( 'Help guide about the form tags.', 'bbforms' ),
            'content' => bbforms_get_tags_help(),
            'buttons' => array(
                'close' => array(
                    'label' => esc_html__( 'Close', 'bbforms' ),
                ),
            ),
        ) );

        // Load default options dialog
        bbforms_render_dialog( array(
            'classes' => 'bbforms-load-default-options-dialog',
            'title' => esc_html__( 'Warning!', 'bbforms' ),
            'content' => esc_html__( 'This action will override your current options.', 'bbforms' )
        . '<br>' . esc_html__( 'Do you want to proceed?', 'bbforms' ),
            'buttons' => array(
                'apply-default-options' => array(
                    'label' => esc_html__( 'Continue', 'bbforms' ),
                    'classes' => 'button button-primary',
                ),
                'close' => array(
                    'label' => esc_html__( 'Cancel', 'bbforms' ),
                ),
            ),
        ) );

        // Settings help dialog
        bbforms_render_dialog( array(
                'classes' => 'bbforms-options-help-dialog',
                'title' => esc_html__( 'Options', 'bbforms' ),
                'subtitle' => esc_html__( 'Help guide about all available form options.', 'bbforms' ),
                'content' => bbforms_get_options_help(),
                'buttons' => array(
                    'close' => array(
                        'label' => esc_html__( 'Close', 'bbforms' ),
                    ),
                ),
        ) );
        ?>
    </div>
    <?php

}
add_action( 'admin_footer', 'bbforms_forms_admin_footer' );

/**
 * Form export dialog
 *
 * @since 1.0.0
 *
 * @return string
 */
function bbforms_get_form_export_dialog_content() {

    ob_start();?>

    <textarea id="bbforms-form-export-editor"></textarea>

    <?php $output = ob_get_clean();

    return $output;

}

/**
 * Form import dialog
 *
 * @since 1.0.0
 *
 * @return string
 */
function bbforms_get_form_import_dialog_content() {
    ob_start();?>

    <div class="bbforms-dialog-tabs">
        <div class="bbforms-dialog-tab bbforms-dialog-tab-1 bbforms-dialog-tab-active" data-toggle=".bbforms-form-import-content-1">
            <?php esc_html_e( 'From Code', 'bbforms' ); ?>
        </div>
        <div class="bbforms-dialog-tab bbforms-dialog-tab-2" data-toggle=".bbforms-form-import-content-2">
            <?php esc_html_e( 'From File', 'bbforms' ); ?>
        </div>
    </div>

    <div class="bbforms-form-import-dialog-content">
        <div class="bbforms-dialog-tab-content bbforms-form-import-content-1 bbforms-dialog-tab-content-active">
            <h3 style="margin-top: 0;"><?php esc_html_e( 'Paste the form code here:', 'bbforms' ); ?></h3>
            <textarea id="bbforms-form-import-editor"></textarea>
        </div>
        <div class="bbforms-dialog-tab-content bbforms-form-import-content-2">
            <h3 style="margin-top: 0;"><?php esc_html_e( 'Upload a form TXT file:', 'bbforms' ); ?></h3>
            <input type="file" accept=".txt">
        </div>
    </div>


    <?php $output = ob_get_clean();

    return $output;

}

/**
 * Helper funtion to render a dialog
 *
 * @since 1.0.0
 *
 * @param array $args
 */
function bbforms_render_dialog( $args = array() ) {

    $args = wp_parse_args( $args, array(
            'classes' => '',
            'title' => '',
            'subtitle' => '',
            'tooltip' => '',
            'content' => '',
            'buttons' => array(),
    ) );

    ?>
    <div class="<?php echo esc_attr( $args['classes'] ); ?>">
        <h2 class="bbforms-dialog-title <?php echo esc_attr( $args['classes'] ); ?>-title">
            <?php echo esc_html( $args['title'] ); ?>
            <?php if ( ! empty( $args['tooltip'] ) ) : cmb_tooltip_html( $args['tooltip'] ); endif; ?>
        </h2>
        <?php if( ! empty( $args['subtitle'] ) ) : ?>
        <p class="bbforms-dialog-subtitle <?php echo esc_attr( $args['classes'] ); ?>-subtitle">
            <?php echo esc_html( $args['subtitle'] ); ?>
        </p>
        <?php endif; ?>
        <?php // Prevents autofocus on the first button or field ?>
        <input type="hidden" autofocus="autofocus" />
        <?php echo $args['content']; ?>
        <?php if( count( $args['buttons'] ) ) : ?>
            <div class="bbforms-dialog-bottom">
                <?php foreach( $args['buttons'] as $button_key => $button ) :
                    $button = wp_parse_args( $button, array(
                        'classes' => 'button',
                        'label' => '',
                    )); ?>
                    <button type="button" class="bbforms-dialog-button bbforms-dialog-button-<?php echo esc_attr( $button_key ) ?> <?php echo esc_attr( $button['classes'] ) ?>"><?php echo esc_html( $button['label'] ); ?></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php

}

function bbforms_settings_open_render_row( $field_args, $field ) {
    ?>
    <div class="cmb-row cmb-type-group cmb2-id-settings">
    <?php
}
function bbforms_settings_close_render_row( $field_args, $field ) {
    ?>
    </div>
    <?php
}
function bbforms_preview_render_row( $field_args, $field ) {
    bbforms_update_form_meta( $field->object_id, 'preview', '' );

    $form = bbforms_get_form( $field->object_id );

    $url = site_url('/');
    $url = add_query_arg( array( 'bbforms_preview' => 'yes' ), $url );
    $url = add_query_arg( array( 'id' => $field->object_id ), $url );
    $sandbox = 'allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts';
    ?>

    <div id="bbforms-form-editor-preview-tabs" class="cmb-tabs cmb2-tab-ignore cmb-tab-active-item">
        <div id="bbforms-form-editor-preview-tab" class="cmb-tab">
            <span class="cmb-tab-icon"><i class="dashicons dashicons-hidden"></i></span>
            <span class="cmb-tab-title"><?php esc_html_e( 'Hide preview', 'bbforms' ); ?></span>
        </div>
    </div>
    <div class="cmb-row cmb-type-iframe cmb2-id-preview cmb2-tab-ignore cmb-tab-active-item">
        <iframe src="<?php echo esc_attr( $url ); ?>" data-url="<?php echo esc_attr( $url ); ?>" sandbox="<?php echo esc_attr( $sandbox ); ?>"></iframe>
    </div>
    <?php
}

function bbforms_render_form_editor_controls() {
    bbforms_render_editor_controls( 'form' );
}

function bbforms_render_actions_editor_controls() {
    bbforms_render_editor_controls( 'actions' );
}

function bbforms_render_options_editor_controls() {
    bbforms_render_editor_controls( 'options' );
}



function bbforms_form_shortcode_default( $field_args, $field ) {
    return '[bbforms id="' . $field->object_id . '"]';
}

function bbforms_term_default_cb( $field_args, $field ) {

    global $ct_table;

    $default = array();

    $table = $field->args( 'attributes' )['data-table'];

    // Try to get the result from cache to prevent duplicated queries
    $cache_key = sanitize_key( $table . '_relationships' );
    $cache = bbforms_get_cache( $cache_key );

    if( $cache !== null ) {
        $terms = $cache;
    } else {
        $ct_table = ct_setup_table( $table . '_relationships' );

        $terms = ct_get_object_terms( $field->object_id );

        ct_reset_setup_table();
    }

    if( $terms === null ) {
        // Cache result
        bbforms_set_cache( $cache_key, array() );

        return $default;
    }

    if( ! is_array( $terms ) ) {
        $terms = array( $terms );
    }

    // Cache result
    bbforms_set_cache( $cache_key, $terms );

    foreach( $terms as $term ) {
        $default[$term->id] = $term->name;
    }

    return $default;

}

function bbforms_term_options_cb( $field ) {

    global $ct_table;

    $options = array();

    $table = $field->args( 'attributes' )['data-table'];

    // Try to get the result from cache to prevent duplicated queries
    $cache_key = sanitize_key( $table . '_relationships' );
    $cache = bbforms_get_cache( $cache_key );

    if( $cache !== null ) {
        $terms = $cache;
    } else {
        $ct_table = ct_setup_table( $table . '_relationships' );

        $terms = ct_get_object_terms( $field->object_id );

        ct_reset_setup_table();
    }

    if( $terms === null ) {
        // Cache result
        bbforms_set_cache( $cache_key, array() );

        return $options;
    }

    if( ! is_array( $terms ) ) {
        $terms = array( $terms );
    }

    // Cache result
    bbforms_set_cache( $cache_key, $terms );

    foreach( $terms as $term ) {
        $options[$term->id] = $term->name;
    }

    return $options;

}

/**
 * On insert form
 *
 * @since  1.0.0
 *
 * @param array $object_data
 * @param array $original_object_data
 */
function bbforms_forms_on_insert_object_data( $object_data, $original_object_data ) {

    global $ct_table;

    if( $ct_table->name !== 'bbforms_forms' ) {
        return $object_data;
    }

    // Sanitize form
    if( isset( $_REQUEST['form'] ) ) {
        $object_data['form'] = bbforms_code_sanitization_cb( $_REQUEST['form'] );
    }

    // Sanitize actions
    if( isset( $_REQUEST['actions'] ) ) {
        $object_data['actions'] = bbforms_code_sanitization_cb( $_REQUEST['actions'] );
    }

    // Sanitize options
    if( isset( $_REQUEST['options'] ) ) {
        $object_data['options'] = bbforms_code_sanitization_cb( $_REQUEST['options'] );
    }

    // Check if we are updating
    if( isset( $object_data['id'] ) ) {
        if( isset( $object_data['category'] ) ) {
            // Update relationships
            bbforms_handle_terms_save( $object_data['id'], $object_data['category'], 'bbforms_categories' );

            // Remove from the object data
            unset( $object_data['category'] );
        } else {
            // Clear relationships
            bbforms_handle_terms_save( $object_data['id'], array(), 'bbforms_categories' );
        }

        if( isset( $object_data['tags'] ) ) {
            // Update relationships
            bbforms_handle_terms_save( $object_data['id'], $object_data['tags'], 'bbforms_tags' );

            // Remove from the object data
            unset( $object_data['tags'] );
        } else {
            // Clear relationships
            bbforms_handle_terms_save( $object_data['id'], array(), 'bbforms_tags' );
        }
    } else {
        // Removed unwanted fields if they exists
        if( isset( $object_data['category'] ) ) {
            unset( $object_data['category'] );
        }

        if( isset( $object_data['tags'] ) ) {
            unset( $object_data['tags'] );
        }
    }

    return $object_data;

}
add_filter( 'ct_insert_object_data', 'bbforms_forms_on_insert_object_data', 10, 2 );

function bbforms_handle_terms_save( $form_id, $terms, $table ) {

    global $ct_table;

    $form_id = absint( $form_id );

    if( ! is_array( $terms ) ) {
        $terms = array( $terms );
    }

    // Clear relationships
    if( count( $terms ) === 0 ) {
        $ct_table = ct_setup_table( $table . '_relationships' );
        ct_delete_object_relationships( $form_id );
        ct_reset_setup_table();
        return;
    }

    $terms_ids = array();

    $ct_table = ct_setup_table( $table );

    // Check terms received
    foreach ( $terms as $term_id ) {

        // Add new term
        if( ! is_numeric( $term_id ) && ! empty( $term_id ) ) {
            $new_term_id = ct_insert_object( array(
                'name' => $term_id,
                'slug' => sanitize_title_with_dashes( $term_id ),
                'description' => '',
            ) );

            if( $new_term_id ) {
                $terms_ids[] = absint( $new_term_id );
            }

            continue;
        }

        $term_id = absint( $term_id );

        if( $term_id === 0 ) {
            continue;
        }

        $terms_ids[] = $term_id;

    }

    ct_reset_setup_table();

    // Update relationships
    $ct_table = ct_setup_table( $table . '_relationships' );
    ct_update_object_relationships( $form_id, $terms_ids );
    ct_reset_setup_table();


}

/**
 * On delete
 *
 * @since 1.0.0
 *
 * @param int $object_id
 */
function bbforms_forms_on_delete_object( $object_id ) {

    global $wpdb, $ct_table;

    if( ! ( $ct_table instanceof CT_Table ) ) {
        return;
    }

    if( $ct_table->name !== 'bbforms_forms' ) {
        return;
    }

    // Delete categories relationships
    $ct_table = ct_setup_table( 'bbforms_categories_relationships' );
    ct_delete_object_relationships( $object_id );
    ct_reset_setup_table();

    // Delete tags relationships
    $ct_table = ct_setup_table( 'bbforms_tags_relationships' );
    ct_delete_object_relationships( $object_id );
    ct_reset_setup_table();

    /**
     * Filter available to decide if form submissions should be deleted when a form gets deleted
     *
     * @since 1.0.0
     *
     * @param bool  $delete
     * @param int   $form_id
     *
     * @return bool
     */
    if( ! apply_filters( 'bbforms_delete_submissions_on_delete_form', true, $object_id ) ) {
        return;
    }

    $ct_table = ct_setup_table( 'bbforms_submissions' );

    $submissions = $ct_table->db->table_name;
    $submissions_meta = $ct_table->meta->db->table_name;

    // Get all submissions assigned to this form
    $submissions_ids = $wpdb->get_col( $wpdb->prepare( "SELECT s.id FROM {$submissions} AS s WHERE s.form_id = %d", $object_id ) );

    // Delete submissions (parsing hooks for extra removals)
    bbforms_submissions_delete( $submissions_ids );

    // Delete orphaned submissions metas
    $wpdb->query( "DELETE sm FROM {$submissions_meta} sm LEFT JOIN {$submissions} s ON s.id = sm.id WHERE s.id IS NULL" );

    ct_reset_setup_table();

}
add_action( 'delete_object', 'bbforms_forms_on_delete_object' );