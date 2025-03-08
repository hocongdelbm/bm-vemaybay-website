<?php
require_once("include/Sugar_Smarty.php");

class Viewreport extends SugarView {
	function display() {
        $this->display_style();

		$smarty = new Sugar_Smarty();
		$this->populate_content($smarty);
		$smarty->display('modules/'.$this->bean->module_dir.'/tpls/report.tpl');

		$this->display_script();

		// global $current_user;
		// if($current_user->id == '1') {
		// 	$json_data = $this->bean->db->getOne("SELECT m.data FROM ec_messages m WHERE id = '1aa06259-4880-2386-3857-67ca7200e76a'");
		// 	$data = json_decode(html_entity_decode($json_data), true);

		// 	$sql = "SELECT id
		// 		FROM ec_vouchers
		// 		WHERE campaign_id = '5010948939e4e8bb7393781f9258069867cb' AND deleted = 0";
			
		// 	$res = $this->bean->db->query($sql);
		// 	while($row = $this->bean->db->fetchByAssoc($res)) {
		// 		$voucher_id = $row['id'];
		// 		$p = trim(array_shift($data));

		// 		$sql_update = "UPDATE ec_vouchers SET name = '$p' WHERE id = '$voucher_id'";
		// 		if(!$this->bean->db->query($sql_update)) pr("ERROR");
		// 	}
		// }

		// Record point log
		global $db;
		$contact_id = 'df43e7e7-bab0-cc80-7820-656edbae6be1';
		$apply_points = 18;
		$total_points = 65;
		$booking_id = '2ecu9n19387rg9812gre9812gf9081g3';

		$point_log = new EC_Contact_Points_Log();
		$point_log->id = '';
		$point_log->name = 'Dùng điểm tích lũy cho booking';
		$point_log->contact_id = $contact_id;
		$point_log->contact_phone = $db->getOne("SELECT phone_mobile FROM contacts WHERE id = '$contact_id' AND deleted = 0");
		$point_log->up = 0;
		$point_log->down = $apply_points;
		$point_log->current_point = $total_points - $apply_points;
		$point_log->parent_type = 'EC_Flight_Bookings';
		$point_log->parent_id = $booking_id;
		$point_log->save();
	}

	function display_style() {
		$style = '<link type="text/css" rel="stylesheet" href="modules/'.$this->bean->module_dir.'/css/report.css?v=1.0.7">';
        echo $style;
    }

	function display_script() {
		$script = '<script src="modules/'.$this->bean->module_dir.'/js/report.js?v=1.0.6"></script>';
        echo $script;
    }
	
	function populate_content($smarty) {
		$tbody = '';
		$total_cost = 0;
		$period = isset($_GET['period']) ? $_GET['period'] : 'today';
		
		$where_date = '';
		if($period == 'today') $where_date = "DATE(m.send_time) = '".date('Y-m-d')."'";
		else if($period == 'yesterder') $where_date = "DATE(m.send_time) = '".date('Y-m-d', strtotime("-1 days"))."'";
		else if($period == '7days') $where_date = "DATE(m.send_time) > '".date('Y-m-d', strtotime("-8 days"))."'";
		else if($period == '30days') $where_date = "DATE(m.send_time) > '".date('Y-m-d', strtotime("-31 days"))."'";
		else if($period == '90days') $where_date = "DATE(m.send_time) > '".date('Y-m-d', strtotime("-91 days"))."'";
		else if($period == 'year') $where_date = "YEAR(m.send_time) = '".date('Y')."'";
		else if($period == 'other') {
			$from_date 	= isset($_GET['from_date']) ? $this->format_date_search($_GET['from_date']) : '';
			$to_date 	= isset($_GET['to_date']) ? $this->format_date_search($_GET['to_date']) : '';
			if(strlen($from_date)*strlen($to_date) == 0) {
				$smarty->assign('TBODY', '<i>Không có dữ liệu</i>');
				$smarty->assign('TOTAL_COST', '0 đ');
				exit();
			}
			$where_date = "DATE(m.send_time) >= '$from_date' AND DATE(m.send_time) <= '$to_date'";
		}

		// Zalo ZNS
		$sql_zns = "SELECT COUNT(id) AS total_qty, SUM(cost) as total_amount
				FROM ec_messages m
				WHERE $where_date
					AND m.type = 'zalo_zns'
					AND m.status = 'done'
					AND m.deleted = 0";
		
		$res_zns = $this->bean->db->query($sql_zns);
		while($row = $this->bean->db->fetchByAssoc($res_zns)) {
			$tbody .= '<tr>
				<td class="td-name">Tin Zalo ZNS</td>
				<td class="td-qty">'.$row['total_qty'].'</td>
				<td class="td-amount">'.number_format($row['total_amount'], 0, ',', '.').' đ</td>
			</tr>';

			$total_cost += $row['total_amount'];
		}

		// SMS
		$sql_sms = "SELECT COUNT(id) AS total_qty, SUM(cost) * 300 as total_amount
				FROM ec_messages m
				WHERE $where_date
					AND m.type = 'sms'
					AND m.status = 'done'
					AND m.deleted = 0";
		
		$res_sms = $this->bean->db->query($sql_sms);
		while($row = $this->bean->db->fetchByAssoc($res_sms)) {
			$tbody .= '<tr>
				<td class="td-name">Tin SMS</td>
				<td class="td-qty">'.$row['total_qty'].'</td>
				<td class="td-amount"></td>
			</tr>';

			$total_cost += $row['total_amount'];
		}

		$smarty->assign('TBODY', $tbody);
		$smarty->assign('TOTAL_COST', number_format($total_cost, 0, ',', '.') . ' đ');
	}

	function format_date_search($date) {
		$date = str_replace('/', '-', $date);
		return date("Y-m-d", strtotime($date));
	}
}
	
?>