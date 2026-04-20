<?php
/**
 * Justify
 *
 * @package     BBForms\BBCode\Justify
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_Justify extends BBForms_BBCode {

    public $bbcode = 'justify';

    public function init() {
        $this->name = __( 'Justify', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( ! isset( $attrs['style'] ) ) $attrs['style'] = '';

        $attrs['style'] .= 'text-align:justify;';

        return sprintf(
            '<%1$s %3$s/>%2$s</%1$s>',
            'p',
            $content,
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_Justify();