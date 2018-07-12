<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Admin Class
 *
 * Handles generic Admin functionality and AJAX requests.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
class Wpw_Auto_Posting_AdminPages {

    public $scripts, $model, $render, $message, $logs,
            $fbposting, $twposting, $liposting, $insposting, $admin, $pinposting,$fb_andrd_posting;

    public function __construct() {

        global $wpw_auto_poster_scripts, $wpw_auto_poster_model, $wpw_auto_poster_render, $wpw_auto_poster_message_stack,
        $wpw_auto_poster_fb_posting, $wpw_auto_poster_tw_posting, $wpw_auto_poster_li_posting, $wpw_auto_poster_ba_posting,
        $wpw_auto_poster_tb_posting, $wpw_auto_poster_ins_posting, $wpw_auto_poster_logs, $wpw_auto_poster_admin, $wpw_auto_poster_pin_posting, $wpw_auto_poster_fb_andrd_posting;

        $this->scripts = $wpw_auto_poster_scripts;
        $this->model = $wpw_auto_poster_model;
        $this->render = $wpw_auto_poster_render;
        $this->message = $wpw_auto_poster_message_stack;
        $this->logs = $wpw_auto_poster_logs;
        $this->admin = $wpw_auto_poster_admin;

        //social posting class objects
        $this->fbposting = $wpw_auto_poster_fb_posting;
        $this->twposting = $wpw_auto_poster_tw_posting;
        $this->liposting = $wpw_auto_poster_li_posting;
        $this->tbposting = $wpw_auto_poster_tb_posting;
        $this->baposting = $wpw_auto_poster_ba_posting;
        $this->insposting = $wpw_auto_poster_ins_posting;
        $this->pinposting = $wpw_auto_poster_pin_posting;
        $this->fb_andrd_posting = $wpw_auto_poster_fb_andrd_posting;
    }

    /**
     * Post to Social Medias
     *
     * Handles to post to social media
     *
     * @package Social Auto Poster
     * @since 1.5.0
     */
    public function wpw_auto_poster_social_posting($post, $scheduled = false) {

        global $wpw_auto_poster_options, $postedstr, $schedulepoststr;

        // get all supported network list array
        $all_social_networks = $this->model->wpw_auto_poster_get_social_type_name();

        $prefix = WPW_AUTO_POSTER_META_PREFIX;

        $postedstr = $schedulepoststr = array();

        $postid = $post->ID;

        $post_type = $post->post_type; // Post type

        // get selected categories slugs for a post
        $post_catgeories = wpw_auto_poster_get_post_categories( $post_type, $postid );

        /** Code to exclude posting for selected category start **/
        $main_exclude_arr = array(); // define main category exclude array for a post.


        // Initially set exclude flag to false at the begining
        $main_exclude_arr['fb'] = $main_exclude_arr['tw'] = $main_exclude_arr['li'] = $main_exclude_arr['tb'] = $main_exclude_arr['ba'] = $main_exclude_arr['ins'] = $main_exclude_arr['pin'] = false;

        // Loop all the supported social networks
        foreach($all_social_networks as $slug => $label) {

            // get selected categories to exclude for each social network
            $exclude_cats = !empty($wpw_auto_poster_options[$slug.'_exclude_cats']) ? $wpw_auto_poster_options[$slug.'_exclude_cats'] : array();

            // Loop through all the categories of a particualr post.
            foreach($post_catgeories as $category) {

                // Check if excluded category is selected for the current post type.
                if(!empty($exclude_cats[$post_type])) {
                    // If atleast one excluded category matches with the post categories than make flag as true
                    if(in_array($category, $exclude_cats[$post_type])){

                        // make social network exclude flag true, if atleast one excluded category matches
                        $main_exclude_arr[$slug] = true;
                        continue;
                    }
                }
            }
        }

        /** Code to exclude posting for selected category end **/

        //Facebook Posting
        $facebookarr = !empty($wpw_auto_poster_options['enable_facebook_for']) ? $wpw_auto_poster_options['enable_facebook_for'] : array();

        //get post published on facebook
        $fb_published = get_post_meta($postid, $prefix . 'fb_published_on_fb', true);

        $schedule_post_to = get_post_meta($postid, $prefix . 'schedule_wallpost', true);
        $schedule_post_to = !empty($schedule_post_to) ? $schedule_post_to : array();

        $post_to_facebook = get_post_meta($postid, $prefix . 'post_to_facebook', true);

        //Check If post is already published and there is disable from metabox but it has checked in backend
        //then it will post to social site when the post is going to published first time when created new
        if ((!empty($wpw_auto_poster_options['enable_facebook']) && (!isset($fb_published) || $fb_published == false ) && in_array($post->post_type, $facebookarr) ) || ( isset($_POST[$prefix . 'post_to_facebook']) && $_POST[$prefix . 'post_to_facebook'] == 'on' ) || ( $scheduled === true && $post_to_facebook == 'on' )) {

			if( !$main_exclude_arr['fb'] ) {

	            if (empty($wpw_auto_poster_options['schedule_wallpost_option'])) { // Check schedule option is "Instantly"
                //record logs for facebook posting
                $this->logs->wpw_auto_poster_add('Facebook Instant Posting | ' . $post->post_type . ' | ' . $postid, true);

	                //post to user wall on facebook
	                $fb_result = $this->fbposting->wpw_auto_poster_fb_posting($post);
	                if ($fb_result) {
	                    $postedstr[] = 'fb';
	                }
	            } else {

	                if (!in_array('facebook', $schedule_post_to)) {
	                    $schedule_post_to[] = 'facebook';
	                }
	                $schedulepoststr[] = 'fb';

	                //Update facebook status to scheduled
	                update_post_meta($postid, $prefix . 'fb_published_on_fb', 2);
	            }
			}
        }

        
        //Twitter Posting
        $twitterarr = !empty($wpw_auto_poster_options['enable_twitter_for']) ? $wpw_auto_poster_options['enable_twitter_for'] : array();

        $tw_published = get_post_meta($postid, $prefix . 'tw_status', true);

        //Check If post is already published and there is disable from metabox but it has checked in backend
        //then it will post to social site when the post is going to published first time when created new
        if ((!empty($wpw_auto_poster_options['enable_twitter']) && (!isset($tw_published) || $tw_published == false ) && in_array($post->post_type, $twitterarr) ) || ( isset($_POST[$prefix . 'post_to_twitter']) && $_POST[$prefix . 'post_to_twitter'] == 'on' )) {

			if( !$main_exclude_arr['tw'] ) {

	            if (empty($wpw_auto_poster_options['schedule_wallpost_option'])) { // Check schedule option is "Instantly"
                    
                    //record logs for twitter posting
                    $this->logs->wpw_auto_poster_add('Twitter Instant Posting | ' . $post->post_type . ' | ' . $postid, true);

	                //post to twitter
	                $tw_result = $this->twposting->wpw_auto_poster_tw_posting($post);
	                if ($tw_result) {
	                    $postedstr[] = 'tw';
	                }
	            } else {

	                if (!in_array('twitter', $schedule_post_to)) {
	                    $schedule_post_to[] = 'twitter';
	                }
	                $schedulepoststr[] = 'tw';
	                //Update twitter status to scheduled
	                update_post_meta($postid, $prefix . 'tw_status', 2);

	            }
			}
        }

        
        //LinkedIn Posting
        $linkedinarr = !empty($wpw_auto_poster_options['enable_linkedin_for']) ? $wpw_auto_poster_options['enable_linkedin_for'] : array();

        $li_published = get_post_meta($postid, $prefix . 'li_status', true);


        //Check If post is already published and there is disable from metabox but it has checked in backend
        //then it will post to social site when the post is going to published first time when created new
        if ((!empty($wpw_auto_poster_options['enable_linkedin']) && (!isset($li_published) || $li_published == false ) && in_array($post->post_type, $linkedinarr) ) || ( isset($_POST[$prefix . 'post_to_linkedin']) && $_POST[$prefix . 'post_to_linkedin'] == 'on' )) {
			if( !$main_exclude_arr['li'] ) {

	            if (empty($wpw_auto_poster_options['schedule_wallpost_option'])) { // Check schedule option is "Instantly"

                    //record logs for linkedin posting
                    $this->logs->wpw_auto_poster_add('LinkedIn Instant Posting | ' . $post->post_type . ' | ' . $postid, true);

	                //post to linkedin
	                $li_result = $this->liposting->wpw_auto_poster_li_posting($post);
	                if ($li_result) {
	                    $postedstr[] = 'li';
	                }
	            } else {

	                if (!in_array('linkedin', $schedule_post_to)) {
	                    $schedule_post_to[] = 'linkedin';
	                }
	                $schedulepoststr[] = 'li';
	                //Update linkedin status to scheduled
	                update_post_meta($postid, $prefix . 'li_status', 2);
	            }
			}
        }

        
        //Tumblr Posting
        $tumblrarr = !empty($wpw_auto_poster_options['enable_tumblr_for']) ? $wpw_auto_poster_options['enable_tumblr_for'] : array();

        $tb_published = get_post_meta($postid, $prefix . 'tb_status', true);

        //Check If post is already published and there is disable from metabox but it has checked in backend
        //then it will post to social site when the post is going to published first time when created new
        if ((!empty($wpw_auto_poster_options['enable_tumblr']) && (!isset($tb_published) || $tb_published == false ) && in_array($post->post_type, $tumblrarr) ) || ( isset($_POST[$prefix . 'post_to_tumblr']) && !empty($_POST[$prefix . 'post_to_tumblr']) )) {
			if( !$main_exclude_arr['tb'] ) {

	            if (empty($wpw_auto_poster_options['schedule_wallpost_option'])) { // Check schedule option is "Instantly"

                    //record logs for Tumblr posting
                    $this->logs->wpw_auto_poster_add('Tumblr Instant Posting | ' . $post->post_type . ' | ' . $postid, true);

	                //post to tumblr
	                $tb_result = $this->tbposting->wpw_auto_poster_tb_posting($post);
	                if ($tb_result) {
	                    $postedstr[] = 'tb';
	                }
	            } else {

	                if (!in_array('tumblr', $schedule_post_to)) {
	                    $schedule_post_to[] = 'tumblr';
	                }
	                $schedulepoststr[] = 'tb';
	                //Update tumblr status to scheduled
	                update_post_meta($postid, $prefix . 'tb_status', 2);
	            }
			}
        }

        
        //bufferapp Posting
        $bufferapparr = !empty($wpw_auto_poster_options['enable_bufferapp_for']) ? $wpw_auto_poster_options['enable_bufferapp_for'] : array();

        $ba_published = get_post_meta($postid, $prefix . 'ba_status', true);

        if ((!empty($wpw_auto_poster_options['enable_bufferapp']) && (!isset($ba_published) || $ba_published == false ) && in_array($post->post_type, $bufferapparr) ) || ( isset($_POST[$prefix . 'post_to_bufferapp']) && !empty($_POST[$prefix . 'post_to_bufferapp']) )) { //if tumblr is seleectd then post to bufferapp account
            if( !$main_exclude_arr['ba'] ) {
				if (empty($wpw_auto_poster_options['schedule_wallpost_option'])) { // Check schedule option is "Instantly"

                    //record logs for BufferApp posting
                    $this->logs->wpw_auto_poster_add('BufferApp Instant Posting | ' . $post->post_type . ' | ' . $postid, true);

	                //post to bufferapp
	                $ba_result = $this->baposting->wpw_auto_poster_ba_posting($post);
	                if ($ba_result) {
	                    $postedstr[] = 'ba';
	                }
	            } else {

	                if (!empty($_SESSION['wpw_auto_poster_ba_user_id'])) {

	                    if (!in_array('bufferapp', $schedule_post_to)) {
	                        $schedule_post_to[] = 'bufferapp';
	                    }
	                    $schedulepoststr[] = 'ba';
	                    //Update bufferapp status to scheduled
	                    update_post_meta($postid, $prefix . 'ba_status', 2);
	                }
	            }
			}
        }

        
         //Instagram Posting
        $instaarr = !empty($wpw_auto_poster_options['enable_instagram_for']) ? $wpw_auto_poster_options['enable_instagram_for'] : array();
        $ins_published = get_post_meta($postid, $prefix . 'ins_published_on_ins', true);

        //Check If post is already published and there is disable from metabox but it has checked in backend
        //then it will post to social site when the post is going to published first time when created new
        if ((!empty($wpw_auto_poster_options['enable_instagram']) && (!isset($ins_published) || $ins_published == false ) && in_array($post->post_type, $instaarr) ) || ( isset($_POST[$prefix . 'post_to_instagram']) && $_POST[$prefix . 'post_to_instagram'] == 'on' )) {
			if( !$main_exclude_arr['ins'] ) {
	            if (empty($wpw_auto_poster_options['schedule_wallpost_option'])) { // Check schedule option is "Instantly"

                    $this->logs->wpw_auto_poster_add('Instagram Instant Posting | ' . $post->post_type . ' | ' . $postid, true);

	                //post to instagram
	                $ins_result = $this->insposting->wpw_auto_poster_ins_posting($post);
	                if ($ins_result) {
	                    $postedstr[] = 'ins';
	                }
	            } else {

	                if (!in_array('instagram', $schedule_post_to)) {
	                    $schedule_post_to[] = 'instagram';
	                }
	                $schedulepoststr[] = 'ins';
	                //Update instagram status to scheduled
	                update_post_meta($postid, $prefix . 'ins_published_on_ins', 2);
	            }
			}
        }

        //Pinterest Posting
        $pinterestarr = !empty($wpw_auto_poster_options['enable_pinterest_for']) ? $wpw_auto_poster_options['enable_pinterest_for'] : array();

        //get post published on pinterest
        $pin_published = get_post_meta($postid, $prefix . 'pin_published_on_pin', true);

        $post_to_pinterest = get_post_meta($postid, $prefix . 'post_to_pinterest', true);

        //Check If post is already published and there is disable from metabox but it has checked in backend
        //then it will post to social site when the post is going to published first time when created new
        if ((!empty($wpw_auto_poster_options['enable_pinterest']) && (!isset($pin_published) || $pin_published == false ) && in_array($post->post_type, $pinterestarr) ) || ( isset($_POST[$prefix . 'post_to_pinterest']) && $_POST[$prefix . 'post_to_pinterest'] == 'on' ) || ( $scheduled === true && $post_to_pinterest == 'on' )) {
			if( !$main_exclude_arr['pin'] ) {

	            if (empty($wpw_auto_poster_options['schedule_wallpost_option'])) { // Check schedule option is "Instantly"

                    //record logs for pinterest posting
                    $this->logs->wpw_auto_poster_add('Pinterest Instant Posting | ' . $post->post_type . ' | ' . $postid, true);

	                //post to user wall on pinterest
	                $pin_result = $this->pinposting->wpw_auto_poster_pin_posting($post);
	                if ($pin_result) {
	                    $postedstr[] = 'pin';
	                }
	            } else {

	                if (!in_array('pinterest', $schedule_post_to)) {
	                    $schedule_post_to[] = 'pinterest';
	                }
	                $schedulepoststr[] = 'pin';
                    //Update pinterest status to scheduled
	                update_post_meta($postid, $prefix . 'pin_published_on_pin', 2);
	            }
			}
        }

        //update schedule wallpost
        update_post_meta($postid, $prefix . 'schedule_wallpost', $schedule_post_to);
    }

    /**
     * Post to Social Medias
     *
     * Handles to post to social media
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_post_to_social_media($postid, $post) {

        global $wpw_auto_poster_options;

        //If post type is autopostlog then return auto posting
        if ($post->post_type == WPW_AUTO_POSTER_LOGS_POST_TYPE)
            return $postid;

        $post_type_object = get_post_type_object($post->post_type);

        if (( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) // Check Autosave
			|| ( wpw_auto_poster_extra_security($postid, $post) == true ) // check extra securiry
            || ( $post->post_status != 'publish' && $post->post_status != 'future' ) // allow only publish and future post status
        ) {
            return $postid;
        }

        // code to stop instant posting if wordpress post status is future
        if( $post->post_status == 'future' && $wpw_auto_poster_options['schedule_wallpost_option'] == "") {
            return $postid;
        }
        
        $prefix = WPW_AUTO_POSTER_META_PREFIX;

        // Update Hour for Individual Post in Hourly Posting
        $wpw_auto_poster_select_hour = isset($_POST[$prefix . 'select_hour']) ? $_POST[$prefix . 'select_hour'] : '';
        $wpw_auto_poster_select_hour = ( !empty( $wpw_auto_poster_select_hour ) ) ? strtotime( $wpw_auto_poster_select_hour ) : '';

        if( !empty( $wpw_auto_poster_select_hour ) ) {
            update_post_meta( $postid, $prefix . 'select_hour', $wpw_auto_poster_select_hour);
        } else{

            if( !empty($wpw_auto_poster_options) && $wpw_auto_poster_options['schedule_wallpost_option'] == "hourly") { 
                 $next_scheduled_cron = wp_next_scheduled( 'wpw_auto_poster_scheduled_cron' );
                 update_post_meta( $postid, $prefix . 'select_hour', $next_scheduled_cron );
            } 
        }
        
        
        // apply filters for verify send wall posr after post create/update
        $has_send_wall_post = apply_filters('wpw_auto_poster_verify_send_wall_post', true, $post, $wpw_auto_poster_options);

        if ($has_send_wall_post) { // Verified for send wall post
            //posting to all social medias
            $this->wpw_auto_poster_social_posting($post);
        }

        //redirect to custom url after saving post
        add_filter('redirect_post_location', array($this, 'wpw_auto_poster_redirect_save_post'));
    }

    /**
     * Add Schedule posting with social media
     *
     * Handles to work posting on social media when
     * someone set schedule for particular post
     * at that time it will automatic posted on social medias
     * whichever is selected in settings page
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_schedule_posting($postid) {

        global $wpw_auto_poster_options;

        $post = get_post($postid);

        if ($post->post_type == 'revision')
            return; // Imp Line //  if revision dont do anything.
        if ($post->post_status != 'publish')
            return;

        $prefix = WPW_AUTO_POSTER_META_PREFIX;

        // apply filters for verify send wall post after post create/update
        $has_send_wall_post = apply_filters('wpw_auto_poster_verify_send_wall_post', true, $post, $wpw_auto_poster_options);

        if ($has_send_wall_post) { // Verified for send wall post
            //posting to all social medias
            $this->wpw_auto_poster_social_posting($post, true);
        }
    }

    /**
     * Redirect After Save Post
     *
     * Handles to redirect after saving post
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_redirect_save_post($loc) {

        global $postedstr, $schedulepoststr;

        if (!empty($postedstr)) {

            return add_query_arg('wpwautoposteron', $postedstr, $loc);
        } else if (!empty($schedulepoststr)) {

            return add_query_arg('wpwautoposterscheduleon', $schedulepoststr, $loc);
        } else {

            return $loc;
        }
    }

    /**
     * Admin Notices
     *
     * Handles to show admin notices after successfully
     * posted to social networks
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_admin_notices() {

        if (isset($_GET['wpwautoposteron']) || isset($_GET['wpwautoposterscheduleon'])) {

            $postedon = isset($_GET['wpwautoposteron']) ? $_GET['wpwautoposteron'] : '';
            $scheduledon = isset($_GET['wpwautoposterscheduleon']) ? $_GET['wpwautoposterscheduleon'] : '';

            $reparr = array('fb', 'tw', 'li', 'tb', 'ba', 'ins', 'pin');
            $replcarr = array(
                __('Facebook', 'wpwautoposter'),
                __('Twitter', 'wpwautoposter'),
                __('LinkedIn', 'wpwautoposter'),
                __('Tumblr', 'wpwautoposter'),
                __('BufferApp', 'wpwautoposter'),
                __('Instagram', 'wpwautoposter'),
                __('Pinterest', 'wpwautoposter')
            );

            if (!empty($scheduledon)) {

                $scheduledon = str_replace($reparr, $replcarr, $scheduledon);
                $scheduledon = implode($scheduledon, ',');
                $msg = sprintf(__('Post scheduled with %1$s', 'wpwautoposter'), $scheduledon);
            } else {

                $postedon = str_replace($reparr, $replcarr, $postedon);
                $postedon = implode($postedon, ',');
                $msg = sprintf(__('Post published on %1$s', 'wpwautoposter'), $postedon);
            }

            echo "<div class='updated notice notice-success is-dismissible'><p>{$msg}.</p>
                  <button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
        }

        // get all notices from sessions
        $all_notices  = isset( $_SESSION['sap_notices'] ) ? $_SESSION['sap_notices'] : array();

        // Display notices if there is any
        if( !empty( $all_notices ) ) {
            foreach ( $all_notices as $notice_type => $messages ) {

                foreach( $messages as $message ) {
                    echo "<div class='notice notice notice-$notice_type is-dismissible'>
                        <p>{$message}</p>
                        <button type='button' class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button>
                    </div>";
                }
            }
            unset( $_SESSION['sap_notices'] );
        }
    }

    /**
     * Bulk Delete
     *
     * Handles bulk delete functinalities of posted logs
     *
     * @package Social Auto Poster
     * @since 1.4.0
     */
    function wpw_auto_poster_posted_logs_bulk_delete() {

        if (( ( isset($_GET['action']) && $_GET['action'] == 'delete') || ( isset($_GET['action2']) && $_GET['action2'] == 'delete' ) ) && isset($_GET['page']) && $_GET['page'] == 'wpw-auto-poster-posted-logs' && isset($_GET['logid']) && !empty($_GET['logid'])) { //check action and page and also logid
            // get redirect url
            $redirect_url = add_query_arg(array('page' => 'wpw-auto-poster-posted-logs'), admin_url('admin.php'));

            //get bulk product array from $_GET
            $action_on_id = $_GET['logid'];

            if (count($action_on_id) > 0) { //check there is some checkboxes are checked or not
                //if there is multiple checkboxes are checked then call delete in loop
                foreach ($action_on_id as $posted_log_id) {

                    //parameters for delete function
                    $args = array(
                        'log_id' => $posted_log_id
                    );

                    //call delete function from model class to delete records
                    $this->model->wpw_auto_poster_bulk_delete($args);
                }
                $redirect_url = add_query_arg(array('message' => '3'), $redirect_url);

            }

            //if bulk delete is performed successfully then redirect
            wp_redirect($redirect_url);
            exit;
        }
    }

    /**
     * Bulk Scheduling
     *
     * Handles bulk scheduling functinalities of manage schedule
     *
     * @package Social Auto Poster
     * @since 1.4.0
     */
    function wpw_auto_poster_scheduling_bulk_process() {

        global $wpw_auto_poster_options;

        $prefix = WPW_AUTO_POSTER_META_PREFIX;

        //Get admin url
        $admin_url = admin_url('admin.php');

        //Get all supported social network
        $all_social_networks = $this->model->wpw_auto_poster_get_social_type_name();

        //Get selected tab
        $selected_tab = !empty($_GET['tab']) ? $_GET['tab'] : 'facebook';

        //Get social network slug
        $social_network  = ucfirst($selected_tab);
        $social_slug = array_search($social_network, $all_social_networks);


        //Get social meta key
        $status_meta_key = $this->model->wpw_auto_poster_get_social_status_meta_key($selected_tab);

        //echo "<pre>";
        //print_r($_GET);exit;

        //Code for Scheduling posts
        if (( ( isset($_GET['action']) && $_GET['action'] == 'schedule') || ( isset($_GET['action2']) && $_GET['action2'] == 'schedule' ) ) && isset($_GET['page']) && $_GET['page'] == 'wpw-auto-poster-manage-schedules' && isset($_GET['schedule']) && !empty($_GET['schedule'])) { //check action and page and also logid
            // get redirect url
            $redirect_url = add_query_arg(array('page' => 'wpw-auto-poster-manage-schedules', 'tab' => $selected_tab), $admin_url);

            //get bulk posts array from $_GET
            $action_on_ids = $_GET['schedule'];

            // Update Hour for Individual Post in Hourly Posting
            if ( isset( $_GET['select_hour'] ) ){

                $wpw_select_hour = $_GET['select_hour'];

            } elseif ( isset( $_GET['bulk_select_hour'] ) ) {

                $wpw_select_hour = $_GET['bulk_select_hour'];

            }


            $wpw_select_hour = ( !empty( $wpw_select_hour ) ) ? strtotime( $wpw_select_hour ) : '';

            if (count($action_on_ids) > 0) { //check there is some checkboxes are checked or not
                //if there is multiple checkboxes are checked then call delete in loop
                foreach ($action_on_ids as $post_id) {

                    $main_exclude_arr[$social_slug] = false;

                    // Add network to scheduled schedule wall post
                    $schedules = get_post_meta($post_id, $prefix . 'schedule_wallpost', true);

                    $post_type = get_post_type($post_id); // get post type
                    $post_catgeories = wpw_auto_poster_get_post_categories( $post_type, $post_id ); // get post categories

                    // get excluded catgeories for the selected tab
                    $exclude_cats = !empty($wpw_auto_poster_options[$social_slug.'_exclude_cats']) ? $wpw_auto_poster_options[$social_slug.'_exclude_cats'] : array();

                    if(!empty($post_catgeories)) {
                        // Loop through all the categories of a particualr post.
                        foreach($post_catgeories as $category) {

                            // Check if excluded category is selected for the current post type.
                            if(!empty($exclude_cats[$post_type])) {
                                // If atleast one excluded category matches with the post categories than make flag as true
                                if(in_array($category, $exclude_cats[$post_type])){

                                    // make social network exclude flag true, if atleast one excluded category matches
                                    $main_exclude_arr[$social_slug] = true;
                                    continue;
                                }
                            }
                        }
                    }

                    $schedules = !empty($schedules) ? $schedules : array();
                    $schedules[] = $selected_tab;

                    // check if selected social tab has any excluded categories selected 
                    if( !$main_exclude_arr[$social_slug] ) {

                        update_post_meta($post_id, $prefix . 'schedule_wallpost', array_unique($schedules));

                        //Update scheduled meta
                        update_post_meta($post_id, $status_meta_key, 2);

                        //Update select hour meta
                        update_post_meta($post_id, $prefix.'select_hour', $wpw_select_hour);
                    }
                }

                if( !$main_exclude_arr[$social_slug] ) {
                    $redirect_url = add_query_arg(array('message' => '1'), $redirect_url);
                }
            }

            //if there is no checboxes are checked then redirect to listing page
            wp_redirect($redirect_url);
            exit;
        }

        //Code for Unscheduling posts
        if (( ( isset($_GET['action']) && $_GET['action'] == 'unschedule') || ( isset($_GET['action2']) && $_GET['action2'] == 'unschedule' ) ) && isset($_GET['page']) && $_GET['page'] == 'wpw-auto-poster-manage-schedules' && isset($_GET['schedule']) && !empty($_GET['schedule'])) { //check action and page and also logid
            // get redirect url
            $redirect_url = add_query_arg(array('page' => 'wpw-auto-poster-manage-schedules', 'tab' => $selected_tab), $admin_url);

            //get bulk posts array from $_GET
            $action_on_ids = $_GET['schedule'];

            if (count($action_on_ids) > 0) { //check there is some checkboxes are checked or not
                //if there is multiple checkboxes are checked then call delete in loop
                foreach ($action_on_ids as $post_id) {

                    // Add network to scheduled schedule wall post
                    $schedules = get_post_meta($post_id, $prefix . 'schedule_wallpost', true);
                    if (!empty($schedules)) {
                        if (($key = array_search($selected_tab, $schedules)) !== false) {
                            unset($schedules[$key]);
                        }
                        if( !empty( $schedules ) ) {
                            update_post_meta($post_id, $prefix . 'schedule_wallpost', $schedules);
                        } else{ // remove post meta if no social media for schedule
                            delete_post_meta($post_id, $prefix . 'schedule_wallpost' );        
                        }
                    }
                    else { // remove post meta if no social media for schedule
                       delete_post_meta( $post_id, $prefix.'schedule_wallpost' );
                    }

                    //Remove status meta
                    delete_post_meta($post_id, $status_meta_key);
                }

                $redirect_url = add_query_arg(array('message' => '2'), $redirect_url);

            }
            //if there is no checboxes are checked then redirect to listing page
            wp_redirect($redirect_url);
            exit;
        }
    }

    /**
     * Validate Setting
     *
     * Handles to add validate schedule settings
     *
     * @package Social Auto Poster
     * @since 1.5.0
     */
    public function wpw_auto_poster_validate_setting($new_data, $old_data) {

        if ( ( !empty($new_data['schedule_wallpost_option']) && $new_data['schedule_wallpost_option'] != $old_data['schedule_wallpost_option'] ) || ( $new_data['schedule_wallpost_option'] == 'wpw_custom_mins' && !empty($new_data['schedule_wallpost_custom_minute'] ) && $new_data['schedule_wallpost_custom_minute'] != $old_data['schedule_wallpost_custom_minute'] ) || ( $new_data['schedule_wallpost_option'] == 'twicedaily' && ( $new_data['enable_twice_random_posting'] != $old_data['enable_twice_random_posting'] || ( $new_data['schedule_wallpost_twice_time1'] != $old_data['schedule_wallpost_twice_time1'] || $new_data['schedule_wallpost_twice_time2'] != $old_data['schedule_wallpost_twice_time2'] ) ) ) || ( $new_data['schedule_wallpost_option'] == 'daily' && ( $new_data['schedule_wallpost_option'] == $old_data['schedule_wallpost_option'] ) ) ) { // Check Schedule WallPost is not "Instance"
            // first clear the schedule
            wp_clear_scheduled_hook('wpw_auto_poster_scheduled_cron');

            if (!wp_next_scheduled('wpw_auto_poster_scheduled_cron')) {

                $utc_timestamp = time(); //

                $local_time = current_time('timestamp'); // to get current local time

                if ($new_data['schedule_wallpost_option'] == 'daily' && isset($new_data['schedule_wallpost_time']) && isset($new_data['schedule_wallpost_minute'])) {

                    // Schedule other CRON events starting at user defined hour and periodically thereafter
                    $schedule_time = mktime($new_data['schedule_wallpost_time'], $new_data['schedule_wallpost_minute'], 0, date('m', $local_time), date('d', $local_time), date('Y', $local_time));

                    // get difference
                    $diff = ( $schedule_time - $local_time );
                    $utc_timestamp = $utc_timestamp + $diff;

                    wp_schedule_event($utc_timestamp, 'daily', 'wpw_auto_poster_scheduled_cron');
                } elseif ($new_data['schedule_wallpost_option'] == 'twicedaily' && empty($new_data['enable_twice_random_posting'])) {                 // Added since version 2.5.1
                    $utc_timestamp = time();

                    // Schedule other CRON events starting at user defined hour and periodically thereafter
                    $schedule_time1 = mktime($new_data['schedule_wallpost_twice_time1'], 0, 0, date('m', $local_time), date('d', $local_time), date('Y', $local_time));

                    // get difference
                    $diff = ( $schedule_time1 - $local_time );
                    $utc_timestamp1 = $utc_timestamp + $diff;

                    wp_schedule_event( $utc_timestamp1, 'daily', 'wpw_auto_poster_scheduled_cron');

                    $schedule_time2 = mktime($new_data['schedule_wallpost_twice_time2'], 0, 0, date('m', $local_time), date('d', $local_time), date('Y', $local_time));

                    // get difference
                    $diff = ( $schedule_time2 - $local_time );
                    $utc_timestamp2 = $utc_timestamp + $diff;

                    wp_schedule_event( $utc_timestamp2, 'daily', 'wpw_auto_poster_scheduled_cron');

                } else if ($new_data['schedule_wallpost_option'] == 'hourly') {                 // Added since version 2.0.0

                    // logic to get hours rounded, if current time is 3:15 am it will return 4 am.
                    // return value in seconds
                    $new_time = ceil($local_time / 3600) * 3600;

                    // get difference between 3:15 and 4 so it will become 45 min (2700 seconds)
                    $diff = ( $new_time - $local_time );

                    // add 2700 seconds so cron will start runnig from 4 am.
                    $utc_timestamp = $utc_timestamp + $diff;

                    wp_schedule_event($utc_timestamp, $new_data['schedule_wallpost_option'], 'wpw_auto_poster_scheduled_cron');
                } else {

                    $scheds = (array) wp_get_schedules();
                    $current_schedule = $new_data['schedule_wallpost_option'];
                    $interval = ( isset($scheds[$current_schedule]['interval']) ) ? (int) $scheds[$current_schedule]['interval'] : 0;

                    $utc_timestamp = $utc_timestamp + $interval;

                    wp_schedule_event($utc_timestamp, $new_data['schedule_wallpost_option'], 'wpw_auto_poster_scheduled_cron');
                }
            }
        }

        return $new_data;
    }

    /**
     * Validate Setting
     *
     * Handles to set schedule based on settings
     *
     * @package Social Auto Poster
     * @since 2.6.9
     */
    public function wpw_auto_poster_reposter_validate_setting($new_data, $old_data) {
        
        if ( ( isset($new_data['schedule_wallpost_option'] ) && isset($old_data['schedule_wallpost_option'] ) && is_array( $new_data['schedule_wallpost_option'] ) && is_array( $old_data['schedule_wallpost_option'] ) ) ) { // Check Schedule WallPost is not "Instance"
            // first clear the schedule
            $schedule = $new_data['schedule_wallpost_option'];
            $old_schedule = $old_data['schedule_wallpost_option'];

            if( $schedule['days'] != $old_schedule['days'] || $schedule['hours'] != $old_schedule['hours'] || $schedule['minutes'] != $old_schedule['minutes'] ) {
                wp_clear_scheduled_hook('wpw_auto_poster_reposter_scheduled_cron');

                if ( !wp_next_scheduled('wpw_auto_poster_reposter_scheduled_cron') ) {

                    $utc_timestamp = time(); //

                    $local_time = current_time('timestamp'); // to get current local time

                    $scheds = (array) wp_get_schedules();
                    
                    $interval = ( isset($scheds['wpw_reposter_custom_schedule']['interval']) ) ? (int) $scheds['wpw_reposter_custom_schedule']['interval'] : 0;

                    $utc_timestamp = $utc_timestamp + $interval;

                    wp_schedule_event($utc_timestamp, 'wpw_reposter_custom_schedule', 'wpw_auto_poster_reposter_scheduled_cron');
                }
            }
        }

        return $new_data;
    }

    /**
     * Add Custom Schedule
     *
     * Handle to add custom schedule
     *
     * @package Social Auto Poster
     * @since 1.5.0
     */
    public function wpw_auto_poster_add_custom_scheduled($schedules) {
        global $wpw_auto_poster_options, $wpw_auto_poster_reposter_options;

        // custom minutes value from input box
        $schedule_wallpost_custom_minute = ( !empty( $wpw_auto_poster_options['schedule_wallpost_custom_minute'] ) ) ? $wpw_auto_poster_options['schedule_wallpost_custom_minute'] : WPW_AUTO_POSTER_SCHEDULE_CUSTOM_DEFAULT_MINUTE;

        
        $schedule_reposter_schedule = ( !empty( $wpw_auto_poster_reposter_options['schedule_wallpost_option'] ) ) ? $wpw_auto_poster_reposter_options['schedule_wallpost_option'] : '';

        // custom scheduler value from reposter schedule input box
        // check on update options
        if( isset( $_POST['wpw_auto_poster_reposter_options']['schedule_wallpost_option'] ) && !empty( $_POST['wpw_auto_poster_reposter_options']['schedule_wallpost_option'] ) ) {
            
            $schedule_reposter_schedule =  $_POST['wpw_auto_poster_reposter_options']['schedule_wallpost_option'];
        }

        // Adds once weekly to the existing schedules.
        $schedules['weekly'] = array(
            'interval' => 604800,
            'display' => __('Once Weekly', 'wpwautoposter')
        );

        // check on update options
        if( isset( $_POST['wpw_auto_poster_options']['schedule_wallpost_custom_minute'] ) && !empty( $_POST['wpw_auto_poster_options']['schedule_wallpost_custom_minute'] ) )
            $schedule_wallpost_custom_minute = $_POST['wpw_auto_poster_options']['schedule_wallpost_custom_minute'];

        // code to set custom mins given to the input box for schedule cron
        $schedules["wpw_custom_mins"]  = array(
                'interval' => $schedule_wallpost_custom_minute*60,
                'display' => __( $schedule_wallpost_custom_minute.' minutes', 'wpwautoposter'));

        // code to set custom mins given to the input box for schedule cron since 2.6.6
        if( !empty( $schedule_reposter_schedule ) ) {
            
            $days = $schedule_reposter_schedule['days'];
            $hours = $schedule_reposter_schedule['hours'];
            $minutes = $schedule_reposter_schedule['minutes'];
            $schedule_name = "Every ";
            if( !empty( $days ) ) {
                $schedule_name .= $days.__(" days", 'wpwautoposter' );
            }
            if( !empty( $hours ) ) {
                $schedule_name .= ' '.$hours. __(" Hours", 'wpwautoposter' );
            }
            if( !empty( $minutes ) ) {
                $schedule_name .= ' '.$minutes.__(" Minutes", 'wpwautoposter' );
            }

            $days = ( !empty( $days ) ) ? $days * 86400 : 0; // days to sec
            $hours = ( !empty( $hours ) ) ? $hours * 3600 : 0; // hours to sec
            $minutes = ( !empty( $minutes ) ) ? $minutes * 60 : 0; // minutes to sec

            $total_seconds = $days+$hours+$minutes; // total in seconds

            $schedules["wpw_reposter_custom_schedule"]  = array(
                    'interval' => $total_seconds,
                    'display' => $schedule_name
                );
        }

        return $schedules;
    }

    /**
     * Cron Job For Send WallPost to Followers
     *
     * Handle to call schedule cron for
     * send wallpost to followers
     *
     * @package Social Auto Poster
     * @since 1.5.0
     */
    public function wpw_auto_poster_scheduled_cron() {

        $prefix = WPW_AUTO_POSTER_META_PREFIX;

        // Get all post data which have send wall post
        $posts_data = $this->model->wpw_auto_poster_get_schedule_post_data();

        if (!empty($posts_data)) { // Check post data are not empty
            foreach ($posts_data as $post_data) {

                $postid = $post_data->ID;

                //get schedule wallpost
                $get_schedule = get_post_meta($postid, $prefix . 'schedule_wallpost', true);
                $this->logs->wpw_auto_poster_add('Start schedule Posting', true);

                if( !empty( $get_schedule ) ) {

                    if ( in_array('facebook', $get_schedule)) { // Check facebook

                        $this->logs->wpw_auto_poster_add('Facebook Schedule Posting | ' . $post_data->post_type . ' | ' . $postid, true);

                        //post to user wall on facebook
                        $res = $this->fbposting->wpw_auto_poster_fb_posting($post_data);

                        // check if published post successfully
                        if( $res ){
                            $key = array_search ( 'facebook', $get_schedule );
                            unset( $get_schedule[$key] );
                        }
                    }
                    if (in_array('twitter', $get_schedule)) { // Check twitter

                        $this->logs->wpw_auto_poster_add('Twitter Schedule Posting | ' . $post_data->post_type . ' | ' . $postid, true);

                        //post to twitter
                        $res = $this->twposting->wpw_auto_poster_tw_posting($post_data);

                        // check if published post successfully
                        if( $res ){
                            $key = array_search ( 'twitter', $get_schedule );
                            unset( $get_schedule[$key] );
                        }
                    }
                    if (in_array('linkedin', $get_schedule)) { // Check linkedin
                        
                        $this->logs->wpw_auto_poster_add('Linkedin Schedule Posting | ' . $post_data->post_type . ' | ' . $postid, true);

                        //post to linkedin
                        $res = $this->liposting->wpw_auto_poster_li_posting($post_data);

                        // check if published post successfully
                        if( $res ){
                            $key = array_search ( 'linkedin', $get_schedule );
                            unset( $get_schedule[$key] );
                        }
                    }
                    if (in_array('tumblr', $get_schedule)) { // Check tumblr
                        
                        $this->logs->wpw_auto_poster_add('Tumblr Schedule Posting | ' . $post_data->post_type . ' | ' . $postid, true);

                        //post to tumblr
                        $res = $this->tbposting->wpw_auto_poster_tb_posting($post_data);

                        // check if published post successfully
                        if( $res ){
                            $key = array_search ( 'tumblr', $get_schedule );
                            unset( $get_schedule[$key] );
                        }
                    }

                    if (in_array('bufferapp', $get_schedule)) { // Check bufferapp
                        
                        $this->logs->wpw_auto_poster_add('BufferApp Schedule Posting | ' . $post_data->post_type . ' | ' . $postid, true);

                        //post to bufferapp
                        $res = $this->baposting->wpw_auto_poster_ba_posting($post_data);

                        // check if published post successfully
                        if( $res ){
                            $key = array_search ( 'bufferapp', $get_schedule );
                            unset( $get_schedule[$key] );
                        }
                    }

                    if (in_array('instagram', $get_schedule)) { // Check instagram
                        
                        $this->logs->wpw_auto_poster_add('Instagram Schedule Posting | ' . $post_data->post_type . ' | ' . $postid, true);

                        //post to user timeline on instagram
                        $res = $this->insposting->wpw_auto_poster_ins_posting($post_data);

                        // check if published post successfully
                        if( $res ){
                            $key = array_search ( 'instagram', $get_schedule );
                            unset( $get_schedule[$key] );
                        }
                    }

                    if (in_array('pinterest', $get_schedule)) { // Check pinterest
                        
                        $this->logs->wpw_auto_poster_add('Pinterest Schedule Posting | ' . $post_data->post_type . ' | ' . $postid, true);

                        //post to user board/pins on pinterest
                        $res = $this->pinposting->wpw_auto_poster_pin_posting($post_data);

                        // check if published post successfully
                        if( $res ){
                            $key = array_search ( 'pinterest', $get_schedule );
                            unset( $get_schedule[$key] );
                        }
                    }
                }

                //delete schedule wallpost
                if( empty( $get_schedule ) ) {
                    delete_post_meta($postid, $prefix . 'schedule_wallpost');
                } else {
                    update_post_meta( $postid, $prefix . 'schedule_wallpost', $get_schedule );
                }

            }
        }
    }


    /**
     * Cron Job For Send WallPost to social account with Reposter options
     *
     * Handle to call schedule cron for
     * send wallpost to social accounts for reposter option
     *
     * @package Social Auto Poster
     * @since 2.6.9
     */
    public function wpw_auto_poster_reposter_scheduled_cron() {

        global $wpw_auto_poster_reposter_options, $wpw_auto_poster_logs;

        $reposter_options = get_option( 'wpw_auto_poster_reposter_options' );

        $wpw_posting_repeat = ( empty( $wpw_auto_poster_reposter_options['schedule_wallpost_repeat'] ) || $wpw_auto_poster_reposter_options['schedule_wallpost_repeat'] == 'no' ) ? false : true;

        $repeat_limit = ( empty( $reposter_options['reposter_repeat_times'] ) )? '' : $reposter_options['reposter_repeat_times'];

        $prefix = WPW_AUTO_POSTER_META_PREFIX;

        $all_social_networks = $this->model->wpw_auto_poster_get_social_type_data();

        // Loop all the supported social networks
        foreach( $all_social_networks as $slug => $label ) {

            // skip if reposter is not enabled for social media
            if( !isset( $wpw_auto_poster_reposter_options[ 'enable_'.$label] ) || empty( $wpw_auto_poster_reposter_options['enable_'.$label] ) ) {
                continue;
            }

            $posting_for = array();

            if( !empty( $wpw_auto_poster_reposter_options['enable_'.$label.'_for'] ) && !empty( $wpw_auto_poster_reposter_options['enable_'.$label] ) ) { 
                $posting_for = $wpw_auto_poster_reposter_options['enable_'.$label.'_for'];
            }

            // skip if no post type is selected for auto posting
            if( empty( $posting_for ) ){
                continue;
            }

            $unique_posting = ( !empty( $wpw_auto_poster_reposter_options['unique_posting'] ) && $wpw_auto_poster_reposter_options['unique_posting'] == 1 ) ? true : false;

            // get selected categories to exclude for each social network
            $exclude_cats = !empty($wpw_auto_poster_reposter_options[$slug.'_post_type_cats']) ? $wpw_auto_poster_reposter_options[$slug.'_post_type_cats'] : array();
            
            // exclude or include selected category?
            $post_type_cats = !empty($wpw_auto_poster_reposter_options[$slug.'_posting_cats']) ? $wpw_auto_poster_reposter_options[$slug.'_posting_cats'] : 'include';
            
            // limit per schedule
            $post_limit = !empty($wpw_auto_poster_reposter_options[$slug.'_posts_limit']) ? $wpw_auto_poster_reposter_options[$slug.'_posts_limit'] : '';

            // Get all post data which have send wall post
            $posts_data = $this->model->wpw_auto_poster_reposter_get_schedule_post_data( $posting_for, $exclude_cats, $post_type_cats, $unique_posting,$post_limit, $slug, $label );

            // repeat reposter if no posts found for posting and repeat loop true 
            if( empty( $posts_data ) && $wpw_posting_repeat == true ) {

                $reposter_options[$slug.'_last_posted_page'] = 1; // reposter start from begining
                    
                update_option( 'wpw_auto_poster_reposter_options' , $reposter_options );

                // Get all post data which have send wall post
                $posts_data = $this->model->wpw_auto_poster_reposter_get_schedule_post_data( $posting_for, $exclude_cats, $post_type_cats, $unique_posting,$post_limit, $slug, $label );
            }


            if ( !empty($posts_data) ) { // Check post data are not empty

                foreach ( $posts_data as $post_data ) {

                    $postid = $post_data->ID;
                    $post_type = $post_data->post_type; // Post type

                    // add log
                    $wpw_auto_poster_logs->wpw_auto_poster_add('Start Reposter for :'.$label, true);

                    //post to user wall on facebook
                    if( $slug == 'fb' ) {

                        $res = $this->fbposting->wpw_auto_poster_fb_posting( $post_data );
                        // check if published post successfully
                        if( $res ){
                            update_post_meta( $postid, $prefix.$slug.'_reposter_publish', 1 );
                            if( !empty( $repeat_limit ) ) { // update repeated time in meta

                                $fb_repeated_time = get_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', true );

                                $fb_repeated_time = ( empty( $fb_repeated_time ) ) ? 0 : $fb_repeated_time;
                                $fb_repeated_time = $fb_repeated_time + 1;
                                
                                update_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', $fb_repeated_time );
                            }
                        } 
                    }
                    elseif( $slug == 'tw' ) { //post to twitter

                        $res = $this->twposting->wpw_auto_poster_tw_posting($post_data);
                        // check if published post successfully
                        if( $res ) {
                            update_post_meta( $postid, $prefix.$slug.'_reposter_publish', 1 );
                            if( !empty( $repeat_limit ) ) {
                                $tw_repeated_time = get_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', true );
                                
                                $tw_repeated_time = ( empty( $tw_repeated_time ) ) ? 0 : $tw_repeated_time;
                                $tw_repeated_time = $tw_repeated_time + 1;
                                
                                update_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', $tw_repeated_time );
                            }
                        }
                    }
                    elseif( $slug == 'li' ) { //post to linkedin

                        $res = $this->liposting->wpw_auto_poster_li_posting($post_data);

                        // check if published post successfully
                        if( $res ) {
                            update_post_meta( $postid, $prefix.$slug.'_reposter_publish', 1 );
                            if( !empty( $repeat_limit ) ) {
                                $li_repeated_time = get_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', true );
                                
                                $li_repeated_time = ( empty( $li_repeated_time ) ) ? 0 : $li_repeated_time;
                                $li_repeated_time = $li_repeated_time + 1;
                                
                                update_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', $li_repeated_time );
                            }
                        }
                    }
                    elseif( $slug == 'tb' ) { //post to tumblr

                        $res = $this->tbposting->wpw_auto_poster_tb_posting($post_data);
                        // check if published post successfully
                        if( $res ) {
                            update_post_meta( $postid, $prefix.$slug.'_reposter_publish', 1 );
                            if( !empty( $repeat_limit ) ) {
                                $tb_repeated_time = get_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', true );
                                
                                $tb_repeated_time = ( empty( $tb_repeated_time ) ) ? 0 : $tb_repeated_time;
                                $tb_repeated_time = $tb_repeated_time + 1;
                                
                                update_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', $tb_repeated_time );
                            }
                        }
                    }
                    elseif( $slug == 'ba' ) { //post to bufferapp

                        $res = $this->baposting->wpw_auto_poster_ba_posting($post_data);
                        // check if published post successfully
                        if( $res ) {
                           update_post_meta( $postid, $prefix.$slug.'_reposter_publish', 1 );

                           if( !empty( $repeat_limit ) ) {

                               $ba_repeated_time = get_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', true );
                                
                                $ba_repeated_time = ( empty( $ba_repeated_time ) ) ? 0 : $ba_repeated_time;
                                $ba_repeated_time = $ba_repeated_time + 1;
                                
                                update_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', $ba_repeated_time );
                            }
                        }
                    }
                    elseif( $slug == 'ins' ) { //post to user timeline on instagram

                        $res = $this->insposting->wpw_auto_poster_ins_posting($post_data);
                        // check if published post successfully
                        if( $res ) {
                           update_post_meta( $postid, $prefix.$slug.'_reposter_publish', 1 );

                           if( !empty( $repeat_limit ) ) {

                               $ins_repeated_time = get_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', true );
                                
                                $ins_repeated_time = ( empty( $ins_repeated_time ) ) ? 0 : $ins_repeated_time;
                                $ins_repeated_time = $ins_repeated_time + 1;
                                
                                update_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', $ins_repeated_time );
                            }
                        }
                    }
                    elseif( $slug == 'pin' ) { //post to user board/pins on pinterest

                        $res = $this->pinposting->wpw_auto_poster_pin_posting($post_data);
                        // check if published post successfully
                        if( $res ) {
                           update_post_meta( $postid, $prefix.$slug.'_reposter_publish', 1 );

                           if( !empty( $repeat_limit ) ) {

                               $pin_repeated_time = get_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', true );
                                
                                $pin_repeated_time = ( empty( $pin_repeated_time ) ) ? 0 : $pin_repeated_time;
                                $pin_repeated_time = $pin_repeated_time + 1;
                                
                                update_post_meta( $postid, $prefix .$slug.'_reposter_repeated_time', $pin_repeated_time );
                            }
                        }
                    }
                    
                    $wpw_auto_poster_logs->wpw_auto_poster_add('End Reposter');
                }
            }
        }

        exit;
    }

    /**
     * Manage WPML compability
     * Remove status of posting on social data
     *
     * so, when user update data,
     * it's going for post data on socials
     *
     * @package Social Auto Poster
     * @since 1.8.3
     */
    public function wpw_auto_poster_wpml_dup_remove_status_meta($master_post_id, $lang, $post_array, $id) {

        if (!empty($id)) {

            global $wpw_auto_poster_options;

            $post_type = isset($post_array['post_type']) ? $post_array['post_type'] : '';

            $fb_enable_post_type = !empty($wpw_auto_poster_options['enable_facebook_for']) ? $wpw_auto_poster_options['enable_facebook_for'] : array();
            $tw_enable_post_type = !empty($wpw_auto_poster_options['enable_twitter_for']) ? $wpw_auto_poster_options['enable_twitter_for'] : array();
            $li_enable_post_type = !empty($wpw_auto_poster_options['enable_linkedin_for']) ? $wpw_auto_poster_options['enable_linkedin_for'] : array();
            $tb_enable_post_type = !empty($wpw_auto_poster_options['enable_tumblr_for']) ? $wpw_auto_poster_options['enable_tumblr_for'] : array();
            $ba_enable_post_type = !empty($wpw_auto_poster_options['enable_bufferapp_for']) ? $wpw_auto_poster_options['enable_bufferapp_for'] : array();
            $ins_enable_post_type = !empty($wpw_auto_poster_options['enable_instagram_for']) ? $wpw_auto_poster_options['enable_instagram_for'] : array();
            $pin_enable_post_type = !empty($wpw_auto_poster_options['enable_pinterest_for']) ? $wpw_auto_poster_options['enable_pinterest_for'] : array();

            if (in_array($post_type, $fb_enable_post_type))
                update_post_meta($id, '_wpweb_fb_published_on_fb', false);

            if (in_array($post_type, $tw_enable_post_type))
                update_post_meta($id, '_wpweb_tw_status', false);

            if (in_array($post_type, $li_enable_post_type))
                update_post_meta($id, '_wpweb_li_status', false);

            if (in_array($post_type, $tb_enable_post_type))
                update_post_meta($id, '_wpweb_tb_status', false);

            if (in_array($post_type, $ba_enable_post_type))
                update_post_meta($id, '_wpweb_ba_status', false);

            if (in_array($post_type, $ins_enable_post_type))
                update_post_meta($id, '_wpweb_ins_published_on_ins', false);

            if (in_array($post_type, $pin_enable_post_type))
                update_post_meta($id, '_wpweb_pin_published_on_pin', false);
        }

        return;
    }

    /**
     * Select Hour for Individual Post When Globally Hourly Posting Selected
     *
     * Handle to add meta in publish box
     *
     * @package Social Auto Poster
     * @since 1.8.4
     */
    public function wpw_auto_poster_publish_meta() {

        global $post;

        $args = array('public' => true);
        $post_types = get_post_types($args);

        $wpw_auto_poster_options = get_option('wpw_auto_poster_options');

        if ($wpw_auto_poster_options['schedule_wallpost_option'] == 'hourly' && in_array($post->post_type, $post_types)) {

            $prefix = WPW_AUTO_POSTER_META_PREFIX;

            // wordpress date format
            $date_format = apply_filters( 'wpw_auto_poster_display_date_format', 'Y-m-d' );

            $wpw_auto_poster_select_hour = get_post_meta($post->ID, $prefix . 'select_hour', true);

            if( !empty( $wpw_auto_poster_select_hour ) && strlen($wpw_auto_poster_select_hour) <= 2 ){
            	$time = $wpw_auto_poster_select_hour;
            	$wpw_auto_poster_select_hour = date( $date_format, current_time('timestamp') );
            	$wpw_auto_poster_select_hour = $wpw_auto_poster_select_hour.' '.$time.':00';
            	$wpw_auto_poster_select_hour = date( $date_format.' H:i', strtotime($wpw_auto_poster_select_hour) );
            }elseif(!empty( $wpw_auto_poster_select_hour )){
            	$wpw_auto_poster_select_hour = date( $date_format.' '.'H:i', $wpw_auto_poster_select_hour );
            }else{
            	$next_cron = wp_next_scheduled( 'wpw_auto_poster_scheduled_cron' );
                $wpw_auto_poster_select_hour = get_date_from_gmt( date( 'Y-m-d H:i:s', $next_cron ), 'Y-m-d H:i' );

            }?>

            <div class="misc-pub-section misc-pub-schedule-date">
                <label for="<?php echo $prefix . 'select_hour'; ?>"><span class="wpw-auto-poster-schedule-icon"><img src="<?php print WPW_AUTO_POSTER_IMG_URL.'/icons/calendar.png';?>"></span>
                <span class="wpw-auto-poster-schedule-label">
                <?php _e('Schedule: ', 'wpwautoposter'); ?>
                </span>
                </label>
                <span class="wpw-auto-poster-schedule-label">
                <input type="text" name="<?php echo $prefix . 'select_hour'; ?>" id="<?php echo $prefix . 'select_hour'; ?>" class="wpw-auto-poster-schedule-date" value="<?php print $wpw_auto_poster_select_hour;?>"> 
                <span class="clear-date" title="<?php _e('Clear date', 'wpwautoposter'); ?>">X</span>
                </span>
            </div><?php
        }
    }

    /**
     * Add FB account list field to add or edit category form
     *
     * Handle to display FB account list to add category form
     *
     * @package Social Auto Poster
     * @since 2.3.1
     */
    function wpw_auto_poster_add_category_fb_acc_fields() {

        print '<table class="form-table">';
        // FB account list
        include_once( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-category-social-fb-fields.php' );

        print '<input type="hidden" name="wpw_auto_category_posting" value="1">';
        print '</table>';
    }

    /**
     * Add Twitter account list field to add or edit category form
     *
     * Handle to display Twitter account list to add category form
     *
     * @package Social Auto Poster
     * @since 2.3.1
     */
    function wpw_auto_poster_add_category_tw_acc_fields() {
        print '<table class="form-table">';
        include_once( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-category-social-tw-fields.php' );
        print '<input type="hidden" name="wpw_auto_category_posting" value="1">';
        print '</table>';
    }

    /**
     * Add Linkdin account list field to add or edit category form
     *
     * Handle to display Linkdin account list to add category form
     *
     * @package Social Auto Poster
     * @since 2.3.1
     */
    function wpw_auto_poster_add_category_li_acc_fields() {
        print '<table class="form-table">';
        // Linkdin account list
        include_once( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-category-social-li-fields.php' );
        print '<input type="hidden" name="wpw_auto_category_posting" value="1">';
        print '</table>';
    }

    /**
     * Add Instagram account list field to add or edit category form
     *
     * Handle to display Instagram account list to add category form
     *
     * @package Social Auto Poster
     * @since 2.3.1
     */
    function wpw_auto_poster_add_category_ins_acc_fields() {
        print '<table class="form-table">';
        include_once( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-category-social-ins-fields.php' );
        print '<input type="hidden" name="wpw_auto_category_posting" value="1">';
        print '</table>';
    }

    /**
     * Add Pinterest account list field to add or edit category form
     *
     * Handle to display Pinterest account list to add category form
     *
     * @package Social Auto Poster
     * @since 2.3.1
     */
    function wpw_auto_poster_add_category_pin_acc_fields() {
        print '<table class="form-table">';
        include_once( WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-category-social-pin-fields.php' );
        print '<input type="hidden" name="wpw_auto_category_posting" value="1">';
        print '</table>';
    }

    /**
     * Add hook to category add edit form
     *
     * Handle to display social account list to add and edit category form
     *
     * @package Social Auto Poster
     * @since 2.3.1
     */
    function wpw_auto_poster_hook_taxonomy() {

        global $wpw_auto_poster_options;

        $fb_selected_post = !empty($wpw_auto_poster_options['enable_facebook_for']) ? $wpw_auto_poster_options['enable_facebook_for'] : array();
        $tw_selected_post = !empty($wpw_auto_poster_options['enable_twitter_for']) ? $wpw_auto_poster_options['enable_twitter_for'] : array();
        $li_selected_post = !empty($wpw_auto_poster_options['enable_linkedin_for']) ? $wpw_auto_poster_options['enable_linkedin_for'] : array();
        $ins_selected_post = !empty($wpw_auto_poster_options['enable_instagram_for']) ? $wpw_auto_poster_options['enable_instagram_for'] : array();
        $pin_selected_post = !empty($wpw_auto_poster_options['enable_pinterest_for']) ? $wpw_auto_poster_options['enable_pinterest_for'] : array();

        $fb_exclude_cats = !empty($wpw_auto_poster_options['fb_exclude_cats']) ? $wpw_auto_poster_options['fb_exclude_cats'] : array();
        $tw_exclude_cats = !empty($wpw_auto_poster_options['tw_exclude_cats']) ? $wpw_auto_poster_options['tw_exclude_cats'] : array();
        $li_exclude_cats = !empty($wpw_auto_poster_options['li_exclude_cats']) ? $wpw_auto_poster_options['li_exclude_cats'] : array();
        $ins_exclude_cats = !empty($wpw_auto_poster_options['ins_exclude_cats']) ? $wpw_auto_poster_options['ins_exclude_cats'] : array();
        $pin_exclude_cats = !empty($wpw_auto_poster_options['pin_exclude_cats']) ? $wpw_auto_poster_options['pin_exclude_cats'] : array();

        $cat_id = "";

        if( !empty( $_GET['tag_ID'] ) ) {

            $cat_id = $_GET['tag_ID'];
            $taxonomy = $_GET['taxonomy'];

            $term = get_term_by( 'id', $cat_id, $taxonomy, ARRAY_A );
            $cat_slug = $term['slug'];
        }

        // code to add category hook to each post types
        $all_post_types = get_post_types(array('public' => true), 'objects');
        $all_post_types = is_array($all_post_types) ? $all_post_types : array();

        if (!empty($all_post_types)) {

            foreach ($all_post_types as $type) {
                $tax_obj = get_taxonomies(array('object_type' => array($type->name)), 'objects');

                // FB account list field to only selcted post types
                if (in_array($type->name, $fb_selected_post)) {
                    // add or edit category form hook for FB acct list
                    foreach ($tax_obj as $key => $value) {

                        // Skip if taxonomy is not category
                        if (!$value->hierarchical)
                            continue;

                        // Add social account list fields to each category add form
                        add_action($key . '_add_form_fields', array($this, 'wpw_auto_poster_add_category_fb_acc_fields'));

                        $edit_display = true;

                        if( !empty( $cat_id ) ) {

                            // check if the category excluded for facebook
                            if( !empty($fb_exclude_cats[$type->name] ) ) {

                                if( in_array( $cat_slug, $fb_exclude_cats[$type->name] ) )
                                    $edit_display = false;
                            }

                            // display facebook edit category account selection if not exclude
                            if( $edit_display ) {
                                // Add social account list fields to each category edit form
                                add_action($key . '_edit_form_fields', array($this, 'wpw_auto_poster_add_category_fb_acc_fields'), 999);
                            }
                        }
                    }
                }

                // Twitter account list field to only selcted post types
                if (in_array($type->name, $tw_selected_post)) {

                    // add or edit category form hook for TW acct list
                    foreach ($tax_obj as $key => $value) {

                        // Skip if taxonomy is not category
                        if (!$value->hierarchical)
                            continue;

                        // Add social account list fields to each category add form
                        add_action($key . '_add_form_fields', array($this, 'wpw_auto_poster_add_category_tw_acc_fields'));

                        $edit_display = true;

                        if( !empty( $cat_id ) ) {

                            // check if the category excluded for Twitter
                            if( !empty($tw_exclude_cats[$type->name] ) ) {

                                if( in_array( $cat_slug, $tw_exclude_cats[$type->name] ) )
                                    $edit_display = false;
                            }

                            // display facebook edit category account selection if not exclude
                            if( $edit_display ) {
                                // Add social account list fields to each category edit form
                                add_action($key . '_edit_form_fields', array($this, 'wpw_auto_poster_add_category_tw_acc_fields'), 999);
                            }
                        }
                    }
                }

                // Linkdin account list field to only selcted post types
                if (in_array($type->name, $li_selected_post)) {

                    // add or edit category form hook for Linkedin acct list
                    foreach ($tax_obj as $key => $value) {

                        // Skip if taxonomy is not category
                        if (!$value->hierarchical)
                            continue;

                        // Add social account list fields to each category add form
                        add_action($key . '_add_form_fields', array($this, 'wpw_auto_poster_add_category_li_acc_fields'));

                        $edit_display = true;

                        if( !empty( $cat_id ) ) {

                            // check if the category excluded for Linkedin
                            if( !empty($li_exclude_cats[$type->name] ) ) {

                                if( in_array( $cat_slug, $li_exclude_cats[$type->name] ) )
                                    $edit_display = false;
                            }

                            // display Linkedin edit category account selection if not exclude
                            if( $edit_display ) {

                                // Add social account list fields to each category edit form
                                add_action($key . '_edit_form_fields', array($this, 'wpw_auto_poster_add_category_li_acc_fields'), 999);
                            }
                        }
                    }
                }

                // Instagram account list field to only selcted post types added since 2.6.0
                if (in_array($type->name, $ins_selected_post)) {
                    // add or edit category form hook for Instagram acct list
                    foreach ($tax_obj as $key => $value) {

                        // Skip if taxonomy is not category
                        if (!$value->hierarchical)
                            continue;

                        // Add social account list fields to each category add form
                        add_action($key . '_add_form_fields', array($this, 'wpw_auto_poster_add_category_ins_acc_fields'));

                        $edit_display = true;

                        if( !empty( $cat_id ) ) {

                            // check if the category excluded for instagram
                            if( !empty($ins_exclude_cats[$type->name] ) ) {

                                if( in_array( $cat_slug, $ins_exclude_cats[$type->name] ) )
                                    $edit_display = false;
                            }

                            // display instagram edit category account selection if not exclude
                            if( $edit_display ) {
                                // Add social account list fields to each category edit form
                                add_action($key . '_edit_form_fields', array($this, 'wpw_auto_poster_add_category_ins_acc_fields'), 999);
                            }
                        }
                    }
                }

                // Pinterest account list field to only selcted post types added since 2.6.0
                if (in_array($type->name, $pin_selected_post)) {
                    // add or edit category form hook for Pinterest acct list
                    foreach ($tax_obj as $key => $value) {

                        // Skip if taxonomy is not category
                        if (!$value->hierarchical)
                            continue;

                        // Add social account list fields to each category add form
                        add_action($key . '_add_form_fields', array($this, 'wpw_auto_poster_add_category_pin_acc_fields'));

                        $edit_display = true;

                        if( !empty( $cat_id ) ) {

                            // check if the category excluded for pinterest
                            if( !empty($pin_exclude_cats[$type->name] ) ) {

                                if( in_array( $cat_slug, $pin_exclude_cats[$type->name] ) )
                                    $edit_display = false;
                            }

                            // display facebook edit category account selection if not exclude
                            if( $edit_display ) {
                                // Add social account list fields to each category edit form
                                add_action($key . '_edit_form_fields', array($this, 'wpw_auto_poster_add_category_pin_acc_fields'), 999);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Save posting to social account for each category
     *
     * Handle to save social account for category
     *
     * @package Social Auto Poster
     * @since 2.3.1
     */
    function wpw_auto_poster_category_fields_save($term_id, $tt_id, $taxonomy) {

        if (!isset($_POST['wpw_auto_category_posting']))
            return false;

        $old_cat_posting_acct = get_option('wpw_auto_poster_category_posting_acct');

        $selected_social_accounts = $_POST['wpw_auto_category_poster_options'];

        // clear old social account for term id
        if (!empty($term_id) && isset($old_cat_posting_acct[$term_id])) {
            unset($old_cat_posting_acct[$term_id]);
        }

        if (!empty($term_id) && !empty($selected_social_accounts)) {

            foreach ($selected_social_accounts as $social_acc_name => $social_acc_ids) {

                // update option for each account
                if (!empty($social_acc_ids)) {
                    $old_cat_posting_acct[$term_id][$social_acc_name] = $social_acc_ids;
                }
            }
        }

        update_option('wpw_auto_poster_category_posting_acct', $old_cat_posting_acct);
    }


    /**
     * Function to post wordpress pretty url if settings selected
     *
     * @package Social Auto Poster
     * @since 1.5.6
    */
    public function wpw_auto_poster_is_wp_pretty_url( $link, $postid, $socialtype ) {

        global $wpw_auto_poster_options;

        $is_pretty = ( !empty( $wpw_auto_poster_options[ $socialtype.'_wp_pretty_url'] ) ) ? $wpw_auto_poster_options[$socialtype.'_wp_pretty_url'] : '';

        if( $is_pretty == 'yes' ) {

            $link = get_permalink( $postid );
        }

        return $link;
    }

    /**
     * Handles to fetch categories from post type
     *
     * @package Social Auto Poster
     * @since 2.6.0
     */
    public function wpw_auto_poster_get_category(){

    	// If $_POST for post type value is not empty
    	if(!empty($_POST['post_type_val'])) {

    		// Get all taxonomies defined for that post type
    		$all_taxonomies = get_object_taxonomies( $_POST['post_type_val'], 'objects' );

    		// Loop on all taxonomies
    		foreach ($all_taxonomies as $taxonomy){

    			/**
    			 * If taxonomy is object and it is hierarchical, than it is our category
    			 * NOTE: If taxonomy is not hierarchical than it is tag and we should not consider this
    			 * And we will only consider first category found in our taxonomy list
    			 */
    			if(is_object($taxonomy) && !empty($taxonomy->hierarchical)){

    				$categories = get_terms( $taxonomy->name, array( 'hide_empty' => false ) ); // Get categories for taxonomy

    				// Start creating html from categories
    				$html = '<option value="">' . __('Select Category', 'wpwautoposter') . '</option>';
    				foreach ($categories as $category){

    					$html .=  '<option value="' . $category->term_id . '"';
    					// If category is already selected and current id is same as the selected one
    					if(!empty($_POST['sel_category_id']) && $_POST['sel_category_id'] == $category->term_id) {

    						$html .= " selected='selected'";
    					}
    					$html .= '>' . $category->name . '</option>';
    				}

    				// Echo html
    				echo $html;
    				exit;
    			}
    		}
    	}
    }

    /**
     *Fetch taxonomies from custom post type
     *
     * @package Social Auto Poster
     * @since 2.3.1
     */
    public function wpw_auto_poster_get_taxonomies(){

        global $wpw_auto_poster_options;

        $social_prefix = $_POST['social_type'];
        $static_post_type_arr = wpw_auto_poster_get_static_tag_taxonomy();

        $post_type_tags = array();
        $post_type_cats = array();
        $selected = $cathtml = $taghtml = '';

        /***** Custom post type TAG taxonomy code ******/
        // Check if any taxonomy tag is selected or not
        if(!empty( $_POST['selected_tags'])) {

            $pre_selected_tags = $_POST['selected_tags'];

            foreach ($pre_selected_tags as $pre_selected_tag) {
                $tagData = explode("|",$pre_selected_tag);
                $post_type = $tagData[0];
                $post_tag= $tagData[1];
                $selected_tags[$post_type][] = $post_tag;
            }

            $post_type_tags = $selected_tags;

        }

        /***** Custom post type CATEGORY taxonomy code ******/
        // Check if any taxonomy category is selected or not
        if(!empty( $_POST['selected_cats'])) {

            $pre_selected_cats = $_POST['selected_cats'];

            foreach ($pre_selected_cats as $pre_selected_cat) {
                $tagData = explode("|",$pre_selected_cat);
                $post_type = $tagData[0];
                $post_cat= $tagData[1];
                $selected_cats[$post_type][] = $post_cat;
            }

            $post_type_cats = $selected_cats;

        }

        // If $_POST for post type value is not empty
        if(!empty($_POST['post_type_val'])) {

            foreach($_POST['post_type_val'] as $post_type) {

                $html_tag = $html_cat = '';
                // Get all taxonomies defined for that post type
                $all_taxonomies = get_object_taxonomies( $post_type, 'objects' );

                // Loop on all taxonomies
                foreach ($all_taxonomies as $taxonomy){


                    if(is_object($taxonomy) && $taxonomy->hierarchical == 1){

                        $selected = "";

                        if( isset( $post_type_cats[$post_type] ) && !empty( $post_type_cats[$post_type] ) ){
                            $selected = ( in_array( $taxonomy->name, $post_type_cats[$post_type] ) ) ? 'selected="selected"' : '';
                        }

                        $html_cat .=  '<option value="' . $post_type."|".$taxonomy->name . '" '.$selected.'>'.$taxonomy->label.'</option>';

                    } elseif (is_object($taxonomy) && $taxonomy->hierarchical != 1) {

                        if( !empty( $static_post_type_arr[$post_type] ) && $static_post_type_arr[$post_type] != $taxonomy->name){
                             continue;
                        }
                        $selected = "";

                        if( isset( $post_type_tags[$post_type] ) && !empty( $post_type_tags[$post_type] ) ) {
                            $selected = ( in_array( $taxonomy->name, $post_type_tags[$post_type] ) ) ? 'selected="selected"' : '';
                        }
                        $html_tag .=  '<option value="' .$post_type."|".$taxonomy->name . '" '.$selected.'>'.$taxonomy->label.'</option>';
                    }
                }

                if(isset($html_cat) && !empty($html_cat)) {
                    $cathtml .= '<optgroup label='.ucfirst($post_type).'>'.$html_cat.'</optgroup>';
                }
                if(isset($html_tag) && !empty($html_tag)) {
                    $taghtml .= '<optgroup label='.ucfirst($post_type).'>'.$html_tag.'</optgroup>';
                }

                // Unset all values
                unset($html_cat);
                unset($html_tag);

                $response['data'] = array('categories'=> $cathtml, 'tags' => $taghtml);
            }
            echo json_encode($response);
            unset($response['data']);
            exit;
        }

    }

    /**
     * Handles to logs report graph process
     *
     * @package Social Auto Poster
     * @since 2.6.0
     */
    public function wpw_auto_poster_logs_graph_process(){

    	$prepare = $final_array= array();

    	$social_types_list = $this->model->wpw_auto_poster_get_social_type_name();

    	if( !empty($_REQUEST['social_type'])){
			$final_array[] = array(  __('Month','wpwautoposter'), $social_types_list[$_REQUEST['social_type']] );
    	}else{
    		$final_array[]  = array( __('Month','wpwautoposter'), __('Facebook','wpwautoposter'), __('Twitter','wpwautoposter'),__('Linkedin','wpwautoposter'),__('Tumblr','wpwautoposter'),__('BufferApp','wpwautoposter'),__('Instagram','wpwautoposter'),__('Pinterest','wpwautoposter') );
    	}

    	$prefix = WPW_AUTO_POSTER_META_PREFIX;

    	//Default Argument
    	$args = array(
						'posts_per_page'		=> -1,
						'orderby'				=> 'ID',
						'order'					=> 'ASC',
						'wpw_auto_poster_list'	=> true
					);

		//searched by social type
		if( !empty($_REQUEST['social_type']) ) {
			$args['meta_query']	= array(
									array(
										'key' => $prefix . 'social_type',
										'value' => $_REQUEST['social_type'],
										)
								  );
		}

		if( !empty($_REQUEST['filter_type']) && $_REQUEST['filter_type']== 'custom' ){

			//Check Start date and set it in query
			if( !empty($_REQUEST['start_date']) ) {
				$args['date_query'][]['after'] = date('Y-m-d', strtotime('-1 day', strtotime($_REQUEST['start_date'])));
			}

			//Check End date and set it in query
			if(!empty($_REQUEST['end_date']) ) {
				$args['date_query'][]['before'] = date('Y-m-d', strtotime('+1 day', strtotime($_REQUEST['end_date'])));
				//$args['date_query'][]['before'] = $_REQUEST['end_date'];
			}

			//Check Start date and End date if empty then month set
			if( empty($_REQUEST['start_date']) && empty($_REQUEST['end_date'])){
				$args['m']	= date('Ym');
			}

		}else if( !empty($_REQUEST['filter_type']) && $_REQUEST['filter_type']== 'current_year' ){
			//Set Current year
			$args['date_query'][]['year'] =  date( 'Y' );
		}else if( !empty($_REQUEST['filter_type']) && $_REQUEST['filter_type']== 'last_7days' ){
			//Set Current Week
			$args['date_query'][]['year'] =  date( 'Y' );
			$args['date_query'][]['week'] =  date( 'W' );
		}else{
			//Default set current month
			$args['m']	= date('Ym');
		}

		//Get result based on argument
    	$results = $this->model->wpw_auto_poster_get_posting_logs_data( $args );

    	//Check data exist
    	if( !empty( $results['data'] ) ){

    		//modify data
    		foreach ( $results['data'] as $key => $value ){

    			$post_id     = $value['ID'];
    			$post_date   = date( 'd-M-Y',  strtotime($value['post_date']));
    			$social_type = get_post_meta( $post_id, $prefix . 'social_type', true );

    			//Check post network type
    			if( !empty($prepare[$post_date][$social_type]) ){
    				$prepare[$post_date][$social_type] = $prepare[$post_date][$social_type] + 1;
    			}else{
    				$prepare[$post_date][$social_type] = 1;
    			}
    		}

    		//Finalize prepared data
    		foreach ( $prepare as $key => $value ){

				$facebook = !empty( $value['fb'] )? $value['fb'] : 0;
				$twitter  = !empty( $value['tw'] )? $value['tw'] : 0;
				$linkedin  = !empty( $value['li'] )? $value['li'] : 0;
				$tumbler  = !empty( $value['tb'] )? $value['tb'] : 0;
				$bufferapp  = !empty( $value['ba'] )? $value['ba'] : 0;
				$instagram  = !empty( $value['ins'] )? $value['ins'] : 0;
				$pinterest  = !empty( $value['pin'] )? $value['pin'] : 0;

    			if( !empty($_REQUEST['social_type'])){
    				$final_array[] = array( $key, $value[$_REQUEST['social_type']] );
    			}else{
					$final_array[] = array( $key, $facebook, $twitter, $linkedin, $tumbler, $bufferapp, $instagram , $pinterest);
    			}
    		}
    	}else{
    		if( !empty($_REQUEST['social_type'])){
    			$final_array[] = array( date('d-M-Y'), 0,);
    		}else{
				$final_array[] = array( date('d-M-Y'), 0, 0, 0, 0, 0, 0, 0);
    		}
    	}
    	echo  json_encode($final_array);
    	exit();
    }

    /**
     * Display license activation notice
     *
     * On Dismiss plugin will expire notice for 30 days. If plugin updated to new version then
     * it will display notice again.
     *
     * @package WooCommerce - Social Login
     * @since 2.6.5
     */
    public function wpw_auto_poster_license_activating_notice() {

        if ( ! $this->model->wpw_auto_poster_is_activated() &&
                ( empty( $_COOKIE['wpwautoposterdeactivationmsg'] ) || version_compare( $_COOKIE['wpwautoposterdeactivationmsg'], WPW_AUTO_POSTER_VERSION, '<' ) ) ) {
            ?>
            <style>
                .wpw_auto_poster_license-activation-notice {
                    position: relative;
                }
            </style>
            <script type="text/javascript">
                (function ( $ ) {
                    var setCookie = function ( c_name, value, exdays ) {
                        var exdate = new Date();
                        exdate.setDate( exdate.getDate() + exdays );
                        var c_value = encodeURIComponent( value ) + ((null === exdays) ? "" : "; expires=" + exdate.toUTCString());
                        document.cookie = c_name + "=" + c_value;
                    };
                    $( document ).on( 'click.wpw-auto-poster-notice-dismiss',
                        '.wpw-auto-poster-notice-dismiss',
                        function ( e ) {
                            e.preventDefault();
                            var $el = $( this ).closest('#wpw_auto_poster_license-activation-notice' );
                            $el.fadeTo( 100, 0, function () {
                                $el.slideUp( 100, function () {
                                    $el.remove();
                                } );
                            } );
                            setCookie( 'wpwautoposterdeactivationmsg',
                                '<?php echo WPW_AUTO_POSTER_VERSION; ?>',
                                30 );
                        } );
                })( window.jQuery );
            </script>
            <?php
            $redirect = add_query_arg(  array( 'page' => 'wpweb-upd-helper' ), esc_url( ( is_multisite() ? network_admin_url() : admin_url() ) ) );
            echo '<div class="updated wpw_auto_poster_license-activation-notice" id="wpw_auto_poster_license-activation-notice"><p>' . sprintf( __( 'Hola! Would you like to receive automatic updates? Please <a href="%s">activate your copy</a> of Social Auto Poster.', 'wpwautoposter' ), $redirect ) . '</p>' . '<button type="button" class="notice-dismiss wpw-auto-poster-notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'wpwautoposter' ) . '</span></button></div>';
        }
    }

    /**
     * Display WPWEB Upgrade notice
     *
     * @package WooCommerce - Social Login
     * @since 2.6.5
     */
    public function wpw_auto_poster_check_wpweb_updater_upgrate_notice() { ?>
        <div class="error fade notice is-dismissible" id="woo-wpweb-upgrade-notice">
            <p><?= __( 'Social Auto Poster requires WPWEB Updater version greater then 1.0.4. Please Upgrade to latest version.', 'wpwautoposter' ); ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'wpwautoposter' ); ?></span></button>
        </div>
        <?php
    }

    /**
     * Check WPWEB Updater v1.0.4 or old version activated
     *
     * If yes then Deactivated WPWEB updater plugin and display notice to install latest updater plugin
     *
     * @package WooCommerce - Social Login
     * @since 2.6.5
     */
    public function wpw_auto_poster_check_wpweb_updater_activation() {

        // if WPWEB Updater is activated
        if ( class_exists( 'Wpweb_Upd_Admin' ) && version_compare( WPWEB_UPD_VERSION, '1.0.5', '<' ) ) {
            // deactivate the WPWEB Updater plugin
            deactivate_plugins('wpweb-updater/wpweb-updater.php');
            // Display notice of WPWEB Updater older version
            add_action( 'admin_notices', array( $this, 'wpw_auto_poster_check_wpweb_updater_upgrate_notice' ) );
        }
    }

    public function wpw_auto_poster_fb_android_get_url() {

        $response = array(
                'type' => 'error',
                'message' => __( 'There is some issue while generating token.', 'wpwautoposter' )
            );

        if( !empty( $_POST['username'] ) && !empty( $_POST['password'] ) ) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $fb_rest_type = !empty($_POST['fb_rest_type']) ? $_POST['fb_rest_type'] : 'android';

            $token_url = $this->fb_andrd_posting->wpw_auto_poster_fb_get_token_url( $username, $password, $fb_rest_type);
            if( $token_url != false ) {
                $response = array(
                    'type' => 'success',
                    'message' => $token_url
                );
            }
            else{

                $message = !(empty( $this->fb_andrd_posting->error ) ) ? $this->fb_andrd_posting->error : __('User data not found.', 'wpwautoposter');
                $response = array(
                    'type' => 'error',
                    'message' => $message
                );
            }
        } else{

            $response = array(
                'type' => 'error',
                'message' => __( 'Please provide your facebook Username and Password.', 'wpwautoposter' )
            );
        }

        wp_send_json($response);
        exit;
    }


    public function wpw_auto_poster_fb_android_get_token() {

        $response = array(
                'type' => 'error',
                'message' => __( 'There is some issue while generating token.', 'wpwautoposter' )
            );

        if( !empty( $_POST['fb_access_token'] ) ) {

            $fb_token = stripslashes($_POST['fb_access_token']);

            $token_response = $this->fb_andrd_posting->wpw_auto_poster_fb_load_userdata( $fb_token);
            if( $token_response != false ) {
                $response = array(
                    'type' => 'success',
                    'message' => __('Your account added successfully.', 'wpwautoposter')
                );
            }
            else{

                $message = !(empty( $this->fb_andrd_posting->error ) ) ? $this->fb_andrd_posting->error : __('User data not found.', 'wpwautoposter');
                $response = array(
                    'type' => 'error',
                    'message' => $message
                );
            }
        } else{

            $response = array(
                'type' => 'error',
                'message' => __( 'Please enter the access token.', 'wpwautoposter' )
            );
        }

        wp_send_json($response);
        exit;
    }

    /**
     * Cron function for clear log
     *
     * Handle to clear system log file when exectuting the cron
     *
     * @package Social Auto Poster
     * @since 2.7.9
    */
    public function wpw_auto_poster_clear_log_cron() {
        global $wpw_auto_poster_logs;

        $wpw_auto_poster_logs->wpw_auto_poster_clear('logs');
    }

    /**
     * Cron function for clearing sap_uploads folder
     *
     * Handle to clear sap_uploads folder content when executing the cron
     *
     * @package Social Auto Poster
     * @since 2.7.9
    */
    public function wpw_auto_poster_clear_sap_uploads_cron() {
        
        // get folder whose content is to be deleted
        $path = WPW_AUTO_POSTER_SAP_UPLOADS_DIR;

        // get all file names
        $files = glob($path.'*'); 

        // if files exists in the folder
        if( !empty( $files ) ){

            foreach( $files as $file ){ // iterate files
                 
                if( is_file( $file ) ){
                    unlink( $file ); // delete file
                }
            }
        }
    }
    
    /**
     * Display notice if sap_uploads directory not exists
     *
     * @package Social Auto Poster
     * @since 2.8.2
     */
    public function wpw_auto_poster_upload_directory_notice() {
        $upload_dir     = wp_upload_dir();
        $upload_path    = isset( $upload_dir['basedir'] ) ? $upload_dir['basedir'].'/sap_uploads/' : ABSPATH;
        ?>
        <div class="error fade notice is-dismissible" id="wpw-auto-poster-upgrade-notice">
            <p><?php echo sprintf( __( 'Error: Could not create directory <code>%s</code>', 'wpwautoposter' ), $upload_path ); ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'wpwautoposter' ); ?></span></button>
        </div>
        <?php
    }

    /**
     * Adding Hooks
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function add_hooks() {

        // if the user can edit plugin options, let the fun begin!
        add_action('admin_menu', 'wpw_auto_poster_add_settings_page');

        add_action('admin_init', 'wpw_auto_poster_init');

        //post to social media when post or page or custom post type will be published
        add_action('save_post', array($this, 'wpw_auto_poster_post_to_social_media'), 15, 2);

        //add for schedule posting
        add_action('publish_future_post', array($this, 'wpw_auto_poster_schedule_posting'));

        //show admin notices
        add_action('admin_notices', array($this, 'wpw_auto_poster_admin_notices'));

        //add admin init for bult delete functionality
        add_action('admin_init', array($this, 'wpw_auto_poster_posted_logs_bulk_delete'));

        //add admin init for bulk scheduling functionality
        add_action('admin_init', array($this, 'wpw_auto_poster_scheduling_bulk_process'));

        // add filter to add validate settings
        add_filter('wpw_auto_poster_validate_settings', array($this, 'wpw_auto_poster_validate_setting'), 10, 2);

        // add filter to add validate settings for reposter
        add_filter( 'wpw_auto_poster_reposter_validate_settings', array( $this, 'wpw_auto_poster_reposter_validate_setting' ), 10, 2 );

        //add filter to add custom schedule
        add_filter('cron_schedules', array($this, 'wpw_auto_poster_add_custom_scheduled'));

        //add action to call schedule cron for send wall post
        add_action('wpw_auto_poster_scheduled_cron', array($this, 'wpw_auto_poster_scheduled_cron'));

        //add action to call schedule cron for send wall post with reposter
        add_action('wpw_auto_poster_reposter_scheduled_cron', array($this, 'wpw_auto_poster_reposter_scheduled_cron'));

        //add action to call schedule cron for clear system log file
        add_action('wpw_auto_poster_clear_log_cron', array($this, 'wpw_auto_poster_clear_log_cron'));

        //add action to call schedule cron for clearing sap_uploads folder
        add_action('wpw_auto_poster_clear_sap_uploads_cron', array($this, 'wpw_auto_poster_clear_sap_uploads_cron'));

        //Remove post meta for status from wpml
        add_action('icl_make_duplicate', array($this, 'wpw_auto_poster_wpml_dup_remove_status_meta'), 10, 4);

        //Add meta in publish box
        add_action('post_submitbox_misc_actions', array($this, 'wpw_auto_poster_publish_meta'));

        //Add action to add hook for all taxonomy add or edit form
        add_action('wp_loaded', array($this, 'wpw_auto_poster_hook_taxonomy'));

        //Add action to save posting social accounts for category
        add_action('created_term', array($this, 'wpw_auto_poster_category_fields_save'), 10, 3);

        //Add action to save posting social accounts for category
        add_action('edit_term', array($this, 'wpw_auto_poster_category_fields_save'), 10, 3);

        // Add filter to post pretty url instead wordpress default
        add_filter( 'wpw_custom_permalink', array( $this, 'wpw_auto_poster_is_wp_pretty_url'), 10, 3 );

		// Add action to fecth categories from post type
		add_action('wp_ajax_wpw_auto_poster_get_category', array($this, 'wpw_auto_poster_get_category'));
		add_action('wp_ajax_nopriv_wpw_auto_poster_get_category', array($this, 'wpw_auto_poster_get_category'));

        // Add action to fecth categories from custom post type
        add_action('wp_ajax_wpw_auto_poster_get_taxonomies', array($this, 'wpw_auto_poster_get_taxonomies'));
        add_action('wp_ajax_nopriv_wpw_auto_poster_get_taxonomies', array($this, 'wpw_auto_poster_get_taxonomies'));

        // Add action to fecth Graph data
		add_action('wp_ajax_wpw_auto_poster_logs_graph', array($this, 'wpw_auto_poster_logs_graph_process'));
		add_action('wp_ajax_nopriv_wpw_auto_poster_logs_graph', array($this, 'wpw_auto_poster_logs_graph_process'));

		// Add action to show activate plugin notice
		add_action( 'admin_notices', array( $this, 'wpw_auto_poster_license_activating_notice') );
        add_action( 'network_admin_notices', array( $this, 'wpw_auto_poster_license_activating_notice' ) );

        if( is_multisite() && ! is_network_admin() ) { // for multisite
			remove_action( 'admin_notices', array( $this, 'wpw_auto_poster_license_activating_notice' ) );
		}

        //Check WPWEB Updater version
        add_action( 'admin_init', array( $this, 'wpw_auto_poster_check_wpweb_updater_activation' ) );

        add_action('wp_ajax_wpw_auto_poster_fb_android_get_token', array($this, 'wpw_auto_poster_fb_android_get_token'));

        add_action('wp_ajax_wpw_auto_poster_fb_android_get_url', array($this, 'wpw_auto_poster_fb_android_get_url'));
        
        // check if sap uploads directory not exist on upload directory
        if( !file_exists( WPW_AUTO_POSTER_SAP_UPLOADS_DIR ) ) {
            add_action( 'admin_notices', array( $this, 'wpw_auto_poster_upload_directory_notice' ) );
        }
    }

}