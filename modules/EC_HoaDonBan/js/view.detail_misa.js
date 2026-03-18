const ENTRYPOINT = "index.php?entryPoint=entryPointGeneral";
const ENTRYCLASS = "entryOutputInvoiceClass";

$(document).ready(function () {
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
		var itemNames = $("input[name='misa_itemName[]']").map(function () { return $(this).val(); }).get();
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
			account_object_code: buyerTax || '',
			account_object_name: buyerName || buyerCompany,
			account_object_address: buyerAddress,
			refdate: refdate,
			posted_date: refdate
		};

		let details = [];
		let itemCodes = $("input[name='misa_itemCode[]']").map(function () { return $(this).val(); }).get();
		let itemUnits = $("input[name='misa_itemUnit[]']").map(function () { return $(this).val(); }).get();
		let itemQtys = $("input[name='misa_itemQuantity[]']").map(function () { return $(this).val(); }).get();
		let itemPrices = $("input[name='misa_itemPrice[]']").map(function () { return $(this).val(); }).get();
		let itemVatRates = $("input[name='misa_itemVatRate[]']").map(function () { return $(this).val(); }).get();
		let itemVatAmnts = $("input[name='misa_itemVatAmnt[]']").map(function () { return $(this).val(); }).get();
		let itemAmounts = $("input[name='misa_itemAmountNoVat[]']").map(function () { return $(this).val(); }).get();

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
				amount: amount.toFixed(4),
				vat_rate: vatRate.toFixed(1),
				vat_amount: vatAmnt.toFixed(4),
				account_object_code: buyerTax || '',
				account_object_name: buyerCompany || buyerName,
			});
		}

		const saInvoice = {
			account_object_name: buyerCompany || buyerName,
			account_object_tax_code: buyerTax || '',
			inv_series: invSerial,
			payment_method: paymentMethod || 'TM/CK',
			total_sale_amount: invSubTotal,
			total_vat_amount: invVatAmount,
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
})
