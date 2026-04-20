<?php
/**
 * Automation Type
 *
 * @package     AutomatorWP\Classes\Automation_Type
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Automation_Type {

    /**
     * Integration
     *
     * @since 1.0.0
     *
     * @var string $integration
     */
    public $integration = '';

    /**
     * Type
     *
     * @since 1.0.0
     *
     * @var string $type
     */
    public $type = '';

    /**
     * Type
     *
     * @since 1.0.0
     *
     * @var array $args
     */
    public $args = array(
        'image' => AUTOMATORWP_URL . 'assets/img/automatorwp-logo.svg',
        'label' => '',
        'desc'  => '',
    );

    public function __construct() {

        $this->hooks();

    }

    public function hooks() {

        if ( ! did_action( 'automatorwp_init' ) ) {
            // Default hook to register
            add_action('automatorwp_init', array( $this, 'register' ) );
        } else {
            // Hook for triggers registered from the theme's functions
            add_action( 'after_setup_theme', array( $this, 'register' ) );
        }

        // Register type
        add_filter('automatorwp_automation_types', array( $this, 'register_type' ) );

    }

    /**
     * Register the automation type
     *
     * @since 1.0.0
     */
    public function register() {
        // Override
    }

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register_type( $types ) {

        $types[$this->type] = $this->args;

        return $types;
    }

}