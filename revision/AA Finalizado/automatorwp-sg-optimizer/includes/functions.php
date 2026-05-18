<?php
/**
 * Functions
 *
 * @package     AutomatorWP\Integrations\SGOptimizer\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Purge all SG Optimizer caches
 *
 * @since 1.0.0
 *
 * @return bool
 */
function automatorwp_sg_optimizer_purge_all() {

    if( ! class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
        return false;
    }

    return SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();

}

/**
 * Purge SG Optimizer cache for a specific URL
 *
 * @since 1.0.0
 *
 * @param string $url
 *
 * @return bool
 */
function automatorwp_sg_optimizer_purge_url( $url ) {

    if( ! class_exists( 'SiteGround_Optimizer\Supercacher\Supercacher' ) ) {
        return false;
    }

    if( empty( $url ) ) {
        return false;
    }

    return SiteGround_Optimizer\Supercacher\Supercacher::purge_cache_request( $url );

}
