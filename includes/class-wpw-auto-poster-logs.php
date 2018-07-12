<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Logs Class
 *
 * Handles to write logs to the file
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
class Wpw_Auto_Poster_Logs {
	
	private $_handles;

	public function __construct() {
		$this->_handles = array();
	}


	/**
	 * Destructor of Class
	 *
	 * @package Social Auto Poster
 	 * @since 1.0.0
	 **/
	public function __destruct() {
		
		foreach ( $this->_handles as $handle ){
	       @fclose( escapeshellarg( $handle ) );
		}	       
	}


	/**
	 * Open Log File
	 *
	 * Handles to open log file
	 * 
	 * @package Social Auto Poster
 	 * @since 1.0.0
	 **/
	private function wpw_auto_poster_open( $handle = 'logs' ) {
		
		//check handle is set in class handles 
		if ( isset( $this->_handles[ $handle ] ) ) {
			//return true
			return true;
		}

		
		//check handle is opened successfully
		if ( $this->_handles[ $handle ] = fopen( WPW_AUTO_POSTER_LOG_DIR . $this->wpw_auto_poster_file_name( $handle ), 'a' ) ) {
			//return true
			return true;
		}
		
		//else return false
		return false;
	}


	/**
	 * Write Log File
	 *
	 * Handles to write log file
	 * 
	 * @package Social Auto Poster
 	 * @since 1.0.0
	 **/
	public function wpw_auto_poster_add( $message, $time = false, $handle = 'logs' ) {
		
		global $wpw_auto_poster_options;
		
		//check logs is enables or not
		// if( isset( $wpw_auto_poster_options['enable_logs'] ) && !empty( $wpw_auto_poster_options['enable_logs'] ) ) {
		
			//check file is opened succesfully and it is resource
			if ( $this->wpw_auto_poster_open( $handle ) && is_resource( $this->_handles[ $handle ] ) ) {
				
				$logmsg = '';
				
				//check need to write time to file
				if( $time ) {
					//append time to log message
					$logmsg .= "\n" . date_i18n( 'm-d-Y @ H:i:s - ' );
				} //end if to check time write to logs
				
				//append message to log message
				$logmsg .= $message;
				
				//write log to file
				@fwrite( $this->_handles[ $handle ], $logmsg . "\n" );
				
			} //end if to check file is opened successfully and $handle is resource
			
		// }//end if to check logs is enables or not
	}


	/**
	 * Clear Logs
	 * 
	 * Handles to clear log entries
	 *
	 * @package Social Auto Poster
 	 * @since 1.0.0
	 **/
	public function wpw_auto_poster_clear( $handle = 'logs' ) {

		//check handles opened successfully and it is resource
		if ( $this->wpw_auto_poster_open( $handle ) && is_resource( $this->_handles[ $handle ] ) ) {
			@ftruncate( $this->_handles[ $handle ], 0 );
		} //end if to check handles opened successfully and it is resource 
	}


	/**
	 * File Name
	 * 
	 * Handles to return file name with hash
	 *
	 * @package Social Auto Poster
 	 * @since 1.0.0
	 **/
	public function wpw_auto_poster_file_name( $handle = 'logs' ) {
		//return file name from handles
		return $handle . '-' . sanitize_file_name( wp_hash( $handle ) ) . '.txt';
	}
}
?>