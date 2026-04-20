<?php
/**
 * Time
 *
 * @package     BBForms\Field_Field\Time
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_Time extends BBForms_Field {

    public $bbcode = 'time';
    public $default_attrs = array(
        'min'   => '',
        'max'   => '',
    );
    public $pattern = '[time* name="" value="CONTENT"]' . "\n";

    public function init() {
        $this->name = __( 'Time Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[time name="" value="CONTENT"]' . "\n",
                'label' => __( 'Basic time field', 'bbforms' ),
            ),
            array(
                'pattern' => '[time* name="" value="CONTENT"]' . "\n",
                'label' => __( 'Required time field', 'bbforms' ),
            ),
            array(
                'pattern' => '[time name="" value="CONTENT" min="08:00" max="16:00"]' . "\n",
                'label' => __( 'Limited time field', 'bbforms' ),
            ),
            array(
                'pattern' => '[time* name="" value="CONTENT" label="" desc="" min="" max="" id="" class=""]' . "\n",
                'label' => __( 'Time field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/>',
            'input',
            'time',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            esc_attr( $attrs['value'] ),
        );
    }

    public function validate() {
        // Do not validate if field is optional and value is empty
        if( ! $this->is_required() && $this->value === '' ) {
            return true;
        }

        return ( preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $this->value ) === 1 );
    }

    public function get_error_message() {
        return bbforms_get_error_message( 'time_error' );
    }

}
new BBForms_Field_Time();