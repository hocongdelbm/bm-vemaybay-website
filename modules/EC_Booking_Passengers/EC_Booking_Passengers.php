<?php
class EC_Booking_Passengers extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Booking_Passengers';
    public $object_name = 'EC_Booking_Passengers';
    public $table_name = 'ec_booking_passengers';
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

    public $salutation;
    public $birthday;
    public $booking_id;
    public $booking;
    public $type;
    public $eticket_outbound;
    public $eticket_inbound;
    public $pnr_outbound;
    public $pnr_inbound;
    public $is_active;

	
    public function bean_implements($interface)
    {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }
}
