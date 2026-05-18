<?php
/**
 * Cancel Membership
 *
 * @package     AutomatorWP\Integrations\MemberPress\Actions\Cancel_Membership
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MemberPress_Cancel_Membership_Action extends AutomatorWP_Integration_Action {

    public $integration = 'memberpress';
    public $action = 'memberpress_cancel_membership';

    /**
     * The action result
     *
     * @since 1.0.0
     *
     * @var string $result
     */
    public $result = '';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Cancel membership to user', 'automatorwp-memberpress' ),
            'select_option'     => __( 'Cancel <strong>membership</strong> to user', 'automatorwp-memberpress' ),
            /* translators: %1$s: Membership. */
            'edit_label'        => sprintf( __( 'Cancel %1$s to %2$s', 'automatorwp-memberpress' ), '{post}', '{user}' ),
            /* translators: %1$s: Membership. */
            'log_label'         => sprintf( __( 'Cancel %1$s to %2$s', 'automatorwp-memberpress' ), '{post}', '{user}' ),
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
                            'desc' => __( 'User ID that will get the membership cancelled. Leave blank to cancel the membership to the user that completes the automation.', 'automatorwp-memberpress' ),
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

        $results = array();

        if( $memberships['count'] > 0 ) {

            foreach( $memberships['results'] as $membership ) {

                // Only cancel if post ID is any or if product ID matches with post ID
                if( $post_id === 'any' || absint( $membership->product_id ) === absint( $post_id ) ) {

                    // Check if membership is a subscription (only subscriptions can be cancelled
                    if( $membership->sub_type == 'subscription' ) {

                        $object = new MeprSubscription( $membership->id );

                        $payment_method = $membership->payment_method();

                        // Check if subscription can be cancelled
                        if( $payment_method->can('cancel-subscriptions') ) {
                            try {
                                // Cancel the subscription
                                $payment_method->process_cancel_subscription( $object->id );

                                $results[] = sprintf( __( 'Subscription #%d: cancelled successfully.', 'automatorwp-memberpress'), $object->id );
                            } catch( Exception $e ) {
                                $results[] = sprintf( __( 'Subscription #%d: %s.', 'automatorwp-memberpress'), $object->id, $e->getMessage() );
                            }
                        }
                    }

                }

            }

        }

        // Update the action result
        $this->result = implode( "\n", $results );

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        // Store result
        $log_meta['result'] = $this->result;

        return $log_meta;
    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        // Bail if log is not assigned to an action
        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-memberpress' ),
            'type' => 'text',
        );

        return $log_fields;
    }
}

new AutomatorWP_MemberPress_Cancel_Membership_Action();