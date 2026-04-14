<?php


class EC_HoanVe extends Basic
{
	public $new_schema = true;
	public $module_dir = 'EC_HoanVe';
	public $object_name = 'EC_HoanVe';
	public $table_name = 'ec_hoanve';
	public $importable = true;

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

	public $ngaychungtu;
	public $booking_id;
	public $booking;
	public $tinhtrang;
	public $tongtienhang;
	public $tongtienkhach;
	public $tongtiendv;

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
		global $current_user, $sugar_config;

		if (isset($_POST['booking_id']) && !empty($_POST['booking_id']) && !$this->checkBooking($_POST['booking_id'])) {
			header('Location: index.php?module=EC_HoanVe&action=Error&error_string=' . urlencode('Booking này chưa xuất vé hoặc chưa hoàn tất'));
			exit();
		
		}
		$is_tele = 0;
		if (empty($this->id)) {
			// HV-230916-0001
			// $where = ' date_entered = "' . date('Y-m-d') . '" ';
			// $this->name = 'HV-' . date('ymd') . '-' . myAutoGenerateName('EC_HoanVe', $where, 4);
			
			$total_row = $this->db->getOne("SELECT COUNT(id) + 1 FROM ec_hoanve");
			$this->name = 'HV-' . date('ymd') . '-' . $total_row;
			$is_tele = 1;
		}

		parent::save($check_notify);

		if ($is_tele == 1) {
			// GHI NHẬN KPI CHO NGƯỜI TẠO
			myCreateWorkingProcess($this->module_dir, $this->id, $this->name, $this->description, $current_user->id, 'create_repaid');
		}

		if (
			isset($_POST['ct_hoten']) && !empty($_POST['ct_hoten'])
			&& isset($_POST['ct_airline_code']) && !empty($_POST['ct_airline_code'])
			&& isset($_POST['ct_noidi']) && !empty($_POST['ct_noidi'])
			&& isset($_POST['ct_noiden']) && !empty($_POST['ct_noiden'])
		) {
			$this->saveListItems();
		}
	}

	function saveListItems()
	{
		$row_count = count($_POST['ct_hoten']);
		for ($i = 0; $i < $row_count; $i++) {
			$c = new EC_ChiTietHoanVe();
			$c->id 			= $_POST['ct_detail_id'][$i];
			$c->name 			= trim(stripslashes($_POST['ct_hoten'][$i]));
			$c->loaihk 		= $_POST['ct_loaihk'][$i];
			$c->danhxung 		= $_POST['ct_danhxung'][$i];
			$c->ngaysinh 		= $_POST['ct_ngaysinh'][$i];
			$c->chieubay 		= $_POST['ct_chieubay'][$i];
			$c->airline_code 	= $_POST['ct_airline_code'][$i];
			$c->noidi 		= trim(stripslashes($_POST['ct_noidi'][$i]));
			$c->noiden 		= trim(stripslashes($_POST['ct_noiden'][$i]));
			$c->sove 			= trim(stripslashes($_POST['ct_sove'][$i]));
			$c->pnr 			= trim(stripslashes($_POST['ct_pnr'][$i]));
			$c->sotienhang 	= unformat_number($_POST['ct_sotienhang'][$i]);
			$c->sotienkhach 	= unformat_number($_POST['ct_sotienkhach'][$i]);
			$c->phidichvu 		= unformat_number($_POST['ct_phidichvu'][$i]);
			$c->hoanve_id 		= $this->id;
			$c->nhacc_id 		= $_POST['ct_nhacc_id'][$i];
			$c->dahoan 		= $_POST['ct_dahoan'][$i];
			$c->deleted 		= $_POST['ct_deleted'][$i];

			if ($c->deleted == 1) {
				$c->mark_deleted($c->id);
			} else {
				if (!empty($c->name)) {
					$c->save();
				}
			}
		}
	}

	function checkBooking($booking_id)
	{
		$sql = "SELECT booking_status
				FROM ec_flight_bookings 
				WHERE deleted=0 AND id='" . $booking_id . "' ";
		$booking_status = $this->db->getOne($sql);
		if ($booking_status == '7' || $booking_status == '8')
			return true;
		return false;
	}

	public function create_new_list_query(
		$order_by,
		$where,
		$filter = array(),
		$params = array(),
		$show_deleted = 0,
		$join_type = '',
		$return_array = false,
		$parentbean = null,
		$singleSelect = false,
		$ifListForExport = false
	) {
		$ret = parent::create_new_list_query(
			$order_by,
			$where,
			$filter,
			$params,
			$show_deleted,
			$join_type,
			true,
			$parentbean,
			$singleSelect,
			$ifListForExport
		);

		$ret['select'] .= ", (
			SELECT GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ')
			FROM ec_chitiethoanve c
			INNER JOIN accounts a ON a.id = c.nhacc_id
				AND a.deleted = 0
				AND a.account_type = 'Supplier'
				AND a.is_stop_tracking = 0
			WHERE c.deleted = 0 AND c.hoanve_id = {$this->table_name}.id
		) AS nhacc_list";

		if ($return_array) {
			return $ret;
		}

		return $ret['select'] . $ret['from'] . $ret['where'] . $ret['order_by'];
	}
}
