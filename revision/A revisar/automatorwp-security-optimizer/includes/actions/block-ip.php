<?php
/**
 * Action: Block IP Address
 * * @package AutomatorWP\Security_Optimizer\Actions\Block_IP
 */

if( !defined( 'ABSPATH' ) ) exit;

/**
 * AutomatorWP action class for blocking a user IP in SG Security.
 */
class AutomatorWP_SG_Security_Block_IP extends AutomatorWP_Integration_Action {

    public $integration = 'security_optimizer';
    public $action = 'sg_security_block_ip';

    /**
     * Registers the IP block action with AutomatorWP.
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Block a user by IP', 'automatorwp' ),
            'select_option' => __( 'Block <strong>user</strong> by IP', 'automatorwp' ),
            'edit_label'    => sprintf( __( 'Block user %1$s by IP', 'automatorwp' ), '{user_id}' ),
            'log_label'     => sprintf( __( 'User %1$s IP blocked', 'automatorwp' ), '{user_id}' ),
            'options'       => array(
                'user_id' => array(
                    'from' => 'user_id',
                    'fields' => array(
                        'user_id' => array(
                            'name' => __( 'User:', 'automatorwp' ),
                            'type' => 'select',
                            'options_cb' => 'automatorwp_sg_security_options_cb_users',
                        )
                    )
                ),
            ),
        ) );
    }

    /**
     * Executes the IP block action via SG Security.
     *
     * @param object $action         The current action object.
     * @param int    $user_id        The ID of the user that triggered the action.
     * @param array  $action_options Action options provided by AutomatorWP.
     * @param object $automation     The automation object.
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        $target_user_id = $action_options['user_id'];
        if ( empty( $target_user_id ) ) {
            return new WP_Error( 'missing_user_id', __( 'No target user selected.', 'automatorwp' ) );
        }

        global $wpdb;
        $ip = $wpdb->get_var( $wpdb->prepare( "SELECT ip FROM {$wpdb->sgs_visitors} WHERE user_id = %d LIMIT 1", $target_user_id ) );
        if ( empty( $ip ) ) {
            return new WP_Error( 'ip_not_found', __( 'No IP address found for the selected user.', 'automatorwp' ) );
        }

        $visitor_id = false;
        if ( class_exists( '\SG_Security\Activity_Log\Activity_Log_Helper' ) ) {
            $activity_helper = new \SG_Security\Activity_Log\Activity_Log_Helper();
            $visitor_id = $activity_helper->get_visitor_by_ip( $ip );
        }

        if ( ! $visitor_id ) {
            $visitor_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->sgs_visitors} WHERE ip = %s AND user_id = 0 LIMIT 1", $ip ) );
            if ( ! $visitor_id ) {
                $wpdb->insert( $wpdb->sgs_visitors, array( 'ip' => $ip, 'user_id' => 0 ), array( '%s', '%d' ) );
                $visitor_id = $wpdb->insert_id;
            }
        }

        if ( empty( $visitor_id ) ) {
            return new WP_Error( 'visitor_not_found', __( 'Could not resolve the visitor record for this IP.', 'automatorwp' ) );
        }

        $updated = false;

        if ( class_exists( '\SG_Security\Block_Service\Block_Service' ) ) {
            $block_service = new \SG_Security\Block_Service\Block_Service();
            $response = $block_service->block_ip( $visitor_id, 1 );

            if ( ! empty( $response['result'] ) && 1 === intval( $response['result'] ) ) {
                return true;
            }
        }

        $updated = $wpdb->update(
            $wpdb->sgs_visitors,
            array(
                'block'      => 1,
                'blocked_on' => time(),
            ),
            array( 'id' => $visitor_id ),
            array( '%d', '%d' ),
            array( '%d' )
        );

        if ( false === $updated ) {
            return new WP_Error( 'block_failed', __( 'Could not block the IP.', 'automatorwp' ) );
        }

        return true;
    }
}

new AutomatorWP_SG_Security_Block_IP();