<?php
/**
 * Remove Product From Cart
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Triggers\Remove_Product_From_Cart
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Remove_Product_From_Cart extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommerce';
    public $trigger = 'woocommerce_remove_product_from_cart';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User removes a product from cart', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User removes a product <strong>from cart</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User removes %1$s from cart %2$s time(s)', 'automatorwp-woocommerce' ), '{post}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'User removes %1$s from cart', 'automatorwp-woocommerce' ), '{post}' ),
            'action'            => 'woocommerce_cart_item_removed',
            'function'          => array( $this, 'listener' ),
            'priority'          => 999,
            'accepted_args'     => 2,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Product:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'any product', 'automatorwp-woocommerce' ),
                    'post_type' => 'product'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_post_tags( __( 'Product', 'automatorwp-woocommerce' ) ),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param string $cart_item_key Cart item key to remove from the cart
     * @param WC_Cart $instance
     */
    public function listener( $cart_item_key, $instance ) {

        $user_id = get_current_user_id();

        // Bail if user not assigned
        if( $user_id === 0 ) {
            return;
        }

        // Get the removed product ID
        $product_id = absint( $instance->removed_cart_contents[$cart_item_key]['product_id'] );

        // Bail if item removed is not a product
        if( $product_id === 0 ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'user_id' => $user_id,
            'post_id' => $product_id,
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Don't deserve if post is not received
        if( ! isset( $event['post_id'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( ! automatorwp_posts_matches( $event['post_id'], $trigger_options['post'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

}

new AutomatorWP_WooCommerce_Remove_Product_From_Cart();