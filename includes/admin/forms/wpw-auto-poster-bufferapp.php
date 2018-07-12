<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * BufferApp Settings
 *
 * The html markup for the BufferApp settings tab.
 *
 * @package Social Auto Poster
 * @since 1.3.0
 */

global $wpw_auto_poster_options, $wpw_auto_poster_model, $wpw_auto_poster_ba_posting;

//model class
$model = $wpw_auto_poster_model;

//BufferApp posting class
$baposting = $wpw_auto_poster_ba_posting;

$ba_wp_pretty_url = ( !empty( $wpw_auto_poster_options['ba_wp_pretty_url'] ) ) ? $wpw_auto_poster_options['ba_wp_pretty_url'] : '';

$ba_wp_pretty_url = !empty( $ba_wp_pretty_url ) ? ' checked="checked"' : '';
$ba_wp_pretty_url_css = ( $wpw_auto_poster_options['ba_url_shortener'] == 'wordpress' ) ? ' display:table-row': ' display:none';

// get url shortner service list array 
$ba_url_shortener = $model->wpw_auto_poster_get_shortner_list();
$ba_exclude_cats = array();

$wpw_auto_poster_options['ba_global_message_template'] = !empty( $wpw_auto_poster_options['ba_global_message_template'] ) ? $wpw_auto_poster_options['ba_global_message_template']: '';
?>

<!-- beginning of the bufferapp general settings meta box -->
<div id="wpw-auto-poster-bufferapp-general" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="bufferapp_general" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
								
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'BufferApp General Settings', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
										
						<table class="form-table">											
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[enable_bufferapp]"><?php _e( 'Enable Autoposting to BufferApp:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input name="wpw_auto_poster_options[enable_bufferapp]" id="wpw_auto_poster_options[enable_bufferapp]" type="checkbox" value="1" <?php if( isset( $wpw_auto_poster_options['enable_bufferapp'] ) ) { checked( '1', $wpw_auto_poster_options['enable_bufferapp'] ); } ?> />
										<p><small><?php _e( 'Check this box, if you want to autopost your content to BufferApp.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>	

								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[enable_bufferapp_for]"><?php _e( 'Enable BufferApp Autoposting for:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<ul>
										<?php 
											$all_types = get_post_types( array( 'public' => true ), 'objects');
											$all_types = is_array( $all_types ) ? $all_types : array();
											
											if( !empty( $wpw_auto_poster_options['enable_bufferapp_for'] ) ) {
												$prevent_meta = $wpw_auto_poster_options['enable_bufferapp_for'];
											} else {
												$prevent_meta = '';
											}
															
											$prevent_meta = is_array( $prevent_meta ) ? $prevent_meta : array();

											if( !empty( $wpw_auto_poster_options['ba_post_type_tags'] ) ) {
												$ba_post_type_tags = $wpw_auto_poster_options['ba_post_type_tags'];
											} else {
												$ba_post_type_tags = array();
											}

											$static_post_type_arr = wpw_auto_poster_get_static_tag_taxonomy();

											if( !empty( $wpw_auto_poster_options['ba_post_type_cats'] ) ) {
												$ba_post_type_cats = $wpw_auto_poster_options['ba_post_type_cats'];
											} else {
												$ba_post_type_cats = array();
											}

											// Get saved categories for buffer to exclude from posting
											if( !empty( $wpw_auto_poster_options['ba_exclude_cats'] ) ) {
												$ba_exclude_cats = $wpw_auto_poster_options['ba_exclude_cats'];
											}
														
											foreach ( $all_types as $type ) {	
															
												if ( !is_object( $type ) ) continue;															
													$label = @$type->labels->name ? $type->labels->name : $type->name;
													if( $label == 'Media' || $label == 'media' ) continue; // skip media
													$selected = ( in_array( $type->name, $prevent_meta ) ) ? 'checked="checked"' : '';
										?>
															
											<li class="wpw-auto-poster-prevent-types">
												<input type="checkbox" id="wpw_auto_posting_ba_prevent_<?php echo $type->name; ?>" name="wpw_auto_poster_options[enable_bufferapp_for][]" value="<?php echo $type->name; ?>" <?php echo $selected; ?>/>
																						
												<label for="wpw_auto_posting_ba_prevent_<?php echo $type->name; ?>"><?php echo $label; ?></label>
											</li>
											
											<?php	} ?>
										</ul>
										<p><small><?php _e( 'Check each of the post types you want to automatically post to BufferApp when they get published.', 'wpwautoposter' ); ?></small></p>  
									</td>
								</tr> 
								
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[ba_post_type_tags][]"><?php _e( 'Select Tags:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[ba_post_type_tags][]" id="wpw_auto_poster_options[ba_post_type_tags]" class="ba_post_type_tags wpw-auto-poster-cats-tags-select" multiple="multiple">
											<?php foreach( $all_types as $type ) {	
												
												if( !is_object( $type ) ) continue;	

													if(in_array( $type->name, $prevent_meta )) {

														$label = @$type->labels->name ? $type->labels->name : $type->name;
														if( $label == 'Media' || $label == 'media' ) continue; // skip media
														$all_taxonomies = get_object_taxonomies( $type->name, 'objects' );
	                							
	                									echo '<optgroup label="'.$label.'">';
										                // Loop on all taxonomies
										                foreach ($all_taxonomies as $taxonomy){

										                	$selected = '';
										                	if( !empty( $static_post_type_arr[$type->name] ) && $static_post_type_arr[$type->name] != $taxonomy->name){
                             										continue;
                    										}
										                	if(isset($ba_post_type_tags[$type->name]) && !empty($ba_post_type_tags[$type->name])){
										                		$selected = ( in_array( $taxonomy->name, $ba_post_type_tags[$type->name] ) ) ? 'selected="selected"' : '';
										                	}
										                    if (is_object($taxonomy) && $taxonomy->hierarchical != 1) {

										                        echo '<option value="' . $type->name."|".$taxonomy->name . '" '.$selected.'>'.$taxonomy->label.'</option>';
										                    }
										                }
										                echo '</optgroup>';
										            }
											}?>
										</select>
										<div class="wpw-ajax-loader"><img src="<?php echo WPW_AUTO_POSTER_IMG_URL."/icons/ajax-loader.gif";?>"/></div>
										<p><small><?php _e( 'Select the Tags for each post type that you want to post as ', 'wpwautoposter' ); ?><b><?php _e('hashtags.', 'wpwautoposter' );?></b></small></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[ba_post_type_cats][]"><?php _e( 'Select Categories:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[ba_post_type_cats][]" id="wpw_auto_poster_options[ba_post_type_cats]" class="ba_post_type_cats wpw-auto-poster-cats-tags-select" multiple="multiple">
											<?php foreach( $all_types as $type ) {	
												
												if( !is_object( $type ) ) continue;	

													if(in_array( $type->name, $prevent_meta )) {														
														$label = @$type->labels->name ? $type->labels->name : $type->name;
														if( $label == 'Media' || $label == 'media' ) continue; // skip media
														$all_taxonomies = get_object_taxonomies( $type->name, 'objects' );
	                							
	                									echo '<optgroup label="'.$label.'">';
										                // Loop on all taxonomies
										                foreach ($all_taxonomies as $taxonomy){

										                	$selected = '';
										                	if(isset($ba_post_type_cats[$type->name]) && !empty($ba_post_type_cats[$type->name])) {
										                		$selected = ( in_array( $taxonomy->name, $ba_post_type_cats[$type->name]) ) ? 'selected="selected"' : '';
										                	}
										                    if (is_object($taxonomy) && $taxonomy->hierarchical == 1) {

										                        echo '<option value="' . $type->name."|".$taxonomy->name . '" '.$selected.'>'.$taxonomy->label.'</option>';
										                    }
										                }
										                echo '</optgroup>';
										            }
											}?>
										</select>
										<div class="wpw-ajax-loader"><img src="<?php echo WPW_AUTO_POSTER_IMG_URL."/icons/ajax-loader.gif";?>"/></div>
										<p><small><?php _e( 'Select the Categories for each post type that you want to post as ', 'wpwautoposter' ); ?><b><?php _e('hashtags.', 'wpwautoposter' );?></b></small></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[ba_exclude_cats][]"><?php _e( 'Exclude Specific Categories:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[ba_exclude_cats][]" id="wpw_auto_poster_options[ba_exclude_cats]" class="ba_exclude_cats wpw-auto-poster-cats-exclude-select" multiple="multiple">
											
											<?php

												$post_type_categories = wpw_auto_poster_get_all_categories();

												if(!empty($post_type_categories)) {
													
													foreach($post_type_categories as $post_type => $post_data){

														echo '<optgroup label="'.$post_data['label'].'">';

														if(isset($post_data['categories']) && !empty($post_data['categories']) && is_array($post_data['categories'])){
															
															foreach($post_data['categories'] as $cat_slug => $cat_name){

																$selected = '';
																if( !empty($ba_exclude_cats[$post_type] ) ) {
											                		$selected = ( in_array( $cat_slug, $ba_exclude_cats[$post_type] ) ) ? 'selected="selected"' : '';
											                	}
																echo '<option value="' . $post_type ."|".$cat_slug . '" '.$selected.'>'.$cat_name.'</option>';
															}

														}
														echo '</optgroup>';
													}
												}

											?>

										</select>
										<p><small><?php _e( 'Select the categories for each post type that you want to exclude for posting.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>


								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[ba_url_shortener]"><?php _e( 'URL Shortener:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[ba_url_shortener]" id="wpw_auto_poster_options[ba_url_shortener]" class="ba_url_shortener" data-content='ba'>
											<?php
																
												foreach ( $ba_url_shortener as $key => $option ) {											
													?>
													<option value="<?php echo $model->wpw_auto_poster_escape_attr( $key ); ?>" <?php selected( $wpw_auto_poster_options['ba_url_shortener'], $key ); ?>>
														<?php esc_html_e( $option ); ?>
													</option>
													<?php
												}															
											?> 														
										</select>
										<p><small><?php _e( 'Long URLs will automatically be shortened using the specified URL shortener.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>
								
								<tr id="row-ba-wp-pretty-url" valign="top" style="<?php print $ba_wp_pretty_url_css;?>">
	                                <th scope="row">
	                                    <label for="wpw_auto_poster_options[ba_wp_pretty_url]"><?php _e( 'Pretty permalink URL:', 'wpwautoposter' ); ?></label> 
	                                </th>
	                                <td>
	                                    <input type="checkbox" name="wpw_auto_poster_options[ba_wp_pretty_url]" id="wpw_auto_poster_options[ba_wp_pretty_url]" class="ba_wp_pretty_url" data-content='ba' value="yes" <?php print $ba_wp_pretty_url;?>>
	                                    <p><small><?php _e( 'Check this box if you want to use pretty permalink. i.e. http://example.com/test-post/. (Not Recommnended).', 'wpwautoposter' ); ?></small></p>
	                                </td>
                            	</tr>

								<?php	        
									if( $wpw_auto_poster_options['ba_url_shortener'] == 'bitly' ) {	        		
										$class = '';	        		
									} else {	        		
										$class = ' style="display:none;"';
									}
									
									if( $wpw_auto_poster_options['ba_url_shortener'] == 'shorte.st' ) {
										$shortest_class = '';	        		
									} else {	        		
										$shortest_class = ' style="display:none;"';
									}
									
									if ( $wpw_auto_poster_options['ba_url_shortener'] == 'google_shortner') {
		                                $google_shortner_cls = '';
		                            } else {
		                                $google_shortner_cls = ' style="display:none;"';
		                            }
								?>
								
								<tr valign="top" class="ba_setting_input_bitly"<?php echo $class; ?>>
									<th scope="row">
										<label for="wpw_auto_poster_options[ba_bitly_access_token]"><?php _e( 'Bit.ly Access Token', 'wpwautoposter' ); ?> </label>
									</th>
									<td>
										<input type="text" name="wpw_auto_poster_options[ba_bitly_access_token]" id="wpw_auto_poster_options[ba_bitly_access_token]" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['ba_bitly_access_token'] ); ?>" class="large-text">
									</td>
								</tr>
								
								<tr valign="top" class="ba_setting_input_shortest"<?php echo $shortest_class; ?>>
									<th scope="row">
										<label for="wpw_auto_poster_options[ba_shortest_api_token]"><?php _e( 'Shorte.st API Token', 'wpwautoposter' ); ?> </label>
									</th>
									<td>
										<input type="text" name="wpw_auto_poster_options[ba_shortest_api_token]" id="wpw_auto_poster_options[ba_shortest_api_token]" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['ba_shortest_api_token'] ); ?>" class="large-text">
									</td>
								</tr>
								<tr valign="top" class="ba_setting_input_g_shortner" <?php echo $google_shortner_cls; ?>>
	                                <th scope="row">
	                                    <label for="wpw_auto_poster_options[ba_google_short_api_key]"><?php _e('Google API Key', 'wpwautoposter'); ?> </label>
	                                </th>
	                                <td>
	                                    <input type="text" name="wpw_auto_poster_options[ba_google_short_api_key]" id="wpw_auto_poster_options[ba_google_short_api_key]" value="<?php echo !empty($wpw_auto_poster_options["ba_google_short_api_key"])?($model->wpw_auto_poster_escape_attr($wpw_auto_poster_options["ba_google_short_api_key"])):''; ?>" class="large-text">
	                                    <p><small><?php _e( 'Enter Google Plus API Key. You need to enable <b>URL Shortener API</b>  in google plus application', 'wpwautoposter' ); ?></small></p>
	                                </td>
	                            </tr>
								
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
									
			</div><!-- #bufferapp_general -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-bufferapp-general -->
<!-- end of the bufferapp general settings meta box -->

<!-- beginning of the bufferapp api settings meta box -->
<div id="wpw-auto-poster-bufferapp-api" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="bufferapp_api" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
								
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'BufferApp API Settings', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
										
						<table class="form-table">											
							<tbody>
							
								<tr valign="top">
									<th scope="row">
										<label><?php _e( 'BufferApp Application:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<p><?php _e( 'Before you can start publishing your content to BufferApp you need to create a BufferApp Application.', 'wpwautoposter' ); ?></p>
										<p><?php printf( __('You can get a step by step tutorial on how to create a BufferApp Application on our %sDocumentation%s.', 'wpwautoposter' ), '<a href="http://wpweb.co.in/documents/social-network-integration/bufferapp/" target="_blank">', '</a>' ); ?></p> 
									</td>
								</tr>	
								
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[bufferapp_client_id]"><?php _e( 'Your BufferApp Client ID:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input type="text" name="wpw_auto_poster_options[bufferapp_client_id]" id="wpw_auto_poster_options[bufferapp_client_id]" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['bufferapp_client_id'] ); ?>" class="large-text">
									</td>
								</tr>
								
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[bufferapp_client_secret]"><?php _e( 'Your BufferApp Client Secret:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input type="text" name="wpw_auto_poster_options[bufferapp_client_secret]" id="wpw_auto_poster_options[bufferapp_client_secret]" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['bufferapp_client_secret'] ); ?>" class="large-text">
									</td>
								</tr>
								
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
									
			</div><!-- #bufferapp_api -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-bufferapp-api -->
<!-- end of the bufferapp api settings meta box -->

<?php if( isset($wpw_auto_poster_options['bufferapp_client_id']) && !empty($wpw_auto_poster_options['bufferapp_client_id']) && isset($wpw_auto_poster_options['bufferapp_client_secret']) && !empty($wpw_auto_poster_options['bufferapp_client_secret'])  ) { ?>

<!-- beginning of the grant extended permission meta box -->
<div id="wpw-auto-poster-bufferapp-grant-permission" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="grant_permission" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
									
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'Grant Extended Permissions', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
										
					<table class="form-table">											
						<tbody>				
							<tr valign="top">
								<th scope="row">
									<label><?php _e( 'Allowing permissions:', 'wpwautoposter' ); ?></label>
								</th>
								<td>
									<p><?php _e( 'Posting content to your chosen BufferApp account requires you to grant extended permissions. If you want to use this feature you should grant the extended permissions now.', 'wpwautoposter' ); ?></p>
									<?php										
										$returnurl = admin_url().'edit.php?post_type=wpw_auto_poster&page=wpw-auto-poster-settings';
										
										if( isset( $_SESSION['wpw_auto_poster_ba_user_id'] ) && !empty( $_SESSION['wpw_auto_poster_ba_user_id'] ) )  {
											
											echo '<p>' . __( 'You already granted extended permissions.', 'wpwautoposter' ) . '</p>';	
											echo apply_filters ( 'wpweb_ba_settings_reset_session', sprintf( __( "<a href='%s'>Reset User Session</a>", 'wpwautoposter' ), $returnurl.'&ba_reset_user=1' ) );
										} else {
										
											echo '<p><a href="' . $baposting->wpw_auto_poster_get_bufferapp_login_url() . '">' . __( 'Grant extended permissions', 'wpwautoposter' ) . '</a></p>';
										}
									?>
								</td>
							</tr> 
																				
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
			</div><!-- #grant_permissions -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-bufferapp-grant-permission -->
<!-- end of the grant extended permissions meta box -->

<?php } ?>

<!-- beginning of the autopost meta box -->
<div id="wpw-auto-poster-autopost-bufferapp" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="autopost" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
									
				<h3 class="hndle">
					<span style='vertical-align: top;'><?php _e( 'Autopost to BufferApp', 'wpwautoposter' ); ?></span>
				</h3>
								
				<div class="inside">
					<table class="form-table">
						<tbody>

							<tr valign="top">
								<th scope="row">
									<label for="wpw_auto_poster_options[prevent_post_ba_metabox]"><?php _e( 'Do not allow individual posts to BufferApp:', 'wpwautoposter' ); ?></label>
								</th>
								<td>
									<input name="wpw_auto_poster_options[prevent_post_ba_metabox]" id="wpw_auto_poster_options[prevent_post_ba_metabox]" type="checkbox" value="1" <?php if( isset( $wpw_auto_poster_options['prevent_post_ba_metabox'] ) ) { checked( '1', $wpw_auto_poster_options['prevent_post_ba_metabox'] ); } ?> />
									<p><small><?php _e( 'Check this box to hide meta settings for BufferApp from individual posts.', 'wpwautoposter' ); ?></small></p>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									<label><?php _e( 'Map WordPress types to BufferApp locations:', 'wpwautoposter' ); ?></label>
								</th>
								<td>
								<?php

									$types = get_post_types( array( 'public'=>true ), 'objects' );
									$types = is_array( $types ) ? $types : array();
									
									foreach( $types as $type ) {
														
										if( !is_object( $type ) ) continue;
										
											if( isset( $wpw_auto_poster_options['ba_type_' . $type->name . '_user'] ) ) {
												$wpw_auto_poster_ba_type_user = $wpw_auto_poster_options['ba_type_' . $type->name . '_user'];	
											} else {
												$wpw_auto_poster_ba_type_user = '';
											}
											
											$wpw_auto_poster_ba_type_user = ( array ) $wpw_auto_poster_ba_type_user;
											
											$label = @$type->labels->name ? $type->labels->name : $type->name;
											
											if( $label == 'Media' || $label == 'media' ) continue; // skip media
									?>		
											<div class="wpw-auto-poster-fb-types-wrap">
												<div class="wpw-auto-poster-ba-types-label">
													<?php	_e( 'Autopost', 'wpwautoposter' ); 
															echo ' '.$label; 
															_e( ' to Bufferapp of this user(s)', 'wpwautoposter' ); 
													?>
												</div><!--.wpw-auto-poster-ba-types-label-->
												<div class="wpw-auto-poster-ba-users-acc">
													<select name="wpw_auto_poster_options[<?php echo 'ba_type_' . $type->name . '_user';?>][]" id="wpw_auto_poster_options[<?php echo 'ba_type_' . $type->name . '_user';?>][]" multiple="multiple">
														<?php
															if ( isset( $_SESSION['wpw_auto_poster_ba_cache'] ) && count( $_SESSION['wpw_auto_poster_ba_cache'] ) > 0 ) {
																foreach ( $_SESSION['wpw_auto_poster_ba_cache'] as $key => $account ) {
																	echo '<option value="'.$account->id.'" '.selected( in_array( $account->id, $wpw_auto_poster_ba_type_user ), true, false ).'>'.$account->formatted_username.'</option>';
																}
															} //end if to check there is user connected to bufferapp or not
														?>
													</select>
												</div><!--.wpw-auto-poster-ba-users-acc-->
											</div><!--.wpw-auto-poster-fb-types-wrap-->
									<?php
										} //end foreach
									?>
								</td>
							</tr>
							
							<tr valign="top">									
								<th scope="row">
									<label for="wpw_auto_poster_options[ba_global_message_template]"><?php _e( 'Custom Message:', 'wpwautoposter' ); ?></label>
								</th>
								<td>
									<textarea type="text" name="wpw_auto_poster_options[ba_global_message_template]" id="wpw_auto_poster_options[ba_global_message_template]" class="large-text"><?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['ba_global_message_template'] ); ?></textarea>
									<p><small style="line-height: 20px;"><?php _e( 'Here you can enter default message which will be used for the posting on BufferApp. Leave it empty to use the post level message. You can use following template tags within the message template:', 'wpwautoposter' ); ?>
									<?php 
									$ba_template_str = '<br /><code>{first_name}</code> - ' . __('displays the first name,', 'wpwautoposter') .
						            '<br /><code>{last_name}</code> - ' . __('displays the last name,', 'wpwautoposter') .
						            '<br /><code>{title}</code> - ' . __('displays the default post title,', 'wpwautoposter') .
						            '<br /><code>{link}</code> - ' . __('displays the default post link,', 'wpwautoposter') .
						            '<br /><code>{sitename}</code> - ' . __('displays the name of your site,', 'wpwautoposter') .
						            '<br /><code>{excerpt}</code> - ' . __('displays the post excerpt.', 'wpwautoposter').
						            '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
						            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
						            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
						            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter');

						            print $ba_template_str;
						            ?>
									</small></p>
								</td>	
							</tr>

							<tr valign="top">
								<th scope="row">
									<label for="wpw_auto_poster_options[ba_post_img]"><?php _e( 'Post Image:', 'wpwautoposter' ); ?></label>
								</th>
								<td>
									<input type="text" name="wpw_auto_poster_options[ba_post_img]" id="wpw_auto_poster_options_ba_post_img" class="large-text wpw-auto-poster-img-field" value="<?php echo !empty($wpw_auto_poster_options['ba_post_img']) ? $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['ba_post_img'] ) : ''; ?>">
									<input type="button" class="button-secondary wpw-auto-poster-uploader-button" name="wpw-auto-poster-uploader" value="<?php _e( 'Add Image','wpwautoposter' );?>" />
									<p><small><?php _e( 'Enter custom post image url which will posted to bufferapp user\'s wall.', 'wpwautoposter' ); ?></small></p>
								</td>	
							</tr>
							
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
			</div><!-- #autopost -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-autopost -->
<!-- end of the autopost meta box -->