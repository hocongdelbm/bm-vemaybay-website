<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class EC_Payment_VoucherViewEdit extends ViewEdit {
	function __construct() {
		parent::__construct();
	}
	
	function display(){
		
		if(empty($this->bean->id) || $this->bean->pv_status == '0' || (isset($_POST['isDuplicate']) && $_POST['isDuplicate'] == 'true')){
			$this->customFields();
			parent::display();
		} else {
			echo '<p class="error">Chứng từ đã khóa</p>';
		}
		
	}
	
	function customFields() {	
		global $app_list_strings, $timedate, $current_user, $locale;
		$date_format = $timedate->get_date_format();
		
		// NGAY HACH TOAN (Giờ lưu dưới DB là giờ VietNam)
		$this->bean->ngayhachtoan = isset($this->bean->ngayhachtoan) && !empty($this->bean->ngayhachtoan)
			? date("$date_format H:i", strtotime($this->bean->ngayhachtoan) - 7*3600)
			: date("$date_format H:i");
		
		// TAI KHOAN NGAN HANG
		$display 		= (isset($_POST['hinhthucchi']) && $_POST['hinhthucchi'] == 'credit_transfer') || $this->bean->hinhthucchi ==  'credit_transfer' ? '' : 'display:none';
		$display2 	= (isset($_POST['hinhthucchi']) && $_POST['hinhthucchi'] == 'cash') || $this->bean->hinhthucchi ==  'cash' ? '' : 'display:none';
		$tknganhang_id = isset($this->bean->tknganhang_id) ? $this->bean->tknganhang_id : '';
		
		// PHAN QUYEN
		/*if(is_admin($current_user))
			$tknganhang_group = "";
		else
			$tknganhang_group = " AND ".SecurityGroup::getGroupWhere("t","EC_TaiKhoanNganHang",$current_user->id);*/
		$tknganhang_group = "";
		
		// DINH DANG SO
		// $sep = get_number_seperators();
		$sep = my_get_number_separators();
		$group_decimal = '<input type="hidden" id="grp_seperator" name="grp_seperator" value="'.$sep[0].'" />
		<input type="hidden" id="dec_seperator" name="dec_seperator" value="'.$sep[1].'" />
		<input type="hidden" id="sig_digits" name="sig_digits" value="'.$locale->getPrecision().'" />';
		
		$hinhthucchi = '<div class="d-flex align-items-center gap-1"><select class="box-select" name="hinhthucchi" id="hinhthucchi" title="" tabindex="103">'.get_select_options_with_id($app_list_strings['receipt_type_list'], isset($_POST['hinhthucchi']) ? $_POST['hinhthucchi'] : $this->bean->hinhthucchi).'</select>';
		$hinhthucchi .= '<select class="box-select" style="'.$display.'" id="tknganhang_id" name="tknganhang_id" tabindex="103"><option value=""></option>'.myGetBankAccountList($tknganhang_id,$tknganhang_group).'</select>';
		$hinhthucchi .= '<select class="box-select" style="'.$display2.'" id="com_location_id" name="com_location_id" tabindex="103">'.myGetLocationListByDepID($this->bean->com_location_id).'</select></div>';
		
		$this->ss->assign('HINHTHUCCHI', $hinhthucchi . $group_decimal);

		// nhân viên
		$employee_list = '<link type="text/css" rel="stylesheet" href="./themes/SuiteP/libs/css/select2.min.css">';
		$employee_arr = $this->getEmployeeList();
		$employee_list .= '<select id="employee-select" name="employee_id"><option value="">-- Trống --</option>'.get_select_options_with_id($employee_arr, $this->bean->employee_id).'</select>';
		$this->ss->assign('EMPLOYEE_NAME', $employee_list);

		// nhà cung cấp
		$supplier_list = '
			<select id="supplier_id" name="supplier_id">
				<option value="">--Trống--</option>
				' . myGetSelectOptionsWithDb('Accounts', (isset($this->bean->supplier_id) ? $this->bean->supplier_id : ''), 'id', " AND account_type='Supplier' AND is_stop_tracking = 0 ") . '
			</select>';
		$this->ss->assign('CUS_SUPPLIER', $supplier_list);

		// khách hàng
		$account_list = '
			<select id="account_list" name="account_id">
				<option value="">--Trống--</option>
				' . myGetSelectOptionsWithDb('Accounts', (isset($this->bean->account_id) ? $this->bean->account_id : ''), 'id', " AND account_type='Customer' AND is_stop_tracking = 0 ") . '
			</select>';
		$this->ss->assign('CUS_ACCOUNT', $account_list);
	}

	function getEmployeeList() {
		// Lấy danh sách nhân viên
		// $sql = '
		// 	SELECT id, CONCAT(last_name, " ", IFNULL(first_name, "")) AS full_name 
		// 	FROM users 
		// 	WHERE deleted = 0 AND status = "Active" 
		// 	AND title NOT IN ("Admin", "Bot")
		// 	AND is_admin = 0
		// 	AND first_name IS NOT NULL';
		$sql = '
			SELECT id, CONCAT(last_name, " ", IFNULL(first_name, "")) AS full_name 
			FROM users 
			WHERE deleted = 0 AND status = "Active" 
			AND title NOT IN ("Administrator", "Bot")
			AND first_name IS NOT NULL';
		$res = $this->bean->db->query($sql);
		while($row = $this->bean->db->fetchByAssoc($res)) {
			$employee_list[$row['id']] = $row['full_name'];
		}

		return $employee_list;
	}
}
?>