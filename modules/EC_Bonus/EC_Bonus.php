<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class EC_Bonus extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Bonus';
    public $object_name = 'EC_Bonus';
    public $table_name = 'ec_bonus';
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

    public $parent_id;
    public $parent_type;
    public $parent_name;
    public $bonus_time;
    public $kpi;
    public $direct_bonus;
    public $indirect_bonus;

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }
}
