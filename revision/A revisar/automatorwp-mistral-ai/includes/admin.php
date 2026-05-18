<?php
/**
 * Admin (Settings section like Anthropic)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function automatorwp_mistral_ai_settings_sections( $sections ) {

    $sections['mistral_ai'] = array(
        'title' => __( 'Mistral AI', 'automatorwp-mistral-ai' ),
        'icon'  => 'dashicons-admin-generic',
    );

    return $sections;
}
add_filter( 'automatorwp_settings_sections', 'automatorwp_mistral_ai_settings_sections' );

function automatorwp_mistral_ai_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'automatorwp_mistral_ai_';

    $meta_boxes['automatorwp-mistral-ai-settings'] = array(
        'title'  => __( 'Mistral AI', 'automatorwp-mistral-ai' ),
        'fields' => array(
            $prefix . 'token' => array(
                'name' => __( 'API token:', 'automatorwp-mistral-ai' ),
                'desc' => __( 'Create it in Mistral Console and paste it here.', 'automatorwp-mistral-ai' ),
                'type' => 'text',
            ),
            $prefix . 'model' => array(
                'name'    => __( 'Text model:', 'automatorwp-mistral-ai' ),
                'desc'    => __( 'Used for text generation.', 'automatorwp-mistral-ai' ),
                'type'    => 'text',
                'default' => 'mistral-large-latest',
            ),
            $prefix . 'image_model' => array(
                'name'    => __( 'Image agent model:', 'automatorwp-mistral-ai' ),
                'desc'    => __( 'Model used when creating the Image Generation agent (Agents API).', 'automatorwp-mistral-ai' ),
                'type'    => 'text',
                'default' => 'mistral-medium-2505',
            ),
            $prefix . 'image_agent_id' => array(
                'name' => __( 'Image agent id (auto):', 'automatorwp-mistral-ai' ),
                'desc' => __( 'This will be auto-created and stored the first time you run “Generate image”.', 'automatorwp-mistral-ai' ),
                'type' => 'text',
            ),
            $prefix . 'authorize' => array(
                'type'          => 'text',
                'render_row_cb' => 'automatorwp_mistral_ai_authorize_display_cb',
            ),
        ),
    );

    return $meta_boxes;
}
add_filter( 'automatorwp_settings_mistral_ai_meta_boxes', 'automatorwp_mistral_ai_settings_meta_boxes' );

function automatorwp_mistral_ai_authorize_display_cb( $field_args, $field ) {

    $prefix = 'automatorwp_mistral_ai_';
    $token  = automatorwp_mistral_ai_get_token();
    ?>
    <div class="cmb-row cmb-type-custom cmb2-id-automatorwp-mistral-ai-authorize table-layout" data-fieldtype="custom">
        <div class="cmb-th">
            <label><?php echo esc_html__( 'Connect with Mistral AI:', 'automatorwp-mistral-ai' ); ?></label>
        </div>
        <div class="cmb-td">
            <a id="<?php echo esc_attr( $prefix . 'authorize' ); ?>" class="button button-primary">
                <?php echo esc_html__( 'Authorize', 'automatorwp-mistral-ai' ); ?>
            </a>

            <p class="cmb2-metabox-description">
                <?php echo esc_html__( 'Click on "Authorize" to verify that your API token is valid.', 'automatorwp-mistral-ai' ); ?>
            </p>

            <?php if ( ! empty( $token ) ) : ?>
                <div class="automatorwp-notice-success" style="margin-top: 10px;">
                    <?php echo esc_html__( 'A token is currently saved. Click "Authorize" to verify it.', 'automatorwp-mistral-ai' ); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
