$(document).ready(function () {
	// Tu dong viet hoa ma khach hang
	$('#ticker_symbol').change(function () {
		var regex = /[^a-zA-Z0-9_]/g;
		$(this).val($(this).val().replace(regex, '').toUpperCase());
	});

	var is_phone_office_exist;
	var is_ticker_symbol_exist;
	var is_sic_code_exist;
	$('#EditView').submit(function () {
		var action = $('#EditView input:hidden[name="action"]').val();
		if (action == 'Save') {

			// Kiem tra loai khach hang
			var account_type = $('#account_type :selected').val();
			if (account_type.length == 0) {
				alert('Loại khách hàng không được trống');
				$('#account_type').focus();
				return false;
			}

			// Kiem tra ma khach hang khong duoc trong
			if ($('#ticker_symbol').val().length == 0) {
				alert('Mã khách hàng không được trống.');
				$('#ticker_symbol').focus();
				$('#ticker_symbol').select();
				return false;
			}

			// Kiem tra ma khach hang co bi trung
			$.ajax({
				cache: false,
				type: 'post',
				async: false, // Set to false so order of operations is correct
				data: 'module=Accounts&field=ticker_symbol&field_value=' + $('#ticker_symbol').val() + '&record=' + $('#EditView input:hidden[name="record"]').val(),
				url: 'index.php?module=Accounts&entryPoint=entryPointCheckValueExist&action=EditView',
				success: function (data) {
					if (data == '1') {
						is_ticker_symbol_exist = true;
					}
					else {
						is_ticker_symbol_exist = false;
					}
				}
			});

			if (is_ticker_symbol_exist) {
				alert('Mã khách hàng <' + $('#ticker_symbol').val() + '> đã bị trùng trong danh sách nhập. Vui lòng kiểm tra lại.');
				$('#ticker_symbol').focus();
				$('#ticker_symbol').select();
				return false;
			}

			// Kiem tra so dien thoai co bi trung
			$.ajax({
				cache: false,
				type: 'post',
				async: false, // set to false so order of operations is correct
				data: 'module=Accounts&field=phone_office&field_value=' + $('#phone_office').val() + '&record=' + $('#EditView input:hidden[name="record"]').val(),
				url: 'index.php?module=Accounts&entryPoint=entryPointCheckValueExist&action=EditView',
				success: function (data) {
					if (data == '1') {
						is_phone_office_exist = true;
					}
					else {
						is_phone_office_exist = false;
					}
				}
			});

			if (is_phone_office_exist) {
				alert('Số điện thoại <' + $('#phone_office').val() + '> đã bị trùng trong danh sách nhập. Vui lòng kiểm tra lại.');
				$('#phone_office').focus();
				$('#phone_office').select();
				return false;
			}

			// Kiem tra ma so thue co bi trung
			$.ajax({
				cache: false,
				type: 'post',
				async: false, // set to false so order of operations is correct
				data: 'module=Accounts&field=sic_code&field_value=' + $('#sic_code').val() + '&record=' + $('#EditView input:hidden[name="record"]').val(),
				url: 'index.php?module=Accounts&entryPoint=entryPointCheckValueExist&action=EditView',
				success: function (data) {
					if (data == '1') {
						is_sic_code_exist = true;
					}
					else {
						is_sic_code_exist = false;
					}
				}
			});

			if (is_sic_code_exist) {
				alert('Mã số thuế <' + $('#sic_code').val() + '> đã bị trùng trong danh sách nhập. Vui lòng kiểm tra lại.');
				$('#sic_code').focus();
				$('#sic_code').select();
				return false;
			}
		}
	});
});
