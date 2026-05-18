<?php
/**
 * Add Comment Task
 *
 * @package     AutomatorWP\Integrations\Slack\Actions\Add_Comment_User
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Slack_Add_Comment_User extends AutomatorWP_Integration_Action {

    public $integration = 'slack';
    public $action = 'slack_add_comment_user';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add comment to user', 'automatorwp-slack' ),
            'select_option'     => __( 'Add comment to <strong>user</strong>', 'automatorwp-slack' ),
            /* translators: %1$s: User. */
            'edit_label'        => sprintf( __( 'Add comment to user %1$s', 'automatorwp-slack' ), '{user}' ),
            /* translators: %1$s: User. */
            'log_label'         => sprintf( __( 'Add comment to user %1$s', 'automatorwp-slack' ), '{user}' ),
            'options'           => array(
                'user' => array(
                    'from' => 'user',
                    'default' => __( 'user', 'automatorwp-slack' ),
                    'fields' => array(
                        'user' => automatorwp_utilities_ajax_selector_field( array(
                            'name'              => __( 'User:', 'automatorwp-slack' ),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_slack_get_users',
                            'options_cb'        => 'automatorwp_slack_options_cb_user',
                            'placeholder'       => 'Select a user',
                            'option_custom'         => true,
                            'option_custom_desc'    => __('Name or user ID', 'automatorwp-slack')
                        ) ),
                        'user_custom' => automatorwp_utilities_custom_field( array(
                            'option_custom_desc'    => __( 'The user id, name or email', 'automatorwp-slack' ),
                        ) ),
                    'comment' => array(
                            'name' => __( 'Comment:', 'automatorwp-slack' ),
                            'desc' => __( 'The comment to add to the user', 'automatorwp-slack' ),
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
        
        if( $action_options['user'] === 'custom' ) {
            $action_options['user'] = $action_options['user_custom'];
        }
        
        // Shorthand
        $user_cod = automatorwp_slack_get_user_id( $action_options['user'] );
        $comment_text = $action_options['comment'];

        // Bail if comment is empty
        if ( empty ( $comment_text ) ) {
            return;
        }

        $this->result = '';

        // Bail if Slack not configured
        if( ! automatorwp_slack_get_api() ) {
            $this->result = __( 'Slack integration not configured in AutomatorWP settings.', 'automatorwp-slack' );
            return;
        }
        
        // Add message to user
        $response = automatorwp_slack_add_comment( $user_cod, $comment_text );
        
        // Create comment
        if ( $response['ok'] === 200 ) {
            $this->result = __( 'Created comment to user ', 'automatorwp-slack' );
        } else {

            $text_error = $response['error'];

            // In Slack users are channels too, improved readability
            $text_error = str_replace( ['channel', 'Channel'], ['user', 'User'], $text_error );
            $text_error = automatorwp_slack_add_error_info( $text_error, "user", $action_options['user'] );
                
            $this->result = __( 'The comment could not be created' . $text_error, 'automatorwp-slack' );
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

new AutomatorWP_Slack_Add_Comment_User();