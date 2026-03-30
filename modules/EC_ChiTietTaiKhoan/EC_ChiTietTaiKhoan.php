<?php

class EC_ChiTietTaiKhoan extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_ChiTietTaiKhoan';
    public $object_name = 'EC_ChiTietTaiKhoan';
    public $table_name = 'ec_chitiettaikhoan';
    public $importable = false;

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

    public $sotaikhoan;
    public $dunodau;
    public $currency_id;
    public $ducodau;
    public $psno_01;
    public $psco_01;
    public $lkeno_01;
    public $lkeco_01;
    public $duno_01;
    public $duco_01;
    public $psno_02;
    public $psco_02;
    public $lkeno_02;
    public $lkeco_02;
    public $duno_02;
    public $duco_02;
    public $psno_03;
    public $psco_03;
    public $lkeno_03;
    public $lkeco_03;
    public $duno_03;
    public $duco_03;
    public $psno_04;
    public $psco_04;
    public $lkeno_04;
    public $lkeco_04;
    public $duno_04;
    public $duco_04;
    public $psno_05;
    public $psco_05;
    public $lkeno_05;
    public $lkeco_05;
    public $duno_05;
    public $duco_05;
    public $psno_06;
    public $psco_06;
    public $lkeno_06;
    public $lkeco_06;
    public $duno_06;
    public $duco_06;
    public $psno_07;
    public $psco_07;
    public $lkeno_07;
    public $lkeco_07;
    public $duno_07;
    public $duco_07;
    public $psno_08;
    public $psco_08;
    public $lkeno_08;
    public $lkeco_08;
    public $duno_08;
    public $duco_08;
    public $psno_09;
    public $psco_09;
    public $lkeno_09;
    public $lkeco_09;
    public $duno_09;
    public $duco_09;
    public $psno_10;
    public $psco_10;
    public $lkeno_10;
    public $lkeco_10;
    public $duno_10;
    public $duco_10;
    public $psno_11;
    public $psco_11;
    public $lkeno_11;
    public $lkeco_11;
    public $duno_11;
    public $duco_11;
    public $psno_12;
    public $psco_12;
    public $lkeno_12;
    public $lkeco_12;
    public $duno_12;
    public $duco_12;

	
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
		parent::save();

		// lưu vào bảng số dư đầu kỳ của năm đó để kết chuyển
		// dựa vào ngày tạo trong năm nào thì vào bảng năm đó
		if(!empty($this->location_id)) {
			$custom_where = ' AND location_id = "' . $this->location_id . '"';
		// tài khoản ngân hàng
		} else if(!empty($this->parent_id)) {
			$custom_where = ' AND parent_id = "' . $this->parent_id . '"';
		//công ty
		} else if(!empty($this->company_id)) {
			$custom_where = ' AND company_id = "' . $this->company_id . '"';
		}
		$this->updateSDDK($custom_where);
	}

	function updateSDDK($custom_where = '', $deleted = 0) {
		global $current_user;

		// xoá cũ tạo mới
		if(!empty($custom_where)) {
			$sql = 'UPDATE ec_chitiettaikhoan'.date('Y').' 
					SET deleted = 1
					WHERE deleted = 0 ' . $custom_where;
			$this->db->getOne($sql);
		}

		if(empty($deleted)) {
			// địa điểm
			$location_id = $parent_id = $company_id = '';
			if(!empty($this->location_id)) {
				$name = $this->location;
				$location_id = $this->location_id;
			// tài khoản ngân hàng
			} else if(!empty($this->parent_id)) {
				$name = $this->parent_name;
				$parent_id = $this->parent_id;
			//công ty
			} else if(!empty($this->company_id)) {
				$name = $this->company;
				$company_id = $this->company_id;
			}

			$dunodau = (empty($this->dunodau)?0:$this->dunodau);
			$ducodau = (empty($this->ducodau)?0:$this->ducodau);

			$sql2 = 'INSERT INTO ec_chitiettaikhoan'.date('Y').' 
					VALUES(
						uuid()
					  , "'.$name.'"
					  , "'.date('Y-m-d H:i:s').'"
					  , "'.date('Y-m-d H:i:s').'"
					  , "'.$current_user->id.'"
					  , "'.$current_user->id.'"
					  , "'.$this->description.'"
					  , 0
					  , "'.$current_user->id.'"
					  , "'.$this->sotaikhoan.'"
					  , '.$dunodau.'
					  , '.$ducodau.'
					  , "EC_Bank_Account"
					  , "'.$parent_id.'"
					  , "'.$company_id.'"
					  , "'.$location_id.'"
					)';
			$this->db->query($sql2);
		}
	}
	
}
