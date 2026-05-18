<?php
/**
 * Trigger: User creates a FluentCart order.
 *
 * Hook:    fluent_cart/order_created
 * Payload: $order (FluentCart Order model object)
 *
 * @package     AutomatorWP\Integrations\FluentCart\Triggers
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCart_Trigger_Order_Created extends AutomatorWP_Integration_Trigger {

    public $integration = 'fluentcart';
    public $trigger     = 'fluentcart_order_created';

    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'User creates an order', 'automatorwp-fluentcart' ),
            'select_option' => __( 'User <strong>creates an order</strong>', 'automatorwp-fluentcart' ),
            'edit_label'    => __( 'User creates an order', 'automatorwp-fluentcart' ),
            'log_label'     => __( 'User creates an order', 'automatorwp-fluentcart' ),
            'action'        => 'fluent_cart/order_created',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(),
            'tags'          => array(
                'order_id' => array(
                    'label'   => __( 'Order ID', 'automatorwp-fluentcart' ),
                    'type'    => 'integer',
                    'preview' => '101',
                ),
                'order_total' => array(
                    'label'   => __( 'Order Total', 'automatorwp-fluentcart' ),
                    'type'    => 'float',
                    'preview' => '59.99',
                ),
                'order_status' => array(
                    'label'   => __( 'Order Status', 'automatorwp-fluentcart' ),
                    'type'    => 'text',
                    'preview' => 'pending',
                ),
                'customer_email' => array(
                    'label'   => __( 'Customer Email', 'automatorwp-fluentcart' ),
                    'type'    => 'email',
                    'preview' => 'customer@example.com',
                ),
                'customer_name' => array(
                    'label'   => __( 'Customer Name', 'automatorwp-fluentcart' ),
                    'type'    => 'text',
                    'preview' => 'Jane Doe',
                ),
            ),
        ) );
    }

    public function listener( $order ) {

        if ( empty( $order ) || ! is_object( $order ) ) {
            return;
        }

        $user_id = automatorwp_fluentcart_get_user_id_from_customer( $order->customer ?? null );

        if ( $user_id === 0 ) {
            automatorwp_fluentcart_log_error(
                'order_created: could not resolve WP user.',
                array( 'order_id' => $order->id ?? null )
            );
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'        => $this->trigger,
            'user_id'        => $user_id,
            'order_id'       => absint( $order->id ?? 0 ),
            'order_total'    => floatval( $order->total ?? 0 ),
            'order_status'   => sanitize_text_field( $order->payment_status ?? '' ),
            'customer_email' => sanitize_email( $order->customer->email ?? '' ),
            'customer_name'  => automatorwp_fluentcart_get_customer_name( $order->customer ?? null ),
        ) );
    }

    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
        return $deserves_trigger;
    }
}

new AutomatorWP_FluentCart_Trigger_Order_Created();