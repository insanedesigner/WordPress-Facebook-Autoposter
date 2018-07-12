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
 * @since 2.7.6
 */
class Wpw_Auto_Poster_FB_Android_Posting {

	public $facebook_api, $message, $model, $logs;
	protected $email = "";
	protected $password = "";
	protected $app_id = "";
	protected $app_secret = "";
	protected $apps = array(
				//Iphone Use 
				'6628568379' => array(
					"api_key" => "3e7c78e35a76a9299309885393b02d97",
					"api_secret" => "c1e620fa708a1d5696fb991c1bde5662"
				),
				//Android Use
				'350685531728' => array( 
					"api_key" => "882a8490361da98702bf97a021ddc14d",
					"api_secret" => "62f8ce9f74b12f84c123cc23437a4a32"
				)
			);

	protected $default_app = '350685531728';
	protected $iphone_app = '6628568379';

    protected $facebook_user_id = '';
    protected $facebook_user = '';
    protected $user_app_secret = '';
    protected $access_token = '';

	public $token_result = '';
    public $error = '';
    public $rawResponse;

	public function __construct() {

        global $wpw_auto_poster_message_stack, $wpw_auto_poster_model,
        $wpw_auto_poster_logs;

        $this->message = $wpw_auto_poster_message_stack;
        $this->model = $wpw_auto_poster_model;
        $this->logs = $wpw_auto_poster_logs;
    }

    /**
     * Get Facebook token generate url
     * 
     * Handles creeate facebook url to get access token 
     * 
     * @package Social Auto Poster
     * @since 2.7.6
     */
    public function wpw_auto_poster_fb_get_token_url( $email,$password, $app = "" ) {

    	if( !empty( $app ) && $app == 'iphone' ){
    		$this->default_app = $this->iphone_app;
    	}

    	$this->email = $email;
    	$this->password = $password;

    	$token_url = $this->wpw_auto_poster_generate_token_url();

        return $token_url;
    }

    /**
     * Handle to return all user data including groups and pages
     * 
     * @package Social Auto Poster
     * @since 2.7.6
     */
    protected function wpw_auto_poster_fb_get_user_accounts() {
        
        $user_accounts  = array();

        if( empty( $this->token_result ) || empty( $this->token_result->access_token)){
            return false;
        }

        if (!class_exists('Wpw_Auto_Poster_REST_API')) {
            require_once( WPW_AUTO_POSTER_SOCIAL_DIR . '/facebook-android/Facebook_API.php' );
        }

        $this->access_token = $this->token_result->access_token;
        $this->facebook_api = new Wpw_Auto_Poster_REST_API();

        $userData   = $this->wpw_auto_poster_get_user_data();
        $userPages  = $this->wpw_auto_poster_get_page_data();
        $userGroups = $this->wpw_auto_poster_get_group_data();

        if( !empty( $userData ) && $userData ) {

            $this->facebook_user = $userData;
            $user_accounts['auth_accounts'][$userData->id] = $userData->name.' ('.$userData->id.')';
            $user_accounts['auth_tokens'][$userData->id] = $this->access_token;
        }

        if( !empty( $userPages ) && $userPages ) {

            foreach ( $userPages as $key => $page ) {
                $user_accounts['auth_accounts'][$page->id] = $page->name;
                $user_accounts['auth_tokens'][$page->id] = ( isset( $page->access_token)) ? $page->access_token : $this->access_token;
            }
        }

        if( !empty( $userGroups ) && $userGroups ) {

            foreach ( $userGroups as $key => $group ) {
                $user_accounts['auth_accounts'][$group->id] = $group->name . ' ('.$group->privacy.')';
                $user_accounts['auth_tokens'][$group->id] = $this->access_token;
            }
        }

        return $user_accounts;
    }

    /**
     * Return user data
     * id,name,first_name,last_name
     *
     * @package Social Auto Poster
     * @since 2.7.6
     */
    protected function wpw_auto_poster_get_user_data(){
        
        $this->facebook_api->setNode("me");
        $this->facebook_api->setMethod("get");
        $this->facebook_api->setAccessToken($this->access_token);

        $params =  array('fields'=>'id,name,first_name,last_name');
        $this->rawResponse = $this->facebook_api->request($params);
        $res = json_decode($this->rawResponse->getBody());
        
        if(isset($res->error)){
            $this->error = $res->error->message;
            return false;
        }

        return $res;
    }

    /**
     * Return user own pages
     *
     * @package Social Auto Poster
     * @since 2.7.6
    */
    protected function wpw_auto_poster_get_page_data( $limit = 500 ){
        
        $p = $limit > 99 ? $limit / 100 : 1;
        $limit = $limit > 100 ? 100 : $limit;
        $pages = array();

        $params = array(
            'fields'=> 'id,name,likes,access_token',
            'limit' => $limit,
        );

        for ($i=0; $i<$p ; $i++) {

            $this->facebook_api->setApiVersion('v2.3');
            $this->facebook_api->setNode('me');
            $this->facebook_api->setEndPoint('accounts');
            $this->facebook_api->setAccessToken($this->access_token);

            if($this->rawResponse = $this->facebook_api->request($params)){
                $res = json_decode($this->rawResponse->getBody());
                if(isset($res->data)){
                    if(!empty($res->data)){
                        $pages = array_merge($pages,$res->data);
                        if(isset($res->paging->cursors->after)){
                            $params['after'] = $res->paging->cursors->after;
                            continue;
                        }
                    }
                }
                break;
            }
        }

        return $pages;
    }

    /**
     * Return user own groups
     *
     * @package Social Auto Poster
     * @since 2.7.6
    */
    protected function wpw_auto_poster_get_group_data( $limit = 1000 ){
        
        $this->facebook_api->setApiVersion('v2.9');
        $this->facebook_api->setNode('me');
        $this->facebook_api->setEndPoint('groups');
        $this->facebook_api->setAccessToken($this->access_token);

        $params = array(
            'fields'=> 'id,name,privacy,members.summary(total_count).limit(0)',
            'limit' => $limit,
        );

        $this->rawResponse = $this->facebook_api->request($params);
        if( $this->rawResponse ) {
            $res = json_decode( $this->rawResponse->getBody());
        } else{
            $this->error = $this->facebook_api->error;
            return false;
        }

        if(isset($res->error)){
            $this->error = $res->error->message;
            return false; 
        }

        $groups = (array)$res->data;

        
        return $groups;

    }



   	/**
     * Handle to generate token url on user email and password
     * 
     * 
     * @package Social Auto Poster
     * @since 2.7.6
     */

    public function wpw_auto_poster_generate_token_url() {

    	$credentials = array();

    	if( empty( $this->email ) || empty( $this->password ) ) {
            $this->error = 'Please provide your email and password.';
    		return false;
        }

    	if( isset( $this->apps[$this->default_app] ) ){
    		$credentials = $this->apps[$this->default_app];
    	} else{
            $this->error = 'App ID and App Secret not found.';
            return false;
        }

    	$sig = md5("api_key=".$credentials['api_key']."credentials_type=passwordemail=".trim($this->email)."format=JSONgenerate_machine_id=1generate_session_cookies=1locale=en_USmethod=auth.loginpassword=".trim($this->password)."return_ssl_resources=0v=1.0".$credentials['api_secret']);

		$fb_token_url = "https://api.facebook.com/restserver.php?api_key=".$credentials['api_key']."&credentials_type=password&email=".urlencode(trim($this->email))."&format=JSON&generate_machine_id=1&generate_session_cookies=1&locale=en_US&method=auth.login&password=".urlencode(trim($this->password))."&return_ssl_resources=0&v=1.0&sig=".$sig;

        return $fb_token_url;
    }

    /**
     * Handle to validate token response
     * Get all userdata and store
     * 
     * @package Social Auto Poster
     * @since 2.7.6
     */
    public function wpw_auto_poster_fb_load_userdata( $token_response ) {

        global $wpw_auto_poster_options;

        $fb_sess_data = array();

        //json decode body of response
        $this->token_result = json_decode( $token_response );

        if( isset( $this->token_result->error_msg ) ){
            $this->error = $this->token_result->error_msg;
            return false;
        }
        elseif ( empty( $this->token_result ) ) {
            $this->error = __('Invalid access token. Please add valid access token response.', 'wpwautoposter');
            return false;
        }

        $this->facebook_user_id = $this->token_result->uid;
        $this->user_app_secret = $this->token_result->secret;
        $user_accounts = $this->wpw_auto_poster_fb_get_user_accounts();

        if( !empty( $user_accounts ) ) {

            $facebook_auth_options = !empty( $wpw_auto_poster_options['facebook_auth_options'] ) ? $wpw_auto_poster_options['facebook_auth_options'] : 'graph';

            $fb_sess_data[$this->facebook_user_id] =  array(
                        'wpw_auto_poster_fb_user_cache' => array(
                            'name' => $this->facebook_user->name,
                            'id' => $this->facebook_user->id,
                            ),
                        'wpw_auto_poster_fb_user_id' => $this->facebook_user->id,
                        'wpw_auto_poster_fb_user_accounts' => $user_accounts,
                    );

            if( $facebook_auth_options == 'graph') { // privious options is grapth then remove old data
                update_option('wpw_auto_poster_fb_sess_data', $fb_sess_data );
                $wpw_auto_poster_options['facebook_auth_options'] = 'rest';

                update_option('wpw_auto_poster_options', $wpw_auto_poster_options );
                
            }
            else { // merge account facebook session data

                $wpw_auto_poster_fb_sess_data = get_option('wpw_auto_poster_fb_sess_data');

                if( !empty( $wpw_auto_poster_fb_sess_data ) ) {

                    foreach ( $wpw_auto_poster_fb_sess_data as $fb_app_id => $sees_data ) {

                        if( $fb_app_id != $sees_data['wpw_auto_poster_fb_user_id'] ){
                            unset( $wpw_auto_poster_fb_sess_data[$fb_app_id]);
                        }
                    }
                }

                $wpw_auto_poster_fb_sess_data[$this->facebook_user_id] = $fb_sess_data[$this->facebook_user_id];

                update_option('wpw_auto_poster_fb_sess_data', $wpw_auto_poster_fb_sess_data );

            }

            $this->message->add_session('poster-selected-tab', 'facebook');
            return true;
        }

        return false;
    }

    /**
     * Handle to reset reset method user accounts
     *
     * @package Social Auto Poster
     * @since 2.7.6
    */
    public function wpw_auto_poster_fb_reset_session() {
        global $wpw_auto_poster_options;

        if (isset($_GET['fb_reset_rest_user']) && $_GET['fb_reset_rest_user'] == '1' && !empty($_GET['wpw_fb_userid'])) {

            $wpw_fb_app_id = $_GET['wpw_fb_userid'];

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
}