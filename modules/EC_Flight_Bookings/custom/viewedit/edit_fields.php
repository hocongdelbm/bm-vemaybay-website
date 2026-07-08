<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

trait EditFieldsTrait
{
	/**
	 * Thông tin hóa đơn
	 */
	public function assignInvoiceField()
	{
		$iv_payment_method = [
			'' => '',
			'Tiền mặt' => 'Tiền mặt',
			'Chuyển khoản' => 'Chuyển khoản',
			'Tiền mặt hoặc Chuyển khoản' => 'Tiền mặt hoặc Chuyển khoản',
		];

		$invoice_arr = json_decode(str_replace("&quot;", "\"", $this->bean->shipping_address), true);
		$invoice_arr = is_array($invoice_arr) ? $invoice_arr : [];

		$textField = function (string $id, int $size, string $value) {
			return '<input type="text" id="' . $id . '" name="' . $id . '" size="' . $size . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '" />';
		};

		$this->ss->assign('CUS_IV_ACCOUNT_NAME', $textField('iv_account_name', 30, $invoice_arr['iv_account_name'] ?? ''));
		$this->ss->assign('CUS_IV_EMAIL', $textField('iv_email', 30, $invoice_arr['iv_email'] ?? ''));
		$this->ss->assign('CUS_IV_IDENTITY_NUMBER', $textField('iv_identity_number', 12, $invoice_arr['iv_identity_number'] ?? ''));
		$this->ss->assign(
			'CUS_IV_PAYMENT_METHOD',
			'<select id="iv_payment_method" name="iv_payment_method" class="w-100">'
				. get_select_options_with_id($iv_payment_method, $invoice_arr['iv_payment_method'] ?? '')
				. '</select>'
		);
		$bankList = EC_Flight_Bookings::getInvoiceBankList();
		$this->ss->assign(
			'CUS_IV_NAME_BANK',
			'<select id="iv_name_banks" name="iv_name_banks" class="w-100">'
				. get_select_options_with_id($bankList, $invoice_arr['iv_name_banks'] ?? '')
				. '</select>'
		);
		$this->ss->assign('CUS_IV_BANK_ACCOUNT', $textField('iv_bank_account', 30, $invoice_arr['iv_bank_account'] ?? ''));
	}

	/**
	 * Nơi đặt vé của Booking
	 */
	public function assignCityField()
	{
		$cities = [];
		$res = $this->bean->db->query("SELECT DISTINCT city_name FROM ec_airports WHERE deleted = 0 AND city_name != '' ORDER BY city_name");
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$cities[] = $row['city_name'];
		}

		$html = '<div class="ui-widget">
			<input placeholder="Hồ Chí Minh, Hà Nội,..." type="text" class="location_booking" name="city" id="location_booking"
				value="' . htmlspecialchars($this->bean->city ?? '', ENT_QUOTES, 'UTF-8') . '"
				data-cities="' . htmlspecialchars(json_encode($cities, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . '">
		</div>';
		$this->ss->assign('CITY', $html);
	}
}
