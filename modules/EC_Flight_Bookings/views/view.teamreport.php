<?php
require_once("include/Sugar_Smarty.php");
class Viewteamreport extends SugarView {
	function display() {
		//if(ACLController::checkAccess($this->bean->module_dir, 'list', true)){
		if(is_admin($GLOBALS['current_user'])) {
			$smarty = new Sugar_Smarty();
			$this->populateContent($smarty);
			$smarty->display('modules/'.$this->bean->module_dir.'/tpls/view_teamreport.tpl');
		} else {
			header('Location: index.php?module='.$this->bean->module_dir.'&action=Error&error_string='.urlencode('Bạn không được quyền truy cập'));
		}
	}
	
	function populateContent($smarty_obj) {
		global $db, $app_list_strings;
		$star = '<img src="custom/themes/default/images/star_medal_32.png" border="0" />';
		
		// Month & year list
		if(isset($_POST['month_list']) && isset($_POST['year_list'])){
			$month_list = (int)$_POST['month_list'];
			$year_list = (int)$_POST['year_list'];
		} else {
			$month_list = date('n');
			$year_list = date('Y');
		}
		
		// Team list
		$team_list = $this->getTeamList($month_list, $year_list);
		$html = '';
		$ttl_booking = 0;
		$ttl_complete = 0;
		$ttl_efficiency = 0;
		$ttl_ticket = 0;
		$ttl_profit = 0;
		$ttl_reward = 0;
		$i = 0;
		foreach($team_list as $team){
			if($i == 0)
				$rating = $star.$star.$star;
			elseif($i == 1)
				$rating = $star.$star;
			elseif($i == 2)
				$rating = $star;
			else
				$rating = '';
			
			$html .= '<tr>
				<td align="left">'.$team['team_name'].'</td>
				<td align="right">'.format_number($team['ttl_booking']).'</td>
				<td align="right">'.format_number($team['ttl_complete']).'</td>
				<td align="right">'.format_number($team['efficiency'], 1, 1).'%</td>
				<td align="right">'.format_number($team['ttl_ticket']).'</td>
				<td align="right">'.format_number($team['ttl_profit']).'</td>
				<td align="right">'.format_number($team['ttl_reward']).'</td>
				<td align="center">'.$rating.'</td>
			</tr>';
			
			$ttl_booking += $team['ttl_booking'];
			$ttl_complete += $team['ttl_complete'];
			$ttl_efficiency += $team['efficiency'];
			$ttl_ticket += $team['ttl_ticket'];
			$ttl_profit += $team['ttl_profit'];
			$ttl_reward += $team['ttl_reward'];
			$i++;
		}
		
		$smarty_obj->assign('DATA', $html);
		$smarty_obj->assign('DATA2', $this->getSaleTargetList());
		$smarty_obj->assign('MONTH_LIST', myGetMonthList($month_list));
		$smarty_obj->assign('YEAR_LIST', myGetYearList($year_list, 3, $year_list));
		$smarty_obj->assign('TTL_BOOKING', format_number($ttl_booking));
		$smarty_obj->assign('TTL_COMPLETE', format_number($ttl_complete));
		$smarty_obj->assign('TTL_EFFICIENCY', format_number($ttl_efficiency / $i, 1, 1).'%');
		$smarty_obj->assign('TTL_TICKET', format_number($ttl_ticket));
		$smarty_obj->assign('TTL_PROFIT', format_number($ttl_profit));
		$smarty_obj->assign('TTL_REWARD', format_number($ttl_reward));
	}
	
	function getTeamList($month, $year){
		global $db;
		
		$sql = "SELECT
			 g.id AS team_id
			,g.name AS team_name
			,SUM(IFNULL(tmp.ttl_booking, 0)) AS ttl_booking
			,SUM(IFNULL(tmp.ttl_complete, 0)) AS ttl_complete
			,ROUND((SUM(IFNULL(tmp.ttl_complete, 0)) * 100 / SUM(IFNULL(tmp.ttl_booking, 0))), 2) AS efficiency
			,SUM(IFNULL(tmp.ttl_ticket, 0)) AS ttl_ticket
			,SUM(IFNULL(tmp.ttl_profit, 0)) AS ttl_profit
			,getRewardAmount(SUM(IFNULL(tmp.ttl_profit, 0)), ".$month.", ".$year.") AS ttl_reward
		FROM (
			SELECT b.id
				  ,1 AS ttl_booking
				  ,0 AS ttl_complete
				  ,0 AS ttl_ticket
				  ,0 AS ttl_profit
				  ,b.modified_user_id AS user_id
			FROM ec_flight_bookings b
			WHERE b.deleted = 0
			AND b.booking_status NOT IN ('7', '8') 
			AND MONTH(DATE_ADD(b.date_entered, INTERVAL 7 HOUR)) = ".$month."
			AND YEAR(DATE_ADD(b.date_entered, INTERVAL 7 HOUR)) = ".$year."
			
			UNION
			SELECT b.id
				  ,1 AS ttl_booking
				  ,1 AS ttl_complete
				  ,SUM(d.quantity) AS ttl_ticket
				  ,(IFNULL(b.total_amount, 0) - (
					   SUM(IFNULL(d.total_bought_price, 0))
					   +
					   IFNULL((
						  SELECT IF(
							 b.flight_type = '0'
							,SUM(IF(p.luggage_price > 0, IFNULL(p.luggage_purchase, 0), 0) + IF(p.luggage_price_inbound > 0, IFNULL(p.luggage_purchase_inbound, 0), 0))
							,SUM(IF(p.luggage_price > 0, IFNULL(p.luggage_purchase, 0), 0))
						  )
						  FROM ec_booking_passengers p
						  WHERE p.deleted = 0
						  AND p.booking_id = b.id
					   ), 0)
					)) AS ttl_profit
				   ,b.assigned_user_id AS user_id
			FROM ec_booking_details d
			LEFT JOIN ec_flight_bookings b ON d.booking_id = b.id AND b.deleted = 0 
			WHERE d.deleted = 0
			AND b.is_ticket_exported = 1
			AND b.booking_status IN ('7', '8')
			AND MONTH(b.date_ticket_issue) = ".$month."
			AND YEAR(b.date_ticket_issue) = ".$year."
			GROUP BY b.id
		) AS tmp
		LEFT JOIN users u ON tmp.user_id = u.id AND u.deleted = 0
		LEFT JOIN securitygroups g ON u.department_id = g.id AND g.deleted = 0
		WHERE g.is_report = 1
		GROUP BY u.department_id
		ORDER BY ttl_reward DESC ";
		
		$arr = array();
		$res = $db->query($sql);
		while($row = $db->fetchByAssoc($res)){
			$arr[] = $row;
		}
		return $arr;
	}
	
	function getSaleTargetList(){
		global $db;
		$html = '';
		$sql = "SELECT s.from_amount, s.to_amount, s.achieve_percent
				FROM ec_saletarget s
				WHERE s.deleted = 0 
				ORDER BY s.achieve_percent DESC ";
		$res = $db->query($sql);
		while($row = $db->fetchByAssoc($res)){
			$html .= '<tr>
				<td>Từ <strong>'.format_number($row['from_amount']).'</strong> đến <strong>'.format_number($row['to_amount']).'</strong></td>
				<td style="text-align:right">'.format_number($row['achieve_percent'], 1, 1).'</td>
			</tr>';
		}
		return $html;
	}
}
	
?>