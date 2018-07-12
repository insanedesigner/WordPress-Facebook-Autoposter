<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Pinterest Posting Class
 * 
 * Handles all the functions to post pins
 * to a chosen Pinterest Boards.
 * 
 * @package Social Auto Poster
 * @since 2.6.0
 */
class Wpw_Auto_Poster_PIN_Posting {

    public $pinterest, $message, $model, $logs, $_user_cache;

    public function __construct() {

        global $wpw_auto_poster_message_stack, $wpw_auto_poster_model,
        $wpw_auto_poster_logs;

        $this->message = $wpw_auto_poster_message_stack;
        $this->model = $wpw_auto_poster_model;
        $this->logs = $wpw_auto_poster_logs;

        //initialize the session value when data is saved in database
        add_action('init', array($this, 'wpw_auto_poster_pin_initialize'));
    }

    /**
     * Include Pinterest Class
     * 
     * Handles to load pinterest class
     * 
     * @package Social Auto Poster
     * @since 2.6.0
     */
    public function wpw_auto_poster_load_pinterest($app_id = false) {

        global $wpw_auto_poster_options;

        // Getting pinterest apps
        $pin_apps = wpw_auto_poster_get_pin_apps();

        // If app id is not passed then take first pinterest app data
        if (empty($app_id)) {
            $pin_apps_keys = array_keys($pin_apps);
            $app_id = reset($pin_apps_keys);
        }

        // Check whether application id and application secret is set or not
        if (!empty($app_id) && !empty($pin_apps[$app_id])) {

            require_once( WPW_AUTO_POSTER_SOCIAL_DIR . '/pinterest/autoload.php' );
            $this->pinterest = new DirkGroenen\Pinterest\Pinterest($app_id, $pin_apps[$app_id]);
            return true;
        } else {

            return false;
        }
    }

    /**
     * Pinterest Login URL
     * 
     * Getting the login URL from Pinterest.
     * 
     * @package Social Auto Poster
     * @since 2.6.0
     */
    public function wpw_auto_poster_get_pinterest_login_url($app_id = false) {
        $pinterest = $this->wpw_auto_poster_load_pinterest($app_id);
        if (!$pinterest)
            return FALSE;
        // $portvalue = is_ssl() ? 'https://' : 'http://';
        // $redirect_URL = $portvalue . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $redirect_URL = add_query_arg( array('page' => 'wpw-auto-poster-settings' ), admin_url('admin.php') );
        
        $redirect_URL = add_query_arg(array('wpw_pinterest_grant' => 'true', 'wpw_pinterest_app_id' => $app_id), $redirect_URL);
        $loginUrl = $this->pinterest->auth->getLoginUrl($redirect_URL, array('write_public', 'read_public'));
        return $loginUrl;
    }

    /**
     * Assign Pinterest User's all Data to session
     * 
     * Handles to assign user's pinterest data
     * to sessoin & save to database
     * 
     * @package Social Auto Poster
     * @since 2.6.0
     */
    public function wpw_auto_poster_pin_initialize() {

        global $wpw_auto_poster_options;

        if ( ( isset ( $_GET['wpw_pinterest_grant'] ) && $_GET['wpw_pinterest_grant'] == 'true' ) 
            && ( isset($_GET['code']) && isset($_REQUEST['state']) && isset($_GET['wpw_pinterest_app_id'] ) ) ) {

            //record logs for grant extended permission
            $this->logs->wpw_auto_poster_add('Pinterest Grant Extended Permission', true);

            //record logs for get parameters set properly
            $this->logs->wpw_auto_poster_add('Get Parameters Set Properly.');
            $code       = $_GET['code'];
            $state      = $_GET['state'];
            $pin_app_id = $_GET['wpw_pinterest_app_id'];

            try {

                //load pinterest class
                $pinterest = $this->wpw_auto_poster_load_pinterest($pin_app_id);
            } catch (Exception $e) {

                //record logs exception generated
                $this->logs->wpw_auto_poster_add('Pinterest error: ' . $e->getMessage());
                $pinterest = null;
            }

            //check pinterest class is exis or not
            if (!$pinterest)
                return false;

            // Pinterest

            try {

                $token = $this->pinterest->auth->getOAuthToken($code);
            } catch (Exception $e) {

                //record logs exception generated
                $this->logs->wpw_auto_poster_add('Pinterest error: ' . $e->getMessage());
            }

            //$token = $this->pinterest->auth->getOAuthToken($code);
            $me = false;
            
            if(!empty($token->access_token)) {

                $_SESSION['wpweb_pin_user_token'] = $token->access_token;
                
                try {

                    $grant = $this->pinterest->auth->setOAuthToken($token->access_token);

                    $me = $this->pinterest->users->me();

                    //record logs for user id
                    $this->logs->wpw_auto_poster_add('Pinterest User ID : ' . $me->id);
                } catch (Exception $e){

                    //record logs exception generated
                    $this->logs->wpw_auto_poster_add('Pinterest error: ' . $e->getMessage());
                }
                

            }

            //check user is logged in pinterest or not
            if ($me) {

                try {

                    // Proceed knowing you have a logged in user who's authenticated.
                    $_SESSION['wpweb_pin_user_id'] = $me->id;
                    $_SESSION['wpweb_pin_user_name'] = $me->first_name . " " . $me->last_name;
                    $_SESSION['wpweb_pin_user_url'] = $me->url;
                    $boards = $this->pinterest->users->getMeBoards();
                    $i = 0;
                    // For record
                    $boardList = $selectBoard = array();
                    
                    foreach ($boards as $boardu) {

                        $board = str_replace('https://www.pinterest.com/', '', @$boardu->url);

                        // Get board details
                        if (substr($board, -1, strlen($board))) {
                            $board = substr($board, 0, -1);
                        }

                        $boardList[$boardu->id] = $board;
                        $selectBoard[$board] = $boardu->name;
                    }
                    $_SESSION['wpweb_pin_user_boards'] = $boardList;
                    // For record
                    $_SESSION['wpweb_pin_user_boards_select'] = $selectBoard;

                    // Start code to manage session from database
                    $wpw_auto_poster_pin_sess_data = get_option('wpw_auto_poster_pin_sess_data');
                    // Checking if the grant extend is already done or not
                    if (!isset($wpw_auto_poster_pin_sess_data[$pin_app_id])) {

                        $sess_data = array(
                            'wpw_auto_poster_pin_user_id' => $_SESSION['wpweb_pin_user_id'],
                            'wpw_auto_poster_pin_user_name' => $_SESSION['wpweb_pin_user_name'],
                            'wpw_auto_poster_pin_user_boards' => $_SESSION['wpweb_pin_user_boards'],
                            'wpw_auto_poster_pin_token' => $_SESSION['wpweb_pin_user_token'],
                        );
                        if ($pin_app_id) {

                            // Save Multiple Accounts
                            $wpw_auto_poster_pin_sess_data[$pin_app_id] = $sess_data;

                            // Update session data to options
                            update_option('wpw_auto_poster_pin_sess_data', $wpw_auto_poster_pin_sess_data);

                            // Record logs for session data updated to options
                            $this->logs->wpw_auto_poster_add('Session Data Updated to Options');
                        } else {
                            // Record logs when app id is not found
                            $this->logs->wpw_auto_poster_add("Pinterest error: The App Id {$pin_app_id} does not exist.");
                        }
                    }// end code to manage session from database
                    // Record logs for grant extend successfully
                    $this->logs->wpw_auto_poster_add('Grant Extended Permission Successfully.');
                } catch (Exception $e) {

                    //record logs exception generated
                    $this->logs->wpw_auto_poster_add('Pinterest error: ' . $e->__toString());

                    //user is null
                    $me = null;
                } //end catch
            } //end if to check user is not empty
            //set tab selected
            $this->message->add_session('poster-selected-tab', 'pinterest');

            //redirect to proper page
            wp_redirect(add_query_arg(array('codex_pinterest_grant' => false, 'code' => false, 'state' => false, 'client_id' => false)));
            exit;
        } //end if to check page is set and wpw_fb_grant is set & its true & code is set & state is set in $_GET
    }

    /**
     * Reset Sessions
     * 
     * Resetting the Pinterest sessions when the admin clicks on
     * its link within the settings page.
     * 
     * @package Social Auto Poster
     * @since 2.6.0
     */
    public function wpw_auto_poster_pin_reset_session() {

        global $wpw_auto_poster_options;

        unset($_SESSION['wpweb_pin_user_boards']);
        unset($_SESSION['wpweb_pin_user_boards_select']);
        unset($_SESSION['wpweb_pin_user_id']);
        unset($_SESSION['wpweb_pin_user_name']);
        unset($_SESSION['wpweb_pin_user_url']);
        unset($_SESSION['wpweb_pin_user_token']);

        // Check if pinterest reset user link is clicked and pin_reset_user is set to 1 and pinterest app id is there
        if (isset($_GET['pin_reset_user']) && $_GET['pin_reset_user'] == '1' && !empty($_GET['wpw_pin_app'])) {

            $wpw_pin_app_id = $_GET['wpw_pin_app'];

            // Getting stored pin app data
            $wpw_auto_poster_pin_sess_data = get_option('wpw_auto_poster_pin_sess_data');

            // Getting pinterest app users
            $app_users = wpw_auto_poster_get_pin_accounts('all_app_users_with_boards');

            // Users need to flush from stored data
            $reset_app_users = !empty($app_users[$wpw_pin_app_id]) ? $app_users[$wpw_pin_app_id] : array();

            // Unset perticular app value data and update the option
            if (isset($wpw_auto_poster_pin_sess_data[$wpw_pin_app_id])) {
                unset($wpw_auto_poster_pin_sess_data[$wpw_pin_app_id]);
                update_option('wpw_auto_poster_pin_sess_data', $wpw_auto_poster_pin_sess_data);
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
                if (isset($wpw_auto_poster_options['pin_type_' . $posttype->name . '_user'])) {

                    // Get stored pinterest users according to post type
                    $pin_stored_users = $wpw_auto_poster_options['pin_type_' . $posttype->name . '_user'];

                    // Flusing the App users and taking remaining
                    $new_stored_users = array_diff($pin_stored_users, $reset_app_users);

                    // If empty data then unset option else update remaining
                    if (!empty($new_stored_users)) {
                        $wpw_auto_poster_options['pin_type_' . $posttype->name . '_user'] = $new_stored_users;
                    } else {
                        unset($wpw_auto_poster_options['pin_type_' . $posttype->name . '_user']);
                    }
                } //end if
            } //end foreach
            // Update autoposter options to settings
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        } //end if
    }

    /**
     * Pinterest Posting
     * 
     * Handles to pinterest posting
     * by post data
     * 
     * @package Social Auto Poster
     * @since 1.5.0
     */
    public function wpw_auto_poster_pin_posting($post) {

        global $wpw_auto_poster_options;

        $prefix = WPW_AUTO_POSTER_META_PREFIX;

        //post to user board on pinterest
        $res = $this->wpw_auto_poster_pin_post_to_userwall($post);

        //if( isset( $res['id'] ) && !empty( $res['id'] ) ) { //check post has been posted on pinterest or not
        if (!empty($res)) { //check post has been posted on pinterest or not
            //record logs for posting done on pinterest
            $this->logs->wpw_auto_poster_add('Pinterest posting completed successfully.');

            update_post_meta($post->ID, $prefix . 'pin_published_on_pin', '1');

            // get current timestamp and update meta as published date/time
            $current_timestamp = current_time( 'timestamp' );
            update_post_meta($post->ID, $prefix . 'published_date', $current_timestamp);
            
            return true;
        }

        return false;
    }

    /**
     * Post to User board on Pinterest
     * 
     * Handles to post content on Pinterest user board
     * 
     * @package Social Auto Poster
     * @since 2.6.0
     */
    public function wpw_auto_poster_pin_post_to_userwall($post) {

        global $wpw_auto_poster_options;

        // Get stored pin app grant data
        $wpw_auto_poster_pin_sess_data = get_option('wpw_auto_poster_pin_sess_data');

        // Check pinterest grant extended permission is set ot not
        if (!empty($wpw_auto_poster_pin_sess_data)) {

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
            $ispublished = get_post_meta($post->ID, $prefix . 'pin_published_on_pin', true);

            // Get all selected tags for selected post type for hashtags support
            if(isset($wpw_auto_poster_options['pin_post_type_tags']) && !empty($wpw_auto_poster_options['pin_post_type_tags'])) {

                $custom_post_tags = $wpw_auto_poster_options['pin_post_type_tags'];
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
            if(isset($wpw_auto_poster_options['pin_post_type_cats']) && !empty($wpw_auto_poster_options['pin_post_type_cats'])) {

                $custom_post_cats = $wpw_auto_poster_options['pin_post_type_cats'];
                if(isset($custom_post_cats[$post_type]) && !empty($custom_post_cats[$post_type])){  
                    foreach($custom_post_cats[$post_type] as $key => $category){
                        $term_list = wp_get_post_terms( $post->ID, $category, array("fields" => "names") );
                        foreach($term_list as $term_single) {
                            $cats_arr[] = $term_single;
                        }
                    }
                    
                }
            }

            if (!isset($wpw_auto_poster_options['prevent_post_pin_metabox'])) { //check if prevent metabox is not enable
                $wpw_auto_poster_pin_user_id = get_post_meta($post->ID, $prefix . 'pin_user_id');
                $wpw_auto_poster_custom_link = get_post_meta($post->ID, $prefix . 'pin_custom_post_link', true);
                $wpw_auto_poster_custom_note = get_post_meta($post->ID, $prefix . 'pin_custom_status_msg', true);
                $wpw_auto_poster_custom_img = get_post_meta($post->ID, $prefix . 'pin_post_image', true);
            } //end if
            // Getting all pinterest apps
            $pin_apps = wpw_auto_poster_get_pin_apps();

            // Getting all stored pinterest auth token
            $pin_auth_token = wpw_auto_poster_get_pin_accounts('all_auth_tokens');

            // Pinterest user id on whose wall the post will be posted
            $pin_user_ids = '';
            //check there is pinterest user ids are set and not empty in metabox
            if (isset($wpw_auto_poster_pin_user_id) && !empty($wpw_auto_poster_pin_user_id)) {
                //users from metabox
                $pin_user_ids = $wpw_auto_poster_pin_user_id;

                /*                 * *** Backward Compatibility Code Starts **** */
                // If user account is selected in meta so creating data accoring to new method ( Will be helpfull when scheduling is done )
                if (!empty($pin_user_ids)) {

                    $pin_first_app_key = !empty($wpw_auto_poster_options['pinterest_keys'][0]['app_id']) ? $wpw_auto_poster_options['pinterest_keys'][0]['app_id'] : '';

                    if (!empty($pin_first_app_key)) {
                        foreach ($pin_user_ids as $pin_user_key => $pin_user_data) {
                            if (strpos($pin_user_data, '|') === false) {
                                $pin_user_ids[$pin_user_key] = $pin_user_data . '|' . $pin_first_app_key;
                            }
                        }
                    }
                }
                /*                 * *** Backward Compatibility Code Ends **** */
            } //end if


            /******* Code to posting to selected category Pinterest account ******/

            // get all categories for custom post type
            $categories = wpw_auto_poster_get_post_categories_by_ID( $post_type, $post->ID );
             
            // Get all selected account list from category
            $category_selected_social_acct = get_option('wpw_auto_poster_category_posting_acct');
            // IF category selected and category social account data found
            if (!empty($categories) && !empty($category_selected_social_acct) && empty($pin_user_ids)) {
                $pin_clear_cnt = true;
                // GET Pinterest user account ids from post selected categories
                foreach ($categories as $key => $term_id) {

                    $cat_id = $term_id;
                    // Get Pinterest user account ids form selected category  
                    if (isset($category_selected_social_acct[$cat_id]['pin']) && !empty($category_selected_social_acct[$cat_id]['pin'])) {
                        // clear pinterest user data once
                        if ($pin_clear_cnt)
                            $pin_user_ids = array();
                        $pin_user_ids = array_merge($pin_user_ids, $category_selected_social_acct[$cat_id]['pin']);
                        $pin_clear_cnt = false;
                    }
                }
                if( !empty( $pin_user_ids ) ){
                    $pin_user_ids = array_unique($pin_user_ids);
                }
            }

            //check pinterest user ids are empty in metabox and set in settings page
            if (empty($pin_user_ids) && isset($wpw_auto_poster_options['pin_type_' . $post_type . '_user']) && !empty($wpw_auto_poster_options['pin_type_' . $post_type . '_user'])) {
                //users from settings
                $pin_user_ids = $wpw_auto_poster_options['pin_type_' . $post_type . '_user'];
            } //end if
            //check pinterest user ids are empty selected for posting
            if (empty($pin_user_ids)) {

                //record logs for pinterest users are not selected
                $this->logs->wpw_auto_poster_add('Pinterest error: user not selected for posting.');
                // display error notice on post page
                sap_add_notice( __('Pinterest: You have not selected any user for the posting.', 'wpwautoposter' ), 'error');
                //return false
                return false;
            } //end if to check user ids are empty
            //convert user ids to single array
            $post_to_users = (array) $pin_user_ids;

            //custom status message to post on pinterest
            $custom_msg = ( isset($wpw_auto_poster_custom_note) && $wpw_auto_poster_custom_note ) ? $wpw_auto_poster_custom_note : $wpw_auto_poster_options["pin_custom_template"];

            //remove html entity from custom message
            $custom_msg = $this->model->wpw_auto_poster_html_decode($custom_msg);

            //get post title
            $title = $post->post_title;
            //remove html entity from title
            $title = $this->model->wpw_auto_poster_html_decode($title);

             //post link for posting to user board pin
            $postlink = isset($wpw_auto_poster_custom_link) && !empty($wpw_auto_poster_custom_link) ? $wpw_auto_poster_custom_link : '';
           
            //if custom link is set or not
            $customlink = !empty($postlink) ? 'true' : 'false';
            //do url shortner
            $postlink = $this->model->wpw_auto_poster_get_short_post_link($postlink, $unique, $post->ID, $customlink, 'pin');

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


            // get post categories
            /*$hashcats = array();
            foreach ((get_the_category($post->ID)) as $category) {
                $hashcats[] = $category->cat_name;
            }*/
            $hashcats = (!empty($cats_arr) ) ? '#' . implode(' #', $cats_arr) : '';

            // check if custom message is empty if yes than set caption as post title
            if (!empty($custom_msg)) {

                $search_arr = array('{first_name}', '{last_name}', '{title}', '{link}', '{excerpt}', '{sitename}', '%title%', '%link%', '{hashtags}', '{hashcats}','{content}');
                $replace_arr = array($first_name, $last_name, $title, $postlink, $excerpt, get_option('blogname'), $title, $postlink, $hashtags, $hashcats,$post_content);

                $code_matches = array();
    
                // check if template tags contains {content-numbers}
                if( preg_match_all( '/\{(content)(-)(\d*)\}/', $custom_msg, $code_matches ) ) {
                    $trim_tag = $code_matches[0][0];
                    $trim_length = $code_matches[3][0];
                    $post_content = substr( $post_content, 0, $trim_length);
                    $search_arr[] = $trim_tag;
                    $replace_arr[] = $post_content;
                }

                $notes = str_replace($search_arr, $replace_arr, $custom_msg);
            } else {
                $notes = $title;
            }


            /*             * ************
             * Image Priority
             * If metabox image set then take from metabox
             * If metabox image is not set then take from featured image
             * If featured image is not set then take from settings page
             * ************ */

            //get featured image from post / page / custom post type
            $post_featured_img['src'] = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
            $post_featured_img['path'] = get_attached_file(get_post_thumbnail_id($post->ID), 'full');

            //check custom image is set in meta and not empty
            if (isset($wpw_auto_poster_custom_img['src']) && !empty($wpw_auto_poster_custom_img['src'])) {
                $img_src = $wpw_auto_poster_custom_img['src'];

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
            } elseif (isset($wpw_auto_poster_options['pin_custom_img']) && !empty($wpw_auto_poster_options['pin_custom_img'])) {
                //else get post image from settings page
                $img_src = $wpw_auto_poster_options['pin_custom_img'];
                $site_url = site_url();
                
                if (strpos($img_src, site_url()) !== false) {
                    $imagePath = str_replace($site_url, "", $img_src);
                    $img_path = '..' . $imagePath;
                } else {
                    $img_path = wpw_auto_poster_get_image_path( $img_src );
                }
            }

            //posting logs data
            $posting_logs_data = array(
                'notes' => $notes,
                'image' => $img_src,
                'link' => $postlink,
            );

            $send = array(
                'note' => mb_substr($notes, 0, 499),
                'link' => $postlink
            );

            if(isset($img_path) && !empty($img_path)){
               $send['image'] = $img_path;
            }

            //initial value of posting flag
            $postflg = false;

            if (!empty($post_to_users)) {
                $posting_logs_user_details = array();
                foreach ($post_to_users as $post_to) {

                    $pin_post_app_arr = explode('|', $post_to);
                    // Pinterest Posting board Id
                    $pin_post_to_board_id = isset($pin_post_app_arr[0]) ? $pin_post_app_arr[0] : '';
                    // Pinterest App Id
                    $pin_post_app_id = isset($pin_post_app_arr[1]) ? $pin_post_app_arr[1] : '';
                    $send['board'] = $pin_post_to_board_id;

                    //check there is auth token is set for pinterest user
                    if (isset($pin_auth_token[$pin_post_app_id])) {
                        $auth_token = $pin_auth_token[$pin_post_app_id];
                    }

                    if(isset($wpw_auto_poster_pin_sess_data[$pin_post_app_id]) && !empty($wpw_auto_poster_pin_sess_data[$pin_post_app_id])){
                        $account_name = $wpw_auto_poster_pin_sess_data[$pin_post_app_id]['wpw_auto_poster_pin_user_name'];
                        if(isset($wpw_auto_poster_pin_sess_data[$pin_post_app_id]['wpw_auto_poster_pin_user_boards']) && !empty($wpw_auto_poster_pin_sess_data[$pin_post_app_id]['wpw_auto_poster_pin_user_boards'])){
                            $board_name = $wpw_auto_poster_pin_sess_data[$pin_post_app_id]['wpw_auto_poster_pin_user_boards'][$pin_post_to_board_id];
                        }
                    }

                    // User details
                    if(!empty($account_name) && !empty($board_name)) {
                        $posting_logs_user_details = array(
                            'display_name' => $account_name." - ".$board_name,
                        );
                    }

                    try {

                        $pinterest = $this->wpw_auto_poster_load_pinterest($pin_post_app_id);
                        if (!$pinterest)
                            return FALSE;
                        $this->pinterest->auth->setOAuthToken($auth_token);

                        $pub = $this->pinterest->pins->create($send);

                        //posting logs store into database
                        $this->model->wpw_auto_poster_insert_posting_log($post->ID, 'pin', $posting_logs_data, $posting_logs_user_details);
                        //record logs for data posted on pinterest boards
                        $this->logs->wpw_auto_poster_add('Pinterest post data : ' . $pub->note);
                        $postflg = true;
                    } catch (\Exception $e) {

                        $this->logs->wpw_auto_poster_add('Pinterest error: Posting exception for ' . $post_to . ' : ' . $e->getMessage());
                        sap_add_notice( sprintf( __('Pinterest: Something was wrong while posting %s', 'wpwautoposter' ), $e->getMessage() ), 'error');
                        $postflg = false;
                    }
                }
            }

            return $postflg;
        } else {
            //record logs when grant extended permission not set
            $this->logs->wpw_auto_poster_add('Pinterest error: Grant extended permissions not set.');
            sap_add_notice( __('Pinterest: Please give Grant extended permission before posting to the Pinterest.', 'wpwautoposter' ), 'error');
        } //end else
    }

}