<?php
/**
 * Subscription Expired
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Triggers\Subscription_Status
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Subscription_Status extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommerce';
    public $trigger = 'woocommerce_subscription_status';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User subscription of a product changes to a status', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User subscription of a product changes to a <strong>status</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User subscription of %1$s changes to %2$s %3$s time(s)', 'automatorwp-woocommerce' ), '{post}', '{status}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'User subscription of %1$s changes to %2$s', 'automatorwp-woocommerce' ), '{post}', '{status}' ),
            'action'            => 'woocommerce_subscription_status_updated',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Product:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'any product', 'automatorwp-woocommerce' ),
                    'post_type' => 'product'
                ) ),
                'status' => array(
                    'from' => 'status',
                    'fields' => array(
                        'status' => array(
                            'name' => __( 'Status:', 'automatorwp-woocommerce' ),
                            'type' => 'select',
                            'options' => array(
                                'any'               => __( 'any status', 'automatorwp-woocommerce' ),
                                'pending'           => __( 'Pending', 'automatorwp-woocommerce' ),
                                'active'            => __( 'Active', 'automatorwp-woocommerce' ),
                                'on-hold'           => __( 'On hold', 'automatorwp-woocommerce' ),
                                'cancelled'         => __( 'Cancelled', 'automatorwp-woocommerce' ),
                                'switched'          => __( 'Switched', 'automatorwp-woocommerce' ),
                                'expired'           => __( 'Expired', 'automatorwp-woocommerce' ),
                                'pending-cancel'    => __( 'Pending Cancellation', 'automatorwp-woocommerce' ),
                            ),
                            'default' => 'any'
                        ),
                    )
                ),
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

            // Skip items not assigned to a product
            if( $product_id === 0 ) {
                continue;
            }

            // Trigger events same times as item quantity
            for ( $i = 0; $i < $quantity; $i++ ) {

                // Trigger the product subscription expired
                automatorwp_trigger_event( array(
                    'trigger'           => $this->trigger,
                    'user_id'           => $user_id,
                    'post_id'           => $product_id,
                    'order_id'          => $subscription->get_id(),
                    'subscription_id'   => $subscription->get_id(),
                    'order_total'       => $order_total,
                    'status'            => $subscription->get_status(),
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

        // Don't deserve if post and status are not received
        if( ! isset( $event['post_id'] ) && ! isset( $event['status'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( ! automatorwp_posts_matches( $event['post_id'], $trigger_options['post'] ) ) {
            return false;
        }

        // Don't deserve if status doesn't match with the trigger option
        if( $trigger_options['status']  !== 'any' ) {
            if( is_array( $event['status'] ) && ! in_array( $event['status'], $trigger_options['status'] ) ) {
                return false;
            } else if( $event['status'] !== $trigger_options['status'] ) {
                return false;
            }
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

        return $log_meta;

    }

}

new AutomatorWP_WooCommerce_Subscription_Status();