<?php
/**
 * Action: Update Task
 *
 * Registers and handles the logic to modify an existing task via the 7todos API.
 *
 * @package     AutomatorWP\7todos
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Guard against duplicate class declaration
if ( class_exists( 'AutomatorWP_7todos_Update_Task' ) ) return;

class AutomatorWP_7todos_Update_Task extends AutomatorWP_Integration_Action {

    public $integration = '7todos';
    public $action      = '7todos_update_task';

    /**
     * Register the action and define its configuration schema
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Update a task', 'automatorwp-7todos' ),
            'select_option' => __( 'Update a <strong>task</strong> in 7todos', 'automatorwp-7todos' ),
            'edit_label'    => sprintf(
                /* translators: 1: Task ID, 2: Workspace ID, 3: Title, 4: State */
                __( 'Update task %1$s in %2$s: Title to %3$s (%4$s)', 'automatorwp-7todos' ),
                '{task_id}', '{workspace_id}', '{title}', '{state}'
            ),
            'log_label'     => sprintf(
                /* translators: 1: Task ID, 2: Workspace ID, 3: Title, 4: State */
                __( 'Updated task %1$s in %2$s: Title to %3$s (%4$s)', 'automatorwp-7todos' ),
                '{task_id}', '{workspace_id}', '{title}', '{state}'
            ),
            'options'       => array(

                'workspace_id' => array(
                    'from'   => 'workspace_id',
                    'fields' => array(
                        'workspace_id' => array(
                            'name' => __( 'Workspace ID:', 'automatorwp-7todos' ),
                            'type' => 'text',
                        ),
                    ),
                ),

                'task_id' => array(
                    'from'   => 'task_id',
                    'fields' => array(
                        'task_id' => array(
                            'name' => __( 'Task ID:', 'automatorwp-7todos' ),
                            'type' => 'text',
                        ),
                    ),
                ),

                'title' => array(
                    'from'   => 'title',
                    'fields' => array(
                        'title' => array(
                            'name' => __( 'New Title:', 'automatorwp-7todos' ),
                            'type' => 'text',
                        ),
                    ),
                ),

                'state' => array(
                    'from'   => 'state',
                    'fields' => array(
                        'state' => array(
                            'name'    => __( 'New State:', 'automatorwp-7todos' ),
                            'type'    => 'select',
                            'options' => array(
                                'todo'     => __( 'To Do',       'automatorwp-7todos' ),
                                'progress' => __( 'In Progress', 'automatorwp-7todos' ),
                                'done'     => __( 'Done',        'automatorwp-7todos' ),
                            ),
                        ),
                    ),
                ),

            ),
        ) );
    }

    /**
     * Execute the Update Task action
     *
     * AutomatorWP core already handles default values and meta retrieval
     * before calling execute(), so $action_args values are always populated.
     *
     * @since  1.0.0
     * @param  object $action      The action object.
     * @param  int    $user_id     ID of the user who triggered the automation.
     * @param  array  $action_args Field values from the automation configuration.
     * @param  object $automation  The parent automation object.
     */
    public function execute( $action, $user_id, $action_args, $automation ) {

        $workspace_id = sanitize_text_field( $action_args['workspace_id'] );
        $task_id      = sanitize_text_field( $action_args['task_id'] );
        $title        = sanitize_text_field( $action_args['title'] );
        $state        = sanitize_text_field( $action_args['state'] );

        // Bail early if required identifiers are missing
        if ( empty( $workspace_id ) || empty( $task_id ) ) {
            return;
        }

        $body = array(
            'workspaceId' => trim( $workspace_id ),
            'taskId'      => trim( $task_id ),
            'title'       => trim( $title ),
            'state'       => $state,
            'description' => '',
        );

        automatorwp_7todos_api_request( 'https://www.7todos.com/api/v1/tasks/update', $body );
    }
}

new AutomatorWP_7todos_Update_Task();
