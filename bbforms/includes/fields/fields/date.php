<?php
/**
 * Date
 *
 * @package     BBForms\Field_Field\Date
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_Date extends BBForms_Field {

    public $bbcode = 'date';
    public $default_attrs = array(
        'min'   => '',
        'max'   => '',
    );
    public $pattern = '[date* name="" value="CONTENT"]' . "\n";

    public function init() {
        $this->name = __( 'Date Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[date name="" value="CONTENT"]' . "\n",
                'label' => __( 'Basic date field', 'bbforms' ),
            ),
            array(
                'pattern' => '[date* name="" value="CONTENT"]' . "\n",
                'label' => __( 'Required date field', 'bbforms' ),
            ),
            array(
                'pattern' => '[date name="" value="CONTENT" min="2025-01-01" max="2025-12-31"]' . "\n",
                'label' => __( 'Limited date field', 'bbforms' ),
            ),
            array(
                'pattern' => '[date name="CONTENT" value="{user.meta.birthday}" label="' . __( 'Birthday', 'bbforms' ) . '" desc="' . __( 'Your birthday', 'bbforms' ) . '" placeholder="' . __( 'Enter your birthday here', 'bbforms' ) . '"]' . "\n",
                'label' => __( 'User birthday field from a custom user meta named "birthday" (autofilled if user is logged in)', 'bbforms' ),
            ),
            array(
                'pattern' => '[date* name="" value="CONTENT" label="" desc="" min="" max="" id="" class=""]' . "\n",
                'label' => __( 'Date field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/>',
            'input',
            'date',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            esc_attr( $attrs['value'] ),
        );
    }

    public function validate() {
        // Do not validate if field is optional and value is empty
        if( ! $this->is_required() && $this->value === '' ) {
            return true;
        }

        return ( strtotime( $this->value ) !== false );
    }

    public function get_error_message() {
        return bbforms_get_error_message( 'date_error' );
    }

}
new BBForms_Field_Date();