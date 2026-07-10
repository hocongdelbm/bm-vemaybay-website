
$(document).ready(function () {

	var dec_seperator = $('#dec_seperator').val();
	var grp_seperator = $('#grp_seperator').val();
	var sig_digits = $('#sig_digits').val();

	$('#amount, #exchange_rate, #bought_amount, .allow-number-only').number(true, sig_digits, dec_seperator, grp_seperator);

	$('#employee-select').select2();

	$('#amount_converted').css('background', '#f8f8f8');
	$('#amount_converted').attr('readonly', true);

	if (amount_type == 'VND') {
		$('#exchange_rate').css('background', '#f8f8f8');
		$('#exchange_rate').attr('readonly', true);
	}

	// loại thu — trạng thái ban đầu do PHP render (display:none để chống nháy),
	// JS lo thay đổi động. trigger('change') bên dưới tự set hiện/ẩn theo loại thu
	// nên không cần clear span_supplier thủ công ở đây.
	var html_customer = $('#span_customer').html();
	var html_supplier = $('#span_supplier').html();

	$('#loai_thu').change(function () {
		let loai_thu = $('#loai_thu :selected').val();
		if (loai_thu_arr.includes(loai_thu)) {
			$('#span_supplier').html(html_supplier).show();
			$('.allow-number-only').number(true, sig_digits, dec_seperator, grp_seperator);
		} else {
			$('#span_customer').html(html_customer).show();
			$('#span_supplier').html('').hide();
		}

		if (loai_thu == 9) {
			$("#span_customer").hide();
		} else {
			$("#span_customer").show();
		}
	});
	$('#loai_thu').trigger('change');

	// auto complete
	$("#customer").on("keydown.autocomplete", function () {
		var tbl = $(this).attr('tbl');
		var fld = $(this).attr('fld');
		$(this).autocomplete({
			source: "ac_all.php?tbl=" + tbl + "&fld=" + fld,
			minLength: 2,
			select: function (event, ui) {
				var obj = $.parseJSON(fld);
				for (var prop in obj) {
					$('#' + obj[prop]).val(ui.item[prop]);
				}
			}
		});
	});

	$(document).on('click', '#btnSelectAccount', function () {
		openAccountPopup();
	});

	$('#btnClearAccount').on('click', function () {
		$('#customer, #account_id_c').val('');
	});

	// xử lý sự kiện submit
	$('#EditView').submit(function () {
		var action = $('#EditView input:hidden[name="action"]').val();
		if (action != 'Save') {
			return;
		}

		var amount = unformatNumber($('#amount').val());
		var receipt_type = $('#receipt_type :selected').val();
		var loai_thu = $('#loai_thu :selected').val();
		var isSupplier = loai_thu_arr.includes(loai_thu);
		var tknganhang_id = $('#tknganhang_id :selected').val();
		var com_location = $('#com_location_id :selected').val();
		var booking_id = $('#booking_id').val();
		var delivery_man = $('#delivery_man_id').val();
		var supplier_id = isSupplier ? $('#supplier_id :selected').val() : $('#account_id_c').val();
		var supplier2_id = $('#supplier2_id :selected').val();
		var supplier3_id = $('#supplier3_id :selected').val();
		var sell_amount = unformatNumber($('#sell_amount').val());
		var sell_amount2 = unformatNumber($('#sell_amount2').val());
		var sell_amount3 = unformatNumber($('#sell_amount3').val());
		var bought_amount2 = unformatNumber($('#bought_amount2').val());
		var bought_amount3 = unformatNumber($('#bought_amount3').val());
		var total_sell = parseInt(sell_amount + sell_amount2 + sell_amount3);

		// Số tiền
		if (amount == '' && loai_thu != '4' && loai_thu != '5' && loai_thu != '27') {
			showToastWarning('Số tiền không được trống!');
			$('#amount').focus();
			return false;
		}

		// Tài khoản ngân hàng
		if (receipt_type == 'credit_transfer' && tknganhang_id == '') {
			showToastWarning('Tài khoản ngân hàng không được trống!');
			$('#tknganhang_id').focus();
			return false;
		}

		// Địa điểm
		if (receipt_type == 'cash' && com_location == '' && loai_thu != 22) {
			showToastWarning('Địa điểm không được trống!');
			$('#com_location_id').focus();
			return false;
		}

		// Người giao chỉ hợp lệ với loại thu 21
		if (delivery_man.length > 0 && loai_thu != '21') {
			showToastWarning('Loại thu và người giao chưa phù hợp!');
			$('#delivery_man_id').focus();
			return false;
		}

		// Loại thu "Thu tiền vé" thì phải chèn booking
		if ((loai_thu == '1' || loai_thu == '4' || loai_thu == '5') && booking_id == '') {
			showToastWarning('Booking không được để trống!');
			$('#booking_name').focus();
			return false;
		}

		// Nhà cung cấp / Đối tượng
		if ((isSupplier && supplier_id == '') || (loai_thu == '7' && supplier_id.length == 0)) {
			if (isSupplier) {
				showToastWarning('Nhà cung cấp không được trống!');
				$('#account_id_c').focus();
			} else {
				showToastWarning('Đối tượng không được trống!');
				$('#customer').focus();
			}
			return false;
		}

		if (bought_amount2 > 0 && supplier2_id == '') {
			showToastWarning('Nhà cung cấp không được trống!');
			$('#supplier2_id').focus();
			return false;
		}
		if (bought_amount3 > 0 && supplier3_id == '') {
			showToastWarning('Nhà cung cấp không được trống!');
			$('#supplier3_id').focus();
			return false;
		}

		// Giá bán
		if (isSupplier && sell_amount == '' && loai_thu != '4' && loai_thu != '5' && loai_thu != '27') {
			showToastWarning('Giá bán không được trống!');
			$('#bought_amount').focus();
			return false;
		}
		if (supplier2_id != '' && sell_amount2 == '' && loai_thu != '4' && loai_thu != '5' && loai_thu != '27') {
			showToastWarning('Giá bán không được trống!');
			$('#sell_amount2').focus();
			return false;
		}
		if (supplier3_id != '' && sell_amount3 == '' && loai_thu != '4' && loai_thu != '5' && loai_thu != '27') {
			showToastWarning('Giá bán không được trống!');
			$('#sell_amount3').focus();
			return false;
		}

		// Giá mua
		if (isSupplier) {
			const suppliers = [
				{ id: supplier_id, amount: String($('#bought_amount').val() || ''), selector: '#bought_amount' },
				{ id: supplier2_id, amount: String($('#bought_amount2').val() || ''), selector: '#bought_amount2' },
				{ id: supplier3_id, amount: String($('#bought_amount3').val() || ''), selector: '#bought_amount3' }
			];
			for (const sup of suppliers) {
				if (sup.id !== '' && sup.amount.trim() === '') {
					showToastWarning('Giá mua không được trống!');
					$(sup.selector).focus();
					return false;
				}
			}
		}

		// Số tiền phải bằng tổng giá bán
		if (isSupplier && amount != total_sell) {
			showToastWarning('Số tiền và tổng giá bán phải bằng nhau!');
			$('#amount').focus();
			return false;
		}
	});

	// khi thay đổi số tiền
	$('#amount, #exchange_rate').blur(function () {
		var amount_type = $('#amount_type :selected').val();
		var amount = unformatNumber($('#amount').val());
		var exchange_rate = unformatNumber($('#exchange_rate').val());
		var amount_converted = amount * exchange_rate;
		if (amount_type != 'VND' && exchange_rate != '' && exchange_rate > 0) {
			$('#amount_converted').val(formatNumber(amount_converted));
		} else {
			$('#amount_converted').val(formatNumber(amount));
		}
	});

	// khi thay đổi hình thức thanh toán
	$('#tknganhang_id').select2();

	if ($("#receipt_type").val() == 'credit_transfer') {
		$('#tknganhang_id').next().show();
		$('#com_location_id').val('');
		$('#com_location_id').hide();

	} else if ($("#receipt_type").val() == 'cash') {
		$('#com_location_id').show();
		$('#tknganhang_id').val('');
		$('#tknganhang_id').next().hide();
		$('#tk_ketoan').val('');
	}

	$('#receipt_type').change(function () {
		if ($(this).val() == 'credit_transfer') {
			$('#tknganhang_id').next().show();
			$('#com_location_id').val('');
			$('#com_location_id').hide();

		} else if ($(this).val() == 'cash') {
			$('#com_location_id').show();
			$('#tknganhang_id').val('');
			$('#tknganhang_id').next().hide();
			$('#tk_ketoan').val('');

		}
	});

	// khi thay đổi tài khoản ngân hàng
	$('#tknganhang_id').change(function () {
		if ($(this).val() != '') {
			var tk = $('#tknganhang_id :selected').attr('tk');
			$('#tk_ketoan').val(tk);
		} else {
			$('#tk_ketoan').val('');
		}
	});

	function openAccountPopup() {
		var popupRequestData = {
			"call_back_function": "setObjectReturn",
			"form_name": "EditView",
			"field_to_name_array": {
				"id": "account_id_c",
				"name": "customer"
			}
		};
		open_popup('Accounts', 650, 600, '&account_type_advanced=Customer&is_stop_tracking_advanced=0', true, false, popupRequestData);
	}
});
