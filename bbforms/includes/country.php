<?php
/**
 * Country
 *
 * @package     BBForms\Country
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the full list of countries codes.
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_countries() {
    static $bbforms_countries;

    if ( ! isset( $bbforms_countries ) ) {
        $bbforms_countries = array_unique(
            apply_filters( 'bbforms_countries',
                array(
                    'AF' => __( 'Afghanistan', 'bbforms' ),
                    'AX' => __( '&#197;land Islands', 'bbforms' ),
                    'AL' => __( 'Albania', 'bbforms' ),
                    'DZ' => __( 'Algeria', 'bbforms' ),
                    'AS' => __( 'American Samoa', 'bbforms' ),
                    'AD' => __( 'Andorra', 'bbforms' ),
                    'AO' => __( 'Angola', 'bbforms' ),
                    'AI' => __( 'Anguilla', 'bbforms' ),
                    'AQ' => __( 'Antarctica', 'bbforms' ),
                    'AG' => __( 'Antigua and Barbuda', 'bbforms' ),
                    'AR' => __( 'Argentina', 'bbforms' ),
                    'AM' => __( 'Armenia', 'bbforms' ),
                    'AW' => __( 'Aruba', 'bbforms' ),
                    'AU' => __( 'Australia', 'bbforms' ),
                    'AT' => __( 'Austria', 'bbforms' ),
                    'AZ' => __( 'Azerbaijan', 'bbforms' ),
                    'BS' => __( 'Bahamas', 'bbforms' ),
                    'BH' => __( 'Bahrain', 'bbforms' ),
                    'BD' => __( 'Bangladesh', 'bbforms' ),
                    'BB' => __( 'Barbados', 'bbforms' ),
                    'BY' => __( 'Belarus', 'bbforms' ),
                    'BE' => __( 'Belgium', 'bbforms' ),
                    'PW' => __( 'Belau', 'bbforms' ),
                    'BZ' => __( 'Belize', 'bbforms' ),
                    'BJ' => __( 'Benin', 'bbforms' ),
                    'BM' => __( 'Bermuda', 'bbforms' ),
                    'BT' => __( 'Bhutan', 'bbforms' ),
                    'BO' => __( 'Bolivia', 'bbforms' ),
                    'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'bbforms' ),
                    'BA' => __( 'Bosnia and Herzegovina', 'bbforms' ),
                    'BW' => __( 'Botswana', 'bbforms' ),
                    'BV' => __( 'Bouvet Island', 'bbforms' ),
                    'BR' => __( 'Brazil', 'bbforms' ),
                    'IO' => __( 'British Indian Ocean Territory', 'bbforms' ),
                    'VG' => __( 'British Virgin Islands', 'bbforms' ),
                    'BN' => __( 'Brunei', 'bbforms' ),
                    'BG' => __( 'Bulgaria', 'bbforms' ),
                    'BF' => __( 'Burkina Faso', 'bbforms' ),
                    'BI' => __( 'Burundi', 'bbforms' ),
                    'KH' => __( 'Cambodia', 'bbforms' ),
                    'CM' => __( 'Cameroon', 'bbforms' ),
                    'CA' => __( 'Canada', 'bbforms' ),
                    'CV' => __( 'Cape Verde', 'bbforms' ),
                    'KY' => __( 'Cayman Islands', 'bbforms' ),
                    'CF' => __( 'Central African Republic', 'bbforms' ),
                    'TD' => __( 'Chad', 'bbforms' ),
                    'CL' => __( 'Chile', 'bbforms' ),
                    'CN' => __( 'China', 'bbforms' ),
                    'CX' => __( 'Christmas Island', 'bbforms' ),
                    'CC' => __( 'Cocos (Keeling) Islands', 'bbforms' ),
                    'CO' => __( 'Colombia', 'bbforms' ),
                    'KM' => __( 'Comoros', 'bbforms' ),
                    'CG' => __( 'Congo (Brazzaville)', 'bbforms' ),
                    'CD' => __( 'Congo (Kinshasa)', 'bbforms' ),
                    'CK' => __( 'Cook Islands', 'bbforms' ),
                    'CR' => __( 'Costa Rica', 'bbforms' ),
                    'HR' => __( 'Croatia', 'bbforms' ),
                    'CU' => __( 'Cuba', 'bbforms' ),
                    'CW' => __( 'Cura&ccedil;ao', 'bbforms' ),
                    'CY' => __( 'Cyprus', 'bbforms' ),
                    'CZ' => __( 'Czech Republic', 'bbforms' ),
                    'DK' => __( 'Denmark', 'bbforms' ),
                    'DJ' => __( 'Djibouti', 'bbforms' ),
                    'DM' => __( 'Dominica', 'bbforms' ),
                    'DO' => __( 'Dominican Republic', 'bbforms' ),
                    'EC' => __( 'Ecuador', 'bbforms' ),
                    'EG' => __( 'Egypt', 'bbforms' ),
                    'SV' => __( 'El Salvador', 'bbforms' ),
                    'GQ' => __( 'Equatorial Guinea', 'bbforms' ),
                    'ER' => __( 'Eritrea', 'bbforms' ),
                    'EE' => __( 'Estonia', 'bbforms' ),
                    'ET' => __( 'Ethiopia', 'bbforms' ),
                    'FK' => __( 'Falkland Islands', 'bbforms' ),
                    'FO' => __( 'Faroe Islands', 'bbforms' ),
                    'FJ' => __( 'Fiji', 'bbforms' ),
                    'FI' => __( 'Finland', 'bbforms' ),
                    'FR' => __( 'France', 'bbforms' ),
                    'GF' => __( 'French Guiana', 'bbforms' ),
                    'PF' => __( 'French Polynesia', 'bbforms' ),
                    'TF' => __( 'French Southern Territories', 'bbforms' ),
                    'GA' => __( 'Gabon', 'bbforms' ),
                    'GM' => __( 'Gambia', 'bbforms' ),
                    'GE' => __( 'Georgia', 'bbforms' ),
                    'DE' => __( 'Germany', 'bbforms' ),
                    'GH' => __( 'Ghana', 'bbforms' ),
                    'GI' => __( 'Gibraltar', 'bbforms' ),
                    'GR' => __( 'Greece', 'bbforms' ),
                    'GL' => __( 'Greenland', 'bbforms' ),
                    'GD' => __( 'Grenada', 'bbforms' ),
                    'GP' => __( 'Guadeloupe', 'bbforms' ),
                    'GU' => __( 'Guam', 'bbforms' ),
                    'GT' => __( 'Guatemala', 'bbforms' ),
                    'GG' => __( 'Guernsey', 'bbforms' ),
                    'GN' => __( 'Guinea', 'bbforms' ),
                    'GW' => __( 'Guinea-Bissau', 'bbforms' ),
                    'GY' => __( 'Guyana', 'bbforms' ),
                    'HT' => __( 'Haiti', 'bbforms' ),
                    'HM' => __( 'Heard Island and McDonald Islands', 'bbforms' ),
                    'HN' => __( 'Honduras', 'bbforms' ),
                    'HK' => __( 'Hong Kong', 'bbforms' ),
                    'HU' => __( 'Hungary', 'bbforms' ),
                    'IS' => __( 'Iceland', 'bbforms' ),
                    'IN' => __( 'India', 'bbforms' ),
                    'ID' => __( 'Indonesia', 'bbforms' ),
                    'IR' => __( 'Iran', 'bbforms' ),
                    'IQ' => __( 'Iraq', 'bbforms' ),
                    'IE' => __( 'Ireland', 'bbforms' ),
                    'IM' => __( 'Isle of Man', 'bbforms' ),
                    'IL' => __( 'Israel', 'bbforms' ),
                    'IT' => __( 'Italy', 'bbforms' ),
                    'CI' => __( 'Ivory Coast', 'bbforms' ),
                    'JM' => __( 'Jamaica', 'bbforms' ),
                    'JP' => __( 'Japan', 'bbforms' ),
                    'JE' => __( 'Jersey', 'bbforms' ),
                    'JO' => __( 'Jordan', 'bbforms' ),
                    'KZ' => __( 'Kazakhstan', 'bbforms' ),
                    'KE' => __( 'Kenya', 'bbforms' ),
                    'KI' => __( 'Kiribati', 'bbforms' ),
                    'KW' => __( 'Kuwait', 'bbforms' ),
                    'KG' => __( 'Kyrgyzstan', 'bbforms' ),
                    'LA' => __( 'Laos', 'bbforms' ),
                    'LV' => __( 'Latvia', 'bbforms' ),
                    'LB' => __( 'Lebanon', 'bbforms' ),
                    'LS' => __( 'Lesotho', 'bbforms' ),
                    'LR' => __( 'Liberia', 'bbforms' ),
                    'LY' => __( 'Libya', 'bbforms' ),
                    'LI' => __( 'Liechtenstein', 'bbforms' ),
                    'LT' => __( 'Lithuania', 'bbforms' ),
                    'LU' => __( 'Luxembourg', 'bbforms' ),
                    'MO' => __( 'Macao S.A.R., China', 'bbforms' ),
                    'MK' => __( 'Macedonia', 'bbforms' ),
                    'MG' => __( 'Madagascar', 'bbforms' ),
                    'MW' => __( 'Malawi', 'bbforms' ),
                    'MY' => __( 'Malaysia', 'bbforms' ),
                    'MV' => __( 'Maldives', 'bbforms' ),
                    'ML' => __( 'Mali', 'bbforms' ),
                    'MT' => __( 'Malta', 'bbforms' ),
                    'MH' => __( 'Marshall Islands', 'bbforms' ),
                    'MQ' => __( 'Martinique', 'bbforms' ),
                    'MR' => __( 'Mauritania', 'bbforms' ),
                    'MU' => __( 'Mauritius', 'bbforms' ),
                    'YT' => __( 'Mayotte', 'bbforms' ),
                    'MX' => __( 'Mexico', 'bbforms' ),
                    'FM' => __( 'Micronesia', 'bbforms' ),
                    'MD' => __( 'Moldova', 'bbforms' ),
                    'MC' => __( 'Monaco', 'bbforms' ),
                    'MN' => __( 'Mongolia', 'bbforms' ),
                    'ME' => __( 'Montenegro', 'bbforms' ),
                    'MS' => __( 'Montserrat', 'bbforms' ),
                    'MA' => __( 'Morocco', 'bbforms' ),
                    'MZ' => __( 'Mozambique', 'bbforms' ),
                    'MM' => __( 'Myanmar', 'bbforms' ),
                    'NA' => __( 'Namibia', 'bbforms' ),
                    'NR' => __( 'Nauru', 'bbforms' ),
                    'NP' => __( 'Nepal', 'bbforms' ),
                    'NL' => __( 'Netherlands', 'bbforms' ),
                    'NC' => __( 'New Caledonia', 'bbforms' ),
                    'NZ' => __( 'New Zealand', 'bbforms' ),
                    'NI' => __( 'Nicaragua', 'bbforms' ),
                    'NE' => __( 'Niger', 'bbforms' ),
                    'NG' => __( 'Nigeria', 'bbforms' ),
                    'NU' => __( 'Niue', 'bbforms' ),
                    'NF' => __( 'Norfolk Island', 'bbforms' ),
                    'MP' => __( 'Northern Mariana Islands', 'bbforms' ),
                    'KP' => __( 'North Korea', 'bbforms' ),
                    'NO' => __( 'Norway', 'bbforms' ),
                    'OM' => __( 'Oman', 'bbforms' ),
                    'PK' => __( 'Pakistan', 'bbforms' ),
                    'PS' => __( 'Palestinian Territory', 'bbforms' ),
                    'PA' => __( 'Panama', 'bbforms' ),
                    'PG' => __( 'Papua New Guinea', 'bbforms' ),
                    'PY' => __( 'Paraguay', 'bbforms' ),
                    'PE' => __( 'Peru', 'bbforms' ),
                    'PH' => __( 'Philippines', 'bbforms' ),
                    'PN' => __( 'Pitcairn', 'bbforms' ),
                    'PL' => __( 'Poland', 'bbforms' ),
                    'PT' => __( 'Portugal', 'bbforms' ),
                    'PR' => __( 'Puerto Rico', 'bbforms' ),
                    'QA' => __( 'Qatar', 'bbforms' ),
                    'RE' => __( 'Reunion', 'bbforms' ),
                    'RO' => __( 'Romania', 'bbforms' ),
                    'RU' => __( 'Russia', 'bbforms' ),
                    'RW' => __( 'Rwanda', 'bbforms' ),
                    'BL' => __( 'Saint Barth&eacute;lemy', 'bbforms' ),
                    'SH' => __( 'Saint Helena', 'bbforms' ),
                    'KN' => __( 'Saint Kitts and Nevis', 'bbforms' ),
                    'LC' => __( 'Saint Lucia', 'bbforms' ),
                    'MF' => __( 'Saint Martin (French part)', 'bbforms' ),
                    'SX' => __( 'Saint Martin (Dutch part)', 'bbforms' ),
                    'PM' => __( 'Saint Pierre and Miquelon', 'bbforms' ),
                    'VC' => __( 'Saint Vincent and the Grenadines', 'bbforms' ),
                    'SM' => __( 'San Marino', 'bbforms' ),
                    'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'bbforms' ),
                    'SA' => __( 'Saudi Arabia', 'bbforms' ),
                    'SN' => __( 'Senegal', 'bbforms' ),
                    'RS' => __( 'Serbia', 'bbforms' ),
                    'SC' => __( 'Seychelles', 'bbforms' ),
                    'SL' => __( 'Sierra Leone', 'bbforms' ),
                    'SG' => __( 'Singapore', 'bbforms' ),
                    'SK' => __( 'Slovakia', 'bbforms' ),
                    'SI' => __( 'Slovenia', 'bbforms' ),
                    'SB' => __( 'Solomon Islands', 'bbforms' ),
                    'SO' => __( 'Somalia', 'bbforms' ),
                    'ZA' => __( 'South Africa', 'bbforms' ),
                    'GS' => __( 'South Georgia/Sandwich Islands', 'bbforms' ),
                    'KR' => __( 'South Korea', 'bbforms' ),
                    'SS' => __( 'South Sudan', 'bbforms' ),
                    'ES' => __( 'Spain', 'bbforms' ),
                    'LK' => __( 'Sri Lanka', 'bbforms' ),
                    'SD' => __( 'Sudan', 'bbforms' ),
                    'SR' => __( 'Suriname', 'bbforms' ),
                    'SJ' => __( 'Svalbard and Jan Mayen', 'bbforms' ),
                    'SZ' => __( 'Swaziland', 'bbforms' ),
                    'SE' => __( 'Sweden', 'bbforms' ),
                    'CH' => __( 'Switzerland', 'bbforms' ),
                    'SY' => __( 'Syria', 'bbforms' ),
                    'TW' => __( 'Taiwan', 'bbforms' ),
                    'TJ' => __( 'Tajikistan', 'bbforms' ),
                    'TZ' => __( 'Tanzania', 'bbforms' ),
                    'TH' => __( 'Thailand', 'bbforms' ),
                    'TL' => __( 'Timor-Leste', 'bbforms' ),
                    'TG' => __( 'Togo', 'bbforms' ),
                    'TK' => __( 'Tokelau', 'bbforms' ),
                    'TO' => __( 'Tonga', 'bbforms' ),
                    'TT' => __( 'Trinidad and Tobago', 'bbforms' ),
                    'TN' => __( 'Tunisia', 'bbforms' ),
                    'TR' => __( 'Turkey', 'bbforms' ),
                    'TM' => __( 'Turkmenistan', 'bbforms' ),
                    'TC' => __( 'Turks and Caicos Islands', 'bbforms' ),
                    'TV' => __( 'Tuvalu', 'bbforms' ),
                    'UG' => __( 'Uganda', 'bbforms' ),
                    'UA' => __( 'Ukraine', 'bbforms' ),
                    'AE' => __( 'United Arab Emirates', 'bbforms' ),
                    'GB' => __( 'United Kingdom (UK)', 'bbforms' ),
                    'US' => __( 'United States (US)', 'bbforms' ),
                    'UM' => __( 'United States (US) Minor Outlying Islands', 'bbforms' ),
                    'VI' => __( 'United States (US) Virgin Islands', 'bbforms' ),
                    'UY' => __( 'Uruguay', 'bbforms' ),
                    'UZ' => __( 'Uzbekistan', 'bbforms' ),
                    'VU' => __( 'Vanuatu', 'bbforms' ),
                    'VA' => __( 'Vatican', 'bbforms' ),
                    'VE' => __( 'Venezuela', 'bbforms' ),
                    'VN' => __( 'Vietnam', 'bbforms' ),
                    'WF' => __( 'Wallis and Futuna', 'bbforms' ),
                    'EH' => __( 'Western Sahara', 'bbforms' ),
                    'WS' => __( 'Samoa', 'bbforms' ),
                    'YE' => __( 'Yemen', 'bbforms' ),
                    'ZM' => __( 'Zambia', 'bbforms' ),
                    'ZW' => __( 'Zimbabwe', 'bbforms' ),
                )
            )
        );

    }

    return $bbforms_countries;
}

/**
 * Returns the country code
 *
 * @since 1.0.0
 *
 * @param string $country_code
 *
 * @return string
 */
function bbforms_get_country_code( $country_code ) {

    $countries = bbforms_get_countries();

    // ES
    if( isset( $countries[$country_code] ) ) {
        return $country_code;
    }

    // es
    if( isset( $countries[strtoupper( $country_code )] ) ) {
        return strtoupper( $country_code );
    }


    // Search by country name
    foreach( $countries as $code => $name ) {
        // Spain
        if( $name === $country_code ) {
            return $code;
        }

        // spain
        if( strtolower( $name ) === $country_code ) {
            return $code;
        }
    }

    return '';

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
function bbforms_get_country_name( $country_code ) {

    $countries = bbforms_get_countries();
    $country_code = bbforms_get_country_code( $country_code );

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
function bbforms_get_country_flag( $country_code ) {

    $country_code = bbforms_get_country_code( $country_code );

    $country_code = strtolower( $country_code );

    $country_code = esc_attr( $country_code );

    return "<span class='bbforms-country-flag bbforms-country-flag-{$country_code}'></span>";

}