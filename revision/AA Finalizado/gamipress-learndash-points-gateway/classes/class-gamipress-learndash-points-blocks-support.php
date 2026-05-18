<?php
use Automattic\LearnDash\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * WC Points Gateway payment method integration
 *
 * @since 1.5.0
 */
final class GamiPress_LEARNDASH_Points_Gateway_Blocks_Support extends AbstractPaymentMethodType {

    /**
     * Points type slug
     *
     * @var string
     */
    protected $slug = '';

    /**
     * Points type data
     *
     * @var array
     */
    protected $points_type = array();

    public function __construct( $slug, $points_type ) {
        $this->name = 'gamipress_' . $slug;
        $this->slug = $slug;
        $this->points_type = $points_type;
    }

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {

        $this->settings = get_option( 'learndash_' . $this->name . '_settings', array() );

        // Load default settings
        if( empty( $this->settings ) ) {
            $this->settings = array(
                'enabled' => 'yes',
                'title' => $this->points_type['plural_name'],
                'description' => sprintf( __( "Pay using %s.", 'gamipress-learndash-points-gateway' ), $this->points_type['plural_name'] ),
                'conversion_rate' => '100',
				'slug' => $this->slug,
            );
        }
        
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {

        return ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'] ? true : false;

	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {

        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        wp_register_script(
            'gamipress-learndash-points-gateway-block-checkout-js',
            GAMIPRESS_LEARNDASH_POINTS_GATEWAY_URL . 'assets/js/gamipress-learndash-points-gateway-block-checkout' . $suffix . '.js',
            array( 'jquery' ),
            GAMIPRESS_LEARNDASH_POINTS_GATEWAY_VER,
            true
        );

        // Get the points details
        ob_start();
        gamipress_learndash_points_gateway_after_order_total_blocks();
        $cart_details = ob_get_clean();

        wp_localize_script( 'gamipress-learndash-points-gateway-block-checkout-js', 'gamipress_learndash_points_gateway_block_checkout', array(
            'cart_details' => '<div class="gamipress-learndash-points-gateway-checkout-details wc-block-components-totals-wrapper" style="display: none;">' . $cart_details . '</div>',
        ) );

        wp_enqueue_script( 'gamipress-learndash-points-gateway-block-checkout-js' );

		wp_register_script(
			'wc-points-gateway-blocks-integration',
			GAMIPRESS_LEARNDASH_POINTS_GATEWAY_URL . 'assets/js/frontend/blocks.js',
            array( 'react', 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities', 'wp-i18n' ),
            GAMIPRESS_LEARNDASH_POINTS_GATEWAY_VER,
			true
		);

		wp_set_script_translations(
			'wc-points-gateway-blocks-integration',
			'gamipress-learndash-points-gateway'
		);

		return [ 'wc-points-gateway-blocks-integration' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */

    public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'enabled'	=> $this->get_setting( 'enabled' ),
			'supports'    => $this->get_supported_features(),
			'slug'	=> $this->get_setting( 'slug' ),
		];
	}

}
