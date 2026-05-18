<?php
/**
 * Affiliate Links Importer
 *
 * @package     ShortLinksPro\Classes\AffiliateLinks_Importer
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class ShortLinksPro_AffiliateLinks_Importer extends ShortLinksPro_Plugin_Importer {

    public function init() {
        $this->plugin = 'affiliate-links';
        $this->args   = array(
            'label'    => 'Affiliate Links',
            'supports' => array( 'links', 'link_categories', 'clicks' ),
            // ----- Links -----
            'links' => array(
                'table'    => 'posts',
                'id_field' => 'ID',
                'where'    => "post_type = 'affiliate-links'",
                'fields'   => array(
                    'title'      => array(
                        'from' => 'post_title'
                    ),
                    'slug'       => array(
                        'from'        => 'post_name',
                        'sanitize_cb' => array( $this, 'slug_sanitize_cb' )
                    ),
                    'status'     => array(
                        'from' => 'post_status'
                    ),
                    'created_at' => array(
                        'from' => 'post_date'
                    ),
                    'updated_at' => array(
                        'from' => 'post_modified'
                    ),
                ),
                'meta_table'                 => 'postmeta',
                'meta_relationship_id_field' => 'post_id',
                'metas'                      => array(
                    'url'                     => array(
                        'from' => '_affiliate_links_target'
                    ),
                    'redirect_type'           => array(
                        'from' => '_affiliate_links_redirect'
                    ),
                    'target_two'              => array(
                        'from' => '_affiliate_links_target_two'
                    ),
                    'description'             => array(
                        'from' => '_affiliate_links_description'
                    ),
                    'iframe'                  => array(
                        'from' => '_affiliate_links_iframe'
                    ),
                    'nofollow'                => array(
                        'from' => '_affiliate_links_nofollow'
                    ),
                    'generate_link'           => array(
                        'from' => '_affiliate_links_generate_link'
                    ),
                    'additional_target_url'   => array(
                        'from' => '_affiliate_links_additional_target_url'
                    ),
                    'embedded_add_rel'        => array(
                        'from' => '_embedded_add_rel'
                    ),
                    'embedded_add_target'     => array(
                        'from' => '_embedded_add_target'
                    ),
                    'embedded_add_link_title' => array(
                        'from' => '_embedded_add_link_title'
                    ),
                    'embedded_add_link_class' => array(
                        'from' => '_embedded_add_link_class'
                    ),
                    'embedded_add_link_anchor'=> array(
                        'from' => '_embedded_add_link_anchor'
                    ),
                ),
            ),
            // ----- Categories -----
            'link_categories' => array(
                'table'      => 'term_taxonomy',
                'join_table' => 'terms',
                'join_on'    => 'term_taxonomy.term_id = terms.term_id',
                'id_field'   => 'term_taxonomy_id',
                'where'      => "term_taxonomy.taxonomy = 'affiliate-links-cat'",
                'fields'     => array(
                    'name'        => array(
                        'from' => 'name'
                    ),
                    'slug'        => array(
                        'from'   => 'slug',
                        'unique' => true,
                    ),
                    'parent' => array(
                        'from' => 'parent'
                    ),
                    'description' => array(
                        'from' => 'description'
                    ),
                ),
                'meta_table' => 'termmeta',
                'metas'      => array(),
                'relationship' => array(
                    'table'        => 'term_relationships',
                    'term_field'   => 'term_taxonomy_id',
                    'object_field' => 'object_id',
                    'object_table' => 'links',
                ),
            ),
            // ----- Clicks -----
            'clicks' => array(
                'table'    => 'af_links_activity',
                'id_field' => 'id',
                'where'    => "",
                'fields'   => array(
                    'link_id' => array(
                        'from'         => 'link_id',
                        'relationship' => 'links',
                    ),
                    'browser' => array(
                        'from' => 'browser',
                        'sanitize_cb' => array( $this, 'browser_sanitize_cb' )
                    ),
                    'referer' => array(
                        'from' => 'referer'
                    ),
                    'os' => array(
                        'from' => 'os',
                        'sanitize_cb' => array( $this, 'os_sanitize_cb' )

                    ),
                    'os_version' => array(
                        'from' => 'os',
                        'sanitize_cb' => array( $this, 'os_version_sanitize_cb' )

                    ),
                    'device' => array(
                        'from' => 'platform'
                    ),
                    'country' => array(
                        'from' => 'lang'
                    ),
                    'created_at' => array(
                        'from' => 'created_date'
                    )
                ),
                'meta_table'                 => 'postmeta',
                'meta_relationship_id_field' => 'link_id',
                'metas'                      => array(
                    'click_count' => array(
                        'from'  => 'meta_value',
                        'where' => "meta_key = '_affiliate_links_stat'"
                    ),
                ),
            ),
        );
    }

    public function slug_sanitize_cb( $value, $entry, $field, $field_args ) {
        // Remove site URL
        $value = str_replace( site_url('/'), '', $value );
        
  
        return $value;
    }

    public function os_sanitize_cb( $value, $entry, $field, $field_args ) {
        $value = explode(" ", $value);

        return $value[0];
    }

    public function os_version_sanitize_cb( $value, $entry, $field, $field_args ) {
        $value = explode(" ", $value);

        return $value[1];
    }

    public function browser_sanitize_cb( $value, $entry, $field, $field_args ) {
        $value = str_replace('is_', '', $value);
        $value = ucfirst($value);

        return $value;
    }
}

new ShortLinksPro_AffiliateLinks_Importer();
