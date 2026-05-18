<?php
/**
 * Plugin Name:           AutomatorWP - Appointment Hour Booking integration
 * Plugin URI:            https://automatorwp.com/add-ons/appointment-hour-booking/
 * Description:           Connect AutomatorWP with Appointment Hour Booking.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-appointment-hour-booking-integration
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          5.7
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Appointment_Hour_Booking
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Appointment_Hour_Booking {

    /**
     * @var         AutomatorWP_Appointment_Hour_Booking $instance The one true AutomatorWP_Appointment_Hour_Booking
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Appointment_Hour_Booking self::$instance The one true AutomatorWP_Appointment_Hour_Booking
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_Appointment_Hour_Booking();
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
        define( 'AUTOMATORWP_APPOINTMENT_HOUR_BOOKING_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_APPOINTMENT_HOUR_BOOKING_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_APPOINTMENT_HOUR_BOOKING_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_APPOINTMENT_HOUR_BOOKING_URL', plugin_dir_url( __FILE__ ) );
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

            // Tags
            require_once AUTOMATORWP_APPOINTMENT_HOUR_BOOKING_DIR . 'includes/tags.php';

            // Triggers
            require_once AUTOMATORWP_APPOINTMENT_HOUR_BOOKING_DIR . 'includes/triggers/user-booking-made.php';
            require_once AUTOMATORWP_APPOINTMENT_HOUR_BOOKING_DIR . 'includes/triggers/guest-booking-made.php';
            require_once AUTOMATORWP_APPOINTMENT_HOUR_BOOKING_DIR . 'includes/triggers/user-cancelled-booking.php';
            

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

        automatorwp_register_integration( 'appointment_hour_booking', array(
            'label' => 'Appointment Hour Booking',
            'icon'  => plugin_dir_url( __FILE__ ) . 'assets/appointment-hour-booking.svg',
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

        $meta_boxes['automatorwp-appointment_hour_booking-license'] = array(
            'title' => 'Appointment Hour Booking',
            'fields' => array(
                'automatorwp_appointment_hour_booking_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_APPOINTMENT_HOUR_BOOKING_FILE,
                    'item_name' => 'Appointment Hour Booking',
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
                        __( 'AutomatorWP - Appointment Hour Booking requires %s and %s in order to work. Please install and activate them.', 'automatorwp-appointment-hour-booking' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/appointment-hour-booking/" target="_blank">Appointment Hour Booking</a>'
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

        if ( ! class_exists( 'CP_AppBookingPlugin' ) ) {
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
        $lang_dir = AUTOMATORWP_APPOINTMENT_HOUR_BOOKING_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_appointment-hour-booking_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-appointment-hour-booking' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-appointment-hour-booking', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-appointment-hour-booking/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/automatorwp-appointment-hour-booking/ folder
            load_textdomain( 'automatorwp-appointment-hour-booking', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/automatorwp-appointment-hour-booking/languages/ folder
            load_textdomain( 'automatorwp-appointment-hour-booking', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'automatorwp-appointment-hour-booking', false, $lang_dir );
        }

    }
}

/**
 * The main function responsible for returning the one true AutomatorWP_Appointment_Hour_Booking instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Appointment_Hour_Booking The one true AutomatorWP_Appointment_Hour_Booking
 */
function AutomatorWP_Appointment_Hour_Booking() {
    return AutomatorWP_Appointment_Hour_Booking::instance();
}
add_action( 'automatorwp_pre_init', 'AutomatorWP_Appointment_Hour_Booking' );
