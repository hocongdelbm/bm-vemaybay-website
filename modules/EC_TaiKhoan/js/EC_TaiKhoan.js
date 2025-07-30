$(document).ready(function () {
	if (!$('#chitiettheo').is(':checked')) {
		$('#LBL_CHITIETTHEO_PANEL input[type=checkbox]').each(function () {
			$(this).attr('disabled', true);
			$(this).attr('readonly', true);
			$(this).attr('checked', false);
			$(this).val(0);
		});
	}
	else {
		$('#LBL_CHITIETTHEO_PANEL input[type=checkbox]').each(function () {
			if ($(this).attr('id') == 'doituong' && $(this).is(':checked')) {
				$('#vthh_ccdc').attr('disabled', true);
				$('#vthh_ccdc').attr('readonly', true);
			} else if ($(this).attr('id') == 'vthh_ccdc' && $(this).is(':checked')) {
				$('#doituong').attr('disabled', true);
				$('#doituong').attr('readonly', true);
			} else {
				$(this).attr('disabled', false);
				$(this).attr('readonly', false);
			}
		});

	}

	$('#chitiettheo').change(function () {
		if (!$('#chitiettheo').is(':checked')) {
			$('#LBL_CHITIETTHEO_PANEL input[type=checkbox]').each(function () {
				$(this).attr('disabled', true);
				$(this).attr('readonly', true);
				$(this).attr('checked', false);
				$(this).val(0);
			});
			$('#loaidoituong').hide();
		} 
		else {
			$('#LBL_CHITIETTHEO_PANEL input[type=checkbox]').each(function () {
				$(this).attr('disabled', false);
				$(this).attr('readonly', false);
			});
		}
	});

	$('#LBL_CHITIETTHEO_PANEL input[type=checkbox]').each(function () {
		$(this).bind(
			'change',
			function () {
				if ($(this).is(':checked') && $(this).attr('id') != 'doituong' && $(this).attr('id') != 'vthh_ccdc') {
					$(this).val(1);
				} else if (!$(this).is(':checked') && $(this).attr('id') != 'doituong' && $(this).attr('id') != 'vthh_ccdc') {
					$(this).val(0);
				} else if ($(this).is(':checked') && $(this).attr('id') == 'doituong') {
					$(this).val(1);
					$('#loaidoituong').show();
					$('#vthh_ccdc').val(0);
					$('#vthh_ccdc').attr('disabled', true);
					$('#vthh_ccdc').attr('readonly', true);
				} else if (!$(this).is(':checked') && $(this).attr('id') == 'doituong') {
					$(this).val(0);
					$('#loaidoituong').hide();
					$('#vthh_ccdc').val(0);
					$('#vthh_ccdc').attr('disabled', false);
					$('#vthh_ccdc').attr('readonly', false);
				} else if ($(this).is(':checked') && $(this).attr('id') == 'vthh_ccdc') {
					$(this).val(1);
					$('#loaidoituong').hide();
					$('#doituong').val(0);
					$('#doituong').attr('disabled', true);
					$('#doituong').attr('readonly', true);
				} else if (!$(this).is(':checked') && $(this).attr('id') == 'vthh_ccdc') {
					$(this).val(0);
					$('#doituong').val(0);
					$('#doituong').attr('disabled', false);
					$('#doituong').attr('readonly', false);
				}
			}
		);
	});

	// Không cho nhấn phím enter trên form
	$("form").bind("keypress", function (e) {
		if (e.keyCode == 13) return false;
	});

	// Xử lý sự kiện save
	$('#btnSave').click(function () {
		if (check_form('EditView')) {
			$.ajax({
				cache: false,
				type: 'GET',
				data: 'ma=' + $('#sotaikhoan').val() + '&id=' + $('#EditView input[name=record]').val() + '&tbl=ec_taikhoan&fld=sotaikhoan',
				url: 'index.php?module=EC_TaiKhoan&entryPoint=checkexist&action=EditView',
				success: function (output) {
					$('#EditView').append(output);
					if (is_exist == 0) {
						$('#EditView input[name=action]').val('Save');
						$('#EditView').submit();
					}
				}
			});
		}
	});

	// Tài khoản tổng hợp
	$('#taikhoantonghop').change(function () {
		var cap = parseInt($('#taikhoantonghop :selected').attr('cap'));
		$('#cap').val(cap + 1);
	});
});
