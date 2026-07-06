<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

trait ECFlightBookingEditAssetsTrait {
	function displayCSS() {
		echo <<<HTML
			<link rel="stylesheet" href="modules/EC_Flight_Bookings/css/view.edit.css?v=1.2.0" />
		HTML;
	}

	function displayJS()
	{
		global $current_user, $app_list_strings;
		$js = '';

		// Inject biến user và bảng giá hành lý Vietjet cho JS tính toán ban đầu.
		$js .= '<script>
			var assigned_user_id="' . $current_user->id . '";
			$(document).ready(function() {
				calculateTotal();
			});
		</script>';

		if (!is_admin($current_user)) {
			// User thường không được nhìn/đổi field assigned_user_name trực tiếp.
			$js .= '<script>
				$(document).ready(function() {
					$("#assigned_user_name_label").css("visibility", "hidden");
				});
			</script>';
		}

		// Load JS chính của edit view: render row, tính tổng, xử lý hành lý/hành trình.
		$js .= '<script src="modules/EC_Flight_Bookings/js/view.edit.js?v=1.9.2"></script>';
		echo $js;
	}

	function displayJS_Edit()
	{
		// Khi quản lý sửa booking đã xuất vé/hoàn tất, ẩn bớt các panel không được phép chỉnh.
		$js = '';
		$js .= '<script>
			$(document).ready(function() {
				$("#detailpanel_1").hide();
				$("#detailpanel_2").hide();
			});
		</script>';

		echo $js;
	}
}
