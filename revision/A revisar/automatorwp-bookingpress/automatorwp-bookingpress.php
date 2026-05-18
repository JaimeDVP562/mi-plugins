<?php
/**
 * Plugin Name:           AutomatorWP - BookingPress
 * Plugin URI:            https://automatorwp.com/add-ons/bookingpress/
 * Description:           Connect AutomatorWP with BookingPress.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-bookingpress
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\BookingPress
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_BookingPress {

    /**
     * @var         AutomatorWP_BookingPress $instance The one true AutomatorWP_BookingPress
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_BookingPress self::$instance The one true AutomatorWP_BookingPress
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_BookingPress();
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

        define( 'AUTOMATORWP_BOOKINGPRESS_VER', '1.0.0' );
        define( 'AUTOMATORWP_BOOKINGPRESS_FILE', __FILE__ );
        define( 'AUTOMATORWP_BOOKINGPRESS_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_BOOKINGPRESS_URL', plugin_dir_url( __FILE__ ) );

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

            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/scripts.php';
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/tags.php';
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/admin.php';

            // Triggers
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/triggers/trigger-new-appointment.php';
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/triggers/trigger-appointment-approved.php';
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/triggers/trigger-appointment-pending.php';
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/triggers/trigger-appointment-canceled.php';
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/triggers/trigger-appointment-rejected.php';


            //Actions
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/actions/action-cancel-appointment.php';
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/actions/action-approve-appointment.php';
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/actions/action-reject-appointment.php';
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/actions/action-pending-appointment.php';
            require_once AUTOMATORWP_BOOKINGPRESS_DIR . 'includes/actions/action-create-customer.php';

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
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

    }

    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration() {

        automatorwp_register_integration( 'bookingpress', array(
            'label' => 'BookingPress',
            'icon'  => AUTOMATORWP_BOOKINGPRESS_URL . 'assets/bookingpress.png',
        ) );

    }

    /**
     * Plugin admin notices
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - BookingPress requires %s and %s in order to work. Please install and activate them.', 'automatorwp-bookingpress' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://www.bookingpressplugin.com/" target="_blank">BookingPress</a>'
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

        if ( ! class_exists( 'BookingPress' ) ) {
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

        $lang_dir = AUTOMATORWP_BOOKINGPRESS_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_bookingpress_languages_directory', $lang_dir );

        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-bookingpress' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-bookingpress', $locale );

        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/automatorwp-bookingpress/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            load_textdomain( 'automatorwp-bookingpress', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            load_textdomain( 'automatorwp-bookingpress', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-bookingpress', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_BookingPress instance
 *
 * @since       1.0.0
 * @return      \AutomatorWP_BookingPress The one true AutomatorWP_BookingPress
 */
function AutomatorWP_BookingPress() {
    return AutomatorWP_BookingPress::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_BookingPress' );
