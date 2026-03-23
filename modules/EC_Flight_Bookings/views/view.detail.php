<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('modules/EC_Messages/SMS.php');

class EC_Flight_BookingsViewDetail extends ViewDetail
{
	public $bean;
	private $_outbound_airline = '';
	private $_inbound_airline = '';
	private $_outbound_ticket_class = '';
	private $_inbound_ticket_class = '';
	private $_is_had_rv = 0;
	private $editing_rights = false;

	function display()
	{
		global $current_user;
		$deparment_info = myGetDepartmentInfo($current_user->department_id);
		$this->editing_rights = ACLController::checkAccess('EC_Flight_Bookings', 'edit', true);

		$this->displayCSS();
		$this->populateCustomButtons($deparment_info);
		$this->populateCustomFields();
		$this->populateLineNotesMessage();
		$this->populateLineDetails();
		$this->populateSMSTemplate();

		// Thông tin hành trình ban đầu
		$html = $this->populateLineItineraries($deparment_info);
		$this->ss->assign('LINE_ITINERARIES', $html);

		// Thông tin hành khách ban đầu (Chưa đổi)
		$this->ss->assign('LINE_PASSENGERS', $this->populateLinePassengers());

		// Danh sách phiếu thu
		$this->ss->assign('LINE_RELATE_VOUCHER', $this->populateLineRelateVoucher());

		$this->createModal(); // Modal for confirm action

		if ($current_user->user_name == 'hungnh') {
			// Cập nhật doanh số theo ngày
			// $this->saveRevenueBookingJob();

			// Cập nhật data doanh số cho table ec_revenue
			// $this->exc_dataRevenue();

			// Quét và cập nhật booking tham khảo - cận
			// $this->rebuildBookingFlagsByYear(2026);
		}

		parent::display();
		$this->displayJS();
	}

	/**
	 * Cập nhật doanh số trong ngày vào ec_revenue
	 */
	private function saveRevenueBookingJob()
	{

		$from = date('Y-m-d 00:00:00');
		$to   = date('Y-m-d 23:59:59');

		$sql = "SELECT id 
				FROM ec_flight_bookings 
				WHERE booking_status = 8 
				AND date_entered BETWEEN '$from' AND '$to'
				AND deleted = 0";

		$res = $this->bean->db->query($sql);
		if ($this->bean->db->countRows($res) > 0) {
			while ($row = $this->bean->db->fetchByAssoc($res)) {
				saveRevenueBooking($row['id']);
			}
		}

		return true;
	}

	/**
	 * Cập nhật dữ liệu is_prior và is_reference cho BK
	 */
	private function rebuildBookingFlagsByYear(int $year)
	{
		global $db;

		// ===== 2. UPDATE VÉ CẬN =====
		$sqlPrior = "
			UPDATE ec_flight_bookings bk
			JOIN (
				SELECT
					i.booking_id,
					TIMESTAMPDIFF(
						MINUTE,
						DATE_ADD(bk.date_entered, INTERVAL 7 HOUR),
						MIN(i.departure_date)
					) AS diff_minutes
				FROM ec_flight_bookings bk
				JOIN ec_booking_itineraries i ON i.booking_id = bk.id AND i.deleted = 0
				WHERE bk.deleted = 0
				AND YEAR(bk.date_entered) = $year
				GROUP BY i.booking_id
				HAVING diff_minutes BETWEEN 1 AND 1440
			) x ON x.booking_id = bk.id
			SET bk.is_prior = 1
		";
		$db->query($sqlPrior);

		// ===== 3. UPDATE BOOKING THAM KHẢO =====
		$sqlReference = "
			UPDATE ec_flight_bookings bk
			LEFT JOIN ec_flight_bookings_audit a ON a.parent_id = bk.id
			AND a.field_name = 'contact_name'
			AND a.before_value_string = 'Tham Khao'
			SET bk.is_reference = 1
			WHERE bk.deleted = 0
			AND YEAR(bk.date_entered) = $year
			AND (
				bk.contact_name = 'Tham Khao'
				OR a.parent_id IS NOT NULL
			)
		";
		$db->query($sqlReference);

		return true;
	}

	/**
	 * Chạy theo quý tránh timeout 504
	 */
	private function exc_dataRevenue()
	{
		// Lấy tất cả danh sách các booking hoàn tất booking_status = 8 trong năm 2025
		$year = 2024;
		for ($month = 1; $month <= 12; $month++) {
			// Ngày đầu tháng
			$from = sprintf('%04d-%02d-01 00:00:00', $year, $month);

			// Ngày cuối tháng
			$lastDay = date('t', strtotime($from));
			$to = sprintf('%04d-%02d-%02d 23:59:59', $year, $month, $lastDay);
			$get_sql = "
				SELECT id
				FROM ec_flight_bookings
				WHERE booking_status = 8
				AND date_entered BETWEEN
					DATE_SUB('{$from}', INTERVAL 7 HOUR)
					AND
					DATE_SUB('{$to}', INTERVAL 7 HOUR)
				AND deleted = 0
			";
			$res = $this->bean->db->query($get_sql);
			while ($row = $this->bean->db->fetchByAssoc($res)) {
				if (!in_array($month, [1, 2, 3, 4])) continue;
				// if(!in_array($month, [5, 6, 7, 8])) continue;
				// if(!in_array($month, [9, 10, 11, 12])) continue;
				saveRevenueBooking($row['id']);
				unset($row);
			}
			unset($res);
			gc_collect_cycles();
			sleep(3);
		}
	}

	private function displayJS()
	{
		global $app_list_strings, $current_user;

		// External file
		$js = '
			<script src="modules/' . $this->bean->module_dir . '/js/view.detail.js?v=1.0.3"></script>
			<script src="modules/' . $this->bean->module_dir . '/js/autobook.js?v=1.0.2"></script>
			<script src="modules/' . $this->bean->module_dir . '/js/api_zalo.js?v=1.0.3"></script>
			<script src="modules/' . $this->bean->module_dir . '/js/api_sms.js?v=1.0.2"></script>
			<script src="modules/' . $this->bean->module_dir . '/js/doc_list.js?v=1.0.2"></script>
			<script src="modules/' . $this->bean->module_dir . '/js/print_ticket.js?v=1.0.2"></script>
		';

		$js .= '<script>
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
			const is_invoice_export = "' . $this->bean->is_invoice_export . '";
			const is_invoice_input_export = "' . $this->bean->is_invoice_input_export . '";
		</script>';

		// Phải tạo phiếu thu trước rồi mới nhấn đã thanh toán
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

		echo $js;
	}

	private function displayCSS()
	{
		echo '
			<link type="text/css" rel="stylesheet" href="./themes/SuiteP/libs/css/select2.min.css">
			<link type="text/css" rel="stylesheet" href="./modules/EC_Flight_Bookings/css/view.detail.css?v=2.0.8">
			<link type="text/css" rel="stylesheet" href="./modules/EC_Flight_Bookings/css/api_zalo.css?v=2.0">
			<link type="text/css" rel="stylesheet" href="./modules/EC_Flight_Bookings/css/autobook.css?v=1.0">
		';
	}

	// Line Note Message - Made by: DucPham at 28/09/2022
	private function populateLineNotesMessage()
	{
		global $current_user;
		$actions_kpi = [];

		// Lấy thông tin đã thanh toán từ kpi
		$sql_kpi = '
			SELECT id, description, paid, called, recheck, recall, check_debt, support, remind
			FROM ec_working_process 
			WHERE parent_id = "' . $this->bean->id . '" 
				AND deleted = 0 
				AND (paid = 1 OR called = 1 OR recheck > 0 OR recall > 0 OR remind > 0 OR check_debt > 0 OR support > 0)';

		$res_kpi = $this->bean->db->query($sql_kpi);
		while ($row_kpi = $this->bean->db->fetchByAssoc($res_kpi)) {
			if ($row_kpi['called'] == 1 || $row_kpi['paid'] == 1 || $row_kpi['recheck'] > 0 || $row_kpi['recall'] > 0 || $row_kpi['remind'] > 0 || $row_kpi['check_debt'] > 0 || $row_kpi['support'] > 0) {
				if ($row_kpi['called'] == 1)
					$action_type = 'called';
				elseif ($row_kpi['paid'] == 1)
					$action_type = 'paid';
				elseif ($row_kpi['recheck'] > 0)
					$action_type = 'recheck';
				elseif ($row_kpi['recall'] > 0)
					$action_type = 'recall';
				elseif ($row_kpi['remind'] > 0)
					$action_type = 'remind';
				elseif ($row_kpi['check_debt'] > 0)
					$action_type = 'check_debt';
				elseif ($row_kpi['support'] > 0)
					$action_type = 'support';

				$actions_kpi[$row_kpi['id']] = [
					'type' => $action_type,
					'description' => $row_kpi['description']
				];
			}
		}

		$sql = "SELECT 
				n.id AS detail_id,
				n.description,
				n.date_entered,
				n.working_process_id,
				u.user_name,
				u.id AS user_id
			FROM notes n 
				LEFT JOIN users u ON n.created_by = u.id AND u.deleted = 0
			WHERE n.parent_id = '{$this->bean->id}'
				AND n.parent_type = 'EC_Flight_Bookings' 
				AND n.deleted = 0
			ORDER BY n.date_entered";

		$res = $this->bean->db->query($sql);
		$user_list = get_user_array(true, 'Active', '', true);
		$note_username = "";
		$row_content = "";

		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$note_username = $user_list[$row['user_id']];
			$id_wprocess = $row['working_process_id'];
			$class_of_row = "row-mess";
			if ($row['user_id'] == $current_user->id)
				$class_of_row .= " row-this";

			$row_action = $icon_action = $data_more = '';
			$typemap = [
				'called' => 'Ghi chú "Đã gọi"',
				'paid' => 'Ghi chú "Đã thanh toán"',
				'recheck' => 'Recheck',
				'recall' => 'Recall',
				'remind' => 'Remind',
				'check_debt' => 'Công nợ',
				'support' => 'Hỗ trợ KH',
			];
			if (in_array($id_wprocess, array_keys($actions_kpi))) {
				$t = $actions_kpi[$id_wprocess]['type'];
				$class_of_row .= " row-" . $t;

				if (!empty($typemap[$t]))
					$row_action = '<div class="row-action">
						<span class="' . $t . '">' . $typemap[$t] . '</span>
					</div>';

				if ($t == 'called') {
					$icon_action = '<span class="icon-action">
						<svg width="16px" height="16px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#808080"><path d="M16 5h6m0 0l-3-3m3 3l-3 3M18.118 14.702L14 15.5c-2.782-1.396-4.5-3-5.5-5.5l.77-4.13L7.815 2H4.064c-1.128 0-2.016.932-1.847 2.047.42 2.783 1.66 7.83 5.283 11.453 3.805 3.805 9.286 5.456 12.302 6.113 1.165.253 2.198-.655 2.198-1.848v-3.584l-3.882-1.479z" stroke="#808080" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					</span>';
				} else if ($t == 'paid') {
					$icon_action = '<span class="icon-action">
						<svg width="16px" height="16px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#808080"><path d="M7 12.5l3 3 7-7" stroke="#808080" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" stroke="#808080" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					</span>';
				}

				$data_more = 'data-id-process="' . $id_wprocess . '" data-type-process="' . $t . '"';
			}

			$action = "";
			if (isAllowedUser()) {
				$action = '<div class="action action-remove" data-toggle="tooltip" data-placement="top" title="Xóa diễn giải" data-id-note="' . $row['detail_id'] . '" ' . $data_more . ' booking-id="' . $this->bean->id . '">
					<svg width="18px" height="18px" viewBox="0 0 24 24" stroke-width="1.76" fill="none" xmlns="http://www.w3.org/2000/svg" color="#a3a4a6">
						<path d="M20 9l-1.995 11.346A2 2 0 0116.035 22h-8.07a2 2 0 01-1.97-1.654L4 9M21 6h-5.625M3 6h5.625m0 0V4a2 2 0 012-2h2.75a2 2 0 012 2v2m-6.75 0h6.75" stroke="#a3a4a6" stroke-width="1.76" stroke-linecap="round" stroke-linejoin="round"></path>
					</svg>
				</div>';
			}

			$send_success = '';
			$row_content .= '<div class="' . $class_of_row . '">';
			$row_content .= '<div class="row-time">' . date('H:i, d/m/Y', strtotime($row['date_entered']) + 7 * 3600) . '</div>
							' . $row_action . '
							<div class="row-user">' . $icon_action . $note_username . '</div>
							<div class="row-content">' . $action . $row['description'] . $send_success . '</div>
						</div>';
		}

		$html = '<div class="menu-control__tablet-wrap" id="line-notes">
			<input type="checkbox" id="slide-menu" />
			<label for="slide-menu" class="header-slide-menu__btn btn btn-primary" id="btn-open-mobile-menu">
				<svg width="18px" height="18px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#fff" style="vertical-align:sub;margin-right:2px">
					<path d="M8 10h8M8 14h4M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12c0 1.821.487 3.53 1.338 5L2.5 21.5l4.5-.838A9.955 9.955 0 0012 22z" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
				</svg>
				<span>Diễn giải</span> 
			</label>
			<div class="mobile-menu">
				<div class="mobile-menu__top">
					<h3 class="title">Diễn giải</h3>
					<label for="slide-menu" class="wrap-cancel">
						<svg aria-hidden="true" height="24px" viewBox="0 0 24 24" width="24px"><g stroke="var(--text-primary-color)" stroke-linecap="round" stroke-width="2"><line x1="6" x2="18" y1="6" y2="18"></line><line x1="6" x2="18" y1="18" y2="6"></line></g></svg>
					</label>
				</div>
				<div class="mobile-menu-wrapper">
					<div class="message_list">
						' . $row_content . '
					</div>
					<div class="">
						<div class="wrap-input">
							<div class="wrap-text">
								<textarea rows="1" class="box-input input-note-description" id="note-description" placeholder="Thêm diễn giải..."></textarea>
							</div>
							<div class="wrap-icon">
								<input type="hidden" name="note-username" id="note-username" value="' . $note_username . '" />
								<input type="hidden" name="note-name" id="note-name" value="' . $this->bean->name . '" />
								<input type="hidden" name="note-parent-id" id="note-parent-id" value="' . $this->bean->id . '" />
								<input type="hidden" name="note-booking-status" id="note-booking-status" value="' . $this->bean->booking_status . '" />
								<input type="hidden" name="note-contact-name" id="note-contact-name" value="' . $this->bean->contact_name . '" />
								<input type="hidden" name="note-total-amount" id="note-total-amount" value="' . $this->bean->total_amount . '" />
								<input type="hidden" name="note-total-qty" id="note-total-qty" value="' . $this->bean->total_qty . '" />
								<svg xmlns="http://www.w3.org/2000/svg" id="icon-send-notes" width="20" height="20" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
									<path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>';

		$html .= '<div id="confirm_delete_message_dialog">
			<div class="content_message_delete">Bạn muốn xóa diễn giải này?</div>
			<div class="action_message_delete d-flex align-items-center justify-content-center gap-2">
				<a class="btn btn-danger cursor-pointer" data-toggle="tooltip" data-placement="top" title="Xác nhận" id="confirm_delete_message" value="default">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
						<path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
					</svg>
				</a>
				<a class="btn btn-secondary cursor-pointer" data-toggle="tooltip" data-placement="top" title="Hủy" id="cancel_delete_message" value="cancel">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
						<path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
					</svg>
				</a>
			</div>
		</div>';

		$this->ss->assign('BUTTON_LINE_NOTES', $html);
	}

	private function populateCustomFields()
	{
		global $app_list_strings, $current_user;

		// Thông tin hoá đơn
		$this->populateInvoiceInf();

		// Booking - tình trạng
		$booking_name = '<b>' . $this->bean->name . '</b> - <span class="fw-bold" style="color:' . ($app_list_strings['booking_status_color_list'][(int) $this->bean->booking_status] ?? '#333') . ';">' . ($app_list_strings['booking_status_list'][(int) $this->bean->booking_status] ?? '') . '</span>';
		$this->ss->assign('CUSTOM_NAME', $booking_name);

		// Loại vé - Chuyến bay
		$ticket_type = $app_list_strings['booking_ticket_type_list'][$this->bean->ticket_type] . ' - Chuyến bay: ' . $app_list_strings['bk_flight_type_list'][$this->bean->flight_type];
		$this->ss->assign('CUSTOM_TICKET_TYPE', $ticket_type);

		// Nguồn khách hàng
		$customer_source = '<div class="d-flex align-items-center flex-nowrap gap-3">';
		foreach ($app_list_strings['booking_customer_source_list'] as $value => $label) {
			$checked = $value == $this->bean->customer_source ? 'checked' : '';
			$customer_source .= '<div class="item" style="font-size:13px;">
				<input type="checkbox" name="customer_source" id="customer_source_' . $value . '" value="' . $value . '" ' . $checked . ' /> ' . $label . '
			</div>';
		}
		$customer_source .= '</div>';
		$this->ss->assign('CUSTOM_CUSTOMER_SOURCE', $customer_source);

		// Đánh dấu
		$list_bookmark = '<div class="d-flex align-items-star flex-nowrap gap-3">';
		// Telesale
		if ($this->bean->is_telesale && !empty($this->bean->telesale_call_id)) {
			$call_name = $this->bean->db->getOne("SELECT name FROM calls WHERE id = '{$this->bean->telesale_call_id}' AND deleted = 0");
			$list_bookmark .= '<div class="item">
				<label for="checkIsTelesale">Là BK Telesale</label>
				<input type="checkbox" name="is_telesale" id="checkIsTelesale" checked disabled />
				<br />
				<a href="index.php?module=Calls&action=DetailView&record=' . $this->bean->telesale_call_id . '" target="_blank">' . $call_name . '</a>
			</div>';
		} elseif ($this->bean->booking_status == '8') {
			$list_bookmark .= '<div class="item">
				</form><form name="frmCheckIsTelesale" id="frmCheckIsTelesale" action="index.php" method="post">
					<input type="hidden" name="module" value="' . $this->bean->module_dir . '" />
					<input type="hidden" name="action" value="Save" />
					<input type="hidden" name="record" value="' . $this->bean->id . '" />
					<input type="hidden" name="record_name" value="' . $this->bean->name . '" />
					<input type="hidden" name="is_telesale_value" value="1" />
					<label for="checkIsTelesale">Là BK Telesale</label>
					<input type="checkbox" name="is_telesale" id="checkIsTelesale" onclick="this.form.submit(); $(\'.container-waiting\').show();" />
				</form>
			</div>';
		}

		// CTV
		if ($this->bean->is_ctv) {
			$list_bookmark .= '<div class="item">
				<label for="checkIsCTV" >Là CTV</label>
				<input type="checkbox" name="is_ctv" id="checkIsCTV" checked disabled />
			</div>';
		} else if ((int)$this->bean->booking_status === 8) {
			$list_bookmark .= '<div class="item">
				</form><form name="frmCheckIsCTV" id="frmCheckIsCTV" action="index.php" method="post">
					<input type="hidden" name="module" value="' . $this->bean->module_dir . '" />
					<input type="hidden" name="action" value="Save" />
					<input type="hidden" name="record" value="' . $this->bean->id . '" />
					<input type="hidden" name="record_name" value="' . $this->bean->name . '" />
					<input type="hidden" name="is_ctv_value" value="1" />
					<label for="checkIsCTV">Là CTV</label>
					<input type="checkbox" name="is_ctv" id="checkIsCTV" onclick="this.form.submit(); $(\'.container-waiting\').show();" />
				</form>
			</div>';
		}

		// Đại lý
		if ($this->bean->is_agent && !empty($this->bean->agent_id)) {
			$list_bookmark .= '<div class="item">
				<label for="is_agent">Là đại lý</label>
				<input type="checkbox" name="is_agent" id="check_is_agent" checked disabled />
				<br />
				<a href="index.php?module=Accounts&action=DetailView&record=' . $this->bean->agent_id . '" target="_blank">
					' . $this->bean->agent_name . '
				</a>
			</div>';
		} else {
			$list_bookmark .= '<div class="item">
				<label for="check_is_agent">Là đại lý</label>
				<input type="checkbox" name="is_agent" id="check_is_agent" disabled />
			</div>';
		}

		// Đã giữ chỗ
		$list_bookmark .= '<div class="item">
				<label for="is_hold">Đã giữ chỗ</label>
				<input type="checkbox" name="is_hold" id="check_is_hold" ' . ($this->bean->is_hold ? 'checked' : '') . ' disabled />
			</div>';

		$list_bookmark .= '</div>';
		$this->ss->assign('CUSTOM_BOOKMARK', $list_bookmark);

		// Hệ thống đánh dấu
		$list_bookmark_system = '<div class="d-flex align-items-center gap-3">';
		$list_bookmark_system .= '<div class="item">
			<input type="checkbox" id="is_prior" ' . ($this->bean->is_prior ? 'checked' : '') . ' disabled /> Vé cận
		</div>';
		$list_bookmark_system .= '<div class="item">
			<input type="checkbox" id="is_mail_confirm" ' . ($this->bean->is_mail_confirm ? 'checked' : '') . ' disabled /> Gửi mail xác nhận
		</div>';
		$list_bookmark_system .= '<div class="item">
			<input type="checkbox" id="is_reference" ' . ($this->bean->is_reference ? 'checked' : '') . ' disabled /> BK tham khảo
		</div>';
		$list_bookmark_system .= '</div>';
		$this->ss->assign('CUSTOM_BOOKMARK_SYSTEM', $list_bookmark_system);

		// Đã xuất vé
		$is_ticket_exported = '<span class="is_ticket_exported d-flex align-items-center">
			<span class="w-50 d-flex gap-2 align-items-center is_ticket_exported--wrap">
				<span>Lượt đi: </span>
				<input disabled type="checkbox" id="is_ticket_exported" ' . ($this->bean->is_ticket_exported ? 'checked' : '') . ' />
			</span>
			<span class="flex-fill d-flex gap-2 align-items-center is_ticket_inbound_exported--wrap">
				<span>Lượt về: </span>
				<input disabled type="checkbox" id="is_ticket_inbound_exported" ' . ($this->bean->is_ticket_inbound_exported ? 'checked' : '') . ' />
			</span>
		</span>';
		$is_exported = $is_ticket_exported;
		$this->ss->assign('CUSTOM_IS_EXPORTED', $is_exported);

		// Date ticket issue (ngày xuất vé) - giao vé
		$ticket_issue = '<span class="is_ticket_exported d-flex align-items-center">
			<span class="w-50 d-flex gap-2 align-items-center is_ticket_exported--wrap">
				<span>Lượt đi: ' . $this->bean->date_ticket_issue . '</span>
			</span>
			<span class="flex-fill d-flex gap-2 align-items-center is_ticket_inbound_exported--wrap">
				<span>Lượt về: ' . $this->bean->date_ticket_inbound_issue . '</span>
			</span>
		</span>';
		$this->ss->assign('CUSTOM_DATE_TICKET_ISSUE', $ticket_issue);

		// Hãng bay - chiều về
		$airline = 'Chiều đi: <b style="margin-right:20px">' . $this->bean->airline . '</b> Chiều về: <b>' . $this->bean->airline_inbound . '</b>';
		$this->ss->assign('CUSTOM_AIRLINE', $airline);

		/************  CONTACT  ************/
		$contact_title = $app_list_strings['passenger_salutation_list'][(int) $this->bean->contact_title];
		$link_contact = $this->bean->contact_id ? "index.php?module=Contacts&action=DetailView&record=" . $this->bean->contact_id : "#";
		// $type_contact = classifyContact($this->bean->contact_id);
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

		// PHONE
		$journeys_info     = $this->getJourneysByBooking($this->bean->id);
		$pass_and_bag     = $this->getPassengerAndBaggage($this->bean->id);
		$zbs_history     = $this->getHistoryZBS($this->bean->phone, $this->bean->id);
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
					<h6 class="title-zalo-oa-name mb-2">Travelpass tìm chuyến bay<h6>
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
								Gửi tới: <input type="text" name="phone_zalo" id="phone_zalo" value="' . $this->bean->phone . '" style="width:120px; margin-left:10px; height:10px;"/>
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

		// Check is paid - is agent (Là đại lý)
		if (!$this->checkIsPaidNote($this->bean->id)) {
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

		// Is invoice export
		$is_invoice_export_title = $this->bean->is_invoice_export ? 'Xuất thêm HĐ' : 'Đã xuất HĐ ra';
		$is_invoice_export = '</form>
		<form class="frmBookingStatus d-flex gap-1 align-items-center" action="index.php" method="post" name="frmCheckInvoiceExport" id="frmCheckInvoiceExport">
		  <input type="hidden" name="module" value="' . $this->bean->module_dir . '" />
		  <input type="hidden" name="action" value="Save" />
		  <input type="hidden" name="record" value="' . $this->bean->id . '" />
		  <input type="hidden" name="record_name" value="' . $this->bean->name . '" />
		  <input type="hidden" name="booking_status" value="' . $this->bean->booking_status . '" />
		  <input type="hidden" name="is_invoice_export" value="1" />
		  <span class="w-50">Hóa đơn đầu ra: </span>
		  <span class="d-flex align-items-center gap-2 flex-fill">
		  	<input type="checkbox" disabled="disabled" ' . ($this->bean->is_invoice_export ? 'checked' : '') . ' />
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
			<span class="w-50">Hóa đơn đầu vào: </span>
			<span class="d-flex align-items-center gap-2 flex-fill">
			<input type="checkbox" disabled="disabled" ' . ($this->bean->is_invoice_input_export ? 'checked' : '') . ' />
			' . ((ACLController::checkAccess('EC_Payment_Voucher', 'edit', true) && $this->bean->booking_status == '8') ? '<input type="submit" name="btnCheckInvoiceInputExport" class="btn btn-primary-2 cursor-pointer" id="btnCheckInvoiceInputExport" value="' . $is_invoice_input_export_title . '" title="' . $is_invoice_input_export_title . '" />' : '') . '
			</span>
		</form>';

		$this->ss->assign('IS_INVOICE_EXPORT', '<div class="d-flex flex-column gap-1">' . $is_invoice_export . $is_invoice_input_export . '</div>');

		$nganluong_code = '';
		$server_name = get_server_name($this->bean->created_by);
		$datepaid = date('Y-m-d', strtotime('-7 hours', strtotime($this->bean->nganluong_datepaid)));
		if ($server_name === 'timchuyenbay.com')
			$payment_link = "$server_name/thanh-toan-online?bkid={$this->bean->id}&datepaid=$datepaid";
		else
			$payment_link = "$server_name/thanh-toan-online?paymentlink={$this->bean->nganluong_code}&datepaid=$datepaid";
		$array_servername = [
			'vietjet.net',
			'timchuyenbay.com',
			'timchuyenbay.vn'
		];
		if (in_array($server_name, $array_servername)) {
			$nganluong_code = '<div class="nganluong__wrap">
				<button class="flex-fill outline-none" id="copy_payment_link" onclick="copyContent(\'' . $payment_link . '\')">
					<img src="themes/SuiteP/images/modules/ec_flight_booking/onepay.svg" alt="onepay">
				</button>
				<button class="flex-fill outline-none" id="get_qr_code">
					<svg width="20px" height="20px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M9 6.6V8.4C9 8.73137 8.73137 9 8.4 9H6.6C6.26863 9 6 8.73137 6 8.4V6.6C6 6.26863 6.26863 6 6.6 6H8.4C8.73137 6 9 6.26863 9 6.6Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6 12H9" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 12V15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 18H15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 12.0111L12.01 12" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 12.0111L18.01 12" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 15.0111L12.01 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 15.0111L18.01 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 18.0111L18.01 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 9.01111L12.01 9" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 6.01111L12.01 6" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9 15.6V17.4C9 17.7314 8.73137 18 8.4 18H6.6C6.26863 18 6 17.7314 6 17.4V15.6C6 15.2686 6.26863 15 6.6 15H8.4C8.73137 15 9 15.2686 9 15.6Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 6.6V8.4C18 8.73137 17.7314 9 17.4 9H15.6C15.2686 9 15 8.73137 15 8.4V6.6C15 6.26863 15.2686 6 15.6 6H17.4C17.7314 6 18 6.26863 18 6.6Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 3H21V6" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 21H21V18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6 3H3V6" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6 21H3V18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					<span>QR code</span>
				</button>
				<button class="flex-fill outline-none btn btn-primary-2" id="get_bank" booking_id="' . $this->bean->id . '">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bank" viewBox="0 0 16 16">
						<path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.5.5 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89zM3.777 3h8.447L8 1zM2 6v7h1V6zm2 0v7h2.5V6zm3.5 0v7h1V6zm2 0v7H12V6zM13 6v7h1V6zm2-1V4H1v1zm-.39 9H1.39l-.25 1h13.72z"/>
					</svg>
					<span>Ngân hàng</span>
				</button>
			</div>';
			$nganluong_code .= $this->generateDialogGetQRCode($this->bean->total_amount, $this->bean->phone);

			// Lịch sử giao dịch
			$transaction_history = '<button type="button" class="history-transaction d-flex align-items-center gap-2 cursor-pointer btn btn-primary-2" data-bs-toggle="modal" data-bs-target="#history-transaction">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
					<path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.76.2 1.126.342zm1.37.71a7.01 7.01 0 0 0-.439-.27l.493-.87a8.025 8.025 0 0 1 .979.654l-.615.789a6.996 6.996 0 0 0-.418-.302zm1.834 1.79a6.99 6.99 0 0 0-.653-.796l.724-.69c.27.285.52.59.747.91l-.818.576zm.744 1.352a7.08 7.08 0 0 0-.214-.468l.893-.45a7.976 7.976 0 0 1 .45 1.088l-.95.313a7.023 7.023 0 0 0-.179-.483m.53 2.507a6.991 6.991 0 0 0-.1-1.025l.985-.17c.067.386.106.778.116 1.17l-1 .025zm-.131 1.538c.033-.17.06-.339.081-.51l.993.123a7.957 7.957 0 0 1-.23 1.155l-.964-.267c.046-.165.086-.332.12-.501zm-.952 2.379c.184-.29.346-.594.486-.908l.914.405c-.16.36-.345.706-.555 1.038l-.845-.535m-.964 1.205c.122-.122.239-.248.35-.378l.758.653a8.073 8.073 0 0 1-.401.432l-.707-.707z"/>
					<path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
					<path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
				</svg>
				<p class="title-history">Các giao dịch</p>
			</button>';
			if (empty($this->bean->nganluong_info) || is_null($this->bean->nganluong_info)) {
				$transaction_history .= '<div class="modal fade" id="history-transaction" tabindex="-1" aria-labelledby="history-transactionLabel" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-header">
								<h2 class="modal-title fs-5" id="history-transactionLabel">Lịch sử giao dịch Onepay</h2>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body d-flex flex-column gap-2 align-items-center">
								<svg fill="#b3b3b3" width="70" height="70" viewBox="0 0 846.66 846.66" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" stroke="#b3b3b3"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <defs> <style type="text/css">  .fil0 {fill:black;fill-rule:nonzero}  </style> </defs> <g id="Layer_x0020_1"> <path class="fil0" d="M93.28 100.89l69.12 0 0 -71.08c0,-11.44 9.28,-20.71 20.72,-20.71l414.03 0c97.36,0 176.94,79.58 176.94,176.94l0 539.02c0,11.44 -9.27,20.71 -20.71,20.71l-69.12 0 0 71.08c0,11.44 -9.28,20.71 -20.72,20.71l-570.26 0c-11.44,0 -20.71,-9.27 -20.71,-20.71l0 -695.25c0,-11.44 9.27,-20.71 20.71,-20.71zm148.42 178.12c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm0 216.78c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm0 -108.39c-27.24,0 -27.24,-41.42 0,-41.42l273.42 0c27.24,0 27.24,41.42 0,41.42l-273.42 0zm-37.87 -286.51l303.48 0c97.36,0 176.95,79.58 176.95,176.94l0 426.52 48.41 0 0 -518.31c0,-74.49 -61.03,-135.52 -135.52,-135.52l-393.32 0 0 50.37zm11.51 478.47l326.15 0c11.43,0 20.71,9.28 20.71,20.71l0 105.46c0,11.44 -9.28,20.72 -20.71,20.72l-326.15 0c-11.44,0 -20.71,-9.28 -20.71,-20.72l0 -105.46c0,-11.43 9.27,-20.71 20.71,-20.71zm305.43 41.42l-284.72 0 0 64.04 284.72 0 0 -64.04zm-13.46 -478.47l-393.32 0 0 653.83 528.84 0 0 -518.31c0,-74.49 -61.02,-135.52 -135.52,-135.52z"></path> </g> </g></svg>
								<p>Booking chưa có giao dịch thanh toán Onepay nào!</p>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
							</div>
						</div>
					</div>
				</div>';
			} else {
				// Lịch sử giao dịch
				$nganluong_info = json_decode(html_entity_decode($this->bean->nganluong_info), true);

				$transaction_history .= '<div class="modal fade" id="history-transaction" tabindex="-1" aria-labelledby="history-transactionLabel" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-header">
								<h2 class="modal-title fs-5" id="history-transactionLabel">Lịch sử giao dịch Onepay</h2>
								<button type="button" class="btn-close d-flex align-items-center" data-bs-dismiss="modal" aria-label="Close">
									<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
										<path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
									</svg>
								</button>
							</div>
							<div class="modal-body d-flex align-items-center gap-3 justify-content-center flex-column">';
				foreach ($nganluong_info as $val) {
					$transaction_history .= '<div class="history-card">
						<div class="history-line time-transaction">
							<div class="transaction-label">Thời gian</div>
							<div class="transaction-value">' . date('H:i:s d/m/Y', strtotime('+7 hours', strtotime($val['payment_date']))) . '</div>
						</div>
						<div class="history-line code-transaction">
							<div class="transaction-label">Mã giao dịch</div>
							<div class="transaction-value">' . $val['vpc_TransactionNo'] . '</div>
						</div>
						<div class="history-line amount-transaction">
							<div class="transaction-label">Số tiền thanh toán</div>
							<div class="transaction-value">' . format_number(substr($val['vpc_Amount'], 0, -2)) . ' VND</div>
						</div>
						<div class="history-line fee-transaction">
							<div class="transaction-label">Phí giao dịch</div>
							<div class="transaction-value">Miễn phí</div>
						</div>
						<div class="history-line desc-transaction">
							<div class="transaction-label">Nội dung</div>
							<div class="transaction-value">' . $val['payment_note'] . '</div>
						</div>
					</div>';
				}
				$transaction_history .= '</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
							</div>
						</div>
					</div>
				</div>';
			}
			$this->ss->assign('CUSTOM_TRANSACTION_HISTORY', $transaction_history);
		} else {
			$nganluong_code = '<div class="nganluong__wrap">
				<button class="flex-fill outline-none" id="get_qr_code">
					<svg width="20px" height="20px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M9 6.6V8.4C9 8.73137 8.73137 9 8.4 9H6.6C6.26863 9 6 8.73137 6 8.4V6.6C6 6.26863 6.26863 6 6.6 6H8.4C8.73137 6 9 6.26863 9 6.6Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6 12H9" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 12V15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 18H15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 12.0111L12.01 12" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 12.0111L18.01 12" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 15.0111L12.01 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 15.0111L18.01 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 18.0111L18.01 18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 9.01111L12.01 9" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 6.01111L12.01 6" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9 15.6V17.4C9 17.7314 8.73137 18 8.4 18H6.6C6.26863 18 6 17.7314 6 17.4V15.6C6 15.2686 6.26863 15 6.6 15H8.4C8.73137 15 9 15.2686 9 15.6Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 6.6V8.4C18 8.73137 17.7314 9 17.4 9H15.6C15.2686 9 15 8.73137 15 8.4V6.6C15 6.26863 15.2686 6 15.6 6H17.4C17.7314 6 18 6.26863 18 6.6Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 3H21V6" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 21H21V18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6 3H3V6" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6 21H3V18" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
					<span>QR code</span>
				</button>
				<button class="flex-fill outline-none btn btn-primary-2" id="get_bank" booking_id="' . $this->bean->id . '">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bank" viewBox="0 0 16 16">
						<path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.5.5 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89zM3.777 3h8.447L8 1zM2 6v7h1V6zm2 0v7h2.5V6zm3.5 0v7h1V6zm2 0v7H12V6zM13 6v7h1V6zm2-1V4H1v1zm-.39 9H1.39l-.25 1h13.72z"/>
					</svg>
					<span>Ngân hàng</span>
				</button>
			</div>';
		}
		$this->ss->assign('CUSTOM_NGANLUONG_CODE', $nganluong_code);

		// Giảm giá
		$discount_html = '<span class="discount_value">' . format_number($this->bean->discount_amount) . '</span>';
		// Giảm giá voucher
		$vouchers = $this->getVoucherApplied();
		if (!empty($vouchers)) {
			$discount_html .= '<div class="wrap-voucher">';
			foreach ($vouchers as $v) {
				$discount_html .= '<a class="' . $v['type'] . '-voucher voucher" for="dialog_voucher_detail_' . $v['code'] . '" title="Xem chi tiết">
					<span class="code">' . $v['code'] . '</span>
				</a>
				<dialog id="dialog_voucher_detail_' . $v['code'] . '" class="dialog dialog-voucher-detail" style="display:none; border-radius:0">
					<ul class="voucher-list-items">
						<li class="voucher-item">
							<span class="label">Sự kiện/Chiến dịch:</span>
							<span class="value">' . $v['campaign_name'] . '</span>
						</li>
						<li class="voucher-item voucher-item-code">
							<span class="label">Mã giảm giá:</span>
							<a class="value" href="index.php?module=EC_Vouchers&action=DetailView&record=' . $v['voucher_id'] . '" target="_blank">
								<span class="me-1">' . $v['code'] . '</span>
								<svg width="14px" height="14px" viewBox="0 0 24 24" stroke-width="2.3" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000333">
									<path d="M21 3L15 3M21 3L12 12M21 3V9" stroke="#000333" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"></path>
									<path d="M21 13V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V5C3 3.89543 3.89543 3 5 3H11" stroke="#000333" stroke-width="2.3" stroke-linecap="round"></path>
								</svg>
							</a>
						</li>
						<li class="voucher-item voucher-item-discount-amount">
							<span class="label">Số tiền được giảm:</span>
							<span class="value">' . format_number($v['discount_amount']) . ' VND</span>
						</li>
					</ul>
				</dialog>';
			}
			$discount_html .= '</div>';
		}
		if (in_array($this->bean->booking_status, ['1', '6', '2'])) {
			// Giảm giá tích điểm
			$points = $this->bean->db->getOne('SELECT points FROM contacts WHERE id = "' . $this->bean->contact_id . '" AND deleted = 0');
			if ($points && $points > 0) {
				$max_point = (int) ($points / $this->bean->point_step) * $this->bean->point_step;
				$discount_html .= '<div class="wrap-points">
					<button class="btn btn btn-primary-2 btn-sm btn-use-point" for="dialog_use_point" title="Dùng điểm tích lũy">Dùng điểm</button>
					<dialog id="dialog_use_point" class="dialog dialog-use-point" style="display:none">
						<div class="content">
							<div class="point">
								<label for="point_of_use">Nhập điểm áp dụng:</label>
								<div class="input-group">
									<input type="number" name="point_of_use" id="point_of_use" class="form-control allow-number-only" min="' . $this->bean->point_step . '" max="' . $max_point . '" step="' . $this->bean->point_step . '" />
									<span class="input-group-text">/<b class="tt_points" id="tt_points" data="' . $points . '">' . $points . ' điểm</b></span>
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
						<button id="btn_apply_points_discount" class="btn btn btn-primary btn-apply-points-discount" contact_id="' . $this->bean->contact_id . '" booking_id="' . $this->bean->id . '" disabled="true">Áp dụng</button>
					</dialog>
				</div>';
			}
		}
		$this->ss->assign('CUS_DISCOUNT_AMOUNT', $discount_html);

		// Doanh số
		$bk_amt = calculateBKAmt($this->bean->id);
		$total_profit = format_number($bk_amt['total_profit'] ?? 0);
		if (is_admin($current_user)) {
			$total_profit .= '<button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#profitBookingModal">Chi tiết D/số</button>
							<div class="modal fade" id="profitBookingModal" tabindex="-1" aria-labelledby="profitBookingModalLabel" aria-hidden="true">
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
													<p class="form-label fw-semibold text-end">' . format_number($bk_amt['total_purchase_pass_refunded'] ?? 0) . '</p>
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

	private function populateCustomButtons($deparment_info)
	{
		global $app_list_strings, $current_user;
		$use_mail_confirm = is_admin($current_user) ? 1 : $deparment_info['use_mail_confirm'];

		// Cancelled booking button - hủy
		if ($this->bean->booking_status != '8' && $this->bean->booking_status != '7' && $this->bean->booking_status != '4' && $this->editing_rights) {
			$cancelled = '</form>
			<form action="index.php" method="post" name="frmCancelled" id="frmCancelled">
			  <input type="hidden" name="module" value="EC_Flight_Bookings" />
			  <input type="hidden" name="action" value="Save" />
			  <input type="hidden" name="record" value="' . $this->bean->id . '" />
			  <input type="hidden" name="booking_status" value="4" />
			  <input type="hidden" name="lydothangthua_id" value="' . $this->bean->lydothangthua_id . '" />
			  <input type="hidden" name="ghichuthangthua" value="' . $this->bean->ghichuthangthua . '" />
			  <input type="hidden" name="optLyDoThangThua" value="' . str_replace('"', "'", myGetSelectOptionsWithDb('EC_LyDoThangThua', $this->bean->lydothangthua_id, 'id', " AND loailydo='1' ORDER BY date_entered ")) . '" />
			  <input type="button" class="btn btn-secondary" name="btnCancelled" id="btnCancelled" value="' . $app_list_strings['booking_status_list']['4'] . '" title="' . $app_list_strings['booking_status_list']['4'] . '" />
			</form>';
			$this->ss->assign('CANCELLED', $cancelled);
		}

		$status_button = $calls_button = '';
		// Called button
		if ($this->bean->booking_status == '1' && $this->editing_rights) {
			$calls_button .= '<div class="btn-group btn-group-called">
				<button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
					Gọi
				</button>
				<ul class="dropdown-menu dropdown-menu-lg-end">
					<li>
						<a class="dropdown-item btn-voiceip-calling" id="btnCalled" booking_id="' . $this->bean->id . '" booking_name="' . $this->bean->name . '" phone="' . $this->bean->phone . '">
							<svg fill="#000000" width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" d="M10.9745053,6.25438069 C11.5604671,6.90391332 11.3746817,7.63976469 10.8565778,8.33797195 C10.7337406,8.50350982 10.5921521,8.6666145 10.4211441,8.84634226 C10.3390625,8.93260918 10.2750591,8.99748744 10.141183,9.13149508 C9.83714115,9.43583583 9.58155513,9.69156272 9.37441088,9.89868984 C9.27396046,9.99913195 9.95978257,11.3696024 11.2907766,12.7019048 C12.6210476,14.0334833 13.9914431,14.7197765 14.0923663,14.6187976 L14.8586096,13.852132 C15.2805737,13.4297532 15.5040355,13.2259664 15.8111037,13.0245121 C16.4494656,12.6057102 17.1457524,12.4919023 17.7329975,13.0170075 C19.6503895,14.3885354 20.7354185,15.2301771 21.2669798,15.782495 C22.303783,16.8597835 22.1679037,18.5180455 21.2728679,19.4640525 C20.9625009,19.7920945 20.5689704,20.1858419 20.1041752,20.6339203 C17.2926326,23.4470127 11.3589665,21.7350681 6.81145433,17.1830859 C2.26291105,12.6300716 0.5518801,6.69583839 3.35753082,3.88868121 C3.86122573,3.37707043 4.02729858,3.211082 4.51785466,2.72771931 C5.43117982,1.82778693 7.16594962,1.68687606 8.22050841,2.7286095 C8.77521019,3.27656509 9.65955176,4.41440275 10.9745053,6.25438069 Z M16.2721965,15.266193 L15.5058008,16.0330112 C14.203091,17.336439 11.9845452,16.2253927 9.8770373,14.1158132 C7.76808363,12.0047866 6.65827534,9.78706944 7.96142436,8.48402821 C8.16828995,8.27717972 8.42363443,8.0216945 8.72744369,7.71758662 C8.8500234,7.59488642 8.90609452,7.5380489 8.97339653,7.46731514 C9.06509326,7.37094278 9.1404434,7.28630078 9.20077275,7.211402 C8.03540499,5.58806095 7.24320651,4.57370892 6.8161396,4.15183592 C6.59558525,3.93396391 6.1017247,3.97407893 5.9204189,4.1527261 C5.43686641,4.6291879 5.27792422,4.78804929 4.77626041,5.29755675 C2.9719475,7.10286418 4.35321008,11.8933879 8.22519368,15.7691775 C12.0959638,19.6437524 16.8857659,21.0256764 18.7038097,19.2068681 C19.161375,18.7655298 19.5342402,18.3924591 19.8212354,18.08912 C20.0286173,17.8699279 20.0656783,17.4176384 19.8271235,17.1697684 C19.4297888,16.7569185 18.4570205,15.9984643 16.777362,14.7922626 C16.6549304,14.8908077 16.5044234,15.033738 16.2721965,15.266193 Z M17.5857864,7 L13,7 L13,5 L17.5857864,5 L16.2928932,3.70710678 L17.7071068,2.29289322 L21.4142136,6 L17.7071068,9.70710678 L16.2928932,8.29289322 L17.5857864,7 Z"></path> </g></svg>
							<span class="ms-1">Gọi ngay</span>
						</a>
					</li>
					<li>
						<a class="dropdown-item btn-voiceip-calling btn-voiceip-calling-zalo" id="btnCalled" booking_id="' . $this->bean->id . '" booking_name="' . $this->bean->name . '" phone="' . $this->bean->phone . '">
							<svg fill="#000000" width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" d="M10.9745053,6.25438069 C11.5604671,6.90391332 11.3746817,7.63976469 10.8565778,8.33797195 C10.7337406,8.50350982 10.5921521,8.6666145 10.4211441,8.84634226 C10.3390625,8.93260918 10.2750591,8.99748744 10.141183,9.13149508 C9.83714115,9.43583583 9.58155513,9.69156272 9.37441088,9.89868984 C9.27396046,9.99913195 9.95978257,11.3696024 11.2907766,12.7019048 C12.6210476,14.0334833 13.9914431,14.7197765 14.0923663,14.6187976 L14.8586096,13.852132 C15.2805737,13.4297532 15.5040355,13.2259664 15.8111037,13.0245121 C16.4494656,12.6057102 17.1457524,12.4919023 17.7329975,13.0170075 C19.6503895,14.3885354 20.7354185,15.2301771 21.2669798,15.782495 C22.303783,16.8597835 22.1679037,18.5180455 21.2728679,19.4640525 C20.9625009,19.7920945 20.5689704,20.1858419 20.1041752,20.6339203 C17.2926326,23.4470127 11.3589665,21.7350681 6.81145433,17.1830859 C2.26291105,12.6300716 0.5518801,6.69583839 3.35753082,3.88868121 C3.86122573,3.37707043 4.02729858,3.211082 4.51785466,2.72771931 C5.43117982,1.82778693 7.16594962,1.68687606 8.22050841,2.7286095 C8.77521019,3.27656509 9.65955176,4.41440275 10.9745053,6.25438069 Z M16.2721965,15.266193 L15.5058008,16.0330112 C14.203091,17.336439 11.9845452,16.2253927 9.8770373,14.1158132 C7.76808363,12.0047866 6.65827534,9.78706944 7.96142436,8.48402821 C8.16828995,8.27717972 8.42363443,8.0216945 8.72744369,7.71758662 C8.8500234,7.59488642 8.90609452,7.5380489 8.97339653,7.46731514 C9.06509326,7.37094278 9.1404434,7.28630078 9.20077275,7.211402 C8.03540499,5.58806095 7.24320651,4.57370892 6.8161396,4.15183592 C6.59558525,3.93396391 6.1017247,3.97407893 5.9204189,4.1527261 C5.43686641,4.6291879 5.27792422,4.78804929 4.77626041,5.29755675 C2.9719475,7.10286418 4.35321008,11.8933879 8.22519368,15.7691775 C12.0959638,19.6437524 16.8857659,21.0256764 18.7038097,19.2068681 C19.161375,18.7655298 19.5342402,18.3924591 19.8212354,18.08912 C20.0286173,17.8699279 20.0656783,17.4176384 19.8271235,17.1697684 C19.4297888,16.7569185 18.4570205,15.9984643 16.777362,14.7922626 C16.6549304,14.8908077 16.5044234,15.033738 16.2721965,15.266193 Z M17.5857864,7 L13,7 L13,5 L17.5857864,5 L16.2928932,3.70710678 L17.7071068,2.29289322 L21.4142136,6 L17.7071068,9.70710678 L16.2928932,8.29289322 L17.5857864,7 Z"></path> </g></svg>
							<span class="ms-1">Gọi Zalo</span>
						</a>
					</li>
					<li>
						<a class="dropdown-item btn-voiceip-mapping" onclick="showDialog(' . "'mapping_call_booking'" . ')">
							<svg fill="#000000" width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" d="M10.9745053,6.25438069 C11.5604671,6.90391332 11.3746817,7.63976469 10.8565778,8.33797195 C10.7337406,8.50350982 10.5921521,8.6666145 10.4211441,8.84634226 C10.3390625,8.93260918 10.2750591,8.99748744 10.141183,9.13149508 C9.83714115,9.43583583 9.58155513,9.69156272 9.37441088,9.89868984 C9.27396046,9.99913195 9.95978257,11.3696024 11.2907766,12.7019048 C12.6210476,14.0334833 13.9914431,14.7197765 14.0923663,14.6187976 L14.8586096,13.852132 C15.2805737,13.4297532 15.5040355,13.2259664 15.8111037,13.0245121 C16.4494656,12.6057102 17.1457524,12.4919023 17.7329975,13.0170075 C19.6503895,14.3885354 20.7354185,15.2301771 21.2669798,15.782495 C22.303783,16.8597835 22.1679037,18.5180455 21.2728679,19.4640525 C20.9625009,19.7920945 20.5689704,20.1858419 20.1041752,20.6339203 C17.2926326,23.4470127 11.3589665,21.7350681 6.81145433,17.1830859 C2.26291105,12.6300716 0.5518801,6.69583839 3.35753082,3.88868121 C3.86122573,3.37707043 4.02729858,3.211082 4.51785466,2.72771931 C5.43117982,1.82778693 7.16594962,1.68687606 8.22050841,2.7286095 C8.77521019,3.27656509 9.65955176,4.41440275 10.9745053,6.25438069 Z M16.2721965,15.266193 L15.5058008,16.0330112 C14.203091,17.336439 11.9845452,16.2253927 9.8770373,14.1158132 C7.76808363,12.0047866 6.65827534,9.78706944 7.96142436,8.48402821 C8.16828995,8.27717972 8.42363443,8.0216945 8.72744369,7.71758662 C8.8500234,7.59488642 8.90609452,7.5380489 8.97339653,7.46731514 C9.06509326,7.37094278 9.1404434,7.28630078 9.20077275,7.211402 C8.03540499,5.58806095 7.24320651,4.57370892 6.8161396,4.15183592 C6.59558525,3.93396391 6.1017247,3.97407893 5.9204189,4.1527261 C5.43686641,4.6291879 5.27792422,4.78804929 4.77626041,5.29755675 C2.9719475,7.10286418 4.35321008,11.8933879 8.22519368,15.7691775 C12.0959638,19.6437524 16.8857659,21.0256764 18.7038097,19.2068681 C19.161375,18.7655298 19.5342402,18.3924591 19.8212354,18.08912 C20.0286173,17.8699279 20.0656783,17.4176384 19.8271235,17.1697684 C19.4297888,16.7569185 18.4570205,15.9984643 16.777362,14.7922626 C16.6549304,14.8908077 16.5044234,15.033738 16.2721965,15.266193 Z M17,5 L17,2 L19,2 L19,5 L22,5 L22,7 L19,7 L19,10 L17,10 L17,7 L14,7 L14,5 L17,5 Z"></path> </g></svg>
							<span class="ms-1">Liên kết</span>
						</a>
					</li>
				</ul>
				<dialog id="mapping_call_booking">
					<h5 class="text-center">Liên kết cuộc gọi</h5>
					<input type="text" name="call_name" class="form-control" placeholder="Nhập mã cuộc gọi" />
					<div class="wrap-button mt-3">
						<button type="button" class="btn btn-secondary" onclick="closeDialog(' . "'mapping_call_booking'" . ')">Hủy</button>
						<button type="button" class="btn btn-primary ms-1" id="btn-mapping-call-booking" booking_id="' . $this->bean->id . '" booking_name="' . $this->bean->name . '">Liên kết</button>
					</div>
				</dialog>
			</div>';

			// Bổ sung thêm Chờ TT
			$status_button .= '</form>
			<form action="index.php" method="post" name="frmPaymentPending" id="frmPaymentPending">
			  <input type="hidden" name="module" value="EC_Flight_Bookings" />
			  <input type="hidden" name="action" value="Save" />
			  <input type="hidden" name="record" value="' . $this->bean->id . '" />
			  <input type="hidden" name="booking_status" value="2" />
			  <input type="hidden" name="assigned_user_id" value="' . $current_user->id . '" />
			  <input type="submit" name="btnPaymentPending" class="btn btn-warning button-action" id="btnPaymentPending" value="' . $app_list_strings['booking_status_list']['2'] . '" title="' . $app_list_strings['booking_status_list']['2'] . '" />
			</form>';
		}
		// Payment pending button
		else if ($this->bean->booking_status == '6' && $this->editing_rights) {
			$status_button = '</form>
			<form action="index.php" method="post" name="frmPaymentPending" id="frmPaymentPending">
			  <input type="hidden" name="module" value="EC_Flight_Bookings" />
			  <input type="hidden" name="action" value="Save" />
			  <input type="hidden" name="record" value="' . $this->bean->id . '" />
			  <input type="hidden" name="booking_status" value="2" />
			  <input type="hidden" name="assigned_user_id" value="' . $current_user->id . '" />
			  <input type="submit" name="btnPaymentPending" class="btn btn-warning button-action" id="btnPaymentPending" value="' . $app_list_strings['booking_status_list']['2'] . '" title="' . $app_list_strings['booking_status_list']['2'] . '" />
			</form>';
		}
		// Completed button
		else if ($this->bean->booking_status == '7' && $this->editing_rights) {
			// Check booking with full baggage price or not
			$complete_ok = $this->bean->db->getOne("SELECT COUNT(*)
				FROM ec_booking_passengers 
				WHERE booking_id = '{$this->bean->id}'
					AND deleted = 0
					AND (
						(luggage_purchase > 0 AND (luggage_price IS NULL OR luggage_price = 0))
						OR
						(luggage_purchase_inbound > 0 AND (luggage_price_inbound IS NULL OR luggage_price_inbound = 0))
					)
			") > 0 ? 0 : 1;

			$status_button = '</form>
				<form class="frmBookingStatus" action="index.php" method="post" name="frmCompleted" id="frmCompleted">
				<input type="hidden" name="module" value="EC_Flight_Bookings" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="' . $this->bean->id . '" />
				<input type="hidden" name="record_name" value="' . $this->bean->name . '" />
				<input type="hidden" name="booking_status" value="8" />
				<input type="hidden" name="flight_type" value="' . $this->bean->flight_type . '" />
				<input type="hidden" name="is_ticket_exported" id="is_ticket_exported" value="' . (isset($this->bean->is_ticket_exported) ? $this->bean->is_ticket_exported : 0) . '" />
				<input type="hidden" name="lydothangthua_id" value="' . $this->bean->lydothangthua_id . '" />
				<input type="hidden" name="ghichuthangthua" value="' . $this->bean->ghichuthangthua . '" />
				<input type="hidden" name="optLyDoThangThua" value="' . str_replace('"', "'", myGetSelectOptionsWithDb('EC_LyDoThangThua', $this->bean->lydothangthua_id, 'id', " AND loailydo='0' ORDER BY date_entered ")) . '" />
				<input type="hidden" name="complete_ok" value="' . $complete_ok . '" />
				<input type="submit" class="btn btn-success save-popup-dialog" name="btnCompleted" id="btnCompleted" value="' . $app_list_strings['booking_status_list']['8'] . '" title="' . $app_list_strings['booking_status_list']['8'] . '" />
			</form>';
		}
		$this->ss->assign('STATUS_BUTTON', $status_button);
		$this->ss->assign('CALLS_BUTTON', $calls_button);


		// Send mail confirm button
		if (!in_array($this->bean->booking_status, [4, 7, 8]) && $use_mail_confirm) {
			$send_mail = '</form>
			<form action="index.php" method="post" name="frmSendMail" id="frmSendMail" class="frmSendMail">
				<input type="hidden" name="module" value="EC_Flight_Bookings" />
				<input type="hidden" name="action" value="sendconfirm" />
				<input type="hidden" name="record" value="' . $this->bean->id . '" />
				<input type="hidden" name="email" value="' . $this->bean->email . '" />
				<input type="hidden" name="return_module" value="EC_Flight_Bookings" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="' . $this->bean->id . '" />
				<span style="display:none;" id="frmContinueSendMail">
					<select name="form_mail" class="box-select">
						<option value="sendmail_confirm.html">Mail xác nhận</option>
						<option value="sendmail_closetime.html">Mail cận giờ bay</option>
						<!-- <option value="sendmail_promo.html">Mail vé khuyến mãi</option> -->
					</select>
					<input type="submit" class="btn btn-primary save-popup-dialog" value="Gửi mail" title="Gửi mail" />
					<input type="button" class="btn btn-primary" id="btnPreviewSendMail" booking_id="' . $this->bean->id . '" value="Xem trước" title="Xem trước" />
					<div id="dialog_mail_confirm_preview" style="display: none;"></div>
					<input type="button" class="btn btn-secondary" id="btnCancelSendMail" value="Hủy bỏ" title="Hủy bỏ" />
				</span>
				<button type="button" class="btn btn-email" id="btnSendMail" value="Gửi mail" >
					<svg width="21px" height="21px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#fff" style="margin-bottom: 1px;"><path d="M7 9l5 3.5L17 9" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M2 17V7a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" stroke="#fff" stroke-width="1.5"></path></svg>
					<span>Gmail</span>
				</button>
			</form>';
			$this->ss->assign('SEND_MAIL', $send_mail);
		}


		// Change booking status button
		$now = date("Y-m-d H:i:s");
		$time_current = date("H:i:s", strtotime('+7 hours', strtotime($now)));

		/**
		 * Trong khung giờ 21h - 6h sáng thì được thấy nút "chuyển trạng thái booking"
		 */
		if (isManagerUser($current_user->id) && !in_array($this->bean->booking_status, [7, 8]) || is_admin($current_user)) {
			// if (ACLController::checkAccess('Bugs', 'edit', true) && $this->editing_rights && !in_array($this->bean->booking_status, array(4, 7, 8))) {
			// if (!in_array($this->bean->booking_status, array(3, 4, 7, 8)) && isManagerUser($current_user->id) || $current_user->user_name == 'admin' || strtotime($time_current) < strtotime("08:00:00") || strtotime($time_current) > strtotime("20:59:59")) {
			$change_status = '</form>
				<form action="index.php" method="post" name="frmChangeStatus" id="frmChangeStatus" class="d-flex align-items-center gap-2">
					<input type="hidden" name="module" value="EC_Flight_Bookings" />
					<input type="hidden" name="action" value="Save" />
					<input type="hidden" name="record" value="' . $this->bean->id . '" />
					<select class="select-box" id="booking_status" name="booking_status" >' . get_select_options_with_id($app_list_strings['booking_status_list'], (int) $this->bean->booking_status) . '</select>
					<input type="hidden" name="flight_type" value="' . $this->bean->flight_type . '" />
					<input type="hidden" name="is_ticket_exported" id="is_ticket_exported" value="' . (isset($this->bean->is_ticket_exported) ? $this->bean->is_ticket_exported : 0) . '" />
					<input type="submit" class="btn btn-warning d-none" name="btnChangeStatus" id="btnChangeStatus" value="Đổi trạng thái" title="Đổi trạng thái" />
				</form>';
			$this->ss->assign('CHANGE_STATUS', $change_status);
		}

		// Những nhân viên đã xem booking
		if (is_admin($current_user) && $current_user->title != 'QuanLy') {
			$viewed = '</form>
			<form action="index.php" method="post" name="frmViewedBooking" id="frmViewedBooking" class="d-flex align-items-center gap-2">
				<input type="hidden" name="module" value="EC_Flight_Bookings" />
				<input type="hidden" name="record" value="' . $this->bean->id . '" />
				<button type="button" class="btn btn-secondary" id="btnViewBooking" value="Đã xem Booking" >
					<span>Đã xem</span>
				</button>
				<div id="viewed_booking--wrap"></div>
			</form>';
			$this->ss->assign('VIEWED_BOOKING', $viewed);
		}

		$common_style = 'display: inline-flex; align-items: center; justify-content: center; height: 34px; padding: 0 12px; vertical-align: middle; gap: 6px;';
		//Thêm style flex để căn chỉnh
		$doc_button = '<a id="btnDocument" class="btn btn-primary btn btn-primary-2 cursor-pointer" 
        style="' . $common_style . '" 
        href="index.php?module=Documents&action=EditView&booking_id=' . $this->bean->id . '&booking_name=' . $this->bean->name . '" target="_blank">';

		// 2. Chèn SVG: 
		$doc_button .= '<svg xmlns="http://www.w3.org/2000/svg" width="18px" height="18px" viewBox="0 0 24 24"><title/><g id="Complete"><g id="upload"><g>
                <path d="M3,12.3v7a2,2,0,0,0,2,2H19a2,2,0,0,0,2-2v-7" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><g>
                <polyline data-name="Right" fill="none" id="Right-2" points="7.9 6.7 12 2.7 16.1 6.7" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                <line fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x1="12" x2="12" y1="16.3" y2="4.8"/>
                </g></g></g></g>
                </svg>';

		$doc_button .= '<span>Tài liệu</span></a>';

		// Gán vào Smarty
		$this->ss->assign('DOC_BUTTON', $doc_button);

		// --- NÚT 2: DANH SÁCH ĐÃ TẢI (Không Icon) ---
		$doc_button_2 = '<button id="btn-uploaded-docs" class="btn btn-primary btn btn-primary-2 cursor-pointer" type="button" 
        style="' . $common_style . '" 
        onclick="showUploadedDocuments(\'' . $this->bean->id . '\')">';

		// Không cần span độn nữa vì đã set height cứng ở trên
		$doc_button_2 .= '<span>Danh sách đã tải</span></button>';
		$this->ss->assign('DOC_LIST_BUTTON', $doc_button_2);

		// Create receipt voucher button
		$this->_is_had_rv = myCheckValueExist('EC_Receipt_Voucher', array('booking_id'), array($this->bean->id), '');
		if (
			$this->bean->booking_status == 2
			&& !$this->_is_had_rv
			&& ACLController::checkAccess('EC_Receipt_Voucher', 'edit', true)
			||
			$this->bean->is_agent == 1
			&&
			!$this->_is_had_rv  /* && $this->bean->is_agent != 1 */
		) {
			$receipt_type = ($this->bean->payment_type == 3 || $this->bean->payment_type == 4) ? 'credit_transfer' : 'cash';
			$create_rv = '</form>
			<form action="index.php" method="post" name="frmCreateRV" id="frmCreateRV">
			  <input type="hidden" name="module" value="EC_Receipt_Voucher" />
			  <input type="hidden" name="action" value="EditView" />
			  <input type="hidden" name="amount" value="' . format_number($this->bean->total_amount) . '" />
			  <input type="hidden" name="amount_converted" value="' . format_number($this->bean->total_amount) . '" />
			  <input type="hidden" name="receipt_type" value="' . $receipt_type . '" />
			  <input type="hidden" name="guest_name" value="' . trim(stripslashes($this->bean->contact_name)) . '" />
			  <input type="hidden" name="guest_phone" value="' . trim(stripslashes($this->bean->phone)) . '" />
			  <input type="hidden" name="guest_address" value="' . trim(stripslashes($this->bean->address)) . '" />
			  <input type="hidden" name="description" value="Thu tiền booking: ' . $this->bean->name . '" />
			  <input type="hidden" name="loai_thu" value="1" />
			  <input type="hidden" name="from_ec_flight_bookings" value="1" />
			  <input type="hidden" name="booking_name" value="' . $this->bean->name . '" />
			  <input type="hidden" name="booking_id" value="' . $this->bean->id . '" />
			  <input type="submit" name="btnCreateRV" id="btnCreateRV" class="btn btn-warning save-popup-dialog" value="Tạo phiếu thu" title="Tạo phiếu thu" />									
			</form>';
			$this->ss->assign('CREATE_RV', $create_rv);
		}

		// Mark as preference button
		$mask_as_refer = '';
		if (!$this->bean->is_reference) {
			$mask_as_refer = '</form>
                                    <form action="index.php" method="post" name="frmMarkAdPreferance" id="frmMarkAdPreferance">
                                        <input type="hidden" name="module" value="' . $this->bean->object_name . '" />
                                        <input type="hidden" name="action" value="Save" />
                                        <input type="hidden" name="record" value="' . $this->bean->id . '" />
                                          <input type="hidden" name="record_name" value="' . $this->bean->name . '" />
                                        <input type="hidden" name="is_reference" value="1" />
                                        <input type="submit" name="btnMarkAsPreference" id="btnMarkAsPreference" class="btn btn-primary" value="Đánh dấu BK tham khảo" title="Đánh dấu BK tham khảo" onclick="return confirm(\'Bạn có chắc chắn muốn đánh dấu đây là BK tham khảo?\');"/>									
                                    </form>';
		}
		$this->ss->assign('MARK_AS_REFERENCE', $mask_as_refer);

		// Ticket return button
		if (
			($this->bean->booking_status == '7' || $this->bean->booking_status == '8')
			&& $this->editing_rights
			&& !myCheckValueExist('EC_HoanVe', array('booking_id'), array($this->bean->id), '')
		) {
			$ticket_return = '</form>
			<form action="index.php" method="post" name="frmTicketReturn" id="frmTicketReturn">
			  <input type="hidden" name="module" value="EC_HoanVe" />
			  <input type="hidden" name="action" value="EditView" />
			  <input type="hidden" name="booking" value="' . $this->bean->name . '" />
			  <input type="hidden" name="booking_id" value="' . $this->bean->id . '" />
			  <input type="submit" class="btn btn-danger" name="btnTicketReturn" id="btnTicketReturn" value="Hoàn vé" title="Hoàn vé" />
			</form>';
			$this->ss->assign('TICKET_RETURN', $ticket_return);
		}

		// Create invoice button
		if ($this->bean->booking_status == '7' || $this->bean->booking_status == '8') {
			$create_inv = '
			<input type="button" id="btnCreateInvoice" class="btn btn-warning" value="Sửa YC xuất HĐ" title="Sửa YC xuất HĐ" />
			</form>
			<form id="edit_invoice_frm" method="post" style="display:none; background-color:#fff;" name="edit_invoice_frm">
				<input type="hidden" name="module" value="EC_Flight_Bookings">
				<input type="hidden" name="action" value="Save">
				<input type="hidden" name="booking_id" value="' . $this->bean->id . '">
				<div class="detail view" id="invoice_inf"></div>
				<div class="text-center">
					<input class="btn btn-primary save-popup-dialog" type="submit" value="Lưu" name="save_request_invoice">
				</div>
			</form>';
			$this->ss->assign('CREATE_INVOICE', $create_inv);
		}

		// Edit booking detail
		// if ($this->bean->booking_status == '7' || ACLController::checkAccess("Bugs", "edit", true) && $this->bean->booking_status == '8') {
		if ($this->bean->booking_status == '7' || $this->bean->booking_status == '8') {
			$total_qty = isset($_POST['total_qty']) && !empty($_POST['total_qty']) ? $_POST['total_qty'] : (isset($this->bean->total_qty) ? $this->bean->total_qty : 0);
			$subtotal_amount = isset($_POST['subtotal_amount']) && !empty($_POST['subtotal_amount']) ? $_POST['subtotal_amount'] : (isset($this->bean->subtotal_amount) ? $this->bean->subtotal_amount : 0);
			$total_bought_amount = isset($_POST['total_bought_amount']) && !empty($_POST['total_bought_amount']) ? $_POST['total_bought_amount'] : (isset($this->bean->total_bought_amount) ? $this->bean->total_bought_amount : 0);

			$bkg_detail = '<input type="button" class="btn btn-warning" id="edit_bkg_btn" value="Sửa chi tiết booking">
							</form><form id="bkg_detail" method="post" style="display:none; background-color:#fff;">
								<input type="hidden" name="module" value="EC_Flight_Bookings">
								<input type="hidden" name="action" value="Save">
								<input type="hidden" id="bkg_no" name="record" value="' . $this->bean->id . '">
								<input type="hidden" name="edit_detail">
								<div class="detail view in-popup">
									<h4 class="dialog-title">Chi tiết vé</h4>
									<table id="tbl_line_details" class="table_config table-edit-details__booking table-details__booking" cellpadding="0" cellspacing="0" border="0"></table>
								</div>
								<input class="btn btn-primary mt-2 d-block mx-auto save-popup-dialog" type="submit" value="Lưu">
							</form>';
			$this->ss->assign('EDIT_BKG_DETAIL', $bkg_detail);
		}

		// Booking ở trạng thái "xuất vé" hoặc "hoàn tất
		if ($this->bean->booking_status == '7' || $this->bean->booking_status == '8') {
			// Change name - Đổi tên hành khách
			$change_name = '
			<input id="change_name_btn" type="button" value="Hành khách / Hành lý / Code vé">
			</form><form id="change_name" method="post" style="display:none; background-color:#fff;">
				<input type="hidden" id="bkg_no_name" name="record" value="' . $this->bean->id . '">
				<input type="hidden" name="module" value="EC_Flight_Bookings">
				<input type="hidden" name="action" value="Save">
				<div class="detail view" id="line_passengers_name_area">
				</div>
				<input class="btn btn-primary mt-2" type="submit" value="Lưu">
			</form>';
			$this->ss->assign('CHANGE_PASSENGER_NAME', $change_name);

			// Đổi thông tin ngày bay / hành trình / tên hành khách / hành lý
			$change_flight_time = '
			<input id="change_flight_time" class="btn btn-warning" type="button" value="Đổi thông tin">
			</form><form id="tbl_change_flight_time" method="post" style="display:none; background-color:#fff; overflow: hidden;" name="tbl_change_flight_time">
				<input type="hidden" name="module" value="EC_Flight_Bookings">
				<input type="hidden" name="action" value="Save">
				<input type="hidden" name="booking_id" value="' . $this->bean->id . '">
				<div class="detail view in-popup" id="line_itineraries_area"></div>
				<input class="btn btn-primary mt-3 d-block mx-auto" type="submit" value="Lưu" name="save_change_flight">
			</form>';
			$this->ss->assign('CHANGE_FLIGHT_TIME', $change_flight_time);
		}

		// Auto book
		if (in_array($this->bean->booking_status, [1, 2, 3, 6])) {
			// if (in_array($this->bean->booking_status, [1, 2, 3, 6]) && !$this->bean->is_hold && !$this->bean->holding_status) {
			$agencyOptions = '
				<li>
					<a type="button" id="auto-book-datacom" class="dropdown-item btn-auto-book" data-entry-class="entryAutoBookDatacomClass">
						<span class="ms-1">Hồng Ngọc Hà</span>
					</a>
				</li>
				<li>
					<a type="button" id="auto-book-phuongnam" class="dropdown-item btn-auto-book" data-entry-class="entryAutoBookPhuongNamClass">
						<span class="ms-1">Phương Nam</span>
					</a>
				</li>
			';
			if ($this->bean->ticket_type == '2') {
				$agencyOptions = '<li>
					<a type="button" id="auto-book-datacom" class="dropdown-item btn-auto-book" data-entry-class="entryAutoBookDatacomClass">
						<span class="ms-1">Hồng Ngọc Hà</span>
					</a>
				</li>';
			}

			$this->ss->assign(
				'BUTTON_AUTO_BOOK',
				'<div class="btn-group btn-group-autobook">
					<button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
						Auto book
					</button>
					<ul class="dropdown-menu dropdown-menu-lg-end">' . $agencyOptions . '</ul>
				</div>'
			);
		} else {
			$this->ss->assign('BUTTON_AUTO_BOOK', '');
		}

		// Print ticket button (old)
		$this->ss->assign(
			'PRINT_TICKET',
			'<div class="btn-group btnPrintEticket-selection">
				<button type="button" class="btn btn-secondary btnPrintEticket" data-bs-display="static" aria-expanded="false">In vé</button>
			</div>'
		);

		// Send ticket button
		$this->ss->assign(
			'SEND_TICKET',
			'<div class="btn-group btnSendEticket-selection">
				<button type="button" class="btn btn-secondary btnSendEticket" data-bs-display="static" aria-expanded="false">
					Gửi vé
				</button>
			</div>'
		);

		// PRINT_TICKET_NEW - Nút in vé mới với popup chọn
		// Build resolved itinerary list (preferring rescheduled over originals) for the popup
		$resolvedItineraries = $this->getResolvedItinerariesForPopup();
		$itinerariesJson = htmlspecialchars(json_encode($resolvedItineraries), ENT_QUOTES, 'UTF-8');

		// Per-passenger itinerary changes detection
		$hasPerPaxChanges = $this->hasPerPassengerItineraryChanges();
		$perPaxDataAttr = '';
		if ($hasPerPaxChanges) {
			$perPaxItineraries = $this->getPerPassengerItinerariesForPopup();
			$perPaxDataAttr = ' data-per-pax-itineraries="' . htmlspecialchars(json_encode($perPaxItineraries), ENT_QUOTES, 'UTF-8') . '"';
		}
		$this->ss->assign(
			'PRINT_TICKET_NEW',
			'<div class="btn-group btnPrintEticketNew-selection">
				<button type="button" class="btn btn-primary btnPrintEticketNew" data-bs-display="static" aria-expanded="false">In & Gửi vé</button>
			</div>
			<div id="dlgPrintTicketNew" style="display:none;" data-itineraries="' . $itinerariesJson . '" data-booking-id="' . $this->bean->id . '" data-booking="' . $this->bean->name . '" data-ticket-type="' . $this->bean->ticket_type . '" data-has-per-pax-changes="' . ($hasPerPaxChanges ? '1' : '0') . '"' . $perPaxDataAttr . '>
				<div class="popup-itinerary-section" style="margin-bottom:14px;">
					<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
						<strong style="font-size:14px;">
							<svg width="16px" height="16px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000" style="vertical-align:sub;margin-right:2px;">
								<path d="M20 10C20 14.4183 12 22 12 22C12 22 4 14.4183 4 10C4 5.58172 7.58172 2 12 2C16.4183 2 20 5.58172 20 10Z" stroke="#000000" stroke-width="1.5"></path><path d="M12 11C12.5523 11 13 10.5523 13 10C13 9.44772 12.5523 9 12 9C11.4477 9 11 9.44772 11 10C11 10.5523 11.4477 11 12 11Z" fill="#000000" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
							</svg>
							Hành trình
						</strong>
						<label style="font-size:12px; cursor:pointer;"><input type="checkbox" id="popup-select-all-iti" checked /> Chọn tất cả</label>
					</div>
					<div class="popup-itinerary-list" style="max-height:160px; overflow-y:auto; border:1px solid #e0e0e0; border-radius:6px; padding:6px 10px; background:#fafafa;"></div>
				</div>
				<div class="popup-passenger-section" style="margin-bottom:14px;">
					<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
						<strong style="font-size:14px;">
							<svg width="17px" height="17px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000" style="vertical-align:sub;margin-right:2px;">
								<path d="M1 20V19C1 15.134 4.13401 12 8 12V12C11.866 12 15 15.134 15 19V20" stroke="#000000" stroke-width="1.5" stroke-linecap="round"></path>
								<path d="M13 14V14C13 11.2386 15.2386 9 18 9V9C20.7614 9 23 11.2386 23 14V14.5" stroke="#000000" stroke-width="1.5" stroke-linecap="round"></path>
								<path d="M8 12C10.2091 12 12 10.2091 12 8C12 5.79086 10.2091 4 8 4C5.79086 4 4 5.79086 4 8C4 10.2091 5.79086 12 8 12Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 9C19.6569 9 21 7.65685 21 6C21 4.34315 19.6569 3 18 3C16.3431 3 15 4.34315 15 6C15 7.65685 16.3431 9 18 9Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
							</svg> 
							Hành khách
						</strong>
						<label style="font-size:12px; cursor:pointer;"><input type="checkbox" id="popup-select-all-psg" checked /> Chọn tất cả</label>
					</div>
					<div class="popup-passenger-list" style="max-height:260px; overflow-y:auto; border:1px solid #e0e0e0; border-radius:6px; padding:6px 10px; background:#fafafa;"></div>
				</div>
				<div class="popup-perpax-section" style="display:none; margin-bottom:14px;">
					<strong style="font-size:14px; display:block; margin-bottom:8px;">👤 Hành khách & Hành trình</strong>
					<div class="popup-perpax-list" style="max-height:480px; overflow-y:auto; border:1px solid #e0e0e0; border-radius:6px; padding:8px 10px; background:#fafafa;"></div>
				</div>
				<div style="display:flex; align-items:center; justify-content:center; gap:16px; margin-bottom:10px;">
					<label style="cursor:pointer;"><input type="radio" name="popup_ngonngu" value="vn" checked /> Tiếng Việt</label>
					<label style="cursor:pointer;"><input type="radio" name="popup_ngonngu" value="en" /> English</label>
				</div>
				<div style="text-align:center;">
					<button type="button" class="btn btn-primary" id="btnSubmitPrintNew" style="padding:8px 28px; font-size:14px;">In vé</button>
					<button type="button" class="btn btn-primary" id="btnSubmitSendNew" style="padding:8px 28px; font-size:14px;">Gửi vé</button>
				</div>
			</div>'
		);

		// Cập nhật doanh số của booking trong table ec_revenue
		$update_revenue = '';
		if (is_admin($current_user) && $current_user->user_name == 'hungnh') {
			$update_revenue = '<input id="update_revenue" class="btn btn-primary" type="button" value="Cập nhật DS">';
		}
		$this->ss->assign('UPDATE_REVENUE', $update_revenue);
	}

	// Display all itineraries
	private function populateLineItineraries($deparment_info)
	{
		global $app_list_strings, $timedate, $current_user;
		$date_format = $timedate->get_date_format();
		$airport_list = $app_list_strings['domestic_airport_list'] + $app_list_strings['africa_airport_list'] + $app_list_strings['americas_airport_list'] + $app_list_strings['australia_airport_list'] + $app_list_strings['europe_airport_list'] + $app_list_strings['northeast_asia_airport_list'] + $app_list_strings['southeast_asia_airport_list'];
		$use_mail_eticket = is_admin($current_user) ? 1 : $deparment_info['use_mail_eticket'];

		$html = '';
		$html .= '<table id="itinerary_tbl" border="0" cellpadding="0" cellspacing="0" class="table-config table-itinerary table-details__booking"> 
					<thead>
						<tr>
							<th scope="col" width="3%"></th> 
							<th scope="col" width="3%">STT</th>
							<th scope="col" width="8%">Chiều</th>
							<th scope="col" width="8%">Mã hãng</th>
							<th scope="col" width="7%">Số hiệu</th>
							<th scope="col" width="7%">Hạng vé</th>
							<th scope="col" width="7%">Nơi đi</th>
							<th scope="col" width="7%">Nơi đến</th>
							<th scope="col" width="10%">Ngày giờ đi</th>
							<th scope="col" width="10%">Ngày giờ đến</th>
							<th scope="col" width="10%">Hạn giữ chỗ</th>
							<th scope="col" width="8%">Giá cơ bản</th>
							<th scope="col">&nbsp;</th>
						</tr>
					</thead>';

		// Lấy ds những hành khách còn áp dụng hành trình đặt ban đầu
		$departure_applied_pass = $this->getAppliedPassengerIti($this->bean->id, 0);
		$arrival_applied_pass = '';
		if ($this->bean->flight_type == '0') {
			$arrival_applied_pass = $this->getAppliedPassengerIti($this->bean->id, 1);
		}

		$sql = "SELECT iti.id,
					iti.name,
					iti.description,
					iti.airline_code,
					iti.flight_number,
					iti.ticket_class,
					iti.departure,
					iti.arrival,
					iti.departure_date,
					iti.arrival_date,
					iti.base_price,
					iti.direction,
					iti.time_limit,
					iti.is_layover,
					iti.date_entered,
					iti.is_remind,
					iti.checkin_status,
					bk.booking_status
				FROM ec_booking_itineraries iti
				LEFT JOIN ec_flight_bookings bk ON bk.id = iti.booking_id AND bk.deleted = 0
				WHERE iti.booking_id = '" . $this->bean->id . "' 
					AND iti.deleted = 0 
					AND iti.add_type = 0
				ORDER BY iti.direction, iti.transit_order, iti.date_entered";

		$res = $this->bean->db->query($sql);
		$i = 0;
		$check_dep = $check_ret = false;
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$even_or_odd = ($i % 2 > 0) ? 'even' : 'odd';

			$airline_code = $airline_code_logo = $row['airline_code'];
			$img_style = 'style="width:45px"';
			if ($row['airline_code'] == 'VNA') {
				$airline_code = $airline_code_logo = 'VN';
			}
			if ($row['airline_code'] == 'VJA') {
				$airline_code = $airline_code_logo = 'VJ';
			}
			if ($row['airline_code'] == 'VNP') {
				$airline_code = 'BL';
				$airline_code_logo = 'VNP';
			}
			if ($row['airline_code'] == 'BBA')
				$airline_code = $airline_code_logo = 'QH';
			if ($row['airline_code'] == 'VTA') {
				$airline_code = 'VU';
				$airline_code_logo = 'VTA';
				$img_style = 'style="width:55px"';
			}

			$img_src = !$row['is_layover'] && !empty($airline_code_logo) ? '<img ' . $img_style . ' src="custom/themes/default/images/airline-icon-100x100/' . $airline_code_logo . '.png" alt="' . $airline_code . '" border="0" />' : '';

			if ($this->bean->ticket_type == '2')
				$img_src .= '<br />(<b>' . $row['airline_code'] . '</b>)';
			$html .= '<tr class="' . $even_or_odd . '">';

			// Checkbox auto book
			$booking_cutoff_time = (strtotime($row['departure_date']) - time()) - 10800;
			if ($row['direction'] == '0' && $check_dep === false && $booking_cutoff_time > 0) {
				$html .= '<td data-label="Autobook" class="text-center"><input type="checkbox" name="check-itinerary" class="check-itinerary" data-id="' . $row['id'] . '" title="Autobook" /></td>';
				$check_dep = true;
			} else if ($row['direction'] == '1' && $check_ret === false && $booking_cutoff_time > 0) {
				$html .= '<td data-label="Autobook" class="text-center"><input type="checkbox" name="check-itinerary" class="check-itinerary" data-id="' . $row['id'] . '" title="Autobook" /></td>';
				$check_ret = true;
			} else
				$html .= '<td data-label="" class="text-center"><input type="checkbox" name="check-itinerary" class="check-itinerary" data-id="' . $row['id'] . '" title="" /></td>';

			$flight_number = $row['flight_number'] ?? '';
			$html .= '<td data-label="STT" class="text-center fw-semibold">' . ($i + 1) . '</td>
				<td data-label="Chiều" class="text-center" id="detail_direction' . $i . '" data-direction="' . $row['direction'] . '">' . $app_list_strings['bk_direction_list'][$row['direction']] . '</td>
				<td data-label="Mã hãng" class="text-center dt_airline" id="detail_airline' . $i . '" data-airline="' . $row['airline_code'] . '">' . $img_src . '</td>
				<td data-label="Số hiệu" class="text-center">' . $flight_number . '</td>
				<td data-label="Hạng vé" class="text-center ticket_class' . $row['direction'] . '">' . $row['ticket_class'] . '</td>
				<td data-label="Nơi đi" class="text-center">' . $row['departure'] . '</td>
				<td data-label="Nơi đến" class="text-center">' . $row['arrival'] . '</td>
				<td data-label="Ngày giờ đi" class="text-center">' . (trim($row['departure_date']) != '' ? date($date_format . ' H:i', strtotime($row['departure_date'])) : '') . '</td>
				<td data-label="Ngày giờ đến" class="text-center">' . (trim($row['arrival_date']) != '' ? date($date_format . ' H:i', strtotime($row['arrival_date'])) : '') . '</td>
				<td data-label="Hạn giữ chỗ" class="text-center">' . (trim($row['time_limit']) != '' ? date($date_format . ' H:i', strtotime($row['time_limit'])) : '') . '</td>
				<td data-label="Giá cơ bản" class="text-end">' . format_number($row['base_price']) . '</td>';

			if ($row['is_layover'] == 0 && $use_mail_eticket) {
				// REMIND BUTTON
				$remind_btn = '';
				if ($row['is_remind'] == 0 && ($row['booking_status'] == 7 || $row['booking_status'] == 8)) {
					$remind_btn .= '<div class="dropdown">
						<button class="btn btn-primary-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
							Remind
						</button>
						<ul class="dropdown-menu dropdown-menu-end box-list">
							<li class="box-item">
								<a class="dropdown-item btn-remind btn-voiceip-calling btn-voiceip-calling-teco" iti_id="' . $row['id'] . '" booking_id="' . $this->bean->id . '" booking_name="' . $this->bean->name . '" phone="' . $this->bean->phone . '" id="btnRemind" href="javascript:void(0)">Gọi nhắc nhở lịch bay</a>
							</li>
							<li class="box-item">
								<a class="dropdown-item btn-remind btn-voiceip-calling btn-voiceip-calling-zalo" iti_id="' . $row['id'] . '" booking_id="' . $this->bean->id . '" booking_name="' . $this->bean->name . '" phone="' . $this->bean->phone . '" id="btnRemind" href="javascript:void(0)">Gọi nhắc nhở lịch bay (Zalo)</a>
							</li>
							<li class="box-item">
								<a class="dropdown-item confirm-remind" iti_id="' . $row['id'] . '" booking_id="' . $this->bean->id . '" id="confirm-remind" href="javascript:void(0)">Đã nhắc nhở khách</a>
							</li>
						</ul>
					</div>';
				}

				// SMS BUTTON
				$journey_name = ucfirst(myRemoveUnicodeChars($airport_list[$row['departure']] ?? '')) . ' - ' . ucfirst(myRemoveUnicodeChars($airport_list[$row['arrival']] ?? ''));
				$sms_depdate = date('d/m/Y H:i', strtotime($row['departure_date']));
				$sms_btn = '<input type="button" name="btnSendSMS" value="SMS" title="Send SMS" class="btn btn-primary-2"
					direction="' . $row['direction'] . '" 
					flightno="' . $flight_number . '"
					journey="' . $journey_name . '"
					date="' . explode(' ', $sms_depdate)[0] . '"
					time="' . explode(' ', $sms_depdate)[1] . '"
					applied_pass="' . ($row['direction'] == 0 ? $departure_applied_pass : $arrival_applied_pass) . '"
				/>';

				// TT Checkin status
				$checkin_status = '';
				if ((int)$row['checkin_status'] !== 2 && in_array((int)$this->bean->booking_status, [3, 7, 8])) {
					$jour_name = $row['departure'] . '-' . $row['arrival'];
					$checkin_status = '<select class="select-box checkin_status_iti" iti_id="' . $row['id'] . '" iti_name="' . $jour_name . '" booking_id="' . $this->bean->id . '" record_name="' . $this->bean->name . '">' . get_select_options_with_id($app_list_strings['booking_checkin_status_list'], (int)$row['checkin_status']) . '</select>';
				}

				$html .= '<td data-label="" class="text-center">
					<form action="index.php?print=true" method="post" name="frmPrintEticket" id="frmPrintEticket' . $i . '" target="_blank">
						<input type="hidden" name="module" value="EC_Flight_Bookings" />
						<input type="hidden" name="action" value="printeticket" />
						<input type="hidden" name="record" value="' . $this->bean->id . '" />
						<input type="hidden" name="return_module" value="EC_Flight_Bookings" />
						<input type="hidden" name="return_action" value="" />
						<input type="hidden" name="return_id" value="' . $this->bean->id . '" />
						<input type="hidden" name="booking" value="' . $this->bean->name . '" />
						<input type="hidden" name="booking_id" value="' . $this->bean->id . '" />
						<input type="hidden" name="contact_email" value="' . $this->bean->email . '" />
						<input type="hidden" name="contact_name" value="' . $this->bean->contact_name . '" />
						<input type="hidden" name="itinerary_id" value="' . $row['id'] . '" />
						<input type="hidden" name="direction" value="' . $row['direction'] . '" />
						<input type="hidden" name="airline_code" value="' . $row['airline_code'] . '" />
						<input type="hidden" name="ticket_type" value="' . $this->bean->ticket_type . '" />
						<div class="action-button-ticket d-flex gap-2 align-items-center justify-content-center">
							' . $sms_btn . '
							' . $remind_btn . '
							' . $checkin_status . '
						</div>
					</form>
				</td>';
			} else {
				$html .= '<td data-label="" class="text-center">&nbsp;</td>';
			}
			$html .= '</tr>';

			// Load description
			if (isset($row['description']) && !empty($row['description'])) {
				$html .= '<tr>
					<td colspan="15" style="font-style:italic;font-weight:bold">' . $row['description'] . '</td>
				</tr>';
			}

			if ($row['direction'] == '0') {
				$this->_outbound_airline = $row['airline_code'];
				$this->_outbound_ticket_class = $row['ticket_class'];
			}

			if ($row['direction'] == '1') {
				$this->_inbound_airline = $row['airline_code'];
				$this->_inbound_ticket_class = $row['ticket_class'];
			}
			$i++;
		}

		/* CHANGE FLIGHT TIME INFO */
		$html .= $this->populateEditedInfo(3);
		$html .= '</table>';

		/* POPUP LÝ DO THẮNG THUA */
		$html .= $this->populateWinLoseTemplate();

		/* POPUP PRINT TICKET */
		$html .= $this->populatePrintLanguage();

		/* POPUP WORKING PROCESS NOTE */
		$html .= $this->populateWorkingProcessNote();

		/* POPUP REMIND */
		$html .= $this->populateRemindTemplate();

		return $html;
	}

	// Direction (0: lượt đi ; 1: lượt về)	
	private function getAppliedPassengerIti($booking_id, $direction)
	{
		$sql_pass = "SELECT GROUP_CONCAT(id) AS applied_pass
			FROM ec_booking_passengers 
			WHERE booking_id = '$booking_id' AND deleted = 0
				AND id NOT IN (
					SELECT parent_detail_id
					FROM ec_booking_passengers 
					WHERE booking_id = '$booking_id' AND add_type = 2 AND deleted = 0
				) 
				AND id NOT IN (
					SELECT assigned_user_id
					FROM ec_booking_itineraries
					WHERE booking_id = '{$this->bean->id}'
						AND direction = '$direction'
						AND add_type = 3
						AND deleted = 0
				)
			GROUP BY booking_id
		";
		$res_pass = $this->bean->db->query($sql_pass);
		$row_pass = $this->bean->db->fetchByAssoc($res_pass);

		// Check null trong th tất cả passenter đều bị thay đổi hành trình
		$row_pass['applied_pass'] = (isset($row_pass['applied_pass'])) ? $row_pass['applied_pass'] : '';

		return $row_pass['applied_pass'];
	}

	/**
	 * Render table ticket details
	 */
	private function populateLineDetails()
	{
		global $app_list_strings, $locale;

		$html = '';
		$html .= '<table id="line_details_tbl" border="0" cellpadding="0" cellspacing="0" class="table-config table-line-details table-details__booking">
					<thead>
						<tr>
							<th scope="col" width="3%"></th> 
							<th scope="col" width="3%">STT</th> 
							<th scope="col" width="7%">Chiều</th> 
							<th scope="col" width="7%">Loại HK</th>
							<th scope="col" width="4%">SL</th>
							<th scope="col" width="8%">Giá cơ bản</th>
							<th scope="col" width="6%">VAT</th>
							<th scope="col" width="8%">Phí sân bay</th>
							<th scope="col" width="8%">Phí admin</th>
							<th scope="col" width="7%" title="Phí dịch vụ">Phí DV</th>
							<th scope="col" width="8%">Thành tiền</th>
							<th scope="col" width="8%">Giá mua</th>
							<th scope="col" width="8%">Chiết khấu</th>
							<th scope="col" width="8%">Phí xuất vé</th>
							<th scope="col">NCC</th>
						</tr>
					</thead>';

		$sql = "SELECT d.id,
					d.passenger_type,
					d.quantity,
					d.unit_price,
					d.tax_and_fee,
					d.total_price,
					d.description,
					d.direction,
					d.service_fee,
					d.admin_fee,
					d.vat_admin,
					d.admin_fee_no_vat,
					d.airport_fee,
					d.total_bought_price,
					d.discount_amount,
					d.fee_bought,
					d.supplier_id,
					IF(d.supplier_id IS NOT NULL, (SELECT a.name FROM accounts a WHERE a.deleted=0 AND a.id=d.supplier_id LIMIT 1), '') AS supplier,
					d.supplier_discount 
				FROM ec_booking_details d
				WHERE d.booking_id = '{$this->bean->id}' AND d.deleted = 0 
				ORDER BY d.direction, d.passenger_type, d.date_entered ";

		$i = 0;
		$total_qty = $total_supplier_discount = 0;
		$total_basic_amount = 0;
		$total_vat_amount = 0;
		$total_airport_amount = 0;
		$total_admin_amount = 0;
		$total_service_amount = 0;
		$total_issue_fee = 0;

		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$admin_fee_inf = '';
			if ($row['admin_fee_no_vat'] > 0) {
				$admin_fee_inf = '<div class="admin_fee_no_vat--wrap d-flex align-items-center justify-content-between">
								<span class="text-start">TVAT:</span>
								<span class="text-end">' . format_number($row['admin_fee_no_vat']) . '</span>
							</div>
							<div class="vat_admin--wrap d-flex align-items-center justify-content-between">
								<span class="text-start">VAT:</span>
								<span class="text-end">' . format_number($row['vat_admin']) . '</span>
							</div>';
			}

			$even_or_odd = ($i % 2 > 0) ? 'even' : 'odd';
			$html .= '<tr class="' . $even_or_odd . '">
				<td data-label="Autobook" class="text-center"><input type="checkbox" name="check-detail" class="check-journey" data-id="' . $row['id'] . '" title="Autobook" /></td>
				<td data-label="STT" class="text-center fw-semibold">' . ($i + 1) . '</td>
				<td data-label="Chiều" class="text-center">' . $app_list_strings['bk_direction_list'][(int) $row['direction']] . '</td>
				<td data-label="Loại HK" class="text-center">' . $app_list_strings['passenger_type_list'][(int) $row['passenger_type']] . '</td>
				<td data-label="SL" class="text-center">' . format_number($row['quantity']) . '</td>
				<td data-label="Giá cơ bản" class="text-end">' . format_number($row['unit_price']) . '</td>
				<td data-label="VAT" class="text-end">' . format_number($row['tax_and_fee']) . '</td>
				<td data-label="Phí sân bay" class="text-end">' . format_number($row['airport_fee']) . '</td>
				<td data-label="Phí admin" class="text-end"><div class="admin_fee">' . format_number($row['admin_fee']) . '</div>' . $admin_fee_inf . '</td>
				<td data-label="Phí dịch vụ" class="text-end">' . format_number($row['service_fee']) . '</td>
				<td data-label="Thành tiền" class="text-end" title="Đã gồm số lượng">' . format_number($row['total_price']) . '</td>
				<td data-label="Giá mua" class="text-end" title="Đã gồm số lượng">
					<input type="hidden" name="check_total_bought_price[]" id="check_total_bought_price' . $i . '" value="' . format_number($row['total_bought_price']) . '" />
					' . (ACLController::checkAccess('Bugs', 'list', true) ? format_number($row['total_bought_price']) : '&nbsp;') . '
				</td>
				<td data-label="Chiết khấu" class="text-end">' . format_number($row['supplier_discount']) . '</td>
				<td data-label="Phí xuất vé" class="text-end">' . format_number($row['fee_bought']) . '</td>
				<td data-label="NCC" class="text-start">
					<input type="hidden" name="check_supplier_id[]" id="check_supplier_id' . $i . '" value="' . $row['supplier_id'] . '" />
					<a href="index.php?module=Accounts&action=DetailView&record=' . $row['supplier_id'] . '" target="_blank">' . $row['supplier'] . '</a>
				</td>
			</tr>';

			$total_qty += $row['quantity'];
			$total_supplier_discount += $row['supplier_discount'];
			$total_basic_amount += $row['unit_price'];
			$total_vat_amount += $row['tax_and_fee'];
			$total_airport_amount += $row['airport_fee'];
			$total_admin_amount += $row['admin_fee'];
			$total_service_amount += $row['service_fee'];
			$total_issue_fee += $row['fee_bought'];

			$i++;
		}

		$sep = my_get_number_separators();
		$html .= '<tr class="footer-tr">
					<td class="hide-mobile show-landscape" colspan="4">Tổng:
						<input type="hidden" id="grp_seperator" name="grp_seperator" value="' . $sep[0] . '" />
						<input type="hidden" id="dec_seperator" name="dec_seperator" value="' . $sep[1] . '" />
						<input type="hidden" id="sig_digits" name="sig_digits" value="' . $locale->getPrecision() . '" />
					</td>
					<td data-label="Tổng số vé" class="text-center">' . format_number($total_qty) . '</td>
					<td data-label="Giá cơ bản" class="hide-mobile text-end show-landscape">' . format_number($total_basic_amount) . '</td>
					<td data-label="VAT" class="hide-mobile text-end show-landscape">' . format_number($total_vat_amount) . '</td>
					<td data-label="Phí sân bay" class="hide-mobile text-end show-landscape">' . format_number($total_airport_amount) . '</td>
					<td data-label="Phí admin" class="hide-mobile text-end show-landscape">' . format_number($total_admin_amount) . '</td>
					<td data-label="Phí DV" class="hide-mobile text-end show-landscape">' . format_number($total_service_amount) . '</td>
					<td data-label="Tổng thành tiền" class="text-end into_money">' . format_number($this->bean->subtotal_amount) . '</td>
					<td data-label="Tổng giá mua" class="text-end purchase_price">' . format_number($this->bean->total_bought_amount) . '</td>
					<td data-label="Tổng chiết khấu" class="text-end supplier_discount">' . format_number($total_supplier_discount) . '</td>
					<td data-label="Phí xuất vé" class="text-end hide-mobile show-landscape">' . format_number($total_issue_fee) . '</td>
					<td class="text-end hide-mobile show-landscape"></td>
				</tr>';
		$html .= '</table>';
		$html .= "<input type='hidden' id='supplier_option_val' value='" . myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', '', 'id', 'AND account_type=\'Supplier\' AND is_stop_tracking=0') . "'>";
		$this->ss->assign('LINE_DETAILS', $html);
	}

	/**
	 * Render table default passengers
	 * 
	 * @return string HTML
	 */
	public function populateLinePassengers()
	{
		global $app_list_strings, $timedate;
		$date_format = $timedate->get_date_format();

		$html = '<table id="tbl_pax" border="0" cellpadding="0" cellspacing="0" class="table-config table-pax table-details__booking">
				<thead>
					<tr>
						<th scope="col" width="2%"><input type="checkbox" id="select-all-passengers"/></th>
						<th scope="col" width="3%">STT</th>
						<th scope="col" width="7%">Loại</th>
						<th scope="col" width="7%">Giới tính</th>
						<th scope="col" width="16%">Họ tên</th>
						<th scope="col" width="8%">Ngày sinh</th>
						<th scope="col" width="12%">CCCD/Passport</th>
						<th scope="col" width="10%">Số vé đi</th>
						<th scope="col" width="10%">Số vé về</th>
						<th scope="col" width="7%">PNR đi</th>
						<th scope="col" width="7%">PNR về</th>
					</tr>
				</thead><tbody>';

		$sql = "SELECT p.id
				,p.name
				,p.salutation
				,p.birthday
				,p.type
				,p.eticket_outbound
				,p.eticket_inbound
				,p.eluggage_outbound
				,p.eluggage_inbound
				,p.pnr_outbound
				,p.pnr_inbound
				,p.direction
				,p.luggage_index_outbound
				,p.luggage_index_inbound
				,p.luggage_price
				,p.luggage_price_inbound
				,p.luggage_purchase_no_vat
				,p.vat_luggage_purchase
				,p.luggage_purchase
				,p.luggage_purchase_text
				,p.luggage_purchase_inbound_no_vat
				,p.vat_luggage_purchase_inbound
				,p.luggage_purchase_inbound
				,p.luggage_purchase_text_inbound
				,p.hand_baggage_outbound
				,p.hand_baggage_inbound
				,p.supplier_id
				,IF(p.supplier_id IS NOT NULL, (SELECT a.name FROM accounts a WHERE a.id=p.supplier_id AND a.deleted=0 LIMIT 1), '') AS supplier
				,p.supplier_inbound_id
				,IF(p.supplier_inbound_id IS NOT NULL, (SELECT a.name FROM accounts a WHERE a.id=p.supplier_inbound_id AND a.deleted=0 LIMIT 1), '') AS supplier_inbound
				,p.add_type
				,p.parent_detail_id
				,p.date_entered
				,p.cic
				,p.passport_number
			FROM ec_booking_passengers p
			WHERE p.booking_id = '{$this->bean->id}'
				AND p.deleted = 0 
				AND (p.add_type NOT IN (1, 2) OR p.add_type IS NULL)
			ORDER BY p.type, p.date_entered";

		$res = $this->bean->db->query($sql);

		$i = 0;
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$even_or_odd = ($i % 2 > 0) ? 'even' : 'odd';

			$hide_cic = ($this->bean->ticket_type == 2 || $row['type'] == 2 || empty($row['cic'])) ? ' style="display:none" ' : '';
			$hide_passport = empty($row['passport_number']) ? ' style="display:none" ' : '';
			if (!empty($row['passport_number']) && empty($row['cic']) && $this->bean->ticket_type == 1 && $row['type'] != 2)
				$hide_cic = ' style="display:none" ';

			// Line 1
			$html .= '<tr class="psg-line ' . $even_or_odd . '" data-id="' . $row['id'] . '">
						<td data-label="Autobook" class="text-center">
							<input type="checkbox" name="check-passenger" class="check-passenger" data-id="' . $row['id'] . '" data-type="' . $row['type'] . '" title="Autobook" />
						</td>
						<td data-label="STT" class="text-center fw-semibold">' . ($i + 1) . '</td>
						<td data-label="Loại HK" class="passenger_type text-center" data="' . $row['type'] . '" class="text-center">' . $app_list_strings['passenger_type_list'][(int) $row['type']] . '</td>
						<td data-label="Danh xưng" class="passenger_salutation text-center" data="' . $row['salutation'] . '" class="text-center">' . $app_list_strings['passenger_salutation_list'][(int) $row['salutation']] . '</td>
						<td data-label="Họ tên" class="passenger_name text-start">
							<p class="fullname">' . $row['name'] . '</p>
						</td>
						<td data-label="Ngày sinh" class="passenger_birthdate text-center">
							<p class="birthdate">' . (isset($row['birthday']) && !empty($row['birthday']) && $row['birthday'] != '0000-00-00' ? date($date_format, strtotime($row['birthday'])) : '') . '</p>
						</td>
						<td data-label="Giấy tờ" class="passenger_id text-center">
							<p class="cic text-nowrap" data="' . $row['cic'] . '" ' . $hide_cic . ' title="CCCD">
								<span style="letter-spacing:0.65px">' . $row['cic'] . '</span>
							</p>
							<p class="passport text-nowrap" data="' . $row['passport_number'] . '" ' . $hide_passport . ' title="Passport">
								<span style="letter-spacing:0.65px">' . $row['passport_number'] . '</span>
							</p>
						</td>
						<td data-label="Số vé đi" class="text-center" class="eticket_outbound" content="' . strtoupper($row['eticket_outbound']) . '" row_no="' . $row['id'] . '">
							' . strtoupper($row['eticket_outbound']) . '
							<input type="hidden" name="eticket_outbound[]" id="eticket_outbound' . $i . '" value="' . strtoupper($row['eticket_outbound']) . '"  />
						</td>
						<td data-label="Số vé về" class="text-center" class="eticket_inbound" content="' . strtoupper($row['eticket_inbound']) . '" row_no="' . $row['id'] . '">
							' . strtoupper($row['eticket_inbound']) . '
							<input type="hidden" name="eticket_inbound[]" id="eticket_inbound' . $i . '" value="' . strtoupper($row['eticket_inbound']) . '"  />
						</td>
						<td data-label="PNR đi" class="text-center">
							' . strtoupper($row['pnr_outbound']) . '
							<input type="hidden" name="pnr_outbound[]" id="pnr_outbound' . $i . '" value="' . strtoupper($row['pnr_outbound']) . '"  />
						</td>
						<td data-label="PNR về" class="text-center">
							' . strtoupper($row['pnr_inbound']) . '
							<input type="hidden" name="pnr_inbound[]" id="pnr_inbound' . $i . '" value="' . strtoupper($row['pnr_inbound']) . '"  />
						</td>
					</tr>';

			// Line 2 (Baggage)
			$row['bookingName']         = $this->bean->name ?? '';
			$row['createdBy']             = $this->bean->created_by ?? '';
			$row['airlineCodeOutbound'] = $this->_outbound_airline ?? '';
			$row['ticketClassOutbound'] = $this->_outbound_ticket_class ?? '';
			$row['airlineCodeInbound']  = $this->_inbound_airline ?? '';
			$row['ticketClassInbound']  = $this->_inbound_ticket_class ?? '';
			$html .= $this->bean->generatePassengerBaggageInfo($row, $i);

			$i++;
		}

		$html .= '</tbody>
			</table>';

		return $html;
	}

	/**
	 * Render table default receipt voucher
	 * @return string HTML
	 */
	private function populateLineRelateVoucher()
	{
		global $db;

		$html = '<table id="tbl_pax" border="0" cellpadding="0" cellspacing="0" class="table-config table-details__booking">
                    <thead>
                        <tr>
                            <th scope="col" width="5%">STT</th>
                            <th scope="col" width="12%">Ngày chứng từ</th>
                            <th scope="col" width="12%">Loại phiếu</th>
                            <th scope="col" width="12%">Tên phiếu</th>
                            <th scope="col" width="12%">Tình trạng</th>
                            <th scope="col" width="12%">Số tiền</th>
                            <th scope="col">Ghi chú</th>
                        </tr>
                    </thead>';

		$sql = "SELECT id, name, rv_status, amount, description, ngaychungtu, guest_name, loai_thu
				FROM ec_receipt_voucher
				WHERE booking_id = '" . $this->bean->id . "'
				AND deleted = 0		
		";

		$booking_id = $db->quote($this->bean->id);

		$sql = "(
					SELECT
						id,
						name,
						rv_status as status,
						'receipt_voucher_status_list' AS status_list,
						'receipt_voucher_status_color_list' AS status_list_color,
						amount,
						description,
						ngaychungtu,
						'EC_Receipt_Voucher' AS parent_type
					FROM ec_receipt_voucher
					WHERE booking_id = '$booking_id'
					AND deleted = 0
				)
				UNION ALL
				(
					SELECT
						id,
						name,
						tinhtrang AS status,
						'tinhtranghoanve_list' AS status_list,
						'tinhtranghoanvecolor_list' AS status_list_color,
						tongtienkhach AS amount,
						description,
						ngaychungtu,
						'EC_HoanVe' AS parent_type
					FROM ec_hoanve
					WHERE booking_id = '$booking_id'
					AND deleted = 0
				)
				ORDER BY ngaychungtu DESC";

		$res = $db->query($sql);
		$count = $db->countRows($res);

		if ($count > 0) {
			$i = 1;
			while ($row = $db->fetchByAssoc($res)) {
				$loai_phieu = '';
				if ($row['parent_type'] == 'EC_HoanVe') {
					$loai_phieu = 'Phiếu hoàn';
				} else if ($row['parent_type'] == 'EC_Receipt_Voucher') {
					$loai_phieu = 'Phiếu thu';
				}

				$html .= '<tr>
							<td data-label="STT" class="text-center">' . $i . '</td>
							<td data-label="Ngày chứng từ" class="text-center">
									' . date('d-m-Y', strtotime($row['ngaychungtu'])) . '
							</td>
							<td data-label="Loại phiếu" class="text-center">
								' . $loai_phieu . '
							</td>
							<td data-label="Tên phiếu" class="text-center">
								<a href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . $row['id'] . '" target="_blank">
									' . $row['name'] . '
								</a>
							</td>
							<td data-label="Tình trạng" class="text-center fw-semibold" style="color:' . $GLOBALS['app_list_strings'][$row['status_list_color']][$row['status']] . ';">' . $GLOBALS['app_list_strings'][$row['status_list']][$row['status']] . '</td>
							<td data-label="Số tiền" class="text-center">' . format_number($row['amount']) . '</td>
							<td data-label="Ghi chú" class="text-start text-wrap">' . $row['description'] . '</td>
						</tr>';

				$i++;
			}
		} else {
			$html .= '<tr>
						<td colspan="8" class="text-start fw-semibold">Không có chứng từ liên quan.</td>
					</tr>';
		}

		$html .= '</table>';

		return $html;
	}

	private function getWinLoseReasonRadio($select, $reason_type)
	{
		$sql = "SELECT id, name
                FROM ec_lydothangthua
                WHERE loailydo = '$reason_type' AND deleted = 0
                ORDER BY date_entered ";

		$res = $this->bean->db->query($sql);
		$html = '';
		$i = 1;
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			if ($i % 2 != 0) {
				$html .= '<tr>';
			}
			$checked = ($row['id'] == $select || $i == 1) ? 'checked' : '';
			$html .= '<td width="50%"><label for="rad-' . $row['id'] . '"><input ' . $checked . ' type="radio" name="radWinLoseReason" txt="' . $row['name'] . '" id="rad-' . $row['id'] . '" value="' . $row['id'] . '"><span class="label_WinLoseReason"> ' . $row['name'] . '</span></label></td>';
			if ($i % 2 == 0) {
				$html .= '</tr>';
			}
			$i++;
		}

		return $html;
	}

	/**
	 * Get voucher applied
	 * 
	 * @param string $booking_id
	 * @return array
	 */
	public function getVoucherApplied($booking_id = '')
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
					bv.discount_amount
				FROM bookings_vouchers bv
					LEFT JOIN ec_vouchers v ON v.id = bv.voucher_id
				WHERE bv.booking_id = '$booking_id'
					AND bv.deleted = 0";

		$res = $this->bean->db->query($sql);
		$results = [];
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$results[] = $row;
		}
		return $results;
	}

	public function checkIsPaidNote($booking_id)
	{
		$sql = "SELECT COUNT(id) 
				FROM ec_working_process
				WHERE parent_id = '$booking_id' AND paid = 1 AND deleted = 0";
		$res = $this->bean->db->getOne($sql);
		if ($res > 0)
			return true;
		return false;
	}

	/**
	 * Get resolved itineraries for the print ticket popup.
	 * Prefers rescheduled (add_type=3, latest sabre_logs) over originals (add_type=0).
	 */
	public function getResolvedItinerariesForPopup()
	{
		global $app_list_strings, $timedate;
		$date_format = $timedate->get_date_format();
		$bookingId = $this->bean->id;
		$results = [];

		// Check which directions have been rescheduled
		$rescheduledDirections = [];
		$sqlCheck = "SELECT DISTINCT direction FROM ec_booking_itineraries
			WHERE booking_id = '$bookingId' AND add_type = 3 AND deleted = 0";
		$resCheck = $this->bean->db->query($sqlCheck);
		while ($row = $this->bean->db->fetchByAssoc($resCheck)) {
			$rescheduledDirections[] = (int)$row['direction'];
		}

		$fields = "i.id, i.departure_date, i.flight_number, i.departure, i.arrival, i.airline_code, i.direction";

		if (empty($rescheduledDirections)) {
			// No rescheduled — just get originals
			$sql = "SELECT MIN(i.id) AS id, i.departure_date, i.flight_number, i.departure, i.arrival, i.airline_code, i.direction
				FROM ec_booking_itineraries i
				WHERE i.booking_id = '$bookingId' AND i.deleted = 0 AND i.add_type = 0
				GROUP BY i.direction, i.flight_number, i.departure_date
				ORDER BY i.direction, i.departure_date";
		} else {
			$rescheduledDirList = implode(',', $rescheduledDirections);

			// Original itineraries for directions NOT rescheduled
			$sqlUnchanged = "SELECT $fields FROM ec_booking_itineraries i
				WHERE i.booking_id = '$bookingId' AND i.deleted = 0 AND i.add_type = 0
				AND i.direction NOT IN ($rescheduledDirList)";

			// Latest rescheduled itineraries — GROUP BY to deduplicate per-passenger rows
			$sqlRescheduled = "SELECT MIN(i.id) AS id, i.departure_date, i.flight_number, i.departure, i.arrival, i.airline_code, i.direction
				FROM ec_booking_itineraries i
				INNER JOIN (
					SELECT direction, MAX(sabre_logs) AS max_logs
					FROM ec_booking_itineraries
					WHERE booking_id = '$bookingId' AND add_type = 3 AND deleted = 0
					GROUP BY direction
				) latest ON i.direction = latest.direction AND i.sabre_logs = latest.max_logs
				WHERE i.booking_id = '$bookingId' AND i.add_type = 3 AND i.deleted = 0
				GROUP BY i.direction, i.flight_number, i.departure_date";

			$sql = "($sqlUnchanged) UNION ALL ($sqlRescheduled) ORDER BY direction, departure_date";
		}

		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$directionLabel = $app_list_strings['bk_direction_list'][(int)$row['direction']] ?? '';
			$airlineCode = $row['airline_code'] ?? '';
			// Map internal codes to display codes
			if ($airlineCode == 'VNA') $airlineCode = 'VN';
			elseif ($airlineCode == 'VJA') $airlineCode = 'VJ';
			elseif ($airlineCode == 'VNP') $airlineCode = 'BL';
			elseif ($airlineCode == 'BBA') $airlineCode = 'QH';
			elseif ($airlineCode == 'VTA') $airlineCode = 'VU';

			$airlineInfo = function_exists('myGetAirlineInfo2') ? myGetAirlineInfo2($airlineCode, 'CODE') : ['data' => [['name' => $airlineCode]]];
			$airlineName = (!empty($airlineInfo['data'][0]['name'])) ? $airlineInfo['data'][0]['name'] : $airlineCode;

			$results[] = [
				'id' => $row['id'],
				'direction' => (int)$row['direction'],
				'directionLabel' => $directionLabel,
				'airline' => $airlineCode,
				'airlineName' => $airlineName,
				'flightNo' => $row['flight_number'] ?? '',
				'departure' => $row['departure'] ?? '',
				'arrival' => $row['arrival'] ?? '',
				'depDate' => !empty($row['departure_date']) ? date($date_format . ' H:i', strtotime($row['departure_date'])) : '',
			];
		}

		return $results;
	}

	/**
	 * Check if itinerary changes (add_type=3) are per-passenger (assigned_user_id differs).
	 * Returns true when at least one add_type=3 record has a non-empty assigned_user_id
	 * that matches an actual passenger ID (i.e. changes target specific passengers).
	 */
	function hasPerPassengerItineraryChanges()
	{
		$bookingId = $this->bean->id;
		// Count distinct assigned_user_id values linked to actual passengers
		$sql = "SELECT COUNT(DISTINCT i.assigned_user_id) AS cnt
			FROM ec_booking_itineraries i
			INNER JOIN ec_booking_passengers p ON p.id = i.assigned_user_id AND p.booking_id = i.booking_id AND p.deleted = 0
			WHERE i.booking_id = '$bookingId' AND i.add_type = 3 AND i.deleted = 0
			AND i.assigned_user_id IS NOT NULL AND i.assigned_user_id != ''";
		$cnt = (int)$this->bean->db->getOne($sql);
		return $cnt > 0;
	}

	/**
	 * Get per-passenger itinerary data for the popup.
	 * Returns an array of passengers, each with their resolved itineraries.
	 * For each direction: use add_type=3 for that passenger if exists, else fallback to add_type=0.
	 */
	function getPerPassengerItinerariesForPopup()
	{
		global $app_list_strings, $timedate;
		$date_format = $timedate->get_date_format();
		$bookingId = $this->bean->id;
		$results = [];

		// Get active passengers (original not superseded + final renamed)
		$supersededIds = "SELECT parent_detail_id FROM ec_booking_passengers
			WHERE booking_id = '$bookingId' AND add_type = 2 AND deleted = 0
			AND parent_detail_id IS NOT NULL";

		$notLatestRenames = "SELECT p2.id FROM ec_booking_passengers p2
			INNER JOIN (
				SELECT parent_detail_id, MAX(date_entered) AS max_date
				FROM ec_booking_passengers
				WHERE booking_id = '$bookingId' AND add_type = 2 AND deleted = 0
				AND parent_detail_id IS NOT NULL
				GROUP BY parent_detail_id
			) latest ON p2.parent_detail_id = latest.parent_detail_id
			WHERE p2.booking_id = '$bookingId' AND p2.add_type = 2 AND p2.deleted = 0
			AND p2.date_entered < latest.max_date";

		$sqlPax = "SELECT p.id, p.name, p.salutation, p.type, p.pnr_outbound
			FROM ec_booking_passengers p
			WHERE p.booking_id = '$bookingId' AND p.deleted = 0
				AND (p.add_type NOT IN (1, 2) OR p.add_type IS NULL)
				AND p.id NOT IN ($supersededIds)
			UNION
			SELECT p.id, p.name, p.salutation, p.type, p.pnr_outbound
			FROM ec_booking_passengers p
			WHERE p.booking_id = '$bookingId' AND p.add_type = 2 AND p.deleted = 0
				AND p.id NOT IN ($supersededIds)
				AND p.id NOT IN ($notLatestRenames)
			ORDER BY type, name";

		$resPax = $this->bean->db->query($sqlPax);

		// Get original itineraries (add_type=0) for fallback
		$sqlOrig = "SELECT i.id, i.departure_date, i.flight_number, i.departure, i.arrival, i.airline_code, i.direction
			FROM ec_booking_itineraries i
			WHERE i.booking_id = '$bookingId' AND i.deleted = 0 AND i.add_type = 0
			ORDER BY i.direction, i.departure_date";
		$resOrig = $this->bean->db->query($sqlOrig);
		$originalItineraries = [];
		while ($rowOrig = $this->bean->db->fetchByAssoc($resOrig)) {
			$dir = (int)$rowOrig['direction'];
			if (!isset($originalItineraries[$dir])) $originalItineraries[$dir] = [];
			$originalItineraries[$dir][] = $rowOrig;
		}

		// Get all directions
		$allDirections = array_keys($originalItineraries);
		sort($allDirections);

		while ($pax = $this->bean->db->fetchByAssoc($resPax)) {
			$paxId = $pax['id'];
			$salutationText = $app_list_strings['passenger_salutation_list'][(int)$pax['salutation']] ?? '';

			$paxItineraries = [];
			foreach ($allDirections as $dir) {
				// Try per-passenger change (add_type=3, assigned_user_id = this passenger)
				$sqlChanged = "SELECT i.id, i.departure_date, i.flight_number, i.departure, i.arrival, i.airline_code, i.direction
					FROM ec_booking_itineraries i
					WHERE i.booking_id = '$bookingId'
						AND i.direction = $dir
						AND i.add_type = 3
						AND i.assigned_user_id = '$paxId'
						AND i.deleted = 0
					ORDER BY i.sabre_logs DESC
					LIMIT 1";
				$resChanged = $this->bean->db->query($sqlChanged);
				$rowChanged = $this->bean->db->fetchByAssoc($resChanged);

				if ($rowChanged) {
					$paxItineraries[] = $this->formatItineraryForPopup($rowChanged, $date_format);
				} else {
					// Fallback to original
					if (isset($originalItineraries[$dir])) {
						foreach ($originalItineraries[$dir] as $origRow) {
							$paxItineraries[] = $this->formatItineraryForPopup($origRow, $date_format);
						}
					}
				}
			}

			$results[] = [
				'passengerId' => $paxId,
				'passengerName' => trim($pax['name']),
				'salutation' => $salutationText,
				'type' => (int)$pax['type'],
				'itineraries' => $paxItineraries,
			];
		}

		return $results;
	}

	/**
	 * Format a single itinerary row for popup JSON (shared by multiple popup methods).
	 */
	function formatItineraryForPopup($row, $date_format)
	{
		global $app_list_strings;
		$directionLabel = $app_list_strings['bk_direction_list'][(int)$row['direction']] ?? '';
		$airlineCode = $row['airline_code'] ?? '';
		if ($airlineCode == 'VNA') $airlineCode = 'VN';
		elseif ($airlineCode == 'VJA') $airlineCode = 'VJ';
		elseif ($airlineCode == 'VNP') $airlineCode = 'BL';
		elseif ($airlineCode == 'BBA') $airlineCode = 'QH';
		elseif ($airlineCode == 'VTA') $airlineCode = 'VU';

		return [
			'id' => $row['id'],
			'direction' => (int)$row['direction'],
			'directionLabel' => $directionLabel,
			'airline' => $airlineCode,
			'flightNo' => $row['flight_number'] ?? '',
			'departure' => $row['departure'] ?? '',
			'arrival' => $row['arrival'] ?? '',
			'depDate' => !empty($row['departure_date']) ? date($date_format . ' H:i', strtotime($row['departure_date'])) : '',
		];
	}

	function populateEditedInfo($type)
	{
		$html = '';
		switch ($type) {
			case 2:
				$html = '<tfoot><tr class="footer-tr edited_pass_line hide-mobile"><td class="text-start" colspan="13"><b>Thông tin hành khách có thay đổi:</b> <span id="no-change__edit-pass"></span></td></tr></tfoot>';
				break;
			case 3:
				$html = '<tfoot><tr class="footer-tr edited_iti_line hide-mobile"><td class="text-start" colspan="13"><b>Thông tin đổi ngày bay / hành trình:</b><span id="no-change__edit-iti"></span></td></tr></tfoot>';
				break;
		}
		return $html;
	}

	function populateSMSTemplate()
	{
		$source = 'Tim chuyen bay';
		$html = '
			<div id="dialog_send_sms" class="dialog-confirm" title="Gửi SMS" style="display:none; border-radius:0">
				<div class="wrap-type">
					<h3 class="subtitle" style="text-align:center">Chọn mẫu tin nhắn</h3>
					<div class="d-flex align-items-center justify-content-between" style="height:25px;">
						<div>
							<input type="radio" class="form-check-input m-0" id="send_sms_journey" name="sms_type" value="send_sms_journey">
							<label for="send_sms_journey" class="form-check-label">Tin nhắn hành trình</label>
						</div>
						<div>
							<input type="radio" class="form-check-input m-0" id="send_sms_call" name="sms_type" value="send_sms_call">
							<label for="send_sms_call" class="form-check-label">Tin nhắn cuộc gọi</label>
						</div>
						<div>
							<input type="radio" class="form-check-input m-0" id="send_sms_payment" name="sms_type" value="send_sms_payment">
							<label for="send_sms_payment" class="form-check-label">Tin nhắn thanh toán</label>
						</div>
						<div>
							<input type="radio" class="form-check-input m-0" id="send_sms_code" name="sms_type" value="send_sms_code">
							<label for="send_sms_code" class="form-check-label">Tin nhắn code vé</label>
						</div>
					</div>
				</div>
				<div class="wrap-form mt-3">
					<table cellpadding="0" cellspacing="0" border="0" class="table-config table-sendsms">
						<tr class="tr-select-payment-templates" style="display:none">
							<td class="text-label">Mẫu tin:</td>
							<td> 
								<select class="box-select w-100" id="SmsTemplateList" name="SmsTemplateList">
									<option value="">----- Chọn ngân hàng -----</option>
									' . $this->getSMSPaymentTemplate() . '
								</select>
							</td>
						</tr>
						<tr>
							<td valign="top" class="text-label">Nội dung:</td>
							<td>
								<p class="sms_content_display" id="sms_content_display"><i>Chưa có nội dung</i></p>
								<textarea class="box-textarea" rows="5" name="sms_content" id="sms_content" style="display:none"></textarea>
							</td>
						</tr>
						<tr>
							<td class="text-label">Gửi đến:</td>
							<td>
								<input type="text" name="send_sms_to" id="send_sms_to" value="' . $this->bean->phone . '" maxlength="20" style="width:120px"/>
								<button class="btn btn-primary" type="button" id="btn-confirm-send-sms"
									booking_id="' . $this->bean->id . '"
									booking_name="' . $this->bean->name . '"
									booking_source="' . $source . '"
									>Gửi SMS
								</button>
								<button class="btn btn-secondary" type="button" id="copy-sms">Copy</button>
								<i class="text-danger">Vui lòng kiểm tra kỹ càng nội dung trước khi gửi</i>
							</td>
						</tr>
					</table>
				</div>
			</div>';
		echo $html;
	}

	function getSMSPaymentTemplate()
	{
		// Lấy những stk đang theo dõi
		$sql = 'SELECT ba.account_number AS account,
				ba.account_holder AS owner,
				b.short_name AS short_name,
				ba.name 
			FROM ec_bank_account ba 
				INNER JOIN ec_banks b ON b.id = ba.bank_id AND is_sms = 1
			WHERE ba.deleted = 0 AND ba.unfollow = 0
			ORDER BY IF(ba.sort IS NULL OR ba.sort = "", 100, ba.sort)';

		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$banks[] = [
				'short_name' => $row['short_name'],
				'account' => $row['account'],
				'owner' => $row['owner'],
				'name' => $row['name']
			];
		}

		$html = '';  // Ngan hang.{0,90}. So tien.{0,40} Noi dung.{0,80}
		foreach ($banks as $bank) {
			$optVal = 'Ngan hang: ' . $bank['short_name'];
			if (!empty($bank['branch'])) {
				$optVal .= ' - ' . myRemoveUnicodeChars($bank['branch']);
			}
			$optVal .= ' - So TK: ' . $bank['account'];
			$optVal .= ' - ' . ucwords(myRemoveUnicodeChars($bank['owner']));

			$total_amount = number_format($this->bean->total_amount, 0, ',', '.');
			$optVal .= '. So tien: ' . $total_amount . ' VND. ';
			// Ngày 02-02-2023 đổi nội dung từ thanh toan + tên booking -> thanh toán + SĐT
			$optVal .= 'Noi dung: thanh toan ' . $this->bean->phone;

			$html .= '<option value="' . $optVal . '">' . $bank['name'] . '</option>';
		}
		return $html;
	}

	function populateRemindTemplate()
	{
		$html = '
			<div id="dlgRemind" style="display:none;" title="Thông báo lịch bay">
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td style="text-align:left; vertical-align:top;">
							<textarea id="txtRemind" name="txtRemind" rows="10" style="font-size:13px"></textarea>
						</td>
					</tr>
					<tr>
						<td class="text-end">
							<div class="d-flex align-items-center gap-2 justify-content-end mt-2">
								<input type="hidden" name="current-journey-id" id="current-journey-id" value="">
								<input type="button" class="btn btn-primary" name="btn-confirm-remind" id="btn-confirm-remind" value="Đồng ý" title="Đồng ý" />
								<input type="button" class="btn btn-danger" name="btn-cancel-remind" id="btn-cancel-remind" value="Hủy" title="Hủy" />
							</div>
						</td>
					</tr>
				</table>
			</div>';
		return $html;
	}

	function populateWinLoseTemplate()
	{
		$html = '
			<div id="dlgLyDoThangThua" style="display:none;" title="Xác nhận hủy Booking">
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td style="width:15%; text-align:left; vertical-align:top; font-weight:600;" class="text-label">Lý do:</td>
						<td style="width:85%; text-align:left; vertical-align:top;">
							<table class="win-lose-radio" border="0" cellpadding="0" cellspacing="0"></table>
						</td>
					</tr>
					<tr>
						<td style="text-align:left; vertical-align:top; font-weight:600; padding-top: 10px" class="text-label">Ghi chú:</td>
						<td style="text-align:left; vertical-align:top;">
							<textarea id="txtGhiChuThangThua" name="txtGhiChuThangThua" rows="10" style="font-size:13px"></textarea>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td class="text-end">
							<input type="hidden" name="which_form" id="which_form" value="" />
							<input type="button" class="btn btn-confirm mt-2" name="btnDongY" id="btnDongY" value="Đồng ý" title="Đồng ý" />
						</td>
					</tr>
				</table>
			</div>';
		return $html;
	}

	function populatePrintLanguage()
	{
		$html = '<div id="dlgSelectLanguage" style="display:none;" title="Ngôn ngữ">
			<div class="d-flex flex-column align-items-center gap-3">
				<div class="option-group d-flex gap-4">
					<div class="form-group">
						<label for="vn" class="form-check-label">Tiếng Việt</label>
						<input class="form-check-input" type="radio" name="ngonngu" id="vn" value="vn" style="vertical-align:middle; margin-top: 0;" checked /> 
					</div>
					<div class="form-group">
						<label for="en" class="form-check-label">Tiếng Anh</label>
						<input class="form-check-input" type="radio" name="ngonngu" id="en" value="en" style="vertical-align:middle; margin-top: 0;" /> 
					</div>
					
				</div>
				<div class="option-passenger"></div>
				<div class="form-group">
					<input type="hidden" id="what_form" value="" />
					<input type="button" class="btn btn-primary" id="btnSelectLanguage" value="Tiếp tục" title="Tiếp tục" />
				</div>
			</div>
		</div>';
		return $html;
	}

	function populateWorkingProcessNote()
	{
		$html = '
		<div id="dlgWorkingProcessNote" title="Ghi chú" style="display:none;">
			<table cellpadding="0" cellspacing="0" border="0">
				<tr style="display:none;">
					<td width="15%">Lý do</td>
					<td width="85%"><table class="win-lose-radio" border="0" cellpadding="0" cellspacing="0"></table></td>
				</tr>
				<tr style="display:none;">
					<td width="15%">Bonus</td>
					<td width="85%"><input type="text" name="txtBonus" id="txtBonus" value="" /></td>
				</tr>
				<tr>
					<td colspan="2"><textarea id="txtWorkingProcessNote" rows="7" placeholder="Aa..."></textarea></td>
				</tr>
				<tr>
					<td class="d-flex justify-content-end align-items-center gap-2 mt-2">
						<input type="button" id="btnSaveWorkingProcess" class="btn btn-primary" value="Lưu" title="Lưu"/>
						<input type="hidden" id="frmSaveWorkingProcess" value="" />
						<input type="button" id="btnCloseWorkingProcess" class="btn btn-danger" value="Hủy bỏ" title="Hủy bỏ" />
						<span id="save-working-process-loading"></span>
						<span id="save-working-process-error" class="error"></span>
					</td>
				</tr>
			</table>	
		</div>';
		return $html;
	}

	// Hiện thông tin hoá đơn
	function populateInvoiceInf()
	{
		$iv_account_name = $iv_email = $iv_identity_number = $iv_payment_method = '';

		if (!empty($this->bean->shipping_address)) {
			$invoice_arr = json_decode(str_replace("&quot;", "\"", $this->bean->shipping_address), 1);
			$iv_account_name = $invoice_arr['iv_account_name'] ?? '';
			$iv_email = $invoice_arr['iv_email'] ?? '';
			$iv_identity_number = $invoice_arr['iv_identity_number'] ?? '';
			$iv_payment_method = $invoice_arr['iv_payment_method'] ?? '';
		}

		$this->ss->assign('CUS_IV_ACCOUNT_NAME', $iv_account_name);
		$this->ss->assign('CUS_IV_EMAIL', $iv_email);
		$this->ss->assign('CUS_IV_IDENTITY_NUMBER', $iv_identity_number);
		$this->ss->assign('CUS_IV_PAYMENT_METHOD', $iv_payment_method);
	}

	// Kiểm tra booker có quyền quản lý booking -> cho thay đổi trạng thái
	function isManagerBK()
	{
		global $current_user;
		$sql = 'SELECT COUNT(id) 
			FROM acl_roles_users 
			WHERE user_id = "' . $current_user->id . '" AND role_id = "554c808f-9f0b-cc0f-9b77-623e724d7516" AND deleted = 0';
		return $this->bean->db->getOne($sql);
	}

	/**
	 * Tạo modal confirm action
	 * 
	 * @return void
	 */
	private function createModal()
	{
		echo '<div class="modal fade" id="modal-confirm">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title"></h4>
					</div>
					<div class="modal-footer border-0">
						<button type="button" class="btn btn-confirm" id="confirm-modal" type="" data="" data-bs-dismiss="modal">Xác nhận</button>
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
					</div>
				</div>
			</div>
		</div>';
		return;
	}

	/**
	 * Get journeys by booking id (Using for ZBS)
	 * 
	 * @param string $bookingId
	 * @return array
	 */
	public function getJourneysByBooking($bookingId)
	{
		$journeys = [];
		if (is_null($bookingId) || empty($bookingId)) return $journeys;

		$sql = "SELECT 
				iti.id,
				iti.departure,
				iti.arrival,
				iti.departure_date AS departure_date,
				iti.arrival_date AS arrival_date,
				iti.direction,
				iti.airline_code,
				iti.flight_number,
				iti.ticket_class
			FROM ec_booking_itineraries iti
			WHERE iti.booking_id = '$bookingId'
				AND iti.add_type = 0
				AND iti.deleted = 0
			ORDER BY iti.direction, iti.date_entered, iti.departure_date";

		$stt_dep = $stt_ret = 0;
		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			// Lượt đi
			if ($row['direction'] == '0') {
				$departure_date = explode(' ', $row['departure_date']);
				if ($stt_dep == 0) {
					$journeys[$row['id']] = array(
						'type' => 'dep',
						'dep_code' => $row['departure'],
						'arv_code' => $row['arrival'],
						'date' => $departure_date[0],
						'time' => substr($departure_date[1], 0, -3),
						'flightno' => $row['flight_number'],
						'dep_name' => myGetAirportInfo2($row['departure'])['data'][0]['name'] . ' (' . $row['departure'] . ')',
						'arv_name' => myGetAirportInfo2($row['arrival'])['data'][0]['name'] . ' (' . $row['arrival'] . ')',
						'airline' => myGetAirlineInfo2($this->bean->airline, 'CODE')['data'][0]['name'] ?? '',
						'datetime' => date('d/m/Y', strtotime($departure_date[0])) . ' ' . substr($departure_date[1], 0, -3),
						'class' => $row['ticket_class'],
					);
				} else {
					$journeys['dep']['arv_code'] = $row['arrival'];
					$journeys['dep']['arv_name'] = myGetAirportInfo2($row['arrival'])['data'][0]['name'] . ' (' . $row['arrival'] . ')';
				}

				$stt_dep++;
			}

			// Lượt về
			if ($row['direction'] == '1') {
				$return_date = explode(' ', $row['departure_date']);
				if ($stt_ret == 0) {
					$journeys[$row['id']] = array(
						'type' => 'ret',
						'dep_code' => $row['departure'],
						'arv_code' => $row['arrival'],
						'date' => $return_date[0],
						'time' => substr($return_date[1], 0, -3),
						'flightno' => $row['flight_number'],
						'dep_name' => myGetAirportInfo2($row['departure'])['data'][0]['name'] . ' (' . $row['departure'] . ')',
						'arv_name' => myGetAirportInfo2($row['arrival'])['data'][0]['name'] . ' (' . $row['arrival'] . ')',
						'airline' => myGetAirlineInfo2($this->bean->airline_inbound, 'CODE')['data'][0]['name'] ?? '',
						'datetime' => date('d/m/Y', strtotime($return_date[0])) . ' ' . substr($return_date[1], 0, -3),
						'class' => $row['ticket_class'],
					);
				} else {
					$journeys['ret']['arv_code'] = $row['arrival'];
					$journeys['ret']['arv_name'] = myGetAirportInfo2($row['arrival'])['data'][0]['name'] . ' (' . $row['arrival'] . ')';
				}

				$stt_ret++;
			}
		}

		return $journeys;
	}

	/**
	 * Get the number of passenger and baggage info to send ZBS
	 * 
	 * @param string $booking_id
	 * @return array [passenger, baggage]
	 */
	public function getPassengerAndBaggage($booking_id)
	{
		if (is_null($booking_id) || empty($booking_id)) return ['passenger' => '', 'baggage' => ''];

		$adt = $chd = $inf = 0;
		$totalPackDep = $totalWeightDep = 0;
		$totalPackRet = $totalWeightRet = 0;
		$sql = "SELECT type
					,IFNULL(luggage_index_outbound, '') AS luggage_index_outbound
					,IFNULL(luggage_index_inbound, '') AS luggage_index_inbound
					,IFNULL(luggage_purchase_text, '') AS luggage_purchase_text
					,IFNULL(luggage_purchase_text_inbound, '') AS luggage_purchase_text_inbound
				FROM ec_booking_passengers
				WHERE booking_id = '{$booking_id}'
					AND deleted = 0";

		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			if ($row['type'] == '0') $adt++;
			elseif ($row['type'] == '1') $chd++;
			elseif ($row['type'] == '2') $inf++;

			// Hành lý có sẵn
			if (!empty($row['luggage_index_outbound'])) {
				$bagText = Baggage::renderAvailableBaggage($row['luggage_index_outbound']);
				preg_match_all('/\d+/', $bagText, $matches);
				$pack   = (int)($matches[0][0] ?? 0); // 1
				$weight = (int)($matches[0][1] ?? 0); // 20

				$totalPackDep += $pack;
				$totalWeightDep += $weight;
			}
			if (!empty($row['luggage_index_inbound'])) {
				$bagText = Baggage::renderAvailableBaggage($row['luggage_index_inbound']);
				preg_match_all('/\d+/', $bagText, $matches);
				$pack   = (int)($matches[0][0] ?? 0); // 1
				$weight = (int)($matches[0][1] ?? 0); // 20

				$totalPackRet += $pack;
				$totalWeightRet += $weight;
			}

			// Hành lý mua thêm
			if (!empty($row['luggage_purchase_text'])) {
				preg_match_all('/\d+/', $row['luggage_purchase_text'], $matches);
				$pack   = (int)($matches[0][0] ?? 0); // 1
				$weight = (int)($matches[0][1] ?? 0); // 20

				$totalPackDep += $pack;
				$totalWeightDep += $weight;
			}
			if (!empty($row['luggage_purchase_text_inbound'])) {
				preg_match_all('/\d+/', $row['luggage_purchase_text_inbound'], $matches);
				$pack   = (int)($matches[0][0] ?? 0); // 1
				$weight = (int)($matches[0][1] ?? 0); // 20

				$totalPackRet += $pack;
				$totalWeightRet += $weight;
			}
		}

		$pass = "$adt người lớn";
		if ($chd > 0) $pass .= ", $chd trẻ em";
		if ($inf > 0) $pass .= ", $inf em bé";

		$bag = '';
		if ($totalPackDep * $totalWeightDep != 0) $bag .= "{$totalPackDep} kiện đi (tổng {$totalWeightDep}kg)";
		elseif ($totalPackDep > 0) $bag .= "{$totalPackDep} kiện đi";
		elseif ($totalWeightDep > 0) $bag .= "{$totalWeightDep}kg lượt đi";

		if (!empty($bag)) $bag .= ', ';
		if ($totalPackRet * $totalWeightRet != 0) $bag .= "{$totalPackRet} kiện về (tổng {$totalWeightRet}kg)";
		elseif ($totalPackRet > 0) $bag .= "{$totalPackRet} kiện về";
		elseif ($totalWeightRet > 0) $bag .= "{$totalWeightRet}kg lượt về";

		if (empty($bag)) $bag = "Không";
		return ['passenger' => $pass, 'baggage' => trim($bag)];
	}

	// Get zalo id
	public function getZaloID($phone)
	{
		if (is_null($phone) || empty($phone))
			return '';
		return $this->bean->db->getOne("SELECT zalo_id FROM contacts WHERE phone_mobile = '$phone' AND deleted = 0 ORDER BY date_entered LIMIT 1") ?? '';
	}

	public function htmlZaloInfo($data)
	{
		if (is_null($data) || empty($data))
			return '<p class="text-secondary" style="text-align:center; font-style:italic">Chưa có thông tin Zalo</p>';

		$follow = $data['is_follow'] ? '<b class="text-primary" style="float:right;margin-left:20px;">Đã quan tâm</b>' : '<span class="text-secondary" style="float:right;margin-left:20px;">Chưa quan tâm</span>';
		$html = '<div class="wrap-zalo-info" style="display:flex; justify-content:center; padding:10px 0; margin-bottom:15px; gap:20px; box-shadow: rgba(17, 17, 26, 0.05) 0px 1px 0px, rgba(17, 17, 26, 0.1) 0px 0px 8px;">
			<div>
				<div class="wrap-avatar" style="background-image: url(' . $data['avatar'] . '); background-size:contain; width:85px; height:85px; border-radius:50%; margin:0 auto;"></div>
				<p class="mt-1">' . $data['name'] . '</p>
			</div>
			<div style="font-weight:normal">
				<p><b>Zalo ID: </b>' . $data['id'] . $follow . '</p>
				<p><b>Số điện thoại: </b>' . $data['phone'] . '</p>
				<p><b>Tương tác lần cuối: </b> ' . $data['last_interaction'] . '</p>
			</div>
		</div>';

		return $html;
	}

	/**
	 * Get history sending ZBS messages
	 * 
	 * @param string $phoneNumber
	 * @param string $bookingId
	 * @return array
	 */
	public function getHistoryZBS($phoneNumber, $bookingId)
	{
		$result = [
			'journey' => 0,
			'payment' => 0,
			'code' => 0,
			'callsale' => 0,
			'remind' => 0,
			'delay' => 0,
		];

		if (empty($phoneNumber) || empty($bookingId)) return $result;

		$sql = "SELECT zm.sub_type, COUNT(*) AS count
			FROM ec_zalo_messages zm
			WHERE zm.booking_id = '$bookingId'
				AND (zm.type = 'zns' OR zm.type = 'zbs')
				AND (zm.to_id = '$phoneNumber')
				AND zm.deleted = 0
			GROUP BY zm.sub_type";

		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			if (stripos($row['sub_type'], 'journey') !== false)
				$result['journey'] += $row['count'];
			elseif (stripos($row['sub_type'], 'code') !== false)
				$result['code'] += $row['count'];
			elseif (stripos($row['sub_type'], 'payment') !== false)
				$result['payment'] += $row['count'];
			elseif (stripos($row['sub_type'], 'after-call-sale') !== false)
				$result['callsale'] += $row['count'];
			elseif (stripos($row['sub_type'], 'remind') !== false)
				$result['remind'] += $row['count'];
			elseif (stripos($row['sub_type'], 'delay') !== false)
				$result['delay'] += $row['delay'];
		}

		return $result;
	}

	public function generateDialogGetQRCode($amount, $phone)
	{
		$addInfo = urlencode("Thanh toan $phone");

		// Lấy những stk đang theo dõi
		$sql = 'SELECT ba.account_number AS account,
				ba.account_holder AS owner,
				b.short_name AS short_name,
				ba.name 
			FROM ec_bank_account ba 
				INNER JOIN ec_banks b ON b.id = ba.bank_id AND is_sms = 1
			WHERE ba.deleted = 0 AND ba.unfollow = 0
			ORDER BY IF(ba.sort IS NULL OR ba.sort = "", 100, ba.sort)';

		$options = '<option value="#">Chọn tài khoản ngân hàng</option>';
		$res = $this->bean->db->query($sql);
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$bankID = str_replace(' ', '', $row['short_name']);
			$accountNo = $row['account'];
			$accountName = urlencode($row['owner']);

			$url = "https://api.vietqr.io/image/$bankID-$accountNo-J49B6oY.jpg?amount=$amount&accountName=$accountName&addInfo=$addInfo";
			$options .= '<option value="' . $url . '">' . $row['name'] . '</option>';
		}

		return '<dialog id="dialog_qr_code" class="dialog_qr_code">
					<h3 class="title">QR thanh toán booking</h3>
					<select id="select_bank_get_qr_code" class="select_bank">' . $options . '</select>
					<img id="img_qr_code" class="img_qr_code" src="" />
					<div class="wrap-redo flex-center p-3" style="display:none;">
						<input type="text" name="new_payment_amount" id="new_payment_amount" placeholder="Nhập số tiền" />
						<button class="btn btn-primary" id="btnRenderQRCode">Tạo lại mã</button>
					</div>
					<div class="flex-center">
						<button class="btn btn-secondary" onclick="closeDialog(\'dialog_qr_code\')">Đóng</button>
						<button class="btn btn-primary" style="display: none;" id="copyQRCodeImage">Sao chép QR</button>
					</div>
				</dialog';
	}
}
