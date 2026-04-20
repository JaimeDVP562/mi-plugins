<?php
/**
 * Email
 *
 * @package     BBForms\Actions\Email
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class BBForms_Action_Email extends BBForms_Action {

    public $bbcode = 'email';
    public $default_attrs = array(
        'from' => '',
        'from_name' => '',
        'to' => '',
        'cc' => '',
        'bcc' => '',
        'reply_to' => '',
        'subject' => '',
        'attachments' => '',
    );
    public $pattern = '[email from="" from_name="" to="" cc="" bcc="" reply_to="" subject=""]CONTENT[/email]' . "\n";

    public function init() {
        $this->name = __( 'Send email', 'bbforms' );
        $this->pattern = array(
            array(
                'pattern' => '[email to="" reply_to="" subject=""]CONTENT[/email]' . "\n",
                'label' => __( 'Basic email action', 'bbforms' ),
            ),
            array(
                'pattern' => '[email to="{site.admin_email}" reply_to="{field.email}" subject="{site.name} - ' . __( 'New submission', 'bbforms' ) . '"]CONTENT_FIELDS_TABLE[/email]' . "\n",
                'label' => __( 'Email site admin about form submitted + Reply to form submitter email', 'bbforms' ),
            ),
            array(
                'pattern' => '[email to="{field.email}" subject="{site.name} - ' . __( 'Submission received', 'bbforms' ) . '"]CONTENT_FIELDS_TABLE[/email]' . "\n",
                'label' => __( 'Email confirmation to form submitter', 'bbforms' ),
            ),
            array(
                'pattern' => '[email from="" from_name="" to="" cc="" bcc="" reply_to="" subject=""]CONTENT[/email]' . "\n",
                'label' => __( 'Email action with several attributes', 'bbforms' ),
            ),
        );
    }

    public function process_action( $attrs = array(), $content = null ) {

        // From
        if( empty( $attrs['from'] ) ) {
            $attrs['from'] = get_bloginfo( 'admin_email' );
        }

        $attrs['from'] = sanitize_email( $attrs['from'] );
        $this->attrs['from'] = $attrs['from'];

        // From Name
        if( empty( $attrs['from_name'] ) ) {
            $attrs['from_name'] = get_bloginfo( 'name' );
        }

        $attrs['from_name'] = wp_specialchars_decode( $attrs['from_name'] );
        $this->attrs['from_name'] = $attrs['from_name'];

        // Sanitize
        $attrs['to'] = $this->sanitize_emails( $attrs['to'] );
        $attrs['cc'] = $this->sanitize_emails( $attrs['cc'] );
        $attrs['bcc'] = $this->sanitize_emails( $attrs['bcc'] );

        // Content
        if( $content === null ) {
            $content = bbforms_do_tags( $this->form, $this->form->user_id, '{fields_table}' );
        }

        $content = wpautop( $content );

        // Headers
        $headers = array();

        if( ! empty( $attrs['from'] ) ) {
            $headers[] = 'From: <' . $attrs['from'] . '>';
        }

        if ( ! empty( $attrs['cc'] ) ) {
            $headers[] = 'Cc: ' . $attrs['cc'];
        }

        if ( ! empty( $attrs['bcc'] ) ) {
            $headers[] = 'Bcc: ' . $attrs['bcc'];
        }

        $headers[] = 'Content-Type: text/html; charset='  . get_option( 'blog_charset' );

        // Attachments
        $attachments = array();

        if ( ! empty( $attrs['attachments'] ) ) {
            $wp_filesystem = bbforms_get_filesystem();

            $files = explode( ',', $attrs['attachments'] );

            foreach( $files as $file ) {
                $file = trim( $file );

                if( $wp_filesystem->exists( $file ) ) {
                    $attachments[] = $file;
                }
            }
        }

        add_filter( 'wp_mail_from', array( $this, 'email_from' ) );
        add_filter( 'wp_mail_from_name', array( $this, 'email_from_name' ) );
        add_filter( 'wp_mail_content_type', array( $this, 'email_content_type' ) );

        // Send the email
        $result = wp_mail( $attrs['to'], $attrs['subject'], $content, $headers, $attachments );

        if( ! $result ) {
            /**
             * Email action error
             *
             * @since 1.0.0
             *
             * @param string|string[]   $to             Array or comma-separated list of email addresses to send message.
             * @param string            $subject        Email subject.
             * @param string            $content        Message contents.
             * @param string|string[]   $headers        Optional. Additional headers.
             * @param string|string[]   $attachments    Optional. Paths to files to attach.
             * @param array             $attrs          Action attributes.
             * @param bool              $result         Whether the email was sent successfully.
             */
            do_action( 'bbforms_email_action_error', $attrs['to'], $attrs['subject'], $content, $headers, $attachments, $attrs, $result );
        } else {
            /**
             * Email action success
             *
             * @since 1.0.0
             *
             * @param string|string[]   $to             Array or comma-separated list of email addresses to send message.
             * @param string            $subject        Email subject.
             * @param string            $content        Message contents.
             * @param string|string[]   $headers        Optional. Additional headers.
             * @param string|string[]   $attachments    Optional. Paths to files to attach.
             * @param array             $attrs          Action attributes.
             * @param bool              $result         Whether the email was sent successfully.
             */
            do_action( 'bbforms_email_action_success', $attrs['to'], $attrs['subject'], $content, $headers, $attachments, $attrs, $result );
        }

        remove_filter( 'wp_mail_from', array( $this, 'email_from' ) );
        remove_filter( 'wp_mail_from_name', array( $this, 'email_from_name' ) );
        remove_filter( 'wp_mail_content_type', array( $this, 'email_content_type' ) );

        if( ! $result ) {
            // TODO: Admin email about incorrect email sent?
            // TODO: Store on error logs table (or in a error log file?)
            return false;
        }

        return true;
    }

    public function sanitize_emails( $email ) {
        $emails = explode( ',', $email );

        foreach( $emails as $i => $email ) {
            $email = trim( $email );
            $email = sanitize_email( $email );
            $emails[$i] = $email;
        }

        return implode( ',', $emails );
    }

    public function email_from() {
        return $this->attrs['from'];
    }

    public function email_from_name() {
        return $this->attrs['from_name'];
    }

    public function email_content_type() {
        return 'text/html';
    }

}
new BBForms_Action_Email();