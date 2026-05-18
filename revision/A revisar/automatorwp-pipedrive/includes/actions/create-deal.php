<?php
/**
 * Create Deal
 *
 * @package     AutomatorWP\Integrations\Pipedrive\Actions\Create-Deal
 * @author      AutomatorWP <contact@automatorwp.com>, Jonathan Agudo <jonathanagudo8@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Pipedrive_Create_Deal extends AutomatorWP_Integration_Action {

    public $integration = 'pipedrive';
    public $action = 'pipedrive_create_deal';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register(){
            
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Create a new deal', 'automatorwp-pipedrive'),
            'select_option'     => __( 'Create a new <strong>deal</strong>', 'automatorwp-pipedrive'),
            /* translators: %1$s: Deal. */
            'edit_label'        => sprintf( __( 'Create a new %1$s', 'automatorwp-pipedrive' ), '{deal}' ),
            /* translators: %1$s: Deal. */
            'log_label'         => sprintf( __( 'Create a new %1$s', 'automatorwp-pipedrive' ) , '{deal}' ),
            'options'           => array(
                'deal' => array(
                    'from' => 'deal',
                    'default' => __('deal', 'automatorwp-pipedrive'),
                    'fields' => array(
                        'deal_title' => array(
                            'name'          => __('Deal title:', 'automatorwp-pipedrive'),
                            'type'          => 'text',
                            'default'       => '',
                            'required'      => true
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
        
        // Shorthand
        $deal_title = $action_options['deal_title'];
        $deal_value = $action_options['deal_value'];
        $deal_currency = $action_options['deal_currency'];
        
        // Bail if any required field is empty
        if ( empty( $deal_title ) || empty( $deal_value ) || empty( $deal_currency ) ) {
            return;
        }

        // Bail if Pipedrive not configured
        if( ! automatorwp_pipedrive_get_option( 'consumer_key' ) ) {
            $this->result = __( 'Pipedrive integration is not configured in AutomatorWP settings', 'automatorwp-pipedrive' );
            return;
        }

        $consumer_key = automatorwp_pipedrive_get_option( 'consumer_key' );

        $response = wp_remote_post( 'https://api.pipedrive.com/v1/deals?api_token=' . $consumer_key, array(
            'body' => json_encode( array(
                'title' => $deal_title,
                'value' => $deal_value,
                'currency' => $deal_currency,
            ) ),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ) );

        if( is_wp_error( $response ) ) {
            $this->result = __( 'Error creating deal in Pipedrive: ', 'automatorwp-pipedrive' ) . $response->get_error_message();
            return;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if( isset( $data['success'] ) && $data['success'] ) {
            $this->result = __( 'Created deal in Pipedrive', 'automatorwp-pipedrive' );
        } else {
            $this->result = __( 'The deal could not be created', 'automatorwp-pipedrive' );
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
     *
     * @param stdClass  $object     The trigger/action object
     * @param string    $item_type  The object type (trigger|action)
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
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
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
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
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
new AutomatorWP_Pipedrive_Create_Deal();