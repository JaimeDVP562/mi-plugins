<?php
/**
 * Multi-turn Conversation
 *
 * @package     AutomatorWP\Integrations\Cohere\Actions\Conversation
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Cohere_Conversation extends AutomatorWP_Integration_Action 
{
    public $integration = 'cohere';
    public $action      = 'cohere_conversation';

    public function register()
    {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Send a message in a Cohere conversation', 'automatorwp-cohere' ),
            'select_option' => __( 'Send a <strong>message</strong> in a Cohere <strong>conversation</strong>', 'automatorwp-cohere' ),
            'edit_label'    => __( 'Send {message} in a Cohere conversation', 'automatorwp-cohere' ),
            'log_label'     => __( 'Send a message in a Cohere conversation', 'automatorwp-cohere' ),
            'options'       => array(
                'message' => array(
                    'from'    => 'action',
                    'default' => __( 'message', 'automatorwp-cohere' ),
                    'fields'  => array(
                        'model' => array(
                            'name'    => __( 'Model', 'automatorwp-cohere' ),
                            'desc'    => __( 'Cohere Command model to use.', 'automatorwp-cohere' ),
                            'type'    => 'select',
                            'options' => automatorwp_cohere_get_chat_models(),
                            'default' => 'command-a-03-2025',
                        ),
                        'conversation_id' => array(
                            'name'     => __( 'Conversation ID', 'automatorwp-cohere' ),
                            'desc'     => __( 'Unique key to group messages (e.g. {user_id}). Messages sharing the same ID maintain context.', 'automatorwp-cohere' ),
                            'type'     => 'text',
                            'default'  => '',
                            'required' => true,
                        ),
                        'system_message' => array(
                            'name'    => __( 'System Message', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) Applied only on the first turn. Supports tags.', 'automatorwp-cohere' ),
                            'type'    => 'textarea',
                            'default' => '',
                        ),
                        'message' => array(
                            'name'     => __( 'User Message', 'automatorwp-cohere' ),
                            'desc'     => __( 'Message to send. Supports tags.', 'automatorwp-cohere' ),
                            'type'     => 'textarea',
                            'default'  => '',
                            'required' => true,
                        ),
                        'max_tokens' => array(
                            'name'    => __( 'Max Tokens', 'automatorwp-cohere' ),
                            'desc'    => __( 'Maximum response length in tokens.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => 1024,
                        ),
                        'temperature' => array(
                            'name'    => __( 'Temperature', 'automatorwp-cohere' ),
                            'desc'    => __( '0 = deterministic, 1 = creative. Default: 0.3.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => '0.3',
                        ),
                        'max_history' => array(
                            'name'    => __( 'Max History Messages', 'automatorwp-cohere' ),
                            'desc'    => __( 'Previous messages to include as context (default: 10). Higher = more context, more tokens.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => 10,
                        ),
                        'usage_limit' => array(
                            'name'    => __( 'Usage Limit', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) Max times this action runs per user per period. 0 = unlimited.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => '0',
                        ),
                        'usage_period' => array(
                            'name'    => __( 'Limit Period', 'automatorwp-cohere' ),
                            'desc'    => __( 'Period over which the limit is counted.', 'automatorwp-cohere' ),
                            'type'    => 'select',
                            'options' => array(
                                'day'   => __( 'Per day', 'automatorwp-cohere' ),
                                'week'  => __( 'Per week', 'automatorwp-cohere' ),
                                'month' => __( 'Per month', 'automatorwp-cohere' ),
                            ),
                            'default' => 'day',
                        ),
                        'response_tag' => array(
                            'name'    => __( 'Store Response As Tag', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) Custom tag name to reuse the response in subsequent actions.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                    ),
                ),
            ),
            'tags' => array(
                'cohere_conv_response' => array(
                    'label'   => __( 'Cohere Conversation Response', 'automatorwp-cohere' ),
                    'type'    => 'text',
                    'preview' => __( 'AI response to the last message', 'automatorwp-cohere' ),
                ),
                'cohere_conv_turns' => array(
                    'label'   => __( 'Cohere Conversation Turns', 'automatorwp-cohere' ),
                    'type'    => 'text',
                    'preview' => __( 'Total turns in this conversation', 'automatorwp-cohere' ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation )
    {
        $model           = isset( $action_options['model'] )           ? $action_options['model']                          : 'command-a-03-2025';
        $conversation_id = isset( $action_options['conversation_id'] ) ? $action_options['conversation_id']                : '';
        $system          = isset( $action_options['system_message'] )  ? $action_options['system_message']                 : '';
        $message         = isset( $action_options['message'] )         ? $action_options['message']                        : '';
        $max_tokens      = isset( $action_options['max_tokens'] )      ? (int) $action_options['max_tokens']               : 1024;
        $temperature     = isset( $action_options['temperature'] )     ? (float) $action_options['temperature']            : 0.3;
        $max_history     = isset( $action_options['max_history'] )     ? (int) $action_options['max_history']              : 10;
        $response_tag    = isset( $action_options['response_tag'] )    ? sanitize_key( $action_options['response_tag'] )   : '';

        if ( empty( $conversation_id ) ) {
            $this->result = __( 'Conversation ID field is empty.', 'automatorwp-cohere' );
            return;
        }

        if ( empty( $message ) ) {
            $this->result = __( 'Message field is empty.', 'automatorwp-cohere' );
            return;
        }

        if ( empty( automatorwp_cohere_get_api_key() ) ) {
            $this->result = __( 'Cohere integration not configured in AutomatorWP settings.', 'automatorwp-cohere' );
            return;
        }

        if ( ! automatorwp_cohere_check_and_increment_usage( $action, $user_id, $action_options ) ) {
            $this->result = __( 'Usage limit reached. Action skipped.', 'automatorwp-cohere' );
            return;
        }

        $history  = automatorwp_cohere_get_conversation_history( $conversation_id );
        $messages = array();

        // System message is injected only on the very first turn to avoid
        // duplicating it on subsequent calls for the same conversation.
        if ( ! empty( $system ) && empty( $history ) ) {
            $messages[] = array( 'role' => 'system', 'content' => $system );
        }

        // Keep only the most recent N turns to avoid exceeding the context window.
        if ( count( $history ) > $max_history ) {
            $history = array_slice( $history, -$max_history );
        }

        foreach ( $history as $turn ) {
            $messages[] = $turn;
        }

        $messages[] = array( 'role' => 'user', 'content' => $message );

        $response = automatorwp_cohere_api_request( '/v2/chat', array(
            'model'       => $model,
            'messages'    => $messages,
            'max_tokens'  => $max_tokens,
            'temperature' => $temperature,
        ) );

        if ( is_wp_error( $response ) ) {
            $this->result = $response->get_error_message();
            return;
        }

        $text = automatorwp_cohere_extract_chat_text( $response );

        $history[] = array( 'role' => 'user',      'content' => $message );
        $history[] = array( 'role' => 'assistant',  'content' => $text );
        automatorwp_cohere_save_conversation_history( $conversation_id, $history );

        $turns = (int) ( count( $history ) / 2 );

        automatorwp_update_action_tag( $action->id, 'cohere_conv_response', $text );
        automatorwp_update_action_tag( $action->id, 'cohere_conv_turns',    $turns );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->id, $response_tag, $text );
        }

        $this->result = sprintf( __( 'Message sent. Conversation now has %d turn(s).', 'automatorwp-cohere' ), $turns );
    }

    public function hooks()
    {
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ),             10, 5 );
        add_filter( 'automatorwp_log_fields',                     array( $this, 'log_fields' ),           10, 3 );
        parent::hooks();
    }

    public function configuration_notice( $object, $item_type )
    {
        if ( $item_type !== 'action' ) return;
        if ( $object->type !== $this->action ) return;
        if ( empty( automatorwp_cohere_get_api_key() ) ): ?>
            <div class="automatorwp-notice-warning" style="margin-top:10px;margin-bottom:0;">
                <?php echo sprintf( __( 'You need to configure the <a href="%s" target="_blank">Cohere settings</a> to get this action to work.', 'automatorwp-cohere' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-cohere' ); ?>
            </div>
        <?php endif;
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
        $log_fields['cohere_conv_response'] = array( 'name' => __( 'Response:', 'automatorwp-cohere' ), 'type' => 'text' );
        return $log_fields;
    }
}

new AutomatorWP_Cohere_Conversation();
