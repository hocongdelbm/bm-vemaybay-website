<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
		
class Viewcreatevouchers extends SugarView {
	function display() {
		$smartyCont = new Sugar_Smarty();
		$this->populateContent($smartyCont);
		$smartyCont->display('modules/EC_Vouchers/tpls/view_createvouchers.tpl');	
	}

	function populateContent($smarty) {
		// Thời hạn voucher
		// nếu là ngày cuối tháng thì thời hạn chạy sang tháng sau
		if(myCalculateDayBetweenDates(date('d-m-Y'), date('t-m-Y')) < 10) {
			$from_date = date('01-m-Y', strtotime("+1 month"));
			$to_date = date('t-m-Y', strtotime("+1 month"));
		} else {
			$from_date = date('d-m-Y');
			$to_date = date('t-m-Y');
		}

		$smarty->assign('FROM_DATE', $from_date);
		$smarty->assign('TO_DATE', $to_date);

		// tên chiến dịch
		if(!empty($_REQUEST['prefix'])) 
			$smarty->assign('PREFIX', $_REQUEST['prefix']);

		// tên chiến dịch
		if(!empty($_REQUEST['campaign_name'])) 
			$smarty->assign('CAMPAIGN_NAME', $_REQUEST['campaign_name']);

		// Mô tả chiến dịch
		// if(!empty($_REQUEST['description'])) 
		// 	$smarty->assign('VOUCHER_DESCRIPTION', $_REQUEST['description']);

		// code vourcher
		if(!empty($_REQUEST['name'])) 
			$smarty->assign('VOUCHER_NAME', $_REQUEST['name']);

		// Số lượng
		if(!empty($_REQUEST['qty'])) 
			$smarty->assign('VOUCHER_QTY', $_REQUEST['qty']);

		// Giá 
		if(!empty($_REQUEST['price'])) 
			$smarty->assign('VOUCHER_PRICE', $_REQUEST['price']);

		// Load các voucher vừa tạo
		if(!empty($_REQUEST['no']) && !empty($_REQUEST['qty'])) {
			$html = '<h1 id="voucher_list_title" class="title">Danh sách VOUCHER VỪA PHÁT HÀNH</h1>';
			$html .= '<table id="voucher_tbl_list" class="table-details__booking table_voucher--release" cellpadding="0" cellspacing="0">
						<thead>
							<th>STT</th>
							<th>Mã Voucher</th>
							<th>Thời hạn</th>
							<th>Mệnh giá</th>
						</thead><tbody>';

			$sql = 'SELECT id,
						name,
						reduce_amount,
						validate_from_date,
						validate_to_date
					FROM ec_vouchers 
					WHERE deleted = 0
						AND order_by_no >= '.($_REQUEST['no'] - $_REQUEST['qty'] + 1).'
						AND order_by_no <= ' . ($_REQUEST['no']) . '
					ORDER BY order_by_no DESC';

			$res = $this->bean->db->query($sql);
			$i = 1;
			while($row = $this->bean->db->fetchByAssoc($res)) {
				$html .= '<tr>
					<td align="center">'.($i++).'</td>
					<td align="center"><a href="index.php?module=EC_Vouchers&return_module=EC_Vouchers&action=DetailView&record='.$row['id'].'" target="_blank">'.$row['name'].'</a></td>
					<td align="center">'.date('d/m/Y', strtotime($row['validate_from_date'])).' - '.date('d/m/Y', strtotime($row['validate_to_date'])).'</td>
					<td align="center">'.format_number($row['reduce_amount']).'</td>
				</tr>';
			}
			$html .= '</tbody></table>';
			
			$smarty->assign('VOUCHER_TBL', $html);
		}
	}
}
