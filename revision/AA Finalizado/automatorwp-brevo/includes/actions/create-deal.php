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

class AutomatorWP_Brevo_Create_Deal extends AutomatorWP_Integration_Action
{

    public $integration = 'brevo';
    public $action = 'brevo_create_deal';

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
                'label' => __('Create deal', 'automatorwp-brevo'),
                'select_option' => __('Create <strong>deal</strong>', 'automatorwp-brevo'),
                /* translators: %1$s: Contact. */
                'edit_label' => sprintf(__('Create %1$s', 'automatorwp-brevo'), '{deal}'),
                /* translators: %1$s: Contact. */
                'log_label' => sprintf(__('Create %1$s', 'automatorwp-brevo'), '{deal}'),
                'options' => array(
                    'deal' => array(
                        'from' => 'deal',
                        'default' => __('deal', 'automatorwp-brevo'),
                        'fields' => array(
                            'pipeline' => automatorwp_utilities_ajax_selector_field(
                                array(
                                    'name' => __('Pipeline:', 'automatorwp-brevo'),
                                    'option_none' => false,
                                    'required' => true,
                                    'action_cb' => 'automatorwp_brevo_get_pipelines',
                                    'options_cb' => 'automatorwp_brevo_options_cb_pipeline',
                                    'placeholder' => 'Select a pipeline',
                                    'default' => ''
                                )
                            ),
                            'stage' => automatorwp_utilities_ajax_selector_field(
                                array(
                                    'name' => __('Stage:', 'automatorwp-brevo'),
                                    'option_none' => false,
                                    'required' => true,
                                    'action_cb' => 'automatorwp_brevo_get_stages',
                                    'options_cb' => 'automatorwp_brevo_options_cb_stage',
                                    'placeholder' => 'Select a stage',
                                    'default' => ''
                                )
                            ),
                            'name' => array(
                                'name' => __('Deal name:', 'automatorwp-brevo'),
                                'desc' => __('The deal name.', 'automatorwp-brevo'),
                                'type' => 'text',
                                'required' => true,
                                'default' => ''
                            ),
                            'amount' => array(
                                'name' => __('Amount:', 'automatorwp-brevo'),
                                'desc' => __('The deal amount in €.', 'automatorwp-brevo'),
                                'type' => 'text',
                                'default' => ''
                            ),
                            'close_date' => array(
                                'name' => __('Close date:', 'automatorwp-brevo'),
                                'desc' => __('The close date.', 'automatorwp-brevo'),
                                'type' => 'text_datetime_timestamp',
                                'date_format' => automatorwp_get_date_format(array('Y-m-d', 'm/d/Y')),
                                'time_format' => automatorwp_get_time_format(),
                                'default' => strtotime(date('Y-m-d 00:00:00', current_time('timestamp'))),
                                'attributes' => array(
                                    'whidth' => 200,
                                ),
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
        // Normalize and validate fields before building payload
        // Close date: convert unix timestamp to ISO 8601 if provided
        $close_date = null;
        if ( isset( $action_options['close_date'] ) && $action_options['close_date'] !== '' ) {
            $close_date = date( "Y-m-d\TH:i:s\Z", (int) $action_options['close_date'] );
        }

        // Amount: normalize numeric input (accept numeric string or number)
        $amount = null;
        if ( isset( $action_options['amount'] ) && $action_options['amount'] !== '' ) {
            $raw = $action_options['amount'];
            // Remove common currency symbols and spaces, convert comma to dot
            $normalized = str_replace( array( '€', '$', '£', ' ' ), '', $raw );
            $normalized = str_replace( ',', '.', $normalized );
            // Keep digits, dot and minus
            $normalized = preg_replace( '/[^0-9\.\-]/', '', $normalized );
            if ( is_numeric( $normalized ) ) {
                // cast to float if decimal, or int if whole
                $amount = ( strpos( $normalized, '.' ) !== false ) ? floatval( $normalized ) : intval( $normalized );
            }
        }

        $attributes = array(
            'pipeline' => isset( $action_options['pipeline'] ) ? $action_options['pipeline'] : '',
            'deal_stage' => isset( $action_options['stage'] ) ? $action_options['stage'] : '',
        );
        if ( $amount !== null ) {
            $attributes['amount'] = $amount;
        }
        if ( $close_date !== null ) {
            $attributes['close_date'] = $close_date;
        }

        $deal_data = array(
            'name' => isset( $action_options['name'] ) ? $action_options['name'] : '',
            'attributes' => $attributes,
        );


        // Bail if user email is empty
        if (empty($deal_data['name'])) {
            $this->result = __('Deal name field is empty.', 'automatorwp-brevo');
            return;
        }

        $this->result = '';

        // Bail if brevo not configured
        if (!automatorwp_brevo_get_api()) {
            $this->result = __('Brevo integration not configured in AutomatorWP settings.', 'automatorwp-brevo');
            return;
        }

        $response = automatorwp_brevo_create_deal($deal_data);

        if ($response === 201) {
            $this->result = sprintf(__('Deal %s created', 'automatorwp-brevo'), $deal_data['name']);
        } else {
            $this->result = __('The deal could not be created', 'automatorwp-brevo');
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

new AutomatorWP_Brevo_Create_Deal();