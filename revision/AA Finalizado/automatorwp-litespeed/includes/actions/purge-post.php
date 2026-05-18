<?php

/**
 * Action: Purge LiteSpeed cache for a specific post
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_Litespeed_Purge_Post extends AutomatorWP_Integration_Action {

    public $integration = 'litespeed';
    public $action      = 'litespeed_purge_post';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Purge LiteSpeed cache for a specific post', 'automatorwp-litespeed' ),
            'select_option' => __( 'Purge LiteSpeed cache for <strong>a specific post</strong>', 'automatorwp-litespeed' ),
            /* translators: %1$s: Post title. */
            'edit_label'    => sprintf( __( 'Purge LiteSpeed cache for %1$s', 'automatorwp-litespeed' ), '{post}' ),
            'log_label'     => sprintf( __( 'Purge LiteSpeed cache for %1$s', 'automatorwp-litespeed' ), '{post}' ),
            'options'       => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name'        => __( 'Post:', 'automatorwp-litespeed' ),
                    'placeholder' => __( 'Select a post', 'automatorwp-litespeed' ),
                    'option_none' => false,
                ) ),
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

        // Get the post ID from the action options
        $post_id = absint( $action_options['post'] );

        // Bail if no post provided
        if ( $post_id === 0 ) {
            return;
        }

        // Purge cache for the specific post
        do_action( 'litespeed_purge_post', $post_id );

    }

}

new AutomatorWP_Litespeed_Purge_Post();