<?php

class EC_Vouchers extends Basic
{
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
    // public $reduce_percent;


	
    public function bean_implements($interface)
    {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

    function save($check_notify = FALSE) {
		global $current_user;

		if(empty($this->assigned_user_id)) $this->assigned_user_id = $current_user->id;

		if($this->status == '') $this->status = 'new';

		if(!isset($_POST['createMultipleVoucher'])) {
			if(!empty($this->name))
				parent::save();
		} else {
			$this->createMultipleVouchers();
		}
	}

	function save2() {
		parent::save();
	}

	function createMultipleVouchers() {
		global $current_user;

		$no 				= (int)$this->countVoucher();
		$qty 			= unformat_number($_POST['voucher_qty']); 
		$price 			= unformat_number($_POST['voucher_price']); 
		$prefix 			= isset($_POST['voucher_code']) ? trim(strtoupper($_POST['voucher_code'])) : ''; 
		$campaign_name 	= trim($_POST['campaign_name']); 
		$campaign_id 		= $this->generateRandomString();

		$condition_voucher  = array();
		$condition_field 	= isset($_POST['field']) ? $_POST['field'] : array();
		$condition_operator = isset($_POST['operator']) ? $_POST['operator'] : '';
		$condition_value 	= isset($_POST['value']) ? $_POST['value'] : '';

		if(count($condition_field) > 0){
			for($i = 0; $i < count($condition_field); $i++){
				$item = array(
					"field" 		=> $condition_field[$i],
					"operator"	=> $condition_operator[$i],
					"value" 		=> preg_replace('/[,\s]+/', '', $condition_value[$i])
				);
	
				$condition_voucher[] = $item;
			}	
		}

		for($i = 0; $i < $qty; $i++) {
			$voucher = new EC_Vouchers;
			do {
				$voucher->name = $prefix.strtoupper(substr(sha1(mt_rand()),17,6));
				$is_dup 		= $this->checkDuplicate($voucher->name);

			} while($is_dup);
				$voucher->condition_voucher 	= json_encode($condition_voucher);
				$voucher->campaign_name 		= $campaign_name;
				$voucher->campaign_id 		= $campaign_id;
				$voucher->description 		= $_POST['voucher_description'];
				$voucher->assigned_user_id 	= $current_user->id;
				$voucher->reduce_amount 		= $price;
				$voucher->validate_from_date 	= $_POST['from_date'];
				$voucher->validate_to_date 	= $_POST['to_date'];
				$voucher->status 			= 'new';
				$voucher->order_by_no 		= $no;
				$voucher->save2();
				$no++;
		}

		header("Location: index.php?module=EC_Vouchers&action=createVouchers&qty=".$qty."&prefix=".$prefix."&campaign_name=".$campaign_name."&price=".$price."&no=".($no-1));
		exit;
	}

	function countVoucher() {
		$sql = 'SELECT COUNT(*) FROM ec_vouchers';
		return $this->db->getOne($sql);
	}

	function checkDuplicate($name) {
		$sql = 'SELECT COUNT(id) 
				FROM ec_vouchers 
				WHERE name = "' . $name . '"
				LIMIT 1';
		return $this->db->getOne($sql);
	}

	// Random campaign_id
	function generateRandomString($length = 36) {
		return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
	}
	
}