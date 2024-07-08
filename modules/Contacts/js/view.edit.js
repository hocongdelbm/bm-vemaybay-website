$(document).ready(function () {

     // Chỉ cho phép nhập số
	$('#phone, #primary_address_postalcode, #alt_address_postalcode').on('input', function() {
		let inputValue  	= $(this).val();
		let numericValue  	= inputValue.replace(/[^0-9]/g, '');
		$(this).val(numericValue);
	});

     // Viết hoa danh từ riêng - last_name
     $('#last_name, #primary_address_state, #primary_address_city, #primary_address_country, #primary_address_street').on('input', function() {
		let last_name       = $(this).val();
          let formattedName   = formatName(last_name);
          $(this).val(formattedName);
	});
});

function formatName(name) {
     // split name 
     let words = name.split(' ');
 
     // convert to "Xxx"
     let formattedWords = words.map(function(word) {
       return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
     });
 
     // concat letter
     let formattedName = formattedWords.join(' ');
 
     return formattedName;
   }