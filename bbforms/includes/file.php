<?php
/**
 * File
 *
 * @package     BBForms\File
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Upload dir init
 *
 * @since 1.0.0
 */
function bbforms_init_uploads_dir() {

    $wp_filesystem = bbforms_get_filesystem();

    // Ensure that uploads directory exists
    if( ! $wp_filesystem->is_dir( BBFORMS_UPLOAD_DIR ) ) {
        wp_mkdir_p( BBFORMS_UPLOAD_DIR );
    }

    bbforms_init_uploads_htaccess_file();

}
add_action( 'bbforms_init', 'bbforms_init_uploads_dir' );

/**
 * Helper function to get the WP Filesystem
 *
 * @since 1.0.0
 *
 * @return WP_Filesystem_Base
 */
function bbforms_get_filesystem() {

    global $wp_filesystem;

    if ( ! $wp_filesystem ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        WP_Filesystem();
    }

    return $wp_filesystem;

}

/**
 * Upload htaccess file init
 *
 * @since 1.0.0
 */
function bbforms_init_uploads_htaccess_file() {

    $wp_filesystem = bbforms_get_filesystem();

    if ( ! $wp_filesystem->is_dir( BBFORMS_UPLOAD_DIR ) ) return;

    if( ! $wp_filesystem->is_writable( BBFORMS_UPLOAD_DIR ) ) return;

    // Check htaccess file
    $htaccess = path_join( BBFORMS_UPLOAD_DIR, '.htaccess' );

    if ( $wp_filesystem->exists( $htaccess ) ) {
        list( $first_line ) = (array) file( $htaccess, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

        // htaccess file found and seems correct, so exit here
        if ( $first_line === '# BEGIN BBForms' ) {
            return;
        }
    }

    // Write a new htaccess file
    $content = "# BEGIN BBForms\n";
    $content .= "<Files *>\n";
    $content .= "\tSetHandler none\n";
    $content .= "\tSetHandler default-handler\n";
    $content .= "\tRemoveHandler .cgi .php .php3 .php4 .php5 .phtml .pl .py .pyc .pyo\n";
    $content .= "\tRemoveType .cgi .php .php3 .php4 .php5 .phtml .pl .py .pyc .pyo\n";
    $content .= "</Files>\n";
    $content .= "<IfModule mod_php5.c>\n";
    $content .= "\tphp_flag engine off\n";
    $content .= "</IfModule>\n";
    $content .= "<IfModule mod_php7.c>\n";
    $content .= "\tphp_flag engine off\n";
    $content .= "</IfModule>\n";
    $content .= "<IfModule mod_php8.c>\n";
    $content .= "\tphp_flag engine off\n";
    $content .= "</IfModule>\n";
    $content .= "<IfModule headers_module>\n";
    $content .= "\tHeader set X-Robots-Tag \"noindex\"\n";
    $content .= "</IfModule>\n";
    $content .= "# END BBForms\n";
    
    $wp_filesystem->put_contents( $htaccess, $content, FS_CHMOD_FILE );

}

/**
 * Creates a random directory inside the given directory.
 *
 * @since 1.0.0
 *
 * @param string $dir The parent directory path.
 *
 * @return string The new child directory path if created, otherwise the parent.
 */
function bbforms_create_random_dir( $dir ) {
    do {
        $rand = zeroise( wp_rand(), 10 );
        $new_dir = path_join( $dir, $rand );
    } while ( file_exists( $new_dir ) );

    if ( wp_mkdir_p( $new_dir ) ) {
        return $new_dir;
    }

    return $dir;
}

/**
 * Helper function to turn 1kb or 1k into 1024 (bytes)
 *
 * @since 1.0.0
 *
 * @param string $size The file size, accepts 123, 123K or 123kb (support kb, mb, gb and tb)
 *
 * @return int The file size converted to bytes
 */
function bbforms_parse_file_size( $size ) {

    $size = trim( $size );
    $size = strtolower( $size );

    // Expect a format like 1kb or 1k
    preg_match( '/^(\d+)([kmgt]?)/', $size, $matches );

    // Turns 1kb into 1024
    if ( isset( $matches[1] ) ) {
        if ( 'k' === $matches[2] ) {
            $size = (int) $matches[1] * KB_IN_BYTES;
        } elseif ( 'm' === $matches[2] ) {
            $size = (int) $matches[1] * MB_IN_BYTES;
        } elseif ( 'g' === $matches[2] ) {
            $size = (int) $matches[1] * GB_IN_BYTES;
        } elseif ( 't' === $matches[2] ) {
            $size = (int) $matches[1] * TB_IN_BYTES;
        } else {
            $size = (int) $matches[1];
        }
    }

    return absint( $size );

}

/**
 * Converts a MIME type string to an array of corresponding file extensions.
 *
 * @since 1.0.0
 *
 * @param string $mime MIME type. Wildcard (*) supported (like image/*).
 *
 * @return array Array of file extensions (without .) for the given MIME.
 */
function bbforms_convert_mime_to_ext( $mime ) {
    $mime_types = wp_get_mime_types();

    $results = array();

    // Split MIME parts
    if ( preg_match( '%^([a-z]+)/([*]|[a-z0-9.+-]+)$%i', $mime, $matches ) ) {
        foreach ( $mime_types as $key => $val ) {
            // Exact MIME type like image/jpeg
            if ( $val === $matches[0] ) {
                $results = array_merge( $results, explode( '|', $key ) );
            }

            // Wildcard MIME type like image/*
            if ( '*' === $matches[2] && strpos( $val, $matches[1] . '/' ) === 0 ) {
                $results = array_merge( $results, explode( '|', $key ) );
            }
        }
    }

    // Ensure unique entries
    $results = array_unique( $results );

    // Ensure correct array values
    $results = array_filter( $results );

    // Remove keys
    $results = array_values( $results );

    return $results;
}