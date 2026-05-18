<?php
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Register action: Create or update contact on GoHighLevel.
 *
 * @since 1.0.0
 * @return void
 */
function awp_gohighlevel_register_action_create_update_contact()
{
    if (! function_exists('automatorwp_register_action')) {
        return;
    }

    automatorwp_register_action('gohighlevel_create_update_contact', array(
        'integration'   => 'gohighlevel',
        'label'         => 'GoHighLevel: Create or update contact',
        'select_option' => 'GoHighLevel: Create or update contact',
        'edit_label'    => 'Create/update contact "{gohighlevel_contact_email}"',
        'options'       => array(
            'contact' => array(
                'from'    => '',
                'default' => '',
                'fields'  => array(
                    'gohighlevel_contact_overwrite_existing' => array(
                        'name'    => 'If the contact exists, overwrite personal data?',
                        'type'    => 'select',
                        'options' => array(
                            'yes' => 'Yes, overwrite (upsert)',
                            'no'  => 'No, keep personal data and only merge tags',
                        ),
                        'default' => 'yes',
                    ),
                    'gohighlevel_contact_first_name' => array(
                        'name'    => 'First name',
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_contact_last_name' => array(
                        'name'    => 'Last name',
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_contact_email' => array(
                        'name'    => 'Email',
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_contact_phone' => array(
                        'name'    => 'Phone',
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_contact_location_id' => array(
                        'name'    => 'Location ID (optional)',
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_contact_source' => array(
                        'name'    => 'Lead source (optional)',
                        'type'    => 'text',
                        'default' => '',
                    ),
                    'gohighlevel_contact_tags' => array(
                        'name'    => 'Tags (CSV, optional)',
                        'type'    => 'text',
                        'default' => '',
                    ),
                ),
            ),
        ),
    ));
}
add_action('automatorwp_init', 'awp_gohighlevel_register_action_create_update_contact', 26);

/**
 * Execute action: Create or update contact on GoHighLevel.
 *
 * @since 1.0.0
 *
 * @param stdClass $action Action object.
 * @param int      $user_id User ID.
 * @param array    $event Event payload.
 * @param array    $action_options Action options.
 * @param stdClass $automation Automation object.
 * @return void
 */
function awp_gohighlevel_execute_action_create_update_contact($action, $user_id, $event, $action_options, $automation)
{
    if (! is_object($action) || empty($action->type) || $action->type !== 'gohighlevel_create_update_contact') {
        return;
    }

    $first_name = isset($action_options['gohighlevel_contact_first_name']) ? sanitize_text_field((string) $action_options['gohighlevel_contact_first_name']) : '';
    $last_name = isset($action_options['gohighlevel_contact_last_name']) ? sanitize_text_field((string) $action_options['gohighlevel_contact_last_name']) : '';
    $email = isset($action_options['gohighlevel_contact_email']) ? sanitize_email((string) $action_options['gohighlevel_contact_email']) : '';
    $phone = isset($action_options['gohighlevel_contact_phone']) ? sanitize_text_field((string) $action_options['gohighlevel_contact_phone']) : '';
    $overwrite_existing = isset($action_options['gohighlevel_contact_overwrite_existing']) ? sanitize_text_field((string) $action_options['gohighlevel_contact_overwrite_existing']) : 'yes';
    $location_id = isset($action_options['gohighlevel_contact_location_id']) ? sanitize_text_field((string) $action_options['gohighlevel_contact_location_id']) : '';
    $source = isset($action_options['gohighlevel_contact_source']) ? sanitize_text_field((string) $action_options['gohighlevel_contact_source']) : '';
    $tags_csv = isset($action_options['gohighlevel_contact_tags']) ? sanitize_text_field((string) $action_options['gohighlevel_contact_tags']) : '';

    if ($email === '' && $phone === '') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[AWP-GoHighLevel] contact upsert canceled: email and phone are empty');
        }
        return;
    }

    if ($location_id === '') {
        $location_id = automatorwp_gohighlevel_get_option('location_id', '');
    }

    $tags = array();
    if ($tags_csv !== '') {
        $raw_tags = array_map('trim', explode(',', $tags_csv));
        $raw_tags = array_filter($raw_tags);
        foreach ($raw_tags as $raw_tag) {
            $tags[] = sanitize_text_field($raw_tag);
        }
    }

    $tags = awp_gohighlevel_normalize_tags($tags);

    $body = array(
        'firstName' => $first_name,
        'lastName'  => $last_name,
        'email'     => $email,
        'phone'     => $phone,
    );

    if ($location_id !== '') {
        $body['locationId'] = $location_id;
    }

    if ($source !== '') {
        $body['source'] = $source;
    }

    if (! empty($tags)) {
        $body['tags'] = $tags;
    }

    $existing_contact = awp_gohighlevel_find_contact_by_identity($location_id, $email, $phone);

    if ($existing_contact && isset($existing_contact['id'])) {
        $existing_contact_id = sanitize_text_field((string) $existing_contact['id']);
        $existing_tags = awp_gohighlevel_get_contact_tags($existing_contact);
        $merged_tags = awp_gohighlevel_normalize_tags(array_merge($existing_tags, $tags));

        if ($overwrite_existing === 'no') {
            if (! empty($merged_tags)) {
                $response = automatorwp_gohighlevel_api_request('PUT', 'contacts/' . rawurlencode($existing_contact_id), array(
                    'body' => array(
                        'tags' => $merged_tags,
                    ),
                ));

                if (is_wp_error($response) && defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[AWP-GoHighLevel] update existing contact tags error: ' . $response->get_error_message());
                }
            }

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[AWP-GoHighLevel] existing contact found, personal data not overwritten, tags merged');
            }

            return;
        }

        $update_body = $body;
        unset($update_body['locationId']);
        $update_body['tags'] = $merged_tags;

        $response = automatorwp_gohighlevel_api_request('PUT', 'contacts/' . rawurlencode($existing_contact_id), array(
            'body' => $update_body,
        ));

        if (is_wp_error($response)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[AWP-GoHighLevel] update existing contact error: ' . $response->get_error_message());
            }
            return;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[AWP-GoHighLevel] existing contact updated with merged tags, contact_id=' . $existing_contact_id);
        }

        return;
    }

    $endpoint = 'contacts/upsert';

    if ($overwrite_existing === 'no') {
        $endpoint = 'contacts/';
    }

    $response = automatorwp_gohighlevel_api_request('POST', $endpoint, array(
        'body' => $body,
    ));

    if (is_wp_error($response)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[AWP-GoHighLevel] contact upsert error: ' . $response->get_error_message());
        }
        return;
    }

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[AWP-GoHighLevel] contact action success endpoint=' . $endpoint . ' email=' . $email . ' phone=' . $phone);
    }
}
add_action('automatorwp_execute_action', 'awp_gohighlevel_execute_action_create_update_contact', 10, 5);

/**
 * Find contact by email or phone using contacts list endpoint.
 *
 * @since 1.0.0
 *
 * @param string $location_id Location ID.
 * @param string $email       Contact email.
 * @param string $phone       Contact phone.
 * @return array|null
 */
function awp_gohighlevel_find_contact_by_identity($location_id, $email, $phone)
{
    if ($location_id === '' || ($email === '' && $phone === '')) {
        return null;
    }

    $queries = array();

    if ($email !== '') {
        $queries[] = $email;
    }

    if ($phone !== '') {
        $queries[] = $phone;
    }

    $email_norm = strtolower(trim((string) $email));
    $phone_norm = preg_replace('/[^0-9+]/', '', (string) $phone);

    foreach ($queries as $query) {
        $endpoint = sprintf(
            'contacts/?locationId=%s&query=%s&limit=20',
            rawurlencode($location_id),
            rawurlencode($query)
        );

        $response = automatorwp_gohighlevel_api_request('GET', $endpoint);

        if (is_wp_error($response) || empty($response['body']) || ! is_array($response['body'])) {
            continue;
        }

        $contacts = isset($response['body']['contacts']) && is_array($response['body']['contacts']) ? $response['body']['contacts'] : array();

        foreach ($contacts as $contact) {
            if (! is_array($contact)) {
                continue;
            }

            $contact_id = awp_gohighlevel_extract_contact_id($contact);
            if ($contact_id === '') {
                continue;
            }

            $contact_email = '';
            if (isset($contact['email'])) {
                $contact_email = strtolower(trim((string) $contact['email']));
            }

            $contact_phone = '';
            if (isset($contact['phone'])) {
                $contact_phone = preg_replace('/[^0-9+]/', '', (string) $contact['phone']);
            }

            if (($email_norm !== '' && $contact_email === $email_norm) || ($phone_norm !== '' && $contact_phone === $phone_norm)) {
                $contact['id'] = $contact_id;
                return $contact;
            }
        }
    }

    return null;
}

/**
 * Extract contact ID from different response shapes.
 *
 * @since 1.0.0
 *
 * @param array $contact Contact data.
 * @return string
 */
function awp_gohighlevel_extract_contact_id($contact)
{
    if (isset($contact['id']) && $contact['id'] !== '') {
        return (string) $contact['id'];
    }

    if (isset($contact['_id']) && $contact['_id'] !== '') {
        return (string) $contact['_id'];
    }

    if (isset($contact['contactId']) && $contact['contactId'] !== '') {
        return (string) $contact['contactId'];
    }

    return '';
}

/**
 * Get contact tags from contact data or API detail endpoint.
 *
 * @since 1.0.0
 *
 * @param array $contact Contact data.
 * @return array
 */
function awp_gohighlevel_get_contact_tags($contact)
{
    if (isset($contact['tags']) && is_array($contact['tags'])) {
        return awp_gohighlevel_normalize_tags($contact['tags']);
    }

    $contact_id = awp_gohighlevel_extract_contact_id($contact);
    if ($contact_id === '') {
        return array();
    }

    $response = automatorwp_gohighlevel_api_request('GET', 'contacts/' . rawurlencode($contact_id));

    if (is_wp_error($response) || empty($response['body']) || ! is_array($response['body'])) {
        return array();
    }

    if (isset($response['body']['contact']) && is_array($response['body']['contact']) && isset($response['body']['contact']['tags']) && is_array($response['body']['contact']['tags'])) {
        return awp_gohighlevel_normalize_tags($response['body']['contact']['tags']);
    }

    if (isset($response['body']['tags']) && is_array($response['body']['tags'])) {
        return awp_gohighlevel_normalize_tags($response['body']['tags']);
    }

    return array();
}

/**
 * Normalize tags array and remove duplicates.
 *
 * @since 1.0.0
 *
 * @param array $tags Tags list.
 * @return array
 */
function awp_gohighlevel_normalize_tags($tags)
{
    if (! is_array($tags)) {
        return array();
    }

    $normalized = array();
    $index = array();

    foreach ($tags as $tag) {
        $tag = sanitize_text_field((string) $tag);
        if ($tag === '') {
            continue;
        }

        $key = strtolower($tag);
        if (isset($index[$key])) {
            continue;
        }

        $index[$key] = true;
        $normalized[] = $tag;
    }

    return $normalized;
}
