<?php
/**
 * Create Coupon
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Create_Coupon
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Create_Coupon extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_create_coupon';

    /**
     * The action post id
     *
     * @since 1.0.0
     *
     * @var int $post_id
     */
    public $post_id = 0;

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
            'label'             => __( 'Create a coupon', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'Create a <strong>coupon</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Coupon. */
            'edit_label'        => sprintf( __( 'Create a %1$s', 'automatorwp-woocommerce' ), '{coupon}' ),
            /* translators: %1$s: Coupon. */
            'log_label'         => sprintf( __( 'Create a %1$s', 'automatorwp-woocommerce' ), '{coupon}' ),
            'options'           => array(
                'coupon' => array(
                    'default' => __( 'coupon', 'automatorwp-woocommerce' ),
                    'fields' => array(
                        'coupon_code' => array(
                            'name' => __( 'Code:', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                        ),
                        'coupon_description' => array(
                            'name' => __( 'Description:', 'automatorwp-woocommerce' ),
                            'type' => 'textarea',
                        ),

                        // General

                        'discount_type' => array(
                            'name' => __( 'Discount type:', 'automatorwp-woocommerce' ),
                            'type' => 'select',
                            'options' => array(
                                'percent'       => __( 'Percentage discount', 'automatorwp-woocommerce' ),
                                'fixed_cart'    => __( 'Fixed cart discount', 'automatorwp-woocommerce' ),
                                'fixed_product' => __( 'Fixed product discount', 'automatorwp-woocommerce' ),
                            ),
                        ),
                        'coupon_amount' => array(
                            'name' => __( 'Coupon amount:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'Value of the coupon.', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                            'default' => '1'
                        ),
                        'free_shipping' => array(
                            'name' => __( 'Allow free shipping:', 'automatorwp-woocommerce' ),
                            'desc' => sprintf( __( 'Check this option if the coupon grants free shipping. A <a href="%s" target="_blank">free shipping method</a> must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'automatorwp-woocommerce' ), 'https://docs.woocommerce.com/document/free-shipping/' ),
                            'type' => 'checkbox',
                            'classes' => 'cmb2-switch',
                        ),
                        'expiry_date' => array(
                            'name' => __( 'Coupon expiry date:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'The coupon will expire at 00:00:00 of this date.', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                            'attributes' => array(
                                'placeholder' => 'YYYY-MM-DD',
                            ),
                        ),

                        // Usage restriction

                        'minimum_amount' => array(
                            'name' => __( 'Minimum spend:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'This field allows you to set the minimum spend (subtotal) allowed to use the coupon.', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'No minimum', 'automatorwp-woocommerce' ),
                            ),
                        ),
                        'maximum_amount' => array(
                            'name' => __( 'Maximum spend:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'This field allows you to set the maximum spend (subtotal) allowed when using the coupon.', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'No maximum', 'automatorwp-woocommerce' ),
                            ),
                        ),
                        'individual_use' => array(
                            'name' => __( 'Individual use only:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'Check this option if the coupon cannot be used in conjunction with other coupons.', 'automatorwp-woocommerce' ),
                            'type' => 'checkbox',
                            'classes' => 'cmb2-switch',
                        ),
                        'exclude_sale_items' => array(
                            'name' => __( 'Exclude sale items:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'Check this option if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are items in the cart that are not on sale.', 'automatorwp-woocommerce' ),
                            'type' => 'checkbox',
                            'classes' => 'cmb2-switch',
                        ),
                        'products_ids' => automatorwp_utilities_post_field( array(
                            'name'              => __( 'Products:', 'automatorwp-woocommerce' ),
                            'desc'              => __( 'Products that the coupon will be applied to, or that need to be in the cart in order for the "Fixed cart discount" to be applied.', 'automatorwp-woocommerce' ),
                            'multiple'          => true,
                            'option_none'       => false,
                            'post_type'         => 'product',
                            'placeholder'       => __( 'Search for products...', 'automatorwp-woocommerce' ),
                        ) ),
                        'exclude_product_ids' => automatorwp_utilities_post_field( array(
                            'name'              => __( 'Exclude products:', 'automatorwp-woocommerce' ),
                            'desc'              => __( 'Products that the coupon will not be applied to, or that cannot be in the cart in order for the "Fixed cart discount" to be applied.', 'automatorwp-woocommerce' ),
                            'multiple'          => true,
                            'option_none'       => false,
                            'post_type'         => 'product',
                            'placeholder'       => __( 'Search for products...', 'automatorwp-woocommerce' ),
                        ) ),
                        'product_categories' => automatorwp_utilities_term_field( array(
                            'name'              => __( 'Product categories:', 'automatorwp-woocommerce' ),
                            'desc'              => __( 'Product categories that the coupon will be applied to, or that need to be in the cart in order for the "Fixed cart discount" to be applied.', 'automatorwp-woocommerce' ),
                            'multiple'          => true,
                            'option_none'       => false,
                            'taxonomy'          => 'product_cat',
                            'placeholder'       => __( 'Search for categories...', 'automatorwp-woocommerce' ),
                        ) ),
                        'exclude_product_categories' => automatorwp_utilities_term_field( array(
                            'name'              => __( 'Exclude categories:', 'automatorwp-woocommerce' ),
                            'desc'              => __( 'Product categories that the coupon will not be applied to, or that cannot be in the cart in order for the "Fixed cart discount" to be applied.', 'automatorwp-woocommerce' ),
                            'multiple'          => true,
                            'option_none'       => false,
                            'taxonomy'          => 'product_cat',
                            'placeholder'       => __( 'Search for categories...', 'automatorwp-woocommerce' ),
                        ) ),
                        'customer_email' => array(
                            'name' => __( 'Allowed emails:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'List of allowed billing emails to check against when an order is placed. Separate email addresses with commas. You can also use an asterisk (*) to match parts of an email. For example "*@gmail.com" would match all gmail addresses.', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'No restrictions', 'automatorwp-woocommerce' ),
                            ),
                        ),

                        // Usage limits

                        'usage_limit' => array(
                            'name' => __( 'Usage limit per coupon:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'How many times this coupon can be used before it is void.', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'Unlimited usage', 'automatorwp-woocommerce' ),
                                'type' => 'number',
                                'step' => '1',
                                'min'  => '0',
                            ),
                        ),
                        'limit_usage_to_x_items' => array(
                            'name' => __( 'Limit usage to X items:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'The maximum number of individual items this coupon can apply to when using product discounts. Leave blank to apply to all qualifying items in cart.', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'Apply to all qualifying items in cart', 'automatorwp-woocommerce' ),
                                'type' => 'number',
                                'step' => '1',
                                'min'  => '0',
                            ),
                        ),
                        'usage_limit_per_user' => array(
                            'name' => __( 'Usage limit per user:', 'automatorwp-woocommerce' ),
                            'desc' => __( 'How many times this coupon can be used by an individual user. Uses billing email for guests, and user ID for logged in users.', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'Unlimited usage', 'automatorwp-woocommerce' ),
                                'type' => 'number',
                                'step' => '1',
                                'min'  => '0',
                            ),
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
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $this->post_id = 0;

        // Shorthand
        $coupon_code = $action_options['coupon_code'];
        $coupon_description = $action_options['coupon_description'];

        $coupon = array(
            'post_title'   => $coupon_code,
            'post_content' => '',
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id(),
            'post_type'    => 'shop_coupon',
            'post_excerpt' => $coupon_description,
        );

        $coupon_id = wp_insert_post( $coupon, true );

        // Bail if could not create the coupon
        if( ! $coupon_id ) {

            if( is_wp_error( $coupon_id ) ) {
                $this->result = sprintf( __( 'Could not create the coupon, the server returned: %s', 'gamipress-woocommerce' ), $coupon_id->get_error_message() );
            } else {
                $this->result = __( 'Could not create the coupon', 'gamipress-woocommerce' );
            }

            return;
        }

        $this->post_id = $coupon_id;
        $this->result = __( 'Coupon created successfully', 'gamipress-woocommerce' );

        // Sanitize fields
        if( ! is_array( $action_options['products_ids'] ) ) {
            $action_options['products_ids'] = explode( ',', $action_options['products_ids'] );
        }

        if( ! is_array( $action_options['exclude_product_ids'] ) ) {
            $action_options['exclude_product_ids'] = explode( ',', $action_options['exclude_product_ids'] );
        }

        if( ! is_array( $action_options['product_categories'] ) ) {
            $action_options['product_categories'] = explode( ',', $action_options['product_categories'] );
        }

        if( ! is_array( $action_options['exclude_product_categories'] ) ) {
            $action_options['exclude_product_categories'] = explode( ',', $action_options['exclude_product_categories'] );
        }

        if( ! is_array( $action_options['customer_email'] ) ) {
            $action_options['customer_email'] = explode( ',', $action_options['customer_email'] );
        }

        // Set coupon meta
        update_post_meta( $coupon_id, 'discount_type', $action_options['discount_type'] );
        update_post_meta( $coupon_id, 'coupon_amount', wc_format_decimal( $action_options['coupon_amount'] ) );
        update_post_meta( $coupon_id, 'free_shipping', ( (bool) $action_options['free_shipping'] ) ? 'yes' : 'no' );
        update_post_meta( $coupon_id, 'expiry_date', $this->get_coupon_expiry_date( wc_clean( $action_options['expiry_date'] ) ) );
        update_post_meta( $coupon_id, 'date_expires', $this->get_coupon_expiry_date( wc_clean( $action_options['expiry_date'] ), true ) );
        update_post_meta( $coupon_id, 'minimum_amount', wc_format_decimal( $action_options['minimum_amount'] ) );
        update_post_meta( $coupon_id, 'maximum_amount', wc_format_decimal( $action_options['maximum_amount'] ) );
        update_post_meta( $coupon_id, 'individual_use', ( (bool) $action_options['individual_use'] ) ? 'yes' : 'no' );
        update_post_meta( $coupon_id, 'exclude_sale_items', ( (bool) $action_options['exclude_sale_items'] ) ? 'yes' : 'no' );
        update_post_meta( $coupon_id, 'product_ids', implode( ',', array_filter( array_map( 'intval', $action_options['products_ids'] ) ) ) );
        update_post_meta( $coupon_id, 'exclude_product_ids', implode( ',', array_filter( array_map( 'intval', $action_options['exclude_product_ids'] ) ) ) );
        update_post_meta( $coupon_id, 'product_categories', array_filter( array_map( 'intval', $action_options['product_categories'] ) ) );
        update_post_meta( $coupon_id, 'exclude_product_categories', array_filter( array_map( 'intval', $action_options['exclude_product_categories'] ) ) );
        update_post_meta( $coupon_id, 'customer_email', array_filter( array_map( 'sanitize_email', $action_options['customer_email'] ) ) );
        update_post_meta( $coupon_id, 'usage_limit', absint( $action_options['usage_limit'] ) );
        update_post_meta( $coupon_id, 'limit_usage_to_x_items', absint( $action_options['limit_usage_to_x_items'] ) );
        update_post_meta( $coupon_id, 'usage_limit_per_user', absint( $action_options['usage_limit_per_user'] ) );

    }

    /**
     * expiry_date format
     *
     * @since  1.0.0
     *
     * @param string $expiry_date
     * @param bool $as_timestamp (default: false)
     *
     * @return string|int
     */
    protected function get_coupon_expiry_date( $expiry_date, $as_timestamp = false ) {

        if ( '' != $expiry_date ) {
            if ( $as_timestamp ) {
                return strtotime( $expiry_date );
            }

            return date( 'Y-m-d', strtotime( $expiry_date ) );
        }

        return '';

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log post ID
        add_filter( 'automatorwp_user_completed_action_post_id', array( $this, 'post_id' ), 10, 6 );

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Action custom log post ID
     *
     * @since 1.0.0
     *
     * @param int       $post_id            The post ID, by default 0
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return int
     */
    public function post_id( $post_id, $action, $user_id, $event, $action_options, $automation ) {

        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $post_id;
        }

        if( $this->post_id ) {
            $post_id = $this->post_id;
        }

        return $post_id;

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        // Store result
        $log_meta['result'] = $this->result;

        return $log_meta;
    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        // Bail if log is not assigned to an action
        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-woocommerce' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_WooCommerce_Create_Coupon();