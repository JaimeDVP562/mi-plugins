<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function gamipress_llms_points_gateway_register_gateways( $gateways ) {
    if ( ! function_exists( 'gamipress_get_points_types' ) ) return $gateways;

    $points_types = gamipress_get_points_types();

    foreach ( $points_types as $slug => $points_type ) {
        $class_name = 'GamiPress_LLMS_Points_Gateway_' . str_replace('-', '_', $slug);

        if ( ! class_exists( $class_name ) ) {
            eval( "
                class $class_name extends GamiPress_LLMS_Points_Gateway {
                    public function __construct() {
                        parent::__construct( '$slug' );
                    }
                }
            " );
        }
        
        $gateways[] = $class_name;
    }

    return $gateways;
}
add_filter( 'lifterlms_payment_gateways', 'gamipress_llms_points_gateway_register_gateways' );