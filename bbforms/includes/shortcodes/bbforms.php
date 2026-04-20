<?php
/**
 * BBForms Shortcode
 *
 * @package     BBForms\Shortcodes\BBForms
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * [bbforms]
 *
 * @since 1.0.0
 *
 * @param array     $atts       Shortcode's attributes
 * @param string    $content    Shortcode's content
 *
 * @return string
 */
function bbforms_shortcode( $atts = array(), $content = '' ) {

    // Get the received shortcode attributes
    $atts = shortcode_atts( array(
        'id'  => '',
    ), $atts, 'bbforms' );

    $id = absint( $atts['id'] );

    bbforms_enqueue_scripts();

    ob_start();
    bbforms_render_form( $id );
    $output = ob_get_clean();

    /**
     * Filter to override shortcode output
     *
     * @since 1.0.0
     *
     * @param string    $output     Final output
     * @param array     $atts       Shortcode attributes
     * @param string    $content    Shortcode content
     */
    return apply_filters( 'bbforms_shortcode_output', $output, $atts, $content );

}
add_shortcode( 'bbforms', 'bbforms_shortcode' );
add_shortcode( 'bbform', 'bbforms_shortcode' );
