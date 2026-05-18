<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\Constant_Contact\Admin
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 */
function automatorwp_constant_contact_get_option( $option_name, $default = false ) {
    $prefix = 'automatorwp_constant_contact_';
    return automatorwp_get_option( $prefix . $option_name, $default );
}

/**
 * Register plugin settings sections
 */
function automatorwp_constant_contact_settings_sections( $sections ) {
    $sections['constant_contact'] = array(
        'title' => __( 'Constant Contact', 'automatorwp-constant-contact' ),
        'icon'  => 'dashicons-email-alt',
    );
    return $sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_constant_contact_settings_sections' );

/**
 * Register plugin settings meta boxes
 */
function automatorwp_constant_contact_settings_meta_boxes( $meta_boxes ) {
    $prefix = 'automatorwp_constant_contact_';

    $meta_boxes['automatorwp-constant-contact-settings'] = array(
        'title'  => automatorwp_dashicon( 'groups' ) . __( 'Constant Contact', 'automatorwp-constant-contact' ),
        'fields' => apply_filters( 'automatorwp_constant_contact_settings_fields', array(
            $prefix . 'client_id'     => array(
                'name' => __( 'Client ID:', 'automatorwp-constant-contact' ),
                'desc' => __( 'Your Constant Contact API client ID.', 'automatorwp-constant-contact' ),
                'type' => 'text',
            ),
            $prefix . 'client_secret' => array(
                'name' => __( 'Client Secret:', 'automatorwp-constant-contact' ),
                'desc' => __( 'Your Constant Contact API client secret.', 'automatorwp-constant-contact' ),
                'type' => 'text',
            ),
            $prefix . 'redirect_url'  => array(
                'type'            => 'text',
                'render_row_cb'   => 'automatorwp_constant_contact_redirect_url_display_cb',
            ),
            $prefix . 'authorize'     => array(
                'type'            => 'text',
                'render_row_cb'   => 'automatorwp_constant_contact_authorize_display_cb',
            ),
        ) ),
    );

    return $meta_boxes;
}
add_filter( 'automatorwp_settings_constant_contact_meta_boxes', 'automatorwp_constant_contact_settings_meta_boxes' );

/**
 * Display callback for the redirect URL setting
 */
function automatorwp_constant_contact_redirect_url_display_cb( $field_args, $field ) {
    $admin_url = admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-constant_contact' );
    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-constant-contact-redirect-url table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo __( 'Redirect URL:', 'automatorwp-constant-contact' ); ?></label>
        </div>
        <div class="cmb-td">
            <input type="text" class="regular-text" value="<?php echo esc_url( $admin_url ); ?>" readonly>
            <p class="cmb2-metabox-description"><?php echo __( 'Copy this URL and place it in the authorized redirect URIs field in your Constant Contact account.', 'automatorwp-constant-contact' ); ?></p>
        </div>
    </div>
    <?php
}

/**
 * Display callback for the authorization setting
 */
function automatorwp_constant_contact_authorize_display_cb( $field_args ) {
    $field_id = esc_attr( $field_args['id'] );
    $client_id = automatorwp_constant_contact_get_option( 'client_id', '' );
    $client_secret = automatorwp_constant_contact_get_option( 'client_secret', '' );
    $access_token = get_option( 'constant_contact_access_token', '' );

    if ( empty( $client_id ) || empty( $client_secret ) ) {
        echo '<p style="color: red;">' . __( 'You must enter the Client ID and Client Secret before authorizing.', 'automatorwp-constant-contact' ) . '</p>';
        return;
    }

    $redirect_uri = admin_url( 'admin.php?page=automatorwp_settings&tab=opt-tab-constant_contact' );

    // Build the authorization URL
    $authorize_url = add_query_arg( array(
        'client_id'     => $client_id,
        'redirect_uri'  => $redirect_uri,
        'response_type' => 'code',
        'scope'         => 'contact_data campaign_data offline_access',
    ), 'https://authz.constantcontact.com/oauth2/default/v1/authorize' );


    echo $authorize_url;
    ?>

    <div>
        <?php if ( ! empty( $access_token ) ) : ?>
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="<?php echo esc_url( $authorize_url ); ?>"><?php echo __( 'Reauthorize Constant Contact', 'automatorwp-constant-contact' ); ?></a>
            <p><?php echo __( 'You have already connected your Constant Contact account.', 'automatorwp-constant-contact' ); ?></p>
        <?php else : ?>
            <a id="<?php echo $field_id; ?>" class="button button-primary" href="<?php echo esc_url( $authorize_url ); ?>"><?php echo __( 'Authorize Constant Contact', 'automatorwp-constant-contact' ); ?></a>
        <?php endif; ?>
    </div>
    <?php
}