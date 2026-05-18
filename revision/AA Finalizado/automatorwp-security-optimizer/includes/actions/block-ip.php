<?php
/**
 * Block IP
 *
 * @package     AutomatorWP\Security_Optimizer\Actions\Block_IP
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_SG_Security_Block_IP extends AutomatorWP_Integration_Action {

    public $integration = 'security_optimizer';
    public $action = 'sg_security_block_ip';

    /**
     * Registers the IP block action with AutomatorWP.
     *
     * @since 1.0.0
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Block a user by IP', 'automatorwp-security-optimizer' ),
            'select_option' => __( 'Block <strong>user</strong> by IP', 'automatorwp-security-optimizer' ),
            'edit_label'    => sprintf( __( 'Block user %1$s by IP', 'automatorwp-security-optimizer' ), '{user_id}' ),
            'log_label'     => sprintf( __( 'User %1$s IP blocked', 'automatorwp-security-optimizer' ), '{user_id}' ),
            'options'       => array(
                'user_id' => array(
                    'from'   => 'user_id',
                    'fields' => array(
                        'user_id' => array(
                            'name'       => __( 'User:', 'automatorwp-security-optimizer' ),
                            'type'       => 'select',
                            'options_cb' => 'automatorwp_sg_security_options_cb_users',
                        ),
                    ),
                ),
            ),
        ) );
    }

    /**
     * Executes the IP block action via SG Security.
     *
     * @since 1.0.0
     *
     * @param object $action
     * @param int    $user_id
     * @param array  $action_options
     * @param object $automation
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $target_user_id = $action_options['user_id'];

        $this->result = '';

        if ( empty( $target_user_id ) ) {
            return;
        }

        global $wpdb;
        $ip = $wpdb->get_var( $wpdb->prepare( "SELECT ip FROM {$wpdb->sgs_visitors} WHERE user_id = %d LIMIT 1", $target_user_id ) );

        if ( empty( $ip ) ) {
            $this->result = __( 'No IP address found for the selected user.', 'automatorwp-security-optimizer' );
            return;
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
            $this->result = __( 'Could not resolve the visitor record for this IP.', 'automatorwp-security-optimizer' );
            return;
        }

        if ( class_exists( '\SG_Security\Block_Service\Block_Service' ) ) {
            $block_service = new \SG_Security\Block_Service\Block_Service();
            $response = $block_service->block_ip( $visitor_id, 1 );

            if ( ! empty( $response['result'] ) && 1 === intval( $response['result'] ) ) {
                $this->result = __( 'IP blocked successfully.', 'automatorwp-security-optimizer' );
                return;
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
            $this->result = __( 'Could not block the IP.', 'automatorwp-security-optimizer' );
            return;
        }

        $this->result = __( 'IP blocked successfully.', 'automatorwp-security-optimizer' );

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

        $log_meta['result'] = $this->result;

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

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-security-optimizer' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_SG_Security_Block_IP();