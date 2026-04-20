<?php
/**
 * Action
 *
 * @package     BBForms\Actions\Action
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Action {

    public $bbcode = '';
    public $pattern = '';
    public $name = '';
    public $priority = 10;
    public $default_attrs = array();
    public $attrs = array();
    public $content = null;
    public $original_attrs = array();
    public $form = null;

    /**
     * Construct
     *
     * @since 1.0.0
     */
    public function __construct() {

        $this->hooks();
        $this->register();

    }

    /**
     * Hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        add_action( 'init', array( $this, 'init' ) );
        add_action( 'init', array( $this, 'register' ) );

    }

    /**
     * Init (called on init hook)
     *
     * @since 1.0.0
     */
    public function init() {

    }

    /**
     * Register (called on init hook)
     *
     * @since 1.0.0
     */
    public function register() {

        global $bbforms_actions;

        if( ! is_array( $bbforms_actions ) ) {
            $bbforms_actions = array();
        }

        $bbforms_actions[$this->bbcode] = $this;

        // Default pattern for most tags
        if( empty( $this->pattern ) ) {
            $this->pattern = "[{$this->bbcode}]\n";
        }

    }

    /**
     * Default attributes
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function default_attrs() {
        $default_attrs = array_merge( array(
            'value' => '',
            'success_message' => '',
            'error_message' => '',
        ), $this->default_attrs );

        /**
         * Hook to override default attrs
         *
         * @since 1.0.0
         *
         * @param array $default_attrs
         *
         * @return array
         */
        $default_attrs = apply_filters( "bbforms_action_default_attrs", $default_attrs );
        $default_attrs = apply_filters( "bbforms_{$this->bbcode}_action_default_attrs", $default_attrs );

        return $default_attrs;
    }

    /**
     * Parse the BBCode attributes
     *
     * @since 1.0.0
     *
     * @param array         $attrs      Attributes to be parsed
     * @param string|null   $content    The BBCode content
     *
     * @return array                    Attributes already parsed
     */
    public function parse_attrs( $attrs, $content = null ) {

        global $bbforms_form;

        // Assign the global form as form
        $this->form = $bbforms_form;

        // Parse attrs
        $this->original_attrs = $attrs;
        $this->attrs = shortcode_atts( $this->default_attrs(), $attrs, 'bbforms_' . $this->bbcode );

        /**
         * Hook to override attrs
         *
         * @since 1.0.0
         *
         * @param array             $attrs
         * @param string|null       $content
         * @param BBForms_Action    $action
         *
         * @return array
         */
        $this->attrs = apply_filters( 'bbforms_action_parse_attrs', $this->attrs, $content, $this );

        $this->content = $content;

        return $this->attrs;

    }

    /**
     * Process the action
     *
     * @since 1.0.0
     *
     * @param array         $attrs      Attributes already parsed
     * @param string|null   $content    The BBCode content
     *
     * @return bool
     */
    public function process_action( $attrs = array(), $content = null ) {
        return true;
    }

    /**
     * Base function to process the action, this calls the process_action() that it should be overwritten
     * Also parses the $attrs.
     *
     * @since 1.0.0
     *
     * @param array $attrs
     * @param string|null $content
     */
    public function process( $attrs = array(), $content = null ) {

        global $bbforms_form, $bbforms_request, $bbforms_response;

        $this->parse_attrs( $attrs, $content );

        $result = $this->process_action( $this->attrs, $content );

        if( $result ) {
            $message = $this->get_success_message();
        } else {
            $message = $this->get_error_message();
        }

        $bbforms_response['success'] = $result;

        if( ! empty( $message ) ) {
            $bbforms_response['messages'][] = $message;
        }

    }

    /**
     * Get the success message
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_success_message() {
        return ( ! empty( $this->attrs['success_message'] ) ? array( 'text' => $this->attrs['success_message'], 'type' => 'success' ) : '' );
    }

    /**
     * Get the error message
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_error_message() {
        return ( ! empty( $this->attrs['error_message'] ) ?  array( 'text' => $this->attrs['error_message'], 'type' => 'error' ) : '' );
    }

}