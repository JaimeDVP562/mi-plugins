<?php

/**
 * Action: Purge all LiteSpeed caches
 */

if (! defined('ABSPATH')) {
    exit;
}

class AutomatorWP_Litespeed_Purge_All extends AutomatorWP_Integration_Action {

    public $integration = 'litespeed';
    public $action      = 'litespeed_purge_all';


    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {
    automatorwp_register_action( $this->action, array(
        'integration'   => $this->integration,
        'label'         => __( 'Purge all LiteSpeed caches', 'automatorwp-litespeed' ),
        'select_option' => __( 'Purge <strong>all LiteSpeed caches</strong>', 'automatorwp-litespeed' ),
        'edit_label'    => __( 'Purge all LiteSpeed caches', 'automatorwp-litespeed' ),
        'log_label'     => __( 'Purge all LiteSpeed caches', 'automatorwp-litespeed' ),
        'options'       => array(),
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

        // Purge all caches
        do_action( 'litespeed_purge_all' );

    }

}

new AutomatorWP_Litespeed_Purge_All();