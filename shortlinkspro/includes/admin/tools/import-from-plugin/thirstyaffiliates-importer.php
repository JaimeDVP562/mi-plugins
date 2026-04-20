<?php
/**
 * ThirstyAffiliates Importer
 *
 * @package     ShortLinksPro\Classes\ThirstyAffiliates_Importer
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class ShortLinksPro_ThirstyAffiliates_Importer extends ShortLinksPro_Plugin_Importer {

    public function init() {
        $this->plugin = 'thirstyaffiliates';
        $this->args = array(
            'label' => 'ThirstyAffiliates',
            'supports' => array( 'links', 'link_categories', 'clicks' ),
            // ----- Links -----
            'links' => array(
                'table' => 'posts',
                'id_field' => 'ID',
                'where' => "post_type = 'thirstylink'",
                'fields' => array(
                    'title' => array(
                        'from' => 'post_title'
                    ),
                    'slug' => array(
                        'from' => 'post_name',
                        'sanitize_cb' => array( $this, 'slug_sanitize_cb' ),
                    ),
                    'sponsored' => array(
                        'default_cb' => array( $this, 'sponsored_default_cb' )
                    ),
                    'tracking' => array(
                        'default_cb' => array( $this, 'tracking_default_cb' )
                    ),
                    'author_id' => array(
                        'from' => 'post_author',
                    ),
                    'created_at' => array(
                        'from' => 'post_date',
                    ),
                    'updated_at' => array(
                        'from' => 'post_modified',
                    ),
                    // Table fields to metas
                    'status' => array(
                        'from' => 'post_status',
                    ),
                    'post_name' => array(
                        'from' => 'post_name',
                    ),
                ),
                'meta_table' => 'postmeta',
                'meta_relationship_id_field' => 'post_id',
                'metas' => array(
                    'redirect_type' => array(
                        'from' => '_ta_redirect_type',
                        'sanitize_cb' => array( $this, 'redirect_type_sanitize_cb' ),
                    ),
                    'nofollow' => array(
                        'from' => '_ta_no_follow',
                        'sanitize_cb' => array( $this, 'nofollow_sanitize_cb' ),
                    ),
                    'parameter_forwarding' => array(
                        'from' => '_ta_pass_query_str',
                        'sanitize_cb' => array( $this, 'parameter_forwarding_sanitize_cb' ),
                    ),
                    'url' => array(
                        'from' => '_ta_destination_url',
                    ),
                ),
            ),
            // ----- Categories -----
            'link_categories' => array(
                'table' => 'term_taxonomy',
                'join_table' => 'terms',
                'join_on' => 'term_taxonomy.term_id = terms.term_id',
                'id_field' => 'term_taxonomy_id',
                'where' => "term_taxonomy.taxonomy = 'thirstylink-category'",
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
                'table' => 'ta_link_clicks',
                'id_field' => 'id',
                'where' => '',
                'fields' => array(
                    'link_id' => array(
                        'from' => 'link_id',
                        'relationship' => 'links',
                    ),
                    'created_at' => array(
                        'from' => 'date_clicked',
                    ),
                ),
                'meta_table' => 'ta_link_clicks_meta',
                'meta_relationship_id_field' => 'click_id',
                'metas' => array(
                    'ip' => array(
                        'from' => 'user_ip_address',
                    ),
                    'browser' => array(
                        'from' => 'browser_device', // Needs sanitization stored as browser|platform|device_type
                    ),
                    'browser_version' => array(
                        // Nothing
                    ),
                    'browser_type' => array(
                        // Nothing
                    ),
                    'os' => array(
                        'from' => 'browser_device', // Needs sanitization stored as browser|platform|device_type
                    ),
                    'os_version' => array(
                        // Nothing
                    ),
                    'device' => array(
                        'from' => 'browser_device', // Needs sanitization stored as browser|platform|device_type
                    ),
                    'user_agent' => array(
                        // Nothing
                    ),
                    'referrer' => array(
                        'from' => 'http_referer'
                    ),
                    'uri' => array(
                        'from' => 'redirect_url',
                    ),
                    'parameters' => array(
                        // Nothing
                    ),
                    'visitor_id' => array(
                        // Nothing
                    ),
                    'first_click' => array(
                        // Nothing
                    ),
                ),
            ),
        );

    }

    public function slug_sanitize_cb( $value, $entry, $field, $field_args ) {

        $post_id = absint( $entry['ID'] );
        $value = get_the_permalink( $post_id );

        // Remove site URL
        $value = str_replace( site_url('/'), '', $value );
        // Remove index.php
        $value = str_replace( 'index.php/', '', $value );

        return $value;

    }

    public function sponsored_default_cb( $default, $entry, $field, $field_args ) {

        if( ! isset( $this->cache['sponsored'] ) ) {
            $link_options = shortlinkspro_get_option( 'link_options', array( 'nofollow', 'tracking' ) ) ;
            $this->cache['sponsored'] = absint( in_array( 'sponsored', $link_options ) );
        }

        return $this->cache['sponsored'];
    }

    public function tracking_default_cb( $default, $entry, $field, $field_args ) {
        if( ! isset( $this->cache['tracking'] ) ) {
            $link_options = shortlinkspro_get_option( 'link_options', array( 'nofollow', 'tracking' ) ) ;
            $this->cache['tracking'] = absint( in_array( 'tracking', $link_options ) );
        }

        return $this->cache['tracking'];
    }

    public function redirect_type_sanitize_cb( $value, $entry, $field, $field_args ) {

        if( $value === 'global' ) {

            if( ! isset( $this->cache['redirect_type'] ) ) {
                $this->cache['redirect_type'] = get_option( 'ta_link_redirect_type', true );
            }

            $value = $this->cache['redirect_type'];

        }

        return $value;

    }

    public function nofollow_sanitize_cb( $value, $entry, $field, $field_args ) {

        if( $value === 'global' ) {

            if( ! isset( $this->cache['nofollow'] ) ) {
                $this->cache['nofollow'] = get_option( 'ta_no_follow', true );
            }

            $value = $this->cache['nofollow'];

        }

        return $value;

    }

    public function parameter_forwarding_sanitize_cb( $value, $entry, $field, $field_args ) {

        if( $value === 'global' ) {

            if( ! isset( $this->cache['parameter_forwarding'] ) ) {
                $this->cache['parameter_forwarding'] = get_option( 'ta_pass_query_str', true );
            }

            $value = $this->cache['parameter_forwarding'];

        }

        return $value;

    }

}

new ShortLinksPro_ThirstyAffiliates_Importer();