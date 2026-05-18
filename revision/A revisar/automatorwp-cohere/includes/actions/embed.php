<?php
/**
 * Generate Embeddings
 *
 * @package     AutomatorWP\Integrations\Cohere\Actions\Embed
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Cohere_Embed extends AutomatorWP_Integration_Action 
{
    public $integration = 'cohere';
    public $action      = 'cohere_embed';

    public function register()
    {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Generate text embeddings with Cohere', 'automatorwp-cohere' ),
            'select_option' => __( 'Generate <strong>embeddings</strong> for text with Cohere', 'automatorwp-cohere' ),
            'edit_label'    => __( 'Generate embeddings for {text} with Cohere', 'automatorwp-cohere' ),
            'log_label'     => __( 'Generate text embeddings with Cohere', 'automatorwp-cohere' ),
            'options'       => array(
                'text' => array(
                    'from'    => 'action',
                    'default' => __( 'text', 'automatorwp-cohere' ),
                    'fields'  => array(
                        'model' => array(
                            'name'    => __( 'Model', 'automatorwp-cohere' ),
                            'desc'    => __( 'Cohere Embed model to use.', 'automatorwp-cohere' ),
                            'type'    => 'select',
                            'options' => automatorwp_cohere_get_embed_models(),
                            'default' => 'embed-v4.0',
                        ),
                        'text' => array(
                            'name'     => __( 'Text', 'automatorwp-cohere' ),
                            'desc'     => __( 'Text to embed. Supports tags.', 'automatorwp-cohere' ),
                            'type'     => 'textarea',
                            'default'  => '',
                            'required' => true,
                        ),
                        'input_type' => array(
                            'name'    => __( 'Input Type', 'automatorwp-cohere' ),
                            'desc'    => __( 'How the text will be used. Affects embedding optimization.', 'automatorwp-cohere' ),
                            'type'    => 'select',
                            'options' => automatorwp_cohere_get_embed_input_types(),
                            'default' => 'search_document',
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
                            'name'    => __( 'Store Embeddings As Tag', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) Custom tag name to store the embeddings JSON array.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                    ),
                ),
            ),
            'tags' => array(
                'cohere_embeddings' => array(
                    'label'   => __( 'Cohere Embeddings', 'automatorwp-cohere' ),
                    'type'    => 'text',
                    'preview' => __( 'JSON array of float values representing the text embedding', 'automatorwp-cohere' ),
                ),
                'cohere_embedding_dimensions' => array(
                    'label'   => __( 'Cohere Embedding Dimensions', 'automatorwp-cohere' ),
                    'type'    => 'text',
                    'preview' => __( 'Number of dimensions in the embedding vector', 'automatorwp-cohere' ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation )
    {
        $model        = isset( $action_options['model'] )        ? $action_options['model']                          : 'embed-v4.0';
        $text         = isset( $action_options['text'] )         ? $action_options['text']                          : '';
        $input_type   = isset( $action_options['input_type'] )   ? $action_options['input_type']                    : 'search_document';
        $response_tag = isset( $action_options['response_tag'] ) ? sanitize_key( $action_options['response_tag'] )  : '';

        if ( empty( $text ) ) {
            $this->result = __( 'Text field is empty.', 'automatorwp-cohere' );
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

        // embedding_types is fixed to 'float' — the standard 32-bit format
        // compatible with all vector databases and similarity search libraries.
        $response = automatorwp_cohere_api_request( '/v2/embed', array(
            'model'           => $model,
            'texts'           => array( $text ),
            'input_type'      => $input_type,
            'embedding_types' => array( 'float' ),
        ) );

        if ( is_wp_error( $response ) ) {
            $this->result = $response->get_error_message();
            return;
        }

        $vectors    = isset( $response['embeddings']['float'][0] ) ? $response['embeddings']['float'][0] : array();
        $embeddings = wp_json_encode( $vectors );
        $dimensions = count( $vectors );

        automatorwp_update_action_tag( $action->id, 'cohere_embeddings',            $embeddings );
        automatorwp_update_action_tag( $action->id, 'cohere_embedding_dimensions',  $dimensions );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->id, $response_tag, $embeddings );
        }

        $this->result = sprintf( __( 'Embeddings generated with Cohere. Vector dimensions: %d.', 'automatorwp-cohere' ), $dimensions );
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
        $log_fields['cohere_embedding_dimensions'] = array( 'name' => __( 'Dimensions:', 'automatorwp-cohere' ), 'type' => 'text' );
        return $log_fields;
    }
}

new AutomatorWP_Cohere_Embed();
