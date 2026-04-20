<?php
/**
 * Action: Toggle Bricks Element
 *
 * @package      AutomatorWP\Bricks\Actions
 * @since        1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Bricks_Toggle_Element extends AutomatorWP_Integration_Action {

    public $integration = 'bricks';
    public $action      = 'bricks_toggle_element';
    public $result      = '';

    /**
     * Register the action
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Change visibility of a Bricks element', 'automatorwp-bricks' ),
            'select_option' => __( 'Change visibility of <strong>a Bricks element</strong>', 'automatorwp-bricks' ),
            
            /* translators: %1$s: Element ID. %2$s: Status (show/hide). */
            'edit_label'    => sprintf( __( '%2$s Bricks element %1$s', 'automatorwp-bricks' ), '{element_id}', '{status}' ),
            'log_label'     => sprintf( __( 'Changed visibility of Bricks element %1$s to %2$s', 'automatorwp-bricks' ), '{element_id}', '{status}' ),
            
            'options'       => array(
                'element_id' => array(
                    'name'          => __( 'Element ID:', 'automatorwp-bricks' ),
                    'desc'          => __( 'Enter the Bricks element ID (e.g. "brxe-abcd")', 'automatorwp-bricks' ),
                    'type'          => 'text',
                    'required'      => true,
                    'supports_tags' => true, 
                ),
                'status' => array(
                    'name'    => __( 'Status:', 'automatorwp-bricks' ),
                    'type'    => 'select',
                    'options' => array(
                        'show' => __( 'Show', 'automatorwp-bricks' ),
                        'hide' => __( 'Hide', 'automatorwp-bricks' ),
                    ),
                    'default' => 'show',
                ),
            ),
        ) );
    }

    /**
     * Action execution logic
     * * @param object $action      The action object
     * @param int    $user_id     The user ID who triggered the recipe
     * @param array  $options     The options selected in the action
     * @param object $automation  The automation object
     */
    public function execute( $action, $user_id, $options, $automation ) {

        $element_id_raw = isset( $options['element_id'] ) ? $options['element_id'] : '';
        $status         = isset( $options['status'] ) ? $options['status'] : 'show';

        $element_id = automatorwp_parse_automation_tags( $element_id_raw, $user_id, $automation->id, $action->id );

        if ( empty( $element_id ) ) {
            $this->result = __( 'Error: Element ID is empty.', 'automatorwp-bricks' );
            return;
        }

        if ( $user_id === 0 ) {
            $this->result = __( 'Error: Action requires a logged-in user.', 'automatorwp-bricks' );
            return;
        }

        update_user_meta( $user_id, "bricks_visibility_{$element_id}", $status );

        $this->result = sprintf( __( 'Visibility for %s updated to %s.', 'automatorwp-bricks' ), $element_id, $status );
    }
    
    /**
     * Setup action hooks
     */
    public function hooks() {
        
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 3 );
        
        parent::hooks();
    }

    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        if( $action->type !== $this->action ) return $log_meta;
        $log_meta['result'] = $this->result;
        return $log_meta;
    }

    public function log_fields( $log_fields, $log, $object ) {
        if( $log->type !== 'action' || $object->type !== $this->action ) return $log_fields;
        $log_fields['result'] = array( 'name' => __( 'Result', 'automatorwp-bricks' ), 'type' => 'text' );
        return $log_fields;
    }
}

new AutomatorWP_Bricks_Toggle_Element();