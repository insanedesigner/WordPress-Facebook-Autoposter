<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Plugin Loaded
 * 
 * Add metabox fields in plugin loaded action.
 * 
 * @package Social Auto Poster
 * @since 1.6.2
 */
function wpw_auto_poster_add_meta_boxes() {

    //include the main class file for metabox
    //require_once( WPW_AUTO_POSTER_META_DIR . '/meta-box-class.php' );
    //include extended metabox class to user in poster plugin

    require_once( WPW_AUTO_POSTER_META_DIR . '/class-wpw-auto-poster-meta.php' );


    global $wpw_auto_poster_model, $wpw_auto_poster_options, $wpw_auto_poster_fb_posting, $wpw_auto_poster_tw_posting, $wpw_auto_poster_li_posting, $wpw_auto_poster_tb_posting, $wpw_auto_poster_ba_posting;

    // Facebook app version
    $fb_app_version = ( !empty( $wpw_auto_poster_options['fb_app_version'] ) ) ? $wpw_auto_poster_options['fb_app_version'] : '';

    $facebook_auth_options = !empty( $wpw_auto_poster_options['facebook_auth_options'] ) ? $wpw_auto_poster_options['facebook_auth_options'] : 'graph';

    //model class
    $model = $wpw_auto_poster_model;

    //posting class
    $fbposting = $wpw_auto_poster_fb_posting;
    $twposting = $wpw_auto_poster_tw_posting;
    $liposting = $wpw_auto_poster_li_posting;
    $tbposting = $wpw_auto_poster_tb_posting;
    $baposting = $wpw_auto_poster_ba_posting;

    /*
     * prefix of meta keys, optional
     * use underscore (_) at the beginning to make keys hidden, for example $prefix = '_ba_';
     *  you also can make prefix empty to disable it
     */
    $prefix = WPW_AUTO_POSTER_META_PREFIX;

    /*
     * configure your meta box
     */
    $config1 = array(
        'id' => 'wpw_auto_poster_meta', // meta box id, unique per meta box
        'title' => __('Social Auto Poster Settings', 'wpwautoposter'), // meta box title
        'pages' => 'all', //insert meta in custom post type
        'context' => 'normal', // where the meta box appear: normal (default), advanced, side; optional
        'priority' => 'high', // order of meta box: high (default), low; optional
        'fields' => array(), // list of meta fields (can be added by field arrays)
        'local_images' => false, // Use local or hosted images (meta box images for add/remove)
    );

    $poster_meta = new Wpw_Auto_Poster_Social_Meta_Box($config1);

    /*     * *********************************** Facebook Tab Starts ***************************************************** */
    $defaulttabon = true; //Active first tab by default

    //Check Post status
    $post_id = !empty($_GET['post']) ? $_GET['post'] :'';
    //Check Shedule general options
    $schedule_option = !empty( $wpw_auto_poster_options['schedule_wallpost_option'] )? $wpw_auto_poster_options['schedule_wallpost_option'] : '';
	$post_status = $post_desc = '';

    if (!isset($wpw_auto_poster_options['prevent_post_metabox']) || empty($wpw_auto_poster_options['prevent_post_metabox'])) { //check if not allowed for individual post in settings page
        $fbmetatab = array(
            'class' => 'facebook', //unique class name of each tabs
            'title' => __('Facebook', 'wpwautoposter'), //  title of tab
            'active' => $defaulttabon //it will by default make tab active on page load
        );

        $defaulttabon = false; //when facebook is on then inactive other tab by default
        //initiate tabs in metabox
        $poster_meta->addTabs($fbmetatab);

        // Get stored fb app grant data
        $wpw_auto_poster_fb_sess_data = get_option('wpw_auto_poster_fb_sess_data');

        // Get all facebook account authenticated
        $fb_users = wpw_auto_poster_get_fb_accounts('all_accounts');

        // Check facebook application id and secret must entered in settings page or not
        if ( ( WPW_AUTO_POSTER_FB_APP_ID == '' || WPW_AUTO_POSTER_FB_APP_SECRET == '' ) && $facebook_auth_options == 'graph' ) {

            $poster_meta->addGrantPermission($prefix . 'fb_warning', array('desc' => __('Enter your Facebook APP ID / Secret within the Settings Page, otherwise the Facebook posting won\'t work.', 'wpwautoposter'), 'url' => add_query_arg(array('page' => 'wpw-auto-poster-settings'), admin_url('admin.php')), 'urltext' => __('Go to the Settings Page', 'wpwautoposter'), 'tab' => 'facebook'));
        } elseif (empty($wpw_auto_poster_fb_sess_data)) { // Check facebook user id is set or not
            $poster_meta->addGrantPermission($prefix . 'fb_grant', array('desc' => __('Your App doesn\'t have enough permissions to publish on Facebook.', 'wpwautoposter'), 'url' => add_query_arg(array('page' => 'wpw-auto-poster-settings'), admin_url('admin.php')), 'urltext' => __('Go to the Settings Page', 'wpwautoposter'), 'tab' => 'facebook'));
        }

        //add label to show status
        $poster_meta->addTweetStatus($prefix . 'fb_published_on_fb', array('name' => __('Status:', 'wpwautoposter'), 'desc' => __('Status of Facebook wall post like published/unpublished/scheduled.', 'wpwautoposter'), 'tab' => 'facebook'));

        $post_status = get_post_meta($post_id, $prefix.'fb_published_on_fb', true );
        $post_label  = __('Publish Post On Facebook:', 'wpwautoposter');
        $post_desc   = __('Publish this Post to Facebook Userwall.', 'wpwautoposter');

		if( $post_status == 1 && empty($schedule_option)) {
			$post_label = __('Re-publish Post On Facebook:', 'wpwautoposter');
			$post_desc  = __('Re-publish this Post to Facebook Userwall.', 'wpwautoposter');
		} elseif ( ( $post_status == 2 ) || ( $post_status == 1 && !empty($schedule_option) ) ) {
			$post_label = __('Re-schedule Post On Facebook:', 'wpwautoposter');
			$post_desc  = __('Re-schedule this Post to Facebook Userwall.', 'wpwautoposter');
		} elseif ( empty($post_status) && !empty($schedule_option) ) {
			$post_label  = __('Schedule Post On Facebook:', 'wpwautoposter');
        	$post_desc   = __('Schedule this Post to Facebook Userwall.', 'wpwautoposter');
		}

        $post_desc .= '<br>'.sprintf( __( 'If you have enabled %sEnable auto posting to Facebook%s in global settings then you do not need to check this box to publish/schedule the post. This setting is only for republishing Or rescheduling post to Facebook.', 'wpwautoposter'), '<strong>', '</strong>');
        $post_desc .= '<br><p style="color:#c83737;" classs="wpw-auto-poster-meta"><strong>'.__('Note:', 'wpwautoposter').'</strong> '. sprintf( __( 'This setting is just an event to republish/reschedule the content, It will not save any value to %sdatabase%s.', 'wpwautoposter'), '<strong>','</strong>').'</p>';

        //post to facebook
        $poster_meta->addPublishBox($prefix . 'post_to_facebook', array('name' => $post_label, 'desc' => $post_desc, 'tab' => 'facebook'));

        //publish with diffrent post title
        $poster_meta->addTextarea($prefix . 'fb_custom_title', array('validate_func' => 'escape_html', 'name' => __('Custom Message:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom message which will be used for the wall post. Leave it empty to use the post title. You can use following template tags within the message:', 'wpwautoposter') .
            '<br /><code>{first_name}</code> - ' . __('displays the first name,', 'wpwautoposter') .
            '<br /><code>{last_name}</code> - ' . __('displays the last name,', 'wpwautoposter') .
            '<br /><code>{title}</code> - ' . __('displays the default post title,', 'wpwautoposter') .
            '<br /><code>{link}</code> - ' . __('displays the default post link,', 'wpwautoposter') .
            '<br /><code>{sitename}</code> - ' . __('displays the name of your site,', 'wpwautoposter') .
            '<br /><code>{excerpt}</code> - ' . __('displays the post excerpt.', 'wpwautoposter').
            '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter')
            , 'tab' => 'facebook', 'rows' => 3));

        do_action('wpw_auto_poster_after_custom_message_field_fb', $poster_meta, $post_id);


        //post to this account
        $poster_meta->addSelect($prefix . 'fb_user_id', $fb_users, array('name' => __('Post To This Facebook Account', 'wpwautoposter') . '(' . __('s', 'wpwautoposter') . '):', 'std' => array(''), 'desc' => __('Select an account to which you want to post. This setting overrides the global and category settings. Leave it  empty to use the global/category defaults.', 'wpwautoposter'), 'multiple' => true, 'placeholder' => __('Default', 'wpwautoposter'), 'tab' => 'facebook'));

        $wall_post_methods = array(
            '' => __('Default', 'wpwautoposter')
        );
        $wall_post_methods = array_merge($wall_post_methods, $model->wpw_auto_poster_get_fb_posting_method());

        //post on wall as a type
        $poster_meta->addSelect($prefix . 'fb_posting_method', $wall_post_methods, array('name' => __('Post As:', 'wpwautoposter'), 'std' => array(''), 'desc' => __('Select a Facebook post type. Leave it empty to use the default one from the settings page.', 'wpwautoposter'), 'tab' => 'facebook'));
        
        $share_posting_type_methods = array(
                ''              => __('Default', 'wpwautoposter'),
                'link_posting'  => __('Link posting', 'wpwautoposter'),
                'image_posting'  => __('Image posting', 'wpwautoposter'),
            );

        // Fb Share posting type as
        $poster_meta->addSelect($prefix . 'fb_share_posting_type', $share_posting_type_methods, array('name' => __('Share type:', 'wpwautoposter'), 'std' => array(''), 'desc' => __('Select a Facebook share post type. Leave it empty to use the default one from the settings page.', 'wpwautoposter'), 'tab' => 'facebook'));

        $sharepost_desc = '<br><p style="color:#c83737;" classs="wpw-auto-poster-meta"><strong>'.__('Note:', 'wpwautoposter').'</strong> '. sprintf( __( 'If you are using image posting then the supported image formats are %sJPEG, BMP, PNG, GIF%s.', 'wpwautoposter'), '<strong>','</strong>').'</p>';
        $sharepost_desc .= '<p style="color:#c83737;" classs="wpw-auto-poster-meta">'.sprintf( __( 'Recommend uploading image under 1MB.', 'wpwautoposter'), '<strong>','</strong>').'</p>';
        $poster_meta->addGallery($prefix . 'fb_post_gallery', array('name' => __('Image(s) to use:', 'wpwautoposter'), 'desc' => __('Here you can upload multiple images which will be used for the Facebook image posting. Leave it empty to use the featured image. if featured image is not set then it will take default image from the settings page.', 'wpwautoposter').$sharepost_desc, 'tab' => 'facebook', 'show_path' => true));

        // Display custom image if fb version below 2.9
        if( $fb_app_version < 209 ) {

            //post image url
            $poster_meta->addImage($prefix . 'fb_post_image', array('name' => __('Post Image:', 'wpwautoposter'), 'desc' => __('Here you can upload a default image which will be used for the Facebook wall post. Leave it empty to use the featured image. if featured image is also blank, then it will take default image from the settings page.', 'wpwautoposter').'<br><br><strong>'.__('Note: ', 'wpwautoposter').'</strong>'.__('This option only work if your facebook app version is below 2.9. If you\'re using latest facebook app, it wont work.','wpwautoposter').' <a href="https://developers.facebook.com/blog/post/2017/06/27/API-Change-Log-Modifying-Link-Previews/" target="_blank">'.__('Learn More.', 'wpwautoposter').'</a>', 'tab' => 'facebook', 'show_path' => true));
        }

        //post image url
        //$poster_meta->addUpload( $prefix . 'fb_post_image_test', array( 'name'=> __( 'Post Image:', 'wpwautoposter' ), 'desc' => __( 'Here you can upload a default image which will be used for the Facebook wall post. Leave it empty to use the featured image. if featured image is also blank, then it will take default image from the settings page.', 'wpwautoposter' ), 'tab' => 'facebook' ) );
        //publish with diffrent post title
        $poster_meta->addText($prefix . 'fb_custom_status_msg', array('default' => __('New blog post :', 'wpwautoposter') . ' {title} - {link}', 'validate_func' => 'escape_html', 'name' => __('Status Update Text:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom status update text. Leave it empty to  use the default one from the settings page. You can use following template tags within the status text:', 'wpwautoposter') .
            '<br /><code>{first_name}</code> - ' . __('displays the first name,', 'wpwautoposter') .
            '<br /><code>{last_name}</code> - ' . __('displays the last name,', 'wpwautoposter') .
            '<br /><code>{title}</code> - ' . __('displays the post title,', 'wpwautoposter') .
            '<br /><code>{link}</code> - ' . __('displays the post link,', 'wpwautoposter') .
            '<br /><code>{sitename}</code> - ' . __('displays the name of your site.', 'wpwautoposter').'<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter')
            , 'tab' => 'facebook'));
        
        // Display custom post link and description if fb version below 2.11
        if( $fb_app_version < 209 ) {

            //custom link to post to facebook
            $poster_meta->addText($prefix . 'fb_custom_post_link', array('validate_func' => 'escape_html', 'name' => __('Custom Link:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom link which will be used for  the wall post. Leave it empty to use the link of the current post. The link must start with', 'wpwautoposter') . ' http://', 'tab' => 'facebook'));
        }
    }
    /*     * *********************************** Facebook Tab Ends ***************************************************** */

    /*     * *********************************** Twitter Tab Starts ***************************************************** */
    if (!isset($wpw_auto_poster_options['prevent_post_tw_metabox']) || empty($wpw_auto_poster_options['prevent_post_tw_metabox'])) { //check if not allowed for individual post in settings page
        $opttemplate = isset($wpw_auto_poster_options['tw_tweet_template']) ? $wpw_auto_poster_options['tw_tweet_template'] : 'title_link';

        //tweet default tempalte 
        $defaulttemplate = $model->wpw_auto_poster_get_tweet_template($opttemplate);

        $twmetatab = array(
            'class' => 'twitter', //unique class name of each tabs
            'title' => __('Twitter', 'wpwautoposter'), //  title of tab
            'active' => $defaulttabon //it will by default make tab active on page load
        );

        $defaulttabon = false; //when twitter is on then inactive other tab by default
        //initiate tabs in metabox
        $poster_meta->addTabs($twmetatab);

        if (WPW_AUTO_POSTER_TW_CONS_KEY == '' || WPW_AUTO_POSTER_TW_CONS_SECRET == '' || WPW_AUTO_POSTER_TW_AUTH_TOKEN == '' || WPW_AUTO_POSTER_TW_AUTH_SECRET == '') {

            $poster_meta->addGrantPermission($prefix . 'tw_warning', array('desc' => __('Enter your Twitter Application Details within the Settings Page, otherwise posting to Twitter won\'t work.', 'wpwautoposter'), 'url' => add_query_arg(array('page' => 'wpw-auto-poster-settings'), admin_url('admin.php')), 'urltext' => __('Go to the Settings Page', 'wpwautoposter'), 'tab' => 'twitter'));
        }

        //Get twitter account details
        $tw_users = get_option('wpw_auto_poster_tw_account_details', array());

        //add label to show status
        $poster_meta->addTweetStatus($prefix . 'tw_status', array('name' => __('Status:', 'wpwautoposter'), 'desc' => __('Status of Twitter wall post like published/unpublished/scheduled.', 'wpwautoposter'), 'tab' => 'twitter'));

        $post_status = get_post_meta($post_id, $prefix.'tw_status', true );

        $post_label  = __('Publish Post On Twitter:', 'wpwautoposter');
        $post_desc   = __('Publish this Post to Twitter.', 'wpwautoposter');

		if( $post_status == 1 && empty($schedule_option)) {
			$post_label = __('Re-publish Post On Twitter:', 'wpwautoposter');
			$post_desc  = __('Re-publish this Post to Twitter.', 'wpwautoposter');
		} elseif ( ( $post_status == 2 ) || ( $post_status == 1 && !empty($schedule_option) ) ) {
			$post_label = __('Re-schedule Post On Twitter:', 'wpwautoposter');
			$post_desc  = __('Re-schedule this Post to Twitter.', 'wpwautoposter');
		} elseif ( empty($post_status) && !empty($schedule_option) ) {
			$post_label  = __('Schedule Post On Twitter:', 'wpwautoposter');
        	$post_desc   = __('Schedule this Post to Twitter.', 'wpwautoposter');
		}

        $post_desc .= '<br>'.sprintf( __( 'If you have enabled %sEnable auto posting to Twitter%s in global settings then you do not need to check this box to publish/schedule the post. This setting is only for republishing Or rescheduling post to Twitter.', 'wpwautoposter'), '<strong>', '</strong>');
        $post_desc .= '<br><p style="color:#c83737;" classs="wpw-auto-poster-meta"><strong>'.__('Note:', 'wpwautoposter').'</strong> '. sprintf( __( 'This setting is just an event to republish/reschedule the content, It will not save any value to %sdatabase%s.', 'wpwautoposter'), '<strong>','</strong>').'</p>';

        //post to twitter
        $poster_meta->addPublishBox($prefix . 'post_to_twitter', array('name' => $post_label, 'desc' => $post_desc, 'tab' => 'twitter'));

        //post to this account 
        $poster_meta->addSelect($prefix . 'tw_user_id', $tw_users, array('name' => __('Post To This Twitter Account', 'wpwautoposter') . '(' . __('s', 'wpwautoposter') . '):', 'std' => array(''), 'desc' => __('Select an account to which you want to post. This setting overrides the global and category settings. Leave it  empty to use the global/category defaults.', 'wpwautoposter'), 'multiple' => true, 'placeholder' => __('Default', 'wpwautoposter'), 'tab' => 'twitter'));

        //tweet mode
        $poster_meta->addTweetMode($prefix . 'tw_tweet_mode', array('name' => __('Mode:', 'wpwautoposter'), 'desc' => __('Tweet Template Mode.', 'wpwautoposter'), 'tab' => 'twitter'));
        
        if( empty($wpw_auto_poster_options['tw_disable_image_tweet']) ) {
            //tweet image url
            $poster_meta->addImage($prefix . 'tw_image', array('name' => __('Tweet Image:', 'wpwautoposter'), 'desc' => __('Here you can upload a default image which will be used for the Tweet Image. Leave it empty to use the featured image. if featured image is also blank, then it will take default image from the settings page.', 'wpwautoposter'), 'tab' => 'twitter', 'show_path' => true));
        }
        
        //tweet template, do not change the order for tweet template and tweet preview field
        $poster_meta->addTweetTemplate($prefix . 'tw_template', array('default' => $defaulttemplate, 'validate_func' => 'escape_html', 'name' => __('Tweet Template:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom Tweeter template. Leave it empty to use the default one from the settings page. You can use following template tags within the status text:', 'wpwautoposter') .
            '<br /><code>{title}</code> - ' . __('displays the post title,', 'wpwautoposter') .
            '<br /><code>{link}</code> - ' . __('displays the post link,', 'wpwautoposter') .
            '<br /><code>{full_author}</code> - ' . __('displays the full author name,', 'wpwautoposter') .
            '<br /><code>{nickname_author}</code> - ' . __('displays the nickname of author,', 'wpwautoposter') .
            '<br /><code>{post_type}</code> - ' . __('displays the post type,', 'wpwautoposter') .
            '<br /><code>{excerpt}</code> - ' . __('displays the post excerpt.', 'wpwautoposter').
            '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter')
            ,'tab' => 'twitter'));

        //add label to show preview, do not change the order for tweet template and tweet preview field
        $poster_meta->addTweetPreview($prefix . 'tw_template', array('default' => $defaulttemplate, 'validate_func' => 'escape_html', 'name' => __('Preview:', 'wpwautoposter'), 'tab' => 'twitter'));
    }
    /*     * *********************************** Twitter Tab Ends ***************************************************** */

    /*     * *********************************** LinkedIn Tab Starts ***************************************************** */
    if (!isset($wpw_auto_poster_options['prevent_post_li_metabox']) || empty($wpw_auto_poster_options['prevent_post_li_metabox'])) { //check if not allowed for individual post in settings page
        $limetatab = array(
            'class' => 'linkedin', //unique class name of each tabs
            'title' => __('LinkedIn', 'wpwautoposter'), //  title of tab
            'active' => $defaulttabon //it will by default make tab active on page load
        );

        $defaulttabon = false; //when linkedin is on then inactive other tab by default
        //initiate tabs in metabox
        $poster_meta->addTabs($limetatab);

        // Get stored li app grant data
        $wpw_auto_poster_li_sess_data = get_option('wpw_auto_poster_li_sess_data');

        if (WPW_AUTO_POSTER_LI_APP_ID == '' || WPW_AUTO_POSTER_LI_APP_SECRET == '') {

            $poster_meta->addGrantPermission($prefix . 'li_warning', array('desc' => __('Enter your LinkedIn Application Details within the Settings Page, otherwise posting to LinkedIn won\'t work.', 'wpwautoposter'), 'url' => add_query_arg(array('page' => 'wpw-auto-poster-settings'), admin_url('admin.php')), 'urltext' => __('Go to the Settings Page', 'wpwautoposter'), 'tab' => 'linkedin'));
        
        } elseif( empty( $wpw_auto_poster_li_sess_data ) ) {

                $poster_meta->addGrantPermission($prefix . 'li_grant', array('desc' => __('Your App doesn\'t have enough permissions to publish on Linkedin.', 'wpwautoposter'), 'url' => add_query_arg(array('page' => 'wpw-auto-poster-settings'), admin_url('admin.php')), 'urltext' => __('Grant extended permissions now', 'wpwautoposter'), 'tab' => 'linkedin'));
        }

        //add label to show status
        $poster_meta->addTweetStatus($prefix . 'li_status', array('name' => __('Status:', 'wpwautoposter'), 'desc' => __('Status of LinkedIn wall post like published/unpublished/scheduled.', 'wpwautoposter'), 'tab' => 'linkedin'));

        $post_status = get_post_meta($post_id, $prefix.'li_status', true );
        $post_label  = __('Publish Post On LinkedIn:', 'wpwautoposter');
        $post_desc   = __('Publish this Post to your LinkedIn.', 'wpwautoposter');

		if( $post_status == 1 && empty($schedule_option)) {
			$post_label = __('Re-publish Post On LinkedIn:', 'wpwautoposter');
			$post_desc  = __('Re-publish this Post to your LinkedIn.', 'wpwautoposter');
		} elseif ( ( $post_status == 2 ) || ( $post_status == 1 && !empty($schedule_option) ) ) {
			$post_label = __('Re-schedule Post On LinkedIn:', 'wpwautoposter');
			$post_desc  = __('Re-schedule this Post to your LinkedIn.', 'wpwautoposter');
		} elseif ( empty($post_status) && !empty($schedule_option) ) {
			$post_label  = __('Schedule Post On LinkedIn:', 'wpwautoposter');
        	$post_desc   = __('Schedule this Post to your LinkedIn.', 'wpwautoposter');
		}

        $post_desc .= '<br>'.sprintf( __( 'If you have enabled %sEnable auto posting to LinkedIn%s in global settings then you do not need to check this box to publish/schedule the post. This setting is only for republishing Or rescheduling post to LinkedIn.', 'wpwautoposter'), '<strong>', '</strong>');
        $post_desc .= '<br><p style="color:#c83737;" classs="wpw-auto-poster-meta"><strong>'.__('Note:', 'wpwautoposter').'</strong> '. sprintf( __( 'This setting is just an event to republish/reschedule the content, It will not save any value to %sdatabase%s.', 'wpwautoposter'), '<strong>','</strong>').'</p>';

        //post to linkedin
        $poster_meta->addPublishBox($prefix . 'post_to_linkedin', array('name' => $post_label, 'desc' => $post_desc, 'tab' => 'linkedin'));

        //publish status to linkedin
        $poster_meta->addTextarea($prefix . 'li_post_title', array('validate_func' => 'escape_html', 'name' => __('Custom Title:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom title which will be used for the wall post. Leave it empty to use the post title. You can use following template tags within the custom title:', 'wpwautoposter').
            '<br /><code>{first_name}</code> - ' . __('displays the first name,', 'wpwautoposter') .
            '<br /><code>{last_name}</code> - ' . __('displays the last name,', 'wpwautoposter') .
            '<br /><code>{title}</code> - ' . __('displays the default post title,', 'wpwautoposter') .
            '<br /><code>{link}</code> - ' . __('displays the default post link,', 'wpwautoposter') .
            '<br /><code>{sitename}</code> - ' . __('displays the name of your site,', 'wpwautoposter') .
            '<br /><code>{excerpt}</code> - ' . __('displays the post excerpt.', 'wpwautoposter').
            '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter')
            , 'tab' => 'linkedin', 'rows' => 3 ));

        $li_profiles = $liposting->wpw_auto_poster_get_profiles_data();

        //post to this account
        $poster_meta->addSelect($prefix . 'li_post_profile', $li_profiles, array('name' => __('Post To This Linkedin Account', 'wpwautoposter') . '(' . __('s', 'wpwautoposter') . '):', 'std' => array(''), 'desc' => __('Select an account to which you want to post. This setting overrides the global and category settings. Leave it  empty to use the global/category defaults.', 'wpwautoposter'), 'multiple' => true, 'placeholder' => __('Default', 'wpwautoposter'), 'tab' => 'linkedin'));

        //publish status to linkedin image
        $poster_meta->addImage($prefix . 'li_post_image', array('name' => __('Post Image:', 'wpwautoposter'), 'desc' => __('Here you can upload a default image which will be used for the LinkedIn wall post. Leave it empty to use the featured image. if featured image is also blank, then it will take default image from the settings page.', 'wpwautoposter'), 'tab' => 'linkedin', 'show_path' => true));

        //custom link to post to facebook
        $poster_meta->addText($prefix . 'li_post_link', array('validate_func' => 'escape_html', 'name' => __('Custom Link:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom link which will be used for the wall post. Leave it empty to use the link of the current post. The link must start with', 'wpwautoposter') . ' http://', 'tab' => 'linkedin'));

        //comment to linkedin
        $poster_meta->addTextarea($prefix . 'li_post_comment', array('validate_func' => 'escape_html', 'name' => __('Custom Message:', 'wpwautoposter'), 'desc' => __('Here you can customize the content which will be used by LinkedIn for the wall post. You can use following template tags within the status text:', 'wpwautoposter') .
            '<br /><code>{first_name}</code> - ' . __('displays the first name,', 'wpwautoposter') .
            '<br /><code>{last_name}</code> - ' . __('displays the last name,', 'wpwautoposter') .
            '<br /><code>{title}</code> - ' . __('displays the post title,', 'wpwautoposter') .
            '<br /><code>{post_content}</code> - ' . __('displays the post content,', 'wpwautoposter') .
            '<br /><code>{link}</code> - ' . __('displays the post link,', 'wpwautoposter') .
            '<br /><code>{sitename}</code> - ' . __('displays the name of your site.', 'wpwautoposter') .
            '<br /><code>{excerpt}</code> - ' . __('displays the post excerpt.', 'wpwautoposter').
            '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter')
            , 'tab' => 'linkedin'));

    
    }
    /*     * *********************************** LinkedIn Tab Ends   ***************************************************** */

    /*     * *********************************** Tumblr Tab Starts ***************************************************** */
    if (!isset($wpw_auto_poster_options['prevent_post_tb_metabox']) || empty($wpw_auto_poster_options['prevent_post_tb_metabox'])) { //check if not allowed for individual post in settings page
        $tbmetatab = array(
            'class' => 'tumblr', //unique class name of each tabs
            'title' => __('Tumblr', 'wpwautoposter'), //  title of tab
            'active' => $defaulttabon //it will by default make tab active on page load
        );

        //Posting type
        $tb_posting_types = array(
            '' => __('Select', 'wpwautoposter'),
            'text' 	=> __('Text', 'wpwautoposter'),
            'link' 	=> __('Link', 'wpwautoposter'),
            'photo' => __('Photo', 'wpwautoposter')
        );

        $defaulttabon = false; //when tumblr is on then inactive other tab by default
        //initiate tabs in metabox
        $poster_meta->addTabs($tbmetatab);


        if (WPW_AUTO_POSTER_TB_CONS_KEY == '' || WPW_AUTO_POSTER_TB_CONS_SECRET == '') {

            $poster_meta->addGrantPermission($prefix . 'tb_warning', array('desc' => __('Enter your Tumblr Application Details within the Settings page, otherwise the posting to Tumblr won\'t work.', 'wpwautoposter'), 'url' => add_query_arg(array('page' => 'wpw-auto-poster-settings'), admin_url('admin.php')), 'urltext' => __('Go to the Settings Page', 'wpwautoposter'), 'tab' => 'tumblr'));
        } else {

            if (!isset($_SESSION['wpw_auto_poster_tb_user_id']) || empty($_SESSION['wpw_auto_poster_tb_user_id'])) {
                $poster_meta->addGrantPermission($prefix . 'tb_grant', array('desc' => __('Your App doesn\'t have enough permissions to publish on Tumblr.', 'wpwautoposter'), 'url' => $tbposting->wpw_auto_poster_get_tb_login_url(), 'urltext' => __('Grant extended permissions now.', 'wpwautoposter'), 'tab' => 'tumblr'));
            }
        }

        //add label to show status
        $poster_meta->addTweetStatus($prefix . 'tb_status', array('name' => __('Status:', 'wpwautoposter'), 'desc' => __('Status of Tumblr wall post like published/unpublished/scheduled.', 'wpwautoposter'), 'tab' => 'tumblr'));

        $post_status = get_post_meta($post_id, $prefix.'tb_status', true );
        $post_label  = __('Publish Post On Tumblr:', 'wpwautoposter');
        $post_desc   = __('Publish this Post to Tumblr Userwall.', 'wpwautoposter');

		if( $post_status == 1 && empty($schedule_option)) {
			$post_label = __('Re-publish Post On Tumblr:', 'wpwautoposter');
			$post_desc  = __('Re-publish this Post to Tumblr Userwall.', 'wpwautoposter');
		} elseif ( ( $post_status == 2 ) || ( $post_status == 1 && !empty($schedule_option) ) ) {
			$post_label = __('Re-schedule Post On Tumblr:', 'wpwautoposter');
			$post_desc  = __('Re-schedule this Post to Tumblr Userwall.', 'wpwautoposter');
		} elseif ( empty($post_status) && !empty($schedule_option) ) {
			$post_label  = __('Schedule Post On Tumblr:', 'wpwautoposter');
        	$post_desc   = __('Schedule this Post to Tumblr Userwall.', 'wpwautoposter');
		}

        $post_desc .= '<br>'.sprintf( __( 'If you have enabled %sEnable auto posting to Tumblr%s in global settings then you do not need to check this box to publish/schedule the post. This setting is only for republishing Or rescheduling post to Tumblr.', 'wpwautoposter'), '<strong>', '</strong>');
        $post_desc .= '<br><p style="color:#c83737;" classs="wpw-auto-poster-meta"><strong>'.__('Note:', 'wpwautoposter').'</strong> '. sprintf( __( 'This setting is just an event to republish/reschedule the content, It will not save any value to %sdatabase%s.', 'wpwautoposter'), '<strong>','</strong>').'</p>';

        //post to tumblr
        $poster_meta->addPublishBox($prefix . 'post_to_tumblr', array('name' => $post_label, 'desc' => $post_desc, 'tab' => 'tumblr'));

        //posting type
        $poster_meta->addSelect($prefix . 'tb_posting_type', $tb_posting_types, array('name' => __('Posting Type:', 'wpwautoposter'), 'std' => array(''), 'desc' => __('Choose posting type which you want to use. Leave it empty to use the default one from the settings page.', 'wpwautoposter'), 'tab' => 'tumblr'));

        //publish status to tumblr
        $poster_meta->addTextarea($prefix . 'tb_post_title', array('validate_func' => 'escape_html', 'name' => __('Custom Title:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom title which will be used on the wall post. Leave it empty to use the post title. You can use following template tags within the custom title:', 'wpwautoposter').
            '<br /><code>{first_name}</code> - ' . __('displays the first name,', 'wpwautoposter') .
            '<br /><code>{last_name}</code> - ' . __('displays the last name,', 'wpwautoposter') .
            '<br /><code>{title}</code> - ' . __('displays the default post title,', 'wpwautoposter') .
            '<br /><code>{link}</code> - ' . __('displays the default post link,', 'wpwautoposter') .
            '<br /><code>{sitename}</code> - ' . __('displays the name of your site,', 'wpwautoposter') .
            '<br /><code>{excerpt}</code> - ' . __('displays the post excerpt.', 'wpwautoposter').
            '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter')
            , 'tab' => 'tumblr', 'rows' => '3' ));

        //post link
        $poster_meta->addText($prefix . 'tb_custom_post_link', array('validate_func' => 'escape_html', 'name' => __('Custom Link:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom link which will be used on the wall post. Leave it empty to use the link to the post. The link must start with http://', 'wpwautoposter'), 'tab' => 'tumblr'));

        //publish status descriptin to tumblr
        $poster_meta->addTextarea($prefix . 'tb_post_desc', array('validate_func' => 'escape_html', 'name' => __('Custom Message:', 'wpwautoposter'), 'desc' => __('Here you can enter custom content which will appear underneath the post title in Tumblr. Leave it empty to use the post content. You can use following template tags within the custom message:', 'wpwautoposter') .
            '<br /><code>{first_name}</code> - ' . __('display the first name,', 'wpwautoposter') .
            '<br /><code>{last_name}</code> - ' . __('display the last name,', 'wpwautoposter') .
            '<br /><code>{title}</code> - ' . __('display the post title,', 'wpwautoposter') .
            '<br /><code>{sitename}</code> - ' . __('display the sitename/blogname.', 'wpwautoposter').
            '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter')
            , 'tab' => 'tumblr'));

        //post image url
        $poster_meta->addImage($prefix . 'tb_post_image', array('name' => __('Post Image:', 'wpwautoposter'), 'desc' => __('Here you can upload a default image which will be used for the Tumblr post. Leave it empty to use the featured image. if featured image is also blank, then it will take default image from the settings page.', 'wpwautoposter'), 'tab' => 'tumblr', 'show_path' => true));
    }
    /*     * *********************************** Tumblr Tab Ends ***************************************************** */

    /*     * *********************************** BufferApp Tab Starts ***************************************************** */
    if (!isset($wpw_auto_poster_options['prevent_post_ba_metabox']) || empty($wpw_auto_poster_options['prevent_post_ba_metabox'])) { //check if not allowed for individual post in settings page
        $dcmetatab = array(
            'class' => 'bufferapp', //unique class name of each tabs
            'title' => __('BufferApp', 'wpwautoposter'), //  title of tab
            'active' => $defaulttabon //it will by default make tab active on page load
        );

        $defaulttabon = false; //when bufferapp is on then inactive other tab by default
        //initiate tabs in metabox
        $poster_meta->addTabs($dcmetatab);

        //get all bufferapp account authenticated
        $ba_users = array();

        $ba_users[''] = __('--Select--', 'wpwautoposter');

        if (isset($_SESSION['wpw_auto_poster_ba_cache']) && !empty($_SESSION['wpw_auto_poster_ba_cache'])) {

            foreach ($_SESSION['wpw_auto_poster_ba_cache'] as $key => $account) {

                $ba_users[$account->id] = $account->formatted_username;
            }
        }

        if (WPW_AUTO_POSTER_BA_CLIENT_ID == '' || WPW_AUTO_POSTER_BA_CLIENT_SECRET == '') {

            $poster_meta->addGrantPermission($prefix . 'ba_warning', array('desc' => __('Enter your BufferApp Application Details within the Settings Page, otherwise posting to BufferApp won\'t work.', 'wpwautoposter'), 'url' => admin_url('edit.php?post_type=wpw_auto_poster&page=wpw-auto-poster-settings'), 'urltext' => __('Go to the Settings Page', 'wpwautoposter'), 'tab' => 'bufferapp'));
        } else {
            if (!isset($_SESSION['wpw_auto_poster_ba_user_id']) || empty($_SESSION['wpw_auto_poster_ba_user_id'])) {
                $poster_meta->addGrantPermission($prefix . 'tb_grant', array('desc' => __('Your App doesn\'t have enough permissions to publish on BufferApp.', 'wpwautoposter'), 'url' => admin_url('edit.php?post_type=wpw_auto_poster&page=wpw-auto-poster-settings'), 'urltext' => __('Go to Settings Page', 'wpwautoposter'), 'tab' => 'bufferapp'));
            }
        }

        //add label to show status
        $poster_meta->addTweetStatus($prefix . 'ba_status', array('name' => __('Status:', 'wpwautoposter'), 'desc' => __('Status of BufferApp wall post like published/unpublished/scheduled.', 'wpwautoposter'), 'tab' => 'bufferapp'));

        $post_status = get_post_meta($post_id, $prefix.'ba_status', true );
        $post_label  = __('Publish Post On BufferApp:', 'wpwautoposter');
        $post_desc   = __('Publish this post to your BufferApp Userwall.', 'wpwautoposter');

		if( $post_status == 1 && empty($schedule_option)) {
			$post_label = __('Re-publish Post On BufferApp:', 'wpwautoposter');
			$post_desc  = __('Re-publish this post to your BufferApp Userwall.', 'wpwautoposter');
		} elseif ( ( $post_status == 2 ) || ( $post_status == 1 && !empty($schedule_option) ) ) {
			$post_label = __('Re-schedule Post On BufferApp:', 'wpwautoposter');
			$post_desc  = __('Re-schedule this post to your BufferApp Userwall.', 'wpwautoposter');
		} elseif ( empty($post_status) && !empty($schedule_option) ) {
			$post_label  = __('Schedule Post On BufferApp:', 'wpwautoposter');
        	$post_desc   = __('Schedule this Post to your BufferApp Userwall.', 'wpwautoposter');
		}

        $post_desc .= '<br>'.sprintf( __( 'If you have enabled %sEnable auto posting to BufferApp%s in global settings then you do not need to check this box to publish/schedule the post. This setting is only for republishing Or rescheduling post to BufferApp.', 'wpwautoposter'), '<strong>', '</strong>');
        $post_desc .= '<br><p style="color:#c83737;" classs="wpw-auto-poster-meta"><strong>'.__('Note:', 'wpwautoposter').'</strong> '. sprintf( __( 'This setting is just an event to republish/reschedule the content, It will not save any value to %sdatabase%s.', 'wpwautoposter'), '<strong>','</strong>').'</p>';

        //post to bufferapp
        $poster_meta->addPublishBox($prefix . 'post_to_bufferapp', array('name' => $post_label, 'desc' => $post_desc, 'tab' => 'bufferapp'));

        //publish status to bufferapp
        $poster_meta->addTextarea($prefix . 'ba_post_title', array('validate_func' => 'escape_html', 'name' => __('Custom Title:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom title which will be used for the wall post. Leave it empty to use the post title. You can use following template tags within the custom title:', 'wpwautoposter').
            '<br /><code>{first_name}</code> - ' . __('displays the first name,', 'wpwautoposter') .
            '<br /><code>{last_name}</code> - ' . __('displays the last name,', 'wpwautoposter') .
            '<br /><code>{title}</code> - ' . __('displays the default post title,', 'wpwautoposter') .
            '<br /><code>{sitename}</code> - ' . __('displays the name of your site,', 'wpwautoposter') .
            '<br /><code>{excerpt}</code> - ' . __('displays the post excerpt.', 'wpwautoposter').
            '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter')
            , 'tab' => 'bufferapp', 'rows' => '3' ));

        //post to this account
        $poster_meta->addSelect($prefix . 'ba_post_to_accounts', $ba_users, array('name' => __('Publish To This BufferApp Account:', 'wpwautoposter'), 'std' => array(''), 'desc' => __('Select an account to which you want to post. Leave it empty to use the default one from the settings page.', 'wpwautoposter'), 'multiple' => true, 'placeholder' => __('Default', 'wpwautoposter'), 'tab' => 'bufferapp'));

        //publish status to bufferapp image
        $poster_meta->addImage($prefix . 'ba_post_image', array('name' => __('Post Image:', 'wpwautoposter'), 'desc' => __('Here you can upload a default image which will be used for the BufferApp wall post. Leave it empty to use the featured image. if featured image is also blank, then it will take default image from the settings page.', 'wpwautoposter'), 'tab' => 'bufferapp', 'show_path' => true));

        //custom link to post to facebook
        $poster_meta->addText($prefix . 'ba_custom_post_link', array('validate_func' => 'escape_html', 'name' => __('Custom Link:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom link which will be used for the wall post. Leave it empty to use the link of the current post. The link must start with', 'wpwautoposter') . ' http://<br /><strong>' . __('Note', 'wpwautoposter') . ' : </strong>' . __('Link is only used for posting on facebook profile(s).', 'wpwautoposter'), 'tab' => 'bufferapp'));

        //comment to bufferapp
        $poster_meta->addTextarea($prefix . 'ba_post_desc', array('validate_func' => 'escape_html', 'name' => __('Custom Message:', 'wpwautoposter'), 'desc' => __('Here you can customize the content which will be used for BufferApp wall post. Leave it empty to use the post content. You can use following template tags within the custom message:', 'wpwautoposter') .
            '<br /><code>{first_name}</code> - ' . __('displays the first name,', 'wpwautoposter') .
            '<br /><code>{last_name}</code> - ' . __('displays the last name,', 'wpwautoposter') .
            '<br /><code>{title}</code> - ' . __('displays the post title,', 'wpwautoposter') .
            '<br /><code>{link}</code> - ' . __('displays the post link,', 'wpwautoposter') .
            '<br /><code>{sitename}</code> - ' . __('display the sitename/blogname.', 'wpwautoposter') 
            .
            '<br /><code>{excerpt}</code> - ' . __('displays the post excerpt.', 'wpwautoposter').
            '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
            '<br /><strong>' . __('Note', 'wpwautoposter') . ' : </strong>' . __('Description is only used for posting on facebook profile(s).', 'wpwautoposter').
            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter')
            , 'tab' => 'bufferapp'));
    }
    /*     * *********************************** BufferApp Tab Ends ***************************************************** */

    /** 
     * Instagram Tab Starts
     * @since 2.6.0
    **/

    if (!isset($wpw_auto_poster_options['prevent_post_ins_metabox']) || empty($wpw_auto_poster_options['prevent_post_ins_metabox'])) { //check if not allowed for individual post in settings page
        $insmetatab = array(
            'class' => 'instagram', //unique class name of each tabs
            'title' => __('Instagram', 'wpwautoposter'), //  title of tab
            'active' => $defaulttabon //it will by default make tab active on page load
        );

        $defaulttabon = false; //when instagram is on then inactive other tab by default
        //initiate tabs in metabox
        $poster_meta->addTabs($insmetatab);

        //Get instagram account details
        $ins_account_details = get_option('wpw_auto_poster_ins_account_details', array());
        $ins_users = array();

        if(!empty($ins_account_details)){
            foreach ($ins_account_details as $key => $ins_account) {
                $ins_user_data = explode("|",$ins_account);
                $ins_users[$ins_account] = trim($ins_user_data[0]); 
            }
        }

        if (empty($ins_users) || count($ins_users) < 1) {

            $poster_meta->addGrantPermission($prefix . 'ins_warning', array('desc' => __('Enter your Instagram username/password within the Settings Page, otherwise posting to Instagram won\'t work.', 'wpwautoposter'), 'url' => add_query_arg(array('page' => 'wpw-auto-poster-settings'), admin_url('admin.php')), 'urltext' => __('Go to the Settings Page', 'wpwautoposter'), 'tab' => 'instagram'));
        } else if( !extension_loaded('gd') ) {
            $poster_meta->addGrantPermission($prefix . 'ins_warning', array('desc' => sprintf( __( 'Instagram requires %sGD%s PHP library enabled. Contact your host or server administrator to configure and install the missing library.', 'wpwautoposter' ), '<b>', '</b>' ), 'url' => '', 'urltext' =>'', 'tab' => 'instagram'));
        } else if( !function_exists('exif_imagetype') ) {
            $poster_meta->addGrantPermission($prefix . 'ins_warning', array('desc' => sprintf( __( 'Instagram requires %sExif%s PHP library enabled. Contact your host or server administrator to configure and install the missing library.', 'wpwautoposter' ), '<b>', '</b>' ), 'url' => '', 'urltext' =>'', 'tab' => 'instagram'));
        }

        $poster_meta->addTweetStatus($prefix . 'ins_published_on_ins', array('name' => __('Status:', 'wpwautoposter'), 'desc' => __('Status of Instagram timeline like published/unpublished/scheduled.', 'wpwautoposter'), 'tab' => 'instagram'));

        $post_status = get_post_meta($post_id, $prefix.'ins_published_on_ins', true );
        $post_label  = __('Publish Post On Instagram:', 'wpwautoposter');
        $post_desc   = __('Publish this Post to Instagram timeline.', 'wpwautoposter');

		if( $post_status == 1 && empty($schedule_option)) {
			$post_label = __('Re-publish Post On Instagram:', 'wpwautoposter');
			$post_desc  = __('Re-publish this Post to Instagram timeline.', 'wpwautoposter');
		} elseif ( ( $post_status == 2 ) || ( $post_status == 1 && !empty($schedule_option) ) ) {
			$post_label = __('Re-schedule Post On Instagram:', 'wpwautoposter');
			$post_desc  = __('Re-schedule this Post to Instagram timeline.', 'wpwautoposter');
		} elseif ( empty($post_status) && !empty($schedule_option) ) {
			$post_label  = __('Schedule Post On Instagram:', 'wpwautoposter');
        	$post_desc   = __('Schedule this Post to Instagram timeline.', 'wpwautoposter');
		}

        $post_desc .= '<br>'.sprintf( __( 'If you have enabled %sEnable auto posting to Instagram%s in global settings then you do not need to check this box to publish/schedule the post. This setting is only for republishing Or rescheduling post to Instagram.', 'wpwautoposter'), '<strong>', '</strong>');
        $post_desc .= '<br><p style="color:#c83737;" classs="wpw-auto-poster-meta"><strong>'.__('Note:', 'wpwautoposter').'</strong> '. sprintf( __( 'This setting is just an event to republish/reschedule the content, It will not save any value to %sdatabase%s.', 'wpwautoposter'), '<strong>','</strong>').'</p>';

        //post to instagram
        $poster_meta->addPublishBox($prefix . 'post_to_instagram', array('name' => $post_label, 'desc' => $post_desc, 'tab' => 'instagram'));

        //post to this account
        $poster_meta->addSelect($prefix . 'ins_user_id', $ins_users, array('name' => __('Post To This Instagram Account', 'wpwautoposter') . '(' . __('s', 'wpwautoposter') . '):', 'std' => array(''), 'desc' => __('Select an account to which you want to post. This setting overrides the global settings. Leave it  empty to use the global defaults.', 'wpwautoposter'), 'multiple' => true, 'placeholder' => __('Default', 'wpwautoposter'), 'tab' => 'instagram'));

        //post image 
        $poster_meta->addImage($prefix . 'ins_post_image', array('name' => __('Post Image:', 'wpwautoposter'), 'desc' => __('Here you can upload a default image which will be used for the Instagram timeline post. Leave it empty to use the featured image. if featured image is also blank, then it will take default image from the settings page.', 'wpwautoposter').'<br><br><strong>'.__('Note: ', 'wpwautoposter').'</strong>'.__('Instagram require atleast one image for posting.','wpwautoposter').'<b>'. __(' Recommended image width between 320 to 1080 pixels.', 'wpwautoposter').'</b><br><br>'.__('If the image width is less than 320 pixels, it will be automatically enlarged to 320 pixels. If the image width is greater than 1080 pixels, it will be automatically resized to 1080 pixels.', 'wpwautoposter'), 'tab' => 'instagram', 'show_path' => true));

        //post message
        $poster_meta->addTextarea($prefix . 'ins_custom_status_msg', array('default' => '', 'validate_func' => 'escape_html', 'name' => __('Custom Message:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom caption text. Leave it empty to  use the default one from the settings page. You can use following template tags within the caption text:', 'wpwautoposter') .
            '<br /><code>{first_name}</code> - ' . __('displays the first name,', 'wpwautoposter') .
            '<br /><code>{last_name}</code> - ' . __('displays the last name,', 'wpwautoposter') .
            '<br /><code>{display_name}</code> - ' . __('displays the display name,', 'wpwautoposter') .
            '<br /><code>{title}</code> - ' . __('displays the post title,', 'wpwautoposter') .
            '<br /><code>{excerpt}</code> - ' . __('displays the short post description,', 'wpwautoposter') .
            '<br /><code>{link}</code> - ' . __('displays the post link,', 'wpwautoposter') .
            '<br /><code>{sitename}</code> - ' . __('displays the name of your site.', 'wpwautoposter').
            '<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter')
            , 'tab' => 'instagram'));
    }
    /*     * *********************************** Instagram Tab Ends ***************************************************** */

    /** 
     * Pinterest Tab Starts
     * @since 2.6.0
    **/
    if (!isset($wpw_auto_poster_options['prevent_post_pin_metabox']) || empty($wpw_auto_poster_options['prevent_post_pin_metabox'])) { //check if not allowed for individual post in settings page
        $pinmetatab = array(
            'class' => 'pinterest', //unique class name of each tabs
            'title' => __('Pinterest', 'wpwautoposter'), //  title of tab
            'active' => $defaulttabon //it will by default make tab active on page load
        );

        $defaulttabon = false; //when pinterest is on then inactive other tab by default
        //initiate tabs in metabox
        $poster_meta->addTabs($pinmetatab);

        // Get stored pin app grant data
        $wpw_auto_poster_pin_sess_data = get_option('wpw_auto_poster_pin_sess_data');

        // Get all pinterest account authenticated
        $pin_users = wpw_auto_poster_get_pin_accounts('all_accounts');

        if (!is_ssl()) {

            $poster_meta->addGrantPermission($prefix . 'pin_warning', array('desc' => __('Pinterest requires SSL for posting to boards.', 'wpwautoposter'), 'url' => '', 'urltext' =>'', 'tab' => 'pinterest'));
        } elseif (WPW_AUTO_POSTER_PIN_APP_ID == '' || WPW_AUTO_POSTER_PIN_APP_SECRET == '') { // Check pinterest application id and secret must entered in settings page or not

            $poster_meta->addGrantPermission($prefix . 'pin_warning', array('desc' => __('Enter your Pinterest APP ID / Secret within the Settings Page, otherwise the Pinterest posting won\'t work.', 'wpwautoposter'), 'url' => add_query_arg(array('page' => 'wpw-auto-poster-settings'), admin_url('admin.php')), 'urltext' => __('Go to the Settings Page', 'wpwautoposter'), 'tab' => 'pinterest'));
        } elseif (empty($wpw_auto_poster_pin_sess_data)) { // Check pinterest user id is set or not
            $poster_meta->addGrantPermission($prefix . 'pin_grant', array('desc' => __('Your App doesn\'t have enough permissions to publish on Pinterest.', 'wpwautoposter'), 'url' => add_query_arg(array('page' => 'wpw-auto-poster-settings'), admin_url('admin.php')), 'urltext' => __('Go to the Settings Page', 'wpwautoposter'), 'tab' => 'pinterest'));
        }

        //add label to show status
        $poster_meta->addTweetStatus($prefix . 'pin_published_on_pin', array('name' => __('Status:', 'wpwautoposter'), 'desc' => __('Status of Pinterest board post like published/unpublished/scheduled.', 'wpwautoposter'), 'tab' => 'pinterest'));

        $post_status = get_post_meta($post_id, $prefix.'pin_published_on_pin', true );
        $post_label  = __('Publish Post On Pinterest:', 'wpwautoposter');
        $post_desc   = __('Publish this Post to Pinterest board.', 'wpwautoposter');

		if( $post_status == 1 && empty($schedule_option)) {
			$post_label = __('Re-publish Post On Pinterest:', 'wpwautoposter');
			$post_desc  = __('Re-publish this Post to Pinterest board.', 'wpwautoposter');
		} elseif ( ( $post_status == 2 ) || ( $post_status == 1 && !empty($schedule_option) ) ) {
			$post_label = __('Re-schedule Post On Pinterest:', 'wpwautoposter');
			$post_desc  = __('Re-schedule this Post to Pinterest board.', 'wpwautoposter');
		} elseif ( empty($post_status) && !empty($schedule_option) ) {
			$post_label  = __('Schedule Post On Pinterest:', 'wpwautoposter');
        	$post_desc   = __('Schedule this Post to Pinterest board.', 'wpwautoposter');
		}

        $post_desc .= '<br>'.sprintf( __( 'If you have enabled %sEnable auto posting to Pinterest%s in global settings then you do not need to check this box to publish/schedule the post. This setting is only for republishing Or rescheduling post to Pinterest.', 'wpwautoposter'), '<strong>', '</strong>');
        $post_desc .= '<br><p style="color:#c83737;" classs="wpw-auto-poster-meta"><strong>'.__('Note:', 'wpwautoposter').'</strong> '. sprintf( __( 'This setting is just an event to republish/reschedule the content, It will not save any value to %sdatabase%s.', 'wpwautoposter'), '<strong>','</strong>').'</p>';

        //post to pinterest
        $poster_meta->addPublishBox($prefix . 'post_to_pinterest', array('name' => $post_label, 'desc' => $post_desc, 'tab' => 'pinterest'));

        //post to this account
        $poster_meta->addSelect($prefix . 'pin_user_id', $pin_users, array('name' => __('Post To This Pinterest Account', 'wpwautoposter') . '(' . __('s', 'wpwautoposter') . '):', 'std' => array(''), 'desc' => __('Select an account to which you want to post. This setting overrides the global settings. Leave it  empty to use the global defaults.', 'wpwautoposter'), 'multiple' => true, 'placeholder' => __('Default', 'wpwautoposter'), 'tab' => 'pinterest'));

        //custom link to post to pinterest
        $poster_meta->addText($prefix . 'pin_custom_post_link', array('validate_func' => 'escape_html', 'name' => __('Custom Link:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom link which will be used for  the board pins. Leave it empty to use the link of the current post.', 'wpwautoposter'), 'tab' => 'pinterest'));

        //post image url
        //$poster_meta->addImage($prefix . 'pin_post_image', array('name' => __('Post Image:', 'wpwautoposter'), 'desc' => __('Here you can upload a default image which will be used for the Pinterest post. Leave it empty to use the featured image. if featured image is also blank, then it will take default image from the settings page.', 'wpwautoposter').'<br>', 'tab' => 'pinterest', 'show_path' => true));
        $poster_meta->addImage($prefix . 'pin_post_image', array('name' => __('Post Image:', 'wpwautoposter'), 'desc' => __('Here you can upload a default image which will be used for the Pinterest post. Leave it empty to use the featured image. if featured image is also blank, then it will take default image from the settings page.', 'wpwautoposter').'<br><br><strong>'.__('Note: ', 'wpwautoposter').'</strong>'.__('You need to select atleast one image, otherwise pinterest posting will not work.', 'wpwautoposter'), 'tab' => 'pinterest', 'show_path' => true));

        //publish with diffrent post title
        $poster_meta->addTextarea($prefix . 'pin_custom_status_msg', array('default' => '', 'validate_func' => 'escape_html', 'name' => __('Custom Message:', 'wpwautoposter'), 'desc' => __('Here you can enter a custom note text. Leave it empty to  use the default one from the settings page. You can use following template tags within the notes text:', 'wpwautoposter') .
            '<br /><code>{first_name}</code> - ' . __('displays the first name.', 'wpwautoposter') .
            '<br /><code>{last_name}</code> - ' . __('displays the last name.', 'wpwautoposter') .
            '<br /><code>{title}</code> - ' . __('displays the post title.', 'wpwautoposter') .
            '<br /><code>{excerpt}</code> - ' . __('displays the short post description.', 'wpwautoposter') .
            '<br /><code>{link}</code> - ' . __('displays the post link.', 'wpwautoposter') .
            '<br /><code>{sitename}</code> - ' . __('displays the name of your site.', 'wpwautoposter').'<br /><code>{hashtags}</code> - ' . __('displays the post tags as hashtags.', 'wpwautoposter').
            '<br /><code>{hashcats}</code> - ' . __('displays the post categories as hashtags.', 'wpwautoposter').
            '<br /><code>{content}</code> - ' . __('displays the post content.', 'wpwautoposter').
            '<br /><code>{content-digits}</code> - ' . __('displays the post content with define number of digits in template tag. <b>E.g. If you add template like {content-100} then it will display first 100 characters from post content.</b>', 'wpwautoposter')
            , 'tab' => 'pinterest'));
        
    }
    /** *********************************** Pinterest Tab Ends **************************************************** **/
    if ($defaulttabon) { // Check no active tab
        //meta settings are not available
        $poster_meta->addParagraph($prefix . 'no_meta_settings', array('value' => __('There is no meta settings allowed to be set for individual posts from global setting.', 'wpwautoposter')));
    }

    /*
     * Don't Forget to Close up the meta box decleration
     */
    //Finish Meta Box Decleration

    $poster_meta->Finish();
}

// add action to add custom meta box in custom post
add_action('load-post.php', 'wpw_auto_poster_add_meta_boxes');
add_action('load-post-new.php', 'wpw_auto_poster_add_meta_boxes');
