<?php
/**
 * BBCodes
 *
 * @package     BBForms\BBCodes
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Base classes
require_once BBFORMS_DIR . 'includes/bbcodes/bbcode.php';
// BBCodes
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/b.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/center.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/code.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/color.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/email.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/font.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/i.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/iframe.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/img.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/justify.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/left.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/list.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/quote.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/right.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/row.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/s.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/size.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/table.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/u.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/url.php';
require_once BBFORMS_DIR . 'includes/bbcodes/bbcodes/youtube.php';

/**
 * Get registered bbcodes
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_bbcodes() {

    global $bbforms_bbcodes;

    if( ! is_array( $bbforms_bbcodes ) ) {
        $bbforms_bbcodes = array();
    }

    return $bbforms_bbcodes;

}

/**
 * Parse BBCodes
 *
 * @since 1.0.0
 *
 * @param BBForms_Form  $form               The form
 * @param int           $user_id            The user ID
 * @param string|array  $content            The content to parse
 *
 * @return string|array
 */
function bbforms_do_bbcodes( $form, $user_id, $content ) {

    global $bbforms_bbcodes, $bbforms_form;

    if( ! is_array( $bbforms_bbcodes ) ) {
        $bbforms_bbcodes = array();
    }

    $bbforms_form = $form;

    // Check if content given is an array to parse each array element
    if( is_array( $content ) ) {

        foreach( $content as $k => $v ) {
            // Replace all bbcodes on this array element
            $content[$k] = bbforms_do_bbcodes( $form, $user_id, $v );
        }

        return $content;

    }

    // Find all registered tag names in $content.
    preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );

    foreach( $matches[1] as $i => $match ) {
        if( bbforms_ends_with( $match, '*' ) ) {
            $matches[1][$i] = str_replace( '*', '', $match );
        }
    }

    $parsed_content = $content;

    // First, parse special bbcodes
    $parsed_content = bbforms_do_special_bbcodes(  $form, $user_id, $parsed_content );

    $bbcodes = $bbforms_bbcodes;
    unset( $bbcodes['email'] );
    unset( $bbcodes['url'] );

    $bbcodes = array_intersect( array_keys( $bbcodes ), $matches[1] );

    // Ensure at least 1 to prevent WordPress get shortcodes in get_shortcode_regex()
    if( empty( $bbcodes ) ) {
        $bbcodes = array( 'b' );
    }

    $pattern = get_shortcode_regex( $bbcodes );

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
    $parsed_content = apply_filters( 'bbforms_do_bbcodes', $parsed_content, $form, $user_id, $content );

    // Parse only found BBCodes
    $parsed_content = preg_replace_callback( "/$pattern/", 'bbforms_do_bbcode_tag', $parsed_content );

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
    $parsed_content = apply_filters( 'bbforms_post_do_bbcodes', $parsed_content, $form, $user_id, $content );

    return $parsed_content;

}

/**
 * Parse conflicting BBCodes (specially [email] & [url])
 *
 * @since 1.0.0
 *
 * @param stdClass      $form               The form
 * @param int           $user_id            The user ID
 * @param string|array  $content            The content to parse
 *
 * @return string|array
 */
function bbforms_do_special_bbcodes( $form, $user_id, $content ) {

    global $bbforms_bbcodes;

    $tags = array( 'email', 'url' );

    foreach( $tags as $tag ) {

        if( ! isset( $bbforms_bbcodes[ $tag ] ) ) continue;

        // [TAG=(attrs?)]CONTENT[/TAG]
        preg_match_all( "/\[" . $tag . "\=(.*?)\](.*?)\[\/" . $tag . "\]|\[" . $tag . "\](.*?)\[\/" . $tag . "\]/is", $content, $matches );

        foreach( $matches[0] as $i => $match ) {

            $attrs = array();

            // [TAG]CONTENT[/TAG]
            $bbcode_content = $matches[3][$i];

            // [TAG=(attrs)]CONTENT[/TAG]
            if( ! empty( $matches[1][$i] ) ) {
                $matches[1][$i] = '=' . $matches[1][$i];
                $attrs = bbforms_parse_atts( $matches[1][$i] );

                $bbcode_content = $matches[2][$i];
            }

            $replacement = call_user_func( 'bbforms_do_bbcode', $attrs, $bbcode_content, $tag );
            $content = str_replace( $match, $replacement, $content );

        }

    }

    return apply_filters( 'bbforms_do_special_bbcodes', $content );

}

/**
 * Regular Expression callable for do_shortcode() for calling shortcode hook.
 *
 * @see get_shortcode_regex() for details of the match array contents.
 *
 * @since 2.5.0
 * @access private
 *
 * @global array $bbforms_bbcodes
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
function bbforms_do_bbcode_tag( $m ) {
    global $bbforms_bbcodes, $bbforms_form;

    // Allow [[foo]] syntax for escaping a tag.
    if ( '[' === $m[1] && ']' === $m[6] ) {
        return substr( $m[0], 1, -1 );
    }

    // Check if tag exists
    $tag  = $m[2];

    if ( ! isset( $bbforms_bbcodes[ $tag ] ) ) {
        _doing_it_wrong(
            __FUNCTION__,
            /* translators: %s: BBCode tag. */
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
    $return = apply_filters( 'bbforms_pre_do_bbcode_tag', false, $tag, $attr, $m );
    if ( false !== $return ) {
        return $return;
    }

    $content = isset( $m[5] ) ? $m[5] : null;

    if( $content !== null ) {
        $content = bbforms_do_bbcodes( $bbforms_form, $bbforms_form->user_id, $content );
        $content = do_shortcode( $content );
    }

    $output = $m[1] . call_user_func( 'bbforms_do_bbcode', $attr, $content, $tag ) . $m[6];

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
    return apply_filters( 'bbforms_do_bbcode_tag', $output, $tag, $attr, $m );
}

/**
 * Does a BBCode
 *
 * @since 1.0.0
 *
 * @param array         $attr       Attributes
 * @param string|null   $content    Content
 * @param string        $tag        BBCode
 *
 * @return string
 */
function bbforms_do_bbcode( $attr, $content, $tag ) {

    global $bbforms_bbcodes;

    if( isset( $bbforms_bbcodes[ $tag ] ) ) {
        return $bbforms_bbcodes[ $tag ]->render( $attr, $content );
    }

    return '';

}