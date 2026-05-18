<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Change task status
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Actions\Change_Task_Status
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
class AutomatorWP_FluentBoards_Change_Task_Status extends AutomatorWP_Integration_Action {
    public $integration = 'fluentboards';
    public $action      = 'fluentboards_change_task_status';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Change task status in FluentBoards', 'automatorwp-fluentboards' ),
            'select_option' => __( 'Change <strong>task</strong> status in FluentBoards', 'automatorwp-fluentboards' ),
            'edit_label'    => __( 'Change status of task %1$s', 'automatorwp-fluentboards' ),
            'log_label'     => __( 'Change status of task %1$s', 'automatorwp-fluentboards' ),
            'fields' => array(
                'task_id' => array(
                    'name'       => __( 'Task:', 'automatorwp-fluentboards' ),
                    'type'       => 'select',
                    'options_cb' => 'automatorwp_fluentboards_get_tasks',
                    'required'   => true,
                ),
                'status' => array(
                    'name'       => __( 'Status:', 'automatorwp-fluentboards' ),
                    'type'       => 'select',
                    'options_cb' => 'automatorwp_fluentboards_get_statuses',
                    'required'   => true,
                ),
            ),
        ));
    }

    /**
     * Execute the action
     *
     * @since 1.0.0
     *
     * @param stdClass $action
     * @param int $user_id
     * @param array $action_options
     * @param stdClass $automation
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        // Aquí iría la lógica real de cambiar el estado
        $this->result = __( 'Task status changed (demo).', 'automatorwp-fluentboards' );
    }

    /**
     * Hooks
     *
     * @since 1.0.0
     */
    public function hooks() {
        parent::hooks();
    }
}

add_action( 'automatorwp_init', function() {
    new AutomatorWP_FluentBoards_Change_Task_Status();
});

