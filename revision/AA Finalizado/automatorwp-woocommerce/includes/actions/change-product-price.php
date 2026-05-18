<?php
/**
 * Change Product Price
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Change_Product_Price
 * @author      Sergio Garcia
 * @since       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Change_Product_Price_Action extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_change_product_price';

    /**
     * Action result message
     * @var string $result
     */
    public $result = '';

    /**
     * Register the action
     * * Improved UI/UX: 
     * - Replaced manual Product ID field with a dynamic product selector.
     * - Added a default label for the new price tag.
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Change the price of a specific product', 'automatorwp-woocommerce' ),
            'select_option' => __( 'Change the price of <strong>product</strong> to <strong>new price</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Product. %2$s: New Price. */
            'edit_label'    => sprintf( __( 'Change the price of product %1$s to %2$s', 'automatorwp-woocommerce' ), '{post}', '{new_price}' ),
            /* translators: %1$s: Product. %2$s: New Price. */
            'log_label'     => sprintf( __( 'Change the price of product %1$s to %2$s', 'automatorwp-woocommerce' ), '{post}', '{new_price}' ),
            'options'       => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name'              => __( 'Product:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'Choose a product', 'automatorwp-woocommerce' ),
                    'option_custom'      => true,
                    'option_custom_desc' => __( 'Product ID', 'automatorwp-woocommerce' ),
                    'post_type'         => 'product',
                ) ),
                'new_price' => array(
                    'from'    => 'new_price',
                    'default' => __( 'new price', 'automatorwp-woocommerce' ), 
                    'fields'  => array(
                        'new_price' => array(
                            'name'        => __( 'New Price:', 'automatorwp-woocommerce' ),
                            'type'        => 'text',
                            'required'    => true,
                            'placeholder' => __( 'e.g., 19.99', 'automatorwp-woocommerce' ),
                        ),
                    ),
                ),
            ),
        ) );

    }

    /**
     * Action execution function
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $product_id = $action_options['post'];
        $new_price_val = isset( $action_options['new_price'] ) ? $action_options['new_price'] : '';
        $new_price = floatval( sanitize_text_field( $new_price_val ) );

        if ( $product_id === 'any' || $product_id === 0 || $new_price < 0 ) {
            return;
        }

        $product = wc_get_product( absint( $product_id ) );

        if ( ! $product || $product->get_type() !== 'simple' ) {
            $this->result = sprintf( __( 'Action failed. Product ID %s not found or not simple.', 'automatorwp-woocommerce' ), $product_id );
            return;
        }

        try {
            $product->set_regular_price( $new_price );
            $product->set_price( $new_price );
            $product->save();

            $this->result = sprintf(
                __( 'Successfully changed price of product %1$s to %2$s.', 'automatorwp-woocommerce' ),
                $product->get_name(),
                wc_price( $new_price )
            );

        } catch ( Exception $e ) {
            $this->result = $e->getMessage();
        }
    }
}

new AutomatorWP_WooCommerce_Change_Product_Price_Action();