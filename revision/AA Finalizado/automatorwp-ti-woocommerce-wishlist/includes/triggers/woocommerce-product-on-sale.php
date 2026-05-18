<?php
/**
 * Product On Sale In Wishlist
 *
 * @package     AutomatorWP\Integrations\ti-woocommerce-wishlist\Triggers\Product_On_Sale
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.1.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_PRODUCT_ON_SALE extends AutomatorWP_Integration_Trigger {

    public $integration = 'tiwoocommercewishlist';
    public $trigger = 'woocommerce_wishlist_product_on_sale';

    public function register() {
        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A product in user wishlist goes on sale', 'automatorwp-ti-woocommerce-wishlist' ),
            'select_option' => __( 'A <strong>product in user wishlist</strong> goes on sale', 'automatorwp-ti-woocommerce-wishlist' ),
            'edit_label'    => sprintf( __( 'A product in user wishlist goes on sale %1$s time(s)', 'automatorwp-ti-woocommerce-wishlist' ), '{times}' ),
            'log_label'     => __( 'A product in user wishlist goes on sale', 'automatorwp-ti-woocommerce-wishlist' ),
            'action'        => 'woocommerce_update_product',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_user_wishlist_get_webhook_tags()
            )
        ) );
    }

    public function listener( $product_id ) {

        $product = wc_get_product( $product_id );

        if( !$product ) {
            return;
        }

        // Bail if product is not on sale
        if( !$product->is_on_sale() ) {
            return;
        }

        // Get all users that have this product in their wishlist
        global $wpdb;
        $table = $wpdb->prefix . 'tinvwl_items';

        $users = $wpdb->get_results( $wpdb->prepare(
            "SELECT DISTINCT `author` FROM `{$table}` WHERE `product_id` = %d AND `author` > 0",
            $product_id
        ), ARRAY_A );

        if( empty( $users ) ) {
            return;
        }

        // Fire trigger for each user that has the product in their wishlist
        foreach( $users as $user ) {
            automatorwp_trigger_event( array(
                'trigger'    => $this->trigger,
                'user_id'    => $user['author'],
                'product_id' => $product_id,
            ) );
        }
    }

    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {
        return $deserves_trigger;
    }

    public function hooks() {
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );
        parent::hooks();
    }

    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }
        $log_meta['product_id'] = ( isset( $event['product_id'] ) ? $event['product_id'] : '' );
        return $log_meta;
    }
}

new AUTOMATORWP_TI_WOOCOMMERCE_WISHLIST_PRODUCT_ON_SALE();
