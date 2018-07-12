<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions 
 * 
 * @package WPWeb Updater
 * @since 1.0.0
 */
if( !defined( 'WPW_AUTO_POSTER_WPWEB_UPD_DIR' ) ) {
	define( 'WPW_AUTO_POSTER_WPWEB_UPD_DIR', dirname( __FILE__ ) ); // plugin dir
}
if( !defined( 'WPW_AUTO_POSTER_WPWEB_UPD_URL' ) ) {
	define( 'WPW_AUTO_POSTER_WPWEB_UPD_URL', plugin_dir_url( __FILE__ ) ); // plugin url
}
if( !defined( 'WPW_AUTO_POSTER_WPWEB_UPD_ADMIN' ) ) {
	define( 'WPW_AUTO_POSTER_WPWEB_UPD_ADMIN', WPW_AUTO_POSTER_WPWEB_UPD_DIR . '/includes/admin' ); // plugin admin dir
}

//Include misc functions file
require_once( WPW_AUTO_POSTER_WPWEB_UPD_DIR . '/includes/wpweb-upd-misc-functions.php' );

if( is_admin() ) {
	    
    //Include admin class file
    require_once( WPW_AUTO_POSTER_WPWEB_UPD_DIR . '/includes/admin/class-wpweb-upd-admin.php' );

    $wpweb_upd_admin = new Wpw_Auto_Poster_Wpweb_Upd_Admin();
    $wpweb_upd_admin->add_hooks();
    
	
	include_once( WPW_AUTO_POSTER_WPWEB_UPD_DIR . '/updates/class-plugin-update-checker.php' );	
}