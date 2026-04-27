<?php
/**
 * Clear Conversation
 *
 * @package     AutomatorWP\Integrations\Cohere\Actions\Clear_Conversation
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Cohere_Clear_Conversation extends AutomatorWP_Integration_Action 
{
    public $integration = 'cohere';
    public $action      = 'cohere_clear_conversation';

    public function register()
    {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Clear a Cohere conversation history', 'automatorwp-cohere' ),
            'select_option' => __( 'Clear a Cohere <strong>conversation</strong> history', 'automatorwp-cohere' ),
            'edit_label'    => __( 'Clear the Cohere conversation history for {conversation_id}', 'automatorwp-cohere' ),
            'log_label'     => __( 'Clear a Cohere conversation history', 'automatorwp-cohere' ),
            'options'       => array(
                'conversation_id' => array(
                    'from'    => 'action',
                    'default' => __( 'conversation_id', 'automatorwp-cohere' ),
                    'fields'  => array(
                        'conversation_id' => array(
                            'name'     => __( 'Conversation ID', 'automatorwp-cohere' ),
                            'desc'     => __( 'The conversation ID to clear. Supports tags.', 'automatorwp-cohere' ),
                            'type'     => 'text',
                            'default'  => '',
                            'required' => true,
                        ),
                    ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation )
    {
        $conversation_id = isset( $action_options['conversation_id'] ) ? $action_options['conversation_id'] : '';

        if ( empty( $conversation_id ) ) {
            $this->result = __( 'Conversation ID field is empty.', 'automatorwp-cohere' );
            return;
        }

        automatorwp_cohere_clear_conversation_history( $conversation_id );

        $this->result = sprintf( __( 'Conversation "%s" cleared successfully.', 'automatorwp-cohere' ), $conversation_id );
    }

    public function hooks()
    {
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ),   10, 5 );
        add_filter( 'automatorwp_log_fields',                     array( $this, 'log_fields' ), 10, 3 );
        parent::hooks();
    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation )
    {
        if ( $action->type !== $this->action ) return $log_meta;
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object )
    {
        if ( $log->type !== 'action' ) return $log_fields;
        if ( $object->type !== $this->action ) return $log_fields;
        $log_fields['result'] = array( 'name' => __( 'Result:', 'automatorwp-cohere' ), 'type' => 'text' );
        return $log_fields;
    }
}

new AutomatorWP_Cohere_Clear_Conversation();
