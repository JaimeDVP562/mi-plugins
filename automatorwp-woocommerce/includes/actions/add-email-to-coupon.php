<?php
/**
 * Add Email To Coupon
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Add_Email_To_Coupon
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Add_Email_To_Coupon extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_add_email_to_coupon';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add email(s) to a coupon', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'Add email(s) to a <strong>coupon</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Email. %2$s: Coupon. */
            'edit_label'        => sprintf( __( 'Add %1$s to %2$s', 'automatorwp-woocommerce' ), '{emails}', '{post}' ),
            /* translators: %1$s: Email. %2$s: Coupon. */
            'log_label'         => sprintf( __( 'Add %1$s to %2$s', 'automatorwp-woocommerce' ), '{emails}', '{post}' ),
            'options'           => array(
                'emails' => array(
                    'from' => 'emails',
                    'default' => __( 'email(s)', 'automatorwp-woocommerce' ),
                    'fields' => array(
                        'emails' => array(
                            'name' => __( 'Email address(es):', 'automatorwp-woocommerce' ),
                            'desc' => __( 'Single or comma-separated list of email addresses. You can also use an asterisk (*) to match parts of an email. For example "*@gmail.com" would match all gmail addresses.', 'automatorwp-woocommerce' ),
                            'type' => 'text',
                        )
                    )
                ),
                'post' => automatorwp_utilities_post_option( array(
                    'name'              => __( 'Coupon:', 'automatorwp-woocommerce' ),
                    'option_none_label' => __( 'Choose a coupon', 'automatorwp-woocommerce' ),
                    'option_custom'         => true,
                    'option_custom_desc'    => __( 'Coupon ID', 'automatorwp-woocommerce' ),
                    'post_type'         => 'shop_coupon',
                ) ),
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

        // Shorthand
        $emails = $action_options['emails'];
        $coupon_id = absint( $action_options['post'] );

        // Bail if not coupon selected
        if( $coupon_id === 0 ) {
            return;
        }

        // Get the coupon emails
        $customer_email = array_filter( (array) get_post_meta( $coupon_id, 'customer_email', true ) );
        $emails = explode( ',', $emails );
        $validated_emails = array();

        foreach( $emails as $i => $email ) {
            $email = sanitize_email( trim( $email ) );

            // Skip if email is already
            if( in_array( $email, $customer_email ) ) {
                continue;
            }

            $validated_emails[] = $email;
        }

        // Update coupon emails
        if( ! empty( $validated_emails ) ) {
            update_post_meta( $coupon_id, 'customer_email', array_filter( array_map( 'sanitize_email', $validated_emails ) ) );
        }

    }

}

new AutomatorWP_WooCommerce_Add_Email_To_Coupon();