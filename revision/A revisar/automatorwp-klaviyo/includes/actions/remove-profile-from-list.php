<?php
/**
 * Remove profile from Klaviyo list
 *
 * @package     AutomatorWP\Integrations\Klaviyo\Actions\Remove_Profile_From_List
 * @author      AutomatorWP <contact@automatorwp.com>, Irene Rodenas <irener.rglez@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AutomatorWP_Klaviyo_Remove_Profile_From_List extends AutomatorWP_Integration_Action {
    public $integration = 'klaviyo';
    public $action = 'klaviyo_remove_profile_from_list';
    public $result;

    public function register() {
        automatorwp_register_action(
            $this->action,
            array(
                'integration' => $this->integration,
                'label' => __('Remove profile from list', 'automatorwp-klaviyo'),
                'select_option' => __('Remove <strong>profile from list</strong>', 'automatorwp-klaviyo'),
                'edit_label' => sprintf(__('Remove %1$s from list', 'automatorwp-klaviyo'), '{profile}'),
                'log_label' => sprintf(__('Remove %1$s from list', 'automatorwp-klaviyo'), '{profile}'),
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
                                'desc' => __('The ID of the list to remove the profile from.', 'automatorwp-klaviyo'),
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

    public function execute($action, $user_id, $action_options, $automation) {
        // Get the profile email and list ID from action options
        $email = $action_options['email'];
        $list_id = $action_options['list_id'];
    
        // Bail if profile email or list ID is empty
        if (empty($email) || empty($list_id)) {
            $this->result = __('Email or List ID field is empty.', 'automatorwp-klaviyo');
            error_log('AutomatorWP Klaviyo: Email or List ID field is empty.');
            return;
        }
    
        // Bail if Klaviyo integration is not configured
        $api = automatorwp_klaviyo_get_api();
        if (!$api) {
            $this->result = __('Klaviyo integration not configured in AutomatorWP settings.', 'automatorwp-klaviyo');
            return;
        }
    
        // Call the function to remove the profile from the list
        $response = remove_profile_from_klaviyo_list($email, $list_id, $api['secret']);
    
        // Handle the API response
        if (is_wp_error($response)) {
            // If there's an error in the response, log it
            $this->result = "cURL Error #:" . $response->get_error_message();
        } else {
            // Log the full API response for debugging
    
            // If the response contains data, confirm the profile was removed
            if (isset($response['data'])) {
                $this->result = sprintf(__('Profile %s removed from list %s in Klaviyo', 'automatorwp-klaviyo'), $email, $list_id);
            } else {
                // If the profile could not be removed, log it
                $this->result = __('The profile could not be removed from the list in Klaviyo', 'automatorwp-klaviyo');
            }
        }
    }    

    public function hooks() {
        add_filter('automatorwp_automation_ui_after_item_label', array($this, 'configuration_notice'), 10, 2);
        add_filter('automatorwp_user_completed_action_log_meta', array($this, 'log_meta'), 10, 5);
        add_filter('automatorwp_log_fields', array($this, 'log_fields'), 10, 5);
        parent::hooks();
    }

    public function configuration_notice($object, $item_type) {
        if ($item_type !== 'action' || $object->type !== $this->action) {
            return;
        }

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

    public function log_meta($log_meta, $action, $user_id, $action_options, $automation) {
        if ($action->type !== $this->action) {
            return $log_meta;
        }
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    public function log_fields($log_fields, $log, $object) {
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

new AutomatorWP_Klaviyo_Remove_Profile_From_List();