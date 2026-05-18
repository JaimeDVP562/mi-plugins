<?php
/**
 * Action: Create Task
 *
 * Registers and handles the "Create Task" action for the 7todos integration.
 *
 * @package     AutomatorWP\7todos
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Guard against duplicate class declaration
if ( class_exists( 'AutomatorWP_7todos_Create_Task' ) ) return;

class AutomatorWP_7todos_Create_Task extends AutomatorWP_Integration_Action {

    public $integration = '7todos';
    public $action      = '7todos_create_task';

    /**
     * Register the action and its configuration fields
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create a task', 'automatorwp-7todos' ),
            'select_option' => __( 'Create a <strong>task</strong> in 7todos', 'automatorwp-7todos' ),
            'edit_label'    => sprintf(
                /* translators: 1: Title, 2: Workspace ID, 3: Description, 4: State */
                __( 'Create task %1$s in %2$s. Description: %3$s. State: %4$s', 'automatorwp-7todos' ),
                '{title}', '{workspace_id}', '{description}', '{state}'
            ),
            'log_label'     => sprintf(
                /* translators: 1: Title, 2: Workspace ID, 3: Description, 4: State */
                __( 'Created task %1$s in %2$s. Description: %3$s. State: %4$s', 'automatorwp-7todos' ),
                '{title}', '{workspace_id}', '{description}', '{state}'
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

                'title' => array(
                    'from'   => 'title',
                    'fields' => array(
                        'title' => array(
                            'name' => __( 'Title:', 'automatorwp-7todos' ),
                            'type' => 'text',
                        ),
                    ),
                ),

                'description' => array(
                    'from'   => 'description',
                    'fields' => array(
                        'description' => array(
                            'name' => __( 'Description:', 'automatorwp-7todos' ),
                            'type' => 'textarea',
                        ),
                    ),
                ),

                'state' => array(
                    'from'   => 'state',
                    'fields' => array(
                        'state' => array(
                            'name'    => __( 'Initial State:', 'automatorwp-7todos' ),
                            'type'    => 'select',
                            'options' => array(
                                'todo'     => __( 'To Do',       'automatorwp-7todos' ),
                                'progress' => __( 'In Progress', 'automatorwp-7todos' ),
                                'done'     => __( 'Done',        'automatorwp-7todos' ),
                            ),
                            'default' => 'todo',
                        ),
                    ),
                ),

            ),
        ) );
    }

    /**
     * Execute the Create Task action
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
        $title        = sanitize_text_field( $action_args['title'] );
        $description  = wp_kses_post( $action_args['description'] );
        $state        = sanitize_text_field( $action_args['state'] );

        // Bail early if required fields are missing
        if ( empty( $workspace_id ) || empty( $title ) ) {
            return;
        }

        $body = array(
            'workspaceId' => trim( $workspace_id ),
            'title'       => trim( $title ),
            'description' => $description,
            'state'       => $state,
        );

        automatorwp_7todos_api_request( 'https://www.7todos.com/api/v1/tasks/create', $body );
    }
}

new AutomatorWP_7todos_Create_Task();
