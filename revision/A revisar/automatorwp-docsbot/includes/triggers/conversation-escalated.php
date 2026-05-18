<?php
/**
 * Conversation Escalated
 *
 * @package     AutomatorWP\Integrations\DocsBot\Triggers\Conversation_Escalated
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_DocsBot_Conversation_Escalated extends AutomatorWP_Integration_Trigger {

    public $integration = 'docsbot';
    public $trigger     = 'docsbot_conversation_escalated';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A conversation is escalated to a human', 'automatorwp-docsbot' ),
            'select_option' => __( 'A DocsBot conversation is <strong>escalated to a human</strong>', 'automatorwp-docsbot' ),
            /* translators: %1$s: Number of times. */
            'edit_label'    => sprintf( __( 'A DocsBot conversation is escalated to a human %1$s time(s)', 'automatorwp-docsbot' ), '{times}' ),
            'log_label'     => __( 'A DocsBot conversation is escalated to a human', 'automatorwp-docsbot' ),
            'action'        => 'automatorwp_docsbot_conversation_escalated',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'docsbot_conversation_id' => array(
                        'label'   => __( 'Conversation ID', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'The DocsBot conversation ID', 'automatorwp-docsbot' ),
                    ),
                    'docsbot_conversation_title' => array(
                        'label'   => __( 'Conversation Title', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'The title of the escalated conversation', 'automatorwp-docsbot' ),
                    ),
                    'docsbot_escalation_summary' => array(
                        'label'   => __( 'Conversation Summary', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'A summary of the escalated conversation', 'automatorwp-docsbot' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            ),
        ) );

    }

    /**
     * Trigger listener — called when DocsBot fires a conversation.escalated webhook
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

        $conversation = isset( $payload['conversation'] ) ? $payload['conversation'] : array();

        automatorwp_trigger_event( array(
            'trigger'                    => $this->trigger,
            'user_id'                    => $user_id,
            'docsbot_conversation_id'    => isset( $conversation['id'] )      ? sanitize_text_field( $conversation['id'] )      : '',
            'docsbot_conversation_title' => isset( $conversation['title'] )   ? sanitize_text_field( $conversation['title'] )   : '',
            'docsbot_escalation_summary' => isset( $conversation['summary'] ) ? sanitize_textarea_field( $conversation['summary'] ) : '',
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

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );

        parent::hooks();

    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta
     * @param stdClass  $trigger
     * @param int       $user_id
     * @param array     $event
     * @param array     $trigger_options
     * @param stdClass  $automation
     *
     * @return array
     */
    public function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['docsbot_conversation_id']    = isset( $event['docsbot_conversation_id'] )    ? $event['docsbot_conversation_id']    : '';
        $log_meta['docsbot_conversation_title'] = isset( $event['docsbot_conversation_title'] ) ? $event['docsbot_conversation_title'] : '';
        $log_meta['docsbot_escalation_summary'] = isset( $event['docsbot_escalation_summary'] ) ? $event['docsbot_escalation_summary'] : '';

        return $log_meta;

    }

    /**
     * Trigger custom log fields
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

        if( $log->type !== 'trigger' ) {
            return $log_fields;
        }

        if( $object->type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['docsbot_conversation_id'] = array(
            'name' => __( 'Conversation ID:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        $log_fields['docsbot_conversation_title'] = array(
            'name' => __( 'Conversation Title:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        $log_fields['docsbot_escalation_summary'] = array(
            'name' => __( 'Conversation Summary:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_DocsBot_Conversation_Escalated();
