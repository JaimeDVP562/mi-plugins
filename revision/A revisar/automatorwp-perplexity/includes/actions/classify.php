<?php
/**
 * Classify Text
 *
 * @package     AutomatorWP\Integrations\Perplexity\Actions\Classify
 * @since       1.1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Perplexity_Classify extends AutomatorWP_Integration_Action
{

    public $integration = 'perplexity';
    public $action      = 'perplexity_classify';

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
                'label'         => __( 'Classify text into categories with Perplexity', 'automatorwp-perplexity' ),
                'select_option' => __( 'Classify <strong>text</strong> into categories with Perplexity', 'automatorwp-perplexity' ),
                'edit_label'    => __( 'Classify {text} into categories with Perplexity', 'automatorwp-perplexity' ),
                'log_label'     => __( 'Classify text with Perplexity', 'automatorwp-perplexity' ),
                'options'       => array(
                    'text' => array(
                        'from'    => 'text',
                        'default' => __( 'text', 'automatorwp-perplexity' ),
                        'fields'  => array(
                            'text' => array(
                                'name'     => __( 'Text to Classify', 'automatorwp-perplexity' ),
                                'desc'     => __( 'Text to classify. Supports tags.', 'automatorwp-perplexity' ),
                                'type'     => 'textarea',
                                'default'  => '',
                                'required' => true,
                            ),
                            'categories' => array(
                                'name'     => __( 'Categories', 'automatorwp-perplexity' ),
                                'desc'     => __( 'Comma-separated list of possible categories (e.g. Billing, Technical Support, Sales, Other). Supports tags.', 'automatorwp-perplexity' ),
                                'type'     => 'text',
                                'default'  => '',
                                'required' => true,
                            ),
                            'multi_label' => array(
                                'name'    => __( 'Allow Multiple Categories', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Whether the text can belong to more than one category.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => array(
                                    'no'  => __( 'No (single best match)', 'automatorwp-perplexity' ),
                                    'yes' => __( 'Yes (all matching categories)', 'automatorwp-perplexity' ),
                                ),
                                'default' => 'no',
                            ),
                            'context' => array(
                                'name'    => __( 'Classification Context', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Additional context to guide classification (e.g. "These are customer support tickets"). Supports tags.', 'automatorwp-perplexity' ),
                                'type'    => 'textarea',
                                'default' => '',
                            ),
                            'model' => array(
                                'name'    => __( 'Model', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Perplexity model to use.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => automatorwp_perplexity_get_models(),
                                'default' => 'sonar',
                            ),
                            'usage_limit' => array(
                                'name'    => __( 'Usage Limit', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Max times this action runs per user per period. 0 = unlimited.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => '0',
                            ),
                            'usage_period' => array(
                                'name'    => __( 'Limit Period', 'automatorwp-perplexity' ),
                                'desc'    => __( 'Period over which the limit is counted.', 'automatorwp-perplexity' ),
                                'type'    => 'select',
                                'options' => array(
                                    'day'   => __( 'Per day', 'automatorwp-perplexity' ),
                                    'week'  => __( 'Per week', 'automatorwp-perplexity' ),
                                    'month' => __( 'Per month', 'automatorwp-perplexity' ),
                                ),
                                'default' => 'day',
                            ),
                            'response_tag' => array(
                                'name'    => __( 'Store Response As Tag', 'automatorwp-perplexity' ),
                                'desc'    => __( '(Optional) Custom tag name to reuse the response in subsequent actions.', 'automatorwp-perplexity' ),
                                'type'    => 'text',
                                'default' => '',
                            ),
                        ),
                    ),
                ),
                'tags'          => array(
                    'perplexity_category'            => array(
                        'label'   => __( 'Perplexity Category', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'Best matching category', 'automatorwp-perplexity' ),
                    ),
                    'perplexity_categories_all'      => array(
                        'label'   => __( 'Perplexity All Matched Categories', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'All matching categories (comma-separated)', 'automatorwp-perplexity' ),
                    ),
                    'perplexity_category_confidence' => array(
                        'label'   => __( 'Perplexity Category Confidence', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'Confidence score 0–1 for the primary category', 'automatorwp-perplexity' ),
                    ),
                    'perplexity_category_reason'     => array(
                        'label'   => __( 'Perplexity Category Reason', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'Brief explanation for the classification', 'automatorwp-perplexity' ),
                    ),
                ),
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
        $text         = isset( $action_options['text'] )         ? $action_options['text']                                    : '';
        $categories   = isset( $action_options['categories'] )   ? $action_options['categories']                              : '';
        $context      = isset( $action_options['context'] )      ? $action_options['context']                                 : '';
        $multi_label  = isset( $action_options['multi_label'] )  ? $action_options['multi_label']                             : 'no';
        $model        = isset( $action_options['model'] )        ? $action_options['model']                                   : 'sonar';
        $response_tag = isset( $action_options['response_tag'] ) ? sanitize_key( $action_options['response_tag'] )            : '';

        if ( empty( $text ) ) {
            $this->result = __( 'Text field is empty.', 'automatorwp-perplexity' );
            return;
        }

        if ( empty( $categories ) ) {
            $this->result = __( 'Categories field is empty.', 'automatorwp-perplexity' );
            return;
        }

        if ( empty( automatorwp_perplexity_get_api_key() ) ) {
            $this->result = __( 'Perplexity integration not configured in AutomatorWP settings.', 'automatorwp-perplexity' );
            return;
        }

        if ( ! automatorwp_perplexity_check_and_increment_usage( $action, $user_id, $action_options ) ) {
            $this->result = __( 'Usage limit reached. Action skipped.', 'automatorwp-perplexity' );
            return;
        }

        $cats_list  = implode( ', ', array_map( 'trim', explode( ',', $categories ) ) );
        $multi_text = ( $multi_label === 'yes' )
            ? 'The text may belong to multiple categories. Return all that apply in the "categories" array.'
            : 'Assign the text to exactly one best-matching category.';
        $ctx_text   = ! empty( $context ) ? "\nContext: " . $context : '';

        $system = 'You are a text classification engine. Respond ONLY with a valid JSON object. No markdown fences, no explanation outside the JSON.';
        $prompt = sprintf(
            'Classify the following text into one of these categories: %s.\n%s%s\n\nReturn a JSON with: "category" (best match), "categories" (array of all matches), "confidence" (0-1 float), "reason" (one sentence).\n\nText:\n%s',
            $cats_list,
            $multi_text,
            $ctx_text,
            $text
        );

        $response = automatorwp_perplexity_api_request( $model, array(
            array( 'role' => 'system', 'content' => $system ),
            array( 'role' => 'user',   'content' => $prompt ),
        ), array(
            'max_tokens'  => 512,
            'temperature' => 0,
        ) );

        if ( is_wp_error( $response ) ) {
            $this->result = $response->get_error_message();
            return;
        }

        $e    = automatorwp_perplexity_extract_response( $response );
        $raw  = trim( preg_replace( '/^```(?:json)?\s*|\s*```$/', '', $e['text'] ) );
        $data = json_decode( $raw, true );

        $category   = isset( $data['category'] )    ? sanitize_text_field( $data['category'] )                                                      : $e['text'];
        $cats_all   = isset( $data['categories'] )  ? implode( ', ', array_map( 'sanitize_text_field', (array) $data['categories'] ) )               : $category;
        $confidence = isset( $data['confidence'] )  ? (string) $data['confidence']                                                                   : '';
        $reason     = isset( $data['reason'] )      ? sanitize_textarea_field( $data['reason'] )                                                     : '';

        automatorwp_update_action_tag( $action->ID, 'perplexity_category',            $category );
        automatorwp_update_action_tag( $action->ID, 'perplexity_categories_all',      $cats_all );
        automatorwp_update_action_tag( $action->ID, 'perplexity_category_confidence', $confidence );
        automatorwp_update_action_tag( $action->ID, 'perplexity_category_reason',     $reason );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->ID, $response_tag, $category );
        }

        $this->result = sprintf( __( 'Text classified as: %s (confidence: %s).', 'automatorwp-perplexity' ), $category, $confidence );
    }

    /**
     * Register required hooks
     *
     * @since 1.1.0
     */
    public function hooks()
    {
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ),             10, 5 );
        add_filter( 'automatorwp_log_fields',                     array( $this, 'log_fields' ),           10, 5 );

        parent::hooks();
    }

    /** @since 1.1.0 */
    public function configuration_notice( $object, $item_type )
    {
        if ( $item_type !== 'action' ) return;
        if ( $object->type !== $this->action ) return;

        if ( empty( automatorwp_perplexity_get_api_key() ) ): ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Perplexity settings</a> to get this action to work.', 'automatorwp-perplexity' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-perplexity'
                ); ?>
            </div>
        <?php endif;
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

new AutomatorWP_Perplexity_Classify();
