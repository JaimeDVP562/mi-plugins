<?php
/**
 * Submit
 *
 * @package     BBForms\Submit
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Form submit
 *
 * @since 1.0.0
 *
 * @param array $request
 */
function bbforms_form_submit( $request ) {

    global $bbforms_form, $bbforms_request, $bbforms_response;

    define( 'BBFORMS_DOING_SUBMIT', true );

    $bbforms_response = array(
        'success' => true,
        'form_id' => 0,
        'messages' => array(),
        'field_messages' => array(),
        'actions' => array(),
        'options' => array(),
    );

    // Check nonce
    if( ! isset( $request['_bbforms_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $request['_bbforms_nonce'] ) ), 'bbforms' ) ) {
        wp_send_json_error( __( 'Security check not passed.', 'bbforms' ) );
    }

    // Check form
    if ( ! isset( $request['_bbforms'] ) ) {
        wp_send_json_error( __( 'No form provided.', 'bbforms' ) );
    }

    $form = new BBForms_Form( absint( $request['_bbforms'] ) );

    if( ! $form->exists() ) {
        wp_send_json_error( __( 'Form not found.', 'bbforms' ) );
    }

    $bbforms_form = $form;
    $bbforms_request = $request;

    $bbforms_response['form_id'] = $form->id;

    // Check if form requires login
    if( $form->options['require_login'] && $form->user_id === 0 ) {
        $bbforms_response['success'] = false;
        $bbforms_response['messages']['require_login_error'] = array(
            'text' => $form->options['require_login_message'],
            'type' => 'warning',
        );
        bbforms_send_response();
    }

    // Check if submissions exceeded
    if( bbforms_form_reached_submissions_limit( $form->id, $form->options['submissions_limit'] ) ) {
        $bbforms_response['success'] = false;
        $bbforms_response['messages']['submissions_limit_error'] = array(
            'text' => $form->options['submissions_limit_message'],
            'type' => 'warning',
        );
        bbforms_send_response();
    }


    // Set hidden fields
    bbforms_set_form_hidden_fields( $bbforms_request );

    // Process Fields, constant BBFORMS_DOING_SUBMIT makes them to only process data
    bbforms_do_form_fields( $form );

    // Unique field
    bbforms_maybe_check_unique_field();

    // Process actions
    bbforms_maybe_process_actions();

    // Submit hooks
    bbforms_do_sumit_hooks();

    // Return the response
    bbforms_send_response();

}

/**
 * Check form submit on request
 *
 * @since 1.0.0
 */
function bbforms_maybe_form_submit() {

    if ( isset( $_POST['_bbforms'] ) ) {
        bbforms_form_submit( $_POST );
    }

}
add_action( 'init', 'bbforms_maybe_form_submit' );

/**
 * Ajax form submit
 *
 * @since 1.0.0
 */
function bbforms_ajax_form_submit() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'bbforms', 'nonce' );

    bbforms_form_submit( $_POST );

}
add_action( 'wp_ajax_bbforms_form_submit', 'bbforms_ajax_form_submit' );
add_action( 'wp_ajax_nopriv_bbforms_form_submit', 'bbforms_ajax_form_submit' );

/**
 * Set hidden fields from request
 *
 * @since 1.0.0
 *
 * @param array $request
 */
function bbforms_set_form_hidden_fields( $request ) {

    global $bbforms_form;

    $hidden_fields = bbforms_get_hidden_fields();

    foreach ( $hidden_fields as $name => $default_value ) {

        $value = isset( $request[$name] ) ? $request[$name] : $default_value;

        switch( $name ) {
            case '_bbforms':
            case '_bbforms_post':
                $sanitized_value = absint( $value );
                break;
            default:
                $sanitized_value = apply_filters( 'bbforms_sanitize_hidden_field', sanitize_text_field( $value ), $name );
                break;
        }

        $bbforms_form->fields[$name] = (object) array(
            'bbcode' => 'hidden',
            'value' => $value,
            'sanitized_value' => $sanitized_value,
        );
    }

    do_action( 'bbforms_set_form_hidden_fields', $bbforms_form, $request );

}

/**
 * Send submit response
 *
 * @since 1.0.0
 */
function bbforms_send_response() {

    global $bbforms_form, $bbforms_response, $bbforms_request;

    // On success, append actions
    if( $bbforms_response['success'] ) {
        $bbforms_response['options']['hide_form_on_success'] = $bbforms_form->options['hide_form_on_success'];
        $bbforms_response['options']['clear_form_on_success'] = $bbforms_form->options['clear_form_on_success'];
    } else {
        if( count( $bbforms_response['messages'] ) === 0 ) {
            $bbforms_response['messages']['submit_error_message'] = array(
                'text' => bbforms_get_form_message( 'submit_error_message' ),
                'type' => 'error',
            );
        }
    }

    // Apply parsers on messages
    if( isset( $bbforms_response['messages'] ) && is_array( $bbforms_response['messages'] ) ) {

        foreach ( $bbforms_response['messages'] as $i => $message ) {

            $text = $message['text'];
            $text = bbforms_do_tags( $bbforms_form, $bbforms_form->user_id, $text );
            $text = bbforms_do_bbcodes( $bbforms_form, $bbforms_form->user_id, $text );
            $text = do_shortcode( $text );
            //$text = wpautop( $text );

            $bbforms_response['messages'][$i]['text'] = $text;
        }

    }

    if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        // Ajax submit
        if( $bbforms_response['success'] ) {
            wp_send_json_success( $bbforms_response );
        } else {
            wp_send_json_error( $bbforms_response );
        }
    } else {
        $uri = bbforms_get_request_uri();

        if( isset( $bbforms_response['actions'] ) && is_array( $bbforms_response['actions'] ) && isset( $bbforms_response['actions']['redirect'] ) ) {
            $uri = $bbforms_response['actions']['redirect']['to'];
        }

        // Add query args with the response & request (the request is to retrieve again the form values)
        $uri = add_query_arg( array( 'bbforms_response' => $bbforms_response ), $uri );
        $uri = add_query_arg( array( 'bbforms_request' => $bbforms_request ), $uri );

        // Request submit
        wp_redirect( $uri );
        exit;
    }

}

/**
 * Does submit hooks (to allow plugins to extend them)
 *
 * @since 1.0.0
 */
function bbforms_do_sumit_hooks() {

    global $bbforms_form, $bbforms_request, $bbforms_response;

    if( $bbforms_response['success'] ) {
        do_action( 'bbforms_form_submit_success', $bbforms_form, $bbforms_request, $bbforms_response );
    } else {
        do_action( 'bbforms_form_submit_error', $bbforms_form, $bbforms_request, $bbforms_response );
    }

}

/**
 * Check if form is limited by unique field
 *
 * @since 1.0.0
 */
function bbforms_maybe_check_unique_field() {
    global $bbforms_form, $bbforms_request, $bbforms_response;

    $unique_field = $bbforms_form->options['unique_field'];

    // Check if unique field exists in the form
    if( ! empty( $unique_field ) && isset( $bbforms_form->fields[$unique_field] ) ) {
        if( bbforms_check_unique_field_value( $bbforms_form->id, $unique_field, $bbforms_form->fields[$unique_field]->sanitized_value ) ) {
            $bbforms_response['success'] = false;
            $bbforms_response['messages']['unique_field_error'] = array(
                'text' => $bbforms_form->options['unique_field_message'],
                'type' => 'warning',
            );
            bbforms_send_response();
        }
    }
}

/**
 * Check if should process form actions after submit
 *
 * @since 1.0.0
 */
function bbforms_maybe_process_actions() {

    global $bbforms_form, $bbforms_request, $bbforms_response;

    if( ! $bbforms_response['success'] ) {
        return;
    }

    do_action( 'bbforms_before_process_form_actions', $bbforms_form, $bbforms_request, $bbforms_response );

    // Sanitize as textarea
    $content = wp_kses_post( $bbforms_form->actions );

    // Parse tags (for performance, parse tags first
    $content = bbforms_do_tags( $bbforms_form, $bbforms_form->user_id, $content );

    // Parse BBCodes
    $content = bbforms_do_bbcodes( $bbforms_form, $bbforms_form->user_id, $content );

    // Parse Shortcodes
    $content = do_shortcode( $content );

    bbforms_do_actions( $bbforms_form, $bbforms_form->user_id, $content );

    do_action( 'bbforms_after_process_form_actions', $bbforms_form, $bbforms_request, $bbforms_response );

}

