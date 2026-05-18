<?php
/**
 * Deep Research Done
 *
 * @package     AutomatorWP\DocsBot\Triggers\Deep_Research_Done
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_DocsBot_Deep_Research_Done extends AutomatorWP_Integration_Trigger {

    public $integration = 'docsbot';
    public $trigger     = 'docsbot_deep_research_done';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A deep research job is completed', 'automatorwp-docsbot' ),
            'select_option' => __( 'A DocsBot <strong>deep research</strong> job is completed', 'automatorwp-docsbot' ),
            /* translators: %1$s: Number of times. */
            'edit_label'    => sprintf( __( 'A DocsBot deep research job is completed %1$s time(s)', 'automatorwp-docsbot' ), '{times}' ),
            'log_label'     => __( 'A DocsBot deep research job is completed', 'automatorwp-docsbot' ),
            'action'        => 'automatorwp_docsbot_deep_research_done',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'docsbot_research_id' => array(
                        'label'   => __( 'Research Job ID', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'The ID of the completed deep research job', 'automatorwp-docsbot' ),
                    ),
                    'docsbot_research_title' => array(
                        'label'   => __( 'Research Title', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'The title of the deep research job', 'automatorwp-docsbot' ),
                    ),
                    'docsbot_research_status' => array(
                        'label'   => __( 'Research Status', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'The final status of the research job', 'automatorwp-docsbot' ),
                    ),
                    'docsbot_research_result' => array(
                        'label'   => __( 'Research Result', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'The result or summary of the deep research', 'automatorwp-docsbot' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            ),
        ) );

    }

    /**
     * Trigger listener — called when DocsBot fires a deep_research.done webhook
     *
     * @since 1.0.0
     *
     * @param array $payload Decoded webhook payload
     */
    public function listener( $payload ) {

        $user_id = automatorwp_docsbot_get_user_id_from_payload( $payload );

        if( $user_id === 0 ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'                 => $this->trigger,
            'user_id'                 => $user_id,
            'docsbot_research_id'     => isset( $payload['id'] )     ? sanitize_text_field( $payload['id'] )               : '',
            'docsbot_research_title'  => isset( $payload['title'] )  ? sanitize_text_field( $payload['title'] )            : '',
            'docsbot_research_status' => isset( $payload['status'] ) ? sanitize_text_field( $payload['status'] )           : '',
            'docsbot_research_result' => isset( $payload['result'] ) ? sanitize_textarea_field( $payload['result'] )       : '',
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger
     * @param stdClass  $trigger
     * @param int       $user_id
     * @param array     $event
     * @param array     $trigger_options
     * @param stdClass  $automation
     *
     * @return bool
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        if( $trigger->type !== $this->trigger ) {
            return $deserves_trigger;
        }

        return $deserves_trigger;

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );

        parent::hooks();

    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     */
    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['docsbot_research_id']     = isset( $event['docsbot_research_id'] )     ? $event['docsbot_research_id']     : '';
        $log_meta['docsbot_research_title']  = isset( $event['docsbot_research_title'] )  ? $event['docsbot_research_title']  : '';
        $log_meta['docsbot_research_status'] = isset( $event['docsbot_research_status'] ) ? $event['docsbot_research_status'] : '';
        $log_meta['docsbot_research_result'] = isset( $event['docsbot_research_result'] ) ? $event['docsbot_research_result'] : '';

        return $log_meta;

    }

    /**
     * Trigger custom log fields
     *
     * @since 1.0.0
     */
    public function log_fields( $log_fields, $log, $object ) {

        if( $log->type !== 'trigger' ) {
            return $log_fields;
        }

        if( $object->type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['docsbot_research_id'] = array(
            'name' => __( 'Research Job ID:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        $log_fields['docsbot_research_title'] = array(
            'name' => __( 'Research Title:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        $log_fields['docsbot_research_status'] = array(
            'name' => __( 'Research Status:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        $log_fields['docsbot_research_result'] = array(
            'name' => __( 'Research Result:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_DocsBot_Deep_Research_Done();
