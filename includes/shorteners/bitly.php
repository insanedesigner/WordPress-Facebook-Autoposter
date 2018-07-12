<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Bitly Class
 *
 * Handles to make url shortner with bitly
 * 
 * @package Social Auto Poster
 * @since 1.0.0
 */
 class wpw_auto_poster_tw_bitly {
 	
   	var $name,$access_token;

    function wpw_auto_poster_tw_bitly($access_token) {
    	$this->name = 'bitly';
    	$this->access_token = $access_token;
    }
    
    function shorten( $pageurl ) {
    	
    	$request_uri = 'https://api-ssl.bitly.com/v3/shorten?' .
    		'access_token=' . $this->access_token .
			'&longUrl=' . urlencode( $pageurl );
			
		$encoded_data =  wp_remote_fopen( $request_uri ); 
		
		if ( $encoded_data ) {
			$decoded_result = json_decode( $encoded_data );
			if ( $decoded_result && $decoded_result->status_code == 200 && isset( $decoded_result->data ) && isset( $decoded_result->data->url ) ) {
				return $decoded_result->data->url;	
			}
		}
		
		return $pageurl;
	}
 }	
?>