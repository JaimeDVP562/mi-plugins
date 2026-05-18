<?php
/**
 * Functions
 *
 * @package     AutomatorWP\ActiveCampaign\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to get the ActiveCampaign API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_activecampaign_get_api() {

    $url = automatorwp_activecampaign_get_option( 'url', '' );
    $key = automatorwp_activecampaign_get_option( 'key', '' );

    if( empty( $url ) || empty( $key ) ) {
        return false;
    }

    return array(
        'url' => $url,
        'key' => $key,
    );

}

/**
 * Get ActiveCampaign webhook URL.
 *
 * @since 1.0.0
 *
 * @return string
 */
function automatorwp_activecampaign_get_webhook_url() {

    $prefix = 'automatorwp_activecampaign_';
    $settings = get_option( 'automatorwp_settings', array() );

    $webhook_url = ( isset( $settings[$prefix . 'webhook'] ) ) ? $settings[$prefix . 'webhook'] : '';

    // Check if webhook URL exists
    if ( empty( $webhook_url ) ){

        $slug = strtolower( wp_generate_password( 8, false ) );
        $webhook_url = get_rest_url() . 'activecampaign/webhooks/' . $slug;

        $settings[$prefix . 'webhook'] = $webhook_url;
        $settings[$prefix . 'slug'] = $slug;

        // Update settings
        update_option( 'automatorwp_settings', $settings );

    }

    return $webhook_url;

}

/**
 * Get ActiveCampaign webhook slug.
 *
 * @since 1.0.0
 *
 * @return string
 */

function automatorwp_activecampaign_get_webhook_slug() {

    $prefix = 'automatorwp_activecampaign_';
    $settings = get_option( 'automatorwp_settings' );

    $slug = $settings[$prefix . 'slug'];

    // Check if webhook slug exists
    if ( empty( $slug ) ){

        $slug = strtolower( wp_generate_password( 8, false ) );
        $webhook_url = get_rest_url() . 'activecampaign/webhooks/' . $slug;

        $settings[$prefix . 'webhook'] = $webhook_url;
        $settings[$prefix . 'slug'] = $slug;

        // Update settings
        update_option( 'automatorwp_settings', $settings );

    }

    return $slug;

}

/**
 * Get ActiveCampaign webhook URL.
 *
 * @since 1.0.0
 *
 * @return string
 */
function automatorwp_activecampaign_trigger_notice() {
    $webhook_url = automatorwp_activecampaign_get_webhook_url();

    // Warn user about copy the webhook url at ActiveCampaign
    ?>
    <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
        <?php echo sprintf(
            __( 'It is required to configure a webhook in your ActiveCampaign account to make this trigger work.', 'automatorwp-activecampaign' ),
            get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-activecampaign'
        ); ?>
        <?php echo sprintf(
            __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp-activecampaign' ),
            'https://automatorwp.com/docs/activecampaign/'
        ); ?> |
        <?php echo __( '<a href="#" class="automatorwp-activecampaign-view-webhook">View Webhook</a>', 'automatorwp-activecampaign' ); ?>
    </div>

    <div class="automatorwp-option-form-container" style="display:none">
        <div class="cmb2-wrap form-table automatorwp-form automatorwp-option-form">
            <div id="cmb2-metabox-webhook_form" class="cmb2-metabox cmb-field-list">
                <div class="cmb-row cmb-type-text cmb2-id-url table-layout automatorwp-webhooks-url" data-fieldtype="text">
                    <div class="cmb-th">
                        <label for="url"><?php echo __('URL:', 'automatorwp-activecampaign'); ?></label>
                    </div>
                    <div class="cmb-td">
                        <input type="text" class="regular-text" name="" id="url" value=<?php echo $webhook_url; ?> readonly data-option="url">
                        <p class="cmb2-metabox-description"><?php echo __('Copy this URL in your ActiveCampaign account.', 'automatorwp-activecampaign'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="button button-primary automatorwp-cancel-option-form"><?php echo __('Hide Webhook', 'automatorwp-activecampaign'); ?></button>
    </div>

    <?php
}

/**
 * Add contact to ActiveCampaign
 *
 * @since 1.0.0
 * 
 * @param array     $contact     The contact data
 */
function automatorwp_activecampaign_add_contact( $contact ) {

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/api/3/contacts', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode( array(
            'contact' => array(
                'email'     => $contact['email'],
                'firstName' => ( isset( $contact['firstName'] ) ? $contact['firstName'] : '' ),
                'lastName'  => ( isset( $contact['lastName'] ) ? $contact['lastName'] : '' ),
                'phone'     => ( isset( $contact['phone'] ) ? $contact['phone'] : '' )
            )
        ) )
    ) );

}

/**
 * Update ActiveCampaign contact.
 *
 * @since 1.0.6
 *
 * @param string  $contact_id       ID tag associated to contact
 * @param array   $contact_update   The contact data to update
 */
function automatorwp_activecampaign_update_contact( $contact_id, $contact_update ) {

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request( $api['url'] . '/api/3/contacts/' . $contact_id, array(
        'method' => 'PUT',
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode( array(
            'contact' => $contact_update,
        ) )
    ));

}

/**
 * Get ActiveCampaign contact data. Filter email
 *
 * @since 1.0.0
 *
 * @param string    $email       The contact email
 *
 * @return array
 */
function automatorwp_activecampaign_get_contact( $email ) {

    $api = automatorwp_activecampaign_get_api();
    
    if( ! $api ) {
        return array( 'contacts' => array() );
    }

    // To manage special characters in email;
    $email = urlencode( $email );

    $response = wp_remote_get( $api['url'] . '/api/3/contacts?email=' . $email, array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response;

}

/**
 * Get tags from ActiveCampaign
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_activecampaign_options_cb_tag( $field ) {

        // Setup vars
        $value = $field->escaped_value;
        $none_value = 'any';
        $none_label = __( 'any tag', 'automatorwp-activecampaign' );
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
                
                $options[$tag_id] = automatorwp_activecampaign_get_tag_name( $tag_id );
            }
        }
    
        return $options;

}

/**
 * Get tags from ActiveCampaign
 *
 * @since 1.0.0
 *
 * @param string $search
 * @param int $page
 *
 * @return array
 */
function automatorwp_activecampaign_get_tags( $search = '', $page = 1 ) {

    $tags = array();

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return $tags;
    }

    $limit = 20;
    $offset = $limit * ($page - 1);

    $response = wp_remote_get( $api['url'] . '/api/3/tags', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        ),
        'body' => array(
            'search' => $search,
            'limit' => $limit,
            'offset' => $offset,
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    foreach ( $response['tags'] as $tag_id ){
        $tags[] = array(
            'id' => $tag_id['id'],
            'name' => $tag_id['tag'],
        );

    }

    return $tags;

}

/**
 * Get tag name
 *
 * @since 1.0.0
 *
 * @param int    $tag_id         ID tag
 * 
 */
function automatorwp_activecampaign_get_tag_name( $tag_id ) {

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return '';
    }
    
    $response = wp_remote_get( $api['url'] . '/api/3/tags/' . $tag_id, array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        )
    ) );
    
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response['tag']['tag'];

}

/**
 * Create tag at ActiveCampaign
 *
 * @since 1.0.1
 *
 * @param string     $tag_name     The tag name
 */
function automatorwp_activecampaign_create_tag( $tag_name ) {

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/api/3/tags', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode( array(
            'tag' => array(
                'tag'     => $tag_name,
                'tagType' => 'contact', // Possible Values: template, contact
            )
        ) )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response;
}

/**
 * Add tag to contact
 *
 * @since 1.0.0
 *
 * @param string    $contact_id         Contact ID
 * @param string    $tag_id             Tag ID
 */
function automatorwp_activecampaign_add_contact_tag( $contact_id, $tag_id ){

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/api/3/contactTags', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode( array(
            'contactTag' => array(
                'contact' => $contact_id,
                'tag' => $tag_id,
            )
        ) )
    ));

}

/**
 * Get ActiveCampaign contact tags.
 *
 * @since 1.0.0
 *
 * @param string  $contact_id   ID contact
 */
function automatorwp_activecampaign_get_contact_tags( $contact_id ){

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return array( 'contactTags' => array() );
    }

    $response = wp_remote_get( $api['url'] . '/api/3/contacts/' . $contact_id . '/contactTags', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        )
    ));

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response;
}

/**
 * Get ActiveCampaign contact tags.
 *
 * @since 1.0.0
 *
 * @param string  $tag_id       ID tag associated to contact
 */
function automatorwp_activecampaign_delete_contact_tags( $tag_id ) {

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_request( $api['url'] . '/api/3/contactTags/' . $tag_id, array(
        'method' => 'DELETE',
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        )
    ));

}

/**
 * Get lists from ActiveCampaign
 *
 * @param stdClass $field
 * 
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_activecampaign_options_cb_list( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any list', 'automatorwp-activecampaign' );
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
            
            $options[$list_id] = automatorwp_activecampaign_get_list_name( $list_id );
        }
    }

    return $options;

}

/**
 * Get lists from ActiveCampaign
 *
 * @since 1.0.0
 *
 * @param string $search
 * @param int $page
 * 
 * @return array
 */
function automatorwp_activecampaign_get_lists( $search = '', $page = 1 ) {

    $lists = array();

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return $options;
    }

    $limit = 20;
    $offset = $limit * ($page - 1);

    $response = wp_remote_get( $api['url'] . '/api/3/lists', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        ),
        'body' => array(
            'search' => $search,
            'limit' => $limit,
            'offset' => $offset,
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    foreach ( $response['lists'] as $list_id ){
        $lists[] = array(
            'id' => $list_id['id'],
            'name' => $list_id['name'],
        );
        
    }

    return $lists;

}

/**
 * Get list name
 *
 * @since 1.0.0
 *
 * @param int    $list_id         ID list
 * 
 */
function automatorwp_activecampaign_get_list_name( $list_id ) {

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return '';
    }

    $response = wp_remote_get( $api['url'] . '/api/3/lists/' . $list_id, array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        )
    ) );
    
    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response['list']['name'];

}


/**
 * Get ActiveCampaign contact Lists associated to contact.
 *
 * @since 1.0.0
 *
 * @param string  $contact_id   ID contact
 */
function automatorwp_activecampaign_get_contact_lists( $contact_id ) {

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return array( 'contactLists' => array() );
    }

    $response = wp_remote_get( $api['url'] . '/api/3/contacts/' . $contact_id . '/contactLists', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    return $response;

}

/**
 * Subscribe or Unsubscribe users in lists
 *
 * @since 1.0.0
 *
 * @param string    $list_id        ID list
 * @param string    $contact_id     ID contact
 * @param string    $status         Subscribe='1'; Unsubscribe='2'
 */
function automatorwp_activecampaign_subscription_lists( $list_id, $contact_id, $status ) {

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return;
    }

    $response = wp_remote_post( $api['url'] . '/api/3/contactLists', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode( array(
            'contactList' => array(
                'list' => $list_id,
                'contact' => $contact_id,
                'status' => $status // '1' subscribe, '2' unsubscribe
            )
        ) )
    ));
}

/**
 * Get custom contact fields from ActiveCampaign
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_activecampaign_options_cb_fields( ) {

    $options = array();

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return $options;
    }

    $response = wp_remote_get( $api['url'] . '/api/3/fields', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    foreach ( $response['fields'] as $field ){
        $options[$field['id']] = $field['title'];
    }

    return $options;

}

/**
 * Update custom contact fields from ActiveCampaign
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_activecampaign_update_custom_field( $ACfield_id, $contact_id, $field_value ) {

    $api = automatorwp_activecampaign_get_api();

    if( ! $api ) {
        return $options;
    }

    $response = wp_remote_post( $api['url'] . '/api/3/fieldValues', array(
        'headers' => array(
            'Accept' => 'application/json',
            'Api-Token' => $api['key'],
            'Content-Type'  => 'application/json'
        ),
        'body' => json_encode( array(
            'fieldValue' => array(
                'contact' => $contact_id,
                'field'=> $ACfield_id,
                'value'=> $field_value
            )
        ) )
    ) );

    return $response;
}