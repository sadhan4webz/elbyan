jQuery(function($){
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
	
	$(document).on('change', '#product-type', function(e){
		if ($(this).val() == 'wccb_package') {
			jQuery('.product_data_tabs .general_tab').addClass('show_if_wcc_package').show();
            jQuery('#general_product_data .pricing').addClass('show_if_wcc_package').show();
		}
	});

	if ($('#product-type').val() == 'wccb_package') {
		jQuery('.product_data_tabs .general_tab').addClass('show_if_wcc_package').show();
        jQuery('#general_product_data .pricing').addClass('show_if_wcc_package').show();
	}

	$(document).on('click', '.get_tutor_availability', function(e){
		let $elm      = $(this),
			$tutor_id = $('input[name="tutor_id_radio"]:checked').val(),
			params    = { tutor_id : $tutor_id , action : $elm.data('action') , date : $elm.data('date') , response_container : $elm.data('response_container') , num_days : wccb_config.num_days_calendar};

		$('#tutor_id').val($tutor_id);

		WCCB.ajax_options['url'] 		= wccb_config.frontend_ajax_url;
		WCCB.ajax_options['data'] 		= 'action='+params.action+'&date='+params.date+'&tutor_id='+params.tutor_id+'&num_days='+params.num_days;
		
		WCCB.ajax_callbacks['after_ajax_success_return'] = function( params, response ){
														$(params.response_container).html(response.content);
													};
		WCCB.ajax_callbacks['after_ajax_error_return'] = function( params, response ){
														alert(response.msg);
													};									
		WCCB.ajax( params );

	});

	$(document).on('change', '.gender_select', function(e){
		window.location.href='?gender='+$(this).val();
	});


});	