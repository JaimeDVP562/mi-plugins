<?php
/**
 * Clear Conversation
 *
 * @package     AutomatorWP\Integrations\Perplexity\Actions\Clear_Conversation
 * @since       1.1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Perplexity_Clear_Conversation extends AutomatorWP_Integration_Action
{

    public $integration = 'perplexity';
    public $action      = 'perplexity_clear_conversation';

    /**
     * Register the action
     *
     * @since 1.1.0
     */
    public function register()
    {
        automatorwp_register_action(
            $this->action,
            array(
                'integration'   => $this->integration,
                'label'         => __( 'Clear a Perplexity conversation history', 'automatorwp-perplexity' ),
                'select_option' => __( 'Clear a Perplexity <strong>conversation history</strong>', 'automatorwp-perplexity' ),
                'edit_label'    => __( 'Clear the Perplexity conversation history for {conversation_id}', 'automatorwp-perplexity' ),
                'log_label'     => __( 'Clear a Perplexity conversation history', 'automatorwp-perplexity' ),
                'options'       => array(
                    'conversation_id' => array(
                        'from'    => 'conversation_id',
                        'default' => __( 'conversation', 'automatorwp-perplexity' ),
                        'fields'  => array(
                            'conversation_id' => array(
                                'name'     => __( 'Conversation ID', 'automatorwp-perplexity' ),
                                'desc'     => __( 'The Conversation ID to reset. Supports tags. Must match the ID used in the conversation action.', 'automatorwp-perplexity' ),
                                'type'     => 'text',
                                'default'  => '',
                                'required' => true,
                            ),
                        ),
                    ),
                ),
                'tags'          => array(),
            )
        );
    }

    /**
     * Action execution function
     *
     * @since 1.1.0
     *
     * @param stdClass $action         The action object
     * @param int      $user_id        The user ID
     * @param array    $action_options The action's stored options (with tags already passed)
     * @param stdClass $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation )
    {
        $conversation_id = isset( $action_options['conversation_id'] ) ? $action_options['conversation_id'] : '';

        if ( empty( $conversation_id ) ) {
            $this->result = __( 'Conversation ID field is empty.', 'automatorwp-perplexity' );
            return;
        }

        automatorwp_perplexity_clear_conversation_history( $conversation_id );

        $this->result = sprintf( __( 'Conversation history cleared for ID: %s', 'automatorwp-perplexity' ), $conversation_id );
    }

    /**
     * Register required hooks
     *
     * @since 1.1.0
     */
    public function hooks()
    {
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ),   10, 5 );
        add_filter( 'automatorwp_log_fields',                     array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /** @since 1.1.0 */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation )
    {
        if ( $action->type !== $this->action ) return $log_meta;
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    /** @since 1.1.0 */
    public function log_fields( $log_fields, $log, $object )
    {
        if ( $log->type !== 'action' ) return $log_fields;
        if ( $object->type !== $this->action ) return $log_fields;

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-perplexity' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_Perplexity_Clear_Conversation();
