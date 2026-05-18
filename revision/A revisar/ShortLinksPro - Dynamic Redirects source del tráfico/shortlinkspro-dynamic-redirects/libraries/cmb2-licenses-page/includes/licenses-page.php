<?php
/**
 * Admin Licenses Page
 *
 * @package     CMB2_Licenses_Page\Admin\Licenses
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register licenses page.
 *
 * @since  1.0.0
 *
 * @return void
 */
function cmb2_licenses_page_register_licenses_page() {

    $pages = cmb2_lp_get_pages();

    foreach ( $pages as $page => $page_args ) {

        $tabs = array();
        $boxes = array();

        $meta_boxes = array();

        // Here is where plugin register their licenses
        $meta_boxes = apply_filters( "{$page}_meta_boxes", $meta_boxes );

        $settings_page_key = str_replace( 'licenses', 'settings_licenses', $page );

        // Backward compatibility
        $meta_boxes = apply_filters( "{$settings_page_key}_meta_boxes", $meta_boxes );


        /**
         * Filter: cmb2_licenses_page_licenses_meta_boxes
         *
         * @param array     $meta_boxes
         * @param string    $page
         *
         * @return array
         */
        $meta_boxes = apply_filters( "cmb2_licenses_page_licenses_meta_boxes", $meta_boxes, $page );

        if( ! empty( $meta_boxes ) ) {

            // Loop licenses section meta boxes
            foreach( $meta_boxes as $meta_box_id => $meta_box ) {

                // Check meta box tabs
                if( isset( $meta_box['tabs'] ) && ! empty( $meta_box['tabs'] ) ) {

                    // Loop meta box tabs
                    foreach( $meta_box['tabs'] as $tab_id => $tab ) {

                        $tab['id'] = $tab_id;

                        $meta_box['tabs'][$tab_id] = $tab;

                    }

                }

                // Only add licenses meta box if has fields
                if( isset( $meta_box['fields'] ) && ! empty( $meta_box['fields'] ) ) {

                    // Loop meta box fields
                    foreach( $meta_box['fields'] as $field_id => $field ) {

                        $field['id'] = $field_id;

                        // Support for group fields
                        if( isset( $field['fields'] ) && is_array( $field['fields'] ) ) {

                            foreach( $field['fields'] as $group_field_id => $group_field ) {

                                $field['fields'][$group_field_id]['id'] = $group_field_id;

                            }

                        }

                        // Register custom update message on plugins menu
                        if( isset( $field['file'] ) && ! is_multisite() ) {

                            $plugin_file = plugin_basename( $field['file'] );

                            // Register custom plugin row for licensed plugins to update package if an active license exists
                            add_action( "after_plugin_row_$plugin_file", 'cmb2_licenses_page_license_plugin_update_row', 5, 3 );
                            add_action( "in_plugin_update_message-$plugin_file", 'cmb2_licenses_page_license_in_plugin_update_message', 10, 2 );

                        }

                        $meta_box['fields'][$field_id] = $field;

                    }

                    $meta_box['id'] = $meta_box_id;

                    $meta_box['display_cb'] = false;
                    $meta_box['admin_menu_hook'] = false;

                    $meta_box['show_on'] = array(
                        'key'   => 'options-page',
                        'value' => array( $page ),
                        'option_key' => $page_args['option_key'],
                    );

                    $box = new_cmb2_box( $meta_box );

                    $box->object_type( 'options-page' );

                    $boxes[] = $box;

                }
            }
        }

        $view_capability = $page_args['view_capability'];

        if( isset( $page_args['view_capability_cb'] ) && is_callable( $page_args['view_capability_cb'] ) ) {
            $view_capability = call_user_func( $page_args['view_capability_cb'] );
        }

        try {
            // Create the options page
            new Cmb2_Metatabs_Options( array(
                'key'      => $page_args['option_key'],
                'class'    => 'cmb2-licenses-page ' . str_replace( '_', '-', $page ) . '-licenses-page',
                'title'    => __( 'Licenses', 'cmb2-licenses-pages' ),
                'topmenu'  => $page_args['admin_parent'],
                'view_capability' => $view_capability,
                'cols'     => 1,
                'boxes'    => $boxes,
                //'tabs'     => $tabs,
                'menuargs' => array(
                    'menu_title' => __( 'Licenses', 'cmb2-licenses-pages' ),
                    'menu_slug'  => $page,
                ),
                'savetxt' => __( 'Save Changes', 'cmb2-licenses-pages' ),
                'resettxt' => false,
            ) );
        } catch ( Exception $e ) {

        }

    }

}
add_action( 'cmb2_admin_init', 'cmb2_licenses_page_register_licenses_page', 12 );

/**
 * Setup the default parameters to license meta boxes
 *
 * @since   1.0.0
 *
 * @param array $meta_boxes
 * @param string $page
 *
 * @return array
 */
function cmb2_licenses_page_licenses_meta_boxes_params( $meta_boxes, $page ) {

    $page_args = cmb2_lp_get_page_args( $page );

    // Loop settings section meta boxes
    foreach( $meta_boxes as $meta_box_id => $meta_box ) {

        // Only add settings meta box if has fields
        if( isset( $meta_box['fields'] ) && ! empty( $meta_box['fields'] ) ) {

            // Loop meta box fields
            foreach( $meta_box['fields'] as $field_id => $field ) {

                // Update edd_license fields with default parameters to the GamiPress server
                if( $field['type'] !== 'edd_license' ) {
                    continue;
                }

                // if not server provider, then add GamiPress server
                if( ! isset( $field['server'] ) ) {
                    $field['server'] = $page_args['server'];
                }

                // Check if is a GamiPress hosted plugin
                if( $field['server'] !== $page_args['server'] ) {
                    continue;
                }

                // Renew link
                $field['renew_license_link'] = $page_args['renew_license_link'];
                $field['license_management_link'] = $page_args['license_management_link'];
                $field['contact_link'] = $page_args['contact_link'];

                // Before field row hook to render some extra information
                $field['before_row'] = 'cmb2_licenses_page_license_field_before';

                // Update the field definition
                $meta_boxes[$meta_box_id]['fields'][$field_id] = $field;
                $meta_boxes[$meta_box_id]['priority'] = 'high'; // Fixes issue with CMB2 2.9.0

            }

        }

    }

    return $meta_boxes;

}
add_filter( 'cmb2_licenses_page_licenses_meta_boxes', 'cmb2_licenses_page_licenses_meta_boxes_params', 9999, 2 );

/**
 * Setup the thumbnail to license meta boxes
 *
 * @since   1.9.5
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function cmb2_licenses_page_licenses_meta_boxes_thumbnails( $meta_boxes, $page ) {

    // Check if we are on the licenses page to prevent API calls outside this page
    if( ! cmb2_lp_is_licenses_page() ) {
        return $meta_boxes;
    }

    $page_args = cmb2_lp_get_page_args( $page );

    $plugins = array();

    // Get our add-ons
    if( isset( $page_args['plugins_api_cb'] ) && is_callable( $page_args['plugins_api_cb'] ) ) {
        $plugins = call_user_func( $page_args['plugins_api_cb'] );
    }

    // Loop settings section meta boxes
    foreach( $meta_boxes as $meta_box_id => $meta_box ) {

        // Only add settings meta box if has fields
        if( isset( $meta_box['fields'] ) && ! empty( $meta_box['fields'] ) ) {

            // Loop meta box fields
            foreach( $meta_box['fields'] as $field_id => $field ) {

                // Update edd_license fields with default parameters to the GamiPress server
                if( $field['type'] !== 'edd_license' ) {
                    continue;
                }

                // if not server provider, then add GamiPress server
                if( ! isset( $field['server'] ) ) {
                    $field['server'] = $page_args['server'];
                }

                // Check if is a GamiPress hosted plugin
                if( $field['server'] !== $page_args['server'] ) {
                    continue;
                }

                // Try to find the plugin thumbnail from plugins API
                if ( ! is_wp_error( $plugins ) && isset( $field['file'] ) && ! isset( $field['thumbnail'] ) ) {

                    foreach ( $plugins as $plugin ) {

                        $slug = basename( $field['file'], '.php' );

                        if( $slug === $plugin->info->slug ) {
                            $field['thumbnail'] = $plugin->info->thumbnail;
                            // Thumbnail found so exit loop
                            break;
                        }

                    }

                }

                // Update the field definition
                $meta_boxes[$meta_box_id]['fields'][$field_id] = $field;

            }

        }

    }

    return $meta_boxes;

}
add_filter( 'cmb2_licenses_page_licenses_meta_boxes', 'cmb2_licenses_page_licenses_meta_boxes_thumbnails', 99999, 2 );

/**
 * License field thumbnail.
 *
 * @since  1.0.0
 *
 * @param  array        $field_args Current field args
 * @param  CMB2_Field   $field      Current field object
 */
function cmb2_licenses_page_license_field_before( $field_args, $field ) {

    if( isset( $field_args['thumbnail'] ) && ! empty( $field_args['thumbnail'] ) ) : ?>

        <div class="cmb2-licenses-page-license-thumbnail">
            <img src="<?php echo $field_args['thumbnail']; ?>" alt="<?php echo $field_args['item_name']; ?>">
        </div>

    <?php endif;

}

/**
 * Force package and download link update for licensed plugins.
 *
 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
 * @param array $plugin_data An array of plugin data. See get_plugin_data()
 *                             and the {@see 'plugin_row_meta'} filter for the list
 *                             of possible values.
 * @param string $status Status filter currently applied to the plugin list.
 *                             Possible values are: 'all', 'active', 'inactive',
 *                             'recently_activated', 'upgrade', 'mustuse', 'dropins',
 *                             'search', 'paused', 'auto-update-enabled', 'auto-update-disabled'.
 * @since  1.0.0
 *
 */
function cmb2_licenses_page_license_plugin_update_row( $file, $plugin_data, $status ) {

    $update_cache = get_site_transient( 'update_plugins' );

    if ( ! isset( $update_cache->response[ $file ] ) ) {
        return;
    }

    $response = $update_cache->response[ $file ];

    // If there is not a package link, then try to update it
    if ( empty( $response->package ) ) {

        // Turn plugin slug like 'plugin-slug' to 'plugin_slug'
        $slug = str_replace( '-', '_', $response->slug );

        $page = cmb2_lp_detect_page_by_prefix( $slug );

        if( $page === false ) {
            return;
        }

        $page_args = cmb2_lp_get_page_args( $page );

        // Get the stored license key
        $license = cmb2_licenses_page_get_option( $page, $slug . '_license', '' );

        // Check the license status
        $license_status = rgc_cmb2_edd_license_status( $license );

        if( $license_status === 'valid' ) {

            // Make a new request to the API to check package and download link
            $api_params = array(
                'edd_action' => 'get_version',
                'license'    => $license,
                'item_name'  => $response->name,
                'slug'       => $response->slug,
                'url'        => home_url(),
            );

            $api_request = wp_remote_post( $page_args['server'], array( 'timeout' => 15, 'sslverify' => true, 'body' => $api_params ) );

            if ( ! is_wp_error( $api_request ) ) {

                // Decode the API response
                $version_info = json_decode( wp_remote_retrieve_body( $api_request ) );

                // If package link provided, update it
                if( ! empty( $version_info->package ) ) {
                    $update_cache->response[ $file ]->package = $version_info->package;
                }

                // If download link provided, update it
                if( ! empty( $version_info->download_link ) ) {
                    $update_cache->response[ $file ]->download_link = $version_info->download_link;
                }

                // Update site transient with updated data
                set_site_transient( 'update_plugins', $update_cache );

            }

        }

    }

}

/**
 * Advice to user about invalid license keys
 *
 * @param array $plugin_data
 * @param array $response
 */
function cmb2_licenses_page_license_in_plugin_update_message( $plugin_data, $response ) {

    // Turn plugin slug like 'plugin-slug' to 'plugin_slug'
    $slug = str_replace( '-', '_', $response->slug );

    // Get the stored license key
    $license = cmb2_licenses_page_get_option( $slug . '_license', '' );

    $page = cmb2_lp_detect_page_by_prefix( $slug );

    // Check the license status
    $license_status = rgc_cmb2_edd_license_status( $license );

    if( $license_status !== 'valid' ) {

        echo '&nbsp;<strong><a href="' . esc_url( admin_url( 'admin.php?page=' . $page ) ) . '">'
            . __( 'Enter valid license key for automatic updates.', 'cmb2-licenses-pages' )
            . '</a></strong>';

    }

}

/**
 * Filter to set correct option key for our licenses
 *
 * @since 1.0.0
 *
 * @param string $option_key
 * @param CMB2 $cmb
 *
 * @return string
 */
function cmb2_licenses_page_licenses_option_key( $option_key, $cmb ) {

    if( isset( $cmb->meta_box['show_on'] ) && isset( $cmb->meta_box['show_on']['option_key'] ) ) {
        return $cmb->meta_box['show_on']['option_key'];
    }

    return $option_key;

}
add_filter( 'cmb2_edd_license_option_key', 'cmb2_licenses_page_licenses_option_key', 10, 2 );

/**
 * Before licenses form
 *
 * @since 1.0.0
 *
 * @param string $filterable
 * @param string $page
 *
 * @return string
 */
function cmb2_licenses_page_licenses_before_form( $output, $page ) {

    $pages = cmb2_lp_get_pages();

    if( ! isset( $pages[$page] ) ) {
        return $output;
    }

    $page_args = cmb2_lp_get_page_args( $page );

    if( isset( $page_args['installation_instructions'] ) && ! empty( $page_args['installation_instructions'] ) ) {

        $output .= '<em class="cmb2-licenses-page-licenses-intructions">'
            . $page_args['installation_instructions']
            . '</em>';

    }

    return $output;

}
add_filter( 'cmb2metatabs_before_form', 'cmb2_licenses_page_licenses_before_form', 10, 2 );
