<?php
/**
 * Submit
 *
 * @package     BBForms\Field_Field\Submit
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_Submit extends BBForms_Field {

    public $bbcode = 'submit';
    public $default_attrs = array();
    public $pattern = '[submit value="CONTENT"]' . "\n";

    public function init() {
        $this->name = __( 'Submit Button', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[submit value="CONTENT"]' . "\n",
                'label' => __( 'Basic submit button', 'bbforms' ),
            ),
            array(
                'pattern' => '[submit value="' . __( 'Submit', 'bbforms' ) . '"]' . "\n",
                'label' => __( 'Submit button with the text "Submit"', 'bbforms' ),
            ),
            array(
                'pattern' => '[submit value="' . __( 'Send', 'bbforms' ) . '"]' . "\n",
                'label' => __( 'Submit button with the text "Send"', 'bbforms' ),
            ),
            array(
                'pattern' => '[submit value="CONTENT" label="" desc="" id="" class=""]' . "\n",
                'label' => __( 'Submit button with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/><span class="bbforms-spinner"></span>',
            'input',
            'submit',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            ( $attrs['value'] !== '' ? esc_attr( $attrs['value'] ) : esc_attr( $attrs['label'] ) ),
        );
    }

    public function get_label() {
        return  ( $this->attrs['value'] !== '' ? parent::get_label() : '' );
    }

}
new BBForms_Field_Submit();