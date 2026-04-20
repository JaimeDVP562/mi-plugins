<?php
/**
 * Left
 *
 * @package     BBForms\BBCode\Left
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_Left extends BBForms_BBCode {

    public $bbcode = 'left';

    public function init() {
        $this->name = __( 'Align Left', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( ! isset( $attrs['style'] ) ) $attrs['style'] = '';

        $attrs['style'] .= 'text-align:left;';

        return sprintf(
            '<%1$s %3$s/>%2$s</%1$s>',
            'p',
            $content,
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_Left();