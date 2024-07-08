<?php
require_once("include/Sugar_Smarty.php");

class Viewagentreport extends SugarView
{
	function display()
	{
		if (ACLController::checkAccess('Bugs', 'edit', true)) {
			$smartyCont = new Sugar_Smarty();
			$this->populateContent($smartyCont);
			$smartyCont->display('modules/EC_Flight_Bookings/tpls/view_agent_report.tpl');
		} else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
			exit();
		}
	}

	function populateContent($smartyobj)
	{
		global $app_list_strings;

		if (isset($_POST['from_date']) && !empty($_POST['from_date'])) {
			$post_fdate = $_POST['from_date'];
		 } else {
			$post_fdate = date('01-m-Y');
		 }
		 if (isset($_POST['to_date']) && !empty($_POST['to_date'])) {
			$post_tdate = $_POST['to_date'];
		 } else {
			$post_tdate = date('t-m-Y');
		 }
		$accounting_code = '131'; // phải thu của khách hàng
		 $opening_year = date('Y', strtotime($post_fdate));

		if (isset($_POST['btnViewDetail'])) {
			$html = '';
			$acc_cnt = count($_POST['agent_id']);
			for ($i = 0; $i < $acc_cnt; $i++) {

				$agent_id = $_POST['agent_id'][$i];
				$voucher_arr = $this->getVoucherList($opening_year, $accounting_code, $agent_id, $post_fdate, $post_tdate);

				$html .= '
					<table width="100%" border="0" cellspacing="0" cellpadding="0" id="tbl-wrapper">
						<tr>
							<td colspan="3">
							' . $app_list_strings['company_info_list']['name'] . '<br />
							' . $app_list_strings['company_info_list']['address'] . '<br />
							ĐT: ' . $app_list_strings['company_info_list']['tel'] . ' - Fax: ' . $app_list_strings['company_info_list']['fax'] . '
							</td>
						</tr>
						<tr>
							<td colspan="3" align="center" style="font-weight:bold;">
								<label style="font-size:15pt;">CHI TIẾT CÔNG NỢ PHẢI THU</label><br />
								<label style="font-style:italic;">Từ ngày ' . $post_fdate . ' đến ngày ' . $post_tdate . '</label><br /><br />
							</td>
						</tr>
						<tr>
							<td colspan="3" style="font-weight:bold;">Mã đối tượng: ' . $_POST['agtcode_' . $agent_id] . ' - Tên đối tượng: ' . $_POST['agtname_' . $agent_id] . '&nbsp;&nbsp;&nbsp;<input type="submit" name="btnViewDetail" class="ffw-semibold btn btn-success" value="Xuất excel"></td>
						</tr>
						<tr>
							<td colspan="3" style="font-weight:bold;">Tài khoản: ' . $accounting_code . '</td>
						</tr>
						<tr>
							<td colspan="3">
								<br />
								<table width="100%" border="0" cellspacing="0" cellpadding="0" id="tbl-details" class="table-details__booking table-congnophaithu">
									<thead>
									<tr>
										<th width="10%"><div align="center"><strong>Ngày hạch toán</strong></div></th>
										<th width="15%"><div align="center"><strong>Số chứng từ</strong></div></th>
										<th width="30%"><div align="center"><strong>Diễn giải</strong></div></th>
										<th width="15%"><div align="center"><strong>Số tiền nợ</strong></div></th>
										<th width="15%"><div align="center"><strong>Tiền đã trả</strong></div></th>
										<th width="15%"><div align="center"><strong>Còn lại</strong></div></th>
									</tr>
									<tr>
										<th><div align="center"><strong>A</strong></div></th>
										<th><div align="center"><strong>B</strong></div></th>
										<th><div align="center"><strong>C</strong></div></th>
										<th><div align="center"><strong>1</strong></div></th>
										<th><div align="center"><strong>2</strong></div></th>
										<th><div align="center"><strong>3</strong></div></th>
									</tr>
									</thead>

									' . $voucher_arr['html'] . '
									<tr class="footer-tr">
										<td colspan="3"><strong>Cộng</strong></td>
										<td style="font-weight:bold;" align="right">' . format_number($voucher_arr['total_debt']) . '</td>
										<td style="font-weight:bold;" align="right">' . format_number($voucher_arr['total_pay']) . '</td>
										<td style="font-weight:bold;" align="right">' . format_number($voucher_arr['total_remain']) . '</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td align="center"><br /><label style="font-weight:bold;">Người lập</label><br /><label style="font-style:italic;">(Ký, họ tên)</label></td>
							<td align="center"><br /><label style="font-weight:bold;">Kế toán trưởng</label><br /><label style="font-style:italic;">(Ký, họ tên)</label></td>
							<td align="center"><br /><label style="font-weight:bold;">Giám đốc</label><br /><label style="font-style:italic;">(Ký, họ tên, đóng dấu)</label></td>
						</tr>
					</table>';
				$smartyobj->assign('AGENT_ID', $agent_id);
			} // end for
			$smartyobj->assign('POST_FROM_DATE', $post_fdate);
			$smartyobj->assign('POST_TO_DATE', $post_tdate);
			$smartyobj->assign('AGENT_CODE', $_POST['agtcode_' . $agent_id]);
			$smartyobj->assign('AGENT_NAME', $_POST['agtname_' . $agent_id]);
			$smartyobj->assign('DATA', $html);

			if (isset($_POST['exportexcel'])) {
				ob_clean();
				header("Pragma: cache");
				require_once('modules/EC_Flight_Bookings/views/congnophaithu.xls.php');
				$xls = generateXLSTemplate($html, $post_fdate, $post_tdate);
				$xls = chr(255) . chr(254) . mb_convert_encoding($xls, "UTF-16LE", "UTF-8");
				header("Content-type: application/x-msdownload");
				header("Content-disposition: xls; filename=congnophaithu_" . time() . ".xls; size=" . strlen($xls));
				echo $xls;
				exit();
			}

			return;
		} // end if

		$agt_arr = $this->getAgentList($opening_year, $accounting_code, $post_fdate, $post_tdate);
		$smartyobj->assign('AGENT_LIST', $agt_arr['html']);
		$smartyobj->assign('TOTAL_DEBT', format_number($agt_arr['total_debt']));
		$smartyobj->assign('POST_FROM_DATE', $post_fdate);
		$smartyobj->assign('POST_TO_DATE', $post_tdate);
	}

	function getAgentList($opening_year, $accounting_code, $post_fdate, $post_tdate) {
		global $db, $current_user;

		$sql = "
			SELECT a.ticker_symbol AS agent_code,
				a.id AS agent_id,
				a.name AS agent_name,
				a.billing_address_street AS address,
				a.phone_office AS phone,
				SUM(IFNULL(tmp.debt_amount, 0)) - SUM(IFNULL(tmp.pay_amount, 0)) AS total_debt
			FROM (
				-- OPENING AMOUNT
				SELECT p.id,
					p.parent_id AS agent_id,
					SUM(IFNULL(p.dunodau, 0) - IFNULL(p.ducodau, 0)) AS debt_amount,
					0 AS pay_amount
				FROM ec_chitiettaikhoan" . $opening_year . " p
				WHERE p.sotaikhoan = '" . $accounting_code . "'
					AND p.parent_type = 'Accounts'
					AND p.deleted = 0
				GROUP BY p.parent_id

				-- BOOKING
				UNION
				SELECT p.id,
					p.agent_id,
					SUM(IFNULL(p.total_amount, 0)) AS debt_amount,
					0 AS pay_amount
				FROM ec_flight_bookings p
				WHERE p.booking_status IN ('7', '8')
					AND p.is_agent = 1
					AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					AND p.deleted = 0
				GROUP BY p.id

				-- PAYMENT VOUCHER
				UNION
				SELECT p.id,
					p.supplier_id AS agent_id,
					SUM(IFNULL(p.amount, 0)) AS debt_amount,
					0 AS pay_amount
				FROM ec_payment_voucher p
					LEFT JOIN ec_payment_types pt ON p.ec_payment_types_id_c = pt.id AND pt.deleted = 0
				WHERE p.pv_status = '3'
					AND pt.is_receipt_debt = 1
					AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					AND p.deleted = 0
				GROUP BY p.id

				-- RECEIPT VOUCHER
				UNION
				SELECT p.id,
					p.account_id_c AS agent_id,
					SUM(IF(p.is_debt = 1 AND p.loai_thu IN (4, 5, 14, 21), IFNULL(p.amount_converted, 0), 0)) AS debt_amount,
					SUM(IF(p.rv_status = 1 AND p.is_debt = 0, IFNULL(p.amount_converted, 0), 0)) AS pay_amount
				FROM ec_receipt_voucher p
				WHERE p.rv_status IN (1, 2)
					AND p.account_id_c IS NOT NULL
					AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					AND p.deleted = 0
				GROUP BY p.id

				-- RECEIPT VOUCHER FOR DEBT
				UNION
				SELECT p.id,
					p.account_id_c AS agent_id,
					0 AS debt_amount,
					SUM(IFNULL(p.amount_converted, 0)) AS pay_amount
				FROM ec_receipt_voucher p
				WHERE p.rv_status = 1 AND p.is_debt = 1
					AND p.account_id_c IS NOT NULL
					AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
					AND p.deleted = 0
				GROUP BY p.id
			) AS tmp
				LEFT JOIN accounts a ON tmp.agent_id = a.id AND a.deleted = 0
			WHERE a.is_stop_tracking = 0
				AND a.account_type IS NOT NULL
				AND a.account_type <> 'Supplier'
			GROUP BY tmp.agent_id
			-- HAVING total_debt <> 0
			ORDER BY agent_code ";

		$res = $db->query($sql);
		$total_debt = 0;
		$html = '';
		while ($row = $db->fetchByAssoc($res)) {
			$html .= '<tr>
			<td align="center">
				<input type="checkbox" name="agent_id[]" value="' . $row['agent_id'] . '" />
				<input type="hidden" name="agtcode_' . $row['agent_id'] . '" value="' . $row['agent_code'] . '" />
				<input type="hidden" name="agtname_' . $row['agent_id'] . '" value="' . $row['agent_name'] . '" />
			</td>
			<td align="left">' . $row['agent_code'] . '</td>
			<td align="left">' . $row['agent_name'] . '</td>
			<td align="left" class="hide-mobile">' . $row['address'] . '</td>
			<td align="left" class="hide-mobile">' . $row['phone'] . '</td>
			<td align="right">' . format_number($row['total_debt']) . '</td>
				</tr>';

			//========== Begin close opening amount ==========//
			// if ($GLOBALS['current_user']->user_name == 'nponline' && $post_fdate == '01-01-2019' && $post_tdate == '31-12-2019' && $row['total_debt'] != 0) {
			//     $sql_cus = "SELECT id
			// 				FROM ec_chitiettaikhoan
			// 				WHERE deleted=0
			// 				AND parent_type='Accounts'
			// 				AND parent_id='" . $row['agent_id'] . "' ";
			//     $cttk_id = $db->getOne($sql_cus);
			//     if (empty($cttk_id)) {
			//         $cttk = new EC_ChiTietTaiKhoan();
			//         $cttk->id = '';
			//         $cttk->name = $row['agent_name'];
			//         $cttk->parent_type = 'Accounts';
			//         $cttk->parent_id = $row['agent_id'];
			//         $cttk->sotaikhoan = $accounting_code;
			//         $cttk->dunodau = unformat_number($row['total_debt']);
			//         $cttk->save();
			//     } else {
			//         $update = "UPDATE ec_chitiettaikhoan 
			// 				   SET dunodau=" . unformat_number($row['total_debt']) . "
			// 				   WHERE deleted=0 
			// 				   AND parent_type='Accounts'
			// 				   AND parent_id='" . $row['agent_id'] . "'
			// 				   AND id='" . $cttk_id . "'
			// 				   LIMIT 1";
			//         $db->query($update);
			//     }
			// }
			//========== End close opening amount ==========//

			$total_debt += $row['total_debt'];
		}

		return array('html' => $html, 'total_debt' => $total_debt);
	}

	function getVoucherList($opening_year, $accounting_code, $agent_id, $post_fdate, $post_tdate)
	{
	    global $db, $current_user;
 
	    // Get opening amount
	    $sql = "SELECT '' AS id
					   ,'OPN' AS voucher_name
					   ,'' AS date_entered
					   ,'' AS posted_date
					   ,'' AS debt_amount
					   ,'' AS pay_amount
					   ,(SUM(IFNULL(tmp.debt_amount, 0)) - SUM(IFNULL(tmp.pay_amount, 0))) AS remain_amount
					   ,'' AS parent_type
					   ,'' AS parent_id
					   ,'' AS description
					   ,'' AS order_date
		 FROM (
					 -- OPENING AMOUNT
					 SELECT p.id
						   ,SUM(IFNULL(p.dunodau, 0)) - SUM(IFNULL(p.ducodau, 0)) AS debt_amount
						   ,0 AS pay_amount
					 FROM ec_chitiettaikhoan" . $opening_year . " p
					 WHERE p.deleted = 0
					 AND p.sotaikhoan = '" . $accounting_code . "'
					 AND p.parent_id = '" . $agent_id . "'
					 AND p.parent_type = 'Accounts'
					 GROUP BY p.parent_id
					 -- BOOKING
					 UNION
					 SELECT p.id
						   ,SUM(IFNULL(p.total_amount, 0)) AS debt_amount
						   ,0 AS pay_amount
					 FROM ec_flight_bookings p
					 WHERE p.deleted = 0
					 AND p.booking_status IN ('7', '8')
					 AND p.is_agent = 1
					 AND p.agent_id = '" . $agent_id . "'
					 AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					 AND p.date_ticket_issue < '" . date('Y-m-d', strtotime($post_fdate)) . "'
					 GROUP BY p.id
 
					 -- PAYMENT VOUCHER
					 UNION
					 SELECT p.id
						   ,SUM(IFNULL(p.amount, 0)) AS debt_amount
						   ,0 AS pay_amount
					 FROM ec_payment_voucher p
					 LEFT JOIN ec_payment_types pt ON p.ec_payment_types_id_c = pt.id AND pt.deleted = 0
					 WHERE p.deleted = 0
					 AND p.pv_status = '3'
					 AND pt.is_receipt_debt = 1
					 AND p.supplier_id = '" . $agent_id . "'
					 AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					 AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') < '" . date('Y-m-d', strtotime($post_fdate)) . "'
					 GROUP BY p.id
 
					 -- RECEIPT VOUCHER
					 UNION
					 SELECT p.id
						   ,SUM(IF(p.is_debt = 1 AND p.loai_thu IN (4, 5, 14, 21), IFNULL(p.amount_converted, 0), 0)) AS debt_amount
						   ,SUM(IF(p.rv_status = 1 AND p.is_debt = 0, IFNULL(p.amount_converted, 0), 0)) AS pay_amount
					 FROM ec_receipt_voucher p
					 WHERE p.deleted = 0
					 AND p.rv_status IN (1, 2)
					 AND p.account_id_c = '" . $agent_id . "'
					 AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					 AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') < '" . date('Y-m-d', strtotime($post_fdate)) . "'
					 GROUP BY p.id
 
					 -- RECEIPT VOUCHER FOR DEBT
					 UNION
					 SELECT p.id
						   ,0 AS debt_amount
						   ,SUM(IFNULL(p.amount_converted, 0)) AS pay_amount
					 FROM ec_receipt_voucher p
					 WHERE p.deleted = 0
					 AND p.rv_status = 1 AND p.is_debt = 1
					 AND p.account_id_c = '" . $agent_id . "'
					 AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
					 AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') < '" . date('Y-m-d', strtotime($post_fdate)) . "'
					 GROUP BY p.id
		 ) AS tmp";
 
	    // Get voucher list
	    $sql .= "
	    -- BOOKING
		   UNION
		 SELECT p.id
			   ,p.name AS voucher_name
			   ,p.date_entered
			   ,p.date_ticket_issue AS posted_date
			   ,IFNULL(p.total_amount, 0) AS debt_amount
			   ,'' AS pay_amount
			   ,'' AS remain_amount
			   ,'EC_Flight_Bookings' AS parent_type
			   ,p.id AS parent_id
			   ,p.description
			   ,p.date_entered AS order_date
		 FROM ec_flight_bookings p
		 WHERE p.deleted = 0
		 AND p.is_agent = 1
		 AND p.booking_status IN ('7', '8')
		 AND p.agent_id = '" . $agent_id . "'
		 AND p.date_ticket_issue >= '" . date('Y-m-d', strtotime($post_fdate)) . "'
		 AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
 
		 -- PAYMENT VOUCHER
		 UNION
		 SELECT p.id
			   ,p.name AS voucher_name
			   ,p.date_entered
			   ,p.ngayhachtoan AS posted_date
			   ,IFNULL(p.amount, 0) AS debt_amount
			   ,'' AS pay_amount
			   ,'' AS remain_amount
			   ,'EC_Payment_Voucher' AS parent_type
			   ,p.id AS parent_id
			   ,p.description
			   ,p.date_entered AS order_date
		 FROM ec_payment_voucher p
		 LEFT JOIN ec_payment_types pt ON p.ec_payment_types_id_c = pt.id AND pt.deleted = 0
		 WHERE p.deleted = 0
		 AND p.pv_status = '3'
		 AND pt.is_receipt_debt = 1
		 AND p.supplier_id = '" . $agent_id . "'
		 AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') >= '" . date('Y-m-d', strtotime($post_fdate)) . "'
		 AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
 
		 -- RECEIPT VOUCHER
		 UNION
		 SELECT p.id
			   ,p.name AS voucher_name
			   ,p.date_entered
			   ,p.ngayhachtoan AS posted_date 
			   ,IF(p.is_debt = 1 AND p.loai_thu IN (4, 5, 14, 21), IFNULL(p.amount_converted, 0), 0) AS debt_amount
			   ,IF(p.rv_status = 1 AND p.is_debt = 0, IFNULL(p.amount_converted, 0), 0) AS pay_amount
			   ,'' AS remain_amount
			   ,'EC_Receipt_Voucher' AS parent_type
			   ,p.id AS parent_id
			   ,p.description
			   ,p.date_entered AS order_date
		 FROM ec_receipt_voucher p
		 WHERE p.deleted = 0
		 AND p.rv_status IN (1, 2)
		 AND p.account_id_c = '" . $agent_id . "'
		 AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') >= '" . date('Y-m-d', strtotime($post_fdate)) . "'
		 AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
		 
		 -- RECEIPT VOUCHER FOR DEBT
		 UNION
		 SELECT p.id
			   ,p.name AS voucher_name
			   ,p.date_entered
			   ,DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') AS posted_date
			   ,0 AS debt_amount
			   ,IFNULL(p.amount_converted, 0) AS pay_amount
			   ,'' AS remain_amount
			   ,'EC_Receipt_Voucher' AS parent_type
			   ,p.id AS parent_id
			   ,p.description
			   ,p.date_entered AS order_date
		 FROM ec_receipt_voucher p
		 WHERE p.deleted = 0
		 AND p.rv_status = 1 AND p.is_debt = 1
		 AND p.account_id_c = '" . $agent_id . "'
		 AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') >= '" . date('Y-m-d', strtotime($post_fdate)) . "'
		 AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
		 ORDER BY posted_date, order_date";
 
		//  if($GLOBALS['current_user']->user_name == 'hungnh'){
		// 	 pr($sql);
		//  }
 
	    $res = $db->query($sql);
	    $i = 0;
	    $html = '';
	    $total_debt = 0;
	    $total_pay = 0;
	    $total_remain = 0;
	    while ($row = $db->fetchByAssoc($res)) {
		   if ($row['voucher_name'] == 'OPN') {
				 $total_remain += $row['remain_amount'];
			  $html .= '<tr>
					 <td>&nbsp;</td>
					 <td>&nbsp;</td>
					 <td style="font-weight:bold;">Số đầu kỳ</td>
					 <td>&nbsp;</td>
					 <td>&nbsp;</td>
					 <td style="font-weight:bold;" align="right">' . format_number($row['remain_amount']) . '</td>
				 </tr>';
		   } else {
				 $total_debt += $row['debt_amount'];
				 $total_pay += $row['pay_amount'];
				 $total_remain += ($row['debt_amount'] - $row['pay_amount']);
			  $html .= '<tr>
				 <td align="center">' . date('d/m/Y', strtotime($row['posted_date'])) . '</td>
				 <td><a href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . $row['parent_id'] . '" target="_blank">' . $row['voucher_name'] . '</a></td>
				 <td>' . $row['description'] . '</td>
				 <td align="right">' . (!empty($row['debt_amount']) ? format_number($row['debt_amount']) : '') . '</td>
				 <td align="right">' . (!empty($row['pay_amount']) ? format_number($row['pay_amount']) : '') . '</td>
				 <td align="right">' . format_number($total_remain) . '</td>
					 </tr>';
		   }
 
		   $i++;
	    }// while
 
	    return array('html' => $html, 'total_debt' => $total_debt, 'total_pay' => $total_pay, 'total_remain' => $total_remain);
	}
}