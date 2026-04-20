<?php
/**
 * Settings page (admin).
 *
 * @package AutomatorWP_Grok
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Option name for the API key.
 *
 * @return string
 */
function automatorwp_grok_api_key_option_name() {
    return 'automatorwp_grok_api_key';
}

/**
 * Add settings menu entries.
 *
 * @return void
 */
function automatorwp_grok_add_settings_menus() {

    if ( ! is_admin() ) {
        return;
    }

    $page_title = __( 'Grok (xAI) Settings', 'automatorwp-grok' );
    $menu_title = __( 'Grok (xAI)', 'automatorwp-grok' );
    $capability = 'manage_options';
    $menu_slug  = 'automatorwp-grok-settings';
    $callback   = 'automatorwp_grok_render_settings_page';

    add_submenu_page(
        'automatorwp',
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $callback
    );

    add_submenu_page(
        'automatorwp_settings',
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $callback
    );

    add_options_page(
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $callback
    );
}
add_action( 'admin_menu', 'automatorwp_grok_add_settings_menus', 99 );

/**
 * Register the API key option.
 *
 * @return void
 */
function automatorwp_grok_register_option() {
    register_setting(
        'automatorwp_grok_settings_group',
        automatorwp_grok_api_key_option_name(),
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        )
    );
}
add_action( 'admin_init', 'automatorwp_grok_register_option' );

/**
 * Render settings page.
 *
 * @return void
 */
function automatorwp_grok_render_settings_page() {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $api_key = get_option( automatorwp_grok_api_key_option_name(), '' );
    $nonce   = wp_create_nonce( 'automatorwp_grok_verify' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__( 'Grok (xAI) Settings', 'automatorwp-grok' ); ?></h1>

        <form method="post" action="options.php">
            <?php settings_fields( 'automatorwp_grok_settings_group' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="automatorwp-grok-api-key"><?php echo esc_html__( 'API Key', 'automatorwp-grok' ); ?></label>
                    </th>
                    <td>
                        <input
                            type="text"
                            class="regular-text"
                            id="automatorwp-grok-api-key"
                            name="<?php echo esc_attr( automatorwp_grok_api_key_option_name() ); ?>"
                            value="<?php echo esc_attr( $api_key ); ?>"
                            placeholder="xai-..."
                            autocomplete="off"
                        />
                        <p class="description"><?php echo esc_html__( 'Enter your xAI API key.', 'automatorwp-grok' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php echo esc_html__( 'Verify connection', 'automatorwp-grok' ); ?></th>
                    <td>
                        <button type="button" class="button" id="automatorwp-grok-verify" data-nonce="<?php echo esc_attr( $nonce ); ?>">
                            <?php echo esc_html__( 'Verify', 'automatorwp-grok' ); ?>
                        </button>
                        <p class="description" id="automatorwp-grok-verify-result" style="margin-top:8px;"></p>
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Save Settings', 'automatorwp-grok' ) ); ?>
        </form>
    </div>
    <?php
}

/**
 * Enqueue admin assets only on our settings page.
 *
 * @param string $hook Current admin page hook.
 * @return void
 */
function automatorwp_grok_admin_enqueue_assets( $hook ) {

    if ( false === strpos( $hook, 'automatorwp-grok-settings' ) ) {
        return;
    }

    wp_enqueue_script(
        'automatorwp-grok-admin',
        AUTOMATORWP_GROK_PLUGIN_URL . 'assets/admin.js',
        array(),
        AUTOMATORWP_GROK_VERSION,
        true
    );
}
add_action( 'admin_enqueue_scripts', 'automatorwp_grok_admin_enqueue_assets' );
