<?php
/**
 * Search Bot
 *
 * @package     AutomatorWP\Integrations\DocsBot\Actions\Search_Bot
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_DocsBot_Search_Bot extends AutomatorWP_Integration_Action {

    public $integration = 'docsbot';
    public $action      = 'docsbot_search_bot';
    public $results     = array();

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Run a semantic search on the bot', 'automatorwp-docsbot' ),
            'select_option' => __( 'Run a <strong>semantic search</strong> on the DocsBot bot', 'automatorwp-docsbot' ),
            /* translators: %1$s: Query. */
            'edit_label'    => sprintf( __( 'Search DocsBot for %1$s', 'automatorwp-docsbot' ), '{query}' ),
            /* translators: %1$s: Query. */
            'log_label'     => sprintf( __( 'Search DocsBot for %1$s', 'automatorwp-docsbot' ), '{query}' ),
            'options'       => array(
                'query' => array(
                    'default' => __( 'query', 'automatorwp-docsbot' ),
                    'fields'  => array(
                        'query' => array(
                            'name'     => __( 'Search Query:', 'automatorwp-docsbot' ),
                            'desc'     => __( 'The text to search for in the DocsBot knowledge base. You can use tags from previous triggers.', 'automatorwp-docsbot' ),
                            'type'     => 'text',
                            'default'  => '',
                            'required' => true,
                        ),
                        'top_k' => array(
                            'name'    => __( 'Number of Results:', 'automatorwp-docsbot' ),
                            'desc'    => __( 'Maximum number of matching chunks to retrieve (1–16).', 'automatorwp-docsbot' ),
                            'type'    => 'text',
                            'default' => '4',
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

        $query = isset( $action_options['query'] ) ? sanitize_text_field( $action_options['query'] ) : '';
        $top_k = isset( $action_options['top_k'] ) ? absint( $action_options['top_k'] )              : 4;

        if( empty( $query ) ) {
            $this->result = __( 'No search query provided.', 'automatorwp-docsbot' );
            return;
        }

        // Clamp top_k between 1 and 16
        $top_k = max( 1, min( 16, $top_k ) );

        if( ! automatorwp_docsbot_get_api() ) {
            $this->result = __( 'DocsBot AI is not configured in AutomatorWP settings.', 'automatorwp-docsbot' );
            return;
        }

        $body = array(
            'query' => $query,
            'top_k' => $top_k,
            'alpha' => 0.75,
        );

        $response = automatorwp_docsbot_api_request( 'search', $body );

        if( is_wp_error( $response ) ) {
            $this->result = sprintf(
                /* translators: %s: error message */
                __( 'DocsBot API error: %s', 'automatorwp-docsbot' ),
                $response->get_error_message()
            );
            return;
        }

        $results = is_array( $response ) ? $response : array();
        $count   = count( $results );

        $this->result  = sprintf(
            /* translators: %d: number of results */
            _n( '%d result found.', '%d results found.', $count, 'automatorwp-docsbot' ),
            $count
        );
        $this->results = $results;

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

        $log_meta['docsbot_search_result']  = $this->result;
        $log_meta['docsbot_search_results'] = isset( $this->results ) ? wp_json_encode( $this->results ) : '';

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

        $log_fields['docsbot_search_result'] = array(
            'name' => __( 'Result:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        $log_fields['docsbot_search_results'] = array(
            'name' => __( 'Matching Chunks:', 'automatorwp-docsbot' ),
            'desc' => __( 'JSON-encoded list of matching document chunks returned by the search.', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_DocsBot_Search_Bot();
