<?php
/**
 * Plugin Name:           AutomatorWP - SG Optimizer
 * Plugin URI:            https://automatorwp.com/add-ons/sg-optimizer/
 * Description:           Connect AutomatorWP with SG Optimizer.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-sg-optimizer
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\SGOptimizer
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_SG_Optimizer {

    /**
     * @var         AutomatorWP_SG_Optimizer $instance The one true AutomatorWP_SG_Optimizer
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_SG_Optimizer self::$instance The one true AutomatorWP_SG_Optimizer
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new AutomatorWP_SG_Optimizer();
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

        define( 'AUTOMATORWP_SG_OPTIMIZER_VER', '1.0.0' );
        define( 'AUTOMATORWP_SG_OPTIMIZER_FILE', __FILE__ );
        define( 'AUTOMATORWP_SG_OPTIMIZER_DIR', plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_SG_OPTIMIZER_URL', plugin_dir_url( __FILE__ ) );

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

            require_once AUTOMATORWP_SG_OPTIMIZER_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_SG_OPTIMIZER_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_SG_OPTIMIZER_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_SG_OPTIMIZER_DIR . 'includes/scripts.php';
            require_once AUTOMATORWP_SG_OPTIMIZER_DIR . 'includes/tags.php';

            // SG Optimizer Actions
            require_once AUTOMATORWP_SG_OPTIMIZER_DIR . 'includes/actions/purge-all-cache.php';
            require_once AUTOMATORWP_SG_OPTIMIZER_DIR . 'includes/actions/purge-url-cache.php';

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

        automatorwp_register_integration( 'sg_optimizer', array(
            'label' => 'SG Optimizer',
            'icon'  => AUTOMATORWP_SG_OPTIMIZER_URL . 'assets/img/sg-optimizer.svg',
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
                        __( 'AutomatorWP - SG Optimizer requires %s and %s in order to work. Please install and activate them.', 'automatorwp-sg-optimizer' ),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>',
                        '<a href="https://wordpress.org/plugins/sg-cachepress/" target="_blank">SG Optimizer</a>'
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

        if ( ! class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
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

        $lang_dir = AUTOMATORWP_SG_OPTIMIZER_DIR . '/languages/';
        $lang_dir = apply_filters( 'automatorwp_sg_optimizer_languages_directory', $lang_dir );

        $locale = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-sg-optimizer' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'automatorwp-sg-optimizer', $locale );

        $mofile_local  = $lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/automatorwp-sg-optimizer/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            load_textdomain( 'automatorwp-sg-optimizer', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            load_textdomain( 'automatorwp-sg-optimizer', $mofile_local );
        } else {
            load_plugin_textdomain( 'automatorwp-sg-optimizer', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_SG_Optimizer instance
 *
 * @since       1.0.0
 * @return      \AutomatorWP_SG_Optimizer The one true AutomatorWP_SG_Optimizer
 */
function AutomatorWP_SG_Optimizer() {
    return AutomatorWP_SG_Optimizer::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_SG_Optimizer' );
