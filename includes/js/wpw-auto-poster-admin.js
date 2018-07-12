jQuery(document).ready(function ($) {

    $(document).on( 'click', '.clear-date',function(){
    	$('#_wpweb_select_hour').val('');
    });

    if( $('#_wpweb_select_hour').length ){

    	$('#_wpweb_select_hour').datetimepicker({
        	dateFormat: WpwAutoPosterAdmin.date_format,
        	minDate: new Date(WpwAutoPosterAdmin.current_date),
        	timeFormat: WpwAutoPosterAdmin.time_format,
        	showMinute : false,
        	ampm: false,
        	stepMinute:60,
        	showOn : 'focus',
        	stepHour: 1,
        	currentText: '',
	    }).attr('readonly','readonly');	    	
    }

    if( $('#wpw_auto_select_hour').length ){
        
        $('#wpw_auto_select_hour').datetimepicker({
            dateFormat: WpwAutoPosterAdmin.date_format,
            minDate: new Date(WpwAutoPosterAdmin.current_date),
            timeFormat: WpwAutoPosterAdmin.time_format,
            showMinute : false,
            ampm: false,
            stepMinute:60,
            stepHour: 1,
            currentText: 'Now',
            showOn : 'focus',
        });
    }

    if( $('.wpw-auto-schedule-content').length ){

        $(document).on( 'click', '.schedule > a',function(event){

            event.preventDefault();
            var scheduleurl = $(this).attr('href');
            $("input[name='schedule_url']").val(scheduleurl);

            $(".wpw-auto-popup-content").show();
            $(".wpw-auto-popup-overlay").show();
            

        });

        $(document).on( 'click', '.wpw-close-button',function(event){

            $(".wpw-auto-popup-content").hide();
            $(".wpw-auto-popup-overlay").hide();
        
        });

        $(document).on( 'click', '.done',function(event){

           var bulk_action = $('#bulk-action-selector-top').val();
           var select_hour = $("input[name='wpw_auto_select_hour']").val();

           if ( bulk_action !='' && bulk_action == 'schedule') {

                $('<input />').attr('type', 'hidden')
                  .attr('name', "bulk_select_hour")
                  .attr('value', select_hour)
                  .appendTo('#product-filter');

                $( ".wpw-close-button").trigger( "click");

           } else {

               var scheduleurl = $("input[name='schedule_url']").val();
               scheduleurl     = scheduleurl+"&select_hour="+select_hour;
               $(location).attr('href', scheduleurl);
           } 

        });

        $(document).on('change', '#bulk-action-selector-top', function () {
            
            var action = $(this).val();
            
            if( action == 'schedule') {
                $(".wpw-auto-popup-content").show();
                $(".wpw-auto-popup-overlay").show();
            }
        });

    }
    
    /**
     * For tumblr
     * 
     * hide post image setting if posting type text is selected
     */
    if( $('.tb_posting_type').length > 0 ) {
        var posting_type = $('.tb_posting_type').val();

        if( posting_type == 'text' ) {
            $('.tb_posting_type').parents('tr').next('tr').hide();
            $('.tb_posting_type').parents('tr').next('tr').next('tr').show();
        } else if( posting_type == 'link' ) {
            $('.tb_posting_type').parents('tr').next('tr').show();
            $('.tb_posting_type').parents('tr').next('tr').next('tr').show();
        } else { 
            $('.tb_posting_type').parents('tr').next('tr').show();
            $('.tb_posting_type').parents('tr').next('tr').next('tr').hide();
        }
    }
    $( document ).on( 'change', '.tb_posting_type', function() {
        var posting_type = $(this).val();
        if( posting_type == 'text' ) {
            $(this).parents('tr').next('tr').hide();
            $(this).parents('tr').next('tr').next('tr').show();
        } else if( posting_type == 'link' ) {
            $(this).parents('tr').next('tr').show();
            $(this).parents('tr').next('tr').next('tr').show();
        } else { 
            $(this).parents('tr').next('tr').show();
            $(this).parents('tr').next('tr').next('tr').hide();
        }
            
    });

});