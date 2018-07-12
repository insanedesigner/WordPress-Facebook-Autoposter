<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Social Posted Logs List
 *
 * The html markup for the system logs
 * 
 * @package Social Auto Poster
 * @since 2.7.9
 */
global $wpw_auto_poster_logs;

$log_file = WPW_AUTO_POSTER_LOG_DIR.$wpw_auto_poster_logs->wpw_auto_poster_file_name( 'logs' );

if( isset( $_POST['wpw_auto_poster_log_action'] ) && $_POST['wpw_auto_poster_log_action'] == 'clear-log' ){
	$wpw_auto_poster_logs->wpw_auto_poster_clear('logs');
}
?>
<div class="wrap">
<h2><?php _e( 'Posting Debug Logs', 'wpwautoposter' ); ?> <small><?php _e( '(Debug Logs will be cleared automatically every week.)', 'wpwautoposter' ); ?></small></h2>
<form method="post" action="">
	<input type="hidden" name="wpw_auto_poster_log_action" value="clear-log">
	<input type="submit" class="button-primary" name="wpw_auto_poster_log_submit" value="<?php _e( 'Clear log', 'wpwautoposter' ); ?>">
</form>
<div class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
								
					<!-- general settings box title -->
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'Debug Logs', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
						<div id="wpw-log-viewer" class="wpw-log-viewer">
							<?php if( file_exists( $log_file ) ){
								if( is_readable( $log_file ) ) { // if the file is readable
								?>
								<code>
									<?php echo esc_html( file_get_contents( trim(WPW_AUTO_POSTER_LOG_DIR.$wpw_auto_poster_logs->wpw_auto_poster_file_name( 'logs' ) ) ) ); ?>
								</code>
							<?php 
								} else{ // if file is not readable
									?>
									<div class="wpw-auto-poster-error"><p><?php _e( 'Log file does not have read permission. Please assign read permission for the file ', 'wpwautoposter' ); ?><code><?php print $log_file;?></code></p></div>	
							<?php }
							}
							else{ ?>
								<p><?php _e( 'Log file not found.', 'wpwautoposter' ); ?></p>
							<?php }?>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>				
</div>