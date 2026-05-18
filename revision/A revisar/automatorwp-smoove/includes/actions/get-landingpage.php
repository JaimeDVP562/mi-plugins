<?php
/**
 * Get Landing Page
 *
 * @package     AutomatorWP\Integrations\Smoove\Actions\Get-LandingPage
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Smoove_Get_LandingPage extends AutomatorWP_Integration_Action {

    public $integration = 'smoove';
    public $action = 'smoove_get_landingpage';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {
        error_log( 'Registering Smoove action: ' . $this->action );
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Get a landing page', 'automatorwp-smoove'),
            'select_option'     => __( 'Get a <strong>landing page</strong>', 'automatorwp-smoove'),
            'edit_label'        => __( 'Retrieve landing page {landing_page_id}', 'automatorwp-smoove' ),
            'log_label'         => __( 'Retrieved landing page {landing_page_id}', 'automatorwp-smoove' ),
            'options'           => array(
                'landing_page' => array(
                    'from' => 'landing_page',
                    'default' => __('Landing Page ID', 'automatorwp-smoove'),
                    'fields' => array(
                        'landing_page_id' => array(
                            'name'          => __('Landing Page ID:', 'automatorwp-smoove'),
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
        
        $landing_page_id = $action_options['landing_page_id'];
        
        if ( empty($landing_page_id) ) {
            return;
        }

        if( ! automatorwp_smoove_get_option( 'api_key' ) ) {
            $this->result = __( 'Smoove integration is not configured in AutomatorWP settings', 'automatorwp-smoove' );
            return;
        }

        $api_key = automatorwp_smoove_get_option( 'api_key' );
        
        $response = automatorwp_smoove_get_landing_page( $landing_page_id );
        
        if( $response !== false ) {
            $this->result = __( 'Landing page retrieved successfully', 'automatorwp-smoove' );
        } else {
            $this->result = __( 'Failed to retrieve landing page', 'automatorwp-smoove' );
        }
    }
}
new AutomatorWP_Smoove_Get_LandingPage();
