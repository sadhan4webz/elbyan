var WCCB = WCCB || {};
(function($){

	 $.fn.wpiafTip = function(options) {
		var defaults = {
			activation: "hover",
			keepAlive: false,
			maxWidth: "200px",
			edgeOffset: 3,
			defaultPosition: "bottom",
			delay: 400,
			fadeIn: 200,
			fadeOut: 200,
			attribute: "title",
			content: false, // HTML or String to fill wpiaftip with
		  	enter: function(){},
		  	exit: function(){}
	  	};
	 	var opts = $.extend(defaults, options);

	 	// Setup tip tip elements and render them to the DOM
	 	if($("#wpiaftip_holder").length <= 0){
	 		var wpiaftip_holder = $('<div id="wpiaftip_holder" style="max-width:'+ opts.maxWidth +';"></div>');
			var wpiaftip_content = $('<div id="wpiaftip_content"></div>');
			var wpiaftip_arrow = $('<div id="wpiaftip_arrow"></div>');
			$("body").append(wpiaftip_holder.html(wpiaftip_content).prepend(wpiaftip_arrow.html('<div id="wpiaftip_arrow_inner"></div>')));
		} else {
			var wpiaftip_holder = $("#wpiaftip_holder");
			var wpiaftip_content = $("#wpiaftip_content");
			var wpiaftip_arrow = $("#wpiaftip_arrow");
		}

		return this.each(function(){
			var org_elem = $(this);
			if(opts.content){
				var org_title = opts.content;
			} else {
				var org_title = org_elem.attr(opts.attribute);
			}
			if(org_title != ""){
				if(!opts.content){
					org_elem.prop(opts.attribute, false); //remove original Attribute
				}
				var timeout = false;

				if(opts.activation == "hover"){
					org_elem.on( 'mouseenter', function(){
						active_wpiaftip();
					} ).on( 'mouseleave', function(){
						if(!opts.keepAlive || !wpiaftip_holder.is(':hover')){
							deactive_wpiaftip();
						}
					});
					if(opts.keepAlive){
						wpiaftip_holder.on( 'mouseenter', function(){} ).on( 'mouseleave', function(){
							deactive_wpiaftip();
						});
					}
				} else if(opts.activation == "focus"){
					org_elem.on( 'focus', function(){
						active_wpiaftip();
					}).on( 'blur', function(){
						deactive_wpiaftip();
					});
				} else if(opts.activation == "click"){
					org_elem.on( 'click', function(){
						active_wpiaftip();
						return false;
					}).on( 'mouseenter', function(){} ).on( 'mouseleave' ,function(){
						if(!opts.keepAlive){
							deactive_wpiaftip();
						}
					});
					if(opts.keepAlive){
						wpiaftip_holder.on( 'mouseenter', function(){} ).on( 'mouseleave', function(){
							deactive_wpiaftip();
						});
					}
				}

				function active_wpiaftip(){
					opts.enter.call(this);
					wpiaftip_content.html(org_title);
					wpiaftip_holder.hide().css("margin","0");
					wpiaftip_holder.removeAttr('class');
					wpiaftip_arrow.removeAttr("style");

					var top = parseInt(org_elem.offset()['top']);
					var left = parseInt(org_elem.offset()['left']);
					var org_width = parseInt(org_elem.outerWidth());
					var org_height = parseInt(org_elem.outerHeight());
					var tip_w = wpiaftip_holder.outerWidth();
					var tip_h = wpiaftip_holder.outerHeight();
					var w_compare = Math.round((org_width - tip_w) / 2);
					var h_compare = Math.round((org_height - tip_h) / 2);
					var marg_left = Math.round(left + w_compare);
					var marg_top = Math.round(top + org_height + opts.edgeOffset);
					var t_class = "";
					var arrow_top = "";
					var arrow_left = Math.round(tip_w - 12) / 2;

                    if(opts.defaultPosition == "bottom"){
                    	t_class = "_bottom";
                   	} else if(opts.defaultPosition == "top"){
                   		t_class = "_top";
                   	} else if(opts.defaultPosition == "left"){
                   		t_class = "_left";
                   	} else if(opts.defaultPosition == "right"){
                   		t_class = "_right";
                   	}

					var right_compare = (w_compare + left) < parseInt($(window).scrollLeft());
					var left_compare = (tip_w + left) > parseInt($(window).width());

					if((right_compare && w_compare < 0) || (t_class == "_right" && !left_compare) || (t_class == "_left" && left < (tip_w + opts.edgeOffset + 5))){
						t_class = "_right";
						arrow_top = Math.round(tip_h - 13) / 2;
						arrow_left = -12;
						marg_left = Math.round(left + org_width + opts.edgeOffset);
						marg_top = Math.round(top + h_compare);
					} else if((left_compare && w_compare < 0) || (t_class == "_left" && !right_compare)){
						t_class = "_left";
						arrow_top = Math.round(tip_h - 13) / 2;
						arrow_left =  Math.round(tip_w);
						marg_left = Math.round(left - (tip_w + opts.edgeOffset + 5));
						marg_top = Math.round(top + h_compare);
					}

					var top_compare = (top + org_height + opts.edgeOffset + tip_h + 8) > parseInt($(window).height() + $(window).scrollTop());
					var bottom_compare = ((top + org_height) - (opts.edgeOffset + tip_h + 8)) < 0;

					if(top_compare || (t_class == "_bottom" && top_compare) || (t_class == "_top" && !bottom_compare)){
						if(t_class == "_top" || t_class == "_bottom"){
							t_class = "_top";
						} else {
							t_class = t_class+"_top";
						}
						arrow_top = tip_h;
						marg_top = Math.round(top - (tip_h + 5 + opts.edgeOffset));
					} else if(bottom_compare | (t_class == "_top" && bottom_compare) || (t_class == "_bottom" && !top_compare)){
						if(t_class == "_top" || t_class == "_bottom"){
							t_class = "_bottom";
						} else {
							t_class = t_class+"_bottom";
						}
						arrow_top = -12;
						marg_top = Math.round(top + org_height + opts.edgeOffset);
					}

					if(t_class == "_right_top" || t_class == "_left_top"){
						marg_top = marg_top + 5;
					} else if(t_class == "_right_bottom" || t_class == "_left_bottom"){
						marg_top = marg_top - 5;
					}
					if(t_class == "_left_top" || t_class == "_left_bottom"){
						marg_left = marg_left + 5;
					}
					wpiaftip_arrow.css({"margin-left": arrow_left+"px", "margin-top": arrow_top+"px"});
					wpiaftip_holder.css({"margin-left": marg_left+"px", "margin-top": marg_top+"px"}).attr("class","tip"+t_class);

					if (timeout){ clearTimeout(timeout); }
					timeout = setTimeout(function(){ wpiaftip_holder.stop(true,true).fadeIn(opts.fadeIn); }, opts.delay);
				}

				function deactive_wpiaftip(){
					opts.exit.call(this);
					if (timeout){ clearTimeout(timeout); }
					wpiaftip_holder.fadeOut(opts.fadeOut);
				}
			}
		});
	}


	WCCB.showTip = function( elm, attrs ){
		$(elm).wpiafTip(attrs);
	}


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
		//$( 'html,body' ).animate({ scrollTop: $target.offset().top - ( header_ht + wp_adminbar_ht ) }, 1000);
		var height = parseInt($target.offset().top) - (parseInt(header_ht) + parseInt(wp_adminbar_ht));

		$( 'html,body' ).animate({ scrollTop: $target.offset().top  }, 1000);
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

	WCCB.show_loader_v2 = function( params ) {
		$('body').prepend('<span class="se-pre-con"></span>');
	}

	WCCB.hide_loader_v2 = function( params ) {
		$('body').find('.se-pre-con').remove();
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
							else if( params.loading_type == 'loader_v2')
								WCCB.show_loader_v2();
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
							else if( params.loading_type == 'loader_v2')
								WCCB.hide_loader_v2();
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