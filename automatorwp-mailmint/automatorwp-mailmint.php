<?php
/**
 * Plugin Name:           AutomatorWP - Mail Mint
 * Plugin URI:            https://automatorwp.com/add-ons/mail-mint/
 * Description:           Connect AutomatorWP with Mail Mint to create contacts, apply tags and lists, change subscription status, and trigger automations on Mail Mint events.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-mailmint
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.9
 * Requires PHP:          7.4
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\MailMint
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

final class AutomatorWP_Integration_MailMint
{
    /**
     * @var         AutomatorWP_Integration_MailMint $instance The one true AutomatorWP_Integration_MailMint
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      AutomatorWP_Integration_MailMint self::$instance
     */
    public static function instance()
    {
        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_MailMint();
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
        define( 'AUTOMATORWP_MAILMINT_VER',  '1.0.0' );
        define( 'AUTOMATORWP_MAILMINT_FILE', __FILE__ );
        define( 'AUTOMATORWP_MAILMINT_DIR',  plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_MAILMINT_URL',  plugin_dir_url( __FILE__ ) );
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
        if ( ! $this->meets_requirements() ) {
            return;
        }

        // Core
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/admin.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/functions.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/ajax-functions.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/scripts.php';

        // Triggers — contact lifecycle
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/triggers/contact-created.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/triggers/contact-subscribed.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/triggers/contact-unsubscribed.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/triggers/double-optin-confirmed.php';

        // Triggers — tags
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/triggers/tag-applied.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/triggers/tag-removed.php';

        // Triggers — lists
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/triggers/list-applied.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/triggers/list-removed.php';

        // Triggers — forms & engagement
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/triggers/form-submitted.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/triggers/email-opened.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/triggers/email-clicked.php';

        // Actions — contact management
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/actions/create-contact.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/actions/change-status.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/actions/send-double-optin.php';

        // Actions — tags
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/actions/add-tag.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/actions/remove-tag.php';

        // Actions — lists
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/actions/add-to-list.php';
        require_once AUTOMATORWP_MAILMINT_DIR . 'includes/actions/remove-from-list.php';
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
     * Registers this integration with AutomatorWP
     *
     * @since 1.0.0
     */
    public function register_integration()
    {
        automatorwp_register_integration( 'mailmint', array(
            'label' => 'Mail Mint',
            'icon'  => AUTOMATORWP_MAILMINT_URL . 'assets/mailmint-icon.svg',
        ) );
    }

    /**
     * Show admin notice when a required plugin is not active.
     *
     * @since 1.0.0
     */
    public function admin_notices()
    {
        if ( ! defined( 'ABSPATH' ) ) return;

        if ( ! class_exists( 'AutomatorWP' ) && ! defined( 'AUTOMATORWP_ADMIN_NOTICES' ) ) : ?>
            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php echo sprintf(
                        __( '<strong>AutomatorWP - Mail Mint</strong> requires <a href="%s" target="_blank">AutomatorWP</a> to be installed and active.', 'automatorwp-mailmint' ),
                        'https://wordpress.org/plugins/automatorwp/'
                    ); ?>
                </p>
            </div>
            <?php define( 'AUTOMATORWP_ADMIN_NOTICES', true ); ?>
        <?php endif;

        if ( ! defined( 'MRM_VERSION' ) ) : ?>
            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php echo sprintf(
                        __( '<strong>AutomatorWP - Mail Mint</strong> requires <a href="%s" target="_blank">Mail Mint</a> to be installed and active.', 'automatorwp-mailmint' ),
                        'https://wordpress.org/plugins/mail-mint/'
                    ); ?>
                </p>
            </div>
        <?php endif;
    }

    /**
     * Check if all plugin requirements are met
     *
     * @since 1.0.0
     *
     * @return bool
     */
    private function meets_requirements()
    {
        return class_exists( 'AutomatorWP' ) && defined( 'MRM_VERSION' );
    }
}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_MailMint instance
 *
 * @since       1.0.0
 * @return      AutomatorWP_Integration_MailMint
 */
function AutomatorWP_MailMint()
{
    return AutomatorWP_Integration_MailMint::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_MailMint' );
