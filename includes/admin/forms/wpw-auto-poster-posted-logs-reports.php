<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Posting Logs Report
 *
 * The html markup for the report.
 *
 * @package Social Auto Poster
 * @since 2.6.0
 */

global $wpw_auto_poster_model;
?>
<div class="wpw-auto-logs-graph-wrap">
	<div class="wpw-auto-logs-graph-filter-wrap">
 		<ul class="wpw-auto-filter-btn-wrap">
 			<li class="wpw-auto-graph-social-type">
 				<?php 
				$social_types = $wpw_auto_poster_model->wpw_auto_poster_get_social_type_name(); ?>
				<select name="wpw_auto_graph_social_type" id="wpw_auto_graph_social_type" data-placeholder="<?php _e( 'Show all social type', 'wpwautoposter' ); ?>">
					<option value=""><?php _e( 'Show all social type', 'wpwautoposter' ); ?></option>
					<?php
						if ( !empty( $social_types ) ) { // Check social types are not empty
							foreach ( $social_types as $social_key => $social_name ) {
								echo '<option value="' . $social_key . '">' . $social_name . '</option>';
							}
						} ?>
				</select>
 			</li>
			<li>
			    <input type="radio" class="wpw_auto_filter_type" id="current_year" name="wpw_auto_filter_type" value="current_year">
  				<label for="current_year"><?php _e( 'Year', 'wpwautoposter');?></label>
			</li>
			<li>
			    <input type="radio" checked="checked" class="wpw_auto_filter_type" id="current_month" name="wpw_auto_filter_type" value="current_month">
  				<label for="current_month"><?php _e( 'This Month', 'wpwautoposter');?></label>
			</li>
			<li>
			    <input type="radio" class="wpw_auto_filter_type" id="last_7days" name="wpw_auto_filter_type" value="last_7days">
  				<label for="last_7days"><?php _e( 'Last 7 days', 'wpwautoposter');?></label>
			</li>
			<li>
			    <input type="radio" class="wpw_auto_filter_type" id="custom" name="wpw_auto_filter_type" value="custom">
  				<label for="custom"><?php _e( 'Custom', 'wpwautoposter');?></label>
			</li>
		</ul>
		<div class="wp-auto-custom-wrap-main">
			<div class="wp-auto-custom-wrap">
				
				<input type="text" name="wpw_auto_graph_start_date" id="wpw_auto_graph_start_date" class="wpw-auto-datepicker" placeholder="<?php _e( 'From Date', 'wpwautoposter') ?>">
				<input type="text" name="wpw_auto_graph_end_date" id="wpw_auto_graph_end_date" class="wpw-auto-datepicker" placeholder="<?php _e( 'To Date', 'wpwautoposter') ?>">
				<button type="button" class="button wpw_auto_graph_filter" name="wpw_auto_graph_filter"><?php _e('Filter', 'wpwautoposter')?></button>
			</div>
		</div>
	</div>
	<div id="wpw-auto-logs-graph"></div>
	<span class="wpw-auto-loader-wrap">
		<div class="wpw-auto-loader-sub">
			<div class="wpw-auto-loader-img"></div>
		</div>
	</span>
</div>