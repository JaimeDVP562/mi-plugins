<?php
/**
 * Tel
 *
 * @package     BBForms\Field_Field\Tel
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Field_Tel extends BBForms_Field {

    public $bbcode = 'tel';
    public $default_attrs = array(
        'pattern' => '',
    );
    public $pattern = '[tel* name="" value="CONTENT"]' . "\n";

    public function init() {
        $this->name = __( 'Tel Field', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[tel name="" value="CONTENT"]' . "\n",
                'label' => __( 'Basic phone field', 'bbforms' ),
            ),
            array(
                'pattern' => '[tel* name="" value="CONTENT"]' . "\n",
                'label' => __( 'Required phone field', 'bbforms' ),
            ),
            array(
                'pattern' => '[tel name="" value="CONTENT" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" placeholder="555-555-5555"]' . "\n",
                'label' => __( 'Formatted phone field (555-555-5555)', 'bbforms' ),
            ),
            array(
                'pattern' => '[tel name="" value="CONTENT" pattern="[0-9]{9}" placeholder="999999999"]' . "\n",
                'label' => __( 'Formatted phone field 2 (999999999)', 'bbforms' ),
            ),
            array(
                'pattern' => '[tel name="CONTENT" value="{user.meta._billing_phone}" label="' . __( 'Phone', 'bbforms' ) . '" desc="' . __( 'Your phone', 'bbforms' ) . '" placeholder="' . __( 'Enter your phone here', 'bbforms' ) . '"]' . "\n",
                'label' => __( 'User phone field from the WooCommerce user meta "_billing_phone" (autofilled if user is logged in)', 'bbforms' ),
            ),
            array(
                'pattern' => '[tel* name="" value="CONTENT" label="" desc="" placeholder="" pattern="" id="" class=""]' . "\n",
                'label' => __( 'Phone field with several attributes', 'bbforms' ),
            ),
        );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s type="%2$s" %3$s value="%4$s"/>',
            'input',
            'tel',
            bbforms_concat_attrs( $attrs, array( 'type', 'value', 'label', 'desc' ) ),
            esc_attr( $attrs['value'] ),
        );
    }

}
new BBForms_Field_Tel();