<?php
/**
 * Plugin Name:     AutomatorWP - Dailybot
 * Plugin URI:      https://automatorwp.com/add-ons/automatorwp-dailybot
 * Description:     Connect AutomatorWP with Dailybot.
 * Version:         1.0.0
 * Author:          AutomatorWP
 * Author URI:      https://automatorwp.com/
 * Text Domain:     automatorwp-dailybot-integration
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         AutomatorWP\Dailybot
 * @author          AutomatorWP
 * @copyright       Copyright (c) AutomatorWP
 */

final class AutomatorWP_Integration_Dailybot {

    /**
     * @var AutomatorWP_Integration_Dailybot $instance The one true AutomatorWP_Integration_Dailybot
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access  public
     * @since   1.0.0
     * @return  AutomatorWP_Integration_Dailybot self::$instance
     */
    public static function instance() {
        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_Dailybot();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @access  private
     * @since   1.0.0
     * @return  void
     */
    private function constants() {
        // Plugin version
        define( 'AUTOMATORWP_DAILYBOT_VER', '1.0.0' );

        // Plugin file
        define( 'AUTOMATORWP_DAILYBOT_FILE', __FILE__ );

        // Plugin path
        define( 'AUTOMATORWP_DAILYBOT_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'AUTOMATORWP_DAILYBOT_URL', plugin_dir_url( __FILE__ ) );

        // Text domain
        define( 'AUTOMATORWP_DAILYBOT_TEXT_DOMAIN', 'automatorwp-dailybot-integration' );

        // API base URL
        define( 'AUTOMATORWP_DAILYBOT_API_BASE', 'https://api.dailybot.com/v1/' );

        // API timeout
        define( 'AUTOMATORWP_DAILYBOT_TIMEOUT', 45 );
    }

    /**
     * Include plugin files
     *
     * @access  private
     * @since   1.0.0
     * @return  void
     */
    private function includes() {
        if ( $this->meets_requirements() ) {
            require_once AUTOMATORWP_DAILYBOT_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_DAILYBOT_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_DAILYBOT_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_DAILYBOT_DIR . 'includes/rest-api.php';
            require_once AUTOMATORWP_DAILYBOT_DIR . 'includes/scripts.php';
            require_once AUTOMATORWP_DAILYBOT_DIR . 'includes/tags.php';

            // Triggers
            require_once AUTOMATORWP_DAILYBOT_DIR . 'includes/triggers/invitation-created.php';
            require_once AUTOMATORWP_DAILYBOT_DIR . 'includes/triggers/invitation-accepted.php';

            // Actions
            require_once AUTOMATORWP_DAILYBOT_DIR . 'includes/actions/send-email.php';
            require_once AUTOMATORWP_DAILYBOT_DIR . 'includes/actions/send-message.php';
            require_once AUTOMATORWP_DAILYBOT_DIR . 'includes/actions/open-conversation.php';
        }
    }

    /**
     * Setup plugin hooks
     *
     * @access  private
     * @since   1.0.0
     * @return  void
     */
    private function hooks() {
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
    }

    /**
     * Register this integration
     *
     * @access  public
     * @since   1.0.0
     */
    public function register_integration() {
        automatorwp_register_integration( 'dailybot', array(
            'label' => 'Dailybot',
            'icon'  => AUTOMATORWP_DAILYBOT_URL . 'assets/images/dailybot.jpg',
        ) );
    }

    /**
     * Admin notices
     *
     * @access  public
     * @since   1.0.0
     */
    public function admin_notices() {
        if ( ! $this->meets_requirements() ) { ?>
            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php _e( 'AutomatorWP - Dailybot requires ', AUTOMATORWP_DAILYBOT_TEXT_DOMAIN ); ?>
                    <a href="https://automatorwp.com/">AutomatorWP</a><?php echo '.'; ?>
                </p>
            </div>
        <?php }
    }

    /**
     * Check plugin requirements
     *
     * @access  private
     * @since   1.0.0
     * @return  bool
     */
    private function meets_requirements() {
        return class_exists( 'AutomatorWP' );
    }

    /**
     * Load text domain
     *
     * @since 1.0.0
     */
    public function load_textdomain() {
        $lang_dir = AUTOMATORWP_DAILYBOT_DIR . '/languages/';
        $locale   = apply_filters( 'plugin_locale', get_locale(), 'automatorwp-dailybot' );
        $mofile   = sprintf( '%1$s-%2$s.mo', 'automatorwp-dailybot', $locale );

        load_textdomain( 'automatorwp-dailybot', WP_LANG_DIR . '/automatorwp-dailybot/' . $mofile );
        load_plugin_textdomain( 'automatorwp-dailybot', false, $lang_dir );
    }
}

/**
 * Returns the one true AutomatorWP_Integration_Dailybot instance
 *
 * @since  1.0.0
 * @return AutomatorWP_Integration_Dailybot
 */
function AutomatorWP_Integration_Dailybot() {
    return AutomatorWP_Integration_Dailybot::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_Integration_Dailybot' );