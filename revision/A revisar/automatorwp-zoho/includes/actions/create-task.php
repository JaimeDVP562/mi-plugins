<?php

/**
 * Zoho Action: Create a task
 *
 * @package     AutomatorWP\Integrations\Zoho\Actions\Create_Task
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AutomatorWP_Zoho_Create_Task extends AutomatorWP_Integration_Action
{
    public $integration = 'zoho';
    public $action      = 'zoho_create_task';
    public $result      = '';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_action($this->action, array(
            'integration'   => $this->integration,
            'label'         => __('Create a task (Zoho)', 'automatorwp-zoho'),
            'select_option' => __('Create a <strong>task</strong> in <strong>Zoho CRM</strong>', 'automatorwp-zoho'),
            'edit_label'    => __('Create a task in Zoho CRM', 'automatorwp-zoho'),
            'log_label'     => __('Create a task in Zoho CRM', 'automatorwp-zoho'),
            'options'       => array(
                'task_details' => array(
                    'from'   => 'task_details',
                    'fields' => array(
                        'subject' => array(
                            'name'        => __('Subject:', 'automatorwp-zoho'),
                            'type'        => 'text',
                            'placeholder' => __('e.g. Follow up with user', 'automatorwp-zoho'),
                            'required'    => true,
                        ),
                        'due_date' => array(
                            'name'    => __('Due Date (YYYY-MM-DD):', 'automatorwp-zoho'),
                            'type'    => 'text',
                            'default' => date('Y-m-d', strtotime('+1 day')),
                            'required'    => true,
                        ),
                        'status' => array(
                            'name'    => __('Status:', 'automatorwp-zoho'),
                            'type'    => 'select',
                            'options' => array(
                                'Not Started' => __('Not Started', 'automatorwp-zoho'),
                                'In Progress' => __('In Progress', 'automatorwp-zoho'),
                                'Completed'   => __('Completed', 'automatorwp-zoho'),
                            ),
                            'default' => 'Not Started',
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
        $subject  = sanitize_text_field($action_options['subject'] ?? '');
        $due_date = sanitize_text_field($action_options['due_date'] ?? '');
        $status   = sanitize_text_field($action_options['status'] ?? 'Not Started');

        if (empty($subject)) {
            $this->result = __('Missing required Subject field.', 'automatorwp-zoho');
            return;
        }

        // Data structure for Zoho CRM API (Tasks Module)
        $body = array(
            'data' => array(
                array(
                    'Subject'   => $subject,
                    'Due_Date'  => $due_date,
                    'Status'    => $status,
                    'Priority'  => 'Normal',
                )
            )
        );

        if (!function_exists('automatorwp_zoho_api_request')) {
            $this->result = __('Zoho helper function is missing.', 'automatorwp-zoho');
            return;
        }

        $response = automatorwp_zoho_api_request('Tasks', $body, 'POST');

        if (is_wp_error($response)) {
            $this->result = $response->get_error_message();
            return;
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code >= 200 && $code < 300) {
            $this->result = __('Task created successfully in Zoho CRM.', 'automatorwp-zoho');
        } else {
            $this->result = sprintf(__('Zoho error (HTTP %d)', 'automatorwp-zoho'), $code);
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
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
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

new AutomatorWP_Zoho_Create_Task();
