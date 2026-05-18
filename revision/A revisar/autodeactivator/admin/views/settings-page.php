<?php

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'automatorwp_automations';
$automations = $wpdb->get_results( "SELECT id, title FROM {$table_name} WHERE status = 'active'" );
$saved_automation	= get_option( 'dawp_selected_automation' );
$saved_date			= get_option( 'dawp_expire_date' );

if ( $saved_date ) {

	$now = current_time( 'timestamp' );
	$expire_time = strtotime( $saved_date );
	
	if ( $now >= $expire_time ) {
		
		$saved_automation = '';
		$saved_date = '';
		
	}
	
}

if ( $saved_automation ) {
	
	$active_ids = wp_list_pluck( $automations, 'id' );
	if ( ! in_array( $saved_automation, $active_ids ) ) {
		
		$saved_automation = '';
		$saved_date = '';
		
	}
	
}

?>
<div class="wrap">
	<h1><?php _e( 'Set autodeactivation', 'dawp' ) ?></h1>
	
	<?php if ( isset( $_GET['deactivated'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Autodeactivation configured correctly.', 'dawp' ); ?></p>
		</div>
	<?php endif; ?>
	
	<form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
		<input type="hidden" name="action" value="dawp_save_settings" />
		<?php wp_nonce_field( 'dawp_verify_action', 'dawp_nonce' ); ?>
		
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="automation_id"><?php _e( 'Select Automatization', 'dawp' ); ?></label>
				</th>
				<td>
					<select name="automation_id" id="automation_id" required>
						<option value=""><?php _e( '-- Select one --', 'dawp' ) ?></option>
						<?php foreach ( $automations as $automation ) : ?>
							<option value="<?php echo $automation->id; ?>" <?php selected( $saved_automation, $automation->id ); ?>>
								<?php echo esc_html( $automation->title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="expire_date"><?php _e( 'Date and Time of deactivation', 'dawp' ) ?></label>
				</th>
				<td>
					<input type="datetime-local" name="expire_date" id="expire_date" value="<?php echo esc_attr( $saved_date ) ?>" required />
				</td>
			</tr>
		</table>
	<?php submit_button( __( 'Save settings', 'dawp' ) ); ?>
	</form>
</div>
