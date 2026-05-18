<?php
/**
 * Lead Created
 *
 * @package     AutomatorWP\Integrations\DocsBot\Triggers\Lead_Created
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_DocsBot_Lead_Created extends AutomatorWP_Integration_Trigger {

    public $integration = 'docsbot';
    public $trigger     = 'docsbot_lead_created';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A lead is captured in a conversation', 'automatorwp-docsbot' ),
            'select_option' => __( 'A <strong>lead</strong> is captured in a DocsBot conversation', 'automatorwp-docsbot' ),
            /* translators: %1$s: Number of times. */
            'edit_label'    => sprintf( __( 'A lead is captured in a DocsBot conversation %1$s time(s)', 'automatorwp-docsbot' ), '{times}' ),
            'log_label'     => __( 'A lead is captured in a DocsBot conversation', 'automatorwp-docsbot' ),
            'action'        => 'automatorwp_docsbot_lead_created',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'docsbot_lead_name' => array(
                        'label'   => __( 'Lead Name', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'The name of the captured lead', 'automatorwp-docsbot' ),
                    ),
                    'docsbot_lead_email' => array(
                        'label'   => __( 'Lead Email', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'The email of the captured lead', 'automatorwp-docsbot' ),
                    ),
                    'docsbot_lead_company' => array(
                        'label'   => __( 'Lead Company', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'The company of the captured lead', 'automatorwp-docsbot' ),
                    ),
                    'docsbot_conversation_id' => array(
                        'label'   => __( 'Conversation ID', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'The DocsBot conversation ID', 'automatorwp-docsbot' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            ),
        ) );

    }

    /**
     * Trigger listener — called when DocsBot fires a lead.created webhook
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
            'trigger'                => $this->trigger,
            'user_id'                => $user_id,
            'docsbot_lead_name'      => isset( $payload['metadata']['name'] )    ? sanitize_text_field( $payload['metadata']['name'] )    : '',
            'docsbot_lead_email'     => isset( $payload['metadata']['email'] )   ? sanitize_email( $payload['metadata']['email'] )         : '',
            'docsbot_lead_company'   => isset( $payload['metadata']['company'] ) ? sanitize_text_field( $payload['metadata']['company'] )  : '',
            'docsbot_conversation_id'=> isset( $payload['conversationId'] )      ? sanitize_text_field( $payload['conversationId'] )       : '',
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

        $log_meta['docsbot_lead_name']       = isset( $event['docsbot_lead_name'] )       ? $event['docsbot_lead_name']       : '';
        $log_meta['docsbot_lead_email']      = isset( $event['docsbot_lead_email'] )      ? $event['docsbot_lead_email']      : '';
        $log_meta['docsbot_lead_company']    = isset( $event['docsbot_lead_company'] )    ? $event['docsbot_lead_company']    : '';
        $log_meta['docsbot_conversation_id'] = isset( $event['docsbot_conversation_id'] ) ? $event['docsbot_conversation_id'] : '';

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

        $log_fields['docsbot_lead_name'] = array(
            'name' => __( 'Lead Name:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        $log_fields['docsbot_lead_email'] = array(
            'name' => __( 'Lead Email:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        $log_fields['docsbot_lead_company'] = array(
            'name' => __( 'Lead Company:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        $log_fields['docsbot_conversation_id'] = array(
            'name' => __( 'Conversation ID:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_DocsBot_Lead_Created();
