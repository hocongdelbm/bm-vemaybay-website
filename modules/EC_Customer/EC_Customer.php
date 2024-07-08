<?php

class EC_Customer extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Customer';
    public $object_name = 'EC_Customer';
    public $table_name = 'ec_customer';
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

    public $email;
	public $phone;
	public $gender;
	public $birthday;
	public $type;
	public $source;

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
