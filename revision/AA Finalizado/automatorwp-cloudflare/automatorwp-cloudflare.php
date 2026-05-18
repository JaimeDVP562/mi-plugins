<?php
/**
 * Plugin Name:     AutomatorWP - Cloudflare
 * Plugin URI:      https://automatorwp.com/add-ons/cloudflare/
 * Description:     Connect AutomatorWP with Cloudflare. Purge cache automatically as part of your automation workflows.
 * Version:         1.0.0
 * Author:          AutomatorWP
 * Author URI:      https://automatorwp.com/
 * License:         GNU AGPL v3.0
 * License URI:     http://www.gnu.org/licenses/agpl-3.0.html
 * Text Domain:     automatorwp-cloudflare
 * Domain Path:     /languages/
 * Requires PHP:    7.4
 *
 * @package         AutomatorWP\Cloudflare
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Plugin constants
define( 'AUTOMATORWP_CLOUDFLARE_VER',  '1.0.0' );
define( 'AUTOMATORWP_CLOUDFLARE_FILE', __FILE__ );
define( 'AUTOMATORWP_CLOUDFLARE_DIR',  plugin_dir_path( __FILE__ ) );
define( 'AUTOMATORWP_CLOUDFLARE_URL',  plugin_dir_url( __FILE__ ) );
define( 'AUTOMATORWP_CLOUDFLARE_SLUG', 'cloudflare' );

/**
 * Main plugin class - Singleton pattern
 *
 * @since 1.0.0
 */
class AutomatorWP_Cloudflare {

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var   AutomatorWP_Cloudflare
     */
    private static $instance = null;

    /**
     * Returns the singleton instance
     *
     * @since  1.0.0
     * @return AutomatorWP_Cloudflare
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Register hooks
     *
     * @since 1.0.0
     */
    public function hooks() {
        add_action( 'automatorwp_init', array( $this, 'init' ) );
    }

    /**
     * Initialize the plugin after AutomatorWP is ready
     *
     * @since 1.0.0
     */
    public function init() {

        // Bail if AutomatorWP integration base class is not available
        if ( ! class_exists( 'AutomatorWP_Integration_Action' ) ) {
            return;
        }

        $this->includes();
        $this->register_integration();
    }

    /**
     * Include all required files
     *
     * @since 1.0.0
     */
    public function includes() {
        require_once AUTOMATORWP_CLOUDFLARE_DIR . 'includes/admin.php';
        require_once AUTOMATORWP_CLOUDFLARE_DIR . 'includes/functions.php';
        require_once AUTOMATORWP_CLOUDFLARE_DIR . 'includes/actions/purge-all.php';
        require_once AUTOMATORWP_CLOUDFLARE_DIR . 'includes/actions/purge-url.php';
        require_once AUTOMATORWP_CLOUDFLARE_DIR . 'includes/actions/purge-post.php';
    }

    /**
     * Register the Cloudflare integration with AutomatorWP
     *
     * @since 1.0.0
     */
    public function register_integration() {
        automatorwp_register_integration( 'cloudflare', array(
            'label' => 'Cloudflare',
            'icon'  => AUTOMATORWP_CLOUDFLARE_URL . 'assets/cloudflare.svg',
        ) );
    }
}

/**
 * Returns the main plugin instance
 *
 * @since  1.0.0
 * @return AutomatorWP_Cloudflare
 */
function AutomatorWP_Cloudflare() {
    return AutomatorWP_Cloudflare::instance();
}

// Check that AutomatorWP is active before booting
add_action( 'plugins_loaded', function() {
    if ( ! function_exists( 'automatorwp_register_integration' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>'
                . __( '<strong>AutomatorWP – Cloudflare</strong> requires AutomatorWP to be installed and active.', 'automatorwp-cloudflare' )
                . '</p></div>';
        } );
        return;
    }
    AutomatorWP_Cloudflare();
} );
