
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

	// loại thu
	var html_customer = $('#span_customer').html();
	var html_supplier = $('#span_supplier').html();
	var loai_thu = $('#loai_thu :selected').val();
	var loai_thu_arr = ['4', '5', '11', '12', '13', '14', '16', '27'];

	if (!loai_thu_arr.includes(loai_thu)) {
		$('#span_supplier').html('');
	}

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
	$('#EditView').submit(function (e) {
		var action = $('#EditView input:hidden[name="action"]').val();
		var amount = unformatNumber($('#amount').val());
		var receipt_type = $('#receipt_type :selected').val();
		var loai_thu = $('#loai_thu :selected').val();
		var tknganhang_id = $('#tknganhang_id :selected').val();
		var com_location = $('#com_location_id :selected').val();
		var booking_id = $('#booking_id').val();
		var delivery_man = $('#delivery_man_id').val();
		var supplier_id = loai_thu_arr.includes(loai_thu) ? $('#supplier_id :selected').val() : $('#account_id_c').val();
		var supplier2_id = $('#supplier2_id :selected').val();
		var supplier3_id = $('#supplier3_id :selected').val();
		var sell_amount = unformatNumber($('#sell_amount').val());
		var sell_amount2 = unformatNumber($('#sell_amount2').val());
		var sell_amount3 = unformatNumber($('#sell_amount3').val());
		var bought_amount = unformatNumber($('#bought_amount').val());
		var bought_amount2 = unformatNumber($('#bought_amount2').val());
		var bought_amount3 = unformatNumber($('#bought_amount3').val());
		var total_sell = parseInt(sell_amount + sell_amount2 + sell_amount3);

		if (action == 'Save') {
			if (amount == '' && loai_thu != '4' && loai_thu != '5' && loai_thu != '27') {
				$text_warning = 'Số tiền không được trống!';
				showToastWarning($text_warning);
				$('#amount').focus();
				return false;
			}

			if (receipt_type == 'credit_transfer' && tknganhang_id == '') {

				$('.toast-warning').addClass('active');
				$('.toast-warning #toast-content').text('Tài khoản ngân hàng không được trống!');
				$('.toast-warning .progress-bar').animate({ width: "100%" }, 3000);
				setTimeout(function () {
					$(".toast-warning").removeClass('active');
				}, 4000);

				$('#tknganhang_id').focus();
				return false;
			}

			if (receipt_type == 'cash' && com_location == '' && loai_thu != 22) {
				$('.toast-warning').addClass('active');
				$('.toast-warning #toast-content').text('Địa điểm không được trống!');
				$('.toast-warning .progress-bar').animate({ width: "100%" }, 3000);

				setTimeout(function () {
					$(".toast-warning").removeClass('active');
				}, 4000);

				$('#com_location_id').focus();
				return false;
			}

			if (delivery_man.length > 0 && loai_thu != '21') {
				$('.toast-warning').addClass('active');
				$('.toast-warning #toast-content').text('Loại thu và người giao chưa phù hợp!');
				$('.toast-warning .progress-bar').animate({ width: "100%" }, 3000);
				setTimeout(function () {
					$(".toast-warning").removeClass('active');
				}, 4000);

				$('#delivery_man_id').focus();
				return false;
			}

			// Loại thu "Thu tiền vé" thì phải chèn booking
			if ((loai_thu == '1' || loai_thu == '4' || loai_thu == '5') && booking_id == '') {
				$text_warning = 'Booking không được để trống!';
				showToastWarning($text_warning);
				$('#booking_name').focus();
				return false;
			}

			// Check supplier
			if ((loai_thu_arr.includes(loai_thu) && supplier_id == '') || (loai_thu == '7' && supplier_id.length == 0)) {
				if (loai_thu_arr.includes(loai_thu)) {
					$text_warning = 'Nhà cung cấp không được trống!';
					showToastWarning($text_warning);
					$('#account_id_c').focus();
				} else {
					$text_warning = 'Đối tượng không được trống!';
					showToastWarning($text_warning);
					$('#customer').focus();
				}
				return false;
			}

			if (bought_amount2 > 0 && supplier2_id == '') {
				$text_warning = 'Nhà cung cấp không được trống!';
				showToastWarning($text_warning);
				$('#supplier2_id').focus();
				return false;
			}
			if (bought_amount3 > 0 && supplier3_id == '') {
				$text_warning = 'Nhà cung cấp không được trống!';
				showToastWarning($text_warning);
				$('#supplier3_id').focus();
				return false;
			}

			// Sell amount
			if (loai_thu_arr.includes(loai_thu) && sell_amount == '' && loai_thu != '4' && loai_thu != '5' && loai_thu != '27') {
				$text_warning = 'Giá bán không được trống!';
				showToastWarning($text_warning);
				$('#bought_amount').focus();
				return false;
			}
			if (supplier2_id != '' && sell_amount2 == '' && loai_thu != '4' && loai_thu != '5' && loai_thu != '27') {
				$text_warning = 'Giá bán không được trống!';
				showToastWarning($text_warning);
				$('#sell_amount2').focus();
				return false;
			}
			if (supplier3_id != '' && sell_amount3 == '' && loai_thu != '4' && loai_thu != '5' && loai_thu != '27') {
				$text_warning = 'Giá bán không được trống!';
				showToastWarning($text_warning);
				$('#sell_amount3').focus();
				return false;
			}

			// Bought amount
			const suppliers = [
				{ id: '', amount: String($('#bought_amount').val() || ''), selector: '#bought_amount', alwaysCheck: true },
				{ id: supplier2_id, amount: String($('#bought_amount2').val() || ''), selector: '#bought_amount2' },
				{ id: supplier3_id, amount: String($('#bought_amount3').val() || ''), selector: '#bought_amount3' }
			];
			for (const sup of suppliers) {
				const shouldCheck = sup.alwaysCheck || (sup.id !== '' && loai_thu !== '4' && loai_thu !== '5');
				if (shouldCheck && sup.amount.trim() === '') {
					showToastWarning('Giá mua không được trống!');
					$(sup.selector).focus();
					return false;
				}
			}

			// Check total sell and amount
			if (['4', '5', '14', '27'].includes(loai_thu) && amount != total_sell) {
				$text_warning = 'Số tiền và tổng giá bán phải bằng nhau!';
				showToastWarning($text_warning);
				$('#amount').focus();
				return false;
			}
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