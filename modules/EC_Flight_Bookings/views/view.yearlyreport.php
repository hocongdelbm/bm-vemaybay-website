<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
		
class Viewyearlyreport extends SugarView  {
	function display() {
		if(is_admin($GLOBALS['current_user'])) {
		    	$smartyCont= new Sugar_Smarty();
			$this->populateContent($smartyCont);
			$smartyCont->display('modules/EC_Flight_Bookings/tpls/view_yearlyreport.tpl');
		} else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=".urlencode("Bạn không được quyền truy cập vào mục này"));
			exit();
		}
	}
	
	function populateContent($smartyobj) {
		global $current_user, $db, $locale;
		//$post_month = isset($_POST['selectMonth']) && !empty($_POST['selectMonth']) ? $_POST['selectMonth'] : '';
		$post_year = (isset($_POST['selectYear']) && !empty($_POST['selectYear'])) ? $_POST['selectYear'] : '';
		
		$report_data = $this->generateReportData($post_year);
		
		echo '<script type="text/javascript">
			const report_data = '.$report_data.';
		</script>';
		
		$sep = my_get_number_separators();
		$smartyobj->assign('GRP_SEPERATOR', $sep[0]);
		$smartyobj->assign('DEC_SEPERATOR', $sep[1]);
		$smartyobj->assign('SIG_DIGITS', $locale->getPrecision());
		//$smartyobj->assign('LIST_OF_MONTH', '<option value=""></option>' . myGetMonthList($post_month));
		$smartyobj->assign('LIST_OF_YEAR', '<option value=""></option>' . myGetYearList(date('Y'), 3, $post_year));
	}

	
	function generateReportData($year = '') {
		global $db;
		
		if(isset($year) && !empty($year)){
			$js = "[['Tháng', 'Doanh thu', 'Chi phí', 'Lợi nhuận'],";
			for($i = 1; $i <= 12; $i++){
				$sql = $this->generateSQLReport($year, $i);
				$res = $db->query($sql);
				$row = $db->fetchByAssoc($res);
				$js .= "['".(int)$i."', ".(int)$row['revenue_amount'].", ".(int)$row['cost_amount'].", ".(int)($row['revenue_amount'] - $row['cost_amount'])."],";
			}

		} else {
			$js 			= "[['Năm', 'Doanh thu', 'Chi phí', 'Lợi nhuận'],";
			$curr_year 	= date('Y');

			for($i = $curr_year - 2; $i <= $curr_year; $i++){
				$sql = $this->generateSQLReport($i);
				$res = $db->query($sql);
				$row = $db->fetchByAssoc($res);
				$js .= "['".(int)$i."', ".(int)$row['revenue_amount'].", ".(int)$row['cost_amount'].", ".(int)($row['revenue_amount'] - $row['cost_amount'])."],";
			}
		}
		
		$new_js = substr($js, 0, -1);
		$new_js .= "]";

		return $new_js;
	} 
	
	function generateSQLReport($year = '', $month = '') {
		$sql_search 	= "";
		$sql_search1 	= "";
		$start_month 	= '01';
		$end_month 	= '12';
		
		if(isset($month) && !empty($month)){
			$start_month = $end_month = $month;
		}
			
		if(isset($year) && !empty($year)){
			$sql_search .= " AND p.ngayhachtoan >= '".date('Y-m-d H:i:s', strtotime("01-".$start_month."-".$year) - 7*60*60)."' AND p.ngayhachtoan <= '".date('Y-m-d 16:59:59', strtotime("31-".$end_month."-".$year))."'";
			$sql_search1 = " AND p.date_ticket_issue >= '".date('Y-m-d', strtotime("01-".$start_month."-".$year))."' AND p.date_ticket_issue <= '".date('Y-m-d', strtotime("31-".$end_month."-".$year))."'";
		}

		// if(isset($month) && !empty($month))	
		// $sql_search .= " AND MONTH(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) = ".$month;
		
		$sql = "SELECT ROUND(SUM(t.revenue_amount),0) AS revenue_amount
					  ,ROUND(SUM(t.cost_amount),0) AS cost_amount
				FROM 
				(
					SELECT p.id
						  ,IFNULL(p.total_amount,0) AS revenue_amount
						  ,(
								SUM(IFNULL(d.total_bought_price,0))
								+
								IFNULL((
									SELECT SUM(pa.luggage_purchase + pa.luggage_purchase_inbound)
									FROM ec_booking_passengers pa
									WHERE pa.deleted=0
									AND pa.booking_id=p.id)
								,0)
						   ) AS cost_amount
					FROM ec_booking_details d 
					LEFT JOIN ec_flight_bookings p ON d.booking_id=p.id AND p.deleted=0 
					WHERE d.deleted=0 
					AND p.booking_status IN ('7','8') ".$sql_search1."
					GROUP BY p.id
					
					UNION
					SELECT p.id
						  ,SUM(IFNULL(p.amount,0)) AS revenue_amount
						  ,SUM(IFNULL(p.bought_amount,0)) AS cost_amount
					FROM ec_receipt_voucher p USE INDEX(idx_rv_loaithu)
					WHERE p.deleted=0 
					AND p.rv_status='1' 
					AND p.loai_thu IN ('4','5')".$sql_search."
					GROUP BY p.id
					
					UNION
					SELECT p.id
						  ,SUM(IFNULL(p.tongtienhang,0)) AS revenue_amount
						  ,SUM(IFNULL(p.tongtienkhach,0)) AS cost_amount
					FROM ec_hoanve p 
					WHERE p.deleted=0 
					AND p.tinhtrang='1' 
					AND p.tongtiendv>0 ".$sql_search."
					GROUP BY p.id
					
					UNION
					SELECT p.id
						  ,0 AS revenue_amount
						  ,SUM(IFNULL(p.amount,0)) AS cost_amount
					FROM ec_payment_voucher p 
					LEFT JOIN ec_payment_types t ON p.ec_payment_types_id_c=t.id AND t.deleted=0
					WHERE p.deleted=0 
					AND p.pv_status='3' 
					AND p.is_margin=0 
					AND t.is_report=1 ".$sql_search."
					GROUP BY p.ec_payment_types_id_c
					
				) AS t ";
				
		// if ($GLOBALS['current_user']->user_name == 'nponline') {
		// 	echo $sql; exit;
		// }
		return $sql;
	}
}
?>