<?php

/**
 * Plugin Name:           AutomatorWP - Zoho
 * Plugin URI:            https://automatorwp.com/add-ons/zoho/
 * Description:           Connect AutomatorWP with Zoho CRM.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-zoho
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.6
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Zoho
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

if (!defined('ABSPATH')) exit;

final class AutomatorWP_Zoho
{

    /**
     * @var         AutomatorWP_Zoho $instance The one true AutomatorWP_Zoho
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new AutomatorWP_Zoho();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     */
    private function constants()
    {
        // Plugin version
        if (!defined('AUTOMATORWP_ZOHO_VER')) {
            define('AUTOMATORWP_ZOHO_VER', '1.0.0');
        }

        // Plugin file
        if (!defined('AUTOMATORWP_ZOHO_FILE')) {
            define('AUTOMATORWP_ZOHO_FILE', __FILE__);
        }

        // Plugin path
        if (!defined('AUTOMATORWP_ZOHO_DIR')) {
            define('AUTOMATORWP_ZOHO_DIR', plugin_dir_path(__FILE__));
        }

        // Plugin URL
        if (!defined('AUTOMATORWP_ZOHO_URL')) {
            define('AUTOMATORWP_ZOHO_URL', plugin_dir_url(__FILE__));
        }
    }

    /**
     * Include plugin files
     */
    private function includes()
    {

        if ($this->meets_requirements()) {

            // Core Includes
            require_once AUTOMATORWP_ZOHO_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_ZOHO_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_ZOHO_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_ZOHO_DIR . 'includes/scripts.php';

            // Actions - Loading your 3 professional actions
            $actions_dir = AUTOMATORWP_ZOHO_DIR . 'includes/actions/';

            if (file_exists($actions_dir . 'create-lead.php')) {
                require_once $actions_dir . 'create-lead.php';
            }
            if (file_exists($actions_dir . 'create-contact.php')) {
                require_once $actions_dir . 'create-contact.php';
            }
            if (file_exists($actions_dir . 'create-task.php')) {
                require_once $actions_dir . 'create-task.php';
            }
        }
    }

    /**
     * Setup plugin hooks
     */
    private function hooks()
    {
        add_action('automatorwp_init', array($this, 'register_integration'));
        add_filter('automatorwp_licenses_meta_boxes', array($this, 'license'));

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('init', array($this, 'load_textdomain'));
    }

    /**
     * Registers this integration
     */
    public function register_integration()
    {
        automatorwp_register_integration('zoho', array(
            'label' => 'Zoho CRM',
            'icon'  => AUTOMATORWP_ZOHO_URL . 'assets/zoho.svg', // Path to your logo
        ));
    }

    /**
     * Licensing
     */
    public function license($meta_boxes)
    {
        $meta_boxes['automatorwp-zoho-license'] = array(
            'title' => 'Zoho CRM',
            'fields' => array(
                'automatorwp_zoho_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_ZOHO_FILE,
                    'item_name' => 'Zoho CRM',
                ),
            )
        );

        return $meta_boxes;
    }

    public function activate() {}
    public function deactivate() {}

    /**
     * Plugin admin notices.
     */
    public function admin_notices()
    {
        if (! $this->meets_requirements() && ! defined('AUTOMATORWP_ADMIN_NOTICES')) { ?>
            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __('AutomatorWP - Zoho requires %s to work.', 'automatorwp-zoho'),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>'
                    ); ?>
                </p>
            </div>
<?php
            define('AUTOMATORWP_ADMIN_NOTICES', true);
        }
    }

    private function meets_requirements()
    {
        return class_exists('AutomatorWP');
    }

    /**
     * Internationalization
     */
    public function load_textdomain()
    {
        $lang_dir = AUTOMATORWP_ZOHO_DIR . 'languages/';
        $lang_dir = apply_filters('automatorwp_zoho_languages_directory', $lang_dir);

        $locale = apply_filters('plugin_locale', get_locale(), 'automatorwp-zoho');
        $mofile = sprintf('automatorwp-zoho-%s.mo', $locale);

        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/automatorwp-zoho/' . $mofile;

        if (file_exists($mofile_global)) {
            load_textdomain('automatorwp-zoho', $mofile_global);
        } elseif (file_exists($mofile_local)) {
            load_textdomain('automatorwp-zoho', $mofile_local);
        } else {
            load_plugin_textdomain('automatorwp-zoho', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }
    }
}

/**
 * Main function
 */
function AutomatorWP_Zoho()
{
    return AutomatorWP_Zoho::instance();
}
add_action('plugins_loaded', 'AutomatorWP_Zoho');
