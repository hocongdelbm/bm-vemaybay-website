<?php
class EC_NhomTaiKhoan extends Basic {
    public $new_schema = true;
    public $module_dir = 'EC_NhomTaiKhoan';
    public $object_name = 'EC_NhomTaiKhoan';
    public $table_name = 'ec_nhomtaikhoan';
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

    public $tinhchat;
    public $manhom;
    public $loaidoituong;
    public $chitiettheo;
    public $ds_chitiettheo;
	
    public function bean_implements($interface) {
        switch($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    function save($check_notify = FALSE){
		if($this->isExist($_POST['manhom'], $this->id) && empty($this->id)){
			header("Location: index.php?module=EC_NhomTaiKhoan&action=Error&error_string=".urlencode("Mã nhóm này đã tồn tại."));
			exit();
		}
		if($_POST['chitiettheo'] == 0){
			$this->ds_chitiettheo = '';
			$this->loaidoituong = '';
			$this->chitiettheo = 0;
		}
		if($_POST['ds_chitiettheo'] != 0){
			$this->loaidoituong = '';
		}
		parent::save($check_notify);
	}
	
	// Kiểm tra sự tồn tại của mã nhóm
	function isExist($manhom, $nhomtaikhoan_id){
		$sql = "SELECT COUNT(id) FROM ec_nhomtaikhoan WHERE deleted = 0 AND manhom = '".trim($manhom)."' AND id <> '".$nhomtaikhoan_id."' ";
		$countrow = $this->db->getOne($sql);
		if($countrow > 0)
			return true;
		return false;
	}
	
	// Lấy danh sách nhóm tài khoản
	function listOfNhomTaiKhoan($val = ''){
		$sql = "SELECT name, manhom
				FROM ec_nhomtaikhoan
				WHERE deleted = 0 
				ORDER BY manhom ";
		$res = $this->db->query($sql);
		$html = '';
		while($row = $this->db->fetchByAssoc($res)){
			if($row['manhom'] == $val) $selected = 'selected="selected"'; else $selected = '';
			$html .= '<option '.$selected.' value="'.$row['manhom'].'">'.$row['manhom'].' - '.$row['name'].'</option>';
		}
		return $html;
	}
}
