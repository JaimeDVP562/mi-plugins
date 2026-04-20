<?php
/**
 * Form
 *
 * @package     BBForms\Classes\From
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Form {

    // Form fields
    public $id = 0;
    public $title = '';
    public $form = '';
    public $actions = '';
    public $options = array(); // Options get parsed as array

    // Extra variables
    public $user_id = 0;
    public $has_required_fields = false;
    public $has_files = false;
    public $fields = array();
    public $input_names = array(); // Used to meet the defined field names (Used in BBForms_Field class)

    // Raw variables
    public $form_raw = '';
    public $actions_raw = '';
    public $options_raw = '';

    /**
     * Construct
     *
     * @since 1.0.0
     *
     * @param int|stdClass $form Form ID or object
     *
     * @return false|BBForms_Form
     */
    public function __construct( $form ) {

        $form = bbforms_get_form( $form );

        if( ! $form ) { return false; }

        // Form fields
        $this->id = $form->id;
        $this->title = $form->title;
        $this->form = $form->form;
        $this->actions = $form->actions;
        $this->set_user_id();

        // Options
        $this->options_raw = $form->options;
        $this->options = bbforms_do_options( $this, $this->user_id, $form->options );

        $this->has_required_fields = ( strpos( $form->form, '*' ) !== false ); // Could give false positives
        $this->has_files = ( strpos( $form->form, '[file' ) !== false ); // Could give false positives

        return $this;

    }

    /**
     * Check if form exists
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function exists() {
        return $this->id !== 0;
    }

    /**
     * Set form user ID (commonly the submitter user)
     *
     * @since 1.0.0
     */
    public function set_user_id() {
        $this->user_id = apply_filters( 'bbforms_form_user_id', get_current_user_id() );
    }

    /**
     * Get form field
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_fields() {
        return $this->fields;
    }

    /**
     * Get form field
     *
     * @since 1.0.0
     *
     * @param string $name The field name
     *
     * @return array
     */
    public function get_field( $name = '' ) {
        return ( ( isset( $this->fields[$name] ) ) ? $this->fields[$name] : null );
    }

    /**
     * Get form field values
     *
     * @since 1.0.0
     *
     * @param bool $sanitized True to get sanitized values, false to get raw values
     *
     * @return array
     */
    public function get_fields_values( $sanitized = false ) {
        $fields = array();

        foreach( $this->fields as $name => $field ) {
            $fields[$name] = ( $sanitized ? $field->sanitized_value : $field->value );
        }

        return $fields;
    }

    /**
     * Get field label
     *
     * @since 1.0.0
     *
     * @param string $name The field name
     *
     * @return string
     */
    public function get_field_label( $name = '' ) {
        $field = $this->get_field( $name );

        return ( $field !== null ? $field->label : '' );
    }

}