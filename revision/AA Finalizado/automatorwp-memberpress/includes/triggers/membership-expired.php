<?php
/**
 * Membership Expired
 *
 * @package     AutomatorWP\Integrations\MemberPress\Triggers\Membership_Expired
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MemberPress_Membership_Expired extends AutomatorWP_Integration_Trigger {

    public $integration = 'memberpress';
    public $trigger = 'memberpress_membership_expired';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User\'s membership expires', 'automatorwp' ),
            'select_option'     => __( 'User\'s <strong>membership expires</strong>', 'automatorwp' ),

            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User\'s %1$s membership expires %2$s time(s)', 'automatorwp' ), '{post}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'User\'s %1$s membership expires', 'automatorwp' ), '{post}' ),
            'action'            => 'mepr_transaction_expired',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name'              => __( 'Membership:', 'automatorwp' ),
                    'option_none_label' => __( 'any membership', 'automatorwp' ),
                    'post_type'         => 'memberpressproduct'
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
     * @param object $txn transaction object.
     * @param boolean $sub_status subscription status.
     */
    public function listener( $txn, $sub_status ) {

        $transaction = $txn;

        $product_id = intval( $transaction->rec->product_id );
        $user_id    = intval( $transaction->rec->user_id );


        automatorwp_trigger_event( array(
            'trigger'     => $this->trigger,
            'user_id'     => $user_id,
            'post_id'     => $product_id,
            'transaction' => $transaction,
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

        if( ! isset( $event['post_id'] ) ) {
            return false;
        }

        if( ! automatorwp_posts_matches( $event['post_id'], $trigger_options['post'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

}

new AutomatorWP_MemberPress_Membership_Expired();