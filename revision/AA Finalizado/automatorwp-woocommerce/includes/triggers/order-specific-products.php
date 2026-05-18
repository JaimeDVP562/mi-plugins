<?php
/**
 * Order Specific Products
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Triggers\Order_Specific_Products
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Order_Specific_Products extends AutomatorWP_Integration_Trigger {

    public $integration = 'woocommerce';
    public $trigger = 'woocommerce_order_specific_products';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User completes an order with specific products', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'User completes an order with specific <strong>products</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User completes an order with specific %1$s %2$s time(s)', 'automatorwp-woocommerce' ), '{products}', '{times}' ),
            'log_label'         => __( 'User completes an order with specific products', 'automatorwp-woocommerce' ),
            'action'            => 'woocommerce_order_status_completed',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'products' => array(
                    'default' => __( 'products', 'automatorwp' ),
                    'fields' => array(
                        'products' => array(
                            'name' => __( 'Products:', 'automatorwp' ),
                            'desc' => __( 'Set products to check in the order.', 'automatorwp' ),
                            'type' => 'group',
                            'classes' => 'automatorwp-fields-table automatorwp-fields-table-col-1',
                            'options'     => array(
                                'add_button'        => __( 'Add condition', 'automatorwp' ),
                                'remove_button'     => '<span class="dashicons dashicons-no-alt"></span>',
                            ),
                            'fields' => array(
                               'post' => automatorwp_utilities_post_field( array(
                                    'name' => __( 'Product:', 'automatorwp-woocommerce' ),
                                    'option_none_label' => __( 'any product', 'automatorwp-woocommerce' ),
                                    'post_type' => 'product'
                                ) ),
                                
                            ),
                        ),
                        
                    )
                ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
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
     */
    public function listener( $order_id ) {

        $order = wc_get_order( $order_id );
        $product_ids = array();

        // Bail if not a valid order
        if( ! $order ) {
            return;
        }

        // Bail if order is not marked as completed
        if ( $order->get_status() !== 'completed' ) {
            return;
        }

        $items = $order->get_items();

        // Bail if no items purchased
        if ( ! is_array( $items ) ) {
            return;
        }

        $order_total = $order->get_total();
        $user_id = $order->get_user_id();

        // Loop to get ID products in order
        foreach ( $items as $item ) {

            $product_id = $item->get_product_id();

            // Skip items not assigned to a product
            if( $product_id === 0 ) {
                continue;
            }

            $product_ids[]  = $product_id;

        }

        // Trigger the product purchase
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'product_ids'   => $product_ids,
            'order_id'      => $order_id,
            'order_total'   => $order_total,
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
        if( ! isset( $event['product_ids'] ) ) {
            return false;
        }

        $products = $trigger_options['products'];
       
        $products_required = array();
        
        foreach ( $products as $product => $id ){
            $products_required[] = $id['post'];
        }

        $products_order = $event['product_ids'];
        
        // Diff with missing products
        $missing_products = array_diff($products_order, $products_required);

        // Diff with extra products
        $extra_products = array_diff( $products_required, $products_order );

        if ( !empty ( $missing_products ) || !empty ( $extra_products )  ) {
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

        return $log_meta;

    }

}

new AutomatorWP_WooCommerce_Order_Specific_Products();