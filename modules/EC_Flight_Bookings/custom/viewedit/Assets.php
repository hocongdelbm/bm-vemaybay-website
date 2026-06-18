<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * CSS/JS assets for EC_Flight_Bookings edit view.
 *
 * Used by EC_Flight_BookingsViewEdit. Methods are kept close to the
 * legacy implementation to preserve the old business behavior.
 */
trait ECFlightBookingEditAssetsTrait
{

	function displayCSS()
	{
		// Load CSS riêng cho form tạo/sửa booking.
		$css = '';
		$css .= '<link rel="stylesheet" href="modules/EC_Flight_Bookings/css/view.edit.css?v=1.1">';
		echo $css;
	}

	function displayJS()
	{
		global $current_user, $app_list_strings;
		$js = '';

		// Inject biến user và bảng giá hành lý Vietjet cho JS tính toán ban đầu.
		$js .= '<script>
			var assigned_user_id="' . $current_user->id . '";
			var vja_luggage_index_list=' . json_encode(array_values($app_list_strings['vietjet_index_price_list2'])) . ';
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
		$js .= '<script src="modules/EC_Flight_Bookings/js/view.edit.js?v=1.9"></script>';
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
