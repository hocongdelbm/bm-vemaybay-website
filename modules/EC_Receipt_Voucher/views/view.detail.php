<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_Receipt_VoucherViewDetail extends ViewDetail {	
	function display(){
		$this->displayJS();
		$this->populateCustomFields();
		$this->populateCustomButtons();
		parent::display();
	}
	
	function displayJS(){
		global $app_list_strings, $current_user;
		$js = '<script>
			var rv_status = "'.$this->bean->rv_status.'";
			var amount_type = "'.$this->bean->amount_type.'";
			const current_user_title = "'.trim($current_user->title).'";
		</script>';

		echo $js;
	}
	
	function populateCustomFields(){
		global $app_list_strings, $current_user;

		// Amount
		$amount = '<span>'.format_number($this->bean->amount).'
			<span '.($this->bean->amount_type != 'VND' ? '' : 'style="display:none"').'> - Quy đổi: '.format_number($this->bean->amount_converted).'</span>
		</span>';
		$this->ss->assign('AMOUNT', $amount);

        	$loaithu_arr = array('4', '5', '10', '11', '12', '13', '14', '16');
		$loai_thu = '<label>'.$app_list_strings['loai_thu_list'][(int)$this->bean->loai_thu].'</label>';
		if(($this->bean->loai_thu == 4 || $this->bean->loai_thu == 5) && ($this->bean->is_debt || !empty($this->bean->customer))) {
			$loai_thu .= '&nbsp;-&nbsp;Đối tượng:&nbsp;<label>' . $this->bean->customer . '</label>';
		}

		if(in_array((int)$this->bean->loai_thu, $loaithu_arr)) {
			$loai_thu .= '<table cellpadding="0" cellspacing="0" border="0" class="table-details__booking mt-2">
					<thead>
						<tr>
							<th style="width:50%;" class="text-start">Nhà cung cấp</th>
							<th style="width:25%;" class="text-center">Giá bán</th>
							<th style="width:25%;" class="text-center">Giá mua</th>
						</tr>
					</thead>';
					
			if($this->bean->supplier_id != ''){
				$loai_thu .= '
					<tr>
						<td class="text-start">'.$this->bean->supplier.'</td>
						<td class="text-end">'.format_number($this->bean->sell_amount).'</td>
						<td class="text-end">'.format_number($this->bean->bought_amount).'</td>
					</tr>';
			}
			
			if($this->bean->supplier2_id != ''){
				$loai_thu .= '
					<tr>
						<td class="text-start">'.$this->bean->supplier2.'</td>
						<td class="text-end">'.format_number($this->bean->sell_amount2).'</td>
						<td class="text-end">'.format_number($this->bean->bought_amount2).'</td>
					</tr>';
			}
			
			if($this->bean->supplier3_id != ''){
				$loai_thu .= '
					<tr>
						<td class="text-start">'.$this->bean->supplier3.'</td>
						<td class="text-end">'.format_number($this->bean->sell_amount3).'</td>
						<td class="text-end">'.format_number($this->bean->bought_amount3).'</td>
					</tr>';
			}
			$loai_thu .= '</table>';
		} 
		else if(!empty($this->bean->customer)) {
			$loai_thu .= '&nbsp;-&nbsp;Đối tượng:&nbsp;<label>'.$this->bean->customer.'</label>';
		} 
		else if(!empty($this->bean->employee_id)) {
			$employee = new User;
			$employee->retrieve($this->bean->employee_id);
			$loai_thu .= '&nbsp;-&nbsp;Nhân viên:&nbsp;<label>'.$employee->last_name.' '.$employee->first_name.'</label>';
		}
		
		$this->ss->assign('LOAI_THU', $loai_thu);

		$rv_status = '<div class="d-flex align-items-center justify-content-between">
					<span class="fw-bold" style="color:'.$app_list_strings['receipt_voucher_status_color_list'][$this->bean->rv_status].';">'.$app_list_strings['receipt_voucher_status_list'][$this->bean->rv_status].'</span>';
				
		if(!empty($this->bean->delivery_man)){
			$rv_status .= '<span>- Giao thực phẩm: ' . $this->bean->delivery_man.'</span>';
		}

		$rv_status .= '</div>';
		
		$this->ss->assign('RV_STATUS', $rv_status);
	}
	
	function populateCustomButtons(){
		global $app_list_strings, $current_user, $timedate;
		$date_format = $timedate->get_date_format();
		
		// Ngày hạch toán
		// $this->bean->ngayhachtoan = date($date_format.' H:i', strtotime($this->bean->ngayhachtoan) + 7*3600);
		$this->bean->ngayhachtoan = date($date_format.' H:i', strtotime($this->bean->ngayhachtoan) - 7*3600);
		
		// Nút in phiếu
		if(ACLController::checkAccess('EC_Receipt_Voucher', 'view', true)) {
			$dep_arr = myGetAllDepByCurrentUser();
			$print_rv = '</form>
			<form action="index.php?print=true" name="frmPrintRV" method="post" target="_blank">
		  		<input type="hidden" name="module" value="EC_Receipt_Voucher" />
			  	<input type="hidden" name="action" value="printrv" />
			  	<input type="hidden" name="print" value="true" />
			  	<input type="hidden" name="record" value="'.$this->bean->id.'" />
				<select class="box-select" name="dep_id" id="dep_id">'.myMakeHtmlOption($dep_arr, isset($_POST['dep_id']) ? $_POST['dep_id'] : $current_user->department_id).'</select>
			  	<input onclick="Set_Cookie(\'showLeftCol\',\'false\',30,\'/\',\'\',\'\')" type="submit" class="btn btn-primary" name="btnPrintRV" value="In phiếu" title="In phiếu" />
			</form>';
			$this->ss->assign('PRINT_RV', $print_rv);
		}
		
		// Nút đã thu hoặc tạo hóa đơn
		$change_status = '';
		if( ($this->bean->rv_status == '0' || $this->bean->rv_status == '2') 
			&& 
			(
				ACLController::checkAccess('EC_Payment_Voucher', 'delete', true) 
				|| $current_user->id == 'd61ac0c1-91b3-0dc8-049a-518b21d2deb9' // Chung Thanh Nhân - nhanchung
			)
		) {
			// <input type="hidden" name="ngayhachtoan" value="'.date($date_format.' H:i', strtotime(date('d-m-Y H:i'))+7*3600).'" /> 
			$change_status = '</form>
			<form action="index.php" name="frmChangeStatus" id="frmChangeStatus" method="post">
				<input type="hidden" name="module" value="EC_Receipt_Voucher" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="'.$this->bean->id.'" />
				<input type="hidden" name="return_module" value="EC_Receipt_Voucher" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="'.$this->bean->id.'" />
				<input type="hidden" name="rv_status" value="1" />
				<input type="hidden" name="ngayhachtoan" value="'.date($date_format.' H:i', strtotime(date('d-m-Y H:i'))+7*3600).'" /> 
				<input type="hidden" name="booking_id" value="'.$this->bean->booking_id.'" />
				<input type="hidden" name="booking_name" value="'.$this->bean->booking_name.'" />
				<input type="submit" class="btn btn-success" name="btnChangeStatus" value="Đã thu" title="Đã thu" />
			</form>';
			$this->ss->assign('CHANGE_STATUS', $change_status);
		} 
		else if($this->bean->rv_status == '1' && !$this->isSalesInvoiceExist($this->bean->id) && ACLController::checkAccess('EC_HoaDonBan', 'edit', true)) {
			$bk = new EC_Flight_Bookings();
			$bk->retrieve($this->bean->booking_id);
			
			$change_status = '</form>
			<form action="index.php" name="frmChangeStatus" id="frmChangeStatus" method="post">
				<input type="hidden" name="module" value="EC_HoaDonBan" />
				<input type="hidden" name="action" value="EditView" />
				<input type="hidden" name="phieuthu_id" value="'.$this->bean->id.'" />
				<input type="hidden" name="phieuthu" value="'.$this->bean->name.'" />
				<input type="hidden" name="booking_id" value="'.$this->bean->booking_id.'" />
				<input type="hidden" name="booking" value="'.$this->bean->booking_name.'" />
				<input type="hidden" name="doituong_id" value="'.$bk->account_id.'" />
				<input type="hidden" name="doituong" value="'.$bk->account_name.'" />
				<input type="hidden" name="diachi" value="'.$bk->address.'" />
				<input type="hidden" name="lienhe" value="'.$bk->contact_name.'" />
				<input type="hidden" name="pt_thanhtoan" value="'.$this->bean->receipt_type.'" />
				<input type="hidden" name="phihanhly" value="'.$bk->luggage_fee.'" />
				<input type="hidden" name="phihoandoive" value="" />
				<input type="hidden" name="phidichvu" value="" />
				<input type="hidden" name="giamgia" value="'.$bk->discount_amount.'" />
				<input type="hidden" name="ptram_giamgia" value="'.$bk->discount_percent.'" />
				<input type="hidden" name="tongtien" value="'.$bk->total_amount.'" />
				<input type="submit" class="btn btn-warning" name="frmChangeStatus" value="Tạo hóa đơn" title="Tạo hóa đơn" style="font-weight:bold;" />
			</form>';
			$this->ss->assign('CHANGE_STATUS', $change_status);	
		}	
		
		// Thay đổi tình trạng phiếu thu - admin only
		if(ACLController::checkAccess('EC_Receipt_Voucher', 'delete', true)){
		  $admin_change_status = '</form>
		  	<form action="index.php" name="frmAdminChangeStatus" id="frmAdminChangeStatus" method="post">
				<input type="hidden" name="module" value="EC_Receipt_Voucher" />
			  	<input type="hidden" name="action" value="Save" />
			  	<input type="hidden" name="record" value="'.$this->bean->id.'" />
			  	<input type="hidden" name="return_module" value="EC_Receipt_Voucher" />
			  	<input type="hidden" name="return_action" value="DetailView" />
			  	<input type="hidden" name="return_id" value="'.$this->bean->id.'" />
			  	<input type="hidden" name="booking_id" value="'.$this->bean->booking_id.'" />
			  	<input type="hidden" name="booking_name" value="'.$this->bean->booking_name.'" />
			  	<select class="box-select" name="rv_status" id="rv_status">'.get_select_options_with_id($app_list_strings['receipt_voucher_status_list'], (int)$this->bean->rv_status).'</select>
			  	<input type="submit" class="btn btn-primary" name="btnAdminChangeStatus" value="Đổi tình trạng" title="Đổi tình trạng" style="font-weight:bold" />
		  	</form>';
			$this->ss->assign('ADMIN_CHANGE_STATUS', $admin_change_status);	
		}
		
		// Tài khoản ngân hàng
		if(isset($this->bean->tknganhang_id) && !empty($this->bean->tknganhang_id)){
			$ba = new EC_Bank_Account();
			$ba->retrieve($this->bean->tknganhang_id);
			$this->bean->tknganhang = $ba->account_number.' - '.$this->bean->tknganhang;
		}

		// Nút công nợ
		if(empty($this->bean->rv_status)) {
			$debt_btn = '</form>
				<form method="post" action="index.php">
					<input type="hidden" name="module" value="EC_Receipt_Voucher">
					<input type="hidden" name="action" value="Save">
					<input type="hidden" name="rv_status" value="2">
					<input type="hidden" name="record" value="' . $this->bean->id . '">
					<input type="submit" class="btn btn-warning fw-bold" value="Công nợ">
				</form>';
			$this->ss->assign('DEBT', $debt_btn);
		}
	}
	
	// Kiểm tra xem đã có hóa đơn bán nào thuộc phiếu thu này?
	function isSalesInvoiceExist($phieuthu_id){
		$sql = "SELECT COUNT(id) FROM ec_hoadonban
				WHERE phieuthu_id='".$phieuthu_id."' AND deleted=0 ";
		$rowcount = $this->bean->db->getOne($sql);
		if($rowcount > 0)
			return true;
		return false;
	}
}
