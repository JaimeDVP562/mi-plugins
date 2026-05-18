<?php
/**
 * Add Comment Task
 *
 * @package     AutomatorWP\Integrations\Slack\Actions\Add_Comment_Thread
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Slack_Add_Comment_Thread_List extends AutomatorWP_Integration_Action {

    public $integration = 'slack';
    public $action = 'slack_add_comment_thread_list';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add comment to thread from a list', 'automatorwp-slack' ),
            'select_option'     => __( 'Add comment to <strong>thread</strong> from a list', 'automatorwp-slack' ),
            /* translators: %1$s: Thread. */
            'edit_label'        => sprintf( __( 'Add comment to a  %1$s', 'automatorwp-slack' ), '{thread}' ),
            /* translators: %1$s: Thread. */
            'log_label'         => sprintf( __( 'Add comment to a  %1$s', 'automatorwp-slack' ), '{thread}' ),
            'options'           => array(
                'thread' => array(
                    'from' => 'thread',
                    'default' => __( 'thread', 'automatorwp-slack' ),
                    'fields' => array(
                        'channel' => automatorwp_utilities_ajax_selector_field( array(
                            'name'              => __( 'Channel:', 'automatorwp-slack' ),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_slack_get_channels',
                            'options_cb'        => 'automatorwp_slack_options_cb_channel',
                            'placeholder'       => 'Select a channel',
                            'default'           => '',
                        ) ),
                        'thread' => automatorwp_utilities_ajax_selector_field( array(
                            'name'              => __( 'Thread:', 'automatorwp-slack' ),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_slack_get_threads',
                            'options_cb'        => 'automatorwp_slack_options_cb_thread',
                            'placeholder'       => 'Select a thread',
                            'default'           => '',
                        ) ),
                        'comment' => array(
                            'name' => __( 'Comment:', 'automatorwp-slack' ),
                            'desc' => __( 'The comment to add to the thread', 'automatorwp-slack' ),
                            'type' => 'wysiwyg',
                            'required'  => true,
                            'default' => ''
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
    
        // Shorthand
        $channel_id = $action_options['channel'];
        $thread_ts = $action_options['thread'];
        $comment_text = $action_options['comment'];
        

        // Bail if data is empty
        if ( empty ( $channel_id ) || empty ( $thread_ts ) || empty ( $comment_text ) ) {
            return;
        }

        $this->result = '';

        // Bail if Slack not configured
        if( ! automatorwp_slack_get_api() ) {
            $this->result = __( 'Slack integration not configured in AutomatorWP settings.', 'automatorwp-slack' );
            return;
        }

        // Search the channel id
        $channel_id = automatorwp_slack_get_channel_id( $channel_id );

        // Add new comment to selected thread
        $response = automatorwp_slack_add_comment_thread( $channel_id, $comment_text, $thread_ts );

        // Create list if not exist
        if ( $response['ok'] === 200 ) {
            $this->result = __( 'Created comment in thread', 'automatorwp-slack' );
        } else {
            $text_error = $response['error'];

             // Append which data causes the failure
             if ( str_contains( strtolower( $text_error ), 'channel' ) ) {
                $text_error = automatorwp_slack_add_error_info( $text_error, 'channel', $action_options['channel_text'] );        
            } elseif( str_contains(strtolower( $text_error ), 'thread' ) ) {
                $text_error = automatorwp_slack_add_error_info( $text_error, 'thread', $thread_ts );        
            }
            
            $this->result = __( 'The comment could not be created', 'automatorwp-slack' );
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

new AutomatorWP_Slack_Add_Comment_Thread_List();