<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Renderer Class
 *
 * To handles some small HTML content for front end
 * 
 * @package Social Auto Poster
 * @since 1.0.0
 */
class Wpw_Auto_Poster_Renderer {
	
	public $model;
	
	public function __construct() {
		
		global $wpw_auto_poster_model;
		
		$this->model = $wpw_auto_poster_model;
	}
	
	/**
	 * Add Popup For View Posting Details 
	 * 
	 * Handels to view posting details with popup
	 * 
	 * @package Social Auto Poster
	 * @since 1.4.0
	 */
	public function wpw_auto_poster_view_posting_popup( $postid ) {
		
		$prefix = WPW_AUTO_POSTER_META_PREFIX;
		
		//get posting details from meta 
	 	$posting_logs = get_post_meta( $postid, $prefix.'posting_logs', true );

	 	//get posting date/time
	 	$format 			   = get_option( 'date_format' ).' '.get_option('time_format') ;
	 	$publication_timestamp = get_the_date($format, $postid);
	 	
	 	//get posting user details from meta 
	 	$user_details = get_post_meta( $postid, $prefix.'user_details', true );

	 	// get posting social type
		$social_type = get_post_meta( $postid, $prefix . 'social_type', true );

		// get posting link
		$post_link = wpw_auto_poster_get_post_link( $social_type, $user_details );
	 	
	 	$html = '';
	 		
		$html .= '<div class="wpw-auto-poster-popup-content">
				
					<div class="wpw-auto-poster-header">
						<div class="wpw-auto-poster-header-title">'.__( 'Social Posting Logs', 'wpwautoposter' ).'</div>
						<div class="wpw-auto-poster-popup-close"><a href="javascript:void(0);" class="wpw-auto-poster-close-button">&times;</a></div>
					</div>';
		
		$html .= '		<div class="wpw-auto-poster-popup wpw-auto-poster-posted-logs">
							
							<table class="form-table" border="1">
								<tbody>
									<tr>
										<th scope="row" class="wpw-auto-poster-label">'.__( 'Label', 'wpwautoposter' ).'</th>
										<th scope="row">'.__( 'Content', 'wpwautoposter' ).'</th>
									</tr>';

										if( !empty( $posting_logs ) &&  count( $posting_logs ) > 0 ) { 
											
											foreach ( $posting_logs as $posting_log_key => $posting_log_value  ) { 
												
												// Check fb_type is exist then display its name
												$posting_log_value = $posting_log_key == 'fb_type' ? $this->model->wpw_auto_poster_get_fb_posting_method( $posting_log_value ) : $posting_log_value;
													
												// Check fb_type is exist then change label
												$posting_log_key = $posting_log_key == 'fb_type' ? __( 'Posting Method', 'wpwautoposter' ) : $posting_log_key;

												$html .= '<tr>
															<td>'.ucwords( $posting_log_key ).'</td>
															<td>'.( $posting_log_key == 'image' || $posting_log_key == 'source'? '<div class="wpw-img-prev"><img src="'.$posting_log_value.'" >' : $posting_log_value ).'</td>
														</tr>';	
											}
											
											if( isset( $user_details['display_name'] ) && !empty( $user_details['display_name'] ) ) { // Check display name
												
												$html .= '<tr>
															<td>'.__( 'Account Name', 'wpwautoposter' ).'</td>
															<td>'.$user_details['display_name'].'</td>
														</tr>';
											}
											$html .= '<tr>
															<td>'.__( 'Date/Time', 'wpwautoposter' ).'</td>
															<td>'.$publication_timestamp.'</td>
													</tr>';

											$html .= '<tr>
															<td>'.__( 'Link to Post', 'wpwautoposter' ).'</td>
															<td>'.$post_link.'</td>
													</tr>';
											
										} else { 
											$html .= '<tr>
													<td colspan="2">'.$postid.__( 'No posting logs yet.','wpwautoposter' ).'</td>
												</tr>';
										}	
		$html .= '					</tbody>
							</table>
					</div><!--.wpw-auto-poster-popup-->
		
				</div><!--.wpw-auto-poster-popup-content-->
				<div class="wpw-auto-poster-popup-overlay"></div>';
		
		return $html;
		
	}
}
?>