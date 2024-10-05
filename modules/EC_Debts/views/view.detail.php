<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_DebtsViewDetail extends ViewDetail {

	function display(){
		$this->populateCustomButtons();
		$this->populateCustomFields();
		parent::display();
	}
	
	function populateCustomFields(){
		global $timedate;
		$date_format = $timedate->get_date_format();
		// ngày hạch toán
		$this->bean->ngayhachtoan = date($date_format.' H:i', strtotime($this->bean->ngayhachtoan));
	}
	
	function populateCustomButtons(){
		global $mod_strings, $app_strings, $app_list_strings;
		$loaichi_id = '3361ac47-2254-701a-55d1-508abcb90f50'; // công nợ phải trả
		
		// nút tạo phiếu thu / chi
		if($this->bean->debt_type == 'Buy'){
			$create_voucher = '</form>
			<form name="frmCreateVoucher" id="frmCreateVoucher" action="index.php" method="post">
				<input type="hidden" name="module" value="EC_Payment_Voucher" />
				<input type="hidden" name="action" value="EditView" />
				<input type="hidden" name="debt_id" value="'.$this->bean->id.'" />
				<input type="hidden" name="debt_name" value="'.$this->bean->name.'" />
				<input type="hidden" name="amount" value="'.format_number($this->bean->debt_amount).'" />
				<input type="hidden" name="receipent_name" value="'.$this->bean->contact_name.'" />
				<input type="hidden" name="receipent_phone" value="'.$this->bean->phone_office.'" />
				<input type="hidden" name="receipent_phone_fax" value="'.$this->bean->phone_fax.'" />
				<input type="hidden" name="receipent_phone_mobile" value="'.$this->bean->phone_mobile.'" />
				<input type="hidden" name="receipent_email" value="'.$this->bean->email.'" />
				<input type="hidden" name="receipent_address" value="'.$this->bean->address.'" />
				<input type="hidden" name="ec_payment_types_id_c" value="'.$loaichi_id.'" />
				<input type="hidden" name="payment_type" value="Công nợ phải trả" />
				<input type="hidden" name="supplier_id" value="'.$this->bean->supplier_id.'" />
				<input type="hidden" name="supplier" value="'.$this->bean->supplier.'" />
				<input type="hidden" name="description" value="Chi tiền công nợ phải trả cho nhà cung cấp" />
				<input type="submit" name="btnCreateVoucher" id="btnCreateVoucher" value="Tạo phiếu chi" title="Tạo phiếu chi" style="font-weight:bold" />
			</form>';
		} else {
			$create_voucher = '</form>
			<form name="frmCreateVoucher" id="frmCreateVoucher" action="index.php" method="post">
				<input type="hidden" name="module" value="EC_Receipt_Voucher" />
				<input type="hidden" name="action" value="EditView" />
				<input type="hidden" name="debt_id" value="'.$this->bean->id.'" />
				<input type="hidden" name="debt_name" value="'.$this->bean->name.'" />
				<input type="hidden" name="amount" value="'.format_number($this->bean->debt_amount).'" />
				<input type="submit" name="btnCreateVoucher" id="btnCreateVoucher" value="Tạo phiếu thu" title="Tạo phiếu thu" style="font-weight:bold" />
			</form>';
		}
		$this->ss->assign('CREATE_VOUCHER', $create_voucher);
	}


}
?>