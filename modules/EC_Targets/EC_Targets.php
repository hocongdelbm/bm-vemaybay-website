<?php

class EC_Targets extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Targets';
    public $object_name = 'EC_Targets';
    public $table_name = 'ec_targets';
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

    public $year;
    public $target_year;
    public $currency_id;
    public $target_quarter1;
    public $target_quarter2;
    public $target_quarter3;
    public $target_quarter4;
    public $target_month1;
    public $target_month2;
    public $target_month3;
    public $target_month4;
    public $target_month5;
    public $target_month6;
    public $target_month7;
    public $target_month8;
    public $target_month9;
    public $target_month10;
    public $target_month11;
    public $target_month12;
    public $target_type;

	
    public function bean_implements($interface)
    {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

    public function save($check_notify = FALSE) {
		global $current_user; 

		// nếu người dùng là QuanLy thì có thể chọn lại loại target, không thì mặc định là target cá nhân
		if($current_user->title != 'QuanLy' && !is_admin($current_user)) {
			$this->target_type = 1;
		}

		if(empty($this->name)) {
			$this->name = 'TG-'.date('Y').'-'.str_pad($this->countVoucher(), 2, '0', STR_PAD_LEFT);
		}

		$this->target_quarter1 = (int)$this->target_month1 + (int)$this->target_month2 + (int)$this->target_month3;
		$this->target_quarter2 = (int)$this->target_month4 + (int)$this->target_month5 + (int)$this->target_month6;
		$this->target_quarter3 = (int)$this->target_month7 + (int)$this->target_month8 + (int)$this->target_month9;
		$this->target_quarter4 = (int)$this->target_month10 + (int)$this->target_month11 + (int)$this->target_month12;
		$this->target_year = $this->target_quarter1 + $this->target_quarter2 + $this->target_quarter3 + $this->target_quarter4;
		parent::save();
	}

	public function countVoucher() {
		$sql = 'SELECT COUNT(*) FROM ec_targets WHERE year = "' . date('Y') . '"';
		return ($this->db->query($sql) + 1);
	}
	
	
}
