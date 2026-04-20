<?php
class Google_Groups_Client_Mock {

    private $option_key = 'automatorwp_googlegroups_mock_state';

    private function load_state() {
        $state = get_option( $this->option_key, array() );
        if ( ! isset( $state['groups'] ) ) {
            $state['groups'] = array(
                array( 'email' => 'staff@local.test', 'name' => 'Staff' ),
                array( 'email' => 'students@local.test', 'name' => 'Students' ),
            );
            $state['members'] = array(); // keyed by group email -> array of member emails
            update_option( $this->option_key, $state );
        }
        return $state;
    }

    private function save_state( $state ) {
        update_option( $this->option_key, $state );
    }

    public function list_groups( $query = '' ) {
        $state = $this->load_state();
        if ( empty( $query ) ) {
            return $state['groups'];
        }
        $q = strtolower( $query );
        return array_values( array_filter( $state['groups'], function( $g ) use ( $q ) {
            return false !== strpos( strtolower( $g['email'] . ' ' . $g['name'] ), $q );
        } ) );
    }

    public function add_member( $group_email, $member_email ) {
        $state = $this->load_state();
        if ( ! isset( $state['members'][ $group_email ] ) ) {
            $state['members'][ $group_email ] = array();
        }
        // member entry includes role by default MEMBER
        foreach( $state['members'][ $group_email ] as $m ) {
            if ( isset( $m['email'] ) && $m['email'] === $member_email ) {
                return array( 'status' => 'exists' );
            }
        }
        $state['members'][ $group_email ][] = array( 'email' => $member_email, 'role' => 'MEMBER' );
        $this->save_state( $state );
        return array( 'status' => 'added' );
    }

    public function remove_member( $group_email, $member_email ) {
        $state = $this->load_state();
        if ( empty( $state['members'][ $group_email ] ) ) {
            return array( 'status' => 'not_found' );
        }
        foreach ( $state['members'][ $group_email ] as $i => $m ) {
            if ( isset( $m['email'] ) && $m['email'] === $member_email ) {
                array_splice( $state['members'][ $group_email ], $i, 1 );
                $this->save_state( $state );
                return array( 'status' => 'removed' );
            }
        }
        return array( 'status' => 'not_found' );
    }

    // ----------------------------------------------------------
    // Additional stubs for new actions/triggers
    // ----------------------------------------------------------

    public function change_member_role( $group_email, $member_email, $role ) {
        $state = $this->load_state();
        if ( empty( $state['members'][ $group_email ] ) ) {
            return array( 'status' => 'not_found' );
        }
        foreach ( $state['members'][ $group_email ] as &$m ) {
            if ( isset( $m['email'] ) && $m['email'] === $member_email ) {
                $m['role'] = $role;
                $this->save_state( $state );
                return array( 'status' => 'role_changed' );
            }
        }
        return array( 'status' => 'not_found' );
    }

    public function create_group( $email, $name = '' ) {
        $state = $this->load_state();
        foreach ( $state['groups'] as $g ) {
            if ( $g['email'] === $email ) {
                return array( 'status' => 'exists' );
            }
        }
        $state['groups'][] = array( 'email' => $email, 'name' => $name );
        $this->save_state( $state );
        return array( 'status' => 'created' );
    }

    public function delete_group( $email ) {
        $state = $this->load_state();
        foreach ( $state['groups'] as $i => $g ) {
            if ( $g['email'] === $email ) {
                array_splice( $state['groups'], $i, 1 );
                unset( $state['members'][ $email ] );
                $this->save_state( $state );
                return array( 'status' => 'deleted' );
            }
        }
        return array( 'status' => 'not_found' );
    }

    public function update_group( $email, $data ) {
        $state = $this->load_state();
        foreach ( $state['groups'] as &$g ) {
            if ( $g['email'] === $email ) {
                if ( isset( $data['name'] ) ) {
                    $g['name'] = $data['name'];
                }
                // other settings ignored
                $this->save_state( $state );
                return array( 'status' => 'updated' );
            }
        }
        return array( 'status' => 'not_found' );
    }

    public function remove_all_members( $group_email ) {
        $state = $this->load_state();
        $state['members'][ $group_email ] = array();
        $this->save_state( $state );
        return array( 'status' => 'emptied' );
    }

    public function send_message( $group_email, $subject, $message ) {
        // just pretend messages are queued
        return array( 'status' => 'sent' );
    }

    public function set_topic( $group_email, $topic ) {
        // no real effect
        return array( 'status' => 'updated' );
    }

    public function list_members( $group_email ) {
        $state = $this->load_state();
        $out = array();
        if ( isset( $state['members'][ $group_email ] ) ) {
            foreach ( $state['members'][ $group_email ] as $m ) {
                // $m may be string or array depending on previous versions
                if ( is_array( $m ) ) {
                    $out[] = $m;
                } else {
                    $out[] = array( 'email' => $m, 'role' => 'MEMBER' );
                }
            }
        }
        return $out;
    }

    public function reset() {
        delete_option( $this->option_key );
    }
}
