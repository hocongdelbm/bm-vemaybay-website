<?php
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
	public $country;
	public $city;
	public $airline;
	public $airline_inbound;
	// public $has_voucher;
	// public $voucher_id;
	// public $voucher;
	public $shipping_address;
	public $agent_id;
	public $total_bought_amount;
	public $total_bought_price;
	public $invoice_require;
	public $date_ticket_issue;
	public $date_ticket_inbound_issue;
	public $nganluong_info;
	public $ghichuthangthua;
	public $delivery_man_id;
	public $delivery_man;
	public $total_qty;
	public $total_amount;
	public $ip_address;
	public $point_step = 50;

	public function bean_implements($interface)
	{
		switch ($interface) {
			case 'ACL':
				return true;
		}

		return false;
	}


	/*==================== CUSTOM ====================*/
	function save($check_notify = FALSE)
	{
		global $current_user, $app_list_strings, $db;

		// Set up current user for use new API
		if (is_null($current_user->id) || empty($current_user->id)) $current_user = BeanFactory::getBean('Users', $this->created_by);

		// Disable mass update
		if (
			!empty($_POST['massupdate']) && $_POST['massupdate'] == 'true'
			&& !is_admin($current_user)
			&& $current_user->title != 'KeToan' && $current_user->title != 'QuanLy'
		) {
			return false;
		}

		// Set name of booking
		if (empty($this->name)) {
			if (isset($current_user->agent_prefix) && !empty($current_user->agent_prefix)) $prefix = $current_user->agent_prefix;
			else $prefix = 'BK';

			// If duplicate save
			if (isset($_POST['duplicateSave']) && $_POST['duplicateSave'] == 'true' && isset($_POST['booking_prev_name']) && !empty($_POST['booking_prev_name'])) {
				$prefix = substr($_POST['booking_prev_name'], 0, 2);
			}

			$this->name = $prefix . $this->generate_booking_name();
		} else {
			// Edit name of booking
			// Lấy booking_name hiện tại từ db
			$sql_get_current_name 	= 'SELECT name FROM ec_flight_bookings WHERE id = "' . $this->id . '"';
			$result_current_name 	= $db->query($sql_get_current_name);
			$row_current_name 		= $db->fetchByAssoc($result_current_name);
			$current_booking_name 	= $row_current_name['name'];

			if ($this->name != $current_booking_name) {
				// kiểm tra BOOKING_NAME có trùng với Booking nào khác không?
				$sql_bkname = 'SELECT count(*) as count_booking
							FROM ec_flight_bookings bk
							WHERE bk.name = "' . trim($this->name) . '"';

				$count_bkname = $db->getOne($sql_bkname);

				if ($count_bkname) {
					// TRÙNG
					header('Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=' . urlencode('Tên booking bị trùng!'));
					die();
				} else {
					// UPDATE NEW BOOKING_NAME 
					$sql_update = 'UPDATE ec_flight_bookings SET name = "' . trim($this->name) . '" WHERE id = "' . $this->id . '"';
					$db->query($sql_update);
				}
			}
		}

		// Convert to new prefix for number with 11 digits
		$this->phone = preg_replace('/\D/', '', $this->phone);
		$phone_prefix = substr($this->phone, 0, 4);
		if (strlen($this->phone) == 11 && in_array($phone_prefix, array_keys($app_list_strings['mobile_phone_new_prefix_convert_list']))) {
			$phone_body = substr($this->phone, 4);
			$this->phone = $app_list_strings['mobile_phone_new_prefix_convert_list'][$phone_prefix] . $phone_body;
		}

		$this->contact_name = ucwords(strtolower(myRemoveUnicodeChars(trim(stripslashes($this->contact_name)))));
		$this->email = strtolower(myRemoveUnicodeChars(trim(stripslashes($this->email))));

		// Fix phí hành lý
		if (isset($_POST['luggage_fee']) && $_POST['luggage_fee'] > 10) $this->luggage_fee = $_POST['luggage_fee'];
		elseif ($this->luggage_fee < 10) $this->luggage_fee = 0;

		// Giao cho
		if (isset($_POST['assigned_user_id']) && !empty($_POST['assigned_user_id'])) { // Chỉnh sửa
			$this->assigned_user_id = $_POST['assigned_user_id'];
		} else if (empty($this->assigned_user_id)) {
			$this->assigned_user_id = $current_user->id;
		}

		// Ngày xuất vé
		$this->date_ticket_issue = $this->is_ticket_exported ? $this->date_ticket_issue : '';

		// Lý do thắng thua
		$this->description = (isset($this->ghichuthangthua) && !empty($this->ghichuthangthua) && $this->booking_status == '4') ? $this->ghichuthangthua : $this->description;

		// // Kiểm tra số tiền giảm giá nếu có voucher
		// if (!empty($this->voucher_id)) {
		// 	$this->discount_amount = $this->checkDiscount($this->voucher_id);
		// }
		if (isset($_POST['is_paid'])) {
			$this->is_paid = $_POST['is_paid'];
		}

		// HÀNH TRÌNH TRONG BẢNG EC_CUSTOMER
		$journey = '';
		if (strpos($this->city, '-')) {
			$journey = $this->city;
			$airport_arr = array_merge($app_list_strings['domestic_airport_list'], $app_list_strings['southeast_asia_airport_list'], $app_list_strings['northeast_asia_airport_list'], $app_list_strings['europe_airport_list'], $app_list_strings['americas_airport_list'], $app_list_strings['australia_airport_list'], $app_list_strings['africa_airport_list']);
			$this->city = $airport_arr[substr($this->city, 0, 3)];
		} else {
			$this->city = ucwords(strtolower(trim(stripslashes($this->city))));
		}

		parent::save($check_notify);

		// Lưu thông tin hoá đơn
		$this->saveInvoiceInf($_POST, $this->id);

		// Begin save working process for delivery man
		if (isset($this->delivery_man_id) && !empty($this->delivery_man_id) && $this->fetched_row['delivery_man_id'] != $this->delivery_man_id) {
			myRemoveWorkingProcess($this->module_dir, $this->id, 'ticket_delivery');
			$work = new EC_Working_Process();
			$work->id = '';
			$work->name = $this->name;
			$work->description = trim($this->delivery_man);
			$work->parent_type = $this->module_dir;
			$work->parent_id = $this->id;
			$work->assigned_user_id = $this->delivery_man_id;
			$work->ticket_delivery = 1;
			$work->save();
		} else if (empty($this->delivery_man_id)) {
			myRemoveWorkingProcess($this->module_dir, $this->id, 'ticket_delivery');
		}
		// End save working process for delivery man

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
			$this->saveLinePassengers();
		}

		// Change flight time
		if (isset($_POST['save_change_flight'])) {
			$this->saveChangeFlightTime();
		}

		// LƯU THÔNG TIN KHÁCH HÀNG
		// $this->saveInforCustomer($journey);
	}

	function save2($check_notify = FALSE)
	{
		parent::save($check_notify);
	}

	// Save booking from webservice
	function save_from_webservice($check_notify = FALSE)
	{
		parent::save($check_notify);
	}

	// Generate booking random string
	function generate_booking_name()
	{
		// New 10/07/2023
		$booking_name = '';
		$booking_name .= date('y') . date('m') . date('d');

		$qty_booking = dechex($this->get_number_of_bookings() + 1);
		if (strlen($qty_booking) < 2) $qty_booking = '0' . $qty_booking;
		$booking_name .= strrev($qty_booking);

		return strtoupper($booking_name);

		// Old
		// return strtoupper(substr(sha1(microtime()), rand(0, 31), 8));
	}

	// Get number of bookings
	function get_number_of_bookings()
	{
		$count = 0;
		$sql = "SELECT COUNT(id)
				FROM ec_flight_bookings
				WHERE date_entered > '" . date('Y-m-d H:i:s', strtotime(date('Y-m-d 16:59:59')) - 86400) . "' "; // giờ sugarcrm lệch 7h so với giờ server

		$count = $this->db->getOne($sql);
		return $count;
		// return str_pad($rowcount + 1, 4, '0', STR_PAD_LEFT);
	}

	function saveLineItineraries()
	{
		global $current_user;
		$row_count = count($_POST['iti_airline_code']);

		for ($i = 0; $i < $row_count; $i++) {
			$iti = new EC_Booking_Itineraries();
			if (!is_null($_POST['iti_detail_id'][$i]) && !empty($_POST['iti_detail_id'][$i])) {
				$iti->retrieve($_POST['iti_detail_id'][$i]);
			}

			$iti->name 		= $_POST['iti_is_layover'][$i] ? 'layover' : 'route';
			$iti->airline_code 	= strtoupper(myRemoveUnicodeChars(trim(stripslashes($_POST['iti_airline_code'][$i]))));
			$iti->flight_number = strtoupper(myRemoveUnicodeChars(trim(stripslashes($_POST['iti_flight_number'][$i]))));
			$iti->ticket_class 	= trim(stripslashes($_POST['iti_ticket_class'][$i]));
			$iti->departure 	= strtoupper(myRemoveUnicodeChars(trim(stripslashes($_POST['iti_departure'][$i]))));
			$iti->arrival 		= strtoupper(myRemoveUnicodeChars(trim(stripslashes($_POST['iti_arrival'][$i]))));

			// Departure date
			if (trim($_POST['iti_departure_date'][$i]) != '') {
				$iti->departure_date = date('Y-m-d', strtotime($_POST['iti_departure_date'][$i])) . ' ' . $_POST['iti_departure_h'][$i] . ':' . $_POST['iti_departure_m'][$i] . ':00';
			} else $iti->departure_date = '';

			// Arrival date
			if (trim($_POST['iti_arrival_date'][$i]) != '') {
				$iti->arrival_date = date('Y-m-d', strtotime($_POST['iti_arrival_date'][$i])) . ' ' . $_POST['iti_arrival_h'][$i] . ':' . $_POST['iti_arrival_m'][$i] . ':00';
			} else $iti->arrival_date = '';

			// Time limit
			if (trim($_POST['iti_time_limit_date'][$i]) != '') {
				$iti->time_limit = date('Y-m-d', strtotime($_POST['iti_time_limit_date'][$i])) . ' ' . $_POST['iti_time_limit_h'][$i] . ':' . $_POST['iti_time_limit_m'][$i] . ':00';
			} else $iti->time_limit = '';

			$iti->base_price 		= unformat_number($_POST['iti_base_price'][$i] ?? 0);
			$iti->is_layover 		= $_POST['iti_is_layover'][$i];
			$iti->booking_id 		= $this->id;
			$iti->description 		= $_POST['iti_description'][$i] ?? '';
			$iti->direction 		= $_POST['iti_direction'][$i] ?? '';
			$iti->add_type 			= $_POST['iti_add_type'][$i] ?? 0;
			$iti->parent_detail_id 	= $_POST['iti_parent_detail_id'][$i] ?? null;
			$iti->deleted 			= $_POST['iti_deleted'][$i] ?? 0;

			if ($iti->deleted == 1) {
				$iti->mark_deleted($iti->id);
			} else if ($iti->name != '' && $iti->departure != '') {
				$iti->save();
			}
		}
	}

	function saveLineDetails()
	{
		global $app_list_strings;

		$row_count = count($_POST['bkd_quantity']);
		$total_bought_amount = 0;

		for ($i = 0; $i < $row_count; $i++) {
			$bkd = new EC_Booking_Details();
			if (!empty($_POST['bkd_detail_id'][$i])) {
				$bkd->retrieve($_POST['bkd_detail_id'][$i]);
			}

			$bkd->name 				= $app_list_strings['passenger_type_list'][$_POST['bkd_passenger_type'][$i]];
			$bkd->passenger_type 	= $_POST['bkd_passenger_type'][$i];
			$bkd->quantity 			= unformat_number($_POST['bkd_quantity'][$i]);
			$bkd->unit_price 		= unformat_number($_POST['bkd_unit_price'][$i]);
			$bkd->tax_and_fee 		= unformat_number($_POST['bkd_tax_and_fee'][$i]);
			$bkd->airport_fee 		= unformat_number($_POST['bkd_airport_fee'][$i]);
			$bkd->admin_fee   		= unformat_number($_POST['bkd_admin_fee'][$i]);
			$bkd->service_fee 		= unformat_number($_POST['bkd_service_fee'][$i]);
			$bkd->total_price 		= unformat_number($_POST['bkd_total_price'][$i]);

			if (isset($_POST['edit_detail'])) {
				// Giá mua = Thành tiền - (Phí DV x Số lượng) - Chiết khấu + Phí xuất vé
				$bkd->total_bought_price = unformat_number($_POST['bkd_total_price'][$i]) - (unformat_number($_POST['bkd_service_fee'][$i]) * unformat_number($_POST['bkd_quantity'][$i])) - unformat_number($_POST['bkd_supplier_discount'][$i]) + unformat_number($_POST['bkd_supplier_ticketing_fee'][$i]);
			} else {
				$bkd->total_bought_price = unformat_number($_POST['bkd_total_bought_price'][$i]);
			}
			$bkd->supplier_discount 	= unformat_number($_POST['bkd_supplier_discount'][$i]);
			$bkd->fee_bought 			= unformat_number($_POST['bkd_supplier_ticketing_fee'][$i]);
			$bkd->supplier_id 			= $_POST['bkd_supplier_id'][$i];
			$bkd->booking_id 			= $this->id;
			$bkd->direction 			= $_POST['bkd_direction'][$i];
			$bkd->is_active 			= isset($_POST['bkd_is_active'][$i]) ? $_POST['bkd_is_active'][$i] : 0;
			$bkd->deleted 				= $_POST['bkd_deleted'][$i];
			$bkd->vat_admin 			= isset($_POST['bkd_vat_admin'][$i]) ? $_POST['bkd_vat_admin'][$i] : 0;
			$bkd->admin_fee_no_vat 		= isset($_POST['bkd_admin_fee_no_vat'][$i]) ? $_POST['bkd_admin_fee_no_vat'][$i] : 0;

			if ($bkd->deleted == 1) {
				$bkd->mark_deleted($bkd->id);
			} else if ($bkd->quantity != '' && $bkd->quantity > 0 && $bkd->total_price > 0) {
				$bkd->save();
			}

			$total_bought_amount += $bkd->total_bought_price;
		}

		// Cập nhật lại giá mua
		if (isset($_POST['edit_detail'])) {
			$sql = 'UPDATE ec_flight_bookings 
					SET total_bought_amount = ' . $total_bought_amount . ' 
					WHERE id = "' . $this->id . '"';
			$this->db->query($sql);
		}
	}

	function saveLinePassengers()
	{
		global $app_list_strings;

		$bk = new EC_Flight_Bookings;
		$bk->retrieve($this->id);

		$row_count = count($_POST['psg_id']);
		for ($i = 0; $i < $row_count; $i++) {
			$psg = new EC_Booking_Passengers();
			if (!empty($_POST['psg_id'][$i])) $psg->retrieve($_POST['psg_id'][$i]);
			else $psg->id = '';

			$psg->type 		 	= $_POST['psg_traveller_type'][$i];
			$psg->salutation 	= $_POST['psg_salutation'][$i];
			$psg->name 		 	= strtoupper(myRemoveUnicodeChars(trim(stripslashes($_POST['psg_full_name'][$i]))));
			if (isset($_POST['psg_birthday'][$i]) && strtotime($_POST['psg_birthday'][$i]) !== false) {
				$psg->birthday = date('d-m-Y', strtotime($_POST['psg_birthday'][$i]));
			}
			$psg->eticket_outbound 	= trim(stripslashes($_POST['psg_eticket_outbound'][$i]));
			$psg->eticket_inbound 	= trim(stripslashes($_POST['psg_eticket_inbound'][$i]));
			$psg->eluggage_outbound 	= trim(stripslashes($_POST['psg_eluggage_outbound'][$i]));
			$psg->eluggage_inbound 	= trim(stripslashes($_POST['psg_eluggage_inbound'][$i]));
			$psg->pnr_outbound 		= trim(stripslashes($_POST['psg_pnr_outbound'][$i]));
			$psg->pnr_inbound 		= trim(stripslashes($_POST['psg_pnr_inbound'][$i]));

			if (isset($_POST['psg_luggage_price'][$i])) {
				if (((!isset($_POST['psg_luggage_ob_ind']) || empty($_POST['psg_luggage_ob_ind'][$i])) && $this->airline != 'VJA' && $this->airline != 'VJ') || ($this->airline != 'VJA' && $this->airline != 'VJ')) {
					$psg->luggage_price = unformat_number($_POST['psg_luggage_price'][$i]);
					$psg->luggage_index_outbound = '';
				} else {
					// BK đặt từ ngày 21-11-2022, VJA có giá mới
					if (strtotime($bk->date_entered) >= strtotime('2022-11-21')) {
						$psg->luggage_price = $app_list_strings['vietjet_index_price_list2'][(int)$_POST['psg_luggage_price'][$i]];
					} else {
						$psg->luggage_price = $app_list_strings['vietjet_index_price_list'][(int)$_POST['psg_luggage_price'][$i]];
					}
					$psg->luggage_index_outbound = (int)$_POST['psg_luggage_price'][$i];
				}
			}

			if (isset($_POST['psg_luggage_price_inbound'][$i])) {
				if (((!isset($_POST['psg_luggage_ib_ind']) || empty($_POST['psg_luggage_ib_ind'][$i])) && $this->airline != 'VJA' && $this->airline != 'VJ') || ($this->airline_inbound != 'VJA' && $this->airline_inbound != 'VJ')) {
					$psg->luggage_price_inbound = unformat_number($_POST['psg_luggage_price_inbound'][$i]);
					$psg->luggage_index_inbound = '';
				} else {
					// BK đặt từ ngày 21-11-2022, VJA có giá mới
					if (strtotime($bk->date_entered) >= strtotime('2022-11-21')) {
						$psg->luggage_price_inbound = $app_list_strings['vietjet_index_price_list2'][(int)$_POST['psg_luggage_price_inbound'][$i]];
					} else {
						$psg->luggage_price_inbound = $app_list_strings['vietjet_index_price_list'][(int)$_POST['psg_luggage_price_inbound'][$i]];
					}
					$psg->luggage_index_inbound = (int)$_POST['psg_luggage_price_inbound'][$i];
				}
			}

			$psg->luggage_purchase 				= unformat_number($_POST['psg_luggage_purchase'][$i]);
			$psg->luggage_purchase_inbound 		= unformat_number($_POST['psg_luggage_purchase_inbound'][$i]);
			$psg->supplier_id 					= $_POST['psg_luggage_supplier'][$i];
			$psg->supplier_inbound_id 			= $_POST['psg_luggage_supplier_inbound'][$i];
			$psg->booking_id 					= $this->id;
			$psg->add_type 						= $_POST['psg_add_type'][$i];
			$psg->parent_detail_id 				= $_POST['psg_parent_detail_id'][$i];
			$psg->deleted 						= $_POST['psg_deleted'][$i] ?? '0';
			$psg->luggage_purchase_no_vat 		= unformat_number($_POST['psg_detail_lug_pur_no_vat'][$i]);
			$psg->vat_luggage_purchase 			= unformat_number($_POST['psg_detail_lug_pur_vat'][$i]);
			$psg->luggage_purchase_inbound_no_vat = unformat_number($_POST['psg_detail_lug_pur_ib_no_vat'][$i]);
			$psg->vat_luggage_purchase_inbound 	= unformat_number($_POST['psg_detail_lug_pur_ib_vat'][$i]);
			$psg->cic 							= trim($_POST['psg_cic'][$i]) ?? '';
			$psg->passport_number 				= trim($_POST['psg_passport_number'][$i]) ?? '';

			if ($psg->deleted == 1) {
				if (!empty($psg->id)) $psg->mark_deleted($psg->id);
				else continue;
			} elseif (!empty($psg->name)) {
				$psg->save();
			}
		}

		// Khi booking ở trạng thái xác nhận
		// Kiểm tra nếu có dù chỉ 1 số vé cũng chuyển sang trạng thái đã xuất vé
		// Sau khi chuyển sang trạng thái đã xuất vé thì cập nhật trạng thái trong bảng ec_customer - info_data
		if ($this->booking_status == '3') {
			$booking = new EC_Flight_Bookings;
			$booking->retrieve($this->id);

			for ($i = 0; $i < $row_count; $i++) {
				if (!empty($_POST['psg_eticket_outbound'][$i])) {
					$booking->is_ticket_exported = '1';
					if (empty($booking->date_ticket_issue)) {

						// $booking->date_ticket_issue = date('d-m-Y');
						$now = date('d-m-Y H:i:s');
						$booking->date_ticket_issue = date("d-m-Y", strtotime('+7 hours', strtotime($now)));
					} else {
						if (isAllowedUser()) {
							$booking->date_ticket_inbound_issue = $_POST['date_ticket_inbound_issue'];
						}
					}
					break;
				} else {
					$booking->is_ticket_exported = '0';
					$booking->date_ticket_issue = '';
				}
			}

			for ($i = 0; $i < $row_count; $i++) {
				if (!empty($_POST['psg_eticket_inbound'][$i])) {
					$booking->is_ticket_inbound_exported = '1';
					if (empty($booking->date_ticket_inbound_issue)) {
						// $booking->date_ticket_inbound_issue = date('d-m-Y');
						$now = date('d-m-Y H:i:s');
						$booking->date_ticket_inbound_issue = date("d-m-Y", strtotime('+7 hours', strtotime($now)));
					} else {
						if (isAllowedUser()) {
							$booking->date_ticket_inbound_issue = $_POST['date_ticket_inbound_issue'];
						}
					}
					break;
				} else {
					$booking->is_ticket_inbound_exported = '0';
					$booking->date_ticket_inbound_issue = '';
				}
			}

			if (!empty($booking->is_ticket_exported) || !empty($booking->is_ticket_inbound_exported)) {
				$booking->booking_status = '7';
			}
			$booking->save2();

			// Cập nhật trạng thái trong ec_customer
			// UpdateInforBookingOfCustomer($this->id);
		}
	}

	// // Kiểm tra giảm giá
	// function checkDiscount($voucher_id)
	// {
	// 	$sql = 'SELECT reduce_amount 
	// 			FROM ec_vouchers
	// 			WHERE id = "' . $voucher_id . '" AND deleted = 0';
	// 	$discount = $this->db->getOne($sql);
	// 	if ($discount > $this->discount_amount) {
	// 		$this->discount_amount = $discount;
	// 	}
	// 	return $this->discount_amount;
	// }

	// Lưu thay đổi Ngày bay / Hành trình / Thông tin hành khách / Hành lý / Số vé / Code vé
	function saveChangeFlightTime()
	{
		global $current_user, $app_list_strings;
		
		// ======== THAY ĐỔI THÔNG TIN HÀNH KHÁCH =========
		// lấy stt của các lần thay đổi thông tin hành khách trước
		$sql_pass_order = '
			SELECT MAX(IFNULL(go_with, 0)) FROM ec_booking_passengers 
			WHERE deleted = 0 AND booking_id = "' . $_POST['booking_id'] . '"';

		$pass_order = $this->db->getOne($sql_pass_order);

		// lưu thông tin hành khách
		$pass_replace = array();
		for ($i = 0; $i < count($_POST['pass_id']); $i++) {
			$pass = new EC_Booking_Passengers;
			$pass->retrieve($_POST['pass_id'][$i]);

			$create_new = 0;
			if (
				isset($_POST['pass_name']) && $pass->name != $_POST['pass_name'][$i]
				|| isset($_POST['pass_birthday' . $i]) && $pass->birthday != $_POST['pass_birthday' . $i]
				|| isset($_POST['pass_eticket_outbound']) && $pass->eticket_outbound != $_POST['pass_eticket_outbound'][$i]
				|| isset($_POST['pass_eticket_inbound']) && $pass->eticket_inbound != $_POST['pass_eticket_inbound'][$i]
				|| isset($_POST['pass_pnr_outbound']) && $pass->pnr_outbound != $_POST['pass_pnr_outbound'][$i]
				|| isset($_POST['pass_pnr_inbound']) && $pass->pnr_inbound != $_POST['pass_pnr_inbound'][$i]
				|| !isset($_POST['pass_luggage_ob_ind']) && $pass->luggage_price != $_POST['pass_luggage_ob'][$i]
				|| isset($_POST['pass_luggage_ob_ind']) && $pass->luggage_index_outbound != $_POST['pass_luggage_ob_ind'][$i]
				|| !isset($_POST['pass_luggage_ib_ind']) && $pass->luggage_price_inbound != $_POST['pass_luggage_ib'][$i]
				|| isset($_POST['pass_luggage_ib_ind']) && $pass->luggage_index_inbound != $_POST['pass_luggage_ib_ind'][$i]
			) {
				if (!isset($_POST['edit_pass_id']))
					$create_new = 1;
			}

			// tạo mới hành khách
			if ($create_new) {
				if (!empty($_POST['pass_name'][$i])) {
					$pass_n 					= new EC_Booking_Passengers;

					$pass_n->name 				= $_POST['pass_name'][$i];
					$pass_n->salutation 		= $_POST['pass_salutation'][$i];
					$pass_n->birthday 			= $_POST['pass_birthday' . $i];
					$pass_n->type 				= $pass->type;
					$pass_n->booking_id 		= $pass->booking_id;
					$pass_n->eticket_outbound 	= $_POST['pass_eticket_outbound'][$i];
					$pass_n->eticket_inbound 	= $_POST['pass_eticket_inbound'][$i];
					$pass_n->eluggage_outbound 	= $_POST['pass_eluggage_outbound'][$i];
					$pass_n->eluggage_inbound 	= $_POST['pass_eluggage_inbound'][$i];
					$pass_n->pnr_outbound 		= $_POST['pass_pnr_outbound'][$i];
					$pass_n->pnr_inbound 		= $_POST['pass_pnr_inbound'][$i];
					$pass_n->direction 			= $pass->direction;

					if (isset($_POST['pass_luggage_ob'][$i])) {
						if (!isset($_POST['pass_luggage_ob_ind']) || empty($_POST['pass_luggage_ob_ind'][$i])) {
							$pass_n->luggage_price = (int)$_POST['pass_luggage_ob'][$i];
						} else {
							$luggage_price_arr 				= generateLuggage($_POST['bk_date_entered'], $_POST['pass_airline_ob'], $_POST['pass_ticket_class_ob'][$i], (int)$_POST['pass_luggage_ob'][$i]);
							$pass_n->luggage_price 			= $luggage_price_arr[(int)$_POST['pass_luggage_ob'][$i]];
							$pass_n->luggage_index_outbound = (int)$_POST['pass_luggage_ob'][$i];
						}
					}
					if (isset($_POST['pass_luggage_ib'][$i])) {
						if (!isset($_POST['pass_luggage_ib_ind']) || empty($_POST['pass_luggage_ib_ind'][$i])) {
							$pass_n->luggage_price_inbound = (int)$_POST['pass_luggage_ib'][$i];
						} else {
							$luggage_price_ib_arr 			= generateLuggage($_POST['bk_date_entered'], $_POST['pass_airline_ib'], $_POST['pass_ticket_class_ib'][$i], (int)$_POST['pass_luggage_ib'][$i]);
							$pass_n->luggage_price_inbound 	= $luggage_price_ib_arr[(int)$_POST['pass_luggage_ib'][$i]];
							$pass_n->luggage_index_inbound 	= (int)$_POST['pass_luggage_ib'][$i];
						}
					}
					$pass_n->luggage_purchase 			= $_POST['bought_price_outbound'][$i];
					$pass_n->luggage_purchase_inbound 		= $_POST['bought_price_inbound'][$i];
					$pass_n->supplier_id 				= $_POST['supplier_outbound'][$i];
					$pass_n->supplier_inbound_id 			= $_POST['supplier_inbound'][$i];
					$pass_n->add_type 					= 2;
					$pass_n->parent_detail_id 			= $pass->id;
					$pass_n->go_with 					= ($pass_order + 1);
					$pass_n->save();

					$pass_replace[$pass->id] = $pass_n->id;
				}
			} else {

				if (!empty($_POST['pass_birthday' . $i])) {
					$birthday = $_POST['pass_birthday' . $i];
				} else $birthday = '';

				$pass = new EC_Booking_Passengers;
				$pass->retrieve($_POST['pass_id'][$i]);
				$pass->birthday 			= $birthday;
				$pass->salutation 			= $_POST['pass_salutation'][$i];
				$pass->name 				= $_POST['pass_name'][$i];
				$pass->eticket_outbound 	= $_POST['pass_eticket_outbound'][$i];
				$pass->eticket_inbound 		= $_POST['pass_eticket_inbound'][$i];
				$pass->eluggage_outbound 	= $_POST['pass_eluggage_outbound'][$i];
				$pass->eluggage_inbound 	= $_POST['pass_eluggage_inbound'][$i];
				$pass->pnr_outbound 		= $_POST['pass_pnr_outbound'][$i];
				$pass->pnr_inbound 			= $_POST['pass_pnr_inbound'][$i];

				$pass->luggage_purchase 	 	= $_POST['bought_price_outbound'][$i];
				$pass->luggage_purchase_inbound = $_POST['bought_price_inbound'][$i];
				$pass->supplier_id 			 	= $_POST['supplier_outbound'][$i];
				$pass->supplier_inbound_id 	 	= $_POST['supplier_inbound'][$i];

				if (isset($_POST['pass_luggage_ob'][$i])) {
					if (!isset($_POST['pass_luggage_ob_ind']) || empty($_POST['pass_luggage_ob_ind'][$i])) {
						$pass->luggage_price = (int)$_POST['pass_luggage_ob'][$i];
					} else {
						$pass->luggage_price = generateLuggage($_POST['bk_date_entered'], $_POST['pass_airline_ob'], $_POST['pass_ticket_class_ob'][$i], (int)$_POST['pass_luggage_ob'][$i]);
						$pass->luggage_index_outbound = (int)$_POST['pass_luggage_ob'][$i];
					}
				}

				if (isset($_POST['pass_luggage_ib'][$i])) {
					if (!isset($_POST['pass_luggage_ib_ind']) || empty($_POST['pass_luggage_ib_ind'][$i])) {
						$pass->luggage_price_inbound = (int)$_POST['pass_luggage_ib'][$i];
					} else {
						$pass->luggage_price_inbound = generateLuggage($_POST['bk_date_entered'], $_POST['pass_airline_ib'], $_POST['pass_ticket_class_ib'][$i], (int)$_POST['pass_luggage_ib'][$i]);
						$pass->luggage_index_inbound = (int)$_POST['pass_luggage_ib'][$i];
					}
				}

				$pass->luggage_purchase 			= str_replace(array(',', '.'), '', $_POST['bought_price_outbound'][$i]);
				$pass->luggage_purchase_inbound 	= str_replace(array(',', '.'), '', $_POST['bought_price_inbound'][$i]);
				$pass->save();
			}
		}

		if($current_user->user_name == 'hungnh'){
			// die;
		}

		if (!empty($_POST['applied_passenger'])) {
			$old_pass_arr = array_keys($pass_replace);
			for ($k = 0; $k < count($old_pass_arr); $k++) {
				$idx = array_search($old_pass_arr, $_POST['applied_passenger']);
				$_POST['applied_passenger'][$idx] = $pass_replace[$old_pass_arr[$k]];
			}
		}

		// ========== THAY ĐỔI HÀNH TRÌNH =============

		// lấy stt của các lần thay đổi hành trình trước
		$sql_iti_order = 'SELECT MAX(IFNULL(sabre_logs, 0)) FROM ec_booking_itineraries 
						WHERE deleted = 0 AND booking_id = "' . $_POST['booking_id'] . '"';

		$iti_order = $this->db->getOne($sql_iti_order); //0, 1, 2

		// nếu là lưu mới thông tin hành trình
		if (!isset($_POST['iti_id']) || empty($_POST['iti_id']) || is_null($_POST['iti_id'])) {

			// lưu lượt đi của hành trình
			if (
				!empty($_POST['flight_number0'])
				&& !empty($_POST['ticket_class0'])
				&& !empty($_POST['departure0'])
				&& !empty($_POST['arrival0'])
				&& !empty($_POST['departure_date0'])
				&& ((int)$_POST['departure_hour0'] >= 0 && (int)$_POST['departure_hour0'] < 24)
				&& ((int)$_POST['departure_minute0'] >= 0 && (int)$_POST['departure_minute0'] < 60)
				&& !empty($_POST['arrival_date0'])
				&& ((int)$_POST['arrival_hour0'] >= 0 && (int)$_POST['arrival_hour0'] < 24)
				&& ((int)$_POST['arrival_minute0'] >= 0 && (int)$_POST['arrival_minute0'] < 60)
				// phần thông tin B
				&& ((isset($_POST['applied_all']) && $_POST['applied_all'] == 'on') || count($_POST['applied_passenger']) > 0)
			) {

				if ($_POST['applied_all'] == 'on') {
					for ($p = 0; $p < count($_POST['pass_id']); $p++) {
						if (in_array($_POST['pass_id'][$p], array_keys($pass_replace))) {
							$pass_id = $pass_replace[$_POST['pass_id'][$p]];
						} else $pass_id = $_POST['pass_id'][$p];
						// lượt đi
						$this->saveFlightItinerary($_POST['pass_name'][$p], 0, $_POST, $pass_id, $iti_order);
					}
				} else {

					for ($p = 0; $p < count($_POST['pass_id']); $p++) {
						// lượt đi
						if (in_array($_POST['pass_id'][$p], array_keys($pass_replace))) {
							$pass_id = $pass_replace[$_POST['pass_id'][$p]];
						} else $pass_id = $_POST['pass_id'][$p];
						$this->saveFlightItinerary($_POST['pass_name'][$p], 0, $_POST, $pass_id, $iti_order);
					}
				}
			} else {

				// nếu không có thay đổi hành trình, nhưng thay đổi hành khách,
				// tìm lại lần thay đổi hành trình mới nhất nếu có
				// đổi hành khách áp dụng
				foreach ($pass_replace as $old_pass_id => $new_pass_id) {
					$sql = 'SELECT id
							FROM ec_booking_itineraries
							WHERE deleted = 0 AND add_type = 3
							AND assigned_user_id = "' . $old_pass_id . '"
							AND direction = 0
							ORDER BY date_entered DESC
							LIMIT 1';
					$res = $this->db->query($sql);
					$row = $this->db->fetchByAssoc($res);

					if (!empty($row['id'])) {
						$new_iti = new EC_Booking_Itineraries;
						$new_iti->retrieve($row['id']);
						$new_iti->id = '';
						$new_iti->date_entered = NULL;

						$new_iti->departure_date = date("Y-m-d H:i:s", strtotime('-7 hours', strtotime($new_iti->departure_date)));
						$new_iti->arrival_date = date("Y-m-d H:i:s", strtotime('-7 hours', strtotime($new_iti->arrival_date)));

						$new_iti->assigned_user_id = $new_pass_id;
						$new_iti->save();

						$old_iti = new EC_Booking_Itineraries;
						$old_iti->retrieve($row['id']);

						$old_iti->departure_date = date("Y-m-d H:i:s", strtotime('-7 hours', strtotime($old_iti->departure_date)));
						$old_iti->arrival_date = date("Y-m-d H:i:s", strtotime('-7 hours', strtotime($old_iti->arrival_date)));

						$old_iti->add_type = 1;
						$old_iti->save();
					}
				}
			}

			// lưu lượt về của hành trình
			if (
				!empty($_POST['flight_number1'])
				&& !empty($_POST['ticket_class1'])
				&& !empty($_POST['departure1'])
				&& !empty($_POST['arrival1'])
				&& !empty($_POST['departure_date1'])
				&& ((int)$_POST['departure_hour1'] >= 0 && (int)$_POST['departure_hour1'] < 24)
				&& ((int)$_POST['departure_minute1'] >= 0 && (int)$_POST['departure_minute1'] < 60)
				&& !empty($_POST['arrival_date1'])
				&& ((int)$_POST['arrival_hour1'] >= 0 && (int)$_POST['arrival_hour1'] < 24)
				&& ((int)$_POST['arrival_minute1'] >= 0 && (int)$_POST['arrival_minute1'] < 60)
				// phần thông tin B
				&& ($_POST['applied_all'] == 'on' || count($_POST['applied_passenger']) > 0)
			) {
				if ($_POST['applied_all'] == 'on') {
					for ($p = 0; $p < count($_POST['pass_id']); $p++) {
						if (in_array($_POST['pass_id'][$p], array_keys($pass_replace))) {
							$pass_id = $pass_replace[$_POST['pass_id'][$p]];
						} else $pass_id = $_POST['pass_id'][$p];
						// lượt về
						$this->saveFlightItinerary($_POST['pass_name'][$p], 1, $_POST, $pass_id, $iti_order);
					}
				} else {
					for ($p = 0; $p < count($_POST['pass_id']); $p++) {
						if (in_array($_POST['pass_id'][$p], array_keys($pass_replace))) {
							$pass_id = $pass_replace[$_POST['pass_id'][$p]];
						} else $pass_id = $_POST['pass_id'][$p];
						$this->saveFlightItinerary($_POST['pass_name'][$p], 1, $_POST, $pass_id, $iti_order);
					}
				}
			} else {
				// nếu không có thay đổi hành trình, nhưng thay đổi hành khách,
				// tìm lại lần thay đổi hành trình mới nhất nếu có
				// đổi hành khách áp dụng
				foreach ($pass_replace as $old_pass_id => $new_pass_id) {
					$sql = 'SELECT id
							FROM ec_booking_itineraries
							WHERE deleted = 0 AND add_type = 3
							AND assigned_user_id = "' . $old_pass_id . '"
							AND direction = 1
							ORDER BY date_entered DESC
							LIMIT 1';
					$res = $this->db->query($sql);
					$row = $this->db->fetchByAssoc($res);

					if (!empty($row['id'])) {
						$new_iti = new EC_Booking_Itineraries;
						$new_iti->retrieve($row['id']);
						$new_iti->id = '';
						$new_iti->date_entered = NULL;

						$new_iti->departure_date = date("Y-m-d H:i:s", strtotime('-7 hours', strtotime($new_iti->departure_date)));
						$new_iti->arrival_date = date("Y-m-d H:i:s", strtotime('-7 hours', strtotime($new_iti->arrival_date)));

						$new_iti->assigned_user_id = $new_pass_id;
						$new_iti->save();

						$old_iti = new EC_Booking_Itineraries;
						$old_iti->retrieve($row['id']);

						$old_iti->departure_date = date("Y-m-d H:i:s", strtotime('-7 hours', strtotime($old_iti->departure_date)));
						$old_iti->arrival_date = date("Y-m-d H:i:s", strtotime('-7 hours', strtotime($old_iti->arrival_date)));

						$old_iti->add_type = 1;
						$old_iti->save();
					}
				}
			}
		} else { // nếu là sửa lại thông tin hành trình
			$applied_pass_arr 		= explode(',', $_POST['applied_pass']);
			$applied_pass_name_arr 	= explode(',', $_POST['applied_pass_name']);
			$iti_id_arr 			= explode(',', $_POST['iti_id']);

			for ($t = 0; $t < count($applied_pass_arr); $t++) {
				$_POST['iti_id'] = $iti_id_arr[$t];
				$this->saveFlightItinerary($applied_pass_name_arr[$t], '', $_POST, $applied_pass_arr[$t], '');
			}
		}
	}

	function getAllPassengers($booking_id)
	{
		$sql = 'SELECT id, name FROM ec_booking_passengers 
				WHERE booking_id = "' . $booking_id . '" AND deleted = 0
					AND id NOT IN (
						SELECT parent_detail_id
						FROM ec_booking_passengers
						WHERE booking_id = "' . $booking_id . '" AND add_type = 2 AND deleted = 0
					)';
		$res = $this->db->query($sql);
		$passenger = array();
		while ($row = $this->db->fetchByAssoc($res)) {
			$passenger[] = $row;
		}
		return $passenger;
	}

	function saveFlightItinerary($pass_name, $direction, $post_fields, $pass_id, $iti_order)
	{
		// direction 0 là lượt đi - 1 là lượt về

		if (empty($post_fields['departure_hour' . $direction]))
			$post_fields['departure_hour' . $direction] = '00';
		if (empty($post_fields['departure_minute' . $direction]))
			$post_fields['departure_minute' . $direction] = '00';
		if (empty($post_fields['arrival_hour' . $direction]))
			$post_fields['arrival_hour' . $direction] = '00';
		if (empty($post_fields['arrival_minute' . $direction]))
			$post_fields['arrival_minute' . $direction] = '00';

		// Chỉ dành cho VNA và VNP
		if (empty($post_fields['bk_airline' . $direction])) {
			if ($direction == 1) {
				$airline_code = $post_fields['airline_code_inbound'];
			} else $airline_code = $post_fields['airline_code'];
		} else {
			$airline_code = $post_fields['bk_airline' . $direction];
		}

		$iti = new EC_Booking_Itineraries;
		if (!empty($post_fields['iti_id'])) {
			$iti->retrieve($post_fields['iti_id']);
		} else {
			$iti->direction = $direction;
			$iti->add_type = 3;
			$iti->sabre_logs = ($iti_order + 1);
			$iti->assigned_user_id = $pass_id;
			$iti->name = $pass_name;
		}

		$iti->airline_code 		= $airline_code;
		$iti->flight_number 	= $post_fields['flight_number' . $direction];
		$iti->ticket_class 		= $post_fields['ticket_class' . $direction];
		$iti->departure 		= $post_fields['departure' . $direction];
		$iti->arrival 			= $post_fields['arrival' . $direction];

		$iti->departure_date 	= date('Y-m-d', strtotime($post_fields['departure_date' . $direction])) . ' ' . $post_fields['departure_hour' . $direction] . ':' . $post_fields['departure_minute' . $direction] . ':00';
		$iti->arrival_date 		= date('Y-m-d', strtotime($post_fields['arrival_date' . $direction])) . ' ' . $post_fields['arrival_hour' . $direction] . ':' . $post_fields['arrival_minute' . $direction] . ':00';

		$iti->booking_id 		= $post_fields['booking_id'];
		$iti->save();
	}

	// Lưu thông tin hoá đơn
	function saveInvoiceInf($post_fields, $booking_id)
	{
		if (isset($post_fields['action']) && $post_fields['action'] == 'Save') {
			if (isset($post_fields['iv_account_name'])) {
				$invoice_inf = array(
					'iv_account_name' => $post_fields['iv_account_name'],
					'iv_email' => $post_fields['iv_email'],
					'iv_payment_method' => $post_fields['iv_payment_method'],
					'iv_bank_account' => $post_fields['iv_bank_account'],
					'iv_name_banks' => $post_fields['iv_name_banks']
				);

				$sql = '
					UPDATE ec_flight_bookings 
					SET shipping_address = \'' . preg_replace('/\\\\u([0-9a-z]{4})/', '&#x$1;', json_encode($invoice_inf)) . '\'
					WHERE id = "' . $booking_id . '"';
				$this->db->query($sql);
			}
		}
	}

	// Tính số lượng vé của 1 booking
	// Tổng sl vé trong booking - sl vé hoàn nếu có
	function calculateBookingTicketQty($booking_id)
	{
		$sql = '
			SELECT  
				total_qty
				- IFNULL((
					SELECT COUNT(ct.id) 
					FROM ec_chitiethoanve ct
					INNER JOIN ec_hoanve hv 
					ON ct.hoanve_id = hv.id 
					AND hv.deleted = 0
					AND hv.booking_id = "' . $booking_id . '"
					WHERE ct.deleted = 0
				), 0)
			FROM ec_flight_bookings
			WHERE id = "' . $booking_id . '"
		';
		return $this->db->getOne($sql);
	}

	// // Khi thêm hành lý thì tạo phiếu thu phí hành lý
	// function createReceiptVoucher($total_amount, $supplier1, $sell_price1, $bought_price1, $supplier2 = '', $sell_price2 = '', $bought_price2 = '') {
	// 	$rv = new EC_Receipt_Voucher;
	// 	$rv->amount = $total_amount;
	// 	$rv->receipt_type = $_POST['receipt_type'];
	// 	$rv->com_location_id = $_POST['com_location_id'];
	// 	$rv->tknganhang_id = $_POST['tknganhang_id'];
	// 	$rv->loai_thu = '5';
	// 	$rv->supplier_id = $supplier1;
	// 	$rv->sell_amount = $sell_price1;
	// 	$rv->bought_amount = $bought_price1;
	// 	$rv->supplier2_id = $supplier2;
	// 	$rv->sell_amount2 = $sell_price2;
	// 	$rv->bought_amount2 = $bought_price2;
	// 	$rv->guest_name = $_POST['contact_name'];
	// 	$rv->guest_phone = $_POST['contact_phone'];
	// 	$rv->booking_id = $this->id;
	// 	$rv->description = 'Phiếu thu tự động thu phí thêm hành lý booking ' . $this->name;
	// 	$rv->assigned_user_id = $GLOBALS['current_user']->id;
	// 	$rv->ngayhachtoan = date('Y-m-d H:i:s', strtotime("now") - 7 * 3600);
	// 	$rv->exchange_rate = 0;
	// 	$rv->amount_converted = $total_amount;
	// 	$rv->save();
	// }

	public function saveInforCustomer($journey_from_to)
	{
		global $db;

		// Kiểm tra customer có tồn tại trong bảng ec_customer hay chưa dựa trên thông tin từ bảng ec_flight_bookings
		$sql_check_customer = '
			SELECT IF(COUNT(c.phone) > 0, 1, 0)
			FROM ec_customer c
			WHERE c.phone = "' . $this->phone . '"';

		$count_customer = $db->getOne($sql_check_customer);
		$cus 		 = new EC_Customer;

		if (isset($journey_from_to) && !empty($journey_from_to)) {
			$journey 	= $journey_from_to;
		} else {
			$journey_array = journeyOfBooking($this->id);
			$journey 		= $journey_array["departure"] . ' - ' . $journey_array["arrival"];
		}

		if (!$count_customer) {
			// TẠO KHÁCH HÀNG MỚI
			$cus->name 	 = $this->contact_name;
			$cus->phone  = $this->phone;
			$cus->email  = $this->email;
			$cus->gender = $this->contact_title;
			$cus->type 	 = 'NEW';

			// LƯU THÔNG TIN BOOKING CỦA KHÁCH HÀNG MỚI
			$booking_list = array();
			$booking_list[$this->id] = array(
				'booking_number'			=> $this->name,
				'booking_status' 			=> $this->booking_status,
				'booking_date' 				=> $this->date_entered,
				'booking_quantity' 			=> $this->total_qty,
				'journey'					=> $journey,
				'customer_name' 			=> trim(stripslashes($this->contact_name)),
				'customer_email' 			=> $this->email,
				'customer_price_total' 		=> unformat_number($this->total_amount),
				'customer_price_revenue' 	=> calculateBKTotalAmt($this->id),
				'customer_ip' 				=> $this->ip_address,
			);

			$cus->info_data	= json_encode($booking_list);
			$cus->save();
		} else {
			// get booking_list hiện có của KH đó
			$sql_get_booking_info = '
			SELECT c.info_data
			FROM ec_customer c
			WHERE c.phone = "' . $this->phone . '"';
			$current_booking = $db->getOne($sql_get_booking_info);

			// convert booking_list hiện có thành array
			$current_booking_array 	= json_decode(html_entity_decode($current_booking), true);

			// Khi booking thay đổi thông tin thì tiến hành cập nhật booking này bên bảng ec_customer
			if (isset($current_booking_array[$this->id])) {
				$current_booking_array[$this->id]['booking_status'] 		= $this->booking_status;
				$current_booking_array[$this->id]['booking_quantity'] 		= $this->total_qty;
				$current_booking_array[$this->id]['customer_name'] 		= $this->contact_name;
				$current_booking_array[$this->id]['customer_email'] 		= $this->email;
				$current_booking_array[$this->id]['journey'] 			= $journey;
				$current_booking_array[$this->id]['customer_price_total'] 	= unformat_number($this->total_amount);
				$current_booking_array[$this->id]['customer_price_revenue'] = calculateBKTotalAmt($this->id);
			} else {
				$current_booking_array[$this->id] = array(
					'booking_number'		=> $this->name,
					'booking_status' 		=> $this->booking_status,
					'booking_date' 			=> $this->date_entered,
					'booking_quantity' 		=> $this->total_qty,
					'journey'				=> $journey,
					'customer_name' 		=> trim(stripslashes($this->contact_name)),
					'customer_email' 		=> $this->email,
					'customer_price_total' 	=> unformat_number($this->total_amount),
					'customer_price_revenue' => calculateBKTotalAmt($this->id),
					'customer_ip' 			=> $this->ip_address,
				);
			}

			// Cập nhật field info_data
			$updated_booking_data  = json_encode($current_booking_array);
			$sql_update_booking = '
				UPDATE ec_customer c
				SET c.type = "' . getCustomerType($this->phone) . '" , info_data = \'' . $updated_booking_data . '\'
				WHERE c.phone = "' . $this->phone . '" AND c.deleted = 0';
			$db->query($sql_update_booking);
		}
	}
}
