<?php

class EC_HoaDonMua extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_HoaDonMua';
    public $object_name = 'EC_HoaDonMua';
    public $table_name = 'ec_hoadonmua';
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

    public $booking_id;
    public $booking;
    public $lienhe;
    public $dienthoai;
    public $email;
    public $diachi;
    public $nhacungcap_id;
    public $nhacungcap;
	
    public function bean_implements($interface)
    {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }

    function save($check_notify = FALSE){
		
		if($this->isNameExist($_POST['name'], $this->id) && empty($this->id)){
			header("Location: index.php?module=EC_HoaDonMua&action=Error&error_string=".urlencode("Mã <".$_POST['name']."> đã bị trùng trong danh sách nhập. Xin vui lòng kiểm tra lại."));
			exit();
		}
		parent::save($check_notify);
		if(isset($_POST['ct_sove']) && !empty($_POST['ct_sove'])){
			$arr_cthd = $this->saveListItems();
		}
	}
	
	// lưu chi tiết hóa đơn
	function saveListItems(){
		
		require_once('modules/EC_ChiTietHoaDon/EC_ChiTietHoaDon.php');
		$cthd = new EC_ChiTietHoaDon();
		
		$countcthd = count($_POST['ct_sove']);
		
		$tongdongia = 0;
		$tongthuevat = 0;
		$tongphidv_sb = 0;
		$tongthanhtien = 0;
		
		for($i = 0; $i < $countcthd; $i++){
			
			$cthd->id = $_POST['ct_id'][$i];
			$cthd->name = $_POST['ct_sove'][$i];
			$cthd->hanhtrinh = $_POST['ct_hanhtrinh'][$i];
			$cthd->soluong = unformat_number($_POST['ct_soluong'][$i]);
			$cthd->dongia = unformat_number($_POST['ct_dongia'][$i]);
			$cthd->thuesuat = $_POST['ct_thuesuat'][$i];
			$cthd->thuevat = unformat_number($_POST['ct_thuevat'][$i]);
			$cthd->phisanbay = unformat_number($_POST['ct_phisanbay'][$i]);
			$cthd->thanhtien = unformat_number($_POST['ct_thanhtien'][$i]);
			$cthd->deleted = $_POST['ct_deleted'][$i];
			$cthd->parent_id = $this->id;
			$cthd->parent_type = 'EC_HoaDonMua';
			
			if ($cthd->deleted == 1) {
				$cthd->mark_deleted($cthd->id);
			} else {
				if(trim($cthd->name) != ''){
					$cthd->save();
					
					$tongdongia += $cthd->soluong * $cthd->dongia;
					$tongthuevat += $cthd->soluong * $cthd->thuevat;
					$tongphidv_sb += $cthd->soluong * $cthd->phidv_phisb;
					$tongthanhtien += $cthd->thanhtien;
				}
			}
		}
		$arr['tongdongia'] = $tongdongia;
		$arr['tongthuevat'] = $tongthuevat;
		$arr['tongphidv_sb'] = $tongphidv_sb;
		$arr['tongthanhtien'] = $tongthanhtien;
		return $arr;	
		
	}
	
	// kiểm tra số hóa đơn có tồn tại
	function isNameExist($sohoadon, $id){
		$sql = "SELECT COUNT(id) FROM ec_hoadonmua WHERE name = '".$sohoadon."' AND deleted = 0 AND id <> '".$id."' ";
		$rowcount = $this->db->getOne($sql);
		if($rowcount > 0)
			return true;
		return false;
	}
	
	
}
