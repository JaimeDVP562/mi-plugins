<?php
/**
 * Plugin Name:           AutomatorWP - Cohere
 * Plugin URI:            https://automatorwp.com/add-ons/cohere/
 * Description:           Connect AutomatorWP with Cohere AI to send prompts, hold multi-turn conversations, summarize text, classify content, rerank documents and generate embeddings.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-cohere
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.9
 * Requires PHP:          7.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Cohere
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AutomatorWP_Cohere' ) ) {

    final class AutomatorWP_Cohere 
    {
        /**
         * @var         AutomatorWP_Cohere $instance The one true AutomatorWP_Cohere
         * @since       1.0.0
         */
        private static $instance;

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      AutomatorWP_Cohere self::$instance The one true AutomatorWP_Cohere
         */
        public static function instance()
        {
            if ( ! isset( self::$instance ) ) {
                self::$instance = new AutomatorWP_Cohere();
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
        private function constants()
        {
            if ( ! defined( 'AUTOMATORWP_COHERE_VER' ) )  define( 'AUTOMATORWP_COHERE_VER',  '1.0.0' );
            if ( ! defined( 'AUTOMATORWP_COHERE_FILE' ) ) define( 'AUTOMATORWP_COHERE_FILE', __FILE__ );
            if ( ! defined( 'AUTOMATORWP_COHERE_DIR' ) )  define( 'AUTOMATORWP_COHERE_DIR',  plugin_dir_path( __FILE__ ) );
            if ( ! defined( 'AUTOMATORWP_COHERE_URL' ) )  define( 'AUTOMATORWP_COHERE_URL',  plugin_dir_url( __FILE__ ) );
        }

        /**
         * Include plugin files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes()
        {
            require_once AUTOMATORWP_COHERE_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_COHERE_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_COHERE_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_COHERE_DIR . 'includes/scripts.php';
            // Actions
            require_once AUTOMATORWP_COHERE_DIR . 'includes/actions/send-prompt.php';
            require_once AUTOMATORWP_COHERE_DIR . 'includes/actions/conversation.php';
            require_once AUTOMATORWP_COHERE_DIR . 'includes/actions/clear-conversation.php';
            require_once AUTOMATORWP_COHERE_DIR . 'includes/actions/summarize.php';
            require_once AUTOMATORWP_COHERE_DIR . 'includes/actions/classify.php';
            require_once AUTOMATORWP_COHERE_DIR . 'includes/actions/rerank.php';
            require_once AUTOMATORWP_COHERE_DIR . 'includes/actions/embed.php';
            require_once AUTOMATORWP_COHERE_DIR . 'includes/actions/translate.php';
        }

        /**
         * Setup plugin hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks()
        {
            add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
            add_action( 'admin_notices',    array( $this, 'admin_notices' ) );
        }

        /**
         * Registers this integration
         *
         * @since 1.0.0
         */
        function register_integration()
        {
            automatorwp_register_integration( 'cohere', array(
                'label' => 'Cohere',
                'icon'  => AUTOMATORWP_COHERE_URL . 'assets/images/cohere-icon.svg',
            ) );
        }

        /**
         * Plugin admin notices
         *
         * @since  1.0.0
         */
        public function admin_notices()
        {
            if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ): ?>

                <div id="message" class="notice notice-error is-dismissible">
                    <p>
                        <?php printf(
                            __( 'AutomatorWP - Cohere requires %s in order to work. Please install and activate it.', 'automatorwp-cohere' ),
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
        private function meets_requirements()
        {
            if ( ! class_exists( 'AutomatorWP' ) ) {
                return false;
            }

            return true;

        }
    }

    /**
     * The main function responsible for returning the one true AutomatorWP_Cohere instance to functions everywhere
     *
     * @since       1.0.0
     * @return      \AutomatorWP_Cohere The one true AutomatorWP_Cohere
     */
    function AutomatorWP_Cohere()
    {
        return AutomatorWP_Cohere::instance();
    }

    /**
     * Bootstrap the plugin once all plugins are loaded.
     * Checking for automatorwp_register_integration() instead of the class ensures
     * the AutomatorWP core API is fully initialized before we register.
     *
     * @since 1.0.0
     */
    function automatorwp_cohere_init()
    {
        if ( ! function_exists( 'automatorwp_register_integration' ) ) return;
        AutomatorWP_Cohere();
    }
    add_action( 'plugins_loaded', 'automatorwp_cohere_init' );
}
