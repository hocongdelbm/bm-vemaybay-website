<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class EC_Receipt_VoucherViewDetail extends ViewDetail {
	/** @var EC_Receipt_Voucher **/
	public $bean;

	function display()
	{
		$this->displayJS();
		$this->populateCustomFields();
		$this->populateCustomButtons();
		parent::display();
	}

	function displayJS()
	{
		global $current_user;
		$js = '<script>
			var rv_status = "' . $this->bean->rv_status . '";
			var amount_type = "' . $this->bean->amount_type . '";
			var loai_thu = "' . $this->bean->loai_thu . '";
			var amount = ' . (float)$this->bean->amount . ';
			var total_sell = ' . ((float)$this->bean->sell_amount + (float)$this->bean->sell_amount2 + (float)$this->bean->sell_amount3) . ';
			const current_user_title = "' . trim($current_user->title) . '";
		</script>';

		echo $js;
	}

	function populateCustomFields()
	{
		global $app_list_strings;

		// Amount
		$amount = '<span>' . format_number($this->bean->amount) . '
			<span ' . ($this->bean->amount_type != 'VND' ? '' : 'style="display:none"') . '> - Quy đổi: ' . format_number($this->bean->amount_converted) . '</span>
		</span>';
		$this->ss->assign('AMOUNT', $amount);

		$loaithu_arr = ['4', '5', '10', '11', '12', '13', '14', '16', '27'];
		$loai_thu = '<label>' . $app_list_strings['loai_thu_list'][(int)$this->bean->loai_thu] . '</label>';
		if (($this->bean->loai_thu == 4 || $this->bean->loai_thu == 5) && ($this->bean->is_debt || !empty($this->bean->customer))) {
			$loai_thu .= '&nbsp;-&nbsp;Đối tượng:&nbsp;<label>' . $this->bean->customer . '</label>';
		}

		if (in_array((int)$this->bean->loai_thu, $loaithu_arr)) {
			$loai_thu .= '<table cellpadding="0" cellspacing="0" border="0" class="table-details__booking mt-2">
					<thead>
						<tr>
							<th style="width:38%;" class="text-start">Nhà cung cấp</th>
							<th style="width:12%;" class="text-center">Chiều bay</th>
							<th style="width:12%;" class="text-center">Hãng</th>
							<th style="width:19%;" class="text-center">Giá bán</th>
							<th style="width:19%;" class="text-center">Giá mua</th>
						</tr>
					</thead>';

			foreach ([
				[$this->bean->supplier_id,  $this->bean->supplier,  $this->bean->sell_amount,  $this->bean->bought_amount,  $this->bean->sup_direction],
				[$this->bean->supplier2_id, $this->bean->supplier2, $this->bean->sell_amount2, $this->bean->bought_amount2, $this->bean->sup_direction2],
				[$this->bean->supplier3_id, $this->bean->supplier3, $this->bean->sell_amount3, $this->bean->bought_amount3, $this->bean->sup_direction3],
			] as [$id, $name, $sell, $buy, $dir]) {
				if ($id !== '') {
					$loai_thu .= $this->buildSupplierRow($name, $sell, $buy, $id, $dir);
				}
			}
			$loai_thu .= '</table>';
		} else if (!empty($this->bean->customer)) {
			$loai_thu .= '&nbsp;-&nbsp;Đối tượng:&nbsp;<label>' . $this->bean->customer . '</label>';
		} else if (!empty($this->bean->employee_id)) {
			$employee = new User;
			$employee->retrieve($this->bean->employee_id);
			$loai_thu .= '&nbsp;-&nbsp;Nhân viên:&nbsp;<label>' . $employee->last_name . ' ' . $employee->first_name . '</label>';
		}

		$this->ss->assign('LOAI_THU', $loai_thu);

		$rv_status = '<div class="d-flex align-items-center justify-content-between">
					<span class="fw-bold" style="color:' . $app_list_strings['receipt_voucher_status_color_list'][$this->bean->rv_status] . ';">' . $app_list_strings['receipt_voucher_status_list'][$this->bean->rv_status] . '</span>';

		if (!empty($this->bean->delivery_man)) {
			$rv_status .= '<span>- Giao thực phẩm: ' . $this->bean->delivery_man . '</span>';
		}

		$rv_status .= '</div>';

		$this->ss->assign('RV_STATUS', $rv_status);
	}

	function populateCustomButtons()
	{
		global $app_list_strings, $current_user, $timedate;
		$date_format = $timedate->get_date_format();

		// Ngày hạch toán (Giờ lưu dưới DB là giờ VietNam)
		$this->bean->ngayhachtoan = date("$date_format H:i", strtotime($this->bean->ngayhachtoan) - 7 * 3600);

		// Nút in phiếu
		if (ACLController::checkAccess('EC_Receipt_Voucher', 'view', true)) {
			$dep_arr = is_admin($current_user) ? SecurityGroup::getAllSecurityGroups() : SecurityGroup::getUserSecurityGroups($current_user->id);
			$dep_options = [];
			foreach ($dep_arr as $id => $item) {
				$dep_options[$id] = $item['name'];
			}

			$print_rv = '</form>
			<form action="index.php?print=true" name="frmPrintRV" method="post" target="_blank">
		  		<input type="hidden" name="module" value="EC_Receipt_Voucher" />
			  	<input type="hidden" name="action" value="printrv" />
			  	<input type="hidden" name="print" value="true" />
			  	<input type="hidden" name="record" value="' . $this->bean->id . '" />
				<select class="box-select" name="dep_id" id="dep_id">' . get_select_options_with_id($dep_options, isset($_POST['dep_id']) ? $_POST['dep_id'] : 'f15f801d-a9bc-cc92-4152-655f5e89867f') . '</select>
			  	<input onclick="Set_Cookie(\'showLeftCol\',\'false\',30,\'/\',\'\',\'\')" type="submit" class="btn btn-primary" name="btnPrintRV" value="In phiếu" title="In phiếu" />
			</form>';
			$this->ss->assign('PRINT_RV', $print_rv);
		}

		// Nút đã thu hoặc tạo hóa đơn
		$change_status = '';
		if (($this->bean->rv_status == '0' || $this->bean->rv_status == '2')
			&&
			(
				ACLController::checkAccess('EC_Payment_Voucher', 'delete', true)
				|| $current_user->id == 'd61ac0c1-91b3-0dc8-049a-518b21d2deb9' // Chung Thanh Nhân - nhanchung
			)
		) {
			$change_status = '</form>
			<form action="index.php" name="frmChangeStatus" id="frmChangeStatus" method="post">
				<input type="hidden" name="module" value="EC_Receipt_Voucher" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="record" value="' . $this->bean->id . '" />
				<input type="hidden" name="return_module" value="EC_Receipt_Voucher" />
				<input type="hidden" name="return_action" value="DetailView" />
				<input type="hidden" name="return_id" value="' . $this->bean->id . '" />
				<input type="hidden" name="rv_status" value="1" />
				<input type="hidden" name="ngayhachtoan" value="' . date("$date_format H:i") . '" /> 
				<input type="hidden" name="booking_id" value="' . $this->bean->booking_id . '" />
				<input type="hidden" name="booking_name" value="' . $this->bean->booking_name . '" />
				<input type="submit" class="btn btn-success" name="btnChangeStatus" value="Đã thu" title="Đã thu" />
			</form>';
			$this->ss->assign('CHANGE_STATUS', $change_status);
		} else if ($this->bean->rv_status == '1' && ACLController::checkAccess('EC_HoaDonBan', 'edit', true)) {
			$change_status = '</form>
			<form action="index.php" name="frmChangeStatus" id="frmChangeStatus" method="post">
				<input type="hidden" name="module" value="EC_HoaDonBan" />
				<input type="hidden" name="action" value="EditView" />
				<input type="hidden" name="phieuthu_id" value="' . $this->bean->id . '" />
				<input type="hidden" name="phieuthu" value="' . $this->bean->name . '" />
				<input type="hidden" name="booking_id" value="' . $this->bean->booking_id . '" />
				<input type="hidden" name="booking" value="' . $this->bean->booking_name . '" />
				<input type="hidden" name="doituong_id" value="' . $this->bean->account_id . '" />
				<input type="hidden" name="doituong" value="' . $this->bean->account_name . '" />
				<input type="hidden" name="diachi" value="' . $this->bean->address . '" />
				<input type="hidden" name="lienhe" value="' . $this->bean->contact_name . '" />
				<input type="hidden" name="pt_thanhtoan" value="' . $this->bean->receipt_type . '" />
				<input type="hidden" name="phihanhly" value="' . $this->bean->luggage_fee . '" />
				<input type="hidden" name="phihoandoive" value="" />
				<input type="hidden" name="phidichvu" value="" />
				<input type="hidden" name="giamgia" value="' . $this->bean->discount_amount . '" />
				<input type="hidden" name="ptram_giamgia" value="' . $this->bean->discount_percent . '" />
				<input type="hidden" name="tongtien" value="' . $this->bean->total_amount . '" />
				<input type="submit" class="btn btn-warning" name="frmChangeStatus" value="Tạo hóa đơn" title="Tạo hóa đơn" style="font-weight:bold;" />
			</form>';
			$this->ss->assign('CHANGE_STATUS', $change_status);
		}

		// Thay đổi tình trạng phiếu thu - admin only
		if (ACLController::checkAccess('EC_Receipt_Voucher', 'delete', true)) {
			$admin_change_status = '</form>
		  	<form action="index.php" name="frmAdminChangeStatus" id="frmAdminChangeStatus" method="post">
				<input type="hidden" name="module" value="EC_Receipt_Voucher" />
			  	<input type="hidden" name="action" value="Save" />
			  	<input type="hidden" name="record" value="' . $this->bean->id . '" />
			  	<input type="hidden" name="return_module" value="EC_Receipt_Voucher" />
			  	<input type="hidden" name="return_action" value="DetailView" />
			  	<input type="hidden" name="return_id" value="' . $this->bean->id . '" />
			  	<input type="hidden" name="booking_id" value="' . $this->bean->booking_id . '" />
			  	<input type="hidden" name="booking_name" value="' . $this->bean->booking_name . '" />
			  	<select class="box-select" name="rv_status" id="rv_status">' . get_select_options_with_id($app_list_strings['receipt_voucher_status_list'], (int)$this->bean->rv_status) . '</select>
			  	<input type="submit" class="btn btn-primary" name="btnAdminChangeStatus" value="Đổi tình trạng" title="Đổi tình trạng" style="font-weight:bold" />
		  	</form>';
			$this->ss->assign('ADMIN_CHANGE_STATUS', $admin_change_status);
		}

		// Tài khoản ngân hàng
		if (isset($this->bean->tknganhang_id) && !empty($this->bean->tknganhang_id)) {
			$ba = new EC_Bank_Account();
			$ba->retrieve($this->bean->tknganhang_id);
			$this->bean->tknganhang = $ba->account_number . ' - ' . $this->bean->tknganhang;
		}

		// Nút công nợ
		if (empty($this->bean->rv_status)) {
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

	private function buildSupplierRow($name, $sellAmount, $boughtAmount, $supplierId = '', $direction = '')
	{
		global $app_list_strings;

		$resolved = resolveRVSupplierAirline($this->bean->booking_id, $supplierId, $direction);
		$dirLabel = ($resolved['direction'] !== null && $resolved['direction'] !== '')
			? ($app_list_strings['bk_direction_list'][$resolved['direction']] ?? '')
			: '';
		if ($resolved['ambiguous']) {
			$airlineLabel = '<span style="color:#c00;" title="Không xác định được hãng - hãy chọn chiều bay trên phiếu">? (chọn chiều)</span>';
		} else {
			$airlineLabel = $resolved['airline_code'] ?? '';
		}

		return '<tr>
				<td class="text-start">' . $name . '</td>
				<td class="text-center">' . $dirLabel . '</td>
				<td class="text-center">' . $airlineLabel . '</td>
				<td class="text-end">' . format_number($sellAmount) . '</td>
				<td class="text-end">' . format_number($boughtAmount) . '</td>
			</tr>';
	}
}
