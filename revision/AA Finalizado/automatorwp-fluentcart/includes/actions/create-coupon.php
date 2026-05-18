<?php
/**
 * Create Coupon
 *
 * @package     AutomatorWP\Integrations\FluentCart\Actions\Create_Coupon
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentCart_Create_Coupon extends AutomatorWP_Integration_Action {

    public $integration = 'fluentcart';
    public $action      = 'fluentcart_create_coupon';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create a coupon', 'automatorwp-fluentcart' ),
            'select_option' => __( 'Create a <strong>coupon</strong> in FluentCart', 'automatorwp-fluentcart' ),
            /* translators: %1$s: Coupon code. */
            'edit_label'    => sprintf( __( 'Create coupon %1$s in FluentCart', 'automatorwp-fluentcart' ), '{coupon_code}' ),
            /* translators: %1$s: Coupon code. */
            'log_label'     => sprintf( __( 'Create coupon %1$s in FluentCart', 'automatorwp-fluentcart' ), '{coupon_code}' ),
            'options'       => array(
                'coupon_code' => array(
                    'from'    => 'coupon_code',
                    'default' => __( 'coupon code', 'automatorwp-fluentcart' ),
                    'fields'  => array(
                        'coupon_code' => array(
                            'name'    => __( 'Coupon Code:', 'automatorwp-fluentcart' ),
                            'type'    => 'text',
                            'default' => ''
                        ),
                    ),
                ),
                'discount_type' => array(
                    'from'    => 'discount_type',
                    'default' => __( 'discount type', 'automatorwp-fluentcart' ),
                    'fields'  => array(
                        'discount_type' => array(
                            'name'    => __( 'Discount Type:', 'automatorwp-fluentcart' ),
                            'type'    => 'select',
                            'options' => array(
                                'percentage' => __( 'Percentage (%)', 'automatorwp-fluentcart' ),
                                'fixed'      => __( 'Fixed Amount', 'automatorwp-fluentcart' ),
                            ),
                            'default' => 'percentage'
                        ),
                    ),
                ),
                'discount_value' => array(
                    'from'    => 'discount_value',
                    'default' => __( 'discount value', 'automatorwp-fluentcart' ),
                    'fields'  => array(
                        'discount_value' => array(
                            'name'    => __( 'Discount Value:', 'automatorwp-fluentcart' ),
                            'type'    => 'text',
                            'default' => '10'
                        ),
                    ),
                ),
                'expiry_date' => array(
                    'from'    => 'expiry_date',
                    'default' => __( 'expiry date', 'automatorwp-fluentcart' ),
                    'fields'  => array(
                        'expiry_date' => array(
                            'name'    => __( 'Expiry Date (Y-m-d):', 'automatorwp-fluentcart' ),
                            'type'    => 'text',
                            'default' => ''
                        ),
                    ),
                ),
                'usage_limit' => array(
                    'from'    => 'usage_limit',
                    'default' => __( 'usage limit', 'automatorwp-fluentcart' ),
                    'fields'  => array(
                        'usage_limit' => array(
                            'name'    => __( 'Usage Limit (0 = unlimited):', 'automatorwp-fluentcart' ),
                            'type'    => 'text',
                            'default' => '0'
                        ),
                    ),
                ),
            ),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action
     * @param int       $user_id
     * @param array     $action_options
     * @param stdClass  $automation
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $coupon_code    = strtoupper( sanitize_text_field( $action_options['coupon_code'] ) );
        $discount_type  = sanitize_text_field( $action_options['discount_type'] );
        $discount_value = floatval( $action_options['discount_value'] );
        $expiry_date    = sanitize_text_field( $action_options['expiry_date'] );
        $usage_limit    = absint( $action_options['usage_limit'] );

        $this->result = '';

        if( empty( $coupon_code ) ) {
            return;
        }

        if( ! class_exists( '\FluentCart\App\Models\Coupon' ) ) {
            return;
        }

        $existing = \FluentCart\App\Models\Coupon::where( 'code', $coupon_code )->first();

        if( $existing ) {
            $this->result = __( 'Coupon code already exists.', 'automatorwp-fluentcart' );
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

        if( ! empty( $expiry_date ) ) {
            $timestamp = strtotime( $expiry_date );
            if( $timestamp ) {
                $coupon_data['expire_at'] = date( 'Y-m-d 23:59:59', $timestamp );
            }
        }

        $coupon = \FluentCart\App\Models\Coupon::create( $coupon_data );

        if( $coupon ) {
            $this->result = $coupon_code;
        } else {
            $this->result = __( 'The coupon could not be created.', 'automatorwp-fluentcart' );
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta
     * @param stdClass  $action
     * @param int       $user_id
     * @param array     $action_options
     * @param stdClass  $automation
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['coupon_code'] = $this->result;

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields
     * @param stdClass  $log
     * @param stdClass  $object
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['coupon_code'] = array(
            'name' => __( 'Coupon Code:', 'automatorwp-fluentcart' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_FluentCart_Create_Coupon();