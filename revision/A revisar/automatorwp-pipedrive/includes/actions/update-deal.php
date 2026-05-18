<?php
/**
 * Update Deal
 *
 * @package     AutomatorWP\Integrations\Pipedrive\Actions\Update-Deal
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Pipedrive_Update_Deal extends AutomatorWP_Integration_Action {

    public $integration = 'pipedrive';
    public $action = 'pipedrive_update_deal';

    /**
     * Register the action
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Update a deal', 'automatorwp-pipedrive'),
            'select_option'     => __( 'Update a <strong>deal</strong>', 'automatorwp-pipedrive'),
            'edit_label'        => sprintf( __( 'Update %1$s', 'automatorwp-pipedrive' ), '{deal}' ),
            'log_label'         => sprintf( __( 'Update %1$s', 'automatorwp-pipedrive' ) , '{deal}'),
            'options'           => array(
                'deal' => array(
                    'from' => 'deal',
                    'default' => __('deal', 'automatorwp'),
                    'fields' => array(
                        'deal_id' => array(
                            'name'          => __('Deal ID:', 'automatorwp-pipedrive'),
                            'type'          => 'text',
                            'default'       => '',
                            'required'      => true
                        ),
                        'deal_title' => array(
                            'name'          => __('Deal title:', 'automatorwp-pipedrive'),
                            'type'          => 'text',
                            'default'       => '',
                            'required'      => false
                        ),
                        'deal_value' => array(
                            'name'          => __('Deal value:', 'automatorwp-pipedrive'),
                            'type'          => 'text',
                            'default'       => '',
                            'required'      => false
                        ),
                        'deal_currency' => array(
                            'name'          => __('Deal currency:', 'automatorwp-pipedrive'),
                            'type'          => 'text',
                            'default'       => 'USD',
                            'required'      => false
                        ),
                    )
                ),
            ),
        ));
    }

    /**
     * Action execution function
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        // Shorthand
        $deal_id = $action_options['deal_id'];
        $deal_title = $action_options['deal_title'];
        $deal_value = $action_options['deal_value'];
        $deal_currency = $action_options['deal_currency'];

        // Bail if deal_id is empty
        if ( empty( $deal_id ) ) {
            return;
        }

        // Bail if Pipedrive not configured
        if( ! automatorwp_pipedrive_get_option( 'consumer_key' ) ) {
            $this->result = __( 'Pipedrive integration is not configured in AutomatorWP settings', 'automatorwp-pipedrive' );
            return;
        }

        $consumer_key = automatorwp_pipedrive_get_option( 'consumer_key' );

        $body = array();
        if ( ! empty( $deal_title ) ) {
            $body['title'] = $deal_title;
        }
        if ( ! empty( $deal_value ) ) {
            $body['value'] = $deal_value;
        }
        if ( ! empty( $deal_currency ) ) {
            $body['currency'] = $deal_currency;
        }

        $response = wp_remote_post( 'https://api.pipedrive.com/v1/deals/' . $deal_id . '?api_token=' . $consumer_key, array(
            'method' => 'PUT',
            'body' => json_encode( $body ),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if( is_wp_error( $response ) ) {
            $this->result = __( 'Error updating deal in Pipedrive: ', 'automatorwp-pipedrive' ) . $response->get_error_message();
            return;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if( isset( $data['success'] ) && $data['success'] ) {
            $this->result = __( 'Updated deal in Pipedrive', 'automatorwp-pipedrive' );
        } else {
            $this->result = __( 'The deal could not be updated', 'automatorwp-pipedrive' );
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

new AutomatorWP_Pipedrive_Update_Deal();