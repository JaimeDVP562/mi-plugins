<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Selzy\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to get the Selzy url
 *
 * @since 1.0.0
 *
 * @return string
 */
function automatorwp_selzy_get_url() {

    return 'https://api.selzy.com/en/api';

}

/**
 * Helper function to get the Selzy API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_selzy_get_api() {

    $url = automatorwp_selzy_get_url();
    $token = automatorwp_selzy_get_option( 'token', '' );

    if( empty( $token ) ) {
        return false;
    }

    return array(
        'url' => $url,
        'token' => $token,
    );

}

/**
 * Get lists from Selzy
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_selzy_get_lists( ) {

    $lists = array();

    $api = automatorwp_selzy_get_api();

    if( ! $api ) {
        return $options;
    }

    $response = wp_remote_get( $api["url"] . '/getLists?&api_key='.$api["token"], array(
        'headers' => array(
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        )
    ) );
    
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );
    $result = $response["result"];

    foreach ( $result as $list ){

        $lists[] = array(
            'id'    => $list['id'],
            'title'  => $list['title'],
        );
        
    }

    return $lists;

}

/**
 * Get list from Selzy
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_selzy_options_cb_list( $field ) {
    
    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any list', 'automatorwp-pro' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }
    
        foreach( $value as $list_id ) {
            
            // Skip option none
            if( $list_id === $none_value ) {
                continue;
            }

            $options[$list_id] = automatorwp_selzy_get_list_title( $list_id );
        }
    }

    return $options;

}

/**
* Get the list name
*
* @since 1.0.0
* 
* @param string $list_id
*
* @return array
*/
function automatorwp_selzy_get_list_title( $list_id ) {

    if( absint( $list_id ) === 0 ) {
        return '';
    }

    $lists = automatorwp_selzy_get_lists();
   
    $list_title = '';

    foreach( $lists as $list ) {

        if( absint( $list['id'] ) === absint( $list_id ) ) {
            $list_title = $list['title'];
            break;
        }
    }

    return $list_title;

}


/**
 * Get tags from Selzy
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_selzy_get_tags( ) {

    $lists = array();

    $api = automatorwp_selzy_get_api();
    $tags = array();

    if( ! $api ) {
        return $options;
    }

    $response = wp_remote_get( $api["url"] . '/getTags?format=json&api_key='.$api["token"], array(
        'headers' => array(
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        )
    ) );
    
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );
    $result = $response["result"];

    foreach ( $result as $tag ){

        $tags[] = array(
            'id'    => $tag['id'],
            'name'  => $tag['name'],
        );
        
    }

    return $tags;

}

/**
 * Get tag from Selzy
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_selzy_options_cb_tag( $field ) {
    
    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any tag', 'automatorwp-pro' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }
    
        foreach( $value as $tag_id ) {
            
            // Skip option none
            if( $tag_id === $none_value ) {
                continue;
            }

            $options[$tag_id] = automatorwp_selzy_get_tag_name( $tag_id );
        }
    }

    return $options;
}

/**
* Get the tag name
*
* @since 1.0.0
* 
* @param string $tag_id
*
* @return array
*/
function automatorwp_selzy_get_tag_name( $tag_id ) {

    if( absint( $tag_id ) === 0 ) {
        return '';
    }

    $tags = automatorwp_selzy_get_tags();
   
    $tag_name = '';

    foreach( $tags as $tag ) {

        if( absint( $tag['id'] ) === absint( $tag_id ) ) {
            $tag_name = $tag['name'];
            break;
        }
    }

    return $tag_name;

}


/**
 * Add contact to list
 *
 * @since 1.0.0
 * 
 * @param string    $user_email    The user email
 * @param int       $list          The list id
 */
function automatorwp_selzy_add_contact( $user_email, $list ) {

    $api = automatorwp_selzy_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_get( $api["url"] . '/subscribe?format=json&api_key='.$api["token"].'&list_ids='.$list.'&fields[email]='.$user_email.'&double_optin=3', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        )
    ) );
    
    return $response['response']['code'];
}


/**
 * Delete contact from list
 *
 * @since 1.0.0
 * 
 * @param string    $user_email    The user email
 * @param int       $list          The list id
 */
function automatorwp_selzy_del_contact( $user_email, $list ) {

    $api = automatorwp_selzy_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_get( $api["url"] . '/exclude?format=json&api_key='.$api["token"].'&contact='.$user_email.'&contact_type=email&list_ids='.$list, array(
        'headers' => array(
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        )
    ) );
    
    return $response['response']['code'];
}


/**
 * Checks if contact is in a list
 *
 * @since 1.0.0
 * 
 * @param string    $user_email    The user email
 * @param int       $list          The list id
 */
function automatorwp_selzy_check_contact( $user_email, $list ) {

    $api = automatorwp_selzy_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_get( $api["url"] . '/isContactInLists?api_key='.$api["token"].'&email='.$user_email.'&list_ids='.$list.'&condition=and', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        )
    ) );
    
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response["result"];
}

/**
 * Delete tag from selzy
 *
 * @since 1.0.0
 * 
 * @param int       $tag          The tag id
 */
function automatorwp_selzy_del_tag( $tag ) {

    $api = automatorwp_selzy_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_get( $api["url"] . '/deleteTag?format=json&api_key='.$api["token"].'&id='.$tag, array(
        'headers' => array(
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        )
    ) );
    
    return $response['response']['code'];
}

/**
 * Create a list
 *
 * @since 1.0.0
 * 
 * @param string       $title          The list title
 */
function automatorwp_selzy_create_list( $title ) {

    $api = automatorwp_selzy_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_get( $api["url"] . '/createList?format=json&api_key='.$api["token"].'&title='.$title, array(
        'headers' => array(
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        )
    ) );
    
    return $response['response']['code'];
}

/**
 * Delete a list
 *
 * @since 1.0.0
 * 
 * @param string       $list_id          The list id
 */
function automatorwp_selzy_delete_list( $list_id ) {

    $api = automatorwp_selzy_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_get( $api["url"] . '/deleteList?format=json&api_key='.$api["token"].'&list_id='.$list_id, array(
        'headers' => array(
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        )
    ) );
    
    return $response['response']['code'];
}