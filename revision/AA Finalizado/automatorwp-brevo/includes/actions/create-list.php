<?php
/**
 * Create list
 *
 * @package     AutomatorWP\Integrations\Brevo\Actions\Create list
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_Brevo_Create_List extends AutomatorWP_Integration_Action
{

    public $integration = 'brevo';
    public $action = 'brevo_create_list';

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
                'label' => __('Create list', 'automatorwp-brevo'),
                'select_option' => __('Create <strong>list</strong>', 'automatorwp-brevo'),
                /* translators: %1$s: Contact. */
                'edit_label' => sprintf(__('Create %1$s', 'automatorwp-brevo'), '{list}'),
                /* translators: %1$s: Contact. */
                'log_label' => sprintf(__('Create %1$s', 'automatorwp-brevo'), '{list}'),
                'options' => array(
                    'list' => array(
                        'from' => 'list',
                        'default' => __('list', 'automatorwp-brevo'),
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
                            'name' => array(
                                'name' => __('list name:', 'automatorwp-brevo'),
                                'desc' => __('The list name.', 'automatorwp-brevo'),
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

        // if there are no options in $action_options, try using $action->options (fallback)
        if (empty((array)$action_options) && !empty($action->options)) {
            // Use fallback options from the action object when necessary.
            $action_options = (array) $action->options;
        }

        // Use only the explicit keys we expect from the AutomatorWP UI
        // (this integration expects `folder` and `name` in the saved options).
        $folder_id = isset($action_options['folder']) ? $action_options['folder'] : null;
        $list_name = isset($action_options['name']) ? $action_options['name'] : '';

        // Normalize
        $list_name = is_string($list_name) ? trim($list_name) : '';

        // Bail if list name is empty
        if (empty($list_name)) {
            $this->result = __('List name field is empty.', 'automatorwp-brevo');
            return;
        }

        $this->result = '';

        // Bail if brevo not configured
        if (!automatorwp_brevo_get_api()) {
            $this->result = __('Brevo integration not configured in AutomatorWP settings.', 'automatorwp-brevo');
            return;
        }

        $response = automatorwp_brevo_create_list($list_name, $folder_id);

        if ($response === 201) {
            $this->result = sprintf(__('List %s created', 'automatorwp-brevo'), $list_name);
        } else {
            $this->result = __('The list could not be created', 'automatorwp-brevo');
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

new AutomatorWP_Brevo_Create_List();