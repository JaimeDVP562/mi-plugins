<?php
/**
 * Helper Functions for Google Groups Integration (minimal skeleton)
 *
 * @package     AutomatorWP\GoogleGroups\Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcut to retrieve plugin settings
 */
if ( ! function_exists( 'automatorwp_googlegroups_get_option' ) ) {
    function automatorwp_googlegroups_get_option( $option_name, $default = false ) {
        $prefix = 'automatorwp_googlegroups_';
        if ( function_exists( 'automatorwp_get_option' ) ) {
            return automatorwp_get_option( $prefix . $option_name, $default );
        }
        return get_option( $prefix . $option_name, $default );
    }
}

// ------------------------------------------------------------------
// service interface & implementations (previously in includes/services)
// ------------------------------------------------------------------

if ( ! interface_exists( 'Groups_Service_Interface' ) ) {
    interface Groups_Service_Interface {
        /**
         * @param string $query optional filter string
         * @return array list of groups (each ['email'=>..., 'name'=>...])
         */
        public function list_groups( string $query = '' ): array;

        /**
         * @param string $group_email
         * @return array list of members (each ['email'=>..., 'role'=>...])
         */
        public function list_members( string $group_email ): array;

        /**
         * Add a member; return HTTP-like status code (200,409,404,0)
         */
        public function add_member( string $group, string $email, string $role = 'MEMBER' ): int;

        public function remove_member( string $group, string $email ): int;

        public function create_group( string $email, string $name = '' ): int;

        public function delete_group( string $email ): int;

        public function update_group( string $email, array $data ): int;

        public function change_member_role( string $group, string $email, string $role ): int;

        public function remove_all_members( string $group ): int;
    }
}

if ( ! class_exists( 'Google_Groups_Service' ) ) {
    class Google_Groups_Service implements Groups_Service_Interface {
        private $access_token;

        public function __construct() {
            $creds = automatorwp_googlegroups_get_service_account();
            if ( $creds ) {
                $this->access_token = automatorwp_googlegroups_get_access_token( $creds );
            }
        }

        private function request( $url, $method = 'GET', $body = null ) {
            $headers = array(
                'Authorization: Bearer ' . $this->access_token,
                'Content-Type: application/json',
            );
            return automatorwp_googlegroups_http_request( $url, $method, $body, $headers );
        }

        public function list_groups( string $query = '' ): array {
            if ( empty( $this->access_token ) ) {
                return array();
            }

            $domain = automatorwp_googlegroups_get_option( 'service_account_domain', '' );
            if ( empty( $domain ) ) {
                $imp = automatorwp_googlegroups_get_option( 'service_account_email', '' );
                if ( ! empty( $imp ) && strpos( $imp, '@' ) !== false ) {
                    $parts = explode( '@', $imp );
                    $domain = array_pop( $parts );
                }
            }
            if ( empty( $domain ) ) {
                return array();
            }

            $url = 'https://admin.googleapis.com/admin/directory/v1/groups?domain=' . urlencode( $domain );
            $response = $this->request( $url, 'GET' );
            if ( ! $response || empty( $response['body'] ) ) {
                return array();
            }
            $data = json_decode( $response['body'], true );
            if ( empty( $data ) || empty( $data['groups'] ) ) {
                return array();
            }
            $out = array();
            foreach ( $data['groups'] as $g ) {
                $out[] = array(
                    'email' => isset( $g['email'] ) ? $g['email'] : '',
                    'name'  => isset( $g['name'] ) ? $g['name'] : ( isset( $g['email'] ) ? $g['email'] : '' ),
                );
            }
            // apply basic query filter if provided
            if ( ! empty( $query ) ) {
                $q = strtolower( $query );
                $out = array_values( array_filter( $out, function( $g ) use ( $q ) {
                    return false !== strpos( strtolower( $g['email'] . ' ' . $g['name'] ), $q );
                } ) );
            }
            return $out;
        }

        public function list_members( string $group_email ): array {
            if ( empty( $this->access_token ) ) {
                return array();
            }
            if ( empty( $group_email ) ) {
                return array();
            }
            $url = sprintf( 'https://admin.googleapis.com/admin/directory/v1/groups/%s/members', urlencode( $group_email ) );
            $response = $this->request( $url, 'GET' );
            if ( ! $response || empty( $response['body'] ) ) {
                return array();
            }
            $data = json_decode( $response['body'], true );
            if ( empty( $data ) || empty( $data['members'] ) ) {
                return array();
            }
            $out = array();
            foreach ( $data['members'] as $m ) {
                $out[] = array(
                    'email' => isset( $m['email'] ) ? $m['email'] : '',
                    'role'  => isset( $m['role'] ) ? $m['role'] : 'MEMBER',
                );
            }
            return $out;
        }

        public function add_member( string $group, string $email, string $role = 'MEMBER' ): int {
            if ( empty( $this->access_token ) || empty( $group ) || empty( $email ) ) {
                return 0;
            }
            $body = json_encode( array( 'email' => $email, 'role' => $role ) );
            $url = sprintf( 'https://admin.googleapis.com/admin/directory/v1/groups/%s/members', urlencode( $group ) );
            $response = $this->request( $url, 'POST', $body );
            if ( ! $response ) {
                return 0;
            }
            $http_code = intval( $response['http_code'] ?? 0 );
            if ( in_array( $http_code, array( 200, 201 ), true ) ) {
                return 200;
            }
            return $http_code;
        }

        public function remove_member( string $group, string $email ): int {
            if ( empty( $this->access_token ) || empty( $group ) || empty( $email ) ) {
                return 0;
            }
            $url = sprintf( 'https://admin.googleapis.com/admin/directory/v1/groups/%s/members/%s', urlencode( $group ), urlencode( $email ) );
            $response = $this->request( $url, 'DELETE' );
            if ( ! $response ) {
                return 0;
            }
            $http_code = intval( $response['http_code'] ?? 0 );
            if ( in_array( $http_code, array( 200, 204 ), true ) ) {
                return 200;
            }
            return $http_code;
        }

        public function create_group( string $email, string $name = '' ): int {
            if ( empty( $this->access_token ) || empty( $email ) ) {
                return 0;
            }
            $body = json_encode( array( 'email' => $email, 'name' => $name ) );
            $url  = 'https://admin.googleapis.com/admin/directory/v1/groups';
            $response = $this->request( $url, 'POST', $body );
            if ( ! $response ) {
                return 0;
            }
            $http_code = intval( $response['http_code'] ?? 0 );
            if ( in_array( $http_code, array( 200, 201 ), true ) ) {
                return 200;
            }
            return $http_code;
        }

        public function delete_group( string $email ): int {
            if ( empty( $this->access_token ) || empty( $email ) ) {
                return 0;
            }
            $url = sprintf( 'https://admin.googleapis.com/admin/directory/v1/groups/%s', urlencode( $email ) );
            $response = $this->request( $url, 'DELETE' );
            if ( ! $response ) {
                return 0;
            }
            $http_code = intval( $response['http_code'] ?? 0 );
            if ( in_array( $http_code, array( 200, 204 ), true ) ) {
                return 200;
            }
            return $http_code;
        }

        public function update_group( string $email, array $data ): int {
            if ( empty( $this->access_token ) || empty( $email ) || empty( $data ) ) {
                return 0;
            }
            $body = json_encode( $data );
            $url  = sprintf( 'https://admin.googleapis.com/admin/directory/v1/groups/%s', urlencode( $email ) );
            $response = $this->request( $url, 'PATCH', $body );
            if ( ! $response ) {
                return 0;
            }
            $http_code = intval( $response['http_code'] ?? 0 );
            if ( in_array( $http_code, array( 200, 204 ), true ) ) {
                return 200;
            }
            return $http_code;
        }

        public function change_member_role( string $group, string $email, string $role ): int {
            if ( empty( $this->access_token ) || empty( $group ) || empty( $email ) || empty( $role ) ) {
                return 0;
            }
            $body = json_encode( array( 'role' => $role ) );
            $url  = sprintf( 'https://admin.googleapis.com/admin/directory/v1/groups/%s/members/%s', urlencode( $group ), urlencode( $email ) );
            $response = $this->request( $url, 'PUT', $body );
            if ( ! $response ) {
                return 0;
            }
            $http_code = intval( $response['http_code'] ?? 0 );
            if ( in_array( $http_code, array( 200, 204 ), true ) ) {
                return 200;
            }
            return $http_code;
        }

        public function remove_all_members( string $group ): int {
            if ( empty( $group ) ) {
                return 0;
            }
            $members = $this->list_members( $group );
            $status  = 200;
            foreach ( $members as $member ) {
                $email = isset( $member['email'] ) ? $member['email'] : '';
                if ( empty( $email ) ) {
                    continue;
                }
                $result = $this->remove_member( $group, $email );
                if ( $result !== 200 ) {
                    $status = $result;
                }
            }
            return $status;
        }
    }
}

/**
 * Return an instance of the service implementation.
 *
 * The real implementation relies on a valid Google service account JSON
 * being configured via the plugin settings (and the "configured" toggle).
 *
 * @return Groups_Service_Interface|null
 */
function automatorwp_googlegroups_get_service() {
    static $service = null;
    if ( null !== $service ) {
        return $service;
    }

    $configured = automatorwp_googlegroups_get_option( 'configured', false );
    if ( ! $configured ) {
        return null;
    }

    $creds = automatorwp_googlegroups_get_service_account();
    if ( ! $creds ) {
        return null;
    }

    $service = new Google_Groups_Service();
    return $service;
}

/**
 * Get available Google Groups (uses Directory API when configured)
 * Return array: group_email => group_name
 */
function automatorwp_googlegroups_get_groups() {
    $service = automatorwp_googlegroups_get_service();
    if ( ! $service ) {
        return array();
    }

    $groups_arr = $service->list_groups(); // array of ['email'=>..,'name'=>..]
    $out = array();
    foreach ( $groups_arr as $g ) {
        $email = isset( $g['email'] ) ? $g['email'] : '';
        $name  = isset( $g['name'] ) ? $g['name'] : $email;
        if ( $email ) {
            $out[ $email ] = $name;
        }
    }
    return $out;
}


/**
 * Options callback for selectors
 */
function automatorwp_googlegroups_options_groups( $field = null ) {
    $groups = automatorwp_googlegroups_get_groups();
    $options = array();
    foreach ( $groups as $email => $name ) {
        $options[ $email ] = $name . ' <' . $email . '>';
    }
    return $options;
}

/**
 * Get members for a given group (real implementation using Directory API if configured)
 * @return array [email => name]
 */
function automatorwp_googlegroups_get_members( $group_email ) {
    if ( empty( $group_email ) ) {
        return array();
    }
    $service = automatorwp_googlegroups_get_service();
    if ( ! $service ) {
        return array();
    }
    $members_arr = $service->list_members( $group_email );
    $out = array();
    foreach ( $members_arr as $m ) {
        $email = isset( $m['email'] ) ? $m['email'] : '';
        $name  = isset( $m['name'] ) ? $m['name'] : $email;
        if ( $email ) {
            $out[ $email ] = $name;
        }
    }
    return $out;
}

/**
 * Options callback for members selector (used by ajax_selector_option)
 *
 * The $field argument may contain form data so we can detect the currently
 * chosen group and filter accordingly. If no group is selected we return
 * an empty array, forcing the user to pick a group first.
 */
function automatorwp_googlegroups_options_members( $field = null ) {
    $group = '';
    if ( is_array( $field ) && isset( $field['group'] ) ) {
        $group = $field['group'];
    }
    if ( empty( $group ) && isset( $_REQUEST['group'] ) ) {
        $group = sanitize_text_field( wp_unslash( $_REQUEST['group'] ) );
    }
    $members = automatorwp_googlegroups_get_members( $group );
    $options = array();
    foreach ( $members as $email => $name ) {
        $options[ $email ] = $name . ' <' . $email . '>';
    }
    return $options;
}


/**
 * Add a member to a Google Group (uses Directory API when configured)
 * Returns HTTP-like codes: 200 success, 409 already exists, 404 group not found, 0 configuration error.
 */
function automatorwp_googlegroups_add_member( $group_email, $member_email, $role = 'MEMBER' ) {
    if ( empty( $group_email ) || empty( $member_email ) ) {
        return 0;
    }
    $service = automatorwp_googlegroups_get_service();
    if ( ! $service ) {
        return 0;
    }
    return $service->add_member( $group_email, $member_email, $role );
}


/**
 * Remove a member from a Google Group (real implementation when configured)
 */
function automatorwp_googlegroups_remove_member( $group_email, $member_email ) {
    if ( empty( $group_email ) || empty( $member_email ) ) {
        return 0;
    }
    $service = automatorwp_googlegroups_get_service();
    if ( ! $service ) {
        return 0;
    }
    return $service->remove_member( $group_email, $member_email );
}


/**
 * Create a Google Group
 * Returns 200 on success, 0 on failure.
 */
function automatorwp_googlegroups_create_group( $email, $name = '' ) {
    if ( empty( $email ) ) {
        return 0;
    }
    $service = automatorwp_googlegroups_get_service();
    if ( ! $service ) {
        return 0;
    }
    return $service->create_group( $email, $name );
}

/**
 * Delete a Google Group
 * Returns 200 on success, 0 on failure.
 */
function automatorwp_googlegroups_delete_group( $email ) {
    if ( empty( $email ) ) {
        return 0;
    }
    $service = automatorwp_googlegroups_get_service();
    if ( ! $service ) {
        return 0;
    }
    return $service->delete_group( $email );
}

/**
 * Update group settings (name/description, etc.)
 */
function automatorwp_googlegroups_update_group( $email, $data = array() ) {
    if ( empty( $email ) ) {
        return 0;
    }
    $service = automatorwp_googlegroups_get_service();
    if ( ! $service ) {
        return 0;
    }
    return $service->update_group( $email, $data );
}

/**
 * Change a member's role
 */
function automatorwp_googlegroups_change_member_role( $group_email, $member_email, $role ) {
    if ( empty( $group_email ) || empty( $member_email ) || empty( $role ) ) {
        return 0;
    }
    $service = automatorwp_googlegroups_get_service();
    if ( ! $service ) {
        return 0;
    }
    return $service->change_member_role( $group_email, $member_email, $role );
}

/**
 * Remove all members from a given group
 */
function automatorwp_googlegroups_remove_all_members( $group_email ) {
    if ( empty( $group_email ) ) {
        return 0;
    }
    $service = automatorwp_googlegroups_get_service();
    if ( ! $service ) {
        return 0;
    }
    return $service->remove_all_members( $group_email );
}

/**
 * Export members (array or csv)
 */
function automatorwp_googlegroups_export_members( $group_email, $format = 'array' ) {
    $members = automatorwp_googlegroups_get_members( $group_email );
    if ( $format === 'csv' ) {
        $rows = array();
        foreach ( $members as $email => $name ) {
            $rows[] = array( $email, $name );
        }
        $csv = '';
        foreach ( $rows as $r ) {
            $csv .= implode( ',', $r ) . "\n";
        }
        update_option( 'automatorwp_googlegroups_export_csv', $csv );
        return $csv;
    }
    return $members;
}


/**
 * Helper: Read service account JSON from settings and decode
 */
function automatorwp_googlegroups_get_service_account() {
    $json = automatorwp_googlegroups_get_option( 'service_account_json', '' );
    if ( empty( $json ) ) {
        return false;
    }

    $data = json_decode( $json, true );
    if ( empty( $data ) || ! is_array( $data ) ) {
        return false;
    }

    return $data;
}


/**
 * Helper: get access token using JWT flow for service accounts
 */
function automatorwp_googlegroups_get_access_token( $creds ) {
    // Check minimum required fields
    if ( empty( $creds['client_email'] ) || empty( $creds['private_key'] ) ) {
        return false;
    }

    // Cache key per service account
    $cache_key = 'automatorwp_googlegroups_access_token_' . md5( $creds['client_email'] );
    if ( function_exists( 'get_transient' ) ) {
        $cached = get_transient( $cache_key );
        if ( ! empty( $cached ) ) {
            return $cached;
        }
    }

    $scopes = array(
        'https://www.googleapis.com/auth/admin.directory.group',
        'https://www.googleapis.com/auth/admin.directory.group.member',
    );

    $now = time();
    $exp = $now + 3600;

    $header = array( 'alg' => 'RS256', 'typ' => 'JWT' );
    $payload = array(
        'iss' => $creds['client_email'],
        'scope' => implode( ' ', $scopes ),
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $exp,
        'iat' => $now,
    );

    // If impersonation email is set, use sub
    $sub = automatorwp_googlegroups_get_option( 'service_account_email', '' );
    if ( ! empty( $sub ) ) {
        $payload['sub'] = $sub;
    }

    $base64url = function( $data ) {
        return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
    };

    $jwt_header = $base64url( json_encode( $header ) );
    $jwt_payload = $base64url( json_encode( $payload ) );
    $unsigned = $jwt_header . '.' . $jwt_payload;

    // Sign with private key
    $private_key = $creds['private_key'];
    $signature = '';
    $ok = openssl_sign( $unsigned, $signature, $private_key, OPENSSL_ALGO_SHA256 );
    if ( ! $ok ) {
        return false;
    }

    $jwt = $unsigned . '.' . $base64url( $signature );

    // Request token
    $body = http_build_query( array(
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt,
    ) );

    $response = automatorwp_googlegroups_http_request( 'https://oauth2.googleapis.com/token', 'POST', $body, array( 'Content-Type: application/x-www-form-urlencoded' ) );
    if ( ! $response || empty( $response['body'] ) ) {
        return false;
    }

    $data = json_decode( $response['body'], true );
    if ( empty( $data ) || empty( $data['access_token'] ) ) {
        return false;
    }

    // Cache token for 'expires_in' seconds minus a safety window
    $expires_in = isset( $data['expires_in'] ) ? intval( $data['expires_in'] ) : 3600;
    $ttl = max( 60, $expires_in - 60 );
    if ( function_exists( 'set_transient' ) ) {
        set_transient( $cache_key, $data['access_token'], $ttl );
    }

    return $data['access_token'];
}


/**
 * Low level HTTP request helper using cURL or fallback to file_get_contents
 */
function automatorwp_googlegroups_http_request( $url, $method = 'GET', $body = null, $headers = array() ) {
    $method = strtoupper( $method );

    // cURL preferred
    if ( function_exists( 'curl_init' ) ) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HEADER, true );
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
        if ( $method === 'POST' || $method === 'PUT' ) {
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
        }
        if ( ! empty( $headers ) ) {
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
        }
        $raw = curl_exec( $ch );
        if ( $raw === false ) {
            curl_close( $ch );
            return false;
        }

        $header_size = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
        $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $header = substr( $raw, 0, $header_size );
        $body = substr( $raw, $header_size );
        curl_close( $ch );

        return array( 'http_code' => $http_code, 'header' => $header, 'body' => $body );
    }

    // Fallback: file_get_contents with stream_context
    $opts = array( 'http' => array( 'method' => $method, 'header' => implode( "\r\n", $headers ) ) );
    if ( $body !== null ) {
        $opts['http']['content'] = $body;
    }
    $context = stream_context_create( $opts );
    $body = @file_get_contents( $url, false, $context );
    $http_code = isset( $http_response_header ) ? intval( preg_replace( '/[^0-9]/', '', $http_response_header[0] ) ) : 0;

    return array( 'http_code' => $http_code, 'header' => implode( "\r\n", $http_response_header ), 'body' => $body );
}
