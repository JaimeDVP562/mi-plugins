<?php
/**
 * Actions
 *
 * @package     BBForms\Actions
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Base classes
require_once BBFORMS_DIR . 'includes/actions/action.php';
// Actions
require_once BBFORMS_DIR . 'includes/actions/actions/delete_request.php';
require_once BBFORMS_DIR . 'includes/actions/actions/email.php';
require_once BBFORMS_DIR . 'includes/actions/actions/export_request.php';
require_once BBFORMS_DIR . 'includes/actions/actions/message.php';
require_once BBFORMS_DIR . 'includes/actions/actions/record.php';
require_once BBFORMS_DIR . 'includes/actions/actions/redirect.php';

/**
 * Get registered actions
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_actions() {

    global $bbforms_actions;

    if( ! is_array( $bbforms_actions ) ) {
        $bbforms_actions = array();
    }

    return $bbforms_actions;

}

/**
 * Parse Actions
 *
 * @since 1.0.0
 *
 * @param stdClass      $form               The form
 * @param int           $user_id            The user ID
 * @param string|array  $content            The content to parse
 *
 * @return string|array
 */
function bbforms_do_actions( $form, $user_id, $content ) {

    global $bbforms_actions, $bbforms_form;

    if( ! is_array( $bbforms_actions ) ) {
        $bbforms_actions = array();
    }

    $bbforms_form = $form;

    // Check if content given is an array to parse each array element
    if( is_array( $content ) ) {

        foreach( $content as $k => $v ) {
            // Replace all actions on this array element
            $content[$k] = bbforms_do_actions( $form, $user_id, $v );
        }

        return $content;

    }

    // Find all registered tag names in $content.
    preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );

    foreach( $matches[1] as $i => $match ) {
        if( bbforms_ends_with( $match, '*' ) ) {
            $bbforms_form->has_required_fields = true;
            $matches[1][$i] = str_replace( '*', '', $match );
        }
    }

    $parsed_content = $content;

    $actions = array_intersect( array_keys( $bbforms_actions ), $matches[1] );
    $pattern = get_shortcode_regex( $actions );

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
    $parsed_content = apply_filters( 'bbforms_do_actions', $parsed_content, $form, $user_id, $content );

    // Parse only found actions
    $parsed_content = preg_replace_callback( "/$pattern/", 'bbforms_do_action_tag', $parsed_content );

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
    $parsed_content = apply_filters( 'bbforms_post_do_actions', $parsed_content, $form, $user_id, $content );

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
 * @global array $bbforms_actions
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
function bbforms_do_action_tag( $m ) {
    global $bbforms_actions;

    // Allow [[foo]] syntax for escaping a tag.
    if ( '[' === $m[1] && ']' === $m[6] ) {
        return substr( $m[0], 1, -1 );
    }

    // Check if tag exists
    $tag  = $m[2];

    if ( ! isset( $bbforms_actions[ $tag ] ) ) {
        _doing_it_wrong(
            __FUNCTION__,
            /* translators: %s: BBCode tag. */
            sprintf( esc_html__( 'Attempting to parse an ACTION without a valid callback: %s', 'bbforms' ), esc_html( $tag ) ),
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
    $return = apply_filters( 'bbforms_pre_do_action_tag', false, $tag, $attr, $m );
    if ( false !== $return ) {
        return $return;
    }

    $content = isset( $m[5] ) ? $m[5] : null;

    $output = $m[1] . call_user_func( 'bbforms_do_action', $attr, $content, $tag ) . $m[6];

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
    return apply_filters( 'bbforms_do_action_tag', $output, $tag, $attr, $m );
}

/**
 * Does a BBCode action
 *
 * @since 1.0.0
 *
 * @param array         $attr       Attributes
 * @param string|null   $content    Content
 * @param string        $tag        BBCode
 *
 * @return string
 */
function bbforms_do_action( $attr, $content, $tag ) {

    global $bbforms_actions;

    if( isset( $bbforms_actions[ $tag ] ) ) {
        $bbforms_actions[ $tag ]->process( $attr, $content );
    }

    return '';

}