$(document).ready(function(){
	// onload
	var dec_seperator = $('#dec_seperator').val();
	var grp_seperator = $('#grp_seperator').val();
	var sig_digits = $('#sig_digits').val();
	$('#sotien').number( true, sig_digits, dec_seperator, grp_seperator );
	
	$('#tutienmat_chk').on('change',function(){
		if($(this).is(':checked')){
			$('#tutienmat').val(1);
			$('#tutknganhang_id').val('');
			$('#tutknganhang_id').attr('disabled',true);
			$('#tutknganhang_id').after('<input type="hidden" name="tutknganhang_id" id="tutknganhang_id" value="" />');
			$('#tudiadiem_id').show();
		} else {
			$('#tutienmat').val(0);
			$('#tutknganhang_id').val('');
			$('#tutknganhang_id').attr('disabled',false);
			$('input:hidden[name="tutknganhang_id"]').remove();
			$('#tudiadiem_id').hide();
			$('#tudiadiem_id').val('');
		}
	});
	
	$('#dentienmat_chk').on('change',function(){
		if($(this).is(':checked')){
			$('#dentienmat').val(1);
			$('#dentknganhang_id').val('');
			$('#dentknganhang_id').attr('disabled',true);
			$('#dentknganhang_id').after('<input type="hidden" name="dentknganhang_id" id="dentknganhang_id" value="" />');
			$('#dendiadiem_id').show();
		}else{
			$('#dentienmat').val(0);
			$('#dentknganhang_id').val('');
			$('#dentknganhang_id').attr('disabled',false);
			$('input:hidden[name="dentknganhang_id"]').remove();
			$('#dendiadiem_id').hide();
			$('#dendiadiem_id').val('');
			
		}
	});
	
	$('#EditView').submit(function(){
		var action = $('#EditView input:hidden[name="action"]').val();
		if(action == 'Save'){
			var tutknganhang_id = $('#tutknganhang_id :selected').val();
			var dentknganhang_id = $('#dentknganhang_id :selected').val();
			var tutienmat = $('#tutienmat').val();
			var dentienmat = $('#dentienmat').val();
			var tudiadiem_id = $('#tudiadiem_id').val();
			var dendiadiem_id = $('#dendiadiem_id').val();
			if(tutknganhang_id=='' && tutienmat==0){
				alert('Bạn chưa chọn tài khoản ngân hàng');
				$('#tutknganhang_id').focus();
				return false;
			}
			if(dentknganhang_id=='' && dentienmat==0){
				alert('Bạn chưa chọn tài khoản ngân hàng');
				$('#dentknganhang_id').focus();
				return false;
			}
			if(tutknganhang_id == dentknganhang_id && (tutienmat==0 || dentienmat==0)){
				alert('Tài khoản ngân hàng không được trùng nhau');
				$('#tutknganhang_id').focus();
				return false;
			}
			if(tutienmat==1 && tudiadiem_id==''){
				alert('Vui lòng chọn địa điểm phù hợp');
				$('#tudiadiem_id').focus();
				return false;
			}
			if(dentienmat==1 && dendiadiem_id==''){
				alert('Vui lòng chọn địa điểm phù hợp');
				$('#dendiadiem_id').focus();
				return false;
			}
		}
	});
});