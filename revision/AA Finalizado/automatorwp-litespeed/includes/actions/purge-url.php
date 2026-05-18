<?php

/**
 * Action: Purge LiteSpeed cache for a specific URL
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_Litespeed_Purge_Url extends AutomatorWP_Integration_Action {

    public $integration = 'litespeed';
    public $action      = 'litespeed_purge_url';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Purge LiteSpeed cache for a specific URL', 'automatorwp-litespeed' ),
            'select_option' => __( 'Purge LiteSpeed cache for <strong>a specific URL</strong>', 'automatorwp-litespeed' ),
            /* translators: %1$s: URL. */
            'edit_label'    => sprintf( __( 'Purge LiteSpeed cache for %1$s', 'automatorwp-litespeed' ), '{url}' ),
            'log_label'     => sprintf( __( 'Purge LiteSpeed cache for %1$s', 'automatorwp-litespeed' ), '{url}' ),
            'options'       => array(
                'url' => array(
                    'from'    => 'url',
                    'default' => __( 'url', 'automatorwp-litespeed' ),
                    'fields' => array(
                        'url' => array(
                            'name'     => __( 'URL:', 'automatorwp-litespeed' ),
                            'desc'     => __( 'The URL to purge the cache for.', 'automatorwp-litespeed' ),
                            'type'     => 'text',
                            'required' => true,
                        ),
                    ),
                ),
            ),
        ) );
    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass $action          The action object
     * @param int      $user_id         The user ID
     * @param array    $action_options  The action's stored options
     * @param stdClass $automation      The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Bail if LiteSpeed Cache is not active
        if ( ! class_exists( 'LiteSpeed\Purge' ) ) {
            return;
        }

        // Get the URL from the action options
        $url = esc_url_raw( $action_options['url'] );

        // Bail if no URL provided
        if ( empty( $url ) ) {
            return;
        }

        // Purge cache for the specific URL
        do_action( 'litespeed_purge_url', $url );

    }
}

new AutomatorWP_Litespeed_Purge_Url();