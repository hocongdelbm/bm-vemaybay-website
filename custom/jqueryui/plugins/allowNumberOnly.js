(function($) {
	// jQuery plugin definition
	$.fn.allowNumberOnly = function(event) {
		 if(event.shiftKey)
		  return event.preventDefault();
		 if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 110 || event.keyCode == 9 || event.keyCode == 190 || event.keyCode == 13) {
		 }
		 else {
			  if (event.keyCode < 95) {
				
				if (event.keyCode < 48 || event.keyCode > 57) {
					if (event.keyCode >= 37 && event.keyCode <= 40) {  
					 }
					else
					{
					  return event.preventDefault();
					}
				}
			  } 
			  else {
					if (event.keyCode < 96 || event.keyCode > 105) {
						return event.preventDefault();
					}
			  }
		}
	};
})(jQuery);