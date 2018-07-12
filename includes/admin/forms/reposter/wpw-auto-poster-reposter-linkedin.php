<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * LinkedIn Settings
 *
 * The html markup for the LinkedIn settings tab.
 *
 * @package Social Auto Poster
 * @since 2.6.9
 */

global $wpw_auto_poster_reposter_options, $wpw_auto_poster_model;

//model class
$model = $wpw_auto_poster_model;

$cat_posts_type = !empty( $wpw_auto_poster_reposter_options['li_posting_cats'] ) ? $wpw_auto_poster_reposter_options['li_posting_cats']: 'include';
$li_exclude_cats = array();

// Get saved categories for li to exclude from posting
if( !empty( $wpw_auto_poster_reposter_options['li_post_type_cats'] ) ) {
	$li_exclude_cats = $wpw_auto_poster_reposter_options['li_post_type_cats'];
}

$li_last_posted_page = ( !empty( $wpw_auto_poster_reposter_options['li_last_posted_page'] ) ) ? $wpw_auto_poster_reposter_options['li_last_posted_page'] : '1';

$exludes_post_ids = !empty( $wpw_auto_poster_reposter_options['li_post_ids_exclude']) ? $wpw_auto_poster_reposter_options['li_post_ids_exclude'] : '';
?>

<!-- beginning of the linkedin general settings meta box -->
<div id="wpw-auto-poster-linkedin-general" class="post-box-container">
	<div class="metabox-holder">	
		<div class="meta-box-sortables ui-sortable">
			<div id="linkedin_general" class="postbox">	
				<div class="handlediv" title="<?php _e( 'Click to toggle', 'wpwautoposter' ); ?>"><br /></div>
								
					<h3 class="hndle">
						<span style='vertical-align: top;'><?php _e( 'LinkedIn Settings', 'wpwautoposter' ); ?></span>
					</h3>
									
					<div class="inside">
										
						<table class="form-table">											
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_reposter_options[enable_linkedin]"><?php _e( 'Repost to LinkedIn:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input name="wpw_auto_poster_reposter_options[enable_linkedin]" id="wpw_auto_poster_reposter_options[enable_linkedin]" type="checkbox" value="1" <?php if( isset( $wpw_auto_poster_reposter_options['enable_linkedin'] ) ) { checked( '1', $wpw_auto_poster_reposter_options['enable_linkedin'] ); } ?> />
										<p><small><?php _e( 'Check this box, if you want to automatically post your new content to LinkedIn.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>	

								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_reposter_options[enable_linkedin_for]"><?php _e( 'Repost for:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<ul>
										<?php 
											$all_types = get_post_types( array( 'public' => true ), 'objects');
											$all_types = is_array( $all_types ) ? $all_types : array();
											
											if( !empty( $wpw_auto_poster_reposter_options['enable_linkedin_for'] ) ) {
												$prevent_meta = $wpw_auto_poster_reposter_options['enable_linkedin_for'];
											} else {
												$prevent_meta = '';
											}
															
											$prevent_meta = is_array( $prevent_meta ) ? $prevent_meta : array();

											if( !empty( $wpw_auto_poster_reposter_options['li_post_type_cats'] ) ) {
												$li_post_type_cats = $wpw_auto_poster_reposter_options['li_post_type_cats'];
											} else {
												$li_post_type_cats = array();
											}

														
											foreach( $all_types as $type ) {	
																											
												if( !is_object( $type ) ) continue;															
													$label = @$type->labels->name ? $type->labels->name : $type->name;
													if( $label == 'Media' || $label == 'media' ) continue; // skip media
													$selected = ( in_array( $type->name, $prevent_meta ) ) ? 'checked="checked"' : '';
										?>
															
											<li class="wpw-auto-poster-prevent-types">
												<input type="checkbox" id="wpw_auto_posting_linkedin_prevent_<?php echo $type->name; ?>" name="wpw_auto_poster_reposter_options[enable_linkedin_for][]" value="<?php echo $type->name; ?>" <?php echo $selected; ?>/>
																						
												<label for="wpw_auto_posting_linkedin_prevent_<?php echo $type->name; ?>"><?php echo $label; ?></label>
											</li>
											
											<?php	} ?>
										</ul>
										<p><small><?php _e( 'Check each of the post types that you want to post automatically to LinkedIn.', 'wpwautoposter' ); ?></small></p>  
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_reposter_options[li_post_type_cats][]"><?php _e( 'Select Categories:', 'wpwautoposter' ); ?></label> 
									</th>
									<td>
										<div class="wpw-auto-poster-cats-option">
											<input name="wpw_auto_poster_reposter_options[li_posting_cats]" id="li_cats_include" type="radio" value="include" <?php checked( 'include', $cat_posts_type ); ?> />
											<label for="li_cats_include"><?php _e( 'Include (Post only with)', 'wpwautoposter');?></label>
											<input name="wpw_auto_poster_reposter_options[li_posting_cats]" id="li_cats_exclude" type="radio" value="exclude" <?php checked( 'exclude', $cat_posts_type ); ?> />
											<label for="li_cats_exclude"><?php _e( 'Exclude (Do not post)', 'wpwautoposter');?></label>
										</div>
										<select name="wpw_auto_poster_reposter_options[li_post_type_cats][]" id="wpw_auto_poster_reposter_options[li_post_type_cats]" class="li_post_type_cats wpw-auto-poster-cats-tags-select" multiple="multiple">
											<?php

												$post_type_categories = wpw_auto_poster_get_all_categories();

												if(!empty($post_type_categories)) {
													
													foreach($post_type_categories as $post_type => $post_data){

														echo '<optgroup label="'.$post_data['label'].'">';

														if(isset($post_data['categories']) && !empty($post_data['categories']) && is_array($post_data['categories'])){
															
															foreach($post_data['categories'] as $cat_slug => $cat_name){

																$selected ='';
																if( !empty( $li_exclude_cats[$post_type] ) ) {
											                		$selected = ( in_array( $cat_slug, $li_exclude_cats[$post_type] ) ) ? 'selected="selected"' : '';
											                	}
											                	
																echo '<option value="' . $post_type ."|".$cat_slug . '" '.$selected.'>'.$cat_name.'</option>';
															}

														}
														echo '</optgroup>';
													}
												}

											?>
										</select>
										<div class="wpw-ajax-loader"><img src="<?php echo WPW_AUTO_POSTER_IMG_URL."/icons/ajax-loader.gif";?>"/></div>
										<p><small><?php _e( 'Select the Categories for each post type that you want to include or exclude for the repost.', 'wpwautoposter' ); ?></small></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="wpw_auto_poster_reposter_options[li_post_ids_exclude]"><?php _e( 'Exclude Posts:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<textarea placeholder="1100,1200,1300" cols="35" id="wpw_auto_poster_reposter_options[li_post_ids_exclude]" name="wpw_auto_poster_reposter_options[li_post_ids_exclude]"><?php echo $exludes_post_ids; ?></textarea>
										<p><small>
											<?php _e( 'Enter the post ids seprated by comma(,) which you want to exclude for the posting.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>
								<tr valign="top" class="wpw-auto-poster-schedule-limit">
									<th scope="row">
										<label for="wpw_auto_poster_reposter_options[li_posts_limit]"><?php _e( 'Maximum Posting per schedule:', 'wpwautoposter' ); ?></label>
									</th>
									<td>
										<input id="wpw_auto_poster_reposter_options[li_posts_limit]" name="wpw_auto_poster_reposter_options[li_posts_limit]" type="text" value="<?php echo $wpw_auto_poster_reposter_options['li_posts_limit']; ?>" />
										<p><small>
											<?php _e( 'Enter the maximum auto posting allowed on each schedule execution. Leave it empty for unlimited posting.', 'wpwautoposter' ); ?>
										</small></p>
									</td>
								</tr>

								<?php
									echo apply_filters ( 
														 'wpweb_reposter_li_settings_submit_button', 
														 '<tr valign="top">
																<td colspan="2">
																	<input type="submit" value="' . __( 'Save Changes', 'wpwautoposter' ) . '" id="wpw_auto_poster_reposter_set_submit" name="wpw_auto_poster_reposter_set_submit" class="button-primary">
																</td>
															</tr>'
														);
								?> 													
							</tbody>
						</table>
						<input type="hidden" name="wpw_auto_poster_reposter_options[li_last_posted_page]" value="<?php print $li_last_posted_page;?>">				
					</div><!-- .inside -->
									
			</div><!-- #linkedin_general -->
		</div><!-- .meta-box-sortables ui-sortable -->
	</div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-linkedin-general -->
<!-- end of the linkedin general settings meta box -->