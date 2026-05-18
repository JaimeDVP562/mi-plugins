<?php
/**
 * Extract Structured Data
 *
 * @package     AutomatorWP\Integrations\Perplexity\Actions\Extract_Data
 * @since       1.1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Perplexity_Extract_Data extends AutomatorWP_Integration_Action
{

    public $integration = 'perplexity';
    public $action      = 'perplexity_extract_data';

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
                'label'         => __( 'Extract structured data from a text with Perplexity', 'automatorwp-perplexity' ),
                'select_option' => __( 'Extract <strong>structured data</strong> from a text with Perplexity', 'automatorwp-perplexity' ),
                'edit_label'    => __( 'Extract structured data from {text} with Perplexity', 'automatorwp-perplexity' ),
                'log_label'     => __( 'Extract structured data with Perplexity', 'automatorwp-perplexity' ),
                'options'       => array(
                    'text' => array(
                        'from'    => 'text',
                        'default' => __( 'text', 'automatorwp-perplexity' ),
                        'fields'  => array(
                            'text' => array(
                                'name'     => __( 'Source Text', 'automatorwp-perplexity' ),
                                'desc'     => __( 'Text from which to extract data. Supports tags.', 'automatorwp-perplexity' ),
                                'type'     => 'textarea',
                                'default'  => '',
                                'required' => true,
                            ),
                            'fields_schema' => array(
                                'name'     => __( 'Fields to Extract', 'automatorwp-perplexity' ),
                                'desc'     => __( 'Comma-separated list of fields to extract (e.g. name, email, phone, company). Supports tags.', 'automatorwp-perplexity' ),
                                'type'     => 'text',
                                'default'  => '',
                                'required' => true,
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
                    'perplexity_extracted_json'  => array(
                        'label'   => __( 'Perplexity Extracted Data (JSON)', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'Extracted fields as a JSON object', 'automatorwp-perplexity' ),
                    ),
                    'perplexity_extracted_found' => array(
                        'label'   => __( 'Perplexity Extracted Fields Found', 'automatorwp-perplexity' ),
                        'type'    => 'text',
                        'preview' => __( 'Comma-separated list of successfully extracted field names', 'automatorwp-perplexity' ),
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
        $text          = isset( $action_options['text'] )          ? $action_options['text']                                    : '';
        $fields_schema = isset( $action_options['fields_schema'] ) ? $action_options['fields_schema']                           : '';
        $model         = isset( $action_options['model'] )         ? $action_options['model']                                   : 'sonar';
        $response_tag  = isset( $action_options['response_tag'] )  ? sanitize_key( $action_options['response_tag'] )            : '';

        if ( empty( $text ) ) {
            $this->result = __( 'Source text field is empty.', 'automatorwp-perplexity' );
            return;
        }

        if ( empty( $fields_schema ) ) {
            $this->result = __( 'Fields to extract are empty.', 'automatorwp-perplexity' );
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

        $fields     = array_filter( array_map( 'trim', explode( ',', $fields_schema ) ) );
        $field_list = implode( '", "', $fields );

        $system = 'You are a data extraction engine. Respond ONLY with a valid JSON object. Keys must match exactly the requested field names. Use null for any field not found in the text. No markdown fences, no explanation.';
        $prompt = sprintf( 'Extract the following fields from the text below and return a JSON object with keys: "%s".\n\nText:\n%s', $field_list, $text );

        $response = automatorwp_perplexity_api_request( $model, array(
            array( 'role' => 'system', 'content' => $system ),
            array( 'role' => 'user',   'content' => $prompt ),
        ), array(
            'max_tokens'  => 1024,
            'temperature' => 0,
        ) );

        if ( is_wp_error( $response ) ) {
            $this->result = $response->get_error_message();
            return;
        }

        $e    = automatorwp_perplexity_extract_response( $response );
        $raw  = trim( preg_replace( '/^```(?:json)?\s*|\s*```$/', '', $e['text'] ) );
        $data = json_decode( $raw, true );

        $found = array();

        if ( is_array( $data ) ) {
            foreach ( $data as $key => $value ) {
                if ( ! is_null( $value ) && $value !== '' ) {
                    $found[] = $key;
                }
                // Register each field as an individual action tag
                automatorwp_update_action_tag( $action->ID, 'perplexity_extracted_' . sanitize_key( $key ), is_array( $value ) ? wp_json_encode( $value ) : (string) $value );
            }
        }

        automatorwp_update_action_tag( $action->ID, 'perplexity_extracted_json',  $raw );
        automatorwp_update_action_tag( $action->ID, 'perplexity_extracted_found', implode( ', ', $found ) );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->ID, $response_tag, $raw );
        }

        $this->result = sprintf( __( 'Data extracted. Fields found: %s.', 'automatorwp-perplexity' ), implode( ', ', $found ) );
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

new AutomatorWP_Perplexity_Extract_Data();
