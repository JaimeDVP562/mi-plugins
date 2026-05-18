<?php
/**
 * Admin
 *
 * @package     ShortLinksPro_Dynamic_Redirects\Admin
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * CMB2 Meta boxes
 *
 * @since  1.0.0
 */
function shortlinkspro_dynamic_redirects_links_meta_boxes() {

    // Dynamic Redirects
    shortlinkspro_add_meta_box(
        'shortlinkspro-dynamic-redirects-settings',
        __( 'Dynamic Redirects', 'shortlinkspro-dynamic-redirects' ),
        'shortlinkspro_links',
        array(
            'dynamic_redirect' => array(
                'name'      => __( 'Dynamic Redirect', 'shortlinkspro-dynamic-redirects' ),
                'type'      => 'select',
                'options'   => array(
                    ''              => __( 'None', 'shortlinkspro-dynamic-redirects' ),
                    'rotation'      => __( 'Rotation', 'shortlinkspro-dynamic-redirects' ),
                    'geographic'    => __( 'Geographic', 'shortlinkspro-dynamic-redirects' ),
                    'technology'    => __( 'Technology', 'shortlinkspro-dynamic-redirects' ),
                    'referrer'      => __( 'Referrer', 'shortlinkspro-dynamic-redirects' ),
                    'date_range'    => __( 'Date Range', 'shortlinkspro-dynamic-redirects' ),
                    'time_range'    => __( 'Time Range', 'shortlinkspro-dynamic-redirects' ),
                    'clicks'        => __( 'Clicks', 'shortlinkspro-dynamic-redirects' ),
                ),
                'tooltip'   => __( 'Set the dynamic redirect of your choice.', 'shortlinkspro-dynamic-redirects' ),
                'label_cb' => 'cmb_tooltip_label_cb',
            ),
            // Rotation
            'dynamic_redirect_rotation_fields' => array(
                'name' => __( 'Target URL Rotations', 'shortlinkspro-dynamic-redirects' )
                    . cmb_tooltip_get_html( __( 'Enter target URLs with their weight of use of your choice. Enter the full URL on each entry like <code>https://site.com/</code>, <code>https://site.com/?param=123</code> or <code>https://other-site.com/</code>', 'shortlinkspro-dynamic-redirects' ) ),
                'desc' => shortlinkspro_dynamic_redirects_conditions_message(),
                'type' => 'group',
                'custom_classes' => 'shortlinkspro-group-field shortlinkspro-group-field-table shortlinkspro-dynamic-redirects-group-field',
                'classes_cb' => 'cmb_conditional_fields_classes_cb',
                'show_if' => array(
                    'dynamic_redirect' => 'rotation',
                ),
                'options'     => array(
                    'add_button'        => __( 'Add', 'shortlinkspro-dynamic-redirects' ),
                    'remove_button'     => '<span class="dashicons dashicons-no-alt"></span>',
                ),
                'fields' => array(
                    'url' => array(
                        'name' => __( 'URL', 'shortlinkspro-dynamic-redirects' ),
                        'type' => 'text',
                        'default' => ''
                    ),
                    'weight' => array(
                        'name' => __( 'Weight', 'shortlinkspro-dynamic-redirects' ) . cmb_tooltip_get_html( __( 'Probability that this URL has to be randomly selected.', 'shortlinkspro-dynamic-redirects' ) ),
                        'desc' => '%',
                        'type' => 'text_small',
                        'default' => '100',
                        'attributes' => array(
                            'type' => 'number',
                            'min' => '0',
                            'max' => '100',
                            'step' => '1',
                        ),
                    ),
                ),
            ),
            // Geographic
            'dynamic_redirect_geographic_fields' => array(
                'name' => __( 'Geographic Redirects', 'shortlinkspro-dynamic-redirects' )
                    . cmb_tooltip_get_html( __( 'Enter target URLs based on the visitor\'s country.', 'shortlinkspro-dynamic-redirects' ) ),
                'desc' => shortlinkspro_dynamic_redirects_conditions_message(),
                'type' => 'group',
                'custom_classes' => 'shortlinkspro-group-field shortlinkspro-group-field-table shortlinkspro-dynamic-redirects-group-field',
                'classes_cb' => 'cmb_conditional_fields_classes_cb',
                'show_if' => array(
                    'dynamic_redirect' => 'geographic',
                ),
                'options'     => array(
                    'add_button'        => __( 'Add', 'shortlinkspro-dynamic-redirects' ),
                    'remove_button'     => '<span class="dashicons dashicons-no-alt"></span>',
                ),
                'fields' => array(
                    'url' => array(
                        'name' => __( 'URL', 'shortlinkspro-dynamic-redirects' ),
                        'type' => 'text',
                        'default' => ''
                    ),
                    'countries' => array(
                        'name' => __( 'Countries', 'shortlinkspro-dynamic-redirects' ),
                        'type' => 'multiselect2',
                        'options' => shortlinkspro_dynamic_redirects_get_countries(),
                        'attributes' => array(
                            'placeholder' => __( 'Select countries...', 'shortlinkspro-dynamic-redirects' ),
                            'data-country-flags' => true,
                        ),
                        'default' => '',

                    ),
                ),
            ),
            // Technology
            'dynamic_redirect_technology_fields' => array(
                'name' => __( 'Technology Redirects', 'shortlinkspro-dynamic-redirects' )
                    . cmb_tooltip_get_html( __( 'Enter target URLs based on the visitor\'s device, operating system and/or browser.', 'shortlinkspro-dynamic-redirects' ) ),
                'desc' => shortlinkspro_dynamic_redirects_conditions_message(),
                'type' => 'group',
                'custom_classes' => 'shortlinkspro-group-field shortlinkspro-group-field-table shortlinkspro-dynamic-redirects-group-field',
                'classes_cb' => 'cmb_conditional_fields_classes_cb',
                'show_if' => array(
                    'dynamic_redirect' => 'technology',
                ),
                'options'     => array(
                    'add_button'        => __( 'Add', 'shortlinkspro-dynamic-redirects' ),
                    'remove_button'     => '<span class="dashicons dashicons-no-alt"></span>',
                ),
                'fields' => array(
                    'url' => array(
                        'name' => __( 'URL', 'shortlinkspro-dynamic-redirects' ),
                        'type' => 'text',
                        'default' => ''
                    ),
                    'device' => array(
                        'name' => __( 'Device', 'shortlinkspro-dynamic-redirects' ),
                        'type' => 'select',
                        'options' => shortlinkspro_dynamic_redirects_get_devices(),
                        'default' => 'any',

                    ),
                    'os' => array(
                        'name' => __( 'Operating System', 'shortlinkspro-dynamic-redirects' ),
                        'type' => 'select',
                        'options' => shortlinkspro_dynamic_redirects_get_os(),
                        'default' => 'any',

                    ),
                    'browser' => array(
                        'name' => __( 'Browser', 'shortlinkspro-dynamic-redirects' ),
                        'type' => 'select2',
                        'options' => shortlinkspro_dynamic_redirects_get_browsers(),
                        'default' => 'any',

                    ),
                ),
            ),
            // Referrer
            'dynamic_redirect_referrer_fields' => array(
                'name' => __( 'Referrer Redirects', 'shortlinkspro-dynamic-redirects' )
                    . cmb_tooltip_get_html( __( 'Enter target URLs based on the visitor referrer domain. Use domains separated by commas like <code>facebook.com, x.com</code>.', 'shortlinkspro-dynamic-redirects' ) ),
                'desc' => shortlinkspro_dynamic_redirects_conditions_message(),
                'type' => 'group',
                'custom_classes' => 'shortlinkspro-group-field shortlinkspro-group-field-table shortlinkspro-dynamic-redirects-group-field',
                'classes_cb' => 'cmb_conditional_fields_classes_cb',
                'show_if' => array(
                    'dynamic_redirect' => 'referrer',
                ),
                'options'     => array(
                    'add_button'        => __( 'Add', 'shortlinkspro-dynamic-redirects' ),
                    'remove_button'     => '<span class="dashicons dashicons-no-alt"></span>',
                ),
                'fields' => array(
                    'url' => array(
                        'name' => __( 'URL', 'shortlinkspro-dynamic-redirects' ),
                        'type' => 'text',
                        'default' => ''
                    ),
                    'referrers' => array(
                        'name' => __( 'Referrers', 'shortlinkspro-dynamic-redirects' ),
                        'desc' => __( 'Enter referrer domains separated by commas (example: facebook.com, x.com).', 'shortlinkspro-dynamic-redirects' ),
                        'type' => 'text',
                        'default' => ''
                    ),
                ),
            ),
            // Date Period
            'dynamic_redirect_date_range_fields' => array(
                'name' => __( 'Date Range Redirects', 'shortlinkspro-dynamic-redirects' )
                    . cmb_tooltip_get_html( __( 'Enter target URLs based on the date ranges in which the visitor visits the link.', 'shortlinkspro-dynamic-redirects' ) ),
                'desc' => shortlinkspro_dynamic_redirects_conditions_message(),
                'type' => 'group',
                'custom_classes' => 'shortlinkspro-group-field shortlinkspro-group-field-table shortlinkspro-dynamic-redirects-group-field',
                'classes_cb' => 'cmb_conditional_fields_classes_cb',
                'show_if' => array(
                    'dynamic_redirect' => 'date_range',
                ),
                'options'     => array(
                    'add_button'        => __( 'Add', 'shortlinkspro-dynamic-redirects' ),
                    'remove_button'     => '<span class="dashicons dashicons-no-alt"></span>',
                ),
                'fields' => array(
                    'url' => array(
                        'name' => __( 'URL', 'shortlinkspro-dynamic-redirects' ),
                        'type' => 'text',
                        'default' => ''
                    ),
                    'start' => array(
                        'name' => __( 'Start Date', 'shortlinkspro-dynamic-redirects' ) . cmb_tooltip_get_html( __( 'Leave empty for no start date.', 'shortlinkspro-dynamic-redirects' ) ),
                        'type' => 'text_datetime_timestamp',
                        //'default' => date( 'Y-m-d 00:00:00' ),
                    ),
                    'end' => array(
                        'name' => __( 'End Date', 'shortlinkspro-dynamic-redirects' ) . cmb_tooltip_get_html( __( 'Leave empty for no end date.', 'shortlinkspro-dynamic-redirects' ) ),
                        'type' => 'text_datetime_timestamp',
                        //'default' => date( 'Y-m-d 01:00:00' ),

                    ),
                ),
            ),
            // Time Period
            'dynamic_redirect_time_range_fields' => array(
                'name' => __( 'Time Range Redirects', 'shortlinkspro-dynamic-redirects' )
                    . cmb_tooltip_get_html( __( 'Enter target URLs based on the time ranges of the day in which the visitor visits the link. These rules will apply every day based on the hours range defined.', 'shortlinkspro-dynamic-redirects' ) ),
                'desc' => shortlinkspro_dynamic_redirects_conditions_message(),
                'type' => 'group',
                'custom_classes' => 'shortlinkspro-group-field shortlinkspro-group-field-table shortlinkspro-dynamic-redirects-group-field',
                'classes_cb' => 'cmb_conditional_fields_classes_cb',
                'show_if' => array(
                    'dynamic_redirect' => 'time_range',
                ),
                'options'     => array(
                    'add_button'        => __( 'Add', 'shortlinkspro-dynamic-redirects' ),
                    'remove_button'     => '<span class="dashicons dashicons-no-alt"></span>',
                ),
                'fields' => array(
                    'url' => array(
                        'name' => __( 'URL', 'shortlinkspro-dynamic-redirects' ),
                        'type' => 'text',
                        'default' => ''
                    ),
                    'start' => array(
                        'name' => __( 'Start Time', 'shortlinkspro-dynamic-redirects' ) . cmb_tooltip_get_html( __( 'Leave empty for no start time.', 'shortlinkspro-dynamic-redirects' ) ),
                        'type' => 'text_time',
                        //'default' => date( '00:00:00' ),
                    ),
                    'end' => array(
                        'name' => __( 'End Time', 'shortlinkspro-dynamic-redirects' ) . cmb_tooltip_get_html( __( 'Leave empty for no end time.', 'shortlinkspro-dynamic-redirects' ) ),
                        'type' => 'text_time',
                        //'default' => date( '01:00:00' ),

                    ),
                ),
            ),
            // Clicks
            'dynamic_redirect_clicks_fields' => array(
                'name' => __( 'Redirect by Clicks', 'shortlinkspro-dynamic-redirects' )
                    . cmb_tooltip_get_html( __( 'Enter target URLs based on the link\'s clicks.', 'shortlinkspro-dynamic-redirects' ) ),
                'desc' => shortlinkspro_dynamic_redirects_conditions_message(),
                'type' => 'group',
                'custom_classes' => 'shortlinkspro-group-field shortlinkspro-group-field-table shortlinkspro-dynamic-redirects-group-field',
                'classes_cb' => 'cmb_conditional_fields_classes_cb',
                'show_if' => array(
                    'dynamic_redirect' => 'clicks',
                ),
                'options'     => array(
                    'add_button'        => __( 'Add', 'shortlinkspro-dynamic-redirects' ),
                    'remove_button'     => '<span class="dashicons dashicons-no-alt"></span>',
                ),
                'fields' => array(
                    'url' => array(
                        'name' => __( 'URL', 'shortlinkspro-dynamic-redirects' ),
                        'type' => 'text',
                        'default' => ''
                    ),
                    'min' => array(
                        'name' => __( 'Min. Clicks', 'shortlinkspro-dynamic-redirects' ) . cmb_tooltip_get_html( __( 'Leave empty for no minimum number of clicks.', 'shortlinkspro-dynamic-redirects' ) ),
                        'type' => 'text_small',
                        'default' => '0',
                        'attributes' => array(
                            'type' => 'number',
                            'min' => '0',
                            'step' => '1',
                        ),
                    ),
                    'max' => array(
                        'name' => __( 'Max. Clicks', 'shortlinkspro-dynamic-redirects' ) . cmb_tooltip_get_html( __( 'Leave empty for no maximum number of clicks.', 'shortlinkspro-dynamic-redirects' ) ),
                        'type' => 'text_small',
                        'default' => '100',
                        'attributes' => array(
                            'type' => 'number',
                            'min' => '0',
                            'step' => '1',
                        ),
                    ),
                ),
            ),
        ),
        array(

        )
    );
}

add_action( 'cmb2_init', 'shortlinkspro_dynamic_redirects_links_meta_boxes' );

function shortlinkspro_dynamic_redirects_conditions_message() {
    return __( 'If conditions are not meet, the redirect will fallback to the link\'s target URL.', 'shortlinkspro-dynamic-redirects' );
}