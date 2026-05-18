<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
	<h1>Speed Optimizer Integration</h1>
	<?php if ( isset( $_GET[ 'soi_msg' ] ) ) : ?>
		<div class="notice notice-<?php echo esc_attr( $_GET[ 'soi_status' ] ); ?> is-dismissible">
			<p><?php echo esc_html( urldecode( $_GET[ 'soi_msg' ] ) ); ?></p>
		</div>
	<?php endif; ?>
	<div class="card">
		<h2>Quick actions</h2>
		<form action="" method="post">
			<?php wp_nonce_field( 'soi_nonce_action', 'soi_nonce' ); ?>
			<button type="submit" name="soi_action" value="clear_all" class="button button-primary">Clear all cache</button>
			<hr />
			<p><strong>Clear URL cache:</strong></p>
			<input type="url" name="cache_url" id="" placeholder="https://example.com/page" class="regular-text" />
			<button type="submit" name="soi_action" value="clear_url" class="button">Clear URL</button>
		</form>
	</div>
</div>