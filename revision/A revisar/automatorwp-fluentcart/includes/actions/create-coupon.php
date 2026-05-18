<?php
/**
 * Action: Create a coupon in FluentCart.
 *
 * @package     AutomatorWP\Integrations\FluentCart\Actions
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCart_Action_Create_Coupon extends AutomatorWP_Integration_Action {

    public $integration = 'fluentcart';
    public $action      = 'fluentcart_create_coupon';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create a coupon', 'automatorwp-fluentcart' ),
            'select_option' => __( 'Create a <strong>coupon</strong> in FluentCart', 'automatorwp-fluentcart' ),
            'edit_label'    => __( 'Create coupon {coupon_code} ({discount_type}: {discount_value})', 'automatorwp-fluentcart' ),
            'log_label'     => __( 'Create coupon {coupon_code}', 'automatorwp-fluentcart' ),
            'tags'          => automatorwp_fluentcart_get_create_coupon_response_tags(),
            'options'       => array(
                'coupon_code' => array(
                    'from'   => 'coupon_code',
                    'fields' => array(
                        'coupon_code' => array(
                            'name'        => __( 'Coupon Code', 'automatorwp-fluentcart' ),
                            'type'        => 'text',
                            'placeholder' => __( 'e.g. WELCOME10 or use {user:user_login}', 'automatorwp-fluentcart' ),
                            'required'    => true,
                            'default'     => '',
                        ),
                    ),
                ),
                'discount_type' => array(
                    'from'   => 'discount_type',
                    'fields' => array(
                        'discount_type' => array(
                            'name'    => __( 'Discount Type', 'automatorwp-fluentcart' ),
                            'type'    => 'select',
                            'options' => array(
                                'percentage' => __( 'Percentage (%)', 'automatorwp-fluentcart' ),
                                'fixed'      => __( 'Fixed Amount', 'automatorwp-fluentcart' ),
                            ),
                            'default' => 'percentage',
                        ),
                    ),
                ),
                'discount_value' => array(
                    'from'   => 'discount_value',
                    'fields' => array(
                        'discount_value' => array(
                            'name'        => __( 'Discount Value', 'automatorwp-fluentcart' ),
                            'type'        => 'text',
                            'placeholder' => __( 'e.g. 10', 'automatorwp-fluentcart' ),
                            'required'    => true,
                            'default'     => '10',
                        ),
                    ),
                ),
                'expiry_date' => array(
                    'from'   => 'expiry_date',
                    'fields' => array(
                        'expiry_date' => array(
                            'name'        => __( 'Expiry Date', 'automatorwp-fluentcart' ),
                            'type'        => 'text',
                            'placeholder' => __( 'Y-m-d — e.g. 2025-12-31. Leave blank for no expiry.', 'automatorwp-fluentcart' ),
                            'default'     => '',
                        ),
                    ),
                ),
                'usage_limit' => array(
                    'from'   => 'usage_limit',
                    'fields' => array(
                        'usage_limit' => array(
                            'name'        => __( 'Usage Limit (0 = unlimited)', 'automatorwp-fluentcart' ),
                            'type'        => 'text',
                            'placeholder' => __( 'e.g. 1', 'automatorwp-fluentcart' ),
                            'default'     => '0',
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        if ( ! class_exists( '\FluentCart\App\Models\Coupon' ) ) {
            automatorwp_fluentcart_log_error( 'create_coupon: Coupon model class not found.' );
            return;
        }
        $event          = array();
        $coupon_code    = strtoupper( sanitize_text_field( automatorwp_fluentcart_parse_and_sanitize( $action_options['coupon_code'] ?? '', $action, $user_id, $event ) ) );
        $discount_type  = sanitize_text_field( $action_options['discount_type'] ?? 'percentage' );
        $discount_value = floatval( automatorwp_fluentcart_parse_and_sanitize( $action_options['discount_value'] ?? '10', $action, $user_id, $event ) );
        $expiry_date    = sanitize_text_field( automatorwp_fluentcart_parse_and_sanitize( $action_options['expiry_date'] ?? '', $action, $user_id, $event ) );
        $usage_limit    = absint( automatorwp_fluentcart_parse_and_sanitize( $action_options['usage_limit'] ?? '0', $action, $user_id, $event ) );
        if ( empty( $coupon_code ) ) {
            automatorwp_fluentcart_log_error( 'create_coupon: coupon_code is empty.' );
            return;
        }
        // Check for duplicate coupon code.
        $existing = \FluentCart\App\Models\Coupon::where( 'code', $coupon_code )->first();
        if ( $existing ) {
            automatorwp_fluentcart_log_error( 'create_coupon: coupon code already exists.', array( 'code' => $coupon_code ) );
            return;
        }
        $coupon_data = array(
            'title'          => $coupon_code,
            'code'           => $coupon_code,
            'discount_type'  => $discount_type,
            'discount_value' => $discount_value,
            'status'         => 'active',
            'usage_limit'    => $usage_limit,
        );
        if ( ! empty( $expiry_date ) ) {
            $timestamp = strtotime( $expiry_date );
            if ( $timestamp ) {
                $coupon_data['expire_at'] = date( 'Y-m-d 23:59:59', $timestamp );
            }
        }
        \FluentCart\App\Models\Coupon::create( $coupon_data );
    }
}

new AutomatorWP_FluentCart_Action_Create_Coupon();