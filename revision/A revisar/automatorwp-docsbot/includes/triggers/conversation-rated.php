<?php
/**
 * Conversation Rated
 *
 * @package     AutomatorWP\Integrations\DocsBot\Triggers\Conversation_Rated
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_DocsBot_Conversation_Rated extends AutomatorWP_Integration_Trigger {

    public $integration = 'docsbot';
    public $trigger     = 'docsbot_conversation_rated';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A user rates a conversation', 'automatorwp-docsbot' ),
            'select_option' => __( 'A user <strong>rates</strong> a DocsBot conversation', 'automatorwp-docsbot' ),
            /* translators: %1$s: Rating. %2$s: Number of times. */
            'edit_label'    => sprintf( __( 'A user rates a DocsBot conversation as %1$s %2$s time(s)', 'automatorwp-docsbot' ), '{docsbot_rating}', '{times}' ),
            'log_label'     => __( 'A user rates a DocsBot conversation', 'automatorwp-docsbot' ),
            'action'        => 'automatorwp_docsbot_conversation_rated',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 1,
            'options'       => array(
                'docsbot_rating' => array(
                    'from'    => 'docsbot_rating',
                    'default' => __( 'any rating', 'automatorwp-docsbot' ),
                    'fields'  => array(
                        'docsbot_rating' => array(
                            'name'    => __( 'Rating:', 'automatorwp-docsbot' ),
                            'type'    => 'select',
                            'options' => array(
                                'any'      => __( 'Any rating', 'automatorwp-docsbot' ),
                                'positive' => __( 'Positive', 'automatorwp-docsbot' ),
                                'negative' => __( 'Negative', 'automatorwp-docsbot' ),
                            ),
                            'default' => 'any',
                        ),
                    ),
                ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'docsbot_conversation_id' => array(
                        'label'   => __( 'Conversation ID', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'The DocsBot conversation ID', 'automatorwp-docsbot' ),
                    ),
                    'docsbot_rating' => array(
                        'label'   => __( 'Rating', 'automatorwp-docsbot' ),
                        'type'    => 'text',
                        'preview' => __( 'The rating given (positive or negative)', 'automatorwp-docsbot' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            ),
        ) );

    }

    /**
     * Trigger listener — called when DocsBot fires a conversation.rated webhook
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
        $rating       = isset( $payload['rating'] ) ? sanitize_text_field( $payload['rating'] ) : '';

        automatorwp_trigger_event( array(
            'trigger'                 => $this->trigger,
            'user_id'                 => $user_id,
            'docsbot_conversation_id' => isset( $conversation['id'] ) ? sanitize_text_field( $conversation['id'] ) : '',
            'docsbot_rating'          => $rating,
        ) );

    }

    /**
     * User deserves check — optionally filter by rating value
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

        $required_rating = isset( $trigger_options['docsbot_rating'] ) ? $trigger_options['docsbot_rating'] : 'any';

        // 'any' matches all ratings
        if( $required_rating === 'any' ) {
            return $deserves_trigger;
        }

        // Check if event rating matches the required rating
        $event_rating = isset( $event['docsbot_rating'] ) ? $event['docsbot_rating'] : '';

        if( $event_rating !== $required_rating ) {
            return false;
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

        $log_meta['docsbot_conversation_id'] = isset( $event['docsbot_conversation_id'] ) ? $event['docsbot_conversation_id'] : '';
        $log_meta['docsbot_rating']          = isset( $event['docsbot_rating'] )          ? $event['docsbot_rating']          : '';

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

        $log_fields['docsbot_rating'] = array(
            'name' => __( 'Rating:', 'automatorwp-docsbot' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_DocsBot_Conversation_Rated();
