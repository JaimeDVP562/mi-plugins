<?php
/**
 * BetterLinks Importer
 *
 * @package     ShortLinksPro\Classes\BetterLinks_Importer
 * @author      ShortLinksPro <contact@shortlinkspro.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class ShortLinksPro_BetterLinks_Importer extends ShortLinksPro_Plugin_Importer {

    public function init() {
        $this->plugin = 'betterlinks';
        $this->args = array(
            'label' => 'BetterLinks',
            'supports' => array( 'links', 'link_categories', 'link_tags', 'clicks' ),
            // ----- Links -----
            'links' => array(
                'table' => 'betterlinks',
                'id_field' => 'ID',
                'where' => '',
                'fields' => array(
                    'title' => array(
                        'from' => 'link_title'
                    ),
                    'url' => array(
                        'from' => 'target_url',
                    ),
                    'slug' => array(
                        'from' => 'short_url',
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
                        'from' => 'param_forwarding',
                    ),
                    'tracking' => array(
                        'from' => 'track_me',
                    ),
                    'author_id' => array(
                        'from' => 'link_author',
                    ),
                    'created_at' => array(
                        'from' => 'link_date',
                    ),
                    'updated_at' => array(
                        'from' => 'link_modified',
                    ),
                    // Table fields to metas
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
                        'from' => 'wildcards	',
                    ),
                    'expire' => array(
                        'from' => 'expire	',
                    ),
                    'dynamic_redirect' => array(
                        'from' => 'dynamic_redirect	',
                    ),
                    'favorite' => array(
                        'from' => 'favorite	',
                    ),
                    'uncloaked' => array(
                        'from' => 'uncloaked	',
                    ),
                ),
                'meta_table' => 'betterlinkmeta',
                'meta_relationship_id_field' => 'link_id',
                'metas' => array(

                ),
            ),
            // ----- Categories -----
            'link_categories' => array(
                'table' => 'betterlinks_terms',
                'id_field' => 'ID',
                'where' => "term_type = 'category'",
                'fields' => array(
                    'name' => array(
                        'from' => 'term_name',
                    ),
                    'slug' => array(
                        'from' => 'term_slug',
                        'unique' => true,
                    ),
                    'description' => array(
                        // Nothing
                    ),
                ),
                // Metas
                'meta_table' => '',
                'metas' => array(

                ),
                // Relationships
                'relationship' => array(
                    'table' => 'betterlinks_terms_relationships',
                    'term_field' => 'term_id',
                    'object_field' => 'link_id',
                    'object_table' => 'links',
                ),
            ),
            // ----- Tags -----
            'link_tags' => array(
                'table' => 'betterlinks_terms',
                'id_field' => 'ID',
                'where' => "term_type = 'tags'",
                'fields' => array(
                    'name' => array(
                        'from' => 'term_name',
                    ),
                    'slug' => array(
                        'from' => 'term_slug',
                        'unique' => true,
                    ),
                    'description' => array(
                        // Nothing
                    ),
                ),
                // Metas
                'meta_table' => '',
                'metas' => array(

                ),
                // Relationships
                'relationship' => array(
                    'table' => 'betterlinks_terms_relationships',
                    'term_field' => 'term_id',
                    'object_field' => 'link_id',
                    'object_table' => 'links',
                ),
            ),
            // ----- Clicks -----
            'clicks' => array(
                'table' => 'betterlinks_clicks',
                'id_field' => 'ID',
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
                    ),
                    'browser_version' => array(
                        'from' => 'browser_version',
                    ),
                    'browser_type' => array(
                        'from' => 'browser_type',
                    ),
                    'os' => array(
                        'from' => 'os',
                    ),
                    'os_version' => array(
                        'from' => 'os_version',
                    ),
                    'device' => array(
                        'from' => 'device',
                    ),
                    'user_agent' => array(
                        // Nothing
                    ),
                    'referrer' => array(
                        'from' => 'referer',
                    ),
                    'uri' => array(
                        'from' => 'uri',
                    ),
                    'parameters' => array(
                        'from' => 'query_params',
                    ),
                    'visitor_id' => array(
                        'from' => 'visitor_id',
                    ),
                    'first_click' => array(
                        // Nothing
                    ),
                    'created_at' => array(
                        'from' => 'created_at',
                    ),
                    // Table fields to metas
                    'host' => array(
                        'from' => 'host',
                    ),
                    'brand_name' => array(
                        'from' => 'brand_name',
                    ),
                    'model' => array(
                        'from' => 'model',
                    ),
                    'bot_name' => array(
                        'from' => 'bot_name',
                    ),
                    'language' => array(
                        'from' => 'language',
                    ),
                    'click_count' => array(
                        'from' => 'click_count',
                    ),
                    'click_order' => array(
                        'from' => 'click_order',
                    ),
                    'created_at_gmt' => array(
                        'from' => 'created_at_gmt',
                    ),
                    'rotation_target_url' => array(
                        'from' => 'rotation_target_url',
                    ),
                ),
                'meta_table' => '',
                'metas' => array(

                ),
            ),
        );

    }

}

new ShortLinksPro_BetterLinks_Importer();