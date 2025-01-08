<?php
class EC_Vouchers extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Vouchers';
    public $object_name = 'EC_Vouchers';
    public $table_name = 'ec_vouchers';
    public $importable = false;

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

	public $type;
	public $status;
	public $start_time;
	public $end_time;
	public $applied_date;
	public $campaign_name;
	public $campaign_id;
    public $reduce_amount;
    public $reduce_percent;
	public $max_discount;
	public $quantity;
	public $website;
	public $condition_voucher;
	public $booking_receive_id;
	public $account_name;
    public $account_phone;
    public $account_email;
    public $account_address;

	public $condition_apply = ['min_order_value', 'number_of_tickets', 'flight_type', 'ticket_type', 'journey'];
	public $condition_included = ['max_discount'];
	

    public function bean_implements($interface) {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

	public function save($check_notify = FALSE) {
		$voucher_type 		= $_POST['type'] ?? '';
		$campaign_name 		= $_POST['campaign_name'] ?? '';
		$reduce_amount 		= isset($_POST['reduce_amount']) && $_POST['reduce_amount'] > 0 ? preg_replace('/\D/', '', $_POST['reduce_amount']) : 0;
		$reduce_percent 	= isset($_POST['reduce_percent']) && $_POST['reduce_percent'] > 0 ? preg_replace('/\D/', '', $_POST['reduce_percent']) : 0;
		$max_discount 		= isset($_POST['max_discount']) ? preg_replace('/\D/', '', $_POST['max_discount']) : 0;
		$voucher_quantity 	= isset($_POST['voucher_qty']) ? preg_replace('/\D/', '', $_POST['voucher_qty']) : 0;
		$voucher_description = $_POST['voucher_description'] ?? '';
		$voucher_website	= $_POST['website'] ?? '';
		
		// Start time
		$start_date 	= isset($_POST['start_date']) ? date("Y-m-d", strtotime(str_replace("/", "-", $_POST['start_date']))) : "";
		$start_hour 	= isset($_POST['start_hour']) ? str_pad($_POST['start_hour'], 2, "0", STR_PAD_LEFT) : '00';
		$start_minute 	= isset($_POST['start_minute']) ? str_pad($_POST['start_minute'], 2, "0", STR_PAD_LEFT) : '00';
		$voucher_start_time = !empty($start_date) ? "$start_date $start_hour:$start_minute:00" : "";

		// End time
		$end_date 	= isset($_POST['end_date']) ? date("Y-m-d", strtotime(str_replace("/", "-", $_POST['end_date']))) : "";
		$end_hour 	= isset($_POST['end_hour']) ? str_pad($_POST['end_hour'], 2, "0", STR_PAD_LEFT) : '00';
		$end_minute = isset($_POST['end_minute']) ? str_pad($_POST['end_minute'], 2, "0", STR_PAD_LEFT) : '00';
		$voucher_end_time = !empty($end_date) ? "$end_date $end_hour:$end_minute:00" : "";

		// Condition voucher
		$conditions = [];
		foreach($this->condition_apply as $con) {
			if(isset($_POST[$con])) {
				// String
				if(in_array($con, ['flight_type', 'journey'])) {
					if(empty($_POST[$con])) continue;
					$conditions[$con] = trim($_POST[$con]);
				}
				// Number
				else {
					$conditions[$con] = (int)preg_replace('/\D/', '', $_POST[$con]);
				}
			}
		}
		$voucher_condition = json_encode($conditions);

		// Random campaign id when not using Campaign module
		$campaign_id = $this->generateUniqueId();

		global $current_user;
		if($voucher_type == 'group') {
			$voucher_code = isset($_POST['voucher_code']) ? strtoupper(myRemoveUnicodeChars(trim($_POST['voucher_code']))) : '';

			if(!$this->isVoucherExisted($voucher_code)) {
				if(!empty($voucher_code)) $this->name = $voucher_code;
				else {
					$prefix = dechex($this->countCampaign($voucher_type) + 1);
					$r = $this->generateRandomString(6);
					$suffix = 'G' . $this->countVoucher($voucher_type);
					$this->name = strtoupper($prefix . $r . $suffix);
				}
				$this->campaign_name 	= $campaign_name;
				$this->campaign_id 		= $this->generateUniqueId();
				$this->website 			= $voucher_website;
				if($reduce_amount > 0) $this->reduce_amount = $reduce_amount;
				elseif($reduce_percent > 0) $this->reduce_percent = $reduce_percent;
				$this->max_discount 	= $max_discount;
				$this->start_time 		= $voucher_start_time;
				$this->end_time 	 	= $voucher_end_time;
				$this->quantity 		= $voucher_quantity;
				$this->condition_voucher = $voucher_condition;
				$this->description 		= $voucher_description;
				$this->status 			= 'new';
				$this->assigned_user_id = $current_user->id;
				parent::save();

				// if(!is_null($this->id) && !empty($this->id)) {
				// 	$this->uploadWebsite($this->website, [
				// 		'name' => $this->name,
				// 		'status' => $this->status,
				// 		'campaign_name' => $this->campaign_name,
				// 		'campaign_id' => $this->campaign_id,
				// 		'validate_from_date' => $this->validate_from_date,
				// 		'validate_to_date' => $this->validate_to_date,
				// 		'reduce_amount' => $this->reduce_amount,
				// 		'reduce_percent' => $this->reduce_percent,
				// 		'max_discount' => $this->max_discount,
				// 		'quantity' => $this->quantity,
				// 		'quantity_used' => 0,
				// 		'condition_voucher' => $this->condition_voucher
				// 	]);
				// }
			}
		}
		elseif($voucher_type == 'single') {
			$prefix = dechex($this->countCampaign($voucher_type) + 1);

			$Voucher = new EC_Vouchers();
			for ($i = 0; $i < $voucher_quantity; $i++) {
				$r = $this->generateRandomString(6);
				$suffix = "S$i";

				$Voucher->id = '';
				$Voucher->name 				= strtoupper($prefix . $r . $suffix);
				$Voucher->type				= $voucher_type;
				$Voucher->campaign_name		= $campaign_name;
				$Voucher->campaign_id 		= $campaign_id;
				if($reduce_amount > 0) $Voucher->reduce_amount = $reduce_amount;
				elseif($reduce_percent > 0) $Voucher->reduce_percent = $reduce_percent;
				$Voucher->max_discount 		= $max_discount;
				$Voucher->status 			= 'pending';
				$Voucher->start_time 		= $voucher_start_time;
				$Voucher->end_time 			= $voucher_end_time;
				$Voucher->quantity 			= 1;
				$Voucher->condition_voucher = $voucher_condition;
				$Voucher->description 		= $voucher_description;
				$Voucher->assigned_user_id 	= $current_user->id;
				$Voucher->save2();
			}

			header("Location: index.php?module=$this->module_dir&action=index");
			exit();
		}

		header("Location: index.php?module=$this->module_dir&action=index");
		exit();
	}

	public function save2() {
		parent::save();
	}

	function get_list_view_data() {
		$task_fields = $this->get_list_view_array();

		// Time
		if($this->start_time && $this->end_time) $task_fields['END_TIME'] = date('d/m/Y H:i', strtotime($this->start_time) - 7*3600) . ' &#11157; '. date('d/m/Y H:i', strtotime($this->end_time) - 7*3600);
		else $task_fields['END_TIME'] = '';

		// Status
		$task_fields['STATUS'] = $this->formatStatus();

		return $task_fields;
	}

	/**
	 * Upload voucher to website
	 * 
	 * @param string $website
	 * @param array $voucher_info
	 * @return string JSON
	 */
	public function uploadWebsite($website, $voucher_info) {
		$url = "https://$website/voucher";
		$voucher_info['action'] = 'add_voucher';
		$voucher_info['api_key'] = "m2-cVyHv2+7oyzyfU8+4c2MVARL+OwvxE48h6n846pGfe3rf52";

		try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $voucher_info);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
			curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			curl_setopt($curl, CURLOPT_DNS_CACHE_TIMEOUT, 60);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($curl, CURLOPT_TIMEOUT, 40);
            $json = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$errorno = curl_errno($curl);
			$error = curl_error($curl);

			if ($json === false || $errorno) {
				return json_encode(['error' => 1, 'code' => 500, 'message' => "$errorno: $error"]);
			}

			if($httpcode == 200) return $json;
			return json_encode(['error' => 1, 'code' => $httpcode, 'message' => "Error", 'data' => $json]);
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
	}

	/**
	 * Check voucher code is exist
	 * 
	 * @param string $code
	 * @return bool
	 */
	public function isVoucherExisted($code) {
		$sql = "SELECT COUNT(id) FROM ec_vouchers WHERE name = '$code'";
		$count = $this->db->getOne($sql);
		return $count > 0;
	}

	/**
	 * Count voucher by type
	 * 
	 * @param string $type
	 * @return int
	 */
	public function countVoucher($type) {
		$sql = "SELECT COUNT(id) FROM ec_vouchers WHERE type='$type'";
		return $this->db->getOne($sql);
	}

	/**
	 * Count campaign ID voucher by type
	 * 
	 * @param string $type
	 * @return int
	 */
	public function countCampaign($type) {
		$sql = "SELECT COUNT(DISTINCT campaign_id) FROM ec_vouchers WHERE type='$type'";
		return $this->db->getOne($sql) ;
	}

	/**
	 * Random string
	 * 
	 * @param string $length
	 * @return string
	 */
	public function generateRandomString($length = 10) {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	/**
	 * Random ID
	 * 
	 * @return string ID
	 */
	public function generateUniqueId() {
		// Generate a unique ID
		$uniqueId = uniqid(bin2hex(random_bytes(16)), true);
		// Trim to 36 characters
		return substr($uniqueId, 0, 36);
	}

	/**
	 * Get HTML of format status
	 * 
	 * @return string HTML
	 */
	public function getFormatStatus() {
		$status = '';
		$status_name = $GLOBALS['app_list_strings']['voucher_status_list'][$this->status];
		if($this->status == 'pending') {
			$status = '<span class="text-primary fst-italic">'.$status_name.'...</span>';
		}
		elseif($this->status == 'done') {
			$status = '<span class="text-success fw-bold">'.$status_name.'</span>';
		}
		elseif($this->status == 'expired') {
			$status = '<span class="text-secondary">'.$status_name.'</span>';
		}
		elseif($this->status == 'done') {
			$status = '<span class="text-danger fw-bold">'.$status_name.'</span>';
		}
		return $status;
	}

	public function formatStatus($status = '') {
		if(empty($status)) $status = $this->status;

		switch ($status) {
			case 'new':
				return '<b class="text-dark">'.$GLOBALS['app_list_strings']['voucher_status_list'][$status].'</b>';
			case 'pending':
				return '<b class="text-warning">'.$GLOBALS['app_list_strings']['voucher_status_list'][$status].'</b>';
			case 'done':
				return '<b class="text-success">'.$GLOBALS['app_list_strings']['voucher_status_list'][$status].'</b>';
			case 'expired':
				return '<b class="text-secondary">'.$GLOBALS['app_list_strings']['voucher_status_list'][$status].'</b>';
			case 'cancel':
				return '<b class="text-danger">'.$GLOBALS['app_list_strings']['voucher_status_list'][$status].'</b>';
			default:
				return $status;
		}
	}
}