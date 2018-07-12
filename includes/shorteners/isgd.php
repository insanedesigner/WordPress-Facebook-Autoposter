<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Tinyurl Class
 *
 * Handles to make url shortner with tinyurl
 * 
 * @package Social Auto Poster
 * @since 1.0.0
 */

 class wpw_auto_poster_tw_isgd {
 	
   	var $name;
	var $url;

    function wpw_auto_poster_tw_isgd() {
    	$this->name = 'tinyurl';
    }
    
    function shorten( $pageurl ) {
		$isgd_url =  wp_remote_fopen('http://is.gd/create.php?format=simple&url=' . urlencode( $pageurl ) ); 
		if ( $isgd_url ) {
			return $tiny_url;
		} else {
			return $pageurl;	
		}
	}
 }	
?>