<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewdebtopay extends SugarView
{
	function display()
	{
		$smartyCont = new Sugar_Smarty();
		$this->populateContent($smartyCont);
		$smartyCont->display('modules/EC_Flight_Bookings/tpls/view_debtopay.tpl');
	}

	// 26-02-2020 : Cập nhật 1, số tiền có hiện số âm, số tiền nợ hiện số dương
	// 26-02-2020 : Cập nhật 2, tính từ trạng thái xác nhận nếu đã xuất vé thì hiện lên báo cáo công nợ 
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

		//$accounting_code = '144'; // tài khoản ký quỹ nhà cung cấp
		//$payment_type = "'3361ac47-2254-701a-55d1-508abcb90f50'"; // công nợ phải trả
		//$payment_type .= ",'55730166-4f7f-f8fc-78a6-529f673b431e'"; // ký quỹ jetstar
		//$payment_type .= ",'6dff0213-1547-1ae9-b433-529f6736aef4'"; // ký quỹ vietjet
		$opening_year = date('Y', strtotime($post_fdate));

		if (isset($_POST['btnViewDetail'])) {
			$html = '';
			$acc_cnt = count($_POST['supplier_id']);
			for ($i = 0; $i < $acc_cnt; $i++) {
				$supplier_id = $_POST['supplier_id'][$i];
				$voucher_arr = $this->getVoucherList($opening_year, $_POST['acc_code_' . $supplier_id], $supplier_id, $post_fdate, $post_tdate);

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
								<label style="font-size:15pt;">CHI TIẾT CÔNG NỢ PHẢI TRẢ</label><br />
								<label style="font-style:italic;">Từ ngày ' . $post_fdate . ' đến ngày ' . $post_tdate . '</label><br /><br />
							</td>
			  			</tr>
			  			<tr>
							<td colspan="3" style="font-weight:bold;">Mã đối tượng: ' . $_POST['supcode_' . $supplier_id] . ' - Tên đối tượng: ' . $_POST['supname_' . $supplier_id] . '&nbsp;&nbsp;&nbsp;<input type="submit" name="btnViewDetail" class="btn btn-success" value="Xuất excel"></td>
						</tr>
						<tr>
							<td colspan="3" style="font-weight:bold;">Tài khoản: ' . $_POST['acc_code_' . $supplier_id] . '</td>
						</tr>
						<tr>
							<td colspan="3">
								<table class="table-details__booking table-details__debtopay" border="0" cellspacing="0" cellpadding="0" id="tbl-details">
						  			<thead><tr>
										<th width="10%"><div align="center"><strong>Ngày hạch toán</strong></div></th>
										<th width="12%"><div align="center"><strong>Số chứng từ</strong></div></th>
										<th width="5%"><div align="center"><strong>Số vé</strong></div></th>
										<th width="30%"><div align="center"><strong>Diễn giải</strong></div></th>
										<th width="12%"><div align="center"><strong>Số tiền bán</strong></div></th>
										<th width="12%"><div align="center"><strong>Số tiền nợ</strong></div></th>
										<th width="12%"><div align="center"><strong>Tiền đã trả</strong></div></th>
										<th width="12%"><div align="center"><strong>Còn lại</strong></div></th>
									</tr>
									<tr>
										<th><div align="center"><strong>A</strong></div></th>
										<th><div align="center"><strong>B</strong></div></th>
										<th><div align="center"><strong>C</strong></div></th>
										<th><div align="center"><strong>D</strong></div></th>
										<th><div align="center"><strong>1</strong></div></th>
										<th><div align="center"><strong>2</strong></div></th>
										<th><div align="center"><strong>3</strong></div></th>
										<th><div align="center"><strong>4</strong></div></th>
									</tr>
									</thead>
						  			' . $voucher_arr['html'] . '
									<tr class="footer-tr">
										<td colspan="2"><strong>Cộng</strong></td>
										<td style="font-weight:bold;" align="right">' . format_number($voucher_arr['total_qty']) . '</td>
										<td></td>
										<td style="font-weight:bold;" align="right">' . format_number($voucher_arr['total_sell']) . '</td>
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
				$smartyobj->assign('SUPPLIER_ID', $_POST['supplier_id'][$i]);
			} // end for
			$smartyobj->assign('VOUCHER_LIST', $html);
			$smartyobj->assign('POST_FROM_DATE', $post_fdate);
			$smartyobj->assign('POST_TO_DATE', $post_tdate);
			$smartyobj->assign('SUPPLIER_CODE', $_POST['supcode_' . $supplier_id]);
			$smartyobj->assign('SUPPLIER_NAME', $_POST['supname_' . $supplier_id]);

			if (isset($_POST['exportexcel'])) {
				pr($_POST);
				pr($html);
				die;
				ob_clean();
				header("Pragma: cache");
				require_once('modules/EC_Flight_Bookings/views/congnophaitra.xls.php');
				$xls = generateXLSTemplate($html, $post_fdate, $post_tdate);
				$xls = chr(255) . chr(254) . mb_convert_encoding($xls, "UTF-16LE", "UTF-8");
				header("Content-type: application/x-msdownload");
				header("Content-disposition: xls; filename=congnophaitra_" . time() . ".xls; size=" . strlen($xls));
				echo $xls;
				exit();
			}
			return;
		} // end if

		$sup_arr = $this->getSupplierList($opening_year, $post_fdate, $post_tdate);
		$smartyobj->assign('SUPPLIER_LIST', $sup_arr['html']);
		$smartyobj->assign('TOTAL_DEBT', format_number($sup_arr['total_debt']));
		$smartyobj->assign('POST_FROM_DATE', $post_fdate);
		$smartyobj->assign('POST_TO_DATE', $post_tdate);
	}

	function getSupplierList($opening_year, $post_fdate, $post_tdate)
	{
		global $db;

		$sql = "SELECT tmp.supplier_id
					  ,a.ticker_symbol AS supplier_code
					  ,a.name AS supplier_name
					  ,a.billing_address_street AS address
					  ,a.phone_office AS phone
					  ,SUM(IFNULL(tmp.debt_amount, 0)) - SUM(IFNULL(tmp.pay_amount, 0)) AS total_debt
					  ,tmp.accounting_code
		FROM (
				-- START TERM
				SELECT p.id
					  ,p.parent_id AS supplier_id
					  ,SUM(IFNULL(p.dunodau, 0) - IFNULL(p.ducodau, 0)) AS debt_amount
					  ,0 AS pay_amount
					  ,p.sotaikhoan AS accounting_code
				FROM ec_chitiettaikhoan" . $opening_year . " p
				WHERE p.deleted = 0
				AND p.parent_type = 'Accounts'
				AND p.sotaikhoan IN ('144','331')
				AND p.parent_id IS NOT NULL
				GROUP BY p.parent_id

				-- BOOKING DETAILS
				UNION
				SELECT d.id
					  ,d.supplier_id
					  ,SUM(IFNULL(d.total_bought_price, 0)) AS debt_amount
					  ,0 AS pay_amount
					  ,'' AS accounting_code
				FROM ec_booking_details d
				LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
				WHERE d.deleted = 0
				AND p.booking_status IN ('7', '8')
				AND p.is_ticket_exported = 1
				AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
				AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
				AND d.total_bought_price > 0
				AND d.supplier_id IS NOT NULL
				GROUP BY d.supplier_id

				-- BOOKING PAXS OUTBOUND
				UNION
				SELECT CONCAT(d.id, '-OUTBOUND') AS id
					  ,d.supplier_id
					  ,SUM(IFNULL(d.luggage_purchase, 0)) AS debt_amount
					  ,0 AS pay_amount
					  ,'' AS accounting_code
				FROM ec_booking_passengers d
				LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
				WHERE d.deleted = 0
				AND p.booking_status IN ('7', '8')
				AND p.is_ticket_exported = 1
				AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
				AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
				AND d.luggage_price > 0
				AND d.luggage_purchase > 0
				AND d.supplier_id IS NOT NULL
				AND d.add_type IS NULL
				GROUP BY d.supplier_id

				-- BOOKING PAXS INBOUND
				UNION
				SELECT CONCAT(d.id, '-INBOUND') AS id
					  ,d.supplier_inbound_id AS supplier_id
					  ,SUM(IFNULL(d.luggage_purchase_inbound, 0)) AS debt_amount
					  ,0 AS pay_amount
					  ,'' AS accounting_code
				FROM ec_booking_passengers d
				LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
				WHERE d.deleted = 0
				AND p.booking_status IN ('7', '8')
				AND p.is_ticket_exported = 1
				AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
				AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
				AND d.luggage_price_inbound > 0
				AND d.luggage_purchase_inbound > 0
				AND d.supplier_inbound_id IS NOT NULL
				AND d.add_type IS NULL
				GROUP BY d.supplier_inbound_id

				-- RECEIPT
				UNION
				SELECT p.id AS id
					  ,account_id_c AS supplier_id
					  ,p.amount AS debt_amount
					  ,0 AS pay_amount
					  ,'' AS accounting_code
				FROM ec_receipt_voucher p
				WHERE p.deleted = 0
				AND p.loai_thu = '9'
				AND p.account_id_c IS NOT NULL
				AND p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
				AND p.ngaychungtu <= '" . date('Y-m-d', strtotime($post_tdate)) . "'

				-- SUPPLIER 1
				UNION
				SELECT CONCAT(p.id, '-SUPPLIER1') AS id
					  ,p.supplier_id
					  ,IFNULL(p.bought_amount, 0) AS debt_amount
					  ,0 AS pay_amount
					  ,'' AS accounting_code
				FROM ec_receipt_voucher p
				WHERE p.deleted = 0
				AND p.loai_thu IN ('4', '5', '14')
				AND p.supplier_id IS NOT NULL
				AND p.bought_amount IS NOT NULL
				AND p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
				AND p.ngaychungtu <= '" . date('Y-m-d', strtotime($post_tdate)) . "'

				-- SUPPLIER 2
				UNION
				SELECT CONCAT(p.id, '-SUPPLIER2') AS id
					  ,p.supplier2_id AS supplier_id
					  ,IFNULL(p.bought_amount2, 0) AS debt_amount
					  ,0 AS pay_amount
					  ,'' AS accounting_code
				FROM ec_receipt_voucher p
				WHERE p.deleted = 0
				AND p.loai_thu IN ('4', '5', '14')
				AND p.supplier2_id IS NOT NULL
				AND p.bought_amount2 IS NOT NULL
				AND p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
				AND p.ngaychungtu <= '" . date('Y-m-d', strtotime($post_tdate)) . "'

				-- SUPPLIER 3
				UNION
				SELECT CONCAT(p.id, '-SUPPLIER3') AS id
					  ,p.supplier3_id AS supplier_id
					  ,IFNULL(p.bought_amount3, 0) AS debt_amount
					  ,0 AS pay_amount
					  ,'' AS accounting_code
				FROM ec_receipt_voucher p
				WHERE p.deleted = 0
				AND p.loai_thu IN ('4', '5', '14')
				AND p.supplier3_id IS NOT NULL
				AND p.bought_amount3 IS NOT NULL
				AND p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
				AND p.ngaychungtu <= '" . date('Y-m-d', strtotime($post_tdate)) . "'

				-- TICKET REFUND
				UNION
				SELECT p.id
					  ,c.nhacc_id AS supplier_id
					  ,-SUM(IFNULL(c.sotienhang, 0)) AS debt_amount
					  ,0 AS pay_amount
					  ,'' AS accounting_code
				FROM ec_chitiethoanve c
				LEFT JOIN ec_hoanve p ON c.hoanve_id = p.id AND p.deleted = 0
				WHERE c.deleted = 0
				AND c.dahoan = 1
				AND p.tinhtrang = '1'
				AND p.ngayhachtoan >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
				AND p.ngayhachtoan <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
				AND c.sotienhang > 0
				AND c.nhacc_id IS NOT NULL
				GROUP BY c.nhacc_id

				-- PAYMENT VOUCHER
				UNION
				SELECT p.id
					  ,p.supplier_id
					  ,0 AS debt_amount
					  ,IFNULL(p.amount, 0) AS pay_amount
					  ,'' AS accounting_code
				FROM ec_payment_voucher p
				WHERE p.deleted = 0
				AND p.pv_status = '3'
				AND p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
				AND p.ngaychungtu <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
				AND p.supplier_id IS NOT NULL
		) AS tmp
		LEFT JOIN accounts a ON tmp.supplier_id = a.id AND a.deleted = 0
		WHERE a.is_stop_tracking = 0
		AND a.account_type = 'Supplier'
		GROUP BY tmp.supplier_id
		-- HAVING total_debt <> 0
		ORDER BY supplier_code ";

		// if($GLOBALS['current_user']->user_name == 'hungnh') {
		// 	pr($sql);
		// }


		$res = $db->query($sql);
		$total_debt = 0;
		$html = '';
		while ($row = $db->fetchByAssoc($res)) {
			if($row['total_debt'] != 0){
				$html .= '<tr>
					<td align="center">
						<input type="checkbox" name="supplier_id[]" value="' . $row['supplier_id'] . '" />
						<input type="hidden" name="supcode_' . $row['supplier_id'] . '" value="' . $row['supplier_code'] . '" />
						<input type="hidden" name="supname_' . $row['supplier_id'] . '" value="' . $row['supplier_name'] . '" />
						<input type="hidden" name="acc_code_' . $row['supplier_id'] . '" value="' . $row['accounting_code'] . '" />
					</td>
					<td align="left">' . $row['supplier_code'] . '</td>
					<td align="left">' . $row['supplier_name'] . '</td>
					<td align="right" data-realnumber="' . $row['total_debt'] . '">' . format_number($row['total_debt']) . '</td>
				</tr>';
			}

			//========== Begin close opening amount ==========//
			// if ($GLOBALS['current_user']->user_name == 'nponline' && $post_fdate == '01-01-2019' && $post_tdate == '31-12-2019' && $row['total_debt'] != 0) {
			//     $accounting_code = '144';
			//     $sql_cus = "SELECT id
			// 				FROM ec_chitiettaikhoan
			// 				WHERE deleted=0
			// 				AND parent_type='Accounts'
			// 				AND parent_id='" . $row['supplier_id'] . "' ";
			//     $cttk_id = $db->getOne($sql_cus);
			//     if (empty($cttk_id)) {
			//         $cttk = new EC_ChiTietTaiKhoan();
			//         $cttk->id = '';
			//         $cttk->name = $row['supplier_name'];
			//         $cttk->parent_type = 'Accounts';
			//         $cttk->parent_id = $row['supplier_id'];
			//         $cttk->sotaikhoan = $accounting_code;
			//         $cttk->dunodau = unformat_number($row['total_debt']);
			//         $cttk->save();
			//     } else {
			//         $update = "UPDATE ec_chitiettaikhoan 
			// 				   SET dunodau=" . unformat_number($row['total_debt']) . "
			// 				   WHERE deleted=0 
			// 				   AND parent_type='Accounts'
			// 				   AND parent_id='" . $row['supplier_id'] . "'
			// 				   AND id='" . $cttk_id . "'
			// 				   LIMIT 1";
			//         $db->query($update);
			//     }
			// }
			//========== End close opening amount ==========//

			$total_debt += $row['total_debt'];
		}

		return array('html' => $html, 'total_debt' => abs($total_debt));
	}

	function getVoucherList($opening_year, $accounting_code, $supplier_id, $post_fdate, $post_tdate)
	{
		global $db, $current_user;

		/**
		 * Loại thu "Thu tiền khách sạn phát sinh từ ngày 17/05/2025"
		 * Nếu cả $post_fdate và $post_tdate đều trước ngày 17/05/2025, dùng: AND p.loai_thu IN ('4', '5')
		 * Nếu bất kỳ ngày nào sau hoặc đúng 17/05/2025, dùng: AND p.loai_thu IN ('4', '5', '14')
		 */
		$targetDate = '2025-05-17';
		$fromDate = date('Y-m-d', strtotime($post_fdate));
		$toDate   = date('Y-m-d', strtotime($post_tdate));
		if ($fromDate < $targetDate && $toDate < $targetDate) {
			$sql_hotel = " AND p.loai_thu IN ('4', '5') ";
		} else {
			$sql_hotel = " AND p.loai_thu IN ('4', '5', '14') ";
		}

		// Get opening amount
		$sql = "
			SELECT 
				'' AS id
				,'OPN' AS voucher_name
				,'' AS qty
				,'' AS date_entered
				,'' AS posted_date
				,'' AS sell_amount
				,'' AS debt_amount
				,'' AS pay_amount
				,(SUM(IFNULL(tmp.debt_amount, 0)) - SUM(IFNULL(tmp.pay_amount, 0))) AS remain_amount
				,'' AS parent_type
				,'' AS parent_id
				,'' AS description
				,'' AS order_date
			FROM (
				-- START TERM 
				SELECT p.id
					  ,(IFNULL(p.dunodau, 0) - IFNULL(p.ducodau, 0)) AS debt_amount
					  ,0 AS pay_amount
				FROM ec_chitiettaikhoan" . $opening_year . " p
				WHERE p.deleted = 0
				AND p.parent_type = 'Accounts' ";

		if (!empty($accounting_code)) {
			$sql .= " AND p.sotaikhoan = '" . $accounting_code . "' ";
		}

		$sql .= " AND p.parent_id = '" . $supplier_id . "'";

		$sql .= " 
			-- BOOKING DETAILS
			UNION
			SELECT 
				d.id
				,IFNULL(d.total_bought_price, 0) AS debt_amount
				,0 AS pay_amount
			FROM ec_booking_details d
			LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
			WHERE d.deleted = 0
			AND p.booking_status IN ('7', '8')
			AND p.is_ticket_exported = 1
			AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
			AND p.date_ticket_issue < '" . $fromDate . "'
			AND d.total_bought_price > 0
			AND d.supplier_id = '" . $supplier_id . "'

			-- BOOKING PAXS OUTBOUND
			UNION
			SELECT 
				CONCAT(d.id, '-OUTBOUND') AS id 
				,IFNULL(d.luggage_purchase, 0) AS debt_amount
				,0 AS pay_amount
			FROM ec_booking_passengers d
			LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
			WHERE d.deleted = 0
			AND p.booking_status IN ('7', '8')
			AND p.is_ticket_exported = 1
			AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
			AND p.date_ticket_issue < '" . $fromDate . "'
			AND d.luggage_price > 0
			AND d.luggage_purchase > 0
			AND d.supplier_id = '" . $supplier_id . "'
			AND d.add_type IS NULL 

			-- BOOKING PAXS INBOUND
			UNION
			SELECT 
				CONCAT(d.id, '-INBOUND') AS id
				,IFNULL(d.luggage_purchase_inbound, 0) AS debt_amount
				,0 AS pay_amount
			FROM ec_booking_passengers d
			LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
			WHERE d.deleted = 0
			AND p.booking_status IN ('7', '8')
			AND p.is_ticket_exported = 1
			AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
			AND p.date_ticket_issue < '" . $fromDate . "'
			AND d.luggage_price_inbound > 0
			AND d.luggage_purchase_inbound > 0
			AND d.supplier_inbound_id = '" . $supplier_id . "'
			AND d.add_type IS NULL

			-- RECEIPT
			UNION
			SELECT 
				p.id AS id
				,p.amount AS debt_amount
				,0 AS pay_amount
			FROM ec_receipt_voucher p
			WHERE p.deleted = 0
			AND p.loai_thu = '9'
			AND p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
			AND p.ngaychungtu < '" . $fromDate . "'
			AND p.account_id_c = '" . $supplier_id . "'

			-- SUPPLIER 1
			UNION
			SELECT 
				CONCAT(p.id, '-SUPPLIER1') AS id
				,IFNULL(p.bought_amount, 0) AS debt_amount
				,0 AS pay_amount
			FROM ec_receipt_voucher p
			WHERE p.deleted = 0
			" . $sql_hotel . "
			AND p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
			AND p.ngaychungtu < '" . $fromDate . "'
			AND p.supplier_id = '" . $supplier_id . "'

			-- SUPPLIER 2
			UNION
			SELECT 
				CONCAT(p.id, '-SUPPLIER2') AS id
				,IFNULL(p.bought_amount2, 0) AS debt_amount
				,0 AS pay_amount
			FROM ec_receipt_voucher p
			WHERE p.deleted = 0
			" . $sql_hotel . "
			AND p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
			AND p.ngaychungtu < '" . $fromDate . "'
			AND p.supplier2_id = '" . $supplier_id . "'

			-- SUPPLIER 3
			UNION
			SELECT 
				CONCAT(p.id, '-SUPPLIER3') AS id
				,IFNULL(p.bought_amount3, 0) AS debt_amount
				,0 AS pay_amount
			FROM ec_receipt_voucher p
			WHERE p.deleted = 0
			" . $sql_hotel . "
			AND p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
			AND p.ngaychungtu < '" . $fromDate . "'
			AND p.supplier3_id = '" . $supplier_id . "'

			-- TICKET REFUND
			UNION
			SELECT c.id
					,-IFNULL(c.sotienhang, 0) AS debt_amount
					,0 AS pay_amount
			FROM ec_chitiethoanve c
			LEFT JOIN ec_hoanve p ON c.hoanve_id = p.id AND p.deleted = 0
			WHERE c.deleted = 0
			AND c.dahoan = 1
			AND p.tinhtrang = '1'
			AND p.ngayhachtoan >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
			AND p.ngayhachtoan < '" . $fromDate . "'
			AND c.nhacc_id = '" . $supplier_id . "'

			-- PAYMENT VOUCHER
			UNION
			SELECT 
				p.id
				,0 AS debt_amount
				,IFNULL(p.amount, 0) AS pay_amount
			FROM ec_payment_voucher p
			WHERE p.deleted = 0
			AND p.pv_status = '3'
			AND p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
			AND p.ngaychungtu < '" . $fromDate . "'
			AND p.supplier_id = '" . $supplier_id . "'
		) AS tmp";

		// Get voucher list
		$sql .= "
		-- TICKET BOOKING
		UNION
		SELECT p.id
			  ,p.name AS voucher_name
			  ,SUM(IFNULL( d.quantity, 0 )) AS qty
			  ,p.date_entered
			  ,IF(d.direction = 0, p.date_ticket_issue, p.date_ticket_inbound_issue) AS posted_date
			  ,SUM(IFNULL(d.total_price, 0)) AS sell_amount
			  ,SUM(IFNULL(d.total_bought_price, 0)) AS debt_amount
			  ,'' AS pay_amount
			  ,'' AS remain_amount
			  ,'EC_Flight_Bookings' AS parent_type
			  ,p.id AS parent_id
			  ,p.description
			  ,p.date_entered AS order_date
		FROM ec_booking_details d
		LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
		WHERE d.deleted = 0
		AND p.booking_status IN ('7', '8')
		AND p.is_ticket_exported = 1
		AND p.date_ticket_issue BETWEEN '" . $fromDate . "' AND '" . $toDate . "'
		AND d.supplier_id = '" . $supplier_id . "'
		GROUP BY d.booking_id
		
		-- BOOKING PAXS OUTBOUND
		UNION
		SELECT CONCAT(d.id, '-OUTBOUND') AS id
			  ,CONCAT('HLCD-', p.name) AS voucher_name
			  ,'' AS qty
			  ,p.date_entered
			  ,p.date_ticket_issue AS posted_date
			  ,SUM(IFNULL(d.luggage_price, 0)) AS sell_amount
			  ,SUM(IFNULL(d.luggage_purchase, 0)) AS debt_amount
			  ,'' AS pay_amount
			  ,'' AS remain_amount
			  ,'EC_Flight_Bookings' AS parent_type
			  ,p.id AS parent_id
			  ,p.description
			  ,p.date_entered AS order_date
		FROM ec_booking_passengers d
		LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
		WHERE d.deleted = 0
		AND p.booking_status IN ('7', '8')
		AND p.is_ticket_exported = 1
		AND p.date_ticket_issue BETWEEN '" . $fromDate . "' AND '" . $toDate . "'
		AND d.luggage_price > 0
		AND d.luggage_purchase > 0
		AND d.supplier_id = '" . $supplier_id . "'
		AND d.add_type IS NULL
		GROUP BY d.booking_id
		
		-- BOOKING PAXS INBOUND
		UNION
		SELECT CONCAT(d.id, '-INBOUND') AS id
			  ,CONCAT('HLCV-', p.name) AS voucher_name
			  ,'' AS qty
			  ,p.date_entered
			  ,p.date_ticket_issue AS posted_date
			  ,SUM(IFNULL(d.luggage_price_inbound, 0)) AS sell_amount
			  ,SUM(IFNULL(d.luggage_purchase_inbound, 0)) AS debt_amount
			  ,'' AS pay_amount
			  ,'' AS remain_amount
			  ,'EC_Flight_Bookings' AS parent_type
			  ,p.id AS parent_id
			  ,p.description
			  ,p.date_entered AS order_date
		FROM ec_booking_passengers d
		LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
		WHERE d.deleted = 0
		AND p.booking_status IN ('7', '8')
		AND p.is_ticket_exported = 1
		AND p.date_ticket_issue BETWEEN '" . $fromDate . "' AND '" . $toDate . "'
		AND d.luggage_price_inbound > 0
		AND d.luggage_purchase_inbound > 0
		AND d.supplier_inbound_id = '" . $supplier_id . "'
		AND d.add_type IS NULL
		GROUP BY d.booking_id

		-- RECEIPT
		UNION
		SELECT p.id AS id
			  ,p.name AS voucher_name
			  ,'' AS qty
			  ,p.date_entered
			  ,p.ngaychungtu AS posted_date
			  ,0 AS sell_amount
			  ,p.amount AS debt_amount
			  ,'' AS pay_amount
			  ,'' AS remain_amount
			  ,'EC_Receipt_Voucher' AS parent_type
			  ,p.id AS parent_id
			  ,p.description
			  ,p.date_entered AS order_date
		FROM ec_receipt_voucher p
		WHERE p.deleted = 0
		AND p.loai_thu = '9'
		AND p.ngaychungtu BETWEEN '" . $fromDate . "' AND '" . $toDate . "'
		AND p.account_id_c = '" . $supplier_id . "'

		-- SUPPLIER 1
		UNION
		SELECT CONCAT(p.id, '-SUPPLIER1') AS id
			  ,p.name AS voucher_name
			  ,'' AS qty
			  ,p.date_entered
			  ,p.ngaychungtu AS posted_date
			  ,IFNULL(p.sell_amount, 0) AS sell_amount
			  ,IFNULL(p.bought_amount, 0) AS debt_amount
			  ,'' AS pay_amount
			  ,'' AS remain_amount
			  ,'EC_Receipt_Voucher' AS parent_type
			  ,p.id AS parent_id
			  ,p.description
			  ,p.date_entered AS order_date
		FROM ec_receipt_voucher p
		WHERE p.deleted = 0
		" . $sql_hotel . "
		AND p.ngaychungtu BETWEEN '" . $fromDate . "' AND '" . $toDate . "'
		AND p.supplier_id = '" . $supplier_id . "'

		-- SUPPLIER 2
		UNION
		SELECT CONCAT(p.id, '-SUPPLIER2') AS id
			  ,p.name AS voucher_name
			  ,'' AS qty
			  ,p.date_entered
			  ,p.ngaychungtu AS posted_date
			  ,IFNULL(p.sell_amount2, 0) AS sell_amount
			  ,IFNULL(p.bought_amount2, 0) AS debt_amount
			  ,'' AS pay_amount
			  ,'' AS remain_amount
			  ,'EC_Receipt_Voucher' AS parent_type
			  ,p.id AS parent_id
			  ,p.description
			  ,p.date_entered AS order_date
		FROM ec_receipt_voucher p
		WHERE p.deleted = 0
		" . $sql_hotel . "
		AND p.ngaychungtu BETWEEN '" . $fromDate . "' AND '" . $toDate . "'
		AND p.supplier2_id = '" . $supplier_id . "'

		-- SUPPLIER 3
		UNION
		SELECT CONCAT(p.id, '-SUPPLIER3') AS id
			  ,p.name AS voucher_name
			  ,'' AS qty
			  ,p.date_entered
			  ,DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) AS posted_date
			  ,IFNULL(p.sell_amount3, 0) AS sell_amount
			  ,IFNULL(p.bought_amount3, 0) AS debt_amount
			  ,'' AS pay_amount
			  ,'' AS remain_amount
			  ,'EC_Receipt_Voucher' AS parent_type
			  ,p.id AS parent_id
			  ,p.description
			  ,p.date_entered AS order_date
		FROM ec_receipt_voucher p
		WHERE p.deleted = 0
		" . $sql_hotel . "
		AND p.ngaychungtu BETWEEN '" . $fromDate . "' AND '" . $toDate . "'
		AND p.supplier3_id = '" . $supplier_id . "'

		-- TICKET REFUND
		UNION
		SELECT p.id
			  ,p.name AS voucher_name
			  ,'' AS qty
			  ,p.date_entered
			  ,p.ngayhachtoan AS posted_date
			  ,SUM(IFNULL(c.sotienkhach, 0)) AS sell_amount
			  ,-SUM(IFNULL(c.sotienhang, 0)) AS debt_amount
			  ,'' AS pay_amount
			  ,'' AS remain_amount
			  ,'EC_HoanVe' AS parent_type
			  ,p.id AS parent_id
			  ,p.description
			  ,p.date_entered AS order_date
		FROM ec_chitiethoanve c
		LEFT JOIN ec_hoanve p ON c.hoanve_id = p.id AND p.deleted = 0
		WHERE c.deleted = 0
		AND c.dahoan = 1
		AND p.tinhtrang = '1'
		AND p.ngayhachtoan >= '" . $fromDate . "'
		AND p.ngayhachtoan <= '" . $toDate . "'
		AND c.nhacc_id = '" . $supplier_id . "'
		GROUP BY c.hoanve_id

		-- PAYMENT VOUCHER
		UNION
		SELECT p.id
			  ,p.name AS voucher_name
			  ,'' AS qty
			  ,p.date_entered
			  ,p.ngaychungtu AS posted_date
			  ,'' AS sell_amount
			  ,'' AS debt_amount
			  ,IFNULL(p.amount, 0) AS pay_amount
			  ,'' AS remain_amount
			  ,'EC_Payment_Voucher' AS parent_type
			  ,p.id AS parent_id
			  ,p.description
			  ,p.date_entered AS order_date
		FROM ec_payment_voucher p
		WHERE p.deleted = 0
		AND p.pv_status = '3'
		AND p.ngaychungtu >= '" . $fromDate . "'
		AND p.ngaychungtu <= '" . $toDate . "'
		AND p.supplier_id = '" . $supplier_id . "'
		ORDER BY posted_date, order_date";

		// if($GLOBALS['current_user']->user_name == 'hungnh') {
		// 	pr($sql);
		// } 

		$res 		= $db->query($sql);
		$i 			= 0;
		$html 		= '';
		$total_sell 	= 0;
		$total_debt 	= 0;
		$total_pay 	= 0;
		$total_remain 	= 0;
		$total_qty = 0;

		while ($row = $db->fetchByAssoc($res)) {
			if ($row['voucher_name'] == 'OPN') {
				$total_remain += $row['remain_amount'];
				$html .= '<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td style="font-weight:bold;">Số đầu kỳ</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td style="font-weight:bold;" align="right">' . format_number($row['remain_amount']) . '</td>
				</tr>';
			} else {
				$total_sell += (int)$row['sell_amount'];
				$total_debt += (int)$row['debt_amount'];
				$total_pay += (int)$row['pay_amount'];
				$total_qty += (int)$row['qty'];
				$total_remain += (int)$row['debt_amount'] - (int)$row['pay_amount'];
				$html .= '<tr>
							<td align="center">' . date('d/m/Y', strtotime($row['posted_date'])) . '</td>
							<td><a href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . $row['parent_id'] . '" target="_blank">' . $row['voucher_name'] . '</a></td>
							<td align="center">' . $row['qty'] . '</td>
							<td>' . $row['description'] . '</td>
							<td align="right">' . (!empty($row['sell_amount']) ? format_number($row['sell_amount']) : '') . '</td>
							<td align="right">' . (!empty($row['debt_amount']) ? format_number($row['debt_amount']) : '') . '</td>
							<td align="right">' . (!empty($row['pay_amount']) ? '-' . format_number($row['pay_amount']) : '') . '</td>
							<td align="right">' . format_number($total_remain) . '</td>
					</tr>';
			}

			$i++;
		} // while

		return array('html' => $html, 'total_qty' => $total_qty, 'total_sell' => $total_sell, 'total_debt' => $total_debt, 'total_pay' => '-' . $total_pay, 'total_remain' => $total_remain);
	}
}
