<?php
/**
 * Admin Add-ons Page
 *
 * @package     ShortLinksPro\Admin\Add_ons
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add-ons page
 *
 * @since  1.0.0
 *
 * @return void
 */
function shortlinkspro_add_ons_page() {

    if( ! function_exists( 'plugins_api' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    }

    wp_enqueue_script( 'plugin-install' );
    add_thickbox();
    wp_enqueue_script( 'updates' );

    ?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"></div>
        <h1 class="wp-heading-inline"><?php _e( 'ShortLinks Pro Add-ons', 'shortlinkspro' ); ?></h1>
        <hr class="wp-header-end">

        <p><?php _e( 'Add-ons to extend and expand the functionality of ShortLinks Pro.', 'shortlinkspro' ); ?></p>

        <form id="plugin-filter" method="post">
            <div class="wp-list-table widefat shortlinkspro-add-ons">

            <?php

            $plugins = shortlinkspro_plugins_api();

            if ( is_wp_error( $plugins ) ) {
                echo $plugins->get_error_message();
                return;
            }

            foreach ( $plugins as $plugin ) {

                if ( ! str_contains($plugin->info->slug, '-pass')) {
                    shortlinkspro_render_plugin_card( $plugin );
                }              

            }

            ?>
            </div>
        </form>
    </div>
    <?php
}

/**
 * Return an array of all installed plugins slugs
 *
 * @since  1.0.0
 *
 * @return array
 */
function shortlinkspro_get_installed_plugins_slugs() {

    $slugs = array();

    $plugin_info = get_site_transient( 'update_plugins' );

    if ( isset( $plugin_info->no_update ) ) {
        foreach ( $plugin_info->no_update as $plugin ) {
            $slugs[] = $plugin->slug;
        }
    }

    if ( isset( $plugin_info->response ) ) {
        foreach ( $plugin_info->response as $plugin ) {
            $slugs[] = $plugin->slug;
        }
    }

    return $slugs;

}

/**
 * Helper function to render a plugin card from the add-ons page
 *
 * @since  1.0.0
 *
 * @param stdClass $plugin
 *
 * @return void
 */
function shortlinkspro_render_plugin_card( $plugin ) {

    // Plugin title
    $name = $plugin->info->title;

    // Plugin slug
    $slug = property_exists( $plugin, 'wp_info' ) ? $plugin->wp_info->slug : 'shortlinkspro-' . $plugin->info->slug;

    // Available actions for this plugin
    $action_links = array();

    $details_link = esc_url( 'https://shortlinkspro.com/add-ons/' . $plugin->info->slug );

    if( property_exists( $plugin, 'wp_info' ) ) {
        // Free add-ons

        $class = 'shortlinkspro-free-add-on';

        // Check plugin status
        if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
            $status = install_plugin_install_status( $plugin->wp_info );

            switch ( $status['status'] ) {
                case 'install':
                    if ( $status['url'] ) {
                        $action_links[] = '<a class="install-now button" data-slug="' . esc_attr( $slug ) . '" href="' . esc_url( $status['url'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Install %s now' ), $name ) ) . '" data-name="' . esc_attr( $name ) . '">' . __( 'Install Now' ) . '</a>';
                    }
                    break;

                case 'update_available':
                    if ( $status['url'] ) {
                        $action_links[] = '<a class="update-now button aria-button-if-js" data-plugin="' . esc_attr( $status['file'] ) . '" data-slug="' . esc_attr( $slug ) . '" href="' . esc_url( $status['url'] ) . '" aria-label="' . esc_attr( sprintf( __( 'Update %s now' ), $name ) ) . '" data-name="' . esc_attr( $name ) . '">' . __( 'Update Now' ) . '</a>';
                    }
                    break;

                case 'latest_installed':
                case 'newer_installed':
                    if ( is_plugin_active( $status['file'] ) ) {
                        $action_links[] = '<button type="button" class="button button-disabled" disabled="disabled">' . _x( 'Active', 'plugin' ) . '</button>';
                    } elseif ( current_user_can( 'activate_plugins' ) ) {
                        $button_text  = __( 'Activate' );
                        $button_label = _x( 'Activate %s', 'plugin' );
                        $activate_url = add_query_arg( array(
                            '_wpnonce'    => wp_create_nonce( 'activate-plugin_' . $status['file'] ),
                            'action'      => 'activate',
                            'plugin'      => $status['file'],
                        ), network_admin_url( 'plugins.php' ) );

                        if ( is_network_admin() ) {
                            $button_text  = __( 'Network Activate' );
                            $button_label = _x( 'Network Activate %s', 'plugin' );
                            $activate_url = add_query_arg( array( 'networkwide' => 1 ), $activate_url );
                        }

                        $action_links[] = sprintf(
                            '<a href="%1$s" class="button activate-now" aria-label="%2$s">%3$s</a>',
                            esc_url( $activate_url ),
                            esc_attr( sprintf( $button_label, $name ) ),
                            esc_html( $button_text )
                        );
                    } else {
                        $action_links[] = '<button type="button" class="button button-disabled" disabled="disabled">' . _x( 'Installed', 'plugin' ) . '</button>';
                    }
                    break;
            }
        }
    } else {
        // Premium add-ons

        $class = 'shortlinkspro-premium-add-on';

        $plugin_file = $slug . '/' . $slug . '.php';

        // If is installed
        if ( is_dir( WP_PLUGIN_DIR . '/' . $slug ) ) {

            // If is active
            if ( is_plugin_active( $slug . '/' . $slug . '.php' ) ) {

                // If has licensing enabled
                if( $plugin->licensing->enabled ) {

                    // Plugin installed and active, so field should be registered
                    $field = cmb2_get_field( $slug . '-license', str_replace( '-', '_', $slug ) . '_license', 'shortlinkspro_settings', 'options-page' );

                    if( $field ) {
                        $license_key = $field->escaped_value();
                        $license = false;

                        if( function_exists( 'rgc_cmb2_edd_license_data' ) ) {
                            $license = rgc_cmb2_edd_license_data( $license_key );
                        }

                        $license_status = ( $license !== false ) ? $license->license : false;

                        if( $license_status !== 'valid' ) {
                            // "Activate License" action
                            $action_links[] = '<a href="' . admin_url( 'admin.php?page=shortlinkspro_licenses' ) . '" class="button">' . esc_html__( 'Activate License', 'shortlinkspro' ) . '</a>';
                        } else {
                            // "Active and License Registered" action
                            $action_links[] = '<button type="button" class="button button-disabled" disabled="disabled">' . esc_html__( 'Active and License Registered', 'shortlinkspro' ) . '</button>';
                        }
                    }

                } else {
                    // "Active" action
                    $action_links[] = '<button type="button" class="button button-disabled" disabled="disabled">' . esc_html__( 'Active', 'shortlinkspro' ) . '</button>';
                }

            } else if ( current_user_can( 'activate_plugins' ) ) {
                // If not active and current user can activate plugins, then add the "Activate" action

                $button_text  = __( 'Activate' );
                $button_label = _x( 'Activate %s', 'plugin' );
                $activate_url = add_query_arg( array(
                    '_wpnonce'    => wp_create_nonce( 'activate-plugin_' . $plugin_file ),
                    'action'      => 'activate',
                    'plugin'      => $plugin_file,
                ), network_admin_url( 'plugins.php' ) );

                if ( is_network_admin() ) {
                    $button_text  = __( 'Network Activate' );
                    $button_label = _x( 'Network Activate %s', 'plugin' );
                    $activate_url = add_query_arg( array( 'networkwide' => 1 ), $activate_url );
                }

                // "Activate" action
                $action_links[] = sprintf(
                    '<a href="%1$s" class="button activate-now" aria-label="%2$s">%3$s</a>',
                    esc_url( $activate_url ),
                    esc_attr( sprintf( $button_label, $name ) ),
                    $button_text
                );
            }
        } else if( shortlinkspro_is_plugin_pass( $plugin ) ) {

            // "Get this pass" action
            $action_links[] = '<a href="https://shortlinkspro.com/add-ons/' . $plugin->info->slug . '" class="button button-primary" target="_blank">' . esc_html__( 'Get this pass', 'shortlinkspro' ) . '</a>';

        } else {

            // "Get this add-on" action
            $action_links[] = '<a href="https://shortlinkspro.com/add-ons/' . $plugin->info->slug . '" class="button button-primary" target="_blank">' . esc_html__( 'Get this add-on', 'shortlinkspro' ) . '</a>';

        }
    }

    if( ! empty( $details_link ) ) {
        // "More Details" action
        $action_links[] = '<a href="' . esc_url( $details_link ) . '" class="more-details" aria-label="' . esc_attr( sprintf( __( 'More information about %s' ), $name ) ) . '" data-title="' . esc_attr( $name ) . '" target="_blank">' . esc_html__( 'More Details' ) . '</a>';
    } ?>

    <div class="shortlinkspro-plugin-card plugin-card plugin-card-<?php echo sanitize_html_class( $slug ); ?> <?php echo $class; ?>">

        <div class="plugin-card-top">

            <div class="thumbnail column-thumbnail">
                <a href="<?php echo esc_url( $details_link ); ?>" target="_blank">
                    <img src="<?php echo esc_attr( $plugin->info->thumbnail ) ?>" class="plugin-thumbnail" alt="">
                </a>
            </div>

            <div class="name column-name">
                <h3>
                    <a href="<?php echo esc_url( $details_link ); ?>" target="_blank">
                        <?php echo esc_html( $name ); ?>
                    </a>
                </h3>
            </div>

            <div class="desc column-description">
                <p><?php echo shortlinkspro_esc_plugin_excerpt( $plugin->info->excerpt ); ?></p>
            </div>

        </div>

        <div class="plugin-card-bottom">
            <div class="action-links">
                <?php if ( $action_links ) {
                    echo '<ul class="plugin-action-buttons"><li>' . implode( '</li><li>', $action_links ) . '</li></ul>';
                } ?>
            </div>
        </div>

    </div>

    <?php
}

/**
 * Function to contact with our plugins API
 *
 * @since  1.0.0
 *
 * @return object|WP_Error Object with plugins
 */
function shortlinkspro_plugins_api() {

    // If a plugins api request has been cached already, then use cached plugins
    if ( false !== ( $res = get_transient( 'shortlinkspro_plugins_api' ) ) ) {
        return $res;
    }

    $url = $http_url = 'https://shortlinkspro.com/wp-json/api/add-ons';

    if ( $ssl = wp_http_supports( array( 'ssl' ) ) ) {
        $url = set_url_scheme( $url, 'https' );
    }

    $http_args = array(
        'timeout' => 15,
    );

    $request = wp_remote_get( $url, $http_args );

    if ( $ssl && is_wp_error( $request ) ) {
        trigger_error(
            sprintf(
                __( 'An unexpected error occurred. Something may be wrong with shortlinkspro.com or this server&#8217;s configuration. If you continue to have problems, please try to <a href="%s">contact us</a>.', 'shortlinkspro' ),
                'https://shortlinkspro.com/contact-us/'
            ) . ' ' . __( '(WordPress could not establish a secure connection to shortlinkspro.com. Please contact your server administrator.)' ),
            headers_sent() || WP_DEBUG ? E_USER_WARNING : E_USER_NOTICE
        );

        $request = wp_remote_get( $http_url, $http_args );
    }

    if ( is_wp_error( $request ) ) {
        $res = new WP_Error( 'shortlinkspro_plugins_api_failed',
            sprintf(
                __( 'An unexpected error occurred. Something may be wrong with shortlinkspro.com or this server&#8217;s configuration. If you continue to have problems, please try to <a href="%s">contact us</a>.', 'shortlinkspro' ),
                'https://shortlinkspro.com/contact-us/'
            ),
            $request->get_error_message()
        );
    } else {
        $res = json_decode( $request['body'] );

        $res = (array) $res->products;

        // Set a transient for 1 week with api plugins
        set_transient( 'shortlinkspro_plugins_api', $res, ( 24 * 7 ) * HOUR_IN_SECONDS );
    }

    return $res;

}

/**
 * Escape plugin description
 *
 * @since 1.0.0
 *
 * @param string $excerpt
 *
 * @return string
 */
function shortlinkspro_esc_plugin_excerpt( $excerpt ) {

    // To prevent execute shortcodes on website, the are double capsuled on []
    $excerpt = str_replace( '[[', '[', $excerpt );
    $excerpt = str_replace( ']]', ']', $excerpt );

    return $excerpt;

}

/**
 * Helper function to determine if give plugin has the passes category
 *
 * @since   1.0.0
 *
 * @param stdClass $plugin
 *
 * @return bool
 */
function shortlinkspro_is_plugin_pass( $plugin ) {

    // Check if plugin has categories
    if( is_array( $plugin->info->category ) && count( $plugin->info->category ) ) {

        // Loop plugin categories
        foreach( $plugin->info->category as $category ) {

            // Passes category found
            if( $category->slug === 'access-pass' ) {
                return true;
            }
        }

    }

    return false;
}