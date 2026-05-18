<?php
/**
 * Plugin Name:           AutomatorWP Gutena Forms
 * Plugin URI:            https://automatorwp.com/add-ons/gutena-forms/
 * Description:           Connect AutomatorWP with Gutena Forms.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-gutenaforms
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\GutenaForms
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_GutenaForms {

    /**
     * @var         AutomatorWP_GutenaForms $instance The one true AutomatorWP_GutenaForms
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_GutenaForms self::$instance The one true AutomatorWP_GutenaForms
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_GutenaForms();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();
        }

        return self::$instance;
    }

/*
    public static function instance() {
        if( ! self::$instance ) {

            self::$instance = new AutomatorWP_GutenaForms();

            if( ! self::$instance->pro_installed() ) {

                self::$instance->constants();
                self::$instance->includes();
                
            }

            self::$instance->hooks();
        }

        return self::$instance;
    }
*/

    /**
     * Setup plugin constants
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function constants() {
        // Plugin version
        define( 'AUTOMATORWP_GUTENA_FORMS_VERSION', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_GUTENA_FORMS_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_GUTENA_FORMS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_GUTENA_FORMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_GUTENA_FORMS_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_GUTENA_FORMS_DIR . 'includes/functions.php';

            // Triggers
            require_once AUTOMATORWP_GUTENA_FORMS_DIR . 'includes/triggers/submit-form.php';
            require_once AUTOMATORWP_GUTENA_FORMS_DIR . 'includes/triggers/submit-field-value.php';
            // Anonymous Triggers
            require_once AUTOMATORWP_GUTENA_FORMS_DIR . 'includes/triggers/anonymous-submit-form.php';
            require_once AUTOMATORWP_GUTENA_FORMS_DIR . 'includes/triggers/anonymous-submit-field-value.php';

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

        automatorwp_register_integration( 'gutenaforms', array(
            'label' => 'WP Gutena Forms',
            'icon'  => AUTOMATORWP_GUTENA_FORMS_PLUGIN_URL . 'assets/gutenaforms.svg',
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

        $meta_boxes['automatorwp-gutenaforms-license'] = array(
            'title' => 'WP Gutena Forms',
            'fields' => array(
                'automatorwp_gutenaforms_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_GUTENA_FORMS_FILE,
                    'item_name' => 'WP Gutena Forms',
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
                        __( 'AutomatorWP - WP Gutena Forms requires %s and %s in order to work. Please install and activate them.', 'automatorwp-gutenaforms' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/gutena-forms/" target="_blank">WP Gutena Forms</a>'
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

        if ( ! class_exists( 'Gutena_Forms' ) ) {
            return false;
        }

        return true;

    }

    /**
     * Check if the pro version of this integration is installed
     *
     * @since  1.0.0
     *
     * @return bool True if pro version installed
     */

    /**
     * Internationalization
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain() {

        // Set filter for language directory
        $lang_dir = AUTOMATORWP_GUTENA_FORMS_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_gutenaforms_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-gutenaforms' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-gutenaforms', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-gutenaforms/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-gutenaforms/ folder
            load_textdomain( 'automatorwp-gutenaforms', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-gutenaforms/languages/ folder
            load_textdomain( 'automatorwp-gutenaforms', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-gutenaforms', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_GutenaForms instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_GutenaForms The one true AutomatorWP_GutenaForms
 */
function AutomatorWP_GutenaForms() {
    return AutomatorWP_GutenaForms::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_GutenaForms' );
