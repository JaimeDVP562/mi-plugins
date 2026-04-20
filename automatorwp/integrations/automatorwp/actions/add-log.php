<?php
/**
 * Add Log
 *
 * @package     AutomatorWP\Integrations\AutomatorWP\Actions\Add_Log
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_AutomatorWP_Add_Log extends AutomatorWP_Integration_Action {


    /**
     * Log title
     *
     * @since 1.0.0
     *
     * @var string $log_title
     */
    public $log_title = '';

    /**
     * Initialize the action
     *
     * @since 1.0.0
     */
    public function __construct( $integration ) {

        $this->integration = $integration;
        $this->action = $integration . '_add_log';

        parent::__construct();

    }

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add log entry', 'automatorwp' ),
            'select_option'     => __( 'Add <strong>log</strong> entry', 'automatorwp' ),
            /* translators: %1$s: URL. */
            'edit_label'        => sprintf( __( 'Add %1$s entry', 'automatorwp' ), '{log}' ),
            /* translators: %1$s: URL. */
            'log_label'         => sprintf( __( 'Add %1$s entry', 'automatorwp' ), '{log}' ),
            'options'           => array(
                'log' => array(
                    //'from' => 'log',
                    'default' => __( 'log', 'automatorwp' ),
                    'fields' => array(
                        'log_title' => array(
                            'name' => __( 'Log Title:', 'automatorwp' ),
                            'desc' => __( 'The log title.', 'automatorwp' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                        'log_fields' => array(
                            'name' => __( 'Log Custom Fields:', 'automatorwp' ),
                            'desc' => __( 'Add the extra fields of your choice to display them on the log entry.', 'automatorwp' ),
                            'type' => 'group',
                            'classes' => 'automatorwp-fields-table',
                            'options'     => array(
                                'add_button'        => __( 'Add field', 'automatorwp' ),
                                'remove_button'     => '<span class="dashicons dashicons-no-alt"></span>',
                            ),
                            'fields' => array(
                                'field' => array(
                                    'name' => __( 'Field:', 'automatorwp' ),
                                    'type' => 'text',
                                    'default' => ''
                                ),
                                'value' => array(
                                    'name' => __( 'Value:', 'automatorwp' ),
                                    'type' => 'text',
                                    'default' => ''
                                ),
                            ),
                        ),
                    )
                )
            ),
        ) );

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
    public function execute( $action, $user_id, $action_options, $automation ) {

        $this->log_title = $action_options['log_title'];

        $this->result = __( 'Log entry added successfully.', 'automatorwp' );

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log data
        add_filter( 'automatorwp_parse_automation_item_log_label', array( $this, 'log_label' ), 10, 5 );
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Action custom log label
     *
     * @since 1.0.0
     *
     * @param string    $label The edit label
     * @param stdClass $object The trigger/action object
     * @param string $item_type The item type (trigger|action)
     * @param string $context The context this function is executed
     * @param array $type_args The type parameters
     *
     * @return string
     */
    public function log_label( $label, $object, $item_type, $context, $type_args ) {

        if( $item_type !== 'action' ) {
            return $label;
        }

        // Bail if action type don't match this action
        if( $object->type !== $this->action ) {
            return $label;
        }

        if( empty( $this->log_title ) ) {
            return $label;
        }

        return $this->log_title;
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
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_fields = array();

        if( is_array( $action_options['log_fields'] ) ) {
            foreach( $action_options['log_fields'] as $field ) {
                $log_fields[$field['field']] = $field['value'];
            }
        }

        $log_meta['log_title'] = $action_options['log_title'];
        $log_meta['log_fields'] = $log_fields;
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
    public function log_fields( $log_fields, $log, $object ) {

        // Bail if log is not assigned to an action
        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['log_fields'] = array(
            'name' => __( 'Log Custom Fields:', 'automatorwp' ),
            'desc' => __( 'The log custom fields.', 'automatorwp' ),
            'type' => 'text',
        );

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_AutomatorWP_Add_Log( 'automatorwp' );
new AutomatorWP_AutomatorWP_Add_Log( 'code' );