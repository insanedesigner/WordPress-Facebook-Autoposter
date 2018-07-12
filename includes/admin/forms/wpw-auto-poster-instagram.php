<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Instagram Settings
 *
 * The html markup for the Instagram settings tab.
 *
 * @package Social Auto Poster
 * @since 2.6.0
 */

global $wpw_auto_poster_options, $wpw_auto_poster_model;

// model class
$model = $wpw_auto_poster_model;

$instagram_keys = isset( $wpw_auto_poster_options['instagram_keys'] ) ? $wpw_auto_poster_options['instagram_keys'] : array();

//$wpw_auto_poster_fb_sess_data = get_option( 'wpw_auto_poster_fb_sess_data' ); // Getting facebook app grant data

$ins_wp_pretty_url = ( !empty( $wpw_auto_poster_options['ins_wp_pretty_url'] ) ) ? $wpw_auto_poster_options['ins_wp_pretty_url'] : '';

$ins_wp_pretty_url = !empty( $ins_wp_pretty_url ) ? ' checked="checked"' : '';
$ins_wp_pretty_url_css = ( $wpw_auto_poster_options['ins_url_shortener'] == 'wordpress' ) ? ' display:table-row': ' display:none';

// get url shortner service list array 
$ins_url_shortener = $model->wpw_auto_poster_get_shortner_list();

$ins_exclude_cats = array();

$error_msgs = array();
$readonly = "";

// Check if gd library loaded or not.
if( !extension_loaded('gd') ) {
   $error_msgs[] = sprintf( __( 'Instagram requires %sGD%s PHP library enabled. Contact your host or server administrator to configure and install the missing library.', 'wpwautoposter' ), '<b>', '</b>' );
   $readonly = 'readonly';
}

// Check if Exif is enabled or not
if( !function_exists('exif_imagetype') ) {   
   $error_msgs[] = sprintf( __( 'Instagram requires %sExif%s PHP library enabled. Contact your host or server administrator to configure and install the missing library.', 'wpwautoposter' ), '<b>', '</b>' );
   $readonly = 'readonly';
}

?>

<!-- beginning of the instagram general settings meta box -->
<div id="wpw-auto-poster-instagram-general" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="instagram_general" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
									
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'Instagram General Settings', 'wpwautoposter' ); ?></span>
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
										<label for="wpw_auto_poster_options[enable_instagram]"><?php _e( 'Enable Autoposting to Instagram:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input name="wpw_auto_poster_options[enable_instagram]" id="wpw_auto_poster_options[enable_instagram]" type="checkbox" value="1" <?php if( isset( $wpw_auto_poster_options['enable_instagram'] ) ) { checked( '1', $wpw_auto_poster_options['enable_instagram'] ); } ?> />
										<p><small><?php _e( 'Check this box, if you want to automatically post your new content to Instagram.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[enable_instagram_for]"><?php _e( 'Enable Instagram Autoposting for:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<ul>
										<?php 
											$all_types = get_post_types( array( 'public' => true ), 'objects');
											$all_types = is_array( $all_types ) ? $all_types : array();
											
											if( !empty( $wpw_auto_poster_options['enable_instagram_for'] ) ) {
												$prevent_meta = $wpw_auto_poster_options['enable_instagram_for'];
											} else {
												$prevent_meta = array();
											}

											if( !empty( $wpw_auto_poster_options['ins_post_type_tags'] ) ) {
												$ins_post_type_tags = $wpw_auto_poster_options['ins_post_type_tags'];
											} else {
												$ins_post_type_tags = array();
											}

											$static_post_type_arr = wpw_auto_poster_get_static_tag_taxonomy();

											if( !empty( $wpw_auto_poster_options['ins_post_type_cats'] ) ) {
												$ins_post_type_cats = $wpw_auto_poster_options['ins_post_type_cats'];
											} else {
												$ins_post_type_cats = array();
											}

											// Get saved categories for instagram to exclude from posting
											if( !empty( $wpw_auto_poster_options['ins_exclude_cats'] ) ) {
												$ins_exclude_cats = $wpw_auto_poster_options['ins_exclude_cats'];
											} 

											foreach( $all_types as $type ) {	
												
												if( !is_object( $type ) ) continue;															
													$label = @$type->labels->name ? $type->labels->name : $type->name;
													if( $label == 'Media' || $label == 'media' ) continue; // skip media
													$selected = ( in_array( $type->name, $prevent_meta ) ) ? 'checked="checked"' : '';
													
										?>
															
											<li class="wpw-auto-poster-prevent-types">
												<input type="checkbox" id="wpw_auto_posting_instagram_prevent_<?php echo $type->name; ?>" name="wpw_auto_poster_options[enable_instagram_for][]" value="<?php echo $type->name; ?>" <?php echo $selected; ?>/>
																						
												<label for="wpw_auto_posting_instagram_prevent_<?php echo $type->name; ?>"><?php echo $label; ?></label>
											</li>
											
											<?php	} ?>
										</ul>
										<p><small><?php _e( 'Check each of the post types that you want to post automatically to Instagram when they get published.', 'wpwautoposter' ); ?></small></p>  
									</td>
								</tr>
									
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_options[ins_post_type_tags][]"><?php _e( 'Select Tags:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[ins_post_type_tags][]" id="wpw_auto_poster_options[ins_post_type_tags]" class="ins_post_type_tags wpw-auto-poster-cats-tags-select" multiple="multiple">
											<?php foreach( $all_types as $type ) {	
												
												if( !is_object( $type ) ) continue;	

													if(in_array( $type->name, $prevent_meta )) {

														$label = @$type->labels->name ? $type->labels->name : $type->name;
														if( $label == 'Media' || $label == 'media' ) continue; // skip media
														$all_taxonomies = get_object_taxonomies( $type->name, 'objects' );
	                							
	                									echo '<optgroup label="'.$label.'">';
										                // Loop on all taxonomies
										                foreach ( $all_taxonomies as $taxonomy ) {

										                	$selected = '';

										                	if( !empty( $static_post_type_arr[$type->name] ) && $static_post_type_arr[$type->name] != $taxonomy->name){
                             										continue;
                    										}

										                	if(isset($ins_post_type_tags[$type->name]) && !empty($ins_post_type_tags[$type->name])) {
										                		$selected = ( in_array( $taxonomy->name, $ins_post_type_tags[$type->name] ) ) ? 'selected="selected"' : '';
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
										<label for="wpw_auto_poster_options[ins_post_type_cats][]"><?php _e( 'Select Categories:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[ins_post_type_cats][]" id="wpw_auto_poster_options[ins_post_type_cats]" class="ins_post_type_cats wpw-auto-poster-cats-tags-select" multiple="multiple">
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
										                	if(isset($ins_post_type_cats[$type->name]) && !empty($ins_post_type_cats[$type->name])) {
										                		$selected = ( in_array( $taxonomy->name, $ins_post_type_cats[$type->name]) ) ? 'selected="selected"' : '';
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
										<label for="wpw_auto_poster_options[ins_exclude_cats][]"><?php _e( 'Exclude Specific Categories:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[ins_exclude_cats][]" id="wpw_auto_poster_options[ins_exclude_cats]" class="ins_exclude_cats wpw-auto-poster-cats-exclude-select" multiple="multiple">
											
											<?php

												$post_type_categories = wpw_auto_poster_get_all_categories();

												if(!empty($post_type_categories)) {
													
													foreach($post_type_categories as $post_type => $post_data){

														echo '<optgroup label="'.$post_data['label'].'">';

														if(isset($post_data['categories']) && !empty($post_data['categories']) && is_array($post_data['categories'])){
															
															foreach($post_data['categories'] as $cat_slug => $cat_name){

																$selected = '';
																if( !empty( $ins_exclude_cats[$post_type] ) ) {
											                		$selected = ( in_array( $cat_slug, $ins_exclude_cats[$post_type] ) ) ? 'selected="selected"' : '';
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
										<label for="wpw_auto_poster_options[ins_url_shortener]"><?php _e( 'URL Shortener:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<select name="wpw_auto_poster_options[ins_url_shortener]" id="wpw_auto_poster_options[ins_url_shortener]" class="ins_url_shortener" data-content='ins'>
											<?php
																
												foreach ( $ins_url_shortener as $key => $option ) {											
													?>
													<option value="<?php echo $model->wpw_auto_poster_escape_attr( $key ); ?>" <?php selected( $wpw_auto_poster_options['ins_url_shortener'], $key ); ?>>
														<?php esc_html_e( $option ); ?>
													</option>
													<?php
												}
											?>
										</select>
										<p><small><?php _e( 'Long URLs will automatically be shortened using the specified URL shortener.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>

								

								<tr id="row-ins-wp-pretty-url" valign="top" style="<?php print $ins_wp_pretty_url_css;?>">
									<th scope="row">
										<label for="wpw_auto_poster_options[ins_wp_pretty_url]"><?php _e( 'Pretty permalink URL:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<input type="checkbox" name="wpw_auto_poster_options[ins_wp_pretty_url]" id="wpw_auto_poster_options[ins_wp_pretty_url]" class="ins_wp_pretty_url" data-content='ins' value="yes" <?php print $ins_wp_pretty_url;?>>
										<p><small><?php _e( 'Check this box if you want to use pretty permalink. i.e. http://example.com/test-post/. (Not Recommnended).', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>
								
								<?php	        
									
		                            $class = $shortest_class = $google_shortner_cls = ' style="display:none;"';

									if( $wpw_auto_poster_options['ins_url_shortener'] == 'bitly' ) {	        		
										$class = '';	        		
									} else if( $wpw_auto_poster_options['ins_url_shortener'] == 'shorte.st' ) {
										$shortest_class = '';	        		
									} else if ($wpw_auto_poster_options['ins_url_shortener'] == 'google_shortner') {
									    $google_shortner_cls = '';
									}
								?>
								
								<tr valign="top" class="ins_setting_input_bitly"<?php echo $class; ?>>
									<th scope="row">
										<label for="wpw_auto_poster_options[ins_bitly_access_token]"><?php _e( 'Bit.ly Access Token', 'wpwautoposter' ); ?> </label>
									</th>
									<td>
										<input type="text" name="wpw_auto_poster_options[ins_bitly_access_token]" id="wpw_auto_poster_options[ins_bitly_access_token]" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['ins_bitly_access_token'] ); ?>" class="large-text">
									</td>
								</tr>
								
								<tr valign="top" class="ins_setting_input_shortest"<?php echo $shortest_class; ?>>
									<th scope="row">
										<label for="wpw_auto_poster_options[ins_shortest_api_token]"><?php _e( 'Shorte.st API Token', 'wpwautoposter' ); ?> </label>
									</th>
									<td>
										<input type="text" name="wpw_auto_poster_options[ins_shortest_api_token]" id="wpw_auto_poster_options[ins_shortest_api_token]" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['ins_shortest_api_token'] ); ?>" class="large-text">
									</td>
								</tr>
							 	<tr valign="top" class="ins_setting_input_g_shortner" <?php echo $google_shortner_cls; ?>>
	                                <th scope="row">
	                                    <label for="wpw_auto_poster_options[ins_google_short_api_key]"><?php _e('Google API Key', 'wpwautoposter'); ?> </label>
	                                </th>
	                                <td>
	                                    <input type="text" name="wpw_auto_poster_options[ins_google_short_api_key]" id="wpw_auto_poster_options[ins_google_short_api_key]" value="<?php echo !empty($wpw_auto_poster_options["ins_google_short_api_key"])?($model->wpw_auto_poster_escape_attr($wpw_auto_poster_options["ins_google_short_api_key"])):''; ?>" class="large-text">
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
							
			</div><!-- #instagram_general -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-instagram-general -->
<!-- end of the instagram general settings meta box -->

<!-- beginning of the instagram api settings meta box -->
<div id="wpw-auto-poster-instagram-api" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="instagram_api" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
									
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'Instagram API Settings', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
										
						<table class="form-table wpw-auto-poster-instagram-settings">											
							<tbody>				
								<tr valign="top">
									<td scope="row">
										<strong><label><?php _e( 'Instagram Application:', 'wpwautoposter' ); ?></label></strong>
									</td>
									<td colspan="3">
										<p>
										<?php _e( 'Before you can start publishing your content to Instagram you need to provide your Instagram username and password.', 'wpwautoposter' ); ?>
										</p>  
									</td>
								</tr>
								
								<tr valign="top">
									<td scope="row">
										<strong><label for="wpw_auto_poster_options[instagram_keys][0][username]"><?php _e( 'Instagram Username', 'wpwautoposter' ); ?></label></strong>
									</td>
									<td scope="row">
										<strong><label for="wpw_auto_poster_options[instagram_keys][0][password]"><?php _e( 'Instagram Password', 'wpwautoposter' ); ?></label></strong>
									</td>
									
									<td></td>
								</tr>
								
							<?php

							if( !empty( $instagram_keys ) ) {
								
								foreach ( $instagram_keys as $instagram_key => $instagram_value ) {
									
									// Don't disply delete link for first row
									$instagram_delete_class = empty( $instagram_key ) ? '' : ' wpw-auto-poster-display-inline ';
							?>
								<tr valign="top" class="wpw-auto-poster-instagram-account-details" data-row-id="<?php echo $instagram_key; ?>">
									<td scope="row" width="30%">
										<input type="text" name="wpw_auto_poster_options[instagram_keys][<?php echo $instagram_key; ?>][username]" value="<?php echo $model->wpw_auto_poster_escape_attr( $instagram_value['username'] ); ?>" class="large-text wpw-auto-poster-instagram-username" <?php echo $readonly;?>/>
										<p><small><?php _e( 'Enter Instagram Username.', 'wpwautoposter' ); ?></small></p>  
									</td>
									<td scope="row" width="30%">
										<input type="password" name="wpw_auto_poster_options[instagram_keys][<?php echo $instagram_key; ?>][password]" value="<?php echo $model->wpw_auto_poster_escape_attr( $instagram_value['password'] ); ?>" class="large-text wpw-auto-poster-instagram-password" <?php echo $readonly;?>/>
										<p><small><?php _e( 'Enter Instagram Password', 'wpwautoposter' ); ?></small></p>  
									</td>
									
									<td>
										<a href="javascript:void(0);" class="wpw-auto-poster-delete-ins-account wpw-auto-poster-instagram-remove <?php echo $instagram_delete_class; ?>" title="<?php _e( 'Delete', 'wpwautoposter' ); ?>"><img src="<?php echo WPW_AUTO_POSTER_META_URL; ?>/images/delete-16.png" alt="<?php _e('Delete','wpwautoposter'); ?>"/></a>
									</td>
								</tr>
							<?php 
								}
							} else {
							?>
								<tr valign="top" class="wpw-auto-poster-instagram-account-details" data-row-id="<?php echo (empty($instagram_key) ? '': $instagram_key); ?>">
									<td scope="row" width="30%">
										<input type="text" name="wpw_auto_poster_options[instagram_keys][0][username]" value="" class="large-text wpw-auto-poster-instagram-username" />
										<p><small><?php _e( 'Enter Instagram Username.', 'wpwautoposter' ); ?></small></p>  
									</td>
									<td scope="row" width="30%">
										<input type="password" name="wpw_auto_poster_options[instagram_keys][0][password]" value="" class="large-text wpw-auto-poster-instagram-password" />
										<p><small><?php _e( 'Enter Instagram Password', 'wpwautoposter' ); ?></small></p>  
									</td>
									<td>
										<a href="javascript:void(0);" class="wpw-auto-poster-delete-ins-account wpw-auto-poster-instagram-remove" title="<?php _e( 'Delete', 'wpwautoposter' ); ?>"><img src="<?php echo WPW_AUTO_POSTER_META_URL; ?>/images/delete-16.png" alt="<?php _e('Delete','wpwautoposter'); ?>"/></a>
									</td>
								</tr>
							<?php } ?>
							
								<tr>
									<td colspan="4">
										<a class='wpw-auto-poster-add-more-ins-account button' href='javascript:void(0);'><?php _e( 'Add more', 'wpwautoposter' ); ?></a>
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
							
			</div><!-- #instagram_api -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-instagram-api -->
<!-- end of the instagram api settings meta box -->

<!-- beginning of the autopost to instagram meta box -->
<div id="wpw-auto-poster-autopost-instagram" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="autopost_instagram" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
									
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'Autopost to Instagram', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
										
						<table class="form-table">											
							<tbody>
							
								<tr valign="top"> 
									<th scope="row">
										<label for="wpw_auto_poster_options[prevent_post_ins_metabox]"><?php _e( 'Do not allow individual posts to Instagram:', 'wpwautoposter' ); ?></label>
									</th>									
									<td>
										<input name="wpw_auto_poster_options[prevent_post_ins_metabox]" id="wpw_auto_poster_options[prevent_post_ins_metabox]" type="checkbox" value="1" <?php if( isset( $wpw_auto_poster_options['prevent_post_ins_metabox'] ) ) { checked( '1', $wpw_auto_poster_options['prevent_post_ins_metabox'] ); } ?> />
										<p><small><?php _e( 'If you run a multi author blog, then you can prevent your authors to posting to individual Instagram Accounts by checking this box. If checked, then all posts, created by any author, will get posted to your chosen Instagram Account.', 'wpwautoposter' ); ?></small></p>
									</td>	
								</tr>
                                
                                <tr valign="top">
									<th scope="row">
                                        <label for="wpw_auto_poster_options[ins_proxy]"><?php _e( 'Proxy', 'wpwautoposter' ); ?></label>
									</th>
									<td scope="row">
                                        <textarea rows="5" cols="70" id="wpw_auto_poster_options[ins_proxy]" name="wpw_auto_poster_options[ins_proxy]" placeholder="Enter one IP per line(example: http://00.00.00.00:(port) or with ssl)"><?php echo !empty($wpw_auto_poster_options['ins_proxy'])? $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['ins_proxy'] ): ''; ?></textarea>
                                        <p><small><?php _e( 'Enter one IP per line(example: http://00.00.00.00:(port) or with ssl)', 'wpwautoposter' ); ?></small></p>
									</td>																		
								</tr>
								
								<?php
								
									$types = get_post_types( array( 'public'=>true ), 'objects' );
									$types = is_array( $types ) ? $types : array();
								?>
								<tr valign="top">
									<th scope="row">
										<label><?php _e( 'Map WordPress types to Instagram locations:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										
											<?php
												
												// Getting all instagram accounts
												$ins_accounts = $instagram_keys;
												
												foreach( $types as $type ) {
													
													if( !is_object( $type ) ) continue;
													
	
														$label = @$type->labels->name ? $type->labels->name : $type->name;
														
														if( $label == 'Media' || $label == 'media' ) continue; // skip media
													?>
													<div class="wpw-auto-poster-fb-types-wrap">
														<div class="wpw-auto-poster-fb-types-label">
															<?php	_e( 'Autopost', 'wpwautoposter' ); 
																	echo ' '.$label; 
																	_e( ' to Instagram', 'wpwautoposter' ); 
															?>
														</div><!--.wpw-auto-poster-fb-types-label-->
														
														<div class="wpw-auto-poster-fb-user-label">
															<?php _e( 'of this user', 'wpwautoposter' ); ?>(<?php _e( 's', 'wpwautoposter' );?>)
														</div><!--.wpw-auto-poster-fb-user-label-->
														<div class="wpw-auto-poster-fb-users-acc">
															<?php
																if( isset( $wpw_auto_poster_options['ins_type_'.$type->name.'_user'] ) ) {
																	$wpw_auto_poster_ins_type_user = $wpw_auto_poster_options['ins_type_'.$type->name.'_user'];	 
																} else {
																	$wpw_auto_poster_ins_type_user = '';
																}
																
																$wpw_auto_poster_ins_type_user = ( array ) $wpw_auto_poster_ins_type_user;

															?>
															<select name="wpw_auto_poster_options[ins_type_<?php echo $type->name; ?>_user][]" multiple="multiple">
																<?php
																if( !empty($ins_accounts) && is_array($ins_accounts) ) {
																	
																	foreach( $ins_accounts as $aid => $aval ) {

																		if( is_array( $aval ) ) { 
																				$value = $aval['username']."|".$aval['password'];
																				?>
																				<option value="<?php echo $value; ?>" <?php selected( in_array( $value, $wpw_auto_poster_ins_type_user ), true, true ); ?>><?php echo $aval['username']; ?></option>
																			
																			</optgroup>
																			
																<?php	} else { ?>
																				<option value="<?php echo $aid; ?>" <?php selected( in_array( $aid, $wpw_auto_poster_ins_type_user ), true, true ); ?> ><?php echo $aval; ?></option>
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
										<label for="wpw_auto_poster_options[ins_custom_img]"><?php _e( 'Post Image:', 'wpwautoposter' ); ?></label>
										
									</th>
									<td>
										<input type="text" value="<?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['ins_custom_img'] ); ?>" name="wpw_auto_poster_options[ins_custom_img]" id="wpw_auto_poster_options_ins_custom_img" class="large-text wpw-auto-poster-img-field">
										<input type="button" class="button-secondary wpw-auto-poster-uploader-button" name="wpw-auto-poster-uploader" value="<?php _e( 'Add Image','wpwautoposter' );?>" />
										<p><small><?php _e( 'Here you can upload a default image which will be used for the Instagram wall post.', 'wpwautoposter' ); ?></small></p><br>
										<p><small><strong><?php _e('Note: ', 'wpwautoposter'); ?></strong><?php 
											$ins_notes = __('Instagram require atleast one image for posting.  ', 'wpwautoposter');

											$ins_notes .= '<b>'.__('Recommended image width between 320 to 1080 pixels.', 'wpwautoposter').'</b><br><br>';

											$ins_notes .= __( 'If the image width is less than 320 pixels, it will be automatically enlarged to 320 pixels. If the image width is greater than 1080 pixels, it will be automatically resized to 1080 pixels.', 'wpwautoposter' );
											print $ins_notes;

										?></small></p>
										
									</td>	
								</tr>
								
								<tr valign="top">									
									<th scope="row">
										<label for="wpw_auto_poster_options[ins_template]"><?php _e( 'Custom Message:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<textarea name="wpw_auto_poster_options[ins_template]" id="wpw_auto_poster_options[ins_template]" class="large-text"><?php echo $model->wpw_auto_poster_escape_attr( $wpw_auto_poster_options['ins_template'] ); ?></textarea>
										<p><small style="line-height: 20px;"><?php _e( 'Here you can enter default caption which will be used for the timeline. Leave it empty to use the post level caption. You can use following template tags within the caption template:', 'wpwautoposter' ); ?>
										<?php 
										$ins_template_str = '<br /><code>{first_name}</code> - ' . __('displays the first name,', 'wpwautoposter') .
							            '<br /><code>{last_name}</code> - ' . __('displays the last name,', 'wpwautoposter') .
							            '<br /><code>{display_name}</code> - ' . __('displays the display name,', 'wpwautoposter') .
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
							
			</div><!-- #autopost_instagram -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #ps-poster-autopost-instagram -->
<!-- end of the autopost to instagram meta box -->