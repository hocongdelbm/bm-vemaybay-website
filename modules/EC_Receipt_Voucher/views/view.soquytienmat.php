<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewsoquytienmat extends SugarView
{
	function display()
	{
		$smartyCont = new Sugar_Smarty();
		$this->populateCont($smartyCont);
		$smartyCont->display('modules/EC_Receipt_Voucher/tpls/soquytienmat.tpl');
	}

	function populateCont($smartyobj)
	{
		global $current_user;
		// From date
		if (isset($_POST['from_date']) && !empty($_POST['from_date'])) {
			$post_fdate = $_POST['from_date'];
		} else {
			$post_fdate = date('d-m-Y');
		}
		// To date
		if (isset($_POST['to_date']) && !empty($_POST['to_date'])) {
			$post_tdate = $_POST['to_date'];
		} else {
			$post_tdate = date('d-m-Y');
		}
		// Location
		if (isset($_POST['location_id']) && !empty($_POST['location_id'])) {
			$location_id = $_POST['location_id'];
			$location_name = $_POST['location_name'];
		} else {
			$location_id = '';
			$location_name = '';
		}
		$department_id = $current_user->department_id;
		$department_info = myGetDepartmentInfo($department_id);
		$smartyobj->assign('POST_FDATE', $post_fdate);
		$smartyobj->assign('POST_TDATE', $post_tdate);
		$smartyobj->assign('LOCATION_NAME', $location_name);
		if ($current_user->id == '7c20e013-b0d6-e1f3-b113-53deed58f0a2') {
			$smartyobj->assign('LOCATION_ID', get_select_options_with_id(array('bfd02e6d-ba30-d724-9937-56f4ed008b4b' => 'VP Giải Phóng')));
		} else {
			$smartyobj->assign('LOCATION_ID', myGetLocationListByDepID($department_id, $location_id));
		}

		if (isset($_POST['btnViewDetail'])) {
			$accounting_code = '1111';
			$opening_year = date('Y', strtotime($post_fdate));

			$data = $this->getVoucherList($opening_year, $accounting_code, $post_fdate, $post_tdate, $location_id);

			$smartyobj->assign('COM_NAME', $department_info['com_name']);
			$smartyobj->assign('COM_ADDRESS', $department_info['com_address']);
			$smartyobj->assign('COM_TAX_CODE', $department_info['com_taxcode']);
			$smartyobj->assign('CURR_DAY', date('d'));
			$smartyobj->assign('CURR_MON', date('m'));
			$smartyobj->assign('CURR_YEAR', date('Y'));
			$smartyobj->assign('VOUCHER_LIST', $data['html']);
			$smartyobj->assign('TOTAL_RECEIPT', format_number($data['total_receipt']));
			$smartyobj->assign('TOTAL_PAYMENT', format_number($data['total_payment']));
			$smartyobj->assign('TOTAL_REMAIN', format_number($data['total_remain']));
		}
	}

	function getVoucherList($opening_year, $accounting_code, $post_fdate, $post_tdate, $location_id = '')
	{
		global $db, $app_list_strings;

		// For opening amount
		$sql_search = " AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) < '" . date('Y-m-d', strtotime($post_fdate)) . "' ";

		// For voucher list
		$sql_search2 = " AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-m-d', strtotime($post_fdate)) . "'
							AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($post_tdate)) . "' ";

		$sql_search3 = '';
		if (isset($location_id) && !empty($location_id)) {
			$sql_search .= " AND p.com_location_id = '" . $location_id . "' ";
			$sql_search2 .= " AND p.com_location_id = '" . $location_id . "' ";
			$sql_search3 .= " AND p.location_id = '" . $location_id . "' ";
		} else {
			$location_list = array_keys($app_list_strings['location_list']);
			$location_list = implode("','", $location_list);
			$sql_search .= " AND p.com_location_id IN ('" . $location_list . "') ";
			$sql_search2 .= " AND p.com_location_id IN ('" . $location_list . "') ";
			$sql_search3 .= " AND p.location_id IN ('" . $location_list . "') ";
		}

		// Get the opening amount
		$sql = "SELECT '' AS date_entered,
					'' AS posted_date,
					'' AS voucher_date,
					'' AS voucher_name,
					'' AS description,
					'OPN' AS parent_type,
					'' AS parent_id,
					'' AS receipt_amount,
					'' AS payment_amount,
					SUM(IFNULL(tmp.receipt_amount, 0) - IFNULL(tmp.payment_amount, 0)) AS remain_amount
				FROM
				(
					-- OPENING AMOUNT
					SELECT p.id,
						SUM(IFNULL(dunodau, 0) - IFNULL(ducodau, 0)) AS receipt_amount,
						0 AS payment_amount
					FROM ec_chitiettaikhoan" . $opening_year . " p
					WHERE p.deleted = 0
						AND SUBSTR(TRIM(p.sotaikhoan), 1, 4) = '" . $accounting_code . "' " . $sql_search3 . "

					UNION

					-- RECEIPT VOUCHER
					SELECT p.id,
						IFNULL(p.amount_converted, 0) AS receipt_amount,
						0 AS payment_amount
					FROM ec_receipt_voucher p
					WHERE p.deleted = 0
						AND p.receipt_type = 'cash'
						AND p.rv_status = '1' " . $sql_search . "

					UNION

					-- PAYMENT VOUCHER
					SELECT p.id,
						0 AS receipt_amount,
						IFNULL(p.amount, 0) AS payment_amount
					FROM ec_payment_voucher p
					WHERE p.deleted = 0
						AND p.hinhthucchi = 'cash'
						AND p.pv_status = '3' " . $sql_search . "

					UNION

					-- FROM TRANSFER VOUCHER
					SELECT p.id,
						0 AS receipt_amount,
						IFNULL(p.sotien, 0) AS payment_amount
					FROM ec_chuyentiennoibo p
					WHERE p.deleted = 0
						AND p.ghiso = 1
						AND p.tutienmat = 1 " . str_replace('p.com_location_id', 'p.tudiadiem_id', $sql_search) . "

					UNION

					-- TO TRANSFER VOUCHER
					SELECT CONCAT(p.id, '-INBOUND') AS id,
						IFNULL(p.sotien, 0) AS receipt_amount,
						0 AS payment_amount
					FROM ec_chuyentiennoibo p
					WHERE p.deleted = 0
						AND p.ghiso = 1
						AND p.dentienmat = 1 " . str_replace('p.com_location_id', 'p.dendiadiem_id', $sql_search) . "
				) AS tmp ";

		// if($GLOBALS['current_user']->user_name == 'hungnh'){
		// 	pr($sql);
		// }

		// Get voucher list
		$sql .= "
			UNION

			-- RECEIPT VOUCHER
			SELECT p.date_entered,
				DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR) AS posted_date,
				p.ngaychungtu AS voucher_date,
				p.name AS voucher_name,
				p.description,
				'EC_Receipt_Voucher' AS parent_type,
				p.id AS parent_id,
				IFNULL(p.amount_converted, 0) AS receipt_amount,
				'' AS payment_amount,
				'' AS remain_amount
			FROM ec_receipt_voucher p
			WHERE p.deleted = 0
				AND p.receipt_type = 'cash'
				AND p.rv_status = '1' " . $sql_search2 . "

			UNION

			-- PAYMENT VOUCHER
			SELECT p.date_entered,
				DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR) AS posted_date,
				p.ngaychungtu AS voucher_date,
				p.name AS voucher_name,
				p.description,
				'EC_Payment_Voucher' AS parent_type,
				p.id AS parent_id,
				'' AS receipt_amount,
				IFNULL(p.amount, 0) AS payment_amount,
				'' AS remain_amount
			FROM ec_payment_voucher p
			WHERE p.deleted = 0
				AND p.hinhthucchi = 'cash'
				AND p.pv_status = '3' " . $sql_search2 . "

			UNION

			-- FROM TRANSFER VOUCHER
			SELECT p.date_entered,
				DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR) AS posted_date,
				p.ngaychungtu AS voucher_date,
				p.name AS voucher_name,
				p.description,
				'EC_ChuyenTienNoiBo' AS parent_type,
				p.id AS parent_id,
				'' AS receipt_amount,
				IFNULL(p.sotien, 0) AS payment_amount,
				'' AS remain_amount
			FROM ec_chuyentiennoibo p
			WHERE p.deleted = 0
				AND p.ghiso = 1
				AND p.tutienmat = 1 " . str_replace('p.com_location_id', 'p.tudiadiem_id', $sql_search2) . "

			UNION

			-- TO TRANSFER VOUCHER
			SELECT p.date_entered,
				DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR) AS posted_date,
				p.ngaychungtu AS voucher_date,
				p.name AS voucher_name,
				p.description,
				'EC_ChuyenTienNoiBo' AS parent_type,
				CONCAT(p.id, '-INBOUND') AS parent_id,
				IFNULL(p.sotien, 0) AS receipt_amount,
				'' AS payment_amount,
				'' AS remain_amount
			FROM ec_chuyentiennoibo p
			WHERE p.deleted = 0
				AND p.ghiso = 1
				AND p.dentienmat = 1 " . str_replace('p.com_location_id', 'p.dendiadiem_id', $sql_search2) . "
			ORDER BY posted_date";

		$res = $db->query($sql);
		$html = '';
		$total_receipt = 0;
		$total_payment = 0;
		$total_remain = 0;

		while ($row = $db->fetchByAssoc($res)) {
			if ($row['parent_type'] == 'OPN') {
				$total_remain += $row['remain_amount'];
				$html .= '<tr>
					<td align="center">&nbsp;</td>
					<td align="center">&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td><strong>Số đầu kỳ</strong></td>
					<td align="right">&nbsp;</td>
					<td align="right">&nbsp;</td>
					<td align="right">' . format_number($row['remain_amount']) . '</td>
           	    </tr>';
			} else {
				$total_receipt += $row['receipt_amount'];
				$total_payment += $row['payment_amount'];
				$total_remain += ($row['receipt_amount'] - $row['payment_amount']);
				$html .= '<tr>
					<td align="center">' . date('d/m/Y', strtotime($row['posted_date'])) . '</td>
					<td align="center">' . date('d/m/Y', strtotime($row['voucher_date'])) . '</td>
					<td>' . (!empty($row['receipt_amount']) ? '<a href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . str_replace('-INBOUND', '', $row['parent_id']) . '" target="_blank">' . $row['voucher_name'] . '</a>' : '&nbsp;') . '</td>
					<td>' . (!empty($row['payment_amount']) ? '<a href="index.php?module=' . $row['parent_type'] . '&action=DetailView&record=' . str_replace('-INBOUND', '', $row['parent_id']) . '" target="_blank">' . $row['voucher_name'] . '</a>' : '&nbsp;') . '</td>
					<td>' . $row['description'] . '</td>
					<td align="right">' . (!empty($row['receipt_amount']) ? format_number($row['receipt_amount']) : '&nbsp;') . '</td>
					<td align="right">' . (!empty($row['payment_amount']) ? format_number($row['payment_amount']) : '&nbsp;') . '</td>
					<td align="right">' . format_number($total_remain) . '</td>
           	    </tr>';
			}
		}

		return array('html' => $html, 'total_receipt' => $total_receipt, 'total_payment' => $total_payment, 'total_remain' => $total_remain);
	}
}
