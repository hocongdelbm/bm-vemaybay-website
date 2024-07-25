$(document).ready(function () {
	// Không cho nhấn phím enter trên form
	$("form").bind("keypress", function (e) {
		if (e.keyCode == 13) return false;
	});

	// Xử lý sự kiện save
	$('#btnSave').click(function () {
		var is_exist = 0;

		if (check_form('EditView')) {
			$.ajax({
				cache: false,
				type: 'GET',
				data: 'tbl=ec_banks&fld=short_name&fldv=' + $('#short_name').val() + '&id=' + $('#EditView input[name=record]').val(),
				url: 'index.php?module=EC_Banks&entryPoint=checkexist&action=EditView',
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
