<?php
/**
 * Suspend Membership
 *
 * @package     AutomatorWP\Integrations\MemberPress\Triggers\Suspend_Membership
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MemberPress_Suspend_Membership extends AutomatorWP_Integration_Trigger {

    public $integration = 'memberpress';
    public $trigger = 'memberpress_suspend_membership';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User suspends a membership', 'automatorwp-memberpress' ),
            'select_option'     => __( 'User suspends <strong>a membership</strong>', 'automatorwp-memberpress' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User suspends %1$s %2$s time(s)', 'automatorwp-memberpress' ), '{post}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'User suspends %1$s', 'automatorwp-memberpress' ), '{post}' ),
            'action'            => 'mepr_subscription_transition_status',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 3,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Membership:', 'automatorwp-memberpress' ),
                    'option_none_label' => __( 'any membership', 'automatorwp-memberpress' ),
                    'post_type' => 'memberpressproduct'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_post_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param string $old_status    Old status object.
     * @param string $new_status    New status.
     * @param object $subscription  Subscription object.
     */
    public function listener( $old_status, $new_status, $subscription ) {

        $old_status = (string) $old_status;
        $new_status = (string) $new_status;

        if( $old_status === $new_status ) {
            return;
        }

        if( $new_status !== 'suspended' ) {
            return;
        }

        $product_id = intval( $subscription->rec->product_id );
        $user_id = intval( $subscription->rec->user_id );

        // Trigger the suspend membership
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'post_id'       => $product_id,
            'subscription'  => $subscription,
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Don't deserve if post is not received
        if( ! isset( $event['post_id'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( ! automatorwp_posts_matches( $event['post_id'], $trigger_options['post'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

}

new AutomatorWP_MemberPress_Suspend_Membership();