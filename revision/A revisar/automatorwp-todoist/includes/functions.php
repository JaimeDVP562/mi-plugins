<?php

/**
 * Helper function to get the Todoist API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_todoist_get_api() {

    $consumer_key = automatorwp_todoist_get_option( 'consumer_key', '' );
    $url = 'https://api.todoist.com/rest/v2/';

    if( empty( $consumer_key ) ) {
        return false;
    }

    return array(
        'consumer_key' => $consumer_key,
        'url' => $url
    );
}

function automatorwp_todoist_check_settings_status( $credentials ) {

    $return = false;

    $response = wp_remote_get( 'https://api.todoist.com/rest/v2/projects', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $credentials['consumer_key'],
        )
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( 200 !== $status_code ) {
        wp_send_json_error( array( 'message' => __( 'Please, check your API credentials', 'automatorwp-todoist' )) );
        return $return;
    } else {
        $return = true;
    }

    return $return;
}

/**
* Get projects from Todoist
*
* @since 1.0.0
*
* @return array
*/
function automatorwp_todoist_get_projects() {

    $projects = array();

    $api = automatorwp_todoist_get_api();
    if ( ! $api ) {
        return $projects;
    }

    $response = wp_remote_get( $api['url'] . 'projects', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['consumer_key'],
        ),
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    foreach ( $response as $sequence ){
        $sequences[] = array(
            'id' => $sequence['id'],
            'name' => $sequence['name']
        );
    }

    return $sequences;
}

/**
 * Get projects from Todoist
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_todoist_options_cb_projects( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any project', 'automatorwp-todoist' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $project_id ) {

            // Skip option none
            if( $project_id === $none_value ) {
                continue;
            }
            
            $options[$project_id] = automatorwp_todoist_get_project_name( $project_id );

        }

    }

    return $options;
}

/**
 * Get tasks from Todoist
 *
 * @since 1.0.0
 *
 * @return array - The tasks from Todoist
 */
function automatorwp_todoist_get_tasks() {

    $tasks = array();

    $api = automatorwp_todoist_get_api();
    if ( ! $api ) {
        return $tasks;
    }

    $response = wp_remote_get( $api['url'] . 'tasks', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['consumer_key'],
        ),
    ) );
    
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    
    foreach( $response as $list ) {

        $lists[] = array(
            'id' => $list['id'],
            'name' => $list['content']
        );
    }

    return $lists;

}

/**
 * Get tasks from Todoist
 *
 * @since 1.0.0
 * 
 * @param stdClass $field - The field object
 *
 * @return array - The options for the tasks
 */
function automatorwp_todoist_options_cb_tasks( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any task', 'automatorwp-todoist' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $task_id ) {
            // Skip option none
            if( $task_id == $none_value ) {
                continue;
            }

            $options[$task_id] = automatorwp_todoist_get_task_name( $task_id );
        }

    }
    return $options;
}

/**
 * Create a new project in Todoist
 * @param string $project_name - The name of the project to be created
 * 
 * @return int - The status code of the response
 */
function automatorwp_todoist_create_project( $project_name ) {

    $api = automatorwp_todoist_get_api();

    if( empty( $api ) ) {
        return false;
    }

    $args = array(
        'body'    => json_encode( array(
            'name' => $project_name,
        ) ),
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api['consumer_key'],
        ),
        'timeout' => 20,
    );

    $response = wp_remote_post( $api['url'] . 'projects', $args );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
}

/**
 * Create a new task in Todoist
 * @param string $task_name - The name of the task to be created
 * @param int $project_id - The ID of the project to which the task belongs
 * 
 * @return int - The status code of the response
 */
function automatorwp_todoist_create_task( $task_name, $project_id ) {

    $api = automatorwp_todoist_get_api();

    error_log($project_id, 3, "project_id.log");

    if( empty( $api ) ) {
        return false;
    }

    $args = array(
        'body'    => json_encode( array(
            'content' => $task_name,
            'project_id' => $project_id,
        ) ),
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api['consumer_key'],
        ),
        'timeout' => 20,
    );

    $response = wp_remote_post( $api['url'] . 'tasks', $args );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
}

/**
 * Close a task in Todoist
 * @param int $task_id - The ID of the task to be closed
 * 
 * @return int - The status code of the response
 */
function automatorwp_todoist_close_task( $task_id ) {

    $api = automatorwp_todoist_get_api();

    if( empty( $api ) ) {
        return false;
    }

    $args = array(
        'body'    => '',
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['consumer_key'],
        ),
        'timeout' => 20,
    );

    $response = wp_remote_post( $api['url'] . 'tasks/' . $task_id . '/close', $args );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
}

/**
 * Get project name from Todoist
 * @param int $project_id - The ID of the project to be retrieved
 * 
 * @return string - The name of the project
 */
function automatorwp_todoist_get_project_name ( $project_id ) {

    $api = automatorwp_todoist_get_api();

    if( empty( $api ) ) {
        return false;
    }

    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['consumer_key'],
        ),
        'timeout' => 20,
    );

    $response = wp_remote_get( $api['url'] . 'projects/' . $project_id, $args );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    $project_name = $response['name'];

    return $project_name;
}

/**
 * Get task name from Todoist 
 * @param int $task_id - The ID of the task to be retrieved
 * 
 * @return string - The name of the task
 */
function automatorwp_todoist_get_task_name ( $task_id ) {

    $api = automatorwp_todoist_get_api();

    if( empty( $api ) ) {
        return false;
    }

    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['consumer_key'],
        ),
        'timeout' => 20,
    );

    $response = wp_remote_get( $api['url'] . 'tasks/' . $task_id, $args );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    $project_name = $response['content'];

    return $project_name;
}