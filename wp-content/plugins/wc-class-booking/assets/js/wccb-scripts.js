jQuery(function($){
	$(document).on('change', '#user_role', function(e){
		e.preventDefault();
		if ($(this).val() == 'Tutor' ) {
			$('.availability_container').show();
		}
		else {
			$('.availability_container').hide();
		}
	});
});	