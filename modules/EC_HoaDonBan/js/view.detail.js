const ENTRYPOINT = "index.php?entryPoint=entryPointGeneral";
const ENTRYCLASS = "entryOutputInvoiceClass";

$(document).ready(function () {
	// HD hủy, đã ký chỉ được view
	if ($("#tinhtrang").val() == '-1' || $("#tinhtrang").val() == '2') {
		$("#edit_button").remove();
	}

	// Ghi/Cập nhật hóa đơn
	$('#btn-confirm-create-invoice').click(function () {
		let invID = $('input[name="invID"]').val();
		let invoice_data = {};
		invoice_data.invRef = $('input[name="invRef"]').val();
		invoice_data.invSerial = $('input[name="invSerial"]').val();
		invoice_data.invDate = $('input[name="invDate"]').val();
		invoice_data.invRefDate = $('input[name="invRefDate"]').val();
		invoice_data.invSubTotal = $('input[name="invSubTotal"]').val();
		invoice_data.invVatAmount = $('input[name="invVatAmount"]').val();
		invoice_data.invTotalAmount = $('input[name="invTotalAmount"]').val();
		invoice_data.invPayment = $('input[name="invPayment"]').val();
		invoice_data.invCustomer = $('input[name="invCustomer"]').val();

		let buyer_data = {};
		buyer_data.buyerName = $('input[name="buyerName"]').val();
		buyer_data.buyerCompany = $('input[name="buyerCompany"]').val();
		buyer_data.buyerEmail = $('input[name="buyerEmail"]').val();
		buyer_data.buyerTax = $('input[name="buyerTax"]').val();
		buyer_data.buyerAddress = $('input[name="buyerAddress"]').val();
		buyer_data.buyerBank = $('input[name="buyerBank"]').val();
		buyer_data.buyerAcc = $('input[name="buyerAcc"]').val();
		buyer_data.buyerCitizenIDNumber = $('input[name="buyerCitizenIDNumber"]').val();
		buyer_data.buyerPassportNumber = $('input[name="buyerPassportNumber"]').val();

		let item_data = {};
		item_data.itemCode = $("input[name='itemCode[]']").map(function () { return $(this).val(); }).get();
		item_data.itemName = $("input[name='itemName[]']").map(function () { return $(this).val(); }).get();
		item_data.itemUnit = $("input[name='itemUnit[]']").map(function () { return $(this).val(); }).get();
		item_data.itemQuantity = $("input[name='itemQuantity[]']").map(function () { return $(this).val(); }).get();
		item_data.itemPrice = $("input[name='itemPrice[]']").map(function () { return $(this).val(); }).get();
		item_data.itemVatRate = $("input[name='itemVatRate[]']").map(function () { return $(this).val(); }).get();
		item_data.itemVatAmnt = $("input[name='itemVatAmnt[]']").map(function () { return $(this).val(); }).get();
		item_data.itemAmountNoVat = $("input[name='itemAmountNoVat[]']").map(function () { return $(this).val(); }).get();

		// Validate
		if (invID.length == 0) {
			showModalNotify("warning", "Không tìm thấy ID hóa đơn");
			return 0;
		}
		if (invoice_data.invRef.length == 0) {
			showModalNotify("warning", "Không tìm thấy số chứng từ");
			return 0;
		}
		if (invoice_data.invTotalAmount < 1 || invoice_data.invSubTotal < 1) {
			showModalNotify("warning", "Số tiền không hợp lệ");
			return 0;
		}
		if (buyer_data.buyerName.length == 0 && buyer_data.buyerCompany.length == 0) {
			showModalNotify("warning", "Vui lòng bổ sung Tên khách hàng hoặc Tên công ty");
			return 0;
		}
		if (item_data.itemName.length < 1) {
			showModalNotify("warning", "Không tìm thấy Sản phẩm/Dịch vụ");
			return 0;
		}

		$.ajax({
			url: ENTRYPOINT,
			type: "POST",
			contentType: "application/json",
			data: JSON.stringify({
				class: ENTRYCLASS,
				method: "set",
				params: {
					recordId: invID,
					invoiceData: invoice_data,
					buyerData: buyer_data,
					itemData: item_data
				}
			}),
			cache: false,
			beforeSend: function () {
				closeDialog('dialog-create-invoice');
				$('.container-waiting').show();
			},
			success: function (response) {
				$('.container-waiting').hide();

				let res = JSON.parse(response);
				if ('status' in res && res.status == 1) {
					showModalNotify(1, res.message ?? 'Thao tác thành công');
					countdownAndReload(3);
				}
				else {
					let description = 'description' in res ? format_html_data_error(res.description) : '';
					showModalNotify(0, res.message ?? 'Đã xảy ra lỗi', description);
				}
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				$('.container-waiting').hide();
				showModalNotify(0, `ERROR (${errorThrown}): Vui lòng liên hệ bộ phận IT`)
				console.error(XMLHttpRequest);
				console.error("Status: " + textStatus);
				console.error("Error: " + errorThrown);
			}
		});
	});

	// Ghi/Cập nhật hóa đơn Misa
	$('#btn-confirm-create-misa-invoice').click(function () {
		// ============================================================
		// 1. Thu thập dữ liệu — dùng prefix misa_ tránh trùng WinInvoice
		// ============================================================
		const invID = $('input[name="misa_invID"]').val();
		const orgRefNo = $('input[name="misa_orgRefNo"]').val();
		var invSerial = $('input[name="misa_invSerial"]').val();
		var refdate = $('input[name="misa_refdate"]').val();
		var paymentMethod = $('input[name="misa_paymentMethod"]').val();
		var invSubTotal = parseFloat($('input[name="misa_invSubTotal"]').val()) || 0;
		var invVatAmount = parseFloat($('input[name="misa_invVatAmount"]').val()) || 0;
		var invTotalAmount = parseFloat($('input[name="misa_invTotalAmount"]').val()) || 0;

		var buyerName = $('input[name="misa_buyerName"]').val();
		var buyerCompany = $('input[name="misa_buyerCompany"]').val();
		var buyerEmail = $('input[name="misa_buyerEmail"]').val();
		var buyerTax = $('input[name="misa_buyerTax"]').val();
		var buyerAddress = $('input[name="misa_buyerAddress"]').val();

		// ============================================================
		// 2. Validate
		// ============================================================
		if (!invID) {
			showModalNotify('warning', 'Không tìm thấy ID hóa đơn');
			return;
		}
		if (!orgRefNo) {
			showModalNotify('warning', 'Không tìm thấy số chứng từ');
			return;
		}
		if (invTotalAmount < 1 || invSubTotal < 1) {
			showModalNotify('warning', 'Số tiền không hợp lệ');
			return;
		}
		if (!buyerName && !buyerCompany) {
			showModalNotify('warning', 'Vui lòng bổ sung Tên khách hàng hoặc Tên công ty');
			return;
		}

		// Collect items
		var itemNames = $("input[name='itemName[]']").map(function () { return $(this).val(); }).get();
		if (itemNames.length < 1) {
			showModalNotify('warning', 'Không tìm thấy Sản phẩm/Dịch vụ');
			return;
		}

		// ============================================================
		// 3. Build đúng cấu trúc MISA (voucher / details / saInvoice)
		// ============================================================
		const voucher = {
			org_refid: invID,
			org_refno: orgRefNo,
			inv_refid: invID,
			account_object_code: buyerTax || buyerEmail || '',
			account_object_name: buyerCompany || buyerName,
			account_object_address: buyerAddress,
			journal_memo: 'Bán hàng cho ' + (buyerCompany || buyerName),
			refdate: refdate,
			posted_date: refdate
		};

		let details = [];
		let itemCodes = $("input[name='itemCode[]']").map(function () { return $(this).val(); }).get();
		let itemUnits = $("input[name='itemUnit[]']").map(function () { return $(this).val(); }).get();
		let itemQtys = $("input[name='itemQuantity[]']").map(function () { return $(this).val(); }).get();
		let itemPrices = $("input[name='itemPrice[]']").map(function () { return $(this).val(); }).get();
		let itemVatRates = $("input[name='itemVatRate[]']").map(function () { return $(this).val(); }).get();
		let itemVatAmnts = $("input[name='itemVatAmnt[]']").map(function () { return $(this).val(); }).get();
		let itemAmounts = $("input[name='itemAmountNoVat[]']").map(function () { return $(this).val(); }).get();

		for (let i = 0; i < itemNames.length; i++) {
			let qty = parseFloat(itemQtys[i]) || 0;
			let price = parseFloat(itemPrices[i]) || 0;
			let amount = parseFloat(itemAmounts[i]) || 0;
			let vatRate = parseFloat(itemVatRates[i]) || 0;
			let vatAmnt = parseFloat(itemVatAmnts[i]) || 0;

			details.push({
				inventory_item_code: itemCodes[i] || '',
				inventory_item_name: itemNames[i] || '',
				description: itemNames[i] || '',
				unit_name: itemUnits[i] || '',
				quantity: qty.toFixed(1),
				unit_price: price.toFixed(1),
				amount_oc: amount.toFixed(4),
				amount: amount.toFixed(4),
				vat_rate: vatRate.toFixed(1),
				vat_amount_oc: vatAmnt.toFixed(4),
				vat_amount: vatAmnt.toFixed(4),
				account_object_code: buyerTax || '',
				account_object_name: buyerCompany || buyerName,
			});
		}

		const saInvoice = {
			account_object_name: buyerCompany || buyerName,
			account_object_tax_code: buyerTax || '',
			inv_series: invSerial,
			currency_id: 'VND',
			exchange_rate: 1,
			payment_method: paymentMethod || 'TM/CK',
			total_sale_amount_oc: invSubTotal,
			total_sale_amount: invSubTotal,
			total_vat_amount_oc: invVatAmount,
			total_vat_amount: invVatAmount,
			total_amount_oc: invTotalAmount,
			total_amount: invTotalAmount,
		};

		// ============================================================
		// 4. Gọi API
		// ============================================================
		$.ajax({
			url: ENTRYPOINT,
			type: 'POST',
			contentType: 'application/json',
			data: JSON.stringify({
				class: ENTRYCLASS,
				method: 'setMisa',
				params: {
					voucher: voucher,
					details: details,
					saInvoice: saInvoice,
				}
			}),
			cache: false,
			beforeSend: function () {
				$('#staticModalCreateMisaInvoice').modal('hide');
				$('.container-waiting').show();
			},
			success: function (response) {
				var res = JSON.parse(response);
				if ('status' in res && res.status == 1) {
					showModalNotify(1, res.message || 'Tạo chứng từ. Đợi đồng bộ dữ liệu');
					countdownAndReload(3);
				} else {
					var desc = res.description ? format_html_data_error(res.description) : '';
					showModalNotify(0, res.message || 'Đã xảy ra lỗi', desc);
				}
			},
			error: function (xhr, textStatus, errorThrown) {
				showModalNotify(0, 'ERROR (' + errorThrown + '): Vui lòng liên hệ bộ phận IT');
				console.error(xhr, textStatus, errorThrown);
			},
			complete: function () {
				$('.container-waiting').hide();
			}
		});
	});


	// Bỏ ghi hóa đơn
	$('#btn-confirm-remove-invoice').click(function () {
		let invID = $("input[name='record']").val();
		let invRef = $(this).attr('data-inv-ref');
		let invSerial = $(this).attr('data-inv-serial');

		// Validate
		if (invID.length == 0) {
			showModalNotify("warning", "Không tìm thấy ID hóa đơn");
			return 0;
		}
		if (invRef.length == 0) {
			showModalNotify("warning", "Không tìm thấy số chứng từ");
			return 0;
		}
		if (invSerial.length == 0) {
			showModalNotify("warning", "Không tìm thấy ký hiệu hóa đơn");
			return 0;
		}

		$.ajax({
			url: ENTRYPOINT,
			type: "POST",
			contentType: "application/json",
			data: JSON.stringify({
				class: ENTRYCLASS,
				method: "delete",
				params: {
					recordId: invID,
					invRef: invRef,
					invSerial: invSerial
				}
			}),
			cache: false,
			beforeSend: function () {
				closeDialog('dialog-remove-invoice');
				$('.container-waiting').show();
			},
			success: function (response) {
				$('.container-waiting').hide();

				let res = JSON.parse(response);
				if ('status' in res && res.status == 1) {
					showModalNotify(1, res.message ?? 'Thao tác thành công');
					countdownAndReload(3);
				}
				else {
					let description = 'description' in res ? format_html_data_error(res.description) : '';
					showModalNotify(0, res.message ?? 'Đã xảy ra lỗi', description);
				}
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				$('.container-waiting').hide();
				showModalNotify(0, `ERROR (${errorThrown}): Vui lòng liên hệ bộ phận IT`)
				console.error(XMLHttpRequest);
				console.error("Status: " + textStatus);
				console.error("Error: " + errorThrown);
			}
		});
	});

	// Hủy hóa đơn
	$(document).on('click', '#btn-confirm-cancel__invoice', function () {
		let description = $("#txtCancelInvoice").val();
		let hd_record = $("input[name='record']").val();
		let hd_record_name = $("input[name='record_name']").val();
		let hd_record_serial = $("input[name='record_serial']").val();
		let company_unit = $("input[name='company_unit']").val();
		let is_signed = $("input[name='is_signed']").val();

		if (description.length == 0 || hd_record_name.length == 0 || hd_record.length == 0 || hd_record_serial.length == 0) return;
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
				hd_record_serial: hd_record_serial,
				is_signed: is_signed,
				company_unit: company_unit,
				description: description,
				for: "reasonCancelInvoice",
			},
			type: "POST",
			cache: false,
			success: function (response) {
				$('#dlgCancelInvoice').dialog('close');
				if (response == 1) {
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

	// Click button Remind
	$(document).on('click', 'input[name="btnCancelInvoice"]', function () {
		$("#dlgCancelInvoice").dialog({
			title: "Lý do hủy hóa đơn đầu ra",
			width: 400,
			modal: true,
			resizable: false,
		});
	});

	// Ký số hóa đơn
	$('#btn-confirm-sign-invoice').click(function () {
		let invID = $('input[name="invID"]').val();
		let invRef = $('input[name="invRef"]').val();

		// Validate
		if (invID.length == 0) {
			showModalNotify("warning", "Không tìm thấy ID hóa đơn");
			return;
		}
		if (invRef.length == 0) {
			showModalNotify("warning", "Không tìm thấy số chứng từ");
			return;
		}

		$.ajax({
			url: ENTRYPOINT,
			type: "POST",
			contentType: "application/json",
			data: JSON.stringify({
				class: ENTRYCLASS,
				method: "sign",
				params: {
					recordId: invID,
					invRef: invRef
				}
			}),
			cache: false,
			beforeSend: function () {
				closeDialog('dialog-sign-invoice');
				$('.container-waiting').show();
			},
			success: function (response) {
				$('.container-waiting').hide();

				let res = JSON.parse(response);
				if ('status' in res && res.status == 1) {
					showModalNotify(1, res.message ?? 'Thao tác thành công');
					countdownAndReload(3);
				}
				else {
					let description = 'description' in res ? format_html_data_error(res.description) : '';
					showModalNotify(0, res.message ?? 'Đã xảy ra lỗi', description);
				}
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				$('.container-waiting').hide();
				showModalNotify(0, `ERROR (${errorThrown}): Vui lòng liên hệ bộ phận IT`)
				console.error(XMLHttpRequest);
				console.error("Status: " + textStatus);
				console.error("Error: " + errorThrown);
			}
		});
	});

	// Chuyển trạng thái sang đã kí
	$('#frmSignTP').submit(function (e) {
		let sohoadon = $('input[name="sohoadon"]').val();
		if (sohoadon.length != 0) {
			$(this).submit();
		} else {
			e.preventDefault();
			let text_warning = 'Vui lòng nhập số hóa đơn!';
			showToastWarning(text_warning);
			return false;
		}
	});
})

function format_html_data_error(objError) {
	if (!objError) return '';
	if (typeof objError === 'string') return objError;
	$.each(objError, function (key, val) {
		html += `<p style="font-size:13px; color:#000"><b>${key} : </b>${val}</p>`;
	});
	return html;
}

function check_date(str) {
	if (str === undefined || str.length != 10 || str.indexOf("-") == -1) return false;

	let ToDate = new Date();
	let y = ToDate.getFullYear();
	let m = ToDate.getMonth() + 1;
	let d = ToDate.getDate();
	let DataCheck = {};
	let parts = str.split('-');
	if (parts[2].length == 4) { // Day first
		let current_date = (d < 10) ? '0' + d.toString() : d.toString();
		current_date += '-' + ((m < 10) ? '0' + m.toString() : m.toString());
		current_date += '-' + y;
		if (current_date == str) return true;

		DataCheck = new Date(parts[2], parts[1] - 1, parts[0]);
	}
	else if (parts[0].length == 4) {
		let current_date = y.toString();
		current_date += '-' + ((m < 10) ? '0' + m.toString() : m.toString());
		current_date += '-' + ((d < 10) ? '0' + d.toString() : d.toString());
		if (current_date == str) return true;

		DataCheck = new Date(parts[0], parts[1] - 1, parts[2]); // Year first
	}

	if (DataCheck.getTime() < ToDate.getTime()) return false;
	return true;
}