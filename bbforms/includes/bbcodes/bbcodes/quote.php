<?php
/**
* Quote
 *
* @package     BBForms\BBCode\Quote
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_Quote extends BBForms_BBCode {

    public $bbcode = 'quote';

    public function init() {
        $this->name = __( 'Quote', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( ! empty( $attrs['value'] ) ) {
            $attrs['value'] = '<div>' . $attrs['value'] . '</div>';
        }

        return sprintf(
            '%3$s<%1$s %4$s/><div>%2$s</div></%1$s>',
            'blockquote',
            $content,
            $attrs['value'],
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_Quote();