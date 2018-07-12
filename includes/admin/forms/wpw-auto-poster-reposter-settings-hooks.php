<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Settings Hooks
 *
 * The code for the plugins main settings hooks
 *
 * @package Social Auto Poster
 * @since 2.6.9
 */

/*********************** General Settings ***************************/

if( !function_exists( 'wpw_auto_poster_reposter_general_setting_tab' ) ) {

	/**
	 * Display General Setting Tab
	 * 
	 * Handle to display general setting tab
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_general_setting_tab( $selected_tab ) {
		
		$selectedtab = !empty( $selected_tab ) && $selected_tab == 'general' ? ' nav-tab-active' : '';
		?>
			<a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-general" attr-tab="general">
				<img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/wpw-auto-poster-icon.png" width="24" height="24" alt="gn" title="<?php _e( 'General', 'wpwautoposter' ); ?>" />
			</a>
		<?php
	}
}

if( !function_exists( 'wpw_auto_poster_reposter_general_setting_tab_content' ) ) {

	/**
	 * Display General Setting Tab Content
	 * 
	 * Handle to display general setting tab content
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_general_setting_tab_content( $selected_tab ) {
	
		$selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'general' ? ' wpw-auto-poster-selected-tab' : '';
		?>
			<div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-general"> 
					
				<?php
			
				// General Settings
				include( WPW_AUTO_POSTER_ADMIN . '/forms/reposter/wpw-auto-poster-reposter-general-settings.php' );
			
				?>
			
			</div><!--#wpw-auto-poster-reposter-tab-general-->
		<?php
	}
}

/*********************** Facebook Settings ***************************/

if( !function_exists( 'wpw_auto_poster_reposter_facebook_setting_tab' ) ) {

	/**
	 * Display Facebook Setting Tab
	 * 
	 * Handle to display facebook setting tab
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_facebook_setting_tab( $selected_tab ) {
		
		$selectedtab = !empty( $selected_tab ) && $selected_tab == 'facebook' ? ' nav-tab-active' : '';
		?>
			<a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-facebook" attr-tab="facebook">
				<img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/facebook_set.png" width="24" height="24" alt="fb" title="<?php _e( 'Facebook', 'wpwautoposter' ); ?>" />
			</a>
		<?php
	}
}

if( !function_exists( 'wpw_auto_poster_reposter_facebook_setting_tab_content' ) ) {

	/**
	 * Display Facebook Setting Tab Content
	 * 
	 * Handle to display facebook setting tab content
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_facebook_setting_tab_content( $selected_tab ) {

		$selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'facebook' ? ' wpw-auto-poster-selected-tab' : '';
		?>
			<div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-facebook"> 
					
				<?php
			
				// Facebook Settings
				include( WPW_AUTO_POSTER_ADMIN . '/forms/reposter/wpw-auto-poster-reposter-facebook.php' );
			
				?>
			
			</div><!--#wpw-auto-poster-tab-facebook-->
		<?php
	}
}

/*********************** Twitter Settings ***************************/

if( !function_exists( 'wpw_auto_poster_reposter_twitter_setting_tab' ) ) {

	/**
	 * Display Twitter Setting Tab
	 * 
	 * Handle to display twitter setting tab
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_twitter_setting_tab( $selected_tab ) {
		
		$selectedtab = !empty( $selected_tab ) && $selected_tab == 'twitter' ? ' nav-tab-active' : '';
		?>
			<a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-twitter" attr-tab="twitter">
				<img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/twitter_set.png" width="24" height="24" alt="tw" title="<?php _e( 'Twitter', 'wpwautoposter' ); ?>" />
			</a>
		<?php
	}
}

if( !function_exists( 'wpw_auto_poster_reposter_twitter_setting_tab_content' ) ) {

	/**
	 * Display Twitter Setting Tab Content
	 * 
	 * Handle to display twitter setting tab content
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_twitter_setting_tab_content( $selected_tab ) {
	
		$selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'twitter' ? ' wpw-auto-poster-selected-tab' : '';
		?>
			<div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-twitter"> 
					
				<?php
			
				// Twitter Settings
				include( WPW_AUTO_POSTER_ADMIN . '/forms/reposter/wpw-auto-poster-reposter-twitter.php' );
			
				?>
			
			</div><!--#wpw-auto-poster-reposter-tab-twitter-->
		<?php
	}
}

/*********************** LinkedIn Settings ***************************/

if( !function_exists( 'wpw_auto_poster_reposter_linkedin_setting_tab' ) ) {

	/**
	 * Display LinkedIn Setting Tab
	 * 
	 * Handle to display linkedin setting tab
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_linkedin_setting_tab( $selected_tab ) {
		
		$selectedtab = !empty( $selected_tab ) && $selected_tab == 'linkedin' ? ' nav-tab-active' : '';
		?>
			<a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-linkedin" attr-tab="linkedin">
				<img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/linkedin_set.png" width="24" height="24" alt="li" title="<?php _e( 'LinkedIn', 'wpwautoposter' ); ?>" />
			</a>
		<?php
	}
}

if( !function_exists( 'wpw_auto_poster_reposter_linkedin_setting_tab_content' ) ) {

	/**
	 * Display LinkedIn Setting Tab Content
	 * 
	 * Handle to display linkedin setting tab content
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_linkedin_setting_tab_content( $selected_tab ) {
	
		$selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'linkedin' ? ' wpw-auto-poster-selected-tab' : '';
		?>
			<div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-linkedin"> 
					
				<?php
			
				// LinkedIn Settings
				include( WPW_AUTO_POSTER_ADMIN . '/forms/reposter/wpw-auto-poster-reposter-linkedin.php' );
			
				?>
			
			</div><!--#wpw-auto-poster-reposter-tab-linkedin-->
		<?php
	}
}

/*********************** Tumblr Settings ***************************/

if( !function_exists( 'wpw_auto_poster_reposter_tumblr_setting_tab' ) ) {

	/**
	 * Display Tumblr Setting Tab
	 * 
	 * Handle to display tumblr setting tab
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_tumblr_setting_tab( $selected_tab ) {
		
		$selectedtab = !empty( $selected_tab ) && $selected_tab == 'tumblr' ? ' nav-tab-active' : '';
		?>
			<a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-tumblr" attr-tab="tumblr">
				<img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/tumblr_set.png" width="24" height="24" alt="tb" title="<?php _e( 'Tumblr', 'wpwautoposter' ); ?>" />
			</a>
		<?php
	}
}

if( !function_exists( 'wpw_auto_poster_reposter_tumblr_setting_tab_content' ) ) {

	/**
	 * Display Tumblr Setting Tab Content
	 * 
	 * Handle to display tumblr setting tab content
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_tumblr_setting_tab_content( $selected_tab ) {
	
		$selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'tumblr' ? ' wpw-auto-poster-selected-tab' : '';
		?>
			<div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-tumblr"> 
					
				<?php
			
				// Tumblr Settings
				include( WPW_AUTO_POSTER_ADMIN . '/forms/reposter/wpw-auto-poster-reposter-tumblr.php' );
			
				?>
			
			</div><!--#wpw-auto-poster-reposter-tab-tumblr-->
		<?php
	}
}

/*********************** BufferApp Settings ***************************/

if( !function_exists( 'wpw_auto_poster_reposter_bufferapp_setting_tab' ) ) {

	/**
	 * Display BufferApp Setting Tab
	 * 
	 * Handle to display bufferapp setting tab
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_bufferapp_setting_tab( $selected_tab ) {
		
		$selectedtab = !empty( $selected_tab ) && $selected_tab == 'bufferapp' ? ' nav-tab-active' : '';
		?>
			<a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-bufferapp" attr-tab="bufferapp">
				<img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/bufferapp_set.png" width="24" height="24" alt="ba" title="<?php _e( 'BufferApp', 'wpwautoposter' ); ?>" />
			</a>
		<?php
	}
}

if( !function_exists( 'wpw_auto_poster_reposter_bufferapp_setting_tab_content' ) ) {

	/**
	 * Display BufferApp Setting Tab Content
	 * 
	 * Handle to display bufferapp setting tab content
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_bufferapp_setting_tab_content( $selected_tab ) {
	
		$selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'bufferapp' ? ' wpw-auto-poster-selected-tab' : '';
		?>
			<div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-bufferapp"> 
					
				<?php
			
				// BufferApp Settings
				include( WPW_AUTO_POSTER_ADMIN . '/forms/reposter/wpw-auto-poster-reposter-bufferapp.php' );
			
				?>
			
			</div><!--#wpw-auto-poster-reposter-tab-bufferapp-->
		<?php
	}
}

/*********************** Instagram Settings ***************************/

if( !function_exists( 'wpw_auto_poster_reposter_instagram_setting_tab' ) ) {

	/**
	 * Display Instagram Setting Tab
	 * 
	 * Handle to display instagram setting tab
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_instagram_setting_tab( $selected_tab ) {
		
		$selectedtab = !empty( $selected_tab ) && $selected_tab == 'instagram' ? ' nav-tab-active' : '';
		?>
			<a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-instagram" attr-tab="instagram">
				<img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/instagram_set.png" width="24" height="24" alt="ins" title="<?php _e( 'Instagram', 'wpwautoposter' ); ?>" />
			</a>
		<?php
	}
}

if( !function_exists( 'wpw_auto_poster_reposter_instagram_setting_tab_content' ) ) {

	/**
	 * Display Instagram Setting Tab Content
	 * 
	 * Handle to display instagram setting tab content
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_instagram_setting_tab_content( $selected_tab ) {
	
		$selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'instagram' ? ' wpw-auto-poster-selected-tab' : '';
		?>
			<div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-instagram"> 
					
				<?php
			
				// Instagram Settings
				include( WPW_AUTO_POSTER_ADMIN . '/forms/reposter/wpw-auto-poster-reposter-instagram.php' );
			
				?>
			
			</div><!--#wpw-auto-poster-reposter-tab-instagram-->
		<?php
	}
}

/*********************** Pinterest Settings ***************************/

if( !function_exists( 'wpw_auto_poster_reposter_pinterest_setting_tab' ) ) {

	/**
	 * Display Pinterest Setting Tab
	 * 
	 * Handle to display pinterest setting tab
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_pinterest_setting_tab( $selected_tab ) {
		
		$selectedtab = !empty( $selected_tab ) && $selected_tab == 'pinterest' ? ' nav-tab-active' : '';
		?>
			<a class="nav-tab <?php echo $selectedtab; ?>" href="#wpw-auto-poster-tab-pinterest" attr-tab="pinterest">
				<img src="<?php echo WPW_AUTO_POSTER_URL; ?>includes/images/pinterest_set.png" width="24" height="24" alt="ins" title="<?php _e( 'Pinterest', 'wpwautoposter' ); ?>" />
			</a>
		<?php
	}
}

if( !function_exists( 'wpw_auto_poster_reposter_pinterest_setting_tab_content' ) ) {

	/**
	 * Display Pinterest Setting Tab Content
	 * 
	 * Handle to display pinterest setting tab content
	 *
	 * @package Social Auto Poster
	 * @since 2.6.9
	 */
	function wpw_auto_poster_reposter_pinterest_setting_tab_content( $selected_tab ) {
	
		$selectedtabcontent = !empty( $selected_tab ) && $selected_tab == 'pinterest' ? ' wpw-auto-poster-selected-tab' : '';
		?>
			<div class="wpw-auto-poster-tab-content <?php echo $selectedtabcontent; ?>" id="wpw-auto-poster-tab-pinterest"> 
					
				<?php
			
				// Instagram Settings
				include( WPW_AUTO_POSTER_ADMIN . '/forms/reposter/wpw-auto-poster-reposter-pinterest.php' );
			
				?>
			
			</div><!--#wpw-auto-poster-reposter-tab-pinterest-->
		<?php
	}
}

/*********************** All Hooks Start ***************************/

// add action to add general settings tab 	-  5
// add action to add facebook settings tab 	- 10
// add action to add twitter settings tab 	- 15
// add action to add linkedin settings tab 	- 20
// add action to add tumblr settings tab 	- 25
// add action to add bufferapp settings tab - 40
add_action( 'wpw_auto_poster_reposter_settings_panel_tab', 'wpw_auto_poster_reposter_general_setting_tab', 	5 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab', 'wpw_auto_poster_reposter_facebook_setting_tab', 	10 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab', 'wpw_auto_poster_reposter_twitter_setting_tab', 	15 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab', 'wpw_auto_poster_reposter_linkedin_setting_tab', 	20 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab', 'wpw_auto_poster_reposter_tumblr_setting_tab', 	25 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab', 'wpw_auto_poster_reposter_bufferapp_setting_tab', 	40 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab', 'wpw_auto_poster_reposter_instagram_setting_tab', 	55 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab', 'wpw_auto_poster_reposter_pinterest_setting_tab', 	60 	);

// add action to add general settings tab content 	-  5
// add action to add facebook settings tab content 	- 10
// add action to add twitter settings tab content 	- 15
// add action to add linkedin settings tab content 	- 20
// add action to add tumblr settings tab content 	- 25
// add action to add bufferapp settings tab content - 40
add_action( 'wpw_auto_poster_reposter_settings_panel_tab_content', 'wpw_auto_poster_reposter_general_setting_tab_content', 	5 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab_content', 'wpw_auto_poster_reposter_facebook_setting_tab_content', 	10 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab_content', 'wpw_auto_poster_reposter_twitter_setting_tab_content', 	15 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab_content', 'wpw_auto_poster_reposter_linkedin_setting_tab_content', 	20 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab_content', 'wpw_auto_poster_reposter_tumblr_setting_tab_content', 	25 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab_content', 'wpw_auto_poster_reposter_bufferapp_setting_tab_content', 	40 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab_content', 'wpw_auto_poster_reposter_instagram_setting_tab_content', 	55 	);
add_action( 'wpw_auto_poster_reposter_settings_panel_tab_content', 'wpw_auto_poster_reposter_pinterest_setting_tab_content', 	60 	);



/*********************** All Hooks End ***************************/