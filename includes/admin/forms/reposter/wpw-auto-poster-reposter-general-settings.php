<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * General Settings
 *
 * The html markup for the general settings box.
 *
 * @package Social Auto Poster
 * @since 2.6.9
 */
global $wpw_auto_poster_reposter_options, $wpw_auto_poster_logs, $wpw_auto_poster_model;

//logs class
$logs 	= $wpw_auto_poster_logs;
$model	= $wpw_auto_poster_model;

$wpw_aps_schedule_order = !empty($wpw_auto_poster_reposter_options['schedule_posting_order']) ? $wpw_auto_poster_reposter_options['schedule_posting_order'] : '';
$wpw_aps_posting_behaviour = !empty($wpw_auto_poster_reposter_options['schedule_posting_order_behaviour']) ? $wpw_auto_poster_reposter_options['schedule_posting_order_behaviour'] : '';

$wpw_behaviour_style = ( !empty($wpw_auto_poster_reposter_options['schedule_posting_order']) && $wpw_auto_poster_reposter_options['schedule_posting_order'] == 'rand' ) ? 'display:none' : '';
$reposter_repeat = ( empty( $wpw_auto_poster_reposter_options['schedule_wallpost_repeat'] ) ) ? 'no' : $wpw_auto_poster_reposter_options['schedule_wallpost_repeat'];

$repeat_times_css = ( $reposter_repeat == 'no' ) ? 'display:none' : '';
$repeat_option_css = ( $reposter_repeat == 'yes' ) ? 'width:59%': '';

$reposter_repeat_times = ( empty( $wpw_auto_poster_reposter_options['reposter_repeat_times'] ) ) ? '' : $wpw_auto_poster_reposter_options['reposter_repeat_times'];

?>

<!-- beginning of the general settings meta box -->
<div id="wpw-auto-poster-general" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="general" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
								
					<!-- general settings box title -->
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'Schedule Settings', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
										
						<table class="form-table">											
							<tbody>				
							
								<?php
									
									// do action for add setting before general settings
									do_action( 'wpw_auto_poster_reposter_before_general_setting', $wpw_auto_poster_reposter_options );
									
								?>								
								
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_reposter_options[unique_posting]"><?php _e( 'Unique posting:', 'wpwautoposter' ); ?></label>
									</th>
									<td colspan="2">
									<input name="wpw_auto_poster_reposter_options[unique_posting]" id="wpw_auto_poster_reposter_options[unique_posting]" type="checkbox" value="1" <?php if( isset( $wpw_auto_poster_reposter_options['unique_posting'] ) ) { checked( '1', $wpw_auto_poster_reposter_options['unique_posting'] ); } ?> />
									<p><small>
											<?php print __( 'Check this box to publish only posts that were never posted by ', 'wpwautoposter' ).'<strong>'.__('Social Auto Poster.', 'wpwautoposter').'</strong>'.' '.__('This is useful if you have existing posts and want them to be posted to your social media accounts only once.', 'wpwautoposter'); ?>
										</small></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_reposter_options[schedule_wallpost_option]"><?php _e( 'When to post (Schedule):', 'wpwautoposter' ); ?></label>
									</th>
									<td colspan="2">
										<div class="reposter-scheduler-time">
											<span>
												<input type="number" name="wpw_auto_poster_reposter_options[schedule_wallpost_option][days]" value="<?php print $wpw_auto_poster_reposter_options['schedule_wallpost_option']['days'];?>" min="0"> <?php _e( 'Days', 'wpwautoposter');?>
											</span>
											<span>
												<input type="number" name="wpw_auto_poster_reposter_options[schedule_wallpost_option][hours]" value="<?php print $wpw_auto_poster_reposter_options['schedule_wallpost_option']['hours'];?>" min="0"> <?php _e( 'Hours', 'wpwautoposter');?>
											</span>
											<span>
												<input type="number" name="wpw_auto_poster_reposter_options[schedule_wallpost_option][minutes]" value="<?php print $wpw_auto_poster_reposter_options['schedule_wallpost_option']['minutes'];?>" min="0"> <?php _e( 'Minutes', 'wpwautoposter');?>
											</span>
										</div>
										<p><small>
											<?php _e( 'Set the posting schedule interval for reposter.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>
								<?php do_action('wpw_auto_poster_after_reposter_schedule_field');?>
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_reposter_options[schedule_wallpost_repeat]"><?php _e( 'When Finished:', 'wpwautoposter' ); ?></label>
									</th>
									<td style="<?php print $repeat_option_css;?>">
										<div class="reposter-repeat-option">
											<span>
												<input id="reposter_no_repeat" type="radio" name="wpw_auto_poster_reposter_options[schedule_wallpost_repeat]" value="no" <?php checked( 'no', $reposter_repeat); ?>>
												<label for="reposter_no_repeat"><?php _e( 'Wait for new posts', 'wpwautoposter' ); ?></label>
											</span>
											<span>
												<input id="reposter_repeat" type="radio" name="wpw_auto_poster_reposter_options[schedule_wallpost_repeat]" value="yes" <?php checked( 'yes', $reposter_repeat); ?>>
												<label for="reposter_repeat"><?php _e( 'Loop it. Reset and Start from the beginning', 'wpwautoposter' ); ?></label>
											</span>
										</div>
										<p><small>
											<?php print sprintf( __( 'Set the action when all post published with reposter. If you select the %1$sLoop it%2$s then at time of repeat posting Reposter will %1$spublish the post which published by Social Auto Poster.%2$s', 'wpwautoposter' ) , '<strong>', '</strong>') ; ?>
										</small></p>
									</td>
									<td class="repeat-times" style="<?php print $repeat_times_css;?>">
										<label><?php _e( 'Repeat', 'wpwautoposter' ); ?></label>
										<input id="reposter_repeat_times" type="number" name="wpw_auto_poster_reposter_options[reposter_repeat_times]" value="<?php print $reposter_repeat_times;?>" min="1">
										<label for="reposter_repeat_times"><?php _e( 'Times', 'wpwautoposter' ); ?></label>
										<p>
											<small>
											<?php _e( 'Leave it empty for unlimited looping.', 'wpwautoposter' ); ?>
										</small>
										</p>
									</td>
								</tr>
								
								<!-- Schedule posting order -->
								<tr id="wpw-auto-poster-schedule-order-row" valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_reposter_options[schedule_wallpost_order]"><?php _e( 'Posting order:', 'wpwautoposter' ); ?></label>
									</th>
									<td colspan="2">
										<select name="wpw_auto_poster_reposter_options[schedule_posting_order]" id="wpw_auto_poster_reposter_options[schedule_posting_order]" class="wpw-auto-poster-schedule-order">
											<?php   												
												$schedule_posting_orders = $model->wpw_auto_poster_get_reposter_posting_orders();

												foreach ( $schedule_posting_orders as $key => $option ) {
													?>
													<option value="<?php echo $key; ?>" <?php selected( $wpw_aps_schedule_order, $key ); ?>>
														<?php esc_html_e( $option ); ?>
													</option>
													<?php
												}														
											?>
										</select>
										<select name="wpw_auto_poster_reposter_options[schedule_posting_order_behaviour]" id="wpw_auto_poster_reposter_options[schedule_posting_order_behaviour]" class="wpw-auto-poster-schedule-order" style="<?php print $wpw_behaviour_style;?>">
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
											<?php _e( 'Select posting order and all scheduled post will be posted on selected <strong>posting order</strong>.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>
								<?php
									
									// do action for add setting after general settings
									do_action( 'wpw_auto_poster_reposter_after_general_setting', $wpw_auto_poster_reposter_options );
									
								?>
								
								<?php
									echo apply_filters ( 
														 'wpweb_reposter_general_settings_submit_button', 
														 '<tr valign="top">
																<td colspan="2">
																	<input type="submit" value="' . __( 'Save Changes', 'wpwautoposter' ) . '" id="wpw_auto_poster_reposter_set_submit" name="wpw_auto_poster_reposter_set_submit" class="button-primary">
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