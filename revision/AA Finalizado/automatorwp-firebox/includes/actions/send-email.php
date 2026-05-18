<?php
/**
 * Send Email Action
 *
 * @package     AutomatorWP\Integrations\Firebox\Actions\Send_Email
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AutomatorWP_FireBox_Send_Email extends AutomatorWP_Integration_Action
{

    public $integration = 'firebox';
    public $action = 'firebox_send_email';

    /**
     * Register the action
     */
    public function register()
    {
        automatorwp_register_action($this->action, array(
            'integration'   => $this->integration,
            'label'         => __('Send Email', 'automatorwp-firebox'),
            'select_option' => __('Send <strong>email</strong>', 'automatorwp-firebox'),
            'edit_label'    => sprintf(__('Send email with %1$s', 'automatorwp-firebox'), '{post}'),
            'log_label'     => sprintf(__('Email sent with %1$s', 'automatorwp-firebox'), '{post}'),
            'options'       => array(
                'post' => automatorwp_utilities_post_option(
                    array(
                        'name'              => __('Email Content:', 'automatorwp-firebox'),
                        'option_default'    => __('a message', 'automatorwp-firebox'),
                        'option_none'       => false,
                        'option_custom'     => true,
                        'option_custom_desc'=> __('Email ID', 'automatorwp-firebox'),
                        'post_type'         => 'email' // Assuming you have a custom post type 'email' for email content
                    )
                ),
            ),
        ));
    }

    /**
     * Action execution function
     *
     * @param stdClass  $action         The action object
     * @param int       $user_id        The user ID
     * @param array     $action_options The action's stored options (with tags already passed)
     * @param stdClass  $automation     The action's automation object
     */
    public function execute($action, $user_id, $action_options, $automation)
    {
        $email_id = absint($action_options['post']);

        // Bail if email ID is not valid
        if (!$email_id) {
            return;
        }

        // Get email content
        $email_content = get_post_field('post_content', $email_id);

        // Bail if email content is empty
        if (empty($email_content)) {
            return;
        }

        // Send email (you need to implement this part)
        // wp_mail( $to, $subject, $message );

        // Log the email action
        automatorwp_log($user_id, $action, $automation);
    }

}

new AutomatorWP_FireBox_Send_Email();
