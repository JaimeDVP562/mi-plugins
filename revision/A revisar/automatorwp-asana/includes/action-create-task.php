<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register action
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
 * Execute action
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

    $response = wp_remote_post('https://app.asana.com/api/1.0/tasks', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
        ),
        'body' => json_encode($body),
    ));

    return $response;
}
add_action('automatorwp_action_execute', function($action, $user_id, $args) {
    if ($action['integration'] === 'asana') {
        automatorwp_asana_execute_create_task($action, $user_id, $args);
    }
}, 10, 3);