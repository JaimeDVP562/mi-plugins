<?php
/**
 * Plugin Name:           AutomatorWP - WP Tripetto
 * Plugin URI:            https://automatorwp.com/add-ons/tripetto/
 * Description:           Connect AutomatorWP with WP Tripetto.
 * Version:               1.1.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-tripetto
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Tripetto
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Tripetto {

    /**
     * @var         AutomatorWP_Tripetto $instance The one true AutomatorWP_Tripetto
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Tripetto self::$instance The one true AutomatorWP_Tripetto
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Tripetto();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();
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
        define( 'AUTOMATORWP_TRIPETTO_VER', '1.1.0' );

        // Plugin file
        define( 'AUTOMATORWP_TRIPETTO_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_TRIPETTO_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_TRIPETTO_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_TRIPETTO_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_TRIPETTO_DIR . 'includes/functions.php';

            // Triggers
            require_once AUTOMATORWP_TRIPETTO_DIR . 'includes/triggers/submit-form.php';
            require_once AUTOMATORWP_TRIPETTO_DIR . 'includes/triggers/submit-field-value.php';
            // Anonymous Triggers
            require_once AUTOMATORWP_TRIPETTO_DIR . 'includes/triggers/anonymous-submit-form.php';
            require_once AUTOMATORWP_TRIPETTO_DIR . 'includes/triggers/anonymous-submit-field-value.php';

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
    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'tripetto', array(
            'label' => 'WP Tripetto',
            'icon'  => AUTOMATORWP_TRIPETTO_URL . 'assets/tripetto.svg',
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

        $meta_boxes['automatorwp-tripetto-license'] = array(
            'title' => 'WP Tripetto',
            'fields' => array(
                'automatorwp_tripetto_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_TRIPETTO_FILE,
                    'item_name' => 'WP Tripetto',
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
                        __( 'AutomatorWP - WP Tripetto requires %s and %s in order to work. Please install and activate them.', 'automatorwp-tripetto' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/tripetto/" target="_blank">WP Tripetto</a>'
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

        if ( ! class_exists( 'Freemius' ) ) {
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
        $lang_dir = AUTOMATORWP_TRIPETTO_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_tripetto_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-tripetto' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-tripetto', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-tripetto/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-tripetto/ folder
            load_textdomain( 'automatorwp-tripetto', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-tripetto/languages/ folder
            load_textdomain( 'automatorwp-tripetto', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-tripetto', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_Tripetto instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Tripetto The one true AutomatorWP_Tripetto
 */
function AutomatorWP_Tripetto() {
    return AutomatorWP_Tripetto::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Tripetto' );
