<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class GamiPress_LLMS_Points_Gateway extends LLMS_Payment_Gateway {

    public $points_type_slug;
    public $points_type;
    public $conversion_rate;

    public function __construct( $slug = '' ) {
        if ( empty( $slug ) ) return;

        $this->id = 'gamipress_' . $slug;
        $this->points_type_slug = $slug;
        
        $points_types = gamipress_get_points_types();
        $this->points_type = isset( $points_types[$slug] ) ? $points_types[$slug] : array( 'plural_name' => 'Points' );

        $this->title = $this->points_type['plural_name'];
        $this->admin_title = 'GamiPress - ' . $this->title; 
        $this->description = sprintf( __( 'Pay with ', 'gamipress-llms-points-gateway' ).'%s', $this->title );
        
        $this->supports = array(
            'single_payments' => true,
            'refunds'         => true,
        );

        $this->enabled = $this->get_option( 'enabled', 'no' );
        $this->conversion_rate = floatval( $this->get_option( 'conversion_rate', 100 ) );
    }

    public function get_admin_settings_fields() {
        return array(
            array(
                'type'    => 'checkbox',
                'id'      => $this->get_option_name( 'enabled' ),
                'title'   => __( 'Enable Gateway', 'gamipress-llms-points-gateway' ),
                'desc'    => __( 'Enable payment with these points.', 'gamipress-llms-points-gateway' ),
                'default' => 'no',
            ),
            array(
                'type'    => 'number',
                'id'      => $this->get_option_name( 'conversion_rate' ),
                'title'   => __( 'Conversion Rate', 'gamipress-llms-points-gateway' ),
                'desc'    => __( 'How many points are equal to 1 currency unit.', 'gamipress-llms-points-gateway' ),
                'default' => '100',
            ),
        );
    }

    public function handle_pending_order( $order, $plan, $person, $coupon = false ) {
        $user_id = $person->get_id(); 
        $total = $order->get( 'total' );
        $required_points = round( $total * $this->conversion_rate );

        if ( gamipress_get_user_points( $user_id, $this->points_type_slug ) < $required_points ) {
            llms_add_notice( __('Insufficient points. You need ','gamipress-llms-points-gateway') . $required_points . __(' points to buy this course.','gamipress-llms-points-gateway'), 'error' );
            return; 
        }

        gamipress_deduct_points_to_user( $user_id, $required_points, $this->points_type_slug, array(
            'reason' => sprintf( __( 'Purchase LifterLMS Order ', 'gamipress-llms-points-gateway' ).'#%s', $order->get( 'id' ) )
        ) );

        $order->record_transaction( array(
            'amount'             => $total,
            'source_description' => $this->title,
            'transaction_id'     => 'gp_' . uniqid('',true),
            'status'             => 'llms-txn-succeeded',
            'payment_gateway'    => $this->get_id(),
            'payment_type'       => 'single',
        ) );

        $order->set_status( 'llms-completed' );

        llms_redirect_and_exit( $order->get_view_link() );
    }

    public function process_refund( $transaction, $amount = 0, $note = '' ) {
        $order = $transaction->get_order();
        if ( ! $order ) return false;

        $user_id = $order->get( 'user_id' );
        if ( ! $user_id ) return false;

        $refund_amount = abs( floatval( $amount ) );
        $refund_points = round( $refund_amount * $this->conversion_rate );

        gamipress_award_points_to_user( $user_id, $refund_points, $this->points_type_slug, array(
            'reason' => sprintf( __( 'Refund for LifterLMS Order ', 'gamipress-llms-points-gateway' ).'#%s', $order->get( 'id' ) )
        ) );

        return 'gp_ref_' . uniqid('',true);
    }
}
