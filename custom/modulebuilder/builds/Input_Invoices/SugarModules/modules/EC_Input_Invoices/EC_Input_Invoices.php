<?php



class EC_Input_Invoices extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Input_Invoices';
    public $object_name = 'EC_Input_Invoices';
    public $table_name = 'ec_input_invoices';
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
