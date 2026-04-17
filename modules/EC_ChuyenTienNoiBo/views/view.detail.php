<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_ChuyenTienNoiBoViewDetail extends ViewDetail {	
	function display(){
		$this->populateCustomButtons();
		$this->displayJS();
		parent::display();
	}
	
	function displayJS(){
		$js = '<script>
			var ghiso = '.$this->bean->ghiso.';
		</script>';
		echo $js;
	}
	
	function populateCustomButtons(){
		global $timedate, $app_list_strings;
		$date_format = $timedate->get_date_format();
		
		$this->bean->tutknganhang = $this->bean->tutienmat == 1 ? 'Từ tiền mặt - ' . $this->bean->tudiadiem : $this->bean->tutknganhang;
		$this->bean->dentknganhang = $this->bean->dentienmat == 1 ? 'Đến tiền mặt - ' . $this->bean->dendiadiem : $this->bean->dentknganhang;
		
		// Ngay hach toan
		// $this->bean->ngayhachtoan = date($date_format.' H:i', strtotime($this->bean->ngayhachtoan)+7*3600);
		// $this->bean->ngayhachtoan = (isset($this->bean->ngayhachtoan) && !empty($this->bean->ngayhachtoan)) ? date($date_format.' H:i', strtotime($this->bean->ngayhachtoan)+7*3600) : date($date_format.' H:i');
		$this->bean->ngayhachtoan = date($date_format.' H:i', strtotime($this->bean->ngayhachtoan) - 7*3600);

		// var_dump($this->bean->ngayhachtoan);
		
		if(ACLController::checkAccess('EC_ChuyenTienNoiBo', 'edit', true)){
			$ghiso_value = $this->bean->ghiso == 0 ? 'Ghi sổ' : 'Bỏ ghi';
			$ghiso = '</form>
			<form action="index.php" method="post" name="frmGhiSo" id="frmGhiSo">
				<input type="hidden" name="module" value="EC_ChuyenTienNoiBo" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="'.$this->bean->id.'" />
				<input type="hidden" name="return_module" value="EC_ChuyenTienNoiBo" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="'.$this->bean->id.'" />
				<input type="hidden" name="ghiso" value="'.($this->bean->ghiso == 0 ? 1 : 0).'" />
				<input type="submit" class="btn btn-warning" name="btnGhiSo" id="btnGhiSo" value="'.$ghiso_value.'" title="'.$ghiso_value.'" style="font-weight:bold;" />
			</form>';
			$this->ss->assign('GHISO', $ghiso);	
		}
		
		// Nút in phiếu
		if(ACLController::checkAccess('EC_ChuyenTienNoiBo', 'view', true)){
			$print_voucher = '</form>
			<form action="index.php" name="frmPrintVoucher" method="post" target="_blank">
		  		<input type="hidden" name="module" value="EC_ChuyenTienNoiBo" />
			  	<input type="hidden" name="action" value="printvoucher" />
			  	<input type="hidden" name="print" value="true" />
			  	<input type="hidden" name="record" value="'.$this->bean->id.'" />
			  	<input type="submit" class="btn btn-info" name="btnPrintVoucher" value="In phiếu" title="In phiếu" style="font-weight:bold;" />
			</form>';
			$this->ss->assign('PRINT_VOUCHER', $print_voucher);
		}
		
		// Từ tài khoản ngân hàng
		if(isset($this->bean->tutknganhang_id) && !empty($this->bean->tutknganhang_id)){
			$nh = new EC_Bank_Account();
			$nh->retrieve($this->bean->tutknganhang_id);
			$this->bean->tutknganhang = $nh->account_number.' - '.$this->bean->tutknganhang;
		}
		
		// Đến tài khoản ngân hàng
		if(isset($this->bean->dentknganhang_id) && !empty($this->bean->dentknganhang_id)){
			$nh = new EC_Bank_Account();
			$nh->retrieve($this->bean->dentknganhang_id);
			$this->bean->dentknganhang = $nh->account_number.' - '.$this->bean->dentknganhang;
		}
	}
}
?>