<?php

class EC_WorkingOverTimeDetails extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_WorkingOverTimeDetails';
    public $object_name = 'EC_WorkingOverTimeDetails';
    public $table_name = 'ec_workingovertimedetails';
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

    public $register_date;
    public $type;
    public $ec_workingovertimes_id_c;
    public $overtime_name;
	
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
