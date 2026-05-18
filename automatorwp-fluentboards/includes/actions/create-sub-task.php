<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create sub-task
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Actions\Create_Sub_Task
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
class AutomatorWP_FluentBoards_Create_Sub_Task extends AutomatorWP_Integration_Action {
    public $integration = 'fluentboards';
    public $action      = 'fluentboards_create_sub_task';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create sub-task in FluentBoards', 'automatorwp-fluentboards' ),
            'select_option' => __( 'Create <strong>sub-task</strong> in FluentBoards', 'automatorwp-fluentboards' ),
            'edit_label'    => __( 'Create sub-task %1$s', 'automatorwp-fluentboards' ),
            'log_label'     => __( 'Create sub-task %1$s', 'automatorwp-fluentboards' ),
            'fields' => array(
                'sub_task_name' => array(
                    'name'       => __( 'Sub-task name:', 'automatorwp-fluentboards' ),
                    'type'       => 'text',
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
        $this->result = __( 'Sub-task created (demo).', 'automatorwp-fluentboards' );
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
    new AutomatorWP_FluentBoards_Create_Sub_Task();
});
