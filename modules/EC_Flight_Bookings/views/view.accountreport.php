<?php
require_once("include/Sugar_Smarty.php");

class Viewaccountreport extends SugarView {
	function display() {
		if (ACLController::checkAccess('Bugs', 'view', true)) {
			$smartyCont = new Sugar_Smarty();
			$this->populateContent($smartyCont);
			$smartyCont->display('modules/EC_Flight_Bookings/tpls/view_accountreport.tpl');
		} else {
			header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
			exit();
		}
	}

	function populateContent($smartyobj) {
		global $db;

		$sql_search = "";
		// Từ ngày
		if (isset($_REQUEST['from_date']) && !empty($_REQUEST['from_date'])) {
			$sql_search .= " AND bk.date_ticket_issue >= '" . date('Y-m-d', strtotime($_REQUEST['from_date'])) . "' ";
			$from_date_value = $_REQUEST['from_date'];
		} else {
			$sql_search .= " AND bk.date_ticket_issue >= '" . date('Y-m-01') . "' ";
			$from_date_value = date('01-m-Y');
		}
		// Đến ngày
		if (isset($_REQUEST['to_date']) && !empty($_REQUEST['to_date'])) {
			$sql_search .= " AND bk.date_ticket_issue <= '" . date('Y-m-d', strtotime($_REQUEST['to_date'])) . "' ";
			$to_date_value = $_REQUEST['to_date'];
		} else {
			$sql_search .= " AND bk.date_ticket_issue <= '" . date('Y-m-d') . "' ";
			$to_date_value = date('d-m-Y');
		}

		$sql = "SELECT bk.contact_name AS lienhe, bk.phone AS dienthoai, bk.email, bk.address AS diachi,
					SUM(bk.total_qty) AS soluong,
					SUM(bk.total_bought_price) AS giavon,
					SUM(bk.total_amount) AS doanhthu,
					(SUM(bk.total_amount) - SUM(bk.total_bought_price)) AS laigop
				FROM ec_flight_bookings bk
				WHERE " . $sql_search . " 
					AND bk.booking_status = '8'
					AND bk.deleted = 0
				GROUP BY bk.phone
				ORDER BY soluong DESC ";

		$res = $db->query($sql);
		$html = '';
		$i = 0;
		$tongsl = 0;
		$tongdt = 0;
		$tonggv = 0;
		$tonglg = 0;
		while ($row = $db->fetchByAssoc($res)) {
			$bg_color = ($i % 2 != 0) ? '#F1F1FE' : 'white';
			$html .= '<tr>
				<td align="center" style="background:' . $bg_color . '">
					<input type="checkbox" name="phone[]" value="' . $row['dienthoai'] . '" />
					<input type="hidden" name="account_' . $row['dienthoai'] . '" value="' . $row['lienhe'] . '" />
					<input type="hidden" name="email_' . $row['dienthoai'] . '" value="' . $row['email'] . '" />
					<input type="hidden" name="address_' . $row['dienthoai'] . '" value="' . $row['diachi'] . '" />
				</td>
				<td align="left" style="background:' . $bg_color . '">' . $row['lienhe'] . '</td>
				<td align="left" style="background:' . $bg_color . '">' . $row['dienthoai'] . '</td>
				<td align="left" style="background:' . $bg_color . '">' . $row['email'] . '</td>
				<td align="left" style="background:' . $bg_color . '">' . $row['diachi'] . '</td>
				<td align="center" style="background:' . $bg_color . '">' . format_number($row['soluong']) . '</td>
				<td align="right" style="background:' . $bg_color . '">' . format_number($row['doanhthu']) . '</td>
				<td align="right" style="background:' . $bg_color . '">' . format_number($row['giavon']) . '</td>
				<td align="right" style="background:' . $bg_color . '">' . format_number($row['laigop']) . '</td>
			</tr>';

			$tongsl += $row['soluong'];
			$tongdt += $row['doanhthu'];
			$tonggv += $row['giavon'];
			$tonglg += $row['laigop'];
			$i++;
		}

		if (isset($_POST['btnCreateAccount'])) {
			$contact_cnt = count($_POST['phone']);
			for ($i = 0; $i < $contact_cnt; $i++) {
				if (isset($_POST['phone'][$i]) && !$this->isAccountExist($_POST['account_' . $_POST['phone'][$i]], $_POST['phone'][$i])) {
					$acc = new Account();
					$acc->id = '';
					$acc->name = trim(stripslashes($_POST['account_' . $_POST['phone'][$i]]));
					$acc->ownership = trim(stripslashes($_POST['account_' . $_POST['phone'][$i]]));
					$acc->ticker_symbol = trim(stripslashes($_POST['phone'][$i]));
					$acc->phone_office = trim(stripslashes($_POST['phone'][$i]));
					$acc->account_type = 'Customer';
					$acc->billing_address_street = $_POST['address_' . $_POST['phone'][$i]];
					$acc->email1 = $_POST['email_' . $_POST['phone'][$i]];
					$acc->save();
				}
			}
		} // end if 

		$smartyobj->assign('DATA', $html);
		$smartyobj->assign('TONGSD', format_number($i));
		$smartyobj->assign('TONGSL', format_number($tongsl));
		$smartyobj->assign('TONGDT', format_number($tongdt));
		$smartyobj->assign('TONGGV', format_number($tonggv));
		$smartyobj->assign('TONGLG', format_number($tonglg));
		$smartyobj->assign('FROM_DATE_VALUE', $from_date_value);
		$smartyobj->assign('TO_DATE_VALUE', $to_date_value);
	}

	function isAccountExist($name, $phone) {
		global $db;
		$row_count = 0;
		$sql = "SELECT COUNT(id)
				FROM accounts
				WHERE (name='" . trim(stripslashes($name)) . "' OR phone_office='" . trim(stripslashes($phone)) . "') AND deleted = 0";
		$row_count += $db->getOne($sql);

		if ($row_count > 0)return true;
		return false;
	}
}
