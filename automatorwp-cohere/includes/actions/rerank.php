<?php
/**
 * Rerank Documents
 *
 * @package     AutomatorWP\Integrations\Cohere\Actions\Rerank
 * @since       1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Cohere_Rerank extends AutomatorWP_Integration_Action 
{
    public $integration = 'cohere';
    public $action      = 'cohere_rerank';

    public function register()
    {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Rerank documents by relevance with Cohere', 'automatorwp-cohere' ),
            'select_option' => __( 'Rerank <strong>documents</strong> by relevance with Cohere', 'automatorwp-cohere' ),
            'edit_label'    => __( 'Rerank documents for {query} with Cohere', 'automatorwp-cohere' ),
            'log_label'     => __( 'Rerank documents by relevance with Cohere', 'automatorwp-cohere' ),
            'options'       => array(
                'query' => array(
                    'from'    => 'action',
                    'default' => __( 'query', 'automatorwp-cohere' ),
                    'fields'  => array(
                        'model' => array(
                            'name'    => __( 'Model', 'automatorwp-cohere' ),
                            'desc'    => __( 'Cohere Rerank model to use.', 'automatorwp-cohere' ),
                            'type'    => 'select',
                            'options' => automatorwp_cohere_get_rerank_models(),
                            'default' => 'rerank-v4.0-pro',
                        ),
                        'query' => array(
                            'name'     => __( 'Query', 'automatorwp-cohere' ),
                            'desc'     => __( 'Search query to rank the documents against. Supports tags.', 'automatorwp-cohere' ),
                            'type'     => 'text',
                            'default'  => '',
                            'required' => true,
                        ),
                        'documents' => array(
                            'name'     => __( 'Documents', 'automatorwp-cohere' ),
                            'desc'     => __( 'One document per line. Each line is ranked independently. Supports tags.', 'automatorwp-cohere' ),
                            'type'     => 'textarea',
                            'default'  => '',
                            'required' => true,
                        ),
                        'top_n' => array(
                            'name'    => __( 'Top N Results', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) Return only the top N ranked documents. Leave empty to return all.', 'automatorwp-cohere' ),
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
                            'name'    => __( 'Store Results As Tag', 'automatorwp-cohere' ),
                            'desc'    => __( '(Optional) Custom tag name to store the ranked results JSON.', 'automatorwp-cohere' ),
                            'type'    => 'text',
                            'default' => '',
                        ),
                    ),
                ),
            ),
            'tags' => array(
                'cohere_rerank_results' => array(
                    'label'   => __( 'Cohere Rerank Results', 'automatorwp-cohere' ),
                    'type'    => 'text',
                    'preview' => __( 'JSON array of ranked documents with relevance scores', 'automatorwp-cohere' ),
                ),
                'cohere_rerank_top' => array(
                    'label'   => __( 'Cohere Top Ranked Document', 'automatorwp-cohere' ),
                    'type'    => 'text',
                    'preview' => __( 'Text of the most relevant document', 'automatorwp-cohere' ),
                ),
            ),
        ) );
    }

    public function execute( $action, $user_id, $action_options, $automation )
    {
        $model        = isset( $action_options['model'] )        ? $action_options['model']                          : 'rerank-v4.0-pro';
        $query        = isset( $action_options['query'] )        ? $action_options['query']                          : '';
        $documents    = isset( $action_options['documents'] )    ? $action_options['documents']                      : '';
        $top_n        = isset( $action_options['top_n'] )        ? (int) $action_options['top_n']                    : 0;
        $response_tag = isset( $action_options['response_tag'] ) ? sanitize_key( $action_options['response_tag'] )  : '';

        if ( empty( $query ) ) {
            $this->result = __( 'Query field is empty.', 'automatorwp-cohere' );
            return;
        }

        if ( empty( $documents ) ) {
            $this->result = __( 'Documents field is empty.', 'automatorwp-cohere' );
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

        $docs = array_values( array_filter( array_map( 'trim', explode( "\n", $documents ) ) ) );

        $body = array(
            'model'     => $model,
            'query'     => $query,
            'documents' => $docs,
        );

        if ( $top_n > 0 ) {
            $body['top_n'] = $top_n;
        }

        $response = automatorwp_cohere_api_request( '/v2/rerank', $body );

        if ( is_wp_error( $response ) ) {
            $this->result = $response->get_error_message();
            return;
        }

        $results     = isset( $response['results'] ) ? $response['results'] : array();
        $results_out = array();
        $top_doc     = '';

        foreach ( $results as $i => $item ) {
            $idx   = isset( $item['index'] ) ? (int) $item['index'] : 0;
            $score = isset( $item['relevance_score'] ) ? round( (float) $item['relevance_score'], 4 ) : 0;
            $doc   = isset( $docs[ $idx ] ) ? $docs[ $idx ] : '';
            $results_out[] = array( 'index' => $idx, 'relevance_score' => $score, 'document' => $doc );
            if ( $i === 0 ) $top_doc = $doc;
        }

        $results_json = wp_json_encode( $results_out );

        automatorwp_update_action_tag( $action->id, 'cohere_rerank_results', $results_json );
        automatorwp_update_action_tag( $action->id, 'cohere_rerank_top',     $top_doc );

        if ( ! empty( $response_tag ) ) {
            automatorwp_update_action_tag( $action->id, $response_tag, $results_json );
        }

        $this->result = sprintf( __( '%d document(s) reranked by Cohere successfully.', 'automatorwp-cohere' ), count( $results_out ) );
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
        $log_fields['cohere_rerank_top'] = array( 'name' => __( 'Top Document:', 'automatorwp-cohere' ), 'type' => 'text' );
        return $log_fields;
    }
}

new AutomatorWP_Cohere_Rerank();
