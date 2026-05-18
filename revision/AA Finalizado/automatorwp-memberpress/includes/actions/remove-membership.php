<?php
/**
 * Remove Membership
 *
 * @package     AutomatorWP\Integrations\MemberPress\Actions\Remove_Membership
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MemberPress_Remove_Membership extends AutomatorWP_Integration_Action {

    public $integration = 'memberpress';
    public $action = 'memberpress_remove_membership';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Remove membership to user', 'automatorwp-memberpress' ),
            'select_option'     => __( 'Remove <strong>membership</strong> to user', 'automatorwp-memberpress' ),
            /* translators: %1$s: Membership. */
            'edit_label'        => sprintf( __( 'Remove %1$s to %2$s', 'automatorwp-memberpress' ), '{post}', '{user}' ),
            /* translators: %1$s: Membership. */
            'log_label'         => sprintf( __( 'Remove %1$s to %2$s', 'automatorwp-memberpress' ), '{post}', '{user}' ),
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Membership:', 'automatorwp-memberpress' ),
                    'option_none_label' => __( 'all memberships', 'automatorwp-memberpress' ),
                    'option_custom'         => true,
                    'option_custom_desc'    => __( 'Membership ID', 'automatorwp-memberpress' ),
                    'post_type' => 'memberpressproduct'
                ) ),
                'user' => array(
                    'from' => 'user',
                    'default' => __( 'user', 'automatorwp-memberpress' ),
                    'fields' => array(
                        'user' => array(
                            'name' => __( 'User ID:', 'automatorwp-memberpress' ),
                            'desc' => __( 'User ID that will get removed to the membership. Leave blank to remove the membership to the user that completes the automation.', 'automatorwp-memberpress' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                    )
                ),
            ),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Shorthand
        $post_id = $action_options['post'];
        $user_id_to_apply = absint( $action_options['user'] );

        if( $user_id_to_apply === 0 ) {
            $user_id_to_apply = $user_id;
        }

        $user = get_user_by( 'id', $user_id_to_apply );

        // Get all user memberships
        $memberships = MeprSubscription::account_subscr_table(
            'created_at', 'DESC',
            '', '', 'any', '', false,
            array(
                'member' => $user->user_login,
                'statuses' => array(
                    MeprSubscription::$active_str,
                    MeprSubscription::$suspended_str,
                    MeprSubscription::$cancelled_str
                )
            ),
            MeprHooks::apply_filters( 'mepr_user_subscriptions_query_cols', array( 'id','product_id','created_at' ) )
        );

        if( $memberships['count'] > 0 ){

            foreach( $memberships['results'] as $membership ) {

                // Only remove if post ID is any or if product ID matches with post ID
                if( $post_id === 'any' || absint( $membership->product_id ) === absint( $post_id ) ) {

                    // Check if membership is subscription or a transaction
                    if( $membership->sub_type == 'subscription' ) {
                        $object = new MeprSubscription( $membership->id );
                    } else if( $membership->sub_type == 'transaction' ) {
                        $object = new MeprTransaction( $membership->id );
                    }

                    // Remove the membership
                    $object->destroy();

                    // Update user information
                    $member = $object->user();
                    $member->update_member_data();

                }

            }

        }

    }
}

new AutomatorWP_MemberPress_Remove_Membership();