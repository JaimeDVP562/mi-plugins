<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Klaviyo\Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper function to check Klaviyo API secret (Private Key Authentication)
 *
 * @since 1.0.0
 * 
 * @param string $secret Klaviyo API secret (Private API key)
 *
 * @return bool
 */
function automatorwp_klaviyo_check_api_secret($secret) {
    $url = 'https://a.klaviyo.com/api/accounts';
    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Klaviyo-API-Key ' . $secret,
            'accept' => 'application/json',
            'revision' => '2024-02-15',
        ],
    ]);

    if (is_wp_error($response)) {
        return false;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    return $response_code === 200;
}

/**
 * Helper function to get Klaviyo API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_klaviyo_get_api() {
    $key = automatorwp_klaviyo_get_option('key', '');
    $secret = automatorwp_klaviyo_get_option('secret', '');
    $url = 'https://a.klaviyo.com/api';

    if (empty($key) || empty($secret)) {
        return false;
    }

    return [
        'key' => $key,
        'secret' => $secret,
        'url' => $url,
    ];
}

/**
 * Create a profile in Klaviyo
 *
 * @since 1.0.0
 *
 * @param array $profile_data The profile data to create
 * @param string $secret The Klaviyo API secret key
 * @return array|null The created profile from Klaviyo or null on failure
 */
function automatorwp_klaviyo_create_profile($profile_data, $secret) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://a.klaviyo.com/api/profiles/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'data' => [
                'type' => 'profile',
                'attributes' => $profile_data
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            "Authorization: Klaviyo-API-Key $secret",
            "accept: application/json",
            "content-type: application/json",
            "revision: 2024-05-15"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    return $err ? null : json_decode($response, true);
}

/**
 * Get all profiles from Klaviyo
 *
 * @since 1.0.0
 *
 * @param string $secret The Klaviyo API secret key
 * @return array|null The profiles retrieved from Klaviyo or null on failure
 */
function automatorwp_klaviyo_get_profiles($secret) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://a.klaviyo.com/api/profiles/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: Klaviyo-API-Key $secret",
            "accept: application/json",
            "revision: 2024-05-15"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    return $err ? null : json_decode($response, true);
}

/**
 * Get a specific profile from Klaviyo by its ID
 *
 * @since 1.0.0
 *
 * @param string $profile_id The ID of the Klaviyo profile
 * @param string $secret The Klaviyo API secret key
 * @return array|null The profile retrieved from Klaviyo or null on failure
 */
function automatorwp_klaviyo_get_profile_by_id($profile_id, $secret) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://a.klaviyo.com/api/profiles/" . $profile_id . "/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: Klaviyo-API-Key $secret",
            "accept: application/json",
            "revision: 2024-05-15"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    return $err ? null : json_decode($response, true);
}

/**
 * Add a user to Klaviyo using Klaviyo API
 *
 * @since 1.0.0
 *
 * @param array $user_data Array containing user data for Klaviyo
 * @return string|false    Returns success message or false on failure
 */
function automatorwp_klaviyo_add_user($user_data) {
    $api = automatorwp_klaviyo_get_api();

    if (!$api) {
        return false;
    }

    $secret = $api['secret'];

    if (!automatorwp_klaviyo_check_api_secret($secret)) {
        return false;
    }

    $profile_data = [
        'email'      => sanitize_email($user_data['email']),
        'first_name' => sanitize_text_field($user_data['first_name']),
        'last_name'  => sanitize_text_field($user_data['last_name']),
    ];

    if (empty($profile_data['email'])) {
        return false;
    }

    $response = add_profile_to_klaviyo($profile_data, $secret);

    if ($response !== false) {
        if (isset($response['code'])) {
            if ($response['code'] === 201) {
                return sprintf(__('User %s added to Klaviyo', 'automatorwp-klaviyo'), $profile_data['email']);
            } elseif ($response['code'] === 204) {
                return sprintf(__('User %s updated in Klaviyo', 'automatorwp-klaviyo'), $profile_data['email']);
            } else {
                return __('The user could not be added to Klaviyo', 'automatorwp-klaviyo');
            }
        } else {
            return __('Invalid response from Klaviyo API', 'automatorwp-klaviyo');
        }
    } else {
        return __('Error adding user to Klaviyo', 'automatorwp-klaviyo');
    }
}

/**
 * Create a list in Klaviyo
 *
 * @param string $list_name
 * @param string|null $folder_id
 * @param string $secret
 * @return int
 */
function automatorwp_klaviyo_create_list($list_name, $folder_id, $secret) {
    $curl = curl_init();

    $payload = [
        'data' => [
            'type' => 'list',
            'attributes' => [
                'name' => $list_name
            ]
        ]
    ];

    if ($folder_id) {
        $payload['data']['attributes']['folder_id'] = intval($folder_id);
    }

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://a.klaviyo.com/api/lists/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authorization: Klaviyo-API-Key $secret",
            "content-type: application/json",
            "revision: 2024-05-15"
        ],
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    $response_body = curl_multi_getcontent($curl);

    curl_close($curl);

    return $http_code;
}

/**
 * Get all lists from Klaviyo
 *
 * @since 1.0.0
 *
 * @param string $secret The Klaviyo API secret key
 * @return array|null The lists retrieved from Klaviyo or null on failure
 */
function automatorwp_klaviyo_get_lists($secret) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://a.klaviyo.com/api/lists/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: Klaviyo-API-Key $secret",
            "accept: application/json",
            "revision: 2024-05-15"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    return $err ? null : json_decode($response, true);
}

/**
 * Get a specific list from Klaviyo by its ID
 *
 * @since 1.0.0
 *
 * @param string $list_id The ID of the Klaviyo list
 * @param string $secret The Klaviyo API secret key
 * @return array|null The list retrieved from Klaviyo or null on failure
 */
function automatorwp_klaviyo_get_list_by_id($list_id, $secret) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://a.klaviyo.com/api/lists/" . $list_id . "/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: Klaviyo-API-Key $secret",
            "accept: application/json",
            "revision: 2024-05-15"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    return $err ? null : json_decode($response, true);
}

/**
 * Add a profile to a specific Klaviyo list
 *
 * @since 1.0.0
 *
 * @param string $list_id The ID of the Klaviyo list
 * @param string $profile_id The ID of the profile to add to the list
 * @param string $secret The Klaviyo API secret key
 * @return array|null The response from the API or null on failure
 */
function automatorwp_klaviyo_add_profile_to_list($list_id, $profile_id, $secret) {
    $curl = curl_init();

    $data = [
        'data' => [
            [
                'type' => 'profile',
                'id' => $profile_id
            ]
        ]
    ];

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://a.klaviyo.com/api/lists/$list_id/relationships/profiles/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            "Authorization: Klaviyo-API-Key $secret",
            "accept: application/json",
            "content-type: application/json",
            "revision: 2024-05-15"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return null;
    } else {
        $decoded_response = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        return $decoded_response;
    }
}

/**
 * Get profiles from a specific Klaviyo list
 *
 * @since 1.0.0
 *
 * @param string $list_id The ID of the Klaviyo list
 * @param string $secret The Klaviyo API secret key
 * @param int $page_size (Optional) The number of profiles to fetch per page (default: 20)
 * @return array|null The profiles retrieved from the list or null on failure
 */
function automatorwp_klaviyo_get_list_profiles($list_id, $secret, $page_size = 20) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://a.klaviyo.com/api/lists/$list_id/profiles/?page[size]=$page_size",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: Klaviyo-API-Key $secret",
            "accept: application/json",
            "revision: 2024-05-15"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return null;
    } else {
        $decoded_response = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        return $decoded_response;
    }
}

/**
 * Remove a profile from a specific Klaviyo list
 *
 * @since 1.0.0
 *
 * @param string $email The email of the profile to remove
 * @param string $list_id The ID of the Klaviyo list
 * @param string $secret The Klaviyo API secret key
 * @return array|WP_Error The response from the Klaviyo API or an error
 */
function remove_profile_from_klaviyo_list($email, $list_id, $secret) {
    $url = "https://a.klaviyo.com/api/lists/$list_id/relationships/profiles/";

    $body = json_encode([
        'data' => [
            [
                'type' => 'profile',
                'email' => $email
            ]
        ]
    ]);

    $response = wp_remote_request($url, [
        'method'    => 'DELETE',
        'body'      => $body,
        'headers'   => [
            'Authorization' => 'Klaviyo-API-Key ' . $secret,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'revision'      => '2024-05-15'
        ]
    ]);

    // Log the full response for debugging
    error_log('AutomatorWP Klaviyo: API response: ' . json_encode($response));

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);
    $decoded_body = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_decode_error', 'Failed to decode JSON response');
    }

    return $decoded_body;
}
