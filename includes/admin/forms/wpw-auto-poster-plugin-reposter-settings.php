<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Settings Page
 *
 * The code for the plugins main settings page
 *
 * @package Social Auto Poster
 * @since 2.6.9
 */
 
global $wpw_auto_poster_reposter_options, $wpw_auto_poster_model, $wpw_auto_poster_message_stack;

//model class
$model = $wpw_auto_poster_model;

//message stack class
$message = $wpw_auto_poster_message_stack;
 
?>
<div class="wrap">

	<!-- Social Auto Poster logo -->
	<img src="<?php echo WPW_AUTO_POSTER_IMG_URL . '/wpw-auto-poster-logo.png'; ?>" class="wpw-auto-poster-logo" alt="<?php _e( 'Logo', 'wpwautoposter' ) ?>" />
		
	<!-- plugin name -->
	<h2><?php esc_attr_e( 'Reposter Settings', 'wpwautoposter' ); ?></h2><br />
	
	<!-- settings reset -->
	<?php
		if( isset( $_POST['wpw_auto_posting_reposter_reset_settings'] ) && $_POST['wpw_auto_posting_reposter_reset_settings'] == __( 'Reset All Settings', 'wpwautoposter' ) ) {

			// set default settings
			wpw_auto_posting_reposter_default_settings();

			echo '<div id="message" class="updated fade" style="width:95%"><p><strong>' . __( 'All Settings Reset Successfully.', 'wpwautoposter' ) . '</strong></p></div>'; 
		}
	?>
		
	<!-- settings updated message -->
	<?php 
		if( isset( $_GET['settings-updated'] ) ) {

			echo '<div id="message" class="updated fade"><p><strong>' . __( 'Changes Saved.', 'wpwautoposter' ) . '</strong></p></div>'; 
		}
	?>
	
	<?php
		echo apply_filters ( 
							 'wpweb_fb_settings_submit_button', 
							 '<form method="post" action="">
									<div class="wpw-auto-poster-posting-reset-setting">
								        <input type="submit" class="button-primary wpw-auto-poster-reset-button" id="wpw_auto_posting_reposter_reset_settings" name="wpw_auto_posting_reposter_reset_settings" value="' . __( 'Reset All Settings', 'wpwautoposter' ) . '" />
								     </div>
								</form>'
							);
	?>
	
	<!-- beginning of the plugin options form -->
	<form id="wpw_auto_poster_setting" method="post" action="options.php">
		
		<?php settings_fields( 'wpw_auto_poster_plugin_reposter_options' ); ?>
		<?php $wpw_auto_poster_reposter_options = get_option( 'wpw_auto_poster_reposter_options' ); ?>
			
		<!-- beginning of the left meta box section -->
		<div class="content">
				
			<?php
				
				/**
				 * Settings Boxes
				 *
				 * Including all the different settings boxes for the plugin options.
				 *
				 * @package Social Auto Poster
				 * @since 1.0.0
				 */
				
				$selected_tab = 'general';
				
				if( $message->size( 'poster-selected-tab' ) > 0 ) { //make tab selected 
					//$selected_tab = $message->output( 'poster-selected-tab' );
					$selected_tab = $message->messages[0]['text'];
					
				}
				if( empty( $selected_tab ) ){
					$selected_tab = 'general';
				}

			?>
			
			<h2 class="nav-tab-wrapper wpw-auto-poster-h2">
			
				<?php do_action( 'wpw_auto_poster_reposter_settings_panel_tab', $selected_tab ) ?>
			
			</h2><!--nav-tab-wrapper-->
			<input type="hidden" id="wpw_auto_poster_selected_tab" name="wpw_auto_poster_reposter_options[selected_tab]" value="<?php echo $selected_tab;?>"/>
			<div class="wpw-auto-poster-content">
			
				<?php do_action( 'wpw_auto_poster_reposter_settings_panel_tab_content', $selected_tab ) ?>
				
			</div>
			
		</div><!-- .content -->
			
	</form><!-- end of plugin options form -->

</div><!-- .wrap -->