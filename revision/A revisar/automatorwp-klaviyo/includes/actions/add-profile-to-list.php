<?php
/**
 * Add profile to Klaviyo list
 *
 * @package     AutomatorWP\Integrations\Klaviyo\Actions\Add_Profile_To_List
  * @author      AutomatorWP <contact@automatorwp.com>, Irene Rodenas <irener.rglez@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AutomatorWP_Klaviyo_Add_Profile_To_List extends AutomatorWP_Integration_Action {
    public $integration = 'klaviyo';
    public $action = 'klaviyo_add_profile_to_list';
    public $result;

    public function register() {
        automatorwp_register_action(
            $this->action,
            array(
                'integration' => $this->integration,
                'label' => __('Add profile to list', 'automatorwp-klaviyo'),
                'select_option' => __('Add <strong>profile to list</strong>', 'automatorwp-klaviyo'),
                'edit_label' => sprintf(__('Add %1$s to list', 'automatorwp-klaviyo'), '{profile}'),
                'log_label' => sprintf(__('Add %1$s to list', 'automatorwp-klaviyo'), '{profile}'),
                'options' => array(
                    'profile' => array(
                        'from' => 'profile',
                        'default' => __('profile', 'automatorwp-klaviyo'),
                        'fields' => array(
                            'email' => array(
                                'name' => __('Email:', 'automatorwp-klaviyo'),
                                'desc' => __('The profile email.', 'automatorwp-klaviyo'),
                                'type' => 'text',
                                'required' => true,
                                'default' => ''
                            ),
                            'list_id' => array(
                                'name' => __('List ID:', 'automatorwp-klaviyo'),
                                'desc' => __('The ID of the list to add the profile to.', 'automatorwp-klaviyo'),
                                'type' => 'text',
                                'required' => true,
                                'default' => ''
                            )
                        ),
                    )
                )
            )
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
    public function execute($action, $user_id, $action_options, $automation) {
        // Get the profile email and list ID from action options
        $email = $action_options['email'];
        $list_id = $action_options['list_id'];
    
        // Bail if profile email or list ID is empty
        if (empty($email) || empty($list_id)) {
            $this->result = __('Email or List ID field is empty.', 'automatorwp-klaviyo');
            return;
        }
    
        // Bail if Klaviyo integration is not configured
        $api = automatorwp_klaviyo_get_api();
        if (!$api) {
            $this->result = __('Klaviyo integration not configured in AutomatorWP settings.', 'automatorwp-klaviyo');
            return;
        }
    
        // Call the function to add the profile to the list
        $response = automatorwp_klaviyo_add_profile_to_list($email, $list_id, $api['secret']);
    
        // Handle the API response
        if (isset($response['error'])) {
            // If there's an error in the response, log it
            $this->result = "cURL Error #:" . $response['error'];
        } else {
            // If the response contains data, confirm the profile was added
            if (isset($response['data'])) {
                $this->result = sprintf(__('Profile %s added to list %s in Klaviyo', 'automatorwp-klaviyo'), $email, $list_id);
            } else {
                // If the profile could not be added, log it
                $this->result = __('The profile could not be added to the list in Klaviyo', 'automatorwp-klaviyo');
            }
        }
    }   
    
    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {
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
    public function configuration_notice($object, $item_type) {
        // Bail if action type doesn't match this action
        if ($item_type !== 'action' || $object->type !== $this->action) {
            return;
        }

        // Warn user if the authorization has not been set up from settings
        if (!automatorwp_klaviyo_get_api()) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __('You need to configure the <a href="%s" target="_blank">Klaviyo settings</a> to get this action to work.', 'automatorwp-klaviyo'),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-klaviyo'
                ); ?>
                <?php echo sprintf(
                    __(' <a href="%s" target="_blank">Documentation</a>', 'automatorwp-klaviyo'),
                    'https://automatorwp.com/docs/klaviyo/'
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
    public function log_meta($log_meta, $action, $user_id, $action_options, $automation) {
        // Bail if action type doesn't match this action
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
    public function log_fields($log_fields, $log, $object) {
        // Bail if log is not assigned to an action
        if ($log->type !== 'action' || $object->type !== $this->action) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __('Result:', 'automatorwp-klaviyo'),
            'type' => 'text',
        );

        return $log_fields;
    }
}

new AutomatorWP_Klaviyo_Add_Profile_To_List();
