<?php

/**
 * Zoho Action: Create a contact
 *
 * @package     AutomatorWP\Integrations\Zoho\Actions\Create_Contact
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AutomatorWP_Zoho_Create_Contact extends AutomatorWP_Integration_Action
{
    public $integration = 'zoho';
    public $action      = 'zoho_create_contact';
    public $result      = '';

    /**
     * Register the action
     * * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_action($this->action, array(
            'integration'   => $this->integration,
            'label'         => __('Create a contact (Zoho)', 'automatorwp-zoho'),
            'select_option' => __('Create a <strong>contact</strong> in <strong>Zoho CRM</strong>', 'automatorwp-zoho'),
            /* translators: %1$s: Email. */
            'edit_label'    => sprintf(__('Create a contact in Zoho for %1$s', 'automatorwp-zoho'), '{email}'),
            /* translators: %1$s: Email. */
            'log_label'     => sprintf(__('Create a contact in Zoho for %1$s', 'automatorwp-zoho'), '{email}'),

            'options'       => array(
                'contact_details' => array(
                    'from'    => 'contact_details',
                    'fields'  => array(
                        'email' => array(
                            'name'        => __('Email:', 'automatorwp-zoho'),
                            'type'        => 'text',
                            'default'     => '{user_email}',
                            'placeholder' => __('e.g. email@example.com', 'automatorwp-zoho'),
                            'required'    => true,
                        ),
                        'last_name' => array(
                            'name'        => __('Last Name:', 'automatorwp-zoho'),
                            'type'        => 'text',
                            'default'     => '{user_lastname}',
                            'placeholder' => __('Required by Zoho', 'automatorwp-zoho'),
                            'required'    => true,
                        ),
                        'first_name' => array(
                            'name'        => __('First Name:', 'automatorwp-zoho'),
                            'type'        => 'text',
                            'default'     => '{user_firstname}',
                            'required'    => false,
                        ),
                    ),
                ),
            ),
        ));
    }

    /**
     * Action execute function
     *
     * @since 1.0.0
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options
     * @param stdClass  $automation         The action's automation object
     */
    public function execute($action, $user_id, $action_options, $automation)
    {
        $email      = sanitize_email($action_options['email'] ?? '');
        $last_name  = sanitize_text_field($action_options['last_name'] ?? '');
        $first_name = sanitize_text_field($action_options['first_name'] ?? '');

        if (empty($email) || empty($last_name)) {
            $this->result = __('Missing required fields (Email or Last Name).', 'automatorwp-zoho');
            return;
        }

        // Data structure for Zoho CRM API (Contacts Module)
        $body = array(
            'data' => array(
                array(
                    'Last_Name'  => $last_name,
                    'First_Name' => $first_name,
                    'Email'      => $email,
                )
            )
        );

        if (!function_exists('automatorwp_zoho_api_request')) {
            $this->result = __('Zoho helper function is missing.', 'automatorwp-zoho');
            return;
        }

        $response = automatorwp_zoho_api_request('Contacts', $body, 'POST');

        if (is_wp_error($response)) {
            $this->result = $response->get_error_message();
            return;
        }

        $code = wp_remote_retrieve_response_code($response);
        $res_body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code >= 200 && $code < 300 && isset($res_body['data'][0]['status']) && $res_body['data'][0]['status'] === 'success') {
            $this->result = __('Contact created successfully in Zoho.', 'automatorwp-zoho');
        } else {
            $error_msg = $res_body['data'][0]['message'] ?? 'Unknown error';
            $this->result = sprintf(__('Zoho error: %s (HTTP %d)', 'automatorwp-zoho'), $error_msg, $code);
        }
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {
        add_filter('automatorwp_automation_ui_after_item_label', array($this, 'configuration_notice'), 10, 2);
        add_filter('automatorwp_user_completed_action_log_meta', array($this, 'log_meta'), 10, 5);
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 3);

        parent::hooks();
    }

    /**
     * Configuration notice
     * * @since 1.0.0
     */
    public function configuration_notice($object, $item_type)
    {
        if ($item_type !== 'action' || (!isset($object->type) || $object->type !== $this->action)) {
            return $object;
        }

        $access_token = get_option('automatorwp_zoho_access_token', '');

        if (!empty($access_token)) {
            return $object;
        }

        $url = admin_url('admin.php?page=automatorwp_settings&tab=zoho');

        echo '<div class="automatorwp-notice-warning" style="margin-top:10px;margin-bottom:0;">'
            . sprintf(__('You need to configure the <a href="%s" target="_blank">Zoho settings</a> to get this action to work.', 'automatorwp-zoho'), $url)
            . '</div>';

        return $object;
    }

    /**
     * Action custom log meta
     * * @since 1.0.0
     */
    public function log_meta($log_meta, $action, $user_id, $action_options, $automation)
    {
        if (isset($action->type) && $action->type === $this->action) {
            $log_meta['result'] = $this->result;
        }
        return $log_meta;
    }

    /**
     * Action custom log fields
     * * @since 1.0.0
     */
    public function log_fields($log_fields, $log, $object)
    {
        if (isset($log->type) && $log->type === 'action' && isset($object->type) && $object->type === $this->action) {
            $log_fields['result'] = array(
                'name' => __('Result:', 'automatorwp-zoho'),
                'type' => 'text',
            );
        }
        return $log_fields;
    }
}

new AutomatorWP_Zoho_Create_Contact();
