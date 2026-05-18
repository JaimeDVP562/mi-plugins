<?php
/**
 * Retry Subscription Payment
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Retry_Subscription_Payment
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Retry_Subscription_Payment extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_retry_subscription_payment';

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
            'label'             => __( 'Retry user\'s subscription payment', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'Retry user\'s subscription <strong>payment</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Product. */
            'edit_label'        => sprintf( __( 'Retry user\'s subscription for %1$s', 'automatorwp-woocommerce' ), '{order}' ),
            /* translators: %1$s: Product. */
            'log_label'         => sprintf( __( 'Retry user\'s subscription for %1$s', 'automatorwp-woocommerce' ), '{order}' ),
            'options'           => array(
                'order' => array(
                    'from' => 'order',
                    'default' => __( 'order ID', 'automatorwp-woocommerce' ),
                    'fields' => array(
                        'order' => array(
                            'name' => __( 'Order ID:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'The order ID to retry payment. Leave blank to get the last failed payment order.', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                    )
                ),
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

        if( ! class_exists( 'WCS_Retry_Manager' ) ) {
            return;
        }

        // Shorthand
        $order_id = $action_options['order'];

        $order = wc_get_order( $order_id );

        if( ! $order ) {
            return;
        }

        // Get the last retry for this order
        $last_retry = WCS_Retry_Manager::store()->get_last_retry_for_order( wcs_get_objects_property( $order, 'id' ) );

        // If last retry is not pending, force the pending status to force the payment retry
        if( $last_retry !== null && $last_retry->get_status() !== 'pending' ) {
            $last_retry->update_status( 'pending' );
        }

        WCS_Retry_Manager::maybe_retry_payment( $order );

    }

}

new AutomatorWP_WooCommerce_Retry_Subscription_Payment();