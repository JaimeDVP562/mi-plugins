<?php
/**
 * URL Shortify Importer
 *
 * @package     ShortLinksPro\Classes\URL_Shortify_Importer
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class ShortLinksPro_URL_Shortify_Importer extends ShortLinksPro_Plugin_Importer {

    public function init() {
        $this->plugin = 'url-shortify';
        $this->args = array(
            'label' => 'URL Shortify',
            'supports' => array( 'links', 'link_categories', 'link_tags', 'clicks' ),
            // ----- Links -----
            'links' => array(
                'table' => 'kc_us_links',
                'id_field' => 'id',
                'where' => '',
                'fields' => array(
                    'title' => array(
                        'from' => 'name'
                    ),
                    'url' => array(
                        'from' => 'url',
                    ),
                    'slug' => array(
                        'from' => 'slug',
                    ),
                    'redirect_type' => array(
                        'from' => 'redirect_type',
                    ),
                    'nofollow' => array(
                        'from' => 'nofollow',
                    ),
                    'sponsored' => array(
                        'from' => 'sponsored',
                    ),
                    'parameter_forwarding' => array(
                        'from' => 'params_forwarding',
                    ),
                    'tracking' => array(
                        'from' => 'track_me',
                    ),
                    'author_id' => array(
                        'from' => 'created_by_id',
                    ),
                    'created_at' => array(
                        'from' => 'created_at',
                    ),
                    'updated_at' => array(
                        'from' => 'updated_at',
                    ),
                    
                    'notes' => array(
                        'from' => 'link_note',
                    ),
                    'status' => array(
                        'from' => 'link_status',
                    ),
                    'param_struct' => array(
                        'from' => 'param_struct',
                    ),
                    'link_date_gmt' => array(
                        'from' => 'link_date_gmt',
                    ),
                    'link_modified_gmt' => array(
                        'from' => 'link_modified_gmt',
                    ),
                    'wildcards' => array(
                        'from' => 'wildcards',
                    ),
                    'expire' => array(
                        'from' => 'expire',
                    ),
                    'dynamic_redirect' => array(
                        'from' => 'dynamic_redirect',
                    ),
                    'favorite' => array(
                        'from' => 'favorite',
                    ),
                    'uncloaked' => array(
                        'from' => 'uncloaked',
                    ),
                ),
                'meta_table' => 'slp_link_metas', 
                'meta_relationship_id_field' => 'link_id',
                'metas' => array(
                    
                ),
            ),
            // ----- Categories -----
            'link_categories' => array(
                'table' => 'kc_us_groups',
                'id_field' => 'id',
                'where' => "",
                'fields' => array(
                    'name' => array(
                        'from' => 'name',
                    ),
                    'slug' => array(
                        'from' => 'name',
                        'sanitize_cb' => array($this, 'generate_slug_from_name'),
                        'unique' => true,
                    ),
                    'description' => array(
                        'from' => 'description',
                    ),
                ),
                // Metas
                'meta_table' => 'slp_term_metas',
                'meta_relationship_id_field' => 'term_id',
                'metas' => array(
                    
                ),
                // Relationships
                'relationship' => array(
                    'table' => 'kc_us_links_groups',
                    'term_field' => 'group_id',
                    'object_field' => 'link_id',
                    'object_table' => 'links',
                ),
            ),
            // ----- Tags -----
            'link_tags' => array(
                'table' => '',
                'id_field' => '',
                'where' => "",
                'fields' => array(
                    // Los tags se manejarán en un proceso personalizado 
                ),
                'meta_table' => '',
                'metas' => array(),
                'relationship' => array(),
            ),
            // ----- Clicks -----
            'clicks' => array(
                'table' => 'kc_us_clicks',
                'id_field' => 'id',
                'where' => '',
                'fields' => array(
                    'link_id' => array(
                        'from' => 'link_id',
                        'relationship' => 'links',
                    ),
                    'ip' => array(
                        'from' => 'ip',
                    ),
                    'browser' => array(
                        'from' => 'user_agent', 
                        'sanitize_cb' => array( $this, 'browser_sanitize_cb' ),
                    ),
                    'browser_version' => array(
                        'from' => 'browser_version',
                        'sanitize_cb' => array( $this, 'browser_version_sanitize_cb' ),
                    ),
                    'browser_type' => array(
                        'from' => 'browser_type',
                        'sanitize_cb' => array( $this, 'browser_type_sanitize_cb' ),
                    ),
                    'os' => array(
                        'from' => 'os',
                        'sanitize_cb' => array( $this, 'os_sanitize_cb' ),
                    ),
                    'os_version' => array(
                        'from' => 'os', 
                        'sanitize_cb' => array( $this, 'os_version_sanitize_cb' ),
                    ),
                    'device' => array(
                        'from' => 'device',
                        'sanitize_cb' => array( $this, 'device_sanitize_cb' ),
                    ),
                    'user_agent' => array(
                        'from' => 'user_agent',
                    ),
                    'referrer' => array(
                        'from' => 'referer',
                    ),
                    'uri' => array(
                        'from' => 'uri',
                    ),
                    'parameters' => array(
                        'from' => 'uri', 
                        'sanitize_cb' => array( $this, 'parameters_sanitize_cb' ),
                    ),
                    'visitor_id' => array(
                        'from' => 'visitor_id',
                    ),
                    'first_click' => array(
                        'from' => 'is_first_click',
                    ),
                    'created_at' => array(
                        'from' => 'created_at',
                    ),
                    // Table fields to metas
                    'host' => array(
                        'from' => 'host',
                    ),
                    'brand_name' => array(
                        'from' => 'device', 
                        'sanitize_cb' => array( $this, 'brand_name_sanitize_cb' ),
                    ),
                    'model' => array(
                        'from' => 'device', 
                        'sanitize_cb' => array( $this, 'model_sanitize_cb' ),
                    ),
                    'bot_name' => array(
                        'from' => 'is_robot',
                        'sanitize_cb' => array( $this, 'bot_name_sanitize_cb' ),
                    ),
                    'language' => array(
                        'from' => 'user_agent', 
                        'sanitize_cb' => array( $this, 'language_sanitize_cb' ),
                    ),
                    'click_count' => array(
                        'default' => 1, 
                    ),
                    'click_order' => array(
                        'default' => 0, 
                    ),
                    'created_at_gmt' => array(
                        'from' => 'created_at', 
                        'sanitize_cb' => array( $this, 'created_at_gmt_sanitize_cb' ),
                    ),
                    'rotation_target_url' => array(
                        'default' => '', 
                    ),
                ),
                'meta_table' => 'slp_click_metas', 
                'meta_relationship_id_field' => 'click_id',
                'metas' => array(
                    
                ),
            ),
        );
    }

    public $dd;

    public function generate_slug_from_name($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            return sanitize_title($value);
        }
        return 'category-' . time();
    }

    
    public function browser_sanitize_cb( $value, $entry, $field, $field_args ) {

        if ( strpos( $value, '/' ) !== false ) {

            $this->dd = new DeviceDetector\DeviceDetector( $value );
            $this->dd->parse();

            $value = DeviceDetector\Parser\Client\Browser::getBrowserFamily( $this->dd->getClient( 'name' ) );
        } else {
            $this->dd = false;
        }

        return $value;

    }

    public function browser_version_sanitize_cb( $value, $entry, $field, $field_args ) {

        if ( $this->dd !== false ) {
            // Browser has been passed, so update value
            $value = $this->dd->getClient( 'version' );
        }

        return $value;

    }

    public function browser_type_sanitize_cb( $value, $entry, $field, $field_args ) {

        if ( $this->dd !== false ) {
            // Browser has been passed, so update value
            $value = $this->dd->getClient( 'type' );
        }

        return $value;

    }

    public function os_sanitize_cb( $value, $entry, $field, $field_args ) {

        if ( $this->dd !== false ) {
            // Browser has been passed, so update value
            $value = DeviceDetector\Parser\OperatingSystem::getOsFamily( $this->dd->getOs( 'name' ) );
        }

        return $value;

    }

    public function device_sanitize_cb( $value, $entry, $field, $field_args ) {

        if ( $this->dd !== false ) {
            // Browser has been passed, so update value
            $value = $this->dd->getDeviceName();
        }

        return $value;

    }
    
    public function parameters_sanitize_cb($value, $entry, $field, $field_args) {
        
        if (!empty($value) && strpos($value, '?') !== false) {
            $parts = explode('?', $value);
            if (isset($parts[1])) {
                return $parts[1];
            }
        }
        return '';
    }
    
    public function brand_name_sanitize_cb($value, $entry, $field, $field_args) {
        
        if ( $this->dd !== false ) {
            // Browser has been passed, so update value
            $value = $this->dd->getBrandName();

        }

        return $value;
    }
    
    public function model_sanitize_cb($value, $entry, $field, $field_args) {

        if (!empty($value)) {
            
            $parts = explode(' ', $value);
            if (count($parts) > 1) {
                return implode(' ', array_slice($parts, 1));
            }
        }
        return '';
    }
    
    public function bot_name_sanitize_cb($value, $entry, $field, $field_args) {

        if ( $this->dd !== false ) {

            $value = $this->dd->getBot();

        }

        return $value;
        
    }
    
    public function language_sanitize_cb($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            
            $matches = array();
            if (preg_match('/\b([a-z]{2}(?:-[A-Z]{2})?)\b/', $value, $matches)) {
                return $matches[1];
            }
        }
        return '';
    }
    
    public function created_at_gmt_sanitize_cb($value, $entry, $field, $field_args) {
        if (!empty($value)) {
            
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return gmdate('Y-m-d H:i:s', $timestamp);
            }
        }
        return current_time('mysql', true);
    }

}

new ShortLinksPro_URL_Shortify_Importer();