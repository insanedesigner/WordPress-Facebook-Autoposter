<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Meta Box Validate Class
 *
 * Handles all the functions to validate meta box data.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */

if( ! class_exists( 'Wpw_Auto_Poster_Meta_Box_Validate' ) ) :

class Wpw_Auto_Poster_Meta_Box_Validate {

	var $model;
	
	public function __construct() {		
		
		global $wpw_auto_poster_model;
		
		$this->model = $wpw_auto_poster_model;		
	}
	
	public function date_str_to_time( $data ) {
            return strtotime( $data );
    }
    
    public function escape_html( $data ) {
	
    	return $this->model->wpw_auto_poster_stripslashes_deep( $data ); 
    }
	
} // End Class

endif; // End Check Class Exists