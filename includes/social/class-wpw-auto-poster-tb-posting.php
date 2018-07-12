<?php 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Tumblr Posting Class
 *
 * Handles all the functions to tweet on twitter
 *
 * @package Social Auto Poster
 * @since 1.3.0
 */
class Wpw_Auto_Poster_TB_Posting {

	public $tumblr,$model,$message;

	public function __construct() {

		global $wpw_auto_poster_model, $wpw_auto_poster_message_stack, $wpw_auto_poster_logs;

		$this->model	= $wpw_auto_poster_model;
		$this->message	= $wpw_auto_poster_message_stack;
		$this->logs		= $wpw_auto_poster_logs;

		//initialize some tumblr data
		$this->wpw_auto_poster_tb_initialize();

		//add action init for making user to logged in tumblr
		add_action( 'init', array( $this, 'wpw_auto_poster_tb_user_logged_in' ) );
	}
	/**
	 * Include Facebook Class
	 * 
	 * Handles to load facebook class
	 * 
	 * @package Social Auto Poster
 	 * @since 1.3.0
	 */
	public function wpw_auto_poster_load_tumblr() {

		global $wpw_auto_poster_options;

		//check facebook application id and application secret is not empty or not
		if( !empty( $wpw_auto_poster_options['tumblr_consumer_key'] ) && !empty( $wpw_auto_poster_options['tumblr_consumer_secret'] ) ) {

			if( !class_exists( 'TumblrOAuth' ) ) {
				require_once( WPW_AUTO_POSTER_SOCIAL_DIR . '/tumblr/tumblrOAuth.php' );
			}
			return true;
			
		} else {
			return false;
		}
	}
	/**
	 * Make Logged In User to Tumblr
	 * 
	 * @package Social Auto Poster
	 * @since 1.3.0
	 */
	public function wpw_auto_poster_tb_user_logged_in() {
		
		// code will excute when user does connect with tumblr
		//check $_GET['wpwautoposter'] isset and equals to tumblr
		//check $_GET['authtumb'] isset and quals to 1
		if( isset( $_GET['authtumb'] ) && $_GET['authtumb'] == '1'
			&& isset( $_GET['wpwautoposter'] ) && $_GET['wpwautoposter'] == 'tumblr' ) { // if user allows access to tumblr

			//record logs for grant extended permission
			$this->logs->wpw_auto_poster_add( 'Tumblr Grant Extended Permission', true );

			//load tumblr class
			$tumblr = $this->wpw_auto_poster_load_tumblr();

			//check tumblr loaded or not
			if( !$tumblr ) return false;

			$pageurl = $this->model->wpw_auto_poster_self_url();
			$wpw_auto_poster_tumb_callback_url = add_query_arg( array( 'auth' => 'tumbauth', 'authtumb' => false ), $pageurl ); //'action' => 'tumblr', 
			$wpw_auto_poster_tumb_oauth = new TumblrOAuth(WPW_AUTO_POSTER_TB_CONS_KEY, WPW_AUTO_POSTER_TB_CONS_SECRET );

			$wpw_auto_poster_tumb_request_token = $wpw_auto_poster_tumb_oauth->getRequestToken($wpw_auto_poster_tumb_callback_url); 

			$_SESSION['wpw_auto_poster_tumblr'] = $wpw_auto_poster_tumb_request_token;

			//record logs for token is set properly to session
			$this->logs->wpw_auto_poster_add( 'Request token assign to the session' );

			if( $wpw_auto_poster_tumb_oauth->http_code == 200 ) {

				//record logs for token is generated successfully
				$this->logs->wpw_auto_poster_add( 'Oauth token successfully generated' );
				$url = $wpw_auto_poster_tumb_oauth->getAuthorizeURL( $wpw_auto_poster_tumb_request_token['oauth_token'] ); 
				wp_redirect( $url );
				exit;
			}
		} //end if

		// code will excute when user does connect with tumblr
		if ( isset($_GET['auth']) && $_GET['auth'] == 'tumbauth' 
			&& isset( $_GET['wpwautoposter'] ) && $_GET['wpwautoposter'] == 'tumblr' ) { 

			//load tumblr class
			$tumblr = $this->wpw_auto_poster_load_tumblr();

			//check tumblr loaded or not
			if( !$tumblr ) return false;

			//record logs when user is connected with tumblr
			$this->logs->wpw_auto_poster_add( 'User is connected to tumblr successfully' );

			$wpw_auto_poster_tumb_oauth = new TumblrOAuth(WPW_AUTO_POSTER_TB_CONS_KEY, WPW_AUTO_POSTER_TB_CONS_SECRET, $_SESSION['wpw_auto_poster_tumblr']['oauth_token'], $_SESSION['wpw_auto_poster_tumblr']['oauth_token_secret']);
			$wpw_auto_poster_tumb_access_token = $wpw_auto_poster_tumb_oauth->getAccessToken($_REQUEST['oauth_verifier']); 

			$_SESSION['wpw_auto_poster_tumblr']['oauth_token'] = isset($wpw_auto_poster_tumb_access_token['oauth_token']) ? $wpw_auto_poster_tumb_access_token['oauth_token'] : $_SESSION['wpw_auto_poster_tumblr']['oauth_token'];
   			$_SESSION['wpw_auto_poster_tumblr']['oauth_token_secret'] = isset($wpw_auto_poster_tumb_access_token['oauth_token_secret']) ? $wpw_auto_poster_tumb_access_token['oauth_token_secret'] : $_SESSION['wpw_auto_poster_tumblr']['oauth_token_secret'];

   			$wpw_auto_poster_tumb_oauth = new TumblrOAuth(WPW_AUTO_POSTER_TB_CONS_KEY, WPW_AUTO_POSTER_TB_CONS_SECRET, $_SESSION['wpw_auto_poster_tumblr']['oauth_token'], $_SESSION['wpw_auto_poster_tumblr']['oauth_token_secret']);

			$wpw_auto_poster_account_info = $wpw_auto_poster_tumb_oauth->get('http://api.tumblr.com/v2/user/info');
			$wpw_auto_poster_account_url = ( isset($wpw_auto_poster_account_info->response->user->blogs[0]->url) && !empty($wpw_auto_poster_account_info->response->user->blogs[0]->url) ) ? $wpw_auto_poster_account_info->response->user->blogs[0]->url : ''; 

			$_SESSION['wpw_auto_poster_tb_user_id'] = isset( $_SESSION['wpw_auto_poster_tb_user_id'] )
				? $_SESSION['wpw_auto_poster_tb_user_id'] : $wpw_auto_poster_account_info->response->user->name;

			$_SESSION['wpw_auto_poster_tb_cache']	= isset( $_SESSION['wpw_auto_poster_tb_cache'] ) 
				? $_SESSION['wpw_auto_poster_tb_cache'] : $wpw_auto_poster_account_info->response->user;

			$_SESSION['wpw_auto_poster_tb_oauth'] = isset($_SESSION['wpw_auto_poster_tb_oauth']) 
				? $_SESSION['wpw_auto_poster_tb_oauth'] : $_SESSION['wpw_auto_poster_tumblr'];

			//record logs all user authentication data assign to session
			$this->logs->wpw_auto_poster_add( 'User authentication data assign to session successfully' );

			// start code to manage session from database 			
			$wpw_auto_poster_tb_sess_data = get_option( 'wpw_auto_poster_tb_sess_data' );

			if( empty( $wpw_auto_poster_tb_sess_data ) ) {
				
				$sess_data = array(

										'wpw_auto_poster_tb_user_id'	=> $wpw_auto_poster_account_info->response->user->name,
										'wpw_auto_poster_tb_cache'		=> $wpw_auto_poster_account_info->response->user,
										'wpw_auto_poster_tb_oauth'		=> $_SESSION['wpw_auto_poster_tumblr']
									);
		      	update_option( 'wpw_auto_poster_tb_sess_data', $sess_data );
		      	//record logs for session data updated to options
				$this->logs->wpw_auto_poster_add( 'User data updated to options' );
			}
			//set session to set tab selected in settings page
			$this->message->add_session( 'poster-selected-tab', 'tumblr' );

			//record logs for grant extend successfully
			$this->logs->wpw_auto_poster_add( 'Grant Extended Permission Successfully.' );

			// end code to manage session from database
	      	$pageurl = add_query_arg( array( 	
	      										'auth'				=> false, 
	      										'wpwautoposter' 	=> false,
	      										'oauth_verifier'	=> false,
	      										'oauth_token'		=> false
	      									)
	      									, $this->model->wpw_auto_poster_self_url() );
			wp_redirect($pageurl);
			exit;
			
		} // end if
	}
	/**
	 * Initializes Some Data to session
	 * 
	 * @package Social Auto Poster
	 * @since 1.3.0
	 * 
	 */
	public function wpw_auto_poster_tb_initialize() {

		global $wpw_auto_poster_options;

		//check tumblr consumer key and secret not empty
		if( !empty( $wpw_auto_poster_options['tumblr_consumer_key'] ) && !empty( $wpw_auto_poster_options['tumblr_consumer_secret'] ) ) {
			//Set Session From Options Value
			$wpw_auto_poster_tb_sess_data = get_option( 'wpw_auto_poster_tb_sess_data' );

			if( !empty( $wpw_auto_poster_tb_sess_data ) &&  !isset( $_SESSION['wpw_auto_poster_tb_user_id'] ) ) { //check user data is not empty
				$_SESSION['wpw_auto_poster_tb_user_id'] = $wpw_auto_poster_tb_sess_data['wpw_auto_poster_tb_user_id'];
				$_SESSION['wpw_auto_poster_tb_cache'] = $wpw_auto_poster_tb_sess_data['wpw_auto_poster_tb_cache'];
				$_SESSION['wpw_auto_poster_tb_oauth'] = $wpw_auto_poster_tb_sess_data['wpw_auto_poster_tb_oauth'];
				$_SESSION['wpw_auto_poster_tumblr'] = $wpw_auto_poster_tb_sess_data['wpw_auto_poster_tb_oauth']; //assign stored oauth token to database
			}
		}
	}
	/**
	 * Get Tumblr Login URL
	 * 
	 * Handles to Return Tumblr URL
	 * 
	 * @package Social Auto Poster
	 * @since 1.3.0
	 * 
	 */

	public function wpw_auto_poster_get_tb_login_url() {

		$preparedurl = add_query_arg( array( 'authtumb' => '1', 'wpwautoposter' => 'tumblr' ) ); 
		return $preparedurl;
	}

	/**
	 * Post To Tumblr
	 * 
	 * Handles to Post on Tumblr account
	 * 
	 * @package Social Auto Poster
	 * @since 1.3.0
	 */
	public function wpw_auto_poster_post_to_tumblr( $post ) {

		global $wpw_auto_poster_options;
		
		//load tumblr class
		$tumblr = $this->wpw_auto_poster_load_tumblr();

		//check tumblr loaded or not
		if( !$tumblr ) return false;

		//check tumblr user id is set in session and not empty
		if( isset( $_SESSION['wpw_auto_poster_tb_user_id'] ) && !empty( $_SESSION['wpw_auto_poster_tb_user_id'] ) ) {
		
			//posting logs data
			$posting_logs_data = array();

			//Initialize tags and categories
            $tags_arr = array();
            $cats_arr = array();
			
			//record logs for tumblr posting
			$this->logs->wpw_auto_poster_add( 'Tumblr posting to user account begins.' );
	
			//meta prefix
			$prefix = WPW_AUTO_POSTER_META_PREFIX;

			$post_type = $post->post_type; // Post type

			//Get posting type
			$posting_type_meta 	= get_post_meta( $post->ID, $prefix . 'tb_posting_type', true );
			$posting_type_global= !empty( $wpw_auto_poster_options['tb_posting_type'] ) ? $wpw_auto_poster_options['tb_posting_type'] : '';
			$posting_type 		= !empty( $posting_type_meta ) ? $posting_type_meta : $posting_type_global;

			//Get image url
			$post_img_meta 		= get_post_meta( $post->ID, $prefix . 'tb_post_image', true );
			$post_featured_img 	= wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );

			//check custom image is set in meta and not empty
            if( !empty( $post_img_meta['src'] ) ) {
                $post_img = $post_img_meta['src'];
            } elseif ( !empty( $post_featured_img[0] ) ) {
                //check post featrued image is set the use that image
                $post_img = $post_featured_img[0];
            } else {
                //else get post image from settings page
                $post_img = !empty( $wpw_auto_poster_options['tb_custom_img'] ) ? $wpw_auto_poster_options['tb_custom_img'] : '';
            }

			$wpw_auto_poster_tb_sess_data = get_option( 'wpw_auto_poster_tb_sess_data' );
			$unique = 'false';
	
			//user details
			$userdata = get_userdata( $post->post_author );
			$first_name = $userdata->first_name; //user first name
			$last_name = $userdata->last_name; //user last name
	
			//published status
			$ispublished = get_post_meta( $post->ID, $prefix . 'tb_status', true );

			// Get all selected tags for selected post type for hashtags support
            if(isset($wpw_auto_poster_options['tb_post_type_tags']) && !empty($wpw_auto_poster_options['tb_post_type_tags'])) {

                $custom_post_tags = $wpw_auto_poster_options['tb_post_type_tags'];
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
            if(isset($wpw_auto_poster_options['tb_post_type_cats']) && !empty($wpw_auto_poster_options['tb_post_type_cats'])) {

                $custom_post_cats = $wpw_auto_poster_options['tb_post_type_cats'];
                if(isset($custom_post_cats[$post_type]) && !empty($custom_post_cats[$post_type])){  
                    foreach($custom_post_cats[$post_type] as $key => $category){
                        $term_list = wp_get_post_terms( $post->ID, $category, array("fields" => "names") );
                        foreach($term_list as $term_single) {
                            $cats_arr[] = $term_single;
                        }
                    }
                    
                }
            }
	
			//post title
			$posttitle = $post->post_title;
			$customtitle = get_post_meta( $post->ID, $prefix . 'tb_post_title', true );
			$title = !empty( $customtitle ) ? $customtitle : $posttitle;
	
			$wpw_auto_poster_tb_custom_link 	= get_post_meta( $post->ID, $prefix . 'tb_custom_post_link', true );

			// get global tumblr custom message
			$tb_global_message_template = ( isset( $wpw_auto_poster_options["tb_global_message_template"] ) )? $wpw_auto_poster_options["tb_global_message_template"] : '';

			//custom description from meta
			$tb_meta_message_template = get_post_meta( $post->ID, $prefix . 'tb_post_desc', true );

			$post_content = strip_shortcodes($post->post_content);
            //strip html kses and tags
            $post_content = $this->model->wpw_auto_poster_stripslashes_deep($post_content);
            //decode html entity
            $post_content = $this->model->wpw_auto_poster_html_decode($post_content);

			if ( !empty( $tb_meta_message_template ) ){
				//custom description set at tumblr post meta level
				$description = $tb_meta_message_template;

			} elseif( !empty( $tb_global_message_template )){
				//custom description set at tumblr global settings
				$description = $tb_global_message_template;

			} else {
				//custom description not set at tumblr global settings then take post content
				$description = $post_content;
			}

			$description = $this->model->wpw_auto_poster_stripslashes_deep( $description, true );

			// Get post excerpt
			$excerpt = !empty( $post->post_excerpt ) ? $post->post_excerpt : '';

			// Get post tags
            //$tags_arr   = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
            $hashtags   = ( !empty( $tags_arr ) ) ? '#'.implode( ' #', $tags_arr ) : '';

            // get post categories
            /*$hashcats = array();
            foreach((get_the_category( $post->ID )) as $category) {
               $hashcats[] = $category->cat_name;
            }*/
            $hashcats   = ( !empty( $cats_arr ) ) ? '#'.implode( ' #', $cats_arr ) : '';
	
			//if post is published on facebook once then change url to prevent duplication
			if( isset( $ispublished ) && $ispublished == '1' ) { 
				$unique = 'true';
			}
			//post link for posting to facebook user wall
			$postlink = isset( $wpw_auto_poster_tb_custom_link ) && !empty( $wpw_auto_poster_tb_custom_link ) ? $wpw_auto_poster_tb_custom_link : '';
			//if custom link is set or not
			$customlink = !empty( $postlink ) ? 'true' : 'false';
			//do url shortner
			$postlink = $this->model->wpw_auto_poster_get_short_post_link( $postlink, $unique, $post->ID, $customlink, 'tb' );
	
			

			//tumblr account URL
			$wpw_auto_poster_account_url = ( isset( $wpw_auto_poster_tb_sess_data['wpw_auto_poster_tb_cache']->blogs[0]->url ) && !empty( $wpw_auto_poster_tb_sess_data['wpw_auto_poster_tb_cache']->blogs[0]->url) ) ? $wpw_auto_poster_tb_sess_data['wpw_auto_poster_tb_cache']->blogs[0]->url : '';
			$wpw_auto_poster_account_url = trim( str_ireplace( 'http://', '', $wpw_auto_poster_account_url ) );
			$wpw_auto_poster_account_url = trim( str_ireplace( 'https://', '', $wpw_auto_poster_account_url ) );

			if ( substr( $wpw_auto_poster_account_url, -1 ) == '/' ) {
				$wpw_auto_poster_account_url = substr( $wpw_auto_poster_account_url, 0, -1 );
			}

	 		$search_arr = array( '{title}', '{first_name}' , '{last_name}', '{sitename}', '{hashtags}', '{hashcats}', '{link}', '{excerpt}','{content}' );
			$replace_arr = array( $posttitle, $first_name, $last_name, get_option( 'blogname'), $hashtags, $hashcats, $postlink, $excerpt,$post_content );

			$code_matches = array();
    
            // check if template tags contains {content-numbers}
            if( preg_match_all( '/\{(content)(-)(\d*)\}/', $description, $code_matches ) ) {

                $trim_tag = $code_matches[0][0];
                $trim_length = $code_matches[3][0];
                $trim_content = substr( $post_content, 0, $trim_length);
                $search_arr[] = $trim_tag;
                $replace_arr[] = $trim_content;
            }

			$description = str_replace( $search_arr, $replace_arr, $description );
			
			if( isset( $wpw_auto_poster_options['tumblr_content_type'] ) && !empty( $wpw_auto_poster_options['tumblr_content_type'] ) ) { //check tumblr content is set full or snippest
				//it will consider first 200 characters when snippests is selected
				$description = $this->model->wpw_auto_poster_excerpt( $description, 200 );
				$description .= '...';
			} else {
				//else it will consider full content
				$description = $description;
			}

			//decode html from posting content
			$description = $this->model->wpw_auto_poster_html_decode( $description );

			// replace title tag support value
			$search_arr = array( '{title}', '{link}', '{first_name}' , '{last_name}', '{sitename}', '{site_name}', '{content}', '{excerpt}', '{hashtags}', '{hashcats}' );
			$replace_arr = array( $posttitle, $postlink, $first_name, $last_name, get_option( 'blogname'), get_option( 'blogname'), $content, $excerpt, $hashtags, $hashcats );

			// check if template tags contains {content-numbers}
            if( preg_match_all( '/\{(content)(-)(\d*)\}/', $title, $code_matches ) ) {

                $trim_tag = $code_matches[0][0];
                $trim_length = $code_matches[3][0];
                $trim_content = substr( $post_content, 0, $trim_length);
                $search_arr[] = $trim_tag;
                $replace_arr[] = $trim_content;
            }
			
			$title = str_replace( $search_arr, $replace_arr, $title );

			$wpw_auto_poster_tumb_oauth = new TumblrOAuth(WPW_AUTO_POSTER_TB_CONS_KEY, WPW_AUTO_POSTER_TB_CONS_SECRET, $_SESSION['wpw_auto_poster_tumblr']['oauth_token'], $_SESSION['wpw_auto_poster_tumblr']['oauth_token_secret']); 

			//Build posting arguments based on Type
			switch ($posting_type) {
				case 'link':

					//Set all params
					$tumblrdata = apply_filters( 'wpw_post_meta_tb_posting_args', array(
						'type' 	=> 'link',
						'title' => $title,
						'url' 	=> $postlink,
						'description' 	=> $description,
						'thumbnail' 	=> $post_img,
						'excerpt' 		=> !empty( $post->post_excerpt ) ? $post->post_excerpt : '',
					), $post );

					break;

				case 'photo':

					//Set all params
					$tumblrdata = apply_filters( 'wpw_post_meta_tb_posting_args', array(
						'type' 		=> 'photo',
						'caption' 	=> $title,
						'link' 		=> $postlink,
						'source' 	=> $post_img,
					), $post );

					break;

				case 'text':
				default:

					//Final posting description
					$finaldescription = $postlink . '<br /><br />' . $description;
					$tumblrdata = apply_filters( 'wpw_post_meta_tb_posting_args', array( 'type' => 'text', 'title' => $title,  'body' => $finaldescription ), $post );

					break;
			}

			//posting logs data
			$posting_logs_data = $tumblrdata;
					
			//record logs for tumblr data
			$this->logs->wpw_auto_poster_add( 'Tumblr post data : ' . var_export( $tumblrdata, true ) );
			
			//Send post to tumblr account
			try {	
				
				$postinfo = $wpw_auto_poster_tumb_oauth->post( 'http://api.tumblr.com/v2/blog/'.$wpw_auto_poster_account_url.'/post', $tumblrdata ); //'tags'=>$tags, 'source'=>get_permalink($post->ID)

				$code = $postinfo->meta->status;
				//record logs for post posted to tumblr
				if( isset( $postinfo->response->id ) && !empty( $postinfo->response->id ) ) {
					
					$user_profile_data 	= isset( $_SESSION['wpw_auto_poster_tb_cache'] ) ? $_SESSION['wpw_auto_poster_tb_cache'] : '';
					$user_profile_id 	= isset( $user_profile_data->name ) ? $user_profile_data->name : '';
					
					//User details
					$posting_logs_user_details = array(
															'account_id' 			=> $user_profile_id,
															'display_name'			=> $user_profile_id,
															'user_name'				=> $user_profile_id,
															'tumblr_consumer_key' 	=> WPW_AUTO_POSTER_TB_CONS_KEY,
															'tumblr_consumer_secret'=> WPW_AUTO_POSTER_TB_CONS_SECRET,
														);
					
					//posting logs store into database
					$this->model->wpw_auto_poster_insert_posting_log( $post->ID, 'tb', $posting_logs_data, $posting_logs_user_details );
					
					$this->logs->wpw_auto_poster_add( 'Tumblr posted to user account with Response ID ' . $postinfo->response->id  );
					
				} //end if to check response id is set & not empty
				else {
					
					if( is_array($postinfo->response->errors) ) {
					
						// added in version 1.5.4	
						$this->logs->wpw_auto_poster_add( 'Tumblr error: ' . $postinfo->response->errors[0] );
						sap_add_notice( sprintf( __('Tumblr: Error while posting %s', 'wpwautoposter' ), $postinfo->response->errors[0] ), 'error');
					} else {
						
						// added in version 1.5.4	
						$this->logs->wpw_auto_poster_add( 'Tumblr error: ' . $postinfo->response->errors->Unprocessable );
						sap_add_notice( sprintf( __('Tumblr: Error while posting %s', 'wpwautoposter' ), $postinfo->response->errors->Unprocessable ), 'error');
					}
					
				}
			 	return $code;
			 	
			} catch ( Exception $e ) {

				//record logs exception generated
				$this->logs->wpw_auto_poster_add( 'Tumblr error: ' . $e->__toString() );
				sap_add_notice( sprintf( __('Tumblr: Something was wrong while posting %s', 'wpwautoposter' ), $e->__toString() ), 'error');
				return false;
			}
		} else {
			//record logs when grant extended permission not set
			$this->logs->wpw_auto_poster_add( 'Tumblr error: Grant extended permissions not set.' );
			sap_add_notice( __('Tumblr: Please give Grant extended permission before posting to the Tumblr.', 'wpwautoposter' ), 'error');
		}
	}
	/**
	 * Reset Sessions
	 *
	 * Resetting the Tumblr sessions when the admin clicks on
	 * its link within the settings page.
	 *
	 * @package Social Auto Poster
	 * @since 1.3.0
	 */
	public function wpw_auto_poster_tb_reset_session() {

		update_option( 'wpw_auto_poster_tb_sess_data', '' );
		unset( $_SESSION['wpw_auto_poster_tb_user_id'] );
		unset( $_SESSION['wpw_auto_poster_tb_cache'] );
		unset( $_SESSION['wpw_auto_poster_tumblr'] );
	}
	
	/**
	 * Tumblr Posting
	 * 
	 * Handles to tumblr posting
	 * by post data
	 * 
	 * @package Social Auto Poster
 	 * @since 1.5.0
	 */
	public function wpw_auto_poster_tb_posting( $post ) {
		
		global $wpw_auto_poster_options;
		
		$prefix = WPW_AUTO_POSTER_META_PREFIX;

					
		$res = $this->wpw_auto_poster_post_to_tumblr( $post );
		
		if ( $res == '201' ) { //check post is publish on tumblr or not
			
			//record logs for posting done on tumblr
			$this->logs->wpw_auto_poster_add( 'Tumblr posting completed successfully.' );
			
			update_post_meta( $post->ID, $prefix . 'tb_status', '1' );

			// get current timestamp and update meta as published date/time
            $current_timestamp = current_time( 'timestamp' );
            update_post_meta($post->ID, $prefix . 'published_date', $current_timestamp);
            
			return true;
		}
		
		return false;
	}
}
?>