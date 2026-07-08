<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

trait ECFlightBookingEditAssetsTrait {
	function displayCSS() {
		echo <<<HTML
			<link rel="stylesheet" href="modules/EC_Flight_Bookings/css/view.edit.css?v=1.9.3" />
		HTML;
	}

	function displayJS()
	{
		global $current_user;
		$js = '';

		// Inject biến user và bảng giá hành lý Vietjet cho JS tính toán ban đầu.
		$js .= '<script>
			var assigned_user_id="' . $current_user->id . '";
			$(document).ready(function() {
				calculateTotal();
			});
		</script>';

		// Load JS chính của edit view: render row, tính tổng, xử lý hành lý/hành trình.
		$js .= '<script src="modules/EC_Flight_Bookings/js/view.edit.js?v=1.9.3"></script>';
		echo $js;
	}

	function displayJS_Edit()
	{
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
