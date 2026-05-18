<?php
/**
 * Action: Cancel a FluentCart subscription.
 *
 * Updates subscription status to 'cancelled' via ORM.
 * This fires fluent_cart/payments/subscription_cancelled downstream.
 *
 * @package     AutomatorWP\Integrations\FluentCart\Actions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCart_Action_Cancel_Subscription extends AutomatorWP_Integration_Action {

    public $integration = 'fluentcart';
    public $action      = 'fluentcart_cancel_subscription';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Cancel a subscription', 'automatorwp-fluentcart' ),
            'select_option' => __( '<strong>Cancel</strong> a FluentCart subscription', 'automatorwp-fluentcart' ),
            'edit_label'    => __( 'Cancel subscription {subscription_id}', 'automatorwp-fluentcart' ),
            'log_label'     => __( 'Cancel subscription {subscription_id}', 'automatorwp-fluentcart' ),
            'tags'          => automatorwp_fluentcart_get_cancel_subscription_response_tags(),
            'options'       => array(
                'subscription_id' => array(
                    'from'   => 'subscription_id',
                    'fields' => array(
                        'subscription_id' => array(
                            'name'        => __( 'Subscription ID', 'automatorwp-fluentcart' ),
                            'type'        => 'text',
                            'placeholder' => __( 'Use {subscription_id} tag from trigger', 'automatorwp-fluentcart' ),
                            'required'    => true,
                            'default'     => '',
                        ),
                    ),
                ),
                'cancellation_note' => array(
                    'from'   => 'cancellation_note',
                    'fields' => array(
                        'cancellation_note' => array(
                            'name'        => __( 'Cancellation Note (optional)', 'automatorwp-fluentcart' ),
                            'type'        => 'textarea',
                            'placeholder' => __( 'Reason for cancellation — supports tags', 'automatorwp-fluentcart' ),
                            'default'     => '',
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        if ( ! class_exists( '\FluentCart\App\Models\Subscription' ) ) {
            automatorwp_fluentcart_log_error( 'cancel_subscription: Subscription model class not found.' );
            return;
        }
        // Read subscription_id directly — no tag parsing for static numeric values.
        $subscription_id   = absint( $action_options['subscription_id'] ?? 0 );
        $event             = array();
        $cancellation_note = sanitize_text_field( automatorwp_fluentcart_parse_and_sanitize( $action_options['cancellation_note'] ?? '', $action, $user_id, $event ) );
        if ( $subscription_id === 0 ) {
            automatorwp_fluentcart_log_error( 'cancel_subscription: subscription_id is 0.' );
            return;
        }
        $subscription = \FluentCart\App\Models\Subscription::find( $subscription_id );
        if ( ! $subscription ) {
            automatorwp_fluentcart_log_error( 'cancel_subscription: subscription not found.', array( 'subscription_id' => $subscription_id ) );
            return;
        }
        // Skip already cancelled subscriptions.
        if ( isset( $subscription->status ) && $subscription->status === 'cancelled' ) {
            return;
        }
        // Update status — fires fluent_cart/payments/subscription_cancelled downstream.
        $subscription->update( array( 'status' => 'cancelled' ) );
        if ( ! empty( $cancellation_note ) ) {
            $subscription->updateMeta( 'cancellation_note', $cancellation_note );
        }
        do_action( 'automatorwp_fluentcart_subscription_cancelled', $subscription_id, $user_id, $cancellation_note );
    }
}

new AutomatorWP_FluentCart_Action_Cancel_Subscription();