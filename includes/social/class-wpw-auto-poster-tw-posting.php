<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Twitter Posting Class
 *
 * Handles all the functions to tweet on twitter
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
class Wpw_Auto_Poster_TW_Posting {

	public $twitter, $model, $logs;
	
	public function __construct() {
	
		global $wpw_auto_poster_model, $wpw_auto_poster_logs;
		
		$this->model = $wpw_auto_poster_model;
		$this->logs	 = $wpw_auto_poster_logs;
	}
	
	/**
	 * Include Twitter Class
	 * 
	 * Handles to load twitter class
	 * 
	 * @package Social Auto Poster
 	 * @since 1.0.0
	 */
	public function wpw_auto_poster_load_twitter(  $twitter_consumer_key, $twitter_consumer_secret, $twitter_oauth_token, $twitter_oauth_secret ) {
		
		global $wpw_auto_poster_options;
		
		//check twitter application id and application secret is not empty or not
		if( !empty( $twitter_consumer_key ) && !empty( $twitter_consumer_secret )
			&& !empty( $twitter_oauth_token ) && !empty( $twitter_oauth_secret ) ) {
			
				if( !class_exists( 'Codebird' ) ) {
					require_once( WPW_AUTO_POSTER_SOCIAL_DIR . '/twitter/codebird.php' );
				}
				
				// Twitter Object
				\Codebird\Codebird::setConsumerKey($twitter_consumer_key, $twitter_consumer_secret);
				
				$this->twitter = \Codebird\Codebird::getInstance();
				
				$this->twitter->setToken( $twitter_oauth_token, $twitter_oauth_secret );
				
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Post To Twitter
	 * 
	 * Handles to Post on Twitter account
	 * 
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
	public function wpw_auto_poster_post_to_twitter( $post ) {
		
		global $wpw_auto_poster_options;
		
		//posting logs data
		$posting_logs_data = array();
		
		//metabox field prefix
		$prefix = WPW_AUTO_POSTER_META_PREFIX;
		
		$post_type 	= $post->post_type; //post type
		
		//get tweet template from post meta
		$tw_user_ids = get_post_meta( $post->ID, $prefix . 'tw_user_id' );

		/******* Code to posting to selected category Twitter account ******/

		//$categories = get_the_category( $post->ID, array()); // get post categories

		// get all categories for custom post type
        $categories = wpw_auto_poster_get_post_categories_by_ID( $post_type, $post->ID );

		// Get all selected account list from category
		$category_selected_social_acct = get_option( 'wpw_auto_poster_category_posting_acct');
		// IF category selected and category social account data found
		if( !empty( $categories ) && !empty( $category_selected_social_acct ) && empty( $tw_user_ids ) ) {
			$tw_clear_cnt = true;

			// GET FB user account ids from post selected categories
			foreach ( $categories as $key => $term_id ) {
				
				$cat_id = $term_id;
				// Get TW user account ids form selected category  
				if( isset( $category_selected_social_acct[$cat_id]['tw'] ) && !empty( $category_selected_social_acct[$cat_id]['tw'] ) ) {
					// clear TW user data once
					if( $tw_clear_cnt)
						$tw_user_ids = array();
					$tw_user_ids = array_merge($tw_user_ids, $category_selected_social_acct[$cat_id]['tw'] );
					$tw_clear_cnt = false;
				}
			}
			if( !empty( $tw_user_ids ) ) {
				$tw_user_ids = array_unique($tw_user_ids);
			}
		}

		//check twitter user ids are empty in metabox and set in settings page
		if( empty( $tw_user_ids ) 
			&& isset( $wpw_auto_poster_options[ 'tw_type_'.$post_type.'_user' ] ) 
			&& !empty( $wpw_auto_poster_options[ 'tw_type_'.$post_type.'_user' ] ) ) {
			//users from settings
			$tw_user_ids = $wpw_auto_poster_options[ 'tw_type_'.$post_type.'_user' ];
		} //end if
		
		//check twitter user ids are empty selected for posting
		if( empty( $tw_user_ids ) ) {
			
			//record logs for twitter users are not selected
			$this->logs->wpw_auto_poster_add( 'Twitter error: user not selected for posting.' );
			sap_add_notice( __('Twitter: You have not selected any user for the posting.', 'wpwautoposter' ), 'error');
			//return false
			return false;
			
		} //end if to check user ids are empty
		
		//convert user ids to single array
		$post_to_users 	= ( array ) $tw_user_ids;
		
		//Twitter Consumer Key and Secret
		$twitter_keys = isset( $wpw_auto_poster_options['twitter_keys'] ) ? $wpw_auto_poster_options['twitter_keys'] : array();
		$disable_image_tweet = !empty( $wpw_auto_poster_options['tw_disable_image_tweet'] ) ? $wpw_auto_poster_options['tw_disable_image_tweet'] : '';
		
		//initial value of posting flag
		$postflg = false;
		
		if( !empty( $post_to_users ) ) { // Check all user ids
			foreach ( $post_to_users as $tw_user_key => $tw_user_value ) {

				// array start from zero while users stored as 1,2,3 so did -1 logic here
				$tw_key = $tw_user_value - 1;

				$tw_consumer_key 		= isset( $twitter_keys[$tw_key]['consumer_key'] ) ? $twitter_keys[$tw_key]['consumer_key'] : '';
				$tw_consumer_secret 	= isset( $twitter_keys[$tw_key]['consumer_secret'] ) ? $twitter_keys[$tw_key]['consumer_secret'] : '';
				$tw_auth_token 			= isset( $twitter_keys[$tw_key]['oauth_token'] ) ? $twitter_keys[$tw_key]['oauth_token'] : '';
				$tw_auth_token_secret 	= isset( $twitter_keys[$tw_key]['oauth_secret'] ) ? $twitter_keys[$tw_key]['oauth_secret'] : '';
				
				//load twitter class
				$twitter = $this->wpw_auto_poster_load_twitter( $tw_consumer_key, $tw_consumer_secret, $tw_auth_token, $tw_auth_token_secret );
				
				//check twitter class is loaded or not
				if( !$twitter ) return false;
				
				//record logs for twitter posting
				$this->logs->wpw_auto_poster_add( 'Twitter posting to user account begins.' );
				
				//get tweet template from post meta
				$status = get_post_meta( $post->ID, $prefix . 'tw_template', true );
				
				$status = apply_filters( 'wpw_post_meta_tw_template', $status, $post->ID );
				
				//check tweet template is empty in post meta
				if( empty( $status ) ) {
					$status = $this->model->wpw_auto_poster_get_tweet_template( $wpw_auto_poster_options['tw_tweet_template'] );
				} //end if 


				//replace tweet status with template
				$status = $this->model->wpw_auto_poster_tweet_status ( $post, $status );
				
				//use content with short description
				$tweetdesc = $this->model->wpw_auto_poster_excerpt( $status );
				
				/**************
				 * Image Priority
				 * If metabox image set then take from metabox
				 * If metabox image is not set then take from featured image
				 * If featured image is not set then take from settings page
				 **************/
				
				//get custom image from post / page / custom post type
				$wpw_auto_poster_custom_img = get_post_meta( $post->ID, $prefix . 'tw_image', true );
				
				//get featured image from post / page / custom post type
				$post_featured_img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
				
				//check custom image is set in meta and not empty
				if( isset( $wpw_auto_poster_custom_img['src'] ) && !empty( $wpw_auto_poster_custom_img['src'] ) ) {
					$post_img = $wpw_auto_poster_custom_img['src'];
				} elseif ( isset( $post_featured_img[0] ) && !empty( $post_featured_img[0] ) ) {
					//check post featrued image is set the use that image
					$post_img = $post_featured_img[0];
				} else {
					//else get post image from settings page
					$post_img = $wpw_auto_poster_options['tw_tweet_img'];
				}
				
				//record logs for twitter data
				$this->logs->wpw_auto_poster_add( 'Twitter post data : ' . $tweetdesc );
				
				//posting logs data
				if( !empty( $post_img ) ) {
					$posting_logs_data = array(	
												'status' => $tweetdesc,
												'image'  => $post_img
											);
				} else {
					$posting_logs_data = array(	
												'status' => $tweetdesc
											);
				}
				
				try {
					
					//do posting to twitter
					if( !empty( $post_img ) && ! $disable_image_tweet ) {
						
						// build an array of images to send to twitter
					    $upload = $this->twitter->media_upload(array(
					        'media' => $post_img
					    ));
					    
					    // check if media upload function successfully run
					    if( $upload->httpstatus == 200 ){

					    	//upload the file to your twitter account
					    	$media_ids = $upload->media_id_string;

					    	$params = array(
							  'status' => $tweetdesc,
							  'media_ids' => $media_ids
							);
					    } else {
					    	$params = array(
							  'status' => $tweetdesc
							);
					    }
					
					} else {
						$params = array(
						  'status' => $tweetdesc
						);
					}

					$result = $this->twitter->statuses_update($params);

					//check id is set in result data and not empty
					if( isset( $result->id ) && !empty( $result->id ) ) {
						
						//User details
						$posting_logs_user_details = array(
																'account_id' 				=> isset( $result->user->id ) ? $result->user->id : '',
																'display_name'				=> isset( $result->user->name ) ? $result->user->name : '',
																'user_name'					=> isset( $result->user->screen_name ) ? $result->user->screen_name : '',
																'twitter_consumer_key' 		=> $tw_consumer_key,
																'twitter_consumer_secret'	=> $tw_consumer_secret,
																'twitter_oauth_token'		=> $tw_auth_token,
																'twitter_oauth_secret'		=> $tw_auth_token_secret,
															);
						
						//posting logs store into database
						$this->model->wpw_auto_poster_insert_posting_log( $post->ID, 'tw', $posting_logs_data, $posting_logs_user_details );
						
						//record logs for post posted to twitter
						$this->logs->wpw_auto_poster_add( 'Twitter posted to user account : Response ID ' . $result->id );
						
						//posting flag that posting successfully
						$postflg = true;
						
					}
					
					//check error is set
					if( isset( $result->errors ) && !empty( $result->errors ) ) {
						//record logs for twitter posting exception
						$this->logs->wpw_auto_poster_add( 'Twitter error: ' . $result->errors[0]->code . ' | ' .$result->errors[0]->message );
						sap_add_notice( sprintf( __('Twitter: Error while posting %s', 'wpwautoposter' ), $result->errors[0]->message ), 'error');
					}
					//return $result;
					
				} catch ( Exception $e ) {
					//record logs exception generated
					$this->logs->wpw_auto_poster_add( 'Twitter error: ' . $e->__toString() );
					sap_add_notice( sprintf( __('Twitter: Something was wrong while posting %s', 'wpwautoposter' ), $e->__toString() ), 'error');
					$postflg = false;
					//return false;
				}
			}
		}
		//returning post flag
		return $postflg;
	}
	
	/**
	 * Get Twitter User Data
	 * 
	 * Handles to get twitter user data
	 * 
	 * @package Social Auto Poster
	 * @since 1.4.0
	 */
	public function wpw_auto_poster_get_user_data( $twitter_consumer_key, $twitter_consumer_secret, $twitter_oauth_token, $twitter_oauth_secret ) {
		
		//load twitter class
		$twitter = $this->wpw_auto_poster_load_twitter( $twitter_consumer_key, $twitter_consumer_secret, $twitter_oauth_token, $twitter_oauth_secret );
	
		//check twitter class is loaded or not
		if( !$twitter ) return false;
		
		//getting user data from twitter
		//$response = $this->twitter->get('https://api.twitter.com/1.1/account/verify_credentials.json');
		$response = $this->twitter->account_verifyCredentials();
		
		// Double check if response is in json then again decode it
		if( is_string($response) ) {
			$response = json_decode($response);
		}
		
		//if user data get successfully
		if( isset( $response->id_str ) && $response->id_str ) {
			
			return $response;
		}
		return false;
	}
	
	/**
	 * Twitter Posting
	 * 
	 * Handles to twitter posting
	 * by post data
	 * 
	 * @package Social Auto Poster
 	 * @since 1.5.0
	 */
	public function wpw_auto_poster_tw_posting( $post ) {
		
		global $wpw_auto_poster_options;
		
		$prefix = WPW_AUTO_POSTER_META_PREFIX;
		
	 	
		$res = $this->wpw_auto_poster_post_to_twitter( $post );
		
		//if( !isset( $res->errors ) && !empty( $res->id ) ) { //check if error should not occured and successfully tweeted
		if( !empty( $res ) ) { //check post has been posted on twitter or not
			
			//record logs for posting done on twitter
			$this->logs->wpw_auto_poster_add( 'Twitter posting completed successfully.' );
			
			update_post_meta( $post->ID, $prefix . 'tw_status', '1' );

			// get current timestamp and update meta as published date/time
            $current_timestamp = current_time( 'timestamp' );
            update_post_meta($post->ID, $prefix . 'published_date', $current_timestamp);
            
			return true;
		}
		return false;
	}
}