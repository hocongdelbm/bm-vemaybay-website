<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

class EC_Flight_Bookings extends Basic {
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
	public $shipping_address;
	public $agent_id;
	public $agent_name;
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
	public $account_id;
	public $account_name;
	public $discount_percent;

	public $is_prior;
	public $is_reference;
	public $is_ctv;
	public $is_telesale;
	public $telesale_call_id;
	public $is_output_invoice_checked;

	public $point_step = 50;
	public $contact_name_ignore = ['THAM KHAO', 'TEST', 'IT', 'DEMO'];
	public $list_website_new_baggage = ['557d4a5b-27ce-5cb1-4531-5800ab9ed31d', '2b2c93b3-e916-113c-29bc-5b4c6de75db4', 'dc22131a-795a-6cd3-2caa-52d40d3b5622', 'd83ad3f6-3b3b-ba7b-f046-5512bad66c66'];
	
	public function bean_implements($interface)
	{
		switch ($interface) {
			case 'ACL':
				return true;
		}

		return false;
	}


	/*==================== CUSTOM ====================*/
	function save($check_notify = FALSE) {
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
        $is_alert = 0;
		$isDuplicate = false;
		// If duplicate save
		if (isset($_POST['duplicateSave']) && $_POST['duplicateSave'] == 'true' && isset($_POST['booking_prev_name']) && !empty($_POST['booking_prev_name'])) {
			$isDuplicate = true;
			$prefix = substr($_POST['booking_prev_name'], 0, 2);
			$this->name = $prefix . $this->generate_booking_name();
		}
		else if (empty($this->name)) {
			if (isset($current_user->agent_prefix) && !empty($current_user->agent_prefix)) $prefix = $current_user->agent_prefix;
			else $prefix = 'BK';

			$this->name = $prefix . $this->generate_booking_name();
			$is_alert = 1;
		}
		else {
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

		if (isset($_POST['is_paid'])) {
			$this->is_paid = $_POST['is_paid'];
		}

		// HÀNH TRÌNH TRONG BẢNG EC_CUSTOMER
		if (strpos($this->city, '-')) {
			$airport_arr = array_merge($app_list_strings['domestic_airport_list'], $app_list_strings['southeast_asia_airport_list'], $app_list_strings['northeast_asia_airport_list'], $app_list_strings['europe_airport_list'], $app_list_strings['americas_airport_list'], $app_list_strings['australia_airport_list'], $app_list_strings['africa_airport_list']);
			$this->city = $airport_arr[substr($this->city, 0, 3)];
		} else {
			$this->city = ucwords(strtolower(trim(stripslashes($this->city))));
		}

		$recordId = parent::save($check_notify);

		// Lưu thông tin hoá đơn
		$this->saveInvoiceInf($_POST, $this->id);

		// MST là bắt buộc khi xuất hóa đơn
		if(!empty($this->tax_code) && (int)$is_alert === 1){
			$inv_arr = json_decode(str_replace("&quot;", "\"", $this->shipping_address), 1);

			$name 		= $inv_arr['iv_account_name'] ?? '';
			$company 	= $this->company_name ?? '';

			if (!empty($name) && !empty($company)) {
				$user_inv = $name . ' [' . $company . ']';
			} elseif (empty($name) && !empty($company)) {
				$user_inv = $company;
			} else {
				$user_inv = $name;
			}

			$list_user_kt = [
				'37cd4853-721c-9808-af64-5600c8835d03', //Kế toán chịu trách nhiệm xuất hóa đơn - ngandtk
			];
			$alertData = [
				'name' 			=> $user_inv,
				'parent_type' 	=> 'EC_Flight_Bookings',
				'parent_id' 	=> $this->id,
				'description' 	=> 'Booking '.$this->name.' yêu cầu xuất hóa đơn.',
				'url_redirect' 	=> 'index.php?module=EC_Flight_Bookings&action=DetailView&record='.$this->id.'',
				'priority' 		=> 'low',
				'type' 			=> 'readonly',
			];
			$alert 		= new Alert();
			$alertId 	= $alert->autoCreateAlert('EC_Flight_Bookings', $list_user_kt, $alertData);
		} 

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
			if(in_array($this->created_by, $this->list_website_new_baggage)) $this->saveLinePassengers();
			else $this->saveLinePassengersOld();
		}

		// Change flight time
		if (isset($_POST['save_change_flight'])) {
			$this->saveChangeFlightTime();
			updateIsPriorForBooking($this->id);
		}

		// LƯU THÔNG TIN KHÁCH HÀNG
		// $this->saveInforCustomer($journey);

		if($isDuplicate) {
			$redirect_url = "index.php?module={$this->module_dir}&action=DetailView&record=$recordId";
			header("Location: {$redirect_url}");
			exit();
		}
	}

	public function save2($check_notify = FALSE) { return parent::save($check_notify); }

	// Save booking from webservice
	public function save_from_webservice($check_notify = FALSE) { return parent::save($check_notify); }

	// Generate booking random string
	function generate_booking_name() {
		// New 10/07/2023
		$booking_name = '';
		$booking_name .= date('y') . date('m') . date('d');

		$qty_booking = dechex($this->get_number_of_bookings() + 1);
		if (strlen($qty_booking) < 2) $qty_booking = '0' . $qty_booking;
		$booking_name .= strrev($qty_booking);

		return strtoupper($booking_name);
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
	
	/**
	 * Save passengers info
	 * @return 
	 */
	public function saveLinePassengers() {
		$bk = new EC_Flight_Bookings;
		$bk->retrieve($this->id);

		$row_count = count($_POST['psg_id']);
		for ($i = 0; $i < $row_count; $i++) {
			$psg = new EC_Booking_Passengers();
			if (!empty($_POST['psg_id'][$i])) $psg->retrieve($_POST['psg_id'][$i]);
			else $psg->id = '';

			$psg->booking_id 	= $this->id;
			$psg->type 		 	= $_POST['psg_traveller_type'][$i];
			$psg->salutation 	= $_POST['psg_salutation'][$i];
			$psg->name 		 	= strtoupper(myRemoveUnicodeChars(trim(stripslashes($_POST['psg_full_name'][$i]))));
			if (isset($_POST['psg_birthday'][$i]) && strtotime($_POST['psg_birthday'][$i]) !== false) {
				$date_str = str_replace('/', '-', $_POST['psg_birthday'][$i]);
				$psg->birthday = date('d-m-Y', strtotime($date_str));
			}
			$psg->pnr_outbound 		= trim(stripslashes($_POST['psg_pnr_outbound'][$i]));
			$psg->pnr_inbound 		= trim(stripslashes($_POST['psg_pnr_inbound'][$i]));
			$psg->eticket_outbound 	= trim(stripslashes($_POST['psg_eticket_outbound'][$i]));
			$psg->eticket_inbound 	= trim(stripslashes($_POST['psg_eticket_inbound'][$i]));
			$psg->add_type 			= $_POST['psg_add_type'][$i];
			$psg->parent_detail_id 	= $_POST['psg_parent_detail_id'][$i];
			$psg->deleted 			= (int)($_POST['psg_deleted'][$i] ?? 0);
			// CCCD / Passport
			$id_number = trim($_POST['psg_id_number'][$i] ?? '');
			if(ctype_digit($id_number) && strlen($id_number) == 12) {
				$psg->cic = $id_number;
				$psg->passport_number = "";
			}
			else {
				$psg->passport_number = $id_number;
				$psg->cic = "";
			}

			/******  BAGGAGES INFO  ******/
			// Text
			$psg->luggage_purchase_text 		= trim($_POST['psg_luggage_purchase_text'][$i]);
			$psg->luggage_purchase_text_inbound = trim($_POST['psg_luggage_purchase_text_inbound'][$i]);
			// Price
			$psg->luggage_purchase 			= unformat_number($_POST['psg_luggage_purchase'][$i]);
			$psg->luggage_purchase_inbound 	= unformat_number($_POST['psg_luggage_purchase_inbound'][$i]);
			// VAT
			$psg->vat_luggage_purchase 			= unformat_number($_POST['psg_vat_luggage_purchase'][$i]);
			$psg->vat_luggage_purchase_inbound 	= unformat_number($_POST['psg_vat_luggage_purchase_inbound'][$i]);
			// Cost
			$psg->luggage_purchase_no_vat 			= $psg->luggage_purchase - $psg->vat_luggage_purchase;
			$psg->luggage_purchase_inbound_no_vat 	= $psg->luggage_purchase_inbound - $psg->vat_luggage_purchase_inbound ;
			// Ticket
			$psg->eluggage_outbound = trim(stripslashes($_POST['psg_eluggage_outbound'][$i]));
			$psg->eluggage_inbound 	= trim(stripslashes($_POST['psg_eluggage_inbound'][$i]));
			// Supplier
			$psg->supplier_id 			= $_POST['psg_luggage_supplier'][$i];
			$psg->supplier_inbound_id 	= $_POST['psg_luggage_supplier_inbound'][$i];
			// Selling price
			$psg->luggage_price			= unformat_number($_POST['psg_luggage_price'][$i] ?? 0);
			$psg->luggage_price_inbound = unformat_number($_POST['psg_luggage_price_inbound'][$i] ?? 0);
			// Available baggage
			$psg->luggage_index_outbound = trim($_POST['psg_luggage_index_outbound'][$i] ?? '');
			$psg->luggage_index_inbound = trim($_POST['psg_luggage_index_inbound'][$i] ?? '');

			if ((int)$psg->deleted === 1) {
				if (!empty($psg->id)) $psg->mark_deleted($psg->id);
				else continue;
			}
			elseif (!empty($psg->name)) {
				$psg->save();
			}
		}

		// Khi booking ở trạng thái xác nhận
		// Kiểm tra nếu có dù chỉ 1 số vé cũng chuyển sang trạng thái đã xuất vé
		// Sau khi chuyển sang trạng thái đã xuất vé thì cập nhật trạng thái trong bảng ec_customer - info_data
		if ((int)$this->booking_status === 3) {
			$booking = new EC_Flight_Bookings;
			$booking->retrieve($this->id);

			for ($i = 0; $i < $row_count; $i++) {
				if (!empty($_POST['psg_eticket_outbound'][$i])) {
					$booking->is_ticket_exported = '1';
					if (empty($booking->date_ticket_issue)) {
						$booking->date_ticket_issue = date("d-m-Y");
					} else {
						if (isAllowedUser()) {
							$booking->date_ticket_issue = $_POST['date_ticket_issue'];
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
						$booking->date_ticket_inbound_issue = date("d-m-Y");
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

	/**
	 * Save when changing flight info such as: flight date, itinerary, passengers, baggages, ticket code, PNR
	 * @return 
	 */
	public function saveChangeFlightTime() {
		global $sugar_config;
		$vat_rate = $sugar_config['flight_config']['vat_percentage'] ?? 0.08;
		
		// ======== THAY ĐỔI THÔNG TIN HÀNH KHÁCH =========
		// Lấy STT của các lần thay đổi thông tin hành khách trước
		$sql_pass_order = 'SELECT MAX(IFNULL(go_with, 0)) FROM ec_booking_passengers WHERE booking_id = "' . $_POST['booking_id'] . '" AND deleted = 0';

		$pass_order = $this->db->getOne($sql_pass_order);

		// Lưu thông tin hành khách
		$pass_replace = [];
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

			// Tạo mới hành khách
			if ($create_new) {
				if (!empty($_POST['pass_name'][$i])) {
					$pass_n 					= new EC_Booking_Passengers;
					$pass_n->name 				= $_POST['pass_name'][$i];
					$pass_n->salutation 		= $_POST['pass_salutation'][$i];
					$pass_n->birthday 			= $_POST['pass_birthday' . $i];
					$pass_n->type 				= $pass->type;
					$pass_n->booking_id 		= $pass->booking_id;
					$pass_n->pnr_outbound 		= $_POST['pass_pnr_outbound'][$i];
					$pass_n->pnr_inbound 		= $_POST['pass_pnr_inbound'][$i];
					$pass_n->eticket_outbound 	= $_POST['pass_eticket_outbound'][$i];
					$pass_n->eticket_inbound 	= $_POST['pass_eticket_inbound'][$i];
					$pass_n->eluggage_outbound 	= $_POST['pass_eluggage_outbound'][$i];
					$pass_n->eluggage_inbound 	= $_POST['pass_eluggage_inbound'][$i];
					$pass_n->direction 			= $pass->direction;
					$pass_n->add_type 			= 2;
					$pass_n->parent_detail_id 	= $pass->id;
					$pass_n->go_with 			= ($pass_order + 1);
					// CCCD / Passport
					$pass_n->cic = trim($pass->cic ?? '');
					$pass_n->passport_number = trim($pass->passport_number ?? '');
					// Hành lý mua thêm
					if (isset($_POST['pass_luggage_ob'][$i])) {
						$pass_n->luggage_index_outbound = $pass->luggage_index_outbound;
						$pass_n->luggage_purchase_text 	= trim($_POST['pass_luggage_ob'][$i]);
						$pass_n->luggage_price 			= unformat_number($_POST['pass_luggage_price'][$i]);
						$pass_n->luggage_purchase 		= unformat_number($_POST['pass_luggage_purchase'][$i]);
						$pass_n->vat_luggage_purchase 	 = $pass_n->luggage_purchase > 0 ? $pass_n->luggage_purchase * $vat_rate : 0;
						$pass_n->luggage_purchase_no_vat = $pass_n->luggage_purchase > 0 ? $pass_n->luggage_purchase - $pass_n->vat_luggage_purchase : 0;
						$pass_n->supplier_id = trim($_POST['supplier_outbound'][$i]);
					}
					if (isset($_POST['pass_luggage_ib'][$i])) {
						$pass_n->luggage_index_inbound 			= $pass->luggage_index_inbound;
						$pass_n->luggage_purchase_text_inbound 	= trim($_POST['pass_luggage_ib'][$i]);
						$pass_n->luggage_price_inbound 			= unformat_number($_POST['pass_luggage_price_inbound'][$i]);
						$pass_n->luggage_purchase_inbound 		= unformat_number($_POST['pass_luggage_purchase_inbound'][$i]);
						$pass_n->vat_luggage_purchase_inbound 	 = $pass_n->luggage_purchase_inbound > 0 ? $pass_n->luggage_purchase_inbound * $vat_rate : 0;
						$pass_n->luggage_purchase_inbound_no_vat = $pass_n->luggage_purchase_inbound > 0 ? $pass_n->luggage_purchase_inbound - $pass_n->vat_luggage_purchase_inbound : 0;
						$pass_n->supplier_inbound_id = trim($_POST['supplier_inbound'][$i]);
					}
					$pass_n->save();

					$pass_replace[$pass->id] = $pass_n->id;
				}
			}
			else {
				$birthday = '';
				if (!empty($_POST['pass_birthday' . $i])) $birthday = $_POST['pass_birthday' . $i];

				// $pass = new EC_Booking_Passengers;
				// $pass->retrieve($_POST['pass_id'][$i]);
				$pass->birthday 			= $birthday;
				$pass->salutation 			= $_POST['pass_salutation'][$i];
				$pass->name 				= $_POST['pass_name'][$i];
				$pass->pnr_outbound 		= $_POST['pass_pnr_outbound'][$i];
				$pass->pnr_inbound 			= $_POST['pass_pnr_inbound'][$i];
				$pass->eticket_outbound 	= $_POST['pass_eticket_outbound'][$i];
				$pass->eticket_inbound 		= $_POST['pass_eticket_inbound'][$i];
				$pass->eluggage_outbound 	= $_POST['pass_eluggage_outbound'][$i];
				$pass->eluggage_inbound 	= $_POST['pass_eluggage_inbound'][$i];

				// Hành lý mua thêm
				if (isset($_POST['pass_luggage_ob'][$i])) {
					$pass_n->luggage_purchase_text 	= trim($_POST['pass_luggage_ob'][$i]);
					$pass_n->luggage_price 			= unformat_number($_POST['pass_luggage_price'][$i]);
					$pass_n->luggage_purchase 		= unformat_number($_POST['pass_luggage_purchase'][$i]);
					$pass_n->vat_luggage_purchase 	 = $pass_n->luggage_purchase > 0 ? $pass_n->luggage_purchase * $vat_rate : 0;
					$pass_n->luggage_purchase_no_vat = $pass_n->luggage_purchase > 0 ? $pass_n->luggage_purchase - $pass_n->vat_luggage_purchase : 0;
					$pass_n->supplier_id = trim($_POST['supplier_outbound'][$i]);
				}
				if (isset($_POST['pass_luggage_ib'][$i])) {
					$pass_n->luggage_purchase_text_inbound 	= trim($_POST['pass_luggage_ib'][$i]);
					$pass_n->luggage_price_inbound 			= unformat_number($_POST['pass_luggage_price_inbound'][$i]);
					$pass_n->luggage_purchase_inbound 		= unformat_number($_POST['pass_luggage_purchase_inbound'][$i]);
					$pass_n->vat_luggage_purchase_inbound 	 = $pass_n->luggage_purchase_inbound > 0 ? $pass_n->luggage_purchase_inbound * $vat_rate : 0;
					$pass_n->luggage_purchase_inbound_no_vat = $pass_n->luggage_purchase_inbound > 0 ? $pass_n->luggage_purchase_inbound - $pass_n->vat_luggage_purchase_inbound : 0;
					$pass_n->supplier_inbound_id = trim($_POST['supplier_inbound'][$i]);
				}
				$pass->save();
			}
		}

		if (!empty($_POST['applied_passenger'])) {
			$old_pass_arr = array_keys($pass_replace);
			for ($k = 0; $k < count($old_pass_arr); $k++) {
				$idx = array_search($old_pass_arr, $_POST['applied_passenger']);
				$_POST['applied_passenger'][$idx] = $pass_replace[$old_pass_arr[$k]];
			}
		}

		// ========== THAY ĐỔI HÀNH TRÌNH =============
		// Lấy STT của các lần thay đổi hành trình trước
		$sql_iti_order = 'SELECT MAX(IFNULL(sabre_logs, 0)) FROM ec_booking_itineraries WHERE deleted = 0 AND booking_id = "' . $_POST['booking_id'] . '"';
		$iti_order = $this->db->getOne($sql_iti_order); // 0, 1, 2

		// Nếu là lưu mới thông tin hành trình
		if (!isset($_POST['iti_id']) || empty($_POST['iti_id']) || is_null($_POST['iti_id'])) {
			// Lưu lượt đi của hành trình
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
				// Nếu không có thay đổi hành trình, nhưng thay đổi hành khách,
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

			// Lưu lượt về của hành trình
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
				// Nếu không có thay đổi hành trình, nhưng thay đổi hành khách,
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
				$invoice_inf = [
					'iv_account_name' => $post_fields['iv_account_name'],
					'iv_email' => $post_fields['iv_email'],
					'iv_identity_number' => $post_fields['iv_identity_number'],
					'iv_payment_method' => $post_fields['iv_payment_method'],
					'iv_bank_account' => $post_fields['iv_bank_account'],
					'iv_name_banks' => $post_fields['iv_name_banks']
				];

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
	function calculateBookingTicketQty($booking_id) {
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
		return $this->db->getOne($sql);
	}

	// public function saveInforCustomer($journey_from_to) {
	// 	global $db;

	// 	// Kiểm tra customer có tồn tại trong bảng ec_customer hay chưa dựa trên thông tin từ bảng ec_flight_bookings
	// 	$sql_check_customer = '
	// 		SELECT IF(COUNT(c.phone) > 0, 1, 0)
	// 		FROM ec_customer c
	// 		WHERE c.phone = "' . $this->phone . '"';

	// 	$count_customer = $db->getOne($sql_check_customer);
	// 	$cus 		 = new EC_Customer;

	// 	if (isset($journey_from_to) && !empty($journey_from_to)) {
	// 		$journey 	= $journey_from_to;
	// 	} else {
	// 		$journey_array = journeyOfBooking($this->id);
	// 		$journey 		= $journey_array["departure"] . ' - ' . $journey_array["arrival"];
	// 	}

	// 	if (!$count_customer) {
	// 		// TẠO KHÁCH HÀNG MỚI
	// 		$cus->name 	 = $this->contact_name;
	// 		$cus->phone  = $this->phone;
	// 		$cus->email  = $this->email;
	// 		$cus->gender = $this->contact_title;
	// 		$cus->type 	 = 'NEW';

	// 		// LƯU THÔNG TIN BOOKING CỦA KHÁCH HÀNG MỚI
	// 		$booking_list = array();
	// 		$booking_list[$this->id] = array(
	// 			'booking_number'			=> $this->name,
	// 			'booking_status' 			=> $this->booking_status,
	// 			'booking_date' 				=> $this->date_entered,
	// 			'booking_quantity' 			=> $this->total_qty,
	// 			'journey'					=> $journey,
	// 			'customer_name' 			=> trim(stripslashes($this->contact_name)),
	// 			'customer_email' 			=> $this->email,
	// 			'customer_price_total' 		=> unformat_number($this->total_amount),
	// 			'customer_price_revenue' 	=> calculateBKTotalAmt($this->id),
	// 			'customer_ip' 				=> $this->ip_address,
	// 		);

	// 		$cus->info_data	= json_encode($booking_list);
	// 		$cus->save();
	// 	} else {
	// 		// get booking_list hiện có của KH đó
	// 		$sql_get_booking_info = '
	// 		SELECT c.info_data
	// 		FROM ec_customer c
	// 		WHERE c.phone = "' . $this->phone . '"';
	// 		$current_booking = $db->getOne($sql_get_booking_info);

	// 		// convert booking_list hiện có thành array
	// 		$current_booking_array 	= json_decode(html_entity_decode($current_booking), true);

	// 		// Khi booking thay đổi thông tin thì tiến hành cập nhật booking này bên bảng ec_customer
	// 		if (isset($current_booking_array[$this->id])) {
	// 			$current_booking_array[$this->id]['booking_status'] 		= $this->booking_status;
	// 			$current_booking_array[$this->id]['booking_quantity'] 		= $this->total_qty;
	// 			$current_booking_array[$this->id]['customer_name'] 		= $this->contact_name;
	// 			$current_booking_array[$this->id]['customer_email'] 		= $this->email;
	// 			$current_booking_array[$this->id]['journey'] 			= $journey;
	// 			$current_booking_array[$this->id]['customer_price_total'] 	= unformat_number($this->total_amount);
	// 			$current_booking_array[$this->id]['customer_price_revenue'] = calculateBKTotalAmt($this->id);
	// 		} else {
	// 			$current_booking_array[$this->id] = array(
	// 				'booking_number'		=> $this->name,
	// 				'booking_status' 		=> $this->booking_status,
	// 				'booking_date' 			=> $this->date_entered,
	// 				'booking_quantity' 		=> $this->total_qty,
	// 				'journey'				=> $journey,
	// 				'customer_name' 		=> trim(stripslashes($this->contact_name)),
	// 				'customer_email' 		=> $this->email,
	// 				'customer_price_total' 	=> unformat_number($this->total_amount),
	// 				'customer_price_revenue' => calculateBKTotalAmt($this->id),
	// 				'customer_ip' 			=> $this->ip_address,
	// 			);
	// 		}

	// 		// Cập nhật field info_data
	// 		$updated_booking_data  = json_encode($current_booking_array);
	// 		$sql_update_booking = '
	// 			UPDATE ec_customer c
	// 			SET c.type = "' . getCustomerType($this->phone) . '" , info_data = \'' . $updated_booking_data . '\'
	// 			WHERE c.phone = "' . $this->phone . '" AND c.deleted = 0';
	// 		$db->query($sql_update_booking);
	// 	}
	// }

	/**
	 * Check is use new baggage
	 * 
	 * @param string $date_entered
	 * @param string $created_by
	 * @return bool
	 */
	public function isUseNewBaggage($date_entered, $created_by) {
		$date_entered = str_replace("/", "-", trim($date_entered));
		if(strtotime($date_entered) > strtotime('2025-10-01') && in_array($created_by, $this->list_website_new_baggage)) return true;
		return false;
	}

	/**
	 * Get baggage info by baggage data
	 * 
	 * @param array $bagData
	 * @param string $language
	 * @return array [available, purchase]
	 */
	public function getBaggageInfoByData($bagData, $language = 'vi') {
		try {
			$airlineCode = $bagData['airlineCode'] ?? ''; // Using for get available baggage info in old data
			$ticketClass = $bagData['ticketClass'] ?? ''; // Using for get available baggage info in old data
			$passType 	 = $bagData['passType'] ?? '0'; // Using for get available baggage info in old data
			$dateEntered = $bagData['dateEntered'] ?? $this->date_entered; // Using for get available baggage info in old data
			$createdBy 	 = $this->created_by ?? $bagData['createdBy'] ?? ''; // Using for get available baggage info in old data
			$bagIndex 	 = $bagData['bagIndex'] ?? ''; // Using for get available baggage info in new data or in old data with Vietjet
			$bagPurchaseText = $bagData['bagPurchaseText'] ?? ''; // Using for get purchase baggage info

			$result = ['available' => '', 'purchase' => ''];

			if(in_array($createdBy, $this->list_website_new_baggage)) $result['available'] = Baggage::renderAvailableBaggage($bagIndex, $language);
			else {
				$bags = generateLuggage($dateEntered, $airlineCode, $ticketClass, $passType, $bagIndex); // Array
				if($bags && !empty($bags)) {
					$bagString = is_numeric($bagIndex) ? $bags[(int)$bagIndex] : $bags[0]; // String

					$bagWeight = 0;
					if(is_string($bagString) && !empty($bagString)) {
						preg_match('/(\d+)kg/isU', $bagString, $output);
						$bagWeight = isset($output[1]) ? (int)$output[1] : 0;
					}

					if($bagWeight > 0) {
						if($language == 'en') $result['available'] = $bagIndex > 1000 ? "Extra {$bagWeight}kg" : "{$bagWeight}kg available";
						else $result['available'] = substr_replace($bagString, '', strpos($bagString, '(') - 1);
					}
				}
			}

			if(!empty($bagPurchaseText)) {
				$result['purchase'] = preg_replace('/\s*\([^)]*\)/', '', $bagPurchaseText);
			}

			return $result;
		}
		catch(Throwable $th) {
			return ['available' => '', 'purchase' => ''];
		}
	}

	/**
	 * Generate passenger baggage info (Use in show detail booking)
	 * 
	 * @param array $passInfo Information of a specific passenger
	 * @param int $orderNumber
	 * @param string $returnType HTML, JSON
	 * 
	 * @return string HTML
	 */
	public function generatePassengerBaggageInfo($passInfo, $orderNumber = 0, $returnType = 'HTML') {
		$date_entered = $passInfo['date_entered'] ?? date('Y-m-d');
		$created_by   = $passInfo['createdBy'] ?? '';
		// $bookingName  = $passInfo['bookingName'] ?? '';
		$airlineCodeOutbound = $passInfo['airlineCodeOutbound'] ?? '';
		$ticketClassOutbound = $passInfo['ticketClassOutbound'] ?? '';
		$airlineCodeInbound = $passInfo['airlineCodeInbound'] ?? '';
		$ticketClassInbound = $passInfo['ticketClassInbound'] ?? '';

		if($this->isUseNewBaggage($date_entered, $created_by)) {
			$rowBagHTML = '';
			foreach (['outbound', 'inbound'] as $roundName) {
				$roundNameHTML = $roundName == "outbound" ? '<b class="color-primary mr-1">Lượt đi:</b>' : '<b class="color-red mr-1">Lượt về:</b>';

				// Hành lý có sẵn
				if (!empty($passInfo["luggage_index_$roundName"])) {
					$rowBagHTML .= '<p class="fst-italic">
						' . $roundNameHTML . '
						' . Baggage::renderAvailableBaggage($passInfo["luggage_index_$roundName"]) . '
						<span>(Hạng vé có sẵn)</span>
					</p>';
				}

				// Hành lý mua thêm
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
						' . $roundNameHTML . '
						' . $bagText . '
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
				<td colspan="10" class="text-start align-middle flex-wrap">' . $rowBagHTML . '</td>
			</tr>';
		}
		else {
			$luggage_price = '';

			/***** Hành lý chiều đi *****/
			// Bag_out là list option hành lý
			$bag_out = generateLuggage($date_entered, $airlineCodeOutbound, $ticketClassOutbound, $passInfo['type'], (int)$passInfo['luggage_index_outbound']);
			if (!empty($passInfo['luggage_index_outbound'])) {
				$passInfo['luggage_price'] = (int)$passInfo['luggage_index_outbound'];
			}

			$bag_out2 = $bag_out[(int)$passInfo['luggage_price']] ?? '';

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
					$eluggage_outbound .= '<span data-label="Số vé HL đi" class="text-center" class="eluggage_outbound">
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
				$bag_in = generateLuggage($date_entered, $airlineCodeInbound, $ticketClassInbound, $passInfo['type'], (int)$passInfo['luggage_index_inbound']);

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
	public function generateCombinedPassengerBaggageInfo($depAvaiBagText, $depPurchaseBagText, $retAvaiBagText, $retPurchaseBagText, $language = 'vn') {
		$isRoundtrip = false;
		if((!empty($depAvaiBagText) || !empty($depPurchaseBagText)) && (!empty($retAvaiBagText) || !empty($retPurchaseBagText))) $isRoundtrip = true;

		// Departure
		$baggageDescriptionDep = '';
		if(!empty($depAvaiBagText) && !empty($depPurchaseBagText)) {
			$avaiBagDepParts = Baggage::parsePackage($depAvaiBagText); // Array
			$purchasedBagDepParts = Baggage::parsePackage($depPurchaseBagText); // Array

			// Conbine
			if($avaiBagDepParts['weight'] === $purchasedBagDepParts['weight'] && !is_null($avaiBagDepParts['weight'])
				&& $avaiBagDepParts['package'] > 0 && $purchasedBagDepParts['package'] > 0
				&& stripos($depAvaiBagText, 't') === false
			) {
				$baggageDescriptionDep .= ($avaiBagDepParts['package'] + $purchasedBagDepParts['package']) . ($language == 'en' ? ' packages' : ' kiện') . ' x ' . $avaiBagDepParts['weight'] . 'kg';
			}
			elseif(is_null($avaiBagDepParts['package']) && is_null($purchasedBagDepParts['package'])) {
				$baggageDescriptionDep .= ($avaiBagDepParts['weight'] + $purchasedBagDepParts['weight']) . 'kg';
			}
			elseif(is_null($avaiBagDepParts['weight']) && is_null($purchasedBagDepParts['weight'])) {
				$baggageDescriptionDep .= ($avaiBagDepParts['package'] + $purchasedBagDepParts['package']) . ($language == 'en' ? ' packages' : ' kiện');
			}
			else {
				$baggageDescriptionDep .= "$depAvaiBagText + $depPurchaseBagText";
			}
		}
		elseif(!empty($depAvaiBagText)) $baggageDescriptionDep .= $depAvaiBagText;
		elseif(!empty($depPurchaseBagText)) $baggageDescriptionDep .= $depPurchaseBagText;
		if(!empty($baggageDescriptionDep) && $isRoundtrip) $baggageDescriptionDep .= ($language == 'en' ? ' (Departure)' : ' (Lượt đi)');

		// Return
		$baggageDescriptionRet = '';
		if(!empty($retAvaiBagText) && !empty($retPurchaseBagText)) {
			$avaiBagRetParts = Baggage::parsePackage($retAvaiBagText); // Array
			$purchasedBagRetParts = Baggage::parsePackage($retPurchaseBagText); // Array

			// Conbine
			if($avaiBagRetParts['weight'] === $purchasedBagRetParts['weight'] && !is_null($avaiBagRetParts['weight']) 
				&& $avaiBagDepParts['package'] > 0 && $purchasedBagDepParts['package'] > 0
				&& stripos($retAvaiBagText, 't') === false
			) {
				$baggageDescriptionRet .= ($avaiBagRetParts['package'] + $purchasedBagRetParts['package']) . ($language == 'en' ? ' packages' : ' kiện') . ' x ' . $avaiBagRetParts['weight'] . 'kg';
			}
			elseif(is_null($avaiBagRetParts['package']) && is_null($purchasedBagRetParts['package'])) {
				$baggageDescriptionRet .= ($avaiBagRetParts['weight'] + $purchasedBagRetParts['weight']) . 'kg';
			}
			elseif(is_null($avaiBagRetParts['weight']) && is_null($purchasedBagRetParts['weight'])) {
				$baggageDescriptionRet .= ($avaiBagRetParts['package'] + $purchasedBagRetParts['package']) . ($language == 'en' ? ' packages' : ' kiện');
			}
			else {
				$baggageDescriptionRet = "$retAvaiBagText + $retPurchaseBagText";
			}
		}
		elseif(!empty($retAvaiBagText)) $baggageDescriptionRet .= $retAvaiBagText;
		elseif(!empty($retPurchaseBagText)) $baggageDescriptionRet .= $retPurchaseBagText;
		if(!empty($baggageDescriptionRet) && $isRoundtrip) $baggageDescriptionRet .= ($language == 'en' ? ' (Return)' : ' (Lượt về)');


		// Combine two way
		if (!empty($baggageDescriptionDep) && !empty($baggageDescriptionRet)) {
			if(stripos($baggageDescriptionDep, '+') !== false || stripos($baggageDescriptionRet, '+') !== false) {
				return "{$baggageDescriptionDep}\n{$baggageDescriptionRet}";
			}
			else {
				return "{$baggageDescriptionDep} - {$baggageDescriptionRet}";
			}
		}
		else return trim("$baggageDescriptionDep $baggageDescriptionRet");
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
	public function generateBaggageOptions($airlineCode, $ticketClass = '', $currentValue = '', $currentPrice = 0) {
		$options = "<option value=''>Chọn hành lý</option>";
		if(is_string($airlineCode) && !empty($airlineCode)) {
			try {
				$baggageData = [];
				$cacheKey = "extra_baggage_options_".strtolower($airlineCode);
				$cacheTime = 3600;

				// Get data in SESSION cache
				$cacheKey = "extra_baggage_options_".strtolower($airlineCode);
				if(isset($_SESSION) && isset($_SESSION[$cacheKey]) && !empty($_SESSION[$cacheKey])) {
					$sessionData = $_SESSION[$cacheKey];
					if(is_array($sessionData) && !empty($sessionData)) {
						$expiredAt = $sessionData['expiredAt']; // Timestamp
						if(time() < $expiredAt) $baggageData = $sessionData['data'];
					}
				}

				// Get data from API
				if(!is_array($baggageData) || empty($baggageData)) {
					$epFactory = new entryFactory();
            		$fareSystem = $epFactory->create('entryFareSystemClass');
					
					$baggageResponse = $fareSystem->getBaggageOption(['airlineCode' => $airlineCode]);
					$baggageResponse = json_decode($baggageResponse, true);

					if (isset($baggageResponse['status']) && $baggageResponse['status'] == 1) {
						$baggageData = $baggageResponse['data'] ?? [];
						if(!empty($baggageData)) {
							$_SESSION[$cacheKey] = [
								'data' => $baggageData,
								'expiredAt' => time() + $cacheTime
							];
						}
						else {
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

						$options .= "<option value='". htmlspecialchars($saveValue) ."'
							data-text='". htmlspecialchars($displayText) ."'
							data-cost='{$cost}'
							data-value='{$value}'
							{$selected}
						>
							". htmlspecialchars($displayText) ."
						</option>";
					}
				}
				if (!empty($currentValue) && !$foundMatch) {
					$options .= '<option value="'. htmlspecialchars($currentValue) .'"
						data-text="'. htmlspecialchars($currentValue) .' (Tùy chỉnh)"
						data-cost="'. $currentPrice .'"
						data-value="'. $currentPrice .'" selected
					>
						'. htmlspecialchars($currentValue) . ' (Tùy chỉnh)
					</option>';
				}
			}
			catch (Throwable $th) {
				$GLOBALS['log']->fatal("Error fetching baggage options: {$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}");
			}
		}
		return $options;
	}

	/**
     * Get list ticket number in booking by times
     * 
     * @param string $bookingId
	 * @return array
     */
    public function getListTickets($bookingId) {
		if(!is_string($bookingId) || empty($bookingId)) return [];

		$listTickets = [];

		$sqloutinv = "SELECT COUNT(DISTINCT parent_id) AS output_invoice_qty
			FROM ec_chitiethoadon ct
			WHERE ct.booking_id = '{$bookingId}' AND ct.deleted = 0";
		$outputInvQty = $this->db->getOne($sqloutinv) ?? 0;

		$goWithArray = [];
		$resPaymentReceipt = $this->db->query("SELECT DISTINCT IFNULL(go_with, 0)
				FROM ec_receipt_voucher
				WHERE booking_id = '{$bookingId}'
					AND rv_status != '0'
					AND deleted = 0");
		while($rowPaymentReceipt = $this->db->fetchByAssoc($resPaymentReceipt)) $goWithArray[] = (int)$rowPaymentReceipt['go_with'];
		
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
			$goWith = (int)($row['goWith'] ?? 0); // Changed times of passenger in booking
			$ticketType = (int)($row['addType'] ?? 0);

			// Only get list ticket code for next processing
			if($goWith < $outputInvQty) continue;

			// Only get list ticket code in changed times which have receipt voucher
			if($goWith > 0 && array_search($goWith, $goWithArray) === false) continue;

			// Flight ticket number
			if($ticketType != 1) {
				if(isset($row['ticketNumberOut']) && !empty($row['ticketNumberOut'])) {
					$listTickets[$goWith][$row['ticketNumberOut']][] = [
						'type' 			=> 'flight',
						'direction' 	=> 0,
						'passName' 		=> $row['name'],
						'purchasePrice' => null,
					];
				}
				if(isset($row['ticketNumberIn']) && !empty($row['ticketNumberIn'])) {
					$listTickets[$goWith][$row['ticketNumberIn']][] = [
						'type' 			=> 'flight',
						'direction' 	=> 1,
						'passName' 		=> $row['name'],
						'purchasePrice' => null,
					];
				}
			}

			// Baggage ticket number
			if(isset($row['bagPriceOut']) && $row['bagPriceOut'] > 0) {
				$bagTicketNumberOut = $row['bagTicketNumberOut'] ?? '';
				if(empty($bagTicketNumberOut)) $bagTicketNumberOut = $row['ticketNumberOut'] ?? 'BAGTICKETOUT';

				$listTickets[$goWith][$bagTicketNumberOut][] = [
					'type' 			=> 'baggage',
					'direction' 	=> 0,
					'passName' 		=> $row['name'],
					'purchasePrice' => $row['bagPriceOut'],
				];
			}
			if(isset($row['bagPriceIn']) && $row['bagPriceIn'] > 0) {
				$bagTicketNumberIn = $row['bagTicketNumberIn'] ?? '';
				if(empty($bagTicketNumberIn)) $bagTicketNumberIn = $row['ticketNumberIn'] ?? 'BAGTICKETIN';

				$listTickets[$goWith][$bagTicketNumberIn][] = [
					'type' 			=> 'baggage',
					'direction' 	=> 1,
					'passName' 		=> $row['name'],
					'purchasePrice' => $row['bagPriceIn'],
				];
			}
        }

        return $listTickets;
    }

	/***********  OLD FUNCTIONS  ***********/

	function saveLinePassengersOld() {
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
				$date_str = str_replace('/', '-', $_POST['psg_birthday'][$i]);
				$psg->birthday = date('d-m-Y', strtotime($date_str));
			} 

			$psg->eticket_outbound 	= trim(stripslashes($_POST['psg_eticket_outbound'][$i]));
			$psg->eticket_inbound 	= trim(stripslashes($_POST['psg_eticket_inbound'][$i]));
			
			$psg->eluggage_outbound = trim(stripslashes($_POST['psg_eluggage_outbound'][$i]));
			$psg->eluggage_inbound 	= trim(stripslashes($_POST['psg_eluggage_inbound'][$i]));

			$psg->pnr_outbound 		= trim(stripslashes($_POST['psg_pnr_outbound'][$i]));
			$psg->pnr_inbound 		= trim(stripslashes($_POST['psg_pnr_inbound'][$i]));

			if (isset($_POST['psg_luggage_price'][$i])) {
				if (((!isset($_POST['psg_luggage_ob_ind']) || empty($_POST['psg_luggage_ob_ind'][$i])) && (string)$this->airline !== 'VJA' && (string)$this->airline !== 'VJ') || ((string)$this->airline !== 'VJA' && (string)$this->airline !== 'VJ')) {
					$psg->luggage_price = unformat_number($_POST['psg_luggage_price'][$i]);
					$psg->luggage_index_outbound = '';
				} else {
					// BK đặt từ ngày 21-11-2022, VJA có giá mới
					$list_key = strtotime($bk->date_entered) >= strtotime('2022-11-21') ? 'vietjet_index_price_list2' : 'vietjet_index_price_list';
					$psg->luggage_price = $app_list_strings[$list_key][(int)$_POST['psg_luggage_price'][$i]];

					$psg->luggage_index_outbound = (int)$_POST['psg_luggage_price'][$i];
				}
			}

			if (isset($_POST['psg_luggage_price_inbound'][$i])) {
				if (((!isset($_POST['psg_luggage_ib_ind']) || empty($_POST['psg_luggage_ib_ind'][$i])) && (string)$this->airline !== 'VJA' && (string)$this->airline !== 'VJ') || ((string)$this->airline_inbound !== 'VJA' && (string)$this->airline_inbound !== 'VJ')) {
					$psg->luggage_price_inbound = unformat_number($_POST['psg_luggage_price_inbound'][$i]);
					$psg->luggage_index_inbound = '';
				} else {
					// BK đặt từ ngày 21-11-2022, VJA có giá mới
					$list_key = strtotime($bk->date_entered) >= strtotime('2022-11-21') ? 'vietjet_index_price_list2' : 'vietjet_index_price_list';
					$psg->luggage_price_inbound = $app_list_strings[$list_key][(int)$_POST['psg_luggage_price_inbound'][$i]];
					$psg->luggage_index_inbound = (int)$_POST['psg_luggage_price_inbound'][$i];
				}
			}

			$psg->luggage_purchase 				= unformat_number($_POST['psg_luggage_purchase'][$i]);
			$psg->luggage_purchase_inbound 		= unformat_number($_POST['psg_luggage_purchase_inbound'][$i]);

			$psg->supplier_id 					= $_POST['psg_luggage_supplier'][$i];
			$psg->supplier_inbound_id 			= $_POST['psg_luggage_supplier_inbound'][$i];
			$psg->booking_id 					= $this->id;
			$psg->add_type 						= $_POST['psg_add_type'][$i] ?? null;
			$psg->parent_detail_id 				= $_POST['psg_parent_detail_id'][$i] ?? '';
			$psg->deleted 						= $_POST['psg_deleted'][$i] ?? 0;
			
			$psg->luggage_purchase_no_vat 		= unformat_number($_POST['psg_detail_lug_pur_no_vat'][$i]);
			$psg->vat_luggage_purchase 			= unformat_number($_POST['psg_detail_lug_pur_vat'][$i]);
			$psg->luggage_purchase_inbound_no_vat = unformat_number($_POST['psg_detail_lug_pur_ib_no_vat'][$i]);
			$psg->vat_luggage_purchase_inbound 	= unformat_number($_POST['psg_detail_lug_pur_ib_vat'][$i]);

			$psg->cic 							= trim($_POST['psg_cic'][$i]) ?? '';
			$psg->passport_number 				= trim($_POST['psg_passport_number'][$i]) ?? '';

			if ((int)$psg->deleted === 1) {
				if (!empty($psg->id)) $psg->mark_deleted($psg->id);
				else continue;
			} elseif (!empty($psg->name)) {
				$psg->save();
			}
		}

		// Khi booking ở trạng thái xác nhận
		// Kiểm tra nếu có dù chỉ 1 số vé cũng chuyển sang trạng thái đã xuất vé
		// Sau khi chuyển sang trạng thái đã xuất vé thì cập nhật trạng thái trong bảng ec_customer - info_data
		if ((int)$this->booking_status === 3) {
			$booking = new EC_Flight_Bookings;
			$booking->retrieve($this->id);

			for ($i = 0; $i < $row_count; $i++) {
				if (!empty($_POST['psg_eticket_outbound'][$i])) {
					$booking->is_ticket_exported = '1';
					if (empty($booking->date_ticket_issue)) {
						$booking->date_ticket_issue = date("d-m-Y");
					} else {
						if (isAllowedUser()) {
							$booking->date_ticket_issue = $_POST['date_ticket_issue'];
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
						$booking->date_ticket_inbound_issue = date("d-m-Y");
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

	// Lưu thay đổi Ngày bay / Hành trình / Thông tin hành khách / Hành lý / Số vé / Code vé
	function saveChangeFlightTimeOld() {
		global $current_user;
		
		// ======== THAY ĐỔI THÔNG TIN HÀNH KHÁCH =========
		// lấy stt của các lần thay đổi thông tin hành khách trước
		$sql_pass_order = 'SELECT MAX(IFNULL(go_with, 0)) FROM ec_booking_passengers WHERE booking_id = "' . $_POST['booking_id'] . '" AND deleted = 0';

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
					$pass_n->luggage_purchase_inbound 	= $_POST['bought_price_inbound'][$i];
					$pass_n->supplier_id 				= $_POST['supplier_outbound'][$i];
					$pass_n->supplier_inbound_id 		= $_POST['supplier_inbound'][$i];
					$pass_n->add_type 					= 2;
					$pass_n->parent_detail_id 			= $pass->id;
					$pass_n->go_with 					= ($pass_order + 1);
					$pass_n->save();

					$pass_replace[$pass->id] = $pass_n->id;
				}
			} else {

				$birthday = '';
				if (!empty($_POST['pass_birthday' . $i])) {
					$birthday = $_POST['pass_birthday' . $i];
				} 

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

		if (!empty($_POST['applied_passenger'])) {
			$old_pass_arr = array_keys($pass_replace);
			for ($k = 0; $k < count($old_pass_arr); $k++) {
				$idx = array_search($old_pass_arr, $_POST['applied_passenger']);
				$_POST['applied_passenger'][$idx] = $pass_replace[$old_pass_arr[$k]];
			}
		}

		// ========== THAY ĐỔI HÀNH TRÌNH =============
		// Lấy STT của các lần thay đổi hành trình trước
		$sql_iti_order = 'SELECT MAX(IFNULL(sabre_logs, 0)) FROM ec_booking_itineraries WHERE deleted = 0 AND booking_id = "' . $_POST['booking_id'] . '"';
		$iti_order = $this->db->getOne($sql_iti_order); // 0, 1, 2

		// Nếu là lưu mới thông tin hành trình
		if (!isset($_POST['iti_id']) || empty($_POST['iti_id']) || is_null($_POST['iti_id'])) {
			// Lưu lượt đi của hành trình
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
				// Nếu không có thay đổi hành trình, nhưng thay đổi hành khách,
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

			// Lưu lượt về của hành trình
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
				// Nếu không có thay đổi hành trình, nhưng thay đổi hành khách,
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
		}
		else { // nếu là sửa lại thông tin hành trình
			$applied_pass_arr 		= explode(',', $_POST['applied_pass']);
			$applied_pass_name_arr 	= explode(',', $_POST['applied_pass_name']);
			$iti_id_arr 			= explode(',', $_POST['iti_id']);

			for ($t = 0; $t < count($applied_pass_arr); $t++) {
				$_POST['iti_id'] = $iti_id_arr[$t];
				$this->saveFlightItinerary($applied_pass_name_arr[$t], '', $_POST, $applied_pass_arr[$t], '');
			}
		}
	}
}
