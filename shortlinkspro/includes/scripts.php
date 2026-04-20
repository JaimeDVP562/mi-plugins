<?php
/**
 * Scripts
 *
 * @package     ShortLinksPro\Scripts
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function shortlinkspro_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'shortlinkspro-admin-bar-css', SHORTLINKSPRO_URL . 'assets/css/shortlinkspro-admin-bar' . $suffix . '.css', array( ), SHORTLINKSPRO_VER, 'all' );

}
add_action( 'init', 'shortlinkspro_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function shortlinkspro_enqueue_scripts( $hook = null ) {



}
add_action( 'wp_enqueue_scripts', 'shortlinkspro_enqueue_scripts' );

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function shortlinkspro_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'shortlinkspro-admin-css', SHORTLINKSPRO_URL . 'assets/css/shortlinkspro-admin' . $suffix . '.css', array( ), SHORTLINKSPRO_VER, 'all' );
    wp_register_style( 'shortlinkspro-admin-rtl-css', SHORTLINKSPRO_URL . 'assets/css/shortlinkspro-admin-rtl' . $suffix . '.css', array( ), SHORTLINKSPRO_VER, 'all' );
    wp_register_style( 'shortlinkspro-flags-css', SHORTLINKSPRO_URL . 'assets/lib/flags/flags.min.css', array( ), SHORTLINKSPRO_VER, 'all' );

    // Scripts
    wp_register_script( 'shortlinkspro-admin-js', SHORTLINKSPRO_URL . 'assets/js/shortlinkspro-admin' . $suffix . '.js', array( 'jquery', 'jquery-ui-dialog' ), SHORTLINKSPRO_VER, true );
    wp_register_script( 'shortlinkspro-admin-notices-js', SHORTLINKSPRO_URL . 'assets/js/shortlinkspro-admin-notices' . $suffix . '.js', array( 'jquery' ), SHORTLINKSPRO_VER, true );
    wp_register_script( 'shortlinkspro-chart-js', SHORTLINKSPRO_URL . 'assets/js/chart.min.js', SHORTLINKSPRO_VER, true );
    wp_register_script( 'shortlinkspro-admin-clicks-js', SHORTLINKSPRO_URL . 'assets/js/shortlinkspro-admin-clicks' . $suffix . '.js', array( 'jquery', 'shortlinkspro-chart-js' ), SHORTLINKSPRO_VER, true );
    wp_register_script( 'shortlinkspro-admin-tools-js', SHORTLINKSPRO_URL . 'assets/js/shortlinkspro-admin-tools' . $suffix . '.js', array( 'jquery' ), SHORTLINKSPRO_VER, true );

}
add_action( 'admin_init', 'shortlinkspro_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 *
 * @param string $hook
 *
 * @return      void
 */
function shortlinkspro_admin_enqueue_scripts( $hook ) {

    // Fix for incorrect prefix
    $hook = str_replace( 'shortlinks-pro', 'shortlinkspro', $hook );

    // Stylesheets
    wp_enqueue_style( 'shortlinkspro-admin-css' );
    wp_enqueue_style( 'shortlinkspro-admin-rtl-css' );
    wp_enqueue_style( 'shortlinkspro-flags-css' );

    // Localize admin script
    wp_localize_script( 'shortlinkspro-admin-notices-js', 'shortlinkspro_admin_notices', array(
        'nonce' => shortlinkspro_get_admin_nonce(),
    ) );

    // Scripts
    wp_enqueue_script( 'shortlinkspro-admin-notices-js' );

    // Localize admin script
    wp_localize_script( 'shortlinkspro-admin-js', 'shortlinkspro_admin', array(
        'ajaxurl'               => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
        'nonce'                 => shortlinkspro_get_admin_nonce(),
        'site_url'              => site_url('/'),
        'copy_text'             => __( 'Copy to clipboard', 'shortlinkspro' ),
        'copied_text'           => __( 'Copied!', 'shortlinkspro' ),
        'invalid_url_text'      => __( 'Please, insert a valid URL.', 'shortlinkspro' ),
        'duplicated_slug_text'  => __( 'There is a link already using this slug! Please, enter a different slug.', 'shortlinkspro' ),
    ) );

    // Scripts
    wp_enqueue_script( 'shortlinkspro-admin-js' );

    // Click screens
    if( $hook === 'shortlinkspro_page_shortlinkspro_clicks'
        || $hook === 'admin_page_edit_shortlinkspro_clicks' ) {

        // Localize clicks script
        wp_localize_script( 'shortlinkspro-admin-clicks-js', 'shortlinkspro_admin_clicks', array(
            'ajaxurl'           => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
            'nonce'             => shortlinkspro_get_admin_nonce(),
        ) );

        // Scripts
        wp_enqueue_script( 'shortlinkspro-chart-js' );
        wp_enqueue_script( 'shortlinkspro-admin-clicks-js' );

    }

    // Tools screen
    if( $hook === 'shortlinkspro_page_shortlinkspro_tools' ) {

        // Localize admin tools script
        wp_localize_script( 'shortlinkspro-admin-tools-js', 'shortlinkspro_admin_tools', array(
            'ajaxurl'               => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
            'nonce'                 => shortlinkspro_get_admin_nonce(),
            'import_from_plugin_no_plugin_text' => __( 'Please, choose at least one plugin to import from.', 'shortlinkspro' ),
            'import_from_plugin_no_data_text'   => __( 'Please, choose at least one data option to import from.', 'shortlinkspro' ),
            'import_from_plugin_importing_text' => __( 'Fetching data...', 'shortlinkspro' ),
            'import_from_plugin_finished_text'  => __( 'Data imported successfully!', 'shortlinkspro' ),
        ) );

        // Scripts
        wp_enqueue_script( 'shortlinkspro-admin-tools-js' );

    }

}
add_action( 'admin_enqueue_scripts', 'shortlinkspro_admin_enqueue_scripts' );

function shortlinkspro_dynamic_css() {

    if( ! isset( $_GET['page'] ) ) {
        return;
    }

    if( $_GET['page'] !== 'shortlinkspro_clicks' ) {
        return;
    }

    $browsers = array(
        'brave',
        'chrome',
        'chromium',
        'edge',
        'firefox',
        'hermes',
        'iceweasel',
        'internet-explorer',
        'jsdom',
        'k-meleon',
        'konqueror',
        'midori',
        'netscape',
        'netsurf',
        'opera',
        'opera-gx',
        'otter',
        'phantomjs',
        'phoenix-firebird',
        'qutebrowser',
        'rekonq',
        'safari',
        'safari-ios',
        'servo',
        'spidermonkey',
        'surf',
        'uc',
        'v8',
        'vivaldi',
    );

    $devices = array(
        'desktop',
        'laptop',
        'smartphone',
        'tablet',
    );

    $os = array(
        'android',
        'apple',
        'archlinux',
        'ios',
        'linux',
        'linux-mint',
        'mac',
        'puppy-linux',
        'ubuntu',
        'windows',
    );

    $browser_selector = '';
    $browser_before_selector = '';

    foreach( $browsers as $b ) {
        $browser_selector .= ".shortlinkspro-browser-{$b},";
        $browser_before_selector .= ".shortlinkspro-browser-{$b}:before,";
    }

    $device_selector = '';
    $device_before_selector = '';

    foreach( $devices as $d ) {
        $device_selector .= ".shortlinkspro-device-{$d},";
        $device_before_selector .= ".shortlinkspro-device-{$d}:before,";
    }

    $os_selector = '';
    $os_before_selector = '';

    foreach( $os as $o ) {
        $os_selector .= ".shortlinkspro-os-{$o},";
        $os_before_selector .= ".shortlinkspro-os-{$o}:before,";
    }

    $os_selector = rtrim( $os_selector, "," );
    $os_before_selector = rtrim( $os_before_selector, "," );
    ?>
	<style>
	<?php echo esc_html( $browser_selector . $device_selector . $os_selector ); ?> {
		font-size: 0;
        line-height: 0;
	}

    <?php echo esc_html( $browser_before_selector . $device_before_selector . $os_before_selector ); ?> {
		content: '';
        display: inline-block;
        width: 20px;
        height: 20px;
        background-size: contain;
        vertical-align: bottom;
	}

    <?php foreach( $browsers as $b ) : ?>
    .shortlinkspro-browser-<?php echo esc_html( $b ); ?>:before {
        background-image: url('<?php echo esc_html( SHORTLINKSPRO_URL ); ?>/assets/img/browser/<?php echo esc_html( $b ); ?>.svg');
    }
    <?php endforeach; ?>

    <?php foreach( $devices as $d ) : ?>
    .shortlinkspro-device-<?php echo esc_html( $d ); ?>:before {
        background-image: url('<?php echo esc_html( SHORTLINKSPRO_URL ); ?>/assets/img/device/<?php echo esc_html( $d ); ?>.svg');
    }
    <?php endforeach; ?>

    <?php foreach( $os as $o ) : ?>
	.shortlinkspro-os-<?php echo esc_html( $o ); ?>:before {
        background-image: url('<?php echo esc_html( SHORTLINKSPRO_URL ); ?>/assets/img/os/<?php echo esc_html( $o ); ?>.svg');
    }
    <?php endforeach; ?>
	</style>
	<?php
}
add_action( 'admin_head', 'shortlinkspro_dynamic_css' );

/**
 * Register and enqueue admin bar scripts
 *
 * @since       1.0.0
 * @return      void
 */
function shortlinkspro_enqueue_admin_bar_scripts() {

    wp_enqueue_style( 'shortlinkspro-admin-bar-css' );

}
add_action( 'admin_bar_init', 'shortlinkspro_enqueue_admin_bar_scripts' );

/**
 * Setup a global nonce for all frontend scripts
 *
 * @since       1.0.0
 *
 * @return      string
 */
function shortlinkspro_get_nonce() {

    if( ! defined( 'SHORTLINKSPRO_NONCE' ) )
        define( 'SHORTLINKSPRO_NONCE', wp_create_nonce( 'shortlinkspro' ) );

    return SHORTLINKSPRO_NONCE;

}

/**
 * Setup a global nonce for all admin scripts
 *
 * @since       1.0.0
 *
 * @return      string
 */
function shortlinkspro_get_admin_nonce() {

    if( ! defined( 'SHORTLINKSPRO_ADMIN_NONCE' ) )
        define( 'SHORTLINKSPRO_ADMIN_NONCE', wp_create_nonce( 'shortlinkspro_admin' ) );

    return SHORTLINKSPRO_ADMIN_NONCE;

}