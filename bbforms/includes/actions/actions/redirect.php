<?php
/**
 * Redirect
 *
 * @package     BBForms\Actions\Redirect
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Action_Redirect extends BBForms_Action {

    public $bbcode = 'redirect';
    public $default_attrs = array(
        'to' => '',
    );
    public $pattern = '[redirect to=""]CONTENT[/redirect]' . "\n";

    public function init() {
        $this->name = __( 'Redirect', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[redirect to=""]CONTENT[/redirect]' . "\n",
                'label' => __( 'Basic redirect action', 'bbforms' ),
            ),
            array(
                'pattern' => '[redirect to="{site.url}"]CONTENT[/redirect]' . "\n",
                'label' => __( 'Redirect to home page', 'bbforms' ),
            ),
        );
    }

    public function process_action( $attrs = array(), $content = null ) {

        global $bbforms_response;

        if( empty( $attrs['to'] ) ) {
            return false;
        }

        if( ! filter_var( $attrs['to'], FILTER_VALIDATE_URL ) ) {
            return false;
        }

        $this->content = $content;

        $bbforms_response['actions']['redirect'] = array(
            'to' => $attrs['to'],
            'content' => ( $content !== null ? $content : '' ),
        );

        return true;
    }

    public function get_success_message() {
        // Override success message
        if( $this->content !== null ) {
            $this->attrs['success_message'] = $this->content;
        }

        return parent::get_success_message();
    }

}
new BBForms_Action_Redirect();