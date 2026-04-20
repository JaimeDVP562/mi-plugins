<?php
/**
 * BBCode Field
 *
 * @package     BBForms\BBCode_Field
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field {

    public $bbcode = '';
    public $pattern = '';
    public $name = '';
    public $default_attrs = array();
    public $attrs = array();
    public $content = null;
    public $original_attrs = array();

    public $scripts_enqueued = false;

    public $value;
    public $sanitized_value;
    public $form = null;

    public $length_error = '';

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

        global $bbforms_fields;

        if( ! is_array( $bbforms_fields ) ) {
            $bbforms_fields = array();
        }

        // Default pattern for most tags
        if( empty( $this->pattern ) ) {
            $this->pattern = "[{$this->bbcode}* name=\"CONTENT\"]\n";
        }

        $bbforms_fields[$this->bbcode] = $this;

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
            'name' => '',
            'value' => '',
            'pattern' => '',
            'placeholder' => '',
            'required' => '',
            'min' => '',
            'max' => '',
            'label' => '',
            'desc' => '',
            'id' => '',
            'class' => '',
            'style' => '',
            'title' => '',
            'disabled' => '',
            'readonly' => '',
            'autocomplete' => '',
            'autocorrect' => '',
            'spellcheck' => '',
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
        $default_attrs = apply_filters( "bbforms_field_default_attrs", $default_attrs );
        $default_attrs = apply_filters( "bbforms_{$this->bbcode}_field_default_attrs", $default_attrs );

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

        global $bbforms_form, $bbforms_response, $bbforms_request;

        // Assign the global form as form
        $this->form = $bbforms_form;

        // Parse attrs
        $this->original_attrs = $attrs;
        $this->attrs = shortcode_atts( $this->default_attrs(), $attrs, 'bbforms_' . $this->bbcode );

        // For fields, name is required, so ensure it
        if( empty( $this->attrs['name'] ) ) {
            $this->attrs['name'] = $this->generate_field_name();
        }

        // Store all input names while process each bbcode
        $this->form->input_names[] = $this->attrs['name'];

        $skip_value = false;
        if( is_array( $bbforms_response )
            && isset( $bbforms_response['success'] )
            && $bbforms_response['success']
            && $bbforms_form->options['clear_form_on_success'] ) {
            $skip_value = true;
        }

        // Update value attribute if comes from a request
        if( ! $skip_value && is_array( $bbforms_request ) && isset( $bbforms_request[$this->attrs['name']] ) ) {
            $this->attrs['value'] = $bbforms_request[$this->attrs['name']];
        }

        // Ensure form fields
        if( ! property_exists( $this->form, 'fields' ) ) {
            $this->form->fields = array();
        }

        /**
         * Hook to override attrs
         *
         * @since 1.0.0
         *
         * @param array         $attrs
         * @param string|null   $content
         * @param BBForms_Field $field
         *
         * @return array
         */
        $this->attrs = apply_filters( 'bbforms_field_parse_attrs', $this->attrs, $content, $this );

        $this->content = $content;

        // Append the field to the form
        $this->form->fields[$this->attrs['name']] = (object) array(
            'bbcode' => $this->bbcode,
            'label' => ( ! empty( $this->attrs['label'] ) ) ? $this->attrs['label'] : $this->attrs['name'],
            'value' => '',
            'sanitized_value' => '',
            'options' => array(),
            'attrs' => $this->attrs,
        );

    }

    public function generate_field_name( $count = 1 ) {

        $name = $this->bbcode . '_' . $count;

        if( in_array( $name, $this->form->input_names ) ) {
            return $this->generate_field_name( $count + 1 );
        }

        return $name;

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

        global $bbforms_response;

        $this->parse_attrs( $attrs, $content );

        $id = $this->attrs['id'];

        if( $this->attrs['id'] === '' && $this->attrs['name'] !== '' ) {
            $id = $this->attrs['name'];
        }

        $label = $this->get_label();
        $desc = $this->get_desc();

        // Check field form option field_desc_after_label
        if( $this->form && $this->form->options['field_desc_after_label'] ) {
            $label .= $desc;
            $desc = '';
        }

        $this->attrs['aria-invalid'] = 'false';

        $row_classes = 'bbforms-' . esc_attr( $this->bbcode ) . '-field-row';
        $field_classes = 'bbforms-' . esc_attr( $this->bbcode ) . '-field';

        if( $id !== $this->bbcode ) {
            $row_classes .= ' bbforms-' . esc_attr( $id ) . '-field-row';
            $field_classes .= ' bbforms-' . esc_attr( $id ) . '-field';
        }

        if( isset( $attrs['required'] ) && ! empty( $attrs['required'] ) ) {
            $field_classes .= ' bbforms-field-required';
        }

        /**
         * Field row classes
         *
         * @since 1.0.0
         *
         * @param string        $row_classes
         * @param array         $attrs
         * @param string        $content
         * @param BBForms_Field $field
         *
         * @return string
         */
        $row_classes = apply_filters( 'bbforms_field_row_classes', $row_classes, $attrs, $content, $this );

        /**
         * Field classes
         *
         * @since 1.0.0
         *
         * @param string        $field_classes
         * @param array         $attrs
         * @param string        $content
         * @param BBForms_Field $field
         *
         * @return string
         */
        $field_classes = apply_filters( 'bbforms_field_field_classes', $field_classes, $attrs, $content, $this );

        // Scripts
        if( ! $this->scripts_enqueued ) {
            $this->enqueue_scripts();
            $this->scripts_enqueued = true;
        }

        // Error messages
        $error = '';

        if( is_array( $bbforms_response )
        && isset( $bbforms_response['field_messages'] )
        && is_array( $bbforms_response['field_messages'] )
        && isset( $bbforms_response['field_messages'][$this->attrs['name']] ) ) {
            $error .= '<div class="bbforms-error">' . $bbforms_response['field_messages'][$this->attrs['name']] . '</div>';
        }

        return '<div class="bbforms-field-row ' . esc_attr( $row_classes ) . '">'
                . $label
                . '<div class="bbforms-field ' . esc_attr( $field_classes ) . '">'
                    /**
                     * Render field
                     *
                     * @since 1.0.0
                     *
                     * @param string        $field_output   The field output.
                     * @param array         $attrs          Attributes.
                     * @param array|null    $content        The content.
                     * @param BBForms_Field $field          The field object.
                     *
                     * @return string
                     */
                    . apply_filters( 'bbforms_render_field', $this->render_field( $this->attrs, $content ), $this->attrs, $content, $this )
                . '</div>'
                . $desc
                . $error
            . '</div>';

    }

    /**
     * Get the field label
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_label() {

        $id = $this->attrs['id'];

        // Check id
        if( $this->attrs['id'] === '' && $this->attrs['name'] !== '' ) {
            $id = $this->attrs['name'];
        }

        $required = '';
        $label = '';

        // Field required
        if( isset( $this->attrs['required'] ) && $this->attrs['required'] ) {
            $required = ' <span aria-label="' . esc_attr( __( 'Required', 'bbforms' ) ) . '" class="bbforms-required">*</span>';

            $this->attrs['aria-required'] = 'true';
        }

        // Field label
        if( isset( $this->attrs['label'] ) && ! empty( $this->attrs['label'] ) ) {
            $label = '<div class="bbforms-label bbforms-' . esc_attr( $id ) . '-label">'
                . '<label for="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '-label">' . $this->attrs['label'] . $required . '</label>'
                . '</div>';

            $this->attrs['aria-labelledby'] = esc_attr( $id ) . '-label';

            // To make label clickable, the id attribute is required and should match with the label for attribute
            if( $this->attrs['id'] === '' ) {
                $this->attrs['id'] = $id;
            }
        }

        return apply_filters( 'bbforms_field_label', $label, $this );

    }

    /**
     * Get the field description
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_desc() {

        $id = $this->attrs['id'];

        if( $this->attrs['id'] === '' && $this->attrs['name'] !== '' ) {
            $id = $this->attrs['name'];
        }

        $desc = '';

        // Field description
        if( isset( $this->attrs['desc'] ) && ! empty( $this->attrs['desc'] ) ) {
            $desc = '<div class="bbforms-desc bbforms-' . esc_attr( $id ) . '-desc">' . $this->attrs['desc'] . '</div>';
        }

        return apply_filters( 'bbforms_field_desc', $desc, $this );

    }

    /**
     * Parse options from attributes (for options fields like check, radio and select)
     *
     * @since 1.0.0
     *
     * @param array         $attrs      Attributes already parsed
     * @param string|null   $content    The BBCode content
     *
     * @return string
     */
    public function parse_options( $attrs = array(), $content = null ) {

        // Values in content
        if( empty( $attrs['options'] ) && empty( $attrs['options_values'] ) && $content !== null ) {

            if( strpos( $content, "\n" ) !== false ) {
                $content_options = explode( "\n", $content );
                $count = count( $content_options );

                foreach( $content_options as $i => $content_option ) {
                    $content_option = trim( $content_option );

                    if( empty( str_replace( ' ', '', $content_option) ) ) continue;

                    $parts = explode( '|', $content_option, 2 );
                    $option_value = $parts[0];
                    $option_label = isset( $parts[1] ) ? $parts[1] : '';

                    if( empty( $option_label ) ) {
                        $option_label = $option_value;
                    }

                    $attrs['options'] .= $option_label . ( $count-2 > $i ? '|' : '' );
                    $attrs['options_values'] .= $option_value . ( $count-2 > $i ? '|' : '' );
                }
            } else {
                $content_option = trim( $content );
                $parts = explode( '|', $content_option, 2 );

                $option_value = $parts[0];
                $option_label = isset( $parts[1] ) ? $parts[1] : '';

                if( empty( $option_label ) ) {
                    $option_label = $option_value;
                }

                $attrs['options'] .= $option_label;
                $attrs['options_values'] .= $option_value;
            }


        }

        // Use options_values if options not defined
        if( empty( $attrs['options'] ) && ! empty( $attrs['options_values'] ) ) {
            $attrs['options'] = $attrs['options_values'];
        }

        // Use value if options not defined
        if( empty( $attrs['options'] ) && ! empty( $attrs['value'] ) ) {
            if( is_array( $attrs['value'] ) ) {
                $attrs['value'] = implode( "|", $attrs['value'] );
            }

            $attrs['options'] = ( strpos( $attrs['value'], '|' ) !== false ? $attrs['value'] : '' );
        }

        // Ensure options_values
        if( empty( $attrs['options_values'] ) && ! empty( $attrs['options'] ) ) {
            $attrs['options_values'] = $attrs['options'];
        }

        $options = explode( '|', $attrs['options'] );
        $options_values = explode( '|', $attrs['options_values'] );

        // Ensure that all options has their value pair
        foreach( $options as $i => $option ) {
            if( ! isset( $options_values[$i] ) ) {
                $options_values[$i] = $option;
            }
        }

        // Quiz excluded from sanitization
        if( $this->bbcode !== 'quiz' ) {
            // Sanitize all options values
            foreach( $options_values as $i => $option_value ) {
                $options_values[$i] = esc_attr( $option_value );
            }
        }

        $final_options = array();

        foreach( $options as $i => $option ) {
            $value = $options_values[$i];
            $final_options[$value] = $option;
        }

        // Update field options
        $this->form->fields[$this->attrs['name']]->options = $final_options;

        return $final_options;

    }

    /**
     * Submit (called on submitting the form)
     *
     * @since 1.0.0
     *
     * @param array         $attrs      BBCode attributes (not parsed)
     * @param string|null   $content    The BBCode content
     */
    public function submit( $attrs = array(), $content = null ) {

        global $bbforms_form, $bbforms_request, $bbforms_response;

        $this->parse_attrs( $attrs, $content );

        // Handle the value
        if( $this->bbcode === 'file' ) {
            $value = ( isset( $_FILES[$this->attrs['name']] ) ? $_FILES[$this->attrs['name']] : '' );
        } else {
            $value = ( isset( $bbforms_request[$this->attrs['name']] ) ? $bbforms_request[$this->attrs['name']] : '' );
        }

        /**
         * Field submit override
         *
         * @since 1.0.0
         *
         * @param bool          $override
         * @param BBForms_Field $field
         * @param mixed         $value
         *
         * @return bool
         */
        if( apply_filters( 'bbforms_field_submit_override', false, $this, $value ) ) {
            return;
        }

        $this->set_value( $value );
        $this->sanitize();

        // Check if required
        if( ! $this->validate_required() ) {
            $bbforms_response['success'] = false;
            $bbforms_response['field_messages'][$this->attrs['name']] = bbforms_get_error_message( 'required_error' );
            return;
        }

        // Check length
        if( ! $this->validate_length() ) {
            $bbforms_response['success'] = false;
            $bbforms_response['field_messages'][$this->attrs['name']] = $this->length_error;
            return;
        }

        // Check pattern
        if( ! $this->validate_pattern() ) {
            $bbforms_response['success'] = false;
            $bbforms_response['field_messages'][$this->attrs['name']] = bbforms_get_error_message( 'pattern_error' );
            return;
        }

        if( ! $this->validate() ) {
            $bbforms_response['success'] = false;
            $error_message = $this->get_error_message();

            if( ! empty( $error_message ) ) {
                $bbforms_response['field_messages'][$this->attrs['name']] = $this->get_error_message();
            }
        }

        // Update values to the form fields object
        $this->form->fields[$this->attrs['name']]->value = $this->value;
        $this->form->fields[$this->attrs['name']]->sanitized_value = $this->sanitized_value;

    }

    /**
     * helper function to meet if field is required
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function is_required() {
        $is_required = ( isset( $this->attrs['required'] ) && ! empty( $this->attrs['required'] ) );

        return apply_filters( "bbforms_{$this->bbcode}_is_required", $is_required );
    }

    /**
     * Required validation
     * Validates if field is required as has any value
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function validate_required() {
        $valid = ! ( $this->is_required() && $this->value === '' );

        return apply_filters( "bbforms_{$this->bbcode}_validate_required", $valid );
    }

    /**
     * min & max validation
     * Extra: Support to minlength & maxlength
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function validate_length() {

        $valid = true;

        $min = ( isset( $this->attrs['min'] ) && ! empty( $this->attrs['min'] ) ? $this->attrs['min'] : '' );
        $max = ( isset( $this->attrs['max'] ) && ! empty( $this->attrs['max'] ) ? $this->attrs['max'] : '' );

        // Support to minlength and maxlength
        // NOTE: attrs are always passed with strtolower()
        if( isset( $this->attrs['minlength'] ) && ! empty( $this->attrs['minlength'] ) )
            $min = $this->attrs['minlength'];

        if( isset( $this->attrs['maxlength'] ) && ! empty( $this->attrs['maxlength'] ) )
            $max = $this->attrs['maxlength'];

        // Parse values to check
        switch( $this->bbcode ) {
            case 'date':
            case 'time':
                // Date (format Y-m-d)
                // Time (format H:i)
                $value = strtotime( $this->value );
                $min_value = ( $min !== '' ? strtotime( $min ) : '' );
                $max_value = ( $max !== '' ? strtotime( $max ) : '' );
                break;
            case 'number':
            case 'range':
                // Prevent to pass this check
                $value = 0;
                $min_value = 0;
                $max_value = 0;
                break;
            default:
                // Default check strlen()
                $value = ( is_array( $this->value ) ? count( $this->value ) : strlen( $this->value ) );
                $min_value = ( $min !== '' ? absint( $min ) : '' );
                $max_value = ( $max !== '' ? absint( $max ) : '' );
                break;
        }

        if( $min === '' && $max === '' ) {
            return $valid;
        }

        if( $min !== '' && $max === '' ) {
            // Only check min
            if( $value < $min_value ) {
                $valid = false;
                $this->length_error = sprintf( bbforms_get_error_message( 'min_error' ), $min );
            }

        } else if( $min === '' && $max !== '' ) {
            // Only check max
            if( $value > $max_value ) {
                $valid = false;
                $this->length_error = sprintf( bbforms_get_error_message( 'max_error' ), $max );
            }

        } else {
            // Check min and max
            if( $value < $min_value || $value > $max_value ) {
                $valid = false;
                $this->length_error = sprintf( bbforms_get_error_message( 'min_max_error' ), $min, $max );
            }

        }

        return apply_filters( "bbforms_{$this->bbcode}_validate_length", $valid );
    }

    /**
     * Pattern validation
     * Validates the field pattern attribute
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function validate_pattern() {

        $valid = true;

        // TODO: HTML & JS regex are different for PHP regex so we need a "regex translator"
        return $valid;

        if( empty( $this->attrs['pattern'] ) ) {
            return $valid;
        }

        try{
            $regex = $this->attrs['pattern'];
            $valid = ( preg_match("/{$regex}/", $this->value ) === 1 );
        } catch( Exception $e ) {

            // If regex can not be validated in PHP (because are JS focused), lets keep the field as valid
            $valid = true;
        }


        return apply_filters( "bbforms_{$this->bbcode}_validate_pattern", $valid );
    }

    /**
     * Field validation
     * Should be overwritten
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function validate() {
        return true;
    }

    /**
     * Get the error message
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_error_message() {
        return '';
    }

    /**
     * Sanitize the field value
     *
     * @since 1.0.0
     */
    public function sanitize() {
        $this->sanitized_value = ( ! is_array( $this->value ) ? sanitize_text_field( $this->value ) : array_map( 'sanitize_text_field', $this->value ) );
    }

    /**
     * Set field value
     *
     * @since 1.0.0
     */
    public function set_value( $value ) {
        $this->value = $value;
    }

    /**
     * Get field value
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    public function get_value() {
        return $this->value;
    }

}