<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

trait AssetsTrait
{
	private function displayCSSTrait()
	{
		$cssVer   = inDeveloperMode() ? time() : '1.2.6';

		$css = '<link type="text/css" rel="stylesheet" href="themes/SuiteP/libs/css/select2.min.css" />' .
			'<link type="text/css" rel="stylesheet" href="modules/' . $this->bean->module_dir . '/css/view.detail.css?v=' . $cssVer . '" />' .
			'<link type="text/css" rel="stylesheet" href="modules/' . $this->bean->module_dir . '/css/api_zalo.css?v=' . $cssVer . '" />' .
			'<link type="text/css" rel="stylesheet" href="modules/' . $this->bean->module_dir . '/css/autobook.css?v=' . $cssVer . '" />';

		echo $css;
	}

	private function displayJSTrait()
	{
		global $app_list_strings, $current_user;

		$jsVer   = inDeveloperMode() ? time() : '1.2.6';

		$js = '
				<script src="modules/' . $this->bean->module_dir . '/js/view.detail.js?v=' . $jsVer . '"></script>
				<script src="modules/' . $this->bean->module_dir . '/js/autobook.js?v=' . $jsVer . '"></script>
				<script src="modules/' . $this->bean->module_dir . '/js/api_zalo.js?v=' . $jsVer . '"></script>
				<script src="modules/' . $this->bean->module_dir . '/js/api_sms.js?v=' . $jsVer . '"></script>
				<script src="modules/' . $this->bean->module_dir . '/js/doc_list.js?v=' . $jsVer . '"></script>
				<script src="modules/' . $this->bean->module_dir . '/js/print_ticket_new.js?v=' . $jsVer . '"></script>
			';

		// Map [iata_code => logo_url] để JS lấy logo hãng bay qua EC_Airlines::getLogoUrl() thay vì tự build đường dẫn ảnh tĩnh.
		$airlineLogoMap = [];
		foreach (array_keys(EC_Airlines::getAirlineList()) as $airlineCode) {
			$airlineLogoMap[$airlineCode] = EC_Airlines::getLogoUrl($airlineCode);
		}

		// Inject biến PHP sang JS để các script phía client dùng đúng trạng thái booking và cấu hình hiện tại.
		$js .= '<script>
			var airline_logo_map = ' . json_encode($airlineLogoMap) . ';
			var booking_status = "' . $this->bean->booking_status . '";
			var win_reason = "' . str_replace('"', "'", $this->getWinLoseReasonRadio($this->bean->lydothangthua_id, '0')) . '";
			var lose_reason = "' . str_replace('"', "'", $this->getWinLoseReasonRadio($this->bean->lydothangthua_id, '1')) . '";
			var domestic_airport_lst = ["' . implode('","', array_keys($app_list_strings['domestic_airport_list'])) . '"];
		
			let bba_ticket_class = ["Eco Saver max", "Eco Saver", "Eco Smart", "Eco Flex", "Pre smart", "Pre Flex", "Buz smart", "Buz Flex"];
			let vja_ticket_class = ["Eco", "Eco1", "B1 Eco", "W1 Eco", "E1 Eco", "R1 Eco"];
			let vna_ticket_class = ["Economy (EL)-Q", "Economy (EP)-A", "Economy (EL)-R", "Economy (EL)-C", "Economy (EC)-K", "Economy (EL)-T", "Economy (EL)-N", "Economy (EL)-E", "Economy (EP)-E", "Economy (EP)-P", "E", "A", "Economy (EC)-L"];
			let vta_ticket_class = ["Dregow (D)", "Cregow (C)", "Bregow (B)", "Aregow (A)", "Eregow (E)", "Kregow (K)", "Hregow (H)", "Mregow (M)", "Nfleow (N)", "Lregow (L)", "Vfleow (V)", "Yfleow (Y)"];
			
			const all_ticket_class = [].concat(bba_ticket_class, vja_ticket_class, vna_ticket_class, vta_ticket_class);
			const current_user_title = "' . trim($current_user->title) . '";
			const is_current_user_admin = ' . (is_admin($current_user) ? 'true' : 'false') . ';
			const is_invoice_export = "' . $this->bean->is_invoice_export . '";
			const is_invoice_input_export = "' . $this->bean->is_invoice_input_export . '";
		</script>';

		// Phải tạo phiếu thu trước rồi mới nhấn đã thanh toán.
		$this->_is_had_rv = myCheckValueExist('EC_Receipt_Voucher', array('booking_id'), array($this->bean->id), '');
		if (empty($this->_is_had_rv))
			$this->_is_had_rv = 0;
		$js .= '<script>
			function checkIsCreatedRV() {
				if(' . $this->_is_had_rv . ' != 1 && ' . $this->bean->is_agent . ' != 1) {
					$("#frmCheckIsPaid").addClass("error unerror");
					let text_warning = "Bạn phải tạo phiếu thu trước.";
					showToastWarning(text_warning);
					return false;
				} else {
					$("#frmCheckIsPaid").removeClass("error");
					return true;
				}
			}
		</script>';

		// Flash message 1 lần sau khi Save (vd: đánh dấu BK Telesale thất bại) - đọc & xoá khỏi session
		if (!empty($_SESSION['ec_flight_flash']) && is_array($_SESSION['ec_flight_flash'])) {
			$flash = $_SESSION['ec_flight_flash'];
			unset($_SESSION['ec_flight_flash']);
			$flashType = (int) ($flash['type'] ?? 2);
			$flashMsg = json_encode((string) ($flash['msg'] ?? ''), JSON_UNESCAPED_UNICODE);
			$js .= '<script>
				$(function () {
					if (typeof showModalNotify === "function") {
						showModalNotify(' . $flashType . ', ' . $flashMsg . ');
					}
				});
			</script>';
		}

		echo $js;
	}
}
