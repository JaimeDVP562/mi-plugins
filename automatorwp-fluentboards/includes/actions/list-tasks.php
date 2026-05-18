<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * List tasks
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Actions\List_Tasks
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
class AutomatorWP_FluentBoards_List_Tasks extends AutomatorWP_Integration_Action {
    public $integration = 'fluentboards';
    public $action      = 'fluentboards_list_tasks';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'List tasks in FluentBoards', 'automatorwp-fluentboards' ),
            'select_option' => __( 'List <strong>tasks</strong> in FluentBoards', 'automatorwp-fluentboards' ),
            'edit_label'    => __( 'List tasks for board %1$s', 'automatorwp-fluentboards' ),
            'log_label'     => __( 'List tasks for board %1$s', 'automatorwp-fluentboards' ),
            'fields' => array(
                'board_id' => array(
                    'name'       => __( 'Board:', 'automatorwp-fluentboards' ),
                    'type'       => 'select',
                    'options_cb' => 'automatorwp_fluentboards_get_boards',
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
        $this->result = __( 'Tasks listed (demo).', 'automatorwp-fluentboards' );
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
    new AutomatorWP_FluentBoards_List_Tasks();
});
