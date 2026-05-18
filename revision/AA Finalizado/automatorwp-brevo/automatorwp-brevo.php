<?php
/**
 * Plugin Name:           AutomatorWP - Brevo
 * Plugin URI:            https://automatorwp.com/add-ons/brevo/
 * Description:           Connect AutomatorWP with Brevo.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-brevo
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.9
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\Brevo
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

final class AutomatorWP_Brevo
{

    /**
     * @var         AutomatorWP_Brevo $instance The one true AutomatorWP_Brevo
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Brevo self::$instance The one true AutomatorWP_Brevo
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new AutomatorWP_Brevo();
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
        // Plugin version
        define('AUTOMATORWP_BREVO_VER', '1.0.0');

        // Plugin file
        define('AUTOMATORWP_BREVO_FILE', __FILE__);

        // Plugin path
        define('AUTOMATORWP_BREVO_DIR', plugin_dir_path(__FILE__));

        // Plugin URL
        define('AUTOMATORWP_BREVO_URL', plugin_dir_url(__FILE__));
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

        if ($this->meets_requirements()) {

            // Includes
            require_once AUTOMATORWP_BREVO_DIR . 'includes/admin.php';
            require_once AUTOMATORWP_BREVO_DIR . 'includes/functions.php';
            require_once AUTOMATORWP_BREVO_DIR . 'includes/ajax-functions.php';
            require_once AUTOMATORWP_BREVO_DIR . 'includes/scripts.php';

            //Actions
            require_once AUTOMATORWP_BREVO_DIR . 'includes/actions/create-contact.php';
            require_once AUTOMATORWP_BREVO_DIR . 'includes/actions/add-contact-to-list.php';
            require_once AUTOMATORWP_BREVO_DIR . 'includes/actions/create-list.php';
            require_once AUTOMATORWP_BREVO_DIR . 'includes/actions/create-deal.php';
            require_once AUTOMATORWP_BREVO_DIR . 'includes/actions/send-email.php';
            require_once AUTOMATORWP_BREVO_DIR . 'includes/actions/remove-contact-list.php';
        }
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

        add_action('automatorwp_init', array($this, 'register_integration'));

        add_filter('automatorwp_licenses_meta_boxes', array($this, 'license'));

        add_action('admin_notices', array($this, 'admin_notices'));

    }


    /**
     * Registers this integration
     *
     * @since 1.0.0
     */
    function register_integration()
    {

        automatorwp_register_integration(
            'brevo',
            array(
                'label' => 'Brevo',
                'icon' => AUTOMATORWP_BREVO_URL . 'assets/clickup.svg',
            )
        );

    }

    /**
     * Licensing
     *
     * @since 1.0.0
     *
     * @param array $meta_boxes
     *
     * @return array
     */
    function license($meta_boxes)
    {

        $meta_boxes['automatorwp-brevo-license'] = array(
            'title' => 'Brevo',
            'fields' => array(
                'automatorwp_brevo_license' => array(
                    'type' => 'edd_license',
                    'file' => AUTOMATORWP_BREVO_FILE,
                    'item_name' => 'Brevo',
                ),
            )
        );

        return $meta_boxes;

    }

    /**
     * Plugin admin notices.
     *
     * @since  1.0.0
     */
    public function admin_notices()
    {

        if (!$this->meets_requirements() && !defined('AUTOMATORWP_ADMIN_NOTICES')): ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __('AutomatorWP - Brevo requires %s in order to work. Please install and activate it.', 'automatorwp-brevo'),
                        '<a href="https://wordpress.org/plugins/automatorwp/" target="_blank">AutomatorWP</a>'
                    ); ?>
                </p>
            </div>

            <?php define('AUTOMATORWP_ADMIN_NOTICES', true); ?>

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

        if (!class_exists('AutomatorWP')) {
            return false;
        }

        return true;

    }



}

/**
 * The main function responsible for returning the one true AutomatorWP_Brevo instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \AutomatorWP_Brevo The one true AutomatorWP_Brevo
 */
function AutomatorWP_Brevo()
{
    return AutomatorWP_Brevo::instance();
}
add_action('plugins_loaded', 'AutomatorWP_Brevo');
