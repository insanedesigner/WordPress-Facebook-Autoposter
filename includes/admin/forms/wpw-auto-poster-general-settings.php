<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * General Settings
 *
 * The html markup for the general settings box.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
global $wpw_auto_poster_options, $wpw_auto_poster_logs, $wpw_auto_poster_model;

//logs class
$logs 	= $wpw_auto_poster_logs;
$model	= $wpw_auto_poster_model;

$twice_daily_time1 = empty( $wpw_auto_poster_options['schedule_wallpost_twice_time1'] ) ? '0': $wpw_auto_poster_options['schedule_wallpost_twice_time1'];

$twice_daily_time2 = empty( $wpw_auto_poster_options['schedule_wallpost_twice_time2'] ) ? '0': $wpw_auto_poster_options['schedule_wallpost_twice_time2'];

$custom_schedule_css	= (!empty( $wpw_auto_poster_options['schedule_wallpost_option'] ) && $wpw_auto_poster_options['schedule_wallpost_option'] == 'daily') ? '' : ' wpw-auto-poster-display-none ';

$custom_twice_schedule_css	= (!empty( $wpw_auto_poster_options['schedule_wallpost_option'] ) && $wpw_auto_poster_options['schedule_wallpost_option'] == 'twicedaily') ? '' : ' wpw-auto-poster-twice-schedule-time ';

if( $custom_schedule_css == '' ) {
	$random_schedule_css	= empty($wpw_auto_poster_options['enable_random_posting']) ? '' : ' wpw-auto-poster-display-none ';
} else {
	$random_schedule_css	= $custom_schedule_css;
}

if( $custom_twice_schedule_css == '' ) {
	$random_twice_schedule_css	= empty( $wpw_auto_poster_options['enable_twice_random_posting'] ) ? '' : ' wpw-auto-poster-twice-schedule-time ';
} else {
	$random_twice_schedule_css	= $custom_twice_schedule_css;
}

$wpw_aps_limit_cls = empty($wpw_auto_poster_options['schedule_wallpost_option']) ? ' wpw-auto-poster-display-none' : '';

$wpw_aps_schedule_order = !empty($wpw_auto_poster_options['schedule_wallpost_order']) ? $wpw_auto_poster_options['schedule_wallpost_order'] : '';

$schedule_posting_order_style = (!empty( $wpw_auto_poster_options['schedule_wallpost_option'] ) ) ? '':'display:none;';

// Code to hide and unhide custom minutes box 
$custom_minute_box_style = (!empty( $wpw_auto_poster_options['schedule_wallpost_option'] ) && $wpw_auto_poster_options['schedule_wallpost_option'] == 'wpw_custom_mins' ) ? 'display:inline-block;':'display:none;';

// Get custom minutes box value 
$schedule_wallpost_custom_minute = ( !empty( $wpw_auto_poster_options['schedule_wallpost_custom_minute'] ) ) ? $wpw_auto_poster_options['schedule_wallpost_custom_minute'] : WPW_AUTO_POSTER_SCHEDULE_CUSTOM_DEFAULT_MINUTE;

// check if google tracking is enable
$google_tracking_style = ( empty( $wpw_auto_poster_options['enable_google_tracking'] ) || $wpw_auto_poster_options['enable_google_tracking'] != 1 ) ? ' display:none' : '';

// check if google analytics script use with plugin or thirdpart
$google_tracking_script = ( !empty( $wpw_auto_poster_options['google_tracking_script'] ) ) ? $wpw_auto_poster_options['google_tracking_script'] : 'yes';

$google_tracking_script_style = ( $google_tracking_script == 'no' || !empty( $google_tracking_style ) ) ? 'display:none"' : '';

$wpw_aps_posting_behaviour = !empty($wpw_auto_poster_options['schedule_wallpost_order_behaviour']) ? $wpw_auto_poster_options['schedule_wallpost_order_behaviour'] : 'DESC';

$wpw_behaviour_style = ( !empty($wpw_auto_poster_options['schedule_wallpost_order']) && $wpw_auto_poster_options['schedule_wallpost_order'] == 'rand' ) ? 'display:none' : '';

?>

<!-- beginning of the general settings meta box -->
<div id="wpw-auto-poster-general" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="general" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
								
					<!-- general settings box title -->
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'General Settings', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
										
						<table class="form-table">											
							<tbody>				
							
								<?php
									
									// do action for add setting before general settings
									do_action( 'wpw_auto_poster_before_general_setting', $wpw_auto_poster_options );
									
								?>
								<!--<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[system_report]"><?php //_e( 'System Report:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<a href="<?php //echo add_query_arg(array('wpw_auto_poster_sys_log' => '1')); ?>" id="wpw_auto_poster_options[system_report]" name="wpw_auto_poster_options[system_report]" class="button"><?php //_e('Generate System Report', 'wpwautoposter'); ?></a>
										<p><small><?php //_e( 'Please generate system report file and provide us in your ticket when contacting support.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>-->

								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[enable_posting_logs]"><?php _e( 'Enable Social Posting Logs:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input id="wpw_auto_poster_options[enable_posting_logs]" name="wpw_auto_poster_options[enable_posting_logs]" type="checkbox" value="1" <?php if ( isset( $wpw_auto_poster_options['enable_posting_logs'] ) ) { checked( '1', $wpw_auto_poster_options['enable_posting_logs'] ); } ?> />
										<p><small>
											<?php _e( 'Check this box to store your social posting activities into the database which can be viewed from "Social Posting Logs" section.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>
								<!-- Google campaign tracking option since 2.6.1 -->
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[enable_google_tracking]"><?php _e( 'Google Analytics Campaign Tracking:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input id="wpw_auto_poster_options[enable_google_tracking]" name="wpw_auto_poster_options[enable_google_tracking]" type="checkbox" value="1" <?php if ( isset( $wpw_auto_poster_options['enable_google_tracking'] ) ) { checked( '1', $wpw_auto_poster_options['enable_google_tracking'] ); } ?> />
										<p><small>
											<?php _e( 'Enable campaign tracking if you want to see how much traffic is generated by this plugin.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>
								<!-- Google campaign tracking script option since 2.6.1 -->
								<tr id="google_tracking_script_row" valign="top" style="<?php print $google_tracking_style;?>" >
									<th scope="row">
										<label for="wpw_auto_poster_options[google_tracking_script]"><?php _e( 'Use Google Analytics:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input id="wpw_auto_poster_google_tracking" name="wpw_auto_poster_options[google_tracking_script]" type="radio" value="yes" <?php if ( isset( $google_tracking_script ) ) { checked( 'yes', $google_tracking_script ); } ?> />
										<label for="wpw_auto_poster_google_tracking"><?php _e('Google Analytics from Social Auto Poster ', 'wpwautoposter'); ?></label>&nbsp;&nbsp;
										<input id="thirdparty_google_tracking" name="wpw_auto_poster_options[google_tracking_script]" type="radio" value="no" <?php if ( isset( $google_tracking_script ) ) { checked( 'no', $google_tracking_script ); } ?> />
										<label for="thirdparty_google_tracking"><?php _e('Google Analytics from thirdparty plugin', 'wpwautoposter'); ?></label>
										<p><small>
											<?php _e( 'If you are using any thirdparty plugin which is adding google analytics tracking code then select ', 'wpwautoposter' ); 
												print '<strong>'.__('thirdparty option.', 'wpwautoposter').'</strong>';
												?>
										</small></p>
									</td>
								</tr>

								<!-- Google campaign tracking option since 2.6.1 -->
								<tr id="google_tracking_code_row" valign="top" style="<?php print $google_tracking_script_style; ?>">
									<th scope="row">
										<label for="wpw_auto_poster_options[google_tracking_code]"><?php _e( 'Google Analytics Tracking code:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<textarea id="wpw_auto_poster_options[google_tracking_code]" name="wpw_auto_poster_options[google_tracking_code]"><?php if ( isset( $wpw_auto_poster_options['google_tracking_code'] ) ) {  print $model->wpw_auto_poster_escape_attr($wpw_auto_poster_options['google_tracking_code']); } ?></textarea>
										<p><small>
											<?php _e( 'Paste your Google Analytics tracking code here. This will be added into the header template of your theme.', 'wpwautoposter' ); ?>
										</small></p>
										<p><small><strong><?php _e('NOTE:', 'wpwautoposter');?></strong>
											<?php _e( 'Get Google Analytics Tracking code from ', 'wpwautoposter' ); ?>
											<a title="<?php _e('Google Analytics javascript code', 'wpwautoposter');?>" href="https://developers.google.com/analytics/devguides/collection/analyticsjs/" target="_blank"><?php _e('here.', 'wpwautoposter');?></a>
										</small></p>
									</td>
								</tr>

								<tr>
									<td colspan="2">
										<strong><?php _e('Schedule Settings', 'wpwautoposter'); ?></strong>
									</td>
								</tr>
								
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[schedule_wallpost_option]"><?php _e( 'Schedule Wall Posts:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<select name="wpw_auto_poster_options[schedule_wallpost_option]" id="wpw_auto_poster_options[schedule_wallpost_option]" class="wpw-auto-poster-schedule-option">
											<?php
												$schedule_wallpost_options = $model->wpw_auto_poster_get_all_schedules();

												foreach ( $schedule_wallpost_options as $key => $option ) {
													?>
													<option value="<?php echo $key; ?>" <?php selected( $wpw_auto_poster_options['schedule_wallpost_option'], $key ); ?>>
														<?php esc_html_e( $option ); ?>
													</option>
													<?php
												}
											?>
										</select>
										<span class="wpw-auto-poster-custom-minute-box" style="<?php print $custom_minute_box_style;?>" id="wpw-auto-poster-custom-minute-box">
											<input type="number" id="wpw_auto_poster_options[schedule_wallpost_custom_minute]" name="wpw_auto_poster_options[schedule_wallpost_custom_minute]" value="<?php print $schedule_wallpost_custom_minute;?>" min="<?php print WPW_AUTO_POSTER_SCHEDULE_CUSTOM_DEFAULT_MINUTE;?>"> 
											<small><?php _e( 'Minutes', 'wpwautoposter' ); ?></small>
										</span>
										<p><small>
											<?php _e( 'Select the Schedule wall post option if you want to auto post your content at a desired time i.e. Minutes, Hourly, Twice Daily, Daily or Weekly.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>

								<tr valign="top" class="wpw-auto-poster-custom-schedule-wrap <?php echo $custom_schedule_css ?>">
									<th scope="row">
										<label><?php _e( 'Posting Type:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input id="wpw_auto_poster_regular_posting" class="wpw-auto-poster-random-posting" name="wpw_auto_poster_options[enable_random_posting]" type="radio" value="" <?php if ( isset( $wpw_auto_poster_options['enable_random_posting'] ) ) { checked( '', $wpw_auto_poster_options['enable_random_posting'] ); } ?> /><label for="wpw_auto_poster_regular_posting" class="wpw-auto-poster-label"><?php _e('Specific Time', 'wpwautoposter'); ?></label>&nbsp;&nbsp;
                                        <input id="wpw_auto_poster_random_posting" class="wpw-auto-poster-random-posting" name="wpw_auto_poster_options[enable_random_posting]" type="radio" value="1" <?php if ( isset( $wpw_auto_poster_options['enable_random_posting'] ) ) { checked( '1', $wpw_auto_poster_options['enable_random_posting'] ); } ?> /><label for="wpw_auto_poster_random_posting" class="wpw-auto-poster-label"><?php _e('Randomly', 'wpwautoposter'); ?></label>
										<p><small>
											<?php _e( 'Select the Posting Type option. i.e. Specific time or Randomly.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>

								<tr valign="top" class="wpw-auto-poster-schedule-time wpw-auto-poster-custom-schedule-wrap <?php echo $random_schedule_css ?> daily">
									<th scope="row">
										<label for="wpw_auto_poster_options[schedule_wallpost_time]"><?php _e( 'Schedule Time:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<select name="wpw_auto_poster_options[schedule_wallpost_time]" id="wpw_auto_poster_options[schedule_wallpost_time]" class="wpw-auto-poster-hours">
											<?php		
												//Get all schedule time					
												$schedule_time_options = $model->wpw_auto_poster_get_all_schedule_time();
													
												foreach ( $schedule_time_options as $key => $value ) {
													
													?>
													<option value="<?php echo $key; ?>" <?php selected( $wpw_auto_poster_options['schedule_wallpost_time'], $key ); ?>>
														<?php esc_html_e( $value ); ?>
													</option>
													<?php
												}															
											?> 														
										</select>
										<select name="wpw_auto_poster_options[schedule_wallpost_minute]" id="wpw_auto_poster_options[schedule_wallpost_minute]" class="wpw-auto-poster-minutes">
											<?php
												//Get all schedule minutes					
												$schedule_minute_options = $model->wpw_auto_poster_get_all_schedule_minutes();
													
												foreach ( $schedule_minute_options as $key => $value ) {
													
													?>
													<option value="<?php echo $key; ?>" <?php selected( $wpw_auto_poster_options['schedule_wallpost_minute'], $key ); ?>>
														<?php esc_html_e( $value ); ?>
													</option>
													<?php
												}														
											?> 														
										</select>
										<p><small>
											<?php _e( 'Select the schedule time.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>
								
								<!-- Code for twice daily schedule posting options-->
								<tr valign="top" class="wpw-auto-poster-custom-twice-schedule-wrap <?php echo $custom_twice_schedule_css ?>">
									<th scope="row">
										<label><?php _e( 'Posting Type:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
                                        <input id="wpw_auto_poster_twice_regular_posting" class="wpw-auto-poster-twice-random-posting" name="wpw_auto_poster_options[enable_twice_random_posting]" type="radio" value="" <?php if ( isset( $wpw_auto_poster_options['enable_twice_random_posting'] ) ) { checked( '', $wpw_auto_poster_options['enable_twice_random_posting'] ); } else{ checked( '', '');} ?> /><label for="wpw_auto_poster_twice_regular_posting" class="wpw-auto-poster-label"><?php _e('Specific Time', 'wpwautoposter'); ?></label>&nbsp;&nbsp;
                                        <input id="wpw_auto_poster_twice_random_posting" class="wpw-auto-poster-twice-random-posting" name="wpw_auto_poster_options[enable_twice_random_posting]" type="radio" value="1" <?php if ( isset( $wpw_auto_poster_options['enable_twice_random_posting'] ) ) { checked( '1', $wpw_auto_poster_options['enable_twice_random_posting'] ); } ?> /><label for="wpw_auto_poster_twice_random_posting" class="wpw-auto-poster-label"><?php _e('Randomly', 'wpwautoposter'); ?></label>
										<p><small>
											<?php _e( 'Select the Posting Type option.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>
								<tr valign="top" class="wpw-auto-poster-schedule-twice-time wpw-auto-poster-custom-twice-schedule-wrap twicedaily <?php print $random_twice_schedule_css;?>">
									<th scope="row">
										<label for="wpw_auto_poster_options[schedule_wallpost_twice_time1]"><?php _e( 'Schedule Time:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<div class="twice-schedule-time-options">
											<select name="wpw_auto_poster_options[schedule_wallpost_twice_time1]" id="wpw_auto_poster_options[schedule_wallpost_twice_time1]" class="wpw-auto-poster-twice-hours">
												<?php		
													//Get all schedule time					
													$schedule_time_options = $model->wpw_auto_poster_get_all_schedule_time();
														
													foreach ( $schedule_time_options as $key => $value ) {
														
														?>
														<option value="<?php echo $key; ?>" <?php selected( $twice_daily_time1, $key ); ?>>
															<?php esc_html_e( $value ); ?>
														</option>
														<?php
													}															
												?> 														
											</select>
											<small>
												<?php _e( 'First schedule', 'wpwautoposter' ); ?>
											</small>
										</div>
										<div class="twice-schedule-time-options">
											<select name="wpw_auto_poster_options[schedule_wallpost_twice_time2]" id="wpw_auto_poster_options[schedule_wallpost_twice_time2]" class="wpw-auto-poster-twice-hours">
												<?php		
													//Get all schedule time					
													$schedule_time_options = $model->wpw_auto_poster_get_all_schedule_time();
														
													foreach ( $schedule_time_options as $key => $value ) {
														
														?>
														<option value="<?php echo $key; ?>" <?php selected( $twice_daily_time2, $key ); ?>>
															<?php esc_html_e( $value ); ?>
														</option>
														<?php
													}															
												?> 														
											</select>
											<small>
												<?php _e( 'Second schedule', 'wpwautoposter' ); ?>
											</small>	
										</div>
									</td>
								</tr>

								<!-- Schedule posting order -->
								<tr id="wpw-auto-poster-schedule-order-row" valign="top" style="<?php print $schedule_posting_order_style;?>">
									<th scope="row">
										<label for="wpw_auto_poster_options[schedule_wallpost_order]"><?php _e( 'Posting order:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<select name="wpw_auto_poster_options[schedule_wallpost_order]" id="wpw_auto_poster_options[schedule_wallpost_order]" class="wpw-auto-poster-schedule-order">
											<?php   												
												$schedule_posting_orders = $model->wpw_auto_poster_get_all_posting_orders();
																
												foreach ( $schedule_posting_orders as $key => $option ) {											
													?>
													<option value="<?php echo $key; ?>" <?php selected( $wpw_aps_schedule_order, $key ); ?>>
														<?php esc_html_e( $option ); ?>
													</option>
													<?php
												}														
											?>
										</select>
										
										<select name="wpw_auto_poster_options[schedule_wallpost_order_behaviour]" id="wpw_auto_poster_options[schedule_wallpost_order_behaviour]" class="wpw-auto-poster-schedule-order" style="<?php print $wpw_behaviour_style;?>">
											<?php   												
												$schedule_posting_behaviour = array( 'ASC' => 'Ascending', 'DESC' => 'Descending' );
																
												foreach ( $schedule_posting_behaviour as $key => $option ) {											
													?>
													<option value="<?php echo $key; ?>" <?php selected( $wpw_aps_posting_behaviour, $key ); ?>>
														<?php esc_html_e( $option ); ?>
													</option>
													<?php
												}														
											?>
										</select>
										<p><small>
											<?php _e( 'Select posting order and all scheduled post will be posted on <strong>posting order</strong>.', 'wpwautoposter' ); ?>
										</small></p>
										<p><small>
											<?php _e( '<strong>Default</strong> - All scheduled post will be posted by post ID in descending order.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>

								<tr valign="top" class="wpw-auto-poster-schedule-limit <?php echo $wpw_aps_limit_cls; ?>">
									<th scope="row">
										<label for="wpw_auto_poster_options[daily_posts_limit]"><?php _e( 'Maximum Posting per schedule:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input id="wpw_auto_poster_options[daily_posts_limit]" name="wpw_auto_poster_options[daily_posts_limit]" type="text" value="<?php echo $wpw_auto_poster_options['daily_posts_limit']; ?>" />
										<p><small>
											<?php _e( 'Enter the maximum number of auto posting that you want to allow per hour, day, week etc. based on selected schedule wall posts option. Leave it blank for unlimited posting.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>
								
								<tr>
									<td colspan="2">
										<strong><?php _e('Misc Settings', 'wpwautoposter'); ?></strong>
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[autopost_thirdparty_plugins]"><?php _e( 'Allow autopost from thirdparty plugins:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input id="wpw_auto_poster_options[autopost_thirdparty_plugins]" name="wpw_auto_poster_options[autopost_thirdparty_plugins]" type="checkbox" value="1" <?php if ( isset( $wpw_auto_poster_options['autopost_thirdparty_plugins'] ) ) { checked( '1', $wpw_auto_poster_options['autopost_thirdparty_plugins'] ); } ?> />
										<p><small>
											<?php _e( 'Check this box if you want to allow autoposting from any thirdparty plugins which allows to submit data from frontend.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[delete_options]"><?php _e( 'Delete Options:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input id="wpw_auto_poster_options[delete_options]" name="wpw_auto_poster_options[delete_options]" type="checkbox" value="1" <?php if ( isset( $wpw_auto_poster_options['delete_options'] ) ) { checked( '1', $wpw_auto_poster_options['delete_options'] ); } ?> />
										<p><small><?php _e( 'Check this box if you don\'t want to use Social Auto Poster plugin on your website anymore. This will make sure that all the settings and tables are being deleted from the database when you deactivate the plugin.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[enable_logs]"><?php _e( 'Debug Log:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
											<p><small>
											<?php _e( 'You can check posting system log at the file location ', 'wpwautoposter' );
													echo ' <code>'.WPW_AUTO_POSTER_LOG_DIR.$logs->wpw_auto_poster_file_name( 'logs' ).'</code>';
											?>
										</small></p>
									</td>
								</tr>

								<?php

									// do action for add setting after general settings
									do_action( 'wpw_auto_poster_after_general_setting', $wpw_auto_poster_options );
								?>

								<?php
									echo apply_filters (
														 'wpweb_fb_settings_submit_button',
														 '<tr valign="top">
																<td colspan="2">
																	<input type="submit" value="' . __( 'Save Changes', 'wpwautoposter' ) . '" id="wpw_auto_poster_set_submit" name="wpw_auto_poster_set_submit" class="button-primary">
																</td>
															</tr>'
														);
								?>
							</tbody>
						</table>
										
					</div><!-- .inside -->
									
			</div><!-- #general -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-general -->
<!-- end of the general settings meta box -->