<?php
/**
 * Open Feature Request
 *
 * @package     AutomatorWP\Integrations\Simple_Feature_Requests\Triggers\Open_Feature_Request
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Simple_Feature_Requests_Open_Feature_Request extends AutomatorWP_Integration_Trigger {

    public $integration = 'simple_feature_requests';
    public $trigger = 'simple_feature_requests_open_feature_request';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User opens a feature request', 'automatorwp' ),
            'select_option'     => __( 'User opens <strong>a feature request</strong>', 'automatorwp' ),
            /* translators: %1$s: Number of times. */
            'edit_label'        => sprintf( __( 'User opens a feature request %1$s time(s)', 'automatorwp' ), '{times}' ),
            'log_label'         => __( 'User opens a feature request', 'automatorwp' ),
            'action'            => 'sfr_post_created',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_post_tags( __( 'Feature request', 'automatorwp' ) ),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int $inserted_id
     */
    public function listener( $inserted_id ) {

        $user_id = get_current_user_id();

        // Bail if user is not logged in
        if( $user_id === 0 ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'   => $this->trigger,
            'user_id'   => $user_id,
            'post_id'   => $inserted_id,
        ) );

    }

}

new AutomatorWP_Simple_Feature_Requests_Open_Feature_Request();