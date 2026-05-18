<?php
/**
 * Classify Text
 *
 * @package     AutomatorWP\Integrations\Cohere\Actions\Classify
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Cohere_Classify extends AutomatorWP_Integration_Action 
{
    public $integration = 'cohere';
    public $action      = 'cohere_classify';

    public function register()
    {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Classify text into categories with Cohere', 'automatorwp-cohere' ),
            'select_option' => __( 'Classify <strong>text</strong> into categories with Cohere', 'automatorwp-cohere' ),
            'edit_label'    => __( 'Classify {text} into categories with Cohere', 'automatorwp-cohere' ),
            'log_label'     => __( 'Classify {text}', 'automatorwp-cohere' ),
            'options'       => array(
                'text' => array(
                    'from'    => 'action',
                    'default' => __( 'text', 'automatorwp-cohere' ),
                    'fields'  => array(
                        'model' => array(
                            'name'    => __( 'Model', 'automatorwp-cohere' ),
                            'desc'    => __( 'Cohere Command model to use.', 'automatorwp-cohere' ),
                            'type'    => 'select',
                            'options' => automatorwp_cohere_get_chat_models(),
                            'default' => 'command-a-03-2025',
                        ),
                        'text' => array(
                            'name'     => __( 'Text', 'automatorwp-cohere' ),
                            'desc'     => __( 'Text to classify. Supports tags.', 'automatorwp-cohere' ),
                            'type'     => 'textarea',
                            'default'  => '',
                            'required' => true,
                        ),
                        'categories' => array(
                            'name'     => __( 'Categories', 'automatorwp-cohere' ),
                            'desc'     => __( 'Comma-separated list of categories (e.g. positive, negative, neutral). Supports tags.', 'automatorwp-cohere' ),
                            'type'     => 'text',
                            'default'  => '',
                            'required' => true,
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
                            'name'    => __( 'Store Category As Tag', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) Custom tag name to reuse the category in subsequent actions.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                    ),
                ),
            ),
            'tags' => array(
                'cohere_category' => array(
                    'label'   => __( 'Cohere Category', 'automatorwp-cohere' ),
                    'type'    => 'text',
                    'preview' => __( 'Best matching category', 'automatorwp-cohere' ),
                ),
                'cohere_category_reason' => array(
                    'label'   => __( 'Cohere Category Reason', 'automatorwp-cohere' ),
                    'type'    => 'text',
                    'preview' => __( 'Brief explanation for the classification', 'automatorwp-cohere' ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation )
    {
        $model        = isset( $action_options['model'] )        ? $action_options['model']                          : 'command-a-03-2025';
        $text         = isset( $action_options['text'] )         ? $action_options['text']                          : '';
        $categories   = isset( $action_options['categories'] )   ? $action_options['categories']                    : '';
        $response_tag = isset( $action_options['response_tag'] ) ? sanitize_key( $action_options['response_tag'] )  : '';

        if ( empty( $text ) ) {
            $this->result = __( 'Text field is empty.', 'automatorwp-cohere' );
            return;
        }

        if ( empty( $categories ) ) {
            $this->result = __( 'Categories field is empty.', 'automatorwp-cohere' );
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

        $cats_list = implode( ', ', array_map( 'trim', explode( ',', $categories ) ) );

        $prompt = "Classify the following text into exactly one of these categories: {$cats_list}.\n\n"
                . "Text: \"{$text}\"\n\n"
                . "Reply with a JSON object with two keys: \"category\" (the best matching category from the list) and \"reason\" (one sentence explaining why). "
                . "Do not include any other text outside the JSON.";

        $response = automatorwp_cohere_api_request( '/v2/chat', array(
            'model'    => $model,
            'messages' => array( array( 'role' => 'user', 'content' => $prompt ) ),
            'max_tokens' => 64,
            'temperature' => 0,
        ) );

        if ( is_wp_error( $response ) ) {
            $this->result = $response->get_error_message();
            return;
        }

        $raw = automatorwp_cohere_extract_chat_text( $response );
        $raw_trim = trim( (string) $raw );

        $parsed = null;
        if ( $raw_trim !== '' ) {
            $parsed = json_decode( $raw_trim, true );
        }

        // Graceful degradation: if the model returns plain text instead of JSON
        // (can happen with smaller models), store the raw text as the category
        // and flag the reason so the admin can spot misconfigured automations.
        if ( ! is_array( $parsed ) || json_last_error() !== JSON_ERROR_NONE ) {
            $category = $raw_trim !== '' ? $raw_trim : __( 'Unknown', 'automatorwp-cohere' );
            $reason   = __( 'Invalid or non-JSON response from Cohere model.', 'automatorwp-cohere' );

            automatorwp_update_action_tag( $action->id, 'cohere_category',        sanitize_text_field( $category ) );
            automatorwp_update_action_tag( $action->id, 'cohere_category_reason', sanitize_text_field( $reason ) );

            if ( ! empty( $response_tag ) ) {
                automatorwp_update_action_tag( $action->id, $response_tag, sanitize_text_field( $category ) );
            }

            $this->result = sprintf( __( 'Cohere returned unexpected response: %s', 'automatorwp-cohere' ), wp_trim_words( $raw_trim, 20, '...' ) );
            return;
        }

        $category = isset( $parsed['category'] ) ? sanitize_text_field( $parsed['category'] ) : '';
        $reason   = isset( $parsed['reason'] )   ? sanitize_text_field( $parsed['reason'] )   : '';

        if ( $category === '' ) {
            $category = __( 'Unknown', 'automatorwp-cohere' );
        }

        automatorwp_update_action_tag( $action->id, 'cohere_category',        $category );
        automatorwp_update_action_tag( $action->id, 'cohere_category_reason', $reason );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->id, $response_tag, $category );
        }

        $this->result = sprintf( __( 'Text classified as "%s".', 'automatorwp-cohere' ), $category );
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
        $log_fields['cohere_category'] = array( 'name' => __( 'Category:', 'automatorwp-cohere' ), 'type' => 'text' );
        $log_fields['cohere_category_reason'] = array( 'name' => __( 'Reason:', 'automatorwp-cohere' ), 'type' => 'text' );
        return $log_fields;
    }
}

new AutomatorWP_Cohere_Classify();
