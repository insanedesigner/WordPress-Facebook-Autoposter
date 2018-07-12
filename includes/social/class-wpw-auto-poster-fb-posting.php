<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Fan Page Posting Class
 * 
 * Handles all the functions to post the submitted and approved
 * reviews to a chosen Fan Page / Facebook Account.
 * 
 * @package Social Auto Poster
 * @since 1.0.0
 */
class Wpw_Auto_Poster_FB_Posting {

    public $facebook, $message, $model, $logs, $_user_cache;
    public $fb_app_version = '';
    public $error = "";


    public function __construct() {

        global $wpw_auto_poster_message_stack, $wpw_auto_poster_model,
        $wpw_auto_poster_logs;

        $this->message = $wpw_auto_poster_message_stack;
        $this->model = $wpw_auto_poster_model;
        $this->logs = $wpw_auto_poster_logs;

        //initialize the session value when data is saved in database
        add_action('init', array($this, 'wpw_auto_poster_fb_initialize'));
    }

    /**
     * Include Facebook Class
     * 
     * Handles to load facebook class
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_load_facebook($app_id = false) {

        global $wpw_auto_poster_options;

        // Facebook app version
        $this->fb_app_version = ( !empty( $wpw_auto_poster_options['fb_app_version'] ) ) ? $wpw_auto_poster_options['fb_app_version'] : '';

        // Getting facebook apps
        $fb_apps = wpw_auto_poster_get_fb_apps();

        // If app id is not passed then take first fb app data
        if (empty($app_id)) {
            $fb_apps_keys = array_keys($fb_apps);
            $app_id = reset($fb_apps_keys);
        }

        // Check facebook application id and application secret is not empty or not
        if (!empty($app_id) && !empty($fb_apps[$app_id])) {

            if (!class_exists('Facebook')) {
                require_once( WPW_AUTO_POSTER_SOCIAL_DIR . '/facebook/facebook.php' );
            }

            $this->facebook = new Facebook(array(
                'appId' => $app_id,
                'secret' => $fb_apps[$app_id],
                'cookie' => true
            ));
            return true;
        } else {

            return false;
        }
    }

    /**
     * Assign Facebook User's all Data to session
     * 
     * Handles to assign user's facebook data
     * to sessoin & save to database
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_fb_initialize() {

        global $wpw_auto_poster_options;

        //set session to set tab selected in settings page
        //isset( $_GET['page'] ) && $_GET['page'] == 'wpw-auto-poster-settings'
        if (isset($_GET['wpw_fb_grant']) && $_GET['wpw_fb_grant'] == 'true' && isset($_GET['code']) && isset($_REQUEST['state']) && isset($_GET['wpw_fb_app_id'])) {

            //record logs for grant extended permission
            $this->logs->wpw_auto_poster_add('Facebook Grant Extended Permission', true);

            //record logs for get parameters set properly
            $this->logs->wpw_auto_poster_add('Get Parameters Set Properly.');

            $fb_app_id = $_GET['wpw_fb_app_id'];
            $facebook_auth_options = !empty( $wpw_auto_poster_options['facebook_auth_options'] ) ? $wpw_auto_poster_options['facebook_auth_options'] : 'graph';

            try {

                //load facebook class
                $facebook = $this->wpw_auto_poster_load_facebook($fb_app_id);
            } catch (Exception $e) {

                //record logs exception generated
                $this->logs->wpw_auto_poster_add('Facebook Exception : ' . $e->getMessage());
                
                $facebook = null;
            }

            //check facebook class is exis or not
            if (!$facebook)
                return false;

            // Facebook
            $user = $this->facebook->getUser();

            //record logs for user id
            $this->logs->wpw_auto_poster_add('Facebook User ID : ' . $user);

            //check user is logged in facebook or not
            if ($user) {

                try {

                    // Proceed knowing you have a logged in user who's authenticated.
                    $_SESSION['wpweb_fb_user_cache'] = $this->facebook->api('/me');
                    $this->_user_cache = $_SESSION['wpweb_fb_user_cache'];

                    $_SESSION['wpweb_fb_user_id'] = $user;

                    $_SESSION['wpweb_fb_user_accounts'] = $this->wpw_auto_poster_fb_fetch_accounts();

                    // Start code to manage session from database
                    $wpw_auto_poster_fb_sess_data = get_option('wpw_auto_poster_fb_sess_data');

                    // Checking if the grant extend is already done or not
                    if (!isset($wpw_auto_poster_fb_sess_data[$fb_app_id])) {

                        $sess_data = array(
                            'wpw_auto_poster_fb_user_cache' => $_SESSION['wpweb_fb_user_cache'],
                            'wpw_auto_poster_fb_user_id' => $_SESSION['wpweb_fb_user_id'],
                            'wpw_auto_poster_fb_user_accounts' => $_SESSION['wpweb_fb_user_accounts'],
                            WPW_AUTO_POSTER_FB_SESS1 => $_SESSION['fb_' . $fb_app_id . '_code'],
                            WPW_AUTO_POSTER_FB_SESS2 => $_SESSION['fb_' . $fb_app_id . '_access_token'],
                            WPW_AUTO_POSTER_FB_SESS3 => $_SESSION['fb_' . $fb_app_id . '_user_id'],
                            WPW_AUTO_POSTER_FB_SESS4 => isset($_SESSION['fb_' . $fb_app_id . '_state']) ? $_SESSION['fb_' . $fb_app_id . '_state'] : '',
                        );

                        if ($fb_app_id) {

                            if( $facebook_auth_options == 'rest') { // if previous session of rest then empty all data
                                $wpw_auto_poster_fb_sess_data = array();
                                $wpw_auto_poster_options['facebook_auth_options'] = 'graph';

                                update_option('wpw_auto_poster_options', $wpw_auto_poster_options );
                                
                            } elseif( !empty( $wpw_auto_poster_fb_sess_data ) ) { // if rest options selected and give graph access then remove rest data
                                foreach ($wpw_auto_poster_fb_sess_data as $k_app_id => $v_sess_data) {

                                        if( $k_app_id == $v_sess_data['wpw_auto_poster_fb_user_id'] ) {
                                            unset( $wpw_auto_poster_fb_sess_data[$k_app_id]);
                                        }
                                    }    
                            }

                            // Save Multiple Accounts
                            $wpw_auto_poster_fb_sess_data[$fb_app_id] = $sess_data;

                            // Update session data to options
                            update_option('wpw_auto_poster_fb_sess_data', $wpw_auto_poster_fb_sess_data);

                            // Record logs for session data updated to options
                            $this->logs->wpw_auto_poster_add('Facebook Session Data Updated to Options');
                        } else {
                            // Record logs when app id is not found
                            $this->logs->wpw_auto_poster_add("Facebook error: The App Id {$fb_app_id} does not exist.");
                        }
                    }// end code to manage session from database
                    // Record logs for grant extend successfully
                    $this->logs->wpw_auto_poster_add('Facebook Grant Extended Permission Successfully.');
                } catch (FacebookApiException $e) {

                    //record logs exception generated
                    $this->logs->wpw_auto_poster_add('Facebook Exception : ' . $e->__toString());

                    //user is null
                    $user = null;
                } //end catch
            } //end if to check user is not empty
            //set tab selected
            $this->message->add_session('poster-selected-tab', 'facebook');

            //redirect to proper page
            wp_redirect(add_query_arg(array('wpw_fb_grant' => false, 'code' => false, 'state' => false, 'wpw_fb_app_id' => false)));
            exit;
        }
    }

    /**
     * Facebook Login URL
     * 
     * Getting the login URL from Facebook.
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_get_fb_login_url($app_id = false) {

        //load facebook class
        $facebook = $this->wpw_auto_poster_load_facebook($app_id);

        //check facebook class is exis or not
        if (!$facebook)
            return false;

        // $portvalue = is_ssl() ? 'https://' : 'http://';
        //$redirect_URL = $portvalue . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        $redirect_URL = add_query_arg( array('page' => 'wpw-auto-poster-settings' ), admin_url('admin.php') );

        $redirect_URL = apply_filters('wpw_auto_poster_fb_redirect_url', $redirect_URL);
        $redirect_URL = add_query_arg(array('wpw_fb_grant' => 'true', 'wpw_fb_app_id' => $app_id), $redirect_URL);
        $loginUrl = $this->facebook->getLoginUrl(array(
            //'scope'       => 'publish_actions,email,manage_pages,user_photos,user_groups',
            'scope' => 'publish_actions,email,manage_pages,publish_pages,user_posts,user_photos,user_managed_groups',
            'redirect_uri' => $redirect_URL
                ));

        return apply_filters('wpw_auto_poster_get_fb_login_url', $loginUrl, $this);
    }

    /**
     * User Data
     * 
     * Getting the cached user data from the connected
     * Facebook user (back end).
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_get_fb_user_data() {

        if (!empty($this->_user_cache)) {
            return $this->_user_cache;
        }
    }

    /**
     * Pages Tokens
     * 
     * Getting the the tokens from all pages/accounts which
     * are associated with the connected Facebook account
     * so that the admin chan choose to which page/account
     * he wants to post the submitted and approved reviews to.
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_fb_get_pages_tokens() {

        $fb_app_id = isset($_GET['wpw_fb_app_id']) ? $_GET['wpw_fb_app_id'] : '';

        // Load facebook class
        $facebook = $this->wpw_auto_poster_load_facebook($fb_app_id);

        // Check facebook class is exis or not
        if (!$facebook)
            return false;

        try {
            $ret = $this->facebook->api('/' . $_SESSION['wpweb_fb_user_id'] . '/accounts/');
        } catch (Exception $e) {
            return false;
        }

        return $ret;
    }

    /**
     * Fetching Accounts
     * 
     * Fetching all the associated accounts from the connected
     * Facebook user (site admin).
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_fb_fetch_accounts() {

        global $wpw_auto_poster_options;

        if (!isset($wpw_auto_poster_options['prevent_linked_accounts_access'])) {

            $page_tokens = $this->wpw_auto_poster_fb_get_pages_tokens();
            $page_tokens = isset($page_tokens['data']) ? $page_tokens['data'] : array();

            //Remove this code due to group posting is not working from fb api 2.4.0 ( SAP V-1.8.0 )
            // Getting user group data
            $group_tokens = $this->wpw_auto_poster_fb_get_groups_tokens();
            $group_tokens = isset($group_tokens['data']) ? $group_tokens['data'] : array();
        } else {
            $page_tokens = array();
            $group_tokens = array();
        }

        $api = array();
        $api['auth_accounts'][$_SESSION['wpweb_fb_user_id']] = $this->_user_cache['name'] . " (" . $_SESSION['wpweb_fb_user_id'] . ")";
        $api['auth_tokens'][$_SESSION['wpweb_fb_user_id']] = isset($_SESSION['fb_' . WPW_AUTO_POSTER_FB_APP_ID . '_access_token']) ? $_SESSION['fb_' . WPW_AUTO_POSTER_FB_APP_ID . '_access_token'] : '';

        // Taking user auth tokens
        $user_auth_tokens = isset($_SESSION['fb_' . WPW_AUTO_POSTER_FB_APP_ID . '_access_token']) ? $_SESSION['fb_' . WPW_AUTO_POSTER_FB_APP_ID . '_access_token'] : '';

        if (!isset($wpw_auto_poster_options['prevent_linked_accounts_access'])) {

            foreach ($page_tokens as $ptk) {
                if (!isset($ptk['id']) || !isset($ptk['access_token']))
                    continue;
                $api['auth_tokens'][$ptk['id']] = $ptk['access_token'];
                $api['auth_accounts'][$ptk['id']] = $ptk['name'];
            }

            //Remove this code due to group posting is not working from fb api 2.4.0 ( SAP V-1.8.0 )
            // Creating user group data if user is administrator of that group
            foreach ($group_tokens as $gtk) {
                if (isset($gtk['id'])) {
                    $api['auth_tokens'][$gtk['id']] = $user_auth_tokens;
                    $api['auth_accounts'][$gtk['id']] = $gtk['name'];
                }
            }
        }


        return $api;
    }

    /**
     * Post to User Wall on Facebook
     * 
     * Handles to post user wall on facebook
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_fb_post_to_userwall($post) {

        global $wpw_auto_poster_options;

        // Get stored fb app grant data
        $wpw_auto_poster_fb_sess_data = get_option('wpw_auto_poster_fb_sess_data');
        $wpw_auto_poster_options["fb_global_message_template"] = ( isset( $wpw_auto_poster_options["fb_global_message_template"] ) )? $wpw_auto_poster_options["fb_global_message_template"] : '';

        // check facebook method for posting since 2.7.6
        $facebook_auth_options = !empty( $wpw_auto_poster_options['facebook_auth_options'] ) ? $wpw_auto_poster_options['facebook_auth_options'] : 'graph';

        // Check facebook grant extended permission is set ot not
        if (!empty($wpw_auto_poster_fb_sess_data)) {

            // Posting logs data
            $posting_logs_data = array();

            //Initialize tags and categories
            $tags_arr = array();
            $cats_arr = array();

            //metabox field prefix
            $prefix = WPW_AUTO_POSTER_META_PREFIX;

            $post_type = $post->post_type; // Post type

            $unique = 'false'; // Unique
            $userdata = get_userdata($post->post_author); //user data form post author
            $first_name = $userdata->first_name; //user first name
            $last_name = $userdata->last_name; //user last name
            //published status
            $ispublished = get_post_meta($post->ID, $prefix . 'fb_published_on_fb', true);

            // Get all selected tags for selected post type for hashtags support
            if(isset($wpw_auto_poster_options['fb_post_type_tags']) && !empty($wpw_auto_poster_options['fb_post_type_tags'])) {

                $custom_post_tags = $wpw_auto_poster_options['fb_post_type_tags'];
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
            if(isset($wpw_auto_poster_options['fb_post_type_cats']) && !empty($wpw_auto_poster_options['fb_post_type_cats'])) {

                $custom_post_cats = $wpw_auto_poster_options['fb_post_type_cats'];
                if(isset($custom_post_cats[$post_type]) && !empty($custom_post_cats[$post_type])){  
                    foreach($custom_post_cats[$post_type] as $key => $category){
                        $term_list = wp_get_post_terms( $post->ID, $category, array("fields" => "names") );
                        foreach($term_list as $term_single) {
                            $cats_arr[] = $term_single;
                        }
                    }
                    
                }
            }

            if (!isset($wpw_auto_poster_options['prevent_post_metabox'])) { //check if prevent metabox is not enable
                $wpw_auto_poster_fb_custom_title = get_post_meta($post->ID, $prefix . 'fb_custom_title', true);

                 // Allow third party plugins to change custom title
                $wpw_auto_poster_fb_custom_title = apply_filters('wpw_sap_change_custom_message', $wpw_auto_poster_fb_custom_title, $post->ID);
                
                $wpw_auto_poster_fb_user_id = get_post_meta($post->ID, $prefix . 'fb_user_id');
                $wpw_auto_fb_posting_method = get_post_meta($post->ID, $prefix . 'fb_posting_method', true);
                $wpw_auto_fb_custom_status_msg = get_post_meta($post->ID, $prefix . 'fb_custom_status_msg', true);
                $wpw_auto_poster_custom_link = get_post_meta($post->ID, $prefix . 'fb_custom_post_link', true);
                $wpw_auto_poster_custom_img = get_post_meta($post->ID, $prefix . 'fb_post_image', true);
            } //end if
            // Getting all facebook apps
            $fb_apps = wpw_auto_poster_get_fb_apps();

            // Getting all stored facebook access token
            $fb_access_token = wpw_auto_poster_get_fb_accounts('all_auth_tokens');

            // Facebook user id on whose wall the post will be posted
            $fb_user_ids = '';
            //check there is facebook user ids are set and not empty in metabox
            if (isset($wpw_auto_poster_fb_user_id) && !empty($wpw_auto_poster_fb_user_id)) {
                //users from metabox
                $fb_user_ids = $wpw_auto_poster_fb_user_id;

                /*                 * *** Backward Compatibility Code Starts **** */
                // If user account is selected in meta so creating data accoring to new method ( Will be helpfull when scheduling is done )
                if (!empty($fb_user_ids)) {

                    $fb_first_app_key = !empty($wpw_auto_poster_options['facebook_keys'][0]['app_id']) ? $wpw_auto_poster_options['facebook_keys'][0]['app_id'] : '';

                    if (!empty($fb_first_app_key)) {
                        foreach ($fb_user_ids as $fb_user_key => $fb_user_data) {
                            if (strpos($fb_user_data, '|') === false) {
                                $fb_user_ids[$fb_user_key] = $fb_user_data . '|' . $fb_first_app_key;
                            }
                        }
                    }
                }
                /*                 * *** Backward Compatibility Code Ends **** */
            } //end if


            /******* Code to posting to selected category FB account ******/

            //$categories = get_the_category($post->ID, array()); // get post categories

            // get all categories for custom post type
            $categories = wpw_auto_poster_get_post_categories_by_ID( $post_type, $post->ID );
             
            // Get all selected account list from category
            $category_selected_social_acct = get_option('wpw_auto_poster_category_posting_acct');
            // IF category selected and category social account data found
            if (!empty($categories) && !empty($category_selected_social_acct) && empty($fb_user_ids)) {
                $fb_clear_cnt = true;
                // GET FB user account ids from post selected categories
                foreach ($categories as $key => $term_id) {

                    $cat_id = $term_id;
                    // Get FB user account ids form selected category  
                    if (isset($category_selected_social_acct[$cat_id]['fb']) && !empty($category_selected_social_acct[$cat_id]['fb'])) {
                        // clear fb user data once
                        if ($fb_clear_cnt)
                            $fb_user_ids = array();
                        $fb_user_ids = array_merge($fb_user_ids, $category_selected_social_acct[$cat_id]['fb']);
                        $fb_clear_cnt = false;
                    }
                }
                if( !empty( $fb_user_ids ) ) {
                    $fb_user_ids = array_unique($fb_user_ids);
                }
            }

            //check facebook user ids are empty in metabox and set in settings page
            if (empty($fb_user_ids) && isset($wpw_auto_poster_options['fb_type_' . $post_type . '_user']) && !empty($wpw_auto_poster_options['fb_type_' . $post_type . '_user'])) {
                //users from settings
                $fb_user_ids = $wpw_auto_poster_options['fb_type_' . $post_type . '_user'];
            } //end if
            //check facebook user ids are empty selected for posting
            if (empty($fb_user_ids)) {

                //record logs for facebook users are not selected
                $this->logs->wpw_auto_poster_add('Facebook error: User not selected for posting.');
                sap_add_notice( __('Facebook: You have not selected any user for the posting.', 'wpwautoposter' ), 'error');
                //return false
                return false;
            } //end if to check user ids are empty
            //convert user ids to single array
            $post_to_users = (array) $fb_user_ids;

            //post custom title for posting on facebook userwall
            $title = !empty($wpw_auto_poster_fb_custom_title) ? $wpw_auto_poster_fb_custom_title : $wpw_auto_poster_options["fb_global_message_template"];
            
            $title = !empty($title) ? $title : $post->post_title;

            $title = apply_filters('wpw_auto_poster_fb_title', $title, $post);

            //remove html entity from title
            $title = $this->model->wpw_auto_poster_html_decode($title);

            //posting method
            $post_as = isset($wpw_auto_fb_posting_method) && !empty($wpw_auto_fb_posting_method) ? $wpw_auto_fb_posting_method : $wpw_auto_poster_options['fb_type_' . $post_type . '_method'];

            //post link for posting to facebook user wall
            $postlink = isset($wpw_auto_poster_custom_link) && !empty($wpw_auto_poster_custom_link) ? $wpw_auto_poster_custom_link : '';

            $glabla_share_post_type = ( !empty( $wpw_auto_poster_options['fb_post_share_type'] ) ) ? $wpw_auto_poster_options['fb_post_share_type'] : 'link_posting';

            $fb_share_post_type = get_post_meta($post->ID, $prefix . 'fb_share_posting_type', true);
            $fb_share_post_type = ( !empty( $fb_share_post_type ) ) ? $fb_share_post_type : $glabla_share_post_type;

            // skip custom link if App version 2.0.9 
            if( $this->fb_app_version >= 209 ) {

                $postlink = "";
            }

            //if custom link is set or not
            $customlink = !empty($postlink) ? 'true' : 'false';

            //do url shortner
            $postlink = $this->model->wpw_auto_poster_get_short_post_link($postlink, $unique, $post->ID, $customlink, 'fb');

            //Check if not
            if( empty( $postlink ) ) {
                $postlink = $this->model->wpw_auto_poster_get_permalink_before_publish( $post->ID );
            }

            //do url shortner
            $postlink_feed = $this->model->wpw_auto_poster_get_short_post_link(get_permalink($post->ID), $unique, $post->ID, 'false', 'fb');

            // not sure why this code here it should be above $postlink but lets keep it here
            //if post is published on facebook once then change url to prevent duplication
            if (isset($ispublished) && !empty($ispublished)) {
                $unique = 'true';
            }

            //custom status message to post on facebook
            $custom_msg = isset($wpw_auto_fb_custom_status_msg) && $wpw_auto_fb_custom_status_msg ? $wpw_auto_fb_custom_status_msg : $post->post_title;

            //remove html entity from custom message
            $custom_msg = $this->model->wpw_auto_poster_html_decode($custom_msg);

            //post content to post
            $post_content = strip_shortcodes($post->post_content);

            //strip html kses and tags
            $post_content = $this->model->wpw_auto_poster_stripslashes_deep($post_content);
            //decode html entity
            $post_content = $this->model->wpw_auto_poster_html_decode($post_content);

            // Taking the limited content to avoid the exception
            $post_content = $this->model->wpw_auto_poster_excerpt($post_content, 9500);

            $trim_content = $post_content;

            // Get post excerpt
            $excerpt = $this->model->wpw_auto_poster_html_decode($this->model->wpw_auto_poster_stripslashes_deep($post->post_excerpt));

            // Get post tags
            //$tags_arr   = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
            $hashtags   = ( !empty( $tags_arr ) ) ? '#'.implode( ' #', $tags_arr ) : '';
            

            // get post categories
            /*$hashcats = array();
            foreach((get_the_category( $post->ID)) as $category) {
               $hashcats[] = $category->cat_name;
            }*/
            $hashcats   = ( !empty( $cats_arr ) ) ? '#'.implode( ' #', $cats_arr ) : '';


            /*             * ************
             * Image Priority
             * If metabox image set then take from metabox
             * If metabox image is not set then take from featured image
             * If featured image is not set then take from settings page
             * ************ */

            //get featured image from post / page / custom post type
            $gallery_images = array();
            $post_featured_img = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');

            //check custom image is set in meta and not empty
            if (isset($wpw_auto_poster_custom_img['src']) && !empty($wpw_auto_poster_custom_img['src'])) {
                $post_img = $wpw_auto_poster_custom_img['src'];
            } elseif (isset($post_featured_img[0]) && !empty($post_featured_img[0])) {
                //check post featrued image is set the use that image
                $post_img = $post_featured_img[0];
            } else {
                //else get post image from settings page
                $post_img = $wpw_auto_poster_options['fb_custom_img'];
            }

            if( $fb_share_post_type == 'image_posting'  ) {
                $gallery_images_ids = get_post_meta($post->ID, $prefix . 'fb_post_gallery', true);
                if( !empty( $gallery_images_ids ) ){
                    foreach ( $gallery_images_ids as $key => $image_id ) {
                        $gall_img = wp_get_attachment_image_src( $image_id, 'full');
                        $gall_img = $gall_img[0];
                        $gallery_images[] = $gall_img;
                    }
                }
                if( empty( $gallery_images ) ){
                    $default_image = (!empty( $post_featured_img )) ? $post_featured_img[0] : $wpw_auto_poster_options['fb_custom_img'];
                    if( !empty( $default_image ) ) {
                        $gallery_images[] = $default_image;
                    }
                }
            }

            //posting logs data
            $posting_logs_data = array(
                'title' => $title,
                'content' => $post_content,
                'link' => $postlink,
                'fb_type' => $post_as,
            );
            switch ($post_as) {

                case "feed_status":

                    $post_method = 'feed';
                    $search_arr = array('{title}', '{link}', '{first_name}', '{last_name}', '{sitename}', '{site_name}', '%title%', '%link%', '{hashtags}', '{hashcats}');
                    $replace_arr = array( $post->post_title , $postlink, $first_name, $last_name, get_option('blogname'), get_option('blogname'), $post->post_title, $postlink, $hashtags, $hashcats);
                    $final_msg = str_replace($search_arr, $replace_arr, $custom_msg);
                    $send = array(
                        'message' => $final_msg
                    );
                    $posting_logs_data['status'] = $final_msg;
                    break;

                case "feed":
                default:
                    $post_method = 'feed';
                    $send = array();
                    //check post image is not empty then pass to facebook
                    if (!empty($post_img)) {
                        $send['picture'] = $post_img;
                        $posting_logs_data['image'] = $post_img;
                    }
                    

                    // Added tag support for wall post
                    $search_arr = array('{title}', '{link}', '{first_name}', '{last_name}', '{sitename}', '{site_name}', '{excerpt}', '{hashtags}', '{hashcats}','{content}');
                    $replace_arr = array($post->post_title, $postlink_feed, $first_name, $last_name, get_option('blogname'), get_option('blogname'), $excerpt, $hashtags, $hashcats, $post_content);
                    
                    $code_matches = array();

                
                    // check if template tags contains {content-numbers}
                    if( preg_match_all( '/\{(content)(-)(\d*)\}/', $title, $code_matches ) ) {
                        $trim_tag = $code_matches[0][0];
                        $trim_length = $code_matches[3][0];
                        $trim_content = substr( $trim_content, 0, $trim_length);
                        $search_arr[] = $trim_tag;
                        $replace_arr[] = $trim_content;
                    }

                    $title = str_replace($search_arr, $replace_arr, $title);

                    $send['message'] = substr($title, 0, 999);
                    $send['link'] = $postlink;
                    $send['name'] = $post->post_title;
                    $send['description'] = $post_content;

                    break;
            }

            //initial value of posting flag
            $postflg = false;
            $media_upload = true;

            // if Post Reviews to this Fan Page/Account option is set
            if (!empty($post_to_users)) {

                // Get facebook account details
                $fb_accounts = wpw_auto_poster_get_fb_accounts();

                // Record logs for facebook users are not selected
                $this->logs->wpw_auto_poster_add('Facebook posting begins with ' . $post_method . ' method.');

                if( $facebook_auth_options == 'rest' ) {
                    if( isset( $send['link'] ) && !empty( $send['link'] ) ){
                        $send['link'] = urlencode($send['link']);
                    }
                    if( !empty( $send['message'] ) ){
                      $send['message'] = urlencode($send['message']);  
                    }
                    if( !empty( $send['description'] ) ){
                      $send['description'] = urlencode($send['description']);  
                    }
                    if( !empty( $send['name'] ) ){
                      $send['name'] = urlencode($send['name']);  
                    }
                }

                // code if fb app version 2.9 or below 
                $replace_send = $send;

                foreach ($post_to_users as $post_to) {

                    $fb_post_app_arr = explode('|', $post_to);
                    $fb_post_to_id = isset($fb_post_app_arr[0]) ? $fb_post_app_arr[0] : ''; // Facebook Posting account Id
                    $fb_post_app_id = isset($fb_post_app_arr[1]) ? $fb_post_app_arr[1] : ''; // Facebook App Id
                    $fb_post_app_sec = isset($fb_apps[$fb_post_app_id]) ? $fb_apps[$fb_post_app_id] : ''; // Facebook App Sec

                    if( $facebook_auth_options == 'graph') {
                        // Load facebook class
                        $facebook = $this->wpw_auto_poster_load_facebook($fb_post_app_id);

                        // Check facebook class is exis or not
                        if (!$facebook) {
                            $this->logs->wpw_auto_poster_add('Facebook error: Account is not initialized with ' . $fb_post_app_id . ' App.'); // Record logs for facebook not initialized
                            continue;
                        }
                    }   else{ // load facebook rest API class

                        if (!class_exists('Wpw_Auto_Poster_REST_API')) {
                            require_once( WPW_AUTO_POSTER_SOCIAL_DIR . '/facebook-android/Facebook_API.php' );
                        }

                        $this->facebook = new Wpw_Auto_Poster_REST_API();
                    }

                    $this->logs->wpw_auto_poster_add('Facebook API Method: ' . $facebook_auth_options );                   

                    // Remove deprecated fields picture,description for fb app version >= 2.9 
                    if( $this->fb_app_version >= 209 && $post_as != 'feed_status' ) {

                        // modified facebook post fields
                        $send = array(
                                // 'access_token' => $replace_send['access_token'],
                                'message' => $replace_send['message'],
                                'link'  => $replace_send['link'],
                                'actions' => array( 'name' => $replace_send['name'], 'link' => $replace_send['link'] )
                            );
                    }

                    $temp_send['access_token'] = ( !empty($fb_access_token[$post_to] ) ) ? $fb_access_token[$post_to] : '';

                    /** code to check is image posting */
                    if( $fb_share_post_type == 'image_posting' && !empty( $gallery_images ) && !empty( $temp_send['access_token'] ) ) {

                        if( isset( $send['link'] ) )
                            unset( $send['link']);
                        if( isset( $send['actions'] ) )
                            unset( $send['actions']);
                        if( isset( $send['picture'] ) )
                            unset( $send['picture']);
                        if( isset( $send['description'] ) )
                            unset( $send['description']);
                        if( isset( $send['name'] ) )
                            unset( $send['name']);
                        
                        
                        $post_method = 'feed';

                        if( count( $gallery_images) > 1 ) { // upload one by one image as draft
                            $media_ids = array();
                            $counter = 0;

                            $access_token = $temp_send['access_token'];

                            foreach ( $gallery_images as $key => $img) {
                                
                                $temp_send['published'] = false;
                                $temp_send['url'] =  $img;
                                try {
                                    if( $facebook_auth_options == 'graph') {
                                        $this->facebook->setFileUploadSupport(true);
                                        //post to facebook user wall
                                        $media = $this->facebook->api('/' . $fb_post_to_id . '/photos/', 'POST', $temp_send );
                                        //check id is set in response and not empty
                                        if (isset($media['id']) && !empty( $media['id'] ) ) {
                                            $send['attached_media['.$counter.']'] = '{"media_fbid":"'.$media['id'].'"}';
                                            $counter++;
                                        }
                                    }
                                    else{
                                        
                                        $temp_send['url'] = urlencode($temp_send['url']);

                                        if( isset( $temp_send['access_token'] ) ) {
                                            unset($temp_send['access_token']);
                                        }
                                        $this->facebook->setMethod("POST");                      
                                        $this->facebook->setAccessToken( $access_token );
                                        $this->facebook->setEndPoint('photos');
                                        $this->facebook->setNode( $fb_post_to_id);
                                        $media = $this->getFbJsonResponse($this->facebook->request($temp_send));
                                        if ( isset( $media->id ) && !empty( $media->id ) ) {
                                            $send['attached_media['.$counter.']'] = '{"media_fbid":"'.$media->id.'"}';
                                            $counter++;
                                        }

                                        if(isset($media->error)){
                                            $this->logs->wpw_auto_poster_add('Facebook error: Posting exception for ' . $post_to . ' : ' . $media->error->message );
                                        }

                                        if(!$media){

                                            sap_add_notice( 'Facebook:'.$this->error , 'error');
                                            $this->logs->wpw_auto_poster_add('Facebook error: Posting exception for ' . $post_to . ' : ' . $media->error );
                                        }
                                    }
                                    
                                } catch (Exception $e) {
                                    $this->logs->wpw_auto_poster_add('Facebook error: Posting exception for ' . $post_to . ' : ' . $e->__toString());
                                    $error_msg = sprintf( __('Facebook: Something was wrong while posting %s', 'wpwautoposter' ), $e->__toString() );
                                    sap_add_notice( $error_msg, 'error');
                                    $media_upload = false;
                                }
                            }

                        } else { // if sinle image then direct posting
                            $post_method = 'photos';
                            $send['url'] = $gallery_images[0];

                            if( $facebook_auth_options == 'graph') {
                                $this->facebook->setFileUploadSupport(true);
                            }else{
                                $send['url'] = urlencode($send['url']);
                            }

                        }

                    }

                    // Getting stored facebook app data
                    $fb_stored_app_data = isset($wpw_auto_poster_fb_sess_data[$fb_post_app_id]) ? $wpw_auto_poster_fb_sess_data[$fb_post_app_id] : array();

                    // Get user cache data
                    $user_cache_data = isset($fb_stored_app_data['wpw_auto_poster_fb_user_cache']) ? $fb_stored_app_data['wpw_auto_poster_fb_user_cache'] : array();

                    $send['access_token'] = '';

                    // User details
                    $posting_logs_user_details = array(
                        'account_id' => $post_to,
                        'fb_app_id' => $fb_post_app_id,
                        'fb_app_secret' => $fb_post_app_sec,
                    );

                    if (isset($user_cache_data['id']) && $user_cache_data['id'] == $fb_post_to_id) { // Check facebook main user data
                        $user_email = isset($user_cache_data['email']) ? $user_cache_data['email'] : '';
                        $posting_logs_user_details['display_name'] = isset($user_cache_data['name']) ? $user_cache_data['name'] . ' (' . $user_email . ')' : '';
                        $posting_logs_user_details['first_name'] = isset($user_cache_data['first_name']) ? $user_cache_data['first_name'] : '';
                        $posting_logs_user_details['last_name'] = isset($user_cache_data['last_name']) ? $user_cache_data['last_name'] : '';
                        $posting_logs_user_details['user_name'] = isset($user_cache_data['username']) ? $user_cache_data['username'] : '';
                        $posting_logs_user_details['user_email'] = $user_email;
                        $posting_logs_user_details['profile_url'] = isset($user_cache_data['link']) ? $user_cache_data['link'] : '';
                    } else {//Account Name
                        $posting_logs_user_details['display_name'] = isset($fb_accounts[$fb_post_to_id]) ? $fb_accounts[$fb_post_to_id] : '';
                    }

                    //record logs for facebook data
                    $this->logs->wpw_auto_poster_add('Facebook post data : ' . var_export($send, true));

                    if (isset($fb_access_token[$post_to])) {//check there is access token is set
                        $send['access_token'] = $fb_access_token[$post_to]; // most imp line
                    } //end if
                    
                    
                    //check accesstoken is not empty
                    if (!empty($send['access_token'])) {

                        if( $media_upload == true ) {

                            try {

                                if( $facebook_auth_options == 'graph') {
                                    //post to facebook user Wall
                                    $ret = $this->facebook->api('/' . $fb_post_to_id . '/' . $post_method . '/', 'POST', $send);
                                    //check id is set in response and not empty
                                    if (isset($ret['id']) && !empty($ret['id'])) {

                                        //posting logs store into database
                                        $this->model->wpw_auto_poster_insert_posting_log($post->ID, 'fb', $posting_logs_data, $posting_logs_user_details);

                                        //record logs for facebook users are not selected
                                        $this->logs->wpw_auto_poster_add('Facebook posted to user ID : ' . $post_to . ' | Response ID ' . $ret['id']);

                                        //posting flag that posting successfully
                                        $postflg = true;
                                    }

                                } else{

                                    $access_token = $send['access_token'];

                                    $this->facebook->setMethod("POST");
                                    $this->facebook->setAccessToken( $access_token );
                                    $this->facebook->setEndPoint($post_method);
                                    $this->facebook->setNode( $fb_post_to_id);

                                    $ret = $this->getFbJsonResponse($this->facebook->request($send));

                                    if ( isset($ret->error) || !$ret ) {

                                        $this->logs->wpw_auto_poster_add('Facebook error: Posting exception for ' . $post_to . ' : ' . $this->error );
                                        $error_msg = sprintf( __('Facebook: Something was wrong while posting %s', 'wpwautoposter' ), $this->error );
                                        sap_add_notice( 'Facebook:'.$this->error , 'error');
                                        $postflg = false;
                                    }

                                    if( isset( $ret->id ) ) {

                                        $this->model->wpw_auto_poster_insert_posting_log($post->ID, 'fb', $posting_logs_data, $posting_logs_user_details);

                                        //record logs for facebook users are not selected
                                        $this->logs->wpw_auto_poster_add('Facebook posted to user ID : ' . $post_to . ' | Response ID ' . $ret->id );

                                        //posting flag that posting successfully
                                        $postflg = true;

                                    } 
                                    
                                }

                            } catch (Exception $e ) {

                                //record logs exception generated
                                $this->logs->wpw_auto_poster_add('Facebook error: Posting exception for ' . $post_to . ' : ' . $e->__toString());
                                $error_msg = sprintf( __('Facebook: Something was wrong while posting %s', 'wpwautoposter' ), $e->__toString() );
                                sap_add_notice( $error_msg, 'error');

                                if( $postflg != true ){
                                    $postflg = false;
                                }
                                
                            } //end catch
                        } 
                    } //end if to check accesstoken is not empty
                } //end foreach
            } //end if to check post_to is not empty
            //returning post flag
            return $postflg;
        } else {
            //record logs when grant extended permission not set
            $this->logs->wpw_auto_poster_add('Facebook error: Grant extended permissions not set.');
            sap_add_notice( __('Facebook: Please give Grant extended permission before posting to the Facebook.', 'wpwautoposter' ), 'error');
        } //end else
    }

    
    /**
     * Reset Sessions
     * 
     * Resetting the Facebook sessions when the admin clicks on
     * its link within the settings page.
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_fb_reset_session() {

        global $wpw_auto_poster_options;

        unset($_SESSION['wpweb_fb_user_id']);
        unset($_SESSION['wpweb_fb_user_cache']);
        unset($_SESSION['wpweb_fb_user_accounts']);

        unset($_SESSION[WPW_AUTO_POSTER_FB_SESS1]);
        unset($_SESSION[WPW_AUTO_POSTER_FB_SESS2]);
        unset($_SESSION[WPW_AUTO_POSTER_FB_SESS3]);
        unset($_SESSION[WPW_AUTO_POSTER_FB_SESS4]);

        // Check if facebook reset user link is clicked and fb_reset_user is set to 1 and facebook app id is there
        if (isset($_GET['fb_reset_user']) && $_GET['fb_reset_user'] == '1' && !empty($_GET['wpw_fb_app'])) {

            $wpw_fb_app_id = $_GET['wpw_fb_app'];

            // Getting stored fb app data
            $wpw_auto_poster_fb_sess_data = get_option('wpw_auto_poster_fb_sess_data');

            // Getting facebook app users
            $app_users = wpw_auto_poster_get_fb_accounts('all_app_users');

            // Users need to flush from stored data
            $reset_app_users = !empty($app_users[$wpw_fb_app_id]) ? $app_users[$wpw_fb_app_id] : array();

            // Unset perticular app value data and update the option
            if (isset($wpw_auto_poster_fb_sess_data[$wpw_fb_app_id])) {
                unset($wpw_auto_poster_fb_sess_data[$wpw_fb_app_id]);
                update_option('wpw_auto_poster_fb_sess_data', $wpw_auto_poster_fb_sess_data);
            }

            // Get all post type
            $all_post_types = get_post_types(array('public' => true), 'objects');
            $all_post_types = is_array($all_post_types) ? $all_post_types : array();

            // Unset users from settings page
            foreach ($all_post_types as $posttype) {

                //check postype is not object
                if (!is_object($posttype))
                    continue;

                $label = @$posttype->labels->name ? $posttype->labels->name : $posttype->name;
                if ($label == 'Media' || $label == 'media')
                    continue; // skip media

                    
                // Check if user is set for posting in settings page then unset it
                if (isset($wpw_auto_poster_options['fb_type_' . $posttype->name . '_user'])) {

                    // Get stored facebook users according to post type
                    $fb_stored_users = $wpw_auto_poster_options['fb_type_' . $posttype->name . '_user'];

                    // Flusing the App users and taking remaining
                    $new_stored_users = array_diff($fb_stored_users, $reset_app_users);

                    // If empty data then unset option else update remaining
                    if (!empty($new_stored_users)) {
                        $wpw_auto_poster_options['fb_type_' . $posttype->name . '_user'] = $new_stored_users;
                    } else {
                        unset($wpw_auto_poster_options['fb_type_' . $posttype->name . '_user']);
                    }
                } //end if
            } //end foreach

            /*             * ***** Code for selected category FB account ***** */

            // unset selected fb account option for category 
            $cat_selected_social_acc = array();
            $cat_selected_acc = get_option('wpw_auto_poster_category_posting_acct');
            $cat_selected_social_acc = (!empty($cat_selected_acc) ) ? $cat_selected_acc : $cat_selected_social_acc;
            if (!empty($cat_selected_social_acc)) {
                foreach ($cat_selected_social_acc as $cat_id => $cat_social_acc) {
                    if (isset($cat_social_acc['fb'])) {
                        if (!empty($cat_social_acc['fb'])) {
                            $new_cat_stored_users = array_diff($cat_social_acc['fb'], $reset_app_users);
                            if (!empty($new_cat_stored_users)) {
                                $cat_selected_acc[$cat_id]['fb'] = $new_cat_stored_users;
                            } else {
                                unset($cat_selected_acc[$cat_id]['fb']);
                            }
                        } else {
                            unset($cat_selected_acc[$cat_id]['fb']);
                        }
                    }
                }

                // Update autoposter category FB posting account options
                update_option('wpw_auto_poster_category_posting_acct', $cat_selected_acc);
            }

            // Update autoposter options to settings
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        } //end if
    }

    /**
     * Facebook Posting
     * 
     * Handles to facebook posting
     * by post data
     * 
     * @package Social Auto Poster
     * @since 1.5.0
     */
    public function wpw_auto_poster_fb_posting($post) {

        global $wpw_auto_poster_options;

        $prefix = WPW_AUTO_POSTER_META_PREFIX;

        //post to user wall on facebook
        $res = $this->wpw_auto_poster_fb_post_to_userwall($post);

        //if( isset( $res['id'] ) && !empty( $res['id'] ) ) { //check post has been posted on facebook or not
        if (!empty($res)) { //check post has been posted on facebook or not
            //record logs for posting done on facebook
            $this->logs->wpw_auto_poster_add('Facebook posting completed successfully.');

            update_post_meta($post->ID, $prefix . 'fb_published_on_fb', '1');

            // get current timestamp and update meta as published date/time
            $current_timestamp = current_time( 'timestamp' );
            update_post_meta($post->ID, $prefix . 'published_date', $current_timestamp);
            
            return true;
        }

        return false;
    }

    /**
     * Group Tokens
     * 
     * Getting the the tokens from all group which
     * are associated with the connected Facebook account
     * so that the admin can choose to which group
     * he wants to post the submitted and approved reviews to.
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_fb_get_groups_tokens() {

        $fb_app_id = isset($_GET['wpw_fb_app_id']) ? $_GET['wpw_fb_app_id'] : '';

        // Load facebook class
        $facebook = $this->wpw_auto_poster_load_facebook($fb_app_id);

        // Check facebook class is exis or not
        if (!$facebook)
            return false;

        try {
            $fb_groups = $this->facebook->api('/' . $_SESSION['wpweb_fb_user_id'] . '/groups/');
        } catch (Exception $e) {
            return false;
        }

        return $fb_groups;
    }

    public function getFbJsonResponse($rawResponse){

        if($rawResponse === FALSE){
            $this->error = $this->facebook->getError();
            return FALSE;
        }

        $res = json_decode($rawResponse->getBody());
        
        if(isset($res->error)){

            $this->error = $res->error->message;
            if(isset($res->error->error_user_title)){
                $this->error .= "\nError Details : ".$res->error->error_user_title;
            }
            if(isset($res->error->error_user_msg)){
                $this->error .= " : ".$res->error->error_user_msg;
            }
            return false; 
        }

        return $res;
    }

}