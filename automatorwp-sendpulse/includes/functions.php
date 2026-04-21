<?php
/**
 * Minimal SendPulse helpers and HTTP wrapper
 * Cleaned from legacy integration helpers.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return integration options
 *
 * @return array
 */
function automatorwp_sendpulse_get_settings() {
    $prefix = 'automatorwp_sendpulse_';
    return array(
        'application_id'     => get_option( $prefix . 'application_id', '' ),
        'application_secret' => get_option( $prefix . 'application_secret', '' ),
        'access_token'       => get_option( $prefix . 'access_token', '' ),
        'access_valid'       => get_option( $prefix . 'access_valid', false ),
        'page_id'            => get_option( $prefix . 'page_id', '' ),
    );
}

/**
 * Basic API info
 *
 * @return array|false
 */
function automatorwp_sendpulse_get_api() {
    $settings = automatorwp_sendpulse_get_settings();
    $prefix = 'automatorwp_sendpulse_';

    if ( empty( $settings['application_id'] ) && empty( $settings['access_token'] ) ) {
        return false;
    }

    return array(
        'application_id'     => $settings['application_id'],
        'application_secret' => $settings['application_secret'],
        'access_token'       => $settings['access_token'],
        'page_id'            => $settings['page_id'],
        'base_url'           => 'https://api.sendpulse.com',
        'access_expires_in'  => get_option( $prefix . 'access_expires_in', 0 ),
        'access_obtained_at' => get_option( $prefix . 'access_token_obtained_at', 0 ),
    );
}

/**
 * Wrapper for SendPulse API requests
 * Returns decoded JSON or WP_Error
 */
function automatorwp_sendpulse_request( $method, $endpoint, $args = array() ) {
    $api = automatorwp_sendpulse_get_api();
    if ( ! $api ) {
        return new WP_Error( 'no_credentials', __( 'SendPulse credentials not configured.', 'automatorwp-sendpulse' ) );
    }

    // Ensure we have a valid access token (may refresh)
    $token = automatorwp_sendpulse_get_access_token();
    if ( is_wp_error( $token ) ) {
        return $token;
    }
    $url = rtrim( $api['base_url'], '/' ) . '/' . ltrim( $endpoint, '/' );

    $defaults = array( 'timeout' => 20 );
    $args = wp_parse_args( $args, $defaults );

    // Prepare headers and include Bearer token. Prefer the freshly-obtained $token
    $headers = isset( $args['headers'] ) ? $args['headers'] : array();
    $auth_token = '';
    if ( ! empty( $token ) && ! is_wp_error( $token ) ) {
        $auth_token = $token;
    } elseif ( ! empty( $api['access_token'] ) ) {
        // Fallback to the value present in the stored API settings
        $auth_token = $api['access_token'];
    }

    if ( ! empty( $auth_token ) ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }
    $headers['Accept'] = isset( $headers['Accept'] ) ? $headers['Accept'] : 'application/json';

    // If a body array is provided, encode as JSON and set content-type
    if ( isset( $args['body'] ) && is_array( $args['body'] ) ) {
        $args['body'] = wp_json_encode( $args['body'] );
        $headers['Content-Type'] = 'application/json';
    }

    $args['headers'] = $headers;

    $method = strtoupper( $method );

    switch ( $method ) {
        case 'GET':
            $response = wp_remote_get( $url, $args );
            break;
        case 'POST':
            $response = wp_remote_post( $url, $args );
            break;
        case 'PUT':
        case 'DELETE':
            $args['method'] = $method;
            $response = wp_remote_request( $url, $args );
            break;
        default:
            return new WP_Error( 'invalid_method', __( 'Invalid HTTP method.', 'automatorwp-sendpulse' ) );
    }

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );

    $data = json_decode( $body, true );

    if ( $code < 200 || $code >= 300 ) {
        $message = isset( $data['error'] ) ? $data['error'] : $body;
        return new WP_Error( 'api_error', $message, array( 'status' => $code ) );
    }

    return $data;
}


/**
 * Ensure we have a valid access token, refreshing with client credentials if expired.
 * Returns token string or WP_Error.
 */
function automatorwp_sendpulse_get_access_token() {
    $prefix = 'automatorwp_sendpulse_';
    $settings = automatorwp_sendpulse_get_settings();

    // If we already have a token and expiry info, check expiration
    $access_token = $settings['access_token'];
    $expires_in = intval( get_option( $prefix . 'access_expires_in', 0 ) );
    $obtained_at = intval( get_option( $prefix . 'access_token_obtained_at', 0 ) );

    if ( ! empty( $access_token ) && $expires_in > 0 && $obtained_at > 0 ) {
        $now = time();
        // Add a small buffer (30s) to avoid edge cases
        if ( ( $obtained_at + $expires_in - 30 ) > $now ) {
            return $access_token;
        }
    } elseif ( ! empty( $access_token ) && $expires_in === 0 ) {
        // No expiry information — assume token valid
        return $access_token;
    }

    // Need to request a new token via client_credentials
    if ( empty( $settings['application_id'] ) || empty( $settings['application_secret'] ) ) {
        return new WP_Error( 'no_credentials', __( 'SendPulse client_id/secret not configured.', 'automatorwp-sendpulse' ) );
    }

    $token_url = 'https://api.sendpulse.com/oauth/access_token';

    $response = wp_remote_post( $token_url, array(
        'body'    => array(
            'grant_type'    => 'client_credentials',
            'client_id'     => $settings['application_id'],
            'client_secret' => $settings['application_secret'],
        ),
        'timeout' => 20,
    ) );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( 200 !== intval( $code ) || ! isset( $data['access_token'] ) ) {
        $msg = isset( $data['error_description'] ) ? $data['error_description'] : ( isset( $data['error'] ) ? $data['error'] : __( 'Invalid response from SendPulse.', 'automatorwp-sendpulse' ) );
        return new WP_Error( 'token_error', $msg );
    }

    $access_token = sanitize_text_field( $data['access_token'] );
    automatorwp_sendpulse_set_option_noautoload( $prefix . 'access_token', $access_token );
    automatorwp_sendpulse_set_option_noautoload( $prefix . 'access_valid', true );

    if ( isset( $data['expires_in'] ) ) {
        automatorwp_sendpulse_set_option_noautoload( $prefix . 'access_expires_in', intval( $data['expires_in'] ) );
    }

    automatorwp_sendpulse_set_option_noautoload( $prefix . 'access_token_obtained_at', time() );

    return $access_token;
}


/**
 * Set an option ensuring it's not autoloaded.
 * Adds the option with autoload 'no' if it does not exist, otherwise updates it
 * and forces the `autoload` column to 'no'.
 *
 * @param string $option Option name
 * @param mixed  $value  Option value
 * @return bool True on success, false on failure
 */
function automatorwp_sendpulse_set_option_noautoload( $option, $value ) {
    global $wpdb;

    if ( false === get_option( $option ) ) {
        return add_option( $option, $value, '', 'no' );
    }

    $updated = update_option( $option, $value );

    // Ensure autoload = 'no' for this option in the DB
    $table = $wpdb->options;
    $wpdb->update( $table, array( 'autoload' => 'no' ), array( 'option_name' => $option ) );

    return $updated;
}


// Migration is implemented in a dedicated upgrade file under includes/admin/upgrades/

/**
 * Add or update a subscriber in a SendPulse addressbook.
 * If no $addressbook_id is provided, uses the first addressbook available.
 * Returns decoded API response array or WP_Error.
 *
 * @param string $email
 * @param string $first_name
 * @param string $last_name
 * @param int|null $addressbook_id
 * @return array|WP_Error
 */
function automatorwp_sendpulse_add_subscriber( $email, $first_name = '', $last_name = '', $addressbook_id = null ) {
    if ( empty( $email ) ) {
        return new WP_Error( 'no_email', __( 'No email provided.', 'automatorwp-sendpulse' ) );
    }

    // Additional temporary debug: write a plugin-local debug file so we can be sure logs are saved
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        try {
            $logfile = defined( 'WP_CONTENT_DIR' ) ? rtrim( WP_CONTENT_DIR, '\\/' ) . DIRECTORY_SEPARATOR . 'automatorwp-sendpulse-debug.log' : __DIR__ . DIRECTORY_SEPARATOR . 'automatorwp-sendpulse-debug.log';
            $entry = date( 'c' ) . " | add_subscriber called | email=" . $email . " | first_name=" . $first_name . " | last_name=" . $last_name . " | addressbook_id=" . var_export( $addressbook_id, true ) . "\n";
            @file_put_contents( $logfile, $entry, FILE_APPEND | LOCK_EX );
        } catch ( Exception $e ) {
            // ignore
        }
    }

    // Determine addressbook
    if ( empty( $addressbook_id ) ) {
        $books = automatorwp_sendpulse_request( 'GET', '/addressbooks' );
        if ( is_wp_error( $books ) ) {
            return $books;
        }
        // SendPulse returns addressbooks under data.data or data depending on API version
        if ( isset( $books['data'] ) && is_array( $books['data'] ) ) {
            $first = reset( $books['data'] );
            $addressbook_id = isset( $first['id'] ) ? $first['id'] : null;
        } elseif ( isset( $books['data']['data'] ) && is_array( $books['data']['data'] ) ) {
            $first = reset( $books['data']['data'] );
            $addressbook_id = isset( $first['id'] ) ? $first['id'] : null;
        }
    }

    if ( empty( $addressbook_id ) ) {
        return new WP_Error( 'no_addressbook', __( 'No addressbook available.', 'automatorwp-sendpulse' ) );
    }

    $payload = array(
        'emails' => array(
            array(
                'email' => $email,
                'variables' => array(
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                ),
            ),
        ),
    );

    $endpoint = '/addressbooks/' . intval( $addressbook_id ) . '/emails';

    $response = automatorwp_sendpulse_request( 'POST', $endpoint, array( 'body' => $payload ) );

    // Log response to plugin-local debug file when WP_DEBUG is enabled
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        try {
            $logfile = defined( 'WP_CONTENT_DIR' ) ? rtrim( WP_CONTENT_DIR, '\\/' ) . DIRECTORY_SEPARATOR . 'automatorwp-sendpulse-debug.log' : __DIR__ . DIRECTORY_SEPARATOR . 'automatorwp-sendpulse-debug.log';
            $entry = date( 'c' ) . " | add_subscriber response | endpoint=" . $endpoint . " | response=" . print_r( $response, true ) . "\n";
            @file_put_contents( $logfile, $entry, FILE_APPEND | LOCK_EX );
        } catch ( Exception $e ) {
            // ignore
        }
    }

    return $response;

}


/**
 * List subscribers (emails) from an addressbook
 *
 * @param int $addressbook_id
 * @param int $page
 * @param int $per_page
 * @return array|WP_Error
 */
function automatorwp_sendpulse_list_subscribers( $addressbook_id, $page = 1, $per_page = 50 ) {
    if ( empty( $addressbook_id ) ) {
        return new WP_Error( 'no_addressbook', __( 'Addressbook ID required.', 'automatorwp-sendpulse' ) );
    }

    $endpoint = '/addressbooks/' . intval( $addressbook_id ) . '/emails?page=' . intval( $page ) . '&limit=' . intval( $per_page );

    $response = automatorwp_sendpulse_request( 'GET', $endpoint );

    return $response;

}


/**
 * Get the addressbook title for a given id
 *
 * @since 1.0.0
 *
 * @param string|int $addressbook_id
 * @return string
 */
function automatorwp_sendpulse_get_addressbook_title( $addressbook_id ) {

    if ( empty( $addressbook_id ) ) {
        return '';
    }

    $books = automatorwp_sendpulse_request( 'GET', '/addressbooks' );
    if ( is_wp_error( $books ) ) {
        return '';
    }

    // Normalize response into array of items like in the AJAX list
    $items = array();
    if ( isset( $books['data'] ) && is_array( $books['data'] ) ) {
        $items = $books['data'];
    } elseif ( isset( $books['data']['data'] ) && is_array( $books['data']['data'] ) ) {
        $items = $books['data']['data'];
    } elseif ( is_array( $books ) ) {
        $items = $books;
    }

    foreach ( $items as $item ) {
        $id = isset( $item['id'] ) ? $item['id'] : ( isset( $item['_id'] ) ? $item['_id'] : ( isset( $item['addressbook_id'] ) ? $item['addressbook_id'] : '' ) );
        $name = isset( $item['name'] ) ? $item['name'] : ( isset( $item['title'] ) ? $item['title'] : ( isset( $item['addressbook'] ) ? $item['addressbook'] : '' ) );
        if ( (string) $id === (string) $addressbook_id ) {
            return $name;
        }
    }

    return '';

}


/**
 * Options callback for addressbook selector (used to render stored values as labels)
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 * @return array
 */
function automatorwp_sendpulse_options_cb_addressbook( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = '';
    $none_label = __( 'No addressbook (use default)', 'automatorwp-sendpulse' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

    if ( ! empty( $value ) ) {
        if ( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach ( $value as $ab_id ) {

            // Skip option none
            if ( $ab_id === $none_value ) {
                continue;
            }

            $options[ $ab_id ] = automatorwp_sendpulse_get_addressbook_title( $ab_id );
        }
    }

    return $options;

}


/**
 * Remove a subscriber (email) from an addressbook
 *
 * @param string $email
 * @param int|null $addressbook_id
 * @return array|WP_Error
 */
function automatorwp_sendpulse_remove_subscriber( $email, $addressbook_id = null ) {

    if ( empty( $email ) ) {
        return new WP_Error( 'no_email', __( 'No email provided.', 'automatorwp-sendpulse' ) );
    }

    // If no addressbook provided, attempt to use first
    if ( empty( $addressbook_id ) ) {
        $books = automatorwp_sendpulse_request( 'GET', '/addressbooks' );
        if ( is_wp_error( $books ) ) {
            return $books;
        }
        if ( isset( $books['data'] ) && is_array( $books['data'] ) ) {
            $first = reset( $books['data'] );
            $addressbook_id = isset( $first['id'] ) ? $first['id'] : null;
        } elseif ( isset( $books['data']['data'] ) && is_array( $books['data']['data'] ) ) {
            $first = reset( $books['data']['data'] );
            $addressbook_id = isset( $first['id'] ) ? $first['id'] : null;
        }
    }

    if ( empty( $addressbook_id ) ) {
        return new WP_Error( 'no_addressbook', __( 'No addressbook available.', 'automatorwp-sendpulse' ) );
    }

    // Try to find the subscriber in the addressbook and delete by record id when possible
    $found_id = null;
    $page = 1;
    $max_pages = 5;
    while ( $page <= $max_pages && is_null( $found_id ) ) {
        $list = automatorwp_sendpulse_list_subscribers( $addressbook_id, $page, 100 );
        if ( is_wp_error( $list ) ) {
            break;
        }

        // Normalized places where email entries may be
        $entries = array();
        if ( isset( $list['data'] ) && is_array( $list['data'] ) ) {
            // Some API versions: data => [ {email...}, ... ]
            $entries = $list['data'];
        } elseif ( isset( $list['data']['data'] ) && is_array( $list['data']['data'] ) ) {
            // Other nested shape
            $entries = $list['data']['data'];
        } elseif ( isset( $list['data']['items'] ) && is_array( $list['data']['items'] ) ) {
            $entries = $list['data']['items'];
        } elseif ( is_array( $list ) ) {
            // fallback if response is already the array
            $entries = $list;
        }

        foreach ( $entries as $entry ) {
            // Various possible keys for email
            $entry_email = isset( $entry['email'] ) ? $entry['email'] : ( isset( $entry['email_address'] ) ? $entry['email_address'] : '' );
            if ( ! empty( $entry_email ) && strtolower( $entry_email ) === strtolower( $email ) ) {
                // Prefer numeric id if available
                if ( isset( $entry['id'] ) && $entry['id'] ) {
                    $found_id = $entry['id'];
                } elseif ( isset( $entry['email_id'] ) ) {
                    $found_id = $entry['email_id'];
                }
                break;
            }
        }

        $page++;
    }

    // If we found a numeric record id, attempt DELETE by id
    if ( $found_id ) {
        $endpoint = '/addressbooks/' . intval( $addressbook_id ) . '/emails/' . intval( $found_id );
        $response = automatorwp_sendpulse_request( 'DELETE', $endpoint );

        // If method not allowed, fall back to other strategy below
        if ( is_wp_error( $response ) ) {
            $err = $response->get_error_message();
            if ( strpos( $err, 'Method Not Allowed' ) === false ) {
                return $response;
            }
        } else {
            return $response;
        }
    }

    // Fallback: try DELETE using email (some APIs may accept)
    $endpoint = '/addressbooks/' . intval( $addressbook_id ) . '/emails/' . rawurlencode( $email );
    $response = automatorwp_sendpulse_request( 'DELETE', $endpoint );

    if ( ! is_wp_error( $response ) ) {
        return $response;
    }

    // If DELETE is not allowed (405), try an alternative: POST to the emails endpoint asking deletion
    $err_msg = $response->get_error_message();
    if ( strpos( $err_msg, 'Method Not Allowed' ) !== false || strpos( $err_msg, '405' ) !== false ) {
        $alt_endpoint = '/addressbooks/' . intval( $addressbook_id ) . '/emails';
        $alt_body = array(
            'emails' => array( $email ),
            'action' => 'delete',
        );

        $alt_response = automatorwp_sendpulse_request( 'POST', $alt_endpoint, array( 'body' => $alt_body ) );
        if ( ! is_wp_error( $alt_response ) ) {
            return $alt_response;
        }
        // Return original error if alt also fails
        return $response;
    }

    return $response;

}


/**
 * Remove an email from all addressbooks. Returns detailed results per addressbook.
 *
 * @param string $email
 * @return array|WP_Error
 */
// Note: helper automatorwp_sendpulse_remove_subscriber_everywhere was removed —
// temporary test-only helper not required in production.
