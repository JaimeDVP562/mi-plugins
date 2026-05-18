<?php
/**
 * Plugin Name:     GamiPress - Monster Taming Game
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-monster-taming-game
 * Description:     Add a Monster Taming game to boost your gamification strategy.
 * Version:         1.0.0
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-monster-taming-game
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\Monster_Taming_Game
 * @author          GamiPress
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_Monster_Taming_Game {

    /**
     * @var         GamiPress_Monster_Taming_Game $instance The one true GamiPress_Monster_Taming_Game
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_Monster_Taming_Game self::$instance The one true GamiPress_Monster_Taming_Game
     */
    public static function instance() {

        if( ! self::$instance ) {

            self::$instance = new GamiPress_Monster_Taming_Game();
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
        define( 'GAMIPRESS_MONSTER_TAMING_GAME_VER', '1.0.0' );

        // GamiPress minimum required version
        define( 'GAMIPRESS_MONSTER_TAMING_GAME_GAMIPRESS_MIN_VER', '7.0.0' );

        // Plugin file
        define( 'GAMIPRESS_MONSTER_TAMING_GAME_FILE', __FILE__ );

        // Plugin path
        define( 'GAMIPRESS_MONSTER_TAMING_GAME_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_MONSTER_TAMING_GAME_URL', plugin_dir_url( __FILE__ ) );

        // Resources constants
        define( 'GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_VER', '1.0.0' );

        $upload_dir = wp_upload_dir();
        $plugin_dir = apply_filters( 'gamipress_monster_taming_game_upload_dir', 'gamipress-monster-taming-game' );

        define( 'GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR', $upload_dir['basedir'] . '/' . $plugin_dir . '/' );
        define( 'GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_URL', $upload_dir['baseurl'] . '/' . $plugin_dir . '/' );

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

            require_once GAMIPRESS_MONSTER_TAMING_GAME_DIR . 'includes/admin.php';
            require_once GAMIPRESS_MONSTER_TAMING_GAME_DIR . 'includes/ajax-functions.php';
            require_once GAMIPRESS_MONSTER_TAMING_GAME_DIR . 'includes/attachments.php';
            require_once GAMIPRESS_MONSTER_TAMING_GAME_DIR . 'includes/csv.php';
            require_once GAMIPRESS_MONSTER_TAMING_GAME_DIR . 'includes/functions.php';
            require_once GAMIPRESS_MONSTER_TAMING_GAME_DIR . 'includes/resources.php';
            require_once GAMIPRESS_MONSTER_TAMING_GAME_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_MONSTER_TAMING_GAME_DIR . 'includes/tools.php';


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

        add_action( 'init', array( $this, 'load_textdomain' ) );

    }

    /**
     * Activation hook for the plugin.
     *
     * @since  1.0.0
     */
    public static function activate() {

    }

    /**
     * Deactivation hook for the plugin.
     *
     * @since  1.0.0
     */
    public static function deactivate() {

    }

    /**
     * Plugin admin notices.
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'GAMIPRESS_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'GamiPress - Monster Taming Game requires %s (%s or higher) in order to work. Please install and activate it.', 'gamipress-monster-taming-game' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_MONSTER_TAMING_GAME_GAMIPRESS_MIN_VER
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

        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_MONSTER_TAMING_GAME_GAMIPRESS_MIN_VER, '>=' ) ) {
            return true;
        } else {
            return false;
        }

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
        $lang_dir = GAMIPRESS_MONSTER_TAMING_GAME_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_monster_taming_game_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-monster-taming-game' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-monster-taming-game', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/gamipress-monster-taming-game/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/gamipress/ folder
            load_textdomain( 'gamipress-monster-taming-game', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/gamipress/languages/ folder
            load_textdomain( 'gamipress-monster-taming-game', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'gamipress-monster-taming-game', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true GamiPress_Monster_Taming_Game instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \GamiPress_Monster_Taming_Game The one true GamiPress_Monster_Taming_Game
 */
function gamipress_monster_taming_game() {
    return GamiPress_Monster_Taming_Game::instance();
}
add_action( 'plugins_loaded', 'gamipress_monster_taming_game' );

// Setup our activation and deactivation hooks
register_activation_hook( __FILE__, array( 'GamiPress_Monster_Taming_Game', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'GamiPress_Monster_Taming_Game', 'deactivate' ) );