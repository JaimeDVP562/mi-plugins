<?php
/**
 * Reset
 *
 * @package     BBForms\Field_Field\Reset
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_Reset extends BBForms_Field {

    public $bbcode = 'reset';
    public $default_attrs = array();
    public $pattern = '[reset value="CONTENT"]' . "\n";

    public function init() {
        $this->name = __( 'Reset Button', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[reset value="CONTENT"]' . "\n",
                'label' => __( 'Basic reset button', 'bbforms' ),
            ),
            array(
                'pattern' => '[reset name="' . __( 'Reset', 'bbforms' ) . '"]' . "\n",
                'label' => __( 'Reset button with the text "Reset"', 'bbforms' ),
            ),
            array(
                'pattern' => '[reset name="' . __( 'Clear', 'bbforms' ) . '"]' . "\n",
                'label' => __( 'Reset button with the text "Clear"', 'bbforms' ),
            ),
            array(
                'pattern' => '[reset value="CONTENT" label="" desc="" id="" class=""]' . "\n",
                'label' => __( 'Reset button with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/>',
            'input',
            'reset',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            ( $attrs['value'] !== '' ? esc_attr( $attrs['value'] ) : esc_attr( $attrs['label'] ) ),
        );
    }

    public function get_label() {
        return  ( $this->attrs['value'] !== '' ? parent::get_label() : '' );
    }

}
new BBForms_Field_Reset();