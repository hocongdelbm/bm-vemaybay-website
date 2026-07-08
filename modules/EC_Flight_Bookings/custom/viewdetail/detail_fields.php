<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 * Booking field, ticket detail, and invoice rendering.
 *
 * Used by EC_Flight_BookingsViewDetail. Methods are kept close to the
 * legacy implementation to preserve the old business behavior.
 */
trait DetailFieldsTrait
{
	private function assignInvoiceInfoField()
	{
		// Thông tin hoá đơn
		$iv_account_name = $iv_email = $iv_identity_number = $iv_payment_method = '';
		$iv_name_banks = $iv_bank_account = '';

		if (!empty($this->bean->shipping_address)) {
			$invoice_arr = json_decode(str_replace("&quot;", "\"", $this->bean->shipping_address), 1);
			$iv_account_name = $invoice_arr['iv_account_name'] ?? '';
			$iv_email = $invoice_arr['iv_email'] ?? '';
			$iv_identity_number = $invoice_arr['iv_identity_number'] ?? '';
			$iv_payment_method = $invoice_arr['iv_payment_method'] ?? '';
			$iv_name_banks = $invoice_arr['iv_name_banks'] ?? '';
			$iv_bank_account = $invoice_arr['iv_bank_account'] ?? '';
		}

		$bankList = EC_Flight_Bookings::getInvoiceBankList();

		$this->ss->assign('CUS_IV_ACCOUNT_NAME', $iv_account_name);
		$this->ss->assign('CUS_IV_EMAIL', $iv_email);
		$this->ss->assign('CUS_IV_IDENTITY_NUMBER', $iv_identity_number);
		$this->ss->assign('CUS_IV_PAYMENT_METHOD', $iv_payment_method);
		$this->ss->assign('CUS_IV_NAME_BANKS', $bankList[$iv_name_banks] ?? $iv_name_banks);
		$this->ss->assign('CUS_IV_BANK_ACCOUNT', $iv_bank_account);
	}

	private function assignBookingNameField()
	{
		global $app_list_strings;

		// Booking - tình trạng
		$booking_name = '<b>' . $this->bean->name . '</b> - <span class="fw-bold" style="color:' . ($app_list_strings['booking_status_color_list'][(int) $this->bean->booking_status] ?? '#333') . ';">' . ($app_list_strings['booking_status_list'][(int) $this->bean->booking_status] ?? '') . '</span>';
		$this->ss->assign('CUSTOM_NAME', $booking_name);
	}

	private function assignTicketTypeField()
	{
		global $app_list_strings;

		// Loại vé - Chuyến bay
		$this->ss->assign('CUSTOM_TICKET_TYPE', <<<HTML
			<span>{$app_list_strings['booking_ticket_type_list'][$this->bean->ticket_type]}</span>
			<span class="ms-3">Chuyến bay: <b>{$app_list_strings['bk_flight_type_list'][$this->bean->flight_type]}</b></span>
		HTML);
	}

	private function assignCustomerSourceField()
	{
		global $app_list_strings;

		// Nguồn khách hàng
		$customer_source = '<div class="d-flex align-items-center flex-wrap gap-3">';
		foreach ($app_list_strings['booking_customer_source_list'] as $value => $label) {
			$checked = $value == $this->bean->customer_source ? 'checked' : '';
			$customer_source .= '<div class="item small">
				<input type="checkbox" name="customer_source" id="customer_source_' . $value . '" value="' . $value . '" ' . $checked . ' /> ' . $label . '
			</div>';
		}
		$customer_source .= '</div>';
		$this->ss->assign('CUSTOM_CUSTOMER_SOURCE', $customer_source);
	}

	private function renderActiveBookmarkCheckbox(string $fieldId, string $confirmMessage, string $hiddenFieldName = '')
	{
		$hiddenFieldName = $hiddenFieldName ?: $fieldId;
		$confirmJs = "if(confirm('" . $confirmMessage . "')){this.form.submit();$('.container-waiting').show();}else{this.checked=false;}";

		return '
			</form><form name="frmCheck' . $fieldId . '" id="frmCheck' . $fieldId . '" action="index.php" method="post">
				<input type="hidden" name="module" value="' . $this->bean->module_dir . '" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="' . $this->bean->id . '" />
				<input type="hidden" name="record_name" value="' . $this->bean->name . '" />
				<input type="hidden" name="' . $hiddenFieldName . '" value="1" />
				<input type="checkbox" name="' . $fieldId . '" id="check' . $fieldId . '" onclick="' . $confirmJs . '" />
			</form>';
	}

	private function assignBookingBookmarkField()
	{
		// Đánh dấu - các cờ người dùng CHỦ ĐỘNG gắn cho booking (luôn xác nhận trước khi lưu)
		$list_bookmark = '<div class="d-flex align-items-start flex-wrap gap-3">';

		// Telesale
		if ($this->bean->is_telesale && !empty($this->bean->telesale_call_id)) {
			$call_name = $this->bean->db->getOne("SELECT name FROM calls WHERE id = '{$this->bean->telesale_call_id}' AND deleted = 0") ?? '';
			$list_bookmark .= '<div class="item small">
				<label for="checkIsTelesale">Là BK Telesale</label>
				<input type="checkbox" name="is_telesale" id="checkIsTelesale" checked disabled />
				<br />
				<a href="index.php?module=Calls&action=DetailView&record=' . $this->bean->telesale_call_id . '" target="_blank">' . $call_name . '</a>
			</div>';
		} elseif ((int)$this->bean->booking_status === 8) {
			$list_bookmark .= '<div class="item small">
				<label for="checkIsTelesale">Là BK Telesale</label>'
				. $this->renderActiveBookmarkCheckbox('IsTelesale', 'Bạn có chắc chắn muốn đánh dấu đây là BK Telesale?', 'is_telesale_value') .
				'</div>';
		}

		// CTV
		if ($this->bean->is_ctv) {
			$list_bookmark .= '<div class="item small">
				<label for="checkIsCTV" >Là CTV</label>
				<input type="checkbox" name="is_ctv" id="checkIsCTV" checked disabled />
			</div>';
		} else if ((int)$this->bean->booking_status === 8) {
			$list_bookmark .= '<div class="item small">
				<label for="checkIsCTV">Là CTV</label>'
				. $this->renderActiveBookmarkCheckbox('IsCTV', 'Bạn có chắc chắn muốn đánh dấu đây là BK của CTV?', 'is_ctv_value') .
				'</div>';
		}

		// BK tham khảo - trước đây là nút riêng trên toolbar (BK tham khảo), nay gộp vào checkbox này
		if ($this->bean->is_reference) {
			$list_bookmark .= '<div class="item small">
				<label for="checkIsReference">BK tham khảo</label>
				<input type="checkbox" id="checkIsReference" checked disabled />
			</div>';
		} else {
			$list_bookmark .= '<div class="item small">
				<label for="checkIsReference">BK tham khảo</label>'
				. $this->renderActiveBookmarkCheckbox('IsReference', 'Bạn có chắc chắn muốn đánh dấu đây là BK tham khảo?', 'is_reference') .
				'</div>';
		}

		$list_bookmark .= '</div>';
		$this->ss->assign('CUSTOM_BOOKMARK', $list_bookmark);
	}

	private function assignSystemBookmarkField()
	{
		// Hệ thống đánh dấu - các cờ do HỆ THỐNG/quy trình khác tự set (không thao tác trực tiếp ở đây)
		$checkedIsPrior = $this->bean->is_prior ? 'checked' : '';
		$checkedIsMailConfirm = $this->bean->is_mail_confirm ? 'checked' : '';
		$checkedIsHold = $this->bean->is_hold ? 'checked' : '';

		$agentHtml = '<div class="item small">
			<label for="check_is_agent">Là đại lý</label>
			<input type="checkbox" name="is_agent" id="check_is_agent" ' . ($this->bean->is_agent ? 'checked' : '') . ' disabled />';
		if ($this->bean->is_agent && !empty($this->bean->agent_id)) {
			$agentHtml .= '<br /><a href="index.php?module=Accounts&action=DetailView&record=' . $this->bean->agent_id . '" target="_blank">' . $this->bean->agent_name . '</a>';
		}
		$agentHtml .= '</div>';

		$this->ss->assign('CUSTOM_BOOKMARK_SYSTEM', '
			<div class="d-flex align-items-start flex-wrap gap-3">
				<div class="item small"><input type="checkbox" id="is_prior" ' . $checkedIsPrior . ' disabled /> <label>Vé cận</label></div>
				<div class="item small"><input type="checkbox" id="is_mail_confirm" ' . $checkedIsMailConfirm . ' disabled /> <label>Mail xác nhận</label></div>
				' . $agentHtml . '
				<div class="item small">
					<label for="check_is_hold">Đã giữ chỗ</label>
					<input type="checkbox" name="is_hold" id="check_is_hold" ' . $checkedIsHold . ' disabled />
				</div>
			</div>');
	}

	private function assignTicketExportedField()
	{
		// Đã xuất vé
		$checkedOutbound = $this->bean->is_ticket_exported ? "checked" : "";
		$checkedInbound  = $this->bean->is_ticket_inbound_exported ? "checked" : "";
		$this->ss->assign('CUSTOM_IS_EXPORTED', <<<HTML
			<span class="is_ticket_exported d-flex align-items-center">
				<span class="w-50 d-flex gap-2 align-items-center is_ticket_exported--wrap">
					<span>Lượt đi: </span>
					<input disabled type="checkbox" id="is_ticket_exported" $checkedOutbound  />
				</span>
				<span class="flex-fill d-flex gap-2 align-items-center is_ticket_inbound_exported--wrap">
					<span>Lượt về: </span>
					<input disabled type="checkbox" id="is_ticket_inbound_exported" $checkedInbound />
				</span>
			</span>
		HTML);
	}

	private function assignTicketIssueDateField()
	{
		// Date ticket issue (ngày xuất vé) - giao vé
		$this->ss->assign('CUSTOM_DATE_TICKET_ISSUE', <<<HTML
			<span class="is_ticket_exported d-flex align-items-center">
				<span class="w-50 d-flex gap-2 align-items-center is_ticket_exported--wrap">
					<span>Lượt đi: {$this->bean->date_ticket_issue}</span>
				</span>
				<span class="flex-fill d-flex gap-2 align-items-center is_ticket_inbound_exported--wrap">
					<span>Lượt về: {$this->bean->date_ticket_inbound_issue}</span>
				</span>
			</span>
		HTML);
	}

	private function assignAirlineField()
	{
		// Airline code
		$this->ss->assign('CUSTOM_AIRLINE', <<<HTML
			Chiều đi: <b style="margin-right:20px">{$this->bean->airline}</b>
			Chiều về: <b>{$this->bean->airline_inbound}</b>
		HTML);
	}

	private function assignContactNameField()
	{
		global $app_list_strings;

		/************  CONTACT  ************/
		$contact_title = $app_list_strings['passenger_salutation_list'][(int) $this->bean->contact_title];
		$link_contact = $this->bean->contact_id ? "index.php?module=Contacts&action=DetailView&record=" . $this->bean->contact_id : "#";
		$type_contact = classifyContactv2($this->bean->contact_id);

		$contact_name_new = '<a target="_blank" href="' . $link_contact . '" class="contact_name" data="' . $this->bean->contact_name . '"><span>' . ($contact_title ? $contact_title . '. ' : '') . $this->bean->contact_name . '</span></a>';
		$contact_assign = '<div class="card-contact gap-2 card-contact__' . $type_contact['type'] . '">
			<div class="flex-fill contact-header">
				' . $contact_name_new . '
			</div>
			<div data-bs-toggle="modal" data-bs-target="#modalHistoryContactBookings" class="flex-fill card-contact-footer contact-footer flex-end" contact_id="' . $this->bean->contact_id . '" booking_id="' . $this->bean->id . '">
				<span class="temp d-none">' . ($type_contact['totalBookings'] ?? 0) . '</span>
				<div class="temp-scale">
					<span>' . $type_contact['label'] . '</span>
				</div>
			</div>
		</div>';

		$modal_history_bookings = '<div class="modal fade modal-history-bookings" id="modalHistoryContactBookings" tabindex="-1" aria-labelledby="modalHistoryContactBookingsLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="modal-title fs-5 text-white" id="modalHistoryContactBookingsLabel">Lịch sử booking của liên hệ</h1>
						<button type="button" class="btn-close me-2" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="td_spinner"></div>
						<div id="dialog-history-bookings"></div>
					</div>
				</div>
			</div>
		</div>';

		$this->ss->assign('CONTACT_NAME', $contact_assign . $modal_history_bookings);
	}

	private function assignContactPhoneField()
	{
		$journeys_info = $this->getJourneysByBooking($this->bean->id);
		$pass_and_bag  = $this->getPassengerAndBaggage($this->bean->id);
		$zbs_history   = $this->getHistoryZBS($this->bean->phone, $this->bean->id);
		$contact_phone = '<div class="wrap-phone d-flex align-items-center justify-content-between">
			<a href="tel:' . $this->bean->phone . '">' . $this->bean->phone . '</a>
			<div class="d-flex align-items-center gap-2">
				<div class="history-calls__wrap">
					<button id="view-history-calls" class="d-flex align-items-center gap-2 btn btn-primary-2" call_phone="' . $this->bean->phone . '" call_id_booking="' . $this->bean->id . '">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
							<path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.76.2 1.126.342zm1.37.71a7.01 7.01 0 0 0-.439-.27l.493-.87a8.025 8.025 0 0 1 .979.654l-.615.789a6.996 6.996 0 0 0-.418-.302zm1.834 1.79a6.99 6.99 0 0 0-.653-.796l.724-.69c.27.285.52.59.747.91l-.818.576zm.744 1.352a7.08 7.08 0 0 0-.214-.468l.893-.45a7.976 7.976 0 0 1 .45 1.088l-.95.313a7.023 7.023 0 0 0-.179-.483m.53 2.507a6.991 6.991 0 0 0-.1-1.025l.985-.17c.067.386.106.778.116 1.17l-1 .025zm-.131 1.538c.033-.17.06-.339.081-.51l.993.123a7.957 7.957 0 0 1-.23 1.155l-.964-.267c.046-.165.086-.332.12-.501zm-.952 2.379c.184-.29.346-.594.486-.908l.914.405c-.16.36-.345.706-.555 1.038l-.845-.535m-.964 1.205c.122-.122.239-.248.35-.378l.758.653a8.073 8.073 0 0 1-.401.432l-.707-.707z"/>
							<path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
							<path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
						</svg>
						<span>Cuộc gọi</span>
					</button>
					<div id="dialog-view-history-calls" title="Lịch sử cuộc gọi" style="display:none;"></div>
				</div>
				<button id="send-zalo" class="p-0" style="border:none; background:none;">
					<svg height="26" viewBox="0 0 460.1 436.6" width="32" xmlns="http://www.w3.org/2000/svg"><style>.st0{fill:#fdfefe}.st1{fill:#0180c7}.st2{fill:#0172b1}.st3{fill:none;stroke:#0180c7;stroke-width:2.5;stroke-miterlimit:10}</style><title/><path class="st0" d="M82.6 380.9c-1.8-.8-3.1-1.7-1-3.5 1.3-1 2.7-1.9 4.1-2.8 13.1-8.5 25.4-17.8 33.5-31.5 6.8-11.4 5.7-18.1-2.8-26.5C69 269.2 48.2 212.5 58.6 145.5 64.5 107.7 81.8 75 107 46.6c15.2-17.2 33.3-31.1 53.1-42.7 1.2-.7 2.9-.9 3.1-2.7-.4-1-1.1-.7-1.7-.7-33.7 0-67.4-.7-101 .2C28.3 1.7.5 26.6.6 62.3c.2 104.3 0 208.6 0 313 0 32.4 24.7 59.5 57 60.7 27.3 1.1 54.6.2 82 .1 2 .1 4 .2 6 .2H290c36 0 72 .2 108 0 33.4 0 60.5-27 60.5-60.3v-.6-58.5c0-1.4.5-2.9-.4-4.4-1.8.1-2.5 1.6-3.5 2.6-19.4 19.5-42.3 35.2-67.4 46.3-61.5 27.1-124.1 29-187.6 7.2-5.5-2-11.5-2.2-17.2-.8-8.4 2.1-16.7 4.6-25 7.1-24.4 7.6-49.3 11-74.8 6zm72.5-168.5c1.7-2.2 2.6-3.5 3.6-4.8 13.1-16.6 26.2-33.2 39.3-49.9 3.8-4.8 7.6-9.7 10-15.5 2.8-6.6-.2-12.8-7-15.2-3-.9-6.2-1.3-9.4-1.1-17.8-.1-35.7-.1-53.5 0-2.5 0-5 .3-7.4.9-5.6 1.4-9 7.1-7.6 12.8 1 3.8 4 6.8 7.8 7.7 2.4.6 4.9.9 7.4.8 10.8.1 21.7 0 32.5.1 1.2 0 2.7-.8 3.6 1-.9 1.2-1.8 2.4-2.7 3.5-15.5 19.6-30.9 39.3-46.4 58.9-3.8 4.9-5.8 10.3-3 16.3s8.5 7.1 14.3 7.5c4.6.3 9.3.1 14 .1 16.2 0 32.3.1 48.5-.1 8.6-.1 13.2-5.3 12.3-13.3-.7-6.3-5-9.6-13-9.7-14.1-.1-28.2 0-43.3 0zm116-52.6c-12.5-10.9-26.3-11.6-39.8-3.6-16.4 9.6-22.4 25.3-20.4 43.5 1.9 17 9.3 30.9 27.1 36.6 11.1 3.6 21.4 2.3 30.5-5.1 2.4-1.9 3.1-1.5 4.8.6 3.3 4.2 9 5.8 14 3.9 5-1.5 8.3-6.1 8.3-11.3.1-20 .2-40 0-60-.1-8-7.6-13.1-15.4-11.5-4.3.9-6.7 3.8-9.1 6.9zm69.3 37.1c-.4 25 20.3 43.9 46.3 41.3 23.9-2.4 39.4-20.3 38.6-45.6-.8-25-19.4-42.1-44.9-41.3-23.9.7-40.8 19.9-40 45.6zm-8.8-19.9c0-15.7.1-31.3 0-47 0-8-5.1-13-12.7-12.9-7.4.1-12.3 5.1-12.4 12.8-.1 4.7 0 9.3 0 14v79.5c0 6.2 3.8 11.6 8.8 12.9 6.9 1.9 14-2.2 15.8-9.1.3-1.2.5-2.4.4-3.7.2-15.5.1-31 .1-46.5z"/><path class="st1" d="M139.5 436.2c-27.3 0-54.7.9-82-.1-32.3-1.3-57-28.4-57-60.7 0-104.3.2-208.6 0-313C.5 26.7 28.4 1.8 60.5.9c33.6-.9 67.3-.2 101-.2.6 0 1.4-.3 1.7.7-.2 1.8-2 2-3.1 2.7-19.8 11.6-37.9 25.5-53.1 42.7-25.1 28.4-42.5 61-48.4 98.9-10.4 66.9 10.5 123.7 57.8 171.1 8.4 8.5 9.5 15.1 2.8 26.5-8.1 13.7-20.4 23-33.5 31.5-1.4.8-2.8 1.8-4.2 2.7-2.1 1.8-.8 2.7 1 3.5.4.9.9 1.7 1.5 2.5 11.5 10.2 22.4 21.1 33.7 31.5 5.3 4.9 10.6 10 15.7 15.1 2.1 1.9 5.6 2.5 6.1 6.1z"/><path class="st2" d="M139.5 436.2c-.5-3.5-4-4.1-6.1-6.2-5.1-5.2-10.4-10.2-15.7-15.1-11.3-10.4-22.2-21.3-33.7-31.5-.6-.8-1.1-1.6-1.5-2.5 25.5 5 50.4 1.6 74.9-5.9 8.3-2.5 16.6-5 25-7.1 5.7-1.5 11.7-1.2 17.2.8 63.4 21.8 126 19.8 187.6-7.2 25.1-11.1 48-26.7 67.4-46.2 1-1 1.7-2.5 3.5-2.6.9 1.4.4 2.9.4 4.4v58.5c.2 33.4-26.6 60.6-60 60.9h-.5c-36 .2-72 0-108 0H145.5c-2-.2-4-.3-6-.3z"/><path class="st1" d="M155.1 212.4c15.1 0 29.3-.1 43.4 0 7.9.1 12.2 3.4 13 9.7.9 7.9-3.7 13.2-12.3 13.3-16.2.2-32.3.1-48.5.1-4.7 0-9.3.2-14-.1-5.8-.3-11.5-1.5-14.3-7.5s-.8-11.4 3-16.3c15.4-19.6 30.9-39.3 46.4-58.9.9-1.2 1.8-2.4 2.7-3.5-1-1.7-2.4-.9-3.6-1-10.8-.1-21.7 0-32.5-.1-2.5 0-5-.3-7.4-.8-5.7-1.3-9.2-7-7.9-12.6.9-3.8 3.9-6.9 7.7-7.8 2.4-.6 4.9-.9 7.4-.9 17.8-.1 35.7-.1 53.5 0 3.2-.1 6.3.3 9.4 1.1 6.8 2.3 9.7 8.6 7 15.2-2.4 5.7-6.2 10.6-10 15.5-13.1 16.7-26.2 33.3-39.3 49.8-1.1 1.3-2.1 2.6-3.7 4.8z"/><path class="st1" d="M271.1 159.8c2.4-3.1 4.9-6 9-6.8 7.9-1.6 15.3 3.5 15.4 11.5.3 20 .2 40 0 60 0 5.2-3.4 9.8-8.3 11.3-5 1.9-10.7.4-14-3.9-1.7-2.1-2.4-2.5-4.8-.6-9.1 7.4-19.4 8.7-30.5 5.1-17.8-5.8-25.1-19.7-27.1-36.6-2.1-18.3 4-33.9 20.4-43.5 13.6-8.1 27.4-7.4 39.9 3.5zm-35.4 36.5c.2 4.4 1.6 8.6 4.2 12.1 5.4 7.2 15.7 8.7 23 3.3 1.2-.9 2.3-2 3.3-3.3 5.6-7.6 5.6-20.1 0-27.7-2.8-3.9-7.2-6.2-11.9-6.3-11-.7-18.7 7.8-18.6 21.9zM340.4 196.9c-.8-25.7 16.1-44.9 40.1-45.6 25.5-.8 44.1 16.3 44.9 41.3.8 25.3-14.7 43.2-38.6 45.6-26.1 2.6-46.8-16.3-46.4-41.3zm25.1-2.4c-.2 5 1.3 9.9 4.3 14 5.5 7.2 15.8 8.6 23 3 1.1-.8 2-1.8 2.9-2.8 5.8-7.6 5.8-20.4.1-28-2.8-3.8-7.2-6.2-11.9-6.3-10.8-.6-18.4 7.6-18.4 20.1zM331.6 177c0 15.5.1 31 0 46.5.1 7.1-5.5 13-12.6 13.2-1.2 0-2.5-.1-3.7-.4-5-1.3-8.8-6.6-8.8-12.9v-79.5c0-4.7-.1-9.3 0-14 .1-7.7 5-12.7 12.4-12.7 7.6-.1 12.7 4.9 12.7 12.9.1 15.6 0 31.3 0 46.9z"/><path class="st0" d="M235.7 196.3c-.1-14.1 7.6-22.6 18.5-22 4.7.2 9.1 2.5 11.9 6.4 5.6 7.5 5.6 20.1 0 27.7-5.4 7.2-15.7 8.7-23 3.3-1.2-.9-2.3-2-3.3-3.3-2.5-3.5-3.9-7.7-4.1-12.1zM365.5 194.5c0-12.4 7.6-20.7 18.4-20.1 4.7.1 9.1 2.5 11.9 6.3 5.7 7.6 5.7 20.5-.1 28-5.6 7.1-16 8.3-23.1 2.7-1.1-.8-2-1.8-2.8-2.9-3-4.1-4.4-9-4.3-14z"/><path class="st3" d="M66 1h328.1c35.9 0 65 29.1 65 65v303c0 35.9-29.1 65-65 65H66c-35.9 0-65-29.1-65-65V66C1 30.1 30.1 1 66 1z"/></svg>
				</button>
				<dialog id="dialog-send-zalo" class="dialog-confirm-send-zalo">
					<h2 class="title mb-1">Gửi tin nhắn theo mẫu Zalo OA</h2>
					<h6 class="title-zalo-oa-name mb-2">Travelpass tìm chuyến bay</h6>
					<form method="dialog">
						<input type="hidden" name="zalo_flight_type" id="zalo_flight_type" value="' . $this->bean->flight_type . '" />
						<input type="hidden" name="zalo_journeys" id="zalo_journeys" value="' . base64_encode(rawurlencode(json_encode($journeys_info, JSON_UNESCAPED_UNICODE))) . '" />
						<input type="hidden" name="zalo_passenger" id="zalo_passenger" value="' . $pass_and_bag['passenger'] . '" />
						<input type="hidden" name="zalo_baggage" id="zalo_baggage" value="' . $pass_and_bag['baggage'] . '" />
						<input type="hidden" name="zalo_booking_id" id="zalo_booking_id" value="' . $this->bean->id . '" />
						<input type="hidden" name="zalo_booking_name" id="zalo_booking_name" value="' . $this->bean->name . '" />
						<input type="hidden" name="zalo_contact" id="zalo_contact" value="' . $this->bean->contact_name . '" />
						<div class="wrap-type">
							<h3 class="subtitle">Chọn mẫu tin nhắn</h3>
							<div class="wrap-radio d-flex align-items-center justify-content-between">
								<div>
									<input type="radio" class="form-check-input" id="type_journey" name="zalo_type" value="journey">
									<label for="type_journey" class="form-check-label">Tin nhắn hành trình
										<span class="me-2 text-danger" title="Đã gửi ' . $zbs_history['journey'] . ' tin">(' . $zbs_history['journey'] . ')</span>
									</label>
								</div>
								<div>
									<input type="radio" class="form-check-input" id="type_payment" name="zalo_type" value="payment">
									<label for="type_payment" class="form-check-label">Tin nhắn thanh toán
										<span class="me-2 text-danger" title="Đã gửi ' . $zbs_history['payment'] . ' tin">(' . $zbs_history['payment'] . ')</span>
									</label>
								</div>
								<div>
									<input type="radio" class="form-check-input" id="type_code" name="zalo_type" value="code">
									<label for="type_code" class="form-check-label">Tin nhắn code vé
										<span class="me-2 text-danger" title="Đã gửi ' . $zbs_history['code'] . ' tin">(' . $zbs_history['code'] . ')</span>
									</label>
								</div>
								<div>
									<input type="radio" class="form-check-input" id="type_remind-flight" name="zalo_type" value="remind-flight">
									<label for="type_remind-flight" class="form-check-label">Nhắc nhở giờ bay
										<span class="me-2 text-danger" title="Đã gửi ' . $zbs_history['remind'] . ' tin">(' . $zbs_history['remind'] . ')</span>
									</label>
								</div>
								<div>
									<input type="radio" class="form-check-input" id="type_delay" name="zalo_type" value="delay">
									<label for="type_delay" class="form-check-label">Thông báo delay
										<span class="me-2 text-danger" title="Đã gửi ' . $zbs_history['delay'] . ' tin">(' . $zbs_history['delay'] . ')</span>
									</label>
								</div>
							</div>	
						</div>
						<div class="wrap-message mt-3">
							<h3 class="subtitle" style="text-align:center">Nội dung</h3>
							<div id="zalo-message" class="zalo-message"></div>
						</div>
						<div class="row mt-2 pt-3" style="border-top: 1px solid #e0e0e0;">
							<div class="col-6 wrap-phone">
								Gửi tới: <input type="text" name="phone_zalo" id="phone_zalo" value="' . $this->bean->phone . '" style="width:120px; margin-left:10px;" />
							</div>
							<div class="col-6 wrap-button">
								<button type="button" id="confirm-send-zalo" class="btn btn-confirm me-2">Gửi</button>
								<button type="button" id="cancel-send-zalo" class="btn btn-secondary" onclick="closeDialogZaloZBS()">Hủy</button>
							</div>
						</div>
					</form>
				</dialog>
			</div>
		</div>';
		$this->ss->assign('CONTACT_PHONE', $contact_phone);
	}

	private function assignZaloInfoField()
	{
		$booking_id = $this->bean->id;
		$zalo_id = $this->bean->zalo_id ?? '';
		// Icon nút thêm/cập nhật
		$edit_icon = '<svg viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg>';

		$info_html = '';
		if (!empty($zalo_id)) {
			$zaloOA = new APIZaloOA();
			$zaloInfo = EC_Zalo_Contacts_Helper::get_zalo_user_info($zalo_id, '', true);

			if (!empty($zaloInfo)) {
				$avatar = htmlspecialchars($zaloInfo['avatar'] ?? '', ENT_QUOTES, 'UTF-8');
				$name = htmlspecialchars($zaloInfo['display_name'] ?: ($zaloInfo['user_alias'] ?? ''), ENT_QUOTES, 'UTF-8');
				$avatar_html = '<img src="' . $avatar . '" alt="Zalo avatar" class="zalo-info__avatar" onerror="this.classList.add(\'zalo-info__avatar--fallback\');this.removeAttribute(\'src\');" />';
			} else {
				$zaloOA = new APIZaloOA();
				$name = htmlspecialchars($zalo_id, ENT_QUOTES, 'UTF-8');
				$avatar_html = '<span class="zalo-info__avatar zalo-info__avatar--fallback"></span>';
			}

			$links_html = '';
			$web_chat_link = htmlspecialchars($zaloOA->get_chat_link($zalo_id), ENT_QUOTES, 'UTF-8');
			if ($web_chat_link) {
				$links_html .= '<a href="' . $web_chat_link . '" target="_blank" class="zalo-chat-link zalo-chat-link--zalo">Zalo Chat</a>';
			}
			$links_html .= "<a href='index.php?module=EC_Zalo&action=index&zalo_id=$zalo_id' target='_blank' class='zalo-chat-link zalo-chat-link--bm'>BM chat</a>";

			$info_html = <<<HTML
				<div class="zalo-info">
					$avatar_html
					<div class="zalo-info__body">
						<span class="zalo-info__name">$name</span>
						<div class="zalo-info__links">$links_html</div>
					</div>
				</div>
			HTML;
		}

		// Nút thêm/cập nhật Zalo ID -> entryBookingClass::updateFields
		$btn_label = !empty($zalo_id) ? 'Cập nhật Zalo ID' : 'Thêm Zalo ID';
		$zalo_id_attr = htmlspecialchars($zalo_id, ENT_QUOTES, 'UTF-8');
		$action_html = '<div class="zalo-info__action">
			<button type="button" class="zalo-info__edit-btn p-2" id="btn_update_zalo_id" booking_id="' . $booking_id . '" title="' . $btn_label . '" aria-label="' . $btn_label . '">' . $edit_icon . '</button>
			<div id="dialog_update_zalo_id" title="Liên kết Zalo" style="display:none;">
				<div class="form-group">
					<input type="text" id="input_zalo_id" class="form-control" value="' . $zalo_id_attr . '" placeholder="https://oa.zalo.me/chat?uid=..." />
				</div>
			</div>
		</div>';

		$wrap_class = 'zalo-info-wrap' . (!empty($info_html) ? ' zalo-info-wrap--has-card' : '');
		$this->ss->assign('CUSTOM_ZALO_INFO', '<div class="' . $wrap_class . '">' . $action_html . $info_html . '</div>');
	}

	private function assignPaidFlagField()
	{
		global $app_list_strings;

		// Check is paid - is agent (Là đại lý)
		if (!$this->checkIsPaidNote()) {
			// không đổi trạng thái nếu đã qua tình trạng xác nhận
			if (array_search($this->bean->booking_status, array_keys($app_list_strings['booking_status_list'])) < array_search(3, array_keys($app_list_strings['booking_status_list']))) {
				$bk_stt = '<input type="hidden" name="booking_status" value="3" />';
			} else
				$bk_stt = '';

			$is_paid = '</form>
			<form name="frmCheckIsPaid" id="frmCheckIsPaid" action="index.php" method="post" class="frmBookingStatus d-flex align-items-center gap-1" onsubmit="return checkIsCreatedRV()">
			  	<input type="hidden" name="module" value="' . $this->bean->module_dir . '" />
			  	<input type="hidden" name="action" value="Save" />
			  	<input type="hidden" name="record" value="' . $this->bean->id . '" />
			  	<input type="hidden" name="record_name" value="' . $this->bean->name . '" />
			  	<input type="hidden" name="is_paid" value="1" />
			  	<input type="hidden" name="contact_name" value="' . $this->bean->contact_name . '" />
			  	<input type="hidden" name="total_amount" value="' . $this->bean->total_amount . '" />
			  	<input type="hidden" name="total_qty" value="' . $this->bean->total_qty . '" />
			  	' . $bk_stt;
			$is_paid .= '<input type="submit" class="btn btn-primary-2 cursor-pointer" name="btnCheckIsPaid" id="btnCheckIsPaid" value="Đã thanh toán" title="Đã thanh toán" />';
			$is_paid .= '</form>';
		} else {
			$is_paid = '<input type="checkbox" disabled checked />';
		}
		if (!empty($this->bean->delivery_man)) $is_paid .= '- Giao vé: ' . $this->bean->delivery_man;
		$this->ss->assign('IS_PAID', $is_paid);
	}

	private function assignWorkingProcessActionFields()
	{
		// Recheck status
		$recheck_count = myGetWorkingProcessCount($this->bean->module_dir, $this->bean->id, 'recheck');
		$recheck_status = '</form>
		<form class="frmBookingStatus d-flex gap-1 align-items-center" action="index.php" method="post" name="frmRecheckStatus" id="frmRecheckStatus">
		  <input type="hidden" name="module" value="' . $this->bean->module_dir . '" />
		  <input type="hidden" name="action" value="Save" />
		  <input type="hidden" name="record" value="' . $this->bean->id . '" />
		  <input type="hidden" name="record_name" value="' . $this->bean->name . '" />
		  <input type="hidden" name="recheck_status" value="2" />
		  <input type="submit" class="btn btn-primary-2 cursor-pointer" name="btnRecheckStatus" id="btnRecheckStatus" value="Recheck (' . $recheck_count . ')" title="Recheck (' . $recheck_count . ')" />
		</form>';

		// Recall status
		$recall_count = myGetWorkingProcessCount($this->bean->module_dir, $this->bean->id, 'recall');
		$recall_status = '<div class="btn btn-primary-2 btn-calling--wrap position-relative">
			<a href="#" class="recall-link collapsed">Recall (' . $recall_count . ')</a>
			<div class="collapse box-list--calling">
				<ul class="d-flex align-items-center gap-2 flex-column"> 
					<li class="box-recall box-recall-phone"> 
						<button type="button" id="btnRecall" class="btn btn-primary-2 btn-voiceip-calling btn-voiceip-calling-teco" booking_id="' . $this->bean->id . '" booking_name="' . $this->bean->name . '" phone="' . $this->bean->phone . '">
							Gọi
						</button>
					</li>
					<li class="box-recall box-recall-phone"> 
						<button type="button" id="btnRecall" class="btn btn-primary-2 btn-voiceip-calling btn-voiceip-calling-zalo" booking_id="' . $this->bean->id . '" booking_name="' . $this->bean->name . '" phone="' . $this->bean->phone . '">
							Gọi Zalo
						</button>
					</li>
				</ul>
			</div>
		</div>';

		// Check debt
		$check_debt = '';
		$check_debt_count = myGetWorkingProcessCount($this->bean->module_dir, $this->bean->id, 'check_debt');
		if (ACLController::checkAccess('EC_Payment_Voucher', 'edit', true)) {
			$check_debt .= '</form>
			<form class="frmBookingStatus" action="index.php" method="post" name="frmCheckDebt" id="frmCheckDebt">
			  <input type="hidden" name="module" value="' . $this->bean->module_dir . '" />
			  <input type="hidden" name="action" value="Save" />
			  <input type="hidden" name="record" value="' . $this->bean->id . '" />
			  <input type="hidden" name="record_name" value="' . $this->bean->name . '" />
			  <input type="hidden" name="check_debt" value="2" />
			  <input type="submit" class="btn btn-primary-2 cursor-pointer" name="btnCheckDebt" id="btnCheckDebt" value="Đ/c công nợ (' . $check_debt_count . ')" title="Đ/c công nợ (' . $check_debt_count . ')" />
			</form>';
		}

		// Support customer
		$support = '';
		$support_count = myGetWorkingProcessCount($this->bean->module_dir, $this->bean->id, 'support');
		$support .= '</form>
		<form class="frmBookingStatus" action="index.php" method="post" name="frmSupportCustomer" id="frmSupportCustomer">
			<input type="hidden" name="module" value="' . $this->bean->module_dir . '" />
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="record" value="' . $this->bean->id . '" />
			<input type="hidden" name="record_name" value="' . $this->bean->name . '" />
			<input type="hidden" name="support_customer" value="2" />
			<input type="hidden" name="booking_status" value="' . $this->bean->booking_status . '" />
			<input type="submit" class="btn btn-primary-2 cursor-pointer" name="btnSupportCustomer" id="btnSupportCustomer" value="Hỗ trợ KH (' . $support_count . ')" title="Hỗ trợ KH (' . $support_count . ')" />
		</form>';
		$this->ss->assign('RECHECK_STATUS', $recheck_status . $recall_status . $check_debt . $support);
	}

	private function assignInvoiceExportFields()
	{
		// Is invoice export
		$is_invoice_export_title = !$this->bean->is_invoice_export ? 'Xuất thêm HĐ' : 'Đã xuất HĐ ra';
		$is_invoice_export = '</form>
		<form class="frmBookingStatus d-flex gap-1 align-items-center" action="index.php" method="post" name="frmCheckInvoiceExport" id="frmCheckInvoiceExport">
		  <input type="hidden" name="module" value="' . $this->bean->module_dir . '" />
		  <input type="hidden" name="action" value="Save" />
		  <input type="hidden" name="record" value="' . $this->bean->id . '" />
		  <input type="hidden" name="record_name" value="' . $this->bean->name . '" />
		  <input type="hidden" name="booking_status" value="' . $this->bean->booking_status . '" />
		  <input type="hidden" name="is_invoice_export" value="1" />
		  <span class="w-50">HĐ đầu ra: </span>
		  <span class="d-flex align-items-center gap-2 flex-fill">
		  	<input type="checkbox" disabled ' . ($this->bean->is_invoice_export ? 'checked' : '') . ' />
		  	' . ((ACLController::checkAccess('EC_Payment_Voucher', 'edit', true) && $this->bean->booking_status == '8') ? '<input type="submit" name="btnCheckInvoiceExport" id="btnCheckInvoiceExport" class="btn btn-primary-2 cursor-pointer" value="' . $is_invoice_export_title . '" title="' . $is_invoice_export_title . '" />' : '') . '
		  </span>
		</form>';

		// Is invoice input export
		$is_invoice_input_export_title = $this->bean->is_invoice_input_export ? 'Chưa xuất HĐ vào' : 'Đã xuất HĐ vào';
		$is_invoice_input_export = '</form>
			<form class="frmBookingStatus d-flex gap-1 align-items-center" action="index.php" method="post" name="frmCheckInvoiceInputExport" id="frmCheckInvoiceInputExport">
			<input type="hidden" name="module" value="' . $this->bean->module_dir . '" />
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="record" value="' . $this->bean->id . '" />
			<input type="hidden" name="record_name" value="' . $this->bean->name . '" />
			<input type="hidden" name="booking_status" value="' . $this->bean->booking_status . '" />
			<input type="hidden" name="is_invoice_input_export" value="' . ($this->bean->is_invoice_input_export ? 0 : 1) . '" />
			<span class="w-50">HĐ đầu vào: </span>
			<span class="d-flex align-items-center gap-2 flex-fill">
			<input type="checkbox" disabled="disabled" ' . ($this->bean->is_invoice_input_export ? 'checked' : '') . ' />
			' . ((ACLController::checkAccess('EC_Payment_Voucher', 'edit', true) && $this->bean->booking_status == '8') ? '<input type="submit" name="btnCheckInvoiceInputExport" class="btn btn-primary-2 cursor-pointer" id="btnCheckInvoiceInputExport" value="' . $is_invoice_input_export_title . '" title="' . $is_invoice_input_export_title . '" />' : '') . '
			</span>
		</form>';

		$this->ss->assign('IS_INVOICE_EXPORT', '<div class="d-flex flex-column gap-1">' . $is_invoice_export . $is_invoice_input_export . '</div>');
	}

	private function assignOnlinePaymentFields()
	{
		// Online payment
		$onlinePaymentLink = EC_Flight_Bookings_Helper::get_online_payment_link($this->bean->id, $this->bean->created_by);
		$nganluong_code = <<<HTML
			<div class="nganluong__wrap">
				<button class="flex-fill d-flex align-items-center justify-content-center gap-1" id="get_qr_code">
					<svg width="20px" height="20px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M9 6.6V8.4C9 8.73137 8.73137 9 8.4 9H6.6C6.26863 9 6 8.73137 6 8.4V6.6C6 6.26863 6.26863 6 6.6 6H8.4C8.73137 6 9 6.26863 9 6.6Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6 12H9" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 12V15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 18H15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 12.0111L12.01 12" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 12.0111L18.01 12" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 15.0111L12.01 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 15.0111L18.01 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 18.0111L18.01 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 9.01111L12.01 9" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 6.01111L12.01 6" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9 15.6V17.4C9 17.7314 8.73137 18 8.4 18H6.6C6.26863 18 6 17.7314 6 17.4V15.6C6 15.2686 6.26863 15 6.6 15H8.4C8.73137 15 9 15.2686 9 15.6Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 6.6V8.4C18 8.73137 17.7314 9 17.4 9H15.6C15.2686 9 15 8.73137 15 8.4V6.6C15 6.26863 15.2686 6 15.6 6H17.4C17.7314 6 18 6.26863 18 6.6Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 3H21V6" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 21H21V18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6 3H3V6" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6 21H3V18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					<span>QR code</span>
				</button>
				<button class="flex-fill" id="copy_payment_link" onclick="copyContent('{$onlinePaymentLink}')">
					<img src="themes/SuiteP/images/modules/ec_flight_booking/onepay.svg" alt="onepay">
				</button>
				<button class="flex-fill d-flex align-items-center justify-content-center gap-1" id="get_bank" booking_id="{$this->bean->id}">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bank" viewBox="0 0 16 16">
						<path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.5.5 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89zM3.777 3h8.447L8 1zM2 6v7h1V6zm2 0v7h2.5V6zm3.5 0v7h1V6zm2 0v7H12V6zM13 6v7h1V6zm2-1V4H1v1zm-.39 9H1.39l-.25 1h13.72z"/>
					</svg>
					<span>Ngân hàng</span>
				</button>
			</div>
		HTML;
		// QR payment
		$nganluong_code .= $this->generateDialogGetQRCode($this->bean->total_amount, $this->bean->phone);
		$this->ss->assign('CUSTOM_NGANLUONG_CODE', $nganluong_code);
	}

	private function assignTransactionHistoryField()
	{
		// Transaction history
		$transBody = '';
		$nganluong_info = json_decode(html_entity_decode($this->bean->nganluong_info), true);
		$transCount = 0;
		if (is_array($nganluong_info) && isset($nganluong_info[0])) {
			$transCount = count($nganluong_info);
			foreach ($nganluong_info as $val) {
				$opdesArr = Onepay::getResponseDescription($val["vpc_TxnResponseCode"] ?? null);
				$transStatusClass = $opdesArr['code'] === '0' ? 'text-success' : 'text-danger';

				$transBody .= '<div class="history-card">
					<div class="history-line time-transaction">
						<div class="transaction-label">Thời gian</div>
						<div class="transaction-value">' . date('d/m/Y H:i:s', strtotime($val['payment_date'])) . '</div>
					</div>
					<div class="history-line code-transaction">
						<div class="transaction-label">Mã giao dịch</div>
						<div class="transaction-value">' . $val['vpc_TransactionNo'] . '</div>
					</div>
					<div class="history-line amount-transaction">
						<div class="transaction-label">Số tiền</div>
						<div class="transaction-value fw-bold">' . format_number(substr($val['vpc_Amount'], 0, -2)) . ' VND</div>
					</div>
					<div class="history-line fee-transaction">
						<div class="transaction-label">Phí giao dịch</div>
						<div class="transaction-value">Miễn phí</div>
					</div>
					<div class="history-line desc-transaction">
						<div class="transaction-label">Nội dung</div>
						<div class="transaction-value">' . ($val['payment_note'] ?? '') . '</div>
					</div>
					<div class="history-line status-transaction">
						<div class="transaction-label">Trạng thái</div>
						<div class="transaction-value ' . $transStatusClass . '">' . ($opdesArr['description']['vi'] ?? 'Chưa xác định') . '</div>
					</div>
				</div>';
			}
		} else {
			$transBody = '<p><i>Booking chưa có giao dịch thanh toán nào!</i></p>';
		}
		$transactionHistory = <<<HTML
			<button type="button" class="history-transaction d-flex align-items-center gap-2 cursor-pointer btn btn-primary-2" data-bs-toggle="modal" data-bs-target="#history-transaction">
				<p class="title-history">Thanh toán online ({$transCount})</p>
			</button>
			<div class="modal fade" id="history-transaction" tabindex="-1" aria-labelledby="history-transactionLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header">
							<h2 class="modal-title fs-5" id="history-transactionLabel">Lịch sử giao dịch Onepay</h2>
						</div>
						<div class="modal-body d-flex align-items-center gap-3 justify-content-center flex-column">
							$transBody
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
						</div>
					</div>
				</div>
			</div>
		HTML;
		$this->ss->assign('CUSTOM_TRANSACTION_HISTORY', $transactionHistory);
	}

	private function assignDiscountAmountField()
	{
		$discount_html = '<div class="discount-summary"><span class="discount_value">' . format_number($this->bean->discount_amount) . '</span>';
		$vouchers = $this->getVoucherApplied();
		$appliedVouchersData = base64_encode(rawurlencode(json_encode($vouchers, JSON_UNESCAPED_UNICODE)));

		if (!empty($vouchers)) {
			$discount_html .= '<div class="wrap-voucher">';
			foreach ($vouchers as $v) {
				$voucherId = htmlspecialchars((string)$v['voucher_id'], ENT_QUOTES, 'UTF-8');
				$voucherCode = htmlspecialchars((string)$v['code'], ENT_QUOTES, 'UTF-8');
				$voucherType = htmlspecialchars((string)$v['type'], ENT_QUOTES, 'UTF-8');
				$campaignName = htmlspecialchars((string)$v['campaign_name'], ENT_QUOTES, 'UTF-8');
				$discountAmount = format_number($v['discount_amount']);
				$invalidReason = htmlspecialchars((string)$v['applied_invalid_reason'], ENT_QUOTES, 'UTF-8');
				$invalidBadge = $invalidReason !== '' ? '<span class="voucher-invalid-badge" title="' . $invalidReason . '">!</span>' : '';
				$discount_html .= '<a class="' . $voucherType . '-voucher voucher" for="dialog_voucher_detail_' . $voucherId . '" title="Xem chi tiết">
					<span class="code">' . $voucherCode . '</span>
					' . $invalidBadge . '
				</a>
				<dialog id="dialog_voucher_detail_' . $voucherId . '" class="dialog dialog-voucher-detail" style="display:none; border-radius:0">
					<ul class="voucher-list-items">
						<li class="voucher-item">
							<span class="label">Sự kiện/Chiến dịch:</span>
							<span class="value">' . $campaignName . '</span>
						</li>
						<li class="voucher-item voucher-item-code">
							<span class="label">Mã giảm giá:</span>
							<a class="value" href="index.php?module=EC_Vouchers&action=DetailView&record=' . $voucherId . '" target="_blank">
								<span class="me-1">' . $voucherCode . '</span>
								<svg width="14px" height="14px" viewBox="0 0 24 24" stroke-width="2.3" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000333">
									<path d="M21 3L15 3M21 3L12 12M21 3V9" stroke="#000333" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"></path>
									<path d="M21 13V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V5C3 3.89543 3.89543 3 5 3H11" stroke="#000333" stroke-width="2.3" stroke-linecap="round"></path>
								</svg>
							</a>
						</li>
						<li class="voucher-item voucher-item-discount-amount">
							<span class="label">Số tiền được giảm:</span>
							<span class="value">' . $discountAmount . ' VND</span>
						</li>'
					. ($invalidReason !== '' ? '<li class="voucher-item voucher-item-invalid">
							<span class="label">Cảnh báo:</span>
							<span class="value">' . $invalidReason . '</span>
						</li>' : '') . '
					</ul>
				</dialog>';
			}
			$discount_html .= '</div>';
		}
		$discount_html .= '</div>';

		if (in_array((int)$this->bean->booking_status, [1, 2, 6])) {
			$discount_html .= <<<HTML
				<div class="discount-actions">
					<div class="wrap-voucher-apply">
						<button class="discount-action-btn btn-use-voucher" type="button" for="dialog_apply_voucher" booking_id="{$this->bean->id}" title="Chọn mã giảm giá">Voucher</button>
						<dialog id="dialog_apply_voucher" class="dialog dialog-booking-voucher" style="display:none" booking_id="{$this->bean->id}" data-applied-vouchers="{$appliedVouchersData}">
							<div class="booking-voucher-list" id="apply_voucher_message">
								<p>Đang tải danh sách mã giảm giá phù hợp...</p>
							</div>
							<div class="booking-voucher-footer">
								<div>
									<p>Đã chọn <span id="booking_voucher_selected_count">0</span> mã</p>
									<strong>Giảm giá: <span id="booking_voucher_selected_amount">0đ</span></strong>
								</div>
								<div class="booking-voucher-actions">
									<button type="button" class="btn btn-secondary" id="btn_cancel_booking_voucher">Trở lại</button>
									<button type="button" class="btn btn-primary" id="btn_apply_booking_voucher" booking_id="{$this->bean->id}">Xác nhận</button>
								</div>
							</div>
						</dialog>
					</div>
			HTML;

			$points = (int)($this->bean->db->getOne("SELECT points FROM contacts WHERE id = '{$this->bean->contact_id}' AND deleted = 0") ?? 0);
			if ($points && $points > 0) {
				$max_point = (int) ($points / $this->bean->point_step) * $this->bean->point_step;
				$discount_html .= <<<HTML
					<div class="wrap-points">
						<button class="discount-action-btn btn-use-point" type="button" for="dialog_use_point" title="Dùng điểm tích lũy">Dùng điểm</button>
						<dialog id="dialog_use_point" class="dialog dialog-use-point" style="display:none">
							<div class="content">
								<div class="point">
									<label for="point_of_use">Nhập điểm áp dụng:</label>
									<div class="input-group">
										<input type="number" name="point_of_use" id="point_of_use" class="form-control allow-number-only" min="{$this->bean->point_step}" max="$max_point" step="{$this->bean->point_step}" />
										<span class="input-group-text">/<b class="tt_points" id="tt_points" data="$points">$points điểm</b></span>
									</div>
								</div>
								<div class="equal">=</div>
								<div class="amount">
									<label for="points_discount">Tổng tiền giảm:</label>
									<div class="input-group">
										<input type="text" name="points_discount" id="points_discount" value="0" class="form-control points_discount allow-number-only" readonly="true"/>
										<span class="input-group-text">đ</span>
									</div>
								</div>
							</div>
							<div class="description">
								<p>Các mốc điểm được sử dụng: 50, 100, 150, 200, 250,...</p>
							</div>
							<button id="btn_apply_points_discount" class="btn btn btn-primary btn-apply-points-discount" contact_id="{$this->bean->contact_id}" booking_id="{$this->bean->id}" disabled="true">Áp dụng</button>
						</dialog>
					</div>
				HTML;
			}
			$discount_html .= '</div>';
		}
		$this->ss->assign('CUS_DISCOUNT_AMOUNT', $discount_html);
	}

	private function assignProfitField()
	{
		global $current_user;

		// Doanh số
		$bk_amt = calculateBKAmt($this->bean->id);
		$total_profit = format_number($bk_amt['total_profit'] ?? 0);
		if (is_admin($current_user)) {
			$total_profit .= '<button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#profitBookingModal">Chi tiết D/số</button>';
			if ($current_user->user_name == 'hungnh') {
				$total_profit .= '<input id="update_revenue" class="btn btn-primary btn-sm ms-2" type="button" value="Cập nhật DS">';
			}

			$total_profit .= '<div class="modal fade" id="profitBookingModal" tabindex="-1" aria-labelledby="profitBookingModalLabel" aria-hidden="true">
								<div class="modal-dialog modal-dialog-centered">
									<div class="modal-content">
										<div class="modal-body">
											<h3 class="sub-title text-center">Chi tiết doanh số booking <span>' . $this->bean->name . '</span></h3>
											<div class="row">
												<div class="col-6">
													<span class="form-label fw-semibold">Tổng tiền BK:</span>
												</div>
												<div class="col-6">
													<p class="form-label fw-semibold text-end">' . format_number($bk_amt['total_amount_booking'] ?? 0) . '</p>
												</div>
											</div>
											<div class="row">
												<div class="col-6">
													<span class="form-label fw-semibold" title="Giá bán Đổi giờ bay, hành trình, tên khách, phí mua hành lý, mua ghế">Tổng tiền phiếu thu:</span>
												</div>
												<div class="col-6">
													<p class="form-label fw-semibold text-end">' . format_number($bk_amt['total_amount_receipt'] ?? 0) . '</p>
												</div>
											</div>
											<div class="row">
												<div class="col-6">
													<span class="form-label fw-semibold" title="Tiền giảm giá sử dụng điểm tích lũy">Tiền sử dụng điểm:</span>
												</div>
												<div class="col-6">
													<p class="form-label fw-semibold text-end">' . format_number($bk_amt['total_amount_points'] ?? 0) . '</p>
												</div>
											</div>
											<div class="row">
												<div class="col-6">
													<span class="form-label fw-semibold" title="Khoản tiền hãng hoàn lại khi hoàn vé">Tiền hãng hoàn:</span>
												</div>
												<div class="col-6">
													<p class="form-label fw-semibold text-end">' . format_number($bk_amt['total_amount_brand_refunded'] ?? 0) . '</p>
												</div>
											</div>
											<div class="row">
												<div class="col-6">
													<span class="form-label fw-semibold">Tổng tiền bán:</span>
												</div>
												<div class="col-6">
													<p class="form-label fw-semibold text-end text-danger">' . format_number($bk_amt['total_amount'] ?? 0) . '</p>
												</div>
											</div>
											<hr>
											<div class="row">
												<div class="col-6">
													<span class="form-label fw-semibold">Tổng tiền mua BK:</span>
												</div>
												<div class="col-6">
													<p class="form-label fw-semibold text-end">' . format_number($bk_amt['total_purchase_booking'] ?? 0) . '</p>
												</div>
											</div>
											<div class="row">
												<div class="col-6">
													<span class="form-label fw-semibold" title="Số tiền phải hoàn trả cho khách hàng">Tiền hoàn khách:</span>
												</div>
												<div class="col-6">
													<p class="form-label fw-semibold text-end">' . format_number($bk_amt['total_purchase_pass_refunded'] ?? 0) . '</p>
												</div>
											</div>
											<div class="row">
												<div class="col-6">
													<span class="form-label fw-semibold" title="Khách sử dụng điểm tích lũy để giảm giá cho BK. Sau đó đổi ý không dùng nữa!">Tiền sử dụng điểm hoàn lại:</span>
												</div>
												<div class="col-6">
													<p class="form-label fw-semibold text-end">' . format_number($bk_amt['total_purchase_points_refunded'] ?? 0) . '</p>
												</div>
											</div>
											<div class="row">
												<div class="col-6">
													<span class="form-label fw-semibold" title="Giá mua Đổi giờ bay, hành trình, tên khách, phí mua hành lý, mua ghế">Tổng tiền mua phiếu thu:</span>
												</div>
												<div class="col-6">
													<p class="form-label fw-semibold text-end">' . format_number($bk_amt['total_purchase_receipt'] ?? 0) . '</p>
												</div>
											</div>
											<div class="row">
												<div class="col-6">
													<span class="form-label fw-semibold" title="">Tổng tiền mua:</span>
												</div>
												<div class="col-6">
													<p class="form-label fw-semibold text-end text-danger">' . format_number($bk_amt['total_purchase'] ?? 0) . '</p>
												</div>
											</div>
											<hr>
											<div class="row">
												<div class="col-6">
													<span class="form-label fw-bold" title="">Tổng doanh số:</span>
												</div>
												<div class="col-6">
													<p class="form-label fw-bold text-end text-danger">' . format_number($bk_amt['total_profit'] ?? 0) . '</p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>';
		}
		$this->ss->assign('TOTAL_PROFIT', $total_profit);
	}

	// HELPERS
	// ---------------------------
	private function getVoucherApplied($booking_id = '')
	{
		if (!$booking_id || empty($booking_id))
			$booking_id = $this->bean->id;

		$sql = "SELECT 
					bv.booking_id,
					bv.voucher_id,
					v.name AS code,
					v.type,
					v.status,
					v.campaign_name,
					v.end_time,
					v.reduce_amount,
					v.reduce_percent,
					v.max_discount,
					v.condition_voucher,
					bv.discount_amount
				FROM bookings_vouchers bv
					LEFT JOIN ec_vouchers v ON v.id = bv.voucher_id
				WHERE bv.booking_id = '$booking_id'
					AND bv.deleted = 0";

		$res = $this->bean->db->query($sql);
		$results = [];
		$bookingPhone = preg_replace('/\D/', '', (string)$this->bean->phone);
		if (strpos($bookingPhone, '0084') === 0) {
			$bookingPhone = '0' . substr($bookingPhone, 4);
		} elseif (strpos($bookingPhone, '84') === 0 && strlen($bookingPhone) >= 11) {
			$bookingPhone = '0' . substr($bookingPhone, 2);
		} elseif (strlen($bookingPhone) === 9) {
			$bookingPhone = '0' . $bookingPhone;
		}
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$row['reduce_amount'] = (int)$row['reduce_amount'];
			$row['reduce_percent'] = (int)$row['reduce_percent'];
			$row['max_discount'] = (int)$row['max_discount'];
			$row['discount_type'] = (int)$row['reduce_percent'] > 0 ? 'percent' : 'amount';
			$row['condition_voucher'] = json_decode(html_entity_decode(trim((string)$row['condition_voucher'])), true) ?: [];
			$row['selectable'] = true;
			$voucherPhone = empty($row['condition_voucher']['for_phone_value'])
				? ''
				: preg_replace('/\D/', '', (string)$row['condition_voucher']['for_phone_value']);
			if (strpos($voucherPhone, '0084') === 0) {
				$voucherPhone = '0' . substr($voucherPhone, 4);
			} elseif (strpos($voucherPhone, '84') === 0 && strlen($voucherPhone) >= 11) {
				$voucherPhone = '0' . substr($voucherPhone, 2);
			} elseif (strlen($voucherPhone) === 9) {
				$voucherPhone = '0' . $voucherPhone;
			}
			$row['applied_invalid_reason'] = ($voucherPhone !== '' && $bookingPhone !== '' && $voucherPhone !== $bookingPhone)
				? 'Không còn hợp lệ do đổi SĐT'
				: '';
			$results[] = $row;
		}
		return $results;
	}

	private function checkIsPaidNote()
	{
		$sql = "SELECT COUNT(id)
				FROM ec_working_process
				WHERE parent_id = '{$this->bean->id}' AND paid = 1 AND deleted = 0";
		$res = $this->bean->db->getOne($sql);
		return (int)$res > 0;
	}
}
