<?php
/**
 * Action: Generate image
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_Mistral_Generate_Image extends AutomatorWP_Integration_Action {

    public $integration = 'mistral_ai';
    public $action      = 'mistral_generate_image';

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
            'label'         => __( 'Generate an image', 'automatorwp-mistral-ai' ),
            'select_option' => __( 'Generate an <strong>image</strong>', 'automatorwp-mistral-ai' ),
            'edit_label'    => sprintf( __( 'Generate an image with %1$s', 'automatorwp-mistral-ai' ), '{prompt}' ),
            'log_label'     => sprintf( __( 'Generate an image with %1$s', 'automatorwp-mistral-ai' ), '{prompt}' ),
            'options'       => array(
                'prompt' => array(
                    'default' => __( 'prompt', 'automatorwp-mistral-ai' ),
                    'fields'  => array(
                        'prompt' => array(
                            'name'     => __( 'Prompt:', 'automatorwp-mistral-ai' ),
                            'desc'     => __( 'The prompt to generate the image.', 'automatorwp-mistral-ai' ),
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

        $url = automatorwp_mistral_generate_image_and_upload( $action_options['prompt'] );

        if ( ! $url ) {
            $this->result   = __( 'Image generation is not configured. Please provide an implementation using the filter "automatorwp_mistral_ai_generate_image_url".', 'automatorwp-mistral-ai' );
            $this->response = '';
            return;
        }

        $this->result   = $url;
        $this->response = $url;
    }

    /**
     * Add response meta to action log.
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if ( empty( $action_options['action'] ) || $action_options['action'] !== $this->action ) {
            return $log_meta;
        }

        if ( ! empty( $this->response ) ) {
            $log_meta['response'] = $this->response;
        }

        return $log_meta;
    }
}

new AutomatorWP_Mistral_Generate_Image();
