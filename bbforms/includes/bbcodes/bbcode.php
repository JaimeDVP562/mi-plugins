<?php
/**
 * BBCode
 *
 * @package     BBForms\BBCode
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode {

    public $bbcode = '';
    public $pattern = '';
    public $name = '';
    public $default_attrs = array();
    public $attrs = array();
    public $content = null;
    public $original_attrs = array();

    public $scripts_enqueued = false;

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
        add_action( 'init', array( $this, 'register_scripts' ) );

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

        global $bbforms_bbcodes;

        if( ! is_array( $bbforms_bbcodes ) ) {
            $bbforms_bbcodes = array();
        }

        // Default pattern for most tags
        if( empty( $this->pattern ) ) {
            $this->pattern = "[{$this->bbcode}]CONTENT[/{$this->bbcode}]";
        }

        $bbforms_bbcodes[$this->bbcode] = $this;

    }

    /**
     * Register Scripts (called on init hook)
     *
     * @since 1.0.0
     */
    public function register_scripts() {

    }

    /**
     * Enqueue Scripts
     *
     * @since 1.0.0
     */
    public function enqueue_scripts() {

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
            'id'    => '',
            'class' => '',
            'value' => '',
            'style' => '',
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
        $default_attrs = apply_filters( "bbforms_bbcode_default_attrs", $default_attrs );
        $default_attrs = apply_filters( "bbforms_{$this->bbcode}_bbcode_default_attrs", $default_attrs );

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
         * @param BBForms_BBCode    $bbcode
         *
         * @return array
         */
        $this->attrs = apply_filters( 'bbforms_bbcode_parse_attrs', $this->attrs, $content, $this );

        $this->content = $content;

        return $this->attrs;

    }

    /**
     * Render the BBCode
     *
     * @since 1.0.0
     *
     * @param array         $attrs      Attributes already parsed
     * @param string|null   $content    The BBCode content
     *
     * @return string
     */
    public function render_field( $attrs = array(), $content = null ) {
        return '';
    }

    /**
     * Base function to render the BBCode, this calls the render_field() that it should be overwritten
     * Also parses the $attrs.
     *
     * @since 1.0.0
     *
     * @param array $attrs
     * @param string|null $content
     *
     * @return string
     */
    public function render( $attrs = array(), $content = null ) {

        $this->parse_attrs( $attrs, $content );

        // Scripts
        if( ! $this->scripts_enqueued ) {
            $this->enqueue_scripts();
            $this->scripts_enqueued = true;
        }

        return apply_filters( 'bbforms_render_bbcode', $this->render_field( $this->attrs, $content ), $this->attrs, $content, $this );

    }

}