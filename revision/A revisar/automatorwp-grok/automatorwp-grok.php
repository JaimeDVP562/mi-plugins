<?php

/**
 * Plugin Name:           AutomatorWP - Grok
 * Description:           AutomatorWP integration with Grok (xAI).
 * Version:               1.0.0
 * Author:                Adrian
 * Text Domain:           automatorwp-grok
 * Domain Path:           /languages
 */

if (! defined('ABSPATH')) {
    exit;
}

final class AutomatorWP_Grok
{

    /**
     * @var AutomatorWP_Grok|null
     */
    private static $instance = null;

    /**
     * Get plugin instance.
     *
     * @return AutomatorWP_Grok
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
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
    private function constants()
    {
        $this->define('AUTOMATORWP_GROK_VER', '1.0.0');
        $this->define('AUTOMATORWP_GROK_FILE', __FILE__);
        $this->define('AUTOMATORWP_GROK_DIR', plugin_dir_path(__FILE__));
        $this->define('AUTOMATORWP_GROK_URL', plugin_dir_url(__FILE__));
        $this->define('AUTOMATORWP_GROK_ASSETS_URL', AUTOMATORWP_GROK_URL . 'assets/');
    }

    private function define($name, $value)
    {
        if (! defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Include required files.
     */
    private function includes()
    {
        if (! $this->meets_requirements()) {
            return;
        }

        // Core logic and settings
        require_once AUTOMATORWP_GROK_DIR . 'includes/functions.php';
        require_once AUTOMATORWP_GROK_DIR . 'includes/admin.php';

        // AJAX and scripts (Connects JS and Admin Button)
        require_once AUTOMATORWP_GROK_DIR . 'includes/ajax-functions.php';
        require_once AUTOMATORWP_GROK_DIR . 'includes/scripts.php';

        // AutomatorWP specific components
        require_once AUTOMATORWP_GROK_DIR . 'includes/tags.php';

        // Load Actions
        add_action('automatorwp_init', function () {
            require_once AUTOMATORWP_GROK_DIR . 'includes/actions/generate-text.php';
            require_once AUTOMATORWP_GROK_DIR . 'includes/actions/analyze-sentiment.php';
            require_once AUTOMATORWP_GROK_DIR . 'includes/actions/summarize-content.php';
        });
    }

    /**
     * Register hooks.
     */
    private function hooks()
    {
        add_action('init', array($this, 'load_textdomain'));
        add_action('automatorwp_init', array($this, 'register_integration'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * Check requirements.
     *
     * @return bool
     */
    private function meets_requirements()
    {
        return class_exists('AutomatorWP');
    }

    /**
     * Register integration.
     */
    public function register_integration()
    {
        automatorwp_register_integration('grok', array(
            'label' => __('Grok', 'automatorwp-grok'),
            'icon'  => AUTOMATORWP_GROK_URL . 'assets/img/dashicon-grok.svg',
        ));
    }

    /**
     * Show admin notices.
     */
    public function admin_notices()
    {
        if ($this->meets_requirements()) {
            return;
        }

        echo '<div class="notice notice-error"><p>'
            . esc_html__('AutomatorWP - Grok requires AutomatorWP to be installed and active.', 'automatorwp-grok')
            . '</p></div>';
    }

    /**
     * Load translations.
     */
    public function load_textdomain()
    {
        load_plugin_textdomain(
            'automatorwp-grok',
            false,
            dirname(plugin_basename(AUTOMATORWP_GROK_FILE)) . '/languages/'
        );
    }
}

/**
 * Main instance of AutomatorWP_Grok.
 */
function AutomatorWP_Grok()
{
    return AutomatorWP_Grok::instance();
}

// Start the plugin
add_action('plugins_loaded', 'AutomatorWP_Grok');
