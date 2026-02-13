<?php
class EC_Zalo_Apps extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Zalo_Apps';
    public $object_name = 'EC_Zalo_Apps';
    public $table_name = 'ec_zalo_apps';
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

    public $access_token;
    public $refresh_token;
    public $expires_at;
    public $oa_id;
    public $oa_name;
    public $secret_key;
    public $code_verifier;
    public $code_challenge;
	
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
