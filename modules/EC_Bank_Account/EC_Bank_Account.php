<?php
class EC_Bank_Account extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_Bank_Account';
    public $object_name = 'EC_Bank_Account';
    public $table_name = 'ec_bank_account';
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

    public $account_number;
    public $account_holder;
    public $bank_id;
    public $bank;
    public $branch;
    public $unfollow;
    public $is_display;
    public $is_sms;
    public $account;

    public function bean_implements($interface) {
        switch($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }
	
    function save($check_notify = FALSE) {
		if(empty($this->assigned_user_id)) {
			$this->assigned_user_id = $GLOBALS['current_user']->id;
		}
		if(myCheckValueExist('EC_Bank_Account', array('account_number', 'bank_id'), array($_POST['account_number'], $_POST['bank_id']), $this->id)) {
			header('Location: index.php?module=EC_Bank_Account&action=Error&error_string='.urlencode('Số tài khoản <'.$_POST['account_number'].'> đã bị trùng trong danh sách nhập. Vui lòng kiểm tra lại.'));
			exit();
		}
		parent::save($check_notify);
	}
}
