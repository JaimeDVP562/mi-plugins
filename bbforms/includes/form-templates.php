<?php
/**
 * Form Templates
 *
 * @package     BBForms\Form_Templates
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Registered form templates
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_form_templates() {

    $n1 = wp_rand( 101, 998 );
    $n2 = wp_rand( 101, 998 );
    $n3 = wp_rand( 101, 998 );
    $n4 = wp_rand( 101, 998 );
    $n5 = wp_rand( 101, 998 );
    $n6 = $n1 + 1;
    $n7 = $n2 - 1;
    $n8 = $n3 + 1;
    $n9 = $n4 - 1;
    $n10 = $n5 + 1;

    $form_templates = array(
        'blank' => array(
            'title' => esc_html__( 'Blank Form', 'bbforms' ),
            'desc' => esc_html__( 'A blank form to let you create the form of your choice using our code editor.', 'bbforms' ),
            'form' => '' . "\n",
            'actions' => '[record]' . "\n"
            . '[message type="success"]{settings.submit_success_message}[/message]',
            'options' => bbforms_get_options_code(),
        ),
        'contact' => array(
            'title' => esc_html__( 'Contact Form', 'bbforms' ),
            'desc' => esc_html__( 'A sample contact form to let your users get in touch with you.', 'bbforms' ),
            'form' => '[text* name="name_' . $n1 . '" value="{user.display_name}" label="' . __( 'Name', 'bbforms' ) . '" placeholder="' . __( 'Enter your name here', 'bbforms' ) . '"]' . "\n\n"

                . '[email* name="email_' . $n2 . '" value="{user.email}" label="' . __( 'Email', 'bbforms' ) . '" placeholder="' . __( 'Enter your email here', 'bbforms' ) . '"]' . "\n\n"

                . '[textarea* name="textarea_' . $n3 . '" value="" label="' . __( 'Message', 'bbforms' ) . '" placeholder="' . __( 'Enter your message here', 'bbforms' ) . '" rows="5"]' . "\n\n"

                // translators: %s: Privacy Policy
                . '[check* name="check_' . $n4 . '" value=""]1|' . sprintf( __( 'I have read and agree with your %s.', 'bbforms' ), '[url="{site.privacy_policy_url}" target="_blank"]' . __( 'Privacy Policy', 'bbforms' ) . '[/url]' ) . '[/check]' . "\n\n"

                . '[submit value="' . __( 'Submit', 'bbforms' ) . '"]',
            'actions' => '[record]' . "\n"
                . '[email to="{site.admin_email}" reply_to="{field.email_' . $n2 . '}" subject="{site.name} - ' . __( 'New submission', 'bbforms' ) . '"]{fields_table}[/email]' . "\n"
                . '[email to="{field.email_' . $n2 . '}" subject="{site.name} - ' . __( 'Submission received', 'bbforms' ) . '"]{fields_table}[/email]' . "\n"
                // translators: %s: Email address
                . '[message type="success"]{settings.submit_success_message} ' . sprintf( __( 'A confirmation email was sent to %s', 'bbforms' ), ' {field.email_' . $n2 . '}' ) . '.[/message]',
            'options' => bbforms_get_options_code(),
        ),
        'job_application' => array(
            'title' => esc_html__( 'Job Application', 'bbforms' ),
            'desc' => esc_html__( 'A sample job application form to let your users apply for a job and attach their curriculum in PDF, DOC or DOCX format.', 'bbforms' ),
            'form' => '<h4>' . __( 'About you', 'bbforms' ) . '</h4>' . "\n\n"

                . '[row]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[text* name="first_name_' . $n1 . '" value="{user.first_name}" label="' . __( 'First Name', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[text* name="last_name_' . $n2 . '" value="{user.last_name}" label="' . __( 'Last Name', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . '[/row]' . "\n\n"

                . '[row]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[email* name="email_' . $n3 . '" value="{user.email}" label="' . __( 'Email', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[tel* name="tel_' . $n4 . '" value="" label="' . __( 'Phone', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . '[/row]' . "\n\n"

                . '<h4>' . __( 'Address', 'bbforms' ) . '</h4>' . "\n\n"

                . '[text* name="address_1_' . $n5 . '" value="" label="' . __( 'Address Line 1', 'bbforms' ) . '"]' . "\n\n"

                . '[text name="address_2_' . $n6 . '" value="" label="' . __( 'Address Line 2', 'bbforms' ) . '"]' . "\n\n"

                . '[row]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[text* name="country_' . $n7 . '" value="" label="' . __( 'Country', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[text* name="city_' . $n8 . '" value="" label="' . __( 'City', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . "\t" . '[column="25%"]' . "\n"
                . "\t\t" . '[text* name="zip_' . $n9 . '" value="" label="' . __( 'Zip', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . '[/row]' . "\n\n"

                . '<h4>' . __( 'Previous Job', 'bbforms' ) . '</h4>' . "\n\n"

                . '[row]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[text name="job_title_' . $n10 . '" value="" label="' . __( 'Title', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[date name="date_' . $n1 . '" value="" label="' . __( 'Started', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[date name="date_' . $n2 . '" value="" label="' . __( 'Ended', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . '[/row]' . "\n\n"

                . '[textarea name="textarea_' . $n3 . '" value="" label="' . __( 'Describe your tasks', 'bbforms' ) . '" rows="5"]' . "\n\n"

                . '<h4>' . __( 'References', 'bbforms' ) . '</h4>' . "\n\n"

                // translators: %d: Number
                . '[b]' . sprintf( __( 'Reference %d', 'bbforms' ), 1 ) . ':[/b]' . "\n"
                . '[row]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[text name="ref_' . $n4 . '" value="" label="' . __( 'Name', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[tel name="ref_tel_' . $n5 . '" value="" label="' . __( 'Phone', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . '[/row]' . "\n\n"

                // translators: %d: Number
                . '[b]' . sprintf( __( 'Reference %d', 'bbforms' ), 2 ) . ':[/b]' . "\n"
                . '[row]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[text name="ref_' . $n6 . '" value="" label="' . __( 'Name', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[tel name="ref_tel_' . $n7 . '" value="" label="' . __( 'Phone', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . '[/row]' . "\n\n"

                . '[file* name="file_' . $n8 . '" accept=".pdf,.doc,.docx" max="10mb" label="' . __( 'CV', 'bbforms' ) . '" desc="' . __( 'Upload your CV in PDF or DOC format.', 'bbforms' ) . '"]' . "\n\n"

                . '[submit value="' . __( 'Apply', 'bbforms' ) . '"]',
            'actions' => '[record]' . "\n"
                . '[email to="{site.admin_email}" reply_to="{field.email_' . $n3 . '}" attachments="{file.file_' . $n8 . '}" subject="{site.name} - ' . __( 'New submission', 'bbforms' ) . '"]{fields_table}[/email]' . "\n"
                . '[email to="{field.email_' . $n3 . '}" subject="{site.name} - ' . __( 'Submission received', 'bbforms' ) . '"]{fields_table}[/email]' . "\n"
                // translators: %s: Email Address
                . '[message type="success"]{settings.submit_success_message}{settings.submit_success_message} ' . sprintf( __( 'A confirmation email was sent to %s', 'bbforms' ), ' {field.email_' . $n2 . '}' ) . '.[/message]',
            'options' => bbforms_get_options_code(),
        ),
        'support_ticket' => array(
            'title' => esc_html__( 'Support Ticket', 'bbforms' ),
            'desc' => esc_html__( 'A more advanced contact form to let your users describe a problem, explain how to reproduce it and attach an image or video.', 'bbforms' ),
            'form' => '[row]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[text* name="name_' . $n1 . '" value="{user.display_name}" label="' . __( 'Name', 'bbforms' ) . '" placeholder="' . __( 'Enter your name here', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t". '[email* name="email_' . $n2 . '" value="{user.email}" label="' . __( 'Email', 'bbforms' ) . '" placeholder="' . __( 'Enter your email here', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . '[/row]' . "\n\n"

                . '[select* name="select_' . $n3 . '" value="" label="' . __( 'Topic', 'bbforms' ) . '" placeholder="' . __( 'Please, select an option', 'bbforms' ) . '"]' . "\n"
                . __( 'Pre-sales Inquiry', 'bbforms' ) . "\n"
                . __( 'General Inquiry', 'bbforms' ) . "\n"
                . __( 'Report a problem', 'bbforms' ) . "\n"
                . __( 'Other', 'bbforms' ) . "\n"
                . '[/select]' . "\n\n"

                . '[textarea* name="textarea_' . $n4 . '" value="" label="' . __( 'Issue', 'bbforms' ) . '" desc="' . __( 'Provide a detailed description of your issue.', 'bbforms' ) . '" placeholder="' . __( 'Describe your issue here', 'bbforms' ) . '" rows="5"]' . "\n\n"

                . '[textarea* name="textarea_' . $n5 . '" value="" label="' . __( 'Steps to reproduce', 'bbforms' ) . '" desc="' . __( 'Describe the steps to reproduce your issue.', 'bbforms' ) . '" placeholder="' . __( 'Example:', 'bbforms' ) . "\n" . __( '1 - Do this', 'bbforms' ) . "\n" . __( '2 - Then do this', 'bbforms' ) . "\n" . __( '3 - ...', 'bbforms' ) . '" rows="5"]' . "\n\n"

                . '[file name="file_' . $n6 . '" accept="image/*,video/*" max="10mb" label="' . __( 'Evidence', 'bbforms' ) . '" desc="' . __( 'You can upload an image or video to evidence your issue.', 'bbforms' ) . '"]' . "\n\n"

                // translators: %s: Privacy Policy
                . '[check* name="check_' . $n7 . '" value=""]1|' . sprintf( __( 'I have read and agree with your %s.', 'bbforms' ), '[url="{site.privacy_policy_url}" target="_blank"]' . __( 'Privacy Policy', 'bbforms' ) . '[/url]' ) . '[/check]' . "\n\n"

                . '[submit value="' . __( 'Open a new ticket', 'bbforms' ) . '"]',
            'actions' => '[record]' . "\n"
                . '[email to="{site.admin_email}" reply_to="{field.email_' . $n2 . '}" attachments="{field.file_' . $n6 . '}" subject="{site.name} - ' . __( 'New ticket', 'bbforms' ) . ': {field.select_' . $n3 . '}"]{fields_table}[/email]' . "\n"
                . '[email to="{field.email_' . $n2 . '}" subject="{site.name} - ' . __( 'Ticket received', 'bbforms' ) . '"]{fields_table}[/email]' . "\n"
                // translators: %s: Email address
                . '[message type="success"]{settings.submit_success_message} ' . sprintf( __( 'A confirmation email was sent to %s', 'bbforms' ), ' {field.email_' . $n2 . '}' ) . '.[/message]',
            'options' => bbforms_get_options_code(),
        ),
        'feedback' => array(
            'title' => esc_html__( 'Feedback', 'bbforms' ),
            'desc' => esc_html__( 'A sample feedback form to collect information about your users experience and opinion.', 'bbforms' ),
            'form' => '[row]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[text* name="name_' . $n1 . '" value="{user.display_name}" label="' . __( 'Name', 'bbforms' ) . '" placeholder="' . __( 'Enter your name here', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t". '[email* name="email_' . $n2 . '" value="{user.email}" label="' . __( 'Email', 'bbforms' ) . '" placeholder="' . __( 'Enter your email here', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . '[/row]' . "\n\n"

                . '[radio name="radio_' . $n3 . '" value="" label="' . __( 'How would you rate your experience?', 'bbforms' ) . '"]' . "\n"
                . __( 'Excellent', 'bbforms' ) . "\n"
                . 'Very good' . "\n"
                . __( 'Good', 'bbforms' ) . "\n"
                . __( 'Bad', 'bbforms' ) . "\n"
                . __( 'Very bad', 'bbforms' ) . "\n"
                . '[/radio]' . "\n\n"

                . '[radio name="radio_' . $n4 . '" value="" label="' . __( 'How would you rate the information provided?', 'bbforms' ) . '"]' . "\n"
                . __( 'Very satisfied', 'bbforms' ) . "\n"
                . __( 'Satisfied', 'bbforms' ) . "\n"
                . __( 'Unsatisfied', 'bbforms' ) . "\n"
                . __( 'Very unsatisfied', 'bbforms' ) . "\n"
                . '[/radio]' . "\n\n"

                . '[b]' . __( 'Please, rate the following aspects', 'bbforms' ) . '[/b]' . "\n\n"

                . '[table align="center"]' . "\n"
                . "\t" . '[tr]' . "\n"
                . "\t\t" . '[td][/td]' . "\n"
                . "\t\t" . '[td]5[/td]' . "\n"
                . "\t\t" . '[td]4[/td]' . "\n"
                . "\t\t" . '[td]3[/td]' . "\n"
                . "\t\t" . '[td]2[/td]' . "\n"
                . "\t\t" . '[td]1[/td]' . "\n"
                . "\t" . '[/tr]' . "\n"
                . "\t" . '[tr]' . "\n"
                . "\t\t" . '[td][left]' . __( 'Speed', 'bbforms' ) . '[/left][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n5 . '" value="5"][/radio][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n5 . '" value="4"][/radio][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n5 . '" value="3"][/radio][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n5 . '" value="2"][/radio][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n5 . '" value="1"][/radio][/td]' . "\n"
                . "\t" . '[/tr]' . "\n"
                . "\t" . '[tr]' . "\n"
                . "\t\t" . '[td][left]' . __( 'Attention', 'bbforms' ) . '[/left][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n6 . '" value="5"][/radio][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n6 . '" value="4"][/radio][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n6 . '" value="3"][/radio][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n6 . '" value="2"][/radio][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n6 . '" value="1"][/radio][/td]' . "\n"
                . "\t" . '[/tr]' . "\n"
                . "\t" . '[tr]' . "\n"
                . "\t\t" . '[td][left]' . __( 'Result', 'bbforms' ) . '[/left][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n7 . '" value="5"][/radio][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n7 . '" value="4"][/radio][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n7 . '" value="3"][/radio][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n7 . '" value="2"][/radio][/td]' . "\n"
                . "\t\t" . '[td][radio name="radio_' . $n7 . '" value="1"][/radio][/td]' . "\n"
                . "\t" . '[/tr]' . "\n"
                . '[/table]' . "\n\n"

                . '[textarea name="textarea_' . $n8 . '" value="" label="' . __( 'Any additional feedback?', 'bbforms' ) . '" rows="5"]' . "\n\n"

                . '[submit value="' . __( 'Submit', 'bbforms' ) . '"]',
            'actions' => '[record]' . "\n"
                . '[email to="{site.admin_email}" reply_to="{field.email_' . $n2 . '}" subject="{site.name} - ' . __( 'New submission', 'bbforms' ) . '"]{fields_table}[/email]' . "\n"
                . '[message type="success"]{settings.submit_success_message}[/message]',
            'options' => bbforms_get_options_code(),
        ),
        'questionnaire' => array(
            'title' => esc_html__( 'Questionnaire', 'bbforms' ),
            'desc' => esc_html__( 'A sample questionnaire form to let you collect information about your users.', 'bbforms' ),
            'form' => '<h3>' . __( 'About you', 'bbforms' ) . '</h3>' . "\n\n"

                . '[row]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[text name="first_name_' . $n1 . '" value="{user.first_name}" label="' . __( 'First Name', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . "\t" . '[column]' . "\n"
                . "\t\t" . '[text name="last_name_' . $n2 . '" value="{user.last_name}" label="' . __( 'Last Name', 'bbforms' ) . '"]' . "\n"
                . "\t" . '[/column]' . "\n"
                . '[/row]' . "\n\n"

                . '[row]' . "\n"
                . '[column]' . "\n"
                . "\t\t" . '[text name="gender_' . $n3 . '" value="" label="' . __( 'Gender Identification', 'bbforms' ) . '"]' . "\n"
                . '[/column]' . "\n"
                . '[column]' . "\n"
                .  "\t\t" . '[range name="range_' . $n4 . '" value="" min="18" max="120" label="' . __( 'Age', 'bbforms' ) . '"]' . "\n"
                . '[/column]' . "\n"
                . '[/row]' . "\n\n"

                . '[email* name="email_' . $n5 . '" value="{user.email}" label="' . __( 'Email', 'bbforms' ) . '"]' . "\n\n"

                . '<h3>' . __( 'Questionnaire', 'bbforms' ) . '</h3>' . "\n\n"

                // translators: %d: Number
                . '[select* name="select_' . $n6 . '" value="" label="' . sprintf( __( 'Question %d', 'bbforms' ), 1 ) . ':"]' . "\n"
                // translators: %d: Number
                . sprintf( __( 'Option %d', 'bbforms' ), 1 ) . "\n"
                // translators: %d: Number
                . sprintf( __( 'Option %d', 'bbforms' ), 2 ) . "\n"
                . '[/select]' . "\n\n"

                // translators: %d: Number
                . '[check* name="check_' . $n7 . '" value="" inline="no" label="' . sprintf( __( 'Question %d', 'bbforms' ), 2 ) . ':"]' . "\n"
                // translators: %d: Number
                . sprintf( __( 'Option %d', 'bbforms' ), 1 ) . "\n"
                // translators: %d: Number
                . sprintf( __( 'Option %d', 'bbforms' ), 2 ) . "\n"
                . '[/check]' . "\n\n"

                // translators: %d: Number
                . '[radio* name="radio_' . $n8 . '" value="" label="' . sprintf( __( 'Question %d', 'bbforms' ), 4 ) . ':"]' . "\n"
                // translators: %d: Number
                . sprintf( __( 'Option %d', 'bbforms' ), 1 ) . "\n"
                // translators: %d: Number
                . sprintf( __( 'Option %d', 'bbforms' ), 2 ) . "\n"
                . '[/radio]' . "\n\n"

                // translators: %d: Number
                . '[text* name="text_' . $n9 . '" value="" label="' . sprintf( __( 'Question %d', 'bbforms' ), 5 ) . ':"]' . "\n\n"

                // translators: %d: Number
                . '[textarea* name="textarea_' . $n10 . '" value="" label="' . sprintf( __( 'Question %d', 'bbforms' ), 6 ) . ':" rows="5"]' . "\n\n"

                . '[quiz* name="quiz_' . $n1 . '" label="' . __( 'Are you human?', 'bbforms' ) . '"]' . "\n"
                . '5 + 2 =|7' . "\n"
                . '5 - 2 =|3' . "\n"
                . '[/quiz]' . "\n\n"

                . '[submit value="' . __( 'Submit', 'bbforms' ) . '"]',
            'actions' => '[record]' . "\n"
                . '[email to="{site.admin_email}" reply_to="{field.email_' . $n5 . '}" subject="{site.name} - ' . __( 'New ticket', 'bbforms' ) . ': {field.select_' . $n3 . '}"]{fields_table}[/email]' . "\n"
                . '[message type="success"]{settings.submit_success_message}[/message]',
            'options' => bbforms_get_options_code(),
        ),

        'export_request' => array(
            'title' => esc_html__( 'Export Data Request', 'bbforms' ),
            'desc' => esc_html__( 'A simple personal data export form to help you comply with GDPR and other privacy regulations.', 'bbforms' )
            . '<br>' . esc_html__( 'Includes an action to register the request in the WordPress "Export Personal Data" tool.', 'bbforms' ),
            'form' => '[email* name="email_' . $n1 . '" value="{user.email}" label="' . __( 'Email', 'bbforms' ) . '" placeholder="' . __( 'Enter your email here', 'bbforms' ) . '"]' . "\n\n"
                . '[submit value="' . __( 'Submit', 'bbforms' ) . '"]',
            'actions' => '[record]' . "\n"
                . '[export_request email="{field.email_' . $n1 . '}" duplicated_message="' . __( 'A request for this email address already exists.', 'bbforms' ) . '"]' . __( 'Request registered successfully!', 'bbforms' ) . '[/export_request]',
            'options' => bbforms_get_options_code(array(
                'unique_field' => 'email_' . $n1
            ) ),
        ),
        'delete_request' => array(
            'title' => esc_html__( 'Delete Data Request', 'bbforms' ),
            'desc' => esc_html__( 'A simple personal data deletion form to help you comply with GDPR and other privacy regulations.', 'bbforms' )
            . '<br>' . esc_html__( 'Includes an action to register the request in the WordPress "Delete Personal Data" tool.', 'bbforms' ),
            'form' => '[email* name="email_' . $n1 . '" value="{user.email}" label="' . __( 'Email', 'bbforms' ) . '" placeholder="' . __( 'Enter your email here', 'bbforms' ) . '"]' . "\n\n"
                . '[submit value="' . __( 'Submit', 'bbforms' ) . '"]',
            'actions' => '[record]' . "\n"
                . '[delete_request email="{field.email_' . $n1 . '}" duplicated_message="' . __( 'A request for this email address already exists.', 'bbforms' ) . '"]' . __( 'Request registered successfully!', 'bbforms' ) . '[/delete_request]',
            'options' => bbforms_get_options_code( array(
                'unique_field' => 'email_' . $n1
            ) ),
        ),
    );

    return apply_filters( 'bbforms_form_templates', $form_templates );

}

/**
 * Render the form templates dialog content
 *
 * @since 1.0.0
 */
function bbforms_get_form_templates_dialog_content() {

    ob_start();
    $templates = bbforms_get_form_templates();
    
    $add_url = admin_url( 'admin.php?page=add_bbforms_forms' );
    ?>

    <div class="bbforms-form-templates">

    <?php foreach( $templates as $key => $template ) :
        $template_url = add_query_arg( array( 'template' => $key ), $add_url )?>

        <div class="bbforms-form-template bbforms-form-template-<?php echo esc_attr( $key ); ?>">
            <a href="<?php echo esc_attr( $template_url ); ?>" class="bbforms-form-template-content">
                <span class="bbforms-form-template-title"><?php echo esc_html( $template['title'] ); ?></span>
                <span class="bbforms-form-template-desc"><?php echo $template['desc']; ?></span>
            </a>
        </div>

    <?php endforeach;?>

    </div>

    <?php $output = ob_get_clean();

    return $output;

}