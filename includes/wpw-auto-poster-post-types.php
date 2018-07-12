<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Post Type Functions
 *
 * Handles all custom post types
 * 
 * @package Social Auto Poster
 * @since 1.4.0
 */

/**
 * Setup Social Posting Logs Post PostTypes
 *
 * Registers the social posting logs post posttypes
 * 
 * @package Social Auto Poster
 * @since 1.4.0
 */
function wpw_auto_poster_register_post_types() {
	
	//social posing logs - post type
	$social_posting_logs_labels = array(
						    'name'				=> __('Social Posing Logs','wpwautoposter'),
						    'singular_name' 	=> __('Social Posing Log','wpwautoposter'),
						    'add_new' 			=> __('Add New','wpwautoposter'),
						    'add_new_item' 		=> __('Add New Social Posing Log','wpwautoposter'),
						    'edit_item' 		=> __('Edit Social Posing Log','wpwautoposter'),
						    'new_item' 			=> __('New Social Posing Log','wpwautoposter'),
						    'all_items' 		=> __('All Social Posing Logs','wpwautoposter'),
						    'view_item' 		=> __('View Social Posing Log','wpwautoposter'),
						    'search_items' 		=> __('Search Social Posing Log','wpwautoposter'),
						    'not_found' 		=> __('No social posing logs found','wpwautoposter'),
						    'not_found_in_trash'=> __('No social posing logs found in Trash','wpwautoposter'),
						    'parent_item_colon' => '',
						    'menu_name' 		=> __('Social Posing Logs','wpwautoposter'),
						);
	$social_posting_logs_args = array(
						    'labels' 				=> $social_posting_logs_labels,
						    'public' 				=> false,
						    'query_var' 			=> false,
						    'rewrite' 				=> false,
						    'capability_type' 		=> WPW_AUTO_POSTER_LOGS_POST_TYPE,
						    'hierarchical' 			=> false,
						    'supports' 				=> array( 'title' )
					 	); 
	
	//register social posing logs post type
	register_post_type( WPW_AUTO_POSTER_LOGS_POST_TYPE, $social_posting_logs_args );
	
}
//register custom post type
add_action( 'init', 'wpw_auto_poster_register_post_types', 100 ); // we need to keep priority 100, because we need to execute this init action after all other init action called.
?>