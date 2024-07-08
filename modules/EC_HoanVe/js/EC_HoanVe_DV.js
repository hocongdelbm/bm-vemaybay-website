
$(document).ready(function() {
	$('#frmDoiTT').submit(function(){
		var tt = $('#frmDoiTT select[name="tinhtrang"] :selected').val();
		if(tt == '1'){
			var arr = document.getElementsByName('ct_dahoan[]');
			var ncc = document.getElementsByName('ct_nhacc_id[]');
			var totalRow = 0;
			var ncc_err = 0;
			for(var i=0; i<arr.length; i++){
				totalRow += parseInt(arr[i].value);
				if($.trim(ncc[i].value) == ''){
					ncc_err++;
				}
			}
			if(totalRow != arr.length){
				alert('Vui lòng chọn đầy đủ các vé đã hoàn');
				return false;
			}
			if(ncc_err > 0){
				alert('Vui lòng chọn đầy đủ nhà cung cấp');
				return false;
			}
		}
	});
	if(tinhtrang != '2'){
		$('form[name="DetailView"] input:submit[name="Edit"]').remove();
		$('form[name="DetailView"] input:submit[name="Delete"]').remove();
	}
});