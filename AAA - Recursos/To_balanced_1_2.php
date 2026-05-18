<?php

/**
*Plugin Name: 1.2.0 To (balanced) Email Panel
*Description: To (balanced)
*Version: 1.2.0
*Author: AMG
*/

/*
 * Adds an additional panel to the Contact Form 7 contact form edit screen with an input that will save to the post meta of the contact form.
 *
 */

/*
 * Sets up the fields inside our new custom panel
 * @param WPCF7_ContactForm $post - modified post type object from Contact Form 7 containing information about the current contact form
 */
function wpcf7_custom_fields ($post) {
    ?>
    <h2><?php echo esc_html( __( 'Additinal email', 'contact-form-7' ) ); ?></h2>
    <fieldset>
        <label for="additional-email">To (balanced)</label>
        <input type="text" id="additional-email" name="additional-email" size="200" value="<?php
        //$post here is not a traditional WP Post object, but a WPCF7_ContactForm object, $post->id is private, so we need to use the id() function to get the post ID

        echo get_post_meta($post->id(), 'additional_email', true) ?>"
        />
    </fieldset>
    <?php
}

/*
 * Adds our new Custom Fields panel to the Contact Form 7 edit screen
 *
 * @param array $panels - an array of all the panels currently displayed on the Contact Form 7 edit screen
 */

function add_cf7_panel ($panels) {
    $panels['additional_email'] = array(
        'title' => 'To (balanced)',
        'callback' => 'wpcf7_custom_fields',
    );

    return $panels;
}

add_filter('wpcf7_editor_panels', 'add_cf7_panel');

/*
 * Hooks into the save_post method and adds our new post meta to the contact form if the POST request contains the custom field we set up earlier
 * @param $post_id - post ID of the current post being saved
 */

function save_wpcf7_custom_fields($post_id) {
    if (array_key_exists('additional-email', $_POST)) {
        update_post_meta(
            $post_id,
            'additional_email',
            $_POST['additional-email']
        );
    }
    //error_log( print_r($_POST, 1) );
}

add_action('save_post', 'save_wpcf7_custom_fields');

/*
 * Hooks into the wpcf7_before_send_mail method and send an email to one of the emails of the field
 */

function send_additional_mail($contact_form) {

    $id_form = $contact_form->id();

    $submission = WPCF7_Submission::get_instance();

    if ($submission) {

        $balanced_index = (int) get_post_meta($id_form, 'cf7_balancer_last_index', true);

        if($balanced_index === '') {

            $balanced_index = 0;
        }

        $posted_data = $submission->get_posted_data();

        $additional_email = sanitize_text_field(get_post_meta($id_form, 'additional_email', true));

        $array_emails = array_filter(explode(', ', $additional_email), 'is_email');

        $cont = 0;
        foreach ($array_emails as $value) {
            
            $array_emails_ordered[$cont] = $value;
            $cont++;
        }

        error_log(print_r($array_emails, 1));
        error_log(print_r($array_emails_ordered, 1));

        if($array_emails) {

            $subject = wpcf7_mail_replace_tags($posted_data['your-subject']);
            $message = wpcf7_mail_replace_tags($posted_data['your-message']);

            $total_emails = count($array_emails_ordered);
    
            if ($balanced_index > ($total_emails-1)) {
    
                $balanced_index = 0;
            }

            wp_mail($array_emails_ordered[$balanced_index], $subject, $message);

            update_post_meta($id_form, 'cf7_balancer_last_index', $balanced_index + 1);
        } 
    }
}

add_action( 'wpcf7_before_send_mail', 'send_additional_mail' );