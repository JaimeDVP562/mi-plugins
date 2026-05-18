<?php
/**
 * Widgets
 *
 * @package     GamiPress\Referrals\Widgets
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once GAMIPRESS_REFERRALS_DIR .'includes/widgets/affiliate-id-widget.php';
require_once GAMIPRESS_REFERRALS_DIR .'includes/widgets/referral-url-widget.php';
require_once GAMIPRESS_REFERRALS_DIR .'includes/widgets/referral-url-generator-widget.php';
require_once GAMIPRESS_REFERRALS_DIR .'includes/widgets/referrals-count-widget.php';

// Register plugin widgets
function gamipress_referrals_register_widgets() {

    register_widget( 'gamipress_affiliate_id_widget' );
    register_widget( 'gamipress_referral_url_widget' );
    register_widget( 'gamipress_referral_url_generator_widget' );
    register_widget( 'gamipress_referrals_count_widget' );

}
add_action( 'widgets_init', 'gamipress_referrals_register_widgets' );