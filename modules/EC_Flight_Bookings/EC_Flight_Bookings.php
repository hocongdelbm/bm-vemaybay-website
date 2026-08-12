<?php
// date_default_timezone_set('Asia/Ho_Chi_Minh');

class EC_Flight_Bookings extends Basic
{
	public $new_schema = true;
	public $module_dir = 'EC_Flight_Bookings';
	public $object_name = 'EC_Flight_Bookings';
	public $table_name = 'ec_flight_bookings';
	public $importable = true;

	public $id;
	public $name;
	public $date_entered;
	public $date_modified;
	public $modified_user_id;
	public $modified_by_name;
	public $created_by;
	public $created_by_name;
	public $description;
	public $deleted;
	public $created_by_link;
	public $modified_user_link;
	public $assigned_user_id;
	public $assigned_user_name;
	public $assigned_user_link;
	public $SecurityGroups;

	public $is_invoice_export;
	public $is_hold;
	public $is_ticket_exported;
	public $is_ticket_inbound_exported;
	public $is_paid;
	public $is_recheck_success;

	public $payment_type;
	public $ticket_type;
	public $recheck_status;
	public $booking_status;
	public $contact_title;
	public $contact_name;
	public $salutation;
	public $email;
	public $email_reservation;
	public $phone;
	public $address;
	public $holding_status;
	public $flight_type;
	public $luggage_fee;
	public $discount_amount;
	public $city;
	public $airline;
	public $airline_inbound;
	public $shipping_address;
	public $agent_id;
	public $agent_name;
	public $subtotal_amount;
	public $total_bought_amount;
	public $total_bought_price;
	public $invoice_require;
	public $date_ticket_issue;
	public $date_ticket_inbound_issue;
	public $nganluong_info;
	public $nganluong_code;
	public $nganluong_datepaid;
	public $journey;

	public $lydothangthua_id;
	public $ghichuthangthua;
	public $delivery_man_id;
	public $delivery_man;
	public $total_qty;
	public $total_amount;
	public $ip_address;
	public $account_id;
	public $account_name;
	public $contact_id;
	public $tax_code;
	public $company_name;
	public $company_address;
	public $discount_percent;
	public $customer_source;
	public $zalo_id;

	public $is_agent;
	public $is_prior;
	public $is_reference;
	public $is_ctv;
	public $is_telesale;
	public $telesale_call_id;
	public $is_output_invoice_checked;
	public $is_invoice_input_export;
	public $is_mail_confirm;

	// Attributes are not included in vardefs
	public $contact_name_ignore = ['THAM KHAO', 'TEST', 'IT', 'DEMO'];
	public $point_step = 50;
	public $_isNewBooking = false;
	public $_isDuplicate = false;

	public function bean_implements($interface)
	{
		switch ($interface) {
			case 'ACL':
				return true;
		}

		return false;
	}

	public function save($check_notify = FALSE)
	{
		// Set up current user for use new API
		$this->_initCurrentUser();

		// Disable mass update
		if ($this->_isMassUpdateBlocked()) {
			return false;
		}

		// Set name of booking
		$this->_resolveBookingName();

		// Convert to new prefix for number with 11 digits
		$this->_normalizePhone();

		$this->_normalizeContactFields();

		// Fix phí hành lý
		$this->_normalizeLuggageFee();

		// Giao cho
		$this->_resolveAssignedUser();

		// Lý do thắng thua
		$this->_resolveDescription();

		// Đánh dấu đã thanh toán
		$this->_resolveIsPaid();

		// Đánh dấu đã thanh toán
		$this->_resolveTotalQty();

		// City
		$this->_normalizeCity();

		// date_entered will disappear after parent::save() is executed
		$saving_date_entered = $this->date_entered;
		$recordId = parent::save($check_notify);
		$this->date_entered = $saving_date_entered;

		$this->_postSave($recordId);

		return $recordId;
	}

	public function save2($check_notify = FALSE)
	{
		return parent::save($check_notify);
	}

	// ─── Private helpers ──────────────────────────────────────────────
	// ──────────────────────────────────────────────────────────────────
	// ──────────────────────────────────────────────────────────────────
	private function _initCurrentUser()
	{
		global $current_user;

		if (is_null($current_user->id) || empty($current_user->id)) {
			$current_user = BeanFactory::getBean('Users', $this->created_by);
		}
	}

	private function _isMassUpdateBlocked(): bool
	{
		global $current_user;
		$isMassUpdate = !empty($_POST['massupdate']) && (string) $_POST['massupdate'] === 'true';
		$isPrivileged = is_admin($current_user) || in_array($current_user->title, ['KeToan', 'QuanLy']);

		return $isMassUpdate && !$isPrivileged;
	}

	private function _resolveBookingName()
	{
		global $current_user;

		$isDuplicate = !empty($_POST['duplicateSave']) && (string) $_POST['duplicateSave'] === 'true' && !empty($_POST['booking_prev_name']);

		if ($isDuplicate) {
			$prefix = substr($_POST['booking_prev_name'], 0, 2);
			$this->name = $prefix . $this->_genBookingName();
			$this->_isDuplicate = true;
			return;
		}

		if (empty($this->name)) {
			$prefix = !empty($current_user->agent_prefix) ? $current_user->agent_prefix : 'BK';
			$this->name = $prefix . $this->_genBookingName();
			$this->_isNewBooking = true;
			return;
		}

		// Booking đã có tên — kiểm tra nếu tên bị đổi
		$this->_validateAndUpdateBookingName();
	}

	// Generate booking random string
	private function _genBookingName(): string
	{
		$booking_name = '';
		$booking_name .= date('y') . date('m') . date('d');

		$qty_booking = dechex($this->_getNumberOfBookings() + 1);
		if (strlen($qty_booking) < 2)
			$qty_booking = '0' . $qty_booking;
		$booking_name .= strrev($qty_booking);

		return strtoupper($booking_name);
	}

	// Get number of bookings
	private function _getNumberOfBookings(): int
	{
		$count = 0;
		$sql = "SELECT COUNT(id)
				FROM ec_flight_bookings
				WHERE date_entered > '" . date('Y-m-d H:i:s', strtotime(date('Y-m-d 16:59:59')) - 86400) . "' "; // giờ sugarcrm lệch 7h so với giờ server

		$count = $this->db->getOne($sql);

		return (int) $count;
	}

	private function _validateAndUpdateBookingName()
	{
		global $db;

		// Dùng prepared statement thay vì nối chuỗi
		$currentName = $db->getOne(
			sprintf(
				'SELECT name FROM ec_flight_bookings WHERE id = %s',
				$db->quoted($this->id)
			)
		);

		if ($this->name === $currentName) {
			return;
		}

		$isDuplicated = (int) $db->getOne(
			sprintf(
				'SELECT COUNT(*) FROM ec_flight_bookings WHERE name = %s AND id != %s',
				$db->quoted(trim($this->name)),
				$db->quoted($this->id)
			)
		) > 0;

		if ($isDuplicated) {
			throw new RuntimeException('Tên booking bị trùng!');
		}

		$db->query(sprintf(
			'UPDATE ec_flight_bookings SET name = %s WHERE id = %s',
			$db->quoted(trim($this->name)),
			$db->quoted($this->id)
		));
	}

	private function _normalizePhone()
	{
		global $app_list_strings;

		$this->phone = preg_replace('/\D/', '', $this->phone);

		if (strlen($this->phone) === 11) {
			$prefix = substr($this->phone, 0, 4);
			$convertList = $app_list_strings['mobile_phone_new_prefix_convert_list'] ?? [];

			if (isset($convertList[$prefix])) {
				$this->phone = $convertList[$prefix] . substr($this->phone, 4);
			}
		}
	}

	private function _normalizeContactFields()
	{
		$this->contact_name = ucwords(strtolower(
			myRemoveUnicodeChars(trim(stripslashes($this->contact_name ?? '')))
		));

		$this->email = strtolower(
			myRemoveUnicodeChars(trim(stripslashes($this->email ?? '')))
		);
	}

	private function _normalizeLuggageFee()
	{
		if (!array_key_exists('luggage_fee', $_POST)) {
			return;
		}

		$postedFee = unformat_number($_POST['luggage_fee']);
		$this->luggage_fee = $postedFee < 1000 ? 0 : $postedFee;
	}

	private function _resolveAssignedUser()
	{
		global $current_user;

		if (!empty($_POST['assigned_user_id'])) {
			$this->assigned_user_id = $_POST['assigned_user_id'];
		} elseif (empty($this->assigned_user_id)) {
			$this->assigned_user_id = $current_user->id;
		}
	}

	private function _resolveDescription()
	{
		if (
			!empty($this->ghichuthangthua)
			&& (int) $this->booking_status === 4
		) {
			$this->description = $this->ghichuthangthua;
		}
	}

	private function _resolveIsPaid()
	{
		if (isset($_POST['is_paid'])) {
			$this->is_paid = $_POST['is_paid'];
		}
	}

	private function _resolveTotalQty()
	{
		// The number of ticket
		if (strlen($this->id) == 36) {
			$sql = "SELECT SUM(IFNULL(quantity, 0)) AS total_ticket
				FROM ec_booking_details
				WHERE booking_id = '{$this->id}' AND deleted = 0";
			$this->total_qty = (int) ($this->db->getOne($sql) ?? 0);
		}
	}

	private function _normalizeCity()
	{
		if (strpos($this->city ?? '', '-') !== false) {
			$airportCode = substr($this->city, 0, 3);
			$airportMap = EC_Airports::getAirportList();

			$this->city = $airportMap[$airportCode] ?? $this->city;
		} else {
			$this->city = ucwords(strtolower(trim(stripslashes($this->city ?? ''))));
		}
	}

	private function _postSave(string $recordId)
	{
		// Lưu thông tin hoá đơn
		$this->saveInvoiceInf($_POST, $recordId);

		// MST là bắt buộc khi xuất hóa đơn
		if (!empty($this->tax_code) && !empty($this->_isNewBooking)) {
			$this->_sendInvoiceAlert();
		}

		// Giao vé
		$this->_handleDeliveryManProcess();

		// Save journeys
		if (isset($_POST['iti_airline_code']) && !empty($_POST['iti_airline_code'])) {
			$this->saveLineItineraries();
		}

		// Save details
		if (isset($_POST['bkd_quantity']) && !empty($_POST['bkd_quantity'])) {
			$this->saveLineDetails();
		}

		// Save passengers
		if (isset($_POST['psg_id']) && !is_null($_POST['psg_id'])) {
			if ($this->isUseNewBaggage($this->date_entered, $this->created_by)) {
				$this->saveLinePassengers();
			} else {
				$this->saveLinePassengersOld();
			}
		}

		// Change flight time
		if (isset($_POST['save_change_flight'])) {
			$this->saveChangeFlightTime();
			updateIsPriorForBooking($this->id);
		}

		if (!empty($this->_isDuplicate)) {
			header("Location: index.php?module={$this->module_dir}&action=DetailView&record={$recordId}");
			exit();
		}
	}

	/**
	 * Thông báo yêu cầu xuất hoá đơn cho kế toán
	 */
	private function _sendInvoiceAlert()
	{
		$invArr = json_decode(str_replace('&quot;', '"', $this->shipping_address ?? '{}'), true);
		$name = $invArr['iv_account_name'] ?? '';
		$company = $this->company_name ?? '';

		if ($name && $company)
			$userInv = "{$name} [{$company}]";
		elseif ($company)
			$userInv = $company;
		else
			$userInv = $name;

		//Kế toán chịu trách nhiệm xuất hóa đơn
		$accountingUserIds = [
			'72ece22c-cb25-8e30-9dea-56f2201cd359', // trangbtq
		];

		$alertData = [
			'name' => $userInv,
			'parent_type' => $this->module_dir,
			'parent_id' => $this->id,
			'description' => "Booking {$this->name} yêu cầu xuất hóa đơn.",
			'url_redirect' => "index.php?module={$this->module_dir}&action=DetailView&record={$this->id}",
			'priority' => 'low',
			'type' => 'readonly',
		];

		$alert = new Alert();
		$alert->autoCreateAlert($this->module_dir, $accountingUserIds, $alertData);
	}

	private function _handleDeliveryManProcess()
	{
		$deliveryManChanged = !empty($this->delivery_man_id) && $this->fetched_row['delivery_man_id'] !== $this->delivery_man_id;

		if ($deliveryManChanged) {
			myRemoveWorkingProcess($this->module_dir, $this->id, 'ticket_delivery');

			/** @var EC_Working_Process **/
			$work = BeanFactory::newBean('EC_Working_Process');
			$work->name = $this->name;
			$work->description = trim($this->delivery_man ?? '');
			$work->parent_type = $this->module_dir;
			$work->parent_id = $this->id;
			$work->assigned_user_id = $this->delivery_man_id;
			$work->ticket_delivery = 1;
			$work->save();
		} elseif (empty($this->delivery_man_id)) {
			myRemoveWorkingProcess($this->module_dir, $this->id, 'ticket_delivery');
		}
	}

	// Save booking from webservice
	public function save_from_webservice($check_notify = FALSE)
	{
		return parent::save($check_notify);
	}

	/**
	 * Save line itineraries information
	 * ──────────────────────────────────────────────────────────────────
	 * ──────────────────────────────────────────────────────────────────
	 */
	private function saveLineItineraries()
	{
		$rows = $_POST['iti_airline_code'] ?? [];

		foreach (array_keys($rows) as $i) {
			$data = $this->_extractItineraryRow($i);
			$this->_saveItineraryRow($data);
		}
	}

	private function _extractItineraryRow(int $i): array
	{
		$post = $_POST;

		return [
			'id'               => $post['iti_detail_id'][$i]       ?? '',
			'is_layover'       => !empty($post['iti_is_layover'][$i]),
			'airline_code'     => $post['iti_airline_code'][$i]     ?? '',
			'flight_number'    => $post['iti_flight_number'][$i]    ?? '',
			'ticket_class'     => $post['iti_ticket_class'][$i]     ?? '',
			'departure'        => $post['iti_departure'][$i]        ?? '',
			'arrival'          => $post['iti_arrival'][$i]          ?? '',
			'departure_date'   => $post['iti_departure_date'][$i]   ?? '',
			'departure_h'      => $post['iti_departure_h'][$i]      ?? '00',
			'departure_m'      => $post['iti_departure_m'][$i]      ?? '00',
			'arrival_date'     => $post['iti_arrival_date'][$i]     ?? '',
			'arrival_h'        => $post['iti_arrival_h'][$i]        ?? '00',
			'arrival_m'        => $post['iti_arrival_m'][$i]        ?? '00',
			'time_limit_date'  => $post['iti_time_limit_date'][$i]  ?? '',
			'time_limit_h'     => $post['iti_time_limit_h'][$i]     ?? '00',
			'time_limit_m'     => $post['iti_time_limit_m'][$i]     ?? '00',
			'base_price'       => $post['iti_base_price'][$i]       ?? 0,
			'description'      => $post['iti_description'][$i]      ?? '',
			'direction'        => $post['iti_direction'][$i]        ?? '',
			'add_type'         => $post['iti_add_type'][$i]         ?? 0,
			'parent_detail_id' => $post['iti_parent_detail_id'][$i] ?? null,
			'deleted'          => (int) ($post['iti_deleted'][$i]   ?? 0),
		];
	}

	private function _saveItineraryRow(array $data)
	{
		/** @var EC_Booking_Itineraries **/
		$iti = BeanFactory::newBean('EC_Booking_Itineraries');

		// Load existing record nếu đang edit
		if (!empty($data['id'])) {
			$iti->retrieve($data['id']);
		}

		if ((int) $data['deleted'] === 1) {
			if (!empty($iti->id)) {
				$iti->mark_deleted($iti->id);
			}
			return;
		}

		// Map dữ liệu vào Bean
		$iti->name             = $data['is_layover'] ? 'layover' : 'route';
		$iti->airline_code     = $this->_normalizeUpper($data['airline_code']);
		$iti->flight_number    = $this->_normalizeUpper($data['flight_number']);
		$iti->departure        = $this->_normalizeUpper($data['departure']);
		$iti->arrival          = $this->_normalizeUpper($data['arrival']);
		$iti->ticket_class     = trim(stripslashes($data['ticket_class']));
		$iti->departure_date   = $this->_buildDatetime($data['departure_date'],  $data['departure_h'],  $data['departure_m']);
		$iti->arrival_date     = $this->_buildDatetime($data['arrival_date'],    $data['arrival_h'],    $data['arrival_m']);
		$iti->time_limit       = $this->_buildDatetime($data['time_limit_date'], $data['time_limit_h'], $data['time_limit_m']);
		$iti->base_price       = unformat_number($data['base_price']);
		$iti->is_layover       = (int) $data['is_layover'];
		$iti->booking_id       = $this->id;
		$iti->description      = $data['description'];
		$iti->direction        = $data['direction'];
		$iti->add_type         = $data['add_type'];
		$iti->parent_detail_id = $data['parent_detail_id'];

		// Guard: chỉ save khi có đủ dữ liệu tối thiểu
		if (!empty($iti->name) && !empty($iti->departure)) {
			$iti->save();
		}
	}

	/**
	 * Normalize string: strip slashes, trim, bỏ unicode, uppercase.
	 */
	private function _normalizeUpper(string $value): string
	{
		return strtoupper(myRemoveUnicodeChars(trim(stripslashes($value))));
	}

	/**
	 * Tạo datetime string 'Y-m-d HH:MM:00' từ date + giờ + phút.
	 * Trả về '' nếu date rỗng.
	 * Dùng sprintf để đảm bảo leading zero (09:05 thay vì 9:5).
	 */
	private function _buildDatetime(string $date, string $h, string $m): string
	{
		$date = trim($date);
		if ($date === '')
			return '';

		$timestamp = strtotime($date);
		if ($timestamp === false)
			return ''; // Guard: date không hợp lệ

		return sprintf(
			'%s %02d:%02d:00',
			date('Y-m-d', $timestamp),
			(int) $h,
			(int) $m
		);
	}

	/**
	 * Save line details information
	 * ──────────────────────────────────────────────────────────────────
	 * ──────────────────────────────────────────────────────────────────
	 */
	private function saveLineDetails()
	{
		global $app_list_strings, $current_user;

		// Validate sửa chi tiết vé - Admin, Quản lý, Kế toán mới được sửa sau khi đã xuất vé/hoàn tất.
		if (isset($_POST['edit_detail']) && in_array((int)$this->booking_status, [7, 8]) && !isManagerUser($current_user->id)) {
			return;
		}

		$rows = $_POST['bkd_quantity'] ?? [];
		$total_bought_amount = 0;

		foreach (array_keys($rows) as $i) {
			$data = $this->_extractDetailRow($i);
			$bought = $this->_saveDetailRow($data, $app_list_strings);

			// Chỉ cộng vào total nếu không bị xóa
			if ((int) $data['deleted'] !== 1) {
				$total_bought_amount += $bought;
			}
		}

		if (isset($_POST['edit_detail'])) {
			$this->_updateTotalBoughtAmount($total_bought_amount);

			// Cập nhật doanh số ec_revenue
			saveRevenueBooking($this->id);
		}
	}

	private function _extractDetailRow(int $i): array
	{
		return [
			'id'                   => $_POST['bkd_detail_id'][$i]            ?? '',
			'passenger_type'       => $_POST['bkd_passenger_type'][$i]       ?? '',
			'quantity'             => $_POST['bkd_quantity'][$i]              ?? 0,
			'unit_price'           => $_POST['bkd_unit_price'][$i]           ?? 0,
			'tax_and_fee'          => $_POST['bkd_tax_and_fee'][$i]          ?? 0,
			'airport_fee'          => $_POST['bkd_airport_fee'][$i]          ?? 0,
			'admin_fee'            => $_POST['bkd_admin_fee'][$i]            ?? 0,
			'service_fee'          => $_POST['bkd_service_fee'][$i]          ?? 0,
			'total_price'          => $_POST['bkd_total_price'][$i]          ?? 0,
			'total_bought_price'   => $_POST['bkd_total_bought_price'][$i]   ?? 0,
			'supplier_discount'    => $_POST['bkd_supplier_discount'][$i]    ?? 0,
			'supplier_ticketing_fee' => $_POST['bkd_supplier_ticketing_fee'][$i] ?? 0,
			'supplier_id'          => $_POST['bkd_supplier_id'][$i]          ?? '',
			'direction'            => $_POST['bkd_direction'][$i]            ?? '',
			'is_active'            => $_POST['bkd_is_active'][$i]            ?? 0,
			'vat_admin'            => $_POST['bkd_vat_admin'][$i]            ?? 0,
			'admin_fee_no_vat'     => $_POST['bkd_admin_fee_no_vat'][$i]     ?? 0,
			'deleted'              => (int) ($_POST['bkd_deleted'][$i]       ?? 0),
			'is_edit_detail'       => isset($_POST['edit_detail']),
		];
	}

	/**
	 * Save data detail and return total bought price
	 * @return float total_bought_price của row này
	 */
	private function _saveDetailRow(array $data, array $app_list_strings): float
	{
		/** @var EC_Booking_Details **/
		$bkd = BeanFactory::newBean('EC_Booking_Details');

		if (!empty($data['id'])) {
			$bkd->retrieve($data['id']);
		}

		// Xóa nếu được đánh dấu — chỉ khi có ID hợp lệ
		if ((int) $data['deleted'] === 1) {
			if (!empty($bkd->id)) {
				$bkd->mark_deleted($bkd->id);
			}
			return 0.0;
		}

		// Map fields
		$quantity = unformat_number($data['quantity']);
		$totalPrice = unformat_number($data['total_price']);
		$serviceFee = unformat_number($data['service_fee']);
		$discount = unformat_number($data['supplier_discount']);
		$ticketingFee = unformat_number($data['supplier_ticketing_fee']);

		$bkd->passenger_type = $data['passenger_type'];
		$bkd->name = $app_list_strings['passenger_type_list'][$data['passenger_type']] ?? $data['passenger_type'];
		$bkd->quantity = $quantity;
		$bkd->unit_price = unformat_number($data['unit_price']);
		$bkd->tax_and_fee = unformat_number($data['tax_and_fee']);
		$bkd->airport_fee = unformat_number($data['airport_fee']);
		$bkd->admin_fee = unformat_number($data['admin_fee']);
		$bkd->service_fee = $serviceFee;
		$bkd->total_price = $totalPrice;
		$bkd->supplier_discount = $discount;
		$bkd->fee_bought = $ticketingFee;
		$bkd->supplier_id = $data['supplier_id'];
		$bkd->booking_id = $this->id;
		$bkd->direction = $data['direction'];
		$bkd->is_active = (int) $data['is_active'];
		$bkd->vat_admin = (int) $data['vat_admin'];
		$bkd->admin_fee_no_vat = (int) $data['admin_fee_no_vat'];

		// Công thức: Giá mua = Thành tiền - (Phí DV × SL) - Chiết khấu + Phí xuất vé
		$bkd->total_bought_price = $data['is_edit_detail'] ? $totalPrice - ($serviceFee * $quantity) - $discount + $ticketingFee : unformat_number($data['total_bought_price']);

		// Guard: chỉ save khi dữ liệu hợp lệ
		if ($quantity > 0 && $totalPrice > 0) {
			$bkd->save();
		}

		return (float) $bkd->total_bought_price;
	}

	private function _updateTotalBoughtAmount(float $amount)
	{
		$sql = sprintf(
			'UPDATE ec_flight_bookings SET total_bought_amount = %f WHERE id = %s',
			$amount,
			$this->db->quoted($this->id)
		);
		$this->db->query($sql);
	}

	/**
	 * Save passengers information
	 * ──────────────────────────────────────────────────────────────────
	 * ──────────────────────────────────────────────────────────────────
	 */
	private function saveLinePassengers()
	{
		global $current_user;

		$rows = $_POST['psg_id'] ?? [];

		foreach (array_keys($rows) as $i) {
			$data = $this->_extractPassengerRow($i);
			$this->_savePassengerRow($data);
		}

		// Khi booking ở trạng thái Xác nhận, kiểm tra đủ số vé mới chuyển sang trạng thái Xuất vé
		if ((int) $this->booking_status === 3) {
			$this->_updateTicketExportStatus($rows);
		}
	}

	private function _extractPassengerRow(int $i): array
	{
		return [
			'id' => $_POST['psg_id'][$i] ?? '',
			'traveller_type' => $_POST['psg_traveller_type'][$i] ?? '',
			'salutation' => $_POST['psg_salutation'][$i] ?? '',
			'full_name' => $_POST['psg_full_name'][$i] ?? '',
			'birthday' => $_POST['psg_birthday'][$i] ?? '',
			'pnr_outbound' => $_POST['psg_pnr_outbound'][$i] ?? '',
			'pnr_inbound' => $_POST['psg_pnr_inbound'][$i] ?? '',
			'eticket_outbound' => $_POST['psg_eticket_outbound'][$i] ?? '',
			'eticket_inbound' => $_POST['psg_eticket_inbound'][$i] ?? '',
			'add_type' => $_POST['psg_add_type'][$i] ?? '',
			'parent_detail_id' => $_POST['psg_parent_detail_id'][$i] ?? null,
			'deleted' => (int) ($_POST['psg_deleted'][$i] ?? 0),
			// Baggage
			'luggage_purchase_text' => $_POST['psg_luggage_purchase_text'][$i] ?? '',
			'luggage_purchase_text_inbound' => $_POST['psg_luggage_purchase_text_inbound'][$i] ?? '',
			'luggage_purchase' => $_POST['psg_luggage_purchase'][$i] ?? 0,
			'luggage_purchase_inbound' => $_POST['psg_luggage_purchase_inbound'][$i] ?? 0,
			'vat_luggage_purchase' => $_POST['psg_vat_luggage_purchase'][$i] ?? 0,
			'vat_luggage_purchase_inbound' => $_POST['psg_vat_luggage_purchase_inbound'][$i] ?? 0,
			'eluggage_outbound' => $_POST['psg_eluggage_outbound'][$i] ?? '',
			'eluggage_inbound' => $_POST['psg_eluggage_inbound'][$i] ?? '',
			'luggage_supplier' => $_POST['psg_luggage_supplier'][$i] ?? '',
			'luggage_supplier_inbound' => $_POST['psg_luggage_supplier_inbound'][$i] ?? '',
			'luggage_price' => $_POST['psg_luggage_price'][$i] ?? 0,
			'luggage_price_inbound' => $_POST['psg_luggage_price_inbound'][$i] ?? 0,
			'luggage_index_outbound' => $_POST['psg_luggage_index_outbound'][$i] ?? '',
			'luggage_index_inbound' => $_POST['psg_luggage_index_inbound'][$i] ?? '',
			'hand_baggage_outbound' => $_POST['psg_hand_baggage_outbound'][$i] ?? '',
			'hand_baggage_inbound' => $_POST['psg_hand_baggage_inbound'][$i] ?? '',
		];
	}

	private function _savePassengerRow(array $data)
	{
		/** @var EC_Booking_Passengers **/
		$psg = BeanFactory::newBean('EC_Booking_Passengers');

		if (!empty($data['id'])) {
			$psg->retrieve($data['id']);
		}

		if ((int) $data['deleted'] === 1) {
			if (!empty($psg->id)) {
				$psg->mark_deleted($psg->id);
			}
			return;
		}

		// Baggage costs — ép float trước khi tính
		$luggagePurchase = (float) unformat_number($data['luggage_purchase']);
		$luggagePurchaseInbound = (float) unformat_number($data['luggage_purchase_inbound']);
		$vatLuggage = (float) unformat_number($data['vat_luggage_purchase']);
		$vatLuggageInbound = (float) unformat_number($data['vat_luggage_purchase_inbound']);

		$psg->booking_id = $this->id;
		$psg->type = $data['traveller_type'];
		$psg->salutation = $data['salutation'];
		$psg->name = $this->_normalizeUpper($data['full_name']);
		$psg->birthday = $this->_normalizeBirthday($data['birthday']);
		$psg->pnr_outbound = trim(stripslashes($data['pnr_outbound']));
		$psg->pnr_inbound = trim(stripslashes($data['pnr_inbound']));
		$psg->eticket_outbound = trim(stripslashes($data['eticket_outbound']));
		$psg->eticket_inbound = trim(stripslashes($data['eticket_inbound']));
		$psg->add_type = $data['add_type'];
		$psg->parent_detail_id = $data['parent_detail_id'];
		$psg->luggage_purchase_text = trim($data['luggage_purchase_text']);
		$psg->luggage_purchase_text_inbound = trim($data['luggage_purchase_text_inbound']);
		$psg->luggage_purchase = $luggagePurchase;
		$psg->luggage_purchase_inbound = $luggagePurchaseInbound;
		$psg->vat_luggage_purchase = $vatLuggage;
		$psg->vat_luggage_purchase_inbound = $vatLuggageInbound;
		$psg->luggage_purchase_no_vat = $luggagePurchase - $vatLuggage;
		$psg->luggage_purchase_inbound_no_vat = $luggagePurchaseInbound - $vatLuggageInbound;
		$psg->eluggage_outbound = trim(stripslashes($data['eluggage_outbound']));
		$psg->eluggage_inbound = trim(stripslashes($data['eluggage_inbound']));
		$psg->supplier_id = $data['luggage_supplier'];
		$psg->supplier_inbound_id = $data['luggage_supplier_inbound'];
		$psg->luggage_price = (float) unformat_number($data['luggage_price']);
		$psg->luggage_price_inbound = (float) unformat_number($data['luggage_price_inbound']);
		$psg->luggage_index_outbound = trim($data['luggage_index_outbound']);
		$psg->luggage_index_inbound = trim($data['luggage_index_inbound']);
		$psg->hand_baggage_outbound = trim($data['hand_baggage_outbound']);
		$psg->hand_baggage_inbound = trim($data['hand_baggage_inbound']);

		if (!empty($psg->name)) {
			$psg->save();
		}
	}

	/**
	 * Normalize birthday
	 */
	private function _normalizeBirthday(string $raw): string
	{
		$normalized = str_replace('/', '-', trim($raw));
		$timestamp = strtotime($normalized);
		return $timestamp !== false ? date('Y-m-d', $timestamp) : '';
	}

	/**
	 * Tách biệt hoàn toàn khỏi saveLinePassengers().
	 * Chỉ xét passengers chưa bị deleted khi kiểm tra eticket.
	 */
	private function _updateTicketExportStatus(array $psgIds): void
	{
		global $current_user;

		$booking = new EC_Flight_Bookings();
		$booking->retrieve($this->id);

		$isAllowedUser_acc = is_admin($current_user);
		$isRoundTrip = (int) $booking->flight_type === 0;

		$outboundExported = false;
		$outboundFull = true;
		$inboundExported = false;
		$inboundFull = true;

		foreach (array_keys($psgIds) as $i) {
			if ((int) ($_POST['psg_deleted'][$i] ?? 0) === 1)
				continue;

			$eticketOut = $_POST['psg_eticket_outbound'][$i] ?? '';
			if (!empty($eticketOut))
				$outboundExported = true;
			else
				$outboundFull = false;

			if ($isRoundTrip) {
				$eticketIn = $_POST['psg_eticket_inbound'][$i] ?? '';
				if (!empty($eticketIn))
					$inboundExported = true;
				else
					$inboundFull = false;
			}
		}

		if (!$isRoundTrip) {
			$inboundExported = false;
			$inboundFull = false;
		}

		$booking->is_ticket_exported = $outboundExported;
		if ($outboundExported) {
			if (empty($booking->date_ticket_issue)) {
				$booking->date_ticket_issue = date('Y-m-d');
			}
			if ($isAllowedUser_acc && !empty($_POST['date_ticket_issue'])) {
				$booking->date_ticket_issue = $_POST['date_ticket_issue'];
			}
		} else {
			$booking->date_ticket_issue = '';
		}

		$booking->is_ticket_inbound_exported = $inboundExported;
		if ($inboundExported) {
			if (empty($booking->date_ticket_inbound_issue)) {
				$booking->date_ticket_inbound_issue = date('Y-m-d');
			}
			if ($isAllowedUser_acc && !empty($_POST['date_ticket_inbound_issue'])) {
				$booking->date_ticket_inbound_issue = $_POST['date_ticket_inbound_issue'];
			}
		} else {
			$booking->date_ticket_inbound_issue = '';
		}

		$isFullyExported = $isRoundTrip ? ($outboundFull && $inboundFull) : $outboundFull;
		if ($isFullyExported) {
			$booking->booking_status = 7;
		}

		$booking->save2();
	}

	/**
	 * Save when changing flight info 
	 * such as: flight date, itinerary, passengers, baggages, ticket code, PNR
	 * ──────────────────────────────────────────────────────────────────
	 * ──────────────────────────────────────────────────────────────────
	 */
	private function saveChangeFlightTime()
	{
		global $sugar_config;

		$this->_validateBookingOwnership();

		$vat_rate = $sugar_config['flight_config']['vat_percentage'] ?? 0.08;
		$passReplace = $this->_processPassengerChanges($vat_rate);

		// Thay đổi thông tin hành khách
		$this->_syncAppliedPassengers($passReplace);

		// Thay đổi thông tin hành trình
		$this->_processItineraryChanges($passReplace);
	}

	/**
	 * Đảm bảo $_POST['booking_id'] khớp với $this->id
	 * tránh thao tác lên booking của người khác
	 */
	private function _validateBookingOwnership()
	{
		if (!empty($_POST['booking_id']) && $_POST['booking_id'] !== $this->id) {
			throw new RuntimeException('Booking ID mismatch — possible tampering.');
		}
	}

	/**
	 * Xử lý thay đổi thông tin hành khách.
	 * @return array $passReplace [old_id => new_id] — dùng cho mapping hành trình
	 */
	private function _processPassengerChanges(float $vat_rate): array
	{
		$passIds = $_POST['pass_id'] ?? [];
		$passReplace = [];

		// Lấy STT thay đổi hành khách mới nhất
		$passOrder = (int) $this->db->getOne(sprintf(
			'SELECT MAX(IFNULL(go_with, 0)) FROM ec_booking_passengers WHERE booking_id = %s AND deleted = 0',
			$this->db->quoted($this->id)
		));

		foreach (array_keys($passIds) as $i) {
			$pass = BeanFactory::getBean('EC_Booking_Passengers', $passIds[$i]);
			if (!$pass)
				continue;

			if ($this->_passengerHasChanges($pass, $i) && !isset($_POST['edit_pass_id'])) {
				// Tạo bản ghi mới — lưu lịch sử thay đổi
				$newId = $this->_createNewPassenger($pass, $i, $vat_rate, $passOrder + 1);
				if ($newId) {
					$passReplace[$pass->id] = $newId;
				}
			} else {
				// Cập nhật trực tiếp
				$this->_updateExistingPassenger($pass, $i, $vat_rate);
			}
		}

		return $passReplace;
	}

	private function _passengerHasChanges(EC_Booking_Passengers $pass, int $i): bool
	{
		$checks = [
			['pass_name', $pass->name],
			['pass_pnr_outbound', $pass->pnr_outbound],
			['pass_pnr_inbound', $pass->pnr_inbound],
			['pass_eticket_outbound', $pass->eticket_outbound],
			['pass_eticket_inbound', $pass->eticket_inbound],
		];

		foreach ($checks as [$field, $current]) {
			if (isset($_POST[$field]) && $_POST[$field][$i] != $current)
				return true;
		}

		// Birthday dùng key động — xử lý riêng
		if (isset($_POST["pass_birthday{$i}"]) && $pass->birthday != $_POST["pass_birthday{$i}"]) {
			return true;
		}

		// Luggage
		$luggageObChanged = !isset($_POST['pass_luggage_ob_ind']) && ($_POST['pass_luggage_ob'][$i] ?? null) != $pass->luggage_price;
		$luggageObIndChanged = isset($_POST['pass_luggage_ob_ind']) && ($_POST['pass_luggage_ob_ind'][$i] ?? null) != $pass->luggage_index_outbound;
		$luggageIbChanged = !isset($_POST['pass_luggage_ib_ind']) && ($_POST['pass_luggage_ib'][$i] ?? null) != $pass->luggage_price_inbound;
		$luggageIbIndChanged = isset($_POST['pass_luggage_ib_ind']) && ($_POST['pass_luggage_ib_ind'][$i] ?? null) != $pass->luggage_index_inbound;

		return $luggageObChanged || $luggageObIndChanged || $luggageIbChanged || $luggageIbIndChanged;
	}

	private function _createNewPassenger(EC_Booking_Passengers $original, int $i, float $vat_rate, int $goWith): ?string
	{
		$name = $_POST['pass_name'][$i] ?? '';
		if (empty($name))
			return null;

		/**
		 * @var EC_Booking_Passengers $pass 
		 */
		$pass = BeanFactory::newBean('EC_Booking_Passengers');
		$pass->name = $name;
		$pass->salutation = $_POST['pass_salutation'][$i] ?? '';
		$pass->birthday = $_POST["pass_birthday{$i}"] ?? '';
		$pass->type = $original->type;
		$pass->booking_id = $original->booking_id;
		$pass->pnr_outbound = $_POST['pass_pnr_outbound'][$i] ?? '';
		$pass->pnr_inbound = $_POST['pass_pnr_inbound'][$i] ?? '';
		$pass->eticket_outbound = $_POST['pass_eticket_outbound'][$i] ?? '';
		$pass->eticket_inbound = $_POST['pass_eticket_inbound'][$i] ?? '';
		$pass->eluggage_outbound = $_POST['pass_eluggage_outbound'][$i] ?? '';
		$pass->eluggage_inbound = $_POST['pass_eluggage_inbound'][$i] ?? '';
		$pass->direction = $original->direction;
		$pass->add_type = 2;
		$pass->parent_detail_id = $original->id;
		$pass->go_with = $goWith;
		$pass->passport_number = trim($original->passport_number ?? '');

		$this->_applyLuggageOutbound($pass, $i, $vat_rate, $original);
		$this->_applyLuggageInbound($pass, $i, $vat_rate, $original);

		$pass->save();
		return $pass->id;
	}

	private function _updateExistingPassenger(EC_Booking_Passengers $pass, int $i, float $vat_rate)
	{
		$pass->birthday = $_POST["pass_birthday{$i}"] ?? '';
		$pass->salutation = $_POST['pass_salutation'][$i] ?? '';
		$pass->name = $_POST['pass_name'][$i] ?? '';
		$pass->pnr_outbound = $_POST['pass_pnr_outbound'][$i] ?? '';
		$pass->pnr_inbound = $_POST['pass_pnr_inbound'][$i] ?? '';
		$pass->eticket_outbound = $_POST['pass_eticket_outbound'][$i] ?? '';
		$pass->eticket_inbound = $_POST['pass_eticket_inbound'][$i] ?? '';
		$pass->eluggage_outbound = $_POST['pass_eluggage_outbound'][$i] ?? '';
		$pass->eluggage_inbound = $_POST['pass_eluggage_inbound'][$i] ?? '';

		$this->_applyLuggageOutbound($pass, $i, $vat_rate);
		$this->_applyLuggageInbound($pass, $i, $vat_rate);

		$pass->save();
	}

	/**
	 * Extract và tách riêng 2 luggage helpers để tránh lặp code
	 */
	private function _applyLuggageOutbound(EC_Booking_Passengers $pass, int $i, float $vat_rate, ?EC_Booking_Passengers $original = null)
	{
		if (!isset($_POST['pass_luggage_ob'][$i]))
			return;

		if ($original)
			$pass->luggage_index_outbound = $original->luggage_index_outbound;
		$purchase = (float) unformat_number($_POST['pass_luggage_purchase'][$i] ?? 0);
		$pass->luggage_purchase_text = trim($_POST['pass_luggage_ob'][$i]);
		$pass->luggage_price = (float) unformat_number($_POST['pass_luggage_price'][$i] ?? 0);
		$pass->luggage_purchase = $purchase;
		$pass->vat_luggage_purchase = $purchase > 0 ? $purchase * $vat_rate : 0;
		$pass->luggage_purchase_no_vat = $purchase > 0 ? $purchase - $pass->vat_luggage_purchase : 0;
		$pass->supplier_id = trim($_POST['supplier_outbound'][$i] ?? '');
	}

	private function _applyLuggageInbound(EC_Booking_Passengers $pass, int $i, float $vat_rate, ?EC_Booking_Passengers $original = null)
	{
		if (!isset($_POST['pass_luggage_ib'][$i]))
			return;

		if ($original)
			$pass->luggage_index_inbound = $original->luggage_index_inbound;
		$purchase = (float) unformat_number($_POST['pass_luggage_purchase_inbound'][$i] ?? 0);
		$pass->luggage_purchase_text_inbound = trim($_POST['pass_luggage_ib'][$i]);
		$pass->luggage_price_inbound = (float) unformat_number($_POST['pass_luggage_price_inbound'][$i] ?? 0);
		$pass->luggage_purchase_inbound = $purchase;
		$pass->vat_luggage_purchase_inbound = $purchase > 0 ? $purchase * $vat_rate : 0;
		$pass->luggage_purchase_inbound_no_vat = $purchase > 0 ? $purchase - $pass->vat_luggage_purchase_inbound : 0;
		$pass->supplier_inbound_id = trim($_POST['supplier_inbound'][$i] ?? '');
	}

	/**
	 * Cập nhật applied_passenger: thay old_id bằng new_id sau khi tạo mới
	 */
	private function _syncAppliedPassengers(array $passReplace)
	{
		if (empty($_POST['applied_passenger']) || empty($passReplace))
			return;

		foreach ($passReplace as $oldId => $newId) {
			$idx = array_search($oldId, $_POST['applied_passenger']);
			if ($idx !== false) {
				$_POST['applied_passenger'][$idx] = $newId;
			}
		}
	}

	private function _processItineraryChanges(array $passReplace)
	{
		$itiOrder = (int) $this->db->getOne(sprintf(
			'SELECT MAX(IFNULL(sabre_logs, 0)) FROM ec_booking_itineraries WHERE deleted = 0 AND booking_id = %s',
			$this->db->quoted($this->id)
		));

		if (!isset($_POST['iti_id']) || empty($_POST['iti_id'])) {
			// Lưu mới hành trình cho cả lượt đi (0) và lượt về (1)
			foreach ([0, 1] as $direction) {
				if ($this->_hasValidItineraryData($direction)) {
					$this->_saveItineraryForAllPassengers($direction, $passReplace, $itiOrder);
				} else {
					$this->_reassignItineraryPassengers($direction, $passReplace);
				}
			}
		} else {
			// Sửa lại hành trình đã có
			$this->_updateExistingItineraries();
		}
	}

	/**
	 * Validate đủ field cho 1 direction 
	 */
	private function _hasValidItineraryData(int $direction): bool
	{
		$d = $direction;

		$requiredFields = [
			"flight_number{$d}",
			"ticket_class{$d}",
			"departure{$d}",
			"arrival{$d}",
			"departure_date{$d}",
			"arrival_date{$d}"
		];
		foreach ($requiredFields as $field) {
			if (empty($_POST[$field]))
				return false;
		}

		$depHour = (int) ($_POST["departure_hour{$d}"] ?? -1);
		$depMin = (int) ($_POST["departure_minute{$d}"] ?? -1);
		$arrHour = (int) ($_POST["arrival_hour{$d}"] ?? -1);
		$arrMin = (int) ($_POST["arrival_minute{$d}"] ?? -1);

		$validTime = $depHour >= 0 && $depHour < 24
			&& $depMin >= 0 && $depMin < 60
			&& $arrHour >= 0 && $arrHour < 24
			&& $arrMin >= 0 && $arrMin < 60;

		$hasPassengers = ($_POST['applied_all'] ?? '') === 'on' || count($_POST['applied_passenger'] ?? []) > 0;

		return $validTime && $hasPassengers;
	}

	private function _saveItineraryForAllPassengers(int $direction, array $passReplace, int $itiOrder)
	{
		$passIds = $_POST['pass_id'] ?? [];

		foreach (array_keys($passIds) as $p) {
			$passId = $passReplace[$passIds[$p]] ?? $passIds[$p];

			$this->saveFlightItinerary(
				$_POST['pass_name'][$p] ?? '',
				$direction,
				$_POST,
				$passId,
				$itiOrder
			);
		}
	}

	/**
	 * Không có thay đổi hành trình nhưng có đổi hành khách
	 * → Clone itinerary mới nhất với assigned_user_id mới
	 * Fix: dùng $this->db->quoted() thay vì nối chuỗi
	 * Fix: kiểm tra strtotime() trước khi adjust timezone
	 */
	private function _reassignItineraryPassengers(int $direction, array $passReplace)
	{
		foreach ($passReplace as $oldPassId => $newPassId) {
			$row = $this->db->fetchByAssoc($this->db->query(sprintf(
				'SELECT id FROM ec_booking_itineraries WHERE deleted = 0 AND add_type = 3 AND assigned_user_id = %s AND direction = %d ORDER BY date_entered DESC LIMIT 1',
				$this->db->quoted($oldPassId),
				$direction
			)));

			if (empty($row['id']))
				continue;

			// Tạo bản ghi mới cho hành khách mới
			/**
			 * @var EC_Booking_Itineraries
			 */
			$newIti = BeanFactory::getBean('EC_Booking_Itineraries', $row['id']);
			$newIti->id = '';
			$newIti->date_entered = null;
			$newIti->departure_date = $this->_adjustTimezone($newIti->departure_date);
			$newIti->arrival_date = $this->_adjustTimezone($newIti->arrival_date);
			$newIti->assigned_user_id = $newPassId;
			$newIti->save();

			// Đánh dấu bản ghi cũ là đã thay thế (add_type = 1)
			/**
			 * @var EC_Booking_Itineraries
			 */
			$oldIti = BeanFactory::getBean('EC_Booking_Itineraries', $row['id']);
			$oldIti->departure_date = $this->_adjustTimezone($oldIti->departure_date);
			$oldIti->arrival_date = $this->_adjustTimezone($oldIti->arrival_date);
			$oldIti->add_type = 1;
			$oldIti->save();
		}
	}

	/**
	 * Adjust timezone -7h với guard cho strtotime() trả về false
	 * TODO: Thay bằng DateTimeZone nếu cần xử lý DST
	 */
	private function _adjustTimezone(string $datetime): string
	{
		$ts = strtotime($datetime);
		if ($ts === false)
			return $datetime; // Guard: không làm sai dữ liệu
		return date('Y-m-d H:i:s', strtotime('-7 hours', $ts));
	}

	private function _updateExistingItineraries()
	{
		$passArr = explode(',', $_POST['applied_pass'] ?? '');
		$passNameArr = explode(',', $_POST['applied_pass_name'] ?? '');
		$itiIdArr = explode(',', $_POST['iti_id'] ?? '');

		foreach (array_keys($passArr) as $t) {
			$_POST['iti_id'] = $itiIdArr[$t] ?? '';
			$this->saveFlightItinerary(
				$passNameArr[$t] ?? '',
				'',
				$_POST,
				$passArr[$t] ?? '',
				''
			);
		}
	}

	/**
	 * Lấy danh sách hành khách hiện tại của booking.
	 * Loại trừ các hành khách đã được thay thế bởi bản ghi mới hơn (add_type = 2).
	 *
	 * @param string $booking_id
	 * @return array<array{id: string, name: string}>
	 */
	public function getAllPassengers(string $booking_id): array
	{
		if (empty($booking_id))
			return [];

		$sql = 'SELECT id, name FROM ec_booking_passengers 
				WHERE booking_id = "' . $this->db->quoted($booking_id) . '" AND deleted = 0
					AND id NOT IN (
						SELECT parent_detail_id
						FROM ec_booking_passengers
						WHERE booking_id = "' . $this->db->quoted($booking_id) . '" AND add_type = 2 AND deleted = 0
					)';
		$res = $this->db->query($sql);
		if (!$res)
			return [];

		$passengers = [];
		while ($row = $this->db->fetchByAssoc($res)) {
			$passengers[] = $row;
		}
		return $passengers;
	}

	/**
	 * Lưu hoặc cập nhật 1 itinerary cho 1 hành khách cụ thể.
	 *
	 * @param string     $pass_name   Tên hành khách
	 * @param int|string $direction   0 = lượt đi, 1 = lượt về, '' = không phân biệt (khi edit)
	 * @param array      $post_fields Dữ liệu POST đã được extract từ Controller
	 * @param string     $pass_id     ID hành khách
	 * @param int        $iti_order   Số thứ tự thay đổi hành trình
	 */
	public function saveFlightItinerary($pass_name, $direction, $post_fields, $pass_id, $iti_order)
	{
		/**
		 * @var EC_Booking_Itineraries $iti
		 */
		$iti = BeanFactory::newBean('EC_Booking_Itineraries');

		if (!empty($post_fields['iti_id'])) {
			$iti->retrieve($post_fields['iti_id']);
		} else {
			$iti->direction = $direction;
			$iti->add_type = 3;
			$iti->sabre_logs = (int) $iti_order + 1;
			$iti->assigned_user_id = $pass_id;
			$iti->name = $pass_name;
		}

		// Không ghi đè hãng bằng giá trị rỗng (đổi hành trình KHÔNG đổi hãng) —
		// tránh làm mất mã hãng của dòng vé sau khi đổi.
		$resolvedAirline = $this->_resolveAirlineCode($post_fields, $direction);
		if ($resolvedAirline !== '') {
			$iti->airline_code = $resolvedAirline;
		}
		$iti->flight_number = $post_fields['flight_number' . $direction] ?? '';
		$iti->ticket_class = $post_fields['ticket_class' . $direction] ?? '';
		$iti->departure = $post_fields['departure' . $direction] ?? '';
		$iti->arrival = $post_fields['arrival' . $direction] ?? '';
		$iti->departure_date = $this->_buildDatetime(
			$post_fields['departure_date' . $direction] ?? '',
			$post_fields['departure_hour' . $direction] ?? '00',
			$post_fields['departure_minute' . $direction] ?? '00'
		);
		$iti->arrival_date = $this->_buildDatetime(
			$post_fields['arrival_date' . $direction] ?? '',
			$post_fields['arrival_hour' . $direction] ?? '00',
			$post_fields['arrival_minute' . $direction] ?? '00'
		);
		$iti->booking_id = $post_fields['booking_id'] ?? $this->id;
		$iti->save();
	}

	/**
	 * Resolve airline code theo thứ tự ưu tiên:
	 * 1. bk_airline{direction} — field riêng cho VNA/VNP
	 * 2. airline_code_inbound / airline_code — hidden theo chiều (lấy từ booking-level)
	 * 3. Fallback: kế thừa mã hãng từ itinerary gốc của booking cùng chiều
	 *    (đổi hành trình KHÔNG đổi hãng — tránh mất mã hãng khi booking-level airline rỗng)
	 */
	private function _resolveAirlineCode(array $post_fields, $direction): string
	{
		if (!empty($post_fields['bk_airline' . $direction])) {
			return $post_fields['bk_airline' . $direction];
		}

		$code = ($direction == 1)
			? ($post_fields['airline_code_inbound'] ?? '')
			: ($post_fields['airline_code'] ?? '');
		if (trim((string) $code) !== '') {
			return $code;
		}

		// Fallback: hãng vốn nằm sẵn trên itinerary gốc của booking (chỉ áp dụng cho chiều 0/1)
		if (in_array((string) $direction, ['0', '1'], true)) {
			$booking_id = $post_fields['booking_id'] ?? $this->id;
			if (!empty($booking_id)) {
				$existing = $this->db->getOne(sprintf(
					'SELECT airline_code FROM ec_booking_itineraries
					 WHERE deleted = 0 AND booking_id = %s AND direction = %s
					 AND IFNULL(airline_code, "") <> ""
					 ORDER BY CAST(IFNULL(sabre_logs, 0) AS UNSIGNED) ASC LIMIT 1',
					$this->db->quoted($booking_id),
					$this->db->quoted((string) $direction)
				));
				if (!empty($existing)) {
					return (string) $existing;
				}
			}
		}

		return (string) $code;
	}

	// Danh sách ngân hàng cho ô "Ngân hàng" của thông tin hoá đơn
	public static function getInvoiceBankList()
	{
		return [
			'' => 'Chọn ngân hàng',
			'VPBank' => '(VPBank) NH TMCP Việt Nam Thịnh Vượng',
			'BIDV' => '(BIDV) NH TMCP Đầu tư và Phát triển Việt Nam',
			'VietinBank' => '(VietinBank) NH TMCP Công thương Việt Nam',
			'Vietcombank' => '(Vietcombank) NH TMCP Ngoại Thương Việt Nam',
			'MB' => '(MB) NH TMCP Quân Đội',
			'Techcombank' => '(Techcombank) NH TMCP Kỹ Thương',
			'Agribank' => '(Agribank) NH PT Nông thôn Việt Nam',
			'ACB' => '(ACB) NH TMCP Á Châu',
			'SHB' => '(SHB) NH TMCP Sài Gòn – Hà Nội',
			'VIB' => '(VIB) NH TMCP Quốc Tế',
			'HDBank' => '(HDBank) NH TMCP Phát triển TPHCM',
			'SeABank' => '(SeABank) NH TMCP Đông Nam Á',
			'VBSP' => '(VBSP) NH Chính sách xã hội Việt Nam',
			'Sacombank' => '(Sacombank) NH TMCP Sài Gòn Thương Tín',
			'LienVietPostBank' => '(LienVietPostBank) NH TMCP Bưu điện Liên Việt',
			'MSB' => '(MSB) NH TMCP Hàng Hải',
			'SCB' => '(SCB) NH TMCP Sài Gòn',
			'VDB' => '(VDB) NH Phát triển Việt Nam',
			'OCB' => '(OCB) NH TMCP Phương Đông',
			'Eximbank' => '(Eximbank) NH TMCP Xuất Nhập Khẩu',
			'TPBank' => '(TPBank) NH TMCP Tiên Phong',
			'PVcomBank' => '(PVcomBank)  NH TMCP Đại Chúng Việt Nam',
			'BacABank' => '(BacABank) NH TMCP Bắc Á',
			'Woori' => '(Woori) NH TNHH MTV Woori Việt Nam',
			'HSBC' => '(HSBC) NH TNHH MTV HSBC Việt Nam',
			'VietABank' => '(Vietbank) NH TMCP Việt Nam Thương Tín',
			'NamABank' => '(Nam A Bank) NH TMCP Nam Á',
			'IVB' => '(IVB) NH TNHH Indovina',
			'Kienlongbank' => '(Kienlongbank) NH TMCP Kiên Long',
		];
	}

	// Lưu thông tin hoá đơn
	public function saveInvoiceInf($post_fields, $booking_id)
	{
		if (!isset($post_fields['action']) || $post_fields['action'] !== 'Save' || !isset($post_fields['iv_account_name'])) {
			return;
		}

		$invoice_inf = [
			'iv_account_name' => $post_fields['iv_account_name'] ?? '',
			'iv_email' => $post_fields['iv_email'] ?? '',
			'iv_identity_number' => $post_fields['iv_identity_number'] ?? '',
			'iv_payment_method' => $post_fields['iv_payment_method'] ?? '',
			'iv_bank_account' => $post_fields['iv_bank_account'] ?? '',
			'iv_name_banks' => $post_fields['iv_name_banks'] ?? '',
		];

		$json = json_encode($invoice_inf, JSON_UNESCAPED_UNICODE);
		$sql = "UPDATE ec_flight_bookings SET shipping_address = '" . $this->db->quote($json) . "' WHERE id = '" . $this->db->quote($booking_id) . "' AND deleted = 0";

		$result = $this->db->query($sql);

		if (!$result) {
			$GLOBALS['log']->fatal(
				"Failed to update shipping_address for booking {$booking_id}: " . $this->db->lastError() . " | SQL: $sql"
			);
		}

		return true;
	}

	// Tính số lượng vé của 1 booking
	// Tổng sl vé trong booking - sl vé hoàn nếu có
	public function calculateBookingTicketQty($booking_id)
	{
		$sql = "SELECT  
				total_qty
				- IFNULL((
					SELECT COUNT(ct.id) 
					FROM ec_chitiethoanve ct
					INNER JOIN ec_hoanve hv 
					ON ct.hoanve_id = hv.id 
					AND hv.deleted = 0
					AND hv.booking_id = '$booking_id'
					WHERE ct.deleted = 0
				), 0)
			FROM ec_flight_bookings
			WHERE id = '$booking_id'";
		return (int) $this->db->getOne($sql);
	}

	/**
	 * Check is use new baggage
	 * 
	 * @param string $date_entered
	 * @param string $created_by
	 * @return bool
	 */
	public function isUseNewBaggage($date_entered, $created_by)
	{
		if (is_null($date_entered) || is_null($created_by) || empty($date_entered) || empty($created_by)) return true;
		global $sugar_config, $current_user;
		$dateFormat = $current_user->getPreference('datef') ?? $sugar_config['datef'] ?? 'd-m-Y';
		$timeFormat = $current_user->getPreference('timef') ?? $sugar_config['timef'] ?? 'H:i';

		if (strtotime($date_entered) > strtotime("$dateFormat $timeFormat"))
			return true;
		return false;
	}

	/**
	 * Get baggage info by baggage data
	 * 
	 * @param array $bagData
	 * @param string $language
	 * @return array [available, purchase]
	 */
	public function getBaggageInfoByData($bagData, $language = 'vi')
	{
		try {
			$airlineCode = $bagData['airlineCode'] ?? ''; // Using for get available baggage info in old data
			$ticketClass = $bagData['ticketClass'] ?? ''; // Using for get available baggage info in old data
			$passType = $bagData['passType'] ?? '0'; // Using for get available baggage info in old data
			$dateEntered = $bagData['dateEntered'] ?? $this->date_entered; // Using for get available baggage info in old data
			$createdBy = $this->created_by ?? $bagData['createdBy'] ?? ''; // Using for get available baggage info in old data
			$bagIndex = $bagData['bagIndex'] ?? ''; // Using for get available baggage info in new data or in old data with Vietjet
			$bagPurchaseText = $bagData['bagPurchaseText'] ?? ''; // Using for get purchase baggage info

			$result = ['available' => '', 'purchase' => ''];

			if ($this->isUseNewBaggage($dateEntered, $createdBy)) {
				$result['available'] = Baggage::renderAvailableBaggage($bagIndex, $language);
			} else {
				$bags = generateLuggage($dateEntered, $airlineCode, $ticketClass, $passType, $bagIndex); // Array
				if ($bags && !empty($bags)) {
					$bagString = is_numeric($bagIndex) ? $bags[(int) $bagIndex] : $bags[0]; // String

					$bagWeight = 0;
					if (is_string($bagString) && !empty($bagString)) {
						preg_match('/(\d+)kg/isU', $bagString, $output);
						$bagWeight = isset($output[1]) ? (int) $output[1] : 0;
					}

					if ($bagWeight > 0) {
						if ($language == 'en')
							$result['available'] = $bagIndex > 1000 ? "Extra {$bagWeight}kg" : "{$bagWeight}kg available";
						else
							$result['available'] = substr_replace($bagString, '', strpos($bagString, '(') - 1);
					}
				}
			}

			if (!empty($bagPurchaseText)) {
				$result['purchase'] = preg_replace('/\s*\([^)]*\)/', '', $bagPurchaseText);
			}

			return $result;
		} catch (Throwable $th) {
			return ['available' => '', 'purchase' => ''];
		}
	}

	/**
	 * Generate passenger baggage info (Use in show detail booking)
	 * 
	 * @param array $passInfo Information of a specific passenger
	 * @param int $orderNumber
	 * 
	 * @return string HTML
	 */
	public function generatePassengerBaggageInfo($passInfo, $orderNumber = 0)
	{
		$date_entered = $passInfo['date_entered'] ?? date('Y-m-d');
		$created_by = $passInfo['createdBy'] ?? '';

		$airlineCodeOutbound = $passInfo['airlineCodeOutbound'] ?? '';
		$ticketClassOutbound = $passInfo['ticketClassOutbound'] ?? '';
		$airlineCodeInbound = $passInfo['airlineCodeInbound'] ?? '';
		$ticketClassInbound = $passInfo['ticketClassInbound'] ?? '';

		if ($this->isUseNewBaggage($date_entered, $created_by)) {
			$rowBagHTML = '';
			foreach (['outbound', 'inbound'] as $roundName) {
				$roundNameHTML = $roundName == "outbound" ? '<b class="color-primary mr-1">Lượt đi:</b>' : '<b class="color-red mr-1">Lượt về:</b>';

				// Hành lý xách tay
				if (!empty($passInfo["hand_baggage_$roundName"])) {
					$rowBagHTML .= '<p class="fst-italic info-hand-baggage">
						' . $roundNameHTML . ' Xách tay
						<b>' . Baggage::renderAvailableBaggage($passInfo["hand_baggage_$roundName"]) . '</b>
					</p>';
				}

				// Hành lý ký gửi có sẵn
				if (!empty($passInfo["luggage_index_$roundName"])) {
					$rowBagHTML .= '<p class="fst-italic">
						' . $roundNameHTML . ' Ký gửi
						<b>' . Baggage::renderAvailableBaggage($passInfo["luggage_index_$roundName"]) . '</b>
						<span>(Hạng vé có sẵn)</span>
					</p>';
				}

				// Hành lý ký gửi mua thêm
				$suffix = $roundName == "outbound" ? "" : "_inbound";
				$bagText = $passInfo["luggage_purchase_text$suffix"] ?? '';
				$bagSellingPrice = $passInfo["luggage_price$suffix"] ?? 0;
				if ($bagSellingPrice > 0 || !empty($bagText)) {
					// $bagCost = $passInfo["luggage_purchase{$suffix}_no_vat"] ?? 0;
					// $bagTax = $passInfo["vat_luggage_purchase$suffix"] ?? 0; // VAT
					$bagPrice = $passInfo["luggage_purchase$suffix"] ?? 0;
					$bagTicketNum = $passInfo["eluggage_$roundName"] ?? '';
					$bagTicketNumHTML = !empty($bagTicketNum) ? '<span class="badge bg-light text-dark fw-normal ms-1">Số vé HL: <b>' . $bagTicketNum . '</b></span>' : '';

					$rowBagHTML .= '<p class="fst-italic info-purchage-baggage">
						' . $roundNameHTML . ' Ký gửi
						<b>' . $bagText . '</b>
						<span class="badge bg-light text-dark fw-normal ms-1">
							Giá bán (VAT): <b>' . format_number($bagSellingPrice) . ' VND</b>
						</span>
						<span class="badge bg-light text-dark fw-normal ms-1">
							Giá mua (VAT): <b>' . format_number($bagPrice) . ' VND</b>
						</span>
						<span class="badge bg-light text-dark fw-normal ms-1">
							Nhà cung cấp: <b>' . $passInfo["supplier$suffix"] . '</b>
						</span>
						' . $bagTicketNumHTML . '
					</p>';
				}
			}

			return '<tr class="psg-line luggage" ' . (empty($rowBagHTML) ? 'style="display:none;"' : '') . '>
						<td data-label="Hành lý ký gửi" class="text-center align-middle">
							<svg fill="#000000" width="24px" height="24px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 290.626 290.626" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <path d="M126.563,70.313H98.438v-56.25C98.438,6.309,92.128,0,84.375,0H56.25c-7.753,0-14.063,6.309-14.063,14.063v56.25H14.063 C6.309,70.313,0,76.622,0,84.375v168.75c0,5.297,2.977,9.862,7.312,12.258c-1.645,2.559-2.625,5.578-2.625,8.836 c0,9.047,7.359,16.406,16.406,16.406S37.5,283.266,37.5,274.219c0-2.527-0.623-4.894-1.645-7.031h68.916 c-1.022,2.138-1.645,4.505-1.645,7.031c0,9.047,7.359,16.406,16.406,16.406s16.406-7.359,16.406-16.406 c0-3.258-0.98-6.277-2.625-8.836c4.336-2.395,7.313-6.961,7.313-12.258V84.375C140.625,76.622,134.316,70.313,126.563,70.313z M51.563,14.063c0-2.588,2.099-4.688,4.687-4.688h28.125c2.588,0,4.688,2.1,4.688,4.688v4.688h-37.5V14.063z M51.563,28.125h37.5 v42.188h-37.5V28.125z M9.375,84.375c0-2.588,2.1-4.687,4.688-4.687h4.688V93.75H9.375V84.375z M9.374,253.125v-9.375h0.001 h9.375v14.063h-4.688C11.474,257.813,9.374,255.713,9.374,253.125z M21.094,281.25c-3.876,0-7.031-3.155-7.031-7.031 c0-3.876,3.155-7.031,7.031-7.031s7.031,3.154,7.031,7.031S24.97,281.25,21.094,281.25z M119.531,281.25 c-3.877,0-7.031-3.155-7.031-7.031c0-3.876,3.155-7.031,7.031-7.031s7.031,3.155,7.031,7.031 C126.562,278.095,123.408,281.25,119.531,281.25z M131.25,253.125c0,2.587-2.1,4.688-4.687,4.688h-4.688V243.75h9.375V253.125z M131.25,234.375h-9.375c-5.17,0-9.375,4.205-9.375,9.375v14.063H28.125V243.75c0-5.17-4.205-9.375-9.375-9.375H9.375v-131.25 h9.375c5.17,0,9.375-4.205,9.375-9.375V79.688h14.063h56.25H112.5V93.75c0,5.17,4.205,9.375,9.375,9.375h9.375V234.375z M131.25,93.75h-9.375V79.688h4.688c2.587,0,4.687,2.099,4.687,4.687V93.75z"></path> <rect x="23.438" y="112.5" width="9.375" height="112.5"></rect> <rect x="46.875" y="112.5" width="9.375" height="112.5"></rect> <rect x="107.813" y="112.5" width="9.375" height="112.5"></rect> <rect x="84.375" y="112.5" width="9.375" height="112.5"></rect> <path d="M276.563,112.5h-112.5c-7.753,0-14.063,6.309-14.063,14.063v126.563c0,5.297,2.977,9.862,7.313,12.258 c-1.645,2.559-2.625,5.578-2.625,8.836c0,9.047,7.359,16.406,16.406,16.406s16.406-7.359,16.406-16.406 c0-2.527-0.623-4.894-1.645-7.031h68.916c-1.022,2.138-1.645,4.505-1.645,7.031c0,9.047,7.359,16.406,16.406,16.406 s16.406-7.359,16.406-16.406c0-3.258-0.98-6.277-2.625-8.836c4.336-2.395,7.313-6.961,7.313-12.258V126.563 C290.625,118.809,284.316,112.5,276.563,112.5z M171.094,281.25c-3.876,0-7.031-3.155-7.031-7.031 c0-3.876,3.155-7.031,7.031-7.031c3.876,0,7.031,3.154,7.031,7.031S174.97,281.25,171.094,281.25z M269.531,281.25 c-3.877,0-7.031-3.155-7.031-7.031c0-3.876,3.155-7.031,7.031-7.031c3.876,0,7.031,3.155,7.031,7.031 C276.562,278.095,273.408,281.25,269.531,281.25z M281.248,253.125c0.002,2.587-2.098,4.688-4.685,4.688h-112.5 c-2.587,0-4.688-2.1-4.688-4.688v-95.784l8.067-4.842c7.838-4.702,16.814-7.186,25.955-7.186h39.164l-2.63,7.894 c-0.82,2.456,0.506,5.114,2.962,5.93c0.492,0.159,0.994,0.239,1.481,0.239c1.964,0,3.792-1.242,4.444-3.206l3.619-10.856h2.62 l3.619,10.856c0.656,1.964,2.484,3.206,4.448,3.206c0.488,0,0.989-0.08,1.481-0.244c2.452-0.816,3.783-3.469,2.962-5.93 l-2.4-7.195c6.342,1.012,12.469,3.164,18.014,6.492l8.067,4.842V253.125z M281.251,146.405l-3.239-1.945 c-8.798-5.273-18.802-8.166-29.034-8.466c-0.145-0.019-0.281-0.009-0.427-0.014c-0.441-0.005-0.881-0.042-1.322-0.042h-53.831 c-10.842,0-21.483,2.948-30.778,8.522l-3.244,1.945v-19.842c-0.001-2.588,2.099-4.688,4.687-4.688h112.5 c2.587,0,4.688,2.1,4.688,4.688V146.405z"></path> <path d="M164.063,107.813h112.5c7.753,0,14.063-6.309,14.039-14.531l-3.97-39.698c-0.534-5.334-2.025-10.467-4.416-15.263 c-7.364-14.723-22.055-23.911-38.466-24.202v-0.056C243.75,6.309,237.441,0,229.688,0h-18.75 c-7.753,0-14.063,6.309-14.063,14.063v0.056c-16.411,0.291-31.102,9.483-38.466,24.206c-2.395,4.791-3.881,9.928-4.416,15.263 L150,93.75C150,101.503,156.309,107.813,164.063,107.813z M210.938,9.375h18.75c2.587,0,4.688,2.1,4.688,4.688H206.25 C206.25,11.475,208.35,9.375,210.938,9.375z M163.322,54.52c0.422-4.195,1.589-8.236,3.473-12.005 c5.887-11.766,17.714-19.078,30.872-19.078h45.291c13.158,0,24.984,7.313,30.872,19.078c1.884,3.769,3.052,7.809,3.473,12.005 l3.947,39.23c0,2.588-2.1,4.688-4.688,4.688h-112.5c-2.587,0-4.688-2.1-4.711-4.219L163.322,54.52z"></path> <path d="M239.064,51.563h-0.001c0,2.592,2.095,4.688,4.688,4.688c2.593,0,4.688-2.095,4.688-4.688v-9.375h9.375v-9.375h-75v9.375 h56.25V51.563z"></path> </g> </g> </g> </g></svg>
						</td>
						<td colspan="10" class="text-start align-middle flex-wrap">
							' . $rowBagHTML . '
						</td>
					</tr>';
		} else {
			$luggage_price = '';

			/***** Hành lý chiều đi *****/
			// Bag_out là list option hành lý
			$bag_out = generateLuggage($date_entered, $airlineCodeOutbound, $ticketClassOutbound, $passInfo['type'], (int) $passInfo['luggage_index_outbound']);
			if (!empty($passInfo['luggage_index_outbound'])) {
				$passInfo['luggage_price'] = (int) $passInfo['luggage_index_outbound'];
			}

			$bag_out2 = $bag_out[(int) $passInfo['luggage_price']] ?? '';

			$bag_weight_out = 0;
			if (isset($bag_out2) && !empty($bag_out2)) {
				preg_match('/(\d+)kg/isU', $bag_out2, $ob_output);
				$bag_weight_out = isset($ob_output[1]) ? (int) $ob_output[1] : 0;
			}

			if ($bag_weight_out > 0) {
				$lug_purchase_inf = '';
				if ($passInfo['luggage_purchase'] > 0) {
					$lug_purchase_inf .= ' - Giá mua: ' . format_number($passInfo['luggage_purchase_no_vat']) . ' - VAT giá mua: ' . format_number($passInfo['vat_luggage_purchase']);
				}

				$eluggage_outbound = '';
				if (strlen($passInfo['eluggage_outbound']) > 0) {
					$eluggage_outbound .= '<span data-label="Số vé HL đi" class="text-center eluggage_outbound">
											(<span class="color-primary fst-italic fw-semibold">Số vé HL lượt đi</span>: <strong>' . strtoupper($passInfo['eluggage_outbound']) . '</strong>)
											<input type="hidden" name="eluggage_outbound[]" id="eluggage_outbound' . $orderNumber . '" value="' . strtoupper($passInfo['eluggage_outbound']) . '"  />
										</span>';
				}
				$luggage_price .= '<div class="luggage__outbound">
										<span class="color-primary fst-italic fw-semibold">Lượt đi</span>: ' . $bag_out2 . ' (Giá mua (VAT): ' . format_number($passInfo['luggage_purchase']) . ' - Nhà cung cấp: ' . $passInfo['supplier'] . $lug_purchase_inf . ')
										' . $eluggage_outbound . '
									</div>';
			}

			/***** Hành lý chiều về *****/
			if (!empty($airlineCodeInbound)) {
				$bag_in = generateLuggage($date_entered, $airlineCodeInbound, $ticketClassInbound, $passInfo['type'], (int) $passInfo['luggage_index_inbound']);

				if (!empty($passInfo['luggage_index_inbound'])) {
					$passInfo['luggage_price_inbound'] = (int) $passInfo['luggage_index_inbound'];
				}

				$bag_in2 = $bag_in[(int) $passInfo['luggage_price_inbound']] ?? '';

				$bag_weight_in = 0;
				if (isset($bag_in2) && !empty($bag_in2)) {
					preg_match('/(\d+)kg/isU', $bag_in2, $ib_output);
					$bag_weight_in = isset($ib_output[1]) ? (int) $ib_output[1] : 0;
				}

				if ($bag_weight_in > 0) {
					$in_lug_purchase_inf = '';
					if ($passInfo['luggage_purchase_inbound'] > 0) {
						$in_lug_purchase_inf .= ' - Giá mua: ' . format_number($passInfo['luggage_purchase_inbound_no_vat']) . ' - VAT giá mua: ' . format_number($passInfo['vat_luggage_purchase_inbound']);
					}

					$eluggage_inbound = '';
					if (strlen($passInfo['eluggage_inbound']) > 0) {
						$eluggage_inbound .= '<span data-label="Số vé HL về" class="text-center" class="eluggage_inbound">
							(<span class="color-red fst-italic fw-semibold">Số vé HL lượt về</span>: <strong>' . strtoupper($passInfo['eluggage_inbound']) . '</strong>)
							<input type="hidden" name="eluggage_inbound[]" id="eluggage_inbound' . $orderNumber . '" value="' . strtoupper($passInfo['eluggage_inbound']) . '"  />
						</span>';
					}

					$luggage_price .= '<div class="luggage__inbound mt-2">
						<span class="color-red fst-italic fw-semibold">Lượt về</span>: ' . $bag_in2 . ' (Giá mua (VAT): ' . format_number($passInfo['luggage_purchase_inbound']) . ' - Nhà cung cấp: ' . $passInfo['supplier_inbound'] . $in_lug_purchase_inf . ')
						' . $eluggage_inbound . '
					</div>';
				}
			}

			return '<tr class="psg-line luggage" ' . (trim($luggage_price) == '' ? 'style="display:none;"' : '') . '>
						<td data-label="Hành lý ký gửi" class="text-center bg-yellow align-middle">&nbsp;</td>
						<td colspan="10" class="text-start align-middle fst-italic flex-wrap">' . $luggage_price . '</td>
					</tr>';
		}
	}

	/**
	 * Generate passenger baggage info (Use in sending email booking)
	 * 
	 * @param string $depAvaiBagText
	 * @param string $depPurchaseBagText
	 * @param string $retAvaiBagText
	 * @param string $retPurchaseBagText
	 * @param string $language vn, es
	 * @return string
	 */
	public function generateCombinedPassengerBaggageInfo($depAvaiBagText, $depPurchaseBagText, $retAvaiBagText, $retPurchaseBagText, $language = 'vn')
	{
		$isRoundtrip = false;
		if ((!empty($depAvaiBagText) || !empty($depPurchaseBagText)) && (!empty($retAvaiBagText) || !empty($retPurchaseBagText)))
			$isRoundtrip = true;

		// Departure
		$baggageDescriptionDep = '';
		if (!empty($depAvaiBagText) && !empty($depPurchaseBagText)) {
			$avaiBagDepParts = Baggage::parsePackage($depAvaiBagText); // Array
			$purchasedBagDepParts = Baggage::parsePackage($depPurchaseBagText); // Array

			// Conbine
			if (
				$avaiBagDepParts['weight'] === $purchasedBagDepParts['weight'] && !is_null($avaiBagDepParts['weight'])
				&& $avaiBagDepParts['package'] > 0 && $purchasedBagDepParts['package'] > 0
				&& stripos($depAvaiBagText, 't') === false
			) {
				$baggageDescriptionDep .= ($avaiBagDepParts['package'] + $purchasedBagDepParts['package']) . ($language == 'en' ? ' packages' : ' kiện') . ' x ' . $avaiBagDepParts['weight'] . 'kg';
			} elseif (is_null($avaiBagDepParts['package']) && is_null($purchasedBagDepParts['package'])) {
				$baggageDescriptionDep .= ($avaiBagDepParts['weight'] + $purchasedBagDepParts['weight']) . 'kg';
			} elseif (is_null($avaiBagDepParts['weight']) && is_null($purchasedBagDepParts['weight'])) {
				$baggageDescriptionDep .= ($avaiBagDepParts['package'] + $purchasedBagDepParts['package']) . ($language == 'en' ? ' packages' : ' kiện');
			} else {
				$baggageDescriptionDep .= "$depAvaiBagText + $depPurchaseBagText";
			}
		} elseif (!empty($depAvaiBagText))
			$baggageDescriptionDep .= $depAvaiBagText;
		elseif (!empty($depPurchaseBagText))
			$baggageDescriptionDep .= $depPurchaseBagText;
		if (!empty($baggageDescriptionDep) && $isRoundtrip)
			$baggageDescriptionDep .= ($language == 'en' ? ' (Departure)' : ' (Lượt đi)');

		// Return
		$baggageDescriptionRet = '';
		if (!empty($retAvaiBagText) && !empty($retPurchaseBagText)) {
			$avaiBagRetParts = Baggage::parsePackage($retAvaiBagText); // Array
			$purchasedBagRetParts = Baggage::parsePackage($retPurchaseBagText); // Array

			// Conbine
			if (
				$avaiBagRetParts['weight'] === $purchasedBagRetParts['weight'] && !is_null($avaiBagRetParts['weight'])
				&& $avaiBagDepParts['package'] > 0 && $purchasedBagDepParts['package'] > 0
				&& stripos($retAvaiBagText, 't') === false
			) {
				$baggageDescriptionRet .= ($avaiBagRetParts['package'] + $purchasedBagRetParts['package']) . ($language == 'en' ? ' packages' : ' kiện') . ' x ' . $avaiBagRetParts['weight'] . 'kg';
			} elseif (is_null($avaiBagRetParts['package']) && is_null($purchasedBagRetParts['package'])) {
				$baggageDescriptionRet .= ($avaiBagRetParts['weight'] + $purchasedBagRetParts['weight']) . 'kg';
			} elseif (is_null($avaiBagRetParts['weight']) && is_null($purchasedBagRetParts['weight'])) {
				$baggageDescriptionRet .= ($avaiBagRetParts['package'] + $purchasedBagRetParts['package']) . ($language == 'en' ? ' packages' : ' kiện');
			} else {
				$baggageDescriptionRet = "$retAvaiBagText + $retPurchaseBagText";
			}
		} elseif (!empty($retAvaiBagText))
			$baggageDescriptionRet .= $retAvaiBagText;
		elseif (!empty($retPurchaseBagText))
			$baggageDescriptionRet .= $retPurchaseBagText;
		if (!empty($baggageDescriptionRet) && $isRoundtrip)
			$baggageDescriptionRet .= ($language == 'en' ? ' (Return)' : ' (Lượt về)');


		// Combine two way
		if (!empty($baggageDescriptionDep) && !empty($baggageDescriptionRet)) {
			if (stripos($baggageDescriptionDep, '+') !== false || stripos($baggageDescriptionRet, '+') !== false) {
				return "{$baggageDescriptionDep}\n{$baggageDescriptionRet}";
			} else {
				return "{$baggageDescriptionDep} - {$baggageDescriptionRet}";
			}
		} else
			return trim("$baggageDescriptionDep $baggageDescriptionRet");
	}

	/**
	 * Generate options to buy extra baggage
	 * 
	 * @param string $airlineCode
	 * @param string $ticketClass
	 * @param string $currentValue Use to mark selected item
	 * @param string $currentPrice Baggage purchase price
	 * @return string HTML
	 */
	public function generateBaggageOptions($airlineCode, $ticketClass = '', $currentValue = '', $currentPrice = 0)
	{
		$options = "<option value=''>Chọn hành lý</option>";
		if (is_string($airlineCode) && !empty($airlineCode)) {
			try {
				$baggageData = [];
				$cacheKey = "extra_baggage_options_" . strtolower($airlineCode);
				$cacheTime = 3600;

				// Get data in SESSION cache
				$cacheKey = "extra_baggage_options_" . strtolower($airlineCode);
				if (isset($_SESSION) && isset($_SESSION[$cacheKey]) && !empty($_SESSION[$cacheKey])) {
					$sessionData = $_SESSION[$cacheKey];
					if (is_array($sessionData) && !empty($sessionData)) {
						$expiredAt = $sessionData['expiredAt']; // Timestamp
						if (time() < $expiredAt)
							$baggageData = $sessionData['data'];
					}
				}

				// Get data from API
				if (!is_array($baggageData) || empty($baggageData)) {
					$epFactory = new entryFactory();
					$fareSystem = $epFactory->create('entryFareSystemClass');

					$baggageResponse = $fareSystem->getBaggageOption(['airlineCode' => $airlineCode]);
					$baggageResponse = json_decode($baggageResponse, true);

					if (isset($baggageResponse['status']) && $baggageResponse['status'] == 1) {
						$baggageData = $baggageResponse['data'] ?? [];
						if (!empty($baggageData)) {
							$_SESSION[$cacheKey] = [
								'data' => $baggageData,
								'expiredAt' => time() + $cacheTime
							];
						} else {
							$_SESSION[$cacheKey] = null;
							unset($_SESSION[$cacheKey]);
						}
					}
				}

				$foundMatch = false;
				$currentValue = trim(preg_replace('/\s*\([^)]*\)\s*$/', '', preg_replace('/^Thêm\s+/', '', $currentValue)));

				foreach ($baggageData as $bag) {
					$cost = $bag['cost'] ?? 0;
					$value = $bag['value'] ?? 0;
					$description = $bag['description'] ?? '';

					if (!empty($description)) {
						$displayText = trim(preg_replace('/^Thêm\s+/', '', $description));
						$saveValue = trim(preg_replace('/\s*\([^)]*\)\s*$/', '', preg_replace('/^Thêm\s+/', '', $description)));

						$selected = '';
						if ($currentValue == $saveValue) {
							$selected = 'selected';
							$foundMatch = true;
						}

						$options .= "<option value='" . htmlspecialchars($saveValue) . "'
							data-text='" . htmlspecialchars($displayText) . "'
							data-cost='{$cost}'
							data-value='{$value}'
							{$selected}
						>
							" . htmlspecialchars($displayText) . "
						</option>";
					}
				}
				if (!empty($currentValue) && !$foundMatch) {
					$options .= '<option value="' . htmlspecialchars($currentValue) . '"
						data-text="' . htmlspecialchars($currentValue) . ' (Tùy chỉnh)"
						data-cost="' . $currentPrice . '"
						data-value="' . $currentPrice . '" selected
					>
						' . htmlspecialchars($currentValue) . ' (Tùy chỉnh)
					</option>';
				}
			} catch (Throwable $th) {
				$GLOBALS['log']->fatal("Error fetching baggage options: {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
			}
		}
		return $options;
	}
	/**
	 * Get raw baggage data array (for JSON output to JS)
	 */
	public function getBaggageOptionsData(string $airlineCode): array
	{
		if (empty($airlineCode))
			return [];

		$baggageData = [];
		$cacheKey = "extra_baggage_options_" . strtolower($airlineCode);

		// Dùng lại SESSION cache giống generateBaggageOptions()
		if (isset($_SESSION[$cacheKey]) && !empty($_SESSION[$cacheKey])) {
			$sessionData = $_SESSION[$cacheKey];
			if (is_array($sessionData) && time() < ($sessionData['expiredAt'] ?? 0)) {
				$baggageData = $sessionData['data'];
			}
		}

		if (empty($baggageData)) {
			try {
				$epFactory = new entryFactory();
				$fareSystem = $epFactory->create('entryFareSystemClass');
				$response = json_decode($fareSystem->getBaggageOption(['airlineCode' => $airlineCode]), true);

				if (isset($response['status']) && $response['status'] == 1) {
					$baggageData = $response['data'] ?? [];
					if (!empty($baggageData)) {
						$_SESSION[$cacheKey] = [
							'data' => $baggageData,
							'expiredAt' => time() + 3600,
						];
					}
				}
			} catch (Throwable $th) {
				$GLOBALS['log']->fatal("getBaggageOptionsData error: " . $th->getMessage());
			}
		}

		// Chuẩn hoá thành array [{description, cost, value}] cho JS dùng
		$result = [];
		foreach ($baggageData as $bag) {
			$description = trim($bag['description'] ?? '');
			if (empty($description))
				continue;

			$result[] = [
				'description' => $description,
				'cost' => (float) ($bag['cost'] ?? 0), // giá mua VAT
				'value' => (float) ($bag['value'] ?? 0), // giá bán VAT
			];
		}
		return $result;
	}
	/**
	 * Get list ticket number in booking by times
	 * 
	 * @param string $bookingId
	 * @return array
	 */
	public function getListTickets($bookingId)
	{
		if (!is_string($bookingId) || empty($bookingId))
			return [];

		$listTickets = [];

		$sqloutinv = "SELECT COUNT(DISTINCT parent_id) AS output_invoice_qty
			FROM ec_chitiethoadon ct
			WHERE ct.booking_id = '{$bookingId}' AND ct.deleted = 0";
		$outputInvQty = $this->db->getOne($sqloutinv) ?? 0;

		$goWithArray = [];
		$resPaymentReceipt = $this->db->query(
			"SELECT DISTINCT IFNULL(go_with, 0)
			FROM ec_receipt_voucher
			WHERE booking_id = '{$bookingId}'
				AND rv_status != '0'
				AND deleted = 0"
		);
		while ($rowPaymentReceipt = $this->db->fetchByAssoc($resPaymentReceipt))
			$goWithArray[] = (int) ($rowPaymentReceipt['go_with'] ?? 0);

		$sqltk = "SELECT p.id
				,p.name
				,IFNULL(p.add_type, 0) AS addType
				,IFNULL(p.go_with, 0) AS goWith
				,p.eticket_outbound AS ticketNumberOut
                ,p.eticket_inbound AS ticketNumberIn
				,p.eluggage_outbound AS bagTicketNumberOut
				,p.eluggage_inbound AS bagTicketNumberIn
				,IFNULL(p.luggage_purchase, 0) AS bagPriceOut
				,IFNULL(p.luggage_purchase_inbound, 0) AS bagPriceIn
            FROM ec_booking_passengers p
            WHERE p.booking_id = '{$bookingId}'
				AND p.deleted = 0
				-- AND (p.add_type NOT IN (1, 2) OR p.add_type IS NULL)
			ORDER BY p.date_entered";

		$restk = $this->db->query($sqltk);
		while ($row = $this->db->fetchByAssoc($restk)) {
			$goWith = (int) ($row['goWith'] ?? 0); // Changed times of passenger in booking
			$ticketType = (int) ($row['addType'] ?? 0);

			// Only get list ticket code for next processing
			if ($goWith < $outputInvQty)
				continue;

			// Only get list ticket code in changed times which have receipt voucher
			if ($goWith > 0 && array_search($goWith, $goWithArray) === false)
				continue;

			// Flight ticket number
			if ($ticketType != 1) {
				if (isset($row['ticketNumberOut']) && !empty($row['ticketNumberOut'])) {
					$listTickets[$goWith][$row['ticketNumberOut']][] = [
						'type' => 'flight',
						'direction' => 0,
						'passName' => $row['name'],
						'purchasePrice' => null,
					];
				}
				if (isset($row['ticketNumberIn']) && !empty($row['ticketNumberIn'])) {
					$listTickets[$goWith][$row['ticketNumberIn']][] = [
						'type' => 'flight',
						'direction' => 1,
						'passName' => $row['name'],
						'purchasePrice' => null,
					];
				}
			}

			// Baggage ticket number
			if (isset($row['bagPriceOut']) && $row['bagPriceOut'] > 0) {
				$bagTicketNumberOut = $row['bagTicketNumberOut'] ?? '';
				if (empty($bagTicketNumberOut))
					$bagTicketNumberOut = $row['ticketNumberOut'] ?? 'BAGTICKETOUT';

				$listTickets[$goWith][$bagTicketNumberOut][] = [
					'type' => 'baggage',
					'direction' => 0,
					'passName' => $row['name'],
					'purchasePrice' => $row['bagPriceOut'],
				];
			}
			if (isset($row['bagPriceIn']) && $row['bagPriceIn'] > 0) {
				$bagTicketNumberIn = $row['bagTicketNumberIn'] ?? '';
				if (empty($bagTicketNumberIn))
					$bagTicketNumberIn = $row['ticketNumberIn'] ?? 'BAGTICKETIN';

				$listTickets[$goWith][$bagTicketNumberIn][] = [
					'type' => 'baggage',
					'direction' => 1,
					'passName' => $row['name'],
					'purchasePrice' => $row['bagPriceIn'],
				];
			}
		}

		return $listTickets;
	}

	public function getPassengerInfoMailConfirm($booking_id, $flight_type)
	{
		global $db, $app_list_strings;

		$sql = "SELECT
				p.type AS pax_type
				,p.salutation AS pax_title
				,p.name AS pax_name
				,p.birthday AS pax_dob
				,p.date_entered
				,p.created_by
				,(
					SELECT i.airline_code
					FROM ec_booking_itineraries i
					WHERE i.booking_id = p.booking_id
						AND i.deleted = 0
						AND i.direction = 0
						AND i.is_layover = 0
					LIMIT 1
				) AS aircode_out
				,(
					SELECT i.ticket_class
					FROM ec_booking_itineraries i
					WHERE i.booking_id = p.booking_id
						AND i.deleted = 0
						AND i.direction = 0
						AND i.is_layover = 0
					LIMIT 1
				) AS ticket_class_out
				,(
					SELECT i.airline_code
					FROM ec_booking_itineraries i
					WHERE i.booking_id = p.booking_id
						AND i.deleted = 0
						AND i.direction = 1
						AND i.is_layover = 0
					LIMIT 1
				) AS aircode_in
				,(
					SELECT i.ticket_class
					FROM ec_booking_itineraries i
					WHERE i.booking_id = p.booking_id 
						AND i.deleted = 0
						AND i.direction = 1
						AND i.is_layover = 0
					LIMIT 1
				) AS ticket_class_in
				,p.luggage_price
				,p.luggage_price_inbound
				,p.luggage_index_outbound
				,p.luggage_index_inbound
				,p.luggage_purchase_text
				,p.luggage_purchase_text_inbound
				,p.passport_number
			FROM ec_booking_passengers p
			WHERE p.booking_id = '$booking_id'
				AND p.deleted = 0
				AND add_type IS NULL
			ORDER BY pax_type, p.date_entered
		";
		$res = $db->query($sql);

		// Title
		$html = '<tr>
			<td style="width:10%; border:1px solid #e7e7e7; padding:5px;"></td>
			<td style="width:25%; border:1px solid #e7e7e7; padding:5px; text-align:center;">Hành khách</td>
			<td style="width:15%; border:1px solid #e7e7e7; padding:5px; text-align:center;">Ngày sinh</td>
			<td style="width:10%; border:1px solid #e7e7e7; padding:5px; text-align:center;">CCCD/Passport</td>
		';
		if ((int) $flight_type === 0) {
			$html .= '
				<td style="width:20%; border:1px solid #e7e7e7; padding:5px; text-align:center;">HL ký gửi đi</td>
				<td style="width:20%; border:1px solid #e7e7e7; padding:5px; text-align:center;">HL ký gửi về</td>
			';
		} else {
			$html .= '<td style="width:40%; border:1px solid #e7e7e7; padding:5px; text-align:center;">HL ký gửi</td>';
		}
		$html .= '</tr>';

		while ($row = $db->fetchByAssoc($res)) {
			$passport_number = $row['passport_number'] ?? '';

			$dob = '';
			if (!is_null($dob) && $row['pax_dob'] != '' && $row['pax_dob'] != '0000-00-00') {
				try {
					$dob = new DateTime($row['pax_dob']);
					$dob = date_format($dob, 'd/m/Y');
				} catch (\Exception $ex) {
					$dob = '';
				}
			}

			// Baggage outbound
			$bagOut = $this->getBaggageInfoByData([
				"airlineCode" => $row['aircode_out'],
				"ticketClass" => $row['ticket_class_out'],
				"passType" => $row["pax_type"],
				"dateEntered" => $row["date_entered"],
				"createdBy" => $row["created_by"],
				"bagIndex" => $row["luggage_index_outbound"] ?? $row["luggage_price"],
				"bagPurchaseText" => $row["luggage_purchase_text"],
			]);
			$bagOutDescription = $this->generateCombinedPassengerBaggageInfo($bagOut['available'], $bagOut['purchase'], '', '');
			$tdBaggage = '<td style="border:1px solid #e7e7e7; padding:5px; text-align:center;">' . trim($bagOutDescription) . '</td>';

			// Baggage inbound
			if ((int) $flight_type == 0) {
				$bagIn = $this->getBaggageInfoByData([
					"airlineCode" => $row['aircode_in'],
					"ticketClass" => $row['ticket_class_in'],
					"passType" => $row["pax_type"],
					"dateEntered" => $row["date_entered"],
					"createdBy" => $row["created_by"],
					"bagIndex" => $row["luggage_index_inbound"] ?? $row["luggage_price_inbound"],
					"bagPurchaseText" => $row["luggage_purchase_text_inbound"],
				]);
				$bagInDescription = $this->generateCombinedPassengerBaggageInfo($bagIn['available'], $bagIn['purchase'], '', '');
				$tdBaggage .= '<td style="border:1px solid #e7e7e7; padding:5px; text-align:center;">' . trim($bagInDescription) . '</td>';
			}

			$html .= '<tr>
				<td style="border:1px solid #e7e7e7; padding:5px; text-align:center;">' . $app_list_strings['passenger_type_list'][$row['pax_type']] . '</td>
				<td style="border:1px solid #e7e7e7; padding:5px;"><label style="text-transform:uppercase;">' . $row['pax_name'] . '</label></td>
				<td style="border:1px solid #e7e7e7; padding:5px; text-align:center;">' . $dob . '</td>
				<td style="border:1px solid #e7e7e7; padding:5px; text-align:center;">' . $passport_number . '</td>
				' . $tdBaggage . '
			</tr>';
		}

		return $html;
	}

	public function getRouteInfosMailConfirm($booking_id, $type = 'normal')
	{
		global $db;
		$sql = "SELECT 
					i.direction
					,i.flight_number
					,i.departure_date
					,i.arrival_date
					,(
						CASE 
							WHEN i.airline_code='VNA' THEN 'VN'
							WHEN i.airline_code='VJA' THEN 'VJ'
							WHEN i.airline_code='JET' THEN 'BL'
							WHEN i.airline_code='VNP' THEN 'BL'
							WHEN i.airline_code='AMK' THEN 'P8'
							WHEN i.airline_code='BBA' THEN 'QH'
							ELSE i.airline_code
						END
					) AS airline_code
					,i.departure
					,i.arrival
					,i.ticket_class
					,i.time_limit
			FROM ec_booking_itineraries i
			WHERE i.booking_id = '$booking_id' AND i.deleted = 0
			ORDER BY i.direction, i.departure_date, i.date_entered";

		$res = $db->query($sql);
		$html = '';
		$time_limit = '';
		$airline_code = '';
		$i = 0;

		while ($row = $db->fetchByAssoc($res)) {
			$airline_code = EC_Airlines::normalizeIataCode($row['airline_code']);
			$airlineName = EC_Airlines::getAirlineName($airline_code) ?: $airline_code;

			$departure_code = $row['departure'] ?? '';
			$departure_name = EC_Airports::getCityName($departure_code) ?: $departure_code;

			$arrival_code = $row['arrival'] ?? '';
			$arrival_name = EC_Airports::getCityName($arrival_code) ?: $arrival_code;

			if ((int) $row['direction'] === 0 && $i == 0) {
				$time_limit = $row['time_limit'];
				$airline_code = $row['airline_code'];
			}

			if ($type === 'normal') {
				$html .= '
					<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-5" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
						<tbody>
							<tr>
								<td>
									<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #a9e0ff; color: #000000;width:100%;">
										<tbody>
											<tr>
												<td class="column column-1" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; border: 0px; " width="33.333333333333336%">
													<table border="0" cellpadding="0" cellspacing="0" class="text_block block-2" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
														<tr>
															<td class="pad">
																<div style="font-family: sans-serif">
																	<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; line-height: 1.5; ">
																		<p style=" margin: 0; font-size: 14px; text-align: center; mso-line-height-alt: 16.8px; ">
																			<span style="font-size: 32px;"><strong>' . $departure_code . '</strong></span>
																		</p>
																	</div>
																</div>
															</td>
														</tr>
													</table>
													<table border="0" cellpadding="10" cellspacing="0" class="text_block block-3" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
														<tr>
															<td class="pad" style="padding: 0;">
																<div style="font-family: sans-serif">
																	<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; line-height: 1.5; ">
																		<p style=" margin: 0; text-align: center; mso-line-height-alt: 14.399999999999999px; ">
																			<span style="font-size: 16px">' . $departure_name . '</span>
																		</p>
																	</div>
																</div>
															</td>
														</tr>
													</table>
												</td>
												<td class="column column-2" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; border: 0px; " width="33.333333333333336%">
													<table border="0" cellpadding="0" cellspacing="0" class="image_block block-2" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; " width="100%">
														<tr>
															<td class="pad" style=" width: 100%; padding-right: 0px; padding-left: 0px; ">
																<div align="center" class="alignment" style="line-height: 10px; font-size: 18px; color: #000; padding: 12px 0; font-weight: 600;">
																	' . $row['flight_number'] . '
																</div>
															</td>
														</tr>
													</table>
													<table border="0" cellpadding="0" cellspacing="0" class="text_block block-4" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
														<tr>
															<td class="pad">
																<div style="font-family: sans-serif">
																	<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; font-weight: 600; line-height: 1.5; ">
																		<p style=" margin: 0; font-size: 14px; text-align: center; mso-line-height-alt: 16.8px; ">
																			<span style="font-size: 15px">' . date('d/m/Y H:i', strtotime($row['departure_date'])) . ' &rarr; ' . date('H:i', strtotime($row['arrival_date'])) . '</span>
																		</p>
																	</div>
																</div>
															</td>
														</tr>
													</table>
												</td>
												<td class="column column-3" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; border: 0px; " width="33.333333333333336%">
													<table border="0" cellpadding="0" cellspacing="0" class="text_block block-2" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
														<tr>
															<td class="pad">
																<div style="font-family: sans-serif">
																<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; line-height: 1.5; ">
																	<p style=" margin: 0; font-size: 14px; text-align: center; mso-line-height-alt: 16.8px; ">
																		<span style="font-size: 32px;"><strong>' . $arrival_code . '</strong></span>
																	</p>
																</div>
																</div>
															</td>
														</tr>
													</table>
													<table border="0" cellpadding="10" cellspacing="0" class="text_block block-3" role="presentation" style=" mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word; " width="100%">
														<tr>
															<td class="pad" style="padding: 0;">
																<div style="font-family: sans-serif">
																	<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; line-height: 1.5; ">
																		<p style="margin: 0;text-align: center;mso-line-height-alt: 14.399999999999999px;">
																			<span style="font-size: 16px">' . $arrival_name . '</span>
																		</p>
																	</div>
																</div>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
					<table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-7" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt" width="100%">
						<tbody>
							<tr>
								<td>
									<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #a9e0ff;color: #000000;width:100%;">
										<tbody>
											<tr>
												<td class="column column-1" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;font-weight: 400;text-align: left;border: 0px;" width="100%">
													<table border="0" cellpadding="10" cellspacing="0" class="text_block block-1" role="presentation" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;word-break: break-word;" width="100%">
														<tr>
															<td class="pad" style="padding-top: 0px;">
																<div style="font-family: sans-serif">
																	<div class="" style="font-size: 12px;font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif;mso-line-height-alt: 14.399999999999999px;color: #000;line-height: 1.5;">
																		<p style="margin: 0;text-align: center;mso-line-height-alt: 14.399999999999999px;">
																			<span style="font-size: 13px; font-weight: 600;">Hãng: ' . $airlineName . '</span>
																		</p>
																	</div>
																</div>
															</td>
														</tr>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>';
			} else {
				// Preview
				$html .= '
					<div class="row pb-3" style="background-color: #a9e0ff;">
						<div class="col-4">
							<div class="d-flex align-items-center flex-fill flex-column">
								<div style="font-family: sans-serif">
									<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; line-height: 1.5; ">
										<p style=" margin: 0; font-size: 14px; text-align: center; mso-line-height-alt: 16.8px; ">
											<span style="font-size: 32px;"><strong>' . $departure_code . '</strong></span>
										</p>
									</div>
								</div>
								<div style="font-family: sans-serif">
									<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; line-height: 1.5; ">
										<p style=" margin: 0; text-align: center; mso-line-height-alt: 14.399999999999999px; ">
											<span style="font-size: 16px">' . $departure_name . '</span>
										</p>
									</div>
								</div>
							</div>
						</div>
						<div class="col-4">
							<div class="d-flex align-items-center flex-fill flex-column">
								<div align="center" class="alignment" style="line-height: 10px; font-size: 18px; color: #000; padding: 12px 0; font-weight: 600;">
									' . $row['flight_number'] . '
								</div>
								<div style="font-family: sans-serif">
									<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; font-weight: 600; line-height: 1.5; ">
										<p style=" margin: 0; font-size: 14px; text-align: center; mso-line-height-alt: 16.8px; ">
											<span style="font-size: 15px">' . date('d/m/Y H:i', strtotime($row['departure_date'])) . ' &rarr; ' . date('H:i', strtotime($row['arrival_date'])) . '</span>
										</p>
									</div>
								</div>
								<div style="font-family: sans-serif">
									<div class="" style="font-size: 12px;font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif;mso-line-height-alt: 14.399999999999999px;color: #000;line-height: 1.5;">
										<p style="margin: 0;text-align: center;mso-line-height-alt: 14.399999999999999px;">
											<span style="font-size: 13px; font-weight: 600;">Hãng: ' . $airlineName . '</span>
										</p>
									</div>
								</div>
							</div>
						</div>
						<div class="col-4">
							<div class="d-flex align-items-center flex-fill flex-column">
								<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; line-height: 1.5; ">
									<p style=" margin: 0; font-size: 14px; text-align: center; mso-line-height-alt: 16.8px; ">
										<span style="font-size: 32px;"><strong>' . $arrival_code . '</strong></span>
									</p>
								</div>
								<div style="font-family: sans-serif">
									<div class="" style=" font-size: 12px; font-family: \'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif; mso-line-height-alt: 14.399999999999999px; color: #000; line-height: 1.5; ">
										<p style="margin: 0;text-align: center;mso-line-height-alt: 14.399999999999999px;">
											<span style="font-size: 16px">' . $arrival_name . '</span>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				';
			}

			$i++;
		}

		return array('html' => $html, 'airline_code' => $airline_code, 'time_limit' => $time_limit);
	}

	/***********  OLD FUNCTIONS (DON'T PASTE NEW CODE HERE) ***********/
	public function saveLinePassengersOld()
	{
		global $app_list_strings, $current_user;

		$row_count = count($_POST['psg_id']);
		for ($i = 0; $i < $row_count; $i++) {
			$psg = new EC_Booking_Passengers();
			if (!empty($_POST['psg_id'][$i]))
				$psg->retrieve($_POST['psg_id'][$i]);
			else
				$psg->id = '';

			$psg->type = $_POST['psg_traveller_type'][$i];
			$psg->salutation = $_POST['psg_salutation'][$i];
			$psg->name = strtoupper(myRemoveUnicodeChars(trim(stripslashes($_POST['psg_full_name'][$i]))));

			$birthday = str_replace('/', '-', $_POST['psg_birthday'][$i] ?? '');
			$psg->birthday = strtotime($birthday) !== false ? date('d-m-Y', strtotime($birthday)) : '';

			$psg->eticket_outbound = trim(stripslashes($_POST['psg_eticket_outbound'][$i]));
			$psg->eticket_inbound = trim(stripslashes($_POST['psg_eticket_inbound'][$i]));

			$psg->eluggage_outbound = trim(stripslashes($_POST['psg_eluggage_outbound'][$i]));
			$psg->eluggage_inbound = trim(stripslashes($_POST['psg_eluggage_inbound'][$i]));

			$psg->pnr_outbound = trim(stripslashes($_POST['psg_pnr_outbound'][$i]));
			$psg->pnr_inbound = trim(stripslashes($_POST['psg_pnr_inbound'][$i]));

			if (isset($_POST['psg_luggage_price'][$i])) {
				if (((!isset($_POST['psg_luggage_ob_ind']) || empty($_POST['psg_luggage_ob_ind'][$i])) && (string) $this->airline !== 'VJA' && (string) $this->airline !== 'VJ') || ((string) $this->airline !== 'VJA' && (string) $this->airline !== 'VJ')) {
					$psg->luggage_price = unformat_number($_POST['psg_luggage_price'][$i]);
					$psg->luggage_index_outbound = '';
				} else {
					// BK đặt từ ngày 21-11-2022, VJA có giá mới
					$list_key = strtotime($this->date_entered) >= strtotime('2022-11-21') ? 'vietjet_index_price_list2' : 'vietjet_index_price_list';
					$psg->luggage_price = $app_list_strings[$list_key][(int) $_POST['psg_luggage_price'][$i]];

					$psg->luggage_index_outbound = (int) $_POST['psg_luggage_price'][$i];
				}
			}

			if (isset($_POST['psg_luggage_price_inbound'][$i])) {
				if (((!isset($_POST['psg_luggage_ib_ind']) || empty($_POST['psg_luggage_ib_ind'][$i])) && (string) $this->airline !== 'VJA' && (string) $this->airline !== 'VJ') || ((string) $this->airline_inbound !== 'VJA' && (string) $this->airline_inbound !== 'VJ')) {
					$psg->luggage_price_inbound = unformat_number($_POST['psg_luggage_price_inbound'][$i]);
					$psg->luggage_index_inbound = '';
				} else {
					// BK đặt từ ngày 21-11-2022, VJA có giá mới
					$list_key = strtotime($this->date_entered) >= strtotime('2022-11-21') ? 'vietjet_index_price_list2' : 'vietjet_index_price_list';
					$psg->luggage_price_inbound = $app_list_strings[$list_key][(int) $_POST['psg_luggage_price_inbound'][$i]];
					$psg->luggage_index_inbound = (int) $_POST['psg_luggage_price_inbound'][$i];
				}
			}

			$psg->luggage_purchase = unformat_number($_POST['psg_luggage_purchase'][$i]);
			$psg->luggage_purchase_inbound = unformat_number($_POST['psg_luggage_purchase_inbound'][$i]);

			$psg->supplier_id = $_POST['psg_luggage_supplier'][$i];
			$psg->supplier_inbound_id = $_POST['psg_luggage_supplier_inbound'][$i];
			$psg->booking_id = $this->id;
			$psg->add_type = $_POST['psg_add_type'][$i] ?? null;
			$psg->parent_detail_id = $_POST['psg_parent_detail_id'][$i] ?? '';
			$psg->deleted = $_POST['psg_deleted'][$i] ?? 0;

			$psg->luggage_purchase_no_vat = unformat_number($_POST['psg_detail_lug_pur_no_vat'][$i]);
			$psg->vat_luggage_purchase = unformat_number($_POST['psg_detail_lug_pur_vat'][$i]);
			$psg->luggage_purchase_inbound_no_vat = unformat_number($_POST['psg_detail_lug_pur_ib_no_vat'][$i]);
			$psg->vat_luggage_purchase_inbound = unformat_number($_POST['psg_detail_lug_pur_ib_vat'][$i]);

			if ((int) $psg->deleted === 1) {
				if (!empty($psg->id))
					$psg->mark_deleted($psg->id);
				else
					continue;
			} elseif (!empty($psg->name)) {
				$psg->save();
			}
		}

		// Khi booking ở trạng thái Xác nhận, kiểm tra đủ số vé mới chuyển sang trạng thái Xuất vé
		if ((int) $this->booking_status === 3) {
			$booking = new EC_Flight_Bookings;
			$booking->retrieve($this->id);
			$isAllowedUser_acc = is_admin($current_user);

			$is_ticket_exported = $is_ticket_inbound_exported = false;
			$is_ticket_exported_fully = $is_ticket_inbound_exported_fully = true;
			for ($i = 0; $i < $row_count; $i++) {
				// Lượt đi
				$psg_eticket_outbound = $_POST['psg_eticket_outbound'][$i] ?? '';
				if (!empty($psg_eticket_outbound)) {
					$is_ticket_exported = true;
				} else {
					$is_ticket_exported_fully = false;
				}

				// Lượt về
				if ($booking->flight_type === '0') {
					$psg_eticket_inbound = $_POST['psg_eticket_inbound'][$i] ?? '';
					if (!empty($psg_eticket_inbound)) {
						$is_ticket_inbound_exported = true;
					} else {
						$is_ticket_inbound_exported_fully = false;
					}
				}
			}
			if ($booking->flight_type === '1') {
				$is_ticket_inbound_exported = false;
				$is_ticket_inbound_exported_fully = false;
			}

			// Cập nhật thông tin xuất vé lượt đi
			$booking->is_ticket_exported = $is_ticket_exported;
			if ($is_ticket_exported) {
				if (empty($booking->date_ticket_issue))
					$booking->date_ticket_issue = date("Y-m-d");
				if ($isAllowedUser_acc && isset($_POST['date_ticket_issue']) && !empty($_POST['date_ticket_issue']))
					$booking->date_ticket_issue = $_POST['date_ticket_issue'];
			} else {
				$booking->date_ticket_issue = '';
			}

			// Cập nhật thông tin xuất vé lượt về
			$booking->is_ticket_inbound_exported = $is_ticket_inbound_exported;
			if ($is_ticket_inbound_exported) {
				if (empty($booking->date_ticket_inbound_issue))
					$booking->date_ticket_inbound_issue = date("Y-m-d");
				if ($isAllowedUser_acc && isset($_POST['date_ticket_inbound_issue']) && !empty($_POST['date_ticket_inbound_issue']))
					$booking->date_ticket_inbound_issue = $_POST['date_ticket_inbound_issue'];
			} else {
				$booking->date_ticket_inbound_issue = '';
			}

			// Cập nhật tình trạng
			if (
				($booking->flight_type === '1' && $is_ticket_exported_fully)
				|| ($booking->flight_type === '0' && $is_ticket_exported_fully && $is_ticket_inbound_exported_fully)
			) {
				$booking->booking_status = '7';
			}

			$booking->save2();
		}
	}
}
