<?php
/**
 * Forms
 *
 * @package     BBForms\Forms
 * @author      BBForms <contact@bbforms.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Render form
 *
 * @since 1.0.0
 *
 * @param int|stdClass $form Form ID or object
 */
function bbforms_render_form( $form ) {

    global $bbforms_form;

    $id = $form;
    $form = new BBForms_Form( $form );

    if( ! $form->exists() ) {

        if( current_user_can( bbforms_get_manager_capability() ) ) : ?>
            <div class="bbforms-error bbforms-shortcode-error">
                <?php // translators: %s: Form ID provided
                printf( esc_html__( 'The form #%s does not exists.', 'bbforms' ), esc_html( $id ) ); ?>
                <small><?php echo esc_html__( 'Message visible only to site administrators', 'bbforms' ); ?></small>
            </div>
        <?php endif;

        return false;
    }

    $bbforms_form = $form;

    if( ! bbforms_can_render_form( $form ) ) {
        return false;
    }

    bbforms_do_form( $form );

}

/**
 * Do form
 *
 * @since 1.0.0
 *
 * @param BBForms_Form $form Form object
 */
function bbforms_do_form( $form ) {

    global $bbforms_response, $bbforms_request;

    $hide_form = '';

    // Handle submit (if field was submitted without JS)
    if( isset( $_REQUEST['bbforms_response'] )
        && is_array( $_REQUEST['bbforms_response'] )
        && isset( $_REQUEST['bbforms_response']['form_id'] )
        && absint( $_REQUEST['bbforms_response']['form_id'] ) === absint( $form->id ) ) {
        // Setup the response
        $bbforms_response = $_REQUEST['bbforms_response'];

        if( isset( $_REQUEST['bbforms_request'] )
            && is_array( $_REQUEST['bbforms_request'] ) ) {
            // Setup the request
            $bbforms_request = urldecode_deep( $_REQUEST['bbforms_request'] );
        }

        // Hide on success
        if( isset( $bbforms_response['success'] ) && $bbforms_response['success'] ) {
            if( $form->options['hide_form_on_success'] ) {
                $hide_form = 'style="display:none"';
            }
        }
    }

    // Do form fields
    $content = bbforms_do_form_fields( $form );

    $uri = bbforms_get_request_uri();
    $lang = get_bloginfo( 'language' );
    $dir = ( function_exists( 'is_rtl' ) && is_rtl() ) ? 'rtl' : 'ltr';
    $form_attrs = array(
        'action' => $uri,
        'method' => 'post',
        'id' => 'bbforms-form-' . $form->id,
        'class' => 'bbforms-form',
        'aria-label' => $form->title,
        'novalidate' => 'novalidate',
    );

    if( $form->has_files ) {
        $form_attrs['enctype'] = 'multipart/form-data';
    }

    $form_attrs = apply_filters( 'bbforms_form_attrs', $form_attrs, $form );
    ?>
    <div class="bbforms <?php echo esc_attr( bbforms_get_theme_class( $form ) ); ?>" lang="<?php echo esc_attr( $lang ); ?>" dir="<?php echo esc_attr( $dir ); ?>">
        <form <?php echo bbforms_concat_attrs( $form_attrs ); ?> <?php echo $hide_form; ?>>
            <?php bbforms_before_render_form( $form ); ?>
            <?php echo $content; ?>
            <?php bbforms_after_render_form( $form ); ?>
        </form>
        <?php bbforms_render_form_messages( $form ); ?>
    </div>
    <?php

    // Restore response & requests
    $bbforms_response = null;
    $bbforms_request = null;

}

/**
 * @param BBForms_Form $form
 * @return string
 */
function bbforms_get_theme_class( $form ) {
    $class = 'bbforms-theme-' . $form->options['theme'];

    if( $form->options['theme'] === 'dark' ) {
        $class .= ' bbforms-theme-default';
    }

    return apply_filters( 'bbforms_get_theme_class', $class, $form );
}

/**
 * Before render form
 *
 * @since 1.0.0
 *
 * @param BBForms_Form $form Form object
 */
function bbforms_before_render_form( $form ) {
    ?>

    <?php if( $form->options['show_form_title'] ) :
        $form_title_tag = strtolower( $form->options['form_title_tag'] );

        if( ! in_array( $form_title_tag,
            array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' ) ) ) {
            $form_title_tag = 'h3';
        }
    ?>
        <<?php echo $form_title_tag ?> class="bbforms-form-title"><?php echo esc_html( $form->title ); ?></<?php echo $form_title_tag ?>>
    <?php endif; ?>
    <?php if( $form->has_required_fields && $form->options['show_required_fields_notice'] ) : ?>
        <p><?php echo bbforms_get_form_message( 'required_fields_message' ); ?></p>
    <?php endif; ?>

    <?php

    bbforms_render_form_hidden_fields();

    do_action( 'bbforms_before_render_form' );

}

/**
 * Get hidden fields
 *
 * @global BBForms_Form $bbforms_form
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_hidden_fields() {
    global $bbforms_form;

    // Do not pass hidden fields to prevent others to override them
    $hidden_fields = (array) apply_filters( 'bbforms_form_hidden_fields', array() );

    $hidden_fields = array_merge( array(
        '_bbforms' => $bbforms_form->id,
        '_bbforms_version' => BBFORMS_VER,
        '_bbforms_nonce' => bbforms_get_nonce(),
        '_bbforms_post' => 0,
        '_bbforms_user' => $bbforms_form->user_id,
    ), $hidden_fields );

    if ( in_the_loop() ) {
        $hidden_fields['_bbforms_post'] = (int) get_the_ID();
    }

    return $hidden_fields;
}

/**
 * Render hidden fields
 *
 * @since 1.0.0
 */
function bbforms_render_form_hidden_fields() {

    $hidden_fields = bbforms_get_hidden_fields();

    $output = '';

    foreach ( $hidden_fields as $name => $value ) {
        $output .= sprintf(
                '<input type="hidden" name="%1$s" value="%2$s" />',
                esc_attr( $name ),
                esc_attr( $value )
            ) . "\n";
    }

    echo '<div style="display: none;">' . "\n" . $output . '</div>' . "\n";
}

/**
 * After render form
 *
 * @since 1.0.0
 *
 * @param BBForms_Form $form Form object
 */
function bbforms_after_render_form( $form ) {

    global $bbforms_response;

    do_action( 'bbforms_after_render_form' );

}

function bbforms_render_form_messages( $form ) {

    global $bbforms_response;

    if( is_array( $bbforms_response )
        && isset( $bbforms_response['messages'] )
        && is_array( $bbforms_response['messages'] ) ) {
        foreach( $bbforms_response['messages'] as $message ) {
            echo '<div class="bbforms-message bbforms-' . esc_attr( $message['type'] ) . '-message">' . wp_kses_post( $message['text'] ) . '</div>';
        }
    }

    do_action( 'bbforms_render_form_messages' );

}

/**
 * Checks if can render a form based in its configuration
 *
 * @since 1.0.0
 *
 * @param BBForms_Form $form Form object
 */
function bbforms_can_render_form( $form ) {

    // Check if form requires login
    if( $form->options['require_login'] && $form->user_id === 0 ) {
        echo '<div class="bbforms ' . esc_attr( bbforms_get_theme_class( $form ) ) . '">';
        echo bbforms_get_message_html( $form->options['require_login_message'], 'warning' );
        echo '</div>';
        return false;
    }

    // Check if submissions exceeded
    if( bbforms_form_reached_submissions_limit( $form->id, $form->options['submissions_limit'] ) ) {
        echo '<div class="bbforms ' . esc_attr( bbforms_get_theme_class( $form ) ) . '">';
        echo bbforms_get_message_html( $form->options['submissions_limit_message'], 'warning' );
        echo '</div>';
        return false;
    }

    return apply_filters( 'bbforms_can_render_form', true, $form );

}

/**
 * Check if form reached submission limit
 *
 * @since 1.0.0
 *
 * @param int $form_id  The Form ID
 * @param int $limit    Limit to check
 *
 * @return bool
 */
function bbforms_form_reached_submissions_limit( $form_id, $limit = 0 ) {

    $limit = absint( $limit );

    // Bail if submission not limited
    if( $limit === 0 ) {
        return false;
    }

    $count = bbforms_get_submissions_count( $form_id );

    if( $count < $limit ) {
        return false;
    }

    return true;

}

/**
 * Check if form reached submission limit
 *
 * @since 1.0.0
 *
 * @param int       $form_id        The Form ID
 * @param string    $unique_field   Unique field to check
 * @param mixed     $value          Field value
 *
 * @return bool
 */
function bbforms_check_unique_field_value( $form_id, $unique_field = '', $value = '' ) {

    // Bail if no field provided
    if( empty( $unique_field ) ) {
        return false;
    }

    $count = bbforms_get_submissions_by_field( $form_id, $unique_field, $value );

    // Bail if not found
    if( $count === 0 ) {
        return false;
    }

    return true;

}

/**
 * Get forms from database
 *
 * @since 1.0.0
 *
 * @return array
 */
function bbforms_get_forms() {

    global $wpdb;

    $ct_table = ct_setup_table( 'bbforms_forms' );

    $forms = $wpdb->get_results( "SELECT * FROM {$ct_table->db->table_name} ORDER BY id DESC" );

    ct_reset_setup_table();

    return $forms;

}

/**
 * Get form from database
 *
 * @since 1.0.0
 *
 * @param int|stdClass $form Form ID or object
 *
 * @return stdClass|false
 */
function bbforms_get_form( $form ) {

    ct_setup_table( 'bbforms_forms' );

    $object = ct_get_object( $form );

    ct_reset_setup_table();

    return $object;

}

/**
 * Helper function to get a form title
 *
 * @since 1.0.0
 *
 * @param int $form_id
 *
 * @return string
 */
function bbforms_get_form_title( $form_id ) {

    global $wpdb;

    $form_id = absint( $form_id );

    if( $form_id === 0 ) {
        return __( '(no title)', 'bbforms' );
    }

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_forms' );

    // Query search
    $form_title = $wpdb->get_var( $wpdb->prepare(
        "SELECT f.title
        FROM {$ct_table->db->table_name} AS f
        WHERE f.id = %d
        LIMIT 1",
        $form_id,
    ) );

    ct_reset_setup_table();

    if( empty( $form_title ) ) {
        return __( '(no title)', 'bbforms' );
    } else {
        return $form_title;
    }

}

/**
 * Helper function to get a form meta
 *
 * @since 1.0.0
 *
 * @param int       $form_id
 * @param string    $meta_key
 * @param bool      $single
 *
 * @return string
 */
function bbforms_get_form_meta( $form_id, $meta_key, $single = false ) {

    global $wpdb, $ct_table;

    $form_id = absint( $form_id );

    if( $form_id === 0 ) {
        return '';
    }

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_forms' );

    // Get the meta value
    $meta_value = ct_get_object_meta( $form_id, $meta_key, $single );

    ct_reset_setup_table();

    return $meta_value;

}

/**
 * Helper function to update a form meta
 *
 * @since 1.0.0
 *
 * @param int       $form_id
 * @param string    $meta_key
 * @param mixed     $meta_value
 *
 * @return int|bool
 */
function bbforms_update_form_meta( $form_id, $meta_key, $meta_value ) {

    global $wpdb, $ct_table;

    $form_id = absint( $form_id );

    if( $form_id === 0 ) {
        return false;
    }

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_forms' );

    // Get the meta value
    $result = ct_update_object_meta( $form_id, $meta_key, $meta_value );

    ct_reset_setup_table();

    return $result;

}

/**
 * Helper function to get a form edit form
 *
 * @since 1.0.0
 *
 * @param int $form_id
 *
 * @return string
 */
function bbforms_get_form_edit_link( $form_id ) {

    $form_id = absint( $form_id );

    if( $form_id === 0 ) {
        return __( '(no title)', 'bbforms' );
    }

    $form_title = bbforms_get_form_title( $form_id );
    /* translators: %s: Link title. */
    $a_title = sprintf( __( 'Edit %s', 'bbforms' ), $form_title );
    $form_edit_url = ct_get_edit_link( 'bbforms_forms', $form_id );

    /* translators: %1$s: Link URL. %2$s: Attribute Link title. %3$s: Link title. */
    return sprintf( __( '<a href="%1$s" title="%2$s">%3$s</a>', 'bbforms' ), $form_edit_url, $a_title, $form_title );

}

/**
 * Helper function to get a form category
 *
 * @since 1.0.0
 *
 * @param int $category_id
 *
 * @return string
 */
function bbforms_get_category_name( $category_id ) {

    global $wpdb;

    $category_id = absint( $category_id );

    if( $category_id === 0 ) {
        return __( '(no name)', 'bbforms' );
    }

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_categories' );

    // Query search
    $category_title = $wpdb->get_var( $wpdb->prepare(
        "SELECT c.name
        FROM {$ct_table->db->table_name} AS c
        WHERE c.id = %d
        LIMIT 1",
        $category_id,
    ) );

    ct_reset_setup_table();

    if( empty( $category_title ) ) {
        return __( '(no title)', 'bbforms' );
    } else {
        return $category_title;
    }

}

/**
 * Helper function to get a form category link
 *
 * @since 1.0.0
 *
 * @param int $category_id
 *
 * @return string
 */
function bbforms_get_category_edit_link( $category_id ) {

    $category_id = absint( $category_id );

    if( $category_id === 0 ) {
        return __( '(no name)', 'bbforms' );
    }

    $name = bbforms_get_category_name( $category_id );
    /* translators: %s: Link title. */
    $a_title = sprintf( __( 'Edit %s', 'bbforms' ), $name );
    $edit_url = ct_get_edit_link( 'bbforms_categories', $category_id );

    /* translators: %1$s: Link URL. %2$s: Attribute Link title. %3$s: Link title. */
    return sprintf( __( '<a href="%1$s" title="%2$s">%3$s</a>', 'bbforms' ), $edit_url, $a_title, $name );

}

/**
 * Helper function to get a form tag
 *
 * @since 1.0.0
 *
 * @param int $tag_id
 *
 * @return string
 */
function bbforms_get_tag_name( $tag_id ) {

    global $wpdb;

    $tag_id = absint( $tag_id );

    if( $tag_id === 0 ) {
        return __( '(no name)', 'bbforms' );
    }

    // Setup table
    $ct_table = ct_setup_table( 'bbforms_tags' );

    // Query search
    $tag_title = $wpdb->get_var( $wpdb->prepare(
        "SELECT t.name
        FROM {$ct_table->db->table_name} AS t
        WHERE t.id = %d
        LIMIT 1",
        $tag_id,
    ) );

    ct_reset_setup_table();

    if( empty( $tag_title ) ) {
        return __( '(no title)', 'bbforms' );
    } else {
        return $tag_title;
    }

}

/**
 * Helper function to get a form tag link
 *
 * @since 1.0.0
 *
 * @param int $tag_id
 *
 * @return string
 */
function bbforms_get_tag_edit_link( $tag_id ) {

    $tag_id = absint( $tag_id );

    if( $tag_id === 0 ) {
        return __( '(no name)', 'bbforms' );
    }

    $name = bbforms_get_tag_name( $tag_id );
    /* translators: %s: Link title. */
    $a_title = sprintf( __( 'Edit %s', 'bbforms' ), $name );
    $edit_url = ct_get_edit_link( 'bbforms_tags', $tag_id );

    /* translators: %1$s: Link URL. %2$s: Attribute Link title. %3$s: Link title. */
    return sprintf( __( '<a href="%1$s" title="%2$s">%3$s</a>', 'bbforms' ), $edit_url, $a_title, $name );

}