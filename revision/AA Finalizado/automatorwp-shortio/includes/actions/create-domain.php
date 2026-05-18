<?php
/**
 * Create a new domain Action
 *
 * @package     AutomatorWP\Shortio\Actions
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Shortio_Create_Domain extends AutomatorWP_Integration_Action {

    public $integration = 'shortio';
    public $action      = 'shortio_create_domain';

    /**
     * The action result message
     * @var string 
     */
    public $result = '';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Create a new domain', 'automatorwp-shortio' ),
            'select_option' => __( 'Create a <strong>new domain</strong>', 'automatorwp-shortio' ),
            /* translators: %s: Domain name */
            'edit_label'    => sprintf( __( 'Create a new domain: %s', 'automatorwp-shortio' ), '{domain}' ),
            'log_label'     => sprintf( __( 'Create a new domain: %s', 'automatorwp-shortio' ), '{domain}' ),
            'options'       => array(
                'domain' => array(
                    'from'    => 'domain',
                    'default' => __( 'domain', 'automatorwp-shortio' ),
                    'fields'  => array(
                        'domain' => array(
                            'name'       => __( 'Domain name:', 'automatorwp-shortio' ),
                            'desc'       => __( 'The hostname you want to add to Short.io.', 'automatorwp-shortio' ),
                            'type'       => 'text',
                            'required'   => true,
                            'attributes' => array( 'placeholder' => 'example.com' ),
                        ),
                    ),
                ),
            ),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     * @param stdClass $action         The action object
     * @param int      $user_id        The user ID
     * @param array    $action_options The action's stored options
     * @param stdClass $automation     The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $domain_name = $action_options['domain'];

        if( ! automatorwp_shortio_get_api() ) {
            $this->result = __( 'Short.io integration not configured.', 'automatorwp-shortio' );
            return;
        }

        if ( empty( $domain_name ) ) {
            $this->result = __( 'Domain name is empty.', 'automatorwp-shortio' );
            return;
        }

        // Llamada a la función centralizada en functions.php
        $response = automatorwp_shortio_create_domain( $domain_name );

        if ( 200 === $response || 201 === $response ) {
            $this->result = sprintf( __( 'Domain "%s" created successfully.', 'automatorwp-shortio' ), $domain_name );
        } else {
            $this->result = sprintf( __( 'Error creating domain. API Code: %s', 'automatorwp-shortio' ), $response );
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Log meta data storage
     *
     * @since 1.0.0
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {
        if( $action->type === $this->action ) {
            $log_meta['result'] = $this->result;
        }
        return $log_meta;
    }

    /**
     * Log fields display
     *
     * @since 1.0.0
     */
    public function log_fields( $log_fields, $log, $object ) {
        if( $log->type === 'action' && $object->type === $this->action ) {
            $log_fields['result'] = array(
                'name' => __( 'Result:', 'automatorwp-shortio' ),
                'type' => 'text',
            );
        }
        return $log_fields;
    }
}

new AutomatorWP_Shortio_Create_Domain();