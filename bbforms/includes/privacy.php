<?php
/**
 * Privacy
 *
 * @package     BBForms\Privacy
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once BBFORMS_DIR . 'includes/privacy/exporters.php';
require_once BBFORMS_DIR . 'includes/privacy/erasers.php';

/**
 * Privacy policy suggested content
 *
 * @since 1.0.0
 */
function bbforms_add_privacy_policy_content() {
    // Backward compatibility with older WordPress installs
    if ( ! function_exists( 'wp_add_privacy_policy_content' ) )  {
        return;
    }

    $content = '<h2>' . esc_html__( 'Personal information collection', 'bbforms' ) . '</h2>' .
        '<p>' . esc_html__( 'If you are using BBForms to collect personal information, you should consult a legal professional for your use case.', 'bbforms' ) . '</p>';

    wp_add_privacy_policy_content( __( 'BBForms', 'bbforms' ), wp_kses_post( $content ) );

}
add_action( 'admin_init', 'bbforms_add_privacy_policy_content' );