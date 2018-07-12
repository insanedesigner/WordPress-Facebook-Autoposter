<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Social Posted Logs List
 *
 * The html markup for the social posted logs list
 * 
 * @package Social Auto Poster
 * @since 1.4.0
 */

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
	
class Wpw_Auto_Poster_Posted_Logs_List extends WP_List_Table {
	
	var $model, $render, $per_page;
	
	function __construct(){
		
		global $wpw_auto_poster_model, $wpw_auto_poster_render;
		
		$this->model = $wpw_auto_poster_model;
		$this->render = $wpw_auto_poster_render;
		
        //Set parent defaults
        parent::__construct( array(
							            'singular'  => 'logid',     //singular name of the listed records
							            'plural'    => 'logids',    //plural name of the listed records
							            'ajax'      => false       //does this table support ajax?
							        ) );   
		
		$this->per_page	= apply_filters( 'wpw_auto_poster_posts_per_page', 10 ); // Per page
	}
    
    /**
	 * Displaying Social Posted Logs
	 *
	 * Does prepare the data for displaying social posted logs in the table.
	 * 
	 * @package Social Auto Poster
	 * @since 1.4.0
	 */	
	function display_social_posted_logs() {
	
		$prefix = WPW_AUTO_POSTER_META_PREFIX;
			
		//if search is call then pass searching value to function for displaying searching values
		$args = array();
		
		// Taking parameter
		$orderby 	= isset( $_GET['orderby'] )	? urldecode( $_GET['orderby'] )		: 'ID';
		$order		= isset( $_GET['order'] )	? $_GET['order']                	: 'DESC';
		$search 	= isset( $_GET['s'] ) 		? sanitize_text_field( trim($_GET['s']) )	: null;
		
		$args = array(
						'posts_per_page'		=> $this->per_page,
						'page'					=> isset( $_GET['paged'] ) ? $_GET['paged'] : null,
						'orderby'				=> $orderby,
						'order'					=> $order,
						'offset'  				=> ( $this->get_pagenum() - 1 ) * $this->per_page,
						'wpw_auto_poster_list'	=> true
					);
		
		//searched by search
		if( !empty( $search ) ) {
			$args['s']	= $search;
		}
		
		//searched by month
		if(isset($_REQUEST['m']) && !empty($_REQUEST['m'])) {
			$args['m']	= $_REQUEST['m'];
		}
		
		//searched by post name
		if(isset($_REQUEST['wpw_auto_poster_post_id']) && !empty($_REQUEST['wpw_auto_poster_post_id'])) {
			$args['post_parent']	= $_REQUEST['wpw_auto_poster_post_id'];
		}
		
		//searched by social type
		if(isset($_REQUEST['wpw_auto_poster_social_type']) && !empty($_REQUEST['wpw_auto_poster_social_type'])) {
			$args['meta_query']	= array(
											array(
													'key' => $prefix . 'social_type',
													'value' => $_REQUEST['wpw_auto_poster_social_type'],
												)
										);
		}

		//get social posted logs list data from database
		$results = $this->model->wpw_auto_poster_get_posting_logs_data( $args );

		$data	= isset( $results['data'] ) ? $results['data'] : '';
		$total	= isset( $results['total'] ) ? $results['total'] : 0;
		
		if( !empty( $data ) ) {

			foreach ($data as $key => $value){

				$title = $post_type = $edit_link = '';
				//$permalink = add_query_arg( array( 'page' => 'wpw-auto-poster-posted-logs', 'postid' => $value['ID'] ), admin_url( 'admin.php' ) );

				//post title & post type
				if( isset( $value[ 'post_parent' ] ) && !empty( $value[ 'post_parent' ] ) ) { // Check post parent is not empty
					$edit_link	= get_edit_post_link( $value[ 'post_parent' ] );
					$title		= get_the_title( $value[ 'post_parent' ] );
					$post_type	= get_post_type( $value[ 'post_parent' ] );
				}
				$data[$key]['post_title'] 	= '<a target="_blank" href="'.$edit_link.'">' . $title . '</a>';
				$data[$key]['post_type'] 	= $post_type;

				//social type
				$social_type = get_post_meta( $value['ID'], $prefix . 'social_type', true );
				$data[$key]['social_type'] = $social_type;

				$data[$key]['view_details'] = '';
			}
		}
		
		$result_arr['data']		= !empty($data) ? $data : array();
		$result_arr['total'] 	= $total; // Total no of data
		
		return $result_arr;
	}
	
	/**
	 * Mange column data
	 *
	 * Default Column for listing table
	 * 
	 * @package Social Auto Poster
	 * @since 1.4.0
	 */
	function column_default( $item, $column_name ){
		switch( $column_name ) {
			case 'post_title':
				$title = $item[ $column_name ];
		    	if( strlen( $title ) > 50 ) {
					$title = substr( $title, 0, 50 );
					$title = $title.'...';
				}
				return $title;
			case 'social_type':
				return isset( $item[ $column_name ] ) ? $this->model->wpw_auto_poster_get_social_type_name( $item[ $column_name ] ) : '';
			case 'post_date':
				return isset( $item[ $column_name ] ) ? $this->model->wpw_auto_poster_get_date_format( $item[ $column_name ] ) : '';
			case 'view_details':
				$viewdetailspopup = '<a href="javascript:void(0);" class="wpw-auto-poster-meta-view-details">'.__( 'View Details', 'wpwautoposter' ).'</a>';
				$viewdetailspopup .= $this->render->wpw_auto_poster_view_posting_popup( $item['ID'] );
				return $viewdetailspopup;
            default:
				return $item[ $column_name ];
        }
    }
    
	/**
	 * Mange post type column data
	 *
	 * Handles to modify post type column for listing table
	 * 
	 * @package Social Auto Poster
	 * @since 1.4.0
	 */
    function column_post_type($item) {
    	
		// get all custom post types
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		
		$post_type_sort_link = '';
		if( !empty( $item[ 'post_type' ] ) && isset( $post_types[$item[ 'post_type' ]]->label ) ) {
			/*$post_type_sort_url = add_query_arg( array( 'page' => 'wpw-auto-poster-posted-logs', 'wpw_auto_poster_post_type' => $item[ 'post_type' ] ), admin_url( 'admin.php' ) );
			$post_type_sort_link = '<a href="' . $post_type_sort_url . '" >' . $post_types[$item[ 'post_type' ]]->label . '</a>';*/
			$post_type_sort_link = $post_types[$item[ 'post_type' ]]->label;
		}
		return $post_type_sort_link;
    }
    
    /**
     * Manage Post Title Column
     *
     * @package Social Auto Poster
     * @since 1.4.0
     */
    
    function column_post_title($item){
    	
    	$pagestr = $pagenumber = '';
    	if( isset( $_GET['paged'] ) ) { $pagestr = '&paged=%s'; $pagenumber = $_GET['paged']; }
    	 
    	$actions['delete'] = sprintf('<a class="wpw-auto-poster-post-title-delete wpw-auto-poster-logs-delete" href="?page=%s&action=%s&logid[]=%s'.$pagestr.'">'.__('Delete', 'wpwautoposter').'</a>','wpw-auto-poster-posted-logs','delete',$item['ID'], $pagenumber );
    	
         //Return the title contents	        
        return sprintf('%1$s %2$s',
            /*$1%s*/ $item['post_title'],
            /*$2%s*/ $this->row_actions( $actions )
        );
        
    }
   	
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }
    
    /**
     * Display Columns
     * 
     * Handles which columns to show in table
     * 
	 * @package Social Auto Poster
	 * @since 1.4.0
     */
	function get_columns(){
	
        $columns = array(
    						'cb'      			=>	'<input type="checkbox" />', //Render a checkbox instead of text
				            'post_title'		=>	__( 'Post Title', 'wpwautoposter' ),
				            'post_type'			=>	__(	'Post Type', 'wpwautoposter' ),
				            'view_details'		=>	__(	'View Details', 'wpwautoposter' ),
				            'social_type'		=>	__(	'Social Type', 'wpwautoposter' ),
				            'post_date'			=>	__(	'Date', 'wpwautoposter' ),
				        );
        return $columns;
    }
	
    /**
     * Sortable Columns
     *
     * Handles soratable columns of the table
     * 
	 * @package Social Auto Poster
	 * @since 1.4.0
     */
	function get_sortable_columns() {
		
		$sortable_columns	= array(
									'post_title'	=>	array( 'post_title', true ),    //true means its already sorted
									//'post_type'		=>	array( 'post_type', true ),
									//'social_type'	=>	array( 'social_type', true ),
									'post_date'		=>	array( 'post_date', true )
								);
		
		return $sortable_columns;
	}
	
	function no_items() {
		//message to show when no records in database table
		_e( 'No social posting logs found.', 'wpwautoposter' );
	}
	
	/**
     * Bulk actions field
     *
     * Handles Bulk Action combo box values
     * 
	 * @package Social Auto Poster
	 * @since 1.4.0
     */
	function get_bulk_actions() {
		//bulk action combo box parameter
		//if you want to add some more value to bulk action parameter then push key value set in below array
		$actions = array(
							'delete'    => __('Delete','wpwautoposter')
						);
		
		return $actions;
	}
    
	/**
     * Add filter for post types
     *
     * Handles to display records for particular post type
     * 
	 * @package Social Auto Poster
	 * @since 1.4.0
     */
    function extra_tablenav( $which ) {
    	
    	if( $which == 'top' ) {
    		
    		$parent_id_args = array( 'fields' => 'id=>parent' );
    		
			//get social posted logs post parent data from database
			$post_ids = $this->model->wpw_auto_poster_get_posting_logs_data( $parent_id_args );
			
			//get all social types
			$social_types = $this->model->wpw_auto_poster_get_social_type_name();
			
			$post_parent_ids = array();
			
			$html = '';
			
    		$html .= '<div class="alignleft actions">';
    			
					$html .= '<select name="wpw_auto_poster_post_id" id="wpw_auto_poster_post_id" data-placeholder="' . __( 'Show all post title', 'wpwautoposter' ) . '">';
					
					$html .= '<option value="" ' .  selected( isset( $_GET['wpw_auto_poster_post_id'] ) ? $_GET['wpw_auto_poster_post_id'] : '', '', false ) . '>'.__( 'Show all post title', 'wpwautoposter' ).'</option>';
			
				if ( !empty( $post_ids ) ) {
		
					foreach ( $post_ids as $post_data ) {
						
						if( !empty( $post_data['post_parent'] ) && !in_array( $post_data['post_parent'], $post_parent_ids ) ) {
							
							$parent_id = $post_data['post_parent'];
							$post_parent_ids[] = $parent_id;
							
							$html .= '<option value="' . $parent_id . '" ' . selected( isset( $_GET['wpw_auto_poster_post_id'] ) ? $_GET['wpw_auto_poster_post_id'] : '', $parent_id, false ) . '>' . get_the_title( $parent_id ) . '</option>';
						}
					}
				
				}
					$html .= '</select>';
				
					$html .= '<select name="wpw_auto_poster_social_type" id="wpw_auto_poster_social_type" data-placeholder="' . __( 'Show all social type', 'wpwautoposter' ) . '">';
					
					$html .= '<option value="" ' .  selected( isset( $_GET['wpw_auto_poster_social_type'] ) ? $_GET['wpw_auto_poster_social_type'] : '', '', false ) . '>'.__( 'Show all social type', 'wpwautoposter' ).'</option>';
			
				if ( !empty( $social_types ) ) { // Check social types are not empty
		
					foreach ( $social_types as $social_key => $social_name ) {
							
						$html .= '<option value="' . $social_key . '" ' . selected( isset( $_GET['wpw_auto_poster_social_type'] ) ? $_GET['wpw_auto_poster_social_type'] : '', $social_key, false ) . '>' . $social_name . '</option>';
					}
				
				}
					$html .= '</select>';
				
				//Monthly dropdown for filter
				ob_start();
    			
    			$this->months_dropdown( WPW_AUTO_POSTER_LOGS_POST_TYPE );
    			
    			$html .= ob_get_clean();
    			
    		$html .= '	<input type="submit" value="'.__( 'Filter', 'wpwautoposter' ).'" class="button" id="post-query-submit" name="">';
    		$html .= '</div>';
    		
			echo $html;
    	}
    }
    
    function prepare_items() {
        
        // Get how many records per page to show
        $per_page	= $this->per_page;
       
        // Get All, Hidden, Sortable columns
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

		// Get final column header
        $this->_column_headers = array($columns, $hidden, $sortable);
        
		// Get Data of particular page
		$data_res 	= $this->display_social_posted_logs();
		$data 		= $data_res['data'];		
		
		// Get current page number
        $current_page = $this->get_pagenum();
        
		// Get total count
        $total_items  = $data_res['total'];
        
        // Get page items
        $this->items = $data;
        
		// We also have to register our pagination options & calculations.
        $this->set_pagination_args( array(
									            'total_items' => $total_items,                  //WE have to calculate the total number of items
									            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
									            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
									        ) );
    }
    
}

//Create an instance of our package class...
$WpwAutoPosterPostedLogsListTable = new Wpw_Auto_Poster_Posted_Logs_List();
	
//Fetch, prepare, sort, and filter our data...
$WpwAutoPosterPostedLogsListTable->prepare_items();
		
    	//showing sorting links on the top of the list
    	$WpwAutoPosterPostedLogsListTable->views(); 
    	
		if(isset($_GET['message']) && !empty($_GET['message']) ) { //check message
			
			if( $_GET['message'] == '3' ) { //check message
				
				echo '<div class="updated fade" id="message">
						<p><strong>'.__("Record (s) deleted successfully.",'wpwautoposter').'</strong></p>
					</div>'; 
				
			} 
		}
		
    ?>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="product-filter" method="get" class="wpw-auto-poster-form">
        
    	<!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        
        <!-- Search Title -->
        <?php //$WpwAutoPosterPostedLogsListTable->search_box( __( 'Search', 'wpwautoposter' ), 'wpwautoposter' ); ?>
        
        <!-- Now we can render the completed list table -->
        <?php $WpwAutoPosterPostedLogsListTable->display(); ?>
        
    </form>