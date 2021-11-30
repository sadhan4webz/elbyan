jQuery(function($){

	// On doc ready
	$(document).ready(function(){
		// init the basic tooltip

		WCCB.showTip( '.wpiaf-tips', {attribute:"data-tip",fadeIn:50,fadeOut:50,delay:200,keepAlive:!0} );

		$( ".date_picker" ).datepicker({
			dateFormat:'dd-mm-yy',
			maxDate: 0
		});
	});

	var wcqi_refresh_quantity_increments = function () {
        $('div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)').addClass('buttons_added').append('<button type="button" class="plus fa fa-plus" ></button>').prepend('<button type="button" class="minus fa fa-minus" ></button>');
    }

    $(document).on('updated_wc_div', function() {
        wcqi_refresh_quantity_increments();
    });

    $(document).on('click', '.plus, .minus', function() {
        var $qty = $(this).closest('.quantity').find('.qty'),
            currentVal = parseFloat($qty.val()),
            max = parseFloat($qty.attr('max')),
            min = parseFloat($qty.attr('min')),
            step = $qty.attr('step');
        if (!currentVal || currentVal === '' || currentVal === 'NaN') currentVal = 0;
        if (max === '' || max === 'NaN') max = '';
        if (min === '' || min === 'NaN') min = 0;
        if (step === 'any' || step === '' || step === undefined || parseFloat(step) === 'NaN') step = 1;
        if ($(this).is('.plus')) {
            if (max && (currentVal >= max)) {
                $qty.val(max);
            } else {
                $qty.val((currentVal + parseFloat(step)).toFixed(step.getDecimals()));
            }
        } else {
            if (min && (currentVal <= min)) {
                $qty.val(min);
            } else if (currentVal > 0) {
                $qty.val((currentVal - parseFloat(step)).toFixed(step.getDecimals()));
            }
        }
        $qty.trigger('change');
    });

    wcqi_refresh_quantity_increments();

    // Increment decrement 
	if (!String.prototype.getDecimals) {
        String.prototype.getDecimals = function() {
            var num = this,
                match = ('' + num).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
            if (!match) {
                return 0;
            }
            return Math.max(0, (match[1] ? match[1].length : 0) - (match[2] ? +match[2] : 0));
        }
    }

	var get_tutor_availability_calendar = function( params ) {
		
		params['loading_type']			= 'loader',
		params['loading_target'] 		= params.wrapper,
		params['html_error'] 			= 0;

		console.log(params);

		WCCB.ajax_options['url'] 		= wccb_config.frontend_ajax_url;
		WCCB.ajax_options['data'] 		= 'action='+params.action+'&date='+params.date+'&num_days='+params.num_days+'&'+params.query_string;
		
		WCCB.ajax_callbacks['after_ajax_success_return'] = function( params, response ){
														$(params.response_container).html(response.content);
														WCCB.showTip( '.wpiaf-tips', {attribute:"data-tip",fadeIn:50,fadeOut:50,delay:200,keepAlive:!0} );
													};
		WCCB.ajax_callbacks['after_ajax_error_return'] = function( params, response ){
														alert(response.msg);
													};									
		WCCB.ajax( params );
	}

	$(document).on('change', '#course_type', function(e){
		e.preventDefault();
		if ($(this).val() == 'fixed' ) {
			$('#course_quantity_wrapper').show();
		}
		else {
			$('#course_quantity_wrapper').hide();
		}
	});

	$(document).on('change', '#user_role', function(e){
		e.preventDefault();
		if ($(this).val() == 'wccb_tutor' ) {
			$('.availability_container').show();
		}
		else {
			$('.availability_container').hide();
		}
	});

	if (wccb_config.is_admin) {
		$('#tutor_ids').select2();
	}
	else {
		$( "#tabs" ).tabs();
	}
	
	$(document).on('change', '#product-type', function(e){
		if ($(this).val() == 'wccb_course') {
			jQuery('.product_data_tabs .general_tab').addClass('show_if_wcc_package').show();
            jQuery('#general_product_data .pricing').addClass('show_if_wcc_package').show();
		}
	});

	if ($('#product-type').val() == 'wccb_course') {
		jQuery('.product_data_tabs .general_tab').addClass('show_if_wcc_package').show();
        jQuery('#general_product_data .pricing').addClass('show_if_wcc_package').show();
	}

	$(document).on('click', '.get_tutor_availability_calendar', function(e){
		let $elm      = $(this),
			$tutor_id = $elm.data('tutor_id');

		if ($elm.data('reset_picked') == 'yes') {
			$('.tutor_profile_wrapper').removeClass('tutor_selected');//Remove selected class from other tutor
			$elm.parent().parent().addClass('tutor_selected'); //Add selected class for selected tutor

			$('.slot_selected_container').html('');
		}

		let	params    = { query_string : $('.wccb_form').serialize() , action : $elm.data('action') , date : $elm.data('date') , response_container : $elm.data('response_container') , num_days : wccb_config.num_days_calendar , wrapper : $($elm.data('response_container'))};

		get_tutor_availability_calendar(params);
	});

	$(document).on('click', '.slot', function(e){
		let $elm                = $(this),
			$slot_date          = $elm.data('slot_date'),
			$slot_time          = $elm.data('slot_time'),
			$slot_date_time     = $elm.data('slot_date_time'),
			$slot_picked_row_id = $elm.data('slot_picked_row_id');
			$reschedule         = $elm.data('reschedule');

		if ($elm.hasClass('slot_picked')) {
			$elm.removeClass('slot_picked');
			$('#'+$slot_picked_row_id).remove();
			console.log('delete from span',$slot_picked_row_id);
		}
		else {
			$slot_span_id = $slot_picked_row_id.split('slot_picked_row_');
			$elm.addClass('slot_picked');
			$html = wccb_config.slot_picked_row;
			$html = $html.replace( '{unique_random_id}' , $slot_span_id[1] );
			$html = $html.replace( '{slot_span_id}' , 'slot_span_id_'+$slot_span_id[1] );
			$html = $html.replace( '{slot_picked_row_id}' , $slot_picked_row_id );
			$html = $html.replace( '{slot_date_time_hidden}' , $slot_date_time );
			$html = $html.replace( '{slot_date}' , $slot_date );
			$html = $html.replace( '{slot_time}' , $slot_time );

			if ($reschedule == 'yes') {
				if ($('.slot_selected_container').children().length > 0 ) {
					alert('You can select only one slot to reschedule this class. If you want to change then remove selected slot first and then select other.');
					$elm.removeClass('slot_picked');
				}
				else {
					$('.slot_selected_container').append($html);
				}
			}
			else {
				$('.slot_selected_container').append($html);
			}
		}

	});

	$(document).on('change', '.gender_select', function(e){
		var url = '?gender='+$(this).val();
		if($('#new_booking').length){
			url += '&new_booking='+$('#new_booking').val()+'&product_id='+$('#product_id').val()+'&user_id='+$('input[name=user_id]').val();
		}
		window.location.href=url;
	});

	$(document).on('click', '.delete_slot', function(e){ 
		e.preventDefault();
		$slot_span_id = $(this).data('slot_span_id');
		$('#'+$slot_span_id).removeClass('slot_picked');
		$(this).closest('.slot_picked_row').remove();

		console.log($('#'+$slot_span_id));
	});

	$(document).on('click', '.cancel_booking', function(e){ 
		e.preventDefault();
		let $link                     = $(this),
			$booking_id               = $link.data('booking_id'),
			$cancel_booking_url_nonce = $link.data('cancel_booking_url_nonce');

		var r   = confirm("Are you sure you want to cancel this class?");
		if (r == true) {
		  url = '?action_do=cancel_class&booking_id='+$booking_id+'&cancel_booking_url_nonce='+$cancel_booking_url_nonce;
		  window.location.href = url;
		} else {
		  return false;
		}
	});

	$(document).on('change', '.get_tutor_profile', function(e){ 
		let product_id  = $(this).val(),
			hour_id     = $(this).find(':selected').data('hour_id'),
			expire_date = $(this).find(':selected').data('expire_date'),
			display_expire_date = $(this).find(':selected').data('display_expire_date');
		let	params    = { product_id : product_id , action : 'get_tutor_profile' , response_container : '.tutor_container', wrapper : $('.tutor_container')  };
			
			params['loading_type']			= 'loader',
			params['loading_target'] 		= params.wrapper,
			params['html_error'] 			= 0;
		

		$(params.response_container).html('');
		$('.slot_selected_container').html('');
		$('.tutor_availability_main_wrapper').html('');
		$('.expire_date_container').html('');
		$('.button_wrapper').hide();

		if (product_id.length == 0 ) {
			return false;
		}

		$('.button_wrapper').show();

		$('input[name=hour_id]').val(hour_id);
		$('input[name=hour_expire_date]').val(expire_date);
		$('input[name=display_expire_date]').val(display_expire_date);
		console.log(expire_date);
		$('.expire_date_container').html('Expire Date :'+ display_expire_date);



		WCCB.ajax_options['url'] 		= wccb_config.frontend_ajax_url;
		WCCB.ajax_options['data'] 		= 'action='+params.action+'&product_id='+params.product_id;
		
		WCCB.ajax_callbacks['after_ajax_success_return'] = function( params, response ){
														$(params.response_container).html(response.content);

													};
		WCCB.ajax_callbacks['after_ajax_error_return'] = function( params, response ){
														alert(response.msg);
													};									
		WCCB.ajax( params );
	});

	$(document).on('click', '.add_time_row', function(e){
		e.preventDefault();
		let $link      = $(this),
			$lower_key = $link.data('lower_key'),
			$html      = wccb_config.availability_time_row;
			$html      = $html.replace(/{lower_key}/g, $lower_key );

			//$(this).closest( 'tr' ).after( $html );
			$($html).insertAfter($(this).closest('tr'));
	});

	$(document).on('click', '.delete_time_row', function(e){
		e.preventDefault();
		$(this).closest('.time_row').remove();
	});

	$(document).on('click', '#reset_search', function(e){
		e.preventDefault();
		$('#start_date').val('');
		$('#end_date').val('');
	});

	$(document).on('submit', '.add_hour_form', function(e){
		e.preventDefault();
		let $frm      = $(this),
			$btn      = $('.save_hour')
			params    = { query_string : $frm.serialize() , action : 'student_add_hour' , response: $('.response_container') , loading_type : 'spinner' , loading_target : $btn , html_error : 1 },

		WCCB.ajax_options['url'] 		= wccb_config.frontend_ajax_url;
		WCCB.ajax_options['data'] 		= 'action='+params.action+'&'+params.query_string;
		WCCB.ajax_callbacks['after_ajax_success_return'] = function( params, response ){
												
														WCCB.show_wpmbd_notice( response.msg, params.response, true, 0 );
														$frm.trigger("reset");
													};									
		WCCB.ajax( params );

	});

});	