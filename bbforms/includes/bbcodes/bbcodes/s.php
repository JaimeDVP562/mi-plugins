<?php
/**
 * S
 *
 * @package     BBForms\BBCode\S
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_S extends BBForms_BBCode {

    public $bbcode = 's';

    public function init() {
        $this->name = __( 'Strikethrough', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {
        return sprintf(
            '<%1$s %3$s/>%2$s</%1$s>',
            'del',
            $content,
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_S();