<?php
/**
 * Form Messages
 *
 * @package     BBForms\Form_Messages
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Registered form messages
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_form_messages() {
    return apply_filters( 'bbforms_form_messages', array(
        // translators: %s: *
        'required_fields_message'       => sprintf( __( 'Fields marked with an %s are required', 'bbforms' ), '<span class="bbforms-required">*</span>' ),
        'submit_success_message'        => __( 'Form submitted successfully!', 'bbforms' ),
        'submit_error_message'          => __( 'Please, correct errors before submitting this form.', 'bbforms' ),
        'unknown_submit_error_message'  => __( 'Unknown error while processing your submission.', 'bbforms' ),
        'require_login_message'         => __( 'You must be logged in to view this form.', 'bbforms' ),
        'unique_field_message'          => __( 'A form with this value has already been submitted.', 'bbforms' ),
        'submissions_limit_message'     => __( 'This form has reached it\'s submission limit.', 'bbforms' ),
    ) );
}

/**
 * Get a error message (overriding it by the one entered in settings)
 *
 * @since 1.0.0
 *
 * @param string $key Error message key
 *
 * @return string
 */
function bbforms_get_form_message( $key ) {

    $error_messages = bbforms_get_form_messages();

    if( isset( $error_messages[ $key ] ) ) {

        $override = bbforms_get_option( $key, '' );

        if( $override !== '' ) {
            return $override;
        }

        return $error_messages[ $key ];
    }

    return '';

}

/**
 * Get all error messages overriding them by the ones entered in settings
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_form_messages_from_settings() {
    $messages = bbforms_get_form_messages();

    foreach( $messages as $key => $message ) {

        $override = bbforms_get_option( $key, '' );

        if( $override !== '' ) {
            $messages[$key] = $override;
        }

    }

    return apply_filters( 'bbforms_get_form_messages_from_options', $messages );
}

/**
 * Registered error messages labels
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_form_messages_labels() {
    return apply_filters( 'bbforms_form_messages_labels', array(
        'required_fields_message'       => bbforms_dashicon( 'bbforms-required' ) . ' ' . __( 'Fields Required', 'bbforms' ),
        'submit_success_message'        => bbforms_dashicon( 'yes-alt' ) . ' ' . __( 'Submit Success', 'bbforms' ),
        'submit_error_message'          => bbforms_dashicon( 'dismiss' ) . ' ' . __( 'Submit Error', 'bbforms' ),
        'unknown_submit_error_message'  => bbforms_dashicon( 'editor-help' ) . ' ' . __( 'Unknown Submit Error', 'bbforms' ),
        'require_login_message'         => bbforms_dashicon( 'bbforms-user' ) . ' ' . __( 'Require Login', 'bbforms' ),
        'unique_field_message'          => bbforms_dashicon( 'star-filled' ) . ' ' .  __( 'Unique Field', 'bbforms' ),
        'submissions_limit_message'     => bbforms_dashicon( 'filter' ) . ' ' .  __( 'Submissions Limit', 'bbforms' ),
    ) );
}

/**
 * Registered error messages descriptions
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_form_messages_desc() {
    return apply_filters( 'bbforms_form_messages_desc', array(
        'required_fields_message'       => __( 'Message displayed at top of the form when the form contains required fields.', 'bbforms' ),
        'submit_success_message'        => __( 'Message displayed when the form gets submitted successfully.', 'bbforms' ),
        'submit_error_message'          => __( 'Message displayed when any of the form fields has found an incorrect value.', 'bbforms' ),
        'unknown_submit_error_message'  => __( 'Message displayed when there is an unknown error while processing a from submission.', 'bbforms' ),
        'require_login_message'         => __( 'Message displayed when to not logged in users when the form requires to be logged in.', 'bbforms' ),
        'unique_field_message'          => __( 'Message displayed when a duplicated value is found for a unique field.', 'bbforms' ),
        'submissions_limit_message'     => __( 'Message displayed when the form has reached the submissions limit.', 'bbforms' ),
    ) );
}