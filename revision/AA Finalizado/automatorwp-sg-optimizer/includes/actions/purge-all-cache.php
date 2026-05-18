<?php
/**
 * Purge All Cache
 *
 * @package     AutomatorWP\Integrations\SGOptimizer\Actions\Purge_All_Cache
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_SG_Optimizer_Purge_All_Cache extends AutomatorWP_Integration_Action {

    public $integration = 'sg_optimizer';
    public $action      = 'sg_optimizer_purge_all_cache';

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'   => $this->integration,
            'label'         => __( 'Purge all caches', 'automatorwp-sg-optimizer' ),
            'select_option' => __( 'Purge <strong>all</strong> SG Optimizer caches', 'automatorwp-sg-optimizer' ),
            'edit_label'    => __( 'Purge all SG Optimizer caches', 'automatorwp-sg-optimizer' ),
            'log_label'     => __( 'Purge all SG Optimizer caches', 'automatorwp-sg-optimizer' ),
            'options'       => array(),
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action
     * @param int       $user_id
     * @param array     $action_options
     * @param stdClass  $automation
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        $this->result = '';

        $result = automatorwp_sg_optimizer_purge_all();

        if( $result ) {
            $this->result = __( 'All caches purged successfully.', 'automatorwp-sg-optimizer' );
        } else {
            $this->result = __( 'Could not purge caches.', 'automatorwp-sg-optimizer' );
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
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta
     * @param stdClass  $action
     * @param int       $user_id
     * @param array     $action_options
     * @param stdClass  $automation
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        $log_meta['result'] = $this->result;

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields
     * @param stdClass  $log
     * @param stdClass  $object
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp-sg-optimizer' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_SG_Optimizer_Purge_All_Cache();
