<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
		
class Viewprintrv extends SugarView {
	function display() {
		$smartyCont= new Sugar_Smarty();
		$this->populateContent($smartyCont);
		$smartyCont->display('modules/EC_Receipt_Voucher/tpls/printrv.tpl');
	}
	
	function populateContent($smartyobj){
		global $current_user;
		require_once("ReadNumberInWords.php");
		$readnum = new ReadNumberInWords();
		
		if(isset($_POST['dep_id']) && !empty($_POST['dep_id'])){
			$dep_id = $_POST['dep_id'];
		} 
		else {
			$dep_id = $current_user->department_id;
		}
		
		$department_info = myGetDepartmentInfo($dep_id);

		$smartyobj->assign('COM_NAME', $department_info['com_name']);
		$smartyobj->assign('COM_ADDRESS', $department_info['com_address']);
		$smartyobj->assign('COM_TEL', $department_info['com_phone']);
		$smartyobj->assign('COM_TAXCODE', $department_info['com_taxcode']);
		
		$ngaychungtu = 'Ngày '.date('d', strtotime($this->bean->ngaychungtu)).' tháng '.date('m', strtotime($this->bean->ngaychungtu)).' năm '.date('Y', strtotime($this->bean->ngaychungtu));
		$smartyobj->assign('NGAYCHUNGTU', $ngaychungtu);
		$smartyobj->assign('SOCHUNGTU', $this->bean->name);
		$smartyobj->assign('DIADIEM', $this->bean->com_location);
		$smartyobj->assign('NGUOINOP', $this->bean->guest_name);
		$smartyobj->assign('DIACHI', $this->bean->guest_address);
		$smartyobj->assign('LYDONOP', str_replace("\n","<br />",$this->bean->description));
		$smartyobj->assign('SOTIEN', number_format($this->bean->amount_converted,0,'.',','));
		$smartyobj->assign('SOTIENBANGCHU', $readnum->docso($this->bean->amount_converted).' đồng');
		$smartyobj->assign('KEMTHEO', str_replace("\n","<br />",$this->bean->rv_notes));
		$smartyobj->assign('DIENTHOAI', $this->bean->guest_phone);
	}
}
