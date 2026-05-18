<?php
/**
 * Anonymous Order Status Product Tag
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Triggers\Anonymous_Order_Status_Porduct_Tag
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Anonymous_Order_Status_Porduct_Tag extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommerce';
    public $trigger = 'woocommerce_anonymous_order_status_product_tag';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'anonymous'         => true,
            'label'             => __( 'Guest\'s order with a product of a tag changes its status', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'Guest\'s order with a product of <strong>a tag</strong> changes its <strong>status</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Term title. %2$s: Order status. %3$s: Number of times. */
            'edit_label'        => sprintf( __( 'Guest\'s order with a product of %1$s changes its status to %2$s %3$s time(s)', 'automatorwp-woocommerce' ), '{term}', '{status}', '{times}' ),
            /* translators: %1$s: Term title. %2$s: Order status. */
            'log_label'         => sprintf( __( 'Guest\'s order with a product of %1$s changes its status to %2$s', 'automatorwp-woocommerce' ), '{term}', '{status}' ),
            'action'            => 'woocommerce_order_status_changed',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 4,
            'options'           => array(
                'term' => automatorwp_utilities_term_option( array(
                    'name'              => __( 'Tag:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'any tag', 'automatorwp-woocommerce' ),
                    'taxonomy'          => 'product_tag',
                ) ),
                'status' => array(
                    'from' => 'status',
                    'fields' => array(
                        'status' => array(
                            'name' => __( 'Status:', 'automatorwp-woocommerce' ),
                            'type' => 'select',
                            'options_cb' => 'wc_get_order_statuses',
                            'default' => 'wc-pending'
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
     * @param int $order_id The order ID
     * @param string $status_from
     * @param string $status_to
     * @param WC_Order $order Order object
     */
    public function listener( $order_id, $status_from, $status_to, $order ) {

        // Bail if status hasn't been changed
        if( $status_from === $status_to ) {
            return;
        }

        // Bail if not a valid order
        if( ! $order ) {
            return;
        }

        $user_id = $order->get_user_id();

        // Bail if not is an anonymous purchase
        if( $user_id !== 0 ) {
            return;
        }

        $items = $order->get_items();

        // Bail if no items purchased
        if ( ! is_array( $items ) ) {
            return;
        }

        // Loop all items to trigger events on each one purchased
        foreach ( $items as $item ) {

            $product_id     = $item->get_product_id();
            $quantity       = $item->get_quantity();

            // Skip items not assigned to a product
            if( $product_id === 0 ) {
                continue;
            }

            $terms_ids = automatorwp_get_term_ids( $product_id, 'product_tag' );

            // Skip items that aren't assigned to any tag
            if( empty( $terms_ids ) ) {
                continue;
            }

            // Trigger events same times as item quantity
            for ( $i = 0; $i < $quantity; $i++ ) {

                // Trigger the product purchase
                automatorwp_trigger_event( array(
                    'trigger'       => $this->trigger,
                    'post_id'       => $product_id,
                    'order_id'      => $order_id,
                    'status'        => $status_to,
                    'terms_ids'     => $terms_ids,
                ) );

            } // End for of quantities

        } // End foreach of items

    }

    /**
     * Anonymous deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if anonymous deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                         True if anonymous deserves trigger, false otherwise
     */
    public function anonymous_deserves_trigger( $deserves_trigger, $trigger, $event, $trigger_options, $automation ) {

        // Don't deserve if status and post ID are not received
        if( ! isset( $event['status'] ) && ! isset( $event['terms_ids'] ) ) {
            return false;
        }

        // Don't deserve if term doesn't match with the trigger option
        if( ! automatorwp_terms_matches( $event['terms_ids'], $trigger_options['term'] ) ) {
            return false;
        }

        // Don't deserve if status doesn't matches with the trigger option
        if( ! automatorwp_woocommerce_order_status_matches( $event['status'], $trigger_options['status'] ) ) {
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
        add_filter( 'automatorwp_anonymous_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['order_id'] = ( isset( $event['order_id'] ) ? $event['order_id'] : 0 );
        $log_meta['terms_ids'] = ( isset( $event['terms_ids'] ) ? $event['terms_ids'] : array() );

        return $log_meta;

    }

}

new AutomatorWP_WooCommerce_Anonymous_Order_Status_Porduct_Tag();