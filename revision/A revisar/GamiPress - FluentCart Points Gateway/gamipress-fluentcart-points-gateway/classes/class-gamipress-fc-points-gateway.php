<?php
/**
 * GamiPress FluentCart Points Gateway Class
 *
 * Extends FluentCart's AbstractPaymentGateway to register GamiPress points types
 * as payment methods in FluentCart.
 *
 * @package GamiPress\FluentCart\Points_Gateway\Classes
 * @since 1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use FluentCart\App\Modules\PaymentProcessor\Gateways\AbstractPaymentGateway;
use FluentCart\App\Modules\PaymentProcessor\Gateways\PaymentGatewayInterface;

if ( ! class_exists( 'AbstractPaymentGateway' ) && ! class_exists( '\\FluentCart\\App\\Modules\\PaymentProcessor\\Gateways\\AbstractPaymentGateway' ) ) {
    return;
}

class GamiPress_FC_Points_Gateway_Handler extends AbstractPaymentGateway implements PaymentGatewayInterface {

    /**
     * @var string Points type slug
     */
    protected $points_type_slug = '';

    /**
     * @var array Points type data
     */
    protected $points_type_data = array();

    /**
     * @var float Conversion rate
     */
    protected $conversion_rate = 100;

    /**
     * Supported features
     *
     * @var array
     */
    public array $supportedFeatures = array( 'payment', 'refund' );

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @param string $points_type_slug
     * @param array  $points_type_data
     */
    public function __construct( $points_type_slug = '', $points_type_data = array() ) {

        $this->points_type_slug = $points_type_slug;
        $this->points_type_data = $points_type_data;
        $this->conversion_rate  = gamipress_fluentcart_points_gateway_get_conversion_rate( $points_type_slug );

        parent::__construct();
    }

    /**
     * Gateway meta information
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function meta() {

        $plural_name = ! empty( $this->points_type_data['plural_name'] )
            ? $this->points_type_data['plural_name']
            : __( 'Points', 'gamipress-fluentcart-points-gateway' );

        return array(
            'id'       => 'gamipress_' . $this->points_type_slug,
            'title'    => sprintf( __( 'GamiPress: %s', 'gamipress-fluentcart-points-gateway' ), $plural_name ),
            'logo'     => GAMIPRESS_FC_POINTS_GATEWAY_URL . 'assets/images/gamipress-logo.png',
            'supports' => array( 'one_time_payment', 'refunds' ),
        );
    }

    /**
     * Boot the gateway
     *
     * @since 1.0.0
     */
    public function boot() {
        // Nothing to boot - GamiPress points don't need external services
    }

    /**
     * Admin settings fields for this gateway
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function fields() {

        $plural_name = ! empty( $this->points_type_data['plural_name'] )
            ? $this->points_type_data['plural_name']
            : __( 'Points', 'gamipress-fluentcart-points-gateway' );

        $currency_symbol = function_exists( 'fluent_cart_api' )
            ? fluent_cart_api()->getStoreCurrencySign()
            : '$';

        return array(
            'is_active' => array(
                'type'    => 'toggle',
                'label'   => sprintf( __( 'Enable GamiPress: %s Gateway', 'gamipress-fluentcart-points-gateway' ), $plural_name ),
                'default' => false,
            ),
            'payment_mode' => array(
                'type'    => 'select',
                'label'   => __( 'Payment Mode', 'gamipress-fluentcart-points-gateway' ),
                'options' => array(
                    array( 'value' => 'live', 'label' => __( 'Live', 'gamipress-fluentcart-points-gateway' ) ),
                ),
                'default' => 'live',
            ),
            'title' => array(
                'type'    => 'text',
                'label'   => __( 'Title', 'gamipress-fluentcart-points-gateway' ),
                'default' => $plural_name,
                'help'    => __( 'This controls the title which the user sees during checkout.', 'gamipress-fluentcart-points-gateway' ),
            ),
            'description' => array(
                'type'    => 'textarea',
                'label'   => __( 'Description', 'gamipress-fluentcart-points-gateway' ),
                'default' => sprintf( __( 'Pay using your %s.', 'gamipress-fluentcart-points-gateway' ), $plural_name ),
                'help'    => __( 'This controls the description which the user sees during checkout.', 'gamipress-fluentcart-points-gateway' ),
            ),
            'conversion_rate' => array(
                'type'    => 'number',
                'label'   => __( 'Exchange Conversion Rate', 'gamipress-fluentcart-points-gateway' ),
                'default' => '100',
                'help'    => sprintf(
                    __( '%1$s to %2$s conversion rate. This defines how much is 1 %2$s worth in %1$s. For example, 100 means 100 %1$s = 1 %2$s.', 'gamipress-fluentcart-points-gateway' ),
                    $plural_name,
                    $currency_symbol
                ),
            ),
        );
    }

    /**
     * Process payment from FluentCart's PaymentInstance
     *
     * @since 1.0.0
     *
     * @param object $paymentInstance FluentCart PaymentInstance object
     *
     * @return mixed
     */
    public function makePaymentFromPaymentInstance( $paymentInstance ) {

        // Get the order and user
        $order   = $paymentInstance->getOrder();
        $user_id = $order->user_id ?? get_current_user_id();

        // Make sure we are still logged in
        if ( ! $user_id || $user_id === 0 ) {
            return $paymentInstance->setError(
                sprintf(
                    __( 'You must be logged in to pay with %s.', 'gamipress-fluentcart-points-gateway' ),
                    $this->points_type_data['plural_name']
                )
            );
        }

        // Get the order total in the store's currency
        $order_total = $paymentInstance->getPayableAmount();

        // Convert the total amount from cents to currency unit (FluentCart stores amounts in cents)
        $order_total_in_currency = $order_total / 100;

        // Get user's current points and required points
        $user_points     = gamipress_get_user_points( $user_id, $this->points_type_slug );
        $conversion_rate = $this->getConversionRate();
        $required_points = gamipress_fluentcart_points_gateway_convert_to_points( $order_total_in_currency, $this->points_type_slug );

        // Check if required points are close to 0
        if ( $required_points < 1 ) {
            $required_points = 1;
        }

        // Check if user has the required amount of points
        if ( $user_points < $required_points ) {
            return $paymentInstance->setError(
                sprintf(
                    __( 'Insufficient %s. You need %d %s but only have %d.', 'gamipress-fluentcart-points-gateway' ),
                    $this->points_type_data['plural_name'],
                    $required_points,
                    $this->points_type_data['plural_name'],
                    $user_points
                )
            );
        }

        // Deduct points from the customer
        gamipress_deduct_points_to_user( $user_id, $required_points, $this->points_type_slug, array(
            'log_type' => 'points_expend',
            'reason'   => sprintf(
                __( '{user} expended {points} {points_type} to complete order #%s for a new total of {total_points} {points_type}', 'gamipress-fluentcart-points-gateway' ),
                $order->id
            ),
        ) );

        // Insert the user points deduction for buying product
        gamipress_insert_user_earning( $user_id, array(
            'title'       => sprintf( __( '-%s points used on order #%s', 'gamipress-fluentcart-points-gateway' ), $required_points, $order->id ),
            'user_id'     => $user_id,
            'post_id'     => gamipress_get_points_type_id( $this->points_type_slug ),
            'post_type'   => 'points-type',
            'points'      => $required_points,
            'points_type' => $this->points_type_slug,
            'date'        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
        ) );

        // Award points to product vendors (if applicable)
        $this->award_vendor_points( $order, $user_id );

        // Store points deduction info in order meta
        $order->updateMeta( 'gamipress_points_type', $this->points_type_slug );
        $order->updateMeta( 'gamipress_points_deducted', $required_points );
        $order->updateMeta( 'gamipress_conversion_rate', $conversion_rate );

        // Add order note
        $order->addNote(
            sprintf(
                __( 'Payment completed: %d %s deducted from user.', 'gamipress-fluentcart-points-gateway' ),
                $required_points,
                $this->points_type_data['plural_name']
            )
        );

        // Mark as paid - FluentCart handles the order status update
        $paymentInstance->setPaymentChargeId( 'gamipress_' . $this->points_type_slug . '_' . time() );
        $paymentInstance->setPaymentStatus( 'paid' );
        $paymentInstance->setPaymentTotal( $order_total );

        return $paymentInstance->confirmPayment();
    }

    /**
     * Handle IPN (Instant Payment Notification)
     * Not needed for points-based gateway since payments are processed instantly
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handleIPN() {
        // Points gateway doesn't need IPN/webhook handling
        // Payments are processed instantly and locally
    }

    /**
     * Get order info for frontend display
     *
     * @since 1.0.0
     *
     * @param array $data
     *
     * @return array
     */
    public function getOrderInfo( array $data ) {

        $user_id = get_current_user_id();

        if ( ! $user_id ) {
            return array(
                'status'  => 'error',
                'message' => __( 'You must be logged in to pay with points.', 'gamipress-fluentcart-points-gateway' ),
            );
        }

        $user_points = gamipress_get_user_points( $user_id, $this->points_type_slug );

        return array(
            'status'       => 'success',
            'payment_args' => array(
                'points_type'  => $this->points_type_slug,
                'user_points'  => $user_points,
                'plural_name'  => $this->points_type_data['plural_name'],
            ),
            'message'      => sprintf(
                __( 'You have %d %s available.', 'gamipress-fluentcart-points-gateway' ),
                $user_points,
                $this->points_type_data['plural_name']
            ),
        );
    }

    /**
     * Process refund
     *
     * @since 1.0.0
     *
     * @param object $order  FluentCart order object
     * @param float  $amount Refund amount (null for full refund)
     * @param string $reason Refund reason
     *
     * @return bool
     */
    public function processRefund( $order, $amount = null, $reason = '' ) {

        $user_id = $order->user_id ?? 0;

        if ( ! $user_id ) {
            return false;
        }

        // Get the points type used for this order
        $points_type     = $order->getMeta( 'gamipress_points_type' );
        $conversion_rate = $order->getMeta( 'gamipress_conversion_rate' );

        if ( ! $points_type || ! $conversion_rate ) {
            return false;
        }

        // If not is a partial refund, then get the full deducted points
        if ( $amount === null ) {
            $refund_points = $order->getMeta( 'gamipress_points_deducted' );
            $order_note    = __( 'Order refunded: %d %s refunded to user.', 'gamipress-fluentcart-points-gateway' );
        } else {
            // Convert refund amount from cents to currency, then to points
            $amount_in_currency = $amount / 100;
            $refund_points      = ceil( $amount_in_currency * $conversion_rate );
            $order_note         = __( 'Order partially refunded: %d %s refunded to user.', 'gamipress-fluentcart-points-gateway' );
        }

        // Return if amount is 0 or negative
        if ( $refund_points <= 0 ) {
            return false;
        }

        $points_types = gamipress_get_points_types();
        $plural_name  = isset( $points_types[ $points_type ]['plural_name'] )
            ? $points_types[ $points_type ]['plural_name']
            : __( 'Points', 'gamipress-fluentcart-points-gateway' );

        // Refund points to the customer
        gamipress_award_points_to_user( $user_id, $refund_points, $points_type, array(
            'log_type' => 'points_earn',
            'reason'   => sprintf(
                __( '{user} awarded {points} {points_type} for the order #%s refund for a new total of {total_points} {points_type}', 'gamipress-fluentcart-points-gateway' ),
                $order->id
            ),
        ) );

        // Add order note
        $order->addNote(
            sprintf( $order_note, $refund_points, $plural_name )
        );

        return true;
    }

    /**
     * Award points to product vendors
     *
     * @since 1.0.0
     *
     * @param object $order   FluentCart order object
     * @param int    $user_id The buyer's user ID
     */
    private function award_vendor_points( $order, $user_id ) {

        $items = $order->getItems();

        if ( empty( $items ) ) {
            return;
        }

        foreach ( $items as $item ) {

            $product_id = $item->product_id ?? 0;

            if ( ! $product_id ) {
                continue;
            }

            // Try to get the product author (vendor)
            $vendor_id = absint( get_post_field( 'post_author', $product_id ) );

            if ( $vendor_id === 0 ) {
                continue;
            }

            // Get the item total and convert to points
            $item_total  = isset( $item->total ) ? $item->total / 100 : 0; // Convert from cents
            $item_points = ceil( $item_total * $this->conversion_rate );

            $award_points_to_vendor = (bool) ( $vendor_id !== $user_id );

            /**
             * Filter to decide if should award points to vendor
             *
             * @since 1.0.0
             *
             * @param bool $award_points_to_vendor
             * @param int  $vendor_id
             * @param int  $user_id
             * @param int  $order_id
             *
             * @return bool
             */
            $award_points_to_vendor = apply_filters(
                'gamipress_fluentcart_points_gateway_award_points_to_vendor',
                $award_points_to_vendor,
                $vendor_id,
                $user_id,
                $order->id
            );

            if ( $award_points_to_vendor && $item_points > 0 ) {

                $product_name = $item->title ?? __( 'Product', 'gamipress-fluentcart-points-gateway' );

                // Award points to each product vendor
                gamipress_award_points_to_user( $vendor_id, $item_points, $this->points_type_slug, array(
                    'log_type' => 'points_earn',
                    'reason'   => sprintf(
                        __( '{user} earned {points} {points_type} for sell %s through {points_type} for a new total of {total_points} {points_type}', 'gamipress-fluentcart-points-gateway' ),
                        $product_name
                    ),
                ) );

                // Insert the vendor user earning for selling product
                gamipress_insert_user_earning( $vendor_id, array(
                    'title'       => sprintf( __( '+%s points for selling product %s', 'gamipress-fluentcart-points-gateway' ), $item_points, $product_name ),
                    'user_id'     => $vendor_id,
                    'post_id'     => gamipress_get_points_type_id( $this->points_type_slug ),
                    'post_type'   => 'points-type',
                    'points'      => $item_points,
                    'points_type' => $this->points_type_slug,
                    'date'        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
                ) );
            }
        }
    }

    /**
     * Get the current conversion rate from gateway settings
     *
     * @since 1.0.0
     *
     * @return float
     */
    private function getConversionRate() {

        $settings = $this->settings ?? array();
        $rate     = isset( $settings['conversion_rate'] ) ? floatval( $settings['conversion_rate'] ) : 0;

        if ( $rate <= 0 ) {
            $rate = gamipress_fluentcart_points_gateway_get_conversion_rate( $this->points_type_slug );
        }

        return $rate > 0 ? $rate : 100;
    }

    /**
     * Check if the gateway has a specific feature
     *
     * @since 1.0.0
     *
     * @param string $feature
     *
     * @return bool
     */
    public function has( string $feature ) {
        return in_array( $feature, $this->supportedFeatures, true );
    }

    /**
     * Register this gateway with FluentCart
     *
     * @since 1.0.0
     *
     * @param string $points_type_slug
     * @param array  $points_type_data
     */
    public static function register( $points_type_slug, $points_type_data ) {

        $gateway = new self( $points_type_slug, $points_type_data );

        fluent_cart_api()->registerCustomPaymentMethod(
            'gamipress_' . $points_type_slug,
            $gateway
        );
    }
}
