<?php
/**
 * Get Order Details
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Get_Order_Details
 * @author      Sergio Garcia
 * @since       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Get_Order_Details_Action extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_get_order_details';

    /**
     * Action result message
     * @var string $result
     */
    public $result = '';

    /**
     * Register the action
     * * Improved UI/UX: Added an options_cb to load orders safely and avoid fatal errors.
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Get details from order', 'automatorwp-woocommerce' ),
            'select_option' => __( 'Get details from <strong>order</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Order. */
            'edit_label'    => sprintf( __( 'Get details from order %1$s', 'automatorwp-woocommerce' ), '{post}' ),
            /* translators: %1$s: Order. */
            'log_label'     => sprintf( __( 'Get details from order %1$s', 'automatorwp-woocommerce' ), '{post}' ),
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
     * Safe callback to fetch WooCommerce orders for the dropdown
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
     * Action listener execution
     */
    public function listener( $trigger_args, $trigger_options, $user_id, $action, $automation ) {

        $order_id = $trigger_options['post'];

        if ( $order_id === 'any' || $order_id === 0 ) {
            $order_id = automatorwp_parse_automation_item_option( '{woocommerce_order_id}', $trigger_args, $user_id );
        }

        $order = wc_get_order( absint( $order_id ) );

        if ( ! $order ) {
            $this->result = sprintf( __( 'Action failed. Order ID %s not found.', 'automatorwp-woocommerce' ), $order_id );
            return;
        }

        automatorwp_store_log_meta( $action, array( 'order_id' => $order_id ) );

        $this->result = sprintf( __( 'Details successfully retrieved for Order ID %s.', 'automatorwp-woocommerce' ), $order_id );
    }

}

new AutomatorWP_WooCommerce_Get_Order_Details_Action();