<?php
class EC_TaiKhoan extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_TaiKhoan';
    public $object_name = 'EC_TaiKhoan';
    public $table_name = 'ec_taikhoan';
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

    public $sotaikhoan;
    public $tentienganh;
    public $taikhoantonghop;
    public $nhomtaikhoan;
    public $tinhchat;
    public $loaidoituong;
    public $taphopchiphi;
    public $doituong;
    public $chitiettheo;
    public $hopdong;
    public $vthh_ccdc;
    public $taikhoannganhang;
    public $khoanmucchiphi;
    public $theongoaite;
    public $theophongban;
    public $theothuchi;
    public $cap;

    public function bean_implements($interface) {
        switch($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    function save($check_notify = FALSE) {
		if($_POST['doituong'] == 0 || $_POST['chitiettheo'] == 0){
			$this->loaidoituong = '';
			$this->doituong = 0;
		}
		
		parent::save($check_notify);	
	}
	
	// Lấy danh sách tài khoản
	function listOfTaiKhoan($where = '', $val = '') {
		$sql = "SELECT name, sotaikhoan, cap
				FROM ec_taikhoan
				WHERE deleted = 0 ";
		
		if(!empty($where))
			$sql .= $where;
				
		$sql .= " ORDER BY sotaikhoan ";
		
		$res = $this->db->query($sql);
		$html = '';
		while($row = $this->db->fetchByAssoc($res)){
			if($row['sotaikhoan'] == $val) $selected = 'selected="selected"'; else $selected = '';
			$html .= '<option cap="'.$row['cap'].'" '.$selected.' value="'.$row['sotaikhoan'].'">'.$row['sotaikhoan'].' - '.$row['name'].'</option>';
		}
		return $html;
	}
}
