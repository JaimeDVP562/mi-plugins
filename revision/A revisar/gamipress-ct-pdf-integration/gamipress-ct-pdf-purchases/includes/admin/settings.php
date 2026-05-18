<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add settings submenu under GamiPress
 */
function gamipress_ct_pdf_purchases_admin_menu() {
    add_submenu_page(
        'gamipress',
        __( 'CT PDF Purchases', 'gamipress-ct-pdf-purchases' ),
        __( 'CT PDF Purchases', 'gamipress-ct-pdf-purchases' ),
        'manage_options',
        'gamipress-ct-pdf-purchases',
        'gamipress_ct_pdf_purchases_settings_page'
    );
}
add_action( 'admin_menu', 'gamipress_ct_pdf_purchases_admin_menu', 20 );

/**
 * Register settings
 */
function gamipress_ct_pdf_purchases_register_settings() {
    register_setting(
        'gamipress_ct_pdf_purchases_settings_group',
        'gamipress_ct_pdf_purchases_settings'
    );
}
add_action( 'admin_init', 'gamipress_ct_pdf_purchases_register_settings' );

/**
 * Get PDF templates
 */
function gamipress_ct_pdf_purchases_get_pdf_templates() {
    $templates = get_posts( array(
        'post_type'      => 'ct_pdf_template',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    return $templates;
}

/**
 * Render settings page
 */
function gamipress_ct_pdf_purchases_settings_page() {
    $options         = get_option( 'gamipress_ct_pdf_purchases_settings', array() );
    $template_id     = isset( $options['template_id'] ) ? absint( $options['template_id'] ) : 0;
    $company_name    = isset( $options['company_name'] ) ? $options['company_name'] : '';
    $company_email   = isset( $options['company_email'] ) ? $options['company_email'] : '';
    $company_phone   = isset( $options['company_phone'] ) ? $options['company_phone'] : '';
    $company_address = isset( $options['company_address'] ) ? $options['company_address'] : '';

    $templates = gamipress_ct_pdf_purchases_get_pdf_templates();
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__( 'CT PDF Purchases', 'gamipress-ct-pdf-purchases' ); ?></h1>

        <form method="post" action="options.php">
            <?php settings_fields( 'gamipress_ct_pdf_purchases_settings_group' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php echo esc_html__( 'Global PDF Template', 'gamipress-ct-pdf-purchases' ); ?></th>
                    <td>
                        <select name="gamipress_ct_pdf_purchases_settings[template_id]">
                            <option value="0"><?php echo esc_html__( 'Select a template', 'gamipress-ct-pdf-purchases' ); ?></option>
                            <?php foreach ( $templates as $template ) : ?>
                                <option value="<?php echo esc_attr( $template->ID ); ?>" <?php selected( $template_id, $template->ID ); ?>>
                                    <?php echo esc_html( $template->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php echo esc_html__( 'This add-on uses a single global template for all receipts.', 'gamipress-ct-pdf-purchases' ); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php echo esc_html__( 'Company Name', 'gamipress-ct-pdf-purchases' ); ?></th>
                    <td>
                        <input type="text" name="gamipress_ct_pdf_purchases_settings[company_name]" value="<?php echo esc_attr( $company_name ); ?>" class="regular-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php echo esc_html__( 'Company Email', 'gamipress-ct-pdf-purchases' ); ?></th>
                    <td>
                        <input type="email" name="gamipress_ct_pdf_purchases_settings[company_email]" value="<?php echo esc_attr( $company_email ); ?>" class="regular-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php echo esc_html__( 'Company Phone', 'gamipress-ct-pdf-purchases' ); ?></th>
                    <td>
                        <input type="text" name="gamipress_ct_pdf_purchases_settings[company_phone]" value="<?php echo esc_attr( $company_phone ); ?>" class="regular-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php echo esc_html__( 'Company Address', 'gamipress-ct-pdf-purchases' ); ?></th>
                    <td>
                        <textarea name="gamipress_ct_pdf_purchases_settings[company_address]" rows="4" cols="50"><?php echo esc_textarea( $company_address ); ?></textarea>
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Save Changes', 'gamipress-ct-pdf-purchases' ) ); ?>
        </form>
    </div>
    <?php
}