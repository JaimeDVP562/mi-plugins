<?php
/**
 * Plugin Name:           AutomatorWP - DeepL
 * Plugin URI:            https://automatorwp.com/add-ons/deepl/
 * Description:           Connect AutomatorWP with DeepL.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-deepl
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.5
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\DeepL
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_DeepL {

    /**
     * @var         AutomatorWP_DeepL $instance The one true AutomatorWP_DeepL
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_DeepL self::$instance
     */
    public static function instance() {

        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_DeepL();
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

        define( 'AUTOMATORWP_DEEPL_VER',  '1.0.0' );
        define( 'AUTOMATORWP_DEEPL_FILE', __FILE__ );
        define( 'AUTOMATORWP_DEEPL_DIR',  plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_DEEPL_URL',  plugin_dir_url( __FILE__ ) );

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

            require_once AUTOMATORWP_DEEPL_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_DEEPL_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_DEEPL_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_DEEPL_DIR . 'includes/scripts.php';
            require_once AUTOMATORWP_DEEPL_DIR . 'includes/tags.php';

            // Actions
            require_once AUTOMATORWP_DEEPL_DIR . 'includes/actions/translate-text.php';
            require_once AUTOMATORWP_DEEPL_DIR . 'includes/actions/translate-html.php';
            require_once AUTOMATORWP_DEEPL_DIR . 'includes/actions/detect-language.php';
            require_once AUTOMATORWP_DEEPL_DIR . 'includes/actions/check-usage.php';

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
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action( 'init', array( $this, 'load_textdomain' ) );

    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    public function register_integration() {

        automatorwp_register_integration( 'deepl', array(
            'label' => 'DeepL',
            'icon'  => AUTOMATORWP_DEEPL_URL . 'assets/deepl.svg',
        ) );

    }

    /**
     * Licensing
     *
     * @since 1.0.0
     *
     * @param array $meta_boxes
     * @return array
     */
    public function license( $meta_boxes ) {

        $meta_boxes['automatorwp-deepl-license'] = array(
            'title'  => 'DeepL',
            'fields' => array(
                'automatorwp_deepl_license' => array(
                    'type'      => 'edd_license',
                    'file'      => AUTOMATORWP_DEEPL_FILE,
                    'item_name' => 'DeepL',
                ),
            ),
        );

        return $meta_boxes;

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
                        __( 'AutomatorWP - DeepL requires %s in order to work. Please install and activate it.', 'automatorwp-deepl' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>'
                    ); ?>
                </p>
            </div>

            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>

        <?php endif;

    }

    /**
     * Check if all plugin requirements are met
     *
     * @since 1.0.0
     * @return bool
     */
    private function meets_requirements() {

        return class_exists( 'AutomatorWP' );

    }

    /**
     * Internationalization
     *
     * @since 1.0.0
     * @return void
     */
    public function load_textdomain() {

        $lang_dir = AUTOMATORWP_DEEPL_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_deepl_languages_directory', $lang_dir );

        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-deepl' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-deepl', $locale );

        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/automatorwp-deepl/' . $mofile;

        if ( file_exists( $mofile_global ) ) {
            load_textdomain( 'automatorwp-deepl', $mofile_global );
        } elseif ( file_exists( $mofile_local ) ) {
            load_textdomain( 'automatorwp-deepl', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-deepl', false, $lang_dir );
        }

    }

}

/**
 * Main function
 *
 * @since 1.0.0
 * @return AutomatorWP_DeepL
 */
function AutomatorWP_DeepL() {
    return AutomatorWP_DeepL::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_DeepL' );
