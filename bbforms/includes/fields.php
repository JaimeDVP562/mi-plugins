<?php
/**
 * Fields
 *
 * @package     BBForms\Fields
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Base classes
require_once BBFORMS_DIR . 'includes/fields/field.php';
// Fields
require_once BBFORMS_DIR . 'includes/fields/fields/check.php';
require_once BBFORMS_DIR . 'includes/fields/fields/country.php';
require_once BBFORMS_DIR . 'includes/fields/fields/date.php';
require_once BBFORMS_DIR . 'includes/fields/fields/email.php';
require_once BBFORMS_DIR . 'includes/fields/fields/file.php';
require_once BBFORMS_DIR . 'includes/fields/fields/hidden.php';
require_once BBFORMS_DIR . 'includes/fields/fields/honeypot.php';
require_once BBFORMS_DIR . 'includes/fields/fields/number.php';
require_once BBFORMS_DIR . 'includes/fields/fields/password.php';
require_once BBFORMS_DIR . 'includes/fields/fields/quiz.php';
require_once BBFORMS_DIR . 'includes/fields/fields/radio.php';
require_once BBFORMS_DIR . 'includes/fields/fields/range.php';
require_once BBFORMS_DIR . 'includes/fields/fields/reset.php';
require_once BBFORMS_DIR . 'includes/fields/fields/select.php';
require_once BBFORMS_DIR . 'includes/fields/fields/submit.php';
require_once BBFORMS_DIR . 'includes/fields/fields/tel.php';
require_once BBFORMS_DIR . 'includes/fields/fields/text.php';
require_once BBFORMS_DIR . 'includes/fields/fields/textarea.php';
require_once BBFORMS_DIR . 'includes/fields/fields/time.php';
require_once BBFORMS_DIR . 'includes/fields/fields/url.php';

/**
 * Get registered fields
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_fields() {

    global $bbforms_fields;

    if( ! is_array( $bbforms_fields ) ) {
        $bbforms_fields = array();
    }

    return $bbforms_fields;

}

/**
 * Do form fields
 *
 * @since 1.0.0
 *
 * @param BBForms_Form $form Form object
 */
function bbforms_do_form_fields( $form ) {

    // Sanitize
    $content = wp_kses_post( $form->form );

    // Parse tags (for performance, parse tags first)
    $content = bbforms_do_tags( $form, $form->user_id, $content );

    // Parse BBCode
    $content = bbforms_do_bbcodes( $form, $form->user_id, $content );

    // Parse Shortcodes
    $content = do_shortcode( $content );

    // Honeypot field
    if( ! $form->options['disable_honeypot'] ) {
        $label = __( 'If you can see this field, please leave it empty.', 'bbforms' );
        $content .= '[honeypot name="_bbforms_hp_' . $form->id . '" label="' . $label . '"]';
    }

    // Parse Fields
    $content = bbforms_do_fields( $form, $form->user_id, $content );

    // Replace return carets to prevent incorrect line breaks
    $content = str_replace( "\r", "", $content );

    // Prevent turn textarea \n into <br />
    $content = preg_replace_callback( '/<(textarea).*?<\/\\1>/s', '_autop_newline_preservation_helper', $content );

    // WP parsers
    $content = wpautop( $content );

    return apply_filters( 'bbforms_do_form_fields', $content, $form );

}

/**
 * Parse Fields
 *
 * @since 1.0.0
 *
 * @param stdClass      $form               The form
 * @param int           $user_id            The user ID
 * @param string|array  $content            The content to parse
 *
 * @return string|array
 */
function bbforms_do_fields( $form, $user_id, $content ) {

    global $bbforms_fields, $bbforms_form;

    if( ! is_array( $bbforms_fields ) ) {
        $bbforms_fields = array();
    }

    $bbforms_form = $form;

    // Check if content given is an array to parse each array element
    if( is_array( $content ) ) {

        foreach( $content as $k => $v ) {
            // Replace all fields on this array element
            $content[$k] = bbforms_do_fields( $form, $user_id, $v );
        }

        return $content;

    }

    // Find all registered tag names in $content.
    preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );

    foreach( $matches[1] as $i => $match ) {

        if( bbforms_ends_with( $match, '*' ) ) {
            $bbforms_form->has_required_fields = true;

            // Prevent to pass email* or tel* removing the *
            $matches[1][$i] = str_replace( '*', '', $match );
        }
    }

    $parsed_content = $content;

    // Turn ] into <cbracket>
    $parsed_content = preg_replace_callback( '/="([^"]*)"/', function($match) {
        return str_replace( ']', '<cbracket>', $match[0] );
    }, $parsed_content);

    $fields = array_intersect( array_keys( $bbforms_fields ), $matches[1] );

    // Ensure at least 1 to prevent WordPress get shortcodes in get_shortcode_regex()
    if( empty( $fields ) ) {
        $fields = array( 'text' );
    }

    $pattern = get_shortcode_regex( $fields );

    /**
     * Available filter to setup custom replacements
     *
     * @since 1.0.0
     *
     * @param string    $parsed_content     Content parsed
     * @param stdClass  $form               The form
     * @param int       $user_id            The user ID
     * @param string    $content            The content to parse
     *
     * @return string
     */
    $parsed_content = apply_filters( 'bbforms_do_fields', $parsed_content, $form, $user_id, $content );

    // Parse only found Fields
    $parsed_content = preg_replace_callback( "/$pattern/", 'bbforms_do_field_tag', $parsed_content );

    // Restore ]
    $parsed_content = str_replace( '<cbracket>', ']', $parsed_content );

    /**
     * Available filter to setup custom replacements (with parsed content parsed)
     *
     * @since 1.0.0
     *
     * @param string    $parsed_content     Content parsed
     * @param stdClass  $form               The form
     * @param int       $user_id            The user ID
     * @param string    $content            The content to parse
     *
     * @return string
     */
    $parsed_content = apply_filters( 'bbforms_post_do_fields', $parsed_content, $form, $user_id, $content );

    return $parsed_content;

}

/**
 * Regular Expression callable for do_shortcode() for calling shortcode hook.
 *
 * @see get_shortcode_regex() for details of the match array contents.
 *
 * @since 2.5.0
 * @access private
 *
 * @global array $bbforms_fields
 *
 * @param array $m {
 *     Regular expression match array.
 *
 *     @type string $0 Entire matched shortcode text.
 *     @type string $1 Optional second opening bracket for escaping shortcodes.
 *     @type string $2 Shortcode name.
 *     @type string $3 Shortcode arguments list.
 *     @type string $4 Optional self closing slash.
 *     @type string $5 Content of a shortcode when it wraps some content.
 *     @type string $6 Optional second closing bracket for escaping shortcodes.
 * }
 * @return string Shortcode output.
 */
function bbforms_do_field_tag( $m ) {
    global $bbforms_fields;

    // Allow [[foo]] syntax for escaping a tag.
    if ( '[' === $m[1] && ']' === $m[6] ) {
        return substr( $m[0], 1, -1 );
    }

    // Check if tag exists
    $tag  = $m[2];

    if ( ! isset( $bbforms_fields[ $tag ] ) ) {
        _doing_it_wrong(
            __FUNCTION__,
            /* translators: %s: Field tag. */
            sprintf( esc_html__( 'Attempting to parse a BBCODE without a valid callback: %s', 'bbforms' ), esc_html( $tag ) ),
            '4.3.0'
        );
        return $m[0];
    }

    // parse parameters
    $attrs = $m[3];

    // Custom shortcode_parse_atts()
    $attr = bbforms_parse_atts( $attrs );

    /**
     * Filters whether to call a shortcode callback.
     *
     * Returning a non-false value from filter will short-circuit the
     * shortcode generation process, returning that value instead.
     *
     * @since 4.7.0
     * @since 6.5.0 The `$attr` parameter is always an array.
     *
     * @param false|string $output Short-circuit return value. Either false or the value to replace the shortcode with.
     * @param string       $tag    Shortcode name.
     * @param array        $attr   Shortcode attributes array, can be empty if the original arguments string cannot be parsed.
     * @param array        $m      Regular expression match array.
     */
    $return = apply_filters( 'bbforms_pre_do_field_tag', false, $tag, $attr, $m );
    if ( false !== $return ) {
        return $return;
    }

    $content = isset( $m[5] ) ? $m[5] : null;

    $output = $m[1] . call_user_func( 'bbforms_do_field', $attr, $content, $tag ) . $m[6];

    /**
     * Filters the output created by a shortcode callback.
     *
     * @since 4.7.0
     * @since 6.5.0 The `$attr` parameter is always an array.
     *
     * @param string $output Shortcode output.
     * @param string $tag    Shortcode name.
     * @param array  $attr   Shortcode attributes array, can be empty if the original arguments string cannot be parsed.
     * @param array  $m      Regular expression match array.
     */
    return apply_filters( 'bbforms_do_field_tag', $output, $tag, $attr, $m );
}

/**
 * Does a BBCode field
 *
 * @since 1.0.0
 *
 * @param array         $attr       Attributes
 * @param string|null   $content    Content
 * @param string        $tag        BBCode
 *
 * @return string
 */
function bbforms_do_field( $attr, $content, $tag ) {

    global $bbforms_fields;

    if( isset( $bbforms_fields[ $tag ] ) ) {
        if( defined('BBFORMS_DOING_SUBMIT') && BBFORMS_DOING_SUBMIT ) {
            $bbforms_fields[ $tag ]->submit( $attr, $content );
        } else {
            return $bbforms_fields[ $tag ]->render( $attr, $content );
        }

    }

    return '';

}