jQuery(document).ready(function ($) {


    $('.wpw-auto-poster-cats-tags-select').select2({
        placeholder: WpwAutoPosterSettings.option_label,
        width : '40%'
    });

	$('.wpw-auto-poster-cats-exclude-select').select2({
        placeholder : WpwAutoPosterSettings.option_label,
        width       : '40%'
    });

    //twitter template
    jQuery('.tw_tweet_template').change(function () {
        if (jQuery('.tw_tweet_template').val() == 'custom') {
            jQuery('#custom_template').slideDown('slow');
        } else {
            jQuery('#custom_template').slideUp('slow');
        }
    });

    //url shortener
    jQuery('.fb_url_shortener, .tw_url_shortener, .li_url_shortener, .tb_url_shortener, .dc_url_shortener, .ff_url_shortener, .ba_url_shortener, .ins_url_shortener, .pin_url_shortener').change(function () {

        var container = $(this).attr('data-content');
        //check shortner value is bitly
        if ($(this).val() == 'bitly') {
            $('.' + container + '_setting_input_bitly').slideDown('fast');
        } else {
            $('.' + container + '_setting_input_bitly').hide();
        }

        //check shortner value is shorte.st
        if ($(this).val() == 'shorte.st') {
            $('.' + container + '_setting_input_shortest').slideDown('fast');
        } else {
            $('.' + container + '_setting_input_shortest').hide();
        }
        
        //check shortner value is google_shortner
        if ($(this).val() == 'google_shortner') {
            $('.' + container + '_setting_input_g_shortner').slideDown('fast');
        } else {
            $('.' + container + '_setting_input_g_shortner').hide();
        }

        if( $(this).val() === 'wordpress' ) {
            $('#row-'+ container +'-wp-pretty-url').show();
        } else{
            $('#row-'+ container +'-wp-pretty-url').hide();
        }
    });

    //jQuery(".nav-tab-wrapper a:first").addClass("nav-tab-active");
    //jQuery(".wpw-auto-poster-content div:first").show(); 

    //  When user clicks on tab, this code will be executed
    jQuery(document).on("click", ".nav-tab-wrapper a", function () {
        //  First remove class "active" from currently active tab
        jQuery(".nav-tab-wrapper a").removeClass('nav-tab-active');

        //  Now add class "active" to the selected/clicked tab
        jQuery(this).addClass("nav-tab-active");

        //  Hide all tab content
        jQuery(".wpw-auto-poster-tab-content").hide();

        //  Here we get the href value of the selected tab
        var selected_tab = $(this).attr("href");

        //  Show the selected tab content
        jQuery(selected_tab).show();
        var tab_title = $(this).attr("attr-tab");
        jQuery(".wpw-auto-poster-tab-content").removeClass('wpw-auto-poster-selected-tab');
        $('#wpw_auto_poster_selected_tab').val(tab_title);

        //  At the end, we add return false so that the click on the link is not executed
        return false;
    });

    //Image uploader
    jQuery(document).on("click", ".wpw-auto-poster-uploader-button", function () {

        var imgfield;
        imgfield = jQuery(this).prev('input').attr('id');

        if (typeof wp == "undefined" || WpwAutoPosterSettings.new_media_ui != '1') {// check for media uploader

            tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');

            window.original_send_to_editor = window.send_to_editor;
            window.send_to_editor = function (html) {

                if (imgfield) {

                    var mediaurl = $('img', html).attr('src');
                    $('#' + imgfield).val(mediaurl);
                    tb_remove();
                    imgfield = '';

                } else {

                    window.original_send_to_editor(html);

                }
            };
            return false;

        } else {

            var file_frame;
            //window.formfield = '';

            //new media uploader
            var button = jQuery(this);

            //window.formfield = jQuery(this).closest('.file-input-advanced');

            // If the media frame already exists, reopen it.
            if (file_frame) {
                //file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
                file_frame.open();
                return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                frame: 'post',
                state: 'insert',
                //title: button.data( 'uploader_title' ),
                /*button: {
                 text: button.data( 'uploader_button_text' ),
                 },*/
                multiple: false  // Set to true to allow multiple files to be selected
            });

            file_frame.on('menu:render:default', function (view) {
                // Store our views in an object.
                var views = {};

                // Unset default menu items
                view.unset('library-separator');
                view.unset('gallery');
                view.unset('featured-image');
                view.unset('embed');

                // Initialize the views in our view object.
                view.set(views);
            });

            // When an image is selected, run a callback.
            file_frame.on('insert', function () {
                // Get selected size from media uploader
                var selected_size = $('.attachment-display-settings .size').val();

                var selection = file_frame.state().get('selection');
                selection.each(function (attachment, index) {
                    attachment = attachment.toJSON();

                    // Selected attachment url from media uploader
                    var attachment_url = attachment.sizes[selected_size].url;

                    if (index == 0) {
                        // place first attachment in field
                        //window.formfield.find('.wpw-auto-poster-upload-file-link').val(attachment.url);
                        $('#' + imgfield).val(attachment_url);

                    } else {
                        $('#' + imgfield).val(attachment_url);
                    }
                });
            });

            // Finally, open the modal
            file_frame.open();

        }
    });

    //reset confirmation
    jQuery(document).on("click", ".wpw-auto-poster-reset-button", function () {

        var ans;
        ans = confirm(WpwAutoPosterSettings.confirmmsg);

        if (ans) {
            return true;
        } else {
            return false;
        }

    });

    //posted logs delete confirmation
    jQuery(document).on("click", ".wpw-auto-poster-logs-delete", function () {

        var ans;
        ans = confirm(WpwAutoPosterSettings.deleteconfirmmsg);

        if (ans) {
            return true;
        } else {
            return false;
        }

    });

    //add more account details for facebook
    jQuery(document).on('click', '.wpw-auto-poster-add-more-fb-account', function () {
        var jQueryfirst = jQuery(this).parents('.wpw-auto-poster-facebook-settings').find('.wpw-auto-poster-facebook-account-details:last');
        var last_row_id = parseInt(jQueryfirst.attr('data-row-id'));
        last_row_id = last_row_id + 1;

        var clone_row = jQueryfirst.clone();

        clone_row.insertAfter(jQueryfirst).show();
        clone_row.find('.wpw-grant-reset-data').html('');

        jQuery(this).parents('.wpw-auto-poster-facebook-settings').find('.wpw-auto-poster-facebook-account-details:last .wpw-auto-poster-facebook-app-id').attr('name', 'wpw_auto_poster_options[facebook_keys][' + last_row_id + '][app_id]').val('');
        jQuery(this).parents('.wpw-auto-poster-facebook-settings').find('.wpw-auto-poster-facebook-account-details:last .wpw-auto-poster-facebook-app-secret').attr('name', 'wpw_auto_poster_options[facebook_keys][' + last_row_id + '][app_secret]').val('');
        jQuery(this).parents('.wpw-auto-poster-facebook-settings').find('.wpw-auto-poster-facebook-account-details:last .fb-oauth-url').val('');
        jQuery(this).parents('.wpw-auto-poster-facebook-settings').find('.wpw-auto-poster-facebook-account-details:last .copy-clipboard').remove();        
        jQuery(this).parents('.wpw-auto-poster-facebook-settings').find('.wpw-auto-poster-facebook-account-details:last .wpw-auto-poster-facebook-remove').show();
        jQuery(this).parents('.wpw-auto-poster-facebook-settings').find('.wpw-auto-poster-facebook-account-details:last').attr('data-row-id', last_row_id);
        return false;
    });

    //delete account details for facebook
    jQuery(document).on('click', '.wpw-auto-poster-delete-fb-account', function () {

        var jQueryparent = jQuery(this).parents('.wpw-auto-poster-facebook-account-details');
        jQueryparent.remove();

        return false;
    });

    // copy Valid oauth url to clipboard
    jQuery( document).on('click', '.copy-clipboard', function(){
        var app_id = jQuery(this).data('appid');
        copy_board = jQuery('#fb-oauth-url-'+app_id);
        var oauth_url = copy_board.val();
        if( oauth_url != ""){
            copy_board.select();
            document.execCommand("Copy");
            jQuery( this ).parent().append( '<div class="wpw-auto-poster-fade-message">'+ WpwAutoPosterSettings.copy_message +'</div>' );
            jQuery( ".wpw-auto-poster-fade-message" ).fadeOut( 3000, function() {
                jQuery( '.wpw-auto-poster-fade-message' ).remove();
            });
        }
    });

    //add more account details for twitter
    jQuery(document).on('click', '.wpw-auto-poster-add-more-account', function () {
        var jQueryfirst = jQuery(this).parents('.wpw-auto-poster-twitter-settings').find('.wpw-auto-poster-twitter-account-details:last');
        var last_row_id = parseInt(jQueryfirst.attr('data-row-id'));
        last_row_id = last_row_id + 1;
        jQueryfirst.clone().insertAfter(jQueryfirst).show();
        jQuery(this).parents('.wpw-auto-poster-twitter-settings').find('.wpw-auto-poster-twitter-account-details:last .wpw-auto-poster-twitter-consumer-key').attr('name', 'wpw_auto_poster_options[twitter_keys][' + last_row_id + '][consumer_key]').val('');
        jQuery(this).parents('.wpw-auto-poster-twitter-settings').find('.wpw-auto-poster-twitter-account-details:last .wpw-auto-poster-twitter-consumer-secret').attr('name', 'wpw_auto_poster_options[twitter_keys][' + last_row_id + '][consumer_secret]').val('');
        jQuery(this).parents('.wpw-auto-poster-twitter-settings').find('.wpw-auto-poster-twitter-account-details:last .wpw-auto-poster-twitter-oauth-token').attr('name', 'wpw_auto_poster_options[twitter_keys][' + last_row_id + '][oauth_token]').val('');
        jQuery(this).parents('.wpw-auto-poster-twitter-settings').find('.wpw-auto-poster-twitter-account-details:last .wpw-auto-poster-twitter-oauth-secret').attr('name', 'wpw_auto_poster_options[twitter_keys][' + last_row_id + '][oauth_secret]').val('');
        jQuery(this).parents('.wpw-auto-poster-twitter-settings').find('.wpw-auto-poster-twitter-account-details:last .wpw-auto-poster-twitter-remove').show();
        jQuery(this).parents('.wpw-auto-poster-twitter-settings').find('.wpw-auto-poster-twitter-account-details:last').attr('data-row-id', last_row_id);
        return false;
    });

    //delete account details for twitter
    jQuery(document).on('click', '.wpw-auto-poster-delete-account', function () {

        var jQueryparent = jQuery(this).parents('.wpw-auto-poster-twitter-account-details');
        jQueryparent.remove();

        return false;
    });

    //add more account details for instagram
    jQuery(document).on('click', '.wpw-auto-poster-add-more-ins-account', function () {
        var jQueryfirst = jQuery(this).parents('.wpw-auto-poster-instagram-settings').find('.wpw-auto-poster-instagram-account-details:last');
        var last_row_id = parseInt(jQueryfirst.attr('data-row-id'));
        last_row_id = last_row_id + 1;

        var clone_row = jQueryfirst.clone();

        clone_row.insertAfter(jQueryfirst).show();
        clone_row.find('.wpw-grant-reset-data').html('');

        jQuery(this).parents('.wpw-auto-poster-instagram-settings').find('.wpw-auto-poster-instagram-account-details:last .wpw-auto-poster-instagram-username').attr('name', 'wpw_auto_poster_options[instagram_keys][' + last_row_id + '][username]').val('');
        jQuery(this).parents('.wpw-auto-poster-instagram-settings').find('.wpw-auto-poster-instagram-account-details:last .wpw-auto-poster-instagram-password').attr('name', 'wpw_auto_poster_options[instagram_keys][' + last_row_id + '][password]').val('');
        jQuery(this).parents('.wpw-auto-poster-instagram-settings').find('.wpw-auto-poster-instagram-account-details:last .wpw-auto-poster-instagram-remove').show();
        jQuery(this).parents('.wpw-auto-poster-instagram-settings').find('.wpw-auto-poster-instagram-account-details:last').attr('data-row-id', last_row_id);
        return false;
    });

    //delete account details for instagram
    jQuery(document).on('click', '.wpw-auto-poster-delete-ins-account', function () {

        var jQueryparent = jQuery(this).parents('.wpw-auto-poster-instagram-account-details');
        jQueryparent.remove();

        return false;
    });

    //add more account details for pinterest
    jQuery(document).on('click', '.wpw-auto-poster-add-more-pin-account', function () {
        var jQueryfirst = jQuery(this).parents('.wpw-auto-poster-pinterest-settings').find('.wpw-auto-poster-pinterest-account-details:last');
        var last_row_id = parseInt(jQueryfirst.attr('data-row-id'));
        last_row_id = last_row_id + 1;

        var clone_row = jQueryfirst.clone();

        clone_row.insertAfter(jQueryfirst).show();
        clone_row.find('.wpw-grant-reset-data').html('');

        jQuery(this).parents('.wpw-auto-poster-pinterest-settings').find('.wpw-auto-poster-pinterest-account-details:last .wpw-auto-poster-pinterest-app-id').attr('name', 'wpw_auto_poster_options[pinterest_keys][' + last_row_id + '][app_id]').val('');
        jQuery(this).parents('.wpw-auto-poster-pinterest-settings').find('.wpw-auto-poster-pinterest-account-details:last .wpw-auto-poster-pinterest-app-secret').attr('name', 'wpw_auto_poster_options[pinterest_keys][' + last_row_id + '][app_secret]').val('');
        jQuery(this).parents('.wpw-auto-poster-pinterest-settings').find('.wpw-auto-poster-pinterest-account-details:last .wpw-auto-poster-pinterest-remove').show();
        jQuery(this).parents('.wpw-auto-poster-pinterest-settings').find('.wpw-auto-poster-pinterest-account-details:last').attr('data-row-id', last_row_id);
        return false;
    });

    //delete account details for pinterest
    jQuery(document).on('click', '.wpw-auto-poster-delete-pin-account', function () {

        var jQueryparent = jQuery(this).parents('.wpw-auto-poster-pinterest-account-details');
        jQueryparent.remove();

        return false;
    });

    //add more account details for linkedin
    jQuery(document).on('click', '.wpw-auto-poster-add-more-li-account', function () {
        var jQueryfirst = jQuery(this).parents('.wpw-auto-poster-linkedin-settings').find('.wpw-auto-poster-linkedin-account-details:last');
        var last_row_id = parseInt(jQueryfirst.attr('data-row-id'));
        last_row_id = last_row_id + 1;

        var clone_row = jQueryfirst.clone();

        clone_row.insertAfter(jQueryfirst).show();
        clone_row.find('.wpw-grant-reset-data').html('');

        jQuery(this).parents('.wpw-auto-poster-linkedin-settings').find('.wpw-auto-poster-linkedin-account-details:last .wpw-auto-poster-linkedin-app-id').attr('name', 'wpw_auto_poster_options[linkedin_keys][' + last_row_id + '][app_id]').val('');
        jQuery(this).parents('.wpw-auto-poster-linkedin-settings').find('.wpw-auto-poster-linkedin-account-details:last .wpw-auto-poster-linkedin-app-secret').attr('name', 'wpw_auto_poster_options[linkedin_keys][' + last_row_id + '][app_secret]').val('');
        jQuery(this).parents('.wpw-auto-poster-linkedin-settings').find('.wpw-auto-poster-linkedin-account-details:last .li-oauth-url').val('');
        jQuery(this).parents('.wpw-auto-poster-linkedin-settings').find('.wpw-auto-poster-linkedin-account-details:last .copy-clipboard').remove(); 
        jQuery(this).parents('.wpw-auto-poster-linkedin-settings').find('.wpw-auto-poster-linkedin-account-details:last .wpw-auto-poster-linkedin-remove').show();
        jQuery(this).parents('.wpw-auto-poster-linkedin-settings').find('.wpw-auto-poster-linkedin-account-details:last').attr('data-row-id', last_row_id);
        return false;
    });

    //delete account details for linkedin
    jQuery(document).on('click', '.wpw-auto-poster-delete-li-account', function () {

        var jQueryparent = jQuery(this).parents('.wpw-auto-poster-linkedin-account-details');
        jQueryparent.remove();

        return false;
    });

    // copy Valid oauth url to clipboard for linkedin
    jQuery( document).on('click', '.copy-clipboard', function(){
        var app_id = jQuery(this).data('appid');
        copy_board = jQuery('#li-oauth-url-'+app_id);
        var oauth_url = copy_board.val();
        if( oauth_url != ""){
            copy_board.select();
            document.execCommand("Copy");
            jQuery( this ).parent().append( '<div class="wpw-auto-poster-fade-message">'+ WpwAutoPosterSettings.copy_message +'</div>' );
            jQuery( ".wpw-auto-poster-fade-message" ).fadeOut( 3000, function() {
                jQuery( '.wpw-auto-poster-fade-message' ).remove();
            });
        }
    });


    //on click of view details from posted logs list
    jQuery(document).on("click", ".wpw-auto-poster-meta-view-details", function () {

        var popupcontent = jQuery(this).parent().find('.wpw-auto-poster-popup-content');
        popupcontent.show();
        jQuery(this).parent().find('.wpw-auto-poster-popup-overlay').show();
        jQuery('html, body').animate({scrollTop: popupcontent.offset().top - 80}, 500);

    });

    //on click of close button or overlay
    jQuery(document).on("click", ".wpw-auto-poster-popup-overlay, .wpw-auto-poster-close-button", function () {

        jQuery('.wpw-auto-poster-popup-content').hide();
        jQuery('.wpw-auto-poster-popup-overlay').hide();
    });

    // apply chosen for posting logs
    jQuery(".wpw-auto-poster-form select").each(function () {
        jQuery(this).css('width', '300px').chosen({search_contains: true});
    });

    $(document).on('change', '.wpw-auto-poster-schedule-option', function () {
        var schedule = $(this).val();

        $('.wpw-auto-poster-custom-schedule-wrap').hide();
        if (schedule == 'daily') {

            $('.wpw-auto-poster-custom-schedule-wrap').show();
        }       

        if ($('#wpw_auto_poster_random_posting').is(':checked')) {
            $('.wpw-auto-poster-schedule-time').hide();
        }

        // Show / hide schedule limit option
        $('.wpw-auto-poster-schedule-limit').show();
        if (schedule == '') {
            $('.wpw-auto-poster-schedule-limit').hide();
            $('#wpw-auto-poster-schedule-order-row').hide();
        } else{
            $('#wpw-auto-poster-schedule-order-row').show();
        }

        // Code to hide and unhide custom minutes box 
        if( schedule == 'wpw_custom_mins' ){
            $('#wpw-auto-poster-custom-minute-box').show();
        } else{
            $('#wpw-auto-poster-custom-minute-box').hide();
        }

        /**Twice daily*/
        $('.wpw-auto-poster-custom-twice-schedule-wrap').hide();
        if (schedule == 'twicedaily') {
            $('.wpw-auto-poster-custom-twice-schedule-wrap').show();
        }

        if ($('#wpw_auto_poster_twice_random_posting').is(':checked')) {
            $('.wpw-auto-poster-schedule-twice-time').hide();
        }

    });

    // Posting type radio button
    $(document).on('click', '.wpw-auto-poster-random-posting', function () {
        if ($(this).val() == 1) {
            $('.wpw-auto-poster-schedule-time').hide();
        } else {
            $('.wpw-auto-poster-schedule-time').show();
        }
    });

    // Posting type twice daily radio button
    $(document).on('click', '.wpw-auto-poster-twice-random-posting', function () {
        if ($(this).val() == 1) {
            $('.wpw-auto-poster-schedule-twice-time').hide();
        } else {
            $('.wpw-auto-poster-schedule-twice-time').show();
        }
    });

    $(document).on('change', '#wpw_auto_poster_li_type_post_method', function () {

        $(this).parent().parent().find('.wpw-auto-poster-li-posting-wrap').hide();
        var posting_type = $(this).val();
        $(this).parent().parent().find('.wpw-auto-poster-li-' + posting_type + '-posting').show();
        //$( '.wpw-auto-poster-li-' + posting_type + '-posting' ).show();
    });
    
    // function to toggle Tweet image
    function woo_vou_toggle_tweet_image() {
        if( $("input[name='wpw_auto_poster_options[tw_disable_image_tweet]']").is(':checked') ) {
            $("#wpw_sap_tw_tweet_img").fadeOut();            
        } else {
            $("#wpw_sap_tw_tweet_img").fadeIn();
        }
    }

    // Setting page onload show/hide Tweet image if Disable Image posting checked
    woo_vou_toggle_tweet_image();    
    $(document).on('click', "input[name='wpw_auto_poster_options[tw_disable_image_tweet]']", function() {
        woo_vou_toggle_tweet_image();
    });

    // AJAX on page load to get categories based on post type selected
    wpw_auto_post_load_cat('no');

    // AJAX when post type is changed to get categories based on post type selected
    $('#wpw_auto_poster_post_type').change(function(){
    	wpw_auto_post_load_cat('yes');
    });

    // Function to fetch categories from post type
    function wpw_auto_post_load_cat(open){
    	// Get post type value
    	var post_type_val = $('#wpw_auto_poster_post_type').val();

    	// If post type value is not empty
    	if($.trim(post_type_val)){
    		// Create data
			var data = {
							action			: 'wpw_auto_poster_get_category',
							post_type_val	: post_type_val,
							sel_category_id	: WpwAutoPosterSettings.sel_category_id
						};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post( WpwAutoPosterSettings.ajaxurl, data, function(response) {

				$('#wpw_auto_poster_cat_id').html(response); // Append response to select box
        		$('#wpw_auto_poster_cat_id').trigger("chosen:updated"); // Trigger change event for adding data in chosen select
        		if(open == 'yes') { // If we need to open the select box
        			$('#wpw_auto_poster_cat_id').trigger('chosen:open'); // Trigger event to open chosen select
        		}
			});
    	}
    }

    //getCheckedPostType();

    $("input[name='wpw_auto_poster_options[enable_facebook_for][]']").change(function() {
        getCheckedPostType('facebook','fb');
    });
    $("input[name='wpw_auto_poster_options[enable_twitter_for][]']").change(function() {
        getCheckedPostType('twitter','tw');
    });
    $("input[name='wpw_auto_poster_options[enable_linkedin_for][]']").change(function() {
        getCheckedPostType('linkedin','li');
    });
    $("input[name='wpw_auto_poster_options[enable_tumblr_for][]']").change(function() {
        getCheckedPostType('tumblr','tb');
    });

    $("input[name='wpw_auto_poster_options[enable_bufferapp_for][]']").change(function() {
        getCheckedPostType('bufferapp','ba');
    });

    $("input[name='wpw_auto_poster_options[enable_instagram_for][]']").change(function() {
        getCheckedPostType('instagram','ins');
    });

    $("input[name='wpw_auto_poster_options[enable_pinterest_for][]']").change(function() {
        getCheckedPostType('pinterest','pin');
    });

    $("select[name='wpw_auto_poster_reposter_options[schedule_posting_order]']").change(function() {
        if( $(this).val() == 'rand' ){
            $("select[name='wpw_auto_poster_reposter_options[schedule_posting_order_behaviour]']").hide();
        } else{
            $("select[name='wpw_auto_poster_reposter_options[schedule_posting_order_behaviour]']").show();
        }
    });

    $("select[name='wpw_auto_poster_options[schedule_wallpost_order]']").change(function() {
        if( $(this).val() == 'rand' ){
            $("select[name='wpw_auto_poster_options[schedule_wallpost_order_behaviour]']").hide();
        } else{
            $("select[name='wpw_auto_poster_options[schedule_wallpost_order_behaviour]']").show();
        }
    });

    function getCheckedPostType (type, slug) {
        
        var post_type = [];
        var checkCount = $( "input[name='wpw_auto_poster_options[enable_"+type+"_for][]']:checked" ).length;
        $("input[name='wpw_auto_poster_options[enable_"+type+"_for][]']:checked").each(function (i) {
            post_type[i] = $(this).val();
        });

        var selected_tags = $("."+slug+"_post_type_tags").select2("val");
        var selected_cats = $("."+slug+"_post_type_cats").select2("val");

        // Create data
        var data = {
            action          : 'wpw_auto_poster_get_taxonomies',
            post_type_val   : post_type,
            selected_tags   : selected_tags,
            selected_cats   : selected_cats,
            social_type     : slug
        };

        $('.wpw-ajax-loader').css("visibility", "visible");
        $.post( WpwAutoPosterSettings.ajaxurl, data, function(response) {

            $('.wpw-ajax-loader').css("visibility", "hidden");
            var result = JSON.parse(response);
            if(result) {
                // Append response to categories and tags select box respectively
                $('.'+slug+'_post_type_cats').html(result['data']['categories']); 
                $('.'+slug+'_post_type_tags').html(result['data']['tags']);
            } else {
                // Clear select box if result is empty
                $('.'+slug+'_post_type_cats').html('');
                $('.'+slug+'_post_type_tags').html('');
            }
        });
    }

    function getCheckedreposterPostType (type, slug) {
        
        var post_type = [];
        var checkCount = $( "input[name='wpw_auto_poster_reposter_options[enable_"+type+"_for][]']:checked" ).length;
        $("input[name='wpw_auto_poster_reposter_options[enable_"+type+"_for][]']:checked").each(function (i) {
            post_type[i] = $(this).val();
        });

        var selected_cats = $("."+slug+"_post_type_cats").select2("val");

        // Create data
        var data = {
            action          : 'wpw_auto_poster_get_taxonomies',
            post_type_val   : post_type,
            selected_tags   : '',
            selected_cats   : selected_cats,
            social_type     : slug
        };

        $('.wpw-ajax-loader').css("visibility", "visible");
        $.post( WpwAutoPosterSettings.ajaxurl, data, function(response) {

            $('.wpw-ajax-loader').css("visibility", "hidden");
            var result = JSON.parse(response);
            if(result) {
                // Append response to categories and tags select box respectively
                $('.'+slug+'_post_type_cats').html(result['data']['categories']);
            } else {
                // Clear select box if result is empty
                $('.'+slug+'_post_type_cats').html('');
            }
        });
    }
        
       $('#wpw_auto_graph_start_date').datepicker({
    	maxDate: 'today',
    	changeMonth: true,
		changeYear: true,
    	onSelect: function( selectedDate ) {
        	$( "#wpw_auto_graph_end_date" ).datepicker( "option", "minDate", selectedDate );
      	}
    });
    $('#wpw_auto_graph_end_date').datepicker({
    	maxDate: 'today',
    	changeMonth: true,
		changeYear: true,
    	onSelect: function( selectedDate ) {
        	$( "#wpw_auto_graph_start_date" ).datepicker( "option", "maxDate", selectedDate );
      	}
    });

    //Filtering Graph Data Process
    $(document).on('click', '.wpw_auto_graph_filter', function () {
		get_poster_logs_json_graph();
    });

    //Filtering Graph Data Process
    $(document).on('change', 'input[type=radio][name=wpw_auto_filter_type], #wpw_auto_graph_social_type', function (){

    	if (this.value == 'custom') {
    		$('.wp-auto-custom-wrap').show();
    	}else{
    		var filter_type = $("input[type=radio][name=wpw_auto_filter_type]:checked").val();
			if( filter_type != 'custom' ){
				$('.wp-auto-custom-wrap').hide();
			}
    		get_poster_logs_json_graph();
    	}
    });

    //Onload logs report page only display
    if( $('#wpw-auto-logs-graph').length ){
           get_poster_logs_json_graph();        
    }

    //Build Graph
    function get_poster_logs_json_graph() {

    	$('.wpw-auto-loader-wrap').show();

    	var social_type = start_date = end_date = '';
    	var filter_type = $("input[type=radio][name=wpw_auto_filter_type]:checked").val();
    	var social_type = $('#wpw_auto_graph_social_type').val();

    	if( filter_type == 'custom'){
	    	//Filter data
	    	var start_date  = $('#wpw_auto_graph_start_date').val();
	    	var end_date    = $('#wpw_auto_graph_end_date').val();
    	}

    	var data = {
					action 		: 'wpw_auto_poster_logs_graph',
					social_type : social_type,
					start_date  : start_date,
					end_date    : end_date,
					filter_type : filter_type,
				   };

		//Ajax send
		$.post( WpwAutoPosterSettings.ajaxurl, data, function(response) {

			var graph_data = $.parseJSON(response);

			if(graph_data){

				google.charts.load('current', {'packages':['corechart']});
	    		google.charts.setOnLoadCallback( function (){

		    		var data = google.visualization.arrayToDataTable(graph_data);

		        	var options = {
						    title: WpwAutoPosterSettings.report_title,
                            titlePosition: 'center',
						    curveType: 'function',
						    legend: { position: 'right' },
						    width: 1150,
						    height: 600,
						    vAxis: {
						    	  format: '#,###',
						    	  minValue: 4,
						          viewWindow:{
						            min:0
						          }
						        }
						}

					var chart = new google.visualization.LineChart(document.getElementById('wpw-auto-logs-graph'));
					chart.draw(data, options);
	    		});
			}else{
				alert('no data available');
			}
			$('.wpw-auto-loader-wrap').hide();
		});
    }

    // code to handle hide and shot Use Google Analytics with radio 
    $( document).on( 'change', 'input[name="wpw_auto_poster_options[enable_google_tracking]"]', function(){
        if( $(this).is(":checked") ){
            $('#google_tracking_script_row').show();
            if( $('input[name="wpw_auto_poster_options[google_tracking_script]"]:checked').val() == 'yes' ){
                $('#google_tracking_code_row').show();
            }
        } else{
            $('#google_tracking_script_row').hide();
            $('#google_tracking_code_row').hide();
        }
    });

    // code to handle hide and shot Use Google Analytics textarea 
    $(document).on( 'change', 'input[name="wpw_auto_poster_options[google_tracking_script]"]', function() {
        if( $(this).val() == 'yes'){
            $('#google_tracking_code_row').show();
        } else{
            $('#google_tracking_code_row').hide();
        }
    });

    // Filter by Date for Scheduled/Published post in Manage Schedule
    /*$('#wpw_auto_start_date').datepicker({
        //maxDate: 'today',
        changeMonth: true,
        changeYear: true,
        onSelect: function( selectedDate ) {
            $( "#wpw_auto_start_date" ).datepicker( "option", "minDate", selectedDate );
        }
    });*/

    /*$('#wpw_auto_end_date').datepicker({
        //maxDate: 'today',
        changeMonth: true,
        changeYear: true,
        onSelect: function( selectedDate ) {
            $( "#wpw_auto_end_date" ).datepicker( "option", "maxDate", selectedDate );
        }
    });*/
    if( $('#wpw_auto_start_date').length ) {
        $('#wpw_auto_start_date').datetimepicker({
            dateFormat: WpwAutoPosterAdmin.date_format,
            //minDate: new Date(WpwAutoPosterAdmin.current_date),
            timeFormat: WpwAutoPosterAdmin.time_format,
            showMinute : false,
            ampm: false,
            stepMinute:60,
            stepHour: 1,
            currentText: 'Now',
            showOn : 'focus',
            onSelect: function(selected) {
                $("#wpw_auto_end_date").datetimepicker("option", "minDate", selected);
            }
        });
    }
    if( $('#wpw_auto_end_date').length ) {
        $('#wpw_auto_end_date').datetimepicker({
            dateFormat: WpwAutoPosterAdmin.date_format,
            minDate: $('#wpw_auto_start_date').datetimepicker('getDate'),
            timeFormat: WpwAutoPosterAdmin.time_format,
            showMinute : false,
            ampm: false,
            stepMinute:60,
            stepHour: 1,
            currentText: 'Now',
           
        });
    }

    $(".wpw-auto-datepicker").keypress(function (e) {
        //if the letter is not digit then display error and don't type anything
        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            //display error message
        return false;
        }
    });

    showDateFilter ( $("#wpw_auto_poster_social_status option:selected").val() ) ;

    $(document).on('change', '#wpw_auto_poster_social_status', function (){

        showDateFilter ( this.value ) ;

    });

    $(document).on( 'change', 'input[name="wpw_auto_poster_reposter_options[schedule_wallpost_repeat]"]', function(){
        if( $(this).val() == 'yes' ) {
            $('td.repeat-times').show();
            $(this).closest('td').css('width','59%');
        } else{
            $('td.repeat-times').hide();
            $(this).closest('td').css('width','');
        }
    });

    function showDateFilter ( social_status ) {

        $('.wp-auto-date-filter').hide();

        if (social_status == '2' || social_status == '1') {
            $('.wp-auto-date-filter').show();
        }
    }


    $(document).on('change', 'input[name="wpw_auto_poster_options[facebook_auth_options]"]', 
        function(){
            if( $(this).val() == 'graph'){
                $('#facebook-graph-api').show();
                $('#facebook-rest-api').hide();
            } else{
                $('#facebook-graph-api').hide();
                $('#facebook-rest-api').show();
            }
        }
    );

    // handle to generate access token response for the facebook
    $(document).on('click', '.wpw-auto-poster-grant-fb-android', function() {

        var link = $(this);
        $(this).attr('disabled','true');

        var username = $('#wpw_auto_poster_facebook_user').val();
        var password = $('#wpw_auto_poster_facebook_password').val();
        var fb_rest_type = $('input[name="wpw_auto_poster_options[facebook_rest_type]"]:checked').val();

        var data = {
                    action : 'wpw_auto_poster_fb_android_get_url',
                    username : username,
                    password : password,
                    fb_rest_type : fb_rest_type
                };

        $(this).addClass('active');

        $('.wpw-grant-extend-loader').show();
        $.post( WpwAutoPosterSettings.ajaxurl, data, function(response) {

            if( response.type == 'success' ){
                $('#rest-result').html('');
                $('#rest-result').removeClass('error');
                $('#token-frame').show();
                $('#token-result').html('<iframe src="'+response.message+'" frameborder="1" scrolling="yes" id="fbFrame"></iframe>'); 
                $('#save-fb-account-button').show();
            } else{
                $('#token-frame').hide();
                $('#save-fb-account-button').hide();
                $('#rest-result').addClass('error');
                $('#rest-result').html(response.message);
            }

            $('.wpw-grant-extend-loader').hide();
            link.removeAttr('disabled');
        });
        
    });

    // code to get access token response from user and add facebook account
    $(document).on('click', '#add-fb-account', function(){
        var link = $(this);

        $(this).attr('disabled','true');
        var fb_access_token = $('#fb_access_token').val();

        var data = {
                    action : 'wpw_auto_poster_fb_android_get_token',
                    fb_access_token : fb_access_token
                };

        $(this).addClass('active');

        $('.wpw-validate-token-loader').show();

        $.post( WpwAutoPosterSettings.ajaxurl, data, function(response) {
            $('#fb_access_token').val('');
            if( response.type == 'success' ){
                $('#rest-result').addClass('success');
                $('#rest-result').html(response.message);
                window.location.reload();
            } else{
                $('#rest-result').addClass('error');
                $('#rest-result').html(response.message);
            }

            $('.wpw-validate-token-loader').hide();
            link.removeAttr('disabled');
        });
    });

    $(document).on('change', 'select[name="wpw_auto_poster_options[fb_post_share_type]"]', function(){
        if( $(this).val() != 'image_posting' ){
            $('.fb-image-notes').hide();            
        } else{
            $('.fb-image-notes').show();
        }
    });

});