<?php
/**
 * Action: Generate text
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_Mistral_Generate_Text extends AutomatorWP_Integration_Action {

    public $integration = 'mistral_ai';
    public $action      = 'mistral_generate_text';

    /**
     * Holds last response for logging.
     *
     * @var string
     */
    public $response = '';

    public function __construct() {

        if ( method_exists( get_parent_class( $this ), '__construct' ) ) {
            parent::__construct();
        }

        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
    }

    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Generate a text', 'automatorwp-mistral-ai' ),
            'select_option' => __( 'Generate a <strong>text</strong>', 'automatorwp-mistral-ai' ),
            'edit_label'    => sprintf( __( 'Generate text with %1$s', 'automatorwp-mistral-ai' ), '{prompt}' ),
            'log_label'     => sprintf( __( 'Generate text with %1$s', 'automatorwp-mistral-ai' ), '{prompt}' ),
            'options'       => array(
                'prompt' => array(
                    'default' => __( 'prompt', 'automatorwp-mistral-ai' ),
                    'fields'  => array(
                        'prompt' => array(
                            'name'     => __( 'Prompt:', 'automatorwp-mistral-ai' ),
                            'desc'     => __( 'The prompt to generate the text.', 'automatorwp-mistral-ai' ),
                            'type'     => 'textarea',
                            'required' => true,
                            'default'  => '',
                        ),
                    ),
                ),
            ),
            'tags' => automatorwp_mistral_get_actions_response_tags(),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation ) {

        if ( empty( $action_options['prompt'] ) ) {
            $this->result   = __( 'Prompt field is empty.', 'automatorwp-mistral-ai' );
            $this->response = '';
            return;
        }

        $text = automatorwp_mistral_chat_completion( $action_options['prompt'] );

        if ( ! $text ) {
            $this->result   = __( 'Error: Please check your Mistral configuration (token/model).', 'automatorwp-mistral-ai' );
            $this->response = '';
            return;
        }

        // Store result and response for tags/log meta
        $this->result   = $text;
        $this->response = $text;
    }

    /**
     * Add response meta to action log.
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        // Ensure this runs only for this action
        if ( empty( $action_options['action'] ) || $action_options['action'] !== $this->action ) {
            return $log_meta;
        }

        if ( ! empty( $this->response ) ) {
            $log_meta['response'] = $this->response;
        }

        return $log_meta;
    }
}

new AutomatorWP_Mistral_Generate_Text();
