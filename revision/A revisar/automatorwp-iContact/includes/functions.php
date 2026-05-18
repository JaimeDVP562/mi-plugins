<?php

/**
 * Helper function to get the IContact API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_iContact_get_api() {

    $consumer_key = automatorwp_iContact_get_option( 'consumer_key', '' );
    $access_token = automatorwp_iContact_get_option( 'access_token', '' );
    $url = 'https://app.icontact.com/icp/a/1928158/c/577/';

    if( empty( $consumer_key ) || empty( $access_token ) ) {
        return false;
    }

    return array(
        'consumer_key' => $consumer_key,
        'access_token' => $access_token,
        'url' => $url
    );
}

function automatorwp_iContact_check_settings_status( $credentials ) {

    $return = false;
    //var_dump($credentials);
    $response = wp_remote_get( 'https://app.icontact.com/icp/a/1928158/c/577/', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer'.'a501c6a90073dc7b896f1e57d1276d16'
        )
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( 200 !== $status_code ) {
        wp_send_json_error( array( 'message' => __( 'Please, check your API credentials', 'automatorwp-iContact' )) );
        return $return;
    } else {
        $return = true;
    }

    return $return;
}

/**
* Get boards from IContact
*
* @since 1.0.0
*
* @return array
*/
function automatorwp_iContact_get_boards() {

    $boards = array();

    $api = automatorwp_iContact_get_api();
    if ( ! $api ) {
        return $boards;
    }

    $response = wp_remote_get( $api['url'] . '/members/me/boards', array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
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
 * Get boards from IContact
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_iContact_options_cb_board( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any board', 'automatorwp-iContact' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $board_id ) {

            // Skip option none
            if( $board_id === $none_value ) {
                continue;
            }
            
            $options[$board_id] = automatorwp_iContact_get_board_name( $board_id );

        }

    }

    return $options;
}

/**
 * Get labels from IContact
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_iContact_options_cb_label( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any label', 'automatorwp-iContact' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {

        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $label_id ) {

            // Skip option none
            if( $label_id === $none_value ) {
                continue;
            }
            
            $options[$label_id] = automatorwp_iContact_get_label_name( $label_id );

        }

    }

    return $options;
}

/**
 * Get cards from IContact
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_iContact_options_cb_card( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any card', 'automatorwp-iContact' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $card_id ) {
            // Skip option none
            if( $card_id === $none_value ) {
                continue;
            }

            $options[$card_id] = automatorwp_iContact_get_card_name( $card_id );

        }

    }

    return $options;
}

/**
 * Get checklist from IContact
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_iContact_options_cb_checklist( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any checklist', 'automatorwp-iContact' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $checklist_id ) {
            // Skip option none
            if( $checklist_id === $none_value ) {
                continue;
            }

            $options[$checklist_id] = automatorwp_iContact_get_checklist_name( $checklist_id );

        }

    }

    return $options;

}

/**
 * Get lists from IContact
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_iContact_options_cb_list( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any list', 'automatorwp-iContact' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $list_id ) {
            // Skip option none
            if( $list_id == $none_value ) {
                continue;
            }

            $options[$list_id] = automatorwp_iContact_get_list_name( $list_id );
        }

    }
    return $options;
}

/**
 * Get member from IContact
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_iContact_options_cb_member( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any member', 'automatorwp-iContact' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $member_id ) {
            // Skip option none
            if( $member_id == $none_value ) {
                continue;
            }

            $options[$member_id] = automatorwp_iContact_get_member_name( $member_id );
        }

    }
    return $options;
}

/**
 * Options callback for select all boards of IContact
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_iContact_get_all_boards_options_cb(  ) {

    $boards = automatorwp_iContact_get_boards();

    $results = [];

    foreach( $boards as $board ) {

        $results[$board['id']] = $board['name'];

    }

    return $results;

}

/**
 * Get lists from board in IContact
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_iContact_get_lists_from_board( $board_id ) {

    $lists = array();

    if( empty( $board_id ) ) {
        return $lists;
    }

    $api = automatorwp_iContact_get_api();
    if ( ! $api ) {
        return $lists;
    }

    $response = wp_remote_get( $api['url'] . '/boards/' . $board_id . '/lists', array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        ) 
    ) );
    
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    
    foreach( $response as $list ) {

        $lists[] = array(
            'id' => $list['id'],
            'name' => $list['name']
        );
    }

    return $lists;

}

/**
* Get labels from Board
*
* @since 1.0.0
*
* @return array
*/
function automatorwp_iContact_get_labels_from_board( $board_id ) {

    $labels = array();

    if( empty( $board_id ) ) {
        return $labels;
    }    

    $api = automatorwp_iContact_get_api();
    if ( ! $api ) {
        return $labels;
    }

    $response = wp_remote_get( $api['url'] . '/boards/' . $board_id . '/labels', array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );
    
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    foreach ( $response as $sequence ){

        $name = $sequence['name'];

        if ( empty( $sequence['name'] ) ) {
            $name = $sequence['color'];
        }

        $sequences[] = array(
            'id' => $sequence['id'],
            'name' => $name
        );
    }

    return $sequences;

}

/**
 * Get cards from list in IContact
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_iContact_get_cards_from_list( $list_id ) {

    $cards = array();

    if( empty( $list_id ) ) {
        return $cards;
    }

    $api = automatorwp_iContact_get_api();
    if ( ! $api ) {
        return $lsists;
    }

    $response = wp_remote_get( $api['url'] . '/lists/' . $list_id . '/cards', array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );

    $response = json_decode(  wp_remote_retrieve_body( $response ), true );
    
    foreach( $response as $card ) {

        $cards[] = array(
            'id' => $card['id'],
            'name' => $card['name']
        );
    }

    return $cards;
}

/**
 * Get members from board in IContact
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_iContact_get_members_from_board( $board_id ) {

    $members = array();

    if( empty( $board_id ) ) {
        return $members;
    }

    $api = automatorwp_iContact_get_api();

    if ( ! $api ) {
        return $members;
    }

    $response = wp_remote_get( $api['url'] . '/boards/' . $board_id . '/members', array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );

    $response = json_decode(  wp_remote_retrieve_body( $response ), true );

    foreach( $response as $member ) {

        $members[] = array(
            'id' => $member['id'],
            'name' => $member['fullName'] . ' (' . $member['username'] . ')'
        );
    }
    
    return $members;
}

/**
 * Get cehcklists from card in IContact
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_iContact_get_checklists_from_card( $card_id ) {

    $checklists = array();

    if( empty( $card_id ) ) {
        return $checklists;
    }

    $api = automatorwp_iContact_get_api();

    if ( ! $api ) {
        return $members;
    }
    
    $response = wp_remote_get( $api['url'] . '/cards/' . $card_id . '/checklists', array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );

    $response = json_decode(  wp_remote_retrieve_body( $response ), true );

    foreach( $response as $checklist ) {

        $checklists[] = array(
            'id' => $checklist['id'],
            'name' => $checklist['name']
        );
    }

    return $checklists;
}

/**
 * Get board name
 *
 * @since 1.0.0
 *
 * @return String
 */
function automatorwp_iContact_get_board_name( $board_id ) {

    // Empty title if no ID provided
    if( empty( $board_id ) ) {
        return '';
    }

    $api = automatorwp_iContact_get_api();
    if ( ! $api ) {
        return $boards;
    }

    $response = wp_remote_get( $api['url'] . '/boards/' . $board_id, array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response['name'];
}

/**
 * Get label name
 *
 * @since 1.0.0
 *
 * @return String
 */
function automatorwp_iContact_get_label_name( $label_id ) {


    // Empty title if no ID provided
    if( empty( $label_id ) ) {
        return '';
    }

    $api = automatorwp_iContact_get_api();
    if ( ! $api ) {
        return $labels;
    }

    $response = wp_remote_get( $api['url'] . '/labels/' . $label_id, array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    $name = $response['name'];

    if ( empty( $response['name'] ) ) {
        $name = $response['color'];
    }

    return $name;
}

/**
 * Get card name
 *
 * @since 1.0.0
 *
 * @return String
 */
function automatorwp_iContact_get_card_name( $card_id ) {

    // Empty title if no ID provided
    if( empty( $card_id ) ) {
        return '';
    }

    $api = automatorwp_iContact_get_api();
    if ( ! $api ) {
        return $cards;
    }

    $response = wp_remote_get( $api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        ) 
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response['name'];
}

/**
 * Get checklist name
 *
 * @since 1.0.0
 *
 * @return String
 */
function automatorwp_iContact_get_checklist_name( $checklist_id ) {

    // Empty title if no ID provided
    if( empty( $checklist_id ) ) {
        return '';
    }

    $api = automatorwp_iContact_get_api();
    if ( ! $api ) {
        return $cards;
    }

    $response = wp_remote_get( $api['url'] . '/checklists/' . $checklist_id, array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        ) 
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response['name'];
}

/**
 * Get list name
 *
 * @since 1.0.0
 *
 * @return String
 */
function automatorwp_iContact_get_list_name( $list_id ) {

    // Empty title if no ID provided
    if( absint( $list_id ) === 0 ) {
        return '';
    }

    $api = automatorwp_iContact_get_api();
    if ( ! $api ) {
        return $boards;
    }

    $response = wp_remote_get($api['url'] . '/lists/' . $list_id, array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response['name'];

}

/**
 * Get member name
 *
 * @since 1.0.0
 *
 * @return String
 */
function automatorwp_iContact_get_member_name( $member_id ) {

    // Empty title if no ID provided
    if( empty( $member_id ) === 0 ) {
        return '';
    }

    $api = automatorwp_iContact_get_api();
    if ( ! $api ) {
        return $boards;
    }

    $response = wp_remote_get($api['url'] . '/members/' . $member_id, array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response['fullName'] . ' (' . $response['username'] . ')';
    
}

/**
 * Add card to list
 *
 * @since 1.0.0
 * 
 * @param string    $card_name      Card name
 * @param string    $list_id        List id
 * 
 * @return int
 */
function automatorwp_iContact_create_card( $card_name, $list_id, $card_desc ) {

    $api = automatorwp_iContact_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards', array(
        'body' => array(
            'idList' => $list_id,
            'name' => $card_name,
            'desc' => $card_desc,
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
}

/**
 * Add comment in card
 *
 * @since 1.0.0
 * 
 * @param string    $card_name      Card name
 * 
 * @return int
 */
function automatorwp_iContact_comment_card( $card_id, $new_comment ) {

    $api = automatorwp_iContact_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards/' . $card_id . '/actions/comments', array(
        'body' => array(
            'text'=> $new_comment,
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
}

/**
 * Add label in card
 *
 * @since 1.0.0
 * 
 * @param string    $card_name      Card name
 * 
 * @return int
 */
function automatorwp_iContact_add_label( $card_id, $label_id ) {

    $api = automatorwp_iContact_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards/' . $card_id . '/idLabels', array(
        'body' => array(
            'value'=> $label_id,
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;

}

/**
 * Add member in card
 *
 * @since 1.0.0
 * 
 * @param string    $card_name      Card name
 * 
 * @return int
 */
function automatorwp_iContact_add_member( $card_id, $member_id ) {

    $api = automatorwp_iContact_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards/' . $card_id . '/idMembers', array(
        'body' => array(
            'value'=> $member_id,
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
}

/**
 * Add checklist item in checklist
 *
 * @since 1.0.0
 * 
 * @param string    $card_name      Card name
 * 
 * @return int
 */
function automatorwp_iContact_add_checklist_item( $checklist_id, $checklist_item ) {

    $api = automatorwp_iContact_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/checklists/' . $checklist_id . '/checkItems', array(
        'body' => array(
            'name'=> $checklist_item,
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        )
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
    
}

/**
 * Change card list
 *
 * @since 1.0.0
 * 
 * @param string    $card_name      Card name
 * @param string    $list_id        List id
 * 
 * @return int
 */
function automatorwp_iContact_change_card_list( $card_id, $new_list_id ) {

    $api = automatorwp_iContact_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request( $api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'idList' => $new_list_id,
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        ),
        'method' => 'PUT'
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;
}

/**
 * Change card description
 *
 * @since 1.0.0
 * 
 * @param string    $card_id      Card Id
 * 
 * @return int
 */
function automatorwp_iContact_change_card_desc( $card_id, $new_desc ) {

    $api = automatorwp_iContact_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request( $api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'desc' => $new_desc,
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        ),
        'method' => 'PUT'
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;
}

/**
 * Archive card
 *
 * @since 1.0.0
 * 
 * @param string    $card_id      Card Id
 * 
 * @return int
 */
function automatorwp_iContact_archive_card( $card_id ) {

    $api = automatorwp_iContact_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request($api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'closed' => true,
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        ),
        'method' => 'PUT'
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;

}

/**
 * Delete card
 *
 * @since 1.0.0
 * 
 * @param string    $card_id      Card Id
 * 
 * @return int
 */
function automatorwp_iContact_delete_card( $card_id ) {

    $api = automatorwp_iContact_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request($api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'key' => $api['consumer_key'],
            'token' => $api['access_token']
        ),
        'method' => 'DELETE'
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;
}