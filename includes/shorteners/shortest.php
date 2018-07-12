<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

 class wpw_auto_poster_tw_shortest {
 	
   	var $name;
	var $url;

    function wpw_auto_poster_tw_shortest() {
    	$this->name = 'shortest';
    }
    
    function shorten( $api_token, $pageurl ) {
		
    	$shortest =  json_decode( wp_remote_fopen('https://api.shorte.st/s/'.$api_token.'/' . urlencode( $pageurl )) ); 
		
    	if ( $shortest ) {
			return $shortest->{'shortenedUrl'};
		} else {
			return $pageurl;	
		}
	}
 }	
?>