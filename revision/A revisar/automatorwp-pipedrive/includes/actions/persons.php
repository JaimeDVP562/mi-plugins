<?php
/**
 * Get Deals
 *
 * @package     AutomatorWP\Integrations\Pipedrive\Actions\Get-Persons
 * @author      AutomatorWP <contact@automatorwp.com>, Jonathan Agudo <jonathanagudo8@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Pipedrive_Get_Persons extends AutomatorWP_Integration_Action {

    public $integration = 'pipedrive';
    public $action = 'pipedrive_get_persons';

    /**
     * Register the action
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Get persons from Pipedrive', 'automatorwp-pipedrive'),
            'select_option'     => __( 'Get <strong>persons</strong>', 'automatorwp-pipedrive'),
            'edit_label'        => sprintf( __( 'Get persons from Pipedrive', 'automatorwp-pipedrive' ), '{person}' ),
            'log_label'         => sprintf( __( 'Get persons from Pipedrive', 'automatorwp-pipedrive' ) , '{person}'),
            'options'           => array(),
        ));
    }

    /**
     * Action execution function
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        // Bail if Pipedrive not configured
        if( ! automatorwp_pipedrive_get_option( 'consumer_key' ) ) {
            $this->result = __( 'Pipedrive integration is not configured in AutomatorWP settings', 'automatorwp-pipedrive' );
            return;
        }

        // Get the API token
        $consumer_key = automatorwp_pipedrive_get_option( 'consumer_key' );

        // Prepare the request URL to get the persons
        $url = 'https://api.pipedrive.com/v1/persons?api_token=' . $consumer_key;

        // Send GET request to fetch the persons
        $response = wp_remote_get( $url );

        if( is_wp_error( $response ) ) {
            $this->result = __( 'Error fetching persons from Pipedrive: ', 'automatorwp-pipedrive' ) . $response->get_error_message();
            return;
        }

        // Get the response body and decode it
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        // Check if the request was successful
        if( isset( $data['success'] ) && $data['success'] ) {
            // Process the data if needed, for example, store the persons or log them
            $persons = $data['data'];  // Array of persons
            $this->result = __( 'Fetched ' . count( $persons ) . ' persons from Pipedrive', 'automatorwp-pipedrive' );
        } else {
            $this->result = __( 'The persons could not be fetched', 'automatorwp-pipedrive' );
        }
    }

    /**
     * Register required hooks
     */
    public function hooks() {
        // Configuration notice
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Configuration notice
     */
    public function configuration_notice( $object, $item_type ) {
        // Bail if action type don't match this action
        if( $item_type !== 'action' ) {
            return;
        }

        if( $object->type !== $this->action ) {
            return;
        }

        // Warn user if the authorization has not been setup from settings
        if( ! automatorwp_pipedrive_get_option( 'consumer_key' ) ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Pipedrive settings</a> to get this action to work.', 'automatorwp-pipedrive' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-pipedrive'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-pipedrive' ),
                    'https://automatorwp.com/docs/pipedrive/'
                ); ?>
            </div>
        <?php endif;
    }

    /**
     * Action custom log meta
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        // Store the action's result
        $log_meta['result'] = $this->result;

        return $log_meta;
    }

    /**
     * Action custom log fields
     */
    public function log_fields( $log_fields, $log, $object ) {
        // Bail if log is not assigned to an action
        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-pipedrive' ),
            'type' => 'text',
        );

        return $log_fields;
    }
}

new AutomatorWP_Pipedrive_Get_Persons();