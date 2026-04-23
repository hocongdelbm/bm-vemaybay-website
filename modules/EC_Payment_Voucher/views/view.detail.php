<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_Payment_VoucherViewDetail extends ViewDetail {
	function display(){
		$this->displayJS();
		$this->populateCustomButtons();
		$this->populateCustomFields();
		parent::display();
	}
	
	function displayJS(){
		$js = '<script>
			var pv_status = "'.$this->bean->pv_status.'";
		</script>';
		echo $js;
	}
	
	function populateCustomButtons(){
		global $app_list_strings, $current_user, $timedate;
		$date_format = $timedate->get_date_format();

		// ngày hạch toán
		$this->bean->ngayhachtoan = date("$date_format H:i", strtotime($this->bean->ngayhachtoan) - 7*3600);
		
		// in phiếu
		if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true)){
			$print_pv = '</form>
			<form action="index.php?print=true" name="frmPrintPV" method="post" target="_blank">
				<input type="hidden" name="module" value="EC_Payment_Voucher" />
				<input type="hidden" name="action" value="printpv" />
				<input type="hidden" name="record" value="'.$this->bean->id.'" />
				<input type="submit" class="btn btn-primary" name="btnPrintPV" onclick="Set_Cookie(\'showLeftCol\',\'false\',30,\'/\',\'\',\'\');" value="In phiếu" value="In phiếu" style="font-weight:bold;" />
			</form>';
			$this->ss->assign('PRINT_PV', $print_pv);
		}
		
		// thay đổi tình trạng phiếu chi
		if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true) && is_admin($current_user)){
			$change_status = '</form>
			<form action="index.php" method="post" name="frmChangeStatus" id="frmChangeStatus">
				<input type="hidden" name="module" value="EC_Payment_Voucher" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="'.$this->bean->id.'" />
				<input type="hidden" name="return_module" value="EC_Payment_Voucher" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="'.$this->bean->id.'" />
				<select class="box-select" id="pv_status" name="pv_status">'.get_select_options_with_id($app_list_strings['payment_voucher_status_list'], (int)$this->bean->pv_status).'</select>
				<input type="submit" class="btn btn-primary" name="btnChangeStatus" id="btnChangeStatus" value="Đổi tình trạng" title="Đổi tình trạng" style="font-weight:bold;" />
			</form>';
			$this->ss->assign('CHANGE_STATUS', $change_status);
		}
		
		// tình trạng phiếu chi
		$pv_status = '';
		if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true) && $this->bean->pv_status == '0'){
			// chờ duyệt
			$pv_status = '</form>
			<form action="index.php" method="post" name="frmPending" id="frmPending">
				<input type="hidden" name="module" value="EC_Payment_Voucher" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="'.$this->bean->id.'" />
				<input type="hidden" name="return_module" value="EC_Payment_Voucher" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="'.$this->bean->id.'" />
				<input type="hidden" name="pv_status" value="1" />
				<input type="submit" class="btn btn-warning" name="btnPending" id="btnPending" value="Chờ duyệt" title="Chờ duyệt" style="font-weight:bold;" />
			</form>';
		}

		if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true) && $this->bean->pv_status == '1' && ACLController::checkAccess('Bugs', 'view', true) && is_admin($current_user)){
			// đã duyệt
			$pv_status = '</form>
			<form action="index.php" method="post" name="frmApproved" id="frmApproved">
				<input type="hidden" name="module" value="EC_Payment_Voucher" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="'.$this->bean->id.'" />
				<input type="hidden" name="return_module" value="EC_Payment_Voucher" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="'.$this->bean->id.'" />
				<input type="hidden" name="pv_status" value="2" />
				<input class="btn btn-info" type="submit" name="btnApproved" id="btnApproved" value="Đã duyệt" title="Đã duyệt" style="font-weight:bold;" />
			</form>';
		} 

		// Nút Đã chi
		if(ACLController::checkAccess('EC_Payment_Voucher', 'edit', true) && $this->bean->pv_status == '2' && ACLController::checkAccess('Bugs', 'edit', true) && is_admin($current_user)){
			$pv_status = '</form>
			<form action="index.php" method="post" name="frmPaid" id="frmPaid">
				<input type="hidden" name="module" value="EC_Payment_Voucher" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="'.$this->bean->id.'" />
				<input type="hidden" name="return_module" value="EC_Payment_Voucher" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="'. $this->bean->id .'" />
				<input type="hidden" name="pv_status" value="3" />
				<input type="hidden" name="hoanve_id" value="'. $this->bean->hoanve_id .'" />
				<input type="hidden" name="phieuthu_id" value="'. $this->bean->phieuthu_id .'" />
				<input type="hidden" name="ngayhachtoan" value="'. date("$date_format H:i") .'" /> 
				<input type="submit" class="btn btn-success fw-semibold" name="btnPaid" id="btnPaid" value="Đã chi" title="Đã chi" />
			</form>';
		}
		$this->ss->assign('PV_STATUS', $pv_status);
		
		// Tài khoản ngân hàng
		if(isset($this->bean->tknganhang_id) && !empty($this->bean->tknganhang_id)){
			$nh = new EC_Bank_Account();
			$nh->retrieve($this->bean->tknganhang_id);
			$this->bean->tknganhang = $nh->account_number.' - '.$this->bean->tknganhang;
		}
	}

	function populateCustomFields() {
		// nhân viên
		$employee = new User;
		$employee->retrieve($this->bean->employee_id);
		$this->ss->assign('EMPLOYEE_NAME', $employee->last_name.' '.$employee->first_name);
	}
}
?>