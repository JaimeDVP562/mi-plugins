<?php
/**
 * Trigger Base Class
 *
 * @package     AutomatorWP\LatePoint\Triggers
 * @since       1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class AutomatorWP_LatePoint_Trigger_Base extends AutomatorWP_Integration_Trigger {

    public $integration = 'latepoint';

    public function get_services_options() {
        $options = array( 'any' => __( 'any service', 'automatorwp-latepoint' ) );

        if ( class_exists( 'OsServiceModel' ) ) {
            $services_model = new OsServiceModel();
            $services = $services_model->get_results();

            if ( $services ) {
                foreach ( $services as $service ) {
                    $options[$service->id] = $service->name;
                }
            }
        }
        return $options;
    }

    protected function validate_object( $object, $property = 'id' ) {
        return is_object( $object ) && isset( $object->$property );
    }

    public function hooks() {
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );
        parent::hooks();
    }
}