<?php
/**
 * Cancel User Subscription
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Cancel_User_Subscription
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Cancel_User_Subscription extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_cancel_user_subscription';

    /**
     * The action result
     *
     * @since 1.0.0
     *
     * @var string $result
     */
    public $result = '';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Cancel the user\'s subscription to product', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'Cancel the user\'s <strong>subscription</strong> to product', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Product. */
            'edit_label'        => sprintf( __( 'Cancel the user\'s subscription to %1$s', 'automatorwp-woocommerce' ), '{post}' ),
            /* translators: %1$s: Product. */
            'log_label'         => sprintf( __( 'Cancel the user\'s subscription to %1$s', 'automatorwp-woocommerce' ), '{post}' ),
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name'                  => __( 'Product:', 'automatorwp-woocommerce' ),
                    'option_none_label'     => __( 'all products', 'automatorwp-woocommerce' ),
                    'option_custom'         => true,
                    'option_custom_desc'    => __( 'Product ID', 'automatorwp-woocommerce' ),
                    'post_type'             => 'product'
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

        if( ! function_exists( 'wcs_get_users_subscriptions' ) ) {
            return;
        }

        // Shorthand
        $product_id = $action_options['post'];

        $subscriptions = wcs_get_users_subscriptions( $user_id );
        
        foreach( $subscriptions as $subscription ) {

            // Skip not active subscription
            if ( ! $subscription->has_status( array( 'active' ) ) ) {
                continue;
            }

            if( $product_id !== 'any' ) {
                // Cancel product specific subscription

                $order = $subscription->get_last_order( 'all' );
                $found = false;

                foreach ( $order->get_items() as $item_id => $item_data ) {


                    if ( $item_data->get_product()->get_id() === absint( $product_id ) )  {
                        $found = true;
                    }

                }

                // Skip subscription if not is for a specific product
                if( ! $found ) {
                    continue;
                }
            }

            // Skip subscription if can not be cancelled
            if( ! $subscription->can_be_updated_to( 'cancelled' ) ) {
                continue;
            }

            $subscription->update_status( 'cancelled', __( 'Subscription cancelled through AutomatorWP.', 'automatorwp-woocommerce' ) );

        }

    }

}

new AutomatorWP_WooCommerce_Cancel_User_Subscription();