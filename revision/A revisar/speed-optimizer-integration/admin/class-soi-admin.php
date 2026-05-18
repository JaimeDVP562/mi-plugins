<?php

class SOI_Admin {

	public function init() {

		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_init', [ $this, 'handle_form_submission' ] );
		add_action( 'admin_init', [ $this, 'check_requirements' ] );

	}

	public function check_requirements() {
		
		if ( ! class_exists( '\SiteGround_Optimizer\Supercacher\Supercacher' ) ) {

			add_action( 'admin_notices', function() {

				echo '<div class="notice notice-error"><p>' . esc_html__( 'Speed Optimizer (SiteGround) is not active. Please install it for this integration to work.', 'soi' ) . '</p></div>';

			} );

		}

	}

	public function add_menu() {

		add_menu_page(

			__( 'Speed Optimizer Integration', 'soi' ),
			__( 'Speed Optimizer', 'soi' ),
			'manage_options',
			'soi-page',
			[ $this, 'render_page' ],
			'dashicons-performance',
			80

		);

	}

	public function handle_form_submission() {

		if ( ! isset( $_POST[ 'soi_action' ] ) || ! current_user_can( 'manage_options' ) ) return;

		check_admin_referer( 'soi_nonce_action', 'soi_nonce' );

		$action = sanitize_text_field( $_POST[ 'soi_action' ] );
		$success = false;

		try {

			$sg = new \SiteGround_Optimizer\Supercacher\Supercacher();

			if ( 'clear_all' === $action ) { $success = $sg->purge_cache(); }
			elseif ( 'clear_url' === $action ) {

				$url = esc_url_raw( $_POST[ 'cache_url' ] );
				$success = $sg->purge_cache( $url );

			}

			$msg = $success ? 'Cache successfully processed.' : 'There was a problem in the purge.';
			$type = $success ? 'success' : 'error';

		} catch ( \Exception $e ) {

			$msg = $e->getMessage();
			$type = 'error';

		}

		wp_safe_redirect( add_query_arg( [

			'page' => 'soi-page',
			'soi_status' => $type,
			'soi_msg' => urlencode( $msg )

		], admin_url( 'admin.php' ) ) );
		exit;

	}

	public function render_page() {
	
		include SOI_PATH . 'admin/views/settings-page.php';

	}

}

?>