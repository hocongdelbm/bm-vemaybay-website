<?php
 
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");

class Viewadvance extends SugarView {
 	function display() {
 		$smarty = new Sugar_Smarty();
 		if(isset($_REQUEST['for']) && $_REQUEST['for'] == 'showdetail') {
 			$this->populatDetail($smarty);
 		} else {
 			$this->populateContent($smarty);
 		}
 		$smarty->display('modules/EC_Employee_Salary/tpls/view_advance.tpl');
 	}

 	function populateContent($smarty) {
 		// tháng
 		// for($m = 1; $m <= 12; $m++) {
 		// 	$month[$m] = $m;
 		// }
 		// if(empty($_POST['month'])) $_POST['month'] = date('n');
 		// $smarty->assign('MONTH', get_select_options_with_id($month, (int)$_POST['month']));

		// đến năm 
 		for($i = 2021; $i <= date('Y'); $i++) {
 			$year[$i] = $i;
 		}
 		if(empty($_POST['year'])) $_POST['year'] = date('Y');
 		$smarty->assign('YEAR', get_select_options_with_id($year, (int)$_POST['year']));

 		// tạo ra bảng hoàn tạm ứng
 		$data = $this->populateAdvance($_POST['year']);
 		$smarty->assign('ADVANCE', $data);
 	}

 	function populateAdvance($year) {
		global $current_user;
 		$from_date 	= date('Y-m-d', strtotime('01-01-2021'));
 		$to_date 		= date('Y-m-d', strtotime('31-12-'.$year));
 		$html 		= '';

		if(is_admin($current_user)){
			$where = '';
		} else $where = ' AND t.employee_id = "'.$current_user->id.'"';

 		// tạm ứng là lấy tổng các phiếu chi trong tháng cho nhân viên
 		// hoàn ứng là lấy tổng các tiền loại tạm ứng tổng cộng, cột giảm trừ trong bảng lương
 		// còn tạm ứng = tạm ứng - hoàn ứng
 		$sql = '
			SELECT 
				t.employee_id,
				t.full_name,
				u.advance_note,
				SUM(t.tam_ung) - SUM(t.hoan_ung) AS con_tam_ung,
				SUM(
					IF(
						YEAR(IF(
							t.ngayhachtoan = "" OR t.ngayhachtoan IS NULL
							, 2021
							, t.ngayhachtoan
						)) < '.$year.'
						, (t.tam_ung - t.hoan_ung)
						, 0
					)
				) AS sdk,
				SUM(
					IF(
						YEAR(t.ngayhachtoan) = '.$year. '
						, IF(
							t.tam_ung IS NULL OR t.tam_ung = 0
							, IFNULL(t.hoan_ung, 0)
							, t.tam_ung
						), t.tam_ung - t.hoan_ung
					)
				) AS trong_ky
			FROM (
				-- sodauky
				SELECT
					u.id AS employee_id,
					CONCAT(IFNULL(u.last_name, ""), " ", IFNULL(u.first_name, "")) AS full_name,
					SUM(IFNULL(sdk.dunodau, 0)) AS tam_ung,
					SUM(IFNULL(sdk.ducodau, 0)) AS hoan_ung,
					"SDK" AS parent_type,
					"" AS ngayhachtoan,
					"" AS voucher_id
				FROM ec_chitiettaikhoan sdk
					INNER JOIN users u ON u.id = sdk.parent_id
				WHERE sdk.sotaikhoan = "141"
					AND sdk.parent_type = "Users"
					AND sdk.date_entered >= "'.$from_date.'" 
					AND sdk.date_entered <= "'.$to_date. '"
					AND sdk.deleted = 0
				GROUP BY u.id
				
				-- tam ung tu phieu chi
				UNION
				SELECT
					u.id AS employee_id,
					CONCAT(IFNULL(u.last_name, ""), " ", IFNULL(u.first_name, "")) AS full_name,
					SUM(t.amount) AS tam_ung,
					0 AS hoan_ung ,
					"" AS parent_type,
					MAX(t.ngayhachtoan) AS ngayhachtoan,
					t.id AS voucher_id
				FROM ec_payment_voucher t
					INNER JOIN users u ON u.id = t.employee_id
				WHERE t.pv_status = 3
					AND t.ngayhachtoan >= "'.$from_date.'" 
					AND t.ngayhachtoan <= "'.$to_date. '"
					AND t.deleted = 0 
				GROUP BY t.id
				
				-- hoan ung tru luong
				UNION
				SELECT
					u.id AS employee_id,
					CONCAT(IFNULL(u.last_name, ""), " ", IFNULL(u.first_name, "")) AS full_name,
					0 AS tam_ung,
					SUM(hu.minus_amount) AS hoan_ung,
					"" AS parent_type,
					MAX(hu.voucher_date) AS ngayhachtoan,
					hu.id AS voucher_id
				FROM ec_salary_details hu
					INNER JOIN users u ON u.id = hu.assigned_user_id
				WHERE hu.type = "minus"
					AND hu.reason = "TamUng" 
					AND hu.voucher_date >= "'.$from_date.'" 
					AND hu.voucher_date <= "'.$to_date. '"
					AND hu.deleted = 0 
				GROUP BY hu.id

				-- hoan ung phieu thu
				UNION
				SELECT
					u.id AS employee_id,
					CONCAT(IFNULL(u.last_name, ""), " ", IFNULL(u.first_name, "")) AS full_name,
					0 AS tam_ung,
					SUM(r.amount) AS hoan_ung,
					"" AS parent_type,
					MAX(r.ngayhachtoan) AS ngayhachtoan,
					r.id AS voucher_id
				FROM ec_receipt_voucher r
					INNER JOIN users u ON u.id = r.employee_id
				WHERE r.rv_status = 1
					AND r.loai_thu = 9
					AND r.ngayhachtoan >= "'.$from_date.'" 
					AND r.ngayhachtoan <= "'.$to_date. '"
					AND r.deleted = 0
				GROUP BY r.id
			) AS t
	 			LEFT JOIN users u ON u.id = t.employee_id
			GROUP BY t.employee_id
			-- HAVING sdk <> 0 OR trong_ky <> 0
			HAVING (sdk <> 0 OR trong_ky <> 0) '.$where.'
			ORDER BY con_tam_ung DESC';

		$res 	= $this->bean->db->query($sql);
		$i 		= 1;
		$total 	= 0;

		while($row = $this->bean->db->fetchByAssoc($res)) {
			if($row['con_tam_ung'] > 0){
				$html .= '
					<tr>
						<td class="text-center hide-mobile">'.$i.'</td>
						<td>'.$row['full_name'].'</td>
						<td class="text-end"><a class="show_detail text-primary text-underline" href="index.php?module=EC_Employee_Salary&action=advance&for=showdetail&user='.$row['employee_id'].'&to_year='.$year.'" target="_blank">'.format_number($row['con_tam_ung']).'</a></td>
						<td><div class="d-flex align-items-center gap-2"><textarea class="advance_note box-textarea">'.$row['advance_note'].'</textarea><input type="button" class="save_note btn btn-primary" value="Lưu" ln="'.$i.'" user="'.$row['employee_id'].'"></div></td>
					</tr>';
				$total += $row['con_tam_ung'];
				$i++;
			}
		}	
		$html .= '
			<tr class="footer-tr">
				<td class="text-center hide-mobile"></td>
				<td><b>Tổng cộng</b></td>
				<td class="text-end"><b>'.format_number($total).'</b></td>
				<td></td>
			</tr>';
	
 		return $html;
 	}

 	// chi tiết tạm hoàn ứng
 	function populatDetail($smarty) {
 		$smarty->assign('isDetail', 1);

		// nhân viên
		$smarty->assign('USER', $_REQUEST['user']);

 		// tháng
 		if(empty($_REQUEST['month'])) $_REQUEST['month'] = date('n');
 		$smarty->assign('MONTH', $_REQUEST['month']);

		for ($i = 2021; $i <= date('Y'); $i++) {
			$year[$i] = $i;
		}
		// đến năm
		if(empty($_REQUEST['to_year'])) $_REQUEST['to_year'] = date('Y');
		$smarty->assign('TO_YEAR', get_select_options_with_id($year, (int)$_REQUEST['to_year']));
		// từ năm
		if (empty($_REQUEST['from_year'])) $_REQUEST['from_year'] = $_REQUEST['to_year'];
		$smarty->assign('FROM_YEAR', get_select_options_with_id($year, (int)$_REQUEST['from_year']));

 		if(empty($_REQUEST['user'])) {
 			$smarty->assign('EMPLOYEE_NAME', 'Thiếu thông tin nhân viên');
 		} else {
 			$employee = new User;
 			$employee->retrieve($_REQUEST['user']);
 			$smarty->assign('EMPLOYEE_NAME', $employee->last_name.' '.$employee->first_name);

 			// lấy dữ liệu ra bảng chi tiết hoàn tạm ứng
	 		$data = $this->populateAdvanceDetail($_REQUEST['from_year'], $_REQUEST['to_year'], $employee->id);
	 		$smarty->assign('ADVANCE_DETAIL', $data);
 		}
 	}

 	function populateAdvanceDetail($from_year, $to_year, $employee_id) {
 		$from_date 	= date('Y-m-d', strtotime('01-01-'.$from_year));
 		$to_date 		= date('Y-m-d', strtotime('31-12-'.$to_year));
 		$html 		= '';

 		// tạm ứng là lấy tổng các phiếu chi trong tháng cho nhân viên
 		// hoàn ứng là lấy tổng các tiền loại tạm ứng tổng cộng, cột giảm trừ trong bảng lương
 		// hoàn ứng cũng có thể là các phiếu thu tiền từ nhân viên
 		// còn tạm ứng = tạm ứng - hoàn ứng
 		$sql = 'SELECT t.voucher_date, t.tam_ung, t.hoan_ung
 					 , t.parent_type, t.voucher, t.voucher_id
 				FROM (
					-- sodauky
					SELECT  
						"Số dư đầu kỳ" AS voucher
						, "" AS voucher_id
						, SUM(IFNULL(dk.dunodau, 0)) AS tam_ung
						, SUM(IFNULL(dk.ducodau, 0)) AS hoan_ung
						, "SoDauKy" AS parent_type
						, "" AS voucher_date
					FROM ( 
						-- dau ky nam 2021
						SELECT SUM(IFNULL(sdk.dunodau, 0)) AS dunodau
							 , SUM(IFNULL(sdk.ducodau, 0)) AS ducodau
							 , "" AS id
						FROM ec_chitiettaikhoan sdk
						INNER JOIN users u ON u.id = sdk.parent_id
						WHERE sdk.deleted = 0 AND sdk.sotaikhoan = "141"
						AND sdk.parent_type = "Users"
						AND sdk.date_entered >= "2021-01-01" 
						AND sdk.date_entered <= "2021-12-31"
						AND sdk.parent_id = "'.$employee_id. '"

						-- tam ung tu phieu chi thoi gian truoc
						UNION
						SELECT SUM(t.amount) AS dunodau
							 , 0 AS ducodau 
							 , t.id
						FROM ec_payment_voucher t
						WHERE t.deleted = 0 AND t.pv_status = 3
						AND t.ngayhachtoan < "' . $from_date . '"
						AND t.ngayhachtoan >= "2021-01-01"
						AND t.employee_id = "' . $employee_id . '"
						GROUP BY t.id

	 					-- hoan ung bang luong thoi gian truoc
	 					UNION
	 					SELECT 0 AS dunodau
							 , SUM(hu.minus_amount) AS ducodau 
							 , hu.id
						FROM ec_salary_details hu
						WHERE hu.deleted = 0 AND hu.type = "minus"
						AND hu.reason = "TamUng" 
						AND hu.voucher_date >= "2021-01-01" 
						AND hu.voucher_date < "' . $from_date . '"
						AND hu.assigned_user_id = "' . $employee_id . '"
						GROUP BY hu.id

						-- hoan ung phieu thu thoi gian truoc
						UNION
						SELECT 0 AS dunodau
							 , SUM(r.amount) AS ducodau
							 , r.id 
						FROM ec_receipt_voucher r
						WHERE r.deleted = 0 AND r.rv_status = 1
						AND r.ngayhachtoan >= "2021-01-01" 
						AND r.ngayhachtoan < "' . $from_date . '"
						AND r.employee_id = "' . $employee_id . '"
						AND r.loai_thu = 9
						GROUP BY r.id
					) AS dk
	 				
	 				-- tam ung tu phieu chi
	 				UNION
	 				SELECT t.name AS voucher
	 					   , t.id AS voucher_id
	 					   , SUM(t.amount) AS tam_ung
	 					   , 0 AS hoan_ung 
	 					   , "EC_Payment_Voucher" AS parent_type
	 					   , DATE_FORMAT(t.ngayhachtoan, "%Y-%m-%d") AS voucher_date
	 				FROM ec_payment_voucher t
	 				WHERE t.deleted = 0 AND t.pv_status = 3
	 				AND t.ngayhachtoan >= "'.$from_date.'" 
	 				AND t.ngayhachtoan <= "'.$to_date.'"
	 				AND t.employee_id = "'.$employee_id.'"
	 				GROUP BY t.id

	 				-- hoan ung bang luong
	 				UNION
	 				SELECT "Hoàn ứng bằng lương" AS voucher
	 					   , "" AS voucher_id
	 					   , 0 AS tam_ung
	 					   , SUM(hu.minus_amount) AS hoan_ung 
	 					   , "HoanUng" AS parent_type
	 					   , DATE_FORMAT(hu.voucher_date, "%Y-%m-%d") AS voucher_date
	 				FROM ec_salary_details hu
	 				WHERE hu.deleted = 0 AND hu.type = "minus"
	 				AND hu.reason = "TamUng" 
	 				AND hu.voucher_date >= "'.$from_date.'" 
	 				AND hu.voucher_date <= "'.$to_date.'"
	 				AND hu.assigned_user_id = "'.$employee_id.'"
	 				GROUP BY hu.id

	 				-- hoan ung phieu thu
	 				UNION
	 				SELECT r.name AS voucher
	 				 	   , r.id AS voucher_id
	 					   , 0 AS tam_ung
	 					   , SUM(r.amount) AS hoan_ung 
	 					   , "EC_Receipt_Voucher" AS parent_type
	 					   , DATE_FORMAT(r.ngayhachtoan, "%Y-%m-%d") AS voucher_date
	 				FROM ec_receipt_voucher r
	 				WHERE r.deleted = 0 AND r.rv_status = 1
	 				AND r.ngayhachtoan >= "'.$from_date.'" 
	 				AND r.ngayhachtoan <= "'.$to_date.'"
	 				AND r.employee_id = "'.$employee_id. '"
					AND r.loai_thu = 9
	 				GROUP BY r.id
	 			) AS t
				ORDER BY t.voucher_date';
		// if($GLOBALS['current_user']->user_name == 'nponline') {
		// 	echo $sql; exit;
		// }
		$res 	= $this->bean->db->query($sql);
		$total 	= $total_tam_ung = $total_hoan_ung = 0;

		while($row = $this->bean->db->fetchByAssoc($res)) {
			$total += $row['tam_ung'] - $row['hoan_ung'];
			if($row['parent_type'] == 'SoDauKy') {
				$so_phieu = '<b>'.$row['voucher'].'</b>';
				$row['tam_ung'] = $row['hoan_ung'] = 0;
				$total_text = '<b>'.format_number($total).'</b>';
			} else {
				if(!empty($row['voucher_id'])) {
					$so_phieu = '<a href="index.php?module='.$row['parent_type'].'&action=DetailView&record='.$row['voucher_id'].'" target="_blank">'.$row['voucher'].'</a>';
				} else {
					$so_phieu = $row['voucher'];
				}
				$total_text = format_number($total);
			}
			
			$html .= '<tr>
						<td class="text-center">'.(!empty($row['voucher_date'])?date('d-m-Y', strtotime($row['voucher_date'])):'').'</td>
						<td class="text-center">'.$so_phieu.'</td>
						<td class="text-end">'.format_number($row['tam_ung']).'</td>
						<td class="text-end">'.format_number($row['hoan_ung']).'</td>
						<td class="text-end">'.$total_text.'</td>
					</tr>';
			$total_tam_ung += $row['tam_ung'];
			$total_hoan_ung += $row['hoan_ung'];
		}	

		$html .= '<tr class="footer-tr">
					<td class="text-center"><b>Tổng cộng</b></td>
					<td></td>
					<td class="text-end"><b>'.format_number($total_tam_ung).'</b></td>
					<td class="text-end"><b>'.format_number($total_hoan_ung).'</b></td>
					<td class="text-end"><b>'.format_number($total).'</b></td>
				</tr>';
	
 		return $html;
 	}
}