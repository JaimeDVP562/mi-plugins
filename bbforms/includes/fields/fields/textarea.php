<?php
/**
 * Textarea
 *
 * @package     BBForms\Field_Field\Textarea
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_TextArea extends BBForms_Field {

    public $bbcode = 'textarea';
    public $default_attrs = array(
        'min' => '',
        'max' => '',
        'minlength' => '',
        'maxlength' => '',
        'cols' => '',
        'rows' => '',
    );
    public $pattern = '[textarea* name="" value="CONTENT"]' . "\n";

    public function init() {
        $this->name = __( 'Text Area Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[textarea name="" value="CONTENT"]' . "\n",
                'label' => __( 'Basic text area field', 'bbforms' ),
            ),
            array(
                'pattern' => '[textarea* name="" value="CONTENT"]' . "\n",
                'label' => __( 'Required text area field', 'bbforms' ),
            ),
            array(
                'pattern' => '[textarea name="" value="CONTENT" max="100"]' . "\n",
                'label' => __( 'Limited length text area field', 'bbforms' ),
            ),
            array(
                'pattern' => '[textarea name="" value="CONTENT" rows="10"]' . "\n",
                'label' => __( 'Text area with 10 lines visible', 'bbforms' ),
            ),
            array(
                'pattern' => '[textarea* name="" value="CONTENT" label="" desc="" placeholder="" max="" cols="" rows="" id="" class=""]' . "\n",
                'label' => __( 'Text area field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        // max to maxlength
        if( ! empty( $attrs['max'] ) && empty( $attrs['maxlength'] ) ) {
            $attrs['maxlength'] = $attrs['max'];
            unset( $attrs['max'] );
        }

        return sprintf(
            '<%1$s %2$s>%3$s</%1$s>',
            'textarea',
            bbforms_concat_attrs( $attrs, array( 'value', 'label', 'desc' ) ),
            esc_html( $this->attrs['value'] ),
        );
    }

    public function sanitize() {
        $this->sanitized_value = ( ! is_array( $this->value ) ? sanitize_textarea_field( $this->value ) : array_map( 'sanitize_textarea_field', $this->value ) );
    }

}
new BBForms_Field_TextArea();