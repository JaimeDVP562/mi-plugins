<?php
/**
 * Add User to channel
 *
 * @package     AutomatorWP\Integrations\Slack\Actions\Remove_Channel
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Slack_Remove_Channel extends AutomatorWP_Integration_Action {

    public $integration = 'slack';
    public $action = 'slack_remove_channel';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Remove channel', 'automatorwp-slack' ),
            'select_option'     => __( 'Remove <strong>channel</strong>', 'automatorwp-slack' ),
            /* translators: %1$s: Channel. */
            'edit_label'        => sprintf( __( 'Remove  %1$s', 'automatorwp-slack' ), '{channel}' ),
            /* translators: %1$s: Channel. */
            'log_label'         => sprintf( __( 'Remove  %1$s', 'automatorwp-slack' ), '{channel}' ),
            'options'           => array(
                'channel' => array(
                        'from' => 'channel',
                        'default' => __( 'channel', 'automatorwp-slack' ),
                        'fields' => array(
                        'channel' => automatorwp_utilities_ajax_selector_field( array(
                                'name'              => __( 'Channel:', 'automatorwp-slack' ),
                                'option_none'       => false,
                                'action_cb'         => 'automatorwp_slack_get_channels',
                                'options_cb'        => 'automatorwp_slack_options_cb_channel',
                                'placeholder'       => 'Select a channel',
                                ) ),
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
        
        // Shorthand
        $channel_name = $action_options['channel'];
        
        // Bail if comment is empty
        if ( empty ( $channel_name ) ) {
            return;
        }

        $this->result = '';

        // Bail if Slack not configured
        if( ! automatorwp_slack_get_api() ) {
            $this->result = __( 'Slack integration not configured in AutomatorWP settings.', 'automatorwp-slack' );
            return;
        }
        
        // Remove channel
        $response = automatorwp_slack_remove_channel( $channel_name );
     
        // Create list if not exist
        if ( $response === 200 ) {
            $this->result = __( 'Rremoved channel', 'automatorwp-slack' );
        } else {
            $this->result = __( 'The channel could not be removed', 'automatorwp-slack' );
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Configuration notice
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Configuration notice
     *
     * @since 1.0.0
     *
     * @param stdClass  $object     The trigger/action object
     * @param string    $item_type  The object type (trigger|action)
     */
    public function configuration_notice( $object, $item_type ) {

        // Bail if action type don't match this action
        if( $item_type !== 'action' ) {
            return;
        }

        if( $object->type !== $this->action ) {
            return;
        }

        // Warn user if the authorization has not been setup from settings
        if( ! automatorwp_slack_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Slack settings</a> to get this action to work.', 'automatorwp-slack' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-slack'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-slack' ),
                    'https://automatorwp.com/docs/slack/'
                ); ?>
            </div>
        <?php endif;

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        // Store the action's result
        $log_meta['result'] = $this->result;
       
        return $log_meta;
    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        // Bail if log is not assigned to an action
        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-slack' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_Slack_Remove_Channel();