<?php
/**
 * Functions
 *
 * @package     BBForms\Functions
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Generates the required HTML with the dashicon provided
 *
 * @since 1.0.0
 *
 * @param string $dashicon      Dashicon class
 * @param string $tag           Optional, tag used (recommended i or span)
 *
 * @return string
 */
function bbforms_dashicon( $dashicon = 'bbforms', $tag = 'i' ) {

    return '<' . $tag . ' class="dashicons dashicons-' . $dashicon . '"></' . $tag . '>';

}

/**
 * Helper function to turn an array intro a string of attributes (attr="value")
 *
 * @since 1.0.0
 *
 * @param array     $attrs              Attributes to pass
 * @param array     $excluded_attrs     Attributes to exclude
 * @param string    $quote              Quotes to use (" or ')
 *
 * @return string
 */
function bbforms_concat_attrs( $attrs, $excluded_attrs = array(), $quote = '"' ) {

    $attributes = '';

    foreach ( $attrs as $attr => $val ) {

        $excluded = in_array( $attr, (array) $excluded_attrs, true );
        $empty = $val === '';

        if ( ! $excluded & ! $empty ) {
            $val = is_array( $val ) ? implode( ',', $val ) : $val;
            $val = esc_attr( $val );
            $attributes .= sprintf( ' %1$s=%3$s%2$s%3$s', $attr, $val, $quote );
        }
    }

    return $attributes;

}

/**
 * Helper function to parse atts
 *
 * @since 1.0.0
 *
 * @param string $attrs              Attributes string to prepare
 *
 * @return array
 */
function bbforms_parse_atts( $attrs ) {
    // Turn text* into text required="required"
    if( isset( $attrs[0] ) && $attrs[0] === '*' ) {
        $attrs = substr_replace( $attrs, ' required="required"', 0, 1 );
    }

    // Turn color=VALUE into color value=VALUE
    if( isset( $attrs[0] ) && $attrs[0] === '=' ) {
        $attrs = substr_replace( $attrs, ' value=', 0, 1 );
    }

    // Double quote parameters (id=1 class=2 into id="1" class="2")
    $attrs = bbforms_prepare_attrs( $attrs );

    $attr = shortcode_parse_atts( $attrs );

    // Restore double quotes
    foreach( $attr as $a => $value ) {
        $attr[$a] = str_replace( '<dquote>', '"', $value );
        $attr[$a] = str_replace( '<cbracket>', ']', $value );
    }

    return $attr;
}

/**
 * Helper function to prepare a string of attrs (commonly, adding quotes and escaping)
 * id=1 class=Hi "Ruben" into id="1" class="Hi \"Ruben\""
 *
 * @since 1.0.0
 *
 * @param string $attrs              Attributes string to prepare
 *
 * @return string
 */
function bbforms_prepare_attrs( $attrs ) {

    $attrs = preg_replace_callback( "/<([^>]*)>/", 'bbforms_prepare_attrs_keep_html', $attrs );

    preg_match_all( "/ \w+\=(.*?)/", $attrs, $matches );
    $parts = preg_split( "/ \w+\=(.*?)/", $attrs );

    $new_attrs = array();

    foreach( $matches[0] as $i => $match ) {

        if( isset( $parts[$i+1] ) && ! empty( $parts[$i+1] ) ) {
            $attr_value = trim( $parts[$i+1] );
            $j = strlen( $attr_value ) - 1; // To get the last character of the string

            if( $attr_value[0] !== '"' || $attr_value[$j] !== '"' ) {
                // Escape double quotes
                $attr_value = str_replace( '"', '<dquote>', $attr_value );

                if( $attr_value[0] === "\\"
                    && $attr_value[1] === "'"
                    && $attr_value[$j-1] === "\\"
                    && $attr_value[$j] === "'" ) {

                    // Turn single quotes into double quotes
                    $pos = strrpos( $attr_value, "\'" );
                    $attr_value = substr_replace( $attr_value, '"', 0, 2 ); // Replace first
                    $attr_value = substr_replace( $attr_value, '"', $pos-1, 2 ); // Replace last

                } else {
                    // Add the double quotes
                    $attr_value = '"' . $attr_value . '"';
                }


            }

            $new_attrs[] = $matches[0][$i] . $attr_value;
        }

    }

    $attrs = implode(' ', $new_attrs );
    $attrs = preg_replace_callback( "/<([^>]*)>/", 'bbforms_prepare_attrs_restore_html', $attrs );

    return $attrs;

}

/**
 * Replace = to EQUAL to keep HTML intact while preparing attrs
 *
 * @since 1.0.0
 *
 * @param array $matches
 *
 * @return string
 */
function bbforms_prepare_attrs_keep_html( $matches ) {
    return str_replace( '=', 'EQUAL', $matches[0] );
}

/**
 * Restore = in HTML while preparing attrs
 *
 * @since 1.0.0
 *
 * @param array $matches
 *
 * @return string
 */
function bbforms_prepare_attrs_restore_html( $matches ) {
    return str_replace( 'EQUAL', '=', $matches[0] );
}

/**
 * Helper function to get the current request URI
 *
 * @since 1.0.0
 *
 * @return string
 */
function bbforms_get_request_uri() {
    if( ! isset( $_SERVER["REQUEST_URI"] ) ) {
        return '';
    }

    $request_uri = stripslashes( rawurldecode( wp_unslash( $_SERVER["REQUEST_URI"] ) ) );

    return $request_uri;
}

/**
 * Helper function to get a form message HTML
 *
 * @since 1.0.0
 *
 * @param string $message
 * @param string $type
 *
 * @return string
 */
function bbforms_get_message_html( $message, $type = 'success' ) {

    global $bbforms_form;

    $message = bbforms_do_tags( $bbforms_form, $bbforms_form->user_id, $message );
    $message = bbforms_do_bbcodes( $bbforms_form, $bbforms_form->user_id, $message );
    $message = do_shortcode( $message );
    //$message = wpautop( $message );

    return apply_filters( 'bbforms_message_html', '<div class="bbforms-message bbforms-' . esc_attr( $type ) . '-message">' . $message . '</div>', $message, $type );

}

/**
 * Helper function to check if a string starts by needle string given
 *
 * @since 1.0.0
 *
 * @param string $haystack
 * @param string $needle
 *
 * @return bool
 */
function bbforms_starts_with( $haystack, $needle ) {
    return strncmp( $haystack, $needle, strlen( $needle ) ) === 0;
}

/**
 * Helper function to check if a string ends by needle string given
 *
 * @since 1.0.0
 *
 * @param string $haystack
 * @param string $needle
 *
 * @return bool
 */
function bbforms_ends_with( $haystack, $needle ) {
    return $needle === '' || substr_compare( $haystack, $needle, -strlen( $needle ) ) === 0;
}

/**
 * Helper function to meet if option is enabled
 * Enabled means that is different than '', '0', 'no', 'false', 'off', false, 0, null
 *
 * @since 1.0.0
 *
 * @param string $value
 *
 * @return bool
 */
function bbforms_is_option_enabled( $value ) {

    $true_values = array( '1', 'yes', 'true', 'on', true, 1 );
    $false_values = array( '', '0', 'no', 'false', 'off', false, 0, null );

    return ! in_array( $value, $false_values );

}

/**
 * Helper function to meet queried page matches the given ones
 *
 * @since 1.0.0
 *
 * @param string|array $pages
 *
 * @return bool
 */
function bbforms_is_page( $pages ) {

    if( ! isset( $_GET['page'] ) )
        return false;

    if( is_array( $pages ) && in_array( $_GET['page'], $pages ) )
        return true;

    if( $_GET['page'] === $pages )
        return true;

    return false;

}