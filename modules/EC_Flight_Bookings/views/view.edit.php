<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');
require_once('custom/entrypoints/entryAuthClass/entryFareSystemClass.php');

class EC_Flight_BookingsViewEdit extends ViewEdit
{
	/**
	 * @var EC_Flight_Bookings
	 */
	public $bean;
	private $_outbound_airline = '';
	private $_inbound_airline = '';
	private $_outbound_ticket_class = '';
	private $_inbound_ticket_class = '';
	private $_journey = '';
	private $icon_x = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>';

	function __construct()
	{
		parent::__construct();
	}

	function display()
	{
		global $current_user;

		$status_arr = ['1', '6', '2', '3']; // allow edit
		$status__com_arr = ['7', '8']; // allow edit admin và QL chỉnh (Admin edit all)

		if (
			(empty($this->bean->id)
				|| in_array($this->bean->booking_status, $status_arr)
				|| (isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true'))
			&& ACLController::checkAccess('EC_Flight_Bookings', 'edit', true)
		) {
			$this->displayJS();
			$this->displayCSS();

			$this->populateBasicFields();
			$this->populateLineDetails();
			$this->populateLineItineraries();

			if (!$this->bean->created_by || in_array($this->bean->created_by, $this->bean->list_website_new_baggage) || substr($this->bean->name, 0, 2) === 'BK') {
				$this->populateLinePassengers();
			} else
				$this->populateLinePassengersOld();

			parent::display();
		} else if (in_array($this->bean->booking_status, $status__com_arr) && (isManagerUser($current_user->id))) {
			$this->displayJS();
			$this->displayCSS();

			if (!is_admin($current_user)) {
				$this->displayJS_Edit();
			}

			$this->populateBasicFields();
			$this->populateLineDetails();
			$this->populateLineItineraries();

			if (!$this->bean->created_by || in_array($this->bean->created_by, $this->bean->list_website_new_baggage) || substr($this->bean->name, 0, 2) === 'BK') {
				$this->populateLinePassengers();
			} else
				$this->populateLinePassengersOld();

			parent::display();
		} else {
			header('Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=' . urlencode('Bạn không được quyền chỉnh sửa booking này'));
			exit();
		}
	}

	function displayCSS()
	{
		$css = '';
		$css .= '<link rel="stylesheet" href="modules/EC_Flight_Bookings/css/view.edit.css?v=1.1">';
		echo $css;
	}

	function displayJS()
	{
		global $current_user, $app_list_strings;
		$js = '';

		$js .= '<script>
			var assigned_user_id="' . $current_user->id . '";
			var vja_luggage_index_list=' . json_encode(array_values($app_list_strings['vietjet_index_price_list2'])) . ';
			$(document).ready(function() {
				calculateTotal();
			});
		</script>';

		if (!isAllowedUser()) {
			$js .= '<script>
				$(document).ready(function() {
					$("#assigned_user_name_label").css("visibility", "hidden");
				});
			</script>';
		}

		$js .= '<script src="modules/EC_Flight_Bookings/js/view.edit.js?v=1.8"></script>';
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

	/* TRƯỜNG DỮ LIỆU */
	function populateBasicFields()
	{
		global $app_list_strings, $timedate, $current_user;
		$date_format = $timedate->get_date_format();

		// Thông tin hoá đơn
		$this->populateInvoiceFields();

		// Tên booking
		if ($this->bean->name != "") {
			if (ACLController::checkAccess('Bugs', 'edit', true)) {
				$name_booking = '<div class="d-flex align-items-center row-booking__name gap-2">
									<input type="text" name="name" id="name" value="' . ($this->bean->name ? $this->bean->name : '') . '" />
								</div>';
				$this->ss->assign('NAME_BOOKING', $name_booking);
			} else {
				$name_booking = '<div class="d-flex align-items-center row-booking__name gap-2">
								<p class="label">' . ($this->bean->name ? $this->bean->name : "") . '</p>
							</div>';
				$this->ss->assign('NAME_BOOKING', $name_booking);
			}
		}

		// Là đại lý
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

		// Check đã xuất vé
		$is_ticket_exported = '<span class="is_ticket_exported">
			<input type="checkbox" name="chk_is_ticket_exported" id="chk_is_ticket_exported" tabindex="105" ' . ($this->bean->is_ticket_exported ? 'checked="checked"' : '') . ' />
			<input type="hidden" name="is_ticket_exported" id="is_ticket_exported" value="' . $this->bean->is_ticket_exported . '" />
			<span class="date_ticket_issue" ' . ($this->bean->is_ticket_exported ? '' : 'style="display:none"') . '>
				<input type="text" maxlength="10" size="11" tabindex="105" title="" value="' . ($this->bean->date_ticket_issue ? $this->bean->date_ticket_issue : date($date_format)) . '" id="date_ticket_issue" name="date_ticket_issue" autocomplete="off">
				<img border="0" align="absmiddle" id="date_ticket_issue_trigger" alt="input date" src="themes/SuiteP/images/Calendar.svg">
			</span>
		</span>';
		$this->ss->assign('IS_TICKET_EXPORTED', $is_ticket_exported);

		// Danh xưng liên hệ
		$contact_name = '<div class="d-flex gap-2">
			<select id="contact_title" name="contact_title" class="" tabindex="112">' . get_select_options_with_id($app_list_strings['passenger_salutation_list'], $this->bean->contact_title ? (int) $this->bean->contact_title : 0) . '</select>
			<input type="text" name="contact_name" id="contact_name" class="flex-fill" value="' . ($this->bean->contact_name ? $this->bean->contact_name : '') . '" />
		</div>';
		$this->ss->assign('CONTACT_NAME', $contact_name);

		// Airline outbound - Airline inbound
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

		// Date ticket issue - Date ticket inbound issue (ngày xuất vé lượt đi - về)
		// Only Admin and Accountant can view
		$icon_calendar = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2" viewBox="0 0 16 16">
							<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1H2z"/>
							<path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5V4z"/>
						</svg>';

		if (ACLController::checkAccess('Bugs', 'edit', true)) {
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

		// Giao cho chỉ có những user được phép mới thấy
		if (isManagerUser($current_user->id)) {
			$user = new User;
			$user->retrieve($this->bean->assigned_user_id);
			$assigned_user = '
				<input type="text" name="assigned_user_name" class="sqsEnabled yui-ac-input" id="assigned_user_name" value="' . $user->user_name . '" autocomplete="off">
				<input type="hidden" name="assigned_user_id" id="assigned_user_id" value="' . $this->bean->assigned_user_id . '">
				<button type="button" name="btn_assigned_user_name" id="btn_assigned_user_name" tabindex="0" title="Chọn [Alt+T]" class="px-1 btn btn-primary" accesskey="T" value="Chọn" onclick="open_popup(&quot;Users&quot;, 600, 400, &quot;&quot;, true, false, {&quot;call_back_function&quot;:&quot;set_return&quot;,&quot;form_name&quot;:&quot;EditView&quot;,&quot;field_to_name_array&quot;:{&quot;id&quot;:&quot;assigned_user_id&quot;,&quot;user_name&quot;:&quot;assigned_user_name&quot;}}, &quot;single&quot;, true);">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
				</button>
				<button type="button" name="btn_clr_assigned_user_name" id="btn_clr_assigned_user_name" tabindex="0" title="Xóa trắng [Alt+C]" class="px-1 btn btn-secondary" value="Xóa" onclick="this.form.assigned_user_name.value = \'\'; this.form.assigned_user_id.value = \'\';">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
				</button>
			';
			$this->ss->assign('CUS_ASSIGNED_USER_NAME', $assigned_user);
		}

		// Nơi đặt vé của Booking
		$location_booking = '
			<div class="ui-widget">
				<input placeholder="Hồ Chí Minh, Hà Nội,..." type="text" class="location_booking" name="country" id="location_booking" value="' . $this->bean->country . '">
			</div>';
		$this->ss->assign('LOCATION_BOOKING', $location_booking);
	}

	/* HÀNH TRÌNH */
	function populateLineItineraries()
	{
		global $app_list_strings, $mod_strings, $timedate, $locale, $current_user;

		$app_list_strings['bambooair_luggage_price_list'] = array_merge(
			$app_list_strings['bambooair_eco_luggage_price_list'],
			$app_list_strings['bambooair_plus_luggage_price_list'],
			$app_list_strings['bambooair_business_luggage_price_list']
		);

		$cal_date_format = $timedate->get_cal_date_format();
		$date_format = $timedate->get_date_format();
		$booking_prev_name = (isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true') ? $this->bean->name : '';

		$sql = "SELECT i.id AS detail_id,
					i.direction,
					i.airline_code,
					i.flight_number,
					i.ticket_class,
					i.departure,
					i.arrival,
					i.departure_date,
					i.arrival_date,
					i.base_price,
					i.is_layover,
					i.description,
					i.time_limit
				FROM ec_booking_itineraries i
				WHERE i.booking_id = '" . $this->bean->id . "'
				AND add_type = 0 AND i.deleted = 0
				ORDER BY i.direction, i.transit_order, i.date_entered, i.departure_date";


		$res = $this->bean->db->query($sql);
		$row_count = $this->bean->db->countRows($res);
		$row_count = !empty($row_count) ? $row_count : 0;

		$html = '';
		$html .= '<table id="tbl_line_itineraries" class="table-vertical__mobile table-edit__booking table-details__booking" border="0" cellpadding="0" cellspacing="0">';
		$html .= '<thead>
				<tr id="iti_first_row">
					<th scope="col" style="width:8%;" class="text-center">Chiều</th>
					<th scope="col" style="width:5%;" class="text-center">Mã hãng</th>
					<th scope="col" style="width:7%;" class="text-center">Số hiệu</th>
					<th scope="col" style="width:11%;" class="text-center">Hạng vé</th>
					<th scope="col" style="width:5%;" class="text-center">Nơi đi</th>
					<th scope="col" style="width:5%;" class="text-center">Nơi đến</th>
					<th scope="col" style="width:14%;" class="text-center">Ngày giờ đi</th>
					<th scope="col" style="width:14%;" class="text-center">Ngày giờ đến</th>
					<th scope="col" style="width:14%;" class="text-center">Hạn giữ chỗ</th>
					<th scope="col" style="width:8%;" class="text-center">Giá cơ bản</th>
					<th scope="col" style="width:2%;" class="text-center">Quá cảnh</th>
					<th scope="col" style="width:3%;" class="text-center">&nbsp;</th>
				</tr>
			</thead>';

		// Chỉnh sửa hành trình
		if (!empty($this->bean->id)) {
			$i = 0;
			while ($row = $this->bean->db->fetchByAssoc($res)) {
				// Nếu là hãng BBA thì hạng vé hiện theo dạng select
				if ($row['airline_code'] == 'BBA') {
					$cus_ticket_class = '<select id="iti_ticket_class' . $i . '" name="iti_ticket_class[]">' . get_select_options_with_id($app_list_strings['bba_ticket_class_list'], $row['ticket_class']) . '</select>';
				} else {
					$cus_ticket_class = '<input type="text" maxlength="100" id="iti_ticket_class' . $i . '" name="iti_ticket_class[]" value="' . $row['ticket_class'] . '" />';
				}


				$detail_id = isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true' ? '' : $row['detail_id'];
				$departure_date = $row['departure_date'] != '' ? date($date_format, strtotime($row['departure_date'])) : '';
				$departure_h = $row['departure_date'] != '' ? date('H', strtotime($row['departure_date'])) : '';
				$departure_m = $row['departure_date'] != '' ? date('i', strtotime($row['departure_date'])) : '';

				$arrival_date = $row['arrival_date'] != '' ? date($date_format, strtotime($row['arrival_date'])) : '';
				$arrival_h = $row['arrival_date'] != '' ? date('H', strtotime($row['arrival_date'])) : '';
				$arrival_m = $row['arrival_date'] != '' ? date('i', strtotime($row['arrival_date'])) : '';

				$time_limit_date = $row['time_limit'] != '' ? date($date_format, strtotime($row['time_limit'])) : '';
				$time_limit_h = $row['time_limit'] != '' ? date('H', strtotime($row['time_limit'])) : '';
				$time_limit_m = $row['time_limit'] != '' ? date('i', strtotime($row['time_limit'])) : '';

				$html .= '<tr id="iti_line_' . $i . '">
							<td data-label="Chiều"><select onchange="getAirlineCode(' . $i . ')" id="iti_direction' . $i . '" name="iti_direction[]" class="w-100" value=>' . get_select_options_with_id($app_list_strings['bk_direction_list'], (int) $row['direction']) . '</select></td>
							<td data-label="Mã hãng"><input autocomplete="off" class="ac airline" type="text" maxlength="255" name="iti_airline_code[]" id="iti_airline_code' . $i . '" value="' . $row['airline_code'] . '" /></td>
							<td data-label="Số hiệu"><input type="text" maxlength="10" name="iti_flight_number[]" id="iti_flight_number' . $i . '" value="' . $row['flight_number'] . '" /></td>
							<td data-label="Hạng vé">' . $cus_ticket_class . '</td>
							<td data-label="Nơi đi"><input autocomplete="off" class="ac airport" type="text" maxlength="255" name="iti_departure[]" id="iti_departure' . $i . '" value="' . $row['departure'] . '" /></td>
							<td data-label="Nơi đến"><input autocomplete="off" class="ac airport" type="text" maxlength="255" name="iti_arrival[]" id="iti_arrival' . $i . '" value="' . $row['arrival'] . '" /></td>
							<td data-label="Ngày giờ đi"><div class="d-flex align-items-center gap-1 align-middle"><div class="date-wrap d-flex w-65 gap-1"><input maxlength="10" type="text" name="iti_departure_date[]" id="iti_departure_date' . $i . '" value="' . $departure_date . '" /><img border="0" class="cursor-pointer" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="iti_departure_date_trigger' . $i . '" align="absmiddle" /></div><div class="time-wrap d-flex flex-fill align-items-center"><input class="datetime_h w-25-px text-center" type="text" name="iti_departure_h[]" id="iti_departure_h' . $i . '" value="' . $departure_h . '" maxlength="2" /><span>:</span><input class="datetime_m w-25-px text-center" type="text" name="iti_departure_m[]" id="iti_departure_m' . $i . '" value="' . $departure_m . '" maxlength="2" /></div></div></td>
							<td data-label="Ngày giờ đến"><div class="d-flex align-items-center gap-1 align-middle"><div class="date-wrap d-flex w-65 gap-1"><input maxlength="10" type="text" name="iti_arrival_date[]" id="iti_arrival_date' . $i . '" value="' . $arrival_date . '" /><img border="0" class="cursor-pointer" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="iti_arrival_date_trigger' . $i . '" align="absmiddle" /></div><div class="time-wrap d-flex flex-fill align-items-center"><input class="datetime_h w-25-px text-center" type="text" name="iti_arrival_h[]" id="iti_arrival_h' . $i . '" value="' . $arrival_h . '" maxlength="2" /><span>:</span><input class="datetime_m w-25-px text-center" type="text" name="iti_arrival_m[]" id="iti_arrival_m' . $i . '" value="' . $arrival_m . '" maxlength="2" /></div></div></td>
							<td data-label="Hạn giữ chỗ"><div class="d-flex align-items-center gap-1 align-middle"><div class="date-wrap d-flex w-65 gap-1"><input maxlength="10" type="text" name="iti_time_limit_date[]" id="iti_time_limit_date' . $i . '" value="' . $time_limit_date . '" /><img border="0" class="cursor-pointer" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="iti_time_limit_date_trigger' . $i . '" align="absmiddle" /></div><div class="time-wrap d-flex flex-fill align-items-center"><input class="datetime_h w-25-px text-center" type="text" name="iti_time_limit_h[]" id="iti_time_limit_h' . $i . '" value="' . $time_limit_h . '" maxlength="2" /><span>:</span><input class="datetime_m w-25-px text-center" type="text" name="iti_time_limit_m[]" id="iti_time_limit_m' . $i . '" value="' . $time_limit_m . '" maxlength="2" /></div></div></td>
							<td data-label="Giá cơ bản"><input class="allow-number-only text-end" type="text" maxlength="20" name="iti_base_price[]" id="iti_base_price' . $i . '" value="' . format_number($row['base_price']) . '" /></td>
							<td data-label="Transit" class="text-center align-middle">
								<input ' . ($row['is_layover'] ? 'checked="checked"' : '') . ' type="checkbox" name="iti_is_layover_chk[]" id="iti_is_layover_chk' . $i . '" onchange="checkActive(\'iti_is_layover_chk' . $i . '\', \'iti_is_layover' . $i . '\')" />
								<input type="hidden" name="iti_is_layover[]" id="iti_is_layover' . $i . '" value="' . $row['is_layover'] . '" />
							</td>
							<td data-label="Xóa dòng" class="text-center align-middle">
								<button title="Xóa" type="button" onclick="markItineraryRowDeleted(' . $i . ')" class="button-remove-in-edit" >' . $this->icon_x . '</button>
								<input type="hidden" value="0" name="iti_deleted[]" id="iti_deleted' . $i . '" />
								<input type="hidden" name="iti_detail_id[]" id="iti_detail_id' . $i . '" value="' . $detail_id . '" />
							</td>
						</tr>';

				if ($row['direction'] == '0') {
					$this->_outbound_airline = $row['airline_code'];
					$this->_outbound_ticket_class = $row['ticket_class'];
					if ($i == 0) {
						$this->_journey = $row['departure'] . '-' . $row['arrival'];
					} else {
						$this->_journey = substr($this->_journey, 0, 3) . '-' . $row['arrival'];
					}
				}

				if ($row['direction'] == '1') {
					$this->_inbound_airline = $row['airline_code'];
					$this->_inbound_ticket_class = $row['ticket_class'];
				}

				$i++;
			}

			$sep = my_get_number_separators();
			$html .= '<tr id="iti_last_row" class="footer-tr">
				<td colspan="12" class="text-start">
					<input type="hidden" name="discount_percent_list" id="discount_percent_list" value="' . get_select_options_with_id($app_list_strings['discount_percent_list'], '') . '" />
					<input type="hidden" name="direction_list" id="direction_list" value="' . get_select_options_with_id($app_list_strings['bk_direction_list'], '') . '" />
					<input type="hidden" name="passenger_type_list" id="passenger_type_list" value="' . get_select_options_with_id($app_list_strings['passenger_type_list'], '') . '" />
					<input type="hidden" name="passenger_salutation_list" id="passenger_salutation_list" value="' . get_select_options_with_id($app_list_strings['passenger_salutation_list'], '') . '" />
					<input type="hidden" name="vna_luggage_price_list" id="vna_luggage_price_list" value="' . get_select_options_with_id($app_list_strings['vietnamair_luggage_price_list2'], '') . '" />
					<input type="hidden" name="vnp_luggage_price_list" id="vnp_luggage_price_list" value="' . get_select_options_with_id($app_list_strings['pacificair_luggage_price_list'], '') . '" />
					<input type="hidden" name="vja_luggage_price_list" id="vja_luggage_price_list" value="' . get_select_options_with_id($app_list_strings['new_vietjet_luggage_price_list'], '') . '" />
					<input type="hidden" name="bba_luggage_price_list" id="bba_luggage_price_list" value="' . get_select_options_with_id($app_list_strings['bambooair_luggage_price_list'], '') . '" />
					<input type="hidden" name="vta_luggage_price_list" id="vta_luggage_price_list" value="' . get_select_options_with_id($app_list_strings['new_vietravel_luggage_price_list2'], '') . '" />
					<input type="hidden" id="grp_seperator" name="grp_seperator" value="' . $sep[0] . '" />
					<input type="hidden" id="dec_seperator" name="dec_seperator" value="' . $sep[1] . '" />
					<input type="hidden" id="sig_digits" name="sig_digits" value="' . $locale->getPrecision() . '" />
					<input type="hidden" id="cal_date_format" name="cal_date_format" value="' . $cal_date_format . '" />
					<input type="hidden" id="iti_row_count" name="iti_row_count" value="' . $row_count . '" />
					<input type="hidden" id="booking_prev_name" name="booking_prev_name" value="' . $booking_prev_name . '" />
					<input type="hidden" id="journey" name="journey" value="' . $this->_journey . '" />
					<input type="button" class="btn btn-primary" id="btnItineraryAddRow" value="Thêm dòng" title="Thêm dòng" />
					Số dòng = <label id="lbl_iti_row_count">' . $row_count . '</label>
				</td>
			</tr>';
			$html .= '</table>';
		} else {
			// Tạo mới hành trình
			$sep = my_get_number_separators();
			$html .= '<tr id="iti_last_row" class="footer-tr">
						<td colspan="12" class="text-start">
							<input type="hidden" name="discount_percent_list" id="discount_percent_list" value="' . get_select_options_with_id($app_list_strings['discount_percent_list'], '') . '" />
							<input type="hidden" name="direction_list" id="direction_list" value="' . get_select_options_with_id($app_list_strings['bk_direction_list'], '') . '" />
							<input type="hidden" name="passenger_type_list" id="passenger_type_list" value="' . get_select_options_with_id($app_list_strings['passenger_type_list'], '') . '" />
							<input type="hidden" name="passenger_salutation_list" id="passenger_salutation_list" value="' . get_select_options_with_id($app_list_strings['passenger_salutation_list'], '') . '" />
							<input type="hidden" name="vna_luggage_price_list" id="vna_luggage_price_list" value="' . get_select_options_with_id($app_list_strings['vietnamair_luggage_price_list2'], '') . '" />
							<input type="hidden" name="vnp_luggage_price_list" id="vnp_luggage_price_list" value="' . get_select_options_with_id($app_list_strings['pacificair_luggage_price_list'], '') . '" />
							<input type="hidden" name="vja_luggage_price_list" id="vja_luggage_price_list" value="' . get_select_options_with_id($app_list_strings['new_vietjet_luggage_price_list'], '') . '" />
							<input type="hidden" name="bba_luggage_price_list" id="bba_luggage_price_list" value="' . get_select_options_with_id($app_list_strings['bambooair_luggage_price_list'], '') . '" />
							<input type="hidden" name="vta_luggage_price_list" id="vta_luggage_price_list" value="' . get_select_options_with_id($app_list_strings['new_vietravel_luggage_price_list2'], '') . '" />
							<input type="hidden" id="grp_seperator" name="grp_seperator" value="' . $sep[0] . '" />
							<input type="hidden" id="dec_seperator" name="dec_seperator" value="' . $sep[1] . '" />
							<input type="hidden" id="sig_digits" name="sig_digits" value="' . $locale->getPrecision() . '" />
							<input type="hidden" id="cal_date_format" name="cal_date_format" value="' . $cal_date_format . '" />
							<input type="hidden" id="iti_row_count" name="iti_row_count" value="' . $row_count . '" />
							<input type="hidden" id="booking_prev_name" name="booking_prev_name" value="' . $booking_prev_name . '" />
							<input type="button" class="btn btn-primary" id="btnItineraryAddRow" value="Thêm dòng" title="Thêm dòng" />
							Số dòng = <label id="lbl_iti_row_count">0</label>
						</td>
					</tr>';
			$html .= '</table>';
		}

		$this->ss->assign('LINE_ITINERARIES', $html);
	}

	/* CHỈNH SỬA CHI TIẾT VÉ */
	function populateLineDetails()
	{
		global $app_list_strings, $current_user;
		$supplier_cus_sql = " AND account_type = 'Supplier' AND is_stop_tracking = 0 ";

		// User permission
		$supplier_list = str_replace('"', "'", myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', '', 'id', $supplier_cus_sql));

		$sql = "SELECT id AS detail_id 
					   ,direction
					   ,passenger_type
					   ,quantity
					   ,unit_price
					   ,tax_and_fee
					   ,airport_fee
					   ,admin_fee, vat_admin, admin_fee_no_vat
					   ,service_fee
					   ,total_price
					   ,total_bought_price
					   ,fee_bought
					   ,supplier_id
					   ,supplier_discount
				FROM ec_booking_details
				WHERE booking_id='" . $this->bean->id . "'
				AND deleted = 0
				ORDER BY direction, passenger_type, date_entered ";

		// if($current_user->user_name == 'hungnh'){
		// 	pr($sql);
		// }

		$res = $this->bean->db->query($sql);
		// $row_count = $this->bean->db->getRowCount($res);
		$row_count = $this->bean->db->countRows($res);
		$row_count = !empty($row_count) ? $row_count : 0;

		$html = '';
		$html .= '<table id="tbl_line_details" class="table-vertical__mobile table-edit__booking table-details__booking" cellpadding="0" cellspacing="0" border="0">';
		$html .= '<thead>
				<tr id="bkd_first_row">
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Chiều</th>
					<th scope="col" style="width:9%;" class="text-center fw-semibold">Loại HK</th>
					<th scope="col" style="width:3%;" class="text-center fw-semibold">SL</th>
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Giá cơ bản</th>
					<th scope="col" style="width:6%;" class="text-center fw-semibold">VAT</th>
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Phí sân bay</th>
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Phí admin</th>
					<th scope="col" style="width:7%;" class="text-center fw-semibold">Phí dịch vụ</th>
					<th scope="col" style="width:9%;" class="text-center fw-semibold">Thành tiền</th>
					<th scope="col" style="width:9%;" class="text-center fw-semibold">Giá mua</th>
					<th scope="col" style="width:8%;" class="text-center fw-semibold">Chiết khấu</th>
					<th scope="col" style="width:8%;" class="text-center fw-semibold">Phí xuất vé</th>
					<th scope="col" class="text-center fw-semibold">NCC</th>
					<th scope="col" style="width:3%;" class="text-center fw-semibold">&nbsp;</th>
				</tr>
			</thead>';

		// Chỉnh sửa chi tiết vé
		if (!empty($this->bean->id)) {
			$i = 0;
			$html .= '<tbody>';
			while ($row = $this->bean->db->fetchByAssoc($res)) {
				$detail_id = isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true' ? '' : $row['detail_id'];

				$html .= '<tr id="bkd_line_' . $i . '" class="bkd_line fw-semibold">
							<td data-label="Chiều"><select class="w-100" name="bkd_direction[]" id="bkd_direction' . $i . '" >' . get_select_options_with_id($app_list_strings['bk_direction_list'], (int) $row['direction']) . '</select></td>
							<td data-label="Loại HK"><select class="w-100" name="bkd_passenger_type[]" id="bkd_passenger_type' . $i . '">' . get_select_options_with_id($app_list_strings['passenger_type_list'], (int) $row['passenger_type']) . '</select></td>
							<td data-label="SL"><input class="allow-number-only text-center" onblur="calculateLineTotal(' . $i . ')" type="text" name="bkd_quantity[]" id="bkd_quantity' . $i . '" value="' . format_number($row['quantity']) . '" maxlength="3" /></td>
							<td data-label="Giá cơ bản"><input class="allow-number-only text-center" onblur="calculateLineTotal(' . $i . ')" onkeyup="calculateLineTotal(' . $i . ', 0, 1)" type="text" name="bkd_unit_price[]" id="bkd_unit_price' . $i . '" value="' . format_number($row['unit_price']) . '" maxlength="25" /></td>
							<td data-label="VAT"><input class="allow-number-only text-center" onblur="calculateLineTotal(' . $i . ')" type="text" name="bkd_tax_and_fee[]" id="bkd_tax_and_fee' . $i . '" value="' . format_number($row['tax_and_fee']) . '" maxlength="25" /></td>
							<td data-label="Phí sân bay"><input class="allow-number-only text-center" onblur="calculateLineTotal(' . $i . ')" type="text" name="bkd_airport_fee[]" id="bkd_airport_fee' . $i . '" value="' . format_number($row['airport_fee']) . '" maxlength="25" /></td>
							<td data-label="Phí admin"><input class="allow-number-only text-center" onblur="calculateLineTotal(' . $i . ', 1)" type="text" name="bkd_admin_fee[]" id="bkd_admin_fee' . $i . '" value="' . format_number($row['admin_fee']) . '" maxlength="25" /></td>
							<td data-label="Phí dịch vụ"><input class="allow-number-only text-center" onblur="calculateLineTotal(' . $i . ')" type="text" name="bkd_service_fee[]" id="bkd_service_fee' . $i . '" value="' . format_number($row['service_fee']) . '" maxlength="25" /></td>
							<td data-label="Thành tiền"><input class="allow-number-only text-center" onblur="calculateLineTotal(' . $i . ')" type="text" name="bkd_total_price[]" id="bkd_total_price' . $i . '" value="' . format_number($row['total_price']) . '" maxlength="25" /></td>
							<td data-label="Giá mua"><input class="allow-number-only text-center" onblur="calculateLineTotal(' . $i . ');" type="text" name="bkd_total_bought_price[]" id="bkd_total_bought_price' . $i . '" value="' . ((float) $row['total_bought_price'] ? format_number($row['total_bought_price']) : format_number($row['total_price'] - ($row['service_fee']) * $row['quantity'])) . '" maxlength="25" /></td>
							<td data-label="Chiết khấu"><input class="allow-number-only text-center" onblur="calculateLineTotal(' . $i . ');" type="text" name="bkd_supplier_discount[]" id="bkd_supplier_discount' . $i . '" value="' . format_number($row['supplier_discount']) . '" maxlength="25" /></td>
							<td data-label="Phí xuất vé"><input class="allow-number-only text-center" onblur="calculateLineTotal(' . $i . ');" id="bkd_supplier_ticketing_fee' . $i . '" type="text" name="bkd_supplier_ticketing_fee[]" value="' . format_number($row['fee_bought']) . '" maxlength="25"></td>
							<td data-label="NCC"><select class="box-select bkd_select_supplier" id="bkd_supplier_id' . $i . '" name="bkd_supplier_id[]"><option value=""></option>' . myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', $row['supplier_id'], 'id', $supplier_cus_sql) . '</select></td>
							<td data-label="Xóa dòng" class="align-middle text-center"><button title="Xóa" type="button" onclick="markDetailRowDeleted(' . $i . ')" style="background:transparent; border:0;" >' . $this->icon_x . '</button><input type="hidden" value="0" name="bkd_deleted[]" id="bkd_deleted' . $i . '" /><input type="hidden" name="bkd_detail_id[]" id="bkd_detail_id' . $i . '" value="' . $detail_id . '" /></td>
						</tr>';

				// Thêm dòng phí admin chưa VAT
				$html .= '<tr id="bkd_admin_line_' . $i . '">';
				$html .= '<td data-label="Chi tiết phí Admin" colspan="15">
							<div class="addmin-fee-wrap d-flex gap-3 align-items-center">
								<div class="d-flex gap-1 align-items-center admin-fee-not-vat">
									<span class="text-label">Phí admin chưa VAT:</span>
									<input type="text" class="allow-number-only detail_ticket_input" name="bkd_admin_fee_no_vat[]" id="bkd_admin_fee_no_vat' . $i . '" onkeyup="calculateRelateAdminFee(' . $i . ');" onpaste="setTimeout(function(){calculateRelateAdminFee(' . $i . ');}, 10);" value="' . format_number($row['admin_fee_no_vat']) . '"/> 
								</div>
								<div class="d-flex gap-1 align-items-center admin-fee-vat">
									<span class="text-label">VAT admin: </span>
									<input type="text" class="allow-number-only detail_ticket_input" name="bkd_vat_admin[]" id="bkd_vat_admin' . $i . '" onkeyup="calculateRelateAdminFee(' . $i . ', 1);" onpaste="setTimeout(function(){calculateRelateAdminFee(' . $i . ', 1);}, 10);" value="' . format_number($row['vat_admin']) . '"/>
								</div>
							</div>
						</td>';
				$html .= '</tr>';

				$i++;
			}

			$total_qty = isset($_POST['total_qty']) && !empty($_POST['total_qty']) ? $_POST['total_qty'] : (isset($this->bean->total_qty) ? $this->bean->total_qty : 0);
			$subtotal_amount = isset($_POST['subtotal_amount']) && !empty($_POST['subtotal_amount']) ? $_POST['subtotal_amount'] : (isset($this->bean->subtotal_amount) ? $this->bean->subtotal_amount : 0);
			$total_bought_amount = isset($_POST['total_bought_amount']) && !empty($_POST['total_bought_amount']) ? $_POST['total_bought_amount'] : (isset($this->bean->total_bought_amount) ? $this->bean->total_bought_amount : 0);

			// Total
			$html .= '<tr id="bkd_last_row" class="footer-tr">
				<td colspan="2">
					<input type="hidden" name="bkd_row_count" id="bkd_row_count" value="' . $row_count . '" />
					<input type="hidden" name="supplier_list" id="supplier_list" value="' . $supplier_list . '" />
					<div class="d-flex align-items-center gap-2">
						<input type="button" class="btn btn-primary" id="btnDetailAddRow" value="Thêm dòng" title="Thêm dòng" />
						<p>Số dòng = <span id="lbl_bkd_row_count">' . $row_count . '</span></p>
					</div>
				</td>
				<td data-label="Tổng số vé"><input type="text" readonly="readonly" name="total_qty" id="total_qty" value="' . format_number($total_qty) . '" /></td>
				<td class="hide-mobile"  colspan="5"></td>
				<td data-label="Tổng thành tiền" class="text-center">
					<input type="text" class="text-danger" readonly="readonly" name="subtotal_amount" id="subtotal_amount" value="' . format_number($subtotal_amount) . '" />
				</td>
				<td data-label="Tổng giá mua" class="text-center">
					<input type="text" class="text-danger" readonly="readonly" name="total_bought_amount" id="total_bought_amount" value="' . format_number($total_bought_amount) . '" />
				</td>
				<td class="hide-mobile" colspan="4"></td>
			</tr>';
			$html .= '</tbody>';
		} else {
			// TẠO MỚI CHI TIẾT VÉ
			$html .= '<tr id="bkd_last_row" class="footer-tr">
						<td colspan="2" style="margin-top: 3px;">
							<input type="hidden" name="bkd_row_count" id="bkd_row_count" value="0" />
							<input type="hidden" name="supplier_list" id="supplier_list" value="' . $supplier_list . '" />
							<div class="d-flex align-items-center gap-2">
								<input type="button" class="btn btn-primary" id="btnDetailAddRow" value="Thêm dòng" title="Thêm dòng" />
								<p>Số dòng = <span id="lbl_bkd_row_count">0</span></p>
							</div>
						</td>
						<td><input type="text" readonly="readonly" name="total_qty" id="total_qty" value="0" /></td>
						<td colspan="5"></td>
						<td class="text-center">
							<input type="text" class="text-danger" readonly="readonly" name="subtotal_amount" id="subtotal_amount" value="0" />
						</td>
						<td class="text-center">
							<input type="text" class="text-danger" readonly="readonly" name="total_bought_amount" id="total_bought_amount" value="0" />
						</td>
						<td colspan="4"></td>
					</tr>';
		}

		$html .= '</table>';
		$this->ss->assign('LINE_DETAILS', $html);
	}

	/* HÀNH KHÁCH */
	function populateLinePassengersOld()
	{
		global $app_list_strings, $timedate, $current_user;
		$date_format = $timedate->get_date_format();
		$sql_supplier = " AND account_type = 'Supplier' AND is_stop_tracking = 0 ";

		$sql = "SELECT p.id,
					p.type,
					p.salutation,
					p.name,
					p.birthday,
					p.eticket_outbound,
					p.eticket_inbound,
					p.eluggage_outbound,
					p.eluggage_inbound,
					p.pnr_outbound,
					p.pnr_inbound,
					p.luggage_price,
					p.luggage_price_inbound,
					p.luggage_purchase,
					p.luggage_purchase_inbound,
					p.supplier_id,
					p.supplier_inbound_id,
					p.luggage_index_outbound,
					p.luggage_index_inbound,
					p.cic,
					p.passport_number
				FROM ec_booking_passengers p
				WHERE p.booking_id = '{$this->bean->id}'
					AND p.booking_id IS NOT NULL
					AND p.booking_id != ''
					AND add_type IS NULL
					AND p.deleted = 0
				ORDER BY p.type, p.date_entered";

		$res = $this->bean->db->query($sql);
		$row_count = $this->bean->db->countRows($res);
		$row_count = !empty($row_count) ? $row_count : 0;

		$html = '';
		$html .= '<table id="tbl_line_passengers" class="table-vertical__mobile table-edit__booking table-config table-details__booking" cellpadding="0" cellspacing="0" border="0">';

		$html .= '<thead>
				<tr id="psg_first_row">
					<th scope="col" class="text-start fw-semibold" style="width:5%;">Loại HK</th>
					<th scope="col" class="text-center fw-semibold" style="width:5%;">Danh xưng</th>
					<th scope="col" class="text-center fw-semibold" style="width:12%;">Họ tên</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">Ngày sinh</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">Số vé lượt đi</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">Số vé lượt về</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">Số vé HL lượt đi</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">Số vé HL lượt về</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">PNR lượt đi</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">PNR lượt về</th>
					<th scope="col" class="text-center fw-semibold" style="width:12%;">Hành lý lượt đi</th>
					<th scope="col" class="text-center fw-semibold" style="width:12%;">Hành lý lượt về</th>
					<th scope="col">&nbsp;</th>
				</tr>
			</thead>';

		$i = 0;
		if (!empty($this->bean->id)) {
			$booking_date = $this->bean->date_entered;
		} else {
			$booking_date = '';
		}

		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$passenger_id = isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true' ? '' : $row['id'];
			$luggage_index_out = '';
			$luggage_index_in = '';

			// Hành lý lượt di
			$psg_luggage_price_out = generateLuggage($booking_date, $this->_outbound_airline, $this->_outbound_ticket_class, $row['type'], (int) $row['luggage_index_outbound'], 1, (int) $row['luggage_price']);
			if (!empty($row['luggage_index_outbound'])) {
				$luggage_index_out .= '<input type="hidden" name="psg_luggage_ob_ind[]" value="1" />';
			}

			// Hành lý lượt về
			if (is_null($row['luggage_index_inbound']) || empty($row['luggage_index_inbound'])) {
				$row['luggage_index_inbound'] = $row['luggage_price_inbound'];
			}
			$psg_luggage_price_in = generateLuggage($booking_date, $this->_inbound_airline, $this->_inbound_ticket_class, $row['type'], (int) $row['luggage_index_inbound'], 1, (int) $row['luggage_price_inbound']);
			if (!empty($row['luggage_index_inbound'])) {
				$luggage_index_in .= '<input type="hidden" name="psg_luggage_ib_ind[]" value="1" />';
			}

			if (empty($psg_luggage_price_out)) {
				$psg_luggage_price_out = '<option value="0">--không--</option>';
			}
			if (empty($psg_luggage_price_in)) {
				$psg_luggage_price_in = '<option value="0">--không--</option>';
			}

			##### Line 1 (Thông tin hành khách) #####
			$html .= '<tr id="psg_line_' . $i . '" class="psg_line">';

			// Loại khách hàng
			$html .= '<td data-label="Loại HK">
				<select name="psg_traveller_type[]" id="psg_traveller_type' . $i . '" class="w-100">
					' . get_select_options_with_id($app_list_strings['passenger_type_list'], (int) $row['type']) . '
				</select>
			</td>';

			// Danh xưng
			$html .= '<td data-label="Danh xưng">
				<select name="psg_salutation[]" id="psg_salutation' . $i . '" class="w-100">
					' . get_select_options_with_id($app_list_strings['passenger_salutation_list'], (int) $row['salutation']) . '
				</select>
			</td>';

			// Họ tên
			$html .= '<td data-label="Họ tên">
				<input type="text" name="psg_full_name[]" id="psg_full_name' . $i . '" value="' . $row['name'] . '" class="text-start" maxlength="128" />
				<label class="mt-1 fw-bold">CCCD:</label>
				<input type="text" name="psg_cic[]" id="psg_cic' . $i . '" value="' . $row['cic'] . '" class="text-start" maxlength="16" />
			</td>';

			// Ngày sinh
			$html .= '<td data-label="Ngày sinh">
				<div class="d-flex align-items-center gap-1">
					<input type="text" name="psg_birthday[]" id="psg_birthday' . $i . '" 
						value="' . (isset($row['birthday']) && !empty($row['birthday']) && $row['birthday'] != '0000-00-00' ? date($date_format, strtotime($row['birthday'])) : '') . '" maxlength="10" />
					<img class="flex-fill cursor-pointer" border="0" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="psg_birthday_trigger' . $i . '" align="absmiddle" />
				</div>
				<label class="mt-1 fw-bold">Passport:</label>
				<input type="text" name="psg_passport_number[]" id="psg_passport_number' . $i . '" value="' . $row['passport_number'] . '" class="text-start" maxlength="10" />
			</td>';

			// Số vé lượt đi
			$html .= '<td data-label="Số vé lượt đi"><input type="text" name="psg_eticket_outbound[]" id="psg_eticket_outbound' . $i . '" value="' . $row['eticket_outbound'] . '" class="text-center" maxlength="25" /></td>';
			// Số vé lượt về
			$html .= '<td data-label="Số vé lượt về"><input type="text" name="psg_eticket_inbound[]" id="psg_eticket_inbound' . $i . '" value="' . $row['eticket_inbound'] . '" class="text-center" maxlength="25" /></td>';
			// Số vé HL lượt đi
			$html .= '<td data-label="Số vé HL lượt đi"><input type="text" name="psg_eluggage_outbound[]" id="psg_eluggage_outbound' . $i . '" value="' . $row['eluggage_outbound'] . '" class="text-center" maxlength="25" /></td>';
			// Số vé HL lượt về
			$html .= '<td data-label="Số vé HL lượt về"><input type="text" name="psg_eluggage_inbound[]" id="psg_eluggage_inbound' . $i . '" value="' . $row['eluggage_inbound'] . '" class="text-center" maxlength="25" /></td>';
			// PNR lượt đi
			$html .= '<td data-label="PNR lượt đi"><input type="text" name="psg_pnr_outbound[]" id="psg_pnr_outbound' . $i . '" value="' . $row['pnr_outbound'] . '" class="text-center" maxlength="30" /></td>';
			// PNR lượt về
			$html .= '<td data-label="PNR lượt về"><input type="text" name="psg_pnr_inbound[]" id="psg_pnr_inbound' . $i . '" value="' . $row['pnr_inbound'] . '" class="text-center" maxlength="30" /></td>';

			// Hành lý lượt đi
			$html .= '<td data-label="HL lượt đi">
				<select name="psg_luggage_price[]" id="psg_luggage_price' . $i . '" class="text-start w-100" onchange="calculateLuggagePrice()">
					' . $psg_luggage_price_out . '
				</select>
				' . $luggage_index_out . '
			</td>';

			// Hành lý lượt về
			$html .= '<td data-label="HL lượt về">
				<select name="psg_luggage_price_inbound[]" id="psg_luggage_price_inbound' . $i . '" class="text-start w-100" onchange="calculateLuggagePrice()">
					' . $psg_luggage_price_in . '
				</select>
				' . $luggage_index_in . '
			</td>';

			// Nút xóa
			$html .= '<td data-label="Xóa dòng" class="text-center align-middle">
				<button type="button" title="Xóa" class="button-remove-in-edit" onclick="markPassengerRowDeleted(' . $i . ')">' . $this->icon_x . '</button>
				<input type="hidden" name="psg_deleted[]" id="psg_deleted' . $i . '" value="0" />
				<input type="hidden" name="psg_id[]" id="psg_id' . $i . '" value="' . $passenger_id . '" readonly />
			</td>';
			$html .= '</tr>';

			#####  Line 2 (Hành lý đi nếu có)  #####
			$html .= '<tr id="psg_line_desc_' . $i . '">
					<td data-label="Thông tin HL đi" class="row_psg_price" colspan="13">
						<div class="psg_price-wrap d-flex gap-2 align-items-center">
							<div class="col_psg_price flex-fill">
								<span class="text-label">Giá mua HL lượt đi (VAT): </span>
								<input type="text" name="psg_luggage_purchase[]" id="psg_luggage_purchase' . $i . '" class="allow-number-only psg_luggage_purchase_input"
									value="' . format_number($row['luggage_purchase']) . '"
									maxlength="25"
									onkeyup="calculateLugPurchasePrice(' . $i . ', 0);"
									onpaste="calculateLugPurchasePrice(' . $i . ', 0);"
								/>
							</div>
							<div class="col_psg_price flex-fill">
								<span class="text-label">NCC HL lượt đi: </span>
								<select name="psg_luggage_supplier[]" id="psg_luggage_supplier' . $i . '" class="psg_luggage_purchase_select">
									<option value=""></option>
									' . myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', $row['supplier_id'], 'id', $sql_supplier) . '
								</select>
							</div>
							<div class="col_psg_price flex-fill">
								<span class="text-label">Giá mua HL lượt đi: </span>
								<input type="text" name="psg_detail_lug_pur_no_vat[]" id="psg_detail_lug_pur_no_vat' . $i . '" class="allow-number-only psg_luggage_purchase_input"
									onkeyup="calculateLugPurchasePrice(' . $i . ', 0);" 
								/>
							</div>
							<div class="col_psg_price flex-fill">
								<span class="text-label">VAT giá mua HL lượt đi: </span>
								<input type="text" name="psg_detail_lug_pur_vat[]" id="psg_detail_lug_pur_vat' . $i . '" class="allow-number-only psg_luggage_purchase_input"
									onkeyup="calculateLugPurchasePrice(' . $i . ', 0);"
								/>
							</div>
						</div>
					</td>
				</tr>';

			#####  Line 3 (Hành lý về nếu có)  #####
			$html .= '<tr id="psg_line_lug_' . $i . '">
						<td data-label="Thông tin HL về" class="row_psg_price" colspan="13">
							<div class="psg_price-wrap d-flex gap-2 align-items-center">
								<div class="col_psg_price flex-fill">
									<span class="text-label">Giá mua HL lượt về (VAT): </span>
									<input type="text" name="psg_luggage_purchase_inbound[]" id="psg_luggage_purchase_inbound' . $i . '" class="allow-number-only psg_luggage_purchase_input"
										value="' . format_number($row['luggage_purchase_inbound']) . '"
										maxlength="25"
										onkeyup="calculateLugPurchasePrice(' . $i . ', 1);"
										onpaste="calculateLugPurchasePrice(' . $i . ', 1);"
									/>
								</div>
								<div class="col_psg_price flex-fill">
									<span class="text-label">NCC HL lượt về: </span>
									<select name="psg_luggage_supplier_inbound[]" class="psg_luggage_purchase_select" id="psg_luggage_supplier_inbound' . $i . '" >
										<option value=""></option>
										' . myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', $row['supplier_inbound_id'], 'id', $sql_supplier) . '
									</select>
								</div>
								<div class="col_psg_price flex-fill">
									<span class="text-label">Giá mua HL lượt về: </span>
									<input type="text" name="psg_detail_lug_pur_ib_no_vat[]" id="psg_detail_lug_pur_ib_no_vat' . $i . '" class="allow-number-only psg_luggage_purchase_input"
										onkeyup="calculateLugPurchasePrice(' . $i . ', 1);"
									/>
								</div>
								<div class="col_psg_price flex-fill">
									<span class="text-label">VAT giá mua HL lượt về: </span>
									<input type="text" name="psg_detail_lug_pur_ib_vat[]" id="psg_detail_lug_pur_ib_vat' . $i . '" class="allow-number-only psg_luggage_purchase_input"
										onkeyup="calculateLugPurchasePrice(' . $i . ', 1);"
									/>
								</div>
							</div>
						</td>
					</tr>';

			$i++;
		}

		$html .= '<tr id="psg_last_row" class="footer-tr">
			<td colspan="13" class="text-start">
				<input type="button" class="btn btn-primary" id="btnPassengerAddRow" value="Thêm dòng" title="Thêm dòng" />
				Số dòng = <label id="lbl_psg_row_count">' . $row_count . '</label>
				<input type="hidden" name="psg_row_count" id="psg_row_count" value="' . $row_count . '" />
				<input type="hidden" id="booking_status" value="' . $this->bean->booking_status . '" >
			</td>
		</tr>';
		$html .= '</table>';
		$this->ss->assign('LINE_PASSENGERS', $html);
	}

	/**
	 * Render passengers info as HTML
	 */
	public function populateLinePassengers()
	{
		global $app_list_strings, $timedate, $current_user;

		$date_format = $timedate->get_date_format();
		$sql_supplier = " AND account_type = 'Supplier' AND is_stop_tracking = 0 ";

		$sql = "SELECT p.id,
				p.type,
				p.salutation,
				p.name,
				p.birthday,
				p.eticket_outbound,
				p.eticket_inbound,
				p.eluggage_outbound,
				p.eluggage_inbound,
				p.pnr_outbound,
				p.pnr_inbound,
				p.supplier_id,
				p.supplier_inbound_id,
				p.luggage_price,
				p.luggage_price_inbound,
				p.luggage_purchase_no_vat,
				p.vat_luggage_purchase,
				p.luggage_purchase,
				p.luggage_purchase_text,
				p.luggage_purchase_inbound_no_vat,
				p.vat_luggage_purchase_inbound,
				p.luggage_purchase_inbound,
				p.luggage_purchase_text_inbound,
				p.luggage_index_outbound,
				p.luggage_index_inbound,
				p.hand_baggage_outbound,
				p.hand_baggage_inbound,
				p.cic,
				p.passport_number
			FROM ec_booking_passengers p
			WHERE p.booking_id = '{$this->bean->id}'
				AND p.booking_id IS NOT NULL
				AND p.booking_id != ''
				AND (p.add_type NOT IN (1, 2) OR p.add_type IS NULL)
				AND p.deleted = 0
			ORDER BY p.type, p.date_entered";

		// if($current_user->user_name == 'hungnh'){
		// 	pr($sql);
		// }

		$res = $this->bean->db->query($sql);
		$row_count = $this->bean->db->countRows($res);
		$row_count = !empty($row_count) ? $row_count : 0;

		$html = '<table id="tbl_line_passengers" class="table-vertical__mobile table-edit__booking table-config table-details__booking" cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr id="psg_first_row">
					<th scope="col" class="text-center fw-semibold" style="width:9%;">Loại HK</th>
					<th scope="col" class="text-center fw-semibold" style="width:8%;">Danh xưng</th>
					<th scope="col" class="text-center fw-semibold" style="width:20%;">Họ tên</th>
					<th scope="col" class="text-center fw-semibold" style="width:10%;">Ngày sinh</th>
					<th scope="col" class="text-center fw-semibold" style="width:12%;">CCCD/Passport</th>
					<th scope="col" class="text-center fw-semibold" style="width:9%;">PNR lượt đi</th>
					<th scope="col" class="text-center fw-semibold" style="width:9%;">PNR lượt về</th>
					<th scope="col" class="text-center fw-semibold" style="width:11%;">Số vé lượt đi</th>
					<th scope="col" class="text-center fw-semibold" style="width:11%;">Số vé lượt về</th>
					<th scope="col">&nbsp;</th>
				</tr>
			</thead>
		';

		$i = 0;
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$passenger_id = isset($_POST['isDuplicate']) && (string)$_POST['isDuplicate'] === 'true' ? '' : $row['id'];

			##### Line 1 (Thông tin hành khách) #####
			$html .= '<tr id="psg_line_' . $i . '" class="psg_line">';

			// Loại khách hàng
			$html .= '<td data-label="Loại HK">
				<select name="psg_traveller_type[]" id="psg_traveller_type' . $i . '" class="w-100">
					' . get_select_options_with_id($app_list_strings['passenger_type_list'], (int) $row['type']) . '
				</select>
        	</td>';

			// Danh xưng
			$html .= '<td data-label="Danh xưng">
				<select name="psg_salutation[]" id="psg_salutation' . $i . '" class="w-100">
					' . get_select_options_with_id($app_list_strings['passenger_salutation_list'], (int) $row['salutation']) . '
				</select>
			</td>';

			// Họ tên
			$html .= '<td data-label="Họ tên">
				<input type="text" name="psg_full_name[]" id="psg_full_name' . $i . '" value="' . $row['name'] . '" class="text-start" maxlength="128" style="padding-left: 8px !important;" />
			</td>';

			// Ngày sinh
			$html .= '<td data-label="Ngày sinh">
				<div class="d-flex align-items-center gap-1">
					<input type="text" name="psg_birthday[]"
						id="psg_birthday' . $i . '" 
						value="' . (isset($row['birthday']) && !empty($row['birthday']) && $row['birthday'] != '0000-00-00' ? date($date_format, strtotime($row['birthday'])) : '') . '"
						class="text-center"
						maxlength="10"
					/>
					<img class="flex-fill cursor-pointer" border="0" src="themes/SuiteP/images/Calendar.svg" alt="Enter Date" id="psg_birthday_trigger' . $i . '" align="absmiddle" />
				</div>
			</td>';

			// CCCD/Passport
			$id_number_value = trim($row['passport_number'] ?? '');
			if (empty($id_number_value)) $id_number_value = trim($row['cic'] ?? '');
			$html .= '<td data-label="CCCD/Passport">
				<input type="text" name="psg_id_number[]"
					id="psg_id_number' . $i . '"
					value="' . $id_number_value . '"
					class="text-start"
					maxlength="16"
					style="padding-left:8px !important; letter-spacing:1px;"
				/>
			</td>';

			// PNR lượt đi
			$html .= '<td data-label="PNR lượt đi"><input type="text" name="psg_pnr_outbound[]" id="psg_pnr_outbound' . $i . '" value="' . $row['pnr_outbound'] . '" class="text-center" maxlength="30" /></td>';
			// PNR lượt về
			$html .= '<td data-label="PNR lượt về"><input type="text" name="psg_pnr_inbound[]" id="psg_pnr_inbound' . $i . '" value="' . $row['pnr_inbound'] . '" class="text-center" maxlength="30" /></td>';
			// Số vé lượt đi
			$html .= '<td data-label="Số vé lượt đi"><input type="text" name="psg_eticket_outbound[]" id="psg_eticket_outbound' . $i . '" value="' . $row['eticket_outbound'] . '" class="text-center" maxlength="25" /></td>';
			// Số vé lượt về
			$html .= '<td data-label="Số vé lượt về"><input type="text" name="psg_eticket_inbound[]" id="psg_eticket_inbound' . $i . '" value="' . $row['eticket_inbound'] . '" class="text-center" maxlength="25" /></td>';

			// Nút xóa
			$html .= '<td data-label="Xóa dòng" class="text-center align-middle">
				<button type="button" title="Xóa" class="button-remove-in-edit" onclick="markPassengerRowDeleted2(' . $i . ')">' . $this->icon_x . '</button>
				<input type="hidden" name="psg_deleted[]" id="psg_deleted' . $i . '" value="0" />
				<input type="hidden" name="psg_id[]" id="psg_id' . $i . '" value="' . $passenger_id . '" readonly />
			</td>';
			$html .= '</tr>';

			// Hành lý - Use different baggage options for each direction
			foreach (["outbound", "inbound"] as $roundName) {
				$suffix = $roundName == "outbound" ? "" : "_inbound";
				$dir = $roundName == "outbound" ? 0 : 1;

				// Input name
				$inputNameBagText = "psg_luggage_purchase_text$suffix";
				$inputNameBagPrice = "psg_luggage_purchase$suffix";
				$inputNameBagTax = "psg_vat_luggage_purchase$suffix";
				$inputNameSuppplier = "psg_luggage_supplier$suffix";
				$inputNameTicketNum = "psg_eluggage_$roundName";
				$inputNameSellingPrice = "psg_luggage_price$suffix"; // Giá bán
				$inputNameAvaiBagIndex = "psg_luggage_index_$roundName";

				$inputNameHandBagIndex = "psg_hand_baggage_$roundName";

				// Value
				$bagText = $row["luggage_purchase_text$suffix"] ?? '';
				$bagPrice = $row["luggage_purchase$suffix"] ?? 0;
				$bagTax = $row["vat_luggage_purchase$suffix"] ?? 0; // VAT
				$bagSuppplier = $row["supplier{$suffix}_id"] ?? '';
				$bagTicketNum = $row["eluggage_$roundName"] ?? '';
				$bagSellingPrice = $row["luggage_price$suffix"] ?? 0;
				$avaiBag = $row["luggage_index_$roundName"] ?? '';
				$handBag = $row["hand_baggage_$roundName"] ?? '';

				// Label
				$suffix_text = $roundName == "outbound" ? "lượt đi" : "lượt về";

				// Build baggage options dropdown
				$baggageOptionsHtml = $this->bean->generateBaggageOptions($roundName == "inbound" ? $this->bean->airline_inbound : $this->bean->airline, '', $bagText, $bagPrice);

				$html .= '<tr id="psg_baggage_line_' . $roundName . '_' . $i . '">
					<td data-label="' . $roundName . ' baggage information" class="row_psg_price" colspan="10">
						<div class="psg_price-wrap d-flex gap-3 align-items-center mb-1">
							<span class="text-label" style="width:155px;">Hành lý xách tay ' . $suffix_text . ':</span>
							<div>
								<input type="text" name="' . $inputNameHandBagIndex . '[]"
									id="' . ($inputNameHandBagIndex . $i) . '"
									value="' . $handBag . '"
									style="width:80px" maxlength="6" size="6"
								/> 
								<button type="button" title="Hướng dẫn nhập liệu" style="border:none; background:none; padding:0;"
									data-bs-toggle="popover"
									data-bs-html="true"
									data-bs-content="Nhập <b>1x23</b> = 1 kiện x 23kg<br>Nhập <b>1T23</b> = 1 kiện tổng 23kg<br>Nhập <b>5</b> trở xuống = 5 kiện<br>Nhập <b>6</b> trở lên = 6kg">
									<svg width="18px" height="18px" stroke-width="2.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#a1a1a1"><path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#a1a1a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9 9C9 5.49997 14.5 5.5 14.5 9C14.5 11.5 12 10.9999 12 13.9999" stroke="#a1a1a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 18.01L12.01 17.9989" stroke="#a1a1a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
								</button>
							</div>
						</div>
						<div class="psg_price-wrap d-flex gap-3 align-items-center mb-1">
							<span class="text-label" style="width:155px;">Hành lý có sẵn ' . $suffix_text . ':</span>
							<div>
								<input type="text" name="' . $inputNameAvaiBagIndex . '[]"
									id="' . ($inputNameAvaiBagIndex . $i) . '"
									value="' . $avaiBag . '"
									style="width:80px" maxlength="6" size="6"
								/> 
								<button type="button" title="Hướng dẫn nhập liệu" style="border:none; background:none; padding:0;"
									data-bs-toggle="popover"
									data-bs-html="true"
									data-bs-content="Nhập <b>1x23</b> = 1 kiện x 23kg<br>Nhập <b>1T23</b> = 1 kiện tổng 23kg<br>Nhập <b>5</b> trở xuống = 5 kiện<br>Nhập <b>6</b> trở lên = 6kg">
									<svg width="18px" height="18px" stroke-width="2.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#a1a1a1"><path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#a1a1a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9 9C9 5.49997 14.5 5.5 14.5 9C14.5 11.5 12 10.9999 12 13.9999" stroke="#a1a1a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 18.01L12.01 17.9989" stroke="#a1a1a1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
								</button>
							</div>
						</div>
						<div class="psg_price-wrap d-flex gap-3 align-items-center">
							<div class="col_psg_price col-psg-bag-text">
								<span class="text-label">Hành lý mua thêm ' . $suffix_text . '</span>
								<select name="' . $inputNameBagText . '[]"
									id="' . ($inputNameBagText . $i) . '"
									class="psg_luggage_purchase_select"
									style="width:100%; max-width:400px;"
									onchange="updateBaggagePriceFromSelect(' . $i . ', \'' . $roundName . '\')">
									' . $baggageOptionsHtml . '
								</select>
							</div>
							<div class="col_psg_price col-psg-bag-selling-price">
								<span class="text-label">Giá bán (VAT): </span>
								<input type="text" name="' . $inputNameSellingPrice . '[]"
									id="' . ($inputNameSellingPrice . $i) . '"
									value="' . format_number($bagSellingPrice) . '"
									class="allow-number-only psg_luggage_purchase_input"
									maxlength="12"
								/>
							</div>
							<div class="col_psg_price col-psg-bag-price">
								<span class="text-label">Giá mua (VAT): </span>
								<input type="text" name="' . $inputNameBagPrice . '[]"
									id="' . ($inputNameBagPrice . $i) . '"
									value="' . format_number($bagPrice) . '"
									class="allow-number-only psg_luggage_purchase_input"
									maxlength="12"
									onkeyup="calculateBagPurchasePrice(' . $i . ', ' . $dir . ');"
									onpaste="calculateBagPurchasePrice(' . $i . ', ' . $dir . ');"
								/>
							</div>
							<div class="col_psg_price col-psg-bag-tax">
								<span class="text-label">VAT giá mua: </span>
								<input type="text" name="' . $inputNameBagTax . '[]"
									id="' . ($inputNameBagTax . $i) . '"
									value="' . format_number($bagTax) . '"
									class="allow-number-only psg_luggage_purchase_input"
									maxlength="12"
									onkeyup="calculateBagPurchasePrice(' . $i . ', ' . $dir . ');"
								/>
							</div>
							<div class="col_psg_price col-psg-bag-supplier">
								<span class="text-label">NCC: </span>
								<select name="' . $inputNameSuppplier . '[]" id="' . ($inputNameSuppplier . $i) . '" class="psg_luggage_purchase_select">
									<option value=""></option>
									' . myGetSelectOptionsWithDbExt('Accounts', 'ticker_symbol', $bagSuppplier, 'id', $sql_supplier) . '
								</select>
							</div>
							<div class="col_psg_price col-psg-bag-ticketnum">
								<span class="text-label">Số vé HL ' . $suffix_text . ': </span>
								<input type="text" name="' . $inputNameTicketNum . '[]"
									id="' . ($inputNameTicketNum . $i) . '"
									value="' . $bagTicketNum . '"
									class="psg_luggage_purchase_input"
									maxlength="25" size="25"
								/>
							</div>
						</div>
					</td>
				</tr>';
			}

			$i++;
		}

		$html .= '<tr id="psg_last_row" class="footer-tr">
			<td colspan="13" class="text-start">
				<input type="button" class="btn btn-primary" id="btnPassengerAddRow" data-is-new="1" value="Thêm dòng" title="Thêm dòng" />
				Số dòng = <label id="lbl_psg_row_count">' . $row_count . '</label>
				<input type="hidden" name="psg_row_count" id="psg_row_count" value="' . $row_count . '" />
				<input type="hidden" id="booking_status" value="' . $this->bean->booking_status . '" >
				<input type="hidden" id="baggage_options_outbound" value="' . htmlspecialchars(json_encode($this->bean->generateBaggageOptions($this->bean->airline)), ENT_QUOTES, 'UTF-8') . '" />
				<input type="hidden" id="baggage_options_inbound" value="' . htmlspecialchars(json_encode($this->bean->generateBaggageOptions($this->bean->airline_inbound)), ENT_QUOTES, 'UTF-8') . '" />
			</td>
		</tr>';
		$html .= '</table>';
		$this->ss->assign('LINE_PASSENGERS', $html);
	}

	// Bổ sung phần thông tin hoá đơn
	public function populateInvoiceFields()
	{
		$iv_payment_method = [
			''                           => '',
			'Tiền mặt'                   => 'Tiền mặt',
			'Chuyển khoản'               => 'Chuyển khoản',
			'Tiền mặt hoặc Chuyển khoản' => 'Tiền mặt hoặc Chuyển khoản',
		];

		$invoice_arr = json_decode(str_replace("&quot;", "\"", $this->bean->shipping_address), 1);
		$invoice_arr = is_array($invoice_arr) ? $invoice_arr : [];

		$this->ss->assign(
			'CUS_IV_ACCOUNT_NAME',
			'<input type="text" id="iv_account_name" name="iv_account_name" size="30" value="' . $invoice_arr['iv_account_name'] . '" />'
		);
		$this->ss->assign(
			'CUS_IV_EMAIL',
			'<input type="text" id="iv_email" name="iv_email" size="30" value="' . $invoice_arr['iv_email'] . '" />'
		);
		$this->ss->assign(
			'CUS_IV_IDENTITY_NUMBER',
			'<input type="text" id="iv_identity_number" name="iv_identity_number" size="12" value="' . $invoice_arr['iv_identity_number'] . '" />'
		);
		$this->ss->assign(
			'CUS_IV_PAYMENT_METHOD',
			'<select name="iv_payment_method" class="w-100">'
				. get_select_options_with_id($iv_payment_method, $invoice_arr['iv_payment_method'] ?? '')
				. '</select>'
		);
		$this->ss->assign(
			'CUS_IV_BANK_ACCOUNT',
			'<input type="text" name="iv_bank_account" size="30" value="' . $invoice_arr['iv_bank_account'] . '" />'
		);
		$this->ss->assign(
			'CUS_IV_NAME_BANK',
			'<input type="text" name="iv_name_banks" size="30" value="' . $invoice_arr['iv_name_banks'] . '" />'
		);
	}
}
