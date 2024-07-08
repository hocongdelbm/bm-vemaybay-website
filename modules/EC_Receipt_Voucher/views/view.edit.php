<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.edit.php');

class EC_Receipt_VoucherViewEdit extends ViewEdit {
	function __construct() {
		parent::__construct();
	}
	
	function display(){
		if(empty($this->bean->id) || $this->bean->rv_status=='0' || (isset($_POST['isDuplicate']) && $_POST['isDuplicate'])) {
			$this->displayCSS();
			$this->displayJS();
			$this->customFields();
			parent::display();
		} 
		else echo '<p class="error">Chứng từ đã khóa</p>';
	}

	function displayCSS() {
		$css = '';
		$css .= '<link type="text/css" rel="stylesheet" href="themes/SuiteP/libs/css/select2.min.css">';
		$css .= '<link type="text/css" rel="stylesheet" href="modules/EC_Receipt_Voucher/css/view.edit.css">';

		echo $css;
	}
	
	function displayJS(){
		$js = '<script>
			var record = "'.$this->bean->id.'";
			var loai_thu = "'.$this->bean->loai_thu.'";
			var amount_type = "'.$this->bean->amount_type.'";
		</script>';

		echo $js;
	}
	
	function customFields(){
		global $app_list_strings, $locale, $timedate, $current_user;
		$date_format = $timedate->get_date_format();

		$sep = my_get_number_separators();
		$group_decimal = '<input type="hidden" id="grp_seperator" name="grp_seperator" value="'.$sep[0].'" />
		<input type="hidden" id="dec_seperator" name="dec_seperator" value="'.$sep[1].'" />
		<input type="hidden" id="sig_digits" name="sig_digits" value="'.$locale->getPrecision().'" />';

		// Get department id
		$department_id = $current_user->department_id;

		// Amount
		$amount = '<span class="d-flex gap-2 align-items-center w-100">
			<input class="flex-fill min-w-25" type="text" name="amount" id="amount" size="20" value="'.(isset($_POST['amount']) ? $_POST['amount'] : format_number($this->bean->amount)).'" tabindex="100">
			<span class="w-100" id="span-amt-converted" '.($this->bean->amount_type != 'VND' ? '' : 'style="display:none"').'>
				<span class="w-33">- Quy đổi: </span>
				<input class="flex-fill" readonly="readonly" type="text" name="amount_converted" id="amount_converted" size="20" value="'.(isset($_POST['amount_converted']) ? $_POST['amount_converted'] : format_number($this->bean->amount_converted)).'" tabindex="100">
			</span>
		</span>';
		$this->ss->assign('AMOUNT', $amount);


		// Amount type
		$amount_type = '<span class="d-flex gap-2 align-items-center">
			<select id="amount_type" name="amount_type" tabindex="101">'.get_select_options_with_id($app_list_strings['loaitien_list'], isset($this->bean->amount_type) ? $this->bean->amount_type : 'VND').'</select>
			<span id="span-exchange-rate" '.($this->bean->amount_type != 'VND' ? '' : 'style="display:none"').'>
				<span class="w-25">- Tỷ giá: </span>
				<input class="flex-fill" type="text" id="exchange_rate" name="exchange_rate" tabindex="101" size="13" value="'.(isset($this->bean->exchange_rate) ? format_number($this->bean->exchange_rate) : 0).'">
			</span>
		</span>';
		$this->ss->assign('AMOUNT_TYPE', $amount_type);

		// NGAY HACH TOAN
		// $this->bean->ngayhachtoan = isset($this->bean->ngayhachtoan) && !empty($this->bean->ngayhachtoan) ? date($date_format.' H:i', strtotime($this->bean->ngayhachtoan)+7*3600) : date($date_format.' H:i');
		$this->bean->ngayhachtoan = isset($this->bean->ngayhachtoan) && !empty($this->bean->ngayhachtoan) ? date($date_format.' H:i', strtotime($this->bean->ngayhachtoan) - 7*3600) : date($date_format.' H:i', strtotime(date('d-m-Y H:i'))+7*3600);


		// TAI KHOAN NGAN HANG
		$display = (isset($_POST['receipt_type']) && $_POST['receipt_type'] == 'credit_transfer') || $this->bean->receipt_type ==  'credit_transfer' ? '' : 'display:none';
		$display2 = (isset($_POST['receipt_type']) && $_POST['receipt_type'] == 'cash') || $this->bean->receipt_type ==  'cash' ? '' : 'display:none';
		$tknganhang_id = isset($this->bean->tknganhang_id) ? $this->bean->tknganhang_id : '';
		

		// PHAN QUYEN
		$tknganhang_group = "";
		// if(is_admin($current_user))
		// 	$tknganhang_group = "";
		// else
		// 	$tknganhang_group = " AND ".SecurityGroup::getGroupWhere("t","EC_Bank_Account",$current_user->id);
			
		$receipt_type = '<select name="receipt_type" id="receipt_type" title="" tabindex="104">'.get_select_options_with_id($app_list_strings['receipt_type_list'], isset($_POST['receipt_type']) ? $_POST['receipt_type'] : $this->bean->receipt_type).'</select>';
		$receipt_type .= '<select style="'.$display.'" id="tknganhang_id" name="tknganhang_id" tabindex="104"><option value=""></option>'.myGetBankAccountList($tknganhang_id, $tknganhang_group).'</select>';
		$receipt_type .= '<select id="com_location_id" name="com_location_id" class="w-100" style="'.$display2.'" tabindex="104">'.myGetLocationListByDepID($department_id, $this->bean->com_location_id).'</select>';
		$this->ss->assign('RECEIPT_TYPE', $receipt_type.$group_decimal);
		

		// LOAI THU
		$loaithu = '<style>
			.ui-autocomplete-loading {
				background: white url(custom/jqueryui/css/ui-lightness/images/ui-anim_basic_16x16.gif) right center no-repeat;
			} 
		</style>';
		$loaithu .= '<div class="d-flex gap-2 flex-column">
		<div class="loai_thu--wrap d-inline-flex gap-2 align-items-center">
		<select id="loai_thu" name="loai_thu" tabindex="106" class="box-select">'.get_select_options_with_id($app_list_strings['loai_thu_list'], (int)$this->bean->loai_thu).'</select>';

		$loaithu .= '<div id="span_customer" class="flex-fill">
					<div class="d-flex gap-1">
						<input type="text" class="flex-fill" name="customer" id="customer" tbl="accounts" fld=\'{"id":"account_id_c", "name":"customer"}\' tabindex="106" size="20" autocomplete="off" value="'.(isset($_POST['customer']) ? $_POST['customer'] : $this->bean->customer).'" />
						<input type="hidden" name="account_id_c" id="account_id_c" value="'.(isset($_POST['account_id_c']) ? $_POST['account_id_c'] : $this->bean->account_id_c).'" />
						<button type="button" name="btnSelectAccount" id="btnSelectAccount" tabindex="0" title="Chọn" class="px-1 btn btn-primary" value="Chọn">
							<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg>
						</button>
						<button type="button" name="btnClearAccount" id="btnClearAccount" tabindex="0" title="Xóa" class="px-1 btn btn-secondary" value="Xóa">
							<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
						</button>
					</div>
				</div>
			</div>';

        	$loaithu_arr = array('4', '5', '10', '11', '12', '13', '14', '16');
		$loaithu .= '<span id="span_supplier" '.(in_array($this->bean->loai_thu, $loaithu_arr) ? '' : 'style="display:none;"').'>
		<table border="0" width="100%" cellpadding="0" cellspacing="0" style="line-height:20px;">';
		$loaithu .= '<tr>
			<td style="width:60%; font-weight:bold; text-align:left;">Nhà cung cấp</td>
			<td style="width:20%; font-weight:bold; text-align:center;">Giá bán</td>
			<td style="width:20%; font-weight:bold; text-align:center;">Giá mua</td>
		</tr>';
		$loaithu .= '<tr>
			<td style="text-align:left; padding:3px;" >
				<select id="supplier_id" name="supplier_id" tabindex="106" class="w-100">
					<option value=""></option>
					'.myGetSelectOptionsWithDb('Accounts', (isset($this->bean->supplier_id) ? $this->bean->supplier_id : ''), 'id', " AND account_type='Supplier' AND is_stop_tracking = 0 ").'
				</select>
			</td>
			<td style="text-align:left; padding:3px;">
				<input class="allow-number-only" type="text" id="sell_amount" name="sell_amount" value="'.format_number(isset($this->bean->sell_amount) ? $this->bean->sell_amount : 0).'" tabindex="106" style="width:100%;" />
			</td>
			<td style="text-align:left; padding:3px;">
				<input class="allow-number-only" type="text" id="bought_amount" name="bought_amount" value="'.format_number(isset($this->bean->bought_amount) ? $this->bean->bought_amount : 0).'" tabindex="106" style="width:100%;" />
			</td>
		</tr>';
		$loaithu .= '<tr>
			<td style="text-align:left; padding:3px;" >
				<select id="supplier2_id" name="supplier2_id" tabindex="106" class="w-100">
					<option value=""></option>
					'.myGetSelectOptionsWithDb('Accounts', (isset($this->bean->supplier2_id) ? $this->bean->supplier2_id : ''), 'id', " AND account_type='Supplier' AND is_stop_tracking = 0 ").'
				</select>
			</td>
			<td style="text-align:left; padding:3px;">
				<input class="allow-number-only" type="text" id="sell_amount2" name="sell_amount2" value="'.format_number(isset($this->bean->sell_amount2) ? $this->bean->sell_amount2 : 0).'" tabindex="106" style="width:100%;" />
			</td>
			<td style="text-align:left; padding:3px;">
				<input class="allow-number-only" type="text" id="bought_amount2" name="bought_amount2" value="'.format_number(isset($this->bean->bought_amount2) ? $this->bean->bought_amount2 : 0).'" tabindex="106" style="width:100%;" />
			</td>
		</tr>';
		$loaithu .= '<tr>
			<td style="text-align:left; padding:3px;" >
				<select id="supplier3_id" name="supplier3_id" tabindex="106" class="w-100">
					<option value=""></option>
					'.myGetSelectOptionsWithDb('Accounts', (isset($this->bean->supplier3_id) ? $this->bean->supplier3_id : ''), 'id', " AND account_type='Supplier' AND is_stop_tracking = 0 ").'
				</select>
			</td>
			<td style="text-align:left; padding:3px;">
				<input class="allow-number-only" type="text" id="sell_amount3" name="sell_amount3" value="'.format_number(isset($this->bean->sell_amount3) ? $this->bean->sell_amount3 : 0).'" tabindex="106" style="width:100%;" />
			</td>
			<td style="text-align:left; padding:3px;">
				<input class="allow-number-only" type="text" id="bought_amount3" name="bought_amount3" value="'.format_number(isset($this->bean->bought_amount3) ? $this->bean->bought_amount3 : 0).'" tabindex="106" style="width:100%;" />
			</td>
		</tr>';
		$loaithu .= '</table></span></div>';
		$this->ss->assign('LOAI_THU', $loaithu);


		// Nhân viên
		$employee_arr = $this->getEmployeeList();
		$employee_list = '<select id="employee-select" name="employee_id"><option value="">-- Trống --</option>'.get_select_options_with_id($employee_arr, $this->bean->employee_id).'</select>';
		$this->ss->assign('EMPLOYEE_NAME', $employee_list);
	}
	
	// Lấy danh sách nhân viên
	function getEmployeeList() {
		$sql = 'SELECT id, CONCAT(last_name, " ", IFNULL(first_name, "")) AS full_name 
				FROM users 
				WHERE deleted = 0 
				-- AND status = "Active" 
				AND title NOT IN ("Admin", "Bot")';
		$res = $this->bean->db->query($sql);
		while($row = $this->bean->db->fetchByAssoc($res)) {
			$employee_list[$row['id']] = $row['full_name'];
		}

		return $employee_list;
	}
}
