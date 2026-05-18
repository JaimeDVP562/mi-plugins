<?php
/**
 * Settings – Admin page registered under GamiPress > Recommended Achievements.
 *
 * Options stored:
 *   gp_ra_max_achievements  int  1-6  Number of recommendations to show.
 *   gp_ra_same_type         bool      Whether to restrict to the same achievement type.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// -------------------------------------------------------------------------
// Register the settings page under the GamiPress menu
// -------------------------------------------------------------------------
add_action( 'admin_menu', 'gp_ra_register_settings_page' );
function gp_ra_register_settings_page() {
    add_submenu_page(
        'gamipress',                          // Parent slug (GamiPress menu)
        __( 'Recommended Achievements', 'gp-recommended-achievements' ),
        __( 'Recommended Achievements', 'gp-recommended-achievements' ),
        'manage_options',
        'gp-recommended-achievements',
        'gp_ra_settings_page_html'
    );
}

// -------------------------------------------------------------------------
// Register settings with the Settings API
// -------------------------------------------------------------------------
add_action( 'admin_init', 'gp_ra_register_settings' );
function gp_ra_register_settings() {
    register_setting(
        'gp_ra_settings_group',
        'gp_ra_max_achievements',
        array(
            'type'              => 'integer',
            'sanitize_callback' => 'gp_ra_sanitize_max',
            'default'           => 3,
        )
    );

    register_setting(
        'gp_ra_settings_group',
        'gp_ra_same_type',
        array(
            'type'              => 'boolean',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        )
    );

    add_settings_section(
        'gp_ra_main_section',
        __( 'General Settings', 'gp-recommended-achievements' ),
        '__return_false',
        'gp-recommended-achievements'
    );

    add_settings_field(
        'gp_ra_max_achievements',
        __( 'Max achievements to show', 'gp-recommended-achievements' ),
        'gp_ra_field_max_achievements',
        'gp-recommended-achievements',
        'gp_ra_main_section'
    );

    add_settings_field(
        'gp_ra_same_type',
        __( 'Show same type only', 'gp-recommended-achievements' ),
        'gp_ra_field_same_type',
        'gp-recommended-achievements',
        'gp_ra_main_section'
    );
}

// -------------------------------------------------------------------------
// Sanitize max value (clamp to 1-6)
// -------------------------------------------------------------------------
function gp_ra_sanitize_max( $value ) {
    $value = absint( $value );
    return max( 1, min( 6, $value ) );
}

// -------------------------------------------------------------------------
// Field callbacks
// -------------------------------------------------------------------------
function gp_ra_field_max_achievements() {
    $value = (int) get_option( 'gp_ra_max_achievements', 3 );
    ?>
    <select name="gp_ra_max_achievements" id="gp_ra_max_achievements">
        <?php for ( $i = 1; $i <= 6; $i++ ) : ?>
            <option value="<?php echo esc_attr( $i ); ?>" <?php selected( $value, $i ); ?>>
                <?php echo esc_html( $i ); ?>
            </option>
        <?php endfor; ?>
    </select>
    <p class="description">
        <?php esc_html_e( 'Number of recommended achievements to display (max 6).', 'gp-recommended-achievements' ); ?>
    </p>
    <?php
}

function gp_ra_field_same_type() {
    $value = (bool) get_option( 'gp_ra_same_type', false );
    ?>
    <label>
        <input type="checkbox" name="gp_ra_same_type" value="1" <?php checked( $value, true ); ?> />
        <?php esc_html_e( 'Only recommend achievements of the same type as the current one.', 'gp-recommended-achievements' ); ?>
    </label>
    <?php
}

// -------------------------------------------------------------------------
// Settings page HTML
// -------------------------------------------------------------------------
function gp_ra_settings_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Recommended Achievements Settings', 'gp-recommended-achievements' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'gp_ra_settings_group' );
            do_settings_sections( 'gp-recommended-achievements' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
