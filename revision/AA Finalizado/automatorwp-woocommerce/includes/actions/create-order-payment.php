<?php
/**
 * Create Order with Payment Gateway
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Create_Order_Payment
 * @author      Sergio Garcia
 * @since       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Create_Order_Payment_Action extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_create_order_payment';

    /**
     * Action result message
     * @var string $result
     */
    public $result = '';

    /**
     * Register the action
     * * Improved UI/UX: 
     * - Replaced manual Product ID field with a dynamic post selector.
     * - Replaced manual Payment ID text field with an automated gateway dropdown menu.
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create an order with a specific product and payment method', 'automatorwp-woocommerce' ),
            'select_option' => __( 'Create an order with <strong>product</strong> and payment', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Product. %2$s: Payment Method. */
            'edit_label'    => sprintf( __( 'Create an order with product %1$s and payment %2$s', 'automatorwp-woocommerce' ), '{post}', '{payment_method}' ),
            /* translators: %1$s: Product. %2$s: Payment Method. */
            'log_label'     => sprintf( __( 'Create an order with product %1$s and payment %2$s', 'automatorwp-woocommerce' ), '{post}', '{payment_method}' ),
            'options'       => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name'              => __( 'Product:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'all products', 'automatorwp-woocommerce' ),
                    'option_custom'      => true,
                    'option_custom_desc' => __( 'Product ID', 'automatorwp-woocommerce' ),
                    'post_type'         => 'product',
                ) ),
                'payment_method' => array(
                    'from'    => 'payment_method',
                    'default' => __( 'all methods', 'automatorwp-woocommerce' ), 
                    'fields'  => array(
                        'payment_method' => array(
                            'name'    => __( 'Payment Method:', 'automatorwp-woocommerce' ),
                            'type'    => 'select',
                            'options' => $this->get_payment_gateways(), 
                            'default' => 'any', 
                        ),
                    ),
                ),
                'user' => array(
                    'from'   => 'user',
                    'fields' => array(
                        'user' => array(
                            'name'    => __( 'User:', 'automatorwp-woocommerce' ),
                            'type'    => 'user',
                            'default' => 'current_user',
                        ),
                    ),
                ),
            ),
        ) );
    }

    /**
     * Retrieve available payment gateways from WooCommerce
     * * @return array List of gateway IDs and titles for the dropdown menu
     */
    public function get_payment_gateways() {
        $options = array(
            'any' => __( 'all methods', 'automatorwp-woocommerce' ) // Added 'any' option for the dropdown
        );
        
        if ( function_exists( 'WC' ) ) {
            $gateways = WC()->payment_gateways->get_available_payment_gateways();
            foreach ( $gateways as $id => $gateway ) {
                $options[$id] = $gateway->get_title();
            }
        }

        return $options;
    }

    /**
     * Action execution logic
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $product_id = $action_options['post'];
        $payment_id = $action_options['payment_method'];

        if ( $product_id === 'any' || $product_id === 0 || $payment_id === 'any' ) {
            return;
        }

        $product = wc_get_product( absint( $product_id ) );
        if ( ! $product ) return;

        try {
            $order = wc_create_order( array( 'customer_id' => $user_id ) );
            if ( is_wp_error( $order ) ) return;

            $order->add_product( $product, 1 );
            $order->set_payment_method( sanitize_text_field( $payment_id ) );
            $order->calculate_totals();
            $order->payment_complete();
            $order->save();

            $this->result = sprintf( __( 'Order %s created via %s', 'automatorwp-woocommerce' ), $order->get_id(), $payment_id );

        } catch ( Exception $e ) {
            $this->result = $e->getMessage();
        }
    }
}

new AutomatorWP_WooCommerce_Create_Order_Payment_Action();