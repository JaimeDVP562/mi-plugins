<?php
/**
 * Retry Subscription Payment
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Retry_Subscription_Payment
 * @author      Sergio Garcia
 * @since       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Retry_Subscription_Payment extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_retry_subscription_payment';

    /**
     * Action result message
     * @var string $result
     */
    public $result = '';

    /**
     * Register the action
     * * Improved UI/UX: Replaced manual Order ID field with a dynamic order selector using a safe callback.
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Retry user\'s subscription payment', 'automatorwp-woocommerce' ),
            'select_option' => __( 'Retry user\'s subscription <strong>payment</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Order. */
            'edit_label'    => sprintf( __( 'Retry user\'s subscription for %1$s', 'automatorwp-woocommerce' ), '{post}' ),
            /* translators: %1$s: Order. */
            'log_label'     => sprintf( __( 'Retry user\'s subscription for %1$s', 'automatorwp-woocommerce' ), '{post}' ),
            'options'       => array(
                'post' => array(
                    'from'    => 'post',
                    'default' => __( 'all orders', 'automatorwp-woocommerce' ),
                    'fields'  => array(
                        'post' => array(
                            'name'       => __( 'Order:', 'automatorwp-woocommerce' ),
                            'type'       => 'select',
                            'options_cb' => array( $this, 'get_orders_options' ),
                            'default'    => 'any',
                        ),
                    ),
                ),
            ),
        ) );

    }

    /**
     * Fetch the most recent orders for the dropdown selector
     * * @return array List of Order IDs and formatted titles
     */
    public function get_orders_options() {
        $options = array(
            'any' => __( 'all orders', 'automatorwp-woocommerce' )
        );

        if ( ! function_exists( 'wc_get_orders' ) ) {
            return $options;
        }

        $orders = wc_get_orders( array(
            'limit'  => 20,
            'status' => array_keys( wc_get_order_statuses() ),
        ) );

        foreach ( $orders as $order ) {
            $order_id = $order->get_id();
            $buyer    = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            $options[$order_id] = sprintf( __( '#%1$s - %2$s', 'automatorwp-woocommerce' ), $order_id, $buyer );
        }

        return $options;
    }

    /**
     * Action execution function
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        if ( ! class_exists( 'WCS_Retry_Manager' ) ) {
            return;
        }

        $order_id = $action_options['post'];

        if ( $order_id === 'any' || $order_id === 0 ) {
            $order_id = automatorwp_parse_automation_item_option( '{woocommerce_order_id}', array(), $user_id );
        }

        $order = wc_get_order( absint( $order_id ) );

        if ( ! $order ) {
            return;
        }

        try {
            $last_retry = WCS_Retry_Manager::store()->get_last_retry_for_order( wcs_get_objects_property( $order, 'id' ) );

            if ( $last_retry !== null && $last_retry->get_status() !== 'pending' ) {
                $last_retry->update_status( 'pending' );
            }

            WCS_Retry_Manager::maybe_retry_payment( $order );
            
            $this->result = sprintf( __( 'Payment retry triggered for Order #%s', 'automatorwp-woocommerce' ), $order_id );

        } catch ( Exception $e ) {
            $this->result = $e->getMessage();
        }

    }

}

new AutomatorWP_WooCommerce_Retry_Subscription_Payment();