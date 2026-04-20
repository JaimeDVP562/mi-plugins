<?php
/**
 * Delete Request
 *
 * @package     BBForms\Actions\Delete_Request
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Action_Delete_Request extends BBForms_Action {

    public $bbcode = 'delete_request';
    public $default_attrs = array(
        'email' => '',
        'anonymize' => 'no',
        'duplicated_message' => '',
    );
    public $duplicated = false;

    public $pattern = '[delete_request email=""]CONTENT[/delete_request]' . "\n";

    public function init() {
        $this->name = __( 'Delete user data request', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[delete_request email="" anonymize="no"]CONTENT[/delete_request]' . "\n",
                'label' => __( 'Basic delete user data request action', 'bbforms' ),
            ),
            array(
                'pattern' => '[delete_request email="" anonymize="yes"]CONTENT[/delete_request]' . "\n",
                'label' => __( 'Anonymize action', 'bbforms' ),
            ),
            array(
                'pattern' => '[delete_request email="" anonymize="no" error_message="' . __( 'Invalid email address.', 'bbforms' ) . '" duplicated_message="' . __( 'A request for this email address already exists.', 'bbforms' ) . '"]CONTENT[/delete_request]' . "\n",
                'label' => __( 'Notify about duplicated request or invalid email', 'bbforms' ),
            ),
        );
    }

    public function process_action( $attrs = array(), $content = null ) {

        $this->duplicated = false;

        if( $content !== null && ! empty( $content ) ) {
            $this->content = $content;
        }

        $email = sanitize_email( $attrs['email'] );

        if ( ! is_email( $email ) ) {
            return false;
        }

        $request_id = wp_create_user_request( $email, 'remove_personal_data' );

        if ( $request_id instanceof WP_Error ) {
            // Duplicated request
            $this->duplicated = true;
            return false;
        }

        add_post_meta( $request_id, 'bbforms_anonymize', ( bbforms_is_option_enabled( $attrs['anonymize'] ) ? '1' : '0' ) );

        // Notify about the request
        wp_send_user_request( $request_id );

        return true;
    }

    public function get_success_message() {
        // Override success message
        if( $this->content !== null && ! empty( $this->content ) ) {
            $this->attrs['success_message'] = $this->content;
        }

        return parent::get_success_message();
    }

    public function get_error_message() {
        // Override error message
        if( $this->duplicated && ! empty( $this->attrs['duplicated_message'] ) ) {
            $this->attrs['error_message'] = $this->attrs['duplicated_message'];
        }

        return parent::get_error_message();
    }

}
new BBForms_Action_Delete_Request();