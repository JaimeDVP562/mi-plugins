<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function automatorwp_mailmint_get_option( $name, $default = '' ) {
    $settings = get_option( 'automatorwp_settings', array() );
    $key = 'automatorwp_mailmint_' . $name;
    return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

function automatorwp_mailmint_settings_sections( $sections ) {
    $sections['mailmint'] = array(
        'title' => __( 'Mail Mint', 'automatorwp-funnels-mail-mint' ),
        'icon'  => 'dashicons-email',
    );
    return $sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_mailmint_settings_sections' );

function automatorwp_mailmint_settings_meta_boxes( $meta_boxes ) {
    $prefix = 'automatorwp_mailmint_';
    $meta_boxes['automatorwp-mailmint-settings'] = array(
        'title' => __( 'Mail Mint', 'automatorwp-funnels-mail-mint' ),
        'fields' => array(
            $prefix . 'auth_method' => array(
                'name' => __( 'Auth method', 'automatorwp-funnels-mail-mint' ),
                'type' => 'select',
                'options' => array(
                    'internal' => __( 'Use internal Mail Mint plugin functions (preferred)', 'automatorwp-funnels-mail-mint' ),
                    'api' => __( 'API Key', 'automatorwp-funnels-mail-mint' ),
                ),
                'default' => 'internal',
            ),
            $prefix . 'api_key' => array(
                'name' => __( 'API Key', 'automatorwp-funnels-mail-mint' ),
                'type' => 'text',
            ),
            $prefix . 'api_base' => array(
                'name' => __( 'API Base URL', 'automatorwp-funnels-mail-mint' ),
                'type' => 'text',
                'desc' => __( 'Ej: https://api.mailmint.example/v1 (opcional si usas funciones internas)', 'automatorwp-funnels-mail-mint' )
            ),
            $prefix . 'authorize' => array(
                'type' => 'text',
                'render_row_cb' => 'automatorwp_mailmint_authorize_display_cb',
            ),
        ),
    );
    return $meta_boxes;
}
add_filter( 'automatorwp_settings_mailmint_meta_boxes', 'automatorwp_mailmint_settings_meta_boxes' );

function automatorwp_mailmint_authorize_display_cb( $field_args, $field ) {
    ?>
    <div class="cmb-row">
        <div class="cmb-th"><label><?php echo __( 'Connect Mail Mint', 'automatorwp-funnels-mail-mint' ); ?></label></div>
        <div class="cmb-td">
            <a id="automatorwp_mailmint_authorize_btn" class="button button-primary" href="#"><?php echo __( 'Save / Validate', 'automatorwp-funnels-mail-mint' ); ?></a>
            <p class="cmb2-metabox-description"><?php echo __( 'Guarda y valida las credenciales. Si usas funciones internas, la validación comprobará que existen.', 'automatorwp-funnels-mail-mint' ); ?></p>
        </div>
    </div>
    <?php
}
