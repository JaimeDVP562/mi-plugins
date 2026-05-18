<?php
/**
 * Create contact
 *
 * @package     AutomatorWP\Integrations\Brevo\Actions\Create contact
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_Brevo_Create_Contact extends AutomatorWP_Integration_Action
{

    public $integration = 'brevo';
    public $action = 'brevo_create_contact';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register()
    {

        automatorwp_register_action(
            $this->action,
            array(
                'integration' => $this->integration,
                'label' => __('Create contact', 'automatorwp-brevo'),
                'select_option' => __('Create <strong>contact</strong>', 'automatorwp-brevo'),
                /* translators: %1$s: Contact. */
                'edit_label' => sprintf(__('Create %1$s', 'automatorwp-brevo'), '{contact}'),
                /* translators: %1$s: Contact. */
                'log_label' => sprintf(__('Create %1$s', 'automatorwp-brevo'), '{contact}'),
                'options' => array(
                    'contact' => array(
                        'from' => 'contact',
                        'default' => __('contact', 'automatorwp-brevo'),
                        'fields' => array(
                            'email' => array(
                                'name' => __('Email:', 'automatorwp-brevo'),
                                'desc' => __('The contact email.', 'automatorwp-brevo'),
                                'type' => 'text',
                                'required' => true,
                                'default' => ''
                            ),
                            'first_name' => array(
                                'name' => __('First Name:', 'automatorwp-brevo'),
                                'desc' => __('The contact first name.', 'automatorwp-brevo'),
                                'type' => 'text',
                                'default' => ''
                            ),
                            'last_name' => array(
                                'name' => __('Last Name:', 'automatorwp-brevo'),
                                'desc' => __('The contact last name.', 'automatorwp-brevo'),
                                'type' => 'text',
                                'default' => ''
                            ),
                            'sms' => array(
                                'name' => __('SMS:', 'automatorwp-brevo'),
                                'desc' => __('The contact sms number.', 'automatorwp-brevo'),
                                'type' => 'text',
                                'default' => ''
                            ),
                            
                        ),
                    )
                )
            ),
        );

    }

    /**
     * Get Brevo attribute names from API
     *
     * @since 1.0.0
     * @return array
     */
    private function get_brevo_attribute_names() {
        if (!function_exists('automatorwp_brevo_get_api') || !automatorwp_brevo_get_api()) {
            return array();
        }

        $api = automatorwp_brevo_get_api();
        $url = $api['url'] . '/contacts/attributes';
        $args = array(
            'headers' => array(
                'Accept'  => 'application/json',
                'api-key' => $api['token'],
            ),
            'timeout' => 15,
        );

        $res = wp_remote_get($url, $args);
        if (is_wp_error($res)) {
            return array();
        }

        $body = wp_remote_retrieve_body($res);
        $data = json_decode($body, true);
        if (empty($data['attributes']) || !is_array($data['attributes'])) {
            return array();
        }

        $names = array();
        foreach ($data['attributes'] as $attr) {
            if (!empty($attr['name'])) {
                $names[] = $attr['name'];
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Resolve an attribute from aliases against available attribute names
     *
     * @since 1.0.0
     * @param array $aliases
     * @param array $available
     * @return string|false
     */
    private function brevo_resolve_attribute($aliases, $available) {
        $available_up = array_map('strtoupper', $available);
        foreach ($aliases as $alias) {
            $a = strtoupper($alias);
            $idx = array_search($a, $available_up, true);
            if ($idx !== false) {
                return $available[$idx];
            }
        }
        return false;
    }


    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute($action, $user_id, $action_options, $automation)
    {

        // Resolve dynamic attribute names from Brevo
        $available = $this->get_brevo_attribute_names();

        $firstname_key = $this->brevo_resolve_attribute(array('FIRSTNAME','NOMBRE','PRENOM'), $available);
        $lastname_key  = $this->brevo_resolve_attribute(array('LASTNAME','APELLIDOS','NOM'), $available);
        $sms_key       = $this->brevo_resolve_attribute(array('SMS','PHONE_SMS'), $available);

        $attributes = array();
        if ($firstname_key && !empty($action_options['first_name'])) {
            $attributes[$firstname_key] = $action_options['first_name'];
        }
        if ($lastname_key && !empty($action_options['last_name'])) {
            $attributes[$lastname_key] = $action_options['last_name'];
        }
        if ($sms_key && !empty($action_options['sms'])) {
            $attributes[$sms_key] = $action_options['sms'];
        }

        $contact_data = array(
            'email' => isset($action_options['email']) ? $action_options['email'] : '',
            'attributes' => $attributes,
        );

        $this->result = '';

        // Bail if user email is empty
        if (empty($contact_data['email'])) {
            $this->result = __('Email subscriber field is empty.', 'automatorwp-brevo');
            return;
        }

        $this->result = '';

        // Bail if brevo not configured
        if (!automatorwp_brevo_get_api()) {
            $this->result = __('Brevo integration not configured in AutomatorWP settings.', 'automatorwp-brevo');
            return;
        }

        $response = automatorwp_brevo_create_contact($contact_data);

        if ($response === 201) {
            $this->result = sprintf(__('%s created in brevo', 'automatorwp-brevo'), $contact_data['email']);
        } else if ($response === 204) {
            $this->result = sprintf(__('%s updated in brevo', 'automatorwp-brevo'), $contact_data['email']);
        } else {
            $this->result = __('The contact could not be created', 'automatorwp-brevo');
        }
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks()
    {

        // Configuration notice
        add_filter('automatorwp_automation_ui_after_item_label', array($this, 'configuration_notice'), 10, 2);

        // Log meta data
        add_filter('automatorwp_user_completed_action_log_meta', array($this, 'log_meta'), 10, 5);

        // Log fields
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);

        parent::hooks();

    }

    /**
     * Configuration notice
     *
     * @since 1.0.0
     *
     * @param stdClass  $object     The trigger/action object
     * @param string    $item_type  The object type (trigger|action)
     */
    public function configuration_notice($object, $item_type)
    {

        // Bail if action type don't match this action
        if ($item_type !== 'action') {
            return;
        }

        if ($object->type !== $this->action) {
            return;
        }

        // Warn user if the authorization has not been setup from settings
        if (!automatorwp_brevo_get_api()): ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __('You need to configure the <a href="%s" target="_blank">brevo settings</a> to get this action to work.', 'automatorwp-brevo'),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-brevo'
                ); ?>
                <?php echo sprintf(
                    __('<a href="%s" target="_blank">Documentation</a>', 'automatorwp-brevo'),
                    'https://automatorwp.com/docs/brevo/'
                ); ?>
            </div>
        <?php endif;

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
    public function log_meta($log_meta, $action, $user_id, $action_options, $automation)
    {

        // Bail if action type don't match this action
        if ($action->type !== $this->action) {
            return $log_meta;
        }

        // Store the action's result
        $log_meta['result'] = $this->result;

        return $log_meta;
    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields($log_fields, $log, $object)
    {

        // Bail if log is not assigned to an action
        if ($log->type !== 'action') {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if ($object->type !== $this->action) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __('Result:', 'automatorwp-brevo'),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_Brevo_Create_Contact();