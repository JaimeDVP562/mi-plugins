<?php

/**
 * Plugin Name:           AutomatorWP - DeepSeek
 * Description:           AutomatorWP integration with DeepSeek.
 * Version:               1.0.0
 * Author:                Your Company
 * Text Domain:           automatorwp-deepseek
 * Domain Path:           /languages
 */

if (! defined('ABSPATH')) {
    exit;
}

final class AutomatorWP_Deepseek
{

    /**
     * @var AutomatorWP_Deepseek|null
     */
    private static $instance = null;

    /**
     * Get plugin instance.
     *
     * @return AutomatorWP_Deepseek
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
        $this->define('AUTOMATORWP_DEEPSEEK_VER', '1.0.0');
        $this->define('AUTOMATORWP_DEEPSEEK_FILE', __FILE__);
        $this->define('AUTOMATORWP_DEEPSEEK_DIR', plugin_dir_path(__FILE__));
        $this->define('AUTOMATORWP_DEEPSEEK_URL', plugin_dir_url(__FILE__));
        // Maximum prompt length to send to DeepSeek (characters)
        $this->define('AUTOMATORWP_DEEPSEEK_MAX_PROMPT_LENGTH', 3000);
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
        require_once AUTOMATORWP_DEEPSEEK_DIR . 'includes/functions.php';
        require_once AUTOMATORWP_DEEPSEEK_DIR . 'includes/admin.php';

        // AJAX and scripts (Added to connect JS and Admin Button)
        require_once AUTOMATORWP_DEEPSEEK_DIR . 'includes/ajax-functions.php';
        require_once AUTOMATORWP_DEEPSEEK_DIR . 'includes/scripts.php';

        // AutomatorWP specific components
        require_once AUTOMATORWP_DEEPSEEK_DIR . 'includes/tags.php';
        require_once AUTOMATORWP_DEEPSEEK_DIR . 'includes/actions/generate-text.php';
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
        automatorwp_register_integration('deepseek', array(
            'label' => __('DeepSeek', 'automatorwp-deepseek'),
            'icon'  => AUTOMATORWP_DEEPSEEK_URL . 'assets/img/dashicon-deepseek.svg',
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
            . esc_html__('AutomatorWP - DeepSeek requires AutomatorWP to be installed and active.', 'automatorwp-deepseek')
            . '</p></div>';
    }

    /**
     * Load translations.
     */
    public function load_textdomain()
    {
        load_plugin_textdomain(
            'automatorwp-deepseek',
            false,
            dirname(plugin_basename(AUTOMATORWP_DEEPSEEK_FILE)) . '/languages/'
        );
    }
}

/**
 * Main instance of AutomatorWP_Deepseek.
 */
function AutomatorWP_Deepseek()
{
    return AutomatorWP_Deepseek::instance();
}

// Start the plugin
add_action('plugins_loaded', 'AutomatorWP_Deepseek');
