<?php

/**
 * Move Contact to List
 *
 * @package     AutomatorWP\Integrations\Mailjet\Actions\Move-Contact-To-List
 * @author      AutomatorWP
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AutomatorWP_Mailjet_Move_Contact_To_List extends AutomatorWP_Integration_Action
{

    public $integration = 'mailjet';
    public $action = 'mailjet_move_contact_to_list';

    /**
     * Register the action
     */
    public function register()
    {

        automatorwp_register_action($this->action, array(
            'integration'       => $this->integration,
            'label'             => __('Move a contact to a list', 'automatorwp-mailjet'),
            'select_option'     => __('Move a <strong>contact</strong> to a <strong>list</strong>', 'automatorwp-mailjet'),
            'edit_label'        => sprintf(__('Move %1$s to list %2$s', 'automatorwp-mailjet'), '{contact}', '{list}'),
            'log_label'         => sprintf(__('Moved %1$s to list %2$s', 'automatorwp-mailjet'), '{contact}', '{list}'),
            'options'           => array(
                'contact' => array(
                    'from' => 'contact',
                    'default' => __('contact', 'automatorwp-mailjet'),
                    'fields' => array(
                        'contact' => automatorwp_utilities_ajax_selector_field(array(
                            'name'              => __('Contact:', 'automatorwp-mailjet'),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_mailjet_get_contacts',
                            'options_cb'        => 'automatorwp_mailjet_options_cb_contact',
                            'placeholder'       => 'Select a contact',
                            'default'           => '',
                            'required'          => true
                        )),
                        'list' => automatorwp_utilities_ajax_selector_field(array(
                            'name'              => __('Destination List:', 'automatorwp-mailjet'),
                            'option_none'       => false,
                            'action_cb'         => 'automatorwp_mailjet_get_lists',
                            'options_cb'        => 'automatorwp_mailjet_options_cb_list',
                            'placeholder'       => 'Select a list',
                            'default'           => '',
                            'required'          => true
                        )),
                    ),
                ),
            ),
        ));
    }

    /**
     * Execute the action
     */
    public function execute($action, $user_id, $action_options, $automation)
    {

        $contact_email = $action_options['contact'];
        $list_id = $action_options['list'];

        // Debug logging
        //error_log("Moving contact: {$contact_email} to list: {$list_id}" . PHP_EOL, 3, ABSPATH . "debug.log");

        if (empty($contact_email) || empty($list_id)) {
            $this->result = __('Contact email or list ID is missing', 'automatorwp-mailjet');
            return;
        }

        if (! automatorwp_mailjet_get_api()) {
            $this->result = __('Mailjet integration is not configured in AutomatorWP settings', 'automatorwp-mailjet');
            return;
        }

        // Add contact to the list
        $response = automatorwp_mailjet_add_contact_to_list($contact_email, $list_id);
        //error_log("id list: {$list_id}" . PHP_EOL, 3, "debug5.log");
        //error_log("id list: {$contact_email}" . PHP_EOL, 3, "debug5.log");

        if ($response === 201 || $response === 200) {
            $this->result = __('Contact moved to the list successfully', 'automatorwp-mailjet');
        } else {
            $this->result = __('Failed to move contact to the list', 'automatorwp-mailjet');
        }
    }

    /**
     * Register hooks
     */
    public function hooks()
    {
        add_filter('automatorwp_automation_ui_after_item_label', array($this, 'configuration_notice'), 10, 2);
        add_filter('automatorwp_user_completed_action_log_meta', array($this, 'log_meta'), 10, 5);
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);
        parent::hooks();
    }

    /**
     * Display configuration notice
     */
    public function configuration_notice($object, $item_type)
    {

        if ($item_type !== 'action' || $object->type !== $this->action) {
            return;
        }

        if (! automatorwp_mailjet_get_api()): ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __('You need to configure the <a href="%s" target="_blank">Mailjet settings</a> to get this action to work.', 'automatorwp-mailjet'),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-mailjet'
                ); ?>
                <?php echo sprintf(
                    __('<a href="%s" target="_blank">Documentation</a>', 'automatorwp-mailjet'),
                    'https://automatorwp.com/docs/mailjet/'
                ); ?>
            </div>
<?php endif;
    }

    /**
     * Add result to action log meta
     */
    public function log_meta($log_meta, $action, $user_id, $action_options, $automation)
    {

        if ($action->type !== $this->action) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    /**
     * Add result field to log display
     */
    public function log_fields($log_fields, $log, $object)
    {

        if ($log->type !== 'action' || $object->type !== $this->action) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __('Result:', 'automatorwp-mailjet'),
            'type' => 'text',
        );

        return $log_fields;
    }
}

new AutomatorWP_Mailjet_Move_Contact_To_List();
