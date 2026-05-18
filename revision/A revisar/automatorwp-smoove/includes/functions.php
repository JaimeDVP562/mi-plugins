<?php

/**
 * Helper function to get the Smoove API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_smoove_get_api() {
   
    $api_key = automatorwp_smoove_get_option( 'api_key', '' );
    $url = 'https://rest.smoove.io/v1/';

    if (empty($api_key)) {
        error_log('Error: No se encontró la API Key en la base de datos.');
        return false;
    }

    return array(
        'api_key' => $api_key,
        'url' => $url
    );
}
function automatorwp_smoove_check_settings_status($credentials) {
    $return = false;

    // Realiza la solicitud GET a la API de Smoove
    $response = wp_remote_get('https://rest.smoove.io/v1/Account/ContactFields', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $credentials['api_key'] // Corregido el header
        )
    ));

    // Obtiene el código de estado HTTP
    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response); // Obtiene la respuesta completa para depuración

    // Registrar errores en el log de WordPress
    error_log('Smoove API Status Code: ' . $status_code);
    error_log('Smoove API Response: ' . print_r($body, true));

    // Verifica si la respuesta no es 200
    if (200 !== $status_code) {
        wp_send_json_error(array('message' => __('Please, check your API credentials', 'automatorwp-smoove')));
        return $return;
    }

    return true;
}


/**
* Get boards from Smoove
*
* @since 1.0.0
*
* @return array
*/
function automatorwp_smoove_get_boards() {

    $boards = array();

    $api = automatorwp_smoove_get_api();
    if ( ! $api ) {
        return $boards;
    }

    $response = wp_remote_get( $api['url'] . '/members/me/boards', array(
        'body' => array(
            'key' => $api['api_key'],

            
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
 * Get boards from Smoove
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_smoove_options_cb_board( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any board', 'automatorwp-smoove' );
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
            
            $options[$board_id] = automatorwp_smoove_get_board_name( $board_id );

        }

    }

    return $options;
}

/**
 * Get labels from Smoove
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_smoove_options_cb_label( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any label', 'automatorwp-smoove' );
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
            
            $options[$label_id] = automatorwp_smoove_get_label_name( $label_id );

        }

    }

    return $options;
}

/**
 * Get cards from Smoove
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_smoove_options_cb_card( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any card', 'automatorwp-smoove' );
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

            $options[$card_id] = automatorwp_smoove_get_card_name( $card_id );

        }

    }

    return $options;
}

/**
 * Get checklist from Smoove
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_smoove_options_cb_checklist( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any checklist', 'automatorwp-smoove' );
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

            $options[$checklist_id] = automatorwp_smoove_get_checklist_name( $checklist_id );

        }

    }

    return $options;

}

/**
 * Get lists from Smoove
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_smoove_options_cb_list( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any list', 'automatorwp-smoove' );
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

            $options[$list_id] = automatorwp_smoove_get_list_name( $list_id );
        }

    }
    return $options;
}

/**
 * Get member from Smoove
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_smoove_options_cb_member( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any member', 'automatorwp-smoove' );
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

            $options[$member_id] = automatorwp_smoove_get_member_name( $member_id );
        }

    }
    return $options;
}

/**
 * Options callback for select all boards of Smoove
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_smoove_get_all_boards_options_cb(  ) {

    $boards = automatorwp_smoove_get_boards();

    $results = [];

    foreach( $boards as $board ) {

        $results[$board['id']] = $board['name'];

    }

    return $results;

}

/**
 * Get lists from board in Smoove
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_smoove_get_lists_from_board( $board_id ) {

    $lists = array();

    if( empty( $board_id ) ) {
        return $lists;
    }

    $api = automatorwp_smoove_get_api();
    if ( ! $api ) {
        return $lists;
    }

    $response = wp_remote_get( $api['url'] . '/boards/' . $board_id . '/lists', array(
        'body' => array(
            'key' => $api['api_key'],

            
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
function automatorwp_smoove_get_labels_from_board( $board_id ) {

    $labels = array();

    if( empty( $board_id ) ) {
        return $labels;
    }    

    $api = automatorwp_smoove_get_api();
    if ( ! $api ) {
        return $labels;
    }

    $response = wp_remote_get( $api['url'] . '/boards/' . $board_id . '/labels', array(
        'body' => array(
            'key' => $api['api_key'],

            
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
 * Get cards from list in Smoove
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_smoove_get_cards_from_list( $list_id ) {

    $cards = array();

    if( empty( $list_id ) ) {
        return $cards;
    }

    $api = automatorwp_smoove_get_api();
    if ( ! $api ) {
        return $lsists;
    }

    $response = wp_remote_get( $api['url'] . '/lists/' . $list_id . '/cards', array(
        'body' => array(
            'key' => $api['api_key'],

           
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
 * Get members from board in Smoove
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_smoove_get_members_from_board( $board_id ) {

    $members = array();

    if( empty( $board_id ) ) {
        return $members;
    }

    $api = automatorwp_smoove_get_api();

    if ( ! $api ) {
        return $members;
    }

    $response = wp_remote_get( $api['url'] . '/boards/' . $board_id . '/members', array(
        'body' => array(
            'key' => $api['consumer_key'],
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
 * Get cehcklists from card in Smoove
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_smoove_get_checklists_from_card( $card_id ) {

    $checklists = array();

    if( empty( $card_id ) ) {
        return $checklists;
    }

    $api = automatorwp_smoove_get_api();

    if ( ! $api ) {
        return $members;
    }
    
    $response = wp_remote_get( $api['url'] . '/cards/' . $card_id . '/checklists', array(
        'body' => array(
            'key' => $api['consumer_key'],
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
function automatorwp_smoove_get_board_name( $board_id ) {

    // Empty title if no ID provided
    if( empty( $board_id ) ) {
        return '';
    }

    $api = automatorwp_smoove_get_api();
    if ( ! $api ) {
        return $boards;
    }

    $response = wp_remote_get( $api['url'] . '/boards/' . $board_id, array(
        'body' => array(
            'key' => $api['api_key'],

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
function automatorwp_smoove_get_label_name( $label_id ) {


    // Empty title if no ID provided
    if( empty( $label_id ) ) {
        return '';
    }

    $api = automatorwp_smoove_get_api();
    if ( ! $api ) {
        return $labels;
    }

    $response = wp_remote_get( $api['url'] . '/labels/' . $label_id, array(
        'body' => array(
            'key' => $api['api_key'],

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
function automatorwp_smoove_get_card_name( $card_id ) {

    // Empty title if no ID provided
    if( empty( $card_id ) ) {
        return '';
    }

    $api = automatorwp_smoove_get_api();
    if ( ! $api ) {
        return $cards;
    }

    $response = wp_remote_get( $api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'key' => $api['api_key'],

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
function automatorwp_smoove_get_checklist_name( $checklist_id ) {

    // Empty title if no ID provided
    if( empty( $checklist_id ) ) {
        return '';
    }

    $api = automatorwp_smoove_get_api();
    if ( ! $api ) {
        return $cards;
    }

    $response = wp_remote_get( $api['url'] . '/checklists/' . $checklist_id, array(
        'body' => array(
            'key' => $api['consumer_key'],
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
function automatorwp_smoove_get_list_name( $list_id ) {

    // Empty title if no ID provided
    if( absint( $list_id ) === 0 ) {
        return '';
    }

    $api = automatorwp_smoove_get_api();
    if ( ! $api ) {
        return $boards;
    }

    $response = wp_remote_get($api['url'] . '/lists/' . $list_id, array(
        'body' => array(
            'key' => $api['consumer_key'],
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
function automatorwp_smoove_get_member_name( $member_id ) {

    // Empty title if no ID provided
    if( empty( $member_id ) === 0 ) {
        return '';
    }

    $api = automatorwp_smoove_get_api();
    if ( ! $api ) {
        return $boards;
    }

    $response = wp_remote_get($api['url'] . '/members/' . $member_id, array(
        'body' => array(
            'key' => $api['consumer_key'],
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response['fullName'] . ' (' . $response['username'] . ')';
    
}

/**
 * Add contact 
 *
 * @since 1.0.0
 * 
 * @param string    $card_name      Card name
 * @param string    $list_id        List id
 * 
 * @return int
 */
function automatorwp_smoove_create_contact( $contact_data ) {

    $api = automatorwp_smoove_get_api();

    if( ! $api ) {
        return;
    }

    $url = 'https://rest.smoove.io/v1/Contacts?updateIfExists=false&restoreIfDeleted=false&restoreIfUnsubscribed=false&forceSync=false&overrideNullableValue=false';

    $response = wp_remote_post( $url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api['api_key'],
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode( array(
            'contact_request' => $contact_data
        )),
        'method' => 'POST'
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );
    
    return $response['contact_request'];
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
function automatorwp_smoove_comment_card( $card_id, $new_comment ) {

    $api = automatorwp_smoove_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards/' . $card_id . '/actions/comments', array(
        'body' => array(
            'text'=> $new_comment,
            'key' => $api['api_key'],

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
function automatorwp_smoove_add_label( $card_id, $label_id ) {

    $api = automatorwp_smoove_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards/' . $card_id . '/idLabels', array(
        'body' => array(
            'value'=> $label_id,
            'key' => $api['api_key'],

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
function automatorwp_smoove_add_member( $card_id, $member_id ) {

    $api = automatorwp_smoove_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/cards/' . $card_id . '/idMembers', array(
        'body' => array(
            'value'=> $member_id,
            'key' => $api['api_key'],

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
function automatorwp_smoove_add_checklist_item( $checklist_id, $checklist_item ) {

    $api = automatorwp_smoove_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/checklists/' . $checklist_id . '/checkItems', array(
        'body' => array(
            'name'=> $checklist_item,
            'key' => $api['api_key'],

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
function automatorwp_smoove_change_card_list( $card_id, $new_list_id ) {

    $api = automatorwp_smoove_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request( $api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'idList' => $new_list_id,
            'key' => $api['api_key'],

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
function automatorwp_smoove_change_card_desc( $card_id, $new_desc ) {

    $api = automatorwp_smoove_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request( $api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'desc' => $new_desc,
            'key' => $api['api_key'],

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
function automatorwp_smoove_archive_card( $card_id ) {

    $api = automatorwp_smoove_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request($api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'closed' => true,
            'key' => $api['api_key'],

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
function automatorwp_smoove_delete_card( $card_id ) {

    $api = automatorwp_smoove_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request($api['url'] . '/cards/' . $card_id, array(
        'body' => array(
            'key' => $api['api_key'],

    
        ),
        'method' => 'DELETE'
    ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;
}
/**
 * Get LandingPage
 *
 * @since 1.0.0
 * 
 * 
 * 
 * @return int
 */
function automatorwp_smoove_get_landing_pages() {
    // Obtener la clave API desde las opciones
    $api_key = automatorwp_smoove_get_option('api_key');
    
    // Verificar si la clave API está configurada
    if (empty($api_key)) {
        return new WP_Error('no_api_key', __('API Key is missing in AutomatorWP settings', 'automatorwp-smoove'));
    }

    // Realizar la solicitud a la API de Smoove
    $response = wp_remote_get('https://rest.smoove.io/v1/LandingPages?type=ALL', array(
        'headers' => array(
            'api_token' => 'Bearer ' . $api_key
        )
    ));

    // Obtener el código de respuesta
    $status_code = wp_remote_retrieve_response_code($response);
    
    // Manejar errores de la API
    if ($status_code !== 200) {
        return new WP_Error('api_error', __('Failed to fetch landing pages from Smoove', 'automatorwp-smoove'));
    }

    // Obtener el cuerpo de la respuesta
    $body = wp_remote_retrieve_body($response);
    $landing_pages = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', __('Invalid JSON response from Smoove API', 'automatorwp-smoove'));
    }
    
    return $landing_pages;
}
function automatorwp_smoove_get_lists() {
    $api_key = automatorwp_smoove_get_option('api_key');
    
    if (!$api_key) {
        return new WP_Error('missing_api_key', __('API key is missing.', 'automatorwp-smoove'));
    }

    $response = wp_remote_get('https://rest.smoove.io/v1/Lists?fields=id%2Cname&page=1&itemsPerPage=100&sort=name&includeContactsCount=false', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json'
        )
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    if ($status_code !== 200) {
        return new WP_Error('api_error', __('Failed to retrieve lists from Smoove.', 'automatorwp-smoove'), array('status_code' => $status_code, 'response' => $body));
    }

    return json_decode($body, true);
}
/**
 * Get Async Contact Status from Smoove
 *
 * @param string $operation_id The operation ID to check the status
 * @return mixed The API response or false on failure
 */
function automatorwp_smoove_get_async_contact_status( $operation_id ) {
    // Get API Key
    $api_key = automatorwp_smoove_get_option( 'api_key' );
    if ( empty( $api_key ) ) {
        return false;
    }

    // API Endpoint
    $url = "https://rest.smoove.io/v1/async/contacts/{$operation_id}/status";

    // Set up request arguments
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ),
        'method'  => 'GET',
        'timeout' => 30,
    );

    // Make the request
    $response = wp_remote_get( $url, $args );

    // Check for errors
    if ( is_wp_error( $response ) ) {
        return false;
    }

    // Decode and return the response body
    $body = wp_remote_retrieve_body( $response );
    return json_decode( $body, true );
}