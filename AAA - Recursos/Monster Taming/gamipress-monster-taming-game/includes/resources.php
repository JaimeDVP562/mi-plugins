<?php
/**
 * Resources
 *
 * @package     GamiPress\Monster_Taming_Game\Resources
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Check if add-on license is valid
 *
 * @since 1.0.0
 *
 * @return bool
 */
function gamipress_monster_taming_game_is_license_valid() {

    return true;

    $license_key = gamipress_get_option( 'gamipress_monster_taming_game_license', '' );
    $license_status = rgc_cmb2_edd_license_status( $license_key );

    $valid = ( $license_status === 'valid' );

    if( ! $valid ) gamipress_monster_taming_game_update_resources_version( '' );

    return $valid;
}

/**
 * Get stored resources version
 *
 * @since 1.0.0
 *
 * @return string
 */
function gamipress_monster_taming_game_get_resources_version() {

    // Get stored version
    if( gamipress_is_network_wide_active() ) {
        $version = get_site_option( 'gamipress_monster_taming_game_resources', '' );
    } else {
        $version = get_option( 'gamipress_monster_taming_game_resources', '' );
    }

    return $version;

}

/**
 * Update stored resources version
 *
 * @since 1.0.0
 *
 * @param string $version
 */
function gamipress_monster_taming_game_update_resources_version( $version ) {

    // Update stored version
    if( gamipress_is_network_wide_active() ) {
        update_site_option( 'gamipress_monster_taming_game_resources', $version );
    } else {
        update_option( 'gamipress_monster_taming_game_resources', $version );
    }

}

/**
 * Check if resources should be updated
 *
 * @since 1.0.0
 *
 * @return bool
 */
function gamipress_monster_taming_game_should_update_resources() {

    $version = gamipress_monster_taming_game_get_resources_version();

    // if version does not matches then requires update
    return version_compare( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_VER, $version, '!=' );

}

/**
 * Check if resources exists
 *
 * @since 1.0.0
 *
 * @return bool
 */
function gamipress_monster_taming_game_resources_exists() {

    $wp_filesystem = gamipress_monster_taming_game_get_filesystem();

    // Assets not found, requires download
    if( ! $wp_filesystem->is_dir( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR ) ) {
        return false;
    }

    // Database sheet not found, requires download
    if( ! $wp_filesystem->exists( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . 'database.csv' ) ) {
        return false;
    }

    return true;

}

/**
 * Helper function to get the WP Filesystem
 *
 * @since 1.0.0
 *
 * @return WP_Filesystem_Base
 */
function gamipress_monster_taming_game_get_filesystem() {

    global $wp_filesystem;

    if ( ! $wp_filesystem ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        WP_Filesystem();
    }

    return $wp_filesystem;

}

/**
 * API URL
 *
 * @since 1.0.0
 *
 * @return string
 */
function gamipress_monster_taming_game_get_api_url() {
    return 'https://gamipress.com/';
}

/**
 * API filename
 *
 * @since 1.0.0
 *
 * @return string
 */
function gamipress_monster_taming_game_get_api_filename() {
    return 'gamipress-monster-taming-game-resources.zip';
}

/**
 * Attempt to download a remote file attachment
 *
 * @since 1.0.0
 *
 * @return true|WP_Error true on success, WP_Error otherwise
 */
function gamipress_monster_taming_game_fetch_resources() {

    if( ! gamipress_monster_taming_game_is_license_valid() ) {
        return new WP_Error( 'licensing_error', __('Invalid URL', 'gamipress') );
    }

    $file_name = gamipress_monster_taming_game_get_api_filename();
    $url = gamipress_monster_taming_game_get_api_url() . md5( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_VER );

    if( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
        return new WP_Error( 'url_error', __('Invalid license', 'gamipress') );
    }

    $wp_filesystem = gamipress_monster_taming_game_get_filesystem();

    // get placeholder file in the upload dir with a unique, sanitized filename
    $upload = wp_upload_bits( $file_name, 0, '', null );

    if ( $upload['error'] )
        return new WP_Error( 'upload_dir_error', $upload['error'] );

    // fetch the remote url and write it to the placeholder file
    $remote_response = wp_safe_remote_get( $url, array(
        'timeout' => 300,
        'stream' => true,
        'filename' => $upload['file'],
    ) );

    $headers = wp_remote_retrieve_headers( $remote_response );

    // request failed
    if ( ! $headers ) {
        $wp_filesystem->delete( $upload['file'] );
        return new WP_Error( 'import_file_error', __('Remote server did not respond', 'gamipress') );
    }

    $remote_response_code = wp_remote_retrieve_response_code( $remote_response );

    // make sure the fetch was successful
    if ( $remote_response_code != '200' ) {
        $wp_filesystem->delete( $upload['file'] );
        return new WP_Error( 'import_file_error', sprintf( __('Remote server returned error response %1$d %2$s', 'gamipress'), esc_html( $remote_response_code ), get_status_header_desc($remote_response_code) ) );
    }

    $filesize = filesize( $upload['file'] );

    if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
        $wp_filesystem->delete( $upload['file'] );
        return new WP_Error( 'import_file_error', __('Remote file is incorrect size', 'gamipress') );
    }

    if ( 0 == $filesize ) {
        $wp_filesystem->delete( $upload['file'] );
        return new WP_Error( 'import_file_error', __('Zero size file downloaded', 'gamipress') );
    }

    // To verify the file type
    $file_verify = wp_check_filetype_and_ext( $upload['file'], $file_name );

    // To check extension
    if ( $file_verify['proper_filename_change_key'] ) {
        $wp_filesystem->delete( $upload['file'] );
        return new WP_Error( 'security_error', __('The contents of the file do not match its extension.', 'gamipress') );
    }

    // To check mime type
    if ( $file_verify['type'] !== 'application/zip' ) {
        $wp_filesystem->delete( $upload['file'] );
        return new WP_Error( 'security_error', __('File type not allowed.', 'gamipress') );
    }

    if( ! gamipress_monster_taming_game_is_license_valid() ) {
        $wp_filesystem->delete( $upload['file'] );
        return new WP_Error( 'licensing_error', __('Invalid URL', 'gamipress') );
    }

    // If another version exists, remove it
    if( $wp_filesystem->is_dir( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR ) ) {
        $wp_filesystem->rmdir( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR );
    }

    // Ensure that the upload directory exists
    if( ! $wp_filesystem->is_dir( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR ) ) {
        $wp_filesystem->mkdir( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR, 755, true );
    }

    // Add an index.php file to prevent directory browsing
    if ( ! $wp_filesystem->exists( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . 'index.php' ) ) {
        $wp_filesystem->put_contents( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . 'index.php', '<?php // Silence is golden and GamiPress is the best plugin ever!' );
    }

    // Copy the downloaded file
    $wp_filesystem->copy( $upload['file'], GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . $file_name );

    // Delete temporal file
    $wp_filesystem->delete( $upload['file'] );

    unzip_file( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . $file_name, GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR );

    $wp_filesystem->delete( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_DIR . $file_name );

    gamipress_monster_taming_game_update_resources_version( GAMIPRESS_MONSTER_TAMING_GAME_RESOURCES_VER );

    return true;

}