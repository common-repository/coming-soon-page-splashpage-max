(function( $ ) {
 
	// Add Color Picker to all inputs that have 'color-field' class
	$(function() {
		function socialCheck(t){
			if(t.prop('checked') === true) {
				$('#social-section').slideDown();
			} else {
				$('#social-section').slideUp();
			}			
		}
		
		function content_check(m){
			if( !m || m ==='' && $('#nnmcs-create-page').hasClass('hidden')  ) {
			   $('#nnmcs-create-page').slideDown();
			} else if(m && !$('#nnmcs-create-page').hasClass('hidden') ) {
			   $('#nnmcs-create-page').slideUp();
			}
		}    

		function check_select(obj){
			var val = obj.val();
			var page = $('#main_content').val();

			if( !page && !obj.hasClass('invalid') ) {            
				if('coming-soon-content' === val || 'page' === val || 'image' === val){
					obj.addClass('invalid');
					obj.parent().append('<p class="alert"><span class="dashicons dashicons-warning"></span> Please select a <a href="#active">Content Page</a> above.</p>');
				}
			} else if( obj.hasClass('invalid') ) {
				obj.removeClass('invalid');
				obj.parent().children('.alert').remove();
			}      
		}

		$('.choose-a-colour').wpColorPicker();
		$('.choose-a-date').datepicker();

		socialCheck($('#enable_social'));
		content_check($('#main_content').val());

		$('.nnmcs-select').each(function(){
			check_select($(this));
		});


		$('.nnmcs-select').change(function(){
			check_select($(this));
		});  

		$('#main_content').change(function(){
			content_check($(this).val());
			$('.nnmcs-select').each(function(){
				check_select($(this));
		    });
		}); 

		$('#enable_social').change(function(){
			socialCheck($(this));
		}); 
	});
	 
})( jQuery );