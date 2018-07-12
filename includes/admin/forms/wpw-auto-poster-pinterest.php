<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Pinterest Settings
 *
 * The html markup for the Pinterest settings tab.
 *
 * @package Social Auto Poster
 * @since 2.6.0
 */

global $wpw_auto_poster_options, $wpw_auto_poster_model, $wpw_auto_poster_pin_posting;

// model class
$model = $wpw_auto_poster_model;

// pinterest posting class
$pinposting = $wpw_auto_poster_pin_posting;

$pinterest_keys = isset( $wpw_auto_poster_options['pinterest_keys'] ) ? $wpw_auto_poster_options['pinterest_keys'] : array();

$wpw_auto_poster_pin_sess_data = get_option( 'wpw_auto_poster_pin_sess_data' ); // Getting pinterest app grant data

$pin_wp_pretty_url = ( !empty( $wpw_auto_poster_options['pin_wp_pretty_url'] ) ) ? $wpw_auto_poster_options['pin_wp_pretty_url'] : '';

$pin_wp_pretty_url = !empty( $pin_wp_pretty_url ) ? ' checked="checked"' : '';
$pin_wp_pretty_url_css = ( $wpw_auto_poster_options['pin_url_shortener'] == 'wordpress' ) ? ' display:table-row': ' display:none';

$error_msgs = array();
$readonly = "";

// Check if site is ssl enabled, if not than set error message.
if (!is_ssl()) {
   
   $error_msgs[] = sprintf( __( 'Pinterest requires %sSSL%s for posting to boards.', 'wpwautoposter' ), '<b>', '</b>' );
   $readonly = 'readonly';
}

$redirect_uri = admin_url('admin.php') ."?page=wpw-auto-poster-settings&wpw_pinterest_grant=true&wpw_pinterest_app_id={app_id}";

// get url shortner service list array 
$pin_url_shortener = $model->wpw_auto_poster_get_shortner_list();
$pin_exclude_cats = array();

?>

<!-- beginning of the pinterest general settings meta box -->
<div id="wpw-auto-poster-pinterest-general" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="pinterest_general" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
									
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'Pinterest General Settings', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
						<?php if(!empty($error_msgs)) { ?>
							<div class="wpw-auto-poster-error">
                                <ul>
                                    <?php foreach ( $error_msgs as $error_msg ) { ?>
                                        <li><?php echo $error_msg;?></li>
                                    <?php } ?>
                                </ul>								
							</div>
						<?php } ?>				
						<table class="form-table">											
							<tbody>				
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[enable_pinterest]"><?php _e( 'Enable Autoposting to Pinterest:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input name="wpw_auto_poster_options[enable_pinterest]" id="wpw_auto_poster_options[enable_pinterest]" type="checkbox" value="1" <?php if( isset( $wpw_auto_poster_options['enable_pinterest'] ) ) { checked( '1', $wpw_auto_poster_options['enable_pinterest'] ); } ?> />
										<p><small><?php _e( 'Check this box, if you want to automatically post your new content to Pinterest.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[enable_pinterest_for]"><?php _e( 'Enable Pinterest Autoposting for:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<ul>
										<?php 
											$all_types = get_post_types( array( 'public' => true ), 'objects');
											$all_types = is_array( $all_types ) ? $all_types : array();
											
											if( !empty( $wpw_auto_poster_options['enable_pinterest_for'] ) ) {
												$prevent_meta = $wpw_auto_poster_options['enable_pinterest_for'];
											} else {
												$prevent_meta = array();
											}

											if( !empty( $wpw_auto_poster_options['pin_post_type_tags'] ) ) {
												$pin_post_type_tags = $wpw_auto_poster_options['pin_post_type_tags'];
											} else {
												$pin_post_type_tags = array();
											}

											$static_post_type_arr = wpw_auto_poster_get_static_tag_taxonomy();

											if( !empty( $wpw_auto_poster_options['pin_post_type_cats'] ) ) {
												$pin_post_type_cats = $wpw_auto_poster_options['pin_post_type_cats'];
											} else {
												$pin_post_type_cats = array();
											}

											// Get saved categories for pinterest to exclude from posting
											if( !empty( $wpw_auto_poster_options['pin_exclude_cats'] ) ) {
												$pin_exclude_cats = $wpw_auto_poster_options['pin_exclude_cats'];
											} 
											
											foreach( $all_types as $type ) {	
												
												if( !is_object( $type ) ) continue;															
													$label = @$type->labels->name ? $type->labels->name : $type->name;
													if( $label == 'Media' || $label == 'media' ) continue; // skip media
													$selected = ( in_array( $type->name, $prevent_meta ) ) ? 'checked="checked"' : '';
													
										?>
															
											<li class="wpw-auto-poster-prevent-types">
												<input type="checkbox" id="wpw_auto_posting_pinterest_prevent_<?php echo $type->name; ?>" name="wpw_auto_poster_options[enable_pinterest_for][]" value="<?php echo $type->name; ?>" <?php echo $selected; ?>/>
																						
												<label for="wpw_auto_posting_pinterest_prevent_<?php echo $type->name; ?>"><?php echo $label; ?></label>
											</li>
											
											<?php	} ?>
										</ul>
										<p><small><?php _e( 'Check each of the post types that you want to post automatically to Pinterest when they get published.', 'wpwautoposter' ); ?></small></p>  
									</td>
								</tr>
									
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[pin_post_type_tags][]"><?php _e( 'Select Tags:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[pin_post_type_tags][]" id="wpw_auto_poster_options[pin_post_type_tags]" class="pin_post_type_tags wpw-auto-poster-cats-tags-select" multiple="multiple">
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
										                	if(isset($pin_post_type_tags[$type->name]) && !empty($pin_post_type_tags[$type->name])) {
										                		$selected = ( in_array( $taxonomy->name, $pin_post_type_tags[$type->name] ) ) ? 'selected="selected"' : '';
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
										<label for="wpw_auto_poster_options[pin_post_type_cats][]"><?php _e( 'Select Categories:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[pin_post_type_cats][]" id="wpw_auto_poster_options[pin_post_type_cats]" class="pin_post_type_cats wpw-auto-poster-cats-tags-select" multiple="multiple">
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
										                	if(isset($pin_post_type_cats[$type->name]) && !empty($pin_post_type_cats[$type->name])) {
										                		$selected = ( in_array( $taxonomy->name, $pin_post_type_cats[$type->name]) ) ? 'selected="selected"' : '';
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
										<label for="wpw_auto_poster_options[pin_exclude_cats][]"><?php _e( 'Exclude Specific Categories:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[pin_exclude_cats][]" id="wpw_auto_poster_options[pin_exclude_cats]" class="pin_exclude_cats wpw-auto-poster-cats-exclude-select" multiple="multiple">
											
											<?php

												$post_type_categories = wpw_auto_poster_get_all_categories();

												if(!empty($post_type_categories)) {
												
													foreach($post_type_categories as $post_type => $post_data){

														echo '<optgroup label="'.$post_data['label'].'">';

														if(isset($post_data['categories']) && !empty($post_data['categories']) && is_array($post_data['categories'])){
															
															foreach($post_data['categories'] as $cat_slug => $cat_name){

																$selected ='';
																if( !empty($pin_exclude_cats[$post_type] ) ) {
											                		$selected = ( in_array( $cat_slug, $pin_exclude_cats[$post_type] ) ) ? 'selected="selected"' : '';
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
										<label for="wpw_auto_poster_options[pin_url_shortener]"><?php _e( 'URL Shortener:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[pin_url_shortener]" id="wpw_auto_poster_options[pin_url_shortener]" class="pin_url_shortener" data-content='pin'>
											<?php
																
												foreach ( $pin_url_shortener as $key => $option ) {											
													?>
													<option value="<?php echo $model->wpw_auto_poster_escape_attr( $key ); ?>" <?php selected( $wpw_auto_poster_options['pin_url_shortener'], $key ); ?>>
														<?php esc_html_e( $option ); ?>
													</option>
													<?php
												}
											?>
										</select>
										<p><small><?php _e( 'Long URLs will automatically be shortened using the specified URL shortener.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>

								<tr id="row-pin-wp-pretty-url" valign="top" style="<?php print $pin_wp_pretty_url_css;?>">
									<th scope="row">
										<label for="wpw_auto_poster_options[pin_wp_pretty_url]"><?php _e( 'Pretty permalink URL:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<input type="checkbox" name="wpw_auto_poster_options[pin_wp_pretty_url]" id="wpw_auto_poster_options[pin_wp_pretty_url]" class="pin_wp_pretty_url" data-content='pin' value="yes" <?php print $pin_wp_pretty_url;?>>
										<p><small><?php _e( 'Check this box if you want to use pretty permalink. i.e. http://example.com/test-post/. (Not Recommnended).', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>
								
								<?php	        
									if( $wpw_auto_poster_options['pin_url_shortener'] == 'bitly' ) {	        		
										$class = '';	        		
									} else {	        		
										$class = ' style="display:none;"';
									}
									
									if( $wpw_auto_poster_options['pin_url_shortener'] == 'shorte.st' ) {
										$shortest_class = '';	        		
									} else {	        		
										$shortest_class = ' style="display:none;"';
									}
									
								  	if ($wpw_auto_poster_options['pin_url_shortener'] == 'google_shortner') {
		                                $google_shortner_cls = '';
		                            } else {
		                                $google_shortner_cls = ' style="display:none;"';
		                            }
								?>
								
								<tr valign="top" class="pin_setting_input_bitly"<?php echo $class; ?>>
									<th scope="row">
										<label for="wpw_auto_poster_options[pin_bitly_access_token]"><?php _e( 'Bit.ly Access Token', 'wpwautoposter' ); ?> </label>
									</th>
									<td>
										<input type="text" name="wpw_auto_poster_options[pin_bitly_access_token]" id="wpw_auto_poster_options[pin_bitly_access_token]" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['pin_bitly_access_token'] ); ?>" class="large-text">
									</td>
								</tr>
								
								<tr valign="top" class="pin_setting_input_shortest"<?php echo $shortest_class; ?>>
									<th scope="row">
										<label for="wpw_auto_poster_options[pin_shortest_api_token]"><?php _e( 'Shorte.st API Token', 'wpwautoposter' ); ?> </label>
									</th>
									<td>
										<input type="text" name="wpw_auto_poster_options[pin_shortest_api_token]" id="wpw_auto_poster_options[pin_shortest_api_token]" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['pin_shortest_api_token'] ); ?>" class="large-text">
									</td>
								</tr>
							 	<tr valign="top" class="pin_setting_input_g_shortner" <?php echo $google_shortner_cls; ?>>
	                                <th scope="row">
	                                    <label for="wpw_auto_poster_options[pin_google_short_api_key]"><?php _e('Google API Key', 'wpwautoposter'); ?> </label>
	                                </th>
	                                <td>
	                                    <input type="text" name="wpw_auto_poster_options[pin_google_short_api_key]" id="wpw_auto_poster_options[pin_google_short_api_key]" value="<?php echo !empty($wpw_auto_poster_options["pin_google_short_api_key"])?($model->wpw_auto_poster_escape_attr($wpw_auto_poster_options["pin_google_short_api_key"])):''; ?>" class="large-text">
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
							
			</div><!-- #pinterest_general -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-pinterest-general -->
<!-- end of the pinterest general settings meta box -->

<!-- beginning of the pinterest api settings meta box -->
<div id="wpw-auto-poster-pinterest-api" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="pinterest_api" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
									
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'Pinterest API Settings', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
										
						<table class="form-table wpw-auto-poster-pinterest-settings">											
							<tbody>				
								<tr valign="top">
									<td scope="row">
										<strong><label><?php _e( 'Pinterest Application:', 'wpwautoposter' ); ?></label></strong>
									</td>
									<td colspan="3">
										<p>
										<?php _e( 'Before you can start publishing your content to Pinterest you need to create a Pinterest Application.', 'wpwautoposter' ); ?>
										</p> 
										<p><?php printf( __('You can get a step by step tutorial on how to create a Pinterest Application on our %sDocumentation%s.', 'wpwautoposter' ), '<a href="http://wpweb.co.in/documents/social-network-integration/pinterest/" target="_blank">', '</a>' ); ?></p> 
									</td>
								</tr>
								
								<tr>
									<td scope="row">
										<strong><label><?php _e( 'Allowing permissions', 'wpwautoposter' ); ?></label></strong>
									</td>
									<td colspan="3">
										<p><?php _e( 'Posting content to your chosen Pinterest boards requires you to grant extended permissions. If you want to use this feature you should grant the extended permissions now.', 'wpwautoposter' ); ?></p>
									</td>
								</tr> 
								
								<tr>
									<td colspan="3">
										<p class="wpw-auto-poster-info-box"><?php echo sprintf(__( "<b>Note: </b>You need to define redirect uri as mentioned below when you create Pinterest Application. Otherwise pinterest won't redirect you to the correct page after authorization. Replace {app_id} with your pinterest application key/id.</br><code class='wpw-auto-poster-url'>%s</code>", "wpwautoposter"), $redirect_uri); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<td scope="row">
										<strong><label for="wpw_auto_poster_options[pinterest_keys][0][app_id]"><?php _e( 'Pinterest App ID/API Key', 'wpwautoposter' ); ?></label></strong>
									</td>
									<td scope="row">
										<strong><label for="wpw_auto_poster_options[pinterest_keys][0][app_secret]"><?php _e( 'Pinterest App Secret', 'wpwautoposter' ); ?></label></strong>
									</td>
									<td scope="row">
										<strong><label><?php _e( 'Allowing permissions', 'wpwautoposter' ); ?></label></strong>
									</td>
									<td></td>
								</tr>
								
							<?php
							if( !empty( $pinterest_keys ) ) {
								
								foreach ( $pinterest_keys as $pinterest_key => $pinterest_value ) {
									
									// Don't disply delete link for first row
									$pinterest_delete_class = empty( $pinterest_key ) ? '' : ' wpw-auto-poster-display-inline ';
							?>
								<tr valign="top" class="wpw-auto-poster-pinterest-account-details" data-row-id="<?php echo $pinterest_key; ?>">
									<td scope="row" width="30%">
										<input type="text" name="wpw_auto_poster_options[pinterest_keys][<?php echo $pinterest_key; ?>][app_id]" value="<?php echo $model->wpw_auto_poster_escape_attr( $pinterest_value['app_id'] ); ?>" class="large-text wpw-auto-poster-pinterest-app-id" <?php echo $readonly;?>/>
										<p><small><?php _e( 'Enter Pinterest App ID / API Key.', 'wpwautoposter' ); ?></small></p>  
									</td>
									<td scope="row" width="30%">
										<input type="text" name="wpw_auto_poster_options[pinterest_keys][<?php echo $pinterest_key; ?>][app_secret]" value="<?php echo $model->wpw_auto_poster_escape_attr( $pinterest_value['app_secret'] ); ?>" class="large-text wpw-auto-poster-pinterest-app-secret" <?php echo $readonly;?>/>
										<p><small><?php _e( 'Enter Pinterest App Secret.', 'wpwautoposter' ); ?></small></p>  
									</td>
									<td scope="row" width="40%" valign="top" class="wpw-grant-reset-data">
										<?php
											
											if( !empty($pinterest_value['app_id']) && !empty($pinterest_value['app_secret']) && !empty($wpw_auto_poster_pin_sess_data[ $pinterest_value['app_id'] ]) )  {
												
												echo '<p>' . __( 'You already granted extended permissions.', 'wpwautoposter' ) . '</p>';	
												echo apply_filters ( 'wpweb_pin_settings_reset_session', sprintf( __( "<a href='%s'>Reset User Session</a>", 'wpwautoposter' ), add_query_arg( array( 'page' => 'wpw-auto-poster-settings', 'pin_reset_user' => '1', 'wpw_pin_app' => $pinterest_value['app_id'] ), admin_url( 'admin.php' ) ) ) );
										
											} elseif( !empty($pinterest_value['app_id']) && !empty($pinterest_value['app_secret']) ) {
												echo '<p><a href="' . $pinposting->wpw_auto_poster_get_pinterest_login_url( $pinterest_value['app_id'] ) . '">' . __( 'Grant extended permissions', 'wpwautoposter' ) . '</a></p>';
											}
										?>
									</td>
									<td>
										<a href="javascript:void(0);" class="wpw-auto-poster-delete-pin-account wpw-auto-poster-pinterest-remove <?php echo $pinterest_delete_class; ?>" title="<?php _e( 'Delete', 'wpwautoposter' ); ?>"><img src="<?php echo WPW_AUTO_POSTER_META_URL; ?>/images/delete-16.png" alt="<?php _e('Delete','wpwautoposter'); ?>"/></a>
									</td>
								</tr>
							<?php 
								}
							} else {
							?>
								<tr valign="top" class="wpw-auto-poster-pinterest-account-details" data-row-id="<?php echo (empty($pinterest_key) ? '': $pinterest_key); ?>">
									<td scope="row" width="30%">
										<input type="text" name="wpw_auto_poster_options[pinterest_keys][0][app_id]" value="" class="large-text wpw-auto-poster-pinterest-app-id" <?php echo $readonly;?>/>
										<p><small><?php _e( 'Enter Pinterest App ID / API Key.', 'wpwautoposter' ); ?></small></p>  
									</td>
									<td scope="row" width="30%">
										<input type="text" name="wpw_auto_poster_options[pinterest_keys][0][app_secret]" value="" class="large-text wpw-auto-poster-pinterest-app-secret" <?php echo $readonly;?>/>
										<p><small><?php _e( 'Enter Pinterest App Secret.', 'wpwautoposter' ); ?></small></p>  
									</td>
									<td scope="row" width="40%" valign="top" class="wpw-grant-reset-data"></td>
									<td>
										<a href="javascript:void(0);" class="wpw-auto-poster-delete-pin-account wpw-auto-poster-pinterest-remove" title="<?php _e( 'Delete', 'wpwautoposter' ); ?>"><img src="<?php echo WPW_AUTO_POSTER_META_URL; ?>/images/delete-16.png" alt="<?php _e('Delete','wpwautoposter'); ?>"/></a>
									</td>
								</tr>
							<?php } ?>
							
								<tr>
									<td colspan="4">
										<a class='wpw-auto-poster-add-more-pin-account button' href='javascript:void(0);'><?php _e( 'Add more', 'wpwautoposter' ); ?></a>
									</td>
								</tr> 
								
								<?php
									echo apply_filters ( 
														 'wpweb_fb_settings_submit_button', 
														 '<tr valign="top">
																<td colspan="4">
																	<input type="submit" value="' . __( 'Save Changes', 'wpwautoposter' ) . '" id="wpw_auto_poster_set_submit" name="wpw_auto_poster_set_submit" class="button-primary">
																</td>
															</tr>'
														);
								?>
							</tbody>
						</table>
									
					</div><!-- .inside -->
							
			</div><!-- #pinterest_api -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-pinterest-api -->
<!-- end of the pinterest api settings meta box -->

<?php if( isset($wpw_auto_poster_options['app_id']) && !empty($wpw_auto_poster_options['app_id']) && isset($wpw_auto_poster_options['app_secret']) && !empty($wpw_auto_poster_options['app_secret'])  ) { ?>


<?php } ?>

<!-- beginning of the autopost to pinterest meta box -->
<div id="wpw-auto-poster-autopost-pinterest" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="autopost_pinterest" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
									
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'Autopost to Pinterest', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
										
						<table class="form-table">											
							<tbody>
							
								<tr valign="top"> 
									<th scope="row">
										<label for="wpw_auto_poster_options[prevent_post_pin_metabox]"><?php _e( 'Do not allow individual posts to Pinterest:', 'wpwautoposter' ); ?></label>
									</th>									
									<td>
										<input name="wpw_auto_poster_options[prevent_post_pin_metabox]" id="wpw_auto_poster_options[prevent_post_pin_metabox]" type="checkbox" value="1" <?php if( isset( $wpw_auto_poster_options['prevent_post_pin_metabox'] ) ) { checked( '1', $wpw_auto_poster_options['prevent_post_pin_metabox'] ); } ?> />
										<p><small></small></p>
									</td>	
								</tr>
										
								<?php				
										
									$types = get_post_types( array( 'public'=>true ), 'objects' );
									$types = is_array( $types ) ? $types : array();
								?>
								<tr valign="top">
									<th scope="row">
										<label><?php _e( 'Map WordPress types to Pinterest locations:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										
											<?php
												
												// Getting all pinterest account/boards
												$pin_accounts = wpw_auto_poster_get_pin_accounts( 'all_app_users_with_boards' );
												
												foreach( $types as $type ) {
													
													if( !is_object( $type ) ) continue;
													
														if( isset( $wpw_auto_poster_options['pin_type_' . $type->name . '_method'] ) ) {
															$wpw_auto_poster_pin_type_method = $wpw_auto_poster_options['pin_type_' . $type->name . '_method'];	
														} else {
															$wpw_auto_poster_pin_type_method = '';
														}
														$label = @$type->labels->name ? $type->labels->name : $type->name;
														
														if( $label == 'Media' || $label == 'media' ) continue; // skip media
													?>
													<div class="wpw-auto-poster-fb-types-wrap">
														<div class="wpw-auto-poster-fb-types-label">
															<?php	_e( 'Autopost', 'wpwautoposter' ); 
																	echo ' '.$label; 
																	_e( ' to Pinterest', 'wpwautoposter' ); 
															?>
														</div><!--.wpw-auto-poster-fb-types-label-->
														
														<div class="wpw-auto-poster-fb-user-label">
															<?php _e( 'of this user', 'wpwautoposter' ); ?>(<?php _e( 's', 'wpwautoposter' );?>)
														</div><!--.wpw-auto-poster-fb-user-label-->
														<div class="wpw-auto-poster-fb-users-acc">
															<?php
																if( isset( $wpw_auto_poster_options['pin_type_'.$type->name.'_user'] ) ) {
																	$wpw_auto_poster_pin_type_user = $wpw_auto_poster_options['pin_type_'.$type->name.'_user'];	 
																} else {
																	$wpw_auto_poster_pin_type_user = '';
																}
																
																$wpw_auto_poster_pin_type_user = ( array ) $wpw_auto_poster_pin_type_user;
															?>
															
															<select name="wpw_auto_poster_options[pin_type_<?php echo $type->name; ?>_user][]" multiple="multiple">
																<?php
																if( !empty($pin_accounts) && is_array($pin_accounts) ) {
																	
																	foreach( $pin_accounts as $aid => $aval ) {
																		
																		if( is_array( $aval ) ) {

																			$pin_app_data 	= isset( $wpw_auto_poster_pin_sess_data[$aid] ) ? $wpw_auto_poster_pin_sess_data[$aid] : array();

																			$pin_opt_label	= !empty( $pin_app_data['wpw_auto_poster_pin_user_name'] ) ? $pin_app_data['wpw_auto_poster_pin_user_name'] .' - ' : '';
																			$pin_opt_label	= $pin_opt_label . $aid;
																	?>
																			<optgroup label="<?php echo $pin_opt_label; ?>">
																			
																			<?php foreach ( $aval as $aval_key => $aval_data ) { ?>
																				<option value="<?php echo $aval_key; ?>" <?php selected( in_array( $aval_key, $wpw_auto_poster_pin_type_user ), true, true ); ?> ><?php echo $aval_data; ?></option>
																			<?php } ?>
																			
																			</optgroup>
																			
																<?php	} else { ?>
																				<option value="<?php echo $aid; ?>" <?php selected( in_array( $aid, $wpw_auto_poster_pin_type_user ), true, true ); ?> ><?php echo $aval; ?></option>
																<?php 	}
																	
																	} // End of foreach
																} // End of main if
																?>
															</select>
														</div><!--.wpw-auto-poster-fb-users-acc-->
													</div><!--.wpw-auto-poster-fb-types-wrap-->
											<?php } ?>
										
									</td>
								</tr> 
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[pin_custom_img]"><?php _e( 'Post Image:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['pin_custom_img'] ); ?>" name="wpw_auto_poster_options[pin_custom_img]" id="wpw_auto_poster_options_pin_custom_img" class="large-text wpw-auto-poster-img-field">
										<input type="button" class="button-secondary wpw-auto-poster-uploader-button" name="wpw-auto-poster-uploader" value="<?php _e( 'Add Image','wpwautoposter' );?>" />
										<p><small><?php _e( 'Here you can upload a default image which will be used for the Pinterest board.', 'wpwautoposter' ); ?></small></p><br>
										<p><small><strong><?php _e('Note: ', 'wpwautoposter'); ?></strong><?php _e( 'You need to select atleast one image, otherwise pinterest posting will not work.', 'wpwautoposter' );?></small></p>
									</td>	
								</tr>
								<tr valign="top">									
									<th scope="row">
										<label for="wpw_auto_poster_options[pin_custom_template]"><?php _e( 'Custom Message:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<textarea name="wpw_auto_poster_options[pin_custom_template]" id="wpw_auto_poster_options[pin_custom_template]" class="large-text"><?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['pin_custom_template'] ); ?></textarea>
										<p><small style="line-height: 20px;"><?php _e( 'Here you can enter default notes which will be used for the pins. Leave it empty to use the post level notes. You can use following template tags within the notes template:', 'wpwautoposter' ); ?>
										<?php 
										$ins_template_str = '<br /><code>{first_name}</code> - ' . __('displays the first name,', 'wpwautoposter') .
							            '<br /><code>{last_name}</code> - ' . __('displays the last name,', 'wpwautoposter') .
							            '<br /><code>{title}</code> - ' . __('displays the default post title,', 'wpwautoposter') .
							            '<br /><code>{link}</code> - ' . __('displays the default post link,', 'wpwautoposter') .
							            '<br /><code>{sitename}</code> - ' . __('displays the name of your site,', 'wpwautoposter') .
							            '<br /><code>{excerpt}</code> - ' . __('displays the post excerpt.', 'wpwautoposter').
							            '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
							            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
							            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
						            	'<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter');
							            print $ins_template_str;
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
							
			</div><!-- #autopost_pinterest -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #ps-poster-autopost-pinterest -->
<!-- end of the autopost to pinterest meta box -->