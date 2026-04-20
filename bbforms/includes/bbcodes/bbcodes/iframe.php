<?php
/**
 * Iframe
 *
 * @package     BBForms\BBCode\Iframe
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_BBCode_Iframe extends BBForms_BBCode {

    public $bbcode = 'iframe';
    public $pattern = '[iframe src="CONTENT" width="" height=""]';
    public $default_attrs = array(
        'allow' => '',
        'allowfullscreen' => '',
        'allowpaymentrequest' => '',
        'csp' => '',
        'importance' => '',
        'name' => '',
        'referrerpolicy' => '',
        'sandbox' => '',
        'seamless' => '',
        'src' => '',
        'srcdoc' => '',
        'width' => '',
        'height' => '',
        // Deprecated but continue working on most browsers
        'align' => '',
        'frameborder' => '',
        'longdesc' => '',
        'marginheight' => '',
        'marginwidth' => '',
        'scrolling' => '',

    );

    public function init() {
        $this->name = __( 'Iframe', 'bbforms' );
    }

    public function render_field( $attrs = array(), $content = null ) {

        if( ! isset( $attrs['value'] ) ) $attrs['value'] = '';
        if( ! isset( $attrs['src'] ) ) $attrs['src'] = '';
        if( ! isset( $attrs['srcdoc'] ) ) $attrs['srcdoc'] = '';

        return sprintf(
            '<%1$s %3$s/>%2$s</%1$s>',
            'iframe',
            $content,
            bbforms_concat_attrs( $attrs, array( 'value' ), "'" )
        );
    }

}
new BBForms_BBCode_Iframe();