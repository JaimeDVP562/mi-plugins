<?php
/**
 * Plugin Name:           AutomatorWP - DocsBot AI
 * Plugin URI:            https://automatorwp.com/add-ons/docsbot/
 * Description:           Connect AutomatorWP with DocsBot AI.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-docsbot
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.9
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\DocsBot
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

final class AutomatorWP_DocsBot {

    /**
     * @var         AutomatorWP_DocsBot $instance The one true AutomatorWP_DocsBot
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_DocsBot self::$instance The one true AutomatorWP_DocsBot
     */
    public static function instance() {

        if( ! self::$instance ) {
            self::$instance = new AutomatorWP_DocsBot();
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
        define( 'AUTOMATORWP_DOCSBOT_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_DOCSBOT_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_DOCSBOT_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_DOCSBOT_URL', plugin_dir_url( __FILE__ ) );

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

            // Core includes
            require_once AUTOMATORWP_DOCSBOT_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_DOCSBOT_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_DOCSBOT_DIR . 'includes/rest-api.php';

            // Triggers
            require_once AUTOMATORWP_DOCSBOT_DIR . 'includes/triggers/lead-created.php';
            require_once AUTOMATORWP_DOCSBOT_DIR . 'includes/triggers/conversation-escalated.php';
            require_once AUTOMATORWP_DOCSBOT_DIR . 'includes/triggers/conversation-rated.php';
            require_once AUTOMATORWP_DOCSBOT_DIR . 'includes/triggers/deep-research-done.php';

            // Actions
            require_once AUTOMATORWP_DOCSBOT_DIR . 'includes/actions/ask-bot.php';
            require_once AUTOMATORWP_DOCSBOT_DIR . 'includes/actions/search-bot.php';
            require_once AUTOMATORWP_DOCSBOT_DIR . 'includes/actions/create-source.php';
            require_once AUTOMATORWP_DOCSBOT_DIR . 'includes/actions/delete-source.php';
            require_once AUTOMATORWP_DOCSBOT_DIR . 'includes/actions/capture-lead.php';

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
    public function register_integration() {

        automatorwp_register_integration( 'docsbot', array(
            'label' => 'DocsBot AI',
            'icon'  => AUTOMATORWP_DOCSBOT_URL . 'assets/docsbot.svg',
        ) );

    }

    /**
     * Plugin admin notices.
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'AutomatorWP - DocsBot AI requires %s in order to work. Please install and activate it.', 'automatorwp-docsbot' ),
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

        if( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        return true;

    }

}

/**
 * The main function responsible for returning the one true AutomatorWP_DocsBot instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_DocsBot The one true AutomatorWP_DocsBot
 */
function AutomatorWP_DocsBot() {
    return AutomatorWP_DocsBot::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_DocsBot' );
