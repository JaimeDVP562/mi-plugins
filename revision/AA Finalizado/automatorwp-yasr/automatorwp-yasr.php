<?php
/**
 * Plugin Name:           AutomatorWP - Yet Another Stars Rating
 * Plugin URI:            https://automatorwp.com/add-ons/yasr/
 * Description:           Connect AutomatorWP with Yet Another Stars Rating.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-yasr
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\YASR
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_YASR {

    /**
     * @var         AutomatorWP_YASR $instance The one true AutomatorWP_YASR
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_YASR self::$instance The one true AutomatorWP_YASR
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_YASR();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            // self::$instance->load_textdomain();
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
        define( 'AUTOMATORWP_YASR_VER', '1.1.0' );

        // Plugin file
        define( 'AUTOMATORWP_YASR_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_YASR_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_YASR_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if( $this->meets_requirements() ) {

            // Includes
            require_once AUTOMATORWP_YASR_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_YASR_DIR . 'includes/functions.php';

            // Triggers
            require_once AUTOMATORWP_YASR_DIR . 'includes/triggers/new-review.php';

            // Anonymous Triggers
            require_once AUTOMATORWP_YASR_DIR . 'includes/triggers/anonymous-new-review.php';

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
    function register_integration() {

        automatorwp_register_integration( 'yasr', array(
            'label' => 'WP Yet Another Stars Rating',
            'icon'  => AUTOMATORWP_YASR_URL . 'assets/yasr.svg',
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
    function license( $meta_boxes ) {

        $meta_boxes['automatorwp-yasr-license'] = array(
            'title' => 'WP Yet Another Stars Rating',
            'fields' => array(
                'automatorwp_yasr_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_YASR_FILE,
                    'item_name' => 'WP Yet Another Stars Rating',
                ),
            )
        );

        return $meta_boxes;

    }

    /**
     * Plugin admin notices.
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - WP Yet Another Stars Rating requires %s and %s in order to work. Please install and activate them.', 'automatorwp-yasr' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/yet-another-stars-rating/" target="_blank">WP Yet Another Stars Rating</a>'
                    ); ?>
                </p>
            </div>

            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>

        <?php endif;

    }

    /**
     * Check if there are all plugin requirements
     *
     * @since  1.0.0
     *
     * @return bool True if installation meets all requirements
     */
    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        if ( ! defined( 'YASR_VERSION_NUM' ) ) {
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

        // Set filter for language directory
        $lang_dir = AUTOMATORWP_YASR_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_yasr_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-yasr' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-yasr', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-yasr/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-yasr/ folder
            load_textdomain( 'automatorwp-yasr', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-yasr/languages/ folder
            load_textdomain( 'automatorwp-yasr', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-yasr', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_YASR instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_YASR The one true AutomatorWP_YASR
 */
function AutomatorWP_YASR() {
    return AutomatorWP_YASR::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_YASR' );
