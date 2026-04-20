<?php
/**
 * Message
 *
 * @package     BBForms\Actions\Message
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Action_Message extends BBForms_Action {

    public $bbcode = 'message';
    public $default_attrs = array( 'type' => 'info', 'content' => '' );
    public $content;

    public $pattern = '[message type="info"]CONTENT[/message]' . "\n";

    public function init() {
        $this->name = __( 'Show message after submission', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[message]CONTENT[/message]' . "\n",
                'label' => __( 'Basic message action (shows a info message)', 'bbforms' ),
            ),
            array(
                'pattern' => '[message type="info"]CONTENT[/message]' . "\n",
                'label' => __( 'Info message action', 'bbforms' ),
            ),
            array(
                'pattern' => '[message type="success"]CONTENT[/message]' . "\n",
                'label' => __( 'Success message action', 'bbforms' ),
            ),
            array(
                'pattern' => '[message type="warning"]CONTENT[/message]' . "\n",
                'label' => __( 'Warning message action', 'bbforms' ),
            ),
            array(
                'pattern' => '[message type="error"]CONTENT[/message]' . "\n",
                'label' => __( 'Error message action', 'bbforms' ),
            ),
        );
    }

    public function process_action( $attrs = array(), $content = null ) {
        $this->content = null;

        if( $attrs['content'] === '' ) {
            $this->content = $content;
        } else {
            $this->content = $attrs['content'];
        }

        if( ! in_array( $attrs['type'], array( 'info', 'success', 'warning', 'error', 'none' ) ) ) {
            $this->attrs['type'] = 'info';
        }

        // Always return success
        return true;
    }

    public function get_success_message() {

        // Bail if no message provided
        if( $this->content === null ) return '';

        $type = $this->attrs['type'];

        return array(
            'text' => $this->content,
            'type' => $type,
        );
    }

}
new BBForms_Action_Message();