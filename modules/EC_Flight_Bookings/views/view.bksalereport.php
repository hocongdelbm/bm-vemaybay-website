<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewbksalereport extends SugarView {
	function display() {
		$smartyCont = new Sugar_Smarty();
		$this->populateContent($smartyCont);
		$smartyCont->display('modules/EC_Flight_Bookings/tpls/view_bksalereport.tpl');
	}

	function populateContent($smartyobj) {
		global $current_user;

		// report term
		// check quarter 
		switch (ceil(date('n') / 3)) {
			case 1:
				$cq_from_date = '01-01-' . date('Y');
				$cq_to_date = '31-03-' . date('Y');
				$lq_from_date = '01-01-' . date('Y', strtotime('- 1 year'));
				$lq_to_date = '31-03-' . date('Y', strtotime('- 1 year'));
				break;
			case 2:
				$cq_from_date = '01-04-' . date('Y');
				$cq_to_date = '30-06-' . date('Y');
				$lq_from_date = '01-01-' . date('Y');
				$lq_to_date = '31-03-' . date('Y');
				break;
			case 3:
				$cq_from_date = '01-07-' . date('Y');
				$cq_to_date = '30-09-' . date('Y');
				$lq_from_date = '01-04-' . date('Y');
				$lq_to_date = '30-06-' . date('Y');
				break;
			case 4:
				$cq_from_date = '01-10-' . date('Y');
				$cq_to_date = '31-12-' . date('Y');
				$lq_from_date = '01-07-' . date('Y');
				$lq_to_date = '30-09-' . date('Y');
				break;
			default:
				$cq_from_date = '';
				$cq_to_date = '';
				$lq_from_date = '';
				$lq_to_date = '';
				break;
		}

		$report_term_list = '<option data-fromdate="' . date('d-m-Y') . '" data-todate="' . date('d-m-Y') . '" data-term="' . date('m') . '" data-year="' . date('Y') . '">Hôm nay</option>';
		$report_term_list .= '<option data-fromdate="' . date('d-m-Y', strtotime("-1 day")) . '" data-todate="' . date('d-m-Y', strtotime("-1 day")) . '" data-term="' . date('m', strtotime("-1 day")) . '" data-year="' . date('Y', strtotime("-1 day")) . '">Hôm qua</option>';
		$report_term_list .= '<option data-fromdate="' . date('d-m-Y', strtotime("first day of this month")) . '" data-todate="' . date('d-m-Y', strtotime("last day of this month")) . '" data-term="' . date('m', strtotime("last day of this month")) . '" data-year="' . date('Y', strtotime("last day of this month")) . '">Tháng này</option>';
		$report_term_list .= '<option data-fromdate="' . date('d-m-Y', strtotime("first day of previous month")) . '" data-todate="' . date('d-m-Y', strtotime("last day of previous month")) . '" data-term="' . date('m', strtotime("last day of previous month")) . '" data-year="' . date('Y', strtotime("last day of previous month")) . '">Tháng trước</option>';
		$report_term_list .= '<option data-fromdate="' . date('d-m-Y', strtotime($cq_from_date)) . '" data-todate="' . date('d-m-Y', strtotime($cq_to_date)) . '" data-term="' . date('m', strtotime($cq_from_date)) . '" data-year="' . date('Y', strtotime($cq_from_date)) . '">Quý này</option>';
		$report_term_list .= '<option data-fromdate="' . date('d-m-Y', strtotime($lq_from_date)) . '" data-todate="' . date('d-m-Y', strtotime($lq_to_date)) . '" data-term="' . date('m', strtotime($lq_from_date)) . '" data-year="' . date('Y', strtotime($lq_from_date)) . '">Quý trước</option>';
		
		$smartyobj->assign('REPORT_TERM_LIST', $report_term_list);

		// from date
		if (empty($_REQUEST['from_date']))
			$from_date = date('d-m-Y');
		else {
			$from_date = $_REQUEST['from_date'];
		}
		$smartyobj->assign('FROM_DATE', $from_date);

		// to date
		if (empty($_REQUEST['to_date']))
			$to_date = date('d-m-Y');
		else {
			$to_date = $_REQUEST['to_date'];
		}
		$smartyobj->assign('TO_DATE', $to_date);

		if (!isset($_REQUEST['for'])) {
			if (is_admin($current_user) || $current_user->title == 'QuanLy') {
				$ds = $this->getDSBooking($from_date, $to_date);
			} else {
				$ds = $this->getDSBooking($from_date, $to_date, $current_user->id);
			}
			$detail = 0;
		} else {
			$ds = $this->getDetailDSBooking($from_date, $to_date, $_REQUEST['user']);
			$detail = 1;

			$u = new User;
			$u->retrieve($_REQUEST['user']);
			$smartyobj->assign('EMPOYEE_NAME', $u->last_name . ' ' . $u->first_name);
		}

		$smartyobj->assign('DETAIL', $detail);
		$smartyobj->assign('DOANHSO', $ds);
	}

	// Doanh số booker
	function getDSBooking($from_date, $to_date, $assigned_user_id = '') {
		global $current_user;
		// chỉ xem của 1 nhân viên
		$user_search = '';
		if (!empty($assigned_user_id)) {
			$user_search = ' AND bk.assigned_user_id = "' . $assigned_user_id . '"';
		}

		// lấy mức ds tối thiểu
		$min_rate = 70000000;

		$hoanve = '';

		$html = '
			<thead>
				<th width="5%">STT</th>
				<th width="20%">Họ và tên</th>
				<th width="5%">Booking</th>
				<th width="5%">Vé</th>
				<th width="10%">Doanh số</th>
				<th width="10%">DS cú đêm</th>
				<th width="10%">DS được thưởng</th>
				<th width="10%">Thưởng DS</th>
				<th width="5%">Thứ hạng</th>
				<th>Ghi chú</th>
			</thead><tbody>';

		// doanh số bao gồm doanh số bán vé, đổi ngày bay, hành lý và hoàn vé
		$sql = '
			SELECT 
				t.user_id, u.title,
				CONCAT(u.last_name, " ", IFNULL( u.first_name, "" )) AS full_name,
				SUM(t.total_bk) AS total_bk,
				SUM(t.total_qty) AS ticket_qty,
				(
					SUM(t.doanhso) - SUM(t.luggage_purchase_price)
					- IFNULL((
						SELECT SUM(IFNULL(com_bk.total_amount, 0))
						FROM ec_completed_bookings com_bk
							INNER JOIN ec_flight_bookings bk ON bk.id = com_bk.ec_flight_bookings_id_c AND bk.deleted = 0
						WHERE bk.assigned_user_id = t.user_id
							AND com_bk.completed_bk_type = "SHARE_PROFIT"
							AND DATE_FORMAT(DATE_ADD(com_bk.date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . date('Y-m-d', strtotime($from_date)) . '" 
							AND DATE_FORMAT(DATE_ADD(com_bk.date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . date('Y-m-d', strtotime($to_date)) . '" 
							AND com_bk.deleted = 0
					), 0)
				) AS doanhso,
				s.profit_overnight AS dscudem,
				(
					SUM(t.doanhso) 
					- SUM(t.luggage_purchase_price) 
					- IFNULL(s.profit_overnight, 0)
					- IFNULL((
						SELECT SUM(IFNULL(com_bk.total_amount, 0))
						FROM ec_completed_bookings com_bk
							INNER JOIN ec_flight_bookings bk ON bk.id = com_bk.ec_flight_bookings_id_c AND bk.deleted = 0
						WHERE bk.assigned_user_id = t.user_id
							AND com_bk.completed_bk_type = "SHARE_PROFIT"
							AND DATE_FORMAT(DATE_ADD(com_bk.date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . date('Y-m-d', strtotime($from_date)) . '" 
							AND DATE_FORMAT(DATE_ADD(com_bk.date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . date('Y-m-d', strtotime($to_date)) . '" 
							AND com_bk.deleted = 0
					), 0)
				) AS dsconlai,
				s.sales AS thuongds
			FROM (
				SELECT 	
					bk.assigned_user_id AS user_id,
					bk.id, bk.id AS voucher_id,
					COUNT( bk.id ) / COUNT( dt.id ) AS total_bk,
					SUM( dt.quantity ) AS total_qty,
					SUM( bk.total_amount ) / COUNT( dt.id ) - SUM(IFNULL( dt.total_bought_price, 0 )) - IFNULL((SELECT SUM(amount) FROM ec_payment_voucher WHERE booking_id=bk.id AND pv_status="3" AND ec_payment_types_id_c="3f9f8060-1866-2b2e-8322-52e36b8f58d5" AND deleted=0), 0) AS doanhso,
					(
						SELECT SUM(IF(luggage_price > 0, IFNULL( luggage_purchase, 0 ), 0) + IF(luggage_price_inbound > 0, IFNULL( luggage_purchase_inbound, 0 ), 0)) 
						FROM ec_booking_passengers 
						WHERE booking_id = bk.id AND deleted = 0 AND add_type IS NULL 
					) AS luggage_purchase_price 
				FROM
					ec_flight_bookings bk LEFT JOIN ec_booking_details dt ON dt.booking_id = bk.id AND dt.deleted = 0 
				WHERE bk.booking_status = 8 
					AND bk.date_ticket_issue >= "' . date('Y-m-d', strtotime($from_date)) . '" 
					AND bk.date_ticket_issue <= "' . date('Y-m-d', strtotime($to_date)) . '" 
					' . $user_search . '
					AND bk.deleted = 0 
				GROUP BY bk.assigned_user_id, bk.id 

				-- hoanve
				UNION
				SELECT
					bk.assigned_user_id AS user_id,
					bk.id, hv.id AS voucher_id,
					0 AS total_bk,
					- COUNT( cthv.id ) AS total_qty,
					IF(SUM(IFNULL( cthv.phidichvu, 0 )) > 0, 0, SUM(IFNULL( cthv.phidichvu, 0 ))) AS doanhso,
					0 AS luggage_purchase_price 
				FROM ec_chitiethoanve cthv
					LEFT JOIN ec_hoanve hv ON hv.id = cthv.hoanve_id AND hv.deleted = 0
					LEFT JOIN ec_flight_bookings bk ON bk.id = hv.booking_id AND bk.deleted = 0 
				WHERE hv.ngayhachtoan >= "' . date('Y-m-d', strtotime($from_date)) . '" 
					AND hv.ngayhachtoan <= "' . date('Y-m-d', strtotime($to_date)) . '"
					AND hv.tinhtrang = 1
					' . $user_search . '
					AND cthv.deleted = 0
				GROUP BY hv.id

				-- hoanve > 0
				UNION
				SELECT
					hv.assigned_user_id AS user_id,
					bk.id, hv.id AS voucher_id,
					0 AS total_bk,
					0 AS total_qty,
					SUM(IFNULL( cthv.phidichvu, 0 )) AS doanhso,
					0 AS luggage_purchase_price 
				FROM ec_chitiethoanve cthv
					LEFT JOIN ec_hoanve hv ON hv.id = cthv.hoanve_id AND hv.deleted = 0
					LEFT JOIN ec_flight_bookings bk ON bk.id = hv.booking_id AND bk.deleted = 0 
				WHERE hv.ngayhachtoan >= "' . date('Y-m-d', strtotime($from_date)) . '" 
					AND hv.ngayhachtoan <= "' . date('Y-m-d', strtotime($to_date)) . '"
					AND hv.tinhtrang = 1 
					' . str_replace('bk', 'hv', $user_search) . ' 
					AND cthv.deleted = 0 
				GROUP BY hv.id
				HAVING SUM(IFNULL( cthv.phidichvu, 0 )) > 0

				-- phieu thu hanh ly, doi ngay bay, doi ten
				UNION
				SELECT
					t.assigned_user_id AS user_id,
					bk.id, t.id AS voucher_id,
					0 AS total_bk,
					0 AS total_qty,
					SUM(IFNULL( t.sell_amount, 0 ) + IFNULL( t.sell_amount2, 0 ) + IFNULL( t.sell_amount3, 0 )) 
					- SUM(IFNULL( t.bought_amount, 0 ) + IFNULL( t.bought_amount2, 0 ) + IFNULL( t.bought_amount3, 0 )) AS doanhso,
					0 AS luggage_purchase_price 
				FROM ec_receipt_voucher t
					LEFT JOIN ec_flight_bookings bk ON bk.id = t.booking_id AND bk.deleted = 0 
				WHERE 
					-- DATE_FORMAT(DATE_ADD(t.ngayhachtoan, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . date('Y-m-d', strtotime($from_date)) . '" 
					-- AND DATE_FORMAT(DATE_ADD(t.ngayhachtoan, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . date('Y-m-d', strtotime($to_date)) . '" 
					DATE_FORMAT(t.ngayhachtoan, "%Y-%m-%d") >= "' . date('Y-m-d', strtotime($from_date)) . '" 
					AND DATE_FORMAT(t.ngayhachtoan, "%Y-%m-%d") <= "' . date('Y-m-d', strtotime($to_date)) . '" 
					AND t.rv_status IN ( 1, 2 )
					AND t.loai_thu IN ( 4, 5 ) 
					AND t.deleted = 0
					' . str_replace('bk', 't', $user_search) . '
				GROUP BY t.id

				-- cộng thêm ds được chia
				UNION
				SELECT 
					assigned_user_id,
					ec_flight_bookings_id_c AS id,
					"" AS voucher_id,
					0 AS total_bk,
					0 AS total_qty,
					total_amount AS doanhso,
					0 AS luggage_purchase_price 
				FROM ec_completed_bookings
				WHERE DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . date('Y-m-d', strtotime($from_date)) . '" 
					AND DATE_FORMAT(DATE_ADD(date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . date('Y-m-d', strtotime($to_date)) . '" 
					' . str_replace('bk', '', $user_search) . '
					AND deleted = 0 
				AND completed_bk_type = "SHARE_PROFIT"
			) AS t
			LEFT JOIN users u ON u.id = t.user_id
			LEFT JOIN ec_employee_salary s ON s.assigned_user_id = t.user_id
				AND s.month = ' . date('n', strtotime($from_date)) . ' 
				AND s.year = ' . date('Y', strtotime($from_date)) .  ' AND s.deleted = 0
			GROUP BY t.user_id
			ORDER BY dsconlai DESC';

		// if($current_user->user_name == 'hungnh'){
		// 	pr($sql);
		// }

		$res = $this->bean->db->query($sql);
		$i = 1;
		$total_bk_qty = $total_ticket_qty = $total_doanhso = 0;
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			if (empty($row['user_id'])) {
				$row['full_name'] = '(Trống)';
			}

			// Đánh dấu người không đạt ds tháng
			if ($row['doanhso'] < $min_rate && $min_rate > 0 && strtotime($from_date) < strtotime(date('01-m-Y')) && $from_date == date('01-m-Y', strtotime($from_date)) && $to_date == date('t-m-Y', strtotime($from_date)) && $row['title'] == 'Booker' && empty($assigned_user_id)) {
				$err_class = 'not_expect_sale';
				$note = 'Tháng này bạn không đạt ds';
			} else {
				$err_class = '';
				$note = '';
			}

			$html .= '<tr class="' . $err_class . '">
	 				<td class="text-center">' . $i . '</td>
	 				<td><a href="index.php?module=EC_Flight_Bookings&action=bksalereport&for=showDetail&user=' . $row['user_id'] . '&from_date=' . $from_date . '&to_date=' . $to_date . '" target="_blank">' . $row['full_name'] . '</a></td>
	 				<td class="text-center">' . format_number($row['total_bk']) . '</td>
	 				<td class="text-center">' . format_number($row['ticket_qty']) . '</td>
	 				<td class="text-end">' . format_number($row['doanhso']) . '</td>
					<td class="text-end">' . format_number($row['dscudem']) . '</td>
					<td class="text-end">' . format_number($row['doanhso'] - $row['dscudem']) . '</td>
	 				<td class="text-end">' . format_number($row['thuongds']) . '</td>
	 				<td class="text-center">' . $i . '/$TOTAL_RANK</td>
	 				<td>' . $note . '</td>
	 			</tr>';
			// <input type="text" class="user-note"><input class="save-note-btn" type="button" value="Lưu">
			$i++;

			$total_bk_qty += $row['total_bk'];
			$total_ticket_qty += $row['ticket_qty'];
			$total_doanhso += $row['doanhso'];
		}

		$html .= '<tr class="last-row footer-tr">
	 			<td></td>
	 			<td>Tổng cộng</td>
	 			<td class="text-end">' . format_number($total_bk_qty) . '</td>
	 			<td class="text-end">' . format_number($total_ticket_qty) . '</td>
	 			<td class="text-end">' . format_number($total_doanhso) . '</td>
	 			<td></td>
	 			<td></td>
	 			<td></td>
	 			<td></td>
				<td></td>
	 		</tr></tbody>';

		$html = str_replace('$TOTAL_RANK', $i - 1, $html);

		return $html;
	}

	// Chi tiết doanh số
	function getDetailDSBooking($from_date, $to_date, $assigned_user_id) {
		if (empty($assigned_user_id)) {
			$assigned_user_id_bk = ' AND (bk.assigned_user_id = "' . $assigned_user_id . '" OR bk.assigned_user_id IS NULL)';
			$assigned_user_id_hv = ' AND (hv.assigned_user_id = "' . $assigned_user_id . '" OR hv.assigned_user_id IS NULL)';
			$assigned_user_id_t = ' AND (t.assigned_user_id = "' . $assigned_user_id . '" OR t.assigned_user_id IS NULL)';
			$assigned_user_id_share = ' AND (com_bk.assigned_user_id = "' . $assigned_user_id . '" OR com_bk.assigned_user_id IS NULL)';
		} else {
			$assigned_user_id_bk = ' AND bk.assigned_user_id = "' . $assigned_user_id . '"';
			$assigned_user_id_hv = ' AND hv.assigned_user_id = "' . $assigned_user_id . '"';
			$assigned_user_id_t = ' AND t.assigned_user_id = "' . $assigned_user_id . '"';
			$assigned_user_id_share = ' AND com_bk.assigned_user_id = "' . $assigned_user_id . '"';
		}
		// if(!empty($assigned_user_id)) {
		$html = '<thead>
					<th>STT</th>
					<th>Ngày xuất vé</th>
					<th>Booking</th>
					<th>Số vé</th>
					<th>Số vé hoàn</th>
					<th>DS booking</th>
					<th>Phí DV hoàn vé</th>
					<th>Phí đối tên / đổi ngày bay</th>
					<th>DS tổng</th>
				</thead><tbody>';

		$sql = 'SELECT t.*
		 				FROM (
			 				SELECT 	
								bk.assigned_user_id AS user_id,
								bk.id AS booking_id, bk.name AS booking,
								"" AS voucher_id, "" AS voucher_name, 
								"EC_Flight_Bookings" AS voucher_type,
								DATE_FORMAT(bk.date_ticket_issue, "%d-%m-%Y") AS date_ticket_issue,
								COUNT( bk.id ) / COUNT( dt.id ) AS total_bk,
								SUM( dt.quantity ) AS total_qty,
								0 AS return_qty,
								(
									SUM( bk.total_amount ) / COUNT( dt.id ) 
									- SUM(IFNULL( dt.total_bought_price, 0 )) 
									- IFNULL((
										SELECT SUM(amount) 
										FROM ec_payment_voucher 
										WHERE booking_id = bk.id AND pv_status = "3" 
											AND ec_payment_types_id_c = "3f9f8060-1866-2b2e-8322-52e36b8f58d5"
											AND deleted = 0 
									), 0)
									- IFNULL((
										SELECT SUM(IFNULL(total_amount, 0))
										FROM ec_completed_bookings 
										WHERE bk.id = ec_flight_bookings_id_c
											AND completed_bk_type = "SHARE_PROFIT"
											AND deleted = 0
										GROUP BY ec_flight_bookings_id_c
									), 0)
								)  AS doanhso,
								(
									SELECT
										SUM(IF(luggage_price > 0, IFNULL( luggage_purchase, 0 ), 0) 
											+ IF(luggage_price_inbound > 0, IFNULL( luggage_purchase_inbound, 0 ), 0)) 
									FROM
										ec_booking_passengers 
									WHERE booking_id = bk.id 
										AND add_type IS NULL
										AND deleted = 0 
								) AS luggage_purchase_price,
								0 AS return_service_fee,
								0 AS change_service_fee 
							FROM ec_flight_bookings bk
								LEFT JOIN ec_booking_details dt ON dt.booking_id = bk.id AND dt.deleted = 0 
							WHERE bk.date_ticket_issue >= "' . date('Y-m-d', strtotime($from_date)) . '" 
								AND bk.date_ticket_issue <= "' . date('Y-m-d', strtotime($to_date)) . '" 
								AND bk.booking_status = 8 
								' . $assigned_user_id_bk . '
								AND bk.deleted = 0 
							GROUP BY
								bk.assigned_user_id,
								bk.id 

							-- hoanve doanh so <= 0
							UNION
							SELECT 
								hv_t.user_id,
								hv_t.booking_id, hv_t.booking,
								hv_t.voucher_id, hv_t.voucher_name, 
								"EC_HoanVe" AS voucher_type,
								hv_t.date_ticket_issue,
								SUM(hv_t.total_bk) AS total_bk,
								SUM(hv_t.total_qty) AS total_qty,
								SUM(hv_t.return_qty) AS return_qty,
								SUM(hv_t.doanhso) AS doanhso,
								SUM(hv_t.luggage_purchase_price) AS luggage_purchase_price,
								SUM(hv_t.return_service_fee) AS return_service_fee,
								SUM(hv_t.change_service_fee) AS change_service_fee
							FROM
							( 
								SELECT
									bk.assigned_user_id AS user_id,
									bk.id AS booking_id, bk.name AS booking,
									hv.id AS voucher_id, hv.name AS voucher_name, "EC_HoanVe" AS voucher_type,
									DATE_FORMAT(hv.ngayhachtoan, "%d-%m-%Y") AS date_ticket_issue,
									0 AS total_bk,
									- COUNT( cthv.id ) AS total_qty,
									COUNT( cthv.id ) AS return_qty,
									0 AS doanhso,
									0 AS luggage_purchase_price,
									IF(SUM(IFNULL( cthv.phidichvu, 0 )) > 0, 0, SUM(IFNULL( cthv.phidichvu, 0 ))) AS return_service_fee,
									0 AS change_service_fee 
								FROM ec_chitiethoanve cthv 
									LEFT JOIN ec_hoanve hv ON hv.id = cthv.hoanve_id AND hv.deleted = 0
									LEFT JOIN ec_flight_bookings bk ON bk.id = hv.booking_id AND bk.deleted = 0 
								WHERE hv.ngayhachtoan >= "' . date('Y-m-d', strtotime($from_date)) . '" 
									AND hv.ngayhachtoan <= "' . date('Y-m-d', strtotime($to_date)) . '"
									AND hv.tinhtrang = 1 
									' . $assigned_user_id_bk . '
									AND cthv.deleted = 0 
								GROUP BY hv.id	

								-- hoanve doanh so > 0
								UNION
								SELECT
									hv.assigned_user_id AS user_id,
									bk.id AS booking_id, bk.name AS booking,
									hv.id AS voucher_id, hv.name AS voucher_name, "EC_HoanVe" AS voucher_type,
									DATE_FORMAT(hv.ngayhachtoan, "%d-%m-%Y") AS date_ticket_issue,
									0 AS total_bk,
									0 AS total_qty,
									0 AS return_qty,
									0 AS doanhso,
									0 AS luggage_purchase_price,
									SUM(IFNULL( cthv.phidichvu, 0 )) AS return_service_fee,
									0 AS change_service_fee 
								FROM
									ec_chitiethoanve cthv
									LEFT JOIN ec_hoanve hv ON hv.id = cthv.hoanve_id AND hv.deleted = 0
									LEFT JOIN ec_flight_bookings bk ON bk.id = hv.booking_id AND bk.deleted = 0 
								WHERE hv.ngayhachtoan >= "' . date('Y-m-d', strtotime($from_date)) . '" 
									AND hv.ngayhachtoan <= "' . date('Y-m-d', strtotime($to_date)) . '"
									AND hv.tinhtrang = 1 
									' . $assigned_user_id_hv . '
									AND cthv.deleted = 0 
								GROUP BY hv.id	
								HAVING SUM(IFNULL( cthv.phidichvu, 0 )) > 0
							) AS hv_t
							GROUP BY hv_t.voucher_id, hv_t.user_id

							-- phieu thu hanh ly, doi ngay bay, doi ten
							UNION
							SELECT
								t.assigned_user_id AS user_id,
								bk.id AS booking_id, bk.name AS booking,
								t.id AS voucher_id, t.name AS voucher_name, 
								"EC_Receipt_Voucher" AS voucher_type,
								DATE_FORMAT(t.ngayhachtoan, "%d-%m-%Y") AS date_ticket_issue,
								0 AS total_bk,
								0 AS total_qty,
								0 AS return_qty,
								0 AS doanhso,
								0 AS luggage_purchase_price,
								0 AS return_service_fee,
								SUM(IFNULL( t.sell_amount, 0 ) + IFNULL( t.sell_amount2, 0 ) + IFNULL( t.sell_amount3, 0 )) 
								- SUM(IFNULL( t.bought_amount, 0 ) + IFNULL( t.bought_amount2, 0 ) + IFNULL( t.bought_amount3, 0 )) AS change_service_fee 
							FROM ec_receipt_voucher t
								LEFT JOIN ec_flight_bookings bk ON bk.id = t.booking_id AND bk.deleted = 0 
							WHERE DATE_FORMAT(DATE_ADD(t.ngayhachtoan, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . date('Y-m-d', strtotime($from_date)) . '" 
								AND DATE_FORMAT(DATE_ADD(t.ngayhachtoan, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . date('Y-m-d', strtotime($to_date)) . '" 
								AND t.rv_status = 1 
								AND t.loai_thu IN ( 4, 5 ) 
								' . $assigned_user_id_t . '
								AND t.deleted = 0 
							GROUP BY t.id

							-- doanh so dc share
							UNION
							SELECT 
								com_bk.assigned_user_id AS user_id,
								com_bk.ec_flight_bookings_id_c AS booking_id,
								CONCAT(bk.name, " (Share)") AS booking,
								"" AS voucher_id, "" AS voucher_name,
								"UReceived_Share_Profit" AS voucher_type,
								DATE_FORMAT(DATE_ADD(com_bk.date_entered, INTERVAL 7 HOUR), "%d-%m-%Y") AS date_ticket_issue,
								0 AS total_bk,
								0 AS total_qty,
								0 AS return_qty,
								com_bk.total_amount AS doanhso,
								0 AS luggage_purchase_price,
								0 AS return_service_fee,
								0 AS change_service_fee
							FROM ec_completed_bookings com_bk
								INNER JOIN ec_flight_bookings bk ON bk.id = com_bk.ec_flight_bookings_id_c AND bk.deleted = 0
							WHERE DATE_FORMAT(DATE_ADD(com_bk.date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") >= "' . date('Y-m-d', strtotime($from_date)) . '" 
								AND DATE_FORMAT(DATE_ADD(com_bk.date_entered, INTERVAL 7 HOUR), "%Y-%m-%d") <= "' . date('Y-m-d', strtotime($to_date)) . '" 
								AND com_bk.completed_bk_type = "SHARE_PROFIT"
								' . $assigned_user_id_share . '
								AND com_bk.deleted = 0 
						) AS t
						ORDER BY t.voucher_type, t.date_ticket_issue';

		$res 		= $this->bean->db->query($sql);
		$i 			= 1;
		$total_qty 	= $total_return_qty = $total_ds_booking = 0;
		$total_return_service_fee = $total_change_service_fee = $total_ds = 0;

		while ($row = $this->bean->db->fetchByAssoc($res)) {
			$html .= '<tr>
			 			<td class="text-center fw-semibold">' . $i . '</td>
			 			<td class="text-center">' . $row['date_ticket_issue'] . '</td>
			 			<td class="text-center">
			 				<a target="_blank" href="index.php?module=EC_Flight_Bookings&action=DetailView&record=' . $row['booking_id'] . '">' . $row['booking'] . '</a>
			 				' . (!empty($row['voucher_name']) ? '/ <a target="_blank" href="index.php?module=' . $row['voucher_type'] . '&action=DetailView&record=' . $row['voucher_id'] . '">' . $row['voucher_name'] . '</a>' : '') . '
			 			</td>
			 			<td class="text-center">' . $row['total_qty'] . '</td>
			 			<td class="text-center">' . $row['return_qty'] . '</td>
			 			<td class="text-end">' . format_number($row['doanhso'] - $row['luggage_purchase_price']) . '</td>
			 			<td class="text-end">' . format_number($row['return_service_fee']) . '</td>
			 			<td class="text-end">' . format_number($row['change_service_fee']) . '</td>
			 			<td class="text-end">' . format_number($row['doanhso'] - $row['luggage_purchase_price'] + $row['return_service_fee'] + $row['change_service_fee']) . '</td>
			 		</tr>';
			$i++;

			$total_qty += $row['total_qty'];
			$total_return_qty += $row['return_qty'];
			$total_ds_booking += $row['doanhso'] - $row['luggage_purchase_price'];
			$total_return_service_fee += $row['return_service_fee'];
			$total_change_service_fee += $row['change_service_fee'];
			$total_ds += $row['doanhso'] - $row['luggage_purchase_price'] + $row['return_service_fee'] + $row['change_service_fee'];
		}

		$html .= '
				<tr class="last-row footer-tr">
			 		<td></td>
			 		<td></td>
			 		<td></td>
			 		<td class="text-end">' . format_number($total_qty) . '</td>
			 		<td class="text-end">' . format_number($total_return_qty) . '</td>
			 		<td class="text-end">' . format_number($total_ds_booking) . '</td>
			 		<td class="text-end">' . format_number($total_return_service_fee) . '</td>
			 		<td class="text-end">' . format_number($total_change_service_fee) . '</td>
			 		<td class="text-end">' . format_number($total_ds) . '</td>
			 	</tr></tbody>';

		return $html;
		// } else {
		// 	return "Thiếu thông tin booker. Vui lòng vào trang doanh số chọn lại.";
		// }
	}

	// Lấy doanh số tối thiểu
	function getMinRate() {
		$sql_rate_bonus = '
				SELECT from_value 
				FROM ec_commission 
				WHERE DATE_FORMAT(date_entered, "%Y-%m-%d") = ( 
					SELECT DATE_FORMAT(date_entered, "%Y-%m-%d") FROM ec_commission 
					WHERE CONCAT(year, "-", month, "-01") <= "' . date('Y-n-01') . '" AND deleted = 0
					ORDER BY date_entered DESC
					LIMIT 1
				) AND deleted = 0
				ORDER BY from_value
				LIMIT 1';
		return $this->bean->db->getOne($sql_rate_bonus);
	}
}
