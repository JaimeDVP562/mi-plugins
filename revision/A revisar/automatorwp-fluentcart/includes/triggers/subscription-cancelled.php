<?php
/**
 * Trigger: User cancels a FluentCart subscription.
 *
 * Hook (confirmed from SubscriptionService.php line 323):
 *   do_action( 'fluent_cart/payments/subscription_cancelled', $data )
 *
 * Payload keys confirmed:
 *   $data['subscription'] -> Subscription model
 *   $data['order']        -> Order model
 *   $data['customer']     -> Customer model
 *   $data['old_status']   -> Previous status string
 *   $data['new_status']   -> New status string
 *
 * @package     AutomatorWP\Integrations\FluentCart\Triggers
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class AutomatorWP_FluentCart_Trigger_Subscription_Cancelled
 *
 * @since 1.0.0
 */
class AutomatorWP_FluentCart_Trigger_Subscription_Cancelled extends AutomatorWP_Integration_Trigger {

    /**
     * @since 1.0.0
     * @var string
     */
    public $integration = 'fluentcart';

    /**
     * @since 1.0.0
     * @var string
     */
    public $trigger = 'fluentcart_subscription_cancelled';

    /**
     * Register the trigger definition.
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'User cancels a subscription', 'automatorwp-fluentcart' ),
            'select_option' => __( 'User <strong>cancels a subscription</strong>', 'automatorwp-fluentcart' ),
            'edit_label'    => __( 'User cancels a subscription', 'automatorwp-fluentcart' ),
            'log_label'     => __( 'User cancels a subscription', 'automatorwp-fluentcart' ),
            'action'        => 'fluent_cart/payments/subscription_cancelled',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(),
            'tags'          => array(
                'subscription_id' => array(
                    'label'   => __( 'Subscription ID', 'automatorwp-fluentcart' ),
                    'type'    => 'integer',
                    'preview' => '8',
                ),
                'order_id' => array(
                    'label'   => __( 'Parent Order ID', 'automatorwp-fluentcart' ),
                    'type'    => 'integer',
                    'preview' => '456',
                ),
                'customer_email' => array(
                    'label'   => __( 'Customer Email', 'automatorwp-fluentcart' ),
                    'type'    => 'email',
                    'preview' => 'customer@example.com',
                ),
            ),
        ) );
    }

    /**
     * Listener for fluent_cart/payments/subscription_cancelled.
     *
     * @since 1.0.0
     *
     * @param array $data  FluentCart event payload.
     */
    public function listener( $data ) {

        if ( empty( $data['subscription'] ) || empty( $data['customer'] ) ) {
            return;
        }

        $subscription = $data['subscription'];
        $customer     = $data['customer'];
        $order        = $data['order'] ?? null;

        $user_id = automatorwp_fluentcart_get_user_id_from_customer( $customer );

        if ( $user_id === 0 ) {
            automatorwp_fluentcart_log_error(
                'subscription_cancelled: could not resolve WP user.',
                array( 'subscription_id' => $subscription->id ?? null )
            );
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'         => $this->trigger,
            'user_id'         => $user_id,
            'subscription_id' => absint( $subscription->id ?? 0 ),
            'order_id'        => absint( $order->id ?? 0 ),
            'customer_email'  => sanitize_email( $customer->email ?? '' ),
        ) );
    }

    /**
     * User deserves trigger check.
     *
     * @since 1.0.0
     *
     * @param bool     $deserves_trigger
     * @param stdClass $trigger
     * @param int      $user_id
     * @param array    $event
     * @param array    $trigger_options
     * @param stdClass $automation
     *
     * @return bool
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
        return $deserves_trigger;
    }
}

new AutomatorWP_FluentCart_Trigger_Subscription_Cancelled();