$(document).ready(function() {

	var dec_seperator 	= $('#dec_seperator').val();
	var grp_seperator 	= $('#grp_seperator').val();
	var sig_digits 	= $('#sig_digits').val();

	$('#amount, .allow-number-only').number( true, sig_digits, dec_seperator, grp_seperator );

	// SELECT2 
	$('#employee-select, #supplier_id, #account_list').select2();

	$('#EditView').submit(function(e){
		let action = $('#EditView input:hidden[name="action"]').val();
		if(action == 'Save') {
			let hinhthucchi = $('#hinhthucchi').val();
			if(hinhthucchi == 'credit_transfer' && $('#tknganhang_id').val() == ''){
				let text_warning = 'Vui lòng chọn tài khoản ngân hàng phù hợp'
				showToastWarning(text_warning)
				$('#tknganhang_id').focus();
				return false;
			}
			if(hinhthucchi == 'cash' && $('#com_location_id').val() == ''){
				let text_warning = 'Vui lòng chọn địa điểm phù hợp'
				showToastWarning(text_warning)
				$('#com_location_id').focus();
				return false;
			}

			// Xuất vé KM
			if(($('#is_margin').is(':checked') || $("#ec_payment_types_id_c").val() == '1a72bc2f-d907-0ec9-853d-5572901455b5') && $('#supplier_id').val() == ''){
				let text_warning = 'Vui lòng chọn NCC'
				showToastWarning(text_warning)
				$('#supplier_id').focus();
				return false;
			}

			// Phải trả - NCC
			if(($("#ec_payment_types_id_c").val() == '3361ac47-2254-701a-55d1-508abcb90f50') && $('#supplier_id').val() == ''){
				let text_warning = 'Vui lòng chọn NCC'
				showToastWarning(text_warning)
				$('#supplier_id').focus();
				return false;
			}

			// loại chi "tạm ứng" thì phải có trường "nhân viên"
			if($("#ec_payment_types_id_c").val() == '688503c7-0d5c-659c-1bb5-540e8cf9ea9e' && $("#employee-select").val() == '') {
				let text_warning = 'Vui lòng chọn nhân viên được tạm ứng'
				showToastWarning(text_warning)
				$("#employee_select").focus();
				return false;
			}

			// loại chi "tiền hoàn vé" thì phải có kèm "phiếu hoàn vé"
			if ($("#ec_payment_types_id_c").val() == '7a7abc9b-0925-bdb7-d92b-526e3c5c0c14' && $("#hoanve_id").val() == '') {
				let text_warning = 'Vui lòng chọn phiếu hoàn vé'
				showToastWarning(text_warning)
				$("#hoanve").focus();
				$("#hoanve").css('border', '1px solid #ec2029');
				return false;
			}

			// số tiền chi phải <= số tiền hoàn khách
			if ($("#hoanve_id").val() != '' && $("#amount").val() > parseInt($("#amount").attr("max_ret_amt"))) {

				let text_warning = 'Số tiền chi tối đa là: ' + $.number($("#amount").attr("max_ret_amt"), sig_digits, dec_seperator, grp_seperator) + '';
				showToastWarning(text_warning)

				$("#amount").focus();
				return false;
			}

			// Loại chi "Hoàn tiền khác" thì phải kèm "Phiếu thu"
			if ($("#ec_payment_types_id_c").val() == '963d0471-d796-fb8a-31eb-60125259411c' && $("#phieuthu_id").val() == '') {
				let text_warning = 'Vui lòng chọn phiếu thu'
				showToastWarning(text_warning)
				$("#phieuthu").focus();
				$("#phieuthu").css('border', '1px solid #ec2029');
				return false;
			}
			// số tiền chi phải <= số tiền hoàn khác
			if ($("#phieuthu_id").val() != '' && $("#amount").val() > parseInt($("#amount").attr("max_refund_amt"))) {
				let text_warning = 'Số tiền chi tối đa là: ' + $.number($("#amount").attr("max_refund_amt"), sig_digits, dec_seperator, grp_seperator) + '';
				showToastWarning(text_warning)
				$("#amount").focus();
				return false;
			}

			// show loading
			$('.container-waiting').show();
		}
	});

	$('#hinhthucchi').change(function(){
		if($(this).val() == 'credit_transfer'){
			$('#tknganhang_id').show();
			$('#com_location_id').val('');
			$('#com_location_id').hide();
		} else if($(this).val() == 'cash'){
			$('#com_location_id').show();
			$('#tknganhang_id').val('');
			$('#tknganhang_id').hide();
			$('#tk_ketoan').val('');
		}
	});

	$('#tknganhang_id').change(function(){
		if($(this).val() != ''){
			var tk = $('#tknganhang_id :selected').attr('tk');
			$('#tk_ketoan').val(tk);
		} else {
			$('#tk_ketoan').val('');
		}
	});

	$('#hoanve_id').on('change', function () {
		getRetMaxAmt();
	});
	if($("#hoanve_id").val() != "") {
		getRetMaxAmt();
	}
		
	// Lấy số tiền tối đa phải chi của Hoàn vé khác
	$('#phieuthu_id').on('change', function () {
		getRefundMaxPrice_PT();
	});
	if($("#phieuthu_id").val() != "") {
		getRefundMaxPrice_PT();
	}
});

function getRetMaxAmt() {
	$.ajax({
		url: "index.php?entryPoint=entryPointEC_Payment_Voucher",
		type: "POST",
		async: true,
		data: {
			return_voucher: $("#hoanve_id").val(),
			for: "getPaidAmt"
		},
		success: function (response) {
			$("#amount").attr("max_ret_amt", response);
		}
	});
}

function getRefundMaxPrice_PT() {
	$.ajax({
		url: "index.php?entryPoint=entryPointEC_Payment_Voucher",
		type: "POST",
		async: true,
		data: {
			return_pt: $("#phieuthu_id").val(),
			for: "getPaid_PT"
		},
		success: function (response) {
			$("#amount").attr("max_refund_amt", response);
		}
	});
}