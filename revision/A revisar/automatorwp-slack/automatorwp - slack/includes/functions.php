<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Slack\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to get the Slack url
 *
 * @since 1.0.0
 *
 * @return string
 */
function automatorwp_slack_get_url() {

    return 'https://slack.com/api/';

}

/**
 * Helper function to get the Slack API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_slack_get_api() {

    $url = automatorwp_slack_get_url();
    $token = automatorwp_slack_get_option( 'token', '' );

    if( empty( $token ) ) {
        return false;
    }

    return array(
        'url' => $url,
        'token' => $token,
    );

}

/**
 * Get channels from Slack
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_slack_get_channels( ) {

    $api = automatorwp_slack_get_api();
    if( ! $api ) {
        return $options;
    }
    
    $transient = get_transient( 'automatorwp_slack_channels' );
    if( $transient !== false ) {
        return $transient;
    }
    
    $response = wp_remote_get( $api['url'] . 'conversations.list', array(
        'headers' => array(
            'Authorization' => "Bearer ".$api['token'],
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
            )
            ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );
            
    $channels = array();

    foreach ( $response['channels'] as $channel ) {
    
        $channels[] = array(
            'id'    => $channel['id'],
            'name'  => $channel['name'],
        );       

    }

    if( count( $channels ) ) {

        // Set a transient for 10 mins with channels
        set_transient( 'automatorwp_slack_channels', $channels, 10 * 60 );
    }

    return $channels;

}

/**
 * Get channels from Slack
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_slack_options_cb_channel( $field ) {
    
    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any channel', 'automatorwp-slack' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }
    
        foreach( $value as $channel_id ) {

            // Skip option none
            if( $channel_id === $none_value ) {
                continue;
            }

            $options[ $channel_id ] = automatorwp_slack_get_channel_name( $channel_id );
        }
    }

    return $options;

}

/**
* Get the channel name
*
* @since 1.0.0
* 
* @param string $chanel_id  Channel ID
*
* @return array
*/
function automatorwp_slack_get_channel_name( $channel_id ) {
    
    $api = automatorwp_slack_get_api();
    
    if( ! $api ) {
        return $options;
    }
    
    $channel_name = '';

    $channels = automatorwp_slack_get_channels();

    foreach ( $channels as $channel) {
        
        if( $channel['id'] === $channel_id ) {
        
            $channel_name = $channel['name'];
            break;
        }

    }
    
    return $channel_name;
}

/**
* Get the channel Id
*
* @since 1.0.0
* 
* @param string $chanel_name  Channel name
*
* @return array
*/
function automatorwp_slack_get_channel_id( $channel_ref ) {
    
    $api = automatorwp_slack_get_api();
    
    if( ! $api ) {
        return $options;
    }
    
    $channels = null;
    $transient = get_transient( 'automatorwp_slack_channels' );
    
    if ( $transient !== false ) {
        $channels = $transient;
    }

    $channel_id = null;
    $channel_ref = strtolower( $channel_ref );

    if( $channels === null) {

        $response = wp_remote_get( $api['url'] . "conversations.list", array(
            'headers' => array(
                'Authorization' => "Bearer " . $api['token'],
            ),
        ) );

        $response = json_decode( wp_remote_retrieve_body( $response ), true  );

        if ( isset ( $response['ok'] ) === false || $response['ok'] === false || !isset ( $response['channels'] )  ){
        
            return;
        }

        $channels = array();

        foreach ( $response['channels'] as $channel ) {

            $channels[] = array(
                'id'    => $channel['id'],
                'name'  => $channel['name'],
            );       
        }
        
        if( count( $channels ) ) {

            // Set a transient for 10 mins with channels
            set_transient( 'automatorwp_slack_channels', $channels, 10 * 60 );
        }
    }

    
    foreach ( $channels as $channel) {
        
        if( strtolower( $channel['name'] ) === $channel_ref || strtolower( $channel['id'] ) === $channel_ref ) {
            $channel_id = $channel['id'];
            break;
        }
    }
    
    return $channel_id;
}

/**
 * Get users from Slack
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_slack_get_users( $channel_id = '' ) {
        
    $api = automatorwp_slack_get_api();
    
    if( ! $api ) {
        return $options;
    }

    $users = array();

    if( $channel_id === null || $channel_id === false){
        $channel_id = '';
    }

    $transient = get_transient( 'automatorwp_slack_users' . ( $channel_id === '' ? '' : '_'.$channel_id ) );
    
    // If dat found, retrieve data-list ( all users or users in a specific channel )
    if( $transient !== null && $transient !== false ) {
        return $transient;
    }
 
    // If no returned data and searching in specific channel try to get all users in team
    if( $channel_id !== '' ) {
        $transient = get_transient( 'automatorwp_slack_users' );
    } 

    // Send request if no data saved 
    if( $transient !== null || $transient === false ) {
    
        // Default method to get all users
        $api_method = 'users.list';

        $array_request = array(
            'headers' => array(
                'Authorization' => "Bearer ".$api['token'],
                'Accept' => 'application/json',
                'Content-Type'  => 'application/json'
            )
        );

        $response_listado = wp_remote_get( $api['url'] . $api_method, $array_request );
        $response_listado = json_decode( wp_remote_retrieve_body( $response_listado ), true );
        
        if( $response_listado['ok'] === false ) {
            return array('ok' => false, 'error' => $response_listado['error'] );
        }

        $users_list_complete = array();

        foreach( $response_listado['members'] as $user ) {
      
            $users_list_complete[] = array (
                'name' => isset( $user['real_name'] ) ? $user['real_name'] : ( isset( $user['profile']['real_name'] ) ? $user['profile']['real_name'] : '' ),
                'id'   => $user['id'],
                'email'=> isset( $user['profile']['email'] ) ? $user['profile']['email'] : ''
            );
           
        }
     
        // Save result array
        if ( count( $users_list_complete ) ) {
            set_transient('automatorwp_slack_users', $users_list_complete, 10 * 60 );
        }

        // If no channel given the search was a full list
        if($channel_id === '') {
            return $users_list_complete;
        }
    
    }else {
        // Retrieve users list
        $users_list_complete = $transient;
    }

    // Method to get users ID in channel
    $api_method = 'conversations.members';

    // Needed in request to get users in channel
    $array_request['body'] = array( 'channel' => $channel_id );

    // Get all users in channel (only returns : id)
    $response_channel = wp_remote_get( $api['url'] . $api_method, $array_request );
    $response_channel = json_decode( wp_remote_retrieve_body( $response_channel ), true  );
     
    if( $response_channel['ok'] === false ) {
        return array(
            'ok' => false,
            'error' => $response_channel['error']
        );
    }

    
    // Parsing and adding only users in specific channel
    foreach ( $users_list_complete as $user ) {
    
        // Method 'conversations.members' only return ID's not names or emails,
        // Only the full list of users have this information
        if( in_array( $user['id'], $response_channel['members']) ) {

            $users[] = array(
                'id'    => $user['id'],
                'name'  => $user['real_name'],
                'email' => $user['email']
            );
        }
    }      
    
    // Save specific channel users
    if ( count( $users ) ) {
        set_transient('automatorwp_slack_users_' . $channel_id, $users, 10 * 60 );
    }

    return $users;
}

/**
 * Get users from Slack
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_slack_options_cb_user( $field ) {
    
    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any user', 'automatorwp-slack' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    $channel_id = ct_get_object_meta( $field->object_id, 'channel', true );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }
    
        foreach( $value as $user_id ) {

            // Skip option none
            if( $user_id === $none_value ) {
                continue;
            }

            $options[$user_id] = automatorwp_slack_get_user_name( $user_id );
        }
    }

    return $options;

}

/**
* Get the user name
*
* @since 1.0.0
* 
* @param string $user_id
*
* @return array
*/
function automatorwp_slack_get_user_name( $user_id ) {
      
    $api = automatorwp_slack_get_api();

    if( ! $api ) {
        return $options;
    }

    $user_name = '';
    $users_list = automatorwp_slack_get_users();
    
    foreach( $users_list as $member){
        if ( $member['id'] === $user_id ) {
            $user_name = $member['name'];
            break;
        }
    }

    return $user_name;
}

/**
* Get the user name
*
* @since 1.0.0
* 
* @param string $user
*
* @return array
*/
function automatorwp_slack_get_user_id( $user_data ) {
    
    $api = automatorwp_slack_get_api();
  
    if( ! $api ) {
        return $options;
    }
    
    $user_id = '';
    $user_data = strtolower( $user_data );

    $transient = get_transient( 'automatorwp_slack_users' );
    
    if ( $transient === false ) {
       
        $response = wp_remote_get( $api['url'] . "users.list", array(
            'headers' => array(
                'Authorization' => "Bearer " . $api['token'],
            ),
        ) );
    
        $response = json_decode( wp_remote_retrieve_body( $response ), true  );
      
        if ( isset ( $response['ok'] ) === false || $response['ok'] === false || !isset ( $response['members'] ) ) {
               return;
        }

        $transient = $response['members'];
    }    

    foreach( $transient as $member){
   
        if ( isset( $member['id'] ) && strtolower( $member['id'] ) === strtolower( $user_data )
        || isset( $member['name'] ) && strtolower( $member['name'] ) === strtolower( $user_data )
        || isset ( $member['email'] ) && strtolower( $member['email'] ) === strtolower( $user_data ) ) {
            
            $user_id = $member['id'];
            break;
        }
    }

    return $user_id;
}

/**
 * Get threads from slack channels
 *
 * @since 1.0.0
 * 
 * @param string $channel_id
 * 
 * @return array
 */
function automatorwp_slack_get_threads( $channel_id ) {
    
    $api = automatorwp_slack_get_api();
    
    if( ! $api ) {
        return $options;
    }
    
    $transient = get_transient( 'automatorwp_slack_threads_' . $channel_id );
    if( $transient !== false ) {
        return $transient;
    }    

    $threads = array();

    $response = wp_remote_get( $api['url'] . 'conversations.history', array(
        'headers' => array(
            'Authorization' => "Bearer ".$api['token'],
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        ),
        'body'  => ( array(
            'channel' => $channel_id,
        ) ),
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );
    
    foreach ( $response['messages'] as $thread ) {

        // Ignore if it's not a user message beacuse Slack API doesn't allow you to reply to them
        if( ! isset($thread['subtype'] ) ) {
    
            $threads[] = array(
                'id'  => $thread['ts'],
                'text'=> $thread['text'],
            );
        }
    }
 
    set_transient('automatorwp_slack_threads_' . $channel_id, $threads, 10 * 60 );
    return $threads;

}

/**
 * Get channels from Slack
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_slack_options_cb_thread( $field ) {
    
    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any team', 'automatorwp-slack' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    $channel_id = ct_get_object_meta( $field->object_id, 'channel', true );

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }
        
        foreach( $value as $thread_id ) {
            
            // Skip option none
            if( $thread_id === $none_value ) {
                continue;
            }

            $options[$thread_id] = automatorwp_slack_get_thread_name( $thread_id, $channel_id );
        }
    }
    
    return $options;

}

/**
 * Get thread name from Slack channel
 *
 * @since 1.0.0
 * 
 * @param string $thread_id
 * 
 * @param string $channel_id
 *
 * @return array
 */
function automatorwp_slack_get_thread_name ( $thread_id, $channel_id ) {
        
    $api = automatorwp_slack_get_api();
    
    if( ! $api ) {
        return $options;
    }

    // Get Threads
    $threads = automatorwp_slack_get_threads( $channel_id );

    $thread_name = '';
    
    foreach ( $threads as $thread ) {
    
        if ( $thread_id === $thread['id'] ) {
            $thread_name = $thread['text'];
            break;
        }
          
    }

    return $thread_name;
}

/**
 * Get reactions from Slack
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_slack_get_reactions( ) {

    $api = automatorwp_slack_get_api();
    if( ! $api ) {
        return $options;
    }
    
    $transient = get_transient( 'automatorwp_slack_reactions' );
    // Return reactions if exists
    if( $transient !== false ) {
      return $transient;
    }

    // List of allowed emojis in Slack
    $response_slack = wp_remote_get( $api['url'] . 'emoji.list?include_categories=true', array(
        'headers' => array(
            'Authorization' => "Bearer ".$api['token'],
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
            )
            ) );

    $response_slack = json_decode( wp_remote_retrieve_body( $response_slack ), true  );
        
    // Source of emoji with relation between unicode and name ( our emojis database )
    $response_emoji = wp_remote_get( 'https://www.emojidex.com/api/v1/utf_emoji', array(
        'headers' => array(
            'Authorization' => "Bearer ".$api['token'],
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
            )
            ) );
            
    $response_emoji = json_decode( wp_remote_retrieve_body( $response_emoji ), true  );
    
    $array_emoji = array();
    
    // Add all emoji data to temporal array
    foreach ( $response_emoji as $emoji ) {
        
        // Parse format to obtain correct emoji unicode
        $emoji_unicode = $emoji['unicode'];
        $char_pos_unicode = strpos($emoji_unicode,'-');
        if($char_pos_unicode !== false){
            $emoji_unicode = substr($emoji_unicode,0,$char_pos_unicode);
        }

        // Parse format on emoji name
        $emoji_name = $emoji['code'];
        $char_pos_name = strpos($emoji_name,'(');
        if($char_pos_name !== false){
            $emoji_name = substr($emoji_name,0,$char_pos_name);
        }
        
        // Convert emoji data so the Slack API understands it ( works with the name not the unicode )
        $array_emoji[ str_replace( ' ','_', $emoji[ 'code' ] ) ] = array(
                    'id' => str_replace( ' ', '_', $emoji_name ),
                    'name' => '&#x' . $emoji_unicode . ';'
                    ); 
    }

    // Matching search
    $reactions = array();

    // All categories in slack
    foreach ( $response_slack['categories'] as $emoji_categories ) {
                
        // All emojis in categories
        foreach ($emoji_categories['emoji_names'] as $emoji_slack )    {
                    
            // If emojis-list contains actual emoji from slack api, add it
            if( array_key_exists( $emoji_slack, $array_emoji ) ) {
                $reactions[$emoji_slack] = $array_emoji[$emoji_slack];
            }
        }
    }
      
    if( count( $reactions ) ) {
      // Set a transient for 10 mins with the reactions
        set_transient( 'automatorwp_slack_reactions', $reactions, 10 * 60 );
    }
  
    return $reactions;

}


/**
 * Get reactions from Slack
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_slack_options_cb_reaction( $field ) {
 
    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any channel', 'automatorwp-slack' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }
    
        foreach( $value as $reaction_id ) {

            // Skip option none
            if( $reaction_id === $none_value ) {
                continue;
            }

            $options[$reaction_id] = automatorwp_slack_get_reaction_name( $reaction_id );
        }
    }

    return $options;

}

/**
* Get the reaction name ( the inner text )
*
* @since 1.0.0
* 
* @param string $reaction_id  Reaction ID
*
* @return array
*/
function automatorwp_slack_get_reaction_name( $reaction_id ) {
   
    $api = automatorwp_slack_get_api();
    
    if( ! $api ) {
        return $options;
    }
    
    $reaction_name = null;
    $reactions = automatorwp_slack_get_reactions();
    
    // Matching search
    foreach ( $reactions as $reaction) {
        
        if($reaction['id'] === $reaction_id){
        
            $reaction_name = $reaction['name'];
            break;
        }
    }
   
    return $reaction_name;
}

/**
 * Add comment to a channel
 *
 * @since 1.0.0
 * 
 * @param string    $channel_id        Channel ID
 * @param string    $comment_text   Comment text
 * 
 * @return array
 */

 function automatorwp_slack_add_comment( $channel_id, $comment_text ) {
     
    $api = automatorwp_slack_get_api();
    
    if( ! $api ) {
        return;
    }
    
    $response = wp_remote_post( $api['url'] . 'chat.postMessage', array(
        'headers' => array(
            'Authorization' => "Bearer ". $api['token'],
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode( array(
            'channel'  => $channel_id, 
            'text'     => $comment_text
            ) )
            ) );
            
    //Read state from $body['ok'] instead $response['response']['code']
    //['code'] is comunication, not operation success
    $body = json_decode( $response['body'], true );

    return automatorwp_slack_parse_response( $body );
}


/**
 * Add comment to thread
 *
 * @since 1.0.0
 * 
 * @param double    $channel_id     Channel ID
 * @param string    $comment_text   Comment text
 * @param string    $thread_ts      Thread ID
 * @param string    $reaction       
 * 
 * @return array
 */
 function automatorwp_slack_add_comment_thread( $channel_id, $comment_text, $thread_ts , $reaction = false ) {
    
    $api = automatorwp_slack_get_api();

    if( ! $api ) {
        return;
    }
    
    // If there is no data, replace with dummy values ( needed for Slack API )
    if( $channel_id === null || $channel_id === false || $channel_id === '' ) {
        $channel_id = 'C000'; 
    }
    if( $thread_ts === null || $thread_ts === false || $thread_ts  === '' ) { 
        $thread_ts = '000'; 
    }

    // default API method
    $method = 'chat.postMessage';

    // common header for messages and reactions
    $request_array = array(
        'headers' => array(
            'Authorization' => "Bearer ". $api['token'],
            'Accept' => 'application/json',
            'Content-Type'  => 'application/json'
        ),
    );

    // body for messages or reactions
    if( ! $reaction ) {
        
        // messages
        $request_array['body'] = json_encode( array(
            'text'      => $comment_text,
            'channel'   => $channel_id,
            'thread_ts' => $thread_ts,
            ) );

    } else {

        // change method and body for reactions
        $method = 'reactions.add';

        $request_array['body'] = json_encode( array(
            'name'      => strtolower( $comment_text ),
            'channel'   => $channel_id,
            'timestamp' => $thread_ts,

        ) );
    }

    $response = wp_remote_post( $api['url'] . $method, $request_array );

    //Read state from $body['ok'] instead $response['response']['code']
    //['code'] is comunication, not operation success
    $body = json_decode( $response['body'], true );
   
    return automatorwp_slack_parse_response( $body );
}

/**
 * Add user to channel
 *
 * @since 1.0.0
 *
 * @param string $channel_id   Channel ID
 * @param string $user_id      User ID
 * 
 * @return array
 */
function automatorwp_slack_add_user_to_channel( $channel_id, $user ) {

    $api = automatorwp_slack_get_api();

    if( ! $api ) {
        return $options;
    }

    // If there is no data, replace with dummy values ( needed for Slack API )
    if( $channel_id === null || $channel_id === false || $channel_id === '' ) {
        $channel_id = 'C0000';
    }
    if( $user === null || $user === false || $user  === '' ) {
        $user = 'C0000';
        $is_user = true;
    }

    $user_id = $user;
    // Retrieve user email
    if( is_email( $user_id ) ) {
       
        $user_id = automatorwp_slack_verify_user( $user_id );

        // When user email not found
        if ( isset( $user_id['error'] ) ) 
        return automatorwp_slack_parse_response( $user_id );
        
        // Email match user
        else
        $user_id = $user_id['user_id'];
       
    }
    
    $response = wp_remote_get( $api['url'] . 'conversations.invite', array(
        'headers' => array(
            'Authorization' => "Bearer " . $api['token'],
        ),
        'body' =>  array(
            'channel'   => $channel_id,
            'users'     => $user_id,
            )
        ) );
        
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    // Slack API doesn't differentiate between user and channel in returned errors
    if( $is_user ) {
        $response['error'] = str_replace( ['Channel','channel'], ['User','user'],$response['error'] );
    }

    return automatorwp_slack_parse_response( $response );

}

/**
 * Remove user from channel
 *
 * @since 1.0.0
 *
 * @param string $channel_id    Channel ID
 * @param string $user_id       User ID
 * 
 * @return array
 */
function automatorwp_slack_remove_user_from_channel( $channel_id, $user_id ) {
    
    $api = automatorwp_slack_get_api();

    if( ! $api ) {
        return $options;
    }

    // If there is no data, replace with dummy values ( needed for Slack API )
    if( $channel_id === null || $channel_id === false || $channel_id === '' ) {
        $channel_id = 'C0000';
    }
    
    $is_user = false;
    if( $user_id === null || $user_id === false || $user_id  === '' ) {
        $user_id = 'C0000';
        $is_user = true;
    }

    $response = wp_remote_get( $api['url'] . 'conversations.kick', array(
        'headers' => array(
            'Authorization' => "Bearer " . $api['token'],
        ),
        'body' =>  array(
            'channel'   => $channel_id,
            'user'     => $user_id,
            )
        ) );
        
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );
    
    // Slack API doesn't differentiate between user and channel in returned errors
    if( $is_user ) {
        $response['error'] = str_replace( ['Channel','channel'], ['User','user'], $response['error'] );
    } 

    return automatorwp_slack_parse_response( $response );
}

/**
 * Add new channel
 *
 * @since 1.0.0
 *
 * @param string $channel_name  Name of the new channel
 *
 * @return array
 */
function automatorwp_slack_add_channel( $channel_name ) {
   
    $api = automatorwp_slack_get_api();

    if( ! $api ) {
        return $options;
    }

    $response = wp_remote_get( $api['url'] . 'conversations.create', array(
        'headers' => array(
            'Authorization' => "Bearer " . $api['token'],
        ),
        'body' =>  array(
            'name'   => strtolower ($channel_name),
            )
        ) );
        
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );
                
    return automatorwp_slack_parse_response( $response );

}


/**
 * Check if the user is added to Slack
 *
 * @since 1.0.0
 *
 * @param string $user_email  The user's email
 * 
 * @return array
 */
function automatorwp_slack_verify_user( $user_email ) {
    
    $api = automatorwp_slack_get_api();
    
    if( ! $api ) {
        return $options;
    }
    
    $user_email = sanitize_email( $user_email );
    
    $response = wp_remote_get( $api['url'] . 'users.lookupByEmail', array(
        'headers' => array(
            'Authorization' => "Bearer " . $api['token'],
        ),
        'body' =>  array(
            'email' => $user_email,
            )
        ) );
        
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );
           
    $array_response = array();

    // Parse boolean to code number
    $array_response['ok'] = $response['ok'] ? 200 : 400;

    // If isn't ok, append reason and return.
    if ( $array_response['ok']  === false  ) {
        $array_response['error'] = $response['error'];
        return $array_response;
    }
    
    //returns user's name and id
    $array_response['user_id'] = $response['user']['id'];
    $array_response['user_name'] = $response['user']['name'];
    
    return $array_response;

}

/**
 * Converts the response to display in log
 *
 * @since 1.0.0
 *
 * @param string $response  The response of the request
 * 
 * @return array
 */
function automatorwp_slack_parse_response( $response ){
    
    if ( $response['ok'] === true ) {
        return array( 'ok' => 200 );
    }

    $error_text = str_replace( '_', ' ', $response['error'] );
    $error_text = ' - ' . ucfirst( $error_text );

    return array( 'ok' => 400, 'error' => $error_text );
}


/**
 * Add the data that causes the failure
 *
 * @since 1.0.0
 *
 * @param string $response  The response of the request
 * 
 * @return array
 */
function automatorwp_slack_add_error_info( $text, $parameter, $value_user, $value_post_custom = null){
        
        $error_text = $text;
        
        // deprecated
        if( $value_post_custom === null ) $value_post_custom = $value_user;
        
        if( ! is_array( $value_user ) && ! is_array( $value_post_custom ) ) {
        
            // If contains mention to parameter add the correct given value
            if( str_contains( strtolower( $text ), $parameter ) ) {
                $error_text .= ' "' . ( $value_user === 'custom'? $value_post_custom : $value_user ) . '" ';
            }

        }else{
        
            if( str_contains( strtolower( $text ), 'user' ) || $parameter === 'user' ) { 
                $error_text .= ' "' . ( $value_user[0] === 'custom' ? $value_post_custom[0] : $value_user[0] ) . '" ';
            
            }else if ( str_contains( strtolower( $text ), 'channel') || $parameter === 'channel' ) {
                $error_text .= ' "' . ( $value_user[1] === 'custom' ? $value_post_custom[1] : $value_user[1] ) . '" ';
            }
        }

    return $error_text;
}




/////////////////////////////////////////
// Not tested needs enterprise account //
/////////////////////////////////////////
/**
 * Remove channel
 *
 * @since 1.0.0
 *
 * @param string $channel_name  Name fo the channel
 * 
 * @return array
 */
/*
function automatorwp_slack_remove_channel( $channel_name ) {
    
    $api = automatorwp_slack_get_api();

    if( ! $api ) {
        return $options;
    }

    $response = wp_remote_get( $api['url'] . '-write scope-', array(
        'headers' => array(
            'Authorization' => "Bearer " . $api['token'],
        ),
        'body' =>  array(
            'channel'   => $channel_name,
            )
        ) );
        
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );
                
    return $response['ok'] ? 200 : 400;

}



/**
 * Invite user to channel
 *
 * @since 1.0.0
 *
 * @param string $channel_id  The channel id
 * @param string $user     The user email
 *
 * @return array
 */
/*
function automatorwp_slack_invite_user_to_channel( $channel_id, $user_email ) {
   
    $api = automatorwp_slack_get_api();

    if( ! $api ) {
        return $options;
    }

    $verification_user = automatorwp_slack_verify_user( $user_email );
    
    if( $verification_user['ok'] === 200 ){
        
        $user_id = $verification_user['user_id'];

        $response = wp_remote_get( $api['url'] . 'auth.signin', array(
            'headers' => array(
                'Authorization' => "Bearer " . $api['token'],
            ),
            'body' =>  array(
               // 'channel'   => $channel_id,
                'users'     => $user_id,
                'team'     => 'T06RJ48D4G1'
                )
            ) );
            
        $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    } else {
        // If user's email not found return error
      
        return array( 400 );
    }

    return automatorwp_slack_parse_response( $response );

}


* Get text from message
*
* @since 1.0.0
* 
* @param string $user_id
*
* @return array

function automatorwp_slack_get_message_text_name( $user_id ) {

    $api = automatorwp_slack_get_api();
    
    if( ! $api ) {
        return $options;
    }

    $response = wp_remote_get( $api['url'] . "users.list", array(
        'headers' => array(
            'Authorization' => "Bearer " . $api['token'],
        ),
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );
   
    if ( isset ( $response['ok'] ) === false || $response['ok'] === false || !isset ( $response['members'] )  ){
   
        return;
    }

    foreach( $response['members'] as $members){
        if ( $members['id'] === $user_id ) {
            $user_name = $members['real_name'];
        }
    }
    
    return $user_name;
}


*/

