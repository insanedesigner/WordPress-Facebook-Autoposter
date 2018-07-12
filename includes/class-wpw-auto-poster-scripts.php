<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Scripts Class
 *
 * Handles adding scripts functionality to the admin pages
 * as well as the front pages.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
class Wpw_Auto_Posting_Scripts {

	public $model;

    public function __construct() {
        global $wpw_auto_poster_model;

		$this->model = $wpw_auto_poster_model;
    }

    /**
     * Enqueuing Styles
     *
     * Loads the required stylesheets for displaying the theme settings page in the WordPress admin section.
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_settings_page_print_styles($hook_suffix) {

        $sap_screen_id = wpw_auto_poster_get_sap_screen_id();

        $pages_hook_suffix = array('post.php', 'post-new.php', 'toplevel_page_wpw-auto-poster-settings', $sap_screen_id . '_page_wpw-auto-poster-posted-logs', $sap_screen_id . '_page_wpw-auto-poster-manage-schedules', 'social-auto-poster_page_wpw-auto-poster-reposter','social-auto-poster_page_wpw-auto-poster-posted-system-logs');

        //Check pages when you needed
        if (in_array($hook_suffix, $pages_hook_suffix)) {

            // loads the required styles for the plugin settings page
            wp_register_style('wpw-auto-poster-admin', WPW_AUTO_POSTER_URL . 'includes/css/wpw-auto-poster-admin.css', array(), WPW_AUTO_POSTER_VERSION);
            wp_enqueue_style('wpw-auto-poster-admin');

            wp_register_style('select2', WPW_AUTO_POSTER_URL . 'includes/css/select2/select2.min.css', array(), WPW_AUTO_POSTER_VERSION);
            wp_enqueue_style('select2');

            // load the required styles for the meta boxes
            wp_enqueue_style(array('thickbox'));

            if ($hook_suffix != 'post.php' && $hook_suffix != 'post-new.php') {

                // load chosen css
                wp_register_style('chosen-css', WPW_AUTO_POSTER_META_URL . '/css/chosen/chosen.css', array(), WPW_AUTO_POSTER_VERSION);
                wp_enqueue_style('chosen-css');
            }

            if( $hook_suffix != 'post.php' || $hook_suffix != 'post-new.php' ){

                wp_enqueue_script('jquery-ui-datepicker');
                wp_enqueue_style('jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
                
                // Js date & time format
                $date_format  = apply_filters( 'wpw_auto_poster_js_date_format', 'yy-mm-dd' );
                $time_format  = 'hh:00';
                //$current_date = current_time( 'Y-m-d H:i' );

                $next_cron    = wp_next_scheduled( 'wpw_auto_poster_scheduled_cron' );
                $current_date = get_date_from_gmt( date('Y-m-d H:i:s', $next_cron) );
                
                // loads the plugin admin script
                wp_register_script('wpw-auto-poster-admin-script', WPW_AUTO_POSTER_URL . 'includes/js/wpw-auto-poster-admin.js', array('jquery-ui-datepicker'), WPW_AUTO_POSTER_VERSION);
                wp_enqueue_script('wpw-auto-poster-admin-script');

                // Localize script
                wp_localize_script('wpw-auto-poster-admin-script', 'WpwAutoPosterAdmin', array(
                    'date_format'   => $date_format,
                    'time_format'   => $time_format,
                    'current_date'  => $current_date
                ));
            }

            if( $hook_suffix != 'post.php' && $hook_suffix != 'post-new.php' ){
                
                wp_register_script('wpw-auto-poster-select-script', WPW_AUTO_POSTER_URL . 'includes/js/select2/select2.min.js', array(), WPW_AUTO_POSTER_VERSION);
                wp_enqueue_script('wpw-auto-poster-select-script');
            }

            // load chosen css
            wp_register_style('chosen-custom', WPW_AUTO_POSTER_META_URL . '/css/chosen/chosen-custom.css', array(), WPW_AUTO_POSTER_VERSION);
            wp_enqueue_style('chosen-custom');
        }
        
        if( $hook_suffix == 'edit-tags.php' || $hook_suffix == 'term.php' ) {
            // load chosen css
            wp_register_style('chosen-css', WPW_AUTO_POSTER_META_URL . '/css/chosen/chosen.css', array(), WPW_AUTO_POSTER_VERSION);
            wp_enqueue_style('chosen-css');
            
            // load chosen css
            wp_register_style('chosen-custom', WPW_AUTO_POSTER_META_URL . '/css/chosen/chosen-custom.css', array(), WPW_AUTO_POSTER_VERSION);
            wp_enqueue_style('chosen-custom');
        }

        //Check Datetime set
        if( $this->model->is_datetime() ) {
	        // Register & Enqueue Timer Picker Style
			wp_register_style( 'wpw-auto-poster-meta-jquery-ui-css', WPW_AUTO_POSTER_META_URL.'/css/datetimepicker/date-time-picker.css', array(), WPW_AUTO_POSTER_VERSION );
			wp_enqueue_style( 'wpw-auto-poster-meta-jquery-ui-css' );
        }
    }

    /**
     * Enqueuing Scripts
     *
     * Loads the JavaScript files required for managing the meta boxes on the theme settings
     * page, which allows users to arrange the boxes to their liking plus all the other java
     * script files needed for the settings page.
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_settings_page_print_scripts($hook_suffix) {

        $sap_screen_id = wpw_auto_poster_get_sap_screen_id();

        $pages_hook_suffix = array('toplevel_page_wpw-auto-poster-settings', $sap_screen_id . '_page_wpw-auto-poster-posted-logs', $sap_screen_id . '_page_wpw-auto-poster-manage-schedules', 'social-auto-poster_page_wpw-auto-poster-reposter');

        //Check pages when you needed
        if (in_array($hook_suffix, $pages_hook_suffix)) {

            global $wp_version;

            // loads the required scripts for the meta boxes
            wp_enqueue_script('common');
            wp_enqueue_script('wp-lists');
            wp_enqueue_script('postbox');
            wp_enqueue_script('jquery');
            wp_enqueue_script('media-upload');
            wp_enqueue_media(); //imp to work with new media uploader wordpress > 3.5
            wp_enqueue_script('thickbox');

            $newui = $wp_version >= '3.5' ? '1' : '0'; //check wp version for showing media uploader

            wp_register_script('wpw-auto-poster-settings', WPW_AUTO_POSTER_URL . 'includes/js/wpw-auto-poster-settings.js', array('jquery'), WPW_AUTO_POSTER_VERSION, true);
            wp_enqueue_script('wpw-auto-poster-settings');
            //localize script
            // Localize script
            wp_localize_script('wpw-auto-poster-settings', 'WpwAutoPosterSettings', array(
                'new_media_ui' 		=> $newui,
                'confirmmsg' 		=> __('Click OK to reset all options. All settings will be lost!', 'wpwautoposter'),
                'deleteconfirmmsg' 	=> __('Are you sure you want to delete?', 'wpwautoposter'),
                'ajaxurl' 			=> admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
                'sel_category_id'	=> !empty($_REQUEST['wpw_auto_poster_cat_id']) ? $_REQUEST['wpw_auto_poster_cat_id'] : '',
                'report_title' => __( 'Social Network Statistics', 'wpwautoposter'),
                'copy_message' => __( 'Copied', 'wpwautoposter'),
                'option_label' => __( 'Select an option', 'wpwautoposter'),
            ));

            // load chosen js
            wp_register_script('chosen', WPW_AUTO_POSTER_META_URL . '/js/chosen/chosen.jquery.js', array('jquery'), WPW_AUTO_POSTER_VERSION, true);
            wp_enqueue_script('chosen');
        }
        
        if( $hook_suffix == 'edit-tags.php' || $hook_suffix == 'term.php' ) {
            // load chosen js
            wp_register_script('chosen', WPW_AUTO_POSTER_META_URL . '/js/chosen/chosen.jquery.js', array('jquery'), WPW_AUTO_POSTER_VERSION, false);            
            wp_enqueue_script('chosen');
        }

        //Check date and time set
        if( $this->model->is_datetime() || ( $hook_suffix == $sap_screen_id . '_page_wpw-auto-poster-manage-schedules' )) {

        	wp_enqueue_script(array('jquery','jquery-ui-core','jquery-ui-datepicker','jquery-ui-slider'));

			// Register & Enqueue Jquery ui slider access script
			wp_register_script( 'wpw-auto-poster-datepicker-slider-script',WPW_AUTO_POSTER_META_URL.'/js/datetimepicker/jquery-ui-slider-Access.js', array(), WPW_AUTO_POSTER_VERSION, true );
			wp_enqueue_script( 'wpw-auto-poster-datepicker-slider-script' );
			// Register & Enqueue date timerpicker addon script
			wp_register_script( 'wpw-auto-poster-datepicker-addon-script',WPW_AUTO_POSTER_META_URL.'/js/datetimepicker/jquery-date-timepicker-addon.js', array('wpw-auto-poster-datepicker-slider-script'), WPW_AUTO_POSTER_VERSION, true );
			wp_enqueue_script( 'wpw-auto-poster-datepicker-addon-script' );
        }

        //Reports
        if ( $hook_suffix == $sap_screen_id . '_page_wpw-auto-poster-posted-logs' ) {
        	wp_enqueue_script( 'wpw-auto-poster-graph-js', 'https://www.gstatic.com/charts/loader.js' );
        }
    }

    /**
     * Loading Additional Java Script
     *
     * Loads the JavaScript required for toggling the meta boxes on the theme settings page.
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function wpw_auto_poster_settings_page_load_scripts() {
        ?>				
        <script type="text/javascript">
            //<![CDATA[
            jQuery(document).ready(function ($) {
                $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                postboxes.add_postbox_toggles('toplevel_page_wpw-auto-poster-settings');
            });
            //]]>
        </script>
        <?php

    }


    /**
     * Add inline Java Script code for google analytics on frontend
     *
     *
     * @package Social Auto Poster
     * @since 2.6.1
    */
    public function wpw_auto_poster_head_print_scripts() {
        global $wpw_auto_poster_options,$wpw_auto_poster_model;

        if( !empty( $wpw_auto_poster_options['enable_google_tracking'] ) && $wpw_auto_poster_options['enable_google_tracking'] == '1'  ) { // if Google Analytics Campaign Tracking enabled

            // if use plugin Use google analytics script and added Google Tracking script code 
            if( !empty( $wpw_auto_poster_options['google_tracking_script'] ) && $wpw_auto_poster_options['google_tracking_script'] == 'yes' && !empty( $wpw_auto_poster_options['google_tracking_code'] ) ) {
                $script_code = $wpw_auto_poster_options['google_tracking_code'];
                
                print htmlspecialchars_decode($script_code); // display script code
            }
        }
    }

    /**
     * Adding Hooks
     *
     * Adding proper hoocks for the scripts.
     *
     * @package Social Auto Poster
     * @since 1.0.0
     */
    public function add_hooks() {

        // adding the admin css for settings page
        add_action('admin_enqueue_scripts', array($this, 'wpw_auto_poster_settings_page_print_styles'));

        //enqueue scripts for setting page
        add_action('admin_enqueue_scripts', array($this, 'wpw_auto_poster_settings_page_print_scripts'));

        // hook to include google analytics script code for tracking 
        add_action( 'wp_head', array($this, 'wpw_auto_poster_head_print_scripts') );
    }

}
