<?php
/**
 * Plugin Name:           AutomatorWP - Perplexity
 * Plugin URI:            https://automatorwp.com/add-ons/perplexity/
 * Description:           Connect AutomatorWP with Perplexity AI to send prompts, run deep research, hold multi-turn conversations, summarize URLs, translate text, analyse sentiment, extract structured data and classify content.
 * Version:               1.1.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-perplexity
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.9
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Perplexity
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AutomatorWP_Perplexity' ) ) {

    final class AutomatorWP_Perplexity {

        /**
         * @var         AutomatorWP_Perplexity $instance The one true AutomatorWP_Perplexity
         * @since       1.0.0
         */
        private static $instance;

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      AutomatorWP_Perplexity self::$instance The one true AutomatorWP_Perplexity
         */
        public static function instance() {
            if ( ! isset( self::$instance ) ) {
                self::$instance = new AutomatorWP_Perplexity();
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
            if ( ! defined( 'AUTOMATORWP_PERPLEXITY_VER' ) )  define( 'AUTOMATORWP_PERPLEXITY_VER',  '1.1.0' );
            if ( ! defined( 'AUTOMATORWP_PERPLEXITY_FILE' ) ) define( 'AUTOMATORWP_PERPLEXITY_FILE', __FILE__ );
            if ( ! defined( 'AUTOMATORWP_PERPLEXITY_DIR' ) )  define( 'AUTOMATORWP_PERPLEXITY_DIR',  plugin_dir_path( __FILE__ ) );
            if ( ! defined( 'AUTOMATORWP_PERPLEXITY_URL' ) )  define( 'AUTOMATORWP_PERPLEXITY_URL',  plugin_dir_url( __FILE__ ) );
        }

        /**
         * Include plugin files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/scripts.php';
            // Actions
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/actions/send-prompt.php';
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/actions/deep-research.php';
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/actions/conversation.php';
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/actions/clear-conversation.php';
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/actions/summarize-url.php';
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/actions/translate.php';
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/actions/sentiment.php';
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/actions/extract-data.php';
            require_once AUTOMATORWP_PERPLEXITY_DIR . 'includes/actions/classify.php';
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
            add_action( 'admin_notices',    array( $this, 'admin_notices' ) );
        }

        /**
         * Registers this integration
         *
         * @since 1.0.0
         */
        function register_integration() {
            automatorwp_register_integration( 'perplexity', array(
                'label' => 'Perplexity',
                'icon'  => AUTOMATORWP_PERPLEXITY_URL . 'assets/images/perplexity-icon.svg',
            ) );
        }

        /**
         * Plugin admin notices
         *
         * @since  1.0.0
         */
        public function admin_notices() {

            if ( ! $this->meets_requirements() && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ): ?>

                <div id="message" class="notice notice-error is-dismissible">
                    <p>
                        <?php printf(
                            __( 'AutomatorWP - Perplexity requires %s in order to work. Please install and activate it.', 'automatorwp-perplexity' ),
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
    }

    /**
     * The main function responsible for returning the one true AutomatorWP_Perplexity instance to functions everywhere
     *
     * @since       1.0.0
     * @return      \AutomatorWP_Perplexity The one true AutomatorWP_Perplexity
     */
    function AutomatorWP_Perplexity() {
        return AutomatorWP_Perplexity::instance();
    }

    function automatorwp_perplexity_init() {
        if ( ! function_exists( 'automatorwp_register_integration' ) ) return;
        AutomatorWP_Perplexity();
    }
    add_action( 'plugins_loaded', 'automatorwp_perplexity_init' );
}
