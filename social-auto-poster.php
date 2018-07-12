<?php

/**
 * Plugin Name: Social Auto Poster
 * Plugin URI: http://www.wpweb.co.in/
 * Description: Social Auto Poster lets you automatically post all your content to several different social networks.
 * Version: 2.8.2
 * Author: WPWeb
 * Author URI: http://www.wpweb.co.in/
 * Text Domain: wpwautoposter
 * Domain Path: languages
 * 
 * @package Social Auto Poster
 * @category Core
 * @author WPWeb
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Basic Plugin Definitions 
 * 
 * @package Social Auto Poster
 * @since 1.0.0
 */
if (!defined('WPW_AUTO_POSTER_VERSION')) {
    define('WPW_AUTO_POSTER_VERSION', '2.8.2'); //version of plugin
}
if (!defined('wpwautoposterlevel')) {
    //specify the user's role capabilites who can access this plugins settings in backend
    //for more informatioon please check  http://codex.wordpress.org/Roles_and_Capabilities
    define('wpwautoposterlevel', 'manage_options'); //administrator role can use this plugin
}
if (!defined('WPW_AUTO_POSTER_DIR')) {
    define('WPW_AUTO_POSTER_DIR', dirname(__FILE__)); // plugin dir
}
if (!defined('WPW_AUTO_POSTER_URL')) {
    define('WPW_AUTO_POSTER_URL', plugin_dir_url(__FILE__)); // plugin url
}
if (!defined('WPW_AUTO_POSTER_IMG_URL')) {
    define('WPW_AUTO_POSTER_IMG_URL', WPW_AUTO_POSTER_URL . 'includes/images'); // plugin image url
}

if (!defined('WPW_AUTO_POSTER_ADMIN')) {
    define('WPW_AUTO_POSTER_ADMIN', WPW_AUTO_POSTER_DIR . '/includes/admin'); // plugin admin dir
}
if (!defined('WPW_AUTO_POSTER_META_DIR')) {
    define('WPW_AUTO_POSTER_META_DIR', WPW_AUTO_POSTER_DIR . '/includes/meta-boxes'); // path to meta boxes
}
if (!defined('WPW_AUTO_POSTER_META_URL')) {
    define('WPW_AUTO_POSTER_META_URL', WPW_AUTO_POSTER_URL . 'includes/meta-boxes'); // path to meta boxes
}
if (!defined('WPW_AUTO_POSTER_SOCIAL_DIR')) {
    define('WPW_AUTO_POSTER_SOCIAL_DIR', WPW_AUTO_POSTER_DIR . '/includes/social/libraries'); // path to meta boxes
}
if (!defined('WPW_AUTO_POSTER_TITLE_PREFIX')) {
    define('WPW_AUTO_POSTER_TITLE_PREFIX', 'WPWeb');
}
if (!defined('WPW_AUTO_POSTER_META_PREFIX')) {
    define('WPW_AUTO_POSTER_META_PREFIX', '_wpweb_'); //metabox prefix
}
if (!defined('WPW_AUTO_POSTER_LOGS_POST_TYPE')) {
    define('WPW_AUTO_POSTER_LOGS_POST_TYPE', 'wpwautoposterlogs'); //social posting logs post type
}
if (!defined('WPW_AUTO_POSTER_LOG_DIR')) {
    define('WPW_AUTO_POSTER_LOG_DIR', ABSPATH . 'sap-logs/');
}
if (!defined('WPW_AUTO_POSTER_PLUGIN_KEY')) {
    define('WPW_AUTO_POSTER_PLUGIN_KEY', 'sap');
}
if (!defined('WPW_AUTO_POSTER_BASENAME')) {
    define('WPW_AUTO_POSTER_BASENAME', basename(WPW_AUTO_POSTER_DIR)); // base name
}
if (!defined('WPW_AUTO_POSTER_SCHEDULE_CUSTOM_DEFAULT_MINUTE')) {
    define('WPW_AUTO_POSTER_SCHEDULE_CUSTOM_DEFAULT_MINUTE', 10 ); // Default custom schedule minutes
}
// added since 2.6.0
if (!defined('WPW_AUTO_POSTER_UTM_SOURCE')) {
    define('WPW_AUTO_POSTER_UTM_SOURCE', 'SocialAutoPoster' ); // Google tracking source name
}
// added since 2.6.0
if (!defined('WPW_AUTO_POSTER_UTM_MEDIUM')) {
    define('WPW_AUTO_POSTER_UTM_MEDIUM', 'Social' ); // Google tracking medium name
}

// added since 2.7.6
if (!defined('WPW_AUTO_POSTER_FB_API_VERSION')) {
    define('WPW_AUTO_POSTER_FB_API_VERSION', '2.9' ); //  FACEBOOK REST API CLASS
}

$upload_dir     = wp_upload_dir();
$upload_path    = isset( $upload_dir['basedir'] ) ? $upload_dir['basedir'].'/' : ABSPATH;
$upload_url     = isset( $upload_dir['baseurl'] ) ? $upload_dir['baseurl'] : site_url();

// SAP upload dir for external images
if( !defined( 'WPW_AUTO_POSTER_SAP_UPLOADS_DIR' ) ) {
    define( 'WPW_AUTO_POSTER_SAP_UPLOADS_DIR' , $upload_path . 'sap_uploads/' ); // external image upload dir
}


// Required Wpweb updater functions file
if (!function_exists('wpweb_updater_install')) {
    require_once( 'includes/wpweb-upd-functions.php' );
}

/**
 * Re read all options to make it wpml compatible
 *
 * @package WooCommerce - Social Login
 * @since 1.3.0
 */
function wpw_auto_poster_loaded_option() {

    // Re-read settings because read plugin default option to Make it WPML Compatible
    global $wpw_auto_poster_options;
    $wpw_auto_poster_options = get_option('wpw_auto_poster_options');
}

//add action to load plugin
add_action('plugins_loaded', 'wpw_auto_poster_loaded_option');

/**
 * Load Text Domain
 * 
 * This gets the plugin ready for translation.
 * 
 * @package Social Auto Poster
 * @since 1.7.5
 */
function wpw_auto_poster_plugins_loaded() {

    // Set filter for plugin's languages directory
    $wpw_auto_poster_lang_dir = dirname(plugin_basename(__FILE__)) . '/languages/';
    $wpw_auto_poster_lang_dir = apply_filters('wpw_auto_poster_languages_directory', $wpw_auto_poster_lang_dir);

    // Traditional WordPress plugin locale filter
    $locale = apply_filters('plugin_locale', get_locale(), 'wpwautoposter');
    $mofile = sprintf('%1$s-%2$s.mo', 'wpwautoposter', $locale);

    // Setup paths to current locale file
    $mofile_local = $wpw_auto_poster_lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/' . WPW_AUTO_POSTER_BASENAME . '/' . $mofile;

    if (file_exists($mofile_global)) { // Look in global /wp-content/languages/social-auto-poster folder
        load_textdomain('wpwautoposter', $mofile_global);
    } elseif (file_exists($mofile_local)) { // Look in local /wp-content/plugins/social-auto-poster/languages/ folder
        load_textdomain('wpwautoposter', $mofile_local);
    } else { // Load the default language files
        load_plugin_textdomain('wpwautoposter', false, $wpw_auto_poster_lang_dir);
    }
}

add_action('plugins_loaded', 'wpw_auto_poster_plugins_loaded');

/**
 * Activation Hook
 *
 * Register plugin activation hook.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
register_activation_hook(__FILE__, 'wpw_auto_poster_install');

/**
 * Plugin Setup (On Activation)
 *
 * Does the initial setup, creates tables in the database and
 * stest default values for the plugin options.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
function wpw_auto_poster_install() {

    global $wpdb;

    // Cron jobs
    wp_clear_scheduled_hook('wpw_auto_poster_scheduled_cron');

    //get plugin options from database
    $wpw_auto_poster_options = get_option('wpw_auto_poster_options');
    $wpw_auto_poster_reposter_options   = get_option('wpw_auto_poster_reposter_options');


    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //check auto poster options is empty or not
    if (empty($wpw_auto_poster_options)) {

        //set default settings of social auto poster
        wpw_auto_posting_default_settings();

        //update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.0');
    }

    //check set option for plugin is set 1.0
    if ($wpw_auto_poster_set_option == '1.0') {

        $udpopt = false;

        if (!isset($wpw_auto_poster_options['enable_logs'])) { //check enable logs is not set
            $enable_logs = array('enable_logs' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $enable_logs);
            $udpopt = true;
        }
        //check url shortener facebook
        if (!isset($wpw_auto_poster_options['fb_url_shortener'])) {
            $fb_url_shortener = array('fb_url_shortener' => 'wordpress');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $fb_url_shortener);
            $udpopt = true;
        }

        //check url facebook bitly user name
        if (!isset($wpw_auto_poster_options['fb_bitly_username'])) {
            $fb_bitly_username = array('fb_bitly_username' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $fb_bitly_username);
            $udpopt = true;
        }

        //check url facebook bitly api key
        if (!isset($wpw_auto_poster_options['fb_bitly_api_key'])) {
            $fb_bitly_api_key = array('fb_bitly_api_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $fb_bitly_api_key);
            $udpopt = true;
        }

        //check url shortener twitter
        if (!isset($wpw_auto_poster_options['tw_url_shortener'])) {
            $tw_url_shortener = array('tw_url_shortener' => 'wordpress');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tw_url_shortener);
            $udpopt = true;
        }

        //check url twitter bitly user name
        if (!isset($wpw_auto_poster_options['tw_bitly_username'])) {
            $tw_bitly_username = array('tw_bitly_username' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tw_bitly_username);
            $udpopt = true;
        }

        //check url twitter bitly api key
        if (!isset($wpw_auto_poster_options['tw_bitly_api_key'])) {
            $tw_bitly_api_key = array('tw_bitly_api_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tw_bitly_api_key);
            $udpopt = true;
        }

        //check url shortener linkedin
        if (!isset($wpw_auto_poster_options['li_url_shortener'])) {
            $li_url_shortener = array('li_url_shortener' => 'wordpress');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $li_url_shortener);
            $udpopt = true;
        }

        //check url linkedin bitly user name
        if (!isset($wpw_auto_poster_options['li_bitly_username'])) {
            $li_bitly_username = array('li_bitly_username' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $li_bitly_username);
            $udpopt = true;
        }

        //check url linkedin bitly api key
        if (!isset($wpw_auto_poster_options['li_bitly_api_key'])) {
            $li_bitly_api_key = array('li_bitly_api_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $li_bitly_api_key);
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated 				
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        //update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.0.1');
    } //check plugin set option value is 1.0
    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //check set option for plugin is set 1.0.1
    if ($wpw_auto_poster_set_option == '1.0.1') {

        $udpopt = false;

        //Tumblr settings
        if (!isset($wpw_auto_poster_options['enable_tumblr'])) { //check enable tumblr is not set
            $enable_tumblr = array('enable_tumblr' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $enable_tumblr);
            $udpopt = true;
        }
        //check enable tumblr for is not set
        if (!isset($wpw_auto_poster_options['enable_tumblr_for'])) {
            $enable_tumblr_for = array('enable_tumblr_for' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $enable_tumblr_for);
            $udpopt = true;
        }
        //check content type of tumblr is not set
        if (!isset($wpw_auto_poster_options['tumblr_content_type'])) {
            $tumblr_content_type = array('tumblr_content_type' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tumblr_content_type);
            $udpopt = true;
        }
        //check url shortener tumblr
        if (!isset($wpw_auto_poster_options['tb_url_shortener'])) {
            $tb_url_shortener = array('tb_url_shortener' => 'wordpress');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tb_url_shortener);
            $udpopt = true;
        }
        //check url tumblr bitly user name
        if (!isset($wpw_auto_poster_options['tb_bitly_username'])) {
            $tb_bitly_username = array('tb_bitly_username' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tb_bitly_username);
            $udpopt = true;
        }
        //check url tumblr bitly api key
        if (!isset($wpw_auto_poster_options['tb_bitly_api_key'])) {
            $tb_bitly_api_key = array('tb_bitly_api_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tb_bitly_api_key);
            $udpopt = true;
        }
        //check consumer key is not set
        if (!isset($wpw_auto_poster_options['tumblr_consumer_key'])) {
            $tumblr_consumer_key = array('tumblr_consumer_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tumblr_consumer_key);
            $udpopt = true;
        }
        //check consumer secret is not set
        if (!isset($wpw_auto_poster_options['tumblr_consumer_secret'])) {
            $tumblr_consumer_secret = array('tumblr_consumer_secret' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tumblr_consumer_secret);
            $udpopt = true;
        }

        
        //bufferapp settings
        if (!isset($wpw_auto_poster_options['enable_bufferapp'])) { //check enable bufferapp is not set
            $enable_bufferapp = array('enable_bufferapp' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $enable_bufferapp);
            $udpopt = true;
        }
        //check enable bufferapp for is not set
        if (!isset($wpw_auto_poster_options['enable_bufferapp_for'])) {
            $enable_bufferapp_for = array('enable_bufferapp_for' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $enable_bufferapp_for);
            $udpopt = true;
        }
        //check bufferapp url shortner is not set 
        if (!isset($wpw_auto_poster_options['ba_url_shortener'])) {
            $ba_url_shortener = array('ba_url_shortener' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ba_url_shortener);
            $udpopt = true;
        }
        //check bufferapp bitly username is not set 
        if (!isset($wpw_auto_poster_options['ba_bitly_username'])) {
            $ba_bitly_username = array('ba_bitly_username' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ba_bitly_username);
            $udpopt = true;
        }
        //check bufferapp bitly api key is not set 
        if (!isset($wpw_auto_poster_options['ba_bitly_api_key'])) {
            $ba_bitly_api_key = array('ba_bitly_api_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ba_bitly_api_key);
            $udpopt = true;
        }
        //check bufferapp client secter is set or not
        if (!isset($wpw_auto_poster_options['bufferapp_client_secret'])) {
            $bufferapp_client_secret = array('bufferapp_client_secret' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $bufferapp_client_secret);
            $udpopt = true;
        }
        //check bufferapp clientid set or not
        if (!isset($wpw_auto_poster_options['bufferapp_client_id'])) {
            $bufferapp_client_id = array('bufferapp_client_id' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $bufferapp_client_id);
            $udpopt = true;
        }
        //check bufferapp post image
        if (!isset($wpw_auto_poster_options['ba_post_img'])) {
            $ba_post_img = array('ba_post_img' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ba_post_img);
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated 				
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        //update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.0.2');
    } //check plugin set option value is 1.0.1
    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //check set option for plugin is set 1.0.2
    if ($wpw_auto_poster_set_option == '1.0.2') {

        $udpopt = false;

        if (!isset($wpw_auto_poster_options['enable_posting_logs'])) { //check enable posting logs is not set
            $enable_posting_logs = array('enable_posting_logs' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $enable_posting_logs);
            $udpopt = true;
        }

        if (isset($wpw_auto_poster_options['twitter_consumer_key']) && isset($wpw_auto_poster_options['twitter_consumer_secret']) && isset($wpw_auto_poster_options['twitter_oauth_token']) && isset($wpw_auto_poster_options['twitter_oauth_secret'])) { //check twitter consumer key is set
            //Twitter Posting Class
            require_once( WPW_AUTO_POSTER_DIR . '/includes/social/class-wpw-auto-poster-tw-posting.php' ); // twitter posting class
            $wpw_auto_poster_tw_posting = new Wpw_Auto_Poster_TW_Posting();

            $twitter_keys_data = array(
                'consumer_key' => $wpw_auto_poster_options['twitter_consumer_key'],
                'consumer_secret' => $wpw_auto_poster_options['twitter_consumer_secret'],
                'oauth_token' => $wpw_auto_poster_options['twitter_oauth_token'],
                'oauth_secret' => $wpw_auto_poster_options['twitter_oauth_secret'],
            );

            $twitter_keys = array('twitter_keys' => array($twitter_keys_data));
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $twitter_keys);

            $tw_account_details = array();
            $user_profile_data = $wpw_auto_poster_tw_posting->wpw_auto_poster_get_user_data($twitter_keys_data['consumer_key'], $twitter_keys_data['consumer_secret'], $twitter_keys_data['oauth_token'], $twitter_keys_data['oauth_secret']);
            if (!empty($user_profile_data)) { // Check user data are not empty
                if (isset($user_profile_data->name) && !empty($user_profile_data->name)) { // Check user name is not empty
                    $tw_account_details['1'] = $user_profile_data->name;

                    $types = get_post_types(array('public' => true), 'objects');
                    $types = is_array($types) ? $types : array();

                    foreach ($types as $type) {

                        if (!is_object($type))
                            continue;

                        $tw_type_user = array('tw_type_' . $type->name . '_user' => array('1'));
                        $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tw_type_user);
                    }
                }
            }

            //Update twitter acoount details
            update_option('wpw_auto_poster_tw_account_details', $tw_account_details);

            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated 				
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        //update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.0.3');
    } //check plugin set option value is 1.0.2
    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //check set option for plugin is set 1.0.3
    if ($wpw_auto_poster_set_option == '1.0.3') {

        $udpopt = false;

        if (!isset($wpw_auto_poster_options['schedule_wallpost_option'])) { //check Schedule WallPost is set or not
            $schedule_wallpost_option = array('schedule_wallpost_option' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $schedule_wallpost_option);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['schedule_wallpost_time'])) { //check Schedule Time is set or not
            $schedule_wallpost_time = array('schedule_wallpost_time' => '0');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $schedule_wallpost_time);
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated 				
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        //update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.0.4');
    } //check plugin set option value is 1.0.3
    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //check set option for plugin is set 1.0.4
    if ($wpw_auto_poster_set_option == '1.0.4') {

        $udpopt = false;

        if (!isset($wpw_auto_poster_options['schedule_wallpost_minute'])) { //check Schedule Time is set or not
            $schedule_wallpost_minute = array('schedule_wallpost_minute' => '0');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $schedule_wallpost_minute);
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated 				
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        //update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.0.5');
    } //check plugin set option value is 1.0.4
    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //check set option for plugin is set 1.0.5
    if ($wpw_auto_poster_set_option == '1.0.5') {

        $udpopt = false;

        //check twitter image is set or not
        if (!isset($wpw_auto_poster_options['tw_tweet_img'])) {
            $tw_tweet_img = array('tw_tweet_img' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tw_tweet_img);
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated 				
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        //update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.0.6');
    } //check plugin set option value is 1.0.5
    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //check set option for plugin is set 1.0.6
    if ($wpw_auto_poster_set_option == '1.0.6') {

        $udpopt = false;

        //check Facebook bitly access token is set or not
        if (!isset($wpw_auto_poster_options['fb_bitly_access_token'])) {
            $fb_bitly_access_token = array('fb_bitly_access_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $fb_bitly_access_token);
            $udpopt = true;
        }

        //check Twitter bitly access token is set or not
        if (!isset($wpw_auto_poster_options['tw_bitly_access_token'])) {
            $tw_bitly_access_token = array('tw_bitly_access_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tw_bitly_access_token);
            $udpopt = true;
        }

        //check LinkedIn bitly access token is set or not
        if (!isset($wpw_auto_poster_options['li_bitly_access_token'])) {
            $li_bitly_access_token = array('li_bitly_access_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $li_bitly_access_token);
            $udpopt = true;
        }

        //check Tumblr bitly access token is set or not
        if (!isset($wpw_auto_poster_options['tb_bitly_access_token'])) {
            $tb_bitly_access_token = array('tb_bitly_access_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tb_bitly_access_token);
            $udpopt = true;
        }

        //check BufferApp bitly access token is set or not
        if (!isset($wpw_auto_poster_options['ba_bitly_access_token'])) {
            $ba_bitly_access_token = array('ba_bitly_access_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ba_bitly_access_token);
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated 				
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        //update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.0.7');
    } //check plugin set option value is 1.0.6
    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //check set option for plugin is set 1.0.7
    if ($wpw_auto_poster_set_option == '1.0.7') {

        $udpopt = false;

        //check Facebook shortest api token is set or not
        if (!isset($wpw_auto_poster_options['fb_shortest_api_token'])) {
            $fb_shortest_api_token = array('fb_shortest_api_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $fb_shortest_api_token);
            $udpopt = true;
        }

        //check Twitter shortest api token is set or not
        if (!isset($wpw_auto_poster_options['tw_shortest_api_token'])) {
            $tw_shortest_api_token = array('tw_shortest_api_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tw_shortest_api_token);
            $udpopt = true;
        }

        //check LinkedIn shortest api token is set or not
        if (!isset($wpw_auto_poster_options['li_shortest_api_token'])) {
            $li_shortest_api_token = array('li_shortest_api_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $li_shortest_api_token);
            $udpopt = true;
        }

        //check Tumblr shortest api token is set or not
        if (!isset($wpw_auto_poster_options['tb_shortest_api_token'])) {
            $tb_shortest_api_token = array('tb_shortest_api_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tb_shortest_api_token);
            $udpopt = true;
        }


        //check BufferApp shortest api token is set or not
        if (!isset($wpw_auto_poster_options['ba_shortest_api_token'])) {
            $ba_shortest_api_token = array('ba_shortest_api_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ba_shortest_api_token);
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated 				
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        //update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.0.8');
    } //check plugin set option value is 1.0.7
    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //check set option for plugin is set 1.0.8
    if ($wpw_auto_poster_set_option == '1.0.8') {

        $udpopt = false;

        // check daily posts limit is set or not
        if (!isset($wpw_auto_poster_options['enable_random_posting'])) {
            $enable_random_posting = array('enable_random_posting' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $enable_random_posting);
            $udpopt = true;
        }

        // check daily posts limit is set or not
        if (!isset($wpw_auto_poster_options['daily_posts_limit'])) {
            $daily_posts_limit = array('daily_posts_limit' => 10);
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $daily_posts_limit);
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated 				
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.0.9');
    } //check plugin set option value is 1.0.8
    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    // Check set option for plugin is set 1.0.9
    if ($wpw_auto_poster_set_option == '1.0.9') {

        $udpopt = false;

        // Saving facebook data for multiple account for new version
        if (isset($wpw_auto_poster_options['fb_app_id']) && isset($wpw_auto_poster_options['fb_app_secret'])) { // Check facebook app id and app secret is set
            // Updating App key and App secret storage 
            $facebook_keys_data = array(
                'app_id' => $wpw_auto_poster_options['fb_app_id'],
                'app_secret' => $wpw_auto_poster_options['fb_app_secret'],
            );
            $facebook_keys = array('facebook_keys' => array($facebook_keys_data));
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $facebook_keys);

            // Updating old fb session data to new method
            $wpw_auto_poster_fb_sess_data = get_option('wpw_auto_poster_fb_sess_data');
            if (!empty($wpw_auto_poster_fb_sess_data) && empty($wpw_auto_poster_fb_sess_data[$wpw_auto_poster_options['fb_app_id']])) {
                $new_fb_sess_data[$wpw_auto_poster_options['fb_app_id']] = $wpw_auto_poster_fb_sess_data;
                update_option('wpw_auto_poster_fb_sess_data', $new_fb_sess_data);
            }

            // Updating facebook post to accounts
            // Getting all post types
            $types = get_post_types(array('public' => true), 'objects');
            $types = is_array($types) ? $types : array();

            // Loop of post types
            foreach ($types as $type) {

                if (!is_object($type))
                    continue;

                // Skip media
                $label = @$type->labels->name ? $type->labels->name : $type->name;
                if ($label == 'Media' || $label == 'media')
                    continue;

                if (isset($wpw_auto_poster_options['fb_type_' . $type->name . '_user'])) {
                    foreach ($wpw_auto_poster_options['fb_type_' . $type->name . '_user'] as $fb_type_key => $fb_type_data) {
                        if (strpos($fb_type_data, '|') === false) {
                            $wpw_auto_poster_options['fb_type_' . $type->name . '_user'][$fb_type_key] = $fb_type_data . '|' . $wpw_auto_poster_options['fb_app_id'];
                        }
                    }
                }
            }
            $udpopt = true;
        }

        if ($udpopt == true) { // Check if any of the settings need to be updated
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // Update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.0');
    } // Check plugin set option value is 1.0.9
    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');
    
    if( $wpw_auto_poster_set_option == '1.1.0' ) {
    	
 		$udpopt = false;
    	 
    	//check Facebook google API key is set or not
        if (!isset($wpw_auto_poster_options['fb_google_short_api_key'])) {
            $fb_google_short_api_key = array('fb_google_short_api_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $fb_google_short_api_key);
            $udpopt = true;
        }

        //check Twitter google API key is set or not
        if (!isset($wpw_auto_poster_options['tw_google_short_api_key'])) {
            $tw_google_short_api_key = array('tw_google_short_api_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tw_google_short_api_key);
            $udpopt = true;
        }

        //check LinkedIn google API key is set or not
        if (!isset($wpw_auto_poster_options['li_google_short_api_key'])) {
            $li_google_short_api_key = array('li_google_short_api_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $li_google_short_api_key);
            $udpopt = true;
        }

        //check Tumblr google API key is set or not
        if (!isset($wpw_auto_poster_options['tb_google_short_api_key'])) {
            $tb_google_short_api_key = array('tb_google_short_api_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tb_google_short_api_key);
            $udpopt = true;
        }

        //check BufferApp google API key is set or not
        if (!isset($wpw_auto_poster_options['ba_google_short_api_key'])) {
            $ba_google_short_api_key = array('ba_google_short_api_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ba_google_short_api_key);
            $udpopt = true;
        }
        
        if ($udpopt == true) { // Check if any of the settings need to be updated
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // Update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.1');
    }
    
 	//get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //Change Log file Dir and create directory on activation
    wpw_auto_poster_create_files();

    //check set option for plugin is set 1.1.0
    if ($wpw_auto_poster_set_option == '1.1.1') {

        $udpopt = false;

        if (!isset($wpw_auto_poster_options['fb_app_version'])) {
            $fb_app_version = array('fb_app_version' => '208');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $fb_app_version);
            $udpopt = true;
        }

        if ($udpopt == true) { // Check if any of the settings need to be updated
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // Update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.2');

        
    } // Check plugin set option value is 1.1.1

    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //check set option for plugin is set 1.1.2
    if ($wpw_auto_poster_set_option == '1.1.2') {
        
         $udpopt = false;

        // check daily posts limit is set or not
        if (!isset($wpw_auto_poster_options['schedule_wallpost_order'])) {
            $order_by = array('schedule_wallpost_order' => '' );
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $order_by);
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated                 
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.3');

    }

     //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //check set option for plugin is set 1.1.3
    if ($wpw_auto_poster_set_option == '1.1.3') {
        
        $udpopt = false;

        // check daily posts limit is set or not
        if (!isset($wpw_auto_poster_options['fb_wp_pretty_url'])) {
            $wp_pretty_url = array(
                        'fb_wp_pretty_url' => '',
                        'tw_wp_pretty_url' => '',
                        'li_wp_pretty_url' => '',
                        'tb_wp_pretty_url' => '',
                        'ba_wp_pretty_url' => ''
                    );

            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $wp_pretty_url );
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated                 
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.4');

    }

     //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    //check set option for plugin is set 1.1.4
    if ($wpw_auto_poster_set_option == '1.1.4') {
        
        $udpopt = false;

        // check is custom schdeule time for minute is set or not
        if (!isset($wpw_auto_poster_options['schedule_wallpost_custom_minute'])) {
            
            $schedule_wallpost_custom_minute = array('schedule_wallpost_custom_minute' => WPW_AUTO_POSTER_SCHEDULE_CUSTOM_DEFAULT_MINUTE );

            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $schedule_wallpost_custom_minute );
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated                 
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.5');

    }

    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');

    // major updates new options added and registered since 2.6.0 
    //check set option for plugin is set 1.1.4
    if ($wpw_auto_poster_set_option == '1.1.5') {

        $udpopt = false;

        // check is custom schdeule time for minute is set or not
        if (!isset($wpw_auto_poster_options['schedule_wallpost_twice_time1'])) {
            
            $schedule_wallpost_twicedaily_settings = array('schedule_wallpost_twice_time1' => '0', 'schedule_wallpost_twice_time2' => '12', 'enable_twice_random_posting' => '');

            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $schedule_wallpost_twicedaily_settings );
            $udpopt = true;
        }

        
        //check whether facebook fb_global_message_template exist or not
        if (!isset($wpw_auto_poster_options['fb_global_message_template'])) {
            $fb_global_message_template = array('fb_global_message_template' => '{title} - {link}');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $fb_global_message_template);
            $udpopt = true;
        }

        /*** Instagram Support Options Start ***/

        //check whether instagram is enabled
        if (!isset($wpw_auto_poster_options['enable_instagram'])) {
            $enable_instagram = array('enable_instagram' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $enable_instagram);
            $udpopt = true;
        }

        // check whether instagram is enabled for post types
        if (!isset($wpw_auto_poster_options['enable_instagram_for'])) {
            $enable_instagram_for = array('enable_instagram_for' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $enable_instagram_for);
            $udpopt = true;
        }

        //check url shortener instagram
        if (!isset($wpw_auto_poster_options['ins_url_shortener'])) {
            $ins_url_shortener = array('ins_url_shortener' => 'wordpress');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ins_url_shortener);
            $udpopt = true;
        }

        //check Instagram shortest api token is set or not
        if (!isset($wpw_auto_poster_options['ins_shortest_api_token'])) {
            $ins_shortest_api_token = array('ins_shortest_api_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ins_shortest_api_token);
            $udpopt = true;
        }
        
        //check Instagram bitly access token is set or not
        if (!isset($wpw_auto_poster_options['ins_bitly_access_token'])) {
            $ins_bitly_access_token = array('ins_bitly_access_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ins_bitly_access_token);
            $udpopt = true;
        }
        
        //check Instagram google short api key is set or not
        if (!isset($wpw_auto_poster_options['ins_google_short_api_key'])) {
            $ins_google_short_api_key = array('ins_google_short_api_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ins_google_short_api_key);
            $udpopt = true;
        }

        // check whether instagram account is configured
        if (!isset($wpw_auto_poster_options['instagram_keys'])) {
            $instagram_keys = array('instagram_keys' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $instagram_keys);
            $udpopt = true;
        }

        // check instagram pretty url is set or not
        if (!isset($wpw_auto_poster_options['ins_wp_pretty_url'])) {
            $ins_wp_pretty_url = array('ins_wp_pretty_url' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ins_wp_pretty_url);
            $udpopt = true;
        }

        // check whether to show instagram metabox in post page
        if (!isset($wpw_auto_poster_options['prevent_post_ins_metabox'])) {
            $prevent_post_ins_metabox = array('prevent_post_ins_metabox' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $prevent_post_ins_metabox);
            $udpopt = true;
        }

        // check whether instagram custom image os set or not
        if (!isset($wpw_auto_poster_options['ins_custom_img'])) {
            $ins_custom_img = array('ins_custom_img' => '' );
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ins_custom_img);
            $udpopt = true;
        }

        // check whether instagram default template is set or not
        if (!isset($wpw_auto_poster_options['ins_template'])) {
            $ins_template = array('ins_template' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ins_template);
            $udpopt = true;
        }

        /*** Instagram Support Options End ***/

        /*** Pinterest Support Options Start ***/

        //check whether pinterest is enabled
        if (!isset($wpw_auto_poster_options['enable_pinterest'])) {
            $enable_pinterest = array('enable_pinterest' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $enable_pinterest);
            $udpopt = true;
        }

        // check whether pinterest is enabled for post types
        if (!isset($wpw_auto_poster_options['enable_pinterest_for'])) {
            $enable_pinterest_for = array('enable_pinterest_for' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $enable_pinterest_for);
            $udpopt = true;
        }

        //check url shortener pinterest
        if (!isset($wpw_auto_poster_options['pin_url_shortener'])) {
            $pin_url_shortener = array('pin_url_shortener' => 'wordpress');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $pin_url_shortener);
            $udpopt = true;
        }

        //check pinterest shortest api token is set or not
        if (!isset($wpw_auto_poster_options['pin_shortest_api_token'])) {
            $pin_shortest_api_token = array('pin_shortest_api_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $pin_shortest_api_token);
            $udpopt = true;
        }
        
        //check pinterest bitly access token is set or not
        if (!isset($wpw_auto_poster_options['pin_bitly_access_token'])) {
            $pin_bitly_access_token = array('pin_bitly_access_token' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $pin_bitly_access_token);
            $udpopt = true;
        }
        
        //check pinterest google short api key is set or not
        if (!isset($wpw_auto_poster_options['pin_google_short_api_key'])) {
            $pin_google_short_api_key = array('pin_google_short_api_key' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $pin_google_short_api_key);
            $udpopt = true;
        }

        // check whether pinterest account is configured
        if (!isset($wpw_auto_poster_options['pinterest_keys'])) {
            $pinterest_keys = array('pinterest_keys' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $pinterest_keys);
            $udpopt = true;
        }

        // check pinterest pretty url is set or not
        if (!isset($wpw_auto_poster_options['pin_wp_pretty_url'])) {
            $pin_wp_pretty_url = array('pin_wp_pretty_url' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $pin_wp_pretty_url);
            $udpopt = true;
        }

        // check whether to show pinterest metabox in post page
        if (!isset($wpw_auto_poster_options['prevent_post_pin_metabox'])) {
            $prevent_post_pin_metabox = array('prevent_post_pin_metabox' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $prevent_post_pin_metabox);
            $udpopt = true;
        }

        // check whether to show pinterest post image is set
        if (!isset($wpw_auto_poster_options['pin_custom_img'])) {
            $pin_custom_img = array('pin_custom_img' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $pin_custom_img);
            $udpopt = true;
        }

        // check whether to show pinterest post image is set
        if (!isset($wpw_auto_poster_options['pin_custom_template'])) {
            $pin_custom_template = array('pin_custom_template' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $pin_custom_template);
            $udpopt = true;
        }

        /*** Pinterest Support Options End ***/

        // New options for category and tags taxomy selection for each social networks
        if (!isset($wpw_auto_poster_options['fb_post_type_tags'])) {
            $fb_post_type_tags = array('fb_post_type_tags' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $fb_post_type_tags);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['fb_post_type_cats'])) {
            $fb_post_type_cats = array('fb_post_type_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $fb_post_type_cats);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['tw_post_type_tags'])) {
            $tw_post_type_tags = array('tw_post_type_tags' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tw_post_type_tags);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['tw_post_type_cats'])) {
            $tw_post_type_cats = array('tw_post_type_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tw_post_type_cats);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['li_post_type_tags'])) {
            $li_post_type_tags = array('li_post_type_tags' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $li_post_type_tags);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['li_post_type_cats'])) {
            $li_post_type_cats = array('li_post_type_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $li_post_type_cats);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['tb_post_type_tags'])) {
            $tb_post_type_tags = array('tb_post_type_tags' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tb_post_type_tags);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['tb_post_type_cats'])) {
            $tb_post_type_cats = array('tb_post_type_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tb_post_type_cats);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['ba_post_type_tags'])) {
            $ba_post_type_tags = array('ba_post_type_tags' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ba_post_type_tags);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['ba_post_type_cats'])) {
            $ba_post_type_cats = array('ba_post_type_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ba_post_type_cats);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['ins_post_type_tags'])) {
            $ins_post_type_tags = array('ins_post_type_tags' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ins_post_type_tags);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['ins_post_type_cats'])) {
            $ins_post_type_cats = array('ins_post_type_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ins_post_type_cats);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['pin_post_type_tags'])) {
            $pin_post_type_tags = array('pin_post_type_tags' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $pin_post_type_tags);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['pin_post_type_cats'])) {
            $pin_post_type_cats = array('pin_post_type_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $pin_post_type_cats);
            $udpopt = true;
        }
        // code end for category and tags selection


        /*** New options for exclude category selection for each social networks start ***/
        if (!isset($wpw_auto_poster_options['fb_exclude_cats'])) {
            $fb_exclude_cats = array('fb_exclude_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $fb_exclude_cats);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['tw_exclude_cats'])) {
            $tw_exclude_cats = array('tw_exclude_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tw_exclude_cats);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['li_exclude_cats'])) {
            $li_exclude_cats = array('li_exclude_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $li_exclude_cats);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['tb_exclude_cats'])) {
            $tb_exclude_cats = array('tb_exclude_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tb_exclude_cats);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['ba_exclude_cats'])) {
            $ba_exclude_cats = array('ba_exclude_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ba_exclude_cats);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['ins_exclude_cats'])) {
            $ins_exclude_cats = array('ins_exclude_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ins_exclude_cats);
            $udpopt = true;
        }

        if (!isset($wpw_auto_poster_options['pin_exclude_cats'])) {
            $pin_exclude_cats = array('pin_exclude_cats' => array());
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $pin_exclude_cats);
            $udpopt = true;
        }

        // check for google tracking options
        if (!isset($wpw_auto_poster_options['enable_google_tracking'])) {
            $google_tracking = array('enable_google_tracking' => '' );
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $google_tracking);
            $udpopt = true;
        }

        /*** New options for exclude category selection for each social networks end ***/

        if ($udpopt == true) { // if any of the settings need to be updated                 
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.6');

    }

    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');
    
    //check set option for plugin is set 1.1.6 
    if ($wpw_auto_poster_set_option == '1.1.6') {
        $udpopt = false;

        // check is google tracking code script option is exist or not
        if (!isset($wpw_auto_poster_options['google_tracking_script'])) {
            
            $google_tracking_script = array('google_tracking_script' => 'yes', 'google_tracking_code' => '');

            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $google_tracking_script );
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated                 
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.7');

    }

    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');
    
    //check set option for plugin is set 1.1.7 
    if ($wpw_auto_poster_set_option == '1.1.7') {

        $udpopt = false;

        // check is google tracking code script option is exist or not
        if (!isset($wpw_auto_poster_options['schedule_wallpost_order_behaviour'])) {
            
            $wallpost_order_behaviour = array('schedule_wallpost_order_behaviour' => 'DESC');

            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $wallpost_order_behaviour );
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated                 
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.8');
    }

    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');
    
    //check set option for plugin is set 1.1.8 
    if( $wpw_auto_poster_set_option == '1.1.8' ) {
        
        if( empty( $wpw_auto_poster_reposter_options ) ) {
            wpw_auto_posting_reposter_default_settings(); // update default settings for reposter options
        }

        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.9');
    }

    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');
    //check set option for plugin is set 1.1.9 
    if( $wpw_auto_poster_set_option == '1.1.9' ) {
        
        $udpopt = false;

        // check is google tracking code script option is exist or not
        if (!isset($wpw_auto_poster_options['li_global_message_template'])) {
            
            $li_global_message_template = array('li_global_message_template' => '');

            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $li_global_message_template );
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated                 
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.10');

    }

    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');
    //check set option for plugin is set 1.1.10 
    if( $wpw_auto_poster_set_option == '1.1.10' ) {
        
        $udpopt = false;

        // check is google tracking code script option is exist or not
        if (!isset($wpw_auto_poster_options['fb_post_share_type']) || empty( $wpw_auto_poster_options['fb_post_share_type'] ) ) {
            
            $fb_post_share_type = array('fb_post_share_type' => 'link_posting');

            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $fb_post_share_type );
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated                 
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.11');
    }

    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');
    //check set option for plugin is set 1.1.11 
    if( $wpw_auto_poster_set_option == '1.1.11' ) {
        $udpopt = false;

        if ( !isset( $wpw_auto_poster_reposter_options['fb_post_ids_exclude']) ) {
            $post_ids_exclude = array(
                    'fb_post_ids_exclude' => '',
                    'ba_post_ids_exclude' => '',
                    'ins_post_ids_exclude' => '',
                    'li_post_ids_exclude' => '',
                    'pin_post_ids_exclude' => '',
                    'tb_post_ids_exclude' => '',
                    'tw_post_ids_exclude' => '',
                );

            $wpw_auto_poster_reposter_options = array_merge($wpw_auto_poster_reposter_options, $post_ids_exclude );
            $udpopt = true;   
        }

        if ($udpopt == true) { // if any of the settings need to be updated                 
            update_option( 'wpw_auto_poster_reposter_options', $wpw_auto_poster_reposter_options );
        }
        
        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.12');
    }

    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');
    //check set option for plugin is set 1.1.12 
    if( $wpw_auto_poster_set_option == '1.1.12' ) {
        
        $udpopt = false;

        if ( !isset( $wpw_auto_poster_options['facebook_auth_options']) ) {

            $facebook_api_options = array(
                    'facebook_auth_options' => 'graph',
                );

            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $facebook_api_options );
            $udpopt = true;   
        }

        if ($udpopt == true) { // if any of the settings need to be updated                 
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }
        
        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.13');
    }

    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');
    //check set option for plugin is set 1.1.13
    if( $wpw_auto_poster_set_option == '1.1.13' ) {
        
        $udpopt = false;

        //check whether buffer ba_global_message_template exist or not
        if (!isset($wpw_auto_poster_options['ba_global_message_template'])) {
            $ba_global_message_template = array('ba_global_message_template' => '{title} - {link}');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ba_global_message_template);
            $udpopt = true;
        }

        //check whether tumblr tb_global_message_template exist or not
        if (!isset($wpw_auto_poster_options['tb_global_message_template'])) {
            $tb_global_message_template = array('tb_global_message_template' => '{title} - {link}');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $tb_global_message_template);
            $udpopt = true;
        }

        if ($udpopt == true) { // if any of the settings need to be updated                 
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.14');

    }

     //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');
    //check set option for plugin is set 1.1.14
    if( $wpw_auto_poster_set_option == '1.1.14' ) {
        
        $udpopt = false;

        //check whether buffer ba_global_message_template exist or not
        if (!isset($wpw_auto_poster_options['facebook_rest_type'])) {
            $facebook_rest_type = array('facebook_rest_type' => 'android');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $facebook_rest_type);
            $udpopt = true;
        }

        //check whether buffer ba_global_message_template exist or not
        if (!isset($wpw_auto_poster_options['ins_proxy'])) {
            $ins_proxy = array('ins_proxy' => '');
            $wpw_auto_poster_options = array_merge($wpw_auto_poster_options, $ins_proxy);
            $udpopt = true;
        }
        
        if ($udpopt == true) { // if any of the settings need to be updated                 
            update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
        }

        // update plugin version to option 
        update_option('wpw_auto_poster_set_option', '1.1.15');
    }

    
    //get option for when plugin is activating first time
    $wpw_auto_poster_set_option = get_option('wpw_auto_poster_set_option');
    //check set option for plugin is set 1.1.14
    if( $wpw_auto_poster_set_option == '1.1.15' ) {        
        // future code here
    }
    // Check and set the crone on pugin activate if it's not set since 2.6.10
    wpw_auto_poster_check_for_schedule();
}

/**
 *
 * Check for schedule the cron
 *
 * Set the crone if it's not set 
 *
 * @package Social Auto Poster
 * @since 2.6.10
 */
function wpw_auto_poster_check_for_schedule() {

    $wpw_auto_poster_options = get_option('wpw_auto_poster_options');

    if ( !wp_next_scheduled('wpw_auto_poster_reposter_scheduled_cron') ) {

        $utc_timestamp = time(); //

        $local_time = current_time('timestamp'); // to get current local time

        $scheds = (array) wp_get_schedules();
        
        $interval = ( isset($scheds['wpw_reposter_custom_schedule']['interval']) ) ? (int) $scheds['wpw_reposter_custom_schedule']['interval'] : 0;

        $utc_timestamp = $local_time + $interval;

        wp_schedule_event($utc_timestamp, 'wpw_reposter_custom_schedule', 'wpw_auto_poster_reposter_scheduled_cron');
    }

    if ( !wp_next_scheduled('wpw_auto_poster_scheduled_cron') && !empty( $wpw_auto_poster_options['schedule_wallpost_option'] ) ) {

        $utc_timestamp = time(); //
        
        $scheds = (array) wp_get_schedules();

        $current_schedule = $wpw_auto_poster_options['schedule_wallpost_option'];
        $interval = ( isset($scheds[$current_schedule]['interval']) ) ? (int) $scheds[$current_schedule]['interval'] : 0;

        $utc_timestamp = $utc_timestamp + $interval;

        wp_schedule_event($utc_timestamp, $wpw_auto_poster_options['schedule_wallpost_option'], 'wpw_auto_poster_scheduled_cron');
    }

    if ( !wp_next_scheduled('wpw_auto_poster_clear_log_cron') ) {

        $utc_timestamp = time(); //

        $local_time = current_time('timestamp'); // to get current local time

        $scheds = (array) wp_get_schedules();
        
        $interval = ( isset($scheds['weekly']['interval']) ) ? (int) $scheds['weekly']['interval'] : 0;

        $utc_timestamp = $local_time + $interval;

        wp_schedule_event($utc_timestamp, 'weekly', 'wpw_auto_poster_clear_log_cron');
    }

    if ( !wp_next_scheduled('wpw_auto_poster_clear_sap_uploads_cron') ) {

        $utc_timestamp = time(); //

        $local_time = current_time('timestamp'); // to get current local time

        $scheds = (array) wp_get_schedules();
        
        $interval = ( isset($scheds['weekly']['interval']) ) ? (int) $scheds['weekly']['interval'] : 0;

        $utc_timestamp = $local_time + $interval;

        wp_schedule_event($utc_timestamp, 'weekly', 'wpw_auto_poster_clear_sap_uploads_cron');
    }
}

/**
 * Deactivation Hook
 *
 * Register plugin deactivation hook.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
register_deactivation_hook(__FILE__, 'wpw_auto_poster_uninstall');

/**
 * Plugin Setup (On Deactivation)
 *
 * Deletes all the plugin options if the user has
 * set the option to do that.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
function wpw_auto_poster_uninstall() {

    global $wpdb;

    $wpw_auto_poster_options = get_option('wpw_auto_poster_options');

    if (isset($wpw_auto_poster_options['delete_options']) && !empty($wpw_auto_poster_options['delete_options']) && $wpw_auto_poster_options['delete_options'] == '1') {

        //facebook posting class
        $fbposting = new Wpw_Auto_Poster_FB_Posting();
        //linkedin posting class
        $liposting = new Wpw_Auto_Poster_Li_Posting();
        //tumblr posting class
        $tbposting = new Wpw_Auto_Poster_TB_Posting();
        //bufferapp posting class
        $baposting = new Wpw_Auto_Poster_BA_Posting();
        //pinterest posting class
        $pinposting = new Wpw_Auto_Poster_PIN_Posting();

        //facebook session reset
        $fbposting->wpw_auto_poster_fb_reset_session();
        //linkedin session reset
        $liposting->wpw_auto_poster_li_reset_session();
        //tumblr session reset
        $tbposting->wpw_auto_poster_tb_reset_session();
        //bufferapp session reset
        $baposting->wpw_auto_poster_ba_reset_session();
        //pinterest session reset
        $pinposting->wpw_auto_poster_pin_reset_session();

        //delete auto poster options
        delete_option('wpw_auto_poster_options');

        //delete auto poster reposter options
        delete_option('wpw_auto_poster_reposter_options');
        
        //deleter facebook session data
        delete_option('wpw_auto_poster_fb_sess_data');
        //delete linkedin session data
        delete_option('wpw_auto_poster_li_sess_data');
        //delete tumblr session data
        delete_option('wpw_auto_poster_tb_sess_data');
        //delete bufferapp session data
        delete_option('wpw_auto_poster_ba_sess_data');
        //delete twitter account data
        delete_option('wpw_auto_poster_tw_account_details');
        //delete pinterest session data
        delete_option('wpw_auto_poster_pin_sess_data');
        //delete set option data
        delete_option('wpw_auto_poster_set_option');

        // delete custom post type data
        $post_types = array(WPW_AUTO_POSTER_LOGS_POST_TYPE);

        foreach ($post_types as $post_type) {
            $args = array('post_type' => $post_type, 'post_status' => 'any', 'numberposts' => '-1');
            $all_posts = get_posts($args);
            foreach ($all_posts as $post) {
                wp_delete_post($post->ID, true);
            }
        }
    }
}

/**
 * Default Settings
 *
 * Defining the default values for the plugin options.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
function wpw_auto_posting_default_settings() {

    global $wpw_auto_poster_options;

    //default values
    $wpw_auto_poster_options = array(
        //General Settings
        'enable_google_tracking' => '',
        'google_tracking_script' => 'yes',
        'google_tracking_code' => '',
        'delete_options' => '',
        'bitly_username' => '',
        'bitly_api_key' => '',
        'enable_logs' => '',
        'enable_posting_logs' => '',
        'enable_random_posting' => '',
        'schedule_wallpost_option' => '',
        'schedule_wallpost_time' => '0',
        'schedule_wallpost_minute' => '0',
        'daily_posts_limit' => 10,
        'schedule_wallpost_order' => '',
        'autopost_thirdparty_plugins' => 0,
        'schedule_wallpost_custom_minute' => WPW_AUTO_POSTER_SCHEDULE_CUSTOM_DEFAULT_MINUTE,
        'schedule_wallpost_twice_time1' => '0',
        'schedule_wallpost_twice_time2' => '12',
        'enable_twice_random_posting' => '',
        //Facebook Settings
        'enable_facebook' => '',
        'enable_facebook_for' => '',
        'fb_post_type_tags' => array(),
        'fb_post_type_cats' => array(),
        'fb_app_version' => '208',
        'fb_url_shortener' => 'wordpress',
        'fb_bitly_access_token' => '',
        'fb_shortest_api_token' => '',
        'fb_google_short_api_key' => '',
        'facebook_keys' => array(),
        'fb_exclude_cats' => array(),
        'fb_wp_pretty_url' => '',
        'prevent_linked_accounts_access' => '',
        'prevent_post_metabox' => '',
        'prevent_post_tw_metabox' => '',
        'prevent_post_li_metabox' => '',
        'prevent_post_tb_metabox' => '',
        'prevent_post_ba_metabox' => '',
        'fb_custom_img' => '',
        'custom_status_msg' => __('New blog post:', 'wpwautoposter') . '  {title} - {link}',
        'fb_global_message_template' => '{title} - {link}',
        'fb_post_share_type'    => 'link_posting',
        'facebook_auth_options' => 'graph',
        'facebook_rest_type' => 'android',

        //Twitter Settings
        'enable_twitter' => '',
        'enable_twitter_for' => '',
        'tw_post_type_tags' => array(),
        'tw_post_type_cats' => array(),
        'tw_exclude_cats' => array(),
        'tw_url_shortener' => 'wordpress',
        'tw_bitly_access_token' => '',
        'tw_shortest_api_token' => '',
        'tw_google_short_api_key' => '',
        'twitter_keys' => '',
        'tw_tweet_img' => '',
        'tw_tweet_template' => 'title_link',
        'tw_custom_tweet_template' => '',
        'tw_wp_pretty_url' => '',
        
        //LinkedIn Settings
        'enable_linkedin' => '',
        'enable_linkedin_for' => '',
        'li_post_type_tags' => array(),
        'li_post_type_cats' => array(),
        'li_exclude_cats' => array(),
        'li_url_shortener' => 'wordpress',
        'li_bitly_access_token' => '',
        'li_shortest_api_token' => '',
        'li_google_short_api_key' => '',
        'linkedin_app_id' => '',
        'linkedin_app_secret' => '',
        'li_post_image' => '',
        'li_wp_pretty_url' => '',
        
        //Tumblr settting
        'enable_tumblr' => '',
        'enable_tumblr_for' => '',
        'tb_post_type_tags' => array(),
        'tb_post_type_cats' => array(),
        'tb_exclude_cats' => array(),
        'tb_url_shortener' => 'wordpress',
        'tb_bitly_access_token' => '',
        'tb_shortest_api_token' => '',
        'tb_google_short_api_key' => '',
        'tumblr_content_type' => '',
        'tumblr_consumer_key' => '',
        'tumblr_consumer_secret' => '',
        'tb_wp_pretty_url' => '',
        'tb_global_message_template' => '{title} - {link}',
        
        //BufferApp settting
        'enable_bufferapp' => '',
        'enable_bufferapp_for' => '',
        'ba_post_type_tags' => array(),
        'ba_post_type_cats' => array(),
        'ba_exclude_cats' => array(),
        'ba_url_shortener' => 'wordpress',
        'ba_bitly_access_token' => '',
        'ba_shortest_api_token' => '',
        'ba_google_short_api_key' => '',
        'bufferapp_client_id' => '',
        'bufferapp_client_secret' => '',
        'ba_global_message_template' => '{title} - {link}',
        'ba_post_img' => '',
        'ba_wp_pretty_url' => '',

        //Instagram Settings since 2.6.0
        'enable_instagram' => '',
        'enable_instagram_for' => '',
        'ins_post_type_tags' => array(),
        'ins_post_type_cats' => array(),
        'ins_exclude_cats' => array(),
        'ins_url_shortener' => 'wordpress',
        'ins_bitly_access_token' => '',
        'ins_shortest_api_token' => '',
        'ins_google_short_api_key' => '',
        'instagram_keys' => array(),
        'ins_wp_pretty_url' => '',
        'prevent_post_ins_metabox' => '',
        'ins_custom_img' => '' ,
        'ins_template'  => '',

        //Pinterest Settings since 2.6.0
        'enable_pinterest' => '',
        'enable_pinterest_for' => '',
        'pin_post_type_tags' => array(),
        'pin_post_type_cats' => array(),
        'pin_exclude_cats' => array(),
        'pin_url_shortener' => 'wordpress',
        'pin_bitly_access_token' => '',
        'pin_shortest_api_token' => '',
        'pin_google_short_api_key' => '',
        'pinterest_keys' => array(),
        'pin_wp_pretty_url' => '',
        'prevent_post_pin_metabox' => '',
        'pin_custom_img' => '',
        'pin_custom_template' => '' ,                                       
    );
    
    // apply filters for default settings
    $wpw_auto_poster_options = apply_filters('wpw_auto_poster_default_settings', $wpw_auto_poster_options);

    update_option('wpw_auto_poster_options', $wpw_auto_poster_options);
}

/**
 * Default Settings
 *
 * Defining the default values for the plugin reposter options.
 *
 * @package Social Auto Poster
 * @since 2.6.9
 */
function wpw_auto_posting_reposter_default_settings() {
    
    global $wpw_auto_poster_reposter_options;

    //default values
    $wpw_auto_poster_reposter_options = array(
        //General Settings
        'schedule_posting_order' => '',
        'schedule_posting_order_behaviour' => 'ASC',
        'schedule_wallpost_option' => array( 'days' => '0', 'hours' => '0', 'minutes' => '0' ),
        'daily_posts_limit' => 10,
        'schedule_wallpost_repeat' => 'no',
        'reposter_repeat_times' => '',
        'unique_posting' => '',

        //Facebook Settings
        'enable_facebook' => '',
        'enable_facebook_for' => '',
        'fb_posts_limit' => 5,
        'fb_posting_cats' => 'include',
        'fb_post_type_cats' => array(),
        'fb_last_posted_page' => 1,
        'fb_post_ids_exclude' => '',

        
        //Twitter Settings
        'enable_twitter' => '',
        'tw_posts_limit' => 5,
        'enable_twitter_for' => '',
        'tw_posting_cats' => 'include',
        'tw_post_type_cats' => array(),
        'tw_last_posted_page' => 1,
        'tw_post_ids_exclude' => '',
        
        //LinkedIn Settings
        'enable_linkedin' => '',
        'enable_linkedin_for' => '',
        'li_posts_limit' => 5,
        'li_posting_cats' => 'include',
        'li_post_type_cats' => array(),
        'li_last_posted_page' => 1,
        'li_post_ids_exclude' => '',
        
        //Tumblr settting
        'enable_tumblr' => '',
        'enable_tumblr_for' => '',
        'tb_posts_limit' => 5,
        'tb_posting_cats' => 'include',
        'tb_post_type_cats' => array(),
        'tb_last_posted_page' => 1,
        'tb_post_ids_exclude' => '',
        
        //BufferApp settting
        'enable_bufferapp' => '',
        'enable_bufferapp_for' => '',
        'ba_posts_limit' => 5,
        'ba_posting_cats' => 'include',
        'ba_post_type_cats' => array(),
        'ba_last_posted_page' => 1,
        'ba_post_ids_exclude' => '',

        //Instagram Settings
        'enable_instagram' => '',
        'enable_instagram_for' => '',
        'ins_posts_limit' => 5,
        'ins_posting_cats' => 'include',
        'ins_post_type_cats' => array(),
        'ins_last_posted_page' => 1,
        'ins_post_ids_exclude' => '',

        //Pinterest Settings
        'enable_pinterest' => '',
        'enable_pinterest_for' => '',
        'pin_posts_limit' => 5,
        'pin_posting_cats' => 'include',
        'pin_post_type_cats' => array(),
        'pin_last_posted_page' => 1,
        'pin_post_ids_exclude' => '',
    );

    // apply filters for reposter default settings
    $wpw_auto_poster_reposter_options = apply_filters('wpw_auto_poster_reposter_default_settings', $wpw_auto_poster_reposter_options );

    update_option('wpw_auto_poster_reposter_options', $wpw_auto_poster_reposter_options);
}

/**
 * Create Files/Directories
 * 
 * Handle to create files/directories on activation
 * 
 * @package Social Auto Poster
 * @since 1.6.2
 */
function wpw_auto_poster_create_files() {

    $files = array(
        array(
            'base' => WPW_AUTO_POSTER_LOG_DIR,
            'file' => 'index.html',
            'content' => ''
        ),
        array(
            'base' => WPW_AUTO_POSTER_SAP_UPLOADS_DIR,
            'file' => '',
            'content' => ''
        ),
    );

    foreach ($files as $file) {
        if (wp_mkdir_p($file['base']) && !file_exists(trailingslashit($file['base']) . $file['file'])) {
            if ($file_handle = @fopen(trailingslashit($file['base']) . $file['file'], 'w')) {
                fwrite($file_handle, $file['content']);
                fclose($file_handle);
            }
        }
    }
}

/**
 * Add plugin action links
 *
 * Adds a settings, support and docs link to the plugin list.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
function wpw_auto_poster_add_settings_link($links) {
    $plugin_links = array(
        '<a href="' . add_query_arg(array('page' => 'wpw-auto-poster-settings'), admin_url('admin.php')) . '">' . __('Settings', 'wpwautoposter') . '</a>',
        '<a href="http://support.wpweb.co.in/">' . __('Support', 'wpwautoposter') . '</a>',
        '<a href="http://wpweb.co.in/documents/social-auto-poster/">' . __('Docs', 'wpwautoposter') . '</a>'
    );

    return array_merge($plugin_links, $links);
}

//add plugin settings, support and docs link to plugin listing page			
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wpw_auto_poster_add_settings_link');

function wpw_auto_poster_plugin_loaded(){

	// Check if Wpweb Updter is not activated then load updater from plugin itself
    if( !class_exists( 'Wpweb_Upd_Admin' ) ) {
        
        // Load the updater file
        include_once ( WPW_AUTO_POSTER_DIR . '/includes/updater/wpweb-updater.php' );
        
        // call to updater function
        wpw_auto_poster_wpweb_updater();
    }
}

//add action to load plugin
add_action( 'plugins_loaded', 'wpw_auto_poster_plugin_loaded' );

/**
 * Start Session
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
function wpw_auto_poster_sessionset() {

    global $wpdb, $wpw_auto_poster_message_stack;

    if (!session_id()) {
        @session_start();
    }

    $settingspage = add_query_arg(array('page' => 'wpw-auto-poster-settings'), admin_url('admin.php'));

    // Reset Facebook User Data
    if (isset($_GET['fb_reset_user']) && $_GET['fb_reset_user'] == '1' && !empty($_GET['wpw_fb_app'])) {
        $fbposting = new Wpw_Auto_Poster_FB_Posting();
        $fbposting->wpw_auto_poster_fb_reset_session();
        $wpw_auto_poster_message_stack->add_session('poster-selected-tab', 'facebook');
        wp_redirect($settingspage);
        exit;
    }

    // Reset Facebook REST method User Data
    if (isset($_GET['fb_reset_rest_user']) && $_GET['fb_reset_rest_user'] == '1' && !empty($_GET['wpw_fb_userid'])) {
        $fbposting = new Wpw_Auto_Poster_FB_Android_Posting();
        $fbposting->wpw_auto_poster_fb_reset_session();
        $wpw_auto_poster_message_stack->add_session('poster-selected-tab', 'facebook');
        wp_redirect($settingspage);
        exit;
    }

    // Reset LinkedIn User Data
    if (isset($_GET['li_reset_user']) && $_GET['li_reset_user'] == '1') {
        $liposting = new Wpw_Auto_Poster_Li_Posting();
        $liposting->wpw_auto_poster_li_reset_session();
        $wpw_auto_poster_message_stack->add_session('poster-selected-tab', 'linkedin');
        wp_redirect($settingspage);
        exit;
    }
    //Reset Twitter User Data
    if (isset($_GET['tb_reset_user']) && $_GET['tb_reset_user'] == '1') { // if user reset session to tumblr
        $tbposting = new Wpw_Auto_Poster_TB_Posting();
        $tbposting->wpw_auto_poster_tb_reset_session();
        $wpw_auto_poster_message_stack->add_session('poster-selected-tab', 'tumblr');
        wp_redirect($settingspage);
        exit;
    }
    //Reset BufferApp User Data
    if (isset($_GET['ba_reset_user']) && $_GET['ba_reset_user'] == '1') {
        $baposting = new Wpw_Auto_Poster_BA_Posting();
        $baposting->wpw_auto_poster_ba_reset_session();
        $wpw_auto_poster_message_stack->add_session('poster-selected-tab', 'bufferapp');
        wp_redirect($settingspage);
        exit;
    }

    // Reset Pinterest User Data
    if (isset($_GET['pin_reset_user']) && $_GET['pin_reset_user'] == '1' && !empty($_GET['wpw_pin_app'])) {
        $pinposting = new Wpw_Auto_Poster_PIN_Posting();
        $pinposting->wpw_auto_poster_pin_reset_session();
        $wpw_auto_poster_message_stack->add_session('poster-selected-tab', 'pinterest');
        wp_redirect($settingspage);
        exit;
    }
}

global $wpw_auto_poster_options, $wpw_auto_poster_message_stack, $wpw_auto_poster_model,
 $wpw_auto_poster_fb_posting, $wpw_auto_poster_fb_andrd_posting,$wpw_auto_poster_tw_posting, $wpw_auto_poster_li_posting, $wpw_auto_poster_tb_posting,
 $wpw_auto_poster_ba_posting, $wpw_auto_poster_ins_posting, $wpw_auto_poster_scripts, $wpw_auto_poster_render, $wpw_auto_poster_admin, $wpw_auto_poster_logs, $wpw_auto_poster_social_meta_box, $wpw_auto_poster_pin_posting,$wpw_auto_poster_reposter_options;

/**
 * Include different files needed for our plugin.
 *
 * @package Social Auto Poster
 * @since 1.0.0
 */
require_once( WPW_AUTO_POSTER_DIR . '/includes/wpw-auto-poster-misc-functions.php' ); // plugin options class
$wpw_auto_poster_options = wpw_auto_poster_settings();
$wpw_auto_poster_reposter_options = wpw_auto_poster_reposter_settings();
wpw_auto_poster_initialize();

//Register Post Types
require_once( WPW_AUTO_POSTER_DIR . '/includes/wpw-auto-poster-post-types.php' );

//Settings functions
require_once(WPW_AUTO_POSTER_ADMIN . '/forms/wpw-auto-poster-settings-functions.php' );

//Logs Class
require_once( WPW_AUTO_POSTER_DIR . '/includes/class-wpw-auto-poster-logs.php');
$wpw_auto_poster_logs = new Wpw_Auto_Poster_Logs();

//Message Stack Class
require_once( WPW_AUTO_POSTER_DIR . '/includes/class-wpw-auto-poster-message-stack.php');
$wpw_auto_poster_message_stack = new Wpw_Auto_Poster_Message_Stack();

//Model Class
require_once( WPW_AUTO_POSTER_DIR . '/includes/class-wpw-auto-poster-model.php' );
$wpw_auto_poster_model = new Wpw_Auto_Poster_Model();


//Facebook Posting Class
require_once( WPW_AUTO_POSTER_DIR . '/includes/social/class-wpw-auto-poster-fb-posting.php' ); // fan page posting class
$wpw_auto_poster_fb_posting = new Wpw_Auto_Poster_FB_Posting();

//Facebook Android Posting Class
require_once( WPW_AUTO_POSTER_DIR . '/includes/social/class-wpw-auto-poster-android-fb-posting.php' ); // fan page posting class
$wpw_auto_poster_fb_andrd_posting = new Wpw_Auto_Poster_FB_Android_Posting();

//Twitter Posting Class
require_once( WPW_AUTO_POSTER_DIR . '/includes/social/class-wpw-auto-poster-tw-posting.php' ); // twitter posting class
$wpw_auto_poster_tw_posting = new Wpw_Auto_Poster_TW_Posting();

//Linkein Posting Class
require_once( WPW_AUTO_POSTER_DIR . '/includes/social/class-wpw-auto-poster-li-posting.php' ); // linkedin posting class
$wpw_auto_poster_li_posting = new Wpw_Auto_Poster_Li_Posting();

//Tumblr Posting Class
require_once( WPW_AUTO_POSTER_DIR . '/includes/social/class-wpw-auto-poster-tb-posting.php' ); // tumblr posting class
$wpw_auto_poster_tb_posting = new Wpw_Auto_Poster_TB_Posting();

//BufferApp Posting Class
require_once( WPW_AUTO_POSTER_DIR . '/includes/social/class-wpw-auto-poster-ba-posting.php' ); // bufferapp posting class
$wpw_auto_poster_ba_posting = new Wpw_Auto_Poster_BA_Posting();

//Instagram Posting Class since 2.6.0
require_once( WPW_AUTO_POSTER_DIR . '/includes/social/class-wpw-auto-poster-ins-posting.php' );// instagram posting class
$wpw_auto_poster_ins_posting = new Wpw_Auto_Poster_INS_Posting();

//Pinterest Posting Class since 2.6.0
require_once( WPW_AUTO_POSTER_DIR . '/includes/social/class-wpw-auto-poster-pin-posting.php' ); // pinterest posting class
$wpw_auto_poster_pin_posting = new Wpw_Auto_Poster_PIN_Posting();

//Metabox File to add metabox
require_once( WPW_AUTO_POSTER_META_DIR . '/wpw-auto-poster-meta-box.php' );

//Including the Scripts and Styles Files
require_once( WPW_AUTO_POSTER_DIR . '/includes/class-wpw-auto-poster-scripts.php' );
$wpw_auto_poster_scripts = new Wpw_Auto_Posting_Scripts();
$wpw_auto_poster_scripts->add_hooks();

//Render Class to handles most of HTML designs for plugin
require_once( WPW_AUTO_POSTER_DIR . '/includes/class-wpw-auto-poster-renderer.php' );
$wpw_auto_poster_render = new Wpw_Auto_Poster_Renderer();

//Admin Class to handles all admin functionalities
require_once( WPW_AUTO_POSTER_ADMIN . '/class-wpw-auto-poster-admin.php' );
$wpw_auto_poster_admin = new Wpw_Auto_Posting_AdminPages();
$wpw_auto_poster_admin->add_hooks();

require_once( WPW_AUTO_POSTER_META_DIR . '/class-wpw-auto-poster-meta.php' );
$wpw_auto_poster_social_meta_box = new Wpw_Auto_Poster_Social_Meta_Box();
$wpw_auto_poster_social_meta_box->add_hooks();


//session set
add_action('init', 'wpw_auto_poster_sessionset', 15);

/**
 * Add plugin to updater list and create updater object
 * 
 * @package Social Auto Poster
 * @since 2.6.5
 */
function wpw_auto_poster_wpweb_updater() {

    // Plugin updates
    wpweb_queue_update(plugin_basename(__FILE__), WPW_AUTO_POSTER_PLUGIN_KEY);

    /**
     * Include Auto Updating Files
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    if( class_exists( 'Wpweb_Upd_Admin' ) )        
        require_once( WPWEB_UPD_DIR . '/updates/class-plugin-update-checker.php' ); // auto updating
    else
        require_once( WPW_AUTO_POSTER_WPWEB_UPD_DIR . '/updates/class-plugin-update-checker.php' ); // auto updating

    $WpwebAutoPosterUpdateChecker = new WpwebPluginUpdateChecker(
        'http://wpweb.co.in/Updates/SAP/license-info.php', __FILE__, WPW_AUTO_POSTER_PLUGIN_KEY
    );

    /**
     * Auto Update
     * 
     * Get the license key and add it to the update checker.
     * 
     * @package Social Auto Poster
     * @since 1.0.0
     */
    function wpw_auto_poster_add_secret_key($query) {

        $plugin_key = WPW_AUTO_POSTER_PLUGIN_KEY;

        $query['lickey'] = wpweb_get_plugin_purchase_code($plugin_key);
        return $query;
    }

    $WpwebAutoPosterUpdateChecker->addQueryArgFilter('wpw_auto_poster_add_secret_key');
} // end check WPWeb Updater is activated

//check Wpweb Updater plugin activated
if( class_exists( 'Wpweb_Upd_Admin' ) ) {	
    wpw_auto_poster_wpweb_updater();
}
