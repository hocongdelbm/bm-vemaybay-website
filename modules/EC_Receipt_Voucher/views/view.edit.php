<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class EC_Receipt_VoucherViewEdit extends ViewEdit
{
	function __construct()
	{
		parent::__construct();
	}

	function display()
	{
		if (empty($this->bean->id) || $this->bean->rv_status == '0' || (isset($_POST['isDuplicate']) && $_POST['isDuplicate'])) {
			if (empty($this->bean->id) && !empty($_REQUEST['booking_id']) && isset($_REQUEST['go_with']) && $_REQUEST['go_with'] !== '') {
				$this->prefillLuggageFromBooking($_REQUEST['booking_id'], $_REQUEST['go_with']);
			}

			$this->displayCSS();
			$this->displayJS();
			$this->customFields();
			parent::display();
		} else echo '<p class="error">Chứng từ đã khóa</p>';
	}

	/**
	 * Autofill Nhà cung cấp/Giá bán/Giá mua/VAT giá mua từ đợt đổi thông tin hành lý
	 * (ec_booking_passengers.go_with) vào tối đa 3 slot NCC của phiếu thu.
	 * Outbound và inbound của cùng 1 dòng hành khách có thể khác NCC (supplier_id vs
	 * supplier_inbound_id) nên tách thành 2 nhóm riêng trước khi gom theo NCC.
	 */
	private function prefillLuggageFromBooking($booking_id, $go_with)
	{
		$db = $this->bean->db;
		$booking_id_q = $db->quote($booking_id);
		$go_with = (int) $go_with;

		$sql = "
			SELECT supplier_id, 0 AS direction,
				SUM(luggage_price) AS sell, SUM(luggage_purchase) AS buy, SUM(vat_luggage_purchase) AS vat
			FROM ec_booking_passengers
			WHERE deleted = 0 AND booking_id = '{$booking_id_q}' AND go_with = {$go_with}
				AND supplier_id IS NOT NULL AND supplier_id != ''
			GROUP BY supplier_id
			HAVING SUM(luggage_price) > 0 OR SUM(luggage_purchase) > 0

			UNION ALL

			SELECT supplier_inbound_id, 1 AS direction,
				SUM(luggage_price_inbound), SUM(luggage_purchase_inbound), SUM(vat_luggage_purchase_inbound)
			FROM ec_booking_passengers
			WHERE deleted = 0 AND booking_id = '{$booking_id_q}' AND go_with = {$go_with}
				AND supplier_inbound_id IS NOT NULL AND supplier_inbound_id != ''
			GROUP BY supplier_inbound_id
			HAVING SUM(luggage_price) > 0 OR SUM(luggage_purchase) > 0
		";

		$res = $db->query($sql);
		$n = 0;
		while ($n < 3 && ($row = $db->fetchByAssoc($res))) {
			$n++;
			$s = $n > 1 ? $n : '';
			$this->bean->{"supplier{$s}_id"}      = $row['supplier_id'];
			$this->bean->{"sup_direction{$s}"}    = $row['direction'];
			$this->bean->{"sell_amount{$s}"}      = $row['sell'];
			$this->bean->{"bought_amount{$s}"}    = $row['buy'];
			$this->bean->{"vat_bought_amount{$s}"} = $row['vat'];
		}
	}

	function displayCSS()
	{
		$css = '';
		$css .= '<link type="text/css" rel="stylesheet" href="themes/SuiteP/libs/css/select2.min.css">';
		$css .= '<link type="text/css" rel="stylesheet" href="modules/EC_Receipt_Voucher/css/view.edit.css">';

		echo $css;
	}

	function displayJS()
	{
		$js_file = 'modules/EC_Receipt_Voucher/js/view.edit.js';
		$v = file_exists($js_file) ? filemtime($js_file) : time();
		$js = '<script type="text/javascript" src="' . $js_file . '?v=' . $v . '"></script>';
		$js .= '<script>
			var record = "' . $this->bean->id . '";
			var loai_thu = "' . $this->bean->loai_thu . '";
			var amount_type = "' . $this->bean->amount_type . '";
		</script>';

		echo $js;
	}

	function customFields()
	{
		global $app_list_strings, $locale, $timedate, $current_user;
		$date_format = $timedate->get_date_format();

		$sep = my_get_number_separators();
		$group_decimal = '<input type="hidden" id="grp_seperator" name="grp_seperator" value="' . $sep[0] . '" />
		<input type="hidden" id="dec_seperator" name="dec_seperator" value="' . $sep[1] . '" />
		<input type="hidden" id="sig_digits" name="sig_digits" value="' . $locale->getPrecision() . '" />';

		// Amount
		$amount = '<span class="d-flex gap-2 align-items-center w-100">
			<input class="flex-fill min-w-25" type="text" name="amount" id="amount" size="20" value="' . (isset($_POST['amount']) ? $_POST['amount'] : format_number($this->bean->amount)) . '" tabindex="100">
			<span class="w-100" id="span-amt-converted" ' . ($this->bean->amount_type != 'VND' ? '' : 'style="display:none"') . '>
				<span class="w-33">- Quy đổi: </span>
				<input class="flex-fill" readonly="readonly" type="text" name="amount_converted" id="amount_converted" size="20" value="' . (isset($_POST['amount_converted']) ? $_POST['amount_converted'] : format_number($this->bean->amount_converted)) . '" tabindex="100">
			</span>
		</span>';
		$this->ss->assign('AMOUNT', $amount);


		// Amount type
		$amount_type = '<span class="d-flex gap-2 align-items-center">
			<select id="amount_type" name="amount_type" tabindex="101">' . get_select_options_with_id($app_list_strings['loaitien_list'], isset($this->bean->amount_type) ? $this->bean->amount_type : 'VND') . '</select>
			<span id="span-exchange-rate" ' . ($this->bean->amount_type != 'VND' ? '' : 'style="display:none"') . '>
				<span class="w-25">- Tỷ giá: </span>
				<input class="flex-fill" type="text" id="exchange_rate" name="exchange_rate" tabindex="101" size="13" value="' . (isset($this->bean->exchange_rate) ? format_number($this->bean->exchange_rate) : 0) . '">
			</span>
		</span>';
		$this->ss->assign('AMOUNT_TYPE', $amount_type);

		// NGAY HACH TOAN (Giờ lưu dưới DB là giờ VietNam)
		$this->bean->ngayhachtoan = isset($this->bean->ngayhachtoan) && !empty($this->bean->ngayhachtoan)
			? date("$date_format H:i", strtotime($this->bean->ngayhachtoan) - 7 * 3600)
			: date("$date_format H:i");

		// TAI KHOAN NGAN HANG
		$display = (isset($_POST['receipt_type']) && $_POST['receipt_type'] == 'credit_transfer') || $this->bean->receipt_type ==  'credit_transfer' ? '' : 'display:none';
		$display2 = (isset($_POST['receipt_type']) && $_POST['receipt_type'] == 'cash') || $this->bean->receipt_type ==  'cash' ? '' : 'display:none';
		$tknganhang_id = isset($this->bean->tknganhang_id) ? $this->bean->tknganhang_id : '';


		$tknganhang_group = "";

		$receipt_type = '<select name="receipt_type" id="receipt_type" title="" tabindex="104">' . get_select_options_with_id($app_list_strings['receipt_type_list'], isset($_POST['receipt_type']) ? $_POST['receipt_type'] : $this->bean->receipt_type) . '</select>';
		$receipt_type .= '<select style="' . $display . '" id="tknganhang_id" name="tknganhang_id" tabindex="104"><option value=""></option>' . myGetBankAccountList($tknganhang_id, $tknganhang_group) . '</select>';
		$receipt_type .= '<select id="com_location_id" name="com_location_id" class="w-100" style="' . $display2 . '" tabindex="104">' . myGetLocationListByDepID($this->bean->com_location_id) . '</select>';
		$this->ss->assign('RECEIPT_TYPE', $receipt_type . $group_decimal);


		// LOAI THU
		$loaithu_arr = ['4', '5', '10', '11', '12', '13', '14', '16', '27'];
		$loaithu = '<style>
			.ui-autocomplete-loading {
				background: white url(custom/jqueryui/css/ui-lightness/images/ui-anim_basic_16x16.gif) right center no-repeat;
			} 
		</style>';
		$loaithu .= '<div class="d-flex gap-2 flex-column">
			<div class="loai_thu--wrap d-inline-flex gap-2 align-items-center">
			<select id="loai_thu" name="loai_thu" tabindex="106" class="box-select">
				' . get_select_options_with_id($app_list_strings['loai_thu_list'], (int)($_POST['loai_thu'] ?? $this->bean->loai_thu)) . '
			</select>';

		$loaithu .= '<div id="span_customer" class="flex-fill">
				<div class="d-flex gap-1">
					<input type="text" class="flex-fill" name="customer" id="customer" tbl="accounts" fld=\'{"id":"account_id_c", "name":"customer"}\' tabindex="106" size="20" autocomplete="off" value="' . (isset($_POST['customer']) ? $_POST['customer'] : $this->bean->customer) . '" />
					<input type="hidden" name="account_id_c" id="account_id_c" value="' . (isset($_POST['account_id_c']) ? $_POST['account_id_c'] : $this->bean->account_id_c) . '" />
					<button type="button" name="btnSelectAccount" id="btnSelectAccount" tabindex="0" title="Chọn" class="px-1 btn btn-primary" value="Chọn">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
					</button>
					<button type="button" name="btnClearAccount" id="btnClearAccount" tabindex="0" title="Xóa" class="px-1 btn btn-secondary" value="Xóa">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
					</button>
				</div>
			</div>
		</div>';
		$loaithu .= '<span id="span_supplier" ' . (in_array($this->bean->loai_thu, $loaithu_arr) ? '' : 'style="display:none;"') . '>
		<table border="0" width="100%" cellpadding="0" cellspacing="0" style="line-height:20px;">';
		$loaithu .= '<tr>
			<td style="width:44%; font-weight:bold; text-align:left;">Nhà cung cấp</td>
			<td style="width:16%; font-weight:bold; text-align:center;">Chiều bay</td>
			<td style="width:20%; font-weight:bold; text-align:center;">Giá bán</td>
			<td style="width:20%; font-weight:bold; text-align:center;">Giá mua</td>
		</tr>';
		foreach ([1, 2, 3] as $n) {
			$s       = $n > 1 ? $n : '';
			$supId   = "supplier{$s}_id";
			$sellF   = "sell_amount{$s}";
			$buyF    = "bought_amount{$s}";
			$dirF    = "sup_direction{$s}";
			$vatBuyF = "vat_bought_amount{$s}";
			$loaithu .= $this->buildSupplierSelectRow(
				$supId,
				$sellF,
				$buyF,
				$dirF,
				$vatBuyF,
				$this->bean->$supId ?? '',
				$this->bean->$sellF ?? 0,
				$this->bean->$buyF  ?? 0,
				$this->bean->$dirF  ?? '',
				$this->bean->$vatBuyF ?? 0
			);
		}
		$loaithu .= '</table></span></div>';
		$this->ss->assign('LOAI_THU', $loaithu);


		// Nhân viên
		$employee_arr = $this->getEmployeeList();
		$employee_list = '<select id="employee-select" name="employee_id"><option value="">-- Trống --</option>' . get_select_options_with_id($employee_arr, $this->bean->employee_id) . '</select>';
		$this->ss->assign('EMPLOYEE_NAME', $employee_list);
	}

	private function buildSupplierSelectRow($supId, $sellF, $buyF, $dirF, $vatBuyF, $supplierId, $sellAmount, $boughtAmount, $direction = '', $vatBought = 0)
	{
		global $app_list_strings;
		$options = myGetSelectOptionsWithDb('Accounts', $supplierId, 'id', " AND account_type='Supplier' AND is_stop_tracking = 0 ");

		// Ô chiều bay: để trống = tự động suy ra hãng; chọn Lượt đi/về.
		$dirOptions = '<option value=""' . ($direction === '' ? ' selected' : '') . '></option>';
		foreach ($app_list_strings['bk_direction_list'] as $dk => $dv) {
			$sel = ((string) $dk === (string) $direction) ? ' selected' : '';
			$dirOptions .= '<option value="' . $dk . '"' . $sel . '>' . $dv . '</option>';
		}

		return '<tr>
			<td style="text-align:left; padding:3px;">
				<select id="' . $supId . '" name="' . $supId . '" tabindex="106" class="w-100">
					<option value=""></option>
					' . $options . '
				</select>
			</td>
			<td style="text-align:left; padding:3px;">
				<select id="' . $dirF . '" name="' . $dirF . '" tabindex="106" class="w-100">' . $dirOptions . '</select>
			</td>
			<td style="text-align:left; padding:3px;">
				<input class="allow-number-only" type="text" id="' . $sellF . '" name="' . $sellF . '" value="' . format_number($sellAmount) . '" tabindex="106" style="width:100%;" />
			</td>
			<td style="text-align:left; padding:3px;">
				<input class="allow-number-only" type="text" id="' . $buyF . '" name="' . $buyF . '" value="' . format_number($boughtAmount) . '" tabindex="106" style="width:100%;" />
				<input type="hidden" id="' . $vatBuyF . '" name="' . $vatBuyF . '" value="' . format_number($vatBought) . '" />
			</td>
		</tr>';
	}

	function getEmployeeList()
	{
		$sql = 'SELECT id, CONCAT(last_name, " ", IFNULL(first_name, "")) AS full_name
				FROM users
				WHERE deleted = 0
				AND title NOT IN ("Admin", "Bot")';
		$res = $this->bean->db->query($sql);
		$employee_list = [];
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$employee_list[$row['id']] = $row['full_name'];
		}
		return $employee_list;
	}
}
