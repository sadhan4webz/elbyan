var WSTDL = WSTDL || {};
(function($){
	WSTDL.sleep = function( ms ){
		return new Promise(resolve => setTimeout(resolve, ms));
	}
	WSTDL.show_wpmbd_notice = function( html_element, $target, animate, ts ) {
		if ( ! $target )
			return;	
		$target.html( html_element );
		if( animate ){
			WSTDL.animate_to_target( $target );
		}
		if( ts > 0 ){
			WSTDL.sleep(ts).then(() => { 
				$target.children().fadeOut();
			});
		}	
	}
	WSTDL.error_msg_wrap_s	= '<p class="wpmbd-msg-entry wpmbd-msg-error">';
	WSTDL.error_msg_wrap_e	= '</p>';

	WSTDL.wpmbd_ajax_error = function( jqXHR, textStatus, errorThrown, $target ){
		$target.html("");
		WSTDL.show_wpmbd_notice( WSTDL.error_msg_wrap_s + textStatus + ' : ' + errorThrown + WSTDL.error_msg_wrap_e, $target, true, 0 );		
	}

	WSTDL.animate_to_target = function( $target ){
		if( !$target )
			return;
		var header_ht 		= $('header.site-header').height();
		var wp_adminbar_ht 	= $('#wpadminbar').length > 0 ? $('#wpadminbar').height() : 0;
		$( 'html,body' ).animate({ scrollTop: $target.offset().top - ( header_ht + wp_adminbar_ht ) }, 1000);
	}
	
	WSTDL.show_spinner = function( params ){
		if( !params.target )
			return;
		if( !params.target.next().is('.wpmbd-spinner') ){
			params.target.after('<span class="wpmbd-spinner"></span>');
		}
		params.target.next('.wpmbd-spinner').addClass('is-active');	
	}
	
	WSTDL.hide_spinner = function( params ){
		if( !params.target )
			return;
		if( !params.target.next().is('.wpmbd-spinner') )
			return;
		params.target.next('.wpmbd-spinner').removeClass('is-active');
		params.target.next('.wpmbd-spinner').remove();	
	}
	
	WSTDL.show_loader = function( params ){
		if( !params.target )
			return;
		if( !params.target.prev().is('.wpmbd-ajax-spinner') ){
			let loader_html = $("#wpmbd-global .wpmbd-ajax-spinner").clone();
			params.target.before(loader_html);			
		}
		params.target.prev('.wpmbd-ajax-spinner').show();	
	}
	
	WSTDL.hide_loader = function( params ){
		if( !params.target )
			return;
		if( !params.target.prev().is('.wpmbd-ajax-spinner') )
			return;
		params.target.prev('.wpmbd-ajax-spinner').hide();
		params.target.prev('.wpmbd-ajax-spinner').remove();	
	}
	
	WSTDL.reset = function( params ){
		
	}
	
	// Ajax Generic
	WSTDL.ajax_callbacks	= {
		before_ajax : function( params ){
						if( typeof params.before_ajax_callback == 'function' )
							params.before_ajax_callback( params );
						if( typeof params.loading_type != 'undefined' ){
							if( params.loading_type == 'spinner' )
								WSTDL.show_spinner({target:params.loading_target});
							else if( params.loading_type == 'loader' )	
								WSTDL.show_loader({target:params.loading_target});
						}
					},
		after_ajax : function( params ){
						if( typeof params.after_ajax_callback == 'function' )
							params.after_ajax_callback( params );
						if( typeof params.loading_type != 'undefined' ){
							if( params.loading_type == 'spinner' )
								WSTDL.hide_spinner({target:params.loading_target});
							else if( params.loading_type == 'loader' )	
								WSTDL.hide_loader({target:params.loading_target});
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
												WSTDL.show_wpmbd_notice( response.msg, params.response, true, 0 );											
										}
									},
		after_ajax_script_error		: function( params, err ){
										if( typeof params.ajax_error_callback == 'function' )
											params.ajax_error_callback( params );
										if( typeof params.html_error != 'undefined' ){
											if( params.html_error == 0 )
												alert(err);
											else	
												WSTDL.show_wpmbd_notice( WSTDL.error_msg_wrap_s + err + WSTDL.error_msg_wrap_e, params.response, true, 0 );											
										}
									},
		after_ajax_thrown_error		: function( params, jxobj ){
										if( typeof params.ajax_error_callback == 'function' )
											params.ajax_error_callback( params );
										if( typeof params.html_error != 'undefined' ){
											if( params.html_error == 0 )
												alert( jxobj.textStatus + ' : ' + jxobj.errorThrown );
											else	
												WSTDL.wpmbd_ajax_error( jxobj.jqXHR, jxobj.textStatus, jxobj.errorThrown, params.response );											
										}
									}, 							
	};
	
	WSTDL.ajax_options	= {
		type 		: 'POST',
		dataType	: 'json',
		url			: '',
		data		: 'action=default',
		success		: function( response ){ console.log( 'WSTDL Default success response: ' + response ); },
		error		: function( jqXHR, textStatus, errorThrown ){ console.log( 'WSTDL Default error response: ' + textStatus + ' : ' + errorThrown );}
	};
	
	WSTDL.ajax	= function( ajax_params ){
		let ajax_options 		= WSTDL.ajax_options;
		WSTDL.ajax_callbacks.before_ajax( ajax_params );
		
		ajax_options['success']	= function(response){
									try{
										if( response.event == "success" ){
											WSTDL.ajax_callbacks.after_ajax( ajax_params );
											WSTDL.ajax_callbacks.after_ajax_success_return( ajax_params, response );											
										}else{
											WSTDL.ajax_callbacks.after_ajax( ajax_params );
											WSTDL.ajax_callbacks.after_ajax_error_return( ajax_params, response );
										}						
									}catch(err){
										console.log("Error occured in success ajax call",err);
										WSTDL.ajax_callbacks.after_ajax( ajax_params );
										WSTDL.ajax_callbacks.after_ajax_script_error( ajax_params, err );
									}	
								};
		ajax_options['error']		= function( jqXHR, textStatus, errorThrown ){
									console.log("Error occured in ajax call");
									WSTDL.ajax_callbacks.after_ajax( ajax_params );
									WSTDL.ajax_callbacks.after_ajax_thrown_error( ajax_params, { jqXHR : jqXHR, textStatus : textStatus, errorThrown : errorThrown } );				
								};							
		$.ajax(ajax_options);
	}
	
	// Ajax Generic ends	
	
})(jQuery);