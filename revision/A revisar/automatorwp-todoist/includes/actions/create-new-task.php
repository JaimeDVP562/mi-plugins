<?php
/**
 * Create New Task
 *
 * @package     AutomatorWP\Integrations\Todoist\Actions\Create-New-Task
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined('ABSPATH')) exit;

class AutomatorWP_Todoist_Create_New_Task extends AutomatorWP_Integration_Action {

    public $integration = 'todoist';
    public $action = 'todoist_create_new-task';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register(){
            
        automatorwp_register_action( $this->action, array(
          'integration'       => $this->integration,
          'label'             => __( 'Create a new task', 'automatorwp-todoist'),
          'select_option'     => __( 'Create a new <strong>task</strong>', 'automatorwp-todoist'),
          /* translators: %1$s: Card. */
          'edit_label'        => sprintf( __( 'Create a new task named %1$s on project %2$s', 'automatorwp-todoist' ), '{task_name}', '{project}' ),
          /* translators: %1$s: Card. */
          'log_label'         => sprintf( __( 'Create a new task named %1$s on project %2$s', 'automatorwp-todoist' ) , '{task_name}', '{project}' ),
          'options'           => array(
              'task_name' => array(
                'from' => 'name',
                'default' => __('name', 'automatorwp-todoist'),
                'fields' => array(
                  'task_name' => array(
                    'name'          => __('Task Name:', 'automatorwp-todoist'),
                    'type'          => 'text',
                    'default'       => '',
                    'required'      => true
                  ),
                ),
              ),
              'project' => array(
                'from' => 'project',
                'default' => __('list from projects', 'automatorwp-todoist'),
                'fields' => array(
                  'project' => automatorwp_utilities_ajax_selector_field( array(
                    'name'              => __('Project: ', 'automatorwp-todoist'),
                    'option_none'       => false,
                    'action_cb'         => 'automatorwp_todoist_get_projects',
                    'options_cb'        => 'automatorwp_todoist_options_cb_projects',
                    'placeholder'       => 'Select a project',
                    'default'           => '',
                    'required'          => true
                  )),
                )
              )
            ),
          ),
        );
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
        $task_name = $action_options['task_name'];
        $project_id = $action_options['project'];
        
        // Bail if list_id is empty
        if ( empty ( $task_name ) ) {
            return;
        }

        // Bail if Todoist not configured
        if( ! automatorwp_todoist_get_api() ) {
            $this->result = __( 'Todoist integration is not configured in AutomatorWP settings', 'automatorwp-todoist' );
            return;
        }

        $response = automatorwp_todoist_create_task( $task_name, $project_id );

        if( $response === 200 ) {
            $this->result = __( 'New task created', 'autoamtorwp-todoist' );
        }else {
            $this->result = __( 'The task could not be created', 'automatorwp-todoist' );
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
        if( ! automatorwp_todoist_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">Todoist settings</a> to get this action to work.', 'automatorwp-todoist' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-todoist'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-todoist' ),
                    'https://automatorwp.com/docs/todoist/'
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
            'name' => __( 'Result:', 'automatorwp-todoist' ),
            'type' => 'text',
        );

        return $log_fields;
    }
}
new AutomatorWP_Todoist_Create_New_Task();