<?php
class EC_Payment_Voucher extends Basic
{
	public $new_schema = true;
	public $module_dir = 'EC_Payment_Voucher';
	public $object_name = 'EC_Payment_Voucher';
	public $table_name = 'ec_payment_voucher';
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

	public $pv_notes;
	public $receipent_name;
	public $receipent_phone;
	public $amount;
	public $amount_type;
	public $pv_number;
	public $receipent_address;
	public $ec_payment_types_id_c;
	public $payment_type;

	public $com_location_id;
	public $employee_id;
	public $pv_status;
	public $hoanve_id;
	public $phieuthu_id;

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

		// KIEM TRA QUYEN CAP NHAT HANG LOAT
		if (isset($_POST['massupdate']) && $_POST['massupdate'] == 'true' && !ACLController::checkAccess('Bugs', 'view', true)) {
			header('Location: index.php?module=' . $this->module_dir . '&action=Error&error_string=' . urlencode('Bạn không được quyền sử dụng tính năng này'));
			exit();
		}

		$is_tele = 0;
		if (empty($this->id)) {
			// PC-230916-0001
			$number = 0;
			$number = $this->db->getOne("SELECT COUNT(id) + 1 FROM ec_payment_voucher WHERE DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), '%Y-%m-%d') = '" . date('Y-m-d') . "'");
			$this->name = 'PC-' . date('ymd') . '-' . str_pad($number, 4, 0, STR_PAD_LEFT);
			$is_tele = 1;
		}

		if (isset($_POST['ngayhachtoan']) && !empty($_POST['ngayhachtoan'])) {
			// $this->ngayhachtoan = date('d-m-Y H:i', strtotime($_POST['ngayhachtoan']) - 7 * 3600);
			$this->ngayhachtoan = date('Y-m-d H:i:s', strtotime($_POST['ngayhachtoan']));
		}


		if (empty($this->supplier_id) && $this->ec_payment_types_id_c == '1a72bc2f-d907-0ec9-853d-5572901455b5') {
			header('Location: index.php?module=' . $this->module_dir . '&action=Error&error_string=' . urlencode('Bạn chưa nhập đối tượng cho loại chi Xuất vé KM'));
			exit;
		}

		// nếu là phiếu chi hoàn vé, thì tổng tiền chi <= tiền hoàn khách
		// kt khi phiếu chi chuyển sang trạng thái đã chi
		if (!empty($this->hoanve_id) && $this->pv_status == 3) {
			$ret_res = $this->checkTotalReturn();
			if ($ret_res['error']) {
				header('Location: index.php?module=' . $this->module_dir . '&action=Error&pv=' . $this->id . '&error_string=' . urlencode('Số tiền chi tối đa: ' . format_number($ret_res['max_paid'])));
				exit;
			}
		}

		// Kiểm tra Phiếu HV "Đã hoàn" thì mới tạo PC
		if (!empty($this->hoanve_id) &&  $this->ec_payment_types_id_c == '7a7abc9b-0925-bdb7-d92b-526e3c5c0c14') {
			$hv = new EC_HoanVe;
			$hv->retrieve($this->hoanve_id);
			if ((int)$hv->tinhtrang != 1) {
				header('Location: index.php?module=' . $this->module_dir . '&action=Error&error_string=' . urlencode('Phiếu Hoàn vé phải ở trạng thái "đã hoàn". Vui lòng kiểm tra lại'));
				exit;
			}
		}

		parent::save($check_notify);

		if ($this->pv_status == '3') {
            if (!isWorkingProcessExisting($this->module_dir, $this->id, 'create_payment')) {
				myCreateWorkingProcess($this->module_dir, $this->id, $this->name, $this->description . ' (PC)', $this->created_by, 'create_payment');
			}
		} else {
			myRemoveWorkingProcess($this->module_dir, $this->id, 'create_payment');
		}
		
		// SEND TELE
		if ($is_tele == 1) {
			$date_entered = date('H:i:s d-m-Y', strtotime('+7 hours', strtotime($this->date_entered)));
			$user_list = get_user_array(true, '', '', true);

			try {
				$notiChannel = $sugar_config['notification_channel'] ?? '';

				if($notiChannel == 'Telegram') {
					$message = "- Phiếu chi: " . $this->name .
						"\n- Loai chi: " . $this->payment_type .
						"\n- Ngày tạo: " . $date_entered . " bởi " . $user_list[$this->created_by] .
						"\n- Số tiền: " . format_number($this->amount) . " VNĐ" .
						"\n- Nội dung: " . $this->description;
					$messageData = [
						'text' => html_entity_decode($message, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
						'parse_mode' => 'HTML',
						'reply_markup' => [
							'inline_keyboard' => [
								[
									[
										'text' => 'Phiếu chi',
										'url' => $sugar_config['site_url'] . "/index.php?module=$this->object_name&record=$this->id&action=DetailView&dothis=true",
									],
								],
							],
						]
					];
					$botToken = $sugar_config['telegram']['accounting']['bot_token'] ?? '';
					$chatId = $sugar_config['telegram']['accounting']['chat_id'] ?? '';
					Telegram::sendMessageData(json_encode($messageData), $botToken, $chatId);
				}
				elseif($notiChannel == 'Mattermost') {
					$text = "- Loại chi: **$this->payment_type**";
					$text .= "\n- Ngày tạo: **$date_entered** bởi **" . $user_list[$this->created_by] . "**";
					$text .= "\n- Số tiền: **". format_number($this->amount) ." VNĐ**";
					$text .= "\n- Nội dung: $this->description";
					$props = [
						"attachments" => [
							[
								"color" => "#fcc00d",
								"title" => "Phiếu chi $this->name",
								"title_link" => $sugar_config['site_url'] . "/index.php?module=$this->object_name&action=DetailView&record=$this->id",
								"text" => $text,
							]
						]
					];
					Mattermost::sendMessage($sugar_config['mattermost']['channel_id_accounting'] ?? '', '', $props);
				}
			}
			catch(Throwable $th) {}
		}
	}

	function checkTotalReturn()
	{
		$ret_voucher = new EC_HoanVe;
		$ret_voucher->retrieve($this->hoanve_id);
		$sql = '
			SELECT SUM(IFNULL(amount, 0))
			FROM ec_payment_voucher
			WHERE deleted = 0 AND pv_status = 3
			AND hoanve_id = "' . $this->hoanve_id . '"';
		$paid = $this->db->getOne($sql);
		$left_amt = (int)$ret_voucher->tongtienkhach - $paid;
		if ($left_amt < 0) $left_amt = 0;
		if ((int)$ret_voucher->tongtienkhach < ($this->amount + $paid)) {
			return array('error' => 1, 'max_paid' =>  $left_amt);
		}
		return '';
	}
}
