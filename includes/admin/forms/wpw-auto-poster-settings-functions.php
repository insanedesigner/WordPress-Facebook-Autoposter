<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if( !function_exists( 'wpw_auto_poster_add_settings_page' ) ) {

    /**
     * Add Top Level Menu Page
     *
     * Runs when the admin_menu hook fires and adds a new
     * top level admin page and menu item.
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_add_settings_page() {

        global $post, $wpw_auto_poster_scripts;

        // plugin settings page
        $wpw_auto_poster_admin = add_menu_page(__('Social Auto Poster', 'wpwautoposter'), __('Social Auto Poster', 'wpwautoposter'), wpwautoposterlevel, 'wpw-auto-poster-settings', '', WPW_AUTO_POSTER_IMG_URL . '/wpw-auto-poster-icon.png');

        $wpw_auto_poster_admin = add_submenu_page('wpw-auto-poster-settings', __('Social Auto Poster Settings', 'wpwautoposter'), __('Settings', 'wpwautoposter'), wpwautoposterlevel, 'wpw-auto-poster-settings', 'wpw_auto_poster_settings_page');

        $wpw_auto_poster_reposter = add_submenu_page('wpw-auto-poster-settings', __('Reposter Settings', 'wpwautoposter'), __('Reposter', 'wpwautoposter'), wpwautoposterlevel, 'wpw-auto-poster-reposter', 'wpw_auto_poster_reposter_settings_page' );

        $wpw_auto_poster_posted_logs = add_submenu_page('wpw-auto-poster-settings', __('Social Auto Poster Posting Logs', 'wpwautoposter'), __('Social Posting Logs', 'wpwautoposter'), wpwautoposterlevel, 'wpw-auto-poster-posted-logs', 'wpw_auto_poster_posted_logs_page');

        //Page for Manage post schedules
        $wpw_auto_poster_manage_schedules = add_submenu_page('wpw-auto-poster-settings', __('Manage Schedules', 'wpwautoposter'), __('Manage Schedules', 'wpwautoposter'), wpwautoposterlevel, 'wpw-auto-poster-manage-schedules', 'wpw_auto_poster_manage_schedules_page');

        $wpw_auto_poster_posted_logs = add_submenu_page('wpw-auto-poster-settings', __('Social Auto Poster Debug Posting Logs', 'wpwautoposter'), __('Posting Debug Logs', 'wpwautoposter'), wpwautoposterlevel, 'wpw-auto-poster-posted-system-logs', 'wpw_auto_poster_posted_system_logs_page');

        add_action("admin_head-$wpw_auto_poster_admin", array($wpw_auto_poster_scripts, 'wpw_auto_poster_settings_page_load_scripts'));
    }
}

if( !function_exists( 'wpw_auto_poster_settings_page' ) ) {

    /**
     * Settings Page
     *
     * Renders the plugin settings page.
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_settings_page() {

        include_once( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-settings-hooks.php' );
        include_once( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-plugin-settings.php' );
    }
}

if( !function_exists( 'wpw_auto_poster_reposter_settings_page' ) ) {
    
    /**
     * Reposter Settings Page
     *
     * Renders the plugin settings page.
     *
     * @package Social Auto Poster
     * @since 2.6.9
     */
    function wpw_auto_poster_reposter_settings_page() {

        include_once( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-reposter-settings-hooks.php' );
        include_once( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-plugin-reposter-settings.php' );
    }

}


if( !function_exists( 'wpw_auto_poster_posted_logs_page' ) ) {

    /**
     * Posted Logs List
     *
     * Renders the posted logs list page.
     *
     * @package Social Auto Poster
     * @since 1.4.0
     */
    function wpw_auto_poster_posted_logs_page() {
    ?>
        <div class="wrap">
             <!-- wpweb logo -->
            <img src="<?php echo WPW_AUTO_POSTER_IMG_URL . '/wpw-auto-poster-logo.png'; ?>" class="wpw-auto-poster-logo" alt="<?php _e( 'Logo', 'wpwautoposter' );?>" />
            <h2><?php _e( 'Social Posting Logs', 'wpwautoposter' ); ?></h2>

            <div class="content">
                <h2 class="nav-tab-wrapper wpw-auto-poster-h2">
                    <a class="nav-tab nav-tab-active" href="#wpw-auto-poster-tab-logs" attr-tab="sap-logs"><?php _e( 'Posting Logs', 'wpwautoposter'); ?></a>
                    <a class="nav-tab" href="#wpw-auto-poster-tab-reports" attr-tab="sap-reports"><?php _e( 'Posting Reports', 'wpwautoposter'); ?></a>
                </h2>
                <div class="wpw-auto-poster-content">
                    <div class="wpw-auto-poster-tab-content" id="wpw-auto-poster-tab-logs" style="display:block">
                        <?php
                        include( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-posted-logs-list.php' ); ?>
                    </div>
                    <div class="wpw-auto-poster-tab-content" id="wpw-auto-poster-tab-reports">
                        <?php
                        include( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-posted-logs-reports.php' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

if( !function_exists( 'wpw_auto_poster_posted_system_logs_page' ) ) {

    /**
     * Posted Logs List
     *
     * Renders the posted logs list page.
     *
     * @package Social Auto Poster
     * @since 1.4.0
     */
    function wpw_auto_poster_posted_system_logs_page() {
        include( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-posted-system-logs.php' ); 
    }
}

if( !function_exists( 'wpw_auto_poster_manage_schedules_page' ) ) {

    /**
     * Post Scheduling
     *
     * Renders the manage posts schedule list page.
     *
     * @package Social Auto Poster
     * @since 1.4.0
     */
    function wpw_auto_poster_manage_schedules_page() {

        include( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-manage-schedules-list.php' );
    }
}

if( !function_exists( 'wpw_auto_poster_validate_options' ) ) {

    /**
     * Validation/Sanitization
     *
     * Sanitize and validate input fields.
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_validate_options($input) {

        global $wpw_auto_poster_options, $wpw_auto_poster_model, $wpw_auto_poster_fb_posting, $wpw_auto_poster_tw_posting, $wpw_auto_poster_li_posting, $wpw_auto_poster_ba_posting,
        $wpw_auto_poster_tb_posting, $wpw_auto_poster_ins_posting, $wpw_auto_poster_message_stack;

        $social_types_arr = $wpw_auto_poster_model->wpw_auto_poster_get_social_type_data();

        /****  Category and Tag handling start ****/
        foreach($social_types_arr as $prefix => $value) {

            if( !empty( $input['enable_'.$value.'_for'] ) ) {
                $prevent_meta = $input['enable_'.$value.'_for'];
            }

            // Custom post type tag taxonomy code
            if(!empty($input[$prefix.'_post_type_tags'])) {

                $post_type_tags = $input[$prefix.'_post_type_tags'];

                if(!empty($prevent_meta)) {

                    $wpw_auto_poster_tags =  array();
                    foreach ($post_type_tags as $post_type_tag) {

                        $tagData = explode("|",$post_type_tag);
                        $post_type = $tagData[0];
                        $post_tag = $tagData[1];
                        if(in_array( $post_type, $prevent_meta )){
                            $wpw_auto_poster_tags[$post_type][] = $post_tag;
                        }
                    }
                    $input[$prefix.'_post_type_tags'] = $wpw_auto_poster_tags;
                }
            }

            // Custom post type category taxonomy code
            if(!empty($input[$prefix.'_post_type_cats'])) {

                $post_type_cats = $input[$prefix.'_post_type_cats'];

                if(!empty($prevent_meta)) {

                    $wpw_auto_poster_cats =  array();
                    foreach ($post_type_cats as $post_type_cat) {

                        $tagData = explode("|",$post_type_cat);
                        $post_type = $tagData[0];
                        $post_cat = $tagData[1];
                        if(in_array( $post_type, $prevent_meta )){
                            $wpw_auto_poster_cats[$post_type][] = $post_cat;
                        }
                    }
                    $input[$prefix.'_post_type_cats'] = $wpw_auto_poster_cats;
                }
            }
        }
        /****  Category and Tag handling end ****/

        /*** Excluding Category handling start ***/
        foreach($social_types_arr as $prefix => $value) {

            // Custom post type exclude category code
            if( !empty( $input[$prefix.'_exclude_cats']) ) {

                $post_type_exclude_cats         = $input[$prefix.'_exclude_cats'];
                $wpw_auto_poster_exclude_cats   =  array();

                foreach ( $post_type_exclude_cats as $post_type_exclude_cat ) {

                    $tagData    = explode("|",$post_type_exclude_cat);
                    $post_type  = $tagData[0]; // post type
                    $cat_slug   = $tagData[1]; // category slug

                    $wpw_auto_poster_exclude_cats[$post_type][] = $cat_slug;
                }

                $input[$prefix.'_exclude_cats'] = $wpw_auto_poster_exclude_cats;
            }

        }
        /*** Excluding Category handling end ***/

        //Facebook Settings Options
        $input['fb_bitly_access_token'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['fb_bitly_access_token']);
        $input['facebook_keys'] = isset($input['facebook_keys']) ? $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['facebook_keys']) : '';

        if( !empty( $input['custom_status_msg'] ) ) {
            $input['custom_status_msg'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['custom_status_msg']);
        }

        $input['fb_custom_img'] = ( isset( $input['fb_custom_img'] ) ) ? $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['fb_custom_img']) : '';

        if( $input['facebook_auth_options'] == 'graph' ) {
            
            // Get facebook account details
            if (!empty($input['facebook_keys'])) {

                $facebook_keys = $input['facebook_keys'];

                // Check difference of arrays
                $facebook_keys_old_data = $wpw_auto_poster_model->wpw_auto_poster_get_one_dim_array($wpw_auto_poster_options['facebook_keys']);
                $facebook_keys_new_data = $wpw_auto_poster_model->wpw_auto_poster_get_one_dim_array($facebook_keys);

                $facebook_keys_result = array_diff($facebook_keys_new_data, $facebook_keys_old_data);
                $facebook_keys_result_vise = array_diff($facebook_keys_old_data, $facebook_keys_new_data);

                // Check any one array is different then reindex all values so if any blank row set it will not consider it.
                if (!empty($facebook_keys_result) || !empty($facebook_keys_result_vise)) {

                    $new_fb_keys = array();
                    $fb_count_key = 0;
                    $wpw_auto_poster_facebook_keys = array();

                    foreach ($facebook_keys as $fb_key => $fb_value) {

                        $fb_app_id = trim($fb_value['app_id']);
                        $fb_app_secret = trim($fb_value['app_secret']);

                        if (!empty($fb_app_id) || !empty($fb_app_secret)) { // Check any one key is set as not empty
                            $wpw_auto_poster_facebook_keys[$fb_count_key]['app_id'] = $fb_app_id;
                            $wpw_auto_poster_facebook_keys[$fb_count_key]['app_secret'] = $fb_app_secret;

                            $fb_count_key++;
                        }

                        // Just taking fb app ids
                        if (!empty($fb_app_id) && !empty($fb_app_secret)) {
                            $new_fb_keys[] = $fb_app_id;
                        }
                    }
                    $input['facebook_keys'] = $wpw_auto_poster_facebook_keys;

                    /*                 * *** Reset facebook session data is app key or appid is deleted **** */
                    // Note : wpw_auto_poster_fb_reset_session() Function is called just to flush the session variable not options
                    // If data is not empty then check which existing key
                    $wpw_auto_poster_fb_sess_data = get_option('wpw_auto_poster_fb_sess_data');

                    // Getting facebook keys from the stored session data
                    $old_fb_keys = (!empty($wpw_auto_poster_fb_sess_data) && is_array($wpw_auto_poster_fb_sess_data) ) ? array_keys($wpw_auto_poster_fb_sess_data) : array();

                    // Getting difference between stored fb keys and setting fb keys
                    $diff_fb_keys = array_diff($old_fb_keys, $new_fb_keys);

                    if (!empty($diff_fb_keys)) {

                        $wpw_auto_poster_fb_posting->wpw_auto_poster_fb_reset_session(); // Flush session variable

                        foreach ($diff_fb_keys as $flush_app_key => $flush_app_data) {
                            // Removing app data from the stored fb session data
                            if (isset($wpw_auto_poster_fb_sess_data[$flush_app_data])) {
                                unset($wpw_auto_poster_fb_sess_data[$flush_app_data]);
                            }
                        }

                        // Updating stored fb session data
                        update_option('wpw_auto_poster_fb_sess_data', $wpw_auto_poster_fb_sess_data);
                    }
                    /*                 * *** Reset facebook session ends **** */
                }
                // end code for reindexing
            }
        } // end if selection method is graph

        //Instagram Settings Options
        $input['ins_bitly_access_token'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['ins_bitly_access_token']);
        $input['instagram_keys'] = isset($input['instagram_keys']) ? $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['instagram_keys']) : '';
        $input['ins_template'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['ins_template']);
        $input['ins_custom_img'] = ( isset( $input['ins_custom_img'] ) ) ? $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['ins_custom_img']) : '';

        // Get instagram account details
        if (!empty($input['instagram_keys'])) {

            $instagram_keys = $input['instagram_keys'];

            // Check difference of arrays
            $instagram_keys_old_data = $wpw_auto_poster_model->wpw_auto_poster_get_one_dim_array($wpw_auto_poster_options['instagram_keys']);
            $instagram_keys_new_data = $wpw_auto_poster_model->wpw_auto_poster_get_one_dim_array($instagram_keys);

            $instagram_keys_result = array_diff($instagram_keys_new_data, $instagram_keys_old_data);
            $instagram_keys_result_vise = array_diff($instagram_keys_old_data, $instagram_keys_new_data);

            // Check any one array is different then reindex all values so if any blank row set it will not consider it.
            if (!empty($instagram_keys_result) || !empty($instagram_keys_result_vise)) {

                $new_ins_keys = array();
                $ins_count_key = 0;
                $wpw_auto_poster_instagram_keys = array();

                foreach ($instagram_keys as $ins_key => $ins_value) {

                    if( !is_array( $ins_value ) )
                        continue;

                    $ins_username = trim($ins_value['username']);
                    $ins_password = trim($ins_value['password']);

                    if (!empty($ins_username) || !empty($ins_password)) { // Check any one key is set as not empty
                        $wpw_auto_poster_instagram_keys[$ins_count_key]['username'] = $ins_username;
                        $wpw_auto_poster_instagram_keys[$ins_count_key]['password'] = $ins_password;

                        $ins_count_key++;
                    }

                    // Just taking fb app ids
                    if (!empty($ins_username) && !empty($ins_password)) {
                        $new_ins_keys[] = $ins_username ."|".$ins_password;
                    }
                }
                $input['instagram_keys'] = $wpw_auto_poster_instagram_keys;

                //Update instagram acoount details
                update_option('wpw_auto_poster_ins_account_details', $new_ins_keys);
            }
            // end code for reindexing
        }

        //Twitter Settings Options
        $input['tw_tweet_img'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['tw_tweet_img']);
        $input['tw_bitly_access_token'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['tw_bitly_access_token']);
        $input['twitter_keys'] = isset($input['twitter_keys']) ? $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['twitter_keys']) : '';

        //Get twitter account details
        if (!empty($input['twitter_keys'])) {

            //Get twitter account details
            $tw_account_details = array();

            $twitter_keys = $input['twitter_keys'];

            //Check difference of arrays
            $twitter_keys_old_data = $wpw_auto_poster_model->wpw_auto_poster_get_one_dim_array($wpw_auto_poster_options['twitter_keys']);
            $twitter_keys_new_data = $wpw_auto_poster_model->wpw_auto_poster_get_one_dim_array($twitter_keys);

            $twitter_keys_result = array_diff($twitter_keys_new_data, $twitter_keys_old_data);
            $twitter_keys_result_vise = array_diff($twitter_keys_old_data, $twitter_keys_new_data);

            // Check any one array is different
            if (!empty($twitter_keys_result) || !empty($twitter_keys_result_vise)) {

                $tw_count_key = 0;
                $wpw_auto_poster_twitter_keys = array();
                foreach ($twitter_keys as $tw_key => $tw_value) {

                    $tw_consumer_key = trim($tw_value['consumer_key']);
                    $tw_consumer_secret = trim($tw_value['consumer_secret']);
                    $tw_auth_token = trim($tw_value['oauth_token']);
                    $tw_auth_token_secret = trim($tw_value['oauth_secret']);

                    if (!empty($tw_consumer_key) || !empty($tw_consumer_secret) || !empty($tw_auth_token) || !empty($tw_auth_token_secret)) { // Check any one key is set as not empty
                        $wpw_auto_poster_twitter_keys[$tw_count_key]['consumer_key'] = $tw_consumer_key;
                        $wpw_auto_poster_twitter_keys[$tw_count_key]['consumer_secret'] = $tw_consumer_secret;
                        $wpw_auto_poster_twitter_keys[$tw_count_key]['oauth_token'] = $tw_auth_token;
                        $wpw_auto_poster_twitter_keys[$tw_count_key]['oauth_secret'] = $tw_auth_token_secret;

                        $tw_count_key = $tw_count_key + 1;
                        $user_profile_data = $wpw_auto_poster_tw_posting->wpw_auto_poster_get_user_data($tw_consumer_key, $tw_consumer_secret, $tw_auth_token, $tw_auth_token_secret);
                        if (!empty($user_profile_data)) { // Check user data are not empty
                            if (isset($user_profile_data->name) && !empty($user_profile_data->name)) { // Check user name is not empty
                                $tw_account_details[$tw_count_key] = $user_profile_data->name;
                            }
                        }
                    }
                }

                $input['twitter_keys'] = $wpw_auto_poster_twitter_keys;

                //Update twitter acoount details
                update_option('wpw_auto_poster_tw_account_details', $tw_account_details);
                /*                 * ***** Code for selected category Twitter account ***** */

                // unset selected twitter account option for category
                $cat_selected_social_acc = array();
                $cat_selected_acc = get_option('wpw_auto_poster_category_posting_acct');
                $cat_selected_social_acc = (!empty($cat_selected_acc) ) ? $cat_selected_acc : $cat_selected_social_acc;

                if (!empty($cat_selected_social_acc)) {
                    foreach ($cat_selected_social_acc as $cat_id => $cat_social_acc) {
                        if (isset($cat_social_acc['tw'])) {
                            if (!empty($cat_social_acc['tw'])) {
                                $new_cat_stored_users = array_diff($cat_social_acc['tw'], $tw_account_details);
                                if (!empty($new_cat_stored_users)) {
                                    $cat_selected_acc[$cat_id]['tw'] = $new_cat_stored_users;
                                } else {
                                    unset($cat_selected_acc[$cat_id]['tw']);
                                }
                            } else {
                                unset($cat_selected_acc[$cat_id]['tw']);
                            }
                        }
                    }

                    // Update autoposter category FB posting account options
                    update_option('wpw_auto_poster_category_posting_acct', $cat_selected_acc);
                }
            }
        }

        //LinkedIn Settings Options
        $input['li_bitly_access_token'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['li_bitly_access_token']);
        //$input['linkedin_app_id'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['linkedin_app_id']);
        //$input['linkedin_app_secret'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['linkedin_app_secret']);
        $input['linkedin_keys'] = isset($input['linkedin_keys']) ? $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['linkedin_keys']) : '';
        $input['li_post_image'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['li_post_image']);

        //linkedin application id or secret blank or change then reset session
        /*if (( empty($input['linkedin_app_id']) || empty($input['linkedin_app_secret']) ) || ( $wpw_auto_poster_options['linkedin_app_id'] != $input['linkedin_app_id'] ) || ( $wpw_auto_poster_options['linkedin_app_secret'] != $input['linkedin_app_secret'] )) {
            //reset linkedin session data
            $wpw_auto_poster_li_posting->wpw_auto_poster_li_reset_session();
        }*/

        // Get linkedin account details
        if (!empty($input['linkedin_keys'])) {

            $linkedin_keys = $input['linkedin_keys'];

            // Check difference of arrays
            $linkedin_keys_old_data = $wpw_auto_poster_model->wpw_auto_poster_get_one_dim_array($wpw_auto_poster_options['linkedin_keys']);
            $linkedin_keys_keys_new_data = $wpw_auto_poster_model->wpw_auto_poster_get_one_dim_array($linkedin_keys);

            $linkedin_keys_result = array_diff($linkedin_keys_keys_new_data, $linkedin_keys_old_data);
            $linkedin_result_vise = array_diff($linkedin_keys_old_data, $linkedin_keys_keys_new_data);

            // Check any one array is different then reindex all values so if any blank row set it will not consider it.
            if (!empty($linkedin_keys_result) || !empty($linkedin_result_vise)) {

                $new_li_keys = array();
                $li_count_key = 0;
                $wpw_auto_poster_linkedin_keys = array();

                foreach ($linkedin_keys as $li_key => $li_value) {

                    $li_app_id = trim($li_value['app_id']);
                    $li_app_secret = trim($li_value['app_secret']);

                    if (!empty($li_app_id) || !empty($li_app_secret)) { // Check any one key is set as not empty
                        $wpw_auto_poster_linkedin_keys[$li_count_key]['app_id'] = $li_app_id;
                        $wpw_auto_poster_linkedin_keys[$li_count_key]['app_secret'] = $li_app_secret;

                        $li_count_key++;
                    }

                    // Just taking li app ids
                    if (!empty($li_app_id) && !empty($li_app_secret)) {
                        $new_li_keys[] = $li_app_id;
                    }
                }
                $input['linkedin_keys'] = $wpw_auto_poster_linkedin_keys;

                /***** Reset linkedin session data is app key or appid is deleted **** */
                // Note : wpw_auto_poster_li_reset_session() Function is called just to flush the session variable not options
                // If data is not empty then check which existing key
                $wpw_auto_poster_li_sess_data = get_option('wpw_auto_poster_li_sess_data');

                // Getting linkedin keys from the stored session data
                $old_li_keys = (!empty($wpw_auto_poster_li_sess_data) && is_array($wpw_auto_poster_li_sess_data) ) ? array_keys($wpw_auto_poster_li_sess_data) : array();

                // Getting difference between stored li keys and setting li keys
                $diff_li_keys = array_diff($old_li_keys, $new_li_keys);

                if (!empty($diff_li_keys)) {

                    $wpw_auto_poster_li_posting->wpw_auto_poster_li_reset_session(); // Flush session variable

                    foreach ($diff_li_keys as $flush_app_key => $flush_app_data) {
                        // Removing app data from the stored li session data
                        if (isset($wpw_auto_poster_li_sess_data[$flush_app_data])) {
                            unset($wpw_auto_poster_li_sess_data[$flush_app_data]);
                        }
                    }

                    // Updating stored li session data
                    update_option('wpw_auto_poster_li_sess_data', $wpw_auto_poster_li_sess_data);
                }
                /***** Reset linkedin session ends **** */
            }
            // end code for reindexing
        }

        //Tumblr Settings Options
        $input['tb_bitly_access_token'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['tb_bitly_access_token']);
        $input['tumblr_consumer_key'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['tumblr_consumer_key']);
        $input['tumblr_consumer_secret'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['tumblr_consumer_secret']);

        //tumblr consumer key or secret balnk or change then reset session
        if (( empty($input['tumblr_consumer_key']) || empty($input['tumblr_consumer_secret']) ) || ( $wpw_auto_poster_options['tumblr_consumer_key'] != $input['tumblr_consumer_key'] ) || ( $wpw_auto_poster_options['tumblr_consumer_secret'] != $input['tumblr_consumer_secret'] )) {
            //reset tumblr session data
            $wpw_auto_poster_tb_posting->wpw_auto_poster_tb_reset_session();
        }

        // BufferApp Settings Option
        $input['ba_bitly_access_token'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['ba_bitly_access_token']);
        $input['bufferapp_client_id'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['bufferapp_client_id']);
        $input['bufferapp_client_secret'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['bufferapp_client_secret']);
        $input['ba_post_img'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['ba_post_img']);

        //BufferApp client id or secret blank or change then reset session
        if (( empty($input['bufferapp_client_id']) || empty($input['bufferapp_client_secret']) ) || ( $wpw_auto_poster_options['bufferapp_client_id'] != $input['bufferapp_client_id'] ) || ( $wpw_auto_poster_options['bufferapp_client_secret'] != $input['bufferapp_client_secret'] )) {
            //reset bufferapp session data
            $wpw_auto_poster_ba_posting->wpw_auto_poster_ba_reset_session();
        }

        //Pinterest Settings Options
        $input['pin_bitly_access_token'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['pin_bitly_access_token']);
        $input['pinterest_keys'] = isset($input['pinterest_keys']) ? $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['pinterest_keys']) : '';
        $input['pin_custom_template'] = $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['pin_custom_template']);
        $input['pin_custom_img'] = ( isset( $input['pin_custom_img'] ) ) ? $wpw_auto_poster_model->wpw_auto_poster_stripslashes_deep($input['pin_custom_img']) : '';

        // Get pinterest account details
        if (!empty($input['pinterest_keys'])) {

            $pinterest_keys = $input['pinterest_keys'];

            // Check difference of arrays
            $pinterest_keys_old_data = $wpw_auto_poster_model->wpw_auto_poster_get_one_dim_array($wpw_auto_poster_options['pinterest_keys']);
            $pinterest_keys_new_data = $wpw_auto_poster_model->wpw_auto_poster_get_one_dim_array($pinterest_keys);

            $pinterest_keys_result = array_diff($pinterest_keys_new_data, $pinterest_keys_old_data);
            $pinterest_keys_result_vise = array_diff($pinterest_keys_old_data, $pinterest_keys_new_data);

            // Check any one array is different then reindex all values so if any blank row set it will not consider it.
            if (!empty($pinterest_keys_result) || !empty($pinterest_keys_result_vise)) {

                $new_pin_keys = array();
                $pin_count_key = 0;
                $wpw_auto_poster_pinterest_keys = array();

                foreach ($pinterest_keys as $pin_key => $pin_value) {

                    $pin_app_id = trim($pin_value['app_id']);
                    $pin_app_secret = trim($pin_value['app_secret']);

                    if (!empty($pin_app_id) || !empty($pin_app_secret)) { // Check any one key is set as not empty
                        $wpw_auto_poster_pinterest_keys[$pin_count_key]['app_id'] = $pin_app_id;
                        $wpw_auto_poster_pinterest_keys[$pin_count_key]['app_secret'] = $pin_app_secret;

                        $pin_count_key++;
                    }

                    // Just taking pin app ids
                    if (!empty($pin_app_id) && !empty($pin_app_secret)) {
                        $new_pin_keys[] = $pin_app_id;
                    }
                }
                $input['pinterest_keys'] = $wpw_auto_poster_pinterest_keys;

                /*                 * *** Reset pinterest session data is app key or appid is deleted **** */
                // Note : wpw_auto_poster_pin_reset_session() Function is called just to flush the session variable not options
                // If data is not empty then check which existing key
                $wpw_auto_poster_pin_sess_data = get_option('wpw_auto_poster_pin_sess_data');

                // Getting pinterest keys from the stored session data
                $old_pin_keys = (!empty($wpw_auto_poster_pin_sess_data) && is_array($wpw_auto_poster_pin_sess_data) ) ? array_keys($wpw_auto_poster_pin_sess_data) : array();

                // Getting difference between stored pinterest keys and setting pinterest keys
                $diff_pin_keys = array_diff($old_pin_keys, $new_pin_keys);

                if (!empty($diff_pin_keys)) {

                    $wpw_auto_poster_pin_posting->wpw_auto_poster_pin_reset_session(); // Flush session variable

                    foreach ($diff_pin_keys as $flush_app_key => $flush_app_data) {
                        // Removing app data from the stored pinterest session data
                        if (isset($wpw_auto_poster_pin_sess_data[$flush_app_data])) {
                            unset($wpw_auto_poster_pin_sess_data[$flush_app_data]);
                        }
                    }

                    // Updating stored pinterest session data
                    update_option('wpw_auto_poster_pin_sess_data', $wpw_auto_poster_pin_sess_data);
                }
                /*                 * *** Reset pinterest session ends **** */
            }
            // end code for reindexing
        }

        //set session to set tab selected in settings page
        $selectedtab = isset($input['selected_tab']) ? $input['selected_tab'] : '';
        $wpw_auto_poster_message_stack->add_session('poster-selected-tab', strtolower($selectedtab));

        // apply filters for validate settings
        $input = apply_filters('wpw_auto_poster_validate_settings', $input, $wpw_auto_poster_options);

        return $input;
    }
}

if( !function_exists( 'wpw_auto_poster_validate_reposter_options' ) ) {

    /**
     * Validation/Sanitization
     *
     * Sanitize and validate input fields.
     *
     * @package Social Auto Poster
     * @since 2.6.9
     */
    function wpw_auto_poster_validate_reposter_options($input) {

        global $wpw_auto_poster_reposter_options, $wpw_auto_poster_model, $wpw_auto_poster_message_stack;

        if( !empty( $input['schedule_wallpost_option'] ) ){
            $input['schedule_wallpost_option']['days'] = !empty( $input['schedule_wallpost_option']['days'] ) ? $input['schedule_wallpost_option']['days']:0;
            $input['schedule_wallpost_option']['hours'] = !empty( $input['schedule_wallpost_option']['hours'] ) ? $input['schedule_wallpost_option']['hours']:0;
            $input['schedule_wallpost_option']['minutes'] = !empty( $input['schedule_wallpost_option']['minutes'] ) ? $input['schedule_wallpost_option']['minutes']:0;

        }

        $social_types_arr = $wpw_auto_poster_model->wpw_auto_poster_get_social_type_data();

        /****  Category and Tag handling start ****/

        foreach($social_types_arr as $prefix => $value) {

            if( !empty( $input['enable_'.$value.'_for'] ) ) {
                $prevent_meta = $input['enable_'.$value.'_for'];
            }

            // Custom post type category taxonomy code
            if(!empty($input[$prefix.'_post_type_cats'])) {

                $post_type_cats = $input[$prefix.'_post_type_cats'];

                if(!empty($prevent_meta)) {

                    $wpw_auto_poster_cats =  array();
                    foreach ($post_type_cats as $post_type_cat) {

                        $tagData = explode("|",$post_type_cat);
                        $post_type = $tagData[0];
                        $post_cat = $tagData[1];
                        
                        if(in_array( $post_type, $prevent_meta )){
                            $wpw_auto_poster_cats[$post_type][] = $post_cat;
                        }
                    }
                    $input[$prefix.'_post_type_cats'] = $wpw_auto_poster_cats;
                }
            }
        }

        //set session to set tab selected in settings page
        $selectedtab = isset($input['selected_tab']) ? $input['selected_tab'] : '';
        $wpw_auto_poster_message_stack->add_session('poster-selected-tab', strtolower($selectedtab));
        
        // apply filters for validate settings
        $input = apply_filters('wpw_auto_poster_reposter_validate_settings', $input, $wpw_auto_poster_reposter_options );

        return $input;
    }
}

if( !function_exists( 'wpw_auto_poster_init' ) ) {

    /**
     * Register Settings
     *
     * Runs when the admin_init hook fires and registers
     * the plugin settings with the WordPress settings API.
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_init() {

        register_setting('wpw_auto_poster_plugin_options', 'wpw_auto_poster_options', 'wpw_auto_poster_validate_options');

        register_setting('wpw_auto_poster_plugin_reposter_options', 'wpw_auto_poster_reposter_options', 'wpw_auto_poster_validate_reposter_options' );
    }
}

/**
 * Settings Hooks
 *
 * The code for the plugins main settings hooks
 *
 * @package Social Auto Poster
 * @since 1.4.0
 */

/*********************** General Settings ***************************/

if( !function_exists( 'wpw_auto_poster_general_setting_tab' ) ) {

    /**
     * Display General Setting Tab
     * 
     * Handle to display general setting tab
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_general_setting_tab( $selected_tab ) {
        
        $selectedtab = !empty( $selected_tab ) && $selected_tab == 'general' ? ' nav-tab-active' : '';
        ?>
            <a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-general" attr-tab="general">
                <img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/wpw-auto-poster-icon.png" width="24" height="24" alt="gn" title="<?php _e( 'General', 'wpwautoposter' ); ?>" />
            </a>
        <?php
    }
}

if( !function_exists( 'wpw_auto_poster_general_setting_tab_content' ) ) {

    /**
     * Display General Setting Tab Content
     * 
     * Handle to display general setting tab content
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_general_setting_tab_content( $selected_tab ) {
    
        $selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'general' ? ' wpw-auto-poster-selected-tab' : '';
        ?>
            <div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-general"> 
                    
                <?php
            
                // General Settings
                include( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-general-settings.php' );
            
                ?>
            
            </div><!--#wpw-auto-poster-tab-general-->
        <?php
    }
}

/*********************** Facebook Settings ***************************/

if( !function_exists( 'wpw_auto_poster_facebook_setting_tab' ) ) {

    /**
     * Display Facebook Setting Tab
     * 
     * Handle to display facebook setting tab
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_facebook_setting_tab( $selected_tab ) {
        
        $selectedtab = !empty( $selected_tab ) && $selected_tab == 'facebook' ? ' nav-tab-active' : '';
        ?>
            <a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-facebook" attr-tab="facebook">
                <img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/facebook_set.png" width="24" height="24" alt="fb" title="<?php _e( 'Facebook', 'wpwautoposter' ); ?>" />
            </a>
        <?php
    }
}

if( !function_exists( 'wpw_auto_poster_facebook_setting_tab_content' ) ) {

    /**
     * Display Facebook Setting Tab Content
     * 
     * Handle to display facebook setting tab content
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_facebook_setting_tab_content( $selected_tab ) {
    
        $selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'facebook' ? ' wpw-auto-poster-selected-tab' : '';
        ?>
            <div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-facebook"> 
                    
                <?php
            
                // Facebook Settings
                include( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-facebook.php' );
            
                ?>
            
            </div><!--#wpw-auto-poster-tab-facebook-->
        <?php
    }
}

/*********************** Twitter Settings ***************************/

if( !function_exists( 'wpw_auto_poster_twitter_setting_tab' ) ) {

    /**
     * Display Twitter Setting Tab
     * 
     * Handle to display twitter setting tab
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_twitter_setting_tab( $selected_tab ) {
        
        $selectedtab = !empty( $selected_tab ) && $selected_tab == 'twitter' ? ' nav-tab-active' : '';
        ?>
            <a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-twitter" attr-tab="twitter">
                <img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/twitter_set.png" width="24" height="24" alt="tw" title="<?php _e( 'Twitter', 'wpwautoposter' ); ?>" />
            </a>
        <?php
    }
}

if( !function_exists( 'wpw_auto_poster_twitter_setting_tab_content' ) ) {

    /**
     * Display Twitter Setting Tab Content
     * 
     * Handle to display twitter setting tab content
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_twitter_setting_tab_content( $selected_tab ) {
    
        $selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'twitter' ? ' wpw-auto-poster-selected-tab' : '';
        ?>
            <div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-twitter"> 
                    
                <?php
            
                // Twitter Settings
                include( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-twitter.php' );
            
                ?>
            
            </div><!--#wpw-auto-poster-tab-twitter-->
        <?php
    }
}

/*********************** LinkedIn Settings ***************************/

if( !function_exists( 'wpw_auto_poster_linkedin_setting_tab' ) ) {

    /**
     * Display LinkedIn Setting Tab
     * 
     * Handle to display linkedin setting tab
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_linkedin_setting_tab( $selected_tab ) {
        
        $selectedtab = !empty( $selected_tab ) && $selected_tab == 'linkedin' ? ' nav-tab-active' : '';
        ?>
            <a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-linkedin" attr-tab="linkedin">
                <img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/linkedin_set.png" width="24" height="24" alt="li" title="<?php _e( 'LinkedIn', 'wpwautoposter' ); ?>" />
            </a>
        <?php
    }
}

if( !function_exists( 'wpw_auto_poster_linkedin_setting_tab_content' ) ) {

    /**
     * Display LinkedIn Setting Tab Content
     * 
     * Handle to display linkedin setting tab content
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_linkedin_setting_tab_content( $selected_tab ) {
    
        $selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'linkedin' ? ' wpw-auto-poster-selected-tab' : '';
        ?>
            <div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-linkedin"> 
                    
                <?php
            
                // LinkedIn Settings
                include( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-linkedin.php' );
            
                ?>
            
            </div><!--#wpw-auto-poster-tab-linkedin-->
        <?php
    }
}

/*********************** Tumblr Settings ***************************/

if( !function_exists( 'wpw_auto_poster_tumblr_setting_tab' ) ) {

    /**
     * Display Tumblr Setting Tab
     * 
     * Handle to display tumblr setting tab
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_tumblr_setting_tab( $selected_tab ) {
        
        $selectedtab = !empty( $selected_tab ) && $selected_tab == 'tumblr' ? ' nav-tab-active' : '';
        ?>
            <a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-tumblr" attr-tab="tumblr">
                <img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/tumblr_set.png" width="24" height="24" alt="tb" title="<?php _e( 'Tumblr', 'wpwautoposter' ); ?>" />
            </a>
        <?php
    }
}

if( !function_exists( 'wpw_auto_poster_tumblr_setting_tab_content' ) ) {

    /**
     * Display Tumblr Setting Tab Content
     * 
     * Handle to display tumblr setting tab content
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_tumblr_setting_tab_content( $selected_tab ) {
    
        $selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'tumblr' ? ' wpw-auto-poster-selected-tab' : '';
        ?>
            <div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-tumblr"> 
                    
                <?php
            
                // Tumblr Settings
                include( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-tumblr.php' );
            
                ?>
            
            </div><!--#wpw-auto-poster-tab-tumblr-->
        <?php
    }
}

/*********************** BufferApp Settings ***************************/

if( !function_exists( 'wpw_auto_poster_bufferapp_setting_tab' ) ) {

    /**
     * Display BufferApp Setting Tab
     * 
     * Handle to display bufferapp setting tab
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_bufferapp_setting_tab( $selected_tab ) {
        
        $selectedtab = !empty( $selected_tab ) && $selected_tab == 'bufferapp' ? ' nav-tab-active' : '';
        ?>
            <a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-bufferapp" attr-tab="bufferapp">
                <img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/bufferapp_set.png" width="24" height="24" alt="ba" title="<?php _e( 'BufferApp', 'wpwautoposter' ); ?>" />
            </a>
        <?php
    }
}

if( !function_exists( 'wpw_auto_poster_bufferapp_setting_tab_content' ) ) {

    /**
     * Display BufferApp Setting Tab Content
     * 
     * Handle to display bufferapp setting tab content
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_bufferapp_setting_tab_content( $selected_tab ) {
    
        $selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'bufferapp' ? ' wpw-auto-poster-selected-tab' : '';
        ?>
            <div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-bufferapp"> 
                    
                <?php
            
                // BufferApp Settings
                include( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-bufferapp.php' );
            
                ?>
            
            </div><!--#wpw-auto-poster-tab-bufferapp-->
        <?php
    }
}

/*********************** Instagram Settings ***************************/

if( !function_exists( 'wpw_auto_poster_instagram_setting_tab' ) ) {

    /**
     * Display Instagram Setting Tab
     * 
     * Handle to display instagram setting tab
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_instagram_setting_tab( $selected_tab ) {
        
        $selectedtab = !empty( $selected_tab ) && $selected_tab == 'instagram' ? ' nav-tab-active' : '';
        ?>
            <a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-instagram" attr-tab="instagram">
                <img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/instagram_set.png" width="24" height="24" alt="ins" title="<?php _e( 'Instagram', 'wpwautoposter' ); ?>" />
            </a>
        <?php
    }
}

if( !function_exists( 'wpw_auto_poster_instagram_setting_tab_content' ) ) {

    /**
     * Display Instagram Setting Tab Content
     * 
     * Handle to display instagram setting tab content
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_instagram_setting_tab_content( $selected_tab ) {
    
        $selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'instagram' ? ' wpw-auto-poster-selected-tab' : '';
        ?>
            <div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-instagram"> 
                    
                <?php
            
                // Instagram Settings
                include( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-instagram.php' );
            
                ?>
            
            </div><!--#wpw-auto-poster-tab-instagram-->
        <?php
    }
}

/*********************** Pinterest Settings ***************************/

if( !function_exists( 'wpw_auto_poster_pinterest_setting_tab' ) ) {

    /**
     * Display Pinterest Setting Tab
     * 
     * Handle to display pinterest setting tab
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_pinterest_setting_tab( $selected_tab ) {
        
        $selectedtab = !empty( $selected_tab ) && $selected_tab == 'pinterest' ? ' nav-tab-active' : '';
        ?>
            <a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-pinterest" attr-tab="pinterest">
                <img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/pinterest_set.png" width="24" height="24" alt="ins" title="<?php _e( 'Pinterest', 'wpwautoposter' ); ?>" />
            </a>
        <?php
    }
}

if( !function_exists( 'wpw_auto_poster_pinterest_setting_tab_content' ) ) {

    /**
     * Display Pinterest Setting Tab Content
     * 
     * Handle to display pinterest setting tab content
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_pinterest_setting_tab_content( $selected_tab ) {
    
        $selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'pinterest' ? ' wpw-auto-poster-selected-tab' : '';
        ?>
            <div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-pinterest"> 
                    
                <?php
            
                // Instagram Settings
                include( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-pinterest.php' );
            
                ?>
            
            </div><!--#wpw-auto-poster-tab-pinterest-->
        <?php
    }
}