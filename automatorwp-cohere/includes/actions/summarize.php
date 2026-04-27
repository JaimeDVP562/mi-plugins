<?php
/**
 * Summarize Text
 *
 * @package     AutomatorWP\Integrations\Cohere\Actions\Summarize
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Cohere_Summarize extends AutomatorWP_Integration_Action 
{
    public $integration = 'cohere';
    public $action      = 'cohere_summarize';

    public function register()
    {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Summarize text with Cohere', 'automatorwp-cohere' ),
            'select_option' => __( 'Summarize <strong>text</strong> with Cohere', 'automatorwp-cohere' ),
            'edit_label'    => __( 'Summarize {text} with Cohere and store the summary', 'automatorwp-cohere' ),
            'log_label'     => __( 'Summarize text with Cohere', 'automatorwp-cohere' ),
            'options'       => array(
                'text' => array(
                    'from'    => 'action',
                    'default' => __( 'text', 'automatorwp-cohere' ),
                    'fields'  => array(
                        'text' => array(
                            'name'     => __( 'Text', 'automatorwp-cohere' ),
                            'desc'     => __( 'Text to summarize (up to 100,000 characters). Supports tags.', 'automatorwp-cohere' ),
                            'type'     => 'textarea',
                            'default'  => '',
                            'required' => true,
                        ),
                        'length' => array(
                            'name'    => __( 'Summary Length', 'automatorwp-cohere' ),
                            'desc'    => __( 'Desired length of the summary.', 'automatorwp-cohere' ),
                            'type'    => 'select',
                            'options' => array(
                                'auto'   => __( 'Auto', 'automatorwp-cohere' ),
                                'short'  => __( 'Short', 'automatorwp-cohere' ),
                                'medium' => __( 'Medium', 'automatorwp-cohere' ),
                                'long'   => __( 'Long', 'automatorwp-cohere' ),
                            ),
                            'default' => 'auto',
                        ),
                        'format' => array(
                            'name'    => __( 'Summary Format', 'automatorwp-cohere' ),
                            'desc'    => __( 'Output format for the summary.', 'automatorwp-cohere' ),
                            'type'    => 'select',
                            'options' => array(
                                'auto'      => __( 'Auto', 'automatorwp-cohere' ),
                                'paragraph' => __( 'Paragraph', 'automatorwp-cohere' ),
                                'bullets'   => __( 'Bullet points', 'automatorwp-cohere' ),
                            ),
                            'default' => 'auto',
                        ),
                        'extractiveness' => array(
                            'name'    => __( 'Extractiveness', 'automatorwp-cohere' ),
                            'desc'    => __( 'How closely the summary follows the original wording.', 'automatorwp-cohere' ),
                            'type'    => 'select',
                            'options' => array(
                                'auto'   => __( 'Auto', 'automatorwp-cohere' ),
                                'low'    => __( 'Low (more abstractive)', 'automatorwp-cohere' ),
                                'medium' => __( 'Medium', 'automatorwp-cohere' ),
                                'high'   => __( 'High (more extractive)', 'automatorwp-cohere' ),
                            ),
                            'default' => 'auto',
                        ),
                        'additional_command' => array(
                            'name'    => __( 'Additional Instruction', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) Extra instruction for the summary. Supports tags.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => '',
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
                            'name'    => __( 'Store Summary As Tag', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) Custom tag name to reuse the summary in subsequent actions.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                    ),
                ),
            ),
            'tags' => array(
                'cohere_summary' => array(
                    'label'   => __( 'Cohere Summary', 'automatorwp-cohere' ),
                    'type'    => 'text',
                    'preview' => __( 'AI-generated summary of the text', 'automatorwp-cohere' ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation )
    {
        $text               = isset( $action_options['text'] )               ? $action_options['text']                          : '';
        $length             = isset( $action_options['length'] )             ? $action_options['length']                        : 'auto';
        $format             = isset( $action_options['format'] )             ? $action_options['format']                        : 'auto';
        $extractiveness     = isset( $action_options['extractiveness'] )     ? $action_options['extractiveness']                : 'auto';
        $additional_command = isset( $action_options['additional_command'] ) ? $action_options['additional_command']            : '';
        $response_tag       = isset( $action_options['response_tag'] )       ? sanitize_key( $action_options['response_tag'] )  : '';

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

        // Map UI option values to natural-language prompt fragments so the
        // instruction sent to the model reads as a coherent English sentence.
        $length_map = array(
            'short'  => 'a short (1-2 sentence)',
            'medium' => 'a medium-length (1 paragraph)',
            'long'   => 'a long (several paragraphs)',
            'auto'   => 'a well-sized',
        );
        $format_map = array(
            'paragraph' => 'as a paragraph',
            'bullets'   => 'as bullet points',
            'auto'      => '',
        );
        $extract_map = array(
            'low'    => 'Use your own words (abstractive).',
            'medium' => '',
            'high'   => 'Stay close to the original wording (extractive).',
            'auto'   => '',
        );

        $len_desc     = isset( $length_map[ $length ] )          ? $length_map[ $length ]          : 'a well-sized';
        $fmt_desc     = isset( $format_map[ $format ] )          ? $format_map[ $format ]          : '';
        $extract_desc = isset( $extract_map[ $extractiveness ] ) ? $extract_map[ $extractiveness ] : '';

        $prompt = "Write {$len_desc} summary of the following text" . ( $fmt_desc ? " {$fmt_desc}" : '' ) . "."
                . ( $extract_desc ? " {$extract_desc}" : '' )
                . ( ! empty( $additional_command ) ? " {$additional_command}" : '' )
                . " Return only the summary, no preamble.\n\nText:\n{$text}";

        $response = automatorwp_cohere_api_request( '/v2/chat', array(
            'model'       => 'command-r7b-12-2024',
            'messages'    => array( array( 'role' => 'user', 'content' => $prompt ) ),
            'max_tokens'  => 1024,
            'temperature' => 0.3,
        ) );

        if ( is_wp_error( $response ) ) {
            $this->result = $response->get_error_message();
            return;
        }

        $summary = automatorwp_cohere_extract_chat_text( $response );

        automatorwp_update_action_tag( $action->id, 'cohere_summary', $summary );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->id, $response_tag, $summary );
        }

        $this->result = sprintf( __( 'Text summarized with Cohere. Summary length: %d characters.', 'automatorwp-cohere' ), strlen( $summary ) );
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
        $log_fields['cohere_summary'] = array( 'name' => __( 'Summary:', 'automatorwp-cohere' ), 'type' => 'text' );
        return $log_fields;
    }
}

new AutomatorWP_Cohere_Summarize();
