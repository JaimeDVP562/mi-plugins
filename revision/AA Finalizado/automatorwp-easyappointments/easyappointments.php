<?php
/**
 * Plugin Name:           AutomatorWP - Easy Appointments
 * Plugin URI:            https://wordpress.org/plugins/automatorwp-easyappointments/
 * Description:           Connect AutomatorWP with Easy Appointments
 * Version:               1.1.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-easyappointments
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.5
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\EasyAppointments
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_EasyAppointments {

    /**
     * @var         AutomatorWP_EasyAppointments $instance The one true AutomatorWP_EasyAppointments
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_EasyAppointments self::$instance The one true AutomatorWP_EasyAppointments
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_EasyAppointments();
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
        define( 'AUTOMATORWP_EASYAPPOINTMENTS_VER', '1.1.0' );

        // Plugin file
        define( 'AUTOMATORWP_EASYAPPOINTMENTS_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_EASYAPPOINTMENTS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_EASYAPPOINTMENTS_URL', plugin_dir_url( __FILE__ ) );

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

            // Triggers
            require_once AUTOMATORWP_EASYAPPOINTMENTS_DIR . 'includes/triggers/appointment-created.php';
            require_once AUTOMATORWP_EASYAPPOINTMENTS_DIR . 'includes/triggers/appointment-confirmed.php';
            require_once AUTOMATORWP_EASYAPPOINTMENTS_DIR . 'includes/triggers/appointment-canceled.php';
            require_once AUTOMATORWP_EASYAPPOINTMENTS_DIR . 'includes/triggers/appointment-updated.php';

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

        automatorwp_register_integration( 'easyappointments', array(
            'label' => 'Easy Appointments',
            'icon'  => AUTOMATORWP_EASYAPPOINTMENTS_URL . 'assets/easyappointments.svg',
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

        $meta_boxes['automatorwp-easyappointments-license'] = array(
            'title' => 'Easy Appointments',
            'fields' => array(
                'automatorwp_easyappointments_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_EASYAPPOINTMENTS_FILE,
                    'item_name' => 'Easy Appointments',
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
                        __( 'AutomatorWP - Easy Appointments requires %s and %s in order to work. Please install and activate them.', 'automatorwp-easyappointments' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/easy-appointments/" target="_blank">Easy Appointments</a>'
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
        $lang_dir = AUTOMATORWP_EASYAPPOINTMENTS_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_easyappointments_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-easyappointments' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-easyappointments', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-easyappointments/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            load_textdomain( 'automatorwp-easyappointments', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            load_textdomain( 'automatorwp-easyappointments', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-easyappointments', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_EasyAppointments instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_EasyAppointments The one true AutomatorWP_EasyAppointments
 */
function AutomatorWP_EasyAppointments() {
    return AutomatorWP_EasyAppointments::instance();
}

add_action( 'plugins_loaded', 'AutomatorWP_EasyAppointments' );

add_action('wp_ajax_ea_appointment', function() {
    $method = isset( $_GET['_method'] ) ? strtoupper( $_GET['_method'] ) : 'POST';
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if ( ! is_array($data) ) return;

    $user_id = isset($data['user']) ? intval($data['user']) : 0;
    $appointment_id = isset($data['id']) ? intval($data['id']) : 0;

    if ( $method === 'POST' ) {
        do_action( 'ea_appointment_created', $appointment_id, $data, $user_id );
    } elseif ( $method === 'PUT' ) {
        $status = $data['status'] ?? '';
        do_action( 'ea_appointment_updated', $appointment_id, $data, $user_id );
        if ( $status === 'confirmed' ) {
            do_action( 'ea_appointment_confirmed', $appointment_id, $data, $user_id );
        } elseif ( $status === 'canceled' ) {
            do_action( 'ea_appointment_canceled', $appointment_id, $data, $user_id );
        }
    }
}, 5 );

