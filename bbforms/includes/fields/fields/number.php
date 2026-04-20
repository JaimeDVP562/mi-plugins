<?php
/**
 * Number
 *
 * @package     BBForms\Field_Field\Number
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_Number extends BBForms_Field {

    public $bbcode = 'number';
    public $default_attrs = array(
        'step'  => '',
        'min'   => '',
        'max'   => '',
    );
    public $pattern = '[number* name="CONTENT" step="" min="" max=""]' . "\n";

    public function init() {
        $this->name = __( 'Number Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[number name="" value="CONTENT"]' . "\n",
                'label' => __( 'Basic number field', 'bbforms' ),
            ),
            array(
                'pattern' => '[number* name="" value="CONTENT"]' . "\n",
                'label' => __( 'Required number field', 'bbforms' ),
            ),
            array(
                'pattern' => '[number name="" value="CONTENT" min="0" max="100"]' . "\n",
                'label' => __( 'Limited number field', 'bbforms' ),
            ),
            array(
                'pattern' => '[number name="" value="CONTENT" step="0.1"]' . "\n",
                'label' => __( 'Decimal number field', 'bbforms' ),
            ),
            array(
                'pattern' => '[number name="" value="CONTENT" step="5" min="0" max="100"]' . "\n",
                'label' => __( 'Number field that increments in 5 (only accepts values multiple of 5)', 'bbforms' ),
            ),
            array(
                'pattern' => '[number* name="" value="CONTENT" label="" desc="" placeholder="" step="" min="" max="" id="" class=""]' . "\n",
                'label' => __( 'Number field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/>',
            'input',
            'number',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            esc_attr( $attrs['value'] ),
        );
    }

    public function validate() {
        // Do not validate if field is optional and value is empty
        if( ! $this->is_required() && $this->value === '' ) {
            return true;
        }

        return ( is_numeric( $this->value ) );
    }

    public function get_error_message() {
        return bbforms_get_error_message( 'number_error' );
    }

    public function sanitize() {
        $this->sanitized_value = ( ! is_array( $this->value ) ? floatval( $this->value ) : array_map( 'floatval', $this->value ) );
    }

}
new BBForms_Field_Number();