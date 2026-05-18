<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Google_Sheets\Admin
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed
 */
function automatorwp_google_sheets_get_option( $option_name, $default = false ) {

    $prefix = 'automatorwp_google_sheets_';

    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_google_sheets_settings_sections( $automatorwp_settings_sections ) {

    $automatorwp_settings_sections['google_sheets'] = array(
        'title' => __( 'Google Sheets', 'automatorwp-google-sheets' ),
        'icon' => 'dashicons-media-spreadsheet',
    );

    return $automatorwp_settings_sections;

}
add_filter( 'automatorwp_settings_sections', 'automatorwp_google_sheets_settings_sections' );

/**
 * Register plugin settings meta boxes
 *
 * @since  1.0.0
 *
 * @return array
 */
function automatorwp_google_sheets_settings_meta_boxes( $meta_boxes )  {

    $prefix = 'automatorwp_google_sheets_';

    $meta_boxes['automatorwp-google-sheets-settings'] = array(
        'title' => automatorwp_dashicon( 'groups' ) . __( 'Google Sheets', 'automatorwp-google-sheets' ),
        'fields' => apply_filters( 'automatorwp_google_sheets_settings_fields', array(
            $prefix . 'client_id' => array(
                'name' => __( 'Client ID:', 'automatorwp-google-sheets' ),
                'desc' => __( 'Your Google API client ID.', 'automatorwp-google-sheets' ),
                'type' => 'text',
            ),
            $prefix . 'client_secret' => array(
                'name' => __( 'Client Secret:', 'automatorwp-google-sheets' ),
                'desc' => __( 'Your Google API client secret.', 'automatorwp-google-sheets' ),
                'type' => 'text',
            ),
            $prefix . 'redirect_url' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_google_sheets_redirect_url_display_cb',
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_google_sheets_authorize_display_cb',
            ),
        ) ),
    );

    return $meta_boxes;

}
add_filter( "automatorwp_settings_google_sheets_meta_boxes", 'automatorwp_google_sheets_settings_meta_boxes' );

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_google_sheets_redirect_url_display_cb( $field_args, $field ) {
    $admin_url = str_replace( 'http://', 'http://', get_admin_url() )  . 'admin.php?page=automatorwp_settings&tab=opt-tab-google_sheets';

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-google-sheets-redirect-url table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Redirect URL:', 'automatorwp-google-sheets' ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="text" class="regular-text" value="<?php echo $admin_url; ?>" readonly>
            <p class="cmb2-metabox-description"><?php echo __( 'Copy this URL and place it the authorized redirect URIs field.', 'automatorwp-google-sheets' ); ?></p>
        </div>
    </div>
    <?php
}

/**
 * Display callback for the authorize setting
 *
 * @since  1.0.0
 *
 * @param array      $field_args Array of field arguments.
 * @param CMB2_Field $field      The field object
 */
function automatorwp_google_sheets_authorize_display_cb( $field_args, $field ) {

    $field_id = $field_args['id'];
    
    $client_id = automatorwp_google_sheets_get_option( 'client_id', '' );
    $client_secret = automatorwp_google_sheets_get_option( 'client_secret', '' );
    $auth = get_option( 'automatorwp_google_sheets_auth' );

    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-google-sheets-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Connect with Google Sheets:', 'automatorwp-google-sheets' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="#"><?php echo __( 'Authorize', 'automatorwp-google-sheets' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Add you Google API client ID and secret fields and click on "Authorize" button to generate access keys for this site.', 'automatorwp-google-sheets' ); ?></p>
            <?php if ( is_array( $auth ) ) : ?>
                <div class="automatorwp-notice-success"><?php echo __( 'Site connected with Google Sheets successfully.', 'automatorwp-google-sheets' ); ?></div>
                <p class="automatorwp-google-sheets-access-token"><strong><?php echo __( 'Access token:', 'automatorwp-google-sheets' ); ?></strong> <input type="text" value="<?php echo $auth['access_token']; ?>" readonly></p>
                <p class="automatorwp-google-sheets-refresh-token"><strong><?php echo __( 'Refresh token:', 'automatorwp-google-sheets' ); ?></strong> <input type="text" value="<?php echo $auth['refresh_token']; ?>" readonly></p>
            <?php elseif( ! empty( $client_id ) && ! empty( $client_secret ) ) : ?>
                <div class="automatorwp-notice-error"><?php echo __( 'Site not connected with Google Sheets.', 'automatorwp-google-sheets' ); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Check if authorization process has been completed
 *
 * @since  1.0.0
 */
function automatorwp_google_sheets_maybe_authorize_complete() {


    if( isset( $_GET['code'] )
        && isset( $_GET['page'] ) && $_GET['page'] == 'automatorwp_settings'
        && isset( $_GET['tab'] ) && $_GET['tab'] == 'opt-tab-google_sheets' ) {

        
        $client_id = automatorwp_google_sheets_get_option( 'client_id', '' );
        $client_secret = automatorwp_google_sheets_get_option( 'client_secret', '' );

        $params = array(
            'headers' => array(
                'Content-Type'  => 'application/x-www-form-urlencoded; charset=utf-8',
                'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ),
                'Accept'        => 'application/json',
            ),
            'body'  => array(
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => str_replace( 'http://', 'http://', get_admin_url() ) . 'admin.php?page=automatorwp_settings&tab=opt-tab-google_sheets',
                'code'          => $_GET['code']
            )
        );

        $response = wp_remote_post( 'https://www.googleapis.com/oauth2/v4/token', $params );

        // Bail if can't contact with the server
        if ( is_wp_error( $response ) ) {
            return;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ) );

        // Bail on receive an error
        if( isset( $body->error ) ) {
            return;
        }

        $auth = array(
            'access_token'  => $body->access_token,
            'refresh_token' => $body->refresh_token,
            'token_type'    => $body->token_type,
            'expires_in'    => $body->expires_in,
            'scope'         => $body->scope,
        );

        // Update the access and refresh tokens
        update_option( 'automatorwp_google_sheets_auth', $auth );

        // Redirect to settings again
        wp_redirect( get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-google_sheets' );
        exit;

    }

}
add_action( 'admin_init', 'automatorwp_google_sheets_maybe_authorize_complete' );