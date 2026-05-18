<?php
/**
 * Create CRM Deal in SendPulse
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Sendpulse_Create_CRM_Deal extends AutomatorWP_Integration_Action {
    public $integration = 'sendpulse';
    public $action = 'sendpulse_create_crm_deal';
    public $result = '';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create a CRM deal in SendPulse', 'automatorwp-sendpulse' ),
            'select_option' => __( 'Create a <strong>CRM deal</strong> in SendPulse', 'automatorwp-sendpulse' ),
            'edit_label'    => sprintf( __( 'Create a CRM deal named %s', 'automatorwp-sendpulse' ), '{deal_name}' ),
            'log_label'     => sprintf( __( 'Create a CRM deal named %s', 'automatorwp-sendpulse' ), '{deal_name}' ),
            'options'       => array(
                'deal_name' => array( 
                    'from'    => 'deal_name',
                    'fields'  => array(
                        'deal_name' => array(
                            'name'    => __( 'Deal Name:', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'The name of the deal/card (e.g., New Website Lead).', 'automatorwp-sendpulse' ),
                            'type'    => 'text',
                            'default' => '',
                            'required' => true,
                        ),
                        'pipeline_id' => array(
                            'name'    => __( 'Pipeline ID:', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'The numeric ID of your Pipeline (Board) in SendPulse CRM.', 'automatorwp-sendpulse' ),
                            'type'    => 'text',
                            'default' => '',
                            'required' => true,
                        ),
                        'step_id' => array(
                            'name'    => __( 'Step/Stage ID:', 'automatorwp-sendpulse' ),
                            'desc'    => __( 'The numeric ID of the Stage (Column) where the deal should be placed.', 'automatorwp-sendpulse' ),
                            'type'    => 'text',
                            'default' => '',
                            'required' => true,
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        $deal_name   = isset( $action_options['deal_name'] ) ? sanitize_text_field( $action_options['deal_name'] ) : '';
        $pipeline_id = isset( $action_options['pipeline_id'] ) ? sanitize_text_field( $action_options['pipeline_id'] ) : '';
        $step_id     = isset( $action_options['step_id'] ) ? sanitize_text_field( $action_options['step_id'] ) : '';

        if ( empty( $deal_name ) || empty( $pipeline_id ) || empty( $step_id ) ) {
            $this->result = __( 'Error: Missing deal name, pipeline ID, or step ID.', 'automatorwp-sendpulse' );
            return;
        }

        $endpoint = '/crm/v1/deals';
        $payload = array(
            'name'        => $deal_name,
            'pipeline_id' => (int) $pipeline_id,
            'step_id'     => (int) $step_id,
        );
        
        $response = automatorwp_sendpulse_request( 'POST', $endpoint, array( 'body' => $payload ) );

        if ( is_wp_error( $response ) ) {
            $this->result = sprintf( __( 'SendPulse API error: %s', 'automatorwp-sendpulse' ), $response->get_error_message() );
            return;
        }

        $this->result = __( 'CRM Deal created successfully via SendPulse.', 'automatorwp-sendpulse' );
    }

    public function hooks() {
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );
        parent::hooks();
    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        if ( $action->type !== $this->action ) return $log_meta;
        $log_meta['result'] = (string) $this->result;
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if ( $log->type !== 'action' || $object->type !== $this->action ) return $log_fields;
        $log_fields['result'] = array( 'name' => __( 'Result:', 'automatorwp-sendpulse' ), 'type' => 'text' );
        return $log_fields;
    }
}

new AutomatorWP_Sendpulse_Create_CRM_Deal();