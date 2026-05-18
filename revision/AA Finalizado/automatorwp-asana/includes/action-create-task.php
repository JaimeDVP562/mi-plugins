<?php
/**
 * Asana Action: Create Task
 *
 * This file handles the registration and execution of the "Create Task" action 
 * for the Asana integration within AutomatorWP.
 *
 * @package AutomatorWP\Asana
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers the "Create task in Asana" action.
 *
 * Defines the label, options, and dynamic labels that will be shown in 
 * the AutomatorWP administration interface.
 *
 * @since 1.0.0
 *
 * @return void
 */
function automatorwp_asana_register_create_task_action() {

    automatorwp_register_action(array(
        'integration' => 'asana',
        'label' => 'Create task in Asana',
        'select_option' => 'Create a task in Asana',
        'edit_label' => 'Create task {{name}} in project {{project}}',
        'log_label' => 'Created task {{name}} in Asana',
        'options' => array(

            'access_token' => array(
                'type' => 'text',
                'label' => 'Asana Access Token',
                'required' => true,
            ),

            'project' => array(
                'type' => 'text',
                'label' => 'Project ID',
                'required' => true,
            ),

            'name' => array(
                'type' => 'text',
                'label' => 'Task Name',
                'required' => true,
            ),

            'description' => array(
                'type' => 'textarea',
                'label' => 'Description',
            ),

            'assignee' => array(
                'type' => 'text',
                'label' => 'Assignee ID',
            ),
        ),
    ));
}
add_action('automatorwp_actions', 'automatorwp_asana_register_create_task_action');

/**
 * Executes the "Create task in Asana" action.
 *
 * This function handles the API request to Asana using wp_remote_post.
 * It prepares the task data and sends it as a JSON payload.
 *
 * @since 1.0.0
 *
 * @param array $action {
 *     The action data and configuration.
 *     @type array $options The selected options for this specific action instance.
 * }
 * @param int   $user_id The ID of the user that triggered the automation.
 * @param array $args    Additional arguments passed by the core trigger.
 *
 * @return WP_Error|array The response from wp_remote_post.
 */
function automatorwp_asana_execute_create_task($action, $user_id, $args) {

    $access_token = $action['options']['access_token'];
    $project      = $action['options']['project'];
    $name         = $action['options']['name'];
    $description  = $action['options']['description'];
    $assignee     = $action['options']['assignee'];

    $body = array(
        'data' => array(
            'name' => $name,
            'notes' => $description,
            'projects' => array($project),
        )
    );

    if (!empty($assignee)) {
        $body['data']['assignee'] = $assignee;
    }

    // Perform the API request to Asana.
    $response = wp_remote_post('https://app.asana.com/api/1.0/tasks', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
        ),
        'body' => json_encode($body),
    ));

    return $response;
}

/**
 * Hook into the universal action execution flow.
 *
 * Identifies if the current integration is 'asana' and calls the 
 * specific execution function.
 *
 * @since 1.0.0
 *
 * @param array $action  The action details.
 * @param int   $user_id The user ID.
 * @param array $args    Contextual arguments.
 */
add_action('automatorwp_action_execute', function($action, $user_id, $args) {
    if ($action['integration'] === 'asana') {
        automatorwp_asana_execute_create_task($action, $user_id, $args);
    }
}, 10, 3);