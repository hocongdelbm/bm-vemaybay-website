<?php

class EC_Debts extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Debts';
    public $object_name = 'EC_Debts';
    public $table_name = 'ec_debts';
    public $importable = true;

	public $disable_row_level_security = true ; // to ensure that modules created and deployed under CE will continue to function under team security if the instance is upgraded to PRO

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
    
    public $debt_amount;
    public $currency_id;
    public $remain_amount;
    public $paid_amount;
    public $date_limit;
    public $booking_id;
    public $booking;
    public $contact_name;
    public $phone_office;
    public $phone_fax;
    public $phone_mobile;
    public $email;
    public $address;
    public $account_id;
    public $supplier;
    public $debt_status;
    public $debt_type;
    public $ngayhachtoan;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    function save($check_notify = FALSE){
		if(empty($this->name)){
			$this->name = 'CN'.$this->getNumberOfDebts();
		}
		$this->ngayhachtoan = date('Y-m-d H:i', strtotime($this->ngayhachtoan));
		parent::save($check_notify);
	}
	
	function save2($check_notify = FALSE){
		parent::save($check_notify);
	}
	
	// đếm số lượng công nợ trong 1 tháng
	function getNumberOfDebts(){
		$sql = "SELECT COUNT(id)
				FROM ec_debts ";
		$count_debt = $this->db->getOne($sql);
		return str_pad($count_debt + 1, 7, '0', STR_PAD_LEFT);
	}
	
}
