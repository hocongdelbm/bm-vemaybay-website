<?php
class EC_ChuyenTienNoiBo extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_ChuyenTienNoiBo';
    public $object_name = 'EC_ChuyenTienNoiBo';
    public $table_name = 'ec_chuyentiennoibo';
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

    public $ngaychungtu;
    public $ngayhachtoan;
    public $tutknganhang_id;
    public $tutknganhang;
    public $dentknganhang_id;
    public $dentknganhang;
    public $tk_no;
    public $tk_co;
    public $loaitien;
    public $sotien;
    public $currency_id;
    public $mucthuchi_id;
    public $mucthuchi;
	public $ghiso;
    public $tutienmat;
    public $dentienmat;
    public $dendiadiem_id;
    public $tudiadiem_id;
	
    public function bean_implements($interface) {
        switch($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }
	
    public function save($check_notify = FALSE){
		global $current_user;
		
		if(empty($this->name)){
            // CTNB-230916-0001
            $where = ' ngaychungtu >= "' . date('Y-m-d') . '" ';
			$this->name = 'CTNB-' . date('ymd') . '-'.myAutoGenerateName('EC_ChuyenTienNoiBo', $where, 4);
		}
	
		// Kiem tra tinh trang ghi so
		if(empty($this->id))
			$ghiso = 1;
		else
			$ghiso = $_POST['ghiso'];
		$this->ghiso = $ghiso;
		
		if(isset($_POST['ngayhachtoan']) && !empty($_POST['ngayhachtoan'])){
			// $this->ngayhachtoan = date('Y-m-d H:i', strtotime($_POST['ngayhachtoan'])-7*3600);
			$this->ngayhachtoan = date('Y-m-d H:i:s', strtotime($_POST['ngayhachtoan']));
		}
		parent::save($check_notify);
		
		if($this->ghiso == 1){
			// Create working process
			myCreateWorkingProcess($this->module_dir, $this->id, $this->name, $this->description, $current_user->id, 'create_transfer');
		} 
        else {
			// Remove working process
			myRemoveWorkingProcess($this->module_dir, $this->id);
		}
	}
}