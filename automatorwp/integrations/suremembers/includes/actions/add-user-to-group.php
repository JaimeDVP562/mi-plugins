<?php
/**
 * Add user to a group
 *
 * @package     AutomatorWP\Integrations\SureMembers\Actions\Add_User_To_Group
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_SureMembers_Add_User_To_Group extends AutomatorWP_Integration_Action {

    public $integration = 'suremembers';
    public $action = 'suremembers_add_user_to_group';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this-> action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add user to a group', 'automatorwp' ),
            'select_option'     => __( 'Add a user to <strong>a group</strong>', 'automatorwp' ),
            /* translators: %1$s: Membership. */
            'edit_label'        => sprintf( __( 'Add %1$s to %2$s', 'automatorwp' ), '{user}', '{group}' ),
            /* translators: %1$s: Membership. */
            'log_label'         => sprintf( __( 'Add %1$s to %2$s', 'automatorwp' ), '{user}', '{group}' ),
            'options'           => array(
                'user' => array(
                    'from' => 'user',
                    'default' => __( 'user', 'automatorwp' ),
                    'fields' => array(
                        'user' => array(
                            'name' => __( 'User ID:', 'automatorwp' ),
                            'desc' => __( 'User ID that will get added to the membership. Leave blank to add the membership to the user that completes the automation.', 'automatorwp-memberpress' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                    )
                ),
                'group' => array(
                    'from' => 'group',
                    'default' => __('group', 'automatorwp'),
                    'fields' => array(
                        'group' => automatorwp_utilities_post_field( array(
                            'name' => __( 'Group:', 'automatorwp' ),
                            'post_type' => 'wsm_access_group',
                            'placeholder'           => __( 'Select a group', 'automatorwp' ),
                            'option_none_label'     => __( 'any group', 'automatorwp' )
                        ) ),
                    )
                )
            )
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

        //Shorthand
        $group_id = $action_options['group'];
        $user_id_to_apply = absint( $action_options['user'] );

        if( $user_id_to_apply === 0 ) {
            $user_id_to_apply = $user_id;
        }

        // Bail if group doesn't exists
        if( ! $group_id ) {
            return;
        }

        $access = new SureMembers\Inc\Access;
        $access->grant( $user_id, $group_id );

    }

}

new AutomatorWP_SureMembers_Add_User_To_Group();