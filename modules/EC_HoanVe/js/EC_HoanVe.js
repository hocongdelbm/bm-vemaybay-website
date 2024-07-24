$(document).ready(function(){
	// onload
	// $('#LBL_LINEITEMS_PANEL').find('h4').eq(0).remove();
	// $('#LBL_LINEITEMS_PANEL').find('br').eq(0).remove();
	// $('#LBL_LINEITEMS_PANEL table').find('tr').eq(1).find('td').eq(0).remove();
	// $('#LBL_LINEITEMS_PANEL table').find('tr').eq(1).find('td').eq(0).attr('colspan', 4);
	
	var cal_date_format = $('#cal_date_format').val();
	var dec_seperator 	= $('#dec_seperator').val();
	var grp_seperator 	= $('#grp_seperator').val();
	var sig_digits 	= $('#sig_digits').val();
	
	$('.allow-number-only').number( true, sig_digits, dec_seperator, grp_seperator );
	
	$('.allow-number-only2').on('keypress', function(event) {
		$('.allow-number-only2').allowNumberOnly(event);
	});

	
	// setup calendar
	if($('input:hidden[name="ct_deleted[]"][value="0"]').length > 0){
		var arr = document.getElementsByName('ct_deleted[]');
		for(var i=0; i<arr.length; i++){
			Calendar.setup ({
				inputField : 'ct_ngaysinh' + i,
				daFormat : cal_date_format,
				button : 'ct_ngaysinh_trigger' + i,
				singleClick : true,
				dateStr : '',
				step : 1,
				weekNumbers : false
			});
		}
	}
	
	// disable ENTER
	$('input:text').bind("keypress", function(e) {
		if (e.keyCode == 13) return false;
	});
	
	// replace TAB by ENTER
	var $inputs = $('input:text');
	$inputs.on('keypress', function(e){
		if(e.which === 13){
			var ind = $inputs.index(this);
			$inputs.eq(ind + 1).focus();
			$inputs.eq(ind + 1).select();
		}
	});
	
	// add row
	$('#btnAddRow').click(function(){
		var ln = parseInt($('#row_count').val());
		$('#last-row').before(insertRow(ln));
		$('#ct_loaihk' + ln).focus();
		calculateLineTotal(ln);
		
		Calendar.setup ({
			inputField : 'ct_ngaysinh' + ln,
			daFormat : cal_date_format,
			button : 'ct_ngaysinh_trigger' + ln,
			singleClick : true,
			dateStr : '',
			step : 1,
			weekNumbers : false
		});
		
		$('.allow-number-only').number( true, sig_digits, dec_seperator, grp_seperator );
		// không cho nhấn phím enter trên form
		$('input:text').bind("keypress", function(e) {
			if (e.keyCode == 13) return false;
		});
		
		// thay thế phím TAB bằng ENTER
		var $inputs = $('input:text');
		$inputs.on('keypress', function(e){
			if(e.which === 13){
				var ind = $inputs.index(this);
				$inputs.eq(ind + 1).focus();
				$inputs.eq(ind + 1).select();
			}
		});
		ln++;
		$('#row_count').val(ln);
	});
	
	// check tình trạng đã hoàn hay chưa
	$('input:checkbox[name="ct_chk_dahoan[]"]').on('change',function(){
		if($(this).is(':checked')){
			$(this).next('input:hidden').val(1);
		} else {
			$(this).next('input:hidden').val(0);
		}			
	});
	
	// SUBMIT event
	$('#EditView').submit(function(){
		var action = $('#EditView input:hidden[name="action"]').val();
		if(action == 'Save'){
			if(!checkLineItems()){
				return false;
			}
		}
	});

	// SETUP AUTOCOMPLETE FOR AIRPORT AND AIRLINE
	$('input.ac').on('keydown.autocomplete', function() {
		var type = '';
		if($(this).hasClass('airline')){
			type = 'airline';
		} 
		$(this).autocomplete({
			source: 'index.php?entryPoint=entryPointGetAirportAndAirline&type=' + type,
			minLength: 2,
			select: function(event, ui) {
				var eleid = $(this).attr('id');
				$('#' + eleid).val(ui.item.code);
				event.preventDefault();
			}
		});
	});
	
});

function markRowDeleted(ln){
	$('#ct_line_' + ln).hide();
	$('#ct_deleted' + ln).val(1);
	calculateLineTotal(ln);
}

function calculateLineTotal(ln){
	var sotienhang = unformatNumber($('#ct_sotienhang' + ln).val());
	var sotienkhach = unformatNumber($('#ct_sotienkhach' + ln).val());
	var phidichvu = sotienhang - sotienkhach;
	$('#ct_sotienhang' + ln).val(sotienhang);
	$('#ct_sotienkhach' + ln).val(sotienkhach);
	$('#ct_phidichvu' + ln).val(phidichvu);
	calculateTotal();
}

function calculateTotal(){
	var arr = document.getElementsByName('ct_deleted[]');
	var sotienhang = document.getElementsByName('ct_sotienhang[]');
	var sotienkhach = document.getElementsByName('ct_sotienkhach[]');
	var phidichvu = document.getElementsByName('ct_phidichvu[]');
	var tongtienhang = 0; 
	var tongtienkhach = 0;
	var tongtiendv = 0;
	var tongsd = 0;
		
	for(var i = 0; i < arr.length; i++){
		if(arr[i].value == '0'){
			tongtienhang += unformatNumber(sotienhang[i].value);
			tongtienkhach += unformatNumber(sotienkhach[i].value);
			tongtiendv += unformatNumber(phidichvu[i].value);
			tongsd++;
		}
	}
	
	$('#lbl_row_count').text(tongsd);
	$('#tongtienhang').val(formatNumber(Math.round(tongtienhang)));
	$('#tongtienkhach').val(formatNumber(Math.round(tongtienkhach)));
	$('#tongtiendv').val(formatNumber(Math.round(tongtiendv)));
}

function strToUpperCase(ln){
	$('#ct_noidi' + ln).val($.trim($('#ct_noidi' + ln).val()).toUpperCase());
	$('#ct_noiden' + ln).val($.trim($('#ct_noiden' + ln).val()).toUpperCase());
}

function insertRow(ln){
	var html = '';
	var loaihk_list = $('#loaihk_list').val();
	var danhxung_list = $('#danhxung_list').val();
	var chieubay_list = $('#chieubay_list').val();
	var aircode_list = $('#aircode_list').val();
	var ncc_list = $('#ncc_list').val();
	
	html += `<tr id="ct_line_${ln}">
				<td data-label="Loại HK"><select class="w-100" name="ct_loaihk[]" id="ct_loaihk${ln}">${loaihk_list}</select></td>
				<td data-label="Danh xưng"><select class="w-100" name="ct_danhxung[]" id="ct_danhxung${ln}">${danhxung_list}</select></td>
				<td data-label="Họ tên"><input type="text" class="text-start" maxlength="255" name="ct_hoten[]" id="ct_hoten${ln}" value="" /></td>
				<td data-label="Ngày sinh">
					<div class="d-flex align-items-center gap-1">
						<input class="text-end w-80" type="text" maxlength="10" name="ct_ngaysinh[]" id="ct_ngaysinh${ln}" value="" />
						<img class="cursor-pointer" border="0" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="ct_ngaysinh_trigger${ln}" align="absmiddle" />
					</div>
				</td>
				<td data-label="Chiều"><select class="w-100" name="ct_chieubay[]" id="ct_chieubay${ln}">${chieubay_list}</select></td> 
				<td data-label="Mã hãng"><input style="width: 80px;" type="text" class="ac airline" id="ct_airline_code${ln}" name="ct_airline_code[]"></td>
				<td data-label="Nơi đi"><input onblur="strToUpperCase(${ln})" type="text" class="text-start" maxlength="3" name="ct_noidi[]" id="ct_noidi${ln}" value="" /></td>
				<td data-label="Nơi đến"><input onblur="strToUpperCase(${ln})" type="text" class="text-start" maxlength="3" name="ct_noiden[]" id="ct_noiden${ln}" value="" /></td>
				<td data-label="Số vé"><input type="text" class="text-start" maxlength="25" name="ct_sove[]" id="ct_sove${ln}" value="" /></td>
				<td data-label="PNR"><input type="text" class="text-start" maxlength="25" name="ct_pnr[]" id="ct_pnr${ln}" value="" /></td>
				<td data-label="NCC"><select id="ct_nhacc_id${ln}" name="ct_nhacc_id[]" class="w-100" ><option value=""></option>${ncc_list}</select></td>
				<td data-label="Tiền lấy về"><input class="text-end allow-number-only" onblur="calculateLineTotal(${ln})" type="text" maxlength="25" name="ct_sotienhang[]" id="ct_sotienhang${ln}" value="0" /></td>
				<td data-label="Tiền HV"><input class="text-end allow-number-only" onblur="calculateLineTotal(${ln})" type="text" maxlength="25" name="ct_sotienkhach[]" id="ct_sotienkhach${ln}" value="0" /></td>
				<td data-label="Phí hoàn"><input class="text-end allow-number-only2" onblur="calculateLineTotal(${ln})" type="text" maxlength="25" name="ct_phidichvu[]" id="ct_phidichvu${ln}" value="0" /></td>
				<td data-label="Đã hoàn" class="text-center align-middle">
					<input type="checkbox" name="ct_chk_dahoan[]" id="ct_chk_dahoan${ln}" checked="checked" />
					<input type="hidden" name="ct_dahoan[]" id="ct_dahoan${ln}" value="1" />
				</td>
				<td data-label="Xóa dòng">
					<button title="Xóa" type="button" onclick="markRowDeleted(${ln})" style="background:transparent; border:0;" >
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
					</button>
					<input type="hidden" value="0" name="ct_deleted[]" id="ct_deleted${ln}" />
					<input type="hidden" name="ct_detail_id[]" id="ct_detail_id${ln}" value="" />
				</td>
			</tr>`;
			
	return html;
}

function checkLineItems(){
	var arr = document.getElementsByName('ct_deleted[]');
	for(var i=0; i<arr.length; i++){
		if(arr[i].value == '0' && $.trim($('#ct_hoten' + i).val()) == ''){
			alert('Họ tên không hợp lệ');
			$('#ct_hoten' + i).focus();
			return false;
		}
		if(arr[i].value == '0' && $.trim($('#ct_airline_code' + i).val()) == ''){
			alert('Mã hãng không hợp lệ');
			$('#ct_airline_code' + i).focus();
			return false;
		}
		if(arr[i].value == '0' && $.trim($('#ct_noidi' + i).val()) == ''){
			alert('Nơi đi không hợp lệ');
			$('#ct_noidi' + i).focus();
			return false;
		}
		if(arr[i].value == '0' && $.trim($('#ct_noiden' + i).val()) == ''){
			alert('Nơi đến không hợp lệ');
			$('#ct_noiden' + i).focus();
			return false;
		}
		// if(arr[i].value == '0' && unformatNumber($('#ct_phidichvu' + i).val()) < 0){
		// 	alert('Phí dịch vụ phải lớn hơn hoặc bằng 0');
		// 	$('#ct_phidichvu' + i).focus();
		// 	return false;
		// }
	}
	return true;
}