<?php
/**
 * Center
 *
 * @package     BBForms\BBCode\Center
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_Center extends BBForms_BBCode {

    public $bbcode = 'center';

    public function init() {
        $this->name = __( 'Align Center', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( ! isset( $attrs['style'] ) ) $attrs['style'] = '';

        $attrs['style'] .= 'text-align:center;';

        return sprintf(
            '<%1$s %3$s/>%2$s</%1$s>',
            'p',
            $content,
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_Center();