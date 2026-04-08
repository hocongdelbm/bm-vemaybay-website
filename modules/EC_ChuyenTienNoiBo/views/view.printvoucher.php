<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
		
class Viewprintvoucher extends SugarView {
	function display() {
		$smartyCont= new Sugar_Smarty();
		$this->populateContent($smartyCont);
		$smartyCont->display('modules/EC_ChuyenTienNoiBo/tpls/view_printvoucher.tpl');
	}
	
	function populateContent($smartyobj){
		global $app_list_strings;
		$smartyobj->assign('VOUCHER_NUMBER', $this->bean->name);
		$smartyobj->assign('DATE_ENTERED', $this->bean->ngaychungtu);
		
		if($this->bean->tutienmat){
			$from_owner = 'Tiền mặt';
			$from_account = '';
			$from_bank = '';
			$smartyobj->assign('FROM_LOCATION', 'Từ: ' . $this->bean->tudiadiem);
		} 
		else {
			$bank = new EC_Bank_Account();
			$bank->retrieve($this->bean->tutknganhang_id);
			$from_owner 	= $bank->account_holder;
			$from_account 	= $bank->account_number;
			$from_bank 		= $bank->bank;
		}
		
		$smartyobj->assign('FROM_OWNER', $from_owner);
		$smartyobj->assign('FROM_ACCOUNT', $from_account);
		$smartyobj->assign('FROM_BANK', $from_bank);
		
		if($this->bean->dentienmat){
			$to_owner = 'Tiền mặt';
			$to_account = '';
			$to_bank = '';
			$smartyobj->assign('TO_LOCATION', 'Đến: ' . $this->bean->dendiadiem);
		} 
		else {
			$bank = new EC_Bank_Account();
			$bank->retrieve($this->bean->dentknganhang_id);
			$to_owner 	= $bank->account_holder;
			$to_account = $bank->account_number;
			$to_bank 	= $bank->bank;
		}
		
		$smartyobj->assign('TO_OWNER', $to_owner);
		$smartyobj->assign('TO_ACCOUNT', $to_account);
		$smartyobj->assign('TO_BANK', $to_bank);
		$smartyobj->assign('AMOUNT_IN_WORD', ReadNumberInWords::readNumber($this->bean->sotien).' đồng');
		$smartyobj->assign('AMOUNT', format_number($this->bean->sotien));
		$smartyobj->assign('CURRENCY', $app_list_strings['loaitien_list'][$this->bean->loaitien]);
		$smartyobj->assign('DESCRIPTION', str_replace("\n", "<br />", $this->bean->description));
	}

}
