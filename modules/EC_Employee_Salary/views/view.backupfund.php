<?php
 
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewbackupfund extends SugarView {
 	function display() {
 		$smarty = new Sugar_Smarty();
 		$this->populateContent($smarty);
 		$smarty->display('modules/EC_Employee_Salary/tpls/view_backupfund.tpl');
 	}

 	function populateContent($smarty) {
 		// năm 
 		for($i = date('Y') - 1; $i <= date('Y') + 1; $i++) {
 			$year[$i] = $i;
 		}
 		if(empty($_POST['year'])) $year_val = date('Y');
 		else $year_val = $_POST['year'];
 		$smarty->assign('YEAR', get_select_options_with_id($year, $year_val));

 		// bảng dự phòng theo từng tháng
 		for($m = 1; $m <= 12; $m++) {
	 		$sql_backupm = 'SELECT SUM(0.1 * IFNULL(sales, 0)) AS fund 
			 				FROM ec_employee_salary 
			 				WHERE deleted = 0 
			 				AND year = "'.$year_val.'"
			 				AND month = "'.$m.'"';
	 		$backupm = $this->bean->db->getOne($sql_backupm);
	 		$sql_minusm =  'SELECT SUM(minus_amount) AS fund
			 				FROM ec_salary_details
			 				WHERE deleted = 0 AND type="minus" AND reason = "Khac"
			 				AND DATE_FORMAT(voucher_date, "%Y") = "'.$year_val.'"
			 				AND DATE_FORMAT(voucher_date, "%m") = "'.str_pad($m, 2, 0, STR_PAD_LEFT).'"';
			$minusm = $this->bean->db->getOne($sql_minusm); 				
	 		$backup_html .= '<tr>
	 				<td class="text-center">'.$m.'</td>
	 				<td class="text-end">'.format_number($backupm).'</td>
	 				<td class="text-end">'.format_number($minusm).'</td>
	 				<td class="text-end">'.format_number($backupm + $minusm).'</td>
	 			</tr>';
	 		$total_bkm += $backupm;	
	 		$total_mnm += $minusm;
	 	}
	 	$backup_html .= '<tr>
	 				<td class="text-center"><b>Tổng</b></td>
	 				<td class="text-end"><b>'.format_number($total_bkm).'</b></td>
	 				<td class="text-end"><b>'.format_number($total_mnm).'</b></td>
	 				<td class="text-end"><b>'.format_number($total_bkm + $total_mnm).'</b></td>
	 			</tr>';
 		$smarty->assign('BACKUPFUND', $backup_html);
 	}
}