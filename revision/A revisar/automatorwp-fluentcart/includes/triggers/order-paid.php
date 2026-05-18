<?php
/**
 * Trigger: User completes a FluentCart order (paid).
 *
 * Hook (confirmed from FluentCart source — actions.php line 156):
 *   do_action( 'fluent_cart/order_paid_done', $eventData )
 *
 * @package     AutomatorWP\Integrations\FluentCart\Triggers
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCart_Trigger_Order_Paid extends AutomatorWP_Integration_Trigger {

    public $integration = 'fluentcart';
    public $trigger     = 'fluentcart_order_paid';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'User completes a purchase', 'automatorwp-fluentcart' ),
            'select_option' => __( 'User <strong>completes a purchase</strong>', 'automatorwp-fluentcart' ),
            'edit_label'    => __( 'User completes a purchase', 'automatorwp-fluentcart' ),
            'log_label'     => __( 'User completes a purchase', 'automatorwp-fluentcart' ),
            'action'        => 'fluent_cart/order_paid_done',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(),
            'tags'          => array(
                'order_id'       => array( 'label' => __( 'Order ID', 'automatorwp-fluentcart' ),       'type' => 'integer', 'preview' => '123' ),
                'order_total'    => array( 'label' => __( 'Order Total', 'automatorwp-fluentcart' ),    'type' => 'float',   'preview' => '49.99' ),
                'order_status'   => array( 'label' => __( 'Order Status', 'automatorwp-fluentcart' ),   'type' => 'text',    'preview' => 'paid' ),
                'customer_email' => array( 'label' => __( 'Customer Email', 'automatorwp-fluentcart' ), 'type' => 'email',   'preview' => 'customer@example.com' ),
                'customer_name'  => array( 'label' => __( 'Customer Name', 'automatorwp-fluentcart' ),  'type' => 'text',    'preview' => 'John Doe' ),
                'transaction_id' => array( 'label' => __( 'Transaction ID', 'automatorwp-fluentcart' ), 'type' => 'integer', 'preview' => '55' ),
            ),
        ) );
    }

    public function listener( $event_data ) {
        if ( empty( $event_data['order'] ) || empty( $event_data['customer'] ) ) {
            return;
        }
        $order       = $event_data['order'];
        $customer    = $event_data['customer'];
        $transaction = $event_data['transaction'] ?? null;
        // Skip subscription and renewal orders.
        if ( isset( $order->type ) && in_array( $order->type, array( 'subscription', 'renewal' ), true ) ) {
            return;
        }
        $user_id = automatorwp_fluentcart_get_user_id_from_customer( $customer );
        if ( $user_id === 0 ) {
            automatorwp_fluentcart_log_error( 'order_paid: could not resolve WP user.', array( 'order_id' => $order->id ?? null ) );
            return;
        }
        automatorwp_trigger_event( array(
            'trigger'        => $this->trigger,
            'user_id'        => $user_id,
            'order_id'       => absint( $order->id ?? 0 ),
            'order_total'    => floatval( $order->total ?? 0 ),
            'order_status'   => sanitize_text_field( $order->payment_status ?? '' ),
            'customer_email' => sanitize_email( $customer->email ?? '' ),
            'customer_name'  => automatorwp_fluentcart_get_customer_name( $customer ),
            'transaction_id' => absint( $transaction->id ?? 0 ),
        ) );
    }

    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
        return $deserves_trigger;
    }
}

new AutomatorWP_FluentCart_Trigger_Order_Paid();