<?php

class AutomatorWP_Asgaros_Forum_Add_User_To_Group extends AutomatorWP_Integration_Action {

    public $integration = 'asgarosforum';
    public $action = 'asgarosforum_add_to_group';

    public function register() {
        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add user to a group', 'automatorwp-asgaros-forum' ),
            'select_option'     => __( 'Add user to <strong>a group</strong>', 'automatorwp-asgaros-forum' ),
            'edit_label'        => sprintf( __( 'Add user to %s', 'automatorwp-asgaros-forum' ), '{group_id}' ),
            'options'           => array(
                'group_id' => array(
                    'from'    => 'group_id',
                    'default' => __( 'any group', 'automatorwp-asgaros-forum' ),
                    'fields'  => array(
                        'group_id' => array(
                            'name'    => __( 'Group:', 'automatorwp-asgaros-forum' ),
                            'type'    => 'select',
                            'default' => 'any',
                            'options' => $this->get_asgaros_groups(),
                        ),
                    ),
                ),

            ),
        ) );
    }

    public function get_asgaros_groups() {
        global $wpdb;
        $options = array(
            'any' => __( 'Any group', 'automatorwp-asgaros-forum' )
        );
        
        $query = "
            SELECT t.term_id, t.name 
            FROM {$wpdb->prefix}terms t 
            INNER JOIN {$wpdb->prefix}term_taxonomy tt ON t.term_id = tt.term_id 
            WHERE tt.taxonomy = 'asgarosforum-usergroup'
        ";
        
        $groups = $wpdb->get_results( $query );

        if ( ! empty( $groups ) ) {
            foreach ( $groups as $group ) {
                $options[ (string) $group->term_id ] = $group->name;
            }
        }

        return $options;
    }

    public function execute( $action, $user_id, $action_options, $automation ) {
        
        $group_id = isset( $action_options['group_id'] ) ? absint( $action_options['group_id'] ) : 0;

        if ( empty( $group_id ) ) {
            return;
        }

        $current_groups = AsgarosForumUserGroups::getUserGroupsOfUser( $user_id, 'ids' );

        if ( ! is_array( $current_groups ) ) {
            $current_groups = array();
        }

        if ( ! in_array( $group_id, $current_groups ) ) {
            $current_groups[] = $group_id;
            AsgarosForumUserGroups::insertUserGroupsOfUsers( $user_id, $current_groups );
        }
    }

}

new AutomatorWP_Asgaros_Forum_Add_User_To_Group();