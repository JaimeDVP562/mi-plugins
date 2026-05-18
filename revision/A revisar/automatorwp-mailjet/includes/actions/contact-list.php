<?php

/**
 * Create Contact List
 *
 * @package     AutomatorWP\Integrations\Mailjet\Actions\Contact_List
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AutomatorWP_Mailjet_Contact_List extends AutomatorWP_Integration_Action
{

    public $integration = 'mailjet';
    public $action = 'mailjet_create_contact_list';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register()
    {

        automatorwp_register_action($this->action, array(
            'integration'       => $this->integration,
            'label'             => __('Create a new contact list', 'automatorwp-mailjet'),
            'select_option'     => __('Create a new <strong>contact list</strong>', 'automatorwp-mailjet'),
            /* translators: %1$s: List name. */
            'edit_label'        => sprintf(__('Create a new list: %1$s', 'automatorwp-mailjet'), '{list_name}'),
            /* translators: %1$s: List name. */
            'log_label'         => sprintf(__('Create a new list: %1$s', 'automatorwp-mailjet'), '{list_name}'),
            'options'           => array(
                'list_name' => array(
                    'from' => 'list_name',
                    'default' => __('contact list', 'automatorwp-mailjet'),
                    'fields' => array(
                        'name' => array(
                            'name'          => __('List name:', 'automatorwp-mailjet'),
                            'type'          => 'text',
                            'default'       => '',
                            'placeholder'   => __('My Contact List', 'automatorwp-mailjet'),
                            'required'      => true
                        ),
                    ),
                ),
            ),
        ));
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
        // Shorthand
        $name = $action_options['name'];

        // Bail if list name is empty
        if (empty($name)) {
            return;
        }

        // Bail if Mailjet not configured
        if (! automatorwp_mailjet_get_api()) {
            $this->result = __('Mailjet integration is not configured in AutomatorWP settings', 'automatorwp-mailjet');
            return;
        }

        $response = automatorwp_mailjet_create_contact_list($name);

        if ($response === 200) {
            $this->result = __('Created contact list', 'automatorwp-mailjet');
        } else {
            $this->result = __('The contact list could not be created', 'automatorwp-mailjet');
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
        if (! automatorwp_mailjet_get_api()) : ?>
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
            'name' => __('Result:', 'automatorwp-mailjet'),
            'type' => 'text',
        );

        return $log_fields;
    }
}
new AutomatorWP_Mailjet_Contact_List();
