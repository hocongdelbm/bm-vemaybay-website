<?php

class EC_HoaDonBan extends Basic {
	public $new_schema 	= true;
	public $module_dir 	= 'EC_HoaDonBan';
	public $object_name = 'EC_HoaDonBan';
	public $table_name 	= 'ec_hoadonban';
	public $importable 	= true;

	public $disable_row_level_security = true; // to ensure that modules created and deployed under CE will continue to function under team security if the instance is upgraded to PRO

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

	public $doituong_id;
	public $doituong;
	public $lienhe;
	public $tencongty;
	public $diachi;
	public $masothue;
	public $ngaychungtu;
	public $ngayhachtoan;
	public $mauhoadon;
	public $loaihoadon;
	public $ngayhoadon;
	public $loaitien;
	public $booking_id;
	public $booking;
	public $tongtien;
	public $is_signed;
	public $tinhtrang;
	public $company_unit;
	public $tongsl;
	public $tongthanhtoan;
	public $invoice_data;
	public $sohoadon;
	public $kyhieuhd;
	public $citizen_id;
	public $passport_number;
	public $loaikh;
	public $hinhthuctt;
	public $nganhang;
	public $sotaikhoan;
	public $email;
	public $represent_booking;

	public function bean_implements($interface) {
		switch ($interface) {
			case 'ACL':
				return true;
		}

		return false;
	}

	public function save($check_notify = FALSE) {
		if(empty($this->name)) $this->name = $this->renderName($this->countVoucher());

		if(!isset($_POST['kyhieuhd']) || empty($_POST['kyhieuhd'])) $this->kyhieuhd = $this->genInvSerial();
		else $this->kyhieuhd = trim($_POST['kyhieuhd']);
		
		$this->masothue = $this->checkTaxCode($this->masothue) ? $this->masothue : '';

		$identity_number = trim($_POST['identity_number'] ?? '');
		if(!empty($identity_number)) {
			if(strlen($identity_number) == 12) $this->citizen_id = $identity_number;
			elseif(strlen($identity_number) > 6) $this->passport_number = $identity_number;
		}
		else {
			$this->citizen_id = '';
			$this->passport_number = '';
		}

		parent::save($check_notify);

		if (
			($this->loaihoadon == '0'
			&& isset($_POST['ct_ticket_number_id']) && !empty($_POST['ct_ticket_number_id'])
			&& isset($_POST['ct_ticket_number']) && !empty($_POST['ct_ticket_number'])
			)	
			|| 
			($this->loaihoadon == '1' && !empty($_POST['ct_name']))
		) {
			$this->saveListItems();
		}
	}

	public function save2($check_notify = FALSE) {
        return parent::save($check_notify);
    }

	/**
	 * Render name for output invoice
	 * 
	 * @param string $countStr
	 * @return string
	 */
	private function renderName($countStr) {
		return "HD-" . date('ymd') . "-{$countStr}";
	}

	/**
	 * Count the number of output invoice in current date
	 * 
	 * @return string The next quantity
	 */
	public function countVoucher() {
		$sql = 'SELECT COUNT(id)
			FROM ec_hoadonban 
			WHERE DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"';
		return str_pad(($this->db->getOne($sql) + 1), 3, 0, STR_PAD_LEFT);
	}

	public function saveListItems() {
		$row_count = count($_POST['ct_detail_id'] ?? []);

		if($row_count > 0) {
			$beanInInv = new EC_Input_Invoices();
			for ($i = 0; $i < $row_count; $i++) {
				$cthd = new EC_ChiTietHoaDon();
				$cthd->id 			= $_POST['ct_detail_id'][$i];
				$cthd->mahang 		= $_POST['ct_code'][$i];
				$cthd->soluong 		= unformat_number($_POST['ct_qty'][$i]);
				$cthd->giamua 		= unformat_number($_POST['ct_purchase_price'][$i]);
				$cthd->dongia 		= unformat_number($_POST['ct_price'][$i]);
				$cthd->thuesuat 	= unformat_number($_POST['ct_percent_vat'][$i]);
				$cthd->tienthue 	= unformat_number($_POST['ct_vat'][$i]);
				$cthd->phithuho 	= unformat_number($_POST['ct_authorized'][$i]);
				$cthd->phisanbay 	= unformat_number($_POST['ct_airport_fee'][$i]); 
				$cthd->phikhac 		= unformat_number($_POST['ct_other_fee'][$i]); 
				$cthd->phidv 		= unformat_number($_POST['ct_service'][$i]);
				$cthd->thanhtien 	= unformat_number($_POST['ct_total'][$i]);
				$cthd->parent_id 	= $this->id;
				$cthd->parent_type 	= 'EC_HoaDonBan';
				$cthd->deleted 		= (int)($_POST['ct_deleted'][$i] ?? 0);
				$cthd->order_by_no 	= $i;

				if ($this->loaihoadon == '0') { // HĐ GTGT
					$cthd->name = trim(stripslashes($_POST['ct_ticket_number'][$i]));

					if($cthd->mahang == 'PHL' || $cthd->mahang == 'PD') {
						// Lưu thông tin phiếu thu
						$receipt_voucher_name = trim($_POST['ct_receipt_voucher'][$i] ?? '');
						$receipt_voucher_id = '';
						if(!empty($receipt_voucher_name)) {
							$receipt_voucher_id = $this->db->getOne("SELECT id FROM ec_receipt_voucher WHERE name='$receipt_voucher_name' AND deleted=0 ORDER BY date_entered DESC LIMIT 1");
						}
						$cthd->receipt_voucher_id = $receipt_voucher_id;
					}
					else if($cthd->mahang == 'PK') {
						$cthd->name = 'PK';
					}

					$cthd->ticket_number_id = $_POST['ct_ticket_number_id'][$i] ?? '';
					$cthd->booking_id = $_POST['ct_booking_id'][$i] ?? '';
					$cthd->booking = $_POST['ct_booking'][$i] ?? '';
				}
				else if ($this->loaihoadon == '1') { // HĐ DV
					$cthd->name = trim(stripslashes($_POST['ct_name'][$i]));
				}

				if ($cthd->deleted == 1) $cthd->mark_deleted($cthd->id);
				else if (!empty($cthd->name)) $cthd->save();
				
				if (!empty($cthd->ticket_number_id)) {
					$beanInInv->updateInventoryStatus($cthd->ticket_number_id);
				}
			}
		}
	}

	/**
	 * Generate invoice serial by customer type
	 * 
	 * @param string $customerType (1:Personal ; 0:Company)
	 * @return string
	 */
	public function genInvSerial($customerType = null) {
		if(is_null($customerType)) $customerType = (string)($this->loaikh);

		$y = date('y');
		if($customerType === '0') return "C{$y}THV";
		elseif($customerType === '1') return "C{$y}MHV";
		return '';
	}

	/**
	 * Automatic create output invoice
	 * 
	 * @param string $bookingId
	 * @param int $additionalSecond Use to create orderedly
	 * @return bool
	 * @author Duc Pham
	 */
	public function createAuto($bookingId, $additionalSecond = 0) {
		if(!is_string($bookingId) || empty($bookingId)) return false;

		// Get list available tickets
		$beanInInv = new EC_Input_Invoices();
		$listAvailableTickets = $beanInInv->getListAvailableTickets($bookingId);

		if(empty($listAvailableTickets)) return false;

		// Get list booking tickets
		$beanBooking = new EC_Flight_Bookings();
		$listBookingTickets = $beanBooking->getListTickets($bookingId);

		// Get info in booking
		$sqlBooking = "SELECT name
				,IFNULL(tax_code, '') AS iv_tax_code
				,IFNULL(company_name, '') AS iv_company_name
				,IFNULL(company_address, '') AS iv_address
				,shipping_address
				,total_bought_amount
				,luggage_fee
				,total_amount
			FROM ec_flight_bookings
			WHERE id = '{$bookingId}' AND deleted = 0";
		$resBooking = $this->db->query($sqlBooking);
		$bookingInfo = $this->db->fetchByAssoc($resBooking);

		foreach ($listBookingTickets as $times => $listValue) {
			$additionalSecond++;
			$listBookingTicketNumber = array_keys($listValue);
			$listAvailableTicketNumber = array_column(array_values($listAvailableTickets), 'ticket_number');

			$diff = array_diff($listBookingTicketNumber, $listAvailableTicketNumber);
			if (empty($diff)) {
				try {
					$invArr = json_decode(str_replace('&quot;', '"', $bookingInfo['shipping_address']), 1);
					$bookingInfo['iv_account_name'] 	= trim($invArr['iv_account_name'] ?? '');
					$bookingInfo['iv_email'] 			= trim($invArr['iv_email'] ?? '');
					$bookingInfo['iv_identity_number'] 	= trim($invArr['iv_identity_number'] ?? '');
					$bookingInfo['iv_payment_method'] 	= trim($invArr['iv_payment_method'] ?? '');
					$bookingInfo['iv_bank_account'] 	= trim($invArr['iv_bank_account'] ?? '');
					$bookingInfo['iv_name_banks'] 		= trim($invArr['iv_name_banks'] ?? '');
					unset($bookingInfo['shipping_address']);

					$loaikh = '1';
					if(isset($bookingInfo['iv_company_name']) && !empty($bookingInfo['iv_company_name'])
						&& isset($bookingInfo['iv_tax_code']) && !empty($bookingInfo['iv_tax_code'])) $loaikh = '0';

					$citizen_id = $passport_number = '';
					if(strlen($bookingInfo['iv_identity_number']) == 12) $citizen_id = $bookingInfo['iv_identity_number'];
					elseif(!empty($bookingInfo['iv_identity_number'])) $passport_number = $bookingInfo['iv_identity_number'];

					$outInv = new EC_HoaDonBan();
					$outInv->update_date_entered = true;
					$outInv->id 			= '';
					$outInv->name 			= $this->renderName($this->countVoucher());
					$outInv->ngayhoadon 	= date('d-m-Y');
					$outInv->mauhoadon		= null;
					$outInv->company_unit 	= 'MHV';
					$outInv->loaihoadon 	= '0'; // HĐ GTGT
					$outInv->loaitien 		= 'VND';
					$outInv->loaikh 		= $loaikh;
					$outInv->kyhieuhd 		= $this->genInvSerial($loaikh);
					$outInv->lienhe 		= $bookingInfo['iv_account_name'];
					$outInv->tencongty 		= $bookingInfo['iv_company_name'];
					// Check tax code
					$bookingInfo['iv_tax_code'] = trim($bookingInfo['iv_tax_code']);
					$outInv->masothue 		= $this->checkTaxCode($bookingInfo['iv_tax_code']) ? $bookingInfo['iv_tax_code'] : '';
					$outInv->email			= $bookingInfo['iv_email'];
					$outInv->citizen_id 	= $citizen_id;
					$outInv->passport_number = $passport_number;
					$outInv->diachi 		= $bookingInfo['iv_address'];
					$outInv->hinhthuctt 	= $bookingInfo['iv_payment_method'];
					$outInv->nganhang 		= $bookingInfo['iv_name_banks'];
					$outInv->sotaikhoan 	= $bookingInfo['iv_bank_account'];
					$outInv->tinhtrang		= 0;
					$outInv->is_signed		= 0;
					$outInv->description 	= "Hóa đơn tạo tự động";
					$outInv->date_entered 	= date('Y-m-d H:i:s', time() + $additionalSecond - 7*3600);
					$parentId = $outInv->save2();

					// Check here
					if(!$parentId || !is_string($parentId)) continue;

					$totalBaggagePrice = 0; // Baggage purchase price in booking
					foreach($listValue as $tknum => $arr) {
						foreach ($arr as $bookingtk) {
							if($bookingtk['type'] == 'baggage') {
								$totalBaggagePrice += $bookingtk['purchasePrice'] ?? 0;
							}
						}
					}

					$isIssueBaggage = false;
					$totalQtyTicket = [];
					$totalQtyAllTicket = 0;
					foreach($listAvailableTickets as $tk) {
						if(array_search($tk['ticket_number'], $listBookingTicketNumber) !== false) {
							if($tk['ticket_type'] == 'baggage') $isIssueBaggage = true;

							if(isset($totalQtyTicket[$tk['ticket_type']])) $totalQtyTicket[$tk['ticket_type']] += $tk['qty'] ?? 1;
							else $totalQtyTicket[$tk['ticket_type']] = $tk['qty'] ?? 1;

							$totalQtyAllTicket += $tk['qty'] ?? 1;
						}
					}

					$totalFlightServiceFee = $totalBaggageServiceFee = 0;
					$totalServiceFee = 0;
					if($times < 1) {
						// Tickets and baggage are issued separately
						if($isIssueBaggage) {
							$totalFlightServiceFee  = $bookingInfo['total_amount'] - $bookingInfo['total_bought_amount'] - $bookingInfo['luggage_fee'];
							$totalBaggageServiceFee = $bookingInfo['luggage_fee'] - $totalBaggagePrice;
						}
						// Tickets and baggages are issued together
						else {
							$totalFlightServiceFee = $bookingInfo['total_amount'] - $bookingInfo['total_bought_amount'] - $totalBaggagePrice;
						}
					}
					else {
						// Get total service fee by difference between purchase price and selling price in receipt vouchers
						$totalServiceFee = $this->db->getOne("SELECT SUM(IFNULL(amount, 0)) - SUM(IFNULL(bought_amount, 0) + IFNULL(bought_amount2, 0) + IFNULL(bought_amount3, 0))
							FROM ec_receipt_voucher rv
							WHERE rv.booking_id = '{$bookingId}'
								AND rv.go_with = $times
								AND rv.rv_status != '0' 
								AND rv.deleted = 0") ?? 0;
					}

					$i = 0;
					$tongsl = $tongthanhtoan = 0;
					$listDoneTickets = [];
					foreach ($listAvailableTickets as $tkid => $tk) {
						if(array_search($tk['ticket_number'], $listBookingTicketNumber) === false) continue;

						$code = $this->getCodeDetail($tk['ticket_type'], $tk['itinerary']); // Item code

						$serviceFee = $serviceFee2 = 0;
						if($tk['ticket_type'] == 'flight') {
							$avgFlightServiceFee = $totalFlightServiceFee / $totalQtyTicket[$tk['ticket_type']];
							$serviceFee = $code == 'VMB_QT' ? 0 : $avgFlightServiceFee;
							$serviceFee2 = $code == 'VMB_QT' ? $avgFlightServiceFee : 0;
						}
						elseif($tk['ticket_type'] == 'ticketing_fee') {
							$serviceFee = $totalFlightServiceFee;
						}
						elseif($tk['ticket_type'] == 'baggage' && $times < 1) {
							$avgBaggageServiceFee = $totalBaggageServiceFee / $totalQtyTicket[$tk['ticket_type']];
							$serviceFee = $avgBaggageServiceFee;
						}
						else {
							$avgServiceFee = $totalServiceFee / $totalQtyAllTicket;
							$serviceFee = $avgServiceFee;
						}

						$taxRate = $tk['vat_per'];
						$divide = 1;
						if($taxRate == 0.08) $divide = 1.08;
						else if($taxRate == 0.1) $divide = 1.1;

						$outInvDetail = new EC_ChiTietHoaDon();
						$outInvDetail->id 			= '';
						$outInvDetail->name 		= trim(stripslashes($tk['ticket_number']));
						$outInvDetail->booking_id 	= $bookingId;
						$outInvDetail->booking 		= $bookingInfo['name'] ?? '';
						$outInvDetail->mahang 		= $code;
						$outInvDetail->soluong 		= $tk['qty'];
						$outInvDetail->phithuho 	= $tk['authorized_fee'] / $tk['qty'];
						$outInvDetail->phisanbay 	= 0;
						$outInvDetail->phikhac 		= 0;
						$outInvDetail->phidv 		= $serviceFee;
						$outInvDetail->giamua 		= $tk['total'] / $tk['qty'];
						$outInvDetail->thuesuat 	= $taxRate;
						$outInvDetail->dongia 		= ($outInvDetail->giamua + $serviceFee - $outInvDetail->phithuho) / $divide;
						$outInvDetail->tienthue 	= $outInvDetail->dongia * $taxRate * $tk['qty'];
						$outInvDetail->thanhtien 	= ($outInvDetail->dongia + $outInvDetail->phithuho) * $outInvDetail->soluong + $outInvDetail->tienthue;
						$outInvDetail->parent_id 	= $parentId;
						$outInvDetail->parent_type 	= 'EC_HoaDonBan';
						$outInvDetail->order_by_no 	= $i;
						$outInvDetail->ticket_number_id = $tkid;
						if($outInvDetail->mahang == 'PK') $outInvDetail->name = 'PK';
						if($outInvDetail->save()) {
							array_push($listDoneTickets, $tkid);
							$tongsl += $tk['qty'];
							$tongthanhtoan += $outInvDetail->thanhtien;
							$beanInInv->updateInventoryStatus($tkid);

							// Quốc tế tách riêng dòng phí DV
							if($code == 'VMB_QT') {
								$svTaxRate = 0.08;
								$svDivide = 1;
								if($svTaxRate == 0.08) $svDivide = 1.08;
								else if($svTaxRate == 0.1) $svDivide = 1.1;

								$svOutInvDetail = new EC_ChiTietHoaDon();
								$svOutInvDetail->id 		= '';
								$svOutInvDetail->name 		= 'PK';
								$svOutInvDetail->booking_id = $bookingId;
								$svOutInvDetail->booking 	= $bookingInfo['name'] ?? '';
								$svOutInvDetail->mahang 	= 'PK';
								$svOutInvDetail->soluong 	= 1;
								$svOutInvDetail->phithuho 	= 0;
								$svOutInvDetail->phisanbay 	= 0;
								$svOutInvDetail->phikhac 	= 0;
								$svOutInvDetail->phidv 		= $serviceFee2;
								$svOutInvDetail->giamua 	= 0;
								$svOutInvDetail->thuesuat 	= 0.08;
								$svOutInvDetail->dongia 	= ($svOutInvDetail->giamua + $serviceFee2 - $svOutInvDetail->phithuho) / $svDivide;
								$svOutInvDetail->tienthue 	= $svOutInvDetail->dongia * $taxRate * $outInvDetail->soluong;
								$svOutInvDetail->thanhtien 	= ($svOutInvDetail->dongia + $svOutInvDetail->phithuho) * $svOutInvDetail->soluong + $svOutInvDetail->tienthue;
								$svOutInvDetail->parent_id 	= $parentId;
								$svOutInvDetail->parent_type = 'EC_HoaDonBan';
								$svOutInvDetail->order_by_no = $i + 1;
								$svOutInvDetail->ticket_number_id = '';
								$svOutInvDetail->save();

								$tongsl += $svOutInvDetail->soluong;
								$tongthanhtoan += $svOutInvDetail->thanhtien;
							}
						}
						
						$i++;
					}
					$listAvailableTickets = array_diff_key($listAvailableTickets, array_flip($listDoneTickets));

					if($i * $tongsl * $tongthanhtoan != 0) {
						$dateModified = date('Y-m-d H:i:s', time() - 7*3600 + $additionalSecond);
						$sqlUpdate = "UPDATE ec_hoadonban
							SET tongsl = {$tongsl}
								,tongthanhtoan = {$tongthanhtoan}
								,date_modified = '{$dateModified}'
							WHERE id = '{$parentId}'";
						$this->db->query($sqlUpdate);
					}
					sleep(1);
				}
				catch(Exception $e) {
					global $sugar_config;
					$botToken   = $sugar_config['telegram']['bot_token'] ?? '';
					$chatId     = $sugar_config['telegram']['chat_id'] ?? '';
					$threadId   = $sugar_config['telegram']['thread_id_logs'] ?? '';
					$m = "<b>[ERROR] Exception when create auto output invoice</b>";
					$m .= "\n{$e->getMessage()} on line {$e->getLine()} with booking id $bookingId";
					Telegram::sendMessage($m, $botToken, $chatId, $threadId);
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Check tax code is invalid
	 * 
	 * @param string $tax_code
	 * @return bool
	 */
	public function checkTaxCode($tax_code) {
		if(strlen($tax_code) == 10 || (strlen($tax_code) == 14 && $tax_code[10] === '-')) return true;
		return false;
	}

	/**
	 * Get code item
	 * 
	 * @param string $ticket_type
	 * @param string $itinerary
	 * 
	 * @return string
	 */
	private function getCodeDetail($ticket_type, $itinerary = '') {
		$ticket_type = strtolower($ticket_type);
		switch ($ticket_type) {
			case 'flight':
				$isInter = false;
				$arr = explode("-", $itinerary);
				foreach($arr as $code) {
					if(!isset($GLOBALS['app_list_strings']['domestic_airport_list'][$code])) {
						$isInter = true;
						break;
					}
				}
				return $isInter ? 'VMB_QT' : 'VMB_QN';
			case 'baggage':
				return 'PHL';
			case 'exchange_fee':
				return 'PD';
			case 'seat':
				return 'PMG';
			default:
				return 'PK';
		}
	}
}
