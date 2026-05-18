<?php
/**
 * Add User to Board
 *
 * @package     AutomatorWP\Integrations\FluentBoards\Actions\Add_User_To_Board
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_FluentBoards_Add_User_To_Board extends AutomatorWP_Integration_Action {
    public $integration = 'fluentboards';
    public $action      = 'fluentboards_add_user_to_board';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Add user to a FluentBoards board', 'automatorwp-fluentboards' ),
            'select_option' => __( 'Add <strong>user</strong> to FluentBoards board', 'automatorwp-fluentboards' ),
            'edit_label'    => __( 'Add user %1$s to board', 'automatorwp-fluentboards' ),
            'log_label'     => __( 'Add user %1$s to board', 'automatorwp-fluentboards' ),
            'fields' => array(
                'user_id' => array(
                    'name'       => __( 'User:', 'automatorwp-fluentboards' ),
                    'type'       => 'select',
                    'options_cb' => 'automatorwp_fluentboards_get_users',
                    'required'   => true,
                ),
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
     * @param stdClass $action         The action object
     * @param int      $user_id        The user ID
     * @param array    $action_options The action's stored options (with tags already passed)
     * @param stdClass $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {
        $data = array(
            'board_id' => isset( $action_options['board_id'] ) ? intval( $action_options['board_id'] ) : 0,
            'user_id'  => isset( $action_options['user_id'] ) ? intval( $action_options['user_id'] ) : 0,
        );
        $this->result = '';
        if ( empty( $data['board_id'] ) ) {
            $this->result = __( 'Board field is empty.', 'automatorwp-fluentboards' );
            return;
        }
        if ( empty( $data['user_id'] ) ) {
            $this->result = __( 'User field is empty.', 'automatorwp-fluentboards' );
            return;
        }
        if ( ! class_exists( '\\FluentBoards\\App\\Services\\BoardService' ) ) {
            $this->result = __( 'FluentBoards integration not configured or missing.', 'automatorwp-fluentboards' );
            return;
        }
        if ( ! class_exists( '\\FluentBoards\\App\\Models\\Board' ) ) {
            $this->result = __( 'FluentBoards Board model not found.', 'automatorwp-fluentboards' );
            return;
        }
        $board = \FluentBoards\App\Models\Board::find( $data['board_id'] );
        if ( ! $board ) {
            $this->result = __( 'Board not found.', 'automatorwp-fluentboards' );
            return;
        }
        $user = get_userdata( $data['user_id'] );
        if ( ! $user ) {
            $this->result = __( 'User not found.', 'automatorwp-fluentboards' );
            return;
        }
        $board_service = new \FluentBoards\App\Services\BoardService();
        $result = $board_service->addMembersInBoard(
            $data['board_id'],
            $data['user_id']
        );
        if ( is_array( $result ) && ! empty( $result['success'] ) ) {
            $this->result = sprintf(
                __( 'User %s (%s) added to board "%s".', 'automatorwp-fluentboards' ),
                esc_html( $user->display_name ),
                esc_html( $user->user_email ),
                esc_html( $board->title )
            );
        } else {
            $this->result = sprintf(
                __( 'Failed to add user %s (%s) to board "%s".', 'automatorwp-fluentboards' ),
                esc_html( $user->display_name ),
                esc_html( $user->user_email ),
                esc_html( $board->title )
            );
        }
    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {
        add_filter(
            'automatorwp_automation_ui_after_item_label',
            array( $this, 'configuration_notice' ),
            10,
            2
        );
        add_filter(
            'automatorwp_user_completed_action_log_meta',
            array( $this, 'log_meta' ),
            10,
            5
        );
        add_filter(
            'automatorwp_log_fields',
            array( $this, 'log_fields' ),
            10,
            3
        );
        parent::hooks();
    }

    /**
     * Display a configuration notice if FluentBoards is not active
     *
     * @since 1.0.0
     *
     * @param stdClass $object    The trigger/action object
     * @param string   $item_type The object type (trigger|action)
     */
    public function configuration_notice( $object, $item_type ) {
        if ( $item_type !== 'action' ) return;
        if ( $object->type !== $this->action ) return;
        if ( ! class_exists( '\\FluentBoards\\App\\Models\\Board' ) ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top:10px;">
                <?php _e( 'FluentBoards must be active for this action to work.', 'automatorwp-fluentboards' ); ?>
            </div>
        <?php endif;
    }

    /**
     * Add custom meta to the action log
     *
     * @since 1.0.0
     *
     * @param array     $log_meta       Log meta data
     * @param stdClass  $action         The action object
     * @param int       $user_id        The user ID
     * @param array     $action_options The action's stored options (with tags already passed)
     * @param stdClass  $automation     The action's automation object
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        if ( $action->type !== $this->action )
            return $log_meta;
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    /**
     * Add custom fields to the action log
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {
        if ( $log->type !== 'action' )
            return $log_fields;
        if ( $object->type !== $this->action )
            return $log_fields;
        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-fluentboards' ),
            'type' => 'text',
        );

        return $log_fields;
    }
}

add_action( 'automatorwp_init', function() {
    new AutomatorWP_FluentBoards_Add_User_To_Board();
});