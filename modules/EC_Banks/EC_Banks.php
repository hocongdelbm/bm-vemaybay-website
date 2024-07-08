<?php
class EC_Banks extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Banks';
    public $object_name = 'EC_Banks';
    public $table_name = 'ec_banks';
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

    public $short_name;
    public $english_name;
    public $headquarters;
    public $unfollow;
    public $image;
	
    public function bean_implements($interface) {
        switch($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }
	
    function save($check_notify = FALSE){
		if(myCheckValueExist('EC_Banks', array('short_name'), array($_POST['short_name']), $this->id)){
			header('Location: index.php?module=EC_Banks&action=Error&error_string='.urlencode('Mã <'.$_POST['short_name'].'> đã bị trùng trong danh sách nhập. Vui lòng kiểm tra lại.'));
			exit();
		}
		parent::save($check_notify);
	}
}