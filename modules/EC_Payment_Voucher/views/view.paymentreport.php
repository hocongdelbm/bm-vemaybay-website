<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
		
class Viewpaymentreport extends SugarView {

	function display() {
		$smartyCont= new Sugar_Smarty();
		$this->populateContent($smartyCont);
		$smartyCont->display('modules/EC_Payment_Voucher/tpls/paymentreport.tpl');
	}
	
	function populateContent($smartyobj){
		global $app_list_strings, $current_user, $sugar_config, $locale;
		
		// $sep = get_number_seperators();
		$sep = my_get_number_separators();
		$smartyobj->assign('GRP_SEPERATOR', $sep[0]);
		$smartyobj->assign('DEC_SEPERATOR', $sep[1]);
		$smartyobj->assign('SIG_DIGITS', $locale->getPrecision());
		$currMonth = date('n');
		if(!isset($_POST['report_term'])) 
        	$reportTerm = 'm' . $currMonth;
        else 
        	$reportTerm = $_POST['report_term'];
		if(empty($_POST['from_date'])) {
			$from_date = date('d-m-Y');
		} else {
			$from_date = $_POST['from_date'];
		}
		$smartyobj->assign('POST_FROM_DATE', $from_date);
		if(empty($_POST['to_date'])) {
			$to_date = date('d-m-Y');
		} else {
			$to_date = $_POST['to_date'];
		}
		$smartyobj->assign('POST_TO_DATE', $to_date);
		$smartyobj->assign('REPORT_TERM_LIST', myGetNewReportTerms($reportTerm));

		$sql_payment = 'SELECT pt.name, SUM(pv.amount) AS total_paid 
						FROM ec_payment_voucher pv LEFT JOIN ec_payment_types pt ON pt.deleted = 0 
						AND pt.id = pv.ec_payment_types_id_c 
						WHERE pv.deleted = 0 AND pv.pv_status = 3
							AND pv.ngaychungtu >= "' . date('Y-m-d', strtotime($from_date)) . '"
							AND pv.ngaychungtu <= "' . date('Y-m-d', strtotime($to_date)) . '"
						GROUP BY 
							pv.ec_payment_types_id_c';

		$res_payment = $this->bean->db->query($sql_payment);
		$i = 1;

		$html = '';
		$total_paid = 0;
		
		while($row_payment=$this->bean->db->fetchByAssoc($res_payment)) {
			$html .= '<tr>
				<td class="text-center">'.$i++.'</td>
				<td>'.$row_payment['name'].'</td>
				<td class="text-end">'.format_number($row_payment['total_paid']).'</td>
			</tr>';
			$total_paid += $row_payment['total_paid'];
		}
		if(empty($html)) {
			$html = '<tr><td colspan="3">Chưa có dữ liệu</td></tr>';
		}
		$smartyobj->assign('PAYMENT_LIST_TBL', $html);
		$smartyobj->assign('TOTAL_PAID', format_number($total_paid));
	}


}
	
?>