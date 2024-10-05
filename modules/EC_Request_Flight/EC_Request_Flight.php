<?php

class EC_Request_Flight extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Request_Flight';
    public $object_name = 'EC_Request_Flight';
    public $table_name = 'ec_request_flight';
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

    public $request_type;
    public $booking_id;
    public $booking;
    public $contact_name;
    public $email;
    public $phone;
    public $request_detail;
    public $request_status;
    public $city;
    public $country;

	
    public function bean_implements($interface)
    {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

    // save request 
	function save($check_notify = FALSE){
		if(empty($this->name)){
			$this->name = 'YC'.date('ymd').$this->getNumberOfRequests();
		}
		parent::save($check_notify);
	}
	
	// get number of request
	function getNumberOfRequests(){
		$count_req = 0;
		$sql = "SELECT COUNT(id) AS count_req
				FROM ec_request_flight
				WHERE deleted = 0 
				AND DATE(date_entered) = '".date('Y-m-d')."' " ;
		$res = $this->db->query($sql);
		$row = $this->db->fetchByAssoc($res);
		if(!empty($row)){
			$count_req = $row['count_req'];
		}
		return str_pad($count_req+1, 4, '0', STR_PAD_LEFT);
	}
	
}
