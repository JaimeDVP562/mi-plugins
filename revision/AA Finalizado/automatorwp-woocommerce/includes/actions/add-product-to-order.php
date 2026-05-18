<?php
/**
 * Add Product To Order
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Add_Product_To_Order
 * @author      Sergio Garcia
 * @since       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Add_Product_To_Order_Action extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_add_product_to_order';

    /**
     * Register the action
     * * Improved UI/UX: Replaced manual ID fields with dynamic selectors for products and orders.
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add product to order', 'automatorwp-woocommerce' ),
            'select_option' => __( 'Add <strong>product</strong> to <strong>order</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Product. %2$s: Order. */
            'edit_label'    => sprintf( __( 'Add product %1$s to order %2$s', 'automatorwp-woocommerce' ), '{post}', '{order_id}' ),
            /* translators: %1$s: Product. %2$s: Order. */
            'log_label'     => sprintf( __( 'Add product %1$s to order %2$s', 'automatorwp-woocommerce' ), '{post}', '{order_id}' ),
            'options'       => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name'              => __( 'Product:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'Choose a products', 'automatorwp-woocommerce' ),
                    'option_custom'      => true,
                    'post_type'         => 'product',
                ) ),
                'order_id' => array(
                    'from'    => 'order_id',
                    'default' => __( 'Choose a orders', 'automatorwp-woocommerce' ),
                    'fields'  => array(
                        'order_id' => array(
                            'name'       => __( 'Order:', 'automatorwp-woocommerce' ),
                            'type'       => 'select',
                            'options_cb' => array( $this, 'get_orders_options' ),
                            'default'    => 'any',
                        ),
                    ),
                ),
                'quantity' => array(
                    'from'   => 'quantity',
                    'fields' => array(
                        'quantity' => array(
                            'name'     => __( 'Quantity:', 'automatorwp-woocommerce' ),
                            'type'     => 'number',
                            'default'  => 1,
                            'required' => true,
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
        $options = array( 'any' => __( 'all orders', 'automatorwp-woocommerce' ) );

        if ( ! function_exists( 'wc_get_orders' ) ) return $options;

        $orders = wc_get_orders( array(
            'limit'  => 20,
            'status' => array_keys( wc_get_order_statuses() ),
        ) );

        foreach ( $orders as $order ) {
            $options[$order->get_id()] = sprintf( __( '#%1$s - %2$s', 'automatorwp-woocommerce' ), $order->get_id(), $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
        }

        return $options;
    }

    /**
     * Action execution function
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $product_id = $action_options['post'];
        $order_id   = $action_options['order_id'];
        $quantity   = absint( $action_options['quantity'] );

        if ( $order_id === 'any' || $order_id === 0 ) {
            $order_id = automatorwp_parse_automation_item_option( '{woocommerce_order_id}', array(), $user_id );
        }

        if ( $product_id === 'any' || empty( $order_id ) || $quantity === 0 ) {
            return;
        }

        $data = array(
            'order_id'      => absint( $order_id ),
            'product_id'    => absint( $product_id ),
            'quantity'      => $quantity,
            'user_id'       => $user_id,
            'automation_id' => $automation->id,
            'action_id'     => $action->id,
        );

        $option_name = 'temp_automatorwp_wc_add_product_' . $user_id . '_' . time();
        update_option( $option_name, $data, false );

        add_action( 'wp_loaded', 'automatorwp_woocommerce_add_product_to_order_forced_run', 999 );

        $this->result = __( 'Action enqueued for execution.', 'automatorwp-woocommerce' );
    }
}

new AutomatorWP_WooCommerce_Add_Product_To_Order_Action();