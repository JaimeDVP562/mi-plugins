<?php

/**
 * Easy Affiliate Links Importer
 *
 * @package     ShortLinksPro\Classes\Plugin_Importer
 * @author      ShortLinksPro
 * @since       1.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class ShortLinksPro_EasyAffiliateLinks_Importer extends ShortLinksPro_Plugin_Importer
{

    public function init()
    {
        $this->plugin = 'easy-affiliate-links';

        $this->args = array(
            'label' => 'Easy Affiliate Links',
            'supports' => array('links', 'link_categories', 'clicks'),

            // ----- Links -----
            'links' => array(
                'table' => 'posts',
                'id_field' => 'ID',
                'where' => "post_type = 'easy_affiliate_link'",
                'fields' => array(
                    'title' => array('from' => 'post_title'),
                    'slug' => array('from' => 'post_name'),
                    'created_at' => array('from' => 'post_date'),
                    'updated_at' => array('from' => 'post_modified'),
                    'author_id' => array('from' => 'post_author'),
                    'link_status' => array('from' => 'post_status'),
                ),
                'meta_table' => 'postmeta',
                'meta_relationship_id_field' => 'post_id',
                'metas' => array(
                    'url' => array('from' => 'eafl_url'),
                    'nofollow' => array('from' => 'eafl_nofollow'),
                    'sponsored' => array('from' => 'eafl_sponsored'),
                    'tracking_enabled' => array('from' => '_easy_affiliate_link_tracking'),
                ),
                'relationship' => array(
                    'table' => 'shortlinkspro_link_categories_relationships',
                    'term_field' => 'link_category_id',
                    'object_field' => 'link_id',
                    'object_table' => 'terms', 
                ),
            ),


            // ----- Categories -----
            'link_categories' => array(
                'table' => 'terms',
                'id_field' => 'term_id',
                'fields' => array(
                    'name' => array('from' => 'name'),
                    'slug' => array('from' => 'slug'),
                    'group' => array('from' => 'term_group'),
                ),
                'meta_table' => 'termmeta',
                'meta_relationship_id_field' => 'term_id',
                'metas' => array(
                ),
                'relationship' => array(
                    'table' => 'term_relationships',
                    'term_field' => 'term_taxonomy_id',
                    'object_field' => 'object_id',
                    'object_table' => 'posts',
                    'object_where' => "post_type = 'easy_affiliate_link'"
                ),
            ),

            // ----- Clicks -----
            'clicks' => array(
                'table' => 'wp_shortlinkspro_clicks',
                'id_field' => 'ID',
                'fields' => array(
                    'link_id' => array('from' => 'link_id', 'relationship' => 'links'),
                    'ip' => array('from' => 'ip_address'),
                    'browser' => array(
                        'from' => 'browser',
                        'sanitize_cb' => array($this, 'browser_type_sanitize_cb')
                    ),
                    'os' => array(
                        'from' => 'os',
                        'sanitize_cb' => array($this, 'os_sanitize_cb')
                    ),
                    'device' => array(
                        'from' => 'device',
                        'sanitize_cb' => array($this, 'device_sanitize_cb')
                    ),
                    'referrer' => array('from' => 'referrer'),
                    'country' => array('from' => 'country'),
                    'utm_source' => array('from' => 'utm_source'),
                    'utm_medium' => array('from' => 'utm_medium'),
                    'utm_campaign' => array('from' => 'utm_campaign'),
                    'created_at' => array('from' => 'created_at'),
                ),
            ),
        );

        $this->process_relationships();
    }

    public function process_relationships()
    {
        global $wpdb;

        // Obtener las relaciones entre enlaces y categorías
        $relationships = $wpdb->get_results("
            SELECT tr.object_id, tr.term_taxonomy_id
            FROM {$wpdb->prefix}term_relationships tr
            INNER JOIN {$wpdb->prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE tt.taxonomy = 'easy_affiliate_link_category'
        ");

        // Procesar cada relación
        foreach ($relationships as $relationship) {
            $link_id = $relationship->object_id;
            $category_id = $relationship->term_taxonomy_id;

            // Asociar el enlace con la categoría en tu sistema
            $this->associate_link_with_category($link_id, $category_id);
        }
    }

    private function associate_link_with_category($link_id, $category_id)
    {
        global $wpdb;

        // Insertar la relación en la tabla wp_shortlinkspro_link_categories_relationships
        $wpdb->insert(
            "{$wpdb->prefix}shortlinkspro_link_categories_relationships", // Tabla de relaciones
            array(
                'link_category_id' => $category_id, // ID de la categoría
                'link_id' => $link_id,             // ID del enlace
            ),
            array('%d', '%d') // Formato de los datos
        );
    }

    public function browser_type_sanitize_cb($value, $entry, $field, $field_args)
    {
        if (strpos($value, 'Chrome') !== false) return 'Chrome';
        if (strpos($value, 'Firefox') !== false) return 'Firefox';
        if (strpos($value, 'Safari') !== false) return 'Safari';
        if (strpos($value, 'Edge') !== false) return 'Edge';
        if (strpos($value, 'Opera') !== false) return 'Opera';
        return 'Unknown';
    }

    public function os_sanitize_cb($value, $entry, $field, $field_args)
    {
        if (strpos($value, 'Windows') !== false) return 'Windows';
        if (strpos($value, 'Mac OS X') !== false) return 'macOS';
        if (strpos($value, 'Linux') !== false) return 'Linux';
        if (strpos($value, 'Android') !== false) return 'Android';
        if (strpos($value, 'iPhone') !== false || strpos($value, 'iPad') !== false) return 'iOS';
        return 'Unknown';
    }

    public function device_sanitize_cb($value, $entry, $field, $field_args)
    {
        if (strpos($value, 'Mobile') !== false) return 'Mobile';
        if (strpos($value, 'Tablet') !== false) return 'Tablet';
        return 'Desktop';
    }
}

new ShortLinksPro_EasyAffiliateLinks_Importer();
