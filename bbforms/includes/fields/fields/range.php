<?php
/**
 * Range
 *
 * @package     BBForms\Field_Field\Range
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_Range extends BBForms_Field {

    public $bbcode = 'range';
    public $default_attrs = array(
        'step' => '',
        'min' => '0',
        'max' => '100',
    );
    public $pattern = '[range* name="CONTENT" min="" max=""]' . "\n";

    public function init() {
        $this->name = __( 'Range Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[range name="" value="CONTENT" min="" max=""]' . "\n",
                'label' => __( 'Basic range field', 'bbforms' ),
            ),
            array(
                'pattern' => '[range* name="" value="CONTENT" min="" max=""]' . "\n",
                'label' => __( 'Required range field', 'bbforms' ),
            ),
            array(
                'pattern' => '[range name="" value="CONTENT" step="0.1" min="0" max="1"]' . "\n",
                'label' => __( 'Decimal range field', 'bbforms' ),
            ),
            array(
                'pattern' => '[range name="" value="CONTENT" step="5" min="0" max="100"]' . "\n",
                'label' => __( 'Range field that increments in 5', 'bbforms' ),
            ),
            array(
                'pattern' => '[range* name="" value="CONTENT" label="" desc="" step="" min="" max="" id="" class=""]' . "\n",
                'label' => __( 'Range field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/><span class="bbforms-field-range-output">%4$s</span>',
            'input',
            'range',
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
new BBForms_Field_Range();