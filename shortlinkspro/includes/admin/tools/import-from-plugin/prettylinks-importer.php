<?php
/**
 * Plugin Importer
 *
 * @package     ShortLinksPro\Classes\Plugin_Importer
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once SHORTLINKSPRO_DIR . 'vendor/autoload.php';

class ShortLinksPro_PrettyLinks_Importer extends ShortLinksPro_Plugin_Importer {

    public function init() {
        $this->plugin = 'prettylinks';
        $this->args = array(
            'label' => 'Pretty Links',
            'supports' => array( 'links', 'link_categories', 'link_tags', 'clicks' ),
            // ----- Links -----
            'links' => array(
                'table' => 'prli_links',
                'id_field' => 'link_cpt_id',
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
                        'sanitize_cb' => array( $this, 'redirect_type_sanitize_cb' ),
                    ),
                    'nofollow' => array(
                        'from' => 'nofollow',
                    ),
                    'sponsored' => array(
                        'from' => 'sponsored',
                    ),
                    'parameter_forwarding' => array(
                        'from' => 'param_forwarding',
                    ),
                    'tracking' => array(
                        'from' => 'track_me',
                    ),
                    'author_id' => array(
                        'default_cb' => array( $this, 'author_id_default_cb' ),
                    ),
                    'created_at' => array(
                        'from' => 'created_at',
                    ),
                    'updated_at' => array(
                        'from' => 'updated_at',
                    ),
                    // Table fields to metas
                    'notes' => array(
                        'from' => 'description',
                    ),
                    'link_status' => array(
                        'from' => 'link_status',
                    ),
                    'param_struct' => array(
                        'from' => 'param_struct',
                    ),
                    'link_id' => array(
                        'from' => 'id', // link ID
                    ),
                    'link_cpt_id' => array(
                        'from' => 'link_cpt_id', // post ID
                    ),
                    'group_id' => array(
                        'from' => 'group_id',
                    ),
                    'prettypay_link' => array(
                        'from' => 'prettypay_link',
                    ),
                ),
                'meta_table' => 'prli_link_metas',
                'meta_relationship_id_field' => 'link_id',
                'meta_relationship_entry_id_field' => 'id',
                'metas' => array(
                    'delay' => array(
                        'from' => 'delay',
                    ),
                    // Expirations
                    'enable_expire' => array(
                        'from' => 'enable_expire',
                        'save' => false,
                    ),
                    'expiration' => array(
                        'from' => 'expire_type',
                        'sanitize_cb' => array( $this, 'expiration_sanitize_cb' ),
                    ),
                    'date_to_expire' => array(
                        'from' => 'expire_date',
                        'sanitize_cb' => array( $this, 'date_to_expire_sanitize_cb' ),
                    ),
                    'clicks_to_expire' => array(
                        'from' => 'expire_clicks',
                    ),
                    'enable_expired_url' => array(
                        'from' => 'enable_expired_url',
                        'save' => false,
                    ),
                    'expired_url' => array(
                        'from' => 'expired_url',
                        'sanitize_cb' => array( $this, 'expired_url_sanitize_cb' ),
                    ),
                    // Dynamic Redirects
                    'dynamic_redirect' => array(
                        'from' => 'prli_dynamic_redirection',
                        'sanitize_cb' => array( $this, 'dynamic_redirect_sanitize_cb' ),
                    ),
                    // --- rotation
                    'dynamic_redirect_rotation_fields' => array(
                        'from' => 'prli-target-url-weight',
                        'sanitize_cb' => array( $this, 'dynamic_redirect_rotation_fields_sanitize_cb' ),
                    ),
                    // --- geographic
                    'geo_url' => array(
                        'from' => 'geo_url',
                        'save' => false,
                    ),
                    'dynamic_redirect_geographic_fields' => array(
                        'from' => 'geo_countries',
                        'sanitize_cb' => array( $this, 'dynamic_redirect_geographic_fields_sanitize_cb' ),
                    ),
                    // --- technology
                    'tech_url' => array(
                        'from' => 'tech_url',
                        'save' => false,
                    ),
                    'tech_device' => array(
                        'from' => 'tech_device',
                        'save' => false,
                    ),
                    'tech_os' => array(
                        'from' => 'tech_os',
                        'save' => false,
                    ),
                    'dynamic_redirect_technology_fields' => array(
                        'from' => 'tech_browser',
                        'sanitize_cb' => array( $this, 'dynamic_redirect_technology_fields_sanitize_cb' ),
                    ),
                    // --- time
                    'time_url' => array(
                        'from' => 'time_url',
                        'save' => false,
                    ),
                    'time_start' => array(
                        'from' => 'time_start',
                        'save' => false,
                    ),
                    'dynamic_redirect_date_range_fields' => array(
                        'from' => 'time_end',
                        'sanitize_cb' => array( $this, 'dynamic_redirect_date_range_fields_sanitize_cb' ),
                    ),
                ),
            ),
            // ----- Categories -----
            'link_categories' => array(
                'table' => 'term_taxonomy',
                'join_table' => 'terms',
                'join_on' => 'term_taxonomy.term_id = terms.term_id',
                'id_field' => 'term_taxonomy_id',
                'where' => "term_taxonomy.taxonomy = 'pretty-link-category'",
                'fields' => array(
                    'name' => array(
                        'from' => 'name',
                    ),
                    'slug' => array(
                        'from' => 'slug',
                        'unique' => true,
                    ),
                    'description' => array(
                        'from' => 'description',
                    ),
                ),
                'meta_table' => 'termmeta',
                'metas' => array(

                ),
                // Relationships
                'relationship' => array(
                    'table' => 'term_relationships',
                    'term_field' => 'term_taxonomy_id',
                    'object_field' => 'object_id',
                    'object_table' => 'links',
                ),
            ),
            // ----- Tags -----
            'link_tags' => array(
                'table' => 'term_taxonomy',
                'join_table' => 'terms',
                'join_on' => 'term_taxonomy.term_id = terms.term_id',
                'id_field' => 'term_taxonomy_id',
                'where' => "term_taxonomy.taxonomy = 'pretty-link-tag'",
                'fields' => array(
                    'name' => array(
                        'from' => 'name',
                    ),
                    'slug' => array(
                        'from' => 'slug',
                        'unique' => true,
                    ),
                    'description' => array(
                        'from' => 'description',
                    ),
                ),
                'meta_table' => 'termmeta',
                'metas' => array(

                ),
                // Relationships
                'relationship' => array(
                    'table' => 'term_relationships',
                    'term_field' => 'term_taxonomy_id',
                    'object_field' => 'object_id',
                    'object_table' => 'links',
                ),

            ),
            // ----- Clicks -----
            'clicks' => array(
                'table' => 'prli_clicks',
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
                        'from' => 'browser',
                        'sanitize_cb' => array( $this, 'browser_sanitize_cb' ),
                    ),
                    'browser_version' => array(
                        'from' => 'bversion',
                        'sanitize_cb' => array( $this, 'browser_version_sanitize_cb' ),
                    ),
                    'browser_type' => array(
                        'from' => 'btype',
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
                        'from' => 'btype',
                        'sanitize_cb' => array( $this, 'device_sanitize_cb' ),
                    ),
                    'user_agent' => array(
                        'from' => 'host',
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
                        'from' => 'vuid',
                    ),
                    'first_click' => array(
                        'from' => 'first_click',
                    ),
                    'created_at' => array(
                        'from' => 'created_at',
                    ),
                    // Table fields to metas
                    'host' => array(
                        'from' => 'host',
                    ),
                    'robot' => array(
                        'from' => 'robot',
                    ),
                ),
                'meta_table' => '',
                'meta_relationship_id_field' => '',
                'metas' => array(

                ),
            ),
        );
    }

    public function redirect_type_sanitize_cb( $value, $entry, $field, $field_args ) {

        switch ( $value ) {
            case 'cloak':
                $value = 'cloaked';
                break;
            case 'metarefresh':
                $value = 'meta_refresh';
                break;
            // Not supported redirects
            case 'prettybar':
            case 'pixel':
                $value = 'meta_refresh';
                break;
        }

        return $value;
    }

    public function expiration_sanitize_cb( $value, $entry, $field, $field_args ) {

        switch ( $value ) {
            case 'none':
                $value = '';
                break;
        }

        // Force value to empty if enable_expire not checked
        if( isset( $entry['enable_expire'] ) && empty( $entry['enable_expire'] ) ) {
            $value = '';
        }

        return $value;
    }

    public function date_to_expire_sanitize_cb( $value, $entry, $field, $field_args ) {

        // Parse to timestamp
        if( ! empty( $value ) ) {
            $value = strtotime( $value );
        }

        return $value;
    }

    public function expired_url_sanitize_cb( $value, $entry, $field, $field_args ) {

        // Force value to empty if enable_expired_url not checked
        if( isset( $entry['enable_expired_url'] ) && empty( $entry['enable_expired_url'] ) ) {
            $value = '';
        }

        return $value;
    }

    public function dynamic_redirect_sanitize_cb( $value, $entry, $field, $field_args ) {

        switch ( $value ) {
            case 'rotate':
                $value = 'rotation';
                break;
            case 'geo':
                $value = 'geographic';
                break;
            case 'tech':
                $value = 'technology';
                break;
            case 'time':
                $value = 'date_range';
                break;
        }

        return $value;
    }

    public function dynamic_redirect_rotation_fields_sanitize_cb( $value, $entry, $field, $field_args ) {

        global $wpdb, $current_entry;

        if( ! isset( $entry['prli_dynamic_redirection'] ) ) {
            return '';
        }

        if( $entry['prli_dynamic_redirection'] !== 'rotate' ) {
            return '';
        }

        if( ! isset( $current_entry['id'] ) ) {
            return '';
        }

        $weight = $value;

        $value = array();

        // Pretty Links keeps a first rotation to the target URL
        $value[] = array(
            'url' => ( isset( $current_entry['url'] ) ? $current_entry['url'] : '' ),
            'weight' => $weight,
        );

        // Query rotations
        $rotations = $wpdb->get_results(
            $wpdb->prepare(
            "SELECT url, weight
                FROM {$wpdb->prefix}prli_link_rotations
                WHERE link_id = %d
                ORDER BY r_index ASC",
                absint( $current_entry['id'] )
            ),
            ARRAY_A
        );

        foreach( $rotations as $rotation ) {
            $value[] = array(
                'url' => ( isset( $rotation['url'] ) ? $rotation['url'] : '' ),
                'weight' => ( isset( $rotation['weight'] ) ? absint( $rotation['weight'] ) : 0 ),
            );
        }

        return $value;
    }

    public function dynamic_redirect_geographic_fields_sanitize_cb( $value, $entry, $field, $field_args ) {

        if( ! isset( $entry['geo_url'] )
            && ! isset( $entry['geo_countries'] ) ) {
            return '';
        }

        if( ! is_array( $entry['geo_url'] ) ) {
            $entry['geo_url'] = array( $entry['geo_url'] );
        }

        if( ! is_array( $entry['geo_countries'] ) ) {
            $entry['geo_countries'] = array( $entry['geo_countries'] );
        }

        $value = array();

        foreach( $entry['geo_url'] as $i => $url ) {
            $countries = array();
            $country_codes = array();

            if( isset( $entry['geo_countries'][$i] ) ) {
                $countries = $entry['geo_countries'][$i];
                $countries = explode( ',', $countries );
            }

            foreach( $countries as $j => $country ) {
                preg_match( '/\[([a-zA-Z]+)\]/i', $country, $match );
                if( isset( $match[1] ) ) {
                    $country_codes[] = strtoupper( $match[1] );
                }
            }

            $value[] = array(
                'url' => $url,
                'countries' => $country_codes,
            );
        }

        return $value;

    }

    public function dynamic_redirect_technology_fields_sanitize_cb( $value, $entry, $field, $field_args ) {
        if( ! isset( $entry['tech_url'] )
            && ! isset( $entry['tech_device'] )
            && ! isset( $entry['tech_os'] )
            && ! isset( $entry['tech_browser'] ) ) {
            return '';
        }

        $value = array();

        foreach( $entry['tech_url'] as $i => $url ) {
            $device = 'any';
            $os = 'any';
            $browser = 'any';

            // Device
            if( isset( $entry['tech_device'][$i] ) )
                $device = $entry['tech_device'][$i];

            if( $device === 'mobile' || $device === 'phone' )
                $device = 'smartphone';

            // OS
            if( isset( $entry['tech_os'][$i] ) )
                $os = $entry['tech_os'][$i];

            if( $os === 'macosx' )
                $device = 'mac';

            if( $os === 'win' )
                $device = 'windows';

            // Browser
            if( isset( $entry['tech_browser'][$i] ) )
                $browser = $entry['tech_browser'][$i];

            if( $os === 'ie' )
                $device = 'internet-explorer';

            if( $os === 'coast' )
                $device = 'opera-coast';

            $value[] = array(
                'url' => $url,
                'device' => $device,
                'os' => $os,
                'browser' => $browser,
            );

        }

        return $value;
    }

    public function dynamic_redirect_date_range_fields_sanitize_cb( $value, $entry, $field, $field_args ) {

        if( ! isset( $entry['time_url'] )
            && ! isset( $entry['time_start'] )
            && ! isset( $entry['time_end'] ) ) {
            return '';
        }

        $value = array();

        foreach( $entry['time_url'] as $i => $url ) {
            $start = '';
            $end = '';

            if( isset( $entry['time_start'][$i] ) )
                $start = strtotime( $entry['time_start'][$i] );

            if( isset( $entry['time_end'][$i] ) )
                $end = strtotime( $entry['time_end'][$i] );

            $value[] = array(
                'url' => $url,
                'start' => $start,
                'end' => $end,
            );

        }

        return $value;

    }

    public function author_id_default_cb( $entry, $field, $field_args ) {

        global $wpdb;
        
        $default = '';

        if( isset( $entry['link_cpt_id'] ) && absint( $entry['link_cpt_id'] ) > 0 ) {
            
            $default = absint( $wpdb->get_var( $wpdb->prepare( "SELECT post_author FROM {$wpdb->posts} WHERE ID = %d", absint( $entry['link_cpt_id'] ) ) ) );
        }

        return $default;
    }

    public $dd;

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

    public function os_version_sanitize_cb( $value, $entry, $field, $field_args ) {

        if ( $this->dd !== false ) {
            // Browser has been passed, so update value
            $value = $this->dd->getOs( 'version' );
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
    
    public function parameters_sanitize_cb( $value, $entry, $field, $field_args ) {

        $params = '';
        if (!empty($value) && strpos($value, '?') !== false) {
            $parts = explode('?', $value);
            if (isset($parts[1])) {
                $params = $parts[1];
            }
        }
        return $params;

    }
    
}

new ShortLinksPro_PrettyLinks_Importer();