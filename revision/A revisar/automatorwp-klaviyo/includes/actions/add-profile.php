<?php
/**
 * Add profile to Klaviyo
 *
 * @package     AutomatorWP\Integrations\Klaviyo\Actions\Add_Profile
 * @author      AutomatorWP <contact@automatorwp.com>, Irene Rodenas <irener.rglez@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AutomatorWP_Klaviyo_Add_Profile extends AutomatorWP_Integration_Action {
    public $integration = 'klaviyo';
    public $action = 'klaviyo_add_profile';
    public $result;

    public function register() {
        automatorwp_register_action(
            $this->action,
            array(
                'integration' => $this->integration,
                'label' => __('Add profile', 'automatorwp-klaviyo'),
                'select_option' => __('Add <strong>profile</strong>', 'automatorwp-klaviyo'),
                'edit_label' => sprintf(__('Add %1$s', 'automatorwp-klaviyo'), '{profile}'),
                'log_label' => sprintf(__('Add %1$s', 'automatorwp-klaviyo'), '{profile}'),
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
                            'first_name' => array(
                                'name' => __('First Name:', 'automatorwp-klaviyo'),
                                'desc' => __('The profile first name.', 'automatorwp-klaviyo'),
                                'type' => 'text',
                                'default' => ''
                            ),
                            'last_name' => array(
                                'name' => __('Last Name:', 'automatorwp-klaviyo'),
                                'desc' => __('The profile last name.', 'automatorwp-klaviyo'),
                                'type' => 'text',
                                'default' => ''
                            )
                        ),
                    )
                )
            )
        );
    }

    public function execute($action, $user_id, $action_options, $automation) {
        $profile_data = array(
            'email' => sanitize_email($action_options['email']),
            'first_name' => sanitize_text_field($action_options['first_name']),
            'last_name' => sanitize_text_field($action_options['last_name'])
        );

        // Bail if profile email is empty
        if (empty($profile_data['email'])) {
            $this->result = __('Email field is empty.', 'automatorwp-klaviyo');
            return;
        }

        // Bail if Klaviyo integration is not configured
        $api = automatorwp_klaviyo_get_api();
        if (!$api) {
            $this->result = __('Klaviyo integration not configured in AutomatorWP settings.', 'automatorwp-klaviyo');
            return;
        }

        try {
            if (!function_exists('automatorwp_klaviyo_create_profile')) {
                throw new Exception(__('Function automatorwp_klaviyo_create_profile not defined.', 'automatorwp-klaviyo'));
            }

            $response = automatorwp_klaviyo_create_profile($profile_data, $api['secret']);

            if ($response !== null) {
                if (isset($response['data'])) {
                    $this->result = sprintf(__('Profile %s added in Klaviyo', 'automatorwp-klaviyo'), $profile_data['email']);
                } else {
                    $this->result = __('The profile could not be added to Klaviyo', 'automatorwp-klaviyo');
                }
            } else {
                $this->result = __('Error adding profile to Klaviyo', 'automatorwp-klaviyo');
            }
        } catch (Exception $e) {
            $this->result = __('Error communicating with Klaviyo API: ', 'automatorwp-klaviyo') . $e->getMessage();
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

new AutomatorWP_Klaviyo_Add_Profile();
