<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( class_exists( 'AutomatorWP_Integration_Action' ) ) {
    class AutomatorWP_Mailmint_Action_Add_Subscriber extends AutomatorWP_Integration_Action {
        public $integration = 'mailmint';
        public $action = 'mailmint_add_subscriber';

        public function register() {
            automatorwp_register_action( $this->integration, $this->action, array(
                'label' => __( 'Mail Mint - Add / Update subscriber', 'automatorwp-funnels-mail-mint' ),
                'fields' => array(
                    'email' => array( 'label' => __( 'Email', 'automatorwp-funnels-mail-mint' ) ),
                    'first_name' => array( 'label' => __( 'First name', 'automatorwp-funnels-mail-mint' ) ),
                    'list_id' => array( 'label' => __( 'List ID', 'automatorwp-funnels-mail-mint' ) )
                ),
                'function' => array( $this, 'execute' )
            ) );
        }

        public function execute( $action, $user_id, $action_options, $automation ) {
            // $action_options contains the selected options/fields for the action
            $fields = is_array( $action_options ) ? $action_options : array();

                // Build data and map from WP user when possible
                $email = isset( $fields['email'] ) ? sanitize_email( $fields['email'] ) : '';
                $first_name = isset( $fields['first_name'] ) ? sanitize_text_field( $fields['first_name'] ) : '';
                $list_id = isset( $fields['list_id'] ) ? sanitize_text_field( $fields['list_id'] ) : '';

                if ( empty( $email ) && $user_id ) {
                    $user = get_userdata( $user_id );
                    if ( $user ) {
                        $email = $user->user_email;
                        if ( empty( $first_name ) ) {
                            $first_name = $user->first_name ? $user->first_name : $user->display_name;
                        }
                    }
                }

                if ( empty( $email ) || ! is_email( $email ) ) {
                    return false;
                }

                $data = array(
                    'email' => $email,
                    'first_name' => $first_name,
                    'list_id' => $list_id,
                );

                $resp = automatorwp_mailmint_add_contact( $data );

                // Log based on response
                if ( is_wp_error( $resp ) ) {
                    return false;
                }

                if ( $resp === false ) {
                    return false;
                }

                return true;
        }
    }

    add_action( 'automatorwp_register_actions', function() {
        $a = new AutomatorWP_Mailmint_Action_Add_Subscriber();
        $a->register();
    } );
}
