<?php
/**
 * Remove Membership
 *
 * @package     AutomatorWP\Integrations\WooCommerce\Actions\Remove_Membership
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WooCommerce_Remove_Membership extends AutomatorWP_Integration_Action {

    public $integration = 'woocommerce';
    public $action = 'woocommerce_remove_membership';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Remove user from membership', 'automatorwp-woocommerce' ),
            'select_option'     => __( 'Remove user from <strong>membership</strong>', 'automatorwp-woocommerce' ),
            /* translators: %1$s: Membership. */
            'edit_label'        => sprintf( __( 'Remove user from %1$s', 'automatorwp-woocommerce' ), '{post}' ),
            /* translators: %1$s: Membership. */
            'log_label'         => sprintf( __( 'Remove user from %1$s', 'automatorwp-woocommerce' ), '{post}' ),
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name'              => __( 'Membership:', 'automatorwp-woocommerce' ),
                    'option_default'    => __( 'membership', 'automatorwp-woocommerce' ),
                    'placeholder'       => __( 'Select a membership', 'automatorwp-woocommerce' ),
                    'option_none'       => true,
                    'option_none_label'     => __( 'all memberships', 'automatorwp-woocommerce' ),
                    'option_custom'         => true,
                    'option_custom_desc'    => __( 'Membership ID', 'automatorwp-woocommerce' ),
                    'post_type'         => 'wc_membership_plan',
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
        $membership_id = $action_options['post'];

        if( $membership_id === 'any' ) {

            $user_memberships = wc_memberships_get_user_memberships( $user_id );

            // Bail if no memberships to remove from
            if ( empty( $user_memberships ) ) {
                return;
            }
			
            // Remove user from all memberships
            foreach ( $user_memberships as $membership ) {
				// To cancel membership before deleting
                $membership->cancel_membership();
                wp_delete_post( $membership->post->ID );
            }

        } else {

            $membership_id = absint( $membership_id );

            // Bail if not membership provided
            if( $membership_id === 0 ) {
                return;
            }

            $is_user_member = wc_memberships_is_user_member( $user_id, $membership_id );

            // Bail if user is not on this membership
            if ( ! $is_user_member ) {
                return;
            }

            // Remove user from a specific membership
            $user_membership = wc_memberships_get_user_membership( $user_id, $membership_id );
			
            // To cancel membership before deleting
            $user_membership->cancel_membership();

            wp_delete_post( $user_membership->post->ID );

        }

    }

}

new AutomatorWP_WooCommerce_Remove_Membership();