<?php

/**
 * Helper function to get the Ontraport API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_ontraport_get_api() {
    $app_id = automatorwp_ontraport_get_option( 'app_id', '' ); // APP_ID
    $api_key = automatorwp_ontraport_get_option( 'api_key', '' ); // API_KEY
    $url = 'https://api.ontraport.com/1/'; // URL base de OntraPort

    if ( empty( $app_id ) || empty( $api_key ) ) {
        return false;
    }

    return array(
        'app_id'  => $app_id,
        'api_key' => $api_key,
        'url'     => $url,
    );
}

function automatorwp_ontraport_check_settings_status( $credentials ) {
    $return = false;

    $response = wp_remote_get('https://api.ontraport.com/1/Contacts', array(
        'headers' => array(
            'Api-Appid' => $credentials['app_id'],
            'Api-Key'   => $credentials['api_key'],
        ),
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( 200 !== $status_code ) {
        wp_send_json_error( array( 'message' => __( 'Please, check your API credentials', 'automatorwp-ontraport' ) ) );
        return $return;
    } else {
        $return = true;
    }

    return $return;
}

/**
* Get boards from Ontraport
*
* @since 1.0.0
*
* @return array
*/
function automatorwp_ontraport_get_boards() {

    $boards = array();

    $api = automatorwp_ontraport_get_api();
    if ( ! $api ) {
        return $boards;
    }

    $response = wp_remote_get( $api['url'] . '/members/me/boards', array(
        'body' => array(
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
 * Get boards from Ontraport
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_ontraport_options_cb_board( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any board', 'automatorwp-ontraport' );
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
            
            $options[$board_id] = automatorwp_ontraport_get_board_name( $board_id );

        }

    }

    return $options;
}

/**
 * Get labels from Ontraport
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_ontraport_options_cb_label( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any label', 'automatorwp-ontraport' );
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
            
            $options[$label_id] = automatorwp_ontraport_get_label_name( $label_id );

        }

    }

    return $options;
}

/**
 * Get cards from Ontraport
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_ontraport_options_cb_card( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any card', 'automatorwp-ontraport' );
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

            $options[$card_id] = automatorwp_ontraport_get_card_name( $card_id );

        }

    }

    return $options;
}

/**
 * Get checklist from Ontraport
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_ontraport_options_cb_checklist( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any checklist', 'automatorwp-ontraport' );
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

            $options[$checklist_id] = automatorwp_ontraport_get_checklist_name( $checklist_id );

        }

    }

    return $options;

}

/**
 * Get lists from Ontraport
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_ontraport_options_cb_list( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any list', 'automatorwp-ontraport' );
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

            $options[$list_id] = automatorwp_ontraport_get_list_name( $list_id );
        }

    }
    return $options;
}

/**
 * Get member from Ontraport
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_ontraport_options_cb_member( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any member', 'automatorwp-ontraport' );
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

            $options[$member_id] = automatorwp_ontraport_get_member_name( $member_id );
        }

    }
    return $options;
}

/**
 * Options callback for select all boards of Ontraport
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_ontraport_get_all_boards_options_cb(  ) {

    $boards = automatorwp_ontraport_get_boards();

    $results = [];

    foreach( $boards as $board ) {

        $results[$board['id']] = $board['name'];

    }

    return $results;

}

/**
 * Get lists from board in Ontraport
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_ontraport_get_lists_from_board( $board_id ) {

    $lists = array();

    if( empty( $board_id ) ) {
        return $lists;
    }

    $api = automatorwp_ontraport_get_api();
    if ( ! $api ) {
        return $lists;
    }

    $response = wp_remote_get( $api['url'] . '/boards/' . $board_id . '/lists', array(
        'body' => array(
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_get_labels_from_board( $board_id ) {

    $labels = array();

    if( empty( $board_id ) ) {
        return $labels;
    }    

    $api = automatorwp_ontraport_get_api();
    if ( ! $api ) {
        return $labels;
    }

    $response = wp_remote_get( $api['url'] . '/boards/' . $board_id . '/labels', array(
        'body' => array(
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
 * Get cards from list in Ontraport
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_ontraport_get_cards_from_list( $list_id ) {

    $cards = array();

    if( empty( $list_id ) ) {
        return $cards;
    }

    $api = automatorwp_ontraport_get_api();
    if ( ! $api ) {
        return $lsists;
    }

    $response = wp_remote_get( $api['url'] . '/lists/' . $list_id . '/cards', array(
        'body' => array(
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
 * Get members from board in Ontraport
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_ontraport_get_members_from_board( $board_id ) {

    $members = array();

    if( empty( $board_id ) ) {
        return $members;
    }

    $api = automatorwp_ontraport_get_api();

    if ( ! $api ) {
        return $members;
    }

    $response = wp_remote_get( $api['url'] . '/boards/' . $board_id . '/members', array(
        'body' => array(
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
 * Get cehcklists from card in Ontraport
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_ontraport_get_checklists_from_card( $card_id ) {

    $checklists = array();

    if( empty( $card_id ) ) {
        return $checklists;
    }

    $api = automatorwp_ontraport_get_api();

    if ( ! $api ) {
        return $members;
    }
    
    $response = wp_remote_get( $api['url'] . '/cards/' . $card_id . '/checklists', array(
        'body' => array(
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_get_board_name( $board_id ) {

    // Empty title if no ID provided
    if( empty( $board_id ) ) {
        return '';
    }

    $api = automatorwp_ontraport_get_api();
    if ( ! $api ) {
        return $boards;
    }

    $response = wp_remote_get( $api['url'] . '/boards/' . $board_id, array(
        'body' => array(
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_get_label_name( $label_id ) {


    // Empty title if no ID provided
    if( empty( $label_id ) ) {
        return '';
    }

    $api = automatorwp_ontraport_get_api();
    if ( ! $api ) {
        return $labels;
    }

    $response = wp_remote_get( $api['url'] . '/labels/' . $label_id, array(
        'body' => array(
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_get_card_name( $card_id ) {

    // Empty title if no ID provided
    if( empty( $card_id ) ) {
        return '';
    }

    $api = automatorwp_ontraport_get_api();
    if ( ! $api ) {
        return $cards;
    }

    $response = wp_remote_get( $api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_get_checklist_name( $checklist_id ) {

    // Empty title if no ID provided
    if( empty( $checklist_id ) ) {
        return '';
    }

    $api = automatorwp_ontraport_get_api();
    if ( ! $api ) {
        return $cards;
    }

    $response = wp_remote_get( $api['url'] . '/checklists/' . $checklist_id, array(
        'body' => array(
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_get_list_name( $list_id ) {

    // Empty title if no ID provided
    if( absint( $list_id ) === 0 ) {
        return '';
    }

    $api = automatorwp_ontraport_get_api();
    if ( ! $api ) {
        return $boards;
    }

    $response = wp_remote_get($api['url'] . '/lists/' . $list_id, array(
        'body' => array(
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_get_member_name( $member_id ) {

    // Empty title if no ID provided
    if( empty( $member_id ) === 0 ) {
        return '';
    }

    $api = automatorwp_ontraport_get_api();
    if ( ! $api ) {
        return $boards;
    }

    $response = wp_remote_get($api['url'] . '/members/' . $member_id, array(
        'body' => array(
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_create_card( $card_name, $list_id, $card_desc ) {

    $api = automatorwp_ontraport_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards', array(
        'body' => array(
            'idList' => $list_id,
            'name' => $card_name,
            'desc' => $card_desc,
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_comment_card( $card_id, $new_comment ) {

    $api = automatorwp_ontraport_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards/' . $card_id . '/actions/comments', array(
        'body' => array(
            'text'=> $new_comment,
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_add_label( $card_id, $label_id ) {

    $api = automatorwp_ontraport_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards/' . $card_id . '/idLabels', array(
        'body' => array(
            'value'=> $label_id,
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_add_member( $card_id, $member_id ) {

    $api = automatorwp_ontraport_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards/' . $card_id . '/idMembers', array(
        'body' => array(
            'value'=> $member_id,
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_add_checklist_item( $checklist_id, $checklist_item ) {

    $api = automatorwp_ontraport_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/checklists/' . $checklist_id . '/checkItems', array(
        'body' => array(
            'name'=> $checklist_item,
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_change_card_list( $card_id, $new_list_id ) {

    $api = automatorwp_ontraport_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request( $api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'idList' => $new_list_id,
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_change_card_desc( $card_id, $new_desc ) {

    $api = automatorwp_ontraport_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request( $api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'desc' => $new_desc,
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
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
function automatorwp_ontraport_archive_contact( $contact_id ) {

    $api = automatorwp_ontraport_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request($api['url'] . 'Contacts/' . $contact_id, array(
        'body' => array(
            'closed' => true,
            'app_id' => $api['app_id'],
            'api_key' => $api['api_key']
        ),
        'method' => 'PUT'
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;

}

/**
 * Delete contact
 *
 * @since 1.0.0
 * 
 * @param string    $contact_id      Contact Id
 * 
 * @return int
 */
function automatorwp_ontraport_delete_contact( $contact_id ) {

    $api = automatorwp_ontraport_get_api();

    if( ! $api ) {
        return;
    }
   // $body =array(
   // 'ids' => (string)$contact_id,
   // );
    error_log($contact_id, 3, "debug.log");

    $response = wp_remote_request( "https://api.ontraport.com/1/Contact?id={$contact_id}", array(
        "method"    => "DELETE",
    'headers' => array(
        'Api-Appid' => $api['app_id'],
        'Api-Key'   => $api['api_key'],
        'Content-Type' => 'application/json'
    ),
   //'body' => json_encode( $body ),
) );

    $status_code = wp_remote_retrieve_response_code( $response );
    return $status_code;
}
/**
 * Add Contact
 *
 * @since 1.0.0
 * 
 * @param string    $first_name     $last_name
 * @param string    $email        $phone
 * 
 * @return int
 */
function automatorwp_ontraport_create_contact( $first_name,$last_name, $email, $phone ) {

    $api = automatorwp_ontraport_get_api();

    if( ! $api ) {
        return;
    }
    $body = array(
            'firstname' => $first_name,
            'lastname' => $last_name,
            'email' => $email,
            'phone' => $phone,
        );

    $response = wp_remote_post( 'https://api.ontraport.com/1/Contacts', array(
        'headers' => array(
            'Api-Appid' => $api['app_id'],
            'Api-Key'   => $api['api_key'],
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode( $body ),
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );
    
    return $status_code;
}


/**
* Get Contacts from Ontraport
*
* @since 1.0.0
*
* @return array
*/
function automatorwp_ontraport_get_contacts() {

    $contacts = array();

    $api = automatorwp_ontraport_get_api();
    if ( ! $api ) {
        return $contacts;
    }

    $response = wp_remote_get( "https://api.ontraport.com/1/Contacts", array(
        'headers' => array(
            'Api-Appid' => $api['app_id'],
            'Api-Key'   => $api['api_key'],
            'Content-Type' => 'application/json'
        ),
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    if(isset($response['data'])){
        $contacts = $response['data'];

    }

    return $contacts;
}
