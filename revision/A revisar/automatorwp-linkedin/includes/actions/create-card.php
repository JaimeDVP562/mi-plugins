<?php
/**
 * Create Card
 *
 * @package     AutomatorWP\Integrations\Linkedin\Actions\Create-Card
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_Linkedin_Create_Card extends AutomatorWP_Integration_Action
{

    public $integration = 'linkedin';
    public $action = 'linkedin_create_card';

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
                'label' => __('Create a new card', 'automatorwp-linkedin'),
                'select_option' => __('Create a new <strong>card</strong>', 'automatorwp-linkedin'),
                /* translators: %1$s: Card. */
                'edit_label' => sprintf(__('Create a new %1$s in %2$s', 'automatorwp-linkedin'), '{card}', '{board}'),
                /* translators: %1$s: Card. */
                'log_label' => sprintf(__('Create a new %1$s in %2$s', 'automatorwp-linkedin'), '{card}', '{board}'),
                'options' => array(
                    'card' => array(
                        'from' => 'card',
                        'default' => __('card', 'automatorwp-linkedin'),
                        'fields' => array(
                            'card_title' => array(
                                'name' => __('Card title:', 'automatorwp-linkedin'),
                                'type' => 'text',
                                'default' => '',
                                'required' => true
                            ),
                            'card_desc' => array(
                                'name' => __('Card description: ', 'automatorwp-linkedin'),
                                'type' => 'textarea',
                                'required' => false,
                                'attributes' => array(
                                    'rows' => '5'
                                ),
                            ),
                        ),
                    ),

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
    public function execute($action, $user_id, $action_options, $automation)
    {

        // Shorthand
        $card_name = $action_options['card_title'];
        $card_desc = $action_options['card_desc'];

        // Bail if list_id is empty
        if (empty($list_id)) {
            return;
        }

        // Bail if Linkedin not configured
        if (!automatorwp_linkedin_get_api()) {
            $this->result = __('Linkedin integration is not configured in AutomatorWP settings', 'automatorwp-linkedin');
            return;
        }

        $response = automatorwp_linkedin_create_card($card_name, $list_id, $card_desc);

        if ($response === 200) {
            $this->result = __('Created card in list', 'automatorwp-linkedin');
        } else {
            $this->result = __('The card could not be created', 'automatorwp-linkedin');
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
        if (!automatorwp_linkedin_get_api()): ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __('You need to configure the <a href="%s" target="_blank">Linkedin settings</a> to get this action to work.', 'automatorwp-linkedin'),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-linkedin'
                ); ?>
                <?php echo sprintf(
                    __('<a href="%s" target="_blank">Documentation</a>', 'automatorwp-linkedin'),
                    'https://automatorwp.com/docs/linkedin/'
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
            'name' => __('Result:', 'automatorwp-linkedin'),
            'type' => 'text',
        );

        return $log_fields;
    }
}
new AutomatorWP_Linkedin_Create_Card();