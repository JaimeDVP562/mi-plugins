<?php

function automatorwp_github_get_webhook_url() {

    $webhook_url = get_rest_url( null, 'automatorwp/v1/github' );

    return $webhook_url;
}


/**
 *  Action: Create GitHub repository function
 * 
 * @since 1.0.0
 * @param string $url   Repository API
 * @param string $token GitHub PAT(Personal Access Token)
 * @param string $repo_name repository name
 * @param string $description repository description
 * @param bool   $private true if the repository is private
 */
function automatorwp_github_action_create_repo($repo_name,$description,$private){
    
    // API to get GitHub repositorys
    $url = AUTOMATORWP_GITHUB_API.'/user/repos';
    $token = automatorwp_github_get_option('key','');
    

    // If required options is empty
    if(empty( $token ) || empty( $repo_name )) return false;

    // Available both PAT versions
    $prefix_token = str_starts_with($token,'github_pat_')? 'Bearer ' : 'token ';


    // Body for construct de repository
    $body = array (
        'name'        => $repo_name,
        'description' => $description,
        'private'     => (bool) $private
    );

    // sent the petition to Create the repository
    $response = wp_remote_post($url, array(
        'timeout' => AUTOMATORWP_GITHUB_API_TIMEOUT,
        'headers' => array(
                    'Authorization' => $prefix_token . $token,
                    'Accept'        => 'application/vnd.github+json',
                    'Content-Type'  => 'application/json'
    ),
        'body'      => json_encode( $body ),
        'sslverify' => false
    ) );

    // WordPress ERROR
    if( is_wp_error( $response ) ) {
        
        return array(
            'Success' => __('No',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
            'Error'   => $response->get_error_message()
        );
    }

    // Get respone code
    $code = wp_remote_retrieve_response_code($response);
    
    // Get respone data
    $data = json_decode(wp_remote_retrieve_body($response), true);

    // If success code return: success array
    if ($code === 201){

          $prive = ( $data['private'] === true ) ? 'Private' : 'Public';


        return array(
            'Success'               => __('Yes',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
            'Url'                   =>'<a href='.$data['html_url'].' >'.$data['html_url'].'</a>',
            'Name repository'       => $data['name'],
            'Description'           => $data['description'],
            'Access Repository'     => $prive,
            
        );
    }
    // If not success code return: Not success array
    return array(
        'Success' => __('No',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
        'Status_code'  => $code,
        'Error'   => isset($data['message']) ? $data['message'] : 'Unknown error'
    );

}



/**
 *  Action: Delete GitHub repository function
 * 
 * @since 1.0.0
 * @param string $url   Repository API
 * @param string $token GitHub PAT(Personal Access Token) 
 * @param string $repo_name Repository name 
 */
function automatorwp_github_action_delete_repo($repo_name){
    
    // API to get GitHub repositorys
    $token = automatorwp_github_get_option('key','');
    // Username from GitHub
    $github_username = automatorwp_github_get_option('username','');
    // API to get GitHub repositorys
    $url = AUTOMATORWP_GITHUB_API.'/repos/'.$github_username.'/'.$repo_name;
    
    // If required options is empty
    if(empty( $token ) || empty( $repo_name ) || empty( $github_username ) ) return false;

    // Available both PAT versions
    $prefix_token = str_starts_with($token,'github_pat_')? 'Bearer ' : 'token ';

    // Send the petition to Delete the repository
    $response = wp_remote_request($url, array(
        'method'  => 'DELETE',
        'timeout' => AUTOMATORWP_GITHUB_API_TIMEOUT,
        'headers' => array(
                    'Authorization' => $prefix_token . $token,
                    'Accept'        => 'application/vnd.github+json',
                    'Content-Type'  => 'application/json'
    ),
        'sslverify' => false
    ) );

    // WordPress ERROR
    if( is_wp_error( $response ) ) {
        
        return array(
            'Deleted' => __('No',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
            'Error'   => $response->get_error_message()
        );
    }

    // Get response code
    $code = wp_remote_retrieve_response_code($response);
    // Get response data
    $data = json_decode(wp_remote_retrieve_body($response), true);

    // If success code return: success array
    if ($code === 204){

          $prive = ( $data['private'] === true ) ? 'Private' : 'Public';


        return array(
            'Deleted'  => __('Yes',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
            
            
        );
    }
    // If not success code return: Not success array
    return array(
        'Deleted' => __('No',AUTOMATORWP_GITHUB_TEXT_DOMAIN),
        'Status Code'  => $code,
        'GitHub Error'   => isset($data['message']) ? $data['message'] : 'Unknown error'
    );

}