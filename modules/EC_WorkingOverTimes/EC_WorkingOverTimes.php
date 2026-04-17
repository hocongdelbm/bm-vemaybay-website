<?php

class EC_WorkingOverTimes extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_WorkingOverTimes';
    public $object_name = 'EC_WorkingOverTimes';
    public $table_name = 'ec_workingovertimes';
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

    public $status;
    public $stage;
    public $approved_date;

	
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
		if (empty($this->name)) {
			$this->name = 'OT-'.preg_replace('/0/', '', date('Y'), 1).date('m').str_pad($this->countVoucher() + 1, 2, '0', STR_PAD_LEFT);
		}

		parent::save();

		if(!empty($_POST['employee_id'])) {
			$this->saveGroupLine();
		}

		// hệ số
		if(!empty($_POST['multiplier'])) {
			$this->updateMultiplier();
		}
	}
	
	function countVoucher() {
		$sql = 'SELECT COUNT(*) FROM ec_workingovertimes 
				WHERE date_entered >= "' . date('Y-m-d', strtotime('first day of this month')) . '"
				AND date_entered <= "' . date('Y-m-d', strtotime('last day of this month')) . '"';
		return $this->db->getOne($sql);
	}

	function saveGroupLine() {
		for($i = 0; $i < count($_POST['employee_id']); $i++) {
			for($j = 0; $j < count($_POST['date_chosen'.($i+1)]); $j++) {
				if($_POST['employee_deleted'][$i] == 1 || $_POST['overtime_deleted'.($i+1)][$j] == 1)
					$deleted = 1;
				else 
					$deleted = 0;

				$detail  					= new EC_WorkingOverTimeDetails;
				$detail->id 				= $_POST['detail_id'.($i+1)][$j];
				$detail->name 				= date('l', strtotime($_POST['date_chosen'.($i+1)][$j]));
				$detail->assigned_user_id 	= $_POST['employee_id'][$i];
				$detail->description 		= $_POST['description'.($i+1)][$j];
				$detail->register_date 		= date('d-m-Y', strtotime($_POST['date_chosen'.($i+1)][$j]));

				$detail->from_time 			= date('d-m-Y H:i', strtotime($_POST['date_chosen'.($i+1)][$j]." ".$_POST['from_hour'.($i+1)][$j].":".$_POST['from_minute'.($i+1)][$j]));
				$detail->to_time 			= date('d-m-Y H:i', strtotime($_POST['date_chosen'.($i+1)][$j]." ".$_POST['to_hour'.($i+1)][$j].":".$_POST['to_minute'.($i+1)][$j]));
				
				$detail->ec_workingovertimes_id_c = $this->id;
				$detail->deleted = $deleted;
				$detail->save();
			}
		}
	}

	function updateMultiplier() {
		for($i = 0; $i < count($_POST['multiplier']); $i++) {
			$hours = myGetHourBetween($_POST['from_time'][$i], $_POST['to_time'][$i]);

			if( strtotime($_POST['from_time'][$i]) < strtotime(date('d-m-Y', strtotime($_POST['from_time'][$i])).' 12:00:00') 
				&& strtotime($_POST['to_time'][$i]) > strtotime(date('d-m-Y', strtotime($_POST['to_time'][$i])).' 13:00:00')
			) {
				$hours--;
			}
			$sql = '
				UPDATE ec_workingovertimedetails 
				SET multiplier = ' . $_POST['multiplier'][$i] . '
					, working_hour = ' . $hours * $_POST['multiplier'][$i] . '
				WHERE id = "' . $_POST['detail'][$i] . '"
			';
			$this->db->query($sql);
		}
		
	}
	
}
