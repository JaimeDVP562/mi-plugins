<?php

class DAWP_Admin {

	public function init() {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_post_dawp_save_settings', [ $this, 'save_settings' ] );
		
		add_action( 'admin_init', [ $this, 'run_auto_deactivation' ] );
		add_action( 'admin_init', [ $this, 'check_requirements' ] );
	}

    public function add_menu() {
        add_menu_page(
            __( 'Automator Deactivator', 'dawp' ),
            __( 'Deactivator', 'dawp' ),
            'manage_options',
            'dawp-page',
            [ $this, 'render_page' ],
            'dashicons-clock',
            80
        );
    }

    public function render_page() {
        if ( file_exists( DAWP_PATH . 'admin/views/settings-page.php' ) ) {
            include DAWP_PATH . 'admin/views/settings-page.php';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Deactivator\'s setting page not found.', 'dawp' ) . '</p></div>';
        }
    }

    public function save_settings() {
        if ( ! isset( $_POST[ 'dawp_nonce' ] ) || ! wp_verify_nonce( $_POST[ 'dawp_nonce' ], 'dawp_verify_action' ) ) { 
            wp_die( 'Security error.' ); 
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'User\'s permissions are insufficient.' );
        }

        update_option( 'dawp_selected_automation', sanitize_text_field( $_POST[ 'automation_id' ] ) );
        update_option( 'dawp_expire_date', sanitize_text_field( $_POST[ 'expire_date' ] ) );

        wp_redirect( admin_url( 'admin.php?page=dawp-page&settings-updated=true' ) );
        exit;
    }

    public function check_requirements() {
        if ( ! class_exists( 'AutomatorWP' ) ) {
            add_action( 'admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . esc_html__( 'AutomatorWP is not active. Please install it for this integration to work.', 'dawp' ) . '</p></div>';
            } );
        }
    }

    public function run_auto_deactivation() {
		$automation_id = get_option( 'dawp_selected_automation' );
		$expire_date   = get_option( 'dawp_expire_date' );

		if ( ! $automation_id || ! $expire_date ) return;

		global $wpdb;
		$table_name = $wpdb->prefix . 'automatorwp_automations';
		
		$status = $wpdb->get_var( $wpdb->prepare( "SELECT status FROM $table_name WHERE id = %d", $automation_id ) );

		if ( ! $status || $status !== 'active' ) {
			delete_option( 'dawp_selected_automation' );
			delete_option( 'dawp_expire_date' );
			
			if ( is_admin() && isset($_GET['page']) && $_GET['page'] === 'dawp-page' ) {
				wp_safe_redirect( admin_url( 'admin.php?page=dawp-page' ) );
				exit;
			}
			return;
		}

		$now         = current_time( 'timestamp' );
		$expire_time = strtotime( $expire_date );

		if ( $now >= $expire_time ) {
			$wpdb->update( 
				$table_name, 
				[ 'status' => 'inactive' ], 
				[ 'id' => $automation_id ] 
			);

			delete_option( 'dawp_selected_automation' );
			delete_option( 'dawp_expire_date' );

			if ( is_admin() && isset($_GET['page']) && $_GET['page'] === 'dawp-page' ) {
				wp_safe_redirect( admin_url( 'admin.php?page=dawp-page&deactivated=true' ) );
				exit;
			}
		}
	}
}