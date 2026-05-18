<?php
/**
 * Get Async Contact Status
 *
 * @package     AutomatorWP\Integrations\Smoove\Actions\Get-Async-Contact-Status
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Smoove_Get_Async_Contact_Status extends AutomatorWP_Integration_Action {

    public $integration = 'smoove';
    public $action = 'smoove_get_async_contact_status';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {
        error_log( 'Registering Smoove action: ' . $this->action );
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Get async contact status', 'automatorwp-smoove'),
            'select_option'     => __( 'Get <strong>async contact status</strong>', 'automatorwp-smoove'),
            'edit_label'        => __( 'Retrieve async contact status {operation_id}', 'automatorwp-smoove' ),
            'log_label'         => __( 'Retrieved async contact status {operation_id}', 'automatorwp-smoove' ),
            'options'           => array(
                'operation' => array(
                    'from' => 'operation',
                    'default' => __('Operation ID', 'automatorwp-smoove'),
                    'fields' => array(
                        'operation_id' => array(
                            'name'          => __('Operation ID:', 'automatorwp-smoove'),
                            'type'          => 'text',
                            'default'       => '',
                            'required'      => true
                        ),
                    ),
                ),
            ),
        ));
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
        
        $operation_id = $action_options['operation_id'];
        
        if ( empty($operation_id) ) {
            return;
        }

        if( ! automatorwp_smoove_get_option( 'api_key' ) ) {
            $this->result = __( 'Smoove integration is not configured in AutomatorWP settings', 'automatorwp-smoove' );
            return;
        }

        $api_key = automatorwp_smoove_get_option( 'api_key' );
        
        $response = automatorwp_smoove_get_async_contact_status( $operation_id );
        
        if( $response !== false ) {
            $this->result = __( 'Async contact status retrieved successfully', 'automatorwp-smoove' );
        } else {
            $this->result = __( 'Failed to retrieve async contact status', 'automatorwp-smoove' );
        }
    }
}
new AutomatorWP_Smoove_Get_Async_Contact_Status();
