<?php
/**
 * Purchase Subscription Variation
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Triggers\Purchase_Subscription_Variation
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Purchase_Subscription_Variation extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommerce';
    public $trigger = 'woocommerce_purchase_subscription_variation';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User purchases a subscription variation of a product', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User purchases a <strong>subscription variation</strong> of a product', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Product. %2$s: Variation. %3$s: Number of times. */
            'edit_label'        => sprintf( __( 'User purchases %1$s with %2$s selected %3$s time(s)', 'automatorwp-woocommerce' ), '{product}', '{variation}', '{times}' ),
            /* translators: %1$s: Product. %2$s: Variation.*/
            'log_label'         => sprintf( __( 'User purchases %1$s with %2$s selected', 'automatorwp-woocommerce' ), '{product}', '{variation}' ),
            'action'            => 'woocommerce_subscription_payment_complete',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'product' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'product',
                    'name'              => __( 'Subscription:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'any variable subscription', 'automatorwp-woocommerce' ),
                    'action_cb'         => 'automatorwp_woocommerce_get_variable_subscriptions',
                    'options_cb'        => 'automatorwp_options_cb_posts',
                    'default'           => 'any',
                ) ),
                'variation' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'variation',
                    'name'              => __( 'Variation:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'any subscription variation', 'automatorwp-woocommerce' ),
                    'action_cb'         => 'automatorwp_woocommerce_get_product_variations',
                    'options_cb'        => 'automatorwp_woocommerce_options_cb_variations',
                    'default'           => 'any',
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_post_tags( __( 'Product', 'automatorwp-woocommerce' ) ),
                automatorwp_woocommerce_order_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param WC_Subscription $subscription
     */
    public function listener( $subscription ) {

        $items = $subscription->get_items();

        // Bail if no items purchased
        if ( ! is_array( $items ) ) {
            return;
        }

        $order_total = $subscription->get_total();
        $user_id = $subscription->get_user_id();

        // Loop all items to trigger events on each one purchased
        foreach ( $items as $item ) {

            $product_id     = $item->get_product_id();
            $quantity       = $item->get_quantity();
            $variation_id   = $item->get_variation_id();

            // Skip items not assigned to a product
            if( $product_id === 0 ) {
                continue;
            }

            // Skip items that are not variations
            if( $variation_id === 0 ) {
                continue;
            }

            // Trigger events same times as item quantity
            for ( $i = 0; $i < $quantity; $i++ ) {

                // Trigger the product subscription purchase
                automatorwp_trigger_event( array(
                    'trigger'           => $this->trigger,
                    'user_id'           => $user_id,
                    'post_id'           => $product_id,
                    'variation_id'      => $variation_id,
                    'order_id'          => $subscription->get_id(),
                    'subscription_id'   => $subscription->get_id(),
                    'order_total'       => $order_total,
                ) );

            } // End for of quantities

        } // End foreach of items

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

        $log_meta['order_id'] = ( isset( $event['order_id'] ) ? $event['order_id'] : 0 );
        $log_meta['subscription_id'] = ( isset( $event['subscription_id'] ) ? $event['subscription_id'] : 0 );
        $log_meta['variation_id'] = ( isset( $event['variation_id'] ) ? $event['variation_id'] : 0 );

        return $log_meta;

    }

}

new AutomatorWP_WooCommerce_Purchase_Subscription_Variation();