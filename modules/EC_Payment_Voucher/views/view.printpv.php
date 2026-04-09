<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
		
class Viewprintpv extends SugarView {

	function display() {
		$smartyCont= new Sugar_Smarty();
		$this->populateContent($smartyCont);
		$smartyCont->display('modules/EC_Payment_Voucher/tpls/printpv.tpl');
	}
	
	function populateContent($smartyobj){
		global $app_list_strings, $current_user;
		
		$department_info = myGetDepartmentInfo($current_user->department_id);
		$smartyobj->assign('COM_NAME', $department_info['com_name']);
		$smartyobj->assign('COM_ADDRESS', $department_info['com_address']);
		$smartyobj->assign('COM_TEL', $department_info['com_phone']);
		$smartyobj->assign('COM_TAXCODE', $department_info['com_taxcode']);
		
		$ngaychungtu = 'Ngày '.date('d', strtotime($this->bean->ngaychungtu)).' tháng '.date('m', strtotime($this->bean->ngaychungtu)).' năm '.date('Y', strtotime($this->bean->ngaychungtu));
		$smartyobj->assign('NGAYCHUNGTU', $ngaychungtu);
		$smartyobj->assign('SOCHUNGTU', $this->bean->name);
		$smartyobj->assign('DIADIEM', $this->bean->com_location);
		$smartyobj->assign('NGUOINHAN', $this->bean->receipent_name);
		$smartyobj->assign('DIACHI', $this->bean->receipent_address);
		$smartyobj->assign('LYDOCHI', str_replace("\n","<br />",$this->bean->description));
		$smartyobj->assign('SOTIEN', number_format($this->bean->amount,0,'.',','));
		$smartyobj->assign('SOTIENBANGCHU', ReadNumberInWords::readNumber($this->bean->amount).' đồng');
		$smartyobj->assign('KEMTHEO', str_replace("\n","<br />",$this->bean->pv_notes));
	}

}
	
?>