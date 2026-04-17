<?php
class EC_Zalo_Contacts extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Zalo_Contacts';
    public $object_name = 'EC_Zalo_Contacts';
    public $table_name = 'ec_zalo_contacts';
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

    public $zalo_id;
    public $oa_id;
    public $contact_id;
    public $contact_name;
    public $alias;
    public $avatar;
    public $birth_date;
    public $last_interaction;
    public $is_follower;
    public $tags;
    public $province_city;
    public $ward_commune;
    public $address;
    public $quota_info;
    public $status;
	
    public function bean_implements($interface) {
        switch($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }
}
