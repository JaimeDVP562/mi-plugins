<?php

/**
 * Plugin Name:           AutomatorWP - LiteSpeed Cache
 * Description:           AutomatorWP integration with LiteSpeed Cache.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Text Domain:           automatorwp-litespeed
 * Domain Path:           /languages
 */

if (! defined('ABSPATH')) {
    exit;
}

final class AutomatorWP_Litespeed
{

    /**
     * @var AutomatorWP_Litespeed|null
     */
    private static $instance = null;

    /**
     * Get plugin instance.
     *
     * @return AutomatorWP_Litespeed
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
        $this->define('AUTOMATORWP_LITESPEED_VER', '1.0.0');
        $this->define('AUTOMATORWP_LITESPEED_FILE', __FILE__);
        $this->define('AUTOMATORWP_LITESPEED_DIR', plugin_dir_path(__FILE__));
        $this->define('AUTOMATORWP_LITESPEED_URL', plugin_dir_url(__FILE__));
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

        // Actions
        require_once AUTOMATORWP_LITESPEED_DIR . 'includes/actions/purge-all.php';
        require_once AUTOMATORWP_LITESPEED_DIR . 'includes/actions/purge-post.php';
        require_once AUTOMATORWP_LITESPEED_DIR . 'includes/actions/purge-url.php';
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
        return class_exists('AutomatorWP') && class_exists('LiteSpeed\Purge');
    }

    /**
     * Register integration.
     */
    public function register_integration()
    {
        automatorwp_register_integration('litespeed', array(
            'label' => __('LiteSpeed Cache', 'automatorwp-litespeed'),
            'icon'  => '',
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
            . esc_html__('AutomatorWP - LiteSpeed Cache requires AutomatorWP and LiteSpeed Cache to be installed and active.', 'automatorwp-litespeed')
            . '</p></div>';
    }

    /**
     * Load translations.
     */
    public function load_textdomain()
    {
        load_plugin_textdomain(
            'automatorwp-litespeed',
            false,
            dirname(plugin_basename(AUTOMATORWP_LITESPEED_FILE)) . '/languages/'
        );
    }
}

/**
 * Main instance of AutomatorWP_LiteSpeed.
 */
function AutomatorWP_Litespeed()
{
    return AutomatorWP_Litespeed::instance();
}

// Start the plugin
add_action('plugins_loaded', 'AutomatorWP_Litespeed');
