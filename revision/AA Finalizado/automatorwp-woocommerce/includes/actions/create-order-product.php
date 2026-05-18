<?php
/**
 * Create Order
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Create_Order
 * @author      Sergio Garcia
 * @since       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Create_Order_Action extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_create_order';

    /**
     * Action result message
     * @var string $result
     */
    public $result = '';

    /**
     * Register the action
     * * Improved UI/UX: 
     * - Replaced manual Product ID field with a dynamic post selector.
     * - Configured user selector with the correct 'fields' structure to avoid UI warnings.
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create an order with a specific product', 'automatorwp-woocommerce' ),
            'select_option' => __( 'Create an order with <strong>product</strong> for user', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Product. %2$s: User. */
            'edit_label'    => sprintf( __( 'Create an order with product %1$s for user %2$s', 'automatorwp-woocommerce' ), '{post}', '{user}' ),
            /* translators: %1$s: Product. %2$s: User. */
            'log_label'     => sprintf( __( 'Create an order with product %1$s for user %2$s', 'automatorwp-woocommerce' ), '{post}', '{user}' ),
            'options'       => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name'              => __( 'Product:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'all products', 'automatorwp-woocommerce' ),
                    'option_custom'      => true,
                    'post_type'         => 'product',
                ) ),
                'user' => array(
                    'from'    => 'user',
                    'default' => __( 'current user', 'automatorwp-woocommerce' ),
                    'fields'  => array(
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
     * Action execution logic
     * * @param stdClass $action          The action object
     * @param int      $user_id         The user ID
     * @param array    $action_options  The action's stored options
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $product_id = $action_options['post'];

        if ( $product_id === 'any' || $product_id === 0 ) {
            return;
        }

        $product   = wc_get_product( absint( $product_id ) );
        $user_data = get_userdata( $user_id );

        if ( ! $product || ! $user_data ) {
            return;
        }

        try {
            $order = wc_create_order( array( 'customer_id' => $user_id ) );

            if ( is_wp_error( $order ) ) {
                $this->result = $order->get_error_message();
                return;
            }

            $order->add_product( $product, 1 );

            $order->set_address( array(
                'first_name' => $user_data->first_name,
                'last_name'  => $user_data->last_name,
                'email'      => $user_data->user_email,
            ), 'billing' );

            $order->calculate_totals();
            $order->save();

            $this->result = sprintf( __( 'Order %s created for %s', 'automatorwp-woocommerce' ), $order->get_id(), $user_data->user_login );

        } catch ( Exception $e ) {
            $this->result = $e->getMessage();
        }
    }
}

new AutomatorWP_WooCommerce_Create_Order_Action();