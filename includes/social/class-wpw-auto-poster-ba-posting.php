<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * BufferApp Posting Class
 *
 * Handles all the functions to tweet on twitter
 *
 * @package Social Auto Poster
 * @since 1.3.0
 */
class Wpw_Auto_Poster_BA_Posting {

	public $bufferapp, $model, $message, $logs;
	
	public function __construct() {
	
		global $wpw_auto_poster_model, $wpw_auto_poster_message_stack, $wpw_auto_poster_logs;
		
		$this->model	= $wpw_auto_poster_model;
		$this->message	= $wpw_auto_poster_message_stack;
		$this->logs		= $wpw_auto_poster_logs;
		
		//initialize the session value when data is saved in database
		$this->wpw_auto_poster_ba_initialize();
	
		//add action init for making user to logged in tumblr
		add_action( 'init', array( $this, 'wpw_auto_poster_ba_user_logged_in' ) );
	}
	
	/**
	 * 
	 * User Logged In
	 * 
	 * Handles to make user to logged in buffer app
	 *
	 * @package Social Auto Poster
	 * @since 1.3.0
	 */
	
	public function wpw_auto_poster_ba_user_logged_in() {
		
		global $pagenow;
		
		//if page is settings page of our plugin or page is new post page or edit page
		if( isset( $_GET['code'] ) && !empty( $_GET['code'] ) 
			&& isset( $_GET['wpwautoposter'] ) && $_GET['wpwautoposter'] == 'bufferapp' ) { //   
			
			//call back url
			$wpw_auto_poster_buffer_callback_url = add_query_arg( array( 'wpwautoposter' => 'bufferapp' ), site_url() );
			
			$result = wp_remote_post( 'https://api.bufferapp.com/1/oauth2/token.json', array(
										'body' => array(
													'client_id' 	=> WPW_AUTO_POSTER_BA_CLIENT_ID,
													'client_secret' => WPW_AUTO_POSTER_BA_CLIENT_SECRET,
													'redirect_uri' 	=> $wpw_auto_poster_buffer_callback_url,
													'code' 			=> $_GET['code'],
													'grant_type' 	=> 'authorization_code'
													),
										'sslverify' 	=> false,
									));
			if ( $result['response']['code'] == 200 ) {
				
				// Check the body contains an access token
				$body = json_decode($result['body']);
				
				if ($body->access_token != '') {
					
					$accounts = $this->wpw_auto_poster_bufferapp_request( $body->access_token, 'profiles.json' );
					
					if ($accounts && count($accounts) > 0) {
						
						$_SESSION['wpw_auto_poster_ba_user_id'] = isset( $_SESSION['wpw_auto_poster_ba_user_id'] )
							? $_SESSION['wpw_auto_poster_ba_user_id'] : '1';
			
						$_SESSION['wpw_auto_poster_ba_cache']	= isset( $_SESSION['wpw_auto_poster_ba_cache'] ) 
							? $_SESSION['wpw_auto_poster_ba_cache'] : $accounts;
							
						$_SESSION['wpw_auto_poster_ba_access_token']	= isset( $_SESSION['wpw_auto_poster_ba_access_token'] ) 
							? $_SESSION['wpw_auto_poster_ba_access_token'] : $body->access_token;
							
						// start code to manage session from database 			
						$wpw_auto_poster_ba_sess_data = get_option( 'wpw_auto_poster_ba_sess_data' );
						if( empty( $wpw_auto_poster_ba_sess_data ) ) {
							
							$sess_data = array(
													'wpw_auto_poster_ba_user_id'		=> '1',
													'wpw_auto_poster_ba_cache'			=> $accounts,
													'wpw_auto_poster_ba_access_token'	=> $body->access_token
												);
							
					      	update_option( 'wpw_auto_poster_ba_sess_data', $sess_data );
						}
				      	// end code to manage session from database
				      	
					}
					
					//set session to set tab selected in settings page
					$this->message->add_session( 'poster-selected-tab', 'bufferapp' );
					
					//when 
					$redirecturl = add_query_arg( array( 
														 'post_type'	=>	'wpw_auto_poster',
														 'page'			=>	'wpw-auto-poster-settings' 
														),admin_url('edit.php') 
												);
					wp_redirect( $redirecturl );
					exit;
				}
			}
			
		} else if(isset($_GET['error']) && $_GET['error'] == 'access_denied') {
			//reset session of buffer app
			$this->wpw_auto_poster_ba_reset_session();
		}
		
	}
	
	
	/**
	 * Initialize Some Data to Session
	 * 
	 * Handles to set some required data to session
	 *
	 * @access private
	 */
	public function wpw_auto_poster_ba_initialize() {

		global $wpw_auto_poster_options;

		//Set Session From Options Value
		$wpw_auto_poster_ba_sess_data = get_option( 'wpw_auto_poster_ba_sess_data' );
		
		//check tumblr consumer key and secret not empty
		if( !empty( $wpw_auto_poster_options['bufferapp_client_id'] ) && !empty( $wpw_auto_poster_options['bufferapp_client_secret'] ) ) {
		
			if( !empty( $wpw_auto_poster_ba_sess_data ) && !isset( $_SESSION['wpw_auto_poster_ba_user_id'] ) ) { //check user data is not empty
				
				$_SESSION['wpw_auto_poster_ba_user_id'] 		= $wpw_auto_poster_ba_sess_data['wpw_auto_poster_ba_user_id'];
				$_SESSION['wpw_auto_poster_ba_cache'] 			= $wpw_auto_poster_ba_sess_data['wpw_auto_poster_ba_cache'];
				$_SESSION['wpw_auto_poster_ba_access_token'] 	= $wpw_auto_poster_ba_sess_data['wpw_auto_poster_ba_access_token'];
			}
		}
		
	}
	
	/**
	 * Send Request to Bufferapp
	 *
	 * Handles to send request to
	 * bufferapp
	 * 
	 * @package Social Auto Poster
	 * @since 1.3.0
	 */
	public function wpw_auto_poster_bufferapp_request($accessToken, $cmd, $method = 'get', $params = array()) {
		
    	// Check for access token
    	if ($accessToken == '') return 'Invalid access token';
		
		// Send request
		switch ($method) {
			case 'get':
				$result = wp_remote_get( 'https://api.bufferapp.com/1/'.$cmd.'?access_token='.$accessToken, array(
		    		'body' 		=>	$params,
		    		'sslverify' =>	false
		    	));
				break;
			case 'post':
				$result = wp_remote_post( 'https://api.bufferapp.com/1/'.$cmd.'?access_token='.$accessToken, array(
		    		'body'		=>	$params,
		    		'sslverify'	=>	false,
		    		'timeout' 	=>  45,
		    	));
				break;
		}
    	
    	// Check the request is valid
    	if ( is_wp_error( $result ) ) return $result->get_error_message();
    	
    	//json decode body of response
    	$resultdata = json_decode( $result['body'] );
    	
		if ( $result['response']['code'] != 200 ) return $resultdata->message;
		//return 'Error '.$result['response']['code'].' while trying to authenticate: '.$result['response']['message'].'. Please try again.';
		
		return $resultdata;
    }

	/**
	 * Get BufferApp Login URL
	 * 
	 * Handles to Return BufferApp URL
	 * 
	 * @package Social Auto Poster
	 * @since 1.3.0
	 * 
	 */
	public function wpw_auto_poster_get_bufferapp_login_url() {
		
		$preparedurl = add_query_arg( 
									array( 
											'client_id'		=> WPW_AUTO_POSTER_BA_CLIENT_ID, 
											'response_type'	=> 'code', 
											'redirect_uri'	=> add_query_arg( array( 'wpwautoposter' => 'bufferapp' ), site_url() )
										),
									'https://bufferapp.com/oauth2/authorize'
								);
		return $preparedurl;
	}
	
	/**
	 * Post To BufferApp
	 * 
	 * Handles to Post on BufferApp account
	 * 
	 * @package Social Auto Poster
	 * @since 1.3.0
	 */
	public function wpw_auto_poster_post_to_bufferapp( $post ) {
		
		global $wpw_auto_poster_options;
		
		//meta prefix
		$prefix = WPW_AUTO_POSTER_META_PREFIX;
		
		if( isset( $_SESSION['wpw_auto_poster_ba_access_token'] ) && !empty( $_SESSION['wpw_auto_poster_ba_access_token'] ) ) { 

			//posting logs data
			$posting_logs_data = array();
			
			//record logs for BufferApp posting
			$this->logs->wpw_auto_poster_add( 'BufferApp posting to user account(s) begins.' );
						
			//unique url flag
			$unique 	= 'false';
			
			//published status
			$ispublished = get_post_meta( $post->ID, $prefix. 'ba_status', true );
			//if post is published on bufferapp once then change url to prevent duplication
			if( isset( $ispublished ) && $ispublished == '1' ) { 
				$unique = 'true';
			}
			
			//get user data of post author
			$userdata 	= get_userdata( $post->post_author );
			//user first name
			$first_name = $userdata->first_name;
			//user last name
			$last_name 	= $userdata->last_name; 
			//post type
			$post_type 	= $post->post_type;
			
			//custom title from metabox
			$customtitle = get_post_meta( $post->ID, $prefix . 'ba_post_title', true );
			//if not set in meta take post title
			$title 		 = !empty( $customtitle ) ? $customtitle : $post->post_title;
			//strip html kses and tags
			$title 		 = $this->model->wpw_auto_poster_stripslashes_deep( $title, true );
			//decode some html
			$title 		 = $this->model->wpw_auto_poster_html_decode( $title );


			// Get all selected tags for a post type
            if(isset($wpw_auto_poster_options['ba_post_type_tags']) && !empty($wpw_auto_poster_options['ba_post_type_tags'])) {

                $custom_post_tags = $wpw_auto_poster_options['ba_post_type_tags'];
                if(isset($custom_post_tags[$post_type]) && !empty($custom_post_tags[$post_type])){  
                    foreach($custom_post_tags[$post_type] as $key => $tag){
                        $term_list = wp_get_post_terms( $post->ID, $tag, array("fields" => "names") );
                        foreach($term_list as $term_single) {
                            $tags_arr[] = $term_single;
                        }
                    }
                    
                }
            }

            // Get all selected categories for a post type
            if(isset($wpw_auto_poster_options['ba_post_type_cats']) && !empty($wpw_auto_poster_options['ba_post_type_cats'])) {

                $custom_post_cats = $wpw_auto_poster_options['ba_post_type_cats'];
                if(isset($custom_post_cats[$post_type]) && !empty($custom_post_cats[$post_type])){  
                    foreach($custom_post_cats[$post_type] as $key => $category){
                        $term_list = wp_get_post_terms( $post->ID, $category, array("fields" => "names") );
                        foreach($term_list as $term_single) {
                            $cats_arr[] = $term_single;
                        }
                    }
                    
                }
            }
		
			//get user on whom wall post will be posted
			$wpw_auto_poster_ba_user_ids	= get_post_meta( $post->ID, $prefix . 'ba_post_to_accounts' );
			//bufferapp user id on whose wall the post will be posted
			$ba_user_ids = '';
			
			//check there is bufferapp user ids are set and not empty in metabox
			if( isset( $wpw_auto_poster_ba_user_ids ) && !empty( $wpw_auto_poster_ba_user_ids ) ) {
				//users from metabox
				$ba_user_ids 	= $wpw_auto_poster_ba_user_ids;	
			} //end if
			
			//check bufferapp user ids are empty in metabox and set in settings page
			if( empty( $ba_user_ids ) 
				&& isset( $wpw_auto_poster_options[ 'ba_type_'.$post_type.'_user' ] ) 
				&& !empty( $wpw_auto_poster_options[ 'ba_type_'.$post_type.'_user' ] ) ) {
				//users from settings
				$ba_user_ids = $wpw_auto_poster_options[ 'ba_type_'.$post_type.'_user' ];
			} //end if
			
			//converter bufferapp user ids to array
			$ba_user_ids = ( array ) $ba_user_ids;
			
			//check bufferapp user ids are empty selected for posting
			if( empty( $ba_user_ids ) ) {
				
				//record logs for bufferapp users are not selected
				$this->logs->wpw_auto_poster_add( 'Bufferapp error: user not selected for posting.' );
				
				//return false
				return false;
				
			} //end if to check user ids are empty
			
			//post link from metabox
			$wpw_auto_poster_ba_custom_link	= get_post_meta( $post->ID, $prefix . 'ba_custom_post_link', true );
			
			//post link for posting to bufferapp user wall
			$postlink = isset( $wpw_auto_poster_ba_custom_link ) && !empty( $wpw_auto_poster_ba_custom_link ) 
							? $wpw_auto_poster_ba_custom_link : '';
			//if custom link is set or not
			$customlink = !empty( $postlink ) ? 'true' : 'false';
			//do url in shortner form when shortner format is not set wordpress
			$postlink 	= $this->model->wpw_auto_poster_get_short_post_link( $postlink, $unique, $post->ID, $customlink, 'ba' );
			
			//post image from meta
			$postimage = get_post_meta( $post->ID, $prefix . 'ba_post_image', true );
			
			/**************
			 * Image Priority
			 * If metabox image set then take from metabox
			 * If metabox image is not set then take from featured image
			 * If featured image is not set then take from settings page
			 **************/
			
			//get featured image from post / page / custom post type
			$post_featured_img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
			//check custom image is set in meta and not empty
			if( isset( $postimage['src'] ) && !empty( $postimage['src'] ) ) {
				$postimage = $postimage['src'];
			} elseif ( isset( $post_featured_img[0] ) && !empty( $post_featured_img[0] ) ) {
				//check post featrued image is set the use that image
				$postimage = $post_featured_img[0];
			} else {
				//else get post image from settings page
				$postimage = $wpw_auto_poster_options['ba_post_img'];
			}
			
			//post title
			$posttitle = $post->post_title;

			// get global buffer custom message
			$ba_global_message_template = ( isset( $wpw_auto_poster_options["ba_global_message_template"] ) )? $wpw_auto_poster_options["ba_global_message_template"] : '';

			//custom description from meta
			$ba_meta_message_template = get_post_meta( $post->ID, $prefix . 'ba_post_desc', true );
			
			if ( !empty( $ba_meta_message_template ) ){
				//custom description set at buffer post meta level
				$description = $ba_meta_message_template;

			} elseif( !empty( $ba_global_message_template )){
				//custom description set at buffer global settings
				$description = $ba_global_message_template;

			} else {
				//custom description not set at buffer global settings then take post content
				$description = strip_shortcodes( $post->post_content );
			}
			
			//strip html kses and tags
			$description = $this->model->wpw_auto_poster_stripslashes_deep( $description );
			//decode html
			$description = $this->model->wpw_auto_poster_html_decode( $description );

			$trim_content = $description;



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

			//replace the shortcodes in description
			$search_arr  = array( '{title}', '{link}', '{first_name}' , '{last_name}', '{sitename}', '{excerpt}', '{hashtags}', '{hashcats}', '{content}' );
			$replace_arr = array( $title, $postlink, $first_name, $last_name, get_option( 'blogname'), $excerpt, $hashtags, $hashcats, $description);
			
			$code_matches = array();

                
            // check if template tags contains {content-numbers}
			if( preg_match_all( '/\{(content)(-)(\d*)\}/', $description, $code_matches ) ) {

				$trim_tag = $code_matches[0][0];
                $trim_length = $code_matches[3][0];
				$trim_content = substr( $trim_content, 0, $trim_length);
				$search_arr[] = $trim_tag;
				$replace_arr[] = $trim_content;
			}


			$description = str_replace( $search_arr, $replace_arr, $description );

			// replace title tag support value
			$search_arr = array( '{title}', '{first_name}', '{last_name}', '{sitename}', '{site_name}', '{content}', '{excerpt}', '{hashtags}', '{hashcats}' );
			$replace_arr = array( $posttitle, $first_name, $last_name, get_option( 'blogname'), get_option( 'blogname'), strip_shortcodes( $post->post_content ), $excerpt, $hashtags, $hashcats );

			// check if template tags contains {content-numbers}
			if( preg_match_all( '/\{(content)(-)(\d*)\}/', $title, $code_matches ) ) {

				$trim_tag = $code_matches[0][0];
                $trim_length = $code_matches[3][0];
				$trim_content = substr( $trim_content, 0, $trim_length);
				$search_arr[] = $trim_tag;
				$replace_arr[] = $trim_content;
			}
			
			$title = str_replace( $search_arr, $replace_arr, $title );

			
			$params = array();
			
			$ba_accounts = isset( $_SESSION['wpw_auto_poster_ba_cache'] ) ? $_SESSION['wpw_auto_poster_ba_cache'] : array();
			
			// post title
			$params['text'] 				= $title;
			//Add profile IDs
			foreach ( $ba_user_ids as $user_id ) {
				$params['profile_ids'][] = $user_id;
			}//end foreach loop
			//add link
			$params['media']['link'] 		= $postlink;
			//check post image is not empty
			if( !empty( $postimage ) ) {
				//add image
				$params['media']['photo'] 	= $postimage;
			}
			//add description content
			$params['media']['description'] = $description;
			
			//posting logs data
			$posting_logs_data = array(	
											'title' 		=> $title,
											'link' 			=> $postlink,
											'image' 		=> $postimage,
											'description'	=> $description
										);
			
			//record logs for bufferapp data
			$this->logs->wpw_auto_poster_add( 'BufferApp post data : ' . var_export( $params, true ) );
			
			//Send to Buffer and store response
			$result = $this->wpw_auto_poster_bufferapp_request( $_SESSION['wpw_auto_poster_ba_access_token'], 'updates/create.json', 'post', apply_filters( 'wpw_auto_poster_ba_post_params', $params, $post ) );
			
			if( isset( $result->success ) && $result->success == 1 ){
				
				//record logs for post posting to bufferapp
				if( !empty( $ba_accounts ) ) {	
					
					foreach ( $ba_accounts as $key => $account ) {
						
						if( in_array( $account->id, $ba_user_ids ) ) {
							
							// Service Type
							$posting_logs_data['service'] = isset( $account->service ) ? $account->service : '';
							
							//User details
							$posting_logs_user_details = array(
																	'account_id' 				=> isset( $account->id ) ? $account->id : '',
																	'display_name'				=> isset( $account->formatted_username ) ? $account->formatted_username : '',
																	'user_name'					=> isset( $account->service_username ) ? $account->service_username : '',
																	'service_id'				=> isset( $account->service_id ) ? $account->service_id : '',
																	'service'					=> isset( $account->service ) ? $account->service : '',
																	'bufferapp_client_id' 		=> WPW_AUTO_POSTER_BA_CLIENT_ID,
																	'bufferapp_client_secret' 	=> WPW_AUTO_POSTER_BA_CLIENT_SECRET
																);
																		
							//posting logs store into database
							$this->model->wpw_auto_poster_insert_posting_log( $post->ID, 'ba', $posting_logs_data, $posting_logs_user_details );
							
							$this->logs->wpw_auto_poster_add( 'BufferApp posted to user account user ID : '. $account->id );
						}
					}
				}
			}else{
				//record logs for post posting failed on bufferapp
				$this->logs->wpw_auto_poster_add( 'BufferApp error: Posting to BufferApp failed | ' . $result );
				sap_add_notice( sprintf( __('BufferApp: Something was wrong %s', 'wpwautoposter' ), $result ), 'error');
			}
			//return result data
			return $result;
		} else {
			//record logs when grant extended permission not set
			$this->logs->wpw_auto_poster_add( 'BufferApp error: grant extended permissions not set.' );

			// display error notice on post page
			sap_add_notice( __('BufferApp: Please give Grant extended permission before posting to the BufferApp.', 'wpwautoposter' ), 'error');
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
	public function wpw_auto_poster_ba_reset_session() {
		
		global $wpw_auto_poster_options;
		
		update_option( 'wpw_auto_poster_ba_sess_data', '' );
		unset( $_SESSION['wpw_auto_poster_ba_user_id'] );
		unset( $_SESSION['wpw_auto_poster_ba_cache'] );
		unset( $_SESSION['wpw_auto_poster_ba_access_token'] );
		
		//check if bufferapp reset user link is clicked and fb_reset_user is set to 1
		if( isset( $_GET['ba_reset_user'] ) && $_GET['ba_reset_user'] == '1' ) {
			
			//get all post type
			$all_post_types = get_post_types( array( 'public' => true ), 'objects' );
			$all_post_types = is_array( $all_post_types ) ? $all_post_types : array();
			
			//unset users from settings page
			foreach ( $all_post_types as $posttype ) {
				
				//check postype is not object
				if( !is_object( $posttype ) ) continue;
				
				$label = @$posttype->labels->name ? $posttype->labels->name : $posttype->name;
				if( $label == 'Media' || $label == 'media' ) continue; // skip media
															
				//check if user is set for posting in settings page then unset it
				if( isset( $wpw_auto_poster_options[ 'ba_type_'.$posttype->name.'_user' ] ) ) {
					unset( $wpw_auto_poster_options[ 'ba_type_'.$posttype->name.'_user' ] );
				} //end if
				
			} //end foreach
			
			//update autoposter options to settings
			update_option( 'wpw_auto_poster_options', $wpw_auto_poster_options );
			
		} //end if
	}
	
	/**
	 * BufferApp Posting
	 * 
	 * Handles to bufferapp posting
	 * by post data
	 * 
	 * @package Social Auto Poster
 	 * @since 1.5.0
	 */
	public function wpw_auto_poster_ba_posting( $post ) {
		
		global $wpw_auto_poster_options;
		
		$prefix = WPW_AUTO_POSTER_META_PREFIX;
			
		$res = $this->wpw_auto_poster_post_to_bufferapp( $post );
		
		if( isset( $res->success ) && $res->success == '1' ) {
			
			//record logs for posting done on friendfeed
			$this->logs->wpw_auto_poster_add( 'BufferApp posting completed successfully.' );
				
			update_post_meta( $post->ID, $prefix.'ba_status', '1');

			// get current timestamp and update meta as published date/time
            $current_timestamp = current_time( 'timestamp' );
            update_post_meta($post->ID, $prefix . 'published_date', $current_timestamp);
            
			return true;
		}
		return false;
	}
}
?>