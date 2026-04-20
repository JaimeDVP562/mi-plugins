<?php
/**
 * Subscriber Subscribed to Campaign
 *
 * @package     AutomatorWP\Integrations\Drip\Triggers\Subscribed_To_Campaign
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Drip_Subscribed_To_Campaign extends AutomatorWP_Integration_Trigger {

    public $integration = 'drip';
    public $trigger     = 'drip_subscribed_to_campaign';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __( 'A subscriber subscribes to a Drip campaign', 'automatorwp-drip' ),
            'select_option' => __( 'A subscriber subscribes to a <strong>Drip campaign</strong>', 'automatorwp-drip' ),
            /* translators: %1$s: Number of times. */
            'edit_label'    => sprintf( __( 'A subscriber subscribes to a Drip campaign %1$s time(s)', 'automatorwp-drip' ), '{times}' ),
            'log_label'     => __( 'A subscriber subscribes to a Drip campaign', 'automatorwp-drip' ),
            'action'        => 'automatorwp_drip_subscribed_to_campaign',
            'function'      => array( $this, 'listener' ),
            'priority'      => 10,
            'accepted_args' => 2,
            'options'       => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags'          => array_merge(
                array(
                    'drip_subscriber_email' => array(
                        'label'   => __( 'Subscriber Email', 'automatorwp-drip' ),
                        'type'    => 'text',
                        'preview' => __( 'The email of the Drip subscriber', 'automatorwp-drip' ),
                    ),
                    'drip_campaign_id' => array(
                        'label'   => __( 'Campaign ID', 'automatorwp-drip' ),
                        'type'    => 'text',
                        'preview' => __( 'The ID of the Drip campaign', 'automatorwp-drip' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            ),
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param array  $subscriber  Drip subscriber data
     * @param string $campaign_id The campaign ID
     */
    public function listener( $subscriber, $campaign_id ) {

        $email = isset( $subscriber['email'] ) ? sanitize_email( $subscriber['email'] ) : '';

        if ( empty( $email ) ) {
            return;
        }

        $user    = get_user_by( 'email', $email );
        $admins  = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC' ) );
        $user_id = $user ? (int) $user->ID : ( ! empty( $admins ) ? (int) $admins[0]->ID : 0 );

        automatorwp_trigger_event( array(
            'trigger'               => $this->trigger,
            'user_id'               => $user_id,
            'drip_subscriber_email' => $email,
            'drip_campaign_id'      => sanitize_text_field( $campaign_id ),
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

        if ( $trigger->type !== $this->trigger ) {
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

        if ( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['drip_subscriber_email'] = isset( $event['drip_subscriber_email'] ) ? $event['drip_subscriber_email'] : '';
        $log_meta['drip_campaign_id']      = isset( $event['drip_campaign_id'] ) ? $event['drip_campaign_id'] : '';

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

        if ( $log->type !== 'trigger' || $object->type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['drip_subscriber_email'] = array( 'name' => __( 'Subscriber Email:', 'automatorwp-drip' ), 'type' => 'text' );
        $log_fields['drip_campaign_id']      = array( 'name' => __( 'Campaign ID:', 'automatorwp-drip' ), 'type' => 'text' );

        return $log_fields;

    }

}

new AutomatorWP_Drip_Subscribed_To_Campaign();
