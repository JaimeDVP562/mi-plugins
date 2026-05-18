<?php
/**
 * Add Product Variation To Cart
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Triggers\Add_Product_Variation_To_Cart
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Add_Product_Variation_To_Cart extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommerce';
    public $trigger = 'woocommerce_add_product_variation_to_cart';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User adds a product variation to cart', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User adds a product variation <strong>to cart</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Product. %2$s: Variation. %3$s: Number of times. */
            'edit_label'        => sprintf( __( 'User adds %1$s with %2$s selected to the %3$s time(s)', 'automatorwp-woocommerce' ), '{product}', '{variation}', '{times}' ),
            /* translators: %1$s: Product. %2$s: Variation. */
            'log_label'         => sprintf( __( 'User adds %1$s with %2$s selected to the', 'automatorwp-woocommerce' ), '{product}', '{variation}' ),
            'action'            => 'woocommerce_add_to_cart',
            'function'          => array( $this, 'listener' ),
            'priority'          => 999,
            'accepted_args'     => 6,
            'options'           => array(
                'product' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'product',
                    'name'              => __( 'Product:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'any variable product', 'automatorwp-woocommerce' ),
                    'action_cb'         => 'automatorwp_woocommerce_get_variable_products',
                    'options_cb'        => 'automatorwp_options_cb_posts',
                    'default'           => 'any',
                ) ),
                'variation' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'variation',
                    'name'              => __( 'Variation:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'any variation', 'automatorwp-woocommerce' ),
                    'action_cb'         => 'automatorwp_woocommerce_get_product_variations',
                    'options_cb'        => 'automatorwp_woocommerce_options_cb_variations',
                    'default'           => 'any',
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
     * @param int   $cart_item_key contains the id of the cart item.
     * @param int   $product_id contains the id of the product to add to the cart.
     * @param int   $quantity contains the quantity of the item to add.
     * @param int   $variation_id ID of the variation being added to the cart.
     * @param array $variation attribute values.
     * @param array $cart_item_data extra cart item data we want to pass into the item.
     */
    public function listener( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

        $user_id = get_current_user_id();

        // Bail if user is not logged in
        if( $user_id === 0 ) {
            return;
        }

        // Bail if item is not assigned to a product
        if( $product_id === 0 ) {
            return;
        }

        // Bail if item is not assigned to a variation
        if( $variation_id === 0 ) {
            return;
        }

        // Trigger the product purchase
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'post_id'       => $product_id,
            'variation_id'  => $variation_id,
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
        if( ! isset( $event['post_id'] ) && ! isset( $event['variation_id'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( ! automatorwp_posts_matches( $event['post_id'], $trigger_options['product'] ) ) {
            return false;
        }

        // Don't deserve if variation doesn't match with the trigger option
        if( $trigger_options['variation'] !== 'any' && absint( $trigger_options['variation'] ) !== absint( $event['variation_id'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['variation_id'] = ( isset( $event['variation_id'] ) ? $event['variation_id'] : 0 );

        return $log_meta;

    }

}

new AutomatorWP_WooCommerce_Add_Product_Variation_To_Cart();