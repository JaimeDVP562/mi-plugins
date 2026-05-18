<?php
/**
 * Action: Purge Cloudflare Cache for a Specific URL
 *
 * @package     AutomatorWP\Cloudflare\Actions
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Cloudflare_Purge_URL extends AutomatorWP_Integration_Action {

    public $integration = 'cloudflare';
    public $action      = 'cloudflare_purge_url';

    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Purge Cloudflare cache for a URL', 'automatorwp-cloudflare' ),
            'select_option' => __( 'Purge Cloudflare cache for a <strong>specific URL</strong>', 'automatorwp-cloudflare' ),
            'edit_label'    => __( 'Purge Cloudflare cache for {url}', 'automatorwp-cloudflare' ),
            'logged_in'     => false,
            'options'       => array(
                'url' => array(
                    'from'   => 'url',
                    'fields' => array(
                        'url' => array(
                            'name'    => __( 'URL:', 'automatorwp-cloudflare' ),
                            'desc'    => __( 'The full URL whose cache should be purged (e.g. https://example.com/my-page/). Supports AutomatorWP tags.', 'automatorwp-cloudflare' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {

        if ( ! automatorwp_cloudflare_has_credentials() ) {
            automatorwp_cloudflare_log_result( array(
                'success' => false,
                'status'  => 0,
                'data'    => null,
                'error'   => __( 'Cloudflare API Token or Zone ID is missing.', 'automatorwp-cloudflare' ),
            ), $action, $user_id, $automation );
            return;
        }

        $raw_url = isset( $action->options->url ) ? $action->options->url : '';
        $url     = isset( $action_options['url'] ) ? $action_options['url'] : '';

        // Anti-Numeric Patch
        if ( is_numeric( $url ) && ! is_numeric( $raw_url ) ) {
            $url = $raw_url;
        }

        $url = sanitize_text_field( $url );

        if ( empty( $url ) || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            automatorwp_cloudflare_log_result( array(
                'success' => false,
                'status'  => 0,
                'data'    => null,
                'error'   => sprintf( __( 'Invalid URL: "%s"', 'automatorwp-cloudflare' ), $url ),
            ), $action, $user_id, $automation );
            return;
        }

        $result = automatorwp_cloudflare_purge_urls( array( $url ) );
        automatorwp_cloudflare_log_result( $result, $action, $user_id, $automation );
    }
}

new AutomatorWP_Cloudflare_Purge_URL();