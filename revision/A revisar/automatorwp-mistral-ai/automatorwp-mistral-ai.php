<?php
/**
 * Plugin Name:           AutomatorWP - Mistral AI
 * Description:           AutomatorWP integration with Mistral AI.
 * Version:               1.0.0
 * Author:                Your Company
 * Text Domain:            automatorwp-mistral-ai
 * Domain Path:            /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class AutomatorWP_Mistral_AI {

    /**
     * @var AutomatorWP_Mistral_AI|null
     */
    private static $instance = null;

    /**
     * Get plugin instance.
     *
     * @return AutomatorWP_Mistral_AI
     */
    public static function instance() {

        if ( self::$instance === null ) {
            self::$instance = new self();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define plugin constants.
     */
    private function constants() {

        if ( ! defined( 'AUTOMATORWP_MISTRAL_AI_VER' ) ) {
            define( 'AUTOMATORWP_MISTRAL_AI_VER', '1.0.0' );
        }

        if ( ! defined( 'AUTOMATORWP_MISTRAL_AI_FILE' ) ) {
            define( 'AUTOMATORWP_MISTRAL_AI_FILE', __FILE__ );
        }

        if ( ! defined( 'AUTOMATORWP_MISTRAL_AI_DIR' ) ) {
            define( 'AUTOMATORWP_MISTRAL_AI_DIR', plugin_dir_path( __FILE__ ) );
        }

        if ( ! defined( 'AUTOMATORWP_MISTRAL_AI_URL' ) ) {
            define( 'AUTOMATORWP_MISTRAL_AI_URL', plugin_dir_url( __FILE__ ) );
        }
    }

    /**
     * Include required files.
     */
    private function includes() {

        if ( ! $this->meets_requirements() ) {
            return;
        }

        require_once AUTOMATORWP_MISTRAL_AI_DIR . 'includes/functions.php';
        require_once AUTOMATORWP_MISTRAL_AI_DIR . 'includes/scripts.php';
        require_once AUTOMATORWP_MISTRAL_AI_DIR . 'includes/admin.php';
        require_once AUTOMATORWP_MISTRAL_AI_DIR . 'includes/ajax-functions.php';
        require_once AUTOMATORWP_MISTRAL_AI_DIR . 'includes/tags.php';

        // Actions
        require_once AUTOMATORWP_MISTRAL_AI_DIR . 'includes/actions/generate-text.php';
        require_once AUTOMATORWP_MISTRAL_AI_DIR . 'includes/actions/generate-image.php';
    }

    /**
     * Register hooks.
     */
    private function hooks() {
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    /**
     * Check requirements.
     *
     * @return bool
     */
    private function meets_requirements() {
        return class_exists( 'AutomatorWP' );
    }

    /**
     * Register integration.
     */
    public function register_integration() {

        automatorwp_register_integration( 'mistral_ai', array(
            'label' => __( 'Mistral AI', 'automatorwp-mistral-ai' ),
            'icon'  => AUTOMATORWP_MISTRAL_AI_URL . 'assets/img/dashicon-mistral.svg',
        ) );
    }

    /**
     * Show admin notices.
     */
    public function admin_notices() {

        if ( $this->meets_requirements() ) {
            return;
        }

        echo '<div class="notice notice-error"><p>'
            . esc_html__( 'AutomatorWP - Mistral AI requires AutomatorWP to be installed and active.', 'automatorwp-mistral-ai' )
            . '</p></div>';
    }

    /**
     * Load translations.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'automatorwp-mistral-ai',
            false,
            dirname( plugin_basename( AUTOMATORWP_MISTRAL_AI_FILE ) ) . '/languages/'
        );
    }
}

function AutomatorWP_Mistral_AI() {
    return AutomatorWP_Mistral_AI::instance();
}

add_action( 'plugins_loaded', 'AutomatorWP_Mistral_AI' );
