<?php

/**
 * Appointment Rejected
 */
if (!defined('ABSPATH')) exit;

class AutomatorWP_BookingPress_Appointment_Rejected extends AutomatorWP_Integration_Trigger
{

    public $integration = 'bookingpress';
    public $trigger = 'bookingpress_appointment_rejected';

    public function register()
    {
        automatorwp_register_trigger($this->trigger, array(
            'integration'   => $this->integration,
            'label'         => __('User appointment is rejected', 'automatorwp-bookingpress'),
            'select_option' => __('User appointment is <strong>rejected</strong>', 'automatorwp-bookingpress'),
            'edit_label'    => sprintf(__('User appointment is rejected %1$s time(s)', 'automatorwp-bookingpress'), '{times}'),
            'log_label'     => __('User appointment is rejected', 'automatorwp-bookingpress'),
            'action'        => 'bookingpress_after_change_appointment_status',
            'function'      => array($this, 'listener'),
            'priority'      => 10,
            'accepted_args' => 2,
            'options'       => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags'          => automatorwp_utilities_times_tag()
        ));
    }

    public function hooks()
    {
        add_filter('automatorwp_user_deserves_trigger_' . $this->trigger, array($this, 'user_deserves_trigger'), 10, 6);
        add_filter('automatorwp_user_completed_trigger_log_meta', array($this, 'log_meta'), 10, 6);
        parent::hooks();
    }

     /**
     * Trigger AutomatorWP when an appointment is rejected for a real WordPress user
     */
    public function listener($appointment_id, $appointment_status)
    {
        if ((string) $appointment_status !== '4') return; // 4 = Rejected

        $appointment_id = absint($appointment_id);
        if (empty($appointment_id)) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'bookingpress_appointment_bookings';
        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE bookingpress_appointment_booking_id = %d", $appointment_id));

        if (! $appointment || empty($appointment->bookingpress_customer_id)) return;

        $user_id = automatorwp_bookingpress_get_wp_user_id_from_customer_id($appointment->bookingpress_customer_id);

        if (empty($user_id)) {
            return;
        }

        automatorwp_trigger_event(array(
            'trigger'        => $this->trigger,
            'user_id'        => absint($user_id),
            'appointment_id' => $appointment_id,
        ));
    }

    public function user_deserves_trigger($deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation)
    {
        if (! isset($event['appointment_id'])) return false;
        return $deserves_trigger;
    }

    public function log_meta($log_meta, $trigger, $user_id, $event, $trigger_options, $automation)
    {
        if ($trigger->type !== $this->trigger) return $log_meta;

        $log_meta['appointment_id'] = isset($event['appointment_id']) ? absint($event['appointment_id']) : '';

        return $log_meta;
    }
}
new AutomatorWP_BookingPress_Appointment_Rejected();
