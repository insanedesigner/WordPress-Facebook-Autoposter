<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Twitter Settings
 *
 * The html markup for the Twitter settings tab.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
global $wpw_auto_poster_options, $wpw_auto_poster_model;

//model class
$model = $wpw_auto_poster_model;

$twitter_keys = isset($wpw_auto_poster_options['twitter_keys']) ? $wpw_auto_poster_options['twitter_keys'] : array();

$tw_wp_pretty_url = ( !empty( $wpw_auto_poster_options['tw_wp_pretty_url'] ) ) ? $wpw_auto_poster_options['tw_wp_pretty_url'] : '';

$tw_wp_pretty_url = !empty( $tw_wp_pretty_url ) ? ' checked="checked"' : '';
$tw_wp_pretty_url_css = ( $wpw_auto_poster_options['tw_url_shortener'] == 'wordpress' ) ? ' display:table-row': ' display:none';

// get url shortner service list array 
$tw_url_shortener = $model->wpw_auto_poster_get_shortner_list();
$tw_exclude_cats = array();
?>

<!-- beginning of the twitter general settings meta box -->
<div id="wpw-auto-poster-twitter-general" class="post-box-container">
    <div class="metabox-holder">	
        <div class="meta-box-sortables ui-sortable">
            <div id="twitter_general" class="postbox">	
                <div class="handlediv" title="<?php _e('Click to toggle', 'wpwautoposter'); ?>"><br /></div>

                <h3 class="hndle">
                    <span style='vertical-align: top;'><?php _e('Twitter General Settings', 'wpwautoposter'); ?></span>
                </h3>

                <div class="inside">

                    <table class="form-table">											
                        <tbody>										
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wpw_auto_poster_options[enable_twitter]"><?php _e('Enable Autoposting to Twitter:', 'wpwautoposter'); ?></label>
                                </th>
                                <td>
                                    <input name="wpw_auto_poster_options[enable_twitter]" id="wpw_auto_poster_options[enable_twitter]" type="checkbox" value="1" <?php if (isset($wpw_auto_poster_options['enable_twitter'])) {
    checked('1', $wpw_auto_poster_options['enable_twitter']);
} ?> />
                                    <p><small><?php _e('Check this box, if you want to automatically post your new content to Twitter.', 'wpwautoposter'); ?></small></p>
                                </td>
                            </tr>	

                            <tr valign="top">
                                <th scope="row">
                                    <label for="wpw_auto_poster_options[enable_twitter_for]"><?php _e('Enable Twitter Autoposting for:', 'wpwautoposter'); ?></label>
                                </th>
                                <td>
                                    <ul>
                                        <?php
                                        $all_types = get_post_types(array('public' => true), 'objects');
                                        $all_types = is_array($all_types) ? $all_types : array();

                                        if (!empty($wpw_auto_poster_options['enable_twitter_for'])) {
                                            $prevent_meta = $wpw_auto_poster_options['enable_twitter_for'];
                                        } else {
                                            $prevent_meta = '';
                                        }

                                        $prevent_meta = is_array($prevent_meta) ? $prevent_meta : array();

                                        if( !empty( $wpw_auto_poster_options['tw_post_type_tags'] ) ) {
                                            $tw_post_type_tags = $wpw_auto_poster_options['tw_post_type_tags'];
                                        } else {
                                            $tw_post_type_tags = array();
                                        }

                                        $static_post_type_arr = wpw_auto_poster_get_static_tag_taxonomy();

                                        if( !empty( $wpw_auto_poster_options['tw_post_type_cats'] ) ) {
                                            $tw_post_type_cats = $wpw_auto_poster_options['tw_post_type_cats'];
                                        } else {
                                            $tw_post_type_cats = array();
                                        }

                                        // Get saved categories for twitter to exclude from posting
                                            if( !empty( $wpw_auto_poster_options['tw_exclude_cats'] ) ) {
                                                $tw_exclude_cats = $wpw_auto_poster_options['tw_exclude_cats'];
                                            }

                                        foreach ($all_types as $type) {

                                            if (!is_object($type))
                                                continue;
                                            $label = @$type->labels->name ? $type->labels->name : $type->name;
                                            if ($label == 'Media' || $label == 'media')
                                                continue; // skip media
                                            $selected = ( in_array($type->name, $prevent_meta) ) ? 'checked="checked"' : '';
                                            ?>

                                            <li class="wpw-auto-poster-prevent-types">
                                                <input type="checkbox" id="wpw_auto_posting_twitter_prevent_<?php echo $type->name; ?>" name="wpw_auto_poster_options[enable_twitter_for][]" value="<?php echo $type->name; ?>" <?php echo $selected; ?>/>

                                                <label for="wpw_auto_posting_twitter_prevent_<?php echo $type->name; ?>"><?php echo $label; ?></label>
                                            </li>

<?php } ?>
                                    </ul>
                                    <p><small><?php _e('Check each of the post types that you want to post automatically to Twitter when they get published.', 'wpwautoposter'); ?></small></p>  
                                </td>
                            </tr>

                            <tr valign="top">
                                    <th scope="row">
                                        <label for="wpw_auto_poster_options[tw_post_type_tags][]"><?php _e( 'Select Tags:', 'wpwautoposter' ); ?></label> 
                                    </th>
                                    <td>
                                        <select name="wpw_auto_poster_options[tw_post_type_tags][]" id="wpw_auto_poster_options[tw_post_type_tags]" class="tw_post_type_tags wpw-auto-poster-cats-tags-select" multiple="multiple">
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
                                                            if(isset($tw_post_type_tags[$type->name]) && !empty($tw_post_type_tags[$type->name])) {
                                                                $selected = ( in_array( $taxonomy->name, $tw_post_type_tags[$type->name] ) ) ? 'selected="selected"' : '';
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
                                        <label for="wpw_auto_poster_options[tw_post_type_cats][]"><?php _e( 'Select Categories:', 'wpwautoposter' ); ?></label> 
                                    </th>
                                    <td>
                                        <select name="wpw_auto_poster_options[tw_post_type_cats][]" id="wpw_auto_poster_options[tw_post_type_cats]" class="tw_post_type_cats wpw-auto-poster-cats-tags-select" multiple="multiple">
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
                                                            if(isset($tw_post_type_cats[$type->name]) && !empty($tw_post_type_cats[$type->name])) {
                                                                $selected = ( in_array( $taxonomy->name, $tw_post_type_cats[$type->name]) ) ? 'selected="selected"' : '';
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
                                        <label for="wpw_auto_poster_options[tw_exclude_cats][]"><?php _e( 'Exclude Specific Categories:', 'wpwautoposter' ); ?></label> 
                                    </th>
                                    <td>
                                        <select name="wpw_auto_poster_options[tw_exclude_cats][]" id="wpw_auto_poster_options[tw_exclude_cats]" class="tw_exclude_cats wpw-auto-poster-cats-exclude-select" multiple="multiple">
                                            
                                            <?php

                                                $post_type_categories = wpw_auto_poster_get_all_categories();

                                                if(!empty($post_type_categories)) {

                                                    foreach($post_type_categories as $post_type => $post_data){

                                                        echo '<optgroup label="'.$post_data['label'].'">';

                                                        if(isset($post_data['categories']) && !empty($post_data['categories']) && is_array($post_data['categories'])){
                                                            
                                                            foreach($post_data['categories'] as $cat_slug => $cat_name){

                                                            	$selected = '';
                                                                if( !empty($tw_exclude_cats[$post_type] ) ) {
                                                                    $selected = ( in_array( $cat_slug, $tw_exclude_cats[$post_type] ) ) ? 'selected="selected"' : '';
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
                                    <label for="wpw_auto_poster_options[tw_url_shortener]"><?php _e('URL Shortener:', 'wpwautoposter'); ?></label> 
                                </th>
                                <td>
                                    <select name="wpw_auto_poster_options[tw_url_shortener]" id="wpw_auto_poster_options[tw_url_shortener]" class="tw_url_shortener" data-content='tw'>
                                        <?php

                                        foreach ($tw_url_shortener as $key => $option) {
                                            ?>
                                            <option value="<?php echo $model->wpw_auto_poster_escape_attr($key); ?>" <?php selected($wpw_auto_poster_options['tw_url_shortener'], $key); ?>>
                                            <?php esc_html_e($option); ?>
                                            </option>
                                            <?php
                                        }
                                        ?> 														
                                    </select>
                                    <p><small><?php _e('Long URLs will automatically be shortened using the specified URL shortener.', 'wpwautoposter'); ?></small></p>
                                </td>
                            </tr>

                            <tr id="row-tw-wp-pretty-url" valign="top" style="<?php print $tw_wp_pretty_url_css;?>">
                                <th scope="row">
                                    <label for="wpw_auto_poster_options[tw_wp_pretty_url]"><?php _e( 'Pretty permalink URL:', 'wpwautoposter' ); ?></label> 
                                </th>
                                <td>
                                    <input type="checkbox" name="wpw_auto_poster_options[tw_wp_pretty_url]" id="wpw_auto_poster_options[tw_wp_pretty_url]" class="tw_wp_pretty_url" data-content='tw' value="yes" <?php print $tw_wp_pretty_url;?>>
                                    <p><small><?php _e( 'Check this box if you want to use pretty permalink. i.e. http://example.com/test-post/. (Not Recommnended).', 'wpwautoposter' ); ?></small></p>
                                </td>
                            </tr>

                            <?php
                            if ($wpw_auto_poster_options['tw_url_shortener'] == 'bitly') {
                                $class = '';
                            } else {
                                $class = ' style="display:none;"';
                            }

                            if ($wpw_auto_poster_options['tw_url_shortener'] == 'shorte.st') {
                                $shortest_class = '';
                            } else {
                                $shortest_class = ' style="display:none;"';
                            }
                            
                         	if ($wpw_auto_poster_options['tw_url_shortener'] == 'google_shortner') {
                                $google_shortner_cls = '';
                            } else {
                                $google_shortner_cls = ' style="display:none;"';
                            }
                            ?>

                            <tr valign="top" class="tw_setting_input_bitly"<?php echo $class; ?>>
                                <th scope="row">
                                    <label for="wpw_auto_poster_options[tw_bitly_access_token]"><?php _e('Bit.ly Access Token', 'wpwautoposter'); ?> </label>
                                </th>
                                <td>
                                    <input type="text" name="wpw_auto_poster_options[tw_bitly_access_token]" id="wpw_auto_poster_options[tw_bitly_access_token]" value="<?php echo $model->wpw_auto_poster_escape_attr($wpw_auto_poster_options['tw_bitly_access_token']); ?>" class="large-text">
                                </td>
                            </tr>

                            <tr valign="top" class="tw_setting_input_shortest"<?php echo $shortest_class; ?>>
                                <th scope="row">
                                    <label for="wpw_auto_poster_options[tw_shortest_api_token]"><?php _e('Shorte.st API Token', 'wpwautoposter'); ?> </label>
                                </th>
                                <td>
                                    <input type="text" name="wpw_auto_poster_options[tw_shortest_api_token]" id="wpw_auto_poster_options[tw_shortest_api_token]" value="<?php echo $model->wpw_auto_poster_escape_attr($wpw_auto_poster_options['tw_shortest_api_token']); ?>" class="large-text">
                                </td>
                            </tr>
                            
                         	<tr valign="top" class="tw_setting_input_g_shortner" <?php echo $google_shortner_cls; ?>>
                                <th scope="row">
                                    <label for="wpw_auto_poster_options[tw_google_short_api_key]"><?php _e('Google API Key', 'wpwautoposter'); ?> </label>
                                </th>
                                <td>
                                    <input type="text" name="wpw_auto_poster_options[tw_google_short_api_key]" id="wpw_auto_poster_options[tw_google_short_api_key]" value="<?php echo !empty($wpw_auto_poster_options["tw_google_short_api_key"])?($model->wpw_auto_poster_escape_attr($wpw_auto_poster_options["tw_google_short_api_key"])):''; ?>" class="large-text">
                                    <p><small><?php _e( 'Enter Google Plus API Key. You need to enable <b>URL Shortener API</b>  in google plus application', 'wpwautoposter' ); ?></small></p>
                                </td>
                            </tr>

                            <?php
                            echo apply_filters(
                                    'wpweb_fb_settings_submit_button', '<tr valign="top">
																<td colspan="2">
																	<input type="submit" value="' . __('Save Changes', 'wpwautoposter') . '" id="wpw_auto_poster_set_submit" name="wpw_auto_poster_set_submit" class="button-primary">
																</td>
															</tr>'
                            );
                            ?>
                        </tbody>
                    </table>

                </div><!-- .inside -->

            </div><!-- #twitter_general -->
        </div><!-- .meta-box-sortables ui-sortable -->
    </div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-twitter-general -->
<!-- end of the twitter general settings meta box -->

<!-- beginning of the twitter api settings meta box -->
<div id="wpw-auto-poster-twitter-api" class="post-box-container">
    <div class="metabox-holder">	
        <div class="meta-box-sortables ui-sortable">
            <div id="twitter_api" class="postbox">	
                <div class="handlediv" title="<?php _e('Click to toggle', 'wpwautoposter'); ?>"><br /></div>

                <h3 class="hndle">
                    <span style='vertical-align: top;'><?php _e('Twitter API Settings', 'wpwautoposter'); ?></span>
                </h3>

                <div class="inside">

                    <table class="form-table wpw-auto-poster-twitter-settings">											
                        <tbody>			
                            <tr valign="top">

                                <td scope="row" valign="top" class="wpw-auto-poster-app-label">
                                    <strong><label><?php _e('Twitter Application:', 'wpwautoposter'); ?></label></strong>
                                </td>
                                <td colspan="3">
                                    <p><?php _e('Before you can start publishing your content to Twitter you need to create a Twitter Application.', 'wpwautoposter'); ?></p>
                                    <p><?php printf(__('You can get a step by step tutorial on how to create a Twitter Application on our %sDocumentation%s.', 'wpwautoposter'), '<a href="http://wpweb.co.in/documents/social-network-integration/twitter/" target="_blank">', '</a>'); ?></p> 
                                </td>
                            </tr>	

                            <tr valign="top">
                                <td scope="row">
                                    <strong><label for="wpw_auto_poster_options[twitter_consumer_key]"><?php _e('API Key', 'wpwautoposter'); ?></label></strong>
                                </td>
                                <td scope="row">
                                    <strong><label for="wpw_auto_poster_options[twitter_consumer_secret]"><?php _e('API Secret', 'wpwautoposter'); ?></label></strong>
                                </td>
                                <td scope="row">
                                    <strong><label for="wpw_auto_poster_options[twitter_oauth_token]"><?php _e('Access Token', 'wpwautoposter'); ?></label></strong>
                                </td>
                                <td scope="row">
                                    <strong><label for="wpw_auto_poster_options[twitter_oauth_secret]"><?php _e('Access Token Secret', 'wpwautoposter'); ?></label></strong>
                                </td>
                            </tr>

                            <?php
                            if (!empty($twitter_keys)) {

                                foreach ($twitter_keys as $twitter_key => $twitter_value) {

                                    // dont disply delete link for first row
                                    $twitter_delete_class = empty($twitter_key) ? '' : ' wpw-auto-poster-display-inline ';
                                    ?>

                                    <tr valign="top" class="wpw-auto-poster-twitter-account-details" data-row-id="<?php echo $twitter_key; ?>">
                                        <td width="25%">
                                            <input type="text" name="wpw_auto_poster_options[twitter_keys][<?php echo $twitter_key; ?>][consumer_key]" class="wpw-auto-poster-twitter-consumer-key" value="<?php echo $model->wpw_auto_poster_escape_attr($twitter_keys[$twitter_key]['consumer_key']); ?>" class="large-text">
                                            <p><small><?php _e('Enter Twitter Consumer Key.', 'wpwautoposter'); ?></small></p>  
                                        </td>
                                        <td width="25%">
                                            <input type="text" name="wpw_auto_poster_options[twitter_keys][<?php echo $twitter_key; ?>][consumer_secret]" class="wpw-auto-poster-twitter-consumer-secret" value="<?php echo $model->wpw_auto_poster_escape_attr($twitter_keys[$twitter_key]['consumer_secret']); ?>" class="large-text">
                                            <p><small><?php _e('Enter Twitter Consumer Secret.', 'wpwautoposter'); ?></small></p>  
                                        </td>
                                        <td width="25%">
                                            <input type="text" name="wpw_auto_poster_options[twitter_keys][<?php echo $twitter_key; ?>][oauth_token]" class="wpw-auto-poster-twitter-oauth-token" value="<?php echo $model->wpw_auto_poster_escape_attr($twitter_keys[$twitter_key]['oauth_token']); ?>" class="large-text">
                                            <p><small><?php _e('Enter Twitter Access Token.', 'wpwautoposter'); ?></small></p>  
                                        </td>
                                        <td width="25%">
                                            <input type="text" name="wpw_auto_poster_options[twitter_keys][<?php echo $twitter_key; ?>][oauth_secret]" class="wpw-auto-poster-twitter-oauth-secret" value="<?php echo $model->wpw_auto_poster_escape_attr($twitter_keys[$twitter_key]['oauth_secret']); ?>" class="large-text">
                                            <a href="javascript:void(0);" class="wpw-auto-poster-delete-account wpw-auto-poster-twitter-remove <?php echo $twitter_delete_class; ?>" title="<?php _e('Delete', 'wpwautoposter'); ?>"><img src="<?php echo WPW_AUTO_POSTER_META_URL; ?>/images/delete-16.png" alt="<?php _e('Delete', 'wpwautoposter'); ?>"/></a>
                                            <p><small><?php _e('Enter Twitter Access Token Secret.', 'wpwautoposter'); ?></small></p>  
                                        </td>
                                    </tr>

                                    <?php
                                }
                            } else {
                                ?>

                                <tr valign="top" class="wpw-auto-poster-twitter-account-details" data-row-id="0">
                                    <td width="25%">
                                        <input type="text" name="wpw_auto_poster_options[twitter_keys][0][consumer_key]" class="wpw-auto-poster-twitter-consumer-key" value="" class="large-text">
                                        <p><small><?php _e('Enter Twitter Consumer Key.', 'wpwautoposter'); ?></small></p>  
                                    </td>
                                    <td width="25%">
                                        <input type="text" name="wpw_auto_poster_options[twitter_keys][0][consumer_secret]" class="wpw-auto-poster-twitter-consumer-secret" value="" class="large-text">
                                        <p><small><?php _e('Enter Twitter Consumer Secret.', 'wpwautoposter'); ?></small></p>  
                                    </td>
                                    <td width="25%">
                                        <input type="text" name="wpw_auto_poster_options[twitter_keys][0][oauth_token]" class="wpw-auto-poster-twitter-oauth-token" value="" class="large-text">
                                        <p><small><?php _e('Enter Twitter Access Token.', 'wpwautoposter'); ?></small></p>  
                                    </td>
                                    <td width="25%">
                                        <input type="text" name="wpw_auto_poster_options[twitter_keys][0][oauth_secret]" class="wpw-auto-poster-twitter-oauth-secret" value="" class="large-text">
                                        <a href="javascript:void(0);" class="wpw-auto-poster-delete-account wpw-auto-poster-twitter-remove" title="<?php _e('Delete', 'wpwautoposter'); ?>"><img src="<?php echo WPW_AUTO_POSTER_META_URL; ?>/images/delete-16.png" alt="<?php _e('Delete', 'wpwautoposter'); ?>"/></a>
                                        <p><small><?php _e('Enter Twitter Access Token Secret.', 'wpwautoposter'); ?></small></p>  
                                    </td>
                                </tr>

                            <?php } ?>		
                            <tr>
                                <td colspan="4">
                                    <a class='wpw-auto-poster-add-more-account button' href='javascript:void(0);'><?php _e('Add more', 'wpwautoposter'); ?></a>
                                </td>
                            </tr>

                            <?php
                            echo apply_filters(
                                    'wpweb_fb_settings_submit_button', '<tr valign="top">
																<td colspan="4">
																	<input type="submit" value="' . __('Save Changes', 'wpwautoposter') . '" id="wpw_auto_poster_set_submit" name="wpw_auto_poster_set_submit" class="button-primary">
																</td>
															</tr>'
                            );
                            ?>
                        </tbody>
                    </table>

                </div><!-- .inside -->

            </div><!-- #twitter_api -->
        </div><!-- .meta-box-sortables ui-sortable -->
    </div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-twitter-api -->
<!-- end of the twitter api settings meta box -->

<!-- beginning of the twitter template settings meta box -->
<div id="wpw-auto-poster-twitter-template" class="post-box-container">
    <div class="metabox-holder">	
        <div class="meta-box-sortables ui-sortable">
            <div id="twitter_template" class="postbox">	
                <div class="handlediv" title="<?php _e('Click to toggle', 'wpwautoposter'); ?>"><br /></div>

                <h3 class="hndle">
                    <span style='vertical-align: top;'><?php _e('Autopost to Twitter', 'wpwautoposter'); ?></span>
                </h3>

                <div class="inside">

                    <table class="form-table">											
                        <tbody>		

                            <tr valign="top"> 
                                <th scope="row">
                                    <label for="wpw_auto_poster_options[prevent_post_tw_metabox]"><?php _e('Do not allow individual posts to Twitter:', 'wpwautoposter'); ?></label>
                                </th>									
                                <td>
                                    <input name="wpw_auto_poster_options[prevent_post_tw_metabox]" id="wpw_auto_poster_options[prevent_post_tw_metabox]" type="checkbox" value="1" <?php if (isset($wpw_auto_poster_options['prevent_post_tw_metabox'])) {
                                checked('1', $wpw_auto_poster_options['prevent_post_tw_metabox']);
                            } ?> />
                                    <p><small><?php _e('If you check this box, then it will hide meta settings for twitter from individual posts.', 'wpwautoposter'); ?></small></p>
                                </td>	
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label><?php _e('Map WordPress types to Twitter locations:', 'wpwautoposter'); ?></label>
                                </th>
                                <td>
                                    <?php
                                    $types = get_post_types(array('public' => true), 'objects');
                                    $types = is_array($types) ? $types : array();

                                    //Get twitter account details
                                    $tw_account_details = get_option('wpw_auto_poster_tw_account_details', array());

                                    foreach ($types as $type) {

                                        if (!is_object($type))
                                            continue;

                                        if (isset($wpw_auto_poster_options['tw_type_' . $type->name . '_user'])) {
                                            $wpw_auto_poster_tw_type_user = $wpw_auto_poster_options['tw_type_' . $type->name . '_user'];
                                        } else {
                                            $wpw_auto_poster_tw_type_user = '';
                                        }

                                        $wpw_auto_poster_tw_type_user = (array) $wpw_auto_poster_tw_type_user;

                                        $label = @$type->labels->name ? $type->labels->name : $type->name;

                                        if ($label == 'Media' || $label == 'media')
                                            continue; // skip media
                                        ?>		
                                        <div class="wpw-auto-poster-fb-types-wrap">
                                            <div class="wpw-auto-poster-tw-types-label">
                                                <?php
                                                _e('Autopost', 'wpwautoposter');
                                                echo ' ' . $label;
                                                _e(' to Twitter of this user(s)', 'wpwautoposter');
                                                ?>
                                            </div><!--.wpw-auto-poster-tw-types-label-->
                                            <div class="wpw-auto-poster-tw-users-acc">
                                                <select name="wpw_auto_poster_options[<?php echo 'tw_type_' . $type->name . '_user'; ?>][]" id="wpw_auto_poster_options[<?php echo 'tw_type_' . $type->name . '_user'; ?>][]" multiple="multiple">
                                                    <?php
                                                    if (!empty($tw_account_details) && count($tw_account_details) > 0) {
                                                        foreach ($tw_account_details as $tw_key => $tw_value) {
                                                            echo '<option value="' . $tw_key . '" ' . selected(in_array($tw_key, $wpw_auto_poster_tw_type_user), true, false) . '>' . $tw_value . '</option>';
                                                        }
                                                    } //end if to check there is user connected to twitter or not
                                                    ?>
                                                </select>
                                            </div><!--.wpw-auto-poster-tw-users-acc-->
                                        </div><!--.wpw-auto-poster-fb-types-wrap-->
    <?php
} //end foreach
?>
                                </td>
                            </tr>
                            
                            <tr valign="top">
                                <th scope="row">
                                    <label for="wpw_auto_poster_options[tw_disable_image_tweet]"><?php _e('Disable Image posting', 'wpwautoposter'); ?></label>
                                </th>
                                <td>
                                    <input name="wpw_auto_poster_options[tw_disable_image_tweet]" id="wpw_auto_poster_options[tw_disable_image_tweet]" type="checkbox" value="1" <?php if (isset($wpw_auto_poster_options['tw_disable_image_tweet'])) {
                                checked('1', $wpw_auto_poster_options['tw_disable_image_tweet']);
                            } ?> />
                                    <p><small><?php _e('Check this box, if you want to disable image posting for twitter.', 'wpwautoposter'); ?></small></p>
                                </td>
                            </tr>

                            <tr valign="top" id="wpw_sap_tw_tweet_img">
                                <th scope="row">
                                    <label for="wpw_auto_poster_options[tw_tweet_img]"><?php _e('Post Image:', 'wpwautoposter'); ?></label>
                                </th>
                                <td>
                                    <input type="text" value="<?php echo $model->wpw_auto_poster_escape_attr($wpw_auto_poster_options['tw_tweet_img']); ?>" name="wpw_auto_poster_options[tw_tweet_img]" id="wpw_auto_poster_options_tw_tweet_img" class="large-text wpw-auto-poster-img-field">
                                    <input type="button" class="button-secondary wpw-auto-poster-uploader-button" name="wpw-auto-poster-uploader" value="<?php _e('Add Image', 'wpwautoposter'); ?>" />
                                    <p><small><?php _e('Here you can upload a default image which will be used for Tweets.', 'wpwautoposter'); ?></small></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label for="wpw_auto_poster_options[tw_tweet_template]"><?php _e('Message Template', 'wpwautoposter'); ?></label>
                                </th>
                                <td>
                                    <select name="wpw_auto_poster_options[tw_tweet_template]" id="wpw_auto_poster_options[tw_tweet_template]" class="tw_tweet_template">
                                        <?php
                                        $select_template = array("title_link" => "[title] - [link]", "title_fullauthor_link" => "[title] by [full_author] - [link]", "title_nickname_link" => "[title] by @[nickname_author] - [link]", "post_type_title_link" => "New [post_type]: [title] - [link]", "post_type_title_fullauthor_link" => "New [post_type]: [title] by [full_author] - [link]", "post_type_title_nickname_link" => "New [post_type]: [title] by [nickname_author] - [link]", "custom" => "Custom");

                                        foreach ($select_template as $key => $option) {
                                            ?>
                                            <option value="<?php echo $model->wpw_auto_poster_escape_attr($key); ?>" <?php selected($wpw_auto_poster_options['tw_tweet_template'], $key); ?>>
                                            <?php esc_html_e($option); ?>
                                            </option>
    <?php
}
?> 														
                                    </select>
                                    <p><small style="line-height: 20px;"><?php _e('Choose the template you want to use to get your content published on twitter. You can customize this content for your needs. There are also several template tags you can use to customize the content. The template tags will then be replaced with the related information.', 'wpwautoposter'); ?></small></p>
                                </td>
                            </tr>

                            <?php
                            if ($wpw_auto_poster_options['tw_tweet_template'] == 'custom') {
                                $showing = '';
                            } else {
                                $showing = ' style="display:none;"';
                            }
                            ?>

                            <tr valign="top" id="custom_template"<?php echo $showing; ?>>
                                <th scope="row">
                                    <label for="wpw_auto_poster_options[tw_custom_tweet_template]"><?php _e('Custom Message', 'wpwautoposter'); ?></label>
                                </th>
                                <td>
                                	<textarea class="large-text" name="wpw_auto_poster_options[tw_custom_tweet_template]"><?php echo $model->wpw_auto_poster_escape_attr($wpw_auto_poster_options['tw_custom_tweet_template']); ?></textarea>
                                	<p><small style="line-height: 20px;"><?php _e( 'Here you can enter custom tweet template which will be used for the tweet. Leave it empty to use the post level tweet. You can use following template tags within the tweet template:', 'wpwautoposter' ); ?>
                                	<?php 
										$tw_template_str = '<br /><code>{title}</code> - ' . __('displays the default post title,', 'wpwautoposter') .
										 '<br /><code>{link}</code> - ' . __('displays the default post link,', 'wpwautoposter') .
										 '<br /><code>{full_author}</code> - ' . __('displays the full author name,', 'wpwautoposter') .
										 '<br /><code>{nickname_author}</code> - ' . __('displays the nickname of author,', 'wpwautoposter') .
										 '<br /><code>{post_type}</code> - ' . __(' displays the post type,', 'wpwautoposter') .
										 '<br /><code>{excerpt}</code> - ' . __('displays the post excerpt.', 'wpwautoposter').
										 '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
							            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
                                        '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
                                        '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter');
							            print $tw_template_str;
							            ?>
                                    </small></p>
                                </td>
                            </tr>                            

                            <?php
                            echo apply_filters(
                                    'wpweb_fb_settings_submit_button', '<tr valign="top">
																<td colspan="2">
																	<input type="submit" value="' . __('Save Changes', 'wpwautoposter') . '" id="wpw_auto_poster_set_submit" name="wpw_auto_poster_set_submit" class="button-primary">
																</td>
															</tr>'
                            );
                            ?>
                        </tbody>
                    </table>

                </div><!-- .inside -->

            </div><!-- #twitter_api -->
        </div><!-- .meta-box-sortables ui-sortable -->
    </div><!-- .metabox-holder -->
</div><!-- #wpw-auto-poster-twitter-template -->
<!-- end of the twitter template settings meta box -->