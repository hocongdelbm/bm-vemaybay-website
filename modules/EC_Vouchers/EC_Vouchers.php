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

    public $status;
    public $applied_date;
    public $validate_from_date;
    public $validate_to_date;
    public $account_name;
    public $account_address;
    public $account_phone;
    public $account_email;
    public $reduce_amount;
    public $reduce_percent;
	public $campaign_name;
	public $campaign_id;
	public $website;
	public $max_discount;
	public $quantity;
	public $condition_voucher;
	public $booking_receive_id;
	public $booking;

    public function bean_implements($interface) {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

    public function save($check_notify = FALSE) {
		global $current_user;

		// Get data
		$type 				= isset($_POST['type']) ? $_POST['type'] : '';
		$campaign_name 		= isset($_POST['campaign_name']) ? $_POST['campaign_name'] : '';
		$voucher_code 		= isset($_POST['voucher_code']) ? strtoupper(myRemoveUnicodeChars(trim($_POST['voucher_code']))) : '';
		$website 			= isset($_POST['website']) ? $_POST['website'] : '';
		$reduce_amount 		= isset($_POST['reduce_amount']) ? $_POST['reduce_amount'] : 0;
		$reduce_percent 	= isset($_POST['reduce_percent']) ? $_POST['reduce_percent'] : 0;
		$max_discount 		= isset($_POST['max_discount']) ? $_POST['max_discount'] : 0;
		$from_date 			= isset($_POST['from_date']) ? date("Y-m-d", strtotime(str_replace("/", "-", $_POST['from_date']))) : '';
		$to_date 			= isset($_POST['to_date']) ? date("Y-m-d", strtotime(str_replace("/", "-", $_POST['to_date']))) : '';
		$quantity 			= isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
		$description 		= isset($_POST['description']) ? $_POST['description'] : '';

		// Validate
		if(empty($type) || empty($campaign_name) || empty($from_date) || empty($to_date)) {
			echo '
				<p class="text-danger"><b>ERROR:</b> Thông tin phát hành voucher không hợp lệ</p>
				<a class="btn btn-primary" href="index.php?module='.$this->module_dir.'&action=createvouchers">Quay lại</a>
			';
			exit();
		}

		// Format voucher condition
		$condition_voucher  = [];
		$condition_field 	= isset($_POST['field']) ? $_POST['field'] : [];
		$condition_operator = isset($_POST['operator']) ? $_POST['operator'] : [];
		$condition_value 	= isset($_POST['value']) ? $_POST['value'] : [];
		for($i = 0; $i < count($condition_field); $i++){
			$condition_voucher[]= [
				"field" 	=> $condition_field[$i],
				"operator"	=> $condition_operator[$i],
				"value" 	=> preg_replace('/[,\s]+/', '', $condition_value[$i])
			];
		}

		// Release
		if($type == 'group') {
			if(!$this->isVoucherExisted($voucher_code)) {
				if(!empty($voucher_code)) $this->name = $voucher_code;
				else {
					$prefix = dechex($this->countCampaign($type) + 1);
					$r = $this->generateRandomString(6);
					$suffix = 'G' . $this->countVoucher($type);
					$this->name = strtoupper($prefix . $r . $suffix);
				}
				$this->campaign_name = $campaign_name;
				$this->campaign_id = $this->generateUniqueId();
				$this->website = $website;
				if($reduce_amount > 0) $this->reduce_amount = $reduce_amount;
				elseif($reduce_percent > 0) $this->reduce_percent = $reduce_percent;
				$this->max_discount = $max_discount;
				$this->validate_from_date = $from_date;
				$this->validate_to_date = $to_date;
				$this->quantity = $quantity;
				$this->condition_voucher = json_encode($condition_voucher);
				$this->description = $description;
				$this->assigned_user_id = $current_user->id;
				parent::save();

				if(!is_null($this->id) && !empty($this->id)) {
					$this->uploadWebsite($this->website, [
						'name' => $this->name,
						'status' => $this->status,
						'campaign_name' => $this->campaign_name,
						'campaign_id' => $this->campaign_id,
						'validate_from_date' => $this->validate_from_date,
						'validate_to_date' => $this->validate_to_date,
						'reduce_amount' => $this->reduce_amount,
						'reduce_percent' => $this->reduce_percent,
						'max_discount' => $this->max_discount,
						'quantity' => $this->quantity,
						'quantity_used' => 0,
						'condition_voucher' => $this->condition_voucher
					]);
				}
			}
		}
		elseif($type == 'single') {
			$campaign_id = $this->generateUniqueId();
			$prefix = dechex($this->countCampaign($type) + 1);
			$Voucher = new EC_Vouchers();

			for ($i = 0; $i < $quantity; $i++) {
				$r = $this->generateRandomString(6);
				$suffix = "S$i";

				$Voucher->id = '';
				$Voucher->name = strtoupper($prefix . $r . $suffix);
				$Voucher->campaign_name	= $campaign_name;
				$Voucher->campaign_id = $campaign_id;
				if($reduce_amount > 0) $Voucher->reduce_amount = $reduce_amount;
				elseif($reduce_percent > 0) $Voucher->reduce_percent = $reduce_percent;
				$Voucher->max_discount = $max_discount;
				$Voucher->validate_from_date = $from_date;
				$Voucher->validate_to_date = $to_date;
				$Voucher->quantity = 1;
				$Voucher->condition_voucher = json_encode($condition_voucher);
				$Voucher->description = $description;
				$Voucher->assigned_user_id 	= $current_user->id;
				$Voucher->save2();
			}

			header('index.php?module='.$this->module_dir.'&action=index'); // Return list view
			exit();
		}
	}

	/**
	 * Use this function to avoid recursion
	 */
	public function save2() {
		parent::save();
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
	 * Count campaign ID voucher by type
	 * 
	 * @param string $type
	 * @return int
	 */
	public function countCampaign($type) {
		$sql = "SELECT COUNT(DISTINCT campaign_id)
				FROM ec_vouchers
				WHERE type='$type'";
		return $this->db->getOne($sql) ;
	}

	/**
	 * Count voucher by type
	 * 
	 * @param string $type
	 * @return int
	 */
	public function countVoucher($type) {
		$sql = "SELECT COUNT(id)
				FROM ec_vouchers
				WHERE type='$type'";
		return $this->db->getOne($sql);
	}

	/**
	 * Check voucher code is exist
	 * 
	 * @param string $code
	 * @return bool
	 */
	public function isVoucherExisted($code) {
		$sql = "SELECT COUNT(id) 
				FROM ec_vouchers 
				WHERE name = '$code'";
		$count = $this->db->getOne($sql);
		return $count > 0;
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
}