<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Paid_Membership_Subscriptions\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Get subscription name of specified subscription id
 *
 * @since 1.0.0
 *
 * @param int $subscription_plan_id
 *
 * @return array
 */
function automatorwp_pms_get_subscription_name( $subscription_plan_id ) {

    // Empty title if no ID provided
    if( absint( $subscription_plan_id ) === 0 ) {
        return '';
    }

   $subscription_plan = pms_get_subscription_plan( $subscription_plan_id );
   $subscription_plan_name = $subscription_plan->name;

   return $subscription_plan_name;

}