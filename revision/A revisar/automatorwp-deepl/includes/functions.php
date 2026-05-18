<?php
/**
 * Functions
 *
 * @package     AutomatorWP\DeepL\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Detect if the API key belongs to the Free plan (ends in :fx)
 * and return the correct base URL accordingly
 *
 * @since 1.0.0
 *
 * @param string $api_key
 * @return string
 */
function automatorwp_deepl_get_base_url( $api_key = '' ) {

    if ( empty( $api_key ) ) {
        $api_key = automatorwp_deepl_get_option( 'api_key', '' );
    }

    // Free plan keys end in :fx
    if ( substr( $api_key, -3 ) === ':fx' ) {
        return 'https://api-free.deepl.com';
    }

    return 'https://api.deepl.com';

}

/**
 * Get the DeepL API credentials
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_deepl_get_api() {

    $api_key = automatorwp_deepl_get_option( 'api_key', '' );

    if ( empty( $api_key ) ) {
        return false;
    }

    return array(
        'api_key'  => $api_key,
        'base_url' => automatorwp_deepl_get_base_url( $api_key ),
    );

}

/**
 * Central API request function for DeepL
 *
 * @since 1.0.0
 *
 * @param string $endpoint  Endpoint path, e.g. '/v2/translate'
 * @param array  $body      Request body params
 * @param string $method    HTTP method (POST|GET)
 *
 * @return array|false Parsed response body or false on error
 */
function automatorwp_deepl_api_request( $endpoint, $body = array(), $method = 'POST' ) {

    $api = automatorwp_deepl_get_api();

    if ( ! $api ) {
        return false;
    }

    $args = array(
        'headers'   => array(
            'Authorization' => 'DeepL-Auth-Key ' . $api['api_key'],
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ),
        'timeout'   => 45,
        'sslverify' => false, // Required for localhost/XAMPP environments
    );

    if ( $method === 'POST' ) {
        $args['body'] = json_encode( $body );
        $response = wp_remote_post( $api['base_url'] . $endpoint, $args );
    } else {
        $response = wp_remote_get( $api['base_url'] . $endpoint, $args );
    }

    // Capture WP errors without crashing
    if ( is_wp_error( $response ) ) {
        return array( 'error' => $response->get_error_message() );
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( $code !== 200 ) {
        $message = isset( $body['message'] ) ? $body['message'] : __( 'Unknown API error.', 'automatorwp-deepl' );
        return array( 'error' => $message );
    }

    return $body;

}

/**
 * Translate text using DeepL API
 *
 * @since 1.0.0
 *
 * @param string $text          Text to translate
 * @param string $target_lang   Target language code (e.g. ES, EN-US)
 * @param string $source_lang   Source language code (optional, auto-detect if empty)
 * @param string $tag_handling  Set to 'html' to preserve HTML tags (default: none)
 *
 * @return string|array Translated text or array with 'error' key
 */
function automatorwp_deepl_translate( $text, $target_lang, $source_lang = '', $tag_handling = '' ) {

    $body = array(
        'text'        => array( $text ),
        'target_lang' => strtoupper( $target_lang ),
    );

    if ( ! empty( $source_lang ) ) {
        $body['source_lang'] = strtoupper( $source_lang );
    }

    if ( ! empty( $tag_handling ) ) {
        $body['tag_handling'] = $tag_handling;
    }

    $response = automatorwp_deepl_api_request( '/v2/translate', $body );

    if ( ! $response || isset( $response['error'] ) ) {
        return $response;
    }

    return isset( $response['translations'][0]['text'] )
        ? $response['translations'][0]['text']
        : array( 'error' => __( 'Unexpected API response.', 'automatorwp-deepl' ) );

}

/**
 * Detect the language of a given text
 *
 * @since 1.0.0
 *
 * @param string $text
 * @return string|array Detected language code or array with 'error' key
 */
function automatorwp_deepl_detect_language( $text ) {

    // DeepL detects source language when no source_lang is sent; we use EN as dummy target
    $body = array(
        'text'        => array( $text ),
        'target_lang' => 'EN',
    );

    $response = automatorwp_deepl_api_request( '/v2/translate', $body );

    if ( ! $response || isset( $response['error'] ) ) {
        return $response;
    }

    return isset( $response['translations'][0]['detected_source_language'] )
        ? $response['translations'][0]['detected_source_language']
        : array( 'error' => __( 'Could not detect language.', 'automatorwp-deepl' ) );

}

/**
 * Get current usage stats from DeepL API
 *
 * @since 1.0.0
 *
 * @return array|false Usage data or false on error
 */
function automatorwp_deepl_get_usage() {

    return automatorwp_deepl_api_request( '/v2/usage', array(), 'GET' );

}

/**
 * Get supported target languages as options array
 * Uses official DeepL language codes
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_deepl_get_target_languages() {

    return array(
        'AR'    => __( 'AR — Arabic', 'automatorwp-deepl' ),
        'BG'    => __( 'BG — Bulgarian', 'automatorwp-deepl' ),
        'CS'    => __( 'CS — Czech', 'automatorwp-deepl' ),
        'DA'    => __( 'DA — Danish', 'automatorwp-deepl' ),
        'DE'    => __( 'DE — German', 'automatorwp-deepl' ),
        'EL'    => __( 'EL — Greek', 'automatorwp-deepl' ),
        'EN-GB' => __( 'EN-GB — English (British)', 'automatorwp-deepl' ),
        'EN-US' => __( 'EN-US — English (American)', 'automatorwp-deepl' ),
        'ES'    => __( 'ES — Spanish', 'automatorwp-deepl' ),
        'ET'    => __( 'ET — Estonian', 'automatorwp-deepl' ),
        'FI'    => __( 'FI — Finnish', 'automatorwp-deepl' ),
        'FR'    => __( 'FR — French', 'automatorwp-deepl' ),
        'HU'    => __( 'HU — Hungarian', 'automatorwp-deepl' ),
        'ID'    => __( 'ID — Indonesian', 'automatorwp-deepl' ),
        'IT'    => __( 'IT — Italian', 'automatorwp-deepl' ),
        'JA'    => __( 'JA — Japanese', 'automatorwp-deepl' ),
        'KO'    => __( 'KO — Korean', 'automatorwp-deepl' ),
        'LT'    => __( 'LT — Lithuanian', 'automatorwp-deepl' ),
        'LV'    => __( 'LV — Latvian', 'automatorwp-deepl' ),
        'NB'    => __( 'NB — Norwegian (Bokmål)', 'automatorwp-deepl' ),
        'NL'    => __( 'NL — Dutch', 'automatorwp-deepl' ),
        'PL'    => __( 'PL — Polish', 'automatorwp-deepl' ),
        'PT-BR' => __( 'PT-BR — Portuguese (Brazilian)', 'automatorwp-deepl' ),
        'PT-PT' => __( 'PT-PT — Portuguese (European)', 'automatorwp-deepl' ),
        'RO'    => __( 'RO — Romanian', 'automatorwp-deepl' ),
        'RU'    => __( 'RU — Russian', 'automatorwp-deepl' ),
        'SK'    => __( 'SK — Slovak', 'automatorwp-deepl' ),
        'SL'    => __( 'SL — Slovenian', 'automatorwp-deepl' ),
        'SV'    => __( 'SV — Swedish', 'automatorwp-deepl' ),
        'TR'    => __( 'TR — Turkish', 'automatorwp-deepl' ),
        'UK'    => __( 'UK — Ukrainian', 'automatorwp-deepl' ),
        'ZH'    => __( 'ZH — Chinese (simplified)', 'automatorwp-deepl' ),
    );

}

/**
 * Get supported source languages as options array
 * Includes "Auto-detect" option at the top
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_deepl_get_source_languages() {

    $languages = array(
        '' => __( '— Auto-detect —', 'automatorwp-deepl' ),
    );

    // Source languages do not have regional variants (EN instead of EN-GB)
    $source = array(
        'AR' => __( 'AR — Arabic', 'automatorwp-deepl' ),
        'BG' => __( 'BG — Bulgarian', 'automatorwp-deepl' ),
        'CS' => __( 'CS — Czech', 'automatorwp-deepl' ),
        'DA' => __( 'DA — Danish', 'automatorwp-deepl' ),
        'DE' => __( 'DE — German', 'automatorwp-deepl' ),
        'EL' => __( 'EL — Greek', 'automatorwp-deepl' ),
        'EN' => __( 'EN — English', 'automatorwp-deepl' ),
        'ES' => __( 'ES — Spanish', 'automatorwp-deepl' ),
        'ET' => __( 'ET — Estonian', 'automatorwp-deepl' ),
        'FI' => __( 'FI — Finnish', 'automatorwp-deepl' ),
        'FR' => __( 'FR — French', 'automatorwp-deepl' ),
        'HU' => __( 'HU — Hungarian', 'automatorwp-deepl' ),
        'ID' => __( 'ID — Indonesian', 'automatorwp-deepl' ),
        'IT' => __( 'IT — Italian', 'automatorwp-deepl' ),
        'JA' => __( 'JA — Japanese', 'automatorwp-deepl' ),
        'KO' => __( 'KO — Korean', 'automatorwp-deepl' ),
        'LT' => __( 'LT — Lithuanian', 'automatorwp-deepl' ),
        'LV' => __( 'LV — Latvian', 'automatorwp-deepl' ),
        'NB' => __( 'NB — Norwegian (Bokmål)', 'automatorwp-deepl' ),
        'NL' => __( 'NL — Dutch', 'automatorwp-deepl' ),
        'PL' => __( 'PL — Polish', 'automatorwp-deepl' ),
        'PT' => __( 'PT — Portuguese', 'automatorwp-deepl' ),
        'RO' => __( 'RO — Romanian', 'automatorwp-deepl' ),
        'RU' => __( 'RU — Russian', 'automatorwp-deepl' ),
        'SK' => __( 'SK — Slovak', 'automatorwp-deepl' ),
        'SL' => __( 'SL — Slovenian', 'automatorwp-deepl' ),
        'SV' => __( 'SV — Swedish', 'automatorwp-deepl' ),
        'TR' => __( 'TR — Turkish', 'automatorwp-deepl' ),
        'UK' => __( 'UK — Ukrainian', 'automatorwp-deepl' ),
        'ZH' => __( 'ZH — Chinese', 'automatorwp-deepl' ),
    );

    return array_merge( $languages, $source );

}
