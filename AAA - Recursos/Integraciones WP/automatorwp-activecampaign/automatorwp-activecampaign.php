<?php
/**
 * Plugin Name:           AutomatorWP - ActiveCampaign
 * Plugin URI:            https://automatorwp.com/add-ons/activecampaign/
 * Description:           Connect AutomatorWP with ActiveCampaign.
 * Version:               1.1.3
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-activecampaign
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.9
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\ActiveCampaign
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_ActiveCampaign {

    /**
     * @var         AutomatorWP_ActiveCampaign $instance The one true AutomatorWP_ActiveCampaign
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_ActiveCampaign self::$instance The one true AutomatorWP_ActiveCampaign
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_ActiveCampaign();
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
        define( 'AUTOMATORWP_ACTIVECAMPAIGN_VER', '1.1.3' );

        // Plugin file
        define( 'AUTOMATORWP_ACTIVECAMPAIGN_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_ACTIVECAMPAIGN_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_ACTIVECAMPAIGN_URL', plugin_dir_url( __FILE__ ) );
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
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/scripts.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/rest-api.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/tags.php';

            // Triggers
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/triggers/user-added.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/triggers/user-tag-added.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/triggers/user-tag-removed.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/triggers/user-unsubscribed.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/triggers/user-list-added.php';

            // Anonymous Triggers
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/triggers/anonymous-contact-added.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/triggers/anonymous-contact-tag-added.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/triggers/anonymous-contact-tag-removed.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/triggers/anonymous-contact-unsubscribed.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/triggers/anonymous-contact-list-added.php';

            // Actions
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/add-contact.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/add-contact-list.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/add-contact-tag.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/create-contact-tag.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/add-user.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/add-user-list.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/add-user-tag.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/create-user-tag.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/remove-user-list.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/remove-user-tag.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/remove-contact-list.php';       
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/remove-contact-tag.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/update-contact.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/update-user-fields.php';
            require_once AUTOMATORWP_ACTIVECAMPAIGN_DIR . 'includes/actions/update-contact-fields.php';

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

        automatorwp_register_integration( 'activecampaign', array(
            'label' => 'ActiveCampaign',
            'icon'  => AUTOMATORWP_ACTIVECAMPAIGN_URL . 'assets/activecampaign.svg',
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

        $meta_boxes['automatorwp-activecampaign-license'] = array(
            'title' => 'ActiveCampaign',
            'fields' => array(
                'automatorwp_activecampaign_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_ACTIVECAMPAIGN_FILE,
                    'item_name' => 'ActiveCampaign',
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
                        __( 'AutomatorWP - ActiveCampaign requires %s in order to work. Please install and activate it.', 'automatorwp-activecampaign' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>'
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
        $lang_dir = AUTOMATORWP_ACTIVECAMPAIGN_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_activecampaign_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-activecampaign' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-activecampaign', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-activecampaign/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-activecampaign/ folder
            load_textdomain( 'automatorwp-activecampaign', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-activecampaign/languages/ folder
            load_textdomain( 'automatorwp-activecampaign', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-activecampaign', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_ActiveCampaign instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_ActiveCampaign The one true AutomatorWP_ActiveCampaign
 */
function AutomatorWP_ActiveCampaign() {
    return AutomatorWP_ActiveCampaign::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_ActiveCampaign' );
