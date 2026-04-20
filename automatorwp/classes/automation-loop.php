<?php
/**
 * Automation Loop
 *
 * @package     AutomatorWP\Classes\Automation_Loop
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Automation_Loop extends AutomatorWP_Automation_Type {

    /**
     * Type
     *
     * @since 1.0.0
     *
     * @var array $args
     */
    public $args = array(
        'image' => AUTOMATORWP_URL . 'assets/img/automatorwp-logo.svg',
        'label' => '',
        'desc'  => '',
        'labels' => array(
            'singular' => '',
            'plural' => '',
        ),
        'required_trigger' => '',
    );

    /**
     * Process the given items IDs to run the automation actions on them
     *
     * @since 2.2.2
     *
     * @param array     $items_ids          The items IDs array.
     * @param stdClass  $automation         The automation object.
     * @param stdClass  $trigger            The trigger object. null if required_trigger is empty
     * @param array     $trigger_options    The trigger options array. An empty array if required_trigger is empty
     *
     * @return array|false
     */
    public function process_items( $items_ids, $automation, $trigger, $trigger_options ) {
        // Override
    }

    /**
     * Get the items count
     *
     * @since 2.2.2
     *
     * @param stdClass  $automation         The automation object.
     * @param stdClass  $trigger            The trigger object. null if required_trigger is empty
     *
     * @return int|false
     */
    public function get_items_count( $automation, $trigger ) {
        // OVERRIDE in case the items count is not from the database

        global $wpdb;

        // Get the posts SQL
        $sql = $this->get_items_sql( $automation, $trigger, true );

        if( ! $sql ) {
            return false;
        }

        return absint( $wpdb->get_var( $sql ) );
    }

    /**
     * Get the items IDs
     *
     * @since 2.2.2
     *
     * @param stdClass  $automation         The automation object.
     * @param stdClass  $trigger            The trigger object. null if required_trigger is empty
     *
     * @return array|false
     */
    public function get_items_ids( $automation, $trigger ) {
        // OVERRIDE in case the items count is not from the database

        global $wpdb;

        // Get the posts SQL
        $sql = $this->get_items_sql( $automation, $trigger, false );

        if( ! $sql ) {
            return false;
        }

        return $wpdb->get_col( $sql );
    }

    /**
     * Get the items SQL (used for count and get ids)
     *
     * @since 2.2.2
     *
     * @param stdClass  $automation         The automation object.
     * @param stdClass  $trigger            The trigger object.
     *
     * @return string|false
     */
    public function get_items_sql( $automation, $trigger, $count = false ) {

        global $automatorwp_run_automation_error;

        if( ! is_object( $automation ) ) {
            $automatorwp_run_automation_error = __( 'Invalid automation.', 'automatorwp' );
            return false;
        }

        $loop = 0;
        $items_per_loop = 0;

        if( ! $count ) {
            $items_per_loop = absint( automatorwp_get_automation_meta( $automation->id, 'items_per_loop', true ) );

            // Bail if users per loop not correctly configured
            if( $items_per_loop <= 0 ) {
                $automatorwp_run_automation_error = sprintf( __( '%s per loop needs to be higher than 0.', 'automatorwp' ), $this->args['labels']['plural'] );
                return false;
            }

            // Get the loop stored in options to calculate the offset
            $loop = absint( automatorwp_get_automation_meta( $automation->id, 'current_loop', true ) );
        }

        $trigger_options = array();

        if( $trigger ) {
            // Get the trigger stored options
            $trigger_options = automatorwp_get_trigger_stored_options( $trigger->id );
        }

        $sql = false;

        /**
         * Available filter to override the all users automation SQL (overwritten by the trigger)
         *
         * @since 2.2.2
         *
         * @param string    $sql                The SQL query
         * @param stdClass  $automation         The automation object
         * @param stdClass  $trigger            The trigger object
         * @param bool      $count              True if is looking for the SQL to count the number of users
         * @param array     $trigger_options    The trigger's stored options
         * @param int       $items_per_loop     The automation items per loop option
         * @param int       $loop               The current loop
         */
        $sql = apply_filters( "automatorwp_get_{$this->type}_automation_sql", $sql, $automation, $trigger, $count, $trigger_options, $items_per_loop, $loop );

        return $sql;

    }

    public function hooks() {

        // Register as loop type
        add_filter( 'automatorwp_automation_loop_types', array( $this, 'register_as_automation_loop_type' ), 10, 1 );

        // On insert/update hooks
        add_action( 'cmb2_admin_init', array( $this, 'meta_boxes' ), 9 );
        add_filter( 'automatorwp_automation_ui_admin_body_class', array( $this, 'automation_ui_admin_body_class' ), 10, 2 );

        add_action( 'ct_insert_object', array( $this, 'on_insert_automation' ), 10, 3 );
        add_action( 'ct_insert_object', array( $this, 'on_update_automation' ), 10, 3 );

        add_filter( 'automatorwp_check_automation_required_triggers', array( $this, 'automation_required_triggers' ), 10, 2 );

        // Run automation hooks
        add_action('automatorwp_ajax_run_automation', array( $this, 'ajax_run_automation' ) );

        add_filter( 'automatorwp_get_automation_run_details_count', array( $this, 'automation_run_details_count' ), 10, 2 );

        add_filter( 'automatorwp_run_automation_result', array( $this, 'maybe_run_automation' ), 10, 2 );

        parent::hooks();

    }

    /**
     * Register the automation loop type
     *
     * @since 2.2.2
     *
     * @param array     $loop_types          The automation loop types.
     *
     * @return array
     */
    public function register_as_automation_loop_type( $loop_types ) {

        $loop_types[] = $this->type;

        return $loop_types;

    }

    /**
     * Register custom CMB2 meta boxes
     *
     * @since  1.0.0
     */
    public function meta_boxes() {

        // Execution Options
        $default_datetime = strtotime( date( 'Y-m-d 00:00:00', current_time( 'timestamp' ) ) );

        automatorwp_add_meta_box(
            'automatorwp-automations-execution-options',
            __( 'Execution Options', 'automatorwp' ),
            'automatorwp_automations',
            array(
                'items_per_loop' => array(
                    'type' 	=> 'text',
                    'default' 	=> '200',
                    'attributes' => array(
                        'type' => 'number',
                        'min' => '1',
                    ),
                    'before_field' 	=> array( $this, 'items_per_loop_before_field' ),
                    'after_field' 	=> array( $this, 'items_per_loop_after_field' ),
                ),
                'delay_amount' => array(
                    'type' 	=> 'text',
                    'default' 	=> '1',
                    'attributes' => array(
                        'type' => 'number',
                        'min' => '1',
                    ),
                    'before_field' 	=> array( $this, 'delay_amount_before_field' ),
                ),
                'delay_seconds' => array(
                    'type' 	=> 'select',
                    'options' 	=> array(
                        MINUTE_IN_SECONDS => __( 'Minutes', 'automatorwp' ),
                        HOUR_IN_SECONDS => __( 'Hours', 'automatorwp' ),
                        DAY_IN_SECONDS => __( 'Days', 'automatorwp' ),
                        MONTH_IN_SECONDS => __( 'Months', 'automatorwp' ),
                        YEAR_IN_SECONDS => __( 'Years', 'automatorwp' ),
                    ),
                ),
                'schedule_run' => array(
                    'name' 	=> '<div class="dashicons dashicons-calendar-alt"></div>' . __( 'Schedule execution:', 'automatorwp' ),
                    'type' 	=> 'checkbox',
                    'classes' => 'cmb2-switch',
                ),
                'schedule_run_datetime' => array(
                    'name' => __( 'Execute this automation on:', 'automatorwp' ),
                    'type' => 'text_datetime_timestamp',
                    'date_format' =>  automatorwp_get_date_format( array( 'Y-m-d', 'm/d/Y' ) ),
                    'time_format' =>  automatorwp_get_time_format(),
                    'default' => $default_datetime,
                ),
                'recurring_run' => array(
                    'name' 	=> '<div class="dashicons dashicons-backup"></div>' . __( 'Recurring execution:', 'automatorwp' ),
                    'type' 	=> 'checkbox',
                    'classes' => 'cmb2-switch',
                ),
                'recurring_run_day' => array(
                    'name' 	=> __( 'Execute this automation:', 'automatorwp' ),
                    'desc' 	=> __( 'of', 'automatorwp' ),
                    'type' 	=> 'text',
                    'default' 	=> '1',
                    'attributes' => array(
                        'type' => 'number',
                        'min' => '1',
                        'max' => '365',
                    ),
                    'before_row' 	=> array( $this, 'recurring_run_day_before_row' ),
                    'before_field' 	=> array( $this, 'recurring_run_day_before_field' ),
                ),
                'recurring_run_period' => array(
                    'name' 	=> __( 'Period:', 'automatorwp' ),
                    'type' 	=> 'select',
                    'default' 	=> 'day',
                    'options' => array(
                        'day'   => __( 'Every day', 'automatorwp' ),
                        'week'  => __( 'every week', 'automatorwp' ),
                        'month' => __( 'every month', 'automatorwp' ),
                        'year'  => __( 'every year', 'automatorwp' ),
                    )
                ),
                'recurring_run_time' => array(
                    'name' => __( 'at', 'automatorwp' ),
                    'type' => 'text_time',
                    'time_format' =>  automatorwp_get_time_format(),
                    'default' =>  date( automatorwp_get_time_format(), $default_datetime ),
                    'after_row' => array( $this, 'execute_options_actions' ),
                ),
            ),
            array(
                'priority' => 'default',
                'context' => 'side',
                'show_on_cb' => array( $this, 'execution_options_box_show_cb' ),
            )
        );

    }

    function get_automation_from_url() {

        global $ct_table;

        if( ! $ct_table ) {
            return false;
        }

        if( $ct_table->name !== 'automatorwp_automations' ) {
            return false;
        }

        $primary_key = $ct_table->db->primary_key;
        $object_id = isset( $_GET[$primary_key] ) ? absint( $_GET[$primary_key] ) : 0;

        $automation = ct_get_object( $object_id );

        // Bail if automation doesn't exists
        if( ! $automation ) {
            return false;
        }

        return $automation;

    }

    public function items_per_loop_before_field() {
        echo '<span class="automatorwp-items-per-loop-label-before">' . automatorwp_dashicon( 'controls-play' ) . __( 'Process', 'automatorwp' ) . '</span>';
    }

    public function items_per_loop_after_field() {

        $automation = $this->get_automation_from_url();
        $html = '<span class="automatorwp-items-per-loop-label-before">%s</span>';

        // Bail if automation doesn't exists
        if( ! $automation ) {
            echo sprintf( $html, __( 'items per loop', 'automatorwp' ) );
            return;
        }

        $automation_types = automatorwp_get_automation_types();

        $label = sprintf( __( '%s per loop', 'automatorwp' ), strtolower( $automation_types[$automation->type]['labels']['plural'] ) );

        echo sprintf( $html, $label );
    }

    public function delay_amount_before_field() {
        echo '<span class="automatorwp-delay-amount-label-before">' . automatorwp_dashicon( 'filter' ) . __( 'Every', 'automatorwp' ) . '</span>';
    }

    /**
     * Before recurring run day row
     *
     * @since 1.0.0
     */
    public function recurring_run_day_before_row() {
        ?><label for="recurring_run_day" class="automatorwp-recurring-run-day-label"><?php _e( 'Execute this automation:', 'automatorwp' ); ?></label><?php
    }

    /**
     * Before recurring run day field
     *
     * @since 1.0.0
     */
    public function recurring_run_day_before_field() {
        ?><p class="cmb2-metabox-description"><?php _e( 'The day', 'automatorwp' ); ?></p><?php
    }

    /**
     * Execute options actions
     *
     * @since 1.0.0
     */
    public function execute_options_actions() {

        $automation = $this->get_automation_from_url();

        // Bail if automation doesn't exists
        if( ! $automation ) {
            return;
        }

        // Check if automation has been scheduled to show the next run date
        $schedule_run = (bool) automatorwp_get_automation_meta( $automation->id, 'schedule_run', true );
        $recurring_run = (bool) automatorwp_get_automation_meta( $automation->id, 'recurring_run', true );
        $next_run_date = automatorwp_get_automation_meta( $automation->id, 'next_run_date', true );

        if( ( $schedule_run || $recurring_run ) && ! empty( $next_run_date ) ) :
            $date_format = automatorwp_get_date_format();
            $time_format = automatorwp_get_time_format(); ?>
            <div class="automatorwp-next-run-date">
                <span class="dashicons dashicons-controls-play"></span>
                <?php _e( 'Next run:', 'automatorwp' ); ?>
                <strong><?php echo date( $date_format . ' ' . $time_format, strtotime( $next_run_date ) ); ?></strong>
            </div>
        <?php endif;

        // Setup vars for an automation in progress
        if( $automation->status === 'in-progress' ) {
            $original_status = automatorwp_get_automation_meta( $automation->id, 'original_status', true );
            $details = automatorwp_get_automation_run_details( $automation );
        }

        ?>
        <div id="major-publishing-actions">

            <div class="automatorwp-run-automation <?php if( $automation->status === 'in-progress' ) : ?>automatorwp-is-running<?php endif; ?>" <?php if( $automation->status === 'in-progress' ) : ?>data-original-status="<?php echo esc_attr( $original_status ); ?>"<?php endif; ?>>
                <button type="button" class="button button-primary button-large" <?php if( $automation->status === 'in-progress' ) : ?>disabled<?php endif; ?>>
                    <div class="automatorwp-run-automation-run-label" <?php if( $automation->status === 'in-progress' ) : ?>style="display: none;"<?php endif; ?>>
                        <span class="dashicons dashicons-controls-play"></span> <?php _e( 'Run automation', 'automatorwp' ); ?>
                    </div>
                    <div class="automatorwp-run-automation-running-label" <?php if( $automation->status !== 'in-progress' ) : ?>style="display: none;"<?php endif; ?>>
                        <span class="dashicons dashicons-update"></span> <?php _e( 'Running...', 'automatorwp' ); ?>
                    </div>
                    <div class="automatorwp-run-automation-done-label" style="display: none;">
                        <span class="dashicons dashicons-yes"></span> <?php _e( 'Done!', 'automatorwp' ); ?>
                    </div>
                </button>
            </div>

            <div class="automatorwp-cancel-automation-run" <?php if( $automation->status !== 'in-progress' ) : ?>style="display: none;"<?php endif; ?>>
                <button type="button" class="button automatorwp-button-danger button-large">
                    <div class="automatorwp-cancel-automation-run-cancel-label">
                        <?php _e( 'Cancel', 'automatorwp' ); ?>
                    </div>
                    <div class="automatorwp-cancel-automation-run-cancelling-label" style="display: none;">
                        <?php _e( 'Cancelling...', 'automatorwp' ); ?>
                    </div>
                    <div class="automatorwp-cancel-automation-run-done-label" style="display: none;">
                        <?php _e( 'Cancelled', 'automatorwp' ); ?>
                    </div>
                </button>
            </div>

            <div class="clear"></div>

            <div class="automatorwp-run-automation-progress" <?php if( $automation->status !== 'in-progress' ) : ?>style="display: none;"<?php endif; ?>>
                <div class="automatorwp-run-automation-progress-bar">
                    <?php if( $automation->status === 'in-progress' ) : ?>
                        <div class="automatorwp-run-automation-progress-current-progress" style="width: <?php echo $details['percentage']; ?>%"></div>
                    <?php else : ?>
                        <div class="automatorwp-run-automation-progress-current-progress"></div>
                    <?php endif; ?>
                </div>
                <?php if( $automation->status === 'in-progress' ) : ?>
                    <div class="automatorwp-run-automation-progress-text"><?php echo $details['processed'] . '/' . $details['count']; ?></div>
                <?php else : ?>
                    <div class="automatorwp-run-automation-progress-text">0/0</div>
                <?php endif; ?>
            </div>

            <div class="clear"></div>

        </div>

        <?php
    }

    /**
     * Execution options box show callback
     *
     * @since 1.0.0
     *
     * @param object $cmb CMB2 object
     *
     * @return bool        True/false whether to show the metabox
     */
    public function execution_options_box_show_cb( $cmb ) {

        global $ct_table;

        if( ! $ct_table ) {
            return false;
        }

        if( $ct_table->name !== 'automatorwp_automations' ) {
            return false;
        }

        $object_id = $cmb->object_id();

        $object = ct_get_object( $object_id );

        return ( in_array( $object->type, automatorwp_get_automation_loop_types() ) );

    }

    function automation_ui_admin_body_class( $classes, $automation ) {

        if( in_array( $automation->type, automatorwp_get_automation_loop_types() ) ) {
            $classes .= ' is-automatorwp-loop';
        }

        return $classes;

    }

    /**
     * Handler when a new automation is getting created
     *
     * @since 1.0.0
     *
     * @param int       $object_id    Object ID.
     * @param stdClass  $object       Object.
     * @param bool      $update       Whether this is an existing object being updated or not.
     */
    public function on_insert_automation( $object_id, $object, $update ) {

        global $ct_table;

        // If not is our custom table, return
        if( $ct_table->name !== 'automatorwp_automations' ) {
            return;
        }

        // Bail if updating
        if( $update ) {
            return;
        }

        // Check if is an loop automation
        if( $object->type !== $this->type ) {
            return;
        }

        /**
         * Filter available to define the default items per loop option
         *
         * @since 1.0.0
         *
         * @param int $items_per_loop
         *
         * @return int
         */
        $items_per_loop = apply_filters( 'automatorwp_default_items_per_loop', 200 );

        // Update the items per loop meta
        ct_update_object_meta( $object_id, 'items_per_loop', $items_per_loop );

    }

    /**
     * Handler when an automation is getting updated
     *
     * @since 1.0.0
     *
     * @param int       $object_id    Object ID.
     * @param stdClass  $object       Object.
     * @param bool      $update       Whether this is an existing object being updated or not.
     */
    public function on_update_automation( $object_id, $object, $update ) {

        global $ct_table;

        // If not is our custom table, return
        if( $ct_table->name !== 'automatorwp_automations' ) {
            return;
        }

        // Bail if not updating
        if( ! $update ) {
            return;
        }

        // Check if is an loop automation
        if( $object->type !== $this->type ) {
            return;
        }

        // Calculate the next run date based on settings submitted
        automatorwp_update_automation_next_run_date( $object_id, true );
    }

    /**
     * If required_trigger configure, ensures that the trigger is present when creating the automation
     *
     * @since 2.2.2
     *
     * @param array     $triggers           The automation triggers list.
     * @param stdClass  $automation         The automation object.
     *
     * @return array
     */
    public function automation_required_triggers( $triggers, $automation ) {

        if( $automation->type !== $this->type ) {
            return $triggers;
        }

        if( $this->args['required_trigger'] === '' ) {
            return $triggers;
        }

        // Creates the required trigger if not exists in the automation

        $create = false;

        // Check if the first action is the action required for anonymous automations
        if( ! isset( $triggers[0] ) ) {
            $create = true;
        }

        if( isset( $triggers[0] ) && $triggers[0]->type !== $this->args['required_trigger'] ) {
            $create = true;
        }

        if( $create ) {
            ct_setup_table( 'automatorwp_triggers' );

            $trigger_args = automatorwp_get_trigger( $this->args['required_trigger'] );

            // Setup the trigger data
            $trigger_data = array(
                'automation_id' => $automation->id,
                'title' => $trigger_args['label'],
                'type' => $this->args['required_trigger'],
                'status' => 'active',
                'position' => 0,
                'date' => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
            );

            // Insert the new trigger
            $trigger_id = ct_insert_object( $trigger_data );

            if( $trigger_id ) {
                $trigger_data['id'] = $trigger_id;

                $trigger_data = (object) $trigger_data;

                // Prepend the new trigger at start of the triggers list
                array_unshift( $triggers, $trigger_data );
            }

            ct_reset_setup_table();
        }


        return $triggers;

    }

    /**
     * Handles the ajax run for automation loop
     *
     * @since 2.2.2
     *
     * @param stdClass  $automation         The automation object.
     */
    public function ajax_run_automation( $automation ) {

        if( $automation->type !== $this->type ) {
            return;
        }

        $items_per_loop = ( isset( $_POST['items_per_loop'] ) ? absint( $_POST['items_per_loop'] ) : 0 );

        if( $items_per_loop <= 0 ) {
            wp_send_json_error( sprintf( __( '%s per loop need to be higher than 0.', 'automatorwp' ), $this->args['labels']['plural'] ) );
        }

        // Get the loop before gets updated
        $loop = absint( automatorwp_get_automation_meta( $automation->id, 'current_loop', true ) );

        // First loop checks
        if( $loop === 0 ) {

            // Update the items per loop
            $stored_items_per_loop = absint( automatorwp_get_automation_meta( $automation->id, 'items_per_loop', true ) );

            if( $items_per_loop !== $stored_items_per_loop ) {
                automatorwp_update_automation_meta( $automation->id, 'items_per_loop', $items_per_loop );
            }

            // Update a flag to meet that is a manual run
            automatorwp_update_automation_meta( $automation->id, 'manual_run', '1' );
        }
    }

    /**
     * Updates the automation run details count
     *
     * @since 2.2.2
     *
     * @param int     $count                The run details count.
     * @param stdClass  $automation         The automation object.
     *
     * @return array
     */
    public function automation_run_details_count( $count, $automation ) {

        global $automatorwp_run_automation_error;

        if( ! is_object( $automation ) ) {
            $automatorwp_run_automation_error = __( 'Invalid automation.', 'automatorwp' );
            return $count;
        }

        if( $automation->type !== $this->type ) {
            return $count;
        }

        $trigger = null;

        if( $this->args['required_trigger'] !== '' ) {
            $trigger = automatorwp_get_automation_trigger_by_type( $automation->id, $this->args['required_trigger'] );

            // Prevent automations already in progress
            if( ! $trigger ) {
                $automatorwp_run_automation_error = __( 'Trigger configuration not found.', 'automatorwp' );
                return $count;
            }
        }

        return $this->get_items_count( $automation, $trigger );
    }

    /**
     * Checks if should run automation
     *
     * @since 2.2.2
     *
     * @param array     $run_details        The automation run details.
     * @param stdClass  $automation         The automation object.
     *
     * @return array
     */
    function maybe_run_automation( $result, $automation ) {

        global $automatorwp_run_automation_error;

        if( ! is_object( $automation ) ) {
            $automatorwp_run_automation_error = __( 'Invalid automation.', 'automatorwp' );
            return false;
        }

        if( $automation->type !== $this->type ) {
            return $result;
        }

        $result = $this->run_automation( $automation );

        return $result;

    }

    /**
     * Run this automation
     *
     * @since 2.2.2
     *
     * @param stdClass  $automation         The automation object.
     *
     * @return bool                         True on success, false on failure
     */
    public function run_automation( $automation ) {

        global $automatorwp_run_automation_error;

        // Get the items per loop
        $items_per_loop = absint( automatorwp_get_automation_meta( $automation->id, 'items_per_loop', true ) );

        // Bail if items per loop not correctly configured
        if( $items_per_loop <= 0 ) {
            $automatorwp_run_automation_error = sprintf( __( '%s per loop needs to be higher than 0.', 'automatorwp' ), $this->args['labels']['plural'] );
            return false;
        }

        $trigger = null;
        $trigger_options = array();

        if( $this->args['required_trigger'] !== '' ) {
            // Get the required trigger
            $trigger = automatorwp_get_automation_trigger_by_type( $automation->id, $this->args['required_trigger'] );

            // Bail if trigger not found
            if( ! $trigger ) {
                $automatorwp_run_automation_error = __( 'Trigger configuration not found.', 'automatorwp' );
                return false;
            }

            // Get the trigger stored options
            $trigger_options = automatorwp_get_trigger_stored_options( $trigger->id );

        }

        // Update automation status to in progress
        if( $automation->status !== 'in-progress' ) {
            // Call the automation run started function
            automatorwp_run_automation_started( $automation, $trigger, $trigger_options );
        }

        // Get the items ids to run the actions
        $items_ids = $this->get_items_ids( $automation, $trigger );

        if( $items_ids === false ) {
            return false;
        }

        $this->process_items( $items_ids, $automation, $trigger, $trigger_options );

        return true;

    }

}