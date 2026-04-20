<?php
/**
 * Error Messages
 *
 * @package     BBForms\Error_Messages
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Registered error messages
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_error_messages() {
    return apply_filters( 'bbforms_error_messages', array(
        'date_error'            => __( 'Please enter a valid date.', 'bbforms' ),
        'email_error'           => __( 'Please enter a valid email address.', 'bbforms' ),
        'honeypot_error'        => __( 'Honeypot error.', 'bbforms' ),
        'number_error'          => __( 'Please enter a valid number.', 'bbforms' ),
        'quiz_error'            => __( 'The answer is incorrect.', 'bbforms' ),
        'time_error'            => __( 'Please enter a time between 00:00 and 23:59.', 'bbforms' ),
        'url_error'             => __( 'Please enter a valid URL.', 'bbforms' ),
        'file_error'            => __( 'There was an error uploading the file.', 'bbforms' ),
        'file_type_error'       => __( 'The uploaded file type is not allowed.', 'bbforms' ),
        'file_size_min_error'   => __( 'The uploaded file is too small.', 'bbforms' ),
        'file_size_max_error'   => __( 'The uploaded file is too large.', 'bbforms' ),
        // Common
        'required_error'        => __( 'This field is required.', 'bbforms' ),
        // min & max
        // translators: %s: Min value
        'min_error'             => __( 'Please enter a value greater or equal than %s.', 'bbforms' ),
        // translators: %s: Max value
        'max_error'             => __( 'Please enter a value lower or equal than %s.', 'bbforms' ),
        // translators: %1$s: Min value %2$s: Max value
        'min_max_error'         => __( 'Please enter a value between %1$s and %2$s.', 'bbforms' ),
        // pattern
        'pattern_error'         => __( 'Please match the requested format.', 'bbforms' ),

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
function bbforms_get_error_message( $key ) {

    $error_messages = bbforms_get_error_messages();

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
function bbforms_get_error_messages_from_settings() {
    $messages = bbforms_get_error_messages();

    foreach( $messages as $key => $message ) {

        $override = bbforms_get_option( $key, '' );

        if( $override !== '' ) {
            $messages[$key] = $override;
        }

    }

    return apply_filters( 'bbforms_get_error_messages_from_options', $messages );
}

/**
 * Registered error messages labels
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_error_messages_labels() {
    return apply_filters( 'bbforms_error_messages_labels', array(
        'date_error'            => bbforms_dashicon( 'calendar-alt' ) . ' ' . __( 'Incorrect Date', 'bbforms' ),
        'email_error'           => bbforms_dashicon( 'email' ) . ' ' . __( 'Incorrect Email', 'bbforms' ),
        'honeypot_error'        => bbforms_dashicon( 'buddicons-replies' ) . ' ' . __( 'Honeypot Error', 'bbforms' ),
        'number_error'          => bbforms_dashicon( 'bbforms-number' ) . ' ' . __( 'Incorrect Number', 'bbforms' ),
        'quiz_error'            => bbforms_dashicon( 'bbforms-quiz' ) . ' ' . __( 'Incorrect Answer', 'bbforms' ),
        'time_error'            => bbforms_dashicon( 'clock' ) . ' ' . __( 'Incorrect Time', 'bbforms' ),
        'url_error'             => bbforms_dashicon( 'admin-links' ) . ' ' . __( 'Incorrect URL', 'bbforms' ),
        'file_error'            => bbforms_dashicon( 'bbforms-file' ) . ' ' . __( 'File Upload Error', 'bbforms' ),
        'file_type_error'       => bbforms_dashicon( 'bbforms-file' ) . ' ' . __( 'File Type Error', 'bbforms' ),
        'file_size_min_error'   => bbforms_dashicon( 'bbforms-file' ) . ' ' . __( 'Min. File Size Error', 'bbforms' ),
        'file_size_max_error'   => bbforms_dashicon( 'bbforms-file' ) . ' ' . __( 'Max. File Size Error', 'bbforms' ),
        // Common
        'required_error'        => bbforms_dashicon( 'bbforms-required' ) . ' ' . __( 'Field Required', 'bbforms' ),
        // min & max
        'min_error'             => bbforms_dashicon( 'arrow-left-alt2' ) . ' ' . __( 'Minimum Error', 'bbforms' ),
        'max_error'             => bbforms_dashicon( 'arrow-right-alt2' ) . ' ' . __( 'Maximum Error', 'bbforms' ),
        'min_max_error'         => bbforms_dashicon( 'editor-code' ) . ' ' . __( 'Min. & Max. Error', 'bbforms' ),
        // pattern
        'pattern_error'         => bbforms_dashicon( 'bbforms-pattern' ) . ' ' . __( 'Pattern Mismatch', 'bbforms' ),

    ) );
}

/**
 * Registered error messages descriptions
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_error_messages_desc() {
    return apply_filters( 'bbforms_error_messages_desc', array(
        'date_error'            => __( 'Error displayed when an incorrect date was entered in a date field.', 'bbforms' ),
        'email_error'           => __( 'Error displayed when an incorrect email address was entered in an email field.', 'bbforms' ),
        'honeypot_error'        => __( 'Error displayed when a honeypot was filled (commonly only bots would fill them).', 'bbforms' ),
        'number_error'          => __( 'Error displayed when an incorrect number was entered in a number or range field.', 'bbforms' ),
        'quiz_error'            => __( 'Error displayed when an incorrect answer was entered in a quiz field.', 'bbforms' ),
        'time_error'            => __( 'Error displayed when an incorrect time was entered in a time field.', 'bbforms' ),
        'url_error'             => __( 'Error displayed when an incorrect URL was entered in a URL field.', 'bbforms' ),
        'file_error'            => __( 'Error displayed when there is an error uploading a file.', 'bbforms' ),
        'file_type_error'       => __( 'Error displayed when an uploaded file type does not matches with the required file type.', 'bbforms' ),
        'file_size_min_error'   => __( 'Error displayed when an uploaded file does not reach the minimum required file size.', 'bbforms' ),
        'file_size_max_error'   => __( 'Error displayed when an uploaded file exceeds the maximum allowed file size.', 'bbforms' ),
        // Common
        'required_error'        => __( 'Error displayed when a required field was not filled.', 'bbforms' ),
        // min & max
        'min_error'             => __( 'Error displayed when a value entered does not reach the minimum required (defined in "min" attribute).', 'bbforms' ),
        'max_error'             => __( 'Error displayed when a value entered exceeds the maximum allowed (defined in "max" attribute).', 'bbforms' ),
        'min_max_error'         => __( 'Error displayed when a value entered does not reach the minimum required or exceeds the maximum allowed (defined in "min" & "max" attributes).', 'bbforms' ),
        // pattern
        'pattern_error'         => __( 'Error displayed when a value entered does not matches with the required pattern (defined in "pattern" attribute).', 'bbforms' ),

    ) );
}