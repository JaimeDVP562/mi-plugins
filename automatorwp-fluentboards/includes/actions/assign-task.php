<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Assign task
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Actions\Assign_Task
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
class AutomatorWP_FluentBoards_Assign_Task extends AutomatorWP_Integration_Action {
    public $integration = 'fluentboards';
    public $action      = 'fluentboards_assign_task';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Assign task in FluentBoards', 'automatorwp-fluentboards' ),
            'select_option' => __( 'Assign <strong>task</strong> in FluentBoards', 'automatorwp-fluentboards' ),
            'edit_label'    => __( 'Assign task %1$s', 'automatorwp-fluentboards' ),
            'log_label'     => __( 'Assign task %1$s', 'automatorwp-fluentboards' ),
            'fields' => array(
                'task_id' => array(
                    'name'       => __( 'Task:', 'automatorwp-fluentboards' ),
                    'type'       => 'select',
                    'options_cb' => 'automatorwp_fluentboards_get_tasks',
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
        $this->result = __( 'Task assigned (demo).', 'automatorwp-fluentboards' );
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
    new AutomatorWP_FluentBoards_Assign_Task();
});
