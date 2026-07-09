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

	/**
	 * Thông tin giao cho
	 * Chỉ có Admin - Quanly - Kế toán mới dc phép chuyển đổi giao cho
	 */
	public function assignAssignToUserField()
	{
		global $current_user;

		if (!isManagerUser($current_user->id)) {
			return;
		}

		$user = new User;
		$user->retrieve($this->bean->assigned_user_id);

		$html = '
			<input type="text" name="assigned_user_name" class="sqsEnabled yui-ac-input" id="assigned_user_name" value="' . htmlspecialchars($user->user_name ?? '', ENT_QUOTES, 'UTF-8') . '" autocomplete="off">
			<input type="hidden" name="assigned_user_id" id="assigned_user_id" value="' . htmlspecialchars($this->bean->assigned_user_id ?? '', ENT_QUOTES, 'UTF-8') . '">
			<button type="button" name="btn_assigned_user_name" id="btn_assigned_user_name" tabindex="0" title="Chọn [Alt+T]" class="px-1 btn btn-primary" accesskey="T" value="Chọn" onclick="open_popup(&quot;Users&quot;, 600, 400, &quot;&quot;, true, false, {&quot;call_back_function&quot;:&quot;set_return&quot;,&quot;form_name&quot;:&quot;EditView&quot;,&quot;field_to_name_array&quot;:{&quot;id&quot;:&quot;assigned_user_id&quot;,&quot;user_name&quot;:&quot;assigned_user_name&quot;}}, &quot;single&quot;, true);">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
			</button>
			<button type="button" name="btn_clr_assigned_user_name" id="btn_clr_assigned_user_name" tabindex="0" title="Xóa trắng [Alt+C]" class="px-1 btn btn-secondary" value="Xóa" onclick="this.form.assigned_user_name.value = \'\'; this.form.assigned_user_id.value = \'\';">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
			</button>
		';
		$this->ss->assign('CUS_ASSIGNED_USER_NAME', $html);
	}

	/**
	 * Tên booking
	 */
	public function assignNameBookingField()
	{
		if ($this->bean->name == '') {
			return;
		}

		if (ACLController::checkAccess($this->bean->object_name, 'edit', true)) {
			$name_booking = '<div class="d-flex align-items-center row-booking__name gap-2">
								<input type="text" name="name" id="name" value="' . htmlspecialchars($this->bean->name, ENT_QUOTES, 'UTF-8') . '" />
							</div>';
		} else {
			$name_booking = '<div class="d-flex align-items-center row-booking__name gap-2">
							<p class="label">' . htmlspecialchars($this->bean->name, ENT_QUOTES, 'UTF-8') . '</p>
						</div>';
		}
		$this->ss->assign('NAME_BOOKING', $name_booking);
	}

	/**
	 * Là đại lý
	 */
	public function assignIsAgentField()
	{
		$is_agent = '<span class="d-flex gap-2 align-items-center">
				<input type="checkbox" name="chk_is_agent" id="chk_is_agent" tabindex="109" ' . ($this->bean->is_agent ? 'checked="checked"' : '') . ' />
				<input type="hidden" name="is_agent" id="is_agent" value="' . $this->bean->is_agent . '" />
				<select name="agent_id" id="agent_id" tabindex="109"><option value=""></option>' . myGetSelectOptionsWithDb('Accounts', $this->bean->agent_id, 'id', " AND account_type='Partner' ") . '</select>
			<span>';

		$is_agent .= '
			<link type="text/css" rel="stylesheet" href="./themes/SuiteP/libs/css/select2.min.css">
			<script>
				$(document).ready(function() {
					$("#agent_id").select2();
					$("#agent_id").next("span").hide();
				});
			</script>
		';
		$this->ss->assign('IS_AGENT', $is_agent);
	}

	/**
	 * Check đã xuất vé
	 */
	public function assignTicketExportedField()
	{
		global $timedate;

		$date_format = $timedate->get_date_format();

		$is_ticket_exported = '<span class="is_ticket_exported">
			<input type="checkbox" name="chk_is_ticket_exported" id="chk_is_ticket_exported" tabindex="105" ' . ($this->bean->is_ticket_exported ? 'checked="checked"' : '') . ' />
			<input type="hidden" name="is_ticket_exported" id="is_ticket_exported" value="' . $this->bean->is_ticket_exported . '" />
			<span class="date_ticket_issue" ' . ($this->bean->is_ticket_exported ? '' : 'style="display:none"') . '>
				<input type="text" maxlength="10" size="11" tabindex="105" title="" value="' . ($this->bean->date_ticket_issue ? $this->bean->date_ticket_issue : date($date_format)) . '" id="date_ticket_issue" name="date_ticket_issue" autocomplete="off">
				<img border="0" align="absmiddle" id="date_ticket_issue_trigger" alt="input date" src="themes/SuiteP/images/Calendar.svg">
			</span>
		</span>';
		$this->ss->assign('IS_TICKET_EXPORTED', $is_ticket_exported);
	}

	/**
	 * Danh xưng liên hệ
	 */
	public function assignContactNameField()
	{
		global $app_list_strings;

		$contact_name = '<div class="d-flex gap-2">
			<select id="contact_title" name="contact_title" class="" tabindex="112">' . get_select_options_with_id($app_list_strings['passenger_salutation_list'], $this->bean->contact_title ? (int) $this->bean->contact_title : 0) . '</select>
			<input type="text" name="contact_name" id="contact_name" class="flex-fill" value="' . ($this->bean->contact_name ? $this->bean->contact_name : '') . '" />
		</div>';
		$this->ss->assign('CONTACT_NAME', $contact_name);
	}

	/**
	 * Airline outbound - Airline inbound
	 */
	public function assignAirlineField()
	{
		$airline = isset($_POST['airline']) && !empty($_POST['airline']) ? $_POST['airline'] : (isset($this->bean->airline) ? $this->bean->airline : '');
		$airline_inbound = isset($_POST['airline_inbound']) && !empty($_POST['airline_inbound']) ? $_POST['airline_inbound'] : (isset($this->bean->airline_inbound) ? $this->bean->airline_inbound : '');
		$airline_html = '<div class="d-flex align-items-center gap-2">
			<div class="airline-wrap airline-wrap__outbound d-flex gap-2 align-items-center flex-fill">
				<span class="sublabel">Lượt đi:</span>
				<input type="text" class="flex-fill value form-control airline_code" name="airline" id="airline" value="' . $airline . '" size="7" placeholder="VJA, VNA, BBA,...">
			</div>
			<div class="airline-wrap airline-wrap__inbound d-flex gap-2 align-items-center flex-fill">
				<span class="sublabel">Lượt về:</span>
				<input type="text" class="flex-fill value form-control airline_code" name="airline_inbound" id="airline_inbound" value="' . $airline_inbound . '" size="8" placeholder="VJA, VNA, BBA,...">
			</div>
		</div>';
		$this->ss->assign('AIRLINE', $airline_html);
	}

	/**
	 * Date ticket issue - Date ticket inbound issue (ngày xuất vé lượt đi - về)
	 */
	public function assignDateTicketIssueField()
	{
		if (!ACLController::checkAccess($this->bean->object_name, 'edit', true)) {
			return;
		}

		$icon_calendar = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
							<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
							<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
						</svg>';

		$date_ticket_issue = '<div class="d-flex cus_date_ticket_issue gap-2 align-items-center">
							<div class="d-flex gap-2 align-items-center date_ticket_issue--wrap date_ticket_issue_outbound flex-fill">
								<span class="sublabel">Lượt đi:</span>
								<span class="value dateTime d-flex gap-2 flex-fill position-relative">
									<input type="text" id="date_ticket_issue_outbound" name="date_ticket_issue" value="' . $this->bean->date_ticket_issue . '" class="date_input flex-fill" autocomplete="off" tabindex="0">
									<button type="button" id="date_ticket_issue_outbound_trigger" class="icon_dateTime" onclick="return false;">' . $icon_calendar . '</button>
								</span>
							</div>
							<div class="d-flex gap-2 align-items-center date_ticket_issue--wrap date_ticket_issue_inbound flex-fill">
								<span class="sublabel">Lượt về:</span>
								<span class="value dateTime d-flex gap-2 flex-fill position-relative">
									<input type="text" id="date_ticket_issue_inbound" name="date_ticket_inbound_issue" value="' . $this->bean->date_ticket_inbound_issue . '" class="date_input flex-fill" autocomplete="off" tabindex="0">
									<button type="button" id="date_ticket_issue_inbound_trigger" class="icon_dateTime" onclick="return false;">' . $icon_calendar . '</button>
								</span>
							</div>
						</div>';

		$date_ticket_issue .= '<script>
			$(document).ready(function() {
				var cal_date_format = $("#cal_date_format").val();

				Calendar.setup({
					inputField : "date_ticket_issue_outbound",
					daFormat : cal_date_format,
					button : "date_ticket_issue_outbound_trigger",
					singleClick : true,
					dateStr : "",
					step : 1,
					weekNumbers : false
				});
				Calendar.setup({
					inputField : "date_ticket_issue_inbound",
					daFormat : cal_date_format,
					button : "date_ticket_issue_inbound_trigger",
					singleClick : true,
					dateStr : "",
					step : 1,
					weekNumbers : false
				});
			});
		</script>';

		$this->ss->assign('DATE_TICKET_ISSUE', $date_ticket_issue);
	}
}
