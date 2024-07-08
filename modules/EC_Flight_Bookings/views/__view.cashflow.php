<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewcashflow extends SugarView {
	function display() {
		$smartyCont = new Sugar_Smarty();
		$this->populateCont($smartyCont);
		$smartyCont->display('modules/EC_Receipt_Voucher/tpls/cash_flow.tpl');
	}

	function populateCont($smartyobj) {
		global $app_list_strings, $locale, $current_user;

		// Permission
		$dep_arr = is_admin($current_user) ? SecurityGroup::getAllSecurityGroups() : SecurityGroup::getUserSecurityGroups($current_user->id);
		if (is_array($dep_arr)) {
			$dep_arr = array_values($dep_arr);
		}

		if (isset($_POST['dep_id']) && !empty($_POST['dep_id'])) {
			$dep_id = $_POST['dep_id'];
		} else {
			$dep_id = $dep_arr[0]['id'];
		}
		// End permission

		$dep_info 	= myGetDepartmentInfo($dep_id);
		$vj_agent 	= myGetStringBetween($dep_info['code_book'], '[VJA]', '[/VJA]');
		$vj_agent 	= explode('|', $vj_agent);
		$vj_agent_id 	= $vj_agent[0];
		$vj_agent_pwd 	= $vj_agent[1];

		$bl_agent 	= myGetStringBetween($dep_info['code_book'], '[JET]', '[/JET]');
		$bl_agent 	= explode('|', $bl_agent);
		$bl_agent_id 	= $bl_agent[0];
		$bl_agent_pwd 	= $bl_agent[1];

		$sql_search = "";
		if (isset($_POST['from_date']) && !empty($_POST['from_date'])) {
			$sql_search .= " AND DATE(p.ngayhachtoan) >= '" . date('Y-01-01', strtotime($_POST['from_date'])) . "' ";
			$post_from_date = $_POST['from_date'];
		} else {
			$sql_search .= " AND DATE(p.ngayhachtoan) >= '" . date('Y-01-01') . "' ";
			$post_from_date = date('d-m-Y');
		}

		if (isset($_POST['to_date']) && !empty($_POST['to_date'])) {
			$sql_search .= " AND DATE(p.ngayhachtoan) <= '" . date('Y-m-d', strtotime($_POST['to_date'])) . "' ";
			$post_to_date = $_POST['to_date'];
		} else {
			$sql_search .= " AND DATE(p.ngayhachtoan) <= '" . date('Y-m-d') . "' ";
			$post_to_date = date('d-m-Y');
		}
		$report_year = date('Y', strtotime($post_from_date));



		$to_date 	= ($post_to_date == date('d-m-Y')) ? date('d-m-Y', strtotime("-1 day")) : $post_to_date;
		$html 	= $this->getDataCashFlow($to_date);

		if (!empty($html) && !isset($_POST['realtime'])) {
			$smartyobj->assign('DATA', $html);
		}
		else {
			$html 		= '';
			$data 		= $this->getBankAccountList($report_year, $post_from_date, $post_to_date, $sql_search, $dep_id);
			$i 			= $data['total_rows'] + 1;
			$total_amount = 0;
			$total_amount += $data['total_amount'];

			// Đưa số tiền trong các tài khoản của nhà cung cấp vào báo cáo dòng tiền
			$total_amt_supplier_arr = $this->getSupplierTotalDebt($report_year, $post_from_date, $post_to_date);
			foreach ($total_amt_supplier_arr['minus'] as $key => $detail_arr) {
				$html .= '<tr>
					<td align="center">' . ($i++) . '</td>
					<td align="left">' . $key . '</td>
					<td align="left">' . $detail_arr['name'] . '</td>
					<td align="left" class="bank_account"></td>
					<td align="right"><span class="total-credit">' . format_number(abs($detail_arr['amount'])) . '</span></td>
				</tr>';
				$total_amount += abs($detail_arr['amount']);
			}

			$total_cash = $this->getTotalCash($report_year, $sql_search, $dep_id);
			$total_amount += $total_cash;
			$html .= '<tr>
				<td align="center">' . ($i++) . '</td>
				<td align="left">TM</td>
				<td align="left">Tiền mặt</td>
				<td align="left" class="bank_account"></td>
				<td align="right"><span class="total-credit">' . format_number($total_cash) . '</span></td>
			</tr>';

			// Đưa số tổng của công nợ phải thu qua báo cáo dòng tiền
			$total_debt = $this->getAgentList($report_year, '131', $post_from_date, $post_to_date);
			if ($total_debt > 0) {
				$html .= '<tr>
					<td align="center">' . ($i++) . '</td>
					<td align="left"></td>
					<td align="left">Công nợ phải thu</td>
					<td align="left" class="bank_account"></td>
					<td align="right"><span class="total-credit">' . format_number($total_debt) . '</span></td>
				</tr>';
				$total_amount += $total_debt;
			}

			// Tổng hiện có
			$html .= '<tr class="footer-tr">
				<td align="center" colspan="3">Tổng tiền</td>
				<td align="right" class="text-end"><span id="total-amount">' . format_number($total_amount) . '</span></td>
			</tr>';

			foreach ($data['html_minus'] as $key => $value) {
				$html .= '<tr>
					<td align="center">' . ($i++) . '</td>
					<td align="left">' . $key . '</td>
					<td align="left">' . $value['name'] . '</td>
					<td align="left" class="bank_account">' . $value['bank'] . '</td>
					<td align="right"><span class="total-credit">' . format_number($value['amount']) . '</span></td>
				</tr>';
				$total_amount += $value['amount'];
			}

			// Nếu số tiền NCC > 0
			foreach ($total_amt_supplier_arr['plus'] as $key => $detail_arr) {
				$html .= '<tr>
					<td align="center">' . ($i++) . '</td>
					<td align="left">' . $key . '</td>
					<td align="left">' . $detail_arr['name'] . '</td>
					<td align="left" class="bank_account"></td>
					<td align="right"><span class="total-credit">' . format_number('-' . $detail_arr['amount']) . '</span></td>
				</tr>';
				$total_amount -= $detail_arr['amount'];
			}

			// Nếu công nợ phải thu < 0
			if ($total_debt < 0) {
				$html .= '<tr>
					<td align="center">' . ($i++) . '</td>
					<td align="left"></td>
					<td align="left">Công nợ phải thu</td>
					<td align="left" class="bank_account"></td>
					<td align="right"><span class="total-credit">' . format_number($total_debt) . '</span></td>
				</tr>';
				$total_amount += $total_debt;
			}

			// Chưa xuất vé
			$not_exported = $this->getNotExportedTicket($post_to_date);
			$html .= '<tr>
					<td align="center">' . ($i++) . '</td>
					<td align="left"></td>
					<td align="left">Chưa xuất vé</td>
					<td align="left" class="bank_account"></td>
					<td align="right"><span class="total-credit">' . (($not_exported > 0) ? '-' . format_number($not_exported) : 0) . '</span></td>
				</tr>';
			$total_amount -= $not_exported;

			// Tiền tạm ứng
			$total_advance = $this->getAdvanceAmt($post_to_date);
			if ($total_advance > 0) {
				$html_advance = '<tr>
					<td align="center">' . ($i++) . '</td>
					<td align="left"></td>
					<td align="left">Nhân viên tạm ứng</td>
					<td align="left" class="bank_account getAdvanceAmt"></td>
					<td align="right"><span class="total-advance">' . format_number($total_advance) . '</span></td>
				</tr>';
				$total_amount += $total_advance;
			}

			// Tiền nợ khách, khách được hoàn vé nhưng tiền chưa trả cho khách
			if (strtotime($post_from_date) >= strtotime('2021-01-01')) {
				$return_amt = $this->getReturnAmount($post_to_date);
				$html_debt_client = '<tr>
					<td align="center">' . ($i++) . '</td>
					<td align="left"></td>
					<td align="left">Tiền nợ khách</td>
					<td align="left" class="bank_account getReturnAmount"></td>
					<td align="right">' . (($return_amt > 0) ? '-' . format_number($return_amt) : 0) . '</td>
				</tr>';
				$total_amount -= $return_amt;
			}

			// Tổng dòng tiền
			$html .= '<tr class="footer-tr">
				<td align="center" colspan="4">Dòng tiền</td>
				<td align="right" class="text-end"><span id="total-amount">' . format_number($total_amount) . '</span></td>
			</tr>';

			// Tiền quỹ dự phòng
			// $html .= $this->getReserveFund($report_year, $post_from_date, $post_to_date, $sql_search, $i);

			// Tiền thẻ tín dụng (Không tính vào dòng tiền)
			$html_credit = $data['html_credit'];


			// calculate total supplier debt
			// $supplier_arr = array(
			// 	'e49e5fb8-d4d5-effd-c0bc-525b957b0897' => array('code' => 'LLD', 'name' => 'Đại Lý Liên Lục Địa'),
			// 	'60a33f75-edb6-07dd-5095-52a5fb4be0a1' => array('code' => 'HNH', 'name' => 'Đại Lý Hồng Ngọc Hà'),
			// 	'8871275a-61cc-69dc-b430-55a0929290ef' => array('code' => 'VCN', 'name' => 'Cty TNHH TMDV Việt Cường Nhân'),
			// );

			// foreach($supplier_arr as $sukey => $suval){
			// 	$total_debt = $this->getTotalPayDebt($sukey, $report_year, $sql_search, $dep_id);
			// 	$html .= '<tr>
			// 		<td align="center">'.($i++).'</td>
			// 		<td align="left">'.$suval['code'].'</td>
			// 		<td align="left">'.$suval['name'].'</td>
			// 		<td align="right"><span class="total-debt">'.format_number(abs($total_debt)).'</span></td>
			// 	</tr>';
			// 	$total_amount += $total_debt;
			// }

			// // calculate total paid booking amount
			// $total_paid_booking_amt = $this->getTotalPaidBookingAmount($dep_id);
			// $html .= '<tr>
			// 	<td align="center">'.($i++).'</td>
			// 	<td align="left">CXV</td>
			// 	<td align="left">Chưa xuất vé</td>
			// 	<td align="right"><span class="total-debt">'.format_number(abs($total_paid_booking_amt)).'</span></td>
			// </tr>';

			// $html .= '<tr>
			// 	<td align="center" colspan="3" style="font-weight:bold; background:#eee;">Tổng cộng</td>
			// 	<td align="right" style="font-weight:bold; background:#eee;"><span id="total-amount-final">'.format_number($total_amount).'</span></td>
			// </tr>';

			$smartyobj->assign('DATA', $data['html'] . $html . $html_advance . $html_debt_client . $html_credit);
		}

		// $sep = get_number_seperators();
		$sep = my_get_number_separators();
		$smartyobj->assign('DEPARTMENT', (count($dep_arr) > 1 ? $this->makeHtmlOption($dep_arr, $dep_id) : ''));
		$smartyobj->assign('GRP_SEPERATOR', $sep[0]);
		$smartyobj->assign('DEC_SEPERATOR', $sep[1]);
		$smartyobj->assign('SIG_DIGITS', $locale->getPrecision());
		$smartyobj->assign('VJ_AGENT_ID', $vj_agent_id);
		$smartyobj->assign('VJ_AGENT_PWD', $vj_agent_pwd);
		$smartyobj->assign('BL_AGENT_ID', $bl_agent_id);
		$smartyobj->assign('BL_AGENT_PWD', $bl_agent_pwd);
		$smartyobj->assign('POST_FROM_DATE', $post_from_date);
		$smartyobj->assign('POST_TO_DATE', $post_to_date);
	}

	// Lấy dữ liệu tính toán trước từ bảng ec_cashflow
	function getDataCashFlow($to_date) {
		global $db;

		$to_date = date('Y-m-d', strtotime($to_date));
		$sql = "SELECT id, account_number, account_name, amount
				FROM ec_cashflow
				WHERE deleted = 0 AND report_date = '" . $to_date . "'";
		$res = $db->query($sql);
		$i = 1;
		$total_plus = $total_minus = 0;
		$html_plus = $html_minus = "";
		while ($row = $db->fetchByAssoc($res)) {
			if ($row['account_number'] == "NOT_EXPORTED" || $row['account_number'] == "REFUND") {
				$content = '<tr>
								<td align="center">' . ($i++) . '</td>
								<td align="left">' . $row['account_number'] . '</td>
								<td align="left">' . $row['account_name'] . '</td>
								<td align="left" class="bank_account"></td>
								<td align="right"><span class="total-credit">' . (($row['amount'] > 0) ? '-' . format_number($row['amount']) : 0) . '</span></td>
							</tr>';
				$html_minus .= $content;
				$total_minus -= $row['amount'];
				continue;
			}

			$content = '<tr>
							<td align="center">' . ($i++) . '</td>
							<td align="left">' . $row['account_number'] . '</td>
							<td align="left">' . $row['account_name'] . '</td>
							<td align="left" class="bank_account"></td>
							<td align="right"><span class="total-credit">' . format_number($row['amount']) . '</span></td>
						</tr>';

			if ($row['amount'] >= 0 || $row['account_number'] == "CASH") {
				$html_plus .= $content;
				$total_plus += $row['amount'];
			} else {
				$html_minus .= $content;
				$total_minus += $row['amount'];
			}
		}

		if ($i == 1) return "";

		$html = $html_plus;
		$html .= '<tr class="footer-tr">
					<td align="left" colspan="4">Tổng tiền</td>
					<td align="right" class="text-end"><span id="total-amount">' . format_number($total_plus) . '</span></td>
				</tr>';
		$html .= $html_minus;
		$html .= '<tr class="footer-tr">
					<td align="left" colspan="4">Dòng tiền</td>
					<td align="right" class="text-end"><span id="total-amount">' . format_number($total_plus + $total_minus) . '</span></td>
				</tr>';
		return $html;
	}

	function getBankAccountList($report_year, $post_from_date, $post_to_date, $sql_search, $dep_id) {
		global $db, $current_user;

		$sql = "SELECT SUM(IFNULL(tmp.thutien,0)) - SUM(IFNULL(tmp.chitien,0)) AS sotien,
					tmp.tknganhang_id,
					tmp.tknganhang,
					tmp.sotaikhoan,
					tmp.tennganhang
				FROM (
					-- OPENING AMOUNT
					SELECT p.id,
						(IFNULL(p.dunodau,0) - IFNULL(p.ducodau,0)) AS thutien,
						0 AS chitien,
						p.parent_id AS tknganhang_id,
						(SELECT ba.name FROM ec_bank_account ba WHERE ba.id=p.parent_id AND ba.deleted=0 LIMIT 1) AS tknganhang,
						(SELECT ba.account_number FROM ec_bank_account ba WHERE ba.id=p.parent_id AND ba.deleted=0 LIMIT 1) AS sotaikhoan,
						,(
							SELECT b.name 
							FROM ec_banks b 
							LEFT JOIN ec_bank_account t ON b.id = t.bank_id 
							WHERE t.deleted=0 
							AND t.id = p.parent_id
							LIMIT 1
						) AS tennganhang,
						(SELECT ba.unfollow FROM ec_bank_account ba WHERE ba.id=p.parent_id AND ba.deleted=0 LIMIT 1) AS ngungtheodoi,
						(SELECT ba.assigned_user_id FROM ec_bank_account ba WHERE ba.id=p.parent_id AND ba.deleted=0 LIMIT 1) AS assigned_user_id
					FROM ec_chitiettaikhoan" . $report_year . " p
					WHERE p.parent_type 'EC_Bank_Account'
						AND p.parent_id IS NOT NULL
						AND p.deleted=0

					-- RECEIPT VOUCHER
					UNION
					SELECT p.id,
						p.amount_converted AS thutien,
						0 AS chitien,
						p.tknganhang_id,
						(SELECT ba.name FROM ec_bank_account ba WHERE ba.id=p.tknganhang_id AND ba.deleted=0 LIMIT 1) AS tknganhang,
						(SELECT ba.account_number FROM ec_bank_account ba WHERE ba.id=p.tknganhang_id AND ba.deleted=0 LIMIT 1) AS sotaikhoan,
						,(
							SELECT b.name 
							FROM ec_banks b 
							LEFT JOIN ec_bank_account t ON b.id = t.bank_id
							WHERE t.id=p.tknganhang_id
							AND t.deleted=0 
							LIMIT 1
						) AS tennganhang,
						(SELECT ba.unfollow FROM ec_bank_account ba WHERE ba.id=p.tknganhang_id AND ba.deleted=0 LIMIT 1) AS ngungtheodoi,
						(SELECT ba.assigned_user_id FROM ec_bank_account ba WHERE ba.id=p.tknganhang_id AND ba.deleted=0 LIMIT 1) AS assigned_user_id
					FROM ec_receipt_voucher p
					WHERE p.receipt_type='credit_transfer' 
						AND p.amount IS NOT NULL 
						AND p.rv_status='1' 
						AND p.is_margin=0 
						AND p.deleted=0 
						" . $sql_search . "
					
					-- PAYMENT VOUCHER
					UNION
					SELECT p.id,
						0 AS thutien,
						p.amount AS chitien,
						p.tknganhang_id,
						(SELECT ba.name FROM ec_bank_account ba WHERE ba.id=p.tknganhang_id AND ba.deleted=0 LIMIT 1) AS tknganhang,
						(SELECT ba.account_number FROM ec_bank_account ba WHERE ba.id=p.tknganhang_id AND ba.deleted=0 LIMIT 1) AS sotaikhoan,
						,(
							SELECT b.name 
							FROM ec_banks b 
							LEFT JOIN ec_bank_account t ON b.id = t.bank_id 
							WHERE t.deleted=0 
							AND t.id=p.tknganhang_id
							LIMIT 1
						) AS tennganhang,
						(SELECT ba.unfollow FROM ec_bank_account ba WHERE ba.id=p.tknganhang_id AND ba.deleted=0 LIMIT 1) AS ngungtheodoi,
						(SELECT ba.assigned_user_id FROM ec_bank_account ba WHERE ba.id=p.tknganhang_id AND ba.deleted=0 LIMIT 1) AS assigned_user_id
					FROM ec_payment_voucher p
					WHERE p.hinhthucchi = 'credit_transfer' 
						AND p.amount IS NOT NULL
						AND p.pv_status = '3'
						AND p.deleted=0 
						" . $sql_search . "
					
					-- TRANSFER FROM
					UNION
					SELECT p.id,
						0 AS thutien,
						IFNULL(p.sotien,0) AS chitien,
						p.tutknganhang_id AS tknganhang_id,
						(SELECT ba.name FROM ec_bank_account ba WHERE ba.id=p.tutknganhang_id AND ba.deleted=0 LIMIT 1) AS tknganhang,
						(SELECT ba.account_number FROM ec_bank_account ba WHERE ba.id=p.tutknganhang_id AND ba.deleted=0 LIMIT 1) AS sotaikhoan,
						,(
							SELECT b.name 
							FROM ec_banks b 
							LEFT JOIN ec_bank_account t ON b.id = t.bank_id 
							WHERE t.id=p.tutknganhang_id
							AND t.deleted=0 
							LIMIT 1
						) AS tennganhang,
						(SELECT ba.unfollow FROM ec_bank_account ba WHERE ba.id=p.tutknganhang_id AND ba.deleted=0 LIMIT 1) AS ngungtheodoi,
						(SELECT ba.assigned_user_id FROM ec_bank_account ba WHERE ba.id=p.tutknganhang_id AND ba.deleted=0 LIMIT 1) AS assigned_user_id
					FROM ec_chuyentiennoibo p
					WHERE p.ghiso=1 
						AND p.tutienmat=0 
						AND p.deleted=0 
						" . $sql_search . "
					
					-- TRANSFER TO
					UNION
					SELECT p.id,
						IFNULL(p.sotien,0) AS thutien,
						0 AS chitien,
						p.dentknganhang_id AS tknganhang_id,
						(SELECT ba.name FROM ec_bank_account ba WHERE ba.id=p.dentknganhang_id AND ba.deleted=0 LIMIT 1) AS tknganhang,
						(SELECT ba.sotaikhoan FROM ec_bank_account ba WHERE ba.id=p.dentknganhang_id AND ba.deleted=0 LIMIT 1) AS sotaikhoan,
						,(
							SELECT b.name 
							FROM ec_banks b 
							LEFT JOIN ec_bank_account t ON b.id = t.bank_id 
							WHERE t.id=p.dentknganhang_id 
							AND t.deleted=0 
							LIMIT 1
						) AS tennganhang,
						(SELECT ba.ngungtheodoi FROM ec_bank_account ba WHERE ba.id=p.dentknganhang_id AND ba.deleted=0 LIMIT 1) AS ngungtheodoi,
						(SELECT ba.assigned_user_id FROM ec_bank_account ba WHERE ba.id=p.dentknganhang_id AND ba.deleted=0 LIMIT 1) AS assigned_user_id
					FROM ec_chuyentiennoibo p
					WHERE p.ghiso=1 
						AND p.dentienmat=0
						AND p.deleted=0 
						" . $sql_search . "
				) AS tmp
				WHERE tmp.ngungtheodoi=0
					-- AND u.department_id='" . $dep_id . "'
					AND tmp.tknganhang_id NOT IN (
						'a56c866c-8476-949d-96b5-60cb1818fc55',
						'2cd3be12-1ddf-6bc9-158e-5ff97562f721' 
					)
				GROUP BY tmp.tknganhang_id 
				ORDER BY CONVERT(tknganhang USING UTF8) COLLATE utf8_unicode_ci";

		$res 	= $db->query($sql);
		$html 	= '';
		$i 		= 0;

		// if($current_user->user_name == 'hungnh'){
		// 	pr($sql);
		// };


		$total_amount = 0;
		$credit_card = ['000000000111789', '651704060028565-visa'];
		$html_credit = '';
		while ($row = $db->fetchByAssoc($res)) {
			if($row['sotaikhoan'] == "0421000448658") continue;

			if ($row['sotien'] > 0) {
				if(!in_array($row['sotaikhoan'], $credit_card)) {
					$html .= '<tr>
						<td align="center">' . ($i + 1) . '</td>
						<td align="left">' . $row['sotaikhoan'] . '</td>
						<td align="left">' . $row['tknganhang'] . '</td>
						<td align="left" class="banks_account">'.$row['tennganhang'].'</td>
						<td align="right"><span class="total-credit">' . format_number($row['sotien']) . '</span></td>
					</tr>';
					$total_amount += $row['sotien'];
					$i++;
				}
				else {
					$html_credit .= '<tr>
						<td align="center" style="background-color:#fdd7d7"></td>
						<td align="left" style="background-color:#fdd7d7">' . $row['sotaikhoan'] . '</td>
						<td align="left" style="background-color:#fdd7d7">' . $row['tknganhang'] . '</td>
						<td align="left" class="banks_account" style="background-color:#fdd7d7">'.$row['tennganhang'].'</td>
						<td align="right" style="background-color:#fdd7d7"><span class="total-credit">' . format_number($row['sotien']) . '</span></td>
					</tr>';
				}
			}
			else {
				$html_minus[$row['sotaikhoan']]['name'] = $row['tknganhang'];
				$html_minus[$row['sotaikhoan']]['amount'] = $row['sotien'];
				$html_minus[$row['sotaikhoan']]['bank'] = $row['tennganhang'];
			}
		}

		return array('total_amount' => $total_amount, 'total_rows' => $i, 'html' => $html, 'html_minus' => $html_minus, 'html_credit' => $html_credit);
	}

	function getTotalCash($report_year, $sql_search, $dep_id) {
		global $db;
		$total_amount = 0;
		// cả nam phương và tralvelpass
		$location_np = $this->getLocationByDep('8df43570-09de-d2b3-b2fd-506eca7522f7');
		$location_tp = $this->getLocationByDep('48840c01-3a4f-c430-f703-56f32c7cd8a4');
		$sql_search .= " AND p.com_location_id IN ('" . implode("','", $location_np) . '\',\'' . implode("','", $location_tp) . "') ";

		$sql = "SELECT SUM(IFNULL(tmp.thutien,0)) - SUM(IFNULL(tmp.chitien,0))
				FROM (
					-- OPENING AMOUNT
					SELECT p.id,
						(IFNULL(p.dunodau,0) - IFNULL(p.ducodau,0)) AS thutien,
						0 AS chitien,
						p.assigned_user_id
					FROM ec_chitiettaikhoan" . $report_year . " p
					WHERE SUBSTRING(p.sotaikhoan, 1, 4) = '1111'
						AND p.deleted=0 
						AND p.location_id IN ('" . implode("','", $location_np) . '\',\'' . implode("','", $location_tp) . "')
					
					-- RECEIPT VOUCHER
					UNION
					SELECT p.id,
						p.amount_converted AS thutien,
						0 AS chitien,
						p.assigned_user_id
					FROM ec_receipt_voucher p
					WHERE p.receipt_type='cash' 
						AND p.amount_converted IS NOT NULL 
						AND p.rv_status='1' 
						AND p.is_margin=0 
						AND p.deleted=0 
						" . $sql_search . "
					 
					-- PAYMENT VOUCHER
					UNION
					SELECT p.id,
						0 AS thutien,
						p.amount AS chitien,
						p.assigned_user_id
					FROM ec_payment_voucher p
					WHERE p.hinhthucchi='cash' 
						AND p.amount IS NOT NULL 
						AND p.pv_status='3'
						AND p.deleted=0 
						" . $sql_search . "
					 
					-- TRANSFER FROM
					UNION
					SELECT p.id,
						0 AS thutien,
						p.sotien AS chitien,
						p.assigned_user_id
					FROM ec_chuyentiennoibo p
					WHERE p.ghiso=1 
						AND p.tutienmat=1 
						AND p.deleted=0 
						" . str_replace('p.com_location_id', 'p.tudiadiem_id', $sql_search) . "
					
					 -- TRANSFER TO
					UNION
					SELECT p.id,
						p.sotien AS thutien,
						0 AS chitien,
						p.assigned_user_id
					FROM ec_chuyentiennoibo p
					WHERE p.ghiso=1 
						AND p.dentienmat=1 
						AND p.deleted=0 
						" . str_replace('p.com_location_id', 'p.dendiadiem_id', $sql_search) . "
				) AS tmp";

		$total_amount += $db->getOne($sql);
		return $total_amount;
	}

	function getTotalPayDebt($nhacungcap_id, $report_year, $sql_search, $dep_id) {
		global $db;
		$tk_congno = '144'; // tài khoản công nợ phải trả
		$loaichi_id = "'3361ac47-2254-701a-55d1-508abcb90f50'"; // công nợ phải trả
		$loaichi_id .= ",'55730166-4f7f-f8fc-78a6-529f673b431e'"; // ký quỹ jetstar
		$loaichi_id .= ",'6dff0213-1547-1ae9-b433-529f6736aef4'"; // ký quỹ vietjet

		//$year = date('Y', strtotime($post_from_date)) == date('Y') ? '' : date('Y', strtotime($post_from_date));
		$total_debt = 0;
		$sql = "SELECT SUM(IFNULL(tmp.tiendatra,0)) - SUM(IFNULL(tmp.tienno,0)) 
		 		FROM (
		 			-- OPENING AMOUNT
			 		SELECT p.id,
						0 AS tienno,
						(IFNULL(p.dunodau,0)-IFNULL(p.ducodau,0)) AS tiendatra,
			 			p.assigned_user_id
					FROM ec_chitiettaikhoan" . $report_year . " p
					WHERE p.parent_id = '" . $nhacungcap_id . "' 
						AND p.parent_type = 'Accounts' 
						AND p.sotaikhoan = '" . $tk_congno . "'
						AND p.deleted = 0 
					
					-- BOOKING
					UNION
					SELECT p.id
						(
							SUM(IFNULL(d.total_bought_price,0)) + 
							IFNULL((
								SELECT 
									IF(p.flight_type = '0',
										SUM(IFNULL(psg.luggage_purchase,0)) + SUM(IFNULL(psg.luggage_purchase_inbound,0)),
										SUM(IFNULL(psg.luggage_purchase,0))
									)
								FROM ec_booking_passengers psg
								WHERE psg.booking_id=p.id AND psg.deleted=0
							), 0)	
						) AS tienno,
						0 AS tiendatra,
						p.assigned_user_id
					FROM ec_booking_details d
						LEFT JOIN ec_flight_bookings p ON d.booking_id=p.id AND p.deleted=0
					WHERE p.is_ticket_exported = 1
						AND p.booking_status IN ('7','8')
						AND p.date_ticket_issue IS NOT NULL
						AND d.supplier_id = '" . $nhacungcap_id . "' " . str_replace('p.ngayhachtoan', 'p.date_ticket_issue', $sql_search) . "
						AND d.deleted = 0
					GROUP BY d.booking_id
					
					-- RECEIPT VOUCHER
					UNION
					SELECT p.id,
						( 
							CASE 
								WHEN (p.supplier2_id='" . $nhacungcap_id . "' AND IFNULL(p.bought_amount2,0) > 0) 
									THEN (IFNULL(p.bought_amount2, 0)) 
								WHEN (p.supplier3_id='" . $nhacungcap_id . "' AND IFNULL(p.bought_amount3,0) > 0)
									THEN (IFNULL(p.bought_amount3, 0)) 
								ELSE (
									IFNULL(p.bought_amount, 0)
								)
							END
						) AS tienno,
						0 AS tiendatra,
						p.assigned_user_id
					FROM ec_receipt_voucher p
					WHERE p.is_margin = 0
						AND p.loai_thu IN ('4','5')
						AND (
							(p.account_id_c = '" . $nhacungcap_id . "' AND IFNULL(p.bought_amount, 0) > 0) 
							OR (p.supplier2_id = '" . $nhacungcap_id . "' AND IFNULL(p.bought_amount2, 0) > 0) 
							OR (p.supplier3_id = '" . $nhacungcap_id . "' AND IFNULL(p.bought_amount3, 0) > 0)
						)
						AND p.deleted=0
						" . $sql_search . "
					
					-- TICKET REFUND
					UNION
					SELECT p.id,
						-SUM(IFNULL(c.sotienhang,0)) AS tienno,
						0 AS tiendatra,
						p.assigned_user_id
					FROM ec_chitiethoanve c
						LEFT JOIN ec_hoanve p ON c.hoanve_id=p.id AND p.deleted=0
					WHERE c.nhacc_id = '" . $nhacungcap_id . "'
						AND c.dahoan = 1
						AND c.deleted=0
						AND p.tinhtrang='1'
						" . $sql_search . "
					GROUP BY c.hoanve_id
					
					-- PAYMENT VOUCHER
					UNION
					SELECT p.id,
						0 AS tienno,
						IFNULL(p.amount,0) AS tiendatra,
						p.assigned_user_id
					FROM ec_payment_voucher p
					WHERE p.supplier_id='" . $nhacungcap_id . "'
						AND p.pv_status = '3' 
						AND p.ec_payment_types_id_c IN (" . $loaichi_id . ") 
						AND p.deleted = 0 
						" . $sql_search . "
				) AS tmp
				LEFT JOIN users u ON tmp.assigned_user_id=u.id AND u.deleted=0
			  	WHERE u.department_id = '" . $dep_id . "' ";

		$total_debt += $db->getOne($sql);
		return $total_debt;
	}

	/**
	 * Get total amount from paid booking but not completed
	 */
	function getTotalPaidBookingAmount($dep_id) {
		global $db;
		$total_amount = 0;
		$sql = "SELECT SUM(IFNULL((
						  SELECT SUM(IFNULL(d.total_bought_price,0)) 
						  FROM ec_booking_details d 
						  WHERE d.booking_id=p.id AND d.deleted=0 
						),0) + IFNULL((
						  SELECT IF(p.flight_type='0'
						  		  	,(SUM(IFNULL(p.luggage_purchase,0)) + SUM(IFNULL(p.luggage_purchase_inbound,0)))
									,SUM(IFNULL(p.luggage_purchase,0)))
						  FROM ec_booking_passengers p
						  WHERE p.booking_id=p.id AND p.deleted=0
						),0))
				FROM ec_flight_bookings p
					LEFT JOIN users u ON p.assigned_user_id = u.id AND u.deleted = 0
				WHERE p.is_paid = 1 
					AND p.booking_status IN ('1','6','2','3') 
					AND p.deleted=0
					AND u.department_id='" . $dep_id . "' ";

		$total_amount += $db->getOne($sql);
		return $total_amount;
	}

	// Make html option
	function makeHtmlOption($rows, $select_val = '') {
		$html = '';
		foreach ($rows as $row) {
			$selected = ($row['id'] == $select_val) ? 'selected="selected"' : '';
			$html .= '<option ' . $selected . ' value="' . $row['id'] . '">' . $row['name'] . '</option>';
		}
		return $html;
	}

	// Get locations by department
	function getLocationByDep($dep_id) {
		global $db;
		$arr = array();

		$sql = "SELECT id
				FROM ec_location
				WHERE company_id = '" . $dep_id . "' AND deleted = 0";

		$res = $db->query($sql);
		while ($row = $db->fetchByAssoc($res)) {
			$arr[] = $row['id'];
		}

		return $arr;
	}

	function getSupplierTotalDebt($opening_year, $post_fdate, $post_tdate) {
		global $current_user;
		$sql = "SELECT a.ticker_symbol AS supplier_code,
					a.name AS supplier_name,
					SUM(IFNULL(tmp.debt_amount, 0)) - SUM(IFNULL(tmp.pay_amount, 0)) AS total_debt
				FROM (
					-- START TERM
					SELECT p.id,
						p.parent_id AS supplier_id,
						SUM(IFNULL(p.dunodau, 0) - IFNULL(p.ducodau, 0)) AS debt_amount,
						0 AS pay_amount,
						p.sotaikhoan AS accounting_code
					FROM ec_chitiettaikhoan" . $opening_year . " p
					WHERE p.parent_type = 'Accounts'
						AND p.sotaikhoan IN ('144','331')
						AND p.parent_id IS NOT NULL
						AND p.deleted = 0
					GROUP BY p.parent_id

					-- BOOKING DETAILS
					UNION
					SELECT d.id,
						d.supplier_id,
						SUM(IFNULL(d.total_bought_price, 0)) AS debt_amount,
						0 AS pay_amount,
						'' AS accounting_code
					FROM ec_booking_details d
						LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
					WHERE p.is_ticket_exported = 1
						AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
						AND p.booking_status IN ('7', '8')
						AND d.deleted = 0
						AND d.total_bought_price > 0
						AND d.supplier_id IS NOT NULL
					GROUP BY d.supplier_id

					-- BOOKING PAXS OUTBOUND
					UNION
					SELECT CONCAT(d.id, '-OUTBOUND') AS id,
						d.supplier_id,
						SUM(IFNULL(d.luggage_purchase, 0)) AS debt_amount,
						0 AS pay_amount,
						'' AS accounting_code
					FROM ec_booking_passengers d
						LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
					WHERE p.is_ticket_exported = 1
						AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
						AND p.booking_status IN ('7', '8')
						AND d.deleted = 0
						AND d.luggage_price > 0
						AND d.luggage_purchase > 0
						AND d.supplier_id IS NOT NULL
						AND d.add_type IS NULL
					GROUP BY d.supplier_id

					-- BOOKING PAXS INBOUND
					UNION
					SELECT CONCAT(d.id, '-INBOUND') AS id,
						d.supplier_inbound_id AS supplier_id,
						SUM(IFNULL(d.luggage_purchase_inbound, 0)) AS debt_amount,
						0 AS pay_amount,
						'' AS accounting_code
					FROM ec_booking_passengers d
						LEFT JOIN ec_flight_bookings p ON d.booking_id = p.id AND p.deleted = 0
					WHERE p.is_ticket_exported = 1
						AND p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
						AND p.booking_status IN ('7', '8')
						AND d.deleted = 0
						AND d.luggage_price_inbound > 0
						AND d.luggage_purchase_inbound > 0
						AND d.supplier_inbound_id IS NOT NULL
						AND d.add_type IS NULL
					GROUP BY d.supplier_inbound_id

					-- SUPPLIER 1
					UNION
					SELECT CONCAT(p.id, '-SUPPLIER1') AS id,
						p.supplier_id,
						IFNULL(p.bought_amount, 0) AS debt_amount,
						0 AS pay_amount,
						'' AS accounting_code
					FROM ec_receipt_voucher p
					WHERE DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
						AND p.loai_thu IN ('4', '5')
						AND p.deleted = 0
						AND p.supplier_id IS NOT NULL
						AND p.bought_amount IS NOT NULL
						AND p.supplier_id IS NOT NULL

					-- SUPPLIER 2
					UNION
					SELECT CONCAT(p.id, '-SUPPLIER2') AS id,
						p.supplier2_id AS supplier_id,
						IFNULL(p.bought_amount2, 0) AS debt_amount,
						0 AS pay_amount,
						'' AS accounting_code
					FROM ec_receipt_voucher p
					WHERE DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
						AND p.loai_thu IN ('4', '5')
						AND p.deleted = 0
						AND p.supplier2_id IS NOT NULL
						AND p.bought_amount2 IS NOT NULL
						AND p.supplier2_id IS NOT NULL

					-- SUPPLIER 3
					UNION
					SELECT CONCAT(p.id, '-SUPPLIER3') AS id,
						p.supplier3_id AS supplier_id,
						IFNULL(p.bought_amount3, 0) AS debt_amount,
						0 AS pay_amount,
						'' AS accounting_code
					FROM ec_receipt_voucher p
					WHERE DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
						AND p.loai_thu IN ('4', '5')
						AND p.deleted = 0
						AND p.supplier3_id IS NOT NULL
						AND p.bought_amount3 IS NOT NULL
						AND p.supplier3_id IS NOT NULL

					-- TICKET REFUND
					UNION
					SELECT p.id,
						c.nhacc_id AS supplier_id,
						-SUM(IFNULL(c.sotienhang, 0)) AS debt_amount,
						0 AS pay_amount,
						'' AS accounting_code
					FROM ec_chitiethoanve c
						LEFT JOIN ec_hoanve p ON c.hoanve_id = p.id AND p.deleted = 0
					WHERE c.dahoan = 1
						AND p.tinhtrang = '1'
						AND p.ngayhachtoan >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND p.ngayhachtoan <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
						AND c.sotienhang > 0
						AND c.nhacc_id IS NOT NULL
						AND c.deleted = 0
					GROUP BY c.nhacc_id

					-- PAYMENT VOUCHER
					UNION
					SELECT p.id,
						p.supplier_id,
						0 AS debt_amount,
						IFNULL(p.amount, 0) AS pay_amount,
						'' AS accounting_code
					FROM ec_payment_voucher p
					WHERE DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND DATE(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR)) <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
						AND p.pv_status = '3'
						AND p.deleted = 0
						AND p.supplier_id IS NOT NULL
				) AS tmp
					LEFT JOIN accounts a ON tmp.supplier_id = a.id AND a.deleted = 0
				WHERE a.account_type = 'Supplier'
					AND a.is_reported = 1
					AND a.is_stop_tracking = 0
				GROUP BY tmp.supplier_id
				-- HAVING total_debt <> 0
				ORDER BY supplier_code";

		$res = $this->bean->db->query($sql);
		$total_BBA = $total_VJA = $total_VU = $total_JPA = 0;
		while ($row = $this->bean->db->fetchByAssoc($res)) {
			if ((float)$row['total_debt'] <> 0) {
				if ($row['supplier_code'] == 'BBATMTP' || $row['supplier_code'] == 'BBATPOLD') {
					unset($total_debt['plus']['BBA']);
					unset($total_debt['minus']['BBA']);
					$total_BBA += $row['total_debt'];
					if ($total_BBA < 0) $type = 'minus';
					else $type = 'plus';
					$total_debt[$type]['BBA'] = array(
						'amount' => $total_BBA,
						'name' => 'Bamboo',
					);
				} else if ($row['supplier_code'] == 'VJATMTP1' || $row['supplier_code'] == 'VJTPOLD') {
					unset($total_debt['plus']['VJA']);
					unset($total_debt['minus']['VJA']);
					$total_VJA += $row['total_debt'];
					if ($total_VJA < 0) $type = 'minus';
					else $type = 'plus';
					$total_debt[$type]['VJA'] = array(
						'amount' => $total_VJA,
						'name' => 'Vietjet',
					);
				} else if ($row['supplier_code'] == 'JPA' || $row['supplier_code'] == 'JPATP') {
					unset($total_debt['plus']['JET']);
					unset($total_debt['minus']['JET']);
					$total_JPA += $row['total_debt'];
					if ($total_JPA < 0) $type = 'minus';
					else $type = 'plus';
					$total_debt[$type]['JET'] = array(
						'amount' => $total_JPA,
						'name' => 'Jetstar',
					);
				} else if ($row['supplier_code'] == 'VUTMTP' || $row['supplier_code'] == 'VUOLD') {
					unset($total_debt['plus']['VU']);
					unset($total_debt['minus']['VU']);
					$total_VU += $row['total_debt'];
					if ($total_VU < 0) $type = 'minus';
					else $type = 'plus';
					$total_debt[$type]['VU'] = array(
						'amount' => $total_VU,
						'name' => 'Vietravel',
					);
				}

				// if($row['supplier_code'] == 'ENVIET' || $row['supplier_code'] == 'VNAHNH')
				else {
					if ((float)$row['total_debt'] < 0) $type = 'minus';
					else $type = 'plus';
					$total_debt[$type][$row['supplier_code']] = array(
						'amount' => (float)$row['total_debt'],
						'name' => $row['supplier_name'],
					);
				}
			}
		}

		// global $current_user;
		// if(is_admin($current_user)) {
		// 	echo '<pre>';
		// 	var_dump($total_debt);
		// 	echo '</pre>';
		// 	exit;
		// }

		return $total_debt;
	}

	function getAgentList($opening_year, $accounting_code, $post_fdate, $post_tdate) {
		global $db;

		$sql = "SELECT a.ticker_symbol AS agent_code,
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
					WHERE
						p.sotaikhoan = '" . $accounting_code . "'
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
					WHERE
						p.date_ticket_issue >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND p.date_ticket_issue <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
						AND p.is_agent = 1
						AND p.deleted = 0
						AND p.booking_status IN ('7', '8')
					GROUP BY p.agent_id

					-- PAYMENT VOUCHER
					UNION
					SELECT p.id,
						p.supplier_id AS agent_id,
						SUM(IFNULL(p.amount, 0)) AS debt_amount,
						0 AS pay_amount
					FROM ec_payment_voucher p 
						LEFT JOIN ec_payment_types pt ON p.ec_payment_types_id_c = pt.id AND pt.deleted = 0
					WHERE
						p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND p.ngaychungtu <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
						AND p.pv_status = '3'
						AND pt.is_receipt_debt = 1
						AND p.deleted = 0
					GROUP BY p.id

					-- RECEIPT VOUCHER
					UNION
					SELECT p.id,
						p.account_id_c AS agent_id,
						SUM(IF(p.is_debt = 1 AND p.loai_thu IN (4, 5), IFNULL(p.amount_converted, 0), 0)) AS debt_amount,
						SUM(IF(p.rv_status = 1 AND p.is_debt = 0, IFNULL(p.amount_converted, 0), 0)) AS pay_amount
					FROM ec_receipt_voucher p
					WHERE 
						p.ngaychungtu >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND p.ngaychungtu <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
						AND p.rv_status IN (1, 2)
						AND p.deleted = 0
						AND p.account_id_c IS NOT NULL
					GROUP BY p.id

					-- RECEIPT VOUCHER FOR DEBT
					UNION
					SELECT p.id,
						p.account_id_c AS agent_id,
						0 AS debt_amount,
						SUM(IFNULL(p.amount_converted, 0)) AS pay_amount,
					FROM ec_receipt_voucher p
					WHERE 
						DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') >= '" . date('Y-01-01', strtotime($post_fdate)) . "'
						AND DATE_FORMAT(DATE_ADD(p.ngayhachtoan, INTERVAL 7 HOUR), '%Y-%m-%d') <= '" . date('Y-m-d', strtotime($post_tdate)) . "'
						AND p.rv_status = 1
						AND p.is_debt = 1
						AND p.deleted = 0
						AND p.account_id_c IS NOT NULL
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
			$total_debt += $row['total_debt'];
		}

		return 0;
		return $total_debt;
	}

	// Chưa xuất vé
	function getNotExportedTicket($to_date) {
		global $db;
		$sql = 'SELECT SUM(d.total_bought_price) AS total_not_exported
				FROM ec_booking_details d
					LEFT JOIN ec_flight_bookings b ON b.id = d.booking_id AND b.deleted = 0 
				WHERE b.booking_status = "3"  
					AND b.is_paid = 1
					AND b.date_entered >= "' . date('Y') . '-01-01"	
					AND d.deleted = 0
				ORDER BY b.date_entered DESC';
				
		$res = $db->query($sql);
		$row = $db->fetchByAssoc($res);
		return $row['total_not_exported'];
	}

	// Quỹ dự phòng
	function getReserveFund($report_year, $post_from_date, $post_to_date, $sql_search, $index) {
		global $db;

		$reserve_fund_bankaccount = array(
			'a56c866c-8476-949d-96b5-60cb1818fc55',
			'2cd3be12-1ddf-6bc9-158e-5ff97562f721'
		);

		$html = '';
		foreach ($reserve_fund_bankaccount as $tk_id) {
			$sql = "
				SELECT SUM(IFNULL(tmp.thutien,0)) - SUM(IFNULL(tmp.chitien,0)) AS sotien 
				FROM (
					-- OPENING AMOUNT
					SELECT (IFNULL(p.dunodau,0)-IFNULL(p.ducodau,0)) AS thutien, 0 AS chitien
					FROM ec_chitiettaikhoan" . $report_year . " p
					WHERE 
						p.parent_id = '" . $tk_id . "'
						AND p.parent_type = 'EC_Bank_Account'
						AND p.deleted = 0

					-- RECEIPT VOUCHER
					UNION
					SELECT p.amount_converted AS thutien, 0 AS chitien
					FROM ec_receipt_voucher p
					WHERE
						p.receipt_type = 'credit_transfer'
						AND p.tknganhang_id = '" . $tk_id . "'
						AND p.rv_status = '1' 
						AND p.is_margin = 0 
						AND p.amount IS NOT NULL 
						" . $sql_search . "
						AND p.deleted = 0 
					
					-- PAYMENT VOUCHER
					UNION
					SELECT 0 AS thutien, p.amount AS chitien
					FROM ec_payment_voucher p
					WHERE 
						p.hinhthucchi = 'credit_transfer' 
						AND p.tknganhang_id = '" . $tk_id . "'
						AND p.pv_status = '3' 
						AND p.amount IS NOT NULL 
						" . $sql_search . "
						AND p.deleted = 0 
					
					-- TRANSFER FROM
					UNION
					SELECT 0 AS thutien, IFNULL(p.sotien,0) AS chitien
					FROM ec_chuyentiennoibo p
					WHERE 
						p.tutknganhang_id = '" . $tk_id . "'
						AND p.ghiso = 1 
						AND p.tutienmat = 0 
						" . $sql_search . "
						AND p.deleted=0 
					
					-- TRANSFER TO
					UNION
					SELECT IFNULL(p.sotien,0) AS thutien, 0 AS chitien
					FROM ec_chuyentiennoibo p
					WHERE 
						p.dentknganhang_id = '" . $tk_id . "'
						AND p.ghiso = 1 
						AND p.dentienmat = 0 
						" . $sql_search . "
						AND p.deleted = 0 
				) AS tmp";
			$total = $db->getOne($sql);

			// ID Tài khoản "Qũy dự phòng"
			if ($tk_id == 'a56c866c-8476-949d-96b5-60cb1818fc55') {
				$html .= '<tr>
					<td align="center">' . ($index++) . '</td>
					<td align="left"></td>
					<td align="left">Quỹ dự phòng</td>
					<td align="left" class="banks_account"></td>
					<td align="right"><span class="total-credit">' . format_number($total) . '</span></td>
				</tr>';
			} else {
				$tk = new EC_Bank_Account;
				$tk->retrieve($tk_id);
				$html .= '<tr>
					<td align="center">' . ($index++) . '</td>
					<td align="left">' . $tk->account_number . '</td>
					<td align="left">' . $tk->name . '</td>
					<td align="left" class="banks_account"></td>
					<td align="right"><span class="total-credit">' . format_number($total) . '</span></td>
				</tr>';
			}
		}

		return $html;
	}

	// Tiền hoàn vé nhưng chưa trả khách
	function getReturnAmount($to_date) {
		global $db;
		$from_date = '2021-01-01';
		$to_date = date('Y-m-d', strtotime($to_date));

		$sql = '
			SELECT (
				SUM(hv.tongtienkhach) 
              - SUM((
                    SELECT SUM(IFNULL(p.amount,0))
				    FROM ec_payment_voucher p
				    WHERE p.hoanve_id = hv.id AND p.pv_status = 3 AND p.deleted = 0
                ))
			) AS return_amt
            FROM ec_hoanve hv
            	INNER JOIN ec_flight_bookings bk ON bk.id = hv.booking_id AND bk.deleted = 0
            WHERE 
				hv.tinhtrang = 1
				AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) >= "' . $from_date . '"
				AND DATE(DATE_ADD(bk.date_entered, INTERVAL 7 HOUR)) <= "' . $to_date . '"
				AND hv.deleted = 0';
		return $db->getOne($sql);
	}

	function getAdvanceAmt($to_date) {
		global $db;
		$from_date = '2021-01-01';
		$to_date = date('Y-m-d', strtotime($to_date));
		$sql = '
			SELECT SUM(t.tam_ung - t.hoan_ung)
			FROM (
				-- sodauky
				SELECT u.id AS employee_id,
					CONCAT(IFNULL(u.last_name, ""), " ", IFNULL(u.first_name, "")) AS full_name,
					SUM(IFNULL(sdk.dunodau, 0)) AS tam_ung,
					SUM(IFNULL(sdk.ducodau, 0)) AS hoan_ung,
					"SDK" AS parent_type,
					"" AS ngayhachtoan
				FROM ec_chitiettaikhoan sdk
					INNER JOIN users u ON u.id = sdk.parent_id
				WHERE 
					sdk.sotaikhoan = "141"
					AND sdk.parent_type = "Users"
					AND sdk.date_entered >= "' . $from_date . '" 
					AND sdk.date_entered <= "' . $to_date . '"
					AND sdk.deleted = 0
				GROUP BY u.id
				
				-- tam ung tu phieu chi
				UNION
				SELECT u.id AS employee_id,
					CONCAT(IFNULL(u.last_name, ""), " ", IFNULL(u.first_name, "")) AS full_name,
					SUM(t.amount) AS tam_ung,
					0 AS hoan_ung,
					"" AS parent_type,
					MAX(t.ngayhachtoan) AS ngayhachtoan
				FROM ec_payment_voucher t
					INNER JOIN users u ON u.id = t.employee_id
				WHERE 
					t.pv_status = 3
					AND t.ngayhachtoan >= "' . $from_date . '" 
					AND t.ngayhachtoan <= "' . $to_date . '"
					AND t.deleted = 0
				GROUP BY u.id
				
				-- hoan ung tru luong
				UNION
				SELECT u.id AS employee_id,
					CONCAT(IFNULL(u.last_name, ""), " ", IFNULL(u.first_name, "")) AS full_name,
					0 AS tam_ung,
					SUM(hu.minus_amount) AS hoan_ung,
					"" AS parent_type,
					MAX(hu.voucher_date) AS ngayhachtoan
				FROM ec_salary_details hu
					INNER JOIN users u ON u.id = hu.assigned_user_id
				WHERE 
					hu.type = "minus"
					AND hu.reason = "TamUng" 
					AND hu.voucher_date >= "' . $from_date . '" 
					AND hu.voucher_date <= "' . $to_date . '"
					AND hu.deleted = 0
				GROUP BY u.id

				-- hoan ung phieu thu
				UNION
				SELECT u.id AS employee_id
					CONCAT(IFNULL(u.last_name, ""), " ", IFNULL(u.first_name, "")) AS full_name
					0 AS tam_ung
					SUM(r.amount) AS hoan_ung
					"" AS parent_type
					MAX(r.ngayhachtoan) AS ngayhachtoan
				FROM ec_receipt_voucher r
					INNER JOIN users u ON u.id = r.employee_id
				WHERE 
					AND r.loai_thu = 9
					AND r.rv_status = 1
					AND r.ngayhachtoan >= "' . $from_date . '" 
					AND r.ngayhachtoan <= "' . $to_date . '"
					AND r.deleted = 0
				GROUP BY u.id	 			
			) AS t
			LEFT JOIN users u ON u.id = t.employee_id';
		return $db->getOne($sql);
	}
}
