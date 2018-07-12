<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * LinkedIn Posting Class
 *
 * Handles all the functions to post the submitted and approved
 * reviews to a chosen application owner account
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
class Wpw_Auto_Poster_Li_Posting {
	
	public $linkedinconfig, $linkedin, $message, $model, $logs;
	
	public function __construct() {
	
		global $wpw_auto_poster_message_stack, $wpw_auto_poster_model, $wpw_auto_poster_logs;
		
		$this->message = $wpw_auto_poster_message_stack;
		$this->model = $wpw_auto_poster_model;
		$this->logs	 = $wpw_auto_poster_logs;
		
		//intialize some data
		$this->wpw_auto_poster_li_initialize();
		
		//add action init for making user to logged in linkedin
		add_action( 'init', array( $this, 'wpw_auto_poster_li_user_logged_in' ) );
		
	}
	
	/**
	 * LinekedIn Get Access Tocken
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function wpw_auto_poster_li_get_access_token( $app_id ) {
		
		//Get stored li app grant data
		$wpw_auto_poster_li_sess_data = get_option( 'wpw_auto_poster_li_sess_data' );

		$access_tocken	= '';
		
		if( isset( $wpw_auto_poster_li_sess_data ) && !empty( $wpw_auto_poster_li_sess_data ) && isset( $wpw_auto_poster_li_sess_data[$app_id]['wpw_auto_poster_li_oauth']['linkedin']['access'] ) ) {
			
			$li_access_data	= $wpw_auto_poster_li_sess_data[$app_id]['wpw_auto_poster_li_oauth']['linkedin']['access'];
			
			$access_tocken	= isset( $li_access_data['access_token'] ) ? $li_access_data['access_token'] : '';
		
		} elseif( isset( $_SESSION['wpw_auto_poster_linkedin_oauth']['linkedin']['access'] ) ) {
			
			$li_access_data	= $_SESSION['wpw_auto_poster_linkedin_oauth']['linkedin']['access'];
			
			$access_tocken	= isset( $li_access_data['access_token'] ) ? $li_access_data['access_token'] : '';
		}

		return $access_tocken;
	}
	
	/**
	 * Include LinkedIn Class
	 * 
	 * Handles to load Linkedin class
	 * 
	 * @package Social Auto Poster
 	 * @since 1.0.0
	 */
	public function wpw_auto_poster_load_linkedin($app_id = false) {
		
		global $wpw_auto_poster_options;
		
		// Getting linkedin apps
        $li_apps = wpw_auto_poster_get_li_apps();

        // If app id is not passed then take first li app data
        if (empty($app_id)) {
            $li_apps_keys = array_keys($li_apps);
            $app_id = reset($li_apps_keys);
        }

		//linkedin declaration
		if( !empty($app_id) && !empty($li_apps[$app_id]) ) {

			if( !class_exists( 'LinkedInOAuth2' ) ) {
				require_once( WPW_AUTO_POSTER_SOCIAL_DIR . '/linkedin/LinkedIn.OAuth2.class.php' );
			}
			
			$call_back_url	= site_url().'/?wpwautoposter=linkedin&wpw_li_app_id='.$app_id;
			
			//linkedin api configuration
			$this->linkedinconfig = array(
									    	'appKey'       => $app_id,
										  	'appSecret'    => $li_apps[$app_id],
										  	'callbackUrl'  => $call_back_url
									  	 );
			
			//Get access token
			$access_token	= $this->wpw_auto_poster_li_get_access_token( $app_id );
			
            //unset($_SESSION['wpw_auto_poster_linkedin_oauth']);
            //unset($_SESSION['wpw_auto_poster_li_oauth']);
            
			//Load linkedin outh2 class
			$this->linkedin = new LinkedInOAuth2( $access_token );
		
			return true;
			
		} else {
			
			return false;
		}
	}
	
	/**
	 * Make Logged In User to LinekedIn
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function wpw_auto_poster_li_user_logged_in() {
		
		global $wpw_auto_poster_options;

		$linkedin_keys = isset( $wpw_auto_poster_options['linkedin_keys'] ) ? $wpw_auto_poster_options['linkedin_keys'] : array();
	
		//check $_GET['wpwautoposter'] equals to linkedin
		if( isset( $_GET['wpwautoposter'] ) && $_GET['wpwautoposter'] == 'linkedin'
			&& !empty( $_GET['code'] ) && !empty( $_GET['state'] ) && isset( $_GET['wpw_li_app_id'] )) {
			
			//record logs for grant extended permission
			$this->logs->wpw_auto_poster_add( 'LinkedIn Grant Extended Permission', true );
			
			//record logs for get parameters set properly
			$this->logs->wpw_auto_poster_add( 'Get Parameters Set Properly.' );
			
			$li_app_id = $_GET['wpw_li_app_id'];

			$li_app_secret = '';

			foreach ( $linkedin_keys as $linkedin_key => $linkedin_value ) {

				if (in_array($li_app_id, $linkedin_value)){

					$li_app_secret = $linkedin_value['app_secret'];
				}

			}

			$callbackUrl = site_url().'/?wpwautoposter=linkedin&wpw_li_app_id='.$li_app_id;


			//load linkedin class
			$linkedin	= $this->wpw_auto_poster_load_linkedin( $li_app_id );

			//$config		= $this->linkedinconfig;

			
			//check linkedin loaded or not
			if( !$linkedin ) return false;
			
			//Get Access token
			$arr_access_token	= $this->linkedin->getAccessToken( $li_app_id, $li_app_secret, $callbackUrl);


			// code will excute when user does connect with linked in
			if( !empty( $arr_access_token['access_token'] ) ) { // if user allows access to linkedin
				
				//record logs for get type initiate called
				$this->logs->wpw_auto_poster_add( 'LinkedIn grant initiate called' );
				
				//record logs for get type response called
				$this->logs->wpw_auto_poster_add( 'LinkedIn permission granted by user' );
				
	        	//record logs for get type initiate called
				$this->logs->wpw_auto_poster_add( 'LinkedIn Request token retrieval success when clicked on allow access by user' );
	        	
				// the request went through without an error, gather user's 'access' tokens
				$_SESSION['wpw_auto_poster_linkedin_oauth']['linkedin']['access'] = $arr_access_token;
				
				// set the user as authorized for future quick reference
				$_SESSION['wpw_auto_poster_linkedin_oauth']['linkedin']['authorized'] = TRUE;
				
				//Get User Profiles
				$resultdata	= $this->linkedin->getProfile();

				//set user data to sesssion for further use
		        $_SESSION['wpw_auto_poster_li_cache'] = $resultdata;
	           	$_SESSION['wpw_auto_poster_li_user_id'] = isset( $resultdata['id'] ) ? $resultdata['id'] : '';
	           	
	           	//Get company data
	           	$company_data	= $this->wpw_auto_poster_get_company_data( $li_app_id );

	           	
	           	//update company data in session
	           	$_SESSION['wpw_auto_poster_li_companies'] = $company_data;
	           	
	           	//Get group data
	           	$group_data	= $this->wpw_auto_poster_get_group_data( $li_app_id );
	           	
	           	//Update group data in session
	           	$_SESSION['wpw_auto_poster_li_groups'] = $group_data;
	           	
				// redirect the user back to the demo page
				$this->message->add_session( 'poster-selected-tab', 'linkedin' );
				
				//set user data  to session
				$this->wpw_auto_poster_set_li_data_to_session( $li_app_id );
				
                // unset session data so there will be no probelm to grant extend another account
				unset($_SESSION['wpw_auto_poster_linkedin_oauth']);
            	unset($_SESSION['wpw_auto_poster_li_oauth']);
                
				//record logs for grant extend successfully
				$this->logs->wpw_auto_poster_add( 'Grant Extended Permission Successfully.' );
				
				$poster_setting_url = add_query_arg( array('page' => 'wpw-auto-poster-settings' ), admin_url() );
				
				wp_redirect( $poster_setting_url );
				exit;
				
		  	} else {
		  		
				//record logs for access token retrieval
				$this->logs->wpw_auto_poster_add( 'LinkedIn error: Access token retrieval failed' );
	        }
			
		} //end if to check $_GET['wpwautoposter'] equals to linkedin
				
	}
	
	/**
	 * Initializes Some Data to session
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function wpw_auto_poster_li_initialize() {
		
		global $wpw_auto_poster_options;
		
		//check user data is not empty and linkedin app id and secret are not empty
		if( !empty( $wpw_auto_poster_options['linkedin_app_id'] ) && !empty( $wpw_auto_poster_options['linkedin_app_secret'] ) ) {
			
			//Set Session From Options Value
			$wpw_auto_poster_li_sess_data	= get_option( 'wpw_auto_poster_li_sess_data' );
			
			if( !empty( $wpw_auto_poster_li_sess_data ) && !isset( $_SESSION['wpw_auto_poster_li_user_id'] ) ) { //check user data is not empty
				
				$_SESSION['wpw_auto_poster_li_user_id']		= $wpw_auto_poster_li_sess_data['wpw_auto_poster_li_user_id'];
				$_SESSION['wpw_auto_poster_li_cache']		= $wpw_auto_poster_li_sess_data['wpw_auto_poster_li_cache'];
				$_SESSION['wpw_auto_poster_li_oauth']		= $wpw_auto_poster_li_sess_data['wpw_auto_poster_li_oauth'];
				$_SESSION['wpw_auto_poster_linkedin_oauth']	= $wpw_auto_poster_li_sess_data['wpw_auto_poster_li_oauth']; //assign stored oauth token to database
				$_SESSION['wpw_auto_poster_li_companies']	= $wpw_auto_poster_li_sess_data['wpw_auto_poster_li_companies']; //assign stored companies to database
				$_SESSION['wpw_auto_poster_li_groups']		= $wpw_auto_poster_li_sess_data['wpw_auto_poster_li_groups']; //assign stored groups to database
			}
		}
	}
	
	/**
	 * Get LinkedIn Login URL
	 * 
	 * Handles to Return LinkedIn URL
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function wpw_auto_poster_get_li_login_url($app_id = false) {
		
		//$scope	= array( 'r_emailaddress', 'r_basicprofile', 'rw_nus', 'r_network', 'r_contactinfo', 'rw_company_admin', 'rw_groups', 'r_fullprofile', 'w_messages' );
		$scope	= array( 'r_emailaddress', 'r_basicprofile', 'rw_company_admin', 'w_share' );
		
		//load linkedin class
		$linkedin = $this->wpw_auto_poster_load_linkedin( $app_id );
		
		//check linkedin loaded or not
		if( !$linkedin ) return false;
		
		//Get Linkedin config
		//$config	= $this->linkedinconfig;

		$callbackUrl = site_url().'/?wpwautoposter=linkedin&wpw_li_app_id='.$app_id;
		
		try {//Prepare login URL
			$preparedurl	= $this->linkedin->getAuthorizeUrl($app_id, $callbackUrl, $scope );
		} catch( Exception $e ) {
			$preparedurl	= '';
        }
        
		return $preparedurl;
	}
	
	
	/**
	 * Post To LinkedIn
	 * 
	 * Handles to Posting to Linkedin User Wall,
	 * Company Page / Group Posting
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function wpw_auto_poster_post_to_linkedin( $post ) {
		
		global $wpw_auto_poster_options;
		
		// Get stored li app grant data
        $wpw_auto_poster_li_sess_data = get_option('wpw_auto_poster_li_sess_data');

		//meta prefix
		$prefix			= WPW_AUTO_POSTER_META_PREFIX;

		$post_type = $post->post_type; // Post type
		
		//Initilize linkedin posting
		$li_posting		= array();

		//Initialize tags and categories
		$tags_arr = array();
        $cats_arr = array();
		
		//load linkedin class
		//$linkedin		= $this->wpw_auto_poster_load_linkedin();

		//check linkedin loaded or not
		//if( !$linkedin ) return false;

		$li_global_template_text = ( !empty( $wpw_auto_poster_options['li_global_message_template'] ) ) ? $wpw_auto_poster_options['li_global_message_template'] : '';

		// Getting all linkedin apps
        $li_apps = wpw_auto_poster_get_li_apps();
		
		//check linkedin authorized session is true or not
		//need to do for linkedin posting code
		if( !empty( $wpw_auto_poster_li_sess_data ) ) {
			
			//posting logs data
			$posting_logs_data	= array();
			
			//record logs for linkedin posting
			//$this->logs->wpw_auto_poster_add( 'LinkedIn posting to '.$posting_type.' account begins.' );
			
			$unique	= 'false';
			
			//user data
			$userdata	= get_userdata( $post->post_author );
			$first_name	= $userdata->first_name; //user first name
			$last_name	= $userdata->last_name; //user last name
			
			//published status
			$ispublished	= get_post_meta( $post->ID, $prefix . 'li_status', true );


			// Get all selected tags for selected post type for hashtags support
            if(isset($wpw_auto_poster_options['li_post_type_tags']) && !empty($wpw_auto_poster_options['li_post_type_tags'])) {

                $custom_post_tags = $wpw_auto_poster_options['li_post_type_tags'];
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
            if(isset($wpw_auto_poster_options['li_post_type_cats']) && !empty($wpw_auto_poster_options['li_post_type_cats'])) {

                $custom_post_cats = $wpw_auto_poster_options['li_post_type_cats'];
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
			$posttitle		= $post->post_title;
			
			//custom title from metabox
			$customtitle	= get_post_meta( $post->ID, $prefix . 'li_post_title', true );

			$customtitle 	= ( empty( $customtitle ) ) ? $li_global_template_text : $customtitle;

			//custom title set use it otherwise user posttiel
			$title			= !empty( $customtitle ) ? $customtitle : $posttitle;


			
			//post image
			$postimage		= get_post_meta( $post->ID, $prefix . 'li_post_image', true );
			

			// Post Content
			$post_content	= $this->model->wpw_auto_poster_stripslashes_deep( strip_shortcodes( $post->post_content ) );
			
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
				$postimage = $wpw_auto_poster_options['li_post_image'];
			}
			
			//post link
			$postlink = get_post_meta( $post->ID, $prefix . 'li_post_link', true );
			$postlink = isset( $postlink ) && !empty( $postlink ) ? $postlink : '';
			//if custom link is set or not
			$customlink = !empty( $postlink ) ? 'true' : 'false';
			
			//do url shortner
			$postlink = $this->model->wpw_auto_poster_get_short_post_link( $postlink, $unique, $post->ID, $customlink, 'li' );
			
			// not sure why this code here it should be above $postlink but lets keep it here
			//if post is published on linkedin once then change url to prevent duplication
			if( isset( $ispublished ) && $ispublished == '1' ) {
				$unique = 'true';
			}
			
			//comments
			$comments = get_post_meta( $post->ID, $prefix . 'li_post_comment', true );
			$comments = !empty( $comments ) ? $comments : '';
			$comments = apply_filters( 'wpw_auto_poster_li_comments', $comments, $post );

			//get linkedin posting description
			$description 		= strip_shortcodes( $post->post_content );

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

			//use 400 character to post to linkedin
			$description 		= $this->model->wpw_auto_poster_excerpt( $description, 400 );
			$description 		= $this->model->wpw_auto_poster_stripslashes_deep( $description );
			
			$search_arr 		= array( '{title}', '{link}', '{first_name}' , '{last_name}', '{sitename}', '{site_name}', '{content}', '{excerpt}', '{hashtags}', '{hashcats}' );
			$replace_arr 		= array( $posttitle , $postlink, $first_name, $last_name, get_option( 'blogname'), get_option( 'blogname' ), $post_content, $excerpt, $hashtags, $hashcats );

			$code_matches = array();
    
            // check if template tags contains {content-numbers}
            if( preg_match_all( '/\{(content)(-)(\d*)\}/', $comments, $code_matches ) ) {
                $trim_tag = $code_matches[0][0];
                $trim_length = $code_matches[3][0];
                $post_content = substr( $post_content, 0, $trim_length);
                $search_arr[] = $trim_tag;
                $replace_arr[] = $post_content;
            }
			
			$comments 			= str_replace( $search_arr, $replace_arr, $comments ); 				
	
	
			// replace title with tag support value					
			$search_arr 		= array( '{title}', '{link}', '{first_name}' , '{last_name}', '{sitename}', '{site_name}', '{content}', '{excerpt}', '{hashtags}', '{hashcats}' );
			$replace_arr 		= array( $posttitle, $postlink, $first_name, $last_name, get_option( 'blogname'), get_option( 'blogname' ), $post_content, $excerpt, $hashtags, $hashcats );

			// check if template tags contains {content-numbers}
            if( preg_match_all( '/\{(content)(-)(\d*)\}/', $title, $code_matches ) ) {
                $trim_tag = $code_matches[0][0];
                $trim_length = $code_matches[3][0];
                $post_content = substr( $post_content, 0, $trim_length);
                $search_arr[] = $trim_tag;
                $replace_arr[] = $post_content;
            }
            
			// replace title with tag support value
			$title 				= str_replace( $search_arr, $replace_arr, $title );

			//Get title
			$title 				= $this->model->wpw_auto_poster_html_decode( $title );

			//Get comment
			$comments 			= $this->model->wpw_auto_poster_html_decode( $comments );
			$comments			= $this->model->wpw_auto_poster_excerpt( $comments, 700 );
			
			//Linkedin Profile Data from setting //_wpweb_li_post_profile
			$li_post_profiles 	= get_post_meta( $post->ID, $prefix . 'li_post_profile' );

			/******* Code to posting to selected category Linkdin account ******/
			//$categories = get_the_category( $post->ID, array()); // get post categories

			// get all categories for custom post type
			$categories = wpw_auto_poster_get_post_categories_by_ID( $post_type, $post->ID );
			
			// Get all selected account list from category
			$category_selected_social_acct = get_option( 'wpw_auto_poster_category_posting_acct');
			
			// IF category selected and category social account data found
			if( !empty( $categories ) && !empty( $category_selected_social_acct ) && empty( $li_post_profiles ) ) {
				$li_clear_cnt = true;

				// GET Linkdin user account ids from post selected categories
				foreach ( $categories as $key => $term_id ) {
					
					$cat_id = $term_id;
					// Get TW user account ids form selected category  
					if( isset( $category_selected_social_acct[$cat_id]['li'] ) && !empty( $category_selected_social_acct[$cat_id]['li'] ) ) {
						// clear TW user data once
						if( $li_clear_cnt)
							$li_post_profiles = array();
						$li_post_profiles = array_merge($li_post_profiles, $category_selected_social_acct[$cat_id]['li'] );
						$li_clear_cnt = false;
					}
				}
				if( !empty( $li_post_profiles ) ) {
					$li_post_profiles = array_unique($li_post_profiles);
				}
			}

		
			if( empty( $li_post_profiles ) ) {//If profiles are empty in metabox
				
				$li_post_profiles	= isset( $wpw_auto_poster_options['li_type_'.$post->post_type.'_profile'] ) ? $wpw_auto_poster_options['li_type_'.$post->post_type.'_profile'] : '';
			}
			
			$content = array( 
								'title' 				=> $title,
								'submitted-url'			=> $postlink,
								'comment'				=> $comments,
								'submitted-image-url'	=> $postimage,
								'description'			=> $description
							);

			//posting logs data
			$posting_logs_data = array(	
											'title' 		=> $title,
											'comment' 		=> $comments,
											'link' 			=> $postlink,
											'image' 		=> $postimage,
											'description'	=> $description
										);
			
			//Get all Profiles
			$profile_datas	= $this->wpw_auto_poster_get_profiles_data();
			
			//record logs for linkedin data
			$this->logs->wpw_auto_poster_add( 'LinkedIn post data : ' . var_export( $content, true ) );
			
			//get user profile data
			$user_profile_data	= $this->wpw_auto_poster_get_li_user_data();			
			
			//Initilize all user/company/group data
			$company_data = $group_data = $userwall_data = $display_name_data = $display_id_data = array();
			
			//initial value of posting flag
			$postflg = false;
			
			//echo "<pre>";
			//print_r($li_post_profiles);exit;
			try {
				if( !empty( $li_post_profiles ) ) {

					// Get linkedin account details
                	//$li_accounts = wpw_auto_poster_get_li_accounts();
				
					foreach ( $li_post_profiles as $li_post_profile ) {
						
						//Initilize log user details
						$posting_logs_user_details	= array();
						
						$split_profile	= explode( ':|:', $li_post_profile );
						
						$profile_type	= isset( $split_profile[0] ) ? $split_profile[0] : '';
						$profile_id		= isset( $split_profile[1] ) ? $split_profile[1] : '';
						$li_post_app_id = isset($split_profile[2]) ? $split_profile[2] : ''; // Linkedin App Id
						$li_post_app_sec = isset($li_apps[$li_post_app_id]) ? $li_apps[$li_post_app_id] : ''; // Linkedin App Sec
						
						// Load linkedin class
                    	$linkedin = $this->wpw_auto_poster_load_linkedin( $li_post_app_id );

                    	// Check linkedin class is exis or not
	                    if (!$linkedin) {
	                        $this->logs->wpw_auto_poster_add('Linkedin error: Linkedin is not initialized with ' . $li_post_app_id . ' App.'); // Record logs for linkedin not initialized
	                        continue;
	                    }

	                     // Getting stored linkedin app data
                    	$li_stored_app_data = isset($wpw_auto_poster_li_sess_data[$li_post_app_id]) ? $wpw_auto_poster_li_sess_data[$li_post_app_id] : array();
                    	
                    	// Get user cache data
                    	$user_cache_data = isset($li_stored_app_data['wpw_auto_poster_li_cache']) ? $li_stored_app_data['wpw_auto_poster_li_cache'] : array();

						//Linkedin Log user details
						$posting_logs_user_details['account_id'] 			= $profile_id;
						$posting_logs_user_details['linkedin_app_id']		= $li_post_app_id;
						$posting_logs_user_details['linkedin_app_secret']	= $li_post_app_sec;
						
						if( $profile_type == 'user' && $user_cache_data['id'] == $profile_id ) { // Check facebook main user data
							
							$user_first_name= isset( $user_cache_data['firstName'] ) ? $user_cache_data['firstName'] : '';
							$user_last_name = isset( $user_cache_data['lastName'] ) ? $user_cache_data['lastName'] : '';
							$user_email		= isset( $user_cache_data['email-address'] ) ? $user_cache_data['email-address'] : '';
							$profile_url 	= isset( $user_cache_data['publicProfileUrl'] ) ? $user_cache_data['publicProfileUrl'] : '';
							$display_name	= $user_first_name . ' ' . $user_last_name;
							
							$posting_logs_user_details['display_name']	= $display_name;
							$posting_logs_user_details['first_name']	= $user_first_name;
							$posting_logs_user_details['last_name']		= $user_last_name;
							$posting_logs_user_details['user_name']		= $user_first_name;
							$posting_logs_user_details['user_email']	= $user_email;
							$posting_logs_user_details['profile_url']	= $profile_url;
							
						} else {
							
							//Account Name
							$posting_logs_user_details['display_name'] = isset( $profile_datas[$li_post_profile] ) ? $profile_datas[$li_post_profile] : '';
						}
						
						switch ( $profile_type ) {
							
							case 'user':
								
								if( !empty( $profile_id ) ) {

									//Filter content
									$content 	= apply_filters( 'wpw_auto_poster_li_content', $content, $post, $profile_type );
									$response	= $this->linkedin->shareStatus( $content );
								}

								//record logs for linkedin users are not selected
								$this->logs->wpw_auto_poster_add( 'Linkedin posted to User ID : ' . $profile_id  . '' );
								
								//echo "<pre>";
								//print_r($response);exit;
								if( !empty( $response['updateKey'] ) ) {
									$postflg	= true;
								}
								
							break;
							
							case 'group':

								//Filter content and title
								$title 		= apply_filters( 'wpw_auto_poster_li_title', $title, $post, $profile_type );
								$content 	= apply_filters( 'wpw_auto_poster_li_content', $content, $post, $profile_type );

								$response	= $this->linkedin->postToGroup( $profile_id, $title, $description, $content );
								
								//record logs for linkedin users are not selected
								$this->logs->wpw_auto_poster_add( 'Linkedin posted to Group ID : ' . $profile_id  . '' );
								
								$postflg	= true;
								
								/*if( !empty( $response['updateKey'] ) ) {
									$li_posting['success'] = 1;
								} else {
									$li_posting['fail'] = 1;
								}*/
								
							break;
							
							case 'company':

								//Filter content and title
								$title 		= apply_filters( 'wpw_auto_poster_li_title', $title, $post, $profile_type );
								$content 	= apply_filters( 'wpw_auto_poster_li_content', $content, $post, $profile_type );

								$response	= $this->linkedin->postToCompany( $profile_id, $title, $content );
								
								//record logs for linkedin group are not selected
								$this->logs->wpw_auto_poster_add( 'Linkedin posted to Company ID : ' . $profile_id  . '' );
								
								if( !empty( $response['updateKey'] ) ) {
									$postflg	= true;
								}
								
							break;
						}
						
						if( $postflg ) {
							
							//posting logs store into database
							$this->model->wpw_auto_poster_insert_posting_log( $post->ID, 'li', $posting_logs_data, $posting_logs_user_details );
							
							$li_posting['success'] = 1;
							
						} else {
							
							$li_posting['fail'] = 1;
						}
						
					}
				}
			} catch ( Exception $e ) {
				
				//record logs exception generated
				$this->logs->wpw_auto_poster_add( 'LinkedIn error: ' . $e->__toString() );
				// display error notice on post page
				sap_add_notice( sprintf( __('LinkedIn: Something was wrong while posting %s', 'wpwautoposter' ), $e->__toString() ), 'error');
				return false;
			}
			
		} else {
			
			//record logs when grant extended permission not set
			$this->logs->wpw_auto_poster_add( 'LinkedIn error: Grant extended permissions not set.' );
			// display error notice on post page
			sap_add_notice( __('LinkedIn: Please give grant extended permission before posting to the LinkedIn.', 'wpwautoposter' ), 'error');
		}
		
		return $li_posting;
	}
	
	/**
	 * Get LinkedIn Profiles
	 * 
	 * Function to get LinkedIn profiles
	 * UserWall/Company/Groups
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function wpw_auto_poster_get_profiles_data() {
		
		$profiles	= array();
		
		//Get Users Data
		//$users		= $this->wpw_auto_poster_get_li_user_data();
		$users		= $this->wpw_auto_poster_get_li_users();
		
		//Get Company Data
		//$companies	= $this->wpw_auto_poster_get_company_data();
		$companies	= $this->wpw_auto_poster_get_li_companies();

		//Get Groups Data
		//$groups		= $this->wpw_auto_poster_get_group_data();
		$groups		= $this->wpw_auto_poster_get_li_groups();
		
		if( !empty( $users ) ) {//If User Data is not empty
			
			foreach ( $users as $app_id => $user_value) {
				$user_id	= isset( $user_value['id'] ) ? $user_value['id'] : '';
				$first_name	= isset( $user_value['firstName'] ) ? $user_value['firstName'] : '';
				$last_name	= isset( $user_value['lastName'] ) ? $user_value['lastName'] : '';
			
				if( !empty( $user_id ) ) {
					$profiles[ 'user:|:'. $user_id .':|:'.$app_id ]	= $first_name.' '.$last_name.' '.'( '. $user_id .' )';
				}
			}
		}
		
		if( !empty( $companies ) ) {//If Company Data is not empty
			
			foreach ( $companies as $app_id => $company_details ) {
				
				foreach ($company_details as $company_id => $company_name) {
					$profiles[ 'company:|:'. $company_id .':|:'.$app_id ]	= $company_name;
				}
			}
		}
		
		if( !empty( $groups ) ) {//If Group Data is not empty
			
			foreach ( $groups as $app_id => $group_details ) {
				
				foreach ($group_details as $group_id => $group_name) {
					$profiles[ 'group:|:'. $group_id .':|:'.$app_id ]	= $group_name;
				}
				
			}
		}
		
		return $profiles;
	}
	
	/**
	 * Get LinkedIn User Data
	 *
	 * Function to get LinkedIn User Data
	 *
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function wpw_auto_poster_get_li_user_data() {
		
		$wpw_auto_poster_li_sess_data = get_option( 'wpw_auto_poster_li_sess_data' );

		$user_profile_data = array();

		if ( isset( $_SESSION['wpw_auto_poster_li_cache'] ) && !empty( $_SESSION['wpw_auto_poster_li_cache'] ) ) {
		
			$user_profile_data = $_SESSION['wpw_auto_poster_li_cache'];
		}
		
		return $user_profile_data;
	}
	
	/**
	 * Set Session Data of linkedin to session
	 * 
	 * Handles to set user data to session
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function wpw_auto_poster_set_li_data_to_session($li_app_id = false) {
		
		//fetch user data who is grant the premission
		$liuserdata = $this->wpw_auto_poster_get_li_user_data();
		
		if( isset( $liuserdata['id'] ) && !empty( $liuserdata['id'] ) ) {
			
			//record logs for user id
			$this->logs->wpw_auto_poster_add( 'LinkedIn User ID : '.$liuserdata['id'] );
			
			try {
		        
		        $_SESSION['wpw_auto_poster_li_user_id'] = isset( $_SESSION['wpw_auto_poster_li_user_id'] )
					? $_SESSION['wpw_auto_poster_li_user_id'] : $liuserdata['id'];

				$_SESSION['wpw_auto_poster_li_cache']	= isset( $_SESSION['wpw_auto_poster_li_cache'] ) 
					? $_SESSION['wpw_auto_poster_li_cache'] : $liuserdata;
					
				$_SESSION['wpw_auto_poster_li_oauth'] = isset( $_SESSION['wpw_auto_poster_li_oauth'] ) 
					? $_SESSION['wpw_auto_poster_li_oauth'] : $_SESSION['wpw_auto_poster_linkedin_oauth'];
				
				$_SESSION['wpw_auto_poster_li_companies'] = isset( $_SESSION['wpw_auto_poster_li_companies'] ) 
					? $_SESSION['wpw_auto_poster_li_companies'] : '';
				
				$_SESSION['wpw_auto_poster_li_groups'] = isset( $_SESSION['wpw_auto_poster_li_groups'] ) 
					? $_SESSION['wpw_auto_poster_li_groups'] : '';
				
				// start code to manage session from database 			
				$wpw_auto_poster_li_sess_data = get_option( 'wpw_auto_poster_li_sess_data' );
			
				if( !isset( $wpw_auto_poster_li_sess_data[$li_app_id] ) ) {				
					
					$sess_data = array(
											'wpw_auto_poster_li_user_id'	=> $_SESSION['wpw_auto_poster_li_user_id'],
											'wpw_auto_poster_li_cache'		=> $liuserdata,
											'wpw_auto_poster_li_oauth'		=> $_SESSION['wpw_auto_poster_linkedin_oauth'],
											'wpw_auto_poster_li_companies'	=> $_SESSION['wpw_auto_poster_li_companies'],
											'wpw_auto_poster_li_groups'		=> $_SESSION['wpw_auto_poster_li_groups']
										);
					
					if ( $li_app_id ) {
			      	
			      		// Save Multiple Accounts
                        $wpw_auto_poster_li_sess_data[$li_app_id] = $sess_data;

			      		update_option( 'wpw_auto_poster_li_sess_data', $wpw_auto_poster_li_sess_data );

			      	}

			      	//record logs for session data updated to options
					$this->logs->wpw_auto_poster_add( 'Session Data Updated to Options' );
				}
			} catch( Exception $e ) {

		 	  	$liuserdata = null;
			}
		}
	}
	
	/**
	 * Reset Sessions
	 *
	 * Resetting the Linkedin sessions when the admin clicks on
	 * its link within the settings page.
	 *
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function wpw_auto_poster_li_reset_session() {
		
		//update_option( 'wpw_auto_poster_li_sess_data', '' );

		// Check if linkedin reset user link is clicked and li_reset_user is set to 1 and linkedin app id is there
        if (isset($_GET['li_reset_user']) && $_GET['li_reset_user'] == '1' && !empty($_GET['wpw_li_app'])) {

        	$wpw_li_app_id = $_GET['wpw_li_app'];

            // Getting stored li app data
            $wpw_auto_poster_li_sess_data = get_option('wpw_auto_poster_li_sess_data');

            // Unset particular app value data and update the option
            if (isset($wpw_auto_poster_li_sess_data[$wpw_li_app_id])) {
                unset($wpw_auto_poster_li_sess_data[$wpw_li_app_id]);
                update_option('wpw_auto_poster_li_sess_data', $wpw_auto_poster_li_sess_data);
            }

        }

		/******* Code for selected category Linkdin account ******/

		// unset selected Linkdin account option for category 
		$cat_selected_social_acc 	= array();
		$cat_selected_acc 		= get_option( 'wpw_auto_poster_category_posting_acct');
		$cat_selected_social_acc 	= ( !empty( $cat_selected_acc) ) ? $cat_selected_acc : $cat_selected_social_acc;

		 if( !empty( $cat_selected_social_acc ) ) {
		 	foreach ( $cat_selected_social_acc as $cat_id => $cat_social_acc ) {
		 		if( isset( $cat_social_acc['li'] ) ) {
					unset( $cat_selected_acc[ $cat_id ]['li'] );
		 		}
		 	}

			// Update autoposter category FB posting account options
			update_option( 'wpw_auto_poster_category_posting_acct', $cat_selected_acc ); 	
		 }
		
		if( isset( $_SESSION['wpw_auto_poster_li_user_id'] ) ) {//destroy userId session
			unset( $_SESSION['wpw_auto_poster_li_user_id'] );
		}
		if( isset( $_SESSION['wpw_auto_poster_li_cache'] ) ) {//destroy cache
			unset( $_SESSION['wpw_auto_poster_li_cache'] );
		}
		if( isset( $_SESSION['wpw_auto_poster_li_oauth'] ) ) {//destroy oauth
			unset( $_SESSION['wpw_auto_poster_li_oauth'] );
		}
		if( isset( $_SESSION['wpw_auto_poster_li_companies'] ) ) {//destroy company session
			unset( $_SESSION['wpw_auto_poster_li_companies'] );
		}
		if( isset( $_SESSION['wpw_auto_poster_li_groups'] ) ) {//destroy group session
			unset( $_SESSION['wpw_auto_poster_li_groups'] );
		}
		if( isset( $_SESSION['wpw_auto_poster_linkedin_oauth'] ) ) {//destroy linkedin session
			unset( $_SESSION['wpw_auto_poster_linkedin_oauth'] );
		}
	}
	
	/**
	 * LinkedIn Posting
	 * 
	 * Handles to linkedin posting
	 * by post data
	 * 
	 * @package Social Auto Poster
 	 * @since 1.5.0
	 */
	public function wpw_auto_poster_li_posting( $post ) {
		
		global $wpw_auto_poster_options;
		
		$prefix = WPW_AUTO_POSTER_META_PREFIX;

		
		$res = $this->wpw_auto_poster_post_to_linkedin( $post );
		
		if( isset( $res['success'] ) && !empty( $res['success'] ) ) { //check if error should not occured and successfully tweeted
			
			//record logs for posting done on linkedin
			$this->logs->wpw_auto_poster_add( 'LinkedIn posting completed successfully.' );
			
			update_post_meta( $post->ID, $prefix . 'li_status', '1' );

			// get current timestamp and update meta as published date/time
            $current_timestamp = current_time( 'timestamp' );
            update_post_meta($post->ID, $prefix . 'published_date', $current_timestamp);
            
			return true;
		}
		
		return false;
	}
	
	/** 
	 * Linkedin Get Company Data
	 * 
	 * @package Social Auto Poster
	 * @since 1.5.0
	 */
	public function wpw_auto_poster_get_company_data( $app_id ) {
		
		//Initilize company array
		$company_data	= array();

		// Get stored li app grant data
		$wpw_auto_poster_li_sess_data = get_option( 'wpw_auto_poster_li_sess_data' );
		
		/*if( isset($_SESSION['wpw_auto_poster_li_companies'] ) ) {
			
			$company_data	= $_SESSION['wpw_auto_poster_li_companies'];
		}*/

		if( isset($wpw_auto_poster_li_sess_data[$app_id]['wpw_auto_poster_li_companies'] ) ) {
			
			$company_data	= $wpw_auto_poster_li_sess_data[$app_id]['wpw_auto_poster_li_companies'];
		
		} else {
			
			//Load linkedin class
			$this->wpw_auto_poster_load_linkedin( $app_id );
			
			if( !empty( $this->linkedin ) ) { //If linkedin object is found
				
				//Get companies data
				$results	= $this->linkedin->getAdminCompanies();
				
				//Companies data
				$companies	= isset( $results['values'] ) ? $results['values'] : array();
				
				if( !empty( $companies ) ) {//If company data is not empty
					foreach ( $companies as $company ) {
						
						//if( !empty( $company['company'] ) ) {
							//Get company Id
							$company_array_id	= isset( $company['id'] ) ? $company['id'] : '';
							//Get company name
							$company_array_name	= isset( $company['name'] ) ? $company['name'] : '';
							
							//If company Id not found
							if( !empty( $company_array_id ) ) {
								$company_data[$company_array_id]	= $company_array_name;
							}
						//}
					}
				}
			}
		}
		
		return $company_data;
	}
		
	/** 
	 * Linkedin Get Group Data
	 * 
	 * @package Social Auto Poster
	 * @since 1.5.0
	 */
	public function wpw_auto_poster_get_group_data( $app_id  ) { 
		
		//Initilize group array
		$group_data	= array();

		//Get stored li app grant data
		$wpw_auto_poster_li_sess_data = get_option( 'wpw_auto_poster_li_sess_data' );
		
		/*if( isset($_SESSION['wpw_auto_poster_li_groups'] ) ) {
			
			$group_data	= $_SESSION['wpw_auto_poster_li_groups'];
			
		}*/

		if( isset($wpw_auto_poster_li_sess_data[$app_id]['wpw_auto_poster_li_groups'] ) ) {
			
			$group_data	= $wpw_auto_poster_li_sess_data[$app_id]['wpw_auto_poster_li_groups'];
		
		} else {
			
			//Load linkedin class
			$this->wpw_auto_poster_load_linkedin( $app_id );
			
			if( !empty( $this->linkedin ) ) { //If linkedin object is found
				
				//Get groups data
				$results	= $this->linkedin->getGroups();
				
				$groups		= isset( $results['values'] ) ? $results['values'] : array();
				
				if( !empty( $groups ) ) {//If groups is not empty
					
					foreach ( $groups as $group ) {
						
						//Get code is owner/member
						$membershipState = isset( $group['membershipState']['code'] ) ? $group['membershipState']['code'] : '';
						
						if( $membershipState == 'owner' ) {//If group owner
							//Get group Id
							$group_id	= isset( $group['_key'] ) ? $group['_key'] : '';
							//Get group name
							$group_name	= isset( $group['group']['name'] ) ? $group['group']['name'] : '';
							
							if( !empty( $group_id ) ) {//Group id is not empty
								$group_data[$group_id]	= $group_name;
							}
						}
					}
				}
			}
		}
		
		return $group_data;
	}

	/** 
	 * Linkedin Get All User Data
	 * 
	 * @package Social Auto Poster
	 * @since 1.5.0
	 */
	public function wpw_auto_poster_get_li_users() {
		
		$wpw_auto_poster_li_sess_data = get_option( 'wpw_auto_poster_li_sess_data' );

		//Initilize users array
		$user_profile_data = array();

		if ( isset ( $wpw_auto_poster_li_sess_data ) && !empty( $wpw_auto_poster_li_sess_data ) ) {
			foreach ( $wpw_auto_poster_li_sess_data as $sess_key => $sess_data ){

				if ( isset( $sess_data['wpw_auto_poster_li_cache'] ) && !empty( $sess_data['wpw_auto_poster_li_cache'] ) ) {
			
					$user_profile_data[$sess_key] = $sess_data['wpw_auto_poster_li_cache'];
				}
			}
		}
		return $user_profile_data;
	}

	/** 
	 * Linkedin Get All Company Data
	 * 
	 * @package Social Auto Poster
	 * @since 1.5.0
	 */
	public function wpw_auto_poster_get_li_companies() {
		
		$wpw_auto_poster_li_sess_data = get_option( 'wpw_auto_poster_li_sess_data' );

		//Initilize company array
		$company_data	= array();
		
		if ( isset( $wpw_auto_poster_li_sess_data ) && !empty( $wpw_auto_poster_li_sess_data ) ) {
			foreach ( $wpw_auto_poster_li_sess_data as $sess_key => $sess_data ){

				if ( isset( $sess_data['wpw_auto_poster_li_companies'] ) && !empty( $sess_data['wpw_auto_poster_li_companies'] ) ) {
			
					$company_data[$sess_key] = $sess_data['wpw_auto_poster_li_companies'];
				}
			}
		}

		return $company_data;
	}

	/** 
	 * Linkedin Get All Group Data
	 * 
	 * @package Social Auto Poster
	 * @since 1.5.0
	 */
	public function wpw_auto_poster_get_li_groups() { 
		
		$wpw_auto_poster_li_sess_data = get_option( 'wpw_auto_poster_li_sess_data' );

		//Initilize group array
		$group_data	= array();
		
		if ( isset( $wpw_auto_poster_li_sess_data ) && !empty( $wpw_auto_poster_li_sess_data ) ) {
			foreach ( $wpw_auto_poster_li_sess_data as $sess_key => $sess_data ){

				if ( isset( $sess_data['wpw_auto_poster_li_groups'] ) && !empty( $sess_data['wpw_auto_poster_li_groups'] ) ) {
			
					$group_data[$sess_key] = $sess_data['wpw_auto_poster_li_groups'];
				}
			}
		}

		return $group_data;
	}
}