<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Tumblr Settings
 *
 * The html markup for the Tumblr settings tab.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */

global $wpw_auto_poster_options, $wpw_auto_poster_model, $wpw_auto_poster_tb_posting;

//model class
$model = $wpw_auto_poster_model;

//tumblr posting class
$tbposting = $wpw_auto_poster_tb_posting;

$tb_wp_pretty_url = ( !empty( $wpw_auto_poster_options['tb_wp_pretty_url'] ) ) ? $wpw_auto_poster_options['tb_wp_pretty_url'] : '';

$tb_wp_pretty_url = !empty( $tb_wp_pretty_url ) ? ' checked="checked"' : '';
$tb_wp_pretty_url_css = ( $wpw_auto_poster_options['tb_url_shortener'] == 'wordpress' ) ? ' display:table-row': ' display:none';

// get url shortner service list array 
$tb_url_shortener = $model->wpw_auto_poster_get_shortner_list();
$tb_exclude_cats = array();

$wpw_auto_poster_options['tb_global_message_template'] = !empty( $wpw_auto_poster_options['tb_global_message_template'] ) ? $wpw_auto_poster_options['tb_global_message_template']: '';

?>

<!-- beginning of the tumblr general settings meta box -->
<div id="wpw-auto-poster-tumblr-general" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="tumblr_general" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
								
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'Tumblr General Settings', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
										
						<table class="form-table">											
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[enable_tumblr]"><?php _e( 'Enable Autoposting to Tumblr:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input name="wpw_auto_poster_options[enable_tumblr]" id="wpw_auto_poster_options[enable_tumblr]" type="checkbox" value="1" <?php if( isset( $wpw_auto_poster_options['enable_tumblr'] ) ) { checked( '1', $wpw_auto_poster_options['enable_tumblr'] ); } ?> />
										<p><small><?php _e( 'Check this box, if you want to autopost your content to Tumblr.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>	

								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[enable_tumblr_for]"><?php _e( 'Enable Tumblr Autoposting for:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<ul>
										<?php
										
											$all_types = get_post_types( array( 'public' => true ), 'objects');
											$all_types = is_array( $all_types ) ? $all_types : array();

											if( !empty( $wpw_auto_poster_options['enable_tumblr_for'] ) ) {
												$prevent_meta = $wpw_auto_poster_options['enable_tumblr_for'];
											} else {
												$prevent_meta = '';
											}
															
											$prevent_meta = is_array( $prevent_meta ) ? $prevent_meta : array();

											if( !empty( $wpw_auto_poster_options['tb_post_type_tags'] ) ) {
												$tb_post_type_tags = $wpw_auto_poster_options['tb_post_type_tags'];
											} else {
												$tb_post_type_tags = array();
											}

											$static_post_type_arr = wpw_auto_poster_get_static_tag_taxonomy();

											if( !empty( $wpw_auto_poster_options['tb_post_type_cats'] ) ) {
												$tb_post_type_cats = $wpw_auto_poster_options['tb_post_type_cats'];
											} else {
												$tb_post_type_cats = array();
											}

											// Get saved categories for tumblr to exclude from posting
											if( !empty( $wpw_auto_poster_options['tb_exclude_cats'] ) ) {
												$tb_exclude_cats = $wpw_auto_poster_options['tb_exclude_cats'];
											}
														
											foreach ( $all_types as $type ) {	
															
												if ( !is_object( $type ) ) continue;															
													$label = @$type->labels->name ? $type->labels->name : $type->name;
													if( $label == 'Media' || $label == 'media' ) continue; // skip media
													$selected = ( in_array( $type->name, $prevent_meta ) ) ? 'checked="checked"' : '';
										?>
															
											<li class="wpw-auto-poster-prevent-types">
												<input type="checkbox" id="wpw_auto_posting_tumblr_prevent_<?php echo $type->name; ?>" name="wpw_auto_poster_options[enable_tumblr_for][]" value="<?php echo $type->name; ?>" <?php echo $selected; ?>/>
																						
												<label for="wpw_auto_posting_tumblr_prevent_<?php echo $type->name; ?>"><?php echo $label; ?></label>
											</li>
											
											<?php	} ?>
										</ul>
										<p><small><?php _e( 'Check each of the post types you want to automatically post to Tumblr when they get published.', 'wpwautoposter' ); ?></small></p>  
									</td>
								</tr>
								
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[tb_post_type_tags][]"><?php _e( 'Select Tags:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[tb_post_type_tags][]" id="wpw_auto_poster_options[tb_post_type_tags]" class="tb_post_type_tags wpw-auto-poster-cats-tags-select" multiple="multiple">
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
										                	if(isset($tb_post_type_tags[$type->name]) && !empty($tb_post_type_tags[$type->name])) {
										                		$selected = ( in_array( $taxonomy->name, $tb_post_type_tags[$type->name] ) ) ? 'selected="selected"' : '';
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
										<label for="wpw_auto_poster_options[tb_post_type_cats][]"><?php _e( 'Select Categories:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[tb_post_type_cats][]" id="wpw_auto_poster_options[tb_post_type_cats]" class="tb_post_type_cats wpw-auto-poster-cats-tags-select" multiple="multiple">
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
										                	if(isset($tb_post_type_cats[$type->name]) && !empty($tb_post_type_cats[$type->name])) {
										                		$selected = ( in_array( $taxonomy->name, $tb_post_type_cats[$type->name]) ) ? 'selected="selected"' : '';
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
										<label for="wpw_auto_poster_options[tb_exclude_cats][]"><?php _e( 'Exclude Specific Categories:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[tb_exclude_cats][]" id="wpw_auto_poster_options[tb_exclude_cats]" class="tb_exclude_cats wpw-auto-poster-cats-exclude-select" multiple="multiple">
											
											<?php

												$post_type_categories = wpw_auto_poster_get_all_categories();

												if(!empty($post_type_categories)) {

													foreach($post_type_categories as $post_type => $post_data){

														echo '<optgroup label="'.$post_data['label'].'">';

														if(isset($post_data['categories']) && !empty($post_data['categories']) && is_array($post_data['categories'])){
															
															foreach($post_data['categories'] as $cat_slug => $cat_name){

																$selected ='';
																if( !empty($tb_exclude_cats[$post_type] ) ) {
											                		$selected = ( in_array( $cat_slug, $tb_exclude_cats[$post_type] ) ) ? 'selected="selected"' : '';
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

								<?php 
										$tumblrcontent = isset( $wpw_auto_poster_options['tumblr_content_type'] ) && !empty( $wpw_auto_poster_options['tumblr_content_type'] ) 
															? $wpw_auto_poster_options['tumblr_content_type'] : '';
										
								?>
								<tr>
									<th scope="row">
										<label for="wpw_auto_poster_options[tumblr_content_type]"><?php _e( 'Post Content:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input type="radio" id="tumblr_content_type_full" name="wpw_auto_poster_options[tumblr_content_type]" <?php if( empty( $tumblrcontent ) )  { checked ( '', $tumblrcontent, true ); }//echo ' checked="checked"';  ?> value=""/>
										<label for="tumblr_content_type_full" class="wpw-auto-poster-label"><?php _e( 'Full', 'wpwautoposter' );?></label>
										
										<input type="radio" id="tumblr_content_type_snippets" name="wpw_auto_poster_options[tumblr_content_type]" <?php checked ( '1', $tumblrcontent, true );?> value="1"/>
										<label for="tumblr_content_type_snippets" class="wpw-auto-poster-label"><?php _e( 'Snippets', 'wpwautoposter' );?></label>
										<p><small><?php _e( 'Choose whether you want to post the full content or just a snippet to your Tumblr page. if you choose snippets, the plugin will post the first 200 characters from your post. You always have the ability to customize that within the meta box.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>
								
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[tb_url_shortener]"><?php _e( 'URL Shortener:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[tb_url_shortener]" id="wpw_auto_poster_options[tb_url_shortener]" class="tb_url_shortener" data-content='tb'>
											<?php
											
												foreach ( $tb_url_shortener as $key => $option ) {											
													?>
													<option value="<?php echo $model->wpw_auto_poster_escape_attr( $key ); ?>" <?php selected( $wpw_auto_poster_options['tb_url_shortener'], $key ); ?>>
														<?php esc_html_e( $option ); ?>
													</option>
													<?php
												}
											?>
										</select>
										<p><small><?php _e( 'Long URLs will automatically be shortened using the specified URL shortener.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>
								
								<tr id="row-tb-wp-pretty-url" valign="top" style="<?php print $tb_wp_pretty_url_css;?>">
									<th scope="row">
										<label for="wpw_auto_poster_options[tb_wp_pretty_url]"><?php _e( 'Pretty permalink URL:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<input type="checkbox" name="wpw_auto_poster_options[tb_wp_pretty_url]" id="wpw_auto_poster_options[tb_wp_pretty_url]" class="tb_wp_pretty_url" data-content='tb' value="yes" <?php print $tb_wp_pretty_url;?>>
										<p><small><?php _e( 'Check this box if you want to use pretty permalink. i.e. http://example.com/test-post/. (Not Recommnended).', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>

								<?php	        
									if( $wpw_auto_poster_options['tb_url_shortener'] == 'bitly' ) {	        		
										$class = '';	        		
									} else {	        		
										$class = ' style="display:none;"';
									}
									
									if( $wpw_auto_poster_options['tb_url_shortener'] == 'shorte.st' ) {
										$shortest_class = '';	        		
									} else {	        		
										$shortest_class = ' style="display:none;"';
									}
									
									if ($wpw_auto_poster_options['tb_url_shortener'] == 'google_shortner') {
		                                $google_shortner_cls = '';
		                            } else {
		                                $google_shortner_cls = ' style="display:none;"';
		                            }
								?>
								
								<tr valign="top" class="tb_setting_input_bitly"<?php echo $class; ?>>
									<th scope="row">
										<label for="wpw_auto_poster_options[tb_bitly_access_token]"><?php _e( 'Bit.ly Access Token', 'wpwautoposter' ); ?> </label>
									</th>
									<td>
										<input type="text" name="wpw_auto_poster_options[tb_bitly_access_token]" id="wpw_auto_poster_options[tb_bitly_access_token]" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['tb_bitly_access_token'] ); ?>" class="large-text">
									</td>
								</tr>
								
								<tr valign="top" class="tb_setting_input_shortest"<?php echo $shortest_class; ?>>
									<th scope="row">
										<label for="wpw_auto_poster_options[tb_shortest_api_token]"><?php _e( 'Shorte.st API Token', 'wpwautoposter' ); ?> </label>
									</th>
									<td>
										<input type="text" name="wpw_auto_poster_options[tb_shortest_api_token]" id="wpw_auto_poster_options[tb_shortest_api_token]" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['tb_shortest_api_token'] ); ?>" class="large-text">
									</td>
								</tr>
								
								 <tr valign="top" class="tb_setting_input_g_shortner" <?php echo $google_shortner_cls; ?>>
	                                <th scope="row">
	                                    <label for="wpw_auto_poster_options[tb_google_short_api_key]"><?php _e('Google API Key', 'wpwautoposter'); ?> </label>
	                                </th>
	                                <td>
	                                    <input type="text" name="wpw_auto_poster_options[tb_google_short_api_key]" id="wpw_auto_poster_options[tb_google_short_api_key]" value="<?php echo !empty($wpw_auto_poster_options["tb_google_short_api_key"])?($model->wpw_auto_poster_escape_attr($wpw_auto_poster_options["tb_google_short_api_key"])):''; ?>" class="large-text">
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
									
			</div><!-- #tumblr_general -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-tumblr-general -->
<!-- end of the tumblr general settings meta box -->

<!-- beginning of the tumblr api settings meta box -->
<div id="wpw-auto-poster-tumblr-api" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="twitter_api" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
								
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'Tumblr API Settings', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
										
						<table class="form-table">											
							<tbody>
							
								<tr valign="top">
									<th scope="row">
										<label><?php _e( 'Tumblr Application:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<p><?php _e( 'Before you can start publishing your content to Tumblr you need to create a Tumblr Application.', 'wpwautoposter' ); ?></p>
										<p><?php printf( __('You can get a step by step tutorial on how to create a Tumblr Application on our %sDocumentation%s.', 'wpwautoposter' ), '<a href="http://wpweb.co.in/documents/social-network-integration/tumblr/" target="_blank">', '</a>' ); ?></p> 
									</td>
								</tr>	
							
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[tumblr_consumer_key]"><?php _e( 'Tumblr OAuth Consumer Key:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input type="text" name="wpw_auto_poster_options[tumblr_consumer_key]" id="wpw_auto_poster_options[tumblr_consumer_key]" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['tumblr_consumer_key'] ); ?>" class="large-text">
									</td>
								</tr>
								
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[tumblr_consumer_secret]"><?php _e( 'Tumblr Secret Key:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input type="text" name="wpw_auto_poster_options[tumblr_consumer_secret]" id="wpw_auto_poster_options[tumblr_consumer_secret]" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['tumblr_consumer_secret'] ); ?>" class="large-text">
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
									
			</div><!-- #tumblr_api -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-tumblr-api -->
<!-- end of the tumblr api settings meta box -->

<?php if( isset( $wpw_auto_poster_options['tumblr_consumer_key'] ) && !empty( $wpw_auto_poster_options['tumblr_consumer_key'] ) 
		&& isset( $wpw_auto_poster_options['tumblr_consumer_secret'] ) && !empty( $wpw_auto_poster_options['tumblr_consumer_secret'] )  ) { ?>

<!-- beginning of the grant extended permission meta box -->
<div id="wpw-auto-poster-tumblr-grant-permission" class="post-box-container">
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
									<p><?php _e( 'Posting content to your chosen Tumblr personal account requires you to grant extended permissions. If you want to use this feature you should grant the extended permissions now.', 'wpwautoposter' ); ?></p>
									<?php										
										$returnurl = admin_url().'edit.php?post_type=wpw_auto_poster&page=wpw-auto-poster-settings';
										
										if( isset( $_SESSION['wpw_auto_poster_tb_user_id'] ) && !empty( $_SESSION['wpw_auto_poster_tb_user_id'] ) )  {
											
											echo '<p>' . __( 'You already granted extended permissions', 'wpwautoposter' ) . '</p>';
											echo apply_filters ( 'wpweb_tb_settings_reset_session', sprintf( __( "<a href='%s'>Reset User Session</a>", 'wpwautoposter' ), $returnurl.'&tb_reset_user=1' ) );
										} else {
										
											echo '<p><a href="' . $tbposting->wpw_auto_poster_get_tb_login_url() . '">' . __( 'Grant extended permissions', 'wpwautoposter' ) . '</a></p>';
										}
									?>
								</td>
							</tr><br />
							<tr>
								<td colspan="2">
									<p class="wpw-auto-poster-info-box"><?php _e( '<b>Note: </b>Please note the Tumblr App, Tumblr profile or page and the user who authorizes the app MUST belong to the <b>same Tumblr account</b>. So please make sure you are logged in to Tumblr as the same user who created the app.', 'wpwautoposter' ); ?></p>
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
</div><!-- #wpw-auto-poster-tumblr-grant-permission -->
<!-- end of the grant extended permissions meta box -->

<?php } ?>

<!-- beginning of the autopost meta box -->
<div id="wpw-auto-poster-autopost" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="autopost" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
									
				<h3 class="hndle">
					<span style='vertical-align: top;'><?php _e( 'Autopost to Tumblr', 'wpwautoposter' ); ?></span>
				</h3>
								
				<div class="inside">
					<table class="form-table">
						<tbody>

							<tr valign="top">
								<th scope="row">
									<label for="wpw_auto_poster_options[prevent_post_tb_metabox]"><?php _e( 'Do not allow individual posts to Tumblr:', 'wpwautoposter' ); ?></label>
								</th>
								<td>
									<input name="wpw_auto_poster_options[prevent_post_tb_metabox]" id="wpw_auto_poster_options[prevent_post_tb_metabox]" type="checkbox" value="1" <?php if( isset( $wpw_auto_poster_options['prevent_post_tb_metabox'] ) ) { checked( '1', $wpw_auto_poster_options['prevent_post_tb_metabox'] ); } ?> />
									<p><small><?php _e( 'Check this box to hide meta settings for Tumblr from individual posts.', 'wpwautoposter' ); ?></small></p>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row">
									<label for="wpw_auto_poster_options[tb_posting_type]"><?php _e( 'Posting Type:', 'wpwautoposter' ); ?></label>
								</th>
								<td>
									<select name="wpw_auto_poster_options[tb_posting_type]" id="wpw_auto_poster_options[tb_posting_type]" class="tb_posting_type">
									<?php
										$tb_posting_type = array( 'text' => __( 'Text', 'wpwautoposter' ), 'link' => __( 'Link', 'wpwautoposter' ), 'photo' => __('Photo', 'wpwautoposter') );
										foreach ( $tb_posting_type as $key => $option ) {											
											?>
											<option value="<?php echo $model->wpw_auto_poster_escape_attr( $key ); ?>" <?php selected( $wpw_auto_poster_options['tb_posting_type'], $key ); ?>>
												<?php esc_html_e( $option ); ?>
											</option>
											<?php
										}
									?>
									</select>
									<p><small><?php _e( 'Choose posting type which you want to use, Default is Text posting.', 'wpwautoposter' ); ?></small></p>
								</td>	
							</tr>

							<tr valign="top">
								<th scope="row">
									<label for="wpw_auto_poster_options[tb_custom_img]"><?php _e( 'Post Image:', 'wpwautoposter' ); ?></label>
								</th>
								<td>
									<input type="text" value="<?php echo !empty( $wpw_auto_poster_options['tb_custom_img'] ) ? $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['tb_custom_img'] ) : ''; ?>" name="wpw_auto_poster_options[tb_custom_img]" id="wpw_auto_poster_options_tb_custom_img" class="large-text wpw-auto-poster-img-field">
									<input type="button" class="button-secondary wpw-auto-poster-uploader-button" name="wpw-auto-poster-uploader" value="<?php _e( 'Add Image','wpwautoposter' );?>" />
									<p><small><?php _e( 'Here you can upload a default image which will be used for the tumblr post.', 'wpwautoposter' ); ?></small></p>
								</td>	
							</tr>

							<tr valign="top">									
								<th scope="row">
									<label for="wpw_auto_poster_options[tb_global_message_template]"><?php _e( 'Custom Message:', 'wpwautoposter' ); ?></label>
								</th>
								<td>
									<textarea type="text" name="wpw_auto_poster_options[tb_global_message_template]" id="wpw_auto_poster_options[tb_global_message_template]" class="large-text"><?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['tb_global_message_template'] ); ?></textarea>
									<p><small style="line-height: 20px;"><?php _e( 'Here you can enter default message template which will be used for the posting on Tumblr. Leave it empty to use the post level message. You can use following template tags within the message template:', 'wpwautoposter' ); ?>
									<?php 
									$tb_template_str = '<br /><code>{first_name}</code> - ' . __('displays the first name,', 'wpwautoposter') .
						            '<br /><code>{last_name}</code> - ' . __('displays the last name,', 'wpwautoposter') .
						            '<br /><code>{title}</code> - ' . __('displays the default post title,', 'wpwautoposter') .
						            '<br /><code>{link}</code> - ' . __('displays the default post link,', 'wpwautoposter') .
						            '<br /><code>{sitename}</code> - ' . __('displays the name of your site,', 'wpwautoposter') .
						            '<br /><code>{excerpt}</code> - ' . __('displays the post excerpt.', 'wpwautoposter').
						            '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
						            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
						            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
						            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter');
						            print $tb_template_str;
						            ?>
									</small></p>
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