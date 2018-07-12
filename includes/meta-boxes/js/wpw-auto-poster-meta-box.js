jQuery(document).ready(function (jQuery) {

	//click on  button
	jQuery(document).on('focusout', '#_wpweb_li_post_link, #_wpweb_tb_custom_post_link, #_wpweb_ba_custom_post_link, #_wpweb_fb_custom_post_link, #_wpweb_pin_custom_post_link', function(e) {

		var url_pattern	= /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;

		if( ! url_pattern.test( jQuery(this).val() ) ) {

			jQuery( this ).parent().append( '<div class="wpw-auto-poster-fade-error">'+ WPSAPMeta.invalid_url +'</div>' );

			jQuery( ".wpw-auto-poster-fade-error" ).fadeOut( 3000, function() {
				jQuery( '.wpw-auto-poster-fade-error' ).remove();
			});

			jQuery( this ).val('');
			return false;
		}
	});

    //edit tweet template
    jQuery(document).on("click", ".wpw-auto-poster-tweet-template span", function () {
        jQuery(this).parent().hide();
        jQuery(this).parent().next('.wpw-auto-poster-tweet-edit-template').show();
        var edittempval = jQuery(this).html();
        jQuery(this).parent().next('.wpw-auto-poster-tweet-edit-template').children('.wpw-auto-poster-meta-textarea').val(edittempval);
    });

    //cancel tweet template
    jQuery(document).on("click", ".wpw-auto-poster-tweet-template-cancel", function () {
        jQuery(this).parent('.wpw-auto-poster-tweet-edit-template').hide();
        jQuery(this).parent().siblings('.wpw-auto-poster-tweet-template').show();
    });

    //update tweet template
    jQuery(document).on("click", ".wpw-auto-poster-tweet-template-update", function () {

        var template = jQuery(this).siblings('textarea').val();
        var metaname = jQuery(this).siblings('textarea').attr('id');
        var clickel = jQuery(this);

        var data = {
            action: 'wpw_auto_poster_update_tweet_template',
            postid: jQuery(this).attr('id'),
            temp: template,
            meta: metaname,
            newtemp: template,
            title: jQuery('input#title').val()
        };
        jQuery(this).siblings('.wpw-auto-poster-tweet-template-loader').show();
        jQuery.post(ajaxurl, data, function (response) {
            //alert(response);
            clickel.siblings('.wpw-auto-poster-tweet-template-loader').hide();

            var result = jQuery.parseJSON(response);
            var metaname = clickel.siblings('textarea').attr('id');
            //clickel.siblings('#'+metaname).val(result.newtemp);
            clickel.parent('.wpw-auto-poster-tweet-edit-template').hide();
            clickel.parent().siblings('.wpw-auto-poster-tweet-template').show();
            clickel.parent().siblings('.wpw-auto-poster-tweet-template').children('span').html(template);

            clickel.parent().siblings('.wpw-auto-poster-tweet-template').show();

            if (result.success == '1') {
                var preview = clickel.parent().parent().parent('tr');
                preview.next('tr').children('td').children('label#' + metaname).html(result.template);
                preview.next('tr').children('td').children('.wpw-auto-poster-tweet-preview-count').html(result.template.length);

                preview.prev('tr').children('td').children('.wpw-auto-poster-tweet-mode').html('Manual');
                var modeid = preview.prev('tr').children('td').children('.wpw-auto-poster-tweet-mode').attr('id');
                preview.prev('tr').children('td').children('.wpw-auto-poster-tweet-mode').siblings('input#' + modeid).val('1');
                preview.prev('tr').children('td').children('.wpw-auto-poster-tweet-mode').removeClass('tweet-mode-full-width');
                preview.prev('tr').children('td').children('.wpw-auto-poster-reset-tweet-template').show();

                if( result.template.length > 280 ){
                	preview.next('tr').children('td').children('#tweet-warning-message').remove();
                    preview.next('tr').children('td').append('<div class="tweet-template-warning-message" id="tweet-warning-message">'+WpwAutoPosterMeta.tweet_exceed_message+'</div>');
                    preview.next('tr').children('td').children('.wpw-auto-poster-tweet-preview-count').addClass('red-color');
                } else{
                    preview.next('tr').children('td').children('#tweet-warning-message').remove();
                    preview.next('tr').children('td').children('.wpw-auto-poster-tweet-preview-count').removeClass('red-color');
                }
            }

        });

    });

    //update count to preivew
    /*jQuery('.wpw-auto-poster-tweet-edit-template .wpw-auto-poster-meta-textarea').on('keypress', function () {

        var count = jQuery(this).val().length;
        var countel = jQuery(this).parent().parent().parent('tr');
        countel.next('tr').children('td').children('.wpw-auto-poster-tweet-preview-count').html(count);
        //countel.prev( 'tr' ).children( 'td' ).children( '.wpw-auto-poster-tweet-mode' ).html( 'Manual' );
        //var modeid = countel.prev( 'tr' ).children( 'td' ).children( '.wpw-auto-poster-tweet-mode' ).attr( 'id' );
        //countel.prev( 'tr' ).children( 'td' ).children( '.wpw-auto-poster-tweet-mode' ).siblings('input#'+modeid).val( '1' );
        //countel.prev( 'tr' ).children( 'td' ).children( '.wpw-auto-poster-tweet-mode' ).removeClass('tweet-mode-full-width');
        //countel.prev( 'tr' ).children( 'td' ).children( '.wpw-auto-poster-reset-tweet-template' ).show();

    });*/

    //reset template
    jQuery(document).on("click", ".wpw-auto-poster-reset-tweet-template", function () {

        var clickel = jQuery(this);
        var clickparent = jQuery(this).parent().parent('tr');
        var metaname = clickparent.next('tr').children('td').children('.wpw-auto-poster-tweet-edit-template').children('textarea').attr('id');

        var data = {
            action: 'wpw_auto_poster_reset_tweet_template',
            postid: jQuery(this).attr('id'),
            meta: metaname,
            title: jQuery('input#title').val(),
        };

        jQuery(this).siblings('.wpw-auto-poster-tweet-template-loader').show();
        clickel.hide();

        jQuery.post(ajaxurl, data, function (response) {

            clickel.siblings('.wpw-auto-poster-tweet-template-loader').hide();
            clickel.show();

            var modeid = clickel.siblings('.wpw-auto-poster-tweet-mode').attr('id');
            clickel.siblings('input#' + modeid).val('0');

            var result = jQuery.parseJSON(response);

            if (result.success == '1') {
                clickel.siblings('.wpw-auto-poster-tweet-mode').html('Automatic');
                clickel.siblings('.wpw-auto-poster-tweet-mode').addClass('tweet-mode-full-width');
                clickel.hide();
                clickparent.next('tr').children('td').children('.wpw-auto-poster-tweet-template').children('span').html(result.newtemp);
                clickparent.next('tr').children('td').children('.wpw-auto-poster-tweet-edit-template').children('textarea').val(result.newtemp);
                clickparent.next('tr').next('tr').children('td').children('.wpw-auto-poster-tweet-preview').html(result.template);
                clickparent.next('tr').next('tr').children('td').children('.wpw-auto-poster-tweet-preview-count').html(result.template.length);
            }
        });
    });

    // Reset status ajax code
    jQuery(document).on("click", ".wpw-auto-poster-rstatus", function () {

        var this_element = jQuery(this);
        var metaname = this_element.attr('aria-label');
        var postid = this_element.attr('aria-data-id');
        var social_type = this_element.attr('aria-type');

        this_element.attr('disabled', 'disabled');
        jQuery('.wpw-auto-poster-loader').addClass('wpw-auto-poster-show');

        var data = {
            action: 'wpw_auto_poster_reset_post_social_status',
            postid: postid,
            meta: metaname,
            social_type: social_type
        };

        jQuery.post(ajaxurl, data, function (response) {

            var result = jQuery.parseJSON(response);

            if (result.status == 'success') {
                this_element.remove();
                jQuery('.wpw-lbl-' + metaname).html('Unpublished');
                jQuery('.wpw-auto-poster-loader').removeClass('wpw-auto-poster-show');
            }
        });
    });

    // code to hide or show fb meta fields based on selected posting type
    if( jQuery('select[name="_wpweb_fb_posting_method"]').val() == 'feed' ){
        jQuery('#_wpweb_fb_custom_status_msg').parent().parent().hide();
        jQuery('input[name*="_wpweb_fb_post_image"]').parent().parent().show();
        jQuery('#_wpweb_fb_custom_post_link').parent().parent().show();
            
    } else if( jQuery('select[name="_wpweb_fb_posting_method"]').val() == 'feed_status' ) {
        jQuery('#_wpweb_fb_custom_status_msg').parent().parent().show();
        jQuery('input[name*="_wpweb_fb_post_image"]').parent().parent().hide();
        jQuery('#_wpweb_fb_custom_post_link').parent().parent().hide();
        
    } else if( WpwAutoPosterMeta.fb_global_posting_type == 'feed'){
        jQuery('#_wpweb_fb_custom_status_msg').parent().parent().hide();
        jQuery('input[name*="_wpweb_fb_post_image"]').parent().parent().show();
        jQuery('#_wpweb_fb_custom_post_link').parent().parent().show();
    } else{
        jQuery('#_wpweb_fb_custom_status_msg').parent().parent().show();
        jQuery('input[name*="_wpweb_fb_post_image"]').parent().parent().hide();
        jQuery('#_wpweb_fb_custom_post_link').parent().parent().hide();
    }

    // code to hide or show fb meta fields based on selected posting type
    jQuery(document).on( 'change', 'select[name="_wpweb_fb_posting_method"]', function(){
        if( jQuery(this).val() == 'feed' ){
            jQuery('#_wpweb_fb_custom_status_msg').parent().parent().hide();
            jQuery('input[name*="_wpweb_fb_post_image"]').parent().parent().show();
            jQuery('#_wpweb_fb_custom_post_link').parent().parent().show();
            
        } else if( jQuery(this).val() == 'feed_status' ) {
            jQuery('#_wpweb_fb_custom_status_msg').parent().parent().show();
            jQuery('input[name*="_wpweb_fb_post_image"]').parent().parent().hide();
            jQuery('#_wpweb_fb_custom_post_link').parent().parent().hide();
            
        } else if( WpwAutoPosterMeta.fb_global_posting_type == 'feed') {
            jQuery('#_wpweb_fb_custom_status_msg').parent().parent().hide();
            jQuery('input[name*="_wpweb_fb_post_image"]').parent().parent().show();
            jQuery('#_wpweb_fb_custom_post_link').parent().parent().show();
        } else{
            jQuery('#_wpweb_fb_custom_status_msg').parent().parent().show();
            jQuery('input[name*="_wpweb_fb_post_image"]').parent().parent().hide();
            jQuery('#_wpweb_fb_custom_post_link').parent().parent().hide();
        }
    });

    // Tublr hide or shor fields code strt

    // code to hide or show tumblr meta fields based on selected posting type
    if( jQuery('select[name="_wpweb_tb_posting_type"]').val() == 'text' || ( jQuery('select[name="_wpweb_tb_posting_type"]').val() == '' && WpwAutoPosterMeta.tb_global_posting_type == 'text') ){
        jQuery('input[name*="_wpweb_tb_post_image"]').parent().parent().hide();
        jQuery('#_wpweb_tb_post_desc').parent().parent().show();
            
    } else if( jQuery('select[name="_wpweb_tb_posting_type"]').val() == 'photo' || ( jQuery('select[name="_wpweb_tb_posting_type"]').val() == '' && WpwAutoPosterMeta.tb_global_posting_type == 'photo') ) {
        jQuery('input[name*="_wpweb_tb_post_image"]').parent().parent().show();
        jQuery('#_wpweb_tb_post_desc').parent().parent().hide();
    }
    else {
        jQuery('input[name*="_wpweb_tb_post_image"]').parent().parent().show();
        jQuery('#_wpweb_tb_post_desc').parent().parent().show();
    }

    // code to hide or show TB meta fields based on selected posting type
    jQuery(document).on( 'change', 'select[name="_wpweb_tb_posting_type"]', function(){

        if( jQuery(this).val() == 'text' || ( jQuery(this).val() == '' && WpwAutoPosterMeta.tb_global_posting_type == 'text') ){
            jQuery('input[name*="_wpweb_tb_post_image"]').parent().parent().hide();
            jQuery('#_wpweb_tb_post_desc').parent().parent().show();
            
        } else if( jQuery(this).val() == 'photo' || ( jQuery(this).val() == '' && WpwAutoPosterMeta.tb_global_posting_type == 'photo') ) {
            jQuery('input[name*="_wpweb_tb_post_image"]').parent().parent().show();
            jQuery('#_wpweb_tb_post_desc').parent().parent().hide(); 
        }
        else {
            jQuery('input[name*="_wpweb_tb_post_image"]').parent().parent().show();
            jQuery('#_wpweb_tb_post_desc').parent().parent().show();
        }
    });


    // code to hide or show facebook gallery meta fields based on selected share posting type
    if( jQuery('select[name="_wpweb_fb_share_posting_type"]').val() == 'image_posting' || ( jQuery('select[name="_wpweb_fb_share_posting_type"]').val() == '' && WpwAutoPosterMeta.fb_global_share_posting_type == 'image_posting') ){
        jQuery('.wpw-auto-poster-meta-gallery').parent().parent().show();    
    } 
    else {
        jQuery('.wpw-auto-poster-meta-gallery').parent().parent().hide();
    }

    // code to hide or show Facebook image Gallery meta fields based on selected share posting type
    jQuery(document).on( 'change', 'select[name="_wpweb_fb_share_posting_type"]', function(){

        if( jQuery(this).val() == 'image_posting' || ( jQuery(this).val() == '' && WpwAutoPosterMeta.fb_global_share_posting_type == 'image_posting') ){
            jQuery('.wpw-auto-poster-meta-gallery').parent().parent().show();            
        } 
        else {
            jQuery('.wpw-auto-poster-meta-gallery').parent().parent().hide();
        }
    });

});