<?php
/**
 * Ajax Functions
 *
 * @package     BBForms\Ajax_Functions
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler to import form
 *
 * @since 1.0.0
 */
function bbforms_ajax_import_form() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'bbforms_admin', 'nonce' );

    if ( ! current_user_can( bbforms_get_manager_capability() ) ) {
        wp_send_json_error( __( 'Security check not passed.', 'bbforms' ) );
    }

    $import_from = ( isset( $_POST['import_from'] ) ? sanitize_text_field( wp_unslash( $_POST['import_from'] ) ) : '' );

    if( ! in_array( $import_from, array( 'code', 'file' ) ) ) {
        wp_send_json_error( __( 'Invalid import source.', 'bbforms' ) );
    }

    if( $import_from === 'code' ) {
        // Check code submitted
        // Do not sanitize, we use a separator like <!---------- FORM ----------> and wp_kses_post() breaks it
        // Instead, after parse the code, wp_kses_post() is parsed to each piece of code
        $code = ( isset( $_POST['code'] ) ? $_POST['code'] : '' );

        if( empty( $code ) ) {
            wp_send_json_error( __( 'Invalid import code.', 'bbforms' ) );
        }
    } else {

        // Check file submitted
        $file = ( isset( $_FILES['file'] ) ? $_FILES['file'] : '' );

        $wp_filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );

        if ( ! wp_match_mime_types( 'text/plain', $wp_filetype['type'] ) ) {
            wp_send_json_error( __( 'The uploaded file is not a valid TXT.', 'bbforms' ) );
        }

        // From the file, we only need its content, do not need to store it
        $code = @file_get_contents( $file['tmp_name'] );

        if( $code === false ) {
            wp_send_json_error( __( 'Could not read the uploaded file.', 'bbforms' ) );
        }

        if( empty( $code ) ) {
            wp_send_json_error( __( 'The uploaded file is empty.', 'bbforms' ) );
        }

    }

    // Import the form
    $form_id = bbforms_import_form_from_code( $code );

    if( $form_id !== false ) {
        $edit_link = ct_get_edit_link( 'bbforms_forms', $form_id );

        wp_send_json_success( $edit_link );
    } else {
        wp_send_json_error( __( 'Could not import this form.', 'bbforms' ) );
    }

}
add_action('wp_ajax_bbforms_import_form', 'bbforms_ajax_import_form');

/**
 * Helper function to import a form from code
 *
 * @since 1.0.0
 *
 * @param string $code
 *
 * @return int|false
 */
function bbforms_import_form_from_code( $code ) {

    $form = '';
    $actions = '';
    $options = '';
    // Since code is passed through wp_kses_post(), the separators change here
    $form_sep = '<!---------- FORM ---------->';
    $actions_sep = '<!---------- ACTIONS ---------->';
    $options_sep = '<!---------- OPTIONS ---------->';

    $has_form_section       = ( strpos( $code, $form_sep ) !== false );
    $has_actions_section    = ( strpos( $code, $actions_sep ) !== false );
    $has_options_section    = ( strpos( $code, $options_sep ) !== false );

    // Standardize line breaks
    $code = str_replace( "\r\n", "\n", $code );

    if( $has_actions_section ) {
        $form = explode( $actions_sep, $code )[0];
    } else if( $has_options_section ) {
        $form = explode( $options_sep, $code )[0];
    } else {
        $form = $code;
    }

    $form = str_replace( $form_sep . "\n\n", '', $form );

    // Extract the actions
    if( $has_actions_section ) {
        $actions = explode( $actions_sep . "\n\n", $code )[1];

        if( $has_options_section ) {
            $actions = explode( $options_sep, $actions )[0];
        }
    }

    // Extract the options
    if( $has_options_section ) {
        $options = explode( $options_sep . "\n\n", $code )[1];
    }

    // Sanitize the code
    $form = wp_kses_post( $form );
    $actions = wp_kses_post( $actions );
    $options = wp_kses_post( $options );

    if( empty( $form ) && empty( $actions ) && empty( $options ) ) {
        return false;
    }

    if( empty( $options ) ) {
        $options = bbforms_get_options_code();
    }

    ct_setup_table( 'bbforms_forms' );

    $form_data = array();

    $form_data['title'] = '';
    $form_data['form'] = bbforms_fill_editor_lines( $form );
    $form_data['actions'] = bbforms_fill_editor_lines( $actions );
    $form_data['options'] = bbforms_fill_editor_lines( $options );
    $form_data['author_id'] = get_current_user_id();
    $form_data['created_at'] = gmdate( 'Y-m-d H:i:s' );
    $form_data['updated_at'] = gmdate( 'Y-m-d H:i:s' );

    $form_id = ct_insert_object( $form_data );

    ct_reset_setup_table();

    return $form_id;

}


/**
 * AJAX handler to export submissions
 *
 * @since 1.0.0
 */
function bbforms_ajax_submissions_export_csv() {
    global $bbforms_form, $wpdb;

    // Security check, forces to die if not security passed
    check_ajax_referer( 'bbforms_admin', 'nonce' );

    if ( ! current_user_can( bbforms_get_manager_capability() ) ) {
        wp_send_json_error( __( 'Security check not passed.', 'bbforms' ) );
    }

    $page = ( isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1 );
    $form_id = ( isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0 );
    $limit = 100;
    $offset = ( $page - 1 ) * $limit;

    $form = new BBForms_Form( $form_id );

    if( ! $form->exists() ) {
        wp_send_json_error( __( 'No form provided.', 'bbforms' ) );
    }

    $bbforms_form = $form;

    bbforms_do_form_fields( $form );

    $ct_table = ct_setup_table( 'bbforms_submissions' );

    // Get a paginated list of submissions
    $submissions = $wpdb->get_results( $wpdb->prepare(
        "SELECT *
        FROM {$ct_table->db->table_name} AS s
        WHERE s.form_id = %d
        ORDER BY s.number DESC
        LIMIT %d OFFSET %d",
        $form_id,
        $limit,
        $offset
    ), ARRAY_A );

    $submissions_count = count( $submissions );

    $results = bbforms_process_submissions_for_csv( $submissions, ( $page === 1 ) );

    wp_send_json_success( array(
        'submissions' => $results,
        'count' => $submissions_count,
        'done' => $submissions_count < $limit,
    ) );
}
add_action('wp_ajax_bbforms_submissions_export_csv', 'bbforms_ajax_submissions_export_csv');

/**
 * Process submissions to be exported in a CSV
 *
 * @since 1.0.0
 *
 * @param array $submissions
 * @param bool  $include_headers
 *
 * @return array
 */
function bbforms_process_submissions_for_csv( $submissions, $include_headers = false ) {

    global $bbforms_form;

    $lines = array();
    $columns = array();

    $excluded_fields = bbforms_submissions_get_excluded_fields();

    foreach( $bbforms_form->fields as $name => $field ) {
        if( in_array( $field->bbcode, $excluded_fields ) ) {
            continue;
        }

        $columns[] = $name;
    }

    // CSV Headers
    if( $include_headers ) {

        $headers = array();

        $headers[] = __( '#', 'bbforms' );

        foreach( $bbforms_form->fields as $name => $field ) {
            if( in_array( $field->bbcode, $excluded_fields ) ) {
                continue;
            }

            $headers[] = $field->label;
        }

        $headers[] = __( 'Date', 'bbforms' );

        $lines[0] = apply_filters( 'bbforms_process_submission_headers_for_csv', $headers );

    }

    foreach( $submissions as $i => $submission ) {

        if( $include_headers ) {
            $i++;
        }

        $line = array();

        $line[] = $submission['number'];

        $fields = json_decode( $submission['fields'], true );

        foreach( $columns as $name ) {
            if( isset( $fields[ $name ] ) ) {
                $value = $fields[ $name ];

                if( is_array( $value ) ) {
                    if( is_array( $value[0] ) ) {
                        $str = '';

                        foreach( $value as $k => $v ) {
                            $str .= implode( ', ', $v ) . "\n";
                        }

                        $value = $str;
                    } else {
                        $value = implode( "\n", $value );
                    }


                }

                $line[] = $value;
            }
        }

        $line[] = $submission['created_at'];

        $lines[$i] = apply_filters( 'bbforms_process_submission_line_for_csv', $line, $submission );

    }

    return $lines;

}