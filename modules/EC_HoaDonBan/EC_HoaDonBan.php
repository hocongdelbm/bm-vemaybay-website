<?php

use PhpParser\Node\Expr\Empty_;

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
	public $diachi;
	public $masothue;
	public $ngaychungtu;
	public $ngayhachtoan;
	public $loaihoadon;
	public $hinhthuchoadon;
	public $ngayhoadon;
	public $loaitien;
	public $booking_id;
	public $booking;
	public $phieuthu_id;
	public $phieuthu;
	public $thuesuat;
	public $thuevat;
	public $currency_id;
	public $thuephikhac;
	public $tongtien;
	public $loaichungtu_id;
	public $loaichungtu;
	// public $pt_thanhtoan;
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

	public function bean_implements($interface) {
		switch ($interface) {
			case 'ACL':
				return true;
		}

		return false;
	}

	public function save($check_notify = FALSE) {
		if(empty($this->name)) $this->name = 'HD-' . date('ymd') . '-' . $this->countVoucher();
		if(!$this->kyhieuhd || empty($this->kyhieuhd)) $this->kyhieuhd = $this->genInvSerial();

		$identity_number = trim($_POST['identity_number'] ?? '');
		if(!empty($identity_number)) {
			if(strlen($identity_number) == 12) $this->citizen_id = $identity_number;
			elseif(strlen($identity_number) > 6) $this->passport_number = $identity_number;
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

	public function countVoucher() {
		$sql = 'SELECT COUNT(id)
			FROM ec_hoadonban 
			WHERE DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"';
		return str_pad(($this->db->getOne($sql) + 1), 3, 0, STR_PAD_LEFT);
	}

	public function saveListItems() {
		$row_count = count($_POST['ct_detail_id']);
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
			$cthd->deleted 		= (int)$_POST['ct_deleted'][$i];
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
				// kiếm tra lại số tồn của số vé nếu hết thì đánh dấu
				$sql_upd = 'UPDATE ec_input_invoices
					SET out_of_stock = IF((qty - (SELECT SUM(soluong)
					FROM ec_chitiethoadon
					WHERE deleted = 0 AND ticket_number_id = "' . $_POST['ct_ticket_number_id'][$i] . '")) > 0, 0, 1) WHERE id = "' . $_POST['ct_ticket_number_id'][$i] . '"';

				$this->db->query($sql_upd);
			}
		}
	}

	public function genInvSerial() {
		$y = date('y');
		if(isset($this->loaikh)) {
			return $this->loaikh == '0' ? "C{$y}THV" : "C{$y}MHV";
		}
		return "C{$y}MHV";
	}
}
