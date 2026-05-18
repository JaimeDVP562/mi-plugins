<?php
/**
 * Admin
 *
 * @package     AutomatorWP\Integrations\MailMint\Admin
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the Mail Mint settings section in AutomatorWP settings
 *
 * @since 1.0.0
 *
 * @param array $sections
 *
 * @return array
 */
function automatorwp_mailmint_settings_sections( $sections )
{
    $sections['mailmint'] = array(
        'title' => __( 'Mail Mint', 'automatorwp-mailmint' ),
        'icon'  => '',
    );

    return $sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_mailmint_settings_sections' );

/**
 * Register the Mail Mint settings meta box
 *
 * @since 1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function automatorwp_mailmint_settings_meta_boxes( $meta_boxes )
{
    $meta_boxes['automatorwp-mailmint-settings'] = array(
        'title'  => automatorwp_dashicon( 'email-alt' ) . __( 'Mail Mint', 'automatorwp-mailmint' ),
        'fields' => apply_filters( 'automatorwp_mailmint_settings_fields', array(
            'automatorwp_mailmint_status' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_mailmint_status_display_cb',
            ),
        ) ),
    );

    return $meta_boxes;
}
add_filter( 'automatorwp_settings_mailmint_meta_boxes', 'automatorwp_mailmint_settings_meta_boxes' );

/**
 * Display the Mail Mint connection status row in settings
 *
 * @since 1.0.0
 *
 * @param array      $field_args
 * @param CMB2_Field $field
 */
function automatorwp_mailmint_status_display_cb( $field_args, $field )
{
    $active  = defined( 'MRM_VERSION' );
    $version = $active ? MRM_VERSION : '';
    $label   = $active
        ? sprintf( __( 'Mail Mint %s is installed and active.', 'automatorwp-mailmint' ), $version )
        : __( 'Mail Mint is not installed or not active.', 'automatorwp-mailmint' );
    $color   = $active ? '#46b450' : '#dc3232';

    ?>
    <div class="cmb-row cmb-type-custom table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php esc_html_e( 'Status:', 'automatorwp-mailmint' ); ?></label>
        </div>
        <div class="cmb-td">
            <span style="color: <?php echo esc_attr( $color ); ?>; font-weight: bold;">
                <?php echo esc_html( $label ); ?>
            </span>
            <?php if ( ! $active ) : ?>
                <p class="cmb2-metabox-description">
                    <?php echo sprintf(
                        __( 'Install <a href="%s" target="_blank">Mail Mint</a> to use this integration.', 'automatorwp-mailmint' ),
                        'https://wordpress.org/plugins/mail-mint/'
                    ); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
