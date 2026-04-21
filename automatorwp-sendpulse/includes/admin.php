<?php
/**
 * Admin Settings for Sendpulse Integration
 *
 * @package     AutomatorWP\Integrations\Sendpulse\Admin
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string $option_name
 * @param bool   $default
 *
 * @return mixed
 */
function automatorwp_sendpulse_get_option($option_name, $default = false) {
    $prefix = 'automatorwp_sendpulse_';
    return get_option($prefix . $option_name, $default);
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_sendpulse_settings_sections($automatorwp_settings_sections) {
    $automatorwp_settings_sections['sendpulse'] = array(
        'title' => __('Sendpulse', 'automatorwp-sendpulse'),
        'icon' => 'dashicons-sendpulse',
    );
    return $automatorwp_settings_sections;
}
add_filter('automatorwp_settings_sections', 'automatorwp_sendpulse_settings_sections');

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_sendpulse_settings_meta_boxes($meta_boxes) {
    $prefix = 'automatorwp_sendpulse_';

    $meta_boxes['automatorwp-sendpulse-settings'] = array(
        'title' => automatorwp_dashicon('sendpulse') . __('Sendpulse', 'automatorwp-sendpulse'),
        'fields' => apply_filters('automatorwp_sendpulse_settings_fields', array(
            $prefix . 'application_id' => array(
                'name' => __('App ID:', 'automatorwp-sendpulse'),
                'desc' => __('Your Sendpulse App ID.', 'automatorwp-sendpulse'),
                'type' => 'text',
            ),
            $prefix . 'application_secret' => array(
                'name' => __('App Secret:', 'automatorwp-sendpulse'),
                'desc' => __('Your Sendpulse App Secret.', 'automatorwp-sendpulse'),
                'type' => 'text',
            ),
            
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_sendpulse_authorize_display_cb',
            ),
            $prefix . 'webhook_secret' => array(
                'name' => __('Webhook secret (optional):', 'automatorwp-sendpulse'),
                'desc' => __('Shared secret to verify HMAC signatures sent by SendPulse. Leave empty if you prefer token fallback.', 'automatorwp-sendpulse'),
                'type' => 'text',
            ),
            $prefix . 'webhook_token' => array(
                'name' => __('Webhook token (optional):', 'automatorwp-sendpulse'),
                'desc' => __('Simple token to validate incoming webhooks via header X-SendPulse-Token or ?token=. Used as fallback when HMAC is not available.', 'automatorwp-sendpulse'),
                'type' => 'text',
                'render_row_cb' => 'automatorwp_sendpulse_webhook_token_display_cb',
            ),
        )),
    );

    return $meta_boxes;
}
add_filter('automatorwp_settings_sendpulse_meta_boxes', 'automatorwp_sendpulse_settings_meta_boxes');

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_sendpulse_authorize_display_cb($field_args, $field) {
    
    $access_valid = automatorwp_sendpulse_get_option('access_valid');

    $application_id = automatorwp_sendpulse_get_option('application_id');
    // Use the same tab value as the JS redirect to ensure the provider-registered
    // Redirect URI matches exactly.
    $redirect_uri = urlencode(admin_url('admin.php?page=automatorwp_settings&tab=sendpulse'));
    $oauth_url = "https://www.sendpulse.com/v22.0/dialog/oauth?client_id={$application_id}&redirect_uri={$redirect_uri}&scope=leads_retrieval,pages_show_list,pages_read_engagement";


    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-sendpulse-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __('Connect with Sendpulse:', 'automatorwp-sendpulse'); ?></label>
        </div>
        <div class="cmb-td">
            <button id="automatorwp_sendpulse_authorize" class="button button-primary">
    <?php echo __('Connect with Sendpulse', 'automatorwp-sendpulse'); ?>
</button>

                <p style="margin-top:8px;">
                    <a id="awp_sendpulse_debug_oauth" href="<?php echo esc_url( $oauth_url ); ?>" target="_blank"><?php echo __('Open OAuth URL (debug)', 'automatorwp-sendpulse'); ?></a>
                </p>

                <p style="margin-top:8px; font-size: 12px; color: #666;">Click "Connect with Sendpulse" to exchange your App ID/Secret server-side and obtain an access token.</p>

            <?php if ( $access_valid ) { ?>
                <input type="button" name="automatorwp_remove_sendpulse_oauth" id="automatorwp_remove_sendpulse_oauth" value="<?php echo __('Delete Credentials', 'automatorwp-sendpulse'); ?>" class="button button-danger" /><br>
                <script>
                document.getElementById('automatorwp_remove_sendpulse_oauth').addEventListener('click', function(event){
                    event.preventDefault();
                    if ( ! confirm( '<?php echo esc_js( __( 'Are you sure you want to delete SendPulse credentials?', 'automatorwp-sendpulse' ) ); ?>' ) ) {
                        return;
                    }
                    jQuery.post( automatorwp_sendpulse.ajax_url, {
                        // Use the server-side action name expected by the AJAX handler
                        action: 'automatorwp_remove_sendpulse_oauth',
                        nonce: automatorwp_sendpulse.nonce
                    }, function( response ) {
                        if ( response && response.success ) {
                            location.reload();
                        } else {
                            alert( '<?php echo esc_js( __( 'Unable to remove credentials', 'automatorwp-sendpulse' ) ); ?>' );
                        }
                    }, 'json' );
                });
                </script>
            <?php } ?>
            <p id='awp_sendpulse_oauth_status'></p>
        </div>
    </div>
    <?php
}


/**
 * Display callback for the webhook token field with generate/copy/test buttons
 */
function automatorwp_sendpulse_webhook_token_display_cb( $field_args, $field ) {
    $token = automatorwp_sendpulse_get_option( 'webhook_token', '' );
    $rest_url = rest_url( 'automatorwp-sendpulse/v1/webhook' );
    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-sendpulse-webhook-token table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo esc_html( $field_args['name'] ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="text" id="automatorwp_sendpulse_webhook_token" name="<?php echo esc_attr( $field->args['id'] ); ?>" value="<?php echo esc_attr( $token ); ?>" style="width:60%;" />
            <button id="automatorwp_sendpulse_generate_token" class="button"><?php _e( 'Generate', 'automatorwp-sendpulse' ); ?></button>
            <button id="automatorwp_sendpulse_copy_token" class="button"><?php _e( 'Copy', 'automatorwp-sendpulse' ); ?></button>
            <button id="automatorwp_sendpulse_test_webhook" class="button"><?php _e( 'Test webhook', 'automatorwp-sendpulse' ); ?></button>
            <p style="margin-top:8px; font-size:12px; color:#666;"><?php echo esc_html( $field_args['desc'] ); ?></p>
            <p style="margin-top:6px; font-size:12px; color:#333;"><?php _e( 'Webhook endpoint URL:', 'automatorwp-sendpulse' ); ?> <code><?php echo esc_html( $rest_url ); ?></code></p>
            <div id="automatorwp_sendpulse_webhook_test_result" style="margin-top:8px; display:none;"></div>
        </div>
    </div>
    <?php
}

/**
 * Handle OAuth response from sendpulse
 *
 * @since  1.0.0
 */
function automatorwp_sendpulse_handle_oauth_response() {
    if (isset($_GET['code']) && isset($_GET['page']) && $_GET['page'] === 'automatorwp_settings') {
        $code = sanitize_text_field($_GET['code']);

        $application_id = automatorwp_sendpulse_get_option('application_id');
        $application_secret = automatorwp_sendpulse_get_option('application_secret');
        $redirect_uri = admin_url('admin.php?page=automatorwp_settings&tab=opt-tab-sendpulse');

        $token_url = "https://graph.sendpulse.com/v12.0/oauth/access_token?client_id=$application_id&redirect_uri=" . urlencode($redirect_uri) . "&client_secret=$application_secret&code={$code}";

        $response = wp_remote_get($token_url);
        
        if (is_wp_error($response)) {
            error_log('Sendpulse OAuth Error: ' . $response->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['access_token'])) {
            error_log('Sendpulse OAuth Error: No access token received.');
            return;
        }

        $access_token = sanitize_text_field($data['access_token']);

        // 🔹 Obtener el Page ID automáticamente
        $page_url = "https://graph.sendpulse.com/v12.0/me/accounts?access_token={$access_token}";
        $page_response = wp_remote_get($page_url);

        if (is_wp_error($page_response)) {
            error_log('Sendpulse Page Fetch Error: ' . $page_response->get_error_message());
            return;
        }

        $page_body = wp_remote_retrieve_body($page_response);
        $page_data = json_decode($page_body, true);

        if (!isset($page_data['data']) || empty($page_data['data'])) {
            error_log('Sendpulse Page Fetch Error: No pages found.');
            return;
        }

        // 🔹 Guardar el ID de la primera página (puedes mejorarlo con una selección más avanzada)
        $page_id = sanitize_text_field($page_data['data'][0]['id']);
        // Page ID is not sensitive but store with no-autoload to reduce exposure
        automatorwp_sendpulse_set_option_noautoload( 'automatorwp_sendpulse_page_id', $page_id );

        // 🔹 Guardar los datos solo si todo fue exitoso
        // Access token is sensitive — store without autoload
        automatorwp_sendpulse_set_option_noautoload( 'automatorwp_sendpulse_access_token', $access_token );
        automatorwp_sendpulse_set_option_noautoload( 'automatorwp_sendpulse_access_valid', true );
    }
}
    add_action('admin_init', 'automatorwp_sendpulse_handle_oauth_response');

/**
 * Remove Sendpulse credentials
 *
 * @since  1.0.0
 */
function automatorwp_sendpulse_remove_credentials() {
    if (isset($_POST['action']) && $_POST['action'] === 'automatorwp_remove_sendpulse_oauth') {
        // Use the same nonce action used across the plugin's AJAX handlers
        check_ajax_referer('automatorwp_admin', 'nonce');

        delete_option('automatorwp_sendpulse_access_token');
        delete_option('automatorwp_sendpulse_access_valid');

        wp_send_json_success();
    }
}
add_action('wp_ajax_automatorwp_remove_sendpulse_oauth', 'automatorwp_sendpulse_remove_credentials');

/**
 * Fetch Sendpulse Leads
 *
 * @since  1.0.0
 */
function automatorwp_sendpulse_fetch_leads() {
    $access_token = automatorwp_sendpulse_get_option('access_token');
    $page_id = automatorwp_sendpulse_get_option('page_id'); // You need to store the Page ID in settings.

    if (!$access_token || !$page_id) {
        return false;
    }

    $url = "https://graph.sendpulse.com/v12.0/$page_id/leadgen_forms?access_token=$access_token";
    $response = wp_remote_get($url);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['data'])) {
        foreach ($data['data'] as $form) {
            $form_id = $form['id'];
            $leads_url = "https://graph.sendpulse.com/v12.0/$form_id/leads?access_token=$access_token";
            $leads_response = wp_remote_get($leads_url);
            $leads_body = wp_remote_retrieve_body($leads_response);
            $leads_data = json_decode($leads_body, true);

            if (isset($leads_data['data'])) {
                // Process leads here (e.g., save to database).
                foreach ($leads_data['data'] as $lead) {
                    // Example: Save lead data to custom table or post type.
                }
            }
        }
    }
}