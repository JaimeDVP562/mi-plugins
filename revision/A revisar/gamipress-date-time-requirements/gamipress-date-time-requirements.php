<?php
/**
 * Plugin Name:     GamiPress - Date & Time Limited Requirements
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-date-time-requirements
 * Description:     Add date and time limits to GamiPress requirements.
 * Version:         1.0.0
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-date-time-requirements
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\Date_Time_Requirements
 * @author          GamiPress
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_Date_Time_Requirements {

    /**
     * @var         GamiPress_Date_Time_Requirements $instance The one true GamiPress_Date_Time_Requirements
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_Date_Time_Requirements self::$instance The one true GamiPress_Date_Time_Requirements
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new GamiPress_Date_Time_Requirements();
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

        define( 'GAMIPRESS_DATE_TIME_REQUIREMENTS_VER', '1.0.0' );
        define( 'GAMIPRESS_DATE_TIME_REQUIREMENTS_GAMIPRESS_MIN_VER', '3.0.0' );
        define( 'GAMIPRESS_DATE_TIME_REQUIREMENTS_FILE', __FILE__ );
        define( 'GAMIPRESS_DATE_TIME_REQUIREMENTS_DIR', plugin_dir_path( __FILE__ ) );
        define( 'GAMIPRESS_DATE_TIME_REQUIREMENTS_URL', plugin_dir_url( __FILE__ ) );

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

            require_once GAMIPRESS_DATE_TIME_REQUIREMENTS_DIR . 'includes/functions.php';
            require_once GAMIPRESS_DATE_TIME_REQUIREMENTS_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_DATE_TIME_REQUIREMENTS_DIR . 'includes/requirements.php';

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

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

    }

    /**
     * Plugin admin notices
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'GAMIPRESS_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'GamiPress - Date & Time Limited Requirements requires %s (%s or higher) in order to work. Please install and activate it.', 'gamipress-date-time-requirements' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_DATE_TIME_REQUIREMENTS_GAMIPRESS_MIN_VER
                    ); ?>
                </p>
            </div>

            <?php define( 'GAMIPRESS_ADMIN_NOTICES', true ); ?>

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

        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_DATE_TIME_REQUIREMENTS_GAMIPRESS_MIN_VER, '>=' ) ) {
            return true;
        }

        return false;

    }

    /**
     * Internationalization
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain() {

        $lang_dir = GAMIPRESS_DATE_TIME_REQUIREMENTS_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_date_time_requirements_languages_directory', $lang_dir );

        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-date-time-requirements' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-date-time-requirements', $locale );

        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/gamipress-date-time-requirements/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            load_textdomain( 'gamipress-date-time-requirements', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            load_textdomain( 'gamipress-date-time-requirements', $mofile_local );
        } else {
            load_plugin_textdomain( 'gamipress-date-time-requirements', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true GamiPress_Date_Time_Requirements instance
 *
 * @since       1.0.0
 * @return      \GamiPress_Date_Time_Requirements The one true GamiPress_Date_Time_Requirements
 */
function GamiPress_Date_Time_Requirements() {
    return GamiPress_Date_Time_Requirements::instance();
}
add_action( 'plugins_loaded', 'GamiPress_Date_Time_Requirements' );