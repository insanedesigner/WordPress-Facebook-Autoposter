<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Meta Box Class
 *
 * Handles admin side plugin functionality.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */

//include the main class file
require_once ( WPW_AUTO_POSTER_META_DIR . '/meta-box-class.php' );

class Wpw_Auto_Poster_Social_Meta_Box extends Wpw_Auto_Poster_Meta_Box {
	
	public function __construct( $config = array() ) {
		
		if( !empty( $config ) ) {
			
			parent::__construct( $config );
		
			// Must enqueue for all pages as we need js for the media upload, too.
			add_action( 'admin_enqueue_scripts', array( &$this, 'wpw_auto_poster_load_scripts_styles' ) );
		}
	}
	
	public function wpw_auto_poster_reset_tweet_template() {
		
		global $wpw_auto_poster_options;
		
		$result = array();
		
		$postid = $_POST['postid'];
		$metaname = $_POST['meta'];
		$posttitle = $_POST['title'];
		//$tweetmode = $_POST['tweetmode'];
		
		$post = get_post( $postid );
		
		if( class_exists( 'Wpw_Auto_Poster_Model' ) ) {
			
			$model = new Wpw_Auto_Poster_Model();
			
			$templatetags = '';
			if( method_exists( 'Wpw_Auto_Poster_Model', 'wpw_auto_poster_get_tweet_template' ) ) {
				$templatetags = $model->wpw_auto_poster_get_tweet_template( $wpw_auto_poster_options['tw_tweet_template'] );
			}
			update_post_meta( $postid, $metaname, $templatetags );
			//update_post_meta( $postid, $tweetmode, '0' );
			if( class_exists( 'Wpw_Auto_Poster_Model' ) && method_exists( 'Wpw_Auto_Poster_Model', 'wpw_auto_poster_tweet_status' ) ) {
	  			$model = new Wpw_Auto_Poster_Model();
	  			$template = $model->wpw_auto_poster_tweet_status( $post, $templatetags, $posttitle );
	  		}
  			
		}
  		$result['template'] = $template;
		$result['newtemp'] = $templatetags;
		$result['success']	= '1';
		
		echo json_encode( $result );
		exit;
		
	}
	
	public function wpw_auto_poster_update_tweet_template() {
		
		$result = array();
		
		$postid = $_POST['postid'];
		$metaname = $_POST['meta'];
		$template = $_POST['temp'];
		$posttitle = $_POST['title'];
		
		$post = get_post( $postid );
		update_post_meta( $postid, $metaname, $template );
		
		if( class_exists( 'Wpw_Auto_Poster_Model' ) && method_exists( 'Wpw_Auto_Poster_Model', 'wpw_auto_poster_tweet_status' ) ) {
  			$model = new Wpw_Auto_Poster_Model();
  			$template = $model->wpw_auto_poster_tweet_status( $post, $template, $posttitle );
  		}
  		
		$result['template'] = $template;
		$result['newtemp'] = $_POST['newtemp'];
		$result['success']	= '1';
		
		echo json_encode( $result );
		exit;
	}
	
	public function wpw_auto_poster_load_scripts_styles() {
		
		// Get Plugin Path
		$plugin_path = WPW_AUTO_POSTER_META_URL;
					
		// Check for which post type we need to load the styles and scripts	
		if( $this->_meta_box['pages'] == 'all' ) {
			$pages = get_post_types( array( 'public' => true ), 'names' );
		} else {
			$pages = $this->_meta_box['pages'];
		}
		
		/**
		 * only load styles and js when needed
		 * since 1.8
		 */
		global $typenow;
    
		if ( in_array( $typenow, $pages ) && $this->is_edit_page() ) {
			
			// Register & Enqueue Extend Meta Box Style
			wp_register_style( 'wpw-auto-poster-meta-box', $plugin_path . '/css/wpw-auto-poster-meta-box.css', array(), WPW_AUTO_POSTER_VERSION );
      		wp_enqueue_style( 'wpw-auto-poster-meta-box' );
      		
			// Register & Enqueue Extend Meta Box Scripts
			wp_register_script( 'wpw-auto-poster-meta-box-script', $plugin_path . '/js/wpw-auto-poster-meta-box.js', array( 'jquery' ), WPW_AUTO_POSTER_VERSION, true );
			wp_enqueue_script( 'wpw-auto-poster-meta-box-script' );
			wp_localize_script( 'wpw-auto-poster-meta-box-script', 'WPSAPMeta', array(	
																					'invalid_url' => __( 'Please enter valid url.', 'wpwautoposter' ),
																				) );
		}
	}

	/**
	 * Add Facebook Grant Permission Field
	 * @author Ohad Raz
	 */
	public function addGrantPermission( $id, $args, $repeater = false ) {
		
		$new_field = array( 'type' => 'grantpermission','id'=> $id,'std' => '','desc' => '','style' =>'','name' => 'FB Grant Permission' );
		$new_field = array_merge( $new_field, $args );
    	
		if( false === $repeater ) {
			$this->_fields[] = $new_field;
		} else {
			return $new_field;
		}
		
	}
	
	/**
	 * 
	 * Show Facebook Grant Permission Field
	 * 
	 * @param string $field 
	 * @param string|mixed $meta 
	 * @since 1.0.0
	 * @access public
	 */
	
	public function show_field_grantpermission( $field, $meta ) {
		
		echo "<div class='wpw-auto-poster-error'>
				<p>".$field['desc']."</p>
				<p><a href='" . $field['url'] . "'>". $field['urltext'] . "</a></p>
			</div>";
		
	}
	
	/**
	 * Add Label to meta box
	 * 
	 * @author Ohad Raz
	 * 
	 */
	public function addTweetStatus($id, $args, $repeater=false){
	
		$new_field = array( 'type' => 'tweetstatus','id'=> $id,'default' => '0','std' => '','desc' => '','style' =>'','name' => 'Label' );
		$new_field = array_merge( $new_field, $args );
    	
		if( false === $repeater ) {
			$this->_fields[] = $new_field;
		} else {
			return $new_field;
		}
	}
	
	/**
	 * Show Field Tweet Status Label.
	 *
	 * @param string $field 
	 * @param string $meta 
	 * @since 1.0.0
	 * @access public
	 */
	public function show_field_tweetstatus( $field, $meta) {  
		
		global $post;

		$this->show_field_begin( $field, $meta );

		$metatext 	= __( 'Unpublished','wpwautoposter' );			
		if( $meta == 1 ) {
			$metatext 	= __( 'Published','wpwautoposter' );		
		} elseif ( $meta == 2 ) {
			$metatext 	= __( 'Scheduled','wpwautoposter' );			
		}

		$postid 	= isset($post->ID) ? $post->ID : '';
		
		echo "<label for='{$field['id']}' id='{$field['id']}' class='wpw-lbl-{$field['id']}'>{$metatext}</label>";
		
		if( $meta ) {
			echo "<input type='button' id='wpw-auto-poster-rstatus' class='wpw-auto-poster-rstatus button button-secondary' name='wpw_auto_poster_reset_status' value='".__('Reset Status', 'wpwautoposter')."' aria-label='{$field['id']}' aria-data-id='{$postid}' aria-type='{$field['tab']}' />";
			echo "<span class='wpw-auto-poster-loader spinner'></span>";
		}
		
		$this->show_field_end( $field, $meta );
	} 
	
	/**
	 * Add Publishbox Field to meta box
	 * @author Ohad Raz
	 * @since 1.0.0
	 * @access public
	 * @param $id string  field id, i.e. the meta key
	 * @param $args mixed|array
	 * 		'name' => // field name/label string optional
	 *    	'desc' => // field description, string optional
	 *    	'std' => // default value, string optional
	 *    	'validate_func' => // validate function, string optional
	 * @param $repeater bool  is this a field inside a repeatr? true|false(default) 
	 */
	public function addPublishBox( $id, $args, $repeater=false ) {
   
		$new_field = array( 'type' => 'publishbox', 'id'=> $id, 'std' => '', 'desc' => '', 'style' =>'', 'name' => 'Publish Checkbox Field' );
		$new_field = array_merge( $new_field, $args );
    
		if( false === $repeater ) {
			$this->_fields[] = $new_field;
		} else {
			return $new_field;
		}
	}

	/**
	 * Show PublishBox Checkbox Field.
	 *
	 * @param string $field 
	 * @param string $meta 
	 * @since 1.0.0
	 * @access public
	 */
	public function show_field_publishbox( $field, $meta ) {
  		
		$prefix = WPW_AUTO_POSTER_META_PREFIX;
		$checked_publishbox = apply_filters('wpw_auto_poster_checked_publishbox', array() );
		
		$publishbox_key 	= ( isset($field['id']) ) ? str_replace( $prefix.'post_to_', '', $field['id']) : '';
		
		$meta = apply_filters( 'wpw_auto_poster_checked_publishbox_meta', $meta );

		$checked_publishbox = ( ( in_array($publishbox_key, $checked_publishbox) ) && $meta == 'on' ) ? 1 : 0;
		
		$this->show_field_begin($field, $meta);
		echo "<input type='checkbox' class='rw-checkbox' name='{$field['id']}' id='{$field['id']}' ".checked( 1, $checked_publishbox, false )." /><p class='wpw-auto-poster-meta'>{$field['desc']}</p></td>";
		//" . checked(!empty($meta), true, false) . "
	}

	/**
	 * Add Tweet Mode to meta box
	 * @author Ohad Raz
	 */
	
	public function addTweetMode($id, $args, $repeater=false){
	
		$new_field = array( 'type' => 'tweetmode','id'=> $id,'default' => '0','std' => '','desc' => '','style' =>'','name' => 'Label' );
		$new_field = array_merge( $new_field, $args );
    	
		if( false === $repeater ) {
			$this->_fields[] = $new_field;
		} else {
			return $new_field;
		}
	}
  	
	/**
	 * Show Field Tweet Status Label.
	 *
	 * @param string $field 
	 * @param string $meta 
	 * @since 1.0.0
	 * @access public
	 */
	public function show_field_tweetmode( $field, $meta) {  
		
		global $post;
		
		$this->show_field_begin( $field, $meta );
		$meta = $meta == '' ? $field['default'] : $meta;
		$metatxt = $meta == '1' ? __( 'Manual','wpwautoposter' ) : __( 'Automatic','wpwautoposter' );
		
		$class = '';
		if($meta == '1') { $stylemode = "style='display:block;'"; }
		else {
			$stylemode = "style='display:none;'";
			$class = 'tweet-mode-full-width';
		}
		
		echo "<label for='{$field['id']}' id='{$field['id']}' class='wpw-auto-poster-tweet-mode {$class}'>{$metatxt}</label>";
		echo "<input type='hidden' name='{$field['id']}' id='{$field['id']}' value='{$meta}'>";
		
		
		echo "<a href='javascript:void(0);' id='{$post->ID}' class='wpw-auto-poster-reset-tweet-template' {$stylemode}>".__( 'Reset','wpwautoposter' )."</a>";
		echo "<img class='wpw-auto-poster-tweet-template-loader tweet-mode-loader' src='".WPW_AUTO_POSTER_META_URL."/images/ajax-loader.gif' />";
		
		$this->show_field_end( $field, $meta );
	} 
	
	/**
	 * Add Tweet Template Textarea Field to meta box
	 * @author Ohad Raz
	 * @since 1.0.0
	 * @access public
	 * @param $id string  field id, i.e. the meta key
	 * @param $args mixed|array
	 *    	'name' => // field name/label string optional
	 *    	'desc' => // field description, string optional
	 *    	'std' => // default value, string optional
	 *    	'style' =>   // custom style for field, string optional
	 *    	'validate_func' => // validate function, string optional
	 * @param $repeater bool  is this a field inside a repeatr? true|false(default) 
	 */
	public function addTweetTemplate( $id, $args, $repeater=false ) {
    
		$new_field = array( 'type' => 'tweettemplate', 'id'=> $id, 'std' => '', 'desc' => '', 'style' =>'', 'name' => 'Tweet Template Field' );
		$new_field = array_merge( $new_field, $args );
    
		if( false === $repeater ) {
			$this->_fields[] = $new_field;
		} else {
			return $new_field;
		}
	}
	  
	/**
	 * Show Field Tweet Template
	 * 
	 * @param string $field 
	 * @param string $meta 
	 * @since 1.0.0
	 * @access public
	 */
  	public function show_field_tweettemplate( $field, $meta ) {
  		
  		global $post;
  		
  		$this->show_field_begin( $field, $meta );
  		$meta = $meta == '' ? $field['default'] : $meta; //check if post is new created then it will consider from default
  		
  		echo "<div class='wpw-auto-poster-tweet-template'>";
  		echo "<span>{$meta}</span>";
  		echo "</div>";
  		echo "<div class='wpw-auto-poster-tweet-edit-template'>";
	  		echo "<textarea class='wpw-auto-poster-meta-textarea large-text' id='{$field['id']}' name='{$field['id']}' cols='60' rows='3'>{$meta}</textarea>"; //{$meta}
	  		//echo "<input type='hidden' id='{$field['id']}' class='wpw-auto-poster-meta-hidden' value=''>";
			echo "<input type='button' id='{$post->ID}' class='wpw-auto-poster-tweet-template-update button' value='".__( 'Update','wpwautoposter' )."' />";
			echo "<input type='button' id='{$field['id']}' class='wpw-auto-poster-tweet-template-cancel button' value='".__( 'Cancel','wpwautoposter' )."' />";
			echo "<img class='wpw-auto-poster-tweet-template-loader' src='".WPW_AUTO_POSTER_META_URL."/images/ajax-loader.gif' />";
		echo "</div>";
		$this->show_field_end( $field, $meta );
  	}
  	
	/**
	 * Add Label to meta box (generic function)
	 * @author Ohad Raz
	 * 
	 */
	public function addTweetPreview($id, $args, $repeater=false){
	
		$new_field = array( 'type' => 'tweetpreview','id'=> $id,'default' => '[title] - [link]','std' => '','desc' => '','style' =>'','name' => 'Label' );
		$new_field = array_merge( $new_field, $args );
    	
		if( false === $repeater ) {
			$this->_fields[] = $new_field;
		} else {
			return $new_field;
		}
	}
	
  	/**
  	 * Show Field Tweet Preview
  	 * 
  	 * @param string $field 
	 * @param string $meta 
	 * @since 1.0.0
	 * @access public
  	 */
  	public function show_field_tweetpreview( $field, $meta ) {
  		
  		global $post;
  		$this->show_field_begin( $field, $meta );
  		$meta = $meta == false ? $field['default'] : $meta; //check if post is new created then it will consider from default
  		if( class_exists( 'Wpw_Auto_Poster_Model' ) && method_exists( 'Wpw_Auto_Poster_Model', 'wpw_auto_poster_tweet_status' ) ) {
  			$model = new Wpw_Auto_Poster_Model();
  			$meta = $model->wpw_auto_poster_tweet_status( $post, $meta );
  		}
  		$tw_tweet_exceed_message = sprintf( __('Twitter only allow %1$s280 characters%2$s limit for the tweet. If the tweet message exceeds the limit it will be automatically truncated.', 'wpwautoposter'), '<strong>', '</strong>');

  		echo "<label for='{$field['id']}' id='{$field['id']}' class='wpw-auto-poster-tweet-preview'>{$meta}</label>";
  		$count = strlen( $meta );
  		$count_class = ( $count > 280 ) ? 'red-color' : '';

  		echo "<div id='{$field['id']}_count' class='wpw-auto-poster-tweet-preview-count ".$count_class."'>{$count}</div>";
  		if( $count > 280 ) {
  			echo '<div class="tweet-template-warning-message" id="tweet-warning-message">'.$tw_tweet_exceed_message.'</div>';
  		}
		$this->show_field_end( $field, $meta );
  		
  	}
  	
  	/**
	 * Function to reset the post social update status
	 *
	 * @package Social Auto Poster
	 * @since 1.6
	 */
  	function wpw_auto_poster_reset_post_social_status() {

		$prefix = WPW_AUTO_POSTER_META_PREFIX;

  		$result		= array();
  		$post_id 	= ( !empty($_POST['postid']) && is_numeric($_POST['postid']) ) ? trim($_POST['postid']) : '';
  		$meta		= (!empty($_POST['meta'])) ? trim($_POST['meta']) : '';
  		$social_type= (!empty($_POST['social_type'])) ? trim($_POST['social_type']) : '';

  		// Updating the meta
  		if( $post_id && $meta ) {

  			delete_post_meta( $post_id, $meta );

			// Remove network from scheduled schedule wall post
			$schedules = get_post_meta( $post_id, $prefix.'schedule_wallpost', true );
			if( !empty( $schedules ) ) {
				if(($key = array_search($social_type, $schedules)) !== false) {
				    unset($schedules[$key]);
				}
				
				if( empty( $schedules ) ){
					delete_post_meta( $post_id, $prefix.'schedule_wallpost' );
				} else {
					update_post_meta( $post_id, $prefix.'schedule_wallpost', $schedules );
				}
			}
			else { // remove post meta if no social media for schedule

               delete_post_meta( $post_id, $prefix.'schedule_wallpost' );        
            }

  			$result['status'] = 'success';
  			
  		} else {
  			$result['status'] = 'error';
  		}
  		
  		echo json_encode( $result );
  		
  		die();
  	}
  	
  	/**
	 * Adding Hooks
	 *
	 * @package Social Auto Poster
	 * @since 1.0.0
	 */
  	public function add_hooks(){
  		
  		//Ajax for saving tweet template
		add_action( 'wp_ajax_wpw_auto_poster_update_tweet_template', array( &$this, 'wpw_auto_poster_update_tweet_template') );		
		add_action( 'wp_ajax_wpw_auto_poster_reset_tweet_template', array( &$this, 'wpw_auto_poster_reset_tweet_template') );
		
		// Ajax for reset the post social publish status
		add_action( 'wp_ajax_wpw_auto_poster_reset_post_social_status', array( &$this, 'wpw_auto_poster_reset_post_social_status') );
  	}
}
?>