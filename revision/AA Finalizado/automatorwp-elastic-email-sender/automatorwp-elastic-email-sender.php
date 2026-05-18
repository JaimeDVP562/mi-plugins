<?php
/**
 * Plugin Name:           AutomatorWP - Elastic Email Sender
 * Plugin URI:            https://automatorwp.com/
 * Description:           Connect AutomatorWP with ElasticEmailSender.
 * Version:               1.0.0
 * Author:                AutomatorWP
 * Author URI:            https://automatorwp.com/
 * Text Domain:           automatorwp-elasticmailsender
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          6.5
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               AutomatorWP\ElasticEmailSender
 * @author                AutomatorWP
 * @copyright             Copyright (c) AutomatorWP
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class AutomatorWP_Integration_ElasticEmailSender {

    /**
     * @var         AutomatorWP_Integration_ElasticEmailSender $instance The one true AutomatorWP_Integration_ElasticEmailSender
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @since       1.0.0
     * @return      AutomatorWP_Integration_ElasticEmailSender
     */
    public static function instance() {

        if ( ! self::$instance ) {
            self::$instance = new AutomatorWP_Integration_ElasticEmailSender();
            self::$instance->constants();
            self::$instance->hooks();
            self::$instance->includes();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @since       1.0.0
     * @return      void
     */
    private function constants() {

        define( 'AUTOMATORWP_ELASTICMAILSENDER_VER',  '1.0.0' );
        define( 'AUTOMATORWP_ELASTICMAILSENDER_FILE', __FILE__ );
        define( 'AUTOMATORWP_ELASTICMAILSENDER_DIR',  plugin_dir_path( __FILE__ ) );
        define( 'AUTOMATORWP_ELASTICMAILSENDER_URL',  plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if ( $this->meets_requirements() ) {
            // General functions and tags
            $functions_file = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/functions.php';
            if ( file_exists( $functions_file ) ) require_once $functions_file;

            $tags_file = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/tags.php';
            if ( file_exists( $tags_file ) ) require_once $tags_file;

            $ajax_file = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/ajax-functions.php';
            if ( file_exists( $ajax_file ) ) require_once $ajax_file;

            // Load admin settings ONLY in the backend
            if ( is_admin() ) {
                $admin_file = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/admin.php';
                if ( file_exists( $admin_file ) ) require_once $admin_file;
            }

            // Load triggers
            $trigger_opened = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/triggers/trigger-email-opened.php';
            if ( file_exists( $trigger_opened ) ) require_once $trigger_opened;
            
            $trigger_clicked = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/triggers/trigger-email-clicked.php';
            if ( file_exists( $trigger_clicked ) ) require_once $trigger_clicked;

            $trigger_unsubscribed = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/triggers/trigger-email-unsubscribed.php';
            if ( file_exists( $trigger_unsubscribed ) ) require_once $trigger_unsubscribed;

            $trigger_bounced = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/triggers/trigger-email-bounced.php';
            if ( file_exists( $trigger_bounced ) ) require_once $trigger_bounced;

            $trigger_spam = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/triggers/trigger-email-spam.php';
            if ( file_exists( $trigger_spam ) ) require_once $trigger_spam;
            
            // Load actions
            $action_send_email = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/actions/action-send-email.php';
            if ( file_exists( $action_send_email ) ) require_once $action_send_email;

            $action_send_template = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/actions/action-send-email-template.php';
            if ( file_exists( $action_send_template ) ) require_once $action_send_template;

            $action_add_contact = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/actions/action-add-contact-to-list.php';
            if ( file_exists( $action_add_contact ) ) require_once $action_add_contact;

            $action_add_update = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/actions/action-add-update-contact.php';
            if ( file_exists( $action_add_update ) ) require_once $action_add_update;

            $action_remove_contact = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/actions/action-remove-contact-from-list.php';
            if ( file_exists( $action_remove_contact ) ) require_once $action_remove_contact;

            $action_unsubscribe_contact = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/actions/action-unsubscribe-contact.php';
            if ( file_exists( $action_unsubscribe_contact ) ) require_once $action_unsubscribe_contact;

            $action_delete_contact = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/actions/action-delete-contact.php';
            if ( file_exists( $action_delete_contact ) ) require_once $action_delete_contact;

            $action_send_attachment = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/actions/action-send-email-attachment.php';
            if ( file_exists( $action_send_attachment ) ) require_once $action_send_attachment;

            $action_update_field = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/actions/action-update-contact-field.php';
            if ( file_exists( $action_update_field ) ) require_once $action_update_field;

            $action_send_to_list = AUTOMATORWP_ELASTICMAILSENDER_DIR . 'includes/actions/action-send-email-to-list.php';
            if ( file_exists( $action_send_to_list ) ) require_once $action_send_to_list;
        }
    }

    /**
     * Setup plugin hooks
     *
     * @since       1.0.0
     * @return      void
     */
    private function hooks() {

        add_action( 'automatorwp_init', array( $this, 'register_integration' ) );
        add_action( 'init',             array( $this, 'load_textdomain' ) );
        add_action( 'admin_notices',    array( $this, 'admin_notices' ) );
    }

    /**
     * Registers this integration with AutomatorWP
     *
     * @since 1.0.0
     */
    public function register_integration() {

        automatorwp_register_integration( 'elasticmailsender', array(
            'label' => 'ElasticEmailSender',
            'icon'  => AUTOMATORWP_ELASTICMAILSENDER_URL . 'assets/elastic-email-sender-icon.jpg',
        ) );
    }

    /**
     * Load plugin textdomain
     *
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain() {

        load_plugin_textdomain(
            'automatorwp-elasticmailsender',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );
    }

    /**
     * Show admin notices if requirements are not met
     *
     * @since       1.0.0
     * @return      void
     */
    public function admin_notices() {

        if ( $this->meets_requirements() ) {
            return;
        }

        ?>
        <div class="error">
            <p><?php printf(
                __( 'AutomatorWP - ElasticEmailSender requires <a href="%s">AutomatorWP</a> in order to work. Please install and activate it first.', 'automatorwp-elasticmailsender' ),
                'https://wordpress.org/plugins/automatorwp/'
            ); ?></p>
        </div>
        <?php
    }

    /**
     * Check if all requirements are met
     *
     * @since       1.0.0
     * @return      bool
     */
    private function meets_requirements() {

        if ( ! class_exists( 'AutomatorWP' ) ) {
            return false;
        }

        return true;
    }
}

/**
 * The main function responsible for returning the one true AutomatorWP_Integration_ElasticEmailSender instance
 *
 * @since       1.0.0
 * @return      AutomatorWP_Integration_ElasticEmailSender
 */
function AutomatorWP_ElasticEmailSender_Init() {
    return AutomatorWP_Integration_ElasticEmailSender::instance();
}
add_action( 'plugins_loaded', 'AutomatorWP_ElasticEmailSender_Init',11);