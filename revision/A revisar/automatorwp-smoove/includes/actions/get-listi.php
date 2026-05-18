<?php
/**
 * Get List
 *
 * @package     AutomatorWP\Integrations\Smoove\Actions\Get-Listi
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Smoove_Get_Listi extends AutomatorWP_Integration_Action {

    public $integration = 'smoove';
    public $action = 'smoove_get_listi';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {
        error_log( 'Registering Smoove action: ' . $this->action );
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Get a list', 'automatorwp-smoove'),
            'select_option'     => __( 'Get a <strong>list</strong>', 'automatorwp-smoove'),
            'edit_label'        => __( 'Retrieve list {list_id}', 'automatorwp-smoove' ),
            'log_label'         => __( 'Retrieved list {list_id}', 'automatorwp-smoove' ),
            'options'           => array(
                'list' => array(
                    'from' => 'list',
                    'default' => __('List ID', 'automatorwp-smoove'),
                    'fields' => array(
                        'list_id' => array(
                            'name'          => __('List ID:', 'automatorwp-smoove'),
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
        
        $list_id = $action_options['list_id'];
        
        if ( empty($list_id) ) {
            return;
        }

        if( ! automatorwp_smoove_get_option( 'api_key' ) ) {
            $this->result = __( 'Smoove integration is not configured in AutomatorWP settings', 'automatorwp-smoove' );
            return;
        }

        $api_key = automatorwp_smoove_get_option( 'api_key' );
        
        $response = automatorwp_smoove_get_list( $list_id );
        
        if( $response !== false ) {
            $this->result = __( 'List retrieved successfully', 'automatorwp-smoove' );
        } else {
            $this->result = __( 'Failed to retrieve list', 'automatorwp-smoove' );
        }
    }
}
new AutomatorWP_Smoove_Get_Listi();
