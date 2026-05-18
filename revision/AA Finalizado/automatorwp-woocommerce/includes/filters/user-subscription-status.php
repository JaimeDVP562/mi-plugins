<?php
/**
 * User Subscription Status
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Filters\User_Subscription_Status
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_User_Subscription_Status_Filter extends AutomatorWP_Integration_Filter {

    public $integration = 'woocommerce';
    public $filter = 'woocommerce_user_subscription_status';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_filter( $this->filter, array(
            'integration'       => $this->integration,
            'label'             => __( 'User subscription', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User <strong>subscription</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Product. %2$s: Status. */
            'edit_label'        => sprintf( __( 'for %1$s is %2$s', 'automatorwp-woocommerce' ), '{post}', '{status}' ),
            /* translators: %1$s: Product. %2$s: Status. */
            'log_label'         => sprintf( __( 'for %1$s is %2$s', 'automatorwp-woocommerce' ), '{post}', '{status}' ),
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Product:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'any product', 'automatorwp-woocommerce' ),
                    'post_type' => 'product',
                    'default' => 'any'
                ) ),
                'status' => array(
                    'from' => 'status',
                    'fields' => array(
                        'status' => array(
                            'name' => __( 'Status:', 'automatorwp-woocommerce' ),
                            'type' => 'select',
                            'options' => array(       
                                'active'    => __( 'active', 'automatorwp-woocommerce' ),
                                'inactive'    => __( 'inactive', 'automatorwp-woocommerce' ),
                            ),
                            'default' => 'active'
                        ),
                    )
                ),
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
        $status = $filter_options['status'];
        
        // Bail if wrong configured
        if( empty( $product_id ) ) {
            $this->result = __( 'Filter not passed. Product option has not been configured.', 'automatorwp-woocommerce' );
            return false;
        }
        
        if ( $product_id !== 'any'){

            /**
             * User has subscription          
             *
             * @param int|string    $user_id        Customer ID
             * @param int|string    $product_id     Product ID
             * @param string        $status         Subscription status       
             *
             * @return bool
             */
            $status_subscription = ( wcs_user_has_subscription( $user_id, $product_id, 'active' ) ? 'active' : 'inactive');
        
        } else {
            
            if ( $status === 'active' ) {

                $status_subscription = ( wcs_user_has_subscription( $user_id, '', 'active' ) ? 'active' : 'inactive');
                   
            } else{

                $status_subscription = ( wcs_user_has_subscription( $user_id, '', 'cancelled' ) || wcs_user_has_subscription( $user_id, '', 'expired' ) ? 'inactive' : 'active' );
                
            }
                        
        }
        
        // Don't deserve if user does not have purchased the product
        if( $status_subscription !== $status ) {

            $this->result = __( 'Filter not passed. User subscription for a product does not meets the condition.', 'automatorwp-woocommerce' );
            return false;
            
        }

        $this->result =  __( 'Filter passed. User subscription for a product meets the condition.', 'automatorwp-woocommerce' );

        return $deserves_filter;

    }

}

new AutomatorWP_WooCommerce_User_Subscription_Status_Filter();