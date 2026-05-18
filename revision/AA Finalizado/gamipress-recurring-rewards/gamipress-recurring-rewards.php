<?php
/**
 * Plugin Name:     GamiPress - Recurring Rewards
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-recurring-rewards
 * Description:     Allow users to earn recurring rewards based on achievements and ranks.
 * Version:         1.0.0
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-recurring-rewards
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\Recurring_Rewards
 * @author          GamiPress
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_Recurring_Rewards {

    /**
     * @var         GamiPress_Recurring_Rewards $instance The one true GamiPress_Recurring_Rewards
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_Recurring_Rewards self::$instance The one true GamiPress_Recurring_Rewards
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new GamiPress_Recurring_Rewards();
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
        define( 'GAMIPRESS_RECURRING_REWARDS_VER', '1.0.0' );
        define( 'GAMIPRESS_RECURRING_REWARDS_GAMIPRESS_MIN_VER', '3.0.0' );
        define( 'GAMIPRESS_RECURRING_REWARDS_FILE', __FILE__ );
        define( 'GAMIPRESS_RECURRING_REWARDS_DIR', plugin_dir_path( __FILE__ ) );
        define( 'GAMIPRESS_RECURRING_REWARDS_URL', plugin_dir_url( __FILE__ ) );
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

            require_once GAMIPRESS_RECURRING_REWARDS_DIR . 'includes/admin.php';
            require_once GAMIPRESS_RECURRING_REWARDS_DIR . 'includes/ajax-functions.php';
            require_once GAMIPRESS_RECURRING_REWARDS_DIR . 'includes/custom-tables.php';
            require_once GAMIPRESS_RECURRING_REWARDS_DIR . 'includes/functions.php';
            require_once GAMIPRESS_RECURRING_REWARDS_DIR . 'includes/listeners.php';
            require_once GAMIPRESS_RECURRING_REWARDS_DIR . 'includes/recurring-rewards.php';
            require_once GAMIPRESS_RECURRING_REWARDS_DIR . 'includes/scripts.php';

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

        add_action( 'init', array( $this, 'init' ), 0 );

    }

    /**
     * Boots the plugin after WordPress finishes loading its dependencies.
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {

        if ( ! $this->meets_requirements() ) {
            return;
        }

        $this->load_textdomain();

    }

    /**
     * Determines whether the plugin can run with the current GamiPress setup.
     *
     * @since  1.0.0
     *
     * @return bool True when GamiPress is available and meets the minimum version.
     */
    public function meets_requirements() {

        if ( ! class_exists( 'GamiPress' ) ) {
            return false;
        }

        if ( defined( 'GAMIPRESS_VER' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_RECURRING_REWARDS_GAMIPRESS_MIN_VER, '<' ) ) {
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

        $lang_dir = GAMIPRESS_RECURRING_REWARDS_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_recurring_rewards_languages_directory', $lang_dir );

        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-recurring-rewards' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-recurring-rewards', $locale );

        $mofile_local = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/gamipress-recurring-rewards/' . $mofile;

        if ( file_exists( $mofile_global ) ) {
            load_textdomain( 'gamipress-recurring-rewards', $mofile_global );
        } elseif ( file_exists( $mofile_local ) ) {
            load_textdomain( 'gamipress-recurring-rewards', $mofile_local );
        } else {
            load_plugin_textdomain( 'gamipress-recurring-rewards', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true GamiPress_Recurring_Rewards instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php $gamipress_recurring_rewards = gamipress_recurring_rewards(); ?>
 *
 * @since 1.0.0
 * @return GamiPress_Recurring_Rewards The one true GamiPress_Recurring_Rewards instance.
 */
function gamipress_recurring_rewards() {
    return GamiPress_Recurring_Rewards::instance();
}

/**
 * Deactivation hook callback.
 *
 * @since 1.0.0
 */
function gamipress_recurring_rewards_deactivate() {

    wp_clear_scheduled_hook( 'gamipress_recurring_rewards_cron_every_minute' );

}

register_deactivation_hook( __FILE__, 'gamipress_recurring_rewards_deactivate' );

add_action( 'plugins_loaded', 'gamipress_recurring_rewards', 11 );
