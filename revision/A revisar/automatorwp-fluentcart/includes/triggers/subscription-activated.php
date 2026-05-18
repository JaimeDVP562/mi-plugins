<?php
/**
 * Trigger: User activates a FluentCart subscription.
 *
 * Hook (confirmed from SubscriptionService.php line 323):
 *   do_action( 'fluent_cart/payments/subscription_active', $data )
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
 * Class AutomatorWP_FluentCart_Trigger_Subscription_Activated
 *
 * @since 1.0.0
 */
class AutomatorWP_FluentCart_Trigger_Subscription_Activated extends AutomatorWP_Integration_Trigger {

    /**
     * @since 1.0.0
     * @var string
     */
    public $integration = 'fluentcart';

    /**
     * @since 1.0.0
     * @var string
     */
    public $trigger = 'fluentcart_subscription_activated';

    /**
     * Register the trigger definition.
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'User activates a subscription', 'automatorwp-fluentcart' ),
            'select_option' => __( 'User <strong>activates a subscription</strong>', 'automatorwp-fluentcart' ),
            'edit_label'    => __( 'User activates a subscription', 'automatorwp-fluentcart' ),
            'log_label'     => __( 'User activates a subscription', 'automatorwp-fluentcart' ),
            'action'        => 'fluent_cart/payments/subscription_active',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(),
            'tags'          => array(
                'order_id' => array(
                    'label'   => __( 'Order ID', 'automatorwp-fluentcart' ),
                    'type'    => 'integer',
                    'preview' => '456',
                ),
                'subscription_id' => array(
                    'label'   => __( 'Subscription ID', 'automatorwp-fluentcart' ),
                    'type'    => 'integer',
                    'preview' => '7',
                ),
                'subscription_status' => array(
                    'label'   => __( 'Subscription Status', 'automatorwp-fluentcart' ),
                    'type'    => 'text',
                    'preview' => 'active',
                ),
                'customer_email' => array(
                    'label'   => __( 'Customer Email', 'automatorwp-fluentcart' ),
                    'type'    => 'email',
                    'preview' => 'customer@example.com',
                ),
                'customer_name' => array(
                    'label'   => __( 'Customer Name', 'automatorwp-fluentcart' ),
                    'type'    => 'text',
                    'preview' => 'John Doe',
                ),
            ),
        ) );
    }

    /**
     * Listener for fluent_cart/payments/subscription_active.
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
                'subscription_activated: could not resolve WP user.',
                array( 'subscription_id' => $subscription->id ?? null )
            );
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'             => $this->trigger,
            'user_id'             => $user_id,
            'order_id'            => absint( $order->id ?? 0 ),
            'subscription_id'     => absint( $subscription->id ?? 0 ),
            'subscription_status' => sanitize_text_field( $subscription->status ?? '' ),
            'customer_email'      => sanitize_email( $customer->email ?? '' ),
            'customer_name'       => automatorwp_fluentcart_get_customer_name( $customer ),
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

new AutomatorWP_FluentCart_Trigger_Subscription_Activated();