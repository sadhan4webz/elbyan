var WCCB = WCCB || {};
(function($){
	WCCB.sleep = function( ms ){
		return new Promise(resolve => setTimeout(resolve, ms));
	}
	WCCB.show_wpmbd_notice = function( html_element, $target, animate, ts ) {
		if ( ! $target )
			return;	
		$target.html( html_element );
		if( animate ){
			WCCB.animate_to_target( $target );
		}
		if( ts > 0 ){
			WCCB.sleep(ts).then(() => { 
				$target.children().fadeOut();
			});
		}	
	}
	WCCB.error_msg_wrap_s	= '<p class="wpmbd-msg-entry wpmbd-msg-error">';
	WCCB.error_msg_wrap_e	= '</p>';

	WCCB.wpmbd_ajax_error = function( jqXHR, textStatus, errorThrown, $target ){
		$target.html("");
		WCCB.show_wpmbd_notice( WCCB.error_msg_wrap_s + textStatus + ' : ' + errorThrown + WCCB.error_msg_wrap_e, $target, true, 0 );		
	}

	WCCB.animate_to_target = function( $target ){
		if( !$target )
			return;
		var header_ht 		= $('header.site-header').height();
		var wp_adminbar_ht 	= $('#wpadminbar').length > 0 ? $('#wpadminbar').height() : 0;
		$( 'html,body' ).animate({ scrollTop: $target.offset().top - ( header_ht + wp_adminbar_ht ) }, 1000);
	}
	
	WCCB.show_spinner = function( params ){
		if( !params.target )
			return;
		if( !params.target.next().is('.wccb-spinner') ){
			params.target.after('<span class="wccb-spinner"></span>');
		}
		params.target.next('.wccb-spinner').addClass('is-active');	
	}
	
	WCCB.hide_spinner = function( params ){
		if( !params.target )
			return;
		if( !params.target.next().is('.wccb-spinner') )
			return;
		params.target.next('.wccb-spinner').removeClass('is-active');
		params.target.next('.wccb-spinner').remove();	
	}
	
	WCCB.show_loader = function( params ){
		if( !params.target )
			return;
		if( !params.target.prev().is('.wccb-ajax-spinner') ){
			let loader_html = $("#wccb-global .wccb-ajax-spinner").clone();
			params.target.before(loader_html);			
		}
		params.target.prev('.wccb-ajax-spinner').show();	
	}
	
	WCCB.hide_loader = function( params ){
		if( !params.target )
			return;
		if( !params.target.prev().is('.wccb-ajax-spinner') )
			return;
		params.target.prev('.wccb-ajax-spinner').hide();
		params.target.prev('.wccb-ajax-spinner').remove();	
	}
	
	WCCB.reset = function( params ){
		
	}
	
	// Ajax Generic
	WCCB.ajax_callbacks	= {
		before_ajax : function( params ){
						if( typeof params.before_ajax_callback == 'function' )
							params.before_ajax_callback( params );
						if( typeof params.loading_type != 'undefined' ){
							if( params.loading_type == 'spinner' )
								WCCB.show_spinner({target:params.loading_target});
							else if( params.loading_type == 'loader' )	
								WCCB.show_loader({target:params.loading_target});
						}
					},
		after_ajax : function( params ){
						if( typeof params.after_ajax_callback == 'function' )
							params.after_ajax_callback( params );
						if( typeof params.loading_type != 'undefined' ){
							if( params.loading_type == 'spinner' )
								WCCB.hide_spinner({target:params.loading_target});
							else if( params.loading_type == 'loader' )	
								WCCB.hide_loader({target:params.loading_target});
						}
					},
		after_ajax_success_return 	: function( params, response ){
										// need to define
									},
		after_ajax_error_return		: function( params, response ){
										if( typeof params.ajax_error_callback == 'function' )
											params.ajax_error_callback( params );
										if( typeof params.html_error != 'undefined' ){
											if( params.html_error == 0 )
												alert(response.msg);
											else	
												WCCB.show_wpmbd_notice( response.msg, params.response, true, 0 );											
										}
									},
		after_ajax_script_error		: function( params, err ){
										if( typeof params.ajax_error_callback == 'function' )
											params.ajax_error_callback( params );
										if( typeof params.html_error != 'undefined' ){
											if( params.html_error == 0 )
												alert(err);
											else	
												WCCB.show_wpmbd_notice( WCCB.error_msg_wrap_s + err + WCCB.error_msg_wrap_e, params.response, true, 0 );											
										}
									},
		after_ajax_thrown_error		: function( params, jxobj ){
										if( typeof params.ajax_error_callback == 'function' )
											params.ajax_error_callback( params );
										if( typeof params.html_error != 'undefined' ){
											if( params.html_error == 0 )
												alert( jxobj.textStatus + ' : ' + jxobj.errorThrown );
											else	
												WCCB.wpmbd_ajax_error( jxobj.jqXHR, jxobj.textStatus, jxobj.errorThrown, params.response );											
										}
									}, 							
	};
	
	WCCB.ajax_options	= {
		type 		: 'POST',
		dataType	: 'json',
		url			: '',
		data		: 'action=default',
		success		: function( response ){ console.log( 'WCCB Default success response: ' + response ); },
		error		: function( jqXHR, textStatus, errorThrown ){ console.log( 'WCCB Default error response: ' + textStatus + ' : ' + errorThrown );}
	};
	
	WCCB.ajax	= function( ajax_params ){
		let ajax_options 		= WCCB.ajax_options;
		WCCB.ajax_callbacks.before_ajax( ajax_params );
		
		ajax_options['success']	= function(response){
									try{
										if( response.event == "success" ){
											WCCB.ajax_callbacks.after_ajax( ajax_params );
											WCCB.ajax_callbacks.after_ajax_success_return( ajax_params, response );											
										}else{
											WCCB.ajax_callbacks.after_ajax( ajax_params );
											WCCB.ajax_callbacks.after_ajax_error_return( ajax_params, response );
										}						
									}catch(err){
										console.log("Error occured in success ajax call",err);
										WCCB.ajax_callbacks.after_ajax( ajax_params );
										WCCB.ajax_callbacks.after_ajax_script_error( ajax_params, err );
									}	
								};
		ajax_options['error']		= function( jqXHR, textStatus, errorThrown ){
									console.log("Error occured in ajax call");
									WCCB.ajax_callbacks.after_ajax( ajax_params );
									WCCB.ajax_callbacks.after_ajax_thrown_error( ajax_params, { jqXHR : jqXHR, textStatus : textStatus, errorThrown : errorThrown } );				
								};							
		$.ajax(ajax_options);
	}
	
	// Ajax Generic ends	
	
})(jQuery);