<?php
/**
 * Submissions
 *
 * @package     BBForms\Custom_Tables\Submissions
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

function bbforms_submissions_init() {
    global $bbforms_form;

    if( ! bbforms_is_page( 'bbforms_submissions' ) ) {
        return;
    }

    $form_id = ( isset( $_GET['form_id'] ) ) ? absint( $_GET['form_id'] ) : 0;

    $form = new BBForms_Form( $form_id );

    if( ! $form->exists() ) return;

    $bbforms_form = $form;

    bbforms_do_form_fields( $form );
}
add_action( 'admin_init', 'bbforms_submissions_init' );

function bbforms_submissions_wp_redirect( $location, $status ) {

    $override = false;

    if( isset( $_GET['action'] ) && $_GET['action'] === 'delete' ) {
        $override = true;
    }

    if( isset( $_GET['ct-action'] ) && $_GET['ct-action'] === 'delete' ) {
        $override = true;
    }

    if( strpos( $location, 'admin.php?page=bbforms_submissions' ) !== false ) {
        $form_id = ( isset( $_GET['form_id'] ) ) ? absint( $_GET['form_id'] ) : 0;

        $location .= '&form_id=' . $form_id;

    }

    return $location;

}
add_filter( 'wp_redirect', 'bbforms_submissions_wp_redirect', 10, 2 );

/**
 * Body classes
 *
 * @since 1.0.0
 *
 * @param string $classes
 *
 * @return string
 */
function bbforms_submissions_admin_body_class( $classes ) {

    if( ! bbforms_is_page( array( 'bbforms_submissions', 'edit_bbforms_submission' ) ) ) return $classes;

    $form_id = ( isset( $_GET['form_id'] ) ) ? absint( $_GET['form_id'] ) : 0;

    $classes .= " bbforms-submissions-from-{$form_id} ";

    return $classes;
}
add_filter( 'admin_body_class', 'bbforms_submissions_admin_body_class' );

/**
 * Custom Table Labels
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_submissions_labels() {

    return array(
        'singular' => __( 'Submission', 'bbforms' ),
        'plural' => __( 'Submissions', 'bbforms' ),
        'labels' => array(
            'list_menu_title' => __( 'Submissions', 'bbforms' ),
        ),
    );

}
add_filter( 'ct_bbforms_submissions_labels', 'bbforms_submissions_labels' );

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
function bbforms_submissions_query_where( $where, $ct_query ) {

    global $ct_table, $wpdb;

    if( $ct_table->name !== 'bbforms_submissions' ) {
        return $where;
    }

    // Shorthand
    $qv = $ct_query->query_vars;

    $form_id = ( isset( $_GET['form_id'] ) ) ? absint( $_GET['form_id'] ) : 0;

    if( $form_id === 0 ) {
        $where .= " AND {$ct_table->db->table_name}.form_id = 0 ";
    }

    // Strings
    $where .= bbforms_custom_table_where( $qv, 'fields', 'string' );

    // Integers
    $where .= bbforms_custom_table_where( $qv, 'number', 'integer' );
    $where .= bbforms_custom_table_where( $qv, 'form_id', 'integer' );
    $where .= bbforms_custom_table_where( $qv, 'user_id', 'integer' );
    $where .= bbforms_custom_table_where( $qv, 'post_id', 'integer' );

    return $where;
}
add_filter( 'ct_query_where', 'bbforms_submissions_query_where', 10, 2 );

/**
 * Define the search fields
 *
 * @since 1.0.0
 *
 * @param array $search_fields
 *
 * @return array
 */
function bbforms_submissions_search_fields( $search_fields ) {

    //$search_fields[] = 'number';
    //$search_fields[] = 'form_id';
    //$search_fields[] = 'user_id';
    //$search_fields[] = 'post_id';
    $search_fields[] = 'fields';

    return $search_fields;

}
add_filter( 'ct_query_bbforms_submissions_search_fields', 'bbforms_submissions_search_fields' );

/**
 * Columns in list view
 *
 * @since 1.0.0
 *
 * @param array $columns
 *
 * @return array
 */
function bbforms_submissions_manage_columns( $columns = array() ) {
    global $bbforms_form;

    if( $bbforms_form !== null ) {
        $columns['number'] = __( '#', 'bbforms' );

        $excluded_fields = bbforms_submissions_get_excluded_fields();

        foreach( $bbforms_form->fields as $name => $field ) {
            if( in_array( $field->bbcode, $excluded_fields ) ) {
                continue;
            }

            $columns[$name] = $field->label;
        }

        $columns['created_at'] = __( 'Date', 'bbforms' );
    }

    return $columns;
}
add_filter( 'manage_bbforms_submissions_columns', 'bbforms_submissions_manage_columns' );

/**
 * Sortable columns for list view
 *
 * @since 1.0.0
 *
 * @param array $sortable_columns
 *
 * @return array
 */
function bbforms_submissions_manage_sortable_columns( $sortable_columns ) {

    $sortable_columns['number']             = array( 'number', false );
    $sortable_columns['form_id']            = array( 'form_id', false );
    $sortable_columns['user_id']            = array( 'user_id', false );
    $sortable_columns['post_id']            = array( 'post_id', false );
    $sortable_columns['fields']             = array( 'fields', false );
    $sortable_columns['created_at']         = array( 'created_at', false );

    return $sortable_columns;

}
add_filter( 'manage_bbforms_submissions_sortable_columns', 'bbforms_submissions_manage_sortable_columns' );

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
function bbforms_submissions_row_actions( $actions, $object ) {

    global $ct_table;

    $form_id = ( isset( $_GET['form_id'] ) ) ? absint( $_GET['form_id'] ) : 0;

    // For now, do not let to edit a submission
    // unset( $actions['edit'] );

    // Override delete
    if ( current_user_can( $ct_table->cap->delete_item, $object->id ) ) {
        unset( $actions['delete'] );
        $actions['delete'] = sprintf(
            '<a href="%s" class="submitdelete" onclick="%s" aria-label="%s">%s</a>',
            ct_get_delete_link( $ct_table->name, $object->id ) . '&form_id=' . $form_id,
            "return confirm('" .
            esc_attr( __( "Are you sure you want to delete this submission?\\n\\nClick \\'Cancel\\' to go back, \\'OK\\' to confirm deletion.", 'bbforms' ) ) .
            "');",
            esc_attr( __( 'Delete', 'bbforms' ) ),
            __( 'Delete', 'bbforms' )
        );
    }

    return $actions;

}
add_filter( 'bbforms_submissions_row_actions', 'bbforms_submissions_row_actions', 10, 2 );

function bbforms_submissions_bulk_actions( $bulk_actions ) {
    global $bbforms_form, $bbforms_bulk_actions_rendered;

    if( $bbforms_bulk_actions_rendered === null ) {
        $bbforms_bulk_actions_rendered = 0;
    }

    $form_id = ( isset( $_GET['form_id'] ) ) ? absint( $_GET['form_id'] ) : 0;

    if( $bbforms_bulk_actions_rendered === 1 ) {
        $forms = bbforms_get_forms();
        ?>

            <div class="bbforms-submissions-form-selector">
                <select id="form_id" name="form_id">
                    <option value="0" <?php echo selected( $form_id, 0, true ); ?> disabled="disabled">- <?php esc_html_e( 'Select a form', 'bbforms' ); ?> -</option>
                <?php foreach( $forms as $form ) :
                    $form_title = $form->title;

                    if( empty( $form_title ) ) {
                        $form_title = __( '(no title)', 'bbforms' );
                    } ?>
                    <option value="<?php echo esc_attr( $form->id ); ?>" <?php echo selected( $form_id, absint( $form->id ), true ); ?>><?php echo esc_html( $form_title . ' (#' . $form->id . ')' ); ?></option>
                <?php endforeach; ?>
                </select>
            </div>

        <?php

        if( $form_id === 0 || $bbforms_form === null ) {
            ?>
            <div class="bbforms-submissions-select-form-message"><?php esc_html_e( 'Please, select a form to view submissions', 'bbforms' ); ?></div>

            <?php
        }
    }

    $bbforms_bulk_actions_rendered++;


    return $bulk_actions;

}
add_filter( 'bbforms_submissions_bulk_actions', 'bbforms_submissions_bulk_actions' );

function bbforms_submissions_extra_tablenav( $witch ) {

    global $bbforms_form;

    $form_id = ( isset( $_GET['form_id'] ) ) ? absint( $_GET['form_id'] ) : 0;

    if( $witch === 'top' ) {

        $columns = apply_filters( 'manage_bbforms_submissions_columns', array() );

        if( count( $columns ) ) : ?>

            <div class="bbforms-submissions-columns-options">
                <fieldset>
                    <label><?php esc_html_e( 'Columns:', 'bbforms' ); ?></label>
                    <?php foreach( $columns as $column => $label ) :?>
                        <label><input class="bbforms-hide-column-toggle"
                                      type="checkbox"
                                      value="<?php echo esc_attr( $column ); ?>"
                                      checked="checked"
                            ><?php echo esc_html( $label ); ?></label>
                    <?php endforeach; ?>
                    <?php if( $bbforms_form->has_files ) : ?>
                        <label><input class="bbforms-show-file-previews-toggle"
                                      type="checkbox"
                                      value="1"
                                      checked="checked"
                            ><?php echo esc_html( __( 'File Previews', 'bbforms' ) ); ?></label>
                    <?php endif;?>
                </fieldset>
            </div>

        <?php endif;

    } else if( $witch === 'bottom' ) {

        if( $form_id !== 0 && $bbforms_form !== null ) :

            $form_title = $bbforms_form->title;

            if( empty( $form_title ) ) {
                $form_title = __( '(no title)', 'bbforms' );
            }

            $form_title = str_replace( ' ', '-', $form_title );
            $form_title = sanitize_key( $form_title );
            ?>
        <div class="bbforms-submissions-export-csv">
            <button type="button"
                    class="button button-primary"
                    data-form="<?php echo esc_attr( $form_id ); ?>"
                    data-filename="<?php echo esc_attr( $form_title . '-submissions' ); ?>"
            ><?php esc_html_e( 'Export as CSV', 'bbforms' ); ?></button>
        </div>
        <?php endif;

    }

}
add_action( 'manage_bbforms_submissions_extra_tablenav', 'bbforms_submissions_extra_tablenav' );


/**
 * Columns rendering for list view
 *
 * @since  1.0.0
 *
 * @param string $column_name
 * @param integer $object_id
 */
function bbforms_submissions_manage_custom_column( $column_name, $object_id ) {

    global $bbforms_form;

    // Setup vars
    $submission = ct_get_object( $object_id );

    $fields = bbforms_submissions_decode_fields( $submission );

    if( isset( $bbforms_form->fields[$column_name] ) ) {

        $field = $bbforms_form->get_field( $column_name );
        $output = ( isset( $fields[$column_name] ) ? $fields[$column_name] : '' );

        bbforms_submissions_render_field_column( $field, $output, $column_name, $fields, $bbforms_form );
    }

    switch( $column_name ) {
        case 'number':
            echo '<b>' . esc_html( $submission->number ) . '</b>';
            break;
        case 'form_id':
            echo esc_html( $submission->form_id );
            break;
        case 'user_id':
            echo esc_html( $submission->user_id );
            break;
        case 'post_id':
            echo esc_html( $submission->post_id );
            break;
        case 'fields':
            echo esc_html( $submission->fields );
            break;
        case 'created_at':
            echo esc_html( $submission->created_at );
            break;
    }
}
add_action( 'manage_bbforms_submissions_custom_column', 'bbforms_submissions_manage_custom_column', 10, 2 );

function bbforms_submissions_render_field_column( $field, $output, $column_name, $fields, $bbforms_form ) {

    if( is_array( $output ) ) {

        $skip = false;

        if( $field->bbcode == 'check' && ! is_array( $output[0] ) ) {
            $skip = true;
        }

        if( $field->bbcode == 'select' && ! is_array( $output[0] ) ) {
            $skip = true;
        }

        if( ! $skip ) {
            $last = array_key_last( $output );

            foreach( $output as $k => $value ) {
                bbforms_submissions_render_field_column( $field, $value, $column_name, $fields, $bbforms_form );

                if( $k !== $last ) echo '<hr>';
            }

            return;
        }
    }

    switch( $field->bbcode ) {
        case 'check':
            bbforms_submissions_render_check_field( $field, $output );
            break;
        case 'select':
            if( is_array( $output ) ) {
                $output = implode( '<br>', $output );
            }

            echo $output;
            break;
        case 'radio':
            bbforms_submissions_render_radio_field( $field, $output );
            break;
        case 'file':
            bbforms_submissions_render_file_field( $field, $output );
            break;
        case 'country':
            bbforms_submissions_render_country_field( $field, $output );
            break;
        default:
            if( is_array( $output ) ) {
                $output = implode( '<br>', $output );
            }

            $output = str_replace( "\n", "<br>", $output );

            //$output = wpautop( wp_kses_post( $output ) );

            // Show the field value
            echo apply_filters( 'bbforms_submissions_field_column_output', $output, $field, $column_name, $fields, $bbforms_form );
            break;
    }

}

function bbforms_submissions_render_check_field( $field, $field_value ) {

    foreach( $field->options as $value => $label ) : ?>
        <div class="bbforms-<?php echo esc_attr( $field->bbcode ); ?>-display">
                    <span class="bbforms-<?php echo esc_attr( $field->bbcode ); ?>-display-icon">
                        <?php if( $value == $field_value || ( is_array( $field_value ) && in_array( $value, $field_value ) ) ) : ?>
                            <span class="dashicons dashicons-yes"></span>
                        <?php endif; ?>
                    </span>
            <span><?php echo esc_html( wp_strip_all_tags( $label ) ); ?></span>
        </div>
    <?php endforeach;

}

function bbforms_submissions_render_radio_field( $field, $field_value ) {

    foreach( $field->options as $value => $label ) : ?>
        <div class="bbforms-<?php echo esc_attr( $field->bbcode ); ?>-display">
                    <span class="bbforms-<?php echo esc_attr( $field->bbcode ); ?>-display-icon">
                        <?php if( $value == $field_value || ( is_array( $field_value ) && in_array( $value, $field_value ) ) ) : ?>
                            <span class="dashicons dashicons-dot"></span>
                        <?php endif; ?>
                    </span>
            <span><?php echo esc_html( wp_strip_all_tags( $label ) ); ?></span>
        </div>
    <?php endforeach;

}

function bbforms_submissions_render_file_field( $field, $field_value ) {

    if( $field_value === '' ) return;

    $parts = explode( '/' , $field_value );
    $filename = end( $parts );
    $file_url = str_replace( BBFORMS_UPLOAD_DIR, BBFORMS_UPLOAD_URL, $field_value );

    $wp_filetype = wp_check_filetype( $filename );

    if( $wp_filetype['type'] ) {
        $main_type = explode( '/' , $wp_filetype['type'] )[0];

        ?>
        <div class="bbforms-file-display">
        <?php

        switch( $main_type ) {
            case 'image':
                ?>
                <a href="<?php echo esc_attr( $file_url ) ?>" target="_blank">
                    <img src="<?php echo esc_attr( $file_url ) ?>"/>
                </a>
                <?php
                break;
            case 'video':
                ?>
                <video controls>
                    <source src="<?php echo esc_attr( $file_url ) ?>"/>
                </video>
                <?php
                break;
            case 'audio':
                ?>
                <audio controls>
                <source src="<?php echo esc_attr( $file_url ) ?>"/>
                </audio>
                <?php
                break;
        }

         ?>
        </div>
        <?php
    }
    
    ?>
    <a href="<?php echo esc_attr( $file_url ) ?>" download><?php echo esc_html( $filename ) ?></a>
    <?php
}

function bbforms_submissions_render_country_field( $field, $field_value ) {

    if( $field_value === '' ) return;

    echo bbforms_get_country_flag( $field_value ) . ' ' . esc_html( $field_value );

}

function bbforms_submissions_decode_fields( $submission ) {

    global $bbforms_submission_content;

    if( ! is_array( $bbforms_submission_content ) ) {
        $bbforms_submission_content = array();
    }

    // Use a global var to decode just one time
    if( ! isset( $bbforms_submission_content[$submission->id] ) ) {
        $bbforms_submission_content[$submission->id] = json_decode( $submission->fields, true );
    }

    return $bbforms_submission_content[$submission->id];

}

/**
 * Default data when creating a new item (similar to WP auto draft) see ct_insert_object()
 *
 * @since  1.0.0
 *
 * @param array $default_data
 *
 * @return array
 */
function bbforms_submissions_default_data( $default_data = array() ) {

    $default_data['number']     = 0;
    $default_data['form_id']    = 0;
    $default_data['user_id']    = 0;
    $default_data['post_id']    = 0;
    $default_data['fields']     = '';
    $default_data['created_at'] = gmdate( 'Y-m-d H:i:s' );

    return $default_data;
}
add_filter( 'ct_bbforms_submissions_default_data', 'bbforms_submissions_default_data' );

/**
 * Register custom meta boxes
 *
 * @since  1.0.0
 */
function bbforms_submissions_add_meta_boxes() {

    add_meta_box( 'bbforms_submissions_actions', __( 'Save Changes', 'bbforms' ), 'bbforms_submissions_actions_meta_box', 'bbforms_submissions', 'side', 'core' );
    add_meta_box( 'bbforms_submissions_fields', __( 'Fields Submitted', 'bbforms' ), 'bbforms_submissions_fields_meta_box', 'bbforms_submissions', 'normal', 'core' );
    remove_meta_box( 'submitdiv', 'bbforms_submissions', 'side' );

}
add_action( 'add_meta_boxes', 'bbforms_submissions_add_meta_boxes' );

/**
 * Actions meta box
 *
 * @since  1.0.0
 *
 * @param stdClass  $submission
 */
function bbforms_submissions_actions_meta_box( $submission ) {

    global $ct_table;

    ?>
    <table class="form-table">
        <tbody>

        <tr>
            <th><?php esc_html_e( 'ID', 'bbforms' ); ?></th>
            <td><?php echo esc_html( '#' . $submission->number ); ?></td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'User', 'bbforms' ); ?></th>
            <td><?php $user = get_userdata( $submission->user_id );

                if( $user ) :

                    if( current_user_can('edit_users')) {
                        ?>

                        <strong><a href="<?php echo esc_attr( get_edit_user_link( $submission->user_id ) ); ?>"><?php echo esc_html( $user->display_name ); ?></a></strong>
                        <br>
                        <?php echo esc_html( $user->user_email ); ?>

                        <?php
                    } else {
                        echo esc_html( $user->display_name ) . '<br>' . esc_html( $user->user_email );
                    }
                else:
                    esc_html_e( 'Not assigned', 'bbforms' );
                endif; ?></td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Post', 'bbforms' ); ?></th>
            <td><?php $post = get_post( $submission->post_id );

                if( $post ) :

                    if( current_user_can( 'edit_post', $submission->post_id ) ) {
                        ?>

                        <strong><a href="<?php echo esc_attr( get_edit_post_link( $submission->post_id ) ); ?>"><?php echo esc_html( $post->post_title ); ?></a></strong>

                        <?php
                    } else {
                        echo esc_html( $post->post_title );
                    }

                else :
                    esc_html_e( 'Not assigned', 'bbforms' );
                endif; ?></td>
        </tr>

        <tr>
            <th><?php esc_html_e( 'Date', 'bbforms' ); ?></th>
            <td><?php echo esc_html( $submission->created_at ); ?></td>
        </tr>

        </tbody>
    </table>
    <?php

    $ct_table->views->edit->submit_meta_box( $submission );


}

/**
 * Submission fields meta box
 *
 * @since  1.0.0
 *
 * @param stdClass  $submission
 */
function bbforms_submissions_fields_meta_box( $submission ) {

    global $bbforms_form, $bbforms_request;

    $form = new BBForms_Form( $submission->form_id );

    if( ! $form->exists() ) return;

    $bbforms_form = $form;

    $bbforms_request = json_decode( $submission->fields, true );

    // Prevent fields to get initialized with a value to show real stored values only
    $form->form = preg_replace_callback('/value="(.*?)"/is', function ( $matches ) { return ''; }, $form->form);

    /**
     * Doing submission fields meta box
     *
     * @since  1.0.0
     *
     * @param stdClass      $submission
     * @param BBForms_Form  $form
     * @param array         $request
     */
    do_action( 'bbforms_submissions_doing_fields_meta_box', $submission, $bbforms_form, $bbforms_request );

    // Add an extra filter to override some fields rendering
    add_filter( 'bbforms_render_field', 'bbforms_submissions_render_field', 10, 4 );

    $content = bbforms_do_form_fields( $form );

    $content = preg_replace_callback('/name="(.*?)"/is', function ( $matches ) {
        if( strpos( $matches[1], '[]' ) !== false ) {
            return sprintf( 'name="new_fields[%s][]"', str_replace( '[]', '', $matches[1] ) );
        }

        return sprintf( 'name="new_fields[%s]"', $matches[1] );
    }, $content);

    echo $content;

}

/**
 * Override field rendering on submissions
 *
 * @since  1.0.0
 *
 * @param string        $output
 * @param array         $attrs
 * @param string        $content
 * @param BBForms_Field $field
 *
 * @return string
 */
function bbforms_submissions_render_field( $output, $attrs, $content, $field ) {

    /**
     * Filter to override field rendering on submissions
     *
     * @since  1.0.0
     *
     * @param string        $output
     * @param array         $attrs
     * @param string        $content
     * @param BBForms_Field $field
     *
     * @return string
     */
    $output = apply_filters( 'bbforms_submissions_render_field', $output, $attrs, $content, $field );

    if( $field->bbcode !== 'file' ) {
        return $output;
    }

    ob_start();
    bbforms_submissions_render_file_field( $field, $attrs['value'] );
    $preview = ob_get_clean();

    return $preview . sprintf(
        '<%1$s type="%2$s" %3$s value="%4$s"/>',
        'input',
        'hidden',
        bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
        esc_attr( $attrs['value'] ),
    );

}


/**
 * On insert submission
 *
 * @since  1.0.0
 *
 * @param array $object_data
 * @param array $original_object_data
 */
function bbforms_submissions_on_insert_object_data( $object_data, $original_object_data ) {

    global $ct_table, $bbforms_form;

    if( $ct_table->name !== 'bbforms_submissions' ) {
        return $object_data;
    }

    // Check if we are updating
    if( isset( $object_data['id'] ) && isset( $object_data['new_fields'] ) ) {

        $form_id = absint( $original_object_data['form_id'] );

        $form = new BBForms_Form( $form_id );

        if( ! $form->exists() ) return $object_data;

        $bbforms_form = $form;

        bbforms_do_form_fields( $form );

        // Sanitize submitted fields
        $fields = bbforms_submissions_sanitize_fields( $object_data['new_fields'] );

        // By default, CT applies an unslash to all fields, so lets update the object fields here, mainly to keep \n in textarea
        $ct_table->db->update( array( 'fields' => json_encode( $fields ) ), array( 'id' => $object_data['id'] ) );

        // Remove the fields key to prevent get them updated again
        unset( $object_data['fields'] );

    }

    return $object_data;

}
add_filter( 'ct_insert_object_data', 'bbforms_submissions_on_insert_object_data', 10, 2 );

function bbforms_submissions_sanitize_fields( $fields ) {

    global $bbforms_form;

    $bbforms_fields = bbforms_get_fields();

    // Not used yet but ready
    $excluded_to_save = bbforms_submissions_get_excluded_to_save_fields();

    foreach( $fields as $name => $value ) {

        if( isset( $bbforms_form->fields[$name] ) ) {
            // Get the bbcode
            $bbcode = $bbforms_form->fields[$name]->bbcode;

            if( $bbcode === 'file' ) {
                $value = str_replace( "\\\\", "\\", $value );
            }

            // Use the bbcode class to sanitize the field
            $bbforms_fields[$bbcode]->set_value( $value );
            $bbforms_fields[$bbcode]->sanitize();

            $fields[$name] = $bbforms_fields[$bbcode]->sanitized_value;

        } else {
            // The basic sanitization is sanitize_text_field
            $fields[$name] = ( ! is_array( $value ) ? sanitize_text_field( $value ) : array_map( 'sanitize_text_field', $value ) );
        }

    }

    return $fields;

}

/**
 * On delete
 *
 * @since 1.0.0
 *
 * @param int $object_id
 */
function bbforms_submissions_on_delete_object( $object_id ) {

    global $wpdb, $ct_table;

    if( ! ( $ct_table instanceof CT_Table ) ) {
        return;
    }

    if( $ct_table->name !== 'bbforms_submissions' ) {
        return;
    }

    $submission = ct_get_object( $object_id );

    if( ! $submission ) {
        return;
    }

    // Init the file system
    bbforms_get_filesystem();

    $fields = bbforms_submissions_decode_fields( $submission );

    foreach( $fields as $name => $value ) {

        // Delete all files in this submission
        $result = ( ! is_array( $value )
            ? bbforms_submissions_delete_file( $value )
            : array_map( 'bbforms_submissions_delete_file', $value ) );
    }

}
add_action( 'delete_object', 'bbforms_submissions_on_delete_object' );

/**
 * Delete a file assigned to a submission
 *
 * @since 1.0.0
 *
 * @param string $path
 *
 * @return null|bool null if nothing deleted (since its not a file), bool for the deletion result
 */
function bbforms_submissions_delete_file( $path ) {

    global $wp_filesystem;

    if( is_array( $path ) ) {
        $result = null;

        foreach( $path as $sub_path ) {
            $result = bbforms_submissions_delete_file( $sub_path );
        }

        return $result;
    }

    // Check if file exists
    if( $wp_filesystem->exists( $path ) ) {
        // Get the file directory
        $dir = dirname( $path );

        // Check if dir is correct
        if( $wp_filesystem->is_dir( $dir ) ) {

            // Remove dir and all its files
            $result = $wp_filesystem->rmdir( $dir, true );

            // If can not remove the dir, lets remove the file and then try to remove the dir again
            if( $result === false ) {
                $wp_filesystem->delete( $path, true, 'f' );
                $result = $wp_filesystem->rmdir( $dir, true );
            }

            return $result;
        }
    }

    return null;

}