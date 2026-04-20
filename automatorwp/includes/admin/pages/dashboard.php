<?php
/**
 * Admin Dashboard Page
 *
 * @package     AutomatorWP\Admin\Dashboard
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       2.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Dashboard page
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_page() {
    ?>
    <div class="wrap automatorwp-dashboard">

        <div id="icon-options-general" class="icon32"></div>
        <h1 class="wp-heading-inline"><?php esc_html_e( 'Dashboard', 'automatorwp' ); ?></h1>
        <hr class="wp-header-end">

        <div id="dashboard-widgets-wrap">
            <div id="dashboard-widgets" class="metabox-holder">

                <?php // Logo ?>
                <div class="automatorwp-dashboard-logo">
                    <img src="<?php echo AUTOMATORWP_URL . 'assets/img/automatorwp-brand-logo.svg' ?>" alt="AutomatorWP">
                    <strong class="automatorwp-dashboard-version">v<?php echo AUTOMATORWP_VER; ?></strong>
                </div>

                <?php // Dashboard ?>
                <h1><?php echo esc_html_e( 'Dashboard', 'automatorwp' ); ?></h1>

                <div id="postbox-container-1" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                        <?php // Latest Automations ?>
                        <?php automatorwp_dashboard_box( array(
                            'id' => 'latest-automations',
                            'title' => __( 'Latest Automations', 'automatorwp' ) . '<a href="' . ct_get_list_link( 'automatorwp_automations' ) . '" class="button button-primary">' . esc_html__( 'View All', 'automatorwp' ) . '</a>',
                            'content_cb' => 'automatorwp_dashboard_latest_automations_box',
                        ) ); ?>

                    </div>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                        <?php // Most Completed Automations  ?>
                        <?php automatorwp_dashboard_box( array(
                            'id' => 'most-completed-automations',
                            'title' => __( 'Most Completed Automations', 'automatorwp' ) . '<a href="' . ct_get_list_link( 'automatorwp_automations' ) . '" class="button button-primary">' . esc_html__( 'View All', 'automatorwp' ) . '</a>',
                            'content_cb' => 'automatorwp_dashboard_most_completed_automations_box',
                        ) ); ?>

                    </div>
                </div>

                <div id="postbox-container-3" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                        <?php // Latest Logs ?>
                        <?php automatorwp_dashboard_box( array(
                            'id' => 'latest-logs',
                            'title' => __( 'Latest Logs', 'automatorwp' ) . '<a href="' . ct_get_list_link( 'automatorwp_logs' ) . '" class="button button-primary">' . esc_html__( 'View All', 'automatorwp' ) . '</a>',
                            'content_cb' => 'automatorwp_dashboard_latest_logs_box',
                        ) ); ?>

                    </div>
                </div>

                <div id="postbox-container-4" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                        <?php // New Automation ?>
                        <?php automatorwp_dashboard_box( array(
                            'id' => 'new-automation',
                            'title' => __( 'New Automation', 'automatorwp' ),
                            'content_cb' => 'automatorwp_dashboard_new_automation_box',
                        ) ); ?>

                    </div>
                </div>

                <?php // Videos ?>
                <h1><?php echo esc_html_e( 'Videos', 'automatorwp' ); ?></h1>

                <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                    <?php // Videos ?>
                    <?php automatorwp_dashboard_box( array(
                        'id' => 'videos',
                        'title' => '',
                        'content_cb' => 'automatorwp_dashboard_videos_box',
                    ) ); ?>

                </div>

                <?php // Add-ons ?>
                <?php automatorwp_dashboard_add_ons_section(); ?>

                <?php // Integrations ?>
                <?php automatorwp_dashboard_integrations_section(); ?>

                <?php // Apps Integrations ?>
                <?php automatorwp_dashboard_apps_section(); ?>

                <?php // Documentation ?>
                <h1><?php echo esc_html_e( 'Documentation', 'automatorwp' ); ?></h1>

                <div id="postbox-container-1" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                        <?php // Getting started ?>
                        <?php automatorwp_dashboard_box( array(
                            'id' => 'docs',
                            'title' => __( 'Getting started', 'automatorwp' ),
                            'content_cb' => 'automatorwp_dashboard_docs_box',
                        ) ); ?>

                    </div>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                        <?php // Features ?>
                        <?php automatorwp_dashboard_box( array(
                            'id' => 'features',
                            'title' => __( 'Features', 'automatorwp' ),
                            'content_cb' => 'automatorwp_dashboard_features_box',
                        ) ); ?>

                    </div>
                </div>

                <div id="postbox-container-3" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                        <?php // Advanced Features ?>
                        <?php automatorwp_dashboard_box( array(
                            'id' => 'advanced',
                            'title' => __( 'Advanced features', 'automatorwp' ),
                            'content_cb' => 'automatorwp_dashboard_advanced_features_box',
                        ) ); ?>

                    </div>
                </div>

                <div id="postbox-container-4" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                    <?php // Support ?>
                    <?php automatorwp_dashboard_box( array(
                            'id' => 'support',
                            'title' => __( 'Support', 'automatorwp' ),
                            'content_cb' => 'automatorwp_dashboard_support_box',
                    ) ); ?>

                    </div>
                </div>

                <?php // About ?>
                <h1><?php echo esc_html_e( 'About', 'automatorwp' ); ?></h1>

                <div id="postbox-container-1" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                        <?php // Plugins ?>
                        <?php automatorwp_dashboard_box( array(
                            'id' => 'plugins',
                            'title' => __( 'Our Plugins', 'automatorwp' ),
                            'content_cb' => 'automatorwp_dashboard_plugins_box',
                        ) ); ?>

                    </div>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                        <?php // Team ?>
                        <?php automatorwp_dashboard_box( array(
                            'id' => 'team',
                            'title' => __( 'Meet the team', 'automatorwp' ),
                            'content_cb' => 'automatorwp_dashboard_team_box',
                        ) ); ?>

                    </div>
                </div>

                <div id="postbox-container-3" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                        <?php // Get involved ?>
                        <?php automatorwp_dashboard_box( array(
                            'id' => 'involved',
                            'title' => __( 'Get involved', 'automatorwp' ),
                            'content_cb' => 'automatorwp_dashboard_involved_box',
                        ) ); ?>

                    </div>
                </div>

                <div id="postbox-container-4" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                        <?php // Social ?>
                        <?php automatorwp_dashboard_box( array(
                            'id' => 'social',
                            'title' => __( 'Follow us', 'automatorwp' ),
                            'content_cb' => 'automatorwp_dashboard_social_box',
                        ) ); ?>

                    </div>
                </div>

            </div>
        </div>

    </div>
    <?php
}

/**
 * Dashboard page
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_box( $args ) {

    $args = wp_parse_args( $args, array(
        'id' => '',
        'title' => '',
        'content' => '',
        'content_cb' => '',
    ) );

    ?>
        <div id="automatorwp-dashboard-<?php echo $args['id']; ?>" class="automatorwp-dashboard-box postbox">

            <div class="postbox-header">
                <h2 class="hndle"><?php echo $args['title']; ?></h2>
            </div>

            <div class="inside">

                <?php if( is_callable( $args['content_cb'] ) ) {
                    call_user_func( $args['content_cb'] );
                } else {
                    echo $args['content'];
                } ?>

            </div>

        </div>
    <?php

}

/**
 * Dashboard plugin card
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_render_plugin_card( $plugin ) {

    $details_link = 'https://automatorwp.com/add-ons/' . $plugin->info->slug;
    $thumbnail = 'https://automatorwp.com/wp-content/themes/automatorwp-theme/assets/img/integrations/' . $plugin->info->slug . '.svg';

    $action_label = esc_html__( 'Get this add-on', 'automatorwp' );

    ?>

    <div class="automatorwp-plugin-card plugin-card plugin-card-<?php echo sanitize_html_class( $plugin->info->slug ); ?>">

        <div class="plugin-card-top cmb-tooltip">

            <div class="thumbnail column-thumbnail">
                <a href="<?php echo esc_url( $details_link ); ?>" class="open-plugin-details-modal" target="_blank">
                    <img src="<?php echo esc_attr( $thumbnail ) ?>" class="plugin-thumbnail" alt="">
                </a>
            </div>

            <div class="name column-name">
                <h3>
                    <a href="<?php echo esc_url( $details_link ); ?>" class="open-plugin-details-modal" target="_blank">
                        <?php echo $plugin->info->title; ?>
                    </a>
                </h3>
            </div>

            <div class="desc column-description cmb-tooltip-desc cmb-tooltip-top">
                <p><?php echo automatorwp_esc_plugin_excerpt( $plugin->info->excerpt ); ?></p>
            </div>

        </div>

    </div>

    <?php

}

function automatorwp_dashboard_render_automation( $automation ) {

    $title = ! empty( $automation->title ) ? $automation->title : __( '(No title)', 'automatorwp' );
    $title = '<strong><a href="' . ct_get_edit_link( 'automatorwp_automations', $automation->id ) . '">' . esc_html( $title ) . '</a></strong>';

    $types = automatorwp_get_automation_types();

    $type = '<span class="automatorwp-automation-type automatorwp-automation-type-' . esc_attr( $automation->type ) . '">';
    $type .= isset( $types[$automation->type] ) ? $types[$automation->type]['label'] : $automation->type;
    $type .= '</span>';

    $type_icon = '';

    if( isset( $types[$automation->type] ) ) {
        $type_icon = '<div class="automatorwp-integration-icon cmb-tooltip">
                            <img src="' . esc_attr( $types[$automation->type]['image'] ) . '" alt="' . esc_attr( $types[$automation->type]['label'] ) . '">
                            <span class="cmb-tooltip-desc cmb-tooltip-top">' . esc_html( $types[$automation->type]['label'] ) . '</span>
                        </div>';
    }

    $completions = ct_get_object_meta( $automation->id, 'completions', true );

    if( empty( $completions ) ) {
        $completions = automatorwp_get_object_completion_times( $automation->id, 'automation' );
        ct_update_object_meta( $automation->id, 'completions', $completions );
    }

    $completions = absint( $completions );
    $times = absint( $automation->times );

    if( $times !== 0 ) {
        $completions = $completions . '/' . $times;
    }

    $completions = '<span class="automatorwp-automation-completions cmb-tooltip ' . ( $times !== 0 && $completions >= $times ? 'automatorwp-automation-completions-completed' : '' ) . '">'
        . $completions
        . '<span class="cmb-tooltip-desc cmb-tooltip-top">' . esc_html__( 'Completions', 'automatorwp' ) . '</span>'
        . '</span>';

    $statuses = automatorwp_get_automation_statuses();
    $status = isset( $statuses[$automation->status] ) ? $statuses[$automation->status] : esc_html( $automation->status );
    $status = '<span class="automatorwp-automation-status automatorwp-automation-status-' . esc_attr( $automation->status ) . '">' . $status . '</span>';

    $time       = strtotime( $automation->date );
    $today      = date( 'Y-m-d', current_time( 'timestamp' ) );
    $yesterday  = date( 'Y-m-d', strtotime( '-1 day', current_time( 'timestamp' ) ) );

    if ( date( 'Y-m-d', $time ) === $today ) {
        $relative = __( 'Today', 'automatorwp' );
    } elseif ( date( 'Y-m-d', $time ) === $yesterday ) {
        $relative = __( 'Yesterday', 'automatorwp' );
    } elseif ( date( 'Y', $time ) !== date( 'Y', current_time( 'timestamp' ) ) ) {
        /* translators: date and time format for recent posts on the dashboard, from a different calendar year, see https://secure.php.net/date */
        $relative = date_i18n( __( 'M jS Y' ), $time );
    } else {
        /* translators: date and time format for recent posts on the dashboard, see https://secure.php.net/date */
        $relative = date_i18n( __( 'M jS' ), $time );
    }

    $date = sprintf( _x( '%1$s, %2$s', 'dashboard' ), $relative, mysql2date( get_option( 'time_format' ), $automation->date ) );
    echo '<li>'
        . '<div>'
            . '<span>' .$type_icon . '</span>'
        . '</div>'
        . '<div>'
            . '<span>' . $title . '</span>'
            . '<span>' . $completions . '</span>'
        . '</div>'
        . '<div>'
            . '<span>' . $status . '</span>'
            . '<span>' . $date . '</span>'
        . '</div>'
        . '</li>';

}

/**
 * Dashboard latest automations box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_latest_automations_box() {
    // Setup table
    ct_setup_table( 'automatorwp_automations' );

    $query = new CT_Query( array(
        'orderby'        => 'date',
        'order'          => 'DESC',
        'items_per_page' => 5,
        'no_found_rows'  => true,
        'cache_results'  => false,
    ) );

    $automations = $query->get_results();

    if ( count( $automations ) > 0 ) {

        echo '<div id="automatorwp-latest-automations" class="automatorwp-automations">';

        echo '<ul>';

        foreach ( $automations as $automation )
            automatorwp_dashboard_render_automation( $automation );

        echo '</ul>';

        echo '</div>';

    } else {
        echo '<p>' . __( 'Nothing to show :)', 'automatorwp' ) .'</p>';
    }
}

/**
 * Dashboard most completed automations box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_most_completed_automations_box() {
    global $wpdb, $ct_table;

    // Setup table
    $automations_table = AutomatorWP()->db->automations;
    $automations_meta_table = AutomatorWP()->db->automations_meta;

    $automations = $wpdb->get_results(
        "SELECT a.*
        FROM {$automations_table} AS a
        LEFT JOIN {$automations_meta_table} AS am ON ( am.id = a.id AND am.meta_key = 'completions' )
        ORDER BY CAST( am.meta_value AS UNSIGNED ) DESC
        LIMIT 5"
    );

    ct_setup_table( 'automatorwp_automations' );

    if ( count( $automations ) > 0 ) {

        echo '<div id="automatorwp-latest-automations" class="automatorwp-automations">';

        echo '<ul>';

        foreach ( $automations as $automation )
            automatorwp_dashboard_render_automation( $automation );

        echo '</ul>';

        echo '</div>';

    } else {
        echo '<p>' . __( 'Nothing to show :)', 'automatorwp' ) .'</p>';
    }
}

/**
 * Dashboard latest logs box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_latest_logs_box() {
    // Setup table
    ct_setup_table( 'automatorwp_logs' );

    $query = new CT_Query( array(
        'orderby'        => 'id',
        'order'          => 'DESC',
        'items_per_page' => 5,
        'no_found_rows'  => true,
        'cache_results'  => false,
    ) );

    $logs = $query->get_results();

    if ( count( $logs ) > 0 ) {

        echo '<div id="automatorwp-latest-logs" class="automatorwp-latest-logs">';

        echo '<ul>';

        $today    = date( 'Y-m-d', current_time( 'timestamp' ) );
        $yesterday = date( 'Y-m-d', strtotime( '-1 day', current_time( 'timestamp' ) ) );

        foreach ( $logs as $log ) {

            $user = get_userdata( $log->user_id );

            if( ! $user ) {
                $user_display_name = '';
            } else {
                $user_display_name = $user->display_name;

                if( current_user_can( 'edit_users' ) ) {
                    $user_display_name = '<a href="' . get_edit_user_link( $log->user_id ) . '">' . $user_display_name . '</a>';
                }
            }


            ob_start();
            automatorwp_get_log_integration_icon( $log );
            $log_icon = ob_get_clean();

            $log_title = ! empty( $log->title ) ? $log->title : __( '(No title)', 'automatorwp' );
            $log_title = '<strong><a href="' . ct_get_edit_link( 'automatorwp_logs', $log->id ) . '">' . $log_title . '</a></strong>';

            $types = automatorwp_get_log_types();
            $type = isset( $types[$log->type] ) ? $types[$log->type] : $log->type;

            $time = strtotime( $log->date );

            if ( date( 'Y-m-d', $time ) === $today ) {
                $relative = __( 'Today', 'automatorwp' );
            } elseif ( date( 'Y-m-d', $time ) === $yesterday ) {
                $relative = __( 'Yesterday', 'automatorwp' );
            } elseif ( date( 'Y', $time ) !== date( 'Y', current_time( 'timestamp' ) ) ) {
                /* translators: date and time format for recent posts on the dashboard, from a different calendar year, see https://secure.php.net/date */
                $relative = date_i18n( __( 'M jS Y' ), $time );
            } else {
                /* translators: date and time format for recent posts on the dashboard, see https://secure.php.net/date */
                $relative = date_i18n( __( 'M jS' ), $time );
            }

            $date = sprintf( _x( '%1$s, %2$s', 'dashboard' ), $relative, mysql2date( get_option( 'time_format' ), $log->date ) );

            echo '<li>'
                . '<div>'
                    . '<span>' . $log_icon . '</span>'
                . '</div>'
                . '<div>'
                    . '<span>' . $log_title . '</span>'
                    . '<span>' . $user_display_name . '</span>'
                . '</div>'
                . '<div>'
                    . '<span>' . $type . '</span>'
                    . '<span>' . $date . '</span>'
                . '</div>'
                . '</li>';
        }

        echo '</ul>';

        echo '</div>';

    } else {
        echo '<p>' . __( 'Nothing to show :)', 'automatorwp' ) .'</p>';
    }
}

/**
 * Dashboard new automation box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_new_automation_box() {

    $types = automatorwp_get_automation_types(); ?>

    <div class="automatorwp-automation-type-dialog">

        <div class="automatorwp-automation-types">

        <?php foreach( $types as $type => $args ) :
            if( in_array( $type, automatorwp_get_automation_loop_types() ) ) continue; ?>
            <div class="automatorwp-automation-type automatorwp-automation-type-<?php echo $type; ?>" data-type="<?php echo $type; ?>">
                <img src="<?php echo $args['image']; ?>" alt="<?php echo $args['label']; ?>">
                <div class="automatorwp-automation-type-description">
                    <strong><?php echo $args['label']; ?></strong>
                    <span><?php echo $args['desc']; ?></span>
                </div>
            </div>
        <?php endforeach; ?>

        </div>

        <h3>
            <?php esc_html_e( 'Loop Automations', 'automatorwp' ); ?>
            <span class="cmb-tooltip">
                <i class="dashicons dashicons-cmb-tooltip"></i>
                <span class="cmb-tooltip-desc cmb-tooltip-top"><?php _e( 'Designed to run actions on a <b>group of elements</b>. Can be run <b>manually</b>, on a <b>specific date</b> or on a <b>recurring</b> basis.', 'automatorwp' ); ?></span>
            </span>
        </h3>

        <div class="automatorwp-automation-types">

        <?php foreach( $types as $type => $args ) :
            if( ! in_array( $type, automatorwp_get_automation_loop_types() ) ) continue; ?>
            <div class="automatorwp-automation-type automatorwp-automation-type-<?php echo $type; ?>" data-type="<?php echo $type; ?>">
                <img src="<?php echo $args['image']; ?>" alt="<?php echo $args['label']; ?>">
                <div class="automatorwp-automation-type-description">
                    <strong><?php echo $args['label']; ?></strong>
                    <span><?php echo $args['desc']; ?></span>
                </div>
            </div>
        <?php endforeach; ?>

        </div>

        <div class="center">
            <a href="<?php esc_attr( admin_url( 'admin.php?page=add_automatorwp_automations' ) ) ?>" class="button button-primary"><?php esc_html_e( 'Add New Automation', 'automatorwp' ); ?></a>
        </div>

    </div>

    <script>
        (function ( $ ) {

            // Select automation type
            $('body').on('click', '.automatorwp-automation-type-dialog .automatorwp-automation-type', function(e) {
                $('.automatorwp-automation-type-dialog .automatorwp-automation-type-selected').removeClass('automatorwp-automation-type-selected');
                $(this).addClass('automatorwp-automation-type-selected');
            });

            // Automation type dialog confirm button
            $('body').on('click', '#automatorwp-dashboard-new-automation .button', function(e) {
                e.preventDefault();

                var $this = $(this);

                if( $this.hasClass('disabled') ) {
                    return;
                }

                // Disable the button
                $this.addClass('disabled');

                $this.parent().prepend('<span class="spinner is-active" style="float: none; margin-top: 13px;"></span>');

                var type = 'user';
                var selected = $('.automatorwp-automation-type-dialog .automatorwp-automation-type-selected');

                if( selected.length ) {
                    type = selected.data('type');
                }

                var url = window.location.href.split('admin.php')[0];

                // Redirect to the add new automation
                window.location.href = url + 'admin.php?page=add_automatorwp_automations&type=' + type;

            });

        })( jQuery );
    </script>

    <?php

}

/**
 * Dashboard add-ons section
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_add_ons_section() {

    $plugins = automatorwp_plugins_api();

    if ( is_wp_error( $plugins ) ) {
        return;
    }

    $add_ons_to_display = array(
        'calculator',
        'csv',
        'custom-user-fields',
        'formatter',
        'generator',
        'restrict-content',
        'user-lists',
        'qr-code',
        'schedule-actions',
        'webhooks',
    );

    $add_ons_to_display_2 = array(
        'block-users',
        'button',
        'link',
        'multimedia-content',
        'run-now',
        'code',
        'users',
        'posts',
        'comments',
        'emails',
    );

    $add_ons = array();
    $add_ons_2 = array();

    foreach( $plugins as $plugin ) {

        if( in_array( $plugin->info->slug, $add_ons_to_display ) ) {
            $add_ons[] = $plugin;
        }

        if( in_array( $plugin->info->slug, $add_ons_to_display_2 ) ) {
            $add_ons_2[] = $plugin;
        }

    }

    ?>

    <?php // Add-ons ?>
    <h1><?php echo esc_html_e( 'Add-ons', 'automatorwp' ); ?></h1>
    <p class="automatorwp-section-desc"><?php esc_html_e( 'Pro add-ons help to maintain AutomatorWP and offer the most advanced features.', 'automatorwp' ); ?></p>

    <div id="normal-sortables" class="meta-box-sortables ui-sortable automatorwp-dashboard-add-ons-section">
        <?php foreach( $add_ons as $add_on )
            automatorwp_dashboard_render_plugin_card( $add_on ); ?>

        <?php foreach( $add_ons_2 as $add_on )
            automatorwp_dashboard_render_plugin_card( $add_on ); ?>

        <div class="automatorwp-dashboard-more-add-ons">
            <a href="<?php echo admin_url( 'admin.php?page=automatorwp_add_ons' ); ?>" target="_blank"><?php esc_html_e( 'View all add-ons', 'automatorwp' ); ?></a>
        </div>
    </div>

    <?php

}

/**
 * Dashboard integrations section
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_integrations_section() {

    $plugins = automatorwp_plugins_api();

    if ( is_wp_error( $plugins ) ) {
        return;
    }

    $add_ons_to_display = array(
        'bbforms',
        'buddyboss',
        'gamipress',
        'learndash',
        'shortlinkspro',
        'woocommerce',
        'lifterlms',
        'fluentcrm',
        'presto-player',
        'groundhogg',
        'wpfusion',
    );

    $add_ons_to_display_2 = array(
        'advanced-custom-fields',
        'contact-form-7',
        'tutor',
        'affiliatewp',
        'slicewp',
        'elementor',
        'paid-memberships-pro',
        'popup-maker',
        'the-events-calendar',
        'wp-job-manager',
        'wishlist-member',
    );

    $add_ons = array();
    $add_ons_2 = array();

    foreach( $plugins as $plugin ) {

        if( in_array( $plugin->info->slug, $add_ons_to_display ) ) {
            $add_ons[] = $plugin;
        }

        if( in_array( $plugin->info->slug, $add_ons_to_display_2 ) ) {
            $add_ons_2[] = $plugin;
        }

    }

    ?>

    <?php // Integrations ?>
    <h1><?php echo esc_html_e( 'Integrations', 'automatorwp' ); ?></h1>
    <p class="automatorwp-section-desc"><?php esc_html_e( 'AutomatorWP integrates seamlessly with your favourites WordPress plugins.', 'automatorwp' ); ?></p>

    <div id="normal-sortables" class="meta-box-sortables ui-sortable automatorwp-dashboard-add-ons-section">
        <?php foreach( $add_ons as $add_on )
            automatorwp_dashboard_render_plugin_card( $add_on ); ?>

        <?php foreach( $add_ons_2 as $add_on )
            automatorwp_dashboard_render_plugin_card( $add_on ); ?>

        <div class="automatorwp-dashboard-more-add-ons">
            <a href="<?php echo admin_url( 'admin.php?page=automatorwp_add_ons&tab=integrations' ); ?>" target="_blank"><?php esc_html_e( 'View all integrations', 'automatorwp' ); ?></a>
        </div>
    </div>

    <?php

}

/**
 * Dashboard apps section
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_apps_section() {

    $plugins = automatorwp_plugins_api();

    if ( is_wp_error( $plugins ) ) {
        return;
    }

    $add_ons_to_display = array(
        'openai',
        'aweber',
        'google-sheets',
        'trello',
        'youtube',
        'zapier',
        'make',
        'hubspot',
        'whatsapp',
        'mautic',
    );

    $add_ons_to_display_2 = array(
        'instagram',
        'twitter',
        'google-calendar',
        'activecampaign',
        'bluesky',
        'campaign-monitor',
        'mailchimp',
        'mailerlite',
        'facebook',
        'zoom',
    );

    $add_ons = array();
    $add_ons_2 = array();

    foreach( $plugins as $plugin ) {

        if( in_array( $plugin->info->slug, $add_ons_to_display ) ) {
            $add_ons[] = $plugin;
        }

        if( in_array( $plugin->info->slug, $add_ons_to_display_2 ) ) {
            $add_ons_2[] = $plugin;
        }

    }

    ?>

    <?php // Apps Integrations ?>
    <h1><?php echo esc_html_e( 'Apps Integrations', 'automatorwp' ); ?></h1>
    <p class="automatorwp-section-desc"><?php esc_html_e( 'Connect your WordPress with external platforms and apps to take your site even further!', 'automatorwp' ); ?></p>

    <div id="normal-sortables" class="meta-box-sortables ui-sortable automatorwp-dashboard-add-ons-section">
        <?php foreach( $add_ons as $add_on )
            automatorwp_dashboard_render_plugin_card( $add_on ); ?>

        <?php foreach( $add_ons_2 as $add_on )
            automatorwp_dashboard_render_plugin_card( $add_on ); ?>

        <div class="automatorwp-dashboard-more-add-ons">
            <a href="<?php echo admin_url( 'admin.php?page=automatorwp_add_ons&tab=app' ); ?>" target="_blank"><?php esc_html_e( 'View all apps integrations', 'automatorwp' ); ?></a>
        </div>
    </div>

    <?php

}

/**
 * Dashboard videos box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_videos_box() {
    ?>
    <div class="automatorwp-dashboard-columns">

        <div class="automatorwp-dashboard-column automatorwp-dashboard-main-video">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/8CcRMWx9EtA" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>

        <div class="automatorwp-dashboard-column automatorwp-dashboard-videos-list">
            <div class="automatorwp-dashboard-videos">
                <?php
                $videos = array(
                    array(
                        'id' => 'vWqRcEO8SgY',
                        'title' => 'Creating your first automation on WordPress',
                        'duration' => '1:53',
                    ),
                    array(
                        'id' => 'bDXtY386RpE',
                        'title' => 'Connect your WordPress plugins with Google Sheets',
                        'duration' => '3:01',
                    ),
                    array(
                        'id' => 'JHCWVbLqR0A',
                        'title' => 'Give access to site B when a purchase is made on site A (WooCommerce & LifterLMS)',
                        'duration' => '4:32',
                    ),
                    array(
                        'id' => 'EkoEmKsSHMo',
                        'title' => 'Run automations through Youtube videos',
                        'duration' => '1:55',
                    ),
                    array(
                        'id' => 'sZfYK6jtGg0',
                        'title' => 'Run automations through Vimeo videos',
                        'duration' => '2:06',
                    ),
                );

                foreach( $videos as $video ) { ?>
                    <div class="automatorwp-dashboard-video">
                        <a href="https://www.youtube.com/watch?v=<?php echo $video['id']; ?>" target="_blank">
                            <div class="automatorwp-dashboard-video-image">
                                <img src="https://img.youtube.com/vi/<?php echo $video['id']; ?>/default.jpg" alt="">
                            </div>
                            <div class="automatorwp-dashboard-video-details">
                                <strong class="automatorwp-dashboard-video-title"><?php echo $video['title']; ?></strong>
                                <div class="automatorwp-dashboard-video-duration"><?php echo $video['duration']; ?></div>
                            </div>
                        </a>
                    </div>
                <?php }

                ?>
            </div>
            <div class="automatorwp-dashboard-more-videos">
                <a href="https://www.youtube.com/channel/UCDBAqLYtoCYYUe2K_kx9Crw/videos" target="_blank"><?php esc_html_e( 'View all videos', 'automatorwp' ); ?></a>
            </div>
        </div>

        <div class="automatorwp-dashboard-column automatorwp-dashboard-videos-list">
            <div class="automatorwp-dashboard-videos">
                <?php
                $videos = array(
                    array(
                        'id' => '3YMMQ6QqrDo',
                        'title' => 'Connect automations from different sites through Webhooks',
                        'duration' => '2:55',
                    ),
                    array(
                        'id' => 'Nk--kqIkC9Y',
                        'title' => 'Schedule actions of an automation',
                        'duration' => '2:11',
                    ),
                    array(
                        'id' => '75PhOiqZRys',
                        'title' => 'Run automations through button clicks',
                        'duration' => '2:24',
                    ),
                    array(
                        'id' => 'yTfygkAG4Lk',
                        'title' => 'Run automations through link clicks',
                        'duration' => '2:17',
                    ),
                    array(
                        'id' => '2Tw3HIqvQuk',
                        'title' => 'Connect your WordPress plugins with BuddyBoss',
                        'duration' => '1:58',
                    ),
                );

                foreach( $videos as $video ) { ?>
                    <div class="automatorwp-dashboard-video">
                        <a href="https://www.youtube.com/watch?v=<?php echo $video['id']; ?>" target="_blank">
                            <div class="automatorwp-dashboard-video-image">
                                <img src="https://img.youtube.com/vi/<?php echo $video['id']; ?>/default.jpg" alt="">
                            </div>
                            <div class="automatorwp-dashboard-video-details">
                                <strong class="automatorwp-dashboard-video-title"><?php echo $video['title']; ?></strong>
                                <div class="automatorwp-dashboard-video-duration"><?php echo $video['duration']; ?></div>
                            </div>
                        </a>
                    </div>
                <?php }

                ?>
            </div>
        </div>

    </div>
    <?php
}

/**
 * Dashboard docs box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_docs_box() {
    ?>
    <ul>
        <li><a href="https://automatorwp.com/docs/getting-started/what-is-automatorwp/" target="_blank"><?php esc_html_e( 'What is AutomatorWP?', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/getting-started/automations/" target="_blank"><?php esc_html_e( 'Automations', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/getting-started/anonymous-automations/" target="_blank"><?php esc_html_e( 'Anonymous Automations', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/getting-started/all-users-automations/" target="_blank"><?php esc_html_e( 'All Users Automations', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/getting-started/all-posts-automations/" target="_blank"><?php esc_html_e( 'All Posts Automations', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/getting-started/import-file-automations/" target="_blank"><?php esc_html_e( 'Import File Automations', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/getting-started/restrict-content-automations/" target="_blank"><?php esc_html_e( 'Restrict Content Automations', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/getting-started/triggers/" target="_blank"><?php esc_html_e( 'Triggers', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/getting-started/actions/" target="_blank"><?php esc_html_e( 'Actions', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/getting-started/filters/" target="_blank"><?php esc_html_e( 'Filters', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/getting-started/tags/" target="_blank"><?php esc_html_e( 'Tags', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/getting-started/logs/" target="_blank"><?php esc_html_e( 'Logs', 'automatorwp' ); ?></a></li>
    </ul>
    <?php
}

/**
 * Dashboard advanced box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_features_box() {
    ?>
    <ul>
        <li>
            <ul>
                <li><a href="https://automatorwp.com/docs/features/sequential-triggers/" target="_blank"><?php esc_html_e( 'Sequential triggers', 'automatorwp' ); ?></a></li>
                <li><a href="https://automatorwp.com/docs/features/redirect-users/" target="_blank"><?php esc_html_e( 'Redirect users', 'automatorwp' ); ?></a></li>
                <li><a href="https://automatorwp.com/docs/features/import-export-automations-through-url/" target="_blank"><?php esc_html_e( 'Import & Export automations through URL', 'automatorwp' ); ?></a></li>
            </ul>
        </li>
        <li>
            <h3><?php esc_html_e( 'Special actions', 'automatorwp' ); ?></h3>
            <ul>
                <li><a href="https://automatorwp.com/docs/special-actions/call-a-function/" target="_blank"><?php esc_html_e( 'Call a function', 'automatorwp' ); ?></a></li>
                <li><a href="https://automatorwp.com/docs/special-actions/run-a-wordpress-hook/" target="_blank"><?php esc_html_e( 'Run a WordPress hook', 'automatorwp' ); ?></a></li>
                <li><a href="https://automatorwp.com/docs/special-actions/multiple-posts-actions/" target="_blank"><?php esc_html_e( 'Multiple posts actions', 'automatorwp' ); ?></a></li>
            </ul>
        </li>
        <li>
            <h3><?php esc_html_e( 'Special tags', 'automatorwp' ); ?></h3>
            <ul>
                <li><a href="https://automatorwp.com/docs/special-tags/user-meta-tag/" target="_blank"><?php esc_html_e( 'User meta tag', 'automatorwp' ); ?></a></li>
                <li><a href="https://automatorwp.com/docs/special-tags/post-meta-tag/" target="_blank"><?php esc_html_e( 'Post meta tag', 'automatorwp' ); ?></a></li>
                <li><a href="https://automatorwp.com/docs/special-tags/date-tag/" target="_blank"><?php esc_html_e( 'Date tag', 'automatorwp' ); ?></a></li>
                <li><a href="https://automatorwp.com/docs/special-tags/function-tags/" target="_blank"><?php esc_html_e( 'Function tags', 'automatorwp' ); ?></a></li>
            </ul>
        </li>
    </ul>
    <?php
}

/**
 * Dashboard advanced features box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_advanced_features_box() {
    ?>
    <ul>
        <li><a href="https://automatorwp.com/docs/connecting-sites/create-an-account-in-site-b-when-a-user-interacts-on-site-a/" target="_blank"><?php esc_html_e( 'Create an account in Site B when a user interacts on Site A', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/connecting-sites/enroll-in-a-course-of-site-b-when-a-purchase-is-made-in-site-a/" target="_blank"><?php esc_html_e( 'Enroll in a course of Site B when a purchase is made in Site A', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/import-csv/import-data-from-csv/" target="_blank"><?php esc_html_e( 'Import data from CSV', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/user-lists/create-user-lists/" target="_blank"><?php esc_html_e( 'Create User Lists', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/restrict-content/" target="_blank"><?php esc_html_e( 'Restrict Content', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/custom-user-fields/add-custom-user-fields/" target="_blank"><?php esc_html_e( 'Add Custom User Fields', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/calculator/calculate-formula/" target="_blank"><?php esc_html_e( 'Calculate formula', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/formatter/formatter-actions/" target="_blank"><?php esc_html_e( 'Format Content', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/generator/generator-actions/" target="_blank"><?php esc_html_e( 'Generate Content', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/qr-code/generate-a-qr-code/" target="_blank"><?php esc_html_e( 'Generate a QR Code', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/schedule-actions/schedule-actions-of-an-automation/" target="_blank"><?php esc_html_e( 'Schedule actions of an automation', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/docs/button/" target="_blank"><?php esc_html_e( 'Run automations through button clicks', 'automatorwp' ); ?></a> <?php esc_html_e( 'or', 'automatorwp' ); ?> <a href="https://automatorwp.com/docs/link/" target="_blank"><?php esc_html_e( 'link clicks', 'automatorwp' ); ?></a></li>
    </ul>
    <?php
}

/**
 * Dashboard support box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_support_box() {
    ?>
    <ul>
        <li><a href="https://automatorwp.com/contact-us/" target="_blank"><?php esc_html_e( 'Contact us', 'automatorwp' ); ?></a></li>
        <li><a href="https://wordpress.org/support/plugin/automatorwp" target="_blank"><?php esc_html_e( 'Support Forums', 'automatorwp' ); ?></a></li>
        <li><a href="https://automatorwp.com/faq/" target="_blank"><?php esc_html_e( 'FAQ', 'automatorwp' ); ?></a></li>
    </ul>
    <?php
}

/**
 * Dashboard plugins box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_plugins_box() {
    $url = AUTOMATORWP_URL . 'assets/img/logos/';
    ?>
    <ul id="our-plugins-list" class="our-plugins-list">
        <li>
            <a href="https://wordpress.org/plugins/gamipress/" target="_blank">
                <img src="<?php echo esc_attr( $url . 'gamipress.svg' ); ?>" class="our-plugins-img our-plugins-gamipress" loading="lazy">
                <span>GamiPress</span>
            </a>
        </li>
        <li>
            <a href="https://wordpress.org/plugins/automatorwp/" target="_blank">
                <img src="<?php echo esc_attr( $url . 'automatorwp.svg' ); ?>" class="our-plugins-img our-plugins-automatorwp" loading="lazy">
                <span>AutomatorWP</span>
            </a>
        </li>
        <li>
            <a href="https://wordpress.org/plugins/shortlinkspro/" target="_blank">
                <img src="<?php echo esc_attr( $url . 'shortlinkspro.svg' ); ?>" class="our-plugins-img our-plugins-shortlinkspro" loading="lazy">
                <span>ShortLinks Pro</span>
            </a>
        </li>
        <li>
            <a href="https://wordpress.org/plugins/bbforms/" target="_blank">
                <img src="<?php echo esc_attr( $url . 'bbforms.svg' ); ?>" class="our-plugins-img our-plugins-bbforms" loading="lazy">
                <span>BBForms</span>
            </a>
        </li>
    </ul>
    <?php
}

/**
 * Dashboard team box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_team_box() {
    ?>
    <ul id="contributors-list" class="contributors-list">
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://profiles.wordpress.org/rubengc/" target="_blank">
                <img src="https://secure.gravatar.com/avatar/103d0ec19ade3804009f105974fd4d05?s=64&amp;d=mm&amp;r=g" class="avatar avatar-32 photo" loading="lazy">
                <span class="cmb-tooltip-desc cmb-tooltip-top center">Ruben Garcia</span>
            </a>
        </li>
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://profiles.wordpress.org/eneribs/" target="_blank">
                <img src="https://secure.gravatar.com/avatar/7103ea44d40111ab67a22efe7ebd6f71?s=64&amp;d=mm&amp;r=g" class="avatar avatar-32 photo" loading="lazy">
                <span class="cmb-tooltip-desc cmb-tooltip-top center">Irene Berna</span>
            </a>
        </li>
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://profiles.wordpress.org/dioni00/" target="_blank">
                <img src="https://secure.gravatar.com/avatar/6de68ad3863fdf3c92a194ba16546571?s=64&amp;d=mm&amp;r=g" class="avatar avatar-32 photo" loading="lazy">
                <span class="cmb-tooltip-desc cmb-tooltip-top center">Dionisio Sanchez</span>
            </a>
        </li>
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://profiles.wordpress.org/tinocalvo/" target="_blank">
                <img src="https://secure.gravatar.com/avatar/a438aa12efcfb007f3db145d6ad37def?s=64&amp;d=retro&amp;r=g" class="avatar avatar-32 photo" loading="lazy">
                <span class="cmb-tooltip-desc cmb-tooltip-top center">Tino Calvo</span>
            </a>
        </li>
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://profiles.wordpress.org/pacogon/" target="_blank">
                <img src="https://secure.gravatar.com/avatar/348f374779e7433ad6bf3930cb2a492e?s=64&amp;d=mm&amp;r=g" class="avatar avatar-32 photo" loading="lazy">
                <span class="cmb-tooltip-desc cmb-tooltip-top center">Paco González</span>
            </a>
        </li>
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://profiles.wordpress.org/flabernardez/" target="_blank">
                <img src="https://secure.gravatar.com/avatar/fd626d9a8463260894f0f6f07a5cc71a?s=64&amp;d=mm&amp;r=g" class="avatar avatar-32 photo" loading="lazy">
                <span class="cmb-tooltip-desc cmb-tooltip-top center">Flavia Bernardez</span>
            </a>
        </li>
    </ul>
    <?php
}

/**
 * Dashboard involved box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_social_box() {
    ?>
    <ul class="automatorwp-dashboard-social-list">
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://www.youtube.com/channel/UCDBAqLYtoCYYUe2K_kx9Crw" target="_blank"><i class="dashicons dashicons-youtube"></i></a>
            <span class="cmb-tooltip-desc cmb-tooltip-top center"><?php esc_html_e( 'Subscribe to our YouTube channel', 'automatorwp' ); ?></span>
        </li>
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://www.facebook.com/AutomatorWP/" target="_blank"><i class="dashicons dashicons-facebook-alt"></i></a>
            <span class="cmb-tooltip-desc cmb-tooltip-top center"><?php esc_html_e( 'Follow us on Facebook', 'automatorwp' ); ?></span>
        </li>
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://www.facebook.com/groups/automatorwp" target="_blank"><i class="dashicons dashicons-groups"></i></a>
            <span class="cmb-tooltip-desc cmb-tooltip-top center"><?php esc_html_e( 'Join our Facebook community', 'automatorwp' ); ?></span>
        </li>
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://twitter.com/AutomatorWP" target="_blank"><i class="dashicons dashicons-twitter"></i></a>
            <span class="cmb-tooltip-desc cmb-tooltip-top center"><?php esc_html_e( 'Follow @AutomatorWP on Twitter', 'automatorwp' ); ?></span>
        </li>
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://www.linkedin.com/company/65262548/" target="_blank"><i class="dashicons dashicons-linkedin"></i></a>
            <span class="cmb-tooltip-desc cmb-tooltip-top center"><?php esc_html_e( 'Follow us on LinkedIn', 'automatorwp' ); ?></span>
        </li>
    </ul>

    <h3><?php esc_html_e( 'Contact us', 'automatorwp' ); ?></h3>
    <ul class="automatorwp-dashboard-social-list">
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://wordpress.org/support/plugin/automatorwp/" target="_blank"><i class="dashicons dashicons-wordpress"></i></a>
            <span class="cmb-tooltip-desc cmb-tooltip-top center"><?php esc_html_e( 'Support Forums', 'automatorwp' ); ?></span>
        </li>
        <li class="cmb-tooltip cmb-tooltip-no-opacity">
            <a href="https://automatorwp.com/contact-us/" target="_blank"><i class="dashicons dashicons-email-alt"></i></a>
            <span class="cmb-tooltip-desc cmb-tooltip-top center"><?php esc_html_e( 'Open a support ticket', 'automatorwp' ); ?></span>
        </li>
    </ul>
    <?php
}

/**
 * Dashboard involved box
 *
 * @since  2.0.0
 */
function automatorwp_dashboard_involved_box() {
    ?>
    <p><?php esc_html_e( 'AutomatorWP is a free and open-source plugin accessible to everyone just like WordPress. There are many ways you can help support AutomatorWP', 'automatorwp' ); ?></p>
    <ul>
        <li><a href="https://translate.wordpress.org/projects/wp-plugins/automatorwp/" target="_blank"><i class="dashicons dashicons-translation"></i> <?php esc_html_e( 'Translate AutomatorWP into your language.', 'automatorwp' ); ?></a></li>
        <li><a href="https://wordpress.org/plugins/automatorwp/#reviews" target="_blank"><i class="dashicons dashicons-wordpress"></i> <?php esc_html_e( 'Review AutomatorWP on WordPress.org.', 'automatorwp' ); ?></a></li>
    </ul>
    <?php
}