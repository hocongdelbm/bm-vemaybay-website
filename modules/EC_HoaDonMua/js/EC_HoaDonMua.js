
$(document).ready(function() {
	// không cho nhấn phím enter trên form
	$("form").bind("keypress", function(e) {
		  if (e.keyCode == 13) return false;
	});
	
	// xử lý sự kiện save
	$('#btnSave').click(function(){
		if(check_form('EditView')){
			
			if(ktraSoVe() && ktraHanhTrinh() && ktraChiTietHoaDon()){
				if($('#nhanhoadon').is(':checked') && $('#sohoadon').val() == ''){
					$('#sohoadon').focus();
					 alert('Vui lòng nhập số hóa đơn');
					 return false;
				}
				
				  $.ajax({	
					  cache : false,
					  type: 'GET',
					  data: 'ma=' + $('#name').val() + '&id=' + $('#EditView input[name=record]').val() + '&tbl=ec_hoadonmua&fld=name',
					  url: 'index.php?module=EC_HoaDonMua&entryPoint=checkexist&action=EditView',
					  success: function(output){
						  $('#EditView').append(output);
						  if(is_exist == 0){
							$('#EditView input[name=action]').val('Save');
							$('#EditView').submit();
						  }
					  }
				  });
			}// end if ktra
		}// end if check form	
	});
	
	// checkbox đã nhận hóa đơn
	$('#nhanhoadon').change(function(){
		if($(this).is(':checked')){
			$('#sohoadon').val('');
			$('#span_sohoadon').show();
		}else{
			$('#sohoadon').val('');
			$('#span_sohoadon').hide();
		}
	});
});


// kiểm tra chi tiết hóa đơn
function ktraChiTietHoaDon(){
	var deleted = document.getElementsByName('ct_deleted[]');
	var ct = 0;
	if(deleted.length <= 0){
		alert('Vui lòng bổ sung chi tiết hóa đơn');
		return false;
	} else {
		for(var i = 0; i < deleted.length; i++){
			if(deleted[i].value == '0'){
				ct++;
			}
		}// end for
		if(ct <= 0) {
			alert('Vui lòng bổ sung chi tiết hóa đơn');
			return false;
		}
	}// end else
	return true;
}


// kiểm tra số vé 
function ktraSoVe(){
	var arr = document.getElementsByName('ct_sove[]');
	var deleted = document.getElementsByName('ct_deleted[]');
	for(var i = 0; i < arr.length; i++){
		if(arr[i].value == '' && deleted[i].value == '0'){
			alert('Số vé không được trống');
			$('#ct_sove' + i).focus();
			return false;
		}
		if(arr[i].value.length < 4 && deleted[i].value == '0'){
			alert('Lỗi: Độ dài chuỗi Số vé phải lớn hơn hoặc bằng 4.');
			$('#ct_sove' + i).focus();
			$('#ct_sove' + i).select();
			return false;
		}
	}
	return true;
}

// kiểm tra hành trình
function ktraHanhTrinh(){
	var arr = document.getElementsByName('ct_hanhtrinh[]');
	var deleted = document.getElementsByName('ct_deleted[]');
	for(var i = 0; i < arr.length; i++){
		if(arr[i].value == '' && deleted[i].value == '0'){
			alert('Hành trình không được trống');
			$('#ct_hanhtrinh' + i).focus();
			return false;
		}
		if(arr[i].value.length < 6 && deleted[i].value == '0'){
			alert('Lỗi: Độ dài chuỗi Hành trình phải lớn hơn hoặc bằng 6.');
			$('#ct_hanhtrinh' + i).focus();
			$('#ct_hanhtrinh' + i).select();
			return false;
		}
	}
	return true;
}


function getHTTPObject_ajax() {
		var xmlhttp;
		/*@cc_on
		@if (@_jscript_version >= 5)
		try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
		try {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (E) {
		xmlhttp = false;
		}
		}
		@else
		xmlhttp = false;
		@end @*/
		if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
			try {
				xmlhttp = new XMLHttpRequest();
			} catch (e) {
				xmlhttp = false;
			}
		}
		return xmlhttp;
	}


// Creating http AJAX Object
var http = getHTTPObject_ajax(); // We create the HTTP Object	

function handle_display_popup(){
	if (http.readyState == 4) {
		var txt = http.responseText;
		if(txt!=''){
			alert(txt);
			document.getElementById('maincodeid' + lineno ).innerHTML = txt;
			//document.getElementById('div_message_popup').style.display = "block";
		}
	}
}
	

/**
 * The reply data must be a JSON array structured with the following information:
 *  1) form name to populate
 *  2) associative array of input names to values for populating the form
 */
var fromPopupReturn  = false;
function setObjectReturn(popupReplyData)
{
	fromPopupReturn = true;
	var formName = popupReplyData.form_name;
	var nameToValueArray = popupReplyData.name_to_value_array;
	
	for (var theKey in nameToValueArray)
	{
		if(theKey == 'toJSON')
		{
			/* just ignore */
		}
		else
		{
			var displayValue = nameToValueArray[theKey].replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');;
			/** depreciated
			 window.document.forms[form_name].elements[the_key].value = displayValue;
			 */
			//alert(theKey + " => " + displayValue);
			document.getElementById(theKey).value = displayValue;
			/** uncomment to copy value on select
			 if (theKey.search('product_list_price') != -1) {
			 	var ln = theKey.slice(18);
				document.getElementById('product_unit_price' + ln).value = displayValue;
			 }
			 */
		}
	}
	/** uncomment to copy value on select
	 calculateProductLine(ln);
	 */
//	 subcode1(document.getElementById('product_id' + lineno).value);
}

function XoaDongCTHD(row_id, deleted_id){
	document.getElementById(row_id).style.display = 'none';
	document.getElementById(deleted_id).value = '1';
	TinhTongHoaDon();
}

/*
 	convert a string to number
*/
function unformatNumber(str)
{
	var grp_sep = String(document.getElementById('grp_seperator').value);
	var dec_sep = String(document.getElementById('dec_seperator').value);
	
	str = String(str);
	
	str = str.replace(grp_sep, '');
	str = str.replace(grp_sep, '');
	str = str.replace(grp_sep, '');
	str = str.replace(grp_sep, '');
	
	str = str.replace(dec_sep, '.');
	
	num = Number(str);
	
	return num;
}


/*
 	convert number to a string
*/
function formatNumber(str)
{
	var grp_sep = String(document.getElementById('grp_seperator').value);
	var dec_sep = String(document.getElementById('dec_seperator').value);
	
	num = Number(str);
	str = formatCurrency(num);
	
	str = str.replace(/,/, '{,}').replace(/\./, '{.}');
	str = str.replace(/{,}/, grp_sep).replace(/{.}/, dec_sep);
	
	return str;
}


/*
 	use for formatNumber
*/
function formatCurrency(strValue)
{
	strValue = strValue.toString().replace(/\$|\,/g,'');
	dblValue = parseFloat(strValue);

	blnSign = (dblValue == (dblValue = Math.abs(dblValue)));
	dblValue = Math.floor(dblValue*100+0.50000000001);
	intCents = dblValue%100;
	strCents = intCents.toString();
	dblValue = Math.floor(dblValue/100).toString();
	if(intCents<10)
		strCents = "0" + strCents;
	for (var i = 0; i < Math.floor((dblValue.length-(1+i))/3); i++)
		dblValue = dblValue.substring(0,dblValue.length-(4*i+3))+','+
		dblValue.substring(dblValue.length-(4*i+3));
		
	if(parseInt(document.getElementById("sig_digits").value) > 0)
		return (((blnSign)?'':'-') + dblValue + '.' + strCents);
	else
		return (((blnSign)?'':'-') + dblValue);
}

// insert payment line
function ThemDongCTHD(ln){
	
	var x=document.getElementById('tbl_ChiTietHoaDon').insertRow(1);
	x.setAttribute('id', 'cthd_line' + ln);
	var a=x.insertCell(0);
	var b=x.insertCell(1);
	var c=x.insertCell(2);
	var d=x.insertCell(3);
	var e=x.insertCell(4);
	var f=x.insertCell(5);
	var g=x.insertCell(6);
	var h=x.insertCell(7);
	
	a.innerHTML = '<input tabindex="116" type="text" size="22" name="ct_sove[]" id="ct_sove'+ ln +'" maxlength="255" value="" />';
	
	b.innerHTML = '<input tabindex="116" type="text" size="22" name="ct_hanhtrinh[]" id="ct_hanhtrinh'+ ln +'" maxlength="150" value="" />';
	
	c.innerHTML = '<input tabindex="116" onclick="select()" onblur="TinhCTHD('+ ln +')" type="text" size="2" name="ct_soluong[]" id="ct_soluong'+ ln +'" maxlength="5" value="" style="text-align:right" />';
	
	d.innerHTML = '<input tabindex="116" onclick="select()" onblur="TinhCTHD('+ ln +')" type="text" size="22" name="ct_dongia[]" id="ct_dongia'+ ln +'" maxlength="15" value="" style="text-align:right" />';
	
	e.innerHTML = '<p><select id="ct_thuesuat'+ ln +'" name="ct_thuesuat[]" onchange="TinhCTHD('+ ln +')" >'+ thuesuat_list +'</select>%<input tabindex="116" onclick="select()" onblur="TinhCTHD('+ ln +')" type="text" size="10" name="ct_thuevat[]" id="ct_thuevat'+ ln +'" maxlength="15" value="" style="text-align:right" /></p>';
	
	f.innerHTML = '<input tabindex="116" onclick="select()" onblur="TinhCTHD('+ ln +')" type="text" size="22" name="ct_phisanbay[]" id="ct_phisanbay'+ ln +'" maxlength="15" value="" style="text-align:right" />';
	
	g.innerHTML = '<input tabindex="116" onclick="select()" onblur="TinhCTHD('+ ln +')" type="text" size="25" name="ct_thanhtien[]" id="ct_thanhtien'+ ln +'" maxlength="15" value="" style="text-align:right" />';
	
	h.innerHTML = '<input tabindex="116" onclick="XoaDongCTHD(\'cthd_line'+ ln +'\',\'ct_deleted'+ ln +'\')" type="button" name="btnXoaDong" id="btnXoaDong" value="'+ LBL_XOA_DONG +'" title="'+ LBL_XOA_DONG +'" /><input type="hidden" name="ct_deleted[]" id="ct_deleted'+ ln +'" value="0" /><input type="hidden" name="ct_id[]" id="ct_id'+ ln +'" value="" />';

	ln++;
	document.getElementById('btnThemDong').onclick = function() {
		ThemDongCTHD(ln);
	}
}

// Tính từng dòng chi tiết hóa đơn
function TinhCTHD(ln){
	var soluong = unformatNumber($('#ct_soluong' + ln).val());
	var thuesuat = unformatNumber($('#ct_thuesuat' + ln).val());
	var dongia = unformatNumber($('#ct_dongia' + ln).val());
	var thuevat = dongia * thuesuat / 100;
	var phisanbay = unformatNumber($('#ct_phisanbay' + ln).val());
	var thanhtien = soluong * (dongia + thuevat + phisanbay);
	$('#ct_soluong' + ln).val(formatNumber(soluong));
	$('#ct_dongia' + ln).val(formatNumber(dongia));
	$('#ct_thuevat' + ln).val(formatNumber(thuevat));
	$('#ct_phisanbay' + ln).val(formatNumber(phisanbay));
	$('#ct_thanhtien' + ln).val(formatNumber(thanhtien));
	TinhTongHoaDon();
}

// tính tổng tất cả các dòng chi tiết hóa đơn
function TinhTongHoaDon(){
	var arr = document.getElementsByName('ct_deleted[]');
	var subtotal = document.getElementsByName('ct_thanhtien[]');
	var total = 0;
	var tongtien = 0;
	
	for(var i = 0; i < arr.length; i++){
		if(arr[i].value == 0)
			total += unformatNumber(subtotal[i].value);
	} // end for
	
	var phihanhly = unformatNumber($('#phihanhly').val()); 
	var phihoandoive = unformatNumber($('#phihoandoive').val());
	var phikhac = unformatNumber($('#phikhac').val());
	var ptram_giamgia = unformatNumber($('#ptram_giamgia').val());
	
	var giamgia = 0;
	if(ptram_giamgia != 0)
		giamgia = total * ptram_giamgia / 100;
	else
		giamgia = unformatNumber($('#giamgia').val());

	tongtien = total + phihanhly + phihoandoive + phikhac - giamgia;
	
	$('#phihanhly').val(formatNumber(phihanhly));
	$('#phihoandoive').val(formatNumber(phihoandoive));
	$('#phikhac').val(formatNumber(phikhac));
	$('#giamgia').val(formatNumber(giamgia));
	$('#tongtien').val(formatNumber(tongtien));
}