$(document).ready(function () {
	// HD hủy, đã ký chỉ được view
	if($("#tinhtrang").val() == '-1' || $("#tinhtrang").val() == '2'){
		$("#edit_button").remove();
	} 

    // Click button Remind
	$(document).on('click', 'input[name="btnCancelInvoice"]', function () {
		$("#dlgCancelInvoice").dialog({
			title: "Lý do hủy hóa đơn đầu ra",
			width: 400,
			modal: true,
			resizable: false,
		});
	});

	// Hủy hóa đơn
	$(document).on('click', '#btn-confirm-cancel__invoice', function () {
		let description = $("#txtCancelInvoice").val();
		let is_signed = $("input[name='is_signed']").val();
		let hd_record = $("input[name='record']").val();
		let hd_record_name = $("input[name='record_name']").val();
		let company_unit = $("input[name='company_unit']").val();

        if (description.length == 0 || hd_record_name.length == 0 ||  hd_record.length == 0) return;
		else if (description.length < 12) {
			let text_warning = 'Lí do hủy hóa đơn quá ngắn!';
			showToastWarning(text_warning);
			return;
		}

        $.ajax({
			url: "index.php?entryPoint=entryPointEC_HoaDonBan",
			data: {
				hd_record: hd_record,
				hd_record_name: hd_record_name,
				is_signed: is_signed,
				company_unit: company_unit,
				description: description,
				for: "reasonCancelInvoice",
			},
			type: "POST",
			cache: false,
			success: function (response) {
				$('#dlgCancelInvoice').dialog('close');
				if(response == 1){
					let text_warning = 'Hủy hóa đơn thành công.';
					showModalNotify(1, text_warning);
					$('.modal-overlay, .btn-modal-close').addClass('reload');
				} else {
					let text_warning = 'Hủy hóa đơn thất bại. Vui lòng liên hệ IT để được hỗ trợ.';
					showModalNotify(0, text_warning);
					$('.modal-overlay, .btn-modal-close').addClass('reload');
				}
			}
		});
	});

	// Ghi/Cập nhật hóa đơn
	$('#btn-confirm-create-invoice').click(function(){
		let invID = $('input[name="invID"]').val();
		let invoice_data = {}; 
		invoice_data.invRef 		= $('input[name="invRef"]').val();
		invoice_data.invDate 		= $('input[name="invDate"]').val();
		invoice_data.invRefDate 	= $('input[name="invRefDate"]').val();
		invoice_data.invSubTotal 	= $('input[name="invSubTotal"]').val();
		invoice_data.invVatAmount 	= $('input[name="invVatAmount"]').val();
		invoice_data.invTotalAmount = $('input[name="invTotalAmount"]').val();
		invoice_data.invPayment		= $('input[name="invPayment"]').val();
		invoice_data.invCustomer	= $('input[name="invCustomer"]').val();

		let buyer_data = {}; 
		buyer_data.buyerName 	= $('input[name="buyerName"]').val();
		buyer_data.buyerCompany = $('input[name="buyerCompany"]').val();
		buyer_data.buyerEmail 	= $('input[name="buyerEmail"]').val();
		buyer_data.buyerTax 	= $('input[name="buyerTax"]').val();
		buyer_data.buyerAddress = $('input[name="buyerAddress"]').val();
		buyer_data.buyerBank 	= $('input[name="buyerBank"]').val();
		buyer_data.buyerAcc 	= $('input[name="buyerAcc"]').val();

		let item_data = {};
		item_data.itemCode 			= $("input[name='itemCode[]']").map(function(){return $(this).val();}).get();
		item_data.itemName 			= $("input[name='itemName[]']").map(function(){return $(this).val();}).get();
		item_data.itemUnit 			= $("input[name='itemUnit[]']").map(function(){return $(this).val();}).get();
		item_data.itemQuantity 		= $("input[name='itemQuantity[]']").map(function(){return $(this).val();}).get();
		item_data.itemPrice 		= $("input[name='itemPrice[]']").map(function(){return $(this).val();}).get();
		item_data.itemVatRate 		= $("input[name='itemVatRate[]']").map(function(){return $(this).val();}).get();
		item_data.itemVatAmnt 		= $("input[name='itemVatAmnt[]']").map(function(){return $(this).val();}).get();
		item_data.itemAmountNoVat 	= $("input[name='itemAmountNoVat[]']").map(function(){return $(this).val();}).get();

		// Validate
		if(invID.length == 0) {
			alert("Không tìm thấy ID hóa đơn");
			return 0;
		}
		if(invoice_data.invRef.length == 0) {
			alert("Không tìm thấy số chứng từ");
			return 0;
		}
		if(invoice_data.invTotalAmount < 1 || invoice_data.invSubTotal < 1) {
			alert("Số tiền không hợp lệ");
			return 0;
		}
		if(buyer_data.buyerName.length == 0 && buyer_data.buyerCompany.length == 0) {
			alert("Vui lòng bổ sung Tên khách hàng hoặc Tên công ty");
			return 0;
		}
		if(item_data.itemName.length < 1) {
			alert("Không tìm thấy Sản phẩm/Dịch vụ");
			return 0;
		}

		closeDialog('dialog-create-invoice');
		$('.container-waiting').show();
		$.ajax({
			url: "index.php?entryPoint=entryPointWinInvoice",
			data: {
				type			: 1,
				invoice_id		: invID,
				invoice_data 	: JSON.stringify(invoice_data),
				buyer_data		: JSON.stringify(buyer_data),
				item_data 		: JSON.stringify(item_data)
			},
			type: "POST",
			cache: false,
			success: function (response) {
				$('.container-waiting').hide();
				let res = JSON.parse(response);

				if(res.error == 0) {
					showModalNotify(1, res['message']);
					countdownAndReload(3);
				}
				else showModalNotify(0, res['message'], format_html_data_error(JSON.stringify(res['description'])));
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$('.container-waiting').hide();

                let text_modal_error = 'ERROR ('+errorThrown+'): Vui lòng liên hệ bộ phận IT.';
                showModalNotify(0, text_modal_error)

                console.error(XMLHttpRequest);
				console.error("Status: " + textStatus);
				console.error("Error: " + errorThrown);
			}
		});
	});

	// Bỏ ghi hóa đơn
	$('#btn-confirm-remove-invoice').click(function(){
		let invID = $("input[name='record']").val();
		let invRef = $(this).attr('data');

		// Validate
		if(invID.length == 0) {
			alert("Không tìm thấy ID hóa đơn");
			return 0;
		}
		if(invRef.length == 0) {
			alert("Không tìm thấy số chứng từ");
			return 0;
		}

		closeDialog('dialog-remove-invoice');
		$('.container-waiting').show();
		$.ajax({
			url: "index.php?entryPoint=entryPointWinInvoice",
			data: {
				type : 0,
				invoice_id : invID,
				invRef : invRef
			},
			type: "POST",
			cache: false,
			success: function (response) {
				$('.container-waiting').hide();
				let res = JSON.parse(response);

				if(res.error == 0) {
					showModalNotify(1, res['message']);
					countdownAndReload(3);
				}
				else showModalNotify(0, res['message'], format_html_data_error(JSON.stringify(res['description'])));
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$('.container-waiting').hide();

                let text_modal_error = 'ERROR ('+errorThrown+'): Vui lòng liên hệ bộ phận IT.';
                showModalNotify(0, text_modal_error)

                console.error(XMLHttpRequest);
				console.error("Status: " + textStatus);
				console.error("Error: " + errorThrown);
			}
		});
	});

	// Ký số hóa đơn
	$('#btn-confirm-sign-invoice').click(function(){
		let invID  = $('input[name="invID"]').val();
		let invRef = $('input[name="invRef"]').val();

		// Validate
		if(invID.length == 0) {
			alert("Không tìm thấy ID hóa đơn");
			return 0;
		}
		if(invRef.length == 0) {
			alert("Không tìm thấy số chứng từ");
			return 0;
		}

		closeDialog('dialog-sign-invoice');
		$('.container-waiting').show();
		$.ajax({
			url: "index.php?entryPoint=entryPointWinInvoice",
			data: {
				type : 2,
				invoice_id : invID,
				invRef : invRef
			},
			type: "POST",
			cache: false,
			success: function (response) {
				$('.container-waiting').hide();
				let res = JSON.parse(response);

				if(res.error == 0) {
					showModalNotify(1, res['message']);
					countdownAndReload(3);
				}
				else showModalNotify(0, res['message'], format_html_data_error(JSON.stringify(res['description'])));
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$('.container-waiting').hide();

                let text_modal_error = 'ERROR ('+errorThrown+'): Vui lòng liên hệ bộ phận IT.';
                showModalNotify(0, text_modal_error)

                console.error(XMLHttpRequest);
				console.error("Status: " + textStatus);
				console.error("Error: " + errorThrown);
			}
		});
	});

	// Chuyển trạng thái sang đã kí
	$('#frmSignTP').submit(function (e) {
		let sohoadon = $('input[name="sohoadon"]').val();
		if(sohoadon.length != 0){
			$(this).submit();
		} else {
			e.preventDefault();
			let text_warning = 'Vui lòng nhập số hóa đơn!';
			showToastWarning(text_warning);
			return false;
		}
	});
})

function format_html_data_error(json) {
	let obj = JSON.parse(json);

	let html = '';
	$.each(obj, function(key, val) {             
		html += `<p style="font-size:13px; color:#000"><b>${key} : </b>${val}</p>`;         
	});
	return html; 
}

function check_date(str) {
	if(str === undefined || str.length != 10 || str.indexOf("-") == -1) return false;

	let ToDate = new Date();
	let y = ToDate.getFullYear();
	let m = ToDate.getMonth() + 1;
	let d = ToDate.getDate();
	let DataCheck = {};
	let parts = str.split('-');
	if(parts[2].length == 4) { // Day first
		let current_date = (d < 10) ? '0'+d.toString() : d.toString();
		current_date += '-' + ((m < 10) ? '0'+m.toString() : m.toString());
		current_date += '-' + y;
		if(current_date == str) return true;

		DataCheck = new Date(parts[2], parts[1] - 1, parts[0]);
	}
	else if(parts[0].length == 4) {
		let current_date = y.toString();
		current_date += '-' + ((m < 10) ? '0'+m.toString() : m.toString());
		current_date += '-' + ((d < 10) ? '0'+d.toString() : d.toString());
		if(current_date == str) return true;

		DataCheck = new Date(parts[0], parts[1] - 1, parts[2]); // Year first
	}

	if (DataCheck.getTime() < ToDate.getTime()) return false;
	return true;
}