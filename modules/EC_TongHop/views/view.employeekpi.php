<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
date_default_timezone_set("Asia/Ho_Chi_Minh");

class Viewemployeekpi extends SugarView {
	function display() {
		if (ACLController::checkAccess('EC_Flight_Bookings', 'list', true)) {
			$smartyCont = new Sugar_Smarty();
			$this->populateContent($smartyCont);
			$smartyCont->display('modules/EC_TongHop/tpls/view_employeekpi.tpl');
		} else {
			header("Location: index.php?module=EC_TongHop&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
			exit();
		}
	}

	function populateContent($smartyobj) {
		global $db, $current_user, $app_list_strings;

		$sql_search = "";
		$sql_search2 = "";

		if (!isset($_POST['report_type'])) $_POST['report_type'] = 'owner';

		// từ ngày
		if (isset($_POST['from_date']) && !empty($_POST['from_date'])) {
			$sql_search .= " AND b.date_ticket_issue >= '" . date('Y-m-d', strtotime($_POST['from_date'])) . "' ";
			$sql_search2 .= " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) >= '" . date('Y-m-d', strtotime($_POST['from_date'])) . "' ";
			$from_date_value = $_POST['from_date'];
		} else {
			$sql_search .= " AND b.date_ticket_issue >= '" . date('Y-m-d') . "' ";
			$sql_search2 .= " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) >= '" . date('Y-m-d') . "' ";
			$from_date_value = date('d-m-Y');
		}

		// đến ngày
		if (isset($_POST['to_date']) && !empty($_POST['to_date'])) {
			$sql_search .= " AND b.date_ticket_issue <= '" . date('Y-m-d', strtotime($_POST['to_date'])) . "' ";
			$sql_search2 .= " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($_POST['to_date'])) . "' ";
			$to_date_value = $_POST['to_date'];
		} else {
			$sql_search .= " AND b.date_ticket_issue <= '" . date('Y-m-d') . "' ";
			$sql_search2 .= " AND DATE(DATE_ADD(w.date_entered, INTERVAL 7 HOUR)) <= '" . date('Y-m-d') . "' ";
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
			'<option value="" fromdate="" todate="">---Trống---</option>',
			'<option value="this_month" fromdate="' . date('d-m-Y', strtotime('first day of this month')) . '" todate="' . date('d-m-Y', strtotime('last day of this month')) . '">Tháng này</option>',
			'<option value="previous_month" fromdate="' . date('d-m-Y', strtotime('first day of last month')) . '" todate="' . date('d-m-Y', strtotime('last day of last month')) . '">Tháng trước</option>',
			'<option value="quater_this_month" fromdate="' . $quater_fromdate . '" todate="' . $quater_todate . '">Quý này</option>',
			'<option value="quater_previous_month" fromdate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_fromdate))) . '" todate="' . date('d-m-Y', strtotime('-3 months', strtotime($quater_todate))) . '">Quý trước</option>',
			'<option value="this_year" fromdate="' . date('01-01-Y') . '" todate="' . date('31-12-Y') . '">Năm nay</option>',
			'<option value="previous_year" fromdate="' . date('01-01-Y', strtotime('-1 year')) . '" todate="' . date('31-12-Y', strtotime('-1 year')) . '">Năm trước</option>',
		);
		$smartyobj->assign('DATE_OPTION', implode('', $arr_date));

		// OTPION DATE - RADIO
		$smartyobj->assign('YESTERDAY_FROMDATE', date('d-m-Y', strtotime('-1 day')));
		$smartyobj->assign('YESTERDAY_TODATE', date('d-m-Y', strtotime('-1 day')));
		$smartyobj->assign('DAYBEFORE_FROMDATE', date('d-m-Y', strtotime('-2 day')));
		$smartyobj->assign('DAYBEFORE_TODATE', date('d-m-Y', strtotime('-2 day')));
		$smartyobj->assign('CURRENT_WEEK_FROMDATE', date('d-m-Y', strtotime('monday this week')));
		$smartyobj->assign('CURRENT_WEEK_TODATE', date('d-m-Y', strtotime('sunday this week')));
		$smartyobj->assign('PREVIOUS_WEEK_FROMDATE', date('d-m-Y', strtotime('monday previous week')));
		$smartyobj->assign('PREVIOUS_WEEK_TODATE', date('d-m-Y', strtotime('sunday previous week')));
		$smartyobj->assign('REPORT_TYPE_OPTION', get_select_options_with_id(array('owner' => 'Của tôi', 'all' => 'Tất cả'), $_POST['report_type']));

		// Phân quyền dữ liệu
		if (!is_admin($current_user) && $current_user->title != 'QuanLy' && (!isset($_POST['report_type']) || $_POST['report_type'] != 'all')) {
			$sql_search .= " AND b.assigned_user_id='" . $current_user->id . "' ";
			$sql_search2 .= " AND w.assigned_user_id='" . $current_user->id . "' ";
		}

		// Kiểm tra xem trong khoảng thời gian 3 tháng
		$date_diff = (abs(strtotime($to_date_value) - strtotime($from_date_value)) / 60 / 60 / 24) + 1;
		if (!is_admin($current_user) && $date_diff > 31) {
			echo 'Vui lòng chọn trong khoảng thời gian 31 ngày';
			exit;
		}

		if (
			is_admin($current_user)
			|| ($current_user->title == 'QuanLy'
			|| $_POST['report_type'] == 'all')
		) {
			/**
			 * completed: số lượng booking hoàn tất
			 * paid: Đã thanh toán / đã thu
			 * recheck: Recheck booking
			 * invoice_issued: Xuất hóa đơn đầu ra * 3
			 * ticket_delivery: Giao vé / giao thực phẩm
			 * Recall: Recall cuộc gọi / Remind (cuộc gọi hoàn tất, có thoại và mô tả)
			 * check_debt: Kiểm tra, đối chiếu công nợ
			 * support: Hỗ trợ khách hàng (Delay, zalo)
			 * create_repaid: Tạo phiếu hoàn vé
			 * create_payment: Lập phiếu chi
			 * create_receipt: Lập phiếu thu (Đổi giờ bay, hành trình, tên khách)
			 * create_transfer : Lập phiếu điều chuyển nội bộ
			 * invoice_input_issued: Xuất hóa đơn đầu vào
			 * 
			 */

			// main query
			$sql = "SELECT w.assigned_user_id
						,u.user_name
						,CONCAT(IFNULL(u.last_name,''),IF(u.first_name IS NOT NULL,' ',''),IFNULL(u.first_name,'')) AS full_name
						,SUM(IFNULL(w.called,0)) AS called
						,SUM(IFNULL(w.completed,0)) AS completed
						,SUM(IFNULL(w.paid,0)) AS paid
						,SUM(IFNULL(w.recheck,0)) AS recheck
						,SUM(IFNULL(w.support,0)) AS support
						,SUM(IFNULL(w.invoice_issued,0) * 3) AS invoice_issued
						,SUM(IFNULL(w.ticket_delivery,0)) AS ticket_delivery
						,SUM(IFNULL(w.recall,0)) AS recall
						,SUM(IFNULL(w.remind,0)) AS remind
						,SUM(IFNULL(w.check_debt,0)) AS check_debt
						,SUM(IFNULL(w.create_repaid,0)) AS create_repaid
						,SUM(IFNULL(w.process_repaid,0)) AS process_repaid
						,SUM(IFNULL(w.create_payment,0)) AS create_payment
						,SUM(IFNULL(w.create_receipt,0)) AS create_receipt
						,SUM(IFNULL(w.create_transfer,0)) AS create_transfer
						,SUM(IFNULL(w.invoice_input_issued,0)) AS invoice_input_issued
						-- ,SUM(IFNULL(w.manner, 0)) AS manner
						-- ,SUM(IFNULL(w.effected, 0)) AS effected
						-- ,SUM(IFNULL(w.awareness, 0)) AS awareness
						-- ,SUM(IFNULL(w.minus, 0)) AS minus
						,SUM(  
						  	IFNULL(w.called,0) 
							+ IFNULL(w.completed,0) 
							+ IFNULL(w.paid,0) 
							+ IFNULL(w.recheck,0) 
							+ IFNULL(w.support,0) 
							+ (IFNULL(w.invoice_issued,0) * 3) 
							+ IFNULL(w.ticket_delivery,0) 
							+ IFNULL(w.recall,0)  
							+ IFNULL(w.remind,0)  
							+ IFNULL(w.check_debt,0) 
							+ IFNULL(w.create_repaid,0) 
							+ IFNULL(w.process_repaid,0) 
							+ IFNULL(w.create_payment,0) 
							+ IFNULL(w.create_receipt,0) 
							+ IFNULL(w.create_transfer,0)
							+ IFNULL(w.invoice_input_issued,0)
							-- + IFNULL(w.manner,0)
							-- + IFNULL(w.effected,0)
							-- + IFNULL(w.awareness,0)
							-- - IFNULL(w.minus, 0)
						  ) AS total_kpi
					FROM ec_working_process w
					INNER JOIN users u ON w.assigned_user_id = u.id AND u.deleted = 0
					AND u.is_admin = 0 AND u.title <> 'QuanLy' 
					-- AND u.start_working_date IS NOT NULL
					WHERE w.deleted = 0
					" . $sql_search2 . "
					GROUP BY w.assigned_user_id
					ORDER BY total_kpi DESC ";

			if($GLOBALS['current_user']->user_name == 'hungnh'){
				// pr($sql);
			}

			$res = $db->query($sql);
			$i = 0;
			$html = '';

			$ttl_called = 0;
			// $ttl_confirmed = 0;
			$ttl_completed = 0;
			$ttl_paid = 0;
			$ttl_recheck = 0;
			$ttl_inv_in_issued = 0;
			$ttl_inv_issued = 0;
			$ttl_delivery = 0;
			$ttl_recall = 0;
			// $ttl_bonus = 0;
			$ttl_comdebt = 0;
			$ttl_new_repaid = 0;
			$ttl_do_repaid = 0;
			$ttl_payment = 0;
			$ttl_receipt = 0;
			$ttl_transfer = 0;
			$ttl_support = 0;
			$ttl_minus = 0;
			$ttl_final = 0;

			$ttl_manner = $ttl_effected = $ttl_awareness = 0;

			while ($row = $db->fetchByAssoc($res)) {
				// recall && remind
				$recall = ($row['recall'] != 0 || $row['remind'] != 0) ? (int)($row['recall'] + $row['remind']) : '';

				$html .= '<tr>
							<td align="center"><span>' . ($i + 1) . '</span></td>
							<td align="left"><a class="admin-view-detail" user_id="' . $row['assigned_user_id'] . '" full_name="' . $row['full_name'] . '" load_type="total_kpi" load_name="điểm KPI" href="#" title="Xem chi tiết">' . $row['full_name'] . '</a></td>
							<td align="center"><span title="Called">' . ($row['called'] != 0 ? $row['called'] : '') . '</span></td>
							<td align="center"><span title="Completed">' . ($row['completed'] != 0 ? $row['completed'] : '') . '</span></td>
							<td align="center"><span title="Đã thanh toán / Đã thu">' . ($row['paid'] != 0 ? $row['paid'] : '') . '</span></td>
							<td align="center"><span title="Recheck">' . ($row['recheck'] != 0 ? $row['recheck'] : '') . '</span></td>
							<td align="center"><span title="Recall">' . $recall . '</span></td>
							<td align="center"><span title="Xuất hóa đơn đầu vào">' . ($row['invoice_input_issued'] != 0 ? $row['invoice_input_issued'] : '') . '</span></td>
							<td align="center"><span title="Xuất hóa đơn đầu ra">' . ($row['invoice_issued'] != 0 ? $row['invoice_issued'] : '') . '</span></td>
							<td align="center"><span title="Giao vé">' . ($row['ticket_delivery'] != 0 ? $row['ticket_delivery'] : '') . '</span></td>
							<td align="center"><span title="Đối chiếu công nợ">' . ($row['check_debt'] != 0 ? $row['check_debt'] : '') . '</span></td>
							<td align="center"><span title="Lập phiếu hoàn vé">' . ($row['create_repaid'] != 0 ? $row['create_repaid'] : '') . '</span></td>
							<td align="center"><span title="Lập phiếu chi">' . ($row['create_payment'] != 0 ? $row['create_payment'] : '') . '</span></td>
							<td align="center"><span title="Lập phiếu thu">' . ($row['create_receipt'] != 0 ? $row['create_receipt'] : '') . '</span></td>
							<td align="center"><span title="Lập phiếu điều chuyển tiền">' . ($row['create_transfer'] != 0 ? $row['create_transfer'] : '') . '</span></td>
							<td align="center"><span title="Hỗ trợ khác">' . ($row['support'] != 0 ? $row['support'] : '') . '</span></td>
						';
						// if ($from_date_value == $to_date_value) {
						// 	$html .= '<td align="center"><span title="Chuyên môn"><a class="showdt cursor-pointer" employee="' . $row['assigned_user_id'] . '" type="manner" id="manner' . ($i + 1) . '">' . $row['manner'] . '</a></span></td>
						// 		<td align="center"><span title="Hiệu quả"><a class="showdt cursor-pointer" employee="' . $row['assigned_user_id'] . '" type="effected" id="effected' . ($i + 1) . '">' . ($row['effected'] + $row['ticket_delivery']) . '</a></span></td>
						// 		<td align="center"><span title="Ý thức"><a class="showdt cursor-pointer" employee="' . $row['assigned_user_id'] . '" type="awareness" id="awareness' . ($i + 1) . '">' . $row['awareness'] . '</a></span></td>';
						// 	$html .= '<td align="center"><span title="Bị trừ"><a class="showdt cursor-pointer" employee="' . $row['assigned_user_id'] . '" type="minus" id="minus' . ($i + 1) . '">' . format_number($row['minus']) . '</a></span></td>';
						// } else {
						// 	$html .= '<td align="center"><span>' . $row['manner'] . '</span></td><td align="center"><span>' . $row['effected'] . '</span></td><td align="center"><span>' . $row['awareness'] . '</span></td>';
						// 	$html .= '<td align="center"><span>' . format_number($row['minus']) . '</span></td>';
						// }

						$html .= '<td align="center"><span title="Tổng cộng">' . ($row['total_kpi'] != 0 ? $row['total_kpi'] : '') . '</span></td></tr>';

				$i++;
				$ttl_called += (int)$row['called'];
				$ttl_completed += (int)$row['completed'];
				$ttl_paid += (int)$row['paid'];
				$ttl_recheck += (int)$row['recheck'];
				$ttl_inv_in_issued += (int)$row['invoice_input_issued'];
				$ttl_inv_issued += (int)$row['invoice_issued'];
				$ttl_delivery += (int)$row['ticket_delivery'];
				$ttl_recall += (int)($row['recall'] + $row['remind']);
				// $ttl_bonus += $row['bonus'];
				$ttl_comdebt += (int)$row['check_debt'];
				$ttl_new_repaid += (int)$row['create_repaid'];
				$ttl_do_repaid += (int)$row['process_repaid'];
				$ttl_payment += (int)$row['create_payment'];
				$ttl_receipt += (int)$row['create_receipt'];
				$ttl_transfer += (int)$row['create_transfer'];
				$ttl_support += (int)$row['support'];
				$ttl_final += (int)$row['total_kpi'];

				// $ttl_manner += $row['manner'];
				// $ttl_effected += $row['effected'];
				// $ttl_awareness += $row['awareness'];
				// $ttl_minus += $row['minus'];
			}

			$smartyobj->assign('ADMIN_DATA', $html);
			$smartyobj->assign('TTL_CALLED', $ttl_called);
			// $smartyobj->assign('TTL_CONFIRMED', $ttl_confirmed);
			$smartyobj->assign('TTL_COMPLETED', $ttl_completed);
			$smartyobj->assign('TTL_PAID', $ttl_paid);
			$smartyobj->assign('TTL_RECHECK', $ttl_recheck);
			$smartyobj->assign('TTL_INV_IN_ISSUED', $ttl_inv_in_issued);
			$smartyobj->assign('TTL_INV_ISSUED', $ttl_inv_issued);
			$smartyobj->assign('TTL_DELIVERY', $ttl_delivery);
			$smartyobj->assign('TTL_RECALL', $ttl_recall);
			// $smartyobj->assign('TTL_BONUS', $ttl_bonus);
			$smartyobj->assign('TTL_COMDEBT', $ttl_comdebt);
			$smartyobj->assign('TTL_NEW_REPAID', $ttl_new_repaid);
			$smartyobj->assign('TTL_DO_REPAID', $ttl_do_repaid);
			$smartyobj->assign('TTL_PAYMENT', $ttl_payment);
			$smartyobj->assign('TTL_RECEIPT', $ttl_receipt);
			$smartyobj->assign('TTL_TRANSFER', $ttl_transfer);
			$smartyobj->assign('TTL_SUPPORT', $ttl_support);
			$smartyobj->assign('TTL_FINAL', $ttl_final);
		} 
		else if ($_POST['report_type'] == 'owner') {
			// main query		
			$sql = "SELECT SUM(IFNULL(t.booking_count,0)) AS booking_count
						  ,SUM(IFNULL(t.ticket_count,0)) AS ticket_count 
						  ,SUM(IFNULL(t.total_bonus,0)) AS total_bonus 
						  ,SUM(IFNULL(t.total_kpi,0)) AS total_kpi 
					FROM (
						SELECT DISTINCT COUNT(w.parent_id) AS booking_count
							  ,0 AS ticket_count
							  ,0 AS total_bonus
							  ,0 AS total_kpi
						FROM ec_working_process w
						WHERE w.deleted=0
						AND w.parent_type='EC_Flight_Bookings' " . $sql_search2 . "
						
						UNION
						SELECT 0 AS booking_count
							  ,SUM(IFNULL(d.quantity,0)) AS ticket_count
							  ,0 AS total_bonus
							  ,0 AS total_kpi
						FROM ec_booking_details d
						LEFT JOIN ec_flight_bookings b ON d.booking_id=b.id AND b.deleted=0
						WHERE d.deleted=0
						AND b.booking_status='8' " . $sql_search . "
						
						UNION
						SELECT 0 AS booking_count
							  ,0 AS ticket_count
							  ,SUM(IFNULL(w.bonus,0)) AS total_bonus
							  ,0 AS total_kpi
						FROM ec_working_process w
						WHERE w.deleted=0
						AND w.parent_type='EC_Flight_Bookings' " . $sql_search2 . "
						
						UNION
						SELECT 0 AS booking_count
							  ,0 AS ticket_count
							  ,0 AS total_bonus
							  ,SUM(
								  IFNULL(w.called,0) 
								+ IFNULL(w.confirmed,0) 
								+ IFNULL(w.completed,0) 
								+ IFNULL(w.paid,0) 
								+ IFNULL(w.recheck,0) 
								+ (IFNULL(w.invoice_issued,0) * 3) 
								+ IFNULL(w.ticket_delivery,0) 
								+ IFNULL(w.recall,0) 
								+ IFNULL(w.remind,0) 
								+ IFNULL(w.bonus,0) 
								+ IFNULL(w.check_debt,0) 
								+ IFNULL(w.create_repaid,0) 
								+ IFNULL(w.process_repaid,0) 
								+ IFNULL(w.create_payment,0) 
								+ IFNULL(w.create_transfer,0)
								+ IFNULL(w.invoice_input_issued,0)
							  ) AS total_kpi
						FROM ec_working_process w
						WHERE w.deleted=0 " . $sql_search2 . "
					) AS t";

			$res = $db->query($sql);
			$row = $db->fetchByAssoc($res);
			$ticket_target = (int)$current_user->ticket_target * $date_diff;
			$percent_achieved = $ticket_target != 0 ? ($row['ticket_count'] * 100 / $ticket_target) : 0;

			$smartyobj->assign('BOOKING_COUNT', format_number($row['booking_count']));
			$smartyobj->assign('TICKET_COUNT', format_number($row['ticket_count']));
			$smartyobj->assign('TICKET_TARGET', format_number($ticket_target));
			$smartyobj->assign('PERCENT_ACHIEVED', format_number($percent_achieved));
			$smartyobj->assign('TOTAL_BONUS', format_number($row['total_bonus']));
			$smartyobj->assign('TOTAL_KPI', format_number($row['total_kpi']));

		}

		$smartyobj->assign('EMPLOYEE_KPI_TYPE_LIST', get_select_options_with_id($app_list_strings['employee_kpi_type_list'], ''));
		$smartyobj->assign('IS_ADMIN', (is_admin($current_user) || $current_user->title == 'QuanLy'));
		$smartyobj->assign('OWNER', ($_POST['report_type'] == 'owner' ? 1 : 0));
		$smartyobj->assign('ALL', ($_POST['report_type'] == 'all' ? 1 : 0));
		$smartyobj->assign('FROM_DATE_VALUE', $from_date_value);
		$smartyobj->assign('TO_DATE_VALUE', $to_date_value);
	}
}
