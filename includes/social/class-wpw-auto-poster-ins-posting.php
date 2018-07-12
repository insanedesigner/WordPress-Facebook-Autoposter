<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Instagram Posting Class
 * 
 * Handles all the functions to post the submitted and approved
 * reviews to a chosen Instagram Account.
 * 
 * @package Social Auto Poster
 * @since 2.6.0
 */
class Wpw_Auto_Poster_INS_Posting {

    public $instagram, $message, $model, $logs;

    public function __construct() {

        global $wpw_auto_poster_message_stack, $wpw_auto_poster_model,
        $wpw_auto_poster_logs;

        $this->message = $wpw_auto_poster_message_stack;
        $this->model = $wpw_auto_poster_model;
        $this->logs = $wpw_auto_poster_logs;

        // Load instagram class
        add_action('init', array($this, 'wpw_auto_poster_load_ins'));
    }

    /**
     * Include Instagram Class
     * 
     * Handles to load instagram class
     * 
     * @package Social Auto Poster
     * @since 2.6.0
     */
    public function wpw_auto_poster_load_ins() {

        global $wpw_auto_poster_options;

        // Get all configured instagram accounts
        $ins_users = get_option('wpw_auto_poster_ins_account_details', array());

        // Check if atleast one instgram account is configured or not in settings page
        if (!empty($ins_users) && count($ins_users) > 0) {

            require_once( WPW_AUTO_POSTER_SOCIAL_DIR . '/instagram/autoload.php' );
            return true;
        } else {

            return false;
        }
    }

    /**
     * Instagram Posting
     * 
     * Handles posting to instagram
     * by post data
     * 
     * @package Social Auto Poster
     * @since 2.6.0
     */
    public function wpw_auto_poster_ins_posting($post) {

        global $wpw_auto_poster_options;

        $prefix = WPW_AUTO_POSTER_META_PREFIX;

        // Post to instagram user timeline
        $res = $this->wpw_auto_poster_ins_post_to_userwall($post);

        // Check post has been posted on instagram or not
        if (!empty($res)) {

            // Record logs for posting done on instagram
            $this->logs->wpw_auto_poster_add('Instagram posting completed successfully.');

            update_post_meta($post->ID, $prefix . 'ins_published_on_ins', '1');

            // get current timestamp and update meta as published date/time
            $current_timestamp = current_time( 'timestamp' );
            update_post_meta($post->ID, $prefix . 'published_date', $current_timestamp);
            
            return true;
        }

        return false;
    }

    /**
     * Post to Instagram timeline
     * 
     * Handles to post on instagram user timeline
     * 
     * @package Social Auto Poster
     * @since 2.6.0
     */
    public function wpw_auto_poster_ins_post_to_userwall($post) {

        global $wpw_auto_poster_options;

        $prefix = WPW_AUTO_POSTER_META_PREFIX;
        $unique = 'false'; // Unique flag

        $post_type = $post->post_type; // Get post type
        $userdata = get_userdata($post->post_author); //user data form post author
        $first_name = $userdata->first_name; //user first name
        $last_name = $userdata->last_name; //user last name
        $display_name = $userdata->display_name; //user display name
        //published status
        $ispublished = get_post_meta($post->ID, $prefix . 'ins_published_on_ins', true);

        $tags_arr = array();
        $cats_arr = array();

        // Get all selected tags for selected post type for hashtags support
        if(isset($wpw_auto_poster_options['ins_post_type_tags']) && !empty($wpw_auto_poster_options['ins_post_type_tags'])) {

            $custom_post_tags = $wpw_auto_poster_options['ins_post_type_tags'];
            if(isset($custom_post_tags[$post_type]) && !empty($custom_post_tags[$post_type])){  
                foreach($custom_post_tags[$post_type] as $key => $tag){
                    $term_list = wp_get_post_terms( $post->ID, $tag, array("fields" => "names") );
                    foreach($term_list as $term_single) {
                        $tags_arr[] = $term_single;
                    }
                }
                
            }
        }

        // Get all selected categories for selected post type for hashcats support
        if(isset($wpw_auto_poster_options['ins_post_type_cats']) && !empty($wpw_auto_poster_options['ins_post_type_cats'])) {

            $custom_post_cats = $wpw_auto_poster_options['ins_post_type_cats'];
            if(isset($custom_post_cats[$post_type]) && !empty($custom_post_cats[$post_type])){  
                foreach($custom_post_cats[$post_type] as $key => $category){
                    $term_list = wp_get_post_terms( $post->ID, $category, array("fields" => "names") );
                    foreach($term_list as $term_single) {
                        $cats_arr[] = $term_single;
                    }
                }
                
            }
        }

        // Check if prevent metabox is not enable
        if (!isset($wpw_auto_poster_options['prevent_post_ins_metabox'])) {
            $wpw_auto_poster_ins_user_details = get_post_meta($post->ID, $prefix . 'ins_user_id');
            $wpw_auto_ins_custom_status_msg = get_post_meta($post->ID, $prefix . 'ins_custom_status_msg', true);
            $wpw_auto_poster_custom_img = get_post_meta($post->ID, $prefix . 'ins_post_image', true);
        }

        $ins_user_ids = '';

        if (isset($wpw_auto_poster_ins_user_details) && !empty($wpw_auto_poster_ins_user_details)) {

            $ins_user_ids = $wpw_auto_poster_ins_user_details;
        }

        /*else {
            $ins_user_ids = $wpw_auto_poster_options['ins_type_' . $post_type . '_user'];
        }*/

        /******* Code to posting to selected category Instagram account ******/

        // get all categories for custom post type
        $categories = wpw_auto_poster_get_post_categories_by_ID( $post_type, $post->ID );

        // Get all selected account list from category
        $category_selected_social_acct = get_option( 'wpw_auto_poster_category_posting_acct');
        // IF category selected and category social account data found
        if( !empty( $categories ) && !empty( $category_selected_social_acct ) && empty( $ins_user_ids ) ) {
            $ins_clear_cnt = true;
            // GET Instagram user account ids from post selected categories
            foreach ( $categories as $key => $term_id ) {
                
                $cat_id = $term_id;
                // Get Instagram user account ids form selected category  
                if( isset( $category_selected_social_acct[$cat_id]['ins'] ) && !empty( $category_selected_social_acct[$cat_id]['ins'] ) ) {
                    // clear Instagram user data once
                    if( $ins_clear_cnt)
                        $ins_user_ids = array();
                    $ins_user_ids = array_merge($ins_user_ids, $category_selected_social_acct[$cat_id]['ins'] );
                    $ins_clear_cnt = false;
                }
            }
            if( !empty( $ins_user_ids ) ) {
                $ins_user_ids = array_unique($ins_user_ids);
            }
        }

        // Check if instagram posting account is set for current post type
        if (empty($ins_user_ids) && isset($wpw_auto_poster_options['ins_type_' . $post_type . '_user']) && !empty($wpw_auto_poster_options['ins_type_' . $post_type . '_user'])) {
            $ins_user_ids = $wpw_auto_poster_options['ins_type_' . $post_type . '_user'];
        }

        if (empty($ins_user_ids)) {

            $this->logs->wpw_auto_poster_add('Instagram error: user not selected for posting.');

            sap_add_notice( __('Instagram: You have not selected any user for the posting.', 'wpwautoposter' ), 'error');
            return false;
        }
        $post_to_users = (array) $ins_user_ids;

        //custom status message to post on instagram
        $custom_msg = ( isset($wpw_auto_ins_custom_status_msg) && $wpw_auto_ins_custom_status_msg ) ? $wpw_auto_ins_custom_status_msg : $wpw_auto_poster_options["ins_template"];

        //remove html entity from custom message
        $custom_msg = $this->model->wpw_auto_poster_html_decode($custom_msg);

        //get post title
        $title = $post->post_title;
        //remove html entity from title
        $title = $this->model->wpw_auto_poster_html_decode($title);

        //post link for posting to instagram user wall
        $postlink = '';
        //if custom link is set or not
        $customlink = !empty($postlink) ? 'true' : 'false';
        //do url shortner
        $postlink = $this->model->wpw_auto_poster_get_short_post_link($postlink, $unique, $post->ID, $customlink, 'ins');

        //Check if not
        if (empty($postlink)) {
            $postlink = $this->model->wpw_auto_poster_get_permalink_before_publish($post->ID);
        }

        if (isset($ispublished) && !empty($ispublished)) {
            $unique = 'true';
        }

        $post_content = strip_shortcodes($post->post_content);
        //strip html kses and tags
        $post_content = $this->model->wpw_auto_poster_stripslashes_deep($post_content);
        //decode html entity
        $post_content = $this->model->wpw_auto_poster_html_decode($post_content);
        // Taking the limited content to avoid the exception
        $post_content = $this->model->wpw_auto_poster_excerpt($post_content, 9500);

        // Get post excerpt
        $excerpt = $this->model->wpw_auto_poster_html_decode($this->model->wpw_auto_poster_stripslashes_deep($post->post_excerpt));

        // Get post tags
        //$tags_arr = wp_get_post_tags($post->ID, array('fields' => 'names'));
        $hashtags = (!empty($tags_arr) ) ? '#' . implode(' #', $tags_arr) : '';

        // Get post categories
        /*$hashcats = array();
        foreach ((get_the_category($post->ID)) as $category) {
            $hashcats[] = $category->cat_name;
        }*/
        $hashcats = (!empty($cats_arr) ) ? '#' . implode(' #', $cats_arr) : '';

        // check if custom message is empty if yes than set caption as post title
        if (!empty($custom_msg)) {

            $search_arr = array('{first_name}', '{last_name}', '{display_name}', '{title}', '{link}', '{excerpt}', '{sitename}', '%title%', '%link%', '{hashtags}', '{hashcats}','{content}');
            $replace_arr = array($first_name, $last_name, $display_name, $title, $postlink, $excerpt, get_option('blogname'), $title, $postlink, $hashtags, $hashcats,$post_content);
            
            $code_matches = array();
    
            // check if template tags contains {content-numbers}
            if( preg_match_all( '/\{(content)(-)(\d*)\}/', $custom_msg, $code_matches ) ) {
                $trim_tag = $code_matches[0][0];
                $trim_length = $code_matches[3][0];
                $post_content = substr( $post_content, 0, $trim_length);
                $search_arr[] = $trim_tag;
                $replace_arr[] = $post_content;
            }

            $caption = str_replace($search_arr, $replace_arr, $custom_msg);
        } else {
            $caption = $title;
        }


        //get featured image from post / page / custom post type
        $post_featured_img['src'] = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
        $post_featured_img['path'] = get_attached_file(get_post_thumbnail_id($post->ID), 'full');

        //check custom image is set in meta and not empty
        if (isset($wpw_auto_poster_custom_img['src']) && !empty($wpw_auto_poster_custom_img['src'])) {
            
            $img_src  = $wpw_auto_poster_custom_img['src'];
            
            if ( empty( $wpw_auto_poster_custom_img['id'] )){

                $img_path = wpw_auto_poster_get_image_path( $img_src );
           
            } else {

                $img_path = get_attached_file($wpw_auto_poster_custom_img['id'], 'full');
            }
        } elseif (isset($post_featured_img) && !empty($post_featured_img['src']) && !empty($post_featured_img['path'])) {
            //check post featrued image is set the use that image
            $img_src = $post_featured_img['src'][0];
            if (strpos($img_src, site_url()) !== false) {
                $img_path = $post_featured_img['path'];
            } else {
                $img_path = wpw_auto_poster_get_image_path( $img_src );
            }

        } else {
            //else get post image from settings page
            $img_src = $wpw_auto_poster_options['ins_custom_img'];
            $site_url = site_url();

            if ( !empty( $img_src )){

                if (strpos($img_src, site_url()) !== false) {
                    $imagePath = str_replace($site_url,"",$img_src);
                    $img_path = '..'.$imagePath;

                } else {
                    $img_path = wpw_auto_poster_get_image_path( $img_src );
                }
            }
        }

        //posting logs data
        $posting_logs_data = array(
            'caption' => $caption,
            'image' => $img_src
        );

        $postflg = false;

        //call instagram posting api fucntion if user account is set
        if (!empty($post_to_users)) {
            $instagramAPI = new \InstagramAPI\Instagram(false, false);
            $proxies = !empty( $wpw_auto_poster_options['ins_proxy'] ) ? explode('<br>', nl2br($wpw_auto_poster_options['ins_proxy'], false)) : '';
            $rand = rand(0, count($proxies));
            if ( !empty( $proxies[$rand] ) ) {
                $instagramAPI->setProxy($proxies[$rand]);
            }
            foreach ($post_to_users as $post_to) {
                $ins_post_to_arr = explode('|', $post_to);
                $username = trim($ins_post_to_arr[0]);
                $password = trim($ins_post_to_arr[1]);

                // User details
                $posting_logs_user_details = array(
                    'display_name' => $username,
                );

                try {
                    $instagramAPI->login($username, $password);
                } catch (\Exception $e) {
                    if( preg_match('/required/i', $e->getMessage() ) ) {
                        $this->logs->wpw_auto_poster_add('Instagram error: Please, go to https://www.instagram.com/, sign in and verify your account.');
                        sap_add_notice( __('Instagram: Please, go to <a href="https://www.instagram.com/">https://www.instagram.com/</a>, sign in and verify your account.', 'wpwautoposter' ), 'error');
                    } else{

                        $this->logs->wpw_auto_poster_add('Instagram error: Something went wrong: ' . $e->getMessage());

                        sap_add_notice( __('Instagram: Please, ensure you have added a correct username and password.', 'wpwautoposter' ), 'error' );
                    }
                    
                    $postflg = false;
                }
                try {
                    if( !empty( $img_path ) ) {

                        // code to auto resize the image to valid instagram ratio
                        $auto_size_Media  = new \InstagramAPI\MediaAutoResizer( $img_path );

                        // get rezied image path                        
                        $resize_imag = $auto_size_Media->getFile();                  
                        
                        // $result = $instagramAPI->timeline->uploadPhoto($img_path, ['caption' => $caption]);
                        
                        $result = $instagramAPI->timeline->uploadPhoto( $resize_imag, ['caption' => $caption]);

                        if ($result->status == 'ok') {

                            //posting logs store into database
                            $this->model->wpw_auto_poster_insert_posting_log($post->ID, 'ins', $posting_logs_data, $posting_logs_user_details);
                            $this->logs->wpw_auto_poster_add('Instagram posting completed with caption ' . $result->media->caption->text);
                            $postflg = true;
                            
                            // delete temp auto resize image
                            $auto_size_Media->deleteFile();
                        }
                    } else{
                        $this->logs->wpw_auto_poster_add('Instagram error: No media files selected for instgram posting');
                        sap_add_notice( __('Instagram: You have not uploaded any image for instagram posting.', 'wpwautoposter' ), 'error');
                        $postflg = false;
                    }
                } catch (\Exception $e) {
                    $this->logs->wpw_auto_poster_add('Instagram error: Something went wrong: ' . $e->getMessage());
                    sap_add_notice( sprintf( __('Instagram: %s', 'wpwautoposter' ), $e->getMessage() ), 'error');
                    $postflg = false;
                }
            }
        }
        return $postflg;
    }

}