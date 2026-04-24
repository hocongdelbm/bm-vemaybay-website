const ENTRYPOINT = "index.php?entryPoint=entryPointGeneral";
const ENTRYCLASS = "entryOutputInvoiceClass";

$(document).ready(function () {

	// === CUSTOM AUTOCOMPLETE WIDGET ===
	$.widget('custom.autocomplete', $.ui.autocomplete, {
		options: {
			open: function (event, ui) {
				$('.ui-autocomplete .ui-menu-item:first').trigger('mouseover');
			},
			focus: function (event, ui) {
				event.preventDefault();
			}
		},
		_create: function () {
			this._super();
			this.widget().menu("option", "items", ".ui-menu-item");
		},
		_renderMenu: function (ul, items) {
			var self = this;
			var $table = $('<table class="table-autocomplete table-autocomplete__hoadonban table-details__booking">'),
				$thead = $('<thead>'),
				$headerRow = $('<tr>'),
				$tbody = $('<tbody>');
			$.each(self.options.columns, function (index, columnMapping) {
				$('<th class="text-center" style="width:' + columnMapping.width + ';">').html(columnMapping.name).appendTo($headerRow);
			});
			$thead.append($headerRow);
			$table.append($thead);
			$table.append($tbody);
			ul.html($table);
			$.each(items, function (index, item) {
				self._renderItemData(ul, ul.find("table tbody"), item);
			});
		},
		_renderItemData: function (ul, table, item) {
			return this._renderItem(table, item).data("ui-autocomplete-item", item);
		},
		_renderItem: function (table, item) {
			var self = this;
			var $tr = $('<tr class="ui-menu-item" role="presentation">');
			$.each(self.options.columns, function (index, columnMapping) {
				var cellContent = !item[columnMapping.valueField] ? '' : item[columnMapping.valueField];
				if (typeof columnMapping.formatter === 'function') {
					cellContent = columnMapping.formatter(cellContent, item);
				}
				$('<td class="text-center">').html(cellContent).appendTo($tr);
			});
			return $tr.appendTo(table);
		}
	});

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

	$(document).on('focus.receiptAutocomplete', '#ac_receipt_search', function () {
		initReceiptSearchAutocomplete($(this));
	});

	$(document).on('paste.receiptAutocomplete', '#ac_receipt_search', function () {
		var $input = $(this);
		// paste event fires TRƯỚC khi value được cập nhật, nên cần delay
		setTimeout(function () {
			initReceiptSearchAutocomplete($input);
			var keyword = ($input.val() || '').trim();
			console.log('[receipt search] paste keyword:', keyword); // debug
			if (keyword.length >= 2) {
				$input.autocomplete('search', keyword);
			}
		}, 100);
	});

	function getReceiptAutocompleteColumns() {
		return [
			{ name: 'Tên phiếu thu', width: '160px', valueField: 'name' },
			{ name: 'Số tiền', width: '90px', valueField: 'amount', formatter: formatReceiptAmount },
			{ name: 'Loại thu', width: '50px', valueField: 'loai_thu_text', formatter: formatReceiptLabelText },
			{ name: 'Ngày chứng từ', width: '100px', valueField: 'ngaychungtu', formatter: formatReceiptDateByUserFormat },
			{ name: 'Trạng thái', width: '90px', valueField: 'rv_status_text', formatter: formatReceiptStatusCell },
			{ name: 'Nội dung thu', width: '100px', valueField: 'description' },
		];
	}

	function getReceiptSearchScore(item, keyword) {
		var name = ((item && item.name) || '').toString().toLowerCase();
		if (!keyword) return 3;

		if (name === keyword) return 0;
		if (name.indexOf(keyword) === 0) return 1;
		if (name.indexOf(keyword) > -1) return 2;
		return 3;
	}

	function initReceiptSearchAutocomplete($input) {
		if (!$input || !$input.length || $input.data('receipt-autocomplete-ready')) return;
		if (typeof $input.autocomplete !== 'function') {
			console.warn('jQuery UI autocomplete is not available on this page');
			return;
		}
		console.log('[receipt search] initializing autocomplete'); // debug

		$input.autocomplete({
			showHeader: true,
			columns: getReceiptAutocompleteColumns(),
			source: function (request, response) {
				console.log('[receipt search] source called, term:', request.term); // debug
				$.ajax({
					url: ENTRYPOINT,
					type: 'POST',
					contentType: 'application/json',
					data: JSON.stringify({
						class: ENTRYCLASS,
						method: 'searchReceiptVouchers',
						params: { term: request.term }
					}),
					success: function (res) {
						console.log('[receipt search] raw response:', res); // debug
						var parsed = parseAjaxJsonResponse(res);
						console.log('[receipt search] parsed:', parsed); // debug
						if (!parsed.error && Array.isArray(parsed.data)) {
							var keyword = (request.term || '').toString().trim().toLowerCase();
							var items = parsed.data.sort(function (a, b) {
								var scoreA = getReceiptSearchScore(a, keyword);
								var scoreB = getReceiptSearchScore(b, keyword);

								if (scoreA !== scoreB) return scoreA - scoreB;

								var nameA = ((a && a.name) || '').toString();
								var nameB = ((b && b.name) || '').toString();
								return nameA.localeCompare(nameB);
							});
							console.log('[receipt search] items:', items); // debug
							response(items);
						} else {
							response([]);
						}
					},
					error: function (xhr) {
						console.error('[receipt search] AJAX error:', xhr.responseText);
						response([]);
					}
				});
			},
			minLength: 2,
			select: function (event, ui) {
				event.preventDefault();
				$input.val(''); // xóa input sau khi chọn

				var receiptId = (ui.item.id || '').trim();
				var hoadonId = getCurrentHoaDonId();

				if (!isValidGuid(hoadonId)) {
					showReceiptNotify('warning', 'Vui lòng lưu hóa đơn trước khi liên kết phiếu thu');
					return false;
				}
				if (!isValidGuid(receiptId)) {
					showReceiptNotify('warning', 'Phiếu thu được chọn không hợp lệ');
					return false;
				}
				if (isReceiptVoucherLinked(receiptId)) {
					showReceiptNotify('warning', 'Phiếu thu này đã được liên kết với hóa đơn hiện tại');
					return false;
				}

				setReceiptPanelPending(true);
				$.ajax({
					url: ENTRYPOINT,
					type: 'POST',
					contentType: 'application/json',
					data: JSON.stringify({
						class: ENTRYCLASS,
						method: 'saveHoaDonReceipt',
						params: { hoadon_id: hoadonId, receipt_id: receiptId }
					})
				}).done(function (res) {
					var parsed = parseAjaxJsonResponse(res);
					if (isReceiptActionError(parsed)) {
						showReceiptNotify('error', parsed.message || 'Liên kết phiếu thu thất bại');
						return;
					}
					appendReceiptVoucherRow({
						id: receiptId,
						name: ui.item.name,
						amount: ui.item.amount,
						loai_thu: ui.item.loai_thu,
						loai_thu_text: ui.item.loai_thu_text,
						ngaychungtu: ui.item.ngaychungtu,
						rv_status: ui.item.rv_status,
						rv_status_text: ui.item.rv_status_text,
						rv_status_color: ui.item.rv_status_color,
						assigned_user_name: ui.item.assigned_user_name,
						description: ui.item.description || ''
					});
					showReceiptNotify('success', 'Liên kết phiếu thu thành công');
				}).fail(function (xhr) {
					var failRes = parseAjaxJsonResponse(xhr && (xhr.responseJSON || xhr.responseText));
					showReceiptNotify('error', (failRes && failRes.message) || 'Liên kết thất bại');
				}).always(function () {
					setReceiptPanelPending(false);
				});

				return false;
			}
		});

		$input.data('receipt-autocomplete-ready', true);
		console.log('[receipt search] autocomplete ready'); // debug
	}

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
				loai_thu: info.loai_thu || popupData.loai_thu || '',
				loai_thu_text: info.loai_thu_text || popupData.loai_thu_text || popupData.loai_thu || '',
				ngaychungtu: info.ngaychungtu || popupData.ngaychungtu || '',
				rv_status: info.rv_status || popupData.rv_status || '',
				rv_status_text: info.rv_status_text || popupData.rv_status_text || popupData.rv_status || '',
				rv_status_color: info.rv_status_color || popupData.rv_status_color || '',
				assigned_user_name: info.assigned_user_name || popupData.assigned_user_name || '',
				description: info.description || popupData.description || ''
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
		loai_thu: '',
		loai_thu_text: '',
		ngaychungtu: '',
		rv_status: '',
		rv_status_color: '',
		assigned_user_name: '',
		description: '',
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
		loai_thu: row.receipt_loai_thu || row.loai_thu || '',
		loai_thu_text: row.receipt_loai_thu_text || row.loai_thu_text || row.receipt_loai_thu || row.loai_thu || '',
		ngaychungtu: row.receipt_ngaychungtu || row.ngaychungtu || '',
		rv_status: row.receipt_status || row.rv_status || '',
		rv_status_text: row.receipt_status_text || row.rv_status_text || row.receipt_status || row.rv_status || '',
		rv_status_color: row.receipt_status_color || row.rv_status_color || '',
		assigned_user_name: row.receipt_assigned_user_name || row.assigned_user_name || '',
		description: row.receipt_description || row.description || '',
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
	var loai_thu = escapeHtml(row.loai_thu_text || row.loai_thu || '');
	var ngaychungtu = formatReceiptDateByUserFormat(row.ngaychungtu || '');
	var statusText = buildReceiptStatusHtml(row.rv_status_text || row.rv_status || '', row.rv_status_color || '');
	var assignedUser = escapeHtml(row.assigned_user_name || '');
	var description = escapeHtml(row.description || '');

	var rowHtml = '';
	rowHtml += '<tr>';
	rowHtml += '<td class="text-center"></td>';
	rowHtml += '<td><a href="index.php?module=EC_Receipt_Voucher&action=DetailView&record=' + receiptId + '" target="_blank">' + receiptName + '</a></td>';
	rowHtml += '<td class="text-end">' + amount + '</td>';
	rowHtml += '<td>' + loai_thu + '</td>';
	rowHtml += '<td>' + ngaychungtu + '</td>';
	rowHtml += '<td>' + statusText + '</td>';
	rowHtml += '<td>' + assignedUser + '</td>';
	rowHtml += '<td>' + description + '</td>';
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
		$tbody.html('<tr class="receipt-empty-row"><td colspan="9" class="text-center">Chưa có phiếu thu nào</td></tr>');
		return;
	}

	$rows.each(function (idx) {
		$(this).children('td').first().text(idx + 1);
	});
}

function markReceiptEmptyRow() {
	$('.panel-receipt-vouchers table tbody tr').each(function () {
		var $td = $(this).children('td[colspan="8"], td[colspan="9"]');
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

function formatReceiptLabelText(value) {
	return escapeHtml(value || '');
}

function formatReceiptDateByUserFormat(value) {
	var raw = (value || '').toString().trim();
	if (!raw) return '';

	var dateParts = raw.match(/^(\d{4})-(\d{1,2})-(\d{1,2})(?:\s|T|$)/);
	if (!dateParts) {
		return escapeHtml(raw);
	}

	var year = dateParts[1];
	var month = ('0' + dateParts[2]).slice(-2);
	var day = ('0' + dateParts[3]).slice(-2);
	var userFormat = (typeof cal_date_format !== 'undefined' && cal_date_format) ? cal_date_format : '%d-%m-%Y';
	var displayDate = String(userFormat)
		.replace('%Y', year)
		.replace('%m', month)
		.replace('%d', day);

	return escapeHtml(displayDate);
}

function formatReceiptStatusCell(value, item) {
	return buildReceiptStatusHtml(value, item && item.rv_status_color ? item.rv_status_color : '');
}

function buildReceiptStatusHtml(value, colorValue) {
	var text = escapeHtml(value || '');
	if (!text) return '';

	var color = sanitizeCssColor(colorValue || '');
	if (!color) return text;

	return '<span class="fw-bold" style="color:' + color + ';">' + text + '</span>';
}

function sanitizeCssColor(value) {
	var color = (value || '').toString().trim();
	if (!color) return '';

	if (/^#[0-9a-fA-F]{3,8}$/.test(color)) return color;
	if (/^(rgb|rgba|hsl|hsla)\([0-9.,%\s-]+\)$/.test(color)) return color;
	if (/^[a-zA-Z]{3,20}$/.test(color)) return color;

	return '';
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