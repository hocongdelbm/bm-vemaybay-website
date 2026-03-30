<?php
class EC_Booking_Itineraries extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Booking_Itineraries';
    public $object_name = 'EC_Booking_Itineraries';
    public $table_name = 'ec_booking_itineraries';
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

    public $airline_code;
    public $flight_number;
    public $ticket_class;
    public $departure;
    public $arrival;
    public $departure_date;
    public $arrival_date;
    public $base_price;
    public $currency_id;
    public $total_price;
    public $booking_id;
    public $booking;
    public $add_type;
    public $direction;
    public $sabre_logs;
    public $time_limit;
    public $is_layover;
    public $parent_detail_id;
	
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
        /*
         Hành trình lưu từ website xuống bị thiếu 7 hours
         */
        $this->departure_date   = date('Y-m-d H:i:s', strtotime($this->departure_date));
        $this->arrival_date     = date('Y-m-d H:i:s', strtotime($this->arrival_date));

		parent::save($check_notify);

    }
}
