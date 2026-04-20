<?php
/**
 * Clicks
 *
 * @package     ShortLinksPro\Custom_Tables\Clicks
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Custom Table Labels
 *
 * @since 1.0.0
 *
 * @return array
 */
function shortlinkspro_clicks_labels() {

    return array(
        'singular' => __( 'Click', 'shortlinkspro' ),
        'plural' => __( 'Clicks', 'shortlinkspro' ),
        'labels' => array(
            'list_menu_title' => __( 'Clicks', 'shortlinkspro' ),
        ),
    );

}
add_filter( 'ct_shortlinkspro_clicks_labels', 'shortlinkspro_clicks_labels' );

/**
 * Parse query args for fields
 *
 * @since 1.0.0
 *
 * @param string $where
 * @param CT_Query $ct_query
 *
 * @return string
 */
function shortlinkspro_clicks_query_where( $where, $ct_query ) {

    global $ct_table;

    if( $ct_table->name !== 'shortlinkspro_clicks' ) {
        return $where;
    }

    // Shorthand
    $qv = $ct_query->query_vars;

    $fields = array();

    $fields['ip']              = 'string';
    $fields['created_at']      = 'string';
    $fields['uri']             = 'string';
    $fields['url']             = 'string';
    $fields['redirect_type']   = 'string';
    $fields['referrer']        = 'string';
    $fields['parameters']      = 'string';
    $fields['country']         = 'string';
    $fields['browser']         = 'string';
    $fields['os']              = 'string';
    $fields['device']          = 'string';
    $fields['bot']             = 'string';
    $fields['link_id']         = 'integer';

    foreach( $fields as $field => $type ) {
        $where .= shortlinkspro_custom_table_where( $qv, $field, $type );
    }

    return $where;
}
add_filter( 'ct_query_where', 'shortlinkspro_clicks_query_where', 10, 2 );

/**
 * Define the search fields
 *
 * @since 1.0.0
 *
 * @param array $search_fields
 *
 * @return array
 */
function shortlinkspro_clicks_search_fields( $search_fields ) {

    $search_fields[] = 'ip';
    $search_fields[] = 'created_at';
    $search_fields[] = 'uri';
    $search_fields[] = 'url';
    $search_fields[] = 'redirect_type';
    $search_fields[] = 'referrer';
    $search_fields[] = 'parameters';
    $search_fields[] = 'country';
    $search_fields[] = 'browser';
    $search_fields[] = 'os';
    $search_fields[] = 'device';
    $search_fields[] = 'bot';
    $search_fields[] = 'link_id';

    return $search_fields;

}
add_filter( 'ct_query_shortlinkspro_clicks_search_fields', 'shortlinkspro_clicks_search_fields' );

/**
 * Columns in list view
 *
 * @since 1.0.0
 *
 * @param array $columns
 *
 * @return array
 */
function shortlinkspro_clicks_manage_columns( $columns = array() ) {

    $columns['ip']              = __( 'IP', 'shortlinkspro' );
    $columns['created_at']      = __( 'Date', 'shortlinkspro' );
    $columns['uri']             = __( 'URI', 'shortlinkspro' );
    $columns['url']             = __( 'Target URL', 'shortlinkspro' );
    $columns['redirect_type']   = __( 'Redirect', 'shortlinkspro' );
    $columns['referrer']        = __( 'Referrer', 'shortlinkspro' );
    $columns['parameters']      = __( 'Parameters', 'shortlinkspro' );
    $columns['country']         = __( 'Country', 'shortlinkspro' );
    $columns['browser']         = __( 'Browser', 'shortlinkspro' );
    $columns['os']              = __( 'OS', 'shortlinkspro' );
    $columns['device']          = __( 'Device', 'shortlinkspro' );

    if( ! shortlinkspro_get_option( 'exclude_robots', false ) ) {
        $columns['bot'] = __('Bot', 'shortlinkspro');
    }

    $columns['link_id']         = __( 'ShortLink', 'shortlinkspro' );

    return $columns;
}
add_filter( 'manage_shortlinkspro_clicks_columns', 'shortlinkspro_clicks_manage_columns' );

/**
 * Sortable columns for list view
 *
 * @since 1.0.0
 *
 * @param array $sortable_columns
 *
 * @return array
 */
function shortlinkspro_clicks_manage_sortable_columns( $sortable_columns ) {

    $sortable_columns['ip']                 = array( 'ip', false );
    $sortable_columns['created_at']         = array( 'created_at', true );
    $sortable_columns['uri']                = array( 'uri', false );
    $sortable_columns['url']                = array( 'url', false );
    $sortable_columns['redirect_type']      = array( 'redirect_type', false );
    $sortable_columns['referrer']           = array( 'referrer', false );
    $sortable_columns['parameters']         = array( 'parameters', false );
    $sortable_columns['country']            = array( 'country', false );
    $sortable_columns['browser']            = array( 'browser', false );
    $sortable_columns['os']                 = array( 'os', false );
    $sortable_columns['device']             = array( 'device', false );
    $sortable_columns['bot']                = array( 'bot', false );
    $sortable_columns['link_id']            = array( 'link_id', false );

    return $sortable_columns;

}
add_filter( 'manage_shortlinkspro_clicks_sortable_columns', 'shortlinkspro_clicks_manage_sortable_columns' );

/**
 * Row actions.
 *
 * @since 1.0.0
 *
 * @param array $actions An array of row action links. Defaults are
 *                         'Edit', 'Quick Edit', 'Restore, 'Trash',
 *                         'Delete Permanently', 'Preview', and 'View'.
 * @param stdClass $object The item object.
 */
function shortlinkspro_clicks_row_actions( $actions, $object ) {

    global $ct_table;

    return array();

}
add_filter( 'shortlinkspro_clicks_row_actions', 'shortlinkspro_clicks_row_actions', 10, 2 );

function shortlinkspro_clicks_get_views( $views ) {

    $periods = shortlinkspro_get_time_periods();
    $periods = array_reverse( $periods );
    $range = shortlinkspro_get_period_range( 'this-week' );
    $period_start = gmdate( 'Y-m-d', strtotime( $range['start'] ) );
    $period_end = gmdate( 'Y-m-d', strtotime( $range['end'] ) );

    $allowed_filters = array( 'ip', 'country', 'browser', 'device', 'os', 'link_id' );

    $ip         = ( isset( $_GET['ip'] ) ) ? sanitize_text_field( $_GET['ip'] ) : '';
    $country    = ( isset( $_GET['country'] ) ) ? sanitize_text_field( $_GET['country'] ) : '';
    $browser    = ( isset( $_GET['browser'] ) ) ? sanitize_text_field( $_GET['browser'] ) : '';
    $os         = ( isset( $_GET['os'] ) ) ? sanitize_text_field( $_GET['os'] ) : '';
    $device     = ( isset( $_GET['device'] ) ) ? sanitize_text_field( $_GET['device'] ) : '';
    $link_id    = ( isset( $_GET['link_id'] ) ) ? absint( $_GET['link_id'] ) : '';

    $from = ( isset( $_GET['from'] ) ) ? sanitize_text_field( $_GET['from'] ) : '';

    $filtering = ( isset( $_GET['ip'] )
        || isset( $_GET['country'] )
        || isset( $_GET['browser'] )
        || isset( $_GET['os'] )
        || isset( $_GET['device'] )
        || isset( $_GET['link_id'] ) );

    ?>
    <div id="shortlinkspro-clicks-chart-container">

        <?php if( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_GET['_wpnonce'] ) ), 'shortlinkspro_clicks_filter' ) ) : ?>
            <?php if( $filtering ) : ?>
            <div class="shortlinkspro-clicks-filtered-by">
                <?php
                echo esc_html( __( "Filtered by", 'shortlinkspro' ) ) . ' ';

                if( isset( $_GET['ip'] ) ) {
                    echo esc_html( $ip );
                } else if( isset( $_GET['country'] ) ) {
                    echo shortlinkspro_get_country_flag( $country ) . ' ' . esc_html( shortlinkspro_get_country_name( $country ) );
                } else if( isset( $_GET['browser'] ) ) {
                    $class = strtolower( str_replace( ' ', '-', $browser ) );
                    ?>
                    <span class="shortlinkspro-browser shortlinkspro-browser-<?php echo esc_attr( $class ); ?>" title="<?php echo esc_attr( $browser ); ?>"><?php echo esc_html( $browser ); ?></span>
                    <?php
                } else if( isset( $_GET['os'] ) ) {
                    $class = strtolower( str_replace( ' ', '-', $os ) );
                    ?>
                    <span class="shortlinkspro-os shortlinkspro-os-<?php echo esc_attr( $class ); ?>" title="<?php echo esc_attr( $os ); ?>"><?php echo esc_html( $os ); ?></span>
                    <?php
                } else if( isset( $_GET['device'] ) ) {
                    $class = strtolower( str_replace( ' ', '-', $device ) );
                    ?>
                    <span class="shortlinkspro-device shortlinkspro-device-<?php echo esc_attr( $class ); ?>" title="<?php echo esc_attr( $device ); ?>"><?php echo esc_html( $device ); ?></span>
                    <?php
                } else if( isset( $_GET['link_id'] ) ) {
                    echo shortlinkspro_get_link_edit_link( $link_id );
                }

                echo ' | ';

                if( $from === 'links' ) {
                    echo sprintf( '<a href="%s">&laquo %s</a>',
                        esc_attr( ct_get_list_link( 'shortlinkspro_links' ) ),
                        esc_html__( "Back to Links", 'shortlinkspro' )
                    );
                } else {
                    echo sprintf( '<a href="%s">&laquo %s</a>',
                        esc_attr( ct_get_list_link( 'shortlinkspro_clicks' ) ),
                        esc_html__( "Back to Clicks", 'shortlinkspro' )
                    );
                }
                ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="shortlinkspro-clicks-chart-controls">
            <div class="shortlinkspro-clicks-chart-custom-period-controls" style="display: none;">
                <label for="period_start"><?php echo esc_html( __('From', 'shortlinkspro') ); ?></label>
                <input type="date" id="period_start" class="shortlinkspro-period-start" value="<?php echo esc_attr( $period_start ); ?>">
                <label for="period_end"><?php echo esc_html( __('to', 'shortlinkspro') ); ?></label>
                <input type="date" id="period_end" class="shortlinkspro-period-end" value="<?php echo esc_attr( $period_end ); ?>">
            </div>
            <div class="shortlinkspro-periods">
                <?php foreach ( $periods as $period => $label ) : ?>
                    <button type="button" value="<?php echo esc_attr( $period ); ?>" class="button shortlinkspro-period <?php echo ( $period === 'this-week' ? 'button-primary' : '' ); ?>"><?php echo esc_html( $label ); ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="shortlinkspro-clicks-chart" data-action="shortlinkspro_clicks_chart" style="height: 300px;"
             data-ip="<?php echo esc_attr( $ip ); ?>"
             data-country="<?php echo esc_attr( $country ); ?>"
             data-browser="<?php echo esc_attr( $browser ); ?>"
             data-os="<?php echo esc_attr( $os ); ?>"
             data-device="<?php echo esc_attr( $device ); ?>"
             data-link_id="<?php echo esc_attr( $link_id ); ?>"
        >
            <canvas></canvas>
        </div>

    </div>
    <?php

    return $views;

}
add_filter( 'shortlinkspro_clicks_get_views', 'shortlinkspro_clicks_get_views' );

/**
 * Columns rendering for list view
 *
 * @since  1.0.0
 *
 * @param string $column_name
 * @param integer $object_id
 */
function shortlinkspro_clicks_manage_custom_column(  $column_name, $object_id ) {

    // Setup vars
    $click = ct_get_object( $object_id );

    switch( $column_name ) {
        case 'ip':
            shortlinkspro_show_clicks_filter_link( array(
                'key' => 'ip',
                'value' => $click->ip,
                'label' => $click->ip,
                'esc_label' => true,
                'from' => 'clicks',
            ) );
            break;
        case 'created_at':
            echo esc_html( $click->created_at );
            break;
        case 'uri':
            echo esc_html( $click->uri );
            break;
        case 'url':
            echo esc_html( $click->url );
            break;
        case 'redirect_type':
            $redirect_type = $click->redirect_type;
            $redirect_types = shortlinkspro_redirect_types();
            $redirect_type_label = ( isset( $redirect_types[$redirect_type] ) ) ? $redirect_types[$redirect_type] : $redirect_type;
            ?>
            <span class="shortlinkspro-link-option shortlinkspro-link-option-enabled shortlinkspro-link-option-redirect-type shortlinkspro-link-option-redirect-<?php echo esc_attr( $redirect_type ); ?>">
                <?php cmb_tooltip_html( __( 'Redirect:', 'shortlinkspro' ) . ' ' . $redirect_type_label, 'arrow-right-alt' ); ?>
            </span>
            <?php
            break;
        case 'referrer':
            echo esc_html( $click->referrer );
            break;
        case 'parameters':
            echo esc_html( $click->parameters );
            break;
        case 'country':
            if( ! empty( $click->country ) ) {
                shortlinkspro_show_clicks_filter_link( array(
                    'key' => 'country',
                    'value' => $click->country,
                    'label' => shortlinkspro_get_country_flag( $click->country ) . ' ' . esc_html( shortlinkspro_get_country_name($click->country) ),
                    'esc_label' => false,
                    'from' => 'clicks',
                ) );
            } else {
                shortlinkspro_show_clicks_filter_link( array(
                    'key' => 'country',
                    'value' => '',
                    'label' => __( 'Unknown', 'shortlinkspro' ),
                    'from' => 'clicks',
                ) );
            }
            break;
        case 'browser':
            $class = strtolower( str_replace( ' ', '-', $click->browser ) );

            ob_start(); ?>
            <span class="shortlinkspro-browser shortlinkspro-browser-<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $click->browser ); ?></span>
            <?php if( ! empty( $click->browser_version ) && $click->browser_version !== 'unknown' ): ?>
            <span class="shortlinkspro-browser-version">v<?php echo esc_html( $click->browser_version ); ?></span>
            <?php endif;
            $label = ob_get_clean();

            shortlinkspro_show_clicks_filter_link( array(
                'key' => 'browser',
                'value' => $click->browser,
                'title' => $click->browser,
                'label' => $label,
                'esc_label' => false,
                'from' => 'clicks',
            ) );
            break;
        case 'os':
            $class = strtolower( str_replace( ' ', '-', $click->os ) );

            ob_start(); ?>
            <span class="shortlinkspro-os shortlinkspro-os-<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $click->os ); ?></span>
            <?php if( ! empty( $click->os_version ) && $click->os_version !== 'unknown' ): ?>
                <span class="shortlinkspro-os-version">v<?php echo esc_html( $click->os_version ); ?></span>
            <?php endif;
            $label = ob_get_clean();

            shortlinkspro_show_clicks_filter_link( array(
                'key' => 'os',
                'value' => $click->os,
                'title' => $click->os,
                'label' => $label,
                'esc_label' => false,
                'from' => 'clicks',
            ) );
            break;
        case 'device':
            $class = strtolower( str_replace( ' ', '-', $click->device ) );

            ob_start(); ?>
            <span class="shortlinkspro-device shortlinkspro-device-<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $click->device ); ?></span>
            <?php
            $label = ob_get_clean();

            shortlinkspro_show_clicks_filter_link( array(
                'key' => 'device',
                'value' => $click->device,
                'title' => $click->device,
                'label' => $label,
                'esc_label' => false,
                'from' => 'clicks',
            ) );
            break;
        case 'bot':
            echo esc_html( $click->bot );
            break;
        case 'link_id':
            shortlinkspro_show_clicks_filter_link( array(
                'key' => 'link_id',
                'value' => $click->link_id,
                'label' => shortlinkspro_get_link_title( $click->link_id ),
                'esc_label' => true,
                'from' => 'clicks',
            ) );
            break;
    }
}
add_action( 'manage_shortlinkspro_clicks_custom_column', 'shortlinkspro_clicks_manage_custom_column', 10, 2 );

/**
 * Helper function to get a click filter URL
 *
 * @since 1.0.0
 *
 * @param string $key
 * @param string $value
 * @param string $from
 *
 * @return string
 */
function shortlinkspro_get_clicks_filter_url( $key, $value, $from = 'clicks' ) {

    $url = ct_get_list_link( 'shortlinkspro_clicks' );
    $url = add_query_arg( array( $key => $value ), $url );
    $url = add_query_arg( array( 'from' => $from ), $url );
    $url = add_query_arg( '_wpnonce', wp_create_nonce( 'shortlinkspro_clicks_filter' ), $url );

    return $url;

}

/**
 * Helper function to display a filter link on the clicks screen
 *
 * @since 1.0.0
 *
 * @param array $args
 */
function shortlinkspro_show_clicks_filter_link( $args ) {

    $args = wp_parse_args( $args, array(
        'key' => '',
        'value' => '',
        'label' => '',
        'title' => '',
        'esc_label' => true,
        'from' => 'clicks',
    ) );

    $label = ( $args['esc_label'] ? esc_html( $args['label'] ) : $args['label'] );

    // Do not show if already filtering by this field
    if( isset( $_GET[$args['key']] ) ) {
        echo $label;
        return;
    }

    $url = shortlinkspro_get_clicks_filter_url( $args['key'], $args['value'], $args['from'] );

    if( empty( $args['title'] ) ) {
        $args['title'] = trim( strip_tags( $label ) );
    }

    /* translators: %s: Field to filter by. */
    $title = sprintf( __( 'Filter by %s', 'shortlinkspro' ), $args['title'] );
    ?>
    <a href="<?php echo esc_attr( $url ); ?>" title="<?php echo esc_attr( $title ); ?>"><?php echo $label; ?></a>
    <?php

}

/**
 * Meta boxes
 *
 * @since  1.0.0
 */
function shortlinkspro_clicks_add_meta_boxes() {

}
add_action( 'add_meta_boxes', 'shortlinkspro_clicks_add_meta_boxes' );

function shortlinkspro_clicks_meta_boxes() {

    // Title
    shortlinkspro_add_meta_box(
        'shortlinkspro-link-title',
        __( 'Link Title', 'shortlinkspro' ),
        'shortlinkspro_clicks',
        array(
            'title' => array(
                'name'      => __( 'Title', 'shortlinkspro' ),
                'type'      => 'text',
                'attributes' => array(
                    'placeholder' => __('Enter title here', 'shortlinkspro'),
                ),
            ),
        ),
        array(
            'priority' => 'high',
        )
    );

    // Link Options
    shortlinkspro_add_meta_box(
        'shortlinkspro-link-options',
        __( 'Link Options', 'shortlinkspro' ),
        'shortlinkspro_clicks',
        array(
            'nofollow' => array(
                'desc'      => __( 'No Follow', 'shortlinkspro' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => array(
                    'position' => 'left',
                    'desc' => __( 'Adds the nofollow and noindex parameters in the HTTP response headers. Recommended.', 'shortlinkspro' ),
                ),
                'after_field' => 'cmb_tooltip_after_field',
            ),
            'sponsored' => array(
                'desc'      => __( 'Sponsored', 'shortlinkspro' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => array(
                    'position' => 'left',
                    'desc' => __( 'Adds the sponsored parameter in the HTTP response headers. Recommended if this an affiliate link.', 'shortlinkspro' ),
                ),
                'after_field' => 'cmb_tooltip_after_field',
            ),
            'parameter_forwarding' => array(
                'desc'      => __( 'Parameter Forwarding', 'shortlinkspro' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => array(
                    'position' => 'left',
                    'desc' => __( 'Forward parameters passed to this link onto the target URL.', 'shortlinkspro' ),
                ),
                'after_field' => 'cmb_tooltip_after_field',
            ),
            'tracking' => array(
                'desc'      => __( 'Tracking', 'shortlinkspro' ),
                'type'      => 'checkbox',
                'classes'   => 'cmb2-switch',
                'tooltip'   => array(
                    'position' => 'left',
                    'desc' => __( 'Enable clicks tracking.', 'shortlinkspro' ),
                ),
                'after_field' => 'cmb_tooltip_after_field',
            ),
        ),
        array(
            'context' => 'side',
            'priority' => 'high',
        )
    );

    // Link Settings
    shortlinkspro_add_meta_box(
        'shortlinkspro-link-settings',
        __( 'Link Settings', 'shortlinkspro' ),
        'shortlinkspro_clicks',
        array(
            'url' => array(
                'name'      => __( 'Target URL', 'shortlinkspro' ),
                'type'      => 'textarea',
                'attributes' => array(
                    'rows' => 2,
                ),
                'tooltip'   => __( 'The URL that your link will redirect to.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'after_field' => 'shortlinkspro_clicks_url_after_field',
            ),
            'slug' => array(
                'name'      => __( 'ShortLink', 'shortlinkspro' ),
                'type'      => 'text',
                'tooltip'   => __( 'How your link will appear.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
                'before_field' => 'shortlinkspro_clicks_slug_before_field',
                'after_field' => 'shortlinkspro_clicks_slug_after_field',
            ),
            'redirect_type' => array(
                'name'      => __( 'Redirect Type', 'shortlinkspro' ),
                'type'      => 'select',
                'options'   => shortlinkspro_redirect_types(),
                'tooltip'   => __( 'Redirect type of this link.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
            'notes' => array(
                'name'      => __( 'Notes', 'shortlinkspro' ),
                'type'      => 'textarea',
                'attributes' => array(
                    'rows' => 2,
                ),
                'tooltip'   => __( 'Add internal notes to your link for your own needs. Those notes are not displayed anywhere.', 'shortlinkspro' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
        ),
        array(

        )
    );
}

add_action( 'cmb2_init', 'shortlinkspro_clicks_meta_boxes' );