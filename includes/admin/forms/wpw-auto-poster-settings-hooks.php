<?php

/*********************** All Hooks Start ***************************/

// add action to add general settings tab 	-  5
// add action to add facebook settings tab 	- 10
// add action to add twitter settings tab 	- 15
// add action to add linkedin settings tab 	- 20
// add action to add tumblr settings tab 	- 25
// add action to add bufferapp settings tab - 40
add_action( 'wpw_auto_poster_settings_panel_tab', 'wpw_auto_poster_general_setting_tab', 	5 	);
add_action( 'wpw_auto_poster_settings_panel_tab', 'wpw_auto_poster_facebook_setting_tab', 	10 	);
add_action( 'wpw_auto_poster_settings_panel_tab', 'wpw_auto_poster_twitter_setting_tab', 	15 	);
add_action( 'wpw_auto_poster_settings_panel_tab', 'wpw_auto_poster_linkedin_setting_tab', 	20 	);
add_action( 'wpw_auto_poster_settings_panel_tab', 'wpw_auto_poster_tumblr_setting_tab', 	25 	);
add_action( 'wpw_auto_poster_settings_panel_tab', 'wpw_auto_poster_bufferapp_setting_tab', 	40 	);
add_action( 'wpw_auto_poster_settings_panel_tab', 'wpw_auto_poster_instagram_setting_tab', 	55 	);
add_action( 'wpw_auto_poster_settings_panel_tab', 'wpw_auto_poster_pinterest_setting_tab', 	60 	);

// add action to add general settings tab content 	-  5
// add action to add facebook settings tab content 	- 10
// add action to add twitter settings tab content 	- 15
// add action to add linkedin settings tab content 	- 20
// add action to add tumblr settings tab content 	- 25
// add action to add bufferapp settings tab content - 40
add_action( 'wpw_auto_poster_settings_panel_tab_content', 'wpw_auto_poster_general_setting_tab_content', 	5 	);
add_action( 'wpw_auto_poster_settings_panel_tab_content', 'wpw_auto_poster_facebook_setting_tab_content', 	10 	);
add_action( 'wpw_auto_poster_settings_panel_tab_content', 'wpw_auto_poster_twitter_setting_tab_content', 	15 	);
add_action( 'wpw_auto_poster_settings_panel_tab_content', 'wpw_auto_poster_linkedin_setting_tab_content', 	20 	);
add_action( 'wpw_auto_poster_settings_panel_tab_content', 'wpw_auto_poster_tumblr_setting_tab_content', 	25 	);
add_action( 'wpw_auto_poster_settings_panel_tab_content', 'wpw_auto_poster_bufferapp_setting_tab_content', 	40 	);
add_action( 'wpw_auto_poster_settings_panel_tab_content', 'wpw_auto_poster_instagram_setting_tab_content', 	55 	);
add_action( 'wpw_auto_poster_settings_panel_tab_content', 'wpw_auto_poster_pinterest_setting_tab_content', 	60 	);



/*********************** All Hooks End ***************************/