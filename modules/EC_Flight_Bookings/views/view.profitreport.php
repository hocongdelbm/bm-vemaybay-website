<?php
require_once("include/Sugar_Smarty.php");

class Viewprofitreport extends SugarView {
	function display() {
		global $current_user;
		if(is_admin($current_user) || $current_user->title == 'QuanLy'){
			$smartyCont= new Sugar_Smarty();
			$this->populateContent($smartyCont);
			$smartyCont->display('modules/EC_Flight_Bookings/tpls/view_profitreport.tpl');
		} else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=".urlencode("Bạn không được quyền truy cập vào mục này"));
			exit();
		}
	}
	
	function populateContent($smartyobj) {
		global $db, $app_list_strings, $app_strings, $current_user;
		//$loaichi_id = "'3361ac47-2254-701a-55d1-508abcb90f50','7a7abc9b-0925-bdb7-d92b-526e3c5c0c14'"; // công nợ phải trả cho nhà cung cấp và chi hoàn vé không tính vào báo cáo lãi lỗ
		$html 		= "";
		$html2 		= "";
		$sql_search 	= "";
		$sql_search_rv = "";
		$sql_search_pv = "";

		// từ ngày
		if(isset($_REQUEST['from_date']) && !empty($_REQUEST['from_date'])){
			$sql_search .= " AND b.date_ticket_issue >= '".date('Y-m-d', strtotime($_REQUEST['from_date']))."' ";
			$sql_search_pv .= " AND DATE(p.ngayhachtoan) >= '".date('Y-m-d', strtotime($_REQUEST['from_date']))."' ";
			$sql_search_rv .= " AND DATE(p.ngayhachtoan) >= '".date('Y-m-d', strtotime($_REQUEST['from_date']))."' ";
			$from_date_value = $_REQUEST['from_date'];
		} else {
			$sql_search .= " AND b.date_ticket_issue >= '".date('Y-m-01')."' ";
			$sql_search_pv .= " AND DATE(p.ngayhachtoan) >= '".date('Y-m-01')."' ";
			$sql_search_rv .= " AND DATE(p.ngayhachtoan) >= '".date('Y-m-01')."' ";
			$from_date_value = date('01-m-Y');
		}
		// đến ngày
		if(isset($_REQUEST['to_date']) && !empty($_REQUEST['to_date'])){
			$sql_search .= " AND b.date_ticket_issue <= '".date('Y-m-d', strtotime($_REQUEST['to_date']))."' ";
			$sql_search_pv .= " AND DATE(p.ngayhachtoan) <= '".date('Y-m-d', strtotime($_REQUEST['to_date']))."' ";
			$sql_search_rv .= " AND DATE(p.ngayhachtoan) <= '".date('Y-m-d', strtotime($_REQUEST['to_date']))."' ";
			$to_date_value = $_REQUEST['to_date'];
		} else {
			$sql_search .= " AND b.date_ticket_issue <= '".date('Y-m-d')."' ";
			$sql_search_pv .= " AND DATE(p.ngayhachtoan) <= '".date('Y-m-d')."' ";
			$sql_search_rv .= " AND DATE(p.ngayhachtoan) <= '".date('Y-m-d')."' ";
			$to_date_value = date('d-m-Y');
		}

		switch (ceil(date('n') / 3)) {
			case 1:
				$quater_fromdate = '01-01-' . date('Y');
				$quater_todate = '31-03-' . date('Y');
				break;
			case 2:
				$quater_fromdate = '01-04-' . date('Y');
				$quater_todate = '30-06-' . date('Y');
				break;
			case 3:
				$quater_fromdate = '01-07-' . date('Y');
				$quater_todate = '30-09-' . date('Y');
				break;
			case 4:
				$quater_fromdate = '01-10-' . date('Y');
				$quater_todate = '31-12-' . date('Y');
				break;
			default:
				break;
		}
		$arr_date = array(
			'<option fromdate="' . date('01-m-Y') . '" todate="' . date('d-m-Y') . '"></option>',
			'<option fromdate="' . date('d-m-Y') . '" todate="' . date('d-m-Y') . '">Hôm nay</option>',
			'<option fromdate="' . date('d-m-Y', strtotime('-1 day')) . '" todate="' . date('d-m-Y', strtotime('-1 day')) . '">Hôm qua</option>',
			'<option fromdate="' . date('d-m-Y', strtotime('monday this week')) . '" todate="' . date('d-m-Y', strtotime('sunday this week')) . '">Tuần này</option>',
			'<option fromdate="' . date('d-m-Y', strtotime('monday last week')) . '" todate="' . date('d-m-Y', strtotime('sunday last week')) . '">Tuần trước</option>',
			'<option fromdate="' . date('d-m-Y', strtotime('first day of this month')) . '" todate="' . date('d-m-Y', strtotime('last day of this month')) . '">Tháng này</option>',
			'<option fromdate="' . date('d-m-Y', strtotime('first day of last month')) . '" todate="' . date('d-m-Y', strtotime('last day of last month')) . '">Tháng trước</option>',
			'<option fromdate="' . $quater_fromdate . '" todate="' . $quater_todate . '">Quý này</option>',
			'<option fromdate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))) . '" todate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))) . '">Quý trước</option>',
		);
		$smartyobj->assign('DATE_OPTION', implode('', $arr_date));

		$total_cost = 0;
		
		// tính cột số tiền của doanh thu, giá mua và doanh số
		$profit = $this->calculateProfit($sql_search, $sql_search_rv);

		// đk để tính cột tháng trước, % tháng trước, quý này, % quý này
		if((empty($from_date_value) && empty($to_date_value)) || ($from_date_value == date('d-m-Y', strtotime('first day of this month')) && $to_date_value == date('d-m-Y'))) 
			$calculate = true;
		else 
			$calculate = false;

		if($calculate) {
			// tháng trước
			$first_day_prev_month 		 = date('Y-m-d', strtotime('first day of previous month'));
			$last_day_prev_month 		 = date('Y-m-d', strtotime('last day of previous month'));
			$sql_search_prev_month 		 = " AND b.date_ticket_issue >= '".$first_day_prev_month."' ";
			$sql_search_prev_month 		.= " AND b.date_ticket_issue <= '".$last_day_prev_month."' ";
			$sql_search_rv_prev_month 	 = " AND DATE(p.ngayhachtoan) >= '".$first_day_prev_month."' ";
			$sql_search_rv_prev_month 	.= " AND DATE(p.ngayhachtoan) <= '".$last_day_prev_month."' ";

			$profit_prev = $this->calculateProfit($sql_search_prev_month, $sql_search_rv_prev_month);

			$smartyobj->assign('TOTAL_AMOUNT_PREV_MONTH', format_number($profit_prev['tt_amount']));
			$smartyobj->assign('TOTAL_BOUGHT_AMOUNT_PREV_MONTH', format_number($profit_prev['tt_bought_amount']));
			$smartyobj->assign('TOTAL_PROFIT_PREV_MONTH', format_number($profit_prev['tt_profit']));

			//% tháng trước
			$smartyobj->assign('TOTAL_AMOUNT_PREV_MONTH_PERCENT', format_number($profit['tt_amount']/$profit_prev['tt_amount']*100, 2, 2).'%');
			$smartyobj->assign('TOTAL_BOUGHT_AMOUNT_PREV_MONTH_PERCENT', format_number($profit['tt_bought_amount']/$profit_prev['tt_bought_amount']*100, 2, 2).'%');
			$smartyobj->assign('TOTAL_PROFIT_PREV_MONTH_PERCENT', format_number($profit['tt_profit']/$profit_prev['tt_profit']*100, 2, 2).'%');

			// quý trước
			$curr_month = date('n');
			if($curr_month < 4) {
				$first_day_last_quarter 	= date('Y-m-d', strtotime('first day of October last year'));
				$last_day_last_quarter 	= date('Y-m-d', strtotime('last day of December last year'));
				$first_day_curr_quarter 	= date('Y-m-d', strtotime('first day of January this year'));
				$last_day_curr_quarter 	= date('Y-m-d', strtotime('last day of March this year'));
			} else if($curr_month < 6) {
				$first_day_last_quarter 	= date('Y-m-d', strtotime('first day of January this year'));
				$last_day_last_quarter 	= date('Y-m-d', strtotime('last day of March this year'));
				$first_day_curr_quarter 	= date('Y-m-d', strtotime('first day of April this year'));
				$last_day_curr_quarter 	= date('Y-m-d', strtotime('last day of June this year'));
			} else if($curr_month < 9) {
				$first_day_last_quarter 	= date('Y-m-d', strtotime('first day of April last year'));
				$last_day_last_quarter 	= date('Y-m-d', strtotime('last day of June last year'));
				$first_day_curr_quarter 	= date('Y-m-d', strtotime('first day of July this year'));
				$last_day_curr_quarter 	= date('Y-m-d', strtotime('last day of September this year'));
			} else if($curr_month < 12) {
				$first_day_last_quarter 	= date('Y-m-d', strtotime('first day of July last year'));
				$last_day_last_quarter 	= date('Y-m-d', strtotime('last day of September last year'));
				$first_day_curr_quarter 	= date('Y-m-d', strtotime('first day of October this year'));
				$last_day_curr_quarter 	= date('Y-m-d', strtotime('last day of December this year'));
			}

			// quý trước
			$sql_search_last_quarter = " AND b.date_ticket_issue >= '".$first_day_last_quarter."' ";
			$sql_search_last_quarter .= " AND b.date_ticket_issue <= '".$last_day_last_quarter."' ";
			$sql_search_rv_last_quarter = " AND DATE(p.ngayhachtoan) >= '".$first_day_last_quarter."' ";
			$sql_search_rv_last_quarter .= " AND DATE(p.ngayhachtoan) <= '".$last_day_last_quarter."' ";
			$profit_last_quarter = $this->calculateProfit($sql_search_last_quarter, $sql_search_rv_last_quarter);

			// quý này
			$sql_search_curr_quarter = " AND b.date_ticket_issue >= '".$first_day_curr_quarter."' ";
			$sql_search_curr_quarter .= " AND b.date_ticket_issue <= '".$last_day_curr_quarter."' ";
			$sql_search_rv_curr_quarter = " AND DATE(p.ngayhachtoan) >= '".$first_day_curr_quarter."' ";
			$sql_search_rv_curr_quarter .= " AND DATE(p.ngayhachtoan) <= '".$last_day_curr_quarter."' ";
			$profit_curr_quarter = $this->calculateProfit($sql_search_curr_quarter, $sql_search_rv_curr_quarter);

			$smartyobj->assign('TOTAL_AMOUNT_CURR_QUARTER', format_number($profit_curr_quarter['tt_amount']));
			$smartyobj->assign('TOTAL_BOUGHT_AMOUNT_CURR_QUARTER', format_number($profit_curr_quarter['tt_bought_amount']));
			$smartyobj->assign('TOTAL_PROFIT_CURR_QUARTER', format_number($profit_curr_quarter['tt_profit']));

			// % quý này
			$smartyobj->assign('TOTAL_AMOUNT_CURR_QUARTER_PERCENT', format_number($profit_curr_quarter['tt_amount']/$profit_last_quarter['tt_amount']*100, 2, 2).'%');
			$smartyobj->assign('TOTAL_BOUGHT_AMOUNT_CURR_QUARTER_PERCENT', format_number($profit_curr_quarter['tt_bought_amount']/$profit_last_quarter['tt_bought_amount']*100, 2, 2).'%');
			$smartyobj->assign('TOTAL_PROFIT_CURR_QUARTER_PERCENT', format_number($profit_curr_quarter['tt_profit']/$profit_last_quarter['tt_profit']*100, 2, 2).'%');
		}

		// tính các loại chi phí phát sinh (trừ công nợ phải trả)
		$other_amt_curr = $this->getOTherAmount($sql_search_pv);
		if($calculate) {
			// tháng trước
			$other_amt_prev_month 	= $this->getOTherAmount($sql_search_rv_prev_month);
			$other_amt_last_quarter 	= $this->getOTherAmount($sql_search_rv_last_quarter);
			$other_amt_curr_quarter 	= $this->getOTherAmount($sql_search_rv_curr_quarter);
		}

		$i = 4;
		$k = 0;
		$total_cost_last_quarter = 0;
		$total_cost_curr_quarter = 0;
		$total_cost_prev_month 	= 0;
		
		foreach($other_amt_curr as $key => $other_amt_curr) {
			if(empty($other_amt_curr['is_other_amount'])) {
				$html .= '<tr>
					<td align="center">'.$i.'</td>
					<td align="left"><a module="EC_Payment_Voucher" uid="'.$key.'" class="cursor-pointer view_detail" title="Xem chi tiết">'.$other_amt_curr['name'].'</a></td>
					<td align="right">'.format_number($other_amt_curr['amount']).'</td>
					<td align="right">'.(!empty($calculate)?format_number($other_amt_prev_month[$key]['amount']):'').'</td>
					<td align="right">'.(!empty($calculate)?format_number($other_amt_curr['amount']/$other_amt_prev_month[$key]['amount']*100, 2, 2).'%':'').'</td>
					<td align="right">'.(!empty($calculate)?format_number($other_amt_curr_quarter[$key]['amount']):'').'</td>
					<td align="right">'.(!empty($calculate)?format_number($other_amt_curr_quarter[$key]['amount']/$other_amt_last_quarter[$key]['amount']*100, 2, 2).'%':'').'</td>
				</tr>';
				$total_cost += $other_amt_curr['amount'];
				$total_cost_prev_month += $other_amt_prev_month[$key]['amount'];
				$total_cost_curr_quarter += $other_amt_curr_quarter[$key]['amount'];
				$total_cost_last_quarter += $other_amt_last_quarter[$key]['amount'];
			} else {
				$html2 .= '<tr>
					<td align="center">'.($i+1).'</td>
					<td align="left"><a module="EC_Payment_Voucher" uid="'.$key.'" class="cursor-pointer view_detail" title="Xem chi tiết">'.$other_amt_curr['name'].'</a></td>
					<td align="right">'.format_number($other_amt_curr['amount']).'</td>
					<td align="right">'.(!empty($calculate)?format_number($other_amt_prev_month[$key]['amount']):'').'</td>
					<td align="right">'.(!empty($calculate)?format_number($other_amt_curr['amount']/$other_amt_prev_month[$key]['amount']*100, 2, 2).'%':'').'</td>
					<td align="right">'.(!empty($calculate)?format_number($other_amt_curr_quarter[$key]['amount']):'').'</td>
					<td align="right">'.(!empty($calculate)?format_number($other_amt_curr_quarter[$key]['amount']/$other_amt_last_quarter[$key]['amount']*100, 2, 2).'%':'').'</td>
				</tr>';
				$k++;
			}

			$i++;
		}
		
		$smartyobj->assign('TOTAL_AMOUNT', format_number($profit['tt_amount']));
		$smartyobj->assign('TOTAL_BOUGHT_AMOUNT', format_number($profit['tt_bought_amount']));
		$smartyobj->assign('TOTAL_PROFIT', format_number($profit['tt_profit']));
		
		$smartyobj->assign('LAST_ROW_NUM', $i - $k);
		$smartyobj->assign('TOTAL_PROFIT_FINAL', format_number($profit['tt_profit']-$total_cost));
		$smartyobj->assign('TOTAL_PROFIT_FINAL_PREV_MONTH', (!empty($calculate)?format_number($profit_prev['tt_profit']-$total_cost_prev_month):''));
		$smartyobj->assign('TOTAL_PROFIT_FINAL_PREV_MONTH_PERCENT', (!empty($calculate)?format_number(($profit['tt_profit']-$total_cost)/($profit_prev['tt_profit']-$total_cost_prev_month)*100, 2, 2).'%':''));
		$smartyobj->assign('TOTAL_PROFIT_FINAL_CURR_QUARTER', (!empty($calculate)?format_number($profit_curr_quarter['tt_profit']-$total_cost_curr_quarter):''));
		$smartyobj->assign('TOTAL_PROFIT_FINAL_CURR_QUARTER_PERCENT', (!empty($calculate)?format_number(($profit_curr_quarter['tt_profit']-$total_cost_curr_quarter)/($profit_last_quarter['tt_profit']-$total_cost_last_quarter)*100, 2, 2).'%':''));

		// những khoản chi không trừ vào lợi nhuận
		$smartyobj->assign('OTHER_AMT', $html2);
		
		$smartyobj->assign('DATA', $html);
		$smartyobj->assign('FROM_DATE_VALUE', $from_date_value);
		$smartyobj->assign('TO_DATE_VALUE', $to_date_value);
    }

    // Tính doanh thu, giá mua và doanh số
    function calculateProfit($sql_search, $sql_search_rv) {
    	global $db;

	$total_amount = 0;
	$total_bought_amount = 0;
	$total_profit = 0;
	
    	$sql = "SELECT SUM(total_amount) AS total_amount, SUM(total_bought_price) AS total_bought_amount 
			FROM (
				SELECT b.id,
					IFNULL(b.total_amount,0) AS total_amount,
					(
						SUM(IFNULL(d.total_bought_price,0))
						+
						IFNULL((
							SELECT 
								IF(b.flight_type='0'
									,SUM(IF(p.luggage_price>0, IFNULL(p.luggage_purchase,0), 0) + IF(p.luggage_price_inbound>0, IFNULL(p.luggage_purchase_inbound,0), 0))
									,SUM(IF(p.luggage_price>0, IFNULL(p.luggage_purchase,0), 0))
								)
							FROM ec_booking_passengers p
							WHERE p.booking_id = b.id AND p.deleted = 0 AND p.add_type IS NULL)
						, 0)
					) AS total_bought_price
				FROM ec_booking_details d 
					LEFT JOIN ec_flight_bookings b ON d.booking_id=b.id AND b.deleted=0 
				WHERE b.booking_status IN ('7','8') ".$sql_search." AND d.deleted=0 
				GROUP BY b.id

				UNION

				SELECT p.id,
					SUM(IFNULL(p.amount,0)) AS total_amount,
					SUM(IFNULL(p.bought_amount,0) + IFNULL(p.bought_amount2,0) + IFNULL(p.bought_amount3,0)) AS total_bought_price
				FROM ec_receipt_voucher p
				WHERE p.rv_status IN (1, 2) AND p.deleted = 0 
				AND p.loai_thu IN ('4','5')".$sql_search_rv. "
				AND IF(p.loai_thu = 10, IF(p.bought_amount IS NULL OR p.bought_amount = 0, 0, 1), 1) = 1
				GROUP BY p.id
				UNION
				SELECT p.id
					,SUM(IFNULL(p.tongtienhang,0)) AS total_amount
					,SUM(IFNULL(p.tongtienkhach,0)) AS total_bought_price
				FROM ec_hoanve p 
				WHERE p.tinhtrang='1' ".$sql_search_rv." AND  p.deleted = 0 
				GROUP BY p.id
			) AS t";

		$res = $db->query($sql);
		$row = $db->fetchByAssoc($res);
		if(!empty($row)){
			$total_amount += $row['total_amount'];
			$total_bought_amount += $row['total_bought_amount'];
			$total_profit += $total_amount - $total_bought_amount;
		}

		return array('tt_amount' => $total_amount, 'tt_bought_amount' => $total_bought_amount, 'tt_profit' => $total_profit);
    }

    // Tính các loại chi phí phát sinh (trừ công nợ phải trả)
    // bao gồm các chi phí và các khoản không trừ vào lợi nhuận
    // các khoản không trừ vào lợi nhuận, nằm phía dưới dòng lợi nhuận
    function getOTherAmount($sql_search_pv) {
    	global $db;

    	// Tiền hãng hoàn
	$sql_hanghoan = 'SELECT SUM(IFNULL(tongtienhang, 0)) AS tien_hang_hoan  
					FROM ec_hoanve WHERE tinhtrang = 1 AND deleted = 0 
					'.str_replace('p.', '', $sql_search_pv);
	$tien_hang_hoan = $db->getOne($sql_hanghoan);

    	// Chi phí phải trả
		$sqlpv = "SELECT t.id, t.name, SUM(p.amount) AS amount, t.is_other_amount
				FROM ec_payment_voucher p 
				LEFT JOIN ec_payment_types t ON p.ec_payment_types_id_c = t.id AND t.deleted=0
				WHERE p.pv_status='3' AND p.is_margin = 0 AND t.is_report = 1 ".$sql_search_pv." AND p.deleted = 0
				GROUP BY p.ec_payment_types_id_c 
				ORDER BY t.is_other_amount";

		$respv = $db->query($sqlpv);
		$i 	  = 0;
		while($rowpv = $db->fetchByAssoc($respv)){
			$other_amt[$rowpv['id']]['name'] = $rowpv['name'];
			$other_amt[$rowpv['id']]['amount'] = $rowpv['amount'];
			$other_amt[$rowpv['id']]['is_other_amount'] = $rowpv['is_other_amount'];

			if($rowpv['name'] == 'Tiền hoàn vé') {
				$other_amt[0]['name'] = 'Tiền hãng hoàn';
				$other_amt[0]['amount'] = $tien_hang_hoan;
				$other_amt[0]['is_other_amount'] = 1;
			}
			$i++;
		}

		return $other_amt;
	}
}
