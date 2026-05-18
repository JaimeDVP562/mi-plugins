<?php
/**
 * User Is Active
 *
 * @package     AutomatorWP\Integrations\MemberPress\Filters\User_Is_Active
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_MemberPress_User_Is_Active_Filter extends AutomatorWP_Integration_Filter {

    public $integration = 'memberpress';
    public $filter = 'memberpress_user_is_active';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_filter( $this->filter, array(
            'integration'       => $this->integration,
            'label'             => __( 'User is active in membership', 'automatorwp-memberpress' ),
            'select_option'     => __( 'User <strong>is active</strong> in membership', 'automatorwp-memberpress' ),
            /* translators: %1$s: Membership. */
            'edit_label'        => sprintf( __( '%1$s', 'automatorwp-memberpress' ), '{post}'  ),
            /* translators: %1$s: Membership. */
            'log_label'         => sprintf( __( '%1$s', 'automatorwp-memberpress' ), '{post}' ),
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name' => __( 'Membership:', 'automatorwp-memberpress' ),
                    'option_none_label' => __( 'any membership', 'automatorwp-memberpress' ),
                    'post_type' => 'memberpressproduct'
                ) ),
            ),
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_filter    True if user deserves filter, false otherwise
     * @param stdClass  $filter             The filter object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $filter_options     The filter's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */
    public function user_deserves_filter( $deserves_filter, $filter, $user_id, $event, $filter_options, $automation ) {

        // Shorthand
        $membership = $filter_options['post'];

        if ($membership !== 'any') {
            $membership_name = get_post( $membership )->post_title;
        } else{
            $membership_name = 'any membership';
        }
        
        // Bail if wrong configured
        if( empty( $membership ) ) {
            $this->result = __( 'Filter not passed. Group option has not been configured.', 'automatorwp-memberpress' );
            return false;
        }

        // To get the active subscriptions
        $mepr_user_obj            = new \MeprUser( $user_id );
        $active_subscriptions = $mepr_user_obj->active_product_subscriptions( 'ids' );

        if ( $membership === 'any' ) {
            // Don't deserve if user is not in this membership
            if( empty( $active_subscriptions ) ) {
                $this->result = sprintf( __( 'Filter not passed. User is not active in membership "%1$s".', 'automatorwp-memberpress' ),
                    $membership_name
                );
                return false;
            }
        } else{
            // Don't deserve if user is not in this membership
            if( ! in_array( $membership, $active_subscriptions ) ) {
                $this->result = sprintf( __( 'Filter not passed. User is not active in membership "%1$s".', 'automatorwp-memberpress' ),
                    $membership_name
                );
                return false;
            }
        }

        $this->result = sprintf( __( 'Filter passed. User is active in membership "%1$s".', 'automatorwp-memberpress' ),
            $membership_name
        );

        return $deserves_filter;

    }

}

new AutomatorWP_MemberPress_User_Is_Active_Filter();