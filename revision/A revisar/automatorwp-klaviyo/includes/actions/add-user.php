<?php
/**
 * Add a user to Klaviyo from WordPress
 *
 * @package     AutomatorWP\Integrations\Klaviyo\Actions\Add_User
 * @author      AutomatorWP <contact@automatorwp.com>, Irene Rodenas <irener.rglez@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AutomatorWP_Klaviyo_Add_User extends AutomatorWP_Integration_Action {
    public $integration = 'klaviyo';
    public $action = 'klaviyo_add_user';
    public $result;

    public function register() {
        automatorwp_register_action(
            $this->action,
            array(
                'integration' => $this->integration,
                'label' => __('Add User to Klaviyo', 'automatorwp-klaviyo'),
                'select_option' => __('Add <strong>user</strong> to Klaviyo', 'automatorwp-klaviyo'),
                'edit_label' => sprintf(__('Add %1$s to Klaviyo', 'automatorwp-klaviyo'), '{user}'),
                'log_label' => sprintf(__('Add %1$s to Klaviyo', 'automatorwp-klaviyo'), '{user}'),
                'options' => array(
                    'user' => array(
                        'from' => 'user',
                        'default' => __('user', 'automatorwp-klaviyo'),
                        'fields' => array(
                            'email' => array(
                                'name' => __('Email:', 'automatorwp-klaviyo'),
                                'desc' => __('The user email.', 'automatorwp-klaviyo'),
                                'type' => 'text',
                                'required' => true,
                                'default' => ''
                            ),
                            'first_name' => array(
                                'name' => __('First Name:', 'automatorwp-klaviyo'),
                                'desc' => __('The user first name.', 'automatorwp-klaviyo'),
                                'type' => 'text',
                                'default' => ''
                            ),
                            'last_name' => array(
                                'name' => __('Last Name:', 'automatorwp-klaviyo'),
                                'desc' => __('The user last name.', 'automatorwp-klaviyo'),
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
        $user_email = sanitize_email($action_options['email']);
        $user_first_name = sanitize_text_field($action_options['first_name']);
        $user_last_name = sanitize_text_field($action_options['last_name']);

        $profile_data = array(
            'email' => $user_email,
            'first_name' => $user_first_name,
            'last_name' => $user_last_name
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

            if ($response !== false) {
                if (isset($response['code'])) {
                    if ($response['code'] === 201) {
                        $this->result = sprintf(__('User %s added to Klaviyo', 'automatorwp-klaviyo'), $profile_data['email']);
                    } elseif ($response['code'] === 204) {
                        $this->result = sprintf(__('User %s updated in Klaviyo', 'automatorwp-klaviyo'), $profile_data['email']);
                    } else {
                        $this->result = __('The user could not be added to Klaviyo', 'automatorwp-klaviyo');
                    }
                } else {
                    $this->result = __('Invalid response from Klaviyo API', 'automatorwp-klaviyo');
                }
            } else {
                $this->result = __('Error adding user to Klaviyo', 'automatorwp-klaviyo');
            }
        } catch (Exception $e) {
            $this->result = __('Error communicating with Klaviyo API: ', 'automatorwp-klaviyo') . $e->getMessage();
        }
    }

    public function configuration_notice($object, $item_type) {
        if ($item_type !== 'action' || $object->type !== $this->action) {
            return;
        }

        $notice_message = sprintf(
            __('You need to configure the <a href="%s" target="_blank">Klaviyo settings</a> to get this action to work.', 'automatorwp-klaviyo'),
            get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-klaviyo'
        );

        $documentation_link = sprintf(
            __(' <a href="%s" target="_blank">Documentation</a>', 'automatorwp-klaviyo'),
            'https://automatorwp.com/docs/klaviyo/'
        );

        echo '<div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">';
        echo $notice_message . $documentation_link;
        echo '</div>';
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

new AutomatorWP_Klaviyo_Add_User();
