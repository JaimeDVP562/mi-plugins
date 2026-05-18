<?php
/**
 * Functions
 *
 * @package     ShortLinksPro\Functions
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Redirect types
 *
 * @since 1.0.0
 *
 * @return array
 */
function shortlinkspro_redirect_types() {

    return apply_filters( 'shortlinkspro_redirect_types', array(
        '307' => '307 (Temporary)',
        '302' => '302 (Temporary)',
        '301' => '301 (Permanent)',
    ) );

}

/**
 * Generates the required HTML with the dashicon provided
 *
 * @since 1.0.0
 *
 * @param string $dashicon      Dashicon class
 * @param string $tag           Optional, tag used (recommended i or span)
 *
 * @return string
 */
function shortlinkspro_dashicon( $dashicon = 'shortlinkspro', $tag = 'i' ) {

    return '<' . $tag . ' class="dashicons dashicons-' . $dashicon . '"></' . $tag . '>';

}

/**
 * Generates the required HTML for the UTM Builder
 *
 * @since 1.0.0
 *
 */
function shortlinkspro_utm_builder() {

    // Setup the CMB2 form
    $cmb2 = new CMB2( array(
        'id'        => 'shortlinkspro_utm_builder',
        'object_types' => array( 'shortlinkspro_links' ),
        'classes'   => 'shortlinkspro-form shortlinkspro-utm-builder-form',
        'hookup'    => false,
    ), 0 );

    $fields = array(
        'utm_campaign' => array(
            'name' => __('Campaign', 'shortlinkspro'),
            'attributes' => array(
                'placeholder' => __('eg: campaign-name', 'shortlinkspro'),
                'name' => '',
            ),
            'type' => 'text',
        ),
        'utm_medium' => array(
            'name' => __('Medium', 'shortlinkspro'),
            'attributes' => array(
                'placeholder' => __('eg: email', 'shortlinkspro'),
                'name' => '',
            ),
            'type' => 'text',
        ),
        'utm_source' => array(
            'name' => __('Source', 'shortlinkspro'),
            'attributes' => array(
                'placeholder' => __('eg: website', 'shortlinkspro'),
                'name' => '',
            ),
            'type' => 'text',
        ),
        'utm_term' => array(
            'name' => __('Term', 'shortlinkspro'),
            'attributes' => array(
                'placeholder' => __('eg: keyword', 'shortlinkspro'),
                'name' => '',
            ),
            'type' => 'text',
        ),
        'utm_content' => array(
            'name' => __('Content', 'shortlinkspro'),
            //'desc' => __('The field name.', 'shortlinkspro'),
            'attributes' => array(
                'placeholder' => __('eg: text', 'shortlinkspro'),
                'name' => '',
            ),
            'type' => 'text',
        ),
    );

    foreach( $fields as $field_id => $field ) {

        $field['id'] = $field_id;

        $cmb2->add_field( $field );

    }

    ?>
    <a href="#" class="shortlinkspro-utm-builder button button-primary"><?php echo esc_html( __( 'UTM', 'shortlinkspro' ) ); ?></a>
    <div class="shortlinkspro-utm-builder-dialog-wrapper" style="display: none;">
        <div class="shortlinkspro-utm-builder-dialog">
            <h2 class="shortlinkspro-dialog-title shortlinkspro-utm-builder-dialog-title">
                <?php esc_html_e( 'UTM Builder', 'shortlinkspro' ); ?>
                <?php cmb_tooltip_html( __( 'Helper tool to add campaign parameters for tracking.', 'shortlinkspro' ) ); ?>
            </h2>
            <?php // Render the form
            $cmb2->show_form(); ?>
            <div class="shortlinkspro-dialog-bottom">
                <button type="button" class="button button-primary button-large shortlinkspro-utm-builder-dialog-save"><?php esc_html_e( 'Save Changes', 'shortlinkspro' ); ?></button>
                <button type="button" class="button button-large shortlinkspro-utm-builder-dialog-cancel"><?php esc_html_e( 'Cancel', 'shortlinkspro' ); ?></button>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Generates the required HTML for the copy to clipboard
 *
 * @since 1.0.0
 *
 * @param string $url      URL to copy
 */
function shortlinkspro_copy_to_clipboard( $url = '' ) {
    ?>

    <span class="shortlinkspro-copy-to-clipboard" data-url="<?php echo ( ! empty( $url ) ? esc_attr($url) : '' ); ?>">
        <?php echo cmb_tooltip_get_html( esc_html__( 'Copy to clipboard', 'shortlinkspro' ), 'clipboard' ); ?>
    </span>

    <?php
}

/**
 * Generates the required HTML with the dashicon provided
 *
 * @since 1.0.0
 *
 * @param string $dashicon      Dashicon class
 * @param string $tag           Optional, tag used (recommended i or span)
 *
 * @return string
 */
function shortlinkspro_sanitize_request_uri( $request_uri ) {

    $request_uri = wp_unslash( $request_uri );
    $request_uri = stripslashes( rawurldecode( $request_uri ) );
    $request_uri = substr( $request_uri, strlen( wp_parse_url( site_url( '/' ), PHP_URL_PATH ) ) );

    return $request_uri;

}

/**
 * Get the full list of countries codes.
 *
 * @since 1.0.0
 *
 * @return array
 */
function shortlinkspro_get_countries() {
    static $shortlinkspro_countries;

    if ( ! isset( $shortlinkspro_countries ) ) {
        $shortlinkspro_countries = array_unique(
            apply_filters( 'shortlinkspro_countries',
                array(
                    'AF' => __( 'Afghanistan', 'shortlinkspro' ),
                    'AX' => __( '&#197;land Islands', 'shortlinkspro' ),
                    'AL' => __( 'Albania', 'shortlinkspro' ),
                    'DZ' => __( 'Algeria', 'shortlinkspro' ),
                    'AS' => __( 'American Samoa', 'shortlinkspro' ),
                    'AD' => __( 'Andorra', 'shortlinkspro' ),
                    'AO' => __( 'Angola', 'shortlinkspro' ),
                    'AI' => __( 'Anguilla', 'shortlinkspro' ),
                    'AQ' => __( 'Antarctica', 'shortlinkspro' ),
                    'AG' => __( 'Antigua and Barbuda', 'shortlinkspro' ),
                    'AR' => __( 'Argentina', 'shortlinkspro' ),
                    'AM' => __( 'Armenia', 'shortlinkspro' ),
                    'AW' => __( 'Aruba', 'shortlinkspro' ),
                    'AU' => __( 'Australia', 'shortlinkspro' ),
                    'AT' => __( 'Austria', 'shortlinkspro' ),
                    'AZ' => __( 'Azerbaijan', 'shortlinkspro' ),
                    'BS' => __( 'Bahamas', 'shortlinkspro' ),
                    'BH' => __( 'Bahrain', 'shortlinkspro' ),
                    'BD' => __( 'Bangladesh', 'shortlinkspro' ),
                    'BB' => __( 'Barbados', 'shortlinkspro' ),
                    'BY' => __( 'Belarus', 'shortlinkspro' ),
                    'BE' => __( 'Belgium', 'shortlinkspro' ),
                    'PW' => __( 'Belau', 'shortlinkspro' ),
                    'BZ' => __( 'Belize', 'shortlinkspro' ),
                    'BJ' => __( 'Benin', 'shortlinkspro' ),
                    'BM' => __( 'Bermuda', 'shortlinkspro' ),
                    'BT' => __( 'Bhutan', 'shortlinkspro' ),
                    'BO' => __( 'Bolivia', 'shortlinkspro' ),
                    'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'shortlinkspro' ),
                    'BA' => __( 'Bosnia and Herzegovina', 'shortlinkspro' ),
                    'BW' => __( 'Botswana', 'shortlinkspro' ),
                    'BV' => __( 'Bouvet Island', 'shortlinkspro' ),
                    'BR' => __( 'Brazil', 'shortlinkspro' ),
                    'IO' => __( 'British Indian Ocean Territory', 'shortlinkspro' ),
                    'VG' => __( 'British Virgin Islands', 'shortlinkspro' ),
                    'BN' => __( 'Brunei', 'shortlinkspro' ),
                    'BG' => __( 'Bulgaria', 'shortlinkspro' ),
                    'BF' => __( 'Burkina Faso', 'shortlinkspro' ),
                    'BI' => __( 'Burundi', 'shortlinkspro' ),
                    'KH' => __( 'Cambodia', 'shortlinkspro' ),
                    'CM' => __( 'Cameroon', 'shortlinkspro' ),
                    'CA' => __( 'Canada', 'shortlinkspro' ),
                    'CV' => __( 'Cape Verde', 'shortlinkspro' ),
                    'KY' => __( 'Cayman Islands', 'shortlinkspro' ),
                    'CF' => __( 'Central African Republic', 'shortlinkspro' ),
                    'TD' => __( 'Chad', 'shortlinkspro' ),
                    'CL' => __( 'Chile', 'shortlinkspro' ),
                    'CN' => __( 'China', 'shortlinkspro' ),
                    'CX' => __( 'Christmas Island', 'shortlinkspro' ),
                    'CC' => __( 'Cocos (Keeling) Islands', 'shortlinkspro' ),
                    'CO' => __( 'Colombia', 'shortlinkspro' ),
                    'KM' => __( 'Comoros', 'shortlinkspro' ),
                    'CG' => __( 'Congo (Brazzaville)', 'shortlinkspro' ),
                    'CD' => __( 'Congo (Kinshasa)', 'shortlinkspro' ),
                    'CK' => __( 'Cook Islands', 'shortlinkspro' ),
                    'CR' => __( 'Costa Rica', 'shortlinkspro' ),
                    'HR' => __( 'Croatia', 'shortlinkspro' ),
                    'CU' => __( 'Cuba', 'shortlinkspro' ),
                    'CW' => __( 'Cura&ccedil;ao', 'shortlinkspro' ),
                    'CY' => __( 'Cyprus', 'shortlinkspro' ),
                    'CZ' => __( 'Czech Republic', 'shortlinkspro' ),
                    'DK' => __( 'Denmark', 'shortlinkspro' ),
                    'DJ' => __( 'Djibouti', 'shortlinkspro' ),
                    'DM' => __( 'Dominica', 'shortlinkspro' ),
                    'DO' => __( 'Dominican Republic', 'shortlinkspro' ),
                    'EC' => __( 'Ecuador', 'shortlinkspro' ),
                    'EG' => __( 'Egypt', 'shortlinkspro' ),
                    'SV' => __( 'El Salvador', 'shortlinkspro' ),
                    'GQ' => __( 'Equatorial Guinea', 'shortlinkspro' ),
                    'ER' => __( 'Eritrea', 'shortlinkspro' ),
                    'EE' => __( 'Estonia', 'shortlinkspro' ),
                    'ET' => __( 'Ethiopia', 'shortlinkspro' ),
                    'FK' => __( 'Falkland Islands', 'shortlinkspro' ),
                    'FO' => __( 'Faroe Islands', 'shortlinkspro' ),
                    'FJ' => __( 'Fiji', 'shortlinkspro' ),
                    'FI' => __( 'Finland', 'shortlinkspro' ),
                    'FR' => __( 'France', 'shortlinkspro' ),
                    'GF' => __( 'French Guiana', 'shortlinkspro' ),
                    'PF' => __( 'French Polynesia', 'shortlinkspro' ),
                    'TF' => __( 'French Southern Territories', 'shortlinkspro' ),
                    'GA' => __( 'Gabon', 'shortlinkspro' ),
                    'GM' => __( 'Gambia', 'shortlinkspro' ),
                    'GE' => __( 'Georgia', 'shortlinkspro' ),
                    'DE' => __( 'Germany', 'shortlinkspro' ),
                    'GH' => __( 'Ghana', 'shortlinkspro' ),
                    'GI' => __( 'Gibraltar', 'shortlinkspro' ),
                    'GR' => __( 'Greece', 'shortlinkspro' ),
                    'GL' => __( 'Greenland', 'shortlinkspro' ),
                    'GD' => __( 'Grenada', 'shortlinkspro' ),
                    'GP' => __( 'Guadeloupe', 'shortlinkspro' ),
                    'GU' => __( 'Guam', 'shortlinkspro' ),
                    'GT' => __( 'Guatemala', 'shortlinkspro' ),
                    'GG' => __( 'Guernsey', 'shortlinkspro' ),
                    'GN' => __( 'Guinea', 'shortlinkspro' ),
                    'GW' => __( 'Guinea-Bissau', 'shortlinkspro' ),
                    'GY' => __( 'Guyana', 'shortlinkspro' ),
                    'HT' => __( 'Haiti', 'shortlinkspro' ),
                    'HM' => __( 'Heard Island and McDonald Islands', 'shortlinkspro' ),
                    'HN' => __( 'Honduras', 'shortlinkspro' ),
                    'HK' => __( 'Hong Kong', 'shortlinkspro' ),
                    'HU' => __( 'Hungary', 'shortlinkspro' ),
                    'IS' => __( 'Iceland', 'shortlinkspro' ),
                    'IN' => __( 'India', 'shortlinkspro' ),
                    'ID' => __( 'Indonesia', 'shortlinkspro' ),
                    'IR' => __( 'Iran', 'shortlinkspro' ),
                    'IQ' => __( 'Iraq', 'shortlinkspro' ),
                    'IE' => __( 'Ireland', 'shortlinkspro' ),
                    'IM' => __( 'Isle of Man', 'shortlinkspro' ),
                    'IL' => __( 'Israel', 'shortlinkspro' ),
                    'IT' => __( 'Italy', 'shortlinkspro' ),
                    'CI' => __( 'Ivory Coast', 'shortlinkspro' ),
                    'JM' => __( 'Jamaica', 'shortlinkspro' ),
                    'JP' => __( 'Japan', 'shortlinkspro' ),
                    'JE' => __( 'Jersey', 'shortlinkspro' ),
                    'JO' => __( 'Jordan', 'shortlinkspro' ),
                    'KZ' => __( 'Kazakhstan', 'shortlinkspro' ),
                    'KE' => __( 'Kenya', 'shortlinkspro' ),
                    'KI' => __( 'Kiribati', 'shortlinkspro' ),
                    'KW' => __( 'Kuwait', 'shortlinkspro' ),
                    'KG' => __( 'Kyrgyzstan', 'shortlinkspro' ),
                    'LA' => __( 'Laos', 'shortlinkspro' ),
                    'LV' => __( 'Latvia', 'shortlinkspro' ),
                    'LB' => __( 'Lebanon', 'shortlinkspro' ),
                    'LS' => __( 'Lesotho', 'shortlinkspro' ),
                    'LR' => __( 'Liberia', 'shortlinkspro' ),
                    'LY' => __( 'Libya', 'shortlinkspro' ),
                    'LI' => __( 'Liechtenstein', 'shortlinkspro' ),
                    'LT' => __( 'Lithuania', 'shortlinkspro' ),
                    'LU' => __( 'Luxembourg', 'shortlinkspro' ),
                    'MO' => __( 'Macao S.A.R., China', 'shortlinkspro' ),
                    'MK' => __( 'Macedonia', 'shortlinkspro' ),
                    'MG' => __( 'Madagascar', 'shortlinkspro' ),
                    'MW' => __( 'Malawi', 'shortlinkspro' ),
                    'MY' => __( 'Malaysia', 'shortlinkspro' ),
                    'MV' => __( 'Maldives', 'shortlinkspro' ),
                    'ML' => __( 'Mali', 'shortlinkspro' ),
                    'MT' => __( 'Malta', 'shortlinkspro' ),
                    'MH' => __( 'Marshall Islands', 'shortlinkspro' ),
                    'MQ' => __( 'Martinique', 'shortlinkspro' ),
                    'MR' => __( 'Mauritania', 'shortlinkspro' ),
                    'MU' => __( 'Mauritius', 'shortlinkspro' ),
                    'YT' => __( 'Mayotte', 'shortlinkspro' ),
                    'MX' => __( 'Mexico', 'shortlinkspro' ),
                    'FM' => __( 'Micronesia', 'shortlinkspro' ),
                    'MD' => __( 'Moldova', 'shortlinkspro' ),
                    'MC' => __( 'Monaco', 'shortlinkspro' ),
                    'MN' => __( 'Mongolia', 'shortlinkspro' ),
                    'ME' => __( 'Montenegro', 'shortlinkspro' ),
                    'MS' => __( 'Montserrat', 'shortlinkspro' ),
                    'MA' => __( 'Morocco', 'shortlinkspro' ),
                    'MZ' => __( 'Mozambique', 'shortlinkspro' ),
                    'MM' => __( 'Myanmar', 'shortlinkspro' ),
                    'NA' => __( 'Namibia', 'shortlinkspro' ),
                    'NR' => __( 'Nauru', 'shortlinkspro' ),
                    'NP' => __( 'Nepal', 'shortlinkspro' ),
                    'NL' => __( 'Netherlands', 'shortlinkspro' ),
                    'NC' => __( 'New Caledonia', 'shortlinkspro' ),
                    'NZ' => __( 'New Zealand', 'shortlinkspro' ),
                    'NI' => __( 'Nicaragua', 'shortlinkspro' ),
                    'NE' => __( 'Niger', 'shortlinkspro' ),
                    'NG' => __( 'Nigeria', 'shortlinkspro' ),
                    'NU' => __( 'Niue', 'shortlinkspro' ),
                    'NF' => __( 'Norfolk Island', 'shortlinkspro' ),
                    'MP' => __( 'Northern Mariana Islands', 'shortlinkspro' ),
                    'KP' => __( 'North Korea', 'shortlinkspro' ),
                    'NO' => __( 'Norway', 'shortlinkspro' ),
                    'OM' => __( 'Oman', 'shortlinkspro' ),
                    'PK' => __( 'Pakistan', 'shortlinkspro' ),
                    'PS' => __( 'Palestinian Territory', 'shortlinkspro' ),
                    'PA' => __( 'Panama', 'shortlinkspro' ),
                    'PG' => __( 'Papua New Guinea', 'shortlinkspro' ),
                    'PY' => __( 'Paraguay', 'shortlinkspro' ),
                    'PE' => __( 'Peru', 'shortlinkspro' ),
                    'PH' => __( 'Philippines', 'shortlinkspro' ),
                    'PN' => __( 'Pitcairn', 'shortlinkspro' ),
                    'PL' => __( 'Poland', 'shortlinkspro' ),
                    'PT' => __( 'Portugal', 'shortlinkspro' ),
                    'PR' => __( 'Puerto Rico', 'shortlinkspro' ),
                    'QA' => __( 'Qatar', 'shortlinkspro' ),
                    'RE' => __( 'Reunion', 'shortlinkspro' ),
                    'RO' => __( 'Romania', 'shortlinkspro' ),
                    'RU' => __( 'Russia', 'shortlinkspro' ),
                    'RW' => __( 'Rwanda', 'shortlinkspro' ),
                    'BL' => __( 'Saint Barth&eacute;lemy', 'shortlinkspro' ),
                    'SH' => __( 'Saint Helena', 'shortlinkspro' ),
                    'KN' => __( 'Saint Kitts and Nevis', 'shortlinkspro' ),
                    'LC' => __( 'Saint Lucia', 'shortlinkspro' ),
                    'MF' => __( 'Saint Martin (French part)', 'shortlinkspro' ),
                    'SX' => __( 'Saint Martin (Dutch part)', 'shortlinkspro' ),
                    'PM' => __( 'Saint Pierre and Miquelon', 'shortlinkspro' ),
                    'VC' => __( 'Saint Vincent and the Grenadines', 'shortlinkspro' ),
                    'SM' => __( 'San Marino', 'shortlinkspro' ),
                    'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'shortlinkspro' ),
                    'SA' => __( 'Saudi Arabia', 'shortlinkspro' ),
                    'SN' => __( 'Senegal', 'shortlinkspro' ),
                    'RS' => __( 'Serbia', 'shortlinkspro' ),
                    'SC' => __( 'Seychelles', 'shortlinkspro' ),
                    'SL' => __( 'Sierra Leone', 'shortlinkspro' ),
                    'SG' => __( 'Singapore', 'shortlinkspro' ),
                    'SK' => __( 'Slovakia', 'shortlinkspro' ),
                    'SI' => __( 'Slovenia', 'shortlinkspro' ),
                    'SB' => __( 'Solomon Islands', 'shortlinkspro' ),
                    'SO' => __( 'Somalia', 'shortlinkspro' ),
                    'ZA' => __( 'South Africa', 'shortlinkspro' ),
                    'GS' => __( 'South Georgia/Sandwich Islands', 'shortlinkspro' ),
                    'KR' => __( 'South Korea', 'shortlinkspro' ),
                    'SS' => __( 'South Sudan', 'shortlinkspro' ),
                    'ES' => __( 'Spain', 'shortlinkspro' ),
                    'LK' => __( 'Sri Lanka', 'shortlinkspro' ),
                    'SD' => __( 'Sudan', 'shortlinkspro' ),
                    'SR' => __( 'Suriname', 'shortlinkspro' ),
                    'SJ' => __( 'Svalbard and Jan Mayen', 'shortlinkspro' ),
                    'SZ' => __( 'Swaziland', 'shortlinkspro' ),
                    'SE' => __( 'Sweden', 'shortlinkspro' ),
                    'CH' => __( 'Switzerland', 'shortlinkspro' ),
                    'SY' => __( 'Syria', 'shortlinkspro' ),
                    'TW' => __( 'Taiwan', 'shortlinkspro' ),
                    'TJ' => __( 'Tajikistan', 'shortlinkspro' ),
                    'TZ' => __( 'Tanzania', 'shortlinkspro' ),
                    'TH' => __( 'Thailand', 'shortlinkspro' ),
                    'TL' => __( 'Timor-Leste', 'shortlinkspro' ),
                    'TG' => __( 'Togo', 'shortlinkspro' ),
                    'TK' => __( 'Tokelau', 'shortlinkspro' ),
                    'TO' => __( 'Tonga', 'shortlinkspro' ),
                    'TT' => __( 'Trinidad and Tobago', 'shortlinkspro' ),
                    'TN' => __( 'Tunisia', 'shortlinkspro' ),
                    'TR' => __( 'Turkey', 'shortlinkspro' ),
                    'TM' => __( 'Turkmenistan', 'shortlinkspro' ),
                    'TC' => __( 'Turks and Caicos Islands', 'shortlinkspro' ),
                    'TV' => __( 'Tuvalu', 'shortlinkspro' ),
                    'UG' => __( 'Uganda', 'shortlinkspro' ),
                    'UA' => __( 'Ukraine', 'shortlinkspro' ),
                    'AE' => __( 'United Arab Emirates', 'shortlinkspro' ),
                    'GB' => __( 'United Kingdom (UK)', 'shortlinkspro' ),
                    'US' => __( 'United States (US)', 'shortlinkspro' ),
                    'UM' => __( 'United States (US) Minor Outlying Islands', 'shortlinkspro' ),
                    'VI' => __( 'United States (US) Virgin Islands', 'shortlinkspro' ),
                    'UY' => __( 'Uruguay', 'shortlinkspro' ),
                    'UZ' => __( 'Uzbekistan', 'shortlinkspro' ),
                    'VU' => __( 'Vanuatu', 'shortlinkspro' ),
                    'VA' => __( 'Vatican', 'shortlinkspro' ),
                    'VE' => __( 'Venezuela', 'shortlinkspro' ),
                    'VN' => __( 'Vietnam', 'shortlinkspro' ),
                    'WF' => __( 'Wallis and Futuna', 'shortlinkspro' ),
                    'EH' => __( 'Western Sahara', 'shortlinkspro' ),
                    'WS' => __( 'Samoa', 'shortlinkspro' ),
                    'YE' => __( 'Yemen', 'shortlinkspro' ),
                    'ZM' => __( 'Zambia', 'shortlinkspro' ),
                    'ZW' => __( 'Zimbabwe', 'shortlinkspro' ),
                )
            )
        );

    }

    return $shortlinkspro_countries;
}

/**
 * Returns the country name
 *
 * @since 1.0.0
 *
 * @param string $country_code
 *
 * @return string
 */
function shortlinkspro_get_country_name( $country_code ) {

    $countries = shortlinkspro_get_countries();

    return ( isset( $countries[$country_code] ) ? $countries[$country_code] : $country_code );

}

/**
 * Returns the country flag HTML
 *
 * @since 1.0.0
 *
 * @param string $country_code
 *
 * @return string
 */
function shortlinkspro_get_country_flag( $country_code ) {

    $country_code = strtolower( str_replace('_', '-', $country_code ) );

    return "<span class='flag flag-{$country_code}'></span>";

}

/**
 * Returns the country flag emoji
 *
 * @since 1.0.0
 *
 * @param string $country_code
 *
 * @return string
 */
function shortlinkspro_get_country_emoji( $country_code ) {

    $unicodePrefix = "\xF0\x9F\x87";
    $unicodeAdditionForLowerCase = 0x45;
    $unicodeAdditionForUpperCase = 0x65;

    if (preg_match('/^[A-Z]{2}$/', $country_code)) {
        $emoji = $unicodePrefix . chr(ord($country_code[0]) + $unicodeAdditionForUpperCase)
            . $unicodePrefix . chr(ord($country_code[1]) + $unicodeAdditionForUpperCase);
    } elseif (preg_match('/^[a-z]{2}$/', $country_code)) {
        $emoji = $unicodePrefix . chr(ord($country_code[0]) + $unicodeAdditionForLowerCase)
            . $unicodePrefix . chr(ord($country_code[1]) + $unicodeAdditionForLowerCase);
    } else {
        $emoji = '';
    }

    return strlen($emoji) ? $emoji : '';

}