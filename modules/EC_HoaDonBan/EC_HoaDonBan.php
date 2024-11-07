<?php

class EC_HoaDonBan extends Basic
{
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
	public $pt_thanhtoan;

	public function bean_implements($interface)
	{
		switch ($interface) {
			case 'ACL':
				return true;
		}

		return false;
	}

	function save($check_notify = FALSE)
	{

		if (empty($this->name)) {
			$this->name = 'HD-' . date('ymd') . '-' . $this->countVoucher();
		}

		// if (
		// 	isset($_POST['sohoadon']) && !empty($_POST['sohoadon']) && isset($_POST['kyhieuhd']) && !empty($_POST['kyhieuhd'])
		// 	&& myCheck2ValueExist('EC_HoaDonBan', 'sohoadon', $_POST['sohoadon'], 'kyhieuhd', $_POST['kyhieuhd'], $this->id)
		// ) {
		// 	header("Location: index.php?module=EC_HoaDonBan&action=Error&error_string=" . urlencode("Số hóa đơn <" . $_POST['sohoadon'] . "> ký hiệu <" . $_POST['kyhieuhd'] . "> đã bị trùng trong danh sách nhập. Vui lòng kiểm tra lại."));
		// 	exit();
		// }

		parent::save($check_notify);

		if (
			(isset($_POST['ct_ticket_number_id']) && !empty($_POST['ct_ticket_number_id'])
				&& isset($_POST['ct_ticket_number']) && !empty($_POST['ct_ticket_number']) && $this->loaihoadon == 0) || ($this->loaihoadon == 1 && !empty($_POST['ct_name']))
		) {
			$this->saveListItems();
		}
	}

	function countVoucher() {
		$sql = 'SELECT COUNT(id)
			FROM ec_hoadonban 
			WHERE DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") = "' . date('Y-m-d') . '"';
		return str_pad(($this->db->getOne($sql) + 1), 3, 0, STR_PAD_LEFT);
	}

	function saveListItems() {
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
			$cthd->deleted 	= $_POST['ct_deleted'][$i];
			$cthd->order_by_no 	= $i;

			if ($this->loaihoadon == 0) {
				$cthd->name = trim(stripslashes($_POST['ct_ticket_number'][$i]));

				if($cthd->mahang == 'PK'){
					$cthd->name = 'PK';
				}

				$cthd->ticket_number_id = $_POST['ct_ticket_number_id'][$i];
				$cthd->booking_id = $_POST['ct_booking_id'][$i];
				$cthd->booking = $_POST['ct_booking'][$i];

			}
			else if ($this->loaihoadon == 1) {
				$cthd->name = trim(stripslashes($_POST['ct_name'][$i]));
			}

			if ($cthd->deleted == 1) {
				$cthd->mark_deleted($cthd->id);
			}
			else {
				if (!empty($cthd->name)) {
					$cthd->save();
				}
			}

			if (!empty($cthd->ticket_number_id)) {
				// kiếm tra lại số tồn của số vé nếu hết thì đánh dấu
				$sql_upd = '
					UPDATE ec_input_invoices
					SET out_of_stock = IF((qty - (SELECT SUM(soluong) FROM ec_chitiethoadon WHERE deleted = 0 AND ticket_number_id = "' . $_POST['ct_ticket_number_id'][$i] . '")) > 0, 0, 1) WHERE id = "' . $_POST['ct_ticket_number_id'][$i] . '"
					';

				$this->db->query($sql_upd);
			}
		}
	}
}
