<?php
/**
 * Filters
 *
 * @package     AutomatorWP\Schedule_Actions\Filters
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Filter to extend registered action arguments
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_schedule_actions_register_action_args() {

    foreach( AutomatorWP()->actions as $action => $args ) {

        if( $action === 'automatorwp_anonymous_user' ) {
            continue;
        }

        if( $action === 'filter' ) {
            continue;
        }

        $args['edit_label'] .= '<br>{schedule_action}';

        if( ! isset( $args['options'] ) ) {
            $args['options'] = array();
        }

        $args['options']['schedule_action'] = array(
            'default' => __( 'Schedule', 'automatorwp-schedule-actions' ),
            'fields' => array(
                'delay_action' => array(
                    'name' => __( 'Delay', 'automatorwp-schedule-actions' ),
                    'desc' => __( 'Delay', 'automatorwp-schedule-actions' ),
                    'type' => 'checkbox',
                    'classes' => 'cmb2-switch',
                ),
                'delay_action_amount' => array(
                    'name' => __( 'Delay this action for:', 'automatorwp-schedule-actions' ),
                    'type' => 'text',
                    'attributes' => array(
                        'type' => 'number',
                        'min' => '0',
                        'step' => '1',
                        'placeholder' => '0',
                    ),
                ),
                'delay_action_period' => array(
                    'name' => '&nbsp;',
                    'type' => 'select',
                    'options' => array(
                        'seconds'   => __( 'Seconds', 'automatorwp-schedule-actions' ),
                        'minutes'   => __( 'Minutes', 'automatorwp-schedule-actions' ),
                        'hours'     => __( 'Hours', 'automatorwp-schedule-actions' ),
                        'days'      => __( 'Days', 'automatorwp-schedule-actions' ),
                        'weeks'     => __( 'Weeks', 'automatorwp-schedule-actions' ),
                        'months'    => __( 'Months', 'automatorwp-schedule-actions' ),
                        'years'     => __( 'Years', 'automatorwp-schedule-actions' ),
                    ),
                    'default' => 'minutes'
                ),
                'schedule_action' => array(
                    'name' => __( 'Schedule', 'automatorwp-schedule-actions' ),
                    'desc' => __( 'Schedule', 'automatorwp-schedule-actions' ),
                    'type' => 'checkbox',
                    'classes' => 'cmb2-switch',
                ),
                'schedule_action_datetime' => array(
                    'name' => __( 'Execute this action on:', 'automatorwp-schedule-actions' ),
                    'desc' => __( 'If user completes the automation after this date and time, the action will be executed <strong>immediately</strong>.', 'automatorwp-schedule-actions' ),
                    'type' => 'text_datetime_timestamp',
                    'date_format' =>  automatorwp_schedule_actions_get_date_format( array( 'Y-m-d', 'm/d/Y' ) ),
                    'time_format' =>  automatorwp_schedule_actions_get_time_format(),
                ),
            )
        );

        AutomatorWP()->actions[$action] = $args;

    }

}
add_action( 'init', 'automatorwp_schedule_actions_register_action_args', 9999 );

/**
 * Filter to dynamically change the edit label
 *
 * @since 1.0.0
 *
 * @param string    $label      The edit label
 * @param stdClass  $object     The trigger/action object
 * @param string    $item_type  The item type (trigger|action)
 * @param string    $context    The context this function is executed
 * @param array     $type_args  The type parameters
 *
 * @return string
 */
function automatorwp_schedule_actions_parse_automation_item_edit_label( $label, $object, $item_type, $context, $type_args ) {

    // Bail if not is an action
    if( $item_type !== 'action' ) {
        return $label;
    }

    if( $object->type === 'automatorwp_anonymous_user' ) {
        return $label;
    }

    if( $object->type === 'filter' ) {
        return $label;
    }

    if( strpos( $label, '{schedule_action}' ) === false ) {
        $label .= '<br>{schedule_action}';
    }

    return $label;

}
add_filter( 'automatorwp_parse_automation_item_edit_label', 'automatorwp_schedule_actions_parse_automation_item_edit_label', 9999, 5 );

/**
 * Filters the option default value for replacement on labels
 *
 * @since 1.0.0
 *
 * @param string    $value      The option default value
 * @param stdClass  $object     The trigger/action object
 * @param string    $item_type  The item type (trigger|action)
 * @param string    $option     The option name
 * @param string    $context    The context this function is executed
 *
 * @return string
 */
function automatorwp_schedule_actions_get_automation_item_option_replacement( $value, $object, $item_type, $option, $context ) {

    // Bail if not is our option
    if( $option !== 'schedule_action' ) {
        return $value;
    }

    ct_setup_table( "automatorwp_{$item_type}s" );

    $delay = (bool) ct_get_object_meta( $object->id, 'delay_action', true );
    $schedule = (bool) ct_get_object_meta( $object->id, 'schedule_action', true );

    if( $delay ) {
        $schedule_amount = absint( ct_get_object_meta( $object->id, 'delay_action_amount', true ) );

        if( $schedule_amount > 0 ) {
            $schedule_period = ct_get_object_meta( $object->id, 'delay_action_period', true );

            if( $schedule_amount === 1 ) {
                $schedule_period = strtolower( automatorwp_schedule_actions_get_period_singular( $schedule_period ) );
            }

            $value = sprintf( __( 'Delayed for %s %s later', 'automatorwp-schedule-actions' ), $schedule_amount, $schedule_period );
        }

        // Add parenthesis on view context
        if( $context === 'view' ) {
            $value = "({$value})";
        }
    } else if ( $schedule ) {

        $schedule_datetime = absint( ct_get_object_meta( $object->id, 'schedule_action_datetime', true ) );

        if( $schedule_datetime !== 0 ) {
            $value = sprintf( __( 'Scheduled for %s', 'automatorwp-schedule-actions' ), date( automatorwp_schedule_actions_get_datetime_format(), $schedule_datetime ) );
        }

        // Add parenthesis on view context
        if( $context === 'view' ) {
            $value = "({$value})";
        }
    } else if( $context === 'view' ) {
        // Prevent to display "Schedule" on view context
        $value = '';
    }

    ct_reset_setup_table();

    return $value;

}
add_filter( 'automatorwp_get_automation_item_option_replacement', 'automatorwp_schedule_actions_get_automation_item_option_replacement', 10, 5 );

/**
 * Filters the option button class
 *
 * @since 1.0.0
 *
 * @param string    $option_class   The option class, by default "button button-primary"
 * @param stdClass  $object         The trigger/action object
 * @param string    $item_type      The item type (trigger|action)
 * @param string    $option         The option name
 * @param string    $context        The context this function is executed
 *
 * @return string
 */
function automatorwp_schedule_actions_get_automation_item_option_button_class( $option_class, $object, $item_type, $option, $context ) {

    // Bail if not is our option
    if( $option !== 'schedule_action' ) {
        return $option_class;
    }

    ct_setup_table( "automatorwp_{$item_type}s" );

    $delay = (bool) ct_get_object_meta( $object->id, 'delay_action', true );
    $schedule = (bool) ct_get_object_meta( $object->id, 'schedule_action', true );

    if( $delay ) {

        $delay_amount = absint( ct_get_object_meta( $object->id, 'delay_action_amount', true ) );

        if( $delay_amount === 0 ) {
            $option_class = 'button';
        }

    } else if( $schedule ) {

        $schedule_datetime = absint( ct_get_object_meta( $object->id, 'schedule_action_datetime', true ) );

        if( $schedule_datetime === 0 ) {
            $option_class = 'button';
        }

    } else {
        $option_class = 'button';
    }

    ct_reset_setup_table();

    return $option_class;

}
add_filter( 'automatorwp_get_automation_item_option_button_class', 'automatorwp_schedule_actions_get_automation_item_option_button_class', 10, 5 );

/**
 * Available filter to determine if an action should be executed or not
 *
 * @since 1.0.0
 *
 * @param bool      $execute            Determines if the action should get executed, by default true
 * @param stdClass  $action             The action object
 * @param int       $user_id            The user ID
 * @param array     $event              Event information
 * @param array     $action_options     The action's stored options (with tags already passed)
 * @param stdClass  $automation         The action's automation object
 *
 * @return bool
 */
function automatorwp_schedule_actions_can_execute_action( $execute, $action, $user_id, $event, $action_options, $automation ) {

    global $automatorwp_completed_triggers, $automatorwp_schedule_actions_action_id;

    // Initialize completed triggers global
    if( ! is_array( $automatorwp_completed_triggers ) ) {
        $automatorwp_completed_triggers = array();
    }

    if( ! $execute ) {
        return $execute;
    }

    $action_id = absint( $action->id );

    // Bail if this execution comes from a scheduled action
    if( $automatorwp_schedule_actions_action_id === $action_id ) {
        return $execute;
    }

    // Initialize delay action option
    if( ! isset( $action_options['delay_action'] ) ) {
        $action_options['delay_action'] = false;
        $delay_action_meta = (bool) automatorwp_get_action_meta( $action_id, 'delay_action' );

        if ( $delay_action_meta ) {
            $action_options['delay_action'] = $delay_action_meta;
            $action_options['delay_action_amount'] = automatorwp_get_action_meta( $action_id, 'delay_action_amount', true );
            $action_options['delay_action_period'] = automatorwp_get_action_meta( $action_id, 'delay_action_period', true );
        }

    }
    
    // Initialize schedule action option
    if( ! isset( $action_options['schedule_action'] ) ) {
        $action_options['schedule_action'] = false;
        $schedule_action_meta = (bool) automatorwp_get_action_meta( $action_id, 'schedule_action' );

        if ( ! empty ((bool) $schedule_action_meta ) ) {
            $action_options['schedule_action'] = $schedule_action_meta;
            $action_options['schedule_action_datetime'] = automatorwp_get_action_meta( $action_id, 'schedule_action_datetime', true );
        }

    }

    $delay = (bool) $action_options['delay_action'];
    $schedule = (bool) $action_options['schedule_action'];

    $datetime = 0;
    $datetime_utc = 0;

    if( $delay ) {
        // Delay

        $delay_amount = absint( $action_options['delay_action_amount'] );

        // Bail if delay amount not configured
        if( $delay_amount <= 0 ) {
            return $execute;
        }

        $delay_period = $action_options['delay_action_period'];

        // Get the scheduled datetime (local and UTC)
        $datetime = automatorwp_schedule_actions_get_scheduled_datetime( $delay_amount, $delay_period );
        $datetime_utc = automatorwp_schedule_actions_get_scheduled_datetime( $delay_amount, $delay_period, true );

    } else if( $schedule ) {
        // Schedule

        $schedule_datetime = absint( $action_options['schedule_action_datetime'] );

        // Bail if schedule datetime not configured
        if( $schedule_datetime <= 0 ) {
            return $execute;
        }

        // Bail if the scheduled datetime is lower than now
        if( current_time( 'timestamp' ) > $schedule_datetime ) {
            return $execute;
        }

        $datetime = $schedule_datetime;
        $datetime_utc = $schedule_datetime;

    }

    // Bail if datetime not configured
    if( $datetime === 0 ) {
        return $execute;
    }

    // Get the datetime from
    $datetime_from = current_time( 'timestamp' ) + count( $automatorwp_completed_triggers );

    // Schedule the event

    // Check if Action Scheduler is installed, if not then use WordPress functions
    if( function_exists( 'as_schedule_single_action' ) && ! apply_filters( 'automatorwp_schedule_actions_force_wp_cron', false ) ) {
        as_schedule_single_action( $datetime_utc, 'automatorwp_schedule_actions_execute_action', array( $action_id, $user_id, $event, $datetime_from ) );
    } else {
        wp_schedule_single_event( $datetime_utc, 'automatorwp_schedule_actions_execute_action', array( $action_id, $user_id, $event, $datetime_from ) );
    }

    // Log entry

    $log_meta = array(
        'event' => $event,
    );

    // Parse the log label (including the automation tags)
    $log_title = automatorwp_parse_automation_item_log_label( $action, 'action', 'view' );
    $log_title = automatorwp_parse_automation_tags( $automation->id, $user_id, $log_title );

    $log_title = sprintf( __( 'Action "%s" scheduled for %s', 'automatorwp-schedule-actions' ), $log_title, date( 'Y-m-d H:i:s', $datetime ) );

    // Insert log entry to let the user know that action has been schedule
    $log_id = automatorwp_insert_log( array(
        'title'     => $log_title,
        'type'      => 'schedule_actions',
        'object_id' => $action->id,
        'user_id'   => $user_id,
        'date'      => date( 'Y-m-d H:i:s', $datetime_from ),
    ), $log_meta );

    // Prevent action execution
    $execute = false;

    return $execute;

}
add_filter( 'automatorwp_can_execute_action', 'automatorwp_schedule_actions_can_execute_action', 10, 6 );

/**
 * Execute a scheduled action
 *
 * @since 1.0.0
 *
 * @param int       $action_id          The action ID
 * @param int       $user_id            The user ID
 * @param array     $event              Event information
 * @param int       $datetime           Datetime to execute the action from
 *
 * @return bool
 */
function automatorwp_schedule_actions_execute_action( $action_id = 0, $user_id = 0, $event = array(), $datetime = 0 ) {

    global $automatorwp_schedule_actions_action_id, $automatorwp_schedule_actions_datetime;

    // Sanitize vars
    $action_id = absint( $action_id );
    $user_id = absint( $user_id );
    $datetime = absint( $datetime );

    // Initialize globals
    $automatorwp_schedule_actions_action_id = $action_id;
    $automatorwp_schedule_actions_datetime = $datetime;

    // Check action's object
    $action = automatorwp_get_action_object( $action_id );

    if( ! is_object( $action ) ) {
        return false;
    }

    // Check automation's object
    $automation = automatorwp_get_automation_object( $action->automation_id );

    if( ! $automation ) {
        return false;
    }

    // Execute the action scheduled
    automatorwp_execute_action( $action, $user_id, $event );

    // Backward compatibility
    if( $datetime === 0 && automatorwp_has_user_executed_all_automation_actions( $automation->id, $user_id ) ) {

        // Insert a new log entry to register the automation completion
        automatorwp_insert_log( array(
            'title'     => $automation->title,
            'type'      => 'automation',
            'object_id' => $automation->id,
            'user_id'   => $user_id,
            'post_id'   => ( isset( $event['post_id'] ) ? $event['post_id'] : 0 ),
            'date'      => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
        ) );

        /**
         * Available action to hook on an automation completion
         *
         * @since 1.0.0
         *
         * @param stdClass  $automation         The automation object
         * @param int       $user_id            The user ID
         * @param array     $event              Event information
         */
        do_action( 'automatorwp_user_completed_automation', $automation, $user_id, $event );

    }

    return true;

}
add_action( 'automatorwp_schedule_actions_execute_action', 'automatorwp_schedule_actions_execute_action', 10, 4 );

/**
 * Get the trigger last completion log using the datetime from
 *
 * @since 1.0.0
 *
 * @param stdClass  $log        The log object
 * @param stdClass  $trigger    The trigger object
 * @param int       $user_id    The user ID
 * @param string    $content    The content to parse
 *
 * @return stdClass
 */
function automatorwp_schedule_actions_get_trigger_last_completion_log( $log, $trigger, $user_id, $content = '' ) {

    global $wpdb;

    global $automatorwp_schedule_actions_datetime;

    $datetime = absint( $automatorwp_schedule_actions_datetime );

    if( $datetime !== 0 ) {

        // Increment datetime in 1 to be sure we are selecting the correct logs
        $datetime += 1;

        // First check if is stored in cache
        $cache = automatorwp_get_cache( 'schedule_actions_user_last_completion', array(), false );

        // If result already cached, return it
        if( isset( $cache[$user_id] )
            && isset( $cache[$user_id]['trigger'] )
            && isset( $cache[$user_id]['trigger'][$trigger->id] )
            && isset( $cache[$user_id]['trigger'][$trigger->id][$datetime] ) ) {
            return $cache[$user_id]['trigger'][$trigger->id][$datetime];
        }

        $date = date( 'Y-m-d H:i:s', $datetime );

        $ct_table = ct_setup_table( 'automatorwp_logs' );

        // Get the last log entry from the datetime
        $log = $wpdb->get_row(
            "SELECT *
            FROM {$ct_table->db->table_name} AS l
            WHERE 1=1 
            AND l.object_id = {$trigger->id}
            AND l.user_id = {$user_id}
            AND l.type = 'trigger'
            AND l.date < '{$date}'
            ORDER BY l.date DESC
            LIMIT 1"
        );

        ct_reset_setup_table();

        if( $log ) {
            // Store result in cache
            $cache[$user_id]['trigger'][$trigger->id][$datetime] = $log;

            automatorwp_set_cache( 'schedule_actions_user_last_completion', $cache );
        }

    }

    return $log;

}
add_filter( 'automatorwp_get_trigger_last_completion_log', 'automatorwp_schedule_actions_get_trigger_last_completion_log', 10, 4 );

/**
 * Get the user last completion log using the datetime from
 *
 * @since 1.0.0
 *
 * @param stdClass  $log        The log object
 * @param stdClass  $object_id    The trigger object
 * @param int       $user_id    The user ID
 * @param string    $type    The content to parse
 *
 * @return stdClass
 */
function automatorwp_schedule_actions_get_user_last_completion_log( $log, $object_id, $user_id, $type = 'trigger' ) {

    global $wpdb;

    global $automatorwp_schedule_actions_datetime;

    $datetime = absint( $automatorwp_schedule_actions_datetime );

    if( $datetime !== 0 ) {

        // Increment datetime in 1 to be sure we are selecting the correct logs
        $datetime += 1;
        
        // First check if is stored in cache
        $cache = automatorwp_get_cache( 'schedule_actions_user_last_completion', array(), false );

        // If result already cached, return it
        if( isset( $cache[$user_id] )
            && isset( $cache[$user_id]['trigger'] )
            && isset( $cache[$user_id]['trigger'][$object_id] )
            && isset( $cache[$user_id]['trigger'][$object_id][$datetime] ) ) {
            return $cache[$user_id]['trigger'][$object_id][$datetime];
        }

        $date = date( 'Y-m-d H:i:s', $datetime );
        
        $ct_table = ct_setup_table( 'automatorwp_logs' );

        // Get the last log entry from the datetime
        $log = $wpdb->get_row(
            "SELECT *
            FROM {$ct_table->db->table_name} AS l
            WHERE 1=1 
            AND l.object_id = {$object_id}
            AND l.user_id = {$user_id}
            AND l.type = 'trigger'
            AND l.date < '{$date}'
            ORDER BY l.date DESC
            LIMIT 1"
        );

        ct_reset_setup_table();

        if( $log ) {
            // Store result in cache
            $cache[$user_id]['trigger'][$object_id][$datetime] = $log;

            automatorwp_set_cache( 'schedule_actions_user_last_completion', $cache );
        }

    }
    
    return $log;

}
add_filter( 'automatorwp_get_user_last_completion', 'automatorwp_schedule_actions_get_user_last_completion_log', 10, 4 );

