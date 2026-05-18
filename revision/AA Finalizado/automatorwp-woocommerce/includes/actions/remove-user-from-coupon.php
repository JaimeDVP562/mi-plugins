<?php
/**
 * Remove User From Coupon
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Remove_User_From_Coupon
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Remove_User_From_Coupon extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_remove_user_from_coupon';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Remove user from a coupon', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'Remove user from a <strong>coupon</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Coupon. */
            'edit_label'        => sprintf( __( 'Remove user from %1$s', 'automatorwp-woocommerce' ), '{post}' ),
            /* translators: %1$s: Coupon. */
            'log_label'         => sprintf( __( 'Remove user from %1$s', 'automatorwp-woocommerce' ), '{post}' ),
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name'              => __( 'Coupon:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'Choose a coupon', 'automatorwp-woocommerce' ),
                    'option_custom'         => true,
                    'option_custom_desc'    => __( 'Coupon ID', 'automatorwp-woocommerce' ),
                    'post_type'         => 'shop_coupon',
                ) ),
            ),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Shorthand
        $coupon_id = absint( $action_options['post'] );

        // Bail if not coupon selected
        if( $coupon_id === 0 ) {
            return;
        }

        $user = get_userdata( $user_id );

        // Get the coupon emails
        $customer_email = array_filter( (array) get_post_meta( $coupon_id, 'customer_email', true ) );

        if( in_array( $user->user_email, $customer_email ) ) {

            foreach ( $customer_email as $key => $value ) {
                
                if ( $value === $user->user_email ) {
                    unset( $customer_email[$key] );
                }
                
            }
            update_post_meta( $coupon_id, 'customer_email', $customer_email );
            
        }

    }

}

new AutomatorWP_WooCommerce_Remove_User_From_Coupon();