<?php
/**
 * Purchase Subscription
 *
 * @package     AutomatorWP\Integrations\Paid_Membership_Subscriptions\Triggers\Purchase_Subscription
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class AutomatorWP_PMS_Purchase_Subscription extends AutomatorWP_Integration_Trigger
{

    public $integration = 'paid_membership_subscriptions';
    public $trigger = 'paid_membership_subscriptions_purchase_subscription';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register()
    {

        automatorwp_register_trigger(
            $this->trigger,
            array(
                'integration' => $this->integration,
                'label' => __('User purchases a subscription of a membership level', 'automatorwp'),
                'select_option' => __('User <strong>purchase</strong> a subscription plan', 'automatorwp'),
                /* translators: %1$s: Content title. %2$s: Number of times. */
                'edit_label' => sprintf(__('User purchase %1$s %2$s time(s)', 'automatorwp'), '{subscription_plan}', '{times}'),
                /* translators: %1$s: Content title. */
                'log_label' => sprintf(__('User purchase a subscription plan', 'automatorwp')),
                'action' => 'pms_member_subscription_insert',
                'function' => array($this, 'listener'),
                'priority' => 10,
                'accepted_args' => 2,
                'options' => array(
                    'subscription_plan' => automatorwp_utilities_post_option(
                        array(
                            'name' => __('Subscription plan:', 'automatorwp'),
                            'option_none_label' => __('any subscription plan', 'automatorwp'),
                            'post_type' => 'pms-subscription'
                        )
                    ),
                    'times' => automatorwp_utilities_times_option(),
                ),
                'tags' => array_merge(
                    automatorwp_pms_get_subscription_tags(),
                    automatorwp_utilities_times_tag()
                )
            )
        );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     * 
     *                
     */
    public function listener($id, $data) {

        $user_id = $data['user_id'];

        // Login is required
        if ($user_id === 0) {
            return;
        }

        // Subscription tags
        $subscription_plan_id = $data['subscription_plan_id'];
        $subscription_plan_name = automatorwp_pms_get_subscription_name( $subscription_plan_id );

        automatorwp_trigger_event(
            array(
                'trigger' => $this->trigger,
                'user_id' => $user_id,
                'subscription_plan_id' => $subscription_plan_id,
                'subscription_plan_name' => $subscription_plan_name
            )
        );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */
    public function user_deserves_trigger($deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation) {


        // Don't deserve if membership is not received
        if (!isset ($event['subscription_plan_id'])) {
            return false;
        }

        // Bail if post doesn't match with the trigger option
        if( $trigger_options['post'] !== 'any' && absint( $event['subscription_plan_id'] ) !== absint( $trigger_options['post'] ) ) {
            return false;
        }
        
        return $deserves_trigger;
        
    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }
        
        $log_meta['subscription_plan'] = ( isset( $event['subscription_plan_id'] ) ? $event['subscription_plan_id'] : array() );
        $log_meta['subscription_plan_id'] = ( isset( $event['subscription_plan_id'] ) ? $event['subscription_plan_id'] : array() );
        $log_meta['subscription_plan_name'] = ( isset( $event['subscription_plan_name'] ) ? $event['subscription_plan_name'] : array() );

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
    public function log_fields( $log_fields, $log, $object ) {

        // Bail if log is not assigned to an trigger
        if( $log->type !== 'trigger' ) {
            return $log_fields;
        }

        // Bail if trigger type don't match this trigger
        if( $object->type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['subscription_plan'] = array(
            'name' => __( 'Subscription plan', 'automatorwp' ),
            'desc' => __( 'Information about the subscription plan purchased.', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;

    }
}

new AutomatorWP_PMS_Purchase_Subscription();