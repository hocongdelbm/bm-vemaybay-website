<?php



class EC_Flight_Bookings extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Flight_Bookings';
    public $object_name = 'EC_Flight_Bookings';
    public $table_name = 'ec_flight_bookings';
    public $importable = true;

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

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }
}
