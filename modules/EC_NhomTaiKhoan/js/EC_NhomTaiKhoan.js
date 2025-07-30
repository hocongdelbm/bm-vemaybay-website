$(document).ready(function () {
	// Checkbox chi tiết theo
	$("#chitiettheo").click(function () {
		if ($(this).is(':checked')) {
			$(this).val('1');
			$("#ds_chitiettheo").val('0');
			$("#ds_chitiettheo").show();
			$("#ds_chitiettheo").attr('disabled', false);
			$("#loaidoituong").val('0');
			$("#loaidoituong").show();
			$("#loaidoituong").attr('disabled', false);
		} 
		else {
			$(this).val('0');
			$("#ds_chitiettheo").hide();
			$("#ds_chitiettheo").attr('disabled', true);
			$("#loaidoituong").hide();
			$("#loaidoituong").attr('disabled', true);
		}
	});

	// Danh sách chi tiết theo
	$("#ds_chitiettheo").change(function () {
		if ($('#ds_chitiettheo :selected').val() == '0') {
			$("#loaidoituong").val('0');
			$("#loaidoituong").show();
			$("#loaidoituong").attr('disabled', false);
		} else {
			$("#loaidoituong").hide();
			$("#loaidoituong").attr('disabled', true);
		}
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
				data: 'ma=' + $('#manhom').val() + '&id=' + $('#EditView input[name=record]').val() + '&tbl=ec_nhomtaikhoan&fld=manhom',
				url: 'index.php?module=EC_NhomTaiKhoan&entryPoint=checkexist&action=EditView',
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
});