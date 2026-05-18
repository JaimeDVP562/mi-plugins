<?php
/**
 * Functions
 *
 * @package     ShortLinksPro_Dynamic_Redirects\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Countries
 *
 * @since 1.0.0
 *
 * @return array
 */
function shortlinkspro_dynamic_redirects_get_countries() {
    $countries = shortlinkspro_get_countries();

    foreach( $countries as $code => $name ) {
        $countries[$code] = $code . ' - ' . $name;
    }

    return $countries;
}

/**
 * Devices
 *
 * @since 1.0.0
 *
 * @return array
 */
function shortlinkspro_dynamic_redirects_get_devices() {
    return array(
        'any' => __( 'Any', 'shortlinkspro-dynamic-redirects' ),
        'desktop'  => __( 'Desktop', 'shortlinkspro-dynamic-redirects' ),
        'smartphone'  => __( 'Phone', 'shortlinkspro-dynamic-redirects' ),
        'tablet'  => __( 'Tablet', 'shortlinkspro-dynamic-redirects' ),
        'unknown'  => __( 'Unknown', 'shortlinkspro-dynamic-redirects' ),
    );
}

/**
 * OS
 *
 * @since 1.0.0
 *
 * @return array
 */
function shortlinkspro_dynamic_redirects_get_os() {
    return array(
        'any' => __( 'Any', 'shortlinkspro-dynamic-redirects' ),
        'android' => __( 'Android', 'shortlinkspro-dynamic-redirects' ),
        'ios' => __( 'iOS', 'shortlinkspro-dynamic-redirects' ),
        'mac' => __( 'MacOS', 'shortlinkspro-dynamic-redirects' ),
        'linux' => __( 'Linux', 'shortlinkspro-dynamic-redirects' ),
        'windows' => __( 'Windows', 'shortlinkspro-dynamic-redirects' ),
        'unknown'  => __( 'Unknown', 'shortlinkspro-dynamic-redirects' ),
    );
}

/**
 * Linux OS
 *
 * @since 1.0.0
 *
 * @return array
 */
function shortlinkspro_dynamic_redirects_get_linux_os() {

    return array(
        'archlinux',
        'debian',
        'fedora',
        'opensuse',
        'manjaro',
        'gentoo',
        'centos',
        'elementary-os',
        'tizen',
        'linux-mint',
        'puppy-linux',
        'ubuntu',
        'lubuntu',
        'xubuntu',
    );

}

/**
 * Browsers
 *
 * @since 1.0.0
 *
 * @return array
 */
function shortlinkspro_dynamic_redirects_get_browsers() {
    return array(
        'any' => __( 'Any', 'shortlinkspro-dynamic-redirects' ),
        'android' => __( 'Android Browser', 'shortlinkspro-dynamic-redirects' ),
        'kindle' => __( 'Amazon Kindle', 'shortlinkspro-dynamic-redirects' ),
        'silk' => __( 'Amazon Silk', 'shortlinkspro-dynamic-redirects' ),
        'brave' => __( 'Brave', 'shortlinkspro-dynamic-redirects' ),
        'chrome' => __( 'Chrome', 'shortlinkspro-dynamic-redirects' ),
        'chromium' => __( 'Chromium', 'shortlinkspro-dynamic-redirects' ),
        'edge' => __( 'Edge', 'shortlinkspro-dynamic-redirects' ),
        'firefox' => __( 'Firefox', 'shortlinkspro-dynamic-redirects' ),
        'hermes' => __( 'Hermes', 'shortlinkspro-dynamic-redirects' ),
        'iceweasel' => __( 'Iceweasel', 'shortlinkspro-dynamic-redirects' ),
        'internet-explorer' => __( 'Internet Explorer', 'shortlinkspro-dynamic-redirects' ),
        'jsdom' => __( 'jsDom', 'shortlinkspro-dynamic-redirects' ),
        'k-meleon' => __( 'K-Meleon', 'shortlinkspro-dynamic-redirects' ),
        'konqueror' => __( 'Konqueror', 'shortlinkspro-dynamic-redirects' ),
        'midori' => __( 'Midori', 'shortlinkspro-dynamic-redirects' ),
        'netscape' => __( 'NetScape', 'shortlinkspro-dynamic-redirects' ),
        'netsurf' => __( 'netSurf', 'shortlinkspro-dynamic-redirects' ),
        'opera' => __( 'Opera', 'shortlinkspro-dynamic-redirects' ),
        'opera-dx' => __( 'Opera DX', 'shortlinkspro-dynamic-redirects' ),
        'opera-coast' => __( 'Opera Coast', 'shortlinkspro-dynamic-redirects' ),
        'otter' => __( 'Otter', 'shortlinkspro-dynamic-redirects' ),
        'phantomjs' => __( 'PhantomJS', 'shortlinkspro-dynamic-redirects' ),
        'phoenix-firebird' => __( 'Phoenix Firebird', 'shortlinkspro-dynamic-redirects' ),
        'qutebrowser' => __( 'qutebrowser', 'shortlinkspro-dynamic-redirects' ),
        'rekonq' => __( 'Rekonq', 'shortlinkspro-dynamic-redirects' ),
        'safari' => __( 'Safari', 'shortlinkspro-dynamic-redirects' ),
        'safari-ios' => __( 'Safari iOS', 'shortlinkspro-dynamic-redirects' ),
        'servo' => __( 'Servo', 'shortlinkspro-dynamic-redirects' ),
        'spidermonkey' => __( 'SpiderMonkey', 'shortlinkspro-dynamic-redirects' ),
        'surf' => __( 'Surf', 'shortlinkspro-dynamic-redirects' ),
        'uc' => __( 'UC', 'shortlinkspro-dynamic-redirects' ),
        'v8' => __( 'V8', 'shortlinkspro-dynamic-redirects' ),
        'vivaldi' => __( 'Vivaldi', 'shortlinkspro-dynamic-redirects' ),
        'unknown'  => __( 'Unknown', 'shortlinkspro-dynamic-redirects' ),
    );
}