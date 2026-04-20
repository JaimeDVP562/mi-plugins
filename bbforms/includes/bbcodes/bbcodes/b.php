<?php
/**
 * B
 *
 * @package     BBForms\BBCode\B
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_B extends BBForms_BBCode {

    public $bbcode = 'b';

    public function init() {
        $this->name = __( 'Bold', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s %3$s/>%2$s</%1$s>',
            'strong',
            $content,
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_B();