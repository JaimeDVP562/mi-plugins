<?php
/**
 * Add contact to a list
 *
 * @package     AutomatorWP\Integrations\Brevo\Actions\Add contact to a list
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_Brevo_Add_Contact_To_List extends AutomatorWP_Integration_Action
{

    public $integration = 'brevo';
    public $action = 'brevo_add_contact_to_list';

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
                'label' => __('Add contact to list', 'automatorwp-brevo'),
                'select_option' => __('Add <strong>contact</strong> to <strong>list</strong>', 'automatorwp-brevo'),
                /* translators: %1$s: Contact. */
                'edit_label' => sprintf(__('Add %1$s to a list', 'automatorwp-brevo'), '{contact}'),
                /* translators: %1$s: Contact. */
                'log_label' => sprintf(__('Add %1$s to a list', 'automatorwp-brevo'), '{contact}'),
                'options' => array(
                    'contact' => array(
                        'from' => 'contact',
                        'default' => __('contact', 'automatorwp-brevo'),
                        'fields' => array(
                            'folder' => automatorwp_utilities_ajax_selector_field(
                                array(
                                    'name' => __('Folder:', 'automatorwp-brevo'),
                                    'option_none' => false,
                                    'required' => true,
                                    'action_cb' => 'automatorwp_brevo_get_folders',
                                    'options_cb' => 'automatorwp_brevo_options_cb_folder',
                                    'placeholder' => 'Select a folder',
                                    'default' => ''
                                )
                            ),
                            'list' => automatorwp_utilities_ajax_selector_field(
                                array(
                                    'name' => __('List:', 'automatorwp-brevo'),
                                    'option_none' => false,
                                    'required' => true,
                                    'action_cb' => 'automatorwp_brevo_get_lists',
                                    'options_cb' => 'automatorwp_brevo_options_cb_list',
                                    'placeholder' => 'Select a list',
                                    'default' => ''
                                )
                            ),
                            'email' => array(
                                'name' => __('Email:', 'automatorwp-brevo'),
                                'desc' => __('The contact email.', 'automatorwp-brevo'),
                                'type' => 'text',
                                'required' => true,
                                'default' => ''
                            )
                        ),
                    ),
                )
            ),
        );

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

        // The contact must exist in order to be added to a list
        $list_id = $action_options['list'];
        $email = $action_options['email'];

        // Bail if user email is empty
        if (empty($email)) {
            $this->result = __('Email field is empty.', 'automatorwp-brevo');
            return;
        }

        $this->result = '';

        // Bail if brevo not configured
        if (!automatorwp_brevo_get_api()) {
            $this->result = __('Brevo integration not configured in AutomatorWP settings.', 'automatorwp-brevo');
            return;
        }

        $response = automatorwp_brevo_add_contact_to_list($email, $list_id);

        if ($response === 201) {
            $this->result = sprintf(__('%s contact added to list', 'automatorwp-brevo'), $email);
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

new AutomatorWP_Brevo_Add_Contact_To_List();