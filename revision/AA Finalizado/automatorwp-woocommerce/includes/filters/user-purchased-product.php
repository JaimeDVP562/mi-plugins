<?php
/**
 * User Purchased Product
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Filters\User_Purchased_Product
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_User_Purchased_Product_Filter extends AutomatorWP_Integration_Filter {

    public $integration = 'woocommerce';
    public $filter = 'woocommerce_user_purchased_product';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_filter( $this->filter, array(
            'integration'       => $this->integration,
            'label'             => __( 'User has purchased', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User has <strong>purchased</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Product. */
            'edit_label'        => sprintf( __( '%1$s', 'automatorwp-woocommerce' ), '{post}' ),
            /* translators: %1$s: Product. */
            'log_label'         => sprintf( __( '%1$s', 'automatorwp-woocommerce' ), '{post}' ),
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Product:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'any product', 'automatorwp-woocommerce' ),
                    'post_type' => 'product',
                    'default' => 'any'
                ) ),
            ),
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_filter    True if user deserves filter, false otherwise
     * @param stdClass  $filter             The filter object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $filter_options     The filter's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */
    public function user_deserves_filter( $deserves_filter, $filter, $user_id, $event, $filter_options, $automation ) {

        // Shorthand
        $product_id = $filter_options['post'];

        // Bail if wrong configured
        if( empty( $product_id ) ) {
            $this->result = __( 'Filter not passed. Product option has not been configured.', 'automatorwp-woocommerce' );
            return false;
        }

        $user = get_user_by( 'id', $user_id );
        $customer_email = $user->user_email;

        if ( $product_id !== 'any' ){
            $purchased = wc_customer_bought_product( $customer_email, $user_id, $product_id );
        }
        else{
            $args = array(
                'customer_id' => $user_id,
                'status' => array( 'wc-completed' ),
                'limit' => -1, // to retrieve _all_ orders by this user
            );
            $orders = wc_get_orders($args);
            
            if ( empty( $orders ) ){
                $purchased = false;
            }
            else {
                $purchased = true;
            }
           
        }
        

        // Don't deserve if user does not have purchased the product
        if( ! $purchased ) {
            $this->result = __( 'Filter not passed. User has not purchased the product.', 'automatorwp-woocommerce' );

            return false;
        }

        $this->result =  __( 'Filter passed. User has purchased the product.', 'automatorwp-woocommerce' );

        return $deserves_filter;

    }

}

new AutomatorWP_WooCommerce_User_Purchased_Product_Filter();