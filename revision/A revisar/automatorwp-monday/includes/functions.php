<?php

/**
 * Helper function to get the Monday API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_monday_get_api()
{

    $consumer_key = automatorwp_monday_get_option('consumer_key', '');
    $url = 'https://api.monday.com/v2';

    if (empty($consumer_key)) {
        return false;
    }

    return array(
        'consumer_key' => $consumer_key,
        'url' => $url
    );
}

function automatorwp_monday_check_settings_status($credentials)
{

    $return = false;

    $body = 'query {
        users {
            id
            name
            email
        }
      }';

    $response = wp_remote_post('https://api.monday.com/v2', array(
        'body' => json_encode(array('query' => $body)),
        'headers' => array(
            'Authorization' => 'Bearer ' . $credentials['consumer_key'],
            'Content-Type' => 'application/json',
        )
    ));

    $status_code = wp_remote_retrieve_response_code($response);

    if (200 !== $status_code) {
        wp_send_json_error(array('message' => __('Please, check your API credentials', 'automatorwp-monday')));
        return $return;
    } else {
        $return = true;
    }

    return $return;
}

/**
 * Get boards from Monday
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_monday_get_boards()
{
    $boards = array();

    $api = automatorwp_monday_get_api();
    if (!$api) {
        return $boards;
    }

    $body = 'query {
        boards {
            id
            name
        }
      }';

    $response = wp_remote_post($api['url'], array(
        'body' => json_encode(array('query' => $body)),
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['consumer_key'],
            'Content-Type' => 'application/json',
        )
    ));

    if (is_wp_error($response)) {
        return $boards;
    }

    $response = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response['data']['boards'])) {
        $boards = $response['data']['boards'];
    }

    return $boards;
}

/**
 * Get items in board from Monday
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_monday_get_items($board_id)
{
    $items = array();

    $api = automatorwp_monday_get_api();
    if (!$api) {
        return $items;
    }

    $body = 'query {  
        boards(ids: [' . $board_id . ']) {
            items_page {  
                items {  
                    id  
                    name  
                }  
            }  
        }  
    }';

    $response = wp_remote_post($api['url'], array(
        'body' => json_encode(array('query' => $body)),
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['consumer_key'],
            'Content-Type' => 'application/json',
        )
    ));

    if (is_wp_error($response)) {
        return $items;
    }

    $response = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response['data']['boards'][0]['items_page']['items'])) {
        $items = $response['data']['boards'][0]['items_page']['items'];
    }

    return $items;
}

/**
 * Get users from Monday
 * 
 * @since 1.0.0
 * 
 * @return array
 */
function automatorwp_monday_get_users()
{
    $users = array();

    $api = automatorwp_monday_get_api();
    if (!$api) {
        return $users;
    }

    $body = 'query {
        users {
            id
            name
        }
    }';

    $response = wp_remote_post($api['url'], array(
        'body' => json_encode(array('query' => $body)),
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['consumer_key'],
            'Content-Type' => 'application/json',
        )
    ));

    if (is_wp_error($response)) {
        return $users;
    }

    $response = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response['data']['users'])) {
        $users = $response['data']['users'];
    }

    return $users;
}

/**
 * Get project status from Monday
 *
 * @since 1.0.0
 * 
 * @return array
 */
function automatorwp_monday_get_project_status($board_id, $item_id)
{
    $project_status = array();

    $api = automatorwp_monday_get_api();
    if (!$api) {
        return $project_status;
    }

    $body = 'query {
        boards(ids: [' . $board_id . ']) {
            columns(ids: ["project_status"]) {
                id
                settings_str
            }
        }
    }';

    $response = wp_remote_post($api['url'], array(
        'body' => json_encode(array('query' => $body)),
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['consumer_key'],
            'Content-Type' => 'application/json',
        )
    ));

    if (is_wp_error($response)) {
        return $project_status;
    }

    $response = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response['data']['boards'][0]['columns'])) {
        $columns = $response['data']['boards'][0]['columns'];

        foreach ($columns as $column) {
            if ($column['title'] === 'Project Status') {
                $project_status = $column;
                break;
            }
        }
    }

    return $project_status;

    /**
     * Get boards from Monday
     *
     * @since 1.0.0
     * 
     * @param stdClass $field
     *
     * @return array
     */
    function automatorwp_monday_options_cb_board($field)
    {

        // Setup vars
        $value = $field->escaped_value;
        $none_value = 'any';
        $none_label = __('any board', 'automatorwp-monday');
        $options = automatorwp_options_cb_none_option($field, $none_value, $none_label);

        if (!empty($value)) {
            if (!is_array($value)) {
                $value = array($value);
            }

            foreach ($value as $board_id) {

                // Skip option none
                if ($board_id === $none_value) {
                    continue;
                }

                $board_name = automatorwp_monday_get_board_name($board_id);
                $options[$board_id] = $board_name . ' (ID: ' . $board_id . ')';
            }
        }

        return $options;
    }

    /**
     * Get items from Monday
     *
     * @since 1.0.0
     * 
     * @param stdClass $field
     *
     * @return array
     */
    function automatorwp_monday_options_cb_item($field)
    {

        // Setup vars
        $value = $field->escaped_value;
        $none_value = 'any';
        $none_label = __('any item', 'automatorwp-monday');
        $options = automatorwp_options_cb_none_option($field, $none_value, $none_label);

        if (!empty($value)) {
            if (!is_array($value)) {
                $value = array($value);
            }

            foreach ($value as $item_id) {

                // Skip option none
                if ($item_id === $none_value) {
                    continue;
                }

                $item_name = automatorwp_monday_get_item_name($item_id);
                $options[$item_id] = $item_name . ' (ID: ' . $item_id . ')';
            }
        }

        return $options;
    }

    /**
     * Get users from Monday
     *
     * @since 1.0.0
     * 
     * @param stdClass $field
     *
     * @return array
     */
    function automatorwp_monday_options_cb_user($field)
    {

        // Setup vars
        $value = $field->escaped_value;
        $none_value = 'any';
        $none_label = __('any user', 'automatorwp-monday');
        $options = automatorwp_options_cb_none_option($field, $none_value, $none_label);

        if (!empty($value)) {
            if (!is_array($value)) {
                $value = array($value);
            }

            foreach ($value as $user_id) {

                // Skip option none
                if ($user_id === $none_value) {
                    continue;
                }

                $user_name = automatorwp_monday_get_user_name($user_id);
                $options[$user_id] = $user_name . ' (ID: ' . $user_id . ')';
            }
        }

        return $options;
    }

    /**
     * Get board name
     *
     * @since 1.0.0
     *
     * @return String
     */
    function automatorwp_monday_get_board_name($board_id)
    {
        $api = automatorwp_monday_get_api();
        if (!$api) {
            return '';
        }

        $body = 'query {
        boards(ids: ' . $board_id . ') {
            name
        }
      }';

        $response = wp_remote_post($api['url'], array(
            'body' => json_encode(array('query' => $body)),
            'headers' => array(
                'Authorization' => 'Bearer ' . $api['consumer_key'],
                'Content-Type' => 'application/json',
            )
        ));

        if (is_wp_error($response)) {
            return '';
        }

        $response = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($response['data']['boards'][0]['name'])) {
            return $response['data']['boards'][0]['name'];
        }

        return '';
    }

    /**
     * Get item name
     *
     * @since 1.0.0
     *
     * @return String
     */
    function automatorwp_monday_get_item_name($item_id)
    {
        $api = automatorwp_monday_get_api();
        if (!$api) {
            return '';
        }

        $body = 'query {
        items(ids: ' . $item_id . ') {
            name
        }
    }';

        $response = wp_remote_post($api['url'], array(
            'body' => json_encode(array('query' => $body)),
            'headers' => array(
                'Authorization' => 'Bearer ' . $api['consumer_key'],
                'Content-Type' => 'application/json',
            )
        ));

        if (is_wp_error($response)) {
            return '';
        }

        $response = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($response['data']['items'][0]['name'])) {
            return $response['data']['items'][0]['name'];
        }

        return '';
    }

    /**
     * Get user name
     *
     * @since 1.0.0
     *
     * @return String
     */
    function automatorwp_monday_get_user_name($user_id)
    {
        $api = automatorwp_monday_get_api();
        if (!$api) {
            return '';
        }

        $body = 'query {
        users(ids: ' . $user_id . ') {
            name
        }
    }';

        $response = wp_remote_post($api['url'], array(
            'body' => json_encode(array('query' => $body)),
            'headers' => array(
                'Authorization' => 'Bearer ' . $api['consumer_key'],
                'Content-Type' => 'application/json',
            )
        ));

        if (is_wp_error($response)) {
            return '';
        }

        $response = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($response['data']['users'][0]['name'])) {
            return $response['data']['users'][0]['name'];
        }

        return '';
    }

    /**
     * Change board name
     *
     * @since 1.0.0
     *
     * @return int
     */
    function automatorwp_monday_change_board_name($board_id, $new_name)
    {
        $api = automatorwp_monday_get_api();
        if (!$api) {
            return false;
        }

        error_log($board_id);
        error_log($new_name);
        $body = 'mutation {
        update_board(
            board_id: ' . $board_id . ',
            board_attribute: name
            new_value: "' . $new_name . '"
        )
    }';

        $response = wp_remote_post($api['url'], array(
            'body' => json_encode(array('query' => $body)),
            'headers' => array(
                'Authorization' => 'Bearer ' . $api['consumer_key'],
                'Content-Type' => 'application/json',
            )
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);

        return $response_code;
    }

    /**
     * Change board name
     *
     * @since 1.0.0
     *
     * @return int
     */
    function automatorwp_monday_change_item_name($board_id, $item_id, $new_name)
    {
        $api = automatorwp_monday_get_api();
        if (!$api) {
            return false;
        }

        $body = 'mutation {
        change_simple_column_value(
            board_id: ' . $board_id . ',
            item_id: ' . $item_id . ',
            column_id: "name",
            value: "' . $new_name . '"
        ) {
            id
        }
    }';

        $response = wp_remote_post($api['url'], array(
            'body' => json_encode(array('query' => $body)),
            'headers' => array(
                'Authorization' => 'Bearer ' . $api['consumer_key'],
                'Content-Type' => 'application/json',
            )
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);

        return $response_code;
    }

    /**
     * Change item project owner
     * 
     * @since 1.0.0
     * 
     * @return int
     */
    function automatorwp_monday_change_item_project_owner($board_id, $item_id, $project_owner)
    {
        $api = automatorwp_monday_get_api();
        if (!$api) {
            return false;
        }

        $body = 'mutation {
        change_column_value(
            board_id: ' . $board_id . ',
            item_id: ' . $item_id . ',
            column_id: "project_owner",
            value: "{ \"personsAndTeams\": [{ \"id\": ' . $project_owner . ', \"kind\": \"person\" }] }"
        ) {
            id
        }
    }';

        $response = wp_remote_post($api['url'], array(
            'body' => json_encode(array('query' => $body)),
            'headers' => array(
                'Authorization' => 'Bearer ' . $api['consumer_key'],
                'Content-Type' => 'application/json',
            )
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);

        return $response_code;
    }

}