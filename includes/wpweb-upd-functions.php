<?php

/**
 * Load installer for the WPWeb Updater.
 * @return $api Object
 */
if ( ! class_exists( 'Wpweb_Upd_Admin' ) && ! function_exists( 'wpweb_updater_install' ) ) {
	
	function wpweb_updater_install( $api, $action, $args ) {
		
		$download_url = 'https://s3.amazonaws.com/wpweb-plugins/Plugins/WPWUPD/wpweb-updater.zip';
		
		if ( 'plugin_information' != $action ||
			false !== $api ||
			! isset( $args->slug ) ||
			'wpweb-updater' != $args->slug
		) return $api;
		
		$api				= new stdClass();
		$api->name			= 'WPWeb Updater';
		$api->version		= '1.0.0';
		$api->download_link	= esc_url( $download_url );
		
		return $api;
	}
	
	add_filter( 'plugins_api', 'wpweb_updater_install', 10, 3 );
}
