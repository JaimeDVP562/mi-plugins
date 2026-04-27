<?php
/**
 * Send Prompt
 *
 * @package     AutomatorWP\Integrations\Cohere\Actions\Send_Prompt
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Cohere_Send_Prompt extends AutomatorWP_Integration_Action 
{
    public $integration = 'cohere';
    public $action      = 'cohere_send_prompt';

    public function register()
    {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Send a prompt to Cohere', 'automatorwp-cohere' ),
            'select_option' => __( 'Send a <strong>prompt</strong> to Cohere', 'automatorwp-cohere' ),
            'edit_label'    => __( 'Send {prompt} to Cohere and store the response', 'automatorwp-cohere' ),
            'log_label'     => __( 'Send a prompt to Cohere', 'automatorwp-cohere' ),
            'options'       => array(
                'prompt' => array(
                    'from'    => 'action',
                    'default' => __( 'prompt', 'automatorwp-cohere' ),
                    'fields'  => array(
                        'model' => array(
                            'name'    => __( 'Model', 'automatorwp-cohere' ),
                            'desc'    => __( 'Cohere Command model to use.', 'automatorwp-cohere' ),
                            'type'    => 'select',
                            'options' => automatorwp_cohere_get_chat_models(),
                            'default' => 'command-a-03-2025',
                        ),
                        'system_message' => array(
                            'name'    => __( 'System Message', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) AI behavior or persona. Supports tags.', 'automatorwp-cohere' ),
                            'type'    => 'textarea',
                            'default' => '',
                        ),
                        'prompt' => array(
                            'name'     => __( 'Prompt', 'automatorwp-cohere' ),
                            'desc'     => __( 'Question or instruction to send. Supports tags.', 'automatorwp-cohere' ),
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
                'cohere_response' => array(
                    'label'   => __( 'Cohere Response', 'automatorwp-cohere' ),
                    'type'    => 'text',
                    'preview' => __( 'AI-generated response', 'automatorwp-cohere' ),
                ),
                'cohere_citations' => array(
                    'label'   => __( 'Cohere Citations', 'automatorwp-cohere' ),
                    'type'    => 'text',
                    'preview' => __( 'Cited text fragments from documents', 'automatorwp-cohere' ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation )
    {
        $model        = isset( $action_options['model'] )        ? $action_options['model']                          : 'command-a-03-2025';
        $system       = isset( $action_options['system_message'] ) ? $action_options['system_message']              : '';
        $prompt       = isset( $action_options['prompt'] )       ? $action_options['prompt']                        : '';
        $max_tokens   = isset( $action_options['max_tokens'] )   ? (int) $action_options['max_tokens']              : 1024;
        $temperature  = isset( $action_options['temperature'] )  ? (float) $action_options['temperature']           : 0.3;
        $response_tag = isset( $action_options['response_tag'] ) ? sanitize_key( $action_options['response_tag'] )  : '';

        if ( empty( $prompt ) ) {
            $this->result = __( 'Prompt field is empty.', 'automatorwp-cohere' );
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

        $messages = array();
        if ( ! empty( $system ) ) {
            $messages[] = array( 'role' => 'system', 'content' => $system );
        }
        $messages[] = array( 'role' => 'user', 'content' => $prompt );

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

        $text      = automatorwp_cohere_extract_chat_text( $response );
        $citations = automatorwp_cohere_extract_chat_citations( $response );

        automatorwp_update_action_tag( $action->id, 'cohere_response',  $text );
        automatorwp_update_action_tag( $action->id, 'cohere_citations', $citations );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->id, $response_tag, $text );
        }

        $this->result = sprintf( __( 'Prompt sent to Cohere successfully. Response length: %d characters.', 'automatorwp-cohere' ), strlen( $text ) );
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
        $log_fields['result']          = array( 'name' => __( 'Result:', 'automatorwp-cohere' ), 'type' => 'text' );
        $log_fields['cohere_response'] = array( 'name' => __( 'Response:', 'automatorwp-cohere' ), 'type' => 'text' );
        return $log_fields;
    }
}

new AutomatorWP_Cohere_Send_Prompt();
