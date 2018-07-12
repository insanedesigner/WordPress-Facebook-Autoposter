<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Class
 * 
 * Handles generic Admin functionality and AJAX requests.
 * 
 * @package WPWeb Updater
 * @since 1.0.0
 */
class Wpw_Auto_Poster_Wpweb_Upd_Admin {
	
	public function __construct() {
		
	}	
	
	/**
	 * Add Admin Menu
	 * 
	 * Handles to add admin menus 
	 * 
	 * @package WPWeb Updater
	 * @since 1.0.0
	 */
	public function wpweb_upd_admin_menu() {

		global $submenu;

		$is_wpweb_defined = false;
		foreach( $submenu as $k => $item ){
			foreach( $item as $sm ){
				if ( 'wpweb-upd-helper' == $sm[2] ) {
					$is_wpweb_defined = true;
					break;
				}
			}
		}

		if( !$is_wpweb_defined ) {
			add_dashboard_page( __( 'The WPWeb Updater', 'woovoucher' ), __( 'WPWeb Updater', 'woovoucher' ), 'manage_options', 'wpweb-upd-helper', array( $this, 'wpweb_upd_helper_screen' ) );
		}
	}
	
	/**
	 * Wpweb Helper Page
	 * 
	 * Handles to display wpweb helper page
	 * 
	 * @package WPWeb Updater
	 * @since 1.0.0
	 */
	public function wpweb_upd_helper_screen() {
		
		include_once( WPW_AUTO_POSTER_WPWEB_UPD_ADMIN . '/forms/wpweb-upd-helper.php' );
	}
	
	/**
	 * Save Product License Key
	 * 
	 * Handle to save product license key
	 * 
	 * @package WPWeb Updater
	 * @since 1.0.0
	 */
	public function wpweb_upd_save_products_license() {
		
		if( !empty( $_POST['wpweb_upd_submit'] ) ) {//If click on save button
			
			//$wpwebupd_lickey	= get_option( 'wpwebupd_lickey' );
			$wpwebupd_lickey	= wpweb_all_plugins_purchase_code();
			$wpwebupd_email		= wpweb_all_plugins_purchase_email();
			
			$post_lickey		= $_POST['wpwebupd_lickey'];
			$post_email			= $_POST['wpwebupd_email'];
			
			foreach ( $post_lickey as $plugin_key => $license_key ) {
				$wpwebupd_lickey[$plugin_key]	= $license_key;
			}
			wpweb_save_plugins_purchase_code( $wpwebupd_lickey );
			
			foreach ( $post_email as $plugin_key => $email_key ) {
				$wpwebupd_email[$plugin_key]	= $email_key;
			}
			wpweb_save_plugins_purchase_email( $wpwebupd_email );
			
			wp_redirect( add_query_arg( array( 'message' => '1' ) ) );
		}
		
		if ( isset( $_GET['action'] ) && ( 'wpweb-upd-helper-dismiss' == $_GET['action'] ) && isset( $_GET['nonce'] ) && check_admin_referer( 'wpweb-upd-helper-dismiss', 'nonce' ) ) {
			
			update_site_option( 'wpwebupd_helper_dismiss_activation_notice', true );
			$redirect_url = remove_query_arg( 'action', remove_query_arg( 'nonce', $_SERVER['REQUEST_URI'] ) );
			wp_safe_redirect( $redirect_url );
			exit;
		}
	}
	
	/**
	 * Add Email Field In Request Query Arguents
	 * 
	 * Handle to add email field in request query arguents
	 * 
	 * @package WPWeb Updater
	 * @since 1.0.2
	 */
	public function wpweb_request_args_add_email_option( $queryArgs, $slug, $pluginFile ) {
		
		// purchase plugin email
		$wpwebupd_email		= wpweb_all_plugins_purchase_email();
		
		// get product email
		$email	= isset( $wpwebupd_email[$slug] ) ? $wpwebupd_email[$slug] : '';
		
		if( !empty( $email ) ) { // if email is not empty
			if( is_email( $email ) ) { // if email is correct format
				$queryArgs['email']	= $wpwebupd_email[$slug];
			}
		}
		
		return $queryArgs;
	}
	
	/**
	 * Add Site URL To Remote Request
	 * 
	 * Handle to add site URL to remote request
	 * 
	 * @package WPWeb Updater
	 * @since 1.0.2
	 */
	public function wpweb_request_args_add_site_url( $options, $slug, $pluginFile ) {
		
		$site_url	= site_url();
		$options['cookies']	= array( 'site_url' => $site_url );
		return $options;
	}
	
	/**
	 * Adding Hooks
	 * 
	 * @package WPWeb Updater
	 * @since 1.0.2
	 */
	public function wpweb_upd_admin_scripts( $hook_suffix = '' ) {
		
		$pages_hook_suffix	= array( 'dashboard_page_wpweb-upd-helper', 'index_page_wpweb-upd-helper' );
		
		if( in_array( $hook_suffix, $pages_hook_suffix ) ) {
			
			wp_register_style( 'wpweb-upd-admin-style', WPW_AUTO_POSTER_WPWEB_UPD_URL . 'includes/css/wpweb-upd-style.css', array(), WPW_AUTO_POSTER_VERSION );
			wp_enqueue_style( 'wpweb-upd-admin-style' );
			
			// add js for check code in admin
			wp_register_script( 'wpweb-upd-admin-script', WPW_AUTO_POSTER_WPWEB_UPD_URL . 'includes/js/wpweb-upd-script.js', array( 'jquery' ), WPW_AUTO_POSTER_VERSION );
			wp_enqueue_script( 'wpweb-upd-admin-script' );
		}
	}	
	
	/**
	 * Adding Hooks
	 * 
	 * @package WPWeb Updater
	 * @since 1.0.0
	 */
	public function add_hooks() {				
		
		//add admin menu pages
		$menu_hook = is_multisite() ? 'network_admin_menu' : 'admin_menu';
		add_action ( $menu_hook, array( $this, 'wpweb_upd_admin_menu' ) );
		
		//save wpweb product license key
		add_action( 'admin_init', array( $this, 'wpweb_upd_save_products_license' ) );
		
		// add email field in request query arguents
		add_action( 'wpweb_modify_request_query_arguments', array( $this, 'wpweb_request_args_add_email_option' ), 10, 3 );
		add_action( 'wpweb_modify_request_remote_option', array( $this, 'wpweb_request_args_add_site_url' ), 10, 3 );
		
		//add scripts for add js css for updater admin page
		add_action( 'admin_enqueue_scripts', array( $this, 'wpweb_upd_admin_scripts' ) );
	}
}