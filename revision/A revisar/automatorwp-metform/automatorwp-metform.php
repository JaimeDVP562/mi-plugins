<?php
/**
 * Plugin Name:           AutomatorWP - MetForm
 * Plugin URI:            https://automatorwp.com/add-ons/metform/
 * Description:           Connect AutomatorWP with MetForm.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-metform
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\MetForm
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_MetForm {

    /**
     * @var         AutomatorWP_MetForm $instance The one true AutomatorWP_MetForm
	 * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_MetForm self::$instance The one true AutomatorWP_MetForm
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_MetForm();
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
        define( 'AUTOMATORWP_METFORM_VER', '1.1.0' );

        // Plugin file
        define( 'AUTOMATORWP_METFORM_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_METFORM_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_METFORM_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_METFORM_DIR . 'includes/functions.php';

            // Triggers
            require_once AUTOMATORWP_METFORM_DIR . 'includes/triggers/submit-form.php';
            require_once AUTOMATORWP_METFORM_DIR . 'includes/triggers/submit-field-value.php';
            // Anonymous Triggers
            require_once AUTOMATORWP_METFORM_DIR . 'includes/triggers/anonymous-submit-form.php';
            require_once AUTOMATORWP_METFORM_DIR . 'includes/triggers/anonymous-submit-field-value.php';

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

        automatorwp_register_integration( 'metform', array(
            'label' => 'MetForm',
            'icon'  => AUTOMATORWP_METFORM_URL . 'assets/metform.svg',
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

        $meta_boxes['automatorwp-metform-license'] = array(
            'title' => 'MetForm',
            'fields' => array(
                'automatorwp_metform_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_METFORM_FILE,
                    'item_name' => 'Metform',
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
                        __( 'AutomatorWP - Metform requires %s and %s in order to work. Please install and activate them.', 'automatorwp-metform' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/metform/" target="_blank">MetForm</a>'
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

        if ( ! class_exists( 'MetForm\Plugin' ) ) {
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
        $lang_dir = AUTOMATORWP_METFORM_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_metform_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-metform' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-metform', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-metform/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-metform/ folder
            load_textdomain( 'automatorwp-metform', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-metform/languages/ folder
            load_textdomain( 'automatorwp-metform', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-metform', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_MetForm instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_MetForm The one true AutomatorWP_MetForm
 */
function AutomatorWP_MetForm() {
    return AutomatorWP_MetForm::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_MetForm' );
