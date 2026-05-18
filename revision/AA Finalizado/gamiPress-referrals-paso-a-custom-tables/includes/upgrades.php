<?php
/**
 * Upgrades
 *
 * @package GamiPress\Referrals\Upgrades
 * @since 1.2.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Check if referrals have been migrated to custom tables
 *
 * @since 1.2.0
 *
 * @return bool
 */
function gamipress_referrals_is_migrated() {
    // ARREGLO SENIOR: Unificamos con la comprobación de versión que usamos en logs.php
    $current_version = get_option( 'gamipress_referrals_version', '1.0.0' );
    return version_compare( $current_version, '1.2.0', '>=' );
}

/**
 * Check if migration is in progress
 *
 * @since 1.2.0
 *
 * @return bool
 */
function gamipress_referrals_is_migration_in_progress() {
    return (bool) get_option( 'gamipress_referrals_migration_in_progress', false );
}

/**
 * Admin notice for migration
 *
 * @since 1.2.0
 */
function gamipress_referrals_migration_admin_notice() {

    // Only show to users with the right capability
    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        return;
    }

    // Don't show if already migrated
    if( gamipress_referrals_is_migrated() ) {
        return;
    }

    // Don't show if migration is in progress
    if( gamipress_referrals_is_migration_in_progress() ) {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <strong><?php _e( 'GamiPress - Referrals:', 'gamipress-referrals' ); ?></strong>
                <?php _e( 'Migration is in progress. Please wait until it completes.', 'gamipress-referrals' ); ?>
                <span id="gamipress-referrals-migration-progress"></span>
            </p>
        </div>
        <?php
        return;
    }

    $migrate_url = wp_nonce_url(
        admin_url( 'admin.php?page=gamipress_settings&gamipress_referrals_run_migration=1' ),
        'gamipress_referrals_run_migration'
    );

    ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php _e( 'GamiPress - Referrals:', 'gamipress-referrals' ); ?></strong>
            <?php _e( 'This version introduces a new custom table for storing referrals. Referral data needs to be migrated from the logs table to the new custom table.', 'gamipress-referrals' ); ?>
        </p>
        <p>
            <?php _e( 'Until the migration is complete, referral data will continue to be read from the old logs table.', 'gamipress-referrals' ); ?>
        </p>
        <p>
            <a href="<?php echo esc_url( $migrate_url ); ?>" class="button button-primary" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to start the migration? This process may take a while depending on the number of referrals.', 'gamipress-referrals' ) ); ?>');">
                <?php _e( 'Start Migration', 'gamipress-referrals' ); ?>
            </a>
        </p>
    </div>
    <?php

}
add_action( 'admin_notices', 'gamipress_referrals_migration_admin_notice' );

/**
 * Handle migration request
 *
 * @since 1.2.0
 */
function gamipress_referrals_handle_migration() {

    // Check for the migration request
    if( ! isset( $_GET['gamipress_referrals_run_migration'] ) ) {
        return;
    }

    // Verify nonce
    if( ! wp_verify_nonce( $_GET['_wpnonce'], 'gamipress_referrals_run_migration' ) ) {
        return;
    }

    // Check capability
    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        return;
    }

    // Run the migration
    gamipress_referrals_run_migration();

    // Redirect back
    wp_redirect( admin_url( 'admin.php?page=gamipress_settings&gamipress_referrals_migration_complete=1' ) );
    exit;

}
add_action( 'admin_init', 'gamipress_referrals_handle_migration' );

/**
 * Show migration complete notice
 *
 * @since 1.2.0
 */
function gamipress_referrals_migration_complete_notice() {

    if( ! isset( $_GET['gamipress_referrals_migration_complete'] ) ) {
        return;
    }

    ?>
    <div class="notice notice-success is-dismissible">
        <p>
            <strong><?php _e( 'GamiPress - Referrals:', 'gamipress-referrals' ); ?></strong>
            <?php _e( 'Migration completed successfully! All referral data has been migrated to the new custom table.', 'gamipress-referrals' ); ?>
        </p>
    </div>
    <?php

}
add_action( 'admin_notices', 'gamipress_referrals_migration_complete_notice' );

/**
 * Run the migration process
 *
 * Migrates referral logs from the GamiPress logs table to the new custom table,
 * then deletes the old log entries ONLY if insertion was successful.
 *
 * @since 1.2.0
 */
function gamipress_referrals_run_migration() {

    global $wpdb;

    // Mark migration as in progress
    update_option( 'gamipress_referrals_migration_in_progress', true );

    // Setup CT table
    $ct_table = gamipress_referrals_get_table();

    if( ! $ct_table ) {
        update_option( 'gamipress_referrals_migration_in_progress', false );
        return;
    }

    // Get the GamiPress log table name
    $logs_table = $wpdb->prefix . 'gamipress_logs';
    $logs_meta_table = $wpdb->prefix . 'gamipress_logs_meta';

    // Referral types to migrate
    $referral_types = array( 'referral_visit', 'referral_signup', 'referral_sale', 'referral_sale_refund' );

    foreach( $referral_types as $type ) {

        // Get all logs of this type
        $logs = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$logs_table} WHERE type = %s",
            $type
        ) );

        if( empty( $logs ) ) {
            continue;
        }

        foreach( $logs as $log ) {

            $prefix = '_gamipress_';

            // Get log meta
            $referral_user_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT meta_value FROM {$logs_meta_table} WHERE log_id = %d AND meta_key = %s",
                $log->log_id, $prefix . 'referral_id'
            ) );

            $referral_ip = $wpdb->get_var( $wpdb->prepare(
                "SELECT meta_value FROM {$logs_meta_table} WHERE log_id = %d AND meta_key = %s",
                $log->log_id, $prefix . 'referral_ip'
            ) );

            $post_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT meta_value FROM {$logs_meta_table} WHERE log_id = %d AND meta_key = %s",
                $log->log_id, $prefix . 'post_id'
            ) );

            $post_url = $wpdb->get_var( $wpdb->prepare(
                "SELECT meta_value FROM {$logs_meta_table} WHERE log_id = %d AND meta_key = %s",
                $log->log_id, $prefix . 'post_url'
            ) );

            $referrer = $wpdb->get_var( $wpdb->prepare(
                "SELECT meta_value FROM {$logs_meta_table} WHERE log_id = %d AND meta_key = %s",
                $log->log_id, $prefix . 'referrer'
            ) );

            $integration = $wpdb->get_var( $wpdb->prepare(
                "SELECT meta_value FROM {$logs_meta_table} WHERE log_id = %d AND meta_key = %s",
                $log->log_id, $prefix . 'integration'
            ) );

            // Insert into new table
            $data = array(
                'user_id'           => $log->user_id,
                'referral_user_id'  => absint( $referral_user_id ),
                'referral_ip'       => $referral_ip ? $referral_ip : '',
                'type'              => $type,
                'post_id'           => absint( $post_id ),
                'post_url'          => $post_url ? $post_url : '',
                'referrer'          => $referrer ? $referrer : '',
                'integration'       => $integration ? $integration : '',
                'date'              => $log->date,
            );

            // ARREGLO SENIOR: Solo borramos el log viejo si la inserción en la CT fue exitosa
            $inserted_id = gamipress_referrals_insert_referral( $data );

            if( $inserted_id ) {
                // Delete log meta
                $wpdb->delete( $logs_meta_table, array( 'log_id' => $log->log_id ) );
                // Delete log
                $wpdb->delete( $logs_table, array( 'log_id' => $log->log_id ) );
            }

        }

    }

    // ARREGLO SENIOR: Actualizamos la "bandera" de versión para que el plugin sepa que ya hemos migrado
    update_option( 'gamipress_referrals_version', '1.2.0' );
    update_option( 'gamipress_referrals_migration_in_progress', false );

}