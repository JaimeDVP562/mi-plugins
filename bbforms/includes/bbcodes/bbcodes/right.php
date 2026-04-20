<?php
/**
 * Right
 *
 * @package     BBForms\BBCode\Right
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_Right extends BBForms_BBCode {

    public $bbcode = 'right';

    public function init() {
        $this->name = __( 'Align Right', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( ! isset( $attrs['style'] ) ) $attrs['style'] = '';

        $attrs['style'] .= 'text-align:right;';

        return sprintf(
            '<%1$s %3$s/>%2$s</%1$s>',
            'p',
            $content,
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_Right();