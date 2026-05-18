<?php
/**
 * Asana Admin.
 *
 * @package     AutomatorWP\Asana\Admin
 * @author      AutomatorWP
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AutomatorWP_Asana_Admin {

    /**
     * AutomatorWP_Asana_Admin constructor.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        // Settings hooks.
        add_filter( 'automatorwp_settings_sections', array( $this, 'settings_sections' ) );
        add_filter( 'automatorwp_settings_asana_meta_boxes', array( $this, 'settings_meta_boxes' ) );
    }



    /**
     * Register plugin settings sections.
     *
     * @since  1.0.0
     * @param array $sections The existing sections.
     * @return array
     */
    public function settings_sections( $sections ) {
        $sections['asana'] = array(
            'title' => __( 'Asana', 'automatorwp-asana' ),
            'icon'  => 'dashicons-admin-generic', // Simplified for matching model
        );
        return $sections;
    }

    /**
     * Register plugin settings meta boxes.
     *
     * @since  1.0.0
     * @param array $meta_boxes The existing meta boxes.
     * @return array
     */
    public function settings_meta_boxes( $meta_boxes ) {
        $prefix = 'automatorwp_asana_';

        $meta_boxes['automatorwp-asana-settings'] = array(
            'title'  => __( 'Asana Settings', 'automatorwp-asana' ),
            'fields' => array(
                $prefix . 'access_token' => array(
                    'name' => __( 'API Access Token:', 'automatorwp-asana' ),
                    'desc' => __( 'Your Asana Personal Access Token.', 'automatorwp-asana' ),
                    'type' => 'text',
                ),
            ),
        );

        return $meta_boxes;
    }





}

new AutomatorWP_Asana_Admin();
