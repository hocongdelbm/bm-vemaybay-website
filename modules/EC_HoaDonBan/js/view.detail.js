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

	$('#btn-add-receipt').click(function () {
		if (receiptPanelState.isPending) return;

		var hoadonId = getCurrentHoaDonId();
		if (!isValidGuid(hoadonId)) {
			showReceiptNotify('warning', 'Vui lòng lưu hóa đơn trước khi liên kết phiếu thu');
			return;
		}

		open_popup(
			'EC_Receipt_Voucher',
			900,
			600,
			'',
			true,
			false,
			{
				"call_back_function": "setReceiptVoucherReturn",
				"form_name": "#DetailView",
				"field_to_name_array": {
					"id": "receipt_id",
					"name": "receipt_name",
					"amount": "receipt_amount",
					"amount_type": "receipt_amount_type",
					"ngaychungtu": "receipt_ngaychungtu",
					"assigned_user_name": "receipt_assigned_user_name"
				}
			},
			"single",
			true
		);
	});

	$(document).on('click', '.btn-remove-receipt', function () {
		if (receiptPanelState.isPending) return;

		var $btn = $(this);
		var receiptId = ($btn.attr('data-id') || '').trim();
		var hoadonId = getCurrentHoaDonId();

		if (!isValidGuid(hoadonId) || !isValidGuid(receiptId)) {
			showReceiptNotify('error', 'Không thể xóa liên kết phiếu thu do dữ liệu không hợp lệ');
			return;
		}

		setReceiptPanelPending(true);
		$.ajax({
			url: ENTRYPOINT,
			type: 'POST',
			contentType: 'application/json',
			data: JSON.stringify({
				class: ENTRYCLASS,
				method: 'removeHoaDonReceipt',
				params: {
					hoadon_id: hoadonId,
					receipt_id: receiptId
				}
			})
		}).done(function (res) {
			var parsedRes = parseAjaxJsonResponse(res);
			if (isReceiptActionError(parsedRes)) {
				showReceiptNotify('error', parsedRes.message || 'Xóa liên kết phiếu thu thất bại');
				return;
			}

			$btn.closest('tr').remove();
			refreshReceiptVoucherTable();
			showReceiptNotify('success', 'Đã xóa liên kết phiếu thu');
		}).fail(function (xhr) {
			var failRes = parseAjaxJsonResponse(xhr && (xhr.responseJSON || xhr.responseText));
			if (failRes && failRes.message == 'Relationship not found') {
				$btn.closest('tr').remove();
				refreshReceiptVoucherTable();
				showReceiptNotify('success', 'Đã xóa liên kết phiếu thu');
				return;
			}

			var message = 'Xóa liên kết phiếu thu thất bại. Vui lòng thử lại';
			if (failRes && failRes.message) {
				message = failRes.message;
			}
			showReceiptNotify('error', message);
		}).always(function () {
			setReceiptPanelPending(false);
		});
	});
})

var receiptPanelState = {
	isPending: false,
};

function setReceiptVoucherReturn(resultData) {
	if (receiptPanelState.isPending) return;

	var popupData = getPopupFirstSelectedRow(resultData);
	var hoadonId = getCurrentHoaDonId();
	var receiptId = (popupData.id || '').trim();

	if (!isValidGuid(hoadonId)) {
		showReceiptNotify('warning', 'Vui lòng lưu hóa đơn trước khi liên kết phiếu thu');
		return;
	}

	if (!isValidGuid(receiptId)) {
		showReceiptNotify('warning', 'Phiếu thu được chọn không hợp lệ');
		return;
	}

	if (isReceiptVoucherLinked(receiptId)) {
		showReceiptNotify('warning', 'Phiếu thu này đã được liên kết với hóa đơn hiện tại');
		return;
	}

	setReceiptPanelPending(true);
	$.ajax({
		url: ENTRYPOINT,
		type: 'POST',
		contentType: 'application/json',
		data: JSON.stringify({
			class: ENTRYCLASS,
			method: 'saveHoaDonReceipt',
			params: {
				hoadon_id: hoadonId,
				receipt_id: receiptId
			}
		})
	}).done(function (res) {
		var parsedRes = parseAjaxJsonResponse(res);
		if (isReceiptActionError(parsedRes)) {
			showReceiptNotify('error', parsedRes.message || 'Liên kết phiếu thu thất bại');
			return;
		}

		fetchReceiptVoucherInfo(receiptId, function (info) {
			appendReceiptVoucherRow({
				id: receiptId,
				name: info.name || popupData.name || receiptId,
				amount: info.amount || popupData.amount || 0,
				amount_type: info.amount_type || popupData.amount_type || 'VND',
				ngaychungtu: info.ngaychungtu || popupData.ngaychungtu || '',
				rv_status_text: info.rv_status_text || popupData.rv_status_text || popupData.rv_status || '',
				assigned_user_name: info.assigned_user_name || popupData.assigned_user_name || ''
			});

			showReceiptNotify('success', 'Liên kết phiếu thu thành công');
		});
	}).fail(function (xhr) {
		var failRes = parseAjaxJsonResponse(xhr && (xhr.responseJSON || xhr.responseText));
		var message = 'Liên kết phiếu thu thất bại. Vui lòng thử lại';
		if (failRes && failRes.message) {
			message = failRes.message;
		}
		showReceiptNotify('error', message);
	}).always(function () {
		setReceiptPanelPending(false);
	});
}

function fetchReceiptVoucherInfo(receiptId, callback) {
	$.ajax({
		url: ENTRYPOINT,
		type: 'POST',
		contentType: 'application/json',
		data: JSON.stringify({
			class: ENTRYCLASS,
			method: 'getReceiptVoucherInfo',
			params: {
				receipt_id: receiptId
			}
		})
	}).done(function (res) {
		var parsedRes = parseAjaxJsonResponse(res);
		if (!isReceiptActionError(parsedRes) && parsedRes.data && typeof parsedRes.data === 'object') {
			callback(parsedRes.data);
			return;
		}

		callback({});
	}).fail(function () {
		callback({});
	});
}

function parseAjaxJsonResponse(rawResponse) {
	if (rawResponse == null) return {};
	if (typeof rawResponse === 'object') return rawResponse;

	try {
		return JSON.parse(rawResponse);
	} catch (e) {
		return {};
	}
}

function isReceiptActionError(res) {
	if (!res || typeof res !== 'object') return true;
	return (res.error === true || res.error === 1 || res.error === '1' || res.status === 0 || res.status === '0');
}


function getCurrentHoaDonId() {
	var recordId = '';

	// Prefer hidden input from current page form.
	recordId = (
		$('#DetailView input[name="record"]').val()
		|| $('form input[name="record"]').first().val()
		|| ''
	).trim();

	if (recordId) {
		return recordId;
	}

	// Fallback: read from URL query string in DetailView.
	try {
		var params = new URLSearchParams(window.location.search || '');
		return (params.get('record') || '').trim();
	} catch (e) {
		return '';
	}
}


function isValidGuid(id) {
	return typeof id === 'string' && id.length === 36;
}

function getPopupFirstSelectedRow(resultData) {
	var fallback = {
		id: '',
		name: '',
		amount: '',
		amount_type: '',
		ngaychungtu: '',
		rv_status: '',
		assigned_user_name: '',
	};

	if (!resultData || !resultData.name_to_value_array) {
		return fallback;
	}

	var source = resultData.name_to_value_array;
	var row = null;
	if (Array.isArray(source)) {
		row = source[0] || null;
	} else if (typeof source === 'object') {
		if (source[0]) {
			row = source[0];
		} else if (source.receipt_id || source.id) {
			// ✅ flat object chính là row - dùng trực tiếp
			row = source;
		} else {
			var keys = Object.keys(source);
			if (keys.length > 0) row = source[keys[0]];
		}
	}

	if (!row || typeof row !== 'object') {
		return fallback;
	}

	return {
		id: (row.receipt_id || row.id || '').trim(),
		name: row.receipt_name || row.name || '',
		amount: row.receipt_amount || row.amount || '',
		amount_type: row.receipt_amount_type || row.amount_type || '',
		ngaychungtu: row.receipt_ngaychungtu || row.ngaychungtu || '',
		rv_status: row.receipt_status || row.rv_status || '',
		rv_status_text: row.receipt_status_text || row.rv_status_text || row.receipt_status || row.rv_status || '',
		assigned_user_name: row.receipt_assigned_user_name || row.assigned_user_name || '',
	};
}

function isReceiptVoucherLinked(receiptId) {
	return $(`.btn-remove-receipt[data-id="${receiptId}"]`).length > 0;
}

function setReceiptPanelPending(isPending) {
	receiptPanelState.isPending = !!isPending;
	$('#btn-add-receipt, .btn-remove-receipt').prop('disabled', !!isPending);
}

function appendReceiptVoucherRow(row) {
	var $tbody = $('.panel-receipt-vouchers table tbody').first();
	if (!$tbody.length) return;

	markReceiptEmptyRow();
	$tbody.find('.receipt-empty-row').remove();

	var receiptId = (row.id || '').trim();
	var receiptName = escapeHtml(row.name || '');
	var amount = formatReceiptAmount(row.amount);
	var amountType = escapeHtml(row.amount_type || 'VND');
	var ngaychungtu = escapeHtml(row.ngaychungtu || '');
	var statusText = normalizeStatusText(row.rv_status_text || row.rv_status || '');
	var assignedUser = escapeHtml(row.assigned_user_name || '');

	var rowHtml = '';
	rowHtml += '<tr>';
	rowHtml += '<td class="text-center"></td>';
	rowHtml += '<td><a href="index.php?module=EC_Receipt_Voucher&action=DetailView&record=' + receiptId + '" target="_blank">' + receiptName + '</a></td>';
	rowHtml += '<td class="text-end">' + amount + '</td>';
	rowHtml += '<td>' + amountType + '</td>';
	rowHtml += '<td>' + ngaychungtu + '</td>';
	rowHtml += '<td>' + statusText + '</td>';
	rowHtml += '<td>' + assignedUser + '</td>';
	rowHtml += '<td class="text-center"><button type="button" class="btn-remove-receipt" data-id="' + receiptId + '">Xóa</button></td>';
	rowHtml += '</tr>';

	$tbody.append(rowHtml);
	refreshReceiptVoucherTable();
}

function refreshReceiptVoucherTable() {
	var $tbody = $('.panel-receipt-vouchers table tbody').first();
	if (!$tbody.length) return;

	markReceiptEmptyRow();

	var $rows = $tbody.children('tr').not('.receipt-empty-row');
	if (!$rows.length) {
		$tbody.html('<tr class="receipt-empty-row"><td colspan="8" class="text-center">Chưa có phiếu thu nào</td></tr>');
		return;
	}

	$rows.each(function (idx) {
		$(this).children('td').first().text(idx + 1);
	});
}

function markReceiptEmptyRow() {
	$('.panel-receipt-vouchers table tbody tr').each(function () {
		var $td = $(this).children('td[colspan="8"]');
		if ($td.length && $td.text().trim() == 'Chưa có phiếu thu nào') {
			$(this).addClass('receipt-empty-row');
		}
	});
}

function formatReceiptAmount(value) {
	var number = parseFloat((value || '0').toString().replace(/,/g, ''));
	if (isNaN(number)) return '0';
	return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function normalizeStatusText(value) {
	if (!value) return '';
	var text = $('<div>').html(value).text().trim();
	if (text) return escapeHtml(text);
	return escapeHtml(value);
}

function escapeHtml(value) {
	return $('<div>').text(value || '').html();
}

function showReceiptNotify(type, message) {
	if (typeof showModalNotify === 'function') {
		if (type == 'success') showModalNotify(1, message);
		else if (type == 'warning') showModalNotify(2, message);
		else showModalNotify(0, message);
		return;
	}

	if (typeof showToastWarning === 'function') {
		showToastWarning(message);
		return;
	}

	alert(message);
}

if (typeof window !== 'undefined') {
	window.setReceiptVoucherReturn = setReceiptVoucherReturn;
}

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