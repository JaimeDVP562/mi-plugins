<?php
/**
 * Plugin Name:           AutomatorWP - Keap
 * Plugin URI:            https://automatorwp.com/add-ons/keap/
 * Description:           Connect AutomatorWP with Keap CRM.
 * Version:               1.1.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-keap
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.7
 * Requires PHP:          7.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Keap
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Keap {

    /**
     * @var         AutomatorWP_Keap $instance The one true AutomatorWP_Keap
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Keap self::$instance The one true AutomatorWP_Keap
     */
    public static function instance() {

        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Keap();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function constants() {

        // Plugin version
        define( 'AUTOMATORWP_KEAP_VER', '1.1.0' );

        // Plugin file
        define( 'AUTOMATORWP_KEAP_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_KEAP_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_KEAP_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if ( $this->meets_requirements() ) {

            // Core includes — order matters: logger first, then cache, then API
            require_once AUTOMATORWP_KEAP_DIR . 'includes/logger.php';
            require_once AUTOMATORWP_KEAP_DIR . 'includes/cache.php';
            require_once AUTOMATORWP_KEAP_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_KEAP_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_KEAP_DIR . 'includes/api-functions.php';
            require_once AUTOMATORWP_KEAP_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_KEAP_DIR . 'includes/scripts.php';

            // Actions
            require_once AUTOMATORWP_KEAP_DIR . 'includes/actions/create-contact.php';
            require_once AUTOMATORWP_KEAP_DIR . 'includes/actions/update-contact.php';
            require_once AUTOMATORWP_KEAP_DIR . 'includes/actions/add-contact-tag.php';
            require_once AUTOMATORWP_KEAP_DIR . 'includes/actions/add-to-campaign.php';

            // Triggers
            require_once AUTOMATORWP_KEAP_DIR . 'includes/triggers/contact-added.php';

        }
    }

    /**
     * Setup plugin hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks() {

        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );

        add_filter( 'automatorwp_licenses_meta_boxes', array( $this, 'license' ) );

        // Activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action( 'init', array( $this, 'load_textdomain' ) );
    }

    /**
     * Registers this integration with AutomatorWP
     *
     * @since 1.0.0
     */
    public function register_integration() {

        automatorwp_register_integration( 'keap', array(
            'label' => 'Keap',
            'icon'  => AUTOMATORWP_KEAP_URL . 'assets/img/keap.svg',
        ) );
    }

    /**
     * Licensing
     *
     * @since 1.0.0
     *
     * @param array $meta_boxes
     *
     * @return array
     */
    public function license( $meta_boxes ) {

        $meta_boxes['automatorwp-keap-license'] = array(
            'title'  => 'Keap',
            'fields' => array(
                'automatorwp_keap_license' => array(
                    'type'      => 'edd_license',
                    'file'      => AUTOMATORWP_KEAP_FILE,
                    'item_name' => 'Keap',
                ),
            ),
        );

        return $meta_boxes;
    }

    /**
     * Activation hook
     *
     * @since 1.0.0
     */
    public function activate() {

        if ( $this->meets_requirements() ) {
            // Clear any existing Keap caches on activation
            if ( function_exists( 'automatorwp_keap_clear_cache' ) ) {
                automatorwp_keap_clear_cache();
            }
        }
    }

    /**
     * Deactivation hook
     *
     * @since 1.0.0
     */
    public function deactivate() {

        // Clear caches on deactivation
        if ( function_exists( 'automatorwp_keap_clear_cache' ) ) {
            automatorwp_keap_clear_cache();
        }
    }

    /**
     * Plugin admin notices
     *
     * @since 1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - Keap requires %s in order to work. Please install and activate it.', 'automatorwp-keap' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>'
                    ); ?>
                </p>
            </div>

            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>

        <?php endif;
    }

    /**
     * Check if plugin requirements are met
     *
     * @since 1.0.0
     *
     * @return bool True if all requirements are met
     */
    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Internationalization
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain() {

        $lang_dir = AUTOMATORWP_KEAP_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_keap_languages_directory', $lang_dir );

        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-keap' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-keap', $locale );

        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/automatorwp-keap/' . $mofile;

        if ( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-keap/ folder
            load_textdomain( 'automatorwp-keap', $mofile_global );
        } elseif ( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-keap/languages/ folder
            load_textdomain( 'automatorwp-keap', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-keap', false, $lang_dir );
        }
    }
}

/**
 * Returns the one true AutomatorWP_Keap instance
 *
 * @since       1.0.0
 * @return      AutomatorWP_Keap
 */
function AutomatorWP_Keap() {
    return AutomatorWP_Keap::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Keap' );