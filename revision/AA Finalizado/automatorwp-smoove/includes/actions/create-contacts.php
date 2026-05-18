<?php
/**
 * Create Contacts
 *
 * @package     AutomatorWP\Integrations\Smoove\Actions\Create-Contacts
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AutomatorWP_Smoove_Create_Contacts extends AutomatorWP_Integration_Action
{
    public $integration = 'smoove';
    public $action = 'smoove_create_contacts';

    /**
     * Register the action
     */
    public function register()
    {
        automatorwp_register_action($this->action, array(
            'integration' => $this->integration,
            'label' => __('Create or update a contact', 'automatorwp-smoove'),
            'select_option' => __('Create or update a <strong>contact</strong>', 'automatorwp-smoove'),
            'edit_label' => sprintf(__('Create or update a %1$s', 'automatorwp-smoove'), '{contact}'),
            'log_label' => sprintf(__('Created or updated a %1$s', 'automatorwp-smoove'), '{contact}'),
            'options' => array(
                'contact' => array(
                    'from' => 'contact',
                    'default' => __('contact', 'automatorwp-smoove'),
                    'fields' => array(
                        'email' => array(
                            'name' => __('Email:', 'automatorwp-smoove'),
                            'type' => 'text',
                            'default' => '',
                            'required' => true
                        ),
                        'first_name' => array(
                            'name' => __('First Name:', 'automatorwp-smoove'),
                            'type' => 'text',
                            'default' => '',
                            'required' => false
                        ),
                        'last_name' => array(
                            'name' => __('Last Name:', 'automatorwp-smoove'),
                            'type' => 'text',
                            'default' => '',
                            'required' => false
                        ),
                        'mobile_phone' => array(
                            'name' => __('Mobile Phone:', 'automatorwp-smoove'),
                            'type' => 'text',
                            'default' => '',
                            'required' => false
                        ),
                        'landline_phone' => array(
                            'name' => __('Landline Phone:', 'automatorwp-smoove'),
                            'type' => 'text',
                            'default' => '',
                            'required' => false
                        ),
                        'company' => array(
                            'name' => __('Company:', 'automatorwp-smoove'),
                            'type' => 'text',
                            'default' => '',
                            'required' => false
                        ),
                        'position' => array(
                            'name' => __('Position:', 'automatorwp-smoove'),
                            'type' => 'text',
                            'default' => '',
                            'required' => false
                        ),
                    ),
                ),
            ),
        ));
    }

    /**
     * Action execution function
     */
    public function execute($action, $user_id, $action_options, $automation)
    {
        // Bail if email is empty
        if (empty($action_options['email'])) {
            $this->result = __('Contact email is required but empty', 'automatorwp-smoove');
            return;
        }

        // Estructura de payload basada en los campos base aceptados por la API de Smoove 
        $contact_data = array(
            'email' => sanitize_email($action_options['email']),
            'firstName' => sanitize_text_field($action_options['first_name']),
            'lastName' => sanitize_text_field($action_options['last_name']),
            'cellPhone' => sanitize_text_field($action_options['mobile_phone']),
            'phone' => sanitize_text_field($action_options['landline_phone']),
            'company' => sanitize_text_field($action_options['company']),
            'position' => sanitize_text_field($action_options['position']),
        );

        // Remove empty fields to avoid overwriting existing data with nulls
        $filtered_data = array();
        foreach ($contact_data as $key => $value) {
            if ($value !== '' && $value !== null) {
                $filtered_data[$key] = $value;
            }
        }

        // Bail if Smoove not configured
        if (!automatorwp_smoove_get_option('api_key')) {
            $this->result = __('Smoove integration is not configured', 'automatorwp-smoove');
            return;
        }

        // Call API
        $response = automatorwp_smoove_create_contact($filtered_data);

        if (is_wp_error($response)) {
            $this->result = sprintf(__('Connection error: %s', 'automatorwp-smoove'), $response->get_error_message());
            return;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        // Check API HTTP response
        if ($response_code === 200 || $response_code === 201 || $response_code === 202) {
            $this->result = __('Contact created successfully in Smoove', 'automatorwp-smoove');
        } else {
            $error_msg = $body;
            $json_body = json_decode($body, true);
            if (is_array($json_body) && isset($json_body['message'])) {
                $error_msg = $json_body['message'];
            }
            
            $this->result = sprintf(__('Failed to create contact. HTTP Code: %s. Smoove API says: %s', 'automatorwp-smoove'), $response_code, sanitize_text_field($error_msg));
        }
    }

    /**
     * Register required hooks
     */
    public function hooks()
    {
        add_filter('automatorwp_automation_ui_after_item_label', array($this, 'configuration_notice'), 10, 2);
        add_filter('automatorwp_user_completed_action_log_meta', array($this, 'log_meta'), 10, 5);
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);
        parent::hooks();
    }

    /**
     * Configuration notice
     */
    public function configuration_notice($object, $item_type)
    {
        if ($item_type !== 'action' || $object->type !== $this->action) return;

        if (!automatorwp_smoove_get_api()): ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __('You need to configure the <a href="%s" target="_blank">Smoove settings</a> to get this action to work.', 'automatorwp-smoove'),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-smoove'
                ); ?>
            </div>
        <?php endif;
    }

    /**
     * Action custom log meta
     */
    public function log_meta($log_meta, $action, $user_id, $action_options, $automation)
    {
        if ($action->type !== $this->action) return $log_meta;
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    /**
     * Action custom log fields
     */
    public function log_fields($log_fields, $log, $object)
    {
        if ($log->type !== 'action' || $object->type !== $this->action) return $log_fields;

        $log_fields['result'] = array(
            'name' => __('Result:', 'automatorwp-smoove'),
            'type' => 'text',
        );
        return $log_fields;
    }
}
new AutomatorWP_Smoove_Create_Contacts();