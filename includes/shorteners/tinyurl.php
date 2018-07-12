<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

 class wpw_auto_poster_tw_tinyurl {
 	
   	var $name;
	var $url;

    function wpw_auto_poster_tw_tinyurl() {
    	$this->name = 'tinyurl';
    }
    
    function shorten( $pageurl ) {
		$tiny_url =  wp_remote_fopen('http://tinyurl.com/api-create.php?url=' . urlencode( $pageurl )); 
		if ( $tiny_url ) {
			return $tiny_url;
		} else {
			return $pageurl;	
		}
	}
 }	
?>