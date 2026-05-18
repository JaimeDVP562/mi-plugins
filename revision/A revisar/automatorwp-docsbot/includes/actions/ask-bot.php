<?php
/**
 * Ask Bot
 *
 * @package     AutomatorWP\Integrations\DocsBot\Actions\Ask_Bot
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_DocsBot_Ask_Bot extends AutomatorWP_Integration_Action {

    public $integration = 'docsbot';
    public $action      = 'docsbot_ask_bot';
    public $answer      = '';
    public $sources     = array();

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Send a question to the bot', 'automatorwp-docsbot' ),
            'select_option' => __( 'Send a <strong>question</strong> to the DocsBot bot', 'automatorwp-docsbot' ),
            /* translators: %1$s: Question. */
            'edit_label'    => sprintf( __( 'Send %1$s to the DocsBot bot', 'automatorwp-docsbot' ), '{question}' ),
            /* translators: %1$s: Question. */
            'log_label'     => sprintf( __( 'Send %1$s to the DocsBot bot', 'automatorwp-docsbot' ), '{question}' ),
            'options'       => array(
                'question' => array(
                    'default' => __( 'question', 'automatorwp-docsbot' ),
                    'fields'  => array(
                        'question' => array(
                            'name'     => __( 'Question:', 'automatorwp-docsbot' ),
                            'desc'     => __( 'The question to send to the DocsBot bot. You can use tags from previous triggers.', 'automatorwp-docsbot' ),
                            'type'     => 'textarea',
                            'default'  => '',
                            'required' => true,
                        ),
                        'conversation_id' => array(
                            'name'    => __( 'Conversation ID (optional):', 'automatorwp-docsbot' ),
                            'desc'    => __( 'Provide a UUID to maintain a stateful conversation. Leave empty to start a new conversation each time.', 'automatorwp-docsbot' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                    ),
                ),
            ),
        ) );

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

        $question        = isset( $action_options['question'] )        ? sanitize_textarea_field( $action_options['question'] )   : '';
        $conversation_id = isset( $action_options['conversation_id'] ) ? sanitize_text_field( $action_options['conversation_id'] ) : '';

        if( empty( $question ) ) {
            $this->result = __( 'No question provided.', 'automatorwp-docsbot' );
            return;
        }

        if( ! automatorwp_docsbot_get_api() ) {
            $this->result = __( 'DocsBot AI is not configured in AutomatorWP settings.', 'automatorwp-docsbot' );
            return;
        }

        // conversationId is required by the API — use provided value or generate a unique one
        if( empty( $conversation_id ) ) {
            $conversation_id = wp_generate_uuid4();
        }

        $body = array(
            'conversationId' => $conversation_id,
            'question'       => $question,
            'stream'         => false,
        );

        $response = automatorwp_docsbot_api_request( 'chat-agent', $body );

        if( is_wp_error( $response ) ) {
            $this->result = sprintf(
                /* translators: %s: error message */
                __( 'DocsBot API error: %s', 'automatorwp-docsbot' ),
                $response->get_error_message()
            );
            return;
        }

        // DocsBot Chat Agent returns an array of SSE-style event objects, each with
        // an 'event' key (e.g. 'lookup_start', 'lookup_answer', 'done') and a 'data' key.
        // We look for 'lookup_answer' (or 'answer' as fallback) which contains the final
        // AI-generated answer and the source documents used to produce it.
        $answer  = '';
        $sources = array();

        if( is_array( $response ) ) {
            foreach( $response as $event ) {
                $type = isset( $event['event'] ) ? $event['event'] : '';
                $data = isset( $event['data'] )  ? $event['data']  : array();

                if( in_array( $type, array( 'lookup_answer', 'answer' ), true ) ) {
                    $answer  = isset( $data['answer'] )  ? $data['answer']  : '';
                    $sources = isset( $data['sources'] ) ? $data['sources'] : array();
                    break;
                }
            }
        }

        $this->result  = $answer;
        $this->answer  = $answer;
        $this->sources = $sources;

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Configuration notice
        add_action( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );

        parent::hooks();

    }

    /**
     * Configuration notice when DocsBot is not set up
     *
     * @since 1.0.0
     *
     * @param stdClass  $object
     * @param string    $item_type
     */
    public function configuration_notice( $object, $item_type ) {

        if( $item_type !== 'action' ) {
            return;
        }

        if( $object->type !== $this->action ) {
            return;
        }

        if( ! automatorwp_docsbot_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    /* translators: %s: settings URL */
                    esc_html__( 'You need to configure the %s to use this action.', 'automatorwp-docsbot' ),
                    '<a href="' . esc_url( admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-docsbot' ) ) . '" target="_blank">' . esc_html__( 'DocsBot AI settings', 'automatorwp-docsbot' ) . '</a>'
                ); ?>
            </div>
        <?php endif;

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta
     * @param stdClass  $action
     * @param int       $user_id
     * @param array     $action_options
     * @param stdClass  $automation
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['docsbot_answer']  = isset( $this->answer )  ? $this->answer  : $this->result;
        $log_meta['docsbot_sources'] = isset( $this->sources ) ? wp_json_encode( $this->sources ) : '';

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields
     * @param stdClass  $log
     * @param stdClass  $object
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['docsbot_answer'] = array(
            'name' => __( 'Bot Answer:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        $log_fields['docsbot_sources'] = array(
            'name' => __( 'Sources:', 'automatorwp-docsbot' ),
            'desc' => __( 'JSON-encoded list of source documents used to generate the answer.', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_DocsBot_Ask_Bot();
