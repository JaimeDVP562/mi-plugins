<?php
/**
 * Record Custom Event
 *
 * @package     AutomatorWP\Integrations\Drip\Actions\Record_Event
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Drip_Record_Event extends AutomatorWP_Integration_Action {

    public $integration = 'drip';
    public $action      = 'drip_record_event';
    public $result      = '';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Record a custom event for a subscriber', 'automatorwp-drip' ),
            'select_option' => __( 'Record a <strong>custom event</strong> for a subscriber', 'automatorwp-drip' ),
            /* translators: %1$s: event. */
            'edit_label'    => sprintf( __( 'Record event %1$s for subscriber', 'automatorwp-drip' ), '{event}' ),
            /* translators: %1$s: event. */
            'log_label'     => sprintf( __( 'Record event %1$s for subscriber', 'automatorwp-drip' ), '{event}' ),
            'options'       => array(
                'event' => array(
                    'from'    => 'event',
                    'default' => __( 'event', 'automatorwp-drip' ),
                    'fields'  => array(
                        'email' => array(
                            'name'       => __( 'Email:', 'automatorwp-drip' ),
                            'desc'       => __( 'Leave empty to use the email of the user who triggers the automation.', 'automatorwp-drip' ),
                            'type'       => 'text',
                            'attributes' => array(
                                'placeholder' => __( 'sample@email.com or use the tag selector', 'automatorwp-drip' ),
                            ),
                            'default'    => '',
                        ),
                        'event' => array(
                            'name'       => __( 'Event Action:', 'automatorwp-drip' ),
                            'desc'       => __( 'The name of the event to record (e.g. "Logged in", "Completed purchase").', 'automatorwp-drip' ),
                            'type'       => 'text',
                            'required'   => true,
                            'attributes' => array(
                                'placeholder' => __( 'e.g. Logged in', 'automatorwp-drip' ),
                            ),
                            'default'    => '',
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
     * @param stdClass  $action         The action object
     * @param int       $user_id        The user ID
     * @param array     $action_options The action's stored options (with tags already passed)
     * @param stdClass  $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        if ( ! automatorwp_drip_get_api() ) {
            $this->result = __( 'Drip integration not configured.', 'automatorwp-drip' );
            return;
        }

        $email = isset( $action_options['email'] ) && ! empty( $action_options['email'] )
            ? sanitize_email( $action_options['email'] )
            : '';

        if ( empty( $email ) ) {
            $user  = get_user_by( 'ID', $user_id );
            $email = $user ? $user->user_email : '';
        }

        $event_action = isset( $action_options['event'] ) ? sanitize_text_field( $action_options['event'] ) : '';

        if ( empty( $email ) || empty( $event_action ) ) {
            $this->result = __( 'No email or event action provided.', 'automatorwp-drip' );
            return;
        }

        $response = automatorwp_drip_record_event( $email, $event_action );

        // Drip returns 204 No Content on success for events
        if ( $response['code'] === 204 || $response['code'] === 200 ) {
            $this->result = sprintf( __( 'Event "%1$s" recorded for %2$s.', 'automatorwp-drip' ), $event_action, $email );
        } else {
            $this->result = sprintf( __( 'Drip API error: HTTP %d', 'automatorwp-drip' ), $response['code'] );
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );

        parent::hooks();

    }

    /**
     * Configuration notice
     *
     * @since 1.0.0
     *
     * @param stdClass  $object
     * @param string    $item_type
     */
    public function configuration_notice( $object, $item_type ) {

        if ( $item_type !== 'action' || $object->type !== $this->action ) return;

        if ( ! automatorwp_drip_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Drip settings</a> to get this action to work.', 'automatorwp-drip' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-drip'
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

        if ( $action->type !== $this->action ) return $log_meta;

        $log_meta['result'] = (string) $this->result;

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

        if ( $log->type !== 'action' || $object->type !== $this->action ) return $log_fields;

        $log_fields['result'] = array( 'name' => __( 'Result:', 'automatorwp-drip' ), 'type' => 'text' );

        return $log_fields;

    }

}

new AutomatorWP_Drip_Record_Event();
